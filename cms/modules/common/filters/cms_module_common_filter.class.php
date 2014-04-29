<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_filter.class.php,v 1.2 2013-04-10 16:55:06 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_filter extends cms_module_root{
	protected $cadre_parent;
	protected $selectors=array();
		
	public function __construct($id=0){
		$this->id = $id+0;
		parent::__construct();
	}
	
	public function get_available_selectors(){
		return array();
	}
	
	public function get_filter_from_selectors(){
		return array();
	}

	public function get_filter_by_selectors(){
		return array();
	}
	
	public function set_cadre_parent($id){
		$this->cadre_parent = $id+0;
	}
	
	/*
	 * R�cup�ration des informations en base
	 */
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
			$this->selectors = $this->parameters['selectors'];
		}
	}
	
	/*
	 * M�thode de g�n�ration du formulaire... 
	 */
	public function get_form(){
		$selectors_by = $this->get_filter_by_selectors();
		$selectors_from = $this->get_filter_from_selectors();
		$form=$this->get_hash_form();
		//on commence avec la valeur � comparer (filter_from)
		$form.= "
		<div class='row'>";
		$form.= $this->get_selectors_form("from");
		if($this->parameters['selector']['from']!= "" || count($selectors_from)==1){
			$current_selector_id = 0;
			if($this->parameters['selector']['from']!= ""){
				for($i=0 ; $i<count($this->selectors['from']) ; $i++){
					if($this->selectors['from'][$i]['name'] == $this->parameters['selector']['from']){
						$selector_id = $this->selectors['from'][$i]['id'];
						break;
					}
				}
				$selector_name= $this->parameters['selector']['from'];
			}else if(count($selectors_from)==1){
				$selector_name= $selectors_from[0];
			}
			$form.="
			<script type='text/javacsript'>
			cms_module_load_elem_form('".$selector_name."','".$selector_id."','".$this->get_form_value_name("selector_from_form")."');
			</script>";
		}
		$form.="
		</div>
		<div class='row'>
		<label>".$this->format_text($this->msg['cms_module_common_filter_compare_from'])."</label>
		</div>
		<div class='row'>
		<div id='".$this->get_form_value_name("selector_from_form")."' dojoType='dojox.layout.ContentPane'></div>
		</div>";
		
		//on continue avec la valeur � laquelle comparer (filter_by)!
		$form.= "
				<div class='row'>
					<label>".$this->format_text($this->msg['cms_module_common_filter_compare_with'])."</label>
				</div>
	  			<div class='row'>";
		$form.= $this->get_selectors_form("by");
		if($this->parameters['selector']['by']!= "" || count($selectors_by)==1){
			$current_selector_id = 0;
			if($this->parameters['selector']['by']!= ""){
				for($i=0 ; $i<count($this->selectors['by']) ; $i++){
					if($this->selectors['by'][$i]['name'] == $this->parameters['selector']['by']){
						$selector_id = $this->selectors['by'][$i]['id'];
							break;
					}
				}
				$selector_name= $this->parameters['selector']['by'];
			}else if(count($selectors_by)==1){
 				$selector_name= $selectors_by[0];
 			}
 			$form.="
	 			 	<script type='text/javacsript'>
	 			 		cms_module_load_elem_form('".$selector_name."','".$selector_id."','".$this->get_form_value_name("selector_by_form")."');
	 			 	</script>";
		}
		$form.="
				</div>
				
				<div class='row'>
	 				<div id='".$this->get_form_value_name("selector_by_form")."' dojoType='dojox.layout.ContentPane'></div>
	 			</div>";
		return $form;
	}	
	
	protected function get_selectors_form($type){
		switch($type){
			case "from" :
				$selectors = $this->get_filter_from_selectors();
				break;
			case "by" :
				$selectors = $this->get_filter_by_selectors();
				break;
		}
		
		if(count($selectors)>1){
			$form = "
			<select name='".$this->get_form_value_name("selector_".$type."_choice")."' onchange='cms_module_load_elem_form(this.value,0,\"".$this->get_form_value_name("selector_by_form")."\");'>";
			foreach($selectors as $selector){
				$form.= "
				<option value='".$selector."' ".($this->parameters['selector'][$type] == $selector ? "selected='selected'" : "").">".$this->format_text($this->msg[$selector])."</option>";
			}
			$form.= "
			</select>";
		}else{
			$form = "
			<input type='hidden' name='".$this->get_form_value_name("selector_".$type."_choice")."' value='".$selectors[0]."'/>";
		}
		return $form;
	}
		
	/*
	 * Sauvegarde des infos depuis un formulaire...
	 */
	public function save_form(){
		
		$this->parameters['selector']['by'] = $this->get_value_from_form("selector_by_choice");
		$this->parameters['selector']['from'] = $this->get_value_from_form("selector_from_choice");
				
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
			cadre_content_type = 'filter',
			cadre_content_object = '".$this->class_name."',".
			($this->cadre_parent ? "cadre_content_num_cadre = ".$this->cadre_parent."," : "")."		
			cadre_content_data = '".addslashes($this->serialize())."'
			".$clause;
		$result = mysql_query($query);
		
		if($result){
			if(!$this->id){
				$this->id = mysql_insert_id();
			}
			//on supprime les anciens filtres
			$query = "delete from cms_cadre_content where id_cadre_content != ".$this->id." and cadre_content_type='filter' and cadre_content_num_cadre = ".$this->cadre_parent;
			mysql_query($query);
			//s�lecteur
			$selector_by_id = $selector_from_id = 0;
			for($i=0 ; $i<count($this->selectors['by']) ; $i++){
				if($this->parameters['selector']['by'] == $this->selectors['by'][$i]['name']){
					$selector_by_id = $this->selectors['by'][$i]['id'];
					break;
				}
			}
			for($i=0 ; $i<count($this->selectors['from']) ; $i++){
				if($this->parameters['selector']['from'] == $this->selectors['from'][$i]['name']){
					$selector_from_id = $this->selectors['from'][$i]['id'];
					break;
				}
			}
			if($this->parameters['selector']['by'] && $this->parameters['selector']['from']){
				$selector_from = new $this->parameters['selector']['from']($selector_from_id);
				$selector_from->set_parent($this->id);
				$selector_from->set_cadre_parent($this->cadre_parent);
				$result = $selector_from->save_form();
				if($result){
					if($selector_from_id==0){
						$this->selectors['from'][] = array(
								'id' => $selector_from->id,
								'name' => $this->parameters['selector']['from']
						);
					}
					$selector_by = new $this->parameters['selector']['by']($selector_by_id);
					$selector_by->set_parent($this->id);
					$selector_by->set_cadre_parent($this->cadre_parent);
					$result = $selector_by->save_form();
					if($result){
						if($selector_by_id==0){
							$this->selectors['by'][] = array(
									'id' => $selector_by->id,
									'name' => $this->parameters['selector']['by']
							);
						}
						
						//on a tout sauvegard�, on garde la trace dans le filtre pour pas tout chamboul� dans les s�lecteurs...
						$this->parameters['selectors'] = $this->selectors;
						mysql_query("update cms_cadre_content set cadre_content_data = '".addslashes($this->serialize())."' where id_cadre_content=".$this->id);
						return true;
					}else{
						$this->delete_hash();
						return false;
					}
				}else{
					$this->delete_hash();
					return false;
				}
			}else{
				return true;
			}
		}else{
			//cr�ation de la source de donn�e rat�e, on supprime le hash de la table...
			$this->delete_hash();
			return false;
		}
	}

	/*
	 * M�thode de suppression
	 */
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
	}
	
	public function get_headers(){
		$headers=array();
		if($this->parameters['selector']){
			$selector = $this->get_selected_selector();
			$headers = array_merge($headers,$selector->get_headers());
			$headers = array_unique($headers);
		}	
		return $headers;
	}
	
	protected function get_selected_selector($origin){
		//on va chercher
		if($this->parameters['selector'][$origin]!= ""){
			$current_selector_id = 0;
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$origin][$i]['name'] == $this->parameters['selector'][$origin]){
					return new $this->selectors[$origin][$i]['name']($this->selectors[$origin][$i]['id']);
				}
			}
		}else{
			return false;
		}
	}

	public function set_module_class_name($module_class_name){
		$this->module_class_name = $module_class_name;
	}

	protected function get_exported_datas(){
		$infos = parent::get_exported_datas();
		$infos['type'] = "filter";
		return $infos;
	}

	public function filter($datas){
		$filtered_datas= array();
		//on r�cup�re le champ � tester...
		$selector_from = $this->get_selected_selector("from");
		$field_from = $selector_from->get_value();
		//a quoi...
		$selector_by = $this->get_selected_selector("by");
		$field_by = $selector_by->get_value();
		if($field_by){
			$fields = new cms_editorial_parametres_perso($field_from['type']);
			foreach($datas as $article_id){
				$fields->get_values($article_id);
				if(in_array($field_by,$fields->values[$field_from['field']])){
					$filtered_datas[] = $article_id;
				}
			}
		}else{
			//pas de valeur pour le filtre, on filtre pas...
			$filtered_datas=$datas;
		}
		return $filtered_datas;
	}
}