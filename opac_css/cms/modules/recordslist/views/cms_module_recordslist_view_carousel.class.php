<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_recordslist_view_carousel.class.php,v 1.9 2013-10-29 11:09:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_recordslist_view_carousel extends cms_module_carousel_view_carousel{
	
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_recordslist_view_link'>".$this->format_text($this->msg['cms_module_recordslist_view_link'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.= $this->get_constructor_link_form("notice");
		$form.="
			</div>
		</div>";
		$form.= parent::get_form();
		return $form;
	}
	
	public function save_form(){
		$this->save_constructor_link_form("notice");
		return parent::save_form();
	}
	
	public function render($records){
		$datas = array();
		global $opac_url_base;
		global $opac_show_book_pics;
		global $opac_book_pics_url;
		global $opac_notice_affichage_class;
		
		if(!$opac_notice_affichage_class){
			$opac_notice_affichage_class ="notice_affichage";
		}
		if(is_array($records['records']) && count($records['records'])){
			$query = "select notice_id,tit1,thumbnail_url,code from notices where notice_id in (".implode(",",$records['records']).") order by field( notice_id, ".implode(",",$records['records']).")";
			$result = mysql_query($query);
			if($result && mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					if ($opac_show_book_pics=='1' && ($opac_book_pics_url || $row->thumbnail_url)) {
						$code_chiffre = pmb_preg_replace('/-|\.| /', '', $row->code);
						$url_image = $opac_book_pics_url ;
						$url_image = $opac_url_base."getimage.php?url_image=".urlencode($url_image)."&noticecode=!!noticecode!!&vigurl=".urlencode($row->thumbnail_url) ;
						if ($row->thumbnail_url){
						$url_vign=$row->thumbnail_url;	
						}else if($code_chiffre){
							$url_vign = str_replace("!!noticecode!!", $code_chiffre, $url_image) ;
						}else {
							$url_vign = $opac_url_base."images/vide.png";			
						}
					}
					if($this->parameters['used_template']){
						$tpl = new notice_tpl_gen($this->parameters['used_template']);
						$content = $tpl->build_notice($row->notice_id);
					}else{
						$notice_class = new $opac_notice_affichage_class($row->notice_id,"");
						$notice_class->do_isbd();
						$content = $notice_class->notice_isbd;
					}
					
					
					$datas[] = array(
						'id' => $row->notice_id,
						'title' => $row->tit1,
						'link' => $this->get_constructed_link("notice",$row->notice_id),
						'vign' => $url_vign,
						'content' => $content
					);
					
				}
			}
		}
		$datas = array(
			'title' => $records['title'],
			'records' => $datas
		);
		return parent::render($datas);
	}
}