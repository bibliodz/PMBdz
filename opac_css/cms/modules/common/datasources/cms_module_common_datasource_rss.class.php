<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_rss.class.php,v 1.8 2013-10-09 15:08:51 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once("$class_path/curl.class.php");

class cms_module_common_datasource_rss extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On défini les sélecteurs utilisable pour cette source de donnée
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_rss"
		);
	}
	
	/*
	 * Sauvegarde du formulaire, revient à remplir la propriété parameters et appeler la méthode parente...
	 */
	public function save_form(){
		global $cms_module_common_datasource_rss_limit;
		
		$this->parameters= array();
		$this->parameters['nb_max_elements'] = $cms_module_common_datasource_rss_limit+0;
		return parent::save_form();
	}
	
	public function get_form(){
		$form = parent::get_form();
		$form.= "
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_datasource_rss_limit'>".$this->format_text($this->msg['cms_module_common_datasource_rss_limit'])."</label> 
				</div>
				<div class='colonne-suite'>
					<input type='text' name='cms_module_common_datasource_rss_limit' value='".$this->parameters['nb_max_elements']."'/>
				</div>
			</div>";
		return $form;
	}
	
	/*
	 * Récupération des données de la source...
	 */
	public function get_datas(){
		//on commence par récupérer l'identifiant retourné par le sélecteur...
		if($this->parameters['selector'] != ""){
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$i]['name'] == $this->parameters['selector']){
					$selector = new $this->parameters['selector']($this->selectors[$i]['id']);
					break;
				}
			}
			@ini_set("zend.ze1_compatibility_mode", "0");
			$information = array();
			$loaded=false;
			$aCurl = new Curl();
			$aCurl->timeout=2;
			$content = $aCurl->get($selector->get_value());
			$flux=$content->body;
			if($flux && $content->headers['Status-Code'] == 200){
			  $rss = new domDocument();
			  $loaded=$rss->loadXML($flux);
			}
			if($loaded){
				//les infos sur le flux...
				$channel = $rss->getElementsByTagName("channel")->item(0);
				$elements = array(
					'title',
					'description',
					'generator',
					'link'
				); 
				$informations = $this->get_informations($channel,$elements,1);
				//on va lire les infos des items...
				$informations['items'] =array();
				$items = $rss->getElementsByTagName("item");
				$elements = array(
					'title',
					'description',
					'link',
					'guid',
					'date',
					'creator',
					'subject',
					'format',
					'language',
				);
				for($i=0 ; $i<$items->length ; $i++){
					if($this->parameters['nb_max_elements']==0 || $i < $this->parameters['nb_max_elements']){
						$informations['items'][]=$this->get_informations($items->item($i),$elements,false);
					}
				}
			}
			@ini_set("zend.ze1_compatibility_mode", "1");
			return $informations;
			
		}
		return false;
	}
	
	protected function get_informations($node,$elements,$first_only=false){
		global $charset;
		$informations = array();
		foreach($elements as $element){
			$items = $node->getElementsByTagName($element);
			if($items->length == 1 || $first_only){
				$informations[$element] = $this->charset_normalize($items->item(0)->nodeValue,"utf-8");
			}else{
				for($i=0 ; $i<$items->length ; $i++){
					$informations[$element][] = $this->charset_normalize($items->item($i)->nodeValue,"utf-8");
				}
			}
		}
		return $informations;
	}
	
	public function get_format_data_structure(){
		return array(
			array(
				'var' => "title",
				'desc' => $this->msg['cms_module_common_datasource_rss_title_desc']
			),
			array(
				'var' => "description",
				'desc' => $this->msg['cms_module_common_datasource_rss_description_desc']
			),
			array(
				'var' => "generator",
				'desc' => $this->msg['cms_module_common_datasource_rss_generator_desc']
			),
			array(
				'var' => "link",
				'desc' => $this->msg['cms_module_common_datasource_rss_link_desc']
			),
			array(
				'var' => "items",
				'desc' => $this->msg['cms_module_common_datasource_rss_items_desc'],
				'children' => array(
					array(
						'var' => "items[i].title",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_title_desc']
					),
					array(
						'var' => "items[i].description",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_description_desc']
					),
					array(
						'var' => "items[i].link",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_link_desc']
					),
					array(
						'var' => "items[i].guid",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_guid_desc']
					),
					array(
						'var' => "items[i].date",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_date_desc']
					),
					array(
						'var' => "items[i].creator",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_creator_desc']
					),
					array(
						'var' => "items[i].subject",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_subject_desc']
					),
					array(
						'var' => "items[i].format",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_format_desc']
					),
					array(
						'var' => "items[i].language",
						"desc" => $this->msg['cms_module_common_datasource_rss_item_language_desc']
					)
				)
			),
		);
	}
}