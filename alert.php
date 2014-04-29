<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alert.php,v 1.13 2013-02-14 14:32:53 dgoron Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "CIRCULATION_AUTH|CATALOGAGE_AUTH|AUTORITES_AUTH|ADMINISTRATION_AUTH|EDIT_AUTH";  
$base_title = "\$msg[5]";
require_once ("$base_path/includes/init.inc.php");  
if(!SESSrights) exit;
require_once("./alert/message.inc.php");
if ($current_alert=="circ") {
	require_once("./alert/resa.inc.php");
	require_once("./alert/expl_todo.inc.php");		
	require_once("./alert/empr.inc.php");
	//pour les alertes de transferts
	if ($pmb_transferts_actif && (SESSrights & TRANSFERTS_AUTH))
		require_once ("./alert/transferts.inc.php");
}
if ($current_alert=="catalog") {
	require_once("./alert/tag.inc.php");
	require_once("./alert/sugg.inc.php");
}

if ($current_alert=="acquisition") {
	require_once("./alert/sugg.inc.php");
}
if ($current_alert=="demandes") {
	require_once("./alert/demandes.inc.php");
}

print "<div id='contenu-frame'><div class='erreur'>$aff_alerte</div></div></body></html>" ;

mysql_close($dbh);

?>