<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: init.inc.php,v 1.6 2007-03-10 10:05:50 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if (substr(phpversion(), 0, 1) == "5") @ini_set("zend.ze1_compatibility_mode", "1");

//Chemins par d�faut de l'application (il faut initialiser $base_path relativement � l'endroit o� s'ex�cute le script)
$include_path=$base_path."/includes";
$class_path=$base_path."/classes";
?>