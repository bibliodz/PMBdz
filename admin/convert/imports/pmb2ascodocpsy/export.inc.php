<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: export.inc.php,v 1.1 2013-01-23 15:24:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function _export_($id,$keep_expl=0,$params=array()) {
	$requete="select * from notices where notice_id=$id";
	$resultat=mysql_query($requete);
	
	$rn=mysql_fetch_object($resultat);
	$notice = "";
	switch($rn->typdoc) {
		case "a" :
		case "h" :
			$notice .= _export_ouvrage_congres_($rn,$params);
			break;
		case "o" :
		case "r" :
			$notice .= _export_these_memoire_($rn,$params);
			break;
		case "m" :
			$notice .= _export_document_multimedia_($rn,$params);
			break;
		case "s" :
			$notice .= _export_article_($rn,$params);
			break;
		case "p" :
			$notice .= _export_periodique_($rn,$params);
			break;
		case "t" :
			$notice .= _export_texte_officiel_($rn, $params);
			break;
		case "q" :
			$notice .= _export_document_en_ligne_($rn, $params);
			break;
		default :
			break;
	}
	$notice .= "\n";
	
	return $notice;
}

function _export_article_($rn,$params=array()) {
	
	$notice.= "Article\t";

	//Auteurs
	$notice.= _make_export_authors($rn);
	$notice.="\t";
	
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";

	//P�riodique
	$notice.= _make_export_title_rev($rn);
	$notice.="\t";
	
	//Volume ou tome
	if ($rn->tnvol) {
	    $notice.=$rn->tnvol;
	}
	$notice.="\t";
	
	//Num�ro
	$notice.= _make_export_numero_bull($rn);
	$notice.="\t";
	
	//PDPF
	if ($rn->npages) {
	    $notice.=$rn->npages;
	}
	$notice.="\t";
	
	//Date de l'article
	if ($rn->year) {
		$notice.=$rn->year;
	}
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		if (_check_url_($rn->lien)) {
			$notice.=$rn->lien;
		}
	}
	$notice.="\t";
	
	//Notes G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);

	return $notice;
}
	
function _export_ouvrage_congres_($rn, $params=array()) {
	
	if($rn->typdoc == "h") {
		$notice.= "Congr�s\t";
	} else {
		$notice.= "Livre\t";
	}

	//Auteurs
	$notice.= _make_export_authors($rn);
	$notice.="\t";
	
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";

	//Congr�s
	$notice.= _make_export_congres($rn);
	$notice.="\t";
	
	//Editeur
	$notice.= _make_export_publishers_name($rn);
	$notice.="\t";
	
	//Lieu Editeur
	$notice.= _make_export_publishers_lieu($rn);
	$notice.="\t";
	
	//Collection
	if ($rn->coll_id) {
		$requete="select collection_name from collections where collection_id=".$rn->coll_id;
		$resultat=mysql_query($requete);
		$notice.=mysql_result($resultat,0,0);	
		if ($rn->nocoll) $notice.=" ;".$rn->nocoll;
	}
	$notice.="\t";
	
	//Mention d'�dition
	if ($rn->mention_edition) {
		$notice.=$rn->mention_edition;
	}
	$notice.="\t";
	
	//Nombre de pages
	if ($rn->npages) {
		$notice.=$rn->npages;
	} else {
		$notice.= "[s.p.]";
	}
	$notice.="\t";
	 
	//Date Year
	if ($rn->year) {
		$notice.=$rn->year;
	} else {
		$notice.= "[s.d.]";
	}
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me(s) 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Notes
	//G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//ISBNISSN
	if ($rn->code) {
		$notice.=$rn->code;
	}
	$notice.="\t";	
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);
	$notice.="\t";
	
	//Loc
	$notice .= _make_export_cp_loc($rn);

	return $notice;
}

function _export_periodique_($rn,$params=array()) {
	
	$notice.= "P�riodique\t";
	
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";

	//Vie du p�riodique
	if ($rn->year) {
		$notice.=$rn->year;
	} else {
		$notice.= "[s.d.]";
	}
	$notice.="\t";
	
	//Etat des collections des centres
	$requete = "select * from collections_state where id_serial=".$rn->notice_id;
	$resultat = mysql_query($requete);
	if (mysql_num_rows($resultat) > 0) {
		$res=mysql_fetch_object($resultat);
		$notice.= $res->location_id." : ".$res->collstate_lacune." ; ".$res->collstate_origine." ; ".$res->state_collections;	
	}
	$notice.="\t";
	
	//ISBNISSN
	if ($rn->code) {
		$notice.=$rn->code;
	}
	$notice.="\t";
	
	//Notes
	//G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);

	return $notice;
}

function _export_these_memoire_($rn,$params=array()) {

	if ($rn->typdoc == "r") $notice.= "M�moire\t";
	else $notice.= "Th�se\t";

	//Auteurs
	$notice.= _make_export_authors($rn);
	$notice.="\t";
	
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";
	
	//DIPSPE
	$notice.= _make_export_cp_dipspe($rn);
	$notice.="\t";
	
	//Nom Editeur
	$notice .= _make_export_publishers_name($rn);
	$notice.="\t";
	
	//Lieu Editeur
	$notice .= _make_export_publishers_lieu($rn);
	$notice.="\t";
	
	//Nombre de pages
	if ($rn->npages) {
		$notice.=$rn->npages;
	} else {
		$notice.= "[s.p.]";
	}
	$notice.="\t";
	 
	//Date Year
	if ($rn->year) {
		$notice.=$rn->year;
	} else {
		$notice.= "[s.d.]";
	}
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me(s) 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Notes
	//G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);
	$notice.="\t";

	//Prodfich
	$notice .= _make_export_cp_loc($rn);

	return $notice;
}

function _export_texte_officiel_($rn,$params=array()) {
	
	$notice.= "Texte officiel\t";
	
	//Nattext
	$notice .= _make_export_cp_nattext($rn);
	$notice.="\t";
	
	//Datetext
	$notice .= _make_export_cp_datetext($rn);
	$notice.= "\t";
	
	//Datepub
	if ($rn->date_parution) {
		$notice.= $rn->date_parution;	
	}
	$notice.= "\t";
	
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";
	
	//P�riodique
	$notice.= _make_export_title_rev($rn);
	$notice.="\t";
	
	//Num�ro du JO ou du BO
	$notice.= _make_export_numero_bull($rn);
	$notice.="\t";
	
	//Num�ro du texte officiel
	$notice.= _make_export_cp_numtexof($rn);
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me(s) 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Annexe
	$notice.= _make_export_cp_annexe($rn);
	$notice.="\t";
	
	//Lienanne
	$notice.= _make_export_cp_lienanne($rn);
	$notice.="\t";
	
	//Datesais
	if ($rn->create_date) {
		$notice.=$rn->create_date;
	}
	$notice.="\t";
	
	//Datevali
	if ($rn->update_date) {
		$notice.= $rn->update_date;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);

	return $notice;
}

function _export_document_en_ligne_($rn,$params=array()) {
	
	$notice.= "Rapport\t";

	//Auteurs
	$notice.= _make_export_authors($rn);
	$notice.="\t";
		
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";
	
	//Nom Editeur
	$notice.= _make_export_publishers_name($rn);
	$notice.="\t";
	
	//Lieu Editeur
	$notice.= _make_export_publishers_lieu($rn);
	$notice.="\t";
	
	//Collection
	if ($rn->coll_id) {
		$requete="select collection_name from collections where collection_id=".$rn->coll_id;
		$resultat=mysql_query($requete);
		$notice.=mysql_result($resultat,0,0);	
		if ($rn->nocoll) $notice.=" ;".$rn->nocoll;
	}
	$notice.="\t";
	
	//Mention d'�dition
	if ($rn->mention_edition) {
		$notice.=$rn->mention_edition;
	}
	$notice.="\t";
	
	//Nombre de pages
	if ($rn->npages) {
		$notice.=$rn->npages;
	} else {
		$notice.="[s.p.]";
	}
	$notice.="\t";

	//P�riodique
	$notice.= _make_export_title_rev($rn);
	$notice.="\t";

	//Volume ou tome
	if ($rn->tnvol) {
	    $notice.=$rn->tnvol;
	}
	$notice.="\t";
	
	//Num�ro
	$notice.=_make_export_numero_bull($rn);
	$notice.="\t";
	
	//Date Year
	if ($rn->year) {
		$notice.=$rn->year;
	} else {
		$notice.="[s.d.]";
	}
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me(s) 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Notes
	//G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//ISBNISSN
	if ($rn->code) {
		$notice.=$rn->code;
	}
	$notice.="\t";
	
	//Datesais
	if ($rn->create_date) {
		$notice.=$rn->create_date;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);

	return $notice;
}

function _export_document_multimedia_($rn,$params=array()) {

	$notice.= "Multim�dia\t";
	
	//Support
	$notice.="\t";
	
	//Auteurs
	$notice.= _make_export_authors($rn);
	$notice.="\t";
		
	//Titres
	$notice.= _make_export_title($rn);
	$notice.="\t";
	
	//Nom Editeur
	$notice.= _make_export_publishers_name($rn);
	$notice.="\t";
	
	//Lieu Editeur
	$notice.= _make_export_publishers_lieu($rn);
	$notice.="\t";
	
	//Date Year
	if ($rn->year) {
		$notice.=$rn->year;
	} else {
		$notice.= "[s.d.]";
	}
	$notice.="\t";
	
	//Mots_cl�s descripteur
	$notice.= _make_export_branch_thesaurus($rn, "MOTCLE");
	$notice.="\t";
	
	//Candidat Descripteur
	$notice.= _make_export_branch_thesaurus($rn, "CANDES");
	$notice.="\t";
	
	//Th�me(s) 
	$notice.= _make_export_branch_thesaurus($rn, "THEME");
	$notice.="\t";
	
	//Nom Propre
	$notice.= _make_export_branch_thesaurus($rn, "NOMP");
	$notice.="\t";
	
	//R�sum�
	if ($rn->n_resume) {
		$notice.=$rn->n_resume;
	}
	$notice.="\t";
	
	//Lien
	if ($rn->lien) {
		$notice.=$rn->lien;
	}
	$notice.="\t";
	
	//Notes
	//G�n�rale
	if ($rn->n_gen) {
		$notice.=$rn->n_gen;
	}
	$notice.="\t";
	
	//Prodfich
	$notice .= _make_export_cp_prodfich($rn);
	$notice.="\t";
	
	//Loc
	$notice .= _make_export_cp_loc($rn);
	
	return $notice;
}

function _make_export_authors($rn) {
	global $authors_function;
	
	$notice = "";
	
	//Auteurs (sauf congr�s : export�s dans la fonction _make_export_congres)
	$requete = "SELECT author_name, author_rejete, author_type, responsability_fonction, responsability_type ";
	$requete .= "FROM authors, responsability where responsability_notice=".$rn->notice_id." and responsability_author=author_id ";
	$requete .= "and author_type<>'72' ";
	$requete .= "ORDER BY responsability_type, responsability_ordre, author_type, responsability_fonction";
	$resultat=mysql_query($requete);
	if (!$authors_function) {
		$authors_function=array("205"=>"Collab.","901"=>"Coord.","651"=>"Dir.","340"=>"Ed.",
			"440"=>"Ill.","080"=>"Pr�f.","730"=>"Trad.","075"=>"Postf.");
	}
	$tmp_array = array();
	if (mysql_num_rows($resultat)) {
		for ($i=0; $i<mysql_num_rows($resultat); $i++) {
			$prenom = mysql_result($resultat,$i, 1);
			$tmp = "";
			//$tmp.= trim(str_replace("-"," ",mysql_result($resultat,$i, 0)));
			$tmp.= trim(mysql_result($resultat,$i, 0));
			if ($prenom) $tmp.= " ".$prenom;
			$func_author = mysql_result($resultat,$i, 3);
			if (array_key_exists($func_author, $authors_function)) {
				$tmp .= " ".$authors_function[$func_author];
			}
			$tmp_array[] = $tmp;	
		}
	}
	if (count($tmp_array)) $notice.= implode("/", $tmp_array);
	else $notice.= "[s.n.]";

	return $notice;
}

function _make_export_title($rn) {
	
	$notice = "";
	//Titres
	if ($rn->tit1) {
	    $notice.=str_replace("/", "-", ucfirst($rn->tit1));
		if ($rn->tit2) {
		    $notice.=" : ".$rn->tit2;
		}
	}
	return $notice;
}

function _make_export_title_rev($rn) {
	
	$notice = "";
	//Titre du p�riodique
	if ($rn->niveau_biblio=="a") {
		//R�cup�ration du titre du p�riodique
		$requete="select tit1 from notices, bulletins, analysis where analysis_notice=".$rn->notice_id." and analysis_bulletin=bulletin_id and bulletin_notice=notice_id";
		$resultat=mysql_query($requete);
		$r_perio=@mysql_fetch_object($resultat);
		if (($r_perio)&&($r_perio->tit1)) {
			$notice .= strtoupper($r_perio->tit1);
		}
	}
	return $notice;
}

function _make_export_numero_bull($rn) {
	
	$notice = "";
	//Num�ro de bulletin
	if ($rn->niveau_biblio=="a") {
		//R�cup�ration du num�ro de bulletin
		$requete="select bulletin_numero from notices, bulletins, analysis where analysis_notice=".$rn->notice_id." and analysis_bulletin=bulletin_id and bulletin_notice=notice_id";
		$resultat=mysql_query($requete);
		$r_bull=@mysql_fetch_object($resultat);
		if (($r_bull)&&($r_bull->bulletin_numero)) {
			$notice .= $r_bull->bulletin_numero;
		} else {
			$notice.= "[s.n.]";
		}
	} else {
		$notice.= "[s.n.]";
	}
	return $notice;
}

function _make_export_publishers_name($rn) {
	
	$notice = "";
	
	//Nom Editeur
	if ($rn->ed1_id || $rn->ed2_id) {
		if ($rn->ed1_id) {
		    $requete="select ed_name from publishers where ed_id=".$rn->ed1_id;
			$resultat=mysql_query($requete);
			$red=mysql_fetch_object($resultat);
			$notice.= ucfirst($red->ed_name);
		}
		if ($rn->ed2_id) {
		    $requete="select ed_name from publishers where ed_id=".$rn->ed2_id;
			$resultat=mysql_query($requete);
			$red=mysql_fetch_object($resultat);
			if ($rn->ed1_id) $notice.= "/"; 
			$notice.= ucfirst($red->ed_name);
		}
	} else {
		$notice.= "[s.n.]";	
	}
	
	return $notice;
}

function _make_export_publishers_lieu($rn) {
	
	$notice = "";
	
	//Lieu Editeur
	if ($rn->ed1_id || $rn->ed2_id) {
		if ($rn->ed1_id) {
		    $requete="select ed_ville from publishers where ed_id=".$rn->ed1_id;
			$resultat=mysql_query($requete);
			$red=mysql_fetch_object($resultat);
			$notice.= ucfirst($red->ed_ville);
		}
		if ($rn->ed2_id) {
		    $requete="select ed_ville from publishers where ed_id=".$rn->ed2_id;
			$resultat=mysql_query($requete);
			$red=mysql_fetch_object($resultat);
			if ($rn->ed1_id) $notice.= "/"; 
			$notice.= ucfirst($red->ed_ville);
		}
	} else {
		$notice.= "[s.l.]";
	}
	
	return $notice;
}

function _make_export_cp_prodfich($rn) {
	
	$notice = "";
	$tmp_array = array();
	$requete="select ncl.notices_custom_list_lib from notices_custom_lists ncl, notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_prodfich' and ncv.notices_custom_champ=ncl.notices_custom_champ and ncv.notices_custom_integer=ncl.notices_custom_list_value";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
	    for ($i=0; $i<mysql_num_rows($resultat); $i++) {
	    	$tmp_array[] = trim(strtolower(mysql_result($resultat,$i)));
		}
		$notice.= implode("/", $tmp_array);
	}
	
	return $notice;
}

function _make_export_cp_lienanne($rn) {
	
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_small_text from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_lienanne' ";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
	    for ($i=0; $i<mysql_num_rows($resultat); $i++) {
	    	$url = mysql_result($resultat,$i);
	    	if (_check_url_($url)) $tmp_array[] = $url;
		}
		$notice.= implode(";", $tmp_array);
	}
	
	return $notice;
}

function _make_export_cp_annexe($rn) {
	
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_small_text from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_annexe' ";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
	    for ($i=0; $i<mysql_num_rows($resultat); $i++) {
	    	$tmp_array[] = ucfirst(mysql_result($resultat,$i));
		}
		$notice.= implode(";", $tmp_array);
	}
	
	return $notice;
}

function _make_export_congres($rn) {
	$notice = "";
	
	//Congr�s
	$requete = "SELECT author_name, author_rejete, author_numero, author_lieu, author_date ";
	$requete .= "FROM authors, responsability where responsability_notice=".$rn->notice_id." and responsability_author=author_id ";
	$requete .= "and author_type='72' ";
	$requete .= "ORDER BY responsability_type, responsability_ordre, author_type, responsability_fonction";
	$resultat=mysql_query($requete);
	$tmp_array = array();
	if (mysql_num_rows($resultat)) {
		for ($i=0; $i<mysql_num_rows($resultat); $i++) {
			$notice.= mysql_result($resultat,$i, 0);
			$notice.="\t";
			$notice.= mysql_result($resultat,$i, 2);
			$notice.="\t";
			$notice.= mysql_result($resultat,$i, 3);
			$notice.="\t";
			$notice.= mysql_result($resultat,$i, 4);	
		}
	} else {
		$notice .= "\t\t\t";
	}

	return $notice;	
}	

function _make_export_cp_dipspe($rn) {
	
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_small_text from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_dipspe'";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$notice.= mysql_result($resultat, 0);
	}
	
	return $notice;
}

function _make_export_cp_loc($rn) {
	
	$notice = "";
	$tmp_array = array();
	$requete="select ncl.notices_custom_list_lib from notices_custom_lists ncl, notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and name='cp_loc' and ncv.notices_custom_champ=ncl.notices_custom_champ and ncv.notices_custom_integer=ncl.notices_custom_list_value";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
	    for ($i=0; $i<mysql_num_rows($resultat); $i++) {
	    	$tmp_array[] = trim(strtolower(mysql_result($resultat,$i)));
		}
		$notice.= implode("/", $tmp_array);
	}
	
	return $notice;
}

function _make_export_cp_nattext($rn) {
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_small_text from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_nattext'";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$notice.= strtoupper(mysql_result($resultat, 0));
	}
	
	return $notice;
}

function _make_export_cp_datetext($rn) {
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_date from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_datetext'";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$notice.= strtoupper(mysql_result($resultat, 0));
	}
	
	return $notice;
}

function _make_export_cp_numtexof($rn) {
	$notice = "";
	$tmp_array = array();
	$requete="select ncv.notices_custom_small_text from notices_custom_values ncv, notices_custom nc where ncv.notices_custom_origine=".$rn->notice_id." and ncv.notices_custom_champ=nc.idchamp and nc.name='cp_numtexof'";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$notice.= strtoupper(mysql_result($resultat, 0));
	}
	
	return $notice;
}

function _make_export_branch_thesaurus($rn, $name) {
	global $dbh;
	
	$notice = "";
	$requete = "SELECT libelle_categorie FROM categories, notices_categories, noeuds ";
	$requete .= "WHERE notcateg_notice=".$rn->notice_id." AND categories.num_noeud = notices_categories.num_noeud ";
	$requete .= "AND notices_categories.num_noeud = noeuds.id_noeud ";
	$requete .= "AND noeuds.num_parent IN (select num_noeud from categories where libelle_categorie='".$name."') ";
	$requete .= "ORDER BY ordre_categorie, libelle_categorie ";
	$resultat=mysql_query($requete, $dbh);
	
	if (mysql_num_rows($resultat)) {
	    for ($i=0; $i<mysql_num_rows($resultat); $i++) {
	    	$tmp_array[] = trim(mysql_result($resultat,$i));
		}
		$notice.= strtoupper(implode("/", $tmp_array));
	}
	
	return $notice;
}

function _check_url_($url) {
	if ((substr(trim($url), 0, 7) == "http://") || (substr(trim($url), 0, 8) == "https://")) return true;
	else return false; 
}
?>