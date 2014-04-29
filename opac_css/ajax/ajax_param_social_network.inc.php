
<?php
/*
 * abacarisse
 */
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

switch($sub){
	case 'get':
		if ($opac_param_social_network=='') {
			ajax_http_send_response("0");
			exit;
		}else{
			ajax_http_send_response($opac_param_social_network);
		}
		break;
}
?>