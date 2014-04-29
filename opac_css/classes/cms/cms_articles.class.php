<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_articles.class.php,v 1.2 2013-07-26 14:29:36 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/cms/cms_articles.tpl.php");

//g�re une liste d'articles
class cms_articles {
	public $num_section;	// id de la rubrique parente
	protected $recursive;	// bol�en d�finissant si on veut aussi les articles des rubriques filles...
	public $list;			// tableau contenant les ids des articles � lister...
	public $section_title;	// titre de la rubrique 
	
	public function __construct($num_section=0,$recursive = false){
		$this->num_section = $num_section;
		$this->recursive = $recursive;
		$this->list = array();
		$this->fetch_data();
	}
	
	protected function fetch_data(){
		
		$this->_recursive_fetch_data($this->num_section);
	}
	
	protected function _recursive_fetch_data($num_parent=0){
		if($num_parent != 0){
			$rqt = "select id_article from cms_articles where num_section='".$num_parent."'";
			$res = mysql_query($rqt);
			if(mysql_num_rows($res)){
				while($row = mysql_fetch_object($res)){
					$this->list[]=$row->id_article;
				}
			}
		}
		if($this->recursive){
			$rqt = "select id_section from cms_sections where section_num_parent = '".$num_parent."'";
			$res = mysql_query($rqt);
			if(mysql_num_rows($res)){
				while($row = mysql_fetch_object($res)){
					$this->_recursive_fetch_data($row->id_section);
				}
			}	
		}
	}
	
	public function get_list(){
		return $this->list;
	}
	
	public function get_nb_articles(){
		return count($this->list);
	}	
	
	public function get_section_title(){
		if(!$this->section_title){
			$rqt = "select section_title from cms_sections where id_section='".$this->num_section."'";
			$res = mysql_query($rqt);
			if(mysql_num_rows($res)){
				$this->section_title = mysql_result($res,0,0);
			}
		}
		return $this->section_title;
	}
	
	public function get_tab(){
		global $msg;
		global $cms_articles_list, $cms_articles_list_item;
		
		$list = str_replace("!!cms_articles_list_title!!", sprintf($msg['cms_articles_list_title'],$this->get_section_title()),$cms_articles_list);
		if($this->get_nb_articles()){
			$rqt = "select *from cms_articles where id_article in (".implode(",",$this->list).")";
			$res = mysql_query($rqt);
			$items="";
			if(mysql_num_rows($res)){
				while($row = mysql_fetch_object($res)){
					$item = str_replace("!!cms_article_logo_src!!","",$cms_articles_list_item);
					$item = str_replace("!!cms_article_title!!",htmlentities($row->article_title,ENT_QUOTES),$item);
					$items.=$item;
				}	
			}
		}
		
		return str_replace("!!items!!",$items,$list);
	}
}
