<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docs_type.class.php,v 1.7 2013-04-11 08:08:11 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'docs_type'

if ( ! defined( 'DOCSTYPE_CLASS' ) ) {
  define( 'DOCSTYPE_CLASS', 1 );

class docs_type {
/* ---------------------------------------------------------------
		propri�t�s de la classe
   -------------------------------------------------------------- */
var $id=0;
var $libelle='';
var $duree_pret=0;
var $tdoc_codage_import="";
var $tdoc_owner=0;

/* ---------------------------------------------------------------
		docs_type($id) : constructeur
   --------------------------------------------------------------- */
function docs_type($id=0) {
	if($id) {
		/* on cherche � atteindre un  typdoc existant */
		$this->id = $id;
	$this->getData();
	} else {
		$this->id = 0;
		$this->getData();
	}
}

/* ---------------------------------------------------------------
		getData() : r�cup�ration des propri�t�s
   --------------------------------------------------------------- */
function getData() {
	global $dbh;

	if(!$this->id) return;

	/* r�cup�ration des informations de la cat�gorie */
	$requete = 'SELECT * FROM docs_type WHERE idtyp_doc='.$this->id.' LIMIT 1;';
	$result = mysql_query($requete, $dbh) or die (mysql_error()." ".$requete);
	if(!mysql_num_rows($result)) return;
		
	$data = mysql_fetch_object($result);
	$this->id = $data->idtyp_doc;		
	$this->libelle = $data->tdoc_libelle;
	$this->duree_pret = $data->duree_pret;
	$this->tdoc_codage_import = $data->tdoc_codage_import;
	$this->tdoc_owner = $data->tdoc_owner;

}

// ---------------------------------------------------------------
//		import() : import d'un type de document
// ---------------------------------------------------------------
function import($data) {
	// cette m�thode prend en entr�e un tableau constitu� des informations suivantes :
	//	$data['tdoc_libelle'] 	
	//	$data['duree_pret']
	//	$data['tdoc_codage_import']
	//	$data['tdoc_owner']

	global $dbh;

	// check sur le type de  la variable pass�e en param�tre
	if(!sizeof($data) || !is_array($data)) {
		// si ce n'est pas un tableau ou un tableau vide, on retourne 0
		return 0;
		}
	// check sur les �l�ments du tableau
	$long_maxi = mysql_field_len(mysql_query("SELECT tdoc_libelle FROM docs_type limit 1"),0);
	$data['tdoc_libelle'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['tdoc_libelle']))),0,$long_maxi));
	$long_maxi = mysql_field_len(mysql_query("SELECT tdoc_codage_import FROM docs_type limit 1"),0);
	$data['tdoc_codage_import'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['tdoc_codage_import']))),0,$long_maxi));

	if($data['tdoc_owner']=="") $data['tdoc_owner'] = 0;
	if($data['tdoc_libelle']=="") return 0;
	/* tdoc_codage_import est obligatoire si tdoc_owner != 0 */
	//if(($data['tdoc_owner']!=0) && ($data['tdoc_codage_import']=="")) return 0;
	
	// pr�paration de la requ�te
	$key0 = addslashes($data['tdoc_libelle']);
	$key1 = addslashes($data['tdoc_codage_import']);
	$key2 = $data['tdoc_owner'];
	
	/* v�rification que le type doc existe */
	$query = "SELECT idtyp_doc FROM docs_type WHERE tdoc_codage_import='${key1}' and tdoc_owner = '${key2}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT docs_type ".$query);
	$docs_type  = mysql_fetch_object($result);

	/* le type de doc existe, on retourne l'ID */
	if($docs_type->idtyp_doc) return $docs_type->idtyp_doc;

	// id non-r�cup�r�e, il faut cr�er la forme.
	/* une petite valeur par d�faut */
	if ($data['duree_pret']=="") $data['duree_pret']=0;
	
	$query  = "INSERT INTO docs_type SET ";
	$query .= "tdoc_libelle='".$key0."', ";
	$query .= "duree_pret='".$data['duree_pret']."', ";
	$query .= "tdoc_codage_import='".$key1."', ";
	$query .= "tdoc_owner='".$key2."' ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into docs_type ".$query);

	return mysql_insert_id($dbh);

	} /* fin m�thode import */

/* une fonction pour g�n�rer des combo Box 
   param�tres :
	$selected : l'�l�ment s�lection� le cas �ch�ant
   retourne une chaine de caract�res contenant l'objet complet */
static function gen_combo_box ( $selected ) {
	global $msg;
	$requete="select idtyp_doc, tdoc_libelle from docs_type order by tdoc_libelle ";
	$champ_code="idtyp_doc";
	$champ_info="tdoc_libelle";
	$nom="book_doctype_id";
	$on_change="";
	$liste_vide_code="0";
	$liste_vide_info=$msg['class_typdoc'];
	$option_premier_code="";
	$option_premier_info="";
	$gen_liste_str="";
	$resultat_liste=mysql_query($requete) or die (mysql_error()." ".$requete);
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


} /* fin de d�finition de la classe */

} /* fin de d�laration */


