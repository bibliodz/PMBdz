<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: fichier.php,v 1.3 2014-01-13 08:07:31 arenou Exp $


// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "FICHIER_AUTH";  
$base_title = "\$msg[onglet_fichier]";  
$prefix = "gestfic0";

if ((isset($_POST["dest"])) && ($_POST["dest"]=="TABLEAU")) {
	$base_noheader=1;
}

require_once ("$base_path/includes/init.inc.php");  
// modules propres � demandes.php ou � ses sous-modules
require("$include_path/templates/fichier.tpl.php");


// cr�ation de la page
switch($dest) {
	case "TABLEAU":
	
		break;
	case "TABLEAUHTML":
		header("Content-Type: application/download\n");
		header("Content-Disposition: atttachement; filename=\"tableau.html\"");
		print "<html><head>" .
		'<meta http-equiv=Content-Type content="text/html; charset='.$charset.'" />'.
		"</head><body>";
		echo "<h1>".htmlentities($msg['onglet_fichier'].$msg[1003].$msg[1001],ENT_QUOTES,$charset)."</h1>";  
		break;
	default:
        print "<div id='att' style='z-Index:1000'></div>";
  		print $menu_bar;
		print $extra;
		print $extra2;
		print $extra_info;
		if($use_shortcuts) {
			include("$include_path/shortcuts/circ.sht");
		}
		echo window_title($database_window_title.$msg['onglet_fichier'].$msg[1003].$msg[1001]);
		print $fichier_layout;
		break;
}



switch($categ){
	case 'consult':
		include("$base_path/fichier/fichier_consult.inc.php");
		break;
	case 'saisie':
		include("$base_path/fichier/fichier_saisie.inc.php");
		break;
	case 'panier':
		include("$base_path/fichier/fichier_panier.inc.php");
		break;
	case 'gerer':
		include("$base_path/fichier/fichier_gestion.inc.php");
		break;
	default:
		include("$include_path/messages/help/$lang/module_fichier.txt");	
		break;
}

switch($dest) {
	case "TABLEAU":
	
		break;
	case "TABLEAUHTML":
		print $footer;
		print "</body>" ;
		break;
	default:
		print $fichier_layout_end;
		// pied de page
		print $footer;
		print "</body>" ;
		break;
}

// deconnection MYSql
mysql_close($dbh);