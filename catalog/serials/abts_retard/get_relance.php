<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// | creator : Yves PRATTER                                                   |
// +-------------------------------------------------+
// $Id: get_relance.php,v 1.1 2011-06-27 15:26:59 ngantier Exp $

$base_path="./../../..";                            
$base_auth = "";  
$base_title = "\$msg[demandes_menu_title]";
$base_noheader = 1;
$base_nobody   = 1;   
require_once ("$base_path/includes/init.inc.php"); 

require_once("$class_path/abts_pointage.class.php");

$abts= new abts_pointage();

$abts->relance_retard();


?>