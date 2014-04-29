<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_onglet.class.php,v 1.4 2013-06-20 07:51:19 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once("./classes/notice_tpl_gen.class.php"); 

class notice_onglet {
	
	public function __construct($id_tpl){
		$this->id_tpl=$id_tpl+0;
		$this->fetch_data();
	}
	
	protected function fetch_data(){
		if(!$this->id_tpl)return false;
		$this->noti_tpl=new notice_tpl_gen($this->id_tpl);
		
			
	}

	public function get_onglet_header(){
		//print ($this->noti_tpl->name);
		return($this->noti_tpl->name);
	}	
	
	public function get_onglet_content($id_notice){
		$tpl= $this->noti_tpl->build_notice($id_notice);		
		
		return $tpl;
	}
} // class end

class notice_onglets {
	
	public function __construct($ids=''){
		global $opac_notices_format_onglets;

		if(!$ids)$ids=$opac_notices_format_onglets;
		$this->ids=$ids;
		
	}
	
	public function insert_onglets($id_notice,$retour_aff){			
		$id_notice+=0;
		$onglets_title="";
		$onglets_content="";
		if($this->ids){
			$onglets=explode(",", $this->ids);
			foreach($onglets as $id_tpl){
				if(is_numeric($id_tpl)){
					$notice_onglet=new notice_onglet($id_tpl);
					
					$title=$notice_onglet->get_onglet_header();
					$onglet_title="
					<li id='onglet_tpl_".$id_tpl."_".$id_notice."'  class='isbd_public_inactive'>
						<a href='#' title=\"".$title."\" onclick=\"show_what('tpl_".$id_tpl."_', '$id_notice'); return false;\">".$title."</a>
					</li>";
		
					$content=$notice_onglet->get_onglet_content($id_notice);
					$onglet_content="
					<div id='div_tpl_".$id_tpl."_".$id_notice."' class='onglet_tpl' style='display:none;'>
						".$content."
					</div>";
					// Si pas de titre ou de contenu rien ne s'affiche.
					if($title && $onglet_content){
						$onglets_title.=$onglet_title;
						$onglets_content.=$onglet_content;
					}
				}
			}
		}	
		$retour_aff=str_replace('<!-- onglets_perso_list -->', $onglets_title, $retour_aff);
		$retour_aff=str_replace('<!-- onglets_perso_content -->', $onglets_content, $retour_aff);
		return $retour_aff;
	}
	
	public function build_onglets($id_notice,$li_tags){
		global $opac_notices_format,$msg;
	
		$id_notice+=0;
		$onglets_title="";
		$onglets_content="";
		if($this->ids){
			$onglets=explode(",", $this->ids);
			$first=1;
			foreach($onglets as $id_tpl){
				// gestion du premier onglet vivible
				if(!$opac_notices_format && $first){
					$class_onglet='isbd_public_active';
					$display_onglet='display:block;';
					$first=0;
				}else {
					$class_onglet='isbd_public_inactive';
					$display_onglet='display:none;';
				}
				
				if($id_tpl=="ISBD"){	    	
					$onglets_title.="
			    		<li id='onglet_isbd!!id!!' class='$class_onglet'><a href='#' title=\"".$msg['ISBD_info']."\" onclick=\"show_what('ISBD', '!!id!!'); return false;\">".$msg['ISBD']."</a></li>";
			    		
					$onglets_content.="
						<div class='row'></div>
						<div id='div_isbd!!id!!' style='$display_onglet'>!!ISBD!!</div>";
					
				}elseif($id_tpl=="PUBLIC"){
					$onglets_title.="
						<li id='onglet_public!!id!!' class='$class_onglet'><a href='#' title=\"".$msg['Public_info']."\" onclick=\"show_what('PUBLIC', '!!id!!'); return false;\">".$msg['Public']."</a></li>";					
						
					$onglets_content.="	<div class='row'></div>
						<div id='div_public!!id!!' style='$display_onglet'>!!PUBLIC!!</div>";
				}elseif(is_numeric($id_tpl)){
					$notice_onglet=new notice_onglet($id_tpl);
		
					$title=$notice_onglet->get_onglet_header();
					if($title){
						$onglets_title.="
							<li id='onglet_tpl_".$id_tpl."_".$id_notice."'  class='$class_onglet'>
							<a href='#' title=\"".$title."\" onclick=\"show_what('tpl_".$id_tpl."_', '$id_notice'); return false;\">".$title."</a>
							</li>";
						
						$onglets_content.="
							<div id='div_tpl_".$id_tpl."_".$id_notice."' style='$display_onglet'>
							".$notice_onglet->get_onglet_content($id_notice)."
							</div>";
					}
				}
				
				
			}
		}
		
		return $onglets_title.$li_tags."</ul><div class='row'></div>".$onglets_content;
	}
}	 // class end

