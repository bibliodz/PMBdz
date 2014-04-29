<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.2 2014-02-24 14:52:17 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/cms/cms_document.class.php");

$id+=0;
$document = new cms_document($id);
switch($action){
	case "get_form" :
		$response['content'] = $document->get_form();
		break;
	case "save_form" :
		$response['content'] = $document->save_form();
		break;
	case "delete" :
		$response['content'] = $document->delete();
		break;
	case "delete_use" :
		$response['content'] = $document->delete_use();
		break;		
	case "thumbnail" :
		$document->render_thumbnail();
		break;
	case "render" :
		$document->render_doc();
		break;
}

if($response['content']){
	if(!$response['content-type'])$response['content-type'] = "text/html";
	ajax_http_send_response($response['content'],$response['content-type']);
}