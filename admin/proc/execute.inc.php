<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: execute.inc.php,v 1.11 2013-04-18 10:29:59 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// include d'ex�cution d'une proc�dure

$is_external = isset($execute_external) && $execute_external;
if ($is_external) {
	$nbr_lignes = 1;
}
else {
	$requete = "SELECT * FROM procs WHERE idproc=$id ";
	$res = mysql_query($requete, $dbh);
	
	$nbr_lignes = mysql_num_rows($res);
	$urlbase = "./admin.php?categ=proc&sub=proc&action=final&id=$id";
}

if($nbr_lignes) {

	// r�cup�ration du r�sultat
	if ($is_external) {
		$idp = $id;
		$name = $execute_external_procedure->name;
		$code = $execute_external_procedure->sql;
		$commentaire = $execute_external_procedure->comment;
	}
	else {
		$row = mysql_fetch_row($res);
		$idp = $row[0];
		$name = $row[1];
		if (!$code)
			$code = $row[2];
		$commentaire = $row[3];
	}
	print "<form class=\"form-admin\" id=\"formulaire\" name=\"formulaire\" action=\"\" method=\"post\">";
	print $param_proc_hidden;
	if($force_exec){
		print "<input type='hidden' name='force_exec'  value='".$force_exec."' />";//On a forc� la requete
	}
	if (!$is_external) {
		print "<br>
			<h3>".htmlentities($msg["procs_execute"]." ".$name, ENT_QUOTES, $charset)."</h3>
			<br />".htmlentities($commentaire, ENT_QUOTES, $charset)."<hr/>
				<input type='button' class='bouton' value='$msg[62]' onClick='this.form.action=\"./admin.php?categ=proc&sub=proc&action=modif&id=".$id."\";this.form.submit();'/>";
		print "<input type='button' id='procs_button_exec' class='bouton' value='$msg[708]' onClick='this.form.action=\"./admin.php?categ=proc&sub=proc&action=execute&id=".$id."\";this.form.submit();'/>";
		print "<br />";
	} else { 
		print "<br>
			<h3>".htmlentities($msg["remote_procedures_executing"]." ".$name, ENT_QUOTES, $charset)."</h3>
			<br />".htmlentities($commentaire, ENT_QUOTES, $charset)."<hr />";
		print "<input type='button' id='procs_button_exec' class='bouton' value='$msg[708]' onClick='this.form.action=\"./admin.php?categ=proc&sub=proc&action=execute_remote&id=$id\";this.form.submit();' />";
		print "<br />";
	}
	$linetemp = explode(";", $code);
	for ($i=0;$i<count($linetemp);$i++) if (trim($linetemp[$i])) $line[]=trim($linetemp[$i]);
	$do_reindexation=false;
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

			print "<strong>".$msg["procs_ligne"]." $cle </strong>:&nbsp;$valeur<br /><br />";
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
					echo "</table><hr />";
				} else {
					$ligne_affected=mysql_affected_rows($dbh);
					print "<br /><font color='#ff0000'>".$msg['admin_misc_lignes']." ".$ligne_affected;
					$err = mysql_error($dbh);
					if ($err){
						print "<br />$err";
					}else{
						if($ligne_affected){
							$do_reindexation=true;
						}
					}
					echo "</font><hr />";
				}
			} else {
				// erreur explain_requete
				print "<br /><br />".htmlentities($msg["proc_param_explain_failed"], ENT_QUOTES, $charset)."<br /><br />".$erreur_explain_rqt;
				
				if ($pmb_procs_force_execution || ($PMBuserid == 1)) {
					if(!$is_external){
						$lien_force="./admin.php?categ=proc&sub=proc&action=final&id=".$id."&force_exec=1";
					}else{
						$lien_force="./admin.php?categ=proc&sub=proc&action=final_remote&id=".$id."&force_exec=1";
					}
					print "
						<script type='text/javascript'>
							if (document.getElementById('procs_button_exec')) {
								var button_procs_exec = document.getElementById('procs_button_exec');
								button_procs_exec.setAttribute('value','".addslashes($msg["procs_force_exec"])."');
								button_procs_exec.setAttribute('onClick','this.form.action=\"".$lien_force."\";this.form.submit();');
							}
						</script>
					";
				}
			}
		}
	} // fin while
	if($do_reindexation){
		echo "<font color='#ff0000'><h2>".$msg['admin_proc_reindex']."</h2><br/>";
	}
	print "</form>";
} else {
	print $msg["proc_param_query_failed"];
}
