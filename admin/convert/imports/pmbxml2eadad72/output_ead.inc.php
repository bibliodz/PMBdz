<?php
/*
 * Created on 15 oct. 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function _get_header_($output_params) {
      $r="<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>".chr(0x0D).chr(0x0A);
      $r.="<!DOCTYPE ead SYSTEM \"ead.dtd\">".chr(0x0D).chr(0x0A);
      $r.="<ead>".chr(0x0D).chr(0x0A);
      $r.="	<eadheader>".chr(0x0D).chr(0x0A);
      $r.="		<eadid countrycode=\"FR\">FRAD072_BIBLIO</eadid>".chr(0x0D).chr(0x0A);
      $r.="		<filedesc>".chr(0x0D).chr(0x0A);
      $r.="			<titlestmt>".chr(0x0D).chr(0x0A);
      $r.="				<titleproper>Inventaire en EAD</titleproper>".chr(0x0D).chr(0x0A);
      $r.="				<author></author>".chr(0x0D).chr(0x0A);
      $r.="			</titlestmt>".chr(0x0D).chr(0x0A);
      $r.="		</filedesc>".chr(0x0D).chr(0x0A);
      $r.="		<profiledesc>".chr(0x0D).chr(0x0A);
      $r.="			<creation>".chr(0x0D).chr(0x0A);
      $r.="				<date></date>".chr(0x0D).chr(0x0A);
      $r.="			</creation>".chr(0x0D).chr(0x0A);
      $r.="			<langusage>Document r�dig� en".chr(0x0D).chr(0x0A);
      $r.="				<language langcode=\"fre\">fran�ais</language>".chr(0x0D).chr(0x0A);
      $r.="			</langusage>".chr(0x0D).chr(0x0A);
      $r.="		</profiledesc>".chr(0x0D).chr(0x0A);
      $r.="	</eadheader>".chr(0x0D).chr(0x0A);
      $r.="	<archdesc level=\"fonds\" relatedencoding=\"UNIMARC\" >".chr(0x0D).chr(0x0A);
      $r.="		<did>".chr(0x0D).chr(0x0A);
      $r.="			<unittitle>Fonds documentaire</unittitle>".chr(0x0D).chr(0x0A);
      $r.="		</did>".chr(0x0D).chr(0x0A);
      $r.="		<dsc>".chr(0x0D).chr(0x0A);
    return $r;
}

function _get_footer_($output_params) {
	$r="";
	$r.="	</dsc>".chr(0x0D).chr(0x0A);
	$r.="	</archdesc>".chr(0x0D).chr(0x0A);
	$r.="</ead>";
    return $r;
}
?>
