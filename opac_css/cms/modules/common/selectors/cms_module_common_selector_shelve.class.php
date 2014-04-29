<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_shelve.class.php,v 1.3 2013-07-05 08:25:15 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
//require_once($base_path."/cms/modules/common/selectors/cms_module_selector.class.php");
class cms_module_common_selector_shelve extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
		if (!is_array($this->parameters)) $this->parameters = array();
	}
	
	public function get_form(){
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_selector_shelve_id_shelve'>".$this->format_text($this->msg['cms_module_common_selector_shelve_id_shelve'])."</label>
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
		$this->parameters = $this->get_value_from_form("id_shelve");
		return parent ::save_form();
	}
	
	protected function gen_select(){
		$query= "select idetagere, name from etagere where (validite = 1) or (now() <= validite_date_fin and now()>= validite_date_deb)";
		$result = mysql_query($query);
		$select = "
					<select name='".$this->get_form_value_name("id_shelve")."[]'>";
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$select.="
						<option value='".$row->idetagere."' ".(in_array($row->idetagere, $this->parameters)  ? "selected='selected'" : "").">".$this->format_text($row->name)."</option>";
			}
		}else{
			$select.= "
						<option value ='0'>".$this->format_text($this->msg['cms_module_common_selector_shelve_no_shelve'])."</option>";
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