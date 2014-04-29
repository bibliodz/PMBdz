<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: keyword.inc.php,v 1.62 2014-02-11 13:02:59 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// second niveau de recherche OPAC sur mots clés
require_once($class_path."/searcher.class.php");

//Enregistrement des stats
if($pmb_logs_activate){
	global $nb_results_tab;
	$nb_results_tab['keywords'] = $count;
}

if($_SESSION["opac_view"] && $_SESSION["opac_view_query"] ){
	$opac_view_restrict=" notice_id in (select opac_view_num_notice from  opac_view_notices_".$_SESSION["opac_view"].") ";
}

if($opac_allow_affiliate_search){
	print $search_result_affiliate_lvl2_head;
}else {
	print "	<div id=\"resultatrech\"><h3>$msg[resultat_recherche]</h3>\n
		<div id=\"resultatrech_container\">
		<div id=\"resultatrech_see\">";
}


//le contenu du catalogue est calculé dans 2 cas  :
// 1- la recherche affiliée n'est pas activée, c'est donc le seul résultat affichable
// 2- la recherche affiliée est active et on demande l'onglet catalog...
if(!$opac_allow_affiliate_search || ($opac_allow_affiliate_search && $tab == "catalog")){
	// gestion du tri
	if (isset($_GET["sort"])) {	
		$_SESSION["last_sortnotices"]=$_GET["sort"];
	}
	if ($count>$opac_nb_max_tri) {
		$_SESSION["last_sortnotices"]="";
	}
	
	$searcher = new searcher_keywords(stripslashes($user_query));
	if(!isset($count)){
		//accès direct sans calcul préalable, on a besoin de recalculer les éléments du paginateur
		$count = $searcher->get_nb_results();
		$nbepages = ceil($count/$opac_search_results_per_page);
		$catal_navbar= "<div class='row'>&nbsp;</div>";
		$catal_navbar .= "<div id='navbar'><hr />\n<center>".printnavbar($page, $nbepages, $url_page,$action)."</center></div>";
	}
	
	if($opac_allow_tags_search == 1)
		print pmb_bidi("<h3><span><b>$count ".$msg["results"]." ".$msg['tags_found']." '".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."'");
	else
		print pmb_bidi("<h3><span><b>$count ".$msg["results"]." ".$msg['keywords_found']." '".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."'");
	
	if ($opac_search_other_function) {
		require_once($include_path."/".$opac_search_other_function);
		print pmb_bidi(" ".search_other_function_human_query($_SESSION["last_query"]));
	}
	print " </b></span>";
	print activation_surlignage();
	print "</h3>";
		
	if($opac_visionneuse_allow){
		$nbexplnum_to_photo = $searcher->get_nb_explnums();	
	}
	if($count){
		if(isset($_SESSION["last_sortnotices"]) && $_SESSION["last_sortnotices"]!==""){
			$notices = $searcher->get_sorted_result($_SESSION["last_sortnotices"],$debut,$opac_search_results_per_page);	
		}else{
			$notices = $searcher->get_sorted_result("default",$debut,$opac_search_results_per_page);	
		}
	}		
	
	if(!$opac_allow_affiliate_search) print "
			</div>";
	print "
			<div id=\"resultatrech_liste\">";
	
	if ($opac_notices_depliable) print $begin_result_liste;
	
	//gestion du tri
	if ($count<=$opac_nb_max_tri) {
		$pos=strpos($_SERVER['REQUEST_URI'],"?");
		$pos1=strpos($_SERVER['REQUEST_URI'],"get");
		if ($pos1==0) $pos1=strlen($_SERVER['REQUEST_URI']);
		else $pos1=$pos1-3;
		$para=urlencode(substr($_SERVER['REQUEST_URI'],$pos+1,$pos1-$pos+1));
		$para1=substr($_SERVER['REQUEST_URI'],$pos+1,$pos1-$pos+1);
		$affich_tris_result_liste=str_replace("!!page_en_cours!!",$para,$affich_tris_result_liste); 
		$affich_tris_result_liste=str_replace("!!page_en_cours1!!",$para1,$affich_tris_result_liste);
		print $affich_tris_result_liste;
		if ($_SESSION["last_sortnotices"]!="") {
			$sort=new sort('notices','session');
			print "<span class='sort'>".$msg['tri_par']." ".$sort->descriptionTriParId($_SESSION["last_sortnotices"])."&nbsp;</span>"; 
		} elseif ($opac_default_sort_display) {
			$sort=new sort('notices','session');
			print "<span class='sort'>".$msg['tri_par']." ".$sort->descriptionTriParId("default")."&nbsp;</span>";
		}
	} else print "&nbsp;";
	//fin gestion du tri
	
	print $add_cart_link;
	if($opac_visionneuse_allow && $nbexplnum_to_photo){
		print "&nbsp;&nbsp;&nbsp;".$link_to_visionneuse;
		print $sendToVisionneuseByPost; 
	}
	//affinage
	//enregistrement de l'endroit actuel dans la session
	if ($_SESSION["last_query"]) {	$n=$_SESSION["last_query"]; } else { $n=$_SESSION["nb_queries"]; }
	
	$_SESSION["notice_view".$n]["search_mod"]="keyword";
	$_SESSION["notice_view".$n]["search_page"]=$page;
	
	//affichage
	print "&nbsp;&nbsp;<a href='$base_path/index.php?search_type_asked=extended_search&mode_aff=aff_simple_search'>".$msg["affiner_recherche"]."</a>";
	//fin affinage
	//Etendre
	if ($opac_allow_external_search) print "&nbsp;&nbsp;<a href='$base_path/index.php?search_type_asked=external_search&mode_aff=aff_simple_search&external_type=simple'>".$msg["connecteurs_external_search_sources"]."</a>";
	//fin etendre
	
	if ($opac_show_suggest) {
		$bt_sugg = "&nbsp;&nbsp;&nbsp;<a href=# ";
		if ($opac_resa_popup) $bt_sugg .= " onClick=\"w=window.open('./do_resa.php?lvl=make_sugg&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;\"";
		else $bt_sugg .= "onClick=\"document.location='./do_resa.php?lvl=make_sugg&oresa=popup' \" ";			
		$bt_sugg.= " >".$msg[empr_bt_make_sugg]."</a>";
		print $bt_sugg;
	}
	
	$search_terms = unserialize(stripslashes($search_terms));
	
	print "<blockquote>";
	print aff_notice(-1);
	$nb=0;
	$recherche_ajax_mode=0;

	for ($i =0 ; $i<count($notices);$i++){
		if($i>4)$recherche_ajax_mode=1;
		print pmb_bidi(aff_notice($notices[$i], 0, 1, 0, "", "", 0, 0, $recherche_ajax_mode));
	}	
	
	print aff_notice(-2);
	print "</blockquote>";
	print "
	</div></div>";
	if($opac_allow_affiliate_search) print $catal_navbar;
	else print "</div>";
}else{
	if($tab == "affiliate"){
		//l'onglet source affiliées est actif, il faut son contenu...
		$as=new affiliate_search_keywords($user_query);
		$as->getResults();
		print $as->results;
	}
	print "
	</div>
	<div class='row'>&nbsp;</div>";
	//Enregistrement des stats
	if($pmb_logs_activate){
		global $nb_results_tab;
		$nb_results_tab['keyword_affiliate'] = $as->getTotalNbResults();
	}	
}
