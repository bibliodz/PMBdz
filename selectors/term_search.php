<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: term_search.php,v 1.9 2012-08-23 14:58:11 mbertin Exp $
//
// Recherche des termes correspondants � la saisie

$base_path="..";                            
$base_auth = ""; 
$base_title="Recherche par termes";

require_once ("$base_path/includes/init.inc.php");  
require_once ("$class_path/term_search.class.php");
require_once ("$class_path/thesaurus.class.php");


//R�cup�ration des param�tres du formulaire appellant
$base_query = "caller=$caller&p1=$p1&p2=$p2&no_display=$no_display&bt_ajouter=$bt_ajouter&parent=&dyn=$dyn&keep_tilde=$keep_tilde&callback=".$callback."&infield=".$infield;

//Page en cours d'affichage
$n_per_page=$thesaurus_categories_term_search_n_per_page;


$ts=new term_search("user_input","f_user_input",$n_per_page,$base_query,"term_show.php","term_search.php",$keep_tilde, $id_thes);

echo $ts->show_list_of_terms();

echo $footer;
?>
