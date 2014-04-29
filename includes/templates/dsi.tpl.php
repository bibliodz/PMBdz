<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: dsi.tpl.php,v 1.64 2014-03-12 14:41:30 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

require_once("$include_path/templates/export_param.tpl.php");

$dsi_menu = "
<div id='menu'>
<h3 onclick='menuHide(this,event)'>$msg[dsi_menu_diffusion]</h3>
<ul>
<li><a href='./dsi.php?categ=diffuser&sub=lancer'>$msg[dsi_menu_dif_lancer]</a></li>
<li><a href='./dsi.php?categ=diffuser&sub=auto'>$msg[dsi_menu_dif_auto]</a></li>
<li><a href='./dsi.php?categ=diffuser&sub=manu'>$msg[dsi_menu_dif_manu]</a></li>
</ul>
<h3 onclick='menuHide(this,event)'>$msg[dsi_menu_bannettes]</h3>
<ul>
<li><a href='./dsi.php?categ=bannettes&sub=pro'>$msg[dsi_menu_ban_pro]</a></li>
<li><a href='./dsi.php?categ=bannettes&sub=abo'>$msg[dsi_menu_ban_abo]</a></li>
</ul>
<h3 onclick='menuHide(this,event)'>$msg[dsi_menu_equations]</h3>
<ul>
<li><a href='./dsi.php?categ=equations&sub=gestion'>".$msg['dsi_menu_equ_gestion']."</a></li>
</ul>
<h3 onclick='menuHide(this,event)'>$msg[dsi_menu_options]</h3>
<ul>
<li><a href='./dsi.php?categ=options&sub=classements'>".$msg['dsi_menu_cla_gestion']."</a></li>
</ul>
<h3 onclick='menuHide(this,event)'>$msg[dsi_menu_flux]</h3>
<ul>
<li><a href='./dsi.php?categ=fluxrss&sub=definition'>".$msg['dsi_menu_flux_definition']."</a></li>
</ul>
</div>
";

// $dsi_layout : layout page DSI
$dsi_layout = "
<div id='conteneur' class='$current_module'>
$dsi_menu
<div id='contenu'>";

// $dsi_layout_end : layout page DSI (fin)
$dsi_layout_end = '
</div></div>
';

// $dsi_search_tmpl : template pour le form de recherche d'empr
$dsi_search_tmpl = "
<form class='form-$current_module' id='saisie_cb_ex' name='saisie_cb_ex' method='post' action='!!form_action!!' >
<h3>!!titre_formulaire!!</h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette' for='form_cb'>!!message!!</label>
		</div>
	<div class='row'>
		<input class='saisie-20em' id='form_cb' type='text' name='form_cb' value=\"!!cb_initial!!\" title='$msg[3000]' />  !!restrict_location!!
		</div>
	</div>
<div class='row'>
	<input type='submit' class='bouton' value='$msg[502]' />
	</div>
</form>
<script type='text/javascript'>
document.forms['saisie_cb_ex'].elements['form_cb'].focus();
</script>";

// $dsi_search_bannette_tmpl : template pour le form de recherche de bannette
$dsi_search_bannette_tmpl = "
<form class='form-$current_module' id='saisie_cb_ex' name='saisie_cb_ex' method='post' action='!!form_action!!' >
<h3>!!titre_formulaire!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='form_cb'>!!message!!</label>
			</div>
		<div class='row'>
			<input class='saisie-20em' id='form_cb' type='text' name='form_cb' value=\"!!cb_initial!!\" title='$msg[3000]' />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='form_classement'>".$msg['dsi_classement']."</label>
			</div>
		<div class='row'>
			!!classement!!
			</div>
		</div>
	</div>
<div class='row'></div>
</div>

<div class='row'>
	<input type='submit' class='bouton' value='$msg[502]' />
	<input type='button' class='bouton' value='$msg[ajouter]' onclick=\"this.form.suite.value='add'; this.form.submit();\" />
	<input type='hidden' name='suite' value='search' />
	</div>
</form>
<script type='text/javascript'>
document.forms['saisie_cb_ex'].elements['form_cb'].focus();
</script>";

// $dsi_search_equation_tmpl : template pour le form de recherche d'�quation
$dsi_search_equation_tmpl = "
<form class='form-$current_module' id='saisie_cb_ex' name='saisie_cb_ex' method='post' action='!!form_action!!' >
<h3>!!titre_formulaire!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='form_cb'>!!message!!</label>
			</div>
		<div class='row'>
			<input class='saisie-20em' id='form_cb' type='text' name='form_cb' value=\"!!cb_initial!!\" title='$msg[3000]' />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='form_classement'>".$msg['dsi_classement']."</label>
			</div>
		<div class='row'>
			!!classement!!
			</div>
		</div>
	</div>
<div class='row'></div>
</div>
<div class='row'>
	<input type='submit' class='bouton' value='$msg[502]' />
	<input type='button' class='bouton' value='$msg[ajouter]' onclick=\"document.location='./catalog.php?categ=search&mode=6';\" />
	<input type='hidden' name='suite' value='search' />
	</div>
</form>
<script type='text/javascript'>
document.forms['saisie_cb_ex'].elements['form_cb'].focus();
</script>";

// template pour la liste emprunteurs, bannettes ou �quations
$dsi_list_tmpl = "
<h1>!!message_trouve!!</h1>
<script type='text/javascript' src='./javascript/sorttable.js'></script>
<table border='0' width='100%' class='sortable'>
!!list!!
</table>
<div class='row'>
!!nav_bar!!
</div>
";

$selector_prop = "toolbar=no, dependent=yes, width=$selector_x_size, height=$selector_y_size, resizable=yes, scrollbars=yes";

$dsi_desc_field = "
<script src='javascript/ajax.js'></script>
<div id='att' style='z-Index:1000'></div>

<div class='row'>
	<div class='colonne'>
		<div class='row'>
			<label for='dsi_desc_form_desc'>".$msg['dsi_ban_form_desc']."</label>
		</div>
		<div class='row'>
			!!cms_categs!!
			<div id='addcateg'/></div>
		</div>
	</div>
</div>
<script type='text/javascript'>

	function add_categ() {
		template = document.getElementById('addcateg');
		categ=document.createElement('div');
		categ.className='row';
			
		suffixe = eval('document.saisie_bannette.max_categ.value')
		nom_id = 'f_categ'+suffixe
		f_categ = document.createElement('input');
		f_categ.setAttribute('name',nom_id);
		f_categ.setAttribute('id',nom_id);
		f_categ.setAttribute('type','text');
		f_categ.className='saisie-80emr';
		f_categ.setAttribute('value','');
		f_categ.setAttribute('completion','categories_mul');
		f_categ.setAttribute('autfield','f_categ_id'+suffixe);
		
		del_f_categ = document.createElement('input');
		del_f_categ.setAttribute('id','del_f_categ'+suffixe);
		del_f_categ.onclick=fonction_raz_categ;
		del_f_categ.setAttribute('type','button');
		del_f_categ.className='bouton';
		del_f_categ.setAttribute('readonly','');
		del_f_categ.setAttribute('value','$msg[raz]');
			
		f_categ_id = document.createElement('input');
		f_categ_id.name='f_categ_id'+suffixe;
		f_categ_id.setAttribute('type','hidden');
		f_categ_id.setAttribute('id','f_categ_id'+suffixe);
		f_categ_id.setAttribute('value','');
			
		categ.appendChild(f_categ);
		space=document.createTextNode(' ');
		categ.appendChild(space);
		categ.appendChild(del_f_categ);
		categ.appendChild(f_categ_id);
			
		template.appendChild(categ);
		
		document.saisie_bannette.max_categ.value=suffixe*1+1*1 ;
		ajax_pack_element(f_categ);
	}
	function fonction_selecteur_categ() {
		name=this.getAttribute('id').substring(4);
		name_id = name.substr(0,7)+'_id'+name.substr(7);
		openPopUp('./select.php?what=categorie&caller=saisie_bannette&p1='+name_id+'&p2='+name+'&dyn=1', 'select_categ', 700, 500, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes');
	}
	function fonction_raz_categ() {
		name=this.getAttribute('id').substring(4);
		name_id = name.substr(0,7)+'_id'+name.substr(7);
		document.getElementById(name_id).value=0;
		document.getElementById(name).value='';
	}
</script>";
$dsi_desc_first_desc = "
<div class='row'>
	<input type='hidden' name='max_categ' value=\"!!max_categ!!\" />
	<input type='text' class='saisie-80emr' id='f_categ!!icateg!!' name='f_categ!!icateg!!' value=\"!!categ_libelle!!\" completion=\"categories_mul\" autfield=\"f_categ_id!!icateg!!\" />
		
	<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.f_categ!!icateg!!.value=''; this.form.f_categ_id!!icateg!!.value='0'; \" />
	<input type='button' class='bouton' value='$msg[parcourir]' onclick=\"openPopUp('./select.php?what=categorie&caller='+this.form.name+'&p1=f_categ_id!!icateg!!&p2=f_categ!!icateg!!&dyn=1&parent=0&deb_rech=', 'select_categ', 700, 500, -2, -2, '$select_categ_prop')\" />
	<input type='hidden' name='f_categ_id!!icateg!!' id='f_categ_id!!icateg!!' value='!!categ_id!!' />
	<input type='button' class='bouton' value='+' onClick=\"add_categ();\"/>
</div>";
$dsi_desc_other_desc = "
<div class='row'>
	<input type='text' class='saisie-80emr' id='f_categ!!icateg!!' name='f_categ!!icateg!!' value=\"!!categ_libelle!!\" completion=\"categories_mul\" autfield=\"f_categ_id!!icateg!!\" />
		
	<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.f_categ!!icateg!!.value=''; this.form.f_categ_id!!icateg!!.value='0'; \" />
	<input type='hidden' name='f_categ_id!!icateg!!' id='f_categ_id!!icateg!!' value='!!categ_id!!' />
</div>";




// $dsi_bannette_form : form saisie des bannettes publiques
$dsi_bannette_form = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.nom_bannette.value.replace(/^\s+|\s+$/g, '').length == 0)
			{
				alert(\"${msg[dsi_ban_nom_oblig]}\");
				return false;
			}
		return true;
	}
function confirm_delete() {
        result = confirm(\"${msg[confirm_suppr]}\");
        if(result)
            document.location='./dsi.php?categ=bannettes&suite=delete&sub=!!type!!&id_bannette=!!id_bannette!!';
        else
            document.forms['saisie_bannette'].elements['nom_bannette'].focus();
    }
-->
</script>
<form class='form-$current_module' id='saisie_bannette' name='saisie_bannette' method='post' action='!!action!!'>
<h3>!!libelle!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='nom_bannette'>$msg[dsi_ban_form_nom]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-30em' name='nom_bannette' value=\"!!nom_bannette!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='num_classement'>$msg[dsi_ban_form_classement]</label>
			</div>
		<div class='row'>
			!!num_classement!!
			</div>
		</div>
	</div>
<div class='row'>
	<label for='comment_gestion' class='etiquette'>$msg[dsi_ban_form_com_gestion]</label>
	</div>
<div class='row'>
	<textarea id='comment_gestion' name='comment_gestion' cols='120' rows='2' wrap='virtual'>!!comment_gestion!!</textarea>
	</div>
<div class='row'>
	<label for='comment_public' class='etiquette'>$msg[dsi_ban_form_com_public]</label>
	</div>
<div class='row'>
	<textarea id='comment_public' name='comment_public' cols='120' rows='2' wrap='virtual'>!!comment_public!!</textarea>
	</div>
<div class='row'></div>

<div class='row'>
	<label for='entete_mail' class='etiquette'>$msg[dsi_ban_form_entete_mail]</label>
	</div>
<div class='row'>
	<textarea id='entete_mail' name='entete_mail' cols='120' rows='6' wrap='virtual' >!!entete_mail!!</textarea>
	</div>
	
<div class='row'>
	<label for='piedpage_mail' class='etiquette'>$msg[dsi_ban_form_piedpage_mail]</label>
	</div>
<div class='row'>
	<textarea id='piedpage_mail' name='piedpage_mail' cols='120' rows='6' wrap='virtual' >!!piedpage_mail!!</textarea>
	</div>
	
<div class='row'>
	<div class='colonne3'>
		<label for='notice_tpl' class='etiquette'>$msg[dsi_ban_form_select_notice_tpl]</label>
		!!notice_tpl!!
	</div>
	<div class='colonne3'>
		<label for='notice_tpl' class='etiquette'>$msg[dsi_ban_form_regroupe_pperso]</label>
		<input type='radio' name='group_type' value='0' !!checked_group_pperso!! class='saisie-simple'>
		!!pperso_group!!
	</div>	
	<div class='colonne3'>
		<label for='notice_tpl' class='etiquette'>$msg[dsi_ban_form_froup_facette]</label>
		<input type='radio' name='group_type' value='1' !!checked_group_facette!! class='saisie-simple'>
		!!facette_group!!
	</div>	
</div>
	
<div class='row'><hr /></div>

<div class='row'>
	<div class='colonne2'>
		<label for='date_last_remplissage' class='etiquette'>$msg[dsi_ban_date_last_remp]</label>
		!!date_last_remplissage!!
		</div>
	<div class='colonne_suite'>
		<label for='date_last_remplissage' class='etiquette'>$msg[dsi_ban_date_last_envoi]</label>
		!!date_last_envoi!!
		</div>
	</div>
<div class='row'>
	<div class='colonne2'>
		<label for='archive_number' class='etiquette'>$msg[dsi_archive_number]</label>
		<input type='text' class='saisie-5em' name='archive_number'  value=\"!!archive_number!!\" />
	</div>
	<div class='colonne_suite'>
		
	</div>
</div>
<div class='row'><hr /></div>

<div class='row'>
	<label for='proprio_bannette' class='etiquette'>$msg[dsi_ban_proprio_bannette]</label>
	!!proprio_bannette!!
	</div>
<div class='row'></div>

<div class='row'>
	<div class='colonne3'>
		<label for='bannette_auto' class='etiquette'>$msg[dsi_ban_form_ban_auto]</label>
		<input type='checkbox' name='bannette_auto' !!bannette_auto!! value=\"1\" />
		</div>
	<div class='colonne3'>
		<label for='periodicite' class='etiquette'>$msg[dsi_ban_form_periodicite]</label>
		<input type='text' class='saisie-5em' name='periodicite' value=\"!!periodicite!!\" />
		</div>
	</div>
<div class='row'>
	<div class='colonne2'>
		<label for='diffusion_email' class='etiquette'>$msg[dsi_ban_form_diff_email]</label>
		<input type='checkbox' name='diffusion_email' !!diffusion_email!! value=\"1\" />
		</div>
	<div class='colonne_suite'>
		<label for='bannette_nb_notices_diff' class='etiquette'>$msg[dsi_ban_form_nb_notices_diff]</label>
		<input type='text' name='nb_notices_diff' value=\"!!nb_notices_diff!!\" />
		</div>
	</div>
<div class='row'>
		<label for='update_type' class='etiquette'>$msg[dsi_ban_update_type]</label>
		!!update_type!!
		</div>
<div class='row'>
		<label for='statut_not_account' class='etiquette'>".$msg["dsi_ban_statut_not_account"]."</label>
		<input type='checkbox' name='statut_not_account' !!statut_not_account!! value=\"1\" />
</div>
<div class='row'><hr /></div>

<div class='row'>
	<label for='categorie_lecteurs' class='etiquette'>$msg[dsi_ban_form_categ_lect]</label>
	!!categorie_lecteurs!!
	<input type='hidden' name='majautocateg' value=\"0\" />
	</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label for='groupe_lecteurs' class='etiquette'>$msg[dsi_ban_form_groupe_lect]</label>
	!!groupe_lecteurs!!
	<input type='hidden' name='majautogroupe' value=\"0\" />
	</div>


<div class='row'><hr /></div>	
	!!desc_fields!!
<div class='row'><hr /></div>

<div class='row'>
	<label for='num_panier' class='etiquette'>$msg[dsi_panier_diffuser]</label>
	!!num_panier!!
	</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<label for='limite_type' class='etiquette'>".$msg[dsi_ban_type_cumul]." : </label>
		!!limite_type!!
	<label for='limite_nombre' class='etiquette'>".$msg[dsi_ban_cumul_taille]." : </label>
		<input type='text' name='limite_nombre' class='saisie-5em' value=\"!!limite_nombre!!\" />
	</div>

<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne2'>
	<label for='typeexport' class='etiquette'>".$msg[dsi_ban_typeexport]." : </label>
		!!typeexport!!
	</div>
	<div class='colonne_suite'>
	<label for='prefixe_fichier' class='etiquette'>".$msg[dsi_ban_prefixe_fichier]." : </label>
		<input type='text' name='prefixe_fichier' class='saisie-15em' value=\"!!prefixe_fichier!!\" />
	</div>
	<div class='row'></div>
</div>
<div class='row'>	
	<label for='bannette_opac_page_accueil' class='etiquette'>".$msg['bannette_opac_page_accueil']."</label>
	<input type='checkbox' name='bannette_opac_accueil' !!bannette_opac_accueil_check!! value=\"1\" />
</div>	

<div class='row' id='liste_parametre' style='!!display_liste_param!!'>&nbsp;!!form_param!!</div>

<div class='row'><hr /></div>

<div class='row'>
	<label class='etiquette'>".$msg["dsi_ban_document_title"]."</label>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne2'>
		<label for='document_generate' class='etiquette'>".$msg["dsi_ban_document_generate"]."</label>
		<input type='checkbox' name='document_generate' !!document_generate!! value='1'>	
	</div>
	<div class='colonne_suite'>
		<label for='document_notice_tpl' class='etiquette'>".$msg["dsi_ban_document_notice_tpl"]."</label>
		!!document_notice_tpl!!
	</div>
</div>
<div class='row'>
	<div class='colonne2'>
		<label for='document_group' class='etiquette'>".$msg["dsi_ban_document_group"]."</label>
		<input type='checkbox' name='document_group' !!document_group!! value='1'>	

	</div>
	<div class='colonne_suite'>
		<label for='document_add_summary' class='etiquette'>".$msg["dsi_ban_document_add_summary"]."</label>
		<input type='checkbox' name='document_add_summary' !!document_add_summary!! value='1'>
	</div>
</div>
<div class='row'>&nbsp;</div>
<div class='row'>
	<div class='colonne2' style='display:none' >		
		<label for='document_insert_docnum' class='etiquette'>".$msg["dsi_ban_document_insert_docnum"]."</label>
		<input type='checkbox' name='document_insert_docnum' !!document_insert_docnum!! value='1'>	
	</div>
	<div class='colonne_suite'>		
	</div>
</div>
<div class='row'>&nbsp;</div>
</div>
<div class='row'>
	<div class='left'>
		<input type='submit' value='$msg[77]' class='bouton' onClick=\"return test_form(this.form)\" />
		!!link_duplicate!!
		!!link_annul!!
		<input type='hidden' name='form_actif' value='1'>
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>

<script type='text/javascript'>
	document.forms['saisie_bannette'].elements['nom_bannette'].focus();
</script>
";

// $dsi_bannette_form_abo : form saisie des bannettes priv�es
$dsi_bannette_form_abo = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.nom_bannette.value.replace(/^\s+|\s+$/g, '').length == 0)
			{
				alert(\"${msg[dsi_ban_nom_oblig]}\");
				return false;
			}
		return true;
	}
function confirm_delete() {
        result = confirm(\"${msg[confirm_suppr]}\");
        if(result)
            document.location='./dsi.php?categ=bannettes&suite=delete&sub=!!type!!&id_bannette=!!id_bannette!!&id_empr=!!id_empr!!';
        else
            document.forms['saisie_bannette'].elements['nom_bannette'].focus();
    }
-->
</script>
<form class='form-$current_module' id='saisie_bannette' name='saisie_bannette' method='post' action='!!action!!'>
<h3>!!libelle!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='nom_bannette'>$msg[dsi_ban_form_nom]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-30em' name='nom_bannette' value=\"!!nom_bannette!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			</div>
		<div class='row'>
			!!num_classement!!
			</div>
		</div>
	</div>
<div class='row'>
	<label for='comment_gestion' class='etiquette'>$msg[dsi_ban_form_com_gestion]</label>
	</div>
<div class='row'>
	<textarea id='comment_gestion' name='comment_gestion' cols='120' rows='2' wrap='virtual'>!!comment_gestion!!</textarea>
	</div>
<div class='row'>
	<label for='comment_public' class='etiquette'>$msg[dsi_ban_form_com_public]</label>
	</div>
<div class='row'>
	<textarea id='comment_public' name='comment_public' cols='120' rows='2' wrap='virtual'>!!comment_public!!</textarea>
	</div>
<div class='row'></div>

<div class='row'>
	<label for='entete_mail' class='etiquette'>$msg[dsi_ban_form_entete_mail]</label>
	</div>
<div class='row'>
	<textarea id='entete_mail' name='entete_mail' cols='120' rows='6' wrap='virtual'>!!entete_mail!!</textarea>
	</div>
	
<div class='row'>
	<label for='piedpage_mail' class='etiquette'>$msg[dsi_ban_form_piedpage_mail]</label>
	</div>
<div class='row'>
	<textarea id='piedpage_mail' name='piedpage_mail' cols='120' rows='6' wrap='virtual'>!!piedpage_mail!!</textarea>
	</div>
<div class='row'>
	<div class='colonne2'>
		<label for='notice_tpl' class='etiquette'>$msg[dsi_ban_form_select_notice_tpl]</label>
		!!notice_tpl!!
	</div>
	<div class='colonne_suite'>
		<label for='notice_tpl' class='etiquette'>$msg[dsi_ban_form_regroupe_pperso]</label>
		!!pperso_group!!
	</div>	
</div>
	
<div class='row'><hr /></div>

<div class='row'>
	<div class='colonne2'>
		<label for='date_last_remplissage' class='etiquette'>$msg[dsi_ban_date_last_remp]</label>
		!!date_last_remplissage!!
		</div>
	<div class='colonne_suite'>
		<label for='date_last_remplissage' class='etiquette'>$msg[dsi_ban_date_last_envoi]</label>
		!!date_last_envoi!!
		</div>
	</div>
	
<div class='row'>
	<label for='proprio_bannette' class='etiquette'>$msg[dsi_ban_proprio_bannette]</label>
	!!proprio_bannette!!
	</div>
<div class='row'></div>

<div class='row'>
	<div class='colonne3'>
		<label for='bannette_auto' class='etiquette'>$msg[dsi_ban_form_ban_auto]</label>
		<input type='checkbox' name='bannette_auto' !!bannette_auto!! value=\"1\" />
		</div>
	<div class='colonne_suite'>
		<label for='periodicite' class='etiquette'>$msg[dsi_ban_form_periodicite]</label>
		<input type='text' class='saisie-5em' name='periodicite' value=\"!!periodicite!!\" />
		</div>
	</div>
<div class='row'>
	<div class='colonne3'>
		<label for='diffusion_email' class='etiquette'>$msg[dsi_ban_form_diff_email]</label>
		<input type='checkbox' name='diffusion_email' !!diffusion_email!! value=\"1\" />
		</div>
	<div class='colonne_suite'>
		<label for='bannette_nb_notices_diff' class='etiquette'>$msg[dsi_ban_form_nb_notices_diff]</label>
		<input type='text' name='nb_notices_diff' value=\"!!nb_notices_diff!!\" />
		</div>
	</div>
<div class='row'>	
	<label for='update_type' class='etiquette'>$msg[dsi_ban_update_type]</label>
	!!update_type!!
</div>	

<div class='row'>
	!!categorie_lecteurs!!
	</div>
<div class='row'>
	!!groupe_lecteurs!!
	</div>

<div class='row'>&nbsp;</div>

<div class='row'>
	<div class='colonne2'>
	<label for='typeexport' class='etiquette'>".$msg[dsi_ban_typeexport]." : </label>
		!!typeexport!!
	</div>
	<div class='colonne_suite'>
	<label for='prefixe_fichier' class='etiquette'>".$msg[dsi_ban_prefixe_fichier]." : </label>
		<input type='text' name='prefixe_fichier' class='saisie-15em' value=\"!!prefixe_fichier!!\" />
	</div>
</div>
<div class='row'>&nbsp;!!form_param!!</div>

<div class='row'>
	<div class='left'>
		<input type='submit' value='$msg[77]' class='bouton' onClick=\"return test_form(this.form)\" />
		<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./dsi.php?categ=bannettes&sub=!!type!!&id_bannette=&suite=acces&id_empr=!!id_empr!!';\" />
		<input type='hidden' name=id_empr value='!!id_empr!!' />
		<input type='hidden' name='form_actif' value='1'>
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>
<script type='text/javascript'>
	document.forms['saisie_bannette'].elements['nom_bannette'].focus();
</script>
";

// $dsi_classement_form : form saisie des classements
$dsi_classement_form = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.nom_classement.value.replace(/^\s+|\s+$/g, '').length == 0)
			{
				alert(\"${msg[dsi_clas_nom_oblig]}\");
				return false;
			}
		return true;
	}
function confirm_delete() {
        result = confirm(\"${msg[confirm_suppr]}\");
        if(result)
            document.location='./dsi.php?categ=options&suite=delete&sub=classements&id_classement=!!id_classement!!';
        else
            document.forms['saisie_classement'].elements['nom_classement'].focus();
    }
-->
</script>
<form class='form-$current_module' id='saisie_classement' name='saisie_classement' method='post' action='!!action!!'>
<h3>!!libelle!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='nom_bannette'>$msg[dsi_clas_form_nom]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-30em' name='nom_classement' value=\"!!nom_classement!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='type_classement'>$msg[dsi_clas_form_type]</label>
			</div>
		<div class='row'>
			!!type_classement!!
			</div>
		</div>
	</div>
<div class='row'></div>
</div>
<div class='row'>
	<div class='left'>
		<input type='submit' value='$msg[77]' class='bouton' onClick=\"return test_form(this.form)\" />
		<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./dsi.php?categ=options&sub=classements&id_classement=';\" />
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>
<script type='text/javascript'>
	document.forms['saisie_classement'].elements['nom_classement'].focus();
</script>
";

// $dsi_equation_form : form saisie des �quations
$dsi_equation_form = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.nom_equation.value.replace(/^\s+|\s+$/g, '').length == 0)
			{
				alert(\"${msg[dsi_ban_nom_oblig]}\");
				return false;
			}
		return true;
	}
function confirm_delete() {
        result = confirm(\"${msg[confirm_suppr]}\");
        if(result)
            document.location='./dsi.php?categ=equations&suite=delete&id_equation=!!id_equation!!';
        else
            document.forms['saisie_equation'].elements['nom_equation'].focus();
    }
-->
</script>
<form class='form-$current_module' id='saisie_equation' name='saisie_equation' method='post' action='!!action!!'>
<h3>!!libelle!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='nom_equation'>$msg[dsi_equ_form_nom]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-30em' name='nom_equation' value=\"!!nom_equation!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='num_classement'>$msg[dsi_equ_form_classement]</label>
			</div>
		<div class='row'>
			!!num_classement!!
			</div>
		</div>
	</div>
<div class='row'>
	<label for='comment_equation' class='etiquette'>$msg[dsi_ban_form_com_gestion]</label>
	</div>
<div class='row'>
	<textarea id='comment_equation' class='saisie-80em' name='comment_equation' cols='62' rows='2' wrap='virtual'>!!comment_equation!!</textarea>
	</div>
<div class='row'></div>

<div class='row'>
	<label for='requete' class='etiquette'>$msg[dsi_equ_form_requete]</label>
	</div>
<div class='row'>
	!!requete_human!!<input type='hidden' name='requete' value=\"!!requete!!\" />!!bouton_modif_requete!!
	</div>
<div class='row'></div>

<div class='row'>
	<label for='proprio_equation' class='etiquette'>$msg[dsi_ban_proprio_bannette]</label>
	!!proprio_equation!!
	</div>
<div class='row'></div>

</div>
<div class='row'>
	<div class='left'>
		<input type='submit' value='$msg[77]' class='bouton' onClick=\"return test_form(this.form)\" />
		<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./dsi.php?categ=equations&sub=!!type!!&id_equation=';\" />
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>
<script type='text/javascript'>
	document.forms['saisie_equation'].elements['nom_equation'].focus();
</script>
!!form_modif_requete!!
";

// $dsi_bannette_equation_assoce : template pour association des �quations/bannette
$dsi_bannette_equation_assoce = "
<form class='form-$current_module' id='bannette_equation_assoce' name='bannette_equation_assoce' method='post' action='!!form_action!!' >
<h3>$msg[dsi_ban_equ_assoce] : !!nom_bannette!!</h3>
<div class='form-contenu'>
	!!classement!!<br />
	!!equations!!
	</div>
<div class='row'>
	<div class='left'>
		<input type='submit' class='bouton' value='$msg[77]' />
		<input type='hidden' name='id_bannette' value='!!id_bannette!!' />
		<input type='hidden' name='faire' value='enregistrer' />
		<input type='hidden' name='form_cb' value=\"!!form_cb_hidden!!\" />
		<input type='button' class='bouton' value=\"$msg[bt_retour]\" onClick=\"document.location='./dsi.php?categ=bannettes&sub=pro&id_bannette=&suite=search&form_cb=!!form_cb!!';\" />
		</div>
	<div class='right'>
		<input type='button' class='bouton' value=\"".$msg[dsi_ban_affect_lecteurs]."\" onclick=\"document.location='./dsi.php?categ=bannettes&sub=pro&suite=affect_lecteurs&id_bannette=!!id_bannette!!&form_cb=!!form_cb!!'\"/>
		</div>
	</div>
<div class='row'></div>
</form>" ;

// $dsi_bannette_lecteurs_assoce : template pour association des lecteurs/bannette
$dsi_bannette_lecteurs_assoce = "
<script type='text/javascript'>

	var check = true;

	//Coche et d�coche les �l�ments de la liste
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


</script>
<form class='form-$current_module' id='bannette_lecteurs_assoce' name='bannette_lecteurs_assoce' method='post' action='!!form_action!!' >
<h3>$msg[dsi_ban_lec_assoce] : !!nom_bannette!!</h3>
<div class='form-contenu'>
	<div class='row'>
		<div class='colonne3'>
			<div class='row'><label class='etiquette'>$msg[dsi_ban_form_categ_lect]</label></div>
			<div class='row'>!!classement!!</div>
		</div>
		<div class='colonne_suite'>
			<div class='row'><label class='etiquette'>$msg[dsi_ban_form_groupe_lect]</label></div>
			<div class='row'>!!groupe!!</div>
		</div>
	</div>
	<div class='row'>
		<div class='colonne_suite'>
			<div class='row'><label class='etiquette'>$msg[dsi_ban_abo_empr_nom]</label></div>
			<div class='row'><input type='text' class='10em' name='lect_restrict' value=\"!!lect_restrict!!\" onchange=\"this.form.faire.value=''; this.form.submit();\" /> !!restrict_location!!
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='colonne_suite'>
			<div class='row'>
				<label for='mail_abon'>$msg[dsi_ban_abo_mail]</label>
				<input type='checkbox' name='mail_abon' value='1' !!mail_abon_checked!! onchange=\"this.form.faire.value=''; this.form.submit();\" />
			</div>
		</div>
	</div>
	<div class='row'>&nbsp;</div>
	<div class='row'>!!limitation!!</div>
	<div class='row'>
		!!lecteurs!!
		</div>
	</div>
<div class='row'>
	<div class='left'>
		<input type='button' id='bt_chk' class='bouton' value='".$msg['acquisition_sug_checkAll']."' onClick=\"checkAll('bannette_lecteurs_assoce', 'bannette_abon', check); return false;\" />
		<input type='submit' class='bouton' value='$msg[77]' />
		<input type='hidden' name='id_bannette' value='!!id_bannette!!' />
		<input type='hidden' name='quoi' value=\"!!selected!!\" />
		<input type='hidden' name='faire' value='enregistrer' />
		<input type='hidden' name='form_cb' value=\"!!form_cb_hidden!!\" />
		<input type='button' class='bouton' value=\"$msg[bt_retour]\" onClick=\"document.location='./dsi.php?categ=bannettes&sub=pro&id_bannette=&suite=search&form_cb=!!form_cb!!';\" />
		</div>
	<div class='right'>
		<input type='button' class='bouton' value=\"".$msg[dsi_ban_affect_equation]."\" onclick=\"document.location='./dsi.php?categ=bannettes&sub=pro&suite=affect_equation&id_bannette=!!id_bannette!!&form_cb=!!form_cb!!'\"/>
		</div>
	</div>
<div class='row'></div>
</form>" ;

// template pour la liste bannettes en diffusion
$dsi_ban_list_diff = "
<h1>!!titre!!</h1>
<form class='form-$current_module' id='bannette_lecteurs_assoce' name='bannette_lecteurs_assoce' method='post' action='!!form_action!!' >
<h3>$msg[dsi_dif_act_ban_contenu]
		<input type='button' class='bouton_small' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);' align='middle'>
		<input type='button' class='bouton_small' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);' align='middle'>
</h3>
<div class='form-contenu'>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<script>	
		function confirm_dsi_ban_diffuser() {
       		result = confirm(\"${msg[confirm_dsi_ban_diffuser]}\");
       		if(result) {
       			return true;
			} else
           		return false;
    	}
    	function confirm_dsi_dif_full_auto() {
       		result = confirm(\"${msg[confirm_dsi_dif_full_auto]}\");
       		if(result) {
       			return true;
			} else
           		return false;
    	}
	</script>
	<table border='0' width='100%' class='sortable'>
		!!list!!
		</table>
	</div>

<div class='row'>
	<div class='left'>
		<input type='button' class='bouton' name='bt_vider' value=\"".$msg['dsi_ban_vider']."\" onclick=\"this.form.suite.value='vider'; this.form.submit();\" />
		<input type='button' class='bouton' name='bt_remplir' value=\"".$msg['dsi_ban_remplir']."\" onclick=\"this.form.suite.value='remplir'; this.form.submit();\" />
		<input type='button' class='bouton' name='bt_voircontenu' value=\"".$msg['dsi_ban_visualiser']."\" onclick=\"this.form.suite.value='visualiser'; this.form.submit();\" />
		<input type='button' class='bouton' name='bt_diffuser' value=\"".$msg['dsi_ban_diffuser']."\" onclick=\"if(confirm_dsi_ban_diffuser()){this.form.suite.value='diffuser'; this.form.submit();}\" />
		<input type='button' class='bouton' name='bt_diffuser' value=\"".$msg['dsi_dif_full_auto']."\" onclick=\"if(confirm_dsi_dif_full_auto()){this.form.suite.value='full_auto'; this.form.submit();}\" />
		<input type='hidden' name='suite' value='' />
		<input type='hidden' name='id_classement' value='!!id_classement!!' />
		<input type='hidden' name='form_cb' value='!!cle!!' />
		</div>
	<div class='right'>
		<input type='button' class='bouton' name='gen_document' value=\"".$msg["dsi_ban_gen_document"]."\" onclick=\"this.form.suite.value='gen_document'; this.form.submit();\" />	
		<input type='button' class='bouton' name='bt_exporter' value=\"".$msg[dsi_ban_exporter_diff]."\" onclick=\"this.form.suite.value='exporter'; this.form.submit();\" />
		</div>
	</div>
<div class='row'></div>
</form>
";

// $dsi_search_flux_tmpl : template pour le form de recherche de flux RSS
$dsi_search_flux_tmpl = "
<form class='form-$current_module' id='saisie_cb_ex' name='saisie_cb_ex' method='post' action='!!form_action!!' >
<h3>!!titre_formulaire!!</h3>
<div class='form-contenu'>
<div class='row'>
	<div class='row'>
		<label class='etiquette' for='form_cb'>!!message!!</label>
		</div>
	<div class='row'>
		<input class='saisie-30em' id='form_cb' type='text' name='form_cb' value=\"!!cb_initial!!\" title='$msg[3000]' />
		</div>
	</div>
	<div class='row'></div>
</div>

<div class='row'>
	<input type='submit' class='bouton' value='$msg[502]' />
	<input type='button' class='bouton' value='$msg[ajouter]' onclick=\"this.form.suite.value='add'; this.form.submit();\" />
	<input type='hidden' name='suite' value='search' />
	</div>
</form>
<script type='text/javascript'>
document.forms['saisie_cb_ex'].elements['form_cb'].focus();
</script>";

// $dsi_flux_form : form saisie des flux RSS
$dsi_flux_form = "
<script type='text/javascript'>
<!--
	function test_form(form) {
		if(form.nom_rss_flux.value.replace(/^\s+|\s+$/g, '').length == 0) {
			alert(\"${msg[dsi_flux_nom_oblig]}\");
			return false;
		}
		return true;
	}
function confirm_delete() {
        result = confirm(\"${msg[confirm_suppr]}\");
        if(result)
            document.location='./dsi.php?categ=fluxrss&suite=delete&id_rss_flux=!!id_rss_flux!!';
        else
            document.forms['saisie_rss_flux'].elements['nom_rss_flux'].focus();
    }
-->
</script>
<form class='form-$current_module' id='saisie_rss_flux' name='saisie_rss_flux' method='post' action='!!action!!'>
<h3>!!libelle!!</h3>
<div class='form-contenu'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='nom_rss_flux'>$msg[dsi_flux_form_nom]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='nom_rss_flux' value=\"!!nom_rss_flux!!\" />
			</div>
		</div>

	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='link_rss_flux'>$msg[dsi_flux_form_link]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='link_rss_flux' value=\"!!link_rss_flux!!\" />
			</div>
		</div>

	<div class='row'>
		<div class='row'>
			<label class='etiquette' for='descr_rss_flux'>$msg[dsi_flux_form_descr]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-80em' name='descr_rss_flux' value=\"!!descr_rss_flux!!\" />
			</div>
		</div>

<div class='row'><hr /></div>
	<div class='colonne4'>
		<div class='row'>
			<label class='etiquette' for='lang_rss_flux'>$msg[dsi_flux_form_lang]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-10em' name='lang_rss_flux' value=\"!!lang_rss_flux!!\" />
			</div>
		</div>
	<div class='colonne4'>
		<div class='row'>
			<label class='etiquette' for='ttl_rss_flux'>$msg[dsi_flux_form_ttl]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-10em' name='ttl_rss_flux' value=\"!!ttl_rss_flux!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='copy_rss_flux'>$msg[dsi_flux_form_copy]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='copy_rss_flux' value=\"!!copy_rss_flux!!\" />
			</div>
		</div>
<div class='row'></div>
<div class='row'><hr /></div>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='editor_rss_flux'>$msg[dsi_flux_form_editor]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='editor_rss_flux' value=\"!!editor_rss_flux!!\" />
			</div>
		</div>
	<div class='colonne_suite'>
		<div class='row'>
			<label class='etiquette' for='webmaster_rss_flux'>$msg[dsi_flux_form_webmaster]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='webmaster_rss_flux' value=\"!!webmaster_rss_flux!!\" />
			</div>
		</div>
<div class='row'></div>
<div class='row'><hr /></div>
	<div class='row'>
		<div class='row'>
			<label class='etiquette' for='img_url_rss_flux'>$msg[dsi_flux_form_img_url]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-80em' name='img_url_rss_flux' value=\"!!img_url_rss_flux!!\" />
			</div>
		</div>
	<div class='row'>
		<div class='row'>
			<label class='etiquette' for='img_title_rss_flux'>$msg[dsi_flux_form_img_title]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-80em' name='img_title_rss_flux' value=\"!!img_title_rss_flux!!\" />
			</div>
		</div>
	<div class='row'>
		<div class='row'>
			<label class='etiquette' for='img_link_rss_flux'>$msg[dsi_flux_form_img_link]</label>
			</div>
		<div class='row'>
			<input type='text' class='saisie-80em' name='img_link_rss_flux' value=\"!!img_link_rss_flux!!\" />
			</div>
		</div>
<div class='row'><hr /></div>
<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<label class='etiquette' for='type_export'>$msg[dsi_flux_form_format_flux]</label>
		</div>
		<div class='row'>
			<input type='radio' name='type_export' onclick='disableTemplateChoice()' value='tpl' id='tpl_rss_flux' !!tpl_rss_flux!! />
			!!sel_notice_tpl!!
		</div>
	</div>
	<div class='colonne_suite'>	
		<div class='row'>
			<label class='etiquette' for='format_flux'>$msg[dsi_flux_form_format_flux_default]</label>
		</div>
		<div class='row'>
			!!format_flux_default!!
		</div>
	</div>
</div>

<div class='row'>
	<div class='colonne2'>
		<div class='row'>
			<input type='radio' name='type_export' onclick='disableTemplateChoice()' value='export_court' id='export_court_flux' !!export_court!! />
			<label class='etiquette' for='export_court_flux'>$msg[dsi_flux_form_short_export]</label>
		</div>
	</div>	
</div>

<div class='row'><hr /></div>

<div class='row'>
	<div class='colonne2'>
		<label for='paniers' class='etiquette'>$msg[dsi_flux_form_paniers]</label>
		!!paniers!!
		</div>
	<div class='colonne_suite'>
		<label for='bannettes' class='etiquette'>$msg[dsi_flux_form_bannettes]</label>
		!!bannettes!!
		</div>
	</div>
<div class='row'><hr /></div>

</div>
<div class='row'>
	<div class='left'>
		<input type='submit' value='$msg[77]' class='bouton' onClick=\"return test_form(this.form)\" />
		<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./dsi.php?categ=fluxrss&id_rss_flux=&suite=search&form_cb=!!form_cb!!';\" />
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>
<script type='text/javascript'>
	document.forms['saisie_rss_flux'].elements['nom_rss_flux'].focus();
	document.ready=disableTemplateChoice();
	function disableTemplateChoice(){
		if(document.getElementById('export_court_flux').checked){
			document.forms['saisie_rss_flux'].elements['notice_tpl'].disabled='disabled';
			document.forms['saisie_rss_flux'].elements['format_flux'].disabled='disabled';
		}else if(!document.getElementById('export_court_flux').checked){
			document.forms['saisie_rss_flux'].elements['notice_tpl'].disabled='';
			document.forms['saisie_rss_flux'].elements['format_flux'].disabled='';
		}
	}
</script>
";
