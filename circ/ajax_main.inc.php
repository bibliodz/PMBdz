<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.9 2014-01-07 10:16:16 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//En fonction de $categ, il inclut les fichiers correspondants

switch($categ):
	case 'pret_ajax':
		include("./circ/pret_ajax/main.inc.php");
		break;
	case 'transferts':
		include("./circ/transferts/ajax/main.inc.php");
		break;			
	case 'print_pret':
		include("./circ/print_pret/main.inc.php");
		break;				
	case 'zebra_print_pret':
		include("./circ/print_pret/zebra_print_pret.inc.php");
		break;			
	case 'periocirc':
		include("./circ/serialcirc/serialcirc_ajax.inc.php");
		break;
	case 'resa_planning':
		include("./circ/resa_planning/resa_planning_ajax.inc.php");
		break;
	case 'empr' :
		include("./circ/empr/ajax/main.inc.php");
		break;
	case 'dashboard' :
		include("./dashboard/ajax_main.inc.php");
		break;
	default:
		ajax_http_send_error('400',$msg["ajax_commande_inconnue"]);
		break;		
endswitch;	
