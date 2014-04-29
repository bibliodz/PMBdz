<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_selector.php,v 1.48 2013-09-30 07:59:07 mbertin Exp $

$base_path=".";
$base_noheader=1;
$base_nobody=1;
//$base_nocheck=1;


require_once("includes/init.inc.php");
require_once("$class_path/marc_table.class.php");
require_once("$class_path/analyse_query.class.php");

header("Content-Type: text/html; charset=$charset");
$start=stripslashes($datas);
$start = str_replace("*","%",$start);

switch($completion):
	case 'categories':
		/* Pas utilis� en gestion Matthieu 02/08/2012 */
		$array_selector=array();
		require_once("$class_path/thesaurus.class.php");
		require_once("$class_path/categories.class.php");
		if ($thesaurus_mode_pmb==1) $id_thes=-1;
			else $id_thes=$thesaurus_defaut;

		$aq=new analyse_query($start);

		$members_catdef = $aq->get_query_members("catdef", "catdef.libelle_categorie", "catdef.index_categorie", "catdef.num_noeud");
		$members_catlg = $aq->get_query_members("catlg", "catlg.libelle_categorie", "catlg.index_categorie", "catlg.num_noeud");
		
		$requete_langue="select catlg.num_noeud as categ_id, noeuds.num_parent as categ_parent, noeuds.num_renvoi_voir as categ_see, noeuds.num_thesaurus, catlg.langue as langue, 
		catlg.libelle_categorie as categ_libelle,catlg.index_categorie as index_categorie, catlg.note_application as categ_comment, 
		(".$members_catlg["select"].") as pert from thesaurus left join noeuds on  thesaurus.id_thesaurus = noeuds.num_thesaurus left join categories as catlg on noeuds.id_noeud = catlg.num_noeud 
		and catlg.langue = '".$lang."' where catlg.libelle_categorie like '".addslashes($start)."%' and catlg.libelle_categorie not like '~%'";
		
		$requete_defaut="select catdef.num_noeud as categ_id, noeuds.num_parent as categ_parent, noeuds.num_renvoi_voir as categ_see, noeuds.num_thesaurus, catdef.langue as langue, 
		catdef.libelle_categorie as categ_libelle,catdef.index_categorie as index_categorie, catdef.note_application as categ_comment, 
		(".$members_catdef["select"].") as pert from thesaurus left join noeuds on  thesaurus.id_thesaurus = noeuds.num_thesaurus left join categories as catdef on noeuds.id_noeud = catdef.num_noeud 
		and catdef.langue = thesaurus.langue_defaut where catdef.libelle_categorie like '".addslashes($start)."%' and catdef.libelle_categorie not like '~%'";
		
		$requete="select * from (".$requete_langue." union ".$requete_defaut.") as sub1 group by categ_id order by pert desc,num_thesaurus, categ_libelle limit 20";
		
		$res = @mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
		while(($categ=mysql_fetch_object($res))) {
			$display_temp = "" ;
			$lib_simple="";
			$tab_lib_categ="";
			$temp = new categories($categ->categ_id, $categ->langue);
			if ($id_thes == -1) {
				$thes = new thesaurus($categ->num_thesaurus);
				$display_temp = htmlentities('['.$thes->libelle_thesaurus.'] ',ENT_QUOTES, $charset);
			}
			$id_categ_retenue = $categ->categ_id ;	
			if($categ->categ_see) {
				$id_categ_retenue = $categ->categ_see ;
				$temp = new categories($categ->categ_see, $categ->langue);
				$display_temp.= $categ->categ_libelle." -> ";
				$lib_simple = $temp->libelle_categorie;
				if ($thesaurus_categories_show_only_last) $display_temp.= $temp->libelle_categorie;
					else $display_temp.= categories::listAncestorNames($categ->categ_see, $categ->langue);				
				$display_temp.= "@";
			} else {
				$lib_simple = $categ->categ_libelle;
				if ($thesaurus_categories_show_only_last) $display_temp.= $categ->categ_libelle;
				else $display_temp.= categories::listAncestorNames($categ->categ_id, $categ->langue); 			
			}		
			
			$tab_lib_categ[$display_temp] = $lib_simple; 
			$array_selector[$id_categ_retenue] = $display_temp;
		} // fin while		
		$origine = "ARRAY" ;
		break;
	case 'categories_mul':
		$array_selector=array();
		require_once("$class_path/thesaurus.class.php");
		require_once("$class_path/categories.class.php");
		if ($thesaurus_mode_pmb==1){
			$id_thes=-1;	
		}else{
			$id_thes=$thesaurus_defaut;
		}
		if($att_id_filter!=0){ //forcage sur un th�saurus en particuli�
			$id_thes=$att_id_filter;
			$linkfield=$att_id_filter; 
		}
		
		if(preg_match("#^f_categ_id#",$autfield)){//Permet de savoir si l'on vient du formulaire de notice ou de recherche
			$from="notice";//Affichage complet du chemin de la cat�gorie
		}else{
			$from="search";//Affichage que de la cat�gorie
		}
		$aq=new analyse_query($start);
		$members_catdef = $aq->get_query_members("catdef", "catdef.libelle_categorie", "catdef.index_categorie", "catdef.num_noeud");
		$members_catlg = $aq->get_query_members("catlg", "catlg.libelle_categorie", "catlg.index_categorie", "catlg.num_noeud");

		$thesaurus_requette='';
		$thes_unique=0;
		if($thesaurus_mode_pmb==0){
			$thesaurus_requette= " id_thesaurus='$thesaurus_defaut' and ";
			$thes_unique=$thesaurus_defaut;
		}elseif($linkfield){
			if(!preg_match("#,#i",$linkfield)){
				$thesaurus_requette= " id_thesaurus='$linkfield' and ";
				$thes_unique=$linkfield;
			}else{
				$thesaurus_requette= " id_thesaurus in ($linkfield) and ";
			}
		}		
		 
		$requete_langue="select catlg.num_noeud as categ_id, noeuds.num_parent as categ_parent, noeuds.num_renvoi_voir as categ_see, noeuds.num_thesaurus, catlg.langue as langue, 
		catlg.libelle_categorie as categ_libelle,catlg.index_categorie as index_categorie, catlg.note_application as categ_comment, noeuds.not_use_in_indexation as not_use_in_indexation, 
		(".$members_catlg["select"].") as pert from thesaurus left join noeuds on  thesaurus.id_thesaurus = noeuds.num_thesaurus left join categories as catlg on noeuds.id_noeud = catlg.num_noeud 
		and catlg.langue = '".$lang."' where $thesaurus_requette catlg.libelle_categorie like '".addslashes($start)."%' and catlg.libelle_categorie not like '~%'";
		
		$requete_defaut="select catdef.num_noeud as categ_id, noeuds.num_parent as categ_parent, noeuds.num_renvoi_voir as categ_see, noeuds.num_thesaurus, catdef.langue as langue, 
		catdef.libelle_categorie as categ_libelle,catdef.index_categorie as index_categorie, catdef.note_application as categ_comment, noeuds.not_use_in_indexation as not_use_in_indexation, 
		(".$members_catdef["select"].") as pert from thesaurus left join noeuds on  thesaurus.id_thesaurus = noeuds.num_thesaurus left join categories as catdef on noeuds.id_noeud = catdef.num_noeud 
		and catdef.langue = thesaurus.langue_defaut where $thesaurus_requette catdef.libelle_categorie like '".addslashes($start)."%' and catdef.libelle_categorie not like '~%'";
		
		$requete="select * from (".$requete_langue." union ".$requete_defaut.") as sub1 group by categ_id order by pert desc,num_thesaurus, index_categorie limit 20";
		
		$res = @mysql_query($requete, $dbh) or die(mysql_error()."<br />$requete");
		while(($categ=mysql_fetch_object($res))) {
			$display_temp = "" ;
			$lib_simple="";
			$tab_lib_categ="";
			$temp = new categories($categ->categ_id, $categ->langue);
			if ($id_thes == -1) {//Si mode multi-th�saurus
				$thes = new thesaurus($categ->num_thesaurus);
				if($from == "notice"){//Si saisi de notice
					$lib_simple = htmlentities('['.$thes->libelle_thesaurus.'] ',ENT_QUOTES, $charset);
				}
			}
			
			$id_categ_retenue = $categ->categ_id ;
			//Cat�gorie � ne pas utiliser en indexation
			$not_use_in_indexation=$categ->not_use_in_indexation;
			if($categ->categ_see) {
				$id_categ_retenue = $categ->categ_see ;
				//Cat�gorie � ne pas utiliser en indexation
				$category=new category($id_categ_retenue);
				$not_use_in_indexation=$category->not_use_in_indexation;
				
				$temp = new categories($categ->categ_see, $categ->langue);
				$display_temp= $categ->categ_libelle." -> ";
				$chemin=categories::listAncestorNames($categ->categ_see, $categ->langue);
				if ($thesaurus_categories_show_only_last){
					$display_temp.= $temp->libelle_categorie;
					$lib_simple.= $temp->libelle_categorie;
				}else{
					$display_temp.=$chemin;
					if($from == "notice"){
						$lib_simple.= $chemin;
					}else{
						$lib_simple.= $temp->libelle_categorie;
					}
				}
				$display_temp.= "@";
			} else {
				$chemin=categories::listAncestorNames($categ->categ_id, $categ->langue);
				if ($thesaurus_categories_show_only_last){
					$display_temp.= $categ->categ_libelle;
					$lib_simple.= $categ->categ_libelle;
				}else{
					$display_temp.= $chemin;
					if($from == "notice"){
						$lib_simple.= $chemin;
					}else{
						$lib_simple.= $categ->categ_libelle;
					}
				}			
			}
			
			if(!$not_use_in_indexation && !preg_match("#:~|^~#i",$chemin)){
				$tab_lib_categ[$display_temp] = $lib_simple; 
				$array_selector[$id_categ_retenue] = $tab_lib_categ;
				if(!$thes_unique){
					$array_prefix[$id_categ_retenue] = array(
						'id' => $categ->num_thesaurus,
						'libelle' => htmlentities('['.$thes->libelle_thesaurus.'] ',ENT_QUOTES, $charset)
					);
				}else{
					$array_prefix[$id_categ_retenue] = array(
						'id' => $categ->num_thesaurus,
						'libelle' => ""
					);
				}
				
			}
		} // fin while		
		$origine = "ARRAY" ;
		break;
	case 'authors':
		if ($autexclude) $restrict = " AND author_id not in ($autexclude) ";
		$requete="select if(author_date!='',concat(if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name),' (',author_date,')'),if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name)) as author,author_id from authors where if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name) like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'authors_person':
		if ($autexclude) $restrict = " AND author_id not in ($autexclude) ";
		$requete="select if(author_date!='',concat(if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name),' (',author_date,')'),if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name)) as author,author_id from authors where author_type='70' and if(author_rejete is not null and author_rejete!='',concat(author_name,', ',author_rejete),author_name) like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'congres_name':
		if ($autexclude) $restrict = " AND author_id not in ($autexclude) ";
		$requete="select distinct author_name from authors where  author_type='72' and author_name like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;	
	case 'collectivite_name':
		if ($autexclude) $restrict = " AND author_id not in ($autexclude) ";
		$requete="select distinct author_name from authors where  author_type='71' and author_name like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;	
	case 'publishers':
		if ($autexclude) $restrict = " AND ed_id not in ($autexclude) ";
		$requete="select concat(
					ed_name,
					if((ed_ville is not null and ed_ville!='') or (ed_pays is not null and ed_pays!=''),' (',''), 
					if(ed_ville is not null and ed_ville!='',ed_ville,''),
					if(ed_ville is not null and ed_ville!='' and ed_pays is not null and ed_pays!='',' - ',''), 
					if(ed_pays is not null and ed_pays!='',ed_pays,''), 
					if((ed_ville is not null and ed_ville!='') or (ed_pays is not null and ed_pays!=''),')','')
					) as ed,ed_id from publishers where concat(
					ed_name,
					if((ed_ville is not null and ed_ville!='') or (ed_pays is not null and ed_pays!=''),' (',''), 
					if(ed_ville is not null and ed_ville!='',ed_ville,''),
					if(ed_ville is not null and ed_ville!='' and ed_pays is not null and ed_pays!='',' - ',''), 
					if(ed_pays is not null and ed_pays!='',ed_pays,''), 
					if((ed_ville is not null and ed_ville!='') or (ed_pays is not null and ed_pays!=''),')','')
					) like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'titre_uniforme':
		if ($autexclude) $restrict = " AND tu_id not in ($autexclude) ";
		$requete="select tu_name as titre_uniforme,tu_id from titres_uniformes where tu_name like '".addslashes($start)."%' $restrict order by 1 limit 20";		
		$origine = "SQL" ;
		break;		
	case 'collections':
		if ($autexclude) $restrict = " AND collection_id not in ($autexclude) ";
		if ($linkfield) $restrict .= " AND collection_parent ='$linkfield' ";
		$requete="select if(collection_issn is not null and collection_issn!='',concat(collection_name,', ',collection_issn),collection_name) as coll,collection_id from collections where if(collection_issn is not null and collection_issn!='',concat(collection_name,', ',collection_issn),collection_name) like '".addslashes($start)."%' $restrict order by index_coll limit 20";
		$origine = "SQL" ;
		break;
	case 'subcollections':
		if ($autexclude) $restrict = " AND sub_coll_id not in ($autexclude) ";
		if ($linkfield) $restrict .= " AND sub_coll_parent ='$linkfield' ";
		$requete="select if(sub_coll_issn is not null and sub_coll_issn!='',concat(sub_coll_name,', ',sub_coll_issn),sub_coll_name) as subcoll,sub_coll_id from sub_collections where if(sub_coll_issn is not null and sub_coll_issn!='',concat(sub_coll_name,', ',sub_coll_issn),sub_coll_name) like '".addslashes($start)."%' $restrict order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'indexint':
		if ($autexclude) $restrict = " AND indexint_id not in ($autexclude) ";
		if ($thesaurus_classement_mode_pmb != 0) { //classement indexation d�cimale autoris� en parametrage
			$requete="select if(indexint_comment is not null and indexint_comment!='',concat('[',name_pclass,'] ',indexint_name,' - ',indexint_comment),
			concat('[',name_pclass,'] ',indexint_name)) as indexint,indexint_id
			from indexint,pclassement
			where if(name_pclass is not null and indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) like '".addslashes($start)."%' $restrict
			and id_pclass = num_pclass
			and typedoc like '%$typdoc%'
			order by indexint_name, name_pclass limit 20";	
		}
		else
			$requete="select if(indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) as indexint,indexint_id from indexint 
			where if(indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) like '".addslashes($start)."%' $restrict and num_pclass = '$thesaurus_classement_defaut' order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'indexint_mul':
		if ($autexclude) $restrict = " AND indexint_id not in ($autexclude) ";
		if ($thesaurus_classement_mode_pmb != 0) { //classement indexation d�cimale autoris� en parametrage
			$requete="select if(indexint_comment is not null and indexint_comment!='',concat('[',name_pclass,'] ',indexint_name,' - ',indexint_comment),
			concat('[',name_pclass,'] ',indexint_name)) as indexint,indexint_id, concat( indexint_name,' ',indexint_comment) as indexsimple
			from indexint,pclassement
			where if(name_pclass is not null and indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) like '".addslashes($start)."%' $restrict
			and id_pclass = num_pclass
			and typedoc like '%$typdoc%'
			order by indexint_name, name_pclass limit 20";	
		}
		else
			$requete="select if(indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) as indexint,indexint_id, concat( indexint_name,' ',indexint_comment) as indexsimple from indexint 
			where if(indexint_comment is not null and indexint_comment!='',concat(indexint_name,' - ',indexint_comment),indexint_name) like '".addslashes($start)."%' $restrict and num_pclass = '$thesaurus_classement_defaut' order by 1 limit 20";	
		$origine = "SQL" ;
		break;
	case 'notice':
		require_once('./includes/isbn.inc.php');
		if ($autexclude) $restrict = " AND notice_id not in ($autexclude) ";
		$requete = "select if(serie_name is not null,if(tnvol is not null,concat(serie_name,', ',tnvol,'. ',tit1),concat(serie_name,'. ',tit1)),tit1), notice_id from notices left join series on serie_id=tparent_id where (index_sew like ' ".addslashes(strip_empty_words($start))."%' or TRIM(index_wew) like '".addslashes($start)."%' or tit1 like '".addslashes($start)."%' or (code like '".traite_code_isbn(addslashes($start))."'";
		if (isISBN(traite_code_isbn($start))) {
			if (strlen(traite_code_isbn($start))==13)
				$requete.=" or code like '".formatISBN(traite_code_isbn($start),13)."'";
			else $requete.=" or code like '".formatISBN(traite_code_isbn($start),10)."'";
		}
		$requete.=")) $restrict order by index_serie, tnvol, index_sew , code limit 20 ";
		$origine = "SQL" ;
		break;
	case 'serie':
		if ($autexclude) $restrict = " AND serie_id not in ($autexclude) ";
		$requete="select serie_name,serie_id from series where serie_name like '".addslashes($start)."%' $restrict order by 1 limit 20";
		$origine = "SQL" ;
		break;
	case 'fonction':
		// r�cup�ration des codes de fonction
		if (!count($s_func )) {
			$s_func = new marc_list('function');
		}
		$origine = "TABLEAU" ;
		break;
	case 'langue':
		// r�cup�ration des codes de langue
		if (!count($s_func )) {
			$s_func = new marc_list('lang');
		}
		$origine = "TABLEAU" ;
		break;
	case 'synonyms':
		$array_selector=array();
		//recherche des mots
		$rqt="select id_mot, mot from mots left join linked_mots on (num_mot=id_mot) where mot like '".addslashes($start)."%' and id_mot not in (select num_mot from linked_mots where linked_mots.num_linked_mot=0) group by id_mot";
		$execute_query=mysql_query($rqt);
		while ($r=mysql_fetch_object($execute_query)) {
			$array_selector[$r->id_mot]=$r->mot;
		}
		mysql_free_result($execute_query);
		if (count($array_selector)) {
			//d�doublonnage du tableau final
			$array_selector=array_unique($array_selector);
			//tri alphanum�rique du tableau
			asort($array_selector);
		}
		$origine = "ARRAY" ;
		break;
	case 'tags':
		require_once("$class_path/tags.class.php");
		$tags = new tags();
		$array_selector = $tags->get_array($start,$pos_cursor);
		$taille_search = $tags->get_taille_search();
		$origine = "ARRAY";
		break;
	case 'perio':
		$requete = "select tit1, notice_id from notices where niveau_biblio='s' and niveau_hierar='1' and tit1 like '".addslashes($start)."%' order by 1 limit 20";
		$origine = "SQL";
		break;
	case 'bull':
		$link_bull = " and bulletin_notice ='".$linkfield."'";
		$requete = "select bulletin_numero, bulletin_id from bulletins where (bulletin_numero like '".addslashes($start)."%' or bulletin_titre like '".addslashes($start)."%')  $link_bull order by 1 limit 20";
		$origine = "SQL";
		break;		
	case 'expl_cote':	
		if($pmb_prefill_cote_ajax){
			include("./catalog/expl/ajax/$pmb_prefill_cote_ajax");
			$array_selector = calculer_cote($start);	
			$origine = "ARRAY";		
		}
		break;	
	case 'fournisseur':
		$requete = "select raison_sociale as lib,id_entite as id from entites where type_entite='0' ";
		if ($linkfield) $requete.= "and num_bibli='".$linkfield."' ";	
		$requete.= "and raison_sociale like '".addslashes($start)."%' order by 1 limit 20";
		$origine = "SQL";
		break;
	case 'origine':
		$requete = "select concat(nom,' ',prenom) as lib, concat(userid,',0') as id from users where nom like '".addslashes($start)."%' ";
		$requete.= "union select concat(empr_nom,' ',empr_prenom), concat(id_empr,',1') from empr where empr_nom like '".addslashes($start)."%' ";
		$requete.= "order by 1 limit 20";
		$origine = "SQL";
		break;
	case 'rubrique':
		$requete = "select concat('[',budgets.libelle,']',rubriques.libelle) as lib, id_rubrique as id, rubriques.libelle as lib2 from rubriques join budgets on num_budget=id_budget where autorisations like ' %".SESSuserid."% ' ";
		if ($linkfield) $requete.= "and num_exercice='".$linkfield."' ";
		$requete.= "and rubriques.libelle like '".addslashes($start)."%' ";
		$requete.= "order by lib limit 20";
		$origine="SQL";
		break;
	case 'perso_notices':
		require_once($class_path.'/parametres_perso.class.php');
		$p_perso = new parametres_perso('notices');
		$array_selector=$p_perso->get_ajax_list($persofield,$start);
		$origine='ARRAY';
		break;
	case 'perso_expl':
		require_once($class_path.'/parametres_perso.class.php');
		$p_perso = new parametres_perso('notices');
		$array_selector=$p_perso->get_ajax_list($persofield,$start);
		$origine='ARRAY';
		break;
	case 'perso_empr':
		require_once($class_path.'/parametres_perso.class.php');
		$p_perso = new parametres_perso('notices');
		$array_selector=$p_perso->get_ajax_list($persofield,$start);
		$origine='ARRAY';
		break;
	case 'perso_gestfic0':
		require_once($class_path.'/parametres_perso.class.php');
		$p_perso = new parametres_perso('notices');
		$array_selector=$p_perso->get_ajax_list($persofield,$start);
		$origine='ARRAY';
		break;
	case 'perso_collstate':
		require_once($class_path.'/parametres_perso.class.php');
		$p_perso = new parametres_perso('notices');
		$array_selector=$p_perso->get_ajax_list($persofield,$start);
		$origine='ARRAY';
		break;
	default: 
		break;
endswitch;


switch ($origine):
	case 'SQL':
		$resultat=mysql_query($requete) or die(mysql_error()."<br />$requete") ;
		$i=1;
		while($r=@mysql_fetch_array($resultat)) {
			if($r[2])
				echo "<div id='"."c".$id."_".$i."' style='display:none'>$r[2]</div>";
			echo "<div id='l".$id."_".$i."'";
			if ($autfield) echo " autid='".$r[1]."'";
			echo " style='cursor:default;font-family:arial,helvetica;font-size:10px;width:100%' onClick='if(document.getElementById(\"c".$id."_".$i."\")) ajax_set_datas(\"c".$id."_".$i."\",\"$id\"); else ajax_set_datas(\"l".$id."_".$i."\",\"$id\");'>".$r[0]."</div>";
			$i++;
		}
		break;
	case 'TABLEAU':
		$i=1;
		while(list($index, $value) = each($s_func->table)) {
			if (strtolower(substr($value,0,strlen($start)))==strtolower($start)) {
				echo "<div id='l".$id."_".$i."'";
				if ($autfield) echo " autid='".$index."'";
				echo " style='cursor:default;font-family:arial,helvetica;font-size:10px;width:100%' onClick='ajax_set_datas(\"l".$id."_".$i."\",\"$id\")'>".$value."</div>";
				$i++;
			}
		}
		break;
	case 'ARRAY':
		if (is_array($array_selector) && count($array_selector)) {
			$i=1;
			while(list($index, $value) = each($array_selector)) {			
				$lib_liste="";
				$thesaurus_lib = $array_prefix[$index]['libelle'];
				$thesaurus_id = $array_prefix[$index]['id'];
				if(is_array($value)){
					foreach($value as $k=>$v){
						$lib_liste = $k;
						echo "<div id='"."c".$id."_".$i."' style='display:none' nbcar='$taille_search'>".$v."</div>";
					}
				} else $lib_liste=$value;
				echo "<div id='l".$id."_".$i."'";
				if ($autfield) echo " autid='".$index."'";
				if ($thesaurus_id) echo " thesid='".$thesaurus_id."'";
				echo " style='cursor:default;font-family:arial,helvetica;font-size:10px;width:100%' onClick='if(document.getElementById(\"c".$id."_".$i."\")) ajax_set_datas(\"c".$id."_".$i."\",\"$id\"); else ajax_set_datas(\"l".$id."_".$i."\",\"$id\");'>".$thesaurus_lib." ".$lib_liste."</div>";
				$i++;	
			}
		}
		break;
	default: 
		break;
endswitch;


