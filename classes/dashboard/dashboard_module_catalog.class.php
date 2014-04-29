<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: dashboard_module_catalog.class.php,v 1.5 2014-03-11 13:22:23 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/dashboard/dashboard_module.class.php");
require_once("$class_path/abts_pointage.class.php");

class dashboard_module_catalog extends dashboard_module {

	public function __construct(){
		global $msg,$base_path;
		$this->template = "template";
		$this->module = "catalog";
		$this->module_name = $msg[6];
		$this->alert_url = $base_path."/ajax.php?module=ajax&categ=alert&current_alert=".$this->module;
		parent::__construct();
	}	
	
	public function get_quick_params_form(){
		global $msg;
		if(SESSrights & PREF_AUTH) {
			$html= "
			<div class='catalog'>
			<form name='quick_params_catalog' action='' method='post' onsubmit='return false;'>
				<h3>".$msg['dashboard_quick_actions']."</h3>
				<div class='form-contenu'>";
			$html.= $this->get_user_param_form("deflt_docs_location");
	 		$html.= $this->get_user_param_form("deflt_docs_section");
	 		$html.= $this->get_user_param_form("deflt_docs_type");
			$html.="
					<div class='row'></div>
				</div>
				<div class='row'>
					<div class='left'>
						<input type='submit' class='bouton' value='".$msg[77]."' onclick='save_catalog_params()'/>
						<span id='quick_params_catalog_infos'></span>
					</div>
				</div>
				<div class='row'></div>
			</form>
			</div>
			<script type='text/javascript'>
				function save_catalog_params(){
					var parameters = '';
	 				var deflt2docs_location = document.forms['quick_params_catalog'].form_deflt_docs_location;
	 				if(deflt2docs_location){
						for (i=0 ; i<deflt2docs_location.options.length ; i++){
		 					if(deflt2docs_location.options[i].selected == true){
		  						parameters = 'deflt2docs_location='+deflt2docs_location.options[i].value;
		 						break;
		 					}
		 				}
					}
					var deflt_docs_section = document.forms['quick_params_catalog'].f_ex_section1;
	 				if(deflt_docs_section){
						for (i=0 ; i<deflt_docs_section.options.length ; i++){
		 					if(deflt2docs_location.options[i].selected == true){
		  						if(parameters != '') parameters+='&';
								parameters+= 'deflt_docs_section='+f_ex_section1.options[i].value;
		 						break;
		 					}
		 				}
					}
	 				var deflt_docs_type = document.forms['quick_params_catalog'].form_deflt_docs_type;
	 				if(deflt_docs_type){
						for (i=0 ; i<deflt_docs_type.options.length ; i++){
		 					if(deflt_docs_type.options[i].selected == true){
		  						if(parameters != '') parameters+='&';
								parameters+= 'deflt_docs_type='+deflt_docs_type.options[i].value;
		 						break;
		 					}
		 				}
					}
					var req= new http_request();
					req.request('./ajax.php?module=catalog&categ=dashboard&sub=save_quick_params',1,parameters,1,catalog_params_saved);
				}
				function catalog_params_saved(text){
					if(text == 1){
						document.getElementById('quick_params_catalog_infos').innerHTML='<h2>".addslashes($msg['dashboard_saved_quick_params'])."</h2>';
					}else{
						document.getElementById('quick_params_catalog_infos').innerHTML='<h2>".addslashes($msg['ajax_saved_failed'])."</h2>';
					}
					setTimeout(function(){document.getElementById('quick_params_circ_infos').innerHTML=''},3000);
				}
			</script>";
		}
		return $html;
	}
		
	public function get_records_recevoir(){
		$return = array();
		global $memo_abts_pointage_calc_alert;
		if(!count($memo_abts_pointage_calc_alert)){	
			$abt=new abts_pointage();
			$memo_abts_pointage_calc_alert=$abt->calc_alert();
		}
		$return[0]["total"]=$memo_abts_pointage_calc_alert["a_recevoir"];
		return $return;
	}	
	
	public function get_records_prochain(){
		$return = array();
		global $memo_abts_pointage_calc_alert;
		if(!count($memo_abts_pointage_calc_alert)){	
			$abt=new abts_pointage();
			$memo_abts_pointage_calc_alert=$abt->calc_alert();
		}
		$return[0]["total"]=$memo_abts_pointage_calc_alert["prochain_numero"];
		return $return;
	}	
	
	public function get_records_retard(){
		$return = array();
		global $memo_abts_pointage_calc_alert;
		if(!count($memo_abts_pointage_calc_alert)){	
			$abt=new abts_pointage();
			$memo_abts_pointage_calc_alert=$abt->calc_alert();
		}
		$return[0]["total"]=$memo_abts_pointage_calc_alert["en_retard"];
		return $return;
	}
	
	public function get_records_alerte(){
		$return = array();
		global $memo_abts_pointage_calc_alert;
		if(!count($memo_abts_pointage_calc_alert)){	
			$abt=new abts_pointage();
			$memo_abts_pointage_calc_alert=$abt->calc_alert();
		}
		$return[0]["total"]=$memo_abts_pointage_calc_alert["en_alerte"];
		return $return;
	}
	
	public function save_quick_params(){
		$query = "update users set ";
		$update=array();
		foreach($_POST as $key => $value){
			switch($key){
				case "deflt_docs_location":
				case "deflt_docs_section":
				case "deflt_docs_type":
					global $$key;
					$update[] = $key."='".$value."'";
					break;
			}
		}
		if(count($update)){
			$query.=implode(", ",$update)." where userid=".SESSuserid;
			$result = mysql_query($query);
			return $result;
		}
		return true;
	}
	
}