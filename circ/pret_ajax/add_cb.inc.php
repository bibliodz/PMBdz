<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: add_cb.inc.php,v 1.2 2012-04-30 14:04:19 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/ajax_pret.class.php");

//cb_doc, id_empr, short_loan
// init de la class
$pret = new do_pret();
$status = $pret->check_pieges($cb_empr, $id_empr,$cb_doc, $id_expl,$forcage,$short_loan);
ajax_http_send_response("$status","text/xml");

?>