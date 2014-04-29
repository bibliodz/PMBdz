<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: dashboard_module_autorites.class.php,v 1.2 2014-01-07 14:34:02 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/dashboard/dashboard_module.class.php");

class dashboard_module_autorites extends dashboard_module {

	
	public function __construct(){
		global $msg;
		$this->template = "template";
		$this->module = "autorites";
		$this->module_name = $msg[132];
		parent::__construct();
	}
	
	public function get_categories_informations(){
		$return = array();
		$query = "select count(id_noeud) as nb from noeuds";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$return = mysql_fetch_assoc($result);
		}
		
		return $return;
	}
	public function get_authors_informations(){
		$return = array();
		$query = "select count(author_id) as nb from authors";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$return = mysql_fetch_assoc($result);
		}
		
		return $return;
	}
}