<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: etagere.class.php,v 1.15 2013-11-08 08:13:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'auteurs'

if ( ! defined( 'ETAGERE_CLASS' ) ) {
  define( 'ETAGERE_CLASS', 1 );
  
require_once($class_path."/sort.class.php");


class etagere {
// propri�t�s
var $idetagere ;
var $name = ''			;	// nom de r�f�rence
var $comment = ""		;	// description du contenu du panier
var $validite = 1		;	// validite de l'�tag�re permanente ?
var $validite_date_deb = ''	;	// 	si non permanente date de d�but
var $validite_date_fin = ''	;	// 	                  date de fin
var $validite_date_deb_f = ''	;	// 	si non permanente date de d�but format�e
var $validite_date_fin_f = ''	;	// 	                  date de fin format�e
var $visible_accueil = 1	;	// visible en page d'accueil ?
var $autorisations = ""		;	// autorisations accord�es sur ce panier

// constructeur
function etagere($etagere_id=0) {
	if($etagere_id) {
		// on cherche � atteindre une etagere existant
		$this->idetagere = $etagere_id;
		$this->getData();
		} else {
			// l'etagere n'existe pas
			$this->idetagere = 0;
			$this->getData();
			}
	}

// r�cup�ration infos etagere
function getData() {
	global $dbh;
	global $msg ;
	if(!$this->idetagere) {
		// pas d'identifiant.
		$this->name	= '';
		$this->comment	= '';
		$this->autorisations	= "";
		$this->validite = "";
		$this->validite_date_deb = "";
		$this->validite_date_fin = "";
		$this->validite_date_deb_f = "";
		$this->validite_date_fin_f = "";
		$this->visible_accueil = "";
		$this->id_tri = 0;
		} else {
			$requete = "SELECT idetagere, name, comment, validite, ";
			$requete .= "validite_date_deb, date_format(validite_date_deb, '".$msg["format_date"]."') as validite_date_deb_f,  ";
			$requete .= "validite_date_fin, date_format(validite_date_fin, '".$msg["format_date"]."') as validite_date_fin_f,  ";
			$requete .= "visible_accueil, autorisations, id_tri FROM etagere WHERE idetagere='$this->idetagere' ";
			$result = @mysql_query($requete, $dbh);
			if(mysql_num_rows($result)) {
				$temp = mysql_fetch_object($result);
				mysql_free_result($result);
				$this->idetagere = $temp->idetagere;
				$this->name = $temp->name;
				$this->comment = $temp->comment;
				$this->validite = $temp->validite;
				$this->validite_date_deb = $temp->validite_date_deb;
				$this->validite_date_deb_f = $temp->validite_date_deb_f;
				$this->validite_date_fin = $temp->validite_date_fin;
				$this->validite_date_fin_f = $temp->validite_date_fin_f;
				$this->visible_accueil = $temp->visible_accueil;
				$this->autorisations = $temp->autorisations;
				$this->id_tri = $temp->id_tri;
				} else {
					// pas de caddie avec cet id
					$this->idetagere = 0;
					$this->name = "";
					$this->comment = "";
					$this->validite = "";
					$this->validite_date_deb = "";
					$this->validite_date_fin = "";
					$this->validite_date_deb_f = "";
					$this->validite_date_fin_f = "";
					$this->visible_accueil = "";
					$this->autorisations = "";
					$this->id_tri = $temp->id_tri;
					}
			}
	}

// liste des �tag�res disponibles
static function get_etagere_list() {
	global $dbh;
	global $msg ;
	$etagere_list=array();
	$requete = "SELECT idetagere, name, comment, validite, ";
	$requete .= "validite_date_deb, date_format(validite_date_deb, '".$msg["format_date"]."') as validite_date_deb_f,  ";
	$requete .= "validite_date_fin, date_format(validite_date_fin, '".$msg["format_date"]."') as validite_date_fin_f,  ";
	$requete .= "visible_accueil, autorisations FROM etagere order by name ";
	$result = @mysql_query($requete, $dbh);
	if(mysql_num_rows($result)) {
		while ($temp = mysql_fetch_object($result)) {
				$sql = "SELECT COUNT(*) FROM etagere_caddie WHERE etagere_id = ".$temp->idetagere;
				$res = mysql_query($sql, $dbh);
				$nbr_paniers = mysql_result($res, 0, 0);
								
				$etagere_list[] = array( 
					'idetagere' => $temp->idetagere,
					'name' => $temp->name,
					'type' => $temp->type,
					'comment' => $temp->comment,
					'validite' => $temp->validite,
					'validite_date_deb' => $temp->validite_date_deb,
					'validite_date_fin' => $temp->validite_date_fin,
					'validite_date_deb_f' => $temp->validite_date_deb_f,
					'validite_date_fin_f' => $temp->validite_date_fin_f,
					'visible_accueil' => $temp->visible_accueil,
					'autorisations' => $temp->autorisations,
					'nb_paniers' => $nbr_paniers
					);
			}
		} 
	return $etagere_list;
}

// cr�ation d'une etagere vide
function create_etagere() {
	global $dbh;
	$requete = "insert into etagere set name='".$this->name."', comment='".$this->comment."', validite='".$his->validite."', validite_date_deb='".$this->validite_date_deb."', validite_date_fin='".$this->validite_date_fin."', visible_accueil='".$this->visible_accueil."', autorisations='".$this->autorisations."'";
	$result = @mysql_query($requete, $dbh);
	$this->idetagere = mysql_insert_id($dbh);
	}

// ajout d'un item panier
function add_panier($item=0) {
	global $dbh;
	if (!$item) return 0 ;
	$requete_compte = "select count(1) from etagere_caddie where etagere_id='".$this->idetagere."' and caddie_id='".$item."' ";
	$result_compte = @mysql_query($requete_compte, $dbh);
	$deja_item=mysql_result($result_compte, 0, 0);
	if (!$deja_item) {
		$requete = "insert into etagere_caddie set etagere_id='".$this->idetagere."', caddie_id='".$item."' ";
		$result = @mysql_query($requete, $dbh);
		} else return 0;
	return 1 ;
	}

// suppression d'un item panier
function del_item($item=0) {
	global $dbh;
	$requete = "delete FROM etagere_caddie where etagere_id='".$this->idcaddie."' and caddie_id='".$item."' ";
	$result = @mysql_query($requete, $dbh);
	}

// suppression d'une etagere
function delete() {
	global $dbh;
	$requete = "delete FROM etagere_caddie where etagere_id='".$this->idetagere."' ";
	$result = @mysql_query($requete, $dbh);
	$requete = "delete FROM etagere where idetagere='".$this->idetagere."' ";
	$result = @mysql_query($requete, $dbh);
	}

// sauvegarde de l'etagere
function save_etagere() {
	global $dbh;
	$requete = "update etagere set name='".$this->name."', comment='".$this->comment."', validite='".$this->validite."', validite_date_deb='".$this->validite_date_deb."', validite_date_fin='".$this->validite_date_fin."', visible_accueil='".$this->visible_accueil."', autorisations='".$this->autorisations."',id_tri='".$this->tri."' where idetagere='".$this->idetagere."'";
	$result = @mysql_query($requete, $dbh);
	}


// get_cart() : ouvre une �tag�re et r�cup�re le contenu
function constitution($modif=1) {
	global $dbh;
	global $PMBuserid ;
	global $msg ;
	$ret .= "<table><tr><th style='text-align:right;'>".$msg['etagere_caddie_inclus']."</th><th>".$msg['caddie_name']."</th></tr>" ;
	$rqt_caddie = "SELECT idcaddie, name, comment FROM caddie where type='NOTI' order by name "; 
	$rescaddie = @mysql_query($rqt_caddie, $dbh);
	$parity=1;
	while ($caddie = mysql_fetch_object($rescaddie)) {
		if ($PMBuserid==1 || verif_droit_caddie($caddie->idcaddie)) {
			if ($parity % 2) {
				$pair_impair = "even";
				} else {
					$pair_impair = "odd";
					}
			$parity += 1;
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" ";
                	$link_visu_caddie = "<a href='./catalog.php?categ=caddie&sub=gestion&quoi=panier&action=&idcaddie=".$caddie->idcaddie."' target='_blank'>" ;
			$ret .= "<tr class='$pair_impair' $tr_javascript>";

			$ret .= "<td style='text-align:right;'><input type=checkbox name=idcaddie[] value='".$caddie->idcaddie."' class='checkbox' " ;
			if ($this->caddie_inclus($caddie->idcaddie)) $ret .= " checked ";
			if (!$modif) $ret .= " disabled='disabled' ";
			$ret .= " />&nbsp;</td>";

			$ret .= "<td>".$link_visu_caddie.$caddie->name ;
			if ($caddie->comment) $ret .= " (".$caddie->comment.")" ;
			$ret .= "</a>";
			$ret .= "</td>";
			$ret .= "</tr>" ;
			}
		}
	$ret .= "</table>" ;
	return $ret;
	}

function caddie_inclus($caddie) {
	global $dbh;
	$rqt = "SELECT count(1) FROM etagere_caddie where etagere_id='".$this->idetagere."' and caddie_id='".$caddie."' "; 
	return mysql_result(mysql_query($rqt,$dbh), 0, 0) ;
	}
	
} // fin de d�claration de la classe cart
  
} # fin de d�claration du fichier caddie.class
