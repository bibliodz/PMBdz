<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docs_statut.class.php,v 1.6 2013-04-11 08:08:11 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'docs_statut'

if ( ! defined( 'DOCSSTATUT_CLASS' ) ) {
  define( 'DOCSSTATUT_CLASS', 1 );

class docs_statut {

/* ---------------------------------------------------------------
		propri�t�s de la classe
   --------------------------------------------------------------- */

var $id=0;
var $libelle='';
var $pret_flag='';
var $statusdoc_codage_import="";
var $statusdoc_owner=0;

/* ---------------------------------------------------------------
		docs_statut($id) : constructeur
   --------------------------------------------------------------- */
function docs_statut($id=0) {
	if($id) {
		/* on cherche � atteindre un statut existant */
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

	/* r�cup�ration des informations du statut */

	$requete = 'SELECT * FROM docs_statut WHERE idstatut='.$this->id.' LIMIT 1;';
	$result = @mysql_query($requete, $dbh);
	if(!mysql_num_rows($result)) return;
		
	$data = mysql_fetch_object($result);
	$this->id = $data->idstatut;		
	$this->libelle = $data->statut_libelle;
	$this->pret_flag = $data->pret_flag;
	$this->statusdoc_codage_import = $data->statusdoc_codage_import;
	$this->statusdoc_owner = $data->statusdoc_owner;
	}

// ---------------------------------------------------------------
//		import() : import d'un statut de document
// ---------------------------------------------------------------
function import($data) {

	// cette m�thode prend en entr�e un tableau constitu� des informations suivantes :
	//	$data['statut_libelle'] 	
	//	$data['pret_flag']
	//	$data['statusdoc_codage_import']
	//	$data['statusdoc_owner']

	global $dbh;

	// check sur le type de  la variable pass�e en param�tre
	if(!sizeof($data) || !is_array($data)) {
		// si ce n'est pas un tableau ou un tableau vide, on retourne 0
		return 0;
		}
	// check sur les �l�ments du tableau
	$long_maxi = mysql_field_len(mysql_query("SELECT statut_libelle FROM docs_statut limit 1"),0);
	$data['statut_libelle'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['statut_libelle']))),0,$long_maxi));
	$long_maxi = mysql_field_len(mysql_query("SELECT statusdoc_codage_import FROM docs_statut limit 1"),0);
	$data['statusdoc_codage_import'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['statusdoc_codage_import']))),0,$long_maxi));

	if($data['statusdoc_owner']=="") $data['statusdoc_owner'] = 0;
	if($data['statut_libelle']=="") return 0;
	/* statusdoc_codage_import est obligatoire si statusdoc_owner != 0 */
	if(($data['statusdoc_owner']!=0) && ($data['statusdoc_codage_import']=="")) return 0;
	
	// pr�paration de la requ�te
	$key0 = addslashes($data['statut_libelle']);
	$key1 = addslashes($data['statusdoc_codage_import']);
	$key2 = $data['statusdoc_owner'];
	
	/* v�rification que le statut existe */
	$query = "SELECT idstatut FROM docs_statut WHERE statusdoc_codage_import='${key1}' and statusdoc_owner = '${key2}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT docs_statut ".$query);
	$docs_statut  = mysql_fetch_object($result);

	/* le statut de doc existe, on retourne l'ID */
	if($docs_statut->idstatut) return $docs_statut->idstatut;

	// id non-r�cup�r�e, il faut cr�er la forme.
	/* une petite valeur par d�faut */
	if ($data['pret_flag']=="") $data['pret_flag']=1;
	
	$query  = "INSERT INTO docs_statut SET ";
	$query .= "statut_libelle='".$key0."', ";
	$query .= "pret_flag='".$data['pret_flag']."', ";
	$query .= "statusdoc_codage_import='".$key1."', ";
	$query .= "statusdoc_owner='".$key2."' ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into docs_statut ".$query);

	return mysql_insert_id($dbh);

	} /* fin m�thode import */

/* une fonction pour g�n�rer des combo Box 
   param�tres :
	$selected : l'�l�ment s�lection� le cas �ch�ant
   retourne une chaine de caract�res contenant l'objet complet */

static function gen_combo_box ( $selected ) {

	global $msg;

	$requete="select idstatut, statut_libelle from docs_statut order by statut_libelle ";
	$champ_code="idstatut";
	$champ_info="statut_libelle";
	$nom="book_statut_id";
	$on_change="";
	$liste_vide_code="0";
	$liste_vide_info=$msg['class_statut'];
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



} /* fin de d�finition de la classe */

} /* fin de d�laration */


