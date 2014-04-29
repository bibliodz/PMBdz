<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: dashboard_module_circ.class.php,v 1.4 2014-02-24 09:19:59 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/dashboard/dashboard_module.class.php");

class dashboard_module_circ extends dashboard_module {

	
	public function __construct(){
		global $msg,$base_path;
		$this->template = "template";
		$this->module = "circ";
		$this->module_name = $msg[5];
		$this->alert_url = $base_path."/ajax.php?module=ajax&categ=alert&current_alert=".$this->module;
		parent::__construct();
	}	
	

	public function get_quick_params_form(){
		global $msg;
		if(SESSrights & PREF_AUTH) {
			$html= "
			<div class='circ'>
			<form name='quick_params_circ' action='' method='post' onsubmit='return false;' class='form-circ'>
				<h3>".$msg['dashboard_quick_actions']."</h3>
				<div class='form-contenu'>";
			$html.= $this->get_user_param_form("deflt2docs_location");
			$html.= $this->get_user_param_form("deflt_cashdesk");
			$html.="
					<div class='row'></div>
				</div>
				<div class='row'>
					<div class='left'>
						<input type='submit' class='bouton' value='".$msg[77]."' onclick='save_circ_params()'/>
						<span id='quick_params_circ_infos'></span>
					</div>
				</div>
				<div class='row'></div>
			</form>
			</div>
			<script type='text/javascript'>
				function save_circ_params(){
					var parameters = '';
					var deflt2docs_location = document.forms['quick_params_circ'].form_deflt2docs_location;
					if(deflt2docs_location){
						for (i=0 ; i<deflt2docs_location.options.length ; i++){
							if(deflt2docs_location.options[i].selected == true){
	 							parameters = 'deflt2docs_location='+deflt2docs_location.options[i].value;
								break;
							}
						}
					}
					var param_allloc = document.forms['quick_params_circ'].form_param_allloc;
					if(param_allloc){
						if(param_allloc.checked){
							parameters+='&param_allloc=1';	
						}else{
							parameters+='&param_allloc=0';			
						}
					}
					var deflt_cashdesk = document.forms['quick_params_circ'].form_deflt_cashdesk;
					if(deflt_cashdesk){
						for (i=0 ; i<deflt_cashdesk.options.length ; i++){
							if(deflt_cashdesk.options[i].selected == true){
	 							parameters+= '&deflt_cashdesk='+deflt_cashdesk.options[i].value;
								break;
							}
						}
					}	
					var req= new http_request();
					req.request('./ajax.php?module=circ&categ=dashboard&sub=save_quick_params',1,parameters,1,circ_params_saved);
				}
				function circ_params_saved(text){
					if(text == 1){
						document.getElementById('quick_params_circ_infos').innerHTML='<h2>".addslashes($msg['dashboard_saved_quick_params'])."</h2>';
					}else{
						document.getElementById('quick_params_circ_infos').innerHTML='<h2>".addslashes($msg['ajax_saved_failed'])."</h2>';	
					}
					setTimeout(function(){document.getElementById('quick_params_circ_infos').innerHTML=''},3000);	
				}
			</script>";
		}
		return $html;
	}
	
	public function save_quick_params(){		
		$query = "update users set ";
		$update=array();
		foreach($_POST as $key => $value){
			switch($key){
				case "deflt2docs_location": 
				case "param_allloc": 
				case "deflt_cashdesk":
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