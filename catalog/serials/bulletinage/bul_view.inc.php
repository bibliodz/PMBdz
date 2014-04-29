<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bul_view.inc.php,v 1.10 2011-06-06 08:04:27 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// page de switch gestion du bulletinage périodiques

// mise à jour de l'entête de page
echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg[4011], $serial_header);

$form = show_bulletinage_info_catalogage($bul_id);

if($art_to_show) { 
	$form.=  "<script>document.location='#anchor_$art_to_show'</script>";
}
print $form;
?>