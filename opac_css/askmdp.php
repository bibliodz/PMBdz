<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: askmdp.php,v 1.38 2014-02-17 14:23:01 abacarisse Exp $

$base_path=".";
$is_opac_included = false;

require_once($base_path."/includes/init.inc.php");
require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');

// récupération paramètres MySQL et connection à la base
require_once($base_path.'/includes/opac_db_param.inc.php');
require_once($base_path.'/includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

require_once($base_path."/includes/misc.inc.php");

//Sessions !! Attention, ce doit être impérativement le premer include (à cause des cookies)
require_once($base_path."/includes/session.inc.php");
require_once($base_path.'/includes/start.inc.php');

require_once($base_path."/includes/notice_authors.inc.php");
require_once($base_path."/includes/notice_categories.inc.php");

require_once($base_path."/includes/check_session_time.inc.php");

// récupération localisation
require_once($base_path.'/includes/localisation.inc.php');

// version actuelle de l'opac
require_once($base_path.'/includes/opac_version.inc.php');

// fonctions de gestion de formulaire
require_once($base_path.'/includes/javascript/form.inc.php');

require_once($base_path.'/includes/templates/common.tpl.php');
require_once($base_path.'/includes/divers.inc.php');

// classe de gestion des catégories
require_once($base_path.'/classes/categorie.class.php');
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/notice_display.class.php');

// classe indexation interne
require_once($base_path.'/classes/indexint.class.php');

// classe d'affichage des tags
require_once($base_path.'/classes/tags.class.php');

// classe de gestion des réservations
require_once($base_path.'/classes/resa.class.php');

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/explnum.inc.php");
require_once($base_path."/includes/notice_affichage.inc.php");
require_once($base_path."/includes/bulletin_affichage.inc.php");

// pour l'envoi de mails
require_once($base_path."/includes/mail.inc.php");

// autenticazione LDAP - by MaxMan
require_once($base_path."/includes/ldap_auth.inc.php");

// RSS
require_once($base_path."/includes/includes_rss.inc.php");

// pour fonction de formulaire de connexion
require_once($base_path."/includes/empr.inc.php");
// pour fonction de vérification de connexion
require_once($base_path.'/includes/empr_func.inc.php');


// si paramétrage authentification particulière et pour la re-authentification ntlm
if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');

//Vérification de la session
$log_ok=connexion_empr();

if ($is_opac_included) {
	$std_header = $inclus_header ;
	$footer = $inclus_footer ;
}

// si $opac_show_homeontop est à 1 alors on affiche le lien retour à l'accueil sous le nom de la bibliothèque dans la fiche empr
if ($opac_show_homeontop==1) $std_header= str_replace("!!home_on_top!!",$home_on_top,$std_header);
else $std_header= str_replace("!!home_on_top!!","",$std_header);

// mise à jour du contenu opac_biblio_main_header
$std_header= str_replace("!!main_header!!",$opac_biblio_main_header,$std_header);

// RSS
$std_header= str_replace("!!liens_rss!!",genere_link_rss(),$std_header);

//Enrichissement OPAC
$std_header = str_replace("!!enrichment_headers!!","",$std_header);

if($opac_parse_html || $cms_active){
	ob_start();
}

print $std_header;

require_once ($base_path.'/includes/navigator.inc.php');
	
$query = "SELECT valeur_param FROM parametres WHERE type_param='opac' AND sstype_param = 'biblio_name'";
$result = mysql_query($query) or die ("*** Erreur dans la requ&ecirc;te <br />*** $query<br />\n");
$row = mysql_fetch_array ($result);
$demandeemail= "<hr /><p class='texte'>".$msg[mdp_txt_intro_demande]."</p>
	<form action=\"askmdp.php\" method=\"post\" ><br />
	<input type=\"text\" name=\"email\" size=\"20\" border=\"0\" value=\"email@\" onFocus=\"this.value='';\">&nbsp;&nbsp;
	<input type=\"hidden\" name=\"demande\" value=\"ok\" >
	<input type='submit' name='ok' value='".$msg[mdp_bt_send]."' class='bouton'>
	</form>"; 

print "<blockquote>";
$email = str_replace("%", "", $email);
if ($demande!="ok" || $email=='') {

	// Mettre ici le formulaire de saisie de l'email
	print $demandeemail ;
	
} elseif ($email) {
	$query = "SELECT empr_login, empr_password, empr_location,empr_mail,concat(empr_prenom,' ',empr_nom) as nom_prenom FROM empr WHERE empr_mail like '%".$email."%'";
	$result = mysql_query($query) or die ("*** Erreur dans la requ&ecirc;te <br />*** $query<br />\n");
	if (mysql_num_rows($result)!=0) {
		$res_envoi = false;
		while ($row = mysql_fetch_object ($result)) {
			$emails_empr = explode(";",$row->empr_mail);
			for ($i=0; $i<count($emails_empr); $i++) {
				if (strtolower($email) == strtolower($emails_empr[$i])) {
					if (!$opac_biblio_name) {
						$query_loc = "SELECT name, email FROM docs_location WHERE idlocation='$row->empr_location'";
						$result_loc = mysql_query($query_loc) or die ("*** Erreur dans la requ&ecirc;te <br />*** $query_loc<br />\n");
						$info_loc = mysql_fetch_object ($result_loc) ;
						$biblio_name_temp=$info_loc->name ;
						$biblio_email_temp=$info_loc->email ;
					} else {
						$biblio_name_temp=$opac_biblio_name;
						$biblio_email_temp=$opac_biblio_email;
					}
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
		
					// Pour faire suite à votre demande, nous vous prions de trouver ci-dessous vos informations de connexion pour <b>!!biblioname!!</b> :<br /> -Identifiant: !!login!! <br /> -Mot de passe: !!password!! <br /><br />Si vous rencontrez des difficultés, adressez un mail à !!biblioemail!!.
					$messagemail = $msg[mdp_mail_body] ;
					$messagemail = str_replace("!!login!!",$row->empr_login,$messagemail);
					$messagemail = str_replace("!!password!!",$row->empr_password,$messagemail);
					$messagemail = str_replace("!!biblioname!!","<a href=\"$opac_url_base\">".$biblio_name_temp."</a>",$messagemail);
					$messagemail = str_replace("!!biblioemail!!","<a href=mailto:$opac_biblio_email>$biblio_email_temp</a>",$messagemail);
		
					$objetemail = str_replace("!!biblioname!!",$biblio_name_temp,$msg[mdp_mail_obj]);
					print "<hr />";
					
					if($opac_parse_html){
						$objetemail = parseHTML($objetemail);
						$messagemail = parseHTML($messagemail);
						$biblio_name_temp = parseHTML($biblio_name_temp);
						$biblio_email_temp = parseHTML($biblio_email_temp); 
					}	
		
					$res_envoi=@mailpmb(trim($row->nom_prenom), $emails_empr[$i],$objetemail,$messagemail,$biblio_name_temp, $biblio_email_temp, $headers);
					if (!$res_envoi) {
						print "<p class='texte'>Could not send information to $emails_empr[$i].</p><br />" ;
					} else {
						print "<p class='texte'>".$msg[mdp_sent_ok]." $emails_empr[$i].</p><br />" ;
					}
				}
			}
		}
		if (!$res_envoi) {
			print "<hr /><p class='texte'>".str_replace("!!biblioemail!!","<a href=mailto:$opac_biblio_email>$opac_biblio_email</a>",$msg[mdp_no_email])."</p>" ;
			print $demandeemail ;
		}
	} else {
		print "<hr /><p class='texte'>".str_replace("!!biblioemail!!","<a href=mailto:$opac_biblio_email>$opac_biblio_email</a>",$msg[mdp_no_email])."</p>" ;
		print $demandeemail ;
	}
}

print "</blockquote>";

//insertions des liens du bas dans le $footer si $opac_show_liensbas
if ($opac_show_liensbas==1) $footer = str_replace("!!div_liens_bas!!",$liens_bas,$footer);
else $footer = str_replace("!!div_liens_bas!!","",$footer);

if ($opac_show_bandeau_2==0) {
	$bandeau_2_contains= "";
} else {
	$bandeau_2_contains= "<div id=\"bandeau_2\">!!contenu_bandeau_2!!</div>";
}
//affichage du bandeau de gauche si $opac_show_bandeaugauche = 1
if ($opac_show_bandeaugauche==0) {
	$footer= str_replace("!!contenu_bandeau!!",$bandeau_2_contains,$footer);
	$footer= str_replace("!!contenu_bandeau_2!!",$opac_facette_in_bandeau_2?$lvl1.$facette:"",$footer);
} else {
	$footer = str_replace("!!contenu_bandeau!!","<div id=\"bandeau\">!!contenu_bandeau!!</div>".$bandeau_2_contains,$footer);
	$home_on_left=str_replace("!!welcome_page!!",$msg["welcome_page"],$home_on_left);
	$adresse=str_replace("!!common_tpl_address!!",$msg["common_tpl_address"],$adresse);
	$adresse=str_replace("!!common_tpl_contact!!",$msg["common_tpl_contact"],$adresse);
	
	// loading the languages avaiable in OPAC - martizva >> Eric
	require_once($base_path.'/includes/languages.inc.php');
	$home_on_left = str_replace("!!common_tpl_lang_select!!", show_select_languages("empr.php"), $home_on_left);
	
	if (!$_SESSION["user_code"]) {
		$loginform=str_replace('<!-- common_tpl_login_invite -->',$msg["common_tpl_login_invite"],$loginform);
		$loginform__ = genere_form_connexion_empr();
	} else {
		$loginform__.="<b>".$empr_prenom." ".$empr_nom."</b><br />\n";
		$loginform__.="<a href=\"empr.php\" id=\"empr_my_account\">".$msg["empr_my_account"]."</a><br />
			<a href=\"index.php?logout=1\" id=\"empr_logout_lnk\">".$msg["empr_logout"]."</a>";
	}
	$loginform = str_replace("!!login_form!!",$loginform__,$loginform);
	$footer= str_replace("!!contenu_bandeau!!",$home_on_left.$loginform.$meteo.$adresse,$footer);
	$footer= str_replace("!!contenu_bandeau_2!!",$opac_facette_in_bandeau_2?$lvl1.$facette:"",$footer);
}

//Enregistrement du log
global $pmb_logs_activate;
if($pmb_logs_activate){	
	global $log;
	$log->add_log('num_session',session_id());
	$log->save();
}

$cms_build_info="";
if($cms_build_activate || $_SESSION["cms_build_activate"]){ // issu de la gestion
	if($pageid){
		require_once($base_path."/classes/cms/cms_pages.class.php");
		$cms_page= new cms_page($pageid);
		$cms_build_info['page']=$cms_page->get_env();
	}
	global $log, $infos_notice, $infos_expl, $nb_results_tab;
	$cms_build_info['input']="askmdp.php";
	$cms_build_info['session']=$_SESSION;
	$cms_build_info['post']=$_POST;
	$cms_build_info['get']=$_GET;
	$cms_build_info['lvl']=$lvl;
	$cms_build_info['tab']=$tab;	
	$cms_build_info['log']=$log;
	$cms_build_info['infos_notice']=$infos_notice;
	$cms_build_info['infos_expl']=$infos_expl;
	$cms_build_info['nb_results_tab']=$nb_results_tab;
	$cms_build_info['search_type_asked']=$search_type_asked;
	$cms_build_info=rawurlencode(serialize($cms_build_info));
	$cms_build_info= "<input type='hidden' id='cms_build_info' name='cms_build_info' value='".$cms_build_info."' />";
	$cms_build_info.="	
	<script type='text/javascript'>
		if(window.top.window.cms_opac_loaded){
			window.onload = function() {
				window.top.window.cms_opac_loaded('".$_SERVER['REQUEST_URI']."');
			}
		}
	</script>
	";
	$_SESSION["cms_build_activate"]="1";
}
$footer=str_replace("!!cms_build_info!!",$cms_build_info,$footer);	

print $footer;

if($opac_parse_html || $cms_active){	
	if($opac_parse_html){
		$htmltoparse= parseHTML(ob_get_contents());
	}else{
		$htmltoparse= ob_get_contents();
	}
			
	ob_end_clean();
	if ($cms_active) {
		require_once($base_path."/classes/cms/cms_build.class.php");
		$cms=new cms_build();
		$htmltoparse = $cms->transform_html($htmltoparse);
	}
	print $htmltoparse;
}
/* Fermeture de la connexion */
mysql_close($dbh);
