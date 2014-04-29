<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: explnum.tpl.php,v 1.27 2014-01-10 15:46:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

// on teste si des r�pertoires de stockages sont param�tr�s
if (mysql_num_rows(mysql_query("select * from upload_repertoire "))==0) $pmb_docnum_in_directory_allow = 0;
else $pmb_docnum_in_directory_allow=1;
// les deux param�tres pour savoir si on peut stocker de la GED sont donc : 
// $pmb_docnum_in_directory_allow
// $pmb_docnum_in_database_allow

// $expl_form :form de saisie/modif exemplaire num�rique
$explnum_form ="
<script type='text/javascript'>
<!--
	function test_form(form) {
		if((form.f_nom.value.length == 0) && (form.f_fichier.value.length == 0) && (form.f_url.value.length == 0)) {
			alert(\"".$msg['explnum_error_creation']."\");
			return false;
		}
		return true;
	}
	
-->

//Test si le fichier est d�j� upload� au meme endroit
function ecraser_fichier(filename){

	var res = confirm(\"".$msg['docnum_ecrase_file']." \"+filename+\".\\n".$msg['agree_question']."\");
	if(res) {
		document.getElementById('f_new_name').value = filename;
		return true;
	}
	document.getElementById('f_new_name').value = '';
	return false;	
	
}
</script>

<script src=\"./javascript/http_request.js\" type='text/javascript'></script>
<script src=\"./javascript/select.js\" type='text/javascript'></script>
<script src=\"./javascript/upload.js\" type='text/javascript'></script>

<form class='form-$current_module' ENCTYPE='multipart/form-data' name='explnum' method='post' action='!!action!!' onsubmit='!!submit_action!!'>
<h3>$msg[explnum_data_doc]</h3>
<div class='form-contenu' >";

if ($pmb_docnum_in_directory_allow || $pmb_docnum_in_database_allow) {
	$explnum_form .="<div class='row'>
			<label class='etiquette' for='f_nom'>$msg[explnum_nom]</label>
		</div>
		<div class='row'>
			<input type='text' id='f_nom' name='f_nom' class='saisie-80em'  value='!!nom!!' />
			<!-- explnum_statut -->
		</div>
		<div class='row'>
			<label class='etiquette' for='f_vignette'>$msg[explnum_vignette]</label>
		</div>
		<div class='row'>
			<input type='file' id='f_vignette' name='f_vignette' class='saisie-80em' size='65' />
			<div>!!mimetype_list!!</div>
		</div>
		<div class='row'>
			!!vignette_existante!!
		</div>
		<div class='row'>
			!!location_explnum!!
		</div>
		<div class='row'></div>
		<div class='row'>
			<hr />
		</div>
		<div class='row'>
			<label class='etiquette' for='f_fichier'>$msg[explnum_fichier]</label>
		</div>
		<div class='row'>
			<input type='file' id='f_fichier' name='f_fichier' class='saisie-80em' size='65' />
			<div><input type='checkbox' id='multi_ck' name='multi_ck' value='1'/><label for='multi_ck'>$msg[upload_repertoire_multifile]</label></div>
		</div>
		<div class='row'>
			<!-- !!scan_button!! -->
		</div>
		!!div_upload!!
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<label class='etiquette' >$msg[explnum_ou] :</label>
		</div>
	
		<div class='row'>
			<label class='etiquette' for='f_url'>$msg[explnum_url]</label>
		</div>
		<div class='row'>
			<input type='text' id='f_url' name='f_url' class='saisie-80em'  value='!!url!!' />
		</div>
		!!ck_indexation!!
		!!ck_diarization!!
		!!fct_conf_diarize_again!!
	</div>
	<div class='row'>
		<input type='button' class='bouton' value='$msg[76]' onClick=\"history.go(-1);\" />
		<input type='submit' class='bouton' value='$msg[77]' onClick=\"return test_form(this.form);\" />
		!!associate_speakers!!
		<div class='right' >
			!!supprimer!!
		</div>
		<input type='hidden' name='f_explnum_id' value='!!explnum_id!!' />
		<input type='hidden' name='f_bulletin' value='!!bulletin!!' />
		<input type='hidden' name='f_notice' value='!!notice!!' />
		<input type='hidden' name='f_new_name' id='f_new_name' value='' />
	</div>
	</form>
	<script type=\"text/javascript\">document.forms['explnum'].elements['f_nom'].focus();</script>";

} else {

	$explnum_form .="<div class='row'>
			<label class='etiquette' for='f_nom'>$msg[explnum_nom]</label>
		</div>
		<div class='row'>
			<input type='text' id='f_nom' name='f_nom' class='saisie-80em'  value='!!nom!!' />
			<!-- explnum_statut -->
		</div>
		<div class='row'>
			<label class='etiquette' for='f_vignette'>$msg[explnum_vignette]</label>
		</div>
		<div class='row'>
			<input type='file' id='f_vignette' name='f_vignette' class='saisie-80em' size='65' />
			<div>!!mimetype_list!!</div>
		</div>
		<div class='row'>
			!!vignette_existante!!
		</div>
		<div class='row'>
			!!location_explnum!!
		</div>
		<div class='row'></div>
		<div class='row'>
			<hr />
		</div>
		<div class='row'><i>$msg[explnum_no_storage_allowed]</i></div>
		<div class='row'>&nbsp;</div>
		
		<div class='row'>
			<label class='etiquette' for='f_url'>$msg[explnum_url]</label>
		</div>
		<div class='row'>
			<input type='text' id='f_url' name='f_url' class='saisie-80em'  value='!!url!!' />
		</div>
		!!ck_indexation!!
		!!ck_diarization!!
	</div>
	<div class='row'>
		<input type='button' class='bouton' value='$msg[76]' onClick=\"history.go(-1);\" />
		<input type='submit' class='bouton' value='$msg[77]' onClick=\"return test_form(this.form);\" />
		!!associate_speakers!!
		<div class='right' >
			!!supprimer!!
		</div>
		<input type='hidden' name='f_explnum_id' value='!!explnum_id!!' />
		<input type='hidden' name='f_bulletin' value='!!bulletin!!' />
		<input type='hidden' name='f_notice' value='!!notice!!' />
		<input type='hidden' name='f_new_name' id='f_new_name' value='' />
	</div>
	</form>
	<script type=\"text/javascript\">document.forms['explnum'].elements['f_nom'].focus();</script>";
}