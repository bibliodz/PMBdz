<?php
function search_other_function_filters() {
	global $cnl_bibli;
	global $charset;
	global $msg,$dbh;
	$r.="<select name='cnl_bibli'>";
	$r.="<option value=''>".htmlentities($msg["search_loc_all_site"],ENT_QUOTES,$charset)."</option>";
	$requete="select location_libelle,idlocation from docs_location where location_visible_opac=1";
	$result = mysql_query($requete, $dbh);
	if (mysql_numrows($result)){
		while ($loc = mysql_fetch_object($result)) {
			$selected="";
			if ($cnl_bibli==$loc->idlocation) {$selected="selected";}
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
	$r = "";
	if ($cnl_bibli) {
		$r="select distinct notice_id from notices where notice_id in (select expl_notice from exemplaires where expl_location='$cnl_bibli' UNION select  bulletin_notice from bulletins join exemplaires on expl_bulletin=bulletin_id  where expl_location='$cnl_bibli' )";
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
	global $dbh;
	global $cnl_bibli;
	$r="";
	$cnl_bibli=$_SESSION["cnl_bibli".$n];
	if ($cnl_bibli) {
		$r="bibliotheque : ";
		$requete="select location_libelle from docs_location where idlocation='".$cnl_bibli."' limit 1";
		$res=mysql_query($requete);
		$r.=@mysql_result($res,0,0);
	}
	return $r;
}

function search_other_function_post_values() {
	global $cnl_bibli;
	return "<input type=\"hidden\" name=\"cnl_bibli\" value=\"$cnl_bibli\">\n";
}

?>