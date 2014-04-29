<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_record_cp_val.class.php,v 1.1 2012-12-18 09:28:12 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
//require_once($base_path."/cms/modules/common/selectors/cms_module_selector.class.php");
class cms_module_common_selector_record_cp_val extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for=''>".$this->format_text($this->msg['cms_module_common_selector_record_id_record'])."</label>
				</div>
				<div class='colonne-suite'>
					".$this->gen_select()."
				</div>
			</div>";
		$form.=parent::get_form();
		return $form;
	}
	
	public function gen_select(){
		$query = "select idchamp,titre from notices_custom";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$select= "
			<select name='".$this->get_form_value_name("cp")."' onchange='load_cp_val_".$this->get_form_value_name("cp")."(this.value)'>
				<option value='0'>".$this->format_text($this->msg[''])."</option>";	
			while($row = mysql_fetch_object($result)){
				$select.="
				<option value='".$row->idchamp."' ".($row->id_champ == $this->parameters['cp'] ? "selected='selected'" : "").">".$this->format_text($row->titre)."</option>";
			}
			$select.="
			<select>
			<script type='text/javascript'>
				function load_cp_val_".$this->get_form_value_name("cp")."(id_cp){
					dojo.xhrGet({
						url : '".$this->get_ajax_link(array($this->class_name."_hash[]" => $this->hash))."&id_cp='+id_cp,
						handelAs : 'text/html',
						load : function(data){
							dojo.byId('".$this->get_form_value_name("cp")."_values').innerHTML = data;
						}
					});	
				}
			</script>
			<div id='".$this->get_form_value_name("cp")."_values' class='row'></div>";
		}
		return $select;
	}
	
	
	public function save_form(){
		$this->parameters['cp'] = $this->get_value_from_form("cp");
		$this->parameters['cp_val'] = $this->get_value_from_form("cp_val");
		return parent ::save_form();
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
	
	public function execute_ajax(){
		global $id_cp;
		$id_cp+=0;
		if($id_cp){
			$response['content'].="
			<div class='colonne3'>
				<label>".$this->format_text($this->msg['cms_module_common_selector_record_cp_val_cp_val_label'])."</label>
			</div>
			<div class='colonne_suite'>";
			//on regarde la nature du CP...
			$query = "select type from notices_custom where idchamp = '".$id_cp."'";
			$pp = new parametres_perso("notices");
			$pp->get_values(0);
			$response['content'].= $pp->get_field_form($id_cp);
			$response['content'].="
			</div>";
			
		}else $response['content'] = "";
		$response['content-type'] = "text/html";
		return $response;
	}
}