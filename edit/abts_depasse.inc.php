<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: abts_depasse.inc.php,v 1.2 2012-02-10 15:03:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// Impression PDF des abonnements dont la date est d�pass�e

function bulletinage_abt($fiche, $x, $y, $link, $short=0, $longmax=99999) {
	global $ourPDF;
	global $msg ;
	global $pmb_pdf_font;

	$ourPDF->SetXY ($x,$y);
	$ourPDF->setFont($pmb_pdf_font, '', 8);
	$ourPDF->multiCell(190, 8, formatdate($fiche['date'])  , 0, 'L', 0);

	$ourPDF->SetXY ($x+20,$y);
	$ourPDF->setFont($pmb_pdf_font, '', 8);
	
	$titre = $fiche['tit1']." / ".$fiche['abt_name'];
	$lgTitre = strlen($titre);
	if($lgTitre>140) $titre = substr($titre,0,140)."...";	
	$ourPDF->multiCell(180, 8, $titre.". ".$fiche['cote'], 0, 'L', 0);
/*	
	$ourPDF->SetXY ($x+140,$y);
	$ourPDF->setFont($pmb_pdf_font, 'B', 8);
	$ourPDF->multiCell(190, 8, $fiche['libelle_numero'], 0, 'L', 0);
*/	
}		

function bulletinage_titre($titre, $x, $y, $link, $short=0, $longmax=99999) {
	global $ourPDF;
	global $msg ;
	global $pmb_pdf_font;
	global $location_view;
	
	$ourPDF->SetXY ($x,$y);
	$ourPDF->setFont($pmb_pdf_font, 'B', 12);
	$ourPDF->multiCell(190, 8, $titre  , 0, 'L', 0);				
}		


$ourPDF = new $fpdf('P', 'mm', 'A4');
$ourPDF->Open();
$ourPDF->addPage();
$ourPDF->SetLeftMargin(10);
$ourPDF->SetTopMargin(10);
$offsety = 0;
if(!$pmb_hide_biblioinfo_letter) biblio_info( 10, 10, 1) ;
$offsety=(ceil($ourPDF->GetStringWidth($biblio_name)/90)-1)*10; //90=largeur de la cell, 10=hauteur d'une ligne
$i=0;
$nb_page=0;
$nb_par_page = 41;
$nb_1ere_page = 39;
$taille_bloc = 5;

$requete = "SELECT abt_id,abt_name,tit1,num_notice, date_fin
			FROM abts_abts,notices
			WHERE date_fin < CURDATE()
			and notice_id= num_notice";
if ($location_view) $requete .= " and location_id='$location_view'";
$requete .= " ORDER BY date_fin,abt_name";		
$resultat = mysql_query($requete);
if (($nb_abts_retard=mysql_num_rows($resultat)) != 0) {
	$titre=$msg["abts_print_depasse"]." ( $nb_abts_retard )";					
	bulletinage_titre ($titre,10,25+$offsety,$dbh, 1, 80);				
				
	while ($r = mysql_fetch_object($resultat)) {
		$fiche["date"]=$r->date_fin;
		$fiche["tit1"]=$r->tit1;
		$fiche["abt_name"]=$r->abt_name;
		if ($nb_page==0 && $i<$nb_1ere_page) {
			$pos_page = 50+$offsety+$taille_bloc*$i;
		}
		if (($nb_page==0 && $i==$nb_1ere_page) || ((($i-$nb_1ere_page) % $nb_par_page)==0)) {
			$ourPDF->addPage();
			$nb_page++;
		}
		if ($nb_page>=1) {
			$pos_page = 10+($taille_bloc*($i-$nb_1ere_page-($nb_page-1)*$nb_par_page));
		}
		bulletinage_abt ($fiche,10,$pos_page,$dbh, 1, 80);	
		$i++;	
	}
}	

header("Content-Type: application/pdf");
$ourPDF->OutPut();

?>