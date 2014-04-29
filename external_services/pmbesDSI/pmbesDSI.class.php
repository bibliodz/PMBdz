<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesDSI.class.php,v 1.2 2013-10-22 12:35:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");
require_once($class_path."/bannette.class.php");

class pmbesDSI extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	function listBannettesAuto($filtre_search="", $id_classement=0) {
		global $dbh;
		
		if (SESSrights & DSI_AUTH) {
			$result = array();
		
			//auto = 1 : bannettes automatiques sans contr�le de date
			$auto=1;
		
			$filtre_search = str_replace("*", "%", $filtre_search) ;
		
			if ($filtre_search) $clause = "WHERE nom_bannette like '$filtre_search%' and bannette_auto='$auto' " ;
			else $clause = "WHERE bannette_auto='$auto' " ;
//			if ($id_classement!=0) $clause.= " and num_classement=0 "; 
			if ($id_classement>0) $clause.= " and num_classement='$id_classement' " ;
			
			$requete = "SELECT COUNT(1) FROM bannettes $clause ";
			$res = mysql_query($requete, $dbh);
			$nbr_lignes = mysql_result($res, 0, 0);
			if($nbr_lignes) {
				$requete = "SELECT id_bannette, nom_bannette, date_last_remplissage, date_last_envoi, proprio_bannette, bannette_auto, nb_notices_diff FROM bannettes $clause ORDER BY nom_bannette, id_bannette ";
				$res = mysql_query($requete, $dbh);
		
				while ($row = mysql_fetch_assoc($res)) {
					$result[] = array(
						"id_bannette" => $row["id_bannette"],
						"nom_bannette" => utf8_normalize($row["nom_bannette"]),
						"date_last_remplissage" => $row["date_last_remplissage"],
						"date_last_envoi" => $row["date_last_envoi"],
						"proprio_bannette" => $row["proprio_bannette"],
						"bannette_auto" => $row["bannette_auto"],
						"nb_notices_diff" => $row["nb_notices_diff"],
					);
				}
			}
			return $result;
		} else {
			return array();
		}
	} 
	
	function diffuseBannettesFullAuto($lst_bannettes) {
		global $msg,$dsi_auto,$PMBusername, $pmb_bdd_version;
			
		if (SESSrights & DSI_AUTH) {
			if (!$dsi_auto)
				throw new Exception("DSI Auto pas activ�e sur base $database (user=$PMBusername) Version noyau: $pmb_bdd_version ");
			if (!$lst_bannettes)
				throw new Exception("Missing parameter: lst_bannettes");
				
			if (!$lst_bannettes) $lst_bannettes = array() ;
			$action_diff_aff="";
			for ($iba=0 ; $iba < sizeof($lst_bannettes) ; $iba++) {
				$bannette = new bannette($lst_bannettes[$iba]) ;
				$action_diff_aff .= $msg['dsi_dif_vidage'].": ".$bannette->nom_bannette."<br />" ; 
				if(!$bannette->limite_type) $action_diff_aff .= $bannette->vider();
				$action_diff_aff .= $msg['dsi_dif_remplissage'].": ".$bannette->nom_bannette ; 
				$action_diff_aff .= $bannette->remplir();
				$action_diff_aff .= $bannette->purger();
				$action_diff_aff .= "<strong>".$msg['dsi_dif_diffusion'].": ".$bannette->nom_bannette."</strong><br />" ; 
				$action_diff_aff .= $bannette->diffuser();
			}
			return $action_diff_aff;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
	
	function diffuseBannetteFullAuto($id_bannette) {
		global $msg,$dsi_auto,$PMBusername, $pmb_bdd_version;
					
		if (SESSrights & DSI_AUTH) {
			if (!$dsi_auto) {
				$action_diff_aff .="DSI Auto pas activ�e sur base $database (user=$PMBusername) Version noyau: $pmb_bdd_version ";
	//			throw new Exception("DSI Auto pas activ�e sur base $database (user=$PMBusername) Version noyau: $pmb_bdd_version ");
				return $action_diff_aff;
			}
			if (!$id_bannette) {
				$action_diff_aff .="Missing parameter: id_bannette";
	//			throw new Exception("Missing parameter: id_bannette");
				return $action_diff_aff;
			}
							
			$bannette = new bannette($id_bannette) ;
			
			$action_diff_aff .= $msg['dsi_dif_vidage'].": ".$bannette->nom_bannette."<br />" ; 
			if(!$bannette->limite_type) $action_diff_aff .= $bannette->vider();
			$action_diff_aff .= $msg['dsi_dif_remplissage'].": ".$bannette->nom_bannette ; 
			$action_diff_aff .= $bannette->remplir();
			$action_diff_aff .= $bannette->purger();
			$action_diff_aff .= "<strong>".$msg['dsi_dif_diffusion'].": ".$bannette->nom_bannette."</strong><br />" ; 
			$action_diff_aff .= $bannette->diffuser();
			
			return $action_diff_aff;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
	
	function flushBannette($id_bannette) {
		global $msg,$PMBusername;
		
		if (SESSrights & DSI_AUTH) {
			if (!$id_bannette)
				throw new Exception("Missing parameter: id_bannette");
				
			$bannette = new bannette($id_bannette) ;
			$action_diff_aff .= $msg['dsi_dif_vidage'].": ".$bannette->nom_bannette."<br />" ; 
			$action_diff_aff .= $bannette->vider();
		
			return $action_diff_aff;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
	
	function fillBannette($id_bannette) {
		global $msg, $PMBusername;
		
		if (SESSrights & DSI_AUTH) {
			if (!$id_bannette)
				throw new Exception("Missing parameter: id_bannette");
				
			$bannette = new bannette($id_bannette) ;
			$action_diff_aff .= $msg['dsi_dif_remplissage'].": ".$bannette->nom_bannette ; 
			$action_diff_aff .= $bannette->remplir();
	
			return $action_diff_aff;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
	
	function diffuseBannette($id_bannette) {
		global $msg, $PMBusername;
		
		if (SESSrights & DSI_AUTH) {
			if (!$id_bannette)
				throw new Exception("Missing parameter: id_bannette");
				
			$bannette = new bannette($id_bannette) ;
			$action_diff_aff .= "<strong>".$msg['dsi_dif_diffusion'].": ".$bannette->nom_bannette."</strong><br />" ; 
			$action_diff_aff .= $bannette->diffuser();
	
			return $action_diff_aff;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
	
	function exportBannette($id_bannette) {
		global $msg, $PMBusername;
		global $ourPDF;
		
		if (SESSrights & DSI_AUTH) {
			if (!$id_bannette)
				throw new Exception("Missing parameter: id_bannette");
	
			$bannette = new bannette($id_bannette) ;
	//		$action_diff_aff .= "<strong>".$msg['dsi_dif_export'].": ".$bannette->nom_bannette."</strong><br />" ; 
	//		$ourPDF = $bannette->construit_contenu_PDF();
			$resultat_html = $bannette->construit_contenu_HTML();
			$ourPDF = new PDF_HTML();
			$ourPDF->AddPage();
			$ourPDF->SetFont('Arial');
			$ourPDF->WriteHTML($resultat_html);

			return $ourPDF;
		} else {
			return sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername);
		}
	}
}




?>