<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bannette.class.php,v 1.132 2014-03-12 14:41:30 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ("$class_path/search.class.php") ; 
require_once ("$class_path/equation.class.php") ; 
require_once ("$class_path/mono_display.class.php") ; 
require_once ("$class_path/serial_display.class.php") ; 
require_once ($include_path."/mail.inc.php") ;
require_once ($include_path."/export_notices.inc.php");
require_once($class_path."/export_param.class.php");
require_once($class_path."/notice_tpl_gen.class.php");
if($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
	require_once ("$class_path/acces.class.php") ; 
}
require_once($class_path."/parametres_perso.class.php");
require_once($class_path."/categories.class.php");
require_once($class_path."/bannette_facettes.class.php");

// d�finition de la classe de gestion des 'bannettes'
class bannette {

// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------
	var $id_bannette=0;	
	var $num_classement=1; 
	var $nom_classement=""; 
	var	$nom_bannette="";
	var	$comment_gestion="";
	var	$comment_public="";
	var	$entete_mail="";
	var	$piedpage_mail="";
	var	$notice_tpl="";
	var	$date_last_remplissage="";
	var	$date_last_envoi="";
	var	$aff_date_last_remplissage="";
	var	$aff_date_last_envoi="";
	var $date_last_envoi_sql="";
	var	$proprio_bannette=0;
	var	$bannette_auto=0;
	var	$periodicite=0;
	var	$diffusion_email=0;
	var $nb_notices_diff=0;
	var	$categorie_lecteurs=0;
	var $groupe_lecteurs=0;
	var	$update_type="C";
	var	$nb_notices=0;
	var	$nb_abonnes=0;
	var	$alert_diff=0;
	var $texte_export ;
	var $texte_diffuse ;
	var $num_panier ;
	var $limite_type; // D ou  I : Days ou Items
	var $limite_nombre; // Nombre limite, = soit dur�e de vie d'une notice dans la bannette ou bien nombre maxi de notices dans le panier
	var $liste_id_notice = array();
	var $export_contenu = "";
	var $typeexport = "pmbxml2marciso";
	var $prefixe_fichier = "prefix_";
	var $param_export = array();
	var	$group_pperso=0;			
	var $archive_number=0;
	var $group_type = 0; 
	var	$statut_not_account=0;
	var $field_type='';						
	var $field_id=0;		
	var $group_pperso_order=array();
	var $document_generate=0;
	var $document_notice_tpl=0;
	var $document_insert_docnum=0;
	var $document_group=0;
	var $document_add_summary=0;
	var $aff_document="";
	var $bannette_opac_accueil=0;
	var $document_diffuse=""; //contenu html du document g�n�r� 
	var $descriptors = array();
// ---------------------------------------------------------------
//		constructeur
// ---------------------------------------------------------------
function bannette($id=0) {
	if ($id) {
		// on cherche � atteindre une notice existante
		$this->id_bannette = $id;
		$this->getData();
	} else {
		// la notice n'existe pas
		$this->id_bannette = 0;
		$this->getData();
	}
}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	global $msg;
	$this->p_perso=new parametres_perso("notices");
	if (!$this->id_bannette) {
		// pas d'identifiant. on retourne un tableau vide
	 	$this->id_bannette=0;
	 	$this->num_classement = 1 ;
	 	$this->nom_classement = "" ;
		$this->nom_bannette="";
		$this->comment_gestion="";
		$this->comment_public="";
		$this->entete_mail="";
		$this->piedpage_mail="";
		$this->notice_tpl="";
		$this->date_last_remplissage="";
		$this->date_last_envoi=today();
		$this->aff_date_last_remplissage="";
		$this->aff_date_last_envoi=formatdate($this->date_last_envoi);
		$this->date_last_envoi_sql=today();
		$this->proprio_bannette=0;
		$this->bannette_auto=0;
		$this->periodicite=0;
		$this->diffusion_email=0;
		$this->nb_notices_diff=0;
		$this->categorie_lecteurs="";
		$this->groupe_lecteurs="";
		$this->update_type="C";
		$this->nb_notices = 0 ;
		$this->nb_abonnes = 0 ;
		$this->alert_diff = 0 ;
		$this->num_panier = 0 ;
		$this->limite_type = '' ;
		$this->limite_nombre = 0 ;
		$this->typeexport = ''; 
		$this->group_pperso = 0; 
		$this->group_type = 0;  
		$this->statut_not_account = 0; 
		$this->archive_number = 0; 			
		$this->document_generate=0;
		$this->document_notice_tpl=0;
		$this->document_insert_docnum=0;
		$this->document_group=0;
		$this->document_add_summary=0;
		$this->descriptor_num=0;
		$this->prefixe_fichier = "prefix_";
		$this->bannette_opac_accueil = 0;
	} else {
		$requete = "SELECT id_bannette, num_classement, nom_bannette,comment_gestion,comment_public,statut_not_account, ";
		$requete .= "date_last_remplissage, date_format(date_last_remplissage, '".$msg["format_date_heure"]."') as aff_date_last_remplissage, ";
		$requete .= "date_last_envoi,date_last_envoi as date_last_envoi_sql, date_format(date_last_envoi, '".$msg["format_date_heure"]."') as aff_date_last_envoi, ";
		$requete .= "proprio_bannette,bannette_auto,periodicite,diffusion_email, nb_notices_diff, categorie_lecteurs, groupe_lecteurs, update_type, entete_mail, piedpage_mail, notice_tpl, num_panier, ";
		$requete .= "limite_type, limite_nombre, typeexport, prefixe_fichier, param_export, group_type, group_pperso, archive_number, ";
		$requete .= "document_generate, document_notice_tpl, document_insert_docnum, document_group, document_add_summary, bannette_opac_accueil ";
		$requete .= "FROM bannettes WHERE id_bannette='".$this->id_bannette."' " ;
		$result = mysql_query($requete, $dbh) or die ($requete."<br /> in bannette.class.php : ".mysql_error());
		if(mysql_num_rows($result)) {
			$temp = mysql_fetch_object($result);
		 	$this->id_bannette			= $temp->id_bannette ;
		 	$this->num_classement 		= $temp->num_classement ;
			$this->nom_bannette			= $temp->nom_bannette ;
			$this->comment_gestion		= $temp->comment_gestion ;	
			$this->comment_public		= $temp->comment_public ;
			$this->entete_mail			= $temp->entete_mail ;
			$this->piedpage_mail		= $temp->piedpage_mail ;
			$this->notice_tpl			= $temp->notice_tpl ;
			$this->date_last_remplissage= $temp->date_last_remplissage ;
			$this->date_last_envoi		= $temp->date_last_envoi ;	
			$this->aff_date_last_remplissage	= $temp->aff_date_last_remplissage ;
			$this->aff_date_last_envoi	= $temp->aff_date_last_envoi ;	
			$this->date_last_envoi_sql	= $temp->date_last_envoi_sql;
			$this->proprio_bannette		= $temp->proprio_bannette ;	
			$this->bannette_auto		= $temp->bannette_auto ;
			$this->periodicite			= $temp->periodicite ;
			$this->diffusion_email		= $temp->diffusion_email ;	
			$this->nb_notices_diff 		= $temp->nb_notices_diff;
			$this->categorie_lecteurs	= $temp->categorie_lecteurs ;
			$this->groupe_lecteurs		= $temp->groupe_lecteurs ;
			$this->update_type			= $temp->update_type ;
			$this->num_panier			= $temp->num_panier ;
			$this->limite_type 			= $temp->limite_type ;
			$this->limite_nombre 		= $temp->limite_nombre ;
			$this->typeexport 			= $temp->typeexport ;
			$this->prefixe_fichier 		= $temp->prefixe_fichier ;
			$this->group_pperso 		= $temp->group_pperso ; 
			$this->group_type 			= $temp->group_type; 
			$this->statut_not_account 	= $temp->statut_not_account ; 
			$this->archive_number 		= $temp->archive_number ; 
			$this->document_generate 	= $temp->document_generate ;
			$this->document_notice_tpl	= $temp->document_notice_tpl;
			$this->document_insert_docnum= $temp->document_insert_docnum ;
			$this->document_group 		= $temp->document_group ;
			$this->document_add_summary = $temp->document_add_summary ;
			$this->descriptor_num		= $temp->ban_descriptor_num ;
			$this->bannette_opac_accueil= $temp->bannette_opac_accueil ;
 
			$this->param_export			= unserialize($temp->param_export) ;
			$this->compte_elements();			
			$requete = "SELECt nom_classement FROM classements WHERE id_classement='".$this->num_classement."'" ;
			$resultclass = mysql_query($requete, $dbh) or die ($requete."<br /> in bannette.class.php : ".mysql_error());
			if ($temp = mysql_fetch_object($resultclass)) $this->nom_classement = $temp->nom_classement ;
			else $this->nom_classement = "" ;		
		} else {
			// pas de bannette avec cette cl�
		 	$this->id_bannette=0;
		 	$this->num_classement = 1 ;
		 	$this->nom_classement = "" ;
			$this->nom_bannette="";
			$this->comment_gestion="";
			$this->comment_public="";
			$this->entete_mail="";
			$this->piedpage_mail="";
			$this->notice_tpl="";
			$this->date_last_remplissage="";
			$this->date_last_envoi="";
			$this->date_last_envoi_sql="";
			$this->aff_date_last_remplissage="";
			$this->aff_date_last_envoi="";
			$this->proprio_bannette=0;
			$this->bannette_auto=0;
			$this->periodicite=0;
			$this->diffusion_email=0;
			$this->nb_notices_diff=0;
			$this->categorie_lecteurs="";
			$this->groupe_lecteurs="";
			$this->update_type="C";
			$this->nb_notices = 0 ;
			$this->nb_abonnes = 0 ;
			$this->num_panier = 0 ;
			$this->limite_type = '' ;
			$this->limite_nombre = 0 ;
			$this->typeexport = '' ;
			$this->prefixe_fichier = "prefix_";
			$this->group_pperso = 0; 
			$this->group_type = 0;  
			$this->statut_not_account = 0; 
			$this->archive_number=0;			
			$this->document_generate=0;
			$this->document_notice_tpl=0;
			$this->document_insert_docnum=0;
			$this->document_group=0;
			$this->document_add_summary=0;
			$this->descriptor_num=0;
			$this->bannette_opac_accueil=0;
		}
	}
	$this->get_descriptors();
}

function get_descriptors(){
	global $lang;
	
	$this->descriptors=array();
	// les descripteurs...
	$rqt = "select num_noeud from bannettes_descriptors where num_bannette = '".$this->id_bannette."' order by bannette_descriptor_order";
	$res = mysql_query($rqt);
	if(mysql_num_rows($res)){
		while($row = mysql_fetch_object($res)){
			$categ = new categories($row->num_noeud, $lang);
			$this->descriptors[] = $categ->num_noeud;
		}
	}
	return $this->descriptors;
}

function build_sel_descriptor(){
	global $msg, $charset,$lang;
	global $dsi_desc_field;
	global $dsi_desc_first_desc,$dsi_desc_other_desc;
	
	$categs = "";
	if(count($this->descriptors)){
		for ($i=0 ; $i<count($this->descriptors) ; $i++){
			if($i==0) $categ=$dsi_desc_first_desc;
			else $categ = $dsi_desc_other_desc;
			//on y va
			$categ = str_replace('!!icateg!!', $i, $categ);
			$categ = str_replace('!!categ_id!!', $this->descriptors[$i], $categ);
			$categorie = new categories($this->descriptors[$i],$lang);
			$categ = str_replace('!!categ_libelle!!', htmlentities($categorie->libelle_categorie,ENT_QUOTES, $charset), $categ);
			$categs.=$categ;
		}
		$categs = str_replace("!!max_categ!!",count($this->descriptors),$categs);
	}else{
		$categs=$dsi_desc_first_desc;
		$categs = str_replace('!!icateg!!', 0, $categs) ;
		$categs = str_replace('!!categ_id!!', "", $categs);
		$categs = str_replace('!!categ_libelle!!', "", $categs);
		$categs = str_replace('!!max_categ!!', 1, $categs);
	}
	return str_replace("!!cms_categs!!",$categs,$dsi_desc_field);
}

function gen_facette_selection(){	
	$facette = new bannette_facettes($this->id_bannette);
	return $facette->gen_facette_selection();
}
// ---------------------------------------------------------------
//		show_form : affichage du formulaire de saisie
// ---------------------------------------------------------------
function show_form($type="pro") {

	global $msg, $charset;
	global $dsi_bannette_form;
	global $dsi_bannette_form_abo;
	global $nom_prenom_abo;
	global $dsi_bannette_notices_template, $PMBuserid;
	global $form_cb;
	
	if ($type=="abo") $dsi_bannette_form = $dsi_bannette_form_abo ;
	
	if($this->id_bannette) {
		$action = "./dsi.php?categ=bannettes&sub=$type&id_bannette=$this->id_bannette&suite=update";
		$link_duplicate =  "<input type='button' class='bouton' value='".$msg['bannette_duplicate_bouton']."' onclick='document.location=\"./dsi.php?categ=bannettes&sub=$type&id_bannette=$this->id_bannette&suite=duplicate\"' />";
		$link_annul = "<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./dsi.php?categ=bannettes&sub=$type&id_bannette=&suite=search&form_cb=$form_cb';\" />";
		$button_delete = "<input type='button' class='bouton' value='$msg[63]' onClick=\"confirm_delete();\">";
		$libelle = $msg['dsi_ban_form_modif'];
	} else {
		$action = "./dsi.php?categ=bannettes&sub=$type&id_bannette=0&suite=update";
		$link_duplicate = "";
		$link_annul = "<input type='button' class='bouton' value='$msg[76]' onClick=\"history.go(-1);\" />";
		$libelle = $msg['dsi_ban_form_creat'];
		$button_delete ="";
		$this->notice_tpl=$dsi_bannette_notices_template;
	}	

	$dsi_bannette_form = str_replace('!!libelle!!', $libelle, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!type!!', $type, $dsi_bannette_form);

	$dsi_bannette_form = str_replace('!!id_bannette!!', $this->id_bannette, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!action!!', $action, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!link_duplicate!!', $link_duplicate, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!link_annul!!', $link_annul, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!nom_bannette!!', htmlentities($this->nom_bannette,ENT_QUOTES, $charset), $dsi_bannette_form);
	
	if ($type=="pro") $dsi_bannette_form = str_replace('!!num_classement!!', show_classement_utilise ('BAN', $this->num_classement, 0), $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!num_classement!!', "<input type=hidden name=num_classement value=0 />", $dsi_bannette_form);
	
	global $id_empr ;
	$dsi_bannette_form = str_replace('!!id_empr!!', $id_empr, $dsi_bannette_form);
	
	$dsi_bannette_form = str_replace('!!comment_gestion!!', htmlentities($this->comment_gestion,ENT_QUOTES, $charset), $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!comment_public!!', htmlentities($this->comment_public,ENT_QUOTES, $charset), $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!entete_mail!!', htmlentities($this->entete_mail,ENT_QUOTES, $charset), $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!piedpage_mail!!', htmlentities($this->piedpage_mail,ENT_QUOTES, $charset), $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!date_last_remplissage!!', htmlentities($this->aff_date_last_remplissage,ENT_QUOTES, $charset), $dsi_bannette_form);
	
	$date_clic   = "onClick=\"openPopUp('./select.php?what=calendrier&caller=saisie_bannette&date_caller=".substr(preg_replace('/-/', '', $this->date_last_envoi),0,8)."&param1=form_date_last_envoi&param2=form_aff_date_last_envoi&auto_submit=NO&date_anterieure=YES', 'date_last_envoi', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\"  ";
	$date_last_envoi = "
				<input type='hidden' name='form_date_last_envoi' value='".str_replace(' ', '', str_replace('-', '', str_replace(':', '', $this->date_last_envoi)))."' />
				<input class='bouton' type='button' name='form_aff_date_last_envoi' value='".$this->aff_date_last_envoi."' ".$date_clic." />";
		
	$dsi_bannette_form = str_replace('!!date_last_envoi!!', $date_last_envoi, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!archive_number!!', $this->archive_number, $dsi_bannette_form);
	if ($type=="pro") $dsi_bannette_form = str_replace('!!proprio_bannette!!', htmlentities($msg['dsi_ban_no_proprio'],ENT_QUOTES, $charset), $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!proprio_bannette!!', htmlentities($nom_prenom_abo,ENT_QUOTES, $charset), $dsi_bannette_form);
			
	if ($this->bannette_auto) $dsi_bannette_form = str_replace('!!bannette_auto!!', "checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!bannette_auto!!', "", $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!periodicite!!', htmlentities($this->periodicite,ENT_QUOTES, $charset), $dsi_bannette_form);
	if ($this->diffusion_email) $dsi_bannette_form = str_replace('!!diffusion_email!!', "checked='checked'", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!diffusion_email!!', "", $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!nb_notices_diff!!', htmlentities($this->nb_notices_diff,ENT_QUOTES, $charset), $dsi_bannette_form);

	$dsi_bannette_form = str_replace('!!notice_tpl!!', notice_tpl_gen::gen_tpl_select("notice_tpl",$this->notice_tpl), $dsi_bannette_form);

	if ($this->statut_not_account) $dsi_bannette_form = str_replace('!!statut_not_account!!', "checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!statut_not_account!!', "", $dsi_bannette_form);	
	// group_type, group_pperso, group_facettes
	if($this->group_type){
		$dsi_bannette_form = str_replace('!!checked_group_facette!!', " checked='checked' ", $dsi_bannette_form);
		$dsi_bannette_form = str_replace('!!checked_group_pperso!!', "", $dsi_bannette_form);
	}else{
		$dsi_bannette_form = str_replace('!!checked_group_facette!!', "", $dsi_bannette_form);
		$dsi_bannette_form = str_replace('!!checked_group_pperso!!', " checked='checked' ", $dsi_bannette_form);		
	}
	$liste_p_perso = $this->p_perso->gen_liste_field("group_pperso",$this->group_pperso,$msg["dsi_ban_form_regroupe_pperso_no"]);				
	$dsi_bannette_form = str_replace('!!pperso_group!!', $liste_p_perso, $dsi_bannette_form);
				
	$dsi_bannette_form = str_replace('!!facette_group!!', $this->gen_facette_selection(), $dsi_bannette_form);
	
	if ($type=="pro") {
		$requete = "SELECT id_categ_empr, libelle FROM empr_categ ORDER BY libelle ";
		$onchange="res=confirm('".htmlentities($msg['dsi_ban_confirm_modif_categ'],ENT_QUOTES, $charset)."'); if (res) this.form.majautocateg.value=1; else this.form.majautocateg.value=0;";  
		$categ_lect_aff = gen_liste ($requete, "id_categ_empr", "libelle", "categorie_lecteurs", $onchange, $this->categorie_lecteurs, 0, $msg['dsi_ban_aucune_categ'], 0,$msg['dsi_ban_aucune_categ'], 0) ;
		$dsi_bannette_form = str_replace('!!categorie_lecteurs!!', $categ_lect_aff, $dsi_bannette_form);
	
		$requete = "SELECT id_groupe, libelle_groupe FROM groupe ORDER BY libelle_groupe ";
		$onchange="res=confirm('".htmlentities($msg['dsi_ban_confirm_modif_groupe'],ENT_QUOTES, $charset)."'); if (res) this.form.majautogroupe.value=1; else this.form.majautogroupe.value=0;";  
		$groupe_lect_aff = gen_liste ($requete, "id_groupe", "libelle_groupe", "groupe_lecteurs", $onchange, $this->groupe_lecteurs, 0, $msg['dsi_ban_aucun_groupe'], 0,$msg['dsi_ban_aucun_groupe'], 0) ;
		$dsi_bannette_form = str_replace('!!groupe_lecteurs!!', $groupe_lect_aff, $dsi_bannette_form);
	} else {
		$dsi_bannette_form = str_replace('!!categorie_lecteurs!!', "<input type=hidden name=categorie_lecteurs value=0 />", $dsi_bannette_form);
		$dsi_bannette_form = str_replace('!!groupe_lecteurs!!', "<input type=hidden name=groupe_lecteurs value=0 />", $dsi_bannette_form);
	}
	
	$dsi_bannette_form = str_replace('!!desc_fields!!', $this->build_sel_descriptor(), $dsi_bannette_form);
	
	$requete = "SELECT idcaddie, name FROM caddie where type='NOTI' "; 
	if ($PMBuserid!=1) $requete.=" and (autorisations='$PMBuserid' or autorisations like '$PMBuserid %' or autorisations like '% $PMBuserid %' or autorisations like '% $PMBuserid') ";
	$requete.=" ORDER BY name ";
	$panier_bann_aff = gen_liste ($requete, "idcaddie", "name", "num_panier", "", $this->num_panier, 0, $msg['dsi_panier_aucun'], 0,$msg['dsi_panier_aucun'], 0) ;
	$dsi_bannette_form = str_replace('!!num_panier!!', $panier_bann_aff, $dsi_bannette_form);
	
	switch ($this->limite_type) {
		case "D":
			$selectn = "" ;
			$selecti = "" ;
			$selectd = " SELECTED " ;
			break;
		case "I":
			$selectn = "" ;
			$selectd = "" ;
			$selecti = " SELECTED " ;
			break;
		default:
		case "":
			$selecti = "" ;
			$selectd = "" ;
			$selectn = " SELECTED " ;
			break;
		}
	$limite_type = "<select name='limite_type' id='limite_type'>
					<option value='' $selectn>".$msg['dsi_ban_non_cumul']."</option>
					<option value='D' $selectd>".$msg['dsi_ban_cumul_jours']."</option>
					<option value='I' $selecti>".$msg['dsi_ban_cumul_notice']."</option>
					</select>";
	$dsi_bannette_form = str_replace('!!limite_type!!', $limite_type, $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!limite_nombre!!', $this->limite_nombre, $dsi_bannette_form);

	// update_type: se baser sur la date de cr�ation ou la date de mise � jour des notices ?
	switch ($this->update_type) {
		case "C":
			$selectu = "" ;
			$selectc = " SELECTED " ;
			break;
		case "U":
			$selectc = "" ;
			$selectu = " SELECTED " ;
			break;
		default:
		case "":
			$selectu = "" ;
			$selectc = " SELECTED " ;
			break;
		}
	$update_type = "<select name='update_type' id='update_type'>
					<option value='C' $selectc>".$msg['dsi_ban_update_type_c']."</option>
					<option value='U' $selectu>".$msg['dsi_ban_update_type_u']."</option>
					</select>";
	$dsi_bannette_form = str_replace('!!update_type!!', $update_type, $dsi_bannette_form);

	$exp = start_export::get_exports();
	$liste_exports = "<select name='typeexport' onchange=\"if(this.selectedIndex==0) document.getElementById('liste_parametre').style.display='none'; else document.getElementById('liste_parametre').style.display=''; \">" ;
	if (!$this->typeexport) $liste_exports .= "<option value='' selected>".$msg['dsi_ban_noexport']."</option>";
	else $liste_exports .= "<option value=''>".$msg['dsi_ban_noexport']."</option>";
	for ($i=0;$i<count($exp);$i++) {
		if ($this->typeexport==$exp[$i]["PATH"]) $liste_exports .= "<option value='".$exp[$i]["PATH"]."' selected>".$exp[$i]["NAME"]."</option>";
		else $liste_exports .= "<option value='".$exp[$i]["PATH"]."' >".$exp[$i]["NAME"]."</option>";
	}
	$liste_exports .= "</select>" ;
	$dsi_bannette_form = str_replace('!!typeexport!!', $liste_exports,  $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!prefixe_fichier!!', $this->prefixe_fichier,  $dsi_bannette_form);
	
	if($this->bannette_opac_accueil)$bannette_opac_accueil_check=" checked ";
	else $bannette_opac_accueil_check="";
	$dsi_bannette_form = str_replace('!!bannette_opac_accueil_check!!', $bannette_opac_accueil_check,  $dsi_bannette_form);
	
	if ($this->document_generate) $dsi_bannette_form = str_replace('!!document_generate!!', "checked=checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!document_generate!!', "", $dsi_bannette_form);		
	$dsi_bannette_form = str_replace('!!document_notice_tpl!!', notice_tpl_gen::gen_tpl_select("document_notice_tpl",$this->document_notice_tpl), $dsi_bannette_form);
	if ($this->document_insert_docnum) $dsi_bannette_form = str_replace('!!document_insert_docnum!!', "checked=checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!document_insert_docnum!!', "", $dsi_bannette_form);	
	if ($this->document_group) $dsi_bannette_form = str_replace('!!document_group!!', "checked=checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!document_group!!', "", $dsi_bannette_form);	
	if ($this->document_add_summary) $dsi_bannette_form = str_replace('!!document_add_summary!!', "checked=checked", $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!document_add_summary!!', "", $dsi_bannette_form);
	
	$dsi_bannette_form = str_replace('!!delete!!', $button_delete,  $dsi_bannette_form);
	
	// afin de revenir o� on �tait : $form_cb, le crit�re de recherche
	$dsi_bannette_form = str_replace('!!form_cb!!', $form_cb,  $dsi_bannette_form);
	if($this->param_export)
		$param=new export_param(EXP_DSI_CONTEXT, $this->param_export);
	else $param=new export_param(EXP_DEFAULT_GESTION);
	if(!$this->typeexport)
		$dsi_bannette_form = str_replace('!!display_liste_param!!', 'display:none',  $dsi_bannette_form);
	else $dsi_bannette_form = str_replace('!!display_liste_param!!', '',  $dsi_bannette_form);
	$dsi_bannette_form = str_replace('!!form_param!!', $param->check_default_param(),  $dsi_bannette_form);	
	print $dsi_bannette_form;
}

// ---------------------------------------------------------------
//		delete() : suppression 
// ---------------------------------------------------------------
function delete() {
	global $dbh;
	global $msg;
	
	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette

	$requete = "delete from bannette_abon WHERE num_bannette='$this->id_bannette'";
	$res = mysql_query($requete, $dbh);
	if ($this->proprio_bannette) {
		$requete = "select num_equation from bannette_equation WHERE num_bannette='$this->id_bannette'";
		$res = mysql_query($requete, $dbh);
		$temp = @mysql_fetch_object($res);
		$requete = "delete from equations WHERE id_equation='$temp->num_equation'";
		$res = mysql_query($requete, $dbh);
		}
		
	$requete = "delete from bannette_equation WHERE num_bannette='$this->id_bannette'";
	$res = mysql_query($requete, $dbh);
	$requete = "delete from bannette_contenu WHERE num_bannette='$this->id_bannette'";
	$res = mysql_query($requete, $dbh);
	$requete = "delete from bannettes WHERE id_bannette='$this->id_bannette'";
	$res = mysql_query($requete, $dbh);

	$query = mysql_query("DELETE bannettes FROM bannettes LEFT JOIN empr ON proprio_bannette = id_empr WHERE id_empr IS NULL AND proprio_bannette !=0");
	$query = mysql_query("DELETE equations FROM equations LEFT JOIN empr ON proprio_equation = id_empr WHERE id_empr IS NULL AND proprio_equation !=0 ");
	$query = mysql_query("DELETE bannette_equation FROM bannette_equation LEFT JOIN bannettes ON num_bannette = id_bannette WHERE id_bannette IS NULL ");
	$query = mysql_query("DELETE bannette_equation FROM bannette_equation LEFT JOIN equations on num_equation=id_equation WHERE id_equation is null");
	$query = mysql_query("DELETE bannette_abon FROM bannette_abon LEFT JOIN empr on num_empr=id_empr WHERE id_empr is null");
	$query = mysql_query("DELETE bannette_abon FROM bannette_abon LEFT JOIN bannettes ON num_bannette=id_bannette WHERE id_bannette IS NULL ");
	$del = "delete from bannettes_descriptors where num_bannette = '".$this->id_bannette."'";
	mysql_query($del);
	$facette = new bannette_facettes($this->id_bannette);
	$facette->delete();
}


// ---------------------------------------------------------------
//		update 
// ---------------------------------------------------------------
function update($temp) {

	global $dbh;
	global $msg;
	global $max_categ;
	global $group_type;
	
	if ($this->id_bannette) {
		// update
		$req = "UPDATE bannettes set ";
		$clause = " WHERE id_bannette='".$this->id_bannette."'";
	} else {
		$req = "insert into bannettes set date_last_remplissage=sysdate(), ";
		$clause = "";
	}	
	$req.="num_classement='$temp->num_classement',";
	$req.="nom_bannette='$temp->nom_bannette',";
	$req.="comment_gestion='$temp->comment_gestion',";	
	$req.="comment_public='$temp->comment_public',"; 
	$req.="entete_mail='$temp->entete_mail',"; 
	$req.="piedpage_mail='$temp->piedpage_mail',"; 
	$req.="notice_tpl='$temp->notice_tpl',"; 
	$req.="proprio_bannette='$temp->proprio_bannette',";	
	$req.="bannette_auto='$temp->bannette_auto',";
	$req.="periodicite='$temp->periodicite',";
	$req.="diffusion_email='$temp->diffusion_email',";	
	$req.="statut_not_account='$temp->statut_not_account',";	
	$req.="nb_notices_diff='$temp->nb_notices_diff',";	
	$req.="categorie_lecteurs='$temp->categorie_lecteurs',";
	$req.="groupe_lecteurs='$temp->groupe_lecteurs',";
	$req.="update_type='$temp->update_type',";
	$req.="num_panier='$temp->num_panier',";
	$req.="limite_type='$temp->limite_type',";
	$req.="limite_nombre='$temp->limite_nombre',";
	$req.="typeexport='$temp->typeexport',";
	$req.="prefixe_fichier='$temp->prefixe_fichier',";
	$req.="group_type='$group_type',";
	$req.="group_pperso='$temp->group_pperso',";
	$req.="archive_number='$temp->archive_number',";
	$req.="param_export='".addslashes(serialize($temp->param_export))."',";
	$req.="document_generate='$temp->document_generate',";
	$req.="document_notice_tpl='$temp->document_notice_tpl',";
	$req.="document_insert_docnum='$temp->document_insert_docnum',";
	$req.="document_group='$temp->document_group',";
	$req.="document_add_summary='$temp->document_add_summary',";
	$req.="bannette_opac_accueil='$temp->bannette_opac_accueil',";
	if (!$temp->date_last_envoi) $req.="date_last_envoi=sysdate() ";
		else $req.="date_last_envoi='".construitdateheuremysql($temp->date_last_envoi)."' ";
	$req.=$clause ;
	$res = mysql_query($req, $dbh);
	if (!$this->id_bannette) $this->id_bannette = mysql_insert_id() ;
	
	$this->descriptors=array();
	for ($i=0 ; $i<$max_categ ; $i++){
		$categ_id = 'f_categ_id'.$i;
		global $$categ_id;
		if($$categ_id > 0){
			$this->descriptors[] = $$categ_id;
		}
	}
	$del = "delete from bannettes_descriptors where num_bannette = '".$this->id_bannette."'";
	mysql_query($del);
	for($i=0 ; $i<count($this->descriptors) ; $i++){
		$rqt = "insert into bannettes_descriptors set num_bannette = '".$this->id_bannette."', num_noeud = '".$this->descriptors[$i]."', bannette_descriptor_order='".$i."'";
		mysql_query($rqt);
	}
	$facette = new bannette_facettes($this->id_bannette);
	$facette->save();
}

// ---------------------------------------------------------------
//		purger() : apr�s remplissage, vider ce qui d�passe selon le type de cumul de la bannette 
// ---------------------------------------------------------------
function purger() {
	global $dbh;
	global $msg;
	global $gestion_acces_active,$gestion_acces_empr_notice;
	
	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette

	//purge pour les bannettes privees des notices ne devant pas etre diffusees 
	if ($this->proprio_bannette && $gestion_acces_active==1 && $gestion_acces_empr_notice==1){
		$ac = new acces();
		$dom_2 = $ac->setDomain(2);
		$acces_j = $dom_2->getJoin($this->proprio_bannette,'4=0','num_notice');
		
		$q="delete from bannette_contenu using bannette_contenu $acces_j WHERE num_bannette='$this->id_bannette' ";
		mysql_query($q,$dbh);
	}
		
	
	switch ($this->limite_type) {
		case "D":
			$requete = "select num_notice from bannette_contenu WHERE num_bannette='$this->id_bannette' and ";
			$requete .= " date_add(date_ajout, INTERVAL ".$this->limite_nombre." DAY)<sysdate() ";
			$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
			$tab = array();
			while ($obj=mysql_fetch_object($res)) $tab[]=$obj->num_notice ;
			$notice_suppr=implode(",",$tab);
			if ($notice_suppr) {
				if ($this->num_panier) {
					$requete = "delete from caddie_content WHERE caddie_id='$this->num_panier' and object_id in (".$notice_suppr.") ";
					$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
				}
				$requete = "delete from bannette_contenu WHERE num_bannette='$this->id_bannette' and num_notice in (".$notice_suppr.") ";
				$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
			}
			break;
		case "I":
			$tab = array();
			// selection des ## derni�res notices, celles qu'il faut absolument garder
			$requete = "select num_notice from bannette_contenu, notices WHERE num_bannette='$this->id_bannette' and notice_id=num_notice order by date_ajout DESC, update_date DESC ";
			$requete .= " limit $this->limite_nombre ";
			$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
			while ($obj=mysql_fetch_object($res)) $tab[]=$obj->num_notice ;
			
			// selection des notices ajout�es depuis moins d'un jour
			$requete = "select num_notice from bannette_contenu WHERE num_bannette='$this->id_bannette' and ";
			$requete .= " date_add(date_ajout, INTERVAL 1 DAY)>=sysdate() ";
			$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
			while ($obj=mysql_fetch_object($res)) $tab[]=$obj->num_notice ;

			// suppression de tout ce qui d�passe
			$notice_suppr=implode(",",$tab);
			if ($notice_suppr) {
				if ($this->num_panier) {
					$requete = "delete from caddie_content WHERE caddie_id='$this->num_panier' and object_id not in (".$notice_suppr.") ";
					$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
				}
				$requete = "delete from bannette_contenu WHERE num_bannette='$this->id_bannette' and num_notice not in (".$notice_suppr.") ";
				$res = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
			}
			break;
		default:
		case "":
			break;
		}
		
		$this->compte_elements() ;
	}

// ---------------------------------------------------------------
//		vider() : vider le contenu de la bannette 
// ---------------------------------------------------------------
function vider() {
	global $dbh;
	global $msg;

	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette

	$requete = "delete from bannette_contenu WHERE num_bannette='$this->id_bannette'";
	mysql_query($requete, $dbh);
	$requete = "delete from caddie_content WHERE caddie_id='$this->num_panier'";
	mysql_query($requete, $dbh);

	$this->compte_elements() ;
}

// ---------------------------------------------------------------
//		remplir() : remplir la bannette � partir des �quations 
// ---------------------------------------------------------------
function remplir() {
	global $dbh;
	global $msg;
	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette
	
	// r�cup�rer les �quations associ�es � la bannette
	$equations = $this->get_equations() ;
	$res_affichage = "<ul>" ;
	if ($this->update_type=="C") $colonne_update_create="create_date";
		else $colonne_update_create="update_date";
	for ($i=0 ; $i < sizeof($equations) ; $i++) {
		// pour chaque �quation ajouter les notices trouv�es au contenu de la bannette
		$equ = new equation ($equations[$i]) ;
		$search = new search() ;
		$search->unserialize_search($equ->requete) ;
		
		$table = $search->make_search() ;
		if($this->statut_not_account) 
			$temp_requete = "insert ignore into bannette_contenu (num_bannette, num_notice) (select ".$this->id_bannette." , notices.notice_id from $table , notices where notices.$colonne_update_create>='".$this->date_last_envoi."' and $table.notice_id=notices.notice_id )" ;
		else 
			$temp_requete = "insert ignore into bannette_contenu (num_bannette, num_notice) (select ".$this->id_bannette." , notices.notice_id from $table , notices, notice_statut where notices.$colonne_update_create>='".$this->date_last_envoi."' and $table.notice_id=notices.notice_id and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0) or (notice_visible_opac_abon=1 and notice_visible_opac=1))) " ;
		@mysql_query($temp_requete, $dbh);

		$res_affichage .= "<li>".$equ->human_query."</li>" ;
		
	    $temp_requete = "drop table $table " ;
		@mysql_query($temp_requete, $dbh);
	}
	// remplissage du panier avec le contenu de la bannette
	if ($this->num_panier) {
		$temp_requete = "delete from caddie_content where caddie_id='".$this->num_panier."'" ;
		mysql_query($temp_requete, $dbh);
		$temp_requete = "insert into caddie_content (caddie_id, object_id) (select ".$this->num_panier.", num_notice from bannette_contenu where num_bannette=".$this->id_bannette.")" ;
		mysql_query($temp_requete, $dbh) or die (mysql_error().$temp_requete);
	}
	
	$res_affichage .= "</ul>" ;
	$this->compte_elements() ;
	$temp_requete = "update bannettes set date_last_remplissage=sysdate() where id_bannette='".$this->id_bannette."' " ;
	mysql_query($temp_requete, $dbh);
	$this->purger();
	return $res_affichage ;
}

// ---------------------------------------------------------------
//		construit_diff() :   
// ---------------------------------------------------------------
function construit_diff() {
	global $base_path,$opac_url_base,$opac_default_style,$charset;
	
	$contenu = $this->construit_contenu_HTML() ;
	$contenu_total = $this->construit_contenu_HTML(0) ;
	$titre = $this->construit_liens_HTML() ;
	
	// r�cup�ration des fichiers de style commun
	$css_path= $base_path."/opac_css/styles/common/dsi";
	if (is_dir($css_path)) {
		if (($dh = opendir($css_path))) {
			while (($css_file = readdir($dh)) !== false) {
				if(filetype($css_path."/".$css_file) =='file') {
					if( substr($css_file, -4) == ".css" ) {
						$css.="<link rel='stylesheet' type='text/css' href='".$opac_url_base."styles/common/dsi/".$css_file."' title='lefttoright' />\n";
					}
				}
			}
			closedir($dh);
		}
	}
	// r�cup�ration des fichiers de style personnalis�
	$css_path= $base_path."/opac_css/styles/".$opac_default_style."/dsi";
	if (is_dir($css_path)) {
	    if (($dh = opendir($css_path))) {
	        while (($css_file = readdir($dh)) !== false) {		
	            if(filetype($css_path."/".$css_file) =='file') { 	       
	            	if( substr($css_file, -4) == ".css" ) {
	            		$css.="<link rel='stylesheet' type='text/css' href='".$opac_url_base."styles/".$opac_default_style."/dsi/".$css_file."' title='lefttoright' />\n";
	            	}	
	            }	
	        }
	        closedir($dh);
	    }
	}  
	if($this->document_generate && $this->aff_document){
		$this->document_diffuse = "<html><head><META HTTP-EQUIV='CONTENT-TYPE' CONTENT='text/html; charset=$charset'>$css</head><body>".$titre . $this->aff_document . $this->piedpage_mail. "</body></html>";
	}

	$this->texte_diffuse = "<html><head>$css</head><body>". $titre;
	if ($this->diffusion_email) $this->texte_diffuse .= $contenu; 
	$this->texte_diffuse .= $this->piedpage_mail;
	$this->texte_diffuse .= "</body></html>";
	$this->texte_diffuse = str_replace ("!!nb_notice!!",$this->nb_notices,$this->texte_diffuse) ;
	$this->texte_export = "<html><head>$css</head><body>".$titre . $contenu_total . "</body></html>";
}	

function get_empr_mail($id_empr){
	global $dbh;
	
	$requete = "select empr_mail, bannette_mail from empr,  bannette_abon, bannettes ";
	$requete .= "where num_bannette='".$this->id_bannette."' and num_empr=$id_empr and num_bannette=id_bannette and num_empr=id_empr";
	
	$res = mysql_query($requete, $dbh);
	$emaildest="";
	if($empr=mysql_fetch_object($res)) {
		$emaildest = $empr->empr_mail;
		if ($empr->bannette_mail && $emaildest){
			$destinataires = explode(";",$emaildest);
			$found=0;
			foreach($destinataires as $mail){
				if($mail == $empr->bannette_mail){
					$found=1;
					break;					
				}
			}
			if($found)$emaildest=$empr->bannette_mail;
		}		
	}
	return $emaildest;
}
// ---------------------------------------------------------------
//		diffuser() : diffuser le contenu de la bannette  
// ---------------------------------------------------------------

function diffuser() {
	global $dbh;
	global $msg, $charset, $base_path, $opac_connexion_phrase, $pmb_mail_delay;
	
	global $PMBusernom;
	global $PMBuserprenom;
	global $PMBuseremail;

	if (!$this->id_bannette) return $msg['dsi_ban_no_access']."<br />"; // impossible d'acc�der � cette bannette
	if (!$this->nb_notices && $this->diffusion_email) return $msg['dsi_ban_empty']."<br />"; // On demande � diffuser le contenu et la bannette vide : pas question d'envoyer du vide
	
	mysql_set_wait_timeout(3600);
	
	$this->construit_diff();
	$texte_base = $this->texte_diffuse ;
	if ($this->export_contenu) {
		$fic_params = $base_path."/admin/convert/imports/".$this->typeexport."/params.xml";
		$temppar = file_get_contents($fic_params);
		$params = _parser_text_no_function_($temppar,"PARAMS");
		if ($params["OUTPUT"][0]["SUFFIX"]) $ext=$params["OUTPUT"][0]["SUFFIX"];
		else $ext="fic";
		$pieces_jointes[0]["nomfichier"] = $this->prefixe_fichier.today().".".$ext ;
		$pieces_jointes[0]["contenu"] = $this->export_contenu ;
	}
	$nb_dest=0;
	$nb_echec=0;
	$nb_no_email=0;
	
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=".$charset."\n";
	
	$requete = "select id_empr, empr_mail, empr_nom, empr_prenom, empr_login, empr_password, statut_libelle, allow_dsi, allow_dsi_priv, proprio_bannette, bannette_mail from empr, empr_statut, bannette_abon, bannettes ";
	$requete .= "where num_bannette='".$this->id_bannette."' and num_empr=id_empr and empr_statut=idstatut and num_bannette=id_bannette "; 
	$requete .= "order by empr_nom, empr_prenom ";
	$res = mysql_query($requete, $dbh);

	while(($empr=mysql_fetch_object($res))) {
		$emaildest = $empr->empr_mail;		
		if ($empr->bannette_mail && $emaildest){
			$destinataires = explode(";",$emaildest);
			$found=0;
			foreach($destinataires as $mail){
				if($mail == $empr->bannette_mail){
					$found=1;					
					break;
				}
			}
			if($found)$emaildest=$empr->bannette_mail;
		}
		$texte = $texte_base ;
		if ($emaildest) {
			if ((!$empr->allow_dsi && !$empr->proprio_bannette) || (!$empr->allow_dsi_priv && $empr->proprio_bannette)) {
				//si la dsi n'est pas autoris�e pour ce lecteur, affichage de l'info mais pas d'envoi
				$nb_echec++;
				$echec_email .= "- ".$empr->empr_nom." ".$empr->empr_prenom." (".$msg["statut_empr"]."' ".$empr->statut_libelle."')<br />" ;
			} else {	
				//function mailpmb($to_nom="", $to_mail, $obj="", $corps="", $from_name="", $from_mail, $headers, $copie_CC="", $copie_BCC="", $faire_nl2br=0, $pieces_jointes=array()) {
				$dates = time();
				$login = $empr->empr_login;
				$code=md5($opac_connexion_phrase.$login.$dates);	
				$texte = str_replace('!!code!!',$code,$texte);
				$texte = str_replace('!!login!!',$login,$texte);
				$texte = str_replace('!!date_conex!!',$dates,$texte);
				$texte = str_replace('!!empr_name!!',$empr->empr_nom,$texte);
				$texte = str_replace('!!empr_first_name!!',$empr->empr_prenom,$texte);
				$res_envoi=@mailpmb($empr->empr_prenom." ".$empr->empr_nom, $emaildest,$this->comment_public,$texte,$PMBuserprenom." ".$PMBusernom, $PMBuseremail, $headers, "", "", 0, $pieces_jointes);
				if ($pmb_mail_delay*1) sleep((int)$pmb_mail_delay*1/1000);
				if ($res_envoi) { 
					$nb_dest++;
				} else {
					$nb_echec++;
					$echec_email .= "- ".$empr->empr_nom." ".$empr->empr_prenom."<br />" ;
				}
			}
		} else {
			$nb_no_email++;
			$no_email .= "- ".$empr->empr_nom." ".$empr->empr_prenom."<br />" ;
		}
	}
	
	/* A commenter pour tests */ 
	$temp_requete = "update bannettes set date_last_envoi=sysdate() where id_bannette='".$this->id_bannette."' " ;
	$res = mysql_query($temp_requete, $dbh);

	$res_envoi = $msg["dsi_dif_res_dif"]."<ul><li>".$msg["dsi_dif_res_dif_mail_ok"].": $nb_dest </li>";
	if ($nb_echec) 
		$res_envoi .= "<li>".$msg["dsi_dif_res_dif_mail_echec"].": $nb_echec <blockquote>$echec_email</blockquote></li>" ;
	if ($nb_no_email) 
		$res_envoi .= "<li>".$msg["dsi_dif_res_dif_no_mail"].": $nb_no_email <blockquote>$no_email</blockquote></li>" ;
	$res_envoi .= "</ul>" ;
	if ($nb_echec || $nb_no_email) 
		$res_envoi .= "<script>openPopUp('./print_dsi.php?id_bannette=$this->id_bannette', 'Impression de DSI', 500, 400, -2, -2, 'toolbar=no, infobar=no, resizable=yes, scrollbars=yes')</script>" ;
	
	return $res_envoi ;
}

// ---------------------------------------------------------------
//		get_equations() : construire un tableau des �quations associ�es  
// ---------------------------------------------------------------
function get_equations() {
	global $dbh;
	global $msg;
	
	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette

	$requete = "select num_equation from bannette_equation, equations WHERE num_bannette='$this->id_bannette' and id_equation=num_equation ";
	$res = mysql_query($requete, $dbh);
	while($equ=mysql_fetch_object($res)) {
		$tab_equ[] = $equ->num_equation ;
		}
	return $tab_equ ;
	}

// ---------------------------------------------------------------
// affichage du contenu complet d'une bannette
// ---------------------------------------------------------------
function aff_contenu_bannette ($url_base="", $no_del=false ) {
	global $msg;
	global $dbh;
	global $begin_result_liste, $end_result_liste;
	global $end_result_list;
	global $url_base_suppr_bannette ;

	$return_affichage = "";
	$url_base_suppr_bannette = $url_base ;

	$cb_display = "
		<div id=\"el!!id!!Parent\" class=\"notice-parent\">
    		<span class=\"notice-heada\">!!heada!!</span>
    		<br />
		</div>
		";

	$requete = "SELECT num_notice FROM bannette_contenu where num_bannette='".$this->id_bannette."' ";

	$liste=array();
	$result = @mysql_query($requete, $dbh);
	if(mysql_num_rows($result)) {
		while ($temp = mysql_fetch_object($result)) {
			if($this->group_pperso) {			
				$this->p_perso->get_values($temp->num_notice);
				$values = $this->p_perso->values;
				foreach ( $values as $field_id => $vals ) {
					if ($this->group_pperso==$field_id) {	
						break;
					}
				}				
				$liste_group[$vals[0]][] = $temp->num_notice; 
			}
			else $liste[] = array('num_notice' => $temp->num_notice) ; 
		}	 
	}
	if(count($liste_group)) {
		foreach($liste_group as $list_notice) {
			foreach($list_notice as $num_notice) {
				$liste[] = array('num_notice' => $num_notice) ; 	
			}			
		}
	}

	if(!sizeof($liste) || !is_array($liste)) {
		return $msg['dsi_ban_empty'];
	} else {
		// boucle de parcours des notices trouv�es
		// inclusion du javascript de gestion des listes d�pliables
		// d�but de liste
		$return_affichage .= $begin_result_liste;
		//Affichage du lien impression et panier
		
		while(list($cle, $object) = each($liste)) {
			
			// affichage de la liste des notices sous la forme 'expandable'
			$requete = "SELECT * FROM notices WHERE notice_id='".$object['num_notice']."' ";
			$fetch = mysql_query($requete, $dbh);
			if (mysql_num_rows($fetch)) {
				$notice = mysql_fetch_object($fetch);
				if($notice->niveau_biblio != 's' && $notice->niveau_biblio != 'a') {
					// notice de monographie
					$link = './catalog.php?categ=isbd&id=!!id!!';
					$link_expl = './catalog.php?categ=edit_expl&id=!!notice_id!!&cb=!!expl_cb!!&expl_id=!!expl_id!!'; 
					$link_explnum = './catalog.php?categ=edit_explnum&id=!!notice_id!!&explnum_id=!!explnum_id!!';
					if (!$no_del) 
						$lien_suppr_cart = "<a href='$url_base&suite=suppr_notice&num_notice=$notice->notice_id&id_bannette=$this->id_bannette'><img src='./images/basket_empty_20x20.gif' alt='basket' title=\"".$msg['caddie_icone_suppr_elt']."\" /></a> $marque_flag";
					else 
						$lien_suppr_cart = "" ;
					$display = new mono_display($notice, 6, $link, 1, $link_expl, $lien_suppr_cart, $link_explnum );
					$return_affichage .= $display->result;
				} else {
					// on a affaire � un p�riodique
					// pr�paration des liens pour lui
					$link_serial = './catalog.php?categ=serials&sub=view&serial_id=!!id!!';
					$link_analysis = './catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=!!bul_id!!&art_to_show=!!id!!';
					$link_bulletin = './catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=!!id!!';
					if (!$no_del) 
						$lien_suppr_cart = "<a href='$url_base&suite=suppr_notice&num_notice=$notice->notice_id&id_bannette=$this->id_bannette'><img src='./images/basket_empty_20x20.gif' alt='basket' title=\"".$msg['caddie_icone_suppr_elt']."\" /></a> $marque_flag";
					else 
						$lien_suppr_cart = "" ;
					$link_explnum = "./catalog.php?categ=serials&sub=analysis&action=explnum_form&bul_id=!!bul_id!!&analysis_id=!!analysis_id!!&explnum_id=!!explnum_id!!";
					$serial = new serial_display($notice, 6, $link_serial, $link_analysis, $link_bulletin, $lien_suppr_cart, $link_explnum, 0);
					$return_affichage .= $serial->result;
				}
			}
		} // fin de liste
		$return_affichage .= $end_result_liste;
	}
	$return_affichage .= "<br />" ;
	return $return_affichage ;
}

// ---------------------------------------------------------------
//		suppr_notice() : suppression d'une notice d'une bannette
// ---------------------------------------------------------------
function suppr_notice($num_notice) {
	global $dbh, $msg;

	if (!$this->id_bannette) return $msg['dsi_ban_no_access']; // impossible d'acc�der � cette bannette

	$requete = "delete from bannette_contenu WHERE num_bannette='$this->id_bannette' and num_notice='$num_notice'";
	$res = mysql_query($requete, $dbh);
	}
	
function clean_archive(){
	global $dbh;
	// purge des archives au dela de $this->archive_number ou si a 0
	if(!$this->archive_number){
		$req="delete from dsi_archive where num_banette_arc='".$this->id_bannette."' ";
		mysql_query($req, $dbh);
	}else{
		$date_arc_list_to_delete=array();
		$nb=0;
		$req="select distinct date_diff_arc from dsi_archive where num_banette_arc='".$this->id_bannette."' order by date_diff_arc desc";
		$res_arc=mysql_query($req, $dbh);
		while (($r = mysql_fetch_object($res_arc))){
			if($nb++ >= $this->archive_number){
				$date_arc_list_to_delete[]=$r->date_diff_arc;
			}
		}
		foreach($date_arc_list_to_delete as $date_arc){
			$req="delete from dsi_archive where num_banette_arc='".$this->id_bannette."' and date_diff_arc='".$date_arc."'";
			mysql_query($req, $dbh);			
		}
	}	
}

// ---------------------------------------------------------------
//		construit_contenu_HTML() : Pr�paration du contenu du mail ou du bulletin
// ---------------------------------------------------------------
function construit_contenu_HTML ($use_limit=1) {
	global $dbh;
	global $msg;
	global $opac_url_base, $use_opac_url_base ;
	global $deflt2docs_location;
	
	global $url_base_opac;
	$url_base_opac = $opac_url_base."index.php?lvl=notice_display&id=";
	$use_opac_url_base=true;
	// pour URL image vue de l'ext�rieur
	global $prefix_url_image ;
	global $depliable ;
	$depliable = 0 ;
	$prefix_url_image = $opac_url_base ;

	global $dsi_bannette_notices_order ;
	if (!$dsi_bannette_notices_order) $dsi_bannette_notices_order="index_serie, tnvol, index_sew";
	if ($this->nb_notices_diff && $use_limit) $limitation = " LIMIT $this->nb_notices_diff " ;
	
	// purge des archives au dela de $this->archive_number ou si a 0
	$this->clean_archive();
	
	$requete = "select num_notice from bannette_contenu, notices where num_bannette='".$this->id_bannette."' and notice_id=num_notice order by $dsi_bannette_notices_order $limitation ";
	$resultat = mysql_query($requete, $dbh) or die($requete." ".mysql_error());
	// param�trage :
	$environement["short"] = 6 ;
	$environement["ex"] = 0 ;
	$environement["exnum"] = 1 ;
	
	if (($this->nb_notices_diff >= $this->nb_notices) || (!$this->nb_notices_diff)) $nb_envoyees = $this->nb_notices ;
	else $nb_envoyees = $this->nb_notices_diff ;

	if($this->notice_tpl){
		$noti_tpl=new notice_tpl_gen($this->notice_tpl);
	} else {
		$resultat_aff .= "<hr />";
		$resultat_aff .= sprintf($msg["dsi_diff_n_notices"],$nb_envoyees, $this->nb_notices);
		$resultat_aff .= "<hr />";		
	}
	$liste=array();
	$liste_group=array();
	$notice_group=array();
	if(mysql_num_rows($resultat)) {
		while (($temp = mysql_fetch_object($resultat))) {
			// Si un champ perso est donn� comme crit�re de regroupement
			if($this->group_pperso && $this->group_type!=1) {			
				$this->p_perso->get_values($temp->num_notice);
				$values = $this->p_perso->values;
				foreach ( $values as $field_id => $vals ) {
					if ($this->group_pperso==$field_id) {		
						$notice_group[$temp->num_notice] = $this->p_perso->get_formatted_output(array($vals[0]),$field_id);
						$this->field_type = $this->p_perso->t_fields[$field_id]["TYPE"];
						$this->field_id = $field_id;
						$liste_group[$vals[0]][] = $temp; 	
						break;
					}
				}							
			}
			else $liste[] = $temp ; 
			// archivage
			if($this->archive_number){
				$req="insert into dsi_archive set num_banette_arc='".$this->id_bannette."', num_notice_arc='".$temp->num_notice."', date_diff_arc=CURDATE()    ";
				mysql_query($req, $dbh);
			}	
		}	 
	}
	
	// groupement par facettes
	if (count($liste) && $this->group_type==1) {
		$notice_ids=array();
		foreach($liste as $r) $notice_ids[]=$r->num_notice;
		$facette = new bannette_facettes($this->id_bannette);
		$this->aff_document=$facette->build_document($notice_ids,$this->notice_tpl,$this->document_add_summary);
		return $this->aff_document ;
	}
	if(count($liste_group)) {
		foreach($liste_group as $list_notice) {
			$req_list=array();
			foreach($list_notice as $r) {
				$req_list[]=$r->num_notice;					
			}
			$requete = "select notice_id as num_notice from  notices where  notice_id in(".implode(",",$req_list).") order by $dsi_bannette_notices_order ";
			$res_tri = mysql_query($requete, $dbh) ;
			while (($r = mysql_fetch_object($res_tri))) {
				$liste[] = $r; 
			}			
		}
	}
	$group_printed=$tri_tpl=array();
	$memo_resultat_aff=$resultat_aff;	
	
	$group_printed_document=$tri_tpl_document=array();
	$aff_document="";
	if($this->document_notice_tpl && $this->document_generate){
		$noti_tpl_document=new notice_tpl_gen($this->document_notice_tpl);
	}
	if ($liste) { 
		foreach($liste as $r) {			
			if($this->document_generate){				
				$tpl_document="";
				if($this->document_notice_tpl) {
					$tpl_document=$noti_tpl_document->build_notice($r->num_notice,$deflt2docs_location);
				}
				if(!$tpl_document) {
					$n=mysql_fetch_object(@mysql_query("select * from notices where notice_id=".$r->num_notice));
					global $use_opac_url_base; $use_opac_url_base=1;
					global $use_dsi_diff_mode; $use_dsi_diff_mode=1;
					if($this->statut_not_account)  $use_dsi_diff_mode=2;//On ne tient pas compte des statuts de notice pour la diffusion
					if ($n->niveau_biblio == 'm'|| $n->niveau_biblio == 'b') {
						$mono=new mono_display($n,$environement["short"],"",$environement["ex"],"","","",0,1,$environement["exnum"],0,"",0,true,false);
						$tpl_document.= "<a href='".$url_base_opac.$n->notice_id."&code=!!code!!&emprlogin=!!login!!&date_conex=!!date_conex!!'><b>".$mono->header."</b></a><br /><br />\r\n";
						$tpl_document.= $mono->isbd;
					} elseif ($n->niveau_biblio == 's' || $n->niveau_biblio == 'a') {						
						$serial = new serial_display($n, 6, "", "", "", "", "", 0,1,$environement["exnum"],0, false );
						$tpl_document.= "<a href='".$url_base_opac.$n->notice_id."&code=!!code!!&emprlogin=!!login!!&date_conex=!!date_conex!!'><b>".$serial->header."</b></a><br /><br />\r\n";
						$tpl_document.= $serial->isbd;
					}
					$tpl_document=str_replace('<!-- !!avis_notice!! -->', "", $tpl_document);
				}				
				if($this->document_group) {
					$tri_tpl_document[$notice_group[$r->num_notice]][]= $tpl_document;
					if($notice_group[$r->num_notice] && !in_array($notice_group[$r->num_notice], $group_printed_document)) {
						$group_printed_document[]=$notice_group[$r->num_notice];	
					} 
				}else{
					$aff_document.= $tpl_document."<hr />\r\n";
				}	
			}
			// DSI classique par mail...
			$tpl="";
			if($this->notice_tpl) {
				$tpl=$noti_tpl->build_notice($r->num_notice,$deflt2docs_location);				
			} 			
			if(!$tpl) {
				$n=mysql_fetch_object(@mysql_query("select * from notices where notice_id=".$r->num_notice));
				global $use_opac_url_base; $use_opac_url_base=1;
				global $use_dsi_diff_mode; $use_dsi_diff_mode=1;
				if($this->statut_not_account)  $use_dsi_diff_mode=2;//On ne tient pas compte des statuts de notice pour la diffusion
				if ($n->niveau_biblio == 'm'|| $n->niveau_biblio == 'b') {
					//function mono_display($id, $level=1,      $action='', $expl=1,    $expl_link='', $lien_suppr_cart="", $explnum_link='', $show_resa=0, $print=0, $show_explnum=1, $show_statut=0, $anti_loop='', $draggable=0, $no_link=false, $show_opac_hidden_fields=true )
					$mono=new mono_display($n,$environement["short"],"",$environement["ex"],"","","",0,1,$environement["exnum"],0,"",0,true,false);
					$tpl .= "<a href='".$url_base_opac.$n->notice_id."&code=!!code!!&emprlogin=!!login!!&date_conex=!!date_conex!!'><b>".$mono->header."</b></a><br /><br />\r\n";
					$tpl .= $mono->isbd;
				} elseif ($n->niveau_biblio == 's' || $n->niveau_biblio == 'a') {
					// level=2 pour ne pas rajouter le "in ..." sur le titre de la notice de d�pouillement
					// function serial_display ($id, $level='1', $action_serial='', $action_analysis='', $action_bulletin='', $lien_suppr_cart="", $lien_explnum="", $bouton_explnum=1,$print=0,$show_explnum=1, $show_statut=0, $show_opac_hidden_fields=true ) {
					$serial = new serial_display($n, 6, "", "", "", "", "", 0,1,$environement["exnum"],0, false );
					$tpl .= "<a href='".$url_base_opac.$n->notice_id."&code=!!code!!&emprlogin=!!login!!&date_conex=!!date_conex!!'><b>".$serial->header."</b></a><br /><br />\r\n";
					$tpl .= $serial->isbd;
				}
				$tpl=str_replace('<!-- !!avis_notice!! -->', "", $tpl);
			}
			if($this->group_pperso) {	
				$tri_tpl[$notice_group[$r->num_notice]][]= $tpl;				
				if($notice_group[$r->num_notice] && !in_array($notice_group[$r->num_notice], $group_printed)) {					
					$group_printed[]=$notice_group[$r->num_notice];				
				} 
			}else{	
				$resultat_aff.= $tpl;			
				$resultat_aff .= "<div class='hr'><hr /></div>\r\n";
			}	
		}	
	}	
	// on retrie chaque goupe de notice selon le crit�re de tri de la DSI
	
	// il faut trier les regroupements par ordre alphab�tique (si document � g�n�rer et groupement)
	$this->aff_document="";
	if($this->document_generate ){
		if($this->document_group){
			$aff_document="";
		
			$this->pmb_ksort($tri_tpl_document);	
			$index=0;
			$summary="";
			global $group_separator;		    	
			global $notice_separator;
			foreach ($tri_tpl_document as $titre => $liste) {			
		    	if($group_separator)$aff_document.=$group_separator;
		    	else $aff_document.= "<div class='hr_group'><hr /></div>";			
			
				$index++;
				$aff_document.= "<a name='[$index]'></a><h1>".$index." - ".$titre."</h1>";	
				$summary.="<a href='#[$index]' class='summary_elt'>".$index." - ".$titre."</a><br />";		
				$nb=0;	
				foreach ($liste as $val) {
				    $aff_document.=$val;
				    if(++$nb < count($liste)){	
				    	if($notice_separator)$aff_document.=$notice_separator;
				    	else $aff_document.="<div class='hr'><hr /></div>";
				    }
				}			
				$aff_document.= "\r\n";			
			}
			//$summary.="</div>";
			if($this->document_add_summary){
				$aff_document="<a name='summary'></a><div class='summary'><br />".$summary."</div>".$aff_document;
			}	
			$this->aff_document=$aff_document;				
		}else{
			$this->aff_document=$aff_document;				
		}
	}	
	
	// il faut trier les regroupements par ordre alphab�tique
	if($this->group_pperso) {	
		$resultat_aff=$memo_resultat_aff;
		//ksort($tri_tpl);
		$this->pmb_ksort($tri_tpl);	
		$index=0;
		$summary="";
		foreach ($tri_tpl as $titre => $liste) {
			global $group_separator;
	    	if($group_separator)$resultat_aff.=$group_separator;
	    	else $resultat_aff.= "<div class='hr_group'><hr /></div>";	
			$index++;		
			$resultat_aff.= "<a name='[$index]'></a><h1>".$index." - ".$titre."</h1>";	
			$summary.="<a href='#[$index]' class='summary_elt'>".$index." - ".$titre."</a><br />";		
			$nb=0;	
			foreach ($liste as $val) {
			    $resultat_aff.=$val;
			    if(++$nb < count($liste)){			    	
			    	global $notice_separator;
			    	if($notice_separator)$resultat_aff.=$notice_separator;
			    	else $resultat_aff.="<div class='hr'><hr /></div>";
			    }
			}
			$index++;
			$resultat_aff.= "\r\n";
		}
		if($this->document_add_summary){
			$resultat_aff="<a name='summary'></a><div class='summary'><br />".$summary."</div>".$resultat_aff;
		}		
	}
	
	if ($this->typeexport && !$use_limit) {
		$this->export_contenu=cree_export_notices($this->liste_id_notice, start_export::get_id_by_path($this->typeexport), 1,$this->param_export) ;
	}

	// DEBUG 
 	if (false && $this->export_contenu) {
 		$fp = fopen ("$base_path/temp/exp_bannette_".$this->id_bannette.".exp","wb");
	 	fwrite ($fp, $this->export_contenu);
	 	fclose ($fp);
 	}

	return $resultat_aff ;
}

function pmb_ksort(&$table){
	$table_final=array();
	if ($this->field_type == 'list') {
		if (is_array($table)) {
			reset($table);
			$tmp=array();
			$requete = "select ordre, notices_custom_list_lib from notices_custom_lists";
			$requete .= " where notices_custom_champ=".$this->field_id;
			$res = mysql_query($requete);
			while ($row = mysql_fetch_object($res)) {
				$this->group_pperso_order[$row->notices_custom_list_lib] = $row->ordre;
			}
			uksort($table, array(&$this,"cmp_pperso"));
		}
	} else {
		if (is_array($table)) {
			reset($table);
			$tmp=array();
			foreach ($table as $key => $value ) {
	       		$tmp[]=strtoupper(convert_diacrit($key));
	       		$tmp_key[]=$key;
	       		$tmp_contens[]=$value;
			}	
			asort($tmp);	
			foreach ($tmp as $key=>$value ) {
	       		$table_final[$tmp_key[$key]]=$tmp_contens[$key];
			}
			$table=$table_final;
		}
	}		
}

function cmp_pperso($a,$b) {
	if ($this->group_pperso_order[$a]>$this->group_pperso_order[$b]) return 1;
	if ($this->group_pperso_order[$a]<$this->group_pperso_order[$b]) return -1;
	return 0;
	
}
// ---------------------------------------------------------------
//		construit_contenu_HTML() : Pr�paration du contenu du mail ou du bulletin
// ---------------------------------------------------------------
function construit_liens_HTML() {
	global $dbh;
	global $opac_url_base ;
	global $msg ;
	
	// $url_base_opac = $opac_url_base."empr.php?lvl=bannette!!empr_info_login!!";
	$url_base_opac = $opac_url_base."empr.php?lvl=bannette";
	$resultat_aff .= "<style type='text/css'>
		body { 	
		font-size: 10pt;
		font-family: verdana, geneva, helvetica, arial;
		color:#000000;
		background:#FFFFFF;
		}
		td {
		font-size: 10pt;
		font-family: verdana, geneva, helvetica, arial;
		color:#000000;
		}
		th {
		font-size: 10pt;
		font-family: verdana, geneva, helvetica, arial;
		font-weight:bold;
		color:#000000;
		background:#DDDDDD;
		text-align:left;
		}
		hr {
		border:none;
		border-bottom:1px solid #000000;
		}
		h3 {
		font-size: 12pt;
		color:#000000;
		}
		</style>";
	
	$date_today = formatdate(today()) ;
	$public  = "<a href='$url_base_opac&id_bannette=".$this->id_bannette."&code=!!code!!&emprlogin=!!login!!&date_conex=!!date_conex!!'>";
	$public .= $this->comment_public." : &nbsp;".sprintf($msg["print_n_notices"],$this->nb_notices) ;
	$public .= "</a>";

	$entete = str_replace ("!!public!!",$public,$this->entete_mail) ;
	// pour le template de bannette priv�es	
	if ($this->proprio_bannette) {
		$equations = $this->get_equations();
		if ($equations[0]) {
			$equa = new equation($equations[0]);
			$entete = str_replace ("!!equation!!",$equa->nom_equation,$entete) ;	
		}
	} else {
		$entete = str_replace ("!!equation!!","",$entete) ;
	}
	$entete = str_replace ("!!date!!",$date_today,$entete) ;
	
	return $entete ;
	}

// ---------------------------------------------------------------
//		compte_elements() : m�thode pour pouvoir recompter en dehors !
// ---------------------------------------------------------------
function compte_elements() {
	global $dbh ;
	
	$req_nb = "SELECT num_notice from bannette_contenu WHERE num_bannette='".$this->id_bannette."' " ;
	$res_nb = mysql_query($req_nb, $dbh) or die ($req_nb."<br /> in bannette.class.php : ".mysql_error());
	$this->nb_notices = mysql_num_rows($res_nb);
	//initialisation du tableau � chaque fois que cette fonction est appel�e pour �viter un mauvais cumul
	$this->liste_id_notice = array();
	while ($res = mysql_fetch_object($res_nb)) {
		$this->liste_id_notice[]=$res->num_notice ;
	}
	
	$req_nb = "SELECT count(1) as nb_abonnes from bannette_abon WHERE num_bannette='".$this->id_bannette."' " ;
	$res_nb = mysql_query($req_nb, $dbh) or die ($req_nb."<br /> in bannette.class.php : ".mysql_error());
	$res = mysql_fetch_object($res_nb);
	$this->nb_abonnes = $res->nb_abonnes ;
	$requete = "SELECt if(date_last_remplissage>date_last_envoi,1,0) as alert_diff ";
	$requete .= "FROM bannettes WHERE id_bannette='".$this->id_bannette."' " ;
	$result = mysql_query($requete, $dbh) or die ($requete."<br /> in bannette.class.php : ".mysql_error());
	$temp = mysql_fetch_object($result);
	$this->alert_diff = $temp->alert_diff ; 
			
	}
} # fin de d�finition de la classe serie
