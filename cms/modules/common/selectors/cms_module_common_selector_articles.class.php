<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_articles.class.php,v 1.2 2012-11-09 14:12:45 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_articles extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for=''>".$this->format_text($this->msg['cms_module_common_selector_articles_ids'])."</label>
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
		$this->parameters = $this->get_value_from_form("articles_ids");
		return parent ::save_form();
	}
	
	protected function gen_select(){
		//si on est en cr�ation de cadre
		if(!$this->id){
			$this->parameters = array();
		}
		
		//pour le moment, on ne regarde pas le statut de publication
		$query= "select id_article, article_title from cms_articles";// where article_publication_state = 1 ";
		$result = mysql_query($query);
		$select = "
					<select name='".$this->get_form_value_name("articles_ids")."[]' multiple='yes'>";
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$select.="
						<option value='".$row->id_article."' ".(in_array($row->id_article,$this->parameters) ? "selected='selected'" : "").">".$this->format_text($row->article_title)."</option>";
			}
		}else{
			$select.= "
						<option value ='0'>".$this->format_text($this->msg['cms_module_common_selector_articles_no_article'])."</option>";
		}
		$select.= "
			</select>";
		return $select;
	}
	
	public function get_value(){
		if(!$this->value){
			$this->value = $this->parameters;
		}
		return $this->value;
	}
}