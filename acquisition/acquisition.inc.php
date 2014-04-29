<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: acquisition.inc.php,v 1.13 2011-06-06 08:04:28 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/entites.class.php");
require_once("$class_path/paiements.class.php");
require_once("$class_path/frais.class.php");
require_once("$class_path/types_produits.class.php");
require_once("$class_path/offres_remises.class.php");
require_once("$class_path/tva_achats.class.php");

//Recherche des etablissements auxquels a acces l'utilisateur
$q = entites::list_biblio(SESSuserid);
$list_bib = mysql_query($q,$dbh);
$nb_bib=mysql_num_rows($list_bib);
$tab_bib=array();
while ($row=mysql_fetch_object($list_bib)) {
	$tab_bib[0][]=$row->id_entite;
	$tab_bib[1][]=$row->raison_sociale;
}		

echo window_title($database_window_title.$msg[acquisition_menu].$msg[1003].$msg[1001]);

switch($categ) {
	case 'ach':
		if(!$nb_bib) {
			//Pas de biblioth�ques d�finies pour l'utilisateur
			$error_msg.= htmlentities($msg["acquisition_err_coord"],ENT_QUOTES, $charset)."<div class='row'></div>";	
			error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
			die;
		}

		//Gestion de la tva
		if ($acquisition_gestion_tva) {
			$nbr = tva_achats::countTva();
			
			//Gestion de TVA et pas de taux de tva d�finis
			if (!$nbr) {
				$error_msg.= htmlentities($msg["acquisition_err_tva"],ENT_QUOTES, $charset)."<div class='row'></div>";	
				error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
				die;
			}
		}
		include_once('./acquisition/achats/achats.inc.php');
		break;

	case 'sug':
		
		switch($sub) {			
			case 'multi':
				include_once('./acquisition/suggestions/suggestions_multi.inc.php');
			break;
			case 'import':
				include_once('./acquisition/suggestions/suggestions_import.inc.php');
			break;
			case 'export':
				include_once('./acquisition/suggestions/suggestions_export.inc.php');
			break;
			case 'empr_sug':
				include_once('./acquisition/suggestions/suggestions_empr.inc.php');
			break;
			default:
				include_once('./acquisition/suggestions/suggestions.inc.php');
			break;
		}		
	break;

	default:
		if (!$nb_bib && !$acquisition_sugg_to_cde) {
			include_once('./acquisition/suggestions/suggestions.inc.php');
		} else {
			if(!$nb_bib) {
				//Pas de biblioth�ques d�finies pour l'utilisateur
				$error_msg.= htmlentities($msg["acquisition_err_coord"],ENT_QUOTES, $charset)."<div class='row'></div>";	
				error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
				die;
			}
			
			//Gestion de la tva
			if ($acquisition_gestion_tva) {
				$nbr = tva_achats::countTva();
				//Gestion de TVA et pas de taux de tva d�finis
				if (!$nbr) {
					$error_msg.= htmlentities($msg["acquisition_err_tva"],ENT_QUOTES, $charset)."<div class='row'></div>";	
					error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
					die;
				}
			}
			include_once('./acquisition/achats/achats.inc.php');
		}
		break;
}