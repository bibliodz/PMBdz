<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: oai.class.php,v 1.11 2012-11-13 08:59:17 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($class_path."/connecteurs_out.class.php");
require_once($class_path."/connecteurs_out_sets.class.php");
require_once ($class_path."/external_services_converters.class.php");

class oai extends connecteur_out {
	
	function get_config_form() {
		//Rien
		return '';
	}
	
	function update_config_from_form() {
		return;
	}
	
	function instantiate_source_class($source_id) {
		return new oai_source($this, $source_id, $this->msg);
	}
	
	function process($source_id, $pmb_user_id) {
		global $base_path;
		require_once ($base_path."/admin/connecteurs/out/oai/oai_out_protocol.class.php");
		
		$source_object = $this->instantiate_source_class($source_id);
		
		$oai_server = new oai_out_server($this->msg, $source_object);
		$oai_server->process();
		
		//Rien
		return;
	}
}

class oai_source extends connecteur_out_source {
	var $repository_name="";
	var $admin_email="";
	var $included_sets=array();
	var $repositoryIdentifier="";
	var $chunk_size=100; //Nombre de résultats par requête
	var $token_lifeduration=600; //Durée de vie en seconde des tokens
	var $cache_complete_records=true;
	var $cache_complete_records_seconds=86400; //Une journée
	var $link_status_to_deletion=false;
	var $linked_status_to_deletion=0;
	var $allow_gzip_compression=true;
	var $allowed_set_types=array(
		1, //Set de paniers de notices
		2  //Set multicritère de notices
	);
	var $allowed_admin_convert_paths=array();
	var $baseURL="";
	var $include_items=false; //Inclure les exemplaires
	var $include_links = array('genere_lien'=>0);
	
	function oai_source($connector, $id, $msg) {
		parent::connecteur_out_source($connector, $id, $msg);
		$this->repository_name = isset($this->config["repo_name"]) ? $this->config["repo_name"] : '';
		$this->admin_email = isset($this->config["admin_email"]) ? $this->config["admin_email"] : '';
		$this->included_sets = isset($this->config["included_sets"]) ? $this->config["included_sets"] : array();
		$this->repositoryIdentifier = isset($this->config["repositoryIdentifier"]) ? $this->config["repositoryIdentifier"] : "";
		$this->chunksize = isset($this->config["chunksize"]) ? $this->config["chunksize"] : 100;
		$this->token_lifeduration = isset($this->config["token_lifeduration"]) ? $this->config["token_lifeduration"] : 600;
		$this->cache_complete_records = isset($this->config["cache_complete_records"]) ? $this->config["cache_complete_records"] : true;
		$this->cache_complete_records_seconds = isset($this->config["cache_complete_records_seconds"]) ? $this->config["cache_complete_records_seconds"] : true;
		$this->link_status_to_deletion = isset($this->config["link_status_to_deletion"]) ? $this->config["link_status_to_deletion"] : false;
		$this->linked_status_to_deletion = isset($this->config["linked_status_to_deletion"]) ? $this->config["linked_status_to_deletion"] : 0;
		$this->allow_gzip_compression = isset($this->config["allow_gzip_compression"]) ? $this->config["allow_gzip_compression"] : true;
		$this->allowed_admin_convert_paths = isset($this->config["allowed_admin_convert_paths"]) ? $this->config["allowed_admin_convert_paths"] : array();
		$this->baseURL = isset($this->config["baseURL"]) ? $this->config["baseURL"] : array();
		$this->include_items = isset($this->config["include_items"]) ? $this->config["include_items"] : false;
		if (count($this->config['include_links'])) { 
			$this->include_links = $this->config['include_links'];
		}
	}
	
	function get_config_form() {
		
		global $charset, $dbh;
		
		$result = parent::get_config_form();
		
		//Repository Name
		$result .=	'<div class="row"><label class="etiquette" for="repo_name">'.$this->msg["repository_name"].'</label><br />';
		$result .=	'<input id="repo_name" name="repo_name" type="text" value="'.htmlentities($this->repository_name,ENT_QUOTES, $charset).'" class="saisie-80em" /></div>';

		//Admin Email
		$result .=	'<div class="row"><label class="etiquette" for="admin_email">'.$this->msg["admin_email"].'</label><br />';
		$result .=	'<input id="admin_email" name="admin_email" type="text" value="'.htmlentities($this->admin_email,ENT_QUOTES, $charset).'" class="saisie-80em" /></div>';

		//repositoryIdentifier
		$result .=	'<div class="row"><label class="etiquette" for="repositoryIdentifier">'.$this->msg["repositoryIdentifier"].'</label><br />';
		$result .=	'<input id="repositoryIdentifier" name="repositoryIdentifier" type="text" value="'.htmlentities($this->repositoryIdentifier,ENT_QUOTES, $charset).'" class="saisie-80em" /></div>';

		//baseURL
		$disable_baseurl_fields = $this->id ? "" : "DISABLED";
		$default_base_url = curPageBaseURL();
		$default_base_url = substr($default_base_url, 0, strrpos($default_base_url, '/')+1);
		$default_base_url .= 'ws/connector_out.php?source_id='.$this->id;
		if (!$this->baseURL) {
			$basee = $default_base_url;	
		}
		else {
			$basee = $this->baseURL;
		}
		$result .= '<div class="row"><label class="etiquette" for="baseURL">'.$this->msg["baseURL"].'</label><br />';
		if (!$this->id)
			$result .= $this->msg['baseURL_sourceadd'].'<br />';
		$result .= '<input '.$disable_baseurl_fields.' id="baseURL" name="baseURL" type="text" value="'.htmlentities($basee,ENT_QUOTES, $charset).'" class="saisie-80em" />';
		if ($this->id)
			$result .= '<input '.$disable_baseurl_fields.' type="button" value="'.$this->msg["baseURL_default"].'" class="bouton" onclick="document.getElementById(\'baseURL\').value=\''.htmlentities($default_base_url ,ENT_QUOTES, $charset).'\'" />';
		$result .= '</div>';
		
		//Included sets
		$included_sets = '<select MULTIPLE name="included_sets[]">';
		$included_sets .= '<option value="">'.htmlentities($this->msg["set_none"] ,ENT_QUOTES, $charset).'</option>';
		$sets = new connector_out_sets();

		foreach ($sets->sets as &$aset) {
			if (!in_array($aset->type, $this->allowed_set_types))
				continue;
			$included_sets .= '<option '.(in_array($aset->id, $this->included_sets) ? 'selected' : '').' value="'.$aset->id.'">'.htmlentities($aset->caption ,ENT_QUOTES, $charset).'</option>';
		}
		$included_sets .= '</select>';
		$result .=	'<div class="row"><label class="etiquette" for="included_sets">'.$this->msg["included_sets"].'</label><br />';
		$result .= $included_sets;
		$result .=	'</div>';

		//Nombre de résultats par requete
		$result .=	'<div class="row"><label class="etiquette" for="chunksize">'.$this->msg["chunksize"].'</label><br />';
		$result .=	'<input id="chunksize" name="chunksize" type="text" value="'.htmlentities($this->chunksize,ENT_QUOTES, $charset).'" class="saisie-40em" /></div>';

		//Nombre de résultats par requete
		$result .=	'<div class="row"><label class="etiquette">'.$this->msg["builtin_formats"].'</label><br />';
		$result .=	'Dublin Core, PMB XML Unimarc</div>';
		
		//Formats de conversion admin/convert autorisé
		$admin_convert_catalog = external_services_converter_notices::get_export_possibilities();
		$admin_convert_select = '<select id="allowed_admin_convert_paths" multiple name="allowed_admin_convert_paths[]">';
		foreach ($admin_convert_catalog as $aconversion) {
			$admin_convert_select .= '<option '.(in_array($aconversion["path"], $this->allowed_admin_convert_paths) ? 'selected' : '').' value="'.$aconversion["path"].'">'.htmlentities($aconversion["caption"] ,ENT_QUOTES, $charset).'</option>';
		}
		$admin_convert_select .= '</select>';
		$result .=	'<div class="row"><label class="etiquette" for="allowed_admin_convert_paths">'.$this->msg["allowed_admin_convert_paths"].'</label><br />';
		$result .= $admin_convert_select;
		$result .=	'</div>';
		
		//feuille XSLT personnalisée
		$result .= "<div class='row'><label for='feuille_xslt'>".$this->msg['feuille_xslt']."</label><br />";
		$result .= "<input type='file' name='feuille_xslt'/>";
		if($this->config['feuille_xslt']){
			$result .= "<div class='row'><br />&nbsp;<i>".htmlentities($this->config['feuille_xslt_name'],ENT_QUOTES, $charset)."</i>&nbsp;<input type='checkbox' name='suppr_feuille_xslt' value='1' />&nbsp;".$this->msg['suppr_feuille_xslt']."</div>";
		}
		$result .= "</div><div class='row'>&nbsp;</div>";
		
		//Token life duration
		$result .=	'<div class="row"><label class="etiquette" for="token_lifeduration">'.$this->msg["token_lifeduration"].'</label><br />';
		$result .=	'<input id="token_lifeduration" name="token_lifeduration" type="text" value="'.htmlentities($this->token_lifeduration,ENT_QUOTES, $charset).'" class="saisie-40em" /></div>';

		//Allow GZIP Compression
		$result .=	'<div class=row><input id="allow_gzip_compression" '.($this->allow_gzip_compression ? 'checked' : '').' name="allow_gzip_compression" type="checkbox" />'.'<label class="etiquette" for="allow_gzip_compression">'.$this->msg["allow_gzip_compression"].'</label><br />';
		$result .=	'</div>';
		
		//Cache complete records
		$result .=	'<div class="row"><input onchange="document.getElementById(\'cache_complete_records_seconds\').disabled = !document.getElementById(\'cache_complete_records\').checked;" id="cache_complete_records" '.($this->cache_complete_records ? 'checked' : '').' name="cache_complete_records" type="checkbox" />'.'<label class="etiquette" for="cache_complete_records">'.$this->msg["cache_complete_records"].'</label><br />';
		$result .=	'</div>';

		//Record cache duration (seconds)
		$result .=	'<blockquote><div class="row"><label class="etiquette" for="cache_complete_records_seconds">'.$this->msg["cache_complete_records_seconds"].'</label><br />';
		$result .=	'<input '.($this->cache_complete_records ? '' : 'disabled').' id="cache_complete_records_seconds" name="cache_complete_records_seconds" type="text" value="'.htmlentities($this->cache_complete_records_seconds,ENT_QUOTES, $charset).'" class="saisie-40em" /></div></blockquote><br />';
		
		//Link Status to deletion
		$result .=	'<div class="row"><input onchange="document.getElementById(\'linked_status_to_deletion\').disabled = !document.getElementById(\'link_status_to_deletion\').checked;" id="link_status_to_deletion" '.($this->link_status_to_deletion ? 'checked' : '').' name="link_status_to_deletion" type="checkbox" />'.'<label class="etiquette" for="link_status_to_deletion">'.$this->msg["link_status_to_deletion"].'</label><br />';
		$result .=	'</div>';

		//Linked Status to deletion
		$notice_statut_select = '<select '.($this->link_status_to_deletion ? '' : 'disabled').' id="linked_status_to_deletion" name="linked_status_to_deletion">';
		$sql = "SELECT id_notice_statut, gestion_libelle FROM notice_statut";
		$res = mysql_query($sql, $dbh);
		while($row=mysql_fetch_assoc($res))
			$notice_statut_select .= '<option '.($this->linked_status_to_deletion == $row["id_notice_statut"] ? "selected" : '').' value="'.$row["id_notice_statut"].'">'.htmlentities($row["gestion_libelle"] ,ENT_QUOTES, $charset).'</option>';
		$notice_statut_select .= '</select>';
		$result .=	'<blockquote><div class="row"><label class="etiquette" for="linked_status_to_deletion">'.$this->msg["linked_status_to_deletion"].'</label><br />';
		$result .= $notice_statut_select;
		$result .=	'</div></blockquote>';
		
		//Include items
		$result .=	'<div class="row"><input id="include_items" '.($this->include_items ? 'checked=checked' : '').' name="include_items" type="checkbox" />'.'<label class="etiquette" for="include_items">'.$this->msg["include_items"].'</label><br />';
		$result .=	'</div>';
		
		//Include links
		global $include_path, $class_path, $msg, $form_param,$include_links;
		require_once($class_path.'/export_param.class.php');
		$include_links = $this->include_links;
		
		$e_param = new export_param(EXP_OAI_CONTEXT);	
		$result.= $e_param->check_default_param();
		
		return $result;
	}
	
	function update_config_from_form() {
		global $dbh;
		parent::update_config_from_form();
		global $repo_name, $admin_email, $included_sets, $repositoryIdentifier, $chunksize, $token_lifeduration, $cache_complete_records, $cache_complete_records_seconds, $link_status_to_deletion, $linked_status_to_deletion, $allow_gzip_compression, $baseURL, $include_items,$suppr_feuille_xslt;
				
		//les trucs faciles
		$this->config["repo_name"] = stripslashes($repo_name);
		$this->config["admin_email"] = stripslashes($admin_email);
		$this->config["repositoryIdentifier"] = stripslashes($repositoryIdentifier);
		$this->config["chunksize"] = $chunksize+0;
		$this->config["token_lifeduration"] = $token_lifeduration+0;
		$this->config["cache_complete_records"] = isset($cache_complete_records);
		$this->config["cache_complete_records_seconds"] = $cache_complete_records_seconds+0;
		$this->config["link_status_to_deletion"] = isset($link_status_to_deletion);
		$this->config["linked_status_to_deletion"] = $linked_status_to_deletion+0;
		$this->config["allow_gzip_compression"] = isset($allow_gzip_compression);
		$this->config["baseURL"] = stripslashes($baseURL);
		$this->config["include_items"] = isset($include_items);
		
		if(!$_FILES['feuille_xslt']['error']){
			$this->config['feuille_xslt'] = file_get_contents($_FILES['feuille_xslt']['tmp_name']);
			$this->config['feuille_xslt_name'] = $_FILES['feuille_xslt']['name'];
		}
		if($suppr_feuille_xslt){
			$this->config['feuille_xslt'] = "";
			$this->config['feuille_xslt_name'] = "";			
		}
		$this->config['include_links']=array();
		$this->config['include_links']['genere_lien'] = 0;
		
		global $include_path, $class_path;
		require_once($class_path.'/export_param.class.php');		
		$e_param = new export_param(EXP_GLOBAL_CONTEXT);	

		$this->config['include_links'] = $e_param->get_parametres(EXP_OAI_CONTEXT);
		
		//Vérifions que le statut proposé existe bien
		$sql = "SELECT COUNT(1) > 0 FROM notice_statut WHERE id_notice_statut = ".($linked_status_to_deletion+0);
		$status_exists = mysql_result(mysql_query($sql, $dbh), 0, 0);
		if (!$status_exists)
			$this->config["linked_status_to_deletion"] = 0;
		
		if (!$this->config["cache_complete_records_seconds"])
			$this->config["cache_complete_records_seconds"] = 86400;

		//et maintenant les sets
		if (!is_array($included_sets))
			$included_sets=array($included_sets);
		array_walk($included_sets, create_function('&$a', '$a+=0;'));	//Virons ce qui n'est pas entier
		//Virons ce qui n'est pas un index de set de notice
		$sql = "SELECT connector_out_set_id FROM connectors_out_sets WHERE connector_out_set_type IN (".implode(",",$this->allowed_set_types).") AND connector_out_set_id IN (".implode(",", $included_sets).')';
		$res = mysql_query($sql, $dbh);
		$this->config["included_sets"] = array();
		while($row=mysql_fetch_assoc($res)) {
			$this->config["included_sets"][] = $row["connector_out_set_id"];
		}

		//Vérifions que les formats autorisés proposés existent bien
		$allowed_paths=array();
		$admin_convert_catalog = external_services_converter_notices::get_export_possibilities();
		foreach ($admin_convert_catalog as $aconvert) {
			$allowed_paths[] = $aconvert["path"];
		}
		global $allowed_admin_convert_paths;
		if (!is_array($allowed_admin_convert_paths))
			$allowed_admin_convert_paths = array($allowed_admin_convert_paths);
		
		$this->config["allowed_admin_convert_paths"] = array();
		foreach ($allowed_admin_convert_paths as $apath) {
			if (!in_array($apath, $allowed_paths))
				continue;
			$this->config["allowed_admin_convert_paths"][] = $apath;
		}
		
		return;
	}
}

?>