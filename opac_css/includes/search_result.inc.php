<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_result.inc.php,v 1.55 2013-11-28 13:54:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// r�sultats d'une recherche sur mots utilisateur OPAC

if ($opac_search_other_function) require_once($include_path."/".$opac_search_other_function);

//Enregistrement de la recherche
require_once($include_path."/rec_history.inc.php");
if ($get_query) {
	get_history($get_query);
	$_SESSION["new_last_query"]=$get_query;
}

//Surlignage
require_once("$include_path/javascript/surligner.inc.php");
print $inclure_recherche;

// affichage recherche
require_once($base_path.'/includes/simple_search.inc.php');

$simple_search_content=simple_search_content(stripslashes($user_query),$css);

// template pour un encadr� du r�sultat
require_once($base_path.'/includes/templates/search_result.tpl.php');

if ((!$get_query)&&(!(($search_type=="extended_search")&&($launch_search!=1))) && (!$mode)) {//On ne met pas dans l'historique les r�sultats obtenus en cliquant sur le mot-cl� d'une notice
	rec_history();
	$_SESSION["new_last_query"]=$_SESSION["nb_queries"];
}

//Activation surlignage
if ($opac_show_results_first_page) {
	require_once($include_path."/surlignage.inc.php");
	$activation_surlignage=activation_surlignage();
	$simple_search_content=str_replace("!!surligne!!",$surligne,$simple_search_content);
} else {
	$simple_search_content=str_replace("!!surligne!!","",$simple_search_content);
}

print pmb_bidi(str_replace('!!user_query!!', '',$simple_search_content));
print pmb_bidi($search_result_header);

print "<div id=\"search_result\" ".((!$mode)&&($search_type=="simple_search")&&($opac_autolevel2)?"style='display:none'":"").">\n";

// lien pour retour au sommaire
unset($_SESSION['facette']);
if (!$mode) {
	switch ($search_type) {
		case "simple_search":
			print pmb_bidi("<h3><span>$msg[search_result_for]<b>".htmlspecialchars(stripslashes($user_query),ENT_QUOTES,$charset)."</b></span>$activation_surlignage</h3>");
			
			if ($user_query=="") {
				if ($opac_search_other_function) {
					if (search_other_function_has_values()) $user_query="*";
				}
			}
			
			if ($user_query!="") {
				$_SESSION["level1"]=array();
				
				$aq=new analyse_query(stripslashes($user_query),0,0,1,1,$opac_stemming_active);
				if ($aq->error) {
					print pmb_bidi(sprintf($msg["searcher_syntax_error_desc"],$aq->current_car,$aq->input_html,$aq->error_message)."<br /><br />");
					break;
				}
	  			
				if ($opac_modules_search_title && $look_TITLE) {
					require_once($base_path.'/search/level1/title.inc.php');	
					$total_results += $nb_result_titres;
				}
		
				if ($opac_modules_search_author && $look_AUTHOR) {
					require_once($base_path.'/search/level1/author.inc.php');
					$total_results += $nb_result_auteurs;
				}
		
				if ($opac_modules_search_publisher && $look_PUBLISHER) {
					require_once($base_path.'/search/level1/publisher.inc.php');
					$total_results += $nb_result_editeurs;
				}
				if ($opac_modules_search_titre_uniforme && $look_TITRE_UNIFORME) {
					require_once($base_path.'/search/level1/titre_uniforme.inc.php');
					$total_results += $nb_result_titres_uniformes;
				}
				if ($opac_modules_search_collection && $look_COLLECTION) {
					require_once($base_path.'/search/level1/collection.inc.php');
					$total_results += $nb_result_collections;
				}
		
				if ($opac_modules_search_subcollection && $look_SUBCOLLECTION) {
					require_once($base_path.'/search/level1/subcollection.inc.php');
					$total_results += $nb_result_subcollections;
				}
		
				if ($opac_modules_search_category && $look_CATEGORY) {
					require_once($base_path.'/search/level1/category.inc.php');	
					$total_results += $nb_result_categories;
				}
				if ($opac_modules_search_indexint && $look_INDEXINT) {
					require_once($base_path.'/search/level1/indexint.inc.php');	
					$total_results += $nb_result_indexint;
				}
		
				if ($opac_modules_search_keywords && $look_KEYWORDS) {	
					require_once($base_path.'/search/level1/keyword.inc.php');
					$total_results += $nb_result_keywords;
				}
		
				if ($opac_modules_search_abstract && $look_ABSTRACT) {
					require_once($base_path.'/search/level1/abstract.inc.php');
					$total_results += $nb_result_abstract;
				}
				
				if ($opac_modules_search_docnum && $look_DOCNUM) {
					require_once($base_path.'/search/level1/docnum.inc.php');
					$total_results += $nb_result_docnum;
				}
	
				if ($opac_modules_search_all && $look_ALL) {
	  				require_once($base_path.'/search/level1/tous.inc.php');	
	  				$total_results += $nb_result;
	  				$nb_all_results=$nb_result;
	  			}
	

	  			if ($opac_show_suggest) {
	  				$bt_sugg = "&nbsp;&nbsp;&nbsp;<a href=# ";
	  				if ($opac_resa_popup) $bt_sugg .= " onClick=\"w=window.open('./do_resa.php?lvl=make_sugg&oresa=popup','doresa','scrollbars=yes,width=600,height=600,menubar=0,resizable=yes'); w.focus(); return false;\"";
	  				else $bt_sugg .= "onClick=\"document.location='./do_resa.php?lvl=make_sugg&oresa=popup' \" ";
	  				$bt_sugg.= " >".$msg[empr_bt_make_sugg]."</a>";
	  			} else $bt_sugg="";
	  				
	  			if ($opac_allow_external_search) 
						$bt_external="<a href='javascript:document.search_input.action=\"$base_path/index.php?search_type_asked=external_search&external_type=simple\"; document.search_input.submit();'>".$msg["connecteurs_external_search_sources"]."</a>";
					else $bt_external="";
	
				// affichage pied-de-page
				if(!$total_results && (!$opac_allow_affiliate_search || ($opac_modules_search_docnum && $look_DOCNUM) )) {
					print $msg[no_result]." ".$bt_sugg.($bt_external?"&nbsp;&nbsp;&nbsp;$bt_external":"");
				} else if ($bt_external || $bt_sugg) print "<br /><div class='row'>".$bt_sugg.($bt_external?"&nbsp;&nbsp;&nbsp;$bt_external":"")."</div>";
				//if (($nb_all_results)&&(!$get_query))
				
				//Suggestions
				if(!$total_results && $opac_simple_search_suggestions){
					$tableSuggest="";
					if ($opac_autolevel2==2) {
						$actionSuggest = $base_path."/index.php?lvl=more_results&autolevel1=1";
					} else {
						$actionSuggest = $base_path."/index.php?lvl=search_result&search_type_asked=simple_search";
					}
			
			    	$termes=str_replace('*','',stripslashes($user_query));
					if (trim($termes)){
						$suggestion = new suggest($termes);
						$tmpArray = array();
						$tmpArray = $suggestion->listUniqueSimilars();
						
						if(count($tmpArray)){
							$tableSuggest.="<table><tbody>";
							foreach($tmpArray as $word){
								$tableSuggest.="<tr>
									<td>
										<a href='".$actionSuggest."&user_query=".rawurlencode($word)."'>
											<span class='facette_libelle'>".$word."</span>
										</a>
									</td>
								</tr>";
							}
							$tableSuggest.="</tbody></table>";
							
							print "<br><h3>".$msg['facette_suggest']."</h3>".$tableSuggest;
						}
					}
				}
				
				if (($nb_all_results)&&($opac_autolevel2)&& !$get_query) print "<script>document.forms['search_tous'].submit();</script>"; else print "<script>document.getElementById('search_result').style.display='';</script>";
			} else {
				print $msg[no_result];
			}
		break;
		case "extended_search":
			print "<h3><span>$msg[search_result]</span></h3>";
			require_once($base_path.'/search/level1/extended.inc.php');
			if ($opac_show_suggest) {
				$bt_sugg = "&nbsp;&nbsp;&nbsp;<a href=# ";		
				if ($opac_resa_popup) $bt_sugg .= " onClick=\"w=window.open('./do_resa.php?lvl=make_sugg&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;\"";
				else $bt_sugg .= "onClick=\"document.location='./do_resa.php?lvl=make_sugg&oresa=popup' \" ";			
				$bt_sugg.= " >".$msg[empr_bt_make_sugg]."</a>";
			} else $bt_sugg="";
			if ($opac_allow_external_search) 
				$bt_external="<a href='javascript:document.search_form.action=\"$base_path/index.php?search_type_asked=external_search&external_type=multi\"; document.search_form.submit();'>".$msg["connecteurs_external_search_sources"]."</a>";
			else $bt_external="";
				
			if (!$nb_result_extended) { 
				print $msg[no_result]." ".$bt_sugg.($bt_external?"&nbsp;&nbsp;&nbsp;$bt_external":"")." ".htmlentities($search_error_message,ENT_QUOTES,$charset);
			} else if ($bt_external || $bt_sugg) print $bt_sugg.($bt_external?"&nbsp;&nbsp;&nbsp;$bt_external":"");
			break;
		case "external_search":
			if ($_SESSION["ext_type"]!="multi")
				print "<h3><span>$msg[search_result_for]<b>".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."</b></span></h3>";
			else
				print "<h3><span>$msg[search_result]</span></h3>";
			require_once($base_path.'/search/level1/external.inc.php');
			if (!$nb_result_external) { 
				print $msg[no_result]." ".htmlentities($search_error_message,ENT_QUOTES,$charset);
			}
			break;
		// *************************************************
		// Tags
		case "tags_search":
			print "<h3><span>$msg[search_result_for]<b>".htmlentities(stripslashes($user_query),ENT_QUOTES,$charset)."</b></span></h3>";
			$tag = new tags();
			if ($user_query=="*") echo $tag->listeAlphabetique();
				else echo $tag->chercheTag($user_query);
			break;
	
		}
} else {
	switch ($mode) {
		case "keyword":
			require_once($base_path.'/search/level1/keyword.inc.php');
			break;
	}
}	

print "</div>";
print $search_result_footer;

/** Fin affichage de la page **/
