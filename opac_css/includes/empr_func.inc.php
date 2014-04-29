<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: empr_func.inc.php,v 1.31 2014-02-11 13:02:57 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function connexion_empr() {
	global $dbh, $msg, $opac_duration_session_auth;
	global $time_expired, $erreur_session, $login, $password ;
	global $auth_ok, $lang, $code, $emprlogin;
	global $first_log;
	global $erreur_connexion;
	global $opac_opac_view_activate, $pmb_opac_view_class, $opac_view_class;
	global $opac_default_style;
	//a positionner si authentification exterieure
	global $ext_auth,$empty_pwd;
	global $base_path,$class_path;
	global $cms_build_activate;
	//a positionner si les vues OPAC sont activ�es
	global $include_path;
	
	$erreur_connexion=0;
	
	$log_ok=0;
	if (!$_SESSION["user_code"]) {
		if (!get_magic_quotes_gpc()) $p_login=addslashes($_POST['login']); else $p_login=$_POST['login'];
		if ($time_expired==0) { // d�but if ($time_expired==0) 1
			//Si pas de session en cours, v�rification du login
			$verif_query = "SELECT id_empr, empr_cb, empr_nom, empr_prenom, empr_password, empr_lang, empr_date_expiration<sysdate() as isexp, empr_login, empr_ldap,empr_location, allow_opac 
					FROM empr
					JOIN empr_statut ON empr_statut=idstatut
					WHERE empr_login='".($emprlogin ? $emprlogin :$p_login)."'";
			$verif_result = mysql_query($verif_query);
			// r�cup�ration des valeurs MySQL du lecteur et injection dans les variables
			while ($verif_line = mysql_fetch_array($verif_result)) {
				$verif_empr_cb = $verif_line['empr_cb'];
				$verif_empr_login = $verif_line['empr_login'];
				$verif_empr_ldap = $verif_line['empr_ldap'];
				$verif_empr_password = $verif_line['empr_password'];
				$verif_lang = ($verif_line['empr_lang']?$verif_line['empr_lang']:"fr_FR");
				$verif_id_empr = $verif_line['id_empr'];
				$verif_isexp = $verif_line['isexp'];
				$verif_opac = $verif_line['allow_opac'];
				$empr_location = $verif_line['empr_location'];
			}

			$auth_ok=0;
			if ($verif_opac) {
				if ($ext_auth) $auth_ok=$ext_auth;
				elseif (($verif_empr_ldap)) $auth_ok=auth_ldap($p_login,$password); // auth by server ldap
				elseif($code) $auth_ok = connexion_auto();
				else $auth_ok=( ($empty_pwd ||(!$empty_pwd && $verif_empr_password)) && ($verif_empr_password==stripslashes($password)) && ($verif_empr_login!="")/*&&(!$verif_isexp)*/ ); //auth standard
			}

			if ($auth_ok) { // d�but if ($auth_ok) 1 
				//Si mot de passe correct, enregistrement dans la session de l'utilisateur
				$log_ok=1;
				if($_SESSION["cms_build_activate"])$cms_build_activate=1;
				if($_SESSION["build_id_version"])$build_id_version=$_SESSION["build_id_version"];
				//R�cup�ration de l'environnement pr�c�dent
				$requete="select session from opac_sessions where empr_id=".$verif_id_empr;
				$res_session=mysql_query($requete);
				if (@mysql_num_rows($res_session)) {
					$temp_session=unserialize(mysql_result($res_session,0,0));
					$_SESSION=$temp_session;
				} else 
					$_SESSION=array();				
				$_SESSION["cms_build_activate"]=$cms_build_activate;
				$_SESSION["build_id_version"]=$build_id_version;	
				if(!$code)$_SESSION["connexion_empr_auto"]=0;
				$_SESSION["user_code"]=$verif_empr_login;
				$_SESSION["id_empr_session"]=$verif_id_empr;
				$_SESSION["connect_time"]=time();
				$_SESSION["lang"]=$verif_lang;
				$_SESSION["empr_location"]=$empr_location;
				$req="select location_libelle from docs_location where idlocation='".$_SESSION["empr_location"]."'";
				$_SESSION["empr_location_libelle"]= mysql_result(mysql_query($req, $dbh),0,0);
				
				// change language and charset after login
				$lang=$_SESSION["lang"]; 
				set_language($lang);
				if(!$verif_isexp){
					recupere_pref_droits($_SESSION["user_code"]);
					$_SESSION["user_expired"] = $verif_isexp;
				} else {
					recupere_pref_droits($_SESSION["user_code"],1);
					$_SESSION["user_expired"] = $verif_isexp;
					echo "<script>alert(\"".$msg["empr_expire"]."\");</script>";
					$erreur_connexion=1;
				}
				if($opac_opac_view_activate){
					$_SESSION["opac_view"]=0;
					$_SESSION['opac_view_query']=0;
					if(!$pmb_opac_view_class) $pmb_opac_view_class= "opac_view";
					require_once($base_path."/classes/".$pmb_opac_view_class.".class.php");
					$opac_view_class= new $pmb_opac_view_class($_SESSION["opac_view"],$_SESSION["id_empr_session"]);
					if($opac_view_class->id){
						$_SESSION["opac_view"]=$opac_view_class->id;
					 	if(!$opac_view_class->opac_view_wo_query) {
 							$_SESSION['opac_view_query']=1;
 						}
					}else {
						$_SESSION["opac_view"]=0;
					}
					$css=$_SESSION["css"]=$opac_default_style;
				}				
				$first_log=true;
			} else {
				//Sinon, on d�truit la session cr��e
				if($_SESSION["cms_build_activate"])$cms_build_activate=1;
				if($_SESSION["build_id_version"])$build_id_version=$_SESSION["build_id_version"];				
				@session_destroy();					
				if($cms_build_activate){
					session_start();
					$_SESSION["cms_build_activate"]=$cms_build_activate;
					$_SESSION["build_id_version"]=$build_id_version;
				}
				if (($verif_empr_password!=stripslashes($password)) || ($verif_empr_login=="") || $verif_empr_ldap || $code){
					// la saisie du mot de passe ou du login est incorrect ou erreur de connexion avec le ldap
					$erreur_session = $empr_erreur_header;
					$erreur_session .= $msg["empr_type_card_number"]."<br />";
					$erreur_session .= $empr_erreur_footer;
					$erreur_connexion=3;
				}elseif ($verif_isexp){
					//Si l'abonnement est expir�
					echo "<script>alert(\"".$msg["empr_expire"]."\");</script>";
					$erreur_connexion=1;
				}elseif(!$verif_opac){
					//Si la connexion � l'opac est interdite
					echo "<script>alert(\"".$msg["empr_connexion_interdite"]."\");</script>";
					$erreur_connexion=2;
				}else{
					// Autre cas au cas o�...
					$erreur_session = $empr_erreur_header;
					$erreur_session .= $msg["empr_type_card_number"]."<br />";
					$erreur_session .= $empr_erreur_footer;
					$erreur_connexion=3;
				}
				$log_ok=0 ;
				$time_expired = 0 ;
			} // fin if ($auth_ok) 1
		} else  // la session a expir�, on va le lui dire
			echo "<script>alert(\"".sprintf($msg["session_expired"],round($opac_duration_session_auth/60))."\");</script>";
	} else {
		//Si session en cours, pas de probl�me...
		$log_ok=1;
		$login=$_SESSION["user_code"];
		if($_SESSION["user_expired"]){
			recupere_pref_droits($login,1);
		} else 	recupere_pref_droits($login);
	}
	// pour visualiser une notice issue de DSI avec une connexion auto
	if($_SESSION["connexion_empr_auto"] && $log_ok){
		global $connexion_empr_auto,$tab,$lvl;
		$connexion_empr_auto=1;
		if(!$code){
			$tab="dsi";
			$lvl="bannette";			
		}
	}	
	return $log_ok;

}

function recupere_pref_droits($login,$limitation_adhesion=0) {
	global $dbh, $msg ;
	global $id_empr,
		$empr_cb,
		$empr_nom,
		$empr_prenom,
		$empr_adr1,
		$empr_adr2,
		$empr_cp,
		$empr_ville,
		$empr_mail,
		$empr_tel1,
		$empr_tel2,
		$empr_prof,
		$empr_year,
		$empr_categ,
		$empr_codestat,
		$empr_sexe,
		$empr_login,
		$empr_ldap,
		$empr_location;

	global $allow_loan,
		$allow_loan_hist,
		$allow_book,
		$allow_opac,
		$allow_dsi,
		$allow_dsi_priv,
		$allow_sugg,
		$allow_dema,
		$allow_prol,
		$allow_avis,
		$allow_tag,
		$allow_pwd,
		$allow_liste_lecture,
		$allow_self_checkout,
		$allow_self_checkin,
		$allow_serialcirc;
	global $opac_adhesion_expired_status;
	
	if($limitation_adhesion && $opac_adhesion_expired_status){
		$req = "select * from empr_statut where idstatut='".$opac_adhesion_expired_status."'";
		$res = mysql_query($req,$dbh);
		$data_expired = mysql_fetch_array($res);
		$droit_loan= $data_expired['allow_loan'];
		$droit_loan_hist= $data_expired['allow_loan_hist'];
		$droit_book= $data_expired['allow_book'];
		$droit_opac= $data_expired['allow_opac'];
		$droit_dsi= $data_expired['allow_dsi'];
		$droit_dsi_priv= $data_expired['allow_dsi_priv'];
		$droit_sugg= $data_expired['allow_sugg'];
		$droit_dema= $data_expired['allow_dema'];
		$droit_prol= $data_expired['allow_prol'];
		$droit_avis= $data_expired['allow_avis'];
		$droit_tag= $data_expired['allow_tag'];
		$droit_pwd= $data_expired['allow_pwd'];
		$droit_liste_lecture = $data_expired['allow_liste_lecture'];		
		$droit_self_checkout = $data_expired['allow_self_checkout'];
		$droit_self_checkin = $data_expired['allow_self_checkin'];
		$droit_serialcirc = $data_expired['allow_serialcirc'];
	} else {
		$droit_loan= 1;
		$droit_loan_hist=1;
		$droit_book= 1;
		$droit_opac= 1;
		$droit_dsi= 1;
		$droit_dsi_priv=1;
		$droit_sugg= 1;
		$droit_dema= 1;
		$droit_prol= 1;
		$droit_avis=1 ;
		$droit_tag= 1;
		$droit_pwd= 1;
		$droit_liste_lecture = 1;
		$droit_self_checkout=1;
		$droit_self_checkin=1;
		$droit_serialcirc=1;
	}
	
	$query0 = "select * from empr, empr_statut where empr_login='".$login."' and idstatut=empr_statut ";
	$req0 = mysql_query($query0,$dbh);
	$data = mysql_fetch_array($req0);
	$id_empr = $data['id_empr'];
	$empr_cb = $data['empr_cb'];
	$empr_nom = $data['empr_nom'];
	$empr_prenom= $data['empr_prenom'];
	$empr_adr1= $data['empr_adr1'];
	$empr_adr2= $data['empr_adr2'];
	$empr_cp= $data['empr_cp'];
	$empr_ville= $data['empr_ville'];
	$empr_mail= $data['empr_mail'];
	$empr_tel1= $data['empr_tel1'];
	$empr_tel2= $data['empr_tel2'];
	$empr_prof= $data['empr_prof'];
	$empr_year= $data['empr_year'];
	$empr_categ= $data['empr_categ'];
	$empr_codestat= $data['empr_codestat'];
	$empr_sexe= $data['empr_sexe'];
	$empr_login= $data['empr_login'];
	$empr_ldap= $data['empr_ldap'];
	$empr_location= $data['empr_location'];
	
	// droits de l'utilisateur
	$allow_loan= $data['allow_loan'] & $droit_loan;
	$allow_loan_hist= $data['allow_loan_hist'] & $droit_loan_hist;
	$allow_book= $data['allow_book'] & $droit_book;
	$allow_opac= $data['allow_opac'] & $droit_opac;
	$allow_dsi= $data['allow_dsi'] & $droit_dsi;
	$allow_dsi_priv= $data['allow_dsi_priv'] & $droit_dsi_priv;
	$allow_sugg= $data['allow_sugg'] & $droit_sugg;
	$allow_dema= $data['allow_dema'] & $droit_dema;
	$allow_prol= $data['allow_prol'] & $droit_prol;
	$allow_avis= $data['allow_avis'] & $droit_avis;
	$allow_tag= $data['allow_tag'] & $droit_tag;
	$allow_pwd= $data['allow_pwd'] & $droit_pwd;
	$allow_liste_lecture = $data['allow_liste_lecture'] & $droit_liste_lecture;
	$allow_self_checkout= $data['allow_self_checkout'] & $droit_self_checkout;
	$allow_self_checkin= $data['allow_self_checkin'] & $droit_self_checkin;
	$allow_serialcirc= $data['allow_serialcirc'] & $droit_serialcirc;
}


function connexion_auto(){
	global $opac_connexion_phrase;
	global $date_conex,$emprlogin,$code;
	
	$log_ok=0;
	if($opac_connexion_phrase && ($code == md5($opac_connexion_phrase.$emprlogin.$date_conex)))
		$log_ok = 1;
	return $log_ok;
}