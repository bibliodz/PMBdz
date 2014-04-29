<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_diff_ajax.inc.php,v 1.3 2012-03-12 10:26:49 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/serialcirc_diff.class.php");

switch($sub){		
	case 'option_form':
		$serialcirc_diff=new serialcirc_diff($id_serialcirc,$num_abt);
		ajax_http_send_response($serialcirc_diff->option_form()); 
	break;		
	case 'ficheformat_form':
		$serialcirc_diff=new serialcirc_diff($id_serialcirc,$num_abt);
		ajax_http_send_response($serialcirc_diff->ficheformat_form()); 
	break;	
	case 'empr_form':
		$serialcirc_diff=new serialcirc_diff($id_serialcirc,$num_abt);
		ajax_http_send_response($serialcirc_diff->empr_form($id_diff)); 
	break;	
	case 'group_form':
		$serialcirc_diff=new serialcirc_diff($id_serialcirc,$num_abt);
		ajax_http_send_response($serialcirc_diff->group_form($id_diff)); 
	break;	
	case 'up_order_circdiff':	
		serialcirc_diff::up_order_circdiff($tablo);	
	break;	
	case 'up_order_circdiffprint':	
		serialcirc_diff::up_order_circdiffprint($id_serialcirc,$tablo);	
	break;	
	case 'up_order_circdiffgroupdrop':	
		serialcirc_diff::up_order_circdiffgroupdrop($tablo);	
	break;		
	case 'get_caddie':	
		ajax_http_send_response(serialcirc_diff::get_caddie($id_caddie));	
	break;		
}



