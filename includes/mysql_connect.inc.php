<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mysql_connect.inc.php,v 1.10 2009-05-16 11:20:27 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// attention : on oublie pas le mysql_close() dans le script PHP appelant !

	// connection MySQL et s�lection base
	// aucun param�tre n'est obligatoire :

	// connection_mysql() fonctionne et utilise les variables des define
	// dans ce cas, les erreurs sont renvoy�es

// connection_mysql(1, 'base', 1, 1) ->
// 1er param�tre (0 ou 1) : affiche un message d'erreur et die
// si erreur � la connection. Mettre � 0 pour d�sactiver
// 2nd param�tre : nom de la base de donn�e
// si vide, on utilise la variable d�finie dans DATA_BASE
// 3�me param�tre (0 ou 1) : active ou d�sactive la s�lection
// d'une base (y compris celle d�finie dans DATA_BASE)
// 4�me param�tre : active ou d�sactive le retour d'erreur qui coupe
// le script quand la base n'est pas valide

function connection_mysql($er_connec=1, $my_bd='', $bd=1, $er_bd=1) {
	global $__erreur_cnx_base__, $pmb_nb_documents, $pmb_opac_url, $pmb_bdd_version;
	global $charset, $SQL_MOTOR_TYPE;
	$my_connec = @mysql_connect(SQL_SERVER, USER_NAME, USER_PASS);
	if($my_connec==0 && $er_connec==1) {
		$__erreur_cnx_base__ =  'erreur '.mysql_errno().' : '.mysql_error().'<br />';
		return 0 ;
		}
	if($bd) {
		$my_bd == '' ? $my_bd=DATA_BASE : $my_bd;
		if( mysql_select_db($my_bd)==0 && $er_bd==1 ) {
			$__erreur_cnx_base__ = 'erreur '.mysql_errno().' : '.mysql_error().'<br />';
			return 0 ;
			}
		}
	$pmb_nb_documents=(@mysql_result(mysql_query("select count(*) from notices"),0,0))*1;
	$pmb_opac_url=(@mysql_result(mysql_query("select valeur_param from parametres where type_param='pmb' and sstype_param='opac_url'"),0,0));
	$pmb_bdd_version=(@mysql_result(mysql_query("select valeur_param from parametres where type_param='pmb' and sstype_param='bdd_version'"),0,0));

	if ($charset=='utf-8') mysql_query("set names utf8 ", $my_connec);
	else mysql_query("set names latin1 ", $my_connec);
	
	if ($SQL_MOTOR_TYPE) mysql_query("set storage_engine=$SQL_MOTOR_TYPE", $my_connec);
	return $my_connec;
}

// fonction de gestion des erreurs de connection.
// my_error(); ou my_error(1); affichent le num�ro et la
// description de la derni�re erreur MySQL.
// $erreur = my_error(0) stocke dans $erreur la cha�ne
// contenant le num�ro et la description de la derni�re
// erreur MySQL.
function my_error($echo=1) {
	if(!mysql_errno()) return "";
	$erreur = 'erreur '.mysql_errno().' : '.mysql_error().'<br />';
	if($echo) echo $erreur;
		else {
			trigger_error($erreur, E_USER_ERROR);
			return $erreur;
			}
	}
