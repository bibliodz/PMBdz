<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: transaction_list.class.php,v 1.1 2013-12-24 13:08:33 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/transaction/transaction.tpl.php");

class transactype_list {	
	var $transactype_list=array(); // liste des caisses
	
	public function __construct(){
		$this->fetch_data();		
	}
	
	protected function fetch_data(){
		// les data...	
		$this->transactype_list=array();	
		$rqt = "select * from transactype order by transactype_name";
		$res = mysql_query($rqt);
		$i=0;
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				$this->transactype_list[$i]['id'] = $row->transactype_id;
				$this->transactype_list[$i]['name'] = $row->transactype_name;
				$this->transactype_list[$i]['unit_price'] = $row->transactype_unit_price;
				$this->transactype_list[$i]['quick_allowed'] = $row->transactype_quick_allowed;			
				$i++;
			}
		}
	}

	public function get_form(){
		global $msg;
		global $transactype_list_form, $charset;
		foreach ($this->transactype_list as $index =>$transactype){
			if ($parity++ % 2)	$pair_impair = "even"; else $pair_impair = "odd";			
			$form.= "
				<tr class='$pair_impair' onmouseout=\"this.className='$pair_impair'\" onmouseover=\"this.className='surbrillance'\" style='cursor: pointer'>
					<td onmousedown=\"document.location='./admin.php?categ=finance&sub=transactype&action=edit&id=".$transactype['id']."'\" >".htmlentities($transactype['name'],ENT_QUOTES, $charset)."</td>
				</tr>
			";
		}		
		$transactype_list_form = str_replace('!!transactype_list!!', $form, $transactype_list_form);
		return $transactype_list_form;
	}
	
	public function proceed(){
		global $action;
		
		switch($action) {
			case 'add':
				break;				
    		default:
				break;
		}
	}
	
	public function get_data(){
		return $this->transactype_list;	
	}
	
	public function get_count(){
		return count($this->transactype_list);		
	}
}