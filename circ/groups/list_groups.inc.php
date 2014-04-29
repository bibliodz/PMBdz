<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_groups.inc.php,v 1.16 2013-06-03 12:14:09 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// affichage de la liste des groupes pour sélection
function list_group($clef, $filter_list, $group_list, $nav_bar, $nb_total) {
	global $group_list_tmpl;
 	global $charset;
	$group_list_tmpl = str_replace("!!filter_list!!", $filter_list, $group_list_tmpl);
 	$group_list_tmpl = str_replace("!!cle!!", $clef, $group_list_tmpl);
 	if ($nb_total>0) $group_list_tmpl = str_replace("<!--!!nb_total!!-->", "(".$nb_total.")", $group_list_tmpl);
	$group_list_tmpl = str_replace("!!list!!", $group_list, $group_list_tmpl);
	$group_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $group_list_tmpl);
	print pmb_bidi($group_list_tmpl);
}

// nombre de références par pages
if (!$nb_per_page) {
	if ($nb_per_page_author != "")
	$nb_per_page = $nb_per_page_author ;
	else $nb_per_page = 10;
}

// traitement de la saisie utilisateur
$group_query = str_replace("*", "%", $group_query) ;
if ($group_query) $clause = " WHERE libelle_groupe like '%$group_query%' ";
	else $clause = '' ;
	
// formulaire de restriction
$filter_list = "<form class='form-$current_module' id='form-$current_module-list' name='form-$current_module-list' action='$PHP_SELF?categ=$categ&action=$action&group_query=$group_query' method='post'>
 	<div class='left'>
		$msg[circ_afficher]
 		<input type=text name=nb_per_page size=2 value=$nb_per_page class='petit' /> $msg[1905]&nbsp;
	</div>
	<input type='button' class='bouton' value='".$msg['actualiser']."' onClick=\"this.form.submit();\">
	</form>";
			
// on récupére le nombre de lignes 
if(!$nbr_lignes) {
	$requete = "SELECT COUNT(1) FROM groupe $clause ";
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = @mysql_result($res, 0, 0);
}


if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

if($nbr_lignes) {
	// on lance la vraie requête
	
	$requete = "SELECT id_groupe, libelle_groupe, resp_groupe, concat(IFNULL(empr_prenom,'') ,' ',IFNULL(empr_nom,'')) as resp_name, count( empr_id ) as nb_empr FROM groupe LEFT  JOIN empr_groupe ON groupe_id = id_groupe left join empr on resp_groupe = id_empr
	$clause group by id_groupe, libelle_groupe, resp_groupe, resp_name ORDER BY libelle_groupe LIMIT $debut,$nb_per_page ";
	$res = mysql_query($requete, $dbh);
	if ((mysql_num_rows($res) > 1)||($page>1)) {
		$parity=1;
		$group_list .= "<tr><th>".$msg[904]."</th><th>".$msg[913]."</th><th>".$msg['circ_group_emprunteur']."</th><th>".$msg['349']."</th><th>".$msg['reserv_en_cours']."</th>";
		while($rgroup=mysql_fetch_object($res)) {
			if ($parity % 2) {
				$pair_impair = "even";
			} else {
				$pair_impair = "odd";
			}
			$parity += 1;
			$nb_pret=0;
			$requete = "SELECT count( pret_idempr ) as nb_pret FROM empr_groupe,pret where groupe_id=$rgroup->id_groupe and empr_id = pret_idempr";
			$res_pret = mysql_query($requete, $dbh);
			if (mysql_num_rows($res_pret)) {
				$rpret=mysql_fetch_object($res_pret);
				$nb_pret=$rpret->nb_pret;	
			}
			$nb_resa=0;
			$requete = "SELECT count( resa_idempr ) as nb_resa FROM empr_groupe,resa where groupe_id=$rgroup->id_groupe and empr_id = resa_idempr";
			$res_resa = mysql_query($requete, $dbh);
			if (mysql_num_rows($res_resa)) {
				$rresa=mysql_fetch_object($res_resa);
				$nb_resa=$rresa->nb_resa;	
			}
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./circ.php?categ=groups&action=showgroup&groupID=$rgroup->id_groupe';\" ";
     			$group_list .= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>
		  			<td>$rgroup->libelle_groupe</td>
					<td>$rgroup->resp_name</td>
					<td>$rgroup->nb_empr</td>
					<td>$nb_pret</td>
					<td>$nb_resa</td>
					</tr>";
    	}
		mysql_free_result($res);

		// constitution des liens
		$nbepages = ceil($nbr_lignes/$nb_per_page);
		$suivante = $page+1;
		$precedente = $page-1;
		
		// affichage du lien précédent si nécéssaire
		if($precedente > 0)
			$nav_bar .= "<a href='$PHP_SELF?categ=groups&action=listgroups&page=$precedente&nbr_lignes=$nbr_lignes&nb_per_page=$nb_per_page&group_query=$group_query'><img src='./images/left.gif' border='0' alt='$msg[48]' hspace='3' align='middle' title='$msg[48]'></a>";

		for($i = 1; $i <= $nbepages; $i++) {
			if($i==$page) $nav_bar .= "<b>page $i/$nbepages</b>";
		}	

		if($suivante<=$nbepages)
			$nav_bar .= "<a href='$PHP_SELF?categ=groups&action=listgroups&page=$suivante&nbr_lignes=$nbr_lignes&nb_per_page=$nb_per_page&group_query=$group_query'><img src='./images/right.gif' border='0' alt='$msg[49]' hspace='3' align='middle' title='$msg[49]'></a>";

		// affichage du résultat
		list_group($group_query, $filter_list, $group_list, $nav_bar, $nbr_lignes);
	} else {
		$rgroup = $rgroup=mysql_fetch_object($res);
		$groupID = $rgroup->id_groupe;
		include('./circ/groups/show_group.inc.php');
	}
} else {
	// la requête n'a produit aucun résultat
	print pmb_bidi($group_search);
	error_message($msg[917], str_replace('!!group_cle!!', htmlentities(stripslashes($group_query),ENT_QUOTES, $charset), $msg[918]), 0, './circ.php?categ=groups');
}
