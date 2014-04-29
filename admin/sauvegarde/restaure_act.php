<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: restaure_act.php,v 1.7 2012-04-24 15:23:00 dgoron Exp $

//Restauration d'un jeu

//Est-ce une restauration critique (dans ce cas, pas de v�rification d'utilisateur)?

if ($_POST["critical"]) {
	include("emergency/messages_env_ract.inc.php");
} else {
	$base_path="../..";
    $base_auth="SAUV_AUTH|ADMINISTRATION_AUTH";
    $base_title="\$msg[sauv_misc_ract_title]";
    require($base_path."/includes/init.inc.php");
}

//R�cup�ration du nom de fichier

$tFilename=explode("/",$filename);
$file_name=$tFilename[count($tFilename)-1];

$user=$db_user;
$password=$db_password;



require_once("../../classes/crypt.class.php");

function abort($message) {
	echo "<script>alert(\"$message\"); history.go(-1);</script>";
	exit();
}

print "<div id=\"contenu-frame\">\n";
echo "<center><h1>".sprintf($msg["sauv_misc_restaure"],$file_name)."</h1></center>\n";

if ($critical==1) {
	mysql_connect($host,$user,$password) or abort($msg["sauv_misc_ract_cant_connect"]);
	mysql_select_db($db) or abort(sprintf($msg["sauv_misc_ract_db_dont_exists"],$db));
}

//R�cup�ration de la partie data
$f=fopen($filename,"r") or abort($msg["sauv_misc_ract_cant_open_file"]);
$line=fgets($f,4096);
$line=rtrim($line);
while ((!feof($f))&&($line!="#data-section")) {
	$line=fgets($f,4096);
	$line=rtrim($line);
}

if ($line!="#data-section") abort($msg["sauv_misc_ract_no_sauv"]);

/*$datas=fread($f,filesize($filename));

fclose($f);

//Si crypt�
if ($crypt==1) {
	echo "<b>".$msg["sauv_misc_ract_decryt_msg"]."</b><br />";
	flush();
	$c=new Crypt(md5($phrase1),md5($phrase2));
	$sign=substr($datas,0,8);
	$dSign=$c->getDecrypt($sign);
	if ($dSign!="PMBCrypt") abort($msg["sauv_misc_ract_bad_keys"]);
	$datas=substr($datas,8);
	$datas=$c->getDecrypt($datas);
}
*/

//Copie des donn�es dans un fichier temporaire
$tempfile="temp_restaure";
$tempfiledest="../backup/backups/temp_restaure.sql";

//Si compress�
if ($compress==1) {
	if ($decompress_type=="internal") $tempfile.=".bz2"; else $tempfile.=".sql.".$decompress_ext;
} else $tempfile.=".sql";

$tempfile="../backup/backups/".$tempfile;
$ftemp=fopen($tempfile,"w+") or abort($msg["sauv_misc_ract_create"]);

while (!feof($f)) {
	fwrite($ftemp,fread($f,4096));
}

//fwrite($ftemp,$datas);
fclose($ftemp);
fclose($f);

//D�compression �ventuelle
if ($compress==1) {
	echo "<b>".$msg["sauv_misc_ract_decompress"]."</b><br />";
	flush();
	if ($decompress_type=="external") {
		$decompress=str_replace("%s",$tempfile,$decompress);
		$decompress=str_replace("%sd",$tempfiledest,$decompress);
		exec($decompress);
	} else {
		$ftempin=bzopen($tempfile,"r") or abort($msg["sauv_misc_ract_not_bz2"]);
		$ftempout=fopen($tempfiledest,"w+") or abort($msg["sauv_misc_ract_create"]);
		while (!feof($ftempin)) {
			$datas=bzread($ftempin,2048);
			fwrite($ftempout,$datas);
		}
		bzclose($ftempin);
		fclose($ftempout);
		@unlink($tempfile);
	}
}

//Application des requ�tes
echo "<b>".$msg["sauv_misc_ract_restaure_tables"]."</b><br /><br />";
if (!is_array($tables)) $tables=array();
$fsql=fopen($tempfiledest,"r") or abort($msg["sauv_misc_ract_open_failed"]);
$mod_query=0;
while (!feof($fsql)) {
	$line="";
	while ((substr($line,strlen($line)-1,1)!="\n")&&(!feof($fsql))) { 
		$line.=fgets($fsql,4096);
	}
	$line=rtrim($line);
	if ($line!="") {
		if (substr($line,0,1)=="#") {
			if (($currentTable!="")&&($mod_query==1)) { echo sprintf($msg["sauv_misc_ract_restaured_t"],$currentTable)."<br />"; flush(); }
			$currentTable=substr($line,1);
			$as=array_search($currentTable,$tables);
			if (($as!==false)&&($as!==null)) { $mod_query=1; echo sprintf($msg["sauv_misc_ract_start_restaure"],$currentTable)."<br />"; } else { $mod_query=0; echo sprintf($msg["sauv_misc_ract_ignore"],$currentTable)."<br />"; }
			flush();
		} else {
			if ($mod_query==1) { mysql_query($line) or abort(sprintf($msg["sauv_misc_ract_invalid_request"],$line)); }
		}
	}
}
fclose($fsql);
@unlink($tempfiledest);

/* Succeed - Gestion du cas particulier :
 * Derni�re sauvegarde effectu�e <=> sauv_log_succeed valoris� � 1 apr�s sauvegarde
 * Lors de la restauration, on r�cup�re la valeur 0, enregistr�e avant la fin de la sauvegarde.
 */
$requete="update sauv_log set sauv_log_succeed=1 where sauv_log_id=".$logid;
@mysql_query($requete);

echo "<h2>".$msg["sauv_misc_ract_correct"]."</h2>";
echo "</div>";
if ($critical==1) unlink($filename);
?>