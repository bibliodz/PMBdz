<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// | creator : Eric ROBERT                                                    |
// | modified : ...                                                           |
// +-------------------------------------------------+
// $Id: z_progression_visible.php,v 1.9 2011-09-30 07:58:18 dgoron Exp $

// d�finition du minimum n�c�ssaire 
$base_path="../..";
$base_auth = "CATALOGAGE_AUTH";  
$base_title = "";    
require_once ("$base_path/includes/init.inc.php");  

// les requis par z_progression_visible.php ou ses sous modules
include("$include_path/isbn.inc.php");
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
include("$class_path/iso2709.class.php");
include("z3950_func.inc.php");
//print "<div id='contenu-frame'>";

print "
<div id='contenu-frame'>
<h1>$msg[z3950_progr_rech]</h1>
<!--
<br /><p align='center'>$msg[z3950_progr_rech_txt]</p>
-->
<table class='nobrd' width='100%' align='center'>";

print "
	<tr><td colspan=2 bgcolor='#FFFFCC'>
		<DIV ID='zframe1' style='background-color:#FFCC99' align='center'>
		".$msg['patientez']."
		<div id='joke' style='visibility:\"visible\";'><img src='../../images/slidbar.gif'></div>
		</DIV>
		</td>
	</tr>";

//
// On d�termine les Biblioth�ques s�lectionn�es
//

$recherche=mysql_query("select * from z_bib $selection_bib");
while ($resultat=mysql_fetch_array($recherche)) {
	$bib_id=$resultat["bib_id"];
	$nom_bib=$resultat["bib_nom"];
	print "
		<tr>
			<td bgcolor='#FFFFCC' width='30%'>$nom_bib</td>
			<td><DIV ID='z$bib_id' style='background-color:#FFCC99' align='center'>$msg[z3950_essai_cnx]</DIV></td>
		</tr>";
	}

print "
</table></div>";
?>
