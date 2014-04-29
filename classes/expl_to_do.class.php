<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: expl_to_do.class.php,v 1.56 2014-02-14 09:06:02 ngantier Exp $

if (stristr ( $_SERVER ['REQUEST_URI'], ".class.php" ))
	die ( "no access" );

require_once("$class_path/transfert.class.php");
require_once("$class_path/expl.class.php");
require_once ("$include_path/templates/expl_retour.tpl.php");
require_once ("$include_path/expl_info.inc.php");
require_once("$class_path/groupexpl.class.php");
require_once($class_path."/comptes.class.php");

//********************************************************************************************
// Classe de gestion des actions � effectuer pour un exemplaire:
// transfert, r�servation, retour
//********************************************************************************************

class expl_to_do {

	var $expl_cb;
	var $expl_id;
	var $url;
	var $expl;
	var $expl_owner_name;
	var $trans_aut;
	var $info_doc;
	var $expl_info;
	var $piege;
	var $flag_resa=0;
	var $flag_resa_is_affecte=0;
	var $flag_resa_ici=0;
	var $flag_resa_origine=0;
	var $flag_resa_autre_site=0;
	var $id_resa;
	var $resa_loc_trans;
	var $piege_resa=0;
	var $id_resa_to_validate;
	var $cb_tmpl;
	var $empr;
	var $resa_date_fin;
	var $flag_resa_planning=0;	
	var $flag_resa_planning_is_affecte=0;			
	var $ids_resa_planning=array();
	var $piege_resa_planning=0;
	
// constructeur
function expl_to_do($cb='', $expl_id=0,$url="./circ.php?categ=retour") {
	$this->expl_cb = $cb;
	$this->expl_id=$expl_id;
	$this->url=$url;
	$this->fetch_data();
	
	return true;
	
}
function gen_liste() {
	global $dbh,$msg,$deflt_docs_location,$begin_result_liste,$end_result_liste;
	if(!$deflt_docs_location)	return"";
	$sql = "SELECT expl_id, expl_cb FROM exemplaires where expl_retloc='".$deflt_docs_location."' ";
	$req = mysql_query($sql) or die ($msg["err_sql"]."<br />".$sql."<br />".mysql_error());
	
	while(($liste = mysql_fetch_object($req))) {
		
		if(($stuff = get_expl_info($liste->expl_id))) {
			$stuff = check_pret($stuff);
			$aff_final .=  print_info($stuff,0,0,0);
		}
	}
	if ($aff_final) return "<h3>".$msg['expl_todo_liste']."</h3>".$begin_result_liste.$aff_final.$end_result_liste;
	else return $msg['resa_liste_docranger_nodoc'] ;
	
}

function fetch_data() {

	global $dbh,$msg;
	global $pmb_confirm_retour;
	global $confirmation_retour_tpl,$retour_ok_tpl;

	$this->build_cb_tmpl($msg[660], $msg[661], $msg[circ_tit_form_cb_expl], $this->url, 1);
	
	if($this->expl_cb) $query = "select * from exemplaires where expl_cb='".$this->expl_cb."' ";
	elseif($this->expl_id) $query = "select * from exemplaires where expl_id='".$this->expl_id."' ";
	else return;
	$result = mysql_query($query, $dbh);
	$this->expl = mysql_fetch_object($result);
	if(!$this->expl->expl_id) {
		return false;
	} else {
		$this->expl_cb =$this->expl->expl_cb;
		$this->expl_id=$this->expl->expl_id;
		// r�cup�ration des infos exemplaires
		if ($this->expl->expl_notice) {
			$notice = new mono_display($this->expl->expl_notice, 0);
			$this->expl->libelle = $notice->header;
		} else {
			$bulletin = new bulletinage_display($this->expl->expl_bulletin);
			$this->expl->libelle = $bulletin->display ;
		}
		if ($this->expl->expl_lastempr) {
			// r�cup�ration des infos emprunteur
			$query_last_empr = "select empr_cb, empr_nom, empr_prenom from empr where id_empr='".$this->expl->expl_lastempr."' ";
			$result_last_empr = mysql_query($query_last_empr, $dbh);
			if(mysql_num_rows($result_last_empr)) {
				$last_empr = mysql_fetch_object($result_last_empr);
				$this->expl->lastempr_cb = $last_empr->empr_cb;
				$this->expl->lastempr_nom = $last_empr->empr_nom;
				$this->expl->lastempr_prenom = $last_empr->empr_prenom;
			}
		}
	}	

	$query = "select lender_libelle from lenders where idlender='".$this->expl->expl_owner."' ";
	
	$result_expl_owner = mysql_query($query, $dbh);
	if(mysql_num_rows($result_expl_owner)) {
		$expl_owner = mysql_fetch_object($result_expl_owner);
		$this->expl_owner_name =$expl_owner->lender_libelle;
	}
	
	$rqt = "SELECT transfert_flag 	FROM exemplaires INNER JOIN docs_statut ON expl_statut=idstatut 
			WHERE expl_id=".$this->expl_id;
	$res = mysql_query ($rqt) or die (mysql_error()."<br /><br />".$rqt);
	$value = mysql_fetch_array ($res);
	$this->trans_aut = $value[0];
		
	$this->expl = check_pret($this->expl);
	$this->expl = check_resa($this->expl);
	$this->expl = check_resa_planning($this->expl);
	
	// r�cup�ration localisation exemplaire
	$query = "SELECT t.tdoc_libelle as type_doc, l.location_libelle as location, s.section_libelle as section, docs_s.statut_libelle as statut FROM docs_type t, docs_location l, docs_section s, docs_statut docs_s";
	$query .= " WHERE t.idtyp_doc=".$this->expl->expl_typdoc;
	$query .= " AND l.idlocation=".$this->expl->expl_location;
	$query .= " AND s.idsection=".$this->expl->expl_section;
	$query .= " AND docs_s.idstatut=".$this->expl->expl_statut;
	$query .= " LIMIT 1";

	$result = mysql_query($query, $dbh);
	$this->info_doc=mysql_fetch_object($result);
	
	// En profiter pour faire le menage doc � ranger
	$rqt = "delete from resa_ranger where resa_cb='".$this->expl_cb."' ";
	$res = mysql_query ($rqt, $dbh) ;
	
	// flag confirm retour 
	if ($pmb_confirm_retour)  {
		$this->expl_form.= $confirmation_retour_tpl;
	} elseif ($this->expl->pret_idempr) {
		$this->expl_form.= $retour_ok_tpl;			
	}
	return true;	
}

function do_form_retour($action_piege=0,$piege_resa=0){
	global $msg,$dbh,$form_retour_tpl,$script_magnetique,$pmb_antivol,$deflt_docs_location,$pmb_transferts_actif;
	global $transferts_retour_origine,$transferts_retour_origine_force;
	global $script_antivol_rfid,$pmb_rfid_activate,$pmb_rfid_serveur_url,$transferts_retour_action_defaut;
	global $expl_section,$retour_ok_tpl,$retour_intouvable_tpl,$categ;
	global $pmb_resa_retour_action_defaut,$pmb_hide_retdoc_loc_error;
	global $alert_sound_list,$pmb_play_pret_sound,$pmb_lecteurs_localises;
	global $pmb_resa_planning,$pmb_location_resa_planning;
	global $pmb_pret_groupement;
	global $pmb_expl_show_lastempr;
	
	$form_retour_tpl_temp=$form_retour_tpl;
	if(!$this->expl_id) {
		// l'exemplaire est inconnu
		$this->expl_form="<div class='erreur'>".$this->expl_cb."&nbsp;: ${msg[367]}</div>";
		// Ajouter ici la recherche empr
		if ($this->expl_cb) { // on a un code-barres, est-ce un cb empr ?
			$query_empr = "select id_empr, empr_cb from empr where empr_cb='".$this->expl_cb."' ";
			$result_empr = mysql_query($query_empr, $dbh);
			if(mysql_num_rows($result_empr)) {
				$this->expl_form.="<script type=\"text/javascript\">document.location='./circ.php?categ=pret&form_cb=$this->expl_cb'</script>";
				}
		}
		$alert_sound_list[]="critique";
		return false;
	}		
//	if($pmb_lecteurs_localises) {
		if ($this->expl->expl_location != $deflt_docs_location && !$piege_resa && $deflt_docs_location) {
			// l'exemplaire n'appartient pas � cette localisation
			if ($pmb_transferts_actif=="1" && !$action_piege) {
				// transfert actif et pas de forcage effectu�
				if (transfert::is_retour_exemplaire_loc_origine($this->expl_id)) {
					$action_piege=4;
				//est ce qu'on peut force le retour en local
				}elseif ($transferts_retour_origine=="1" && $transferts_retour_origine_force=="0") {
					//pas de forcage possible, on interdit le retour
					$question_form="<div class='message_important'>".str_replace("!!lib_localisation!!",$this->info_doc->location,$msg["transferts_circ_retour_emprunt_erreur_localisation"])."</div>";
					$alert_sound_list[]="critique";	
					$this->piege=2;	
				}elseif($adminTransRetourAction==0 || $adminTransRetourAction==1) { 
					//formulaire de Quoi faire? 
					$selected[$transferts_retour_action_defaut]=" checked ";		
					$question_form="
					<form name='piege' method='post' action='".$this->url."&form_cb_expl=".rawurlencode(stripslashes($this->expl_cb))."' >
					<div class='message_important'>".
						str_replace("!!lib_localisation!!",$this->info_doc->location,$msg["transferts_circ_retour_emprunt_erreur_localisation"])."<br />
					</div>
					<div class='erreur'>
						<input type=\"radio\" name=\"action_piege\" value=\"1\" $selected[2]>&nbsp;".$msg["transferts_circ_retour_accepter_retour"]."<br />
						<input type=\"radio\" name=\"action_piege\" value=\"2\" $selected[1]>&nbsp;".$msg["transferts_circ_retour_changer_loc"]."&nbsp;".$this->get_liste_section()."<br />
						<input type=\"radio\" name=\"action_piege\" value=\"3\" $selected[0]>&nbsp;".$msg["transferts_circ_retour_traiter_plus_tard"]."<br />
						<input type=\"submit\" class=\"bouton\" value=\"".$msg["transferts_circ_retour_exec_action"]."\" >
					</div>
					</form>";
					$alert_sound_list[]="question";	
					$this->piege=1;	
				}else{
					$action_piege=1;	
					$alert_sound_list[]="information";
				}						
				
			}elseif (!$pmb_transferts_actif) {
				if(!$pmb_hide_retdoc_loc_error) {
					// pas de message et le retour se fait
				} elseif($pmb_hide_retdoc_loc_error==1){
					// Message et pas de retour
					$this->expl_form="<div class='erreur'>".str_replace("!!lib_localisation!!",$this->info_doc->location,$msg["transferts_circ_retour_emprunt_erreur_localisation"])."</div>";
					$alert_sound_list[]="critique";
					return false;
				}elseif($pmb_hide_retdoc_loc_error==2) {
					// Message et pas de retour
					$question_form="<div class='erreur'>".str_replace("!!lib_localisation!!",$this->info_doc->location,$msg["transferts_circ_retour_emprunt_erreur_localisation"])."</div>";
					$alert_sound_list[]="information";
				}	
			}
		}
	//fin si lecteur localis�
//	}
	if($pmb_pret_groupement){			
		if($id_group=groupexpls::get_group_expl($this->expl_cb)){
			// ce document appartient � un groupe
			$is_doc_group=1;
			$groupexpl=new groupexpl($id_group);
			$question_form.= $groupexpl->get_confirm_form($this->expl_cb);	
		}
	}
	//affichage de l'erreur de site et eventuellement du formulaire de forcage  
		
	$form_retour_tpl_temp=str_replace('!!html_erreur_site_tpl!!',$question_form, $form_retour_tpl_temp);	

	if($pmb_transferts_actif=="1" && !$this->piege) {
		$trans = new transfert();
		switch($action_piege) {
			case '1'://issu d'une autre localisation: accepter le retour
				if($this->expl->pret_idempr) $message_del_pret=$this->del_pret();
				$this->calcul_resa();
				if ($this->flag_resa_is_affecte){
					$message_resa="<div class='erreur'>".$msg["circ_retour_ranger_resa"]."</div>";
					global $charset;
					$requete="SELECT empr_cb, empr_nom, empr_prenom, location_libelle, resa_cb FROM resa JOIN empr ON resa_idempr=id_empr JOIN docs_location ON resa_loc_retrait=idlocation  WHERE id_resa=".$this->id_resa."";
					$res=mysql_query($requete);
					$message_resa .= "<div class='row'>";
					$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_resa_par"]." : </strong><a href='./circ.php?categ=pret&form_cb=".rawurlencode(mysql_result($res,0,0))."'>".htmlentities(mysql_result($res,0,2),ENT_QUOTES,$charset)." ".htmlentities(pmb_strtoupper(mysql_result($res,0,1),ENT_QUOTES,$charset),$charset)."</a></span><br/>";
					$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_loc_retrait"]." : </strong>".htmlentities(mysql_result($res,0,3),ENT_QUOTES,$charset)."</span><br/>";
					$message_resa .= "</div>" ;
					$alert_sound_list[]="information";
				}	
				if($this->flag_resa_ici) {										
				} elseif($this->flag_resa_origine){
					//Gen retour sur site origine
					$param = $trans->retour_exemplaire_genere_transfert_retour($this->expl_id);
					$message_transfert= "<div class='erreur'>" . str_replace("!!lbl_site!!",$this->info_doc->location,$msg["transferts_circ_retour_lbl_transfert"]) . "</div>";
				} elseif($this->flag_resa_autre_site){					
					//Gen retour sur autre site....
					// Pour l'instant on retourne au site d'origine
					$param = $trans->retour_exemplaire_genere_transfert_retour($this->expl_id);
					$message_transfert= "<div class='erreur'>" . str_replace("!!lbl_site!!",$this->info_doc->location,$msg["transferts_circ_retour_lbl_transfert"]) . "</div>";
				
				}else {
					// pas de r�sa on gen�re un retour au site d'origine	
					$param = $trans->retour_exemplaire_genere_transfert_retour($this->expl_id);				
					$message_transfert= "<div class='erreur'>" . str_replace("!!lbl_site!!",$this->info_doc->location,$msg["transferts_circ_retour_lbl_transfert"]) . "</div>";
				}	
			break;
			case '3':// A traiter plus tard				
				if($this->expl->pret_idempr) $message_del_pret=$this->del_pret();
				$this->piege=1;	
			break;			
			case '4':// retour sur le site d'origne, il faut nettoyer
				$param = $trans->retour_exemplaire_loc_origine($this->expl_id);
				if($this->expl->pret_idempr) $message_del_pret=$this->del_pret();
				$this->calcul_resa();
			break;
			case '2'://issu d'une autre localisation: changer la loc, effacer les transfert				
				//$trans->retour_exemplaire_supprime_transfert( $this->expl_id, $param );
				//change la localisation d'origine
				$param = $trans->retour_exemplaire_change_localisation($this->expl_id);
				
				// modif de la section, si demand�e
				if($expl_section && ($expl_section != $this->expl->expl_section)){
					$rqt = 	"UPDATE exemplaires SET expl_section=$expl_section WHERE expl_id=" . $this->expl_id; 
					mysql_query ( $rqt );
				}	
			// pas de break; on fait le reste du traitement par d�faut
			default:
				if($this->expl->pret_idempr) $message_del_pret=$this->del_pret();
				$resa_id=$this->calcul_resa();
				if ($this->flag_resa_is_affecte){
					$message_resa="<div class='erreur'>".$msg["circ_retour_ranger_resa"]."</div>";
					global $charset;
					$requete="SELECT empr_cb, empr_nom, empr_prenom, location_libelle, resa_cb FROM resa JOIN empr ON resa_idempr=id_empr JOIN docs_location ON resa_loc_retrait=idlocation  WHERE id_resa=".$this->id_resa."";
					$res=mysql_query($requete);
					$message_resa .= "<div class='row'>";
					$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_resa_par"]." : </strong><a href='./circ.php?categ=pret&form_cb=".rawurlencode(mysql_result($res,0,0))."'>".htmlentities(mysql_result($res,0,2),ENT_QUOTES,$charset)." ".htmlentities(pmb_strtoupper(mysql_result($res,0,1)),ENT_QUOTES,$charset)."</a></span><br/>";
					$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_loc_retrait"]." : </strong>".htmlentities(mysql_result($res,0,3),ENT_QUOTES,$charset)."</span><br/>";
					$message_resa .= "</div>" ;
					$alert_sound_list[]="information";		
				}
				if($this->flag_resa_ici) {										
				}elseif($this->flag_resa_origine){
					//Gen retour sur site origine
					if(!$trans->est_retournable($this->expl_id)) {
						// si pas encore g�n�r�
						$param = $trans->retour_exemplaire_genere_transfert_retour($this->expl_id);
						$message_transfert= "<div class='erreur'>" . str_replace("!!lbl_site!!",$this->info_doc->location,$msg["transferts_circ_retour_lbl_transfert"]) . "</div>";
					} else {
						// le retour est d�j� g�n�r�
						$message_transfert = "<hr /><div class='erreur'>".$msg["transferts_circ_menu_titre"].":</div><div class='message_important'>".
			 			str_replace("!!source_location!!", $trans->location_libelle_source,$msg["transferts_circ_retour_a_retourner"])."</div>";							
						$alert_sound_list[]="information";
					}	
				} elseif($this->flag_resa_autre_site){
					// si r�sa autre site � d�ja une demande de transfert, ou transfert
					$req="select * from transferts, transferts_demande where num_transfert=id_transfert and resa_trans='$resa_id' and etat_transfert=0";
					$r = mysql_query($req, $dbh);
					if (!mysql_num_rows($r)) {
						//Gen transfert sur site de la r�sa....
						$param = $trans->transfert_pour_resa($this->expl_cb,$this->resa_loc_trans,$resa_id);
						// r�cup�ration localisation exemplaire
						$query = "SELECT location_libelle FROM  docs_location WHERE idlocation=".$this->resa_loc_trans." LIMIT 1";
						$result = mysql_query($query, $dbh);
						$info_loc=mysql_fetch_object($result);					
						$message_transfert= "<div class='erreur'>" . str_replace("!!site_dest!!",$info_loc->location_libelle,$msg["transferts_circ_transfert_pour_resa"]) . "</div>";
					}				
				}else {
					// pas de r�sa.Doit-il �tre retourn� � son site d'origine?
					if($trans->est_retournable($this->expl_id)) {
						$message_transfert = "<hr /><div class='erreur'>".$msg["transferts_circ_menu_titre"].":</div><div class='message_important'>".
			 			str_replace("!!source_location!!", $trans->location_libelle_source,$msg["transferts_circ_retour_a_retourner"])."</div>";	
						$trans->retour_exemplaire_genere_transfert_retour_origine($this->expl_id);// netoyer les transfert interm�diaire
						$alert_sound_list[]="information";
					} else {
						// A ranger
					}	
				}
				//v�rifions s'il y a des r�servations pr�visionnelles sur ce document..
				if ($pmb_resa_planning) {
					$this->calcul_resa_planning();
					if ($this->flag_resa_planning_is_affecte) {
						global $charset;
						$message_resa_planning = "<div class='erreur'>$msg[resas_planning]</div>";
						$message_resa_planning .= "<div class='row'>
							<img src='./images/plus.gif' class='img_plus'
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
											
						//Affichage des r�servations pr�visionnelles sur le document courant
						$q = "SELECT id_resa, resa_idnotice, resa_date, resa_date_debut, resa_date_fin, resa_validee, IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_fin, '".$msg["format_date_sql"]."') as aff_date_fin, ";
						$q.= "resa_idempr, concat(lower(empr_prenom), ' ',upper(empr_nom)) as resa_nom, if(resa_idempr!='".$this->expl->pret_idempr."', 0, 1) as resa_same ";
						$q.= "FROM resa_planning left join empr on resa_idempr=id_empr ";
						$q.= "where resa_idnotice in (select expl_notice from exemplaires where expl_cb = '".$this->expl_cb."') ";
						if ($pmb_location_resa_planning) $q.= "and empr_location in (select expl_location from exemplaires where expl_cb = '".$this->expl_cb."') ";
						$r = mysql_query($q, $dbh);
						if (mysql_num_rows($r)) {
							$message_resa_planning.= "<div id='erreur-child' class='erreur-child'>";
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
									$message_resa_planning.= "<b>".htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;</b>";
								} else {
									$message_resa_planning.= htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;";
								}
								$message_resa_planning.= " &gt;&gt; <b>".$msg['resa_planning_date_debut']."</b> ".formatdate($resa_date_debut)."&nbsp;<b>".$msg['resa_planning_date_fin']."</b> ".formatdate($resa_date_fin)."&nbsp;" ;
								if (!$resa['perimee']) {
									if ($resa['resa_validee'])  $message_resa_planning.= " ".$msg['resa_validee'] ;
										else $message_resa_planning.= " ".$msg['resa_attente_validation']." " ;
								} else  $message_resa_planning.= " ".$msg['resa_overtime']." " ;
								$message_resa_planning.= "<br />" ;
							} //while
							$message_resa_planning.= "</div></div>";
							$alert_sound_list[]="information";	
						}
					}
				}
			break;		
		}
			
	}		
	
	if(!$pmb_transferts_actif){
		if($this->expl->pret_idempr) $message_del_pret=$this->del_pret();
		$this->calcul_resa();		
		if ($this->flag_resa_is_affecte){
			$message_resa="<div class='erreur'>".$msg["circ_retour_ranger_resa"]."</div>";
			global $charset;
			$requete="SELECT empr_cb, empr_nom, empr_prenom, location_libelle, resa_cb FROM resa JOIN empr ON resa_idempr=id_empr JOIN docs_location ON resa_loc_retrait=idlocation  WHERE id_resa=".$this->id_resa."";
			$res=mysql_query($requete);
			$message_resa .= "<div class='row'>";
			$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_resa_par"]." : </strong><a href='./circ.php?categ=pret&form_cb=".rawurlencode(mysql_result($res,0,0))."'>".htmlentities(mysql_result($res,0,2),ENT_QUOTES,$charset)." ".pmb_strtoupper(htmlentities(mysql_result($res,0,1),ENT_QUOTES,$charset),$charset)."</a></span><br/>";
			$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_loc_retrait"]." : </strong>".htmlentities(mysql_result($res,0,3),ENT_QUOTES,$charset)."</span><br/>";
			$message_resa .= "</div>" ;
			$alert_sound_list[]="information";	
		}
		if ($pmb_resa_planning) {
			$this->calcul_resa_planning();	
			if ($this->flag_resa_planning_is_affecte) {
				global $charset;
				$message_resa_planning = "<div class='erreur'>$msg[resas_planning]</div>";
				$message_resa_planning .= "<div class='row'>
					<img src='./images/plus.gif' class='img_plus'
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
											
				//Affichage des r�servations pr�visionnelles sur le document courant
				$q = "SELECT id_resa, resa_idnotice, resa_date, resa_date_debut, resa_date_fin, resa_validee, IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_fin, '".$msg["format_date_sql"]."') as aff_date_fin, ";
				$q.= "resa_idempr, concat(lower(empr_prenom), ' ',upper(empr_nom)) as resa_nom, if(resa_idempr!='".$this->expl->pret_idempr."', 0, 1) as resa_same ";
				$q.= "FROM resa_planning left join empr on resa_idempr=id_empr ";
				$q.= "where resa_idnotice in (select expl_notice from exemplaires where expl_cb = '".$this->expl_cb."') ";
				if ($pmb_location_resa_planning) $q.= "and empr_location in (select expl_location from exemplaires where expl_cb = '".$this->expl_cb."') ";
				$r = mysql_query($q, $dbh);
				if (mysql_num_rows($r)) {
					$message_resa_planning.= "<div id='erreur-child' class='erreur-child'>";
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
							$message_resa_planning.= "<b>".htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;</b>";
						} else {
							$message_resa_planning.= htmlentities($resa_nom, ENT_QUOTES, $charset)."&nbsp;";
						}
						$message_resa_planning.= " &gt;&gt; <b>".$msg['resa_planning_date_debut']."</b> ".formatdate($resa_date_debut)."&nbsp;<b>".$msg['resa_planning_date_fin']."</b> ".formatdate($resa_date_fin)."&nbsp;" ;
						if (!$resa['perimee']) {
							if ($resa['resa_validee'])  $message_resa_planning.= " ".$msg['resa_validee'] ;
								else $message_resa_planning.= " ".$msg['resa_attente_validation']." " ;
						} else  $message_resa_planning.= " ".$msg['resa_overtime']." " ;
						$message_resa_planning.= "<br />" ;
					} //while
					$message_resa_planning.= "</div></div>";
					$alert_sound_list[]="information";	
				}
			}
		}
	}	
	if(!$this->piege) {
		if($this->flag_resa_ici && !$piege_resa) { 
			$query = "SELECT empr_location,empr_prenom, empr_nom, empr_cb FROM resa INNER JOIN empr ON resa_idempr = id_empr WHERE id_resa='".$this->id_resa_to_validate."'";
			$result = mysql_query($query, $dbh);		
			$empr=@mysql_fetch_object($result);
			$info_resa="<div class='message_important'>$msg[352]</div>
			<div class='row'>".$msg[373]."&nbsp;<strong><a href='./circ.php?categ=pret&form_cb=".rawurlencode($empr->empr_cb)."'>".$empr->empr_prenom."&nbsp;".$empr->empr_nom."</a></strong>&nbsp;($empr->empr_cb )
			</div>";
			if($categ=="ret_todo"|| $pmb_resa_retour_action_defaut==1) $checked[1]="checked";else $checked[2]="checked";
			$question_resa="
				<form name='piege' method='post' action='".$this->url."&form_cb_expl=".rawurlencode($this->expl_cb)."' >
				$info_resa
				<div class='erreur'>
					<input type=\"radio\" name=\"piege_resa\" value=\"1\" $checked[1] >&nbsp;".$msg["circ_retour_piege_resa_affecter"]."<br />
					<input type=\"radio\" name=\"piege_resa\" value=\"2\" $checked[2] >&nbsp;".$msg["transferts_circ_retour_traiter_plus_tard"]."<br />
					<input type=\"submit\" class=\"bouton\" value=\"".$msg["transferts_circ_retour_exec_action"]."\" >
				</div>
				</form>";
			$alert_sound_list[]="question";
			$this->piege_resa=1;	
		}elseif($this->flag_resa_ici && $piege_resa==1) {										
			alert_empr_resa($this->affecte_resa());	
			$message_resa="<div class='erreur'>".$msg["circ_retour_ranger_resa"]."</div>";	
			global $charset;
			$requete="SELECT empr_cb, empr_nom, empr_prenom, location_libelle, resa_cb FROM resa JOIN empr ON resa_idempr=id_empr JOIN docs_location ON resa_loc_retrait=idlocation  WHERE id_resa=".$this->id_resa."";
			$res=mysql_query($requete);
			$message_resa .= "<div class='row'>";
			$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_resa_par"]." : </strong><a href='./circ.php?categ=pret&form_cb=".rawurlencode(mysql_result($res,0,0))."'>".htmlentities(mysql_result($res,0,2),ENT_QUOTES,$charset)." ".mb_strtoupper(htmlentities(mysql_result($res,0,1),ENT_QUOTES,$charset),$charset)."</a></span><br/>";
			$message_resa .= "<span style='margin-left:2em;'><strong>".$msg["circ_retour_loc_retrait"]." : </strong>".htmlentities(mysql_result($res,0,3),ENT_QUOTES,$charset)."</span><br/>";
			$message_resa .= "</div>" ;	
			$alert_sound_list[]="information";	
		} elseif($this->flag_resa_ici) {
			$this->piege_resa=1;
		}
	}
	
	if($this->piege || ($this->piege_resa && $piege_resa !=1)) {
		// il y a des pieges, on marque comme exemplaire � probl�me dans la localisation qui fait le retour
		$sql = "UPDATE exemplaires set expl_retloc='".$deflt_docs_location."' where expl_cb='".addslashes($this->expl_cb)."' limit 1";
	} else {
		// pas de pi�ges, ou pi�ges r�solus, on d�marque
		$sql = "UPDATE exemplaires set expl_retloc=0 where expl_cb='".addslashes($this->expl_cb)."' limit 1";
	}
	mysql_query($sql);
	
	$form_retour_tpl_temp=str_replace('!!piege_resa_ici!!',$question_resa, $form_retour_tpl_temp);
		
	if($this->expl->pret_idempr)	$this->empr = new emprunteur($this->expl->pret_idempr, "", FALSE, 2);
	
	if( $pmb_rfid_activate && $pmb_rfid_serveur_url ) {			
		$form_retour_tpl_temp= str_replace('<!--antivol_script-->',$script_antivol_rfid, $form_retour_tpl_temp);
		$this->cb_tmpl = str_replace("//antivol_test//", "if(0)", $this->cb_tmpl);		
	} elseif( $pmb_antivol>0) {
		// gestion de  l'antivol magn�tique 3M
		if($this->expl->type_antivol ==1)// c'est un support non magn�tique (livre, revue...)
			$script_magnetique= str_replace('<!--call_script_magnetique-->', "magnetise('RRR');", $script_magnetique);
		if($this->expl->type_antivol ==2)//c'est un support magn�tique (cassette)	
			$script_magnetique= str_replace('<!--call_script_magnetique-->', "magnetise('SSS');", $script_magnetique);
		$form_retour_tpl_temp= str_replace('<!--antivol_script-->',$script_magnetique, $form_retour_tpl_temp);	
	}
	if ($this->flag_rendu && $pmb_play_pret_sound)
			 $alert_sound_list[]="information";
			
	$form_retour_tpl_temp=str_replace('!!message_del_pret!!',$message_del_pret, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!message_resa!!',$message_resa, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!message_resa_planning!!',$message_resa_planning, $form_retour_tpl_temp) ;	
	$form_retour_tpl_temp=str_replace('!!message_transfert!!',$message_transfert, $form_retour_tpl_temp) ;	
	
	$form_retour_tpl_temp=str_replace('!!libelle!!',$this->expl->libelle, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!type_doc!!',$this->info_doc->type_doc, $form_retour_tpl_temp) ;	
	$form_retour_tpl_temp=str_replace('!!location!!',$this->info_doc->location, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!section!!',$this->info_doc->section, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!statut!!',$this->info_doc->statut, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!expl_cote!!',$this->expl->expl_cote, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!expl_cb!!',$this->expl_cb, $form_retour_tpl_temp) ;
	$form_retour_tpl_temp=str_replace('!!expl_owner!!',$this->expl_owner_name, $form_retour_tpl_temp);
	$form_retour_tpl_temp=str_replace('!!expl_id!!',$this->expl_id, $form_retour_tpl_temp);
	if($this->flag_rendu)
		$form_retour_tpl_temp=str_replace('!!message_retour!!',$retour_ok_tpl, $form_retour_tpl_temp);
	elseif($categ!="ret_todo" && !$piege_resa)
		$form_retour_tpl_temp=str_replace('!!message_retour!!',$retour_intouvable_tpl, $form_retour_tpl_temp);
	else 
		$form_retour_tpl_temp=str_replace('!!message_retour!!',"", $form_retour_tpl_temp);		
	
	//Champs personalis�s
	$p_perso=new parametres_perso("expl");
	$perso_aff = "" ;
	if (!$p_perso->no_special_fields) {
		$perso_=$p_perso->show_fields($this->expl_id);
		for ($i=0; $i<count($perso_["FIELDS"]); $i++) {
			$p=$perso_["FIELDS"][$i];
			if ($p["AFF"]) $perso_aff .="<br />".$p["TITRE"]." ".$p["AFF"];
		}
	}
	if ($perso_aff) $perso_aff= "<div class='row'>".$perso_aff."</div>" ;
	$form_retour_tpl_temp=str_replace('!!perso_aff!!',$perso_aff, $form_retour_tpl_temp);
	
	if ($this->expl->expl_note) {
		$alert_sound_list[]="critique";
		$expl_note.=pmb_bidi("<hr /><div class='erreur'>${msg[377]} :</div><div class='message_important'>".$this->expl->expl_note."</div>");
	}
	$form_retour_tpl_temp=str_replace('!!expl_note!!',$expl_note, $form_retour_tpl_temp);
	
	if ($this->expl->expl_comment) {
		if (!$this->expl->expl_note) $expl_comment.=pmb_bidi("<hr />");
		$expl_comment.=pmb_bidi("<div class='erreur'>${msg[expl_zone_comment]} :</div><div class='expl_comment'>".$this->expl->expl_comment."</div>");
	}
	$form_retour_tpl_temp=str_replace('!!expl_comment!!',$expl_comment, $form_retour_tpl_temp);
	
	// zone du dernier emrunteur
	if ($pmb_expl_show_lastempr && $this->expl->expl_lastempr) {
		$dernier_empr = "<hr /><div class='row'>$msg[expl_prev_empr] ";
		$link = "<a href='./circ.php?categ=pret&form_cb=".rawurlencode($this->expl->lastempr_cb)."'>";
		$dernier_empr .= $link.$this->expl->lastempr_prenom.' '.$this->expl->lastempr_nom.' ('.$this->expl->lastempr_cb.')</a>';
		$dernier_empr .= "</div><hr />";
	}
	$form_retour_tpl_temp=str_replace('!!expl_lastempr!!',$dernier_empr, $form_retour_tpl_temp);
	
	if($this->empr) $expl_empr= pmb_bidi($this->empr->fiche_affichage);
	$form_retour_tpl_temp=str_replace('!!expl_empr!!',$expl_empr, $form_retour_tpl_temp);

	$this->expl_form=$form_retour_tpl_temp;	
	
}
function get_liste_section(){
	global $transferts_retour_action_defaut;
	global $transferts_retour_action_autorise_autre;
	global $msg,$deflt_docs_location;

	
	//on genere la liste des sections
	$rqt = "SELECT idsection, section_libelle FROM docs_section ORDER BY section_libelle";
	$res_section = mysql_query($rqt);
	$liste_section = "<select name='expl_section'>";
	while(($value = mysql_fetch_object($res_section))) {
		$liste_section .= "<option value='".$value->idsection ."'";
		if ($value->idsection==$this->expl->expl_section) {
			$liste_section .= " selected";
		}	
		$liste_section .= ">" . $value->section_libelle . "</option>";
	}						
	$liste_section.= "</select>";
	return $liste_section;
}	

function calcul_resa() {
	global $dbh,$msg, $pmb_utiliser_calendrier;
	global $deflt2docs_location,$pmb_transferts_actif,$transferts_choix_lieu_opac,$transferts_site_fixe;	
	global $deflt_docs_location,$pmb_location_reservation;
	
	// chercher si ce document a d�j� valid� une r�servation
	$rqt = 	"SELECT id_resa	FROM resa WHERE resa_cb='".addslashes($this->expl_cb)."' "; 
	$res = mysql_query ($rqt, $dbh) ;
	if (mysql_num_rows($res)) {
		$obj_resa=mysql_fetch_object($res);
		$this->flag_resa_is_affecte=1;			
		$this->id_resa=$obj_resa->id_resa;
		return $obj_resa->id_resa;
	}
	
	// chercher s'il s'agit d'une notice ou d'un bulletin
	$rqt = "SELECT expl_notice, expl_bulletin FROM exemplaires WHERE expl_cb='".addslashes($this->expl_cb)."' ";
	$res = mysql_query ($rqt, $dbh) ;
	$nb=mysql_num_rows($res) ;
	if (!$nb) return 0 ;	
	$obj=mysql_fetch_object($res) ;
	
	if($pmb_transferts_actif) {
		$clause_trans= " and id_resa not in (select resa_trans from  transferts,transferts_demande where  num_transfert=id_transfert  and etat_transfert=0 and etat_demande<3) ";
	}	
	if($pmb_location_reservation) {			
		$sql_loc_resa.="  and resa_idempr=id_empr and empr_location=resa_emprloc and resa_loc='".$deflt_docs_location."' ";
		$sql_loc_resa_from=", resa_loc, empr";
	}	
	// chercher le premier (par ordre de rang, donc de date de d�but de r�sa, non valid�
	$rqt = 	"SELECT id_resa, resa_idempr,resa_loc_retrait 
			FROM resa $sql_loc_resa_from
			WHERE resa_idnotice='".$obj->expl_notice."' 
				AND resa_idbulletin='".$obj->expl_bulletin."' 
				AND resa_cb='' 
				AND resa_date_fin='0000-00-00' 
				$clause_trans
				$sql_loc_resa
			ORDER BY resa_date ";	
	
	$res = mysql_query ($rqt, $dbh) ;
	if (!mysql_num_rows($res)) return 0 ; // aucune r�sa
	$obj_resa=mysql_fetch_object($res) ;
	
	$this->flag_resa=1;
	// a verifier si cela ne d�pend pas plus de la localisation des r�servation
	if($pmb_transferts_actif) {
		$res_trans = 0; 		
		switch ($transferts_choix_lieu_opac) {					
			case "1":
				//retrait de la resa sur lieu choisi par le lecteur
				$res_trans = $obj_resa->resa_loc_retrait;
			break;				
			case "2":
				//retrait de la resa sur lieu fix�
				$res_trans = $transferts_site_fixe;
			break;				
			case "3":
				//retrait de la resa sur lieu exemplaire
				$res_trans = $deflt2docs_location;
			break;	
			default:
				//retrait de la resa sur lieu lecteur
				//on recupere la localisation de l'emprunteur
				$rqt = "SELECT empr_location,empr_prenom, empr_nom, empr_cb FROM resa INNER JOIN empr ON resa_idempr = id_empr WHERE id_resa='".$obj_resa->id_resa."'";
				$res = mysql_query($rqt);
				$res_trans = mysql_result($res,0) ;
			break;
		}

		if($res_trans==$deflt2docs_location) {
			// l'exemplaire peut �tre retir� ici
			$this->flag_resa_ici=1;
			$this->id_resa_to_validate=$obj_resa->id_resa;
		}elseif ($this->expl->transfert_location_origine == $res_trans) {
			// la r�sa est retirable sur le site d'origine
			$this->flag_resa_origine=1;						
		}else {
			// r�sa sur autre site que l'origine et qu'ici
			if(!$this->trans_aut){ // Si statut pas tranf�rable
				$this->flag_resa=0;
				return 0 ;
			}
			$this->flag_resa_autre_site=1;							
		}
		$this->resa_loc_trans=$res_trans;
	}else {
		$this->id_resa_to_validate=$obj_resa->id_resa;	
		$this->flag_resa_ici=1;	
	}		

	if($this->id_resa_to_validate) {
		// calcul de la date de fin de la r�sa (utile pour affecte_resa())
		$resa_nb_days = get_time($obj_resa->resa_idempr,$obj->expl_notice,$obj->expl_bulletin) ;		
		$rqt_date = "select date_add(sysdate(), INTERVAL '".$resa_nb_days."' DAY) as date_fin ";
		
		$resultatdate = mysql_query($rqt_date);
		$res = mysql_fetch_object($resultatdate) ;
		$this->resa_date_fin = $res->date_fin ;
		
		if ($pmb_utiliser_calendrier) {
			$rqt_date = "select date_ouverture from ouvertures where ouvert=1 and num_location=$deflt2docs_location and to_days(date_ouverture)>=to_days('".$this->resa_date_fin."') order by date_ouverture ";
			$resultatdate=mysql_query($rqt_date);
			$res=@mysql_fetch_object($resultatdate) ;
			if ($res->date_ouverture) $this->resa_date_fin=$res->date_ouverture ;
		}
	
	}		
	return $obj_resa->id_resa;
}	

function affecte_resa () {
	global $dbh;
	global $deflt2docs_location;
	
	if(!$this->id_resa_to_validate)return 0;
	// mettre resa_cb � jour pour cette resa
	$rqt = "update resa set resa_cb='".addslashes($this->expl_cb)."', resa_date_debut=sysdate() , resa_date_fin='".$this->resa_date_fin."', resa_loc_retrait='$deflt2docs_location' where id_resa='".$this->id_resa_to_validate."' ";
	mysql_query ($rqt, $dbh) or die(mysql_error()." <br />$rqt");
	$this->id_resa=$this->id_resa_to_validate;
	$this->id_resa_to_validate=0;
	return $this->id_resa;
}

function calcul_resa_planning() {
	global $dbh,$msg;
	global $pmb_location_resa_planning;
	
	// chercher si ce document a des r�servations plannifi�es
	$q = "select resa_idempr as empr, id_resa, concat(ifnull(concat(empr_nom,' '),''),empr_prenom) as nom_prenom ";
	$q.= "from resa_planning left join empr on resa_idempr=id_empr ";
	$q.= "where resa_idnotice = '".$this->expl->expl_notice."' ";
	if ($pmb_location_resa_planning) $q.= "and empr_location='".$this->expl->expl_location."' ";
	$q.= "and resa_date_debut >= curdate() ";
	$q.= "order by resa_date_debut ";
	$r = mysql_query($q, $dbh);
	// On compte les r�servations planifi�es sur ce document � des dates ult�rieures
	$nb_resa = mysql_num_rows($r);
	if ($nb_resa > 0) {
		$this->flag_resa_planning_is_affecte=1;
		while ($obj_resa = mysql_fetch_object($r)) {
			$ids_resa_planning[]=$obj_resa->id_resa;
		}
		$this->ids_resa_planning = $ids_resa_planning; 
	}	
	$this->flag_resa_planning=1;

	return $ids_resa_planning;
}

function del_pret() {
	global $dbh; 
	global $msg,$pmb_blocage_retard,$pmb_blocage_delai,$pmb_blocage_coef,$pmb_blocage_max,$pmb_gestion_financiere,$pmb_gestion_amende;
	global $selfservice_retour_retard_msg, $selfservice_retour_blocage_msg, $selfservice_retour_amende_msg;
	global $alertsound_list;
	if(!$this->expl->pret_idempr) return '';
	// calcul du retard �ventuel
	$rqt_date = "select ((TO_DAYS(CURDATE()) - TO_DAYS('".$this->expl->pret_retour."'))) as retard ";
	$resultatdate=mysql_query($rqt_date);
	$resdate=mysql_fetch_object($resultatdate);
	$retard = $resdate->retard;
	if($retard > 0) {
		//Calcul du vrai nombre de jours
		$date_debut=explode("-",$this->expl->pret_retour);
		$ndays=calendar::get_open_days($date_debut[2],$date_debut[1],$date_debut[0],date("d"),date("m"),date("Y"));
		if ($ndays>0) {
			$retard = (int)$ndays;
			$message.= "<br /><div class='erreur'>".$msg[369]."&nbsp;: ".$retard." ".$msg[370]."</div>";
			$alertsound_list[]="critique";
			$this->message_retard=$selfservice_retour_retard_msg." ".$msg[369]." : ".$retard." ".$msg[370];
		}
	}
	
	//Calcul du blocage
	if ($pmb_blocage_retard) {
		$date_debut=explode("-",$this->expl->pret_retour);
		$ndays=calendar::get_open_days($date_debut[2],$date_debut[1],$date_debut[0],date("d"),date("m"),date("Y"));
		if ($ndays>$pmb_blocage_delai) {
			$ndays=$ndays*$pmb_blocage_coef;
			if (($ndays>$pmb_blocage_max)&&($pmb_blocage_max!=0)) {
				$ndays=$pmb_blocage_max;
			}
		} else $ndays=0;
		if ($ndays>0) {
			//Le lecteur est-il d�j� bloqu� ?
			$date_fin_blocage_empr = mysql_result(mysql_query("select date_fin_blocage from empr where id_empr='".$this->expl->pret_idempr."'"),0,0);
			//Calcul de la date de fin
			$date_fin=calendar::add_days(date("d"),date("m"),date("Y"),$ndays);
			if ($date_fin > $date_fin_blocage_empr) {
				//Mise � jour
				mysql_query("update empr set date_fin_blocage='".$date_fin."' where id_empr='".$this->expl->pret_idempr."'");
				$message.= "<br /><div class='erreur'>".sprintf($msg["blocage_retard_pret"],formatdate($date_fin))."</div>";
				$alertsound_list[]="critique";
				$this->message_blocage=sprintf($selfservice_retour_blocage_msg,formatdate($date_fin));
			} else {
				$message.= "<br /><div class='erreur'>".sprintf($msg["blocage_already_retard_pret"],formatdate($date_fin_blocage_empr))."</div>";
				$alertsound_list[]="critique";
				$this->message_blocage=sprintf($selfservice_retour_blocage_msg,formatdate($date_fin_blocage_empr));
			}
		}
	}
	
	//V�rification des amendes
	if (($pmb_gestion_financiere) && ($pmb_gestion_amende)) {
		$amende=new amende($this->expl->pret_idempr);
		$amende_t=$amende->get_amende($this->expl_id);
		//Si il y a une amende, je la d�bite
		if ($amende_t["valeur"]) {
			$message.= pmb_bidi("<br /><div class='erreur'>".$msg["finance_retour_amende"]."&nbsp;: ".comptes::format($amende_t["valeur"]));
			$this->message_amende=$selfservice_retour_amende_msg." : ".comptes::format($amende_t["valeur"]);
			$alertsound_list[]="critique";
			$compte_id=comptes::get_compte_id_from_empr($this->expl->pret_idempr,2);
			if ($compte_id) {
				$cpte=new comptes($compte_id);
				if ($cpte->id_compte) {
					$cpte->record_transaction("",$amende_t["valeur"],-1,sprintf($msg["finance_retour_amende_expl"],$this->expl_id),0);
					$message.= " ".$msg["finance_retour_amende_recorded"];
				}
			}
			$message.="</div>";
			$req="delete from cache_amendes where id_empr=".$this->expl->pret_idempr;
			mysql_query($req);
		}
	}
	$query = "delete from pret where pret_idexpl=".$this->expl_id;
	if (!mysql_query($query, $dbh)) return '' ;
	
	$query = "update empr set last_loan_date=sysdate() where id_empr='".$this->expl->pret_idempr."' ";
	@mysql_query($query, $dbh);
	
	$query = "update exemplaires set expl_lastempr='".$this->expl->pret_idempr."', last_loan_date=sysdate() where expl_id='".$this->expl->expl_id."' ";
	if (!mysql_query($query, $dbh)) return '' ;
	
	$this->maj_stat_pret ();

	$this->empr = new emprunteur($this->expl->pret_idempr, $erreur_affichage, FALSE, 2);
	$this->expl->pret_idempr=0;
	$this->flag_rendu=1;
	return $message;
}

function maj_stat_pret () {
	global $dbh, $empr_archivage_prets, $empr_archivage_prets_purge; 

	$query = "update pret_archive set ";
	$query .= "arc_debut='".$this->expl->pret_date."', ";
	$query .= "arc_fin=now(), ";
	if ($empr_archivage_prets) $query .= "arc_id_empr='".addslashes($this->expl->id_empr)."', ";
	$query .= "arc_empr_cp='".			addslashes($this->expl->empr_cp)		."', ";
	$query .= "arc_empr_ville='".		addslashes($this->expl->empr_ville)	."', ";
	$query .= "arc_empr_prof='".		addslashes($this->expl->empr_prof)	."', ";
	$query .= "arc_empr_year='".		addslashes($this->expl->empr_year)	."', ";
	$query .= "arc_empr_categ='".		$this->expl->empr_categ    			."', ";
	$query .= "arc_empr_codestat='".	$this->expl->empr_codestat 			."', ";
	$query .= "arc_empr_sexe='".		$this->expl->empr_sexe     			."', ";
	$query .= "arc_empr_statut='".		$this->expl->empr_statut     		."', ";
	$query .= "arc_empr_location='".	$this->expl->empr_location     		."', ";
	$query .= "arc_type_abt='".			$this->expl->type_abt     			."', ";
	$query .= "arc_expl_typdoc='".		$this->expl->expl_typdoc   			."', ";
	$query .= "arc_expl_id='".			$this->expl->expl_id   				."', ";
	$query .= "arc_expl_notice='".		$this->expl->expl_notice   			."', ";
	$query .= "arc_expl_bulletin='".	$this->expl->expl_bulletin  			."', ";
	$query .= "arc_expl_cote='".		addslashes($this->expl->expl_cote)	."', ";
	$query .= "arc_expl_statut='".		$this->expl->expl_statut   			."', ";
	$query .= "arc_expl_location='".	$this->expl->expl_location 			."', ";
	$query .= "arc_expl_section='".		$this->expl->expl_section 			."', ";
	$query .= "arc_expl_codestat='".	$this->expl->expl_codestat 			."', ";
	$query .= "arc_expl_owner='".		$this->expl->expl_owner    			."', ";		
	$query .= "arc_niveau_relance='".	$this->expl->niveau_relance  			."', ";
	$query .= "arc_date_relance='".		$this->expl->date_relance    			."', ";
	$query .= "arc_printed='".			$this->expl->printed    				."', ";
	$query .= "arc_cpt_prolongation='".	$this->expl->cpt_prolongation 		."' ";	
	$query .= " where arc_id='".$this->expl->pret_arc_id."' ";
	$res = mysql_query($query, $dbh);

	audit::insert_modif (AUDIT_PRET, $this->expl->pret_arc_id) ;

	// purge des vieux trucs
	if ($empr_archivage_prets_purge) {
		//on ne purge qu'une fois par session et par jour
		if (!isset($_SESSION["last_empr_archivage_prets_purge_day"]) || ($_SESSION["last_empr_archivage_prets_purge_day"] != date("m.d.y"))) {
			mysql_query("update pret_archive set arc_id_empr=0 where arc_id_empr!=0 and date_add(arc_fin, interval $empr_archivage_prets_purge day) < sysdate()") or die(mysql_error()."<br />"."update pret_archive set arc_id_empr=0 where arc_id_empr!=0 and date_add(arc_fin, interval $empr_archivage_prets_purge day) < sysdate()");
			$_SESSION["last_empr_archivage_prets_purge_day"] = date("m.d.y");
		}
	}
	
	return $res ;
}


function build_cb_tmpl($title, $message, $title_form, $form_action, $check = 0) {
	global $expl_cb_retour_tmpl;
	global $script1expl;
	global $script2expl;
	global $form_cb_expl;
	global $rfid_retour_script,$pmb_rfid_activate,$pmb_rfid_serveur_url;
	

	if ($pmb_rfid_activate==1 && $pmb_rfid_serveur_url ) {
		$this->cb_tmpl = $rfid_retour_script;
		global $memo_cb_rfid;
		//foreach($memo_cb_rfid as $cb)
		$memo_cb_rfid_js="var memo_cb_rfid_js=new Array();\n";
		$i=0;
		$memo_cb=array();

		if($memo_cb_rfid)foreach($memo_cb_rfid as $cb){
			$memo_cb[]=$cb;
		}	
		if($form_cb_expl)$memo_cb[]=$form_cb_expl;
		
		$memo_cb_rfid_form="<select name='memo_cb_rfid[]' id='memo_cb_rfid' MULTIPLE style='display: none;'>";
		
		foreach($memo_cb as $cb){
			$memo_cb_rfid_form.="<OPTION VALUE='$cb' selected>$cb";
			$memo_cb_rfid_js.="memo_cb_rfid_js[".$i++."]='$cb';\n";
		}
		$memo_cb_rfid_form.="</select>";
		
		$this->cb_tmpl = str_replace("<!--memo_cb_rfid_form-->", $memo_cb_rfid_form, $this->cb_tmpl);
		$this->cb_tmpl = str_replace("//memo_cb_rfid_js//", $memo_cb_rfid_js, $this->cb_tmpl);

	}else {
		$this->cb_tmpl = $expl_cb_retour_tmpl;
	}

	
	if ($check) {
		$this->cb_tmpl = str_replace ( "!!script!!", $script2expl, $this->cb_tmpl );
	} else {
		$this->cb_tmpl = str_replace ( "!!script!!", $script1expl, $this->cb_tmpl );
	}
	$this->cb_tmpl = str_replace('!!expl_cb!!', $form_cb_expl, $this->cb_tmpl);
	$this->cb_tmpl = str_replace ( "!!titre_formulaire!!", $title_form, $this->cb_tmpl );
	$this->cb_tmpl = str_replace ( "!!form_action!!", $form_action, $this->cb_tmpl );
	
	if ($title)
		$this->cb_tmpl = str_replace ( "<h1>!!title!!</h1>", "<h1>" . $title . "</h1>", $this->cb_tmpl ); 
	else
		$this->cb_tmpl = str_replace ( "<h1>!!title!!</h1>", "", $this->cb_tmpl );
	
	$this->cb_tmpl = str_replace ( "!!message!!", $message, $this->cb_tmpl );
	
}

function do_retour_selfservice(){
	global $deflt_docs_location,$pmb_transferts_actif, $pmb_lecteurs_localises;
	global $transferts_retour_origine,$transferts_retour_origine_force;	
	global $selfservice_loc_autre_todo,$selfservice_resa_ici_todo,$selfservice_resa_loc_todo;
	global $selfservice_loc_autre_todo_msg,$selfservice_resa_ici_todo_msg,$selfservice_resa_loc_todo_msg;
	
	if(!$this->expl_id) {
		// l'exemplaire est inconnu
		$this->status=-1;
		return false;
	}
	if ($pmb_transferts_actif=="1") {
		$trans = new transfert();
		// transfert actif 
		if (transfert::is_retour_exemplaire_loc_origine($this->expl_id)) {
			// retour sur le site d'origne, il faut nettoyer
			$trans->retour_exemplaire_loc_origine($this->expl_id);	
			$this->expl->expl_location = $deflt_docs_location;			
		}
		if ($this->expl->expl_location != $deflt_docs_location ) {
			// l'exemplaire n'appartient pas � cette localisation
			if ($transferts_retour_origine=="1" && $transferts_retour_origine_force=="0") {
				//pas de forcage possible, on interdit le retour
				$non_retournable=1;
			}else { 
				// Quoi faire? 
				switch($selfservice_loc_autre_todo) {			
					case '4':// Refuser le retour
						$non_retournable=1;
					break;		
					case '1':// Accepter et G�n�rer un transfert
						$trans->retour_exemplaire_genere_transfert_retour($this->expl_id);				
						$non_reservable=1;						
					break;		
					case '2':// Accepter et changer la localisation
						$trans->retour_exemplaire_change_localisation($this->expl_id);				
					break;		
					case '3':// Accepter sans changer la localisation					
					break;				
					default:// Accepter et sera traiter plus tard						
						$non_reservable=1;
						$plus_tard=1;			
					break;
				}
			}	
			$this->message_loc= $selfservice_loc_autre_todo_msg;
			if(!$non_retournable) {
				if($this->expl->pret_idempr) $this->message_del_pret=$this->del_pret();
				if(!$non_reservable) {
					$resa_id=$this->calcul_resa();
					if ($this->flag_resa_is_affecte) {
						// D�j� affect�: il aurai du ne pas etre en pr�t
						$this->message_resa= $selfservice_resa_ici_todo_msg;
					}elseif($this->flag_resa_ici) {	
						switch($selfservice_resa_ici_todo) {			
							case '1':// Valider la rservation
								alert_empr_resa($this->affecte_resa(),0, 1);	
							break;		
							default://	A traiter plus tard
								$plus_tard=1;						
							break;	
						}	
						$this->message_resa=$selfservice_resa_ici_todo_msg;							
					}elseif($this->flag_resa_autre_site){
						switch($selfservice_resa_loc_todo) {			
							case '1':// Valider la rservation
								//Gen transfert sur site de la r�sa....
								$trans->transfert_pour_resa($this->expl_cb,$this->resa_loc_trans,$resa_id);
							break;		
							default://	A traiter plus tard
								$plus_tard=1;						
							break;	
						}						
						$this->message_resa=$selfservice_resa_loc_todo_msg;
						
					} else { 
						// pas de r�sa � g�rer
					}
				}			
			}
		}else {
			// c'est la bonne localisation ( et transfert actif)			
			if($this->expl->pret_idempr) $this->message_del_pret=$this->del_pret();			
			$this->calcul_resa();
			if ($this->flag_resa_is_affecte) {
				// D�j� affect�: il aurai du ne pas etre en pr�t
				$this->message_resa= $selfservice_resa_ici_todo_msg;
			}elseif($this->flag_resa_ici) {	
				switch($selfservice_resa_ici_todo) {			
					case '1':// Valider la rservation
						alert_empr_resa($this->affecte_resa(),0, 1);
					break;		
					default://	A traiter plus tard
						$plus_tard=1;						
					break;	
				}	
				$this->message_resa=$selfservice_resa_ici_todo_msg;							
			}elseif($this->flag_resa_autre_site){
				switch($selfservice_resa_loc_todo) {			
					case '1':// Valider la rservation
						//Gen transfert sur site de la r�sa....
						$trans->transfert_pour_resa($this->expl_cb,$this->resa_loc_trans,$resa_id);
					break;		
					default://	A traiter plus tard
						$plus_tard=1;						
					break;	
				}						
				$this->message_resa=$selfservice_resa_loc_todo_msg;
				
			} else { 
				// pas de r�sa � g�rer
			}				
		//Fin bonne localisation				
		}				
	//Fin transfert actif		
	}else {
		// transfert inactif $pmb_lecteurs_localises
		if ($pmb_lecteurs_localises && ($this->expl->expl_location != $deflt_docs_location) ) {
			//ce n'est pas la bonne localisation
			switch($selfservice_loc_autre_todo) {			
				case '4':// Refuser le retour
					$non_retournable=1;
				break;			
				case '3':// Accepter sans changer la localisation				
				break;				
				default:// Accepter et sera traiter plus tard					
					$non_reservable=1;
					$plus_tard=1;
				break;
			}
			$this->message_loc= $selfservice_loc_autre_todo_msg;
			if(!$non_retournable) {	
				if(!$non_reservable) {
					
					$this->calcul_resa();
						
					if($this->flag_resa_ici || $this->flag_resa_is_affecte) {
						if($selfservice_resa_ici_todo==4){
							$this->message_resa=$selfservice_resa_ici_todo_msg;
							$non_retournable=1;
						}
					}
					elseif($this->flag_resa_autre_site){
						if($selfservice_resa_loc_todo==4){
							$this->message_resa=$selfservice_resa_loc_todo_msg;
							$non_retournable=1;
						}
					}
					if($non_retournable){
						$this->status=-1;
						return false;
					}					
					
					if($this->expl->pret_idempr) $this->message_del_pret=$this->del_pret();
					
					if ($this->flag_resa_is_affecte){
						$this->message_resa= $selfservice_resa_ici_todo_msg;
					}elseif($this->flag_resa_ici) {	
						switch($selfservice_resa_ici_todo) {			
							case '1':// Valider la rservation
								alert_empr_resa($this->affecte_resa(),0, 1);
							break;		
							default://	A traiter plus tard
								$plus_tard=1;						
							break;	
						}
						$this->message_resa=$selfservice_resa_ici_todo_msg;										
					}
					// Le transfert retour g�re ceci?  elseif($this->flag_resa_origine){}
					elseif($this->flag_resa_autre_site){
						switch($selfservice_resa_loc_todo) {			
							case '1':// Valider la rservation
								alert_empr_resa($this->affecte_resa(),0, 1);	
							break;		
							default://	A traiter plus tard
								$plus_tard=1;						
							break;	
						}						
						$this->message_resa=$selfservice_resa_loc_todo_msg;
					}
				}else{					
					if($this->expl->pret_idempr) $this->message_del_pret=$this->del_pret();
				}	
			}
		}else {
			// c'est une bonne localisation	ou lecteur non localis�:		
			$this->calcul_resa();			
			
			if($this->flag_resa_ici || $this->flag_resa_is_affecte) {	
				if($selfservice_resa_ici_todo==4){
					$this->message_resa=$selfservice_resa_ici_todo_msg;	
					$non_retournable=1;
				}							
			}
			elseif($this->flag_resa_autre_site){	
				if($selfservice_resa_loc_todo==4){					
					$this->message_resa=$selfservice_resa_loc_todo_msg;	
					$non_retournable=1;
				}
			} 
			if($non_retournable){
				$this->status=-1;
				return false;		
			}	
			
			if($this->expl->pret_idempr) $this->message_del_pret=$this->del_pret();				
//			$this->calcul_resa();
			if ($this->flag_resa_is_affecte){
				$this->message_resa= $selfservice_resa_ici_todo_msg;
			}elseif($this->flag_resa_ici) {	
				switch($selfservice_resa_ici_todo) {			
					case '1':// Valider la rservation
						alert_empr_resa($this->affecte_resa(),0, 1);
					break;		
					default://	A traiter plus tard
						$plus_tard=1;						
					break;	
				}
				$this->message_resa=$selfservice_resa_ici_todo_msg;								
			}
			elseif($this->flag_resa_autre_site){
				switch($selfservice_resa_loc_todo) {			
					case '1':// Valider la rservation
						alert_empr_resa($this->affecte_resa(),0, 1);			
					break;		
					default://	A traiter plus tard
						$plus_tard=1;						
					break;	
				}						
				$this->message_resa=$selfservice_resa_loc_todo_msg;
			} else { 
				// pas de r�sa � g�rer
			}
		// fin bonne loc	
		}	
	// fin transfert inactif
	}			
	if($non_retournable){
		$this->status=-1;
		return false;		
	}
	if($plus_tard) {
		// il y a des pieges, on marque comme exemplaire � probl�me dans la localisation qui fait le retour
		$sql = "UPDATE exemplaires set expl_retloc='".$deflt_docs_location."' where expl_cb='".addslashes($this->expl_cb)."' limit 1";
	} else {
		// pas de pi�ges, ou pi�ges r�solus, on d�marque
		$sql = "UPDATE exemplaires set expl_retloc=0 where expl_cb='".addslashes($this->expl_cb)."' limit 1";
	}
	mysql_query($sql);
		
	return true;
	
}
//class end
}		
?>