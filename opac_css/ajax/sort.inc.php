<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sort.inc.php,v 1.1 2014-01-17 09:02:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/sort.class.php");

include($include_path."/templates/sort.tpl.php");

switch($sub){
	case 'get_sort':
		$display = '';
		$sort = new sort('notices','session');
		
		//Si vidage historique des tris demand� ?
		if ($raz_sort && suppr_ids) {
			$suppr_sort_ids = array();
			$suppr_sort_ids = explode(",",$suppr_ids);
			if (count($suppr_sort_ids)) {
				$sort->supprimer($suppr_sort_ids);
			}
		}
		
		if (isset($_GET['page_en_cours'])) {
			$page_en_cours=$_GET['page_en_cours'];
		}
		
		if (isset($_GET['modif_sort'])) {
			$temp=array();
			for ($i=0;$i<=4;$i++) {
				if ($_POST['liste_critere'.$i]!="") {
					$temp[$i]=$_POST['croit_decroit'.$i]."_".$_POST['num_text'.$i]."_".$_POST['liste_critere'.$i];	
				}	
			}

			if (count($temp)!=0) {
				$display .= $sort->sauvegarder('','',$temp);
				if (substr($display,0,8)=="<script>") {
					$tmpStr = $sort->show_tris_form();
					$tmpStr = str_replace("<!--bouton close-->","<a href='#' onClick='parent.kill_frame_expl();return false;'><img src='" . $base_path . "/images/close.gif' border='0' align='right'></a></div>",$tmpStr);
			    	$tmpStr = str_replace("!!page_en_cours!!",urlencode($page_en_cours),$tmpStr);
			    	$tmpStr = str_replace("!!page_en_cours1!!",$page_en_cours,$tmpStr);
			    	$tmpStr = str_replace("!!action_suppr_tris!!", "get_sort_content(1, sortSupprIds('cases_a_cocher','cases_suppr'));", $tmpStr);
					$display .= $tmpStr;
					$tmpStr = $sort->show_sel_form();
		    		$tmpStr = str_replace("!!page_en_cours!!",urlencode($page_en_cours),$tmpStr);
					$tmpStr = str_replace("!!page_en_cours1!!",$page_en_cours,$tmpStr);
					$display .= $tmpStr;
				} else {
					$temp_tri=$_SESSION["nb_sortnotices"]-1;
					$display .= "<script> document.location='./index.php?".$page_en_cours."&get_last_query=".$_SESSION["last_query"]."&sort=".$temp_tri."';</script>";	
				}	
			} else {
				$display .= "<script> document.location='./index.php?".$page_en_cours."&get_last_query=".$_SESSION["last_query"]."';</script>";	
			}
		} else {
			$tmpStr = $sort->show_tris_form();
			$tmpStr = str_replace("<!--bouton close-->","<a href='#' onClick='parent.kill_sort_frame();return false;'><img src='" . $base_path . "/images/close.gif' border='0' align='right'></a></div>",$tmpStr);
			$tmpStr=str_replace("!!page_en_cours!!",urlencode($page_en_cours),$tmpStr);
			$tmpStr=str_replace("!!page_en_cours1!!",$page_en_cours,$tmpStr);
			$tmpStr = str_replace("!!action_suppr_tris!!", "get_sort_content(1, sortSupprIds('cases_a_cocher','cases_suppr'));", $tmpStr);
			$display .= $tmpStr;
			$tmpStr = $sort->show_sel_form();
			$tmpStr = str_replace("!!page_en_cours!!",urlencode($page_en_cours),$tmpStr);
			$tmpStr = str_replace("!!page_en_cours1!!",$page_en_cours,$tmpStr);
			$display .= $tmpStr;
		}
		break;
}

ajax_http_send_response($display);
return;

?>