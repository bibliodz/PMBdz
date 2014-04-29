<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesSync.class.php,v 1.4 2012-11-22 11:37:45 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");
require_once($class_path."/connecteurs.class.php");

class pmbesSync extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	var $id_source;
	var $id_tache;
	var $callback_listen_command;
	var $callback_deals_command;
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	/* Liste des connecteurs sources �tant un entrep�t */
	function listEntrepotSources() {
		global $dbh;
		
		if (SESSrights & ADMINISTRATION_AUTH) {
			$result = array();
			
			$requete = "select source_id, id_connector, comment, name from connectors_sources where repository=1";
			$res = mysql_query($requete) or die(mysql_error());
				
			while ($row = mysql_fetch_assoc($res)) {
				$result[] = array(
					"source_id" => $row["source_id"],
					"id_connector" => utf8_normalize($row["id_connector"]),
					"comment" => utf8_normalize($row["comment"]),
					"name_connector_in" => utf8_normalize($row["name"]),
				);
			}
			return $result;
		} else {
			return array();
		}
	}

	function callback_progress($percent,$nlu,$ntotal) {
		global $charset,$dbh;
		
		$callback_listen_command = $this->callback_listen_command;
		$callback_deals_command = $this->callback_deals_command;
		
		$requete="update source_sync set percent=".round($percent*100)." where source_id=".$this->id_source;
		$r=mysql_query($requete,$dbh);
		
		if ($this->id_tache != "") {
			$requete = "update taches set indicat_progress =".round($percent*100)." where id_tache=".$this->id_tache;
			mysql_query($requete,$dbh);
		}
		
		// listen commands
		if ($callback_listen_command != NULL)
			call_user_func($callback_listen_command,$this->id_tache,$callback_deals_command);
	}
	
	/* Lancement de la synchronisation */
	function doSync($id_connector, $id_source,$auto_import = false, $id_tache='', $callback_listen_command=NULL, $callback_deals_command=NULL) {
		global $base_path, $dbh,$PMBuserid, $PMBusername, $msg, $charset;

		if ((!$id_connector) || (!$id_source))
			return array();
		
		if (SESSrights & ADMINISTRATION_AUTH) {
			$this->callback_listen_command = $callback_listen_command;
			$this->callback_deals_command = $callback_deals_command;
			
			$result=array();
			$this->id_source = $id_source;
			$this->id_tache = $id_tache;
			
			$contrs=new connecteurs();
			require_once($base_path."/admin/connecteurs/in/".$contrs->catalog[$id_connector]["PATH"]."/".$contrs->catalog[$id_connector]["NAME"].".class.php");
			eval("\$conn=new ".$contrs->catalog[$id_connector]["NAME"]."(\"".$base_path."/admin/connecteurs/in/".$contrs->catalog[$id_connector]["PATH"]."\");");
			
			//V�rification qu'il n'y a pas de synchronisation en cours...
			$is_already_sync=false;
			$recover_env="";
			$recover=false;
			$requete="select * from source_sync where source_id=$id_source";
			$resultat=mysql_query($requete, $dbh);
			if (mysql_num_rows($resultat)) {
				$rs_s=mysql_fetch_object($resultat);
				if (!$rs_s->cancel) {
					$result[] =  $conn->msg["connecteurs_sync_currentexists"];
					$is_already_sync=true;
				} else {
					$recover=true;
					$recover_env=$rs_s->env;
					$env = array();
				}
			} else {
				$env = $conn->get_maj_environnement($id_source);
				
			}
		
			if (!$is_already_sync) {
				if (!$recover) {
					$requete="insert into source_sync (source_id,nrecu,ntotal,date_sync) values($id_source,0,0,now())";
					$r=mysql_query($requete, $dbh);
				} 
				else {
					$requete="update source_sync set cancel=0 where source_id=$id_source";
					$r=mysql_query($requete, $dbh);
				}
				if ($r) {
					$n_maj=$conn->maj_entrepot($id_source,array(&$this,"callback_progress"),$recover,$recover_env);
	
					$result[] = sprintf($msg["connecteurs_count_notices"],$n_maj);
					$result[] = $conn->error_message;
					if (!$conn->error) {
						$this->callback_progress(1, $n_maj, $n_maj);
						$percent = 1;
						$requete="update source_sync set percent=".round($percent*100)." where source_id=$id_source";
						$r=mysql_query($requete, $dbh);
			
						$requete="delete from source_sync where source_id=".$id_source;
						mysql_query($requete);
						$requete="update connectors_sources set last_sync_date=now() where source_id=".$id_source;
						mysql_query($requete, $dbh);
					} else {
						if ($conn->break_maj($id_source)) {
							$requete="delete from source_sync where source_id=".$id_source;
						} else {
							$requete="update source_sync set cancel=2 where source_id=".$id_source;
						}
						mysql_query($requete, $dbh);
						$result[] = $conn->error_message;
					}
				} else $result[] = mysql_error();
			} else $result[] = $msg["connecteurs_sync_currentexists"];
	
			//si l'import automatique est activ�
			if($auto_import/* && !$auto_import*/){
				//on va chercher les notices non int�gr�es
				$query = "select distinct entrepot_source_".$id_source.".recid from entrepot_source_".$id_source." left join notices_externes on notices_externes.recid = concat(connector_id,' ".$id_source." ',ref) where num_notice is null";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					while ($row = mysql_fetch_object($result)){
						$infos=entrepot_to_unimarc($row->recid);
						
						if($infos['notice']){
							$z=new z3950_notice("unimarc",$infos['notice'],$infos['source_id']);
							if($pmb_notice_controle_doublons != 0){
								$sign = new notice_doublon(true,$infos['source_id']);
								$signature = $sign->gen_signature($row->recid);
							}else {
								$signature = "";
							}
							$z->signature = $signature;
							if($infos['notice']) $z->notice = $infos['notice'];
							if($infos['source_id']) $z->source_id = $infos['source_id'];
							$z->var_to_post();
							$ret=$z->insert_in_database(false);
							$id_notice = $ret[1];
							$rqt = "select recid from external_count where rid = '$row->recid'";
							$res = mysql_query($rqt);
							if(mysql_num_rows($res)) $recid = mysql_result($res,0,0);
							$req= "insert into notices_externes set num_notice = '".$id_notice."', recid = '".$recid."'";
							mysql_query($req);
						}
					}
				}
			}
			return $result;
		} else {
			return array();	
		}	
	}	
}


?>