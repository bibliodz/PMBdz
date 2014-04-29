<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_records.class.php,v 1.13 2013-09-05 07:15:15 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_records extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->limitable = true;
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_shelve",
			"cms_module_common_selector_type_section",
			"cms_module_common_selector_type_article"
		);
	}

	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		if($this->parameters['selector'] != ""){
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$i]['name'] == $this->parameters['selector']){
					$selector = new $this->parameters['selector']($this->selectors[$i]['id']);
					break;
				}
			}
			$shelves = $selector->get_value();
			if(is_array($shelves) && count($shelves)){
				foreach ($shelves as $shelve_id){
					$query = "select id_tri from etagere where idetagere =".$shelve_id;
					$result = mysql_query($query);
					$records = $notices = array();
					if($result && mysql_num_rows($result)){
						while($row = mysql_fetch_object($result)){
							notices_caddie($shelve_id, $notices, '', '', '',0,$row->id_tri);
						}
					}
					foreach($notices as $id => $niv){
						$records[]=$id;
					}
				}
			}
			$records = $this->filter_datas("notices",$records);
			$records = array_slice($records, 0, $this->parameters['nb_max_elements']);
			$return = array(
					'title'=> 'Liste de Notices',
					'records' => $records
			);
			
			return $return;
		}
		return false;
	}
}