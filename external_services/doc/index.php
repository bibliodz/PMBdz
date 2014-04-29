<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index.php,v 1.2 2013-04-04 09:44:23 mbertin Exp $

//Ce script n�c�ssite php5

if (phpversion() < 5) {
	die("PHP5 required");
}

header("Content-Type: text/html; charset=utf-8");

$doc = new DOMDocument('1.0');
$xsl = new XSLTProcessor();
$xsl->registerPHPFunctions();

$doc->load("mache_doc_group_to_html.xsl");
$xsl->importStyleSheet($doc);
$xsl->setParameter('', 'working_group', (isset($_GET["group"]) ? $_GET["group"] : ''));
$xsl->setParameter('', 'external_services_basepath', '..');
$xsl->setParameter('', 'navigation_base', '?');

$result = $xsl->transformToXML($doc);

print $result;

?>