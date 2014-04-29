<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alter_v5.inc.php,v 1.344 2014-03-19 09:39:24 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

settype ($action,"string");

mysql_query("set names latin1 ", $dbh);

switch ($action) {
	case "lancement":
		switch ($version_pmb_bdd) {
			case "v4.94":
			case "v4.95":
			case "v4.96":
			case "v4.97":
				$maj_a_faire = "v5.00";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.00":
				$maj_a_faire = "v5.01";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.01":
				$maj_a_faire = "v5.02";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.02":
				$maj_a_faire = "v5.03";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.03":
			case "v5.04":
				$maj_a_faire = "v5.05";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.05":
				$maj_a_faire = "v5.06";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.06":
				$maj_a_faire = "v5.07";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.07":
				$maj_a_faire = "v5.08";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.08":
				$maj_a_faire = "v5.09";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.09":
				$maj_a_faire = "v5.10";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.10":
				$maj_a_faire = "v5.11";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.11":
				$maj_a_faire = "v5.12";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.12":
				$maj_a_faire = "v5.13";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.13":
				$maj_a_faire = "v5.14";
				echo "<strong><font color='#FF0000'>".$msg[1804]."$maj_a_faire !</font></strong><br />";
				echo form_relance ($maj_a_faire);
				break;
			case "v5.14":
				echo "<strong><font color='#FF0000'>".$msg[1805].$version_pmb_bdd." !</font></strong><br />";
				break;

			default:
				echo "<strong><font color='#FF0000'>".$msg[1806].$version_pmb_bdd." !</font></strong><br />";
				break;
			}
		break;

	case "v5.00":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='opac_view_activate' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'pmb', 'opac_view_activate', '0', 'Activer les vues OPAC:\n 0 : non activé \n 1 : activé', '', '0')";
			echo traite_rqt($rqt,"insert pmb_opac_view_activate='0' into parametres ");
		}

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='opac_view_activate' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			  		VALUES (NULL, 'opac', 'opac_view_activate', '0', 'Activer les vues OPAC:\n 0 : non activé \n 1 : activé', 'a_general', '0')";
			echo traite_rqt($rqt,"insert opac_opac_view_activate='0' into parametres ");
		}

		//Gestion des vues Opac
		$rqt = "CREATE TABLE if not exists opac_views (
			opac_view_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			opac_view_name VARCHAR( 255 ) NOT NULL default '',
			opac_view_query TEXT NOT NULL,
			opac_view_human_query TEXT NOT NULL,
			opac_view_param TEXT NOT NULL,
			opac_view_visible INT( 1 ) UNSIGNED NOT NULL default 0,
			opac_view_comment TEXT NOT NULL)";
		echo traite_rqt($rqt,"CREATE TABLE opac_views ") ;

		//Gestion des filtres de module ( pour vues Opac )
		$rqt = "CREATE TABLE if not exists opac_filters (
			opac_filter_view_num INT UNSIGNED NOT NULL default 0 ,
			opac_filter_path VARCHAR( 20 ) NOT NULL default '',
			opac_filter_param TEXT NOT NULL,
			PRIMARY KEY(opac_filter_view_num,opac_filter_path))";
		echo traite_rqt($rqt,"CREATE TABLE opac_filters ") ;

		//Gestion générique des subst de parametre ( pour vues Opac )
		$rqt = "CREATE TABLE if not exists param_subst (
			subst_module_param VARCHAR( 20 ) NOT NULL default '',
			subst_module_num INT( 2 ) UNSIGNED NOT NULL default 0,
			subst_type_param VARCHAR( 20 ) NOT NULL default '',
			subst_sstype_param VARCHAR( 255 ) NOT NULL default '',
			subst_valeur_param TEXT NOT NULL,
			subst_comment_param longtext NOT NULL,
			PRIMARY KEY(subst_module_param, subst_module_num, subst_type_param, subst_sstype_param))";
		echo traite_rqt($rqt,"CREATE TABLE param_subst ") ;

		$rqt = "CREATE TABLE if not exists opac_views_empr (
			emprview_view_num INT UNSIGNED NOT NULL default 0 ,
			emprview_empr_num INT UNSIGNED NOT NULL default 0 ,
		    emprview_default INT UNSIGNED NOT NULL default 0 ,
			PRIMARY KEY(emprview_view_num,emprview_empr_num))";
		echo traite_rqt($rqt,"CREATE TABLE opac_views_empr ") ;

		// Gestion des sur-localisations
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='sur_location_activate' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			  		VALUES (NULL, 'pmb', 'sur_location_activate', '0', 'Activer les sur-localisations:\n 0 : non activé \n 1 : activé', '', '0')";
			echo traite_rqt($rqt,"insert pmb_sur_location_activate='0' into parametres ");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='sur_location_activate' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			  		VALUES (NULL, 'opac', 'sur_location_activate', '0', 'Activer les sur-localisations:\n 0 : non activé \n 1 : activé', 'a_general', '0')";
			echo traite_rqt($rqt,"insert opac_sur_location_activate='0' into parametres ");
		}

		$rqt = "CREATE TABLE if not exists sur_location (
			surloc_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			surloc_libelle VARCHAR( 255 ) NOT NULL default '',
			surloc_pic VARCHAR( 255 ) NOT NULL default '',
			surloc_visible_opac tinyint( 1 ) UNSIGNED NOT NULL default 1,
			surloc_name VARCHAR( 255 ) NOT NULL default '',
			surloc_adr1 VARCHAR( 255 ) NOT NULL default '',
			surloc_adr2 VARCHAR( 255 ) NOT NULL default '',
			surloc_cp VARCHAR( 15 ) NOT NULL default '',
			surloc_town VARCHAR( 100 ) NOT NULL default '',
			surloc_state VARCHAR( 100 ) NOT NULL default '',
			surloc_country VARCHAR( 100 ) NOT NULL default '',
			surloc_phone VARCHAR( 100 ) NOT NULL default '',
			surloc_email VARCHAR( 100 ) NOT NULL default '',
			surloc_website VARCHAR( 100 ) NOT NULL default '',
			surloc_logo VARCHAR( 100 ) NOT NULL default '',
			surloc_comment TEXT NOT NULL,
			surloc_num_infopage INT( 6 ) UNSIGNED NOT NULL default 0,
			surloc_css_style VARCHAR( 100 ) NOT NULL default '')";
		echo traite_rqt($rqt,"CREATE TABLE sur_location ") ;

		$rqt = "ALTER TABLE docs_location ADD surloc_num INT NOT NULL default 0";
		echo traite_rqt($rqt,"alter table docs_location add surloc_num");

		$rqt = "ALTER TABLE docs_location ADD surloc_used tinyint( 1 ) NOT NULL default 0";
		echo traite_rqt($rqt,"alter table docs_location add surloc_used");

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='opac_view_class' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param) VALUES (0, 'pmb', 'opac_view_class', '', 'Nom de la classe substituant la class opac_view pour la personnalisation de la gestion des vues Opac','')";
			echo traite_rqt($rqt,"insert pmb_opac_view_class='' into parametres");
		}
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.01");
		break;

	case "v5.01":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+

		// Favicon, reporté de la 4.94 - ER
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='faviconurl' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param) VALUES (0, 'opac', 'faviconurl', '', 'URL du favicon, si vide favicon=celui de PMB','a_general')";
			echo traite_rqt($rqt,"insert opac_faviconurl='' into parametres");
		}

		//on précise si une source est interrogée directement en ajax dans l'OPAC
		$rqt = "ALTER TABLE connectors_sources ADD opac_affiliate_search INT NOT NULL default 0";
		echo traite_rqt($rqt,"alter table connectors_sources add opac_affiliate_search");

		// Activation des recherches affiliées dans les sources externes
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='allow_affiliate_search' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'allow_affiliate_search', '0', 'Activer les recherches affiliées en OPAC:\n 0 : non \n 1 : oui', 'c_recherche', '0')";
			echo traite_rqt($rqt,"insert opac_allow_affiliate_search='0' into parametres ");
		}

		$rqt = "ALTER TABLE users CHANGE explr_invisible explr_invisible TEXT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE users CHANGE explr_invisible explr_invisible TEXT NULL");
		$rqt = "ALTER TABLE users CHANGE explr_visible_mod explr_visible_mod TEXT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE users CHANGE explr_visible_mod explr_visible_mod TEXT NULL");
		$rqt = "ALTER TABLE users CHANGE explr_visible_unmod explr_visible_unmod TEXT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE users CHANGE explr_visible_unmod explr_visible_unmod TEXT NULL");

		//ajout table statuts de lignes d'actes
		$rqt = "CREATE TABLE lignes_actes_statuts (
			id_statut INT(3) NOT NULL AUTO_INCREMENT,
			libelle TEXT NOT NULL,
			relance INT(3) NOT NULL DEFAULT 0,
			PRIMARY KEY (id_statut)
			)  ";
		echo traite_rqt($rqt,"create table lignes_actes_statuts");

		$rqt = "CREATE TABLE lignes_actes_relances (
			num_ligne INT UNSIGNED NOT NULL ,
			date_relance DATE NOT NULL default '0000-00-00',
			type_ligne int(3) unsigned NOT NULL DEFAULT 0,
			num_acte int(8) unsigned NOT NULL DEFAULT 0,
			lig_ref int(15) unsigned NOT NULL DEFAULT 0,
			num_acquisition int(12) unsigned NOT NULL DEFAULT 0,
			num_rubrique int(8) unsigned NOT NULL DEFAULT 0,
			num_produit int(8) unsigned NOT NULL DEFAULT 0,
			num_type int(8) unsigned NOT NULL DEFAULT 0,
			libelle text NOT NULL,
			code varchar(255) NOT NULL DEFAULT '',
			prix float(8,2) NOT NULL DEFAULT 0,
			tva float(8,2) unsigned NOT NULL DEFAULT 0,
			nb int(5) unsigned NOT NULL DEFAULT 1,
			date_ech date NOT NULL DEFAULT '0000-00-00',
			date_cre date NOT NULL DEFAULT '0000-00-00',
			statut int(3) unsigned NOT NULL DEFAULT 1,
			remise float(8,2) NOT NULL DEFAULT 0,
			index_ligne text NOT NULL,
			ligne_ordre smallint(2) unsigned NOT NULL DEFAULT 0,
			debit_tva smallint(2) unsigned NOT NULL DEFAULT 0,
			commentaires_gestion text NOT NULL,
			commentaires_opac text NOT NULL,
			PRIMARY KEY (num_ligne, date_relance)
			) ";
		echo traite_rqt($rqt,"create table lignes_actes_relances");

		//ajout d'un statut de lignes d'actes par défaut
		if (mysql_num_rows(mysql_query("select 1 from lignes_actes_statuts where id_statut='1' "))==0) {
			$rqt = "INSERT INTO lignes_actes_statuts (id_statut,libelle,relance) VALUES (1 ,'Traitement normal', '1') ";
			echo traite_rqt($rqt,"insert default lignes_actes_statuts");
		}

		//raz des statuts de lignes d'actes
		$rqt = "UPDATE lignes_actes set statut='1' ";
		echo traite_rqt($rqt,"alter lignes_actes raz statut");

		//ajout d'un statut de ligne d'acte par défaut par utilisateur pour les devis
		$rqt = "ALTER TABLE users ADD deflt3lgstatdev int(3) not null default 1 ";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default lg state dev");

		//ajout d'un statut de ligne d'acte par défaut par utilisateur pour les commandes
		$rqt = "ALTER TABLE users ADD deflt3lgstatcde int(3) not null default 1 ";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default lg state cde");

		//ajout d'un commentaire de gestion pour les lignes d'actes
		$rqt = "ALTER TABLE lignes_actes ADD commentaires_gestion TEXT NOT NULL";
		echo traite_rqt($rqt,"alter table lignes_actes add commentaires_gestion");

		//ajout d'un commentaire OPAC pour les lignes d'actes
		$rqt = "ALTER TABLE lignes_actes ADD commentaires_opac TEXT NOT NULL";
		echo traite_rqt($rqt,"alter table lignes_actes add commentaires_opac");

		//ajout d'un nom (pour les commandes)
		$rqt = "ALTER TABLE actes ADD nom_acte VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter table actes add nom_acte");

		//Paramètres de mise en page des relances d'acquisitions
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_format_page' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_format_page','210x297','Largeur x Hauteur de la page en mm','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_format_page into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_orient_page' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_orient_page into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_marges_page' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_marges_page into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_logo' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_logo','10,10,20,20','Position du logo: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_logo into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_raison' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_raison','35,10,100,10,16','Position Raison sociale: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_raison into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_date' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_date','170,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_date into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_adr_rel' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_adr_rel','10,35,60,5,10','Position Adresse de relance: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_adr_rel into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_adr_fou' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_adr_fou','100,55,100,6,14','Position Adresse fournisseur: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_adr_fou into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_num_cli' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_num_cli','10,80,0,10,16','Position numéro de client: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_num_cli into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_num' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_num','10,0,10,16','Position numéro de commande/devis: Distance par rapport au bord gauche de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_num into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_text_size' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_text_size','10','Taille de la police texte','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_text_size into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_titre' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_titre','10,90,100,10,16','Position titre: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_titre into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_text_before' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_text_before','','Texte avant le tableau de relances','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_text_before into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_text_after' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_text_after','','Texte après le tableau de relances','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_text_after into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_tab_rel' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_tab_rel','5,10','Tableau de relances: Hauteur ligne,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_tab_rel into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_footer' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_footer into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pos_sign' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pos_sign','10,60,5,10','Position signature: Distance par rapport au bord gauche de la page, Largeur, Hauteur ligne,Taille police','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pos_sign into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_text_sign' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_text_sign','Le responsable de la bibliothèque.','Texte signature','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_text_sign into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_by_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_by_mail','1','Effectuer les relances par mail :\n 0 : non \n 1 : oui','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_by_mail into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_text_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_text_mail','Bonjour, \r\n\r\nVous trouverez ci-joint un état des commandes en cours.\r\n\r\nMerci de nous préciser par retour vos délais d\'envoi.\r\n\r\nCordialement,\r\n\r\nLe responsable de la bibliothèque.','Texte du mail','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_text_mail into parametres") ;
		}

		//ajout bulletinage avec document numérique
		$rqt = "ALTER TABLE abts_abts ADD abt_numeric int(1) not null default 0 ";
		echo traite_rqt($rqt,"ALTER TABLE abts_abts ADD abt_numeric ");

		//ajout dans les bannettes la possibilité de ne pas tenir compte du statut des notices
		$rqt = "ALTER TABLE bannettes ADD statut_not_account INT( 1 ) UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add statut_not_account");

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_perio_browser' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','show_perio_browser','0','Affichage du navigateur de périodiques en page d\'accueil OPAC.\n 0 : Non.\n 1 : Oui.','f_modules',0)" ;
			echo traite_rqt($rqt,"insert opac_show_perio_browser into parametres") ;
		}

		// Gestion des relances des périodiques
		$rqt = "CREATE TABLE perio_relance (
			rel_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			rel_abt_num int(10) unsigned NOT NULL DEFAULT 0,
			rel_date_parution date NOT NULL default '0000-00-00',
			rel_libelle_numero varchar(255) default NULL,
			rel_comment_gestion TEXT NOT NULL,
			rel_comment_opac TEXT NOT NULL ,
			rel_nb int unsigned NOT NULL DEFAULT 0,
			rel_date date NOT NULL default '0000-00-00',
			PRIMARY KEY  (rel_id) ) ";
		echo traite_rqt($rqt,"create table perio_relance ");

		//relances d'acquisitions en pdf/rtf
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_pdfrtf' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_pdfrtf','0','Envoi des relances en :\n 0 : pdf\n 1 : rtf','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_pdfrtf into parametres") ;
		}

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_onglet_perio_a2z' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','show_onglet_perio_a2z','0','Activer l\'onglet du navigateur de périodiques en OPAC.\n 0 : Non.\n 1 : Oui.','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_show_onglet_perio_a2z into parametres") ;
		}

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='avis_note_display_mode' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','avis_note_display_mode','1','Mode d\'affichage de la note pour les avis de notices.\n 0 : Note non visible.\n 1 : Affichage de la note sous la forme d\'étoiles.\n 2 : Affichage de la note sous la forme textuelle.\n 3 : Affichage de la note sous la forme textuelle et d\'étoiles.','a_general',0)" ;
			echo traite_rqt($rqt,"insert opac_avis_note_display_mode into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='avis_display_mode' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','avis_display_mode','0','Mode d\'affichage des avis de notices.\n 0 : Visible en lien à coté de l\'onglet Public/ISBD de la notice.\n 1 : Visible dans la notice.','a_general',0)" ;
			echo traite_rqt($rqt,"insert opac_avis_display_mode into parametres") ;
		}

		$rqt = "ALTER TABLE avis ADD avis_rank INT UNSIGNED NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"ALTER TABLE avis ADD avis_rank") ;

		//Module Gestionnaire de tâches
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='planificateur_allow' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'pmb', 'planificateur_allow', '0', 'Planificateur activé.\n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_planificateur_allow=0 into parameters");
		}

		$rqt = "CREATE TABLE taches_type (
				id_type_tache int(11) unsigned NOT NULL,
				parameters text NOT NULL,
				timeout int(11) NOT NULL default '5',
				histo_day int(11) NOT NULL default '7',
				histo_number int(11) NOT NULL default '3',
				PRIMARY KEY  (id_type_tache)
				)";
		echo traite_rqt($rqt, "CREATE TABLE taches_type ");

		// Création des tables nécessaires au gestionnaire de tâches
		$rqt="CREATE TABLE taches (
			id_tache int(11) unsigned auto_increment,
			num_planificateur int(11),
			start_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			end_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			status varchar(128),
			msg_statut blob,
			commande int(8) NOT NULL default 0,
			next_state int(8) NOT NULL default 0,
			msg_commande blob,
			indicat_progress int(3),
			rapport text,
			id_process int(8),
			primary key (id_tache));";
		echo traite_rqt($rqt,"CREATE TABLE taches ");

		$rqt="CREATE TABLE planificateur (
			id_planificateur int(11) unsigned auto_increment,
			num_type_tache int(11) NOT NULL,
			libelle_tache VARCHAR(255) NOT NULL,
			desc_tache VARCHAR(255),
			num_user int(11) NOT NULL,
			param text,
			statut tinyint(1) unsigned DEFAULT 0,
			rep_upload int(8),
			path_upload text,
			perio_heure varchar(28),
			perio_minute varchar(28) DEFAULT '01',
			perio_jour varchar(128),
			perio_mois varchar(128),
			calc_next_heure_deb varchar(28),
			calc_next_date_deb date,
			primary key (id_planificateur))";
		echo traite_rqt($rqt,"CREATE TABLE planificateur ");

		$rqt="CREATE TABLE taches_docnum (
			id_tache_docnum int(11) unsigned auto_increment,
			tache_docnum_nomfichier varchar(255) NOT NULL,
			tache_docnum_mimetype VARCHAR(255) NOT NULL,
			tache_docnum_data mediumblob NOT NULL,
			tache_docnum_extfichier varchar(20),
			tache_docnum_repertoire int(8),
			tache_docnum_path text NOT NULL,
			num_tache int(11) NOT NULL,
			primary key (id_tache_docnum))";
		echo traite_rqt($rqt,"CREATE TABLE taches_docnum ");

		//modification de la longueur du champ numero de la table actes
		$rqt = "ALTER TABLE actes MODIFY numero varchar(255) NOT NULL default '' ";
		echo traite_rqt($rqt,"alter table actes modify numero");

		//ajout d'un statut par défaut en réception pour les suggestions
		$rqt = "ALTER TABLE users ADD deflt3receptsugstat int(3) not null default 32 ";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default recept sug state");

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfrel_obj_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfrel_obj_mail','Etat des en-cours','Objet du mail','pdfrel',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfrel_obj_mail into parametres") ;
		}

		//ajout de paramètres pour l'envoi de commandes par mail
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfcde_by_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfcde_by_mail','1','Effectuer les envois de commandes par mail :\n 0 : non \n 1 : oui','pdfcde',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfcde_by_mail into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfcde_obj_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfcde_obj_mail','Commande','Objet du mail','pdfcde',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfcde_obj_mail into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfcde_text_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfcde_text_mail','Bonjour, \r\n\r\nVous trouverez ci-joint une commande à traiter.\r\n\r\nMerci de nous confirmer par retour vos délais d\'envoi.\r\n\r\nCordialement,\r\n\r\nLe responsable de la bibliothèque.','Texte du mail','pdfcde',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfcde_text_mail into parametres") ;
		}

		//ajout de paramètres pour l'envoi de devis par mail
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfdev_by_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfdev_by_mail','1','Effectuer les envois de demandes de devis par mail :\n 0 : non \n 1 : oui','pdfdev',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfdev_by_mail into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfdev_obj_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfdev_obj_mail','Demande de devis','Objet du mail','pdfdev',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfdev_obj_mail into parametres") ;
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'acquisition' and sstype_param='pdfdev_text_mail' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'acquisition','pdfdev_text_mail','Bonjour, \r\n\r\nVous trouverez ci-joint une demande de devis.\r\n\r\nCordialement,\r\n\r\nLe responsable de la bibliothèque.','Texte du mail','pdfdev',0)" ;
			echo traite_rqt($rqt,"insert acquisition_pdfcdev_text_mail into parametres") ;
		}

		// masquer la possibilité d'uploader les docnum en base
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='docnum_in_database_allow' "))==0){
			if (mysql_num_rows(mysql_query("select * from upload_repertoire "))==0) $upd_param_docnum_in_database_allow = 1;
			else $upd_param_docnum_in_database_allow=0;
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (0, 'pmb', 'docnum_in_database_allow', '$upd_param_docnum_in_database_allow', 'Autoriser le stockage de document numérique en base ? \n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_docnum_in_database_allow=$upd_param_docnum_in_database_allow into parameters <br><b>SET this parameter to 1 to (re)allow file storage in database !</b>");
		}

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='recherche_ajax_mode' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'recherche_ajax_mode', '1', 'Affichage accéléré des résultats de recherche: header uniquement, la suite est chargée lors du click sur le \"+\".\n 0: Inactif\n 1: Actif (par lot)\n 2: Actif (par notice)', 'c_recherche', '0')" ;
			echo traite_rqt($rqt,"insert opac_recherche_ajax_mode=1 into parametres") ;
		}

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='avis_note_display_mode' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'pmb','avis_note_display_mode','1','Mode d\'affichage de la note pour les avis de notices.\n 0 : Note non visible.\n 1 : Affichage de la note sous la forme d\'étoiles.\n 2 : Affichage de la note sous la forme textuelle.\n 3 : Affichage de la note sous la forme textuelle et d\'étoiles.','',0)" ;
			echo traite_rqt($rqt,"insert pmb_avis_note_display_mode into parametres") ;
		}

		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.02");
		break;

	case "v5.02":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+

		//Module CMS
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'cms' and sstype_param='active' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'cms', 'active', '0', 'Module \'Portail\' activé.\n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert cms_active=0 into parameters");
		}

		//langue d'indexation par défaut
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='indexation_lang' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (0, 'pmb', 'indexation_lang', '', 'Choix de la langue d\'indexation par défaut. (ex : fr_FR,en_UK,...,ar), si vide c\'est la langue de l\'interface du catalogueur qui est utilisée.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_indexation_lang into parameters");
		}

		//ajout du champ permettant la pré-selection du connecteur en OPAC
		$rqt = "ALTER TABLE connectors_sources ADD opac_selected int(3) unsigned not null default 0 ";
		echo traite_rqt($rqt,"ALTER TABLE connectors_sources ADD opac_selected");

		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='websubscribe_show_location' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'websubscribe_show_location', '0', 'Afficher la possibilité pour le lecteur de choisir sa localisation lors de son inscription en ligne.\n 0: Non\n 1: Oui', 'f_modules', '0')" ;
			echo traite_rqt($rqt,"insert opac_websubscribe_show_location=0 into parametres") ;
		}

		// CMS PMB
		//rubriques
		$rqt="create table if not exists cms_sections(
			id_section int unsigned not null auto_increment primary key,
			section_title varchar(255) not null default '',
			section_resume text not null,
			section_logo mediumblob not null,
			section_publication_state varchar(50) not null,
			section_start_date datetime,
			section_end_date datetime,
			section_num_parent int not null default 0,
			index i_cms_section_title(section_title),
			index i_cms_section_publication_state(section_publication_state),
			index i_cms_section_num_parent(section_num_parent)
			)";
		echo traite_rqt($rqt, "create table cms_sections");

		$rqt = "create table if not exists cms_sections_descriptors(
			num_section int not null default 0,
			num_noeud int not null default 0,
			section_descriptor_order int not null default 0,
			primary key (num_section,num_noeud)
			)";
		echo traite_rqt($rqt, "create table cms_sections_descriptors");

		$rqt="create table if not exists cms_articles(
			id_article int unsigned not null auto_increment primary key,
			article_title varchar(255) not null default '',
			article_resume text not null,
			article_contenu text not null,
			article_logo mediumblob not null,
			article_publication_state varchar(50) not null default '',
			article_start_date datetime,
			article_end_date datetime,
			num_section int not null default 0,
			index i_cms_article_title(article_title),
			index i_cms_article_publication_state(article_publication_state),
			index i_cms_article_num_parent(num_section)
			)";
		echo traite_rqt($rqt, "create table cms_articles");

		$rqt = "create table if not exists cms_articles_descriptors(
			num_article int not null default 0,
			num_noeud int not null default 0,
			article_descriptor_order int not null default 0,
			primary key (num_article,num_noeud)
			)";
		echo traite_rqt($rqt, "create table cms_articles_descriptors");


		$rqt = "create table if not exists cms_editorial_publications_states(
			id_publication_state int unsigned not null auto_increment primary key,
			editorial_publication_state_label varchar(255) not null default '',
			editorial_publication_state_opac_show int(1) not null default 0,
			editorial_publication_state_auth_opac_show int(1) not null default 0
			)";
		echo traite_rqt($rqt, "create table cms_editorial_publications_states");

		$rqt="create table if not exists cms_build (
			id_build int unsigned not null auto_increment primary key,
			build_obj varchar(255) not null default '',
			build_parent varchar(255) not null default '',
			build_child_after varchar(255) not null default '',
			build_css text not null
			)";
		echo traite_rqt($rqt, "create table cms_build");

		//paramétrage de la pondération des champs persos...
		// dans le notices
		$rqt = "alter table notices_custom add pond int not null default 100";
		echo traite_rqt($rqt,"alter table notices_custom add pond");
		//dans les exemplaires
		$rqt = "alter table expl_custom add pond int not null default 100";
		echo traite_rqt($rqt,"alter table expl_custom add pond ");
		//dans les états des collections
		$rqt = "alter table collstate_custom add pond int not null default 100";
		echo traite_rqt($rqt,"alter table collstate_custom add pond");
		//dans les lecteurs, pour rester homogène...
		$rqt = "alter table empr_custom add pond int not null default 100";
		echo traite_rqt($rqt,"alter table empr_custom add pond");

		//tri sur les états des collections en OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='collstate_order' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param) 
				VALUES (0, 'opac', 'collstate_order', 'archempla_libelle,collstate_cote','Ordre d\'affichage des états des collections, dans l\'ordre donné, séparé par des virgules : archempla_libelle,collstate_cote','e_aff_notice')";
			echo traite_rqt($rqt,"insert opac_collstate_order=archempla_libelle,collstate_cote into parametres");
		}

		//la pondération dans les fiches ne sert à rien mais pour rester homogène avec les autres champs persos...
		$rqt = "alter table gestfic0_custom add pond int not null default 100";
		echo traite_rqt($rqt,"alter table gestfic0_custom add pond");	

		//AR new search !
		@set_time_limit(0);
		flush();
		$rqt = "truncate table notices_mots_global_index";
		echo traite_rqt($rqt,"truncate table notices_mots_global_index");

		//Changement du type de code_champ dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index change code_champ code_champ int(3) not null default 0";
		echo traite_rqt($rqt,"alter table notices_mots_global_index change code_champ");	
		
		//ajout de code_ss_champ dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index add code_ss_champ int(3) not null default 0 after code_champ";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add code_ss_champ");
		
		//ajout de pond dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index add pond int(4) not null default 100";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add pond");	
		
		//ajout de position dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index add position int not null default 1";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add position");	
		
		//ajout de lang dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index add lang varchar(10) not null default ''";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add lang");	
		
		//changement de clé primaire
		$rqt = "alter table notices_mots_global_index drop primary key, add primary key(id_notice,code_champ,code_ss_champ,mot)";
		echo traite_rqt($rqt,"alter table notices_mots_global_index change primary key(id_notice,code_champ,code_ss_champ,mot");	
		
		//index
		$rqt = "alter table notices_mots_global_index drop index i_mot";
		echo traite_rqt($rqt,"alter table notices_mots_global_index drop index i_mot");	
		$rqt = "alter table notices_mots_global_index add index i_mot(mot)";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add index i_mot");	

		$rqt = "alter table notices_mots_global_index drop index i_id_mot";
		echo traite_rqt($rqt,"alter table notices_mots_global_index drop index i_id_mot");
		$rqt = "alter table notices_mots_global_index add index i_id_mot(id_notice,mot)";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add index i_id_mot");
		
		//une nouvelle table pour les recherches exactes...
		$rqt="create table if not exists notices_fields_global_index (
			id_notice mediumint(8) not null default 0,
			code_champ int(3) not null default 0,
			code_ss_champ int(3) not null default 0,
			ordre int(4) not null default 0,
			value text not null,
			pond int(4) not null default 100,
			lang varchar(10) not null default '',
			primary key(id_notice,code_champ,code_ss_champ,ordre),
			index i_value(value(300)),
			index i_id_value(id_notice,value(300))
			)";
		echo traite_rqt($rqt, "create table notices_fields_global_index");		
		
		$rqt = "create table if not exists search_cache (
			object_id varchar(255) not null default '',
			delete_on_date datetime not null default '0000-00-00 00:00:00',
			value mediumblob not null,
	 		PRIMARY KEY (object_id)
			)";
		echo traite_rqt($rqt, "create table search_cache");	
		
		// ajout d'un paramètre de tri par défaut
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='default_sort' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','default_sort','d_num_6,c_text_28','Tri par défaut des recherches OPAC.\nDe la forme, c_num_6 (c pour croissant, d pour décroissant, puis num ou text pour numérique ou texte et enfin l\'identifiant du champ (voir fichier xml sort.xml))','d_aff_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_default_sort into parametres") ;
		}
		flush();
		//AR /new search !
		
		//maj valeurs possibles pour empr_filter_rows
		$rqt = "update parametres set comment_param='Colonnes disponibles pour filtrer la liste des emprunteurs : \n v: ville\n l: localisation\n c: catégorie\n s: statut\n g: groupe\n y: année de naissance\n cp: code postal\n cs : code statistique\n #n : id des champs personnalisés' where type_param= 'empr' and sstype_param='filter_rows' ";
		echo traite_rqt($rqt,"update empr_filter_rows into parametres");
		
		//Précision affichage amendes
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='fine_precision' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion) VALUES (0, 'pmb', 'fine_precision', '2', 'Nombre de décimales pour l\'affichage des amendes',1)";
			echo traite_rqt($rqt,"insert fine_precision=2 into parametres");
		}
	
		//Rafraichissement des vues opac
		$rqt = "alter table opac_views add opac_view_last_gen datetime default null";
		echo traite_rqt($rqt,"alter table opac_views add opac_view_last_gen");
		$rqt = "alter table opac_views add opac_view_ttl int not null default 86400";
		echo traite_rqt($rqt,"alter table opac_views add opac_view_ttl");
		
		// paramétrage du cache en OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='search_cache_duration' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','search_cache_duration','600','Durée de validité (en secondes) du cache des recherches OPAC','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_search_cache_duration into parametres") ;
		}

		// ajout d'un paramètre utilisateur de statut par défaut en import (report de l'alter V4, modif tardive en 3.4)
		$rqt = "alter table users add deflt_integration_notice_statut int(6) not null default 1 after deflt_notice_statut";
		echo traite_rqt($rqt,"alter table users add deflt_integration_notice_statut");

		// Info de réindexation
		$rqt = " select 1 " ;
		echo traite_rqt($rqt,"<b><a href='".$base_path."/admin.php?categ=netbase' target=_blank>VOUS DEVEZ REINDEXER / YOU MUST REINDEX : Admin > Outils > Nettoyage de base</a></b> ") ;

		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.03");
		break;

	case "v5.03":
	case "v5.04":
	case "v5.05":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+

		//Type de document par défaut en création de périodique
		$rqt = "ALTER TABLE users ADD xmlta_doctype_serial varchar(2) NOT NULL DEFAULT '' after xmlta_doctype";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default xmlta_doctype_serial after xmlta_doctype");

		//Type de document par défaut en création de bulletin
		$rqt = "ALTER TABLE users ADD xmlta_doctype_bulletin varchar(2) NOT NULL DEFAULT '' after xmlta_doctype_serial";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default xmlta_doctype_bulletin after xmlta_doctype_serial");

		//Type de document par défaut en création d'article
		$rqt = "ALTER TABLE users ADD xmlta_doctype_analysis varchar(2) NOT NULL DEFAULT '' after xmlta_doctype_bulletin";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default xmlta_doctype_analysis after xmlta_doctype_bulletin");

		// Mise à jour des valeurs en fonction du type de document par défaut en création de notice, si la valeur est vide !
		if ($res = mysql_query("select userid, xmlta_doctype,xmlta_doctype_serial,xmlta_doctype_bulletin,xmlta_doctype_analysis from users")){
			while ( $row = mysql_fetch_object($res)) {
				if ($row->xmlta_doctype_serial == '') mysql_query("update users set xmlta_doctype_serial='".$row->xmlta_doctype."' where userid=".$row->userid);
				if ($row->xmlta_doctype_bulletin == '') mysql_query("update users set xmlta_doctype_bulletin='".$row->xmlta_doctype."' where userid=".$row->userid);
				if ($row->xmlta_doctype_analysis == '') mysql_query("update users set xmlta_doctype_analysis='".$row->xmlta_doctype."' where userid=".$row->userid);
			}
		}

		// Ajout affichage a2z par localisation
		$rqt = "alter table docs_location add show_a2z int(1) unsigned not null default 0 ";
		echo traite_rqt($rqt,"ALTER TABLE docs_location ADD show_a2z");
		
		// demande GM : index sur 
		$rqt = "alter table pret drop index i_pret_arc_id";
		echo traite_rqt($rqt,"alter table pret drop index i_pret_arc_id");
		$rqt = "alter table pret add index i_pret_arc_id(pret_arc_id)";
		echo traite_rqt($rqt,"alter table pret add index i_pret_arc_id");
		
		$rqt = "CREATE TABLE if not exists facettes (
				id_facette int unsigned auto_increment,
				facette_name varchar(255) not null default '',
				facette_critere int(5) not null default 0,
				facette_ss_critere int(5) not null default 0,
				facette_nb_result int(2) not null default 0,
				facette_visible tinyint(1) not null default 0,
				facette_type_sort int(1) not null default 0,
				facette_order_sort int(1) not null default 0,
				primary key (id_facette))";
		echo traite_rqt($rqt,"CREATE TABLE facettes");
		
		// début circulation périodiques
		//ajout du champ expl_abt_num permettant de lier l'exemplaire a un abonnement de pério
		$rqt = "ALTER TABLE exemplaires ADD expl_abt_num int unsigned not null default 0 ";
		echo traite_rqt($rqt,"ALTER TABLE exemplaires ADD expl_abt_num");		
		
		$rqt="create table if not exists serialcirc (
			id_serialcirc int unsigned not null auto_increment primary key,
			num_serialcirc_abt int unsigned not null default 0,
			serialcirc_type int unsigned not null default 0,
			serialcirc_virtual int unsigned not null default 0,
			serialcirc_duration int unsigned not null default 0,
			serialcirc_checked int unsigned not null default 0,
			serialcirc_retard_mode int unsigned not null default 0,
			serialcirc_allow_resa int unsigned not null default 0,
			serialcirc_allow_copy int unsigned not null default 0,
			serialcirc_allow_send_ask int unsigned not null default 0,			
			serialcirc_allow_subscription int unsigned not null default 0,					
			serialcirc_duration_before_send int unsigned not null default 0,									
			serialcirc_expl_statut_circ int unsigned not null default 0,							
			serialcirc_expl_statut_circ_after int unsigned not null default 0,			
			serialcirc_state int unsigned not null default 0			
		)";
		echo traite_rqt($rqt, "create table serialcirc");				
			
		$rqt="create table if not exists serialcirc_diff (
			id_serialcirc_diff int unsigned not null auto_increment primary key,
			num_serialcirc_diff_serialcirc int unsigned not null default 0,
			serialcirc_diff_empr_type int unsigned not null default 0,		
			serialcirc_diff_type_diff int unsigned not null default 0,	
			num_serialcirc_diff_empr int unsigned not null default 0,
			serialcirc_diff_group_name varchar(255) not null default '',	
			serialcirc_diff_duration int unsigned not null default 0,	
			serialcirc_diff_order int unsigned not null default 0			
		)";				
		echo traite_rqt($rqt, "create table serialcirc_diff");				
		
		$rqt="create table if not exists serialcirc_group (
			id_serialcirc_group int unsigned not null auto_increment primary key,
			num_serialcirc_group_diff int unsigned not null default 0,
			num_serialcirc_group_empr int unsigned not null default 0,
			serialcirc_group_responsable int unsigned not null default 0,
			serialcirc_group_order int unsigned not null default 0					
		)";		
		echo traite_rqt($rqt, "create table serialcirc_group");				
		
		$rqt="create table if not exists serialcirc_expl (
			id_serialcirc_expl int unsigned not null auto_increment primary key,
			num_serialcirc_expl_id int unsigned not null default 0,
			num_serialcirc_expl_serialcirc int unsigned not null default 0,
			serialcirc_expl_bulletine_date date NOT NULL default '0000-00-00',
			serialcirc_expl_state_circ int unsigned not null default 0,
			num_serialcirc_expl_serialcirc_diff int unsigned not null default 0,
			serialcirc_expl_ret_asked int unsigned not null default 0,
			serialcirc_expl_trans_asked int unsigned not null default 0,
			serialcirc_expl_trans_doc_asked int unsigned not null default 0,			
			num_serialcirc_expl_current_empr int unsigned not null default 0,
			serialcirc_expl_start_date date NOT NULL default '0000-00-00'			
		)";		
		echo traite_rqt($rqt, "create table serialcirc_expl");						
		
		$rqt="create table if not exists serialcirc_circ (
			id_serialcirc_circ int unsigned not null auto_increment primary key,
			num_serialcirc_circ_diff int unsigned not null default 0,
			num_serialcirc_circ_expl int unsigned not null default 0,
			num_serialcirc_circ_empr int unsigned not null default 0,
			num_serialcirc_circ_serialcirc int unsigned not null default 0,
            serialcirc_circ_order int unsigned not null default 0,
            serialcirc_circ_subscription int unsigned not null default 0,
            serialcirc_circ_ret_asked int unsigned not null default 0,
            serialcirc_circ_trans_asked int unsigned not null default 0,
            serialcirc_circ_trans_doc_asked int unsigned not null default 0,
			serialcirc_circ_expected_date datetime,
			serialcirc_circ_pointed_date datetime
		)";
		//,			primary key(id_serialcirc_circ, num_serialcirc_circ_diff,num_serialcirc_circ_expl,num_serialcirc_circ_empr,num_serialcirc_circ_serialcirc)
		echo traite_rqt($rqt,"create table serialcirc_circ");
		
		//path_pmb planificateur
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='path_php' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (0, 'pmb', 'path_php', '', 'Chemin absolu de l\'interpréteur PHP, local ou distant', '',0) ";
			echo traite_rqt($rqt, "insert pmb_path_php into parameters");
		}
		
		//modification taille du champ expl_comment de la table exemplaires
		$rqt = "ALTER TABLE exemplaires MODIFY expl_comment TEXT ";
		echo traite_rqt($rqt,"ALTER TABLE exemplaires MODIFY expl_comment");

		//tri sur les documents numériques en OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='explnum_order' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'opac', 'explnum_order', 'explnum_mimetype, explnum_nom, explnum_id','Ordre d\'affichage des documents numériques, dans l\'ordre donné, séparé par des virgules : explnum_mimetype, explnum_nom, explnum_id','e_aff_notice')";
			echo traite_rqt($rqt,"insert opac_explnum_order=explnum_mimetype, explnum_nom, explnum_id into parametres");
		}
		
		//modification taille du champ resa_idempr de la table resa
		$rqt = "ALTER TABLE resa MODIFY resa_idempr int(10) unsigned NOT NULL default 0";
		echo traite_rqt($rqt,"ALTER TABLE resa MODIFY resa_idempr");
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.06");
		break;

	case "v5.06":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		@set_time_limit(0);
		//ajout d'un flag pour la résa en ciculation
		$rqt = "alter table serialcirc_circ add serialcirc_circ_hold_asked int not null default 0 after serialcirc_circ_subscription";
		echo traite_rqt($rqt,"alter table serialcirc_circ add serialcirc_circ_hold_asked");
		
		//table de gestion des demandes de reproduction
		$rqt="create table if not exists serialcirc_copy (
			id_serialcirc_copy int not null auto_increment primary key,
			num_serialcirc_copy_empr int not null default 0,
			num_serialcirc_copy_bulletin int not null default 0,
			serialcirc_copy_analysis text,
			serialcirc_copy_date date not null default '0000-00-00',
			serialcirc_copy_state int not null default 0,
			serialcirc_copy_comment text not null
			)";
		echo traite_rqt($rqt,"create table serialcirc_copy");
				
		$rqt="create table if not exists serialcirc_ask (
			id_serialcirc_ask int unsigned not null auto_increment primary key,
			num_serialcirc_ask_perio int unsigned not null default 0,
			num_serialcirc_ask_serialcirc int unsigned not null default 0,
			num_serialcirc_ask_empr int unsigned not null default 0,
			serialcirc_ask_type int unsigned not null default 0,
			serialcirc_ask_statut int unsigned not null default 0,
			serialcirc_ask_date date NOT NULL default '0000-00-00',
			serialcirc_ask_comment text not null
			)";
		echo traite_rqt($rqt,"create table serialcirc_ask");		

		// Création table facettes foireuse en développement
		$rqt = "ALTER TABLE facettes add facette_type_sort int(1) not null default 0 AFTER facette_visible";
		echo traite_rqt($rqt,"ALTER TABLE facettes add facette_type_sort ");
		$rqt = "ALTER TABLE facettes add facette_order_sort int(1) not null default 0 AFTER facette_type_sort";
		echo traite_rqt($rqt,"ALTER TABLE facettes add facette_order_sort ");
		
		// comptabilisation de l'amende : à partir de la date de retour, à partir du délai de grâce
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='amende_comptabilisation' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'pmb', 'amende_comptabilisation', '0','Date à laquelle le début de l\'amende sera comptabilisée \r\n 0 : à partir de la date de retour \r\n 1 : à partir du délai de grâce','')";
			echo traite_rqt($rqt,"insert pmb_amende_comptabilisation=0 into parametres");
		}
		
		// prêt en retard : compter le jour de la date de retour ou la date de relance comme un retard ?
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='pret_calcul_retard_date_debut_incluse' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'pmb', 'pret_calcul_retard_date_debut_incluse', '0','Compter le jour de retour ou de relance comme un jour de retard pour le calcul de l\'amende ? \r\n 0 : Non \r\n  1 : Oui','')";
			echo traite_rqt($rqt,"insert pmb_pret_calcul_retard_date_debut_incluse=0 into parametres");
		}
		
		//modification taille du champ comment_gestion de la table bannettes
		$rqt = "ALTER TABLE bannettes MODIFY comment_gestion text NOT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE bannettes MODIFY comment_gestion");
		
		//modification taille du champ comment_public de la table bannettes
		$rqt = "ALTER TABLE bannettes MODIFY comment_public text NOT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE bannettes MODIFY comment_public");

		//AR
		//Exclusion de champs dans la recherche tous les champs en OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='exclude_fields' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'opac', 'exclude_fields', '','Identifiants des champs à exclure de la recherche tous les champs (liste dispo dans le fichier includes/indexation/champ_base.xml)','c_recherche')";
			echo traite_rqt($rqt,"insert opac_exclude_fields into parametres");
		}		
		
		//ajout dates log dans table des vues
		$rqt = "ALTER TABLE statopac_vues ADD date_debut_log DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				ADD date_fin_log DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ";
		echo traite_rqt($rqt,"ALTER TABLE statopac_vues add log dates");
				
		//Ajout champ serialcirc_tpl pour l'impression de la fiche de circulation
		$rqt = "ALTER TABLE serialcirc ADD serialcirc_tpl TEXT NOT NULL";
		echo traite_rqt($rqt,"ALTER TABLE serialcirc ADD serialcirc_tpl ");
		
		//AR
		//Onglet Abonnement du compte emprunteur visible ou non...
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='serialcirc_active' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'opac', 'serialcirc_active', 0,'Activer la circulation des pédioques dans l\'OPAC \r\n 0: Non \r\n 1: Oui','f_modules')";
			echo traite_rqt($rqt,"insert opac_serialcirc_active into parametres");
		}
		
		//AR
		//Ajout d'un droit sur le statut pour la circulation des périos
		$rqt = "alter table empr_statut add allow_serialcirc int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table empr_statut add allow_serialcirc");
		
		// création $pmb_bdd_subversion
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='bdd_subversion' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) 
				VALUES (0, 'pmb', 'bdd_subversion', '0', 'Sous-version de la base de données')";
			echo traite_rqt($rqt,"insert pmb_bdd_subversion=0 into parametres");
		}
		
		//AR - Ajout d'un paramètre pour définir la classe d'import des autorités...
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='import_modele_authorities' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'pmb', 'import_modele_authorities', 'notice_authority_import','Quelle classe d\'import utiliser pour les notices d\'autorités ?','')";
			echo traite_rqt($rqt,"insert pmb_import_modele_authorities into parametres");
		}
			
		//AR - pris dans le tapis entre 2 versions...
		//création de la table origin_authorities
		$rqt = "create table if not exists origin_authorities (
			id_origin_authorities int(10) unsigned NOT NULL AUTO_INCREMENT,
			origin_authorities_name varchar(255) NOT NULL DEFAULT '',
			origin_authorities_country varchar(10) NOT NULL DEFAULT '',
			origin_authorities_diffusible int(10) unsigned NOT NULL DEFAULT 0,
			primary key (id_origin_authorities)
			)";
		echo traite_rqt($rqt,"create table origin_authorities");
		//AR - ajout de valeurs par défault...
		$rqt = "insert into origin_authorities 
				(id_origin_authorities,origin_authorities_name,origin_authorities_country,origin_authorities_diffusible) 
			values
				(1,'Catalogue Interne','FR',1),
				(2,'BnF','FR',1)";
		echo traite_rqt($rqt,"insert default values into origin_authorities");
		
		//AR - création de la table authorities_source
		$rqt = "create table if not exists authorities_sources (
			id_authority_source int(10) unsigned NOT NULL AUTO_INCREMENT,
			num_authority int(10) unsigned NOT NULL DEFAULT 0,
			authority_number varchar(50) NOT NULL DEFAULT '',
			authority_type varchar(20) NOT NULL DEFAULT '',
			num_origin_authority int(10) unsigned NOT NULL DEFAULT 0,
			authority_favorite int(10) unsigned NOT NULL DEFAULT 0,
			import_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			update_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			primary key (id_authority_source) )";
		echo traite_rqt($rqt,"create table authorities_sources");
		
		//AR - création de la table notices_authorities_sources
		$rqt ="create table if not exists notices_authorities_sources (
			num_authority_source int(10) unsigned NOT NULL DEFAULT 0,
			num_notice int(10) unsigned NOT NULL DEFAULT 0,
			primary key (num_authority_source,num_notice)
			)"; 
		echo traite_rqt($rqt,"create table notices_authorities_sources");
		
		//AR - modification du champ aut_link_type
		$rqt = "alter table aut_link change aut_link_type aut_link_type varchar(2) not null default ''";
		echo traite_rqt($rqt,"alter table aut_link change aut_link_type varchar");
		
		//MB - Modification de l'explication du paramètre d'affichage des dates d'exemplaire
		$rqt="UPDATE parametres SET comment_param='Afficher les dates des exemplaires ? \n 0 : Aucune date.\n 1 : Date de création et modification.\n 2 : Date de dépôt et retour (BDP).\n 3 : Date de création, modification, dépôt et retour.' WHERE type_param='pmb' AND sstype_param='expl_show_dates'";
		$res = mysql_query($rqt, $dbh) ;
		
		//DG
		// localisation des prévisions
		if (mysql_num_rows(mysql_query("SELECT 1 FROM parametres WHERE type_param= 'pmb' and sstype_param='location_resa_planning' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
					VALUES (0, 'pmb', 'location_resa_planning', '0', '0', 'Utiliser la gestion de la prévision localisée?\n 0: Non\n 1: Oui') ";
			echo traite_rqt($rqt,"INSERT location_resa_planning INTO parametres") ;
		}
		
		//Localisation par défaut sur la visualisation des états des collections
		$rqt = "ALTER TABLE users ADD deflt_collstate_location int(6) UNSIGNED DEFAULT 0 after deflt_docs_location";
		echo traite_rqt($rqt,"ALTER TABLE users ADD deflt_collstate_location after deflt_docs_location");
		
		//maj valeurs possibles pour empr_filter_rows
		$rqt = "update parametres set comment_param='Colonnes disponibles pour filtrer la liste des emprunteurs : \n v: ville\n l: localisation\n c: catégorie\n s: statut\n g: groupe\n y: année de naissance\n cp: code postal\n cs : code statistique\n ab : type d\'abonnement\n #n : id des champs personnalisés' where type_param= 'empr' and sstype_param='filter_rows' ";
		echo traite_rqt($rqt,"update empr_filter_rows into parametres");
		
		//maj valeurs possibles pour empr_show_rows
		$rqt = "update parametres set comment_param='Colonnes affichées en liste de lecteurs, saisir les colonnes séparées par des virgules. Les colonnes disponibles pour l\'affichage de la liste des emprunteurs sont : \n n: nom+prénom \n a: adresse \n b: code-barre \n c: catégories \n g: groupes \n l: localisation \n s: statut \n cp: code postal \n v: ville \n y: année de naissance \n ab: type d\'abonnement \n #n : id des champs personnalisés \n 1: icône panier' where type_param= 'empr' and sstype_param='show_rows' ";
		echo traite_rqt($rqt,"update empr_show_rows into parametres");
		
		//maj valeurs possibles pour empr_sort_rows
		$rqt = "update parametres set comment_param='Colonnes qui seront disponibles pour le tri des emprunteurs. Les colonnes possibles sont : \n n: nom+prénom \n c: catégories \n g: groupes \n l: localisation \n s: statut \n cp: code postal \n v: ville \n y: année de naissance \n ab: type d\'abonnement \n #n : id des champs personnalisés' where type_param= 'empr' and sstype_param='sort_rows' ";
		echo traite_rqt($rqt,"update empr_sort_rows into parametres");

		//maj commentaire sms_msg_retard
		$rqt = "update parametres set comment_param='Texte du sms envoyé lors d\'un retard' where type_param= 'empr' and sstype_param='sms_msg_retard' ";
		echo traite_rqt($rqt,"update empr_sms_msg_retard into parametres");
		
		//maj commentaire afficher_numero_lecteur_lettres
		$rqt = "update parametres set comment_param='Afficher le numéro et le mail du lecteur sous l\'adresse dans les différentes lettres' where type_param= 'pmb' and sstype_param='afficher_numero_lecteur_lettres' ";
		echo traite_rqt($rqt,"update pmb_afficher_numero_lecteur_lettres into parametres");

		//DB
		//modification du paramètre empr_sms_activation
		$rqt = "select valeur_param from parametres where type_param= 'empr' and sstype_param='sms_activation' ";
		$res = mysql_query($rqt);
		if (mysql_num_rows($res)) {
			$old_value = mysql_result($res,0,0);
			if ($old_value==1) {
				$new_value='1,1,1,1';
				$rqt = "update parametres set valeur_param='".$new_value."', comment_param='Activation de l\'envoi de sms. : relance 1,relance 2,relance 3,resa\n\n 0: Inactif\n 1: Actif' where type_param= 'empr' and sstype_param='sms_activation' ";
				echo traite_rqt($rqt,"update sms_activation");
			} elseif ($old_value==0) {
				$new_value='0,0,0,0';	
				$rqt = "update parametres set valeur_param='".$new_value."', comment_param='Activation de l\'envoi de sms. : relance 1,relance 2,relance 3,resa\n\n 0: Inactif\n 1: Actif' where type_param= 'empr' and sstype_param='sms_activation' ";
				echo traite_rqt($rqt,"update empr_sms_activation");
			}
		}
		
		//Ajout de la durée de consultation pour la circulation des périos
		$rqt = "alter table abts_periodicites add consultation_duration int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table abts_periodicites add consultation_duration");

		// suppr index inutile
		$rqt = "alter table notices_fields_global_index drop index i_id_value";
		echo traite_rqt($rqt,"alter table notices_fields_global_index drop index i_id_value");	
		
		//Modification du commentaire du paramètre opac_notice_reduit_format pour ajout format titre uniquement
		$rqt = "update parametres set comment_param = 'Format d\'affichage des réduits de notices :\n 0 = titre+auteur principal\n 1 = titre+auteur principal+date édition\n 2 = titre+auteur principal+date édition + ISBN\n 3 = titre seul\n P 1,2,3 = tit+aut+champs persos id 1 2 3\n E 1,2,3 = tit+aut+édit+champs persos id 1 2 3\n T = tit1+tit4' where type_param='opac' and sstype_param='notice_reduit_format'";
		echo traite_rqt($rqt,"update parametre opac_notice_reduit_format");
		
		// Ajout du module Havest: Moissonneur de notice
        $rqt="create table if not exists harvest_profil (
            id_harvest_profil int unsigned not null auto_increment primary key,
            harvest_profil_name varchar(255) not null default ''    
        	)";
        echo traite_rqt($rqt,"create table harvest");
           
        $rqt="create table if not exists harvest_field (
            id_harvest_field int unsigned not null auto_increment primary key,
            num_harvest_profil int unsigned not null default 0,            
            harvest_field_xml_id int unsigned not null default 0,        
            harvest_field_first_flag int unsigned not null default 0,
            harvest_field_order int unsigned not null default 0
       		)";
        echo traite_rqt($rqt,"create table harvest_field");            
           
        $rqt="create table if not exists harvest_src (
            id_harvest_src int unsigned not null auto_increment primary key,
            num_harvest_field int unsigned not null default 0,            
            num_source int unsigned not null default 0,    
            harvest_src_unimacfield varchar(255) not null default '',                    
            harvest_src_unimacsubfield varchar(255) not null default '',
            harvest_src_pmb_unimacfield varchar(255) not null default '',
            harvest_src_pmb_unimacsubfield varchar(255) not null default '',                
            harvest_src_prec_flag int unsigned not null default 0,                
            harvest_src_order int unsigned not null default 0
        	)";
        echo traite_rqt($rqt,"create table harvest_src");      
       
        $rqt="create table if not exists harvest_profil_import (
            id_harvest_profil_import int unsigned not null auto_increment primary key,
            harvest_profil_import_name varchar(255) not null default ''
        	)";        
        echo traite_rqt($rqt,"create table harvest_profil_import");    
       
        $rqt="create table if not exists harvest_profil_import_field (
            num_harvest_profil_import int unsigned not null default 0,        
            harvest_profil_import_field_xml_id int unsigned not null default 0, 
            harvest_profil_import_field_flag int unsigned not null default 0,       
            harvest_profil_import_field_order int unsigned not null default 0,            
            PRIMARY KEY (num_harvest_profil_import, harvest_profil_import_field_xml_id)
        	)";
        echo traite_rqt($rqt,"create table harvest_profil_import_field");    
        
       	$rqt = "CREATE TABLE if not exists harvest_search_field (
			num_harvest_profil int unsigned not null default 0,               
			num_source int unsigned not null default 0,        
			num_field int unsigned not null default 0,        
			num_ss_field int unsigned not null default 0 ,         
            PRIMARY KEY (num_harvest_profil, num_source)
			)";
		echo traite_rqt($rqt,"CREATE TABLE harvest_search_field");
		
		//AR - Ajout d'un paramètre de blocage d'import dans les autorités
		$rqt = "alter table noeuds add authority_import_denied int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table noeuds add authority_import_denied");
		$rqt = "alter table authors add author_import_denied int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table authors add author_import_denied");
		$rqt = "alter table titres_uniformes add tu_import_denied int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table titres_uniformes add tu_import_denied");
		$rqt = "alter table sub_collections add authority_import_denied int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table sub_collections add authority_import_denied");
		$rqt = "alter table collections add authority_import_denied int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table collections add authority_import_denied");
		
		//AR - Modification d'un paramètre pour définir la classe d'import des autorités...
		$rqt = "update parametres set valeur_param = 'authority_import' where type_param= 'pmb' and sstype_param = 'import_modele_authorities'";
		echo traite_rqt($rqt,"update parametres set pmb_import_modele_authorities = 'authority_import'");
		
		//Ajout d'un index sur le champ ref dans les tables entrepots
		//Récupération de la liste des sources
		$sql_liste_sources = "SELECT source_id FROM connectors_sources ";
		$res_liste_sources = mysql_query($sql_liste_sources, $dbh) or die(mysql_error());

		//Pour chaque source
		while ($row=mysql_fetch_row($res_liste_sources)) {
			$sql_alter_table = "alter table entrepot_source_".$row[0]." drop index i_ref ";
			echo traite_rqt($sql_alter_table, "alter table entrepot_source_".$row[0]." drop index i_ref");
			$sql_alter_table = "alter table entrepot_source_".$row[0]." add index i_ref (ref) ";
			echo traite_rqt($sql_alter_table, "alter table entrepot_source_".$row[0]." add index i_ref");
		}
		 
		//Ajout d'un parametre permettant de préciser si l'on informe par email de l'évolution des demandes 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'demandes' and sstype_param='email_demandes' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'demandes', 'email_demandes', '1', 
					'Information par email de l\'évolution des demandes.\n 0 : Non\n 1 : Oui',
					'',0) ";
			echo traite_rqt($rqt, "insert demandes_email_demandes into parameters");
		}
		
		
		//AR - Ajout d'un paramètre utilisateur (choix d'un thésaurus par défaut en import d'autorités
		$rqt = "alter table users add deflt_import_thesaurus int not null default 1 after deflt_thesaurus";
		echo traite_rqt($rqt,"alter table users add deflt_import_thesaurus'");
		
		//AR - On lui met un bonne valeur par défaut...
		$rqt = "update users set deflt_import_thesaurus = ".$thesaurus_defaut;
		echo traite_rqt($rqt,"update users set deflt_import_thesaurus");
		
		//AR - Ajout d'une colonne sur la table connectors_sources pour définir les types d'enrichissements autorisés dans une source
		$rqt = "alter table connectors_sources add type_enrichment_allowed text not null";
		echo traite_rqt($rqt,"alter table connectors_sources add type_enrichment_allowed");

		// ER - index notices.statut
		$rqt = "ALTER TABLE notices DROP INDEX i_not_statut " ;
		echo traite_rqt($rqt,"ALTER TABLE notices DROP INDEX i_not_statut ") ;
		$rqt = "ALTER TABLE notices ADD INDEX i_not_statut (statut)" ;
		echo traite_rqt($rqt,"ALTER TABLE notices ADD INDEX i_not_statut (statut)") ;
		
		
		// Création cms
		$rqt="create table if not exists cms_cadres (
            id_cadre int unsigned not null auto_increment primary key,          
            cadre_hash varchar(255) not null default '',      
            cadre_name varchar(255) not null default '',                   
            cadre_styles text not null, 
            cadre_dom_parent varchar(255) not null default '', 
            cadre_dom_after varchar(255) not null default ''
        	)";
        echo traite_rqt($rqt,"create table cms_cadres");      
        
		$rqt="create table if not exists cms_cadre_content (
            id_cadre_content int unsigned not null auto_increment primary key,          
            cadre_content_hash varchar(255) not null default '',                        
            cadre_content_type varchar(255) not null default '',  
            cadre_content_num_cadre int(10) unsigned not null default 0,                  
            cadre_content_data text not null,     
            cadre_content_num_cadre_content int unsigned not null default 0
        	)";
        echo traite_rqt($rqt,"create table cms_cadre_content");    
        
		$rqt="create table if not exists cms_pages (
            id_page int unsigned not null auto_increment primary key,          
            page_hash varchar(255) not null default '',             
            page_name varchar(255) not null default '',                  
            page_description text not null
       		)";
        echo traite_rqt($rqt,"create table cms_pages");  
        
		$rqt="create table if not exists cms_vars (
            id_var int unsigned not null auto_increment primary key, 
            var_num_page int unsigned not null default 0,         
            var_name varchar(255) not null default '',             
            var_comment varchar(255) not null default ''
        	)";
        echo traite_rqt($rqt,"create table cms_vars"); 
         		
		$rqt="create table if not exists cms_pages_env (
            page_env_num_page int unsigned not null auto_increment primary key,
            page_env_name varchar(255) not null default '',
            page_env_id_selector varchar(255) not null default ''
        	)";
        echo traite_rqt($rqt,"create table cms_pages_env");        
				

		$rqt="create table if not exists cms_hash (
            hash varchar(255) not null default '' primary key
        	)";
        echo traite_rqt($rqt,"create table cms_hash ");  
              
		//DB - parametre gestion de pret court
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='short_loan_management' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) 
				VALUES (0, 'pmb', 'short_loan_management', '0', 'Gestion des prêts courts\n 0: Non\n 1: Oui')";
			echo traite_rqt($rqt,"insert pmb_short_loan_management=0 into parametres");
		}
		//DB - ajout colonne duree pret court dans la table docs_type
		$rqt="ALTER TABLE docs_type ADD short_loan_duration INT(6) UNSIGNED NOT NULL DEFAULT 1 ";
		echo traite_rqt($rqt,"alter table docs_type add short_loan_duration");
		
		//DB - correction origine notices
		$rqt = "update notices set origine_catalogage='1' where origine_catalogage='0' ";
		echo traite_rqt($rqt,"alter table notices correct origine_catalogage");
		
		//DB - ajout flag pret court dans table pret
		$rqt = "ALTER TABLE pret ADD short_loan_flag INT(1) NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"alter table pret add short_loan_flag");

		//DB - ajout flag pret court dans table pret_archive
		$rqt = "ALTER TABLE pret_archive ADD arc_short_loan_flag INT(1) NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"alter table pret_archive add arc_short_loan_flag");
		
		//DB - parametre gestion de monopole de pret 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='loan_trust_management' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) 
				VALUES (0, 'pmb', 'loan_trust_management', '0', 'Gestion de monopole de prêt\n 0: Non\n x: nombre de jours entre 2 prêts d\'un exemplaire d\'une même notice (ou bulletin)')";
			echo traite_rqt($rqt,"insert pmb_loan_trust_management=0 into parametres");
		}
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.07");
		break;

	case "v5.07":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		// ER : pour le gars au pull rouge
		$rqt = "ALTER TABLE exemplaires MODIFY expl_cote varchar(255) ";
		echo traite_rqt($rqt,"ALTER TABLE exemplaires MODIFY expl_cote varchar(255) ");
		$rqt = "ALTER TABLE exemplaires MODIFY expl_cb varchar(255) ";
		echo traite_rqt($rqt,"ALTER TABLE exemplaires MODIFY expl_cb varchar(255) ");
		
		//AR - Ajout d'un champ dans cms_cadres
		$rqt = "alter table cms_cadres add cadre_object varchar(255) not null default '' after cadre_hash";
		echo traite_rqt($rqt,"alter table cms_cadre add cadre_object");
		
		//JP - Ajout tri en opac pour champs persos de notice
		$rqt = "ALTER TABLE collstate_custom ADD opac_sort INT NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE collstate_custom ADD opac_sort INT NOT NULL DEFAULT 0");
		
		$rqt = "ALTER TABLE empr_custom ADD opac_sort INT NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE empr_custom ADD opac_sort INT NOT NULL DEFAULT 0");
		
		$rqt = "ALTER TABLE expl_custom ADD opac_sort INT NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE expl_custom ADD opac_sort INT NOT NULL DEFAULT 0");
		
		$rqt = "ALTER TABLE gestfic0_custom ADD opac_sort INT NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE gestfic0_custom ADD opac_sort INT NOT NULL DEFAULT 0");
		
		$rqt = "ALTER TABLE notices_custom ADD opac_sort INT NOT NULL DEFAULT 1";
		echo traite_rqt($rqt,"ALTER TABLE notices_custom ADD opac_sort INT NOT NULL DEFAULT 1");

		//JP : Ajout d'un paramètre permettant de choisir une navigation abécédaire ou non en navigation dans les périodiques en OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='perio_a2z_abc_search' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'opac', 'perio_a2z_abc_search', '0', 
					'Recherche abécédaire dans le navigateur de périodiques en OPAC.\n0 : Non.\n1 : Oui.',
					'c_recherche',0) ";
			echo traite_rqt($rqt, "insert opac_perio_a2z_abc_search 0 into parameters");
		}
		
		//JP : Ajout d'un paramètre permettant de choisir le nombre maximum de notices par onglet en navigation dans les périodiques en OPAC 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='perio_a2z_max_per_onglet' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'opac', 'perio_a2z_max_per_onglet', '10', 
					'Recherche dans le navigateur de périodiques en OPAC : nombre maximum de notices par onglet.',
					'c_recherche',0) ";
			echo traite_rqt($rqt, "insert opac_perio_a2z_max_per_onglet 10 into parameters");
		}

		//DG - Mail de rappel au référent
		$rqt = "ALTER TABLE groupe ADD mail_rappel INT( 1 ) UNSIGNED DEFAULT 0 NOT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE groupe ADD mail_rappel default 0");
		
		//DG - Modification du commentaire du paramètre opac_notice_reduit_format pour ajout format titre uniquement
		$rqt = "update parametres set comment_param = 'Format d\'affichage des réduits de notices :\n 0 = titre+auteur principal\n 1 = titre+auteur principal+date édition\n 2 = titre+auteur principal+date édition + ISBN\n 3 = titre seul\n P 1,2,3 = tit+aut+champs persos id 1 2 3\n E 1,2,3 = tit+aut+édit+champs persos id 1 2 3\n T = tit1+tit4\n 4 = titre+titre parallèle+auteur principal' where type_param='opac' and sstype_param='notice_reduit_format'";
		echo traite_rqt($rqt,"update parametre opac_notice_reduit_format");
		
		//DG - Alerter l'utilisateur par mail des nouvelles demandes en OPAC ?
		$rqt = "ALTER TABLE users ADD user_alert_demandesmail INT(1) UNSIGNED NOT NULL DEFAULT 0 after user_alert_resamail";
		echo traite_rqt($rqt,"ALTER TABLE users add user_alert_demandesmail default 0");
		
		$rqt = "ALTER TABLE cms_cadre_content ADD cadre_content_object  VARCHAR(  255 ) NOT NULL DEFAULT '' AFTER cadre_content_type";
		echo traite_rqt($rqt,"ALTER TABLE cms_cadre_content ADD cadre_content_object");
		
		$rqt = "ALTER TABLE cms_build ADD build_page int(11) NOT NULL DEFAULT 0 AFTER build_obj";
		echo traite_rqt($rqt,"ALTER TABLE cms_build ADD build_page");

		//DG - Ordre des langues pour les notices
		$rqt = "ALTER TABLE notices_langues ADD ordre_langue smallint(2) UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE notices_langues ADD ordre_langue") ;

		//DB - grilles emprunteurs
		$rqt = "create table empr_grilles (
				empr_grille_categ int(5) not null default 0,
				empr_grille_location int(5) not null default 0,
				empr_grille_format longtext,
				primary key  (empr_grille_categ,empr_grille_location))";
		echo traite_rqt($rqt,"create table empr_grilles") ;

		//DB - parametres de gestion d'accès aux programmes externes pour l'indexation des documents numeriques
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='indexation_docnum_ext' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'pmb', 'indexation_docnum_ext', '', 
					'Paramètres de gestion d\'accès aux programmes externes pour l\'indexation des documents numériques :\n\n Chaque paramètre est défini par un  couple : \"nom=valeur\"\n Les paramètres sont séparés par un \"point-virgule\".\n\n\n Exemples d\'utilisation de \"pyodconverter\", \"jodconverter\" et \"pdftotext\" :\n\npyodconverter_cmd=/opt/openoffice.org3/program/python /opt/ooo_converter/DocumentConverter.py %1s %2s;\njodconverter_cmd=/usr/bin/java -jar /opt/ooo_converter/jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar %1s %2s;\njodconverter_url=http://localhost:8080/converter/converted/%1s;\npdftotext_cmd=/usr/bin/pdftotext -enc UTF-8 %1s -;',
					'',0) ";
			echo traite_rqt($rqt, "insert indexation_docnum_ext into parameters");
		}		
		
		//Onglet perso en affichage de notice
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='notices_format_onglets' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'opac', 'notices_format_onglets', '','Liste des id de template de notice pour ajouter des onglets personnalisés en affichage de notice\nExemple: 1,3','e_aff_notice')";
			echo traite_rqt($rqt,"insert opac_notices_format_onglets into parametres");
		}
			
		//DG - Ajout de la localisation de l'emprunteur pour les stats
		$rqt="ALTER TABLE pret_archive ADD arc_empr_location INT( 6 ) UNSIGNED DEFAULT 0 NOT NULL AFTER arc_empr_statut "; 
 		echo traite_rqt($rqt,"alter table pret_archive add arc_empr_location default 0");
 		
 		//DG - Ajout du type d'abonnement de l'emprunteur pour les stats
		$rqt="ALTER TABLE pret_archive ADD arc_type_abt INT( 6 ) UNSIGNED DEFAULT 0 NOT NULL AFTER arc_empr_location "; 
 		echo traite_rqt($rqt,"alter table pret_archive add arc_type_abt default 0");

		//DG - Libellé OPAC des statuts d'exemplaires
		$rqt = "ALTER TABLE docs_statut ADD statut_libelle_opac VARCHAR(255) DEFAULT '' after statut_libelle";
		echo traite_rqt($rqt,"ALTER TABLE docs_statut add statut_libelle_opac default ''");
		
		//DG - Visibilité OPAC des statuts d'exemplaires
 		$rqt = "ALTER TABLE docs_statut ADD statut_visible_opac TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 1";
		echo traite_rqt($rqt,"ALTER TABLE docs_statut ADD statut_visible_opac") ;

		//DB - parametres d'alerte avant affichage des documents numériques
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='visionneuse_alert' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'opac', 'visionneuse_alert', '', 'Message d\'alerte à l\'ouverture des documents numériques.', 'm_photo',0) ";
			echo traite_rqt($rqt, "insert opac_visionneuse_alert into parameters");
		}		
			
		$rqt = "ALTER TABLE cms_build ADD build_fixed int(11) NOT NULL DEFAULT 0 AFTER id_build";
		echo traite_rqt($rqt,"ALTER TABLE cms_build ADD build_fixed");
				
		$rqt = "ALTER TABLE cms_build ADD build_child_before varchar(255) not null default '' AFTER build_parent";
		echo traite_rqt($rqt,"ALTER TABLE cms_build ADD build_child_before");

		//AR - création d'une boite noire pour les modules du portail
		$rqt="create table if not exists cms_managed_modules (
			managed_module_name varchar(255) not null default '',
			managed_module_box text not null,
			primary key (managed_module_name))";
		echo traite_rqt($rqt, "create table if not exists cms_managed_modules");
		
		
		$rqt = "alter table cms_cadres add cadre_fixed int(11) not null default 0 after cadre_name";
		echo traite_rqt($rqt,"alter table cms_cadres add cadre_fixed");


		//DG - Fixer l'âge minimum d'accès à la catégorie de lecteurs
		$rqt = "ALTER TABLE empr_categ ADD age_min INT(3) UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE empr_categ ADD age_min default 0");
		
		//DG - Fixer l'âge maximum d'accès à la catégorie de lecteurs
		$rqt = "ALTER TABLE empr_categ ADD age_max INT(3) UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE empr_categ ADD age_max default 0");

		// Liste des cms  
		$rqt="create table if not exists cms (
            id_cms int unsigned not null auto_increment primary key,
            cms_name varchar(255) not null default '',
            cms_comment text not null
        )";		
        echo traite_rqt($rqt,"create table cms"); 
       
 		// évolutions des cms  
		$rqt="create table if not exists cms_version (
            id_version int unsigned not null auto_increment primary key,
            version_cms_num int unsigned not null default 0 ,  
            version_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            version_comment text not null,          
            version_public int unsigned not null default 0,            
            version_user int unsigned not null default 0
        )";		
        echo traite_rqt($rqt,"create table cms_version"); 
               
		$rqt = "alter table cms_build add build_version_num int not null default 0 after id_build";
		echo traite_rqt($rqt,"alter table cms_build add build_version_num");		
		
		//id du cms à utiliser en Opac
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='cms' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param)
				VALUES (0, 'opac', 'cms', 0,'id du CMS utilisé en OPAC','a_general')";
			echo traite_rqt($rqt,"insert opac_cms into parametres");
		}
        
		//DG - Colonnes exemplaires affichées en gestion
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='expl_data' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param) 
				VALUES (0, 'pmb', 'expl_data', 'expl_cb,expl_cote,location_libelle,section_libelle,statut_libelle,tdoc_libelle', 'Colonne des exemplaires, dans l\'ordre donné, séparé par des virgules : expl_cb,expl_cote,location_libelle,section_libelle,statut_libelle,tdoc_libelle #n : id des champs personnalisés \r\n expl_cb est obligatoire et sera ajouté si absent','')";
			echo traite_rqt($rqt,"insert pmb_expl_data=expl_cb,expl_cote,location_libelle,section_libelle,statut_libelle,codestat_libelle,lender_libelle,tdoc_libelle into parametres");
		}
		
		//DB - parametre gestion de monopole de pret 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='expl_display_location_without_expl' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) 
				VALUES (0, 'pmb', 'expl_display_location_without_expl', '0', 'Affichage de la liste des localisations sans exemplaire\n 0: Non\n 1: oui')";
			echo traite_rqt($rqt,"insert pmb_expl_display_location_without_expl=0 into parametres");
		}				
		
		// Voir les prets de son groupe de lecteur
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_group_checkout' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param) 
				VALUES (0, 'opac', 'show_group_checkout', '0', 'Le responsable du groupe de lecteur voit les prêts de son groupe\n 0: Non\n 1: oui','a_general')";
			echo traite_rqt($rqt,"insert opac_show_group_checkout=0 into parametres");
		}	
			
		// Archivage DSI  
		$rqt="create table if not exists dsi_archive (
           	num_banette_arc int unsigned not null default 0,
            num_notice_arc int unsigned not null default 0,
            date_diff_arc date not null default '0000-00-00',
            primary key (num_banette_arc,num_notice_arc,date_diff_arc)
        )";	
		echo traite_rqt($rqt,"create table dsi_archive"); 
	
		//Nombre d'archive à mémoriser en dsi
		$rqt = "ALTER TABLE bannettes ADD archive_number INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add archive_number");	

		//AR - Erreur dans le type de colonne
		$rqt = "ALTER TABLE cms_pages MODIFY page_hash varchar(255) ";
		echo traite_rqt($rqt,"ALTER TABLE exemplaires MODIFY expl_cote varchar(255) ");
		
		//AR - L'authentification Digest impose une valeur en clair...
		$rqt= "alter table users add user_digest varchar(255) not null default '' after pwd";
		echo traite_rqt($rqt,"alter table users add user_digest");

		//Ajout de deux paramètres pour la navigation par facette
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='facette_in_bandeau_2' "))==0){
			$rqt = "insert into parametres values(0,'opac','facette_in_bandeau_2',0,'La navigation par facettes apparait dans le bandeau ou dans le bandeau 2\n0 : dans le bandeau\n1 : Dans le bandeau 2','c_recherche',0)";
			echo traite_rqt($rqt,"insert opac_facette_in_bandeau_2=0 into parametres");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='autolevel2' "))==0){
			$rqt = "insert into parametres values(0,'opac','autolevel2',0,'0 : mode normal de recherche\n1 : Affiche directement le résultat de la recherche tous les champs sans passer par la présentation du niveau 1 de recherche','c_recherche',0)";
			echo traite_rqt($rqt,"insert opac_autolevel2=0 into parametres");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='first_page_params' "))==0){
			$rqt = "insert into parametres values(0,'opac','first_page_params','','Structure Json récapitulant les paramètres à initialiser pour la page d\\'accueil :\nExemple : \n{\n\"lvl\":\"cmspage\",\n\"pageid\":2\n}','b_aff_general',0)";
			echo traite_rqt($rqt,"insert opac_first_page_params='' into parametres");
		}
						
		$rqt = "ALTER TABLE cms_build ADD build_type varchar(255) not null default 'cadre' AFTER build_version_num";
		echo traite_rqt($rqt,"ALTER TABLE cms_build ADD build_type");
		
		//Création d'un div class raw
		$rqt = "ALTER TABLE cms_build ADD build_div INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table cms_build add build_div");	

		// Ajout tpl de notice pour générer le header
		$rqt = "update parametres set comment_param = 'Format d\'affichage des réduits de notices :\n 0 = titre+auteur principal\n 1 = titre+auteur principal+date édition\n 2 = titre+auteur principal+date édition + ISBN\n 3 = titre seul\n P 1,2,3 = tit+aut+champs persos id 1 2 3\n E 1,2,3 = tit+aut+édit+champs persos id 1 2 3\n T = tit1+tit4\n 4 = titre+titre parallèle+auteur principal\n H 1 = id d\'un template de notice' where type_param='opac' and sstype_param='notice_reduit_format'";
		echo traite_rqt($rqt,"update parametre opac_notice_reduit_format");
				
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.08");
		break;

	case "v5.08":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		set_time_limit(0);
		mysql_query("set wait_timeout=28800");
		
		//AR - paramètre activant les liens vers les documents numériques non visibles
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_links_invisible_docnums' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'opac', 'show_links_invisible_docnums', '0',
			'Afficher les liens vers les documents numériques non visible en mode non connecté. (Ne fonctionne pas avec les droits d\'accès).\n 0 : Non.\n1 : Oui.',
			'e_aff_notice',0) ";
			echo traite_rqt($rqt, "insert opac_show_links_invisible_docnums into parameters");
		}
		
		// Générer un document (dsi)
		$rqt = "ALTER TABLE bannettes ADD document_generate INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add document_generate");
		
		// Template de notice en génération de document (dsi)
		$rqt = "ALTER TABLE bannettes ADD document_notice_tpl INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add document_notice_tpl");
		
		// Générer un document avec les doc num (dsi)
		$rqt = "ALTER TABLE bannettes ADD document_insert_docnum INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add document_insert_docnum");
		
		// Grouper les documents (dsi)
		$rqt = "ALTER TABLE bannettes ADD document_group INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add document_group");
		
		// Ajouter un sommaire (dsi)
		$rqt = "ALTER TABLE bannettes ADD document_add_summary INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add document_add_summary");
		
		//DG - Index
		$rqt = "alter table explnum drop index explnum_repertoire";
		echo traite_rqt($rqt,"alter table explnum drop index explnum_repertoire");
		$rqt = "alter table explnum add index explnum_repertoire(explnum_repertoire)";
		echo traite_rqt($rqt,"alter table explnum add index explnum_repertoire");	
		
		// Ajout du module template de mail
        $rqt="create table if not exists mailtpl (
            id_mailtpl int unsigned not null auto_increment primary key,
            mailtpl_name varchar(255) not null default '',   
            mailtpl_objet varchar(255) not null default '',     
            mailtpl_tpl text not null, 
            mailtpl_users varchar(255) not null default ''        
        	)";
        echo traite_rqt($rqt,"create table mailtpl");
		
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_folder' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'pmb', 'img_folder', '',	'Répertoire de stockage des images', '', 0) ";
			echo traite_rqt($rqt, "insert pmb_img_folder into parameters");
		}			
				
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_url' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
					VALUES (0, 'pmb', 'img_url', '',	'URL d\'accès du répertoire des images (pmb_img_folder)', '', 0) ";
			echo traite_rqt($rqt, "insert pmb_img_url into parameters");
		}			
		// Ajout de la possibilité de joindre les images dans le mail ( pmb_mail_html_format=2 )
		$rqt = "update parametres set comment_param = 'Format d\'envoi des mails à partir de l\'opac: \n 0: Texte brut\n 1: HTML \n 2: HTML, images incluses\nAttention, ne fonctionne qu\'en mode d\'envoi smtp !' where type_param='pmb' and sstype_param='mail_html_format'";
		echo traite_rqt($rqt,"update parametre pmb_mail_html_format");
		
		// Ajout de la possibilité de joindre les images dans le mail ( opac_mail_html_format=2 )
		$rqt = "update parametres set comment_param = 'Format d\'envoi des mails à partir de l\'opac: \n 0: Texte brut\n 1: HTML \n 2: HTML, images incluses\nAttention, ne fonctionne qu\'en mode d\'envoi smtp !' where type_param='opac' and sstype_param='mail_html_format'";
		echo traite_rqt($rqt,"update parametre opac_mail_html_format");

		//AR - Ajout d'une colonne pour marquer un set comme étant en cours de rafraississement
		$rqt = "alter table connectors_out_sets add being_refreshed int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table connectors_out_sets add bien_refreshed");
		
		//DG - Infobulle lors du survol des vignettes (gestion)
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='book_pics_msg' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param) VALUES (0, 'pmb', 'book_pics_msg', '', 'Message sur le survol des vignettes des notices correspondant au chemin fourni par le paramètre book_pics_url','')";
			echo traite_rqt($rqt,"insert pmb_book_pics_msg='' into parametres");
		}

		//DG - Infobulle lors du survol des vignettes (opac)
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='book_pics_msg' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param) VALUES (0, 'opac', 'book_pics_msg', '', 'Message sur le survol des vignettes des notices correspondant au chemin fourni par le paramètre book_pics_url','e_aff_notice')";
			echo traite_rqt($rqt,"insert opac_book_pics_msg='' into parametres");
		}
				
		//AR - Utilisation des quotas pour la définition des vues disponibles pour un emprunteur
		$rqt = "create table if not exists quotas_opac_views (
			quota_type int(10) unsigned not null default 0,
			constraint_type varchar(255) not null default '',
			elements int(10) unsigned not null default 0,
			value text not null,
			primary key(quota_type,constraint_type,elements)
		)";
		echo traite_rqt($rqt,"create table quotas_opac_views");
		
		//AR - table de mots
		$rqt = "create table if not exists words (
			id_word int unsigned not null auto_increment primary key,
			word varchar(255) not null default '',
			lang varchar(10) not null default '',
			unique i_word_lang (word,lang)
		)";
		echo traite_rqt($rqt,"create table words");
				
		$rqt = "show fields from notices_mots_global_index";
		$res = mysql_query($rqt);
		$exists = false;
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				if($row->Field == "num_word"){
					$exists = true;
					break;
				}
			}
		}
		if(!$exists){
			//la méthode du chef reste la meilleure
			set_time_limit(0);
			//on ajoute un index bien pratique...
			$rqt ="alter table notices_mots_global_index add index mot_lang(mot,lang)";
			echo traite_rqt($rqt,"alter table notices_mots_global_index add index");
			
			//remplissage de la table mots
			$rqt ="insert into words (word,lang) select distinct mot,lang from notices_mots_global_index";
			echo traite_rqt($rqt,"insert into words");
			
			//on utilise une table tampon
			$rqt ="create table transition select id_notice,code_champ,code_ss_champ,mot,id_word from notices_mots_global_index join words on (mot=word and notices_mots_global_index.lang=words.lang);";
			echo traite_rqt($rqt,"create table transition");
			//on y ajoute les index qui vont bien
			$rqt ="alter table transition add primary key (id_notice,code_champ,code_ss_champ,mot)";
			echo traite_rqt($rqt,"alter table transition add primary key");
			
			//on ajout la clé étrangère num_word dans notices_mots_global_index
			$rqt ="alter table notices_mots_global_index add num_word int(10) unsigned not null default 0 after mot";
			echo traite_rqt($rqt,"alter table notices_mots_global_index add num_word");		
			//on l'affecte
			$rqt ="update notices_mots_global_index as a0 join transition as a1 on (a0.id_notice=a1.id_notice and a0.code_champ=a1.code_champ and a0.code_ss_champ=a1.code_ss_champ and a0.mot=a1.mot) set num_word=id_word";
			echo traite_rqt($rqt,"update notices_mots_global_index set num_word=id_word");		
			
			//on peut se passer de certains index et mettre les nouveaux
			$rqt ="drop index i_mot on notices_mots_global_index";
			echo traite_rqt($rqt,"drop index i_mot on notices_mots_global_index");
			$rqt ="drop index i_id_mot on notices_mots_global_index";
			echo traite_rqt($rqt,"drop index i_id_mot on notices_mots_global_index");
			$rqt ="alter table notices_mots_global_index add index i_id_mot(num_word,id_notice)";
			echo traite_rqt($rqt,"alter table notices_mots_global_index add index i_id_mot");			
			$rqt ="alter table notices_mots_global_index drop primary key";
			echo traite_rqt($rqt,"alter table notices_mots_global_index drop primary key");			
			$rqt ="alter table notices_mots_global_index add primary key (id_notice,code_champ,code_ss_champ,num_word,position)";
			echo traite_rqt($rqt,"alter table notices_mots_global_index add primary key");			
			
			//on supprime l'index pratique
			$rqt ="drop index mot_lang on notices_mots_global_index";
			echo traite_rqt($rqt,"drop index mot_lang on notices_mots_global_index");			
			
			//certains champs n'ont plus d'utilité dans notices_mots_global_index
			$rqt ="alter table notices_mots_global_index drop mot";
			echo traite_rqt($rqt,"alter table notices_mots_global_index drop mot");			
			$rqt ="alter table notices_mots_global_index drop nbr_mot";
			echo traite_rqt($rqt,"alter table notices_mots_global_index drop nbr_mot");		
			$rqt ="alter table notices_mots_global_index drop lang";
			echo traite_rqt($rqt,"alter table notices_mots_global_index drop lang");
			
			//on supprime l'index pratique
			//on supprime la table de transition
			$rqt ="drop table transition";
			echo traite_rqt($rqt,"drop table transition");			
		}	
	
		//AR - modification du paramètre de gestion des vues
		$rqt = "update parametres set comment_param = 'Activer les vues OPAC :\n 0 : non activé\n 1 : activé avec gestion classique\n 2 : activé avec gestion avancée' where type_param = 'pmb' and sstype_param = 'opac_view_activate'";
		echo traite_rqt($rqt,"update parametres pmb_opac_view_activate");
		
		//DB - modification du paramètre utiliser_calendrier
		$rqt = "update parametres set comment_param = 'Utiliser le calendrier des jours d\'ouverture ?\n 0 : non\n 1 : oui, pour le calcul des dates de retour et des retards\n 2 : oui, pour le calcul des dates de retour uniquement' where type_param = 'pmb' and sstype_param = 'utiliser_calendrier'";
		echo traite_rqt($rqt,"update parametres pmb_utiliser_calendrier");
			
		//NG - Ajout dans les statuts d'exemplaire la possibilité de rendre réservable ou non
		$rqt = "ALTER TABLE docs_statut ADD statut_allow_resa INT( 1 ) UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table docs_statut add statut_allow_resa");			
		$rqt = "UPDATE docs_statut set statut_allow_resa=1 where pret_flag=1 ";
		echo traite_rqt($rqt,"UPDATE docs_statut set statut_allow_resa=1 where pret_flag=1");			
			
		// Ajout CMS actif par défaut en Opac
		$rqt = "alter table cms add cms_opac_default int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table cms add cms_opac_default");	
		
		$rqt = "create table if not exists cms_editorial_types (
			id_editorial_type int unsigned not null auto_increment primary key,
			editorial_type_element varchar(20) not null default '',
			editorial_type_label varchar(255) not null default '',
			editorial_type_comment text not null
		)";
		echo traite_rqt($rqt,"create table cms_editorial_types");	
		
		//AR - on ajoute le type de contenu sur les tables cms_articles et cms_sections
		$rqt = "alter table cms_articles add article_num_type int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table cms_articles add article_num_type");	
		$rqt = "alter table cms_sections add section_num_type int unsigned not null default 0";
		echo traite_rqt($rqt,"alter table cms_sections add section_num_type");	
		
		//AR - Un type de contenu c'est quoi? c'est une définition de grille de champs perso
		$rqt = "create table if not exists cms_editorial_custom (
			idchamp int(10) unsigned NOT NULL auto_increment, 
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '', 
			titre varchar(255) default NULL, 
			type varchar(10) NOT NULL default 'text', 			
			datatype varchar(10) NOT NULL default '', 			
			options text, 
			multiple int(11) NOT NULL default 0, 
			obligatoire int(11) NOT NULL default 0, 
			ordre int(11) default NULL, 
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table cms_editorial_custom ");
		
		$rqt = "create table if not exists cms_editorial_custom_lists (
			cms_editorial_custom_champ int(10) unsigned NOT NULL default 0,
			cms_editorial_custom_list_value varchar(255) default NULL, 
			cms_editorial_custom_list_lib varchar(255) default NULL, 
			ordre int(11) default NULL, 
			KEY editorial_custom_champ (cms_editorial_custom_champ), 
			KEY editorial_champ_list_value (cms_editorial_custom_champ,cms_editorial_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists cms_editorial_custom_lists ");
		
		$rqt = "create table if not exists cms_editorial_custom_values (
			cms_editorial_custom_champ int(10) unsigned NOT NULL default 0, 
			cms_editorial_custom_origine int(10) unsigned NOT NULL default 0, 
			cms_editorial_custom_small_text varchar(255) default NULL, 
			cms_editorial_custom_text text, 
			cms_editorial_custom_integer int(11) default NULL, 
			cms_editorial_custom_date date default NULL, 
			cms_editorial_custom_float float default NULL, 
			KEY editorial_custom_champ (cms_editorial_custom_champ), 
			KEY editorial_custom_origine (cms_editorial_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists cms_editorial_custom_values ");
				
		//NG - Ajout de l'url permetant de retouver la page Opac contenant le cadre 
		$rqt = "alter table cms_cadres add cadre_url text not null ";
		echo traite_rqt($rqt,"alter table cms_cadre add cadre_url");
		
		//MB - Ajout d'une colonne pour les noeuds utilisables ou non en indexation
		$rqt = "ALTER TABLE noeuds ADD not_use_in_indexation INT( 1 ) UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table noeuds add not_use_in_indexation");

		//MB - Modification du commentaire du paramètre show_categ_browser
		$rqt = "UPDATE parametres SET comment_param = 'Affichage des catégories en page d\'accueil OPAC:\n0: Non\n1: Oui\n1 3,1: Oui, avec thésaurus id 3 puis 1 (préciser les thésaurus à afficher et l\'ordre)' where type_param = 'opac' and sstype_param = 'show_categ_browser'";
		echo traite_rqt($rqt,"update parametres show_categ_browser");
		
		//MB - Remplacement du code de lien d'autorité 2 par z car c'est le même libellé et z est normé
		$rqt = "UPDATE aut_link SET aut_link_type = 'z' where aut_link_type = '2' ";
		echo traite_rqt($rqt,"update aut_link");
		
		//AR indexons correctement le contenu éditorial
		$rqt = "create table if not exists cms_editorial_words_global_index(
			num_obj int unsigned not null default 0,
			type varchar(20) not null default '',
			code_champ int not null default 0,
			code_ss_champ int not null default 0,
			num_word int not null default 0,
			pond int not null default 100,
			position int not null default 1,
			primary key (num_obj,type,code_champ,code_ss_champ,num_word,position)
			
		)";
		echo traite_rqt($rqt,"create table cms_editorial_words_global_index ");
		
		$rqt = "create table if not exists cms_editorial_fields_global_index(
			num_obj int unsigned not null default 0,
			type varchar(20) not null default '',
			code_champ int(3) not null default 0,
			code_ss_champ int(3) not null default 0,
			ordre int(4) not null default 0,
			value text not null,
			pond int(4) not null default 100,
			lang varchar(10) not null default '',
			primary key(num_obj,type,code_champ,code_ss_champ,ordre),
			index i_value(value(300))
		)";
		echo traite_rqt($rqt,"create table cms_editorial_fields_global_index ");	
		
		//DB - parametre d'alerte avant affichage des documents numériques
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='visionneuse_alert_doctype' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'opac', 'visionneuse_alert_doctype', '', 'Liste des types de documents pour lesquels une alerte est générée (séparés par une virgule).', 'm_photo',0) ";
			echo traite_rqt($rqt, "insert opac_visionneuse_alert_doctype into parameters");
		}		

		$rqt = "alter table cms_cadres add cadre_memo_url int not null default 0 after cadre_url";
		echo traite_rqt($rqt,"alter table cms_cadres add cadre_memo_url");
		
		//DB - entrepot d'archivage à la suppression des notices
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='archive_warehouse' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'archive_warehouse', '0', 'Identifiant de l\'entrepôt d\'archivage à la suppression des notices.', '',0) ";
			echo traite_rqt($rqt, "insert archive_warehouse into parameters");
		}		
		
		$rqt = "alter table cms_cadres add cadre_classement  varchar(255) not null default ''";
		echo traite_rqt($rqt,"alter table cms_cadres add cadre_classement");
		
		//NG - Imprimante ticket 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='printer_name' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'printer_name', '', 'Nom de l\'imprimante de ticket de prêt, utilisant l\'applet jzebra. Le nom de l\'imprimante doit correspondre à la class développée spécifiquement pour la piloter.\nExemple: Nommer l\'imprimante \'metapace\' pour utiliser le driver classes/printer/metapace.class.php', '',0) ";
			echo traite_rqt($rqt, "insert pmb_printer_name into parameters");
		}		

		//DG - Localisation par défaut sur la visualisation des réservations
		$rqt = "ALTER TABLE users ADD deflt_resas_location int(6) UNSIGNED DEFAULT 0 after deflt_collstate_location";
		echo traite_rqt($rqt,"ALTER TABLE users ADD deflt_resas_location after deflt_collstate_location");

		//DG - parametre localisation des groupes de lecteurs
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='groupes_localises' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) 
				VALUES (0, 'empr', 'groupes_localises', '0', 'Groupes de lecteurs localisés par rapport au responsable \n0: Non \n1: oui')";
			echo traite_rqt($rqt,"insert empr_groupes_localises=0 into parametres");
		}
		
		// Activation des recherches similaires 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='allow_simili_search' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'allow_simili_search', '0', 'Activer les recherches similaires en OPAC:\n 0 : non \n 1 : oui', 'c_recherche', '0')";
			echo traite_rqt($rqt,"insert opac_allow_simili_search='0' into parametres ");
		}
		
		//ajout d'une date de création pour les articles et les rubriques
		$rqt ="alter table cms_articles add article_creation_date date";
		echo traite_rqt($rqt,"alter table cms_articles add article_creation_date date");
		$rqt ="alter table cms_sections add section_creation_date date";
		echo traite_rqt($rqt,"alter table cms_sections add section_creation_date date");
				
		//index d'on se lève tous pour la bannette de Camille
		$rqt = "alter table bannette_abon drop index i_num_empr";
		echo traite_rqt($rqt,"alter table bannette_abon drop index i_num_empr");
		$rqt = "alter table bannette_abon add index i_num_empr(num_empr)";
		echo traite_rqt($rqt,"alter table bannette_abon add index i_num_empr(num_empr)");
		
		// MB - Modification du plus Opac devant les notices
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='notices_depliable_plus' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'notices_depliable_plus', 'plus.gif', 'Image à utiliser devant un titre de notice pliée', 'e_aff_notice', '0')";
			echo traite_rqt($rqt,"insert notices_depliable_plus into parametres ");
		}
		
		// MB - Modification du plus Opac devant les notices
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='notices_depliable_moins' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'notices_depliable_moins', 'minus.gif', 'Image à utiliser devant un titre de notice dépliée', 'e_aff_notice', '0')";
			echo traite_rqt($rqt,"insert notices_depliable_moins into parametres ");
		}
		
		//MB - Modification du commentaire du paramètre notices_depliable
		$rqt = "UPDATE parametres SET comment_param = 'Affichage dépliable des notices en résultat de recherche:\n0: Non dépliable\n1: Dépliable en cliquant que sur l\'icone\n2: Déplibable en cliquant sur toute la ligne du titre' where type_param = 'opac' and sstype_param = 'notices_depliable'";
		echo traite_rqt($rqt,"update parametres notices_depliable");
		
		// Ajout du regroupement d'exemplaires pour le prêt
		$rqt = "create table if not exists groupexpl (
			id_groupexpl int(10) unsigned NOT NULL auto_increment, 
			groupexpl_resp_expl_num int(10) unsigned NOT NULL default 0, 
			groupexpl_name varchar(255) NOT NULL default '', 
			groupexpl_comment varchar(255) NOT NULL default '', 
			groupexpl_location int(10) unsigned NOT NULL default 0, 
			groupexpl_statut_resp int(10) unsigned NOT NULL default 0, 
			groupexpl_statut_others int(10) unsigned NOT NULL default 0, 
			PRIMARY KEY (id_groupexpl)) ";
		echo traite_rqt($rqt,"create table groupexpl ");
		
		// Ajout du regroupement d'exemplaires pour le prêt
		$rqt = "create table if not exists groupexpl_expl (
			groupexpl_num int(10) unsigned NOT NULL  default 0, 
			groupexpl_expl_num int(10) unsigned NOT NULL  default 0, 
			groupexpl_checked int unsigned NOT NULL  default 0, 
			PRIMARY KEY (groupexpl_num, groupexpl_expl_num)) ";
		echo traite_rqt($rqt,"create table groupexpl_expl ");
		
		// Activation du prêt d'exemplaires groupés
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='pret_groupement' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'pmb', 'pret_groupement', '0', 'Activer le prêt d\'exemplaires regroupés en un seul lot. La gestion des groupes se gére en Circulation / Groupe d\'exemplaires :\n 0 : non \n 1 : oui', '', '0')";
			echo traite_rqt($rqt,"insert pmb_pret_groupement='0' into parametres ");
		}
		
		//AR - refonte éditions...
		$rqt = "create table if not exists editions_states (
			id_editions_state int unsigned not null auto_increment primary key,
			editions_state_name varchar(255) not null default '',
			editions_state_num_classement int not null default 0,
			editions_state_used_datasource varchar(50) not null default '',
			editions_state_comment text not null,
			editions_state_fieldslist text not null,
			editions_state_fieldsparams text not null
		)";
		echo traite_rqt($rqt,"create table if not exists editions_states");
		
		// cms: Classement des pages
		$rqt = "alter table cms_pages add page_classement  varchar(255) not null default ''";
		echo traite_rqt($rqt,"alter table cms_pages add page_classement");		
		
		// Transfert: regroupement des départs
		if (mysql_num_rows(mysql_query("SELECT 1 FROM parametres WHERE type_param= 'transferts' and sstype_param='regroupement_depart' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
					VALUES (0, 'transferts', 'regroupement_depart', '0', '1', 'Active le regroupement des départs\n 0: Non \n 1: Oui') ";
			echo traite_rqt($rqt,"INSERT transferts_regroupement_depart INTO parametres") ;
		}
		
		//index Camille (comment ça encore ?)
		$rqt = "alter table coordonnees drop index i_num_entite";
		echo traite_rqt($rqt,"alter table coordonnees drop index i_num_entite");
		$rqt = "alter table coordonnees add index i_num_entite (num_entite)";
		echo traite_rqt($rqt,"alter table coordonnees add index i_num_entite (num_entite)");
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.09");
		break;

	case "v5.09":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		set_time_limit(0);
		mysql_query("set wait_timeout=28800");
		
		//AR - On revoit une clé primaire
		$rqt ="alter table notices_fields_global_index drop primary key";
		echo traite_rqt($rqt,"alter table notices_fields_global_index drop primary key");	
		$rqt ="alter table notices_fields_global_index add primary key(id_notice,code_champ,code_ss_champ,lang,ordre)";
		echo traite_rqt($rqt,"alter table notices_fields_global_index add primary key(id_notice,code_champ,code_ss_champ,lang,ordre)");
		
		//AR - ajout du partitionnement de manière systématique
		$rqt="show table status where name='notices_mots_global_index' or name='notices_fields_global_index'";
		$result = mysql_query($rqt);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				if($row->Create_options != "partitioned"){
					$rqt="alter table ".$row->Name." partition by key(code_champ,code_ss_champ) partitions 50";
					echo traite_rqt($rqt,"alter table ".$row->Name." partition by key");
				}
			}
		}

		// RFID: ajout de la gestion de l'antivol par afi
		if (mysql_num_rows(mysql_query("SELECT 1 FROM parametres WHERE type_param= 'pmb' and sstype_param='rfid_afi_security_codes' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
			VALUES (0, 'pmb', 'rfid_afi_security_codes', '', '0', 'Gestion de l\'antivol par le registre AFI.\nLa première valeur est celle de l\'antivol actif, la deuxième est celle de l\antivol inactif.\nExemple: 07,C2  ') ";
			echo traite_rqt($rqt,"INSERT pmb_rfid_afi_security_codes INTO parametres") ;
		}		
		
		// CMS: ajout de l'url de construction de l'opac
		if (mysql_num_rows(mysql_query("SELECT 1 FROM parametres WHERE type_param= 'pmb' and sstype_param='url_base_cms_build' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
			VALUES (0, 'pmb', 'url_base_cms_build', '', '0', 'url de construction du CMS de l\'OPAC') ";
			echo traite_rqt($rqt,"INSERT pmb_url_base_cms_build INTO parametres") ;
		}		
		
		//AR - on stocke le double metaphone de chaque mot !
		$rqt = "alter table words add double_metaphone varchar(255) not null default ''";
		echo traite_rqt($rqt,"alter table words add double_metaphone");
		$rqt = "alter table words add stem varchar(255) not null default ''";
		echo traite_rqt($rqt,"alter table words add stem");
		//AR - Suggestions de mots dans la saisie en recherche simple
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='simple_search_suggestions' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','simple_search_suggestions','0','Activer la suggestion de mots en recherche simple via la complétion\n0 : Désactiver\n1 : Activer\n\nNB : Cette fonction nécessite l\'installation de l\'extension levenshtein dans MySQL','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_simple_search_suggestions into parametres") ;
		}
		
		//AR - Suggestions de mots dans la saisie en recherche simple
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='stemming_active' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'opac','stemming_active','0','Activer le stemming dans la recherche\n0 : Désactiver\n1 : Activer\n','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_stemming_active into parametres") ;
		}
		
		$rqt = "delete from parametres where sstype_param like 'url_base_cms_build%' " ;
		$res = mysql_query($rqt, $dbh) ;
		$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
		VALUES (0, 'cms', 'url_base_cms_build', '', '0', 'url de construction du CMS de l\'OPAC') ";
		echo traite_rqt($rqt,"INSERT pmb_url_base_cms_build INTO parametres") ;

		//DG - Modification de la taille du champ content_infopage de la table infopages
		$rqt = "ALTER TABLE infopages MODIFY content_infopage longblob NOT NULL default ''";
		echo traite_rqt($rqt,"alter table infopages modify content_infopage");
		
		//DG - Modification du commentaire du paramètre pmb_blocage_delai
		$rqt = "UPDATE parametres SET comment_param = 'Délai à partir duquel le retard est pris en compte pour le blocage' where type_param = 'pmb' and sstype_param = 'blocage_delai'";
		echo traite_rqt($rqt,"update parametres pmb_blocage_delai");

		$rqt = "delete from parametres where sstype_param like 'url_base_cms_build%' " ;
		$res = mysql_query($rqt, $dbh) ;
		$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
		VALUES (0, 'cms', 'url_base_cms_build', '', '0', 'url de construction du CMS de l\'OPAC') ";
		echo traite_rqt($rqt,"INSERT pmb_url_base_cms_build INTO parametres") ;

		
		//index Camille (c'est que le début d'accord d'accord ?)
		$rqt = "alter table resa drop index i_idbulletin";
		echo traite_rqt($rqt,"alter table resa drop index i_idbulletin");
		$rqt = "alter table resa add index i_idbulletin (resa_idbulletin)";
		echo traite_rqt($rqt,"alter table resa add index i_idbulletin (resa_idbulletin)");
		
		$rqt = "alter table resa drop index i_idnotice";
		echo traite_rqt($rqt,"alter table resa drop index i_idnotice");
		$rqt = "alter table resa add index i_idnotice (resa_idnotice)";
		echo traite_rqt($rqt,"alter table resa add index i_idnotice (resa_idnotice)");
				
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.10");
		break;

	case "v5.10":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		//AR - ajout de type de contenu générique pour les articles et rubriques...
		if(!mysql_num_rows(mysql_query("select id_editorial_type from cms_editorial_types where editorial_type_element  ='article_generic'"))){
			$rqt = "insert into cms_editorial_types set editorial_type_element = 'article_generic', editorial_type_label ='CP pour Article'";
			echo traite_rqt($rqt,"insert into cms_editorial_types set editorial_type_element = 'article_generic'") ;
			$rqt = "insert into cms_editorial_types set editorial_type_element = 'section_generic', editorial_type_label ='CP pour Rubrique'";
			echo traite_rqt($rqt,"insert into cms_editorial_types set editorial_type_element = 'section_generic'") ;
		}
		
		//DG - Ajout du champ index_libelle dans la table frais
		$rqt = "ALTER TABLE frais ADD index_libelle TEXT";
		echo traite_rqt($rqt,"alter table frais add index_libelle");
		
		//DG - Paramètres pour les lettres de retard par groupe
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pdflettreretard' and sstype_param='1before_list_group' "))==0){
			$rqt = "select valeur_param,comment_param from parametres where type_param= 'pdflettreretard' and sstype_param='1before_list' ";
			$res = mysql_query($rqt);
			$value_param = mysql_result($res,0,0);
			$comment_param = mysql_result($res,0,1);
			$rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param,comment_param) VALUES ('pdflettreretard', '1before_list_group', '".addslashes($value_param)."', '".addslashes($comment_param)."') " ;
			echo traite_rqt($rqt,"insert pdflettreretard,1before_list_group into parametres");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pdflettreretard' and sstype_param='1after_list_group' "))==0){
			$rqt = "select valeur_param,comment_param from parametres where type_param= 'pdflettreretard' and sstype_param='1after_list' ";
			$res = mysql_query($rqt);
			$value_param = mysql_result($res,0,0);
			$comment_param = mysql_result($res,0,1);
			$rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param,comment_param) VALUES ('pdflettreretard', '1after_list_group', '".addslashes($value_param)."', '".addslashes($comment_param)."') " ;
			echo traite_rqt($rqt,"insert pdflettreretard,1after_list_group into parametres");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pdflettreretard' and sstype_param='1fdp_group' "))==0){
			$rqt = "select valeur_param,comment_param from parametres where type_param= 'pdflettreretard' and sstype_param='1fdp' ";
			$res = mysql_query($rqt);
			$value_param = mysql_result($res,0,0);
			$comment_param = mysql_result($res,0,1);
			$rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param,comment_param) VALUES ('pdflettreretard', '1fdp_group', '".addslashes($value_param)."', '".addslashes($comment_param)."') " ;
			echo traite_rqt($rqt,"insert pdflettreretard,1fdp_group into parametres");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pdflettreretard' and sstype_param='1madame_monsieur_group' "))==0){
			$rqt = "select valeur_param,comment_param from parametres where type_param= 'pdflettreretard' and sstype_param='1madame_monsieur' ";
			$res = mysql_query($rqt);
			$value_param = mysql_result($res,0,0);
			$comment_param = mysql_result($res,0,1);
			$rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param,comment_param) VALUES ('pdflettreretard', '1madame_monsieur_group', '".addslashes($value_param)."', '".addslashes($comment_param)."') " ;
			echo traite_rqt($rqt,"insert pdflettreretard,1madame_monsieur_group into parametres");
		}
		
		//DG - Impression du nom du groupe sur la lettre de rappel
		$rqt = "ALTER TABLE groupe ADD lettre_rappel_show_nomgroup INT( 1 ) UNSIGNED DEFAULT 0 NOT NULL ";
		echo traite_rqt($rqt,"ALTER TABLE groupe ADD lettre_rappel_show_nomgroup default 0");
		$rqt = "update groupe set lettre_rappel_show_nomgroup=lettre_rappel ";
		echo traite_rqt($rqt,"update groupe set lettre_rappel_show_nomgroup=lettre_rappel");
		
		//AR - Ajout des extensions de formulaire pour les types de contenus
		$rqt = "alter table cms_editorial_types add editorial_type_extension text not null"; 
		echo traite_rqt($rqt,"alter table cms_editorial_types add editorial_type_extension");
		
		//AR - Ajout de la table de stockages des infos des extension
		$rqt = "create table cms_modules_extensions_datas (
			id_extension_datas int(10) not null auto_increment primary key,
			extension_datas_module varchar(255) not null default '',
			extension_datas_type varchar(255) not null default '',
			extension_datas_type_element varchar(255) not null default '',
			extension_datas_num_element int(10) not null default 0,
			extension_datas_datas blob
		)";
		echo traite_rqt($rqt,"create table cms_modules_extensions_datas");
		
		//NG - Ordre des facettes
		$rqt = "alter table facettes add facette_order int not null default 1";
		echo traite_rqt($rqt,"alter table facettes add facette_order");
		//NG - limit_plus des facettes
		$rqt = "alter table facettes add facette_limit_plus int not null default 0";
		echo traite_rqt($rqt,"alter table facettes add facette_limit_plus");
							
		//MB - Modification de l'identifiant 28 en 1 pour le trie car il est présent en double dans sort.xml
		$rqt = "update parametres set valeur_param=REPLACE(valeur_param, '_28', '_1') WHERE type_param='opac' AND sstype_param='default_sort' AND valeur_param REGEXP '_28[^0-9]|_28$'";
		echo traite_rqt($rqt,"update param opac_default_sort");
		
		//NG pb de placement de main_hors_footer et footer
		$rqt = "update cms_build set build_parent='main' where build_obj='main_header' or build_obj='main_hors_footer' or build_obj='footer' ";
		echo traite_rqt($rqt,"update cms_build set build_parent");

		//NG pb de placement des zones du contener
		$rqt = "update cms_build set build_child_before='', build_child_after='intro' where build_obj='main' ";
		echo traite_rqt($rqt,"update cms_build where build_obj='main'");
		$rqt = "update cms_build set build_child_before='main', build_child_after='bandeau' where build_obj='intro' ";
		echo traite_rqt($rqt,"update cms_build where build_obj='intro'");
		$rqt = "update cms_build set build_child_before='intro', build_child_after='bandeau_2' where build_obj='bandeau' ";
		echo traite_rqt($rqt,"update cms_build  where build_obj='bandeau'");
		$rqt = "update cms_build set build_child_before='bandeau', build_child_after='' where build_obj='bandeau_2' ";
		echo traite_rqt($rqt,"update cms_build where build_obj='bandeau_2' ");
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.11");
		break;

	case "v5.11":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		//NG Ajout param opac_show_bandeau_2
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_bandeau_2' "))==0){
			$rqt = "select valeur_param from parametres where type_param= 'opac' and sstype_param='show_bandeaugauche' ";
			$res = mysql_query($rqt);
			$value_param = mysql_result($res,0,0);
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'opac', 'show_bandeau_2', '".addslashes($value_param)."', 'Affichage du bandeau_2 ? \n 0 : Non\n 1 : Oui', 'f_modules', 0) " ;
			echo traite_rqt($rqt,"insert opac_show_bandeau_2=opac_show_bandeaugauche into parametres");
		}
		
		//NG ajout de field_position dans notices_mots_global_index
		$rqt = "alter table notices_mots_global_index add field_position int not null default 1";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add field_position");
		
		//abacarisse en attente
		if (mysql_num_rows(mysql_query("select id_param from parametres where type_param= 'opac' and sstype_param='param_social_network' "))==0){
			//Ajout du paramètre de configuration de l'api addThis
			$rqt = "INSERT INTO parametres (type_param ,sstype_param ,valeur_param ,comment_param ,section_param ,gestion) VALUES ('opac', 'param_social_network',
			'{
			\"token\":\"ra-4d9b1e202c30dea1\",
			\"version\":\"300\",
			\"buttons\":[
			{
			\"attributes\":{
			\"class\":\"addthis_button_facebook_like\",
			\"fb:like:layout\":\"button_count\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_button_tweet\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_counter addthis_button_compact\"
			}
			}
			],
			\"toolBoxParams\":{
			\"class\":\"addthis_toolbox addthis_default_style\"
			},
			\"addthis_share\":{
			
			},
			\"addthis_config\":{
			\"data_track_clickback\":\"true\",
			\"ui_click\":\"true\"
			}
			}
			', 'Tableau de paramètrage de l\'API de gestion des interconnexions aux réseaux sociaux.
			Au format JSON.
			Exemple :
			{
			\"token\":\"ra-4d9b1e202c30dea1\",
			\"version\":\"300\",
			\"buttons\":[
			{
			\"attributes\":{
			\"class\":\"addthis_button_preferred_1\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_button_preferred_2\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_button_preferred_3\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_button_preferred_4\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_button_compact\"
			}
			},
			{
			\"attributes\":{
			\"class\":\"addthis_counter addthis_bubble_style\"
			}
			}
			],
			\"toolBoxParams\":{
			\"class\":\"addthis_toolbox addthis_default_style addthis_32x32_style\"
			},
			\"addthis_share\":{
			
			},
			\"addthis_config\":{
			\"data_track_addressbar\":true
			}
			}', 'e_aff_notice', '0'
			)";
			echo traite_rqt($rqt,"insert opac_param_social_network into parametres");
		}
		
		// DG 
		//ajout du champ groupe_lecteurs dans la table bannettes
		$rqt = "ALTER TABLE bannettes ADD groupe_lecteurs INT(8) UNSIGNED NOT NULL default 0";
		echo traite_rqt($rqt,"alter table bannettes add groupe_lecteurs");
	
		// JP
		$rqt = "update parametres set comment_param='Tri par défaut des recherches OPAC. Deux possibilités :\n- un seul tri par défaut de la forme c_num_6\n- plusieurs tris par défaut de la forme c_num_6|Libelle;d_text_7|Libelle 2;c_num_5|Libelle 3\n\nc pour croissant, d pour décroissant\nnum ou text pour numérique ou texte\nidentifiant du champ (voir fichier xml sort.xml)\nlibellé du tri si plusieurs' WHERE type_param='opac' AND sstype_param='default_sort'";
		echo traite_rqt($rqt,"update comment for param opac_default_sort");
		
		// Transfert: statut non pretable pour les expl en demande de transfert
		if (mysql_num_rows(mysql_query("SELECT 1 FROM parametres WHERE type_param= 'transferts' and sstype_param='pret_demande_statut' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
			VALUES (0, 'transferts', 'pret_demande_statut', '0', '1', 'Appliquer ce statut avant la validation') ";
			echo traite_rqt($rqt,"INSERT transferts_pret_demande_statut INTO parametres") ;
		}
			
		// descriptors in DSI
		$rqt = "create table if not exists bannettes_descriptors(
			num_bannette int not null default 0,
			num_noeud int not null default 0,
			bannette_descriptor_order int not null default 0,
			primary key (num_bannette,num_noeud)
		)";
		echo traite_rqt($rqt,"create table bannettes_descriptors") ;
		
		//ajout du champ bannette_mail dans bannette_abon
		$rqt = "ALTER TABLE bannette_abon ADD bannette_mail varchar(255) not null default '' ";
		echo traite_rqt($rqt,"alter table bannette_abon add bannette_mail");
		
		//AR - on a vu un cas ou ca se passe mal dans la 5.10, par précaution, on répète!
		if(!mysql_num_rows(mysql_query("select id_editorial_type from cms_editorial_types where editorial_type_element  ='article_generic'"))){
			$rqt = "insert into cms_editorial_types set editorial_type_element = 'article_generic', editorial_type_label ='CP pour Article'";
			echo traite_rqt($rqt,"insert into cms_editorial_types set editorial_type_element = 'article_generic'") ;
		}
		if(!mysql_num_rows(mysql_query("select id_editorial_type from cms_editorial_types where editorial_type_element  ='section_generic'"))){
			$rqt = "insert into cms_editorial_types set editorial_type_element = 'section_generic', editorial_type_label ='CP pour Rubrique'";
			echo traite_rqt($rqt,"insert into cms_editorial_types set editorial_type_element = 'section_generic'") ;
		}
		
		//DG - Augmentation de la taille du champ mention_date de la table bulletins
		$rqt = "ALTER TABLE bulletins MODIFY mention_date varchar(255) not null default ''";
		echo traite_rqt($rqt,"alter table bulletins modify mention_date");
		
		//DG - parametre pour l'affichage des notices de bulletins dans la navigation a2z
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='perio_a2z_show_bulletin_notice' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'opac', 'perio_a2z_show_bulletin_notice', '0', 'Affichage de la notice de bulletin dans le navigateur de périodiques', 'c_recherche',0) ";
			echo traite_rqt($rqt, "insert opac_perio_a2z_show_bulletin_notice=0 into parametres");
		}
		
		//DG - ajout d'un commentaire de gestion pour les suggestions
		$rqt = "ALTER TABLE suggestions ADD commentaires_gestion TEXT AFTER commentaires";
		echo traite_rqt($rqt,"alter table suggestions add commentaires_gestion");
		
		//NG - Champs perso author
		$rqt = "create table if not exists author_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table author_custom ");
		
		$rqt = "create table if not exists author_custom_lists (
			author_custom_champ int(10) unsigned NOT NULL default 0,
			author_custom_list_value varchar(255) default NULL,
			author_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (author_custom_champ),
			KEY editorial_champ_list_value (author_custom_champ,author_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists author_custom_lists ");
		
		$rqt = "create table if not exists author_custom_values (
			author_custom_champ int(10) unsigned NOT NULL default 0,
			author_custom_origine int(10) unsigned NOT NULL default 0,
			author_custom_small_text varchar(255) default NULL,
			author_custom_text text,
			author_custom_integer int(11) default NULL,
			author_custom_date date default NULL,
			author_custom_float float default NULL,
			KEY editorial_custom_champ (author_custom_champ),
			KEY editorial_custom_origine (author_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists author_custom_values ");
		
		//NG - Champs perso categ
		$rqt = "create table if not exists categ_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table categ_custom ");
		
		$rqt = "create table if not exists categ_custom_lists (
			categ_custom_champ int(10) unsigned NOT NULL default 0,
			categ_custom_list_value varchar(255) default NULL,
			categ_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (categ_custom_champ),
			KEY editorial_champ_list_value (categ_custom_champ,categ_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists categ_custom_lists ");
		
		$rqt = "create table if not exists categ_custom_values (
			categ_custom_champ int(10) unsigned NOT NULL default 0,
			categ_custom_origine int(10) unsigned NOT NULL default 0,
			categ_custom_small_text varchar(255) default NULL,
			categ_custom_text text,
			categ_custom_integer int(11) default NULL,
			categ_custom_date date default NULL,
			categ_custom_float float default NULL,
			KEY editorial_custom_champ (categ_custom_champ),
			KEY editorial_custom_origine (categ_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists categ_custom_values ");
		
		//NG - Champs perso publisher
		$rqt = "create table if not exists publisher_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table publisher_custom ");
		
		$rqt = "create table if not exists publisher_custom_lists (
			publisher_custom_champ int(10) unsigned NOT NULL default 0,
			publisher_custom_list_value varchar(255) default NULL,
			publisher_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (publisher_custom_champ),
			KEY editorial_champ_list_value (publisher_custom_champ,publisher_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists publisher_custom_lists ");
		
		$rqt = "create table if not exists publisher_custom_values (
			publisher_custom_champ int(10) unsigned NOT NULL default 0,
			publisher_custom_origine int(10) unsigned NOT NULL default 0,
			publisher_custom_small_text varchar(255) default NULL,
			publisher_custom_text text,
			publisher_custom_integer int(11) default NULL,
			publisher_custom_date date default NULL,
			publisher_custom_float float default NULL,
			KEY editorial_custom_champ (publisher_custom_champ),
			KEY editorial_custom_origine (publisher_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists publisher_custom_values ");
		
		//NG - Champs perso collection
		$rqt = "create table if not exists collection_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table collection_custom ");
		
		$rqt = "create table if not exists collection_custom_lists (
			collection_custom_champ int(10) unsigned NOT NULL default 0,
			collection_custom_list_value varchar(255) default NULL,
			collection_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (collection_custom_champ),
			KEY editorial_champ_list_value (collection_custom_champ,collection_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists collection_custom_lists ");
		
		$rqt = "create table if not exists collection_custom_values (
			collection_custom_champ int(10) unsigned NOT NULL default 0,
			collection_custom_origine int(10) unsigned NOT NULL default 0,
			collection_custom_small_text varchar(255) default NULL,
			collection_custom_text text,
			collection_custom_integer int(11) default NULL,
			collection_custom_date date default NULL,
			collection_custom_float float default NULL,
			KEY editorial_custom_champ (collection_custom_champ),
			KEY editorial_custom_origine (collection_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists collection_custom_values ");
		
		//NG - Champs perso subcollection
		$rqt = "create table if not exists subcollection_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table subcollection_custom ");
		
		$rqt = "create table if not exists subcollection_custom_lists (
			subcollection_custom_champ int(10) unsigned NOT NULL default 0,
			subcollection_custom_list_value varchar(255) default NULL,
			subcollection_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (subcollection_custom_champ),
			KEY editorial_champ_list_value (subcollection_custom_champ,subcollection_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists subcollection_custom_lists ");
		
		$rqt = "create table if not exists subcollection_custom_values (
			subcollection_custom_champ int(10) unsigned NOT NULL default 0,
			subcollection_custom_origine int(10) unsigned NOT NULL default 0,
			subcollection_custom_small_text varchar(255) default NULL,
			subcollection_custom_text text,
			subcollection_custom_integer int(11) default NULL,
			subcollection_custom_date date default NULL,
			subcollection_custom_float float default NULL,
			KEY editorial_custom_champ (subcollection_custom_champ),
			KEY editorial_custom_origine (subcollection_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists subcollection_custom_values ");
		
		//NG - Champs perso serie
		$rqt = "create table if not exists serie_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table serie_custom ");
		
		$rqt = "create table if not exists serie_custom_lists (
			serie_custom_champ int(10) unsigned NOT NULL default 0,
			serie_custom_list_value varchar(255) default NULL,
			serie_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (serie_custom_champ),
			KEY editorial_champ_list_value (serie_custom_champ,serie_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists serie_custom_lists ");
		
		$rqt = "create table if not exists serie_custom_values (
			serie_custom_champ int(10) unsigned NOT NULL default 0,
			serie_custom_origine int(10) unsigned NOT NULL default 0,
			serie_custom_small_text varchar(255) default NULL,
			serie_custom_text text,
			serie_custom_integer int(11) default NULL,
			serie_custom_date date default NULL,
			serie_custom_float float default NULL,
			KEY editorial_custom_champ (serie_custom_champ),
			KEY editorial_custom_origine (serie_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists serie_custom_values ");
		
		//NG - Champs perso tu
		$rqt = "create table if not exists tu_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table tu_custom ");
		
		$rqt = "create table if not exists tu_custom_lists (
			tu_custom_champ int(10) unsigned NOT NULL default 0,
			tu_custom_list_value varchar(255) default NULL,
			tu_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (tu_custom_champ),
			KEY editorial_champ_list_value (tu_custom_champ,tu_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists tu_custom_lists ");
		
		$rqt = "create table if not exists tu_custom_values (
			tu_custom_champ int(10) unsigned NOT NULL default 0,
			tu_custom_origine int(10) unsigned NOT NULL default 0,
			tu_custom_small_text varchar(255) default NULL,
			tu_custom_text text,
			tu_custom_integer int(11) default NULL,
			tu_custom_date date default NULL,
			tu_custom_float float default NULL,
			KEY editorial_custom_champ (tu_custom_champ),
			KEY editorial_custom_origine (tu_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists tu_custom_values ");
		
		//NG - Champs perso indexint
		$rqt = "create table if not exists indexint_custom (
			idchamp int(10) unsigned NOT NULL auto_increment,
			num_type int unsigned not null default 0,
			name varchar(255) NOT NULL default '',
			titre varchar(255) default NULL,
			type varchar(10) NOT NULL default 'text',
			datatype varchar(10) NOT NULL default '',
			options text,
			multiple int(11) NOT NULL default 0,
			obligatoire int(11) NOT NULL default 0,
			ordre int(11) default NULL,
			search INT(1) unsigned NOT NULL DEFAULT 0,
			export INT(1) unsigned NOT NULL DEFAULT 0,
			exclusion_obligatoire INT(1) unsigned NOT NULL DEFAULT 0,
			pond int not null default 100,
			opac_sort INT NOT NULL DEFAULT 0,
			PRIMARY KEY  (idchamp)) ";
		echo traite_rqt($rqt,"create table indexint_custom ");
		
		$rqt = "create table if not exists indexint_custom_lists (
			indexint_custom_champ int(10) unsigned NOT NULL default 0,
			indexint_custom_list_value varchar(255) default NULL,
			indexint_custom_list_lib varchar(255) default NULL,
			ordre int(11) default NULL,
			KEY editorial_custom_champ (indexint_custom_champ),
			KEY editorial_champ_list_value (indexint_custom_champ,indexint_custom_list_value)) " ;
		echo traite_rqt($rqt,"create table if not exists indexint_custom_lists ");
		
		$rqt = "create table if not exists indexint_custom_values (
			indexint_custom_champ int(10) unsigned NOT NULL default 0,
			indexint_custom_origine int(10) unsigned NOT NULL default 0,
			indexint_custom_small_text varchar(255) default NULL,
			indexint_custom_text text,
			indexint_custom_integer int(11) default NULL,
			indexint_custom_date date default NULL,
			indexint_custom_float float default NULL,
			KEY editorial_custom_champ (indexint_custom_champ),
			KEY editorial_custom_origine (indexint_custom_origine)) " ;
		echo traite_rqt($rqt,"create table if not exists indexint_custom_values ");
	
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.12");
		break;

	case "v5.12":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+

		//DG - parametre pour forcer l'exécution des procédures
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='procs_force_execution' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
		VALUES (0, 'pmb', 'procs_force_execution', '0', 'Permettre le forçage de l\'exécution des procédures', '',0) ";
			echo traite_rqt($rqt, "insert pmb_procs_force_execution=0 into parametres");
			$rqt = "update users set rights=rights+131072 where rights<131072 and userid=1 ";
			echo traite_rqt($rqt, "update users add editions forcing rights where super user ");
		}
		
		//NG - ajout facette en dsi
		$rqt = "ALTER TABLE bannettes ADD group_type int unsigned NOT NULL default 0 AFTER notice_tpl";
		echo traite_rqt($rqt,"alter table bannettes add group_type");
		
		$rqt = "CREATE TABLE if not exists bannette_facettes (
			num_ban_facette int unsigned NOT NULL default 0,
			ban_facette_critere int(5) not null default 0,
			ban_facette_ss_critere int(5) not null default 0,
			ban_facette_order int(1) not null default 0,
			KEY bannette_facettes_key (num_ban_facette,ban_facette_critere,ban_facette_ss_critere)) " ;
		echo traite_rqt($rqt,"CREATE TABLE bannette_facettes");	
		
		//DB - L'authentification Digest impose une valeur, ce qui n'est pas le cas avec une authentification externe
		$rqt= "alter table empr add empr_digest varchar(255) not null default '' after empr_password";
		echo traite_rqt($rqt,"alter table empr add empr_digest");		
		
		//AB
		$rqt = "UPDATE users SET value_deflt_relation=CONCAT(value_deflt_relation,'-up') WHERE value_deflt_relation!='' AND value_deflt_relation NOT LIKE '%-%'";
		echo traite_rqt($rqt, 'UPDATE users SET value_deflt_relation=CONCAT(value_deflt_relation,"-up")');
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.13");
		break;
		
	case "v5.13":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		// +-------------------------------------------------+
		
		//AB parametre OPAC pour activer ou non le drag and drop si notice_depliable != 2
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='draggable' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (0, 'opac', 'draggable', '1', 'Permet d\'activer le glisser déposer dans le panier pour l\'affichage des notices à l\'OPAC', 'e_aff_notice',0) ";
			echo traite_rqt($rqt, "insert opac_draggable=1 into parametres");
		}
		
		//DG - Modification de la longueur du champ description de la table opac_liste_lecture
		$rqt = "ALTER TABLE opac_liste_lecture MODIFY description TEXT ";
		echo traite_rqt($rqt,"alter table opac_liste_lecture modify description");
		
		//DB - Ajout d'un champ timestamp dans la table acces_user_2
		@mysql_query("describe acces_usr_2",$dbh);
		if (!mysql_error($dbh)) {
			$rqt = "ALTER IGNORE TABLE acces_usr_2 ADD updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ";
			echo traite_rqt($rqt,"alter table acces_usr_2 add field updated");
		}
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		echo form_relance ("v5.14");
		break;
		
	case "v5.14":
		echo "<table ><tr><th>".$msg['admin_misc_action']."</th><th>".$msg['admin_misc_resultat']."</th></tr>";
		
		// +-------------------------------------------------+
		// MB - Indexer la colonne num_renvoi_voir de la table noeuds
		$rqt = "ALTER TABLE noeuds DROP INDEX i_num_renvoi_voir";
		echo traite_rqt($rqt,"ALTER TABLE noeuds DROP INDEX i_num_renvoi_voir");
		$rqt = "ALTER TABLE noeuds ADD INDEX i_num_renvoi_voir (num_renvoi_voir)";
		echo traite_rqt($rqt,"ALTER TABLE noeuds ADD INDEX i_num_renvoi_voir (num_renvoi_voir)");
		
		$rqt="update parametres set comment_param='Liste des id de template de notice pour ajouter des onglets personnalisés en affichage de notice\nExemple: 1,3,ISBD,PUBLIC\nLe paramètre notices_format doit être à 0 pour placer ISBD et PUBLIC' where type_param='opac' and sstype_param='notices_format_onglets' ";
		echo traite_rqt($rqt,"update opac notices_format_onglets comments in parametres") ;
		
		$rqt = "update parametres set comment_param='0 : mode normal de recherche\n1 : Affiche directement le résultat de la recherche tous les champs sans passer par la présentation du niveau 1 de recherche \n2 : Affiche directement le résultat de la recherche tous les champs sans passer par la présentation du niveau 1 de recherche sans faire de recherche intermédaire'  where type_param='opac' and sstype_param='autolevel2' ";
		echo traite_rqt($rqt,"update opac_autolevel comments in parametres");
		
		
		//Création des tables pour le portfolio
		$rqt = "create table cms_collections (
			id_collection int unsigned not null auto_increment primary key,
			collection_title varchar(255) not null default '',
			collection_description text not null,
			collection_num_parent int not null default 0,
			collection_num_storage int not null default 0,
			index i_cms_collection_title(collection_title)
		)";
		echo traite_rqt($rqt,"create table cms_collections") ;
		$rqt = "create table cms_documents (
			id_document int unsigned not null auto_increment primary key,
			document_title varchar(255) not null default '',
			document_description text not null,
			document_filename varchar(255) not null default '',
			document_mimetype varchar(100) not null default '',
			document_filesize int not null default 0,
			document_vignette mediumblob not null default '',
			document_url text not null,
			document_path varchar(255) not null default '',
			document_create_date date not null default '0000-00-00',
			document_num_storage int not null default 0,
			document_type_object varchar(255) not null default '',
			document_num_object int not null default 0,
			index i_cms_document_title(document_title)
		)";
		echo traite_rqt($rqt,"create table cms_documents") ;
		$rqt = "create table storages (
			id_storage int unsigned not null auto_increment primary key,
			storage_name varchar(255) not null default '',
			storage_class varchar(255) not null default '',
			storage_params text not null,
			index i_storage_class(storage_class)
		)";
		echo traite_rqt($rqt,"create table storages") ;
		$rqt = "create table cms_documents_links (
			document_link_type_object varchar(255) not null default '',
			document_link_num_object int not null default 0,
			document_link_num_document int not null default 0,
			primary key(document_link_type_object,document_link_num_object,document_link_num_document)
		)";
		echo traite_rqt($rqt,"create table cms_documents_links") ;
		
		// FT - Ajout des paramètres pour forcer les tags meta pour les moteurs de recherche
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='meta_description' "))==0){
			$rqt="insert into parametres(type_param,sstype_param,valeur_param,comment_param,section_param,gestion) values('opac','meta_description','','Contenu du meta tag description pour les moteurs de recherche','b_aff_general',0)";
			echo traite_rqt($rqt,"INSERT INTO parametres opac_meta_description");
		}
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='meta_keywords' "))==0){
			$rqt="insert into parametres(type_param,sstype_param,valeur_param,comment_param,section_param,gestion) values('opac','meta_keywords','','Contenu du meta tag keywords pour les moteurs de recherche','b_aff_general',0)";
			echo traite_rqt($rqt,"INSERT INTO parametres opac_meta_keywords");
		}	
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='meta_author' "))==0){
			$rqt="insert into parametres(type_param,sstype_param,valeur_param,comment_param,section_param,gestion) values('opac','meta_author','','Contenu du meta tag author pour les moteurs de recherche','b_aff_general',0)";
			echo traite_rqt($rqt,"INSERT INTO parametres opac_meta_author");
		}
		
		//DG - autoriser le code HTML dans les cotes exemplaires
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='html_allow_expl_cote' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'html_allow_expl_cote', '0', 'Autoriser le code HTML dans les cotes exemplaires ? \n 0 : non \n 1', '',0) ";
			echo traite_rqt($rqt, "insert pmb_html_allow_expl_cote=0 into parametres");
		}
		
		//maj valeurs possibles pour empr_sort_rows
		$rqt = "update parametres set comment_param='Colonnes qui seront disponibles pour le tri des emprunteurs. Les colonnes possibles sont : \n n: nom+prénom \n b: code-barres \n c: catégories \n g: groupes \n l: localisation \n s: statut \n cp: code postal \n v: ville \n y: année de naissance \n ab: type d\'abonnement \n #n : id des champs personnalisés' where type_param= 'empr' and sstype_param='sort_rows' ";
		echo traite_rqt($rqt,"update empr_sort_rows into parametres");
			
		//DB - création table index pour le magasin rdf
		$rqt = "create table rdfstore_index (
					num_triple int(10) unsigned not null default 0, 
					subject_uri text not null ,
					predicat_uri text not null ,
					num_object int(10) unsigned not null default 0 primary key,
					object_val text not null ,
					object_index text not null ,
					object_lang char(5) not null default '' 
		) default charset=utf8 ";
		echo traite_rqt($rqt,"create table rdfstore_index");

		// MB - Création d'une table de cache pour les cadres du portail pour accélérer l'affichage
		$rqt = "DROP TABLE IF EXISTS cms_cache_cadres";
		echo traite_rqt($rqt,"DROP TABLE IF EXISTS cms_cache_cadres");
		$rqt = "CREATE TABLE  cms_cache_cadres (
			cache_cadre_hash VARCHAR( 32 ) NOT NULL,
			cache_cadre_type_content VARCHAR(30) NOT NULL,
			cache_cadre_create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			cache_cadre_content MEDIUMTEXT NOT NULL,
			PRIMARY KEY (  cache_cadre_hash, cache_cadre_type_content )
		);";
		echo traite_rqt($rqt,"CREATE TABLE  cms_cache_cadres");
		
		$rqt = "ALTER TABLE rdfstore_index ADD subject_type TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER  subject_uri";
		echo traite_rqt($rqt,"alter table rdfstore_index add subject_type");
		
		// Info de réindexation
		$rqt = " select 1 " ;
		echo traite_rqt($rqt,"<b><a href='".$base_path."/admin.php?categ=netbase' target=_blank>VOUS DEVEZ REINDEXER / YOU MUST REINDEX : Admin > Outils > Nettoyage de base > Réindexer le magasin RDF</a></b> ") ;

		// AP - Ajout de l'ordre dans les rubriques et les articles
		$rqt = "ALTER TABLE cms_sections ADD section_order INT UNSIGNED default 0";
		echo traite_rqt($rqt,"alter table cms_sections add section_order");
		
		$rqt = "ALTER TABLE cms_articles ADD article_order INT UNSIGNED default 0";
		echo traite_rqt($rqt,"alter table cms_articles add article_order");
		
		//DG - CSS add on en gestion
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='default_style_addon' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'default_style_addon', '', 'Ajout de styles CSS aux feuilles déjà incluses ?\n Ne mettre que le code CSS, exemple:  body {background-color: #FF0000;}', '',0) ";
			echo traite_rqt($rqt, "insert pmb_default_style_addon into parametres");
		}
		
		// NG - circulation sans retour
		$rqt = "ALTER TABLE serialcirc ADD serialcirc_no_ret INT UNSIGNED not null default 0";
		echo traite_rqt($rqt,"alter table serialcirc add serialcirc_no_ret");
		
		// NG - personnalisation d'impression de la liste de circulation des périodiques	
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='serialcirc_subst' "))==0){
			$rqt="insert into parametres(type_param,sstype_param,valeur_param,comment_param,section_param,gestion) values('pmb','serialcirc_subst','','Nom du fichier permettant de personnaliser l\'impression de la liste de circulation des périodiques','',0)";
			echo traite_rqt($rqt,"INSERT INTO parametres pmb_serialcirc_subst");
		}
		
		//MB - Augmenter la taille du libellé de groupe
		$rqt = "ALTER TABLE groupe CHANGE libelle_groupe libelle_groupe VARCHAR(255) NOT NULL";
		echo traite_rqt($rqt,"alter table groupe");
		
		//AR - Ajout d'un type de cache pour un cadre
		$rqt = "alter table cms_cadres add cadre_modcache varchar(255) not null default 'get_post_view'";
		echo traite_rqt($rqt,"alter table cms_cadres add cadre_modcache");
		
		//DG - Type de relation par défaut en création de périodique
		$rqt = "ALTER TABLE users ADD value_deflt_relation_serial VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER value_deflt_relation";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default value_deflt_relation_serial after value_deflt_relation");
		
		//DG - Type de relation par défaut en création de bulletin
		$rqt = "ALTER TABLE users ADD value_deflt_relation_bulletin VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER value_deflt_relation_serial";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default value_deflt_relation_bulletin after value_deflt_relation_serial");
		
		//DG - Type de relation par défaut en création d'article
		$rqt = "ALTER TABLE users ADD value_deflt_relation_analysis VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER value_deflt_relation_bulletin";
		echo traite_rqt($rqt,"ALTER TABLE users ADD default value_deflt_relation_analysis after value_deflt_relation_bulletin");
		
		//DG - Mise à jour des valeurs en fonction du type de relation par défaut en création de notice, si la valeur est vide !
		if ($res = mysql_query("select userid, value_deflt_relation,value_deflt_relation_serial,value_deflt_relation_bulletin,value_deflt_relation_analysis from users")){
			while ( $row = mysql_fetch_object($res)) {
				if ($row->value_deflt_relation_serial == '') mysql_query("update users set value_deflt_relation_serial='".$row->value_deflt_relation."' where userid=".$row->userid);
				if ($row->value_deflt_relation_bulletin == '') mysql_query("update users set value_deflt_relation_bulletin='".$row->value_deflt_relation."' where userid=".$row->userid);
				if ($row->value_deflt_relation_analysis == '') mysql_query("update users set value_deflt_relation_analysis='".$row->value_deflt_relation."' where userid=".$row->userid);
			}
		}
	
		//DG - Activer le prêt court par défaut
		$rqt = "ALTER TABLE users ADD deflt_short_loan_activate INT(1) UNSIGNED DEFAULT 0 NOT NULL ";
		echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_short_loan_activate");
		
		//DG - Alerter l'utilisateur par mail des nouvelles inscriptions en OPAC ?
		$rqt = "ALTER TABLE users ADD user_alert_subscribemail INT(1) UNSIGNED NOT NULL DEFAULT 0 after user_alert_demandesmail";
		echo traite_rqt($rqt,"ALTER TABLE users add user_alert_subscribemail default 0");

		//DB - Modification commentaire autolevel
		$rqt = "update parametres set comment_param='0 : mode normal de recherche.\n1 : Affiche le résultat de la recherche tous les champs après calcul du niveau 1 de recherche.\n2 : Affiche directement le résultat de la recherche tous les champs sans passer par le calcul du niveau 1 de recherche.' where type_param= 'opac' and sstype_param='autolevel2' ";
		echo traite_rqt($rqt,"update parameter comment for opac_autolevel2");
		
		//AR - Ajout du paramètres pour la durée de validité du cache des cadres du potail
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'cms' and sstype_param='cache_ttl' "))==0){
			$rqt = "insert into parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'cms', 'cache_ttl', '1800', 'durée de vie du cache des cadres du portail (en secondes)', '',0) ";
			echo traite_rqt($rqt, "insert cms_caches_ttl into parametres");
		}
		
		//DG - Périodicité : Jour du mois
		$rqt = "ALTER TABLE planificateur ADD perio_jour_mois VARCHAR( 128 ) DEFAULT '*' AFTER perio_minute";
		echo traite_rqt($rqt,"ALTER TABLE planificateur ADD perio_jour_mois DEFAULT * after perio_minute");
		
		//DG - Replanifier la tâche en cas d'échec
		$rqt = "alter table taches_type add restart_on_failure int(1) UNSIGNED DEFAULT 0 NOT NULL";
		echo traite_rqt($rqt,"alter table taches_type add restart_on_failure");
		
		//DG - Alerte mail en cas d'échec de la tâche
		$rqt = "alter table taches_type add alert_mail_on_failure VARCHAR(255) DEFAULT ''";
		echo traite_rqt($rqt,"alter table taches_type add alert_mail_on_failure");
		
		//DG - Préremplissage de la vignette des dépouillements
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='serial_thumbnail_url_article' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'serial_thumbnail_url_article', '0', 'Préremplissage de l\'url de la vignette des dépouillements avec l\'url de la vignette de la notice mère en catalogage des périodiques ? \n 0 : Non \n 1 : Oui', '',0) ";
			echo traite_rqt($rqt, "insert pmb_serial_thumbnail_url_article=0 into parametres");
		}
		
		//DG - Délai en millisecondes entre les mails envoyés lors d'un envoi groupé
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='mail_delay' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'pmb','mail_delay','0','Temps d\'attente en millisecondes entre chaque mail envoyé lors d\'un envoi groupé. \n 0 : Pas d\'attente', '',0)" ;
			echo traite_rqt($rqt,"insert pmb_mail_delay=0 into parametres") ;
		}
		
		//DG - Timeout cURL sur la vérifications des liens
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='curl_timeout' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES(0,'pmb','curl_timeout','5','Timeout cURL (en secondes) pour la vérification des liens', '',1)" ;
			echo traite_rqt($rqt,"insert pmb_curl_timeout=0 into parametres") ;
		}	
			
		//DG - Autoriser la prolongation groupée pour tous les membres
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='allow_prolong_members_group' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'empr', 'allow_prolong_members_group', '0', 'Autoriser la prolongation groupée des adhésions des membres d\'un groupe ? \n 0 : Non \n 1 : Oui', '',0) ";
			echo traite_rqt($rqt, "insert empr_allow_prolong_members_group=0 into parametres");
		}
			
		
		//DB - ajout d'un index stem+lang sur la table words
		$rqt = "alter table words add index i_stem_lang(stem, lang)";
		echo traite_rqt($rqt, "alter table words add index i_stem_lang");
		
		//NG - Autoindex
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'thesaurus' and sstype_param='auto_index_notice_fields' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'thesaurus', 'auto_index_notice_fields', '', 'Liste des champs de notice à utiliser pour l\'indexation automatique, séparés par une virgule.\nLes noms des champs sont les identifiants des champs listés dans le fichier XML pmb/notice/notice.xml\nExemple: tit1,n_resume', 'categories',0) ";
			echo traite_rqt($rqt, "insert thesaurus_auto_index_notice_fields='' into parametres");
		}
		
		//NG - Autoindex: surchage du parametrage de la recherche
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'thesaurus' and sstype_param='auto_index_search_param' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'thesaurus', 'auto_index_search_param', '', 'Surchage des paramètres de recherche de l\'indexation automatique.\n\nSyntaxe: param=valeur;\n\nListe des paramètres:\nautoindex_max_up_distance,\nautoindex_max_down_distance,\nautoindex_stem_ratio,\nautoindex_see_also_ratio,\nautoindex_max_down_ratio,\nautoindex_max_up_ratio,\nautoindex_deep_ratio,\nautoindex_distance_ratio,\nmax_relevant_words,\nmax_relevant_terms', 'categories',0) ";
			echo traite_rqt($rqt, "insert thesaurus_auto_index_search_param='' into parametres");
		}
		
		//DG - Choix par défaut pour la prolongation des lecteurs
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='abonnement_default_debit' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) VALUES (0, 'empr', 'abonnement_default_debit', '0', 'Choix par défaut pour la prolongation des lecteurs. \n 0 : Ne pas débiter l\'abonnement \n 1 : Débiter l\'abonnement sans la caution \n 2 : Débiter l\'abonnement et la caution') " ;
			echo traite_rqt($rqt,"insert empr_abonnement_default_debit = 0 into parametres");
		}
		
		//NG - Ajout indexation_lang dans la table notices
		$rqt = "ALTER TABLE notices ADD indexation_lang VARCHAR( 20 ) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE notices ADD indexation_lang VARCHAR( 20 ) NOT NULL DEFAULT '' ");
		
		$rqt = "alter table users add xmlta_indexation_lang varchar(10) NOT NULL DEFAULT '' after deflt_integration_notice_statut";
		echo traite_rqt($rqt,"alter table users add xmlta_indexation_lang");
		
		//NG - Ajout ico_notice 
		$rqt = "ALTER TABLE connectors_sources ADD ico_notice VARCHAR( 256 ) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE connectors_sources ADD ico_notice VARCHAR( 256 ) NOT NULL DEFAULT '' ");
		
		//NG - liste des sources externes d'enrichissements à intégrer dans le a2z
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='perio_a2z_enrichissements' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'opac', 'perio_a2z_enrichissements', '0', 'Affichage de sources externes d\'enrichissement dans le navigateur de périodiques.\nListe des couples (séparé par une virgule) Id de connecteur, Id de source externe d\'enrichissement, séparé par un point virgule\nExemple:\n6,4;6,5', 'c_recherche',0) ";
			echo traite_rqt($rqt, "insert opac_perio_a2z_enrichissements=0 into parametres");
		}
	
		//DG - Modification taille du champ empr_msg de la table empr
		$rqt = "ALTER TABLE empr MODIFY empr_msg TEXT null " ;
		echo traite_rqt($rqt,"alter table empr modify empr_msg");
		
		//DG - Identifiant du template de notice par défaut en impression de panier
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='print_template_default' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
			VALUES (0, 'opac', 'print_template_default', '0', 'En impression de panier, identifiant du template de notice utilisé par défaut. Si vide ou à 0, le template classique est utilisé', 'a_general', 0)";
			echo traite_rqt($rqt,"insert opac_print_template_default='0' into parametres");
		}
	
		//DG - Paramètre pour afficher le permalink de la notice dans le detail de la notice
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='show_permalink' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) 
			VALUES (0, 'pmb', 'show_permalink', '0', 'Afficher le lien permanent de l\'OPAC en gestion ? \n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_show_permalink=0 into parameters");
		}
		
		//AB - Ajout du champ pour choix d'un template d'export pour les flux RSS 
		$rqt = "ALTER TABLE rss_flux ADD tpl_rss_flux INT(11) UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE rss_flux ADD tpl_rss_flux INT(11) UNSIGNED NOT NULL DEFAULT 0 ");
		
		//DG - Parametre pour afficher ou non l'emprunteur précédent dans la fiche exemplaire
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='expl_show_lastempr' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'pmb', 'expl_show_lastempr', '1', 'Afficher l\'emprunteur précédent sur la fiche exemplaire ? \n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_expl_show_lastempr=1 into parameters");
		}
		
		// NG - Gestion de caisses
		$rqt = "CREATE TABLE cashdesk (
			cashdesk_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			cashdesk_name VARCHAR(255) NOT NULL DEFAULT '',
			cashdesk_autorisations VARCHAR(255) NOT NULL DEFAULT '',
			cashdesk_transactypes VARCHAR(255) NOT NULL DEFAULT '',
			cashdesk_cashbox INT UNSIGNED NOT NULL default 0
			)";
		echo traite_rqt($rqt,"CREATE TABLE cashdesk");
		
		$rqt = "CREATE TABLE cashdesk_locations (
			cashdesk_loc_cashdesk_num  INT UNSIGNED NOT NULL default 0,
			cashdesk_loc_num  INT UNSIGNED NOT NULL default 0,
			PRIMARY KEY(cashdesk_loc_cashdesk_num,cashdesk_loc_num)
			)";
		echo traite_rqt($rqt,"CREATE TABLE cashdesk_locations");
		
		$rqt = "CREATE TABLE cashdesk_sections (
			cashdesk_section_cashdesk_num  INT UNSIGNED NOT NULL default 0,
			cashdesk_section_num  INT UNSIGNED NOT NULL default 0,
			PRIMARY KEY(cashdesk_section_cashdesk_num,cashdesk_section_num)
			)";
		echo traite_rqt($rqt,"CREATE TABLE cashdesk_sections");		
		
		// NG - Gestion de type de transactions
		$rqt = "CREATE TABLE  transactype (
			transactype_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			transactype_name VARCHAR(255) NOT NULL DEFAULT '',
			transactype_quick_allowed INT UNSIGNED NOT NULL default 0,
			transactype_unit_price FLOAT NOT NULL default 0
			)";
		echo traite_rqt($rqt,"CREATE TABLE transactype");
		
		// NG - Mémorisation du payement des transactions
		$rqt = "CREATE TABLE transacash (
			transacash_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			transacash_empr_num INT UNSIGNED NOT NULL default 0,
			transacash_desk_num INT UNSIGNED NOT NULL default 0,
			transacash_user_num INT UNSIGNED NOT NULL default 0,
			transacash_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',			
			transacash_sold FLOAT NOT NULL default 0,			
			transacash_collected FLOAT NOT NULL default 0,		
			transacash_rendering FLOAT NOT NULL default 0			
			)";
		echo traite_rqt($rqt,"CREATE TABLE transacash");
		
		// NG - Activer la gestion de caisses en gestion financière
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='gestion_financiere_caisses' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES (0, 'pmb', 'gestion_financiere_caisses', '0', 'Activer la gestion de caisses en gestion financière? \n 0 : Non.\n 1 : Oui.', '',0) ";
			echo traite_rqt($rqt, "insert pmb_gestion_financiere_caisses=0 into parameters");
		}
		
		$rqt = "ALTER TABLE transactions ADD transactype_num INT UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE transactions ADD transactype_num INT UNSIGNED NOT NULL DEFAULT 0 ");
		
		$rqt = "ALTER TABLE transactions ADD cashdesk_num INT UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE transactions ADD cashdesk_num INT UNSIGNED NOT NULL DEFAULT 0 ");
		
		$rqt = "ALTER TABLE transactions ADD transacash_num INT UNSIGNED NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE transactions ADD transacash_num INT UNSIGNED NOT NULL DEFAULT 0 ");
		
		$rqt = "alter table users add deflt_cashdesk int NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"alter table users add deflt_cashdesk");
		
		$rqt= "alter table sessions add notifications text";
		echo traite_rqt($rqt,"alter table sessions add notifications");
		
		// AP - Ajout du paramètre de segmentation des documents numériques	
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='diarization_docnum' "))==0){
			$rqt="insert into parametres(type_param,sstype_param,valeur_param,comment_param,section_param,gestion) values('pmb','diarization_docnum',0,'Activer la segmentation des documents numériques vidéo ou audio 0 : non activée 1 : activée','',0)";
			echo traite_rqt($rqt,"INSERT INTO parametres diarization_docnum");
		}
		
		// AP - Ajout de la table explnum_speakers
		$rqt = "CREATE TABLE explnum_speakers (
			explnum_speaker_id int unsigned not null auto_increment primary key,
			explnum_speaker_explnum_num int unsigned not null default 0,
			explnum_speaker_speaker_num varchar(10) not null default '',
			explnum_speaker_gender varchar(1) default '',
			explnum_speaker_author int unsigned not null default 0
			)";
		echo traite_rqt($rqt,"CREATE TABLE explnum_speakers");
		$rqt = "alter table explnum_speakers drop index i_ensk_explnum_num";
		echo traite_rqt($rqt,"alter table explnum_speakers drop index i_ensk_explnum_num");	
		$rqt = "alter table explnum_speakers add index i_ensk_explnum_num(explnum_speaker_explnum_num)";
		echo traite_rqt($rqt,"alter table explnum_speakers add index i_ensk_explnum_num");
		$rqt = "alter table explnum_speakers drop index i_ensk_author";
		echo traite_rqt($rqt,"alter table explnum_speakers drop index i_ensk_author");	
		$rqt = "alter table explnum_speakers add index i_ensk_author(explnum_speaker_author)";
		echo traite_rqt($rqt,"alter table explnum_speakers add index i_ensk_author");

		
		// AP - Ajout de la table explnum_segments
		$rqt = "CREATE TABLE  explnum_segments (
			explnum_segment_id int unsigned not null auto_increment primary key,
			explnum_segment_explnum_num int unsigned not null default 0,
			explnum_segment_speaker_num int unsigned not null default 0,
			explnum_segment_start double not null default 0,
			explnum_segment_duration double not null default 0,
			explnum_segment_end double not null default 0
			)";
		echo traite_rqt($rqt,"CREATE TABLE explnum_segments");
		$rqt = "alter table explnum_segments drop index i_ensg_explnum_num";
		echo traite_rqt($rqt,"alter table explnum_segments drop index i_ensg_explnum_num");	
		$rqt = "alter table explnum_segments add index i_ensg_explnum_num(explnum_segment_explnum_num)";
		echo traite_rqt($rqt,"alter table explnum_segments add index i_ensg_explnum_num");
		$rqt = "alter table explnum_segments drop index i_ensg_speaker";
		echo traite_rqt($rqt,"alter table explnum_segments drop index i_ensg_speaker");	
		$rqt = "alter table explnum_segments add index i_ensg_speaker(explnum_segment_speaker_num)";
		echo traite_rqt($rqt,"alter table explnum_segments add index i_ensg_speaker");
		
		//DG - Modification de l'emplacement du paramètre bannette_notices_template dans la zone DSI
		$rqt = "update parametres set type_param='dsi',section_param='' where type_param='opac' and sstype_param='bannette_notices_template' ";
		echo traite_rqt($rqt,"update parametres set bannette_notices_template");
		
		//DG - Retour à la précédente forme de tri
		$rqt = "update parametres set comment_param='Tri par défaut des recherches OPAC.\nDe la forme, c_num_6 (c pour croissant, d pour décroissant, puis num ou text pour numérique ou texte et enfin l\'identifiant du champ (voir fichier xml sort.xml))' WHERE type_param='opac' AND sstype_param='default_sort'";
		echo traite_rqt($rqt,"update comment for param opac_default_sort");
	
		//DG - Mode d'application d'un tri - Liste de tris pré-enregistrés
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='default_sort_list' "))==0){
	 		$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'opac', 'default_sort_list', '0 d_num_6,c_text_28;d_text_7', 'Afficher la liste déroulante de sélection d\'un tri ? \n 0 : Non \n 1 : Oui \nFaire suivre d\'un espace pour l\'ajout de plusieurs tris sous la forme : c_num_6|Libelle;d_text_7|Libelle 2;c_num_5|Libelle 3\n\nc pour croissant, d pour décroissant\nnum ou text pour numérique ou texte\nidentifiant du champ (voir fichier xml sort.xml)\nlibellé du tri (optionnel)','d_aff_recherche',0) " ;
	 		echo traite_rqt($rqt,"insert opac_default_sort_list = 0 d_num_6,c_text_28;d_text_7 into parametres");
	 	}

	 	//DG - Afficher le libellé du tri appliqué par défaut en résultat de recherche
	 	if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='default_sort_display' "))==0){
	 		$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'opac', 'default_sort_display', '0', 'Afficher le libellé du tri appliqué par défaut en résultat de recherche ? \n 0 : Non \n 1 : Oui','d_aff_recherche',0) " ;
	 		echo traite_rqt($rqt,"insert opac_default_sort_display = 0 into parametres");
	 	}	
	 		
		// NG - Affichage des bannettes privées en page d'accueil de l'Opac	
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_bannettes' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES(0,'opac','show_bannettes','0','Affichage des bannettes en page d\'accueil OPAC.\n 0 : Non.\n 1 : Oui.','f_modules',0)" ;
			echo traite_rqt($rqt,"insert opac_show_bannettes into parametres") ;
		}

		// AB - Affichage des facettes en AJAX	
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='facettes_ajax' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES(0,'opac','facettes_ajax','1','Charger les facettes en ajax\n0 : non\n1 : oui','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_facettes_ajax into parametres") ;
		}
		
		// DB - Modification index sur table notices_mots_global_index
		set_time_limit(0);
		mysql_query("set wait_timeout=28800", $dbh);
		$rqt = 'alter table notices_mots_global_index drop primary key';
		echo traite_rqt($rqt, 'alter table notices_mots_global_index drop primary key');
		$rqt = 'alter table notices_mots_global_index add primary key (id_notice, code_champ, num_word, position, code_ss_champ)';  
		echo traite_rqt($rqt, 'alter table notices_mots_global_index add primary key');
		
		//AB
		$rqt = "ALTER TABLE cms_build drop INDEX cms_build_index";
		echo traite_rqt($rqt,"alter cms_build drop index cms_build_index ");
		$rqt = "ALTER TABLE cms_build ADD INDEX cms_build_index (build_version_num , build_obj)";
		echo traite_rqt($rqt,"alter cms_build add index cms_build_index ON build_version_num , build_obj");
			
		// AR - Paramètres pour ne pas prendre en compte les mots vides en tous les champs à l'OPAC
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='search_all_keep_empty_words' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES(0,'opac','search_all_keep_empty_words','1','Conserver les mots vides pour les autorités dans la recherche tous les champs\n0 : non\n1 : oui','c_recherche',0)" ;
			echo traite_rqt($rqt,"insert opac_search_all_keep_empty_words into parametres") ;
		}				
		
		// NG - Paramètre pour activer le piège en prêt si l'emprunteur a déjà emprunté l'exemplaire
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='pret_already_loaned' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES(0,'pmb','pret_already_loaned','0','Activer le piège en prêt si le document a déjà été emprunté par le lecteur. Nécessite l\'activation de l\'archivage des prêts\n0 : non\n1 : oui','',0)" ;
			echo traite_rqt($rqt,"insert pmb_pret_already_loaned into parametres") ;
		}

		//DB - Ajout d'index
		set_time_limit(0);
		mysql_query("set wait_timeout=28800", $dbh);
		
		$rqt = "alter table abts_abts drop index i_date_fin";
		echo traite_rqt($rqt,"alter table abts_abts drop index i_date_fin");
		$rqt = "alter table abts_abts add index i_date_fin (date_fin)";
		echo traite_rqt($rqt,"alter table abts_abts add index i_date_fin");
		
		$rqt = "alter table cms_editorial_types drop index i_editorial_type_element";
		echo traite_rqt($rqt,"alter table cms_editorial_types drop index i_editorial_type_element");
		$rqt = "alter table cms_editorial_types add index i_editorial_type_element (editorial_type_element)";
		echo traite_rqt($rqt,"alter table cms_editorial_types add index i_editorial_type_element");
		
		$rqt = "alter table cms_editorial_custom drop index i_num_type";
		echo traite_rqt($rqt,"alter table cms_editorial_custom drop index i_num_type");
		$rqt = "alter table cms_editorial_custom add index i_num_type (num_type)";
		echo traite_rqt($rqt,"alter table cms_editorial_custom add index i_num_type");
		
		$rqt = "alter table cms_build drop index i_build_parent_build_version_num";
		echo traite_rqt($rqt,"alter table cms_build drop index i_build_parent_build_version_num");
		$rqt = "alter table cms_build add index i_build_parent_build_version_num (build_parent,build_version_num)";
		echo traite_rqt($rqt,"alter table cms_build add index i_build_parent_build_version_num");

		$rqt = "alter table cms_build drop index i_build_type_build_version_num";
		echo traite_rqt($rqt,"alter table cms_build drop index i_build_type_build_version_num");
		$rqt = "alter table cms_build add index i_build_parent_build_version_num (build_type,build_version_num)";
		echo traite_rqt($rqt,"alter table cms_build add index i_build_type_build_version_num");

		$rqt = "alter table cms_build drop index i_build_obj_build_version_num";
		echo traite_rqt($rqt,"alter table cms_build drop index i_build_obj_build_version_num");
		$rqt = "alter table cms_build add index i_build_obj_build_version_num (build_obj,build_version_num)";
		echo traite_rqt($rqt,"alter table cms_build add index i_build_obj_build_version_num");

		$rqt = "alter table notices_fields_global_index drop index i_code_champ_code_ss_champ";
		echo traite_rqt($rqt,"alter table notices_fields_global_index drop index i_code_champ_code_ss_champ");
		$rqt = "alter table notices_fields_global_index add index i_code_champ_code_ss_champ (code_champ,code_ss_champ)";
		echo traite_rqt($rqt,"alter table notices_fields_global_index add index i_code_champ_code_ss_champ");

		$rqt = "alter table notices_mots_global_index drop index i_code_champ_code_ss_champ_num_word";
		echo traite_rqt($rqt,"alter table notices_mots_global_index drop index i_code_champ_code_ss_champ_num_word");
		$rqt = "alter table notices_mots_global_index add index i_code_champ_code_ss_champ_num_word (code_champ,code_ss_champ,num_word)";
		echo traite_rqt($rqt,"alter table notices_mots_global_index add index i_code_champ_code_ss_champ_num_word");

		// Activation des recherches exemplaires voisins 
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='allow_voisin_search' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'allow_voisin_search', '0', 'Activer la recherche des exemplaires dont la cote est proche:\n 0 : non \n 1 : oui', 'c_recherche', '0')";
			echo traite_rqt($rqt,"insert opac_allow_voisin_search='0' into parametres ");
		}

		// MHo - Paramètre pour indiquer le nombre de notices similaires à afficher à l'opac
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='nb_notices_similaires' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param,gestion)
			VALUES (0, 'opac', 'nb_notices_similaires', '6', 'Nombre de notices similaires affichées lors du dépliage d\'une notice.\nValeur max = 6.','e_aff_notice',0)";
			echo traite_rqt($rqt,"insert opac_nb_notices_similaires='6' into parametres");
		}
		// MHo - Paramètre pour rendre indépendant l'affichage réduit des notices similaires par rapport aux notices pliées
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='notice_reduit_format_similaire' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param,section_param,gestion)
			VALUES (0, 'opac', 'notice_reduit_format_similaire', '1', 'Format d\'affichage des réduits de notices similaires :\n 0 = titre+auteur principal\n 1 = titre+auteur principal+date édition\n 2 = titre+auteur principal+date édition + ISBN\n 3 = titre seul\n P 1,2,3 = tit+aut+champs persos id 1 2 3\n E 1,2,3 = tit+aut+édit+champs persos id 1 2 3\n T = tit1+tit4\n 4 = titre+titre parallèle+auteur principal\n H 1 = id d\'un template de notice','e_aff_notice',0)";
			echo traite_rqt($rqt,"insert opac_notice_reduit_format_similaire='0' into parametres");
		}
		
		//AR - Paramètres d'écretage des résultats de recherche
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='search_noise_limit_type' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'search_noise_limit_type', '0', 'Ecrêter les résulats de recherche en fonction de la pertinence. \n0 : Non \n1 : Retirer du résultat tout ce qui est en dessous de la moyenne - l\'écart-type\n2,ratio : Retirer du résultat tout ce qui est en dessous de la moyenne - un ratio de l\'écart-type (ex: 2,1.96)\n3,ratio : Retirer du résultat tout ce qui est dessous d\'un ratio de la pertinence max (ex: 3,0.25 élimine tout ce qui est inférieur à 25% de la plus forte pertinence)' , 'c_recherche', '0')";
			echo traite_rqt($rqt,"insert opac_search_noise_limit_type='0' into parametres ");
		}
		
		//AR - Prise en compte de la fréquence d'apparition d'un mot dans le fonds pour le calcul de pertinence
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='search_relevant_with_frequency' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (NULL, 'opac', 'search_relevant_with_frequency', '0', 'Utiliser la fréquence d\'apparition des mots dans les notices pour le calcul de la pertinence.\n0 : Non \n1 : Oui' , 'c_recherche', '0')";
			echo traite_rqt($rqt,"insert opac_search_relevant_with_frequency='0' into parametres ");
		}
		
		//DG - Calcul de la prolongation d'adhésion à partir de la date de fin d'adhésion ou la date du jour
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'empr' and sstype_param='prolong_calc_date_adhes_depassee' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'empr', 'prolong_calc_date_adhes_depassee', '0', 'Si la date d\'adhésion est dépassée, le calcul de la prolongation se fait à partir de :\n 0 : la date de fin d\'adhésion\n 1 : la date du jour','',0) " ;
			echo traite_rqt($rqt,"insert empr_prolong_calc_date_adhes_depassee = 0 into parametres");
		}
		
		//DG - Modification du commentaire du paramètre pmb_notice_reduit_format pour les améliorations
		$rqt = "update parametres set comment_param = 'Format d\'affichage des réduits de notices :\n 0 = titre+auteur principal\n 1 = titre+auteur principal+date édition\n 2 = titre+auteur principal+date édition + ISBN\n 3 = titre seul\n P 1,2,3 = tit+aut+champs persos id 1 2 3\n E 1,2,3 = tit+aut+édit+champs persos id 1 2 3\n T = tit1+tit4\n 4 = titre+titre parallèle+auteur principal\n H 1 = id d\'un template de notice' where type_param='pmb' and sstype_param='notice_reduit_format'";
		echo traite_rqt($rqt,"update parametre pmb_notice_reduit_format");
		
		//DG - Périodicité d'envoi par défaut en création de bannette privée (en jours)
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='bannette_priv_periodicite' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion) VALUES (0, 'opac', 'bannette_priv_periodicite', '15', 'Périodicité d\'envoi par défaut en création de bannette privée (en jours)','l_dsi',0) " ;
			echo traite_rqt($rqt,"insert opac_bannette_priv_periodicite = 15 into parametres");
		}
		
		//DG - Modification du commentaire opac_notices_format
		$rqt = "update parametres set comment_param='Format d\'affichage des notices en résultat de recherche \n 0 : Utiliser le paramètre notices_format_onglets \n 1 : ISBD seul \n 2 : Public seul \n 4 : ISBD et Public \n 5 : ISBD et Public avec ISBD en premier \n 8 : Réduit (titre+auteurs) seul' where type_param='opac' and sstype_param='notices_format'" ;
		echo traite_rqt($rqt,"UPDATE parametres SET comment_param for opac_notices_format") ;
		
		
		//DB - Modifications et ajout de commentaires pour les paramètres décrivant l'autoindexation
		$rqt = "UPDATE parametres SET valeur_param=replace(valeur_param,',',';'), comment_param = 'Liste des champs de notice à utiliser pour l\'indexation automatique.\n\n";
		$rqt.= "Syntaxe: nom_champ=poids_indexation;\n\n";
		$rqt.= "Les noms des champs sont ceux précisés dans le fichier XML \"pmb/includes/notice/notice.xml\"\n";
		$rqt.= "Le poids de l\'indexation est une valeur de 0.00 à 1. (Si rien n\'est précisé, le poids est de 1)\n\n";
		$rqt.= "Exemple :\n\n";
		$rqt.= "tit1=1.00;n_resume=0.5;' ";
		$rqt.= "WHERE type_param = 'thesaurus' and sstype_param='auto_index_notice_fields' ";
		echo traite_rqt($rqt,"UPDATE parametres SET comment_param for thesaurus_auto_index_notice_fields") ;
		
		$rqt = "UPDATE parametres SET comment_param = 'Surchage des paramètres de recherche de l\'indexation automatique.\n";
		$rqt.= "Syntaxe: param=valeur;\n\n";
		$rqt.= "Listes des parametres:\n\n";
		$rqt.= "max_relevant_words = 20 (nombre maximum de mots et de lemmes de la notice à prendre en compte pour le calcul)\n\n";
		$rqt.= "autoindex_deep_ratio = 0.05 (ratio sur la profondeur du terme dans le thésaurus)\n";
		$rqt.= "autoindex_stem_ratio = 0.80 (ratio de pondération des lemmes / aux mots)\n\n";
		$rqt.= "autoindex_max_up_distance = 2 (distance maximum de recherche dans les termes génériques du thésaurus)\n";
		$rqt.= "autoindex_max_up_ratio = 0.01 (pondération sur les termes génériques)\n\n";
		$rqt.= "autoindex_max_down_distance = 2 (distance maximum de recherche dans les termes spécifiques du thésaurus)\n";
		$rqt.= "autoindex_max_down_ratio = 0.01 (pondération sur les termes spécifiques)\n\n";
		$rqt.= "autoindex_see_also_ratio = 0.01 (surpondération sur les termes voir aussi du thésaurus)\n\n";
		$rqt.= "autoindex_distance_type = 1 (calcul de distance de 1 à 4)\n";
		$rqt.= "autoindex_distance_ratio = 0.50 (ratio de pondération sur la distance entre les mots trouvés et les termes d\'une expression du thésaurus)\n\n";
		$rqt.= "max_relevant_terms = 10 (nombre maximum de termes retournés)' ";
		$rqt.= "WHERE type_param = 'thesaurus' and sstype_param='auto_index_search_param' ";
		echo traite_rqt($rqt,"UPDATE parametres SET comment_param for thesaurus_auto_index_search_param") ;
		
		// MHo - Ajout des attributs de l'oeuvre dans la table des titres uniformes
		$rqt = "ALTER TABLE titres_uniformes ADD tu_num_author BIGINT(11) UNSIGNED NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_num_author");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_forme VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_forme");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_date VARCHAR(50) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_date");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_date_date DATE NOT NULL DEFAULT '0000-00-00' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_date_date");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_sujet VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_sujet");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_lieu VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_lieu");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_histoire TEXT NULL ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_histoire");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_caracteristique TEXT NULL ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_caracteristique");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_public VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_public");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_contexte TEXT NULL ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_contexte");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_coordonnees VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_coordonnees");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_equinoxe VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_equinoxe");
		$rqt = "ALTER TABLE titres_uniformes ADD tu_completude INT(2) UNSIGNED NOT NULL DEFAULT 0 ";
		echo traite_rqt($rqt,"alter titres_uniformes add tu_completude");
		
		// AR - Retrait du paramètres juste commité : Activation des recherches exemplaires voisins
		$rqt="delete from parametres where type_param= 'opac' and sstype_param='allow_voisin_search'";
		echo traite_rqt($rqt,"delete from parametres opac_allow_voisin_search");

		// AR - Modification du paramètre opac_allow_simili
		$rqt="update parametres set comment_param = 'Activer les recherches similaires sur une notice :\n0 : Non\n1 : Activer la recherche \"Dans le même rayon\" et \"Peut-être aimerez-vous\"\n2 : Activer seulement la recherche \"Dans le même rayon\"\n3 : Activer seulement la recherche \"Peut-être aimerez-vous\"', section_param = 'e_aff_notice' where type_param='opac' and sstype_param='allow_simili_search'";
		echo traite_rqt($rqt,"update parametres set opac_allow_simili_search");		
		
		// NG - Affichage des bannettes en page d'accueil de l'Opac	selon la banette
		$rqt = "ALTER TABLE bannettes ADD bannette_opac_accueil INT UNSIGNED NOT NULL default 0 ";
		echo traite_rqt($rqt,"alter table bannettes add bannette_opac_accueil");
		
		// AR - DSI abonné en page d'accueil
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_subscribed_bannettes' "))==0){
			$rqt = "insert into parametres ( type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES('opac','show_subscribed_bannettes',0,'Affichage des bannettes auxquelles le lecteur est abonné en page d\'accueil OPAC :\n0 : Non.\n1 : Oui.','f_modules',0)" ;
			echo traite_rqt($rqt,"insert opac_show_subscribed_bannettes=0 into parametres") ;
		}
		
		// AR - DSI publique sélectionné en page d'accueil
		if (mysql_num_rows(mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='show_public_bannettes' "))==0){
			$rqt = "insert into parametres ( type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
			VALUES('opac','show_public_bannettes',0,'Affichage des bannettes sélectionnées en page d\'accueil OPAC :\n0 : Non.\n1 : Oui.','f_modules',0)" ;
			echo traite_rqt($rqt,"insert show_public_bannettes=0 into parametres") ;
		}
		
		// AR - Retrait du paramètre perio_a2z_enrichissements, on ne l'a jamais utilisé car on a finalement ramené le paramétrage par un connecteur
		$rqt="delete from parametres where type_param= 'opac' and sstype_param='perio_a2z_enrichissements'";
		echo traite_rqt($rqt,"delete from parametres opac_perio_a2z_enrichissements");
		
		//DG - Paramètre non utilisé
		$rqt = "delete from parametres where sstype_param='confirm_resa' and type_param='opac' " ;
		$res = mysql_query($rqt, $dbh) ;
		
		//DG - Paramètre non utilisé
		$rqt = "delete from parametres where sstype_param='authors_aut_rec_per_page' and type_param='opac' " ;
		$res = mysql_query($rqt, $dbh) ;
		
		// +-------------------------------------------------+
		echo "</table>";
		$rqt = "update parametres set valeur_param='".$action."' where type_param='pmb' and sstype_param='bdd_version' " ;
		$res = mysql_query($rqt, $dbh) ;
		echo "<strong><font color='#FF0000'>".$msg[1807].$action." !</font></strong><br />";
		break;

	default:
		include("$include_path/messages/help/$lang/alter.txt");
		break;
	}


/*


	A mettre en 5.15
	
	
**/
/*

*/

