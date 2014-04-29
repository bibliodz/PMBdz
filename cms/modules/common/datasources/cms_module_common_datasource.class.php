<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource.class.php,v 1.27 2014-03-14 10:06:44 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource extends cms_module_root{
	protected $cadre_parent;
	protected $selectors=array();
	private $used_external_filter = false;
	private $external_filter;
		
	public function __construct($id=0){
		$this->id = $id+0;
		parent::__construct();
	}
	
	public function get_available_selectors(){
		return array();
	}
	
	public function set_cadre_parent($id){
		$this->cadre_parent = $id+0;
	}
	
	public function set_filter($filter){
		$this->used_external_filter = true;
		$this->external_filter = $filter;
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
			//on va chercher les infos des s�lecteurs...
			$query = "select id_cadre_content, cadre_content_object from cms_cadre_content where cadre_content_type='selector' and cadre_content_num_cadre != 0 and cadre_content_num_cadre_content = ".$this->id;
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
	
	/*
	 * M�thode de g�n�ration du formulaire... 
	 */
	public function get_form(){
		$selectors = $this->get_available_selectors();
		
		$form = "
			<div class='row'>";
		$form.=$this->get_hash_form();
		$form.= $this->get_selectors_list_form();
		
		if($this->parameters['selector']!= "" || count($selectors)==1){
			$current_selector_id = 0;
			if($this->parameters['selector']!= ""){
				for($i=0 ; $i<count($this->selectors) ; $i++){
					if($this->selectors[$i]['name'] == $this->parameters['selector']){
						$selector_id = $this->selectors[$i]['id'];
						break;
					}
				}
				$selector_name= $this->parameters['selector'];
			}else if(count($selectors)==1){
				$selector_name= $selectors[0];
			}
			$form.="
			<script type='text/javacsript'>
				cms_module_load_elem_form('".$selector_name."','".$selector_id."','selector_form');
			</script>";
		}
		$form.="
				<div id='selector_form' dojoType='dojox.layout.ContentPane'>
				</div>
			</div>";
		return $form;
	}	
	
	/*
	 * Formulaire de s�lection d'un s�lecteur
	 */
	protected function get_selectors_list_form(){
		$selectors = $this->get_available_selectors();
		if(count($selectors)>1){
			$form= "
				<div class='colonne3'>
					<label for='selector_choice'>".$this->format_text($this->msg['cms_module_common_datasource_selector_choice'])."</label>
				</div>
				<div class='colonne-suite'>
					<input type='hidden' name='selector_choice_last_value' id='selector_choice_last_value' value='".($this->parameters['selector'] ? $this->parameters['selector'] : "" )."' />
					<select name='selector_choice' id='selector_choice' onchange='load_selector_form(this.value)'>
						<option value=''>".$this->format_text($this->msg['cms_module_common_datasource_selector_choice'])."</option>";
			foreach($selectors as $selector){
				$form.= "
						<option value='".$selector."' ".($selector == $this->parameters['selector'] ? "selected='selected'":"").">".$this->format_text($this->msg[$selector])."</option>";
			}
			$form.="
					</select>
					<script type='text/javascript'>
						function load_selector_form(selector){
							if(selector != ''){
								//on �vite un message d'alerter si le il n'y a encore rien de fait...
								if(document.getElementById('selector_choice_last_value').value != ''){
									var confirmed = confirm('".addslashes($this->msg['cms_module_common_selector_confirm_change_selector'])."');
								}else{
									var confirmed = true;
								} 
								if(confirmed){
									document.getElementById('selector_choice_last_value').value = selector;
									cms_module_load_elem_form(selector,0,'selector_form');
								}else{
									var sel = document.getElementById('selector_choice');
									for(var i=0 ; i<sel.options.length ; i++){
										if(sel.options[i].value == document.getElementById('selector_choice_last_value').value){
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
				<input type='hidden' name='selector_choice' value='".$selectors[0]."'/>";
		}
		return $form;
	}
	
	/*
	 * Sauvegarde des infos depuis un formulaire...
	 */
	public function save_form(){
		global $selector_choice;
		
		$this->parameters['selector'] = $selector_choice;
				
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
			cadre_content_type = 'datasource',
			cadre_content_object = '".$this->class_name."',".
			($this->cadre_parent ? "cadre_content_num_cadre = ".$this->cadre_parent."," : "")."		
			cadre_content_data = '".addslashes($this->serialize())."'
			".$clause;
		$result = mysql_query($query);
		
		if($result){
			if(!$this->id){
				$this->id = mysql_insert_id();
			}
			//on supprime les anciennes sources de donn�es...
			$query = "delete from cms_cadre_content where id_cadre_content != ".$this->id." and cadre_content_type='datasource' and cadre_content_num_cadre = ".$this->cadre_parent;
			mysql_query($query);
			//s�lecteur
			$selector_id = 0;
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->parameters['selector'] == $this->selectors[$i]['name']){
					$selector_id = $this->selectors[$i]['id'];
					break;
				}
			}
			if($this->parameters['selector']){
				$selector = new $this->parameters['selector']($selector_id);
				$selector->set_parent($this->id);
				$selector->set_cadre_parent($this->cadre_parent);
				$result = $selector->save_form();
				if($result){
					if($selector_id==0){
						$this->selectors[] = array(
							'id' => $selector->id,
							'name' => $this->parameters['selector']
						);
					}
					return true;	
				}else{
					//cr�ation de la source de donn�e rat�e, on supprime le hash de la table...
					$this->debug(sprintf($this->msg['cms_module_commom_datasource_selector_save_error'],$this->parameters['selector']),CMS_DEBUG_MODE_FILE);
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
	
	/*
	 * M�thode pour renvoyer les donn�es tel que d�fini par le s�lecteur
	 */
	public function get_datas(){
		
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
	
	public function set_module_class_name($module_class_name){
		$this->module_class_name = $module_class_name;
		$this->fetch_managed_datas();
	}
	
	protected function fetch_managed_datas(){
		parent::fetch_managed_datas("datasources");
	}
	
	protected function get_exported_datas(){
		$infos = parent::get_exported_datas();
		$infos['type'] = "datasource";
		return $infos;
	}
		
	/*
	 * M�thode pour filtrer les r�sultats en fonction de la visibilit�
	 */
	protected function filter_datas($type,$datas){
		//la m�thode g�n�rique permet de filter les entit�s de base...
		switch($type){
			case "notices" :
				$result = $this->filter_notices($datas);
				break;
			case "articles" :
				$result = $this->filter_articles($datas);
				break;
			case "sections" :
				$result = $this->filter_sections($datas);
				break;
			default :
				//si on est pas avec une entit� connue, on s'en charge quand m�me...
				if(method_exists($this,"filter_".$type)){
					$result = call_user_func(array($this,"filter_".$type),$datas);
				}else{
					$result = $datas;
				}
				break;	
		}
		if ($this->used_external_filter){
			$result = $this->external_filter->filter($result);
		}
		if(!$result){
			$result=array();
		}
		return $result;
	}

	protected function filter_notices($datas){
		return $datas;
	}

	protected function filter_articles($datas){
		$valid_datas = $valid_datas = array();
		//quand on filtre un article, on cherche d�j� si la rubrique parente est visible...
		$valid_sections = $sections = array();
		$query = "select distinct num_section from cms_articles where id_article in (".implode(",",$datas).")";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$sections[] = $row->num_section;
			}
			$valid_sections = $this->filter_sections($sections);
		}
		
		$clause_date = "
		((article_start_date != 0 and to_days(article_start_date)<=to_days(now()) and to_days(article_end_date)>=to_days(now()))||(article_start_date != 0 and article_end_date =0 and to_days(article_start_date)<=to_days(now()))||(article_start_date=0 and article_end_date=0)||(article_start_date = 0 and to_days(article_end_date)>=to_days(now())))";
		
		
		if(count($valid_sections)){
			$query = "select id_article from cms_articles 
				join cms_editorial_publications_states on id_publication_state = article_publication_state 
				where num_section in (".implode(",",$valid_sections).") and id_article in (".implode(",",$datas).") and editorial_publication_state_opac_show = 1".(!$_SESSION['id_empr_session'] ? " and editorial_publication_state_auth_opac_show = 0" : "")." and ".$clause_date;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					$valid_datas[]=$row->id_article;
				}
			}
			foreach($datas as $article_id){
				if(in_array($article_id,$valid_datas)){
					$articles[] = $article_id;
				}
			}
		}
		return $articles;
	}
	
	protected function filter_sections($datas){
		$valid_datas = array();
		//on initialise un arbre avec les sections
		
		$paths = array();
		$tree = $this->get_sections_tree(0,"",$paths);		
		$nb_days_since_1970 = 719528;
		$nb_days_today = round((time()/(3600*24)))+$nb_days_since_1970;
		foreach($datas as $id_section){
			$valid = 1;
			$section_path_ids = explode("/",$paths[$id_section]);
			$array_path = "";
			$current_tree = $tree[$section_path_ids[0]];
			//v�rification sur le statut
			if(!($current_tree['opac_show'] && (!$current_tree['auth_opac_show'] || ($current_tree['auth_opac_show'] & $_SESSION['id_empr_session'])))){
				$valid = 0;
			}else{
				//v�rification sur les dates...
				if($current_tree['start_day']!= 0 && $current_tree['end_day']!=0 && ($current_tree['start_day']>$nb_days_today || $current_tree['end_day']<$nb_days_today)){
					$valid = 0;
				}else if ($current_tree['start_day']!=0 && !$current_tree['end_day'] && $current_tree['start_day']>$nb_days_today){
					$valid = 0;
				}else if ($current_tree['end_day']!=0 && !$current_tree['start_day'] && $current_tree['end_day']<$nb_days_today){
					$valid = 0;
				}
			}
			
			for($i=1 ; $i< count($section_path_ids) ; $i++){
				if($valid){
					$current_tree = $current_tree['children'][$section_path_ids[$i]];
					//v�rification sur le statut
					if(!($current_tree['opac_show'] && (!$current_tree['auth_opac_show'] || ($current_tree['auth_opac_show'] & $_SESSION['id_empr_session'])))){
						$valid = 0;
					}else{
						//v�rification sur les dates...
						if($current_tree['start_day']!= 0 && $current_tree['end_day']!=0 && ($current_tree['start_day']>$nb_days_today || $current_tree['end_day']<$nb_days_today)){
							$valid = 0;
						}else if ($current_tree['start_day']!=0 && !$current_tree['end_day'] && $current_tree['start_day']>$nb_days_today){
							$valid = 0;
						}else if ($current_tree['end_day']!=0 && !$current_tree['start_day'] && $current_tree['end_day']<$nb_days_today){
							$valid = 0;
						}
					}
				}else{
					break;
				}
			}
			if($valid){
				$valid_datas[]=$id_section;
			}
		}
		return $valid_datas;
	}
	
	protected function get_sections_tree($id_parent = 0,$path="",&$paths){
		$id_parent+=0;
		$tree = array();
		
		$clause = "((section_start_date != 0 and section_start_date<now() and section_end_date>now())||(section_start_date != 0 and section_end_date =0 and section_start_date <now())||(section_start_date = 0 and section_end_date>now()))";
		
		$query="select id_section,to_days(section_start_date) as start_day, to_days(section_end_date) as end_day , editorial_publication_state_opac_show,editorial_publication_state_auth_opac_show from cms_sections join cms_editorial_publications_states on id_publication_state = section_publication_state where section_num_parent = ".$id_parent;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$paths[$row->id_section] = ($path ? $path."/" : "").$row->id_section ;
				$tree[$row->id_section] = array(
					'start_day' => $row->start_day,
					'end_day' => $row->end_day,
					'opac_show'=> $row->editorial_publication_state_opac_show,
					'auth_opac_show'=> $row->editorial_publication_state_auth_opac_show,
					'children' => $this->get_sections_tree($row->id_section,($path ? $path."/" : "").$row->id_section,$paths)
				);
			}
		}
		return $tree;
	}
	
	public function get_format_data_structure(){
		return array();
	}
}