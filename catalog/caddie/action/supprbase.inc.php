<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: supprbase.inc.php,v 1.18 2012-07-27 14:21:44 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if($idcaddie) {
	$myCart= new caddie($idcaddie);
	print pmb_bidi(aff_cart_titre ($myCart));
	switch ($action) {
		case 'choix_quoi':
			print aff_cart_nb_items ($myCart) ;
			print aff_choix_quoi ("./catalog.php?categ=caddie&sub=action&quelle=supprbase&action=del_base&idcaddie=$idcaddie",
				"./catalog.php?categ=caddie&sub=action&quelle=supprbase&action=&idcaddie=0", 
				$msg["caddie_choix_supprbase"], 
				$msg["supprimer"], 
				"return confirm('$msg[caddie_confirm_supprbase]')",false,$myCart->type);
		break;
		case 'del_base':
			print "<br /><h3>".$msg['caddie_situation_before_suppr']."</h3>";
			print aff_cart_nb_items ($myCart);			
			$res_aff_suppr_base = "" ;			
			$liste_0=$liste_1= array();
			if ($elt_flag) {
				$liste_0 = $myCart->get_cart("FLAG", $elt_flag_inconnu) ;
			}	
			if ($elt_no_flag) {
				$liste_1= $myCart->get_cart("NOFLAG", $elt_no_flag_inconnu) ;
			}	
			$liste= array_merge($liste_0,$liste_1);
			if($liste) {
				// le formulaire demande de supprimer les notices meme avec liens
				if($supp_notice_linked) $forcage['notice_linked']=1;
				if($supp_notice_linked_expl_num) $forcage['notice_linked_expl_num']=1;
				if($source_id) $forcage['source_id']=$source_id;
				if($supp_notice_perio_abo) $forcage['notice_perio_abo']=1;
				if($supp_notice_perio_collstat) $forcage['notice_perio_collstat']=1;
				if($supp_notice_perio_modele) $forcage['notice_perio_modele']=1;
				if($supp_bulletin_linked_expl_num) $forcage['bulletin_linked_expl_num']=1;
				while(list($cle, $object) = each($liste)) {
					// le formulaire demande de suprimmer toutes les notices li�es � celle-ci
					if($supp_notice_linked_cascade) {
						$forcage['notice_linked']=1;
						$liste_linked=notice::get_list_child($object);
						foreach($liste_linked as $object) {
							if ($myCart->del_item_base($object,$forcage)==CADDIE_ITEM_SUPPR_BASE_OK) 
								$myCart->del_item_all_caddies ($object, $myCart->type) ;
							else { 
								$res_aff_suppr_base .= aff_cart_unique_object ($object, $myCart->type, $url_base="./catalog.php?categ=caddie&sub=gestion&quoi=panier&idcaddie=$idcaddie" ) ;
							}
						}
					} else {
						if ($myCart->del_item_base($object,$forcage)==CADDIE_ITEM_SUPPR_BASE_OK) $myCart->del_item_all_caddies ($object, $myCart->type) ;
						else { 
							$res_aff_suppr_base .= aff_cart_unique_object ($object, $myCart->type, $url_base="./catalog.php?categ=caddie&sub=gestion&quoi=panier&idcaddie=$idcaddie" ) ;
						}
					}	
				}
			}
			if ($res_aff_suppr_base) {
				print "<br /><h3>".$msg['caddie_supprbase_elt_used']."</h3>";
				// inclusion du javascript de gestion des listes d�pliables
				// d�but de liste
				print $begin_result_liste;
				print $res_aff_suppr_base ;
				print $end_result_liste;
			}
			print "<br /><h3>".$msg['caddie_situation_after_suppr']."</h3>";
			$myCart->compte_items();
			print aff_cart_nb_items ($myCart) ;
			break;
		default:
			break;
	}

} else aff_paniers($idcaddie, "NOTI", "./catalog.php?categ=caddie&sub=action&quelle=supprbase", "choix_quoi", $msg['caddie_select_supprbase'], "", 0, 0, 0);
