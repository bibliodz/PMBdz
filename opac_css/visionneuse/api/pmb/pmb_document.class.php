<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb_document.class.php,v 1.1 2013-07-24 13:09:22 arenou Exp $

require_once($class_path."/cms/cms_document.class.php");


class pmb_document extends base_params implements params {
	var $listeDocs = array();		//tableau de documents
	var $current = 0;				//position courante dans le tableau
	var $currentDoc = "";			//tableau d�crivant le document courant
	var $params;					//tableau de param�tres utiles pour la recontructions des requetes...et m�me voir plus
	var $watermark = array();			//Url du watermark si d�fini  + transparence
  
    public function __construct($params,$visionneuse_path) {
    	global $opac_photo_mean_size_x,$opac_photo_mean_size_y;
    	$this->driver_name = "pmb_document";
    	$this->params = $params;
    	$this->params["maxX"] = $opac_photo_mean_size_x;
    	$this->params["maxY"] = $opac_photo_mean_size_y;
    	$this->visionneuse_path = $visionneuse_path;
    	if($this->params["lvl"] == "ajax") {
	    	$this->getDocById($this->params["explnum_id"]);
	    } else if($this->params["lvl"] != "afficheur"){
	    	$this->recupListDocNum();
	    } else{
	    	$this->getDocById($this->params["explnum"]);
	    }
    }
	
	
 	//renvoie un param
 	function getParam($parametre){
 		return $this->params[$parametre];
 	}
 	//renvoie le nombre de documents
 	function getNbDocs(){
 		return sizeof($this->listeDocs);
 	}
 	//renvoie un document pr�cis
 	function getDoc($numDoc){
 		if($numDoc >= 0 && $numDoc <= $this->getNbDocs()-1){
 			$this->current = $numDoc;
 			return $this->getCurrentDoc();
 		}else return false;
 	}
 	
	function getDocById($id){
		$this->current = 0;
		$this->listeDocs = array($id);
	}
 	
 	function recupListDocNum(){
 		if(!count($this->listeDocs)){
			$this->listeDocs = array();
	 		switch($this->params['type']){
	 			case "article" :
	 				$id_article = ($this->params['num_type']*1);
					$query = "select document_link_num_document from cms_documents_links where document_link_type_object = 'article' and document_link_num_object = '".$id_article."'";
					
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$i=0;
						while($row = mysql_fetch_object($result)){
							if($this->params['explnum'] == $row->document_link_num_document){
								$this->current = $i;
							}
							$this->listeDocs[] = $row->document_link_num_document+0;
							$i++;
						}
					}
	 				break;
	 		}
 		}
 		if(isset($this->params['position'])){
 			$this->current = $this->params['position'];
 		}
  	}
// 	
// 	//recup�re les documents num�riques associ�s
// 	function getExplnums($id=0){
//		global $dbh;
//		global $opac_photo_filtre_mimetype; //filtre des mimetypes
//		global $gestion_acces_active,$gestion_acces_empr_notice;
//		
//		if( sizeof($this->listeDocs) ==0 ){
//			$requete = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path from explnum ";
//			if($id !=0){
//				$id+=0;
//				$requete .= "where explnum_id = $id";
//				//if($opac_photo_filtre_mimetype) //Si on est ici c'est que la visionneuse est activ� alors on filtre les mimetypes (si il y en a pas on ne doit rien afficher)
//					$requete .= " and explnum_mimetype in ($opac_photo_filtre_mimetype)";
//				$res = mysql_query($requete,$dbh);
//				$this->listeDocs[] = mysql_fetch_object($res);
//				$this->current = 0;
//			}else {
//				if(sizeof($this->listeNotices) > 0 && sizeof($this->listeBulls) == 0){
//					$requete .= "where (explnum_notice in ('".implode("','",$this->listeNotices)."') and explnum_bulletin = 0 ) ";
//				}else if(sizeof($this->listeBulls) >0 && sizeof($this->listeNotices) == 0){
//					$requete .= "where (explnum_bulletin in ('".implode("','",$this->listeBulls)."') and explnum_notice = 0)";
//				}else {
//					$requete .= "where ((explnum_notice in ('".implode("','",$this->listeNotices)."') and explnum_bulletin = 0) or (explnum_bulletin in ('".implode("','",$this->listeBulls)."') and explnum_notice = 0))";
//				}
//				//if($opac_photo_filtre_mimetype) //Si on est ici c'est que la visionneuse est activ� alors on filtre les mimetypes (si il y en a pas on ne doit rien afficher)
//					$requete .= " and explnum_mimetype in ($opac_photo_filtre_mimetype)";
//				$res = mysql_query($requete,$dbh);
//				while(($expl = mysql_fetch_object($res))){
//					$this->listeDocs[] = $expl;
//				}
//			}
//			$this->checkCurrentExplnumId();
//		}
//	} 
//	
//	function checkCurrentExplnumId(){
//		if($this->params["explnum_id"] != 0 && $this->params["start"]){
//			for ($i=0;$i<sizeof($this->listeDocs);$i++){
//				if($this->params["explnum_id"] == $this->listeDocs[$i]->explnum_id){
//					$this->current = $i;
//					break;
//				}
//			}
//		}else $this->current = $this->params["position"];			
//	}
//	
	function getCurrentDoc(){
		$this->currentDoc = "";
		//on peut r�cup d�j� un certain nombre d'infos...
		$this->currentDoc["id"] = $this->listeDocs[$this->current];
		$document = new cms_document($this->currentDoc["id"]);
		$this->currentDoc["titre"] = $document->title ? $document->title : $document->filename; 
		$this->currentDoc["searchterms"] = $this->params["user_query"];
		$this->currentDoc["mimetype"] = $document->mimetype;
		//pour le moment, on s'emmerde pas aevc l'article...
		$this->currentDoc["desc"] = "";
		$this->currentDoc["path"] = $document->get_document_in_tmp();
		$this->currentDoc['extension'] = $ext=substr($document->filename,strrpos($document->filename,'.')*1+1);
		
		
		return $this->currentDoc;		
		$this->params["explnum_id"] = $this->listeDocs[$this->current]->explnum_id;
	}

/*******************************************************************
*  Renvoie le contenu du document brut et g�re le cache si besoin  *
******************************************************************/
	function openCurrentDoc(){
		global $dbh;
		return file_get_contents($this->currentDoc['path']);
	}
	
	function getMimetypeConf(){
		global $opac_visionneuse_params;
 		return unserialize(htmlspecialchars_decode($opac_visionneuse_params));
	}
	
	function getUrlBase(){
		global $opac_url_base;
		return $opac_url_base;
	}

	
	function getUrlImage($img){
		global $opac_url_base;
		if($img !== "")
			$img = $opac_url_base."images/".$img;
			
		return $img;
	}
	
	function getClassParam($class){
		$params = serialize(array());
		if($class != ""){
			$req="SELECT visionneuse_params_parameters FROM visionneuse_params WHERE visionneuse_params_class LIKE '$class'";
			if($res=mysql_query($req)){
				if(mysql_num_rows($res)){
					$result = mysql_fetch_object($res);
					$params = htmlspecialchars_decode($result->visionneuse_params_parameters);
				}
			}
		}
		return $params;
	}

	function forbidden_callback(){
		global $opac_show_links_invisible_docnums;
		
		$display ="";
		if(!$_SESSION['user_code'] && $opac_show_links_invisible_docnums){
			$auth_popup = new auth_popup();
			$display.= "
			<script type='text/javascript'>
				auth_popup('./ajax.php?module=ajax&categ=auth&callback_func=pmb_visionneuse_refresh');
				function pmb_visionneuse_refresh(){
					window.location.reload();
				}
			</script>";
		}
		return $display;
	} 
	
	function getBnfClass($mimetype){
		global $base_path,$class_path,$include_path;

		switch($mimetype){
			case "application/bnf" :
				require_once($class_path."/docbnf.class.php");
				$classname = "docbnf";
				break;
			case "application/bnf+zip" :
				require_once($class_path."/docbnf_zip.class.php");
				$classname = "docbnf_zip";
				break;
		}
		
		return $classname;
	}
	
	function getVisionneuseUrl($params){
		$url = "./visionneuse.php?driver=pmb_document";
		if($params){
			$url.= "&".$params;
		}
		return $url;
	}

	function getDocumentUrl($id){
		global $opac_url_base;
		return $opac_url_base."/ajax.php?module=cms&categ=document&action=render&id=".$id;
	}

	function getCurrentBiblioInfos(){
		global $msg;
		
// 		$current = $this->listeDocs[$this->current]->explnum_id;
// 		if(!isset($this->biblioInfos[$current])){
// 			$query = "select explnum_notice,explnum_bulletin from explnum where explnum_id = ".$current;
// 			$result = mysql_query($query);
// 			if(mysql_num_rows($result)){
// 				$row = mysql_fetch_object($result);
// 				if($row->explnum_notice){
// 					$query = "select notice_id, tit1, year from notices where notice_id = ".$row->explnum_notice;
// 					$result = mysql_query($query);
// 					if(mysql_num_rows($result)){
// 						$row = mysql_fetch_object($result);
// 						$this->biblioInfos[$current]['title']['value'] = $row->tit1;
// 						$this->biblioInfos[$current]['date']['value'] = $row->year;
// 						$this->biblioInfos[$current]['permalink']['value'] = "./index.php?lvl=notice_display&id=".$row->notice_id;
// 						$aut_query = "select responsability_author from responsability where responsability_notice = ".$row->notice_id." order by responsability_type asc, responsability_ordre asc limit 1";
// 					}
// 				}else{
// 					$query = "select bulletin_id, bulletin_titre,mention_date,date_date,notices.tit1,perio.tit1 as perio_title, notices.notice_id, perio.notice_id as serial_id from bulletins join notices as perio on bulletin_notice = perio.notice_id left join notices on num_notice = notices.notice_id where bulletin_id = ".$row->explnum_bulletin;					
// 					$result = mysql_query($query);
// 					if(mysql_num_rows($result)){
// 						$row = mysql_fetch_object($result);
// 						$titre = $row->tit1;
// 						if(!$titre) $titre = $row->bulletin_titre;
// 						$this->biblioInfos[$current]['title']['value'] = $row->perio_title.", ".$titre;
// 						$this->biblioInfos[$current]['date']['value'] = ($row->mention_date ? $row->mention_date : format_date($row->date_date));
// 						$this->biblioInfos[$current]['permalink']['value'] = "./index.php?lvl=bulletin_display&id=".$row->bulletin_id;
// 						$aut_query = "select responsability_author from responsability where responsability_notice = ".($row->notice_id ? $row->notice_id:$row->serial_id)." order by responsability_type asc, responsability_ordre asc limit 1";
// 					}
// 				}
// 				$result = mysql_query($aut_query);
// 				if(mysql_num_rows($result)){
// 					$author_id = mysql_result($result,0,0);
// 					$author= new auteur($author_id);
// 					$this->biblioInfos[$current]['author']['value'] =$author->isbd_entry;
// 				}
// 				$this->biblioInfos[$current]['title']['label'] = $msg['title'];
// 				$this->biblioInfos[$current]['date']['label'] = $msg['serialcirc_ask_date'];
// 				$this->biblioInfos[$current]['permalink']['label'] = $msg['location_more_info'];
// 				$this->biblioInfos[$current]['author']['label'] = $msg['author_search'];
// 			}
			
// 		}
		return $this->biblioInfos[$current];
	}
}
?>