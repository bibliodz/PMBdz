<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_localisation_mpba.inc.php,v 1.5 2013-12-19 09:25:20 dgoron Exp $

/*
 * 
 * Permet de limiter la recherche aux documents dont les exemplaires sont localis�s tel que sp�cifi� dans l'interface*
 * 
 * S'applique � :
 * notices de monographies dont un exemplaire est localise
 * notices de p�rio dont un exemplaire de bulletin est localise
 * notices de bulletins dont un exemplaire est localise
 * notices d'articles rattach�es a des bulletins dont un exemplaire est localise
 * 
 * Param�trer opac_search_other_function
*/

function search_other_function_filters() {
	global $cnl_bibli;
	global $charset,$msg;
	global $dbh;
	$r.="<select name='cnl_bibli'>";
	$r.="<option value=''>".htmlentities($msg["search_loc_all_site"],ENT_QUOTES,$charset)."</option>";
	$requete="select location_libelle,idlocation from docs_location where location_visible_opac=1";
	$result = mysql_query($requete, $dbh);
	if (mysql_numrows($result)){
		while ($loc = mysql_fetch_object($result)) {
			$selected="";
			if ($cnl_bibli==$loc->idlocation) {$selected="selected='selected'";}
			$r.= "<option value='$loc->idlocation' $selected>$loc->location_libelle</option>";
		}
	}
	$r.="</select>";
	return $r;
}

function search_other_function_get_values(){
	global $cnl_bibli;
	return $cnl_bibli;
}

function search_other_function_clause() {
	global $cnl_bibli;
	$cnl_bibli+=0;
	if ($cnl_bibli) {
		$r = "select distinct notice_id from notices where notice_id in ( ";
		//notices de mono dont un exemplaire est localise a $cnl_bibli
		$r.= "select expl_notice from exemplaires where expl_bulletin='0' AND expl_location='$cnl_bibli' ";
		//notices de p�rio dont un exemplaire de bulletin est localise a $cnl_bibli 
		$r.= "UNION select DISTINCT bulletin_notice from bulletins join exemplaires on expl_bulletin=bulletin_id AND expl_notice=0  where expl_location='$cnl_bibli' ";
		//notices de bulletins dont un exemplaire est localise a $cnl_bibli
		$r.= "UNION select DISTINCT num_notice from bulletins join exemplaires on expl_bulletin=bulletin_id AND expl_notice=0 where expl_location='$cnl_bibli' ";
		//notices d'articles rattach�es a des bulletins dont un exemplaire est localise a $cnl_bibli	
		$r.= "UNION select analysis_notice from analysis join bulletins on analysis_bulletin=bulletin_id join exemplaires on expl_bulletin=bulletin_id AND expl_notice=0 where expl_location='$cnl_bibli' ";
		//notices de mono/p�riodique/article dont un exemplaire num�rique est localise a $cnl_bibli ou dans toutes les localisations
		$r.= "UNION select DISTINCT explnum_notice from explnum LEFT JOIN explnum_location ON explnum_id=num_explnum WHERE explnum_bulletin='0' AND (num_location='$cnl_bibli' OR num_location IS NULL)  ";
		//notices de bulletin dont un exemplaire num�rique est localise a $cnl_bibli ou dans toutes les localisations
		$r.= "UNION select DISTINCT num_notice from bulletins JOIN explnum ON explnum_bulletin=bulletin_id AND explnum_notice='0' LEFT JOIN explnum_location ON explnum_id=num_explnum WHERE num_location='$cnl_bibli' OR num_location IS NULL  ";
		$r.= ")";
	}
	return $r;
}

function search_other_function_has_values() {
	global $cnl_bibli;
	if ($cnl_bibli) return true; 
	else return false;
}

function search_other_function_rec_history($n) {
	global $cnl_bibli;
	$_SESSION["cnl_bibli".$n]=$cnl_bibli;
}

function search_other_function_get_history($n) {
	global $cnl_bibli;
	$cnl_bibli=$_SESSION["cnl_bibli".$n];
}

function search_other_function_human_query($n) {
	global $dbh,$msg,$charset;
	global $cnl_bibli;
	$r="";
	$cnl_bibli=$_SESSION["cnl_bibli".$n];
	if ($cnl_bibli) {
		$r=htmlentities($msg["search_loc_mpba_bib"],ENT_QUOTES,$charset)." : ";
		$requete="select location_libelle from docs_location where idlocation='".$cnl_bibli."' limit 1";
		$res=mysql_query($requete);
		$r.=@mysql_result($res,0,0);
	}
	return $r;
}

function search_other_function_post_values() {
	global $cnl_bibli;
	return "<input type=\"hidden\" name=\"cnl_bibli\" value=\"$cnl_bibli\" />\n";
}
