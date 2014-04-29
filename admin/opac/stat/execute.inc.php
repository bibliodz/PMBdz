<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: execute.inc.php,v 1.5 2013-04-18 10:29:59 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path,$PMBuserid,$charset;//Comme ce script est appel� d'une fonction, il faut d�finir des globals
require_once ($class_path."/stat_query.class.php");

// include d'ex�cution d'une proc�dure

$requete = "SELECT * FROM statopac_request WHERE idproc=$id ";
$res = mysql_query($requete, $dbh);

$nbr_lignes = mysql_num_rows($res);
$urlbase = "./admin.php?categ=opac&sub=stat&section=view_list&act=final&id=$id";
if ($force_exec) $urlbase .= "&force_exec=$force_exec";

if($nbr_lignes) {

	// r�cup�ration du r�sultat
	$row = mysql_fetch_row($res);
	$idp = $row[0];
	$name = $row[1];
	if (!$code)
		$code = $row[2];
	$commentaire = $row[3];
	
	//on remplace VUE par el nom de la table dynamique associ�e
	$num_vue = stat_query::get_vue_associee($id);
	$code = str_replace('VUE()','statopac_vue_'.$num_vue,$code);
	print "<br>
		<h3>".htmlentities($msg["procs_execute"]." ".$name, ENT_QUOTES, $charset)."</h3>
		<br/>".htmlentities($commentaire, ENT_QUOTES, $charset)."<hr/>
			<input type='button' class='bouton' value='$msg[62]'  onClick='document.location=\"./admin.php?categ=opac&sub=stat&section=query&act=update_request&id_req=$id\"' />";
		if (($pmb_procs_force_execution && $force_exec) || (($PMBuserid == 1) && $force_exec)) {
			print "<input type='button' id='procs_button_exec' class='bouton' value='".htmlentities($msg["procs_force_exec"], ENT_QUOTES, $charset)."' onClick='document.location=\"./admin.php?categ=opac&sub=stat&section=view_list&act=exec_req&id_req=$id&force_exec=1\"' />";
		} else {
			print "<input type='button' id='procs_button_exec' class='bouton' value='$msg[708]' onClick='document.location=\"./admin.php?categ=opac&sub=stat&section=view_list&act=exec_req&id_req=$id\"' />";
		}
	print "<br />";
	$linetemp = explode(";", $code);
	for ($i=0;$i<count($linetemp);$i++) if (trim($linetemp[$i])) $line[]=trim($linetemp[$i]);
	while(list($cle, $valeur)= each($line)) {
		if($valeur) {
			// traitement des param�tres
			// traitement tri des colonnes
			if ($sortfield != "") {
				// on cherche � trier sur le champ $trifield
				// compose la cha�ne de tri
				$tri = $sortfield;
				if ($desc == 1) $tri .= " DESC";
					else $tri .= " ASC";
				// on enl�ve les doubles espaces dans la proc�dure
				$valeur = ereg_replace("/\s+/", " ", $valeur);
				// supprime un �ventuel ; � la fin de la requ�te
				$valeur = ereg_replace("/;$/", "", $valeur);
				// on recherche la premi�re occurence de ORDER BY
				$s = stristr($valeur, "order by");
				if ($s) {
					// y'a d�j� une clause order by... moins facile...
					// il faut qu'on sache si on aura besoin de mettre une virgule ou pas
					if ( preg_match("#,#", $s) ) {
						$virgule = true;
					} else if ( ! ereg("${sortfield}", $s)) {
						$virgule = true;
					} else {
						$virgule = false;
					}
					if ($virgule) {
						$tri .= ", ";
					}
					// regarde si le champ est d�j� dans la liste des champs � trier et le remplace si besoin
					$new_s = preg_replace("/$sortfield, /", "", $s);
					$new_s = preg_replace("/$sortfield/", "", $new_s);
					// ajoute la clause order by correcte
					$new_s = preg_replace("/order\s+by\s+/i", "order by $tri", $new_s);
					// replace l'ancienne cha�ne par la nouvelle
					$valeur = str_replace($s, $new_s, $valeur);
				} else {
					$valeur .= " order by $tri";
				}
			}

			print "<strong>".$msg["procs_ligne"]." $cle </strong>:&nbsp;".$valeur."<br><br>";

			if (($pmb_procs_force_execution && $force_exec) || (($PMBuserid == 1) && $force_exec) || explain_requete($valeur)) {
				$res = @mysql_query($valeur, $dbh);
				echo mysql_error();
				$nbr_lignes = @mysql_num_rows($res);
				$nbr_champs = @mysql_num_fields($res);

				if($nbr_lignes) {
					echo "<table >";
					for($i=0; $i < $nbr_champs; $i++) {
						// ajout de liens pour trier les pages
						$fieldname = mysql_field_name($res, $i);
						$sortasc = "<a href='${urlbase}&sortfield=".($i+1)."&desc=0'>asc</a>";
						$sortdesc = "<a href='${urlbase}&sortfield=".($i+1)."&desc=1'>desc</a>";
						print("<th>${fieldname}</th>");
					}

					for($i=0; $i < $nbr_lignes; $i++) {
						$row = mysql_fetch_row($res);
						echo "<tr>";
						foreach($row as $dummykey=>$col) {
							if(trim($col)=='') $col = '&nbsp;';
							print '<td>'.$col.'</td>';
						}
						echo "</tr>";
					}
					echo "</table><hr>";
				} else {
					print "<br><font color='#ff0000'>".$msg['admin_misc_lignes']." ".mysql_affected_rows($dbh);
					$err = mysql_error($dbh);
					if ($err) print "<br>$err";
					echo "</font><hr>";
				}
			} else {
				// erreur explain_requete
				print "<br><br>".$msg["proc_param_explain_failed"]."<br><br>".$erreur_explain_rqt;

				if ($pmb_procs_force_execution || ($PMBuserid == 1)) {
					print "
						<script type='text/javascript'>
							if (document.getElementById('procs_button_exec')) {
								var button_procs_exec = document.getElementById('procs_button_exec');
								button_procs_exec.setAttribute('value','".addslashes($msg["procs_force_exec"])."');
								button_procs_exec.setAttribute('onClick','document.location=\"./admin.php?categ=opac&sub=stat&section=view_list&act=exec_req&id_req=$id&force_exec=1\"');
							}
						</script>
					";
				}
			}
		}
	} // fin while

} else {
	print $msg["proc_param_query_failed"];
}
