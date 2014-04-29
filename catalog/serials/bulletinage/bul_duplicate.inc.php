<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bul_duplicate.inc.php,v 1.1 2011-11-29 13:17:25 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//verification des droits de modification notice
$acces_m=1;
if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
	$dom_1= $ac->setDomain(1);
	if (!$bul_id) {
		$acces_m = $dom_1->getRights($PMBuserid,$serial_id,8);
	} else {
		$acces_j = $dom_1->getJoin($PMBuserid,8,'bulletin_notice');
		$q= "select count(1) from bulletins $acces_j where bulletin_id=".$bul_id;
		$r = mysql_query($q, $dbh);
		if ($r) {
			if(mysql_result($r,0,0)==0) {
				$acces_m=0;
			}
		} else {
			$acces_m=0;
		}
	}
}

if ($acces_m==0) {

	if (!$bul_id) {
		error_message('', htmlentities($dom_1->getComment('mod_seri_error'), ENT_QUOTES, $charset), 1, '');
	} else {
		error_message('', htmlentities($dom_1->getComment('mod_bull_error'), ENT_QUOTES, $charset), 1, '');
	}

} else {
	// affichage d'un form pour duplication d'un p�riodique
	if(!$bul_id) {
		echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4005], $serial_header);
	} else {
		echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg["bull_duplicate"], $serial_header);
	}
		
	// on instancie le bulletinage
	$myBulletinage = new bulletinage($bul_id, $serial_id);
	$perio = new serial_display($myBulletinage->serial_id, 1);
	$myBulletinage->bulletin_id=0 ;
	
	$perio_header =  $perio->header;
		
	// titre g�n�ral du p�riodique
	print pmb_bidi("<div class='notice-perio'>
		<div class='row'>
			<h2>$perio_header</h2>
			</div>
		<div class='row'>
			$perio->isbd
			</div>
		</div>");
	
	    // affichage du form
	print "<div class=\"row\">".$myBulletinage->do_form().'</div>';
}	
?>