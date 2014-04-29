<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: es_list.class.php,v 1.3 2014-03-14 10:27:01 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path,$base_path, $include_path;
require_once($class_path."/connecteurs.class.php");
require_once($class_path."/curl.class.php");
require_once($class_path."/nusoap/nusoap.php");
require_once($include_path."/notice_affichage.inc.php");

class es_list extends connector {
	//Variables internes pour la progression de la r�cup�ration des notices
	var $del_old;				//Supression ou non des notices dej� existantes
	
	var $profile;				//Profil Amazon
	var $match;					//Tableau des crit�res UNIMARC / AMAZON
	var $current_site;			//Site courant du profile (n�)
	var $searchindexes;			//Liste des indexes de recherche possibles pour le site
	var $current_searchindex;	//Num�ro de l'index de recherche de la classe
	var $match_index;			//Type de recherche (power ou simple)
	var $types;					//Types de documents pour la conversino des notices
	
	//R�sultat de la synchro
	var $error;					//Y-a-t-il eu une erreur	
	var $error_message;			//Si oui, message correspondant
	
    function es_list($connector_path="") {
    	parent::connector($connector_path);
    }
    
    function get_id() {
    	return "es_list";
    }
    
    //Est-ce un entrepot ?
	function is_repository() {
		return 2;
	}
    
    function unserialize_source_params($source_id) {
    	$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			$params["PARAMETERS"]=$vars;
		}
		return $params;
    }
    
    function get_libelle($message) {
    	if (substr($message,0,4)=="msg:") return $this->msg[substr($message,4)]; else return $message;
    }
    
    function source_get_property_form($source_id) {
		global $charset;
		
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		
		if (!isset($es_selected)) $es_selected = array();
		if (!isset($use_in_a2z)) $use_in_a2z = 0;
		if (!isset($libelle)) $libelle = "External";
		if (!isset($infobulle)) $infobulle = "";
		if (!isset($source_as_origine)) $source_as_origine="";

		$form ="
		<div class='row'>
			<div class='colonne3'><label for='libelle'>".$this->msg["es_list_libelle"]."</label></div>
			<div class='colonne-suite'><input type='text' name='libelle' value='".htmlentities($libelle,ENT_QUOTES,$charset)."'/></div>
		</div>
		<div class='row'>
			<div class='colonne3'><label for='infobulle'>".$this->msg["es_list_infobulle"]."</label></div>
			<div class='colonne-suite'><input type='text' name='infobulle' value='".htmlentities($infobulle,ENT_QUOTES,$charset)."'/></div>
		</div>
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='source_as_origine'>".$this->msg["es_list_source_as_origine"]."</label>
			</div>
			<div class='colonne-suite'>
				<input type='radio' name='source_as_origine' value='0'".($source_as_origine==0 ? "checked='checked'" : "")."/>".$this->msg['es_list_source_as_origine_this']."
				<input type='radio' name='source_as_origine' value='1'".($source_as_origine==1 ? "checked='checked'" : "")."/>".$this->msg['es_list_source_as_origine_record']."
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='use_in_a2z'>".$this->msg["es_list_use_in_a2z"]."</label>
			</div>
			<div class='colonne-suite'>
				<input type='radio' name='use_in_a2z' value='0'".($use_in_a2z==0 ? "checked='checked'" : "")."/>".$this->msg['no']."
				<input type='radio' name='use_in_a2z' value='1'".($use_in_a2z==1 ? "checked='checked'" : "")."/>".$this->msg['yes']."
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'><label for='es_selected'>".$this->msg["es_list_list"]."</label></div>
			<div class='colonne-suite'>
				<select name='es_selected[]' multiple='yes' size='6' class='saisie-30em'>";
	
		
		// on regarde les connecteurs existants !
		$query = "select source_id, name from connectors_sources where id_connector != 'es_list' order by name";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$form.="
					<option value='".htmlentities($row->source_id,ENT_QUOTES,$charset)."'".(in_array($row->source_id,$es_selected) ? " selected='selected'" : "").">".htmlentities($row->name,ENT_QUOTES,$charset)."</option>";
			}
		}
		$form.="
				</select>
			</div>
		</div>";

		return $form;
    }
    
    function make_serialized_source_properties($source_id) {
    	global $es_selected;
    	global $use_in_a2z;
    	global $libelle;
    	global $infobulle;
    	global $source_as_origine;
    	$t['es_selected'] = $es_selected;
    	$t['use_in_a2z'] = $use_in_a2z;
    	$t['libelle'] = $libelle;
    	$t['infobulle'] = $infobulle;
    	$t['source_as_origine'] = $source_as_origine;
    	$this->sources[$source_id]["PARAMETERS"]=serialize($t);
	}
	
	//R�cup�ration  des prori�t�s globales par d�faut du connecteur (timeout, retry, repository, parameters)
	function fetch_default_global_values() {
		$this->timeout=5;
		$this->repository=2;
		$this->retry=3;
		$this->ttl=1800;
		$this->parameters="";
	}
	
	 //Formulaire des propri�t�s g�n�rales
	function get_property_form() {
		return "";
	}
    
    function make_serialized_properties() {
    	global $accesskey, $secretkey;
		//Mise en forme des param�tres � partir de variables globales (mettre le r�sultat dans $this->parameters)
		$keys = array();
		$this->parameters = serialize($keys);
	}

	function enrichment_is_allow(){
		return true;
	}
	
	function getEnrichmentHeader(){
		$header= array();
		return $header;
	}
	
	function getTypeOfEnrichment($notice_id,$source_id){	
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		$type['type'] = array(
			array(
				'code' => str_replace(array(" ","%","-","?","!",";",",",":"),"",strip_empty_chars(strtolower($libelle))),
				'label' => $libelle,
				'infobulle' => $infobulle
			) 
		);
		$type['source_id'] = $source_id;
		return $type;
	}
	
	function getEnrichment($notice_id,$source_id,$type="",$enrich_params=array(),$page=1){
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		$enrichment= array();
		
		//on renvoi ce qui est demand�... si on demande rien, on renvoi tout..
		switch ($type){
			case "external" :
			default :
				$rqt="select code from notices where notice_id = '$notice_id'";
				$res=mysql_query($rqt);
				if(mysql_num_rows($res)){
					$code = mysql_result($res,0,0);
					$queries = array();
					for($i=0 ; $i<count($es_selected) ; $i++){
						$queries[] = "select recid,source_id from entrepot_source_".$es_selected[$i]." where (ufield = '011' or ufield ='010')  and usubfield = 'a' and value = '".addslashes($code)."'";
					}
					$query = "select recid,source_id from ((".implode(") union (",$queries).")) as subs";
					$result = mysql_query($query);
					$nb_result = mysql_num_rows($result);
					if($nb_result){
						while($row = mysql_fetch_object($result)){
							$es_source_id = $row->source_id;
							$enrichment['external']['content'].= aff_notice_unimarc($row->recid);
						}
					}else{
						$enrichment['external']['content'] ="<span>".$this->msg["es_list_no_preview"]."</span>";
					}
				}
				break;
		}	
		if($nb_result <= 1){	
			switch($source_as_origine){
				//Cette source
				case 0 :
					$enrichment['source_label']=sprintf($this->msg['es_list_enrichment_source'],$params['NAME']);
					break;
				//source de la notice
				case 1 :
					$query="select name from connectors_sources where source_id=".$es_source_id."";
					$result =mysql_query($query);
					if(mysql_num_rows($result)){
						$name = mysql_result($result,0,0);
					}else{
						$name = $params['NAME'];
					}						
					$enrichment['source_label']=sprintf($this->msg['es_list_enrichment_source'],$name);
					break;
			}
		}else{
			$enrichment['source_label']=sprintf($this->msg['es_list_enrichment_source'],$params['NAME']);
		}
		
		return $enrichment;
	}
}
?>