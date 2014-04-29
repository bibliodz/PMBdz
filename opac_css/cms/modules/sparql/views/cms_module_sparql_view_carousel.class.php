<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_sparql_view_carousel.class.php,v 1.1 2014-01-31 07:41:50 gueluneau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
require_once($include_path."/h2o/h2o.php");

class cms_module_sparql_view_carousel extends cms_module_carousel_view_carousel{
	
	public function render($datas){
		$this->debug($datas);
		$datas['records']=$datas['result'];
		return parent::render($datas);
	}
	
	
}