<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: google_book.class.php,v 1.8 2012-12-07 13:29:43 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path,$base_path, $include_path;
require_once($class_path."/connecteurs.class.php");
require_once($class_path."/curl.class.php");
require_once($class_path."/nusoap/nusoap.php");
require_once($include_path."/notice_affichage.inc.php");

class google_book extends connector {
	//Variables internes pour la progression de la r�cup�ration des notices
	var $del_old;				//Supression ou non des notices dej� existantes
	
	var $profile;				//Profil Amazon
	var $match;					//Tableau des crit�res UNIMARC / AMAZON
	var $current_site;			//Site courant du profile (n�)
	var $searchindexes;			//Liste des indexes de recherche possibles pour le site
	var $current_searchindex;	//Num�ro de l'index de recherche de la classe
	var $match_index;			//Type de recherche (power ou simple)
	var $types;					//Types de documents pour la conversino des notices
	
	//R�sultat de la synchro
	var $error;					//Y-a-t-il eu une erreur	
	var $error_message;			//Si oui, message correspondant
	
    function google_book($connector_path="") {
    	parent::connector($connector_path);
    }
    
    function get_id() {
    	return "google_book";
    }
    
    //Est-ce un entrepot ?
	function is_repository() {
		return 2;
	}
    
    function unserialize_source_params($source_id) {
    	$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			$params["PARAMETERS"]=$vars;
		}
		return $params;
    }
    
    function get_libelle($message) {
    	if (substr($message,0,4)=="msg:") return $this->msg[substr($message,4)]; else return $message;
    }
    
    function source_get_property_form($source_id) {
		global $charset;
		
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		if (!isset($width))
			$width = "500";
		if (!isset($height))
			$height = "500";
			
		$form="<div class='row'>
				<div class='colonne3'><label for='width'>".$this->msg["gbooks_width"]."</label></div>
				<div class='colonne-suite'><input type='text' name='width' value='".htmlentities($width,ENT_QUOTES,$charset)."'/></div>
			</div>
			<div class='row'>
				<div class='colonne3'><label for='mdp'>".$this->msg["gbooks_height"]."</label></div>
				<div class='colonne-suite'><input type='text' name='height' value='".htmlentities($height,ENT_QUOTES,$charset)."'/></div>
			</div>";
		return $form;
    }
    
    function make_serialized_source_properties($source_id) {
    	global $width,$height;
    	$t["width"]=$width+0;
    	$t["height"]=$height+0;
    	
    	$this->sources[$source_id]["PARAMETERS"]=serialize($t);
	}
	
	//R�cup�ration  des prori�t�s globales par d�faut du connecteur (timeout, retry, repository, parameters)
	function fetch_default_global_values() {
		$this->timeout=5;
		$this->repository=2;
		$this->retry=3;
		$this->ttl=1800;
		$this->parameters="";
	}
	
	 //Formulaire des propri�t�s g�n�rales
	function get_property_form() {
		return "";
	}
    
    function make_serialized_properties() {
    	global $accesskey, $secretkey;
		//Mise en forme des param�tres � partir de variables globales (mettre le r�sultat dans $this->parameters)
		$keys = array();
    	
    	$keys['accesskey']=$accesskey;
		$keys['secretkey']=$secretkey;
		$this->parameters = serialize($keys);
	}

	function enrichment_is_allow(){
		return true;
	}
	
	function getEnrichmentHeader($source_id){
		global $lang;
		$header= array();
		$header[]= "<!-- Script d'enrichissement pour Google Book-->";
		$header[]= "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
		$header[]= "<script type='text/javascript'>google.load('books','0', {'language': '".substr($lang,0,2)."'});</script>";
		return $header;
	}
	
	function getTypeOfEnrichment($notice_id,$source_id){
		$type['type'] = array(
			"books"
		);		
		$type['source_id'] = $source_id;
		return $type;
	}
	
	function getEnrichment($notice_id,$source_id,$type="",$enrich_params=array(),$page=1){
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		$enrichment= array();
		
		//on renvoi ce qui est demand�... si on demande rien, on renvoi tout..
		switch ($type){
			case "books" :
			default :
				$rqt="select code from notices where notice_id = '$notice_id'";
				$res=mysql_query($rqt);
				if(mysql_num_rows($res)){
					$ref = mysql_result($res,0,0);
					//google change son API, on s'assure d'avoir un ISBN13 format� !
					if(isEAN($$ref)) {
						// la saisie est un EAN -> on tente de le formater en ISBN
						$EAN=$ref;
						$isbn = EANtoISBN($ref);
						// si �chec, on prend l'EAN comme il vient
						if(!$isbn)
							$code = str_replace("*","%",$ref);
						else {
							$code=$isbn;
							$code10=formatISBN($code,10);
						}
					} else {
						if(isISBN($ref)) {
							// si la saisie est un ISBN
							$isbn = formatISBN($ref);
							// si �chec, ISBN erron� on le prend sous cette forme
							if(!$isbn)
								$code = str_replace("*","%",$ref);
							else {
								$code10=$isbn ;
								$code=formatISBN($code10,13);
							}
						} else {
							// ce n'est rien de tout �a, on prend la saisie telle quelle
							$code = str_replace("*","%",$ref);
						}
					}
					
					//plutot que de faire une requete pour lancer que si ca marche, on ajoute un callback en cas d'�chec
					if($code /*&& $this->checkIfEmbeddable($code)*/){
						$enrichment['books']['content'] = "
						<div id='gbook$notice_id' style='width: ".$width."px; height: ".$height."px;margin-bottom:0.5em;'></div>";
						$enrichment['books']['callback'] = "
							var viewer = new google.books.DefaultViewer(document.getElementById('gbook".$notice_id."'));
							var gbook".$notice_id."_failed = function(){
								var content = document.getElementById('gbook".$notice_id."');
								var span = document.createElement('span');
								var txt = document.createTextNode('".$this->msg["gbook_no_preview"]."');
								span.appendChild(txt);
								content.appendChild(span);
								content.style.height='auto';
							}
							viewer.load('ISBN:".str_replace("-","",$code)."',gbook".$notice_id."_failed);	
						";
					}else{
						$enrichment['books']['content'] = "<span>".$this->msg["gbook_no_preview"]."</span>";
					}
				}
				break;
		}		
		$enrichment['source_label']=$this->msg['gbooks_enrichment_source'];
		return $enrichment;
	}
	
	function checkIfEmbeddable($isbn){
		$identifiers = array();
		$curl = new Curl();
		$xmlToParse = $curl->get("http://www.google.com/books/feeds/volumes?q=ISBN".$isbn);	
		$xml = _parser_text_no_function_($xmlToParse,"FEED");
		if($xml['ENTRY'][0]){
			$isbn = preg_replace('/-|\.| /', '', $isbn);
			//on regarde quand meme si on est le bon livre...
			foreach($xml['ENTRY'][0]['DC:IDENTIFIER'] as $identifier){
				if(substr($identifier['value'],0,4) == "ISBN"){
					$identifiers[]=substr($identifier['value'],5);
				}
			}
			//si le feuillatage est disponible...
			if((in_array(substr("-","",$isbn),$identifiers) || in_array($isbn,$identifiers)) && substr($xml['ENTRY'][0]['GBS:EMBEDDABILITY'][0]['VALUE'],strpos($xml['ENTRY'][0]['GBS:EMBEDDABILITY'][0]['VALUE'],"#")+1) == "embeddable"){
				return true;
			}else return false;
		}
		
	}
}
?>