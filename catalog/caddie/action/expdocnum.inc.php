<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: expdocnum.inc.php,v 1.3 2011-05-09 08:19:16 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once ("$include_path/explnum.inc.php");  

if($idcaddie) {
	$myCart= new caddie($idcaddie);
	print pmb_bidi(aff_cart_titre ($myCart));
	switch ($action) {
		case 'choix_quoi':
			print aff_cart_nb_items ($myCart) ;
			print aff_choix_quoi ("./catalog.php?categ=caddie&sub=action&quelle=expdocnum&action=export&idcaddie=$idcaddie", "./catalog.php?categ=caddie&sub=action&quelle=expdocnum&action=&idcaddie=0", $msg["caddie_choix_expdocnum"], $msg["caddie_expdocnum_export"], "return confirm('$msg[caddie_confirm_export]')");
			break;
		case 'export':
			print "<br /><h3>$msg[caddie_situation_exportdocnum]</h3>";
			print aff_cart_nb_items ($myCart) ;
	
			// v�rifier et/ou cr�er le r�pertoire $chemin
			$chemin_export_doc_num=$base_path."/temp/cart".$idcaddie."/";
			$handledir = @opendir($chemin_export_doc_num);
			if (!$handledir) {
				if (!mkdir($chemin_export_doc_num)) die ("Unsufficient privileges on temp directory");
			} else closedir($handledir);
			
			$res_aff_exp_doc_num="";
			if ($elt_flag) {
				$liste = $myCart->get_cart("FLAG", $elt_flag_inconnu) ;
				while(list($cle, $object) = each($liste)) {
					$res_aff_exp_doc_num.=$myCart->export_doc_num ($object,$chemin_export_doc_num,$pattern_nom_fichier_doc_num) ;
				}
			}
			if ($elt_no_flag) {
				$liste = $myCart->get_cart("NOFLAG", $elt_no_flag_inconnu) ;
				while(list($cle, $object) = each($liste)) {
					$res_aff_exp_doc_num.=$myCart->export_doc_num ($object,$chemin_export_doc_num,$pattern_nom_fichier_doc_num) ;
				}
			}
			if ($res_aff_exp_doc_num) {
				print "<br /><h3>$msg[caddie_res_expdocnum]</h3>";
				print $res_aff_exp_doc_num;
			} else print "<br /><h3>$msg[caddie_res_expdocnum_nodocnum]</h3>";
			break;
		default:
			break;
		}

	} else aff_paniers($idcaddie, "NOTI", "./catalog.php?categ=caddie&sub=action&quelle=expdocnum", "choix_quoi", $msg["caddie_select_expdocnum"], "", 0, 0, 0);

	
	
/*
 *     explnumid_idnotice_idbulletin_indicedocnum_nomdoc.extention

 

o� : 
	explnumid serait (sur 6 chiffres) l'id du document num�rique
    idnotice serait (sur 6 chiffres) l'id de la notice tel qu'il est export� dans l'export UNIMARC TXT
    idbulletin serait (sur 6 chiffres) l'id du bulletin (et dans ce cas idnotice serait l'id de la notice m�re du bulletin)

    indicedocnum serait un chiffre allant de 001 � 00n en fonction du ni�me document num�rique attach� � cette notice

    nomdoc: nom du document tel que d�fini lors de la cr�ation de l'attachement

    extension: telle que donn�e lors de la cr�ation si existante, sinon en fonction du mimetype

			
 * 
 */