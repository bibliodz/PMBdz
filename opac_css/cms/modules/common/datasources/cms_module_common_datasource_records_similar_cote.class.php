<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_records_similar_cote.class.php,v 1.3 2013-09-05 07:15:15 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_records_similar_cote extends cms_module_common_datasource_list{

	public function __construct($id=0){
		parent::__construct($id);
		$this->limitable = true;
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_record_permalink",
			"cms_module_common_selector_record",
			"cms_module_common_selector_env_var"
		);
	}

	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		global $dbh;
		$return = array();
		$selector = $this->get_selected_selector();
		if ($selector) {
			$value = $selector->get_value();
			if($value!= 0){
				//on part du premier exemplaire...
				$query ="select expl_cote from exemplaires where expl_notice = ".$value." order by expl_cote  limit 1 ";
				$result = mysql_query($query,$dbh);
				if(mysql_num_rows($result) > 0){
					$row = mysql_fetch_object($result);
					$cote = $row->expl_cote;
					$query = "
					(select distinct expl_notice,expl_cote from exemplaires where expl_notice!=0 and expl_bulletin = 0 and expl_cote >= '".$cote."' and expl_notice = ".$value." order by expl_cote asc limit 5)
						union 
					(select distinct expl_notice,expl_cote from exemplaires where expl_notice!=0 and expl_bulletin = 0 and expl_cote < '".$cote."' and expl_notice = ".$value." order by expl_cote desc limit 5)" ;
					
					$result = mysql_query($query,$dbh);
					if(mysql_num_rows($result) > 0){
						$return["title"] = "";
						while($row = mysql_fetch_object($result)){
							$return["records"][] = $row->expl_notice;
						}
					}
					$return['records'] = $this->filter_datas("notices",$return['records']);
					$return['records'] = array_slice($return['records'], 0, $this->parameters['nb_max_elements']);
				}
			}
			return $return;
		}
		return false;
	}
}