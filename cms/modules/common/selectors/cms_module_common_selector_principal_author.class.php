<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_principal_author.class.php,v 1.1 2012-10-22 14:57:27 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_principal_author extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->once_sub_selector=true;
	}
	
	protected function get_sub_selectors(){
		return array(
			"cms_module_common_selector_record_permalink",
			"cms_module_common_selector_record",
			"cms_module_common_selector_env_var"
		);
	}
	
	public function get_value(){
		//le sous-s�lecteur va nous donner la notice...
		if(!$this->value){
			$sub_selector= new $this->parameters['sub_selector']($this->get_sub_selector_id($this->parameters['sub_selector']));
			$this->value= array(
				'record' => 0,
				'author' => 0
			);
			if($sub_selector->get_value()){
				$this->value['record'] = $sub_selector->get_value();
				$query = "select responsability_author from responsability where responsability_notice = ".$sub_selector->get_value()." and responsability_type = 0";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$row = mysql_fetch_object($result);
					$this->value['author'] = $row->responsability_author;
				}
			}
		}
		return $this->value;
	}
}