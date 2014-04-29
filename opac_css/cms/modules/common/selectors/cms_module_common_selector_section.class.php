<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_section.class.php,v 1.4 2012-11-09 14:12:45 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
//require_once($base_path."/cms/modules/common/selectors/cms_module_selector.class.php");
class cms_module_common_selector_section extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for=''>".$this->format_text($this->msg['cms_module_common_selector_section_id_section'])."</label>
				</div>
				<div class='colonne-suite'>";
		$form.=$this->gen_select();
		$form.="
				</div>
			</div>";
		$form.=parent::get_form();
		return $form;
	}
	
	public function save_form(){
		$this->parameters = $this->get_value_from_form("id_section");
		return parent ::save_form();
	}
	
	protected function gen_select(){
		$query= "select id_section, section_title from cms_sections";// where section_publication_state = 1";
		$result = mysql_query($query);
		$select = "
					<select name='".$this->get_form_value_name("id_section")."'>";
		if(mysql_num_rows($result)){
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
	
	/*
	 * Retourne la valeur s�lectionn�
	 */
	public function get_value(){
		if(!$this->value){
			$this->value = $this->parameters;
		}
		return $this->value;
	}
}