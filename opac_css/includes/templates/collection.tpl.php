<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collection.tpl.php,v 1.7 2010-11-02 16:20:29 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// ce fichier contient des templates indiquant comment doit s'afficher une collection

if ( ! defined( 'COLLECTION_TMPL' ) ) {
  define( 'COLLECTION_TMPL', 1 );

//	----------------------------------
//	$collection_display : �cran d'info pour une collection
// Liste des variables statiques prises en charges :
// !!name!!      nom de la collection
// !!issn!!      num�ro ISSN de la collection
// !!publ!!      libell� de l'�diteur "parent" de la collection
// !!publ_isbd!! nom de l'�diteur principal, affichage isbd
// !!isbd!!      affichage isbd de la collection

// Liste des variables dynamiques prises en charges. Les affichages dynamiques sont cliquables le plus souvent
// !!publisher!! nom de l'�diteur principal
// !!subcolls!!  sous-collections



// level 2 : affichage g�n�ral
$collection_level2_display = "
<div class=collectionlevel2>
<h3>$msg[collection_tpl_coll] !!name!!</h3>
<ul>
  <li>$msg[collection_tpl_publisher] : !!publisher!!</li>
  <li>$msg[collection_tpl_issn] : !!issn!!</li>
</ul>
<div class=aut_comment>!!comment!!</div>
!!subcolls!!
</div>
";

$collection_level2_no_issn_info = "$msg[collection_tpl_no_issn]";

} # fin de d�finition
