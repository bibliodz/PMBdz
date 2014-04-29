<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collstate.class.php,v 1.15 2014-02-17 14:29:13 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes de gestion des �tats de collection de p�riodique

require_once($class_path."/parametres_perso.class.php");
require_once($include_path."/templates/collstate.tpl.php");
		
class collstate {

// classe de la notice chapeau des p�riodiques
var $id    			= 0;       // id de l'�tat de collection
var $serial_id      = 0;         // id du p�riodique li�
var $surloc_id		= 0;
var $surloc_libelle = '';

// constructeur
function collstate($id=0,$serial_id=0) {
	// si id, allez chercher les infos dans la base
	if($id) {
		$this->id = $id;
		$this->fetch_data();
	}
	if($serial_id) {
		$this->serial_id=$serial_id;
	}	
	return $this->id;
}
    
// r�cup�ration des infos en base
function fetch_data() {
	global $dbh;
	global $opac_sur_location_activate;
	
	$myQuery = mysql_query("SELECT * FROM collections_state WHERE collstate_id='".$this->id."' LIMIT 1", $dbh);
	$mycoll= mysql_fetch_object($myQuery);
	
	$this->serial_id=$mycoll->id_serial;
	$this->location_id=$mycoll->location_id;
	$this->state_collections=$mycoll->state_collections;
	$this->emplacement=$mycoll->collstate_emplacement;
	$this->type=$mycoll->collstate_type;
	$this->origine=$mycoll->collstate_origine;
	$this->note=$mycoll->collstate_note;
	$this->cote=$mycoll->collstate_cote;
	$this->archive=$mycoll->collstate_archive;
	$this->lacune=$mycoll->collstate_lacune;
	$this->statut=$mycoll->collstate_statut;
	
	$myQuery = mysql_query("SELECT * FROM arch_emplacement WHERE archempla_id='".$this->emplacement."' LIMIT 1", $dbh);
	$myempl= mysql_fetch_object($myQuery);
	$this->emplacement_libelle=$myempl->archempla_libelle;	

	$myQuery = mysql_query("SELECT * FROM arch_type WHERE archtype_id='".$this->type."' LIMIT 1", $dbh);
	$mytype= mysql_fetch_object($myQuery);
	$this->type_libelle=$mytype->archtype_libelle;	
	
	// Lecture des statuts
	$myQuery = mysql_query("SELECT * FROM arch_statut WHERE archstatut_id='".$this->statut."' LIMIT 1", $dbh);
	$mystatut= mysql_fetch_object($myQuery);
	$this->statut_gestion_libelle=$mystatut->archstatut_gestion_libelle;	
	$this->statut_opac_libelle=$mystatut->archstatut_opac_libelle;
	$this->statut_visible_opac=$mystatut->archstatut_visible_opac;	
	$this->statut_visible_opac_abon=$mystatut->archstatut_visible_opac_abon; 	
	$this->statut_visible_gestion=$mystatut->archstatut_visible_gestion; 	
	$this->statut_class_html=$mystatut->archstatut_class_html;
	
	$myQuery = mysql_query("select location_libelle, num_infopage, surloc_num from docs_location where idlocation='".$this->location_id."' LIMIT 1", $dbh);
	$mylocation= mysql_fetch_object($myQuery);
	$this->location_libelle=$mylocation->location_libelle;
	$this->num_infopage=$mylocation->num_infopage;
	
	if ($opac_sur_location_activate) {
		$this->surloc_id = $mylocation->surloc_num;
		$myQuery = mysql_query("select surloc_libelle from sur_location where surloc_id='".$this->surloc_id."' LIMIT 1", $dbh);
		$mysurloc = mysql_fetch_object($myQuery);
		$this->surloc_libelle=$mysurloc->surloc_libelle;
	}		
}

//R�cup�rer de l'affichage complet
function get_display_list($base_url,$filtre,$debut=0,$page=0, $type=0) {
	global $dbh, $msg,$nb_per_page_a_search,$tpl_collstate_liste,$tpl_collstate_liste_line, $tpl_collstate_surloc_liste, $tpl_collstate_surloc_liste_line;
	global $opac_sur_location_activate, $opac_view_filter_class;
	global $opac_collstate_order, $opac_url_base;
	
	$location=$filtre->location;	
	if($opac_view_filter_class){		
		$req="SELECT  collstate_id , location_id, num_infopage, surloc_id FROM arch_statut, collections_state 
			LEFT JOIN arch_emplacement ON collstate_emplacement=archempla_id, docs_location
			LEFT JOIN sur_location on docs_location.surloc_num=surloc_id 
			WHERE ".($location?"(location_id='$location') and ":"")."id_serial='".$this->serial_id."' 
			and location_id=idlocation and idlocation in(". implode(",",$opac_view_filter_class->params["nav_collections"]).")
			and archstatut_id=collstate_statut 
			and ((archstatut_visible_opac=1 and archstatut_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (archstatut_visible_opac_abon=1 and archstatut_visible_opac=1)" : "").")";
		if ($opac_collstate_order) $req .= " ORDER BY ".$opac_collstate_order;
		else $req .= " ORDER BY ".($type?"location_libelle, ":"")."archempla_libelle, collstate_cote";
	} else {
		$req="SELECT collstate_id , location_id, num_infopage, surloc_id FROM arch_statut, collections_state 
			LEFT  JOIN docs_location ON location_id = idlocation
			LEFT JOIN sur_location on docs_location.surloc_num=surloc_id 
			LEFT JOIN arch_emplacement ON collstate_emplacement=archempla_id
			WHERE ".($location?"(location_id='$location') and ":"")."id_serial='".$this->serial_id."' 
			and archstatut_id=collstate_statut 
			and ((archstatut_visible_opac=1 and archstatut_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (archstatut_visible_opac_abon=1 and archstatut_visible_opac=1)" : "").")";
		if ($opac_collstate_order) $req .= " ORDER BY ".$opac_collstate_order;	 
		else $req .= " ORDER BY ".($type?"location_libelle, ":"")."archempla_libelle, collstate_cote";	
	}
	$myQuery = mysql_query($req, $dbh);
	
	if((!mysql_error() && $this->nbr = mysql_num_rows($myQuery))) {
		
		if ($opac_sur_location_activate) {
			$tpl_collstate_liste[$type] = str_replace('<!-- surloc -->',$tpl_collstate_surloc_liste,$tpl_collstate_liste[$type]);
			$tpl_collstate_liste_line[$type] = str_replace('<!-- surloc -->',$tpl_collstate_surloc_liste_line,$tpl_collstate_liste_line[$type]);
		}
		
		$parity=1;
		while(($coll = mysql_fetch_object($myQuery))) {
			$my_collstate=new collstate($coll->collstate_id);
			if ($parity++ % 2) $pair_impair = "even"; else $pair_impair = "odd";
	        $tr_javascript="  ";
	        $tr_surbrillance = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";

	        $line = str_replace('!!tr_javascript!!',$tr_javascript , $tpl_collstate_liste_line[$type]);
	        $line = str_replace('!!tr_surbrillance!!',$tr_surbrillance , $line);
	        $line = str_replace('!!pair_impair!!',$pair_impair , $line);
	        if ($opac_sur_location_activate) {
	        	$line = str_replace('!!surloc!!', $my_collstate->surloc_libelle, $line);
	        }
	        if ($my_collstate->num_infopage) {
				if ($my_collstate->surloc_id != "0") $param_surloc="&surloc=".$my_collstate->surloc_id;
				else $param_surloc="";
				$collstate_location = "<a href=\"".$opac_url_base."index.php?lvl=infopages&pagesid=".$my_collstate->num_infopage."&location=".$my_collstate->location_id.$param_surloc."\" alt=\"".$msg['location_more_info']."\" title=\"".$msg['location_more_info']."\">".$my_collstate->location_libelle."</a>";
			} else 
				$collstate_location = $my_collstate->location_libelle;
	        $line = str_replace('!!localisation!!', $collstate_location, $line);
	        $line = str_replace('!!cote!!', $my_collstate->cote, $line);	        
	        $line = str_replace('!!type_libelle!!', $my_collstate->type_libelle, $line);
	        $line = str_replace('!!emplacement_libelle!!', $my_collstate->emplacement_libelle, $line);
	        $line = str_replace('!!statut_libelle!!', $my_collstate->statut_opac_libelle, $line);
	        $line = str_replace('!!origine!!', $my_collstate->origine, $line);
	        $line = str_replace('!!state_collections!!',str_replace("\n","<br />",$my_collstate->state_collections), $line);
	        $line = str_replace('!!archive!!',$my_collstate->archive, $line);
	        $line = str_replace('!!lacune!!', str_replace("\n","<br />",$my_collstate->lacune), $line);
	        $liste.=$line;
		}
		$liste = str_replace('!!collstate_liste!!',$liste , $tpl_collstate_liste[$type]);
		$liste = str_replace('!!base_url!!', $base_url, $liste);
		$liste = str_replace('!!location!!', $location, $liste);		
	} else {
		$liste= $msg["collstate_no_collstate"];
	}	
	$this->liste=$liste;
		
}


} // fin d�finition classe
