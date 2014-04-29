<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_print_fields.class.php,v 1.5 2012-03-20 11:09:58 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once("$class_path/parametres_perso.class.php");

class serialcirc_print_fields {
	var $id=0;
	var $circ_tpl=array();
	
	function serialcirc_print_fields($id_serialcirc=0) {
		$this->id=$id_serialcirc+0;
		$this->fetch_data();
	}
	
	function fetch_data() {
		$this->p_perso = new parametres_perso("empr");
		$this->circ_tpl=array();
		$requete="select * from serialcirc where id_serialcirc=".$this->id ;
		$resultat=mysql_query($requete);
		if (mysql_num_rows($resultat)) {
			$r=mysql_fetch_object($resultat);
			if($r->serialcirc_tpl)
			$this->circ_tpl=unserialize($r->serialcirc_tpl);
		}	
	}
	
	function get_header_list(){
		$header_list=array();
		foreach($this->circ_tpl as $cpt => $line){
			if($line['type']=="libre"){
				$header_list[]=$line['label'];
			}else {
				$header_list[]=$this->get_field_label($line['type'],$line['id']);
			}
		}
		return($header_list);
	}
	
	function get_line($data){
		$elt=array();
		if($data['empr_id']){
			$req="select * from empr where id_empr=".$data['empr_id'];
			$res_empr=mysql_query($req);
			$empr=mysql_fetch_object($res_empr);
			$pp=$this->p_perso->show_fields($data['empr_id']);
		}
		
		foreach($this->circ_tpl as $cpt => $line){
			switch($line['type']){
				case 'pp':
					$found=0;
					foreach($pp['FIELDS'] as $pp_data){
						if($pp_data['ID']==$line['id']){
							$elt[]=$pp_data['AFF'];
							$found=1;
						}
					}
					if(!$found)$elt[]=" ";
				break;	
				case "name":$elt[]= $empr->empr_nom;
				break;
				case "emprlibelle":$elt[]= $empr->empr_nom." ".$empr->empr_prenom;
				break;
				case "cb":$elt[]=$empr->empr_cb;
				break;
				case "mail":$elt[]=$empr->empr_mail;
				break;
				case "adr1":$elt[]=$empr->empr_adr1;
				break;
				case "adr2":$elt[]=$empr->empr_adr2;
				break;
				case "tel1":$elt[]=$empr->empr_tel1;
				break;
				case "tel2":$elt[]=$empr->empr_tel2;
				break;
				case "ville":$elt[]=$empr->empr_ville;
				break;			
				case "libre":$elt[]=" ";
				break;	
				default :$elt[]=" ";
				break;					
			}		
		}	
		return $elt;
	}	
	
	function get_select_form($name="select_field",$selected=0,$onchange="serialcirc_print_add_button();") {
		global $charset,$msg,$base_path;
		
		$sel="
		<div class='row'>
		 <select name='$name' id='$name' onchange='$onchange'>
		 	<option value='name'>".htmlentities($msg['serialcirc_print_add_fields'],ENT_QUOTES,$charset)."</option>
		 	<optgroup class='erreur' label='".htmlentities($msg["serialcirc_print_group_empr_fields"],ENT_QUOTES,$charset)."' ></optgroup>		
			<option value='emprlibelle'>".htmlentities($this->get_field_label('emprlibelle'),ENT_QUOTES,$charset)."</option>		
			<option value='name'>".htmlentities($this->get_field_label('name'),ENT_QUOTES,$charset)."</option>
			<option value='cb'>".htmlentities($this->get_field_label('cb'),ENT_QUOTES,$charset)."</option>
			<option value='adr1'>".htmlentities($this->get_field_label('adr1'),ENT_QUOTES,$charset)."</option>
			<option value='adr2'>".htmlentities($this->get_field_label('adr2'),ENT_QUOTES,$charset)."</option>
			<option value='tel1'>".htmlentities($this->get_field_label('tel1'),ENT_QUOTES,$charset)."</option>
			<option value='tel2'>".htmlentities($this->get_field_label('tel2'),ENT_QUOTES,$charset)."</option>	
			<option value='ville'>".htmlentities($this->get_field_label('ville'),ENT_QUOTES,$charset)."</option>			
			!!empr_param_perso!!	
			<optgroup class='erreur' label='".htmlentities($msg["serialcirc_print_group_other"],ENT_QUOTES,$charset)."' ></optgroup>	
			<option value='libre'>".htmlentities($this->get_field_label('libre'),ENT_QUOTES,$charset)."</option>		
		</select>
		</div>	
		<div class='row'>	
			!!fiche_fields!!
		</div>
		<div class='row'>
		</div>
		";
		$perso_fields="";
		if(count($this->p_perso->t_fields ))$perso_fields="<optgroup class='erreur' label='".htmlentities($msg["serialcirc_print_group_empr_p_perso"],ENT_QUOTES,$charset)."' ></optgroup>";
		foreach($this->p_perso->t_fields as $id =>$p){
			$perso_fields.="<option value='pp_$id'>".htmlentities($this->get_field_label('pp',$id),ENT_QUOTES,$charset)."</option>";
		}
		$sel=str_replace('!!empr_param_perso!!', $perso_fields, $sel);	
		
		$line_tpl="		
			<div id='drag_!!index!!'  handler=\"handleprint_!!index!!\" dragtype='circdiffprint' draggable='yes' recepttype='circdiffprint' id_circdiff='!!index!!'		
				recept='yes' dragicon=\"".$base_path."/images/icone_drag_notice.png\" dragtext='!!titre_drag!!' downlight=\"circdiff_downlight\" highlight=\"circdiff_highlight\"			
				order='!!index!!' style='' id_serialcirc='".$this->id."'>
				<span id=\"handleprint_!!index!!\" style=\"float:left; padding-right : 7px\"><img src=\"".$base_path."/images/sort.png\" style='width:12px; vertical-align:middle' /></span>
				
				<input type='button' class='bouton' name='delete_line'  value='".htmlentities($msg["serialcirc_print_delete_line"],ENT_QUOTES,$charset)."'
				onclick=\"serialcirc_print_del_button('!!index!!'); \" >
				!!titre_field!!								
			</div>		
		";

		$tpl_list="";	
		$index=0;
		foreach($this->circ_tpl as $cpt => $line){
			$tpl=$line_tpl;
			$titre_field=$this->get_field_label($line['type'],$line['id']);
			$tpl=str_replace('!!titre_drag!!',$titre_field , $tpl);
			if(!$line['id'])$line['id']=0;
			$name=$line['type']."_".$cpt."_".$line['id'];
			if($line['type']=="libre") $titre_field.="<input type='text' name='".$name."_label'  value='".$line['label']."' >";			
			$titre_field.="<input type='hidden' name='field_list[]'  value='$name' >";
				
			$tpl=str_replace('!!titre_field!!',$titre_field , $tpl);
			$tpl=str_replace('!!index!!',$index , $tpl);
			$tpl_list.=$tpl;
				
			$index++;				
		}		
		$sel=str_replace('!!fiche_fields!!', $tpl_list, $sel);	
		return $sel;
	}
	
	function get_field_label($field,$id=0){
		global $msg;
		switch($field){
			case "name":return $msg["serialcirc_print_empr_name"];
			break;
			case "emprlibelle":return $msg["serialcirc_print_empr_libelle"];
			break;
			case "cb":return $msg["serialcirc_print_empr_cb"];
			break;
			case "adr1":return $msg["serialcirc_print_empr_adr1"];
			break;
			case "adr2":return $msg["serialcirc_print_empr_adr2"];
			break;
			case "tel1":return $msg["serialcirc_print_empr_tel1"];
			break;
			case "tel2":return $msg["serialcirc_print_empr_tel2"];
			break;
			case "ville":return $msg["serialcirc_print_empr_ville"];
			break;			
			case "libre":return $msg["serialcirc_print_libre_fields"];
			break;							
			case "pp":
				if(count($this->p_perso->t_fields )){
					if($this->p_perso->t_fields[$id]){
						return $this->p_perso->t_fields[$id]["TITRE"];
					}			
				}	
			break;
		}
	}
	
	function save_form(){
		global $field_list;
		$this->circ_tpl=array();
		$cpt=0;
		if(!$field_list)$field_list=array();
		foreach($field_list as $field){
			$data=explode('_',$field);
			$this->circ_tpl[$cpt]['type']=$data[0];
			$this->circ_tpl[$cpt]['id']=$data[2];		
			$val_label=$field."_label";
   			global $$val_label;
   			$this->circ_tpl[$cpt]['label']=  $$val_label;				
			$cpt++;
		}			
		$req="update serialcirc set serialcirc_tpl='".serialize($this->circ_tpl)."' where id_serialcirc=".$this->id ;
		mysql_query($req);
		$this->fetch_data();
	}	
	
	function up_order($tablo){	
		global $dbh;	
		$liste = explode(",",$tablo);
		$new_circ_tpl=array();
		for($i=0;$i<count($liste);$i++){			
			$new_circ_tpl[]=$this->circ_tpl[$liste[$i]];
		}	
		$req="update serialcirc set serialcirc_tpl='".serialize($new_circ_tpl)."' where id_serialcirc=".$this->id ;
		mysql_query($req);
		$this->fetch_data();
	}
	
	function add_field(){
		global $select_field;
		$cpt=count($this->circ_tpl);
		$data=explode('_',$select_field);
		$this->circ_tpl[$cpt]['type']=$data[0];
		$this->circ_tpl[$cpt]['id']=$data[1];		
		$req="update serialcirc set serialcirc_tpl='".serialize($this->circ_tpl)."' where id_serialcirc=".$this->id ;
		mysql_query($req);
		$this->fetch_data();
	}	
	
	function del_field(){
		global $index;
		array_splice($this->circ_tpl,$index,1);
		$req="update serialcirc set serialcirc_tpl='".serialize($this->circ_tpl)."' where id_serialcirc=".$this->id ;
		mysql_query($req);
		$this->fetch_data();	
	}
	
} //serialcirc class end