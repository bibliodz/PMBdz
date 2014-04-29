<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: print_docnum.inc.php,v 1.3 2013-01-30 16:26:51 ngantier Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
require_once ($class_path."/upload_folder.class.php");

//gestion des droits
require_once($class_path."/acces.class.php");
require_once($class_path."/notice_affichage.class.php");

switch($sub){
	case 'get_list':	
		if($number && $select_noti){
			$id_notices = explode(",",$select_noti);
		} else $id_notices=$_SESSION["cart"];	
		ajax_http_send_response( doc_num_get_list($id_notices) );
	
	break;
}
function doc_num_get_list($id_notices){
	global $msg,$dbh, $gestion_acces_active,$gestion_acces_empr_notice;
	$cpt_doc_num=0;
	foreach($id_notices as $notine_id){
		
		$req= "SELECT explnum_id from explnum where explnum_notice=$notine_id and explnum_mimetype= 'application/pdf' ";
		$req.= " union ";
		$req.= " select explnum_id from explnum ,bulletins where explnum_bulletin=bulletin_id and num_notice=$notine_id and explnum_mimetype= 'application/pdf'";
	//	$req.= " union ";
	//	$req.= " select explnum_id from explnum ,bulletins where explnum_bulletin=bulletin_id and bulletin_notice=$notine_id and explnum_mimetype= 'application/pdf'";
		$res = mysql_query($req);
		$nb_res = mysql_num_rows($res) ;
		if (!$nb_res)	continue;		
		// pour tout les pdf de la notice
		while($r = mysql_fetch_object($res)){
			$explnum_id=$r->explnum_id;
			
			$resultat = mysql_query("SELECT explnum_id, explnum_notice, explnum_bulletin, explnum_nom, explnum_mimetype, explnum_url, explnum_data, length(explnum_data) as taille,explnum_path, concat(repertoire_path,explnum_path,explnum_nomfichier) as path, repertoire_id FROM explnum left join upload_repertoire on repertoire_id=explnum_repertoire WHERE explnum_id = '$explnum_id' ", $dbh);
			$nb_res = mysql_num_rows($resultat) ;		
			$ligne = mysql_fetch_object($resultat);
					
			$id_for_rigths = $ligne->explnum_notice;
			if($ligne->explnum_bulletin != 0){
				//si bulletin, les droits sont rattachés à la notice du bulletin, à défaut du pério...
				$req = "select bulletin_notice,num_notice from bulletins where bulletin_id =".$ligne->explnum_bulletin;
				$res = mysql_query($req);
				if(mysql_num_rows($res)){
					$row = mysql_fetch_object($result);
					$id_for_rigths = $row->num_notice;
					if(!$id_for_rigths){
						$id_for_rigths = $row->bulletin_notice;
					}
				}$type = "" ;
			}
						
			//droits d'acces emprunteur/notice
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
				$ac= new acces();
				$dom_2= $ac->setDomain(2);
				$rights= $dom_2->getRights($_SESSION['id_empr_session'],$id_for_rigths);
			}
						
			//Accessibilité des documents numériques aux abonnés en opac
			$req_restriction_abo = "SELECT explnum_visible_opac, explnum_visible_opac_abon ,notice_id FROM notice_statut, explnum, notices WHERE explnum_notice=notice_id AND statut=id_notice_statut  AND explnum_id='$explnum_id' ";
			$result=mysql_query($req_restriction_abo,$dbh);
			if(! mysql_num_rows($result) ){// bulletin
				$req_restriction_abo="SELECT explnum_visible_opac, explnum_visible_opac_abon,notice_id
					FROM notice_statut, explnum, bulletins, notices
					WHERE explnum_bulletin = bulletin_id
					AND num_notice = notice_id
					AND statut = id_notice_statut
					AND explnum_id='$explnum_id' ";
				$result=mysql_query($req_restriction_abo,$dbh);
			}			
			$expl_num=mysql_fetch_array($result);
			
			if( $rights & 16 || (is_null($dom_2) && $expl_num["explnum_visible_opac"] && (!$expl_num["explnum_visible_opac_abon"] || ($expl_num["explnum_visible_opac_abon"] && $_SESSION["user_code"])))){
				if (($ligne->explnum_data)||($ligne->explnum_path)) {
					$notice = new notice_affichage($expl_num["notice_id"], $liens_opac) ;
					$notice->do_header_without_html();
					$tpl.="<input id='doc_num_list_".$explnum_id."' type='checkbox' name='doc_num_list[]' value='".$explnum_id."'> ".$notice->notice_header_without_html." : ".$ligne->explnum_nom."<br />";
					$cpt_doc_num++;					
				}
			}	
		}
	}
	if($cpt_doc_num){
		$tpl=" 		
		<br /><b>".$msg["print_output_docnum_list"]."</b>
		<input type='button' id='list_lecture_cart_checked_all' class='bouton' value=\"".$msg["list_docnum_checked_all"]."\" title=\"".$msg["list_docnum_checked_all"]."\" onClick=\"setCheckboxes('print_options', 'doc_num_list', true); return false;\" />		
		<input type='button' id='list_lecture_cart_checked_all' class='bouton' value=\"".$msg["list_docnum_unchecked_all"]."\" title=\"".$msg["list_docnum_unchecked_all"]."\" onClick=\"setCheckboxes('print_options', 'doc_num_list', false); return false;\" />
		<br />". $tpl;
	}else {
		$tpl="<b>".$msg["print_output_docnum_list_no_file"]."<br /></b>";
	}	
	return $tpl;
}
