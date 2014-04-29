<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesCollections.class.php,v 1.7 2013-02-20 16:09:28 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

class pmbesCollections extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	var $es;				//Classe m�re qui impl�mente celle-ci !
	var $msg;
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	function list_collection_notices($collection_id, $OPACUserId=-1) {
		global $dbh;
		global $msg;
		$result = array();

		$collection_id += 0;
		if (!$collection_id)
			throw new Exception("Missing parameter: collection_id");

		$requete  = "SELECT notice_id FROM notices WHERE (coll_id='$collection_id') "; 
			
		$res = mysql_query($requete, $dbh);
		if ($res)
			while($row = mysql_fetch_assoc($res)) {
				$result[] = $row["notice_id"];
			}
	
		//Je filtre les notices en fonction des droits
		$result=$this->filter_tabl_notices($result);
		
		return $result;
	}
	
	function list_subcollection_notices($subcollection_id, $OPACUserId=-1) {
		global $dbh;
		global $msg;
		$result = array();

		$subcollection_id += 0;
		if (!$subcollection_id)
			throw new Exception("Missing parameter: collection_id");
			
		$requete  = "SELECT notice_id FROM notices WHERE (subcoll_id='$subcollection_id') "; 
			
		$res = mysql_query($requete, $dbh);
		if ($res)
			while($row = mysql_fetch_assoc($res)) {
				$result[] = $row["notice_id"];
			}
	
		//Je filtre les notices en fonction des droits
		$result=$this->filter_tabl_notices($result);
		
		return $result;
	}
	
	function get_collection_information($collection_id) {
		global $dbh;
		global $msg;
		$result = array();

		$collection_id += 0;
		if (!$collection_id)
			throw new Exception("Missing parameter: collection_id");
			
		$sql = "SELECT * FROM collections WHERE collection_id = ".$collection_id;
		$res = mysql_query($sql);
		if (!$res)
			throw new Exception("Not found: collection_id = ".$collection_id);
		$row = mysql_fetch_assoc($res);

		$result = array(
			"collection_id" => $row["collection_id"],
			"collection_name" => utf8_normalize($row["collection_name"]),
			"collection_parent" => $row["collection_parent"],
			"collection_issn" => utf8_normalize($row["collection_issn"]),
			"collection_web" => utf8_normalize($row["collection_web"]),
			"collection_links" => $this->proxy_parent->pmbesAutLinks_getLinks(4, $collection_id),			
		);
		
		return $result;
	}
	
	function get_subcollection_information($subcollection_id) {
		global $dbh;
		global $msg;
		$result = array();

		$subcollection_id += 0;
		if (!$subcollection_id)
			throw new Exception("Missing parameter: sub_coll_id");
			
		$sql = "SELECT * FROM sub_collections WHERE sub_coll_id = ".$subcollection_id;
		$res = mysql_query($sql);
		if (!$res)
			throw new Exception("Not found: sub_coll_id = ".$subcollection_id);
		$row = mysql_fetch_assoc($res);
		
		$result = array(
			"sous_collection_id" => $row["sub_coll_id"],
			"sous_collection_name" => utf8_normalize($row["sub_coll_name"]),
			"sous_collection_parent" => $row["sub_coll_parent"],
			"sous_collection_issn" => utf8_normalize($row["sub_coll_issn"]),
			"sous_collection_web" => utf8_normalize($row["subcollection_web"]),
			"sous_collection_links" => $this->proxy_parent->pmbesAutLinks_getLinks(5, $subcollection_id),			
		);
		
		return $result;
	}

	function get_collection_information_and_notices($collection_id, $OPACUserId=-1) {
		return array(
			"information" => $this->get_collection_information($collection_id),
			"notice_ids" => $this->list_collection_notices($collection_id, $OPACUserId=-1)
		);
	}
	
	function get_subcollection_information_and_notices($subcollection_id, $OPACUserId=-1) {
		return array(
			"information" => $this->get_subcollection_information($subcollection_id),
			"notice_ids" => $this->list_subcollection_notices($subcollection_id, $OPACUserId=-1)
		);
	}
	
	function list_collection_subcollections($collection_id) {
		global $dbh;
		global $msg;
		$result = array();

		$collection_id += 0;
		if (!$collection_id)
			throw new Exception("Missing parameter: collection_id");
			
		$sql = "SELECT * FROM sub_collections WHERE sub_coll_parent = ".$collection_id;
		$res = mysql_query($sql, $dbh);
		if ($res)
			while($row = mysql_fetch_assoc($res)) {
				$aresult = array(
					"sous_collection_id" => $row["sub_coll_id"],
					"sous_collection_name" => utf8_normalize($row["sub_coll_name"]),
					"sous_collection_parent" => $row["sub_coll_parent"],
					"sous_collection_issn" => utf8_normalize($row["sub_coll_issn"]),
					"sous_collection_web" => utf8_normalize($row["subcollection_web"]),
					"sous_collection_links" => $this->proxy_parent->pmbesAutLinks_getLinks(5, $collection_id),
				);
				$result[] = $aresult;
			}
	
		return $result;
	}
}




?>