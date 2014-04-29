<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ontology.inc.php,v 1.2 2013-09-04 08:52:12 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

/* $caller = Nom du formulaire appelant
 * $objs = type d'objet demandé
 * $code = id du champ recevant l'identifiant de la sélection dans le formulaire appelant
 * $label = id du champ recevant le libellé de la sélection dans le formulaire appelant
 * $cpt = id du champ recevant le compteur d'objets dans le formulaire appelant
 * $deb_rech = texte à rechercher 
 */

$racine=preg_replace("/_[0-9]+$/","",$label);//voir js sel_ontology.tpl.php

//$base_url = "./select.php?what=ontology&caller=$caller&objs=$objs&code=$code&label=$label&cpt=$cpt&deb_rech=$deb_rech";
$base_url = "./select.php?what=ontology&caller=$caller&objs=$objs&code=$code&label=$label&cpt=$cpt&infield=$infield&callback=$callback&dyn=$dyn";

// contenu popup selection
require('./selectors/templates/sel_ontology.tpl.php');

require_once("$class_path/rdf/ontology.class.php");
$op = new ontology_parser("$class_path/rdf/skos_pmb.rdf");
$sh = new skos_handler($op);

// affichage du header
$t_objs=explode(',',$objs);
$ontology_search_field="";
foreach ( $t_objs as $value ) {
	if($tmp2 = $sh->format($value)){
		if($ontology_search_field) $ontology_search_field.=", ";
       	$ontology_search_field.=$msg["ontology_".$tmp2];
	}
}
print str_replace("!!ontology_search_field!!",$ontology_search_field,$sel_header);

// traitement en entree des requetes utilisateur
if ($deb_rech) {
	$f_user_input = $deb_rech ;
}

if(isset($f_user_input) && $f_user_input==''){
	$f_user_input="*";
}

if($f_user_input=='' && $user_input=='') {
	$user_input='';
} else {
	// traitement de la saisie utilisateur
	if ($user_input) {
		$f_user_input=$user_input;
	}
	if (($f_user_input)&&(!$user_input)) {
		$user_input=$f_user_input;	
	}
}


// nombre de references par pages
$nb_per_page = 10;
if ($nb_per_page_a_select != "") {
	$nb_per_page = $nb_per_page_a_select ;
}

// affichage des membres de la page
$sel_search_form = str_replace("!!deb_rech!!", stripslashes($f_user_input), $sel_search_form);
print $sel_search_form;
print $jscript;


//show_results($dbh, $user_input, $nbr_lignes, $page);


$params['objects'] = $t_objs;
$params['user_input'] = $user_input;

$res = $sh->search_objects($params);
if(!is_array($res)){
	print $res;
	print "<br />";
}else{
	$nbr_lignes = count($res);
	if(!$page) $page=1;
	$debut =($page-1)*$nb_per_page;
	
	if($nbr_lignes){
		for ($i = $debut; $i < ($nb_per_page+$debut); $i++) {
			$label=$sh->get_object_label($res[$i]["subject_type"], $res[$i]["subject_uri"]);
			print pmb_bidi("
 				<a href='#' onclick=\"set_parent('".$caller."', '".rawurlencode($res[$i]["subject_uri"])."', '".htmlentities(addslashes($label),ENT_QUOTES, $charset)."','".$callback."')\">".
				htmlentities($label,ENT_QUOTES, $charset)."</a><br />");
		}
		
		// constitution des liens
		$nbepages = ceil($nbr_lignes/$nb_per_page);
		$suivante = $page+1;
		$precedente = $page-1;

		// affichage pagination
		print "<div class='row'>&nbsp;<hr /></div><div align='center'>";
		$url_base = $base_url."&user_input=".rawurlencode(stripslashes($user_input));
		$nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		print $nav_bar;
		print "</div>";
	}
	
}

print $sel_footer;

?>