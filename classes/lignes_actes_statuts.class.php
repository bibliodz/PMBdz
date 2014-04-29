<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: lignes_actes_statuts.class.php,v 1.4 2013-11-28 14:18:52 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class lgstat{
	
	 
	var $id_statut = 0;					//Identifiant de statut de ligne d'acte	
	var $libelle  = '';					//Libelle
	var $relance = 0;					//0=non, 1=oui
	 
	//Constructeur.	 
	function lgstat($id_statut=0) {
		
		if ($id_statut) {
			$this->id_statut = $id_statut;
			$this->load();	
		}

	}	
	
	
	// charge un statut de ligne d'acte � partir de la base.
	function load(){
	
		global $dbh;
		
		$q = "select * from lignes_actes_statuts where id_statut = '".$this->id_statut."' ";
		$r = mysql_query($q, $dbh) ;
		$obj = mysql_fetch_object($r);
		$this->libelle = $obj->libelle;
		$this->relance = $obj->relance;

	}

	
	// enregistre un statut de ligne d'acte en base.
	function save(){
		
		global $dbh;

		if( $this->libelle == '' ) die("Erreur de cr�ation statut de ligne d'acte");
	
		if ($this->id_statut) {
			
			$q = "update lignes_actes_statuts set  
					libelle = '".$this->libelle."',
					relance = '".$this->relance."'
					where id_statut = '".$this->id_statut."' ";
			$r = mysql_query($q, $dbh);
			
		} else {
			
			$q = "insert into lignes_actes_statuts set 
					libelle = '".$this->libelle."',
					relance = '".$this->relance."' ";
			$r = mysql_query($q, $dbh);
			$this->id_statut = mysql_insert_id($dbh);
		
		}
	}


	//Retourne une liste des statuts de lignes d'actes (tableau)
	static function getList($x='ARRAY_ALL') {
		
		global $dbh;
		$res='';
		
		$q = "select * from lignes_actes_statuts order by libelle ";
		
		switch ($x) {
			case 'QUERY' :
				$res=$q;
				break;
			case 'ARRAY_VALUES' :
				$r = mysql_query($q, $dbh);
				$res = array();
				while ($row = mysql_fetch_object($r)){
					$res[] = $row->id_statut;
				}
				break;
			case 'ARRAY_ALL':
			default :
				$r = mysql_query($q, $dbh);
				$res = array();
				while ($row = mysql_fetch_object($r)){
					$res[$row->id_statut] = array();
					$res[$row->id_statut][0] = $row->libelle;
					$res[$row->id_statut][1] = $row->relance;
				}
				break;
		}
		return $res;
	}

	//Retourne un selecteur html avec la liste des statuts de lignes d'actes
	static function getHtmlSelect($selected=array(), $sel_all='', $sel_attr=array()) {
		
		global $dbh,$msg,$charset;

		$sel='';
		$q = "select id_statut,libelle from lignes_actes_statuts order by libelle ";
		$r = mysql_query($q, $dbh);
		$res = array();
		if ($sel_all) {
			$res[0]=htmlentities($sel_all,ENT_QUOTES,$charset);
		}
		
		while ($row = mysql_fetch_object($r)){
			$res[$row->id_statut] = $row->libelle;
		}
		
		$size=count($res);
		if ($sel_attr['size']>$size) $sel_attr['size']=$size;
		
		if ($size) {
			$sel="<select ";
			if (count($sel_attr)) {
				foreach($sel_attr as $attr=>$val) {
					$sel.="$attr='".$val."' ";
				}
			}
			$sel.=">";
			foreach($res as $id=>$val){
				$sel.="<option value='".$id."'";
				if(in_array($id,$selected)) $sel.=" selected='selected'";
				$sel.=" >";
				$sel.=htmlentities($val,ENT_QUOTES,$charset);
				$sel.="</option>";
			}
			$sel.='</select>';
		}
		return $sel;
	}
	
	
	
	//V�rifie si un statut de ligne d'acte existe
	static function exists($id_statut) {
		
		global $dbh;
		$q = "select count(1) from lignes_actes_statuts where id_statut = '".$id_statut."' ";
		$r = mysql_query($q, $dbh); 
		return mysql_result($r, 0, 0);
		
	}
	
		
	//V�rifie si le libelle d'un statut de ligne d'acte existe d�j� en base
	static function existsLibelle($libelle,$id_statut) {

		global $dbh;
		$q = "select count(1) from lignes_actes_statuts where libelle = '".$libelle."' ";
		if ($id_statut) $q.= "and id_statut != '".$id_statut."' ";
		$r = mysql_query($q, $dbh);
		return mysql_result($r, 0, 0);

	}


	//supprime un statut de ligne d'acte de la base
	static function delete($id_statut= 0) {
		
		global $dbh;

		if (!$id_statut) return;

		$q = "delete from lignes_actes_statuts where id_statut = '".$id_statut."' ";
		$r = mysql_query($q, $dbh);
				
	}


	//V�rifie si un statut de ligne d'acte est utilise dans les lignes d'actes	
	static function isUsed($id_statut){
		
		global $dbh;
		if (!$id_statut) return 0;
		$total=0;
		$q = "select count(1) from lignes_actes where num_statut = '".$id_statut."' ";
		$r = mysql_query($q, $dbh); 
		$total+=mysql_result($r, 0, 0);
		$q = "select count(1) from lignes_actes_relances where num_statut = '".$id_statut."' ";
		$r = mysql_query($q, $dbh); 
		$total+=mysql_result($r, 0, 0);
		$q = "select count(1) from users where deflt3lgstatdev='".$id_statut."' or deflt3lgstatcde='".$id_statut." '";
		$r = mysql_query($q, $dbh);
		mysql_result($r, 0, 0);
		$total+=mysql_result($r, 0, 0);
		return $total;
	}


	//optimization de la table lignes_actes_statuts
	function optimize() {
		
		global $dbh;
		
		$opt = mysql_query('OPTIMIZE TABLE lignes_actes_statuts', $dbh);
		return $opt;
				
	}
				
}