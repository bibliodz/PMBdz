<?php
// +----------------------------------------------------------------------------------------+
// � 2002-2006 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +----------------------------------------------------------------------------------------+
// $Id: index.php,v 1.9 2013-04-04 09:44:23 mbertin Exp $

//enregistrement des variables get et post en variables globales
error_reporting(E_ERROR);
require_once ("../includes/global_vars.inc.php");  
//d�finition du frameset
echo "<HTML>
<HEAD>
	<TITLE>Documentation PMB</TITLE>
</HEAD>";

if(!isset($lang) || !$lang){
	$lang="fr_FR";
}

//affichage ou non : pas de traduction dans la langue d�sir�e
if ($lang=="fr_FR") {
	echo "
		<FRAMESET ROWS='0%,*' border=0 frameborder=0 framespacing=0>
			<FRAME>
		";
	$doc_directory="documentation/fr_FR";
} else {
	if ($lang=="en_US") $lang="en_UK";
	$doc_directory="documentation/".$lang;
	if (!is_dir($doc_directory)) {
		//il n'y a qu'un r�pertoire pour la doc
		$lang="fr_FR";
		$doc_directory="documentation/fr_FR";
	}
	echo "<HTML>
			<HEAD>
			<TITLE>Documentation PMB</TITLE>
			</HEAD>
			<FRAMESET ROWS='40,*'>
			<FRAME SRC='missing_trans.html'>";
}
if(!is_dir($doc_directory)) {
	print "	<FRAME SRC='doc_install.html"; 
} else {
	//affichage de la page de doc correspondante
	print "	<FRAME SRC='".$doc_directory."/";
	$doc_correspondance="documentation/$lang/correspondance.php";
	include($doc_correspondance);			
}

//fin du frame affichant la doc correspondant aux infos post�es
echo "' NAME='main'>";        
//fermeture du frameset
echo "
	</FRAMESET>
	</HTML>";
        
?>