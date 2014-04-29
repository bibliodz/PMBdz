<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reception.inc.php,v 1.11 2013-04-04 10:07:58 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/resa.class.php");

// Titre de la fen�tre
print window_title($database_window_title.$msg[transferts_circ_menu_reception].$msg[1003].$msg[1001]);

//creation de l'objet transfert
$obj_transfert = new transfert();

switch ($action) {
	
	case "aff_recep":
		echo "<h1>" . $msg[transferts_circ_menu_titre] . " > " . $msg[transferts_circ_menu_reception] . "</h1>";
		
		$transferts_reception_liste_valide_reception = str_replace("!!liste_sections!!",do_liste_section(0),$transferts_reception_liste_valide_reception);
		
		$tmpString =  affiche_liste_valide(
								$transferts_reception_liste_valide_reception,
								$transferts_reception_liste_valide_reception_ligne,
								"SELECT num_notice, num_bulletin, id_transfert as val_id, " .
									"expl_cb as val_ex,lender_libelle, transferts.date_creation as val_date_creation, " .
									"date_visualisee as val_date_accepte, motif as val_motif, location_libelle as val_dest, " .
									"expl_section as val_section, empr_cb as val_empr " .
								"FROM transferts " .
									"INNER JOIN transferts_demande ON id_transfert=num_transfert " .
									"INNER JOIN exemplaires ON num_expl=expl_id " .
									"INNER JOIN lenders ON idlender=expl_owner " .
									"INNER JOIN docs_location ON num_location_dest=idlocation " .
									"LEFT JOIN resa ON resa_trans=id_resa " .
									"LEFT JOIN empr ON resa_idempr=id_empr " .
								"WHERE ".
									"id_transfert IN (!!liste_numeros!!) ".
									"AND etat_demande=2",
								"circ.php?categ=trans&sub=". $sub
								);
		//on r�cupere l'id du statut par d�faut du site de l'utilisateur
		$rqt = "SELECT transfert_statut_defaut FROM docs_location " .
				"INNER JOIN users ON idlocation=deflt_docs_location " .
				"WHERE userid=".$PMBuserid;
		$res = mysql_query($rqt);
		$statut_defaut = mysql_result($res,0);
		
		//on remplit le select avec la liste des statuts
		$tmpString = str_replace("!!liste_statuts!!", do_liste_statut($statut_defaut), $tmpString);

		echo $tmpString;

		break;
	
	case "recep":
		//on valide les receptions
		$obj_transfert->enregistre_reception($liste_transfert,$statut_reception,$liste_section,$info);
		$motif=$info[0]["motif"];
		//on affiche l'ecran principal
		$action = "";
		break;
}


if ($action=="") {

	$tmpString = do_cb_expl($msg[transferts_circ_menu_titre]." > ".$msg[transferts_circ_menu_reception],
								$msg[661], $msg[transferts_circ_reception_exemplaire], "./circ.php?categ=trans&sub=".$sub."&f_source=".$f_source."&nb_per_page=".$nb_per_page, 0,"recep");

	//on r�cupere l'id du statut par d�faut du site de l'utilisateur
	$rqt = "SELECT transfert_statut_defaut FROM docs_location " .
			"INNER JOIN users ON idlocation=deflt_docs_location " .
			"WHERE userid=".$PMBuserid;
	$res = mysql_query($rqt);
	$statut_defaut = mysql_result($res,0);
	
	//on remplit le select avec la liste des statuts
	$tmpString = str_replace("!!liste_statuts!!", do_liste_statut($statut_defaut), $tmpString);
	
	$liste_sel = "<option value=0>" . $msg["transferts_circ_reception_meme_section"] . "</option>" . do_liste_section(0);
	//on remplit le select avec la liste des sections
	$tmpString = str_replace("!!liste_sections!!", $liste_sel, $tmpString);
	
	echo $tmpString;

	if ($form_cb_expl != "") {
		//enregistrement de la reception
		$res_rcp = $obj_transfert->enregistre_reception_cb($form_cb_expl, $statut_reception, $section_reception,$info);
		$motif=$info[0]["motif"];
		if ($res_rcp==false) {
			// reception pas valide
			echo $transferts_reception_erreur;
		} else {
			// reception est faite
			echo str_replace("!!cb_expl!!", $form_cb_expl,$transferts_reception_OK);
			$resa=new reservation(0,0,0,$form_cb_expl);
			if(($empr_resa=$resa->get_empr_info_cb())){			
				$motif=$obj_transfert->get_motif($res_rcp);		
				echo str_replace("!!empr_link!!", $empr_resa,"<div class='row' align='center'><b>".$msg["transferts_circ_reception_accepte_resa"]."</b></div>");				
			}
			if($motif)echo "<div class='row' align='center'><b>".$motif."</b></div>";
		}
	}

	// les filtres � afficher
	$filtres = "&nbsp;".$msg["transferts_circ_reception_filtre_source"].str_replace("!!nom_liste!!","f_source",$transferts_liste_localisations_tous);
	$filtres = str_replace("!!liste_localisations!!", do_liste_localisation($f_source), $filtres);
	
	$req=	"FROM transferts " .
				"INNER JOIN transferts_demande ON id_transfert=num_transfert " .
				"INNER JOIN exemplaires ON num_expl=expl_id " .
				"INNER JOIN lenders ON idlender=expl_owner " .
				"INNER JOIN docs_location ON num_location_source=idlocation " .
				"LEFT JOIN resa ON resa_trans=id_resa " .
				"LEFT JOIN empr ON resa_idempr=id_empr " .
			"WHERE etat_transfert=0 " . //pas fini
				"AND etat_demande=2 " . //envoy�
				"AND num_location_dest=".$deflt_docs_location; //pour le site de l'utilisateur

	//on applique le filtre s�lectionner
	if ($f_source)
		$req .= " AND num_location_source=".$f_source;
	
	//on affiche la liste
	echo affiche_liste(
		$sub,
		$page,
		"SELECT ".
			"num_notice, num_bulletin, ".
			"id_transfert as val_id,lender_libelle, date_envoyee as val_date_envoi, " .
			"expl_cb as val_ex, transferts.date_creation as val_date_creation, " .
			"motif as val_motif, location_libelle as val_source, empr_cb as val_empr " ,
		$req,
		$nb_per_page,
		$transferts_reception_form_global,
		$transferts_reception_tableau_definition,
		$transferts_reception_tableau_ligne,
		$transferts_reception_boutons_action,
		$transferts_reception_pas_de_resultats,
		"",
		$filtres,
		"&f_source=".$f_source 
		);
}


?>