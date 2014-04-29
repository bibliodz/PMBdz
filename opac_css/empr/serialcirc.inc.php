<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc.inc.php,v 1.6 2012-02-01 10:15:51 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/serialcirc_empr.class.php");
require_once($class_path."/serialcirc_diff.class.php");
require_once($include_path."/serialcirc.inc.php");


$serialcirc_empr = new serialcirc_empr();
switch ($lvl){
	case "add_resa" :
		print "<h3><span>".htmlentities($msg["serialcirc_add_resa"],ENT_QUOTES,$charset)."</span></h3>";
		if($expl_to_hold != ""){
			if(!$serialcirc_empr->hold_expl($expl_to_hold)){
				print $serialcirc_empr->get_holding_form();	
			}
		}else{
			print $serialcirc_empr->get_holding_form();
		}
		break;	
	case "point" :
		print "<h3><span>".htmlentities($msg["serialcirc_checkpoint"],ENT_QUOTES,$charset)."</span></h3>";
		if($expl_to_point != ""){
			if(!$serialcirc_empr->point_expl($expl_to_point)){
				print $serialcirc_empr->get_point_form();	
			}else{
				print htmlentities($msg['serialcirc_expl_pointed'],ENT_QUOTES,$charset);
				print "<br /><a href='empr.php?tab=serialcirc&lvl=list_abo'>".htmlentities($msg['serialcirc_point_back_to_list'],ENT_QUOTES,$charset)."</a>";
			}
		}else{
			print $serialcirc_empr->get_point_form();
		}
		break;
	case "list_virtual_abo" :
		$virtual_serialcirc = serialcirc_empr::get_virtual_abo();
		print "<h3><span>".htmlentities($msg['serialcirc_list_asked_abo']."(".$nb_virtual.")",ENT_QUOTES,$charset)."</span></h3>";
		for($i=0 ; $i<count($virtual_serialcirc) ; $i++){
			if($action == "ask_copy"){
				if($expl_id == $virtual_serialcirc[$i]->num_expl){
					print $virtual_serialcirc[$i]->show_ask_form();
				}
			}
			print $virtual_serialcirc[$i]->show_issue_display($expl_id);
		}
		break;
	case "copy" :
		print "<h3><span>".htmlentities($msg["serialcirc_ask_copy"],ENT_QUOTES,$charset)."</span></h3>";
		if($action == "ask_copy"){
			if($bulletin_id != 0){
				$serialcirc_empr->ask_copy($bulletin_id,$serialcirc_ask_copy_analysis,$serialcirc_ask_comment);
			}else if($expl_cb){			
				print $serialcirc_empr->show_ask_form($expl_cb);
			}
		}
		print $serialcirc_empr->resume_ask_copy();
		break;
	case "ask" :
		if($action == "subscribe"){
			$serialcirc_empr->ask_subscription($serial_id);
		}
		print $serialcirc_empr->resume_ask();
		break;
	case "list_abo" :
	default :
		print "<h3><span>".htmlentities($msg["serialcirc_list_abo"],ENT_QUOTES,$charset)."</span></h3>";
		//si une action vient d'�tre faites...
		if($action == "unsubscribe"){
			$serialcirc_empr->unsubscribe($unsubscribe_list);
		}else if($actions_form_submit == 1){
			$serialcirc_empr->process_actions($id_serialcirc,$expl_id,$subscription,$ask_transmission,$report_late,$trans_accepted,$trans_doc_accepted,$ret_accepted);
		}
		$serialcirc_empr->get_my_circ_list();
		print $serialcirc_empr->get_tab_circ_list();
		break;	
}
