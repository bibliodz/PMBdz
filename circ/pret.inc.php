<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pret.inc.php,v 1.105 2014-02-14 09:04:20 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once ("$class_path/emprunteur.class.php");
require_once ("$class_path/serial_display.class.php");
require_once ("$class_path/quotas.class.php");
require_once ("$class_path/comptes.class.php");
require_once("$class_path/audit.class.php");
require_once("$class_path/expl.class.php");
require_once("$class_path/transfert.class.php");
require_once($class_path."/ajax_pret.class.php");
require_once("$class_path/groupexpl.class.php");

// define pour diff�rent flags de situation document
define ('EX_OK', 1);
define ('EX_INCONNU', 2);
define ('HAS_RESA_GOOD', 4); // l'exemplaire est r�serv� pour ce lecteur
define ('NON_PRETABLE', 8);
define ('HAS_NOTE', 16);
define ('HAS_RESA_FALSE', 32); // l'exemplaire est r�serv� pour un autre lecteur
define ('ALREADY_LOANED', 64); // cet emprunteur a d�j� emprunt� ce document
define ('ALREADY_BORROWED', 128); // ce document est emprunt� par un autre emprunteur
define ('HAS_RESA_PLANNED_FALSE', 256); //Les r�servations planifi�es sur le document sont �gales ou sup�rieures au nb d'exemplaires disponibles
define ('IS_TRUSTED',512); //l'exemplaire est monopolis�
define ('IS_GROUP',1024); //l'exemplaire fait parti d'un groupe d'exemplaires 
$affichage = "";
$warning_text='';
$dispo_text='';
$erreur_affichage = "<table border='0' cellpadding='1' width='100%' height='40'><tr><td width='30'>&nbsp;<span></span></td>
		<td width='100%'>&nbsp;</td></tr></table>";

// Confirm pret rfid mode1
if($confirm_pret && $id_empr){
	$expl = new do_pret();
	if(is_array($id_expl)) {
		foreach($id_expl as $id) {
			if($id)$status= $expl->confirm_pret($id_empr, $id,$short_loan);		
		}
	} else {
		if($id_expl)$status = $expl->confirm_pret($id_empr, $id_expl,$short_loan);
	}
	$erreur_affichage = "<hr />
		<div class='row'>
		<div class='colonne10'><img src='./images/info.png' /></div>
		<div class='colonne-suite'><span class='erreur'>".$msg[384]."</span><br />
		";
	if($pmb_play_pret_sound)$alert_sound_list[]="information";	
	$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
	$affichage = $empr -> fiche;
	
}else if (($sub == "pret_annulation") && ($id_expl)) {
	// r�cup�rer la stat ins�r�e pour la supprimer !
	$query = "select pret_arc_id from pret ";
	$query.= "where pret_idexpl = '".$id_expl."' ";
	$result = mysql_query($query, $dbh);
	$stat_id = mysql_fetch_object($result) ;
	$result = mysql_query("delete from pret_archive where arc_id='".$stat_id->pret_arc_id."' ", $dbh);
	audit::delete_audit (AUDIT_PRET, $stat_id->pret_arc_id) ;

	// supprimer le pr�t annul�
	$query = "delete from pret ";
	$query.= "where pret_idexpl = '".$id_expl."' ";
	$result = mysql_query($query, $dbh);
	$erreur_affichage = "<hr />
					<div class='row'>
					<div class='colonne10'><img src='./images/info.png' /></div>
					<div class='colonne-suite'><span class='erreur'>".str_replace('!!cb_expl!!', $cb_doc, $msg[607])."</span></div>
					</div><br />";
	$alert_sound_list[]="information";

	$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
	$affichage = $empr -> fiche;
} else {
	
	$script_magnetique="
<script language='javascript' type='text/javascript'>
var requete = null;

function creerRequette(){
	if(window.XMLHttpRequest) // Firefox
		requete = new XMLHttpRequest();
	else if(window.ActiveXObject) // Internet Explorer
  		requete = new ActiveXObject('Microsoft.XMLHTTP');
	else { // XMLHttpRequest non support� par le navigateur
   		alert('Votre navigateur ne supporte pas les objets XMLHTTPRequest...');
    	return;
	}
}

function magnetise(commande){
	creerRequette();
	if(netscape.security.PrivilegeManager)netscape.security.PrivilegeManager.enablePrivilege('UniversalBrowserRead');	
	requete.open('GET', 'http://localhost:30000/?send_value='+commande+'&command=Send', false);
	requete.send(null);
	if(requete.readyState != 4) alert('Requ�te antivol non effectu�e !');
}

";

	//Si il y a un emprunteur
	if ($id_empr) {
		// V�rification id, on dispose d'un id pour l'emprunteur, donc on est en situation de pr�t
		if (check_empr($id_empr)) {
			$empr_temp = new emprunteur($id_empr, '', FALSE, 1);
			$empr_date_depassee = $empr_temp -> adhesion_depassee();
			//Si adh�sion d�pass�e
			if (!($pmb_pret_adhesion_depassee == 0 && $empr_date_depassee)) {
				//Si un exemplaire ou un code barres a �t� fourni
				if ($cb_doc || $id_expl) {
					if ($id_expl = get_expl_id_from_cb($cb_doc)) {
						
						// Gestion Antivol
						if($pmb_antivol>0) {
							$rqt = "SELECT type_antivol FROM exemplaires WHERE expl_id='".$id_expl."' ";
							$result = mysql_query($rqt, $dbh);		
							$expl = mysql_fetch_object($result);
							$type_antivol =$expl->type_antivol;				
							if($type_antivol ==1)// c'est un support non magn�tique (livre, revue...)
								print "$script_magnetique"."magnetise('DDD');</script>";
							if($type_antivol ==2)//c'est un support magn�tique (cassette)	
								print "$script_magnetique"."magnetise('SSS');</script>";
						}

						//V�rification de la validit� du document
						$statut = check_document($id_expl, $id_empr); 
						// check_document remonte $statut->notice_id et $statut->bulletin_id
						if ($statut->notice_id) {
							$notice_temp = new mono_display($statut->notice_id, 0);
							$titre_prete = $notice_temp->header;
						} elseif ($statut->bulletin_id) {
							$bulletin_temp = new bulletinage_display($statut->bulletin_id);
							$titre_prete = $bulletin_temp->display ;
						} else $titre_prete = "";
						$titre_prete="<b>".$titre_prete."<br />".$cb_doc."</b> $statut->tdoc_libelle $statut->location_libelle $statut->section_libelle <b>$statut->expl_cote</b>";
						
						//Y-a-t-il un quota ?
						if (!$expl_todo && $deflt_docs_location) {		
							$sql = "SELECT expl_retloc FROM exemplaires where expl_retloc='".$deflt_docs_location."' and  expl_id='".$id_expl."' ";
							$req = mysql_query($sql) or die ($msg["err_sql"]."<br />".$sql."<br />".mysql_error());
							$nb = mysql_num_rows($req) ;
							if($nb)	{				
								$erreur_affichage = "<hr />
								<div class='row'>
									<div class='colonne10'><img src='./images/error.png' /></div>
									<div class='colonne-suite'>$titre_prete : <span class='erreur'>".$msg["circ_pret_piege_expl_todo"]."</span><br />";
								$alert_sound_list[]="critique";
								$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
								$erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[389]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr&cb_doc=$cb_doc&expl_todo=1'\" />";	
								$erreur_affichage.= "</div></div><br />";
								$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
								$affichage = $empr -> fiche;
								print pmb_bidi($affichage);
								print alert_sound_script();
								exit();
							} 
						}	
											
						//Y-a-t-il un quota ?
						if (!$quota) {
							$qt=check_quota($id_empr, $id_expl);
							//Si quota viol�
							if (is_array($qt)) {
								$erreur_affichage = "<hr />
								<div class='row'>
									<div class='colonne10'><img src='./images/error.png' /></div>
									<div class='colonne-suite'>$titre_prete : <span class='erreur'>".$qt["MESSAGE"]."</span><br />";
								$alert_sound_list[]="critique";
								$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
								if ($qt["FORCE"]==1) {
									$quota = 1;
									$erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[389]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr&cb_doc=$cb_doc&quota=$quota'\" />";	
								}
								$erreur_affichage.= "</div></div><br />";
								$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
								$affichage = $empr -> fiche;
								print pmb_bidi($affichage);
								print alert_sound_script();
								exit();
							} // fin if (is_array($qt))			
						} // fin if !$quota
									
						// Le lecteur a d�j� emprunt� ce document ?
						if (!$pret_arc && $pmb_pret_already_loaned) {				
							$rqt_arch = "select arc_id from pret_archive WHERE arc_id_empr = '".$id_empr."' AND arc_expl_id = ".$id_expl." ";
							$pretarc_res=mysql_query($rqt_arch, $dbh);
							if(mysql_num_rows($pretarc_res)){
								$pret_arc=1;
								$res_pret_arc = mysql_fetch_object($pretarc_res);
								$resarc_id = $res_pret_arc->resarc_id;
								$erreur_affichage = "<hr />
								<div class='row'>
								<div class='colonne10'><img src='./images/error.png' /></div>
								<div class='colonne-suite'>$titre_prete : <span class='erreur'>".$msg['pret_already_loaned_arch']."</span><br />";
								$alert_sound_list[]="critique";
								$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
	
								$erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[389]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr&cb_doc=$cb_doc&quota=$quota&pret_arc=1'\" />";
		
								$erreur_affichage.= "</div></div><br />";
								$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
								$affichage = $empr -> fiche;
								print pmb_bidi($affichage);
								print alert_sound_script();
								exit();							
							}
						}							
						
						if ($statut -> flag && ((($statut -> flag & HAS_NOTE) || ($statut -> flag & IS_GROUP) || ($statut -> flag & ALREADY_LOANED_ARCHIVE) || ($statut -> flag & NON_PRETABLE) || ($statut -> flag & HAS_RESA_FALSE)) || ($statut -> flag & HAS_RESA_PLANNED_FALSE) || ($statut -> flag & IS_TRUSTED)) && !($statut -> flag & ALREADY_LOANED) && !($statut -> flag & ALREADY_BORROWED) ) {
							if (!$confirm) {
								// mettre ici les routines confirmation								
								if($is_doc_group){
									$information_text.= $groupexpl->get_confirm_form($cb_doc);	
									if($groupexpl->is_doc_header($cb_doc))	$serious = FALSE;	
									else $serious = TRUE;
								}
								// l'exemplaire a une note
								if ($statut -> flag & HAS_NOTE) {
									// l'exemplaire a une note attach�e
									$warning_text.= "$msg[377] : <span class='message_important'>".$statut -> note."</span>&nbsp;";
									$serious = FALSE;
								}
								if ($statut -> flag & NON_PRETABLE) {
									// l'exemplaire a le statut non-pr�table
									if ($warning_text) $warning_text.= "<br />".$msg[382]." (".$statut->statut.")";
										else $warning_text.= $msg[382]." (".$statut->statut.")";
									$serious = TRUE;
									// Si transfert activ�, on v�rifie le pr�t est forcable ou non
									if($pmb_transferts_actif) {
										$transfert = new transfert();
										$statut_trans=$transfert->check_pret($cb_doc);
									
										if($statut_trans==1) {
											//non forcable
											$erreur_affichage = "<hr />
											<div class='row'>
												<div class='colonne10'><img src='./images/error.png' /></div>
												<div class='colonne-suite'>$titre_prete : <span class='erreur'>".$transfert->check_pret_error_message."</span><br />";
											$alert_sound_list[]="critique";
											$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
											
											$erreur_affichage.= "</div></div><br />";
											$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
											$affichage = $empr -> fiche;
											print pmb_bidi($affichage);
											print alert_sound_script();
											exit();
										} elseif($statut_trans==2)	{
											// for�able
											$warning_text.= "<br />".$transfert->check_pret_error_message;
										}	
									}
								}
								if ($statut -> flag & HAS_RESA_FALSE) {
									// le document est r�serv� pour un autre lecteur
									if ($warning_text) $warning_text.= "<br />".$msg[383]." : <a href='./circ.php?categ=pret&form_cb=".rawurlencode($reservataire_empr_cb)."'>".$reservataire_nom_prenom."</a>";
										else $warning_text.= $msg[383]." : <a href='./circ.php?categ=pret&form_cb=".rawurlencode($reservataire_empr_cb)."'>".$reservataire_nom_prenom."</a>";
									$serious = TRUE;
								}
								if ($statut -> flag & HAS_RESA_PLANNED_FALSE) { 
									// le document � des r�servations planifi�es 
									if ($warning_text) $warning_text.= "<br />";
									$warning_text.= "<img src='./images/plus.gif' class='img_plus'
										onClick=\"
										var elt=document.getElementById('erreur-child');
										var vis=elt.style.display;
										if (vis=='block'){
											elt.style.display='none';
											this.src='./images/plus.gif';									
										} else {
											elt.style.display='block';
											this.src='./images/minus.gif';
										}
										\" /> ".htmlentities($msg['resa_planning_encours'], ENT_QUOTES, $charset)." <a href='./circ.php?categ=pret&form_cb=".rawurlencode($reservataire_empr_cb)."'>".$reservataire_nom_prenom."</a><br />";
										
									//Affichage des r�servations sur le document courant
									$q = "SELECT id_resa, resa_idnotice, resa_date, resa_date_debut, resa_date_fin, resa_validee, IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_fin, '".$msg["format_date_sql"]."') as aff_date_fin, ";
									$q.= "resa_idempr, concat(lower(empr_prenom), ' ',upper(empr_nom)) as resa_nom, if(resa_idempr!='".$id_empr."', 0, 1) as resa_same ";
									$q.= "FROM resa_planning left join empr on resa_idempr=id_empr ";
									$q.= "where resa_idnotice in (select expl_notice from exemplaires where expl_cb = '".$cb_doc."') ";
									if ($pmb_location_resa_planning) $q.= "and empr_location in (select expl_location from exemplaires where expl_cb = '".$cb_doc."') ";
									$r = mysql_query($q, $dbh);
									if (mysql_num_rows($r)) {
										$warning_text.= "<div id='erreur-child' class='erreur-child'>";
										while ($resa = mysql_fetch_array($r)) {
											$id_resa = $resa['id_resa'];
											$resa_idempr = $resa['resa_idempr'];
											$resa_idnotice = $resa['resa_idnotice'];
											$resa_date = $resa['resa_date'];
											$resa_date_debut = $resa['resa_date_debut'];
											$resa_date_fin = $resa['resa_date_fin'];
											$resa_validee = $resa['resa_validee'];
											$resa_nom = $resa['resa_nom'];
											$resa_same = $resa['resa_same'];
											if ($resa_idempr==$id_empr) {
												$warning_text.= "<b>".htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;</b>";
											} else {
												$warning_text.= htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;";
											}
											$warning_text.= " &gt;&gt; <b>".$msg['resa_planning_date_debut']."</b> ".formatdate($resa_date_debut)."&nbsp;<b>".$msg['resa_planning_date_fin']."</b> ".formatdate($resa_date_fin)."&nbsp;" ;
											if (!$resa['perimee']) {
												if ($resa['resa_validee'])  $warning_text.= " ".$msg['resa_validee'] ;
													else $warning_text.= " ".$msg['resa_attente_validation']." " ;
											} else  $warning_text.= " ".$msg['resa_overtime']." " ;
											$warning_text.= "<br />" ;
										} //while
										$warning_text.= "</div>";
									} // if (mysql_num_rows($r))	
									$serious = TRUE;
								} //if ($statut -> flag & HAS_RESA_PLANNED_FALSE)	
								if ($statut->flag & IS_TRUSTED ) {
									// le document est monopolis�
									$nd=0;
									if ($statut->notice_id || $statut->bulletin_id) {
										if ($statut->notice_id) {
											$qd = "select count(*) from exemplaires join docs_statut on idstatut=expl_statut and pret_flag=1 where expl_notice=".$statut->notice_id;
										} else if ($statut->bulletin_id) {
											$qd = "select count(*) from exemplaires join docs_statut on idstatut=expl_statut and pret_flag=1 where expl_bulletin=".$statut->bulletin_id;
										}
										$rd = mysql_query($qd,$dbh);
										if (mysql_num_rows($rd)) {
											$nd = mysql_result($rd,0,0);
										}
									}
									$warning_text.= sprintf("<br />".$msg['loan_trust_warning'],$pmb_loan_trust_management,$nd);
									$serious = TRUE;
								}
								$erreur_affichage = "<hr />
									<div class='row' >
									$information_text
									</div>
									<div class='row' >
									<div class='colonne10' ><img src='./images/quest.png' /></div>
									<div class='colonne-suite'>$titre_prete : <span class='erreur' >$warning_text</span><br />";
								
								$alert_sound_list[]="question";
								$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr".(($pmb_short_loan_management==1)?"&short_loan='+document.getElementById('short_loan').value;":"'")."\" />";
								$confirm = $statut -> flag ;
								$erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[389]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr&cb_doc=$cb_doc&confirm=$confirm&quota=$quota&pret_arc=$pret_arc".(($pmb_short_loan_management==1)?"&short_loan='+document.getElementById('short_loan').value;":"'")."\" />";
								$erreur_affichage.= "&nbsp;<input class='bouton' type='button' value=\"".$msg[375]."\" onClick=\"document.location='circ.php?categ=visu_ex&form_cb_expl=".$cb_doc."';\" />";
								$erreur_affichage.= "</div></div><br />";
								$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
								$affichage = $empr -> fiche;
							} else { // else if !confirm
								// il y a eu confirmation du pr�t
								if ($statut -> flag == $confirm) {
									// ajout du pr�t
									// si transfert activ�, faire le n��essaire en cas de for�age
									if($pmb_transferts_actif) {
										$transfert = new transfert();
										$statut_trans=$transfert->check_pret($cb_doc,1);								
									}
									if ($statut -> flag & HAS_RESA_GOOD) {
										// archivage resa
										$rqt_arch = "UPDATE resa_archive, resa SET resarc_pretee = 1 WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";	
										mysql_query($rqt_arch, $dbh);
										$rqt_arch = "select resarc_id from resa_archive, resa WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";	
										$resarc_res=mysql_query($rqt_arch, $dbh);
										$resarc = mysql_fetch_object($resarc_res);
										$resarc_id = $resarc->resarc_id;	
										
										// suppression de la resa pour ce lecteur
										del_resa($id_empr, $statut -> idnotice, $statut -> idbulletin, $statut -> expl_cb);
									}
									if ($statut -> flag & HAS_RESA_FALSE) {
										// d�valider la resa correspondante
										if ($statut->resa_cb == $statut->expl_cb) {
											// la r�sa prioritaire avait d�j� un CB identique : il suffit de la d�valider
											$rqt_invalide_resa = "update resa set resa_date_debut='0000-00-00', resa_date_fin='0000-00-00', resa_cb='' where id_resa = '".$statut->id_resa."' " ;  
											$truc_vide = mysql_query ($rqt_invalide_resa, $dbh) ;
										} // sinon rien � faire, la r�sa �tait valid�e avec autre chose, elle le reste
										// archivage resa
										$rqt_arch = "UPDATE resa_archive, resa SET resarc_pretee = 2 WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";	
										mysql_query($rqt_arch, $dbh);	
										$rqt_arch = "select resarc_id from resa_archive, resa WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";	
										$resarc_res=mysql_query($rqt_arch, $dbh);
										$resarc = mysql_fetch_object($resarc_res);
										$resarc_id = $resarc->resarc_id;										
										del_resa($id_empr, $statut -> idnotice, $statut -> idbulletin, $statut -> expl_cb);
									}
									del_resa($id_empr, $statut -> idnotice, $statut -> idbulletin, $statut -> expl_cb);
									add_pret($id_empr, $id_expl, $cb_doc, $resarc_id, $short_loan);
									// mise � jour de l'affichage
									// ER ici ajout du bouton d'annulation violente 
									/*
									$rqt = "SELECT notice_m.tit1, notices_s.tit1 ";
									$rqt.= "FROM ((exemplaires LEFT JOIN notices AS notice_m ON expl_notice = notice_m.notice_id) LEFT JOIN bulletins ON expl_bulletin = bulletin_id) LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id ";
									$rqt.= "WHERE expl_id='".$id_expl."' ";

									$result_pret = mysql_query($rqt, $dbh);
									$titre_prete = mysql_result($result_pret, 0, 0).mysql_result($result_pret, 0, 1);
									*/
									if($pmb_pret_groupement){
										if($id_group=groupexpls::get_group_expl($cb_doc)){
											// ce document appartient � un groupe
											$is_doc_group=1;
											$groupexpl=new groupexpl($id_group);$information_text.= $groupexpl->get_confirm_form($cb_doc);
											$information_group= $groupexpl->get_confirm_form($cb_doc);
											//	$statut->flag+=IS_GROUP; client ne veut pas de comfirmation
										}
									}
									$erreur_affichage = $information_group."<hr />
										<div class='row'>
										<div class='colonne10'><img src='./images/info.png' /></div>
										<div class='colonne-suite'>".$titre_prete." : <span class='erreur'>".$msg[384]."</span><br />
										";
									$alert_sound_list[]="information";
									$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='circ.php?categ=pret&sub=pret_annulation&id_empr=".$id_empr."&id_expl=".$id_expl."&cb_doc=".$cb_doc."&short_loan=".$short_loan."'\" />";
									$erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[1300]}' onclick=\"openPopUp('./pdf.php?pdfdoc=ticket_pret&cb_doc=$cb_doc&id_empr=$id_empr', 'ticket', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\" />";
									$erreur_affichage.= "</div></div>";
									if ($statut->expl_comment) $erreur_affichage.= "<div class='expl_comment'>".$statut->expl_comment."</div>";

									$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
									$affichage = $empr -> fiche;

									// prise en compte du param d'envoi de ticket de pr�t �lectronique 
									if ($empr_electronic_loan_ticket && $param_popup_ticket) {
										electronic_ticket($id_empr, $cb_doc); 
									}
									
									// prise en compte du param popup_ticket 
									if ($param_popup_ticket == 1) {
										if(!$pmb_printer_ticket_url) {
											print "<script type='text/javascript'>openPopUp('./pdf.php?pdfdoc=ticket_pret&cb_doc=$cb_doc&id_empr=$id_empr', 'ticket', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes');</script>";
										} else {
											$affichage.="<script type='text/javascript'>print_ticket('./ajax.php?module=circ&categ=print_pret&sub=one&id_empr=".$id_empr."&id_expl=".$id_expl."&cb_doc=$cb_doc');</script>";
										}												
									}
								} else {
									$erreur_affichage = "<hr />
										<div class='row'>
										<div class='colonne10'><img src='./images/info.png' /></div>
										<div class='colonne-suite'>$titre_prete : <span class='erreur'>$msg[384]</span></div>
										</div><br />";
									$alert_sound_list[]="information";

									$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
									$affichage = $empr -> fiche;
								} // fin else if ($statut -> flag == $confirm) 
							} // fin if else !confirm
						} else {
							if ($statut -> flag & ALREADY_LOANED || $statut -> flag & ALREADY_BORROWED) {
								if ($statut -> flag & ALREADY_LOANED) {
									$erreur_affichage = "<hr />
									<div class='row'>
									<div class='colonne10'><img src='./images/error.png' /></div>
									<div class='colonne-suite'>$titre_prete : <span class='erreur'>$msg[386]</span></div>
									</div><br />";
									$alert_sound_list[]="critique";
								}
								if ($statut -> flag & ALREADY_BORROWED) {
									$erreur_affichage = "<hr />
									<div class='row'>
									<div class='colonne10'><img src='./images/error.png' /></div>
									<div class='colonne-suite'>$titre_prete : <span class='erreur'>$msg[387]</span></div>
									<input class='bouton' type='button' value=\"".$msg[375]."\" onClick=\"document.location='circ.php?categ=visu_ex&form_cb_expl=$cb_doc';\" />
									</div><br />";
									$alert_sound_list[]="critique";
								}
								$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
								$affichage = $empr -> fiche;
							} else {
									if ($statut -> flag && ($statut -> flag & HAS_RESA_GOOD)) {
										// archivage resa
										$rqt_arch = "UPDATE resa_archive, resa SET resarc_pretee = 1 WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";		
										mysql_query($rqt_arch, $dbh);	
										$rqt_arch = "select resarc_id from resa_archive, resa WHERE id_resa = '".$statut->id_resa."' AND resa_arc = resarc_id ";	
										$resarc_res=mysql_query($rqt_arch, $dbh);
										$resarc = mysql_fetch_object($resarc_res);
										$resarc_id = $resarc->resarc_id;								
										// suppression de la resa pour ce lecteur
										del_resa($id_empr, $statut -> idnotice, $statut -> idbulletin, $statut -> expl_cb);
									}
									// ajout du pr�t
									del_resa($id_empr, $statut -> idnotice, $statut -> idbulletin, $statut -> expl_cb);
									add_pret($id_empr, $id_expl, $cb_doc,$resarc_id,$short_loan);
									// mise � jour de l'affichage
									if($pmb_pret_groupement){
										if($id_group=groupexpls::get_group_expl($cb_doc)){
											// ce document appartient � un groupe
											$is_doc_group=1;
											$groupexpl=new groupexpl($id_group);$information_text.= $groupexpl->get_confirm_form($cb_doc);
											$information_group= $groupexpl->get_confirm_form($cb_doc);
											//	$statut->flag+=IS_GROUP; client ne veut pas de comfirmation
										}
									}
									// ajout du bouton d'annulation violente
									$erreur_affichage = $information_group."<hr />
										<div class='row'>
										<div class='colonne10'><img src='./images/info.png' /></div>
										<div class='colonne-suite'>$titre_prete : <span class='erreur'>".$msg[384]."</span><br />
										";
									if($pmb_play_pret_sound)$alert_sound_list[]="information";
									$erreur_affichage.= "<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='circ.php?categ=pret&sub=pret_annulation&id_empr=".$id_empr."&id_expl=".$id_expl."&cb_doc=".$cb_doc."&short_loan=".$short_loan."'\" />";
									
									if($pmb_printer_ticket_url) $erreur_affichage.="&nbsp;<a href='#' onclick=\"print_ticket('./ajax.php?module=circ&categ=print_pret&sub=one&id_empr=".$id_empr."&id_expl=".$id_expl."&cb_doc=$cb_doc'); return false;\"><img src='./images/print.gif' alt='Imprimer...' title='Imprimer...' align='middle' border='0'></a>";
									else $erreur_affichage.= "&nbsp;<input type='button' class='bouton' value='${msg[1300]}' onclick=\"openPopUp('./pdf.php?pdfdoc=ticket_pret&cb_doc=$cb_doc&id_empr=$id_empr', 'ticket', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\" />";
									$erreur_affichage.= "</div></div>";
									if ($statut->expl_comment) $erreur_affichage.= "<div class='expl_comment'>".$statut->expl_comment."</div>";

									$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
									$affichage = $empr -> fiche;
									// prise en compte du param d'envoi de ticket de pr�t �lectronique 
									if ($empr_electronic_loan_ticket && $param_popup_ticket) {
										electronic_ticket($id_empr, $cb_doc); 
									}
										
									// prise en compte du param popup_ticket
									if ($param_popup_ticket == 1)
										if(!$pmb_printer_ticket_url) 
											print "<script type='text/javascript'>openPopUp('./pdf.php?pdfdoc=ticket_pret&cb_doc=$cb_doc&id_empr=$id_empr', 'ticket', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes');</script>";
										else 
											$affichage.= "<script type='text/javascript'>print_ticket('./ajax.php?module=circ&categ=print_pret&sub=one&id_empr=".$id_empr."&id_expl=".$id_expl."&cb_doc=$cb_doc');</script>";	
									} // fin else if ($statut -> flag & ALREADY_LOANED || $statut -> flag & ALREADY_BORROWED) {
						} // fin de quoi ???
					} else { // pas d'exemplaire avec ce code-barre
						$erreur_affichage = "<hr />
						<div class='row'>
						<div class='colonne10'><img src='./images/error.png' /></div>
						<div class='colonne-suite'><b>$cb_doc</b> : <span class='erreur'>$msg[367]</span></div>
						</div><br />";
						// on a un code-barres, est-ce un cb empr ?
						$query_empr = "select id_empr, empr_cb from empr where empr_cb='".$cb_doc."' ";
						$result_empr = mysql_query($query_empr, $dbh);
						if(mysql_num_rows($result_empr)) {
							$erreur_affichage.="<script type=\"text/javascript\">document.location='./circ.php?categ=pret&form_cb=$cb_doc'</script>";
							}
						$alert_sound_list[]="critique";

						$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
						$affichage = $empr -> fiche;
					}
				} else { // aucun $id_expl ni de $cd_doc
					$erreur_affichage = "<hr />
					<div class='row'>
					<div class='colonne10'></div>
					<div class='colonne-suite'><span class='erreur'></span></div>
					</div><br />";
					$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
					$affichage = $empr -> fiche;
				}
			} else { // date adh�sion d�pass�e et ici on bloque !!!
				$erreur_affichage = "<hr />
				<div class='row'>
				<div class='colonne10'></div>
				<div class='colonne-suite'><span class='erreur'>$msg[pret_impossible_adhesion]</span></div>
				</div><br />";
				$empr = new emprunteur($id_empr, $erreur_affichage, FALSE, 1);
				$affichage = $empr -> fiche;
			}
		} else {
			// afficher 'lecteur inconnu'
			$erreur_affichage = "<hr />
			<div class='row'>
			<div class='colonne10'><img src='./images/error.png' /></div>
			<div class='colonne-suite'><span class='erreur'>$msg[388]</span></div>
			</div><br />";
			$alert_sound_list[]="critique";
			print $erreur_affichage;
		}

	} else { // pas d'idempr

		$query = "select id_empr as id from empr where empr_cb='$form_cb' ";
		$result = mysql_query($query, $dbh);
		$id = @ mysql_result($result, '0', 'id');
		if (($id) && ($form_cb)) {
			$erreur_affichage = "<hr />
			<div class='row'>
			<div class='colonne10'></div>
			<div class='colonne-suite'><span class='erreur'></span></div>
			</div><br />";
			if ($id_notice) {
				if ($type_resa)
					echo "<script> parent.location.href='./circ.php?categ=resa_planning&resa_action=add_resa&id_empr=$id&groupID=$groupID&id_notice=$id_notice'; </script>";
				else
					echo "<script> parent.location.href='./circ.php?categ=resa&id_empr=$id&groupID=$groupID&id_notice=$id_notice'; </script>";					
			} elseif($id_bulletin) {
				echo "<script> parent.location.href='./circ.php?categ=resa&id_empr=$id&groupID=$groupID&id_bulletin=$id_bulletin'; </script>";
			} else {
				$empr = new emprunteur($id, $erreur_affichage, FALSE, 1);
				$affichage = $empr -> fiche;
			}
		} else {
			include ('./circ/empr/empr_list.inc.php');
		}
	} /* fin if else ajout� par ER pour fonction annulation */
}
//Comme dans $affichage on met la fiche de l'emprunteur ($affichage = $empr -> fiche) � aucun moment !!voir_sugg!! ne peut �tre encore pr�sent
if(SESSrights & ACQUISITION_AUTH){
	global $nb_per_page;
	$ori = ($id_empr ? $id_empr : $id);
	$req = "select count(id_suggestion) as nb from suggestions, suggestions_origine where num_suggestion=id_suggestion and origine='".$ori."' and type_origine='1'  ";
	$res=mysql_query($req,$dbh);
	$btn_sug = "";
	$sug = mysql_fetch_object($res);
	if($sug->nb){
		$btn_sug = "<input type='button' class='bouton' id='see_sug' name='see_sug' value='".$msg['acquisition_lecteur_see_sugg']."' onclick=\"document.location='./acquisition.php?categ=sug&action=list&user_id=$ori&user_statut=1' \" />";
	} 
	$affichage = str_replace('!!voir_sugg!!',$btn_sug,$affichage);
}else{
	$affichage = str_replace('!!voir_sugg!!',"",$affichage);
}
//print $erreur_affichage ;
print pmb_bidi($affichage);

// <-------------- do_confirm_box() ------------>
// fabrique une boite de confirmation pour la fiche lecteur
function do_confirm_box($id_empr, $id_expl, $cb_doc, $message, $confirm_flag) {
	global $confirm;
	global $msg;
	global $alert_sound_list;// l'utilisateur veut-il les sons d'alerte
	global $quota,$pret_arc;
	
	$alert_sound_list[]="question";
	
	$warning.= "<table border='0' cellpadding='3' width='100%'>
			<tr>
				<td>
					<span><img src='./images/error.gif' />document $cb_doc</span>
				</td>
			</tr>
			<tr>
				<td>
					$message
				</td>
			</tr>
			<tr>
				<td>
					<input type='button' class='bouton' value='${msg[76]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
	$confirm = $confirm + $confirm_flag;
	$warning.= "&nbsp;
					<input type='button' class='bouton' value='${msg[389]}' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr&cb_doc=$cb_doc&confirm=$confirm&quota=$quota&pret_arc=$pret_arc'\" />
				</td>
			</tr>
			</table>";
	return $warning;
}

// <-------------- check_empr --------------->
// teste l'id_empr pass�e en param�tre (check si l'emprunteur existe)
function check_empr($id) {
	global $dbh;
	if (!$id)
		return FALSE;
	$query = "select count(1) as qte from empr where id_empr='$id' ";
	$result = mysql_query($query, $dbh);
	return mysql_result($result, 0, 0);

}

// <------------- check_quota --------------->
//V�rifie les quotas de pr�t si activ�s
function check_quota($id_empr, $id_expl) {
	global $msg;
	global $pmb_quotas_avances, $pmb_short_loan_management, $short_loan;
	
	if ($pmb_quotas_avances) {
		//Initialisation des quotas pour nombre de documents pr�tables
		if ($pmb_short_loan_management && $short_loan) {
			$qt = new quota("SHORT_LOAN_NMBR_QUOTA");
		} else {
			$qt = new quota("LEND_NMBR_QUOTA");
		}//Tableau de passage des param�tres
		$struct["READER"] = $id_empr;
		$struct["EXPL"] = $id_expl;
		//Test du quota pour l'exemplaire et l'emprunteur
		if ($qt -> check_quota($struct)) {
			//Si erreur, r�cup�ration du message et peut-on forcer ou non ?
			$error["MESSAGE"] = $qt -> error_message;
			$error["FORCE"] = $qt -> force;
		} else
			$error = "";
	}
	return $error;
}

// <-------------- get_expl_id_from_cb() --------------->
// r�cup�re l'id d'un exemplaire d'apr�s son code barre
function get_expl_id_from_cb($cb) {
	global $dbh;
	if (!$cb)
		return FALSE;
	$query = "select expl_id as id from exemplaires where expl_cb='$cb' limit 1";
	$result = mysql_query($query, $dbh);
	return @ mysql_result($result, '0', 'id');

}

// <-------------- check_document() --------------->
// r�cup�re diff�rents param�tres sur le document � emprunter
/* ce qui nous int�resse :
- si le document est inconnu : on ne fait rien bien entendu -> retour EX_INCONNU
- si le document est d�ja en pr�t -> allready_BORROWED
- si l'exemplaire a une note -> l'utilisateur doit confirmer le pr�t (HAS_NOTE)
- si le document est en consultation sur place -> l'utilisateur doit confirmer le pr�t retour SUR_PLACE
- si le document est r�serv� pour un autre lecteur -> l'utilisateur doit confirmer le pr�t retour HAS_RESA
- si le document est r�serv� pour ce lecteur -> on efface la r�servation et on retourne EX_OK 

- si des r�servations sont planifi�es pour un exemplaire du document :
	nb exemplaires r�serv�s > nb exemplaires dispos >> ok
	nb exemplaires r�serv�s <= nb exemplaires dispos >> on affiche les r�sas planifi�es
*/


function check_document($id_expl, $id_empr) {

	global $dbh;
	global $pmb_resa_planning;
	global $empr_archivage_prets, $pmb_loan_trust_management;
	$retour = new stdClass();
	$retour -> flag = 0;

	if (!$id_expl || !$id_empr)
		return $retour -> flag;

	// on tente de r�cup�rer les infos exemplaire utiles
	$query = "select expl_cote, location_libelle, section_libelle, tdoc_libelle, e.expl_cb as cb, e.expl_id as id, e.expl_location, s.pret_flag as pretable, s.statut_allow_resa as reservable, e.expl_notice as notice, e.expl_bulletin as bulletin, e.expl_note as note, expl_comment, s.statut_libelle as statut";
	$query.= " from exemplaires e, docs_statut s, docs_location l, docs_section sec, docs_type t";
	$query.= " where e.expl_id=$id_expl";
	$query.= " and s.idstatut=e.expl_statut";
	$query.= " and sec.idsection=e.expl_section";
	$query.= " and l.idlocation=e.expl_location";
	$query.= " and t.idtyp_doc =e.expl_typdoc";
	$query.= " limit 1";
	$result = mysql_query($query, $dbh);

	// exemplaire inconnu
	if (!mysql_num_rows($result)) {
		$retour -> flag = EX_INCONNU;
		return $retour;
	}
	$expl = mysql_fetch_object($result);

	$retour -> expl_cb = $expl -> cb;
	$retour -> notice_id = $expl -> notice;
	$retour -> bulletin_id = $expl -> bulletin;
	$retour -> expl_cote = $expl -> expl_cote;
	$retour -> tdoc_libelle = $expl -> tdoc_libelle;
	$retour -> location_libelle = $expl -> location_libelle;
	$retour -> section_libelle = $expl -> section_libelle;
	$retour -> expl_comment = $expl -> expl_comment;
	$retour->reservable=$expl->reservable;
	// une autre query pour savoir si l'exemplaire est en pr�t...
	$query = "select pret_idempr from pret where pret_idexpl=$id_expl limit 1";
	$result = mysql_query($query, $dbh);
	if (@ mysql_num_rows($result)) {
		// l'exemplaire est d�j� en pr�t
		$empr = mysql_result($result, '0', 'pret_idempr');
		// l'emprunteur est l'emprunteur actuel
		if ($empr == $id_empr) $retour -> flag += ALREADY_LOANED;
			else $retour -> flag += ALREADY_BORROWED;
	}

	// cas de l'exemplaire qui a une note
	if ($expl -> note) {
		$retour -> flag += HAS_NOTE;
		$retour -> note = $expl -> note;
	}

	// cas de l'exemplaire en consultation sur place
	if (!$expl -> pretable) {
		// l'exemplaire est en consultation sur place
		$retour -> flag += NON_PRETABLE;
		if (!$retour -> note) $retour -> note = $expl -> statut;
			else $retour -> note = $retour -> note." / ".$expl -> statut;
	}

	// cas des r�servations
	// on checke si l'exemplaire a une r�servation
	$query = "select resa_idempr as empr, id_resa, resa_cb, concat(ifnull(concat(empr_nom,' '),''),empr_prenom) as nom_prenom, empr_cb from resa left join empr on resa_idempr=id_empr where resa_idnotice='$expl->notice' and resa_idbulletin='$expl->bulletin' order by resa_date limit 1";
	$result = mysql_query($query, $dbh);
	if (mysql_num_rows($result)) {
		$reservataire = mysql_result($result, 0, 'empr');
		$id_resa = mysql_result($result, 0, 'id_resa');
		$resa_cb = mysql_result($result, 0, 'resa_cb');
		$nom_prenom = mysql_result($result, 0, 'nom_prenom');
		$empr_cb = mysql_result($result, 0, 'empr_cb');
		$retour -> idnotice = $expl -> notice;
		$retour -> idbulletin = $expl -> bulletin;
		$retour -> id_resa = $id_resa ;
		$retour -> resa_cb = $resa_cb ;
		if ($reservataire == $id_empr) {
			// la r�servation est pour ce lecteur
			$retour -> flag += HAS_RESA_GOOD;
		} else {
			if ($expl->cb==$resa_cb) // r�serv� (valid�) pour un autre lecteur
			$retour -> flag += HAS_RESA_FALSE;
			global $reservataire_nom_prenom ;
			global $reservataire_empr_cb ;
			$reservataire_nom_prenom = $nom_prenom ; 
			$reservataire_empr_cb = $empr_cb ; 
		}
	}

	// cas des r�servations planifi�es		
	if($pmb_resa_planning) {		
		global $pmb_location_resa_planning;
		
		// On compte les r�servations planifi�es sur ce document � des dates ult�rieures
		$q = "select resa_idempr as empr, id_resa, concat(ifnull(concat(empr_nom,' '),''),empr_prenom) as nom_prenom ";
		$q.= "from resa_planning left join empr on resa_idempr=id_empr ";
		$q.= "where resa_idnotice = '".$expl->notice."' ";
		if ($pmb_location_resa_planning) $q.= "and empr_location='".$expl->expl_location."' ";
		$q.= "and resa_date_debut >= curdate() ";
		$q.= "order by resa_date_debut ";
		$r = mysql_query($q, $dbh);
		$nb_resa = mysql_num_rows($r); 

		// On compte les exemplaires disponibles
		$q = "select count(1) ";
		$q.= "from exemplaires left join pret on expl_notice = pret_idexpl ";
		$q.= "and pret_idexpl is null ";
		$q.= "where expl_notice = '".$expl->notice."' ";
		if ($pmb_location_resa_planning) $q.= "and expl_location='".$expl->expl_location."'";
		$r = mysql_query($q, $dbh);
		$nb_dispo = mysql_result($r, 0, 0);

		//$retour -> idnotice = $expl -> notice;

		if (($nb_dispo-$nb_resa) <= 0 ) { 
			$retour -> flag += HAS_RESA_PLANNED_FALSE;
		}
	}
	
	//cas du non monopole
	if ($pmb_loan_trust_management) {
		$np=0;
		$npa=0;
		$qp = "select count(*) from pret join exemplaires on pret_idexpl=expl_id where pret_idempr='".$id_empr."' ";
		$qp.= (($expl->notice)?"and expl_notice='".$expl->notice."' ":"and expl_bulletin='".$expl->bulletin."' ");
		$rp = mysql_query($qp, $dbh);
		$np=mysql_result($rp,0,0);
		if($empr_archivage_prets) { 
			$qpa = "select count(*) from pret_archive where arc_id_empr='".$id_empr."' ";
			$qpa.= (($expl->notice)?"and arc_expl_notice='".$expl->notice."' ":"and arc_expl_bulletin='".$expl->bulletin."' ");
			$qpa.= "and date_add(arc_fin, interval ".$pmb_loan_trust_management." day) >= now()";
			$rpa = mysql_query($qpa, $dbh);
			$npa=mysql_result($rpa,0,0);
		}
		if ($np || $npa) { 
			$retour -> flag += IS_TRUSTED;
		}
	}
	
	return $retour;
}


// ajoute le pr�t en table
function add_pret($id_empr, $id_expl, $cb_doc,$resarc_id=0,$short_loan=0) {
	// le lien MySQL
	global $dbh, $msg;
	global $pmb_quotas_avances, $pmb_utiliser_calendrier;
	global $pmb_gestion_financiere,$pmb_gestion_tarif_prets;
	global $include_path,$lang;
	global $deflt2docs_location ;
	global $pmb_pret_date_retour_adhesion_depassee;
	global $pmb_short_loan_management;
	global $pmb_transferts_actif;
	/* on pr�pare la date de d�but*/
	
	$pret_date = today();

	/* on cherche la dur�e du pr�t */
	if ($pmb_short_loan_management && $short_loan) {
		if($pmb_quotas_avances) {
			//Initialisation de la classe
			$qt=new quota("SHORT_LOAN_TIME_QUOTA");
			$struct["READER"]=$id_empr;
			$struct["EXPL"]=$id_expl;
			$duree_pret=$qt->get_quota_value($struct);
			if ($duree_pret==-1) $duree_pret=0; 
		} else {
				$query = "SELECT short_loan_duration as duree_pret";
				$query.= " FROM exemplaires, docs_type";
				$query.= " WHERE expl_id='".$id_expl;
				$query.= "' and idtyp_doc=expl_typdoc LIMIT 1";
				$result = @ mysql_query($query, $dbh) or die("can't SELECT exemplaires ".$query);
				$expl_properties = mysql_fetch_object($result);
				$duree_pret = $expl_properties -> duree_pret;
		} 	
	} else {
		if($pmb_quotas_avances) {
			//Initialisation de la classe
			$qt=new quota("LEND_TIME_QUOTA");
			$struct["READER"]=$id_empr;
			$struct["EXPL"]=$id_expl;
			$duree_pret=$qt->get_quota_value($struct);
			if ($duree_pret==-1) $duree_pret=0; 
		} else {
				$query = "SELECT duree_pret";
				$query.= " FROM exemplaires, docs_type";
				$query.= " WHERE expl_id='".$id_expl;
				$query.= "' and idtyp_doc=expl_typdoc LIMIT 1";
				$result = @ mysql_query($query, $dbh) or die("can't SELECT exemplaires ".$query);
				$expl_properties = mysql_fetch_object($result);
				$duree_pret = $expl_properties -> duree_pret;
		} 
	}	
	// calculer la date de retour pr�vue, tenir compte de la date de fin d'adh�sion
	if (!$duree_pret) $duree_pret="0" ; 
	if($pmb_pret_date_retour_adhesion_depassee) {
		$rqt_date = "select empr_date_expiration,if(empr_date_expiration>date_add('".$pret_date."', INTERVAL '$duree_pret' DAY),0,1) as pret_depasse_adhes, date_add('".$pret_date."', INTERVAL '$duree_pret' DAY) as date_retour from empr where id_empr='".$id_empr."'";
	} else {
		$rqt_date = "select empr_date_expiration,if(empr_date_expiration>date_add('".$pret_date."', INTERVAL '$duree_pret' DAY),0,1) as pret_depasse_adhes, if(empr_date_expiration>date_add('".$pret_date."', INTERVAL '$duree_pret' DAY),date_add('".$pret_date."', INTERVAL '$duree_pret' DAY),empr_date_expiration) as date_retour from empr where id_empr='".$id_empr."'";
	}
	$resultatdate = mysql_query($rqt_date) or die(mysql_error()."<br /><br />$rqt_date<br /><br />");
	$res = mysql_fetch_object($resultatdate) ;
	$date_retour = $res->date_retour ;
	$pret_depasse_adhes = $res->pret_depasse_adhes ;
	$empr_date_expiration= $res->empr_date_expiration;
	
	if ($pmb_utiliser_calendrier) {
		if (($pret_depasse_adhes==0) || $pmb_pret_date_retour_adhesion_depassee) {
			$rqt_date = "select date_ouverture from ouvertures where ouvert=1 and to_days(date_ouverture)>=to_days('$date_retour') and num_location=$deflt2docs_location order by date_ouverture ";
			$resultatdate=mysql_query($rqt_date);
			$res=@mysql_fetch_object($resultatdate) ;
			if ($res->date_ouverture) $date_retour=$res->date_ouverture ;
		} else {
			$rqt_date = "select date_ouverture from ouvertures where date_ouverture>=sysdate() and ouvert=1 and to_days(date_ouverture)<=to_days('$date_retour') and num_location=$deflt2docs_location order by date_ouverture DESC";
			$resultatdate=mysql_query($rqt_date);
			$res=@mysql_fetch_object($resultatdate) ;
			if ($res->date_ouverture) $date_retour=$res->date_ouverture ;
		}
		// Si la date_retour, calcul�e ci-dessus d'apr�s le calendrier, d�passe l'adh�sion, alors que c'est interdit,
		// la date de retour doit etre le dernier jour ouvert
		if(!$pmb_pret_date_retour_adhesion_depassee){
			$rqt_date = "SELECT DATEDIFF('$empr_date_expiration','$date_retour')as diff";
			$resultatdate=mysql_query($rqt_date);
			$res=@mysql_fetch_object($resultatdate) ;
			if ($res->diff<0) {
				$rqt_date = "select date_ouverture from ouvertures where date_ouverture>=sysdate() and ouvert=1 and to_days(date_ouverture)<=to_days('$empr_date_expiration') and num_location=$deflt2docs_location order by date_ouverture DESC";
				$resultatdate=mysql_query($rqt_date);
				$res=@mysql_fetch_object($resultatdate) ;
				if ($res->date_ouverture) $date_retour=$res->date_ouverture ;									
			}
		}	
	} 
	
	// ins�rer le pr�t 
	$query = "INSERT INTO pret SET ";
	$query.= "pret_idempr = '".$id_empr."', ";
	$query.= "pret_idexpl = '".$id_expl."', ";
	$query.= "pret_date   = sysdate(), ";
	$query.= "pret_retour = '$date_retour', ";
	$query.= "retour_initial = '$date_retour', ";
	$query.= "short_loan_flag = ".(($pmb_short_loan_management && $short_loan)?"'1'":"'0'");
	$result = @ mysql_query($query, $dbh) or die(mysql_error()."<br />can't INSERT into pret".$query);

	// ins�rer la trace en stat, r�cup�rer l'id et le mettre dans la table des pr�ts pour la maj ult�rieure
	$stat_avant_pret = pret_construit_infos_stat ($id_expl) ;
	$stat_id = stat_stuff ($stat_avant_pret) ;
	$query = "update pret SET pret_arc_id='$stat_id' where ";
	$query.= "pret_idempr = '".$id_empr."' and ";
	$query.= "pret_idexpl = '".$id_expl."' ";
	$result = @ mysql_query($query, $dbh) or die("can't update pret for stats ".$query);
	audit::insert_creation (AUDIT_PRET, $stat_id) ;
	
	if($resarc_id){
		$rqt_arch = "UPDATE resa_archive SET resarc_arcpretid = $stat_id WHERE resarc_id = '".$resarc_id."' ";	
		@ mysql_query($rqt_arch, $dbh);
	}	
	$query = "update exemplaires SET ";
	$query.= "last_loan_date = sysdate() ";
	$query.= "where expl_id= '".$id_expl."' ";
	$result = @ mysql_query($query, $dbh) or die("can't update last_loan_date in exemplaires : ".$query);

	$query = "update exemplaires SET ";
	$query.= "expl_retloc=0 ";
	$query.= "where expl_id= '".$id_expl."' ";
	$result = @ mysql_query($query, $dbh) or die("can't update expl_retloc in exemplaires : ".$query);
	
	$query = "update empr SET ";
	$query.= "last_loan_date = sysdate() ";
	$query.= "where id_empr= '".$id_empr."' ";
	$result = @ mysql_query($query, $dbh) or die("can't update last_loan_date in empr : ".$query);
	
	$query = "delete from resa_ranger ";
	$query .= "where resa_cb='".$cb_doc."'";
	$result = @ mysql_query($query, $dbh) or die("can't delete cb_doc in resa_ranger : ".$query);	
	

	//D�bit du compte lecteur si n�cessaire
	if (($pmb_gestion_financiere)&&($pmb_gestion_tarif_prets)) {
		$tarif_pret=0;
		switch ($pmb_gestion_tarif_prets) {
			case 1:
				//Gestion simple
				$query = "SELECT tarif_pret";
				$query.= " FROM exemplaires, docs_type";
				$query.= " WHERE expl_id='".$id_expl;
				$query.= "' and idtyp_doc=expl_typdoc LIMIT 1";	
				
				$result = @ mysql_query($query, $dbh) or die("can't SELECT exemplaires ".$query);
				$expl_tarif = mysql_fetch_object($result);
				$tarif_pret = $expl_tarif -> tarif_pret;
				
				break;
			case 2:
				//Gestion avanc�e
				//Initialisation Quotas
				global $_parsed_quotas_;
				$_parsed_quotas_=false;
				$qt_tarif=new quota("COST_LEND_QUOTA","$include_path/quotas/own/$lang/finances.xml");
				$struct["READER"]=$id_empr;
				$struct["EXPL"]=$id_expl;
				$tarif_pret=$qt_tarif->get_quota_value($struct);
				break;
		}
		$tarif_pret=$tarif_pret*1;
		if ($tarif_pret) {
			$compte_id=comptes::get_compte_id_from_empr($id_empr,3);
			if ($compte_id) {
				$cpte=new comptes($compte_id);
				$explaire = new exemplaire('',$id_expl);
				
				if($explaire->id_notice == 0 && $explaire->id_bulletin){
					//C'est un exemplaire de bulletin
					$bulletin = new bulletinage_display($explaire->id_bulletin);
					$titre = strip_tags($bulletin->display);	
				} elseif($explaire->id_notice) {
					$notice = new mono_display($explaire->id_notice);
					$titre = strip_tags($notice->header);
				}								
				$libelle_expl = (strlen($titre)>15)?$explaire->cb." ".$titre:$explaire->cb." ".$titre;				
				$cpte->record_transaction("",abs($tarif_pret),-1,sprintf($msg["finance_pret_expl"],$libelle_expl),0);
			}
		}
	}
	if ($pmb_transferts_actif){
		// si transferts valid� (en attente d'envoi), il faut restaurer le statut 
		global $PMBuserid;
		$rqt = "SELECT id_transfert FROM transferts,transferts_demande
		where
		num_transfert=id_transfert and
		etat_demande=1 and num_expl =$id_expl and etat_transfert=0 and sens_transfert=0";
		$res = mysql_query ( $rqt );
		if (mysql_num_rows($res)){
			$obj = mysql_fetch_object($res);
			$idTrans=$obj->id_transfert;
			//R�cup�ration des informations d'origine
			$rqt = "SELECT statut_origine, num_expl FROM transferts INNER JOIN transferts_demande ON id_transfert=num_transfert
			WHERE id_transfert=".$idTrans." AND sens_transfert=0";
			$res = mysql_query($rqt);
			$obj_data = mysql_fetch_object($res);
			//on met � jour
			$rqt = "UPDATE exemplaires SET expl_statut=".$obj_data->statut_origine." WHERE expl_id=".$obj_data->num_expl;
			mysql_query ( $rqt );
		}
		// cloture les demandes de transfert pour r�sa, refus�e ou pas
		// afin de g�n�rer les transfert en automatique dans le circuit classique des r�sa
		$req=" update transferts,transferts_demande 
		set etat_transfert=1 ,
		motif=CONCAT(motif,'. Cloture, car parti en pret (gestion $PMBuserid, $id_empr)') 
		where 
		num_transfert=id_transfert and
		(etat_demande=4 or etat_demande=0 or etat_demande=1)and
		etat_demande != 3 and etat_demande!=2 and etat_demande!=5 and 
		num_expl =$id_expl and etat_transfert=0 and sens_transfert=0
		";
		mysql_query($req, $dbh);
	}
	// invalidation des r�sas avec ce code-barre, au cas o�
	// $query = "update resa SET resa_cb='' where resa_cb='".$cb_doc."' ";
	// $result = @ mysql_query($query, $dbh) or die("can't update resa ".$query);

}

// efface une r�sa pour un emprunteur donn� et r�affecte le cb �ventuellement
function del_resa($id_empr, $id_notice, $id_bulletin, $cb_encours_de_pret) {
	
	global $dbh;
	
	if (!$id_empr || (!$id_notice && !$id_bulletin))
		return FALSE;

	if (!$id_notice)
		$id_notice = 0;
	if (!$id_bulletin)
		$id_bulletin = 0;
	$rqt = "select resa_cb, id_resa from resa where resa_idnotice='".$id_notice."' and resa_idbulletin='".$id_bulletin."'  and resa_idempr='".$id_empr."' ";
	$res = mysql_query($rqt, $dbh);
	$obj = mysql_fetch_object($res);
	$cb_recup = $obj->resa_cb;
	$id_resa = $obj->id_resa;

	// suppression
	$rqt = "delete from resa where id_resa='".$id_resa."' ";
	$res = mysql_query($rqt, $dbh);
	
	// si on delete une resa � partir d'un pr�t, on invalide la r�sa qui �tait valid�e avec le cb, mais on ne change pas les dates, �a sera fait par affect_cb
	$rqt_invalide_resa = "update resa set resa_cb='' where resa_cb='".$cb_encours_de_pret."' " ;  
	$res = mysql_query ($rqt_invalide_resa, $dbh) ;
												
	// r�affectation du doc �ventuellement
	if ($cb_recup != $cb_encours_de_pret) {
		// les cb sont diff�rents
		if (!verif_cb_utilise($cb_recup)) {
			// le cb qui �tait affect� � la r�sa qu'on vient de supprimer n'est pas utilis�
			// on va affecter le cb_r�cup�r� � une resa non valid�e
			$res_affectation = affecte_cb($cb_recup) ;
			if (!$res_affectation && $cb_recup) {
				// cb non r�affect�, il faut transf�rer les infos de la r�sa dans la table des docs � ranger
				$rqt = "insert into resa_ranger (resa_cb) values ('".$cb_recup."') ";
				$res = mysql_query($rqt, $dbh);
				}
			}
		}
	// Au cas o� il reste des r�sa invalid�es par resa_cb, on leur colle les dates comme il faut...
	$rqt_invalide_resa = "update resa set resa_date_debut='0000-00-00', resa_date_fin='0000-00-00' where resa_cb='' " ;  
	$res = mysql_query ($rqt_invalide_resa, $dbh) ;
	return TRUE;
}
