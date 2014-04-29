<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb.class.php,v 1.34 2013-07-24 13:09:22 arenou Exp $
require_once("$include_path/notice_affichage.inc.php");
require_once("$include_path/bulletin_affichage.inc.php");
require_once("$class_path/upload_folder.class.php");
require_once("$class_path/search.class.php");
require_once("$class_path/searcher.class.php");
require_once($class_path."/auth_popup.class.php");

class pmb extends base_params implements params {
	var $listeDocs = array();		//tableau de documents
	var $current = 0;				//position courante dans le tableau
	var $currentDoc = "";			//tableau d�crivant le document courant
	var $params;					//tableau de param�tres utiles pour la recontructions des requetes...et m�me voir plus
	var $listeBulls = array();
	var $listeNotices = array();
	var $watermark = array();			//Url du watermark si d�fini  + transparence
	var $bibliInfos = array();
	
    function pmb($params,$visionneuse_path) {
    	global $opac_photo_mean_size_x,$opac_photo_mean_size_y;
    	$this->params = $params;
    	
    	$this->driver_name = "pmb";
    	$this->params["maxX"] = $opac_photo_mean_size_x;
    	$this->params["maxY"] = $opac_photo_mean_size_y;
    	$this->visionneuse_path = $visionneuse_path;
    	if($this->params["lvl"] != "afficheur")
	    	$this->recupListDocNum();
	    if($this->params["lvl"] != "afficheur" && $this->params["explnum"] !== 0)
	    	$this->getDocById($this->params["explnum"]);
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
		$this->getExplnums($id);
 	}
 	
 	function recupListDocNum(){
 		global $dbh;
 		global $opac_indexation_docnum_allfields;
 		global $gestion_acces_active,$gestion_acces_empr_notice;
 		global $opac_photo_filtre_mimetype; //filtre des mimetypes;
 		global $opac_nb_max_tri;
 		
 		//droits d'acces emprunteur/notice
		$acces_j='';
		if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1){
			$ac= new acces();
			$dom_2= $ac->setDomain(2);
			$acces_j = $dom_2->getJoin($_SESSION['id_empr_session'],4,'notice_id');
		}
		if($acces_j){
			$statut_j='';
			$statut_r='';
		}else{
			$statut_j=',notice_statut';
			$statut_r="and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
		}	

     	//on reconstruit la requete...
     	$this->listeBulls =array();
     	$this->listeNotices =array();
		$requete_noti = "";
		$requete_bull = "";
		$requete_explnum = "";
	 	$searcher = false;
	 	
		switch($this->params['mode']){
			//nouvelle m�thode...
			case "title" :
				$searcher = new searcher_title(stripslashes($this->params['user_query']));
				break;
			case "tous" :
				$searcher = new searcher_all_fields(stripslashes($this->params['user_query']));
				break;
			case "keyword" :
				$searcher = new searcher_keywords(stripslashes($this->params['user_query']));
				break;
			case "extended" :
				$searcher = new searcher_extended(stripslashes($this->params['search']));
				break;
			case "abstract" :
				$searcher = new searcher_abstract(stripslashes($this->params['user_query']));
				break;
			//autorit�s , pas optimal, faudra y revenir...
			case "author_see":
				//recup des auteurs associ�s...
				$rqt_auteurs = "select author_id as aut from authors where author_see='".$this->params["idautorite"]."' and author_id!=0 ";
				$rqt_auteurs .= "union select author_see as aut from authors where author_id='".$this->params["idautorite"]."' and author_see!=0 " ;
				$res_auteurs = mysql_query($rqt_auteurs, $dbh);
				$clause_auteurs = "responsability_author in('".$this->params["idautorite"]."' ";
				while(($id_aut=mysql_fetch_object($res_auteurs))) {
					$clause_auteurs .= ", '".$id_aut->aut."' ";
					$rqt_auteursuite = "select author_id as aut from authors where author_see='$id_aut->aut' and author_id!=0 ";
					$res_auteursuite = mysql_query($rqt_auteursuite, $dbh);
					while(($id_autsuite=mysql_fetch_object($res_auteursuite))) $clause_auteurs .= ", '".$id_autsuite->aut."' "; 
				} 
				$clause_auteurs .= ")";					
					
				// on lance la vraie requ�te
				$requete_noti = "SELECT distinct notices.notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j, responsability $statut_j ";
				$requete_noti.= "where $clause_auteurs and notice_id=responsability_notice $statut_r ";
				$requete_noti.= "ORDER BY index_serie,tnvol,index_sew";				
				break;
			case "congres_see" : 
				//on r�cup les auteurs associ�s
				$rqt_auteurs = "select author_id as aut from authors where author_see='".$this->params["idautorite"]."' and author_id!=0 ";
				$rqt_auteurs .= "union select author_see as aut from authors where author_id='".$this->params["idautorite"]."' and author_see!=0 " ;
				$res_auteurs = mysql_query($rqt_auteurs, $dbh);
				$clause_auteurs = "responsability_author in('".$this->params["idautorite"]."' ";
				while(($id_aut=mysql_fetch_object($res_auteurs))) {
					$clause_auteurs .= ", '".$id_aut->aut."' ";
					$rqt_auteursuite = "select author_id as aut from authors where author_see='$id_aut->aut' and author_id!=0 ";
					$res_auteursuite = mysql_query($rqt_auteursuite, $dbh);
					while(($id_autsuite=mysql_fetch_object($res_auteursuite))) $clause_auteurs .= ", '".$id_autsuite->aut."' "; 
				} 
				$clause_auteurs .= ")" ;
				
				//on peut lancer la vrai requete maintenant...					
				$requete_noti = "SELECT distinct notices.notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j, responsability $statut_j ";
				$requete_noti.= "where $clause_auteurs and notice_id=responsability_notice $statut_r ";
				$requete_noti.= "ORDER BY index_serie,tnvol,index_sew";
			
				break;
			case "categ_see":
				global $opac_auto_postage_nb_descendant,$opac_auto_postage_nb_montant;
				global $opac_auto_postage_descendant,$opac_auto_postage_montant,$opac_auto_postage_etendre_recherche;
				global $opac_categories_categ_sort_records;
				
				//auto-postage...
				$nb_level_descendant=$opac_auto_postage_nb_descendant;
				$nb_level_montant=$opac_auto_postage_nb_montant;

				$q = "select path from noeuds where id_noeud = '".$this->params["idautorite"]."' ";
				$r = mysql_query($q, $dbh);
				$path=mysql_result($r, 0, 0);
				$nb_pere=substr_count($path,'/');
				
				// Si un path est renseign� et le param�trage activ�			
				if ($path && ($opac_auto_postage_descendant || $opac_auto_postage_montant || $opac_auto_postage_etendre_recherche) && ($nb_level_montant || $nb_level_descendant)){
					
					//Recherche des fils 
					if(($opac_auto_postage_descendant || $opac_auto_postage_etendre_recherche)&& $nb_level_descendant) {
						if($nb_level_descendant != '*' && is_numeric($nb_level_descendant))
							$liste_fils=" path regexp '^$path(\\/[0-9]*){0,$nb_level_descendant}$' ";
						else 
							//$liste_fils=" path regexp '^$path(\\/[0-9]*)*' ";
							$liste_fils=" path like '$path/%' or  path = '$path' ";
					} else {
						$liste_fils=" id_noeud='".$this->params["idautorite"]."' ";
					}
							
					// recherche des p�res
					if(($opac_auto_postage_montant || $opac_auto_postage_etendre_recherche) && $nb_level_montant ) {
						
						$id_list_pere=explode('/',$path);	
						$stop_pere=0;
						if($nb_level_montant != '*' && is_numeric($nb_level_montant)) $stop_pere=$nb_pere-$nb_level_montant;
						if($stop_pere<0) $stop_pere=0;
						for($i=$nb_pere;$i>=$stop_pere; $i--) {
							$liste_pere.= " or id_noeud='".$id_list_pere[$i]."' ";
						}
					}			
					$suite_req = " FROM noeuds join notices_categories on id_noeud=num_noeud join notices on notcateg_notice=notice_id  $acces_j $statut_j ";
					$suite_req.= "WHERE ($liste_fils $liste_pere) $statut_r ";
					
				} else {	
					// cas normal d'avant		
					//$suite_req=" FROM notices_categories, notices, notice_statut WHERE (notices_categories.num_noeud = '".$id."' and notices_categories.notcateg_notice = notices.notice_id) and (notices.statut = notice_statut.id_notice_statut and ((notice_statut.notice_visible_opac = 1 and notice_statut.notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_statut.notice_visible_opac_abon=1 and notice_statut.notice_visible_opac = 1)":"").")) ";
					$suite_req = " FROM notices_categories join notices on notcateg_notice=notice_id $acces_j $statut_j ";
					$suite_req.= "WHERE num_noeud=".$this->params["idautorite"]." $statut_r ";
				}
				//on a ce qu'il nous faut, on peut lancer la recherche...
				$requete_noti ="SELECT distinct notices.notice_id, notices.niveau_biblio, notices.niveau_hierar $suite_req ORDER BY $opac_categories_categ_sort_records";
				break;
			case "indexint_see":
				$requete_noti = "SELECT notice_id,niveau_biblio,niveau_hierar FROM notices, notice_statut WHERE indexint='".$this->params["idautorite"]."' and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (notice_visible_opac_abon=1 and notice_visible_opac=1)" : "").")" ;
				break;
			case "coll_see":
				$requete_noti= "SELECT notices.notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j $statut_j WHERE coll_id='".$this->params["idautorite"]."' $statut_r ";
				break;
			case "publisher_see":
				$requete_noti  = "SELECT notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j $statut_j WHERE (ed1_id='".$this->params["idautorite"]."' or ed2_id='".$this->params["idautorite"]."') $statut_r "; 
				break;
			case "titre_uniforme_see" : 
				$requete_noti = "SELECT notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j $statut_j ,notices_titres_uniformes ";
				$requete_noti.= "WHERE ntu_num_notice=notice_id and ntu_num_tu='".$this->params["idautorite"]."' $statut_r ";
				break;
			case "serie_see":
				$requete_noti  = "SELECT distinct notice_id,niveau_biblio,niveau_hierar FROM notices, notice_statut WHERE tparent_id='".$this->params["idautorite"]."' and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (notice_visible_opac_abon=1 and notice_visible_opac=1)" : "").")" ;
				break;
			case "subcoll_see":
				$requete_noti = "SELECT notice_id,niveau_biblio,niveau_hierar FROM notices $acces_j $statut_j WHERE subcoll_id='".$this->params["idautorite"]."'  $statut_r ";
				break;
			case "perio_bulletin":
				//TODO : droits sur les bulletins et d�pouillements
				$requete_bull = "SELECT bulletin_id FROM bulletins WHERE bulletin_notice='".$this->params["idperio"]."'";
				//on r�cup�re aussi les articles associ�s aux bulletins
				$requete_noti ="select analysis_notice as notice_id from analysis join bulletins on analysis_bulletin = bulletin_id AND bulletin_notice='".$this->params["idperio"]."'";
				break;
			case "docnum":
				//cas assez particulier, on va pas rechercher toutes les notices et bulletins pour retrouver les explnum le tout en partant des explnums....
				$requete1 = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path, notice_id, ".stripslashes($this->params["pert"])." from explnum, notices $statut_j $acces_j ".stripslashes($this->params["clause"])." ";  
				$requete2 = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path, notice_id, ".stripslashes($this->params["pert"])." from bulletins, explnum, notices $statut_j $acces_j ".stripslashes($this->params["clause_bull"])." ";
				$requete3 = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path, notice_id, ".stripslashes($this->params["pert"])." from bulletins, explnum, notices $statut_j $acces_j ".stripslashes($this->params["clause_bull_num_notice"])." ";
				$requete_explnum = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path from ($requete1 UNION $requete2 UNION $requete3) as uni join notices n on uni.notice_id=n.notice_id  ".stripslashes($this->params["tri"]); 
				break; 
			default :
				//on ne peut avoir que l'id de l'exemplaire
				$requete_noti = "select explnum_notice as notice_id from explnum where explnum_notice != 0 and explnum_id = ".$this->params["explnum_id"];
				$requete_bull = "select explnum_bulletin as bulletin_id from explnum where explnum_bulletin != 0 and explnum_id = ".$this->params["explnum_id"];
				break;	
											
		}
		
		//avec le nouveau mode de recherche on utilise un nouveau mode de visionneuse...
		if(is_object($searcher)){
			if(isset($_SESSION["last_sortnotices"]) && $_SESSION["last_sortnotices"]!==""){
				$explnums = $searcher->get_explnums($_SESSION["last_sortnotices"]);
			}else{
				$explnums = $searcher->get_explnums("default");
			}
			$this->listeDocs = array();
			if(count($explnums)){
				for ($i=0 ; $i<count($explnums) ; $i++){
					$rqt = " select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path from explnum where explnum_id =".$explnums[$i]." and explnum_mimetype in (".$opac_photo_filtre_mimetype.")";
					$res = mysql_query($rqt);
					if(mysql_num_rows($res)){
						while($row = mysql_fetch_object($res)){
							$this->listeDocs[] = $row;
						}
					}
				}
			}
			$this->checkCurrentExplnumId();
		}else{
			if ($requete_explnum != ""){
				$res_explnum = mysql_query($requete_explnum,$dbh);
				$allowed_mimetype = explode(",",str_replace("'","",$opac_photo_filtre_mimetype));
				while(($expl = mysql_fetch_object($res_explnum))){
					if($expl->explnum_mimetype && (in_array($expl->explnum_mimetype,$allowed_mimetype))){//Si le mimetype du document n'est pas connu il ne peut pas �tre affich� dans la visionneuse.
						$this->listeDocs[] = $expl;
					}
				}
				$this->current = 0;
				$this->checkCurrentExplnumId();		
			}else{
				if($requete_noti){
					$res_notice = mysql_query($requete_noti,$dbh);
					if(mysql_num_rows($res_notice)){
						while(($not_ids = mysql_fetch_object($res_notice))){
							//cas d'une notice de bulletin, le docnum peut etre rattach� au bulletin
							//donc on va le chercher et le rajoute � la liste...
							if($not_ids->niveau_biblio == "b" && $not_ids->niveau_hierar == "2"){
								$req = "select bulletin_id from bulletins where num_notice = ".$not_ids->notice_id." LIMIT 1";
								$res_notibull = mysql_query($req);
								if(mysql_num_rows($res_notibull))
									$this->listeBulls[] = mysql_result($res_notibull,0,0);
							}else{
								$this->listeNotices[] = $not_ids->notice_id;
							}
						}
					}
				}
			
				if($requete_bull){
					$res_bull = mysql_query($requete_bull,$dbh);
					if(mysql_num_rows($res_bull)){
						while(($bull_ids = mysql_fetch_object($res_bull))){
							$this->listeBulls[]= $bull_ids->bulletin_id;
						}
					}
				}
				
				if($this->listeNotices || $this->listeBulls)
					$this->getExplnums();	
			}
 		}
  	}
 	
 	//recup�re les documents num�riques associ�s
 	function getExplnums($id=0){
		global $dbh;
		global $opac_photo_filtre_mimetype; //filtre des mimetypes
		global $gestion_acces_active,$gestion_acces_empr_notice;
		
		if( sizeof($this->listeDocs) ==0 ){
			$requete = "select explnum_id,explnum_notice,explnum_bulletin,explnum_nom,explnum_mimetype,explnum_url,explnum_extfichier,explnum_nomfichier,explnum_repertoire,explnum_path from explnum ";
			if($id !=0){
				$id+=0;
				$requete .= "where explnum_id = $id";
				//if($opac_photo_filtre_mimetype) //Si on est ici c'est que la visionneuse est activ� alors on filtre les mimetypes (si il y en a pas on ne doit rien afficher)
					$requete .= " and explnum_mimetype in ($opac_photo_filtre_mimetype)";
				$res = mysql_query($requete,$dbh);
				$this->listeDocs[] = mysql_fetch_object($res);
				$this->current = 0;
			}else {
				if(sizeof($this->listeNotices) > 0 && sizeof($this->listeBulls) == 0){
					$requete .= "where (explnum_notice in ('".implode("','",$this->listeNotices)."') and explnum_bulletin = 0 ) ";
				}else if(sizeof($this->listeBulls) >0 && sizeof($this->listeNotices) == 0){
					$requete .= "where (explnum_bulletin in ('".implode("','",$this->listeBulls)."') and explnum_notice = 0)";
				}else {
					$requete .= "where ((explnum_notice in ('".implode("','",$this->listeNotices)."') and explnum_bulletin = 0) or (explnum_bulletin in ('".implode("','",$this->listeBulls)."') and explnum_notice = 0))";
				}
				//if($opac_photo_filtre_mimetype) //Si on est ici c'est que la visionneuse est activ� alors on filtre les mimetypes (si il y en a pas on ne doit rien afficher)
					$requete .= " and explnum_mimetype in ($opac_photo_filtre_mimetype)";
				$res = mysql_query($requete,$dbh);
				while(($expl = mysql_fetch_object($res))){
					$this->listeDocs[] = $expl;
				}
			}
			$this->checkCurrentExplnumId();
		}
	} 
	
	function checkCurrentExplnumId(){
		if($this->params["explnum_id"] != 0 && $this->params["start"]){
			for ($i=0;$i<sizeof($this->listeDocs);$i++){
				if($this->params["explnum_id"] == $this->listeDocs[$i]->explnum_id){
					$this->current = $i;
					break;
				}
			}
		}else $this->current = $this->params["position"];			
	}
	
	function getCurrentDoc(){
		$this->currentDoc = "";
		//on peut r�cup d�j� un certain nombre d'infos...
		$this->currentDoc["id"] = $this->listeDocs[$this->current]->explnum_id;
		$this->params["explnum_id"] = $this->listeDocs[$this->current]->explnum_id;
		$this->currentDoc["titre"] = $this->listeDocs[$this->current]->explnum_nom;
		$req_expl = "select explnum_id from explnum ";
		$req_expl.= "where explnum_id = ".$this->listeDocs[$this->current]->explnum_id." and ";
		$terms = explode(" ",$this->params["user_query"]);
		if(sizeof($terms>0)) $req_expl.="(";
		$search = '';
		for ($i=0 ; $i<sizeof($terms) ; $i++){
			if( $search != "") $search .= " or ";
			$search .= "explnum_index_sew LIKE '%".$terms[$i]."%'";
		}
		if( $search != "") $req_expl .= $search;
		if(sizeof($terms>0)) $req_expl.=")";
		$searchInExplnum = mysql_query($req_expl);
		if(mysql_num_rows($searchInExplnum)==0){
			$this->currentDoc["searchterms"]  ="";
		}else{
			$this->currentDoc["searchterms"]  =$this->params["user_query"];
		}
				
		//on r�cup�re le chemin
		if($this->listeDocs[$this->current]->explnum_url != ""){
			//c'est une url
			$this->currentDoc["path"] = $this->listeDocs[$this->current]->explnum_url ;
		}elseif($this->listeDocs[$this->current]->explnum_repertoire != 0){
			//il est en r�pertoire d'upload
			$rep = new upload_folder($this->listeDocs[$this->current]->explnum_repertoire);
			$this->currentDoc["path"] = $rep->repertoire_path."/".$this->listeDocs[$this->current]->explnum_nomfichier;	
		}else{
			//il est en base
			//faudra revoir ce truc
			$this->currentDoc["path"] = "";
		}

		//dans le cadre d'une URL, on doit r�cup le mimetype...
		if ($this->listeDocs[$this->current]->explnum_url){
			if (file_exists($this->visionneuse_path."/conf/mime_magic.txt")){
				$fp = fopen($this->visionneuse_path."/conf/mime_magic.txt","r");
				$mimetype_file=fread($fp,filesize($this->visionneuse_path."/conf/mime_magic.txt"));
				fclose($fp);
				if ($mimetype_file == "") $mimetype_file=get_cfg_var("mime_magic.magicfile");
			}else $mimetype_file= get_cfg_var("mime_magic.magicfile");	
			$finfo = finfo_open(FILEINFO_MIME,$mimetype_file); 
			$mime_magic=finfo_file($finfo, $this->listeDocs[$this->current]->explnum_url);
			$this->currentDoc["mimetype"] =(strpos($mime_magic,";") >0 ? substr($mime_magic,0,strpos($mime_magic,";")) : $mime_magic);		
		}else{
		//sinon il a d�j� �t� d�tect� et est pr�sent en base...	
			$this->currentDoc["mimetype"] =$this->listeDocs[$this->current]->explnum_mimetype;				
		}
		
		//pour la conversion on y ajoute l'extension
		$ext='';
		$ext=$this->listeDocs[$this->current]->explnum_extfichier;
		if (!$ext && $this->listeDocs[$this->current]->explnum_nomfichier) $ext=substr($this->listeDocs[$this->current]->explnum_nomfichier,strrpos($this->listeDocs[$this->current]->explnum_nomfichier,'.')*1+1);
		if (!$ext && $this->listeDocs[$this->current]->explnum_url) $ext=substr($this->listeDocs[$this->current]->explnum_url,strrpos($this->listeDocs[$this->current]->explnum_url,'.')*1+1);
		$this->currentDoc['extension'] = $ext;
		
		//on r�cup la notice associ�e...
		if($this->listeDocs[$this->current]->explnum_notice)
			$this->currentDoc["desc"]=aff_notice($this->listeDocs[$this->current]->explnum_notice,1,1,0,"",0,1);
		else $this->currentDoc["desc"]=bulletin_affichage($this->listeDocs[$this->current]->explnum_bulletin,"visionneuse");
		
		preg_match_all("/(<a href=[\"'][^#][^>]*>)(.*?)<\/a>/",$this->currentDoc["desc"],$lop);
		for ($i = 0 ; $i <sizeof($lop[0]) ; $i++){
			$plop = explode ($lop[0][$i],$this->currentDoc["desc"]);
			$this->currentDoc["desc"] = implode($lop[2][$i],$plop); 
		}	
		return $this->currentDoc;
	}
	
	function getCurrentBiblioInfos(){
		global $msg;
		
		$current = $this->listeDocs[$this->current]->explnum_id;
		if(!isset($this->biblioInfos[$current])){
			$query = "select explnum_notice,explnum_bulletin from explnum where explnum_id = ".$current;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				if($row->explnum_notice){
					$query = "select notice_id, tit1, year from notices where notice_id = ".$row->explnum_notice;
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$row = mysql_fetch_object($result);
						$this->biblioInfos[$current]['title']['value'] = $row->tit1;
						$this->biblioInfos[$current]['date']['value'] = $row->year;
						$this->biblioInfos[$current]['permalink']['value'] = "./index.php?lvl=notice_display&id=".$row->notice_id;
						$aut_query = "select responsability_author from responsability where responsability_notice = ".$row->notice_id." order by responsability_type asc, responsability_ordre asc limit 1";
					}
				}else{
					$query = "select bulletin_id, bulletin_titre,mention_date,date_date,notices.tit1,perio.tit1 as perio_title, notices.notice_id, perio.notice_id as serial_id from bulletins join notices as perio on bulletin_notice = perio.notice_id left join notices on num_notice = notices.notice_id where bulletin_id = ".$row->explnum_bulletin;					
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$row = mysql_fetch_object($result);
						$titre = $row->tit1;
						if(!$titre) $titre = $row->bulletin_titre;
						$this->biblioInfos[$current]['title']['value'] = $row->perio_title.", ".$titre;
						$this->biblioInfos[$current]['date']['value'] = ($row->mention_date ? $row->mention_date : format_date($row->date_date));
						$this->biblioInfos[$current]['permalink']['value'] = "./index.php?lvl=bulletin_display&id=".$row->bulletin_id;
						$aut_query = "select responsability_author from responsability where responsability_notice = ".($row->notice_id ? $row->notice_id:$row->serial_id)." order by responsability_type asc, responsability_ordre asc limit 1";
					}
				}
				$result = mysql_query($aut_query);
				if(mysql_num_rows($result)){
					$author_id = mysql_result($result,0,0);
					$author= new auteur($author_id);
					$this->biblioInfos[$current]['author']['value'] =$author->isbd_entry;
				}
				$this->biblioInfos[$current]['title']['label'] = $msg['title'];
				$this->biblioInfos[$current]['date']['label'] = $msg['serialcirc_ask_date'];
				$this->biblioInfos[$current]['permalink']['label'] = $msg['location_more_info'];
				$this->biblioInfos[$current]['author']['label'] = $msg['author_search'];
			}
			
		}
		return $this->biblioInfos[$current];
	}

/*******************************************************************
 *  Renvoie le contenu du document brut et g�re le cache si besoin  *
 ******************************************************************/
	function openCurrentDoc(){
		global $dbh;
				
		//s'il est en cache, c'est vachement simple
		if($this->isInCache($this->listeDocs[$this->current]->explnum_id)){
			$document = $this->readInCache($this->listeDocs[$this->current]->explnum_id);
		//sinon on va devoir regarder un peu ou ca se passe...
		}elseif($this->listeDocs[$this->current]->explnum_url != ""){
			//on est sur une URL
			$document = file_get_contents($this->listeDocs[$this->current]->explnum_url);	
			//on met les documents issues d'une URL en cache, ca �vite les probl�mes de connexion plus tard...
			$this->setInCache($this->listeDocs[$this->current]->explnum_id,$document);
		}elseif($this->listeDocs[$this->current]->explnum_repertoire != 0){
			//le document est stock� dans un r�pertoire d'upload
			$rep = new upload_folder($this->listeDocs[$this->current]->explnum_repertoire);
			$filepath = $rep->encoder_chaine(str_replace("//","/",$rep->repertoire_path.$this->listeDocs[$this->current]->explnum_path."/".$this->listeDocs[$this->current]->explnum_nomfichier));
			$document = file_get_contents($filepath);	
		}else{
			$requete ="SELECT explnum_data FROM explnum WHERE explnum_id = ".$this->listeDocs[$this->current]->explnum_id;
			$res = mysql_query($requete,$dbh);
			if(mysql_num_rows($res))
				$document = mysql_result($res,0,0);			
		}
		//on renvoie le contenu du document
		return $document;
	}
	
	function getMimetypeConf(){
		global $opac_visionneuse_params;
 		return unserialize(htmlspecialchars_decode($opac_visionneuse_params));
	}
	
	function getUrlImage($img){
		global $opac_url_base;
	
		if($img !== "")
			$img = $opac_url_base."images/".$img;
			
		return $img;
	}
	
	function getUrlBase(){
		global $opac_url_base;
		return $opac_url_base;
	}
	
	function getClassParam($class){
		$params = array();
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
	
	function is_allowed($explnum_id){
		$docnum_visible = true;
		global $gestion_acces_active,$gestion_acces_empr_notice;
		
		$query = "select explnum_notice,explnum_bulletin from explnum where explnum_id = ".$explnum_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$infos = mysql_fetch_object($result);
			if($infos->explnum_notice != 0){
				$id_for_right = $infos->explnum_notice;
			}else {
				$query = "select num_notice,bulletin_notice from bulletins where bulletin_id = ".$infos->explnum_bulletin;
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$infos = mysql_fetch_object($result);
					if($infos->num_notice){
						$id_for_right = $infos->num_notice;
					}else{
						$id_for_right = $infos->bulletin_notice;	
					}
				}
			}
			
		}

		if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
			$ac= new acces();
			$dom_2= $ac->setDomain(2);
			$docnum_visible = $dom_2->getRights($_SESSION['id_empr_session'],$id_for_right,16);
		} else {
			$requete = "SELECT explnum_visible_opac, explnum_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$id_for_right."' and id_notice_statut=statut ";
			$myQuery = mysql_query($requete);
			if(mysql_num_rows($myQuery)) {
				$statut_temp = mysql_fetch_object($myQuery);
				if(!$statut_temp->explnum_visible_opac){
					$docnum_visible=false;
				}
				if($statut_temp->explnum_visible_opac_abon && !$_SESSION['id_empr_session']){
					$docnum_visible=false;
				}
			} else {
				$docnum_visible=false;
			}
		}
		return $docnum_visible;
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
		global $base_path;
	
		$url = $base_path."/visionneuse.php";
		if($params){
			$url.= "?".$params;
		}
		return $url;
	}
	
	function getDocumentUrl($id){
		global $opac_url_base;
		return $opac_url_base."doc_num_data.php?explnum_id=".$id;
	}
}
?>