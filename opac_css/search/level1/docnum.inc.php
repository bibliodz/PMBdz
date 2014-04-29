<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docnum.inc.php,v 1.19 2014-02-11 13:02:59 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// premier niveau de recherche OPAC sur les documents num�riques

// inclusion classe pour affichage notices (level 1)
require_once($base_path.'/includes/templates/notice.tpl.php');
require_once($base_path.'/classes/notice.class.php');

if ($opac_search_other_function) require_once($include_path."/".$opac_search_other_function);

if($_SESSION["opac_view"] && $_SESSION["opac_view_query"] ){
	$opac_view_restrict=" notice_id in (select opac_view_num_notice from  opac_view_notices_".$_SESSION["opac_view"].") ";
}
if ($typdoc) $restrict="typdoc='".$typdoc."'"; else $restrict="";

//droits d'acces emprunteur/notice
$acces_j='';
if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
	$dom_2= $ac->setDomain(2);
	$acces_j = $dom_2->getJoin($_SESSION['id_empr_session'],16,'notice_id');
} 

// on regarde comment la saisie utilisateur se presente
$clause = '';
$clause_bull = '';
$clause_bull_num_notice = '';
$add_notice = '';

$aq=new analyse_query(stripslashes($user_query),0,0,1,0,$opac_stemming_active);

if ($acces_j) {
	$members=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_notice"," explnum_notice=notice_id and explnum_bulletin=0",0,0,true);
	$clause="where ".$members["where"]." and (".$members["restrict"].")";
	
	$members_bull=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_bulletin"," explnum_bulletin=bulletin_id and explnum_notice=0 and num_notice=0 and bulletin_notice=notice_id",0,0,true);
	$clause_bull="where ".$members_bull["where"]." and (".$members_bull["restrict"].")";
	
	$members_bull_num_notice=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_bulletin"," explnum_bulletin=bulletin_id and num_notice=notice_id",0,0,true);
	$clause_bull_num_notice="where ".$members_bull_num_notice["where"]." and (".$members_bull_num_notice["restrict"].")";
	$statut_j='';

} else {
	$members=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_notice" ," explnum_notice=notice_id and statut=id_notice_statut and (((notice_visible_opac=1 and notice_visible_opac_abon=0) and (explnum_visible_opac=1 and explnum_visible_opac_abon=0)) ".($_SESSION["user_code"]?" or ((notice_visible_opac_abon=1 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1)) or ((notice_visible_opac_abon=0 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1))":"").")",0,0,true);
	$clause="where ".$members["where"]." and (".$members["restrict"].")";
	
	$members_bull=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_bulletin" ," explnum_bulletin=bulletin_id and bulletin_notice=notice_id and num_notice=0 and statut=id_notice_statut and (((notice_visible_opac=1 and notice_visible_opac_abon=0) and (explnum_visible_opac=1 and explnum_visible_opac_abon=0)) ".($_SESSION["user_code"]?" or ((notice_visible_opac_abon=1 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1)) or ((notice_visible_opac_abon=0 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1))":"").")",0,0,true);
	$clause_bull="where ".$members_bull["where"]." and (".$members_bull["restrict"].")";
	
	$members_bull_num_notice=$aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_bulletin" ," explnum_bulletin=bulletin_id and num_notice=notice_id and statut=id_notice_statut and (((notice_visible_opac=1 and notice_visible_opac_abon=0) and (explnum_visible_opac=1 and explnum_visible_opac_abon=0)) ".($_SESSION["user_code"]?" or ((notice_visible_opac_abon=1 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1)) or ((notice_visible_opac_abon=0 and notice_visible_opac=1) and (explnum_visible_opac=1 and explnum_visible_opac_abon=1))":"").")",0,0,true);
	$clause_bull_num_notice="where ".$members_bull_num_notice["where"]." and (".$members_bull_num_notice["restrict"].")";
	
	$statut_j=',notice_statut';
}

if ($opac_search_other_function) {
	$add_notice = search_other_function_clause();
	if ($add_notice) {
		$clause.= ' and notice_id in ('.$add_notice.')';
		$clause_bull.= ' and notice_id in ('.$add_notice.')';  
		$clause_bull_num_notice.= ' and notice_id in ('.$add_notice.')';  
	}
}

$search_terms = $aq->get_positive_terms($aq->tree);
//On enl�ve le dernier terme car il s'agit de la recherche bool�enne compl�te
unset($search_terms[count($search_terms)-1]);

$pert=$members["select"]." as pert";
$tri="order by pert desc, index_serie, tnvol, index_sew";

if ($restrict) {
	$clause.=" and ".$restrict;
	$clause_bull.=" and ".$restrict;
	$clause_bull_num_notice.=" and ".$restrict;
}

if($opac_view_restrict)  $clause.=" and ".$opac_view_restrict;

if($clause) {
	// instanciation de la nouvelle requ�te 
	$q_docnum_noti = "select explnum_id from explnum, notices $statut_j $acces_j $clause"; 
	$q_docnum_bull = "select explnum_id from bulletins, explnum, notices $statut_j $acces_j $clause_bull";
	$q_docnum_bull_notice = "select explnum_id from bulletins, explnum, notices $statut_j $acces_j $clause_bull_num_notice";
	
	$q_docnum = "select count(explnum_id) from ( $q_docnum_noti UNION $q_docnum_bull UNION $q_docnum_bull_notice) as uni	";
	$docnum = mysql_query($q_docnum, $dbh);
	$nb_result_docnum = mysql_result($docnum, 0, 0); 
	
	//Enregistrement des stats
	if($pmb_logs_activate){
		global $nb_results_tab;
		$nb_results_tab['docnum'] = $nb_result_docnum;
	}
	
	
	$req_typdoc_noti="select distinct typdoc from explnum,notices $statut_j $acces_j $clause group by typdoc"; 
	$req_typdoc_bull = "select distinct typdoc from bulletins, explnum,notices $statut_j $acces_j $clause_bull group by typdoc";  
	$req_typdoc_bull_num_notice = "select distinct typdoc from bulletins, explnum,notices $statut_j $acces_j $clause_bull_num_notice group by typdoc";  
	$req_typdoc = "($req_typdoc_noti) UNION ($req_typdoc_bull) UNION ($req_typdoc_bull_num_notice)";
	$res_typdoc = mysql_query($req_typdoc, $dbh);
	$t_typdoc=array();	
	while (($tpd=mysql_fetch_object($res_typdoc))) {
		$t_typdoc[]=$tpd->typdoc;
	}
	$l_typdoc=implode(",",$t_typdoc);	
	if ($nb_result_docnum) {
		// tout bon, y'a du r�sultat, on lance le pataqu�s d'affichage
		// (affichage sur une ligne cliquable, maybe...
		print "<strong>$msg[docnum]</strong> ".$nb_result_docnum." $msg[results] ";
		// $found = mysql_query("select * from notices $clause $tri LIMIT $opac_search_results_first_level", $dbh);
		// si il y a d'autres r�sultats, je met le lien 'plus de r�sultats'
		// Le lien validant le formulaire est ins�r� avant le formulaire, cela �vite les blancs � l'�cran
		print "<a href=\"#\" onclick=\"document.forms['search_docnum'].submit(); return false;\">$msg[suite]&nbsp;<img src='./images/search.gif' border='0' align='absmiddle'/></a>";
		$form = "<div style=search_result><form name=\"search_docnum\" action=\"./index.php?lvl=more_results\" method=\"post\">";
		$form .= "<input type=\"hidden\" name=\"user_query\" value=\"".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."\">\n";
		if (function_exists("search_other_function_post_values")){ $form .=search_other_function_post_values(); }
		$form .= "<input type=\"hidden\" name=\"mode\" value=\"docnum\">\n";
		$form .= "<input type=\"hidden\" name=\"count\" value=\"".$nb_result_docnum."\">\n";
		$form .= "<input type=\"hidden\" name=\"clause\" value=\"".htmlentities($clause,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"clause_bull\" value=\"".htmlentities($clause_bull,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"clause_bull_num_notice\" value=\"".htmlentities($clause_bull_num_notice,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"pert\" value=\"".htmlentities($pert,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"l_typdoc\" value=\"".htmlentities($l_typdoc,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"tri\" value=\"".htmlentities($tri,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"search_terms\" value=\"".htmlentities(serialize($search_terms),ENT_QUOTES,$charset)."\"></form></div>\n";
		
		print $form;
	}
}

if ($nb_result_docnum) {
	$_SESSION["level1"]["docnum"]["form"]=$form;
	$_SESSION["level1"]["docnum"]["count"]=$nb_result_docnum;	
}
?>