<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: run_task.php,v 1.5 2013-04-08 14:56:08 mbertin Exp $

$base_path="..";
$base_title="";
$base_noheader=1;
$base_nocheck=1;
$base_nobody=1;
$base_nosession=1;

$class_path = $base_path."/classes";
$include_path = $base_path."/includes";

require_once($include_path."/init.inc.php");
require_once($include_path."/db_param.inc.php");
require_once($include_path."/mysql_connect.inc.php");
require_once($class_path."/external_services.class.php");
require_once($class_path."/tache.class.php");
require_once($class_path."/connecteurs_out.class.php");

$dbh = connection_mysql();

if (!$user_id) $user_id=$argv[4];

$requete_nom = "SELECT nom, prenom, user_email, userid, username, rights, user_lang, deflt_docs_location, deflt2docs_location  FROM users 
	LEFT JOIN es_esgroups on userid=esgroup_pmbusernum
	LEFT JOIN es_esusers on esgroup_id=esuser_groupnum
	WHERE esuser_id=$user_id";
$res_nom = mysql_query($requete_nom, $dbh);
@$param_nom = mysql_fetch_object ( $res_nom );
$lang = $param_nom->user_lang ;
$PMBusernom=$param_nom->nom ;
$PMBuserprenom=$param_nom->prenom ;
$PMBuseremail=$param_nom->user_email ;
$deflt_docs_location=$param_nom->deflt_docs_location;
$deflt2docs_location=$param_nom->deflt2docs_location; 	
// pour que l'id user soit dispo partout
define('SESSuserid'	, $param_nom->userid);
$PMBuserid = $param_nom->userid;
$PMBusername = $param_nom->username;

//droits utilisateurs
define('SESSrights'	, $param_nom->rights);

$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
$messages->analyser();
$msg = $messages->table;


function run_task($id_tache, $type_tache, $id_planificateur, $num_es_user, $connectors_out_source_id) {
	global $base_path,$dbh;
	global $PMBuserid;

	@ini_set('zend.ze1_compatibility_mode',0);
	
	$query = "select * from connectors_out_sources where connectors_out_source_id=".$connectors_out_source_id;
	$res = mysql_query($query);
	$row = mysql_fetch_object($res);

	$connectors_out_sources_connectornum = $row->connectors_out_sources_connectornum; 

	$daconn = instantiate_connecteur_out($connectors_out_sources_connectornum);
	if ($daconn) {
		$source_object = $daconn->instantiate_source_class($connectors_out_source_id);
	} else {
		$source_object= NULL;
	}
	
	$es=new external_services();

	$array_functions = array();
	foreach ($source_object->config["exported_functions"] as $exported_function) {
		$array_functions[] = $exported_function["group"]."_".$exported_function["name"];
	}
	$proxy=$es->get_proxy($PMBuserid,$array_functions);

	$filename = $base_path."/admin/planificateur/catalog.xml";
	$xml=file_get_contents($filename);
	$param=_parser_text_no_function_($xml,"CATALOG");
	
	foreach ($param["ACTION"] as $anitem) {
		if($type_tache == $anitem["ID"]) {
			require_once($base_path."/admin/planificateur/".$anitem["NAME"]."/".$anitem["NAME"].".class.php");
			$obj_type = new $anitem["NAME"]($id_tache);
			$obj_type->setEsProxy($proxy);
			$obj_type->execute();
			$obj_type->checkParams($id_planificateur);
		}
	}
}

if ($argv[1] && $argv[2] && $argv[3] && $argv[4] && $argv[5]) {	
	run_task($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);
}