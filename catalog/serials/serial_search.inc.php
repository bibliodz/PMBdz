<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serial_search.inc.php,v 1.27 2013-12-11 15:49:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$include_path/templates/catalog.tpl.php");
require_once("$include_path/isbn.inc.php");
require_once("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once("$class_path/marc_table.class.php");
require_once("$class_path/serie.class.php");
require_once("$class_path/indexint.class.php");
require_once("$class_path/author.class.php");
require_once("$class_path/subcollection.class.php");
require_once("$class_path/collection.class.php");
require_once("$class_path/editor.class.php");
require_once("$class_path/category.class.php");
require_once("$class_path/notice.class.php");
require_once("$class_path/serial_display.class.php");
require_once("$class_path/mono_display.class.php");
require_once("$class_path/expl.class.php");
require_once("$class_path/explnum.class.php");
require_once("$class_path/emprunteur.class.php");
require_once("$include_path/fields_empr.inc.php");
require_once("$include_path/datatype.inc.php");
require_once("$include_path/parser.inc.php");
require_once("$include_path/notice_authors.inc.php");
require_once("$include_path/notice_categories.inc.php");
require_once("$include_path/explnum.inc.php") ;
require_once("$include_path/expl_info.inc.php") ;
require_once("$include_path/bull_info.inc.php") ;
require_once("$include_path/resa_func.inc.php") ;
require_once("$class_path/analyse_query.class.php");


//droits d'acces lecture notice
$acces_j='';
if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
	$dom_1= $ac->setDomain(1);
	$acces_j = $dom_1->getJoin($PMBuserid,4,'notice_id');
} 

// r�sultat de recherche pour gestion des p�riodiques
echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg["recherche"], $serial_header);

$base_url = "./catalog.php?categ=serials&sub=search&user_query=".rawurlencode(stripslashes($user_query));

print $serial_access_form;

// comptage du nombre de r�sultats
$where="";
if ($user_query) {
	$aq=new analyse_query(stripslashes($user_query));
	if ($aq->error) {
		error_message($msg["searcher_syntax_error"],sprintf($msg["searcher_syntax_error_desc"],$aq->current_car,$aq->input_html,$aq->error_message));
		exit();
	}
	$members=$aq->get_query_members("notices","index_wew","index_sew","notice_id");
	$where.=$members["where"]." and ";
} 
if ($issn_query) {
	if (strpos($issn_query,"*")===FALSE) {
		$code = $issn_query;
	} else {
		$code = str_replace("*","%",$issn_query);	
	}
	$t = array("-"," ",".");
	$code=str_replace($t,'%',$code);
	if ($code) {
		$members["where"]="(code like '$code')";
		$where.=$members["where"]." and ";
	}
}
$where.="niveau_biblio='s' AND niveau_hierar='1'";

$requete_count = "select count(distinct notice_id) from notices $acces_j where $where ";
$count_query = mysql_query($requete_count, $dbh); 
$nbr_lignes = mysql_result ($count_query, 0, 0);


print $message_search;

if (!$nbr_lignes) {
	print "<div class='row'>".$msg["serial_no_result"]."</div>";
} elseif ($nbr_lignes>0) {
	if (!$page) $page=1;
	$debut =($page-1)*$nb_per_page_a_search;
	// inclusion du javascript de gestion des listes d�pliables
	// d�but de liste
	print $begin_result_liste;
	
	$requete = "SELECT notice_id,tit1,ed1_id,".$members["select"]." as pert FROM notices $acces_j ";
	$requete.= "WHERE $where ";
	$requete.= "group by notice_id ORDER BY pert desc,index_sew LIMIT $debut,$nb_per_page_a_search";
	
	$myQuery=mysql_query($requete, $dbh);
	
	print "<div class='row'>";
	$recherche_ajax_mode=0;
	$nb=0;
	if($user_query && $issn_query){
		print "<b>${msg[233]}</b>&nbsp;".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)." et <b>${msg[165]}</b>&nbsp;".htmlentities(stripslashes($issn_query),ENT_QUOTES,$charset)." => ".$nbr_lignes." ".$msg["search_resultat"];
	} else {
		print "<b>${msg[233]}</b>&nbsp;".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)." => ".$nbr_lignes." ".$msg["search_resultat"];
	}
	
	while($perio=mysql_fetch_object($myQuery)) {
		if($nb++>5)$recherche_ajax_mode=1;
		$edPerio = "";
   		if($perio->ed1_id) {
	       	$editeur = new editeur($perio->ed1_id);
       		$edPerio = ' - '.$editeur->display;
		}
		$link_serial = './catalog.php?categ=serials&sub=view&serial_id=!!id!!';
		$link_analysis = './catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=!!bul_id!!&art_to_show=!!id!!';
		$link_bulletin = './catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=!!id!!';
		$link_explnum = "./catalog.php?categ=serials&sub=analysis&action=explnum_form&bul_id=!!bul_id!!&analysis_id=!!analysis_id!!&explnum_id=!!explnum_id!!";
		// function serial_display ($id, $level='1', $action_serial='', $action_analysis='', $action_bulletin='', $lien_suppr_cart="", $lien_explnum="", $bouton_explnum=1,$print=0,$show_explnum=1, $show_statut=0) 
		$serial = new serial_display($perio->notice_id, 6, $link_serial, $link_analysis, $link_bulletin, "", $link_explnum, 0, 0, 1, 1 ,true,0,$recherche_ajax_mode);
		print pmb_bidi($serial->result);
	}
	print '</div>';

	// affichage de la pagination
	print "<div class='row'><div align='center'>";
	$nav_bar = aff_pagination ($base_url, $nbr_lignes, $nb_per_page_a_search, $page, 10, false, true) ;
	print $nav_bar;
	print '</div></div>';
} else {
		// la recherche ne renvoit qu'un r�sultat -> on y va direct
		
		$requete = "SELECT notice_id FROM notices $acces_j WHERE $where limit 1";
		$myQuery = mysql_query($requete, $dbh);
		       		
		$perio=mysql_fetch_object($myQuery);
		show_serial_info($perio->notice_id, 0, 0);
}
