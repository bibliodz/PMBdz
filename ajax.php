<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax.php,v 1.22 2014-01-07 10:16:16 arenou Exp $
/*
Mode d'emploi des transactions client - serveur utilisant les requettes Ajax.
Cette technique permet d'interroger le serveur dynamiquement sans recharger toute la page.

Une transaction s'effectue en envoyant une commande via un script javascript dans le formulaire d'une page html.
Ce script est cod� sous forme d'une classe javascript dans le fichier /javascript/http_request.js

Usage et exemple d'envoie d'une requette cot� client.
	Dans cet exemple, on teste la validit�e de la date avant de commiter le formulaire.
	....
		// Inclusion du script Ajax
		<script type='text/javascript' src='./javascript/http_request.js'></script>
		<script language="JavaScript">
	
		function CheckDataAjax() {
			// R�cup�ration de la valeur de l'objet 'DirectDate'
			var DirectDate = document.Cal.DirectDate.value;
			// Construction de la requette 
			var url= "./ajax.php?module=ajax&categ=misc&fname=verifdate&p1=" + DirectDate;
			// On initialise la classe:
			var test_date = new http_request();
			// Ex�cution de la requette
			if(test_date.request(url)){
				// Il y a une erreur. Afficher le message retourn�
				alert ( test_date.get_text() );			
			}else { 
				// La date est valide, on commit
				document.getElementById('date_directe').value = DirectDate;
				return 1;	
			}
		}
		</script>
			
		<form name="Cal" id="Cal" method='post' action='./test.php'>
		<input type='text' name='DirectDate' size=10 value='10/08/2007'>
		<input type='button' value="Send" onClick="if(CheckDataAjax()) submit();">
		</form>
	....
	
Explication du code:
	Construction de la requette 'url': 
	url= "./ajax.php?module=ajax&categ=misc&fname=verifdate&p1=" + DirectDate;
	Les parametres module,categ permettent de parser la commande au bon endroit dans 
	la structure de codage de PMB
	
	Plusieurs param�tres optionnels de la fonction 'request' permettent de faire des POST
	en mode synchrone ou pas.
	Pour plus de pr�cisions, voir l'entete de la proc�dure dans: ./javascript/http_request.js
	
Cot� serveur, on se rend dans le bon module, grace aux param�tres pass�s dans 'url'.
Dans l'exemple, module=ajax , categ=misc .
Ainsi le fichier /ajax/ajax_main.inc.php parse la commande � /pmb/ajax/misc/misc.inc.php

Cette m�todologie devra �tre respect�e pour chaque requette Ajax, afin de localiser le traitement 
facilement et de r�utiliser au mieux le code existant du module

*/

$base_path = ".";
$base_noheader = 1;
$base_nobody = 1;  
$base_nodojo = 1;  
$clean_pret_tmp=1;

//avant l'inclusion faudrait peut-�tre s'occuper de la gestion des droits dans les diff�rentes requetes...
switch($_GET['module']){
	case "cms" :
		$base_auth = "CMS_AUTH";
		break;	
	case "autorites" :
		$base_auth = "AUTORITES_AUTH";
		break;
	case 'catalog':
		$base_auth = "CATALOGAGE_AUTH";
	break;
	case 'circ':
		$base_auth = "CIRCULATION_AUTH";
	break;		
	case 'admin':
		$base_auth = "ADMINISTRATION_AUTH";
	break;
	case 'demandes':
		$base_auth = "DEMANDES_AUTH";
	break;	
	case 'acquisition':
		$base_auth = "ACQUISITION_AUTH";
	break;
	case 'fichier':
		$base_auth = "FICHES_AUTH";
	break;
	case "edit" :
		$base_auth = "EDIT_AUTH";
	break;
}

require_once ($base_path . "/includes/init.inc.php");

if(!SESSrights) exit;

// inclusion des fonctions utiles pour renvoyer la r�ponse � la requette recu 
require_once ($base_path . "/includes/ajax.inc.php");

/*	
 * Parse la commande Ajax du client vers 
 * $module est pass� dans l'url,envoy� par http_send_request, in http_request.js script file
 * les valeurs envoy�es dans les requ�tes en ajax du client vers le serveur sont encod�es
 * exclusivement en utf-8 donc d�codage de toutes les variables envoy�es si n�cessaire
*/


function utf8_decode_pmb(&$var) {
	if(is_array($var)){
		foreach($var as $key => $val) {
			utf8_decode_pmb($var[$key]);
		}
	}
	else $var=utf8_decode($var);
}

function array_uft8_decode($tab){
	foreach($tab as $key => $val) {
		if(is_array($val)){
			$tab[$key] = array_uft8_decode($val);
		}else{
			$tab[$key] = utf8_decode($val);
		}
	}
	return $tab;
}
if (strtoupper($charset)!="UTF-8") {
	$t=array_keys($_POST);	
	foreach($t as $v) {
		global $$v;
		utf8_decode_pmb($$v);
	}
	$t=array_keys($_GET);	
	foreach($t as $v) {
		global $$v;	
		utf8_decode_pmb($$v);
	}
	//On d�code aussi les POST et les GET en plus de les mettre en global 
	$_POST = array_uft8_decode($_POST);
	$_GET = array_uft8_decode($_GET);
}

$main_file="./$module/ajax_main.inc.php";
switch($module) {
	case 'ajax':
		include($main_file);
	break;
	case 'autorites':		
		include($main_file);
	break;		
	case 'catalog':
		include($main_file);
	break;
	case 'circ':
		include($main_file);
	break;		
	case 'admin':
		include($main_file);
	break;
	case 'demandes':
		include($main_file);
	break;	
	case 'acquisition':
		include($main_file);
	break;
	case 'fichier':
		include($main_file);
	break;
	case 'cms':
		include($main_file);
	break;
	case "edit" :
		include($main_file);
		break;
	case "dsi" :
		include($main_file);
		break;
	case "dashboard" :
		include($main_file);
		break;
	default:
		//tbd
	break;	
}		
?>