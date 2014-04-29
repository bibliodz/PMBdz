<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: group_main.inc.php,v 1.8 2013-10-24 08:24:12 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/group.class.php");
require_once("$include_path/templates/group.tpl.php");

print pmb_bidi($group_header);
$group_search = str_replace("!!group_query!!", htmlentities(stripslashes($group_query),ENT_QUOTES, $charset), $group_search );

switch($action) {
	case 'create':
		// cr�ation d'un groupe
		$group = new group(0);
		print $group->form();
		break;
	case 'modify':
		// modification d'un groupe
		if($groupID) {
			$group = new group($groupID);
			print $group->form();
			}
		break;
	case 'update':
		require_once("./circ/groups/update_group.inc.php");
		break;
	case 'addmember':
		// ajout d'un membre
		if($groupID && $memberID) require_once('./circ/groups/addmember.inc.php');
		break;
	case 'delmember':
		// suppression d'un membre
		if($groupID && $memberID) require_once('./circ/groups/delmember.inc.php');
		break;
	case 'delgroup':
		// suppression d'un group
		require_once('./circ/groups/del_group.inc.php');
		break;
	case 'listgroups':
		// affichage r�sultat recherche
		require_once("./circ/groups/list_groups.inc.php");
		break;
	case 'showgroup':
		// affichage des membres d'un groupe
		if ($groupID) require_once('./circ/groups/show_group.inc.php');
		break;
	case 'prolonggroup':
		// prolonger l'abonnement des membres d'un groupe
		if ($groupID) require_once('./circ/groups/prolong_group.inc.php');
		break;
	default:
		// action par d�faut : affichage form de recherche
		print pmb_bidi($group_search);
		break;
	}
print $group_footer;
