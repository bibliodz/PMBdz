<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: rubriques.inc.php,v 1.14 2014-01-03 14:03:47 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// la variable $caller, pass�e par l'URL, contient le nom du form appelant
$base_url = "./select.php?what=rubriques&caller=$caller&param1=$param1&param2=$param2&id_bibli=$id_bibli&id_exer=$id_exer&no_display=$no_display&bt_ajouter=$bt_ajouter";

// contenu popup s�lection fournisseur
require_once('./selectors/templates/sel_rubriques.tpl.php');
require_once($class_path.'/entites.class.php');
require_once($class_path.'/budgets.class.php');
require_once($class_path.'/rubriques.class.php');


// affichage du header
print $sel_header;

print $jscript;
show_results($dbh, $nbr_lignes, $page);


// affichage des membres de la page
function show_results($dbh, $nbr_lignes=0, $page=0) {
	
	global $nb_per_page;
	global $base_url;
	global $caller;
 	global $charset;
	global $msg;
	global $id_bibli, $id_exer;
	$nb_per_page = 10;
	// on r�cup�re le nombre de lignes qui vont bien
	$nbr_lignes = entites::countRubriquesFinales($id_bibli, $id_exer, true);

	if (!$page) $page=1;
	$debut = ($page-1)*$nb_per_page;

	if($nbr_lignes) {
		// on lance la vraie requ�te
		$res = entites::listRubriquesFinales($id_bibli, $id_exer, true, $debut, $nb_per_page);
		$id_bud = 0;

		print "<div class=\"row\"><table><tr><th>".htmlentities($msg['acquisition_rub'], ENT_QUOTES, $charset)."</th><th>".htmlentities($msg['acquisition_rub_sol'], ENT_QUOTES, $charset)."</th></tr>";
		
		while($row = mysql_fetch_object($res)) {
							
			$new_id_bud = $row->num_budget;
			if ($new_id_bud != $id_bud) {
				$id_bud = $new_id_bud;
				print pmb_bidi("<tr><td>".htmlentities($row->lib_bud, ENT_QUOTES, $charset)."</td>");
				if($row->type_budget) {
					$aff_glo = true;
					$mnt = $row->montant_global;	
					$cal = budgets::calcEngagement($id_bud);
					if($cal > $mnt) $sol=0; else $sol = $mnt-$cal;
					$sol = number_format($sol, 2,'.','' );
					if($cal > $mnt*($row->seuil_alerte/100)) $alert = true; else $alert = false;					
				} else {
					$aff_glo = false;
				}
				print "<td></td></tr>";
			}
			
			$tab_rub = rubriques::listAncetres($row->id_rubrique, true);
			
			$lib_rub = '';
			$lib_rub_no_html = "" ;
			foreach ($tab_rub as $dummykey=>$value) {
				$lib_rub.= htmlentities($value[1], ENT_QUOTES, $charset);
				$lib_rub_no_html.= $value[1];
				if($value[0] != $row->id_rubrique) $lib_rub.= ":";
			}
			if(!$aff_glo) {
				$mnt = $row->montant;
				$cal = rubriques::calcEngagement($row->id_rubrique);
				if($cal > $mnt) $sol=0; else $sol = $mnt-$cal;
				$sol = number_format($sol, 2,'.','' );
				if($cal >= $mnt*($row->seuil_alerte/100)) $alert = true; else $alert = false;
			}				
			if ($alert) $cl = "class='erreur' "; else $cl= '';
											 
			print "<tr><td><div class='child_tab'>";						 
			print pmb_bidi("
			<a href='#' onclick=\"set_parent('$caller', '$row->id_rubrique', '".htmlentities(addslashes($row->lib_bud.":".$lib_rub_no_html),ENT_QUOTES, $charset)."' )\" ><span ".$cl.">".$lib_rub."</span></a>
			</div></td><td style='text-align:right;'><span ".$cl.">".$sol."</span></td></tr>");
			

		}
		
		print "</table>";
		mysql_free_result($res);

		// affichage pagination
		print "<hr /><div align='center'>";
		$nav_bar = aff_pagination ($base_url, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		print $nav_bar;
		print "</div></div>";
	}
}

print $sel_footer;