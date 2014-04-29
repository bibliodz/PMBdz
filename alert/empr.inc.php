<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: empr.inc.php,v 1.14 2012-04-11 13:36:07 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$temp_aff = empr_proche_expiration() . empr_expiration() ;

if ($temp_aff) $aff_alerte .= "<ul>".$msg["fins_abonnements"].$temp_aff."</ul>";

$temp_aff = empr_categ_change () ;

if ($temp_aff) $aff_alerte .= "<ul>".$msg["empr_categ_alert"].$temp_aff."</ul>";

function empr_proche_expiration () {
	global $dbh ;
	global $msg;
	global $pmb_relance_adhesion, $deflt2docs_location,$pmb_lecteurs_localises;
	
	if($pmb_lecteurs_localises){
		$condion_loc=" AND empr_location='".$deflt2docs_location."' ";
	}else{
		$condion_loc="";
	}
					
	// comptage des emprunteurs proche d'expiration d'abonnement
	$sql = " SELECT 1 FROM empr where ((to_days(empr_date_expiration) - to_days(now()) ) <=  $pmb_relance_adhesion ) and empr_date_expiration >= now()  ".$condion_loc." limit 1";
	$req = mysql_query($sql) or die ($msg["err_sql"]."<br />".$sql."<br />".mysql_error());
	$nb_limite = mysql_num_rows($req) ;
	if (!$nb_limite) return "" ;
		else return "<li><a href='./edit.php?categ=empr&sub=limite' target='_parent'>$msg[empr_expir_pro]</a></li>" ;
}

function empr_expiration () {
	global $dbh ;
	global $msg; 
	global $empr_statut_adhes_depassee,$deflt2docs_location,$pmb_lecteurs_localises;

	if (!$empr_statut_adhes_depassee) $empr_statut_adhes_depassee=2;
	
	if($pmb_lecteurs_localises){
		$condion_loc=" AND empr_location='".$deflt2docs_location."' ";
	}else{
		$condion_loc="";
	}	
	// comptage des emprunteurs expiration d'abonnement
	$sql = "SELECT 1 FROM empr where empr_statut!=$empr_statut_adhes_depassee and empr_date_expiration < now() ".$condion_loc."  limit 1";
	$req = mysql_query($sql) or die ($msg["err_sql"]."<br />".$sql."<br />".mysql_error());
	$nb_depasse = mysql_num_rows($req) ;
	if (!$nb_depasse) return "" ;
		else return "<li><a href='./edit.php?categ=empr&sub=depasse' target='_parent'>$msg[empr_expir_att]</a></li>" ;
}

function empr_categ_change () {
	global $dbh ;
	global $msg; 
	
	// comptage des emprunteurs qui n'ont pas le droit d'�tre dans la cat�gorie
	$sql = "select 1 from empr left join empr_categ on empr_categ = id_categ_empr ";
	$sql .=" where ((((age_min<> 0) || (age_max <> 0)) && (age_max >= age_min)) && (((DATE_FORMAT( curdate() , '%Y' )-empr_year) < age_min) || ((DATE_FORMAT( curdate() , '%Y' )-empr_year) > age_max)))";
	$req = mysql_query($sql) or die ($msg["err_sql"]."<br />".$sql."<br />".mysql_error());
	$nb_change = mysql_num_rows($req) ;
	if (!$nb_change) return "" ;
		else return "<li><a href='./edit.php?categ=empr&sub=categ_change' target='_parent'>$msg[empr_change_categ_todo]</a></li>" ;
}

