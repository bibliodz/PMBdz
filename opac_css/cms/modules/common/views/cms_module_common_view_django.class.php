<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_django.class.php,v 1.18 2013-12-18 14:52:13 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
require_once($base_path."/cms/modules/common/includes/pmb_h2o.inc.php");

class cms_module_common_view_django extends cms_module_common_view{
	protected $cadre_parent;
	
	public function __construct($id=0){
		parent::__construct($id+0);
	}
	
	public function get_form(){
		if(count($this->managed_datas['templates'])){
			//s�lection d'un template d�finie en adminsitration
			$form="
		<div clas='row'>
			<div class='colonne3'>
				<label for='cms_module_common_view_django_template_choice'>".$this->format_text($this->msg['cms_module_common_view_django_template_choice'])."</label>
			</div>
			<div class='colonne-suite'>
				<select name='cms_module_common_view_django_template_choice' onchange='load_cms_template_content(this.value);'>
					<option value='0'>".$this->format_text($this->msg['cms_module_common_view_django_template_choice'])."</value>";
			foreach($this->managed_datas['templates'] as $key => $infos){
				$form.="
					<option value='".$key."'>".$this->format_text($infos['name'])."</option>";
			}
			$form.="
				</select>
				
				<script type='text/javascript'>
					function load_cms_template_content(template){
						switch(template){";
			foreach($this->managed_datas['templates'] as $key => $infos){
				$contents = explode("\n",$infos['content']);
				$form.="
							case '".$key."' :
								dojo.byId('cms_module_common_view_django_template_content').value=''";
				foreach($contents as $content){
					$form.="
								dojo.byId('cms_module_common_view_django_template_content').value+= \"".str_replace(array("\n","\r"),"",addslashes($content)).'\n'."\"";
				}
				$form.="
								break;";			
			}
			$form.="
							default :
								//do nothing
								break;
						}
					}
				</script>
			</div>
		</div>";
		}else if($this->parameters['active_template'] == ""){
			$this->parameters['active_template'] = $this->default_template;
		}
		$form.="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_common_view_django_template_content'>".$this->format_text($this->msg['cms_module_common_view_django_template_content'])."</label> 
				".$this->get_format_data_structure_tree("cms_module_common_view_django_template_content")."
			</div>
			<div class='colonne-suite'>
				<textarea name='cms_module_common_view_django_template_content' id='cms_module_common_view_django_template_content'>".$this->format_text($this->parameters['active_template'])."</textarea>
			</div>
		</div>";
		
		return $form;
	}	
	
	/*
	 * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
	 */
	public function save_form(){
		global $cms_module_common_view_template_choice;
		global $cms_module_common_view_templates;
		global $cms_module_common_view_django_template_content;
		
		
		$this->parameters['active_template'] = $this->stripslashes($cms_module_common_view_django_template_content);
		return parent::save_form();
	}
	
	public function render($datas){
		if(!$datas['get_vars']){
			$datas['get_vars'] = $_GET;
		}
		if(!$datas['post_vars']){
			$datas['post_vars'] = $_POST;
		}
		if(!$datas['session_vars']){
			$datas['session_vars']['view'] = $_SESSION['opac_view'];
		}
		if(!$datas['env_vars']){
			$datas['env_vars']['script'] = basename($_SERVER['SCRIPT_NAME']);
			$datas['env_vars']['request'] = basename($_SERVER['REQUEST_URI']);
		}		

		try{
			$html = H2o::parseString($this->parameters['active_template'])->render($datas); 
		}catch(Exception $e){
			$html = $this->msg["cms_module_common_view_error_template"];
		}
		return $html;
	}
	
	public function get_manage_form(){
		global $base_path;
		//variables persos...
		global $cms_template;
		global $cms_template_delete;

		if(!$this->managed_datas) $this->managed_datas = array();
		if($this->managed_datas['templates'][$cms_template_delete]) unset($this->managed_datas['templates'][$cms_template_delete]);
		
		$form="
		<div dojoType='dijit.layout.BorderContainer' style='width: 100%; height: 800px;'>
			<div dojoType='dijit.layout.ContentPane' region='left' splitter='true' style='width:200px;' >";
		if($this->managed_datas['templates']){
			foreach($this->managed_datas['templates'] as $key => $infos){
				$form.="
					<p>
						<a href='".$base_path."/cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->module_class_name)."&quoi=views&elem=".$this->class_name."&cms_template=".$key."&action=get_form'>".$this->format_text($infos['name'])."</a>
						&nbsp;
						<a href='".$base_path."/cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->module_class_name)."&quoi=views&elem=".$this->class_name."&cms_template_delete=".$key."&action=save_form' onclick='return confirm(\"".$this->format_text($this->msg['cms_module_common_view_django_delete_template'])."\")'>
							<img src='".$base_path."/images/trash.png' alt='".$this->format_text($this->msg['cms_module_root_delete'])."' title='".$this->format_text($this->msg['cms_module_root_delete'])."'/>
						</a>
					</p>";
			}
		}
			$form.="
				<a href='".$base_path."/cms.php?categ=manage&sub=".str_replace("cms_module_","",$this->module_class_name)."&quoi=views&elem=".$this->class_name."&cms_template=new&action=get_form'/>".$this->format_text($this->msg['cms_module_common_view_django_add_template'])."</a> 
			";
		$form.="
			</div>
			<div dojoType='dijit.layout.ContentPane' region='center' >";
		if($cms_template){
			$form.=$this->get_managed_form_start(array('cms_template'=>$cms_template));
			$form.=$this->get_managed_template_form($cms_template);
			$form.=$this->get_managed_form_end();
		}
		$form.="
			</div>
		</div>";
		return $form;
	}
	
	protected function get_managed_template_form($cms_template){
		global $opac_url_base;

		if($cms_template != "new"){
			$infos = $this->managed_datas['templates'][$cms_template];
		}else{
			$infos = array(
				'name' => "Nouveau Template",
				'content' => $this->default_template
			);
		}
		//nom
		$form.="
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_view_django_template_name'>".$this->format_text($this->msg['cms_module_common_view_django_template_name'])."</label>
				</div>
				<div class='colonne-suite'>
					<input type='text' name='cms_module_common_view_django_template_name' value='".$this->format_text($infos['name'])."'/>
				</div>
			</div>";
		//contenu	
		$form.="
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_view_django_template_content'>".$this->format_text($this->msg['cms_module_common_view_django_template_content'])."</label><br/>
					".$this->get_format_data_structure_tree("cms_module_common_view_django_template_content")."
				</div>
				<div class='colonne-suite'>
					<textarea id='cms_module_common_view_django_template_content' name='cms_module_common_view_django_template_content'>".$this->format_text($infos['content'])."</textarea>
				</div>
			</div>";
		return $form;
	}
	
	public function save_manage_form($managed_datas){
		global $cms_template;
		global $cms_template_delete;
		global $cms_module_common_view_django_template_name,$cms_module_common_view_django_template_content;
		
		if($cms_template_delete){
			unset($managed_datas['templates'][$cms_template_delete]);
		}else{
			if($cms_template == "new"){
				$cms_template = "template".(cms_module_common_view_django::get_max_template_id($managed_datas['templates'])+1);
			}
			$managed_datas['templates'][$cms_template] = array(
					'name' => stripslashes($cms_module_common_view_django_template_name),
					'content' => stripslashes($cms_module_common_view_django_template_content)		
			);
		}
		return $managed_datas;
	}
	
	protected function get_max_template_id($datas){
		$max = 0;
		if(count($datas)){
			foreach	($datas as $key => $val){
				$key = str_replace("template","",$key)*1; 
				if($key>$max) $max = $key; 
			}
		}
		return $max;
	}
	
	public function get_format_data_structure_tree($textarea){
		$html = "
		<div id='struct_tree' class='row'>
		</div>
		<script type='text/javascript'>
			require(['dojo/data/ItemFileReadStore', 'dijit/tree/ForestStoreModel', 'dijit/Tree','dijit/Tooltip'],function(Memory,ForestStoreModel,Tree,Tooltip){
				var datas = {identifier:'var',label:'var'};
				datas.items = ".json_encode($this->utf8_encode($this->get_format_data_structure())).";
			
				var store = Memory({
					data :datas
				});
				var model = new ForestStoreModel({
					store: store,
					rootId: 'root',
					rootLabel:'Vars'
				});
				var tree = new Tree({
					model: model,
					showRoot: false,
					onDblClick: function(item){
						document.getElementById('".$textarea."').value = document.getElementById('".$textarea."').value + '{{'+item.var[0]+'}}';
					},

				},'struct_tree');
				new Tooltip({
					connectId: 'struct_tree',
					selector: 'span',
					getContent: function(matchedNode){
						return dijit.getEnclosingWidget(matchedNode).item.desc[0];
					}
				});
			});
			
			
		</script>";
		
		return $html;
	}
}