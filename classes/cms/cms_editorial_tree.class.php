<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_tree.class.php,v 1.3 2013-11-27 09:53:25 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/cms/cms_editorial_tree.tpl.php");

//gère l'arbre éditorial
class cms_editorial_tree {
	public $tree=array();		// tableau représentant l'arbre
	protected $inc_articles;	// booléen précisant si on veut les articles avec ou non...
	
	public function __construct($inc_articles=true){
		$this->inc_articles = $inc_articles;
	}
	
	protected function fetch_data(){
		global $charset;
		
		$rqt = "select id_section, section_title, section_num_parent, if(section_logo!='',1,0) as logo_exist  from cms_sections order by section_order";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				$infos = array(
					'id' => $row->id_section,
					'title' => ($charset!= "utf-8" ? utf8_encode($row->section_title) : $row->section_title),
					'type' => ($row->section_num_parent == 0 ? "root_section": 'section')
				);
				if($row->logo_exist == 1){
					$infos['icon'] =  "./cms_vign.php?type=section&id=".$row->id_section."&mode=small_vign";
				}
				$sub_rqt = "select id_section, section_order from cms_sections where section_num_parent='".$row->id_section."' ORDER BY section_order ASC";
				$sub_res = mysql_query($sub_rqt);
				if(mysql_num_rows($sub_res)){
					while($sub_row = mysql_fetch_object($sub_res)){
						$infos['children'][]['_reference'] = $sub_row->id_section;
					}
				}
				if($this->inc_articles){
					$art_rqt = "select id_article, article_title, if(article_logo!='',1,0) as logo_exist from cms_articles where num_section ='".$row->id_section."' ORDER BY article_order ASC";
					$art_res = mysql_query($art_rqt);
					if(mysql_num_rows($art_res)){
						//on ajout un éléments Articles qui contiendra la liste des articles
						$infos['children'][]['_reference']= "articles_".$row->id_section;
						$art_content_infos = array(
							'id' => "articles_".$row->id_section,
							'title' => "Articles",
							'type' => 'articles'					
						);
						while ($art_row = mysql_fetch_object($art_res)){
							$art_content_infos['children'][]['_reference']= "article_".$art_row->id_article;
							$art_infos = array(
								'id' => "article_".$art_row->id_article,
								'title' => $charset!= "utf-8" ? utf8_encode($art_row->article_title) : $art_row->article_title,
								'type' => 'article'			
							);
							if($art_row->logo_exist == 1){
								$art_infos['icon'] =  "./cms_vign.php?type=article&id=".$art_row->id_article."&mode=small_vign";
							}
							$this->tree[]=$art_infos;
						}
						$this->tree[]=$art_content_infos;
					}
				}
				$this->tree[]=$infos;
			}
		}
	}
	
	public function get_json_list(){
		if(count($this->tree) == 0){
			$this->fetch_data();
		}
		$json = array(
			'identifier' => 'id',
			'label' => 'title',
			'items' => $this->tree
		);
		return json_encode($json);
	}
	
	public function get_listing(){
		global $cms_editorial_tree_layout;
		return $cms_editorial_tree_layout;
	}
	
	public function get_tree(){
		global $cms_editorial_tree_content;
		return $cms_editorial_tree_content;
	}
	
	public function update_children($children,$num_parent){
		$children = explode(",", $children);
		$cpt = 1;
		foreach ($children as $child) {
			$rqt = "UPDATE cms_sections SET section_num_parent='".$num_parent."', section_order='".$cpt."' WHERE id_section='".$child."'";
			$res = mysql_query($rqt);
			$cpt++;
			if (!$res) return "$rqt";
		}
		return "done";
	}
}