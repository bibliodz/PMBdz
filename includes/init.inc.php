<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: init.inc.php,v 1.29 2011-09-30 07:58:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// Cet include permet de r�duire consid�rablement les trucs � mettre au d�part d'un script
// Six param�tres � fournir en fixant les valeurs avant l'include de ce fichier
//	$base_path="../.."; par ex : = chemin pour acc�der � la racine de l'applic PMB
//	$base_auth = "SAUV_AUTH|ADMINISTRATION_AUTH"; les droits du user � tester
//	$base_title = "Titre de la fen�tre"; le titre de la page : facultatif
//		si besoin d'une variable : $base_title = "\$msg[28]";
//	$base_noheader = 0; par d�faut, pas obligatoire, si non vide : pas d'envoi du d�but de page (header & co)
//	$base_nocheck = 0; par d�faut, pas obligatoire : si non vide : pas de checkuser ( session, droits )
//	$base_nobody = 0; par d�faut, pas obligatoire : si non vide : pas de <body> apr�s le header envoy�
//  $base_nosession =0; par d�faut, pas obligatoire, si non vide pas d'envoi du cookie de session dans global_vars.inc.php
//
//	l'exemple ci-dessus correspond � l'inclusion dans le fichier : admin/sauvegarde/launch.php :
//		$base_path="../.."; 
//		$base_auth = "SAUV_AUTH|ADMINISTRATION_AUTH";
//		$base_title = "Lancement d'une sauvegarde"; 
//		require_once ("$base_path/includes/init.inc.php");
//	l'exemple ci-dessus correspond � l'inclusion dans le fichier : catalog/z3950/z_progession_main.php :
//		J'ai besoin du header mais pas du <body> � cause des frames
//		$base_path="../..";
//		$base_auth = "CIRCULATION_AUTH";  
//		$base_title = "";    
//		$base_nobody = 1;    
//		require_once ("$base_path/includes/init.inc.php");  

if (!$base_path) $base_path=".";

if (substr(phpversion(), 0, 1) == "5") @ini_set("zend.ze1_compatibility_mode", "1");
	
include_once ("$base_path/includes/error_report.inc.php") ;
//include_once ("$base_path/includes/global_vars.inc.php") ;
require_once ("$base_path/includes/config.inc.php");

// prevents direct script access
if(preg_match('/init\.inc\.php/', $REQUEST_URI)) {
	include('forbidden.inc.php'); forbidden();
	}

$include_path      = $base_path."/".$include_path; 
$class_path        = $base_path."/".$class_path;
$javascript_path   = $base_path."/".$javascript_path;
$styles_path       = $base_path."/".$styles_path;

require_once("$class_path/XMLlist.class.php");

// fichier de d�f. pour gestion des erreurs
require_once("$include_path/error_handler.inc.php");

require_once("$include_path/db_param.inc.php");

if ($_tableau_databases[1] && $base_title) {
	// multi-databases
	$database_window_title=$_libelle_databases[array_search(LOCATION,$_tableau_databases)].": ";
	} else $database_window_title="" ; 

require_once("$include_path/mysql_connect.inc.php");
$dbh = connection_mysql();

require_once("$include_path/sessions.inc.php");
require_once("$include_path/misc.inc.php");
require_once("$javascript_path/misc.inc.php");
require_once("$include_path/user_error.inc.php");

// classe de gestion de l'audit des objets
require_once("$class_path/audit.class.php");

include("$include_path/start.inc.php");

require_once("$include_path/clean_pret_temp.inc.php");
if (!$clean_pret_tmp) clean_pret_temp();

if ($base_auth) eval("\$auth=".$base_auth.";"); 
	else $auth="";
if (!$base_nocheck) {
	if(!checkUser('PhpMyBibli', $auth)) {
		// localisation (fichier XML) (valeur par d�faut)
		$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
 		$messages->analyser();
		$msg = $messages->table;
		include("$include_path/templates/common.tpl.php");
 		header ("Content-Type: text/html; charset=$charset");
		print $std_header;
		print "<body class='$current_module claro' id='body_current_module' page_name='$current_module'>";
		require_once("$include_path/user_error.inc.php");
		switch ($checkuser_type_erreur) {
			case CHECK_USER_NO_SESSION :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_no_session], 1, './index.php',basename($_SERVER['REQUEST_URI']))."</div>";
				break;
			case CHECK_USER_SESSION_DEPASSEE :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_session_depassee], 1, './index.php', basename($_SERVER['REQUEST_URI']))."</div>";
				break;
			case CHECK_USER_SESSION_INVALIDE :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_session_invalide], 1, './index.php', basename($_SERVER['REQUEST_URI']))."</div>";
				break;
			case CHECK_USER_AUCUN_DROIT :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_aucun_droit], 1)."</div>";
				break;
			case CHECK_USER_PB_ENREG_SESSION :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_pb_enreg_session], 1, './index.php')."</div>";
				break;
			case CHECK_USER_PB_OUVERTURE_SESSION :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[checkuser_pb_ouverture_session], 1, './index.php')."</div>";
				break;
			default :
				print "<div id='login-box'>".return_error_message($msg[11], $msg[12], 1)."</div>";
				break;
			}
		print $footer;
		exit;
	}
	
	if(SESSlang) {
		$lang=SESSlang;
		$helpdir = $lang;
	}

	if (!$pmb_indexation_lang) $pmb_indexation_lang = $lang; 

	// localisation (fichier XML)
	$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
 	$messages->analyser();
	$msg = $messages->table;
	require("$include_path/templates/common.tpl.php");  
	
	//
	$champs_base=array();
	}

if (!$base_noheader) {
 	header ("Content-Type: text/html; charset=$charset");
	print $std_header;
	if (!$base_nobody) print "<body class='$current_module claro' id='body_current_module' page_name='$current_module'>";
	if ($base_title) {
		eval ("\$base_title_temp=\"".$database_window_title.$base_title."\";") ;
		echo window_title($base_title_temp);
		}
	}

// Param�trage de la RFID, en fonction �ventuellement de la localisation
require_once($class_path."/parameters_subst.class.php");
$parameter_subst = new parameters_subst($include_path."/parameters_subst/rfid_per_localisations.xml", $deflt2docs_location);
$parameter_subst->extract();

// Activation RFID selon les prefs user
if($pmb_rfid_activate)	$pmb_rfid_activate=$param_rfid_activate;
// Pr�paration des js sripts pour la RFID
if($pmb_rfid_activate) {	
	require_once($include_path."/rfid_config.inc.php");
	get_rfid_js_header();
}	