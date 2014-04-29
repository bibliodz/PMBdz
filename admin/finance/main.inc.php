<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.10 2013-12-24 13:08:33 ngantier Exp $
// Gestion financi�re

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

switch($sub) {
    case 'abts':
        $admin_layout = str_replace('!!menu_sous_rub!!', $msg["finance_abts"], $admin_layout);
        print $admin_layout;
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["finance_abts"].$msg[1003].$msg[1001]);
        include("./admin/finance/abts.inc.php");
        break;
    case 'prets':
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["finance_prets"]);
        include("./admin/finance/tarif_prets.inc.php");
        break;
    case 'amendes':
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["finance_amendes"]);
        include("./admin/finance/amendes.inc.php");
        break;
    case 'amendes_relance':
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["finance_amendes_relances"]);
        include("./admin/finance/amendes_relances.inc.php");
        break;
    case 'transactype':
    	$admin_layout = str_replace('!!menu_sous_rub!!', $msg["transaction_admin"], $admin_layout);
        print $admin_layout;
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["transaction_admin"]);
        include("./admin/finance/transaction.inc.php");
        break;
    case 'cashdesk':
    	$admin_layout = str_replace('!!menu_sous_rub!!', $msg["cashdesk_admin"], $admin_layout);
        print $admin_layout;
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["cashdesk_admin"]);
        include("./admin/finance/cashdesk.inc.php");
        break;
    case 'blocage':
    	$admin_layout = str_replace('!!menu_sous_rub!!', $msg["finance_blocage"], $admin_layout);
        print $admin_layout;
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]." - ".$msg["finance_blocage"].$msg[1003].$msg[1001]);
        include("./admin/finance/blocage.inc.php");
    	break;
    default:
        $admin_layout = str_replace('!!menu_sous_rub!!', "", $admin_layout);
        print $admin_layout;
        echo window_title($database_window_title.$msg["admin_gestion_financiere"]);
        include("$include_path/messages/help/$lang/admin_gestion_financiere.txt");
        break;
    }
