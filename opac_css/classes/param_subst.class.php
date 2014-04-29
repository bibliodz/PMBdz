<?php
// +-------------------------------------------------+
// © 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: param_subst.class.php,v 1.3 2012-02-23 16:29:12 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class param_subst {
	var $values = array();
	var $subst_param = array();
	var $type;	
	var $module;
	var $module_num;
	
	function param_subst($type, $module, $module_num) {
		$this->type = $type;// opac, acquisition...
		$this->module = $module;// opac_view
		$this->module_num = $module_num;// pour évolution...
		$this->fetch_data();
	}

	function fetch_data() {
		global $dbh;

		$this->subst_param=array();
		$myQuery = mysql_query("SELECT * FROM param_subst where subst_type_param= '".$this->type."' and  subst_module_param= '".$this->module."' and subst_module_num= '".$this->module_num."' ", $dbh);
		if(mysql_num_rows($myQuery)){
			while(($r=mysql_fetch_assoc($myQuery))) {
				$this->subst_param[]=$r;
			}
		}
	}

	function set_parameters() {
		foreach($this->subst_param as $param){
			$subst_param_name = $param["subst_type_param"]."_".$param["subst_sstype_param"];
			global $$subst_param_name;
			$$subst_param_name=$param["subst_valeur_param"];
			
		}
	}
}
?>