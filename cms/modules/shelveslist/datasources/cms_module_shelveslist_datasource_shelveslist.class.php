<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_shelveslist_datasource_shelveslist.class.php,v 1.3 2013-06-12 07:54:54 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_shelveslist_datasource_shelveslist extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->sortable = false;
		$this->limitable = false;
	}
	/*
	 * On défini les sélecteurs utilisable pour cette source de donnée
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_shelves_generic"
		);
	}
	
	/*
	 * On défini les critères de tri utilisable pour cette source de donnée
	 */
	protected function get_sort_criterias() {
		return array (
			"title"
		);
	}
	
	/*
	 * Récupération des données de la source...
	 */
	public function get_datas(){
		global $opac_url_base;
		
		$selector = $this->get_selected_selector();
		if ($selector) {
			$return = array();
			if (count($selector->get_value()) > 0) {
				foreach ($selector->get_value() as $value) {
					$return[] = $value;
				}
			}
			
			if(count($return)){
				$query = "select idetagere, name, comment from etagere where idetagere in (".implode(",",$return).")";

// 				if ($this->parameters["sort_by"] != "") {
// 					$query .= " order by ".$this->parameters["sort_by"];
// 					if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
// 				} 
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$return = array();
					while($row=mysql_fetch_object($result)){
						$link_rss = "";
						$query2 = "select num_rss_flux from ((select etagere_id, group_concat(distinct caddie_id order by caddie_id asc separator ',') as gc0 from etagere_caddie group by etagere_id) a0 join (select num_rss_flux, group_concat(distinct num_contenant order by num_contenant asc separator ',') as gc1 from rss_flux_content group by num_rss_flux) a1 on (a0.gc0 like a1.gc1)) where etagere_id = $row->idetagere";
						$result2 = mysql_query($query2);
						if (mysql_num_rows($result2)) {
							while ($row2 = mysql_fetch_object($result2)) {
								$link_rss = "./rss.php?id=".$row2->num_rss_flux;
							}
						}
						$return[] = array("id" => $row->idetagere, "name" => $row->name, "comment" => $row->comment, "link_rss" => $link_rss);
					}
				}
			}
			return array('shelves' => $return);
		}
		return false;
	}
}