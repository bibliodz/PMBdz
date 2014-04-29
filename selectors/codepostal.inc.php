<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: codepostal.inc.php,v 1.6 2013-11-04 08:09:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// gestion d'un �l�ment � ne pas afficher
if (!$no_display) $no_display=0;

// la variable $caller, pass�e par l'URL, contient le nom du form appelant
$base_url = "./select.php?what=codepostal&caller=$caller&param1=$param1&param2=$param2&no_display=$no_display&bt_ajouter=$bt_ajouter&dyn=$dyn";

// contenu popup s�lection auteur
require('./selectors/templates/sel_codepostal.tpl.php');

// affichage du header
print $sel_header;

// traitement en entr�e des requ�tes utilisateur
if ($deb_rech) $f_user_input = $deb_rech ;

if($f_user_input=="" && $user_input=="") {
	$user_input='';
} else {
	// traitement de la saisie utilisateur
	if ($user_input) $f_user_input=$user_input;
	if (($f_user_input)&&(!$user_input)) $user_input=$f_user_input;
}

$sel_search_form = str_replace("!!deb_rech!!", htmlentities(stripslashes($f_user_input),ENT_QUOTES,$charset), $sel_search_form);
print $sel_search_form;
print $jscript;
show_results($dbh, $user_input, $nbr_lignes, $page, 0);

print $sel_footer;

// function d'affichage
function show_results($dbh, $user_input, $nbr_lignes=0, $page=0, $id = 0) {
	global $nb_per_page;
	global $base_url;
	global $caller;
	global $class_path;
	global $no_display;
 	global $charset;
 	global $msg ;
	global $dbh ;

	// on r�cup�re le nombre de lignes 
	if($user_input=="") {
		$requete = "SELECT empr_cp, empr_ville FROM empr group by empr_cp, empr_ville ";
	} else {
		$requete = "SELECT empr_cp, empr_ville FROM empr where empr_cp like '$user_input%' group by empr_cp, empr_ville ";
	}
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = mysql_num_rows($res);

	if(!$page) $page=1;
	$debut =($page-1)*$nb_per_page;
	
	if($nbr_lignes) {
		// on lance la vraie requ�te
		if($user_input=="") 
			$requete = "SELECT empr_cp, empr_ville, count(id_empr) as nbre FROM empr group by empr_cp, empr_ville ORDER BY empr_cp, empr_ville LIMIT $debut,$nb_per_page ";
		else 
			$requete = "SELECT empr_cp, empr_ville, count(id_empr) as nbre  FROM empr where empr_cp like '$user_input%' group by empr_cp, empr_ville ORDER BY empr_cp, empr_ville LIMIT $debut,$nb_per_page ";
		$res = mysql_query($requete, $dbh);
		while(($cp_ville=mysql_fetch_object($res))) {
			print "<div class='row'>";
			print pmb_bidi("<a href='#' onclick=\"set_parent('$caller', '".htmlentities(addslashes($cp_ville->empr_ville),ENT_QUOTES, $charset)."', '".htmlentities(addslashes($cp_ville->empr_cp),ENT_QUOTES, $charset)."')\">$cp_ville->empr_cp - $cp_ville->empr_ville : $cp_ville->nbre</a>");
			print "</div>";

		}
		mysql_free_result($res);

		// constitution des liens
		$nbepages = ceil($nbr_lignes/$nb_per_page);
		$suivante = $page+1;
		$precedente = $page-1;

		// affichage de la pagination
		print "<hr /><div align='center'>";
		$url_base = $base_url."&rech_regexp=$rech_regexp&user_input=".rawurlencode(stripslashes($user_input));
		$nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		print $nav_bar;
		print '</div>';
	}
}