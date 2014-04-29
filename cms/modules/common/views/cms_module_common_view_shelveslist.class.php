<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_shelveslist.class.php,v 1.6 2013-11-29 11:06:01 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_view_shelveslist extends cms_module_common_view_django{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "
		<div>
			{% for shelve in shelves %}
				<h3>{{shelve.name}}</h3>
				{% if shelve.link_rss %}
					<a href='{{shelve.link_rss}}'>Flux RSS</a>
				{% endif %}
				<div>
					<blockquote>{{shelve.comment}}</blockquote>
					{{shelve.records}}
				</div>
			{% endfor %}
		</div>";
	}
	
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_common_shelveslist_view_link'>".$this->format_text($this->msg['cms_module_common_view_shelveslist_build_shelve_link'])."</label>
			</div>
			<div class='colonne_suite'>";
		$form.= $this->get_constructor_link_form("shelve");
		$form.="
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_common_shelveslist_view_nb_notices'>".$this->format_text($this->msg['cms_module_common_view_shelveslist_build_shelve_nb_notices'])."</label>
			</div>
			<div class='colonne_suite'>
				<input type='number' name='cms_module_common_view_shelveslist_nb_notices' value='".$this->parameters["nb_notices"]."'/>
			</div>
		</div>";
		$form.= parent::get_form();
		return $form;
	}
	
	public function save_form(){
		global $cms_module_common_view_shelveslist_nb_notices;
		$this->save_constructor_link_form("shelve");
		$this->parameters['nb_notices'] = $cms_module_common_view_shelveslist_nb_notices+0;
		return parent::save_form();
	}
	
	public function render($datas){
		global $opac_notices_format;
		global $opac_etagere_order;
		
		// on gère l'ordre des étagères, id asc par défaut
		$critere = "id";
		$dir = "asc";
		if ($opac_etagere_order) {
			$etagere_order = explode(" ", $opac_etagere_order);
			if (($etagere_order[0] == 'name') || ($etagere_order[0] == 'comment')) {
				$critere  =  $etagere_order[0];
			}
			if (isset($etagere_order[1])) {
				$dir = $etagere_order[1];
			}
		}
		
		$order = array();
		foreach ($datas['shelves'] as $shelf) {
			$order[] = $shelf[$critere];
		}
		if ($dir == 'desc') array_multisort($order, SORT_DESC, $datas['shelves']);
		else array_multisort($order, SORT_ASC, $datas['shelves']);
		
		//on gère l'affichage des notices
		foreach($datas["shelves"] as $i => $shelve) {
			$datas['shelves'][$i]['records'] = contenu_etagere($shelve['id'],$this->parameters["nb_notices"],$opac_notices_format,"",1,'./index.php?lvl=etagere_see&id=!!id!!');
		}
		//on rappelle le tout...
		return parent::render($datas);
	}
	
	public function get_format_data_structure(){		
		$format = array();
// 		$format[] = array(
// 			'var' => "title",
// 			'desc' => $this->msg['cms_module_common_view_title']
// 		);
// 		$sections = array(
// 			'var' => "articles",
// 			'desc' => $this->msg['cms_module_common_view_articles_desc'],
// 			'children' => $this->prefix_var_tree(cms_article::get_format_data_structure(),"articles[i]")
// 		);
// 		$sections['children'][] = array(
// 			'var' => "articles[i].link",
// 			'desc'=> $this->msg['cms_module_common_view_article_link_desc']
// 		);
// 		$format[] = $sections;
		return $format;
	}
}