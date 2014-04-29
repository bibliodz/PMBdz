<?php
// +-------------------------------------------------+
// | 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesTasks.class.php,v 1.7 2013-10-15 08:33:15 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

class pmbesTasks extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
		
	function timeoutTasks() {
		global $dbh;

		$requete = "select id_tache, param, start_at FROM taches, planificateur 
			WHERE num_planificateur=id_planificateur AND id_process <> 0";

		$resultat=mysql_query($requete, $dbh);
		while ($row = mysql_fetch_object($resultat)) {
			$params=unserialize($row->param);
			foreach ($params as $index=>$param) {
				if (($index == "timeout") && ($param != "")) {
					// 6 = FAIL - Sera mis � l'�chec � l'�coute de la t�che
					$requete_check_timeout = "update taches set commande=6 
						where DATE_ADD('".$row->start_at."', INTERVAL ".($param)." MINUTE) <= CURRENT_TIMESTAMP 
						and id_tache=".$row->id_tache;
					
					mysql_query($requete_check_timeout, $dbh);
				}
			}
		}
	}
	
	function getOS() {
		if (stripos($_SERVER['SERVER_SOFTWARE'], "win")!==false || stripos(PHP_OS, "win")!==false )
		  $os = "Windows";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "mac")!==false || stripos(PHP_OS, "mac")!==false || stripos($_SERVER['SERVER_SOFTWARE'], "ppc")!==false || stripos(PHP_OS, "ppc")!==false )
		  $os = "Mac";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "linux")!==false || stripos(PHP_OS, "linux")!==false )
		  $os = "Linux";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "freebsd")!==false || stripos(PHP_OS, "freebsd")!==false )
		  $os = "FreeBSD";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "sunos")!==false || stripos(PHP_OS, "sunos")!==false )
		  $os = "SunOS";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "irix")!==false || stripos(PHP_OS, "irix")!==false )
		  $os = "IRIX";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "beos")!==false || stripos(PHP_OS, "beos")!==false )
		  $os = "BeOS";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "os/2")!==false || stripos(PHP_OS, "os/2")!==false )
		  $os = "OS/2";
		elseif (stripos($_SERVER['SERVER_SOFTWARE'], "aix")!==false || stripos(PHP_OS, "aix")!==false )
		  $os = "AIX";
		else
		  $os = "Autre";
		  
		return $os;
	}
	
	/*V�rifie les processus actifs*/
	function checkTasks() {
		global $dbh,$base_path,$include_path,$class_path,$lang;
		global $charset;
		global $PMBusernom,$PMBuserprenom,$PMBuseremail;
		
		//R�cup�ration de l'OS pour la v�rification des processus
		$os = $this->getOS();
		
		$sql = "SELECT id_tache, start_at, id_process FROM taches WHERE id_process <> 0";
		$res = mysql_query($sql,$dbh);
		if ($res && mysql_num_rows($res)) {
			while ($row = mysql_fetch_assoc($res)) {
				if ($os == "Linux") {
					$command = 'ps -p '.$row['id_process'];
				} else if ($os == "Windows") {
					$command = 'PsList '.$row['id_process'];
				} else if ($os == "Mac") {
					$command = 'ps -p '.$row['id_process'];
				} else {
					$command = 'ps -p '.$row['id_process'];
				}
	        	exec($command,$output);
	        	if (!isset($output[1])) {
	        		// 5 = STOPPED
	        		$sql_stop_task = "update taches set status=5, "; 
	        		if ($row['start_at'] == '0000-00-00 00:00:00') $sql_stop_task .= "start_at=CURRENT_TIMESTAMP, ";
	        		$sql_stop_task .= "end_at=CURRENT_TIMESTAMP, id_process=0, commande=0 where id_tache=".$row["id_tache"];
	        		$res = mysql_query($sql_stop_task);
	        		//En fonction du param�trage de la t�che...
	        		//Replanifier / Envoi de mail
	        		$query = "select num_type_tache, libelle_tache, param, num_planificateur, indicat_progress from planificateur join taches on id_planificateur=num_planificateur where id_tache=".$row["id_tache"];
	        		$result = mysql_query($query);
	        		if ($result && mysql_num_rows($result)) {
	        			$task_info = mysql_fetch_object($result);
	        			$params = unserialize($task_info->param);
	        			if ($params["alert_mail_on_failure"] != "") {
	        				$params_alert_mail = explode(",",$params["alert_mail_on_failure"]);
	        				if ($params_alert_mail[0]) {
	        					$mails = explode(";",$params_alert_mail[1]);
	        					if(preg_match("#.*@.*#",$PMBuseremail)) {
		        					if (count($mails)) {
		        						//Allons chercher les messages
		        						if (file_exists("$include_path/messages/".$lang.".xml")) {
		        							//Allons chercher les messages
		        							require_once("$class_path/XMLlist.class.php");
		        							$messages = new XMLlist("$include_path/messages/".$lang.".xml", 0);
		        							$messages->analyser();
		        							$msg = $messages->table;
		        						
			        						$objet = $msg["task_alert_user_mail_obj"];
			        						$corps = str_replace("!!task_name!!",$task_info->libelle_tache,$msg["task_alert_user_mail_corps"]) ;
			        						$corps = str_replace("!!percent!!",$task_info->indicat_progress,$corps) ;
			        						foreach ($mails as $mail) {
			        							if(preg_match("#.*@.*#",$mail)) {
			        								@mailpmb("", $mail, $objet, $corps, $PMBusernom." ".$PMBuserprenom, $PMBuseremail, "Content-Type: text/plain; charset=\"$charset\"", '', '', 0, '');
			        							}
			        						}
		        						}
		        					}
	        					}
	        				}
	        			}
	        			if ($params["restart_on_failure"]) {
							$this->createNewTask($row["id_tache"],$task_info->num_type_tache,$task_info->num_planificateur);
	        			}
	        		}
	        	}
			}
		}
	}
		
	/*V�rifie si une ou plusieurs t�ches doivent �tre ex�cut�es et lance celles-ci*/
	function runTasks($connectors_out_source_id) {
		global $dbh;
		global $base_path;
		global $pmb_path_php;
		
		//R�cup�ration de l'OS sur lequel est ex�cut� la t�che
		$os = $this->getOS();

		//Y-a t-il une ou plusieurs t�ches � ex�cuter...
		$sql = "SELECT id_planificateur, p.num_type_tache, p.libelle_tache, p.num_user, t.id_tache FROM planificateur p, taches t
			WHERE t.num_planificateur = p.id_planificateur
			And t.start_at='0000-00-00 00:00:00'
			And t.status=1
			And p.calc_next_date_deb <> '0000-00-00'
			And (p.calc_next_date_deb < '".date('Y-m-d')."'
			Or p.calc_next_date_deb = '".date('Y-m-d')."' 
			And p.calc_next_heure_deb <= '".date('H:i')."')
			";
		$res = mysql_query($sql,$dbh);
		while ($row = mysql_fetch_assoc($res)) {
			if ($os == "Linux") {		
				exec("nohup $pmb_path_php  $base_path/admin/planificateur/run_task.php ".$row["id_tache"]." ".$row["num_type_tache"]." ".$row["id_planificateur"]." ".$row["num_user"]." ".$connectors_out_source_id." > /dev/null 2>&1 & echo $!", $output);
			} else if ($os == "Windows") {
				exec("PsExec -d $pmb_path_php $base_path/admin/planificateur/run_task.php ".$row["id_tache"]." ".$row["num_type_tache"]." ".$row["id_planificateur"]." ".$row["num_user"]." ".$connectors_out_source_id,$output);
			} else if ($os == "Mac") {
				exec("nohup $pmb_path_php  $base_path/admin/planificateur/run_task.php ".$row["id_tache"]." ".$row["num_type_tache"]." ".$row["id_planificateur"]." ".$row["num_user"]." ".$connectors_out_source_id." > /dev/null 2>&1 & echo $!", $output);
			} else {
				exec("nohup $pmb_path_php  $base_path/admin/planificateur/run_task.php ".$row["id_tache"]." ".$row["num_type_tache"]." ".$row["id_planificateur"]." ".$row["num_user"]." ".$connectors_out_source_id." > /dev/null 2>&1 & echo $!", $output);
			}
			$id_process = (int)$output[0];
			
			$update_process = "update taches set id_process='".$id_process."' where id_tache='".$row["id_tache"]."'";		
			mysql_query($update_process,$dbh);
		}
	}
	
	/*Retourne la liste des t�ches r�alis�es et planifi�es
	 */
	function listTasksPlanned() {
		global $dbh;

		$result = array();
		
		$sql = "SELECT t.id_tache, p.libelle_tache, p.desc_tache,";
		$sql .= "t.start_at, t.end_at, t.indicat_progress, t.status";
		$sql .= "FROM taches t, planificateur p WHERE t.num_planificateur=p.id_planificateur"; 
			
		$res = mysql_query($sql, $dbh);
		if ($res) {
			while($row = mysql_fetch_assoc($res)) {
				$result[] = array (
						"id_tache" => $row["id_tache"],
						"libelle_tache" => utf8_normalize($row["libelle_tache"]),
						"desc_tache" => utf8_normalize($row["desc_tache"]),
						"start_at" => $row["start_at"],
						"end_at" => $row["end_at"],
						"indicat_progress" => $row["indicat_progress"],
						"status" => $row["status"],
				);
			}
		}
		return $result;
	}
	
	/*Retourne les types de t�ches*/
	function listTypesTasks() {
		global $dbh;

		$result = array();
	
		$filename="../admin/planificateur/catalog.xml";
		$xml=file_get_contents($filename);
		$param=_parser_text_no_function_($xml,"CATALOG");
		
		foreach ($param["ACTION"] as $anitem) {
			$t=array();
			$t["ID"] = $anitem["ID"];
			$t["NAME"] = $anitem["NAME"];
			$t["COMMENT"] = $anitem["COMMENT"];
			$types_taches[$t["ID"]] = $t;
		}				
		return $types_taches;
	}
	
	/*Retourne les informations concernant une t�che planifi�e
	 */
	function getInfoTaskPlanned($planificateur_id, $active="") {
		global $dbh;

		$result = array();

		$planificateur_id += 0;
		if (!$planificateur_id)
			throw new Exception("Missing parameter: planificateur_id");

		if ($active !="") {
			$critere = " and statut=".$active;
		} else {
			$critere ="";
		}
		
		$sql = "SELECT * FROM planificateur WHERE id_planificateur = ".$planificateur_id;
		$sql = $sql.$critere;
		$res = mysql_query($sql,$dbh);
		if (!$res)
			throw new Exception("Not found: planificateur_id = ".$planificateur_id);
		
		while ($row = mysql_fetch_assoc($res)) {
			$result[] = array(
				"id_planificateur" => $row["id_planificateur"],
				"num_type_tache" => $row["num_type_tache"],
				"libelle_tache" => utf8_normalize($row["libelle_tache"]),
				"desc_tache" => utf8_normalize($row["desc_tache"]),
				"num_user" => $row["num_user"],
				"statut" => $row["statut"],
				"calc_next_date_deb" => utf8_normalize($row["calc_next_date_deb"]),
				"calc_next_heure_deb" => utf8_normalize($row["calc_next_heure_deb"]),
			);
		}		
		return $result;
	}
	
	function createNewTask($id_tache, $id_type_tache, $id_planificateur) {
		global $base_path;
	
		if (!$id_tache)
			throw new Exception("Missing parameter: id_tache");
	
		$filename = $base_path."/admin/planificateur/catalog.xml";
		$xml=file_get_contents($filename);
		$param=_parser_text_no_function_($xml,"CATALOG");
		
		foreach ($param["ACTION"] as $anitem) {
			if($id_type_tache == $anitem["ID"]) {
				require_once($base_path."/admin/planificateur/".$anitem["NAME"]."/".$anitem["NAME"].".class.php");
				$obj_type = new $anitem["NAME"]($id_tache);
				$obj_type->calcul_execution($id_planificateur);
				$obj_type->insertOfTask($id_planificateur);
			}
		}
	}

	/**
	 * 
	 * Change le statut d'une planification
	 * @param $id_planificateur 
	 * @param $activation (0=false, 1=true)
	 */
	function changeStatut($id_planificateur,$activation='') {
		global $dbh;
		
		if (!$id_planificateur)
			throw new Exception("Missing parameter: id_planificateur");
			
		$sql = "select statut from planificateur where id_planificateur=".$id_planificateur;
		$res = mysql_query($sql, $dbh);
		
		if (mysql_num_rows($res) == "1") {
			$statut_sql = mysql_result($res, 0,"statut");
			if ((($statut_sql == "0") && ($activation == "1")) ||
				(($statut_sql == "1") && ($activation == "0"))) {
				$sql_update = "update planificateur set statut=".$activation." where id_planificateur=".$id_planificateur;
				mysql_query($sql_update, $dbh);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

?>