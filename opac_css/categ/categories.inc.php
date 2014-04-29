<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: categories.inc.php,v 1.34 2012-08-23 14:58:11 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// affichage du sommaire g�n�ral des cat�gories

require_once ($base_path.'/classes/thesaurus.class.php');
require_once ($base_path.'/classes/noeuds.class.php');


//Si l'on d�finit une liste de th�saurus � afficher � l'Opac on la reprend
if ($opac_show_categ_browser_home_id_thes) {
	$opac_show_categ_browser_home_id_thes_tab=explode(",",$opac_show_categ_browser_home_id_thes);
}else{
	$opac_show_categ_browser_home_id_thes_tab=array();
}

//recuperation du thesaurus session 
if (!$id_thes){
	if(count($opac_show_categ_browser_home_id_thes_tab)){
		$id_thes=$opac_show_categ_browser_home_id_thes_tab[0];//Je reprends le premier th�saurus de la liste si pas de th�saurus trouv�
	}else{
		$id_thes = thesaurus::getSessionThesaurusId();
	}
}

//Si tous th�saurus selectionn� en session, on prend celui par d�faut
if ($id_thes == '-1'){
	$id_thes = $opac_thesaurus_defaut;
}

thesaurus::setSessionThesaurusId($id_thes);
$thes = new thesaurus($id_thes);
	
//on positionne le parent comme �tant le noeud racine du thesaurus
$parent = $thes->num_noeud_racine;

// on constitue un tableau avec les cat�gories � afficher
$requete = "select ";
$requete.= "noeuds.id_noeud AS num_noeud, noeuds.num_renvoi_voir, noeuds.num_thesaurus,";
if(($lang==$thes->langue_defaut) || (in_array($lang, thesaurus::getTranslationsList())===false)){
		$requete.=" catdef.comment_public as comment_public,";
		$requete.= "catdef.libelle_categorie ";
		$requete.= "from noeuds JOIN categories as catdef on noeuds.id_noeud=catdef.num_noeud and catdef.langue = '".$thes->langue_defaut."' "; 
		$requete.= "where ";
		$requete.= "noeuds.num_thesaurus = '".$id_thes."' ";
		$requete.= "and noeuds.num_parent = '".$thes->num_noeud_racine."' ";
		$requete.= "and catdef.libelle_categorie not like '~%' ";
}else{
		$requete.=" if (catlg.num_noeud is null, catdef.comment_public, catlg.comment_public ) as comment_public,";
		$requete.= "if (catlg.num_noeud is null, catdef.libelle_categorie, catlg.libelle_categorie ) as libelle_categorie ";
		$requete.= "from noeuds left join categories as catdef on noeuds.id_noeud=catdef.num_noeud and catdef.langue = '".$thes->langue_defaut."' "; 
		$requete.= "left join categories as catlg on catdef.num_noeud = catlg.num_noeud and catlg.langue = '".$lang."' "; 
		$requete.= "where ";
		$requete.= "noeuds.num_thesaurus = '".$id_thes."' ";
		$requete.= "and noeuds.num_parent = '".$thes->num_noeud_racine."' ";
		$requete.= "and if (catlg.num_noeud is null, catdef.libelle_categorie not like '~%', catlg.libelle_categorie not like '~%') ";
}
$requete.= "order by libelle_categorie limit ".$opac_categories_max_display;
$result = mysql_query($requete, $dbh);

while ($level0 = mysql_fetch_object($result)) {
	// mise en forme de la cat�gorie chapeau
	if(!$level0->num_renvoi_voir) {
		$id = $level0->num_noeud;
		$link = $level0->libelle_categorie;
	} else {
		$id = $level0->num_renvoi_voir;
		$link =  '<i>'.$level0->libelle_categorie.'@</i>';
	}
			
	// Si il y a pr�sence d'un commentaire affichage du layer					
	$result_com = categorie::zoom_categ($id, $level0->comment_public);	
	
	$categ = "<a href='./index.php?&lvl=categ_see&id=$id&main=1&id_thes=".$level0->num_thesaurus."'".$result_com['java_com'].">$link</a>";
	$categ .= $result_com['zoom'];
	
	$requete = "select ";
	$requete.= "noeuds.id_noeud AS num_noeud, noeuds.num_renvoi_voir, noeuds.num_thesaurus,";
	if(($lang==$thes->langue_defaut) || (in_array($lang, thesaurus::getTranslationsList())===false)){
			$requete.=" catdef.comment_public as comment_public,";
			$requete.= "catdef.libelle_categorie ";
			$requete.= "from noeuds JOIN categories as catdef on noeuds.id_noeud=catdef.num_noeud and catdef.langue = '".$thes->langue_defaut."' "; 
			$requete.= "where ";
			$requete.= "noeuds.num_parent = '".$level0->num_noeud."' ";
			$requete.= "and catdef.libelle_categorie not like '~%' ";
	}else{
			$requete.=" if (catlg.num_noeud is null, catdef.comment_public, catlg.comment_public ) as comment_public,";
			$requete.= "if (catlg.num_noeud is null, catdef.libelle_categorie, catlg.libelle_categorie ) as libelle_categorie ";
			$requete.= "from noeuds left join categories as catdef on noeuds.id_noeud=catdef.num_noeud and catdef.langue = '".$thes->langue_defaut."' "; 
			$requete.= "left join categories as catlg on catdef.num_noeud = catlg.num_noeud and catlg.langue = '".$lang."' "; 
			$requete.= "where ";
			$requete.= "noeuds.num_parent = '".$level0->num_noeud."' ";
			$requete.= "and if (catlg.num_noeud is null, catdef.libelle_categorie not like '~%', catlg.libelle_categorie not like '~%') ";
	}
	$requete.= "order by ".$opac_categories_sub_mode." limit ".$opac_categories_sub_display;
	$result_bis = mysql_query($requete, $dbh);


	$child = array();
	while($sub_categ = mysql_fetch_object($result_bis)) {
		if(!$sub_categ->num_renvoi_voir) {
			$id = $sub_categ->num_noeud;
			$link = $sub_categ->libelle_categorie;
		} else {
			$id = $sub_categ->num_renvoi_voir;
			$link =  '<i>'.$sub_categ->libelle_categorie.'@</i>';
		}
		// Si il y a pr�sence d'un commentaire affichage du layer					
		$result_com = categorie::zoom_categ($id, $sub_categ->comment_public);
										
		$child[] = "<a href='./index.php?lvl=categ_see&id=$id&main=1&id_thes=".$sub_categ->num_thesaurus."'".$result_com['java_com'].">".$link."</a>".$result_com['zoom'];
		
	}
	$categ_array[] = array( categ => $categ,
							child => $child);						
}	
	
//affichage des liens vers les autres th�saurus 
$liste_thesaurus = thesaurus::getThesaurusList($opac_show_categ_browser_home_id_thes);
if($opac_show_categ_browser_home_id_thes_tab){
	$liste_thesaurus_tmp=array();
	foreach ( $opac_show_categ_browser_home_id_thes_tab as $value ) {
		if($liste_thesaurus[$value]){
	       	$liste_thesaurus_tmp[$value]=$liste_thesaurus[$value];
	       	unset($liste_thesaurus[$value]);
		}
	}
	foreach ( $liste_thesaurus as $key => $value ) {
	       	$liste_thesaurus_tmp[$key]=$value;
	}
	$liste_thesaurus = $liste_thesaurus_tmp;
}
$liens_thesaurus = '';

if ($opac_thesaurus != 0) {	 //la liste des thesaurus n'est pas affich�e en mode monothesaurus
	$liens_thesaurus.= "<ul class='search_tabs'>";
	foreach($liste_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
		$liens_thesaurus.= '<li ';
		if ($id_thesaurus == $id_thes) 
			$liens_thesaurus.= "id='current'>".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</li>\n" ;
		else
			$liens_thesaurus.= "><a href='./index.php?id_thes=".$id_thesaurus."'>".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</a></li>\n" ;
	}
	$liens_thesaurus.= "</ul>";
}	

$tpl_div_categories=str_replace("<!-- liens_thesaurus -->",$liens_thesaurus,$tpl_div_categories);

// Pr�paration � l'affichage
$toprint = $tpl_div_categories;
$toprint_rootcategories = "";
$to_jump=0;

while(list($cle, $valeur) = each($categ_array)) {
	if ($to_jump==0) $toprint_rootcategories .="<div class='row_categ'>";
	$toprint_rootcategories .= $tpl_div_category;
	$toprint_rootcategories = str_replace("!!category_name!!", $valeur['categ'], $toprint_rootcategories);
	$toprint_subcategories = "";
	$cpt=0;
	while(list($key, $sub) = each($valeur[child])) {
		$toprint_subcategories .= $tpl_subcategory;
		$toprint_subcategories = str_replace("!!sub_category!!", $sub, $toprint_subcategories);
		$cpt++;
	}
	// evite de mettre ... si moins que $opac_categories_sub_display
	if($toprint_subcategories && ($cpt== $opac_categories_sub_display)) $toprint_subcategories .= "...";

	$toprint_rootcategories = str_replace("!!sub_categories!!", $toprint_subcategories, $toprint_rootcategories);
	if ($to_jump==($opac_categories_columns-1)) $toprint_rootcategories.="</div>";
	$to_jump++;
	if ($to_jump>($opac_categories_columns-1)) $to_jump=0;
}

if ($to_jump!=0) {
	while ($to_jump<=($opac_categories_columns-1) ) {
		$toprint_rootcategories.="<div class='category'>&nbsp;</div>";
		$to_jump++ ;
	}
	$toprint_rootcategories.="</div>";
}

$toprint_rootcategories.="<div class='div_clr'></div>";
$toprint = str_replace("!!root_categories!!", $toprint_rootcategories, $toprint);
print pmb_bidi($toprint);
