<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pointage_exemplarise.php,v 1.49 2014-01-13 13:36:44 mbertin Exp $

// d�finition du minimum n�c�ssaire
$base_path="./../../..";
$base_auth = "CATALOGAGE_AUTH";
$base_title = "\$msg[6]";
require_once ("$base_path/includes/init.inc.php");
require_once($class_path."/serials.class.php");
require_once($class_path."/serial_display.class.php");
require_once("$include_path/explnum.inc.php") ;
require_once ($class_path . "/parse_format.class.php");
require_once($class_path."/parametres_perso.class.php");
require_once("$class_path/abts_pointage.class.php");
require_once("$class_path/explnum.class.php");
require_once("$class_path/serialcirc_diff.class.php");
require_once($class_path."/serialcirc.class.php");


$templates = <<<ENDOFFILE
			<script type='text/javascript'>
				function desactive(obj) {
					var obj_1=obj+"_1";	
					var obj_2=obj+"_2";	
					var obj_3=obj+"_3";		
					parent.document.getElementById(obj_1).disabled = true;
					parent.document.getElementById(obj_2).disabled = true;
					parent.document.getElementById(obj_3).disabled = true;
				}		
				function enregistre(obj,bul_id) {
					var obj_bul=obj+"_bul";		
					desactive(obj)
					parent.document.getElementById(obj_bul).innerHTML="<a href='./catalog.php?categ=serials&sub=bulletinage&action=view&bul_id="+bul_id+"'>"+"!!Voir_le_bulletin!!"+"</a>!!serialcir_print!!!!print_cote!!";
					parent.kill_frame_periodique();
				}		
				function Fermer(obj) {
					desactive(obj)
				 	parent.kill_frame_periodique();
				}
			</script>
<div style='width: 98%;'>
	<div id="bouton_fermer_notice_preview" class="right"><a href='#' onClick='parent.kill_frame_periodique();return false;'>X</a></div>
	!!form!!
</div>						
ENDOFFILE;
$templates=str_replace("!!Voir_le_bulletin!!",$msg['pointage_voir_le_bulletin'],$templates);


if($act=="memo_doc_num"){	
	// retour apr�s telechargement du document num�rique associ� au bulletin
	print "
		<script type='text/javascript'>
			function desactive(obj) {
				var obj_1=obj+'_1';	
				var obj_2=obj+'_2';	
				var obj_3=obj+'_3';		
				parent.document.getElementById(obj_1).disabled = true;
				parent.document.getElementById(obj_2).disabled = true;
				parent.document.getElementById(obj_3).disabled = true;
			}
			var obj_bul='".$id_bull."_bul';		
			desactive($id_bull);
			parent.document.getElementById(obj_bul).innerHTML=\"<a href='./catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=$bul_id'>".$msg['pointage_voir_le_bulletin']."</a>\";
			parent.kill_frame_periodique();
		</script>
	";
	exit;
}	
/*
if(!$expl_id) // pas d'id, c'est une cr�ation
	echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4007], $serial_header);
else echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4008], $serial_header);
*/
function do_selector_bul_section($section_id, $location_id) {
	global $dbh;
 	global $charset;
	global $deflt_section;
	global $deflt_location;
	
	if (!$section_id) $section_id=$deflt_section ;
	if (!$location_id) $location_id=$deflt_location;

	$rqtloc = "SELECT idlocation FROM docs_location order by location_libelle";
	$resloc = mysql_query($rqtloc, $dbh);
	while ($loc=mysql_fetch_object($resloc)) {
		$requete = "SELECT idsection, section_libelle FROM docs_section, docsloc_section where idsection=num_section and num_location='$loc->idlocation' order by section_libelle";
		$result = mysql_query($requete, $dbh);
		$nbr_lignes = mysql_num_rows($result);
		if ($nbr_lignes) {			
			if ($loc->idlocation==$location_id) $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:block\">";
				else $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:none\">";
			$selector .= "<select name='f_ex_section".$loc->idlocation."' id='f_ex_section".$loc->idlocation."'>";
			while($line = mysql_fetch_row($result)) {
				$selector .= "<option value='$line[0]'";
				$line[0] == $section_id ? $selector .= ' SELECTED>' : $selector .= '>';
	 			$selector .= htmlentities($line[1],ENT_QUOTES, $charset).'</option>';
				}                                         
			$selector .= '</select></div>';
			}                 
		}
	return $selector;                         
}                                                 

function bul_do_form($obj) {
	// $obj = objet contenant les propri�t�s de l'exemplaire associ�
	global $bul_expl_form1,$expl_bulletinage_tpl;
	global $msg; // pour texte du bouton supprimer
	global $dbh,$charset;
	global $pmb_type_audit,$select_categ_prop,$pmb_antivol ;
	global $id_bull,$bul_id,$serial_id,$numero,$pmb_rfid_activate,$pmb_rfid_serveur_url;
	
	if(!$obj->abt_numeric)$bul_expl_form1 = str_replace('!!expl_bulletinage_tpl!!', $expl_bulletinage_tpl, $bul_expl_form1);	
	else $bul_expl_form1 = str_replace('!!expl_bulletinage_tpl!!', "", $bul_expl_form1);	
	$action = "./pointage_exemplarise.php?act=update&id_bull=$id_bull&bul_id=$bul_id";
	
	// mise � jour des champs de gestion
	$bul_expl_form1 = str_replace('!!bul_id!!', $obj->expl_bulletin, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!id_form!!', md5(microtime()), $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!org_cb!!', $obj->expl_cb, $bul_expl_form1);	
	$bul_expl_form1 = str_replace('!!expl_id!!', $obj->expl_id, $bul_expl_form1);
	
	$bul_expl_form1 = str_replace('!!action!!', $action, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!id!!', $obj->expl_notice, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!cb!!', $obj->expl_cb, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!note!!', $obj->expl_note, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!comment!!', $obj->expl_comment, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!cote!!', htmlentities($obj->expl_cote,ENT_QUOTES, $charset), $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!prix!!', $obj->expl_prix, $bul_expl_form1);
	if(!$obj->abt_numeric)$bul_expl_form1 = str_replace('!!focus!!',$obj->focus, $bul_expl_form1);
	else $bul_expl_form1 = str_replace('!!focus!!',"", $bul_expl_form1);
	// select "type document"
	$bul_expl_form1 = str_replace('!!type_doc!!',
				do_selector('docs_type', 'expl_typdoc', $obj->expl_typdoc),
				$bul_expl_form1);		
	// select "section"
	$bul_expl_form1 = str_replace('!!section!!',
				do_selector_bul_section($obj->expl_section, $obj->expl_location),
				$bul_expl_form1);
	// select "statut"
	$bul_expl_form1 = str_replace('!!statut!!',
				do_selector('docs_statut', 'expl_statut', $obj->expl_statut),
				$bul_expl_form1);
	// select "localisation"
	$bul_expl_form1 = str_replace('!!localisation!!',
				gen_liste ("select distinct idlocation, location_libelle from docs_location, docsloc_section where num_location=idlocation order by 2", "idlocation", "location_libelle", 'expl_location', "calcule_section(this);", $obj->expl_location, "", "","","",0),
				$bul_expl_form1);
	// select "code statistique"
	$bul_expl_form1 = str_replace('!!codestat!!',
				do_selector('docs_codestat', 'expl_codestat', $obj->expl_codestat),
				$bul_expl_form1);
	// select "owner"
	$bul_expl_form1 = str_replace('!!owner!!',
				do_selector('lenders', 'expl_owner', $obj->expl_owner),
				$bul_expl_form1);
	$selector="";
	if($pmb_antivol>0) {
		// select "type_antivol"
		$selector = "
		<div class='colonne3'>
		<!-- code stat -->
		<label class='etiquette' for='type_antivol'>$msg[type_antivol]</label>
		<div class='row'>
		<select name='type_antivol' id='type_antivol'>";	
		$selector .= "<option value='0'";
		if($obj->type_antivol ==0)$selector .= ' SELECTED';
		$selector .= '>';
		$selector .= $msg["type_antivol_aucun"].'</option>';
		$selector .= "<option value='1'";
		if($obj->type_antivol ==1)$selector .= ' SELECTED';
		$selector .= '>';
		$selector .= $msg["type_antivol_magnetique"].'</option>';
		$selector .= "<option value='2'";
		if($obj->type_antivol ==2)$selector .= ' SELECTED';
		$selector .= '>';
		$selector .= $msg["type_antivol_autre"].'</option>';		
	                                        
		$selector .= '</select></div></div>';   
	}        
	$bul_expl_form1 = str_replace('!!type_antivol!!', $selector, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!bul_id!!', $bul_id, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!expl_id!!', $obj->expl_id, $bul_expl_form1);	
	$bul_expl_form1 = str_replace('!!bul_no!!', htmlentities($obj->bul_no,ENT_QUOTES, $charset)	, $bul_expl_form1);
	$date_date_formatee = formatdate_input($obj->date_date);
	$date_clic = "onClick=\"openPopUp('./../../../select.php?what=calendrier&caller=expl&date_caller=".str_replace('-', '', $obj->date_date)."&param1=date_date&param2=date_date_lib&auto_submit=NO&date_anterieure=YES', 'date_date', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\"  ";
	$date_date = "<input type='hidden' name='date_date' value='".str_replace('-','', $obj->date_date)."' />
		<input class='saisie-10em' type='text' name='date_date_lib' value='".$date_date_formatee."' placeholder='".$msg["format_date_input_placeholder"]."'/>
		<input class='bouton_small' type='button' name='date_date_lib_bouton' value='".$msg["bouton_calendrier"]."' ".$date_clic." />";
		
	$bul_expl_form1 = str_replace('!!date_date!!', $date_date, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!bul_date!!', htmlentities($obj->bul_date,ENT_QUOTES, $charset), $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!bul_titre!!', htmlentities($obj->bul_titre,ENT_QUOTES, $charset), $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!serial_id!!', $serial_id, $bul_expl_form1);
	$bul_expl_form1 = str_replace('!!numero!!', $obj->bul_titre, $bul_expl_form1);	
	$bul_expl_form1 = str_replace('!!destinataire!!', $obj->destinataire, $bul_expl_form1);

	$p_perso=new parametres_perso("expl");
	if (!$p_perso->no_special_fields) {
		$c=0;
		$perso="";
		$perso_=$p_perso->show_editable_fields($obj->expl_id);
		for ($i=0; $i<count($perso_["FIELDS"]); $i++) {
			$p=$perso_["FIELDS"][$i];
			if ($c==0) $perso.="<div class='row'>\n";
			$perso.="<div class='colonne3'><label for='".$p["NAME"]."' class='etiquette'>".$p["TITRE"]."</label><div class='row'>".$p["AFF"]."</div></div>\n";
			$c++;
			if ($c==3) {
				$perso.="</div>\n";
				$c=0;
			}
		}	
		if ($c==1) $perso.="<div class='colonne2'>&nbsp;</div>\n</div>\n";
		$perso=$perso_["CHECK_SCRIPTS"]."\n".$perso;
	} else 
		$perso="\n<script>function check_form() { return true; }</script>\n";
	$bul_expl_form1 = str_replace("!!champs_perso!!",$perso,$bul_expl_form1);
	
	if ($pmb_rfid_activate==1 && $pmb_rfid_serveur_url && !$obj->abt_numeric) {
		$script_rfid_encode="if(script_rfid_encode()==false) return false;";	
		$bul_expl_form1 = str_replace('!!questionrfid!!', $script_rfid_encode, $bul_expl_form1);
	}
	else $bul_expl_form1 = str_replace('!!questionrfid!!', '', $bul_expl_form1);
	
	return $bul_expl_form1 ;
}

function sql_value($rqt) {
	if($result=mysql_query($rqt))
		if($row = mysql_fetch_row($result))	return $row[0];
	return '';
}

$requete = "SELECT * FROM abts_grille_abt WHERE id_bull='$id_bull'";
$abtsQuery = mysql_query($requete, $dbh);
if(mysql_num_rows($abtsQuery)) {
	$abts = mysql_fetch_object($abtsQuery);
	$modele_id = $abts->modele_id;
	$abt_id = $abts->num_abt;
	$value['date_date']=$abts->date_parution;
}
$requete = "SELECT * FROM abts_abts WHERE abt_id='$abt_id'";
$abtsQuery = mysql_query($requete, $dbh);
if(mysql_num_rows($abtsQuery)) {
	$abts = mysql_fetch_object($abtsQuery);
	$abt_numeric = $abts->abt_numeric;
	$exemp_auto = $abts->exemp_auto;
	$type_antivol = $abts->type_antivol;
	$date_debut = $abts->date_debut;
	$date_fin = $abts->date_fin;
	
}
$requete = "SELECT num_notice,format_periode FROM abts_modeles WHERE modele_id='$modele_id'";
$abtsQuery = mysql_query($requete, $dbh);
if(mysql_num_rows($abtsQuery)) {
	$abts = mysql_fetch_object($abtsQuery);
	$format_periode = $abts->format_periode;
	$serial_id = $abts->num_notice;
}

//Pr�paration nouveau bulletin
$myBulletinage = new bulletinage(0, $serial_id);

//Gen�ration du libell� de p�riode
$print_format=new parse_format();
$print_format->var_format['DATE'] = $value['date_date'];
$print_format->var_format['NUM'] = $nume;
$print_format->var_format['VOL'] = $vol;
$print_format->var_format['TOM'] = $tom;
$print_format->var_format['START_DATE'] = $date_debut;
$print_format->var_format['END_DATE'] = $date_fin;

$requete = "SELECT * FROM abts_abts_modeles WHERE modele_id='$modele_id' and abt_id='$abt_id' ";
$abtsabtsQuery = mysql_query($requete, $dbh);
if(mysql_num_rows($abtsabtsQuery)) {
	$abtsabts = mysql_fetch_object($abtsabtsQuery);
	$print_format->var_format['START_NUM'] = $abtsabts->num;
	$print_format->var_format['START_VOL'] = $abtsabts->vol;
	$print_format->var_format['START_TOM'] = $abtsabts->tome;	
	$num_statut=$abtsabts->num_statut_general;
}


$print_format->cmd = $format_periode;
$libelle_periode=$print_format->exec_cmd();

$flag_exemp_auto=0;
if ($exemp_auto==1){
	require_once($include_path."/$pmb_numero_exemplaire_auto_script");
	$flag_exemp_auto=1;
}

if(($act=='update') ) {
	$value['niveau_biblio']='b'; 
	$value['bul_no']=$bul_no;
	$value['bul_date']=$bul_date;
	// on verifie l'existance du bulletin
	if(!$bul_id){
		if(!($bul_id=sql_value("SELECT bulletin_id FROM bulletins where  bulletin_numero='$bul_no' and date_date='".$value['date_date']."' and bulletin_notice='$serial_id'"))){ 		
			//Cr�ation du bulletin si pas d�j� pr�sent
			$bul_id = $myBulletinage->update($value);
		}
	}
	if(!$abt_numeric){
		// c'est un abonnement qui n'est pas exclusivement num�rique. On cr�e l'exemplaire de bulletin
		$expl_cote = clean_string($expl_cote);
		$expl_note = clean_string($expl_note);
		$expl_comment = clean_string($expl_comment);
		$expl_prix = clean_string($expl_prix);	
		
		$formlocid="f_ex_section".$expl_location ;
		$expl_section=$$formlocid ;
	
		// si le code-barre saisi est vide, on affiche une erreur
		if (trim($f_ex_cb) == "") {
			print "<script>alert('".addslashes($msg['pointage_message_code_vide'])."'); history.go(-1);</script>";
			exit();
		}
		// si le code-barre saisi est d�j� utilis�, on affiche une erreur
		$requete = "SELECT COUNT(1) FROM exemplaires WHERE expl_cb='$f_ex_cb'";
		$myQuery = mysql_query($requete, $dbh);
		if(mysql_result($myQuery, 0, 0))  { 
			print "<script>alert('".addslashes($msg['pointage_message_code_utilise'])."'); history.go(-1);</script>";
			exit();
		}
		// D�pi�ger l'exemplaire (li� � l'abonnement) du dernier bulletin
		if($num_statut) {
			$requete="SELECT bulletin_id  FROM bulletins where date_date<'$date_date' and bulletin_notice='$serial_id' ORDER BY date_date DESC LIMIT 1";
			$result_dernier = mysql_query($requete);
			if ($r_dernier = mysql_fetch_object($result_dernier)) {
				$dernier_bul_id	=$r_dernier->bulletin_id;
				$requete = "update exemplaires set expl_statut=$num_statut where expl_bulletin=$dernier_bul_id and expl_abt_num='$abt_id' ";
				mysql_query($requete, $dbh);
			}
		}
		// on pr�pare la date de cr�ation ou modification
		$expl_date = today();
		
		$values = "expl_cb='$f_ex_cb'";
		$values .= ", expl_notice='0'";
		$values .= ", expl_bulletin='$bul_id'";
		$values .= ", expl_typdoc='$expl_typdoc'";
		$values .= ", expl_cote='$expl_cote'";
		$values .= ", expl_section='$expl_section'";
		$values .= ", expl_statut='$expl_statut'";
		$values .= ", expl_location='$expl_location'";
		$values .= ", expl_codestat='$expl_codestat'";
		$values .= ", expl_note='$expl_note'";
		$values .= ", expl_comment='$expl_comment'";
		$values .= ", expl_prix='$expl_prix'";
		$values .= ", expl_owner='$expl_owner'";
		$values .= ", type_antivol='$type_antivol'";
		$values .= ", expl_abt_num='$abt_id'";
		$requete = "INSERT INTO exemplaires set $values , create_date=sysdate() ";
	
		$myQuery = mysql_query($requete, $dbh);
		$expl_id=mysql_insert_id();	
		audit::insert_creation (AUDIT_EXPL, $expl_id) ;

		//parametres_perso de l'exemplaire
		$p_perso=new parametres_perso("expl");
		$nberrors=$p_perso->check_submited_fields();
		if(!$nberrors) $p_perso->rec_fields_perso($expl_id);

		$serialcirc_diff=new serialcirc_diff(0,$abt_id);
		// Si c'est � faire circuler
		if($serialcirc_diff->id){ 
			$serialcirc_diff->add_circ_expl($expl_id);
			$serialcir_print="<br/><input class='bouton' type='button' onclick='serialcirc_print_list_circ($expl_id,0);return false;' value='".$msg['serialcirc_circ_list_bull_circulation_imprimer_bt']."'>";
		}elseif ($pmb_serialcirc_subst){			
			$print_cote="<img src='./images/print.gif' alt='Imprimer...' title='Imprimer...' align='middle' border='0'	style='padding-left:7px' onclick='imprime_cote($expl_id);return false;'	>";
		}
	
	}
	//Mis � jour du bulletin avec les valeurs du formulaire	
	$requete = "UPDATE bulletins set bulletin_numero='$bul_no',date_date='$date_date', mention_date='$bul_date', bulletin_titre='$bul_titre' WHERE bulletin_id='$bul_id' ";
	$myQuery = mysql_query($requete, $dbh);
	
	// Mise a jour de la table notices_mots_global_index pour toutes les notices en relation avec l'exemplaire
	$req_maj="SELECT bulletin_notice,num_notice, analysis_notice FROM bulletins LEFT JOIN analysis ON analysis_bulletin=bulletin_id WHERE bulletin_id='".$bul_id."'";
	$res_maj=mysql_query($req_maj);
	if($res_maj && mysql_num_rows($res_maj)){
		$first=true;//Pour la premiere ligne de r�sultat on doit indexer aussi la notice de p�riodique et de bulletin au besoin
		while ( $ligne=mysql_fetch_object($res_maj) ) {
			if($first){
				if($ligne->bulletin_notice){
					notice::majNoticesMotsGlobalIndex($ligne->bulletin_notice,'expl');
				}
				if($ligne->num_notice){
					notice::majNoticesMotsGlobalIndex($ligne->num_notice,'expl');
				}
			}
			if($ligne->analysis_notice){
				notice::majNoticesMotsGlobalIndex($ligne->analysis_notice,'expl');
			}
			$first=false;
		}
	}
	
	// D�claration du bulletin comme re�u
	$requete="update abts_grille_abt set state='2' where id_bull= '$id_bull' ";	
	mysql_query($requete);
	
	
	if($f_fichier["name"]!=""){	
		// Il y a un document num�rique rattach� au bulletin
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
		// Url de retour apr�s t�l�chargement du document.	
		$retour ="$base_path/catalog/serials/pointage/pointage_exemplarise.php?act=memo_doc_num&id_bull=$id_bull&bul_id=$bul_id";		
		$explnum->mise_a_jour(0, $bul_id, '', $f_url, $retour,0,0);	
		exit();
	}else{	
		// Pas de doc num�rique, on ferme l'iframe 
		$id_form = md5(microtime());
		$templates=str_replace("!!form!!","<script type='text/javascript'>enregistre('$id_bull','$bul_id');</script>",$templates);	
	}
} else {
	// Formulaire 
	include("$include_path/templates/serials.tpl.php");
	
	abts_pointage::delete_retard($abt_id,  $value['date_date'],$numero);	
	
	if($nonrecevable) {
		$value['bul_titre'] = $msg['abonnements_bulletin_non_recevable'] ;
		$requete="update abts_grille_abt set state='3' where id_bull= '$id_bull' ";	
		mysql_query($requete);		
		abts_pointage::delete_retard($this->abt_id);
		$templates=str_replace("!!form!!","<script type='text/javascript'>Fermer('$id_bull');</script>",$templates);
		print $templates;
		exit();
	}
	$expl = new stdClass();
	$expl->date_date =$value['date_date'];
	$expl->bul_date = $libelle_periode; 
	$expl->bul_no = stripslashes($numero);
		
	//R�cup�ration des infos du bulletin pour les proposer sur la frame
	$requete = "SELECT * FROM bulletins where bulletin_numero='$numero' and bulletin_notice='$serial_id' and date_date='".$value['date_date']."'";
	$bull_Query = mysql_query($requete, $dbh);
	if(mysql_num_rows($bull_Query)) {	
		$bull = mysql_fetch_object($bull_Query);
		$bul_id= $bull->bulletin_id;
		$expl->date_date = $bull->date_date;
		$expl->bul_date = $bull->mention_date;
		$expl->bul_titre = $bull->bulletin_titre;
	}	
	if($flag_exemp_auto==1)	{
		//G�n�ration automatique de code barre, activ� pour cet abonnement
  		$requete="DELETE from exemplaires_temp where sess not in (select SESSID from sessions)";
   		$res = mysql_query($requete,$dbh); 	
    	//Appel � la fonction de g�n�ration automatique de cb
    	$code_exemplaire =init_gen_code_exemplaire(0,$bul_id);
    	do {
    		$code_exemplaire = gen_code_exemplaire(0,$bul_id,$code_exemplaire);
    		$requete="select expl_cb from exemplaires WHERE expl_cb='$code_exemplaire'";
    		$res0 = mysql_query($requete,$dbh);
    		$requete="select cb from exemplaires_temp WHERE cb='$code_exemplaire' AND sess <>'".SESSid."'";
    		$res1 = mysql_query($requete,$dbh);
    	} while((mysql_num_rows($res0)||mysql_num_rows($res1)));
    		
   		//Memorise dans temps le cb et la session pour le cas de multi utilisateur session
   		$requete="INSERT INTO exemplaires_temp (cb ,sess) VALUES ('$code_exemplaire','".SESSid."')";
   		$res = mysql_query($requete,$dbh);
		$expl->expl_cb=$code_exemplaire;	
		//Focus sur le bouton 'Enregistre'
		$expl->focus="<script type='text/javascript' >document.forms[\"expl\"].bouton_enregistre.focus();</script>";
	} else {
		//Focus sur le l'input de saisie de code barre 
		$expl->focus="<script type='text/javascript' >document.forms[\"expl\"].f_ex_cb.focus();</script>";
	}
	$bull_form="";				
	$perio = new serial_display($myBulletinage->serial_id, 1);
	$perio_header =  $perio->header;
	
	print pmb_bidi("<div class='row'><h2>".$bulletinage->display.'</h2></div>');
	if($abt_numeric){
		$expl->abt_numeric=1;
	}else {
		// c'est un abonnement qui n'est pas exclusivement num�rique. On cr�e le formulaire de l'exemplaire de bulletin
		$expl->abt_numeric=0;

		$requete = "SELECT * FROM abts_abts WHERE abt_id='$abt_id'";
		$abtsQuery = mysql_query($requete, $dbh);
		if(mysql_num_rows($abtsQuery)) {
			$abts = mysql_fetch_object($abtsQuery);
			$expl->expl_cote = $abts->cote;
			$expl->expl_location = $abts->location_id;
			$expl->expl_section = $abts->section_id;
			$expl->expl_codestat = $abts->codestat_id;
			$expl->expl_typdoc = $abts->typdoc_id;
			$expl->expl_statut = $abts->statut_id;
			$expl->expl_owner = $abts->lender_id;
			$expl->type_antivol = $abts->type_antivol;
			$expl->expl_note = $abts->expl_note;
			$expl->expl_comment = $abts->expl_comment;
			$expl->expl_prix = $abts->expl_prix;		
			$expl->destinataire = $abts->destinataire;
		}				
		// s�lection de la cote dewey de la notice chapeau pour pr�-renseignement de la cote en cr�ation expl
		$query_cote = "select indexint_name from indexint, notices, bulletins where bulletin_id='$bul_id' and bulletin_notice=notice_id and notices.indexint=indexint.indexint_id ";
		$myQuery_cote = mysql_query($query_cote , $dbh);
		if(mysql_num_rows($myQuery_cote)) {
			$pre_cote = mysql_fetch_object($myQuery_cote);
			$expl->expl_cote = $pre_cote->indexint_name ;
		}	
	}	
	$bull_form.= bul_do_form($expl);
	$templates=str_replace("!!form!!",$bull_form,$templates);	
}
$templates=str_replace("!!serialcir_print!!",$serialcir_print,$templates);
$templates=str_replace("!!print_cote!!",$print_cote,$templates);

print $templates;
?>