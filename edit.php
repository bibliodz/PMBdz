<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: edit.php,v 1.54 2014-01-13 08:07:15 arenou Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "EDIT_AUTH";  
$base_title = "\$msg[6]";
$base_noheader=1;
$base_nosession=1;
$base_use_dojo = true;
if ((isset($_GET["dest"])) && ($_GET["dest"]=="TABLEAUCSV" || $_GET["dest"]=="EXPORT_NOTI")) {
	$base_nocheck = 1 ;
	$include_path = $base_path."/includes" ;
	require_once("$include_path/db_param.inc.php");
	require_once("$include_path/mysql_connect.inc.php");
	$dbh = connection_mysql();
	// on checke si l'utilisateur existe et si le mot de passe est OK
	$query = "SELECT count(1) FROM users WHERE username='".$_GET["user"]."' AND pwd=password('".$_GET["password"]."') ";
	$result = mysql_query($query, $dbh);
	$valid_user = mysql_result($result, 0, 0);
	if (!$valid_user) exit;
}
require_once ("$base_path/includes/init.inc.php");
require_once("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once("$class_path/marc_table.class.php");
require_once("$class_path/docs_location.class.php");
require_once("$class_path/author.class.php");
require_once("$include_path/notice_authors.inc.php");
require_once("$include_path/notice_categories.inc.php");
require_once("$include_path/resa_func.inc.php");
require_once("$include_path/resa_planning_func.inc.php");

require_once("$include_path/explnum.inc.php");

// modules propres � edit.php ou � ses sous-modules
require("$include_path/templates/edit.tpl.php");


// cr�ation de la page
switch($dest) {
	case "TABLEAU":
		require_once ("$class_path/writeexcel/class.writeexcel_workbook.inc.php");
		require_once ("$class_path/writeexcel/class.writeexcel_worksheet.inc.php");
		header("Content-Type: application/x-msexcel; name=\"empr_list.xls\"");
		header("Content-Disposition: inline; filename=\"tableau.xls\"");
		$fichier_temp_nom=str_replace(" ","",microtime());
		$fichier_temp_nom=str_replace("0.","",$fichier_temp_nom);
		break;
	case "TABLEAUHTML":
		header("Content-Type: application/download\n");
		header("Content-Disposition: atttachement; filename=\"tableau.html\"");
		print "<html><head>" .
		'<meta http-equiv=Content-Type content="text/html; charset='.$charset.'" />'.
		"</head><body>";
		break;
	case "TABLEAUCSV":
		// header ("Content-Type: text/html; charset=".$charset);
		header("Content-Type: application/download\n");
		header("Content-Disposition: atachement; filename=\"tableau.csv\"");
		break;
	case "EXPORT_NOTI":
		// header ("Content-Type: text/html; charset=".$charset);
		header("Content-Type: application/download\n");
		header("Content-Disposition: atachement; filename=\"notices.doc\"");
		break;
	default:
        header ("Content-Type: text/html; charset=".$charset);
		print $std_header."<body class='$current_module claro' id='body_current_module' page_name='$current_module'>";
		print "<div id='att' style='z-Index:1000'></div>";
		echo window_title($database_window_title.$msg["1100"].$msg["1003"].$msg["1001"]);
		print $menu_bar;
		print $extra;
		print $extra2;
		if($use_shortcuts) {
			include("$include_path/shortcuts/circ.sht");
			}
		print $edit_layout;
		break;
	}

switch($categ) {
	// EDITIONS LIEES AUX NOTICES
	case "notices":
		switch($sub) {
			case "resa" :
			default :
				include("./edit/notices.inc.php");
				break;
			}
		break;
	// EDITIONS LIEES AUX EMPRUNTEURS
	case "empr":
		$restrict="";
		switch($sub) {
			case "limite" :
				$titre_page = $msg["1120"].": ".$msg["edit_titre_empr_abo_limite"];  
				$restrict = " ((to_days(empr_date_expiration) - to_days(now()) ) <=  $pmb_relance_adhesion ) and empr_date_expiration >= now() ";
				include("./edit/empr_list.inc.php");
				break;
			case "depasse" :
				$titre_page = $msg["1120"].": ".$msg["edit_titre_empr_abo_depasse"];  
				$restrict = " empr_date_expiration < now() ";
				include("./edit/empr_list.inc.php");
				break;
			case "cashdesk" :
				$titre_page = $msg["1120"].": ".$msg["cashdesk_edition_menu"];  
				include("./edit/cashdesk.inc.php");
				break;
			case "categ_change" :
				$titre_page = $msg["1120"].": ".$msg["edit_titre_empr_categ_change"];
				if (($categ_action)&&($categ_action=="change_categ_empr")) {
					for ($i=0; $i<count($empr); $i++) {
						$id_empr=$empr[$i];
						$action="empr_chang_categ_edit_".$id_empr;
						global $$action;
						$act=$$action;
						if ($act!=0) {
							// on modifie la cat�gorie du lecteur si demand�
							if($id_empr){
								$requete="update empr set empr_categ=$act where id_empr=$id_empr";
								mysql_query($requete);
							}
						}
					}
				}
				$restrict = " ((((age_min<> 0) || (age_max <> 0)) && (age_max >= age_min)) && (((DATE_FORMAT( curdate() , '%Y' )-empr_year) < age_min) || ((DATE_FORMAT( curdate() , '%Y' )-empr_year) >= age_max))) ";
				include("./edit/empr_list.inc.php");
				break;
			default :
			case "encours" :
				$sub = "encours" ;
				$titre_page = $msg["1120"].": ".$msg["1121"];  
				$restrict = " empr_date_expiration >= now() ";
				include("./edit/empr_list.inc.php");
				break;
			}
			
		if (($sub=="limite")||($sub=="depasse")) {
			if (($action)&&($action=="print_all")) {
				print "<script>openPopUp('./pdf.php?pdfdoc=lettre_relance_adhesion&action=print_all&empr_location_id=$empr_location_id&empr_statut_edit=$empr_statut_edit&restricts=".rawurlencode(stripslashes($restrict))."', 'lettre', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes');</script>";	
				if ($empr_relance_adhesion==1) print "<script>openPopUp('./mail.php?type_mail=mail_relance_adhesion&action=print_all&empr_location_id=$empr_location_id&empr_statut_edit=$empr_statut_edit&restricts=".rawurlencode(stripslashes($restrict))."', 'mail', 600, 500, -2, -2, 'toolbar=no, dependent=yes, resizable=yes, scrollbars=yes');</script>";
			} 	
		}
		break ;
	// EDITIONS LIEES AUX PERIODIQUES
	case "serials":
		switch($sub) {
			/* en attente d'une gestion correcte du bulletinage, actuellement absente de la base de donn�es. 
			case "manquant" :
				echo "<h1>".$msg["1150"]."&nbsp;:&nbsp;".$msg["1154"]."</h1>";
				include("./edit/serials_manq.inc.php");
				break;
			*/
			case "collect" :
			default :
				$sub = "collect" ;
				echo "<h1>".$msg["1150"]."&nbsp;:&nbsp;".$msg["1151"]."</h1>";
				include("./edit/serials_coll.inc.php");
				break;
			}
		break;

	// EDITIONS DES STATISTIQUES
	case "procs":
		switch($dest) {
			case "TABLEAUCSV":
			default:
				include_once("./edit/procs.inc.php");
				break;
			}
		break;

	// CODES A BARRES
	case "cbgen":
		switch($sub) {
			default :
			case "libre" :
				$sub = "libre" ;
				echo "<h1>".$msg["1140"]."&nbsp;:&nbsp;".$msg["1141"]."</h1>";  
				include("./edit/cbgenlibre.inc.php");
				break;
			}
		break;

	//LES TRANSFERTS
	case "transferts" :
		require_once ("./edit/transferts.inc.php");
	break;
	
	//STATISTIQUES DE L'OPAC
	case "stat_opac" :
		//echo "<h1>".$msg["opac_admin_menu"]."&nbsp;:&nbsp;".$msg["stat_opac_menu"]."</h1>";
		include("./edit/stat_opac.inc.php");
		break;
		
	// Edition Template de notices
	case "tpl" :
		switch($sub) {
			case "notice" :
			default :
				echo "<h1>".$msg["edit_tpl_menu"]."&nbsp;:&nbsp;".$msg["edit_notice_tpl_menu"]."</h1>";
				include("./edit/notice_tpl.inc.php");
			break;
		}
	break;
	case "state" :
		include($base_path."/edit/editions_state/main.inc.php");
		break;
	// EDITIONS LIEES AUX EXEMPLAIRES
	default:
	case "expl":
		$categ = "expl" ;
		switch($sub) {
				case "ppargroupe" :
					$critere_requete=" order by libelle_groupe, empr_nom, empr_prenom, pret_retour ";
					include("./edit/expl_groupe.inc.php");
					break;
				case "rpargroupe" :
					$critere_requete=" and pret_retour < curdate() order by libelle_groupe, empr_nom, empr_prenom, pret_retour ";
					include("./edit/expl_groupe.inc.php");
					break;	
				case "retard" :
					$titre_page = $msg[1110]." : ".$msg[1112];
					$critere_requete=" and pret_retour < curdate() order by empr_nom, empr_prenom ";
					include("./edit/expl.inc.php");
					break;
				case "retard_par_date" :
					$titre_page = $msg[1110]." : ".$msg['edit_expl_retard_par_date'];
					$critere_requete=" and pret_retour < curdate() order by pret_retour, empr_nom, empr_prenom ";
					include("./edit/expl.inc.php");
					break;
				case "owner" :
					$critere_requete=" order by idlender, expl_cote, expl_cb ";
					include("./edit/expl_owner.inc.php");
					break;
				case "relance" :
					include("./edit/relance.inc.php");
					break;					
				case 'short_loans' :
					$titre_page = $msg['short_loans'];
					$critere_requete=" and short_loan_flag='1' order by pret_retour ";
					include("./edit/expl.inc.php");
					break;
				case 'unreturned_short_loans' :
					$titre_page = $msg['short_loans'];
					$critere_requete=" and short_loan_flag='1' and pret_date < curdate() and pret_retour >= curdate() order by pret_retour ";
					include("./edit/expl.inc.php");
					break;
				case 'overdue_short_loans' :
					$titre_page = $msg['short_loans'];
					$critere_requete=" and short_loan_flag='1' and pret_retour < curdate() order by pret_retour ";
					include("./edit/expl.inc.php");
					break;					
				default :
				case "encours" :
					$sub = "encours" ;
					$titre_page = $msg[1110]." : ".$msg[1111];
					$critere_requete=" order by pret_retour ";
					include("./edit/expl.inc.php");
					break;
				}
		break;
	}
	switch($dest) {
		case "TABLEAU":
		case "TABLEAUCSV":
		case "EXPORT_NOTI":
			break;
		case "TABLEAUHTML":
			print $footer;
			break;
		default:
			print $edit_layout_end;
			print $footer;
			print "</body>" ;
			break;
	}
	
mysql_close($dbh);
