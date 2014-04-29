<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_condition_lvl.class.php,v 1.7 2013-08-22 09:58:54 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_condition_lvl extends cms_module_common_condition{

	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_lvl",
		);
	}
	
	public static function is_loadable_default(){
		global $cms_build_info;
		if($cms_build_info['lvl'] || $cms_build_info['search_type_asked']){
			return true;
		}
		return false;
	}
	
	public function get_form(){
		//si on est sur une page de type Page en cr�ation de cadre, on propose la condition pr�-remplie...
		if($this->cms_build_env['lvl'] || $this->cms_build_env['search_type_asked']){
			if(!$this->id){
				$this->parameters['selectors'][] = array(
					'id' => 0,
					'name' => "cms_module_common_selector_lvl"
				);
			}
		}
		return parent::get_form();
	}
	
	public function check_condition(){
		global $lvl;
		global $search_type_asked;
		$selector = $this->get_selected_selector();
		$values = $selector->get_value();
		//on regarde si on est sur la bonne page...
		if($search_type_asked && is_array($values) && in_array($search_type_asked,$values)){
			return true;
		}else if(is_array($values) && in_array($lvl,$values)){
			//sur la page
			if($lvl == "index" || $lvl == ""){
				if(!$search_type_asked){
					return true;
				}
			}else{
				return true;
			}
		}
		//on est encore dans la fonction, donc la condition n'est pas v�rifi�e!
		return false;
	}
}