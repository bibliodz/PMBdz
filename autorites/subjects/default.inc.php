<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: default.inc.php,v 1.41 2014-03-12 15:42:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$base_url = "./autorites.php?categ=categories&sub=&id=0&parent=";

// inclusions diverses
include("$include_path/templates/category.tpl.php");
require_once("$class_path/category.class.php");
require_once("$class_path/thesaurus.class.php");
require_once("$include_path/misc.inc.php");

if (!$parent) $parent = 0;
if (!$id) $id = 0;


//recuperation du thesaurus session 
if(!$id_thes) {
	$id_thes = thesaurus::getSessionThesaurusId();
} else {
	thesaurus::setSessionThesaurusId($id_thes);
}

$liste_thesaurus = '';
$browser_top = '';


//affichage du selectionneur de thesaurus et du lien vers les th�saurus
$liste_thesaurus = thesaurus::getThesaurusList();
$sel_thesaurus = '';
$lien_thesaurus = '';

if ($thesaurus_mode_pmb != 0) {	 //la liste des thesaurus n'est pas affich�e en mode monothesaurus
	$sel_thesaurus = "<select class='saisie-30em' id='id_thes' name='id_thes' ";
	$sel_thesaurus.= "onchange = \"document.location = '".$base_url."&id_thes='+document.getElementById('id_thes').value; \">" ;
	foreach($liste_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
		$sel_thesaurus.= "<option value='".$id_thesaurus."' "; ;
		if ($id_thesaurus == $id_thes) $sel_thesaurus.= " selected";
		$sel_thesaurus.= ">".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</option>";
	}
	$sel_thesaurus.= "<option value=-1 ";
	if ($id_thes == -1) $sel_thesaurus.= "selected ";
	$sel_thesaurus.= ">".htmlentities($msg['thes_all'],ENT_QUOTES, $charset)."</option>";
	$sel_thesaurus.= "</select>&nbsp;";

	$lien_thesaurus = "<a href='./autorites.php?categ=categories&sub=thes'>".$msg[thes_lien]."</a>";

}	
$user_query=str_replace("<!-- sel_thesaurus -->",$sel_thesaurus,$user_query);
$user_query=str_replace("<!-- lien_thesaurus -->",$lien_thesaurus,$user_query);

if ($id_thes>=1) 
	$lien_imprimer_thesaurus = "&nbsp;<a href='#' onClick=\"openPopUp('./print_thesaurus.php?current_print=2&action=print_prepare&aff_num_thesaurus=".$id_thes."','print', 500, 600, -2, -2, 'scrollbars=yes,menubar=0,resizable=yes'); return false;\">".$msg[print_thesaurus]."</a> ";
else 
	$lien_imprimer_thesaurus = "" ;
$user_query=str_replace("<!-- imprimer_thesaurus -->",$lien_imprimer_thesaurus,$user_query);


//affichage du choix de langue pour la recherche
$sel_langue = "<div class='row'>";
$sel_langue.= "<input type='checkbox' name='lg_search' id='lg_search' value='1' />&nbsp;".htmlentities($msg['thes_sel_langue'],ENT_QUOTES, $charset);
$sel_langue.= "</div><br />";
$user_query=str_replace("<!-- sel_langue -->",$sel_langue,$user_query);
$user_query=str_replace("!!user_input!!",htmlentities(stripslashes($user_input),ENT_QUOTES, $charset),$user_query);

// nombre de r�f�rences par pages
if ($nb_per_page_author != "") 
	$nb_per_page = $nb_per_page_author ;
	else $nb_per_page = 10;


if ($id_thes == -1) { //on affiche la liste des thesaurus 
	
	$odd_even = 0;
	
	foreach($liste_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
		if ($odd_even==0) {
			$browser_content .= "	<tr class='odd'>";
			$odd_even=1;
		} else if ($odd_even==1) {
			$browser_content .= "	<tr class='even'>";
			$odd_even=0;
		}

		$browser_content.= "<td><a href='".$base_url."&id_thes=".$id_thesaurus."'>".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</a>";
		$browser_content.= "</td></tr>";	
	}		
	
} else {


	$thes = new thesaurus($id_thes);
	
	//si le parent n'est pas passe, on positionne
	//le parent comme �tant le noeud racine du thesaurus
	if (!$parent) {
		$parent = $thes->num_noeud_racine;
	}
	
	//Si le parent n'as pas de fils, on remonte au noeud sup�rieur.
	if (!noeuds::hasChild($parent)) {
		$noeud=new noeuds($parent);
		$parent = $noeud->num_parent;
	}
	
	
	if($thes == NULL){
		$browser_content = $msg[4051];
		affiche();
		exit;
	}

// on r�cup�re le nombre de lignes qui vont bien
	$requete = "select count(1) " ;
	$requete.= "from noeuds ";
	$requete.= "where ";
	$requete.= "noeuds.num_thesaurus = '".$id_thes."'  ";
	$requete.= "and noeuds.num_parent = '".$parent."' ";
	if ($last_param)
		$requete .= $limit_param;
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = mysql_result($res, 0, 0);

	if(!$page) $page=1;
	$debut =($page-1)*$nb_per_page;	

	$nbmax_cat=500;
	$pagine=false;
	
	$requete = "select catdef.num_noeud, ";
	$requete.= "autorite, ";
	$requete.= "if (catlg.num_noeud is null, catdef.libelle_categorie, catlg.libelle_categorie ) as lib ";
	$requete.= "from noeuds left join categories as catdef on noeuds.id_noeud = catdef.num_noeud and catdef.langue = '".$thes->langue_defaut."' ";
	$requete.= "left join categories as catlg on catdef.num_noeud = catlg.num_noeud and catlg.langue = '".$lang."' ";
	$requete.= "where ";
	$requete.= "noeuds.num_thesaurus = '".$id_thes."'  ";
	$requete.= "and noeuds.num_parent = '".$parent."' ";
	if ($last_param) {
		$requete .= $tri_param." ";
		$requete.= $limit_param." ";
	} else {
		$requete.= "order by lib ";
		if($nbr_lignes>$nbmax_cat){
			$nb_per_page = $nbmax_cat;
			$pagine = true;
			$debut =($page-1)*$nb_per_page;
			$requete.= "limit ".$debut.",".$nb_per_page." ";
		}
	}
	$result = mysql_query($requete, $dbh);
		
	if(mysql_num_rows($result)) {
		
		$browser_top = "<a href='./autorites.php?categ=categories&sub=&parent=0&id=0'>";
		$browser_top.= "<img src='./images/top.gif' border='0' hspace='3' align='middle'></a>";
		
		// r�cup�ration de la 1�re entr�e et cr�ation du header
		$cat = mysql_fetch_row($result);
		$tcateg =  new category($cat[0]);
		if(sizeof($tcateg->path_table)) {
			for($i=0; $i < sizeof($tcateg->path_table) - 1; $i++){
				$browser_header ? $browser_header .= '&gt;' : $browser_header = '';
				$browser_header .= "<a href='";
				$browser_header .= $base_url;
				$browser_header .= $tcateg->path_table[$i]['id'];
				$browser_header .= "' title='".$tcateg->path_table[$i]['commentaire']."'>";
				$browser_header .= $tcateg->path_table[$i]['libelle'];
				$browser_header .= "</a>";
			}
			$browser_header ? $browser_header .= '&gt;<strong>' : $browser_header = '<strong>';
			$browser_header .= $tcateg->path_table[sizeof($tcateg->path_table) - 1]['libelle'];
			$browser_header .= '</strong>';
		}
		
		$browser_content .= "<tr>
			<th>".$msg[103]."</th>
			<!--!!col_num_autorite!!-->
			<th>".$msg["count_notices_assoc"]."</th>
			</tr>";
		
		$num_auth_present=false;
		$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='category' AND TRIM(authority_number) !='' LIMIT 1";
		$res_aut=mysql_query($req,$dbh);
		if($res_aut && mysql_num_rows($res_aut)){
			$browser_content=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$browser_content);
			$num_auth_present=true;
		}
	
		$odd_even=0;
		mysql_data_seek($result, 0);
		while($cat = mysql_fetch_row($result)) {
			$tcateg =  new category($cat[0]);
			if ($odd_even==0) {
				$browser_content .= "	<tr class='odd'>";
				$odd_even=1;
			} else if ($odd_even==1) {
				$browser_content .= "	<tr class='even'>";
				$odd_even=0;
			}
			
			$notice_count = $tcateg->notice_count(false);
			
			$browser_content .= "<td>";
			if($tcateg->has_child) {
				$browser_content .= "<a href='$base_url".$tcateg->id."'>";
				$browser_content .= "<img src='./images/folderclosed.gif' hspace='3' border='0'></a>";
			} else {
				$browser_content .= "<img src='./images/doc.gif' hspace='3' border='0'>";
			}
			if ($cat[1] || $tcateg->commentaire) {
				$zoom_comment = "<div id='zoom_comment".$tcateg->id."' style='border: solid 2px #555555; background-color: #FFFFFF; position: absolute; display:none; z-index: 2000;'>";
				if ($cat[1]) $zoom_comment.=htmlentities('('.$cat[1].') ', ENT_QUOTES, $charset);
				if ($tcateg->commentaire) $zoom_comment.= htmlentities($tcateg->commentaire,ENT_QUOTES, $charset);
				$zoom_comment.="</div>";
				$java_comment = " onmouseover=\"z=document.getElementById('zoom_comment".$tcateg->id."'); z.style.display=''; \" onmouseout=\"z=document.getElementById('zoom_comment".$tcateg->id."'); z.style.display='none'; \"" ;
			} else {
				$zoom_comment = "" ;
				$java_comment = "" ;
			}
			$browser_content .= "<a href='./autorites.php?categ=categories&sub=categ_form&parent=".$parent."&id=".$tcateg->id."' $java_comment >";
			$browser_content .= $tcateg->libelle;
			$browser_content .= '</a>';
			$browser_content .= $zoom_comment.'</td>';
			
			//Num�ros d'autorite
			if($num_auth_present){
				$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='category' AND num_authority='".$tcateg->id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
				$res_aut=mysql_query($requete,$dbh);
				if($res_aut && mysql_num_rows($res_aut)){
					$browser_content .= "<td>";
					$first=true;
					while ($aut = mysql_fetch_object($res_aut)) {
						if(!$first)$browser_content .=", ";
						$browser_content .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
						if($tmp=trim($aut->origin_authorities_name)){
							$browser_content .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
						}
						$first=false;
					}
					$browser_content .= "</td>";
				}else{
					$browser_content .= "<td>&nbsp;</td>";
				}
			}
			
			if($notice_count && $notice_count!=0)
				$browser_content .= "<td style='cursor: pointer' onmousedown=\"document.location='./catalog.php?categ=search&mode=1&etat=aut_search&aut_type=categ&aut_id=$tcateg->id'\">".$notice_count."</td>";
			else $browser_content .= "<td>&nbsp;</td>";
			$browser_content .='</tr>';
		} //fin while
	} else {
		$browser_content = $msg[4051];
	}
}	
	
	//Barre de navigation
	$url_base=$PHP_SELF."?categ=categories&sub=&id_thes=".$id_thes."&parent=".$parent;
	if (!$last_param && $pagine)
		$nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
	else $nav_bar = "";

	affiche($nav_bar);
	exit;
	

// cr�ation du tableau � partir du template et affichage
function affiche($nav_bar='') {
	
	global $categ_browser;
	global $browser_top;
	global $liste_thesaurus;
	global $browser_header;
	global $browser_content;
	global $parent;
	global $user_query;
	
	$categ_browser = str_replace('!!browser_top!!', $browser_top, $categ_browser);		
	$categ_browser = str_replace('!!liste_thesaurus!!', $liste_thesaurus, $categ_browser);		
	$categ_browser = str_replace('!!browser_header!!', $browser_header, $categ_browser);
	$categ_browser = str_replace('!!browser_content!!', $browser_content, $categ_browser);
	$categ_browser = str_replace('!!id_parent!!', $parent, $categ_browser);
	categ_browser::search_form($parent);
	print pmb_bidi($categ_browser);
	if ($nav_bar) print $nav_bar;
	
}


?>