<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frame_liste_items.php,v 1.1 2013-11-29 13:55:10 dgoron Exp $

$base_path="./../..";
$base_auth = "ADMINISTRATION_AUTH";
$base_title = "\$msg[7]";
//permet d'appliquer le style de l'onglet ou apparait la frame
$current_alert = "admin";

require_once ("$base_path/includes/init.inc.php");

switch ($what) {
	case "typdoc_docs":
		$titre = $msg["admin_docs_list"];
		$link = $base_path."/catalog.php?categ=edit_expl&id=!!id_notice!!&expl_id=!!id!!";
		$rqt = "select expl_id,expl_cb,expl_notice from exemplaires where expl_typdoc ='".$item."' order by expl_cb";
		break;
	case "location_docs":
		$titre = $msg["admin_docs_list"];
		$link = $base_path."/catalog.php?categ=edit_expl&id=!!id_notice!!&expl_id=!!id!!";
		$rqt = "select expl_id,expl_cb,expl_notice from exemplaires where expl_location='".$item."' order by expl_cb";
		break;
	case "location_users":
		$titre = $msg["admin_users_list"];
		$link = $base_path."/admin.php?categ=users&sub=users&action=modif&id=!!id!!";
		$rqt = "select userid,concat (nom,' ',prenom,' (',username,')') from users where deflt2docs_location='".$item."' or deflt_docs_location='".$item."' order by nom,prenom";
		break;
	case "location_empr":
		$titre = $msg["admin_empr_list"];
		$link = $base_path."/circ.php?categ=pret&form_cb=!!id!!";
		$rqt = "select empr_cb,concat (empr_nom,' ',empr_prenom,' (',empr_cb,')') from empr where empr_location='".$item."' order by empr_nom,empr_prenom";
		break;
	case "location_abts":
		$titre = $msg["admin_abts_list"];
		$link = $base_path."/catalog.php?categ=serials&sub=abon&serial_id=!!id_notice!!&abt_id=!!id!!";
		$rqt = "select abt_id,abt_name from abts_abts where location_id ='".$item."' order by abt_name";
		break;
	case "location_collections_state":
		$titre = $msg["admin_collections_state_list"];
		$link = $base_path."/catalog.php?categ=serials&sub=collstate_form&id=!!id!!&serial_id=!!id_notice!!";
		$rqt = "select collstate_id,state_collections from collections_state where location_id ='".$item."' order by state_collections";
		break;
	case "section_docs":
		$titre = $msg["admin_docs_list"];
		$link =$base_path."/catalog.php?categ=edit_expl&id=!!id_notice!!&expl_id=!!id!!";
		$rqt = "select expl_id,expl_cb,expl_notice from exemplaires where expl_section ='".$item."' order by expl_cb";
		break;
	case "section_users":
		$titre = $msg["admin_users_list"];
		$link = $base_path."/admin.php?categ=users&sub=users&action=modif&id=!!id!!";
		$rqt = "select userid,concat (nom,' ',prenom,' (',username,')') from users where deflt_docs_section='".$item."' order by nom,prenom";
		break;
	case "section_abts":
		$titre = $msg["admin_abts_list"];
		$link = $base_path."/catalog.php?categ=serials&sub=abon&serial_id=!!id_notice!!&abt_id=!!id!!";
		$rqt = "select abt_id,abt_name from abts_abts where section_id ='".$item."' order by abt_name";
		break;
	case "statut_docs":
		$titre = $msg["admin_docs_list"];
		$link =$base_path."/catalog.php?categ=edit_expl&id=!!id_notice!!&expl_id=!!id!!";
		$rqt = "select expl_id,expl_cb,expl_notice from exemplaires where expl_statut ='".$item."' order by expl_cb";
		break;
	case "codestat_docs":
		$titre = $msg["admin_docs_list"];
		$link =$base_path."/catalog.php?categ=edit_expl&id=!!id_notice!!&expl_id=!!id!!";
		$rqt = "select expl_id,expl_cb,expl_notice from exemplaires where expl_codestat ='".$item."' order by expl_cb";
		break;
}

$nb_per_page=100;

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

$rqt.=" limit $debut,$nb_per_page";

$res = mysql_query($rqt,$dbh);
$st = "odd";
while (($data = mysql_fetch_array($res))) {
	if ($st=="odd")
		$st = "even";
	else
		$st = "odd";
	
	$lien = str_replace("!!id!!",$data[0],$link);
	if ($data[2]) $lien = str_replace("!!id_notice!!",$data[2],$lien);
	
	$liste .= 	"<tr class='" .$st ."' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='" . $st ."'\"  style='cursor: pointer'>
					<td onClick=\"parent.location.href='".$lien."';\">".$data[1]."</td>
				</tr>";
}

$global = "
<div class='row'>
	<div class='right'><a href='#' onClick='parent.kill_frame_items();return false;'><img src='" . $base_path . "/images/close.gif' border='0' align='right'></a></div>
	<h3>" . $titre . " (".$total.")</h3>
	<table>	
		!!liste!!
	</table>
</div>";

print str_replace("!!liste!!",$liste,$global);

print "<div class='row'><div align='center'>";
$url_base = $base_path."/admin/docs/frame_liste_items.php?what=".$what."&item=".$item."&total=".$total;
$nav_bar = aff_pagination ($url_base, $total, $nb_per_page, $page, 10, false, true) ;
print $nav_bar;
print "</div>";

print "</body></html>";

mysql_close($dbh);

?>