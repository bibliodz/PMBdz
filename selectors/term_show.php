<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: term_show.php,v 1.15 2012-08-23 14:58:11 mbertin Exp $

$base_path="..";                            
$base_auth = ""; 

require_once ("$base_path/includes/init.inc.php"); 
require_once("$class_path/term_show.class.php"); 
require_once ("$javascript_path/misc.inc.php");
require_once($base_path."/selectors/templates/category.tpl.php");

print reverse_html_entities();

//R�cup�ration des param�tres du formulaire appellant
$base_query = "caller=$caller&p1=$p1&p2=$p2&no_display=$no_display&bt_ajouter=$bt_ajouter&parent=&history=".rawurlencode(stripslashes($term))."&history_thes=".rawurlencode(stripslashes($id_thes))."&dyn=$dyn&keep_tilde=$keep_tilde&callback=".$callback."&infield=".$infield;
echo $jscript_term;


function parent_link($categ_id,$categ_see) {
	global $caller,$keep_tilde,$base_path,$p1;
	global $charset;
	global $thesaurus_mode_pmb ;
	global $callback;
	
	if ($categ_see) $categ=$categ_see; else $categ=$categ_id;
	$tcateg =  new category($categ);
	
	if ($tcateg->commentaire_public) {
		$zoom_comment = "<div id='zoom_comment".$tcateg->id."' style='border: solid 2px #555555; background-color: #FFFFFF; position: absolute; display:none; z-index: 2000;'>".htmlentities($tcateg->commentaire_public,ENT_QUOTES, $charset)."</div>" ;
		$java_comment = " onmouseover=\"z=document.getElementById('zoom_comment".$tcateg->id."'); z.style.display=''; \" onmouseout=\"z=document.getElementById('zoom_comment".$tcateg->id."'); z.style.display='none'; \"" ;
	} else {
		$zoom_comment = "" ;
		$java_comment = "" ;
	}
	if ($thesaurus_mode_pmb && $caller=='notice') $nom_tesaurus='['.$tcateg->thes->getLibelle().'] ' ;
		else $nom_tesaurus='' ;
	if($tcateg->not_use_in_indexation && ($caller == "notice")){
		$link= "<img src='$base_path/images/interdit.gif' hspace='3' border='0'/>";
	}elseif(((!$tcateg->is_under_tilde) || $keep_tilde)){
		if($caller == "search_form"){
			$lib_final=$tcateg->libelle;
		}else{
			$lib_final=$nom_tesaurus.$tcateg->catalog_form;
		}
		$link="<a href=\"\" onclick=\"set_parent('$caller', '$tcateg->id', '".htmlentities(addslashes($lib_final),ENT_QUOTES, $charset)."','$callback','".$tcateg->thes->id_thesaurus."'); return false;\" $java_comment><span class='plus_terme'><span>+</span></span></a>$zoom_comment";
	}
	$visible=true;
	$r=array("VISIBLE"=>$visible,"LINK"=>$link);
	return $r;
}

$ts=new term_show(stripslashes($term),"term_show.php",$base_query,"parent_link",$keep_tilde, $id_thes);
echo pmb_bidi($ts->show_notice());
?>