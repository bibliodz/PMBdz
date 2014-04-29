<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_portfolio.class.php,v 1.1 2013-07-04 12:55:50 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_portfolio extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On défini les sélecteurs utilisable pour cette source de donnée
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_documents"
		);
	}
	
	/*
	 * Récupération des données de la source...
	 */
	public function get_datas(){
		$documents = array();
		//on commence par récupérer l'identifiant retourné par le sélecteur...
		$selector = $this->get_selected_selector();
		if($selector){
			$docs = $selector->get_value();
			foreach($docs['ids'] as $document_linked){
				$document = new cms_document($document_linked);
				$documents[] = $document->format_datas();
			}
		}
		return array(
			'documents'=>$documents,
			'nb_documents' => count($documents),
			'type_object' => $docs['type_object'],
			'num_object' => $docs['num_object']
		);
	}
	
	public function get_format_data_structure(){
		return array(
			array(
				'var' => "documents",
				'desc' => $this->msg['cms_module_common_datasource_portfolio_documents'],
				'children' => $this->prefix_var_tree(cms_document::get_format_data_structure(),"documents[i]")
			),
			array(
				'var' => "nb_documents",
				'desc' => $this->msg['cms_module_common_datasource_portfolio_nb_documents']
			),
			array(
				'var' => "type_object",
				'desc' => $this->msg['cms_module_common_datasource_portfolio_type_object']
			),
			array(
				'var' => "num_object",
				'desc' => $this->msg['cms_module_common_datasource_portfolio_num_object']
			)
		);
	}		
}