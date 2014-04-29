<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: subcollections.inc.php,v 1.11 2013-03-20 18:21:23 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/subcollection.class.php");

// la taille d'un paquet de notices
$lot = SUBCOLLECTION_PAQUET_SIZE; // defini dans ./params.inc.php

// taille de la jauge pour affichage
$jauge_size = GAUGE_SIZE;

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_suppr_subcollections"], ENT_QUOTES, $charset)."</h2>";

$query = mysql_query("SELECT sub_coll_id from sub_collections left join notices on sub_coll_id=subcoll_id where subcoll_id is null ");
$affected=0;
if($affected = mysql_num_rows($query)){
	while ($ligne = mysql_fetch_object($query)) {
		$subcoll = new subcollection($ligne->sub_coll_id);
		$subcoll->delete();
	}
}

//Nettoyage des informations d'autorit�s pour les sous collections
subcollection::delete_autority_sources();

$query = mysql_query("update notices left join sub_collections ON sub_coll_id=subcoll_id SET subcoll_id=0 WHERE sub_coll_id is null");

$spec = $spec - CLEAN_SUBCOLLECTIONS;
$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_suppr_subcollections"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_subcollections"], ENT_QUOTES, $charset);
$opt = mysql_query('OPTIMIZE TABLE sub_collections');
// mise � jour de l'affichage de la jauge
print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>
   		<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>
		<div align='center'>100%</div>";
print "
	<form class='form-$current_module' name='process_state' action='./clean.php?spec=$spec&start=0' method='post'>
		<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
		<input type='hidden' name='spec' value=\"$spec\">
		</form>
	<script type=\"text/javascript\"><!--
		document.forms['process_state'].submit();
		-->
	</script>";
