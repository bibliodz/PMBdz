<?php
require_once($base_path."/classes/marc_table.class.php");

function search_other_function_filters() {
	global $typdoc_multi, $rbs_bibli;
	global $charset;
	global $msg,$dbh;

	$requete = "SELECT typdoc FROM notices where typdoc!='' GROUP BY typdoc";
	$result = mysql_query($requete, $dbh);
	$r .= " <span><table style='width:30%'><tr><td>";
	$r .= " <select name='typdoc_multi[]' multiple size='3'>";
	$r .= "  <option ";
	$r .=" value=''";
	if (is_array($typdoc_multi)) {
		if (in_array("", $typdoc_multi)) $r .=" selected";
	}
	$r .=">".$msg["simple_search_all_doc_type"]."</option>\n";
	$doctype = new marc_list('doctype');
	while (($rt = mysql_fetch_row($result))) {
		$obj[$rt[0]]=1;
	}	
	foreach ($doctype->table as $key=>$libelle){
		if ($obj[$key]==1){
			$r .= "  <option ";
			$r .= " value='$key'";
			if (is_array($typdoc_multi)) {
				if (in_array($key, $typdoc_multi)) $r .=" selected";
			}
			$r .= ">".htmlentities($libelle,ENT_QUOTES, $charset)."</option>\n";
		}
	}
	$r .= "</select></td><td>";
	$r.="<select name='rbs_bibli'>";
	$r.="<option value=''>".htmlentities($msg["search_loc_all_site"],ENT_QUOTES,$charset)."</option>";
	$requete="select location_libelle,idlocation from docs_location where location_visible_opac=1";
	$result = mysql_query($requete, $dbh);
	if (mysql_numrows($result)){
		while ($loc = mysql_fetch_object($result)) {
			$selected="";
			if ($rbs_bibli==$loc->idlocation) {$selected="selected";}
			$r.= "<option value='$loc->idlocation' $selected>$loc->location_libelle</option>";
		}
	}
	$r.="</select>";
	$r.="</td></tr></table></span>";
	return $r;
}

function search_other_function_get_values(){
	global $typdoc_multi, $rbs_bibli;
	return serialize(array($typdoc_multi))."---".$rbs_bibli;
}

function search_other_function_clause() {
	global $typdoc_multi;
	global $rbs_bibli;

	$r = "";
	$where = "";
	$t_m=array();
	if (count($typdoc_multi)) {
		reset($typdoc_multi);
		// on ne remplit pas le tableau si la valeur 'tout type de document' est sélectionnée
		if (!in_array('', $typdoc_multi)) {
			$typdoc_multi = array_flip($typdoc_multi);
			while (list($key,$val)=each($typdoc_multi)) {
				$t_m[]=$key;
			}
			$typdoc_multi = array_flip($typdoc_multi);
		}
		$t_m=implode("','",$t_m);
		if ($t_m) {
			$t_m="'".$t_m."'";
			$where .= " and typdoc in (".$t_m.")";
		}
	}
	if ($rbs_bibli) {
		$where .= " and notice_id in (select expl_notice from exemplaires where expl_location='$rbs_bibli' UNION select  bulletin_notice from bulletins join exemplaires on expl_bulletin=bulletin_id  where expl_location='$rbs_bibli' )";
	}
	if ($t_m || $rbs_bibli) {
		$r="select distinct notice_id from notices where 1 ".$where;
	}
	return $r;
}

function search_other_function_has_values() {
	global $typdoc_multi, $rbs_bibli;
	if ((count($typdoc_multi))||($rbs_bibli)) return true; 
	else return false;
}

function search_other_function_rec_history($n) {
	global $typdoc_multi;
	global $rbs_bibli;
	$_SESSION["typdoc_multi".$n]=$typdoc_multi;
	$_SESSION["rbs_bibli".$n]=$rbs_bibli;
}

function search_other_function_get_history($n) {
	global $typdoc_multi;
	global $rbs_bibli;
	$typdoc_multi=$_SESSION["typdoc_multi".$n];
	$rbs_bibli=$_SESSION["rbs_bibli".$n];	
}

function search_other_function_human_query($n) {
	global $dbh;
	global $typdoc_multi;
	global $rbs_bibli;
	
	$r="";
	$typdoc_multi=$_SESSION["typdoc_multi".$n];
	if (count($typdoc_multi)) {
		$r.="pour les types de documents ";
		$doctype = new marc_list('doctype');
		reset($typdoc_multi);
		$t_d=array();
		while (list($key,$val)=each($typdoc_multi)) {
			$t_d[]=$doctype->table[$val];
		}
		$r.=implode(", ",$t_d);
	}
	$cnl_bibli=$_SESSION["rbs_bibli".$n];
	if ($rbs_bibli) {
		$r.="bibliotheque : ";
		$requete="select location_libelle from docs_location where idlocation='".$rbs_bibli."' limit 1";
		$res=mysql_query($requete);
		$r.=@mysql_result($res,0,0);
	}
	return $r;
}

function search_other_function_post_values() {
	global $typdoc_multi;
	global $rbs_bibli;
	$retour = "";
	if (is_array($typdoc_multi) && count($typdoc_multi)) {
		foreach($typdoc_multi as $v) {
			$retour.= "<input type='hidden' name='typdoc_multi[]' value='".$v."' />\n";
		}
	}
	$retour .= "<input type=\"hidden\" name=\"rbs_bibli\" value=\"$rbs_bibli\">\n";
	
	return $retour;
}

?>