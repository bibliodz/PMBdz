<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cnl_crips.inc.php,v 1.5 2012-09-18 15:13:10 mbertin Exp $


function search_other_function_filters() {
	global $thematique;
	global $charset,$msg;
	$r="<select name='thematique'>";
	$r.="<option value=''>Toutes les th�matiques</option>";
	$requete="select * from notices_custom_lists where notices_custom_champ=17 order by notices_custom_list_lib";
	$resultat=mysql_query($requete);
	while (($res=mysql_fetch_object($resultat))) {
		$r.="<option value='".htmlentities($res->notices_custom_list_value,ENT_QUOTES,$charset)."' ";
		if ($res->notices_custom_list_value==$thematique) $r.="selected";
		$r.=">".$res->notices_custom_list_lib;
		$r.="</option>";
	}
	$r.="</select>";
 	
	return $r;
}

function search_other_function_clause() {
	global $thematique;
	$r="";
	if ($thematique) {
		$r .= "select distinct notices_custom_origine from notices_custom_values where notices_custom_integer=".$thematique." and notices_custom_champ=17";
		print "<!--\n".$r."\n-->";	
	}
	return $r;  
}

function search_other_function_has_values() {
	global $thematique;
	if ($thematique) return true; else return false;
}

function search_other_function_get_values(){
	global $thematique;
	return $thematique;
}

function search_other_function_rec_history($n) {
	global $thematique;
	$_SESSION["thematique".$n]=$thematique;
}

function search_other_function_get_history($n) {
	global $thematique;
	$thematique=$_SESSION["thematique".$n];
}

function search_other_function_human_query($n) {
	global $thematique;
	global $msg;
	$r="";
	$thematique=$_SESSION["thematique".$n];
	if ($thematique) {
		$r="th�matique : ";
		$requete="select notices_custom_list_lib from notices_custom_lists where notices_custom_champ=17 and notices_custom_list_value='".$thematique."' limit 1";
		$res=mysql_query($requete);
		$r.=@mysql_result($res,0,0);
	}		
	return $r;
}
?>
