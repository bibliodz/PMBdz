<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_search_datasource_search.class.php,v 1.2 2012-10-17 09:13:39 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_search_datasource_search extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_available_selectors(){
		return array(
			"cms_module_search_selector_dest"
		);
	}
	
	public function get_datas(){
		$selector = $this->get_selected_selector();
		$datas = array();
		if($selector){
			$dests =  $selector->get_value();
			$query = "select managed_module_box from cms_managed_modules join cms_cadres on id_cadre = ".$this->cadre_parent." and cadre_object = managed_module_name";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$box = mysql_result($result,0,0);
				$infos =unserialize($box);
				foreach($dests as $dest){
					$datas[]=$infos['module']['search_dests'][$dest];
				}
			}
		}
		return $datas;
	}
}