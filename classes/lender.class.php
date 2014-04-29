<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $s_id: lender.class,v 1.0 2003/03/01 22:47:48 balno Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'pr�teurs d'ouvrage'

if ( ! defined( 'LENDER_CLASS' ) ) {
  define( 'LENDER_CLASS', 1 );

class lender {

// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------

	var $idlender=0;			// MySQL idlender in table 'lenders'
	var	$lender_libelle='';		// nom du pr�teur


// ---------------------------------------------------------------
//		lender($id) : constructeur
// ---------------------------------------------------------------

function lender($id=0) {
	if($id!="") {
		// on cherche � atteindre un lender existant
		$this->idlender = $id;
		$this->getData();
	} else {
		// le lender n'existe pas
		$this->idlender = 0;
		$this->getData();
	}
}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos du lender
// ---------------------------------------------------------------

function getData() {
	global $dbh;
	
	if($this->idlender=="") {
		// pas d'identifiant. on retourne un tableau vide
		$this->idlender	= 0;
		$this->lender_libelle =	"";
	} else {
		$requete = "SELECT idlender, lender_libelle FROM lenders WHERE idlender='".$this->idlender."' LIMIT 1 " or die (mysql_error());
		$result = mysql_query($requete, $dbh);
		if(mysql_num_rows($result)) {
			$temp = mysql_fetch_object($result);
			$this->idlender		= $temp->idlender;
			$this->lender_libelle		= $temp->lender_libelle;
		} else {
			// pas de lender avec cette cl�
			$this->idlender		=	0;
			$this->lender_libelle	=	"";
		}
	}
}

/* une fonction pour g�n�rer des combo Box 
   param�tres :
	$selected : l'�l�ment s�lection� le cas �ch�ant
   retourne une chaine de caract�res contenant l'objet complet */

static function gen_combo_box ( $selected ) {
	$requete="select idlender, lender_libelle from lenders order by lender_libelle ";
	$champ_code="idlender";
	$champ_info="lender_libelle";
	$nom="book_lender_id";
	$on_change="";
	$liste_vide_code="0";
	$liste_vide_info="Aucun pr�teur d�fini";
	$option_premier_code="";
	$option_premier_info="";
	$gen_liste_str="";
	$resultat_liste=mysql_query($requete);
	$gen_liste_str = "<select name=\"$nom\" onChange=\"$on_change\">\n" ;
	$nb_liste=mysql_numrows($resultat_liste);
	if ($nb_liste==0) {
		$gen_liste_str.="<option value=\"$liste_vide_code\">$liste_vide_info</option>\n" ;
	} else {
		if ($option_premier_info!="") {	
			$gen_liste_str.="<option value=\"".$option_premier_code."\" ";
			if ($selected==$option_premier_code) $gen_liste_str.="selected" ;
			$gen_liste_str.=">".$option_premier_info."\n";
		}
		$i=0;
		while ($i<$nb_liste) {
			$gen_liste_str.="<option value=\"".mysql_result($resultat_liste,$i,$champ_code)."\" " ;
			if ($selected==mysql_result($resultat_liste,$i,$champ_code)) {
				$gen_liste_str.="selected" ;
			}
			$gen_liste_str.=">".mysql_result($resultat_liste,$i,$champ_info)."</option>\n" ;
			$i++;
		}
	}
	$gen_liste_str.="</select>\n" ;
	return $gen_liste_str ;
} /* fin gen_combo_box */

function import($data) {

	// cette m�thode prend en entr�e un tableau constitu� des informations suivantes :
	//	$data['lender_libelle'] 	

	global $dbh;

	// check sur le type de la variable pass�e en param�tre
	if(!sizeof($data) || !is_array($data)) {
		// si ce n'est pas un tableau ou un tableau vide, on retourne 0
		return 0;
	}
	// check sur les �l�ments du tableau
	
	$long_maxi = mysql_field_len(mysql_query("SELECT lender_libelle FROM lenders limit 1"),0);
	$data['lender_libelle'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['lender_libelle']))),0,$long_maxi));

	if($data['lender_libelle']=="") return 0;

	// pr�paration de la requ�te
	$key0 = addslashes($data['lender_libelle']);
	
	/* v�rification que le lender existe */
	$query = "SELECT idlender FROM lenders WHERE lender_libelle='${key0}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT lenders ".$query);
	$lenders  = mysql_fetch_object($result);

	/* le lender existe, on retourne l'ID */
	if($lenders->idlender) return $lenders->idlender;

	// id non-r�cup�r�e, il faut cr�er la forme.
	
	$query  = "INSERT INTO lenders SET lender_libelle='".$key0."' ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into lenders ".$query);

	return mysql_insert_id($dbh);

} /* fin m�thode import */


} # fin de d�finition de la classe serie

} # fin de d�laration

