<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: auth_popup.class.php,v 1.3 2014-02-05 16:00:17 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
// authentification via un "popup" � l'OPAC

require_once($include_path."/empr.inc.php");
require_once($include_path."/empr_func.inc.php");

class auth_popup {
	var $callback_func ="";
	var $callback_url = "";
	var $new_tab=false;
	

	public function __construct(){
		$this->load_js();
	}
	
	
	public function process(){
		global $base_path,$msg;
		global $empty_pwd,$ext_pwd;
		
		global $action;
		global $callback_func;
		global $callback_url,$new_tab;
		
		$this->callback_func = $callback_func;
		$this->callback_url = $callback_url;
		$this->new_tab = $new_tab;
		
		switch($action){
			case 'check_auth' :
				//On tente la connexion
				// si param�trage authentification particuli�re
				$empty_pwd=true;
				$ext_auth=false;
				if (file_exists($base_path.'/includes/ext_auth.inc.php')) { $file_orig="empr.php"; require_once($base_path.'/includes/ext_auth.inc.php'); }
				$log_ok = connexion_empr();
				if($log_ok){
					//r�ussie, on poursuit le tout...		
					$this->success_callback();
				}else{
					print $this->get_form($msg['auth_failed']);
				}
				break;
			case 'get_form' :
			default :
				if(!$_SESSION['user_code']){
					print $this->get_form();
				}else{
					$this->success_callback();
				}
				break;
		}
	}
	
	public function success_callback(){
		$html = "
		<script type='text/javascript'>";
		if($this->callback_func){
			$html .="
			window.parent.".$this->callback_func."('".$_SESSION['id_empr_session']."');";
		}else if ($this->callback_url){
			if($this->new_tab){
				$html .="
			window.open('".$this->callback_url."');";
			}else{
				$html .="
			window.parent.document.location='".$this->callback_url."';";
			}
		}
		$html.="
			var frame = window.parent.document.getElementById('auth_popup');
			frame.parentNode.removeChild(frame);
		</script>";	
		print $html;	
	}
	
	public function load_js(){
		global $include_path;
		// print "<script type='text/javascript' src='".$include_path."/javascript/auth_popup.js'></script>";
	}	
	
	public function show_form(){
		print "
		<div id='auth_popup' style='z-index:inherit;padding:10px;border:1px solid black;background-color:white;height:100px;position:absolute;top:auto;left:auto
		'>".
			genere_form_connexion_empr(true)."
		</div>";
		print "
		<script type='text/javascript'>
			document.getElementById('att').appendChild(document.getElementById('auth_popup'));
		</script>";
	}
	
	public function get_form($message=""){
		global $base_path,$charset;
		global $opac_websubscribe_show,$opac_password_forgotten_show,$msg;
		
		if(!$message){
			$message = $msg["need_auth"];
		}
		$form= "
		<div id='connexion'>
			<span id='login_form'>
				<form action='".$base_path."/ajax.php?module=ajax&categ=auth&action=check_auth' method='post' name='myform'>
					<h4>".$message."</h4><br />
					<input type='text' name='login' class='login' size='14' value=\"".$msg["common_tpl_cardnumber"]."\" onFocus=\"this.value='';\"><br />
					<input type='password' name='password' class='password' size='8' value='' />&nbsp;&nbsp;
					<input type='hidden' name='callback_func' value='".htmlentities($this->callback_func,ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='callback_url' value='".htmlentities($this->callback_url,ENT_QUOTES,$charset)."'/>
					<input type='hidden' name='new_tab' value='".$this->new_tab."'/>
					<input type='submit' name='ok' value='".$msg[11]."' class='bouton'>";
		$form.="</form>";
		if($opac_password_forgotten_show)	
			$form.="<a href='#' onclick='window.parent.location = \"".$base_path."/askmdp.php\";'>".$msg["mdp_forgotten"]."</a>";
		if ($opac_websubscribe_show) 
			$form .= "<br /><a href='#' onclick='window.parent.location = \"".$base_path."/subscribe.php\";'>".$msg["subs_not_yet_subscriber"]."</a>";

		$form.="
			</span>
		</div> ";
		return $form;
	}
}


