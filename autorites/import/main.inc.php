<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.2 2012-01-30 11:01:00 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/import_authorities.class.php");

switch ($sub){
	default :
		// gestion des autorit�s
		print "<h1>".$msg[140]."&nbsp;> ". $msg[authorities_import]."</h1>";
		$import_authorities = new import_authorities();
		print $import_authorities->show_form();
		break;
}