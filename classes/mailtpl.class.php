<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mailtpl.class.php,v 1.3 2013-04-26 12:37:31 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/mailtpl.tpl.php");
require_once($class_path."/files_gestion.class.php");

class mailtpl {
	var $id=0;
	var $info=array();
	var $users=array();
	
	function mailtpl($id=0) {
		$this->id=$id+0;
		$this->fetch_data();
	}
	
	function fetch_data() {
		global $include_path;
		global $PMBuserid;
		
		$this->info=array();
		$this->users=array();
		$requete_users = "SELECT userid, username FROM users order by username ";
		$res_users = mysql_query($requete_users);
		$this->all_users=array();
		while (list($this->all_userid,$all_username)=mysql_fetch_row($res_users)) {
			$this->all_users[]=array($this->all_userid,$all_username);
		}	
		if(!$this->id){
			$this->users[]=$PMBuserid;
			return;
		} 
		$req="select * from mailtpl where id_mailtpl=". $this->id;
		
		$resultat=mysql_query($req);	
		if (mysql_num_rows($resultat)) {
			$r=mysql_fetch_object($resultat);		
			$this->info['id']= $r->id_mailtpl;	
			$this->info['name']= $r->mailtpl_name;	
			$this->info['objet']= $r->mailtpl_objet;	
			$this->info['tpl']= $r->mailtpl_tpl;	
			$this->info['users']= $r->mailtpl_users;	
		}			
		$this->users= explode(" ",$this->info['users']);
		 				
	// printr($this->info[28]);
	}

    function get_mailtpl(){
		global $charset;
    	$ajax_send=$this->info;
    	if($charset != 'utf-8'){ // cause: json_encode veut de l'utf8
    		$ajax_send['id'] =utf8_encode($this->info['id']);
    		$ajax_send['name']=utf8_encode($this->info['name']);
    		$ajax_send['objet'] =utf8_encode($this->info['objet']);
    		$ajax_send['tpl'] =utf8_encode($this->info['tpl']);
    		$ajax_send['users'] =utf8_encode($this->info['users']);
    	}    	
    	return($ajax_send);
    }
	
    static function get_selvars(){
    	global $msg,$mailtpl_form_selvars;    	
		return $mailtpl_form_selvars;   
    }   
 	
    static function get_sel_img(){
    	global $msg,$mailtpl_form_sel_img, $pmb_img_folder,$pmb_img_url;
    	if(!$pmb_img_folder) return '';
    	$tpl=$mailtpl_form_sel_img;
		$img=new files_gestion($pmb_img_folder,$pmb_img_url);	
		if(!$img->get_count_file()) return '';
		
    	$select=$img->get_sel('select_file',"!!path!!!!name!!","!!name!!");
		$tpl=str_replace('!!select_file!!',$select,$tpl);  	
		return $tpl;   
    }   
       
	function get_form() {
		global $mailtpl_form_tpl,$msg,$charset;		
		
		$tpl=$mailtpl_form_tpl;
		if($this->id){
			$tpl=str_replace('!!msg_title!!',$msg['admin_mailtpl_form_edit'],$tpl);
			$tpl=str_replace('!!delete!!',"<input type='button' class='bouton' value='".$msg['admin_mailtpl_delete']."'  onclick=\"document.getElementById('action').value='delete';this.form.submit();\"  />", $tpl);
			$name=$this->info['name'];
			$tpl_objet=$this->info['objet'];
			$tpl_contens=$this->info['tpl'];
		}else{ 
			$tpl=str_replace('!!msg_title!!',$msg['admin_mailtpl_form_add'],$tpl);
			$tpl_objet="";
			$tpl=str_replace('!!delete!!',"",$tpl);
			$name="";
			$tpl_contens="";
		}
		$id_check_list="";
		foreach($this->all_users as $a_user) {
			$id_check="auto_".$a_user[0];			
			if($a_user[0]==1){
				$checked=" checked readonly ";
			}else{ 
				if(in_array( $a_user[0],$this->users)){
					$checked=" checked ";
				}else $checked="";
				if($id_check_list)$id_check_list.='|';
				$id_check_list.=$id_check;
			}
			$autorisations_users.="<span class='usercheckbox'><input type='checkbox' $checked name='userautorisation[]' id='$id_check' value='".$a_user[0]."' class='checkbox'><label for='$id_check' class='normlabel'>&nbsp;".$a_user[1]."</label></span>&nbsp;&nbsp;";
		}		
		$tpl=str_replace('!!name!!',htmlentities($name, ENT_QUOTES, $charset),$tpl);
		$tpl=str_replace('!!selvars!!',mailtpl::get_selvars(),$tpl);	
		$sel_img_tpl="";
		$sel_img=mailtpl::get_sel_img();
		if($sel_img)$sel_img_tpl="
		<div class='row'>
			<label class='etiquette'>".$msg["admin_mailtpl_form_sel_img"]."</label>
			<div class='row'>
				$sel_img
			</div>
		</div>";
		$tpl=str_replace('!!sel_img!!',$sel_img_tpl,$tpl);					
		$tpl=str_replace('!!autorisations_users!!',$autorisations_users,$tpl);		
		$tpl=str_replace('!!id_check_list!!',$id_check_list,$tpl);			
		$tpl=str_replace('!!tpl!!',htmlentities($tpl_contens, ENT_QUOTES, $charset),$tpl);		
		$tpl=str_replace('!!objet!!',htmlentities($tpl_objet,ENT_QUOTES,$charset),$tpl);
		$tpl=str_replace('!!id_mailtpl!!',$this->id,$tpl);
		 
		return $tpl;
	}

	function save($data) {
		global $dbh;
		
		$fields="
			mailtpl_name='".$data['name']."',
			mailtpl_objet='".$data['objet']."',
			mailtpl_tpl='".$data['tpl']."',
			mailtpl_users=' ".implode(" ",$data['users'])." ' 
		";
		
		if(!$this->id){ // Ajout
			$req="INSERT INTO mailtpl SET $fields ";	
			mysql_query($req, $dbh);
			$this->id = mysql_insert_id($dbh);
		} else {
			$req="UPDATE mailtpl SET $fields where id_mailtpl=".$this->id;	
			mysql_query($req, $dbh);				
		}	
		$this->fetch_data();
	}	
	
	function delete() {
		global $dbh;
		
		$req="DELETE from mailtpl WHERE id_mailtpl=".$this->id;
		mysql_query($req, $dbh);	
		
		$this->fetch_data();	
	}	
	
} //mailtpl class end





class mailtpls {	
	var $info=array();
	
	function mailtpls() {
		$this->fetch_data();
	}
	
	function fetch_data() {
		global $PMBuserid;
		$this->info=array();
		$i=0;
		$req="select * from mailtpl where  mailtpl_users like '% $PMBuserid %' ";
		$resultat=mysql_query($req);	
		if (mysql_num_rows($resultat)) {
			while($r=mysql_fetch_object($resultat)){	
				$this->info[$i]= $mailtpl=new mailtpl($r->id_mailtpl);	
				
				$i++;
			}
		}
	}
		
	function get_count_tpl() {
		return count($this->info);
	}
		
	function get_list() {
		global $mailtpl_list_tpl,$mailtpl_list_line_tpl,$msg;
		
		$tpl=$mailtpl_list_tpl;
		$tpl_list="";
		$odd_even="odd";
		foreach($this->info as $elt){
			$tpl_elt=$mailtpl_list_line_tpl;
			if($odd_even=='odd')$odd_even="even";
			else $odd_even="odd";
			$tpl_elt=str_replace('!!odd_even!!',$odd_even, $tpl_elt);	
			$tpl_elt=str_replace('!!name!!',$elt->info['name'], $tpl_elt);	
			$tpl_elt=str_replace('!!id!!',$elt->info['id'], $tpl_elt);	
			$tpl_list.=$tpl_elt;	
		}
		$tpl=str_replace('!!list!!',$tpl_list, $tpl);
		return $tpl;
	}	
	
	function get_sel($sel_name,$sel_id=0) {
		global $msg;
		$tpl="<select name='$sel_name' id='$sel_name'>";				
		foreach($this->info as $elt){
			if($elt->info['id']==$sel_id){
				$tpl.="<option value=".$elt->info['id']." selected='selected'>".$elt->info['name']."</option>";
			} else {
				$tpl.="<option value=".$elt->info['id'].">".$elt->info['name']."</option>";
			}
		}
		$tpl.="</select>";
		return $tpl;
	}	

    	
} // mailtpls class end
	
