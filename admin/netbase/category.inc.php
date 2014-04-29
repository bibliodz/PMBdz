<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: category.inc.php,v 1.17 2013-03-20 18:21:23 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/thesaurus.class.php");
require_once("$class_path/noeuds.class.php");
require_once("$class_path/categories.class.php");


//function process_categ($id_noeud) {
//	global $dbh;
//	
//	global $deleted;
//	global $lot;
//	
//	$res = noeuds::listChilds($id_noeud, 0);
//	$total = mysql_num_rows ($res);
//	if ($total) {
//		while ($row = mysql_fetch_object ($res)) {
//			// la categorie a des filles qu'on va traiter
//			process_categ ($row->id_noeud);
//		}
//		
//		// apr�s m�nage de ses filles, reste-t-il des filles ?
//		$total_filles = noeuds::hasChild($id_noeud);
//		
//		// categ utilis�e en renvoi voir ?
//		$total_see = noeuds::isTarget($id_noeud);
//		
//		// est-elle utilis�e ?
//		$iuse = noeuds::isUsedInNotices($id_noeud) + noeuds::isUsedinSeeALso($id_noeud);
//		
//		if(!$iuse && !$total_filles && !$total_see) {
//			$deleted++ ;
//			noeuds::delete($id_noeud);
//		}
//		
//	} else { // la cat�gorie n'a pas de fille on va la supprimer si possible
//			// regarder si categ utilis�e
//			$iuse = noeuds::isUsedInNotices($id_noeud) + noeuds::isUsedinSeeALso($id_noeud);
//			if(!$iuse) {
//				$deleted++ ;
//				noeuds::delete($id_noeud);
//			}
//	}
//			
//}

$v_state=urldecode($v_state);

if ($deleted=="") $deleted=0 ;

print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_suppr_categories"], ENT_QUOTES, $charset)."</h2>";

$list_thesaurus = thesaurus::getThesaurusList();
foreach($list_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
	$thes = new thesaurus($id_thesaurus);
	$noeud_rac =  $thes->num_noeud_racine;
	$r = noeuds::listChilds($noeud_rac, 0);
	while($row = mysql_fetch_object($r)){
		noeuds::process_categ($row->id_noeud);
	}
}	

//Nettoyage des informations d'autorit�s pour les sous collections
noeuds::delete_autority_sources();

$spec = $spec - CLEAN_CATEGORIES;
//TODO non repris >> Utilit� ???
//	$delete = mysql_query("delete from categories where categ_libelle='#deleted#'");

$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_suppr_categories"], ENT_QUOTES, $charset)." : ";
$v_state .= $deleted." ".htmlentities($msg["nettoyage_res_suppr_categories"], ENT_QUOTES, $charset);

$optn = noeuds::optimize();
$optc = categories::optimize();

print "<form class='form-$current_module' name='process_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
			</form>
			<script type=\"text/javascript\"><!--
				document.forms['process_state'].submit();
				-->
			</script>";
