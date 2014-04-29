<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sel_sub_collection.tpl.php,v 1.18 2013-12-27 09:27:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// templates du s�lecteur sous-collections

//--------------------------------------------
//	$nb_per_page : nombre de lignes par page
//--------------------------------------------
// nombre de r�f�rences par pages
if ($nb_per_page_sc_select != "") 
	$nb_per_page = $nb_per_page_sc_select ;
	else $nb_per_page = 10;

//-------------------------------------------
//	$sel_header : header
//-------------------------------------------

$sel_header = "
<div class='row'>
	<label for='titre_select_subcoll' class='etiquette'>$msg[194]</label>
	</div>
<div class='row'>
";

//-------------------------------------------
//	$jscript : script de m.a.j. du parent
//-------------------------------------------

/* pour $dyn=3, renseigner les champs suivants: (pass� dans l'url)
 *
* $max_field : nombre de champs existant
* $field_id : id de la cl�
* $field_name_id : id  du champ text
* $add_field : nom de la fonction permettant de rajouter un champ
*
*/
if ($dyn==3) {
	$jscript ="
<script type='text/javascript'>
	function set_parent(f_caller, id_value, libelle_value){
		var w=window;
		var i=0;
		if(!(typeof w.opener.$add_field == 'function')) {
			w.opener.document.getElementById('$field_id').value = id_value;
			w.opener.document.getElementById('$field_name_id').value = reverse_html_entities(libelle_value);
			parent.parent.close();
			return;
		}
		var n_element=w.opener.document.forms[f_caller].elements['$max_field'].value;
		var flag = 1;
		
		//V�rification que l'�l�ment n'est pas d�j� s�lectionn�e
		for (var i=0; i<n_element; i++) {
			if (w.opener.document.getElementById('$field_id'+i).value==id_value) {
				alert('".$msg["term_already_in_use"]."');
				flag = 0;
				break;
			}
		}
		if (flag) {
			for (var i=0; i<n_element; i++) {
				if ((w.opener.document.getElementById('$field_id'+i).value==0)||(w.opener.document.getElementById('$field_id'+i).value=='')) break;
			}
		
			if (i==n_element) w.opener.$add_field();
			w.opener.document.getElementById('$field_id'+i).value = id_value;
			w.opener.document.getElementById('$field_name_id'+i).value = reverse_html_entities(libelle_value);
		}	
	}
</script>";
}elseif ($dyn==2) { // Pour les liens entre autorit�s
	$jscript = "
	<script type='text/javascript'>
	<!--
	function set_parent(f_caller, id_value, libelle_value)
	{	
		w=window;
		n_aut_link=w.opener.document.forms[f_caller].elements['max_aut_link'].value;
		flag = 1;	
		//V�rification que l'autorit� n'est pas d�j� s�lectionn�e
		for (i=0; i<n_aut_link; i++) {
			if (w.opener.document.getElementById('f_aut_link_id'+i).value==id_value && w.opener.document.getElementById('f_aut_link_table'+i).value==$p1) {
				alert('".$msg["term_already_in_use"]."');
				flag = 0;
				break;
			}
		}	
		if (flag) {
			for (i=0; i<n_aut_link; i++) {
				if ((w.opener.document.getElementById('f_aut_link_id'+i).value==0)||(w.opener.document.getElementById('f_aut_link_id'+i).value=='')) break;
			}	
			if (i==n_aut_link) w.opener.add_aut_link();
			
			var selObj = w.opener.document.getElementById('f_aut_link_table_list');
			var selIndex=selObj.selectedIndex;
			w.opener.document.getElementById('f_aut_link_table'+i).value= selObj.options[selIndex].value;
			
			w.opener.document.getElementById('f_aut_link_id'+i).value = id_value;
			w.opener.document.getElementById('f_aut_link_libelle'+i).value = reverse_html_entities('['+selObj.options[selIndex].text+']'+libelle_value);		
		}	
	}
	-->
	</script>
	";
}else
$jscript = "
<script type='text/javascript'>
<!--
function set_parent(f_caller, idSubColl, libelleSubColl, idParent, idLibelleParent, idEd, libelleEd)
{
	window.opener.document.forms[f_caller].elements['$p1'].value = idEd;
	window.opener.document.forms[f_caller].elements['$p2'].value = reverse_html_entities(libelleEd);
	window.opener.document.forms[f_caller].elements['$p3'].value = idParent;
	window.opener.document.forms[f_caller].elements['$p4'].value = reverse_html_entities(idLibelleParent);
	window.opener.document.forms[f_caller].elements['$p5'].value = idSubColl;
	window.opener.document.forms[f_caller].elements['$p6'].value = reverse_html_entities(libelleSubColl);
	window.close();
}
-->
</script>
";
if($mode=="un")
$jscript = "
<script type='text/javascript'>
<!--
function set_parent(f_caller, idSubColl, libelleSubColl, idParent, idLibelleParent, idEd, libelleEd, callback)
{
	window.opener.document.forms[f_caller].elements['$p1'].value = idSubColl;
	window.opener.document.forms[f_caller].elements['$p2'].value = reverse_html_entities(libelleSubColl);
	if(callback)
		window.opener[callback]('$infield');
	window.close();
}
-->
</script>
";

//-------------------------------------------
//	$sel_search_form : module de recherche
//-------------------------------------------
$sel_search_form ="
<form name='search_form' method='post' action='$base_url'>
<input type='text' name='f_user_input' value=\"!!deb_rech!!\" >&nbsp;
<input type='submit' class='bouton_small' value='$msg[142]' />&nbsp;
!!bouton_ajouter!!
</form>
<script type='text/javascript'>
<!--
	document.forms['search_form'].elements['f_user_input'].focus();
-->
</script>
";

// ------------------------------------------
// 	$sub_collection_form : form saisie collection
// ------------------------------------------
$sub_collection_form = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.collection_nom.value.length == 0)
			{
				alert(\"$msg[166]\");
				return false;
			}
		if(form.coll_id.value == 0)
			{
				alert(\"$msg[180]\");
				return false;
			}
		return true;
	}
-->
</script>
<form name='saisie_sub_collection' method='post' action=\"$base_url&action=update\">
<h3>$msg[177]</h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette'>$msg[67]</label>
		</div>
	<div class='row'>
		<input type='text' size='40' name='collection_nom' value=\"!!deb_saisie!!\" />
		</div>
	<div class='row'>
		<label class='etiquette'>$msg[179]</label>
		</div>
	<div class='row'>
		<input type='text' class='saisie-30emr' name='coll_libelle' value='' readonly />
		
		<input class='bouton_small' type='button' onclick=\"openPopUp('./select.php?what=collection&caller=saisie_sub_collection&p1=ed_id&p2=ed_libelle&p3=coll_id&p4=coll_libelle&p5=dsubcoll_id&p6=dsubcoll_lib', 'select_coll', $selector_x_size, $selector_y_size, -2, -2, 'toolbar=no, resizable=yes')\" title='$msg[157]' value='$msg[parcourir]' />
		<input type='button' class='bouton_small' value='$msg[raz]' onclick=\"this.form.ed_libelle.value=''; this.form.ed_id.value='0'; this.form.coll_libelle.value=''; this.form.coll_id.value='0'; \" />
		
		<input type='hidden' name='coll_id' value='0'>
		</div>
	<div class='row'>
		<label class='etiquette'>$msg[164]</label>
		</div>
	<div class='row'>
		<input type='text' class='saisie-30emr' name='ed_libelle' value='' readonly />
		<input type='hidden' name='dsubcoll_id'>
		<input type='hidden' name='dsubcoll_lib'>
		<input type='hidden' name='ed_id' value='0'>
		</div>
	<div class='row'>
		<label class='etiquette'>$msg[165]</label>
		</div>
	<div class='row'>
		<input type='text' size='40' name='issn' value='' maxlength='12'>
		</div>
	</div>
<div class='row'>
	<input type='button' class='bouton_small' value='$msg[76]' onClick=\"document.location='$base_url'\">
	<input type='submit' value='$msg[77]' class='bouton_small' onClick=\"return test_form(this.form)\">
	</div>
</form>
<script type='text/javascript'>
	document.forms['saisie_sub_collection'].elements['collection_nom'].focus();
</script>
";

//-------------------------------------------
//	$sel_footer : footer
//-------------------------------------------
$sel_footer = "
</div>
";
