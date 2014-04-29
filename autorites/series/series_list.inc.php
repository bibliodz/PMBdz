<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: series_list.inc.php,v 1.26 2013-11-27 17:19:56 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// nombre de r�f�rences par pages
if ($nb_per_page_serie != "") 
	$nb_per_page = $nb_per_page_serie ;
	else $nb_per_page = 10;

// traitement de la saisie utilisateur
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once($class_path."/analyse_query.class.php");

if($user_input)
	//a priori pas utile. Armelle
	$clef = reg_diacrit($user_input);

// $serie_list_tmpl : template pour la liste titres de s�ries
$serie_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >$msg[334] !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>
";

function list_serie($cb, $empr_list, $nav_bar) {
	global $serie_list_tmpl;
	$serie_list_tmpl = str_replace("!!cle!!", $cb, $serie_list_tmpl);
	$serie_list_tmpl = str_replace("!!list!!", $empr_list, $serie_list_tmpl);
	$serie_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $serie_list_tmpl);
	serie::search_form();
	print pmb_bidi($serie_list_tmpl);
}


// on r�cup�re le nombre de lignes qui vont bien
if(!$nbr_lignes) {
	if(!$user_input) {
		$requete = "SELECT count(1) FROM series ";
		if ($last_param) 
			$requete = "SELECT count(1) FROM series ".$tri_param." ".$limit_param;
	} else {
		$aq=new analyse_query(stripslashes($user_input));
		if ($aq->error) {
			serie::search_form();
			error_message($msg["searcher_syntax_error"],sprintf($msg["searcher_syntax_error_desc"],$aq->current_car,$aq->input_html,$aq->error_message));
			exit;
		}
		$requete=$aq->get_query_count("series","serie_name","serie_index","serie_id");
	}
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = mysql_result($res, 0, 0);
} else $aq=new analyse_query(stripslashes($user_input));


if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;


if($nbr_lignes) {
	$serie_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$serie_list_tmpl);
	
	$serie_list = "<tr>
		<th>".$msg[103]."</th>
		<!--!!col_num_autorite!!-->
		<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$num_auth_present=false;
	$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='serie' AND TRIM(authority_number) !='' LIMIT 1";
	$res_aut=mysql_query($req,$dbh);
	if($res_aut && mysql_num_rows($res_aut)){
		$serie_list=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$serie_list);
		$num_auth_present=true;
	}
	
	// on lance la vraie requ�te
	if(!$user_input) {
		$requete = "SELECT * FROM series ORDER BY serie_name LIMIT $debut,$nb_per_page ";
		if ($last_param) $requete = "SELECT * FROM series ".$tri_param." ".$limit_param;
		} else {
			$members=$aq->get_query_members("series","serie_name","serie_index","serie_id");
			$requete="select *,".$members["select"]." as pert from series where ".$members["where"]." group by serie_id order by pert desc, serie_index limit $debut,$nb_per_page";
			}
	$res = @mysql_query($requete, $dbh);
	$parity=1;
	$url_base = "$PHP_SELF?categ=series&sub=reach&user_input=".rawurlencode(stripslashes($user_input)) ;
	while(($serie=mysql_fetch_object($res))) {
		if ($parity % 2) {
			$pair_impair = "even";
			} else {
				$pair_impair = "odd";
				}
		$parity += 1;
		
		$notice_count_sql = "SELECT count(*) FROM notices WHERE tparent_id = ".$serie->serie_id;
		$notice_count = mysql_result(mysql_query($notice_count_sql), 0, 0);
		
	    $tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
        $serie_list.= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>
                	  <td valign='top' onmousedown=\"document.location='./autorites.php?categ=series&sub=serie_form&id=$serie->serie_id&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page';\">
					  $serie->serie_name
					  </td>";
		
		//Num�ros d'autorite
		if($num_auth_present){
			$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='serie' AND num_authority='".$serie->serie_id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
			$res_aut=mysql_query($requete,$dbh);
			if($res_aut && mysql_num_rows($res_aut)){
				$serie_list .= "<td>";
				$first=true;
				while ($aut = mysql_fetch_object($res_aut)) {
					if(!$first)$serie_list .=", ";
					$serie_list .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
					if($tmp=trim($aut->origin_authorities_name)){
						$serie_list .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
					}
					$first=false;
				}
				$serie_list .= "</td>";
			}else{
				$serie_list .= "<td>&nbsp;</td>";
			}
		}
		
		if($notice_count && $notice_count!=0)
			$serie_list.= "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=10&etat=aut_search&aut_type=tit_serie&aut_id=$serie->serie_id'\">".$notice_count."</td>";
		else $serie_list.= "<td>&nbsp;</td>";
		$serie_list.= "</tr>";
	}
	mysql_free_result($res);
	
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
        else $nav_bar = "";	
        
	// affichage du r�sultat
	list_serie($user_input, $serie_list, $nav_bar);

} else {
	// la requ�te n'a produit aucun r�sultat
	serie::search_form();
	error_message($msg[152], str_replace('!!titre_cle!!', stripslashes($user_input), $msg[335]), 0, './autorites.php?categ=series&sub=&id=');
}
