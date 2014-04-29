<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: crontab.class.php,v 1.1 2011-07-29 12:32:10 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class crontab {

	/*
	 * Ajoute ou modifie la t�che PMB dans le cron de l'OS 
	 */
	function addTaskCron($id) {
		global $dbh;
		
		$requete = "select id_planificateur, num_type_tache, libelle_tache, desc_tache, calc_next_date_deb, calc_next_heure_deb 
			from planificateur 
			where statut='1' 
			and id_planificateur= '".$id."'";
		$res = mysql_query($requete,$dbh);
		
		while ($row = mysql_fetch_array($res)) {
			$oldCrontab = Array();				/* r�cup�re les informations de l'ancien crontab */
			$newCrontab = Array();				/* ajoute le nouveau crontab */
							
			exec('crontab -l', $oldCrontab);		/* on r�cup�re l'ancienne crontab dans $oldCrontab */
	
			$calc_next_date_deb = explode("-", $row["calc_next_date_deb"]);
			$calc_next_heure_deb = explode(":", $row["calc_next_heure_deb"]);
			$ident = "pmb_".$row["id_planificateur"].".".$row["num_type_tache"];
			
			if ($row["desc_tache"] != '') {
				$comment = $row["desc_tache"];
			} else {
				$comment = ' Aucun commentaire';
			}
			$chpCommande = "/rep/le fichier php";
			
			$trouve = false;
			
			//on v�rifie si cette t�che est d�j� dans le cron
			foreach($oldCrontab as $index => $ligne) {
				if (preg_match("/^# ".$ident."/",$oldCrontab[$index], $matches, PREG_OFFSET_CAPTURE) == "1") {
					$oldCrontab[$index] = "# ".$ident." : ".$comment;
					$oldCrontab[$index+1] = $calc_next_heure_deb[0].' '.$calc_next_heure_deb[1].' '.$calc_next_date_deb[2].' '.$calc_next_date_deb[1].' * '.$chpCommande;
					$trouve = true;
				}
				$newCrontab[] = $oldCrontab[$index];
			}

			//si la t�che n'est pas trouv�e, on l'ajoute
			if (!$trouve) {
				$newCrontab[] = "# ".$ident." : ".$comment;
				$newCrontab[] = $calc_next_heure_deb[0].' '.$calc_next_heure_deb[1].' '.$calc_next_date_deb[2].' '.$calc_next_date_deb[1].' * '.$chpCommande;
			}
			$f = fopen('/var/spool/cron/apache', 'w');			/* on cr�e le fichier s'il n'existe pas */
			fwrite($f, implode(chr(10), $newCrontab)); 
			fclose($f);
			
			exec('crontab /var/spool/cron/apache');				/* on le soumet comme crontab */
		}
		
	}
	

	function delTaskCron($ident) {
		$oldCrontab = Array();						/* r�cup�re les informations de l'ancien crontab */
		$newCrontab = Array();						/* ajoute le nouveau crontab */
		exec('crontab -l', $oldCrontab);			/* on r�cup�re l'ancienne crontab dans $oldCrontab */
		
		foreach($oldCrontab as $index=>$ligne) {
			/* copie $oldCrontab dans $newCrontab sans le script � effacer */
			if (preg_match("/^# ".$ident."/",$oldCrontab[$index], $matches, PREG_OFFSET_CAPTURE) == "1") {
				$ligne= "";
			//	$oldCrontab[$index+1] = "";
			} 
			if ($ligne != "") {
				$newCrontab[] = $ligne;	
			}			
						
		}
		
		$f = fopen('/var/spool/cron/apache', 'w');			/* on cr�e le fichier s'il n'existe pas */
		fwrite($f, implode(chr(10), $newCrontab)); 
		fclose($f);
		
		exec('crontab /var/spool/cron/apache');				/* on le soumet comme crontab */
		
		return 	$id;
	}				
}