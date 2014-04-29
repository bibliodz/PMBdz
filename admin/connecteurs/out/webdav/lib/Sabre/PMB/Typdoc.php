<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Typdoc.php,v 1.2 2013-04-25 16:02:17 mbertin Exp $

namespace Sabre\PMB;

class Typdoc extends Collection {
	protected $typdoc = "";
	protected $name;

	function __construct($name,$config) {
		parent::__construct($config);
		global $tdoc;
		if (!sizeof($tdoc)) $tdoc = new \marc_list('doctype');
		$name = str_replace(" (T)","",$name);
		foreach($tdoc->table as $key => $label){
			if($name == $label){
				$this->typdoc = $key;
				break;		
			}
		}
		$this->type = "typdoc";
	}

	function getName() {
		global $tdoc,$charset;
		if (!sizeof($tdoc)) $tdoc = new \marc_list('doctype');
		if($charset != "utf-8"){
			return utf8_encode($tdoc->table[$this->typdoc]." (T)");
		}else{
			return $tdoc->table[$this->typdoc]." (T)";
		}
	}
	
	function getNotices(){
		$this->notices = array();
		if(!count($this->notices)){
			if($this->typdoc){
				//notice
				$query = "select notice_id from notices join explnum on explnum_notice = notice_id and explnum_bulletin = 0 where typdoc = '".$this->typdoc."' and explnum_mimetype != 'URL'";
				//notice de bulletin
				$query.= " union select notice_id from notices join bulletins on num_notice != 0 and num_notice = notice_id join explnum on explnum_notice = 0 and explnum_bulletin = bulletin_id where typdoc = '".$this->typdoc."' and explnum_mimetype != 'URL'";
				$this->filterNotices($query);		
			}
		}
		return $this->notices;
	}
	
	function update_notice_infos($notice_id){
		if($notice_id*1 >0){
			$query = "update notices set typdoc = '".$this->typdoc."' where notice_id = ".$notice_id;
			mysql_query($query);
		}
	}
}