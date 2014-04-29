<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_article.class.php,v 1.7 2013-01-02 11:07:10 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_article extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_article",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_global_var"
		);
	}
	
	/*
	 * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
	 */
	public function save_form(){
		global $selector_choice;
		
		$this->parameters= array();
		$this->parameters['selector'] = $selector_choice;
		return parent::save_form();
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		$selector = $this->get_selected_selector();
		if($selector){
			$article_id = $selector->get_value();
			$article_ids = $this->filter_datas("articles",array($selector->get_value()));
			if($article_ids[0]){
				$article = new cms_article($article_ids[0]);
				return $article->format_datas();
			}
		}
		return false;
	}
	
	public function get_format_data_structure(){
		return cms_article::get_format_data_structure();
	}
}