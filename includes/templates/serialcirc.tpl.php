<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc.tpl.php,v 1.20 2013-11-12 09:46:47 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$serialcirc_circ_form = "
	<script type='text/javascript' src='./javascript/serialcirc.js'></script>
	<script type='text/javascript'>
		function form_serialcirc_circ_get_info_cb(){
			serialcirc_circ_get_info_cb(document.forms['saisie_cb_ex'].elements['form_cb_expl'].value,'serialcirc_pointage_zone'); 
			document.forms['saisie_cb_ex'].elements['form_cb_expl'].value='';			
			document.forms['saisie_cb_ex'].elements['form_cb_expl'].focus();
		}
	</script>
	<h1>".htmlentities($msg["serialcirc_circ_title"],ENT_QUOTES,$charset)."</h1>
	<h3>".htmlentities($msg["serialcirc_circ_title_form"],ENT_QUOTES,$charset)."</h3>		
	<form class='form-$current_module' name='saisie_cb_ex' method='post' action='!!form_action!!' onSubmit=\"form_serialcirc_circ_get_info_cb();	return false;\" >
		<h3>".htmlentities($msg["serialcirc_circ_cb_doc"],ENT_QUOTES,$charset)."</h3>
		<div class='form-contenu'>
			<div class='row'>
				<label class='etiquette' for='form_cb_expl'>!!message!!</label>
			</div>
			<div class='row'>
				<input class='saisie-20em' type='text' id='form_cb_expl' name='form_cb_expl' value=''  />
				<input type='button' class='bouton' value='$msg[502]'  
				onClick=\"form_serialcirc_circ_get_info_cb();	return false;\" />
			</div>
			<div  class='row' id='serialcirc_pointage_zone'>			
			</div>		
		</div>
	</form>
	<script type='text/javascript'>	
		document.forms['saisie_cb_ex'].elements['form_cb_expl'].focus();
	</script>
	
";

if($pmb_lecteurs_localises) $serialcirc_circ_liste_location="
	<h3>".htmlentities($msg["serialcirc_circ_list_title"],ENT_QUOTES,$charset)."</h3>
	
	<div class='row'>
		".htmlentities($msg["serialcirc_circ_list_location_title"],ENT_QUOTES,$charset)." : !!localisation!!
	</div>
";		
$serialcirc_circ_liste = "	
<script type='text/javascript'>	

	function my_serialcirc_circ_list_bull_ajouter_sommaire(zone,bull_id){	
		var url = './catalog.php?categ=serials&sub=bulletinage&action=view&bul_id='+bull_id;
		window.open(url,'blank');				
	}	
	
	function my_serialcirc_circ_list_bull_envoyer_alert	(zone,expl_id){
		if (confirm('".addslashes($msg["serialcirc_envoyer_alert"])."')){
			serialcirc_circ_list_bull_envoyer_alert(expl_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_alert"])."';	
		}else{
		
		}						
	}	
	
	function my_serialcirc_print_list_circ(zone,expl_id){
		var start_diff_id=0;

		var  obj=document.getElementById(zone + '_group_circ_select_' + expl_id);
		for(var i=0 ; i<obj.options.length; i++){
			if(obj.options[i].selected){
				start_diff_id=obj.options[i].value;
			}
		}			
		serialcirc_print_list_circ(expl_id,start_diff_id);			
	}
	
	function my_serialcirc_comeback_expl(zone,expl_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_retour"])."')){
			var info = serialcirc_comeback_expl(expl_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id);
			if(obj) obj.innerHTML=info;	
		}else{
		
		}					
	}		
		
	function my_serialcirc_call_expl(zone,expl_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_call_expl"])."')){
			serialcirc_call_expl(expl_id);	
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_call_expl"])."';
		}else{
		
		}				
	}		
	
	function my_serialcirc_do_trans(zone,expl_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_do_trans"])."')){
			serialcirc_do_trans(expl_id);	
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_do_trans"])."';	
		}else{
		
		}							
	}		
	
	function my_serialcirc_delete_circ(zone,expl_id){
	
		if (confirm('".addslashes($msg["serialcirc_confirm_delete"])."')){
			serialcirc_delete_circ(expl_id);
			if(document.getElementById('tr_'+zone+'_'+expl_id))document.getElementById('tr_'+zone+'_'+expl_id).parentNode.removeChild(document.getElementById('tr_'+zone+'_'+expl_id));
		}else{
		
		}
	}
	
	function my_serialcirc_callinsist_expl(zone,expl_id){
		serialcirc_callinsist_expl(expl_id);				
	}
		
	function my_serialcirc_copy_accept(zone,copy_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_copy"])."')){
			serialcirc_copy_accept(copy_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + copy_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_copy"])."';	
		}	
	}
	
	function my_serialcirc_copy_none(zone,copy_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_copy_none"])."')){
			serialcirc_copy_none(copy_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + copy_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_copy_none"])."';	
		}	
	}
	
	function my_serialcirc_resa_accept(zone,expl_id,empr_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_resa"])."')){
			serialcirc_resa_accept(expl_id,empr_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id+ '_' + empr_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_resa"])."';	
		}	
	}
	
	function my_serialcirc_resa_none(zone,expl_id,empr_id){
		if (confirm('".addslashes($msg["serialcirc_confirm_resa_none"])."')){
			serialcirc_resa_none(expl_id,empr_id);
			var  obj=document.getElementById('circ_actions_'+ zone + '_' + expl_id+ '_' + empr_id);
			if(obj) obj.innerHTML='".addslashes($msg["serialcirc_info_resa_none"])."';	
		}	
	}	
	
	function my_serialcirc_print_all_sel_list_diff(zone){
		var check;	
		var expl_start_empr= new Array();
		var list = document.getElementsByName('serialcirc_sel_print[]');
 		if(!list) return;
 		var cpt=0;
		for(var i=0 ; i<list.length ; i++){
			if(list[i].checked){
				expl_id=list[i].value;
				var start_diff_id=document.getElementById('to_be_circ_group_circ_select_' + expl_id).value;
				expl_start_empr[cpt]= new Array();
				expl_start_empr[cpt]['expl_id']=expl_id;
				expl_start_empr[cpt]['start_diff_id']=start_diff_id;
				cpt++;
			}
		}
		if(cpt>0) serialcirc_print_all_sel_list_diff(expl_start_empr);
	}

</script>
<script type='text/javascript' src='./javascript/tablist.js'></script>

<form class='form-$current_module' id='form_pointage' name='form_pointage' method='post' action='./circ.php?categ=serialcirc'>	
	<div class='form-contenu'>
		$serialcirc_circ_liste_location		
		<h3>".htmlentities($msg["serialcirc_circ_list_bull_title"],ENT_QUOTES,$charset)."</h3>
		<script type='text/javascript' src='./javascript/sorttable.js'></script>
		<a href='javascript:expandAll()'><img src='./images/expand_all.gif' border='0' id='expandall'></a>
		<a href='javascript:collapseAll()'><img src='./images/collapse_all.gif' border='0' id='collapseall'></a>
		!!liste_alerter!!
		!!liste_circuler!!
		!!liste_circulation!!
		!!liste_retard!!
		!!liste_reproduction!!
		!!liste_resa!!			
	</div>
	<div class='row'>
		<input type=\"submit\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_actualiser_bt"],ENT_QUOTES,$charset)."' onClick=\"document.location='./circ.php?categ=serialcirc'; return false;\"/>&nbsp;
		<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_print_all_sel_list_diff('to_be_circ'); return false;\"/>&nbsp;	
	</div>		
	<input type='hidden' id='act' name='act' value='' />
</form>	
";	//				
		
$serialcirc_pointage_form="
	<div class='row'>
		!!liste_alerter!!
		!!liste_circuler!!
		!!liste_circulation!!
		!!liste_retard!!
	</div>		
";
$serialcirc_circ_liste_alerter = "	
	<table width='100%' class='sortable'>
		<tr>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_date"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_abonnement"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_cb"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_destinataire"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>
			
		</tr>
		!!liste_alerter!!
	</table>
";
$serialcirc_circ_liste_alerter_tr="
	<tr id='tr_!!zone!!_!!expl_id!!'>						
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>
		<td>
			!!expl_cb!!
		</td>		
		<td>
			!!destinataire!!
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_ajouter_sommaire_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_circ_list_bull_ajouter_sommaire('!!zone!!','!!bull_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_envoyer_alert_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_circ_list_bull_envoyer_alert('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
		</td>	
	</tr>
";	
$serialcirc_circ_liste_is_alerted_tr="
	<tr id='tr_!!zone!!_!!expl_id!!'>						
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>
		<td>
			!!expl_cb!!
		</td>		
		<td>
			!!destinataire!!
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_ajouter_sommaire_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_circ_list_bull_ajouter_sommaire('!!zone!!','!!bull_id!!'); return false;\"/>&nbsp;
		</td>	
	</tr>
";	
$serialcirc_circ_liste_circuler = "	
	<table width='100%' class='sortable'>
		<tr id='tr_!!zone!!_!!expl_id!!'>
			<th>
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_date"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_abonnement"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_cb"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_destinataire"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>
			
		</tr>
		!!liste_circuler!!
	</table>
";
$serialcirc_circ_liste_circuler_tr="
	<tr id='tr_!!zone!!_!!expl_id!!'>					
		<td>
			<input type='checkbox' name='serialcirc_sel_print[]'  value='!!expl_id!!' class='checkbox' />
		</td>				
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_print_list_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_annuler_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_delete_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
		</td>	
	</tr>
";	

$serialcirc_circ_liste_circulation = "	
	<table width='100%' class='sortable'>
		<tr id='tr_!!zone!!_!!expl_id!!'>
			<th>
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_date"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_abonnement"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_cb"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_destinataire"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>
		</tr>
		!!liste_circulation!!
	</table>
";
$serialcirc_circ_liste_circulation_rotative_tr="
	<tr  id='tr_!!zone!!_!!expl_id!!' >					
		<td>
			<img src='./images/plus.gif' class='img_plus' name='imEx' id='circ_detail_!!zone!!_!!expl_id!!Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('circ_detail_!!zone!!_!!expl_id!!', true);return false;\" hspace='3'>				
		</td>				
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
			<div id='circ_detail_!!zone!!_!!expl_id!!Child' style='display:none;'>			
				!!empr_list!!				
			</div>			
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_call_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_call_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_go_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_do_trans('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_annuler_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_delete_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_comeback_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_comeback_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_print_list_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
			</div>			
		</td>	
	</tr>
";	
$serialcirc_circ_liste_circulation_star_tr="
	<tr id='tr_!!zone!!_!!expl_id!!'>					
		<td>
			<input type='checkbox' name='serialcirc_sel_print[]'  value='!!expl_id!!' class='checkbox' />
			<img src='./images/plus.gif' class='img_plus' name='imEx' id='circ_detail_!!zone!!_!!expl_id!!Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('circ_detail_!!zone!!_!!expl_id!!', true);return false;\" hspace='3'>				
			
		</td>				
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
			<div id='circ_detail_!!zone!!_!!expl_id!!Child' style='display:none;'>			
				!!empr_list!!				
			</div>				
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_call_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_call_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_go_return_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_callinsist_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_annuler_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_delete_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_comeback_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_comeback_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_print_list_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
			</div>			
		</td>	
	</tr>		
";	
$serialcirc_circ_liste_retard = "	
	<table width='100%' class='sortable'>
		<tr id='tr_!!zone!!_!!expl_id!!'>
			<th>
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_date"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_abonnement"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_cb"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_destinataire"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>
			
		</tr>
		!!liste_retard!!
	</table>
";
$serialcirc_circ_liste_retard_tr="
	<tr  id='tr_!!zone!!_!!expl_id!!' class='!!tr_class!!' >	
		<td>
		</td>						
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"serialcirc_imprimer_list_diff('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_annuler_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_delete_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
		</td>	
	</tr>
";
$serialcirc_circ_liste_retard_rotative_tr="
	<tr  id='tr_!!zone!!_!!expl_id!!' >					
		<td>
			<img src='./images/plus.gif' class='img_plus' name='imEx' id='circ_detail_!!zone!!_!!expl_id!!Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('circ_detail_!!zone!!_!!expl_id!!', true);return false;\" hspace='3'>				
		</td>				
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
			<div id='circ_detail_!!zone!!_!!expl_id!!Child' style='display:none;'>			
				!!empr_list!!				
			</div>			
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_call_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_call_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_go_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_do_trans('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
			</div>			
		</td>	
	</tr>
";	
$serialcirc_circ_liste_retard_star_tr="
	<tr  id='tr_!!zone!!_!!expl_id!!'>					
		<td>
			<img src='./images/plus.gif' class='img_plus' name='imEx' id='circ_detail_!!zone!!_!!expl_id!!Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('circ_detail_!!zone!!_!!expl_id!!', true);return false;\" hspace='3'>							
		</td>				
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!abonnement!!
		</td>	
		<td>
			!!expl_cb!!
		</td>	
		<td>
			!!destinataire!!
			<div id='circ_detail_!!zone!!_!!expl_id!!Child' style='display:none;'>			
				!!empr_list!!				
			</div>				
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_call_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_call_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_go_return_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_callinsist_expl('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
			<div id='circ_actions_!!zone!!_!!expl_id!!' class='erreur'>						
			</div>			
		</td>	
	</tr>		
";	
$serialcirc_copy = "	
	<table width='100%' class='sortable'>
		<tr id='tr_!!zone!!_!!expl_id!!'>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_date"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_reproduction_empr"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_reproduction_empr_message"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_reproduction_state"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>
			
		</tr>
		!!liste_reproduction!!
	</table>
";
$serialcirc_copy_tr="
	<tr  id='tr_!!zone!!_!!id_copy!!' class='!!tr_class!!' >		
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!empr_name!!
		</td>
		<td>
			!!empr_message!!
		</td>
		<td>
			
		</td>
		<td>
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_reproduction_ok_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_copy_accept('!!zone!!','!!id_copy!!'); return false;\"/>&nbsp;
			<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_reproduction_none_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_copy_none('!!zone!!','!!id_copy!!'); return false;\"/>&nbsp;
			<div id='circ_actions_!!zone!!_!!id_copy!!' class='erreur'>						
			</div>			
		</td>	
	</tr>
";
$serialcirc_copy_ok_tr="
	<tr  id='tr_!!zone!!_!!expl_id!!' class='!!tr_class!!' >		
		<td>
			!!date!!
		</td>	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!empr_name!!
		</td>
		<td>
			!!empr_message!!
		</td>
		<td>
			".htmlentities($msg["serialcirc_circ_list_reproduction_state_ok"],ENT_QUOTES,$charset)."
		</td>
		<td>
		</td>	
	</tr>
";
$serialcirc_circ_liste_reservation = "	
	<table width='100%' class='sortable'>
		<tr>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_perodique"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_numero"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_resa_empr"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_circ_list_bull_circulation_actions"],ENT_QUOTES,$charset)."
			</th>			
		</tr>
		!!liste_resa!!
	</table>
";
$serialcirc_circ_liste_reservation_tr="
	<tr  class='!!tr_class!!' >	
		<td>
			!!periodique!!
		</td>
		<td>
			!!numero!!
		</td>
		<td>
			!!empr_name!!
		</td>	
		<td>
			<div id='circ_actions_!!zone!!_!!expl_id!!_!!empr_id!!' class='erreur'>						
				<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_resa_ok_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_resa_accept('!!zone!!','!!expl_id!!','!!empr_id!!'); return false;\"/>&nbsp;
				<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_resa_none_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_resa_none('!!zone!!','!!expl_id!!','!!empr_id!!'); return false;\"/>&nbsp;			
			</div>			
		</td>	
	</tr>
";

$serialcirc_circ_cb_notfound="
	<br />
	<div class='erreur'>$msg[540]</div>
		<div class='row'>
		<div class='colonne10'>
			<img src='./images/error.gif' align='left'>
			</div>
		<div class='colonne80'>
			<strong>".htmlentities($msg["serialcirc_circ_cb_notfound"],ENT_QUOTES,$charset)."</strong>
		</div>
	</div>
";
$serialcirc_circ_cb_info="
	<div class='row'>
		<strong>!!date!! - !!periodique!! - !!numero!! - !!abonnement!!</strong>
	</div>
	<div class='row'>
		".htmlentities($msg["serialcirc_circ_cb_first_diff"],ENT_QUOTES,$charset)."	!!destinataire!!
	</div>	
	<div class='row'>
		<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_imprimer_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_print_list_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
		<input type=\"button\" class='bouton' value='".htmlentities($msg["serialcirc_circ_list_bull_circulation_annuler_bt"],ENT_QUOTES,$charset)."' onClick=\"my_serialcirc_delete_circ('!!zone!!','!!expl_id!!'); return false;\"/>&nbsp;
	</div>
";		


$serialcirc_circ_pdf_diffusion="
	<style type='text/css'>
	table.listcirc {
		border-width: 5px;
		border-spacing: 0px;
		border-style: outset;
		border-color: gray;
		border-collapse: separate;
		background-color: rgb(255, 250, 250);
	}
	table.listcirc th {
		border-width: 1px;
		padding: 4px;
		margin:0px;
		border-style: solid;
		border-color: gray;
		background-color: white;
		-moz-border-radius: ;
	}
	table.listcirc td {
		border-width: 1px;
		padding: 4px;
		margin:0px;
		border-style: solid;
		border-color: gray;
		background-color: white;
		-moz-border-radius: ;
	}
	</style>
	<page backtop='10mm' backbottom='10mm' backleft='10mm' backright='10mm'>
	<span style='font-size: 18pt;'>	
	    <strong>!!periodique!! - !!numero!! - !!date!!</strong>
	    <br/>
	    ".htmlentities($msg["serialcirc_circ_list_bull_circulation_cb"],ENT_QUOTES,$charset)." <b>!!expl_cb!!</b><br/>
	    ".$msg["serialcirc_print_date"]."
	    <br/>    
	    <br/>
		<table class='listcirc' style='width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;' cellspacing='0'>
			<tbody> <tr>!!th!!</tr>			 
			!!table_contens!!
			</tbody>
		</table>
	</span>
	</page>
";		

$serialcirc_call_mail="
<p>Bonjour,</p>

<p>Sauf erreur de notre part, vous �tes toujours en possession du bulletin suivant�: !!issue!!.<br />
Nous vous remercions de bien vouloir le rapporter � votre centre de ressources.</p>

<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_transmission_mail="
<p>Bonjour,</p>

<p>Sauf erreur de notre part, vous �tes toujours en possession du bulletin suivant�: !!issue!!.<br />
Nous vous remercions de bien vouloir le transmettre au prochain destinataire de la liste de circulation.</p>

<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_copy_accepted_mail="
<p>Bonjour,</p>
<p>Nous vous informons que votre demande de reproduction concernant le bulletin ci-dessous a �t� accept�e.<br /> 
!!issue!! .
</p>
<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_send_alert_mail="
<p>Bonjour,</p>
<p>Nous vous informons que le bulletin ci-dessous est disponible.<br />
!!issue!! .<br />
Vous pouvez vous inscrire sur votre compte lecteur pour le recevoir. 
</p>
<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_copy_isdone_mail="
<p>Bonjour,</p>
<p>La demande de reproduction concernant le bulletin ci-dessous a �t� effectu�e.<br /> 
!!issue!!<br />
Voici le lien pour consulter cette reproduction : <br />
!!see!! 
</p>
<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_copy_no_mail="
<p>Bonjour,</p>
<p>Nous vous informons que votre demande de reproduction concernant le bulletin ci-dessous a �t� rejet�e.<br /> 
!!issue!! <br />
Merci de votre compr�hension.
</p>
<p>Cordialement,<br />
$biblio_name</p>";

$serialcirc_resa_accepted_mail ="
<p>Bonjour,</p>
<p>Nous vous informons que votre demande de reproduction concernant le bulletin ci-dessous a �t� rejet�e.<br />  
!!issue!! a �t� accept�e.
</p>
<p>Cordialement,<br />
$biblio_name</p>";


$serialcirc_resa_no_mail ="
<p>Bonjour,</p>
<p>La demande de r�servation concernant le bulletin !!issue!! a �t� refus�e.
</p>
<p>Cordialement,<br />
$biblio_name</p>";
