<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: perio_a2z.inc.php,v 1.7 2014-03-14 11:10:36 arenou Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
                    
require_once($base_path."/classes/perio_a2z.class.php");

switch($sub){
	case 'get_onglet':
		$a2z=new perio_a2z(0,$opac_perio_a2z_abc_search,$opac_perio_a2z_max_per_onglet);
		ajax_http_send_response( $a2z->get_onglet($onglet_sel) );
	break;
	case 'get_perio':	
		$a2z=new perio_a2z($id,$opac_perio_a2z_abc_search,$opac_perio_a2z_max_per_onglet);	
		ajax_http_send_response($a2z->get_perio($id) );
	break;
	case 'reload':	
		$a2z=new perio_a2z(0,$opac_perio_a2z_abc_search,$opac_perio_a2z_max_per_onglet);	
		ajax_http_send_response( $a2z->get_form(0,0,1) );
	break;
}

?>