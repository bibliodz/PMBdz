<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facette.inc.php,v 1.4 2014-03-07 15:32:27 abacarisse Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/facette_search.class.php');

switch($sub){
	case 'call_facettes':
		session_write_close();
		
		if($opac_facettes_ajax){
			$tab_result=$_SESSION['tab_result'];
			$str .= facettes::make_ajax_facette($tab_result);
			ajax_http_send_response($str);
		}
		
		break;
	case 'see_more':		
		$facette = new facettes();
		$sended_datas=utf8_encode($sended_datas);
		$sended_datas=pmb_utf8_array_decode(json_decode(stripslashes($sended_datas),true));
		ajax_http_send_response($facette->see_more($sended_datas['json_facette_plus']));
	
	break;
}
