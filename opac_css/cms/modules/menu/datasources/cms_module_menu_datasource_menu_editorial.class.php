<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_menu_datasource_menu_editorial.class.php,v 1.8 2013-09-12 08:55:05 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_menu_datasource_menu_editorial extends cms_module_menu_datasource_menu{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_menu_selector_section",
			"cms_module_common_selector_env_var"
		);
	}
	
	public function get_form(){
		$form = parent::get_form();
		$form.="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_menu_datasource_menu_editorial_max_depth'>".$this->format_text($this->msg['cms_module_menu_datasource_menu_editorial_max_depth'])."</label>
			</div>
			<div class='colonne-suite'>
				<input type='text' name='cms_module_menu_datasource_menu_editorial_max_depth' value='".$this->parameters['max_depth']."'/>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_menu_datasource_menu_editorial_link_constructor'>".$this->format_text($this->msg['cms_module_menu_datasource_menu_editorial_link_constructor'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.=$this->get_constructor_link_form("section");
		$form.="
			</div>
		</div>";
		return $form;
	}

	public function save_form(){
		global $cms_module_menu_datasource_menu_editorial_max_depth;
		$this->parameters['max_depth'] = $cms_module_menu_datasource_menu_editorial_max_depth+0;
		$this->save_constructor_link_form("section");	
		return parent::save_form();
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		$selector = $this->get_selected_selector();
		//le s�lecteur nous retourne l'identifiant de la rubrique racine de la construction du menu ( pour tous avoir...)
		if($selector){
			return array(
				'name' => '',
				'items' => $this->build_tree_sections($selector->get_value())
			);
		}
		return false;
	}
	
	protected function build_tree_sections($id_parent,$depth=0){
		if($this->parameters['max_depth'] == 0 || $depth < $this->parameters['max_depth']){
			$items = $ids = $rows = array();
			$query = "select id_section,section_title from cms_sections where section_num_parent = ".$id_parent." order by section_order asc";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					$ids[] = $row->id_section;
					$rows[] = $row;
				}
				$ids = $this->filter_datas("sections",$ids);
				foreach($rows as $row){
					if(in_array($row->id_section,$ids)){
						$item = array(
							'id' => $row->id_section,
							'title' => $row->section_title,
							'link' => $this->get_constructed_link("section",$row->id_section)
						);
						$sub_query = "select count(id_section) from cms_sections where section_num_parent = ".$row->id_section;
						$sub_result = mysql_query($sub_query);
						if(mysql_num_rows($result) && mysql_result($sub_result,0,0)>0){
							$item['children'] = $this->build_tree_sections($row->id_section,$depth+1);
						}
						$items[]=$item;
					}
				}
			}
		}
		return $items;
	}	
}