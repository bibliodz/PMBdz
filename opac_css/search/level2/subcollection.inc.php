<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: subcollection.inc.php,v 1.23 2012-07-30 12:24:59 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// second niveau de recherche OPAC sur sous-collections

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
	// requ�te de recherche sur les sous-collections
	print pmb_bidi("	<h3><span>$count $msg[subcolls_found] <b>'".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."'");
	if ($opac_search_other_function) {
		require_once($include_path."/".$opac_search_other_function);
		print pmb_bidi(" ".search_other_function_human_query($_SESSION["last_query"]));
	}
	print "</b>";
	print activation_surlignage();
	print "</h3></span>\n";
	
	if(!$opac_allow_affiliate_search) print "
			</div>";
	print "
			<div id=\"resultatrech_liste\">
			<ul>";

	$found = mysql_query("select sub_coll_id, ".$pert.",sub_coll_name from sub_collections $clause group by sub_coll_id $tri $limiter", $dbh);

	while($mesSousCollections = mysql_fetch_object($found)) {
		print pmb_bidi("<li class='categ_colonne'><font class='notice_fort'><a href='index.php?lvl=subcoll_see&id=".$mesSousCollections->sub_coll_id."'>".$mesSousCollections->sub_coll_name."</a></font></li>\n");
	}
	print "</ul>";
	print "
	</div></div>";
	if($opac_allow_affiliate_search) print $catal_navbar;
	else print "</div>";
}else{
	if($tab == "affiliate"){
		//l'onglet source affili�es est actif, il faut son contenu...
		$as=new affiliate_search_subcollection($user_query,"authorities");
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
		$nb_results_tab['subcollection_affiliate'] = $as->getTotalNbResults();
	}
}
//Enregistrement des stats
if($pmb_logs_activate){
	global $nb_results_tab;
	$nb_results_tab['subcollections'] = $count;
}