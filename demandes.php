<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes.php,v 1.3 2014-01-13 08:07:15 arenou Exp $


// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "DEMANDES_AUTH";  
$base_title = "\$msg[demandes_menu_title]";    
require_once ("$base_path/includes/init.inc.php");  

// modules propres � demandes.php ou � ses sous-modules
require("$include_path/templates/demandes.tpl.php");
require("$include_path/templates/demandes_actions.tpl.php");
require("$include_path/templates/demandes_notes.tpl.php");
require_once($class_path."/liste_simple.class.php");

print "<div id='att' style='z-Index:1000'></div>";

print $menu_bar;
print $extra;
print $extra2;
print $extra_info;
if($use_shortcuts) {
	include("$include_path/shortcuts/circ.sht");
}

echo window_title($database_window_title.$msg[demandes_menu].$msg[1003].$msg[1001]);
print $demandes_layout;

$nb_themes = demandes_themes::get_qty();
$nb_types = demandes_types::get_qty();
if(!$nb_themes || !$nb_types) {
	//Pas de themes ou de types d�finis
	$error_msg.= htmlentities($msg["demandes_err_theme_type"],ENT_QUOTES, $charset)."<div class='row'></div>";	
	error_message($msg[321], $error_msg.htmlentities($msg["demandes_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=demandes');
} else {

	switch($categ){
		
		case 'gestion':
			include("./demandes/demandes.inc.php");
			break;
		case 'list' :
			include("./demandes/demandes_liste.inc.php");
			break;
		case 'action' :
			include("./demandes/demandes_actions.inc.php");
			break;
		case 'notes' :
			include("./demandes/demandes_notes.inc.php");
			break;		
		default :		
			include("$include_path/messages/help/$lang/demandes.txt");	
		break;
	}

}
print $demandes_layout_end;
// pied de page
print $footer;

// deconnection MYSql
mysql_close($dbh);
?>