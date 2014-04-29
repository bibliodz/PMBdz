<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: do_resa.php,v 1.51 2014-01-30 11:11:44 abacarisse Exp $

$base_path=".";

require_once($base_path."/includes/init.inc.php");
require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');

// r�cup�ration param�tres MySQL et connection � la base
require_once($base_path.'/includes/opac_db_param.inc.php');
require_once($base_path.'/includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

require_once($base_path."/includes/misc.inc.php");

//Sessions !! Attention, ce doit �tre imp�rativement le premer include (� cause des cookies)
require_once($base_path."/includes/session.inc.php");
require_once($base_path.'/includes/start.inc.php');

require_once($base_path."/includes/notice_authors.inc.php");
require_once($base_path."/includes/notice_categories.inc.php");

require_once($base_path."/includes/check_session_time.inc.php");

// r�cup�ration localisation
require_once($base_path.'/includes/localisation.inc.php');

// version actuelle de l'opac
require_once($base_path.'/includes/opac_version.inc.php');

// fonctions de gestion de formulaire
require_once($base_path.'/includes/javascript/form.inc.php');

require_once($base_path.'/includes/templates/common.tpl.php');
require_once($base_path.'/includes/divers.inc.php');

// classe de gestion des cat�gories
require_once($base_path.'/classes/categorie.class.php');
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/notice_display.class.php');

// classe indexation interne
require_once($base_path.'/classes/indexint.class.php');

// classe d'affichage des tags
require_once($base_path.'/classes/tags.class.php');

// classe de gestion des r�servations
require_once($base_path.'/classes/resa.class.php');

// pour l'affichage correct des notices
require_once($base_path."/includes/templates/notice.tpl.php");
require_once($base_path."/includes/navbar.inc.php");
require_once($base_path."/includes/explnum.inc.php");
require_once($base_path."/includes/notice_affichage.inc.php");
require_once($base_path."/includes/bulletin_affichage.inc.php");

require_once($base_path."/includes/empr.inc.php");
require_once($base_path."/includes/connexion_empr.inc.php");
// pour fonction de v�rification de connexion
require_once($base_path.'/includes/empr_func.inc.php');

// autenticazione LDAP - by MaxMan
require_once($base_path."/includes/ldap_auth.inc.php");

// RSS
require_once($base_path."/includes/includes_rss.inc.php");

if ( ($lvl=='make_sugg' || $lvl=='valid_sugg') && $opac_show_suggest == 2) {
	//Suggestion possible sans authentification
	$log_ok = 1;
} else {
	//V�rification de la session
	// si param�trage authentification particuli�re et pour la re-authentification ntlm
	$empty_pwd=true;
	$ext_auth=false;
	if (file_exists($base_path.'/includes/ext_auth.inc.php')) require_once($base_path.'/includes/ext_auth.inc.php');
	$log_ok=connexion_empr();
}

if($opac_parse_html){
	ob_start();
}
	
if ($opac_resa_popup) {
	print $popup_header;
} else {
	if ($opac_show_homeontop==1) $std_header= str_replace("!!home_on_top!!",$home_on_top,$std_header);
	else $std_header= str_replace("!!home_on_top!!","",$std_header);
	// mise � jour du contenu opac_biblio_main_header
	$std_header= str_replace("!!main_header!!",$opac_biblio_main_header,$std_header);
	$std_header= str_replace("!!liens_rss!!",genere_link_rss(),$std_header);
	$std_header = str_replace("!!enrichment_headers!!","",$std_header);
	print $std_header ;
	include($base_path.'/includes/navigator.inc.php');
}
$popup_resa = 1 ;

if ($log_ok) {
	
	switch($lvl) {
		case 'make_sugg' :
			if ($allow_sugg || $opac_show_suggest==2) include($base_path.'/includes/make_sugg.inc.php');
				else print $msg[empr_no_allow_sugg];
			break;
		case 'valid_sugg' :
			if ($allow_sugg || $opac_show_suggest==2) include($base_path.'/includes/valid_sugg.inc.php');
				else print $msg[empr_no_allow_sugg];
			break;
		case 'resa_planning' : 
			if ($allow_book && $opac_resa) include($base_path.'/includes/resa_planning.inc.php');
				else print $msg[empr_no_allow_book];
			break;
		case 'resa_cart':
			if($pmb_logs_activate) recup_notice_infos($id_notice);
			if ($allow_book && $opac_resa){
				include($base_path.'/includes/resa_cart.inc.php');
		
				global $resa_cart_display;
				if($resa_cart_display){
					//on imprime le tableau d'affichage sur la page du panier
					print($resa_cart_display);
				}
			}else{
				print $msg[empr_no_allow_book];
			}
			break;
		default:
		case 'resa':
			if($pmb_logs_activate) recup_notice_infos($id_notice);
			if ($allow_book && $opac_resa) include($base_path.'/includes/resa.inc.php');
			else print $msg[empr_no_allow_book];
			break;
	}

} else {

	if (!$time_expired) {
		$erreur_session = "" ;
		if ($login) print "<br />".$msg["empr_bad_login"]."<br /><br /><br />";
		else {
			print do_formulaire_connexion() ;
		}
	} else {
		print "<br />".sprintf($msg["session_expired"],round($opac_duration_session_auth/60))."<br /><br /><br />";
		print do_formulaire_connexion() ;
	}
	
}

if ($erreur_session) print $erreur_session ;

if ($opac_resa_popup) print $popup_footer;
else {
	//insertions des liens du bas dans le $footer si $opac_show_liensbas
	if ($opac_show_liensbas==1) 
		$footer = str_replace("!!div_liens_bas!!",$liens_bas,$footer);
	else 
		$footer = str_replace("!!div_liens_bas!!","",$footer);
	
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
		
		$footer=str_replace("!!cms_build_info!!","",$footer);	
		
	}
	print $footer ;
}

/**
 * R�cup�re les infos de la notice
 */
function recup_notice_infos($id_notice){
	
	global $infos_notice, $infos_expl;
	
	$rqt="select notice_id, typdoc, niveau_biblio, index_l, libelle_categorie, name_pclass, indexint_name 
		from notices n 
		left join notices_categories nc on nc.notcateg_notice=n.notice_id 
		left join categories c on nc.num_noeud=c.num_noeud 
		left join indexint i on n.indexint=i.indexint_id 
		left join pclassement pc on i.num_pclass=pc.id_pclass
		where notice_id='".$id_notice."'";
	$res_noti = mysql_query($rqt);
	while(($noti=mysql_fetch_array($res_noti))){		
		$infos_notice=$noti;
		$rqt_expl = " select section_libelle, location_libelle, statut_libelle, codestat_libelle, expl_date_depot, expl_date_retour, tdoc_libelle 
					from exemplaires e
					left join docs_codestat co on e.expl_codestat = co.idcode
					left join docs_location dl on e.expl_location=dl.idlocation
					left join docs_section ds on ds.idsection=e.expl_section
					left join docs_statut dst on e.expl_statut=dst.idstatut 
					left join docs_type dt on dt.idtyp_doc=e.expl_typdoc
					where expl_notice='".$id_notice."'";
		$res_expl=mysql_query($rqt_expl);
		while(($expl = mysql_fetch_array($res_expl))){
			$infos_expl[]=$expl;
		}
	}
}


global $pmb_logs_activate;
if($pmb_logs_activate){
	//Enregistrement du log
	global $log, $infos_expl, $infos_notice;	

	$rqt= " select empr_prof,empr_cp, empr_ville, empr_year, empr_sexe, empr_login, empr_date_adhesion, empr_date_expiration, count(pret_idexpl) as nbprets, count(resa.id_resa) as nbresa, code.libelle as codestat, es.statut_libelle as statut, categ.libelle as categ, gr.libelle_groupe,dl.location_libelle 
			from empr e
			left join empr_codestat code on code.idcode=e.empr_codestat
			left join empr_statut es on e.empr_statut=es.idstatut
			left join empr_categ categ on categ.id_categ_empr=e.empr_categ
			left join empr_groupe eg on eg.empr_id=e.id_empr
			left join groupe gr on eg.groupe_id=gr.id_groupe
			left join docs_location dl on e.empr_location=dl.idlocation
			left join resa on e.id_empr=resa_idempr
			left join pret on e.id_empr=pret_idempr
			where e.empr_login='".addslashes($_SESSION['user_code'])."'
			group by resa_idempr, pret_idempr";
	
	$res=mysql_query($rqt);
	if($res){
		$empr_carac = mysql_fetch_array($res);
		$log->add_log('empr',$empr_carac);
	}
	$log->add_log('num_session',session_id());
	$log->add_log('expl',$infos_expl);
	$log->add_log('docs',$infos_notice);
	$log->save();
}

if($opac_parse_html){
	$htmltoparse = ob_get_contents();
	ob_end_clean();
	$res = parseHTML($htmltoparse);
	print $res;
}

/* Fermeture de la connexion */
mysql_close($dbh);