<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: term_show.class.php,v 1.21 2012-08-23 14:58:10 mbertin Exp $
//
// Gestion de l'affichage d'un notice d'un terme du th�saurus

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/category.class.php");
require_once($class_path."/thesaurus.class.php");
require_once("$class_path/marc_table.class.php");
require_once("$class_path/aut_link.class.php");

class term_show {

	var $base_query;				//Param�tres suppl�mentaires pass�s dans les URL
	var $term;						//Terme � afficher
	var $parent_link;				//Nom de la fonction � appeller pour afficher les liens d'action � c�t� des cat�gories
	var $url_for_term_show;			//URL a rappeller
	var $keep_tilde;
	var $id_thes = 0;
	var $thes;
	
    function term_show($term,$url_for_term_show,$base_query,$parent_link,$keep_tilde=0, $id_thes) {

    	$this->base_query=$base_query;
    	$this->term=$term;
    	$this->parent_link=$parent_link;
    	$this->url_for_term_show=$url_for_term_show;
    	$this->keep_tilde=$keep_tilde;
    	$this->id_thes = $id_thes;
		$this->thes = new thesaurus($id_thes); 
    }
    
    function has_child($categ_id) {

		$requete = "select count(1) from noeuds where num_parent = '".$categ_id."' ";
		$resultat=mysql_query($requete);
		return mysql_result($resultat,0,0);
	}

	//R�cup�ration du chemin
	function get_categ_lib_($categ_id) {
		global $charset;
		
		$re="";

		//Instanciation de la cat�gorie
		$r=new category($categ_id);
		//R�cup�ration du chemin
		for ($i=0; $i<count($r->path_table); $i++) {
			if ($re!='') $re.=' - ';
			//Si la cat�gorie ne commence pas par "~", on affiche le libelle avec un lien pour la recherche sur le terme, sinon on affiche ~
			if (($r->path_table[$i]['libelle'][0]!='~')||($this->keep_tilde))
				$re.="<a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r->path_table[$i]['libelle']).'&id_thes='.$r->thes->id_thesaurus.'&'.$this->base_query."\">".htmlentities($r->path_table[$i]['libelle'],ENT_QUOTES,$charset).'</a>';
			else{
				$re.='~';
			}
		}
		if ($re!='') $re.=' - ';
		//Si le libell� de la cat�gorie ne commence pas par "~", on affiche le libell� avec un lien sinon ~
		if (($r->libelle[0]!='~')||($this->keep_tilde))
			$re.="<a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r->libelle).'&id_thes='.$r->thes->id_thesaurus.'&'.$this->base_query."\">".htmlentities($r->libelle,ENT_QUOTES,$charset).'</a>';
		else{
			$re.='~';
		}
		return $re;
	}

	function get_categ_lib($categ_id, $categ_libelle,$force_link=false) {
		global $charset;
		
		$r=new category($categ_id);
		
		if($r->is_under_tilde){
			return "~";
		}
		
		if ($r->parent_id) {
			$path=$this->get_categ_lib_($r->parent_id);
		}
		
		$same=false;
		if( pmb_strtolower(convert_diacrit($r->libelle)) == pmb_strtolower(convert_diacrit($categ_libelle))){
			$same=true;
		}
		//if ($r->libelle != $categ_libelle) {
		if (!$same || $force_link) {
			if($same){
				$re=htmlentities($r->libelle,ENT_QUOTES,$charset);
			}else{
				$re="<a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r->libelle).'&id_thes='.$r->thes->id_thesaurus.'&'.$this->base_query."\">".htmlentities($r->libelle,ENT_QUOTES,$charset).'</a>';
			}
			if ($path) $re.='&nbsp;<font size=1>('.$path.')</font>';
		} else {
			if ($path) $re=$path;
		}
		return $re;
	}

	function is_same_lib($categ_libelle,$categ_id) {
		$r=new category($categ_id);
		if( pmb_strtolower(convert_diacrit($r->libelle)) == pmb_strtolower(convert_diacrit($categ_libelle))){
			return true;
		}else{
			return false;
		}
	}

	function show_tree($categ_id,$prefixe,$level,$max_level) {
		
		global $charset;
		global $msg;
		global $lang;
		global $dbh;
		$pl=$this->parent_link;
		global $$pl;
		
		$res='';
		
		if ($this->has_child($categ_id)&&($level<($max_level))) {

		$resultat_2=$this->do_query(4,$categ_id);

			while ($r2=mysql_fetch_object($resultat_2)) {
				if($r2->categ_libelle[0] != "~"){
					$visible=$pl($r2->categ_id,$r2->categ_see);
					if ($visible["VISIBLE"]) {
						$res.='<font size=2>'.$visible['LINK'].'&nbsp;'.$prefixe." - <a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r2->categ_libelle).'&id_thes='.$this->id_thes.'&'.$this->base_query."\">".htmlentities($r2->categ_libelle,ENT_QUOTES,$charset).'</a></font>';
						if ($r2->categ_see) {
							$res.='<br /><font size=1>&nbsp;&nbsp;<i>'.$msg['term_show_see'].' '.$this->get_categ_lib($r2->categ_see,$r2->categ_libelle,true);
							//if ($this->is_same_lib($r2->categ_libelle,$r2->categ_see)) $res.=' - '.htmlentities($r2->categ_libelle,ENT_QUOTES,$charset);
							$res.='</i></font>';
						}
						$res.='<br />';
					}
					$res.=$this->show_tree($r2->categ_id,$prefixe." - <a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r2->categ_libelle).'&id_thes='.$this->id_thes.'&'.$this->base_query."\">".htmlentities($r2->categ_libelle,ENT_QUOTES,$charset).'</a>',$level+1,$max_level);
				}
			}
		}
		return $res;
	}


	function get_level($categ_id) {
		$l=0;
		$parent=new category($categ_id);
		$l=count($parent->path_table);
		return $l;
	}


	function show_notice() {
		
		global $history,$history_thes;
		global $charset;
		global $msg;
		global $dbh;
		global $lang;
		global $thesaurus_mode_pmb;
		$pl=$this->parent_link;
		global $$pl;

		$res='';
		
		if ($history!='') {
			$res.="<a href=\"".$this->url_for_term_show.'?term='.rawurlencode(stripslashes($history)).'&id_thes='.rawurlencode(stripslashes($history_thes)).'&'.$this->base_query."\">&lt;</a>&nbsp;";
		}

		//R�cup�ration des cat�gories ayant le m�me libell�
		$resultat_1=$this->do_query(1);
		
		if($thesaurus_mode_pmb == 0){
			$res.='<b>'.htmlentities($this->term,ENT_QUOTES,$charset).'</b><blockquote>';
		}else{
			$res.='<b>'.htmlentities("[".$this->thes->libelle_thesaurus."] ".$this->term,ENT_QUOTES,$charset).'</b><blockquote>';
		}
		

		//Initialisation du tableau des renvois (permet d'�viter d'afficher deux fois un m�me renvoi, ou un renvoi vers le noeud trait�)
		$t_see=array();

		//Pour chaque cat�gorie ayant le m�me libell�
		while ($r1=mysql_fetch_object($resultat_1)) {
			$t_see[$r1->categ_id]=1;//Pour les renvois vers le un noeud trait�
			//Lecture du chemin vers la cat�gorie
			$renvoi=$this->get_categ_lib($r1->categ_id,$this->term).' ';
			//Si la cat�gorie est une sous cat�gorie d'une terme "~", alors c'est un renvoi d'un terme orphelin ou on en tient pas compte
			if (($renvoi[0]=='~')&&($r1->categ_see)&&(!$this->keep_tilde)) {
				//Si le renvoi n'existe pas d�j�, on l'affiche et on l'enregistre
				if (!$t_see[$r1->categ_see]) {
					$visible=$pl($r1->categ_id,$r1->categ_see);
					if ($visible["VISIBLE"])
						$res.=$visible["LINK"].'&nbsp;<i>'.$msg['term_show_see'].' </i>'.$this->get_categ_lib($r1->categ_see,$this->term).'<br />';
					$t_see[$r1->categ_see]=1;
				}
			} else {
				if (($renvoi[0]!='~')||($this->keep_tilde)) {
					//Si la cat�gorie n'est pas une sous cat�gorie d'un terme "~", on affiche le chemin					$visible=$pl($r1->categ_id,$r1->categ_see);
					$visible=$pl($r1->categ_id,$r1->categ_see);
					if ($visible["VISIBLE"]) {
						$res.=$visible["LINK"].'&nbsp;'.$renvoi.' - <b>'.$r1->categ_libelle.'</b><br />';
						//Si il y a un renvoi, on l'affiche
						if ($r1->categ_see) {
							$res.='<blockquote>'.$msg['term_show_see'].' '.$this->get_categ_lib($r1->categ_see,$r1->categ_libelle,true);
							//Si c'est le m�me libell�, on l'ajoute au chemin parent, sans lien
							$res.='</blockquote><br />';
						}
					}
				}
			}
			
			//Si le renvoi ne commence pas par "~" alors on affiche les sous niveaux et les cat�gories associ�es
			if (($renvoi[0]!='~')||($this->keep_tilde)) {
				//Affichage des premiers sous niveaux
				$res.='<blockquote>';
				//Recherche du niveau de la cat�gorie (0,1 ou sup�rieur � 1)
				$l=$this->get_level($r1->categ_id);
				//Si le niveau est sup�rieur � 1, on affiche que deux sous niveaux sinon 3
				if ($l>1) $max_level=3; else $max_level=2;
		
				//Affichage des n sous premiers niveaux
				$res.=$this->show_tree($r1->categ_id,$this->term,0,$max_level);	
				$res.='</blockquote>';
				
				//Recherche des cat�gories associ�es
				$requete = "select count(1) from voir_aussi where voir_aussi.num_noeud_orig = '".$r1->categ_id."' ";
				$nta=mysql_result(mysql_query($requete),0,0);
				//Si il y en a
				if ($nta) {
					$res.='<blockquote>';
					
					$resultat_ta=$this->do_query(2,$r1->categ_id);
					
					$first = 1;
					$res1 = '';
					while ($r_ta=mysql_fetch_object($resultat_ta)) {
						$visible=$pl($r_ta->categ_id,$r_ta->categ_see);
						if ($visible["VISIBLE"]) {
							if (!$first) $res1.=", "; else $first=0;
							$res1.=$visible["LINK"]."&nbsp;<a href=\"".$this->url_for_term_show.'?term='.rawurlencode($r_ta->categ_libelle).'&id_thes='.$this->id_thes.'&'.$this->base_query."\">".htmlentities($r_ta->categ_libelle,ENT_QUOTES,$charset).'</a>';
						}
					}
					if ($res1!='') $res.=''.$msg['term_show_see_also'].'<blockquote><font size=2><i>'.$res1.'</i></font></blockquote>';
					$res.= '</blockquote>';
				}
				
				//Recherche des liens d'autorit�s entre cat�gories
				$aut_link= new aut_link(AUT_TABLE_CATEG,$r1->categ_id);
				if(count($aut_link->aut_list)){
					$res1 = array();
					$source = new marc_list("relationtype_autup");
					$tab_lib_autup = $source->table;
					$source = new marc_list("relationtype_aut");
					$tab_lib_aut = $source->table;
					foreach ( $aut_link->aut_list as $val ) {
       					if($val["to"] == AUT_TABLE_CATEG){
							$r_link=$this->do_query(3,$val["to_num"]);
							
							if(mysql_num_rows($r_link) == 1){
								$r_link_res=mysql_fetch_object($r_link);
								$visible=$pl($r_link_res->categ_id,$r_link_res->categ_see);
								$info_thes="";
								if($r_link_res->thes_id != $this->id_thes){
									$info_thes="[".$r_link_res->thes_libelle."] ";
								}
								if ($visible["VISIBLE"]) {
									$tmp=$visible["LINK"]."&nbsp;".htmlentities($info_thes,ENT_QUOTES,$charset).$this->get_categ_lib($r_link_res->categ_id, $this->term,true);
									if($val["flag_reciproc"]){
										$res1[$tab_lib_autup[$val["type"]]][]=$tmp;
									}else{
										$res1[$tab_lib_aut[$val["type"]]][]=$tmp;
									}
								}
							}
       					}
					}
					if(count($res1)){
						$res.='<blockquote>'.$msg['aut_link'].' :';
						foreach ( $res1 as $key => $value ) {
       						$res.='<font size=2><i><blockquote>'.htmlentities($key,ENT_QUOTES,$charset).' : '.implode(",",$value).'</blockquote></i></font>';
						}
						$res.= '</blockquote>';
					}
				}
			}
		}
		$res.= '</blockquote>';
		return $res;
	}
	
	
	function do_query($mode,$param=""){
		global $lang;
		$select="SELECT DISTINCT noeuds.id_noeud AS categ_id, ";
		$from="FROM noeuds ";
		$join=" JOIN categories AS catdef ON noeuds.id_noeud = catdef.num_noeud AND catdef.langue = '".addslashes($this->thes->langue_defaut)."' ";
		$where="WHERE 1 ";
		$order="ORDER BY categ_libelle ";
		$limit="";
		
		if(($lang==$this->thes->langue_defaut) || (in_array($lang, thesaurus::getTranslationsList())===false)){
			$simple=true;
		}else{
			$simple=false;
		}
		
		//$select.= "noeuds.num_parent AS categ_parent, ";
		
		
		if($simple){
			$select.="catdef.libelle_categorie AS categ_libelle, ";
			//$select.= "catdef.note_application as categ_comment, ";
			//$select.= "catdef.index_categorie as index_categorie ";
		}else{
			$select.="IF (catlg.num_noeud IS NULL, catdef.libelle_categorie, catlg.libelle_categorie) AS categ_libelle, ";
			$join.="LEFT JOIN categories AS catlg ON catdef.num_noeud = catlg.num_noeud AND catlg.langue = '".$lang."' ";
			//$select.= "if (catlg.num_noeud is null, catdef.note_application, catlg.note_application) as categ_comment, ";
			//$select.= "if (catlg.num_noeud is null, catdef.index_categorie, catlg.index_categorie) as index_categorie ";
		}
		
		if($mode == 1){
			$where.="AND noeuds.num_thesaurus = '".$this->id_thes."' ";
			if($simple){
				$where.="AND catdef.libelle_categorie = '".addslashes($this->term)."' ";
			}else{
				$where.="AND (IF (catlg.num_noeud IS NULL, catdef.libelle_categorie = '".addslashes($this->term)."', catlg.libelle_categorie = '".addslashes($this->term)."') ) ";
			}
		}elseif($mode == 2){
			$from="FROM voir_aussi JOIN noeuds ON noeuds.id_noeud=voir_aussi.num_noeud_dest ";//On �crase l'ancien from car ce n'est pas ce que l'on veut
			$where.="AND voir_aussi.num_noeud_orig = '".$param."' ";
		}elseif($mode == 3){
			$select.="noeuds.num_thesaurus as thes_id, ";
			$select.="thesaurus.libelle_thesaurus as thes_libelle, ";
			$join.="JOIN thesaurus ON noeuds.num_thesaurus=thesaurus.id_thesaurus ";
			$where.="AND noeuds.id_noeud = '".$param."' ";
		}elseif($mode == 4){
			$where.="AND noeuds.num_parent = '".$param."' ";
			$limit.="LIMIT 400";
		}

		$select.="noeuds.num_renvoi_voir AS categ_see ";

		$requete=$select.$from.$join.$where.$order.$limit;
		return mysql_query($requete);
	}
}
?>
