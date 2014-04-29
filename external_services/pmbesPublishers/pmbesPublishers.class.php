<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesPublishers.class.php,v 1.5 2013-02-20 16:09:28 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

class pmbesPublishers extends external_services_api_class {
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
	
	function list_publisher_notices($publisher_id, $OPACUserId=-1) {
		global $dbh;
		global $msg;
		$result = array();

		$publisher_id += 0;
		if (!$publisher_id)
			throw new Exception("Missing parameter: author_id");
			
		$requete  = "SELECT notice_id FROM notices WHERE (ed1_id='$publisher_id' or ed2_id='$publisher_id')"; 
			
		$res = mysql_query($requete, $dbh);
		if ($res)
			while($row = mysql_fetch_assoc($res)) {
				$result[] = $row["notice_id"];
			}
	
		//Je filtre les notices en fonction des droits
		$result=$this->filter_tabl_notices($result);
		
		return $result;
	}
	
	function get_publisher_information($publisher_id) {
		global $dbh;
		global $msg;
		$result = array();

		$publisher_id += 0;
		if (!$publisher_id)
			throw new Exception("Missing parameter: publisher_id");
			
		$sql = "SELECT * FROM publishers WHERE ed_id = ".$publisher_id;
		$res = mysql_query($sql);
		if (!$res)
			throw new Exception("Not found: publisher_id = ".$publisher_id);
		$row = mysql_fetch_assoc($res);

		$result = array(
			"publisher_id" => $row["ed_id"],
			"publisher_name" => utf8_normalize($row["ed_name"]),
			"publisher_address1" => utf8_normalize($row["ed_adr1"]),
			"publisher_address2" => utf8_normalize($row["ed_adr2"]),
			"publisher_zipcode" => utf8_normalize($row["ed_cp"]),
			"publisher_city" => utf8_normalize($row["ed_ville"]),
			"publisher_country" => utf8_normalize($row["ed_pays"]),
			"publisher_web" => utf8_normalize($row["ed_web"]),
			"publisher_comment" => utf8_normalize($row["ed_comment"]),
			"publisher_links" => $this->proxy_parent->pmbesAutLinks_getLinks(3, $publisher_id),		
		);
		
		return $result;
	}

	function get_publisher_information_and_notices($publisher_id, $OPACUserId=-1) {
		return array(
			"information" => $this->get_publisher_information($publisher_id),
			"notice_ids" => $this->list_publisher_notices($publisher_id, $OPACUserId=-1)
		);
	}
}




?>