<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: perso.inc.php,v 1.4 2013-11-18 15:58:06 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/parametres_perso.class.php");

$option_visibilite=array();
$option_visibilite["multiple"]="none";
$option_visibilite["obligatoire"]="block";
$option_visibilite["search"]="none";
$option_visibilite["export"]="block";
$option_visibilite["exclusion"]="none";
$option_visibilite["opac_sort"]="none";

			
$p_perso=new parametres_perso("collstate","./admin.php?categ=collstate&sub=perso",$option_visibilite);

$p_perso->proceed();

?>