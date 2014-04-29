<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_type_editorial.class.php,v 1.6 2013-06-12 07:54:54 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_type_editorial extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);		
		$this->once_sub_selector=true;
	}
	
	protected function get_sub_selectors(){
		return array(
			'cms_module_common_selector_env_var'
		);
	}
	public function get_form(){
		$form=parent::get_form();
		$form.= "
			<div class='row'>
				<div class='colonne3'>
					<label for=''>".$this->format_text($this->msg['cms_module_common_selector_type_editorial_label'])."</label>
				</div>
				<div class='colonne-suite'>";
		$form.=$this->gen_select();
		$form.="
				</div>
			</div>
			<div id='type_editorial_fields'>
				<div class='row'>
					<div class='colonne3'>
						<label for=''>".$this->format_text($this->msg['cms_module_common_selector_type_editorial_fields_label'])."</label>
					</div>
					<div class='colonne-suite'> 
						<select name='".$this->get_form_value_name("select_field")."' >";	
		$fields = new cms_editorial_parametres_perso($this->parameters["type_editorial"]);
		$form.= $fields->get_selector_options($this->parameters["type_editorial_field"]);
		$form.= "
						</select>
					</div>
				</div>
			</div>";
		return $form;	
		
	}
	
	protected function gen_select(){
		//si on est en cr�ation de cadre
		if(!$this->id){
			$this->parameters = array();
		}
		$select = "<select name='".$this->get_form_value_name($this->cms_module_common_selector_type_editorial_type)."' 
			onchange=\"cms_type_fields(this.value);\" >
		";	
		
		$types = new cms_editorial_types($this->cms_module_common_selector_type_editorial_type);
		$select.= $types->get_selector_options($this->parameters["type_editorial"]);
		$select.= "</select>
		<script type='text/javascript'>
			function cms_type_fields(id_type){
				dojo.xhrGet({
					url : '".$this->get_ajax_link(array($this->class_name."_hash[]" => $this->hash))."&id_type='+id_type,
					handelAs : 'text/html',
					load : function(data){
						dojo.byId('type_editorial_fields').innerHTML = data;
					}
				});						
			}
		</script>";
		
		return $select;
	}	
	
	public function save_form(){
		$this->parameters["type_editorial_field"] = $this->get_value_from_form("select_field");
		$this->parameters["type_editorial"] = $this->get_value_from_form($this->cms_module_common_selector_type_editorial_type);
		return parent::save_form();
	}
	/*
	 * Retourne la valeur s�lectionn�
	 */
	public function get_value(){
		if(!$this->value){
			$fields = new cms_editorial_parametres_perso($this->parameters["type_editorial"]);
			if($this->parameters['sub_selector']){
				$sub = new $this->parameters['sub_selector']($this->get_sub_selector_id($this->parameters['sub_selector']));
				$fields->get_values($sub->get_value());
				$this->value = $fields->values[$this->parameters['type_editorial_field']];
			}else{
				$this->value = array(
					'type' => $this->parameters['type_editorial'],
					'field' =>$this->parameters['type_editorial_field']
				);
			}
		}
		return $this->value;
	}
}