<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: inra.inc.php,v 1.2 2012-01-17 17:39:54 dbellamy Exp $

function get_field_dateparution() {
	global $field_dateparution;
	if(!$field_dateparution) {
		$q = "select idchamp from notices_custom where name='dateparution' limit 1 "; 
		$result = mysql_query ($q);
		if (mysql_num_rows($result)) $field_dateparution = mysql_result($result,0,0);
	}
	if(!$field_dateparution) $field_dateparution=0;
	return $field_dateparution;
}

function search_other_function_filters() {
	global $opac_view_class;
	global $pmb_opac_view_class;
	global $charset,$msg,$base_path;
	
	if(is_object($opac_view_class)) { 
		return $opac_view_class->get_list("chg_opac_view",$_SESSION['opac_view']);
	}else {
		$opac_view_class= new $pmb_opac_view_class(0,0);
		return $opac_view_class->get_list("chg_opac_view");
	}
}

function search_other_function_clause() {
	return '';
}

function search_other_function_has_values() {
	return true;
}

function search_other_function_get_values() {
	return $_SESSION['opac_view'];
}

function search_other_function_rec_history($n) {
}

function search_other_function_get_history($n) {
}

function search_other_function_human_query($n) {
	return "";
}
?>