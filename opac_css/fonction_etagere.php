<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: fonction_etagere.php,v 1.8 2006-12-29 16:42:17 touraine37 Exp $

$base_path=".";
require_once($base_path."/includes/includes_etagere.inc.php");

print $etageres_header;

// pour affichage de liens sur les �l�ments affich�s :
/*
$liens_opac['lien_rech_notice'] 		= "./index.php?lvl=notice_display&id=!!id!!";
$liens_opac['lien_rech_auteur'] 		= "./index.php?lvl=author_see&id=!!id!!";
$liens_opac['lien_rech_editeur'] 		= "./index.php?lvl=publisher_see&id=!!id!!";
$liens_opac['lien_rech_serie'] 		= "./index.php?lvl=serie_see&id=!!id!!";
$liens_opac['lien_rech_collection'] 		= "./index.php?lvl=coll_see&id=!!id!!";
$liens_opac['lien_rech_subcollection'] 		= "./index.php?lvl=subcoll_see&id=!!id!!";
$liens_opac['lien_rech_indexint'] 		= "./index.php?lvl=indexint_see&id=!!id!!";
$liens_opac['lien_rech_motcle'] 		= "./index.php?lvl=more_results&mode=keyword&user_query=!!mot!!";
$liens_opac['lien_rech_categ'] 			= "./index.php?lvl=categ_see&id=!!id!!";
$liens_opac['lien_rech_perio'] 			= "./index.php?lvl=notice_display&id=!!id!!";
$liens_opac['lien_rech_bulletin'] 		= "./index.php?lvl=bulletin_display&id=!!id!!";
*/

// param�tres :
//	$accueil : filtres les �tag�res de l'accueil uniquement si 1
//	$etageres : les num�ros des �tag�res s�par�s par les ',' toutes si vide
//	$commentaire : affiche ou non le commentaire
//	$aff_notices_nb : nombres de notices affich�es : toutes = 0 
//	$mode_aff_notice : mode d'affichage des notices, REDUIT (titre+auteur principal) ou ISBD ou PMB ou les deux : dans ce cas : (titre + auteur) en ent�te du truc, � faire dans notice_display.class.php
//	$depliable : affichage des notices une par ligne avec le bouton de d�pliable
//	$link_to_etagere : lien pour afficher le contenu de l'�tag�re
//	$htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="" : les id, class et zindex du <DIV > englobant le r�sultat de la fonction
//	$liens_opac : tableau contenant les url destinatrices des liens si voulu 
// function affiche_etagere($accueil=0, $etageres="", $aff_commentaire=0, $aff_notices_nb=0, $mode_aff_notice=AFF_ETA_NOTICES_BOTH, $depliable=AFF_ETA_NOTICES_DEPLIABLES_OUI, $link_to_etagere="", $htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="", $liens_opac=array() ) {

print pmb_bidi(affiche_etagere (0, "$id", 1, $opac_etagere_nbnotices_accueil=0, $opac_etagere_notices_format=AFF_ETA_NOTICES_BOTH, $opac_etagere_notices_depliables=AFF_ETA_NOTICES_DEPLIABLES_OUI, "./fonction_etagere.php?lvl=etagere_see&id=!!id!!" , $liens_opac)) ; 

print $etageres_footer;
