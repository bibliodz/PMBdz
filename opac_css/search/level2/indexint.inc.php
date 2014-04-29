<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexint.inc.php,v 1.20 2011-05-19 10:39:21 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// second niveau de recherche OPAC sur indexation interne

//Enregistrement des stats
if($pmb_logs_activate){
	global $nb_results_tab;
	$nb_results_tab['indexint'] = $count;
}

if($opac_allow_affiliate_search){
	print $search_result_affiliate_lvl2_head;
}else {
	print "	<div id=\"resultatrech\"><h3>$msg[resultat_recherche]</h3>\n
		<div id=\"resultatrech_container\">
		<div id=\"resultatrech_see\">";
}

//le contenu du catalogue est calcul� dans 2 cas  :
// 1- la recherche affili�e n'est pas activ�e, c'est donc le seul r�sultat affichable
// 2- la recherche affili�e est active et on demande l'onglet catalog...
if(!$opac_allow_affiliate_search || ($opac_allow_affiliate_search && $tab == "catalog")){
	print pmb_bidi("<h3><span><b>$count</b> $msg[indexint_found] <b>'".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."'");
	if ($opac_search_other_function) {
		require_once($include_path."/".$opac_search_other_function);
		print pmb_bidi(" ".search_other_function_human_query($_SESSION["last_query"]));
	}
	print "</b></font>";
	print activation_surlignage();
	print "</h3></span>\n<ul>";
	
	$found = mysql_query("select *,".$pert." from indexint $clause group by indexint_id $tri $limiter", $dbh);
	
	if(!$opac_allow_affiliate_search) print "
			</div>";
	print "
			<div id=\"resultatrech_liste\">
			<ul>";
	while($mesCategories = mysql_fetch_object($found)) {
		print "<li>";
		$categ_lien = $mesCategories->indexint_id ;
		print pmb_bidi("<a href=./index.php?lvl=indexint_see&id=".$categ_lien."><img src='./images/folder.gif' border='0'/> ".$mesCategories->indexint_name." ".$mesCategories->indexint_comment."</a>");
		print "</li>";
		}
	print "</ul>";
	print "
	</div></div>";
	if($opac_allow_affiliate_search) print $catal_navbar;
	else print "</div>";
}else{
	if($tab == "affiliate"){
		//l'onglet source affili�es est actif, il faut son contenu...
		$as=new affiliate_search_indexint($user_query,"authorities");
		//un peu crade, mais dans l'imm�diat ca fait ce qu'on lui demande...
		$as->filter = $author_type;
		print $as->getResults();
	}
	print "
	</div>
	<div class='row'>&nbsp;</div>";
	//Enregistrement des stats
	if($pmb_logs_activate){
		global $nb_results_tab;
		$nb_results_tab['indexint_affiliate'] = $as->getTotalNbResults();
	}
}
