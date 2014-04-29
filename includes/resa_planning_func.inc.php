<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: resa_planning_func.inc.php,v 1.21 2012-11-05 14:12:12 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($include_path."/mail.inc.php") ;

// defines pour flag affichage info de gestion
define ('NO_INFO_GESTION', 0); // 0 >> aucune info de gestion : liste simple
define ('GESTION_INFO_GESTION', 1); // pour traitement des prévisions
define ('LECTEUR_INFO_GESTION', 2); // pour affichage en fiche lecteur
define ('EDIT_INFO_GESTION', 3); // pour affichage en édition

function planning_list($idempr=0, $idnotice=0, $order="",$info_gestion=NO_INFO_GESTION) {

	global $dbh ;
	global $msg, $charset;
	global $montrerquoi, $f_loc ;
	global $current_module ;
	global $pdflettreresa_priorite_email_manuel;
	global $pmb_transferts_actif;
	global $pmb_lecteurs_localises;
	
	$aff_final = "<script>		
		var ajax_func_to_call=new http_request();
		var f_caller='';
		var param1='';
		var param2='';
		var id;
		function func_callback(p_caller,p_id,p_date,p_param1,p_param2) {
			f_caller = p_caller;
			param1 = p_param1;
			param2 = p_param2;
			id = p_id;
			var url_func = './ajax.php?module=circ&categ=resa_planning&sub=update_resa_planning&id='+p_id+'&date='+p_date+'&param1='+p_param1;
			ajax_func_to_call.request(url_func,0,'',1,func_callback_ret,0,0); 
		}
		
		function func_callback_ret() {
			if (param1 == '1') document.forms[f_caller].elements['resa_date_debut['+id+']'].value = ajax_func_to_call.get_text();
			if (param1 == '2') document.forms[f_caller].elements['resa_date_debut['+id+']'].value = ajax_func_to_call.get_text();
			document.forms[f_caller].elements[param2].value = ajax_func_to_call.get_text();
		}
	</script>";
	
	switch ($info_gestion) {
		case GESTION_INFO_GESTION:
			if (!$montrerquoi) $montrerquoi='all' ;
			$url_gestion = "./circ.php?categ=resa_planning";
			
			$aff_final .= "<form class='form-".$current_module."' name='check_resa_planning' action='".$url_gestion."' method='post' ><div class='left' >" ;
			$aff_final .= "<input type='hidden' name='resa_action' value='' />";  
			$aff_final .= "<span class='usercheckbox'><input type='radio' name='montrerquoi' value='all' id='all' onclick='this.form.submit();' ";
			if ($montrerquoi=='all') {
				$aff_final .= "checked" ;
				$clause = "";
			}
			$aff_final .= "><label for='all'>".htmlentities($msg['resa_show_all'], ENT_QUOTES, $charset)."</label></span>&nbsp;<span class='usercheckbox'><input type='radio' name='montrerquoi' value='validees' id='validees' onclick='this.form.submit();' ";
			
			if ($montrerquoi=='validees') {
				$aff_final .= "checked" ;
				$clause = "and resa_validee='1' ";
			}
			$aff_final .= "><label for='validees'>".htmlentities($msg['resa_show_validees'], ENT_QUOTES, $charset)."</label></span>&nbsp;<span class='usercheckbox'><input type='radio' name='montrerquoi' value='invalidees' id='invalidees' onclick='this.form.submit();' ";
			
			if ($montrerquoi=='invalidees') {
				$aff_final .= "checked" ;
				$clause = "and resa_validee='0' ";
			}
			$aff_final.= "><label for='invalidees'>".htmlentities($msg['resa_show_invalidees'], ENT_QUOTES, $charset)."</label></span>&nbsp;<span class='usercheckbox'><input type='radio' name='montrerquoi' value='valid_noconf' id='valid_noconf' onclick='this.form.submit();' ";
			
			if ($montrerquoi=='valid_noconf') {
				$aff_final .= "checked" ;
				$clause = "and resa_validee='1' and resa_confirmee='0' ";
			}
			$aff_final .= "><label for='valid_noconf'>".htmlentities($msg['resa_show_non_confirmees'], ENT_QUOTES, $charset)."</label></span></div>";
		
			//la liste de sélection de la localisation
			$aff_final .= "<div class='row'>".$msg["transferts_circ_resa_lib_localisation"];
			$aff_final .= "<select name='f_loc' onchange='document.check_resa_planning.submit();'>";
			$res = mysql_query("SELECT idlocation, location_libelle, count(*) as nb FROM docs_location join empr on empr_location=idlocation join resa_planning on resa_idempr = id_empr group by idlocation, location_libelle order by location_libelle ");
			$aff_final .= "<option value='0'>".$msg["all_location"]."</option>";
			//on parcours la liste des options
			while ($value = mysql_fetch_array($res)) {
				//debut de l'option
				$aff_final .= "<option value='".$value[0]."'";
				if ($value[0]==$f_loc)
					//c'est l'option par défaut
					$aff_final .= " selected";
				
				//fin de l'option
				$aff_final .= ">".$value[1]." (".$value[2].")</option>";
			}
			$aff_final .= "</select>";
			if ($f_loc) {
				$clause .= " AND empr_location='".$f_loc."' ";
			}
			
			$aff_final .= "</div><div class='row'>&nbsp;</div>" ;
			break;
		case LECTEUR_INFO_GESTION:
			break;
		case EDIT_INFO_GESTION:
			break;
		default:
		case NO_INFO_GESTION:
			break;
	}
	
	if (!$order) $order="empr_nom, empr_prenom, tit, resa_idnotice, resa_date " ;
	
	$q = "select id_resa, resa_idnotice, resa_date, resa_date_debut, resa_date_fin, resa_validee, resa_confirmee, resa_idempr, ";
	$q.= "trim(concat(if(series.serie_name <>'', if(notices.tnvol <>'', concat(series.serie_name,', ',notices.tnvol,'. '), concat(series.serie_name,'. ')), if(notices.tnvol <>'', concat(notices.tnvol,'. '),'')),notices.tit1)) as tit, ";
	$q.= "concat(empr_nom,', ',empr_prenom) as empr_nom_prenom, id_empr, empr_cb, location_libelle,";
	$q.= "IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_debut, '".$msg["format_date"]."') as aff_resa_date_debut, if(resa_date_fin='0000-00-00', '', date_format(resa_date_fin, '".$msg["format_date"]."')) as aff_resa_date_fin, date_format(resa_date, '".$msg["format_date"]."') as aff_resa_date " ;
	$q.= "FROM resa_planning LEFT JOIN notices ON resa_idnotice = notices.notice_id LEFT JOIN series ON notices.tparent_id = series.serie_id, empr, docs_location ";
	$q.= "where resa_idempr = id_empr ";
	if ($clause) $q.= $clause;
	$q.= "and idlocation = empr_location ";
	if ($idnotice) $q.= "and notice_id = '".$idnotice."' ";
	if ($idempr) $q.= "and id_empr = '".$idempr."' ";
	$q.= "order by ".$order;

	$r = mysql_query($q) or die("Erreur SQL !<br />".$q."<br />".mysql_error()); 
	
	if (!mysql_num_rows($r)) {
		switch ($info_gestion) {
			case GESTION_INFO_GESTION:
				$aff_final .= "</form>" ;
				break;
			case LECTEUR_INFO_GESTION:
				break;
			case EDIT_INFO_GESTION:
				break;
			default:
			case NO_INFO_GESTION:
				break;
		}
		return $aff_final ;
	}
	
	switch ($info_gestion) {
		case GESTION_INFO_GESTION:
			break;
		case LECTEUR_INFO_GESTION:
			$url_gestion = "./circ.php?categ=pret";
			$aff_final .= "<form class='form-".$current_module."' name='check_resa_planning' action='".$url_gestion."' method='post' style='display:none;' >";
			$aff_final.= "</form>";
			break;
		case EDIT_INFO_GESTION:
			break;
		default:
		case NO_INFO_GESTION:
			break;
	}
	
	$aff_final .= "	<script type='text/javascript' src='./javascript/sorttable.js'></script>
				<table width='100%' class='sortable'>
					<tr>";
	
	switch ($info_gestion) {
		case GESTION_INFO_GESTION:
		case EDIT_INFO_GESTION:
			$aff_final .= "<th>".htmlentities($msg['233'], ENT_QUOTES, $charset)."</th>
				<th>".htmlentities($msg['empr_nom_prenom'], ENT_QUOTES, $charset)."</th>
				".($pmb_lecteurs_localises ? "<th>".htmlentities($msg["empr_location"], ENT_QUOTES, $charset)."</th>" :"");
			break;
		case LECTEUR_INFO_GESTION:
			$aff_final .= "<th>".htmlentities($msg['233'], ENT_QUOTES, $charset)."</th>";
			break;
		default:
		case NO_INFO_GESTION:
			$aff_final .= "<th>".htmlentities($msg['empr_nom_prenom'], ENT_QUOTES, $charset)."</th>
				".($pmb_lecteurs_localises ? "<th>".htmlentities($msg["empr_location"], ENT_QUOTES, $charset)."</th>" :"");
			break;
	}
	
	$aff_final .= "<th>".htmlentities($msg['374'], ENT_QUOTES, $charset)."</th>
		<th>".htmlentities($msg['resa_planning_date_debut'], ENT_QUOTES, $charset)."</th>
		<th>".htmlentities($msg['resa_planning_date_fin'], ENT_QUOTES, $charset)."</th>";

	switch ($info_gestion) {
		case GESTION_INFO_GESTION:
 			$aff_final .= "<th class='sorttable_nosort'>".htmlentities($msg['resa_validee'], ENT_QUOTES, $charset)."</th>
				<th class='sorttable_nosort'>".htmlentities($msg['resa_confirmee'], ENT_QUOTES, $charset)."</th>";
			if ($pmb_transferts_actif=="1")
				$aff_final .= "<th>" . $msg["resa_loc_retrait"] . "</th>";
			$aff_final .= "<th class='sorttable_nosort'>".htmlentities($msg['resa_selectionner'], ENT_QUOTES, $charset)."</th>" ;
			break;
		case LECTEUR_INFO_GESTION:
			$aff_final .= "
				<th class='sorttable_nosort'>".htmlentities($msg['resa_validee'], ENT_QUOTES, $charset)."</th>
				<th class='sorttable_nosort'>".htmlentities($msg['resa_confirmee'], ENT_QUOTES, $charset)."</th>";
			if ($pmb_transferts_actif=="1")
				$aff_final .= "<th>" . $msg["resa_loc_retrait"] . "</th>";
				$aff_final .= "<th class='sorttable_nosort'>" . $msg["resa_suppr_th"] . "</th>" ;
			break;
		case EDIT_INFO_GESTION:
 			$aff_final .= "<th class='sorttable_nosort'>".htmlentities($msg['resa_validee'], ENT_QUOTES, $charset)."</th>
				<th class='sorttable_nosort'>".htmlentities($msg['resa_confirmee'], ENT_QUOTES, $charset)."</th>";
			if ($pmb_transferts_actif=="1")
				$aff_final .= "<th>" . $msg["resa_loc_retrait"] . "</th>";
			break;
		default:
		case NO_INFO_GESTION:
			break;
	}
	$aff_final .= "</tr>";
	$odd_even=0;

	while ($data = mysql_fetch_object($r)) {
	
		if ($odd_even==0) {
			$aff_final .= "\n<tr class='odd' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='odd'\">";
			$odd_even=1;
		} else if ($odd_even==1) {
			$aff_final .= "\n<tr class='even' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='even'\">";
			$odd_even=0;
		}
		switch ($info_gestion) {
			case GESTION_INFO_GESTION:
			case EDIT_INFO_GESTION:
				if (SESSrights & CATALOGAGE_AUTH) $link = "<a href='./catalog.php?categ=isbd&id=".$data->resa_idnotice."'>".htmlentities($data->tit, ENT_QUOTES, $charset)."</a>";
				else $link = htmlentities($data->tit, ENT_QUOTES, $charset);
				$aff_final.= "<td><b>".$link."</b></td>";
				if (SESSrights & CIRCULATION_AUTH) $aff_final .= "<td><a href=\"./circ.php?categ=pret&form_cb=".rawurlencode($data->empr_cb)."\">".htmlentities($data->empr_nom_prenom,  ENT_QUOTES, $charset)."</a></td>"; 
				else $aff_final .= "<td>".htmlentities($data->empr_nom_prenom, ENT_QUOTES, $charset)."</td>";
				if ($pmb_lecteurs_localises) $aff_final.= "<td>".$data->location_libelle."</td>";   		
				break;
			case LECTEUR_INFO_GESTION:
				if (SESSrights & CATALOGAGE_AUTH) $link = "<a href='./catalog.php?categ=isbd&id=".$data->resa_idnotice."'>".htmlentities($data->tit, ENT_QUOTES, $charset)."</a>";
				else $link = htmlentities($data->tit, ENT_QUOTES, $charset);
				$aff_final.= "<td><b>".$link."</b></td>";
				break;
			default:
			case NO_INFO_GESTION:
				if (SESSrights & CIRCULATION_AUTH) $aff_final .= "<td><a href=\"./circ.php?categ=pret&form_cb=".rawurlencode($data->empr_cb)."\">".htmlentities($data->empr_nom_prenom,  ENT_QUOTES, $charset)."</a></td>"; 
				else $aff_final .= "<td>".htmlentities($data->empr_nom_prenom, ENT_QUOTES, $charset)."</td>";
				if ($pmb_lecteurs_localises) $aff_final.= "<td>".$data->location_libelle."</td>"; 
				break;
		}
		    
		$aff_final.= "<td style='text-align:center;'>".$data->aff_resa_date."</td>"; 
		
		switch ($info_gestion) {
			case GESTION_INFO_GESTION:
			case LECTEUR_INFO_GESTION:
				$aff_final.= "<input type='hidden' id='id_empr[".$data->id_resa."]' name='id_empr[".$data->id_resa."]' value='".$data->id_empr."' />";
				if($data->resa_validee) {
					$aff_final.= "<td class='sorttable_mmdd' style='text-align:center;'>".$data->aff_resa_date_debut."</td>";
					$aff_final.= "<td class='sorttable_mmdd' style='text-align:center;'>".$data->aff_resa_date_fin." </td>";
					$aff_final.= "<td style='text-align:center;'><b>X</b></td>";
				} else {
					$aff_final .= "<td style='text-align:center;'>";
					$aff_final .= "<input type='hidden' id='resa_date_debut[".$data->id_resa."]' name='resa_date_debut[".$data->id_resa."]' value='".$data->aff_resa_date_debut."' />";
					$resa_date_debut = str_replace("-", "", $data->resa_date_debut);
					$aff_final .= "<input type='hidden' id='form_resa_date_debut_".$data->id_resa."' name='form_resa_date_debut_".$data->id_resa."' value='".$resa_date_debut."' />";
					$aff_final .= "<input type='button' class='bouton' sorttable_customkey='".$data->aff_resa_date_debut."' onclick=\"openPopUp('./select.php?what=calendrier&caller=check_resa_planning&date_caller=".$resa_date_debut."&param1=form_resa_date_debut_".$data->id_resa."&param2=form_resa_date_debut_lib_".$data->id_resa."&auto_submit=NO&date_anterieure=YES&func_to_call=func_callback&id=".$data->id_resa."&sub_param1=1', 'resa_date_debut', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes'); \" value='".$data->aff_resa_date_debut."' name='form_resa_date_debut_lib_".$data->id_resa."'>";
					$aff_final .= "</td>";
					
					$aff_final .= "<td style='text-align:center;'>";
					$aff_final .= "<input type='hidden' id='resa_date_fin[".$data->id_resa."]' name='resa_date_fin[".$data->id_resa."]' value='".$data->aff_resa_date_fin."' />";
					$resa_date_fin = str_replace("-", "", $data->resa_date_fin);
					$aff_final .= "<input type='hidden' id='form_resa_date_fin_".$data->id_resa."' name='form_resa_date_fin_".$data->id_resa."' value='".$resa_date_fin."' />";
					$aff_final .= "<input type='button' class='bouton' sorttable_customkey='".$data->aff_resa_date_fin."' onclick=\"openPopUp('./select.php?what=calendrier&caller=check_resa_planning&date_caller=".$resa_date_fin."&param1=form_resa_date_fin_".$data->id_resa."&param2=form_resa_date_fin_lib_".$data->id_resa."&auto_submit=NO&date_anterieure=YES&func_to_call=func_callback&id=".$data->id_resa."&sub_param1=2', 'resa_date_fin', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\" value='".$data->aff_resa_date_fin."' name='form_resa_date_fin_lib_".$data->id_resa."'>";
					$aff_final .= "</td>";
							
					$aff_final.= "<td></td>";
				}
				if($data->resa_confirmee) $aff_final.= "<td style='text-align:center;'><b>X</b></td>";
					else $aff_final.= "<td></td>";
				if ($pmb_transferts_actif=="1") {
					$loc_retrait = resa_planning_loc_retrait($data->id_resa);
					$rqt = "SELECT location_libelle FROM docs_location WHERE idlocation='".$loc_retrait."'";
					$libloc = @mysql_result(mysql_query($rqt),0);
					$aff_final .= "<td>".$libloc."</td>";
				}
				break;
			case EDIT_INFO_GESTION:
				$aff_final.= "<td class='sorttable_mmdd' style='text-align:center;'>".$data->aff_resa_date_debut."</td>";
				$aff_final.= "<td class='sorttable_mmdd' style='text-align:center;'>".$data->aff_resa_date_fin." </td>";
				if($data->resa_validee) {
					$aff_final.= "<td style='text-align:center;'><b>X</b></td>";
				} else {
					$aff_final.= "<td></td>";
				} 
				if($data->resa_confirmee) {
					$aff_final.= "<td style='text-align:center;'><b>X</b></td>";
				} else {
					$aff_final.= "<td></td>";					
				}
				if ($pmb_transferts_actif=="1") {
					$loc_retrait = resa_planning_loc_retrait($data->id_resa);
					$rqt = "SELECT location_libelle FROM docs_location WHERE idlocation='".$loc_retrait."'";
					$libloc = @mysql_result(mysql_query($rqt),0);
					$aff_final .= "<td>".$libloc."</td>";
				}
				break;
			default:
			case NO_INFO_GESTION:
				$aff_final.= "<td style='text-align:center;'>".$data->aff_resa_date_debut."</td>";
				$aff_final.= "<td style='text-align:center;'>".$data->aff_resa_date_fin." </td>";
				break;
		}
				
		switch ($info_gestion) {
			case GESTION_INFO_GESTION:
				$aff_final .= "\n<td style='text-align:center;'><input type='checkbox' id='resa_check[".$data->id_resa."]' name='resa_check[]' value='".$data->id_resa."' /></td>" ;					
				break;
			case LECTEUR_INFO_GESTION:
				$aff_final .= "\n<td style='text-align:center;'><input type='button' id='resa_supp' name='resa_supp' class='bouton' value='X' onclick=\"document.location='./circ.php?categ=pret&sub=suppr_resa_planning_from_fiche&action=suppr_resa&id_resa=".$data->id_resa."&id_empr=".$idempr."';\" /></td>" ;
				break;
			case EDIT_INFO_GESTION:
				break;
			default:
			case NO_INFO_GESTION:
				break;
		}
		$aff_final.= "</tr>";
	}
	$aff_final.= "</table>";
	$aff_final.= "<div class='row'></div>";
	
	switch ($info_gestion) {
		case GESTION_INFO_GESTION:
			$aff_final .= "	<div class='right'>
						<input type='button' id='bt_chk' class='bouton' value='".$msg['resa_tout_cocher']."' onClick=\"checkAll('check_resa_planning', 'resa_check', check); return false;\" />
					</div>
					<div class='row'>&nbsp;</div>
					<div class='left' >
						<input type='button' class='bouton' value='".$msg['acquisition_sug_bt_val']."' onclick=\"this.form.resa_action.value='val_resa'; this.form.submit();\"/>&nbsp;
						<input type='button' class='bouton' value='".$msg["resa_modifier"]."' onclick=\"this.form.resa_action.value='modif_resa'; this.form.submit();\"/>&nbsp;
						<input type='button' class='bouton' value='".$msg['resa_impression_confirmation']."' onclick=\"this.form.resa_action.value='conf_resa'; this.form.submit();\"/>&nbsp;
					</div>
					<div class='right' >
						<input type='button' class='bouton' value='".$msg['resa_valider_suppression']."'  onclick=\"this.form.resa_action.value='suppr_resa'; this.form.submit();\" />						
					</div>
					<div class='row'></div>
				</form>" ;
			$aff_final.= "
				<script type='text/javascript'>
					var check = true;
				
					//Coche et décoche les éléments de la liste
					function checkAll(the_form, the_objet, do_check) {
					
						var elts = document.forms[the_form].elements[the_objet+'[]'] ;
						var elts_cnt  = (typeof(elts.length) != 'undefined')
					              ? elts.length
					              : 0;
					
						if (elts_cnt) {
							for (var i = 0; i < elts_cnt; i++) {
								elts[i].checked = do_check;
							} 
						} else {
							elts.checked = do_check;
						}
						if (check == true) {
							check = false;
							document.getElementById('bt_chk').value = '".$msg['acquisition_sug_uncheckAll']."';
						} else {
							check = true;
							document.getElementById('bt_chk').value = '".$msg['acquisition_sug_checkAll']."';	
						}
						return true;
					}
				
				</script>";
			break;
		case LECTEUR_INFO_GESTION:
			break;
		case EDIT_INFO_GESTION:
			break;
		default:
		case NO_INFO_GESTION:
			break;
	}

	return $aff_final ;
}

function resa_planning_loc_retrait($id_resa) {
	global $transferts_choix_lieu_opac, $transferts_site_fixe;

	$res_trans = 0; 
	
	switch ($transferts_choix_lieu_opac) {			
		case "2":
			//retrait de la resa sur lieu fixé
			$res_trans = $transferts_site_fixe;
		break;			
		case "3":
			//retrait de la resa sur lieu exemplaire
			//==>on fait rien !
		break;
		case "1":
		default:
			//retrait de la resa sur lieu lecteur
			//on recupere la localisation de l'emprunteur
			$rqt = "SELECT empr_location FROM resa_planning INNER JOIN empr ON resa_idempr = id_empr WHERE id_resa='".$id_resa."'";
			$res = mysql_query($rqt);
			$res_trans = mysql_result($res,0) ;
		break;		
	
	}
	
	return $res_trans;

}

//Affichage entete réservation avec verification numero lecteur
function aff_entete($id_empr=0) {
	
	global $msg,$dbh, $layout_begin;
	
	if (!$id_empr) {
		// pas d'id empr, quelque chose ne va pas
		error_message($msg[350], $msg[54], 1 , './circ.php');
		break;
	} else {
		// récupération nom emprunteur
		$requete = "SELECT empr_nom, empr_prenom, empr_cb FROM empr WHERE id_empr='".$id_empr."' LIMIT 1";
		$result = @mysql_query($requete, $dbh);
		if(!mysql_num_rows($result)) {
			// pas d'emprunteur correspondant, quelque chose ne va pas
			error_message($msg[350], $msg[54], 1 , './circ.php');
			break;
		} else {
			$empr = mysql_fetch_object($result);
			$name = $empr->empr_prenom;
			$name ? $name .= ' '.$empr->empr_nom : $name = $empr->empr_nom;
			//echo window_title($database_window_title.$name.$msg[1003].$msg[352]);
			$layout_begin = str_replace('!!nom_lecteur!!', $name, $layout_begin);
			$layout_begin = str_replace('!!cb_lecteur!!', $empr->empr_cb, $layout_begin);
			return $layout_begin;
		}
	}
}

//Affichage des réservations planifiées sur le document courant par le lecteur courant
function doc_planning_list($id_empr, $id_notice) {

	global $msg, $dbh;

	$requete3 = "SELECT id_resa, resa_idempr, resa_idnotice, resa_date, resa_date_debut, resa_date_fin, resa_validee, IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_fin, '".$msg["format_date_sql"]."') as aff_date_fin ";
	$requete3.= "FROM resa_planning ";
	$requete3.= "WHERE resa_idempr='".$id_empr."' and resa_idnotice='".$id_notice."' ";
	$result3 = mysql_query($requete3, $dbh);
	
	if (mysql_num_rows($result3)) $message_resa = '<br /><b>'.$msg['resa_planning_enc'].'</b>'; 
	while ($resa = mysql_fetch_array($result3)) {
		$id_resa = $resa['id_resa'];
		$resa_idempr = $resa['resa_idempr'];
		$resa_idnotice = $resa['resa_idnotice'];
		$resa_date = $resa['resa_date'];
		$resa_date_debut = $resa['resa_date_debut'];
		$resa_date_fin = $resa['resa_date_fin'];
		$resa_validee = $resa['resa_validee'];
		$message_resa.= "<blockquote><b>".$msg['resa_planning_date_debut']."</b> ".formatdate($resa_date_debut)."&nbsp;<b>".$msg['resa_planning_date_fin']."</b> ".formatdate($resa_date_fin)."&nbsp;" ;
		if (!$resa['perimee']) {
			if ($resa['resa_validee'])  $message_resa.= " ".$msg['resa_validee'] ;
				else $message_resa.= " ".$msg['resa_attente_validation']." " ;
		} else  $message_resa.= " ".$msg['resa_overtime']." " ;
		$message_resa.= "</blockquote>" ;
	}

	return $message_resa;

}

function alert_empr_resa_planning($id_resa=0, $id_empr_concerne=0) {

	global $dbh;
	global $msg, $charset;
	global $PMBuserid, $PMBuseremail, $PMBuseremailbcc ;
	global $pdflettreresa_priorite_email ;
	global $pdflettreresa_before_list , $pdflettreresa_madame_monsieur, $pdflettreresa_after_list, $pdflettreresa_fdp;
	global $biblio_name, $biblio_email ;
	global $biblio_adr1, $biblio_adr2, $biblio_cp, $biblio_town, $biblio_phone ; 
	global $pdflettreresa_priorite_email_manuel;
	
	if ($pdflettreresa_priorite_email_manuel==3) return ;
	
	$query = "select distinct "; 
	$query.= "trim(notices.tit1) as tit, ";  
	$query.= "date_format(resa_date_fin, '".$msg["format_date"]."') as aff_resa_date_fin, ";
	$query.= "date_format(resa_date_debut, '".$msg["format_date"]."') as aff_resa_date_debut, ";
	$query.= "empr_prenom, empr_nom, empr_cb, empr_mail ";  
	$query.= "from resa_planning LEFT JOIN notices ON resa_idnotice = notices.notice_id, empr ";
	$query.= "where id_resa in (".$id_resa.") and resa_idempr=id_empr ";
	if ($id_empr_concerne) $query .= "and id_empr=$id_empr_concerne ";

	$result = mysql_query($query, $dbh);
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=".$charset."\n";

	$var = "pdflettreresa_fdp";
	eval ("\$pdflettreresa_fdp=\"".$$var."\";");
	
	// le texte après la liste des ouvrages en résa
	$var = "pdflettreresa_after_list";
	eval ("\$pdflettreresa_after_list=\"".$$var."\";");
		
	// le texte avant la liste des ouvrages en réservation
	$var = "pdflettreresa_before_list";
	eval ("\$pdflettreresa_before_list=\"".$$var."\";");
	
	// le "Madame, Monsieur," ou tout autre truc du genre "Cher adhérent,"
	$var = "pdflettreresa_madame_monsieur";
	eval ("\$pdflettreresa_madame_monsieur=\"".$$var."\";");

	while ($empr=mysql_fetch_object($result)) {
		$id_empr = $empr->id_empr ;
		if (($pdflettreresa_priorite_email_manuel==1 || $pdflettreresa_priorite_email_manuel==2) && $empr->empr_mail) {
			$to = $empr->empr_prenom." ".$empr->empr_nom." <".$empr->empr_mail.">";
			$output_final = "<html><body>" ;
			$texte_madame_monsieur=str_replace("!!empr_name!!", $empr->empr_nom,$pdflettreresa_madame_monsieur);
			$texte_madame_monsieur=str_replace("!!empr_first_name!!", $empr->empr_prenom,$texte_madame_monsieur);
			$output_final .= $texte_madame_monsieur.' <br />'.$pdflettreresa_before_list ;
			$output_final .= '<hr /><strong>'.$empr->tit.'</strong>';
			$output_final .= '<br />' ;
			$output_final .= $msg['resa_planning_date_debut'].'  '.$empr->aff_resa_date_debut.'  '.$msg['resa_planning_date_fin'].'  '.$empr->aff_resa_date_fin ;
			
			$output_final .= '<hr />'.$pdflettreresa_after_list.' <br />'.$pdflettreresa_fdp."<br /><br />".mail_bloc_adresse() ;
			$output_final .= '</body></html>';
			$res_envoi=mailpmb($empr->empr_prenom.' '.$empr->empr_nom, $empr->empr_mail,$msg['mail_obj_resa_validee'],$output_final,$biblio_name, $biblio_email, $headers, "", $PMBuseremailbcc);
			if (!$res_envoi || $pdflettreresa_priorite_email_manuel==2) {
				print "<script type='text/javascript'>openPopUp('./pdf.php?pdfdoc=lettre_resa_planning&id_resa=$id_resa', 'lettre_confirm_resa".$id_resa."', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes, scrollbars=yes');</script>";
			}
		} elseif ($pdflettreresa_priorite_email_manuel!=3) {
			print "<script type='text/javascript'>openPopUp('./pdf.php?pdfdoc=lettre_resa_planning&id_resa=$id_resa', 'lettre_confirm_resa".$id_resa."', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes, scrollbars=yes');</script>";
		}
		$rqt_maj = "update resa_planning set resa_confirmee=1 where id_resa in (".$id_resa.") " ;
		if ($id_empr_concerne) $rqt_maj .= " and resa_idempr=$id_empr_concerne ";
		mysql_query($rqt_maj, $dbh);
	} // end while
}


?>
