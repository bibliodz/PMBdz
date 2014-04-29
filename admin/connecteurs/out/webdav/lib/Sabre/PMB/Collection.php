<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Collection.php,v 1.19 2013-04-25 16:02:17 mbertin Exp $
namespace Sabre\PMB;

use Sabre\DAV;
use Sabre\PMB;

class Collection extends DAV\Collection {
	public $type;
	public $config;
	public $restricted_notices;
	
	function __construct($config){
		$this->config = $config;
	}
	
	function get_code_from_name($name){
		$val="";
		if(preg_match("/\((T|[NTCSIE][0-9]{1,})\)$/i",$name,$matches)){
			$val=$matches[1];
		}elseif(preg_match("/\((T|[NTCSIE][0-9]{1,})\)\./i",$name,$matches)){
			$val=$matches[1];
		}
		return $val;
	}
	
	function get_notice_by_meta($name,$filename){
		global $pmb_keyword_sep;
		global $pmb_type_audit;
		global $webdav_current_user_name,$webdav_current_user_id;
		
		\create_tableau_mimetype();
		$mimetype = \trouve_mimetype($filename,extension_fichier($name));
		
		$notice_id = 0;
		$title = $cplt = $code = $pages = $year = $keywords = $url = $thumbnail_content = "";
		//on commence avec la gymnatisque des m�tas...
		if($mimetype == "application/epub+zip"){
			//pour les ebook, on g�re ca directement ici !
			$epub = new \epubData(realpath($filename));
			$title = $epub->metas['title'][0];
			$authors = $epub->metas['creator'];
			$co_authors = $epub->metas['contributor'];
			if($epub->metas['identifier']['isbn']){
				$code = \formatISBN($epub->metas['identifier']['isbn'],13);
			}else if($epub->metas['identifier']['ean']){
				$code = \EANtoISBN($epub->metas['identifier']['ean']);
				$code = \formatISBN($code,13);
			}
			if($epub->metas['identifier']['uri']){
				$url = \clean_string($epub->metas['identifier']['uri']);
			}
			
			$publisher = $epub->metas['publisher'][0];
			$year = $epub->metas['date'][0]['value'];
			if(strlen($year) && strlen($year) != 4){
				$year = \formatdate(detectFormatDate($year));
			}
			$lang= $epub->metas['language'];
			$resume = implode("\n",$epub->metas['description']);
			$keywords = implode($pmb_keyword_sep,$epub->metas['subject']);
			
			//jouons � et si on trouvait a vignette...
			$img = imagecreatefromstring($epub->getCoverContent());
			$file=tempnam(sys_get_temp_dir(),"vign");
			imagepng($img,$file);
			$thumbnail_content = file_get_contents($file);
			unlink($file);
		}else{
			$metas = \extract_metas(realpath($filename),$mimetype);
			if($metas['Title'] && $metas['Author'] && $metas['Subject']){
				$title = $metas['Title'];
				$author = $metas['Author'];
				$cplt = $metas['Subject'];
			}else{
				// m�tas non fiable, on regarde avec le titre...
				$title = $name;
			}
	
			//date de cr�ation...
			if($metas["CreateDate"]){
				$year = substr($metas["CreateDate"],0,4);
			}
			//pages
			if($metas['PageCount']){
				$pages = $metas['PageCount'];
			}
			//keywords
			if($metas['Keywords']){
				foreach($metas['Keywords'] as $keyword){
					if($keywords != "")	$keywords.= $pmb_keyword_sep;
					$keywords.=$keyword;
				}
			}
		}		
		
		
		$query = "select notice_id from notices where tit1 = '".addslashes($title)."'";
		$result= mysql_query($query);
		if(mysql_num_rows($result)){
			$notice_id = mysql_result($result,0,0);
		}
		if(!$notice_id){
			//en cas d'une leture moyenne des infos, on s'assure d'avoir au moins un titre....
			if(!$title) $title = $name;
			
			
			if($publisher){
				$ed_1 = \editeur::import(array('name'=>$publisher));
			}else $ed_1 = 0;
			

			$ind_wew = $title." ".$cplt;
			$ind_sew = \strip_empty_words($ind_wew) ; 
			
			$query = "insert into notices set 
				tit1 = '".addslashes($title)."',". 
				($code ? "code='".$code."',":"").
				"ed1_id = '".$ed_1."',".
				($cplt ? "tit4 = '".addslashes($cplt)."'," : "").
				($pages ? "npages = '".addslashes($pages)."'," : "").
				($keywords ? "index_l = '".addslashes($keywords)."'," : "")."
				year = '".$year."',
				niveau_biblio='m', 
				niveau_hierar='0',
				statut = '".$this->config['default_statut']."',
				index_wew = '".$ind_wew."',
				index_sew = '".$ind_sew."',
				n_resume = '".addslashes($resume)."',
				lien = '".addslashes($url)."',
				index_n_resume = '".\strip_empty_words($resume)."',".
				($thumbnail_content ? "thumbnail_url = 'data:image/png;base64,".base64_encode($thumbnail_content)."',":"").
				"create_date = sysdate(), 
				update_date = sysdate()";
			mysql_query($query);
			$notice_id = mysql_insert_id();
			$sign = new \notice_doublon();
			mysql_query("update notices set signature = '".$sign->gen_signature($notice_id)."' where notice_id = ".$notice_id);
			
			//traitement audit 
			if ($pmb_type_audit) {
				$query = "INSERT INTO audit SET ";
				$query .= "type_obj='1', ";
				$query .= "object_id='$notice_id', ";
				$query .= "user_id='$webdav_current_user_id', ";
				$query .= "user_name='$webdav_current_user_name', ";
				$query .= "type_modif=1 ";
				$result = @mysql_query($query);	
			}
			
			if(count($authors)){
				$i=0;
				foreach($authors as $author){
					$aut = array();
					if($author['file-as']){
						$infos = explode(",",$author['file-as']);
						$aut = array(
							'name' => $infos[0],
							'rejete' => $infos[1],
							'type' => 70
						);
					}
					if(!$aut['name']){
						$aut = array(
							'name' => $author['value'],
							'type' => 70
						);
					}
					$aut_id = \auteur::import($aut);			
					if($aut_id){
						$query = "insert into responsability set 
							responsability_author = '".$aut_id."',
							responsability_notice = '".$notice_id."',
							responsability_type = '0'";
						mysql_query($query);
						$i++;
					}
				}
			}
			if(count($co_authors)){
				foreach($co_authors as $author){
					$aut = array();
					if($author['file-as']){
						$infos = explode(",",$author['file-as']);
						$aut = array(
							'name' => $infos[0],
							'rejete' => $infos[1],
							'type' => 70
						);
					}
					if(!$aut['name']){
						$aut = array(
							'name' => $author['value'],
							'type' => 70
						);
					}
					$aut_id = \auteur::import($aut);			
					if($aut_id){
						$query = "insert into responsability set 
							responsability_author = '".$aut_id."',
							responsability_notice = '".$notice_id."',
							responsability_type = '0',
							repsonsability_ordre = '".$i."'";
						mysql_query($query);
						$i++;
					}
				}
			}			
		}
		return $notice_id;
	}
	
	function set_parent($parent){
		$this->parentNode = $parent;
	}
	function getChildren(){
		global $tdoc;
		
		$children = array();
		$children_type = "";
		if($this->type == "rootNode"){
			$children_type = $this->config['tree'][0];
		}else{
			for($i=0 ; $i<count($this->config['tree']) ; $i++){
				if($this->config['tree'][$i] == $this->type){
					if($this->config['tree'][$i+1]){
						$children_type = $this->config['tree'][$i+1];
					}
					break;
				}
			}
		}
		$tmp=$this->getNotices();//On calcule les restrictions
		switch($children_type){
			case "categorie" :
				$thes = new \thesaurus($this->config['used_thesaurus']);
				$node = new PMB\Categorie("(C".$thes->num_noeud_racine.")",$this->config);
				$node->restricted_notices=$this->restricted_notices;//On prends en compte les restrictions pour le cas on ne voudrait que les cat�gories avec des notices
				$children = $node->getChildren();
				break;
			case "typdoc" :
				if (!sizeof($tdoc)) $tdoc = new \marc_list('doctype');
				foreach($tdoc->table as $label){
					$children[] = new PMB\Typdoc($label. " (T)" ,$this->config); 
				}
				break;
			case "statut" :
				$query = "select id_notice_statut from notice_statut";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					while($row = mysql_fetch_object($result)){
						$children[] = new PMB\Statut("(S".$row->id_notice_statut.")",$this->config);
					}
				}
				break;
			case "indexint" :
				$query = "select * from indexint where indexint_id not in (select child.indexint_id from indexint join indexint as child on child.indexint_name like concat(indexint.indexint_name,'%') and indexint.indexint_id != child.indexint_id group by child.indexint_id) order by indexint_name";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					while($row = mysql_fetch_object($result)){
						$children[] = new PMB\Indexint("(I".$row->indexint_id.")",$this->config);
					}
				}	
				break;
			default :
				break;
		}
		usort($children,"sortChildren");
		if((count($tmp)>0) && ($tmp[0] != "'ensemble_vide'")){
			$children = array_merge(array(new PMB\Notices($tmp,$this->config)),$children);
		}
		return $children;
	}
	
	function getChild($name){
		switch($name){
			case "[Notices]" :
				$child = new PMB\Notices($this->getNotices(),$this->config);
				break;
			default :
				$code = $this->get_code_from_name($name);
				if($code === "T" || substr($code,1)*1 > 0){
					switch(substr($code,0,1)){
						//notice 
						case "N" :
							//on v�rifie juste pour pas se faire avoir...
							$child = new PMB\Notice("(".$code.")",$this->config);
							break;
						//typdoc
						case "T" :
							$child = new PMB\Typdoc($name,$this->config);	
							break;
						//categorie
						case "C" :
							$child = new PMB\Categorie("(".$code.")",$this->config);	
							break;
						//statut de notice
						case "S" :
							$child = new PMB\Statut("(".$code.")",$this->config);
							break;	
						//indexint
						case "I" :
							$child = new PMB\Indexint("(".$code.")",$this->config);	
							break;
						//explnum
						case "E" :
							$child = new PMB\Explnum("(".$code.")");
							break;	
						default :
							throw new DAV\Exception\BadRequest('Bad Request: ' . $name);
							break;
					}
				}else{
					//document num�rique d'une notice
					$query = "select distinct explnum_id,notice_id from explnum join notices on explnum_bulletin = 0 and explnum_notice = notice_id where explnum_nomfichier = '".addslashes($name)."' and explnum_mimetype != 'URL'";
					//document num�riques d'une notice de bulletin
					$query.= "union select distinct explnum_id,notice_id from explnum join bulletins on explnum_notice = 0 and explnum_bulletin = bulletin_id join notices on num_notice != 0 and num_notice = notice_id where explnum_nomfichier = '".addslashes($name)."' and explnum_mimetype != 'URL'";
					//$query = $this->filterExplnums($query);
					$result  = mysql_query($query);
					if(mysql_num_rows($result)){
						$row = mysql_fetch_object($result);
						$child = new PMB\Explnum("(E".$row->explnum_id.")");
					}else{
						throw new DAV\Exception\FileNotFound('File not found: ' . $name);
					}
					break;
				}
		}
		return $child;
	}
	
	
	function childExists($name){
		//pour les besoin des tests, on veut passer par la m�thode de cr�ation...
		return false;
		switch($name){
			case "[Notices]" :
				if(count($this->getNotices())>0){
					return true;
				}else return false;
				break;
			default :
				$code = $this->get_code_from_name($name);
				if($code === "T" || substr($code,1)*1 > 0){
					switch(substr($code,0,1)){
						//notice 
						case "N" :
						case "T" :
						case "C" :
						case "S" :
						case "I" :
						case "E" :
							return true;
							break;	
						default :
							return false;
							break;
					}
				}else{
					$query = "select distinct explnum_id from explnum where explnum_nomfichier = '".addslashes($name)."'";
					$result  = mysql_query($query);
					if(mysql_num_rows($result)){
						return true;
					}else{
						return false;
					}
					break;
				}
		}
	}
	
	function getName(){
		//must be defined
	}
	
	function createFile($name, $data = null) {
		if($this->check_write_permission()){
			global $base_path;
			global $id_rep;
			global $charset;
			
			if($charset !=='utf-8'){
				$name=utf8_decode($name);
			}
			$filename = realpath($base_path."/temp/")."/webdav_".md5($name.time()).".".extension_fichier($name);
			$fp = fopen($filename, "w");
			if(!$fp){
				//on a pas le droit d'�criture 
				throw new DAV\Exception\Forbidden('Permission denied to create file (filename ' . $filename . ')');
			}
			
			while ($buf = fread($data, 1024)){
				fwrite($fp, $buf);
			}
			fclose($fp);
			if(!filesize($filename)){
				//Erreur de copie du fichier
				//unlink($filename);
				//throw new Sabre_DAV_Exception_FileNotFound('Empty file (filename ' . $filename . ')');
			}	
			
			$notice_id = $this->get_notice_by_meta($name,$filename);
			$this->update_notice($notice_id);

			$explnum = new \explnum(0,$notice_id);
			$id_rep = $this->config['upload_rep'];
			$explnum->get_file_from_temp($filename,$name,$this->config['up_place']);
			$explnum->update();
			if(file_exists($filename)){
				unlink($filename);
			}
		}else{
			//on a pas le droit d'�criture 
			throw new DAV\Exception\Forbidden('Permission denied to create file (filename ' . $name . ')');
		}
    }
	
    
    function update_notice($notice_id){
    	global $pmb_type_audit;
		global $webdav_current_user_name,$webdav_current_user_id;
		global $gestion_acces_active, $gestion_acces_user_notice, $gestion_acces_empr_notice;
    	
    	$obj = $this;
    	$type = $obj->type;
    	$obj->update_notice_infos($notice_id);
    	while ($obj = $obj->parentNode){
    		if($obj->type != $type){
    			$type = $obj->type;
    			$obj->update_notice_infos($notice_id);
    		}
    	}
    	if ($pmb_type_audit) {
			$query = "INSERT INTO audit SET ";
			$query .= "type_obj='1', ";
			$query .= "object_id='$notice_id', ";
			$query .= "user_id='$webdav_current_user_id', ";
			$query .= "user_name='$webdav_current_user_name', ";
			$query .= "type_modif=2 ";
			$result = @mysql_query($query);	
		}
		
		\notice::majNoticesGlobalIndex($notice_id);
		\notice::majNoticesMotsGlobalIndex($notice_id);
		
		//TODO - Calcul des droits sur la notice dans les 2 domaines... 
		$ac = new \acces();
		//pour la gestion
		if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
			$dom_1 = $ac->setDomain(1);
			$dom_1->applyRessourceRights($notice_id);
		}
		//pour l'opac
		if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
			$dom_2 = $ac->setDomain(2);
			$dom_2->applyRessourceRights($notice_id);
		}
    }
    
    function update_notice_infos($notice_id){
    	//must be defined
    }
    
    function filterNotices($query){
    	//on remonte d'abord les parents...
    	$current = $this;
    	$parents = array();
    	while($current->parentNode != null && $current->parentNode->type != "rootNode"){
    		$parents[] = $current->parentNode;
    		$current=$current->parentNode;
    	}
    	$parents = array_reverse($parents);
    	foreach($parents as $parent){
    		$parent->getNotices();
    	}
    	
    	global $gestion_acces_active,$gestion_acces_user_notice,$gestion_acces_empr_notice;
		global $webdav_current_user_id;
 		switch($this->config['authentication']){
			case "gestion" :
				$acces_j='';
				//soit les droits d'acc�s sont activ�s et il est possible que la notice ne soit pas visible pour certaines personnes
				//soit c'est la requete de base
				if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(1);
					$acces_j = $dom_1->getJoin($webdav_current_user_id,3,'notice_id');
					$query = "select notice_id from (".$query.") as uni ".$acces_j;
					if($this->parentNode && $this->parentNode->restricted_notices){
						$query.= " where uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
					}
				}elseif($this->parentNode && $this->parentNode->restricted_notices){//Si la gestion des droits n'est pas activ� il faut quand m�me restreindre la recherche
					$query = "select notice_id from (".$query.") as uni ";
					$query.= " where uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
				} 
				break;
			case "opac" :
				$acces_j='';
				//droit d'acc�s ou statut
				if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(2);
					$acces_j = $dom_1->getJoin($webdav_current_user_id,16,'notice_id');
					$query = "select notice_id from (".$query.") as uni ".$acces_j;
					if($this->parentNode && $this->parentNode->restricted_notices){
						$query.= " where uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
					}
				}else{
					$query = "select uni.notice_id from (".$query.") as uni join notices on notices.notice_id = uni.notice_id join notice_statut on notices.statut= id_notice_statut where ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($webdav_current_user_id ?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
					if($this->parentNode && $this->parentNode->restricted_notices){
						$query.= " and uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
					}
				}
				break;
			case "anonymous" :
				//droit d'acc�s ou statut
				if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(2);
					$acces_j = $dom_1->getJoin(0,16,'notice_id');
					$query = "select notice_id from (".$query.") as uni ".$acces_j;
					if($this->parentNode && $this->parentNode->restricted_notices){
						$query.= " where uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
					}
				}else{
					$query = "select uni.notice_id from (".$query.") as uni join notices on notices.notice_id = uni.notice_id join notice_statut on notices.statut= id_notice_statut where explnum_visible_opac=1 and explnum_visible_opac_abon=0";
					if($this->parentNode && $this->parentNode->restricted_notices){
						$query.= " and uni.notice_id in (".$this->parentNode->restricted_notices.")"; 
					}
				}
				break;
			default ://On ne doit jamais passer dans ce cas l�
				$query="";
				break;
		}	
		$this->notices =array();
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$this->notices[] = $row->notice_id;
			}
		}else{//Si j'ai plus de notice dans cette branche il faut le garde en m�moire sinon dans la branche du dessous on repart avec toute les notices
			$this->notices[] = "'ensemble_vide'";
		}
		$this->restricted_notices = implode(",",$this->notices);
    }
    
    function filterExplnums($query){
    	global $gestion_acces_active,$gestion_acces_user_notice,$gestion_acces_empr_notice;
		global $webdav_current_user_id;
 		switch($this->config['authentication']){
			case "gestion" :
				$acces_j='';
				//soit les droits d'acc�s sont activ�s et il est possible que la notice ne soit pas visible pour certaines personnes
				//soit c'est la requete de base
				if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(1);
					$acces_j = $dom_1->getJoin($webdav_current_user_id,3,'notice_id');
					$query = "select distinct explnum_id, notice_id from (".$query.") as uni ".$acces_j;
				} 
				break;
			case "opac" :
				$acces_j='';
				//droit d'acc�s ou statut
				if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(2);
					$acces_j = $dom_1->getJoin($webdav_current_user_id,16,'notice_id');
					$query = "select distinct explnum_id, notice_id from (".$query.") as uni ".$acces_j;
				}else{
					$query = "select distinct explnum_id, uni.notice_id from (".$query.") as uni join notices on notices.notice_id = uni.notice_id join notice_statut on notices.statut= id_notice_statut where ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($webdav_current_user_id ?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
				}
				break;
			case "anonymous" :
				//droit d'acc�s ou statut
				if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
					$ac= new \acces();
					$dom_1= $ac->setDomain(2);
					$acces_j = $dom_1->getJoin(0,16,'notice_id');
					$query = "select distinct explnum_id, notice_id from (".$query.") as uni ".$acces_j;
				}else{
					$query = "select distinct explnum_id, uni.notice_id from (".$query.") as uni join notices on notices.notice_id = uni.notice_id join notice_statut on notices.statut= id_notice_statut where explnum_visible_opac=1 and explnum_visible_opac_abon=0";
				}
				break;
		}
		return $query;
    } 

    function getNotices(){
    	return array();
    }   
    
    function check_write_permission(){
    	global $webdav_current_user_id;
    	if($this->config['write_permission']){
    		$tab = array();
    		$query = "";
    		switch($this->config['authentication']){
    			case "gestion" :
    				$tab = $this->config['restricted_user_write_permission'];
    				$query = "select grp_num from users where userid = ".$webdav_current_user_id;
    				break;
    			case "opac" :
    				$query = "select empr_categ from empr where id_empr = ".$webdav_current_user_id;
    			case "anonymous" : 
    			default :
    				$tab = $this->config['restricted_empr_write_permission'];
    				break;
    		}
    		//pas de restriction, on est bon
    		if(!count($tab)){
    			return true;
    		}elseif($query != ""){
    			//on doit s'assurer que la personne connect�e est dispose des droits...
    			$result = mysql_query($query);
    			if(mysql_num_rows($result)){
    				if(in_array(mysql_result($result,0,0),$tab)){
    					return true;
    				}
    			}
    		} 
    	}
    	//si on est encore dans la fonction, c'est qu'on correspond � aucun crit�re !
    	return false;
    }
}