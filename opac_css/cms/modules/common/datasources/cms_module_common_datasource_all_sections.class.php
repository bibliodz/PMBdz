<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_all_sections.class.php,v 1.3 2013-09-06 08:00:05 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_all_sections extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->sortable = true;
		$this->limitable = true;
	}
	/*
	 * On défini les sélecteurs utilisable pour cette source de donnée
	 */
	public function get_available_selectors(){
		return array(
		);
	}

	/*
	 * On défini les critères de tri utilisable pour cette source de donnée
	 */
	protected function get_sort_criterias() {
		return array (
			"publication_date",
			"id_section",
			"section_title",
			"section_order"
		);
	}
	
	/*
	 * Récupération des données de la source...
	 */
	public function get_datas(){
		$query = "select id_section,if(section_start_date != '0000-00-00 00:00:00',section_start_date,section_creation_date) as publication_date  from cms_sections";
		if ($this->parameters["sort_by"] != "") {
			$query .= " order by ".$this->parameters["sort_by"];
			if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
		}
		$result = mysql_query($query);
		$return = array();
		if(mysql_num_rows($result) > 0){
			while($row = mysql_fetch_object($result)){
				$return[] = $row->id_section;
			}
		}
		$return = $this->filter_datas("sections",$return);
		if ($this->parameters["nb_max_elements"] > 0) $return = array_slice($return, 0, $this->parameters["nb_max_elements"]);
		return $return;
	}
}