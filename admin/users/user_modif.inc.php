<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: user_modif.inc.php,v 1.49 2013-12-24 14:26:26 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once ("$class_path/marc_table.class.php");
require_once("$class_path/actes.class.php");
require_once("$class_path/suggestions_map.class.php");
require_once("$class_path/lignes_actes_statuts.class.php");

$requete = "SELECT username, nom, prenom, rights, userid, user_lang, ";
$requete .="nb_per_page_search, nb_per_page_select, nb_per_page_gestion, ";
$requete .="param_popup_ticket, param_sounds, ";
$requete .="user_email, user_alert_resamail, user_alert_demandesmail, user_alert_subscribemail, explr_invisible, explr_visible_mod, explr_visible_unmod, grp_num FROM users WHERE userid='$id' LIMIT 1 ";
$res = mysql_query($requete, $dbh);
$nbr = mysql_num_rows($res);
if ($nbr) {
	$usr=mysql_fetch_object($res);
} else die ('Unknown user');

$requete_param = "SELECT * FROM users WHERE userid='$id' LIMIT 1 ";
$res_param = mysql_query($requete_param, $dbh);
$field_values = mysql_fetch_row ( $res_param );

$param_user="<div class='row'><b>".$msg["1500"]."</b></div>\n";
$deflt_user="<div class='row'><b>".$msg["1501"]."</b></div>\n";

$acquisition_user_param="";

$i = 0;
while ($i < mysql_num_fields($res_param)) {
	$field = mysql_field_name($res_param, $i) ;
	$field_deb = substr($field,0,6);
	switch ($field_deb) {

		case "deflt_" :
			if ($field=="deflt_styles") {
				$deflt_user_style="
					<div class='row'>
						<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
						</div>
						<div class='colonne_suite'>".make_user_style_combo($field_values[$i])."
						</div>
					</div>\n";
			} elseif ($field=="deflt_docs_location") {
				//visibilit� des exemplaires
				if ($pmb_droits_explr_localises && $usr->explr_visible_mod) $where_clause_explr = "idlocation in (".$usr->explr_visible_mod.") and";
				else $where_clause_explr = "";
				$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location, docsloc_section where $where_clause_explr num_location=idlocation order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "account_calcule_section(this);", $field_values[$i], "", "","","",0);
				$deflt_user.="
					<div class='row'>
						<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
						</div>\n
						<div class='colonne_suite'>".$selector."
						</div>
					</div>\n";
				//localisation de l'utilisateur pour le calcul de la section
				$location_user_section = $field_values[$i];
			} elseif ($field=="deflt_collstate_location") {
				$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "", $field_values[$i], "", "","0",$msg["all_location"],0);
				$deflt_user.="
					<div class='row'><div class='colonne60'>".
					$msg[$field]."&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>"			
					.$selector.
					"</div></div>\n";
			} elseif ($field=="deflt_resas_location") {
				$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "", $field_values[$i], "", "","0",$msg["all_location"],0);
				$deflt_user.="
					<div class='row'><div class='colonne60'>".
					$msg[$field]."&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>"			
					.$selector.
					"</div></div>\n";
			} elseif ($field=="deflt_docs_section") {
				// calcul des sections
				$selector="";
				if (!$location_user_section) $location_user_section = $deflt_docs_location;
				if ($pmb_droits_explr_localises && $usr->explr_visible_mod) $where_clause_explr = "where idlocation in (".$usr->explr_visible_mod.")";
				else $where_clause_explr = "";
				$rqtloc = "SELECT idlocation FROM docs_location $where_clause_explr order by location_libelle";
				$resloc = mysql_query($rqtloc, $dbh);
				while ($loc=mysql_fetch_object($resloc)) {
					$requete = "SELECT idsection, section_libelle FROM docs_section, docsloc_section where idsection=num_section and num_location='$loc->idlocation' order by section_libelle";
					$result = mysql_query($requete, $dbh);
					$nbr_lignes = mysql_num_rows($result);
					if ($nbr_lignes) {
						if ($loc->idlocation==$location_user_section ) $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:block\">\r\n";
						else $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:none\">\r\n";
						$selector .= "<select name='f_ex_section".$loc->idlocation."' id='f_ex_section".$loc->idlocation."'>\r\n";
						while($line = mysql_fetch_row($result)) {
							$selector .= "<option value='$line[0]' ";
							$selector .= (($line[0] == $field_values[$i]) ? "selected='selected' >" : '>');
				 			$selector .= htmlentities($line[1],ENT_QUOTES, $charset).'</option>\r\n';
						}
						$selector .= '</select></div>';
					}
				}
				$deflt_user.="
					<div class='row'>
						<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
						</div>\n
						<div class='colonne_suite'>".$selector."
						</div>
					</div>\n";
			} elseif ($field=="deflt_upload_repertoire") {
				$selector = "";
					$requpload = "select repertoire_id, repertoire_nom from upload_repertoire";
					$resupload = mysql_query($requpload, $dbh);
					$selector .=  "<div id='upload_section'>";
					$selector .= "<select name='form_deflt_upload_repertoire'>";
					$selector .= "<option value='0'>".$msg[upload_repertoire_sql]."</option>";
					while(($repupload = mysql_fetch_object($resupload))){
						$selector .= "<option value='".$repupload->repertoire_id."' ";
						if ($field_values[$i] == $repupload->repertoire_id ) {
							$selector .= "selected='selected' ";
						}
						$selector .= ">";
						$selector .= htmlentities($repupload->repertoire_nom,ENT_QUOTES,$charset)."</option>";
					}
					$selector .=  "</select></div>";
					$deflt_user.="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>
							<div class='colonne_suite'>".$selector."
							</div>
						</div>";
			} elseif($field=="deflt_import_thesaurus"){
				$requete="select * from thesaurus order by 2";
				$resultat_liste=mysql_query($requete,$dbh);
				$nb_liste=mysql_num_rows($resultat_liste);
				if ($nb_liste==0) {
					$deflt_user.="" ;
				} else {
					$deflt_user.="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>\n
							<div class='colonne_suite'>
								<select class='saisie-30em' name=\"form_".$field."\">";
					$j=0;
					while ($j<$nb_liste) {
						$liste_values = mysql_fetch_row ( $resultat_liste );
						$deflt_user.="<option value=\"".$liste_values[0]."\" " ;
						if ($field_values[$i]==$liste_values[0]) {
							$deflt_user.="selected='selected' " ;
						}
						$deflt_user.=">".$liste_values[1]."</option>\n" ;
						$j++;
					}
					$deflt_user.="</select>
							</div>
						</div>\n" ;
				}
				
			} elseif ($field=="deflt_short_loan_activate") {
					$deflt_user.="<div class='row'><div class='colonne60'>".$msg[$field]."</div>\n
						<div class='colonne_suite'>
						<input type='checkbox' class='checkbox'";
					if ($field_values[$i]==1) $deflt_user.=" checked"; 
					$deflt_user.=" value='1' name='form_$field'></div></div>\n" ;
			} elseif ($field=="deflt_cashdesk"){
					$requete="select * from cashdesk order by cashdesk_name";
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_num_rows($resultat_liste);
					if ($nb_liste==0) {
						$deflt_user.="" ;
					} else {
						$deflt_user.="
							<div class='row'>
								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
								</div>\n
								<div class='colonne_suite'>
									<select class='saisie-30em' name=\"form_".$field."\">";
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_object( $resultat_liste );
							$deflt_user.="<option value=\"".$liste_values->cashdesk_id."\" " ;
							if ($field_values[$i]==$liste_values->cashdesk_id) {
								$deflt_user.="selected" ;
							}
							$deflt_user.=">".htmlentities($liste_values->cashdesk_name,ENT_QUOTES,$charset)."</option>\n" ;
							$j++;
						}
						$deflt_user.="</select>
								</div>
							</div>\n" ;
					}
				}else {
				$deflt_table = substr($field,6);
				if($deflt_table == "integration_notice_statut") $deflt_table= "notice_statut";
				switch($field) {
					case "deflt_entites":
						$requete="select id_entite, raison_sociale from ".$deflt_table." where type_entite='1' order by 2 ";
						break;
					case "deflt_exercices":
						$requete="select id_exercice, libelle from ".$deflt_table." order by 2 ";
						break;
					case "deflt_rubriques":
						$requete="select id_rubrique, concat(budgets.libelle,':', rubriques.libelle) from ".$deflt_table." join budgets on num_budget=id_budget order by 2 ";
						break;
					default :
						$requete="select * from ".$deflt_table." order by 2";
						break;
				}

				$resultat_liste=mysql_query($requete,$dbh);
				$nb_liste=mysql_num_rows($resultat_liste);
				if ($nb_liste==0) {
					$deflt_user.="" ;
				} else {
					$deflt_user.="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>\n
							<div class='colonne_suite'>
								<select class='saisie-30em' name=\"form_".$field."\">";
					$j=0;
					while ($j<$nb_liste) {
						$liste_values = mysql_fetch_row ( $resultat_liste );
						$deflt_user.="<option value=\"".$liste_values[0]."\" " ;
						if ($field_values[$i]==$liste_values[0]) {
							$deflt_user.="selected='selected' " ;
						}
						$deflt_user.=">".$liste_values[1]."</option>\n" ;
						$j++;
					}
					$deflt_user.="</select>
							</div>
						</div>\n" ;
				}
			}
			break;

		case "param_" :
			if ($field=="param_allloc") {
				$param_user_allloc="<div class='row'><div class='colonne60'>".$msg[$field]."</div>\n
					<div class='colonne_suite'>
					<input type='checkbox' class='checkbox'";
				if ($field_values[$i]==1) $param_user_allloc.=" checked";
				$param_user_allloc.=" value='1' name='form_$field'></div></div>\n" ;
			} else {
				$param_user.="<div class='row'>";
				//if (strpos($msg[$field],'<br />')) $param_user .= "<br />";
				$param_user.="<input type='checkbox' class='checkbox'";
				if ($field_values[$i]==1) $param_user.=" checked";
				$param_user.=" value='1' name='form_$field'>\n
					$msg[$field]
					</div>\n";
			}
			break ;

		case "value_" :
			switch ($field) {
				case "value_deflt_fonction" :
					$flist=new marc_list('function');
					$f=$flist->table[$field_values[$i]];
					$value_user.="<div class='row'><div class='colonne60'>
					$msg[$field]&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>
					<input type='text' class='saisie-30emr' id='form_value_deflt_fonction_libelle' name='form_value_deflt_fonction_libelle' value='".htmlentities($f,ENT_QUOTES, $charset)."' />
					<input type='button' class='bouton_small' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=function&caller=userform&p1=form_value_deflt_fonction&p2=form_value_deflt_fonction_libelle', 'select_func0', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
					<input type='button' class='bouton_small' value='X' onclick=\"this.form.elements['form_value_deflt_fonction'].value='';this.form.elements['form_value_deflt_fonction_libelle'].value='';return false;\" />
					<input type='hidden' name='form_value_deflt_fonction' id='form_value_deflt_fonction' value=\"$field_values[$i]\" />
					</div></div><br />";
					break;
				case "value_deflt_lang" :
					$llist=new marc_list('lang');
					$l=$llist->table[$field_values[$i]];
					$value_user.="<div class='row'><div class='colonne60'>
					$msg[$field]&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>
					<input type='text' class='saisie-30emr' id='form_value_deflt_lang_libelle' name='form_value_deflt_lang_libelle' value='".htmlentities($l,ENT_QUOTES, $charset)."' />
					<input type='button' class='bouton_small' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=lang&caller=userform&p1=form_value_deflt_lang&p2=form_value_deflt_lang_libelle', 'select_lang', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
					<input type='button' class='bouton_small' value='X' onclick=\"this.form.elements['form_value_deflt_lang'].value='';this.form.elements['form_value_deflt_lang_libelle'].value='';return false;\" />
					<input type='hidden' name='form_value_deflt_lang' id='form_value_deflt_lang' value=\"$field_values[$i]\" />
					</div></div><br />";
					break;
				case "value_deflt_relation" :
				case "value_deflt_relation_serial" :
				case "value_deflt_relation_bulletin" :
				case "value_deflt_relation_analysis" :
					$value_user.="<div class='row'><div class='colonne60'>
					$msg[$field]&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>";
					
					$liste_type_relation_down=new marc_list("relationtypedown");
					$liste_type_relation_up=new marc_list("relationtypeup");
					$liste_type_relation_both=array();
					
					foreach($liste_type_relation_up->table as $key_up=>$val_up){
						foreach($liste_type_relation_down->table as $key_down=>$val_down){
							if($val_up==$val_down){
								$liste_type_relation_both[$key_down]=$val_down;
								unset($liste_type_relation_down->table[$key_down]);
								unset($liste_type_relation_up->table[$key_up]);
							}
						}
					}
					
					$value_user.="<select onchange='' name='form_".$field."' size='1'>
					<optgroup class='erreur' label='$msg[notice_lien_montant]'>";
					
					foreach($liste_type_relation_up->table as $key=>$val){
						if($key.'-up'==$field_values[$i]){
							$value_user.='<option  style="color:#000000" value="'.$key.'-up" selected="selected">'.$val.'</option>';
						}else{
							$value_user.='<option  style="color:#000000" value="'.$key.'-up">'.$val.'</option>';
						}
					}
					$value_user.="</optgroup>
					<optgroup class='erreur' label='$msg[notice_lien_descendant]'>";
					
					foreach($liste_type_relation_down->table as $key=>$val){
						if($key.'-down'==$field_values[$i]){
							$value_user.='<option  style="color:#000000" value="'.$key.'-down" selected="selected" >'.$val.'</option>';
						}else{
							$value_user.='<option  style="color:#000000" value="'.$key.'-down">'.$val.'</option>';
						}
					}
					$value_user.="</optgroup>
					<optgroup class='erreur' label='$msg[notice_lien_symetrique]'>";
					
					foreach($liste_type_relation_both as $key=>$val){
						if($key.'-down'==$field_values[$i]){
							$value_user.='<option  style="color:#000000" value="'.$key.'-down" selected="selected" >'.$val.'</option>';
						}else{
							$value_user.='<option  style="color:#000000" value="'.$key.'-down">'.$val.'</option>';
						}
					}
					$value_user.="</optgroup>
					</select>";
					$value_user.="</div></div><br />";
					break;
				default :
					$value_user.="<div class='row'><div class='colonne60'>
					$msg[$field]&nbsp;:&nbsp;</div>\n
					<div class='colonne_suite'>
					<input type='text' class='saisie-20em' name='form_$field' value='".htmlentities($field_values[$i],ENT_QUOTES, $charset)."' />
					</div></div><br />";
					break;
			}
			break ;

		case "deflt2" :
			if ($field=="deflt2docs_location") {
				// localisation des lecteurs
				$deflt_table = substr($field,6);
				$requete="select * from ".$deflt_table." order by 2";
				$resultat_liste=mysql_query($requete,$dbh);
				$nb_liste=mysql_num_rows($resultat_liste);
				if ($nb_liste==0) {
					$deflt_user.="" ;
				} else {
					$deflt_user.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
						<div class='colonne_suite'>
						<select class='saisie-30em' name=\"form_".$field."\">";

					$j=0;
					while ($j<$nb_liste) {
						$liste_values = mysql_fetch_row ( $resultat_liste );
						$deflt_user.="<option value=\"".$liste_values[0]."\" " ;
						if ($field_values[$i]==$liste_values[0]) {
							$deflt_user.="selected='selected' " ;
						}
						$deflt_user.=">".$liste_values[1]."</option>\n" ;
						$j++;
					}
					$deflt_user.="</select></div></div>!!param_allloc!!<br />\n" ;
				}
			} else {
				$deflt_table = substr($field,6);
				$requete="select * from ".$deflt_table." order by 2 ";
				$resultat_liste=mysql_query($requete,$dbh);
				$nb_liste=mysql_numrows($resultat_liste);
				if ($nb_liste==0) {
					$deflt_user.="" ;
				} else {
					$deflt_user.="
							<div class='row'><div class='colonne60'>".
								$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
							<div class='colonne_suite'>
								<select class='saisie-30em' name=\"form_".$field."\">";
					$j=0;
					while ($j<$nb_liste) {
						$liste_values = mysql_fetch_row ( $resultat_liste );
						$deflt_user.="<option value=\"".$liste_values[0]."\" " ;
						if ($field_values[$i]==$liste_values[0]) {
							$deflt_user.="selected='selected' " ;
						}
						$deflt_user.=">".$liste_values[1]."</option>\n" ;
						$j++;
					}
					$deflt_user.="</select></div></div>\n" ;
				}
			}
			break;

		case "xmlta_" :
			switch($field) {
				case "xmlta_indexation_lang" :
					$langues = new XMLlist("$include_path/messages/languages.xml");
					$langues->analyser();
					$clang = $langues->table;
				
					$combo = "<select name='form_".$field."' id='form_".$field."' class='saisie-20em' >";
					if(!$field_values[$i]) $combo .= "<option value='' selected>--</option>";
					else $combo .= "<option value='' >--</option>";
					while(list($cle, $value) = each($clang)) {
						// arabe seulement si on est en utf-8
						if (($charset != 'utf-8' and $user_lang != 'ar') or ($charset == 'utf-8')) {
							if(strcmp($cle, $field_values[$i]) != 0) $combo .= "<option value='$cle'>$value ($cle)</option>";
							else $combo .= "<option value='$cle' selected>$value ($cle)</option>";
						}
					}
					$combo .= "</select>";
					$deflt_user.="
					<div class='row'><div class='colonne60'>".
					$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
					<div class='colonne_suite'>$combo</div></div>\n" ;
					break;
				case "xmlta_doctype_serial" :
					$deflt_user.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
						<div class='colonne_suite'>";
					$select_doc = new marc_select("doctype", "form_".$field, $field_values[$i], "");
					$deflt_user.= $select_doc->display ;
					$deflt_user.="</div></div>\n" ;
					break;
				case "xmlta_doctype_bulletin" :
				case "xmlta_doctype_analysis" :
					$deflt_user.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
						<div class='colonne_suite'>";
					$select_doc = new marc_select("doctype", "form_".$field, $field_values[$i], "","0",$msg[$field."_parent"]);
					$deflt_user.= $select_doc->display ;
					$deflt_user.="</div></div>\n" ;
					break;
				default :
					$deflt_table = substr($field,6);
					$deflt_user.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$deflt_user.= "
						<div class='colonne_suite'>";
					$select_doc = new marc_select("$deflt_table", "form_".$field, $field_values[$i], "");
					$deflt_user.= $select_doc->display ;
					$deflt_user.="</div></div>\n" ;
					break;
			}
		case "deflt3" :
			$q='';
			$t=array();
			switch($field) {
				case "deflt3bibli":
					$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
					$q.="select id_entite, raison_sociale from entites where type_entite='1' order by 2 ";
					break;
				case "deflt3exercice":
					$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
					$q.="select id_exercice, libelle from exercices order by 2 ";
					break;
				case "deflt3rubrique":
					$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
					$q.="select id_rubrique, concat(budgets.libelle,':', rubriques.libelle) from rubriques join budgets on num_budget=id_budget order by 2 ";
					break;
				case "deflt3dev_statut":
					$t=actes::getStatelist(TYP_ACT_DEV);
					break;
				case "deflt3cde_statut":
					$t=actes::getStatelist(TYP_ACT_CDE);
					break;
				case "deflt3liv_statut":
					$t=actes::getStatelist(TYP_ACT_LIV);
					break;
				case "deflt3fac_statut":
					$t=actes::getStatelist(TYP_ACT_FAC);
					break;
				case "deflt3sug_statut":
					$m=new suggestions_map();
					$t=$m->getStateList();
					break;
				case 'deflt3lgstatcde':
				case 'deflt3lgstatdev':
					$q=lgstat::getList('QUERY');
					break;
				case 'deflt3receptsugstat':
					$m=new suggestions_map();
					$t=$m->getStateList('ORDERED',TRUE);
					break;
			}
			if($q) {
				$r=mysql_query($q, $dbh);
				$nb=mysql_num_rows($r);
				while($row=mysql_fetch_row($r)) {
					$t[$row[0]]=$row[1];
				}
			}
			if (count($t)) {
				$deflt3user.="<div class='row'><div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;</div>\n";
				$deflt3user.= "<div class='colonne_suite'><select class='saisie-30em' name=\"form_".$field."\">";
				foreach($t as $k=>$v) {
					$deflt3user.="<option value=\"".$k."\" " ;
					if ($field_values[$i]==$k) {
						$deflt3user.="selected='selected' " ;
					}
					$deflt3user.=">".htmlentities($v, ENT_QUOTES, $charset)."</option>\n" ;
				}
				$deflt3user.="</select></div></div><br />\n";
			}
			break;

		case "speci_" :
			$speci_func = substr($field, 6);
			eval('$speci_user.= get_'.$speci_func.'($id, $field_values, $i, \'userform\');');
			break;

		case "explr_" :
			$$field=$field_values[$i];
			break;
		default :
			break ;
		}
		$i++;
	}

//visibilit� des exemplaires
if ($pmb_droits_explr_localises) {

	$explr_tab_invis=explode(",",$explr_invisible);
	$explr_tab_unmod=explode(",",$explr_visible_unmod);
	$explr_tab_modif=explode(",",$explr_visible_mod);

	$visibilite_expl_user="
		<div class='row'><hr /></div>
		<div class='row'>
			<div class='colonne3'>".$msg["expl_visibilite"]."&nbsp;:&nbsp;</div>
			<div class='colonne_suite'>&nbsp;</div>
		</div>\n";
	$requete_droits_expl="select idlocation, location_libelle from docs_location order by location_libelle";
	$resultat_droits_expl=mysql_query($requete_droits_expl);
	$temp="";
	while ($j=mysql_fetch_array($resultat_droits_expl)) {
		$temp.=$j["idlocation"].",";
		$visibilite_expl_user.= "
			<div class='row'>
				<div class='colonne3' align='right'>".$j["location_libelle"]." : </div>
				<div class='colonne_suite'>&nbsp;<select name=\"form_expl_visibilite_".$j["idlocation"]."\">
			";
		$as_invis = array_search($j["idlocation"],$explr_tab_invis);
		$as_unmod = array_search($j["idlocation"],$explr_tab_unmod);
		$as_mod = array_search($j["idlocation"],$explr_tab_modif);
		$visibilite_expl_user .="\n<option value='explr_invisible' ".($as_invis!== FALSE && $as_invis!== NULL?"selected='selected' ":"").">".$msg["explr_invisible"]."</option>";
		if (($as_mod!== FALSE && $as_mod !== NULL)||($as_unmod!== FALSE && $as_unmod !== NULL)||($as_invis!== FALSE && $as_invis !== NULL)) {
			$visibilite_expl_user .="\n<option value='explr_visible_unmod' ".($as_unmod!== FALSE && $as_unmod!== NULL?"selected='selected' ":"").">".$msg["explr_visible_unmod"]."</option>";
		} else {
			$visibilite_expl_user .="\n<option value='explr_visible_unmod' selected='selected' >".$msg["explr_visible_unmod"]."</option>";
		}
		$visibilite_expl_user .="\n<option value='explr_visible_mod' ".($as_mod!== FALSE && $as_mod!== NULL?"selected='selected' ":"").">".$msg["explr_visible_mod"]."</option>";
		$visibilite_expl_user.="</select></div></div>\n" ;
	}
	mysql_free_result($resultat_droits_expl);

	if ((!$explr_invisible)&&(!$explr_visible_unmod)&&(!$explr_visible_mod)) {
		$rqt="UPDATE users SET explr_invisible=0,explr_visible_mod=0,explr_visible_unmod='".substr($temp,0,strlen($temp)-1)."' WHERE userid=$id";
		@mysql_query($rqt);
	}

	$deflt_user .=$visibilite_expl_user;
	} //fin visibilit� des exemplaires

$param_default="
<div class='row'><hr /></div>
		$param_user
	<div class='row'><hr /></div>
		".str_replace("!!param_allloc!!",$param_user_allloc,$deflt_user)."
	<br />
	<div class='row'><hr /></div>
		$value_user
	<div class='row'><hr /></div>
		$deflt_user_style
	<br />";
if ($speci_user || $deflt3user) {
	$param_default.= "<div class='row'><hr /></div>";
	$param_default.=$deflt3user;
	$param_default.=$speci_user;
	$param_default.= "<div class='row'></div>";
}

echo window_title($msg[1003].$msg[18].$msg[1003].$msg[86].$msg[1003].$usr->username.$msg[1001]);
user_form(	$usr->username,
			$usr->nom,
			$usr->prenom,
			$usr->rights,
			$usr->userid,
			$usr->user_lang,
			$usr->nb_per_page_search,
			$usr->nb_per_page_select,
			$usr->nb_per_page_gestion,
			$param_default,
			$usr->user_email,
			$usr->user_alert_resamail,
			$usr->user_alert_demandesmail,
			$usr->user_alert_subscribemail,
			$usr->grp_num
			);
echo form_focus('userform', 'form_nom');
