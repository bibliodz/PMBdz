<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Explnum.php,v 1.5 2013-04-25 16:02:17 mbertin Exp $
namespace Sabre\PMB;

use Sabre\DAV;
use Sabre\PMB;

class Explnum extends PMB\File {
	private $explnum_id;
	private $name;

	function __construct($name) {
		$this->explnum_id = substr($this->get_code_from_name($name),1);
	}
	
	function getName() {
		global $charset;
		$query = "select explnum_nom, explnum_extfichier from explnum where explnum_id = ".$this->explnum_id;
		$result = mysql_query($query);
		$name = "";
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$name = $row->explnum_nom;
			if(strpos(strtolower($row->explnum_nom),".".str_replace(".","",$row->explnum_extfichier))!==false){
				$name = substr($row->explnum_nom,0,strrpos($row->explnum_nom,"."));
			}
			$name.= " (E".$this->explnum_id.").".str_replace(".","",$row->explnum_extfichier);
		}
		if($charset != "utf-8"){
			return utf8_encode($name);
		}else{
			return $name;
		}
	}

	function get() {
		$explnum = new \explnum($this->explnum_id);
		return $explnum->get_file_content();
	}
	
	function getSize() {
		return strlen($this->get());
	}
	
	function getContentType(){
		$mimetype= "";
		$query = "select explnum_mimetype from explnum where explnum_id = ".$this->explnum_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$mimetype = mysql_result($result,0,0);
		}
		return $mimetype;
	}
	
	function getETag() {
		if(file_exists($this->explnum_id)){
			return '"' . md5_file($this->explnum_id) . '"';
		}else{
			return '"' . md5($this->explnum_id) . '"';
		}
	}
	
	function put($data){
		global $base_path;
		global $id_rep;
		if($this->check_write_permission()){
			$filename = tempnam($base_path."/temp/","webdav_");
			$fp = fopen($filename, "w");
			while ($buf = fread($data, 1024)){
				fwrite($fp, $buf);
			}
			$explnum = new \explnum($this->explnum_id);
			fclose($fp);
			$id_rep = $this->config['upload_rep'];
			$explnum->get_file_from_temp($filename,$explnum->explnum_nomfichier,$this->config['up_place']);	
			$explnum->update();
			unlink($filename);
		}else{
			//on a pas le droit d'�criture 
			throw new DAV\Exception\Forbidden('Permission denied to modify file (filename ' . $this->getName() . ')');
		}
	} 
	
	function delete(){
		$explnum = new \explnum($this->explnum_id);
		$explnum->delete();
	}
}