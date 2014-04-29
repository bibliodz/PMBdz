<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: series.inc.php,v 1.10 2011-06-13 14:39:19 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// on a besoin des templates s�ries
include("$include_path/templates/series.tpl.php");

// la classe de gestion des s�ries
require_once("$class_path/serie.class.php");

print "<h1>".$msg[140]."&nbsp;: ". $msg[333]."</h1>";

switch($sub) {
	case 'reach':
		include('./autorites/series/series_list.inc.php');
		break;
	case 'delete':
		$serie = new serie($id);
		$sup_result = $serie->delete();
		if(!$sup_result)
			include('./autorites/series/series_list.inc.php');
		else {
			error_message($msg[132], $sup_result, 1, "./autorites.php?categ=series&sub=serie_form&id=$id");
		}
		break;
	case 'replace':
		if(!$n_serie_id) {
			$serie = new serie($id);
			$serie->replace_form();
		} else {
			// routine de remplacement
			$serie = new serie($id);
			$rep_result = $serie->replace($n_serie_id,$aut_link_save);
			if (!$rep_result)
				include('./autorites/series/series_list.inc.php');
			else {
				error_message($msg[132], $rep_result, 1, "./autorites.php?categ=series&sub=serie_form&id=$id");
			}
		} 
		break;
	case 'update':
		// mettre � jour titre de s�rie id
		$serie = new serie($id);
		$serie->update($serie_nom);
/*		
		// maj de index_serie
		$index_serie=strip_empty_words($serie_nom);
		$rqt = "update notices set index_serie='".$index_serie."' where tparent_id='".$id."' ";
		mysql_query($rqt, $dbh) ;
*/		
		include('./autorites/series/series_list.inc.php');
		break;
	case 'serie_form':
	// cr�ation / modification d'un titre
		if(!$id) {
			// affichage du form pour cr�ation
			$serie = new serie(0);
			$serie->show_form();
		} else {
			// affichage du form pour modification
			$serie = new serie($id);
			$serie->show_form($id);
		}
		break;
	case 'serie_last':
		$last_param=1;
		$tri_param = "order by serie_id desc ";
		$limit_param = "limit 0, $pmb_nb_lastautorities ";
		$clef = "";
		$nbr_lignes = 0 ;
		include('./autorites/series/series_list.inc.php');
		break;
	default:
	// affichage du d�but de la liste (par d�faut)
		include('./autorites/series/series_list.inc.php');
		break;
}
?>