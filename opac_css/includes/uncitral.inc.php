<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: uncitral.inc.php,v 1.12 2013-02-03 18:26:20 gueluneau Exp $

require($class_path."/cms/cms_module_root.class.php");

function search_other_function_filters() {
	global $charset,$dbh;
	global $unc_country,$unc_applicable, $look_DOCNUM;
	global $opac_indexation_docnum_allfields;
	global $unc_docnum;
	global $type_date;
	global $date_decision,$date_decisiony,$date_decision1,$date_decision2;

	if(!is_array($unc_country)) $unc_country=array();
	if(!is_array($unc_applicable)) $unc_applicable=array();
	if(!isset($look_DOCNUM)) $look_DOCNUM='0';
	if(!is_array($unc_docnum)) $unc_docnum=array();
	if(!isset($type_date)) $type_date=0;
	
	$unc_aff="<span id='unc_search'><table><tbody><tr>";
	
	//Pour la sélection par "country"
	/*$q="SELECT num_noeud, libelle_categorie FROM noeuds join categories on num_noeud=id_noeud and num_parent!=42 WHERE libelle_categorie NOT LIKE  '~%' AND langue =  'en_UK' ORDER  BY libelle_categorie";
	$r=mysql_query($q,$dbh);
	$unc_aff.="<td><label>Countries</label><br />";
	$unc_aff.="<select name='unc_country[]' multiple='multiple' >" ;
	if (mysql_num_rows($r)) {
		while (($row = mysql_fetch_object($r))) {
			$selected='';
			if (in_array($row->num_noeud, $unc_country)) {
				$selected="selected='selected' ";
			}
			$unc_aff.= "<option value='$row->num_noeud' $selected>".htmlentities($row->libelle_categorie,ENT_QUOTES,$charset)."</option>";
		}
	}
	$unc_aff.="</select></td>";

	//Pour la sélection par "applicable NYC provisions"
	$q="select notices_custom_list_value,notices_custom_list_lib from notices_custom_lists where notices_custom_champ='10' order by ordre";
	$r=mysql_query($q,$dbh);
	$unc_aff.="<td><label>Applicable NYC provisions</label><br />";
	$unc_aff.="<select name='unc_applicable[]' multiple='multiple' >" ;
	if (mysql_num_rows($r)) {
		while (($row = mysql_fetch_object($r))) {
			$selected='';
			if (in_array($row->notices_custom_list_value,$unc_applicable)) {
				$selected="selected='selected' ";
			}
			$unc_aff.= "<option value='$row->notices_custom_list_value' $selected>".htmlentities($row->notices_custom_list_lib,ENT_QUOTES,$charset)."</option>";
		}
	}
	$unc_aff.="</select></td>";*/
		
	//Pour la selection par "country" avec liste de checkbox
	$q="SELECT num_noeud, libelle_categorie FROM noeuds join categories on num_noeud=id_noeud and num_parent!=42 WHERE libelle_categorie NOT LIKE  '~%' AND langue =  'en_UK' ORDER  BY libelle_categorie";
	$r=mysql_query($q,$dbh);
	$unc_aff.="<td><label>Countries</label><br />";
	$unc_aff.="<ul id='list_countries'>" ;
	if (mysql_num_rows($r)) {
		while (($row = mysql_fetch_object($r))) {
			$checked='';
			if (in_array($row->num_noeud, $unc_country)) {
				$checked="checked='checked' ";
			}
			$unc_aff.= "<li><label for='c$row->num_noeud'><input name='unc_country[]' type='checkbox' id='c$row->num_noeud' value='$row->num_noeud' $checked/>&nbsp;".htmlentities($row->libelle_categorie,ENT_QUOTES,$charset)."</label></li>";
		}
	}
	$unc_aff.="</ul></td>";
	
	//Pour la sélection par "applicable NYC provisions"
	$q="select notices_custom_list_value,notices_custom_list_lib from notices_custom_lists where notices_custom_champ='10' order by ordre";
	$r=mysql_query($q,$dbh);
	$unc_aff.="<td><label>Applicable NYC provisions</label><br />";
	$unc_aff.="<ul id='list_provisions'>" ;
		if (mysql_num_rows($r)) {
		while (($row = mysql_fetch_object($r))) {
			$checked='';
			if (in_array($row->notices_custom_list_value,$unc_applicable)) {
				$checked="checked='checked' ";
			}
			$unc_aff.= "<li><label for='a$row->notices_custom_list_value'><input name='unc_applicable[]' type='checkbox' id='a$row->notices_custom_list_value' value='$row->notices_custom_list_value' $checked/>&nbsp;".htmlentities($row->notices_custom_list_lib,ENT_QUOTES,$charset)."</label></li>";
		}
	}
	$unc_aff.="</ul></td>";
	
	//Pour la sélection "Search in attachments"
	/*$unc_aff.="<td><div><input type='checkbox' name='look_DOCNUM' id='look_DOCNUM' value='1' ";
	if ($opac_indexation_docnum_allfields) $unc_aff.="checked='checked' ";
	$unc_aff.="/><label for='look_DOCNUM'>&nbsp;Search in attachments</label></div><br/><br/>";*/
	$unc_aff.="<td><div><input type='checkbox' name='dont_check_opac_indexation_docnum_allfields' id='dont_check_opac_indexation_docnum_allfields' value='1' ";
	if ($dont_check_opac_indexation_docnum_allfields) $unc_aff.="checked='checked' ";
	$unc_aff.="/><label for='dont_check_opac_indexation_docnum_allfields'>Exclude full text of Decisions</label></div><br/><br/>";
	
	//Pour la selection par nom de document numérique
	$unc_aff.="<div><label>Search only Decisions containing:</label><br />";
	$checked="";
	if (in_array("Original Language",$unc_docnum)) $checked="checked='checked' ";
	$unc_aff.="<div><label for='d1'><input name='unc_docnum[]' type='checkbox' id='d1' value='Original Language' $checked/>&nbsp;Original Language</label></div>";
	$checked="";
	if (in_array("Official Translation",$unc_docnum)) $checked="checked='checked' ";
	$unc_aff.="<div><label for='d2'><input name='unc_docnum[]' type='checkbox' id='d2' value='Official Translation' $checked/>&nbsp;Official Translation</label></div>";
	$checked="";
	if (in_array("Unofficial Translation",$unc_docnum)) $checked="checked='checked' ";
	$unc_aff.="<div><label for='d3'><input name='unc_docnum[]' type='checkbox' id='d3' value='Unofficial Translation' $checked/>&nbsp;Unofficial Translation</label></div>";
	$unc_aff.="</div></td>";
	
	//Récupération du calendrier
	$unc_aff.="<script type='text/javascript' src='includes/javascript/calendarDateInput.js'>
	
	/***********************************************
	 * Jason's Date Input Calendar- By Jason Moon http://calendar.moonscript.com/dateinput.cfm
	* Script featured on and available at http://www.dynamicdrive.com
	* Keep this notice intact for use.
	***********************************************/
	
	</script>";
	
	//Pour la selection par date de décision
	
		//Script pour l'affichage conditionnel des champs de date
	$unc_aff.="<script>
	function aff_date_field(id) {
		switch(id) {
			case 0:
				document.getElementById('date').style.display = 'none';
				document.getElementById('year').style.display = 'none';
				document.getElementById('period').style.display = 'none';
				break;
			case 1:
				document.getElementById('date').style.display = 'block';
				document.getElementById('year').style.display = 'none';
				document.getElementById('period').style.display = 'none';
				break;
			case 2:
				document.getElementById('date').style.display = 'none';
				document.getElementById('year').style.display = 'block';
				document.getElementById('period').style.display = 'none';
				break;
			case 3:
				document.getElementById('date').style.display = 'none';
				document.getElementById('year').style.display = 'none';
				document.getElementById('period').style.display = 'block';
				break;
		}
	}
	</script>
	<td>
		<table>
			<tr>";
		//Selecteur de type de date (Date précise, année seule ou période)
	$unc_aff .= "<td id='date_decision'>";
	$unc_aff .= "<select id='type_date' name='type_date' style='width:130px;' onChange='aff_date_field(this.selectedIndex);'>";
	$unc_aff .= "<option value='0'>Date of decision</option>";
	$selected = "";
	if ($type_date == 1) $selected = "selected='selected' ";
	$unc_aff .= "<option value='1' $selected>By date</option>";
	$selected = "";
	if ($type_date == 2) $selected = "selected='selected' ";
	$unc_aff .= "<option value='2' $selected>By year</option>";
	$selected = "";
	if ($type_date == 3) $selected = "selected='selected' ";
	$unc_aff .= "<option value='3' $selected>By period</option>";
	$unc_aff .= "</select><br/><br/>";
	
		//Affichage des champs de date
	$date = "";
	if ($type_date*1 == 1) $date = ",'$date_decision'";
	$unc_aff .= "<div id='date'><script>DateInput('date_decision', true, 'YYYY-MM-DD'$date)</script></div>";
	
	$value = "";
	if (($type_date*1 == 2) && ($date_decisiony*1)) $value = "value='$date_decisiony' ";
	$unc_aff .= "<div id='year' style='display:none;'><input type='number' maxlength='4' size='4' name='date_decisiony' $value/></div>";
	
	$date1 = "";
	$date2 = "";
	if ($type_date*1 == 3) {
		$date1 = ",'$date_decision1'";
		$date2 = ",'$date_decision2'";
	}
	$unc_aff .= "<div id='period' style='display:none;'>From&nbsp;<script>DateInput('date_decision1', true, 'YYYY-MM-DD'$date1)</script>to&nbsp;<script>DateInput('date_decision2', true, 'YYYY-MM-DD'$date2)</script></div>";
	$unc_aff .= "</td>
			</tr>
			<tr>
				<td>
					<input type='button' class='bouton' name='clearSearch' value='Clear Fields' onClick='clear_search(document.search_input);return false;'>
				</td>
			</tr>
		</table>
	</td>";
	$unc_aff .= "<script>
	function clear_search(form){
		var els = form.elements;
		
		for(i=0;i<els.length;i++){
		
			//Countries, Provisions, Attachments	
			if(els[i].type=='checkbox')
			els[i].checked = false;
			
			//Terms
			if(els[i].type=='text')
			els[i].value = '';
			
		}

		//Dates
		var maDate=new Date();
		document.getElementsByName('date_decision')[0].value='".date("Y-m-d")."';	
		document.getElementById('type_date').selectedIndex=0;
		
		//Je n'ai pas trouvé d'appel plus propre pour ré-initialier les éléments de formulaire du calendarDateInput
		//Donc je vais chercher les valeurs par défaut
		
		var tmp;
		for (var i = 0; i < 3; i++) {
			tmp='';
			if(i!=0){
				tmp = i;
			}
			//jours
			document.getElementById('date_decision'+tmp+'_Day_ID').value = maDate.getDate();
			//mois
			document.getElementById('date_decision'+tmp+'_Month_ID').selectedIndex = maDate.getMonth();
			//années
			document.getElementById('date_decision'+tmp+'_Year_ID').value = maDate.getFullYear();
		}
		aff_date_field(0);
		
	}
	</script>";
	$unc_aff .= "<script>aff_date_field(document.getElementById('type_date').selectedIndex)</script>";
	
	//Fermeture des balises
	$unc_aff.="</tr></tbody></table></span>";
	
	return $unc_aff;
}


function search_other_function_clause() {
	
	
	//doit retourner une requete de selection d'identifiants de notices
	global $unc_country,$unc_applicable;
	global $jurisdiction,$provision;
	global $unc_docnum;
	global $type_date;
	global $date_decision,$date_decisiony,$date_decision1,$date_decision2;

	if(!is_array($unc_country)) $unc_country=array();
	$country = implode(",",$unc_country);
	if(!is_array($unc_applicable)) $unc_applicable=array();
	$applicable = implode(",",$unc_applicable);
	if(!is_array($unc_docnum)) $unc_docnum=array();
	$docnum = implode("','",$unc_docnum);
	
	$r='';
	$r1 = '';
	$r2='';
	$r3='';
	$r4='';
	$r5='';
	$r6='';
	
	if ($country) {
		$r1 = 'select distinct notcateg_notice as notice_id from notices_categories where (num_noeud != 42 and num_noeud in ('.$country.')) ';
	}
	if(in_array(42,$unc_country)) {
		$r1.= 'or (num_noeud in (select distinct id_noeud from noeuds where num_parent=42))' ;
	}
	if ($applicable) {
		$r2 = "select distinct notices_custom_origine as notice_id from notices_custom_values where notices_custom_champ='10' and notices_custom_integer in (".$applicable.")";
	}
	if (($jurisdiction*1) && ($jurisdiction*1 != 19)) {
		$r3 = "select distinct notcateg_notice as notice_id from notices_categories a0 join cms_sections_descriptors a1 on (a0.num_noeud = a1.num_noeud) where num_section = ".$jurisdiction;
	} else if ($jurisdiction*1) {
		$r3 = "select distinct notcateg_notice as notice_id from notices_categories a0 join noeuds a1 on (a0.num_noeud = a1.id_noeud) where num_parent = 42";
	}
	if ($provision*1) {
		$r4 = "select distinct notices_custom_origine as notice_id from notices_custom_values a0 join cms_editorial_custom_values a1 on a0.notices_custom_integer = a1.cms_editorial_custom_integer where cms_editorial_custom_champ = 14 and notices_custom_champ = 10 and cms_editorial_custom_origine = ".$provision;
	}
	if ($docnum) {
		$r5 = "select distinct explnum_notice as notice_id from explnum where explnum_nom in ('".$docnum."')";
	}
	if ($type_date*1 == 1) {
		$r6 = "select distinct notices_custom_origine as notice_id from notices_custom_values where notices_custom_champ = '6' and notices_custom_date = '$date_decision'";
	} else if ($type_date*1 == 2) {
		$r6 = "select distinct notices_custom_origine as notice_id from notices_custom_values where notices_custom_champ = '6' and YEAR(notices_custom_date) = $date_decisiony";
	} else if ($type_date*1 == 3) {
		$r6 = "select distinct notices_custom_origine as notice_id from notices_custom_values where notices_custom_champ = '6' and notices_custom_date >= '$date_decision1' and notices_custom_date <= '$date_decision2'";
	}
	
	
	if ($r1 && !$r2 && !$r5 && !$r6)	$r = $r1;
	if (!$r1 && $r2 && !$r5 && !$r6)	$r = $r2;
	if (!$r1 && !$r2 && $r5 && !$r6)	$r = $r5;
	if (!$r1 && !$r2 && !$r5 && $r6)	$r = $r6;
	
	if ($r1 && $r2 && !$r5 && !$r6)		$r = "select * from ($r1) as q1 where notice_id in ($r2) ";
	if ($r1 && !$r2 && $r5 && !$r6)		$r = "select * from ($r1) as q1 where notice_id in ($r5) ";
	if ($r1 && !$r2 && !$r5 && $r6)		$r = "select * from ($r1) as q1 where notice_id in ($r6) ";
	if (!$r1 && $r2 && $r5 && !$r6)		$r = "select * from ($r2) as q1 where notice_id in ($r5) ";
	if (!$r1 && $r2 && !$r5 && $r6)		$r = "select * from ($r2) as q1 where notice_id in ($r6) ";
	if (!$r1 && !$r2 && $r5 && $r6)		$r = "select * from ($r5) as q1 where notice_id in ($r6) ";
	
	if ($r1 && $r2 && $r5 && !$r6)		$r = "select * from ($r1) as q1 where (notice_id in ($r2) and notice_id in ($r5)) ";
	if ($r1 && $r2 && !$r5 && $r6)		$r = "select * from ($r1) as q1 where (notice_id in ($r2) and notice_id in ($r6)) ";
	if ($r1 && !$r2 && $r5 && $r6)		$r = "select * from ($r1) as q1 where (notice_id in ($r5) and notice_id in ($r6)) ";
	if (!$r1 && $r2 && $r5 && $r6)		$r = "select * from ($r2) as q1 where (notice_id in ($r5) and notice_id in ($r6)) ";
	
	if ($r1 && $r2 && $r5 && $r6)		$r = "select * from ($r1) as q1 where (notice_id in ($r2) and notice_id in ($r5) and notice_id in ($r6)) ";
	
	if ($r3)					$r = $r3;
	if ($r4)					$r = $r4;

	return $r;
}


function search_other_function_has_values() {
	global $unc_country,$unc_applicable,$unc_docnum,$type_date;
	
	if ( (is_array($unc_country) && count($unc_country)) || (is_array($unc_applicable) && count($unc_applicable)) || (is_array($unc_docnum) && count($unc_docnum)) || $type_date ) return true; else return false;
}


function search_other_function_get_values(){
	global $unc_country,$unc_applicable,$provision,$jurisdiction,$unc_docnum,$type_date,$date_decision,$date_decisiony,$date_decision1,$date_decision2;
	
	return serialize(array($unc_country,$unc_applicable,$provision,$jurisdiction,$unc_docnum,$type_date,$date_decision,$date_decisiony,$date_decision1,$date_decision2));
}


function search_other_function_rec_history($n) {
	global $unc_country,$unc_applicable,$jurisdiction,$provision,$unc_docnum,$type_date,$date_decision,$date_decisiony,$date_decision1,$date_decision2;
	
	$_SESSION['unc_country'.$n]=$unc_country;
	$_SESSION['unc_applicable'.$n]=$unc_applicable;
	$_SESSION['jurisdiction'.$n]=$jurisdiction;
	$_SESSION['provision'.$n]=$provision;
	$_SESSION['unc_docnum'.$n]=$unc_docnum;
	$_SESSION['type_date'.$n]=$type_date;
	$_SESSION['date_decision'.$n]=$date_decision;
	$_SESSION['date_decisiony'.$n]=$date_decisiony;
	$_SESSION['date_decision1'.$n]=$date_decision1;
	$_SESSION['date_decision2'.$n]=$date_decision2;
}


function search_other_function_get_history($n) {
	global $unc_country,$unc_applicable,$jurisdiction,$provision,$unc_docnum,$type_date,$date_decision,$date_decisiony,$date_decision1,$date_decision2;
	
	$unc_country=$_SESSION['unc_country'.$n];
	$unc_applicable=$_SESSION['unc_applicable'.$n];
	$jurisdiction=$_SESSION['jurisdiction'.$n];
	$provision=$_SESSION['provision'.$n];
	$unc_docnum=$_SESSION['unc_docnum'.$n];
	$type_date=$_SESSION['type_date'.$n];
	$date_decision=$_SESSION['date_decision'.$n];
	$date_decisiony=$_SESSION['date_decisiony'.$n];
	$date_decision1=$_SESSION['date_decision1'.$n];
	$date_decision2=$_SESSION['date_decision2'.$n];
}


function search_other_function_human_query($n) {
	global $unc_country,$unc_applicable,$jurisdiction,$provision,$unc_docnum,$date_decision,$date_decisiony,$date_decision1,$date_decision2;
	global $dbh;
	
	$ret='';
	$unc_country=$_SESSION['unc_country'.$n];
	$unc_applicable=$_SESSION['unc_applicable'.$n];
	$jurisdiction=$_SESSION['jurisdiction'.$n];
	$provision=$_SESSION['provision'.$n];
	$unc_docnum=$_SESSION['unc_docnum'.$n];
	$type_date=$_SESSION['type_date'.$n];
	$date_decision=$_SESSION['date_decision'.$n];
	$date_decisiony=$_SESSION['date_decisiony'.$n];
	$date_decision1=$_SESSION['date_decision1'.$n];
	$date_decision2=$_SESSION['date_decision2'.$n];
	
	$app=array();
	if ($jurisdiction) {
		$requete="select num_noeud from cms_sections_descriptors where num_section=$jurisdiction";
		$r=mysql_query($requete);
		if (mysql_num_rows($r)) $unc_country=array(mysql_result($r,0,0));
	}
	if (count($unc_country)) {
		$q="select libelle_categorie from categories where num_noeud in (".implode(',',$unc_country).") and langue='en_UK' ";
		$r=mysql_query($q,$dbh);
		if (mysql_num_rows($r)) {
			while($row=mysql_fetch_object($r)) $app[] = $row->libelle_categorie;
		}
	}
	if (count($app)) $ret.="Country : ".implode(' or ',$app);
	
	$app=array();
	if ($provision) {
		$requete="select cms_editorial_custom_integer from cms_editorial_custom_values where cms_editorial_custom_origine=$provision";
		$r=mysql_query($requete);
		if (mysql_num_rows($r)) $unc_applicable=array(mysql_result($r,0,0));
	}
	if (count($unc_applicable)) {
 		$q="select notices_custom_list_lib from notices_custom_lists where notices_custom_champ='10' and notices_custom_list_value in (".implode(',',$unc_applicable).") ";
       	$r=mysql_query($q,$dbh);
       	if (mysql_num_rows($r)) {
			while($row=mysql_fetch_object($r)) $app[] = $row->notices_custom_list_lib;
		}
	}
	if($ret && $app) $ret.=", ";
	if (count($app)) $ret.="Applicable NYC provision : ".implode(' or ',$app);
	
	if ($ret && $unc_docnum) $ret.=". ";
	if ($unc_docnum) $ret.="Attachment : ".implode(' or ',$unc_docnum);
	
	if ($type_date && $ret) $ret .= ", ";
	if ($type_date*1 == 1) {
		$date = array();
		$date = explode('-', $date_decision);
		$ret .= "By date : $date[1]/$date[2]/$date[0]";
	}
	if (($type_date*1 == 2) && ($date_decisiony*1)) $ret .= "By year : $date_decisiony";
	if ($type_date*1 == 3) {
		$date1 = array();
		$date1 = explode('-', $date_decision1);
		$date2 = array();
		$date2 = explode('-', $date_decision2);
		$ret .= "By period : from $date1[1]/$date1[2]/$date1[0] to $date2[1]/$date2[2]/$date2[0]";
	}
	
	return $ret;
}


function search_other_function_post_values() {
	global $unc_country,$unc_applicable,$jurisdiction,$provision,$unc_docnum,$type_date,$date_decision,$date_decisiony,$date_decision1,$date_decision2;
	$retour='';
	if (is_array($unc_country) && count($unc_country)) {
		foreach($unc_country as $v) {
			$retour.= "<input type='hidden' name='unc_country[]' value='".$v."' />\n";
		}
	}
	if (is_array($unc_applicable) && count($unc_applicable)) {
		foreach($unc_applicable as $v) {
			$retour.= "<input type='hidden' name='unc_applicable[]' value='".$v."' />\n";
		}
	}
	if ($jurisdiction*1) {
		$retour.= "<input type='hidden' name='jurisdiction' value='".$jurisdiction."' />\n";
	}
	if ($provision*1) {
		$retour.= "<input type='hidden' name='provision' value='".$provision."' />\n";
	}
	if (is_array($unc_docnum) && count($unc_docnum)) {
		foreach($unc_docnum as $v) {
			$retour.= "<input type='hidden' name='unc_docnum[]' value='".$v."' />\n";
		}
	}
	if ($type_date*1) {
		$retour .= "<input type='hidden' name='type_date' value='".$type_date."' />\n
		<input type='hidden' name='date_decision' value='$date_decision' />
		<input type='hidden' name='date_decision1' value='$date_decision1' />
		<input type='hidden' name='date_decision2' value='$date_decision2' />";
	}
	if ($date_decisiony*1) {
		$retour .= "<input type='hidden' name='date_decisiony' value='".$date_decisiony."' />\n";
	}

	return $retour;
}
