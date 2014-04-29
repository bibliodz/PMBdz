<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_empr.class.php,v 1.17 2013-12-30 10:16:50 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/serialcirc_diff.class.php");
require_once($class_path."/serialcirc.class.php");
require_once($include_path."/templates/serialcirc.tpl.php");
require_once($include_path."/mail.inc.php");
require_once($include_path."/serialcirc.inc.php");

class serialcirc_empr{
	var $empr_id;	// identifiant de l'emprunteur
	var $circ_list;	// tableau d'instance de serialcirc_empr_circ

	public function __construct(){
		$this->empr_id = $_SESSION['id_empr_session']*1;
		$this->get_my_circ_list();
	}

	public function get_my_circ_list(){
		$this->circ_list=array();
		$serialcirc_expl_list = serialcirc_empr::get_serialcirc_list($this->empr_id);
		for($i=0 ; $i<count($serialcirc_expl_list) ; $i++){
			$this->circ_list[] = new serialcirc_empr_circ($this->empr_id,$serialcirc_expl_list[$i]['id_serialcirc'],$serialcirc_expl_list[$i]['num_expl']);
		}	
	}

	//renvoi un tableau avec les ids des circulation de l'emprunteur
	public static function get_all_serialcirc($empr_id){
		$serialcirc_list = array();
		$empr_id+=0;
		$alone = "select distinct id_serialcirc from serialcirc_diff join serialcirc on num_serialcirc_diff_serialcirc = id_serialcirc where num_serialcirc_diff_empr = ".$empr_id;
		$group = "select distinct id_serialcirc from serialcirc_diff join serialcirc on num_serialcirc_diff_serialcirc = id_serialcirc join serialcirc_group on num_serialcirc_group_diff = id_serialcirc_diff where num_serialcirc_group_empr = ".$empr_id;
		$already_start = "select distinct num_serialcirc_circ_serialcirc as id_serialcirc from serialcirc_circ where num_serialcirc_circ_empr = ".$empr_id;
		$query = $alone." union ".$group." union ".$already_start;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$serialcirc_list[] = $row->id_serialcirc;
			}
		}
		return $serialcirc_list;
	}

	//renvoi un tableau avec les ids des circulation de l'emprunteur et les expl qui vont avec
	public static function get_serialcirc_list($empr_id){
		$empr_id+=0;
		$query = "select id_serialcirc, if(serialcirc_virtual = 0 or datediff(now(),date_add(serialcirc_expl_start_date, interval serialcirc_duration_before_send day)),num_serialcirc_expl_id,0) as num_serialcirc_expl_id from serialcirc left join serialcirc_expl on id_serialcirc=num_serialcirc_expl_serialcirc where id_serialcirc in (".implode(",",serialcirc_empr::get_all_serialcirc($empr_id)).") group by id_serialcirc,if(serialcirc_virtual = 0 or datediff(now(),date_add(serialcirc_expl_start_date, interval serialcirc_duration_before_send day)),num_serialcirc_expl_id,0) order by serialcirc_expl_start_date desc, serialcirc_expl_bulletine_date desc";
		$expl_list = array();
		$result = mysql_query($query);
		if($result && mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$expl_id = $row->num_serialcirc_expl_id;
				if($row->num_serialcirc_expl_id!=0){
					//on �limine ceux dont un emprunteur suivant a d�j� point�...
					$query = "select * from serialcirc_circ where num_serialcirc_circ_expl = ".$row->num_serialcirc_expl_id." order by serialcirc_circ_order asc";
					$res = mysql_query($query);
					if(mysql_num_rows($res)){
						$found =false;
						while($r = mysql_fetch_object($res)){
							if($r->num_serialcirc_circ_empr == $empr_id){
								$found = true;
								continue;
							}
							if($found){
								if($r->serialcirc_circ_pointed_date){
									$expl_id=0;
									break;
								}
							}
						}
					}
				}
				$expl_list[] = array(
					'id_serialcirc' => $row->id_serialcirc,
					'num_expl' => $expl_id
				);
			}
		}
		return $expl_list;
	}

	public function get_tab_circ_list(){
		global $charset,$msg;
		global $serialcirc_circ_list_tpl;
		
		$rows = "";
		for($i=0; $i<count($this->circ_list) ; $i++){
			$css_class = ($i%2 == 0 ? "odd" :"even"); 
			$rows .= $this->circ_list[$i]->get_tab_row($css_class);
		}
		$tab = str_replace("!!rows!!",$rows,$serialcirc_circ_list_tpl);
		return $tab;
	}

	public function get_point_form(){
		global $msg,$charset;
		return "
	<form method='post' action='' name='serialcirc_checkpoint'>
		<input type='text' name='expl_to_point' value='' title='".htmlentities($msg['serialcirc_codebarre'],ENT_QUOTES,$charset)."' placeholder='".htmlentities($msg['serialcirc_codebarre'],ENT_QUOTES,$charset)."'/>
		&nbsp;<input type='submit' class='bouton' value='".htmlentities($msg['serialcirc_point_expl'],ENT_QUOTES,$charset)."'/>
	</form>";
	}

	public function get_holding_form(){
		global $msg,$charset;
		return "
	<form method='post' action='' name='serialcirc_holding'>
		<input type='text' name='expl_to_hold' value='' title='".htmlentities($msg['serialcirc_codebarre'],ENT_QUOTES,$charset)."' placeholder='".htmlentities($msg['serialcirc_codebarre'],ENT_QUOTES,$charset)."'/>
		&nbsp;<input type='submit' class='bouton' value='".htmlentities($msg['serialcirc_hold_expl'],ENT_QUOTES,$charset)."'/>
	</form>";
	}

	public function point_expl($expl_to_point,$no_print=false){
		global $charset,$msg;

		$query = "select expl_id from exemplaires where expl_cb = '".$expl_to_point."'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$expl_id = mysql_result($result,0,0);
			$query = "select num_serialcirc_circ_diff,serialcirc_circ_subscription,serialcirc_circ_pointed_date,serialcirc_checked from serialcirc_circ join serialcirc on id_serialcirc = num_serialcirc_circ_serialcirc where num_serialcirc_circ_expl = ".$expl_id." and num_serialcirc_circ_empr = ".$this->empr_id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				if($row->serialcirc_circ_subscription != 0){
					if(!$row->serialcirc_circ_pointed_date){
						if($row->serialcirc_checked == 1){
							$query = "update serialcirc_circ set serialcirc_circ_pointed_date = now() where num_serialcirc_circ_expl = ".$expl_id." and num_serialcirc_circ_empr = ".$this->empr_id;
							$result = mysql_query($query);
							if($result){
								//on met � jour la table serialcirc_expl...
								$query = "update serialcirc_expl set num_serialcirc_expl_serialcirc_diff=".$row->num_serialcirc_circ_diff.",num_serialcirc_expl_current_empr=".$this->empr_id.", serialcirc_expl_trans_asked = 0, serialcirc_expl_trans_doc_asked = 0 where num_serialcirc_expl_id=".$expl_id;
								$result = mysql_query($query);
								if($result){
									$this->calc_new_expected_date($expl_id);
									return true;
								}
							}	
						}else{
							if(!$no_print) print htmlentities($msg['serialcirc_cant_point_expl'],ENT_QUOTES,$charset);
							return false;
						}
					}else{
						if(!$no_print) print htmlentities($msg['serialcirc_cant_point_expl_twice'],ENT_QUOTES,$charset);
						return false;
					}
				}else{
					//TODO revoir message, fallait s'incrire plutot...
					if(!$no_print) print htmlentities($msg['serialcirc_cant_point_expl'],ENT_QUOTES,$charset);
					return false;
				}
			}else{
				//TODO revoir message, pas dans nos abo..;
				if(!$no_print) print htmlentities($msg['serialcirc_point_expl_not_in_list'],ENT_QUOTES,$charset);
				return false;
			}
		}else{
			if(!$no_print) print htmlentities($msg['serialcirc_point_wrong_expl'],ENT_QUOTES,$charset);
			return false;
		}
		return false;
	}

	public function calc_new_expected_date($expl_id){
		$query = "select id_serialcirc, serialcirc_retard_mode,serialcirc_checked from serialcirc join serialcirc_expl on id_serialcirc = num_serialcirc_expl_serialcirc where num_serialcirc_expl_id = ".$expl_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			if($row->serialcirc_retard_mode == 0 && $row->serialcirc_checked == 1){
				//on r�cup�re le nombre de jours de d�calage...
				$query = "select datediff(now(),serialcirc_circ_expected_date) as diff, serialcirc_circ_order from serialcirc_circ join serialcirc_expl on num_serialcirc_circ_empr = num_serialcirc_expl_current_empr where num_serialcirc_circ_expl = ".$expl_id;
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$row = mysql_fetch_object($result);
					$query = "update serialcirc_circ set serialcirc_circ_expected_date = date_add(serialcirc_circ_expected_date, interval ".$row->diff." day) where num_serialcirc_circ_expl = ".$expl_id." and serialcirc_circ_order > ".$row->serialcirc_circ_order;
					$result = mysql_query($query);
					if($result) return true;
					else return false;
				}
			}
		}
		return true;
	}

	public function hold_expl($expl_to_hold,$no_print=false){
		global $charset,$msg;

		$query = "select expl_id from exemplaires where expl_cb = '".$expl_to_hold."'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$expl_id = mysql_result($result,0,0);
			if(count($this->circ_list) == 0) $this->get_my_circ_list();
			$found=false;
			for($i=0 ; $i<count($this->circ_list) ; $i++){
				if($this->circ_list[$i]->serialcirc_expl && $this->circ_list[$i]->num_expl == $expl_id){
					$found = true;
					if($this->circ_list[$i]->serialcirc['allow_resa']){
						$this->circ_list[$i]->send_hold_mail();
						$query = "update serialcirc_circ set serialcirc_circ_hold_asked = 1 where num_serialcirc_circ_empr = ".$this->empr_id." and num_serialcirc_circ_diff = ".$this->circ_list[$i]->num_serialcirc_diff." and num_serialcirc_circ_expl = ".$expl_id;
						$result = mysql_query($query);
						if($result){
							print "<span>".htmlentities($msg['serialcirc_holded'],ENT_QUOTES,$charset)."</span><br />";
							print "<a href='empr.php?tab=serialcirc&lvl=list_abo'>".htmlentities($msg['serialcirc_point_back_to_list'],ENT_QUOTES,$charset)."</a>";
						}else{
							return false;
						}
					}else{
						if(!$no_print) print htmlentities($msg['serialcirc_holding_not_allowed'],ENT_QUOTES,$charset);
						return false;
					}
				}
			}
			if(!$found){
				if(!$no_print) print htmlentities($msg['serialcirc_expl_not_in_circ'],ENT_QUOTES,$charset);
				return false;
			}
		}else{
			if(!$no_print) print htmlentities($msg['serialcirc_point_wrong_expl'],ENT_QUOTES,$charset);
			return false;
		}
		return true;
	}

	public function process_actions($id_serialcirc,$expl_id,$subscription=0,$ask_transmission=0,$report_late=0,$trans_accepted=0,$trans_doc_accepted=0,$ret_accepted=0){
		global $charset,$msg;

		$id_serialcirc+=0;
		$expl_id+=0;

		$empr_circ = new serialcirc_empr_circ($this->empr_id,$id_serialcirc,$expl_id);
		if($subscription==1){
			$empr_circ->subscribe();
		}else if($ask_transmission==1){
			$empr_circ->ask_transmission();
		}else if ($report_late == 1){
			$empr_circ->report_late();
		}else if ($trans_accepted == 1){
			$empr_circ->accept_transmission();
		}else if ($trans_doc_accepted == 1){
			$empr_circ->accept_transmission_doc();
		}else if($ret_accepted == 1){
			$empr_circ->accept_ret();
		}
	}

	public static function get_virtual_abo(){
		$virtual = array();
		$serialcirc_list = 
		$serialcirc_expl_list = serialcirc_empr::get_serialcirc_list($_SESSION['id_empr_session']);
		for($i=0 ; $i<count($serialcirc_expl_list) ; $i++){
			if($serialcirc_expl_list[$i]['num_expl']){
				$serialcirc = new serialcirc_empr_circ($_SESSION['id_empr_session'],$serialcirc_expl_list[$i]['id_serialcirc'],$serialcirc_expl_list[$i]['num_expl']);
				if($serialcirc->serialcirc['virtual'] == 1){
					$virtual[] = $serialcirc;
				}
			}
		}
		return $virtual;
	}

	public function ask_copy($bulletin_id,$analysis_ids,$comment){
		global $charset,$msg;

		$bulletin_id+=0;
		$query = "insert into serialcirc_copy set 
			num_serialcirc_copy_empr = ".$this->empr_id.",
			num_serialcirc_copy_bulletin = ".$bulletin_id.",
			serialcirc_copy_analysis = '".serialize($analysis_ids)."',
			serialcirc_copy_date = '".date("Y-m-d")."',
			serialcirc_copy_state = 0,
			serialcirc_copy_comment ='".$comment."'";
		$result = mysql_query($query);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function resume_ask_copy(){
		global $charset,$msg;
		global $opac_notice_affichage_class;
		global $serialcirc_copy_resume;
		global $opac_url_base;

		$list="
			<table>
				<tr>
					<th>".htmlentities($msg['serialcirc_ask_copy_date'],ENT_QUOTES,$charset)."</th>
					<th>".htmlentities($msg['serialcirc_ask_copy_issue'],ENT_QUOTES,$charset)."</th>
					<th>".htmlentities($msg['serialcirc_ask_copy_analysis'],ENT_QUOTES,$charset)."</th>
					<th>".htmlentities($msg['serialcirc_ask_copy_msg'],ENT_QUOTES,$charset)."</th>
					<th>".htmlentities($msg['serialcirc_ask_statut'],ENT_QUOTES,$charset)."</th>
				</tr>";
		$query="select * from serialcirc_copy where num_serialcirc_copy_empr = ".$this->empr_id." order by serialcirc_copy_state asc,serialcirc_copy_date asc";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$i=0;
			while($row = mysql_fetch_object($result)){
				$analysis_ids = unserialize($row->serialcirc_copy_analysis);
				if(count($analysis_ids)==0){
					$analysis="n/a";
				}else{
					$analysis="";
					for($j=0 ; $j<count($analysis_ids) ; $j++){
						$notice = new $opac_notice_affichage_class($analysis_ids[$j]);
						$notice->do_header();
						if($analysis.="")$analysis.="<br />";
						$analysis.= "<a href='".$opac_url_base."/index.php?lvl=notice_display&id=".$analysis_ids[$j]."'>".$notice->notice_header."</a>";
					}
				}
				$list.="
				<tr class='".($i%2 == 0 ? "odd" : "even")."'>
					<td>".htmlentities(format_date($row->serialcirc_copy_date),ENT_QUOTES,$charset)."</td>
					<td><a href='".$opac_url_base."index.php?lvl=bulletin_display&id=".$row->num_serialcirc_copy_bulletin."'>".bulletin_header($row->num_serialcirc_copy_bulletin)."</a></td>
					<td>".$analysis."</td>
					<td>".htmlentities($row->serialcirc_copy_comment,ENT_QUOTES,$charset)."</td>
					<td>".htmlentities($msg['serialcirc_copy_statut_'.$row->serialcirc_copy_state],ENT_QUOTES,$charset)."</td>
				</tr>";
				$i++;
			}
		}
		$list.="
			</table>";
		return str_replace("!!ask_copy_list!!",$list,$serialcirc_copy_resume);
	}

	public function show_ask_form($expl_cb){
		global $charset,$msg;

		$query = "select expl_id from exemplaires where expl_cb = '".$expl_cb."'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$expl_id = mysql_result($result,0,0);
			$expl_found=false;
			for ($i=0 ; $i<count($this->circ_list) ; $i++){
				if($this->circ_list[$i]->num_expl == $expl_id){
					$expl_found = true;
					return $this->circ_list[$i]->show_ask_form();
				}
			}
			if(!$expl_found){
				print htmlentities($msg['serialcirc_expl_not_in_circ'],ENT_QUOTES,$charset);
				return false;
			}
		}else{
			
			print htmlentities($msg['serialcirc_point_wrong_expl'],ENT_QUOTES,$charset);
			return false;
		}
	}

	public function unsubscribe($ids=array()){
		if(is_array($ids)){
			for($i=0 ; $i<count($ids) ; $i++){
				$ids[$i]+=0;
				$query = "insert into serialcirc_ask set 
					num_serialcirc_ask_perio = 0,
					num_serialcirc_ask_serialcirc=".$ids[$i].",
					num_serialcirc_ask_empr = ".$this->empr_id.",
					serialcirc_ask_type = 1,
					serialcirc_ask_statut = 0,
					serialcirc_ask_date = '".date("Y-m-d")."',
					serialcirc_ask_comment =''";
				$res = mysql_query($query);
				if($res){
					return true;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	public function ask_subscription($serial_id){
		$serial_id+=0;
		//TODO jeter les ids pourris....
		$query = "insert into serialcirc_ask set 
			num_serialcirc_ask_perio = ".$serial_id.",
			num_serialcirc_ask_serialcirc = 0,
			num_serialcirc_ask_empr = ".$this->empr_id.",
			serialcirc_ask_type = 0,
			serialcirc_ask_statut = 0,
			serialcirc_ask_date = '".date("Y-m-d")."',
			serialcirc_ask_comment =''";				
		$result = mysql_query($query);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function resume_ask(){
		global $charset,$msg;
		global $opac_url_base;
		
		$query = "select * from serialcirc_ask where num_serialcirc_ask_empr = ".$this->empr_id." order by serialcirc_ask_type asc, serialcirc_ask_statut asc";
		$result = mysql_query($query);
		$display="
			<div class='row'>
				<table>
					<tr>
						<th>".htmlentities($msg['serialcirc_ask_type'],ENT_QUOTES,$charset)."</th>
						<th>".htmlentities($msg['serialcirc_serial_name'],ENT_QUOTES,$charset)."</th>
						<th>".htmlentities($msg['serialcirc_ask_date'],ENT_QUOTES,$charset)."</th>
						<th>".htmlentities($msg['serialcirc_ask_statut'],ENT_QUOTES,$charset)."</th>
						<th>".htmlentities($msg['serialcirc_ask_msg'],ENT_QUOTES,$charset)."</th>
					</tr>
					!!rows!!
				</table>
			</div>
		";
		$rows="";
		if(mysql_num_rows($result)){
			$i=0;
			while($row = mysql_fetch_object($result)){
				if($row->num_serialcirc_ask_perio!=0){
					$query = "select tit1 from notices where notice_id = ".$row->num_serialcirc_ask_perio;
					$res= mysql_query($query);
					if(mysql_num_rows($res)){
						$serial = mysql_result($res,0,0);
					}
				}else{
					$serialcirc = new serialcirc($row->num_serialcirc_ask_serialcirc);
					$serial = $serialcirc->get_serial_title();
				}
				$rows.= "
					<tr class='".($i%2 == 0 ? "odd":"even")."'>
						<td>".htmlentities($msg['serialcirc_ask_type_'.$row->serialcirc_ask_type],ENT_QUOTES,$charset)."</td>
						<td><a href='".$opac_url_base."index.php?lvl=notice_display&id=".$row->num_serialcirc_ask_perio."'>".htmlentities($serial,ENT_QUOTES,$charset)."</a></td>
						<td>".htmlentities(formatdate($row->serialcirc_ask_date),ENT_QUOTES,$charset)."</td>
						<td>".htmlentities($msg['serialcirc_ask_statut_'.$row->serialcirc_ask_statut],ENT_QUOTES,$charset)."</td>
						<td>".htmlentities($row->serialcirc_ask_comment,ENT_QUOTES,$charset)."</td>
					</tr>";
				$i++;
			}
		}
		$display = str_replace("!!rows!!",$rows,$display);
		return $display;
	}
}

class serialcirc_empr_circ{
	var $empr_id;				// identifiant de l'emprunteur
	var $num_serialcirc_diff;	// identifiant de serialcirc_diff
	var $serialcirc;			// infos de serialcirc;
	var $serialcirc_expl;		// infos de serialcirc_expl
	var $rank;					// rang de l'emprunteur
	var $unsubscribe;			// demande de d�sinscription
	var $serial_id;
	var $serial_title;
	var $issue_title = "";

	public function __construct($empr_id,$id_serialcirc,$num_expl){
		$this->empr_id = $empr_id*1;
		$this->id_serialcirc = $id_serialcirc*1;
		$this->num_expl = $num_expl*1;
		$this->fetch_data();
	}

	public function fetch_data(){
		//serialcirc
		$this->get_serialcirc_infos();
		$this->get_serialcirc_expl_infos();
		$this->get_serialcirc_circ_infos();
		$this->get_issue_title();
	}

	public function get_serialcirc_infos(){
		$query = "select * from serialcirc where id_serialcirc = ".$this->id_serialcirc;
		$result = mysql_query($query);
		$this->serialcirc = array();
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$this->serialcirc['num_abt'] = $row->num_serialcirc_abt;
			$this->serialcirc['type'] = $row->serialcirc_type;
			$this->serialcirc['virtual'] = $row->serialcirc_virtual;
			//$row->serialcirc_duration;
			$this->serialcirc['check'] = $row->serialcirc_checked;
			$this->serialcirc['late_mode'] = $row->serialcirc_retard_mode;
			$this->serialcirc['allow_resa'] = $row->serialcirc_allow_resa;
			$this->serialcirc['allow_copy'] = $row->serialcirc_allow_copy;
			//$row->serialcirc_allow_send_ask;
			$this->serialcirc['allow_subscription'] = $row->serialcirc_allow_subscription;
			$this->serialcirc['duration_before_send'] = $row->serialcirc_duration_before_send;
			//$row->serialcirc_expl_statut_circ;
			//$row->serialcirc_expl_statut_circ_after;
			$this->serialcirc['state'] = $row->serialcirc_state;
		}
	}

	public function get_serialcirc_expl_infos(){
		$query = "select 
			serialcirc_expl_bulletine_date,
			serialcirc_expl_state_circ,
			serialcirc_expl_ret_asked,
			serialcirc_expl_trans_asked,
			serialcirc_expl_trans_doc_asked,
			num_serialcirc_expl_current_empr,
			serialcirc_expl_start_date,
			expl_cb
		from serialcirc_expl join exemplaires on expl_id = num_serialcirc_expl_id where num_serialcirc_expl_id = ".$this->num_expl;
		$result = mysql_query($query);
		$this->serialcirc_expl = array();
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$this->serialcirc_expl['bulletine_date'] = $row->serialcirc_expl_bulletine_date;
			$this->serialcirc_expl['state_circ'] = $row->serialcirc_expl_state_circ;
			$this->serialcirc_expl['ret_asked']  = $row->serialcirc_expl_ret_asked;
			$this->serialcirc_expl['trans_asked']  = $row->serialcirc_expl_trans_asked;
			$this->serialcirc_expl['trans_doc_asked']  = $row->serialcirc_expl_trans_doc_asked;
			$this->serialcirc_expl['num_current_empr']  = $row->num_serialcirc_expl_current_empr;
			$this->serialcirc_expl['start_date_sql'] = $row->serialcirc_expl_start_date;
			if ($row->serialcirc_expl_start_date!=0) $this->serialcirc_expl['start_date'] = format_date($row->serialcirc_expl_start_date);
			else $this->serialcirc_expl['start_date'] = "";
			$this->serialcirc_expl['cb'] = $row->expl_cb;
		}
	}

	public function get_serialcirc_circ_infos(){
		$query = "select serialcirc_circ.*,serialcirc_diff_type_diff from serialcirc_circ join serialcirc_diff on num_serialcirc_circ_diff = id_serialcirc_diff where num_serialcirc_circ_expl = ".$this->num_expl." order by serialcirc_circ_order asc";
		$result = mysql_query($query);
		$this->serialcirc_circ = array();
		if(mysql_num_rows($result)){
			while ($row = mysql_fetch_object($result)){
				if($row->num_serialcirc_circ_empr == $this->empr_id){
					$this->num_serialcirc_diff = $row->num_serialcirc_circ_diff;
				}
				$this->serialcirc_circ[] = array(
					'num_diff' => $row->num_serialcirc_circ_diff,
					'type' => $row->serialcirc_diff_type_diff,
					'num_empr' => $row->num_serialcirc_circ_empr,
					'order' => $row->serialcirc_circ_order,
					'subscription' => $row->serialcirc_circ_subscription,
					'hold_asked' => $row->serialcirc_circ_hold_asked,
					'ret_asked' => $row->serialcirc_circ_ret_asked,
					'trans_asked' => $row->serialcirc_circ_trans_asked,
					'trans_doc_asked' => $row->serialcirc_circ_trans_doc_asked,
					'expected_date' => $row->serialcirc_circ_expected_date,
					'pointed_date' => $row->serialcirc_circ_pointed_date
				);
			}
		}
	}
	
	public function get_issue_title(){
		global $msg;
		if($this->issue_title == ""){					
			$query = "select bulletin_titre,mention_date,bulletin_numero from exemplaires join bulletins on bulletin_id = expl_bulletin where expl_id =".$this->num_expl;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$this->issue_title = $row->bulletin_numero;
				if($row->mention_date) $this->issue_title.=" ".$row->mention_date;
				if($row->bulletin_titre) $this->issue_title.=" ".$row->bulletin_titre;
			}else{
				$this->issue_title = $msg['serialcirc_no_expl_in_circulation'];
			}
		}
		return $this->issue_title;
	}
	
	public function get_issue_id(){
		global $msg;
		if(!$this->issue_id){					
			$query = "select bulletin_id from exemplaires join bulletins on bulletin_id = expl_bulletin where expl_id =".$this->num_expl;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$this->issue_id= $row->bulletin_id;
			}
		}
		return $this->issue_id;
	}
	
	public function get_serial_title(){
		if(!$this->serial_title){
			$query="select tit1 from notices join abts_abts on num_notice = notice_id where abt_id = ".$this->serialcirc['num_abt'];
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$this->serial_title = mysql_result($result,0,0);
			}
		}
		return $this->serial_title;
	}
	
	public function get_serial_id(){
		if(!$this->serial_id){
			$query="select num_notice from abts_abts where abt_id = ".$this->serialcirc['num_abt'];
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$this->serial_id = mysql_result($result,0,0);
			}
		}
		return $this->serial_id;
	}

	public function get_tab_row($css_class){
		global $charset,$msg;
		global $opac_url_base;
		$this->get_rank();
		$this->check_unsubscribe();
		for($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
			if($this->serialcirc_circ[$i]['num_empr'] == $this->empr_id){
				$current_empr = $this->serialcirc_circ[$i];
				break;
			}
		}
		$issue ="";
		if($this->get_issue_id()!=0){
			$issue = "<a href='".$opac_url_base."index.php?lvl=bulletin_display&id=".$this->get_issue_id()."'>".htmlentities($this->get_issue_title(),ENT_QUOTES,$charset)."</a>";
		}else{
			$issue = htmlentities($this->get_issue_title(),ENT_QUOTES,$charset);
		}
		
		$row_tpl = "
		<tr class='".$css_class."'>
			<td><input type='checkbox' name='unsubscribe' value='".$this->id_serialcirc."' ".($this->unsubscribe ? "checked='checked' disabled='disabled'":"")."/></td>
			<td><a href='".$opac_url_base."index.php?lvl=notice_display&id=".$this->get_serial_id()."'>".htmlentities($this->get_serial_title(),ENT_QUOTES,$charset)."</a></td>
			<td>".htmlentities($msg['serialcirc_virtual_mode_'.$this->serialcirc['virtual']],ENT_QUOTES,$charset)."</td>
			<td>".$issue."</td>
			<td>".htmlentities($this->serialcirc_expl['start_date'],ENT_QUOTES,$charset)."</td>
			<td>".htmlentities($this->serialcirc_expl['cb'],ENT_QUOTES,$charset)."</td>
			<td>".htmlentities($this->rank,ENT_QUOTES,$charset)."</td>
			<td>".htmlentities(format_date($current_empr['expected_date']),ENT_QUOTES,$charset)."</td>
			<td>".htmlentities(format_date($this->get_transmission_date()),ENT_QUOTES,$charset)."</td>
			<td>".$this->get_actions_form()."</td>
		</tr>";
		return $row_tpl;
	}

	public function get_transmission_date(){
		$found=false;
		for($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
			if($found && $this->serialcirc_circ[$i]['subscription'] == 1){
				return $this->serialcirc_circ[$i]['expected_date'];
			}
			if($this->serialcirc_circ[$i]['num_empr'] == $this->empr_id){
				$current = $i;
				$found=true;
			}
		}
		//si on l'a pas trouv�, on la calcule (on est le dernier...)
		$diff = new serialcirc_diff_dest($this->serialcirc_circ[$current]['num_diff']);
		$query = "select date_add('".$this->serialcirc_circ[$current]['expected_date']."', interval ".$diff->duration." day)";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result)){
			return mysql_result($result,0,0);
		}
		return 0;
	}
	
	public function get_rank(){
		if($this->num_expl && $this->serialcirc_expl['state_circ'] == SERIALCIRC_EXPL_STATE_CIRC_inprogress){
			$rank = 0;
			$empr_found=false;
			if($this->serialcirc_expl['num_current_empr'] == $this->empr_id){
				$empr_found = true;
				$this->rank = 0;
			}else{
				$last_diff = 0;
				$current_found=false;
				for($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
					if($this->serialcirc_expl['num_current_empr'] == $this->serialcirc_circ[$i]['num_empr']){
						$current_found=true;
					}
					if($current_found || $this->serialcirc_expl['num_current_empr'] == 0){
						if($this->serialcirc_circ[$i]['num_empr'] == $this->empr_id){
							$empr_found = true;
							$this->rank = $rank;
							break;
						}
						if($last_diff == $this->serialcirc_circ[$i]['num_diff'] && $this->serialcirc_circ[$i]['type'] == 0){
							$rank++;
						}else if($last_diff != $this->serialcirc_circ[$i]['num_diff']){
							$rank++;
						}
						$last_diff = $this->serialcirc_circ[$i]['num_diff'];
					}
				}
				if(!$empr_found){
					$this->rank = "";
				}
			}	
		}else if($this->num_expl){
			$query = "select * from serialcirc_diff where num_serialcirc_diff_serialcirc = ".$this->id_serialcirc." order by serialcirc_diff_order asc";
			$result = mysql_query($query);
			$rank=0;
			$empr_found = false;
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					if($row->serialcirc_diff_empr_type == 0 || $row->serialcirc_diff_type_diff == 0){
						if(!$empr_found){
							if($row->num_serialcirc_diff_empr == $this->empr_id){
								$empr_found = true;
								break;
							}else{
								$rank++;
							}
						}
					}else{
						$gp_query = "select num_serialcirc_group_empr from serialcirc_group where num_serialcirc_group_diff = ".$row->id_serialcirc_diff." order by serialcirc_group_order asc";
						$gp_result = mysql_query($gp_query);
						if(mysql_num_rows($gp_result)){
							while($gp_row = mysql_fetch_object($gp_result)){
								if(!$empr_found){
									if($gp_row->num_serialcirc_group_empr == $this->empr_id){
										$empr_found = true;
										break;
									}else{
										$rank++;
									}									
								}
							}
						}
					}
				}
			}
			if($empr_found) $this->rank = $rank;
		}else{
			$this->rank = "";
		}
	}

	public function is_late(){
		//TODO expected_date
		for ($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
			if($this->serialcirc_circ[$i]['num_empr'] == $this->empr_id){
				$empr_circ = $this->serialcirc_circ[$i];
			}	
		}
		$query = "select date_diff(now(),'".$empr_circ['expected_date']."')";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$diff = mysql_result($result,0,0);
			if($diff > 0) return true;
		}
		return false;
	}

	public function get_actions_form(){
		global $charset,$msg;
		//pas d'actions si pas de pointage...
		$form="
					<form method='post' action='empr.php?tab=serialcirc&lvl=list_abo' name='actions_form_".$this->id_serialcirc."_".$this->num_expl."'>
						<input type='hidden' name='id_serialcirc' value='".htmlentities($this->id_serialcirc,ENT_QUOTES,$charset)."'/>
						<input type='hidden' name='expl_id' value='".htmlentities($this->num_expl,ENT_QUOTES,$charset)."'/>
						<input type='hidden' name='actions_form_submit' value ='1' />";
		if($this->serialcirc['check'] == 1){
			//si le premier n'a pas point�, on consid�re pas qu'il est en retard...
			if($this->serialcirc_expl['num_current_empr'] != 0){
				for($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
					if($this->serialcirc_circ[$i]['num_empr'] == $this->serialcirc_expl['num_current_empr']){
						$current_circ = $this->serialcirc_circ[$i];
					}
				}				
				if($this->rank === 0){
					if($this->serialcirc_expl['ret_asked'] == SERIALCIRC_EXPL_RET_asked){
							$form.="
						<input type='hidden' name='ret_accepted' value='1' />
						<input type='button' class='imp_bouton' value='".htmlentities($msg['serialcirc_ret_asked'],ENT_QUOTES,$charset)."' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();'/>";
						//transmission demand� par le centre de doc
					}else if($this->serialcirc_expl['trans_doc_asked'] == SERIALCIRC_EXPL_TRANS_DOC_asked) {
							$form.="
						<input type='hidden' name='trans_doc_accepted' value='1' />
						<input type='button' class='imp_bouton' value='".htmlentities($msg['serialcirc_trans_doc_asked'].($current_circ['trans_doc_asked']*1 >0 ? " (".$current_circ['trans_doc_asked'].")":""),ENT_QUOTES,$charset)."' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();'/>";
						//transmission demand�e
					}else if($this->serialcirc_expl['trans_asked'] == SERIALCIRC_EXPL_TRANS_asked){
							$form.="
						<input type='hidden' name='trans_accepted' value='1' />
						<input type='button' class='imp_bouton' value='".htmlentities($msg['serialcirc_trans_asked'].($current_circ['trans_asked']*1 >0 ? " (".$current_circ['trans_asked'].")":""),ENT_QUOTES,$charset)."' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();'/>";	
					}					
				}else if($this->serialcirc['type']== SERIALCIRC_TYPE_rotative && $this->rank == 1 && $this->serialcirc_expl['state_circ']==SERIALCIRC_EXPL_STATE_CIRC_inprogress){
					$form.="
						<input type='hidden' name='report_late' value='1' />
						<input type='hidden' name='ask_transmission' value='1' />";					
						
						if($this->is_late()){
							if($this->serialcirc_expl['trans_doc_asked'] != SERIALCIRC_EXPL_TRANS_DOC_ask && $this->serialcirc_expl['trans_doc_asked'] != SERIALCIRC_EXPL_TRANS_DOC_asked){
								$form.="
							<input type='button' class='bouton' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].report_late.value=1;document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].ask_transmission.value=0;document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();' value='".htmlentities($msg['serialcirc_report_late'].($current_circ['trans_doc_asked']*1 >0 ? " (".$current_circ['trans_doc_asked'].")":""),ENT_QUOTES,$charset)."'/>";
							}else{
								$form.="
							<input type='button' class='bouton' disabled='disabled' value='".htmlentities($msg['serialcirc_late_reported'].($current_circ['trans_doc_asked']*1 >0 ? " (".$current_circ['trans_doc_asked'].")":""),ENT_QUOTES,$charset)."'/>";
							}		
						}
						$form.="
						<input type='button' class='bouton' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].ask_transmission.value=1;document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].report_late.value=0;document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();' value='".htmlentities($msg['serialcirc_ask_transmission'].($current_circ['trans_asked']*1 >0 ? " (".$current_circ['trans_asked'].")":""),ENT_QUOTES,$charset)."'/>";
				}
			}
		}
		
		if($this->num_expl && $this->serialcirc['virtual'] == 1  && $this->serialcirc_expl['state_circ'] == SERIALCIRC_EXPL_STATE_CIRC_pending){
			$query = "select date_add('".$this->serialcirc_expl['start_date_sql']."', interval ".$this->serialcirc['duration_before_send']." day)";
			$res = mysql_query($query);
			if(mysql_num_rows($res)){
				$end_subscription = mysql_result($res,0,0);
				$query = "select datediff('".$end_subscription."',now())";
				$res = mysql_query($query);
				if(mysql_num_rows($res)){
					$test = mysql_result($res,0,0);
				}else $test = -1;
				if($test >=0 && !serialcirc_empr_circ::is_subscribe($this->empr_id,$this->num_expl)){
					$form.="
					<input type='hidden' name='subscription' value='1' />
					<input type='button' class='bouton' onclick='document.forms[\"actions_form_".$this->id_serialcirc."_".$this->num_expl."\"].submit();' value='".htmlentities(sprintf($msg['serialcirc_subscribe_list'],formatdate($end_subscription)),ENT_QUOTES,$charset)."' />";
				}else{
					$form.="
					<input type='button' class='bouton' disabled='disabled' value='".htmlentities(sprintf($msg['serialcirc_subscribe_list'],formatdate($end_subscription)),ENT_QUOTES,$charset)."' />";
				}
			}
		}
		$form.="
				</form>";		
		return $form;		
	}
	
	public static function is_subscribe($empr_id,$expl_id){
		$query = "select serialcirc_circ_subscription from serialcirc_circ where num_serialcirc_circ_empr = ".$empr_id." and num_serialcirc_circ_expl = ".$expl_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$subscribe = mysql_result($result,0,0)*1;
			if($subscribe == 1){
				return true;
			}else return false;
		}
		return false;
	}

	public function subscribe(){
		$query ="update serialcirc_circ set serialcirc_circ_subscription=1 where num_serialcirc_circ_empr = ".$this->empr_id." and num_serialcirc_circ_expl = ".$this->num_expl;
		$result = mysql_query($query);
		if(mysql_affected_rows($result))
			return true;
		else 
			return false;
	}

	public function check_unsubscribe(){
		if(!$this->unsubscribe){
			$query = "select serialcirc_ask_statut from serialcirc_ask where num_serialcirc_ask_serialcirc = ".$this->id_serialcirc." and serialcirc_ask_type = 1 and num_serialcirc_ask_empr = ".$this->empr_id;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$statut = mysql_result($result,0,0);
				if($statut <2 ){
					$this->unsubscribe = true;
				}else{
					$this->unsubscribe = false;
				}
			}else{
				$this->unsubscribe = false;
			}
		}
		return $this->unsubscribe;
	}

	public function ask_transmission(){
		global $charset,$msg;
		global $ask_transmission_mail;

		$subject = $msg['serialcirc_asking_transmission'];
		$mail = $this->get_mail_infos($this->serialcirc_expl['num_current_empr']);
		$content = $ask_transmission_mail;

		$this->_send_mail($mail['dest'],$mail['cc'],$subject,$content);
		$this->serialcirc_expl['trans_asked'] = SERIALCIRC_EXPL_TRANS_asked;
		$query = "update serialcirc_expl set serialcirc_expl_trans_asked = 1 where num_serialcirc_expl_id = ".$this->num_expl;
		$result = mysql_query($query);
		if($result){
			$query = "update serialcirc_circ set serialcirc_circ_trans_asked = serialcirc_circ_trans_asked+1 where num_serialcirc_circ_expl = ".$this->num_expl." and num_serialcirc_circ_empr = ".$this->serialcirc_expl['num_current_empr'];
			$result = mysql_query($query);
			if(!$result){
				return false;
			}
		}else{
			return false;
		}
		return true;
	}

	public function report_late(){
		global $charset,$msg;
		global $report_late_mail;
		global $opac_biblio_name;

		$subject=$msg['serialcirc_report_late_mail'];

		$dest = array();
		$dest['mail'] = $this->_get_users_mails();
		$dest['name'] = $opac_biblio_name; 
		if($dest['mail']!= ""){
			$from = serialcirc_empr_circ::get_mail_infos($this->empr_id);
			$content = str_replace("!!empr!!",$from['name'],$report_late_mail);
			$this->_send_mail($dest,"",$subject,$content,$from['name'],$from['mail']);
		}
		$query = "update serialcirc_expl set serialcirc_expl_trans_doc_asked = ".SERIALCIRC_EXPL_TRANS_DOC_ask." where num_serialcirc_expl_id = ".$this->num_expl;
		$result = mysql_query($query);
		if(!mysql_affected_rows($result)) return false;
		return true;
	}

	public function accept_transmission(){
		global $charset,$msg;
		global $transmission_accepted_mail;

		$subject = $msg['serialcirc_transmission_accepted'];
		$content = $transmission_accepted_mail;

		$mail = $this->get_mail_infos($this->get_next_empr());
		$this->_send_mail($mail,"",$subject,$content);
		$query = "update serialcirc_expl set serialcirc_expl_trans_asked = ".SERIALCIRC_EXPL_TRANS_accepted." where num_serialcirc_expl_id = ".$this->num_expl;
		$result = mysql_query($query);
		if(!$result) return false;
		return true;
	}

	public function accept_transmission_doc(){
		global $charset,$msg;
		global $transmission_accepted_mail;

		$subject = $msg['serialcirc_transmission_accepted'];
		$content = $transmission_accepted_mail;

		$mail = $this->get_mail_infos($this->get_next_empr());
		$this->_send_mail($mail,"",$subject,$content);
		$query = "update serialcirc_expl set serialcirc_expl_trans_doc_asked = ".SERIALCIRC_EXPL_TRANS_DOC_accepted.", serialcirc_expl_trans_asked = ".SERIALCIRC_EXPL_TRANS_accepted." where num_serialcirc_expl_id = ".$this->num_expl;
		$result = mysql_query($query);
		if(!$result) return false;
		return true;
	}

	public function accept_ret(){
		global $charset,$msg;
		global $ret_accepted_mail;

		$subject = $msg['serialcirc_ret_accepted'];
		$content = $ret_accepted_mail;

		$mail = $this->_get_users_mails();
		if($mail!=""){
			$dest=array(
				'mail' => $mail,
				'name' => $opac_biblio_email
			);
			$from = serialcirc_empr_circ::get_mail_infos($this->empr_id);
			$content = str_replace("!!empr!!",$from['name'],$content);		
			$this->_send_mail($dest,"",$subject,$content,$from['name'],$from['mail']);
		}
		$query = "update serialcirc_expl set serialcirc_expl_ret_asked = ".SERIALCIRC_EXPL_TRANS_DOC_accepted;
		$result = mysql_query($query);
		if(!mysql_affected_rows($result)) return false;
		return true;
	}

	public function send_hold_mail(){
		global $charset,$msg;
		global $opac_bilio_email;
		global $serialcirc_hold_mail;

		$mail = $this->_get_users_mails();
		if($mail!=""){
			$dest=array(
				'mail' => $mail,
				'name' => $opac_biblio_email
			);
			$from = serialcirc_empr_circ::get_mail_infos($this->empr_id);
			$content = str_replace("!!empr!!",$from['name'],$serialcirc_hold_mail);		
			return $this->_send_mail($dest,"",$msg['serialcirc_hold_mail'],$content);
		}else{
			return true;
		}
	}

	private function _send_mail($dest,$cc="", $subject, $content,$from_name="",$from_mail=""){
		global $charset;	
		global $opac_biblio_name;
		global $opac_biblio_email;

		$headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=".$charset."\n";
		$content = str_replace("!!issue!!",$this->get_issue_title()." in ".$this->get_serial_title(),$content);
		if($from_name == ""){
			$from_name = $opac_biblio_name;
		}
		if($from_mail == ""){
			$from_mail = $opac_biblio_email;
		}
		if($dest['mail']!=""){
			return mailpmb($dest['name'], $dest['mail'], $subject, $content, $from_name, $from_mail, $headers,$cc);
		}else{
			return true;
		}
	}

	private function _get_users_mails(){
		global $pmb_lecteurs_localises;
		$mails="";
		if($pmb_lecteurs_localises){
			$query = "select user_email from users join empr on empr_location = deflt2docs_location where user_alert_resamail=1 and id_empr=".$this->empr_id;
		}else{
			$query = "select user_email from users where user_alert_resamail=1";
		}
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				if($row->user_email != ""){
					if($mails!="") $mails.=";";
					$mails.=$row->user_email;
				}
			}
		}
		return $mails;
	}

	public static function get_mail_infos($empr_id){
		$query = "select empr_nom, empr_prenom, empr_mail from empr where id_empr = ".$empr_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$mail=array(
				'name' => $row->empr_nom.($row->empr_prenom ? " ".$row->empr_prenom : ""),
				'mail' => $row->empr_mail
			);
		}
		return $mail;
	}

	public function get_next_empr(){
		if($this->serialcirc_expl['num_current_empr'] != 0){
			for($i=0 ; $i<count($this->serialcirc_circ) ; $i++){
				if($this->serialcirc_circ[$i]['num_empr'] == $this->serialcirc_expl['num_current_empr']){
					$current_circ = $this->serialcirc_circ[$i];
				}
			}
		}
		$query = "select num_serialcirc_circ_empr from serialcirc_circ where serialcirc_circ_order > ".$current_circ['order']." and num_serialcirc_circ_expl = ".$this->num_expl." order by serialcirc_circ_order asc limit 1";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$next = mysql_result($result,0,0);
		}else{
			$next =0;
		}
		return $next;
	}
	
	public function show_ask_form(){
		global $charset,$msg;
		global $opac_notice_affichage_class;
		
		$form = "";
		if($this->serialcirc['allow_copy']){
			$analysis_ids = $this->get_analysis_list();
		
			$query = "select expl_bulletin from exemplaires where expl_id =".$this->num_expl;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$id_issue = mysql_result($result,0,0);
			}
			$form ="
		<div id='serialcirc_ask_copy' style='z-index:inherit;padding:10px;border:2px solid black;background-color:white;position:absolute;top:40%;left:40%'>
			<h3>".htmlentities($msg['serialcirc_ask_copy_title'],ENT_QUOTES,$charset)." ".bulletin_header($id_issue)."</h3>
			<form name='serialcirc_ask_copy' action='empr.php?tab=serialcirc&lvl=copy&action=ask_copy' method='post'>";
			if(count($analysis_ids)){
				$form.="
				<div class='row'>
					".htmlentities($msg['serialcirc_ask_analysis'],ENT_QUOTES,$charset)."
				</div>";
				for($i=0 ; $i<count($analysis_ids) ; $i++){
					$analysis = new $opac_notice_affichage_class($analysis_ids[$i]);
					$analysis->do_header();
					$form.="
				<div class='row'>
					<input type='checkbox' name='serialcirc_ask_copy_analysis[]' id='serialcirc_ask_copy_analysis_".$analysis_ids[$i]."' value='".$analysis_ids[$i]."' />&nbsp;
					<label for ='serialcirc_ask_copy_analysis_".$analysis_ids[$i]."'>".$analysis->notice_header."</label>
				</div>";
				}
				$form .="
				<div class='row'>&nbsp;</div>";
			}
			$form .="
				<div class='row'>
					<label for='serialcirc_ask_comment'>".htmlentities($msg['serialcirc_ask_comment'],ENT_QUOTES,$charset)."</label><br />
					<textarea name='serialcirc_ask_comment' rows='5' cols='60'></textarea>
				</div>
				<div class='row'>&nbsp;</div>
				<div class='row'>
					<input type='hidden' name='bulletin_id' value='".$id_issue."'/>
					<input type='submit' class='bouton' value='".htmlentities($msg['serialcirc_ask_submit_button'],ENT_QUOTES,$charset)."' />
				</div>
			</form>
		</div>
		<script type='text/javascript'>
			document.getElementById('att').appendChild(document.getElementById('serialcirc_ask_copy'));
		</script>";
		}else{
			$form=htmlentities($msg['serialcirc_cant_copy_expl'],ENT_QUOTES,$charset);
		}
		
		return $form;
	}
	
	public function get_analysis_list(){
		$query = "select analysis_notice from analysis join bulletins on analysis_bulletin = bulletin_id join exemplaires on bulletin_id = expl_bulletin where expl_id =".$this->num_expl;
		$result = mysql_query($query);
		$analysis_ids=array();
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$analysis_ids[] = $row->analysis_notice;
			}
		}
		return $analysis_ids;
	}
	
	public function show_issue_display($opened_expl=0){
		global $opac_notices_depliable;
		global $msg,$charset;
		
		$query = "select expl_bulletin from exemplaires where expl_id =".$this->num_expl;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$id_issue = mysql_result($result,0,0);
		}
		$serialcirc = new serialcirc($this->num_serialcirc);
		
		$content = bulletin_affichage($id_issue);
		$query = "select date_add('".$this->serialcirc_expl['start_date_sql']."', interval ".$this->serialcirc['duration_before_send']." day)";
		$res = mysql_query($query);
		if(mysql_num_rows($res)){
			$end_subscription = mysql_result($res,0,0);
			$query = "select datediff('".$end_subscription."',now())";
			$res = mysql_query($query);
			if(mysql_num_rows($res)){
				$test = mysql_result($res,0,0);
			}else $test = -1;
			if($test >=0 && !serialcirc_empr_circ::is_subscribe($_SESSION['id_empr_session'],$this->num_expl)){
				$form="
				<input type='submit' class='bouton' value='".htmlentities(sprintf($msg['serialcirc_subscribe_list'],formatdate($end_subscription)),ENT_QUOTES,$charset)."' />";
			}else{
				$form="
				<input type='submit' class='bouton' disabled='disabled' value='".htmlentities(sprintf($msg['serialcirc_subscribe_list'],formatdate($end_subscription)),ENT_QUOTES,$charset)."' />";
			}
		}
		$content.= "
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<form action='empr.php?tab=serialcirc&lvl=list_abo' method='post' name='actions_form_".$this->num_serialcirc."_".$this->num_expl."' style='display:inline;'>
				<input type='hidden' name='id_serialcirc' value='".htmlentities($this->num_serialcirc,ENT_QUOTES,$charset)."'/>
				<input type='hidden' name='expl_id' value='".htmlentities($this->num_expl,ENT_QUOTES,$charset)."'/>
				<input type='hidden' name='actions_form_submit' value ='1' />
				<input type='hidden' name='subscription' value='1' />
				$form
			</form>";
		if($serialcirc->allow_copy){
		$content.= "&nbsp;
			<form action='empr.php?tab=serialcirc&lvl=list_virtual_abo&action=ask_copy' method='post' style='display:inline;'>
				<input type='hidden' name='expl_id' value='".htmlentities($this->num_expl,ENT_QUOTES,$charset)."'/>
				<input type='submit' class='bouton' value='".htmlentities($msg['serialcirc_ask_copy'],ENT_QUOTES,$charset)."' />
			</form>
		";	
		}		
		$content.= "		
		</div>
		<div class='row'>&nbsp;</div>
		";
		if($opac_notices_depliable){
			if($opened_expl == $this->num_expl){
				$open = 1;
			}else $open=0;
			$display = gen_plus("serialcirc_issue".$id_issue,bulletin_header($id_issue),$content,$open);
		}else{
			$display = $content;
		}
		return $display;
	}
}