<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc.inc.php,v 1.2 2011-12-05 15:17:34 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/serialcirc.class.php");

$serialcirc=new serialcirc($location_id);

switch($sub){	
	// Zone de pointage
	case 'cb_enter':
		print $serialcirc->gen_circ_cb($cb); 
	break;	
	case 'print_diff':
		$cb_list[]=$cb;
		print $serialcirc->print_diff_list($cb_list); 
	
	break;
	case 'del_circ':
	
	break;		
	
	// Zone de liste
	case 'print_diff_list':
		print $serialcirc->print_diff_list($cb_list); 
	break;		
	
	default :
		print $serialcirc->gen_circ_form(); 
	break;		
	
}



