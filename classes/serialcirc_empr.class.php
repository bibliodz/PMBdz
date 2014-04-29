<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_empr.class.php,v 1.1 2012-03-13 13:47:27 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/serialcirc_empr.tpl.php");
require_once($class_path."/serialcirc_diff.class.php");

class serialcirc_empr{
	var $empr_id;	// identifiant de l'emprunteur
	var $serialcirc_list;	// tableau des circulation de l'emprunteur
	var $info;	// info des listes de circulation de l'emprunteur

	public function __construct($empr_id){
		$this->empr_id = $empr_id+0;
		$this->fetch_data();
	}

	protected function fetch_data(){
		$this->serialcirc_list = array();
	
		$alone = "select distinct id_serialcirc from serialcirc_diff join serialcirc on num_serialcirc_diff_serialcirc = id_serialcirc where num_serialcirc_diff_empr = ".$this->empr_id;
		$group = "select distinct id_serialcirc from serialcirc_diff join serialcirc on num_serialcirc_diff_serialcirc = id_serialcirc join serialcirc_group on num_serialcirc_group_diff = id_serialcirc_diff where num_serialcirc_group_empr = ".$this->empr_id;
		$already_start = "select distinct num_serialcirc_circ_serialcirc as id_serialcirc from serialcirc_circ where num_serialcirc_circ_empr = ".$this->empr_id;
		$query = $alone." union ".$group." union ".$already_start;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$this->serialcirc_list[] = $row->id_serialcirc;
				$diff = new serialcirc_diff($row->id_serialcirc);
				$this->info[$row->id_serialcirc] = $diff->serial_info;				
			}
		}		
	}

	public function get_list(){
		global $msg,$charset;
		global $empr_serialcirc_tmpl,$empr_serialcirc_tmpl_item;		
		
		$tpl=$empr_serialcirc_tmpl;
		$items="";
		for($i=0; $i<count($this->serialcirc_list) ; $i++){
			$diff_id=$this->serialcirc_list[$i];
			$item=$empr_serialcirc_tmpl_item;
			$css_class = ($i%2 == 0 ? "odd" :"even"); 
			$item = str_replace("!!periodique!!","<a href='".$this->info[$diff_id]['serial_link']."'>".htmlentities($this->info[$diff_id]['serial_name'],ENT_QUOTES,$charset)."</a>",$item);
			$item=str_replace('!!abt!!',   "<a href='".$this->info[$diff_id]['serialcirc_link']."'>".htmlentities($this->info[$diff_id]['abt_name'],ENT_QUOTES,$charset)."</a>" , $item);	
			$item=str_replace('!!bulletinage_see!!',   "<a href='".$this->info[$diff_id]['bulletinage_link']."'>".htmlentities($msg['link_notice_to_bulletinage'],ENT_QUOTES,$charset)."</a>" , $item);	
				
			$items.=$item;
		}
		$tpl = str_replace("!!serialcirc_empr_list!!",$items,$tpl);
		return $tpl;
	}

} // class end
