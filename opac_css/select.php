<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: select.php,v 1.6 2013-02-05 08:17:54 dbellamy Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";

require_once($base_path."/includes/init.inc.php");
require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path."/includes/rec_history.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');
	
// r�cup�ration param�tres MySQL et connection � la base
require_once($base_path.'/includes/opac_db_param.inc.php');
require_once($base_path.'/includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

require_once($base_path."/includes/misc.inc.php");

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
require_once($base_path."/includes/session.inc.php");
require_once($base_path.'/includes/start.inc.php');

require_once($base_path."/includes/check_session_time.inc.php");

// r�cup�ration localisation
require_once($base_path.'/includes/localisation.inc.php');

// version actuelle de l'opac
require_once($base_path.'/includes/opac_version.inc.php');

// fonctions de gestion de formulaire
require_once($base_path.'/includes/javascript/form.inc.php');

require_once($base_path.'/includes/templates/common.tpl.php');
require_once($base_path.'/includes/divers.inc.php');
// classe d'affichage des tags
require_once($base_path.'/classes/tags.class.php');

require_once($base_path.'/includes/marc_tables/'.$pmb_indexation_lang.'/empty_words');

print $popup_header;

// si param�trage authentification particuli�re et pour la re-authentification ntlm
if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');

print "<script type='text/javascript'>
	self.focus();
</script>";

switch($what) {

	case 'calendrier':
		require_once('./selectors/calendrier.inc.php');
		break;

	case 'indexint':
		require_once('./selectors/indexint.inc.php');
		break;
		
	default:
		print "<script type='text/javascript'>
			window.close();
			</script>";
		break;
}

print $popup_footer;

mysql_close($dbh);
