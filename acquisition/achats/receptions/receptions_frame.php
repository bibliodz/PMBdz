<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: receptions_frame.php,v 1.8 2013-04-16 08:16:41 mbertin Exp $

// d�finition du minimum n�cessaire
$base_path="./../../..";
$base_auth = "ACQUISITION_AUTH";
$base_title = "\$msg[acquisition_menu_title]";    
require_once ("$base_path/includes/init.inc.php");

require_once("$include_path/templates/receptions_frame.tpl.php");
require_once("$class_path/entites.class.php");
require_once("$class_path/actes.class.php");
require_once("$class_path/lignes_actes.class.php");
require_once("$class_path/liens_actes.class.php");
require_once("$class_path/lignes_actes_statuts.class.php");
require_once("$class_path/suggestions.class.php");
require_once("$class_path/suggestions_map.class.php");
require_once("$class_path/mono_display.class.php");
require_once("$class_path/serials.class.php");
require_once("$base_path/catalog/serials/bulletinage/bul_func.inc.php");
require_once("$include_path/bull_info.inc.php");
require_once("$class_path/serial_display.class.php");
require_once("$class_path/explnum.class.php");
require_once("$class_path/expl.class.php");
if ($pmb_prefill_cote) {
	require_once("$base_path/catalog/expl/$pmb_prefill_cote"); 
} else {
	require_once("$base_path/catalog/expl/custom_no_cote.inc.php");
}


function show_delivery_form($msg_client='') {
	
	global $dbh, $msg, $charset;
	global $no, $id_lig, $id_prod, $typ_lig;
	global $recept_deliv_form, $recept_deliv_form_suite;
	global $recept_form_qte_liv, $recept_bt_update, $recept_bt_undo, $previous, $recept_bt_next;

	$form = $recept_deliv_form;
	$lg = new lignes_actes($id_lig);
	$act = new actes($lg->num_acte);
	$fou = new entites($act->num_fournisseur);
	
	$nb_rec = $lg->getNbDelivered($id_lig);
	$nb_sol = $lg->nb-$nb_rec;

	if($nb_sol > 0) {
		$form = str_replace('<!-- bt_update -->', $recept_bt_update, $form);
		$form = str_replace('<!-- qte_liv -->', $recept_form_qte_liv, $form);
	} else {
		$form = str_replace('<!-- qte_liv -->', '', $form);
	}
	
	if ($previous) {
		$tp = unserialize(rawurldecode(stripslashes($previous))); 
		if (is_array($tp) && count($tp)) {
			$form = str_replace('<!-- bt_undo -->', $recept_bt_undo, $form);
			$form = str_replace('!!previous!!', stripslashes($previous), $form);
		} else {
			$previous=0;
		}
	}
	if (!$previous) {
		$form = str_replace('<!-- bt_undo -->', '', $form);
		$form = str_replace('!!previous!!', 0, $form);
	}
	$form = str_replace('<!-- bt_next -->', $recept_bt_next, $form);
	$lgstat_form=lgstat::getHtmlSelect(array(0=>0), FALSE, array('id'=>'sel_lgstat_!!id_lig!!','onchange'=>'recept_upd_lgstat(this.getAttribute("id"));' )); 
	
	$form=str_replace('!!lib_acte!!',
						htmlentities($msg['acquisition_recept_fou'], ENT_QUOTES, $charset)."&nbsp;".htmlentities($fou->raison_sociale,ENT_QUOTES,$charset).'&nbsp;'
						.htmlentities((($act->type_acte)?($msg['acquisition_act_num_dev']):($msg['acquisition_act_num_cde'])),ENT_QUOTES,$charset).htmlentities($act->numero,ENT_QUOTES,$charset),
						$form);
	
	$form = str_replace('!!code!!',htmlentities($lg->code,ENT_QUOTES,$charset),$form);
	$form = str_replace('!!lib!!',nl2br(htmlentities($lg->libelle,ENT_QUOTES,$charset)),$form);
	$form = str_replace('!!qte_cde!!',$lg->nb,$form);
	$form = str_replace('!!qte_rec!!',$nb_rec,$form);
	$form = str_replace('!!qte_sol!!',$nb_sol,$form);	
	$lgstat_form=str_replace("value='".$lg->statut."'","value='".$lg->statut."' selected='selected' ",$lgstat_form);
	$form = str_replace('!!lgstat!!',$lgstat_form,$form);		
	$form = str_replace('!!comment_lg!!',nl2br(htmlentities($lg->commentaires_gestion,ENT_QUOTES,$charset)),$form);
	$form = str_replace('!!comment_lo!!',nl2br(htmlentities($lg->commentaires_opac,ENT_QUOTES,$charset)),$form);
	$form = str_replace('!!id_lig!!',$id_lig,$form);
	$form = str_replace('!!id_prod!!',$id_prod,$form);
	$form = str_replace('!!typ_lig!!',$typ_lig,$form);
	$form = str_replace('!!no!!',$no,$form);
		
	switch($typ_lig) {
		case '1': //notice
			$form.=do_notice_form($id_prod);
			$form.=do_explnum_form();
			$form.=do_expl_form();
			if ($lg->num_acquisition) $form.=do_sugg_form($lg->num_acquisition);
			break;
		case '2': //bulletin
			$form.=do_bull_form($id_prod);
			$form.=do_explnum_form();
			$form.=do_expl_form();
			//if ($lg->num_acquisition) $form.=do_sugg_form($lg->num_acquisition);
			break;
		case '3': //frais 
			break;
		case '4': //abt
			break;
		case '5' : //article
			$form.=do_art_form($id_prod);
			$form.=do_explnum_form();
			if ($lg->num_acquisition) $form.=do_sugg_form($lg->num_acquisition);
			break;
		default : //non catalogu�
			if ($lg->num_acquisition) $form.=do_sugg_form($lg->num_acquisition);
			break;
	}
	
	$form = str_replace('!!msg_client!!', $msg_client,$form);
	print $form.$recept_deliv_form_suite;
}


function do_notice_form($notice_id=0) {
	
	global $recept_deliv_form_notice;
	global $prefix_url_image;
	
	$prefix_url_image='./../../../';
	$form = $recept_deliv_form_notice;
	$md = new mono_display(	$notice_id, 	// $id = id de la notice � afficher
							6, 				// $level :
											//	0 : juste le header (titre  / auteur principal avec le lien si applicable) 
											//	1 : ISBD seul, pas de note, bouton modif, expl, explnum et r�sas
											// 	6 : cas g�n�ral d�taill� avec notes, categ, langues, indexation... + boutons
							'',				// $action = URL associ�e au header
							1, 				// $expl -> affiche ou non les exemplaires associ�s
							'',				// $expl_link
							'', 			// $lien_suppr_cart
							'', 			// $explnum_link
							0, 				// $show_resa
							2,				// $print
							1,				// $show_explnum
							1,				// $show_statut
							'',				// $anti_loop
							0,				// $draggable
							0,				// $no_link
							1,				// $show_opac_hidden_fields
							0				// $ajax_mode
							);
							
	$form = str_replace('!!notice!!',$md->result,$form);
	return $form;
}


function do_art_form($art_id) {

	global $recept_deliv_form_notice;
	global $prefix_url_image;
	
	$prefix_url_image='./../../../';
	$form = $recept_deliv_form_notice;
	$md = new serial_display (	$art_id,					// $id = id de la notice � afficher 
								6,		 					// $level :
															// 0 : juste le header (titre  / auteur principal avec le lien si applicable)
															// 6 : cas g�n�ral d�taill� avec notes, categ, langues, indexation... + boutons 
								'',				 			// $action_serial = URL � atteindre si la notice est une notice chapeau
								'', 						// $action_analysis = URL � atteindre si la notice est un d�pouillement
															// note dans ces deux variables, '!!id!!' sera remplac� par l'id de cette notice
															// les deux liens s'excluent mutuellement, bien sur. 
								'', 						// $action_bulletin
								'', 						// $lien_suppr_cart = lien de suppression de la notice d'un caddie
								'', 						// $lien_explnum
								0,							// $bouton_explnum
								2,							// $print
								1, 							// $show_explnum
								0, 							// $show_statut=
								1, 							// $show_opac_hidden_fields=
								0,							// $draggable
								0, 							// $ajax_mode
								'',							// $anti_loop
								0							// $no_link
								);
								
	$form = str_replace('!!notice!!',$md->result,$form);
	return $form;
}


function do_bull_form($bull_id) {

	global $recept_deliv_form_bull;
	global $prefix_url_image;
	
	$prefix_url_image='./../../../';
	$form = $recept_deliv_form_bull;
	$md = show_bulletinage_info_catalogage($bull_id, true);
	$form = str_replace('!!bulletin!!',$md,$form);
	return $form;
}


function do_expl_form() {
	
	global $recept_deliv_form_expl, $expl_form;
	global $typ_lig, $id_prod;
	global $option_num_auto, $pmb_numero_exemplaire_auto, $pmb_numero_exemplaire_auto_script, $recept_deliv_form_expl_auto;
	global $pmb_droits_explr_localises, $explr_visible_mod;
	
	$form = '';
	$num_auto = 0;
	if (!isset($first)) $first=1;
	
	// visibilit� des exemplaires
	// On ne v�rifie que si l'utilisateur peut cr�er sur au moins une localisation.
	if (!$pmb_droits_explr_localises || $explr_visible_mod) {
	
		$id_notice = 0;
		$id_bulletin = 0;
		
		switch($typ_lig) {
			case '1': //notice
				$id_notice = $id_prod;
				break;
			case '2': //bulletin
				$id_bulletin = $id_prod;
				break;
			default : //non catalogu�
				break;
		}
		
		if ($id_notice) {
			
			$expl_form = $recept_deliv_form_expl;
			if (  ($pmb_numero_exemplaire_auto=='1' || $pmb_numero_exemplaire_auto=='2') && $pmb_numero_exemplaire_auto_script ) {
				$num_auto = 1;
			}

			if ($num_auto==1 && (isset($option_num_auto)) ) {
				$recept_deliv_form_expl_auto = str_replace('!!checked!!', "checked='checked'",$recept_deliv_form_expl_auto);
				$expl_form = str_replace('<!-- option_num_auto -->', $recept_deliv_form_expl_auto, $expl_form); 
			} elseif ($num_auto==1 && !isset($option_num_auto)) {
				$recept_deliv_form_expl_auto = str_replace('!!checked!!', '',$recept_deliv_form_expl_auto);
				$expl_form = str_replace('<!-- option_num_auto -->', $recept_deliv_form_expl_auto, $expl_form); 
			}
			
			$nex = new exemplaire('', 0, $id_notice);
			$expl_form = $nex->expl_form('','');
			$form = $expl_form;
			
		} elseif ($id_bulletin) {
			
			$expl_form = $recept_deliv_form_expl;
			if ( ($pmb_numero_exemplaire_auto=='1' || $pmb_numero_exemplaire_auto=='3') && $pmb_numero_exemplaire_auto_script ) {
				$num_auto = 1 ;
			}
			
			if ($num_auto==1 && (isset($option_num_auto)) ) {
				$recept_deliv_form_expl_auto = str_replace('!!checked!!', "checked='checked'",$recept_deliv_form_expl_auto);
				$expl_form = str_replace('<!-- option_num_auto -->', $recept_deliv_form_expl_auto, $expl_form); 
			} elseif ($num_auto==1 && !isset($option_num_auto)) {
				$recept_deliv_form_expl_auto = str_replace('!!checked!!', '',$recept_deliv_form_expl_auto);
				$expl_form = str_replace('<!-- option_num_auto -->', $recept_deliv_form_expl_auto, $expl_form); 
			}
						
			$nex = new exemplaire('', 0, 0, $id_bulletin);
			$expl_form = $nex->expl_form('','');
			$form = $expl_form;
			
		}
	
	}
	return $form;
}


function add_expl() {

	global $typ_lig, $id_prod;
	global $pmb_droits_explr_localises, $explr_visible_mod;
	global $f_ex_cb, $f_ex_cote, $f_ex_typdoc, $f_ex_location, $f_ex_statut, $f_ex_cstat;
	global $f_ex_note, $f_ex_comment, $f_ex_prix, $f_ex_owner;
	global ${'f_ex_section'.$f_ex_location};
	
	$error = false;
	
	// visibilit� des exemplaires
	// On ne v�rifie que si l'utilisateur peut cr�er sur au moins une localisation.
	if ($pmb_droits_explr_localises && !$explr_visible_mod) return $error; 

	$id_notice = 0;
	$id_bulletin = 0;
	
	switch($typ_lig) {
		case '1': //notice
			$id_notice = $id_prod;
			break;
		case '2': //bulletin
			$id_bulletin = $id_prod;
			break;
		default : //non catalogu�
			break;
	}

	if (!$id_bulletin && !$id_notice) return $error;
		
	//V�rification des champs personalis�s
	$p_perso=new parametres_perso("expl");
	$nberrors=$p_perso->check_submited_fields();
	if ($nberrors) return $error;
		
	if ($id_notice) {
		$nex = new exemplaire($f_ex_cb, 0, $id_notice, 0);
	} else {
		$nex = new exemplaire($f_ex_cb, 0, 0, $id_bulletin);
	}
	
	if ($nex->expl_id) {
		return $error;
	} else {
		$nex->typdoc_id = $f_ex_typdoc;
		$nex->expl_cb = $nex_expl_cb;
		$nex->cote = $f_ex_cote;
		$nex->section_id = ${'f_ex_section'.$f_ex_location};
		$nex->statut_id = $f_ex_statut;
		$nex->location_id = $f_ex_location;
		$nex->codestat_id = $f_ex_cstat;
		$nex->note = $f_ex_note;
		$nex->prix = $f_ex_prix;
		$nex->owner_id = $f_ex_owner;
		$nex->create_date = today();
		$nex->expl_comment = $f_ex_comment;
		if (!$nex->save()) {
			return $error;
		}
		$p_perso->rec_fields_perso($nex->expl_id);
	}
	return !$error;
}


function do_explnum_form() {
	global $recept_deliv_form_explnum;
	return $recept_deliv_form_explnum;
}


function do_sugg_form($id_suggestion) {
	
	global $dbh, $charset;
	global $recept_deliv_form_sugg, $deflt3receptsugstat;
	
	$sug = new suggestions($id_suggestion);
	$tab_orig = $sug->getOrigines();
	$form = '';
	
	//R�cup�ration des noms des cr�ateurs des suggestions
	$list_orig='';
	if (count($tab_orig)) {
		$form = $recept_deliv_form_sugg;
		
		foreach($tab_orig as $orig) {
			switch($orig['type_origine']){
				default:
				case '0' :
				 	$q_user = "SELECT userid, nom, prenom FROM users where userid = '".$orig['origine']."'";
					$r_user = mysql_query($q_user, $dbh);
					$row_user=mysql_fetch_row($r_user);
					$list_orig = htmlentities($row_user[1], ENT_QUOTES, $charset);
					if ($row_user[2]) $list_orig.= ", ".htmlentities($row_user[2], ENT_QUOTES, $charset);					
					$list_orig .= "<br />";
					break;
				case '1' :
				 	$q_empr = "SELECT id_empr, empr_nom, empr_prenom FROM empr where id_empr = '".$orig['origine']."'";
					$r_empr = mysql_query($q_empr, $dbh);
					$row_empr=mysql_fetch_row($r_empr);
					$list_orig.= htmlentities($row_empr[1], ENT_QUOTES, $charset);
					if ($row_empr[2]) $list_orig.= ", ".htmlentities($row_empr[2], ENT_QUOTES, $charset);
					$list_orig .= "<br />";
					break;
				case '2' :
					break;
			}
		}
		$form = str_replace('<!-- origines -->', $list_orig, $form);
		$form = str_replace('!!id_sug!!',$id_suggestion,$form);
		
		$sug_map= new suggestions_map();
		if($sug->statut==$sug_map->getState_ID('ORDERED')) {
			$sel_sugstat = $sug_map->getHtmlStateSelect('ORDERED', array(0=>$sug_map->getStateNameFromId($deflt3receptsugstat)), TRUE, array('name'=>'sel_sugstat'));
		} else {
			$sel_sugstat = $sug_map->getHtmlStateSelect('ORDERED', array(0=>$sug_map->getStateNameFromId($sug->statut)), TRUE, array('name'=>'sel_sugstat'));
		}
		$form = str_replace('<!-- sel_sugstat -->', $sel_sugstat, $form);
	}	
	return $form;	
}


function upload_file() {
	
	global $f_fichier, $id_lig, $typ_lig, $id_prod, $no, $base_path;
	global $id_rep, $path, $up_place, $f_fichier, $f_url, $deflt_upload_repertoire, $pmb_indexation_docnum, $pmb_indexation_docnum_default,$ck_index ;

	switch($typ_lig) {
		case '1': //notice
		case '5' : //article
			$f_notice = $id_prod;
			$f_bulletin = 0;
			break;
		case '2': //bulletin
			$f_notice = 0;
			$f_bulletin = $id_prod;
			break;
		default : //non catalogu�
			break;
	}
	if (($f_notice || $f_bulletin) && $f_fichier) {
		
		$up_place=0;
		$id_rep=0;
		$path = '';
		$ck_index=0;
		if ($deflt_upload_repertoire) {
			$id_rep = $deflt_upload_repertoire;
			if($id_rep) {
				$r = new upload_folder($id_rep);
				$path = $r->repertoire_nom;
				$up_place = 1;
			}
		}
		if ($pmb_indexation_docnum && $pmb_indexation_docnum_default) $ck_index=1;
		$explnum = new explnum();
		$retour = $base_path.'/acquisition/achats/receptions/receptions_frame.php?action=delivery&no='.$no.'&id_lig='.$id_lig.'&typ_lig='.$typ_lig.'&id_prod='.$id_prod;
		$explnum->mise_a_jour($f_notice, $f_bulletin, '', $f_url, $retour,0,0);
	}
	return;
}


function update() {

	global $id_lig, $qte_liv, $previous; 

	$error = false;
	
	if (!$id_lig || $qte_liv<=0) return $error;
	
	$lig_cde = new lignes_actes($id_lig);
	$id_cde = $lig_cde->num_acte;
	$cde = new actes($id_cde);

	$id_liv=0;
	$t_liv = liens_actes::getDeliveries($cde->id_acte, today());
	
	if (count($t_liv) && $t_liv[0]) {
		$id_liv = $t_liv[0];
		$liv = new actes($id_liv);
	} else {
		$liv = new actes();
		$liv->date_acte = today();
		$liv->type_acte = TYP_ACT_LIV;
		$liv->statut = STA_ACT_REC;
		$liv->num_entite = $cde->num_entite;
		$liv->num_fournisseur = $cde->num_fournisseur;
		$liv->num_contact_livr = $cde->num_contact_livr;
		$liv->num_contact_fact = $cde->num_contact_fact;
		$liv->num_exercice = $cde->num_exercice;
		$liv->commentaires = '';
		$liv->reference = '';
		$liv->calc(); //numero
		$liv->save();
	
		$id_liv = $liv->id_acte;
		//cr�ation des liens entre actes
		$la = new liens_actes($id_cde, $id_liv);
		
	}	

	//Cr�ation de la ligne de livraison
	$lig_liv = new lignes_actes();	
	$lig_liv->type_ligne = $lig_cde->type_ligne;
	$lig_liv->num_acte = $id_liv;
	$lig_liv->lig_ref = $lig_cde->id_ligne;
	$lig_liv->num_acquisition = $lig_cde->num_acquisition; 				
	$lig_liv->num_rubrique = $lig_cde->num_rubrique;
	$lig_liv->num_produit = $lig_cde->num_produit;
	$lig_liv->num_type = $lig_cde->num_type;
	$lig_liv->libelle = addslashes($lig_cde->libelle);
	$lig_liv->code = addslashes($lig_cde->code);
	$lig_liv->prix = $lig_cde->prix;
	$lig_liv->tva = $lig_cde->tva;
	$lig_liv->nb = $qte_liv;
	$lig_liv->date_cre = today();
	$lig_liv->statut = $sel_lgstat;
	$lig_liv->remise = $lig_cde->remise;
	$lig_liv->debit_tva = $lig_cde->debit_tva;
	$lig_liv->save();		

/*
	//Mise � jour de la suggestion
	$sug_map = new suggestions_map();
	if ( $lig_cde->num_acquisition != 0 ) {
		$sug = array();
		$sug[] = $lig_cde->num_acquisition;
		$sug_map->doTransition('RECEIVED', $sug);						
	}
*/
	//La commande est-elle sold�e
	$tab_cde = actes::getLignes($id_cde);
	$solde = true;
	while (($row_cde = mysql_fetch_object($tab_cde))) {
		
		if ($row_cde->type_ligne != 3) {	// Frais, non livrables
			$tab_liv = lignes_actes::getLivraisons($row_cde->id_ligne);
			$nb_rec = 0;
			while (($row_liv = mysql_fetch_object($tab_liv))) {
				$nb_rec = $nb_rec + $row_liv->nb;
			}
			if ($row_cde->nb > $nb_rec) {
				$solde = false;
				break;
			}
		}		
	}
	if ($solde) {
		$cde->statut = ($cde->statut & (~STA_ACT_ENC) | STA_ACT_REC); // Cde sold�e >> Statut commande = en cours->sold�
	}
	$cde->update_statut();
	
	if ($previous) {
		$tp = unserialize(rawurldecode(stripslashes($previous)));
	}
	if(!is_array($tp)) $tp = array();
	array_push($tp, $lig_liv->id_ligne);
	
	$previous = addslashes(rawurlencode(serialize($tp)));	
	
	return !$error;
}


function update_sug() {
	
	global $id_sug, $sel_sugstat;
	
	//Mise � jour de la suggestion
	$sug = array();
	$sug[0] = new suggestions($id_sug);
	$sug_map = new suggestions_map();
	$sug_map->doTransition($sug_map->getStateNameFromId($sel_sugstat), $sug[0], TRUE);						
}


function undo() {
	
	global $id_lig, $previous;
	$error=false;
	
	if(!$id_lig) return $error;
	if(!$previous) return $error;
	
	$tp = unserialize(rawurldecode(stripslashes($previous))) ;
	if (!is_array($tp) || !count($tp)) {
		$previous = 0;
		return $error;
	}
	$id_liv = array_pop($tp);
	if (count($tp)) {
		$previous = addslashes(rawurlencode(serialize($tp)));
	} else {
		$previous=0;
	}
		
	$lg_liv = new lignes_actes($id_liv);
	if (!$lg_liv->id_ligne) return $error;
	
	$liv = new actes($lg_liv->num_acte);
	if (!$liv->id_acte) return $error;
	$id_cde = liens_actes::getOrder($id_liv);
	
	$lg_liv->delete();
	$r = actes::getLignes($lg_liv->num_acte);
	if (mysql_num_rows($r)==0) {
		$liv->delete();
	}
	
	$cde = new actes($id_cde);
	$cde->statut = ($cde->statut & !STA_ACT_AVA & !STA_ACT_REC & !STA_ACT_ARC | STA_ACT_ENC) ;
	$cde->update_statut();
	
	return !$error;
}


//Traitement des actions
$error_msg = '';

switch($action) {

	case 'upload_file' :
		upload_file();
		break;
	case 'add_expl' :
		if (!add_expl()) {
			$msg_client = htmlentities($msg['acquisition_recept_add_expl_err'], ENT_QUOTES, $charset); 
		} else {
			$msg_client = htmlentities($msg['acquisition_recept_add_expl_ok'], ENT_QUOTES, $charset);
		}
		show_delivery_form($msg_client);
		break;
	case 'update' :
		if (!update()) {
			$msg_client = htmlentities($msg['acquisition_recept_deliv_err'], ENT_QUOTES, $charset); 
		} else {
			$msg_client = htmlentities($msg['acquisition_recept_deliv_ok'], ENT_QUOTES, $charset); 
		}
		show_delivery_form($msg_client);
		break;
	case 'update_sug' :
		update_sug();
		show_delivery_form();
		break;
	case 'undo' :
		if (!undo()) {
			$msg_client = htmlentities($msg['acquisition_recept_undo_err'], ENT_QUOTES, $charset); 
		} else {
			$msg_client = htmlentities($msg['acquisition_recept_undo_ok'], ENT_QUOTES, $charset); 
		}
		show_delivery_form($msg_client);
		break;
	case 'show' :
	default:
		show_delivery_form();
		break;
		
}
?>
