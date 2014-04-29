<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_records_by_cp_val.class.php,v 1.2 2013-09-05 07:15:15 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_records_by_cp_val extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->limitable = true;
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_record_cp_val"
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
 			$records = $notices = array();
			$records = $this->filter_datas("notices",$records);
			$records = array_slice($records, 0, $this->parameters['nb_max_elements']);
			$return = array(
				'title'=> 'Liste de Notices',
				'records' => $records
			);
			
			return $return;
		}
		return false;
	}
}