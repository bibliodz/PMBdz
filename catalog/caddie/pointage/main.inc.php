<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.9 2013-07-15 13:37:31 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

switch ($moyen) {
	case 'raz':
		$catalog_layout = str_replace('<!--!!sous_menu_choisi!! -->', $msg["caddie_menu_pointage_raz"], $catalog_layout);
		print $catalog_layout ;
		include ("./catalog/caddie/pointage/raz.inc.php");
		break;
	case 'selection':
		$catalog_layout = str_replace('<!--!!sous_menu_choisi!! -->', $msg["caddie_menu_pointage_selection"], $catalog_layout);
		print $catalog_layout ;
		include ("./catalog/caddie/pointage/selection.inc.php");
		break;
	case 'douchette':
		$catalog_layout = str_replace('<!--!!sous_menu_choisi!! -->', $msg["caddie_menu_pointage_cb"], $catalog_layout);
		print $catalog_layout ;
		include ("./catalog/caddie/pointage/douchette.inc.php");
		break;
	case 'panier':
		$catalog_layout = str_replace('<!--!!sous_menu_choisi!! -->', $msg["caddie_menu_pointage_panier"], $catalog_layout);
		print $catalog_layout ;
		include ("./catalog/caddie/pointage/panier.inc.php");
		break;
	default:
		$catalog_layout = str_replace('<!--!!sous_menu_choisi!! -->', "?", $catalog_layout);
		print $catalog_layout ;
		print "<br /><br /><b>".$msg["caddie_select_pointage"]."</b>" ;
		break;
	}
