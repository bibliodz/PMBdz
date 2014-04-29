<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_article.class.php,v 1.12 2013-11-27 09:53:24 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/cms/cms_editorial.class.php");



class cms_article extends cms_editorial {
	
	public function __construct($id=0,$num_parent=0){
		//on g�re les propri�t�s communes dans la classe parente
		parent::__construct($id,"article",$num_parent);

		$this->opt_elements = array(
			'contenu' => true
		);
	}
	
	protected function fetch_data(){
		global $lang;
		
		if(!$this->id)
			return false;
		
		// les infos g�n�rales...	
		$rqt = "select * from cms_articles where id_article ='".$this->id."'";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			$row = mysql_fetch_object($res);
			$this->num_type = $row->article_num_type;
			$this->title = $row->article_title;
			$this->resume = $row->article_resume;
			$this->contenu = $row->article_contenu;
			$this->publication_state = $row->article_publication_state;
			$this->start_date = $row->article_start_date;
			$this->end_date = $row->article_end_date;
			$this->num_parent = $row->num_section;		
			$this->create_date = $row->article_creation_date;
		}
		if(strpos($this->start_date,"0000-00-00")!== false){
			$this->start_date = "";
		}
		if(strpos($this->end_date,"0000-00-00")!== false){
			$this->end_date = "";
		}

		$this->get_descriptors();
		$this->get_fields_type();
		$this->get_documents();
	}

	public function save(){
		if($this->id){
			$save = "update ";
			$order = "";
			$clause = "where id_article = '".$this->id."'";
		}else{
			$save = "insert into ";
			
			//on place le nouvel article � la fin par d�faut
			$query = "SELECT id_article FROM cms_articles WHERE num_section=".addslashes($this->num_parent);
			$result = mysql_query($query);
			$order = ",article_order = '".(mysql_num_rows($result)+1)."' ";
			
			$clause = "";
		}
		$save.= "cms_articles set 
		article_title = '".addslashes($this->title)."', 
		article_resume = '".addslashes($this->resume)."', 
		article_contenu = '".addslashes($this->contenu)."',
		article_publication_state ='".addslashes($this->publication_state)."', 
		article_start_date = '".addslashes($this->start_date)."', 
		article_end_date = '".addslashes($this->end_date)."', 
		num_section = '".addslashes($this->num_parent)."', 
		article_num_type = '".$this->num_type."' ".
		(!$this->id ? ",article_creation_date=sysdate() " :"")."
		$order"."
		$clause";
		mysql_query($save);
		if(!$this->id) $this->id = mysql_insert_id();
		//au tour des descripteurs...
		//on commence par tout retirer...
		$del = "delete from cms_articles_descriptors where num_article = '".$this->id."'";
		mysql_query($del);
		for($i=0 ; $i<count($this->descriptors) ; $i++){
			$rqt = "insert into cms_articles_descriptors set num_article = '".$this->id."', num_noeud = '".$this->descriptors[$i]."',article_descriptor_order='".$i."'";
			mysql_query($rqt);
		}
			
		//et maintenant le logo...
		$this->save_logo();
		
		//enfin les �l�ments du type de contenu
		$types = new cms_editorial_types("article");
		$types->save_type_form($this->num_type,$this->id);
		$this->maj_indexation();
		
		$this->save_documents();
	}	
	
	public function duplicate($num_parent = 0) {
		if (!$num_parent) $num_parent = $this->num_parent;
			
		//on place le nouvel article � la fin par d�faut
		$query = "SELECT id_article FROM cms_articles WHERE num_section=".addslashes($num_parent);
		$result = mysql_query($query);
		if ($result) $order = ",article_order = '".(mysql_num_rows($result)+1)."' ";
		else $order = ",article_order = 1";
		
		$insert = "insert into cms_articles set 
		article_title = '".addslashes($this->title)."', 
		article_resume = '".addslashes($this->resume)."', 
		article_contenu = '".addslashes($this->contenu)."',
		article_logo = '".addslashes($this->logo->data)."',
		article_publication_state ='".addslashes($this->publication_state)."', 
		article_start_date = '".addslashes($this->start_date)."', 
		article_end_date = '".addslashes($this->end_date)."', 
		num_section = '".addslashes($num_parent)."', 
		article_num_type = '".$this->num_type."',
		article_creation_date=sysdate() ".$order;
		
		mysql_query($insert);
		$id = mysql_insert_id();
		
		//au tour des descripteurs...
		for($i=0 ; $i<count($this->descriptors) ; $i++){
			$rqt = "insert into cms_articles_descriptors set num_article = '".$id."', num_noeud = '".$this->descriptors[$i]."',article_descriptor_order='".$i."'";
			mysql_query($rqt);
		}
		
		//on cr�e la nouvelle instance
		$new_article = new cms_article($id);
		
		//enfin les �l�ments du type de contenu
		$types = new cms_editorial_types("article");
		$types->save_type_form($this->num_type,$this->id);
		$new_article->maj_indexation();
		
		$new_article->documents_linked = $this->documents_linked;
		$new_article->save_documents();
	}
	
	public function get_parent_selector(){
		$opts.=$this->_recurse_parent_select();
		return $opts;
	}
	
	protected function _recurse_parent_select($parent=0,$lvl=0){
		global $charset;
		global $msg;
		$opts = "";
		$rqt = "select id_section, section_title from cms_sections where section_num_parent = '".$parent."'";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				$opts.="
				<option value='".$row->id_section."'".($this->num_parent == $row->id_section ? " selected='selected'" : "").">".str_repeat("&nbsp;&nbsp;",$lvl).htmlentities($row->section_title,ENT_QUOTES,$charset)."</option>";
				$opts.=$this->_recurse_parent_select($row->id_section,$lvl+1);
			}	
		}
		return $opts;	
	}
	
	public function update_parent_section($num_section,$order=0){
		$this->num_section = $num_section;
		$update = "update cms_articles set num_section ='".$num_section."', article_order = '".$order."' where id_article = '".$this->id."'";
		mysql_query($update);
	}
	
	protected function is_deletable(){
		return true;
	}
	
	public function format_datas(){
		if ($this->logo->data) $logo_exists = true;
		else $logo_exists = false;
		$parent = new cms_section($this->num_parent);
		return array(
			'id' => $this->id,
			'parent' => $parent->format_datas(false,false),
			'title' => $this->title,
			'resume' => $this->resume,
			'logo' => array(
				'small_vign' => $this->logo->get_vign_url("small_vign"),
				'vign' =>$this->logo->get_vign_url("vign"),
				'large' =>$this->logo->get_vign_url("large"),
				'exists' => $logo_exists
			),
			'publication_state' => $this->publication_state,
			'start_date' => format_date($this->start_date),
			'end_date' => format_date($this->end_date),
			'descriptors' => $this->descriptors,
			'content' => $this->contenu,
			'type' => $this->type_content,
			'fields_type' => $this->fields_type,
			'create_date' => $this->create_date
		);
	}
	
	public static function get_format_data_structure($full=true){
		return cms_editorial::get_format_data_structure("article",$full);
	}
}