<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_tpl.inc.php,v 1.2 2013-10-07 07:08:26 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/notice_tpl.class.php");

$notice_tpl=new notice_tpl($id);

switch ($action) {
	case "edit": 
		print $notice_tpl->show_form();
	break;
	case "update": 
		$notice_tpl->update_from_form();	
		print $notice_tpl->show_list();	
	break;	
	case "delete": 
		$notice_tpl->delete();
		print $notice_tpl->show_list();
	break;
	case "eval": 
		print $notice_tpl->show_eval();	
	break;
	case 'duplicate':
		$notice_tpl->id = 0;
		print $notice_tpl->show_form();
		break;
	default:
		print $notice_tpl->show_list();
	break;	
}

?>
