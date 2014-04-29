<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_articles_sections.class.php,v 1.7 2013-09-06 08:00:05 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_articles_sections extends cms_module_common_datasource_list{
	
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
			"cms_module_common_selector_sections",
			"cms_module_common_selector_env_var",
		);
	}

	/*
	 * On d�fini les crit�res de tri utilisable pour cette source de donn�e
	 */
	protected function get_sort_criterias() {
		return array (
			"publication_date",
			"id_article",
			"article_title",
			"article_order"
		);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		$selector = $this->get_selected_selector();
		if ($selector) {
			$tab_values = $selector->get_value();
			if(!is_array($tab_values)){
				$tab_values = array($tab_values);
			}
			if (count($tab_values) > 0) {
				$list_values = implode(",", $tab_values);
				$query = "select id_article,if(article_start_date != '0000-00-00 00:00:00',article_start_date,article_creation_date) as publication_date  from cms_articles where num_section in (".$list_values.")";	
				if ($this->parameters["sort_by"] != "") {
					$query .= " order by ".$this->parameters["sort_by"];
					if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
				}
				$result = mysql_query($query);
				$return = array();
				if(mysql_num_rows($result) > 0){
					while($row = mysql_fetch_object($result)){
						$return[] = $row->id_article;
					}
				}
				$return = $this->filter_datas("articles",$return);
				if ($this->parameters["nb_max_elements"] > 0) $return = array_slice($return, 0, $this->parameters["nb_max_elements"]);
				return $return;
			}
		}
		return false;
	}
}