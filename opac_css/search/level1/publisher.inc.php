<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: publisher.inc.php,v 1.33 2013-10-30 15:00:54 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if ($opac_search_other_function) require_once($include_path."/".$opac_search_other_function);

// on regarde comment la saisie utilisateur se pr�sente
$clause = '';
$add_notice = '';

$aq=new analyse_query(stripslashes($user_query),0,0,1,1);
$members=$aq->get_query_members("publishers","ed_name","index_publisher","ed_id");
$clause.= "where ".$members["where"];

if ($opac_search_other_function) $add_notice=search_other_function_clause();

if ($typdoc || $add_notice) $clause = ', notices '.$clause.' and (ed1_id=ed_id or ed2_id=ed_id) ';

if ($typdoc) $clause.=" and typdoc='".$typdoc."' ";

if ($add_notice) $clause.= ' and notice_id in ('.$add_notice.')'; 

$tri = 'order by pert desc, index_publisher';
$pert=$members["select"]." as pert";

$editeurs = mysql_query("SELECT COUNT(distinct ed_id) FROM publishers $clause", $dbh);
$nb_result_editeurs = mysql_result($editeurs, 0 , 0); 

//Enregistrement des stats
if($pmb_logs_activate){
	global $nb_results_tab;
	$nb_results_tab['editeurs'] = $nb_result_editeurs;
}

//d�finition du formulaire
$form = "<div style=search_result><form name=\"search_publishers\" action=\"./index.php?lvl=more_results\" method=\"post\">";
$form .= "<input type=\"hidden\" name=\"user_query\" value=\"".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."\">\n";
if (function_exists("search_other_function_post_values")){ $form .=search_other_function_post_values(); }
$form .= "<input type=\"hidden\" name=\"mode\" value=\"editeur\">\n";
$form .= "<input type=\"hidden\" name=\"count\" value=\"".$nb_result_editeurs ."\">\n";
$form .= "<input type=\"hidden\" name=\"clause\" value=\"".htmlentities($clause,ENT_QUOTES,$charset)."\">";
$form .= "<input type=\"hidden\" name=\"pert\" value=\"".htmlentities($pert,ENT_QUOTES,$charset)."\">\n";
$form .= "<input type=\"hidden\" name=\"tri\" value=\"".htmlentities($tri,ENT_QUOTES,$charset)."\"></form>\n";	
$form .= "</div>";	

if($opac_allow_affiliate_search){
	$search_result_affiliate_all =  str_replace("!!mode!!","publisher",$search_result_affiliate_lvl1);
	$search_result_affiliate_all =  str_replace("!!search_type!!","authorities",$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!label!!",$msg['publishers'],$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!nb_result!!",$nb_result_editeurs,$search_result_affiliate_all);
	if($nb_result_editeurs){
		$link = "<a href='#' onclick=\"document.search_publishers.action = './index.php?lvl=more_results&tab=catalog'; document.search_publishers.submit();return false;\">".$msg['suite']."&nbsp;<img src='./images/search.gif' border='0' align='absmiddle'/></a>";
	}else $link = "";
	$search_result_affiliate_all =  str_replace("!!link!!",$link,$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!style!!","",$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!user_query!!",rawurlencode(stripslashes((($charset == "utf-8")?$user_query:utf8_encode($user_query)))),$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!form_name!!","search_publishers",$search_result_affiliate_all);
	$search_result_affiliate_all =  str_replace("!!form!!",$form,$search_result_affiliate_all);
	print $search_result_affiliate_all;
}else{
	if ($nb_result_editeurs ) {
		// tout bon, y'a du r�sultat, on lance le pataqu�s d'affichage
		$requete = "select ed_id,ed_name from publishers $clause $tri LIMIT $opac_search_results_first_level";
		// ??? ER : $found = mysql_query($requete, $dbh);
		print "<div style=search_result id=\"publisher\" name=\"publisher\">";
		print "<strong>$msg[publishers]</strong> ".$nb_result_editeurs." $msg[results] ";
		/* while($mesEditeurs = mysql_fetch_object($found)) {
			print "<li><a href='./index.php?lvl=publisher_see&id=".$mesEditeurs->ed_id."'>".$mesEditeurs->ed_name."</a></li>\n";
			} */
		
		// si il y a d'autres r�sultats, je met le lien 'plus de r�sultats'
			// Le lien validant le formulaire est ins�r� dans le code avant le formulaire, cela �vite les blancs � l'�cran
			print "<a href=\"#\" onclick=\"document.forms['search_publishers'].submit(); return false;\">$msg[suite]&nbsp;<img src='./images/search.gif' border='0' align='absmiddle'/></a>";
			
			print $form;
		print "</div>";
	}
}

if ($nb_result_editeurs) {
	$_SESSION["level1"]["publisher"]["form"]=$form;
	$_SESSION["level1"]["publisher"]["count"]=$nb_result_editeurs;	
}