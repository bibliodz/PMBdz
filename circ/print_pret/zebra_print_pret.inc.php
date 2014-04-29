<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: zebra_print_pret.inc.php,v 1.6 2014-02-06 09:49:14 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/printer.class.php");

$id_empr+=0;
$printer= new printer();

switch($sub):
	case 'one':
		$ticket_pret=$printer->print_pret($id_empr,$cb_doc);
	break;
	case 'get_script':	
		// a cause du focus qui ne se fait pas
		// Pb https <applet name='jzebra' code='jzebra.PrintApplet.class' archive='".$pmb_opac_url."includes/javascript/printers/zebra/jzebra.jar' width='0px' height='0px'>
		$ticket_pret="
		<applet name='jzebra' code='jzebra.PrintApplet.class' archive= '".$pmb_opac_url."includes/javascript/printers/zebra/jzebra.jar' width='0px' height='0px'>
			<param name='printer' value='$pmb_printer_name'>
		</applet>";
	break;
	case 'all':			
		$ticket_pret=$printer->print_all_pret($id_empr);
	break;
	case 'transacash_ticket':			
		$ticket_pret=$printer->transacash_ticket($transacash_id);
	break;
	default:
		ajax_http_send_error('400',"commande inconnue");
	break;		
endswitch;

ajax_http_send_response($ticket_pret);

/*

require_once('./circ/print_pret/func.inc.php');


$ticket_pret="\x1B\x40"; // init de l'imprimante
$ticket_pret.="\x1B\x21\x16"; // Taille police gras
$ticket_pret.="".$biblio_name."\n";
$ticket_pret.="\x1B\x21\x04"; // Taille police 
$ticket_pret.="".$biblio_adr1."\n";
$ticket_pret.="".$biblio_town."\n";
$ticket_pret.="".$biblio_phone."\n";
$ticket_pret.="".$biblio_email."\n";
$ticket_pret.="\n";
$ticket_pret.="".$msg['fpdf_edite']." ".formatdate(date("Y-m-d",time()))."\n";
$ticket_pret.="\n";
$ticket_pret.="Emprunteur:\n ";
$ticket_pret.="".$empr->nom." ". $empr->prenom."\n";
$ticket_pret.="\n";


function print_expl($cb_doc) {
	global $charset,$msg;
	$expl=get_info_expl($cb_doc);
	$info_expl.="";
	
	$titre=substr($expl->tit,0,25);
	
	$info_expl.="".$titre."\n";
	$info_expl.="".$expl->expl_cb."\n";
	$info_expl.="".$expl->location_libelle." / ".$expl->section_libelle." / ".$expl->expl_cote."\n";		
	$info_expl.=$msg['printer_pret_date'].$expl->aff_pret_date.". "; 
	$info_expl.="\x1B\x21\x14";
	$info_expl.=$msg['printer_retour_date'].$expl->aff_pret_retour."";
	$info_expl.="\x1B\x21\x04";
	
	return $info_expl;
}
//En fonction de $sub, inclure les fichiers correspondants
switch($sub):
	case 'one':
		$ticket_pret.="".$msg["ticket_de_pret"]."\n";
		$ticket_pret.=print_expl($cb_doc);
		$ticket_pret.="\n\n";
	break;
	case 'all':	
		
		$ticket_pret.=""."Liste des pr�ts:"."\n\n";
		$query = "select expl_cb from pret,exemplaires  where pret_idempr=$id_empr and expl_id=pret_idexpl ";		
		$result = mysql_query($query, $dbh);
		$first=0;
		while (($r= mysql_fetch_array($result))) {
			if($first)$ticket_pret.="\n ______________________________________\n\n";
			$ticket_pret.=print_expl( $r['expl_cb']);
			
			$first=1;
		}
		$ticket_pret.="\n\n";
	break;
	default:
		ajax_http_send_error('400',"commande inconnue");
	break;		
endswitch;	

$ticket_pret.="\x1D\x56\x41"; //cut
$ticket_pret.="\x1B\x40"; //init de l'imprimante
if($charset!="utf-8"){
//	$ticket_pret = utf8_encode($ticket_pret);
}		
ajax_http_send_response($ticket_pret);

?>
*/