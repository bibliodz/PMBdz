<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: editions_state_filter_list.class.php,v 1.5 2013-03-12 17:12:21 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/editions_state_filter.class.php");

class editions_state_filter_list extends editions_state_filter {

	public function __construct($elem,$params=array()){
		parent::__construct($elem,$params);
		//on s'assure d'avoir un nombre !
		if(!$this->value) $this->value = array();
	}
	
	public function get_from_form(){
		$filter_value = $this->elem['id']."_filter";
		$filter_op = $this->elem['id']."_filter_op";
		global $$filter_value;
		global $$filter_op;
		$this->value = $$filter_value;
		$this->op =$$filter_op;	
	}
	
	public function get_form($draggable=false){
		global $msg,$charset;
		$form= "
			<div class='row'>&nbsp;</div>
			<div class='row' >";
		if($draggable){
			$form.= "<div class='colonne3' id='filters_".$this->elem['id']."_drag' draggable='yes' dragtype='editionsstatefilterslist'>";
		}else{
			$form.= "<div class='colonne3' id='filters_".$this->elem['id']."' >";
		}
		$form.= "
					<label>".htmlentities($this->elem['label'],ENT_QUOTES,$charset)."</label>
				</div>
				<div class='colonne3'>
					<select name='".$this->elem['id']."_filter_op'>
						<option value='in'".($this->op == "in" ? " selected='selected'" : "").">=</option>
						<option value='not in'".($this->op == "not in" ? " selected='selected'" : "").">".$msg['editions_state_filter_different']."</option>
					</select>
				</div>
				<div class='colonne3'>
					<select name='".$this->elem['id']."_filter[]' multiple='yes'>";
		foreach($this->elem['value'] as $value => $label){
			$form.= "
						<option value='".htmlentities($value,ENT_QUOTES,$charset)."'".(in_array(addslashes($value),$this->value) ? " selected='selected'" :"").">".htmlentities($label,ENT_QUOTES,$charset)."</option>";
		}
		$form.= "			
					</select>
				</div>
			</div>";
		return $form;		
	}
	
	public function get_sql_filter(){
		$sql_filter = "";
		if($this->op && count($this->value)){
			if($this->elem['field_join']){
				$champ=$this->elem['field_join'];
			}elseif($this->elem['field_alias']){
				$champ=$this->elem['field_alias'];
			}else{
				$champ=$this->elem['field'];
			}
			if($this->elem['type'] == "text_like"){
				if($this->op == "in"){
					$liste_filter="(".$champ." LIKE '%".implode("%' AND ".$champ." LIKE '%",$this->value)."%')";
				}else/*if($this->op == "not in")*/{
					$liste_filter="(".$champ." NOT LIKE '%".implode("%' AND ".$champ." NOT LIKE '%",$this->value)."%')";
				}
				return $liste_filter;
			}elseif($this->elem['type'] == "text"){
				$liste_filter="('".implode("','",$this->value)."')";
			}elseif($this->elem['type'] == "integer"){
				$liste_filter="(".implode(",",$this->value).")";
			}else{
				$liste_filter="(".implode(",",$this->value).")";
			}
			$sql_filter = $champ." ".$this->op." ".$liste_filter;
			if($this->elem['authorized_null']){
				$sql_filter="((".$sql_filter.") OR (".$champ." IS NULL))";
			}
		}
		return $sql_filter;
	} 
}