<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc.class.php,v 1.2 2011-11-28 14:18:56 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/serialcirc_diff.class.php") ;
require_once($include_path."/serialcirc.inc.php");

class serialcirc {
	var $id_serialcirc;			// identifiant unique
	var $num_abt;				// identifiant de l'abonnement associ�
	var $type;					// type de circulation
	var $virtual;				// bool�en d�finissant si la circulation est virtuelle ou non
	var $check;					// bool�en d�finissant si le pointage est demand�e!
	var $allow_resa;			// bool�en d�finissant si la demande de r�sa est permise
	var $allow_copy;			// bool�en d�finissant si la demande de copie est permise
	var $duration_before_send;	// nombre de jours avant le d�marrage de la circulation depuis la date de bulletinnage
	var $allow_subscription;	// bool�en d�finissant si l'inscription est permise
	var $serial_title;			// titre du p�riodique
	var $state;					// �tat de la circulation
	var $late_mode;				// mode de retard
	
	public function __construct($id){
		$this->id_serialcirc = $id*1;
		$this->_fetch_data();
	}
	
	protected function _fetch_data(){
		$query = "select * from serialcirc where id_serialcirc = ".$this->id_serialcirc;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$this->num_abt = $row->num_serialcirc_abt;
			$this->type = $row->serialcirc_type;
			$this->virtual = $row->serialcirc_virtual;
			//$row->serialcirc_duration;
			$this->check = $row->serialcirc_checked;
			$this->late_mode = $row->serialcirc_retard_mode;
			$this->allow_resa = $row->serialcirc_allow_resa;
			$this->allow_copy = $row->serialcirc_allow_copy;
			//$row->serialcirc_allow_send_ask;
			$this->allow_subscription = $row->serialcirc_allow_subscription;
			$this->duration_before_send = $row->serialcirc_duration_before_send;
			//$row->serialcirc_expl_statut_circ;
			//$row->serialcirc_expl_statut_circ_after;
			$this->state = $row->serialcirc_state;
		}
	}
	
	public function is_virtual(){
		if($this->virtual) return true;
		else return false;
	}
	
	public function resa_is_allowed(){
		if($this->allow_resa) return true;
		else return false;
	}

	public function copy_is_allowed(){
		if($this->allow_copy) return true;
		else return false;
	}
	public function subscription_is_allowed(){
		if($this->allow_subscription) return true;
		else return false;
	}
	
	public function get_serial_title(){
		if(!$this->serial_title){
			$query="select tit1 from notices join abts_abts on num_notice = notice_id where abt_id = ".$this->num_abt;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$this->serial_title = mysql_result($result,0,0);
			}
		}
		return $this->serial_title;
	}
	
	public function empr_is_subscribe($empr_id){
		$serialcirc_diff = new serialcirc_diff($this->id_serialcirc);
		for($i=0 ; $i<count($serialcirc_diff->list) ; $i++){
			if($serialcirc_diff->list[$i]->type == 0){
				if($serialcirc_diff->list[$i]->num_empr == $empr_id){
					return true;
				}
			}else{
				for($j=0 ; $j<count($serialcirc_diff->list[$i]->group->members) ; $i++){
					if($serialcirc_diff->list[$i]->group->members[$j] == $empr_id){
						return true;
					}	
				}
			}
		}
		return false;
	}
}