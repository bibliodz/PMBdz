<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: openurl_transport.class.php,v 1.1 2011-08-02 12:36:00 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/openurl/openurl.class.php");
require_once($class_path."/openurl/serialize/openurl_serialize_kev_mtx.class.php");
require_once($class_path."/openurl/context_object/openurl_context_object_kev_mtx_ctx.class.php");

function _getMapItem_($param){
	global $openurl_map;
	$openurl_map[$param['URI']] = array(
		'class' =>$param['CLASS'],
		'require' => $param['REQUIRE_PATH']
	);
}

class openurl_transport extends openurl_root{
	var $infos= array();		// Infos g�n�rales concernant le transport
	var $service_address = "";	// URL du service
	var $contextObject;			// ContextObject � transporter
	var $serialized_tsp="";		// infos du transport s�rialis�es
	var $serialized_obj="";		// ContextObject s�rialis�
    
    function openurl_transport($url) {
    	$this->service_address = $url;
    	$this->uri = parent::$uri."/tsp";
        $this->infos = array(
    		'url_ver' => "Z39.88-2004",
    		'url_tim' => date("Y-m-d")
    	);
    }

    function addContext($context){
    	$this->infos['url_ctx_fmt'] = $context->uri;
    	$this->contextObject = $context;
    }

	function serialize($debug=false){
		if($debug){
			highlight_string("Transport :".print_r($this->infos,true));
		}
		return openurl_serialize_kev_mtx::serialize($this->infos);
    }

    function unserialize($str){
    	global $include_path;
    	global $openurl_map;
    	global $url_ctx_val,$url_ctx_ref;
    	    	
    	$openurl_map = array();
    	//on va avoir besoin du mapping...
  	 	require_once ("$include_path/parser.inc.php") ;
    	_parser_($include_path."/openurl/openurl_mapping.xml", array("ITEM" => "_getMapItem_"), "MAP");
    	$ctx = new openurl_context_object_kev_mtx_ctx();
    	if($url_ctx_val != ""){
    		//Transport By-Value
			$ctx->unserialize($url_ctx_val);
    	}else if ($url_ctx_ref != ""){
    		//Transport By-Reference
    		$content = openurl_transport_http::get($url_ctx_ref);
			$ctx->unserialize($content);   	
    	}else{
    		//Transport Inline
			$ctx->unserialize($str);
    	}
    }
}

class openurl_transport_byref extends openurl_transport{
	var $notice_id;
	var $source_id;
	var $byref_url;
	
    function openurl_transport_byref($url,$notice_id,$source_id,$byref_url) {
    	parent::openurl_transport($url);
    	$this->notice_id = $notice_id;
    	$this->source_id = $source_id;
    	$this->byref_url = $byref_url;
    }
    
    function generateURL($debug=false){
    	if(!$this->serialized_tsp) $this->serialized_tsp = $this->serialize($debug);
    	if(!$this->serialized_obj) $this->serialized_obj = openurl_serialize_kev_mtx::serialize(array('url_ctx_ref' => $this->byref_url."?notice_id=".$this->notice_id."&in_id=".$this->source_id."&uri=".$this->contextObject->uri));
    	return $this->service_address.(strpos($this->service_address,"?")===false ? "?":"&").$this->serialized_tsp.($this->serialized_obj ? "&".$this->serialized_obj : "");
    }    
}

class openurl_transport_byval extends openurl_transport{

    function openurl_transport_byval($url) {
    	parent::openurl_transport($url);
    }
    function generateURL($debug=false){
    	if(!$this->serialized_tsp) $this->serialized_tsp = $this->serialize($debug);
    	if(!$this->serialized_obj) $this->serialized_obj = openurl_serialize_kev_mtx::serialize(array('url_ctx_val' => $this->contextObject->serialize($debug)));
    	return $this->service_address.(strpos($this->service_address,"?")===false ? "?":"&").$this->serialized_tsp.($this->serialized_obj ? "&".$this->serialized_obj : "");
    } 
}
class openurl_transport_inline extends openurl_transport{

    function openurl_transport_inline($url) {
    	parent::openurl_transport($url);
    }

    function generateURL($debug=false){
    	if(!$this->serialized_tsp) $this->serialized_tsp = $this->serialize($debug);
    	if(!$this->serialized_obj) $this->serialized_obj = $this->contextObject->serialize($debug);
    	return $this->service_address.(strpos($this->service_address,"?")===false ? "?":"&").$this->serialized_tsp.($this->serialized_obj ? "&".$this->serialized_obj : "");
    }    
}