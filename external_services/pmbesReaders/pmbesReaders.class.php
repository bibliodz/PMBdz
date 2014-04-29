<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesReaders.class.php,v 1.1 2011-07-29 12:32:12 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

class pmbesReaders extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	/**
	 * $search => limit - exceed - running
	 * $empr_location_id => ex : (0 pour Toutes les localisations,..)
	 * $empr_statut_edit => ex : (1 pour actif,..)
	 * $sortby => nom des champs
	 */
	function listReadersSubscription($search='',$empr_location_id='',$empr_statut_edit='', $sortby=''){
		global $dbh, $msg, $pmb_relance_adhesion,$pmb_lecteurs_localises;
		global $deflt2docs_location;
							
		if ($search =='limit') {
			$restrict = " ((to_days(empr_date_expiration) - to_days(now()) ) <=  $pmb_relance_adhesion ) and empr_date_expiration >= now() ";
		} else if ($search =='exceed') {
			$restrict = " empr_date_expiration < now() ";
		} else if ($search =='running') {
			$restrict = " empr_date_expiration >= now() ";
		}
		
		// restriction localisation le cas �ch�ant
		if ($pmb_lecteurs_localises) {
			if ($empr_location_id=="") 
				$empr_location_id = $deflt2docs_location ;
			if ($empr_location_id!=0) 
				$restrict_localisation = " AND empr_location='$empr_location_id' ";
			else 
				$restrict_localisation = "";
		}

		// filtr� par un statut s�lectionn�
		if ($empr_statut_edit) {
			if ($empr_statut_edit!=0) 
				$restrict_statut = " AND empr_statut='$empr_statut_edit' ";
			else 
				$restrict_statut="";
		} 
		
		// on r�cup�re le nombre de lignes 
		if(!$nbr_lignes) {
			$requete = "SELECT COUNT(1) FROM empr, empr_statut where 1 ";
			$requete = $requete.$restrict_localisation.$restrict_statut." and ".$restrict;
			$requete .= " and empr_statut=idstatut";
			$res = mysql_query($requete, $dbh);
			$nbr_lignes = @mysql_result($res, 0, 0);
		}

		if($nbr_lignes) {
//			if ($statut_action=="modify") {
//				$requete="UPDATE empr set empr_statut='$empr_chang_statut_edit' where 1 ".$restrict_localisation.$restrict_statut." and ".$restrict;
//				$restrict_statut = " AND empr_statut='$empr_chang_statut_edit' ";
//				@mysql_query($requete);
//			} 
			// on lance la vraie requ�te
			$requete = "SELECT id_empr,empr_cb, empr_nom, empr_prenom, empr_adr1, empr_adr2, empr_ville, empr_mail,
				empr_year, date_format(empr_date_adhesion, '".$msg["format_date"]."') as aff_empr_date_adhesion, date_format(empr_date_expiration, '".$msg["format_date"]."') as aff_empr_date_expiration, statut_libelle  FROM empr, empr_statut ";
			$restrict_empr = " WHERE 1 ";
			$restrict_requete = $restrict_empr.$restrict_localisation.$restrict_statut." and ".$restrict;
			$requete .= $restrict_requete;
			$requete .= " and empr_statut=idstatut ";
			if (!isset($sortby))
				$sortby = 'empr_nom';
		
			$requete .= " ORDER BY $sortby ";

			$res = @mysql_query($requete, $dbh);
			
			while ($row = mysql_fetch_assoc($res)) {
				$result[] = array (
					"id_empr" => $row["id_empr"],
					"empr_cb" => $row["empr_cb"],
					"empr_nom" => $row["empr_nom"],
					"empr_prenom" => $row["empr_prenom"],
					"empr_adr1" => $row["empr_adr1"],
					"empr_adr2" => $row["empr_adr2"],
					"empr_ville" => $row["empr_ville"],
					"empr_mail" => $row["empr_mail"],
					"empr_year" => $row["empr_year"],
					"aff_empr_date_expiration" => $row["aff_empr_date_expiration"],
					"statut_libelle" => $row["statut_libelle"],
				);
			}
		}
		return $result;
	}

	function listGroupReaders() {
		global $dbh;
		
		$result=array();
		
		$requete = "SELECT id_groupe, libelle_groupe, resp_groupe, concat(IFNULL(empr_prenom,'') ,' ',IFNULL(empr_nom,'')) as resp_name, count( empr_id ) as nb_empr FROM groupe LEFT  JOIN empr_groupe ON groupe_id = id_groupe left join empr on resp_groupe = id_empr
		$clause group by id_groupe, libelle_groupe, resp_groupe, resp_name ORDER BY libelle_groupe LIMIT $debut,$nb_per_page ";
		$res = mysql_query($requete, $dbh);
		
		while($rgroup=mysql_fetch_assoc($res)) {
			$requete = "SELECT count( pret_idempr ) as nb_pret FROM empr_groupe,pret where groupe_id=$rgroup->id_groupe and empr_id = pret_idempr";
			$res_pret = mysql_query($requete, $dbh);
			if (mysql_num_rows($res_pret)) {
				$rpret=mysql_fetch_object($res_pret);
				$nb_pret=$rpret->nb_pret;	
			}
			
			$result[] = array (
				"libelle_groupe" => $rgroup->libelle_groupe,
				"resp_name" => $rgroup->resp_name,
				"nb_empr" => $rgroup->nb_empr,
				"nb_pret" => $nb_pret,
			);
		}
	}
	
	/**
	 * $search => limit - exceed - running
	 * $empr_location_id => ex : (0 pour Toutes les localisations,..)
	 * $empr_statut_edit => ex : (1 pour actif,..)
	 * $sortby => nom des champs
	 */
	function relanceReadersSubscription($tresults, $empr_location_id) {
		global $dbh,$fpdf,$ourPDF,$pmb_pdf_font;
		global $format_page,$marge_page_gauche, $marge_page_droite, $largeur_page, $fdp, $after_list, $limite_after_list, $before_list, $madame_monsieur, $nb_1ere_page, $nb_par_page, $taille_bloc_expl, $debut_expl_1er_page, $debut_expl_page, $before_recouvrement,$after_recouvrement,$texte;
		global $pmb_hide_biblioinfo_letter;
		global $empr_relance_adhesion;

		if (!$tresults)
			return "";
					
		// la formule de politesse du bas (le signataire)
		$var = "pdflettreadhesion_fdp";
		global $$var;
		eval ("\$fdp=\"".$$var."\";");
		
		// le "Madame, Monsieur," ou tout autre truc du genre "Cher adh�rent,"
		$var = "pdflettreadhesion_madame_monsieur";
		global $$var;
		eval ("\$madame_monsieur=\"".$$var."\";");
		
		// le texte
		$var = "pdflettreadhesion_texte";
		global $$var;
		eval ("\$texte=\"".$$var."\";");
		
		// la marge gauche des pages
		$var = "pdflettreadhesion_marge_page_gauche";
		global $$var;
		$marge_page_gauche = $$var;
		
		// la marge droite des pages
		$var = "pdflettreadhesion_marge_page_droite";
		global $$var;
		$marge_page_droite = $$var;
		
		// la largeur des pages
		$var = "pdflettreadhesion_largeur_page";
		global $$var;
		$largeur_page = $$var;
		
		// la hauteur des pages
		$var = "pdflettreadhesion_hauteur_page";
		global $$var;
		$hauteur_page = $$var;
		
		// le format des pages
		$var = "pdflettreadhesion_format_page";
		global $$var;
		$format_page = $$var;

		$taille_doc=array($largeur_page,$hauteur_page);
		
		$ourPDF = new $fpdf($format_page, 'mm', $taille_doc);
		$ourPDF->Open();

		
		foreach($tresults as $result) {
			$ourPDF->addPage();
			$ourPDF->SetMargins($marge_page_gauche,$marge_page_gauche);
			
			if(!$pmb_hide_biblioinfo_letter) {
				$this->infos_biblio($empr_location_id) ;
				biblio_info($marge_page_gauche, 10);
			}

			lecteur_adresse($result["id_empr"], ($marge_page_gauche+90), 45, $dbh, !$pmb_afficher_numero_lecteur_lettres);

			$ourPDF->SetXY ($marge_page_gauche,125);
			$ourPDF->setFont($pmb_pdf_font, '', 12);
			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $madame_monsieur, 0, 'L', 0);
	
			// mettre ici le texte 
			$empr_temp = new emprunteur($result["id_empr"], '', FALSE, 0);
			$texte = str_replace("!!date_fin_adhesion!!", $result["aff_empr_date_expiration"], $texte);
			$ourPDF->SetXY ($marge_page_gauche,135);
			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $texte, 0, 'J', 0);
	
			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $fdp, 0, 'R', 0);
		}
		
		return $ourPDF;
	}
//	/**
//	 * $search => limit - exceed - running
//	 * $empr_location_id => ex : (0 pour Toutes les localisations,..)
//	 * $empr_statut_edit => ex : (1 pour actif,..)
//	 * $sortby => nom des champs
//	 */
//	function relanceReadersSubscription($search='',$empr_location_id='',$empr_statut_edit='', $sortby='') {
//		global $dbh,$fpdf,$ourPDF,$pmb_pdf_font;
//		global $format_page,$marge_page_gauche, $marge_page_droite, $largeur_page, $fdp, $after_list, $limite_after_list, $before_list, $madame_monsieur, $nb_1ere_page, $nb_par_page, $taille_bloc_expl, $debut_expl_1er_page, $debut_expl_page, $before_recouvrement,$after_recouvrement,$texte;
//		global $pmb_hide_biblioinfo_letter;
//		global $empr_relance_adhesion;
//								
//		// la formule de politesse du bas (le signataire)
//		$var = "pdflettreadhesion_fdp";
//		global $$var;
//		eval ("\$fdp=\"".$$var."\";");
//		
//		// le "Madame, Monsieur," ou tout autre truc du genre "Cher adh�rent,"
//		$var = "pdflettreadhesion_madame_monsieur";
//		global $$var;
//		eval ("\$madame_monsieur=\"".$$var."\";");
//		
//		// le texte
//		$var = "pdflettreadhesion_texte";
//		global $$var;
//		eval ("\$texte=\"".$$var."\";");
//		
//		// la marge gauche des pages
//		$var = "pdflettreadhesion_marge_page_gauche";
//		global $$var;
//		$marge_page_gauche = $$var;
//		
//		// la marge droite des pages
//		$var = "pdflettreadhesion_marge_page_droite";
//		global $$var;
//		$marge_page_droite = $$var;
//		
//		// la largeur des pages
//		$var = "pdflettreadhesion_largeur_page";
//		global $$var;
//		$largeur_page = $$var;
//		
//		// la hauteur des pages
//		$var = "pdflettreadhesion_hauteur_page";
//		global $$var;
//		$hauteur_page = $$var;
//		
//		// le format des pages
//		$var = "pdflettreadhesion_format_page";
//		global $$var;
//		$format_page = $$var;
//
//		$taille_doc=array($largeur_page,$hauteur_page);
//		
//		$ourPDF = new $fpdf($format_page, 'mm', $taille_doc);
//		$ourPDF->Open();
//		$results = $this->listReadersSubscription($search,$empr_location_id,$empr_statut_edit, $sortby);
//		
//		foreach($results as $result) {
//			$ourPDF->addPage();
//			$ourPDF->SetMargins($marge_page_gauche,$marge_page_gauche);
//			
//			if(!$pmb_hide_biblioinfo_letter) $this->biblio_info($empr_location_id) ;
	
//			lecteur_adresse($result["id_empr"], ($marge_page_gauche+90), 45, $dbh, !$pmb_afficher_numero_lecteur_lettres);	
//			$ourPDF->SetXY ($marge_page_gauche,125);
//			$ourPDF->setFont($pmb_pdf_font, '', 12);
//			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $madame_monsieur, 0, 'L', 0);
	
//			// mettre ici le texte 
//			$empr_temp = new emprunteur($result["id_empr"], '', FALSE, 0);
//			$texte = str_replace("!!date_fin_adhesion!!", $result["aff_date_expiration"], $texte);
//			$ourPDF->SetXY ($marge_page_gauche,135);
//			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $texte, 0, 'J', 0);
//	
//			//
//			$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $fdp, 0, 'R', 0);
//		}
//		
//		return $ourPDF;
//	}
	
//	function listReadersEndSubscription(){
//		global $dbh, $msg, $pmb_relance_adhesion;
//		
//		$restrict = " ((to_days(empr_date_expiration) - to_days(now()) ) <=  $pmb_relance_adhesion ) and empr_date_expiration >= now() ";
//		
//		// requete � modifier en fonction de param�tres de restrictions
//		$requete = "SELECT empr.*, date_format(empr_date_adhesion, '".$msg["format_date"]."') as aff_empr_date_adhesion, date_format(empr_date_expiration, '".$msg["format_date"]."') as aff_empr_date_expiration, statut_libelle  FROM empr, empr_statut Where 1 ";
//		$requete .= " and ".$restrict;
//		$res = mysql_query($requete, $dbh);
//		
//		while ($row = mysql_fetch_assoc($res)) {
//			$result[] = array (
//				"empr_cb" => $row->empr_cb,
//				"empr_nom" => $row->empr_nom,
//				"empr_prenom" => $row->empr_prenom,
//				"empr_adr1" => $row->empr_adr1,
//				"empr_adr2" => $row->empr_adr2,
//				"empr_ville" => $row->empr_ville,
//				"empr_year" => $row->empr_year,
//				"aff_empr_date_expiration" => $row->aff_empr_date_expiration,
//				"statut_libelle" => $row->statut_libelle,
//			);
//		}
//		return $result;
//	}
		
//	function listReadersExceedSubscription() {
//		global $dbh, $msg;
//		
//		$restrict = " empr_date_expiration < now() ";
//		
//		// requete � modifier en fonction de param�tres de restrictions
//		$requete = "SELECT empr.*, date_format(empr_date_adhesion, '".$msg["format_date"]."') as aff_empr_date_adhesion, date_format(empr_date_expiration, '".$msg["format_date"]."') as aff_empr_date_expiration, statut_libelle  FROM empr, empr_statut Where 1 ";
//		$requete .= " and ".$restrict;
//		$res = mysql_query($requete, $dbh);
//		
//		while ($row = mysql_fetch_assoc($res)) {
//			$result[] = array (
//				"empr_cb" => $row->empr_cb,
//				"empr_nom" => $row->empr_nom,
//				"empr_prenom" => $row->empr_prenom,
//				"empr_adr1" => $row->empr_adr1,
//				"empr_adr2" => $row->empr_adr2,
//				"empr_ville" => $row->empr_ville,
//				"empr_year" => $row->empr_year,
//				"aff_empr_date_expiration" => $row->aff_empr_date_expiration,
//				"statut_libelle" => $row->statut_libelle,
//			);
//		}
//		return $result;
//	}
	
	function generatePdfReaderSubscription($id_empr,$empr_location_id) {
		global $dbh,$fpdf,$ourPDF,$pmb_pdf_font;
		global $format_page,$marge_page_gauche, $marge_page_droite, $largeur_page, $fdp, $after_list, $limite_after_list, $before_list, $madame_monsieur, $nb_1ere_page, $nb_par_page, $taille_bloc_expl, $debut_expl_1er_page, $debut_expl_page, $before_recouvrement,$after_recouvrement,$texte;
		global $pmb_hide_biblioinfo_letter;
		
		$this->infos_biblio($empr_location_id);
		
		// la formule de politesse du bas (le signataire)
		$var = "pdflettreadhesion_fdp";
		global $$var;
		eval ("\$fdp=\"".$$var."\";");
		
		// le "Madame, Monsieur," ou tout autre truc du genre "Cher adh�rent,"
		$var = "pdflettreadhesion_madame_monsieur";
		global $$var;
		eval ("\$madame_monsieur=\"".$$var."\";");
		
		// le texte
		$var = "pdflettreadhesion_texte";
		global $$var;
		eval ("\$texte=\"".$$var."\";");
		
		// la marge gauche des pages
		$var = "pdflettreadhesion_marge_page_gauche";
		global $$var;
		$marge_page_gauche = $$var;
		
		// la marge droite des pages
		$var = "pdflettreadhesion_marge_page_droite";
		global $$var;
		$marge_page_droite = $$var;
		
		// la largeur des pages
		$var = "pdflettreadhesion_largeur_page";
		global $$var;
		$largeur_page = $$var;
		
		// la hauteur des pages
		$var = "pdflettreadhesion_hauteur_page";
		global $$var;
		$hauteur_page = $$var;
		
		// le format des pages
		$var = "pdflettreadhesion_format_page";
		global $$var;
		$format_page = $$var;

		$taille_doc=array($largeur_page,$hauteur_page);
		
		$ourPDF = new $fpdf($format_page, 'mm', $taille_doc);
		$ourPDF->Open();
		
		$ourPDF->addPage();
		$ourPDF->SetMargins($marge_page_gauche,$marge_page_gauche);
		
		if(!$pmb_hide_biblioinfo_letter) biblio_info( $marge_page_gauche, 10) ;
		lecteur_adresse($id_empr, ($marge_page_gauche+90), 45, $dbh, !$pmb_afficher_numero_lecteur_lettres);

		$ourPDF->SetXY ($marge_page_gauche,125);
		$ourPDF->setFont($pmb_pdf_font, '', 12);
		$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $madame_monsieur, 0, 'L', 0);
	
		// mettre ici le texte 
		$empr_temp = new emprunteur($id_empr, '', FALSE, 0);
		$texte = str_replace("!!date_fin_adhesion!!", $empr_temp->aff_empr_date_expiration, $texte);

		$ourPDF->SetXY ($marge_page_gauche,135);
		$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $texte, 0, 'J', 0);

		$ourPDF->multiCell(($largeur_page - $marge_page_droite - $marge_page_gauche), 8, $fdp, 0, 'R', 0);
	
		return $ourPDF;
	}
	
	function generateMailReadersSubscription($id_empr,$empr_location_id) {
		global $mailrelanceadhesion_objet, $mailrelanceadhesion_fdp,$mailrelanceadhesion_madame_monsieur, $mailrelanceadhesion_texte;
		
		$this->infos_biblio($empr_location_id);
		
		// l'objet du mail
		$var = "mailrelanceadhesion_objet";
		eval ("\$objet=\"".$$var."\";");
		
		// la formule de politesse du bas (le signataire)
		$var = "mailrelanceadhesion_fdp";
		eval ("\$fdp=\"".$$var."\";");
		
		// le "Madame, Monsieur," ou tout autre truc du genre "Cher adh�rent,"
		$var = "mailrelanceadhesion_madame_monsieur";
		eval ("\$madame_monsieur=\"".$$var."\";");
		
		// le texte
		$var = "mailrelanceadhesion_texte";
		eval ("\$texte=\"".$$var."\";");
		
		// mettre ici le texte 
		$coords = new emprunteur($id_empr,'', FALSE, 0);
		if($madame_monsieur) $texte_mail = $madame_monsieur."\r\n\r\n";
		$texte_mail.=$texte."\r\n";
		if($fdp) $texte_mail.= $fdp."\r\n\r\n";
		$texte_mail.=mail_bloc_adresse() ;
	
		$texte_mail = str_replace("!!date_fin_adhesion!!", $coords->aff_empr_date_expiration, $texte_mail);
		
		//remplacement nom et prenom
		$texte_mail=str_replace("!!empr_name!!", $coords->nom,$texte_mail); 
		$texte_mail=str_replace("!!empr_first_name!!", $coords->prenom,$texte_mail); 
		
		$headers .= "Content-type: text/plain; charset=".$charset."\n";
		
		$res_envoi=mailpmb($coords->prenom." ".$coords->nom, $coords->mail, $objet, $texte_mail, $biblio_name, $biblio_email, $headers, "", $PMBuseremailbcc,1);
		
		return $res_envoi;
	}
	
	function infos_biblio($empr_location_id) {
		global $dbh,$pmb_lecteurs_localises;
		global $biblio_name, $biblio_adr1, $biblio_adr2, $biblio_cp, $biblio_town, $biblio_state, $biblio_country, $biblio_phone, $biblio_email,$biblio_website;
		global $biblio_logo;
		
		if ($pmb_lecteurs_localises) {
			if ($empr_location_id == '0') {
				global $deflt_docs_location;
				$empr_location_id = $deflt_docs_location;
			}
			$query = "select name, adr1,adr2,cp,town,state,country,phone,email,website,logo from docs_location where idlocation=".$empr_location_id;
			$res = mysql_query($query,$dbh);
			if (mysql_num_rows($res) == 1) {
				$row = mysql_fetch_object($res);
				$biblio_name = $row->name;
				$biblio_adr1 = $row->adr1;
				$biblio_adr2 = $row->adr2;
				$biblio_cp = $row->cp;
				$biblio_town = $row->town;
				$biblio_state = $row->state;
				$biblio_country = $row->country;
				$biblio_phone = $row->phone;
				$biblio_email = $row->email;
				$biblio_website = $row->website;
				$biblio_logo = $row->logo;
			}	
		} else {
			/*** Informations provenant des param�tres g�n�raux - on ne parle donc pas de multi-localisations **/
			// nom de la structure
			$var = "opac_biblio_name";
			global $$var;
			eval ("\$biblio_name=\"".$$var."\";");
		
			// logo de la structure
			$var = "opac_logo";
			global $$var;
			eval ("\$biblio_logo=\"".$$var."\";");
		
			// adresse principale
			$var = "opac_biblio_adr1";
			global $$var;
			eval ("\$biblio_adr1=\"".$$var."\";");
			
			// adresse secondaire
			$var = "opac_biblio_adr2";
			global $$var;
			eval ("\$biblio_adr2=\"".$$var."\";");
			
			// code postal
			$var = "opac_biblio_cp";
			global $$var;
			eval ("\$biblio_cp=\"".$$var."\";");
			
			// ville
			$var = "opac_biblio_town";
			global $$var;
			eval ("\$biblio_town=\"".$$var."\";");
			
			// Etat
			$var = "opac_biblio_state";
			global $$var;
			eval ("\$biblio_state=\"".$$var."\";");
			
			// pays
			$var = "opac_biblio_country";
			global $$var;
			eval ("\$biblio_country=\"".$$var."\";");
			
			// telephone
			$var = "opac_biblio_phone";
			global $$var;
			eval ("\$biblio_phone=\"".$$var."\";");
			
			// adresse mail
			$var = "opac_biblio_email";
			global $$var;
			eval ("\$biblio_email=\"".$$var."\";");
			
			//site web
			$var = "opac_biblio_website";
			global $$var;
			eval ("\$biblio_website=\"".$$var."\";");
		}
	}	
}




?>