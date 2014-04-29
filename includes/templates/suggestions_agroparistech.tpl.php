<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: suggestions_agroparistech.tpl.php,v 1.1 2012-04-25 10:29:39 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

//	------------------------------------------------------------------------------
// $sug_list_form : template de liste pour les suggestions
//	------------------------------------------------------------------------------

$sug_list_form ="
<form class='form-$current_module' id='sug_list_form' name='sug_list_form' method='post' action=\"\" >
	<div class='form-contenu'>
		<table width='100%'><tr>
			<th>".htmlentities($msg['acquisition_sug_dat_cre'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['acquisition_sug_tit'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['acquisition_sug_edi'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['acquisition_sug_aut'], ENT_QUOTES, $charset)."</th>
			<th>".htmlentities($msg['acquisition_sug_etat'], ENT_QUOTES, $charset)."</th>	
			<th>".htmlentities($msg['acquisition_sug_iscat'], ENT_QUOTES, $charset)."</th>";

if ($acquisition_sugg_categ == '1') {
	$sug_list_form.="<th>".htmlentities($msg['acquisition_categ'], ENT_QUOTES, $charset)."</th>";
}		
$sug_list_form.="
	<th>".htmlentities($msg['acquisition_sugg_src'], ENT_QUOTES, $charset)."</th>
	<th>".htmlentities($msg['acquisition_sug_poi'], ENT_QUOTES, $charset)."</th>
	<th>".htmlentities($msg['acquisition_sug_orig'], ENT_QUOTES, $charset)."</th>
	<th>".htmlentities($msg['acquisition_sugg_date_publication'], ENT_QUOTES, $charset)."</th>	
	<th>".htmlentities($msg['acquisition_sugg_piece_jointe'], ENT_QUOTES, $charset)."</th>"
;	
$sug_list_form.="				
			<th>&nbsp;</th></tr>
			<!-- sug_list -->
		</table>
	</div>
	<div class='row'>
		<div class='left'><!-- bt_imp -->&nbsp;<!-- bt_exporter -->&nbsp;<!-- bt_todo --><span class='child' ><!-- to_categ --></span></div>
		<div class='right'><!-- bt_chk --></div>
	</div>
	<div class='row'>&nbsp;</div>
	<div class='row'>
		<div class='left'><!-- bt_list --></div>
		<div class='right'><!-- bt_sup --></div>
	</div>
</form>
<!-- script -->
<br />
<div class='form' >
	<!-- nav_bar -->
</div>
";

?>
