<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: start.inc.php,v 1.11 2011-11-14 16:32:17 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// param�tres par d�faut de l'applic :
// ce syst�me cr�e des variables de nom type_param_sstype_param et de contenu valeur_param � partir de la table parametres

// prevents direct script access
if(preg_match('/start\.inc\.php/', $REQUEST_URI)) {
	include('./forbidden.inc.php'); forbidden();
}

/* param par d�faut */	
$requete_param = "SELECT type_param, sstype_param, valeur_param FROM parametres ";
$res_param = mysql_query($requete_param, $dbh);
while ($field_values = mysql_fetch_row ( $res_param )) {
	$field = $field_values[0]."_".$field_values[1] ;
	global $$field;
	$$field = $field_values[2];
}

/* param pmb_indexation_lang empty_words */
if (!$pmb_indexation_lang) {
	$requete_param = "SELECT valeur_param FROM parametres ";
	$requete_param .="WHERE type_param='pmb' and sstype_param='indexation_lang'";
	$res_param = mysql_query($requete_param, $dbh);
	if ($field_values = mysql_fetch_row ( $res_param )) {
		if ($field_values[0] != '')	$pmb_indexation_lang = $field_values[0];
	}
}
if (!$pmb_indexation_lang) $pmb_indexation_lang = $lang;

require_once($include_path."/marc_tables/".$pmb_indexation_lang."/empty_words");
require_once($class_path."/semantique.class.php");
//ajout des mots vides calcules
$add_empty_words=semantique::add_empty_words();
if ($add_empty_words) eval($add_empty_words);
