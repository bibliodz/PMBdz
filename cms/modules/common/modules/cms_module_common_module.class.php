<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_module.class.php,v 1.56 2014-02-19 15:12:22 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_module extends cms_module_root{
	protected $module_path = "";
	protected $manifest;
	public $informations = array();
	public $elements_used = array();
	public $name= "";
	public $styles;
	public $dom_parent;
	public $dom_after;
	protected $datasource = array();
	protected $filter = array();
	protected $view = array();
	protected $conditions = array();
	protected $managed_datas;
	public $fixed = false;
	protected $extension_datas = array();
	protected $modcache = "get_post_view";
	
	public function __construct($id=0){
		$this->id = $id+0;
		$infos = self::read_manifest();
		$this->informations = $infos['informations'];
		$this->elements_used = $infos['elements_used'];
		parent::__construct();
		//on va chercher le contenu de la boite noire...
		$this->fetch_managed_datas();
	}
	
	public static function get_informations(){
		$infos = self::read_manifest();
		return $infos['informations'];
	}
	
	public static function read_manifest(){
		global $base_path;
		$informations = array();
		@ini_set("zend.ze1_compatibility_mode", "0");
		$manifest = new domDocument();
		$module_path = realpath(dirname($base_path."/cms/modules/".str_replace("cms_module_","",get_called_class())."/".get_called_class().".class.php"));
		
		$manifest->load($module_path."/manifest.xml");
		//on r�cup�re le nom
		$name = $manifest->getElementsByTagName("name")->item(0);
		$informations['informations']['name']= cms_module_root::charset_normalize($name->nodeValue,"utf-8");
		//on r�cup�re le(les) auteur(s)
		$informations['informations']['author'] = array();
		$authors = $manifest->getElementsByTagName("author");
		for($i=0 ; $i<$authors->length ; $i++){
			$author = array();
			//on r�cup�re son nom
			$author['name'] = cms_module_root::charset_normalize($authors->item($i)->getElementsByTagName('name')->item(0)->nodeValue,"utf-8");
			//on r�cup�re son organisation
			$organisation = $authors->item($i)->getElementsByTagName("organisation");
			if($organisation->length>0){
				$author['organisation'] = cms_module_root::charset_normalize($organisation->item(0)->nodeValue,"utf-8");
			}
			$informations['informations']['author'][] = $author;
		}
		
		//on r�cup�re les dates
		$created_date = $manifest->getElementsByTagName("created_date")->item(0);
		$informations['informations']['created_date']= cms_module_root::charset_normalize($created_date->nodeValue,"utf-8");
		$updated_date = $manifest->getElementsByTagName("updated_date");
		if($updated_date->length>0){
			$informations['informations']['updated_date'] = cms_module_root::charset_normalize($updated_date->item(0)->nodeValue,"utf-8");
		}
		//on r�cup�re la version
		$version = $manifest->getElementsByTagName("version")->item(0);
		$informations['informations']['version']= cms_module_root::charset_normalize($version->nodeValue,"utf-8");
		
		// on r�cup�re la langue par d�faut du module...
		$informations['informations']['default_language'] = self::get_module_default_language($manifest);
		
		// administrable?
		$informations['informations']['managed'] = ($manifest->getElementsByTagName("managed") && $manifest->getElementsByTagName("managed")->item(0)->nodeValue == "true" ? true : false);
		
		//fournisseur de liens?
		$informations['informations']['extension_form'] = ($manifest->getElementsByTagName("extension_form") && $manifest->getElementsByTagName("extension_form")->item(0)->nodeValue == "true" ? true : false);
		
		
		@ini_set("zend.ze1_compatibility_mode", "0");
		//on r�cup�re la listes des �l�ments utilis�s par le module...
		$use = $manifest->getElementsbyTagName("use")->item(0);
		$informations['elements_used'] = self::read_elements_used($use);
		@ini_set("zend.ze1_compatibility_mode", "1");
		return $informations;
	}
		
	protected function fetch_datas(){
		if($this->id){
			$this->classement_list=array();
			//on va cherches les infos du cadres...
			$query = "select * from cms_cadres where id_cadre = ".$this->id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$this->id = $row->id_cadre+0;
				$this->hash = $row->cadre_hash;
				$this->name = $row->cadre_name;
				$this->fixed = $row->cadre_fixed;
				$this->styles = unserialize($row->styles);
				$this->dom_parent = $row->cadre_dom_parent;
				$this->dom_after = $row->cadre_dom_after;
				$this->memo_url = $row->cadre_memo_url;
				$this->cadre_url = $row->cadre_url;				
				$this->classement = $row->cadre_classement;	
				$this->modcache = $row->cadre_modcache;
				$query = "select id_cadre_content,cadre_content_object,cadre_content_type from cms_cadre_content where cadre_content_num_cadre = ".$this->id;
				$result = mysql_query($query);
				if($result && mysql_num_rows($result)){
					while ($ligne=mysql_fetch_object($result)) {
						switch ($ligne->cadre_content_type) {
							case "datasource":
					$this->datasource = array(
									'id' => $ligne->id_cadre_content+0,
									'name' => $ligne->cadre_content_object
					);
								break;
							case "filter":
					$this->filter = array(
									'id' => $ligne->id_cadre_content+0,
									'name' => $ligne->cadre_content_object
					);
								break;
							case "view":
					$this->view = array(
									'id' => $ligne->id_cadre_content+0,
									'name' => $ligne->cadre_content_object
					);
								break;
							case "condition":
						$this->conditions[] = array(
									'id' => $ligne->id_cadre_content+0,
									'name' => $ligne->cadre_content_object
						);
														break;
							default:
								break;
						}
					}
				}
			}
		}
	}
	
	static function read_elements_used($use_node){
		@ini_set("zend.ze1_compatibility_mode", "0");
		$elements_used = array();
		$types = array(
			'condition',
			'view',
			'datasource',
			'filter'
		);
		foreach($types as $type){
			$elements = $use_node->getElementsByTagName($type);
			$elements_used[$type] = array();
			if($elements->length>0){
				for($i=0 ; $i<$elements->length ; $i++){
					if($elements->item($i)->nodeValue != ""){
						$elements_used[$type][] = $elements->item($i)->nodeValue;
					}
				}
			}
		}
		//certaines conditions sont par d�faut dans tous les modules...
		if(!in_array("cms_module_common_condition_authentificated",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_authentificated";
		if(!in_array("cms_module_common_condition_page",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_page";
		if(!in_array("cms_module_common_condition_lvl",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_lvl";
		if(!in_array("cms_module_common_condition_global_var",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_global_var";
		if(!in_array("cms_module_common_condition_global_var_value",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_global_var_value";
		if(!in_array("cms_module_common_condition_view",$elements_used['condition'])) $elements_used['condition'][] = "cms_module_common_condition_view";
		
		@ini_set("zend.ze1_compatibility_mode", "1");
		return $elements_used;
	}
	
	static function get_elements_used($file=""){
		@ini_set("zend.ze1_compatibility_mode", "0");
		//on r�cup�re la partie int�ressante du manifest...
		$dom = new domDocument();
		$dom->load($file);
		$use = $dom->getElementsbyTagName("use")->item(0);
		$elements_used = self::read_elements_used($use);
		@ini_set("zend.ze1_compatibility_mode", "1");
		return $elements_used;
	}

	protected function get_datasources_list_form(){
		if(count($this->elements_used['datasource'])>1){
			$form = "
			<div class='colonne3'>
				<label for='datasource_choice'>".$this->format_text($this->msg['cms_module_datasource_choice'])."</label>
			</div>
			<div class='colonne-suite'>
				<select name='datasource_choice' onchange='load_datasource_form(this.value)'>
					<option value=''>".$this->format_text($this->msg['cms_module_datasource_choice'])."</option>";
			foreach($this->elements_used['datasource'] as $datasource){
				$form.= "
					<option value='".$datasource."'".($datasource == $this->datasource['name'] ? " selected='selected'" : "").">".$this->format_text($this->msg[$datasource])."</option>";
			}
			$form.="
				</select>
				<script type='text/javascript'>
					function load_datasource_form(datasource){
						if(datasource != ''){
							cms_module_load_elem_form(datasource,'0','datasource_form');
						}				
					}
				</script>
			</div>";			
		}else{
			$form = "
				<input type='hidden' name='datasource_choice' value='".$this->elements_used['datasource'][0]."'/>";
		}
		return $form;
	}
	
	protected function get_views_list_form(){
		if(count($this->elements_used['view'])>1){
			$form= "
				<div class='colonne3'>
					<label for='view_choice'>".$this->format_text($this->msg['cms_module_common_module_view_choice'])."</label>
				</div>
				<div class='colonne-suite'>
					<select name='view_choice' onchange='load_view_form(this.value)'>
						<option value=''>".$this->format_text($this->msg['cms_module_view_choice'])."</option>";
			foreach($this->elements_used['view'] as $view){
				$form.= "
						<option value='".$view."'".($view == $this->view['name'] ? " selected='selected'" : "").">".$this->format_text($this->msg[$view])."</option>";
			}
			$form.="
					</select>
				<script type='text/javascript'>
					function load_view_form(datasource){
						if(datasource != ''){
							cms_module_load_elem_form(datasource,'0','view_form');
						}				
					}
				</script>
			</div>";		
		}else{
			$form = "
					<input type='hidden' name='view_choice' value='".$this->elements_used['view'][0]."'/>";
		}
		
		return $form;
	}
	
	
	public function get_form($ajax= true,$callback='',$cancel_callback='',$delete_callback='',$action="?action=save"){
		global $charset;
		
		//en cr�ation ,on peut permettre certaines choses par d�faut (pr�-chargement de conditions,...)
		if(!$this->id){
			$this->creation_init();
		}		
		if($ajax){
			$action = "./ajax.php?module=cms&categ=module&elem=".$this->class_name."&action=save_form";
		}
		$form = "
		<script type='text/javascript'>
			function test_form_".$this->class_name."() {		
				if(document.getElementById('cms_module_common_module_name').value=='' ) {
					alert(\"".$this->msg['cms_module_common_module_name_empty']."\");
					document.getElementById('cms_module_common_module_name').focus();
					return false;
				}
				return true;
			}		
		</script>
		<form name='".$this->class_name."_form' id='".$this->class_name."_form' method='POST' action='".$action."' style='width:800px'>
			<h3>".$this->format_text(($this->id ? sprintf($this->msg['cms_module_common_module_alter_cadre'],$this->informations['name']." : ".$this->name) : $this->msg['cms_module_common_module_new_cadre']." - ". $this->informations['name'] ))."</h3>
			<div class='form-contenu'>
				<div class='row'>
					<div class='colonne3'>
						<label for='cms_module_common_module_name'>".$this->msg['cms_module_common_module_name']."</label>
					</div>
					<div class='colonne-suite'>
						<input type='text' id='cms_module_common_module_name' name='cms_module_common_module_name' value = '".addslashes($this->format_text($this->name))."' />
					</div>
				</div>
				<div class='row'>
					<div class='colonne3'>
						<label for='cms_module_common_module_fixed'>".$this->msg['cms_module_common_module_fixed']."</label>
					</div>
					<div class='colonne-suite'>
						<input type='checkbox' name='cms_module_common_module_fixed' value='1' ".($this->fixed? "checked='checked'":"")."/>
					</div>
				</div>
				<div class='row'>		
					<div class='colonne3'>
						<label for='cms_module_common_module_memo_url'>".$this->msg['cms_module_common_module_memo_url']."</label>
					</div>
					<div class='colonne-suite'>
						<input type='checkbox' name='cms_module_common_module_memo_url' value='1' ".($this->memo_url? "checked='checked'":"")."/>
					</div>
				</div>
				<div class='row'>
					<div class='colonne3'>
						<label for='cms_module_common_module_modcache'>".$this->msg['cms_module_common_module_modcache']."</label>
					</div>
					<div class='colonne_suite'>
						<select name='cms_module_common_module_modcache'>";
		$modcache_choices = $this->get_modcache_choices();		
		foreach($modcache_choices as $choice){
			$form.="
							<option value='".$choice['value']."'".($this->modcache == $choice['value'] ? " selected='selected'" : "").">".$this->format_text($choice['name'])."</option>";
		}		
		$form.="
						</select>
					</div>
				</div>
				<div class='row'></div>
				<hr/>";
		
		$form.= $this->get_conditions_form();
		$form.= "
				<hr/>
				<div class='row'>";
		$form.= $this->get_datasources_list_form();
		if($this->datasource['id'] || count($this->elements_used['datasource'])==1){
			if($this->datasource['id']){
				$datasource_name = $this->datasource['name'];
				$datasource_id = $this->datasource['id'];
			}else if(count($this->elements_used['datasource'])==1){
				$datasource_name = $this->elements_used['datasource'][0];
				$datasource_id = 0;
			}
			$form.="
				<script type='text/javascript'>
					cms_module_load_elem_form('".$datasource_name."','".$datasource_id."','datasource_form');
				</script>";			
		}
		$form.="		
				<div id='datasource_form' dojoType='dojox.layout.ContentPane'>
				</div>
			</div>";
		$form.=$this->get_filters_form();
		if($this->filter['id']){
			$form.="
			<script type='text/javascript'>
				cms_module_load_elem_form('".$this->filter['name']."','".$this->filter['id']."','filter_form');
			</script>";
		}
		$form.="
			<hr/>
			<div class='row'>";
		$form.= $this->get_views_list_form();
		if($this->view['id'] || count($this->elements_used['view'])==1 ){
			if($this->view['id']){
				$view_name = $this->view['name'];
				$view_id = $this->view['id'];
			}else if(count($this->elements_used['view'])==1){
				$view_name = $this->elements_used['view'][0];
				$view_id = 0;
			}
			$form.="
				<script type='text/javascript'>
					cms_module_load_elem_form('".$view_name."','".$view_id."','view_form');
				</script>";	
		}
		$form.="		
				<div id='view_form' dojoType='dojox.layout.ContentPane'>
				</div>
				<div class='row'>&nbsp;</div>
			</div>	
			<div class='row'><hr></div>
			<div class='row'>
				<input type='hidden' name='cms_build_info' id='cms_build_info' value='".htmlentities(serialize($this->cms_build_env),ENT_QUOTES,$charset)."' />
				<input type='hidden' name='cms_module_common_module_id' id='cms_module_common_module_id' value='".$this->id."' />
				<input type='submit' id='cms_module_common_module_submit' class='bouton' value='".$this->msg['cms_module_common_module_save']."' ".( $ajax ? "onclick=\"if(test_form_".$this->class_name."())cms_module_save();return false;\"" : "")."/>
				&nbsp;
				<input type='button' class='bouton' value='".$this->msg['cms_module_common_module_cancel']."' ".($cancel_callback != '' ? "onclick='".$cancel_callback."();'" : "")."/>
				&nbsp;
				<input type='button' class='bouton' value='".$this->msg['cms_module_common_module_delete']."' onclick='cms_module_delete()'/>
			</div>
		</form>
		<script type='text/javacript'>
			function cms_module_load_elem_form(elem,id,dom_id){
				dojo.xhrPost({
					url : './ajax.php?module=cms&categ=module&elem='+elem+'&action=get_form&id='+id,
					postData : 'cms_build_info=".rawurlencode(serialize($this->cms_build_env))."&cms_module_class=".rawurlencode($this->class_name)."',
					handelAs : 'text/html',
					load : function(data){
						dijit.byId(dom_id).set('content',data);
					}
				});		
			}
		</script>";
		if($ajax){
			$form.="
		<script type='text/javascript'>
			function cms_module_save(){
				dojo.xhrPost({
					form: '".$this->class_name."_form',
					handleAs: 'json',
					load: function(data) {
						dojo.byId('cms_module_common_module_id').value = data;";
			if($callback!=''){
				$form.="
						if(typeof(".$callback.") == 'function'){
							".$callback."(data);
						}else{
							alert('".$this->addslashes($this->msg['cms_module_common_module_saved'])."');
						}";	
			}else{
				$form.="
						alert('".$this->addslashes($this->msg['cms_module_common_module_saved'])."');";
			}
			$form.="
					},
					error: function(error) {
						alert('".$this->addslashes($this->msg['cms_module_common_module_save_error'])."');
					}
				});
			}
			
			function cms_module_delete(){
				dojo.xhrGet({
					url: './ajax.php?module=cms&categ=module&elem=".$this->class_name."&action=delete&id=".$this->id."',
					handleAs: 'json',
					load: function(data) {";
				if($delete_callback!=''){
					$form.="
						if(typeof(".$delete_callback.") == 'function'){
							".$delete_callback."(data);
						}else{
							alert('".$this->addslashes($this->msg['cms_module_common_module_deleted'])."');
						}";	
				}else{
					$form.="
						alert('".$this->addslashes($this->msg['cms_module_common_module_deleted'])."');";
				}
				$form.="
					},
					error: function(error) {
						alert('".$this->addslashes($this->msg['cms_module_common_module_delete_error'])."');
					}
				});		
			}
		</script>";
		}
		return $form;
	}
	
	protected function get_modcache_choices(){
		return array(
			array(
				'value' => "no_cache",
				'name' => $this->msg['cms_module_common_module_no_cache']
			),
			array(
				'value' => "get_post",
				'name' => $this->msg['cms_module_common_module_cache_get_post']
			),
			array(
				'value' => "get_post_view",
				'name' => $this->msg['cms_module_common_module_cache_get_post_view']
			),
			array(
				'value' => "view",
				'name' => $this->msg['cms_module_common_module_cache_view']
			),
			array(
				'value' => "all",
				'name' => $this->msg['cms_module_common_module_cache_all']
			),
		);
	}
	
	public static function get_hash_cache($obj_name,$id){
		global $dbh;
		$str_to_hash = "";
		$hash = "";
		$str_to_hash_more="";
		if($tmp=$_SERVER["REQUEST_URI"]){
			if(preg_match("#/([^/]*?\.php)#i",$tmp,$matches)){
				if($tmp2=trim($matches[1])){
					$str_to_hash_more.=$tmp2;
				}
			}
		}
		if($_SESSION["id_empr_session"]){//utilisateur connect�
			$str_to_hash_more.="_empr_is_logged";
		}
		
		if($id){
			$query = "select cadre_modcache from cms_cadres where id_cadre = ".$id;
			$result = mysql_query($query,$dbh);
			if(mysql_num_rows($result)){
				$mode = mysql_result($result,0,0);
			}else{
				$mode = "get_post";
			}
			switch($mode){
				case "no_cache" :
					$str_to_hash = "";
					break;
				case "get_post_view" :
					$str_to_hash = $obj_name."_".serialize($_GET)."_".serialize($_POST)."_".$_SESSION['opac_view'];
					break;
				case "view" :
					$str_to_hash = $obj_name."_".$_SESSION['opac_view'];
					break;
				case "all" :
					$str_to_hash = $obj_name;
					break;
				case "get_post" :
					$str_to_hash = $obj_name."_".serialize($_GET)."_".serialize($_POST);
					break;
			}
		}
		if($str_to_hash) {
			$hash = md5($str_to_hash.$str_to_hash_more);
		}
		return $hash;
	}
	

	public function build_cadre_url(){		
		global $cms_build_info;
		
		if(!is_array($cms_build_info))return "";
		$url=$cms_build_info['input']."?";	

		foreach($cms_build_info['get'] as $key => $val){
			if($key!="database" && $key!="cms_build_activate" && $key!="build_id_version")
				$url.="&$key=$val";
		}	
		foreach($cms_build_info['post'] as $key => $val){
			$url.="&$key=$val";
		}
		return $url;
	}
	
	public function save_form(){
		global $datasource_choice;
		global $view_choice;
		global $filter_choice;
		global $cms_module_common_module_name;
		global $cms_module_common_module_fixed;
		global $cms_module_common_module_memo_url;		
		global $cms_module_common_module_modcache;	
		
		$this->name = strip_tags(stripslashes($cms_module_common_module_name));
		//on calcule un hash...
		$this->get_hash();		
		//on enregistre le cadre...
		if($cms_module_common_module_memo_url){
			$cadre_url = " cadre_url = '".$this->build_cadre_url()."', ";
		}
		if($this->id){
			$query = "update cms_cadres set ";
			$clause = " where id_cadre = ".$this->id;
		}else{
			$query = "insert into cms_cadres set ";
			$clause= "";
		}
		$query.= "
			cadre_hash = '".$this->hash."',
			cadre_object = '".$this->class_name."',
			cadre_name = '".addslashes($this->name)."',
			cadre_fixed = ".($cms_module_common_module_fixed ? "1" : "0")." ,	
			$cadre_url		
			cadre_memo_url = ".($cms_module_common_module_memo_url ? "1" : "0").",
			cadre_modcache ='".addslashes($cms_module_common_module_modcache)."'		
			".$clause;
		
		$result = mysql_query($query);
		if($result){
			if(!$this->id){
				$this->id = mysql_insert_id();
			} 
			
			//les Conditions
			$result = $this->save_conditions();
			if($result){
				//source de donn�e
				if($datasource_choice == $this->datasource['name']){
					$datasource_id = $this->datasource['id'];
				}else{
					$datasource_id = 0;
				}
				$datasource = new $datasource_choice($datasource_id);
				$datasource->set_cadre_parent($this->id);
				$result = $datasource->save_form();
				if($result){
					$this->datasource = array(
						'id' => $datasource->id,
						'name' => $datasource_choice
					);
					//le filtre
					if($filter_choice == $this->filter['name']){
						$filter_id = $this->filter['id'];
					}else{
						$filter_id = 0;
					}
					if($filter_choice){
						$filter = new $filter_choice($filter_id);
						$filter->set_cadre_parent($this->id);
						$result = $filter->save_form();
						
						if($result){
							$this->filter = array(
									'id' => $filter->id,
									'name' => $filter_choice
							);
						}else{
							//	sauvegarde du filtre rat�e, on supprime le cadre...
							$this->delete();
						}
					}
					//vue
					if($view_choice == $this->view['name']){
						$view_id = $this->view['id'];
					}else{
						$view_id = 0;
					}
					$view = new $view_choice($view_id);
					$view->set_cadre_parent($this->id);
					$result = $view->save_form();
					if($result){
						$this->view = array(
								'id' => $view->id,
								'name' => $view_choice
						);
							
						//reste � nettoyer la table de hash...
						$this->clean_hash_table();
						//tout est bon, on a fini
						return $this->utf8_normalize(array(
								'id' => $this->id,
								'name' => $this->name,
								'object' => $this->class_name,
								'dom_id' => $this->get_dom_id()
						));
					}else{
						//	sauvegarde de la vue rat�e, on supprime le cadre...
						$this->debug(sprintf($this->msg['cms_module_commom_module_view_save_error'],$view_choice),CMS_DEBUG_MODE_FILE);
						$this->delete();
					}
				}else{
					//sauvegarde de la source de donn�e rat�e, on supprime le cadre...
					$this->debug(sprintf($this->msg['cms_module_commom_module_datasource_save_error'],$datasource_choice),CMS_DEBUG_MODE_FILE);
					$this->delete();
				}
			}else{
				//sauvegarde des conditions rat�e, on supprime le cadre...
				$this->debug($this->msg['cms_module_commom_module_conditions_save_error'],CMS_DEBUG_MODE_FILE);
				$this->delete();
			}
		}else{
			//cr�ation du cadre rat�e, on supprime le hash de la table...
			$this->debug(sprintf($this->msg['cms_module_commom_module_cadre_save_error'],$this->cadre_name),CMS_DEBUG_MODE_FILE);
			$this->delete_hash();
		}
		return false;
	}
	
	public function get_conditions_form(){
		$form ="
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_module_conditions_selector'>".$this->format_text($this->msg['cms_module_common_module_conditions_selector'])."</label>
				</div>
				<div class='colonne-suite'>
					<select name='cms_module_common_module_conditions_selector' onchange='load_condition_form(this.value);'>
						<option value='0'>".$this->format_text($this->msg['cms_module_common_module_conditions_selector_choice'])."</option>";
		for($i=0 ; $i<count($this->elements_used['condition']) ; $i++){
			$form.= "
						<option value='".$this->elements_used['condition'][$i]."'>".$this->format_text($this->msg[$this->elements_used['condition'][$i]])."</option>";
		}
		$form.="				
					</select>
					<input type='hidden' name='cms_module_common_module_conditions[]' id='cms_module_common_module_conditions'/> 
				</div>
				<div id='cms_module_common_module_conditions_form'>";
		for($i=0 ; $i<count($this->conditions) ; $i++){
			$form.="
				<script type='text/javascript'>
					dojo.xhrPost({
						url : './ajax.php?module=cms&categ=module&elem=".$this->conditions[$i]['name']."&action=get_form&id=".$this->conditions[$i]['id']."',
						postData : 'cms_build_info=".rawurlencode(serialize($this->cms_build_env))."&cms_module_class=".rawurlencode($this->class_name)."',
						handelAs : 'text/html',
						load : function(data){
							var form_content = dojo.create('div');
							var condition_form= new dojox.layout.ContentPane({
								content : data
							},form_content);
							dojo.place(form_content,'cms_module_common_module_conditions_form');
						}
					});							
				</script>";
		}
		$form.="	
				</div>
			</div>
			<script type='text/javascript'>
				dojo.require('dojox.layout.ContentPane');
				function load_condition_form(condition){
					if(condition!=0){
						var form_content = dojo.create('div');
						dojo.xhrPost({
							url : './ajax.php?module=cms&categ=module&elem='+condition+'&action=get_form&id=0',
							postData : 'cms_build_info=".rawurlencode(serialize($this->cms_build_env))."&cms_module_class=".rawurlencode($this->class_name)."',
							handelAs : 'text/html',
							load : function(data){
								var condition_form= new dojox.layout.ContentPane({
									content : data
								},form_content);
								dojo.place(form_content,'cms_module_common_module_conditions_form');
							}
						});		
					}
				}
			</script>";
		return $form;
	}
	
	public function save_conditions(){
		global $cms_module_common_module_conditions; 
		$result = true;
		for($i=0 ; $i<count($cms_module_common_module_conditions) ; $i++){
			if($cms_module_common_module_conditions[$i] != ""){						
				$condition_id=0;
				for($j=0 ; $j<count($this->conditions) ; $j++){
					if($cms_module_common_module_conditions[$i] == $this->conditions[$j]['name']){
						$condition_id = $this->conditions[$j]['id'];
						break;
					}
				}
				$condition = new $cms_module_common_module_conditions[$i]($condition_id);
				$condition->set_cadre_parent($this->id);
				$result = $condition->save_form();
				if($result){
					if($condition_id == 0){
						$this->conditions[]=array(
							'id' => $condition->id,
							'name' => $cms_module_common_module_conditions[$i]
						);
					}
					continue;
				}else{
					break;
				}
			}
		}
		return $result;
	}
	
	public function delete(){
		$dom_id = $this->get_dom_id();
		//on commence par supprimer la d�finition dans le portail...
		$query = "delete from cms_build where build_obj = '".$dom_id."'";
		mysql_query($query);
		
		//on �limine tous les �l�ments associ�s directement au cadre...
		$query = "select id_cadre_content, cadre_content_object from cms_cadre_content where cadre_content_num_cadre = ".$this->id." and cadre_content_num_cadre_content = 0";
		$result=mysql_query($query);
		if(mysql_num_rows($result)){
			//pour �viter tout probl�me, on ne supprime pas directement les �lements de la table, on appelle la m�thode de suppression de l'objet...
			while($row = mysql_fetch_object($result)){
				$elem = new $row->cadre_content_object($row->id_cadre_content);
				$success = $elem->delete();
				if(!$success){
					//TODO verbose mode
					return false;
				}
			}
		}
		//il ne peut en rester qu'un, et c'est perdu pour celui-ci...
		$query = "delete from cms_cadres where id_cadre = ".$this->id;
		$result = mysql_query($query);
		if($result){
			$this->delete_hash();
			return array('dom_id' =>$dom_id);
		}else{
			//TODO verbose mode
			return false;
		}
	}
	
	public function check_conditions(){
		for($i=0 ; $i<count($this->conditions) ; $i++){
			$condition = new $this->conditions[$i]['name']($this->conditions[$i]['id']);
			if(!$condition->check_condition()){
				return false;
			}else{
				continue;
			}
		}
		return true;
	}
	
	public function show_cadre(){
		if($this->datasource['id']!= 0){
			$datasource = new $this->datasource['name']($this->datasource['id']);	
			if($this->filter['id']!= 0){
				$filter = new $this->filter['name']($this->filter['id']);
				$datasource->set_filter($filter);
			}
			$datas = $datasource->get_datas();
			if($this->view['id'] != 0){
				$view = new $this->view['name']($this->view['id']);
				return "<div id='".$this->get_dom_id()."'>".$view->render($datas)."</div>";
			}
		}
		return "";
	}
	
	public function get_dom_id(){
		return $this->class_name."_".$this->id;
	}
	
	protected function creation_init(){
		//on regarde si des conditions peuvent �tre pr�-charg�es...
		for($i=0 ; $i<count($this->elements_used['condition']) ; $i++){
			//appel statique du test de la conditions 
			if(call_user_func(array($this->elements_used['condition'][$i],is_loadable_default))){
				//	si c'est positif, on ajoute la condition...
				$this->conditions[]=array(
					'id' => 0,
					'name' => $this->elements_used['condition'][$i]
				);
			}
		}
	}

	public function get_headers(){
		$headers=array();
		$datasource = new $this->datasource['name']($this->datasource['id']);
		$headers = array_merge($headers,$datasource->get_headers());
		$headers = array_unique($headers);
		$view = new $this->view['name']($this->view['id']);
		$headers = array_merge($headers,$view->get_headers());
		$headers = array_unique($headers);
		for($i=0 ; $i<count($this->conditions) ; $i++){
			$condition = new $this->conditions[$i]['name']($this->conditions[$i]['id']);
			$headers = array_merge($headers,$condition->get_headers());
			$headers = array_unique($headers);
		}		
		return $headers;
	}
	
	protected function fetch_managed_datas(){
		$query = "select managed_module_box from cms_managed_modules where managed_module_name = '".$this->class_name."'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$this->managed_datas = unserialize(mysql_result($result,0,0));
		}
	}
	
	public function get_manage_forms(){
		global $base_path;
		global $quoi;
		
		switch($quoi){
			case "views" :
			case "datasources" :
			case "conditions" :
				$form = $this->get_element_manage_form($quoi);
				break;
			case "module" :
				$form= $this->get_manage_form();
				break;
		}
		return $form;
	}
	
	public function save_manage_forms(){
		global $quoi,$elem;
			
		//on sauvegarde les infos modifi�es
		switch ($quoi){
			case "views" :
			case "datasources" :
			case "conditions" :
				$this->managed_datas[$quoi][$elem] = call_user_func(array($elem,"save_manage_form"),$this->managed_datas[$quoi][$elem]);
				break;
			case "module" :
				$this->managed_datas[$quoi] = $this->save_manage_form();
				break;		
		}
		$query = "replace into cms_managed_modules set managed_module_name = '".$this->class_name."', managed_module_box = '".$this->addslashes(serialize($this->managed_datas))."'";
		return mysql_query($query);
	}
	
	public function get_manage_menu(){
		global $javascript_path;
		$manage_menu = "
			<script type='text/javascript' src='".$javascript_path."/cms/cms_form.js'></script>
		";
		//on regarde si le module lui-m�me est administrable
		if(method_exists($this->class_name,"get_manage_form")){
			$manage_menu.= "
			<span".ongletSelect("categ=manage&sub=".str_replace("cms_module_","",$this->class_name)."&quoi=module").">
				<a title='".$this->format_text($this->msg["cms_manage_module_general"])."' href='./cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->class_name)."&quoi=module'>
					".$this->format_text($this->msg["cms_manage_module_general"])."
				</a>
			</span>";
		}
		//on regarde aussi pour chaque type d'�l�ments
		$elements=array("view","datasource","condition");
		foreach($elements as $element){
			if($this->check_managed_elem($element)){
				$manage_menu.= "
		<span".ongletSelect("categ=manage&sub=".str_replace("cms_module_","",$this->class_name)."&quoi=".$element."s").">
			<a title='".$this->format_text($this->msg["cms_manage_module_".$element."s"])."' href='./cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->class_name)."&quoi=".$element."s'>
				".$this->format_text($this->msg["cms_manage_module_".$element."s"])."
			</a>
		</span>";
			}
		}
		return $manage_menu;
	}
	
	protected function check_managed_elem($elem){
		foreach($this->elements_used[$elem] as $element){
			if(method_exists($element,"get_manage_form")){
				return true;
			}
		}
		return false;
	}
	
	protected function get_element_manage_form($quoi){
		global $base_path;
		global $elem;
		$type = substr($quoi,0,strlen($quoi)-1);
		$nb_managed_elems=0;
		$elem_choice="";
		for($i=0 ; $i<count($this->elements_used[$type]) ; $i++){
			if(method_exists($this->elements_used[$type][$i],"get_manage_form")){
				if(!$elem) $elem = $this->elements_used[$type][$i];
				$nb_managed_elems++;
				$elem_choice.="<p><a href='".$base_path."/cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->class_name)."&quoi=".$quoi."&elem=".$this->elements_used[$type][$i]."&action=get_form'>".$this->format_text($this->msg[$this->elements_used[$type][$i]])."</a></p>";
			}
		}
		
		$form="
		<div dojoType='dijit.layout.BorderContainer' style='width: 100%; height: 800px;'>";
		if($nb_managed_elems>1){
			$form.="
			<div dojoType='dijit.layout.ContentPane' region='left' splitter='true' style='width:300px;' >
				".$elem_choice."
			</div>";
		}
		$form.="
			<div dojoType='dijit.layout.ContentPane' region='center' >";
		$view = new $elem();
		$view->set_module_class_name($this->class_name);
		$form.= $view->get_manage_form();
		$form.="
			</div>
		</div>";
		return $form;		
	}
	
	protected function get_filters_form(){
 		$form = "";
 		if(count($this->elements_used['filter'])){
 			$form.="
 			<hr/>
 			<div class='row'>
 				<div class='colonne3'>
 					<label>".$this->format_text($this->msg['cms_module_common_module_filter_label'])."
 				</div>
 				<div class='colonne_suite'>
 					<select name='filter_choice' onchange='load_filter_form(this.value)'>
 						<option value=''>".$this->format_text($this->msg['cms_module_common_module_filter_choice'])."</option>";
	 		foreach($this->elements_used['filter'] as $filter){
 				$form.= "
 						<option value='".$filter."'".($filter == $this->filter['name'] ? " selected='selected'" : "").">".$this->format_text($this->msg[$filter])."</option>";
	  		}
 			$form.="
 					</select>
  					<script type='text/javascript'>
 						function load_filter_form(filter){
					 		if(filter != ''){
							 	cms_module_load_elem_form(filter,'0','filter_form');
						 	}else{
						 		dojo.byId('filter_form').innerHTML= '';
						 	}
					 	}
			 		</script>					
 				</div>
 			</div>
 			<div class='row' id='filter_form' dojoType='dojox.layout.ContentPane'></div>
 			<div class='row'>&nbsp;</div>";	
 		}
		return $form;
	}
	
	public function get_exported_datas(){
		$infos = array(
			"id" => $this->id,
			"class" => $this->class_name,
			"name" => $this->name,
			"hash" => $this->hash,
			"fixed"=> $this->fixed,
			"managed_datas" => $this->managed_datas,
			"parameters" => $this->parameters
		);
		$datasource = new $this->datasource['name']($this->datasource['id']);
		$infos['datasource'] = $datasource->get_exported_datas();
		$view = new $this->view['name']($this->view['id']);
		$infos['view'] = $view->get_exported_datas();
		$infos['conditions'] = array();
		for($i=0 ; $i<count($this->conditions) ; $i++){
			$condition = new $this->conditions[$i]['name']($this->conditions[$i]['id']);
			$infos['conditions'][] = $condition->get_exported_datas();
		}
		return $infos;
	}
	
	
	public function get_extension_form($type,$type_elem,$num_elem){
		$query = "select extension_datas_datas from cms_modules_extensions_datas where extension_datas_module = '".$this->class_name."' and extension_datas_type = '".$type."' and extension_datas_type_element = '".$type_elem."' and extension_datas_num_element = '".$num_elem."'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$this->extension_datas = unserialize(mysql_result($result,0,0));
		}
		//on var chercher les donn�es pour l'�l�ment courant
		return $this->get_hash_form();
	}
	
	
	protected function save_extension_form($type,$type_elem,$num_elem){
		//on supprime ceux d'avant...
		$query = "delete from cms_modules_extensions_datas where extension_datas_module = '".$this->class_name."' and extension_datas_type = '".$type."' and extension_datas_type_element = '".$type_elem."' and extension_datas_num_element = '".$num_elem."'";
		mysql_query($query);
		
		$query = "insert into cms_modules_extensions_datas set 
			extension_datas_module = '".$this->class_name."',
			extension_datas_type_element = '".$type_elem."',
			extension_datas_num_element = '".$num_elem."',
			extension_datas_type = '".$type."',
			extension_datas_datas = '".addslashes(serialize($this->extension_datas))."'";
		mysql_query($query);
	}
	

	//on parcours les conditions pour savoir si rien n'empeche la mise en cache du cadre!
	public function check_for_cache(){
		for($i=0 ; $i<count($this->conditions) ; $i++){
			$condition = $this->conditions[$i]['name'];
			if(!$condition::use_cache()){
				return false;
			}else{
				continue;
			}
		}
		return true;		
	}
}