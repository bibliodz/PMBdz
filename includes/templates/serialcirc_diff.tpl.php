<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_diff.tpl.php,v 1.13 2013-09-24 13:31:10 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$serialcirc_diff_form="
	<script type='text/javascript' src='./javascript/ajax.js'></script>
	<script type='text/javascript' src='./javascript/serialcirc_diff.js'></script>	
	<script type='text/javascript' src='./javascript/circdiff_drop.js'></script>
	<script >
		function verif_form(){
			if( document.getElementById('group_name')){
				if(document.getElementById('group_name').value==''){
					alert('".addslashes($msg["serialcirc_diff_group_add_error_name"])."');
					return false;
				}	
				if(document.forms['form_edition'].empr_resp.value=='0'){
					alert('".addslashes($msg["serialcirc_diff_group_add_error_responsable"])."');
					return false;
				}	
			}	
		return true;
		}
	</script>
	<h1>".htmlentities($msg["serialcirc_diff_title"], ENT_QUOTES, $charset)."</h1>			
	<h3>!!perio!! / !!abt!!</h3> !!bulletinage_see!!
	<table>
		<tr>
			<td valign='top'>
				<div id='serialcirc_diff_form_empr_list'>
					!!serialcirc_diff_form_empr_list!!
				</div>
			</td> 	
			<td valign='top'>					
				<div id='serialcirc_diff_form_type'>
					!!serialcirc_diff_form_type!!
				</div>				
			</td> 				
		</tr>
		<tr>	
			<td valign='top'>				
				<input type='button' class='bouton' name='add_empr_button' value='".htmlentities($msg["serialcirc_diff_add_empr_button"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_get_empr_form(0);' />
				<input type='button' class='bouton' name='ficheformat_button' value='".htmlentities($msg["serialcirc_diff_ficheformat_button"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_get_ficheformat_form();' />				
				<input type='button' class='bouton' name='selection_empr_button' id='selection_empr_button' value='".htmlentities($msg["serialcirc_diff_selection_empr_button"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_selection_empr_button();' />			
				<input type='button' class='bouton' name='delete_empr_button' value='".htmlentities($msg["serialcirc_diff_delete_empr_button"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_delete_empr_button()' />			
			</td> 				
			<td valign='top'>
				<input type='button' class='bouton' name='record_button' id='record_button'  value='".htmlentities($msg["serialcirc_diff_record_button"], ENT_QUOTES, $charset)."' onclick='if(verif_form())serialcirc_diff_record_button();' />			
				<input type='button' class='bouton' name='cancel_button' id='cancel_button' value='".htmlentities($msg["serialcirc_diff_cancel_button"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_get_option_form();' />
				<div class='right'>
					<input type='button' class='bouton' name='del_button' id='del_button' value='".htmlentities($msg["serialcirc_delete_circ"], ENT_QUOTES, $charset)."' onclick='serialcirc_diff_delete();' />							
				</div>					
			</td>					
	
		</tr> 				
	</table> 			

";

$serialcirc_diff_form_empr_list="
	<h3>".htmlentities($msg["serialcirc_diff_empr_list_title"], ENT_QUOTES, $charset)."</h3>	
	<form id='form_empr_list' name='form_empr_list' method='post' action=''>
		<input type='hidden' id='num_abt' name='num_abt' value='!!num_abt!!' />
		<div class='row'>
			!!empr_list!!
		</div> 
	</form>	
";
 
$serialcirc_diff_form_empr_list_empr="

	<div id=\"drag_!!id_diff!!\" handler=\"handle_!!id_diff!!\" dragtype='circdiffdrop' draggable='yes' recepttype='circdiffdrop' id_circdiff='!!id_diff!!'
			recept='yes' dragicon=\"".$base_path."/images/icone_drag_notice.png\" dragtext='!!empr_name!!' downlight=\"circdiff_downlight\" highlight=\"circdiff_highlight\" 
			order='!!order!!' style='' >				
		<span id=\"handle_!!id_diff!!\" style=\"float:left; padding-right : 7px\"><img src=\"".$base_path."/images/sort.png\" style='width:12px; vertical-align:middle' /></span>
		<input type='checkbox' name='diff_list[]' value='!!id_diff!!' />
		<a href='#'  onclick='serialcirc_diff_get_empr_form(!!id_diff!!)' >!!empr_name!!</a>					
	</div>
";

$serialcirc_diff_form_empr_list_group = "

	<div id=\"drag_!!id_diff!!\" handler=\"handle_!!id_diff!!\" dragtype='circdiffdrop' draggable='yes' recepttype='circdiffdrop' id_circdiff='!!id_diff!!'
			recept='yes' dragicon=\"".$base_path."/images/icone_drag_notice.png\" dragtext='!!empr_name!!' downlight=\"circdiff_downlight\" highlight=\"circdiff_highlight\" 
			order='!!order!!' style='' >				
		<span id=\"handle_!!id_diff!!\" style=\"float:left; padding-right : 7px\"><img src=\"".$base_path."/images/sort.png\" style='width:12px; vertical-align:middle' /></span>
		<div id='group_circ!!id_diff!!' >
			
			<input type='checkbox' name='diff_list[]' value='!!id_diff!!' /> 
	    	<img src='./images/plus.gif' class='img_plus' name='imEx' id='group_circ!!id_diff!!Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('group_circ!!id_diff!!', true); recalc_recept();return false;\" hspace='3'>				
		    <a href='#' onclick='serialcirc_diff_get_group_form(!!id_diff!!)' >!!empr_name!!</a>					
		</div>
		<div id='group_circ!!id_diff!!Child' class='notice-child' style='margin-bottom:6px;display:none;'>
			!!empr_list!!
		</div>
	</div>	

";

$serialcirc_diff_form_empr_list_group_elt="
	<div id=\"drag_!!id_diff!!_!!id_empr!!\" handler=\"handle_!!id_diff!!_!!id_empr!!\" dragtype='circdiffgroupdrop' draggable='yes' recepttype='circdiffgroupdrop' id_circdiff='!!id_diff!!_!!id_empr!!'
			recept='yes' dragicon=\"".$base_path."/images/icone_drag_notice.png\" dragtext='!!empr_libelle!!' downlight=\"circdiff_downlight\" highlight=\"circdiff_highlight\" 
			order='!!order!!' style='' >				
		<span id=\"handle_!!id_diff!!_!!id_empr!!\" style=\"float:left; padding-right : 7px\"><img src=\"".$base_path."/images/sort.png\" style='width:12px; vertical-align:middle' /></span>
		!!empr_libelle!!	
	</div>	
";

$serialcirc_diff_form_empr_list_group_empty="

	<div id=\"drag_!!id_diff!!\" handler=\"handle_!!id_diff!!\" dragtype='circdiffdrop' draggable='yes' recepttype='circdiffdrop' id_circdiff='!!id_diff!!'
			recept='yes' dragicon=\"".$base_path."/images/icone_drag_notice.png\" dragtext='!!empr_name!!' downlight=\"circdiff_downlight\" highlight=\"circdiff_highlight\" 
			order='!!order!!' style='' >				
		<span id=\"handle_!!id_diff!!\" style=\"float:left; padding-right : 7px\"><img src=\"".$base_path."/images/sort.png\" style='width:12px; vertical-align:middle' /></span>
		<input type='checkbox' name='diff_list[]' value='!!id_diff!!' />
		<a href='#'  onclick='serialcirc_diff_get_group_form(!!id_diff!!)' >!!empr_name!!</a>	
	</div>	

";

$serialcirc_diff_form_empr="
	<h3>!!form_title!!</h3>	
	<form id='form_edition' name='form_edition' method='post' action=''>
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_empr_type"], ENT_QUOTES, $charset)."
			<input type='radio' name='empr_type' value='0' checked='checked' />&nbsp;".htmlentities($msg["serialcirc_diff_empr_type_empr"], ENT_QUOTES, $charset)."
			<input type='radio' name='empr_type' value='1'  onclick='serialcirc_diff_get_group_form();return false;'/>&nbsp;".htmlentities($msg["serialcirc_diff_empr_type_group"], ENT_QUOTES, $charset)."<br />
			<input type='hidden' id='form_type' name='form_type' value='empr_form' />
		</div>
		<div class='row'>						
			".htmlentities($msg["serialcirc_diff_day_number"], ENT_QUOTES, $charset)."
			<input type='text' name='duration' value='!!duration!!' size='5'/>	
		</div>	
		<div class='row'>
			<input type=\"text\" class='saisie-30emr' name=\"libelle_member\" value='!!empr_libelle!!' readonly value=\"\" />
			<input type='button' class='bouton' value='$msg[parcourir]' onclick=\"openPopUp('./select.php?what=emprunteur&caller=form_edition&param1=id_empr&param2=libelle_member&auto_submit=NO', 'select_empr', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
			<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.libelle_member.value=''; this.form.id_empr.value='0'; \" />
			<input type=\"hidden\" name=\"id_empr\"  value='!!id_empr!!'  />
			<input type=\"hidden\" name=\"id_diff\"  value='!!id_diff!!'  />
		</div>
	</form>				
";

$serialcirc_diff_form_group_empr_0="	
	<input type=\"hidden\" id=\"empr_count\" name=\"empr_count\"  value='!!empr_count!!'  />	
	<div class='row'>			
		<input type='radio' name='empr_resp'  id='empr_resp_!!empr_cpt!!' value='!!id_empr!!' !!checked!! onclick=\"document.getElementById('empr_resp_!!empr_cpt!!').value=document.getElementById('id_empr_!!empr_cpt!!').value;\"/>
		<input type=\"text\" class='saisie-30emr' name=\"libelle_member_!!empr_cpt!!\" value='!!empr_libelle!!' readonly value=\"\" />
		<input type='button' class='bouton' value='$msg[parcourir]' onclick=\"openPopUp('./select.php?what=emprunteur&caller=form_edition&param1=id_empr_!!empr_cpt!!&param2=libelle_member_!!empr_cpt!!&auto_submit=NO', 'select_empr', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
		<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.libelle_member_!!empr_cpt!!.value=''; this.form.id_empr_!!empr_cpt!!.value='0'; \" />
		<input class='bouton' type='button' onclick='serialcirc_diff_group_add_line_empr();' value='+'>
		<input type=\"hidden\" id=\"id_empr_!!empr_cpt!!\"  name=\"id_empr_!!empr_cpt!!\"  value='!!id_empr!!'  />
	</div>
";

$serialcirc_diff_form_group_empr="
	<div class='row'>		
		<input type='radio' name='empr_resp'  id='empr_resp_!!empr_cpt!!' value='!!id_empr!!' !!checked!! onclick=\"document.getElementById('empr_resp_!!empr_cpt!!').value=document.getElementById('id_empr_!!empr_cpt!!').value;\"/>
		<input type=\"text\" class='saisie-30emr' name=\"libelle_member_!!empr_cpt!!\" value='!!empr_libelle!!' readonly value=\"\" />
		<input type='button' class='bouton' value='$msg[parcourir]' onclick=\"openPopUp('./select.php?what=emprunteur&caller=form_edition&param1=id_empr_!!empr_cpt!!&param2=libelle_member_!!empr_cpt!!&auto_submit=NO', 'select_empr', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
		<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.libelle_member_!!empr_cpt!!.value=''; this.form.id_empr_!!empr_cpt!!.value='0'; \" />
		<input type=\"hidden\" id=\"id_empr_!!empr_cpt!!\"  name=\"id_empr_!!empr_cpt!!\" value='!!id_empr!!'  />
	</div>
";

$serialcirc_diff_form_group="
	<h3>!!form_title!!</h3>	
	<form id='form_edition' name='form_edition' method='post' action=''>
		<input type='hidden' id='form_type' name='form_type' value='group_form' />
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_empr_type"], ENT_QUOTES, $charset)."
			<input type='radio' name='empr_type' value='0' onclick='serialcirc_diff_get_empr_form(0);return false;' />&nbsp;".htmlentities($msg["serialcirc_diff_empr_type_empr"], ENT_QUOTES, $charset)."
			<input type='radio' name='empr_type' value='1'  checked='checked' />&nbsp;".htmlentities($msg["serialcirc_diff_empr_type_group"], ENT_QUOTES, $charset)."
			<input type='checkbox' name='type_diff' value='1' !!type_diff_checked!! />".htmlentities($msg["serialcirc_diff_group_marguerite"], ENT_QUOTES, $charset)."
		</div>
		<div class='row'>						
			".htmlentities($msg["serialcirc_diff_empr_type_group_name"], ENT_QUOTES, $charset)."
			<input type='text' id='group_name' name='group_name' value='!!group_name!!' size='40'/>		
			<input type='hidden' name='id_diff' value='!!id_diff!!'  />
		</div>	
		<div class='row'>						
			".htmlentities($msg["serialcirc_diff_day_number"], ENT_QUOTES, $charset)."
			<input type='text' name='duration' value='!!duration!!' size='5'/>	
		</div>	
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_group_add_list"], ENT_QUOTES, $charset)."
			<input type='radio' name='add_type' value='1'  checked onchange='document.getElementById(\"caddie_add\").style.display=\"block\";document.getElementById(\"group_circ_add\").style.display=\"none\";return false;' />&nbsp;".htmlentities($msg["serialcirc_diff_group_add_caddie"], ENT_QUOTES, $charset)."
			<input type='radio' name='add_type' value='2' onchange='document.getElementById(\"group_circ_add\").style.display=\"block\";document.getElementById(\"caddie_add\").style.display=\"none\";return false;'/>&nbsp;".htmlentities($msg["serialcirc_diff_group_add_empr_group"], ENT_QUOTES, $charset)."					
		</div>	
		<div class='row' id='caddie_add' style='display:block;'>
			<select name='caddie_select' id='caddie_select' onchange='serialcirc_diff_add_caddie(this.options[this.selectedIndex].value);return false;'>
				<option value='0' selected ='selected'> ".htmlentities($msg["serialcirc_diff_no_selection_caddie"], ENT_QUOTES, $charset)."</option>
				!!caddie_select!!
			</select>
		</div>					
		<div class='row' id='group_circ_add' style='display:none;' >
			<select name='group_circ_select' id='group_circ_select' >
				<option value='0' selected ='selected'>".htmlentities($msg["serialcirc_diff_no_selection_group"], ENT_QUOTES, $charset)."</option>
				!!group_circ_select!!
			</select>
		</div>	
		<div class='row'>	
			".htmlentities($msg["serialcirc_diff_empr_reponsability"], ENT_QUOTES, $charset)."
		</div>
		<div id='group_empr_list'>	
			!!group_empr_list!!
		</div>				
		<div id='addempr'>		
		</div>	
	</form>				
";

$serialcirc_diff_form_ficheformat="
	<h3>".htmlentities($msg["serialcirc_diff_option_form_fiche_format_title"], ENT_QUOTES, $charset)."</h3>			
	<form id='form_edition' name='form_edition' method='post' action=''>
		<input type='hidden' id='form_type' name='form_type' value='ficheformat_form' />				
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_option_form_fiche_format_add_field"], ENT_QUOTES, $charset)."
			!!fiche_add_field_sel!!				
		</div>				
	</form>			
";

$serialcirc_diff_form_option="
	<h3>".htmlentities($msg["serialcirc_diff_option_form_param_title"], ENT_QUOTES, $charset)."</h3>	
	<form id='form_edition' name='form_edition' method='post' action=''>
		<input type='hidden' id='form_type' name='form_type' value='option_form' />

		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_option_form_param_circ_type_title"], ENT_QUOTES, $charset)."
			<input type='radio' name='circ_type' value='0' !!circ_type_checked_0!! />&nbsp;".htmlentities($msg["serialcirc_diff_option_form_param_circ_type_rotative"], ENT_QUOTES, $charset)."
			<input type='radio' name='circ_type' value='1' !!circ_type_checked_1!! />&nbsp;".htmlentities($msg["serialcirc_diff_option_form_param_circ_type_etoile"], ENT_QUOTES, $charset)."
			<input type='checkbox' id='virtual_circ' name='virtual_circ' value='1' !!virtual_checked!! onclick='serialcirc_diff_option_form_param_change();'/>".htmlentities($msg["serialcirc_diff_option_form_param_circ_type_virtuelle"], ENT_QUOTES, $charset)."
			<div id='div_no_ret_circ'>
				<input type='checkbox' id='no_ret_circ' name='no_ret_circ' value='1' !!no_ret_circ_checked!! />".htmlentities($msg["serialcirc_no_ret_circ"], ENT_QUOTES, $charset)."
			</div>
		</div>
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_option_form_param_periodicity"], ENT_QUOTES, $charset)."	
			<input type='text' id='duration' name='duration' value='!!duration!!' size='5'/>			
		</div>			
		<div class='row'>				
			
			<input type='radio' name='retard_mode' value='0' !!retard_mode_checked_0!! />&nbsp;".htmlentities($msg["serialcirc_diff_option_form_param_retard_mode_day"], ENT_QUOTES, $charset)."<br />
			<input type='radio' name='retard_mode' value='1' !!retard_mode_checked_1!! />&nbsp;".htmlentities($msg["serialcirc_diff_option_form_param_retard_mode_date"], ENT_QUOTES, $charset)."
		</div>		
		<div class='row'>				
			<input type='checkbox' name='checked' value='1' !!checked_checked!! />".htmlentities($msg["serialcirc_diff_option_form_param_checked_title"], ENT_QUOTES, $charset)."
		</div>
		<div class='row'>				
			<input type='checkbox' name='allow_resa' value='1' !!allow_resa_checked!! />".htmlentities($msg["serialcirc_diff_option_form_param_allow_resa_title"], ENT_QUOTES, $charset)."
		</div>
		<div class='row'>				
			<input type='checkbox' name='allow_copy' value='1' !!allow_copy_checked!! />".htmlentities($msg["serialcirc_diff_option_form_param_allow_copy_title"], ENT_QUOTES, $charset)."
		</div>
	
		<div id='virtual_circ_part' style='display:!!display_virtual_circ_part!!'>		
			<div class='row'>				
				<input type='checkbox' name='allow_send_ask' value='1' !!allow_send_ask_checked!! />".htmlentities($msg["serialcirc_diff_option_form_param_allow_send_ask_title"], ENT_QUOTES, $charset)."
			</div>			
			<div class='row'>				
				<input type='checkbox' name='allow_subscription' value='1' !!allow_subscription_checked!! />".htmlentities($msg["serialcirc_diff_option_form_param_allow_subscription_title"], ENT_QUOTES, $charset)."
			</div>
			<div id='allow_send_ask_part'>
				<div class='row'>						
					".htmlentities($msg["serialcirc_diff_option_form_param_duration_before_send_title"], ENT_QUOTES, $charset)."	<input type='text' name='duration_before_send' value='!!duration_before_send!!' size='5'/>
				</div>		
			</div>		
		</div>	
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_option_form_param_expl_statut_circ_title"], ENT_QUOTES, $charset)."
			!!expl_statut_circ!!
		</div>
		<div class='row'>				
			".htmlentities($msg["serialcirc_diff_option_form_param_expl_statut_circ_after_title"], ENT_QUOTES, $charset)."
			!!expl_statut_circ_after!!
		</div>			
	</form>				
";
