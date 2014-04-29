<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: opac_filters.class.php,v 1.3 2011-06-10 10:11:40 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class opac_filters {
	
	var $catalog=array();			//Liste des filtres declares
	
	//Constructeur
	function opac_filters($id_vue) {
		$this->id_vue=$id_vue;
    	$this->fetch_data();
	}
	  
    function fetch_data() {
 		global $dbh;
    			
		$this->params=array();
		$req="SELECT * FROM opac_filters where opac_filter_view_num=".$this->id_vue;
		$myQuery = mysql_query($req, $dbh);
		if(mysql_num_rows($myQuery)){		
			while(($r=mysql_fetch_object($myQuery))) {		
				$param=unserialize($r->opac_filter_param);
				$this->params[$r->opac_filter_path]=$param["selected"];
			}
		}
    }
    
    function is_selected($path, $id_ask) {
 		return in_array($id_ask, $this->params[$path]);
    }	
}
?>