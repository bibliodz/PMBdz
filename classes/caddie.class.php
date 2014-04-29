<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: caddie.class.php,v 1.42 2014-02-21 16:06:09 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des paniers

if ( ! defined( 'CADDIE_CLASS' ) ) {
  define( 'CADDIE_CLASS', 1 );

define( 'CADDIE_ITEM_NULL', 0 );
define( 'CADDIE_ITEM_OK', 1 );
define( 'CADDIE_ITEM_DEJA', 1 ); // identique car on peut ajouter des li�s avec l'item et non pas l'item saisi lui-m�me ...
define( 'CADDIE_ITEM_IMPOSSIBLE_BULLETIN', 2 );
define( 'CADDIE_ITEM_EXPL_PRET' , 3 );
define( 'CADDIE_ITEM_BULL_USED', 4) ;
define( 'CADDIE_ITEM_NOTI_USED', 5) ;
define( 'CADDIE_ITEM_SUPPR_BASE_OK', 6) ;

define( 'CADDIE_ITEM_INEXISTANT', 7 );

require_once ($class_path."/expl.class.php");
require_once ($class_path."/audit.class.php");

class caddie {
// propri�t�s
var $idcaddie ;
var $name = ''			;	// nom de r�f�rence
var $type = ''			;	// Type de panier (EXPL = exemplaire, BULL = bulletin, NOTI = notice)
var $comment = ""		;	// description du contenu du panier
var $nb_item = 0		;	// nombre d'enregistrements dans le panier
var $nb_item_pointe = 0		;	// nombre d'enregistrements point�s dans le panier
var $item_base = 0		;	// nombre d'enregistrements issus/connus dans la base PMB dans le panier
var $nb_item_base_pointe = 0	;	// nombre d'enregistrements point�s issus/connus dans la base PMB dans le panier
var $nb_item_blob = 0 		;	// nombre d'enregistrements inconnus dans la base PMB dans le panier
var $nb_item_blob_pointe = 0 	;	// nombre d'enregistrements point�s inconnus dans la base PMB dans le panier
var $autorisations = ""		;	// autorisations accord�es sur ce panier

// ---------------------------------------------------------------
//		caddie($id) : constructeur
// ---------------------------------------------------------------
function caddie($caddie_id=0) {
	if($caddie_id) {
		// on cherche � atteindre un caddie existant
		$this->idcaddie = $caddie_id;
		$this->getData();
	} else {
		// la notice n'existe pas
		$this->idcaddie = 0;
		$this->getData();
	}
}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos caddie
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	if(!$this->idcaddie) {
		// pas d'identifiant.
		$this->type	= '';
		$this->name	= '';
		$this->comment	= '';
		$this->nb_item	= 0;
		$this->autorisations	= "";
	} else {
		$requete = "SELECT * FROM caddie WHERE idcaddie='$this->idcaddie' ";
		$result = @mysql_query($requete, $dbh);
		if(mysql_num_rows($result)) {
			$temp = mysql_fetch_object($result);
			mysql_free_result($result);
			$this->idcaddie = $temp->idcaddie;
			$this->type = $temp->type;
			$this->name = $temp->name;
			$this->comment = $temp->comment;
			$this->autorisations = $temp->autorisations;
		} else {
			// pas de caddie avec cet id
			$this->idcaddie = 0;
			$this->type = '';
			$this->name = '';
			$this->comment = '';
			$this->autorisations = "";
		}
		$this->compte_items();
	}
}

// liste des paniers disponibles
static function get_cart_list($restriction_panier="") {
	global $dbh, $PMBuserid;
	$cart_list=array();
	if ($restriction_panier=="") $requete = "SELECT * FROM caddie where 1 ";
	else $requete = "SELECT * FROM caddie where type='$restriction_panier' ";
	if ($PMBuserid!=1) $requete.=" and (autorisations='$PMBuserid' or autorisations like '$PMBuserid %' or autorisations like '% $PMBuserid %' or autorisations like '% $PMBuserid') ";
	$requete.=" order by type, name, comment ";
	$result = @mysql_query($requete, $dbh) or die (mysql_error()."<br />".$requete);
	if(mysql_num_rows($result)) {
		while ($temp = mysql_fetch_object($result)) {
			$nb_item = 0 ;
			$nb_item_pointe = 0 ;
			$nb_item_base = 0 ;
			$nb_item_base_pointe = 0 ;
			$nb_item_blob = 0 ;
			$nb_item_blob_pointe = 0 ;
			$rqt_nb_item="select count(1) from caddie_content where caddie_id='".$temp->idcaddie."' ";
			$nb_item = mysql_result(mysql_query($rqt_nb_item, $dbh), 0, 0);
			$rqt_nb_item_pointe = "select count(1) from caddie_content where caddie_id='".$temp->idcaddie."' and (flag is not null and flag!='') ";
			$nb_item_pointe = mysql_result(mysql_query($rqt_nb_item_pointe, $dbh), 0, 0);
			$rqt_nb_item_base="select count(1) from caddie_content where caddie_id='".$temp->idcaddie."' and (content is null or content='') ";
			$nb_item_base = mysql_result(mysql_query($rqt_nb_item_base, $dbh), 0, 0);
			$rqt_nb_item_base_pointe="select count(1) from caddie_content where caddie_id='".$temp->idcaddie."' and (content is null or content='') and (flag is not null and flag!='') ";
			$nb_item_base_pointe = mysql_result(mysql_query($rqt_nb_item_base_pointe, $dbh), 0, 0);
			$nb_item_blob = $nb_item - $nb_item_base ;
			$nb_item_blob_pointe = $nb_item_pointe - $nb_item_base_pointe ;

			$cart_list[] = array( 
				'idcaddie' => $temp->idcaddie,
				'name' => $temp->name,
				'type' => $temp->type,
				'comment' => $temp->comment,
				'autorisations' => $temp->autorisations,
				'nb_item' => $nb_item,
				'nb_item_pointe' => $nb_item_pointe,
				'nb_item_base' => $nb_item_base,
				'nb_item_base_pointe' => $nb_item_base_pointe,
				'nb_item_blob' => $nb_item_blob,
				'nb_item_blob_pointe' => $nb_item_blob_pointe
			
				);
		}
	} 
	return $cart_list;
}

// cr�ation d'un panier vide
function create_cart() {
	global $dbh;
	$requete = "insert into caddie set name='".$this->name."', type='".$this->type."', comment='".$this->comment."', autorisations='".$this->autorisations."' ";
	$result = @mysql_query($requete, $dbh);
	$this->idcaddie = mysql_insert_id($dbh);
	$this->compte_items();
	}


// ajout d'un item
function add_item($item=0, $object_type="NOTI", $bul_or_dep="") {
	// $bul_or_dep permet de choisir entre notice de d�pouillement (DEP) 
	//   ou notice de bulletin (par d�faut) lors de l'ajout d'un bulletin � un panier de notices
	
	global $dbh;
	
	if (!$item) return CADDIE_ITEM_NULL ;
	
	// les objets sont identiques
	if ($object_type==$this->type) {
		// r�gle : les caddies sont homog�nes, on y stocke des objets de m�me type en fonction du type du caddie
		$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' AND object_id='".$item."' ";
		$result_compte = @mysql_query($requete_compte, $dbh);
		$deja_item=mysql_result($result_compte, 0, 0);
		if (!$deja_item) {
			$requete= "insert into caddie_content set caddie_id='".$this->idcaddie."', object_id='".$item."', content='' ";
			$result = @mysql_query($requete, $dbh);
		}
	} else {
		// Traitement des cas particuliers
		// panier d'exemplaires : 
		//		Notice re�ue : 
		//			on stocke tous les exemplaires associ�s � la notice
		//				voir le pb de notice de d�pouillement
		if ($this->type=="EXPL" && $object_type=="NOTI") { 
			$rqt_mono_serial_bull_analysis = "select niveau_biblio, niveau_hierar from notices where notice_id = '$item' ";
			$res_mono_serial_bull_analysis = mysql_query($rqt_mono_serial_bull_analysis, $dbh);
			$row_mono_serial_bull_analysis = mysql_fetch_object($res_mono_serial_bull_analysis);
			// monographie
			if ($row_mono_serial_bull_analysis->niveau_biblio=="m" && $row_mono_serial_bull_analysis->niveau_hierar=="0")
				$rqt_expl = "select expl_id from exemplaires where expl_notice='$item' ";
			// p�riodique : notice m�re
			if ($row_mono_serial_bull_analysis->niveau_biblio=="s" && $row_mono_serial_bull_analysis->niveau_hierar=="1")
				$rqt_expl = "select expl_id from exemplaires, bulletins where bulletin_notice='$item' and expl_bulletin=bulletin_id ";
			// p�riodique : notice de d�pouillement (analytique)
			if ($row_mono_serial_bull_analysis->niveau_biblio=="a" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select expl_id from exemplaires, analysis where analysis_notice='$item' and analysis_bulletin=expl_bulletin ";
			// bulletin : notice de bulletin
			if ($row_mono_serial_bull_analysis->niveau_biblio=="b" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select expl_id from exemplaires, bulletins where num_notice='$item' and bulletin_id=expl_bulletin ";
		}
		//		Bulletin re�u : 
		//			on stocke tous les exemplaires associ�s au bulletin
		if ($this->type=="EXPL" && $object_type=="BULL") {
			$rqt_expl = "select expl_id from exemplaires where expl_bulletin='$item' ";
		}
		
		// panier de notices :
		//		EXPL re�u : 
		//			on stocke la notice de l'exemplaire 
		//				voir le pb d'expl de bulletin
		if ($this->type=="NOTI" && $object_type=="EXPL") {
			$rqt_mono_bull = "select expl_notice, expl_bulletin from exemplaires where expl_id='$item' ";
			$res_mono_bull = mysql_query($rqt_mono_bull, $dbh);
			$row_mono_bull = mysql_fetch_object($res_mono_bull);
			// expl de monographie
			if ($row_mono_bull->expl_notice && !$row_mono_bull->expl_bulletin)
				$rqt_expl = "select expl_notice from exemplaires where expl_id='$item' ";
			// expl de bulletin
			if (!$row_mono_bull->expl_notice && $row_mono_bull->expl_bulletin)
				$rqt_expl = "select bulletin_notice from exemplaires, bulletins where expl_id='$item' and expl_bulletin=bulletin_id ";
		} 
		//		BULL re�u : 
		//			on stocke la notice du bulletin si existante
		//    ATTENTION: modif version 3.1.12: ajout de la notice de bulletin et non plus les notices de d�pouillement
		if ($this->type=="NOTI" && $object_type=="BULL") {
			if ($bul_or_dep=="DEP") $rqt_expl = "select analysis_notice from analysis where analysis_bulletin='$item' ";
			else $rqt_expl = "select num_notice from bulletins where bulletin_id='$item' and num_notice!=0";
		} // fin if NOTI / BULL
		
		// panier de bulletins :
		//		EXPL re�u : 
		//			on stocke le bulletin de l'exemplaire 
		if ($this->type=="BULL" && $object_type=="EXPL") {
			$rqt_mono_bull = "select expl_notice, expl_bulletin from exemplaires where expl_id='$item' ";
			$res_mono_bull = mysql_query($rqt_mono_bull, $dbh);
			$row_mono_bull = mysql_fetch_object($res_mono_bull);
			// expl de monographie
			if ($row_mono_bull->expl_notice && !$row_mono_bull->expl_bulletin)
				return CADDIE_ITEM_IMPOSSIBLE_BULLETIN;
			// expl de bulletin
			if (!$row_mono_bull->expl_notice && $row_mono_bull->expl_bulletin)
				$rqt_expl = "select expl_bulletin from exemplaires where expl_id='$item' ";
		}
		//		NOTI re�ue : 
		//			on stocke le bulletin associ� � la notice chapeau re�ue
		//			ou bien le bulletin contenant la notice de d�pouillement re�ue
		if ($this->type=="BULL" && $object_type=="NOTI") {
			$rqt_mono_serial_bull_analysis = "select niveau_biblio, niveau_hierar from notices where notice_id = '$item' ";
			$res_mono_serial_bull_analysis = mysql_query($rqt_mono_serial_bull_analysis, $dbh);
			$row_mono_serial_bull_analysis = mysql_fetch_object($res_mono_serial_bull_analysis);
			// monographie
			if ($row_mono_serial_bull_analysis->niveau_biblio=="m" && $row_mono_serial_bull_analysis->niveau_hierar=="0")
				return CADDIE_ITEM_IMPOSSIBLE_BULLETIN;
			// p�riodique : notice m�re
			if ($row_mono_serial_bull_analysis->niveau_biblio=="s" && $row_mono_serial_bull_analysis->niveau_hierar=="1")
				$rqt_expl = "select bulletin_id from bulletins where bulletin_notice='$item' ";
			// p�riodique : notice de d�pouillement (analytique)
			if ($row_mono_serial_bull_analysis->niveau_biblio=="a" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select analysis_bulletin from analysis where analysis_notice='$item' ";
			// bulletin : notice de bulletin
			if ($row_mono_serial_bull_analysis->niveau_biblio=="b" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select bulletin_id from bulletins where num_notice='$item' ";
		}
		if ($this->type=="EXPL" && $object_type=="EXPL") {
			$rqt_expl = "select expl_id from exemplaires where expl_id='$item' ";
		} // fin if NOTI / BULL
		
		if ($rqt_expl) {
			$res_expl = mysql_query($rqt_expl, $dbh);
			for($i=0;$i<mysql_num_rows($res_expl);$i++) {
				$row=mysql_fetch_row($res_expl);
				$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' AND object_id='".$row[0]."' ";
				$result_compte = @mysql_query($requete_compte, $dbh);
				$deja_item=mysql_result($result_compte, 0, 0);
				if (!$deja_item) {
					$requete= "insert into caddie_content set caddie_id='".$this->idcaddie."', object_id='".$row[0]."', content='' ";
					$result = @mysql_query($requete, $dbh);
				}
			} // fin for
		}
	} // fin else types diff�rents
	return CADDIE_ITEM_OK ;
}

// ajout d'un item blob
function add_item_blob($blobobject=0, $blob_type="EXPL_CB") {
	global $dbh;
	
	if (!$blobobject) return CADDIE_ITEM_NULL ;
	
	$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and content='".$blobobject."' and blob_type='".$blob_type."' ";
	$result_compte = @mysql_query($requete_compte, $dbh);
	$deja_item=mysql_result($result_compte, 0, 0);
	
	if (!$deja_item) {
		$requete= "insert into caddie_content set caddie_id='".$this->idcaddie."', object_id=0, content='".$blobobject."', blob_type='".$blob_type."' ";
		$result = mysql_query($requete, $dbh);
	}	
}			
	
// suppression d'un item
function del_item($item=0) {
	global $dbh;
	$requete = "delete FROM caddie_content where caddie_id='".$this->idcaddie."' and object_id='".$item."' ";
	$result = @mysql_query($requete, $dbh);
	$this->compte_items();
	}

// suppression d'un item EXPL_CB
function del_item_blob($expl_cb="") {
	global $dbh;
	$requete = "delete FROM caddie_content where caddie_id='".$this->idcaddie."' and blob_type='EXPL_CB' and content='".$expl_cb."' ";
	$result = @mysql_query($requete, $dbh);
	$this->compte_items();
	}

function del_item_base($item=0,$forcage=array()) {
	global $dbh;
	
	if (!$item) return CADDIE_ITEM_NULL ;
	
	switch ($this->type) {
		case "EXPL" :
			if (!$this->verif_expl_item($item)) {
				if ($forcage['source_id']) {
					exemplaire::save_to_agnostic_warehouse(array(0=>$item),$forcage['source_id']);
				}
				exemplaire::del_expl($item);
				return CADDIE_ITEM_SUPPR_BASE_OK ;
			} else return CADDIE_ITEM_EXPL_PRET ;
			break ;
		case "BULL" :
			if (!$this->verif_bull_item($item,$forcage)) {
				// aucun pr�t d'exemplaire de ce bulletin en cours, on supprime :
				$myBulletinage = new bulletinage($item);
				$myBulletinage->delete();	
				
				return CADDIE_ITEM_SUPPR_BASE_OK ;
			} else return CADDIE_ITEM_BULL_USED ;
			break ;
		case "NOTI" :
			if (!$this->verif_noti_item($item,$forcage)) {
				if ($forcage['source_id']) {
					notice::save_to_agnostic_warehouse(array(0=>$item),$forcage['source_id']);
				}
				$requete="SELECT niveau_biblio, niveau_hierar FROM notices WHERE notice_id='".$item."'";
				$res=mysql_query($requete, $dbh);
				if(mysql_num_rows($res) && (mysql_result($res,0,0) == "s") && (mysql_result($res,0,1) == "1")){
					$myBulletinage = new serial($item);
					$myBulletinage->serial_delete();
				}else{
					notice::del_notice($item);
				}
				return CADDIE_ITEM_SUPPR_BASE_OK ;
			} else return CADDIE_ITEM_NOTI_USED ;
			break ;
		}
					
	return CADDIE_ITEM_OK ;
	}

// suppression d'un item de tous les caddies du m�me type le contenant
function del_item_all_caddies($item, $type) {
	global $dbh;
	$requete = "select idcaddie FROM caddie where type='".$type."' ";
	$result = mysql_query($requete, $dbh);
	for($i=0;$i<mysql_num_rows($result);$i++) {
		$temp=mysql_fetch_object($result);
		$requete_suppr = "delete from caddie_content where caddie_id='".$temp->idcaddie."' and object_id='".$item."' ";
		$result_suppr = mysql_query($requete_suppr, $dbh);
	}
}

function del_item_flag($inconnu_aussi=1) {
	global $dbh;
	$requete = "delete FROM caddie_content where caddie_id='".$this->idcaddie."' and (flag is not null and flag!='') ";
	if (!$inconnu_aussi) $requete .= " and (content is null or content='') ";
	$result = @mysql_query($requete, $dbh);
	$this->compte_items();
}

function del_item_no_flag($inconnu_aussi=1) {
	global $dbh;
	$requete = "delete FROM caddie_content where caddie_id='".$this->idcaddie."' and (flag is null or flag='') ";
	if (!$inconnu_aussi) $requete .= " and (content is null or content='') "; 
	$result = @mysql_query($requete, $dbh);
	$this->compte_items();
}

// Export des documents num�rique d'un item 
function export_doc_num($item=0,$chemin) {
	global $dbh, $charset, $msg;
	
	$pattern_nom_fichier_doc_num="!!explnumid!!_!!idnotice!!_!!idbulletin!!_!!indicedocnum!!_!!nomdoc!!";
	
	if ($this->type=="NOTI") {
		$requete = "select explnum_id, explnum_notice as numnotice, explnum_bulletin, explnum_data, explnum_extfichier, explnum_nomfichier, length(explnum_data) as taille ";
		$requete .= " FROM explnum WHERE ";
		$requete .= " explnum_notice=$item ";
	} elseif ($this->type=="BULL") {
		$requete = "select explnum_id, bulletin_notice as numnotice, explnum_bulletin, explnum_data, explnum_extfichier, explnum_nomfichier, length(explnum_data) as taille ";
		$requete .= " FROM explnum JOIN bulletins on bulletin_id=explnum_bulletin WHERE ";
		$requete .= " explnum_bulletin=$item ";
	} else return; // pas encore de document num�rique attach� � un exemplaire
	$requete .= " and explnum_data is not null and explnum_data!='' ";
	
	$result = mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
	for($i=0;$i<mysql_num_rows($result);$i++) {
		$t=mysql_fetch_object($result);
		$t->explnum_id = str_pad ($t->explnum_id, 6, "0", STR_PAD_LEFT) ;
		$t->numnotice = str_pad ($t->numnotice, 6, "0", STR_PAD_LEFT) ;
		$t->explnum_bulletin = str_pad ($t->explnum_bulletin, 6, "0", STR_PAD_LEFT) ;
		$nomfic= $pattern_nom_fichier_doc_num;
		$nomfic = str_replace("!!explnumid!!",    str_pad ($t->explnum_id, 6, "0", STR_PAD_LEFT), $nomfic) ;
		$nomfic = str_replace("!!idnotice!!",     str_pad ($t->numnotice, 6, "0", STR_PAD_LEFT), $nomfic) ;
		$nomfic = str_replace("!!idbulletin!!",   str_pad ($t->explnum_bulletin, 6, "0", STR_PAD_LEFT), $nomfic) ;
		$nomfic = str_replace("!!indicedocnum!!", str_pad ($i, 3, "0", STR_PAD_LEFT), $nomfic) ;
		$nomfic = str_replace("!!nomdoc!!",       $t->explnum_nomfichier, $nomfic) ;
		$hf = fopen($chemin.$nomfic, "w");
		if ($hf) {
			fwrite($hf, $t->explnum_data);
			fclose($hf);
			$ret .= "<li>".$msg[caddie_expdocnum_wtrue]." <a href=\"".$chemin.$nomfic."\">".htmlentities($nomfic, ENT_QUOTES, $charset)."</a></li>";
		} else {
			$ret .= "<li><i>".$msg[caddie_expdocnum_wfalse]." ".htmlentities($nomfic, ENT_QUOTES, $charset)."</i></li>";
		}
	}
	if ($ret) return "<blockquote>".$msg[caddie_expdocnum_dir]." ".htmlentities($chemin, ENT_QUOTES, $charset)."<br /><ul>".$ret."</ul></blockquote>";
	else return;
	}

// Pointage d'un item
function depointe_items() {
	global $dbh;
	$requete = "update caddie_content set flag=null where caddie_id='".$this->idcaddie."' ";
	$result = @mysql_query($requete, $dbh);
	$this->compte_items();
}	

function depointe_item($item=0) {
	global $dbh;
	
	if ($item) {
		$requete = "update caddie_content set flag=null where caddie_id='".$this->idcaddie."' and object_id='".$item."' ";
		$result = @mysql_query($requete, $dbh);
		if ($result) {
			$this->compte_items();
			return 1;
		} else {
			return 0;
		}
	}
}

function pointe_item($item=0, $object_type="NOTI", $blob="", $blob_type="EXPL_CB") {
	global $dbh;
	
	if (!$item) {
		$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and content='".$blob."' and blob_type='".$blob_type."' ";
		$result_compte = @mysql_query($requete_compte, $dbh);
		$deja_item=mysql_result($result_compte, 0, 0);
		
		if ($deja_item) {
			$requete = "update caddie_content set flag='1' where caddie_id='".$this->idcaddie."' and content='".$blob."' ";
			$result = @mysql_query($requete, $dbh);
			$this->compte_items();
		} else return CADDIE_ITEM_INEXISTANT;
		
		return CADDIE_ITEM_NULL ;
	}
	
	// les objets sont identiques
	if ($object_type==$this->type) {
		// r�gle : les caddies sont homog�nes, on y stocke des objets de m�me type en fonction du type du caddie
		$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and object_id='".$item."' ";
		$result_compte = @mysql_query($requete_compte, $dbh);
		$deja_item=mysql_result($result_compte, 0, 0);
		
		if ($deja_item) {
			$requete = "update caddie_content set flag='1' where caddie_id='".$this->idcaddie."' and object_id='".$item."' ";
			$result = @mysql_query($requete, $dbh);
			$this->compte_items();
		} else return CADDIE_ITEM_INEXISTANT;
	} else {
		// Traitement des cas particuliers
		// panier d'exemplaires : 
		//		Notice re�ue : 
		//			on stocke tous les exemplaires associ�s � la notice
		//				voir le pb de notice de d�pouillement
		if ($this->type=="EXPL" && $object_type=="NOTI") {
			$rqt_mono_serial_bull_analysis = "select niveau_biblio, niveau_hierar from notices where notice_id = '$item' ";
			$res_mono_serial_bull_analysis = mysql_query($rqt_mono_serial_bull_analysis, $dbh);
			$row_mono_serial_bull_analysis = mysql_fetch_object($res_mono_serial_bull_analysis);
			// monographie
			if ($row_mono_serial_bull_analysis->niveau_biblio=="m" && $row_mono_serial_bull_analysis->niveau_hierar=="0")
				$rqt_expl = "select expl_id from exemplaires where expl_notice='$item' ";
			// p�riodique : notice m�re
			if ($row_mono_serial_bull_analysis->niveau_biblio=="s" && $row_mono_serial_bull_analysis->niveau_hierar=="1")
				$rqt_expl = "select expl_id from exemplaires, bulletins where bulletin_notice='$item' and expl_bulletin=bulletin_id ";
			// p�riodique : notice de d�pouillement (analytique)
			if ($row_mono_serial_bull_analysis->niveau_biblio=="a" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select expl_id from exemplaires, analysis where analysis_notice='$item' and analysis_bulletin=expl_bulletin ";
			// bulletin : notice de bulletin
			if ($row_mono_serial_bull_analysis->niveau_biblio=="b" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select expl_id from exemplaires, bulletins where num_notice='$item' and bulletin_id=expl_bulletin ";
		}
		//		Bulletin re�u : 
		//			on stocke tous les exemplaires associ�s au bulletin
		if ($this->type=="EXPL" && $object_type=="BULL") {
			$rqt_expl = "select expl_id from exemplaires where expl_bulletin='$item' ";
		}
		
		// panier de notices :
		//		EXPL re�u : 
		//			on stocke la notice de l'exemplaire 
		//				voir le pb d'expl de bulletin
		if ($this->type=="NOTI" && $object_type=="EXPL") {
			$rqt_mono_bull = "select expl_notice, expl_bulletin from exemplaires where expl_id='$item' ";
			$res_mono_bull = mysql_query($rqt_mono_bull, $dbh);
			$row_mono_bull = mysql_fetch_object($res_mono_bull);
			// expl de monographie
			if ($row_mono_bull->expl_notice && !$row_mono_bull->expl_bulletin)
				$rqt_expl = "select expl_notice from exemplaires where expl_id='$item' ";
			// expl de bulletin
			if (!$row_mono_bull->expl_notice && $row_mono_bull->expl_bulletin)
				$rqt_expl = "select bulletin_notice from exemplaires, bulletins where expl_id='$item' and expl_bulletin=bulletin_id ";
		} 
		//		BULL re�u : 
		//			on stocke les notices de d�pouillement du bulletin
		if ($this->type=="NOTI" && $object_type=="BULL") {
			$rqt_expl = "select analysis_notice from analysis where analysis_bulletin='$item' ";
		} // fin if NOTI / EXPL
		
		// panier de bulletins :
		//		EXPL re�u : 
		//			on stocke le bulletin de l'exemplaire 
		if ($this->type=="BULL" && $object_type=="EXPL") {
			$rqt_mono_bull = "select expl_notice, expl_bulletin from exemplaires where expl_id='$item' ";
			$res_mono_bull = mysql_query($rqt_mono_bull, $dbh);
			$row_mono_bull = mysql_fetch_object($res_mono_bull);
			// expl de monographie
			if ($row_mono_bull->expl_notice && !$row_mono_bull->expl_bulletin)
				return CADDIE_ITEM_IMPOSSIBLE_BULLETIN;
			// expl de bulletin
			if (!$row_mono_bull->expl_notice && $row_mono_bull->expl_bulletin)
				$rqt_expl = "select expl_bulletin from exemplaires where expl_id='$item' ";
		}
		//		NOTI re�ue : 
		//			on stocke le bulletin associ� � la notice chapeau re�ue
		//			ou bien le bulletin contenant la notice de d�pouillement re�ue
		if ($this->type=="BULL" && $object_type=="NOTI") {
			$rqt_mono_serial_bull_analysis = "select niveau_biblio, niveau_hierar from notices where notice_id = '$item' ";
			$res_mono_serial_bull_analysis = mysql_query($rqt_mono_serial_bull_analysis, $dbh);
			$row_mono_serial_bull_analysis = mysql_fetch_object($res_mono_serial_bull_analysis);
			// monographie
			if ($row_mono_serial_bull_analysis->niveau_biblio=="m" && $row_mono_serial_bull_analysis->niveau_hierar=="0")
				return CADDIE_ITEM_IMPOSSIBLE_BULLETIN;
			// p�riodique : notice m�re
			if ($row_mono_serial_bull_analysis->niveau_biblio=="s" && $row_mono_serial_bull_analysis->niveau_hierar=="1")
				$rqt_expl = "select bulletin_id from bulletins where bulletin_notice='$item' ";
			// p�riodique : notice de d�pouillement (analytique)
			if ($row_mono_serial_bull_analysis->niveau_biblio=="a" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select analysis_bulletin from analysis where analysis_notice='$item' ";
			// bulletin : notice de bulletin
			if ($row_mono_serial_bull_analysis->niveau_biblio=="b" && $row_mono_serial_bull_analysis->niveau_hierar=="2")
				$rqt_expl = "select bulletin_id from bulletins where num_notice='$item' ";
		}
		
		if ($rqt_expl) {
			$res_expl = mysql_query($rqt_expl, $dbh);
			for($i=0;$i<mysql_num_rows($res_expl);$i++) {
				$row=mysql_fetch_row($res_expl);
				$requete_compte = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and object_id='".$row[0]."' ";
				$result_compte = @mysql_query($requete_compte, $dbh);
				$deja_item=mysql_result($result_compte, 0, 0);
				if ($deja_item) {
					$requete = "update caddie_content set flag='1' where caddie_id='".$this->idcaddie."' and object_id='".$row[0]."' ";
					$result = @mysql_query($requete, $dbh);
				}
			} // fin for
			$this->compte_items();
		}
	} // fin else types diff�rents
	return CADDIE_ITEM_OK ;
	}

// suppression d'un panier
function delete() {
	global $dbh;
	$requete = "delete FROM caddie_content where caddie_id='".$this->idcaddie."' ";
	$result = @mysql_query($requete, $dbh);
	$requete = "delete FROM caddie where idcaddie='".$this->idcaddie."' ";
	$result = @mysql_query($requete, $dbh);
	
	}

// sauvegarde du panier
function save_cart() {
	global $dbh;
	$requete = "update caddie set name='".$this->name."', comment='".$this->comment."', autorisations='".$this->autorisations."' where idcaddie='".$this->idcaddie."'";
	$result = @mysql_query($requete, $dbh);
	}


// get_cart() : ouvre un panier et r�cup�re le contenu
function get_cart($flag="", $inconnu_aussi=1) {
	global $dbh;
	$cart_list=array();
	switch ($flag) {
		case "FLAG" :
			$requete = "SELECT * FROM caddie_content where caddie_id='".$this->idcaddie."' and (flag is not null and flag!='') ";
			if (!$inconnu_aussi) $requete .= " and (content is null or content='') "; 
			break ;
		case "NOFLAG" :
			$requete = "SELECT * FROM caddie_content where caddie_id='".$this->idcaddie."' and (flag is null or flag='') ";
			if (!$inconnu_aussi) $requete .= " and (content is null or content='') ";
			break ;
		case "ALL" :
		default :
			$requete = "SELECT * FROM caddie_content where caddie_id='".$this->idcaddie."' ";
			if (!$inconnu_aussi) $requete .= " and (content is null or content='') ";
			break ;
		}
	$result = @mysql_query($requete, $dbh);
	if(mysql_num_rows($result)) {
		while ($temp = mysql_fetch_object($result)) {
			$cart_list[] = $temp->object_id;
			}
		} 
	return $cart_list;
	}

// compte_items 
function compte_items() {
	global $dbh;
	$this->nb_item = 0 ;
	$this->nb_item_pointe = 0 ;
	$this->nb_item_base = 0 ;
	$this->nb_item_base_pointe = 0 ;
	$this->nb_item_blob = 0 ;
	$this->nb_item_blob_pointe = 0 ;
	$rqt_nb_item="select count(1) from caddie_content where caddie_id='".$this->idcaddie."' ";
	$this->nb_item = mysql_result(mysql_query($rqt_nb_item, $dbh), 0, 0);
	$rqt_nb_item_pointe = "select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and (flag is not null and flag!='') ";
	$this->nb_item_pointe = mysql_result(mysql_query($rqt_nb_item_pointe, $dbh), 0, 0);
	$rqt_nb_item_base="select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and (content is null or content='')";
	$this->nb_item_base = mysql_result(mysql_query($rqt_nb_item_base, $dbh), 0, 0);
	$rqt_nb_item_base_pointe="select count(1) from caddie_content where caddie_id='".$this->idcaddie."' and (content is null or content='') and (flag is not null and flag!='') ";
	$this->nb_item_base_pointe = mysql_result(mysql_query($rqt_nb_item_base_pointe, $dbh), 0, 0);
	$this->nb_item_blob = $this->nb_item - $this->nb_item_base ;
	$this->nb_item_blob_pointe = $this->nb_item_pointe - $this->nb_item_base_pointe ;
}

function verif_expl_item($expl) {

	global $dbh;
	if ($expl) {
		$query = "select count(1) from pret where pret_idexpl=".$expl." limit 1 ";
		$result = mysql_query($query, $dbh);
		if(mysql_result($result, 0, 0)) return 1 ;
		
		return 0 ;
		
		} else return 0 ;
}
	
function verif_bull_item($bull,$forcage=array()) {
	
	global $dbh;
	// plus aucune v�rification, on supprime en cascade :
	//		bulletin
	//		notice
	//		exemplaire
	//		exemplaires num�riques
	/*$query = "select count(1) from exemplaires, pret where expl_bulletin=".$bull." and pret_idexpl=expl_id limit 1 ";
	$result = mysql_query($query, $dbh);
	if (mysql_result($result, 0, 0)) return 1 ;
		else return 0 ;*/
	if($bull){
		$query = "select count(1) from analysis where analysis_bulletin=".$bull." limit 1 ";
		$result = mysql_query($query, $dbh);
		if(mysql_result($result, 0, 0)){
			return 1 ;
		}
		$query = "select count(1) from exemplaires where expl_bulletin=".$bull." limit 1 ";
		$result = mysql_query($query, $dbh);
		if(mysql_result($result, 0, 0)){
			return 1 ;
		}
		$query = "select count(1) from bulletins where bulletin_id=".$bull." AND num_notice!='0' limit 1 ";
		$result = mysql_query($query, $dbh);
		if(mysql_result($result, 0, 0)){
			return 1 ;
		}
		$query = "select count(1) from explnum where explnum_bulletin=".$bull." limit 1 ";
		$result = mysql_query($query, $dbh);
		if (mysql_result($result, 0, 0)&& !$forcage['bulletin_linked_expl_num']){
			return 1 ;
		}
	}
	return 0;
}
	
	function verif_noti_item($noti,$forcage=array()) {
	
		global $dbh;
		if ($noti) {
			if ($this->type=="BULL") {
				$query = "select count(1) from analysis where analysis_notice=".$noti." limit 1 ";
				$result = mysql_query($query, $dbh);
				if (mysql_result($result, 0, 0)) return 1 ;
			}
			
			$query = "select count(1) from bulletins where bulletin_notice=".$noti." limit 1 ";
			$result = mysql_query($query, $dbh);
			if (mysql_result($result, 0, 0)) return 1 ;
			
			$query = "select count(1) from notices_relations where num_notice=$noti or linked_notice=$noti limit 1 ";
			$result = mysql_query($query, $dbh);
			if (mysql_result($result, 0, 0)&& !$forcage['notice_linked']) return 1 ;
			
			$query = "select count(1) from exemplaires where expl_notice=".$noti." limit 1 ";
			$result = mysql_query($query, $dbh);
			if (mysql_result($result, 0, 0)) return 1 ;
			
			$query = "select count(1) from resa where resa_idnotice=".$noti." limit 1 ";
			$result = mysql_query($query, $dbh);
			if (mysql_result($result, 0, 0)) return 1 ;
			
			$query = "select count(1) from explnum where explnum_notice=".$noti." limit 1 ";
			$result = mysql_query($query, $dbh);
			if (mysql_result($result, 0, 0)&& !$forcage['notice_linked_expl_num']) return 1 ;
			
			//Pour les p�riodiques
			$requete="SELECT niveau_biblio, niveau_hierar FROM notices WHERE notice_id='".$noti."'";
			$res=mysql_query($requete, $dbh);
			if(mysql_num_rows($res) && (mysql_result($res,0,0) == "s") && (mysql_result($res,0,1) == "1")){
				
				$query = "select count(1) from collections_state where id_serial=".$noti." limit 1 ";
				$result = mysql_query($query, $dbh);
				if (mysql_result($result, 0, 0) && !$forcage['notice_perio_collstat']) return 1 ;
				
				$query = "select count(1) from abts_abts where num_notice=".$noti." limit 1 ";
				$result = mysql_query($query, $dbh);
				if (mysql_result($result, 0, 0) && !$forcage['notice_perio_abo']) return 1 ;
				
				$query = "select count(1) from abts_modeles where num_notice=".$noti." limit 1 ";
				$result = mysql_query($query, $dbh);
				if (mysql_result($result, 0, 0) && !$forcage['notice_perio_modele']) return 1 ;
			}
		}
		return 0 ;
	}
} // fin de d�claration de la classe cart
  
} # fin de d�claration du fichier caddie.class
