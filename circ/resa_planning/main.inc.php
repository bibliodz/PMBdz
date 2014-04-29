<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.9 2011-12-28 08:48:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/resa_planning.class.php");
require_once("$include_path/resa_planning_func.inc.php");
require_once("$include_path/templates/resa_planning.tpl.php");


switch ($categ) {

	case 'resa_planning' :
		print "<h1>".$msg['resa_menu']." &gt; ".$msg['resa_menu_planning']."</h1>";
		print $msg_a_pointer ;

		switch($resa_action) {
			
			case 'search_resa' : //Recherche pour r�servation

				print (aff_entete($id_empr));
				
				switch($mode) {
					case 1:
						// recherche cat�gorie/sujet
						print $menu_search[1];
						include('./circ/resa_planning/subjects/main.inc.php');
						break;
					case 5:
						// recherche par termes
						print $menu_search[6];
						include('./circ/resa_planning/terms/main.inc.php');
						break;
					case 2:
						// recherche �diteur/collection
						print $menu_search[2];
						include('./circ/resa_planning/publishers/main.inc.php');
						break;
					case 3:
						// acc�s aux paniers
						print $menu_search[3];
						include('./circ/resa_planning/cart.inc.php');
						break;
					case 6:
						// recherches avancees
						print $menu_search[6];
						include('./circ/resa_planning/extended/main.inc.php');
						break;	
					default :
						// recherche auteur/titre
						print $menu_search[0];
						$action_form = "./circ.php?categ=resa_planning&mode=0&id_empr=$id_empr&groupID=$groupID" ;
						include('./circ/resa_planning/authors/main.inc.php');
						break;
				}				
				break;

			case 'add_resa' : //Ajout d'une r�servation depuis une recherche catalogue

				print (aff_entete($id_empr));

				$display = new mono_display($id_notice, 6, '', 0, '', '', '', 0, 1, 1, 1);
				print ($display->result);
				print "<script type='text/javascript' src='./javascript/tablist.js'></script>\n";
				
				$form_resa_dates = str_replace('!!resa_date_debut!!', formatdate(today()), $form_resa_dates);
				$form_resa_dates = str_replace('!!resa_date_fin!!', formatdate(today()), $form_resa_dates);
				$form_resa_dates = str_replace('!!resa_deb!!', today(), $form_resa_dates);
				$form_resa_dates = str_replace('!!resa_fin!!', today(), $form_resa_dates);				
				print $form_resa_dates;
				
				//Affichage des r�servations planifi�es sur le document courant par le lecteur courant
				print doc_planning_list($id_empr, $id_notice);
							
				break;
			case 'add_resa_suite' :	//Enregistrement r�servation depuis fiche 

				//On v�rifie les dates
				$query="SELECT DATEDIFF('$resa_fin', '$resa_deb') AS diff";
				
				$resultatdate=mysql_query($query);
				if( mysql_numrows($resultatdate) ) {
					$resdate=mysql_fetch_object($resultatdate);
					if($resdate->diff > 0 ) {
						$r = new resa_planning();
						$r->resa_idempr = $id_empr;
						$r->resa_idnotice = $id_notice;
						$r->resa_date_debut = $resa_deb;
						$r->resa_date_fin = $resa_fin;
						$r->save();
						
						$q="select empr_cb from empr where id_empr='".$id_empr."' ";
						$r=mysql_result(mysql_query($q, $dbh), 0, 0);
						
						print "<script type='text/javascript'>document.location='./circ.php?categ=pret&form_cb=".rawurlencode($r)."'</script>";
					
					} else {
						
						print (aff_entete($id_empr));
	
						$display = new mono_display($id_notice, 6, '', 0, '', '', '', 0, 1, 1, 1);
						print ($display->result);
						print "<script type='text/javascript' src='./javascript/tablist.js'></script>\n";
						
						$form_resa_dates = str_replace('!!resa_date_debut!!', formatdate($resa_deb), $form_resa_dates);
						$form_resa_dates = str_replace('!!resa_date_fin!!', formatdate($resa_fin), $form_resa_dates);
						$form_resa_dates = str_replace('!!resa_deb!!', $resa_deb, $form_resa_dates);
						$form_resa_dates = str_replace('!!resa_fin!!', $resa_fin, $form_resa_dates);

						print $form_resa_dates;
						
						//Affichage des r�servations planifi�es sur le document courant par le lecteur courant
						print doc_planning_list($id_empr, $id_notice);
						
					}
				}
				break;
				
			case 'val_resa':	//Validation r�servation depuis liste

				for($i=0;$i<count($resa_check);$i++) {
		
					$key = $resa_check[$i];
					//On v�rifie les dates
					$tresa_date_debut = explode('-', extraitdate($resa_date_debut[$key]));
					if (strlen($tresa_date_debut[2])==1) $tresa_date_debut[2] = '0'.$tresa_date_debut[2];
					if (strlen($tresa_date_debut[1])==1) $tresa_date_debut[1] = '0'.$tresa_date_debut[1];
					$r_date_debut = implode('', $tresa_date_debut);
					
					$tresa_date_fin = explode('-', extraitdate($resa_date_fin[$key]));
					if (strlen($tresa_date_fin[2])==1) $tresa_date_fin[2] = '0'.$tresa_date_fin[2];
					if (strlen($tresa_date_fin[1])==1) $tresa_date_fin[1] = '0'.$tresa_date_fin[1];
					$r_date_fin = implode('', $tresa_date_fin); 	
					
					if ( (checkdate($tresa_date_debut[1], $tresa_date_debut[2], $tresa_date_debut[0])) 
							&& (checkdate($tresa_date_fin[1], $tresa_date_fin[2], $tresa_date_fin[0])) 
							&& (strlen($r_date_debut)==8) && (strlen($r_date_fin)==8) 
							&& ($r_date_debut < $r_date_fin) ) {
						$r = new resa_planning($key);
						$r->resa_date_debut=implode('-', $tresa_date_debut);
						$r->resa_date_fin=implode('-', $tresa_date_fin);
						$r->resa_validee='1';
						$r->save();
				
					}
				}
				print pmb_bidi(planning_list(0, 0, "",GESTION_INFO_GESTION)) ;
				break;
		
			case 'suppr_resa':	//Suppression r�servation depuis liste
		
				for($i=0;$i<count($resa_check);$i++) {
					$key = $resa_check[$i];
					resa_planning::delete($key);
				}	
				print pmb_bidi(planning_list(0, 0, "",GESTION_INFO_GESTION)) ;
				break;
			
			case 'conf_resa':

				for($i=0;$i<count($resa_check);$i++) {
					$key = $resa_check[$i];
					alert_empr_resa_planning ($resa_check[$i], $id_empr[$resa_check[$i]]) ;
				}
				print pmb_bidi(planning_list(0, 0, "",GESTION_INFO_GESTION)) ;
				break;
				
			case 'modif_resa':

				for($i=0;$i<count($resa_check);$i++) {
					$key = $resa_check[$i];
					$rqt_maj = "update resa_planning set resa_validee=0 where id_resa in (".$resa_check[$i].") and resa_confirmee=0 " ;
					if ($id_empr[$resa_check[$i]]) $rqt_maj .= " and resa_idempr=".$id_empr[$resa_check[$i]];
					mysql_query($rqt_maj, $dbh);
				}
				print pmb_bidi(planning_list(0, 0, "",GESTION_INFO_GESTION)) ;
				break;
		
			default :
				print pmb_bidi(planning_list(0, 0, "",GESTION_INFO_GESTION)) ;		
				break;	
		}
		break;
		
	case 'pret' :
		switch ($action) {
			case 'suppr_resa' :	//Suppression r�servation depuis fiche lecteur
				resa_planning::delete($id_resa);
				break;
				
			default :
				break;
		}
		break;

	default :
		break;
}
	

?>