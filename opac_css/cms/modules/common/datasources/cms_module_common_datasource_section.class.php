<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_section.class.php,v 1.7 2013-01-02 11:07:10 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_section extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_section",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_global_var",
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
		$selector = $this->get_selected_selector();
		if($selector){
			$section_id = $selector->get_value();
			$section_ids = $this->filter_datas("sections",array($section_id));
			if($section_ids[0]){
				$section = new cms_section($section_ids[0]);
				$return = $section->format_datas();
				return $return;
			}
		}
		return false;
	}
	
	public function get_format_data_structure(){
		return cms_section::get_format_data_structure();
	}
}