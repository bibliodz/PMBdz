<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: tva_achats.class.php,v 1.9 2013-11-29 08:32:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class tva_achats{
	
	
	var $id_tva = 0;					//Identifiant de tva_achats 
	var $libelle = '';					//Libelle sur la tva
	var $taux_tva = '0.00';				//taux de tva en %					
	var $num_cp_compta = 0;

	 
	//Constructeur.	 
	function tva_achats($id_tva= 0) {
		
		global $dbh;
	
		if ($id_tva) {
			$this->id_tva = $id_tva;
			$this->load();	
		}
	}
	
		
	// charge le taux de tva � partir de la base.
	function load(){
	
		global $dbh;
		
		$q = "select * from tva_achats where id_tva = '".$this->id_tva."' ";
		$r = mysql_query($q, $dbh) ;
		$obj = mysql_fetch_object($r);
		$this->libelle = $obj->libelle;
		$this->taux_tva = $obj->taux_tva;
		$this->num_cp_compta = $obj->num_cp_compta;

	}

	
	// enregistre le taux de tva en base.
	function save(){
		
		global $dbh;
		
		if(!$this->libelle) die("Erreur de cr�ation tva_achats");
		
		if($this->id_tva) {
		
			$q = "update tva_achats set taux_tva ='".$this->taux_tva."', libelle = '".$this->libelle."', num_cp_compta = '".$this->num_cp_compta."' ";
			$q.= "where id_tva = '".$this->id_tva."' ";
			$r = mysql_query($q, $dbh);
			
		} else {
			
			$q = "insert into tva_achats set libelle = '".$this->libelle."', taux_tva = '".$this->taux_tva."', num_cp_compta = '".$this->num_cp_compta."' ";
			$r = mysql_query($q, $dbh);
			$this->id_tva = mysql_insert_id($dbh);
			
		}

	}


	//supprime un taux de tva de la base
	static function delete($id_tva= 0) {
		
		global $dbh;

		if(!$id_tva) return; 	

		$q = "delete from tva_achats where id_tva = '".$id_tva."' ";
		$r = mysql_query($q, $dbh);
				
	}


	//Retourne une requete contenant la liste des taux de tva achats
	static function listTva() {
		
		$q = "select * from tva_achats order by libelle ";
		return $q;
	}


	//Compte les taux de tva achats
	static function countTva() {
		
		global $dbh;

		$q = "select count(1) from tva_achats  ";
		$r = mysql_query($q, $dbh);
		return mysql_result($r, 0, 0);
				
	}


	//V�rifie si un taux de tva achats existe			
	static function exists($id_tva){
		
		global $dbh;
		$q = "select count(1) from tva_achats where id_tva = '".$id_tva."' ";
		$r = mysql_query($q, $dbh); 
		return mysql_result($r, 0, 0);
		
	}


	//V�rifie si le libell� d'un taux de tva achats existe d�j�			
	static function existsLibelle($libelle, $id_tva=0){
		
		global $dbh;
		$q = "select count(1) from tva_achats where libelle = '".$libelle."' ";
		if ($id_tva) $q.= "and id_tva != '".$id_tva."' ";
		$r = mysql_query($q, $dbh); 
		return mysql_result($r, 0, 0);
		
	}


	//V�rifie si le taux de tva achats est utilis� dans les types de produits			
	static function hasTypesProduits($id_tva= 0){
		
		global $dbh;
		if (!$id_tva) return 0;
		$q = "select count(1) from types_produits where num_tva_achat = '".$id_tva."' ";
		$r = mysql_query($q, $dbh); 
		return mysql_result($r, 0, 0);
		
	}


	//V�rifie si le taux de tva achats est utilis� dans les frais		
	static function hasFrais($id_tva= 0){
		
		global $dbh;
		if (!$id_tva) return 0;
		$q = "select count(1) from frais where num_tva_achat = '".$id_tva."' ";
		$r = mysql_query($q, $dbh); 
		return mysql_result($r, 0, 0);
		
	}
	
	//optimization de la table taux de tva
	function optimize() {
		
		global $dbh;
		
		$opt = mysql_query('OPTIMIZE TABLE tva_achats', $dbh);
		return $opt;
				
	}
	
				
}
?>