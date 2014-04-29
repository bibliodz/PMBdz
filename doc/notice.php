<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice.php,v 1.4 2011-09-30 08:06:12 dgoron Exp $

// Ce fichier, copi� � la racine de l'opac montre l'affichage 
// d'une notice en dehors de l'OPAC, inclus dans une de vos page web quelconque. 

$base_path=".";
require_once($base_path."/includes/init.inc.php");

include($base_path."/includes/error_report.inc.php") ;

include($base_path."/includes/global_vars.inc.php");
require_once($base_path."/includes/opac_config.inc.php");
	
// r�cup�ration param�tres MySQL et connection � la base
require_once($base_path."/includes/opac_db_param.inc.php");
require_once($base_path."/includes/opac_mysql_connect.inc.php");
$dbh = connection_mysql();

include($base_path."/includes/misc.inc.php");

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
include($base_path."/includes/session.inc.php");

require_once($base_path."/includes/start.inc.php");
require_once($base_path."/includes/opac_config.inc.php");
require_once($base_path."/includes/check_session_time.inc.php");

// r�cup�ration localisation
require_once($base_path."/includes/localisation.inc.php");

// version actuelle de l'opac
require_once($base_path."/includes/opac_version.inc.php");


// fonctions de gestion de formulaire
require_once($base_path."/includes/javascript/form.inc.php");

require_once($base_path."/includes/templates/common.tpl.php");
require_once($base_path."/includes/divers.inc.php");
// classe de gestion des cat�gories
require_once($base_path."/classes/categorie.class.php");
require_once($base_path."/classes/notice.class.php");
require_once($base_path."/classes/notice_display.class.php");

// classe indexation interne
require_once($base_path."/classes/indexint.class.php");

require_once($base_path."/includes/marc_tables/".$pmb_indexation_lang."/empty_words");

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/common.tpl.php");
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/list_notices.inc.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/notice_authors.inc.php");
require_once($base_path."/includes/notice_categories.inc.php");

require_once($base_path."/includes/notice_affichage.inc.php");

// les nouveaux affichages
require_once($base_path."/includes/isbn.inc.php");
require_once($base_path."/classes/notice_affichage.class.php");
require_once($base_path.'/includes/templates/notice_display.tpl.php');
require_once($base_path.'/includes/explnum.inc.php');


$notice = new notice_affichage(32, $liens_opac) ;
$notice->do_header();
$notice->do_isbd();
$notice->do_public();
$notice->genere_double($depliable, 'PUBLIC') ;
print $notice->result ;
			