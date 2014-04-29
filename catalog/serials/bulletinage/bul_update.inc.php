<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bul_update.inc.php,v 1.31 2013-12-11 12:25:26 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if($gestion_acces_active==1) {
	require_once("$class_path/acces.class.php");
	$ac= new acces();
}

//verification des droits de modification notice
$acces_m=1;
if ($gestion_acces_active==1 && $gestion_acces_user_notice==1) {
	$dom_1= $ac->setDomain(1);
	$acces_m = $dom_1->getRights($PMBuserid,$serial_id,8);
}

if ($acces_m==0) {
	
	if (!$bul_id) {
		error_message('', htmlentities($dom_1->getComment('mod_seri_error'), ENT_QUOTES, $charset), 1, '');
	} else {
		error_message('', htmlentities($dom_1->getComment('mod_bull_error'), ENT_QUOTES, $charset), 1, '');
	}
		
} else {

	
	// script d'update d'un bulletinage
	
	// nettoyage des valeurs du form
	// les valeurs passees sont mises en tableau pour etre passees
	// a la methode de mise a jour
	$table = array();
	$table['bul_no']      = clean_string($bul_no);
	$table['bul_date']    = clean_string($bul_date);
	$table['date_date']    = extraitdate($date_date_lib) ;
	
	$table['bul_cb']    = clean_string($bul_cb);
	$table['bul_titre'] = $bul_titre ;
	
	// mise a jour de l'entete de page
	echo str_replace('!!page_title!!', $msg[4000].$msg[1003].$msg['catalog_serie_modif_bull'], $serial_header);
	
	// nettoyage des valeurs du form
	$f_tit1 = clean_string($f_tit1);
	$f_tit3 = clean_string($f_tit3);
	$f_tit4 = clean_string($f_tit4);
	//$f_n_gen = clean_string($f_n_gen);
	//$f_n_resume = clean_string($f_n_resume);
	//$f_indexation = clean_string($f_indexation);
	$f_lien = clean_string($f_lien);
	$f_eformat = clean_string($f_eformat);
	
	// les valeurs passees sont mises en tableau pour etre passees
	// a la methode de mise � jour
	//$table = array();
	$table['typdoc']        = $typdoc;
	$table['statut']        = $form_notice_statut;
	$table['commentaire_gestion'] =  $f_commentaire_gestion ;
	$table['thumbnail_url'] =  $f_thumbnail_url ;
	$table['code']          = $f_cb;
	$table['tit1']          = $table["bul_no"].($table["bul_date"]?" - ".$table["bul_date"]:"").($table["bul_titre"]?" - ".$table["bul_titre"]:"");
	$table['tit3']          = $f_tit3;
	$table['tit4']          = $f_tit4;
	
	// auteur principal
	$f_aut[] = array (
			'id' => $f_aut0_id,
			'fonction' => $f_f0_code,
			'type' => '0',
			'ordre' => 0 );
	// autres auteurs
	for ($i=0; $i<$max_aut1; $i++) {
		$var_autid = "f_aut1_id$i" ;
		$var_autfonc = "f_f1_code$i" ;
		$f_aut[] = array (
				'id' => $$var_autid,
				'fonction' => $$var_autfonc,
				'type' => '1',
				'ordre' => $i );
	}
	// auteurs secondaires
	for ($i=0; $i<$max_aut2 ; $i++) {
	
		$var_autid = "f_aut2_id$i" ;
		$var_autfonc = "f_f2_code$i" ;
		$f_aut[] = array (
				'id' => $$var_autid,
				'fonction' => $$var_autfonc,
				'type' => '2',
				'ordre' => $i );
	}
	
	$table['aut'] = $f_aut;
	
	/*
	 * Les liens entre notices
	 */
	$f_rel=array();
	for ($i=0; $i<$max_rel; $i++) {
		$f_rel_type_dir="f_rel_type_".$i;
		$f_rel_type_dir=explode('-', ${$f_rel_type_dir});
		$f_rel_id="f_rel_id_".$i;
		$f_rel_rank="f_rel_rank_".$i;
		$f_rel_type=$f_rel_type_dir[0];
		$f_rel_direction=$f_rel_type_dir[1];

		$f_rel_rank=${$f_rel_rank};
		$f_rel_id=${$f_rel_id};
		
		$f_rel[]=array(
				'relation_direction'=>$f_rel_direction,
				'id_notice'=>$f_rel_id,
				'rank'=>$f_rel_rank,
				'relation_type'=>$f_rel_type);
	}				
	
	$table['rel']=$f_rel;
	
	$table['ed1_id']        = $f_ed1_id;
	$table['ed2_id']        = $f_ed2_id;
	$table['n_gen']         = $f_n_gen;
	$table['n_contenu']		= $f_n_contenu;
	$table['n_resume']      = $f_n_resume;
	
// categories		
	if($tab_categ_order){
		$categ_order=explode(",",$tab_categ_order);
		$order=0;
		foreach($categ_order as $old_order){
			$var_categid = "f_categ_id$old_order" ;
			if($var_categid){
				$f_categ[] = array (
						'id' => $$var_categid,
						'ordre' => $order );
				$order++;
			}	
		}
	}else{
		for ($i=0; $i< $max_categ ; $i++) {
			$var_categid = "f_categ_id$i" ;
			$f_categ[] = array (
					'id' => $$var_categid,
					'ordre' => $i );
		}
	}
	
	$table['categ']=$f_categ;
	
	$table['indexint']      = $f_indexint_id;
	$table['index_l']       = clean_tags($f_indexation);
	$table['lien']          = $f_lien;
	$table['eformat']       = $f_eformat;
	$table['niveau_biblio'] = $b_level;
	$table['niveau_hierar'] = $h_level;
	$table['ill']			= $f_ill;
	$table['size']			= $f_size;
	$table['prix']			= $f_prix;
	$table['accomp']		= $f_accomp;
	$table['npages']		= $f_npages;
	$table['indexation_lang'] = $indexation_lang;
	
	if($table['date_date'] == '0000-00-00' || !isset($date_date_lib)) $table['year'] = "";
	else $table['year'] = substr($table['date_date'],0,4);
	
	$table['date_parution'] = $table['date_date'];
		
	$p_perso=new parametres_perso("notices");
	$nberrors=$p_perso->check_submited_fields();
	
	$table['force_empty'] = $p_perso->presence_exclusion_fields();
	
	if (!$nberrors) {
		$myBulletinage = new bulletinage($bul_id, $serial_id);
		$result = $myBulletinage->update($table);
	} else {
		error_message_history($msg["notice_champs_perso"],$p_perso->error_message,1);
		exit();
	}
	
	$update_result=$myBulletinage->bull_num_notice;
	
	if ($update_result) {
		//Traitement des liens
		$requete="
		DELETE notices_relations FROM notices_relations
		LEFT OUTER JOIN bulletins ON bulletins.num_notice=notices_relations.num_notice AND bulletins.bulletin_notice=notices_relations.linked_notice
		WHERE (notices_relations.num_notice=$update_result OR notices_relations.linked_notice=$update_result)
		AND (bulletin_notice IS NULL OR bulletins.bulletin_notice!=$serial_id)";
		mysql_query($requete);	
		foreach($table['rel'] as $rel){
			if ($rel['id_notice']) {
				if($rel['relation_direction']=='up'){
					$requete="INSERT INTO notices_relations VALUES('$update_result','".$rel['id_notice']."','".$rel['relation_type']."','".$rel['rank']."')";
					@mysql_query($requete);
				}elseif($rel['relation_direction']=='down'){
					$requete="INSERT INTO notices_relations VALUES('".$rel['id_notice']."','$update_result','".$rel['relation_type']."','".$rel['rank']."')";
					@mysql_query($requete);
				}
			}
		}
		
		// traitement des auteurs
		$rqt_del = "DELETE FROM responsability WHERE responsability_notice='$update_result' ";
		$res_del = mysql_query($rqt_del, $dbh);
		$rqt_ins = "INSERT INTO responsability (responsability_author, responsability_notice, responsability_fonction, responsability_type, responsability_ordre) VALUES ";
		$i=0;
		while ($i<=count ($f_aut)-1) {
			$id_aut=$f_aut[$i]['id'];
			if ($id_aut) {
				$fonc_aut=$f_aut[$i]['fonction'];
				$type_aut=$f_aut[$i]['type'];
				$ordre_aut = $f_aut[$i]['ordre'];
				$rqt = $rqt_ins . " ('$id_aut','$update_result','$fonc_aut','$type_aut', $ordre_aut) " ; 
				$res_ins = @mysql_query($rqt, $dbh);
			}
			$i++;
		}
	
		// traitement des categories
		$rqt_del = "DELETE FROM notices_categories WHERE notcateg_notice='$update_result' ";
		$res_del = mysql_query($rqt_del, $dbh);
		$rqt_ins = "INSERT INTO notices_categories (notcateg_notice, num_noeud, ordre_categorie) VALUES ";
		while (list ($key, $val) = each ($f_categ)) {
			$id_categ=$val['id'];
			if ($id_categ) {
				$ordre_categ = $val['ordre'];
				$rqt = $rqt_ins . " ('$update_result','$id_categ', $ordre_categ ) " ; 
				$res_ins = @mysql_query($rqt, $dbh);
			}
		}
	
	
		// traitement des langues
		// langues
		$f_lang_form = array();
		$f_langorg_form = array() ;
		for ($i=0; $i< $max_lang ; $i++) {
			$var_langcode = "f_lang_code$i" ;
			if ($$var_langcode) $f_lang_form[] =  array ('code' => $$var_langcode,'ordre' => $i);
		}
	
		// langues originales
		for ($i=0; $i< $max_langorg ; $i++) {
			$var_langorgcode = "f_langorg_code$i" ;
			if ($$var_langorgcode) $f_langorg_form[] =  array ('code' => $$var_langorgcode,'ordre' => $i);
		}
	
		$rqt_del = "delete from notices_langues where num_notice='$update_result' ";
		$res_del = mysql_query($rqt_del, $dbh);
		$rqt_ins = "insert into notices_langues (num_notice, type_langue, code_langue, ordre_langue) VALUES ";
		while (list ($key, $val) = each ($f_lang_form)) {
			$tmpcode_langue=$val['code'];
			if ($tmpcode_langue) {
				$tmpordre_langue = $val['ordre'];
				$rqt = $rqt_ins . " ('$update_result',0, '$tmpcode_langue', $tmpordre_langue) " ; 
				$res_ins = mysql_query($rqt, $dbh);
			}
		}
		
		// traitement des langues originales
		$rqt_ins = "insert into notices_langues (num_notice, type_langue, code_langue, ordre_langue) VALUES ";
		while (list ($key, $val) = each ($f_langorg_form)) {
			$tmpcode_langue=$val['code'];
			if ($tmpcode_langue) {
				$tmpordre_langue = $val['ordre'];
				$rqt = $rqt_ins . " ('$update_result',1, '$tmpcode_langue', $tmpordre_langue) " ; 
				$res_ins = @mysql_query($rqt, $dbh);
			}
		}
		
		//Traitement des champs perso
		$p_perso->rec_fields_perso($update_result);
		// Mise � jour de la table notices_global_index
		notice::majNoticesGlobalIndex($update_result);
		// Mise � jour de la table notices_mots_global_index
		notice::majNoticesMotsGlobalIndex($update_result);
		
		if ($gestion_acces_active==1 && $myBulletinage->bull_num_notice) {
			
			//mise a jour des droits d'acces user_notice (idem notice mere perio)
			if ($gestion_acces_user_notice==1) {
				$q = "replace into acces_res_1 select $myBulletinage->bull_num_notice, res_prf_num, usr_prf_num, res_rights, res_mask from acces_res_1 where res_num=".$myBulletinage->bulletin_notice;
				mysql_query($q, $dbh);
			} 
	
			//mise a jour des droits d'acces empr_notice 
			if ($gestion_acces_empr_notice==1) {
				$dom_2 = $ac->setDomain(2);
				if ($bul_id) {	
					$dom_2->storeUserRights(1, $myBulletinage->bull_num_notice, $res_prf, $chk_rights, $prf_rad, $r_rad);
				} else {
					$dom_2->storeUserRights(0, $myBulletinage->bull_num_notice, $res_prf, $chk_rights, $prf_rad, $r_rad);
				}
			} 
		}
		
	}
	if($result) {
		print "<div class='row'><div class='msg-perio'>".$msg["maj_encours"]."</div></div>";
		$retour = "./catalog.php?categ=serials&sub=view&sub=bulletinage&action=view&bul_id=$result";
		print "
			<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"$retour\" style=\"display:none\">
				<input type=\"hidden\" name=\"id_form\" value=\"$id_form\">
			</form>
			<script type=\"text/javascript\">document.dummy.submit();</script>
			";
	} else {
		error_message($msg['catalog_serie_modif_bull'] , $msg['catalog_serie_modif_bull_imp'], 1, "./catalog.php?categ=serials&sub=view&serial_id=$serial_id");
	}

}
?>