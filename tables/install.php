<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: install.php,v 1.24 2013-04-08 14:56:08 mbertin Exp $

// plus rien ici : reprise d'un script d'une autre install
if(preg_match('/noinstall\.php/', $_SERVER['REQUEST_URI'])) {
	include('../includes/forbidden.inc.php'); forbidden();
	}

include('../includes/config.inc.php');

$sel_lang="
	<html>
	<head>
		<title>PMB setup</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
		<style type=\"text/css\">
			body {
				font-family: \"Verdana\", \"Arial\", sans-serif;
				background: #eeeae4;
				text-align: center;
			}
			.bouton {
				color: #fff;
				font-size: 12pt;
				font-weight: bold;
				border: 1px outset #D47800;
				background-color: #5483AC;
			}

			.bouton:hover {
				border-style: inset;
				border: 1px solid #ED8600;
				background-color: #7DC2FF;
			}
		</style>
	</head>
	<body>
	<center>
	<form method='post' action=install.php>
	<h1><img src='../images/logo_pmb_rouge.png'>&nbsp;&nbsp;install</h1>
	<br />
	<h3>Encodage de caract�re (charset) :</h3><br />
	<ul> 
		<li>
    		<input type=\"radio\" name=\"charset\" checked value=\"iso-8859-1\"/>iso-8859-1 (Uniquement les caract�res latins)
   		</li>
     	<li>
     		<input type=\"radio\" name=\"charset\" value=\"utf-8\"/>utf-8 (Tous alphabets, a choisir pour l'Arabe en plus de l'installation en fran�ais) 
    	</li>
	</ul>
	<h3>Langue:</h3><br />
	<table>
		<tr>
			<td><input type='submit' class='bouton' name='submit' value='Fran�ais'></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type='submit' class='bouton' name='submit' value='Italiano'></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type='submit' class='bouton' name='submit' value='English'></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type='submit' class='bouton' name='submit' value='Catal�'></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type='submit' class='bouton' name='submit' value='Espa�ol'></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type='submit' class='bouton' name='submit' value='Portuguese'></td>
		<tr>
	</table>

	</form>
	</center>
	</body>
	</html>
";

switch ($_REQUEST['submit']){
	case 'Italiano':
		$lang='it';
		break;
	case 'Fran�ais':
		$lang='fr';
		break;
	case 'English':
		$lang='en';
		break;
	case 'Catal�':
		$lang='ca';
		break;
	case 'Espa�ol':
		$lang='es';
		break;
	case 'Portuguese':
		$lang='pt';
		break;
	default:
		print $sel_lang;
}

$charset = $_REQUEST['charset'];
if ($lang && $lang != $default_lang){
	include("./$lang/install_inc.php");
	print $header;
	print $body;
	print $footer;
}
	

?>

