<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: avis.inc.php,v 1.3 2013-01-16 15:27:49 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if ($opac_avis_allow==0) {
	ajax_http_send_response("0");
	exit;
}

switch($sub){
	case 'add':
		if (!$notice_id) {
			ajax_http_send_response("0");
			exit;
		}
		if (!$note) $note="NULL";
		$masque="@<[\/\!]*?[^<>]*?>@si";
		$commentaire = preg_replace($masque,'',$commentaire);
		if($charset != "utf-8") $commentaire=cp1252Toiso88591($commentaire);	
		$sql="insert into avis (num_empr,num_notice,note,sujet,commentaire) values ('$id_empr','$notice_id','$note','$sujet','".$commentaire."')";
		if (mysql_query($sql, $dbh)) {
			ajax_http_send_response("1");
		} else { 
			ajax_http_send_response("0");
			exit;
		}
		break;
	break;
}

?>