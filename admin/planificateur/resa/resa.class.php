<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: resa.class.php,v 1.2 2012-07-31 10:12:16 dgoron Exp $

global $class_path, $include_path;
require_once($include_path."/parser.inc.php");
require_once($class_path."/tache.class.php");
require_once($class_path."/docs_location.class.php");

class resa extends tache {
	
	function resa($id_tache=0){
		global $base_path;
		
		parent::get_messages($base_path."/admin/planificateur/".get_class());
		$this->id_tache = $id_tache;
				
	}
	
	//formulaire sp�cifique au type de t�che
	function show_form ($param='') {
		global $msg,$pmb_transferts_actif,$pmb_location_reservation;

		//param�tres pr�-enregistr�
		$lst_opt = array();
		if ($param['chk_resa']) {
			foreach ($param['chk_resa'] as $elem) {
				$lst_opt[$elem] = $elem;
			}
		}
		$empr_location_id = $param['empr_location_id'];
				
		//Choix de l'action � r�aliser
		$form_task .= "
		<div class='row'>
			<div class='colonne3'>
				<label for='resa'>".$this->msg["planificateur_resa_empr"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='checkbox' name='chk_resa[]' value='resa_en_cours_noconf' ".(($lst_opt["resa_en_cours_noconf"] == "resa_en_cours_noconf")  ? "checked" : "")." />".$this->msg["resa_en_cours_noconf"]."
				<br /><input type='checkbox' name='chk_resa[]' value='resa_depassee_noconf' ".(($lst_opt["resa_depassee_noconf"] == "resa_depassee_noconf")  ? "checked" : "")." />".$this->msg["resa_depassee_noconf"]."
			</div>
		</div>
		<div class='row' >&nbsp;</div>";	
				
		if ($pmb_transferts_actif=="1" || $pmb_location_reservation) {
			//Choix de la localisation
			$form_task .= "
			<div class='row'>
				<div class='colonne3'>
					<label for='resa'>".$this->msg["planificateur_resa_loc"]."</label>
				</div>
				<div class='colonne_suite'>".
				docs_location::gen_combo_box_empr($empr_location_id)."
				</div>
			</div>
			<div class='row' >&nbsp;</div>";
		}
			
		return $form_task;
	}
		
	function task_execution() {
		global $dbh, $msg, $PMBusername;
		global $pdflettreresa_priorite_email;

		if ((SESSrights & CIRCULATION_AUTH)) {
			//requete pour la construction du pdf
			$rqt = "select distinct p.libelle_tache, p.rep_upload, p.path_upload from planificateur p
				left join taches t on t.num_planificateur = p.id_planificateur
				left join tache_docnum tdn on tdn.tache_docnum_repertoire=p.rep_upload
				where t.id_tache=".$this->id_tache;
			$res_query = mysql_query($rqt, $dbh);
			
			$parameters = $this->unserialize_task_params();
	
			//filtre sur la localisation de l'emprunteur
//			$empr_location_id = ($parameters["empr_location_id"] ? $parameters["empr_location_id"] : $deflt2docs_location);
			$empr_location_id = ($parameters["empr_location_id"] ? $parameters["empr_location_id"] : "0");
			if ($empr_location_id != "0") {
				$query = "select name from docs_location where idlocation=".$empr_location_id;
				$res = mysql_query($query, $dbh);
				if ($res) {
					$location_name = mysql_result($res,0,"name");
				}
			}
			$count = count($parameters["chk_resa"]);
			$percent = 0;
			$p_value = (int) 100/$count;
	
			if ($parameters["chk_resa"]) {
				foreach ($parameters["chk_resa"] as $elem) {
					//traitement des options choisies
					/**
					 * Seulement utile pour la premiere requete
					 * Si un emprunteur a une r�sa en cours et une r�sa d�pass�e,
					 * les deux seront prises en comptes
					 */
					switch ($elem) {
						case "resa_en_cours_noconf":
							//Resas en cours non confirm�e
							$title = "<tr><th>".$this->msg["resa_en_cours_noconf"]." ".($location_name ? "(".$msg[298]." : ".$location_name.")" : "")."</th></tr>";
							$cl_where = " and (resa_date_fin >= CURDATE() or resa_date_fin='0000-00-00')";
							break;
						case "resa_depassee_noconf":
							//Resas d�pass�es non confirm�e
							$title = "<tr><th>".$this->msg["resa_depassee_noconf"]." ".($location_name ? "(".$msg[298]." : ".$location_name.")" : "")."</th></tr>";
							$cl_where = " and resa_date_fin < CURDATE() and resa_date_fin<>'0000-00-00' ";	
							break;
						default :
							$title="";
							$cl_where="";
							break;
					}
			
//					$this->report[] = "<tr><th>".$this->msg["resa_confirm"]."</th></tr>";
					$this->report[] = $title;
					if (method_exists($this->proxy, 'pmbesResas_get_empr_information_and_resas')) {
						//requete trop peu complete...
						$requete = "select distinct(resa_idempr) from resa ";
						$requete .="where resa_confirmee=0 and resa_cb != ''";
						$requete .= $cl_where;
						$res = mysql_query($requete);
						$result = array();
						while ($row = mysql_fetch_object($res)) {
							if ($row->resa_idempr)
								$result[] = $this->proxy->pmbesResas_get_empr_information_and_resas($row->resa_idempr);
						}
						if ($result) {
							foreach ($result as $empr) {
								if ($empr["information"]["id_empr"]) {
									$id_empr_concerne = $empr["information"]["id_empr"];
									if ($empr["resas_ids"] != "") {
										$tab_resas_empr=array();
										foreach ($empr["resas_ids"] as $resa_id) {
											$tab_resas_empr[] = $resa_id;
										}
							
										$ids_resas = implode(",", $tab_resas_empr);
										if (method_exists($this->proxy, 'pmbesResas_confirmResaReader')) {
											//pdflettreresa_priorite_email == 3 ? aucune alerte
											if ($pdflettreresa_priorite_email != "3") {
												if (method_exists($this->proxy, 'pmbesResas_generatePdfResasReaders')) {
													$list_letter_resa = $this->proxy->pmbesResas_confirmResaReader($ids_resas, $id_empr_concerne,$empr_location_id);
													if ($list_letter_resa) {
														$tab_letter_empr_resas[$id_empr_concerne] = explode(",",$list_letter_resa);
														$object_fpdf = $this->proxy->pmbesResas_generatePdfResasReaders($tab_letter_empr_resas);	
														if ($object_fpdf) {
															//pb � corriger :
															//si le fichier n'est pas g�n�r�, la r�sa est confirm� mais sans confirmation de lettre
															$succeed = $this->generate_docnum($object_fpdf);
															if (!$succeed) {
																//erreur de cr�ation du pdf
																$rqt_maj = "update resa set resa_confirmee=0 where id_resa in (".$list_letter_resa.") AND resa_cb is not null and resa_cb!=''" ;
																if ($id_empr_concerne) $rqt_maj .= " and resa_idempr=$id_empr_concerne ";
																mysql_query($rqt_maj, $dbh);
															}
														} else {
															//erreur de cr�ation du pdf
															$rqt_maj = "update resa set resa_confirmee=0 where id_resa in (".$list_letter_resa.") AND resa_cb is not null and resa_cb!=''" ;
															if ($id_empr_concerne) $rqt_maj .= " and resa_idempr=$id_empr_concerne ";
															mysql_query($rqt_maj, $dbh);
														}
													}
												} else {
													$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],"generatePdfResasReaders","pmbesResas",$PMBusername)."</td></tr>";
												}
											} else {
												$this->report[] = "<tr><td>".$this->msg["resa_alerte_disabled"]."</td></tr>";
											}
										} else {
											$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],"confirmResaReader","pmbesResas",$PMBusername)."</td></tr>";
										}
									}
								}
							}
						} else {
							$this->report[] = "<tr><td>".$this->msg["resa_no_result"]."</td></tr>";
						}
					} else {
						$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],"get_empr_information_and_resas","pmbesResas",$PMBusername)."</td></tr>";
					}
//					$percent += $p_value;			
					$this->update_progression(100);
				}
			} else {
				$this->report[] = "<tr><th>".$this->msg["resa_error_parameters"]."</th></tr>";
			}
		} else {
			$this->report[] = "<tr><th>".sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername)."</th></tr>";
		}															
	}
	
	/*Inutilis�...*/
	function traite_commande($cmd,$message) {		
		switch ($cmd) {
			case RESUME:
				$this->send_command(WAITING);
				break;
			case SUSPEND:
				break;
			case STOP:
				$this->finalize();
				die();
				break;
			case RETRY:
				break;
			case ABORT:
				break;				
		}
	}
    
	function make_serialized_task_params() {
    	global $chk_resa,$montrerquoi,$empr_location_id;
		$t = parent::make_serialized_task_params();
		
		if (!empty($chk_resa)) {
			for ($i=0; $i<count($chk_resa); $i++) {
				$t["chk_resa"]=$chk_resa;				
			}
		}
		$t["empr_location_id"] = $empr_location_id;

    	return serialize($t);
	}
	
	function unserialize_task_params() {
    	$params = $this->get_task_params();
		
		return $params;
    }
}