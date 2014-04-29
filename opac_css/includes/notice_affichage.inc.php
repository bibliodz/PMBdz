<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_affichage.inc.php,v 1.42 2014-03-14 17:31:02 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//Affichage d'une notice

require_once($class_path."/notice_affichage.class.php");
require_once($class_path."/notice_affichage.ext.class.php");
require_once($class_path."/serial_affichage_unimarc.class.php");
require_once($class_path."/notice_onglet.class.php");

//afin d'inclure les fichiers contenant les fonctions particuli�res d'affichage
// require_once($include_path."/func_phototheque.inc.php"); EST REMPLACE PAR LE CODE CI-DESSOUS
if ($opac_notice_groupe_fonction) {
	$couples_type_not_fonction=explode(";",$opac_notice_groupe_fonction);
	for ($i=0; $i<count($couples_type_not_fonction); $i++) {
		$couples_type_not_fonction_c=explode(" ",trim($couples_type_not_fonction[$i]));
		$groupe_fonction_temp=trim($couples_type_not_fonction_c[1]);
		if ($groupe_fonction_temp!="aff_notice") {
			require_once($include_path."/".$groupe_fonction_temp.".inc.php");
		}
	}
}

function get_aff_function() {
	global $l_typdoc;
	global $opac_notice_groupe_fonction;
	global $aff_notice_fonction;
	global $is_aff_notice_fonction;
	
	if (!$is_aff_notice_fonction) {
		$couples=explode(";",$opac_notice_groupe_fonction);
		for ($i=0; $i<count($couples); $i++) {
			$c=explode(" ",trim($couples[$i]));
			$t_typdoc_o[]=explode(",",trim($c[0]));
			//Tri du tableau
			$fonction[]=trim($c[1]);
		}
		$t_typdoc=explode(",",$l_typdoc);
		//Pour chaque t_typdoc, recherche des �l�ments qui le contienne
		for ($i=0; $i<count($t_typdoc); $i++) {
			for ($j=0; $j<count($t_typdoc_o); $j++) {
				$as=array_search($t_typdoc[$i],$t_typdoc_o[$j]);
				if ($as===false) {
					for ($k=$j+1; $k<count($t_typdoc_o); $k++) {
						$t_typdoc_o[$k-1]=$t_typdoc_o[$k];
						$fonction[$k-1]=$fonction[$k];
					}
					unset($t_typdoc_o[count($t_typdoc_o)-1]);
					unset($fonction[count($t_typdoc_o)]);
					$j--;
				}
			}
		}	
		if ((count($t_typdoc_o))&&($fonction[0])) {
			$aff_notice_fonction=$fonction[0];
		}
		if ($aff_notice_fonction!="aff_notice") {
			$is_aff_notice_fonction=true;
		} else {
			$aff_notice_fonction="";
		}
	}
	return $aff_notice_fonction;
}

function aff_notice($id, $nocart=0, $gen_header=1, $use_cache=0, $mode_aff_notice="", $depliable="", $nodocnum=0, $enrichment=1, $recherche_ajax_mode=0) {

	global $liens_opac;
	global $opac_notices_format;
	global $opac_notices_depliable;
	global $opac_cart_allow;
	global $opac_cart_only_for_subscriber;
	global $opac_notice_affichage_class;
	global $opac_notice_enrichment;
	global $opac_recherche_ajax_mode;
	global $opac_notices_format_onglets;
	
	if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) {
		$cart=1; 
	} else {
		$cart=0;
	}
	if ($nocart) $cart=0;
	$id+=0;	
	//Recherche des fonctions d'affichage
	$f=get_aff_function();
	if ($f) return $f($id,$cart);
	
	if ($id>0) {
		$header_only=0;
		if($recherche_ajax_mode && $opac_recherche_ajax_mode){
			//Si ajax, on ne charge pas tout
			$header_only=1;
		}
		$current = new $opac_notice_affichage_class($id,$liens_opac,$cart,0,$header_only,!$gen_header);
		if($nodocnum) $current->docnum_allowed = 0;
		
		if ($depliable === "") $depliable=$opac_notices_depliable;
		if ($gen_header) $current->do_header();
		if ($mode_aff_notice !== "") $type_aff=$mode_aff_notice;
		else $type_aff=$opac_notices_format;
		if(!$current->visu_notice){
			return "";
		}
		if($opac_recherche_ajax_mode && $recherche_ajax_mode && $depliable && $type_aff!=AFF_ETA_NOTICES_REDUIT){
			$current->genere_ajax($type_aff,0) ;
			$retour_aff .= $current->result ;
		}else{
			switch ($type_aff) {
				case AFF_ETA_NOTICES_REDUIT :
					$retour_aff .= $current->notice_header_with_link."<br />";
					break;
				case AFF_ETA_NOTICES_ISBD :
					$current->do_isbd();
					$current->genere_simple($depliable, 'ISBD') ;
					$retour_aff .= $current->result ;
					break;
				case AFF_ETA_NOTICES_PUBLIC :
					$current->do_public();
					$current->genere_simple($depliable, 'PUBLIC') ;
					$retour_aff .= $current->result ;
					break;
				case AFF_ETA_NOTICES_BOTH :
					$current->do_isbd();
					$current->do_public();
					$current->genere_double($depliable, 'PUBLIC') ;
					$retour_aff .= $current->result ;
					break ;
				case AFF_ETA_NOTICES_BOTH_ISBD_FIRST :
					$current->do_isbd();
					$current->do_public();
					$current->genere_double($depliable, 'ISBD') ;
					$retour_aff .= $current->result ;
					break ;
				default:
					$current->do_isbd();
					$current->do_public();					
					$current->genere_double($depliable, 'autre') ;
					$retour_aff .= $current->result ;
					$flag_no_onglet_perso=1;
					break ;
					
			}
		
	/*			
			$onglets_title="";
			$onglets_content="";
			if($opac_notices_format_onglets){
				$onglets=explode(",", $opac_notices_format_onglets);
				foreach($onglets as $id_tpl){
					$notice_onglet=new notice_onglet($id_tpl);
					$onglets_title.="
					<li id='onglet_tpl_".$id_tpl."_".$id."'  class='isbd_public_inactive'>
						<a href='#' title=\"".$notice_onglet->get_onglet_header()."\" onclick=\"show_what('tpl_".$id_tpl."_', '$id'); return false;\">".$notice_onglet->get_onglet_header()."</a>
					</li>";
		
					$onglets_content.="
					<div id='div_tpl_".$id_tpl."_".$id."' class='onglet_tpl' style='display:none;'>
					".$notice_onglet->get_onglet_content($id)."
					</div>";
				}
			}	
			$retour_aff=str_replace('<!-- onglets_perso_list -->', $onglets_title, $retour_aff);
			$retour_aff=str_replace('<!-- onglets_perso_content -->', $onglets_content, $retour_aff);
				
	*/			
			if(!$flag_no_onglet_perso){
				$onglet_perso=new notice_onglets();
				$retour_aff=$onglet_perso->insert_onglets($id,$retour_aff);
			}
			if(!$depliable && $opac_notice_enrichment && $enrichment==1){
				$retour_aff.="<script type='text/javascript'>getEnrichment('$id');</script>";
			}
		}
	}	
	return $retour_aff;
}

function aff_notice_unimarc($id,$nocart=0, $entrepots_localisations=array()) {

	global $opac_notices_format;
	global $opac_notices_depliable;
	global $opac_cart_allow;
	global $opac_cart_only_for_subscriber;
	global $msg;

	
	if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) $cart=1; else $cart=0;
	if ($nocart) $cart=0;
	
	//Recherche des fonctions d'affichage
	//$f=get_aff_function();
	//if ($f) return $f($id,$cart);
	
	if ($id) {
		$current = new notice_affichage_unimarc($id,"",$cart,0, $entrepots_localisations);
		$depliable=$opac_notices_depliable;
		$current->do_header(); 
		
		if($current->notice_header == ""){
			$current->notice_header = sprintf($msg['cart_notice_expired'],$id);
			$current->notice_expired = true;
		}
		switch ($opac_notices_format) {
				case AFF_ETA_NOTICES_REDUIT :
					$retour_aff .= $current->notice_header." ";
					break;
				case AFF_ETA_NOTICES_ISBD :	
					$current->do_isbd();
					$current->genere_simple($depliable, 'ISBD') ;
					$retour_aff .= $current->result ;
					break;
				case AFF_ETA_NOTICES_PUBLIC :
					$current->do_public();
					$current->genere_simple($depliable, 'PUBLIC') ;
					$retour_aff .= $current->result ;
					break;
				case AFF_ETA_NOTICES_BOTH :
					$current->do_isbd();
					$current->do_public();
					$current->genere_double($depliable, 'PUBLIC') ;
					$retour_aff .= $current->result ;
					break ;
				case AFF_ETA_NOTICES_BOTH_ISBD_FIRST :
					$current->do_isbd();
					$current->do_public();
					$current->genere_double($depliable, 'ISBD') ;
					$retour_aff .= $current->result ;
					break ;
				default:
					$current->do_isbd();
					$current->do_public();					
					$current->genere_double($depliable, 'autre') ;
					$retour_aff .= $current->result ;
					break ;
			}
	}
	return $retour_aff;
}

function aff_serial_unimarc($id,$nocart=0, $entrepots_localisations=array()) {

	global $opac_notices_format;
	global $opac_notices_depliable;
	global $opac_cart_allow;
	global $opac_cart_only_for_subscriber;
	global $msg;


	if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) $cart=1; else $cart=0;
	if ($nocart) $cart=0;

	//Recherche des fonctions d'affichage
	//$f=get_aff_function();
	//if ($f) return $f($id,$cart);

	if ($id) {
		$current = new serial_affichage_unimarc($id,"",$cart,0, $entrepots_localisations);
		$depliable=$opac_notices_depliable;
		$current->do_header();

		if($current->notice_header == ""){
			$current->notice_header = sprintf($msg['cart_notice_expired'],$id);
			$current->notice_expired = true;
		}
		switch ($opac_notices_format) {
			case AFF_ETA_NOTICES_REDUIT :
				$retour_aff .= $current->notice_header." ";
				break;
			case AFF_ETA_NOTICES_ISBD :
				$current->do_isbd();
				$current->genere_simple($depliable, 'ISBD') ;
				$retour_aff .= $current->result ;
				break;
			case AFF_ETA_NOTICES_PUBLIC :
				$current->do_public();
				$current->genere_simple($depliable, 'PUBLIC') ;
				$retour_aff .= $current->result ;
				break;
			case AFF_ETA_NOTICES_BOTH :
				$current->do_isbd();
				$current->do_public();
				$current->genere_double($depliable, 'PUBLIC') ;
				$retour_aff .= $current->result ;
				break ;
			case AFF_ETA_NOTICES_BOTH_ISBD_FIRST :
				$current->do_isbd();
				$current->do_public();
				$current->genere_double($depliable, 'ISBD') ;
				$retour_aff .= $current->result ;
				break ;
			default:
				$current->do_isbd();
				$current->do_public();
				$current->genere_double($depliable, 'autre') ;
				$retour_aff .= $current->result ;
				break ;
		}
	}
	return $retour_aff;
}
?>