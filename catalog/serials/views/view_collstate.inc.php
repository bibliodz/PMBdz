<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: view_collstate.inc.php,v 1.2 2013-09-19 14:08:34 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path,$include_path;
global $tpl_collstate_liste;
require_once($class_path."/collstate.class.php");
$collstate = new collstate(0,$serial_id);
$filtre = new stdClass();
$filtre->location=$location;

if($pmb_etat_collections_localise && $location==0)
	$collstate->get_display_list($base_url,$filtre,$debut,$page,1);
else 	
	$collstate->get_display_list($base_url,$filtre,$debut,$page,0);	

$bulletins=$collstate->liste;
$pages_display=$collstate->pagination;

?>
