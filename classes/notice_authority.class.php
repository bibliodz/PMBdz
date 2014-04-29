<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_authority.class.php,v 1.7 2013-11-28 09:30:09 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/i_2709.class.php");
require_once($class_path."/author.class.php");
require_once($class_path."/titre_uniforme.class.php");
require_once($class_path."/category.class.php");
require_once($class_path."/notice_authority_generic.class.php");
require_once($class_path."/origins.class.php");


/*
 * Classe pour les autorit� classiques...
 */
class notice_authority extends iso2709_authorities implements notice_authority_generic{
	var $type;
	var $common_data;
	var $specifics_data;
	var $rejected_forms;
	var $associated_forms;
	var $parallel_forms;
	var $use_rejected;
	var $use_associated;
	var $use_parallel;
	
	public function __construct($data="",$type="UNI",$file_charset="iso-8859-1"){
		if($file_charset == "utf-8"){
			$this->is_utf8 = true;
		}
		parent::iso2709($data,$type);
		if($this->error){
			$this->try_autocorrect();
		}
		$this->get_type();
	}
	
	public function get_type(){
		switch($this->guide_infos['et']){
			// nom de personne
			case "a" :
				$this->type = "author"; 
				break;
			// nom de collectivit�
			case "b" :
				$this->type = "author";
				break;
			// famille
			case "e" :	
				$this->type = "author";
				break;
			// marque
			case "d" :
				$this->type = "author";
				break;
			// titre uniforme
			case "f" :
				$this->type = "uniform_title";
				break;
			// nom de territoire ou nom g�ographique	
			case "c" :	
			// mati�re nom commun
			case "j" :
				$this->type = "category";
				break;
			/*certaines autorit�s ne sont pas traitables par PMB*/
			// rubrique de classement
			case "g" :
			// forme, genre ou caract�ristiques physiques	
			case "l" :	
			// lieu d'�dition
			case "k" :
			// auteur / titre
			case "h" :
			// auteur / rubrique de classement
			case "i" :	
			default :
				$this->type = "";
				break;
		}			
	}
	
	/*
	 * Pour avoir le num�ro d'autorit� 
	 */
	public function format_authority_number($authority_number){
		global $pmb_import_modele_authorities;
		//appel � la m�thode statique de la classe d'import...
		if($pmb_import_modele_authorities!= ""){
			return call_user_func(array($pmb_import_modele_authorities,"format_authority_number"),$authority_number);
		}else{
			return $authority_number;
		}
	}	
	
	
	public function get_informations($use_rejected = true, $use_associated = true, $use_parallel = false){
		$this->use_rejected = $use_rejected;
		$this->use_associated = $use_associated;
		$this->use_parallel = $use_parallel;
		if(!$this->error){
			$this->get_common_informations();
			$this->get_specifics_informations();
			$this->get_rejected_forms();
			$this->get_associated_forms();
			$this->get_parallel_forms();
		}
	}
	
	public function get_common_informations(){
		$this->common_data = array();
		$this->common_data['authority_number'] = $this->format_authority_number($this->fields['001'][0]['value']);
		$this->common_data['lang'] = $this->fields[101][0]['a'][0];
		$this->common_data['source']=array(
			'country' => $this->fields[801][0]['a'][0],
			'origin' => $this->fields[801][0]['b'][0],
			'date' => $this->fields[801][0]['c'][0]
		);
	}
	
	public function get_specifics_informations(){
		switch($this->guide_infos['et']){
			// nom de personne
			case "a" :
				$this->specifics_data = auteur::get_informations_from_unimarc($this->fields,"2",70);
				break;
			// nom de collectivit�
			case "b" :
				$this->specifics_data = auteur::get_informations_from_unimarc($this->fields,"2",71);				
				break;
			// famille
			case "e" :	
				$this->specifics_data = auteur::get_informations_from_unimarc($this->fields,"2",71,"220");
				break;
			// marque
			case "d" :
				$this->specifics_data = auteur::get_informations_from_unimarc($this->fields,"2",71,"216");
				break;
			// titre uniforme
			case "f" :
				$this->specifics_data = titre_uniforme::get_informations_from_unimarc($this->fields,"2");
				break;
			case "j" :	
			// mati�re nom commun
				$this->specifics_data = category::get_informations_from_unimarc($this->fields,false,"250");
				break;
			// nom de territoire ou nom g�ographique	
			case "c" :
				$this->specifics_data = category::get_informations_from_unimarc($this->fields,false,"215");
				break;
			// forme, genre ou caract�ristiques physiques	
			case "l" :
				$this->specifics_data = category::get_informations_from_unimarc($this->fields,false,"280");
				break;		
			// rubrique de classement
			case "g" :
			/*certaines autorit�s ne sont pas traitables par PMB*/
			// lieu d'�dition
			case "k" :
			// auteur / titre
			case "h" :
			// auteur / rubrique de classement
			case "i" :		
			default :
				break;
		}		
	}
	
	public function get_rejected_forms(){
		if($this->use_rejected)
			$this->rejected_forms = $this->get_linked_authorities("4");
	}
	
	public function get_associated_forms(){
		if($this->use_associated)
			$this->associated_forms = $this->get_linked_authorities("5");		
	}
		
	public function get_parallel_forms(){
		if($this->use_parallel)
			$this->parallel_forms = $this->get_linked_authorities("7");
	}

	public function get_linked_authorities($zone){
		$data = array();
		foreach($this->fields as $key => $field){
			switch($key){
				// Forme associ�e - Nom de Personne
				case $zone."00" :
					for($i=0 ; $i<count($field) ; $i++){
						$infos = array();
						$infos = auteur::get_informations_from_unimarc($field[$i],$zone,70);
						$infos['link_code'] = $field[$i]['5'][0];
						$infos['comment'] = $field[$i]['0'][0];
						$data[] = $infos;
					}
					break;
				// Forme associ�e - Nom de Collectivit�
				case $zone."10" :
				// Forme associ�e - Marque
				case $zone."16" :	
				// Forme associ�e - Famille
				case $zone."20" :
					for($i=0 ; $i<count($field) ; $i++){
						$infos = array();
						$infos = auteur::get_informations_from_unimarc($field[$i],$zone,71);
						$infos['link_code'] = $field[$i]['5'][0];
						$infos['comment'] = $field[$i]['0'][0];
						$data[] = $infos;
					}
					break;
				// Forme associ�e - Titre Uniforme
				case $zone."30" :
					for($i=0 ; $i<count($field) ; $i++){
						$infos = titre_uniforme::get_informations_from_unimarc($field[$i],$zone);
						$infos['link_code'] = $field[$i]['5'][0];
						$infos['comment'] = $field[$i]['0'][0];
						$data[] = $infos;
					}
					break;
				// Forme rejet�e - Rubrique de Classement
				case $zone."35" :
					
					break;
				// Forme associ�e - Nom de territoire ou nom g�ographique 
				case $zone."15" :
				// Forme associ�e - Auteur / Titre 
				case $zone."40" :
				// Forme associ�e - Auteur / Rubrique de Classement
				case $zone."45" :
				// Forme associ�e - Forme, genre ou caract�ristiques physiques
				case $zone."80" :	
				// Forme associ�e - Mati�re nom commun
				case $zone."50" :
					for($i=0 ; $i<count($field) ; $i++){
						$infos = category::get_informations_from_unimarc($field[$i],true);
						$infos['link_code'] = $field[$i]['5'][0];
						if($this->type!= "category" || ($this->type== "category" && $infos['link_code']!="z")){
							$infos['comment'] = $field[$i]['0'][0];
						}
						$data[] = $infos;
					}
					break;
				// Forme associ�e - Lieu d'�dition	
				case $zone."60" :
				default :
					continue;
					break;
			}
		}
		return $data;				
	}
	
	
	public function check_if_exists($data,$id_thesaurus = 0){
		switch($data['type_authority']){
			// Forme associ�e - Nom de Personne
			case "author" :
				$id = auteur::check_if_exists($data);
				break;
			// Forme associ�e - Titre Uniforme	
			case "uniform_title" :
				$id = titre_uniforme::import_tu_exist($data);
				break;
			case "category" :
				$id = category::check_if_exists($data,$id_thesaurus,0,$this->common_data['lang']);
				break;
			default :
				$id=0;
				break;
		}
		return $id; 
	}
	
	protected function try_autocorrect(){
		$this->error = false;
		foreach($this->directory_table as $field){
			if(substr($field['LABEL'],0,1) == "2"){
				switch($field['LABEL']){
					case "200" :
						$this->guide_infos["et"] = "a";
						break;
					case "210" :
						$this->guide_infos["et"] = "b";
						break;
					case "215" :
						$this->guide_infos["et"] = "c";
						break;
					case "216" :
						$this->guide_infos["et"] = "d";
						break;
					case "220" :
						$this->guide_infos["et"] = "e";
						break;
					case "230" :
						$this->guide_infos["et"] = "f";
						break;
					case "235" :
						$this->guide_infos["et"] = "g";
						break;
					case "240" :
						$this->guide_infos["et"] = "h";
						break;
					case "245" :
						$this->guide_infos["et"] = "i";
						break;
					case "250" :
						$this->guide_infos["et"] = "j";
						break;
					case "260" :
						$this->guide_infos["et"] = "k";
						break;
					case "280" :
						$this->guide_infos["et"] = "l";
						break;
				}
				break;
			}else{
				continue;
			}
		}
		if ($this->check_guide_infos()) {
			$this->read_fields();
		}
	}
}