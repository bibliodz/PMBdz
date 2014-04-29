<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: category.class.php,v 1.22 2013-04-12 09:59:41 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'auteurs'
if ( ! defined( 'CATEGORY_CLASS' ) ) {
  define( 'CATEGORY_CLASS', 1 );
require_once("$class_path/thesaurus.class.php");
require_once("$class_path/acces.class.php");
		
class category {
	
// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------
var $id=0;
var $libelle='';
var $commentaire='';
var $catalog_form=''; // forme pour affichage complet
var $parent_id=0;
var $parent_libelle = '';
var $voir_id=0;
var $has_child=FALSE;
var $has_parent=FALSE;
var $path_table;	// tableau contenant le path �clat� (ids et libell�s)
var $associated_terms; // tableau des termes associ�s
var $is_under_tilde=0; // Savoir si c'est sous une cat�gorie qui commence par un ~
var $thes;		//le thesaurus d'appartenance
var $libelle_aff_complet = "";
var $commentaire_public = "";
var $not_use_in_indexation=0; //Savoir si l'on peut utiliser le terme en indexation

// ---------------------------------------------------------------
//		category($id) : constructeur
// ---------------------------------------------------------------
function category($id=0) {
	if($id) {
		// on cherche � atteindre une notice existante
		$this->id = $id;
		$this->is_under_tilde=0;
		$this->thes = thesaurus::getByEltId($id);
		$this->getData();
		} else {
			// la notice n'existe pas
			$this->id = 0;
			$this->is_under_tilde=0;
			$this->getData();
			}
	}

// ---------------------------------------------------------------
//		getData() : r�cup�ration des propri�t�s
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	global $lang;
	global $opac_categories_show_only_last ; // le param�tre pour afficher le chemin complet ou pas
	
	$anti_recurse=array();	
	if(!$this->id) return;
	$requete = "select id_noeud as categ_id, num_noeud, num_parent as categ_parent, libelle_categorie as categ_libelle,	num_renvoi_voir as categ_see, not_use_in_indexation, 	note_application as categ_comment, comment_public,	if(langue = '".$lang."',2, if(langue= '".$this->thes->langue_defaut."' ,1,0)) as p
		FROM noeuds, categories where id_noeud ='".$this->id."' 
		AND noeuds.id_noeud = categories.num_noeud 
		order by p desc limit 1";

	$result = @mysql_query($requete, $dbh);
	if(!mysql_num_rows($result)) return;
	
	$data = mysql_fetch_object($result);
	$this->id = $data->categ_id;		
	$this->libelle = $data->categ_libelle;
	if(preg_match("#^~#",$this->libelle)){
		$this->is_under_tilde=1;
	}
	$this->commentaire = $data->categ_comment;
	$this->parent_id = $data->categ_parent;
	$this->voir_id = $data->categ_see;
	$this->not_use_in_indexation = $data->not_use_in_indexation;
	$this->commentaire_public = $data->comment_public;
	//$anti_recurse[$this->voir_id]=1;
	if($this->parent_id ) $this->has_parent = TRUE;

	$requete = "SELECT id_noeud as categ_id FROM noeuds WHERE num_parent='".$this->id."' ";
	$result = @mysql_query($requete, $dbh);
	if(mysql_num_rows($result)) $this->has_child = TRUE;

	// constitution du chemin
	$anti_recurse[$this->id]=1;
	$this->path_table=array();
	if ($this->has_parent) {
		$id_parent=$this->parent_id;
		do {
			$requete = "select id_noeud as categ_id, num_noeud, num_parent as categ_parent, libelle_categorie as categ_libelle,	num_renvoi_voir as categ_see, note_application as categ_comment,if(langue = '".$lang."',2, if(langue= '".$this->thes->langue_defaut."' ,1,0)) as p
			FROM noeuds, categories where id_noeud ='".$id_parent."' 
			AND noeuds.id_noeud = categories.num_noeud 
			order by p desc limit 1";
			$result=@mysql_query($requete);
			if (mysql_num_rows($result)) {
				$parent = mysql_fetch_object($result);
				if(preg_match("#^~#",$parent->categ_libelle)){
					$this->is_under_tilde=1;
				}
				$anti_recurse[$parent->categ_id]=1;
				$this->path_table[] = array(
							'id' => $parent->categ_id,
							'libelle' => $parent->categ_libelle,
							'commentaire' => $parent->categ_comment);
				$id_parent=$parent->categ_parent;
			} else {
				break;
			}
		} while (($parent->categ_parent) &&(!$anti_recurse[$parent->categ_parent]));
	}
	
	// ceci remet le tableau dans l'ordre g�n�ral->particulier	
	$this->path_table = array_reverse($this->path_table);

	if ($opac_categories_show_only_last) {
		$this->catalog_form = $this->libelle;		
		// si notre cat�gorie a un parent, on initie la boucle en le r�cup�rant
		$requete = "select id_noeud as categ_id, num_noeud, num_parent as categ_parent, libelle_categorie as categ_libelle,	num_renvoi_voir as categ_see, note_application as categ_comment,if(langue = '".$lang."',2, if(langue= '".$this->thes->langue_defaut."' ,1,0)) as p
			FROM noeuds, categories where id_noeud ='".$parent->parent_id."' 
			AND noeuds.id_noeud = categories.num_noeud 
			order by p desc limit 1";
		
		$result_temp=@mysql_query($requete);
		if (mysql_num_rows($result_temp)) {
			$parent = mysql_fetch_object($result_temp);
			$this->parent_libelle = $parent->categ_libelle ;
		} else $this->parent_libelle ; 

	} else {
		if(sizeof($this->path_table)) {
			while(list($i, $l) = each($this->path_table)) {
					$temp_table[] = $l['libelle'];
			}
			$this->parent_libelle = join(':', $temp_table);
			$this->catalog_form = $this->parent_libelle.':'.$this->libelle;
		} else {
			$this->catalog_form = $this->libelle;
		}
	}
	// pour libell� complet mais sans le nom du th�saurus 
	$this->libelle_aff_complet = $this->catalog_form ;

	global $opac_thesaurus;
	if ($opac_thesaurus) $this->catalog_form="[".$this->thes->libelle_thesaurus."] ".$this->catalog_form;
	/* Ne sert plus??
	//Recherche des termes associ�s
	$requete = "select distinct voir_aussi.num_noeud_dest as categ_assoc_categassoc, id_noeud as categ_id, num_noeud, num_parent as categ_parent, libelle_categorie as categ_libelle,num_renvoi_voir as categ_see, note_application as categ_comment, if(categories.langue = '".$lang."',2, if(categories.langue= '".$this->thes->langue_defaut."' ,1,0)) as p
		FROM noeuds, categories, voir_aussi where id_noeud ='".$this->id."' 
		AND noeuds.id_noeud = categories.num_noeud 
		AND categories.num_noeud=voir_aussi.num_noeud_dest 
		AND voir_aussi.num_noeud_orig=id_noeud
		order by p desc limit 1";	

	$result=@mysql_query($requete,$dbh);
	while ($ta=mysql_fetch_object($result)) {
		print $requete;
		$this->associated_terms[] = array(
						'id' => $ta->categ_assoc_categassoc,
						'libelle' => $ta->categ_libelle,
						'commentaire' => $ta->categ_comment);
	}
	*/
}

function has_notices($id=0) {
	
	global $dbh;
	global $gestion_acces_active, $gestion_acces_empr_notice;
	global $class_path;
	if (is_object($this) && !$id) $id=$this->id;
	//droits d'acces emprunteur/notice
	$acces_j='';
	if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
		$ac= new acces();
		$dom_2= $ac->setDomain(2);
		$acces_j = $dom_2->getJoin($_SESSION['id_empr_session'],4,'notice_id');
	}
		
	if($acces_j) {
		$statut_j='';
		$statut_r='';
	} else {
		$statut_j=',notice_statut';
		$statut_r="and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
	}
	
	$query = "select count(1) from notices_categories,notices $acces_j $statut_j ";
	$query.= "where (notices_categories.num_noeud='".$id."' and notices_categories.notcateg_notice=notice_id) $statut_r ";
	$result = mysql_query($query, $dbh);
	return (mysql_result($result, 0, 0));

}

	
	
	
	
} # fin de d�finition de la classe category

} # fin de d�claration
