<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: analysis_duplicate.inc.php,v 1.1 2011-11-29 13:25:23 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");


//verification des droits de modification notice
$acces_m=1;
if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
	$dom_1= $ac->setDomain(1);
	$acces_j = $dom_1->getJoin($PMBuserid,8,'bulletin_notice');
	$q = "select count(1) from bulletins $acces_j where bulletin_id=".$bul_id;
	$r = mysql_query($q, $dbh);
	if ($r) {
		if(mysql_result($r,0,0)==0) {
			$acces_m=0;
		}
	} else {
		$acces_m=0;
	}
}

if ($acces_m==0) {

	if (!$analysis_id) {
		error_message('', htmlentities($dom_1->getComment('mod_bull_error'), ENT_QUOTES, $charset), 1, '');
	} else {
		error_message('', htmlentities($dom_1->getComment('mod_depo_error'), ENT_QUOTES, $charset), 1, '');
	}

} else {

	// affichage d'un form pour cr�ation, modification d'un article de p�riodique
	if(!$analysis_id) {
		// pas d'id, c'est une cr�ation
		echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4022], $serial_header);
	} else {
		echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[analysis_duplicate], $serial_header);
	}
	
	// on instancie le truc
	$myAnalysis = new analysis($analysis_id, $bul_id);
	$myAnalysis->analysis_id = 0;
	$myAnalysis->duplicate_from_id = $analysis_id;
	$myBul = new bulletinage($bul_id);
	// lien vers la notice chapeau
	$link_parent = "<a href=\"./catalog.php?categ=serials\">";
	$link_parent .= $msg[4010]."</a>";
	$link_parent .= "<img src=\"./images/d.gif\" align=\"middle\" hspace=\"5\">";
	$link_parent .= "<a href=\"./catalog.php?categ=serials&sub=view&serial_id=";
	$link_parent .= $myBul->bulletin_notice."\">".$myBul->tit1.'</a>';
	$link_parent .= "<img src=\"./images/d.gif\" align=\"middle\" hspace=\"5\">";
	$link_parent .= "<a href=\"./catalog.php?categ=serials&sub=bulletinage&action=view&bul_id=$bul_id\">";
	if ($myBul->bulletin_numero) $link_parent .= $myBul->bulletin_numero." ";
	if ($myBul->mention_date) $link_parent .= " (".$myBul->mention_date.") "; 
	$link_parent .= "[".$myBul->aff_date_date."]";  
	$link_parent .= "</a> <img src=\"./images/d.gif\" align=\"middle\" hspace=\"5\">";
	$link_parent .= "<h3>".$myAnalysis->analysis_tit1."</h3>";
	
	print pmb_bidi("<div class='row'><div class='perio-barre'>".$link_parent."</div></div><br />");
	
	print "<div class='row'>".$myAnalysis->analysis_form()."</div>";
	
}
?>