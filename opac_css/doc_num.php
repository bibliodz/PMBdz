<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: doc_num.php,v 1.37 2014-01-23 10:35:01 gueluneau Exp $

$base_path=".";
require_once($base_path."/includes/init.inc.php");

require_once($base_path."/includes/error_report.inc.php") ;

//Sessions !! Attention, ce doit être impérativement le premer include (à cause des cookies)
require_once($base_path."/includes/session.inc.php");
require_once($base_path."/includes/global_vars.inc.php");
require_once('./includes/opac_config.inc.php');


if ($css=="") $css=1;
	
// récupération paramètres MySQL et connection á la base
require_once('./includes/opac_db_param.inc.php');
require_once('./includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

require_once('./includes/start.inc.php');

require_once("./includes/check_session_time.inc.php");

// récupération localisation
require_once('./includes/localisation.inc.php');

// version actuelle de l'opac
require_once('./includes/opac_version.inc.php');

require_once ("./includes/explnum.inc.php");  

require_once ($class_path."/upload_folder.class.php"); 

//gestion des droits
require_once($class_path."/acces.class.php");

//si paramétrage authentification particulière et pour la re-authentification ntlm
if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');


/**
 * Récupère les infos du document numérique
 */
function recup_explnum_infos($id_explnum){
	
	global $infos_explnum;
	
	$rqt_explnum = "SELECT explnum_notice, explnum_bulletin, IF(location_libelle IS null, '', location_libelle) AS location_libelle, explnum_nom, explnum_mimetype, explnum_url, explnum_extfichier, IF(explnum_nomfichier IS null, '', explnum_nomfichier) AS nomfichier, explnum_path, IF(rep.repertoire_nom IS null, '', rep.repertoire_nom) AS nomrepertoire
		from explnum ex_n
		LEFT JOIN explnum_location ex_l ON ex_n.explnum_id= ex_l.num_explnum
		LEFT JOIN docs_location dl ON ex_l.num_location= dl.idlocation
		LEFT JOIN upload_repertoire rep ON ex_n.explnum_repertoire= rep.repertoire_id
		where explnum_id='".$id_explnum."'";
	$res_explnum=mysql_query($rqt_explnum);
	while(($explnum = mysql_fetch_array($res_explnum,MYSQL_ASSOC))){
		$infos_explnum[]=$explnum;
	}
}

$requete = "SELECT explnum_id, explnum_notice, explnum_bulletin, explnum_nom, explnum_nomfichier, explnum_mimetype, explnum_url, 
			explnum_data, explnum_extfichier, explnum_path, concat(repertoire_path,explnum_path,explnum_nomfichier) as path, repertoire_id
			FROM explnum left join upload_repertoire on repertoire_id=explnum_repertoire WHERE explnum_id = '$explnum_id' ";
$resultat = mysql_query($requete,$dbh);
$nb_res = mysql_num_rows($resultat) ;


if (!$nb_res) {
	header("Location: images/mimetype/unknown.gif");
	exit ;
} 
	
$ligne = mysql_fetch_object($resultat);

$id_for_rigths = $ligne->explnum_notice;
if($ligne->explnum_bulletin != 0){
	//si bulletin, les droits sont rattachés à la notice du bulletin, à défaut du pério...
	$req = "select bulletin_notice,num_notice from bulletins where bulletin_id =".$ligne->explnum_bulletin;
	$res = mysql_query($req);
	if(mysql_num_rows($res)){
		$row = mysql_fetch_object($res);
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
$req_restriction_abo = "SELECT explnum_visible_opac, explnum_visible_opac_abon FROM notices,notice_statut WHERE notice_id='".$id_for_rigths."' AND statut=id_notice_statut ";

$result=mysql_query($req_restriction_abo,$dbh);
$expl_num=mysql_fetch_array($result,MYSQL_ASSOC);

if($pmb_logs_activate){
	//Récupération des informations du document numérique
	recup_explnum_infos($explnum_id);
	//Enregistrement du log
	global $log, $infos_explnum;
			
	$rqt= " select empr_prof,empr_cp, empr_ville as ville, empr_year, empr_sexe, empr_login,  empr_date_adhesion, empr_date_expiration, count(pret_idexpl) as nbprets, count(resa.id_resa) as nbresa, code.libelle as codestat, es.statut_libelle as statut, categ.libelle as categ, gr.libelle_groupe as groupe,dl.location_libelle as location 
			from empr e
			left join empr_codestat code on code.idcode=e.empr_codestat
			left join empr_statut es on e.empr_statut=es.idstatut
			left join empr_categ categ on categ.id_categ_empr=e.empr_categ
			left join empr_groupe eg on eg.empr_id=e.id_empr
			left join groupe gr on eg.groupe_id=gr.id_groupe
			left join docs_location dl on e.empr_location=dl.idlocation
			left join resa on e.id_empr=resa_idempr
			left join pret on e.id_empr=pret_idempr
			where e.empr_login='".addslashes($_SESSION['user_code'])."'
			group by resa_idempr, pret_idempr";	
	$res=mysql_query($rqt);
	if($res){
		$empr_carac = mysql_fetch_array($res);
		$log->add_log('empr',$empr_carac);
	}

	$log->add_log('num_session',session_id());
	$log->add_log('explnum',$infos_explnum);
	$infos_restriction_abo = array();
	foreach ($expl_num as $key=>$value) {
		$infos_restriction_abo[$key] = $value;
	}
	$log->add_log('restriction_abo',$infos_restriction_abo);

	$log->save();
}
		
if( $rights & 16 || (is_null($dom_2) && $expl_num["explnum_visible_opac"] && (!$expl_num["explnum_visible_opac_abon"] || ($expl_num["explnum_visible_opac_abon"] && $_SESSION["user_code"])))){
	if (($ligne->explnum_data)||($ligne->explnum_path)) {

		if ($ligne->explnum_path) {
			$up = new upload_folder($ligne->repertoire_id);
			$path = str_replace("//","/",$ligne->path);
			$path=$up->encoder_chaine($path);
			if(file_exists($path) && filesize($path)){
				$fo = fopen($path,'rb');
			}else{
				$fo=false;
				header("Location: images/mimetype/unknown.gif");
				exit ;
			}
			if ($fo && (substr($ligne->explnum_mimetype,0,5)=="image")&&($opac_photo_watermark)) {
				$ligne->explnum_data=fread($fo,filesize($path));
				fclose($fo);
			} else $ligne->explnum_data="";
		}
		
		create_tableau_mimetype() ;
		$name=$_mimetypes_bymimetype_[$ligne->explnum_mimetype]["plugin"] ;
		if ($name) {
			$type = "" ;
			// width='700' height='525' 
			$name = " name='$name' ";
		}
		$type="type='$ligne->explnum_mimetype'" ;
		
		if ($_mimetypes_bymimetype_[$ligne->explnum_mimetype]["embeded"]=="yes") {
			print "<html><body><EMBED src=\"./doc_num_data.php?explnum_id=$explnum_id\" $type $name controls='console' ></EMBED></body></html>" ;
			if ($fo) fclose($fo);
			exit ;
		}
		
		$nomfichier="";
		if ($ligne->explnum_nomfichier) {
			$nomfichier=$ligne->explnum_nomfichier;
		} elseif ($ligne->explnum_extfichier)
			$nomfichier="pmb".$ligne->explnum_id.".".$ligne->explnum_extfichier;
		if ($nomfichier) header("Content-Disposition: inline; filename=".$nomfichier);
		
		if ((substr($ligne->explnum_mimetype,0,5)=="image")&&($opac_photo_watermark)) {
			$content_image=reduire_image_middle($ligne->explnum_data);
			if ($content_image) {
				print header("Content-Type: image/png");
				print $content_image;
			} else {
				header("Content-Type: ".$ligne->explnum_mimetype);
				print $ligne->explnum_data;
			}
		} else {
			header("Content-Type: ".$ligne->explnum_mimetype);
			if (($fo)&&($ligne->explnum_path)&&(!$ligne->explnum_data)) {
				while(!feof($fo)){
					print fread($fo,4096);
				}
				//fpassthru($fo);
				fclose($fo);
			} else print $ligne->explnum_data;
		}
		exit ;
	}
	
	if ($ligne->explnum_mimetype=="URL") {
		if ($ligne->explnum_url) header("Location: $ligne->explnum_url");
		exit ;
	}
}else{
	print $msg['forbidden_docnum'];
}
