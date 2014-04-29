<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_section.class.php,v 1.11 2013-09-05 12:08:14 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_view_section extends cms_module_common_view_django{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "<h3>{{title}}</h3>
<p>{{resume}}</p>
<img src='{{logo.large}}'/>
<p>{{content}}</p>
 <h5>Sous-rubriques</h5> 
  <ul>
   {% for child in children %}
    <li><a href='{{child.link}}'>{{child.title}}</a></li>
   {% endfor %}
  </ul>
<h4>Articles</h4>
 <ul>
   {% for article in articles %}
    <li><a href='{{article.link}}'>{{article.title}}</a></li>
   {% endfor %}
  </ul>";
	}
		
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for=''>".$this->format_text($this->msg['cms_module_common_view_section_build_section_link'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.= $this->get_constructor_link_form("section");
		$form.="	
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for=''>".$this->format_text($this->msg['cms_module_common_view_section_build_article_link'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.= $this->get_constructor_link_form("article");
		$form.="	
			</div>
		</div>";
		$form.=parent::get_form();
		return $form;
	}
	
	/*
	 * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
	 */
	public function save_form(){
// 		global $cms_module_common_view_section_page_section;
// 		global $cms_module_common_view_section_page_section_var;
// 		global $cms_module_common_view_section_page_article;
// 		global $cms_module_common_view_section_page_article_var;

// 		$this->parameters['links'] = array(
// 			'section' => array(
// 				'page' => $cms_module_common_view_section_page_section+0,
// 				'var' => $cms_module_common_view_section_page_section_var
// 			),
// 			'article' => array(	
// 				'page' => $cms_module_common_view_section_page_article+0,
// 				'var' => $cms_module_common_view_section_page_article_var
// 			)
// 		);
		$this->save_constructor_link_form('section');
		$this->save_constructor_link_form('article');
		return parent::save_form();
	}
	
	public function gen_section_select($type,$name=""){
		if(!$name) $name = "cms_module_common_view_section_page_".$type;
		
		$form = "
				<select id='".$name."' name='".$name."' onChange='cms_module_common_view_section_load_".$type."_page_env();'>
					<option value='0'>".$this->format_text($this->msg['cms_module_common_link_constructor_page'])."</option>";
		$query = "select id_page,page_name from cms_pages order by 2";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while( $row = mysql_fetch_object($result)){
				$form.= "
					<option value='".$row->id_page."' ".($row->id_page == $this->parameters['links'][$type]['page'] ? "selected='selected'" : "").">".$this->format_text($row->page_name)."</option>";
			}
		}
		$form.="		
				</select>
				<script type='text/javascript'>
					function cms_module_common_view_section_load_".$type."_page_env(){
						dijit.byId('".$name."_env').href = './ajax.php?module=cms&elem=".$this->class_name."&categ=module&action=get_env&name=".$this->class_name."_page_".$type."_var"."&pageid='+dojo.byId('".$name."').value;
						dijit.byId('".$name."_env').refresh();
					}
				</script>";
		$href = "";
		if($this->parameters['links'][$type]['page']){
			$href = "./ajax.php?module=cms&elem=".$this->class_name."&categ=module&action=get_env&name=".$this->class_name."_page_".$type."_var"."&pageid=".$this->parameters['links'][$type]['page']."&var=".$this->parameters['links'][$type]['var'];
		}
		$form.="
				<div id='".$name."_env' dojoType='dojox.layout.ContentPane' ".($href ? "preload='true'" : "")." href='".$href."'></div>";
		return $form;
	}
	
	public function get_page_env_select($pageid,$name,$var=""){
		$pageid+=0;
		$page = new cms_page($pageid);
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for='".$name."'>".$this->format_text($this->msg['cms_module_common_view_section_page_var'])."</label>
			</div>
			<div class='colonne-suite'>
				<select name='".$name."' id='".$name."'>";
		foreach($page->vars as $page_var){
				$form.="
					<option value='".$this->format_text($page_var['name'])."' ".($page_var['name'] == $var ? "selected='selected'" : "").">".$this->format_text(($page_var['comment']!=""? $page_var['comment'] : $page_var['name']))."</option>";
		}		
		$form.="	
				</select>
			</div>
		</div>";
		return $form;		
	}
	
	public function render($datas){
		$datas = $this->add_links($datas);
		return parent::render($datas);
	}
	
	protected function add_links($data,$type='section'){
		global $opac_url_base;
		$data['link'] = $opac_url_base."?lvl=cmspage&pageid=".$this->parameters['links'][$type]['page']."&".$this->parameters['links'][$type]['var']."=".$data['id'];
		for ($i=0; $i<count($data['children']) ; $i++){
			$data['children'][$i] = $this->add_links($data['children'][$i]);
		}	
		for ($i=0; $i<count($data['articles']) ; $i++){
			$data['articles'][$i] = $this->add_links($data['articles'][$i],'article');
		}
		return $data;
	}
	
	public function get_format_data_structure(){
		$datasource = new cms_module_common_datasource_section();
		return $datasource->get_format_data_structure();
	}
}