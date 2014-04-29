<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docs_codestat.class.php,v 1.7 2013-04-11 08:08:11 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'docs_codestat'

if ( ! defined( 'DOCSCODESTAT_CLASS' ) ) {
  define( 'DOCSCODESTAT_CLASS', 1 );

class docs_codestat {

/* ---------------------------------------------------------------
		propri�t�s de la classe
   --------------------------------------------------------------- */

var $id=0;
var $libelle='';
var $statisdoc_codage_import="";
var $statisdoc_owner=0;

/* ---------------------------------------------------------------
		docs_codestat($id) : constructeur
   --------------------------------------------------------------- */

function docs_codestat($id=0) {
	if($id) {
		/* on cherche � atteindre un code statistique existant */
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

	/* r�cup�ration des informations du code statistique */

	$requete = 'SELECT * FROM docs_codestat WHERE idcode='.$this->id.' LIMIT 1;';
	$result = @mysql_query($requete, $dbh);
	if(!mysql_num_rows($result)) return;
		
	$data = mysql_fetch_object($result);
	$this->id = $data->idcode;		
	$this->libelle = $data->codestat_libelle;
	$this->statisdoc_codage_import = $data->statisdoc_codage_import;
	$this->statisdoc_owner = $data->statisdoc_owner;

	}

// ---------------------------------------------------------------
//		import() : import d'un code statistique de document
// ---------------------------------------------------------------
function import($data) {

	// cette m�thode prend en entr�e un tableau constitu� des informations suivantes :
	//	$data['codestat_libelle'] 	
	//	$data['statisdoc_codage_import']
	//	$data['statisdoc_owner']

	global $dbh;

	// check sur le type de la variable pass�e en param�tre
	if(!sizeof($data) || !is_array($data)) {
		// si ce n'est pas un tableau ou un tableau vide, on retourne 0
		return 0;
		}
	// check sur les �l�ments du tableau
	
	$long_maxi = mysql_field_len(mysql_query("SELECT codestat_libelle FROM docs_codestat limit 1"),0);
	$data['codestat_libelle'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['codestat_libelle']))),0,$long_maxi));
	$long_maxi = mysql_field_len(mysql_query("SELECT statisdoc_codage_import FROM docs_codestat limit 1"),0);
	$data['statisdoc_codage_import'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['statisdoc_codage_import']))),0,$long_maxi));

	if($data['statisdoc_owner']=="") $data['statisdoc_owner'] = 0;
	if($data['codestat_libelle']=="") return 0;
	/* statisdoc_codage_import est obligatoire si statisdoc_owner != 0 */
	// comment� depuis le choix de quel codage rec995 on r�cup�re if(($data['statisdoc_owner']!=0) && ($data['statisdoc_codage_import']=="")) return 0;
	
	// pr�paration de la requ�te
	$key0 = addslashes($data['codestat_libelle']);
	$key1 = addslashes($data['statisdoc_codage_import']);
	$key2 = $data['statisdoc_owner'];
	
	/* v�rification que le code statistique existe */
	$query = "SELECT idcode FROM docs_codestat WHERE statisdoc_codage_import='${key1}' and statisdoc_owner = '${key2}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT docs_codestat ".$query);
	$docs_codestat  = mysql_fetch_object($result);

	/* le code statistique de doc existe, on retourne l'ID */
	if($docs_codestat->idcode) return $docs_codestat->idcode;

	// id non-r�cup�r�e, il faut cr�er la forme.
	
	$query  = "INSERT INTO docs_codestat SET ";
	$query .= "codestat_libelle='".$key0."', ";
	$query .= "statisdoc_codage_import='".$key1."', ";
	$query .= "statisdoc_owner='".$key2."' ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into docs_codestat ".$query);

	return mysql_insert_id($dbh);

	} /* fin m�thode import */

/* une fonction pour g�n�rer des combo Box 
   param�tres :
	$selected : l'�l�ment s�lection� le cas �ch�ant
   retourne une chaine de caract�res contenant l'objet complet */
static function gen_combo_box ( $selected ) {
	global $msg;
	$requete="select idcode, codestat_libelle from docs_codestat order by codestat_libelle ";
	$champ_code="idcode";
	$champ_info="codestat_libelle";
	$nom="book_codestat_id";
	$on_change="";
	$liste_vide_code="0";
	$liste_vide_info=$msg['class_codestat'];
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


