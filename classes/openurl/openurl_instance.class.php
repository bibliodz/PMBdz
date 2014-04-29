<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: openurl_instance.class.php,v 1.1 2011-08-02 12:35:59 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/openurl/descriptors/openurl_descriptors_kev_mtx.class.php");
require_once($class_path."/openurl/entities/openurl_entities.class.php");
require_once($class_path."/openurl/context_object/openurl_context_object_kev_mtx_ctx.class.php");
require_once($class_path."/openurl/transport/openurl_transport_http.class.php");
//pour r�cup les infos de notice
require_once($base_path."/admin/convert/export.class.php");
require_once($class_path."/export_param.class.php");

//Cette s'occupe de toute la gestion des classes OpenURL, donc en th�orie, si je m'en sors, cette seule classe peut suffir � l'interface...

class openurl_instance {
	var $notice_id = 0;				// identifiant de la notice
	var $notice_externe_id = 0;		// identifiant de la notice externe
	var $parent_id = 0;				// identifiant de la notice parente
	var $params = array();			// jeu de param�tres...
	var $notice_infos = "";			// informations sur la notice
	var $parent_infos = "";			// informations sur la notice parente, si disponible...
	var $serialization = "";		// mode de s�rialization
	var $referent;					// entit� referent
	var $referringEntity;			// entit� referring_entity
	var $requester;					// entit� requester
	var $serviceType;				// entit� service_type
	var $resolver;					// entit� resolver
	var $referrer;					// entit� referrer
	var $contextObject;				// l'objet contextuel
	var $transport;					// object pour le transport
	var $source_id;					// id de la source

	function openurl_instance($id=0,$id_externe=0,$params=array(),$source_id=0){
		$this->notice_id = $id;
		$this->notice_externe_id = $id_externe;
		$this->params = $params;
		$this->source_id = $source_id;
		$this->fetch_data();
	}
	
	function fetch_data(){
		//si on a rien, on peut pas travailler...
		if(!$this->notice_id && !$this->notice_externe_id)
			return false;

		if($this->notice_id){
			//pour une notice de la base...
			
			//r�cup�re les param d'exports
			$export_param = new export_param();
			$param = $export_param->get_parametres($export_param->context);
			//petit nettoyage pour un bon fonctionnement...
			foreach($param as $key => $value){
				$param[str_replace("export_","",$key)] = $param[$key];
			}
			//maintenant que c'est en ordre, on peut y aller!
			$export = new export(array($this->notice_id),array(),array());
			$export->get_next_notice("",array(),array(),false,$param);
			$this->notice_infos = $export->xml_array;
	
			//on regarde si on veut aussi les infos de la notice contenante...
			if ($this->params['entities']['referring_entity']['allow'] == "yes"){
				//il nous faut d�j� l'identifiant du parent, s'il existe...
				$this->parent_id = 0;
				switch($this->notice_infos['bl']['value'].$this->notice_infos['hl']['value']){
					case "a2" :
					case "s2" :
						$field = "461";
						break;
					default :
						$field = "463";
						break;
				}
				foreach($this->notice_infos['f'] as $f){
					switch($f['c']){
						case $field:
							foreach($f['s'] as $s){
								switch($s['c']){
									case "9" :
										if(strpos($s['value'],"id:")!==false){
											$this->parent_id = str_replace("id:","",$s['value']);
										}
										break;
								}
							}
							break;
					}
				}			
				if($this->parent_id){
					$export_parent = new export(array($this->parent_id),array(),array());
					$export_parent->get_next_notice("",array(),array(),false,$param);
					$this->parent_infos=$export_parent->xml_array;
				}	
			}
		}else{
			//pour une notice externe
			//TODO : r�cup notice infos
		}

		//on r�cup la s�rialization pour g�rer nos objets
		switch($this->params['serialization']){
			case "kev" :
			default :
				$this->serialization = "kev_mtx";
				break;
		}
	}

	function getReferent(){
		$descriptors = array();
		foreach($this->params['entities']['referent'] as $desc => $type){
			if(is_array($type)){
				foreach($type as $key => $asked){
					if($asked != 0){
						//si le descripteur "$key" est demand�
						$class_desc = "openurl_descriptor_".$desc."_".$this->serialization."_".$key;
						$descriptors[] = new $class_desc($this->notice_infos);
					}
				}
			}else{
				if($type != 0){
					if($this->notice_infos['bl']['value'] == "m") $item = "book";
					else $item = "journal";
					$class_desc = "openurl_descriptor_".$desc."_".$this->serialization."_".$item;
					if($desc == "byref"){
						$this->getTransport();
						$descriptors[] = new $class_desc($this->notice_infos,$this->source_id,$this->params['transport']['byref_url']);
					}else{
						$descriptors[] = new $class_desc($this->notice_infos);
					}
				}
			}
		}
		$this->referent = new openurl_entity_referent();
		foreach($descriptors as $desc){
			$this->referent->addDescriptor($desc);
		}
		return $this->referent;
	}

	function getReferringEntity(){
		if($this->parent_id){
			$descriptors = array();
			
			foreach($this->params['entities']['referring_entity']['elem'] as $desc => $type){
				if(is_array($type)){
					foreach($type as $key => $asked){
						if($asked != 0){
							//si le descripteur "$key" est demand�
							$class_desc = "openurl_descriptor_".$desc."_".$this->serialization."_".$key;
							$descriptors[] = new $class_desc($this->parent_infos);
						}
					}
				}else{
					if($type != 0){
						if($this->notice_infos['bl']['value'] == "m") $item = "book";
						else $item = "journal";
						$class_desc = "openurl_descriptor_".$desc."_".$this->serialization."_".$item;
						if($desc == "byref"){
							$this->getTransport();
							$descriptors[] = new $class_desc($this->parent_infos,$this->source_id,$this->params['transport']['byref_url']);
						}else{
							$descriptors[] = new $class_desc($this->parent_infos);
						}
					}
				}	
			}
			$this->referringEntity = new openurl_entity_referring_entity();
			foreach($descriptors as $desc){
				$this->referringEntity->addDescriptor($desc);
			}
			return $this->referringEntity;
			
		}else {
			return false;
		}
	}

	function getRequester(){
		if($this->params['entities']['requester']['allow'] == "yes"){
			$class_desc = "openurl_descriptor_identifier_".$this->serialization."_requester";
			$desc = new $class_desc($this->params['entities']['requester']['value']);
			$this->requester = new openurl_entity_requester();
			$this->requester->addDescriptor($desc);
			return $this->requester;
		}else{
			return false;
		}
	}

	function getServiceType(){
		if($this->params['entities']['service_type']['allow'] == "yes"){
			$class_desc = "openurl_descriptor_byval_".$this->serialization."_service_type";
			$desc = new $class_desc($this->params['entities']['service_type']['values']);
			$this->serviceType = new openurl_entity_service_type();
			$this->serviceType->addDescriptor($desc);
			return $this->serviceType;	
		}else {
			return false;
		}
	}

	function getResolver(){
			if($this->params['entities']['resolver']['allow'] == "yes"){
			$class_desc = "openurl_descriptor_identifier_".$this->serialization."_resolver";
			$desc = new $class_desc($this->params['entities']['resolver']['value']);
			$this->resolver = new openurl_entity_resolver();
			$this->resolver->addDescriptor($desc);
			return $this->resolver;
		}else {
			return false;
		}
	}

	function getReferrer(){
		if($this->params['entities']['referrer']['allow'] == "yes"){
			$class_desc = "openurl_descriptor_identifier_".$this->serialization."_referrer";
			$desc = new $class_desc($this->params['entities']['referrer']['value']);
			$this->referrer = new openurl_entity_referrer();
			$this->referrer->addDescriptor($desc);
			return $this->referrer;
		}else {
			return false;
		}
	}

	function generateEntities(){
		$entities = array();
		$entities[] = $this->getReferent();
		
		if($this->parent_id){
			$entities[] = $this->getReferringEntity();
		}
		if($this->params['entities']['requester']['allow'] == "yes"){
			$entities[] = $this->getRequester();
		}
		if($this->params['entities']['service_type']['allow'] == "yes"){
			$entities[] = $this->getServiceType();
		}
		if($this->params['entities']['resolver']['allow'] == "yes"){
			$entities[] = $this->getResolver();
		}
		if($this->params['entities']['referrer']['allow'] == "yes"){
			$entities[] = $this->getReferrer();
		}
		return $entities;
	}

	function getContextObject(){
		switch($this->serialization){
			case "kev_mtx" : 
					$this->contextObject = new openurl_context_object_kev_mtx_ctx();
				break;
		}
	}
	
	function generateContextObject(){
		$this->getContextObject();
		$entities = $this->generateEntities();
		foreach($entities as $entity){
			$this->contextObject->addEntity($entity);
		}
	}
	
	function getTransport(){
		if(!$this->transport){
			$class = "openurl_transport_".$this->params['transport']['method']."_".$this->params['transport']['protocole'];
			if($this->params['transport']['method']=="byref"){
				$this->transport = new $class($this->params['transport']['param'],$this->notice_id,$this->source_id,$this->params['transport']['byref_url']);
			}else $this->transport = new $class($this->params['transport']['param']);
		}
	}
	
	function generateTransport(){
		$this->getTransport();
		if(!$this->contextObject) $this->generateContextObject();
		$this->transport->addContext($this->contextObject);
	}
	
	function getInFrame($width,$height){
		$this->generateTransport();
		return  "<iframe style='width:".$width."px;height:".$height."px' src='".$this->transport->generateURL()."'></iframe>";
	}
}