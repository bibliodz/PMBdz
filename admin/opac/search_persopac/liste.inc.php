<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: liste.inc.php,v 1.2 2013-10-23 14:56:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// page de switch recherche notice

// inclusions principales
require_once("$class_path/search_persopac.class.php");

if (!$id && $id_search_persopac) $id = $id_search_persopac;
$search_p= new search_persopac($id);

switch($action) {	
	case "add":
		// affichage du formulaire de recherche perso, en cr�ation ou en modification => $id)
		print $search_p->add_search();	
	break;
	case "build":
		// affichage du formulaire de recherche perso, en cr�ation ou en modification => $id)
		print $search_p->add_search();	
	break;	
	case "form":
		// affichage du formulaire de recherche perso, en cr�ation ou en modification => $id)
		print $search_p->do_form();	
	break;
	case "save":
		// sauvegarde issu du formulaire
		$search_p->update_from_form();
		print $search_p->do_list();
	break;	
	case "delete":
		// effacement d'une recherche personalis�e, issu du formulaire
		$search_p->delete();
		print $search_p->do_list();
	break;
	default :
		// affiche liste des recherches personalis�e
		print $search_p->do_list();
	break;
}


