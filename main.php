<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.php,v 1.31 2014-01-07 16:17:16 arenou Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "";  
$base_title = "\$msg[308]";
$base_noheader=1;
$base_nocheck=1;
require_once ("$base_path/includes/init.inc.php");  

//Est-on d�j� authentifi� ?
if (!checkUser('PhpMyBibli')) {
	//V�rification que l'utilisateur existe dans PMB
	$query = "SELECT userid,username FROM users WHERE username='$user'";
	$result = mysql_query($query, $dbh);
	if (mysql_num_rows($result)) {
		//R�cup�ration du mot de passe
		$dbuser=mysql_fetch_object($result);
		
		//Autentification externe si n�c�ssaire
		if ((file_exists("$include_path/external_admin_auth.inc.php"))&&($dbuser->userid!=1)) {
			include("$include_path/external_admin_auth.inc.php");
		} else {
			// on checke si l'utilisateur existe et si le mot de passe est OK
			$query = "SELECT count(1) FROM users WHERE username='$user' AND pwd=password('$password') ";
			$result = mysql_query($query, $dbh);
			$valid_user = mysql_result($result, 0, 0);
		}
	}
} else 
	$valid_user=2;

if(!$valid_user) {
	header("Location: index.php?login_error=1");
} else {
	if ($valid_user==1)
		startSession('PhpMyBibli', $user, $database);
}	

if(SESSlang) {
	$lang=SESSlang;
	$helpdir = $lang;
}

// localisation (fichier XML)
$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
$messages->analyser();
$msg = $messages->table;
require("$include_path/templates/common.tpl.php");  

if ((!$param_licence)||($pmb_bdd_version!=$pmb_version_database_as_it_should_be)||($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion)) {
	require_once("$include_path/templates/main.tpl.php");
	print $std_header;
	print $menu_bar;

	print $extra;
	if($use_shortcuts) {
		include("$include_path/shortcuts/circ.sht");
	}
	print $main_layout;
	
	if ($pmb_bdd_version!=$pmb_version_database_as_it_should_be) {
		echo "<h1>".$msg["pmb_v_db_pas_a_jour"]."</h1>";  
		echo "<h1>".$msg[1803]."<font color=red>".$pmb_bdd_version."</font></h1>";  
		echo "<h1>".$msg[pmb_v_db_as_it_should_be]."<font color=red>".$pmb_version_database_as_it_should_be."</font></h1>";
		echo "<a href='./admin.php?categ=alter&sub='>".$msg["pmb_v_db_mettre_a_jour"]."</a>";  
		echo "<SCRIPT>alert(\"".$msg["pmb_v_db_pas_a_jour"]."\\n".$pmb_version_database_as_it_should_be." <> ".$pmb_bdd_version."\");</SCRIPT>";
	}  
	
	if ($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion) {
		echo "<h1>Minor changes in database in progress...</h1>";
		include("./admin/misc/addon.inc.php"); 
		echo "<h1>Changes applied in database.</h1>";
	}  
	
	if (!$param_licence) {
		include("$base_path/resume_licence.inc.php");
		print $PMB_texte_licence ;
	}
	
	print $main_layout_end;
	print $footer;

	mysql_close($dbh);
	exit ;
}

if ($ret_url) {
	print "<SCRIPT>document.location=\"$ret_url\";</SCRIPT>"; 
	exit ;
} 

//chargement de la premi�re page
require_once($include_path."/misc.inc.php");

go_first_tab();

mysql_close($dbh);
