<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_searcharticles.class.php,v 1.6 2013-09-06 08:00:05 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_searcharticles extends cms_module_common_datasource_list{
	
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
			"id_article",
			"article_title"
		);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		global $dbh;
		$selector = $this->get_selected_selector();
		if($selector) {
			$tab_word_query = array();
			$tab_word_query = explode (" ",$selector->get_value());
		
			$query = "";
			if (count($tab_word_query) > 0) {
				$select ="(";
				$where ="("; 
				foreach ($tab_word_query as $i=>$word_query) {
					$select .= "(concat(article_title,' ',article_resume,' ',article_contenu) like '%$word_query%')+";
					if ($i > 0) $operator = " or ";
					else $operator = "";
					$where .= $operator." concat(article_title,' ',article_resume,' ',article_contenu) like '%".$word_query."%'";
				}
				$select .= "(trim(article_title) like '".$selector->get_value()."%')*0.2";
				$select .= ") as pert";
				$where .= ")";
				$query = "select distinct id_article,".$select." from cms_articles where ".$where;
				if ($this->parameters["sort_by"] != "") {
					$query .= " order by ".$this->parameters["sort_by"];
					if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
				}
				$result = mysql_query($query,$dbh);
				$return = array();
				while ($row = mysql_fetch_object($result)) {
					$return[] = $row->id_article;
				}
				$return = $this->filter_datas("articles",$return);
				if ($this->parameters["nb_max_elements"] > 0) $return = array_slice($return, 0, $this->parameters["nb_max_elements"]);
				return $return;
			}
		} 
		return false;	
	}
}