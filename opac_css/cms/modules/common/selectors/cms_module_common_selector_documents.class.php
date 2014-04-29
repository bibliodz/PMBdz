<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_documents.class.php,v 1.1 2013-07-04 12:55:50 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_documents extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->once_sub_selector = true;
	}
	
	protected function get_sub_selectors(){
		return array(
			"cms_module_common_selector_generic_article"
		);
	}	
		
	public function get_value(){
		if(!$this->value){
			$this->value = array();
			$type_selector= $this->get_selected_sub_selector();
			//en fonction de la source des doc, les requetes changent...
			switch($this->parameters['sub_selector']){
				//docs associés à une article
				case "cms_module_common_selector_generic_article" :
					$id_article = $type_selector->get_value();
					$id_article+=0;
					$query = "select document_link_num_document from cms_documents_links where document_link_type_object = 'article' and document_link_num_object = '".$id_article."'";
					$result = mysql_query($query);
					
					$this->value['type_object'] = 'article';
					$this->value['num_object'] = $id_article;
					
					if(mysql_num_rows($result)){
						while($row = mysql_fetch_object($result)){
							$this->value['ids'][] = $row->document_link_num_document+0;
						}
					}
					break;
			}
		}
		return $this->value;
	}
}