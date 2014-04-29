<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: printer.class.php,v 1.2 2014-02-06 09:49:14 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/printer/printer_data.class.php");

class printer {
	public $printer_data;		// info d'impression
	public $name;	// nom de l'imprimante
	
	public function __construct(){
		global $pmb_printer_name,$class_path;
		$this->name=$pmb_printer_name;
		$this->printer_data= new printer_data();		
		require_once($class_path."/printer/$pmb_printer_name.class.php");
		$this->printer_driver=new $pmb_printer_name();
	}
	
	protected function fetch_data(){

	}
	
	function print_pret($id_empr,$cb_doc,$tpl_perso=""){
		$this->printer_data->get_data_empr($id_empr);
		$this->printer_data->get_data_expl($cb_doc);
			
		return $this->printer_driver->gen_print($this->printer_data->data);
	}

	function print_all_pret($id_empr,$tpl_perso=""){
		global $dbh;
		$this->printer_data->get_data_empr($id_empr);
		$query = "select expl_cb from pret,exemplaires  where pret_idempr=$id_empr and expl_id=pret_idexpl ";		
		$result = mysql_query($query, $dbh);		
		while (($r= mysql_fetch_object($result))) {	
			$this->printer_data->get_data_expl($r->expl_cb);		
		}
		
		$query = "select * from resa where resa.resa_idempr=$id_empr ";
		$result = mysql_query($query, $dbh);
		while($resa = mysql_fetch_object($result)) {
			$this->printer_data->get_data_resa($resa->id_resa);	
		}
		
		return $this->printer_driver->gen_print($this->printer_data->data);
	}
	
	function transacash_ticket($transacash_id,$tpl_perso=""){
		global $dbh;
		
		
		$this->printer_data->get_data_empr($id_empr);
		
		return $this->printer_driver->gen_print($this->printer_data->data);
	}
}