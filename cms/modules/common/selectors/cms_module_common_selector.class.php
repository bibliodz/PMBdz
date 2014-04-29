<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector.class.php,v 1.14 2013-07-04 12:55:49 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector extends cms_module_root{
	protected $num_cadre_content;
	protected $cadre_parent;
	protected $sub_selectors = array();
	protected $value;
	protected $once_sub_selector=false;
	
	public function __construct($id=0){
		$this->id = $id+0;
		parent::__construct();
	}
	
	protected function get_sub_selectors(){
		return array();
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
			//on va chercher les infos des sous-s�lecteurs...
			$query = "select id_cadre_content, cadre_content_object from cms_cadre_content where cadre_content_type='selector' and cadre_content_num_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row=mysql_fetch_object($result)){
				//	$this->sub_selectors[$row->cadre_content_object] = $row->id_cadre_content+0;				
					$this->sub_selectors[] = array(
						'id' => $row->id_cadre_content+0,
						'name' => $row->cadre_content_object
					);	
				}
			}
		}
	}

	public function get_form(){
		$form ="";		
		$form.=$this->get_hash_form();
		if($this->once_sub_selector==true){
			$sub_selectors = $this->get_sub_selectors();
			if(count($sub_selectors)){
				$form.= "
				<div class='row'>				
					<div class='colonne3'>
						<label for='sub_selector_choice'>".$this->format_text($this->msg['cms_module_common_selector_sub_choice_label'])."</label>
					</div>
					<div class='colonne-suite'>
						<input type='hidden' name='".$this->get_form_value_name("sub_selector_choice_last_value")."' id='".$this->get_form_value_name("sub_selector_choice_last_value")."' value='".($this->parameters['sub_selector'] ? $this->parameters['sub_selector'] : "" )."' />
						<select name='".$this->get_form_value_name("sub_selector_choice")."' id='".$this->get_form_value_name("sub_selector_choice")."' onchange='load_".$this->get_hash()."_sub_selector_form(this.value)'>
							<option value=''>".$this->format_text($this->msg['cms_module_common_selector_sub_choice'])."</option>";
				foreach($sub_selectors as $sub_selector){
					$form.= "								
							<option  value='".$sub_selector."' ".($sub_selector == $this->parameters['sub_selector'] ? "selected='selected'":"").">".$this->format_text($this->msg[$sub_selector])."</option>";
					
						$tab_sub_selector_js.="tab_sub_selector_js['$sub_selector']='".$this->get_sub_selector_id($sub_selector)."';";
				}
				$form.="
						</select>
						<script type='text/javascript'>
							function load_".$this->get_hash()."_sub_selector_form(sub_selector){
								if(sub_selector != ''){
									var tab_sub_selector_js = new Array();
									$tab_sub_selector_js
									
									//on �vite un message d'alerter si le il n'y a encore rien de fait...
									if(document.getElementById('".$this->get_form_value_name("sub_selector_choice_last_value")."').value != ''){
										var confirmed = confirm('".addslashes($this->msg['cms_module_common_selector_confirm_change_selector'])."');
									}else{
										var confirmed = true;
									} 
									if(confirmed){
										document.getElementById('".$this->get_form_value_name("sub_selector_choice_last_value")."').value = sub_selector;
										cms_module_load_elem_form(sub_selector,tab_sub_selector_js[sub_selector] ,'".$this->get_hash()."_sub_selector_form');
									}else{
										var sel = document.getElementById('".$this->get_form_value_name("sub_selector_choice")."');
										for(var i=0 ; i<sel.options.length ; i++){
											if(sel.options[i].value == document.getElementById('".$this->get_form_value_name("sub_selector_choice_last_value")."').value){
												sel.selectedIndex = i;
											}
										}
									}
								}			
							}
						</script>
					</div>
				</div>
				<div id='".$this->get_hash()."_sub_selector_form' dojotype='dojox.layout.ContentPane'></div>
				";
				if($this->parameters['sub_selector'])
				$form.="
					<script type='text/javacsript'>					
						cms_module_load_elem_form('".$this->parameters['sub_selector']."',".$this->get_sub_selector_id($this->parameters['sub_selector']).",'".$this->get_hash()."_sub_selector_form');
					</script>";
			}else{
				$form.= "
					<input type='hidden' name='".$this->get_form_value_name("sub_selector_choice")."' value='".$sub_selectors[0]."'/>";
			}
		}else{
			if(!$this->id){
				foreach($this->get_sub_selectors() as $sub_selector_class){
					$sub_selector = new $sub_selector_class();
					$sub_selector->set_cms_build_env($this->cms_build_env);
					$sub_selector->module_class_name = $this->module_class_name;
					$form.= $sub_selector->get_form();
				}
			}else{
			//	foreach($this->sub_selectors as $class => $id){
				foreach($this->sub_selectors as $sub_select){
					$sub_selector = new $sub_select['name']($sub_select['id']);
					$sub_selector->set_cms_build_env($this->cms_build_env);
					$sub_selector->module_class_name = $this->module_class_name;
					$form.= $sub_selector->get_form();
				}
			}
		}
		return $form;
	}
	public function get_sub_selector_id($name){
		
		for($i=0 ; $i<count($this->sub_selectors) ; $i++){
			if($this->sub_selectors[$i]['name'] ==$name){
				return $this->sub_selectors[$i]['id'];
			}
		}
		return 0;
	}	
	public function save_form(){
		$sub_selector_choice = $this->get_value_from_form("sub_selector_choice");	
		if($sub_selector_choice && $this->once_sub_selector) $this->parameters['sub_selector'] = $sub_selector_choice;
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
			cadre_content_type = 'selector',
			cadre_content_object = '".$this->class_name."',".
			($this->cadre_parent ? "cadre_content_num_cadre = ".$this->cadre_parent."," : "")."		 
			cadre_content_data = '".addslashes($this->serialize())."'".
			($this->num_cadre_content ? ",cadre_content_num_cadre_content = ".$this->num_cadre_content : "")."			 
		".$clause;
		$result = mysql_query($query);
		if($result){
			if(!$this->id){
				$this->id = mysql_insert_id();
			}
			//on enregistre les sous-selecteurs...
			foreach($this->get_sub_selectors() as $sub_selector_class){
				$id=$this->get_sub_selector_id($sub_selector_class);
				$sub_selector = new $sub_selector_class($id);
				$sub_selector->set_parent($this->id);
				$sub_selector->set_cadre_parent($this->cadre_parent);
				$sub_selector->save_form();
			}
			return true;
		}else{
			//cr�ation du s�lecteur rat�e, on supprime le hash de la table...
			$this->delete_hash();
			return false;
		}				
	}

	public function set_parent($id){
		$this->num_cadre_content = $id+0;
	}
	
	public function set_cadre_parent($id){
		$this->cadre_parent = $id+0;
	}
	
	public function delete(){
		if($this->id){
			//on commence par supprimer les sous-selecteurs
			$query = "select id_cadre_content, cadre_content_object from cms_cadre_content where cadre_content_num_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					$sub_selector = new $row->cadre_content_object($row->id_cadre_content);
					$success = $sub_selector->delete();
					if(!$success){
						//TODO verbose mode
						return false;
					}
				}
			}
			//plus de sous-s�lecteurs, �liminons-nous !
			$query = "delete from cms_cadre_content where id_cadre_content = ".$this->id;
			$result = mysql_query($query);
			if($result){
				$this->delete_hash();
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function set_module_class_name($module_class_name){
		$this->module_class_name = $module_class_name;
		$this->fetch_managed_datas();
	}
	
	protected function fetch_managed_datas(){
		//parent::fetch_managed_datas("conditions");
	}
	
	protected function get_exported_datas(){
		$infos = parent::get_exported_datas();
		$infos['type'] = "selector";
		return $infos;
	}
	
	protected function get_selected_sub_selector(){
		return new $this->parameters['sub_selector']($this->get_sub_selector_id($this->parameters['sub_selector']));
	}
}