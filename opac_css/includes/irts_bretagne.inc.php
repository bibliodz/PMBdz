<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: irts_bretagne.inc.php,v 1.3 2012-09-18 15:13:10 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function search_other_function_filters() {
	global $typ_notice, $charset, $irts_bibli, $dbh;
	
	$r.="<select name='irts_bibli'>";
	$r.="<option value=''>tous les sites</option>";
	$requete="select location_libelle,idlocation from docs_location where location_visible_opac=1";
	$result = mysql_query($requete, $dbh);
	if (mysql_numrows($result)){
		while ($loc = mysql_fetch_object($result)) {
			$selected="";
			if ($irts_bibli==$loc->idlocation) {$selected="selected";}
			$r.= "<option value='$loc->idlocation' $selected>$loc->location_libelle</option>";
		}
	}
	$r.="</select>";

	$r.="<br/>Restreindre à <input type='checkbox' name=\"typ_notice[a]\" value='1' ".($typ_notice['a']?"checked":"")."/>&nbsp;Articles&nbsp;
		<input type='checkbox' name=\"typ_notice[b]\" value='1' ".($typ_notice['b']?"checked":"")."/>&nbsp;Numéros de revue&nbsp;
		<input type='checkbox' name=\"typ_notice[s]\" value='1' ".($typ_notice['s']?"checked":"")."/>&nbsp;Revues&nbsp;
		<input type='checkbox' name=\"typ_notice[m]\" value='1' ".($typ_notice['m']?"checked":"")."/>&nbsp;Tout sauf revues";
	return $r;
}

function search_other_function_clause() {
	global $typ_notice;
	global $irts_bibli;
	reset($typ_notice);
	$from="";
	$where="";
	if ($irts_bibli) {
		$from .= ",exemplaires";
		$where .= " and notices.notice_id=exemplaires.expl_notice and expl_location=$irts_bibli";
	}
	$t_n=array();
	while (list($key,$val)=each($typ_notice)) {
		$t_n[]=$key;
	}
	$t_n=implode("','",$t_n);
	if ($t_n) {
		$t_n="'".$t_n."'";
		$where .= " and niveau_biblio in (".$t_n.")";
	}
	if ($irts_bibli || $t_n) {
		$r = "select distinct notice_id from notices $from where 1 $where";
	}
	return $r;
}

function search_other_function_has_values() {
	global $typ_notice;
	global $irts_bibli;
	if ((count($typ_notice))||($irts_bibli)) return true; else return false;
}

function search_other_function_get_values(){
	global $typ_notice, $irts_bibli;
	return serialize($typ_notice)."---".$irts_bibli;
}

function search_other_function_rec_history($n) {
	global $typ_notice;
	global $irts_bibli;
	$_SESSION["irts_bibli".$n]=$irts_bibli;
	$_SESSION["typ_notice".$n]=$typ_notice;
}

function search_other_function_get_history($n) {
	global $typ_notice;
	global $irts_bibli;
	$irts_bibli=$_SESSION["irts_bibli".$n];
	$typ_notice=$_SESSION["typ_notice".$n];
}

function search_other_function_human_query($n) {
	global $dbh;
	global $typ_notice;
	global $irts_bibli;
	$r="";
	$irts_bibli=$_SESSION["irts_bibli".$n];
	if ($irts_bibli) {
		$r="bibliotheque : ";
		$requete="select location_libelle from docs_location where idlocation='".$irts_bibli."' limit 1";
		$res=mysql_query($requete,$dbh);
		$r.=@mysql_result($res,0,0);
	}
	$notices_t=array("m"=>"Monographies","s"=>"Périodiques","a"=>"Articles","b"=>"Bulletins");
	$typ_notice=$_SESSION["typ_notice".$n];
	if (count($typ_notice)) {
		$r.="pour les types de notices ";
		reset($typ_notice);
		$t_l=array();
		while (list($key,$val)=each($typ_notice)) {
			$t_l[]=$notices_t[$key];
		}
		$r.=implode(", ",$t_l);
	}
	
	return $r;
}

function search_other_function_post_values() {
	global $irts_bibli,$typ_notice;
	
	$ret = "";
	if ($typ_notice[m] != "") $ret .= "<input type=\"hidden\" name=\"typ_notice[m]\" value=\"$typ_notice[m]\">";
	if ($typ_notice[s] != "") $ret .= "<input type=\"hidden\" name=\"typ_notice[s]\" value=\"$typ_notice[s]\">";
	if ($typ_notice[b] != "") $ret .= "<input type=\"hidden\" name=\"typ_notice[b]\" value=\"$typ_notice[b]\">";
	if ($typ_notice[a] != "") $ret .= "<input type=\"hidden\" name=\"typ_notice[a]\" value=\"$typ_notice[a]\">";
	return "<input type=\"hidden\" name=\"irts_bibli\" value=\"$irts_bibli\">".$ret."\n";
	
}

?>