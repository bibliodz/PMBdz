<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: proc.class.php,v 1.4 2012-11-23 14:39:48 dgoron Exp $

global $class_path, $include_path;
require_once($include_path."/parser.inc.php");
require_once($include_path."/fields.inc.php");
require_once($class_path."/tache.class.php");
require_once($class_path."/parameters.class.php");
require_once("$class_path/remote_procedure_client.class.php");

define ('1','INTERNAL');
define ('2','EXTERNAL');

class proc extends tache {
	
	function proc($id_tache=0){
		global $base_path;
		
		parent::get_messages($base_path."/admin/planificateur/".get_class());
		$this->id_tache = $id_tache;

	}
	
	//formulaire sp�cifique au type de t�che
	function show_form ($param='') {
		global $subaction,$dbh,$aff_list, $msg;
		global $pmb_procedure_server_credentials, $pmb_procedure_server_address;
		
		if ($subaction == 'change') {
			global $type_proc, $form_procs, $form_procs_remote;
		} else {
			if (is_array($param)) {
				foreach ($param as $aparam=>$aparamv) {
					if (is_array($aparamv)) {
						foreach ($aparamv as $sparam=>$sparamv) {
							global $$sparam;
							$$sparam = $sparamv;
						}
					} else {
						global $$aparam;
						$$aparam = $aparamv;	
					}		
				}				
			}
		}
		
		$form_task .= "<script>
			function reload_type_proc(obj) {
					document.getElementById('subaction').value='change';
					obj.form.submit();
				
			}
			function reload(obj) {
					document.getElementById('subaction').value='change';
					obj.form.submit();
			}
		</script>";
		
		// Proc�dure interne ou Proc�dure distante ??
		$form_task .= "
		<div class='row'>
			<div class='colonne3'>
				<label for='proc'>".$this->msg["planificateur_proc_type"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='radio' id='type_proc' name='type_proc' value='internal' ".($type_proc == 'internal' ? 'checked' : '')." onchange='reload_type_proc(this);' />".$this->msg["planificateur_proc_internal"]."
				<input type='radio' id='type_proc' name='type_proc' value='remote' ".($type_proc == 'remote' ? 'checked' : '')." onchange='reload_type_proc(this);' />".$this->msg["planificateur_proc_remote"]."
			</div>
		</div>
		<div class='row'>&nbsp;</div>";
		
		//proc�dure interne
		if ($type_proc == 'internal') {
			//Choix d'une proc�dure
			$form_task .= "
			<div class='row'>
				<div class='colonne3'>
					<label for='proc'>".$this->msg["planificateur_proc_perso"]."</label>
				</div>
				<div class='colonne_suite'>
					<select id='form_procs' class='saisie-60em' name='form_procs' onchange='reload(this);'>
						<option id='' value='' >".$this->msg["planificateur_proc_choice"]."</option>";
						$requete = "SELECT idproc, name FROM procs order by name";
						$result = mysql_query($requete,$dbh);
						while ($row = mysql_fetch_object($result)) {
							$form_task .= "<option  value='".$row->idproc."' ".($form_procs == $row->idproc ? 'selected=\'selected\'' : '' ).">".$row->name."</option>";
						}
			$form_task .="</select>
				</div>
			</div>
			<div class='row'>&nbsp;</div>";		
		
			if ($form_procs) {
				$form_task .= "<div class='row'>
					<div class='colonne3'>
						<label for='source'>&nbsp;</label>
					</div>
					<div class='colonne_suite' id='param_proc' >";
						$hp=new parameters($form_procs,"procs");
						if (preg_match_all("|!!(.*)!!|U",$hp->proc->requete,$query_parameters))
						$form_task .= $hp->gen_form_plann();
				$form_task .= "</div>
					</div>";
			}
		} else if ($type_proc == 'remote') {
			$form_task .= "<div class='row'>
				<div class='colonne3'>
					<label for='proc'>".$this->msg["planificateur_proc_perso"]."</label>
				</div>
				<div class='colonne_suite'>";
			//
			//Proc�dures Externes
			//
			$pmb_procedure_server_credentials_exploded = explode("\n", $pmb_procedure_server_credentials);
			if ($pmb_procedure_server_address && (count($pmb_procedure_server_credentials_exploded) == 2)) {
				$aremote_procedure_client = new remote_procedure_client($pmb_procedure_server_address, trim($pmb_procedure_server_credentials_exploded[0]), trim($pmb_procedure_server_credentials_exploded[1]));
				$procedures = $aremote_procedure_client->get_procs("AP");
				
				if ($procedures) {
					if ($procedures->error_information->error_code) {
						$form_task .=$msg['remote_procedures_error_server'].":<br><i>".$procedures->error_information->error_string."</i>";
					}
					else if (isset($procedures->elements)){
						$form_task .= "<select id='form_procs_remote' class='saisie-60em' name='form_procs_remote' onchange='reload(this);'>";
						foreach ($procedures->elements as $aprocedure) {
						    $form_task .= "<option value='".$aprocedure->id."' ".($form_procs_remote == $aprocedure->id ? "selected" : "").">".($aprocedure->untested ? "[<i>".$msg["remote_procedures_procedure_non_validated"]."</i>]&nbsp;&nbsp;" : '')."<strong>$aprocedure->name</strong></option>";    
						}
						$form_task .= "</select>";
					}
					else {
						$form_task .="<br>".$msg["remote_procedures_no_procs"]."<br><br>";
					}
				}
				$form_task .= "</div>
				</div>
				<div class='row'>&nbsp;</div>";
			
				if ($form_procs_remote) {
					$id = $form_procs_remote;
					$procedure = $aremote_procedure_client->get_proc($id,"AP");
					
					$form_task .= "<div class='row'>
							<div class='colonne3'>
								<label for='source'>&nbsp;</label>
							</div>
							<div class='colonne_suite' id='param_proc_remote' >";
					
					if ($procedure["error_message"]) {
						$form_task .= htmlentities($msg["remote_procedures_error_server"], ENT_QUOTES, $charset).":<br><i>".$procedure["error_message"]."</i>";		
					} else {
						$the_procedure = $procedure["procedure"];
						if ($the_procedure->params && ($the_procedure->params != "NULL")) {
							$sql = "CREATE TEMPORARY TABLE remote_proc LIKE procs";
							mysql_query($sql, $dbh) or die(mysql_error());
							
							$sql = "INSERT INTO remote_proc (idproc, name, requete, comment, autorisations, parameters, num_classement) VALUES (0, '".mysql_escape_string($the_procedure->name)."', '".mysql_escape_string($the_procedure->sql)."', '".mysql_escape_string($the_procedure->comment)."', '', '".mysql_escape_string($the_procedure->params)."', 0)";
							mysql_query($sql, $dbh) or die(mysql_error());
							$idproc = mysql_insert_id($dbh);
							
							$hp=new parameters($idproc,"remote_proc");
							if (preg_match_all("|!!(.*)!!|U",$hp->proc->requete,$query_parameters))
							$form_task .= $hp->gen_form_plann();
						}
					}
					$form_task .= "</div>
						</div>";	
				}	
			}
		}
		
		return $form_task;
	}
	
	function task_execution() {
		global $dbh,$msg, $PMBusername;

		if (SESSrights & ADMINISTRATION_AUTH) {
			$parameters = $this->unserialize_task_params();
	
			if ($parameters["type_proc"]) {
				if ($parameters["type_proc"] == 'internal') {
					//v�rifie que la proc�dure existe toujours en base PMB
					$res = mysql_query("SELECT name FROM procs where idproc=".$parameters["form_procs"],$dbh);
					if (mysql_num_rows($res) == 1) {
						$id_proc = $parameters["form_procs"];
						$row = mysql_fetch_object($res);					
						if($this->statut == RUNNING) {
							$this->report[] = "<tr><th>".$this->msg["proc_execution"]." : ".$row->name."</th></tr>";
							if (method_exists($this->proxy, "pmbesProcs_executeProc")) {
								$result_proc = $this->proxy->pmbesProcs_executeProc(INTERNAL,$id_proc,$parameters["envt"]);
								$this->report[] = "<tr><td>".$result_proc["report"]."</td></tr>";
								$this->update_progression(100);
							} else {
								$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],"executeProc","pmbesProcs",$PMBusername)."</td></tr>";
							}
						}
					} else {
						$this->report[] = $this->msg["proc_unknown"];
					}
				} else if ($parameters["type_proc"] == 'remote') {
					$id_proc = $parameters["form_procs_remote"];
					if($this->statut == RUNNING) {
						if (method_exists($this->proxy, "pmbesProcs_executeProc")) {
							$result_proc = $this->proxy->pmbesProcs_executeProc(EXTERNAL,$id_proc,$parameters["envt"]);
							$this->report[] = "<tr><th>".$this->msg["proc_execution_remote"]." : ".$result_proc["name"]."</th></tr>";
							$this->report[] = "<tr><td>".$result_proc["report"]."</td></tr>";
							$this->update_progression(100);
						} else {
							$this->report[] = "<tr><th>".$this->msg["proc_execution_remote"]."</th></tr>";
							$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],"executeProc","pmbesProcs",$PMBusername)."</td></tr>";
						}
					}
				} else {
					$this->report[] = "<tr><td>".$this->msg["proc_error"]."</td></tr>";
				}
			} else {
				$this->report[] = "<tr><td>".$this->msg["proc_error"]."</td></tr>";
			}
		} else {
			$this->report[] = "<tr><th>".sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername)."</th></tr>";
		}
	}
    
	function make_serialized_task_params() {
    	global $dbh, $type_proc, $form_procs, $form_procs_remote;
    	global $pmb_procedure_server_credentials, $pmb_procedure_server_address;
		
		$t = parent::make_serialized_task_params();
		
		$t["type_proc"] = stripslashes($type_proc);
		$t["form_procs"] = stripslashes($form_procs);
		$t["form_procs_remote"] = stripslashes($form_procs_remote);		
		if ($form_procs) {
			$hp=new parameters($form_procs,"procs");
			$t["envt"]=$hp->make_serialized_parameters_params();
		} else if ($form_procs_remote) {
			$id = $form_procs_remote;

			$pmb_procedure_server_credentials_exploded = explode("\n", $pmb_procedure_server_credentials);
			if ($pmb_procedure_server_address && (count($pmb_procedure_server_credentials_exploded) == 2)) {
				$aremote_procedure_client = new remote_procedure_client($pmb_procedure_server_address, trim($pmb_procedure_server_credentials_exploded[0]), trim($pmb_procedure_server_credentials_exploded[1]));
				$procedure = $aremote_procedure_client->get_proc($id,"AP");
				if (!$procedure["error_message"]) {
					$the_procedure = $procedure["procedure"];
					if ($the_procedure) {
						$sql = "CREATE TEMPORARY TABLE remote_proc LIKE procs";
						mysql_query($sql, $dbh) or die(mysql_error());
						
						$sql = "INSERT INTO remote_proc (idproc, name, requete, comment, autorisations, parameters, num_classement) VALUES (0, '".mysql_escape_string($the_procedure->name)."', '".mysql_escape_string($the_procedure->sql)."', '".mysql_escape_string($the_procedure->comment)."', '', '".mysql_escape_string($the_procedure->params)."', 0)";
						mysql_query($sql, $dbh) or die(mysql_error());
						$idproc = mysql_insert_id($dbh);
						
						$hp=new parameters($idproc,"remote_proc");
						$t["envt"]=$hp->make_serialized_parameters_params();
					}
				}
			}
		}

    	return serialize($t);
	}
	
	function unserialize_task_params() {
    	$params = $this->get_task_params();
		
		return $params;
    }
}