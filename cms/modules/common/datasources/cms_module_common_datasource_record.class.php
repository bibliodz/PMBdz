<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_record.class.php,v 1.2 2013-07-30 07:42:38 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_record extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_record",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_type_article",
			"cms_module_common_selector_type_section"
		);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		if($this->parameters['selector'] != ""){
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$i]['name'] == $this->parameters['selector']){
					$selector = new $this->parameters['selector']($this->selectors[$i]['id']);
					break;
				}
			}
			//$notice = new notice_info($selector->get_value());
			$notice=$selector->get_value();
			if($this->parameters['selector'] == "cms_module_common_selector_type_article" || $this->parameters['selector'] == "cms_module_common_selector_type_section"){
				$notice = $notice[0];
			}
			return $notice;
		}
		return false;
	}
}