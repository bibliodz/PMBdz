<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_global_var.class.php,v 1.1 2013-01-02 11:07:10 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
//require_once($base_path."/cms/modules/common/selectors/cms_module_selector.class.php");
class cms_module_common_selector_global_var extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for=''>".$this->format_text($this->msg['cms_module_common_selector_global_var_label'])."</label>
				</div>
				<div class='colonne-suite'>
					<input type='text' name='".$this->get_form_value_name("global")."' value='".$this->addslashes($this->format_text($this->parameters))."'/>
				</div>
			</div>";
		$form.=parent::get_form();
		return $form;
	}
	
	public function save_form(){
		$this->parameters = $this->get_value_from_form("global");
		return parent ::save_form();
	}
	
	/*
	 * Retourne la valeur s�lectionn�
	 */
	public function get_value(){
		if(!$this->value){
			$value = $this->parameters;
			global $$value;
			if(isset($$value)){
				$this->value = $$value;
			}else{
				$this->value = false;
			}
		}
		return $this->value;
	}
}