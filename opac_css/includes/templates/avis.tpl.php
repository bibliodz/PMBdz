<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: avis.tpl.php,v 1.7 2011-08-26 15:05:53 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

$avis_tpl_header = "<div id='titre-popup'>".$msg[notice_title_avis]."</div>";

$avis_tpl_form = "	

	<script type='text/javascript' src='./includes/javascript/bbcode.js'></script>	
		
	<center>".$msg[avis_explications]."</center><br />
	<form id='f' name='f' method='post' action='avis.php?todo=save'>
				<div class='row'><label>".$msg[avis_appreciation]."</label>
					<span class='echelle_avis'>
					$msg[avis_note_1]
					<input type='radio' name='note' id='note_1' value='1' />
					<input type='radio' name='note' id='note_2' value='2' />
					<input type='radio' name='note' id='note_3' value='3' checked />
					<input type='radio' name='note' id='note_4' value='4' />
					<input type='radio' name='note' id='note_5' value='5' />
					$msg[avis_note_5]
					</span>
					</div>
		       <input type='hidden' name='noticeid' value='".$noticeid."'>
		       <input type='hidden' name='login' value='".$login."'>

				<div class='row'><label>".$msg[avis_sujet]."</label><br />
					<input type='text' name='sujet' size='50'/>
					</div>
				<div style='padding-top: 4px;'>
					<input value=' B ' name='B' onclick=\"insert_text('commentaire','[b]','[/b]')\" type='button' class='bouton'> 
					<input value=' I ' name='I' onclick=\"insert_text('commentaire','[i]','[/i]')\" type='button' class='bouton'>
					<input value=' U ' name='U' onclick=\"insert_text('commentaire','[u]','[/u]')\" type='button' class='bouton'>
					<input value='http://' name='Url' onclick=\"insert_text('commentaire','[url]','[/url]')\" type='button' class='bouton'>
					<input value='Img' name='Img' onclick=\"insert_text('commentaire','[img]','[/img]')\" type='button' class='bouton'>
					<input value='Code' name='Code' onclick=\"insert_text('commentaire','[code]','[/code]')\" type='button' class='bouton'>
					<input value='Quote' name='Quote' onclick=\"insert_text('commentaire','[quote]','[/quote]')\" type='button' class='bouton'>
				</div>		
				<div class='row'><label>".$msg[avis_avis]."</label><br />
					<textarea id='commentaire' name='commentaire' cols='50' rows='4'></textarea>
					</div>

		      <div class='row'>
		        <input type='submit' class='bouton' name='Submit' value='".$msg[avis_bt_envoyer]."'>
		        <input type='button' class='bouton' value='".$msg[avis_bt_retour]."' onclick='javascript:document.location.href=\"avis.php?todo=liste&noticeid=".$noticeid."\"; return false;'>
		      </div>
		</form>";

$avis_tpl_post_add=	"
	<div align='center'><br /><br />".$msg[avis_msg_validation]."
	<br /><br /><a href='#' onclick='window.close()'>".$msg[avis_fermer]."</a>";

$avis_tpl_post_add_pb="<div align='center'><br /><br />".$msg[avis_msg_pb];

$avis_tpl_form1_script="
	<script type='text/javascript' src='./includes/javascript/bbcode.js'></script>		
	<script type='text/javascript'>
	<!--	
		function show_add_avis(notice_id) {
			var div_add_avis=document.getElementById('add_avis_'+notice_id);
			if(div_add_avis.style.display  == 'block'){
				div_add_avis.style.display  = 'none';
			}else{
				div_add_avis.style.display  = 'block';
			}				
		}
		
		function send_avis(notice_id) {		
			var note=3;
		 	var boutons_note = document.getElementsByName('avis_note_'+notice_id);
		 	if(boutons_note.length == 1) {
		 		boutons_note = document.getElementById('avis_note_'+notice_id);
		 		if(boutons_note){
				 	var selIndex = boutons_note.selectedIndex;				
					note = boutons_note.options[selIndex].value;	
				}		 		
		 	} else {
				for (var i=0; i < boutons_note.length; i++) {
	                if (boutons_note[i].checked) {
	                    note=i + 1;
	                }
	            }
	        }    					
			var sujet=document.getElementById('edit_sujet_'+notice_id).value;	
			var commentaire=document.getElementById('edit_commentaire_'+notice_id).value;	
			if(	sujet  || commentaire){		
				var url= './ajax.php?module=ajax&categ=avis&sub=add&id_empr=$id_empr';
				url+='&note='+note;
				url+='&notice_id='+notice_id;
				
				// On initialise la classe:
				var req = new http_request();
				// Exécution de la requette
				req.request(url, true, 'sujet='+encodeURIComponent(sujet)+'&commentaire='+encodeURIComponent(commentaire));
				
				document.getElementById('add_avis_'+notice_id).innerHTML = '<label>".$msg["avis_validation_en_cours"]."</label>';
			}	
			return 1;
		}			
	-->
	</script>
";


if($opac_avis_note_display_mode==2)
	$avis_detail_note_msg="		
		<div class='row'><label>".$msg[avis_appreciation]."</label>
			<select id='avis_note_!!notice_id!!' name='avis_note_!!notice_id!!'>
				<option value='0'>".$msg["avis_detail_note_0"]."</option>
				<option value='1'>".$msg["avis_detail_note_1"]."</option>
				<option value='2'>".$msg["avis_detail_note_2"]."</option>
				<option value='3' selected='selected'>".$msg["avis_detail_note_3"]."</option>
				<option value='4'>".$msg["avis_detail_note_4"]."</option>
				<option value='5'>".$msg["avis_detail_note_5"]."</option>
			</select>
		</div>
	";
else if($opac_avis_note_display_mode!=0)
	$avis_detail_note_msg="	
		<div class='row'><label>".$msg[avis_appreciation]."</label>
			<span class='echelle_avis'>
				$msg[avis_note_1]
				<input type='radio' name='avis_note_!!notice_id!!' id='note_1_!!notice_id!!' value='1' />
				<input type='radio' name='avis_note_!!notice_id!!' id='note_2_!!notice_id!!' value='2' />
				<input type='radio' name='avis_note_!!notice_id!!' id='note_3_!!notice_id!!' value='3' checked />
				<input type='radio' name='avis_note_!!notice_id!!' id='note_4_!!notice_id!!' value='4' />
				<input type='radio' name='avis_note_!!notice_id!!' id='note_5_!!notice_id!!' value='5' />				
				$msg[avis_note_5]
			</span>
		</div>
	";
else
	$avis_detail_note_msg="	
		<input type='hidden' name='avis_note_!!notice_id!!' value='3'>
	";
	
$avis_tpl_form1 = "
	$avis_tpl_form1_script
	<div id='add_avis_!!notice_id!!' style='display: none;'>
		$avis_detail_note_msg
		<div class='row'><label>".$msg[avis_sujet]."</label><br />
			<input type='text' name='sujet' id='edit_sujet_!!notice_id!!' size='50'/>
		</div>
		<div class='row'><label>".$msg[avis_avis]."</label><br />
			<input value=' B ' name='B' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[b]','[/b]')\" type='button' class='bouton'> 
			<input value=' I ' name='I' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[i]','[/i]')\" type='button' class='bouton'>
			<input value=' U ' name='U' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[u]','[/u]')\" type='button' class='bouton'>
			<input value='http://' name='Url' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[url]','[/url]')\" type='button' class='bouton'>
			<input value='Img' name='Img' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[img]','[/img]')\" type='button' class='bouton'>
			<input value='Code' name='Code' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[code]','[/code]')\" type='button' class='bouton'>
			<input value='Quote' name='Quote' onclick=\"insert_text('edit_commentaire_!!notice_id!!','[quote]','[/quote]')\" type='button' class='bouton'>
		</div>		
		<div class='row'>
			<textarea name='commentaire' id='edit_commentaire_!!notice_id!!' cols='60' rows='4'></textarea>
		</div>
      	<div class='row'>
	        <input type='button' class='bouton' onclick=\" send_avis(!!notice_id!!);  return false; \" value='".$msg[avis_bt_envoyer]."'>
		</div>
	</div>
";
					
// si paramétrage formulaire particulier
if (file_exists($base_path.'/includes/templates/avis_subst.tpl.php')) require_once($base_path.'/includes/templates/avis_subst.tpl.php'); 

