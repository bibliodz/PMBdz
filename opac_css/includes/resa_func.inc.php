<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: resa_func.inc.php,v 1.35 2013-10-25 13:31:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/quotas.class.php");

function resa_list ($idnotice=0, $idbulletin=0, $idempr=0, $order="", $where = "", $info_gestion=0, $url_gestion="") {
	
global $dbh ;
global $msg;
global $montrerquoi ;
global $current_module ;

if (!$montrerquoi) $montrerquoi='all' ;

if (!$order) $order="tit, resa_idnotice, resa_idbulletin, resa_date" ;

$sql="SELECT resa_idnotice, resa_idbulletin, resa_date, resa_date_debut, resa_date_fin, resa_cb, resa_idempr, empr_nom, empr_prenom, empr_cb, ";
$sql.=" trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date, concat(' (',mention_date,')') ,''))) as tit, id_resa, ";
$sql.=" IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_debut, '".$msg["format_date"]."') as aff_resa_date_debut, if(resa_date_fin='0000-00-00', '', date_format(resa_date_fin, '".$msg["format_date"]."')) as aff_resa_date_fin, date_format(resa_date, '".$msg["format_date"]."') as aff_resa_date " ;
$sql.=" FROM (((resa LEFT JOIN notices AS notices_m ON resa_idnotice = notices_m.notice_id ) ";
$sql.=" LEFT JOIN bulletins ON resa_idbulletin = bulletins.bulletin_id) ";
$sql.=" LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), ";
$sql.=" empr ";
if ($idempr) $sql.=" where id_empr='$idempr' AND resa_idempr = id_empr ";
	elseif ($idnotice || $idbulletin) $sql.=" where resa_idnotice = '$idnotice' AND resa_idbulletin='$idbulletin' and resa_idempr = id_empr ";
		else $sql.=" where resa_idempr = id_empr ";
if ($where) $sql.=" AND ".$where ;
$sql.=" group by resa_idnotice, resa_idbulletin, resa_idempr ";
$sql.=" order by ".$order ;

$req = mysql_query($sql) or die("Erreur SQL !<br />".$sql."<br />".mysql_error()); 

if ($info_gestion) {
	$aff_final .= "<br /><form class='form-$current_module' name='check_resa' action='$url_gestion' method='post'>" ;
	$aff_final .= $msg['resa_show_all']."<input type='radio' name='montrerquoi' value='all' onclick='this.form.submit();' ";
	if ($montrerquoi=='all') $aff_final .= "checked" ;
	$aff_final .= ">&nbsp;&nbsp;&nbsp;".$msg['resa_show_validees']."<input type='radio' name='montrerquoi' value='validees' onclick='this.form.submit();' ";
	if ($montrerquoi=='validees') $aff_final .= "checked" ;
	$aff_final .= ">&nbsp;&nbsp;&nbsp;".$msg['resa_show_invalidees']."<input type='radio' name='montrerquoi' value='invalidees' onclick='this.form.submit();' ";
	if ($montrerquoi=='invalidees') $aff_final .= "checked" ;
	$aff_final .= "><br />" ;
	jscript_checkbox() ;
	}

if (!mysql_num_rows($req)) {
	if ($info_gestion) $aff_final .= "</form>" ;
	return $aff_final ;
	}
	
$aff_final .= "<table width='100%'>";
$aff_final .= "<tr>" ;
if (!$idnotice && !$idbulletin) $aff_final .= "<th>$msg[233]</th>" ;
if (!$idempr) $aff_final .= "<th>$msg[empr_nom_prenom]</th>";

$aff_final .= "<th>$msg[366]</th>
 	<th>$msg[374]</th>
 	<th>$msg[654]</th>
 	<th>$msg[resa_date_fin]</th>";
if ($info_gestion) $aff_final .= "<th>$msg[resa_validee]</th><th>$msg[resa_selectionner]</th>" ;
$aff_final .= "</tr>";
$odd_even=0;
$precedenteresa_idnotice = 0 ;
$precedenteresa_idbulletin = 0 ;
$rank = 0 ;
while ($data = mysql_fetch_array($req)) {
	$resa_idnotice = $data['resa_idnotice'];
	$resa_idbulletin = $data['resa_idbulletin'];
	$resa_idempr = $data['resa_idempr'] ;
	if (($resa_idnotice != $precedenteresa_idnotice) || ($resa_idbulletin != $precedenteresa_idbulletin)) {
		$rank=1;
		$precedenteresa_idnotice=$resa_idnotice;
		$precedenteresa_idbulletin=$resa_idbulletin;
		
		// d�termination de la date � afficher dans la case retour pour le rang 1
		// disponible, r�serv� ou date de retour du premier exemplaire
		
		// on compte le nombre total d'exemplaires r�servables et visibles pour la notice
		$query = "select count(1) from exemplaires, docs_statut";
		if ($resa_idnotice)  $query .= " where expl_notice=".$resa_idnotice;
			elseif ($resa_idbulletin) $query .= " where expl_bulletin=".$resa_idbulletin;
		$query .= " and expl_statut=idstatut and statut_allow_resa=1 and statut_visible_opac=1";
		$tresult = @mysql_query($query, $dbh);
		$total_ex = mysql_result($tresult, 0, 0);

		// on compte le nombre d'exemplaires sortis
		$query = "select count(1) as qte from exemplaires e, pret p";
		if ($resa_idnotice) $query .= " where e.expl_notice=".$resa_idnotice;
			elseif ($resa_idbulletin) $query .= " where e.expl_bulletin=".$resa_idbulletin;
		$query .= " and p.pret_idexpl=e.expl_id";
		$tresult = @mysql_query($query, $dbh);
		$total_sortis = mysql_result($tresult, 0, 0);

		// on en d�duit le nombre d'exemplaires disponibles
		$total_dispo = $total_ex - $total_sortis;

		if($rank <= $total_dispo) {
			// un exemplaire est disponible pour le r�servataire (affichage : disponible)
			$situation = "<strong>$msg[359]</strong>";
			} else {
				if($total_dispo) {
					// un ou des exemplaires sont disponibles, mais pas pour ce r�servataire (affichage : reserv�)
					$situation = 'reserv�';
					} else {
						// rien n'est disponible, on trouve la date du premier retour
						$query = "select date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour from pret p, exemplaires e ";
						if ($resa_idnotice) $query .= " where e.expl_notice=".$resa_idnotice;
							elseif ($resa_idbulletin) $query .= " where e.expl_bulletin=".$resa_idbulletin;
						$query .= " and e.expl_id=p.pret_idexpl";
						$query .= " order by p.pret_retour limit 1";
						$tresult = mysql_query($query, $dbh);
						if (mysql_num_rows($tresult)) $situation = mysql_result($tresult, 0, 0);
							else $situation = $msg['resa_no_expl'];
						}
				}
		} else {
			$rank++;
			$situation='';
			}
	
	if ( ($montrerquoi=='validees' && $data['resa_cb']) || ($montrerquoi=='invalidees' && !$data['resa_cb']) || ($montrerquoi=='all') ) {
		$rank = recupere_rang($resa_idempr, $resa_idnotice, $resa_idbulletin) ;
		// on affiche les r�sultats 
		if ($odd_even==0) {
			$aff_final .= "\n<tr class='odd'>";
			$odd_even=1;
			} else if ($odd_even==1) {
				$aff_final .= "\n<tr class='even'>";
				$odd_even=0;
				}
		if (SESSrights & CATALOGAGE_AUTH) {
			if ($resa_idnotice) $link = "<a href='./catalog.php?categ=isbd&id=".$resa_idnotice."'>".$data['tit']."</a>";
				elseif ($resa_idbulletin) $link = "<a href='./catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=".$resa_idbulletin."'>".$data['tit']."</a>";
			} else $link = $data['tit'];
		if (!$idnotice && !$idbulletin) $aff_final .= "<td><b>$link</b></td>";    
		if (!$idempr) {
			if (SESSrights & CIRCULATION_AUTH) $aff_final .= "<td><a href=\"./circ.php?categ=pret&form_cb=".rawurlencode($data['empr_cb'])."\">".$data['empr_nom'].", ".$data['empr_prenom']."</a></td>"; 
				else $aff_final .= "<td>".$data['empr_nom'].", ".$data['empr_prenom']."</td>"; 
			}
		$aff_final .= "<td>".$rank."</td>"; 
		$aff_final .= "<td>".$data['aff_resa_date']."</td>"; 
		$aff_final .= "<td>".$situation."</td>"; 
		$aff_final .= "<td>".$data['aff_resa_date_fin']."</td>"; 
		// gestion du formulaire de validation/suppression
		if ($info_gestion) {
			$aff_final .= "\n<td style='text-align:center;'>";
			if ($data['resa_cb']) $aff_final .= "<font color='red'><b>X</b></font>" ; else $aff_final .= "&nbsp;" ;
			$aff_final .= "</td>\n<td style='text-align:center;'><input type='checkbox' name='suppr_id_resa[]' value='".$data['id_resa']."' id='suppr_resa' /></td>" ;
			}
		$aff_final .= "</tr>\n";
		}
	} 

$aff_final .= "</table>";

if ($info_gestion) {
	$aff_final .= "<table style='background:none;border-right:0px;border-left:0px;border-bottom:0px;border-top:0px;'>
			<tr><td style='background:none;border-right:0px;border-left:0px;border-bottom:0px;border-top:0px;text-align:left;'><input type='submit' class='bouton' value='".$msg[resa_valider_suppression]."' /></td>";
	$aff_final .= "<td style='background:none;border-right:0px;border-left:0px;border-bottom:0px;border-top:0px;text-align:right;'>";
	$aff_final .= "<input type='button' class='bouton' onClick=\"setCheckboxes('check_resa', 'suppr_id_resa', true); return false;\" value='".$msg['resa_tout_cocher']."' />";
	$aff_final .= "</td></tr></table></form>" ;
	}

mysql_free_result ($req); 

return $aff_final ;
}

// cette fonction va retourner un tableau des r�sa pas trait�es
function resa_list_resa_a_traiter () {
	
/* Traitement :
	chercher toutes les r�servations non trait�es (resa_cb ="")
	construire le tableau avec le titre de l'ouvrage, le nom du r�servataire et son rang
*/
global $dbh ;
global $msg;

$tableau_final=array();

$order="tit, resa_idnotice, resa_idbulletin, resa_date" ;

$sql="SELECT resa_idnotice, resa_idbulletin, resa_date, resa_date_fin, resa_cb, resa_idempr, empr_nom, empr_prenom, empr_cb, ";
$sql.=" trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date, concat(' (',mention_date,')') ,''))) as tit, id_resa, ";
$sql.=" IF(resa_date_fin>=sysdate() or resa_date_fin='0000-00-00',0,1) as perimee, date_format(resa_date_debut, '".$msg["format_date"]."') as aff_resa_date_debut, if(resa_date_fin='0000-00-00', '', date_format(resa_date_fin, '".$msg["format_date"]."')) as aff_resa_date_fin, date_format(resa_date, '".$msg["format_date"]."') as aff_resa_date " ;
$sql.=" FROM (((resa LEFT JOIN notices AS notices_m ON resa_idnotice = notices_m.notice_id ) ";
$sql.=" LEFT JOIN bulletins ON resa_idbulletin = bulletins.bulletin_id) ";
$sql.=" LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), empr ";
$sql.=" where resa_idempr=id_empr and (resa_cb='' or resa_cb is null) ";
$sql.=" group by resa_idnotice, resa_idbulletin, resa_idempr ";
$sql.=" order by ".$order ;

$req = mysql_query($sql) or die("Erreur SQL !<br />".$sql."<br />".mysql_error()); 

if (!mysql_num_rows($req)) return $tableau_final;

while ($data = mysql_fetch_array($req)) {
	$rank = recupere_rang($data['resa_idempr'], $data['resa_idnotice'], $data['resa_idbulletin']) ;
	$tableau_final[] = array(
				'resa_tit' => $data['tit'],
				'resa_idnotice' => $data['resa_idnotice'],
				'resa_idbulletin' => $data['resa_idbulletin'],
				'resa_idempr' => $data['resa_idempr'],
				'resa_empr' => $data['empr_nom']." ".$data['empr_prenom'],
				'rank' => $rank ) ;
	} // fin while

mysql_free_result ($req); 

return $tableau_final ;
}


function resa_ranger_list () {
	
global $dbh ;
global $msg;
global $begin_result_liste;
global $end_result_liste;

$sql="SELECT resa_cb, expl_id from resa_ranger left join exemplaires on resa_cb=expl_cb ";
$res = mysql_query($sql, $dbh) ;
while ($ranger = mysql_fetch_object($res)) {
	if ($ranger->expl_id) {
		if($stuff = get_expl_info($ranger->expl_id)) {
			$stuff = check_pret($stuff);
			$aff_final .=  print_info($stuff,0,0,0);
			} else {
				$aff_final .=  "<strong>$form_cb_expl&nbsp;: ${msg[395]}</strong>";
				}
		}
	}
if ($aff_final) return $begin_result_liste.$aff_final.$end_result_liste;
	else return $msg['resa_liste_docranger_nodoc'] ;
}

// permet de savoir si un CB expl est d�j� affect� � une r�sa
function verif_cb_utilise ($cb) {
	global $dbh ;
	$rqt = "select id_resa from resa where resa_cb='".$cb."' ";
	$res = mysql_query ($rqt, $dbh) ;
	$nb=mysql_num_rows($res) ;
	if (!$nb) return 0 ;
	$obj=mysql_fetch_object($res) ;
	return $obj->id_resa ;
	}
	
function affecte_cb ($cb) {
	global $dbh ;
	
	// chercher s'il s'agit d'une notice ou d'un bulletin
	$rqt = "select expl_notice, expl_bulletin from exemplaires where expl_cb='".$cb."' ";
	$res = mysql_query ($rqt, $dbh) ;
	$nb=mysql_num_rows($res) ;
	if (!$nb) return 0 ;
	
	$obj=mysql_fetch_object($res) ;
	
	// chercher le premier (par ordre de rang, donc de date de d�but de r�sa, non valid�
	$rqt = "select id_resa, resa_idempr from resa where resa_idnotice='".$obj->expl_notice."' and resa_idbulletin='".$obj->expl_bulletin."' and resa_cb='' and resa_date_fin='0000-00-00' order by resa_date ";
	$res = mysql_query ($rqt, $dbh) ;
	
	if (!mysql_num_rows($res)) return 0 ;
	
	$obj_resa=mysql_fetch_object($res) ;
	
	$nb_days = get_time($obj_resa->resa_idempr,$obj->expl_notice,$obj->expl_bulletin) ;
	
	// mettre resa_cb � jour pour cette resa
	$rqt = "update resa set resa_cb='".$cb."' " ;
	$rqt .= ", resa_date_debut=sysdate() " ;
	$rqt .= ", resa_date_fin=date_add(sysdate(), interval $nb_days DAY) " ;
	$rqt .= " where id_resa='".$obj_resa->id_resa."' ";
	$res = mysql_query ($rqt, $dbh);
	return $obj_resa->id_resa ;
	}


function desaffecte_cb ($cb) {
	global $dbh ;
	$rqt = "update resa set resa_cb='', resa_date_debut='0000-00-00', resa_date_fin='0000-00-00' where resa_cb='".$cb."' ";
	$res = mysql_query ($rqt, $dbh) ;
	return mysql_affected_rows($dbh) ;
	}

function recupere_cb ($id) {
	global $dbh ;
	$rqt = "select resa_cb from resa where id_resa='".$id."' ";
	$res = mysql_query ($rqt, $dbh) ;
	$nb=mysql_num_rows($res) ;
	if (!$nb) return "" ;
	$obj=mysql_fetch_object($res) ;
	return $obj->resa_cb ;
	}

//   calcul du rang d'un emprunteur sur une r�servation
function recupere_rang($id_empr, $id_notice, $id_bulletin) {
	global $dbh;
	$rank = 1;
	if (!$id_notice) $id_notice=0;
	if (!$id_bulletin) $id_bulletin=0 ;
	$query = "select resa_idempr from resa where resa_idnotice='".$id_notice."' and resa_idbulletin='".$id_bulletin."' order by resa_date";
	$result = mysql_query($query, $dbh);
	while($resa=mysql_fetch_object($result)) {
		if($resa->resa_idempr == $id_empr) break;
		$rank++;
		}
	return $rank;
	}

//R�cup�ration de la dur�e de r�servation pour une notice ou un bulletin et un emprunteur
function get_time($id_empr,$id_notice,$id_bulletin) {
	global $pmb_quotas_avances;
	
	//Si les quotas avanc�s sont actifs
	if ($pmb_quotas_avances) {
		$struct=array();
		if ($id_notice) {
			$struct["NOTI"]=$id_notice;
			$quota_type="BOOK_TIME_QUOTA";
		} else {
			$struct["BULL"]=$id_bulletin;
			$quota_type="BOOK_TIME_SERIAL_QUOTA";
		}
		$struct["READER"]=$id_empr;
		$qt=new quota($quota_type);
		$t=$qt->get_quota_value($struct);
		if ($t==-1) $t=0;
	} else {
		//Sinon je regarde la dur�e de r�servation la plus d�favorable par type de document
		if ($id_notice)
			$requete="select min(duree_resa) from docs_type, exemplaires where expl_notice='$id_notice' and expl_typdoc=idtyp_doc";
		else
			$requete="select min(duree_resa) from docs_type, exemplaires where expl_bulletin='$id_bulletin' and expl_typdoc=idtyp_doc";
		$resultat=mysql_query($requete);
		if (mysql_num_rows($resultat)) $t=mysql_result($resultat,0,0); else $t=0;
	}
	return $t;
}

function check_quota_resa($id_empr,$id_notice,$id_bulletin) {
	global $dbh;
	global $msg;
	global $pmb_quotas_avances;
	global $_quotas_elements_;
	global $pmb_resa_quota_pret_depasse;
	
	//Initialisation r�sultat
	$error=array();
	$error["ERROR"]=false;
	
	//Si les quotas avanc�s sont autoris�s
	if ($pmb_quotas_avances) {
		$struct=array();
		//Quota de notice ou bulletin ?
		if ($id_notice) {
			$quota_type="BOOK_NMBR_QUOTA";
			$struct["NOTI"]=$id_notice;
			$elt_name="NOTICETYPE";
		} else {
			$quota_type="BOOK_NMBR_SERIAL_QUOTA";
			$struct["BULL"]=$id_bulletin;
			$elt_name="BULLETINTYPE";
		}
		//Initialisation du quota
		$qt=new quota($quota_type);
		$struct["READER"]=$id_empr;
		
		//Si r�sa bloqu�e en cas de d�passement de quota de pr�t
		if (!$pmb_resa_quota_pret_depasse) {
			//Le quota de pr�t est-il atteint pour cette notice ou bulletin
			//R�cup�ration de l'�l�ment indirect � tester
			$elt=$qt->get_element_by_name($elt_name);
			//R�cup�ration de l'exemplaire le plus d�favorable associ� � la r�servation
			$object_id=$qt->get_object_for_indirect_element($_quotas_elements_[$elt],$struct);
			//Initialisation du quota de pr�t
			$qt_pret=new quota("LEND_NMBR_QUOTA");
		
			$struct_pret["READER"]=$id_empr;
			$struct_pret["EXPL"]=$object_id;
			$r=$qt_pret->check_quota($struct_pret);
		} else $r=false;
		
		//Si quota de pr�t non viol� alors on regarde les quotas de r�servation
		if (!$r) {
			//V�rification
			$r=$qt->check_quota($struct);
			//Si quota viol�
			if ($r) {
				$error["ERROR"]=true;
				//Erreur
				$error["MESSAGE"]=$qt->error_message;
				//Peut-on forcer ou pas la r�sa
				$error["FORCE"] = $qt->force;
				return $error;
			}
		} else {
			$error["ERROR"]=true;
			//Erreur
			$error["MESSAGE"]=$qt_pret->error_message."<br />".$msg["resa_quota_pret_error"];
			//Peut-on forcer ou pas la r�sa
			$error["FORCE"] = 0;
			return $error;
		}
		return $error;
	} else return $error;
}

// retourne un tableau constitu� des exemplaires disponibles pour une r�sa donn�e
function expl_dispo ($no_notice=0, $no_bulletin=0) {
	global $dbh;
	// on r�cup�re les donn�es des exemplaires
	$requete = "SELECT expl_id, expl_cb, expl_cote, expl_notice, expl_bulletin, pret_retour, location_libelle, section_libelle, statut_libelle ";
	$requete .= " FROM exemplaires, docs_location, docs_section, docs_statut";
	$requete .= " LEFT JOIN pret ON exemplaires.expl_id=pret.pret_idexpl";
	$requete .= " WHERE expl_notice='$no_notice' and expl_bulletin='$no_bulletin' ";
	$requete .= " AND exemplaires.expl_location=docs_location.idlocation";
	$requete .= " AND exemplaires.expl_section=docs_section.idsection ";
	$requete .= " AND exemplaires.expl_statut=docs_statut.idstatut ";
	$requete .= " order by location_libelle, section_libelle, expl_cote ";
	$result = mysql_query($requete, $dbh);	
	while($expl = mysql_fetch_object($result)) {
		if(!$expl->pret_retour && !verif_cb_utilise($expl->expl_cb))  
			$tableau[] = array (
				'expl_id' => $expl->expl_id,
				'expl_cb' => $expl->expl_cb,
				'expl_notice' => $expl->expl_notice,					
				'expl_bulletin' => $expl->expl_bulletin,					
				'expl_cote' => $expl->expl_cote,
				'location' => $expl->location_libelle,
				'section' => $expl->section_libelle,
				'statut' => $expl->statut_libelle ) ;
		}
	return $tableau ;
	}

function check_statut($id_notice=0, $id_bulletin=0) {
	global $dbh;
	global $opac_resa_dispo; // les r�sa de disponibles sont-elles autoris�es ?
	global $msg;
	global $message_resa,$empr_location,$pmb_location_reservation;

	// on checke s'il y a des exemplaires r�servables et visibles
	if($id_notice) {
		$query = "select expl_id, expl_cb from exemplaires e, docs_statut s, docs_location l, docs_section se";
		$query .= " where (e.expl_notice='$id_notice'  ) and s.statut_allow_resa=1 and s.statut_visible_opac=1 and l.location_visible_opac=1 and se.section_visible_opac=1";
		$query .= " and s.idstatut=e.expl_statut";
		$query .= " and e.expl_location=l.idlocation";
		$query .= " and e.expl_section=se.idsection ";
	} elseif($id_bulletin) {
		$query = "select expl_id, expl_cb from exemplaires e, docs_statut s, docs_location l, docs_section se";
		$query .= " where (e.expl_bulletin='$id_bulletin' ) and s.statut_allow_resa=1 and s.statut_visible_opac=1 and l.location_visible_opac=1 and se.section_visible_opac=1" ;
		$query .= " and s.idstatut=e.expl_statut";
		$query .= " and e.expl_location=l.idlocation";
		$query .= " and e.expl_section=se.idsection ";
	} else {
		$message_resa.= "<strong>".$msg["resa_no_expl"]."</strong>";
		return 0;
	}
	if($pmb_location_reservation) {			
		$query.=" and e.expl_location in (select resa_loc from resa_loc where resa_emprloc=$empr_location) "; 	
	}
	$result = mysql_query($query, $dbh);
	if(!@mysql_num_rows($result)) {
		// aucun exemplaire n'est disponible pour le pr�t
		$message_resa.= "<strong>".$msg["resa_no_expl"]."</strong>";
		return 0;
	}
	
	// on regarde si les r�sa de disponibles sont autoris�es
	if ($opac_resa_dispo=="1") return 1;
	
	// on checke si un exemplaire est disponible
	// aka. si un des exemplaires en circulation n'est pas mentionn� dans la table des pr�ts,
	// c'est qu'il est disponible � la biblioth�que
	$list_dispo = '';

	while($reservable = mysql_fetch_object($result)) {
		$req2 = "select count(1) from pret where pret_idexpl=".$reservable->expl_id;
		$req2_result = mysql_query($req2, $dbh);
		if(!mysql_result($req2_result, 0, 0)) {			
			// l'exemplaire ne figure pas dans la table pret -> dispo
			// on r�cup�re les donn�es exemplaires pour constituer le message
			$req3 = "select p.expl_cb, p.expl_cote, s.section_libelle, l.location_libelle ";
			$req3 .= " from exemplaires p, docs_section s, docs_location l";
			$req3 .= " where p.expl_id=".$reservable->expl_id;
			$req3 .= " and s.idsection=p.expl_section";
			$req3 .= " and l.idlocation=p.expl_location limit 1";		
			$req3_result = mysql_query($req3, $dbh);
			$req3_obj = mysql_fetch_object($req3_result);
			if($req3_obj->expl_cb) {				
				// Si r�sa valid� il n'est pas disponible en pr�t
				$req4 = "select count(1) from resa where resa_cb='".$reservable->expl_cb."' and resa_confirmee='1'";
				$req4_result = mysql_query($req4, $dbh);
				if(!mysql_result($req4_result, 0, 0)) {
					$list_dispo .= '<br />'.$req3_obj->location_libelle.'.';
					$list_dispo .= $req3_obj->section_libelle.' cote&nbsp;: '.$req3_obj->expl_cote;
				} else {
					return 1; //Au moins 1 exemplaire n'est pas disponible
				}
			}
		} else {
			return 1; //Au moins 1 exemplaire n'est pas disponible
		}
	}

	if($list_dispo) {
		$message_resa = "<b>$msg[resa_doc_dispo]</b>";
		$message_resa .= $list_dispo;
		//signifie que : opac_resa_dispo == 0 && exemplaire(s) dispo(s)
		return 0;
// 		return 2;
	}

	// rien de sp�cial
	return  1;
}


function allready_loaned($id_notice=0, $id_bulletin=0, $id_empr) {
	global $dbh;
	global $msg;
	$query = "select count(1) from pret p, exemplaires e";
	$query .= " where p.pret_idempr='$id_empr' and e.expl_notice='$id_notice' and e.expl_bulletin='$id_bulletin' "; 
	$query .= " and p.pret_idexpl=e.expl_id";
	$result = @mysql_query($query, $dbh);
	if (@mysql_result($result, 0, 0)) {
		return $msg[resa_deja_doc] ;
	}
	return "";
}


function alert_mail_users_pmb($id_notice=0, $id_bulletin=0, $id_empr, $annul=0) {
	global $dbh;
	global $msg, $charset;
	global $opac_biblio_name, $opac_biblio_email ;
	global $opac_url_base ;
	
	// param�trage OPAC: choix du nom de la biblioth�que comme exp�diteur
	$requete = "select location_libelle, email from empr, docs_location where empr_location=idlocation and id_empr='$id_empr' ";
	$res = mysql_query($requete, $dbh);
	$loc=mysql_fetch_object($res) ;
	$PMBusernom = $loc->location_libelle ;
	$PMBuserprenom = '' ;
	$PMBuseremail = $loc->email ;
	if ($PMBuseremail) {
		$query = "select distinct empr_prenom, empr_nom, empr_cb, empr_mail, empr_tel1, empr_tel2, empr_ville, location_libelle, nom, prenom, user_email, date_format(sysdate(), '".$msg["format_date_heure"]."') as aff_quand from empr, docs_location, users where id_empr='$id_empr' and empr_location=idlocation and user_email like('%@%') and user_alert_resamail=1";
		$result = @mysql_query($query, $dbh);
		$headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=".$charset."\n";
		$output_final='';
		while ($empr=@mysql_fetch_object($result)) {
			if (!$output_final) {
				$output_final = "<html><body>" ;
				if ($annul==1) {
					$output_final .= "<a href='".$opac_url_base."'><font color=red><strong>".$msg["mail_obj_resa_canceled"] ;
					$sujet = $msg["mail_obj_resa_canceled"] ;
				} elseif ($annul==2) {
					$output_final .= "<a href='".$opac_url_base."'><font color=blue><strong>".$msg["mail_obj_resa_reaffected"] ;
					$sujet = $msg["mail_obj_resa_reaffected"] ;
				} else {
					$output_final .= "<a href='".$opac_url_base."'><font color=green><strong>".$msg["mail_obj_resa_added"] ;
					$sujet = $msg["mail_obj_resa_added"] ;
				}
				$output_final .= "</strong></font></a> ".$empr->aff_quand."
									<br /><strong>".$empr->empr_prenom." ".$empr->empr_nom."</strong>							
									<br /><i>".$empr->empr_mail." / ".$empr->empr_tel1." / ".$empr->empr_tel2."</i>";
				if ($empr->empr_cp || $empr->empr_ville) $output_final .= "<br /><u>".$empr->empr_cp." ".$empr->empr_ville."</u>";
				$output_final .= "<hr />".$msg[situation].": ".$empr->location_libelle."<hr />";
				if ($id_notice) {
					$current = new notice_affichage($id_notice,array(),0,1);
					$current->do_header();
					$current->do_isbd(1,1);
					$output_final .= "<h3>".$current->notice_header."</h3>";
					$output_final .= $current->notice_isbd;
					$output_final .= $current->affichage_expl ;
				} else {
					$output_final .= bulletin_affichage_reduit($id_bulletin) ;
					$output_final .= notice_affichage::expl_list("m",0,$id_bulletin) ;
				}
				$output_final .= "<hr /></body></html> ";
			}
			$res_envoi=mailpmb($empr->nom." ".$empr->prenom, $empr->user_email,$sujet." ".$empr->aff_quand,$output_final,$PMBusernom, $PMBuseremail, $headers, "", "", 1);
		}
	}
}
