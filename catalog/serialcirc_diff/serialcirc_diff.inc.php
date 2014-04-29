<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_diff.inc.php,v 1.6 2013-09-24 13:31:10 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/serialcirc_diff.class.php");

$serialcirc_diff=new serialcirc_diff($id_serialcirc,$num_abt);

switch($sub){		
	case 'option_form':
		if($action=='save'){		
			$data['circ_type']=$circ_type; // rotative ou �toile
			$data['virtual_circ']=$virtual_circ; //  virtuelle
			$data['no_ret_circ']=$no_ret_circ; 
			$data['duration']=$duration;
			$data['checked']=$checked;
			$data['retard_mode']=$retard_mode;
			$data['allow_resa']=$allow_resa;
			$data['allow_copy']=$allow_copy;
			$data['allow_send_ask']=$allow_send_ask;
			$data['duration_before_send']=$duration_before_send;
			$data['allow_subscription']=$allow_subscription;
			$data['expl_statut_circ']=$expl_statut_circ;
			$data['expl_statut_circ_after']=$expl_statut_circ_after;
			$serialcirc_diff->option_save($data); 	
			print $serialcirc_diff->show_form();
		}
	break;	
	case 'ficheformat_form':
		if($action=='save'){
			
			$serialcirc_diff->ficheformat_save($data); 
			print $serialcirc_diff->show_form(5);
		} elseif($action=='add_field'){			
			$serialcirc_diff->ficheformat_add_field($data); 
			print $serialcirc_diff->show_form(5);
		} elseif($action=='del_field'){			
			$serialcirc_diff->ficheformat_del_field($data); 
			print $serialcirc_diff->show_form(5);
		}
	break;	
	case 'empr_form':
		if($action=='save'){
			$data['duration']=$duration;
			$data['id_empr']=$id_empr;
			$serialcirc_diff->empr_save($id_diff,$data); 
			print $serialcirc_diff->show_form();
		}
	break;	
	case 'group_form':		
		if($action=='save'){
			$data['group_name']=$group_name;
			$data['type_diff']=$type_diff; // circ en Marguerite ou normal
			$data['duration']=$duration;
			
			$data['add_type']=$add_type;
			$data['caddie_select']=$caddie_select;
			$data['group_circ_select']=$group_circ_select;
			for($i=0;$i<=$empr_count;$i++){
				$id_empr=0;
				eval("\$id_empr=\$id_empr_".$i.";");
				if($id_empr){
					$data['empr_list'][]=$id_empr;
				}	
			}			
			$data['empr_resp']=$empr_resp;
			$serialcirc_diff->group_save($id_diff,$data); 
			print $serialcirc_diff->show_form();
		}		
	break;	
	case 'del_empr':
		print $serialcirc_diff->del_empr($id_empr); 	
	break;	
	case 'del_diff':
		if(count($diff_list))
		foreach($diff_list as $id_diff){
			$serialcirc_diff->del_diff($id_diff); 	
		}	
		print $serialcirc_diff->show_form();
	break;		
	case 'delete':
		if($msg_error=$serialcirc_diff->delete($num_abt)){
			$retour = "./circ.php?categ=serialcirc";
			error_message('', $msg_error, 1, $retour);
			print $serialcirc_diff->show_form();
		}else {			
			$query="select num_notice from abts_abts where abt_id=$num_abt";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$r = mysql_fetch_object($result);
				print "<script type=\"text/javascript\">document.location='catalog.php?categ=serials&sub=view&view=abon&serial_id=".$r->num_notice."';</script>";				
			}	
		}
	break;	
	default :	
		if($empr_id){	
			print $serialcirc_diff->show_form(4,$empr_id);
		}else	print $serialcirc_diff->show_form();
	break;		
	
}



