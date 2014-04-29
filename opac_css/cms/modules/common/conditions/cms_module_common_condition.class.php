<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_condition.class.php,v 1.12 2013-09-17 10:26:52 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_condition extends cms_module_root{
	protected $cadre_parent;
	protected $module_class_name;
	
	public function __construct($id=0){
		$this->id = $id+0;
		parent::__construct();
	}
	
	protected function fetch_datas(){
		if($this->id){
			//on commence par aller chercher ses infos
			$query = " select id_cadre_content, cadre_content_hash, cadre_content_num_cadre, cadre_content_data from cms_cadre_content where id_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$this->id = $row->id_cadre_content+0;
				$this->hash = $row->cadre_content_hash;
				$this->cadre_parent = $row->cadre_content_num_cadre+0;
				$this->unserialize($row->cadre_content_data);
			}
			//on va chercher les infos des s�lecteurs...
			$query = "select id_cadre_content, cadre_content_object from cms_cadre_content where cadre_content_type='selector' and cadre_content_num_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row=mysql_fetch_object($result)){
					$this->selectors[] = array(
						'id' => $row->id_cadre_content+0,
						'name' => $row->cadre_content_object
					);	
				}
			}	
		}
	}
	
	public function set_module_class_name($module_class_name){
		$this->module_class_name = $module_class_name;
		$this->fetch_managed_datas();
	}
	
	public static function is_loadable_default(){
		return false;
	}
			
	public function set_cadre_parent($id){
		$this->cadre_parent = $id+0;
	}	
	
	public function get_available_selectors(){
		return array();
	}
	
	public function get_form(){
		$selectors = $this->get_available_selectors();
		$form = $this->get_hash_form();
		$form.= "
		
			<input type='hidden' name='cms_module_common_module_conditions[]' value='".$this->class_name."'/>
			<div class='row'>";
		$form.= $this->get_selectors_list_form();
		if($this->parameters['selector']!= "" || count($selectors)==1){
			$selector_id = 0;
			if($this->parameters['selector']!= ""){
				for($i=0 ; $i<count($this->selectors) ; $i++){
					if($this->selectors[$i]['name'] == $this->parameters['selector']){
						$selector_id = $this->selectors[$i]['id'];
						break;
					}
				}
				$selector_name = $this->parameters['selector'];
			}else if(count($selectors)==1){
				$selector_name = $selectors[0];
			}
			$form.="
			<script type='text/javacsript'>
				cms_module_load_elem_form('".$selector_name."','".$selector_id."','".$this->class_name."_selector_form');
			</script>";
		}
		$form.="
				<div id='".$this->class_name."_selector_form' dojoType='dojox.layout.ContentPane'>
				</div>
			</div>";
		return $form;
	}
	
	protected function get_selectors_list_form(){
		$selectors = $this->get_available_selectors();
		if(count($selectors)>1){
			$form= "
				<div class='colonne3'>
					<label for='".$this->class_name."_selector_choice'>".$this->format_text($this->msg['cms_module_common_condition_selector_choice'])."</label>
				</div>
				<div class='colonne3'>
					<input type='hidden' name='".$this->class_name."_selector_choice_last_value' id='".$this->class_name."_selector_choice_last_value' value='".($this->parameters['selector'] ? $this->parameters['selector'] : "" )."' />
					<select name='".$this->class_name."_selector_choice' id='".$this->class_name."_selector_choice' onchange='load_".$this->class_name."_selector_form(this.value)'>
						<option value=''>".$this->format_text($this->msg['cms_module_common_condition_selector_choice'])."</option>";
			foreach($selectors as $selector){
				$form.= "
						<option value='".$selector."' ".($selector == $this->parameters['selector'] ? "selected='selected'":"").">".$this->format_text($this->msg[$selector])."</option>";
			}
			$form.="
					</select>
					<script type='text/javascript'>
						function load_".$this->class_name."_selector_form(selector){
							if(selector != ''){
								//on �vite un message d'alerter si le il n'y a encore rien de fait...
								if(document.getElementById('".$this->class_name."_selector_choice_last_value').value != ''){
									var confirmed = confirm('".addslashes($this->msg['cms_module_common_condition_selector_confirm_change_selector'])."');
								}else{
									var confirmed = true;
								} 
								if(confirmed){
									document.getElementById('".$this->class_name."_selector_choice_last_value').value = selector;
									cms_module_load_elem_form(selector,0,'selector_form');
								}else{
									var sel = document.getElementById('".$this->class_name."_selector_choice');
									for(var i=0 ; i<sel.options.length ; i++){
										if(sel.options[i].value == document.getElementById('".$this->class_name."_selector_choice_last_value').value){
											sel.selectedIndex = i;
										}
									}
								}
							}			
						}
					</script>
				</div>";			
		}else{
			$form = "
				<div class='colonne3'>&nbsp;</div>
				<div class='colonne3'>&nbsp;
					<input type='hidden' name='".$this->class_name."_selector_choice' value='".$selectors[0]."'/>
				</div>";
		}
		$form.="
				<div class='colonne-suite'>
					<input type='button' class='bouton' value='X' onclick='destroy_condition(this);'/>
				</div>
				<script type='text/javascript'> 
					function destroy_condition(node){
						dojo.xhrGet({
							url : './ajax.php?module=cms&categ=module&elem=".$this->class_name."&action=delete&id=".$this->id."'
						});
						var content = dijit.byId(node.parentNode.parentNode.parentNode.id);
						if(content){
							content.destroyRecursive(false);
						}
					}
				</script>";
		return $form;
	}
	
	public function save_form(){
		$selector_choice = $this->class_name."_selector_choice";
		global $$selector_choice;

		$this->parameters['selector'] = $$selector_choice;
		
		$this->get_hash();
		if($this->id){
			$query = "update cms_cadre_content set";
			$clause = " where id_cadre_content=".$this->id;
		}else{
			$query = "insert into cms_cadre_content set";
			$clause = "";
		}
		$query.= " 
			cadre_content_hash = '".$this->hash."',
			cadre_content_type = 'condition',
			cadre_content_object = '".$this->class_name."',".
			($this->cadre_parent ? "cadre_content_num_cadre = ".$this->cadre_parent."," : "")."		
			cadre_content_data = '".addslashes($this->serialize())."'
			".$clause;
		$result = mysql_query($query);
		
		if($result){
			if(!$this->id){
				$this->id = mysql_insert_id();
			} 
			//s�lecteur
			$selector_id = 0;
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($$selector_choice == $this->selectors[$i]['name']){
					$selector_id = $this->selectors[$i]['id'];
					break;
				}
			}
			$selector = new $$selector_choice($selector_id);
			$selector->set_parent($this->id);
			$selector->set_cadre_parent($this->cadre_parent);
			$result = $selector->save_form();
			if($result){
				if($selector_id==0){
					$this->selectors[] = array(
						'id' => $selector->id,
						'name' => $$selector_choice
					);
				}
				return true;	
			}else{
				//cr�ation de la source de donn�e rat�e, on supprime le hash de la table...
				$this->delete_hash();
				return false;
			}
		}else{
			//cr�ation de la source de donn�e rat�e, on supprime le hash de la table...
			$this->delete_hash();		
			return false;
		}		
	}
	
	protected function get_selected_selector(){
		//on va chercher
		if($this->parameters['selector']!= ""){
			$current_selector_id = 0;
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$i]['name'] == $this->parameters['selector']){
					return new $this->parameters['selector']($this->selectors[$i]['id']);
				}
			}
		}else{
			return false;
		}
	}
	
	public function delete(){
		if($this->id){
			//on commence par �liminer le s�lecteur associ�...
			$query = "select id_cadre_content,cadre_content_object from cms_cadre_content where cadre_content_num_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				//la logique voudrait qu'il n'y ai qu'un seul s�lecteur (enfin sous-�l�ment, la conception peut �voluer...), mais sauvons les brebis �gar�es...
				while($row = mysql_fetch_object($result)){
					$sub_elem = new $row->cadre_content_object($row->id_cadre_content);
					$success = $sub_elem->delete();
					if(!$success){
						//TODO verbose mode
						return false;
					}
				}
			}
			//on est tout seul, �liminons-nous !
			$query = "delete from cms_cadre_content where id_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if($result){
				$this->delete_hash();
				return true;
			}else{
				return false;
			}
		}
		//on est tout seul, �liminons-nous !
		$query = "delete from cms_cadre_content where id_cadre_content = ".$this->id;
		$result = mysql_query($query);
		if($result){
			$this->delete_hash();
			return true;
		}else{
			return false;
		}		
	}
	
	protected function fetch_managed_datas(){
		parent::fetch_managed_datas("conditions");
	}
	
	protected function get_exported_datas(){
		$infos = parent::get_exported_datas();
		$infos['type'] = "condition";
		return $infos;
	}
		
	//fonction qui d�termine si un cadre utilisant cette condition peut �tre cach�!
	public static function use_cache(){
		return true;
	}
}