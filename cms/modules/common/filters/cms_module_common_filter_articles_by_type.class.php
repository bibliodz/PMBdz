<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_filter_articles_by_type.class.php,v 1.1 2012-12-17 10:35:13 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_filter_articles_by_type extends cms_module_common_filter{

	public function get_filter_from_selectors(){
		return array(
			"cms_module_common_selector_article_type_from"
		);
	}

	public function get_filter_by_selectors(){
		return array(
			"cms_module_common_selector_article_type"
		);
	}
	
	public function filter($datas){
		$filtered_datas= $filter = array();

		$selector_by = $this->get_selected_selector("by");
		$field_by = $selector_by->get_value();
		if(count($field_by)){
			$query = "select id_article from cms_articles where article_num_type in (".implode(",",$field_by).")";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					$filter[] = $row->id_article;
				}
				foreach($datas as $article){
					if(in_array($article,$filter)){
						$filtered_datas[]=$article;
					}
				}
			}
		}
		return $filtered_datas;
	}
}