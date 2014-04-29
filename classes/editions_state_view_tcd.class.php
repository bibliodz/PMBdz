<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: editions_state_view_tcd.class.php,v 1.3 2013-10-29 09:41:12 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/editions_state_view.class.php");

class editions_state_view_tcd extends editions_state_view {
	public $datas_tcd =array(
		'values' => array(),
		'cols' => array()
	);		//tableau de donn�es
	
	public function __construct($datas,$id,$param=array()){
		//on g�re les propri�t�s communes dans la classe parente
		parent::__construct($datas,$id,$param);
		$this->set_param_tcd($param["tcd"]);
		$this->get_datas();
	}
	
	public function get_datas(){

		if(count($this->datas_tcd) && count($this->datas_tcd["values"])){
			//Le calcule est d�j� fait	
		}elseif(!$this->my_param["tcd"]){
			$this->datas_tcd=array(
				'values' => array(),
				'cols' => array()
			);
		}else{
			$value = explode("_",$this->my_param["tcd"]);
			for($i=1 ; $i<count($this->datas) ; $i++){
				if(!in_array($this->datas[$i][$value[1]],$this->datas_tcd['cols'])){
					$this->datas_tcd['cols'][] = $this->datas[$i][$value[1]];
				}
				$this->datas_tcd['values'][$this->datas[$i][$value[0]]][$this->datas[$i][$value[1]]]++;
			}
		}
		
		return $this->datas_tcd;
	}
	
	public function set_param_tcd($param=array()){
		global $save_param;
		global $tcd;
		
		if($save_param == "tcd"){
			global $tcd;
		}else{
			$tcd=$param;
		}
		$this->my_param["tcd"]=$tcd;
		return;
	}
	
	public function get_form(){
		global $charset,$msg;
		//document.forms.editions_state_tcd.submit()
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<input type='hidden' name='save_param'  id='save_param' value='tcd'/>
					<label for='tcd'>".htmlentities($msg["editions_state_tcd_form"],ENT_QUOTES,$charset)."</label>
				</div>
				<div class='colonne_suite'>
					<select name='tcd' onchange='test_form(\"tcd\");'>
						<option value=''>".htmlentities($msg["editions_state_tcd_choice"],ENT_QUOTES,$charset)."</option>";
		for($i=0 ; $i<count($this->datas[0]) ; $i++){
			for($j=0 ; $j<count($this->datas[0]) ; $j++){
				if($i!=$j){
				$form.="
						<option value='".$i."_".$j."'".($this->my_param["tcd"] == $i."_".$j ? " selected='selected'" : "").">".htmlentities($this->datas[0][$i]." / ".$this->datas[0][$j],ENT_QUOTES,$charset)."</option>";
				}
			}
		}
		$form.= "
					</select>
				</div>
			</div>
			<div class='row'></div>";
		return $form;
	}
	
	
	
	//un simple tableau pour la classe g�n�rique...
	public function show(){
		global $charset,$msg;
		global $javascript_path;

		$html = $this->get_form();
		if(count($this->datas_tcd['values'])){	
			$html.= "
			<script type='text/javascript' src='".$javascript_path."/sorttable.js'></script>
			<div class='row'>
			<table class='sortable'>
				<tr>
					<th></th>";
			foreach($this->datas_tcd['cols'] as $label){
				$html.= "
					<th>".htmlentities($label,ENT_QUOTES,$charset)."</th>";
			}	
			$html.= "
				</tr>";	
			foreach($this->datas_tcd['values'] as $row => $cols){
				$html.= "
				<tr>
					<th>".htmlentities($row,ENT_QUOTES,$charset)."</th>";
				foreach($this->datas_tcd['cols'] as $key){
					$html.= "
					<td>".htmlentities(($cols[$key] ? $cols[$key] : 0),ENT_QUOTES,$charset)."</td>";
				}		
			$html.= "	
				</tr>";
					
			}
			$html.="
			</table>";
		}
		$html.="
		</div>
		<div class='row'>
			<input type='button' class='bouton' value='".htmlentities($msg["editions_state_view_export_excel"],ENT_QUOTES,$charset)."' onclick=\"test_form('tcd','edit');\" />
		</div>";	
		return $html;
	}
	
	public function render_xls_file($name="state"){
		$tmp_file = tempnam(sys_get_temp_dir(),"state_");
		header("Content-Type: application/x-msexcel; name=\"".$name.".xls\"");
		header("Content-Disposition: inline; filename=\"".$name.".xls\"");
		$workbook = new writeexcel_workbook($tmp_file);
		$worksheet = &$workbook->addworksheet();
		foreach($this->datas_tcd['cols'] as $key => $label){
			$worksheet->write(0,$key+1,$label);
		}
		$nb_ligne=1;
		foreach($this->datas_tcd['values'] as $row => $cols){
			$worksheet->write($nb_ligne,0,$row);
			$nb_col=1;
			foreach($this->datas_tcd['cols'] as $key){
				$worksheet->write($nb_ligne,$nb_col,($cols[$key] ? $cols[$key] : 0));
				$nb_col++;
			}
			$nb_ligne++;
		}
		$workbook->close();
		$fh=fopen($tmp_file, "rb");
		fpassthru($fh);
		unlink($tmp_file);		
	}
}