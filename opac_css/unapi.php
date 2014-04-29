<?php
// +--------------------------------------------------------------------------+
// | PMB est sous licence GPL, la r�utilisation du code est cadr�e            |
// +--------------------------------------------------------------------------+
// $Id: unapi.php,v 1.3 2013-02-05 08:17:54 dbellamy Exp $

$base_path=".";
require_once($base_path."/includes/init.inc.php");
require_once("./includes/error_report.inc.php") ;
require_once("./includes/global_vars.inc.php");
require_once('./includes/opac_config.inc.php');
	
// r�cup�ration param�tres MySQL et connection � la base
require_once('./includes/opac_db_param.inc.php');
require_once('./includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();
// (si la connection est impossible, le script die ici).

require_once("./includes/misc.inc.php");

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
require_once("./includes/session.inc.php");
require_once('./includes/start.inc.php');
require_once("./includes/check_session_time.inc.php");

// r�cup�ration localisation
require_once('./includes/localisation.inc.php');

// version actuelle de l'opac
require_once('./includes/opac_version.inc.php');

// fonctions de gestion de formulaire
require_once('./includes/javascript/form.inc.php');
require_once('./includes/templates/common.tpl.php');
require_once('./includes/divers.inc.php');
require_once('./includes/notice_categories.inc.php');

// classe de gestion des cat�gories
require_once($base_path.'/classes/categorie.class.php');
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/notice_display.class.php');

// classe indexation interne
require_once($base_path.'/classes/indexint.class.php');

// classe d'affichage des tags
require_once($base_path.'/classes/tags.class.php');

require_once($base_path."/includes/marc_tables/".$pmb_indexation_lang."/empty_words");

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/common.tpl.php");
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/notice_authors.inc.php");
require_once($base_path."/includes/notice_categories.inc.php");
require_once($base_path."/includes/explnum.inc.php");

require_once('./classes/notice_affichage.class.php');
require_once('./classes/notice_affichage.ext.class.php');

require_once($include_path."/mail.inc.php") ;

// pour export
require_once("$base_path/admin/convert/start_export.class.php");
require_once ($include_path."/export_notices.inc.php");

require_once($class_path."/unapi.class.php");

// si param�trage authentification particuli�re et pour la re-authentification ntlm
if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');

$unapi = new unapi($format,$id);
?>