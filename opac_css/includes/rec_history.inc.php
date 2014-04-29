<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: rec_history.inc.php,v 1.32 2012-09-12 15:17:39 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path,$include_path,$class_path,$msg;
require_once($base_path."/classes/search.class.php");

//Enregistrement de l'historique en fonction du type de recherche
function rec_history() {
	global $search_type;
	global $opac_search_other_function;

	switch ($search_type) {
		case "simple_search":
			global $user_query;
			global $look_TITLE,
	       		$look_AUTHOR,
	      	 	$look_PUBLISHER,
	      	 	$look_TITRE_UNIFORME,
	       		$look_COLLECTION,
	       		$look_SUBCOLLECTION,
	       		$look_CATEGORY,
	       		$look_INDEXINT,
	       		$look_KEYWORDS,
	       		$look_ABSTRACT,
	       		$look_ALL,
	       		$look_DOCNUM,
	       		$look_CONTENT;
	       	global $typdoc,$l_typdoc;
	     
			$_SESSION["nb_queries"]=$_SESSION["nb_queries"]+1;
			$n=$_SESSION["nb_queries"];
			$_SESSION["user_query".$n]=$user_query;
			$_SESSION["typdoc".$n]=$typdoc;
			$_SESSION["look_TITLE".$n]=$look_TITLE;
	       	$_SESSION["look_AUTHOR".$n]=$look_AUTHOR;
	      	$_SESSION["look_PUBLISHER".$n]=$look_PUBLISHER;
	      	$_SESSION["look_TITRE_UNIFORME".$n]=$look_TITRE_UNIFORME;
	       	$_SESSION["look_COLLECTION".$n]=$look_COLLECTION;
	       	$_SESSION["look_SUBCOLLECTION".$n]=$look_SUBCOLLECTION;
	        $_SESSION["look_CATEGORY".$n]=$look_CATEGORY;
	       	$_SESSION["look_INDEXINT".$n]=$look_INDEXINT;
	       	$_SESSION["look_KEYWORDS".$n]=$look_KEYWORDS;
	       	$_SESSION["look_ABSTRACT".$n]=$look_ABSTRACT;
	       	$_SESSION["look_CONTENT".$n]=$look_CONTENT;
	       	$_SESSION["look_DOCNUM".$n]=$look_DOCNUM;
	       	$_SESSION["look_ALL".$n]=$look_ALL;
	       	$_SESSION["search_type".$n]=$search_type;
	       	$_SESSION["l_typdoc".$n]=$l_typdoc;
	       	$_SESSION["level1".$n]=$_SESSION["level1"];
	       	if ($opac_search_other_function) search_other_function_rec_history($n);
	       	
			break;
		case "extended_search":
			global $es;
			$_SESSION["nb_queries"]=$_SESSION["nb_queries"]+1;
			$n=$_SESSION["nb_queries"];
			$_SESSION["human_query".$n]=$es->make_human_query();
			global $search;
			$_SESSION["nb_search".$n]=count($search);
			for ($i=0; $i<count($search); $i++) {
				$_SESSION["search_".$i."_".$n]=$search[$i];
				$inter="inter_".$i."_".$search[$i];
				global $$inter;
				$_SESSION["inter_".$i."_".$search[$i]."_".$n]=$$inter;
				$op="op_".$i."_".$search[$i];
				global $$op;
				$_SESSION["op_".$i."_".$search[$i]."_".$n]=$$op;
				$field_="field_".$i."_".$search[$i];
    			global $$field_;
    			$field=$$field_;
    			$_SESSION["n_fields_".$i."_".$search[$i]."_".$n]=count($field);
    			for ($j=0; $j<count($field); $j++) {
    				$_SESSION["field_".$i."_".$search[$i]."_".$j."_".$n]=$field[$j];
    			}
				$fieldvar_="fieldvar_".$i."_".$search[$i];
    			global $$fieldvar_;
    			$fieldvar=$$fieldvar_;
    			$_SESSION["fieldvar_".$i."_".$search[$i]."_".$n]=$fieldvar;
			}
			$_SESSION["search_type".$n]=$search_type;
			break;
		case "term_search":
			global $search_term;
			global $term_click;
			global $page_search;
			$_SESSION["nb_queries"]=$_SESSION["nb_queries"]+1;
			$n=$_SESSION["nb_queries"];
			$_SESSION["search_type".$n]=$search_type;
			$_SESSION["search_term".$n]=stripslashes($search_term);
			$_SESSION["term_click".$n]=stripslashes($term_click);
			$_SESSION["page_search".$n]=$page_search;
			$_SESSION["l_typdoc".$n]=$l_typdoc;
			break;	
	}
}

function get_history($n) {
	global $search_type;
	global $opac_search_other_function;
	
	$search_type=$_SESSION["search_type".$n];
	
	switch ($search_type) {
		case "simple_search":
			global $user_query;
			global $look_TITLE,
	       		$look_AUTHOR,
	      	 	$look_PUBLISHER,
	      	 	$look_TITRE_UNIFORME,
	       		$look_COLLECTION,
	       		$look_SUBCOLLECTION,
	       		$look_CATEGORY,
	       		$look_INDEXINT,
	       		$look_KEYWORDS,
	       		$look_ABSTRACT,
	       		$look_DOCNUM,
	       		$look_ALL,
	       		$look_CONTENT;
	       	global $typdoc,$l_typdoc;
	       	
			$user_query=$_SESSION["user_query".$n];
			$typdoc=$_SESSION["typdoc".$n];
			$look_TITLE=$_SESSION["look_TITLE".$n];
	       	$look_AUTHOR=$_SESSION["look_AUTHOR".$n];
	      	$look_PUBLISHER=$_SESSION["look_PUBLISHER".$n];
	      	$look_TITRE_UNIFORME=$_SESSION["look_TITRE_UNIFORME".$n];
	       	$look_COLLECTION=$_SESSION["look_COLLECTION".$n];
	       	$look_SUBCOLLECTION=$_SESSION["look_SUBCOLLECTION".$n];
	        $look_CATEGORY=$_SESSION["look_CATEGORY".$n];
	       	$look_INDEXINT=$_SESSION["look_INDEXINT".$n];
	       	$look_KEYWORDS=$_SESSION["look_KEYWORDS".$n];
	       	$look_ABSTRACT=$_SESSION["look_ABSTRACT".$n];
	       	$look_ALL=$_SESSION["look_ALL".$n];
	       	$look_DOCNUM=$_SESSION["look_DOCNUM".$n];
	       	$look_CONTENT=$_SESSION["look_CONTENT".$n];			
	       	$l_typdoc=$_SESSION["l_typdoc".$n];
	       	$_SESSION["level1"]=$_SESSION["level1".$n];
	       	if ($opac_search_other_function) search_other_function_get_history($n);
	       	
			break;
		case "extended_search":
			global $search;
			for ($i=0; $i<$_SESSION["nb_search".$n]; $i++) {
				$search[$i]=$_SESSION["search_".$i."_".$n];
				$inter="inter_".$i."_".$search[$i];
				global $$inter;
				$$inter=$_SESSION["inter_".$i."_".$search[$i]."_".$n];
				$op="op_".$i."_".$search[$i];
				global $$op;
				$$op=$_SESSION["op_".$i."_".$search[$i]."_".$n];
    			$n_fields=$_SESSION["n_fields_".$i."_".$search[$i]."_".$n];
    			for ($j=0; $j<$n_fields; $j++) {
    				$field[$j]=$_SESSION["field_".$i."_".$search[$i]."_".$j."_".$n];
    			}
    			$field_="field_".$i."_".$search[$i];
    			global $$field_;
    			$$field_=$field;
    			$fieldvar=$_SESSION["fieldvar_".$i."_".$search[$i]."_".$n];
    			$fieldvar_="fieldvar_".$i."_".$search[$i];
    			global $$fieldvar_;
    			$$fieldvar_=$fieldvar;
			}
			break;
		case "term_search":
			global $search_term;
			global $term_click;
			global $page_search;
			
			$search_term=$_SESSION["search_term".$n];
			$term_click=$_SESSION["term_click".$n];
			$page_search=$_SESSION["page_search".$n];
			break;
	}
	$_SESSION["search_type"]=$search_type;
}

function get_human_query($n) {
	global $msg;
	global $opac_search_other_function, $opac_indexation_docnum_allfields;
	global $include_path;
	
	if ($opac_search_other_function) require_once($include_path."/".$opac_search_other_function);
	
	switch ($_SESSION["search_type".$n]) {
		case "simple_search":
			if ($_SESSION["look_TITLE".$n]) $r1.=$msg["titles"]." ";
			if ($_SESSION["look_AUTHOR".$n]) $r1.=$msg["authors"]." ";
			if ($_SESSION["look_PUBLISHER".$n]) $r1.=$msg["publishers"]." ";
			if ($_SESSION["look_TITRE_UNIFORME".$n]) $r1.=$msg["titres_uniformes"]." ";
			if ($_SESSION["look_COLLECTION".$n]) $r1.=$msg["collections"]." ";
			if ($_SESSION["look_SUBCOLLECTION".$n]) $r1.=$msg["subcollections"]." ";
			if ($_SESSION["look_CATEGORY".$n]) $r1.=$msg["categories"]." ";
			if ($_SESSION["look_INDEXINT".$n]) $r1.=$msg["indexint"]." ";
			if ($_SESSION["look_KEYWORDS".$n]) $r1.=$msg["keywords"]." ";
			if ($_SESSION["look_ABSTRACT".$n]) $r1.=$msg["abstract"]." ";
			if ($_SESSION["look_ALL".$n]) $r1.=$msg["tous"]." ".($opac_indexation_docnum_allfields ? "[".$msg[docnum_search_with]."] " : '');
			if ($_SESSION["look_DOCNUM".$n]) $r1.=$msg["docnum"]." ";
			if ($_SESSION["look_CONTENT".$n]) $r1.=" ";
			if ($_SESSION["typdoc".$n]) {
				$doctype = new marc_list('doctype');
				$r2=sprintf($msg["simple_search_history_doc_type"],$doctype->table[$_SESSION["typdoc".$n]]);
			} else $r2=$msg["simple_search_history_all_doc_types"];
			if ($opac_search_other_function) {
				$r3=search_other_function_human_query($n);
				if ($r3) $r2.=", ".$r3;
			}
			$r=sprintf($msg["simple_search_history"],stripslashes($_SESSION["user_query".$n]),$r1,$r2);
			break;
		case "extended_search":
			$r=sprintf($msg["extended_search_history"],stripslashes($_SESSION["human_query".$n]));
			break;
		case "term_search":
			if ($_SESSION["search_term".$n]=="") $r1="(tous les termes)"; else $r1=stripslashes($_SESSION["search_term".$n]);
			$r=sprintf($msg["term_search_history"],$r1,($_SESSION["page_search".$n]+1),$_SESSION["term_click".$n]);
			break;
		case "module":
			$r=sprintf($msg["navigation_search_libelle"],stripslashes($_SESSION["human_query".$n]));
		break;
	}
	return $r;
}

function get_human_query_level_two($n) {
	global $msg;
	global $opac_search_other_function, $opac_indexation_docnum_allfields;
	global $include_path;
	
	if ($opac_search_other_function) require_once($include_path."/".$opac_search_other_function);
	
	if ($_SESSION["search_type".$n]=="simple_search") {
		$valeur_champ="";
		switch ($_SESSION["notice_view".$n]["search_mod"]) {
			case 'abstract':
				$r1=$msg["abstract"]." ";
			break;
			case 'title':
				$r1=$msg["title_search"]." ";
			break;
			case 'all':
				$r1=$msg["global_search"]." ".($opac_indexation_docnum_allfields ? "[".$msg[docnum_search_with]."] " : '');
			break;
			case 'keyword':
				$r1=$msg["keyword_search"]." ";
			break;
			case 'categ_see':
				$categ_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select libelle_categorie from categories where num_noeud=".$categ_id;
				$r_cat=mysql_query($requete);
				if (@mysql_num_rows($r_cat)) {
					$valeur_champ=mysql_result($r_cat,0,0);
				}
				$r1=$msg["category"]." ";
			break;
			case 'author_see':
				$author_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select concat(author_name,', ',author_rejete) from authors where author_id=".$author_id;
				$r_author=mysql_query($requete);
				if (@mysql_num_rows($r_author)) {
					$valeur_champ=mysql_result($r_author,0,0);
				}
				$r1=$msg["author_search"]." ";
			break;
			case 'indexint_see':
				$indexint_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select indexint_name from indexint where indexint_id=".$indexint_id;
				$r_indexint=mysql_query($requete);
				if (@mysql_num_rows($r_indexint)) {
					$valeur_champ=mysql_result($r_indexint,0,0);
				}
				$r1=$msg["indexint_search"]." ";
			break;
			case 'publisher_see':
				$publisher_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select ed_name from publishers where ed_id=".$publisher_id;
				$r_pub=mysql_query($requete);
				if (@mysql_num_rows($r_pub)) {
					$valeur_champ=mysql_result($r_pub,0,0);
				}
				$r1=$msg["publisher_search"]." ";
			break;		
			case 'titre_uniforme_see':
				$titre_uniforme_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select tu_name from publishers where tu_id=".$titre_uniforme_id;
				$r_tu=mysql_query($requete);
				if (@mysql_num_rows($r_tu)) {
					$valeur_champ=mysql_result($r_tu,0,0);
				}
				$r1=$msg["titre_uniforme_search"]." ";
			break;
			case 'coll_see':
				$coll_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select collection_name from collections where collection_id=".$coll_id;
				$r_coll=mysql_query($requete);
				if (@mysql_num_rows($r_coll)) {
					$valeur_champ=mysql_result($r_coll,0,0);
				}
				$r1=$msg["coll_search"]." ";
			break;
			case 'subcoll_see':
				$subcoll_id=$_SESSION["notice_view".$n]["search_id"];
				$requete="select sub_coll_name from sub_collections where sub_coll_id=".$subcoll_id;
				$r_subcoll=mysql_query($requete);
				if (@mysql_num_rows($r_subcoll)) {
					$valeur_champ=mysql_result($r_subcoll,0,0);
				}
				$r1=$msg["subcoll_search"]." ";
			break;
			case 'docnum':
				$r1=$msg["docnum"];
				break;
		}
		if ($_SESSION["typdoc".$n]) {
			$doctype = new marc_list('doctype');
			$r2 .= sprintf($msg["simple_search_history_doc_type"],$doctype->table[$_SESSION["typdoc".$n]]);
		} else $r2 .= $msg["simple_search_history_all_doc_types"];
		if ($opac_search_other_function) {
			$r3=search_other_function_human_query($n);
			if ($r3) $r2.=", ".$r3;
		}
		$r=sprintf($msg["simple_search_history"],(!$valeur_champ?stripslashes($_SESSION["user_query".$n]):$valeur_champ),$r1,$r2);
	} else {
		$r= get_human_query($n);
	}
	return $r;
}

function rec_last_history() {
	global $page;
	global $msg;
	global $opac_search_other_function;
	global $facette_test;
	if ($page=="") $page_=1; else $page_=$page;
	
	if ($facette_test) $search_type=$_SESSION["search_type".$_SESSION["last_query"]]; else $search_type=$_SESSION["search_type"];
	
	$_SESSION["lq_facette_test"]=($facette_test?2:0);
	
	switch ($search_type) {
		case "simple_search":
			global $user_query,$mode,$count,$typdoc,$clause,$clause_bull,$clause_bull_num_notice,$tri,$pert,$page,$l_typdoc, $join,$id_thes;
			global $affiliate_page, $catalog_page;
			if (!$facette_test) {
				$_SESSION["lq_user_query"]=$user_query;
				$_SESSION["lq_mode"]=$mode;
				$_SESSION["lq_count"]=$count;
				$_SESSION["lq_typdoc"]=$typdoc;
				$_SESSION["lq_clause"]=$clause;
				$_SESSION["lq_clause_bull"]=$clause_bull;
				$_SESSION["lq_clause_bull_num_notice"]=$clause_bull_num_notice;
				$_SESSION["lq_tri"]=$tri;
				$_SESSION["lq_pert"]=$pert;
				$_SESSION["lq_page"]=$page_;
				$_SESSION["lq_affiliate_page"]=$affiliate_page;
				$_SESSION["lq_catalog_page"]=$catalog_page;
				$_SESSION["lq_l_typdoc"]=$l_typdoc;
				$_SESSION["lq_join"]=$join;
				$_SESSION["lq_id_thes"]=$id_thes;
				$_SESSION["lq_level1"]=$_SESSION["level1"];
				unset($_SESSION["lq_facette"]);
				
				if ($opac_search_other_function) search_other_function_rec_history($_SESSION["last_query"]);
				switch ($mode) {
					case "tous" :
						$_SESSION["list_name"]=$msg["list_tous"];
						$_SESSION["list_name_msg"]="list_tous";
						break;
					case "auteur":
						$_SESSION["list_name"]=$msg["list_authors"];
						$_SESSION["list_name_msg"]="list_authors";
						break;
					case "titre":
						$_SESSION["list_name"]=$msg["list_titles"];
						$_SESSION["list_name_msg"]="list_titles";
						break;
					case "editeur":
						$_SESSION["list_name"]=$msg["list_publishers"];
						$_SESSION["list_name_msg"]="list_publishers";
						break;
					case "titre_uniforme":
						$_SESSION["list_name"]=$msg["list_titres_uniformes"];
						$_SESSION["list_name_msg"]="list_titres_uniformes";
						break;
					case "collection":
						$_SESSION["list_name"]=$msg["list_collections"];
						$_SESSION["list_name_msg"]="list_collections";
						break;
					case "souscollection":
						$_SESSION["list_name"]=$msg["list_subcollections"];
						$_SESSION["list_name_msg"]="list_subcollections";
						break;
					case "categorie":
						$_SESSION["list_name"]=$msg["list_categories"];
						$_SESSION["list_name_msg"]="list_categories";
						break;
					case "indexint":
						$_SESSION["list_name"]=$msg["list_indexint"];
						$_SESSION["list_name_msg"]="list_indexint";
						break;
					case "keyword":
						$_SESSION["list_name"]=$msg["list_keywords"];
						$_SESSION["list_name_msg"]="list_keywords";
						break;	
					case "docnum":
						$_SESSION["list_name"]=$msg["docnum_list"];
						$_SESSION["list_name_msg"]="docnum_list";
						break;		
				}
			}
			break;
		case "extended_search":
			if (!$facette_test) {
				$_SESSION["lq_page"]=$page_;
				$_SESSION["lq_affiliate_page"]=$affiliate_page;
				$_SESSION["lq_catalog_page"]=$catalog_page;
				$_SESSION["lq_mode"]="extended";
				$_SESSION["list_name"]=$msg["list_titles"];
				$_SESSION["list_name_msg"]="list_titles";
			}
			break;
	}
	//Si on est en navigation par facette
	if ($facette_test) {
		$_SESSION["lq_facette"]=$_SESSION["facette"];
		//La recherche �tendue pour les facettes
		$_SESSION["lq_facette_search"]["lq_page"]=$page_;
		$_SESSION["lq_facette_search"]["lq_affiliate_page"]=$affiliate_page;
		$_SESSION["lq_facette_search"]["lq_catalog_page"]=$catalog_page;
		$_SESSION["lq_facette_search"]["lq_mode"]="extended";
		$_SESSION["lq_facette_search"]["lq_search"]=search::serialize_search();
		$_SESSION["lq_facette_search"]["lq_notice_view"]=$_SESSION["notice_view".$_SESSION["last_query"]];
	}
}

function get_last_history() {
	global $search_type;
	global $opac_search_other_function;
	global $facette_test;
	global $reinit_facette;
	
	if ($reinit_facette==1) {
		unset($_SESSION["lq_facette"]);
		unset($_SESSION["lq_facette_search"]);
		unset($_SESSION["lq_facette_test"]);
	}
	
	$search_type=$_SESSION["search_type".$_SESSION["last_query"]];
	$facette_test=$_SESSION["lq_facette_test"];
	switch ($search_type) {
		case "simple_search":
			if (!$facette_test) {
				global $user_query,$mode,$count,$typdoc,$clause,$clause_bull,$clause_bull_num_notice,$tri,$pert,$page,$l_typdoc, $join, $id_thes;
				$user_query=$_SESSION["lq_user_query"];
				$mode=$_SESSION["lq_mode"];
				$count=$_SESSION["lq_count"];
				$typdoc=$_SESSION["lq_typdoc"];
				$clause=$_SESSION["lq_clause"];
				$clause_bull=$_SESSION["lq_clause_bull"];
				$clause_bull_num_notice=$_SESSION["lq_clause_bull_num_notice"];
				$tri=$_SESSION["lq_tri"];
				$pert=$_SESSION["lq_pert"];
				$page=$_SESSION["lq_page"];
				$affiliate_page=$_SESSION["lq_affiliate_page"];
				$catalog_page=$_SESSION["lq_catalog_page"];
				$l_typdoc=$_SESSION["lq_l_typdoc"];
				$join=$_SESSION["lq_join"];
				$id_thes=$_SESSION["lq_id_thes"];
				$_SESSION["facette"]=$_SESSION["lq_facette"];
				$_SESSION["level1"]=$_SESSION["lq_level1"];
				if ($opac_search_other_function) search_other_function_get_history($_SESSION["last_query"]);
			}
			break;
		case "extended_search":
			global $page,$mode,$catalog_page,$affiliate_page;
			get_history($_SESSION["last_query"]);
			$page=$_SESSION["lq_page"];
			$affiliate_page=$_SESSION["lq_affiliate_page"];
			$catalog_page=$_SESSION["lq_catalog_page"];
			$mode=$_SESSION["lq_mode"];
			break;
	}
	if ($facette_test) {
		global $page,$mode,$catalog_page,$affiliate_page;
		$_SESSION["facette"]=$_SESSION["lq_facette"];
		$page=$_SESSION["lq_facette_search"]["lq_page"];
		$affiliate_page=$_SESSION["lq_facette_search"]["lq_affiliate_page"];
		$catalog_page=$_SESSION["lq_facette_search"]["lq_catalog_page"];
		$mode=$_SESSION["lq_facette_search"]["lq_mode"];
		search::unserialize_search($_SESSION["lq_facette_search"]["lq_search"]);
		$_SESSION["notice_view".$_SESSION["last_query"]]=$_SESSION["lq_facette_search"]["lq_notice_view"];
	}
}
?>