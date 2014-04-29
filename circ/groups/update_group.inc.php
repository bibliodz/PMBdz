<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: update_group.inc.php,v 1.7 2013-01-03 16:06:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if(!$libelle_resp) $respID = 0;

$group = new group($groupID);
$group->set($group_name, $respID, $lettre_rappel, $mail_rappel, $lettre_rappel_show_nomgroup);
$group->update();

if ($group->id && $group->libelle) {
    $groupID = $group->id;
    include('./circ/groups/show_group.inc.php');
} else {
	error_message($msg[919], $msg[923], 1, './circ.php?categ=groups');
}

?>

