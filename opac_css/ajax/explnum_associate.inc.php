<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: explnum_associate.inc.php,v 1.2 2014-02-25 15:05:18 apetithomme Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/explnum_associate_svg.class.php');

switch ($sub) {
	case 'get_associate_svg':
		get_associate_svg($explnum_id);
		break;
	case 'get_associate_js':
		get_associate_js($explnum_id);
		break;
}

function get_associate_svg($explnum_id) {
	$explnum_associate_svg = new explnum_associate_svg($explnum_id);
	$svg = $explnum_associate_svg->getSvg(false);
	ajax_http_send_response($svg,"text/xml");
}

function get_associate_js($explnum_id) {
	$explnum_associate_svg = new explnum_associate_svg($explnum_id);
	$js = $explnum_associate_svg->getJs(false);
	ajax_http_send_response($js,"text/xml");
}
?>