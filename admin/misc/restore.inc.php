<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: restore.inc.php,v 1.8 2013-04-11 08:02:52 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

print "<table >";
print "<tr><th>";
print "$msg[532]";
print "</th></tr><td>";

$tmp_dump = "./tables/tmp_dump";

if(file_exists($tmp_dump))
	unlink($tmp_dump);

if($file)
{
	// proc�dure de restauration
	$file = urldecode($file);
	$fp = @fopen($file, 'r');
	if($fp) {

		$sql_dump = fread($fp, filesize($file));
		fclose($fp);

		// on enl�ve les commentaires et lignes vides

		$sql_dump = preg_replace("/#.*?\n/msi", "", $sql_dump);

		// �clate le truc en requ�tes distinctes

		$req_table = explode(";", $sql_dump);

		$error_flag = FALSE;

		while(list($cle,$valeur)=each($req_table)) {
			// ex�cution du lot de requ�tes

			// on nettoie les retours chariot

			$valeur = preg_replace("/\n/m", "", $valeur);
			if($valeur) {
				$result = mysql_query($valeur, $dbh);
				if(!$result) {
					print "<font color=#ff0000><strong>".$msg[540]."</strong></font> ".$msg['admin_misc_requete']." $cle&nbsp;: $valeur<hr />";
					$error_flag = TRUE;
				}
			}
		}


		if(!$error_flag) {
			print "<strong>$msg[533]</strong>";
		} else {
			print "<strong>$msg[535]</strong>";
		}
	}
} else {
	print "<strong>$msg[534]&nbsp;:</strong><br /><br />";
    /* affichage des fichiers du r�pertoire */

	$sav_path = "./tables/";

    /* ouverture du r�pertoire courant */

    $handle = @opendir($sav_path);

    /* lecture des entr�es du r�pertoire */
	if($handle) {
		print "<table cellspacing='3'>";
		while($file = readdir($handle)) {
			$sav = $sav_path.$file;
			if (is_file("$sav") && preg_match("/sql$/si", $sav)) {
				$symbol = "<img src='./images/texte_ico.gif'>";
				$fdate = date("d/m/Y", filemtime($sav));
				$fheure = date("H:i:s", filemtime($sav));

				print "<tr>
							<td>$symbol</td>
							<td><strong>$file</strong></td>
							<td>$fdate</td>
							<td>$fheure</td>";
				print "<td>
						<a href='./admin.php?categ=misc&sub=restore&file=";
						print urlencode($sav);
						print "'>$msg[536]</a>
					</td></tr>";
			}
		}
		print "</table>";
		closedir($handle);
	}
}
?>
