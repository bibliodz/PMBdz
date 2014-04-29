<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bannette_func.inc.php,v 1.35 2014-03-12 14:41:30 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($base_path.'/includes/templates/notice_display.tpl.php');
require_once($base_path.'/includes/explnum.inc.php');
require_once($class_path.'/bannette.class.php');
if($gestion_acces_active && $gestion_acces_empr_notice) {
	require_once($class_path.'/acces.class.php');
}

// tableau des notices d'un caddie
function notices_bannette($id_bannette, &$notices,$date_diff='') {
	global $dbh ;
	global $opac_bannette_notices_order ;
	global $gestion_acces_active, $gestion_acces_empr_notice;
	
	if (!$opac_bannette_notices_order) {
		$opac_bannette_notices_order =" index_serie, tnvol, index_sew ";
	} else {
		$opac_bannette_notices_order = " $opac_bannette_notices_order ";
	}

	$acces_j='';
	if($gestion_acces_active && $gestion_acces_empr_notice) {
		$ac = new acces();
		$dom_2 = $ac->setDomain(2);
		$acces_j = $dom_2->getJoin($_SESSION['id_empr_session'],4,'notice_id');
	}
	
	if(!$date_diff){
		// on constitue un tableau avec les notices de la bannette
		$query_notice = "select distinct notice_id, niveau_biblio from bannette_contenu, notices $acces_j where num_bannette='".$id_bannette."' and num_notice=notice_id order by $opac_bannette_notices_order ";
		$result_notice = mysql_query($query_notice, $dbh);
		if (mysql_num_rows($result_notice)) {
			while (($notice=mysql_fetch_object($result_notice))) {
				$notices[$notice->notice_id]= $notice->niveau_biblio ; 
			}
		}
	}else{			
		
		// on constitue un tableau avec les notices des archives de diffusion
		$query_notice = "select distinct num_notice_arc as notice_id, niveau_biblio from dsi_archive, notices $acces_j where num_banette_arc='".$id_bannette."' and num_notice_arc=notice_id
		 and date_diff_arc = '$date_diff' order by $opac_bannette_notices_order ";
		
		$result_notice = mysql_query($query_notice, $dbh);
		if (mysql_num_rows($result_notice)) {
			while (($notice=mysql_fetch_object($result_notice))) {
				$notices[$notice->notice_id]= $notice->niveau_biblio ; 
			}
		}		
	}
	
}

$affiche_bannette_tpl="
	<div class='bannette' id='banette_!!id_bannette!!'>
		<div class='colonne2' style='width: 20%;'>
			<table>
				!!historique!!			
			</table>
		</div>
		<div class='colonne2' style='width: 80%;'>
			!!diffusion!!							
		</div>	
		<div class='row'></div>
	</div>
";

// function affiche_bannette : affiche les bannettes et leur contenu pour l'abonn�
// param�tres :
//	$bannettes : les num�ros des bannettes s�par�s par les ',' toutes si vides
//	$aff_notices_nb : nombres de notices affich�es : toutes = 0 
//	$mode_aff_notice : mode d'affichage des notices, REDUIT (titre+auteur principal) ou ISBD ou PMB ou les deux : dans ce cas : (titre + auteur) en ent�te du truc
//	$depliable : affichage des notices une par ligne avec le bouton de d�pliable
//	$link_to_bannette : lien pour afficher le contenu de la bannette
//	$htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="" : les id, class et zindex du <DIV > englobant le r�sultat de la fonction
//	$liens_opac : tableau contenant les url destinatrices des liens si voulu 
function affiche_bannette($bannettes="", $aff_notices_nb=0, $mode_aff_notice=AFF_BAN_NOTICES_BOTH, $depliable=AFF_BAN_NOTICES_DEPLIABLES_OUI, $link_to_bannette="", $liens_opac=array(), $date_diff='', $htmldiv_id="bannette-container", $htmldiv_class="bannette-container", $htmldiv_zindex="",$home=false ) {
	global $dbh;
	global $msg,$charset;
	global $opac_notice_affichage_class;
	global $affiche_bannette_tpl;
	// r�cup�ration des bannettes
	if($home){
		$tableau_bannettes = tableau_bannette_accueil($bannettes) ;
	}else{
		$tableau_bannettes = tableau_bannette($bannettes) ;
	}
	
	if (!sizeof($tableau_bannettes))		
		return "" ;
	
	// pr�paration du div comme il faut
	$retour_aff = "<div id='$htmldiv_id' class='$htmldiv_class'";
	if ($htmldiv_zindex) $retour_aff .=" zindex='$htmldiv_zindex' ";
	$retour_aff .=" >";
	for ($i=0; $i<sizeof($tableau_bannettes); $i++ ) {
		$aff_banette="";
		$id_bannette=$tableau_bannettes[$i]['id_bannette'] ;
		$comment_public=$tableau_bannettes[$i]['comment_public'] ;
		$aff_date_last_envoi=$tableau_bannettes[$i]['aff_date_last_envoi'] ;
		$aff_banette.="\n<div class='bannette-titre'><h1>";
		$aff_banette.="<a href='cart_info.php?lvl=dsi&id=$id_bannette' target='cart_info'><img src='images/basket_small_20x20.gif' border='0' title=\"".$msg[notice_title_basket]."\" alt=\"".$msg[notice_title_basket]."\"></a>";
		if ($link_to_bannette) $aff_banette.="<a href=\"".str_replace("!!id_bannette!!",$id_bannette,$link_to_bannette)."\">";
		if($date_diff){
			$aff_banette.= htmlentities($comment_public." - ".formatdate($date_diff),ENT_QUOTES, $charset);
		}else{
			$aff_banette.= htmlentities($comment_public." - ".$aff_date_last_envoi,ENT_QUOTES, $charset);
		}	
		if ($link_to_bannette) $aff_banette.="</a>";
		$aff_banette.= "</h1></div>";
		
		$notices = array();
		notices_bannette($id_bannette, $notices,$date_diff) ;

		if ($aff_notices_nb>0) $limite_notices = min($aff_notices_nb, count($notices)) ;
		elseif ($aff_notices_nb<0) $limite_notices = min($aff_notices_nb, count($notices)) ;
		else  $limite_notices = count($notices) ;
		reset ($notices) ;
		$limit=0;
		if ($limite_notices) $aff_banette.= "<div id='etagere-notice-list_!!id_bannette!!'>";
		while ((list($idnotice, $niveau_biblio)= each($notices)) && ($limit<$limite_notices)) {
			$limit++;
			$notice = new $opac_notice_affichage_class($idnotice, $liens_opac, 1) ;
			// si notice visible
			if ($notice->visu_notice) {
				$notice->do_header();
				switch ($mode_aff_notice) {
					case AFF_BAN_NOTICES_REDUIT :	
						$aff_banette .= "<div class='etagere-titre-reduit'>".$notice->notice_header_with_link."</div>" ;
						break;
					case AFF_BAN_NOTICES_ISBD :	
						$notice->do_isbd();
						$notice->genere_simple($depliable, 'ISBD') ;
						$aff_banette .= $notice->result ;
						break;
					case AFF_BAN_NOTICES_PUBLIC :
						$notice->do_public();
						$notice->genere_simple($depliable, 'PUBLIC') ;
						$aff_banette .= $notice->result ;
						break;
					case AFF_BAN_NOTICES_BOTH :
						$notice->do_isbd();
						$notice->do_public();
						$notice->genere_double($depliable, 'PUBLIC') ;
						$aff_banette .= $notice->result ;
						break ;
					default:
						$notice->do_isbd();
						$notice->do_public();		
						$notice->genere_double($depliable, 'autre') ;
						$aff_banette .= $notice->result ;
						break ;
				}
			}
		}
		if ($limite_notices&&($limite_notices<count($notices))) $aff_banette.= "<br />...";
		if ($limite_notices) $aff_banette.= "</div>";

		$req="select distinct date_diff_arc from dsi_archive where num_banette_arc='".$id_bannette."' order by date_diff_arc desc";
		$res_arc=mysql_query($req, $dbh);
		$first=0;
		$diff_list="";
		while (($r = mysql_fetch_object($res_arc))){
			if(!$first)$libelle=$msg["dsi_archive_last"];
			else $libelle=sprintf($msg["dsi_archive_other"], formatdate($r->date_diff_arc));
			
			if($pair_impair == 'even')$pair_impair='odd'; else $pair_impair='even';
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='$link_to_bannette&date_diff=".$r->date_diff_arc."';\" ";
			$diff= "<tr style='cursor: pointer' class='$pair_impair' $tr_javascript><td>".$libelle ."</td></tr>";
			$first=1;
			
			$diff_list.=$diff;
		}			
		$tpl=$affiche_bannette_tpl;
		$tpl = str_replace ("!!historique!!",$diff_list,$tpl) ;
		$tpl = str_replace ("!!diffusion!!",$aff_banette,$tpl) ;
		$tpl=str_replace("!!id_bannette!!",$id_bannette,$tpl);
		$retour_aff.=$tpl;
	}
	
	// fermeture du DIV
	$retour_aff .= "</div><!-- fin id='$htmldiv_id' class='$htmldiv_class' -->";

	return $retour_aff ; 
	
	}
// retourne un tableau des bannettes de l'abonn�	
function tableau_bannette($id_bannette) {
	global $dbh, $msg ;
	global $id_empr ;
	
	if ($id_bannette) $clause = " and num_bannette in ('$id_bannette') ";
	//R�cup�ration des infos des bannettes
	$requete="select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi from bannettes join bannette_abon on num_bannette=id_bannette where num_empr='$id_empr' $clause order by date_last_envoi DESC ";
	$resultat=mysql_query($requete);
	while ($r=mysql_fetch_object($resultat)) {
		$requete="select count(1) as compte from bannette_contenu where num_bannette='$r->id_bannette'";
		$resnb=mysql_query($requete);
		$nb=mysql_fetch_object($resnb) ;
		if ($nb->compte)
			$tableau_bannette[] = array (
				'id_bannette' => $r->id_bannette,
				'comment_public' => $r->comment_public,
				'aff_date_last_envoi' => $r->aff_date_last_envoi,
				'nb_contenu' => $nb->compte
				);
		}
	return $tableau_bannette ; 
	}

// retourne un tableau des bannettes possibles de l'abonn� : les priv�es / les publiques : celles de sa cat�gorie et/ou celles auxquelles il est abonn�	
function tableau_gerer_bannette($priv_pub="PUB") {
	global $dbh, $msg ;
	global $id_empr, $empr_categ ;
	
	//R�cup�ration des infos des bannettes
	if ($priv_pub=="PUB") {
		$rqt = "select groupe_id from empr_groupe where empr_id=".$id_empr;
		$res = mysql_query($rqt,$dbh);
		$restrict = "categorie_lecteurs ='$empr_categ'";
		if ($res) {
			$groups = array();
			while ($r=mysql_fetch_object($res)) {
				$groups[] = $r->groupe_id;
			}
			if (count($groups)) {
				$restrict = "(".$restrict." or groupe_lecteurs IN (".implode(",",$groups)."))";
			}
		}
		$requete = "select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi, categorie_lecteurs, groupe_lecteurs, periodicite  from bannettes join bannette_abon on num_bannette=id_bannette where num_empr='$id_empr' and proprio_bannette=0 ";
		$requete .=" union select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi, categorie_lecteurs, groupe_lecteurs, periodicite from bannettes where ".$restrict." and proprio_bannette=0 ";
		$requete .=" order by comment_public ";
	} else {
		$requete .="select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi, categorie_lecteurs, groupe_lecteurs, periodicite from bannettes where proprio_bannette='$id_empr' ";
		$requete .=" order by comment_public ";
	}

	$resultat=mysql_query($requete,$dbh);
	while ($r=mysql_fetch_object($resultat)) {
		if ($priv_pub=="PUB") { 
			$requete_abonn="select CASE WHEN (count(*))>0 THEN 'checked' ELSE '' END as abonn from bannette_abon where num_bannette='$r->id_bannette' and num_empr='$id_empr' ";
			$res_abonn=mysql_query($requete_abonn,$dbh);
			$abonn=mysql_fetch_object($res_abonn) ;
		} else {
			$abonn = new stdClass();
			$abonn->abonn = "" ;
		}
		$requete="select count(1) as compte from bannette_contenu where num_bannette='$r->id_bannette'";
		$resnb=mysql_query($requete,$dbh);
		$nb=mysql_fetch_object($resnb) ; 
		$tableau_bannette[] = array (
				'id_bannette' => $r->id_bannette,
				'comment_public' => $r->comment_public,
				'aff_date_last_envoi' => $r->aff_date_last_envoi,
				'nb_contenu' => $nb->compte,
				'abonn' => $abonn->abonn,
				'categorie_lecteurs' => $r->categorie_lecteurs,
				'groupe_lecteurs' => $r->groupe_lecteurs,
				'periodicite' => $r->periodicite  
				);
	}
	return $tableau_bannette ; 
}

// function gerer_abon_bannette permet d'afficher un formulaire de gestion des abonnements aux bannettes du lecteur
// param�tres :
//	$bannettes : les num�ros des bannettes s�par�s par les ',' toutes si vides
//	$aff_notices_nb : nombres de notices affich�es : toutes = 0 
//	$mode_aff_notice : mode d'affichage des notices, REDUIT (titre+auteur principal) ou ISBD ou PMB ou les deux : dans ce cas : (titre + auteur) en ent�te du truc
//	$depliable : affichage des notices une par ligne avec le bouton de d�pliable
//	$link_to_bannette : lien pour afficher le contenu de la bannette
//	$htmldiv_id="etagere-container", $htmldiv_class="etagere-container", $htmldiv_zindex="" : les id, class et zindex du <DIV > englobant le r�sultat de la fonction
//	$liens_opac : tableau contenant les url destinatrices des liens si voulu 
function gerer_abon_bannette( $priv_pub="PUB", $link_to_bannette="", $htmldiv_id="bannette-container", $htmldiv_class="bannette-container", $htmldiv_zindex="" ) {
	global $dbh;
	global $charset;
	global $msg ;
	global $opac_allow_resiliation ;
	
	// r�cup�ration des bannettes
	$tableau_bannettes = tableau_gerer_bannette($priv_pub) ;
	
	if (!sizeof($tableau_bannettes)) return "" ;
	
	// pr�paration du tableau
	$retour_aff = "<div id='$htmldiv_id' class='$htmldiv_class'";
	if ($htmldiv_zindex) $retour_aff .=" zindex='$htmldiv_zindex' ";
	$retour_aff .=" >";
	$retour_aff .="<form name='bannette_abonn' method='post' >";
	$retour_aff .="<input type='hidden' name='lvl' value='bannette_gerer' />";
	$retour_aff .="<input type='hidden' name='enregistrer' value='$priv_pub' />";
	$retour_aff .="<table cellpadding='3px' cellspacing='5px'><tr>
							<th align='right' valign='bottom'>".$msg[dsi_bannette_gerer_abonn]."</th>
							<th align='left' valign='bottom'>".$msg[dsi_bannette_gerer_nom_liste]."</th>
							<th align='center' valign='bottom'>".$msg[dsi_bannette_gerer_date]."</th>
							<th align='center' valign='bottom'>".$msg[dsi_bannette_gerer_nb_notices]."</th>
							<th align='center' valign='bottom'>".$msg[dsi_bannette_gerer_periodicite]."</th>
							</tr>";
	for ($i=0; $i<sizeof($tableau_bannettes); $i++ ) {
		$id_bannette=$tableau_bannettes[$i]['id_bannette'] ;
		$comment_public=$tableau_bannettes[$i]['comment_public'] ;
		$aff_date_last_envoi=$tableau_bannettes[$i]['aff_date_last_envoi'] ;
		$retour_aff.="\n<tr><td align='right' valign='top'>";
		
		if (!$opac_allow_resiliation && $tableau_bannettes[$i]['categorie_lecteurs']) {
			$retour_aff.="\n<input type='checkbox' name='dummy[]' value='' ".$tableau_bannettes[$i]['abonn']." disabled />";
			$retour_aff.="<input type='hidden' name='bannette_abon[$id_bannette]' value='1' ".$tableau_bannettes[$i]['abonn']." style='display:none'/>";
		} else $retour_aff.="\n<input type='checkbox' name='bannette_abon[$id_bannette]' value='1' ".$tableau_bannettes[$i]['abonn']." />";
			 
		$retour_aff.="\n</td><td align='left' valign='top'>";
		
		if ($link_to_bannette) {
			// Construction de l'affichage de l'info bulle de la requette			
			$requete="select * from bannette_equation, equations where num_equation=id_equation and num_bannette=$id_bannette";	
			$resultat=mysql_query($requete);
			if (($r=mysql_fetch_object($resultat))) {				 
				$recherche =  $r->requete;		
				$equ = new equation ($r->num_equation);
				if(!is_object($search)) $search = new search();
				$search->unserialize_search($equ->requete);
				$recherche = $search->make_human_query();
				$zoom_comment = "<div id='zoom_comment".$id_bannette."' style='border: solid 2px #555555; background-color: #FFFFFF; position: absolute; display:none; z-index: 2000;'>";
 				$zoom_comment.= $recherche;
				$zoom_comment.="</div>";
				$java_comment = " onmouseover=\"z=document.getElementById('zoom_comment".$id_bannette."'); z.style.display=''; \" onmouseout=\"z=document.getElementById('zoom_comment".$id_bannette."'); z.style.display='none'; \"" ;		
			}
			$retour_aff.="<a href=\"".str_replace("!!id_bannette!!",$id_bannette,$link_to_bannette)."\" $java_comment >";
		}
		$retour_aff.= htmlentities($comment_public,ENT_QUOTES, $charset);
		if ($link_to_bannette) {
			$retour_aff.="</a>";
			$retour_aff .= $zoom_comment;
		}		
		$retour_aff.="\n</td><td align='center' valign='top'>";
		$retour_aff.= htmlentities($aff_date_last_envoi,ENT_QUOTES, $charset);
		$retour_aff.="\n</td><td align='center' valign='top'>";
		$retour_aff.= htmlentities($tableau_bannettes[$i]['nb_contenu'],ENT_QUOTES, $charset);
		$retour_aff.="\n</td><td align='center' valign='top'>";
		$retour_aff.= htmlentities($tableau_bannettes[$i]['periodicite'],ENT_QUOTES, $charset);
		$retour_aff.= "</td></tr>";
	}
	
	// fermeture du tableau
	$retour_aff .= "</table>
					<INPUT type='submit' class='bouton' value=\"";
	if ($priv_pub=="PUB") $retour_aff .= $msg[dsi_bannette_gerer_sauver];
	else $retour_aff .= $msg[dsi_bannette_gerer_supprimer];
	$retour_aff .= "\" />
					</form></div><!-- fin id='$htmldiv_id' class='$htmldiv_class' -->";
	return $retour_aff ; 
	
	}
	
	
// retourne un tableau des bannettes public accessible en page d'accueil
function tableau_bannette_accueil($id_bannette) {
	global $dbh, $msg ;
	global $id_empr ;
	global $opac_show_subscribed_bannettes;

	if ($id_bannette) $clause = " and num_bannette in ('$id_bannette') ";
	//R�cup�ration des infos des bannettes
	$requete="select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi from bannettes where bannette_opac_accueil=1 $clause order by date_last_envoi DESC ";

	$resultat=mysql_query($requete);
	while ($r=mysql_fetch_object($resultat)) {
		$abon = 0;
		if($id_empr && $opac_show_subscribed_bannettes){
			$query = "select count(1) from bannette_abon where num_bannette=".$r->id_bannette." and num_empr=".$id_empr;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$abon = mysql_result($result,0,0);
			}
		}
		if(!$abon){	
			$requete="select count(1) as compte from bannette_contenu where num_bannette='$r->id_bannette'";
			$resnb=mysql_query($requete);
			$nb=mysql_fetch_object($resnb) ;
			if ($nb->compte){
				$tableau_bannette[] = array (
					'id_bannette' => $r->id_bannette,
					'comment_public' => $r->comment_public,
					'aff_date_last_envoi' => $r->aff_date_last_envoi,
					'nb_contenu' => $nb->compte
				);
			}
		}
	}
	return $tableau_bannette;
}

function affiche_public_bannette($bannettes="", $aff_notices_nb=0, $mode_aff_notice=AFF_BAN_NOTICES_BOTH, $depliable=AFF_BAN_NOTICES_DEPLIABLES_OUI, $link_to_bannette="", $liens_opac=array(), $date_diff='', $htmldiv_id="bannette-container", $htmldiv_class="bannette-container", $htmldiv_zindex="" ) {
	global $dbh;
	global $msg,$charset;
	global $opac_notice_affichage_class;
	global $affiche_bannette_tpl;
	// r�cup�ration des bannettes
	global $id_empr ;
	
	if ($bannettes) $clause = " and id_bannette in ($bannettes) ";
	//R�cup�ration des infos des bannettes
	$requete="select distinct id_bannette,comment_public, date_format(date_last_envoi, '".$msg["format_date"]."') as aff_date_last_envoi from bannettes where proprio_bannette = 0 $clause order by date_last_envoi DESC ";
	$resultat=mysql_query($requete);
	$tableau_bannettes = array();
	while ($r=mysql_fetch_object($resultat)) {
		$requete="select count(1) as compte from bannette_contenu where num_bannette='$r->id_bannette'";
		$resnb=mysql_query($requete);
		$nb=mysql_fetch_object($resnb) ;
		if ($nb->compte)
			$tableau_bannettes[] = array (
				'id_bannette' => $r->id_bannette,
				'comment_public' => $r->comment_public,
				'aff_date_last_envoi' => $r->aff_date_last_envoi,
				'nb_contenu' => $nb->compte
				);
		}
	
	if (!sizeof($tableau_bannettes))
		return "" ;

	// pr�paration du div comme il faut
	$retour_aff = "<div id='$htmldiv_id' class='$htmldiv_class'";
	if ($htmldiv_zindex) $retour_aff .=" zindex='$htmldiv_zindex' ";
	$retour_aff .=" >";
	for ($i=0; $i<sizeof($tableau_bannettes); $i++ ) {
		$aff_banette="";
		$id_bannette=$tableau_bannettes[$i]['id_bannette'] ;
		$comment_public=$tableau_bannettes[$i]['comment_public'] ;
		$aff_date_last_envoi=$tableau_bannettes[$i]['aff_date_last_envoi'] ;
		$aff_banette.="\n<div class='bannette-titre'><h1>";
		$aff_banette.="<a href='cart_info.php?lvl=dsi&id=$id_bannette' target='cart_info'><img src='images/basket_small_20x20.gif' border='0' title=\"".$msg[notice_title_basket]."\" alt=\"".$msg[notice_title_basket]."\"></a>";
		if ($link_to_bannette) $aff_banette.="<a href=\"".str_replace("!!id_bannette!!",$id_bannette,$link_to_bannette)."\">";
		if($date_diff){
			$aff_banette.= htmlentities($comment_public." - ".formatdate($date_diff),ENT_QUOTES, $charset);
		}else{
			$aff_banette.= htmlentities($comment_public." - ".$aff_date_last_envoi,ENT_QUOTES, $charset);
		}
		if ($link_to_bannette) $aff_banette.="</a>";
		$aff_banette.= "</h1></div><hr/>";

		$notices = array();
		notices_bannette($id_bannette, $notices,$date_diff) ;

		if ($aff_notices_nb>0) $limite_notices = min($aff_notices_nb, count($notices)) ;
		elseif ($aff_notices_nb<0) $limite_notices = min($aff_notices_nb, count($notices)) ;
		else  $limite_notices = count($notices) ;
		reset ($notices) ;
		$limit=0;
		if ($limite_notices) $aff_banette.= "<div id='etagere-notice-list_!!id_bannette!!'>";
		while ((list($idnotice, $niveau_biblio)= each($notices)) && ($limit<$limite_notices)) {
			$limit++;
			$notice = new $opac_notice_affichage_class($idnotice, $liens_opac, 1) ;
			// si notice visible
			if ($notice->visu_notice) {
				$notice->do_header();
				switch ($mode_aff_notice) {
					case AFF_BAN_NOTICES_REDUIT :
						$aff_banette .= "<div class='etagere-titre-reduit'>".$notice->notice_header_with_link."</div>" ;
						break;
					case AFF_BAN_NOTICES_ISBD :
						$notice->do_isbd();
						$notice->genere_simple($depliable, 'ISBD') ;
						$aff_banette .= $notice->result ;
						break;
					case AFF_BAN_NOTICES_PUBLIC :
						$notice->do_public();
						$notice->genere_simple($depliable, 'PUBLIC') ;
						$aff_banette .= $notice->result ;
						break;
					case AFF_BAN_NOTICES_BOTH :
						$notice->do_isbd();
						$notice->do_public();
						$notice->genere_double($depliable, 'PUBLIC') ;
						$aff_banette .= $notice->result ;
						break ;
					default:
						$notice->do_isbd();
						$notice->do_public();
						$notice->genere_double($depliable, 'autre') ;
						$aff_banette .= $notice->result ;
						break ;
				}
			}
		}
		if ($limite_notices&&($limite_notices<count($notices))) $aff_banette.= "<br />...";
		if ($limite_notices) $aff_banette.= "</div>";

		$req="select distinct date_diff_arc from dsi_archive where num_banette_arc='".$id_bannette."' order by date_diff_arc desc";
		$res_arc=mysql_query($req, $dbh);
		$first=0;
		$diff_list="";
		while (($r = mysql_fetch_object($res_arc))){
			if(!$first)$libelle=$msg["dsi_archive_last"];
			else $libelle=sprintf($msg["dsi_archive_other"], formatdate($r->date_diff_arc));
				
			if($pair_impair == 'even')$pair_impair='odd'; else $pair_impair='even';
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='$link_to_bannette&date_diff=".$r->date_diff_arc."';\" ";
			$diff= "<tr style='cursor: pointer' class='$pair_impair' $tr_javascript><td>".$libelle ."</td></tr>";
			$first=1;
				
			$diff_list.=$diff;
		}
		$tpl=$affiche_bannette_tpl;
		$tpl = str_replace ("!!historique!!",$diff_list,$tpl) ;
		$tpl = str_replace ("!!diffusion!!",$aff_banette,$tpl) ;
		$tpl=str_replace("!!id_bannette!!",$id_bannette,$tpl);
		$retour_aff.=$tpl;
	}

	// fermeture du DIV
	$retour_aff .= "</div><!-- fin id='$htmldiv_id' class='$htmldiv_class' -->";

	return $retour_aff ;

}