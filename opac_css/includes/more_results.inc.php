<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: more_results.inc.php,v 1.58 2011-10-26 09:31:34 fgautier Exp 

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

	// récupération configuration
	require_once($base_path."/includes/opac_config.inc.php");

	// récupération paramètres MySQL et connection à la base
	require_once($base_path."/includes/opac_db_param.inc.php");
	require_once($base_path."/includes/opac_mysql_connect.inc.php");
	$dbh = connection_mysql();
	
	require_once($base_path."/includes/start.inc.php");

	// récupération localisation
	require_once($base_path."/includes/localisation.inc.php");
	// les mots vides sont importants pour la requête à appliquer
	require_once($base_path."/includes/marc_tables/$pmb_indexation_lang/empty_words");
	// version actuelle de l'opac
	require_once($base_path."/includes/opac_version.inc.php");

	// fonctions de formattage requêtes
	require_once($base_path."/includes/misc.inc.php");


	// fonctions de gestion de formulaire
	require_once($base_path."/includes/javascript/form.inc.php");
	require_once($base_path."/includes/templates/common.tpl.php");
	
	require_once($base_path."/includes/rec_history.inc.php");
	
	require_once($include_path.'/surlignage.inc.php');
	
	require_once($class_path."/searcher.class.php");
	
	//Affectation du numéro de page avant l'enregistrement en session, ca évite certains problèmes...
	if(!$page){
		$page=1;
		if($opac_allow_affiliate_search && ($mode != 'external' && $mode != 'docnum')) $affiliate_page = $catalog_page = 1;
	} else{
		if($opac_allow_affiliate_search && ($mode != 'external' && $mode != 'docnum')){
			if($tab == "affiliate"){
				$page = $affiliate_page;
			}else{
				$page = $catalog_page;
			}
		}
	}
	
	if ($opac_search_other_function) {
		require_once($include_path."/".$opac_search_other_function);
	}
	
	if ($get_last_query) {
		get_last_history();
	} else {
		if ($_SESSION["new_last_query"]) {
			$_SESSION["last_query"]=$_SESSION["new_last_query"];
			$_SESSION["new_last_query"]="";
			unset($_SESSION["facette"]);
			unset($_SESSION["lq_facette_search"]);
			unset($_SESSION["lq_facette_test"]);
		}
	 	rec_last_history();
	}
	
	//on s'assure d'avoir un onglet sélectionné...	
	if ($opac_allow_affiliate_search && ($mode != 'external' && $mode != 'docnum') && $tab == "") $tab = "catalog";
	
	//Surlignage
	require_once("$include_path/javascript/surligner.inc.php");
	
	//recherche affiliées
	require_once($class_path."/affiliate_search.class.php");
	require_once("$include_path/templates/more_results.tpl.php");
	print $inclure_recherche;
	// lien pour retour au sommaire
		
	//print "<a href=\"./index.php?lvl=index\">
	//		<img src=\"./images/home.gif\" border=\"0\" title=\"$msg[back_summmary]\" alt=\"$msg[back_summary]\">$msg[back_summary]</a>"; 

	// affichage recherche
	$clause = stripslashes($clause);
	$tri = stripslashes($tri);
	$pert=stripslashes($pert);
	$clause_bull = stripslashes($clause_bull);
	$clause_bull_num_notice = stripslashes($clause_bull_num_notice);
	$join = stripslashes($join);
/*	 les données disponibles dans ce script sont :
	$user_query : la requête utilisateur
	$mode : sur quoi porte la recherche
	$count : le nombre de résultats trouvés
	$clause : la chaine contenant la clause MySQL
	$tri : la chaine contenant la clause MySQL de tri
*/

	// nombre de références par pages (10 par défaut)
	if (!isset($opac_search_results_per_page)) $opac_search_results_per_page=10; 
	
	$debut =($page-1)*$opac_search_results_per_page;
	$limiter = "LIMIT $debut,$opac_search_results_per_page";
	
	if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) $add_cart_link="<a href='javascript:document.cart_values.submit()'>".$msg["cart_add_result_in"]."</a>";
	
	// constitution des liens
	$nbepages = ceil($count/$opac_search_results_per_page);
	$catal_navbar= "<div class='row'>&nbsp;</div>";
	if(!$opac_allow_affiliate_search || $mode == 'external' || $mode == 'docnum'){
		$url_page = "javascript:document.form_values.page.value=!!page!!; document.form_values.submit()";
		$action = "javascript:document.form_values.page.value=document.form.page.value; document.form_values.submit()";
	}else{
		$url_page = "javascript:document.form_values.page.value=!!page!!; document.form_values.catalog_page.value=document.form_values.page.value; document.form_values.action = \"./index.php?lvl=more_results&tab=catalog\"; document.form_values.submit()";
		$action = "javascript:document.form_values.page.value=document.form.page.value; document.form_values.catalog_page.value=document.form_values.page.value; document.form_values.action = \"./index.php?lvl=more_results&tab=catalog\"; document.form_values.submit()";
	}
	$catal_navbar .= "<div id='navbar'><hr />\n<center>".printnavbar($page, $nbepages, $url_page,$action)."</center></div>";

	$active_facette = 0;
	
	switch($mode) {
		case 'tous':
			$active_facette = 1;
			require_once($base_path.'/search/level2/tous.inc.php');
			break;
		case 'titre':
		case 'title':
			$active_facette = 1;
			require_once($base_path.'/search/level2/title.inc.php');
			break;
		case 'auteur':
			require_once($base_path.'/search/level2/author.inc.php');
			break;
		case 'editeur':
			require_once($base_path.'/search/level2/publisher.inc.php');
			break;
		case 'titre_uniforme':
			require_once($base_path.'/search/level2/titre_uniforme.inc.php');
			break;			
		case 'collection':
			require_once($base_path.'/search/level2/collection.inc.php');
			break;
		case 'souscollection':
			require_once($base_path.'/search/level2/subcollection.inc.php');
			break;
		case 'categorie':
			require_once($base_path.'/search/level2/category.inc.php');
			break;
		case 'indexint':
			require_once($base_path.'/search/level2/indexint.inc.php');
			break;
		case 'abstract':
			$active_facette = 1;
			require_once($base_path.'/search/level2/abstract.inc.php');
			break;
		case 'keyword':
			$active_facette = 1;
			if ($search_type=="extended_search") $search_type="";
			require_once($base_path.'/search/level2/keyword.inc.php');
			break;
		case 'extended':
			//On annule la navigation par critères simples
			$_SESSION["level1"]=array();
			$active_facette = 1;
			require_once($base_path.'/search/level2/extended.inc.php');
			break;
		case 'external':
			//On annule la navigation par critères simples
			$_SESSION["level1"]=array();
			require_once($base_path.'/search/level2/external.inc.php');
			break;
		case 'docnum':
			require_once($base_path.'/search/level2/docnum.inc.php');
			break;
		default:
			print $msg[no_document_found];
			break;
	}
	//gestion des facette si active
	if(($active_facette)&& ($tab!="affiliate")){
		require_once($base_path.'/classes/facette_search.class.php');
		$tab_result = $searcher->get_result();
		if($reinit_facette) unset($_SESSION['facette']); 
		if(count($_SESSION['facette'])>0){
			$search_type = "extended_search";
			if(!is_object($es)) $es=new search();
		}
		if(!$opac_facettes_ajax){
			$str .= facettes::make_facette($tab_result);
		}else{
			$_SESSION['tab_result']=$tab_result;
			$str .=facettes::get_facette_wrapper();
			$str .="<div id='facette_wrapper'><img src='./images/patience.gif'/></div>";
			
			$str .="
			<script type='text/javascript'>
				var req = new http_request();
				req.request(\"./ajax.php?module=ajax&categ=facette&sub=call_facettes\",false,null,true,function(data){
					document.getElementById('facette_wrapper').innerHTML=data;
				});
			</script>";
		}
	}

	$str_lvl1=facettes::do_level1();
	//suggestions : on affiche le bloc si une recherche a été tapée, différente de juste '*' et si le paramètre est bien activé
	if(trim(str_replace('*','',$user_query)) && $opac_simple_search_suggestions){
		$str .= facettes::make_facette_suggest($user_query);
	}
	
	
switch ($search_type) {
	case 'simple_search':
	case 'tags_search':
		// constitution du form pour la suite
		$form .= "<input type=\"hidden\" name=\"user_query\" value=\"".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\">\n";
		$form .= "<input type=\"hidden\" name=\"count\" value=\"$count\">\n";
		$form .= "<input type=\"hidden\" name=\"typdoc\" value=\"".$typdoc."\">";
	 	if (function_exists("search_other_function_post_values")){
			$form .=search_other_function_post_values(); 
		}
		$form .= "<input type=\"hidden\" name=\"clause\" value=\"".htmlentities($clause,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"clause_bull\" value=\"".htmlentities($clause_bull,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"clause_bull_num_notice\" value=\"".htmlentities($clause_bull_num_notice,ENT_QUOTES,$charset)."\">\n";
		if($opac_indexation_docnum_allfields) 
			$form .= "<input type=\"hidden\" name=\"join\" value=\"".htmlentities($join,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"tri\" value=\"".htmlentities($tri,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"pert\" value=\"".htmlentities($pert,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" name=\"l_typdoc\" value=\"".htmlentities($l_typdoc,ENT_QUOTES,$charset)."\">\n";
		$form .= "<input type=\"hidden\" id=author_type name=\"author_type\" value=\"$author_type\">\n";		
		$form .= "<input type=\"hidden\" id=\"id_thes\" name=\"id_thes\" value=\"".$id_thes."\">\n";
		$form .= "<input type=\"hidden\" name=\"surligne\" value=\"".$surligne."\">\n";
		$form .= "<input type=\"hidden\" name=\"tags\" value=\"".$tags."\">\n";
		$f_values=$form;
		$form = "<form name=\"form_values\" action=\"./index.php?lvl=more_results\" method=\"post\">\n";
		$form .= $f_values;
		$form .= "<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		if($opac_allow_affiliate_search){
			$form .= "<input type=\"hidden\" name=\"catalog_page\" value=\"$catalog_page\">\n";
			$form .= "<input type=\"hidden\" name=\"affiliate_page\" value=\"$affiliate_page\">\n";
		}
		$form .= "<input type=\"hidden\" name=\"nbexplnum_to_photo\" value=\"".$nbexplnum_to_photo."\">\n";
		$form .= "</form>";
		if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) {
			$form .= "<form name='cart_values' action='./cart_info.php?lvl=more_results' method='post' target='cart_info'>\n";
			$form .= $f_values;
			$form .= "</form>";
		}
		break;
	case 'extended_search':
		$form=$es->make_hidden_search_form("./index.php?lvl=more_results&mode=extended","form_values","",false);
		if($opac_allow_affiliate_search){
			$form .= "<input type=\"hidden\" name=\"catalog_page\" value=\"$catalog_page\">\n";
			$form .= "<input type=\"hidden\" name=\"affiliate_page\" value=\"$affiliate_page\">\n";
		}
		if($facette_test) $form .= "<input type=\"hidden\" name=\"facette_test\" value=\"2\">\n";
		$form.="</form>";
		if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) {
			$form.=$es->make_hidden_search_form("./cart_info.php?lvl=more_results&mode=extended","cart_values","cart_info","",false);
			if($opac_allow_affiliate_search){
				$form .= "<input type=\"hidden\" name=\"catalog_page\" value=\"$catalog_page\">\n";
				$form .= "<input type=\"hidden\" name=\"affiliate_page\" value=\"$affiliate_page\">\n";
			}
		$form.="</form>";
		}
		break;
	case 'external_search':
		$form=$es->make_hidden_search_form("./index.php?lvl=more_results&mode=external","form_values","",false);
		$form .= "<input type=\"hidden\" name=\"count\" value=\"$count\">\n";
		if ($_SESSION["ext_type"]!="multi") {
			$form.="<input type='hidden' name='external_env' value='".htmlentities(stripslashes($external_env),ENT_QUOTES,$charset)."'/>";
			$form.="</form>";
		} else $form.="</form>";
		if ((($opac_cart_allow)&&(!$opac_cart_only_for_subscriber))||(($opac_cart_allow)&&($_SESSION["user_code"]))) 
			$form.=$es->make_hidden_search_form("./cart_info.php?lvl=more_results&mode=external","cart_values","cart_info");
		break;
}
print pmb_bidi($form);

// affichage du navigateur si besoin (recherche affiliées off ou multi-critère (pagin géré dans le lvl2)
if( $mode != 'extended' && (!$opac_allow_affiliate_search || $mode == 'external' || $mode == 'docnum')) print $catal_navbar; 