<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: harvest_notice.tpl.php,v 1.2 2013-03-22 15:34:05 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$harvest_notice_tpl="	
<h3>".htmlentities($msg["harvest_notice_sel_notice"], ENT_QUOTES, $charset)."</h3>			
<form class='form-".$current_module."' id='harvest' name='harvest'  method='post' action=\"./catalog.php?categ=harvest&notice_id=!!notice_id!!&action=build\" >
	<div class='form-contenu'>	
		<div class='row'>
			<label class='etiquette' for='name'>".$msg['admin_harvest_form_sel']."</label>
		</div>
		<div class='row'>
			!!sel_harvest!!
		</div>
		<div class='row'>
			<label class='etiquette' for='name'>".$msg['admin_harvest_profil_form_sel']."</label>
		</div>
		<div class='row'> 
			!!sel_profil!!		
		</div>	
		<div class='row'> 
		</div>
	</div>
	
	<div class='row'>	
		<div class='left'>
			<input type=\"button\" class=\"bouton\" value=\"".htmlentities($msg["76"], ENT_QUOTES, $charset)."\" onclick=\"history.go(-1);\">&nbsp;
			<input type='submit' class='bouton' name='add_empr_button' value='".htmlentities($msg["harvest_notice_build"], ENT_QUOTES, $charset)."' />	
		</div>		
	</div>
</form>	
";

$harvest_notice_tpl_error="	
<h3>".htmlentities($msg["harvest_notice_sel_notice"], ENT_QUOTES, $charset)."</h3>			
<div class='form-contenu'>	
	<div class='row'>
		<h2>".htmlentities($msg["harvest_notice_error"], ENT_QUOTES, $charset)."<h2>
	</div>
	<div class='row'>
		<input type=\"button\" class=\"bouton\" value=\"".htmlentities($msg["76"], ENT_QUOTES, $charset)."\" onclick=\"history.go(-1);\">
	</div>
</div>
";