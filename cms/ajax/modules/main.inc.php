<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.20 2012-11-15 09:19:03 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/autoloader.class.php");
$autoloader = new autoloader();
$autoloader->add_register("cms_modules",true);

//si l'id n'est pas pass� en GET, on r�cup�re le hidden qui se balade dans les posts...
if(!$id){
	$id = $cms_module_common_module_id;
}
switch($action){
	case "save_form" :
		$element = new $elem($id);
		$cms_build_info = unserialize(rawurldecode(stripslashes($cms_build_info)));
		$element->set_cms_build_env($cms_build_info);				
		$response = $element->save_form();
		break;
	case "delete" :
		$element = new $elem($id);
		$response = $element->delete();
		break;	
	case "cadres_list_in_page" :
		$cms= new cms_build();
		$response=$cms->build_cadres_list_in_page($in_page);		
		break;
	case "cadres_list_not_in_page" :
		$cms= new cms_build();
		$response=$cms->build_cadres_list_not_in_page($in_page);		
		break;
	case "cadre_save_classement" :
		$cms= new cms_build();
		$response=$cms->save_cadre_classement($id_cadre,$classement);		
		break;
	case "get_env":
		$element = new $elem();
		$response = $element->get_page_env_select($pageid,$name,$var);
		break;
	case "ajax" :
		$element = new $elem($id);
		$response = $element->execute_ajax();
		ajax_http_send_response($response['content'],$response['content-type']);
		break;	
	case "get_form" :
	default :
		if(!$cancel_callback) $cancel_callback = "";
		$element = new $elem($id);
		if($cms_module_class){
			$element->set_module_class_name($cms_module_class);
		}
		$element->set_cms_build_env(restore_cms_env($cms_build_info));
		$response = $element->get_form(true,$callback,$cancel_callback,$delete_callback);
		break;
}

if($action!="ajax"){
	ajax_http_send_response($response);
}

function restore_cms_env($infos){
	global $cms_build_info;
	$cms_build_info = unserialize(stripslashes($infos));
	return $cms_build_info;
}
