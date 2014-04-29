<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: autorites.inc.php,v 1.11 2013-08-14 15:23:29 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

echo window_title($database_window_title.$msg[132].$msg[1003].$msg[1001]);

switch($categ) {
	case 'series':
		include('./autorites/series/series.inc.php');
		break;
	case 'indexint':
		include('./autorites/indexint/indexint.inc.php');
		break;
	case 'auteurs':
		include('./autorites/authors/authors.inc.php');
		break;
	case 'categories':
		if (SESSrights & THESAURUS_AUTH) include('./autorites/subjects/categories.inc.php');
		break;
	case 'editeurs':
		include('./autorites/publishers/publishers.inc.php');
		break;
	case 'collections':
		include('./autorites/collections/collections.inc.php');
		break;
	case 'souscollections':
		include('./autorites/subcollections/subcollections.inc.php');
		break;
	case 'concepts':
		if (SESSrights & THESAURUS_AUTH) include('./autorites/concepts/concepts.inc.php');
		break;
	case 'semantique':
		if (SESSrights & THESAURUS_AUTH) include('./autorites/semantique/semantique_main.inc.php');
		break;
	case 'titres_uniformes':
		include('./autorites/titres_uniformes/titres_uniformes.inc.php');
		break;
	case 'import':
		include('./autorites/import/main.inc.php');
		break;
	default:
		include('./autorites/authors/authors.inc.php');
		break;
}
