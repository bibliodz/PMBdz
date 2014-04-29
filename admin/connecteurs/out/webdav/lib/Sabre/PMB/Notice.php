<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Notice.php,v 1.6 2013-04-25 16:02:17 mbertin Exp $
namespace Sabre\PMB;

class Notice extends Collection {
	private $notice_id;
	public $type;

	function __construct($name) {
		$this->notice_id = substr($this->get_code_from_name($name),1);
		$this->type = "notice";
	}
	

	function getChildren() {
		$children = array();
		$query = "select explnum_id from explnum where explnum_mimetype!= 'URL' and ((explnum_notice = ".$this->notice_id." and explnum_bulletin = 0) or (explnum_notice =0 and explnum_bulletin = (select bulletin_id from bulletins join notices on notice_id = num_notice where niveau_biblio = 'b' and notice_id=".$this->notice_id.")))";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$children[] = $this->getChild("(E".$row->explnum_id.")");
			}
		}
		return $children;
	}

	function getName() {
		global $charset;
		$query = "select concat(serials.tit1,' - ',notices.tit1) as title from notices join bulletins on notices.notice_id = bulletins.num_notice and notices.niveau_biblio = 'b' join notices as serials on bulletins.bulletin_notice = serials.notice_id join explnum on explnum_notice = 0 and explnum_bulletin = bulletin_id where notices.notice_id= ".$this->notice_id." and explnum_mimetype!= 'URL' union select tit1 as title from notices join explnum on explnum_bulletin = 0 and explnum_notice = notice_id where notice_id = ".$this->notice_id." and explnum_mimetype != 'URL'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$name = $row->title." (N".$this->notice_id.")";
		}	
		if($charset != "utf-8"){
			return utf8_encode($name);
		}else{
			return $name;
		}
	}
	
    public function createFile($name, $data = null) {
    	global $charset,$base_path,$id_rep;
		if($charset !=='utf-8'){
			$name=utf8_decode($name);
		}
		
		$filename = tempnam($base_path."/temp/","webdav_");
		$fp = fopen($filename, "w");
		while ($buf = fread($data, 1024)){
			fwrite($fp, $buf);
		}
		fclose($fp);
		
		$explnum = new \explnum(0,$this->notice_id);
		$id_rep = $this->parentNode->config['upload_rep'];
		$explnum->get_file_from_temp($filename,$name,$this->parentNode->config['up_place']);
		$explnum->update();
		$this->update_notice($this->notice_id);
		if(file_exists($filename)){
			unlink($filename);
		}
		
    }
}