<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: flexpdf.class.php,v 1.9 2013-07-24 13:09:22 arenou Exp $

require_once($visionneuse_path."/classes/mimetypes/affichage.class.php");
require_once($visionneuse_path."/classes/mimetypes/converter_factory.class.php");

class flexpdf extends affichage{
	var $doc;					//le document num�rique � afficher
	var $driver;				//class driver de la visionneuse
	var $params;				//param�tres �ventuels
	var $toDisplay= array();	//tableau des infos � afficher	
	var $tabParam = array();	//tableau d�crivant les param�tres de la classe
	var $parameters = array();	//tableau des param�tres de la classe
 
    function flexpdf($doc=0) {
    	if($doc){
    		$this->doc = $doc; 
    		$this->driver = $doc->driver;
    		$this->params = $doc->params;
    		$this->getParamsPerso();
    	}
    }
    
    function fetchDisplay(){
    	global $visionneuse_path,$base_path;
     	//le titre
    	$this->toDisplay["titre"] = $this->doc->titre;
    	//la visionneuse pdf
    	$this->toDisplay["doc"]="
    	<script type='text/javascript' src='visionneuse/classes/mimetypes/flexpdf/flexpaper/js/jquery.min.js'></script>
    	<script type='text/javascript' src='visionneuse/classes/mimetypes/flexpdf/flexpaper/js/flexpaper.js'></script>
    	<script type='text/javascript' src='visionneuse/classes/mimetypes/flexpdf/flexpaper/js/flexpaper_handlers.js'></script>
    	<div id='flexpaperFrameViewer' class='flexpaper_viewer' style='margin:auto;display:block'></div>
    	<script type='text/javascript'> 
    			window.onload = function(){
					var iframe= document.getElementById('flexpaperFrameViewer');
					iframe.style.width = '".$this->parameters["size_x"]."%';
					iframe.style.height = ((getFrameHeight()-40-80)*".($this->parameters["size_y"]/100).")+'px';				
					$('#flexpaperFrameViewer').FlexPaperViewer({ config : {
							 SWFFile : ".pmb_escape()."('".$this->driver->getVisionneuseUrl("lvl=afficheur&explnum=".$this->doc->id)."'),
							 jsDirectory : 'visionneuse/classes/mimetypes/flexpdf/flexpaper/js/',
							 Scale : 0.6, 
							 ZoomTransition : 'easeOut',
							 ZoomTime : 0.5,
							 ZoomInterval : 0.2,
							 FitPageOnLoad : true,
							 FitWidthOnLoad : false,
							 PrintEnabled : ".($this->parameters["print_allowed"]?"true":"false").",
							 FullScreenAsMaxWindow : false,
							 ProgressiveLoading : true,
							 MinZoomSize : 0.2,
							 MaxZoomSize : 5,
							 SearchMatchAll : true,
							 InitViewMode : 'Portrait',
							 RenderingOrder : 'flash,html,html5',
							 ViewModeToolsVisible : true,
							 ZoomToolsVisible : true,
							 NavToolsVisible : true,
							 CursorToolsVisible : true,
							 SearchToolsVisible : true,
	  						 localeChain: 'fr_FR'
							}});
				}";
    	if ($this->doc->search) {
    		$this->toDisplay["doc"].="	
				window.onDocumentLoaded=function() {
					getDocViewer().searchText('".addslashes(substr($this->doc->search,9,strlen($this->doc->search)-10))."');
				}";
    	}
    	$this->toDisplay["doc"].="
	        </script>
	        
    	";
    	//if ($this->parameters['autoresize'] == 1)
		//la description
		$this->toDisplay["desc"] = $this->doc->desc;
		return $this->toDisplay;  	
    }
    
    function render(){
    	global $visionneuse_path;	
    	$this->driver->cleanCache();
    	if (!$this->driver->isInCache($this->doc->id)) {
    		$converter = converter_factory::make(	$this->driver->driver_name."_".$this->driver->currentDoc['id'],
   													$this->driver->currentDoc['path'],
   													$this->driver->currentDoc['mimetype'],
   													$this->driver->currentDoc['extension'],
   													'swf',
   													$visionneuse_path.'/temp/',
   													$this->parameters
   												);
   			if (is_object($converter)) {
    			if ($converter->convert($this->driver->openCurrentDoc())) {
  	    			$this->driver->setInCache($this->doc->id,file_get_contents($this->driver->get_cached_filename($this->doc->id).".swf"));
    			}
     			$converter->remove_tmp_files();
   			}
   		}
    	print $this->driver->readInCache($this->doc->id);
    }
    
    function getTabParam(){

    	$this->tabParam = array(
			"size_x"=>array("type"=>"text","name"=>"size_x","value"=>$this->parameters['size_x'],"desc"=>"Largeur du document en % de l'espace visible"),
			"size_y"=>array("type"=>"text","name"=>"size_y","value"=>$this->parameters['size_y'],"desc"=>"Hauteur du document en % de l'espace visible"),
			"print_allowed"=>array("type"=>"checkbox","name"=>"print_allowed","value"=>1,"desc"=>"Autoriser l'impression"),
			"pdftotext_cmd"=>array("type"=>"text","name"=>"pdftotext_cmd","value"=>$this->parameters['pdftotext_cmd'],"desc"=>"Commande d'ex&eacute;cution du script de conversion pdftotext"),
			"pdf2swf_cmd"=>array("type"=>"text","name"=>"pdf2swf_cmd","value"=>$this->parameters['pdf2swf_cmd'],"desc"=>"Commande d'ex&eacute;cution du script de conversion pdf2swf"),
			"pyodconverter_cmd"=>array("type"=>"text","name"=>"pyodconverter_cmd","value"=>$this->parameters['pyodconverter_cmd'],"desc"=>"Commande d'ex&eacute;cution du script de conversion python \"pyodconverter\""),
			"jodconverter_cmd"=>array("type"=>"text","name"=>"jodconverter_cmd","value"=>$this->parameters['jodconverter_cmd'],"desc"=>"Commande d'ex&eacute;cution du script de conversion java \"jodconverter\""),
			"jodconverter_url"=>array("type"=>"text","name"=>"jodconverter_url","value"=>$this->parameters['jodconverter_url'],"desc"=>"Adresse de la webapp \"jodconverter\"")
		);
       	return $this->tabParam;
    }
    
	function getParamsPerso(){
		$params = $this->driver->getClassParam('flexpdf');
		$this->unserializeParams($params);
		if($this->parameters['size_x'] == 0) $this->parameters['size_x'] = $this->driver->getParam("maxX");
		if($this->parameters['size_y'] == 0) $this->parameters['size_y'] = $this->driver->getParam("maxY");
		if(!$this->parameters['print_allowed']) $this->parameters['print_allowed'] = 0;
	}
	
	function unserializeParams($paramsToUnserialized){
		$this->parameters = unserialize($paramsToUnserialized);
		if(!$this->parameters['print_allowed']) $this->parameters['print_allowed'] = 0;
		return $this->parameters;
	}
	
	function serializeParams($paramsToSerialized){
		if(!$paramsToSerialized['print_allowed']) $paramsToSerialized['print_allowed'] = 0;
		$this->parameters =$paramsToSerialized;
		return serialize($paramsToSerialized);
	}
}
?>
