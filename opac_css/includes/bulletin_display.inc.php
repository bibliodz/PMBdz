<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bulletin_display.inc.php,v 1.51 2014-03-13 16:38:12 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// error_reporting (E_ALL);             
// largeur du tableau notice en pixels
$libelle = $msg[270];
$largeur = 500;		

require_once($base_path.'/includes/explnum.inc.php');
require_once($base_path.'/classes/notice_affichage.class.php');
require_once($include_path."/resa_func.inc.php"); 
require_once($base_path.'/classes/notice.class.php');
require_once($class_path."/acces.class.php");

/*
 * gestion des droits...
 * Dans l'ordre :
 * 	on regarde les droits sur le p�rio,
 * ensuite la notice de bulletin (pour les expl et explnum)...
 */

if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
	$ac= new acces();
	$dom_2= $ac->setDomain(2);
}

$requete = "SELECT bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, bulletin_titre, bulletin_cb, date_format(date_date, '".$msg["format_date_sql"]."') as aff_date_date,num_notice FROM bulletins WHERE bulletin_id='$id'";

$resdep = @mysql_query($requete, $dbh);
while(($obj=mysql_fetch_array($resdep))) {
	//on regarde tout de suite les droits de la notice de p�riodique...
	$perio_id = $obj["bulletin_notice"];
	//droits d'acces emprunteur/notice
	$acces_v=TRUE;
	if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
		$acces_v = $dom_2->getRights($_SESSION['id_empr_session'],$perio_id,4);
	} else {
		$requete = "SELECT notice_visible_opac, expl_visible_opac, notice_visible_opac_abon, expl_visible_opac_abon, explnum_visible_opac, explnum_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$perio_id."' and id_notice_statut=statut ";
		$myQuery = mysql_query($requete, $dbh);
		if(mysql_num_rows($myQuery)) {
			$statut_temp = mysql_fetch_object($myQuery);
			if(!$statut_temp->notice_visible_opac)	$acces_v=FALSE;
			if($statut_temp->notice_visible_opac_abon && !$_SESSION['id_empr_session'])	$acces_v=FALSE;
		} else 	$acces_v=FALSE;
	}
	
	if($id && $acces_v) {
		//on peut voir les bulletins de ce p�riodique...
		//on regarde si on a vraiment de voir ce bulletin en particulier (si on a une notice de bulletin)
		if($obj['num_notice']){
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
				$acces_v = $dom_2->getRights($_SESSION['id_empr_session'],$obj['num_notice'],4);
			} else {
				$requete = "SELECT notice_visible_opac, notice_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$obj['num_notice']."' and id_notice_statut=statut ";
				$myQuery = mysql_query($requete, $dbh);
				if(mysql_num_rows($myQuery)) {
					$statut_temp = mysql_fetch_object($myQuery);
					if(!$statut_temp->notice_visible_opac) $acces_v=FALSE;
					if($statut_temp->notice_visible_opac_abon && !$_SESSION['id_empr_session'])	$acces_v=FALSE;
				} else 	$acces_v=FALSE;
			}	
		}
		if($acces_v){
			//on est maintenant sur que ce bulletin est visible !
			
			$id_for_right = $perio_id;
			if($obj['num_notice']){
				$id_for_right = $obj['num_notice'];
			}
			
			//est-ce que je peux voir les exemplaires
			$expl_visible = true;
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
				$expl_visible = $dom_2->getRights($_SESSION['id_empr_session'],$id_for_right,8);
			} else {
				$requete = "SELECT expl_visible_opac, expl_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$id_for_right."' and id_notice_statut=statut ";
				$myQuery = mysql_query($requete, $dbh);
				if(mysql_num_rows($myQuery)) {
					$statut_temp = mysql_fetch_object($myQuery);
					if(!$statut_temp->expl_visible_opac)	$expl_visible=false;
					if($statut_temp->expl_visible_opac_abon && !$_SESSION['id_empr_session'])	$expl_visible=false;
				} else 	$expl_visible=false;
			}
			
			//est-ce que je peux voir les documents num�riques
			$docnum_visible = true;
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
				$docnum_visible = $dom_2->getRights($_SESSION['id_empr_session'],$id_for_right,16);
			} else {
				$requete = "SELECT explnum_visible_opac, explnum_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$id_for_right."' and id_notice_statut=statut ";
				$myQuery = mysql_query($requete, $dbh);
				if(mysql_num_rows($myQuery)) {
					$statut_temp = mysql_fetch_object($myQuery);
					if(!$statut_temp->explnum_visible_opac)	$docnum_visible=false;
					if($statut_temp->explnum_visible_opac_abon && !$_SESSION['id_empr_session'])	$docnum_visible=false;
				} else 	$docnum_visible=false;
			}
			
			$nb_docnum = 0;
			$res_print_docnum=$info_bulle="";
			if($docnum_visible || $opac_show_links_invisible_docnums){
				//on cherches des documents num�riques
				$req = "select explnum_id, explnum_nom, explnum_nomfichier, explnum_url from explnum where explnum_bulletin = ".$obj["bulletin_id"];
				$resultat = mysql_query($req, $dbh) or die ($req." ".mysql_error());
				$nb_docnum = mysql_num_rows($resultat);
				//on met le n�cessaire pour la visionneuse
				if($opac_visionneuse_allow && $nb_docnum){
					//print "&nbsp;&nbsp;&nbsp;".$link_to_visionneuse;
					print "
					<script type='text/javascript'>
						function sendToVisionneuse(explnum_id){
							document.getElementById('visionneuseIframe').src = 'visionneuse.php?mode=perio_bulletin&idperio=".$obj['bulletin_notice']."'+(typeof(explnum_id) != 'undefined' ? '&explnum_id='+explnum_id+\"\" : '\'');
						}
					</script>";
				}
				if($nb_docnum == 1){
					$explnumrow = mysql_fetch_object($resultat);
					if ($explnumrow->explnum_nomfichier){
						if($explnumrow->explnum_nom == $explnumrow->explnum_nomfichier)	$info_bulle=$msg["open_doc_num_notice"].$explnumrow->explnum_nomfichier;
						else $info_bulle=$explnumrow->explnum_nom;
					}elseif ($explnumrow->explnum_url){
						if($explnumrow->explnum_nom == $explnumrow->explnum_url)	$info_bulle=$msg["open_link_url_notice"].$explnumrow->explnum_url;
						else $info_bulle=$explnumrow->explnum_nom;
					}
					
					if($opac_visionneuse_allow){
						$res_print_docnum="&nbsp;<a href='#' onclick=\"open_visionneuse(sendToVisionneuse,".$explnumrow->explnum_id.");return false;\" alt='' title=''>";
					}else{
						$res_print_docnum= "&nbsp;<a href=\"./doc_num.php?explnum_id=".$explnumrow->explnum_id."\" target=\"__LINK__\">";
					}
					$res_print_docnum .= "<img src=\"./images/globe_orange.png\" ";
					$res_print_docnum .= " alt=\"";
					$res_print_docnum .= htmlentities($info_bulle,ENT_QUOTES,$charset);
					$res_print_docnum .= "\" title=\"";
					$res_print_docnum .= htmlentities($info_bulle,ENT_QUOTES,$charset);
					$res_print_docnum .= "\"/>";
					$res_print_docnum .= "</a>";
				}elseif($nb_docnum > 1){
					$info_bulle=$msg["info_docs_num_notice"];
					$res_print_docnum = "&nbsp;<a href='#docnum'><img src=\"./images/globe_rouge.png\" alt=\"".htmlentities($info_bulle,ENT_QUOTES,$charset)."\" \" title=\"".htmlentities($info_bulle,ENT_QUOTES,$charset)."\"/></a>";
				}
			}
			
			$typdocchapeau="a";
			$icon="";
			$requete3 = "SELECT notice_id,typdoc FROM notices WHERE notice_id='".$perio_id."' ";
			$res3 = @mysql_query($requete3, $dbh);
			while(($obj3=mysql_fetch_object($res3))) {
				$notice3 = new notice($obj3->notice_id);
				$typdocchapeau=$obj3->typdoc;
			}
			$notice3->fetch_visibilite();
			if (!$icon) $icon="icon_per.gif";
			$icon = $icon_doc["b".$typdocchapeau];
			
			//carrousel pour la navigation
			if($opac_show_bulletin_nav)
				$res_print = do_carroussel($obj);
			else $res_print="";
			
			//$res_print .= "<h3><img src=./images/$icon /> ".$notice3->print_resume(1,$css)."."." <b>".$obj["bulletin_numero"]."</b>".($nb_docnum ? "&nbsp;<a href='#docnum'>".($nb_docnum > 1 ? "<img src='./images/globe_rouge.png' />" : "<img src='./images/globe_orange.png' />")."</a>" : "")."</h3>\n";
			$res_print .= "<h3><img src=./images/$icon /> ".$notice3->print_resume(1,$css)."."." <b>".$obj["bulletin_numero"]."</b>";
			if($res_print_docnum){
				$res_print .=$res_print_docnum."</h3>\n";
			}else{
				$res_print .="</h3>\n";
			}
			
			$num_notice=$obj['num_notice'];
			if ($obj['bulletin_titre']) {
				$res_print .=  htmlentities($obj['bulletin_titre'],ENT_QUOTES, $charset)."<br />";
			} 
			if ($obj['mention_date']) $res_print .= $msg['bull_mention_date']." &nbsp;".$obj['mention_date']."\n"; 
			if ($obj['date_date']) $res_print .= "<br />".$msg['bull_date_date']." &nbsp;".$obj['aff_date_date']." \n";     
			if ($obj['bulletin_cb']) {
				$res_print .= "<br />".$msg["code_start"]." ".htmlentities($obj['bulletin_cb'],ENT_QUOTES, $charset)."\n";
				$code_cb_bulletin = $obj['bulletin_cb'];
			} 
		
			do_image($res_print, $code_cb_bulletin, 0 ) ;
			if ($num_notice) {
				
				// Il y a une notice de bulletin
				print $res_print ;	
				$opac_notices_depliable = 0;
				$seule=1;
				print pmb_bidi(aff_notice($num_notice,0,0)) ;	
			} else {
				// construction des d�pouillements
				$depouill= "<br /><h3>".$msg['bull_dep']."</h3>";
				$requete = "SELECT * FROM analysis, notices, notice_statut WHERE analysis_bulletin='$id' AND notice_id = analysis_notice AND statut = id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").") order by analysis_notice"; 
				$res = @mysql_query($requete, $dbh);
				if (mysql_num_rows($res)) {
					if ($opac_notices_depliable) $depouill .= $begin_result_liste;
					if ($opac_cart_allow) $depouill.="<a href=\"cart_info.php?id=".$id."&lvl=analysis&header=".rawurlencode(strip_tags($notice_header))."\" target=\"cart_info\" class=\"img_basket\">".$msg["cart_add_result_in"]."</a>"; 		
					$depouill.= "<blockquote>";
					while(($obj=mysql_fetch_array($res))) {
						$depouill.= pmb_bidi(aff_notice($obj["analysis_notice"]));
					}
					$depouill.= "</blockquote>";
				} else $depouill = $msg["no_analysis"];
				mysql_free_result($res);
				print $res_print ;	
				print $depouill ;
				
				if ($expl_visible)	{	
					if (!$opac_resa_planning) {
						$resa_check=check_statut(0,$id) ;
						if ($resa_check) {
							$requete_resa = "SELECT count(1) FROM resa WHERE resa_idbulletin='$id'";
							$nb_resa_encours = mysql_result(mysql_query($requete_resa,$dbh), 0, 0) ;
							if ($nb_resa_encours) $message_nbresa = str_replace("!!nbresa!!", $nb_resa_encours, $msg["resa_nb_deja_resa"]) ;
						
							if (($_SESSION["user_code"] && $allow_book) && $opac_resa && !$popup_resa) {
								$ret_resa .= "<h3>".$msg["bulletin_display_resa"]."</h3>";
								if ($opac_max_resa==0 || $opac_max_resa>$nb_resa_encours) {
									if ($opac_resa_popup) $ret_resa .= "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
									else $ret_resa .= "<a href='./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
									$ret_resa .= $message_nbresa ;
								} else $ret_resa .= str_replace("!!nb_max_resa!!", $opac_max_resa, $msg["resa_nb_max_resa"]) ; 
								$ret_resa.= "<br />";
							} elseif (!($_SESSION["user_code"]) && $opac_resa && !$popup_resa) {
								// utilisateur pas connect�
								// pr�paration lien r�servation sans �tre connect�
								$ret_resa .= "<h3>".$msg["bulletin_display_resa"]."</h3>";
								if ($opac_resa_popup) $ret_resa .= "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
								else $ret_resa .= "<a href='./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
								$ret_resa .= $message_nbresa ;
								$ret_resa .= "<br />";
							} elseif ($fonction=='notice_affichage_custom_bretagne') {
								if ($opac_resa_popup) $reserver = "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_notice=".$this->notice_id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
								else $reserver = "<a href='./do_resa.php?lvl=resa&id_notice=".$this->notice_id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
								$reservernbre = $message_nbresa ;
							} else $ret_resa = ""; 
							print pmb_bidi($ret_resa) ;
						}
					}
					if ($opac_show_exemplaires) {
						if($fonction=='notice_affichage_custom_bretagne')	print pmb_bidi(notice_affichage_custom_bretagne::expl_list("m",0,$id));
						else print pmb_bidi(notice_affichage::expl_list("m",0,$id));
					}
				}
				
				if ($docnum_visible || $opac_show_links_invisible_docnums) {
					if (($explnum = show_explnum_per_notice(0, $id, ''))) print pmb_bidi("<a name='docnum'><h3>".$msg["explnum"]."</h3></a>".$explnum);
				}	
			}
			print notice_affichage::autres_lectures (0,$id);
		}
	}
}
mysql_free_result($resdep);

function do_carroussel($bull){
	global $gestion_acces_active,$gestion_acces_empr_notice;
	global $msg;
	if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
		$ac = new acces();
		$dom_2= $ac->setDomain(2);
		$join_noti = $dom_2->getJoin($_SESSION["id_empr_session"],4,"bulletins.num_notice");
		$join_bull = $dom_2->getJoin($_SESSION["id_empr_session"],4,"bulletins.bulletin_notice");
	}else{
		$join_noti = "join notices on bulletins.num_notice = notices.notice_id join notice_statut on notices.statut = notice_statut.id_notice_statut AND ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
		$join_bull = "join notices on bulletins.bulletin_notice = notices.notice_id join notice_statut on notices.statut = notice_statut.id_notice_statut AND ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
	}
	
	$requete_noti = "select bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, bulletin_titre, bulletin_cb, date_format(date_date, '".$msg["format_date_sql"]."') as aff_date_date,num_notice from bulletins ".$join_noti." where bulletins.num_notice != 0 and bulletin_notice = ".$bull['bulletin_notice']."";
	$requete_bull = "select bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, bulletin_titre, bulletin_cb, date_format(date_date, '".$msg["format_date_sql"]."') as aff_date_date,num_notice from bulletins ".$join_bull." where bulletins.num_notice  = 0 and bulletin_notice = ".$bull['bulletin_notice']."";
	
	$requete = "select * from (".$requete_noti." union ".$requete_bull.") as uni";
	
	
	$requete = "(".$requete." where date_date < '".$bull['date_date']."' order by date_date desc limit 0,3) union (".$requete." where date_date >= '".$bull['date_date']."' order by date_date asc limit 0,4)";

	$res_caroussel = mysql_query($requete);
	if(mysql_num_rows($res_caroussel)){
		$prev = true;
		$current = $previous = $next = array();
		while (($bullForNav=mysql_fetch_array($res_caroussel))) {
			if($bullForNav['bulletin_id'] == $bull['bulletin_id']){
				$prev = false;
				$current = $bullForNav;
			}else{
				if($prev == true){
					$previous[] = $bullForNav;	
				}else{
					$next[] = $bullForNav;
				}
			}
		}
		$carroussel = "
			<table class='carroussel_bulletin' style=''>
				<tr>";
				
		$taille = 100;
		//on a des bulletins pr�c�dent
		if (sizeof($previous)>0){
			$taille =$taille - 4;
		}
		//on a des bulletins suivant
		if(sizeof($next)>0){
			$taille =$taille - 4;
		}
					
		//ceux d'avant
		//on �galise  : 3 de chaque cot�
		if(sizeof($previous)>0)$carroussel .= "<td style='width:4%;'><a href='index.php?lvl=bulletin_display&id=".$previous[0]['bulletin_id']."'><img align='middle' src='images/previous1.png'/></a></td>";
		for($i=0 ; $i<(3-sizeof($previous)); $i++){
			if($i<(3-sizeof($previous))-1)
				$carroussel .="<td style='width:".($taille/((3*2)+1))."%;'>&nbsp;</td>";
			else{
				if(!$link_perio=get_perio_link($bull['bulletin_notice'],'before'))$carroussel .="<td style='width:".($taille/((3*2)+1))."%;'>&nbsp;</td>";
				else $carroussel .="<td class='active' style='width:".($taille/((3*2)+1))."%;'>$link_perio</td>";	
			} 
		}
		
		if(sizeof($previous)>0){
			for($i=sizeof($previous)-1 ; $i>=0 ; $i--){
				$carroussel .="<td class='active' style='width:".($taille/((3*2)+1))."%;'><a href='index.php?lvl=bulletin_display&id=".$previous[$i]['bulletin_id']."'>".$previous[$i]['bulletin_numero'].($previous[$i]['bulletin_titre'] ? " - ".$previous[$i]['bulletin_titre'] : "")."<br />".($previous[$i]['mention_date'] ? $previous[$i]['mention_date'] :$previous[$i]['aff_date_date'] )."</a></td>";
			}
		}
		//le bull courant en �vidence
		$carroussel .="<td class='current_bull_carroussel' style='width:".($taille/((3*2)+1))."%;'><a href='index.php?lvl=bulletin_display&id=".$current['bulletin_id']."'>".$current['bulletin_numero'].($current['bulletin_titre'] ? " - ".$current['bulletin_titre'] : "")."<br />".($current['mention_date'] ? $current['mention_date'] :$current['aff_date_date'] )."</a></td>";
		//la suite
		if(sizeof($next)>0){
			for($i=0 ; $i<sizeof($next) ; $i++){
				$carroussel .="<td class='active' style='width:".($taille/((3*2)+1))."%;'><a href='index.php?lvl=bulletin_display&id=".$next[$i]['bulletin_id']."'>".$next[$i]['bulletin_numero'].($next[$i]['bulletin_titre'] ? " - ".$next[$i]['bulletin_titre'] : "")."<br />".($next[$i]['mention_date'] ? $next[$i]['mention_date'] :$next[$i]['aff_date_date'] )."</a></td>";
			}
		}
		//on �galise  : 3 de chaque cot�
		for($i=0 ; $i<(3-sizeof($next)) ; $i++){
			if($i){
				$carroussel .="<td style='width:".($taille/((3*2)+1))."%;'>&nbsp;</td>";
			}else{
				if(!$link_perio=get_perio_link($bull['bulletin_notice'],'after'))	$carroussel .="<td style='width:".($taille/((3*2)+1))."%;'>&nbsp;</td>";
				else $carroussel .="<td class='active' style='width:".($taille/((3*2)+1))."%;'>$link_perio</td>";	
			}
		}
		if(sizeof($next)>0)$carroussel .= "<td style='width:4%;'><a href='index.php?lvl=bulletin_display&id=".$next[0]['bulletin_id']."'><img align='middle' src='images/next1.png'/></a></td>";
		//on ferme le tout
			$carroussel .= "
				</tr>
			</table>";
	}
	
	return $carroussel;
}

// Construction des tritres suivants et pr�c�dants
function get_perio_link($id,$sens) {
	global $dbh,$lang,$include_path;
	global $msg;
	global $charset;
	global $relation_listup;
	global $opac_notice_affichage_class;
	
	//Recherche des notices parentes
	$requete="select linked_notice, relation_type, rank from notices_relations join notices on notice_id=linked_notice 
	where num_notice=".$id." 
	order by rank ";	
	$result_linked=mysql_query($requete,$dbh);
	if (mysql_num_rows($result_linked)) {
		$parser = new XMLlist_perio("$include_path/marc_tables/$lang/relationtypeup.xml",1,1);
		$parser->analyser();
		$listup= $parser->table_serial;			
		//Pour toutes les notices li�es
		$link=""; 
		while(($r_rel=mysql_fetch_object($result_linked))) {
			if($listup[$r_rel->relation_type]==$sens){
				$current = new $opac_notice_affichage_class($r_rel->linked_notice,"",0,0,1);
				$current->do_header_without_html();
				$link="<a href='index.php?lvl=notice_display&id=".$r_rel->linked_notice."'> ".$current->notice_header_without_html."</a>";
				return $link;
			}
		}
	}
	
	//Recherche des notices filles
	$requete="select num_notice, relation_type, rank from notices_relations join notices on notice_id=linked_notice
	where linked_notice=".$id."
	order by rank ";	
	$result_linked=mysql_query($requete,$dbh);
	if (!mysql_num_rows($result_linked)) {
		return "";
	}	
	$parser = new XMLlist_perio("$include_path/marc_tables/$lang/relationtypedown.xml",1,1);
	$parser->analyser();
	$listdown= $parser->table_serial;
	//Pour toutes les notices li�es
	$link="";
	while(($r_rel=mysql_fetch_object($result_linked))) {
		if($listdown[$r_rel->relation_type]==$sens){
			$current = new $opac_notice_affichage_class($r_rel->num_notice,"",0,0,1);
			$current->do_header_without_html();
			$link="<a href='index.php?lvl=notice_display&id=".$r_rel->num_notice."'> ".$current->notice_header_without_html."</a>";
			return $link;
		}
	}
	return "";	
}


class XMLlist_perio extends XMLlist {
	var $current_serial;
	var $table_serial;

	function debutBalise($parser, $nom, $attributs) {
		global $_starttag; $_starttag=true;

		if($nom == 'ENTRY' && $attributs['CODE']){
			$this->current = $attributs['CODE'];			
			if($attributs['SERIAL'])	$this->current_serial = $attributs['SERIAL'];
		}
		if($nom == 'ENTRY' && $attributs['ORDER']) {
			$this->flag_order = true;
			$this->order[$attributs['CODE']] =  $attributs['ORDER'];
		}
		if($nom == 'XMLlist') {
			$this->table = array();
			$this->table_serial = array();
			$this->fav = array();
		}
	}

	
	function texte($parser, $data) {
		global $_starttag;
		if($this->current){
			if ($_starttag) {
				$this->table[$this->current] = $data;
				$_starttag=false;
			} else $this->table[$this->current] .= $data;
			$this->table_serial[$this->current] = $this->current_serial;
		}	
	}
	
}
