<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesProcs.class.php,v 1.3 2012-11-28 15:48:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");
require_once($class_path."/parameters.class.php");

define ('1','INTERNAL');
define ('2','EXTERNAL');

class pmbesProcs extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant à l'erreur
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	function listProcs() {
		global $dbh;
		
		if (SESSrights & ADMINISTRATION_AUTH) {
			$result=array();
			
			$rqt = "select idproc, name, requete, comment from procs";
			$res = mysql_query($rqt, $dbh);
				
			while ($row = mysql_fetch_assoc($res)) {
				$result[] = array (
					"idproc" => $row->idproc,
					"name" => $row->name,
					"requete" => $row->requete,
					"comment" => $row->comment,
				);
			}
			return $result;
		} else {
			return array();
		}
	}
	
	/* 
	 *  
	 */
	function executeProc($procedure, $idProc,$tparams) {
		global $msg,$dbh, $PMBuserid;
		global $pmb_procedure_server_credentials,$pmb_procedure_server_address;
	
		if (SESSrights & ADMINISTRATION_AUTH) {
			$name = "";
			$report = "";
			if ($tparams) {
				foreach ($tparams as $aparam=>$vparam) {
					global $$aparam;
					$$aparam = $vparam;
				}
			}
			switch ($procedure) {
				case INTERNAL:
					$hp=new parameters($idProc,"procs");
					$hp->get_final_query();
					$code_sql=$hp->final_query;
					$autorisations = $hp->proc->autorisations;
					break;
				case EXTERNAL:
					$pmb_procedure_server_credentials_exploded = explode("\n", $pmb_procedure_server_credentials);
					if ($pmb_procedure_server_address && (count($pmb_procedure_server_credentials_exploded) == 2)) {
						$aremote_procedure_client = new remote_procedure_client($pmb_procedure_server_address, trim($pmb_procedure_server_credentials_exploded[0]), trim($pmb_procedure_server_credentials_exploded[1]));
						$procedure = $aremote_procedure_client->get_proc($idProc,"AP");
						$the_procedure = $procedure["procedure"];
						if ($procedure["error_message"]) {
							$report = htmlentities($msg["remote_procedures_error_server"], ENT_QUOTES, $charset).":<br><i>".$procedure["error_message"]."</i>";
							$result = array(
								"name" => $the_procedure->name,
								"report" => $report
							); 
							return $result;				
						} else if ($the_procedure->params && ($the_procedure->params != "NULL")) {
							$sql = "CREATE TEMPORARY TABLE remote_proc LIKE procs";
							mysql_query($sql, $dbh) or die(mysql_error());
							
							$sql = "INSERT INTO remote_proc (idproc, name, requete, comment, autorisations, parameters, num_classement) VALUES (0, '".mysql_escape_string($the_procedure->name)."', '".mysql_escape_string($the_procedure->sql)."', '".mysql_escape_string($the_procedure->comment)."', '', '".mysql_escape_string($the_procedure->params)."', 0)";
							mysql_query($sql, $dbh) or die(mysql_error());
							$idproc = mysql_insert_id($dbh);
							
							$hp=new parameters($idproc,"remote_proc");
							$hp->get_final_query();
							$the_procedure->sql = $hp->final_query;
							$name = $the_procedure->name;
							$code_sql = $the_procedure->sql;
							$commentaire = $the_procedure->comment;
						}	
					}
					break;
			}
			
			$linetemp = explode(";", $code_sql);
			if($autorisations)
				$temp_autorisation = explode(" ", $autorisations);
	
			$allow=false;
			if ($temp_autorisation) {
				foreach ($temp_autorisation as $userid) {
					if ($userid == $PMBuserid)
						$allow = true;
				}
				if (!$allow) {
					$report = $msg["11"];
//					throw new Exception($message, $code);
					$result = array(
						"name" => $name,
						"report" => $report
					); 
					return $result;	
				}			
			}
			
			for ($i=0;$i<count($linetemp);$i++) if (trim($linetemp[$i])) $line[]=trim($linetemp[$i]);
			while(list($cle, $valeur)= each($line)) {
				if($valeur) {
					// traitement des paramètres
					// traitement tri des colonnes
					if ($sortfield != "") {
						// on cherche à trier sur le champ $trifield
						// compose la chaîne de tri
						$tri = $sortfield;
						if ($desc == 1) $tri .= " DESC";
							else $tri .= " ASC";
						// on enlève les doubles espaces dans la procédure
						$valeur = ereg_replace("/\s+/", " ", $valeur);
						// supprime un éventuel ; à la fin de la requête
						$valeur = ereg_replace("/;$/", "", $valeur);
						// on recherche la première occurence de ORDER BY
						$s = stristr($valeur, "order by");
						if ($s) {
							// y'a déjà une clause order by... moins facile...
							// il faut qu'on sache si on aura besoin de mettre une virgule ou pas
							if ( ereg(",", $s) ) {
								$virgule = true;
							} else if ( ! ereg("${sortfield}", $s)) {
								$virgule = true;
							} else {
								$virgule = false;
							}
							if ($virgule) {
								$tri .= ", ";
							}
							// regarde si le champ est déjà dans la liste des champs à trier et le remplace si besoin
							$new_s = preg_replace("/$sortfield, /", "", $s);
							$new_s = preg_replace("/$sortfield/", "", $new_s);
							// ajoute la clause order by correcte
							$new_s = preg_replace("/order\s+by\s+/i", "order by $tri", $new_s);
							// replace l'ancienne chaîne par la nouvelle
							$valeur = str_replace($s, $new_s, $valeur);
						} else {
							$valeur .= " order by $tri";
						}
					}
		
					$report .= "<strong>".$msg["procs_ligne"]." $cle </strong>:&nbsp;$valeur<br /><br />";
					if (explain_requete($valeur)) {
						$res = @mysql_query($valeur, $dbh);
						$report .= mysql_error();
						$nbr_lignes = @mysql_num_rows($res);
						$nbr_champs = @mysql_num_fields($res);
		
						if($nbr_lignes) {
							$report .= "<table >";
							for($i=0; $i < $nbr_champs; $i++) {
								$fieldname = mysql_field_name($res, $i);
								$report .= "<th>${fieldname}</th>";
							}
		
							for($i=0; $i < $nbr_lignes; $i++) {
								$row = mysql_fetch_row($res);
								$report .= "<tr>";
								foreach($row as $dummykey=>$col) {
									if(trim($col)=='') $col = '&nbsp;';
									$report .= '<td >'.$col.'</td>';
								}
								$report .= "</tr>";
							}
							$report .= "</table><hr />";
							$report .= "<font color='#ff0000'>".$msg['admin_misc_lignes']." ".mysql_affected_rows($dbh)."</font>";
						} else {
							$report .= "<br /><font color='#ff0000'>".$msg['admin_misc_lignes']." ".mysql_affected_rows($dbh);
							$err = mysql_error($dbh);
							if ($err) $report .= "<br />$err";
							$report .= "</font><hr />";
						}
					} else {
						// erreur explain_requete
						$report .= $valeur."<br /><br />".$msg["proc_param_explain_failed"]."<br /><br />".$erreur_explain_rqt;
					}
				}
			} // fin while
			
			$result = array(
				"name" => $name,
				"report" => $report
			); 
			return $result;
		}
		return array();
	}
}




?>