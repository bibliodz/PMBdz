<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: user_pwd.inc.php,v 1.10 2013-06-14 07:29:24 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$requete = "SELECT username FROM users WHERE userid='$id' LIMIT 1 ";
$res = mysql_query($requete, $dbh);
$row = $row=mysql_fetch_row($res);
$myUser = $row[0];

if(empty($form_pwd)) {
	echo window_title($database_window_title.$msg[2]." $myUser".$msg[1003].$msg[1001]);
	$admin_npass_form = str_replace('!!id!!', $id, $admin_npass_form);
	$admin_npass_form = str_replace('!!myUser!!', $myUser, $admin_npass_form);
	print $admin_npass_form;
	echo form_focus('userform', 'form_pwd');
} else {
	if($form_pwd==$form_pwd2 && !empty($form_pwd)) {		
		$requete = "UPDATE users SET last_updated_dt=curdate(),pwd=password('$form_pwd'), user_digest = '".md5($myUser.":".md5($pmb_url_base).":".$form_pwd)."' WHERE userid=$id ";
		$res = mysql_query($requete, $dbh);
	}
	show_users($dbh);
	echo window_title("{$msg[7]}.$msg[25]");
}

