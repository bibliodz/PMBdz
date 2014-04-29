<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// � 2006 mental works / www.mental-works.com contact@mental-works.com
// 	repris et corrig� par PMB Services 
// +-------------------------------------------------+
// $Id: tags.class.php,v 1.5 2012-02-02 13:12:26 dbellamy Exp $

// d�finition de la classe d'affichage des 'tags'



class tags {

	// ---------------------------------------------------------------
	//		propri�t�s de la classe
	// ---------------------------------------------------------------
	var $search_tag=''; 
	// ---------------------------------------------------------------
	//		constructeur
	// ---------------------------------------------------------------
	function tags() {		
	}
	
	function get_array($start='', $pos_cursor=0){
		global $dbh;
		global $pmb_keyword_sep;
		
		$liste_mots = array();
		$liste_res = array();
		$tags = array();
		$liste_finale = array();
		
		$deb_chaine='';
		$fin_chaine='';

	 	if(strlen($start)==$pos_cursor){
	 		$liste_mots=explode($pmb_keyword_sep,$start);
			$mot = array_pop($liste_mots);
			$deb_chaine = implode($pmb_keyword_sep,$liste_mots);
			if(trim($deb_chaine)!=='') $deb_chaine.=$pmb_keyword_sep;			
	 	} else {
	 		$liste_mots = explode($pmb_keyword_sep,substr($start,0,$pos_cursor));
	 		$mot = array_pop($liste_mots);
	 		$deb_chaine = implode($pmb_keyword_sep,$liste_mots);
	 		if (trim($deb_chaine)!=='') $deb_chaine.=$pmb_keyword_sep;
	 		$liste_mots = explode($pmb_keyword_sep,substr($start,$pos_cursor));
	 		array_shift($liste_mots);
	 		$fin_chaine = $pmb_keyword_sep.implode($pmb_keyword_sep,$liste_mots);	
	 	}
	 	$mot=trim($mot);
	 	if ($mot==='') return $liste_finale;
	 	
		$this->search_tag = $mot;

		$requete = "select distinct index_l from notices where index_l is not null and index_l like '".addslashes($mot)."%' or index_l like '%".$pmb_keyword_sep.addslashes($mot)."%' ";
		$res = mysql_query($requete,$dbh);
		while(($mot_trouve=mysql_fetch_object($res))){
			$liste_tmp = explode($pmb_keyword_sep,$mot_trouve->index_l);
			foreach($liste_tmp as $v) {
				if (strip_empty_chars(substr($v,0,strlen($mot))) == strip_empty_chars($mot)) $liste_res[]=$v;
			}
		}
		$liste_res=array_unique($liste_res);
		asort($liste_res);
		
		foreach($liste_res as $v) {
			$liste_finale[] = array($v=>$deb_chaine.$v.$fin_chaine); 
		}
		return $liste_finale;
	}
	
	
	function get_taille_search(){
		return strlen($this->search_tag);
	}
	
}
?>