<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: build.inc.php,v 1.1 2012-01-25 15:20:35 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/harvest.class.php");


switch($action) {
	case 'form':
		$harvest=new harvest($id_harvest);
		print $harvest->get_form();
	break;
	case 'save':
		$harvest=new harvest($id_harvest);
		$data['name']=$name;
		print $harvest->save($data);
		$harvests=new harvests();
		print $harvests->get_list();
	break;	
	case 'delete':
		$harvest=new harvest($id_harvest);
		print $harvest->delete();
		$harvests=new harvests();
		print $harvests->get_list();
	break;		
	case 'test':
		$harvest=new harvest($id_harvest);
		print $harvest->havest_notice();
	break;
	default:
		$harvests=new harvests();
		print $harvests->get_list();
	break;
}
