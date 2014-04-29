<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: abts_retard.inc.php,v 1.1 2011-06-27 15:26:59 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/abts_pointage.class.php");

$abts= new abts_pointage();

switch($action) {
	case 'relance':
		$abts->relance_retard();
		echo $abts->get_form_retard();
	break;
	case 'comment_gestion':
		$abts->set_comment_retard(1);
		echo $abts->get_form_retard();
	break;	
	case 'comment_opac':
		$abts->set_comment_retard(0);
		echo $abts->get_form_retard();
	break;
	default:
		echo $abts->get_form_retard();
	break;
}
?>