<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pclass.inc.php,v 1.4 2013-11-12 15:43:44 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// inclusions diverses
include("$include_path/templates/pclass.tpl.php");

$base_url = "./autorites.php?categ=indexint&sub=";
$action = $base_url."pclass_form&id_pclass=0";

//affichage de la liste pclassement

$q = "select id_pclass,name_pclass,typedoc from pclassement ";
$r = mysql_query($q, $dbh);
if (mysql_num_rows($r) == 0) {
	$browser_content = $msg[4051];
	affiche();
	exit;
}

$odd_even = 1;
while ($row = mysql_fetch_object($r)) {
	if ($odd_even==0) {
		$browser_content .= "	<tr class='odd'>";
		$odd_even=1;
	} else {
		$browser_content .= "	<tr class='even'>";
		$odd_even=0;
	}
	$browser_content .= "<td>";
	$browser_content .= "<a href='".$base_url."pclass_form&id_pclass=".$row->id_pclass."' >".$row->name_pclass."</a>";
	$browser_content .= "</td>";
	$browser_content .= "<td>";
	$browser_content .= $row->typedoc;
	$browser_content .= "</td></tr>";
}		

affiche();
exit;

// cr�ation du tableau � partir du template et affichage
function affiche() {
	
	global $browser;
	global $browser_content;
	global $action;
	global $browser_header;

	$browser = str_replace('!!browser_header!!', $browser_header, $browser);
	$browser = str_replace('!!browser_content!!', $browser_content, $browser);
	$browser = str_replace('!!action!!', $action, $browser);	
	print pmb_bidi($browser);
}


?>