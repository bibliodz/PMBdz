<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_section.class.php,v 1.19 2013-11-27 09:53:25 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/cms/cms_editorial.class.php");

class cms_section extends cms_editorial {
	public $num_parent;		// id du parent
	function __construct($id=0,$num_parent=0){
		//on g�re les propri�t�s communes dans la classe parente
		parent::__construct($id,"section");

		if($this->id == 0){
			$this->num_parent = $num_parent;
		}
		$this->opt_elements =array(
			'contenu' => false,
		);
	}

	protected function fetch_data(){
		$rqt = "select section_title,section_resume,section_publication_state,section_start_date,section_end_date,section_num_parent,section_num_type,section_creation_date from cms_sections where id_section ='".$this->id."'";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			$row = mysql_fetch_object($res);
			$this->num_type = $row->section_num_type;
			$this->title = $row->section_title;
			$this->resume = $row->section_resume;
			$this->publication_state = $row->section_publication_state;
			$this->start_date = $row->section_start_date;
			$this->end_date = $row->section_end_date;
			$this->num_parent = $row->section_num_parent;		
			$this->create_date = $row->section_creation_date;	
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
			$clause = "where id_section = '".$this->id."'";
		}else{
			$save = "insert into ";
			
			//on place la nouvelle rubrique � la fin par d�faut
			$query = "SELECT id_section FROM cms_sections WHERE section_num_parent=".addslashes($this->num_parent);
			$result = mysql_query($query);
			$order = ",section_order = '".(mysql_num_rows($result)+1)."' ";
			
			$clause = "";
		}
		$save.= "cms_sections set 
		section_title = '".addslashes($this->title)."', 
		section_resume = '".addslashes($this->resume)."', 
		section_publication_state ='".addslashes($this->publication_state)."', 
		section_start_date = '".addslashes($this->start_date)."', 
		section_end_date = '".addslashes($this->end_date)."', 
		section_num_parent = '".addslashes($this->num_parent)."' ,
		section_num_type = '".$this->num_type."'  ".
		(!$this->id ? ",section_creation_date=sysdate() " :"")."
		$order"."
		$clause";
		mysql_query($save);
		if(!$this->id) $this->id = mysql_insert_id();
		
		//au tour des descripteurs...
		//on commence par tout retirer...
		$del = "delete from cms_sections_descriptors where num_section = '".$this->id."'";
		mysql_query($del);
		for($i=0 ; $i<count($this->descriptors) ; $i++){
			$rqt = "insert into cms_sections_descriptors set num_section = '".$this->id."', num_noeud = '".$this->descriptors[$i]."',section_descriptor_order='".$i."'";
			mysql_query($rqt);
		}
		
		//et maintenant le logo...
		$this->save_logo();
		
		//enfin les �l�ments du type de contenu
		$types = new cms_editorial_types("section");
		$types->save_type_form($this->num_type,$this->id);
		$this->maj_indexation();
		
		$this->save_documents();
	}
	
	public function duplicate($recursive, $num_parent = 0) {
		if (!$num_parent) $num_parent = $this->num_parent;
			
		//on place la nouvelle rubrique � la fin par d�faut
		$query = "SELECT id_section FROM cms_sections WHERE section_num_parent=".addslashes($num_parent);
		$result = mysql_query($query);
		if ($result) $order = ",section_order = '".(mysql_num_rows($result)+1)."' ";
		else $order = ",section_order = 1";
		
		$insert = "insert into cms_sections set
		section_title = '".addslashes($this->title)."',
		section_resume = '".addslashes($this->resume)."',
		section_logo = '".addslashes($this->logo->data)."',
		section_publication_state ='".addslashes($this->publication_state)."',
		section_start_date = '".addslashes($this->start_date)."',
		section_end_date = '".addslashes($this->end_date)."',
		section_num_parent = '".addslashes($num_parent)."' ,
		section_num_type = '".$this->num_type."' ,
		section_creation_date=sysdate() ".$order;
		
		mysql_query($insert);
		$id = mysql_insert_id();
		
		//au tour des descripteurs...
		for($i=0 ; $i<count($this->descriptors) ; $i++){
			$rqt = "insert into cms_sections_descriptors set num_section = '".$id."', num_noeud = '".$this->descriptors[$i]."',section_descriptor_order='".$i."'";
			mysql_query($rqt);
		}
		
		//on cr�e la nouvelle instance
		$new_section = new cms_section($id);
		
		//enfin les �l�ments du type de contenu
		$types = new cms_editorial_types("section");
		$types->save_type_form($this->num_type,$id);
		$new_section->maj_indexation();
		
		$new_section->documents_linked = $this->documents_linked;
		$new_section->save_documents();
		
		if ($recursive) {
			//on duplique les rubriques enfants
			$query = "select id_section from cms_sections where section_num_parent = ".$this->id." order by section_order";
			$result = mysql_query($query);
			if ($result && mysql_num_rows($result)) {
				while ($row = mysql_fetch_object($result)) {
					$child = new cms_section($row->id_section);
					$child->duplicate($recursive,$id);
				}
			}
			
			//on duplique les articles enfants
			$query = "select id_article from cms_articles where num_section = ".$this->id." order by article_order";
			$result = mysql_query($query);
			if ($result && mysql_num_rows($result)) {
				while ($row = mysql_fetch_object($result)) {
					$article = new cms_article($row->id_article);
					$article->duplicate($id);
				}
			}
		}
	}
	
	public function get_parent_selector(){
		$opts.=$this->_recurse_parent_select();
		return $opts;
	}
	
	protected function _recurse_parent_select($parent=0,$lvl=0){
		global $charset;
		global $msg;
		if($lvl==0){
			$opts = "
			<option value='0' >".htmlentities($msg['cms_editorial_form_parent_default_value'],ENT_QUOTES,$charset)."</option>";
		}else{
			$opts = "";
		}
		$rqt = "select id_section, section_title from cms_sections where section_num_parent = '".$parent."'";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				if($this->id != $row->id_section){
					$opts.="
				<option value='".$row->id_section."'".($this->num_parent == $row->id_section ? " selected='selected'" : "").">".str_repeat("&nbsp;&nbsp;",$lvl).htmlentities($row->section_title,ENT_QUOTES,$charset)."</option>";
					$opts.=$this->_recurse_parent_select($row->id_section,$lvl+1);
				}
			}	
		}
		return $opts;	
	}	

	public function is_deletable(){
		global $msg;
		//on commence par regarder si la rubrique � des articles...
		$check_article = "select count(id_article) from cms_articles where num_section ='".$this->id."'";
		$res = mysql_query($check_article);
		if(mysql_num_rows($res)>0){
			$nb_articles = mysql_result($res,0,0);
			if($nb_articles>0){
				return $msg['cms_section_cant_delete_with_articles'];
			};
		}
		//on est encore la donc pas d'articles, on regarde les rubriques filles...
		$check_children = "select count(id_section) from cms_sections where section_num_parent ='".$this->id."'";
		$res = mysql_query($check_children);
		if(mysql_num_rows($res)){
			$nb_children = mysql_result($res,0,0);
			if($nb_children>0){
				return $msg['cms_section_has_children'];
			}
		}
		return true;
	}
	
	public function format_datas($get_children= true,$get_articles = true,$filter = true){
		if ($this->logo->data) $logo_exists = true;
		else $logo_exists = false;
		$result = array(
			'id' => $this->id,
			'num_parent' =>$this->num_parent,
			'title' => $this->title,
			'resume' => $this->resume,
			'logo' => array(
				'small_vign' => $this->logo->get_vign_url("small_vign"),
				'vign' =>$this->logo->get_vign_url("vign"),
				'large' =>$this->logo->get_vign_url("large"),
				'exists' => $logo_exists
			),
			'publication_state' => $this->publication_state,
			'start_date' => $this->start_date,
			'end_date' => $this->end_date,
			'descriptors' => $this->descriptors,
			'type' => $this->type_content,
			'fields_type' => $this->fields_type,
			'create_date' => $this->create_date
		);
		if($get_children){
			$result['children'] = $this->get_children($filter);
		}
		if($get_articles){
			$result['articles'] = $this->get_articles($filter);
		}
		return $result;
	}
	
	public function get_children($filter){
		$children = array();
		if($this->id){
			$query = "select id_section from cms_sections where section_num_parent = ".$this->id;
			if($filter){
				$query.= " and ((section_start_date != 0 and to_days(section_start_date)<=to_days(now()) and to_days(section_end_date)>=to_days(now()))||(section_start_date != 0 and section_end_date =0 and to_days(section_start_date)<=to_days(now()))||(section_start_date = 0 and to_days(section_end_date)>=to_days(now()))||(section_start_date = 0 and section_end_date = 0))";
			}
			$query .= " order by section_order";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while ($row = mysql_fetch_object($result)){
					$child = new cms_section($row->id_section);
					$children[] = $child->format_datas();
				}
			}
		}	
		return $children;	
	}
	
	public function get_articles($filter){
		$articles = array();
		if($this->id){
			$query = "select id_article from cms_articles JOIN cms_editorial_publications_states ON article_publication_state=id_publication_state where num_section = ".$this->id." and (editorial_publication_state_opac_show=1".(!$_SESSION['id_empr_session'] ? " and editorial_publication_state_auth_opac_show = 0" : "").") ";
			if($filter){
				$query.= " and ((article_start_date != 0 and to_days(article_start_date)<=to_days(now()) and to_days(article_end_date)>=to_days(now()))||(article_start_date != 0 and article_end_date =0 and to_days(article_start_date)<=to_days(now()))||(article_start_date=0 and article_end_date=0)||(article_start_date = 0 and to_days(article_end_date)>=to_days(now())))";
			}
			$query .= " order by article_order";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while ($row = mysql_fetch_object($result)){
					$article = new cms_article($row->id_article);
					$articles[] = $article->format_datas();
				}
			}	
		}
		return $articles;			
	}
	
	public static function get_format_data_structure($get_children= true,$get_articles = true,$full=true){
		global $msg;
		$format = cms_editorial::get_format_data_structure("section",$full);
		if($get_children){
			$format[] = array(
				'var' => 'children',
				'desc'=> $msg['cms_editorial_desc_children'],
				'children' => self::prefix_var_tree(cms_section::get_format_data_structure(false,false),"children[i]")
			);
		}
		if($get_articles){
			$format[] = array(
				'var' => 'articles',
				'desc'=> $msg['cms_editorial_desc_articles'],
				'children' => self::prefix_var_tree(cms_article::get_format_data_structure(),"articles[i]")
			);			
		}			
		return $format;
	}
}