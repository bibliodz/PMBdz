<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mailing.php,v 1.19 2014-02-26 14:01:34 dgoron Exp $

// définition du minimum nécéssaire
$base_path="../../..";
$base_auth = "CIRCULATION_AUTH";
$base_title = "";
require_once ("$base_path/includes/init.inc.php");
require_once($class_path."/mailtpl.class.php");
require_once($class_path."/mailing_empr.class.php");

// les requis par mailing.php ou ses sous modules
include_once("$include_path/mail.inc.php") ;
include_once("$include_path/mailing.inc.php") ;

$mailtpl = new mailtpls();
if($mailtpl->get_count_tpl()){
	$mailtpl_script="
	<script type='text/javascript'>
		function insert_template(theselector,objet_mail,dest){	
			var id_mailtpl=0;
			for (var i=0 ; i< theselector.options.length ; i++){
				if (theselector.options[i].selected){
					id_mailtpl=theselector.options[i].value ;
					break;
				}
			}
			if(!id_mailtpl) return ;
			var url= '$base_path/ajax.php?module=ajax&categ=mailtpl&quoifaire=get_mailtpl&id_mailtpl='+id_mailtpl;	
			var action = new http_request();
			action.request(url,0,'',1,response_tpl,0,0);				
		}
		
		function response_tpl(info){
			try{ 
				var info=eval('(' + info + ')');
			} catch(e){
				if(typeof console != 'undefined') {
					console.log(e);
				}
			}
	
			// objet du mail
			document.getElementById('f_objet_mail').value=info.objet;			
			// contenu
			document.getElementById('f_message').innerHTML=info.tpl;	
			if(typeof(tinyMCE)!= 'undefined')tinyMCE.updateContent('f_message');
		}
	</script>
	<div class='row'>
		<label class='etiquette' >".$msg["admin_mailtpl_sel"]."</label>
		<div class='row'>
			".$mailtpl->get_sel('mailtpl_id',0)."							
			<input type='button' class='bouton' value=\" ".$msg["admin_mailtpl_insert"]." \" 
			onClick=\"insert_template(document.getElementById('mailtpl_id'), document.getElementById('f_objet_mail'), document.getElementById('f_message')); return false; \" />							
		</div>
	</div>
	";	
} else 	$mailtpl_script="";

$mailtpl_vars="
	<div class='row'>
		<label class='etiquette'>".$msg["admin_mailtpl_form_selvars"]."</label>
		<div class='row'>
			".mailtpl::get_selvars()."	
		</div>
	</div>
";

$get_sel_img="";
$sel_img=mailtpl::get_sel_img();
if($sel_img)$get_sel_img="
	<div class='row'>
		<label class='etiquette'>".$msg["admin_mailtpl_form_sel_img"]."</label>
		<div class='row'>
			".mailtpl::get_sel_img()."
		</div>
	</div>
";

$urlbase="./circ/caddie/";
if (!$idemprcaddie) die();

if ($pmb_javascript_office_editor) {
	print $pmb_javascript_office_editor ;
}

if (!$f_message && !$pmb_javascript_office_editor) {
	$f_message="
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">
</head>
<body>
</body>
</html>";
} else $f_message=stripslashes($f_message);
$f_objet_mail = stripslashes($f_objet_mail);

print "<div id='contenu-frame'>" ;

switch ($sub) {
	case "redige" :
		echo "<br />
				<form class='form-$current_module' method='post' name='form_message' id='form_message' action='./mailing.php' enctype='multipart/form-data' />
				<h3>$msg[empr_mailing_titre_form]</h3>
				<div class='form-contenu'>
					$mailtpl_script
					<div class='row'>
						<label class='etiquette' for='f_objet_mail'>$msg[empr_mailing_form_obj_mail]</label>
						<div class='row'>
							<input type='text' class='saisie-80em' id='f_objet_mail'  name='f_objet_mail' value=\"".htmlentities(stripslashes($f_objet_mail),ENT_QUOTES,$charset)."\" />
						</div>
					</div>
					
					<div class='row'>
						<label class='etiquette' for='f_message'>$msg[empr_mailing_form_message]</label>
						<div class='row'>
							<textarea id='f_message' name='f_message' cols='100' rows='20'>".htmlentities(stripslashes($f_message),ENT_QUOTES,$charset)."</textarea>
						</div>
					</div>
					<div class='row'>
						<label class='etiquette' >".$msg["empr_mailing_form_message_piece_jointe"]."</label>
					</div>
					<div class='row'>
						<input type='file' id='piece_jointe_mailing' name='piece_jointe_mailing' class='saisie-80em' size='60' />
	  				</div>
					$mailtpl_vars
					$get_sel_img
					<div class='row'></div>
					</div>
					<div class='row'>
						<div class='left'>";
		if (!$pmb_javascript_office_editor) echo "<input type='button' class='bouton' value=\" ".$msg["empr_mailing_bt_visualiser"]." \" onClick=\"document.getElementById('form_message').action='visu_message.php'; document.getElementById('form_message').target='visu_message'; document.getElementById('form_message').submit(); \" />";
		echo "					</div>
						<div class='right'>
							<input type='button' class='bouton' value=\" ".$msg["empr_mailing_bt_envoyer"]." \" onClick=\"document.getElementById('form_message').action='mailing.php'; document.getElementById('form_message').target='_self'; document.getElementById('form_message').submit(); \" />
							<input type='hidden' name='sub' value='envoi' />
							<input type='hidden' name='idemprcaddie' value='$idemprcaddie' />
							</div>
						</div>
				<div class='row'></div>
				</form>";

		if (!$pmb_javascript_office_editor)	echo "<div class='row'>
					<label class='etiquette'>$msg[empr_mailing_form_obj_mail]</label>
					<div class='row'>
						".htmlentities(stripslashes($f_objet_mail),ENT_QUOTES,$charset)."
						</div>
					</div>
				<div class='row'>
					<label class='etiquette'>$msg[empr_mailing_form_message]</label>
					<div class='row'>
						<center><iframe id='visu_message' name='visu_message' frameborder='2' scrolling='yes' width='80%' height='700' src='./visu_message.php'></iframe>
						</center>
						</div>
					</div>
			";
		break;
	case "envoi" :		
		$mailing = new mailing_empr($idemprcaddie);
		if ($total_envoyes) $mailing->total_envoyes = $total_envoyes;
		if ($total) $mailing->total = $total;
		$mailing->send($f_objet_mail, $f_message, 20);

		$sql = "select id_empr, empr_mail, empr_nom, empr_prenom from empr, empr_caddie_content where (flag='' or flag is null) and empr_caddie_id=$idemprcaddie and object_id=id_empr";
		$sql_result = mysql_query($sql) or die ("Couldn't select compte reste mailing !");
		$n_envoi_restant=mysql_num_rows($sql_result);

		if ($n_envoi_restant > 0) {
			$parametres[total]=$mailing->total;
			$parametres[sub]="envoi";
			$parametres[total_envoyes]=$mailing->total_envoyes;
			$parametres[f_objet_mail]=htmlentities($f_objet_mail,ENT_QUOTES,$charset);
			$parametres[f_message]=htmlentities($f_message,ENT_QUOTES,$charset);
			$parametres[idemprcaddie]=$idemprcaddie;
			$msg[empr_mailing_recap_comptes_encours] = str_replace("!!total_envoyes!!", $mailing->total_envoyes, $msg[empr_mailing_recap_comptes_encours]) ;
			$msg[empr_mailing_recap_comptes_encours] = str_replace("!!total!!", $mailing->total, $msg[empr_mailing_recap_comptes_encours]) ;
			$msg[empr_mailing_recap_comptes_encours] = str_replace("!!n_envoi_restant!!", $n_envoi_restant, $msg[empr_mailing_recap_comptes_encours]) ;
			$message_info="<div class='row'>".
							$msg[empr_mailing_recap_comptes_encours]."
							</div>";
			print construit_formulaire_recharge (1000, "./mailing.php", "envoi_mailing", $parametres, $f_objet_mail, $message_info) ;
		} else {
			print "
			<h1>$msg[empr_mailing_titre_resultat]</h1>
				<div class='row'>
					<strong>$msg[empr_mailing_form_obj_mail]</strong> 
						".htmlentities($f_objet_mail,ENT_QUOTES,$charset)."
					</div>
				<div class='row'>
					<strong>$msg[empr_mailing_resultat_envoi]</strong> ";
			$msg[empr_mailing_recap_comptes] = str_replace("!!total_envoyes!!", $mailing->total_envoyes, $msg[empr_mailing_recap_comptes]) ;
			$msg[empr_mailing_recap_comptes] = str_replace("!!total!!", $mailing->total, $msg[empr_mailing_recap_comptes]) ;
			print $msg[empr_mailing_recap_comptes] ;
			print "		</div>
				<hr />
				<div class='row'>
					<a href='../../../circ.php?categ=caddie&sub=gestion&quoi=razpointage&moyen=raz&action=&idemprcaddie=$idemprcaddie&item=' target=_top>".$msg[empr_mailing_raz_pointage]."</a>
					</div>
				";
			$sql = "select id_empr, empr_mail, empr_nom, empr_prenom from empr, empr_caddie_content where flag='2' and empr_caddie_id=$idemprcaddie and object_id=id_empr ";
			$sql_result = mysql_query($sql) ;
			if (mysql_num_rows($sql_result)) {
				print "
					<hr /><div class='row'>
					<strong>$msg[empr_mailing_liste_erreurs]</strong>  
					</div>";
				while ($obj_erreur=mysql_fetch_object($sql_result)) {
					print "<div class='row'>
						".$obj_erreur->empr_nom." ".$obj_erreur->empr_prenom." (".$obj_erreur->empr_mail.") 
						</div>
						";
				}
			}
		}
		break;
	
	default:
		// include("$include_path/messages/help/$lang/mailing_empr.txt") ;
		break;
	}
print "</div></body></html>";



// Fonction qui construit un formulaire qui relance
function construit_formulaire_recharge ($time_out, $action, $name, $hidden_param, $texte_titre="",$texte_message="") {
	global $current_module, $msg;
	
	if (!is_array($hidden_param)) return "";
	$formulaire="\n<form class='form-$current_module' name=\"$name\" method=\"post\" action=\"$action\">";
	$formulaire.="\n<h3>$texte_titre</h3>
		<div class='form-contenu'>";
		
	while (list($cle, $params) = each($hidden_param)) {
		$formulaire.="\n<INPUT NAME=\"$cle\" TYPE=\"hidden\" value=\"$params\">";
		} // fin de liste
	$formulaire.=$texte_message;
	$formulaire.="\n</div>";
	if ($time_out<0) $formulaire.="\n<div class='row'><input type=submit class=bouton value='".$msg[form_recharge_bt_continuer]."' /></div>";
	$formulaire.="\n</form>";
	switch ($time_out) {
		case 0:
			$formulaire.="\n<script>document.".$name.".submit();</script>";
		 	break;
		case -1:
		 	break;
		default:
			$formulaire.="\n<script>setTimeout(\"document.".$name.".submit()\",".$time_out.");</script>";
		 	break;
		}
	return $formulaire;
	} 