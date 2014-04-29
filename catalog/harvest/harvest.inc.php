<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: harvest.inc.php,v 1.1 2012-01-25 15:20:35 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/harvest_notice.class.php");
// functions particuli�res � ce module
$acces_m=1;
if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
	$dom_1= $ac->setDomain(1);
	$acces_m = $dom_1->getRights($PMBuserid,$id,8);
}

if ($acces_m==0) {
	error_message('', htmlentities($dom_1->getComment('mod_noti_error'), ENT_QUOTES, $charset), 1, '');
} else {

	$harv = new harvest_notice($notice_id,$harvest_id,$profil_id);			
	switch($action){
		case 'build':
			print "<h1>".$msg[harvest_notice_replace_title]."</h1>";
			
			$harv->get_notice_externe($notice_id);
		break;
		case 'record':
			$harv->record_notice($notice_id);
		break;
	
		default:
			print "<h1>".$msg[harvest_notice_replace_title]."</h1>";
			
			print $harv->get_form_sel();
		break;		
	}
}