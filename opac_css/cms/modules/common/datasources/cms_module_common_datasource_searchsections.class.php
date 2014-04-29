<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_searchsections.class.php,v 1.3 2012-10-17 13:17:33 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_searchsections extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->sortable = true;
		$this->limitable = true;
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_search_result"
		);
	}
	
	/*
	 * On d�fini les crit�res de tri utilisable pour cette source de donn�e
	 */
	protected function get_sort_criterias() {
		return array (
			"pert",
			"id_section",
			"section_title"
		);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		global $dbh;
		$selector = $this->get_selected_selector();
		if($selector) {
			if($selector->get_value() != ""){
				$searcher = new cms_editorial_searcher($selector->get_value(),"section");
				return $searcher->get_sorted_result($this->parameters["sort_by"],$this->parameters["sort_order"],$this->parameters["nb_max_elements"]);
			}
		} 
		return false;	
	}
}