<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_breadcrumb_datasource_sections_from_article.class.php,v 1.2 2012-11-15 09:47:31 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_breadcrumb_datasource_sections_from_article extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_available_selectors(){
		return array(
			'cms_module_common_selector_article',
			'cms_module_common_selector_env_var'
		);
	}
	
	/*
	 * Récupération des données de la source...
	 */
	public function get_datas(){
		//on commence par récupérer l'identifiant retourné par le sélecteur...
		$selector = $this->get_selected_selector();
		if($selector){
			$article_id = $selector->get_value();
			if($article_id[0]){
				$sections = array();
				$query = "select num_section from cms_articles where id_article = ".$article_id[0];
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$section_id = mysql_result($result,0,0);
					if($section_id){
						$datas = array();
						$i=0;
						do {
							$i++;
							$query = "select id_section,section_num_parent from cms_sections where id_section = ".$section_id;
							$result = mysql_query($query);
							if(mysql_num_rows($result)){
								$row = mysql_fetch_object($result);
								$section_id = $row->section_num_parent;
								$datas[] = $row->id_section;
								
							}else{
								break;
							}
						//en théorie on sort toujours, mais comme c'est un pays formidable, on lock à 100 itérations...
						}while ($row->section_num_parent != 0 || $i>100);
						return array_reverse($datas);
					}
				}
			}
		}
		return false;
	}
}