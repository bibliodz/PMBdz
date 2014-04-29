<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: expand_ajax.inc.php,v 1.6 2013-06-20 09:59:31 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/notice_affichage.class.php");
require_once("$class_path/notice_affichage.ext.class.php");
require_once($class_path."/notice_onglet.class.php");

$notice_affichage_cmd=stripslashes($notice_affichage_cmd);
$param=unserialize($notice_affichage_cmd);

if($opac_notice_affichage_class == "") $opac_notice_affichage_class = "notice_affichage";
$display = new $opac_notice_affichage_class($param['id'], $param['aj_liens'], $param['aj_cart'], $param['aj_to_print'], $param['aj_header_only'], !$param['aj_no_header']);
//$display->do_header_without_html();
if($param['aj_nodocnum']) $display->docnum_allowed = 0;
$type_aff=$param['aj_type_aff'];
switch ($type_aff) {
	case AFF_ETA_NOTICES_ISBD :
		$display->do_isbd();
		$display->genere_simple(0, 'ISBD') ;
		break;
	case AFF_ETA_NOTICES_PUBLIC :
		$display->do_public();
		$display->genere_simple(0, 'PUBLIC') ;
		break;
	case AFF_ETA_NOTICES_BOTH :
		$display->do_isbd();
		$display->do_public();
		$display->genere_double(0, 'PUBLIC') ;
		break ;
	case AFF_ETA_NOTICES_BOTH_ISBD_FIRST :
		$display->do_isbd();
		$display->do_public();
		$display->genere_double(0, 'ISBD') ;
		break ;
	default:
		$display->do_isbd();
		$display->do_public();		
		$display->genere_double(0, 'autre') ;
		$flag_no_onglet_perso=1;
		break ;
					
}
$html=$display->result;
if(!$flag_no_onglet_perso){
	$onglet_perso=new notice_onglets();
	$html=$onglet_perso->insert_onglets($param['id'],$html);
}
ajax_http_send_response($html);
?>