<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: resa_planning.class.php,v 1.2 2009-03-17 11:46:32 gueluneau Exp $


class resa_planning{
	
	
	var $id_resa = 0;							//Identifiant de r�servation	
	var $resa_idempr = 0;						//Identifiant du lecteur ayant fait la r�servation
	var $resa_idnotice = 0;						//Identifiant de la notice sur laquelle est pos�e la r�servation
	var $resa_date = NULL;						//Date et heure de la demande
	var $resa_date_debut = '0000-00-00';		//Date de d�but de la r�servation
	var $resa_date_fin = '0000-00-00';			//Date de fin de la r�servation
	var $resa_validee = 0;						//R�servation valid�e si 1
	var $resa_confirmee = 0;					//R�servation confirm�e si 1

	 
	//Constructeur.	 
	function resa_planning($id_resa= 0) {
		
		if ($id_resa) {
			$this->id_resa = $id_resa;
			$this->load();	
		}
	}

	
	// charge une r�servation plannifi�e � partir de la base.
	function load(){
	
		global $dbh;
		
		$q = "select * from resa_planning where id_resa = '".$this->id_resa."' ";
		$r = mysql_query($q, $dbh) ;
		$obj = mysql_fetch_object($r);
		$this->resa_idempr = $obj->resa_idempr;
		$this->resa_idnotice = $obj->resa_idnotice;
		$this->resa_date = $obj->resa_date;
		$this->resa_date_debut = $obj->resa_date_debut;
		$this->resa_date_fin = $obj->resa_date_fin;
		$this->resa_validee = $obj->resa_validee;
		$this->resa_confirmee = $obj->resa_confirmee;

	}

	
	// enregistre une r�servation plannifi�e en base.
	function save(){
		
		global $dbh;
		
		if ( !$this->resa_idempr || !$this->resa_idnotice || !$this->resa_date_debut || !$this->resa_date_fin ) die("Erreur de cr�ation resa_planning");
	
		if ($this->id_resa) {
			
			$q = "update resa_planning set resa_date_debut = '".$this->resa_date_debut."', resa_date_fin = '".$this->resa_date_fin."', ";
			$q.= "resa_validee = '".$this->resa_validee."', resa_confirmee = '".$this->resa_confirmee."' ";
			$q.= "where id_resa = '".$this->id_resa."' ";
			$r = mysql_query($q, $dbh);
			
		} else {
			
			$q = "insert into resa_planning set resa_idempr = '".$this->resa_idempr."', resa_idnotice = '".$this->resa_idnotice."', resa_date = SYSDATE(), ";
			$q.= "resa_date_debut = '".$this->resa_date_debut."', resa_date_fin = '".$this->resa_date_fin."', resa_validee = '0', resa_confirmee = '0' ";
			$r = mysql_query($q, $dbh);
			$this->id_resa = mysql_insert_id($dbh);
			
		}

	}


	//supprime une r�servation plannifi�e de la base
	function delete($id_resa= 0) {
		
		global $dbh;

		if(!$id_resa) $id_resa = $this->id_resa; 	

		$q = "delete from resa_planning where id_resa = '".$id_resa."' ";
		$r = mysql_query($q, $dbh);
				
	}


	//Compte le nb de r�servations planifi�e sur une notice
	function countResa($id_notice=0) {
		
		global $dbh;
		
		if (!$id_notice) $id_notice=$this->resa_idnotice;
		$q = "SELECT count(1) FROM resa_planning WHERE resa_idnotice='".$id_notice."' ";
		$r = mysql_query($q, $dbh);
		return mysql_result($r, 0, 0);
	}

	
	//optimization de la table resa_planning
	function optimize() {
		
		global $dbh;
		
		$opt = mysql_query('OPTIMIZE TABLE resa_planning', $dbh);
		return $opt;
				
	}
	
				
}
?>