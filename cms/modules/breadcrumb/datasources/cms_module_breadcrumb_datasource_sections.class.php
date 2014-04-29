<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_breadcrumb_datasource_sections.class.php,v 1.1 2012-08-21 14:23:24 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_breadcrumb_datasource_sections extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_available_selectors(){
		return array(
			'cms_module_common_selector_section',
			'cms_module_common_selector_env_var'
		);
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
				$sections = array();
				$section_id = $section_ids[0];
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
				//en th�orie on sort toujours, mais comme c'est un pays formidable, on lock � 100 it�rations...
				}while ($row->section_num_parent != 0 || $i>100);
				return array_reverse($datas);
			}
		}
		return false;
	}
}