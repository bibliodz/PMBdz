<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serie.tpl.php,v 1.2 2007-03-14 16:58:01 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// ce fichier contient des templates indiquant comment doit s'afficher un �diteur

if ( ! defined( 'SERIE_TMPL' ) ) {
  define( 'SERIE_TMPL', 1 );

//	----------------------------------
//	$serie_display : �cran d'info pour une s�rie
// !!id!!        identifiant de l'�diteur
// !!name!!      nom de l'�diteur

// level 2 : affichage g�n�ral
$serie_level2_display = "
<div class=serielevel2>
<h3>".sprintf($msg["serie_details_serie"],"!!name!!")."</h3>
</div>
";

} # fin de d�finition
