<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// ce fichier contient des templates indiquant comment doit s'afficher une sous-collection

if ( ! defined( 'SUBCOLLECTION_TMPL' ) ) {
  define( 'SUBCOLLECTION_TMPL', 1 );

//	----------------------------------
//	$subcoll_display : �cran d'info pour une souscollection
// Liste des variables statiques prises en charges :
// !!name!!      nom de la souscollection
// !!issn!!      num�ro ISSN de la collection
// !!coll!!      libell� de la collection parente
// !!coll_isbd!! libell� de la collection parente, affichage isbd
// !!publ!!      libell� de l'�diteur parent
// !!publ_isbd!! libell� de l'�diteur parent, affichage isbd
// !!isbd!!      affichage isbd de la sous-collection

// Liste des variables dynamiques prises en charges. Les affichages dynamiques sont cliquables le plus souvent
// !!publisher!! nom de l'�diteur parent
// !!collection!! nom de la collection parente

// level 2 : affichage g�n�ral
$subcollection_level2_display = "
<div class=subcollectionlevel2>
<h3>".sprintf($msg["subcollection_details_subcollection"],"!!name!!")."</h3>
<ul>
  <li>".sprintf($msg["subcollection_details_author"],"!!publisher!!")."</li>
  <li>".sprintf($msg["subcollection_details_collection"],"!!collection!!")."</li>
  <li>".sprintf($msg["subcollection_details_issn"],"!!issn!!")."</li>
</ul>
<div class=aut_comment>!!comment!!</div>
</div>
";

$subcollection_level2_no_issn_info = $msg["subcollection_details_no_issn"];

} # fin de d�finition
