<?php 
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titres_uniformes.tpl.php,v 1.8 2014-03-05 08:46:32 mhoestlandt Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$selector_prop = "toolbar=no, dependent=yes,resizable=yes, scrollbars=yes";

 
$titre_uniforme_form = jscript_unload_question()."
<script type='text/javascript'>

function test_form(form) {
	if(form.name.value.length == 0)	{
		alert(\"$msg[213]\");
		return false;
	}
	unload_off();	
	return true;
}

function confirm_delete() {
    result = confirm(\"${msg[confirm_suppr]}\");
    if(result) {
        unload_off();
        document.location='./autorites.php?categ=titres_uniformes&sub=delete&id=!!id!!&user_input=!!user_input_url!!&page=!!page!!&nbr_lignes=!!nbr_lignes!!';
	} else
        document.forms['saisie_titre_uniforme'].elements['titre_uniforme'].focus();
}
function check_link(id) {
	w=window.open(document.getElementById(id).value);
	w.focus();
}


</script>

<script src='javascript/ajax.js'></script>
<form class='form-$current_module' id='saisie_titre_uniforme' name='saisie_titre_uniforme' method='post' action='!!action!!' onSubmit=\"return false\" >
<h3>!!libelle!!</h3>
<div class='form-contenu'>

<!--	nom	-->
<div class='row'>
	<label class='etiquette' for='form_nom'>".$msg["aut_titre_uniforme_form_nom"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='form_nom' name='name' value=\"!!nom!!\" />
</div>
			
<!--	Auteur principal de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_author'>".$msg["auteur_principal_sort"]."</label>
</div>
<div class='row'>
	<input type='text' completion='author' autfield='form_author_id' id='form_author' class='saisie-30emr' name='form_author' value=\"!!aut_name!!\" />
    <input type='button' class='bouton' value='$msg[parcourir]' onclick=\"openPopUp('./select.php?what=auteur&caller=saisie_titre_uniforme&param1=form_author_id&param2=form_author&deb_rech='+".pmb_escape()."(this.form.form_author.value), 'select_author0', 500, 400, -2, -2, '$selector_prop')\" />
    <input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.form_author.value=''; this.form.form_author_id.value='0'; \" />
    <input type='hidden' name='form_author_id' id='form_author_id' value=\"!!aut_id!!\" />
</div>
			
<!--	Forme de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_form'>".$msg["aut_oeuvre_form_forme"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-30em' id='form_form' name='form' value='!!form!!'>
</div>	

			<!--	Date de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_dates'>".$msg["aut_oeuvre_form_date"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-30em' id='form_dates' name='date' value='!!date!!'>
</div>
			
<!--	Lieu d'origine de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_place'>".$msg["aut_oeuvre_form_lieu"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-30em' id='form_place' name='place' value='!!place!!'>
</div>
			
<!--	Sujet de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_subject'>".$msg["aut_oeuvre_form_sujet"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='form_subject' name='subject' value='!!subject!!'>
</div>

<!--	Complétude visée de l'oeuvre	-->
<div class='colonne2'>
	<label class='etiquette' for='form_completude'>".$msg["aut_oeuvre_form_completude"]."</label>
</div>
<div class='row'>
	<select id='form_intended_termination' name='intended_termination' class='saisie-20em'>
		<option value='0' !!intended_termination_0!!>--</option>\n
		<option value='1' !!intended_termination_1!!>Oeuvre finie</option>\n
		<option value='2' !!intended_termination_2!!>Oeuvre infinie</option>\n
	</select>	
</div>
			
<!--	Public visé de l'oeuvre	-->
<div class='colonne_suite'>
	<label class='etiquette' for='form_intended_audience'>".$msg["aut_oeuvre_form_public"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-30em' id='form_intended_audience' name='intended_audience' value='!!intended_audience!!'>
</div>

<!--	Histoire de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_history'>".$msg["aut_oeuvre_form_histoire"]."</label>
</div>
<div class='row'>
	<textarea class='saisie-80em' id='form_history' name='history' cols='62' rows='4' wrap='virtual'>!!history!!</textarea>
</div>
		
<!--	Contexte de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_context'>".$msg["aut_oeuvre_form_contexte"]."</label>
</div>
<div class='row'>
	<textarea class='saisie-80em' id='form_context' name='context' cols='62' rows='4' wrap='virtual'>!!context!!</textarea>
</div>			

<!--	Distribution instrumentale et vocale (pour la musique)	-->
<!--	Référence numérique (pour la musique)	-->

<!--	Tonalité (pour la musique)	-->
<div class='row'>
	<label class='etiquette' for='form_tonalite'>".$msg["aut_titre_uniforme_form_tonalite"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='form_tonalite' name='tonalite' value='!!tonalite!!'>
</div>

	
<!--	Coordonnées (oeuvre cartographique)	-->
<div class='row'>
	<label class='etiquette' for='form_coordinates'>".$msg["aut_oeuvre_form_coordonnees"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='form_coordinates' name='coordinates' value='!!coordinates!!'>
</div>
			
<!--	Equinoxe (oeuvre cartographique)	-->
<div class='row'>
	<label class='etiquette' for='form_equinox'>".$msg["aut_oeuvre_form_equinoxe"]."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='form_equinox' name='equinox' value='!!equinox!!'>
</div>

<!-- Subdivision de forme -->
					
<!--	Autres caractéristiques distinctives de l'oeuvre	-->
<div class='row'>
	<label class='etiquette' for='form_carac'>".$msg["aut_oeuvre_form_caracteristique"]."</label>
</div>
<div class='row'>
	<textarea class='saisie-80em' id='form_carac' name='characteristic' cols='62' rows='4' wrap='virtual'>!!characteristic!!</textarea>
</div>
			
<!-- 	Commentaire -->
<div class='row'>
	<label class='etiquette' for='comment'>".$msg["aut_titre_uniforme_commentaire"]."</label>
</div>
<div class='row'>
	<textarea class='saisie-80em' id='comment' name='comment' cols='62' rows='4' wrap='virtual'>!!comment!!</textarea>
</div>
!!aut_pperso!!
<div class='row'>
	<label class='etiquette' for='tu_import_denied'>".$msg['authority_import_denied']."</label> &nbsp;
	<input type='checkbox' id='tu_import_denied' name='tu_import_denied' value='1' !!tu_import_denied!!/>
</div>
<!-- aut_link -->
</div>
<!--	boutons	-->
<div class='row'>
	<div class='left'>
		<input type='button' class='bouton' value='$msg[76]' onClick=\"unload_off();document.location='./autorites.php?categ=titres_uniformes&sub=reach&user_input=!!user_input_url!!&page=!!page!!&nbr_lignes=!!nbr_lignes!!';\" />
		<input type='button' value='$msg[77]' class='bouton' id='btsubmit' onClick=\"if (test_form(this.form)) this.form.submit();\" />
		!!remplace!!
		!!voir_notices!!
		!!audit_bt!!
		<input type='hidden' name='page' value='!!page!!' />
		<input type='hidden' name='nbr_lignes' value='!!nbr_lignes!!' />
		<input type='hidden' name='user_input' value=\"!!user_input!!\" />
		</div>
	<div class='right'>
		!!delete!!
		</div>
	</div>
<div class='row'></div>
</form>
<script type='text/javascript'>
	ajax_parse_dom();
	document.forms['saisie_titre_uniforme'].elements['name'].focus();
</script>
";

// $titre_uniforme_replace : form remplacement titre_uniforme
$titre_uniforme_replace = "
<script src='javascript/ajax.js'></script>
<form class='form-$current_module' name='titre_uniforme_replace' method='post' action='./autorites.php?categ=titres_uniformes&sub=replace&id=!!id!!' onSubmit=\"return false\" >
<h3>$msg[159] !!old_titre_uniforme_libelle!! </h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette' for='titre_uniforme_libelle'>$msg[160]</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-50emr' id='titre_uniforme_libelle' name='titre_uniforme_libelle' value=\"\" completion=\"titres_uniformess\" autfield=\"by\" autexclude=\"!!id!!\"
    	onkeypress=\"if (window.event) { e=window.event; } else e=event; if (e.keyCode==9) { openPopUp('./select.php?what=titre_uniforme&caller=titre_uniforme_replace&param1=by&param2=titre_uniforme_libelle&no_display=!!id!!', 'select_ed', $selector_x_size, $selector_x_size, -2, -2, '$selector_prop'); }\" />

		<input class='bouton' type='button' onclick=\"openPopUp('./select.php?what=titre_uniforme&caller=titre_uniforme_replace&param1=by&param2=titre_uniforme_libelle&no_display=!!id!!', 'select_ed', $selector_x_size, $selector_x_size, -2, -2, '$selector_prop')\" title='$msg[157]' value='$msg[parcourir]' />
		<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.titre_uniforme_libelle.value=''; this.form.by.value='0'; \" />
		<input type='hidden' name='by' id='by' value=''>
	</div>
	<div class='row'>		
		<input id='aut_link_save' name='aut_link_save' type='checkbox'  value='1'>".$msg["aut_replace_link_save"]."
	</div>	
	</div>
<div class='row'>
	<input type='button' class='bouton' value='$msg[76]' onClick=\"document.location='./autorites.php?categ=titres_uniformes&sub=titre_uniforme_form&id=!!id!!';\">
	<input type='button' class='bouton' value='$msg[159]' id='btsubmit' onClick=\"this.form.submit();\" >
	</div>
</form>
<script type='text/javascript'>
	ajax_parse_dom();
	document.forms['titre_uniforme_replace'].elements['titre_uniforme_libelle'].focus();
</script>
";
