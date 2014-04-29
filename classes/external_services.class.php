<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: external_services.class.php,v 1.11 2013-02-20 16:09:28 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/*
==========================================================================================
Comment �a marche toutes ces classes?

        .----------------------------------.
        |             es_base              |
        |----------------------------------| h�rite de
        | classe de base, contient le      |<-------------------------.
        | m�canisme des erreurs            |                          |
        '----------------------------------'                          |
h�ritent de ^             ^ h�rite de                  .----------------------------.
            |             |                            |        es_parameter        |
            |             |                     [0..n] |----------------------------|
            |   .------------------.    ^------------->| repr�sente une variable d' |
            |   |    es_method     |    |              | entr�e d'une m�thode       |
            |   |------------------|    |              '----------------------------'
            |   | repr�sente une   |----.                             ^ h�rite de
            |   | m�thode de l'API |    |                             |
            |   '------------------'    |                             |
            |             ^             |              .----------------------------.
            |             |             |              |         es_result          |
            |             |             |       [0..n] |----------------------------|
            |     .---------------.     v------------->| repr�sente une variable de |
            |     |   es_group    |                    | retour d'une m�thode       |
            |     |---------------|                    '----------------------------'
            '-----| contient des  |
            ^     | m�thodes      |
            |     '---------------'
            |             ^
            |             |
            |             |
            |     .---------------.               .-------------------------------.
            |     |  es_catalog   |               |       external_services       |
            |     |---------------|[1]            |-------------------------------|
            '-----| contient des  |<--------------| g�re les diff�rentes m�thodes |
            ^     | groupes       |               | et g�n�re le proxy associ�    |
            |     '---------------'               '-------------------------------'
            |                                                     |
            |-----------------------------------------------------'

==========================================================================================
* */

require_once("$include_path/parser.inc.php");
require_once("$class_path/external_services_rights.class.php");
require_once($class_path."/external_services_caches.class.php");
require_once("$class_path/acces.class.php");
require_once("$include_path/connecteurs_out_common.inc.php");

define("ES_GROUP_CANNOT_READ_MANIFEST_FILE",1);
define("ES_METHOD_NO_GROUP_DEFINED",2);
define("ES_PARAMETER_UNKNOWN_PARAMETER_TYPE",3);
define("ES_CATALOG_CANNOT_READ_CATALOG_FILE",4);

//Classe de base avec gestion des erreurs
class es_base {
	var $error=false;
	var $error_message="";
	var $description="";
	
	function set_error($error_code,$error_message) {
		$this->error=$error_code;
		$this->error_message=$error_message;
	}
	
	function copy_error($object) {
		$this->error=$object->error;
		$this->error_message=$object->error_message;
	}
	
	function clear_error() {
		$this->error=false;
		$this->error_message="";
	}
}

//Param�tre d'une fonction
class es_parameter extends es_base {
	var $name="";
	var $type="scalar";
	var $datatype="string";
	var $nodename="PARAM";
	var $optional=false;
	
	//Pour les param�tres structure : un tableau de type es_parametre;
	var $struct=array();
	
	//Constructeur
	function es_parameter($param="") {
		if (is_array($param)) {
			$this->name=$param["NAME"];
			$this->datatype=$param["DATATYPE"];
			$this->optional=$param["OPTIONAL"];
			//Selon le type (param ou result), �a change
			$classname = get_class($this);
			switch($param["TYPE"]) {
				case "scalar":
					break;
				case "array":
					for ($i=0; $i<count($param[$this->nodename]); $i++) {
						$parametre=$param[$this->nodename][$i];
						$p=new $classname($parametre);
						if (!$p->error) 
							$this->struct[]=$p; 
						else {
							$this->copy_error($p);
							return;
						} 
					}
					break;
				case "structure":
					for ($i=0; $i<count($param[$this->nodename]); $i++) {
						$parametre=$param[$this->nodename][$i];
						$p=new $classname($parametre);
						if (!$p->error) 
							$this->struct[]=$p; 
						else {
							$this->copy_error($p);
							return;
						} 
					}
					break;
				default:
					$this->set_error(ES_PARAMETER_UNKNOWN_PARAMETER_TYPE,"Type de param�tre inconnu");
					return;
			}
			$this->type=$param["TYPE"];
		}
	}
}

//R�sultat d'une fonction
class es_result extends es_parameter {
	var $nodename="RESULT";
}

//Composant d'un type
class es_part extends es_parameter {
	var $nodename="PART";
}

//Un type
class es_type extends es_base {
	var $name='';
	var $description='';
	var $imported=false;
	var $imported_from = "";
	var $struct=array();
	var $type="structure";
	
	function es_type($type) {
		if (isset($type["IMPORTED"]) && $type["IMPORTED"]) {
			$this->name = $type["NAME"];
			$this->imported = true;
			$this->imported_from = $type["IMPORTED_FROM"];
			return;
		}
		if (isset($type["DESCRIPTION"][0]["value"]))
			$this->description=$type["DESCRIPTION"][0]["value"];
		$this->name = $type["NAME"];
		if (isset($type["PART"])) {
			foreach ($type["PART"] as $part) {
				$part_object = new es_part($part);
				$this->struct[] = $part_object;
			}
		}
	}
}

//R�f�rence d'une methode dans une autre m�thode
class es_requirement extends es_base {
	var $group="";
	var $name="";
	var $version="";

	//Constructeur
	function es_requirement($param="") {
		if (is_array($param)) {
			$this->group=$param["GROUP"];
			$this->name=$param["NAME"];
			$this->version=$param["VERSION"];
		}
	}
	
    public function __toString()
    {
        return $this->group.'_'.$this->name;
    }
}

//Requirement d'une methode
class es_pmb_requirement extends es_base {
	var $start_path="";
	var $file="";

	//Constructeur
	function es_pmb_requirement($param="") {
		if (is_array($param)) {
			$this->start_path=$param["START_PATH"];
			$this->file=$param["FILE"];
		}
	}
	
	//Permet de pouvoir comparer deux instances, dans un array_unique par exemple
    public function __toString()
    {
        return $this->start_path.'___'.$this->file;
    }

}

//M�thode
class es_method extends es_base {
	var $group;
	var $name;
	//Tableau de es_params
	var $inputs=array();
	//Tableau de es_results
	var $outputs=array();
	//Descriptions
	var $description;
	var $input_description="";
	var $output_description="";
	//Droits pour cette m�thode
	var $rights=0;
	//Num�ro de version de cette m�thode
	var $version=0;
	//m�thodes n�cessaires pour executer cette m�thode
	var $requirements=array();
	var $recurvised_requirement_list=array();
	//require_once n�cessaires pour executer cette m�thode
	var $pmb_file_requirements=array();
	//D�fini si la m�thode a besoin des messages localis�s
	var $language_independant=false;
	
	function es_method($method="",$group="") {
		if (is_array($method)) {
			if (!$group) {
				$this->set_error(ES_METHOD_NO_GROUP_DEFINED,"No group defined");
				return;
			}
			//Analyse du tableau
			$this->group=$group;
			$this->name=$method["NAME"];
			$this->description=$method["COMMENT"];
			$this->version=$method["VERSION"];
			if ($method["RIGHTS"]) {
				$rights=explode("|",$method["RIGHTS"]);
				for ($i=0; $i<count($rights); $i++) {
					$this->rights|=constant($rights[$i]);
				}
			}
			if (isset($method["LANGUAGE_INDEPENDANT"]) && $method["LANGUAGE_INDEPENDANT"] == 'true')
				$this->language_independant = true;
			//Lecture des inputs
			$this->input_description=$method["INPUTS"][0]["DESCRIPTION"][0]["value"];
			for ($i=0; $i<count($method["INPUTS"][0]["PARAM"]); $i++) {
				$parameter=$method["INPUTS"][0]["PARAM"][$i];
				$p=new es_parameter($parameter);
				if (!$p->error) 
					$this->inputs[]=$p;
				else {
					$this->copy_error($p);
					return;
				}
			}
			//Lecture des outputs
			$this->output_description=$method["OUTPUTS"][0]["DESCRIPTION"][0]["value"];
			for ($i=0; $i<count($method["OUTPUTS"][0]["RESULT"]); $i++) {
				$result=$method["OUTPUTS"][0]["RESULT"][$i];
				$r=new es_result($result);
				if (!$r->error)
					$this->outputs[]=$r;
				else {
					$this->copy_error($r);
					return;
				}
			}
			
			//Lecture des requirements
			if (isset($method["REQUIREMENTS"][0]["REQUIREMENT"])) {
				for ($i=0; $i<count($method["REQUIREMENTS"][0]["REQUIREMENT"]); $i++) {
					$result=$method["REQUIREMENTS"][0]["REQUIREMENT"][$i];
					$r=new es_requirement($result);
					if (!$r->error) {
						$this->requirements[]=$r;
						$this->recurvised_requirement_list[] = $r->__toString();
					}
					else {
						$this->copy_error($r);
						return;
					}
				}
			}
			
			//Lecture des pmb_requirements
			if (isset($method["PMB_REQUIREMENTS"][0]["PMB_REQUIREMENT"])) {
				for ($i=0; $i<count($method["PMB_REQUIREMENTS"][0]["PMB_REQUIREMENT"]); $i++) {
					$result=$method["PMB_REQUIREMENTS"][0]["PMB_REQUIREMENT"][$i];
					$r=new es_pmb_requirement($result);
					if (!$r->error)
						$this->pmb_file_requirements[]=$r;
					else {
						$this->copy_error($r);
						return;
					}
				}
			}
		}
	}
}

//Classe repr�sentant un groupe de fonctions
class es_group extends es_base {
	var $name;
	//Tableau de es_type
	var $types=array();
	//Tableau de es_methods
	var $methods=array();
	//Identifiant unique du groupe
	var $id="";
	//Description
	var $description="";
	//Tableau des messages du groupe
	var $msg=array();
	
	function es_group($group_name,$id) {
		global $base_path,$lang;
		//Lecture des propri�t�s du fichier manifest
		$xml=@file_get_contents($base_path."/external_services/$group_name/manifest.xml");
		if (!$xml) {
			$this->set_error(ES_GROUP_CANNOT_READ_MANIFEST_FILE,"Can't read manifest file");
			return;
		}
		
		$this->name=$group_name;
		$this->id=$id;
		
		//Parse du fichier
		$methods=_parser_text_no_function_($xml,"MANIFEST");
		
		$this->description=$methods["DESCRIPTION"][0]["value"];
		
		//Pour chaque type, on instancie sa repr�sentation
		if (isset($methods["TYPES"][0]["TYPE"])) {
			foreach ($methods["TYPES"][0]["TYPE"] as $atype) {
				$t = new es_type($atype);
				if (!$t->error)
					$this->types[$t->name] = $t;
				else {
					$this->copy_error($t);
					return;
				}
			}
		}
		
		//Pour chaque m�thode, on instancie sa repr�sentation
		for ($i=0; $i<count($methods["METHODS"][0]["METHOD"]); $i++) {
			$method=$methods["METHODS"][0]["METHOD"][$i];
			$m=new es_method($method,$this->name);
			if (!$m->error) 
				$this->methods[$m->name]=$m;
			else {
				$this->copy_error($m);
				return;
			}
		}
		
		//Lecture du fichier des messages
		if (!file_exists($base_path."/external_services/$group_name/messages/$lang.xml")) $tlang="fr_FR"; else $tlang=$lang;
		if (file_exists($base_path."/external_services/$group_name/messages/$tlang.xml")) {
			$msg_list=new XMLlist($base_path."/external_services/$group_name/messages/$tlang.xml");
			$msg_list->analyser();
			$this->msg=$msg_list->table;
		}
	}
}

//Classe de lecture du catalogue
class es_catalog extends es_base {
	
	var $groups; //Tableau de groupes
	
	var $recursive_depth;
	
	function es_catalog() {
		global $base_path;
		if (file_exists($base_path."/external_services/catalog_subst.xml")) 
			$catalog_file=$base_path."/external_services/catalog_subst.xml";
		else
			$catalog_file=$base_path."/external_services/catalog.xml";
			
		$xml=@file_get_contents($catalog_file);
		
		if (!$xml) {
			$this->set_error(ES_CATALOG_CANNOT_READ_CATALOG_FILE,"Fichier catalog introuvable");
			return;
		}
		
		$catalog=_parser_text_no_function_($xml,"CATALOG");
		
		//D�pouillement du r�sultat
		for ($i=0; $i<count($catalog["ITEM"]);$i++) {
			$g=new es_group($catalog["ITEM"][$i]["NAME"],$catalog["ITEM"][$i]["ID"]);
			if (!$g->error)
				$this->groups[$g->name]=$g;
			else {
				$this->copy_error($g);
			}
		}
		
		//Construit la liste des d�pendances des fichiers php dont les m�thodes ont besoin (exemple: $class_path/acces.class.php)
		$this->recursive_depth = 0;
		$this->create_requirements_lists();
		
		//Construit la liste des d�pendandes des autres m�thodes dont les m�thodes ont besoin.
		$this->recursive_depth = 0;
		$this->fix_imported_pmb_requirements();
	}
	
	function fix_imported_pmb_requirement(&$amethod) {
		if ($this->recursive_depth > 5) //Faut pas pousser m�m� dans les orties: �vitons une recursion infinie.
			return;
		$this->recursive_depth++;
		if ($amethod->requirements) {
			foreach ($amethod->requirements as &$arequirement) {
				if (isset($this->groups[$arequirement->group]->methods[$arequirement->name])) {
					$this->fix_imported_pmb_requirement($this->groups[$arequirement->group]->methods[$arequirement->name]);
				}
				if (!isset($this->groups[$arequirement->group]->methods[$arequirement->name]->pmb_file_requirements) || !$this->groups[$arequirement->group]->methods[$arequirement->name]->pmb_file_requirements)
					continue;
				$amethod->pmb_file_requirements = array_merge($amethod->pmb_file_requirements, $this->groups[$arequirement->group]->methods[$arequirement->name]->pmb_file_requirements);
				$amethod->pmb_file_requirements = array_unique($amethod->pmb_file_requirements);
			}
		}
		$this->recursive_depth--;
	}
	
	function fix_imported_pmb_requirements() {
		foreach ($this->groups as &$agroup) {
			foreach ($agroup->methods as &$amethod) {
				$this->fix_imported_pmb_requirement($amethod);
			}
		}
	}
	
	function create_requirements_list(&$amethod) {
		if ($this->recursive_depth > 5) //Faut pas pousser m�m� dans les orties: �vitons une recursion infinie.
			return;
		$this->recursive_depth++;
		if ($amethod->requirements) {
			foreach ($amethod->requirements as &$arequirement) {
				if (isset($this->groups[$arequirement->group]->methods[$arequirement->name])) {
					$this->create_requirements_list($this->groups[$arequirement->group]->methods[$arequirement->name]);
				}
				if (!isset($this->groups[$arequirement->group]->methods[$arequirement->name]->recurvised_requirement_list) || !$this->groups[$arequirement->group]->methods[$arequirement->name]->recurvised_requirement_list)
					continue;
				$amethod->recurvised_requirement_list = array_merge($amethod->recurvised_requirement_list, $this->groups[$arequirement->group]->methods[$arequirement->name]->recurvised_requirement_list);
				$amethod->recurvised_requirement_list = array_unique($amethod->recurvised_requirement_list);
			}
		}
		$this->recursive_depth--;
	}
	
	function create_requirements_lists() {
		foreach ($this->groups as &$agroup) {
			foreach ($agroup->methods as &$amethod) {
				$this->create_requirements_list($amethod);
			}
		}
	}

}

class external_services_api_class {
	protected  $proxy_parent=NULL;
	protected $msg=array();
	protected $es = NULL;
	
	function external_services_api_class($external_services, $group_name, &$proxy_parent) {
		$this->proxy_parent = &$proxy_parent;
		$this->es=$external_services;
		$this->msg=$this->es->msg($group_name);
	}
	
	//Filtrage du tableau des Id de notices pour la visibilit� des notices
	//si for=exemplaire -> on filtre les notices pour la visibilt� des exemplaires
	//si for=docnum -> on filtre les notices pour la visibilt� des documents num�riques
	function filter_tabl_notices($tabl_notices,$for="notices"){
		if($for == "exemplaire"){
			$mask=8;
			$filter=" ((expl_visible_opac=1 and expl_visible_opac_abon=0)".($this->proxy_parent->idEmpr?" or (expl_visible_opac_abon=1 and expl_visible_opac=1)":"").")";
			$filter.=" AND ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($this->proxy_parent->idEmpr?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";//Je dois aussi avoir le droit de voir les notices
		}elseif($for == "docnum"){
			$mask=16;
			$filter=" ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($this->proxy_parent->idEmpr?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
			$filter.=" AND ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($this->proxy_parent->idEmpr?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";//Je dois aussi avoir le droit de voir les notices
		}else{
			$mask=4;
			$filter=" ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($this->proxy_parent->idEmpr?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
		}
		if($this->proxy_parent->isOPAC){
			//V�rifions que l'emprunteur a bien le droit de voir les notices
			global $gestion_acces_active, $gestion_acces_empr_notice;
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
				$ac= new acces();
				$dom_2= $ac->setDomain(2);
				foreach ($tabl_notices as $anoticeid) {
					$rights= $dom_2->getRights($this->proxy_parent->idEmpr, $anoticeid);
					if (!($rights & $mask)) {
						$tabl_notices = array_diff($tabl_notices, array($anoticeid));
					}
				}
			}else{
				//On contr�le les statuts de notices si pas de gestion de droit
				$where = "notice_id in (".implode(",",$tabl_notices).") AND";
				$query = "select distinct notice_id from notices join notice_statut on notices.statut= id_notice_statut where ".$where." ".$filter;
				$tabl_notices=array();
				$res=mysql_query($query);
				if(mysql_num_rows($res)){
					while ($ligne=mysql_fetch_object($res)) {
						$tabl_notices[]=$ligne->notice_id;
					}
				}
			}
		}else{//Si je ne viens pas de OPACAnonymous ou OPACEmpr
		}
		return $tabl_notices;
	}
	
	//Filtrage du tableau des Id de bulletins pour la visibilit�
	//Pour les droits sur les bulletins c'est la notice de p�riodique qui les d�finis
	//si for=exemplaire -> on filtre les bulletins pour la visibilt� des exemplaires
	//si for=docnum -> on filtre les bulletins pour la visibilt� des documents num�riques
	function filter_tabl_bulletins($tabl_bulletins,$for="notices"){
		
		$requete="SELECT DISTINCT bulletin_notice FROM bulletins WHERE bulletin_id IN (".implode(",",$tabl_bulletins).")";
		$res=mysql_query($requete);
		if(mysql_num_rows($res)){
			$notice_ids=array();
			while ($ligne=mysql_fetch_object($res)) {
				$notice_ids[]=$ligne->bulletin_notice;
			}
			$notice_ids=$this->filter_tabl_notices($notice_ids,$for);
			if(count($notice_ids)){
				$requete="SELECT DISTINCT bulletin_id FROM bulletins WHERE bulletin_id IN (".implode(",",$tabl_bulletins).") AND bulletin_notice IN (".implode(",",$notice_ids).")";
				$res=mysql_query($requete);
				$tabl_bulletins=array();
				if(mysql_num_rows($res)){
					while ($ligne=mysql_fetch_object($res)) {
						$tabl_bulletins[]=$ligne->bulletin_id;
					}
				}else{
					//Je ne peux normalement pas passer par ici
					return array();
				}
			}else{
				//Je n'ai plus de notices de p�riodique apr�s filtrage donc je ne dois pas voir de bulletin
				return array();
			}
		}else{
			//Comme un bulletin � forc�ment un p�riodique je ne dois pas passer par l�
			return array();
		}
		return $tabl_bulletins;
	}
	
	//Permet de savoir si un exemplaire est visible ou non
	function expl_visible_opac($expl_id){
		$visible=true;
		if($this->proxy_parent->isOPAC){
			global $opac_sur_location_activate;
			$opac_sur_location_join="";
			if($opac_sur_location_activate){
				$opac_sur_location_join=" JOIN sur_location ON docs_location.surloc_num=sur_location.surloc_id AND surloc_visible_opac=1 ";
			}
			$requete="SELECT expl_id FROM exemplaires JOIN docs_location ON expl_location=idlocation JOIN docs_section ON expl_section=idsection JOIN docs_statut ON expl_statut=idstatut $opac_sur_location_join WHERE ";
			$requete.=" expl_id='".$expl_id."' ";
			$requete.=" AND location_visible_opac=1 AND section_visible_opac=1 AND statut_visible_opac=1 ";
			$res=mysql_query($requete);
			if(!mysql_num_rows($res)){
				$visible=false;
			}
		}
		return $visible;
	}
	
}

//Classe qui impl�mente les fonctions externes
class external_services extends es_base {
	var $msg=array();
	var $catalog;
	var $proxy;	//Classe regroupant toutes les fonctions
	
	//Constructeur
	function external_services($allow_caching=false) {
		if ($allow_caching) {
			$es_cache = new external_services_cache('es_cache_blob', 86400);
			
			//V�rifions que le catalogue xml n'a pas chang� avant de chercher dans le cache
			$situation = $this->compute_situation_catalog_identity();
			$old_situation = $es_cache->decache_single_object('external_service_catalog_situation', CACHE_TYPE_MISC);
			if ($old_situation == $situation) {
				$cached_result = $es_cache->decache_single_object('external_service_catalog', CACHE_TYPE_MISC);
				if ($cached_result !== false) {
					$cached_result = unserialize(base64_decode($cached_result));
					$this->catalog = $cached_result;
				}
			}
		}

		if (!$this->catalog) {
			//Parse des biblioth�ques disponibles
			$this->catalog=new es_catalog();
			if ($this->catalog->error) {
				$this->copy_error($this->catalog);
				return;
			}
			
			if ($allow_caching) {
				//Mettons le catalogue dans le cache
				$es_cache = new external_services_cache('es_cache_blob', 86400);
				$es_cache->encache_single_object('external_service_catalog', CACHE_TYPE_MISC, base64_encode(serialize($this->catalog)));
				$es_cache->encache_single_object('external_service_catalog_situation', CACHE_TYPE_MISC, $situation);
			}
		}

	}
	
	function compute_situation_catalog_identity() {
		global $base_path;
		if (file_exists($base_path."/external_services/catalog_subst.xml")) 
			$catalog_file=$base_path."/external_services/catalog_subst.xml";
		else
			$catalog_file=$base_path."/external_services/catalog.xml";
			
		$xml=@file_get_contents($catalog_file);
		
		if (!$xml) {
			return "";
		}
		
		$catalog=_parser_text_no_function_($xml,"CATALOG");
		
		//D�pouillement du r�sultat
		$identity = "";
		for ($i=0; $i<count($catalog["ITEM"]);$i++) {
			$identity .= $catalog["ITEM"][$i]["NAME"].filemtime($base_path."/external_services/".$catalog["ITEM"][$i]["NAME"]."/manifest.xml")."_";
		}
		$identity = md5($identity);
		return $identity;
	}
	
	function get_proxy($user, $restrict_use_to_function_list=array()) {
		if ($this->proxy) return $this->proxy;
		$proxy_desc=array();
		$rights=new external_services_rights($this);
		$proxy="
class es_proxy extends es_base {
	var \$es;
	var \$user".($user?"=$user":"").";
	var \$isOPAC=false;
	var \$idEmpr=0;
	var \$description=\"\";
	var \$error_callback_function=NULL;
	var \$input_charset='utf-8';
";
		
		$proxy_method_requires = "";
		
		$proxy_err_calback_set = "
		
	function set_error_callback(\$callback_function) {
		\$this->error_callback_function = \$callback_function;
	}
		";
		
		$proxy_init="

	function init() {";
		
		//Si on nous soumet une liste de fonctions, il ne faut pas oublier les �ventuelles d�pendances de celles-ci.
		if ($restrict_use_to_function_list) {
			 $restrict_use_to_function_list_requirements = array();
			 foreach ($this->catalog->groups as $group_name=>$es_group) {
				foreach ($es_group->methods as $method_name=>$es_method) {
					if ($restrict_use_to_function_list && !in_array($group_name.'_'.$method_name, $restrict_use_to_function_list))
						continue;
					if (!$es_method->recurvised_requirement_list)
						continue;
					$restrict_use_to_function_list_requirements = array_merge($restrict_use_to_function_list_requirements, $es_method->recurvised_requirement_list);
				}
			 }
			if (!$restrict_use_to_function_list_requirements)
				$restrict_use_to_function_list_requirements = array();
			$restrict_use_to_function_list = array_merge($restrict_use_to_function_list, $restrict_use_to_function_list_requirements);
			$restrict_use_to_function_list = array_unique($restrict_use_to_function_list);
		}
		
		$pmb_file_requirements = array();
		
		//Cr�ation dess variables des classes correspondantes aux groupes
		foreach ($this->catalog->groups as $group_name=>$es_group) {
			//Cr�ation des fonctions
			$group_has_method=false;
			$methods_desc=array();
			foreach ($es_group->methods as $method_name=>$es_method) {
				if ($restrict_use_to_function_list && !in_array($group_name.'_'.$method_name, $restrict_use_to_function_list))
					continue;

				if (!$es_method->pmb_file_requirements)
					$es_method->pmb_file_requirements = array();
				$pmb_file_requirements = array_merge($pmb_file_requirements, $es_method->pmb_file_requirements);
					
				//Les droits sont-ils l� ?
				if ($rights->has_rights($user,$group_name,$method_name)) {
					
					//Construction des param�tres de la m�thode
					$params=array();
					for ($i=0; $i<count($es_method->inputs); $i++) {
						$params[].="\$".$es_method->inputs[$i]->name;
					}
					$group_has_method=true;
					$proxy_func.="
	function ".$group_name."_".$method_name."(".implode(",",$params).") {
		try {
		\$result =  \$this->".$group_name."->".$method_name."(".implode(",",$params).");
		} catch(Exception \$e) {
			if (\$this->error_callback_function)
				call_user_func(\$this->error_callback_function, \$e);
		}
		return \$result;
	}
";
					$mdesc=array();
					$mdesc["name"]=$method_name;
					$mdesc["description"]=$this->get_text($es_method->description,$group_name);
					$mdesc["inputs_description"]=$this->get_text($es_method->input_description,$group_name);
					$mdesc["outputs_description"]=$this->get_text($es_method->output_description,$group_name);
					$methods_desc[]=$mdesc;
				}
			}
			if ($group_has_method) {
				//Fonction d'initialisation
				$proxy_init.="
		\$this->".$group_name."=new ".$group_name."(\$this->es, '".$group_name."', \$this);";
				
				//Variable pour la classe du groupe
				$proxy.="
	var \$".$group_name.";";
				
				//Require pour le groupe
				$proxy_require.="require_once(\$base_path.\"/external_services/".$group_name."/".$group_name.".class.php\");
";			
				//Description du groupe
				$gdesc=array();
				$gdesc["name"]=$group_name;
				$gdesc["description"]=$this->get_text($es_group->description,$group_name);
				$gdesc["methods"]=$methods_desc;
				$proxy_desc[]=$gdesc;
			}
		}
		
		$pmb_file_requirements = array_unique($pmb_file_requirements);
		$name_variable_correspondance = array(
			"class" => '$class_path',
			"base" => '$base_path',
			"include" => '$include_path'
		);
		foreach ($pmb_file_requirements as $arequirement) {
			if (!$name_variable_correspondance[$arequirement->start_path])
				continue;
			$proxy_method_requires .= 'require_once("'.$name_variable_correspondance[$arequirement->start_path].'/'.$arequirement->file.'");'."\n";
		}
		
		$proxy_init.="
	}
";
		$proxy_end="
	function es_proxy(\$external_services) {
		\$this->es=\$external_services;
		\$this->init();
	}
}
";
		//Instanciation de la classe proxy !
		$proxy=$proxy_method_requires.$proxy_require.$proxy.$proxy_init.$proxy_err_calback_set.$proxy_func.$proxy_end;
		
		//Restauration de l'environnement global
		foreach ($GLOBALS as $var_name=>$value) {
			global $$var_name;
		}
		
		//Enregistrons le nom des variables qui existent d�j� avant l'eval
		$before_eval_vars = get_defined_vars();
		
		try {
			//error_reporting(E_ALL);
			$re =eval("try { $proxy } catch (Exception \$e) { }");
		} catch (Exception $e) {
			//print $e->getMessage();	
		}
		$this->proxy=new es_proxy($this);
		//Affectation des descriptions
		$this->proxy->description=$proxy_desc;
		//Affectation du charset
		global $charset;
		$this->proxy->input_charset = $charset;
		
		//Maintenant nous avons sortir toutes les variables globales g�n�r�e par l'eval du contexte de la fonction
		$function_variable_names = array("function_variable_names" => 0, "before_eval_vars" => 0, "created" => 0);
		$created = array_diff_key(get_defined_vars(), $GLOBALS, $function_variable_names, $before_eval_vars);
		foreach ($created as $created_name => $on_sen_fiche)
			global $$created_name;
		extract($created);

		return $this->proxy;
	}
	
	function get_group_list() {
		$r=array();
		foreach ($this->catalog->groups as $group_name=>$group) {
			$t=array();
			$t["name"]=$group_name;
			$t["description"]=$this->get_text($group->description,$group_name);
			foreach ($group->methods as $method_name=>$method) {
				$m=array();
				$m["name"]=$method_name;
				$m["description"]=$this->get_text($method->description,$group_name);
				$m["inputs_description"]=$this->get_text($method->input_description,$group_name);
				$m["outputs_description"]=$this->get_text($method->output_description,$group_name);
				$t["methods"][]=$m;
			}
			$r[]=$t;
		}
		return $r;
	}
	
	function group_exists($group) {
		if (is_object($this->catalog->groups[$group])) return true; else return false;
	}
	
	function method_exists($group,$method) {
		if ($this->group_exists($group)) {
			if (is_object($this->catalog->groups[$group]->methods[$method])) return true;
		}
		return false;
	}
	
	function save_persistent($group_name,$uniqueid,$message) {
		//Sauvegarde de mani�re persistente 
		$requete="insert into external_persist (group_name,uniqueid,message) values('".addslashes($group_name)."','".addslashes($uniqueid)."','".addslashes($message)."')";
		$r=mysql_query($requete);
		if ($r) return true; else return false;
	}
	
	function msg($group_name) {
		return $this->catalog->groups[$group_name]->msg;
	}
	
	function get_text($text,$group_name) {
		if (substr($text,0,4)=="msg:") {
			$lmsg=$this->msg($group_name);
			return $lmsg[substr($text,4)];
		} else return $text;
	}
	
	function operation_need_messages($operation) {
		foreach ($this->catalog->groups as &$agroup) {
			foreach ($agroup->methods as &$amethod) {
				if ($operation == $agroup->name."_".$amethod->name) {
					return !$amethod->language_independant;
				}
			}
		}
	}
}
?>