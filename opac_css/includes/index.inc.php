<?php 
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index.inc.php,v 1.46 2014-03-12 14:41:30 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// affichage recherche
require_once ($base_path.'/includes/simple_search.inc.php');

if ($search_type == "simple_search" && $opac_show_infopages_id_top) {
	// affichage des infopages demand�s juste AVANT le formulaire de recherche simple et si !$user_query
	require_once ($base_path.'/includes/show_infopages.inc.php');
	print "<div id='infopages_top'>".show_infopages($opac_show_infopages_id_top)."</div>";
	}

if ($opac_show_search_title) print "<div id='search_block'><h3><span>".$msg['search_block_title']."</span></h3>";
$simple_search_content=simple_search_content($user_query, $css);
$simple_search_content=str_replace("!!surligne!!","",$simple_search_content);
print pmb_bidi(str_replace('!!user_query!!', $user_query, $simple_search_content));
if ($opac_show_search_title) print "</div>";

if ($search_type == "simple_search") {
	// affichage des infopages demand�s juste apr�s le formulaire de recherche simple et si !$user_query
	if ($opac_show_infopages_id) {
		require_once ($base_path.'/includes/show_infopages.inc.php');
		print "<div id='infopages'>".show_infopages($opac_show_infopages_id)."</div>";
	}
	// affichage des du navigateur de p�riodiques
	if ($opac_show_perio_browser) {
		require_once($base_path."/classes/perio_a2z.class.php");	
		$a2z=new perio_a2z(0,$opac_perio_a2z_abc_search,$opac_perio_a2z_max_per_onglet);		
		print $perio_a2z=$a2z->get_form();		
	}	
	// affichage cat�gories
	if ($opac_show_categ_browser) {
		$opac_show_categ_browser_tab=explode(" ",$opac_show_categ_browser);
		if ($opac_show_categ_browser_tab[1]) 
			$opac_show_categ_browser_home_id_thes=$opac_show_categ_browser_tab[1];
		require_once ($base_path.'/classes/categorie.class.php');
		require_once ($base_path.'/includes/templates/categories.tpl.php');
		require_once ($base_path.'/categ/categories.inc.php');
	}
	// affichage des bannettes auxquelles le lecteur est abonn�
	if ($opac_show_subscribed_bannettes) {
		require_once($base_path."/includes/bannette_func.inc.php");		
		$affiche_bannette_tpl="
		<div class='bannette' id='banette_!!id_bannette!!'>
			!!diffusion!!
		</div>
		";
		$aff = pmb_bidi(affiche_bannette ("", $opac_bannette_nb_liste, $opac_bannette_notices_format, $opac_bannette_notices_depliables, "./empr.php?lvl=bannette&id_bannette=!!id_bannette!!", $liens_opac ,$date_diff,"bannettes_private-container_2", "" , "")) ;		
		if($aff){
			$bannettes= "<div id='bannettes_subscribed'>\n";
			$bannettes.= "<h3><span>".$msg['accueil_bannette_privee']."</span></h3>";
			$bannettes.= "<div id='bannettes_private-container'>";
			$bannettes.= $aff;
			$bannettes.= "</div><!-- fermeture #bannettes-container -->\n";
			$bannettes.= "</div><!-- fermeture #bannettes -->\n";
			print $bannettes;
		} 		
	}
	
	//affichage des bannettes publiques s�lectionn�es (et restantes si $opac_show_subscribed_bannettes est activ�) pour la page d'accueil	
 	if ($opac_show_public_bannettes) {
 		require_once($base_path."/includes/bannette_func.inc.php");
 		$affiche_bannette_tpl="
		<div class='bannette' id='banette_!!id_bannette!!'>
 			!!diffusion!!
 		</div>";
 		$aff = pmb_bidi(affiche_bannette ("", $opac_bannette_nb_liste, $opac_bannette_notices_format, $opac_bannette_notices_depliables, "./index.php?lvl=bannette_see&id_bannette=!!id_bannette!!", $liens_opac ,$date_diff,"bannettes-public-container_2", "" , "",true)) ;
 		if($aff){
 			$bannettes= "<div id='bannettes_public'>\n";
 			$bannettes.= "<h3><span>".$msg['accueil_bannette_public']."</span></h3>";
 			$bannettes.= "<div id='bannettes_public-container'>";
 			$bannettes.= $aff;
 			$bannettes.= "</div><!-- fermeture #bannettes-container -->\n";
			$bannettes.= "</div><!-- fermeture #bannettes -->\n";
 			print $bannettes;
 		}
 	}

	if ($opac_show_section_browser==1) {
		if ($opac_sur_location_activate==1) require_once($base_path."/includes/enter_sur_location.inc.php");
		else require_once($base_path."/includes/enter_localisation.inc.php");
	}
	// affichage marguerite des couleurs
	if ($opac_show_marguerite_browser) require_once ($base_path.'/indexint/marguerite_browser.inc.php');

	// affichage tableau des 100 cases du savoir
	if ($opac_show_100cases_browser) require_once ($base_path.'/indexint/100cases_browser.inc.php');

	// affichage derniers ouvrages saisis
	if ($opac_show_dernieresnotices) {
		require_once ($base_path.'/includes/templates/last_records.tpl.php');
		require_once ($base_path.'/includes/last_records.inc.php');
	}

	// affichage des �tag�res de l'accueil
	if ($opac_show_etageresaccueil) {
		require_once ($base_path.'/includes/templates/etagere.tpl.php');
		$aff_etagere = affiche_etagere(1, "", 1, $opac_etagere_nbnotices_accueil, $opac_etagere_notices_format, $opac_etagere_notices_depliables, "./index.php?lvl=etagere_see&id=!!id!!", $liens_opac);
		if ($aff_etagere) {
			print $etageres_header;
			print $aff_etagere ;
			print $etageres_footer;
		}
	}

	// affichage des flux rss
	if ($opac_show_rss_browser) require_once ($base_path.'/includes/rss.inc.php');

	//define( 'AFF_ETA_NOTICES_NON', 0 );
	//define( 'AFF_ETA_NOTICES_ISBD', 1 );
	//define( 'AFF_ETA_NOTICES_PMB', 2 );
	//define( 'AFF_ETA_NOTICES_BOTH', 4 );
	//define( 'AFF_ETA_NOTICES_REDUIT', 8 );
	//define( 'AFF_ETA_NOTICES_DEPLIABLES_NON', 0 );
	//define( 'AFF_ETA_NOTICES_DEPLIABLES_OUI', 1 );
	// param�tres :
	//	$accueil : filtres les �tag�res de l'accueil uniquement si 1
	//	$etageres : les num�ros des �tag�res s�par�s par les ',' toutes si vides
	//	$aff_commentaire : affichage du commentaire associ� � l'�tag�re
	//	$aff_notices_nb : nombres de notices affich�es : toutes = 0 
	//	$mode_aff_notice : mode d'affichage des notices, REDUIT (titre+auteur principal) ou ISBD ou PMB ou les deux : dans ce cas : (titre + auteur) en ent�te du truc, � faire dans notice_display.class.php
	//	$depliable : affichage des notices une par ligne avec le bouton de d�pliable
	//	$htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="" : les id, class et zindex du <DIV > englobant le r�sultat de la fonction 
	//function affiche_etagere($accueil=0, $etageres="", $aff_commentaire=0, $aff_notices_nb=0, $mode_aff_notice=AFF_ETA_NOTICES_BOTH, $depliable=AFF_ETA_NOTICES_DEPLIABLES_OUI, $htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="" ) {

}
