<?php

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_opacitem_datasource_opacitem extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_opacitem_selector_opacitem"
		);
	}
	
	/*
	 * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
	 */
	public function save_form(){
		global $selector_choice;
	
		$this->parameters= array();
		$this->parameters['selector'] = $selector_choice;
		return parent::save_form();
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
			
			return $selector->get_value();
		}
		return false;
	}
	
}