<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: categ_update.inc.php,v 1.21 2013-02-20 14:35:53 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// si tout est OK, on a les variables suivantes � exploiter :
// $id			id de la cat�gorie (0 si nouvelle)
// $category_libelle	libell� de la cat�gorie
// $category_comment	commentaire de la cat�gorie
// $category_parent_id	id de la cat�gorie parent
// $category_parent	libell� de la cat�gorie parent
// 		note : peut �tre vide si l'utilisateur a vid� le champ -> suppression du parent dans ce cas
// $category_voir_id	id de la forme retenue
// $category_voir	libelle de la forme retenue
// 		m�me remarque que pour $category_parent

require_once("$class_path/category.class.php");
require_once("$class_path/thesaurus.class.php");
require_once("$class_path/categories.class.php");
require_once("$class_path/XMLlist.class.php");
require_once("$class_path/aut_pperso.class.php");

if (noeuds::isRacine($id)) {
	error_form_message($msg['categ_forb']);
	exit();		
}

if(!strlen($category_parent)) $category_parent_id = 0;
if(!strlen($category_voir)) $category_voir_id = 0;

if ($id && ($category_parent_id==$id || $category_voir_id==$id)) {
	error_form_message($msg["categ_update_error_parent_see"]);
	exit ;
}

//recuperation de la table des langues
$langages = new XMLlist("$include_path/messages/languages.xml", 1);
$langages->analyser();
$lg = $langages->table;

//recuperation du thesaurus session 
$id_thes = thesaurus::getSessionThesaurusId();
$thes = new thesaurus($id_thes);	

// libelle langue defaut thesaurus non renseigne
if ( (trim($category_libelle[$thes->langue_defaut])) == '' ) {
	error_form_message($msg["thes_libelle_categ_ref_manquant"].'\n('.$lg[$thes->langue_defaut].')');
	exit ;	
}

//V�rification de l'unicit� du num�ro d'autorit�
$num_aut=trim(stripslashes($num_aut));

if ($num_aut && !noeuds::isUnique($id_thes, $num_aut,$id) ) {
	error_form_message($msg['categ_num_aut_not_unique']);
	exit;
}

//Si pas de parent, le parent est le noeud racine du thesaurus
if (!$category_parent_id) $category_parent_id = $thes->num_noeud_racine;

//traitement noeud
if($id) {
	//noeud existant
	$noeud = new noeuds($id);
	if (!noeuds::isProtected($id)) {
		$noeud->num_parent = $category_parent_id;
		$noeud->num_renvoi_voir = $category_voir_id;
		$noeud->authority_import_denied = $authority_import_denied;
		$noeud->not_use_in_indexation = $not_use_in_indexation;
		$noeud->autorite=$num_aut;
		$noeud->save();
	}
} else {
	//noeud a creer
	$noeud = new noeuds();
	$noeud->num_parent = $category_parent_id;
	$noeud->num_renvoi_voir = $category_voir_id;
	$noeud->autorite=$num_aut;
	$noeud->num_thesaurus = $thes->id_thesaurus;
	$noeud->authority_import_denied = $authority_import_denied;
	$noeud->not_use_in_indexation = $not_use_in_indexation;
	$noeud->save();
	$id = $noeud->id_noeud;
}
// liens entre autorit�s 
require_once("$class_path/aut_link.class.php");
$aut_link= new aut_link(AUT_TABLE_CATEG,$id);
$aut_link->save_form();

$aut_pperso= new aut_pperso("categ",$id);
$aut_pperso->save_form();

//traitement categories 
foreach($lg as $key=>$value) {

	if ( ($category_libelle[$key]) !== NULL ) {
		
		if ( ($category_libelle[$key] !== '')  || 
		 ( ($category_libelle[$key] === '') && (categories::exists($id, $key)) ) ){

			$cat = new categories($id, $key);
			$cat->libelle_categorie = stripslashes($category_libelle[$key]);	
			$cat->note_application = stripslashes($category_na[$key]);
			$cat->comment_public = stripslashes($category_cm[$key]);			
			$cat->index_categorie = strip_empty_words($category_libelle[$key]);
			$cat->save();
		 }
	}
}


if (!noeuds::isProtected($id)) {

	//Ajout des renvois "voir aussi"
	$requete="DELETE FROM voir_aussi WHERE num_noeud_orig=".$id;
	mysql_query($requete);
	for ($i=0; $i<$max_categ; $i++) {
		$categ_id="f_categ_id".$i;
		$categ_rec = "f_categ_rec".$i;
		if ($$categ_id && $$categ_id!=$id) {
			$requete="INSERT INTO voir_aussi (num_noeud_orig, num_noeud_dest, langue) VALUES ($id,".$$categ_id.",'".$thes->langue_defaut."' )";
			@mysql_query($requete);
			if ($$categ_rec) {
				$requete="INSERT INTO voir_aussi (num_noeud_orig, num_noeud_dest, langue) VALUES (".$$categ_id.",".$id.",'".$thes->langue_defaut."' )";				
			} else {
				$requete="DELETE from voir_aussi where num_noeud_dest = '".$id."' and num_noeud_orig = '".$$categ_id."'	";
			}
			@mysql_query($requete);
	
		}
	}
}

include('./autorites/subjects/default.inc.php');
