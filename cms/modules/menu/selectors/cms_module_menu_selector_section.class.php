<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_menu_selector_section.class.php,v 1.2 2012-11-09 14:12:46 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_menu_selector_section extends cms_module_common_selector_section{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	//on surcharge juste la propri�t� qui g�n�re le select
	protected function gen_select(){
		$query= "select id_section, section_title from cms_sections";// where section_publication_state = 1";
		$result = mysql_query($query);
		$select = "
					<select name='".$this->get_form_value_name("id_section")."'>";
		if(mysql_num_rows($result)){
				$select.="
						<option value='0'>".$this->msg['cms_module_menu_selector_section_root_item']."</option>";
			while($row = mysql_fetch_object($result)){
				$select.="
						<option value='".$row->id_section."' ".($this->parameters == $row->id_section ? "selected='selected'" : "").">".$this->format_text($row->section_title)."</option>";
			}
		}else{
			$select.= "
						<option value ='0'>".$this->format_text($this->msg['cms_module_common_selector_section_no_section'])."</option>";
		}
		$select.= "
					</select>";
		return $select;
	}
}