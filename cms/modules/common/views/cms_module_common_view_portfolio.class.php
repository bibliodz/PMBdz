<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_portfolio.class.php,v 1.2 2013-07-24 13:09:22 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_view_portfolio extends cms_module_common_view_django{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "<div class='row document_list'>
 {% for document in documents %}
  <div class='document_item'>
   &nbsp;
   <div class='document_item_content'>
    <a target='_blank' href='{{document.url}}' title='{% if document.name %}{{document.name}}{% else %}{{document.filename}}{% endif %}' alt='{% if document.name %}{{document.name}}{% else %}{{document.filename}}{% endif %}'>
     <img src='{{document.thumbnails_url}}'/>
     <br />
     <p>{% if document.name %}{{document.name|limitstring 30 \"[...]\"}}{% else %}{{document.filename|limitstring 30 \"[...]\"}}{% endif %}
     <br />
     <span style='font-size:.8em;'>{{document.mimetype}}{%if document.filesize.human %} - ({{document.filesize.human}}){% endif %}</span></p>
    </a>
   </div>
   &nbsp;
  </div>
 {% endfor %}
</div>
<div class='row'></div>";
	}
	
	public function get_form(){
		$form = "
		<div class='row'>
			<div class='colonne3'>
				<label for='".$this->get_form_value_name("visionneuse")."' >".$this->format_text($this->msg['cms_module_common_view_portfolio_visionneuse'])."</label>
			</div>
			<div class'colonne_suite'>
				<input type='radio' name='".$this->get_form_value_name("visionneuse")."' value='1'".($this->parameters['visionneuse'] ? " checked='checked' " : "")."/>&nbsp;".$this->format_text($this->msg['yes'])."
				<input type='radio' name='".$this->get_form_value_name("visionneuse")."' value='0'".($this->parameters['visionneuse'] ? "" : " checked='checked' ")."/>&nbsp;".$this->format_text($this->msg['no'])."
			</div>
		</div>
		<div class='row'>&nbsp;</div>";
		$form.= parent::get_form();
		return $form;
	}
	
	public function get_headers(){
		$headers = array();
		$headers[] = "<script type='text/javascript' src='javascript/visionneuse.js'></script>";
		return $headers;
	}
	
	public function save_form(){
		$this->parameters['visionneuse'] = $this->get_value_from_form("visionneuse");
// 		$this->parameters['visionneuse'] = 0;
		return parent::save_form();
	}
	
	public function render($datas){
		$render =  parent::render($datas);
		if($this->parameters['visionneuse']){
			for($i=0 ; $i<count($datas['documents']) ; $i++){
				$str_to_replace = substr($render,strpos($render,$datas['documents'][$i]['url'])-1,strlen($datas['documents'][$i]['url'])+2);
				$render = str_replace($str_to_replace, "'#' onclick='open_visionneuse(open_cms_visionneuse_".$this->id.",".$datas['documents'][$i]['id'].");return false;',", $render);
			}
			$render.= "
			<script type='text/javascript'>
				function open_cms_visionneuse_".$this->id."(id){
					var url = 'visionneuse.php?driver=pmb_document&lvl=visionneuse&cms_type=".$datas['type_object']."&num_type=".$datas['num_object']."';
					if(id){
						url+='&explnum='+id;
					}
					document.getElementById('visionneuseIframe').src = url;
				}
			</script>";
		}
		return $render;
	}

	public function get_format_data_structure(){
		$datasource = new cms_module_common_datasource_portfolio();
		return $datasource->get_format_data_structure();
	}
}