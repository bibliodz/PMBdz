<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: config.inc.php,v 1.196 2014-03-19 09:39:24 touraine37 Exp $

// fichier de configuration g�n�rale

$pmb_version = "</b>4.1.RC1</b>";
$pmb_version_brut = "4.1.RC1";
$pmb_version_database_as_it_should_be = "v5.14";
$pmb_subversion_database_as_it_shouldbe = "0";

$pmb_version_web = "http://www.sigb.net/config.inc.php" ;

// permet de transcrire les caract�res du cp1252 en 8x et 9x ... (d�sactiv� par d�faut)
//$pmb_cp1252_normalize = 1;

// prevents direct script access
if(isset($HTTP_SERVER_VARS) && strpos($HTTP_SERVER_VARS['PHP_SELF'],'config.inc.php')) {
	echo $pmb_version_brut ;
	exit ;
}

$default_lang = 'fr_FR';
$default_helpdir = $default_lang;
// Character set = encodage des donn�es. Attention ne pas modifier en cours d'utilisation, votre base de donn�es serait pleine de caracteres bizarre !!!
$charset= 'iso-8859-1';

// feuille de style � utiliser
$stylesheet = 'enjoy';

// utilisation des raccourcis clavier (0=non ; 1=oui)
$use_shortcuts = 1;

// taille des fen�tres de selecteurs
$selector_x_size = 400; 	# largeur
$selector_y_size = 400;		# hauteur

// niveau du fichier de log :
// stable=erreurs utilisateur seulement
// unstable=toutes erreurs
// off=pas de gestion des erreurs

$loglevel = 'off';

// fichier de log
$logfile = './journal.log';

// flags pour la gestion des droits utilisateurs
define('CIRCULATION_AUTH'		,    1);
define('CATALOGAGE_AUTH'		,    2);
define('AUTORITES_AUTH'			,    4);
define('ADMINISTRATION_AUTH'	,    8);
define('EDIT_AUTH'				,   16);
define('SAUV_AUTH'				,   32);
define('DSI_AUTH'				,   64);
define('PREF_AUTH'				,  128);
define('ACQUISITION_AUTH'		,  256);
define('RESTRICTCIRC_AUTH'		,  512);
define('RESTRICTCATAL_AUTH'		, 1024);
define('THESAURUS_AUTH'			, 2048);
define('TRANSFERTS_AUTH'		, 4096);
define('EXTENSIONS_AUTH'		, 8192);
define('DEMANDES_AUTH'			, 16384);
define('FICHES_AUTH'			, 32768);
define('CMS_AUTH'				, 65536);
define('EDIT_FORCING_AUTH'		,131072);

// dur�e des sessions
define('SESSION_REACTIVATE', 7200); // refresh max = 120 minutes
define('SESSION_MAXTIME', 86400);	// dur�e de vie maximum d'une session = 24h

// d�finition des p�riodicit�s de p�rio
define('ABT_PERIODICITE_JOUR'		,    1);

// d�finition des types d'audit
define('AUDIT_NOTICE'	,    1);
define('AUDIT_EXPL'		,    2);
define('AUDIT_BULLETIN'	,    3);
define('AUDIT_ACQUIS'	,    4);
define('AUDIT_PRET'		,    5);
define('AUDIT_AUTHOR'	,    6);
define('AUDIT_COLLECTION',   7);
define('AUDIT_SUB_COLLECTION',8);
define('AUDIT_INDEXINT'	,    9);
define('AUDIT_PUBLISHER',    10);
define('AUDIT_SERIE'	,    11);
define('AUDIT_CATEG'	,    12);
define('AUDIT_TITRE_UNIFORME',13);

/* la langue est fix�e sur la valeur par d�faut pour l'instant */
$lang= $default_lang;
$helpdir = $lang;

/* r�pertoire o� sont stock�es les sauvegardes (dans le r�p 'admin/backup') */
$backup_dir = "backups";

// est stock�e en base mais par d�faut, si vide ...
if (!isset($pmb_opac_url)) $pmb_opac_url = "./opac_css/";
	
/* Nbre d'enregistrements affich�s par page */
/* autorit�s */                  /* each was 10 */
$nb_per_page_author = 20 ;
$nb_per_page_publisher = 20 ;
$nb_per_page_collection = 20 ;
$nb_per_page_subcollection = 20 ;
$nb_per_page_serie = 20 ;

/* recherches */
/* author */
$nb_per_page_a_search = 10 ; /* was 3 */
/* publisher */
$nb_per_page_p_search = 10 ; /* was 4 */
/* subject */
$nb_per_page_s_search = 10 ; /* was 4 */

/* lecteur */
$nb_per_page_empr = 10 ; /* was 4 */

/* selectors */
/* author */
$nb_per_page_a_select = 10 ; /* was 10 */
/* collection */
$nb_per_page_c_select = 10 ; /* was 10 */
/* sub-collection */
$nb_per_page_sc_select = 10 ; /* was 10 */
/* publisher */
$nb_per_page_p_select = 10 ; /* was 10 */
/* serie */
$nb_per_page_s_select = 10 ; /* was 10 */
/* groups */
$nb_per_page_group = 10; /* is 10 */

$include_path      = 'includes';               // includes
$class_path        = 'classes';                // classes
$javascript_path   = 'javascript';             // scripts
$styles_path       = 'styles';                 // styles

// alertes sonores, en tableau pour pouvoir en mettre plusieurs dans le futur :
$alertsound["critique"]="<embed src='sounds/boing.wav' autostart='true' loop='false' hidden='true' width='0' height='0'>";
$alertsound["information"]="<embed src='sounds/waou.wav' autostart='true' loop='false' hidden='true' width='0' height='0'>";
$alertsound["question"]="<embed src='sounds/boing.wav' autostart='true' loop='false' hidden='true' width='0' height='0'>";
$alertsound["application"]="<embed src='sounds/boing.wav' autostart='true' loop='false' hidden='true' width='0' height='0'>";
$param_sounds = 1 ;

$homepage = 'http://www.sigb.net/';

@include_once("includes/config_local.inc.php") ;
@include_once("config_local.inc.php") ;

@include_once("includes/global_vars.inc.php") ;
@include_once("global_vars.inc.php") ;