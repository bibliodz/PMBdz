<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: storage.class.php,v 1.2 2013-07-04 14:46:17 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class storage {
	public $id = 0;
	public $class_name="";
	public $parameters = array();
	public $name ="";
	
	public function __construct($id=0){
		$this->id = ($id*1);
		$this->fetch_datas();
	}
	
	public function get_form($class){
		global $base_path,$include_path,$class_path;
		if($class == $this->class_name){
			//on a la classe d�j� d�clar�
			$obj = storages::get_storage_class($this->id);
		}else{
			require_once($class_path."/storages/".$class.".class.php");
			$obj = new $class($id);
		}
		return $obj->get_params_form();
	}
	
	protected function fetch_datas(){
		$query = "select * from storages where id_storage = ".$this->id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
			$this->name = $row->storage_name;
			$this->class_name = $row->storage_class;
			$this->parameters = unserialize($row->storage_params);
		}		
	}
	
 	public function upload_process(){
 		global $fnc;
 		$protocol = $_SERVER["SERVER_PROTOCOL"];
 		$uploadDir = "./temp/";
 		
 		switch ($fnc){
			case 'upl':
				if (is_dir($uploadDir)) {
					if (is_writable($uploadDir)) {
						return $this->get_file();
					}else{
 						header($protocol.' 405 Method Not Allowed');
 						exit('Upload directory is not writable.');
					}
				}else{
 					header($protocol.' 404 Not Found');
 					exit('Upload directory does not exist.');
				}
				break;
			case 'del':
// 				$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : null;
// 				if ($fileName) {
// 					$this->delete($fileName, $uploadDir);
// 				}else {
// 					header($protocol.' 404 Not Found');
// 					exit('No file name provided.');
// 				}
				break;
			case 'resume':
// 				$this->save($uploadDir, true);
 				break;
 			case 'getNumWrittenBytes':
// 				$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : null;
// 				if($fileName){
// 					if (file_exists($uploadDir.$fileName)) {
// 						echo json_encode(array('numWritten' => filesize($uploadDir.$fileName)));
// 					}else {
// 						header($protocol.' 404 Not Found');
// 						exit('Previous upload not found. Resume not possible.');
// 					}
// 				}else{
// 					header($protocol.' 404 Not Found');
// 					exit('No file name provided.');
// 				}
 				break;
 		}	
 	}
 	/**
 	 * @see http://ch2.php.net/manual/en/function.ini-get.php
 	 * @param  $val
 	 * @return int|string
 	 */
 	public function getBytes($val) {
 		$val = trim($val);
 		$last = strtolower($val[strlen($val) - 1]);
 		switch ($last) {
 			// The 'G' modifier is available since PHP 5.1.0
 			case 'g':
 				$val *= 1024;
 			case 'm':
 				$val *= 1024;
 			case 'k':
 				$val *= 1024;
 		}
 		return $val;
 	}
 	
 	public function getNumWrittenBytes() {
 		return $this->numWrittenBytes;
 	}
 	
 	protected function get_file(){
 		$headers = getallheaders();
 		$protocol = $_SERVER["SERVER_PROTOCOL"];
 		
 		if (!isset($headers['Content-Length'])) {
 			header($protocol.' 411 Length Required');
 			exit('Header \'Content-Length\' not set.');
 		}
 		
 		/*if (isset($headers['Content-Type'], $headers['X-File-Size'], $headers['X-File-Name']) &&
 		 ($headers['Content-Type'] === 'multipart/form-data' || $headers['Content-Type'] === 'application/octet-stream; charset=UTF-8')) {*/
 		if (isset($headers['X-File-Size'], $headers['X-File-Name'])) {
 			// Sanitize all uploaded headers before saving to disk
 			// Enable writing to disk at your own risk! Special care needs to be taken, that only the right person can
 			// save/append a file. Also the type is not checked, a user can upload anything!
 			$file = new stdClass();
 			$file->name = preg_replace('/[^ \.\w_\-]*/', '', basename($headers['X-File-Name']));
 			$file->size = preg_replace('/\D*/', '', $headers['X-File-Size']);
 		
 			// php://input bypasses the ini settings, we have to limit the file size ourselves:
 			// Find smallest init setting and set upload limit accordingly.
 			$maxUpload = $this->getBytes(ini_get('upload_max_filesize')); // can only be set in php.ini and not by ini_set()
 			$maxPost = $this->getBytes(ini_get('post_max_size'));         // can only be set in php.ini and not by ini_set()
 			$memoryLimit = $this->getBytes(ini_get('memory_limit'));
 			$limit = min($maxUpload, $maxPost, $memoryLimit);
 			if ($headers['Content-Length'] > $limit) {
 				return false;
 				header($protocol.' 403 Forbidden');
 				exit('File size to big. Limit is '.$limit. ' bytes.');
 			}
 		
 			$i=1;
 			$this->fileName = $file->name;
 			while(file_exists("./temp/".$file->name)){
 				if($i==1){
 					$file->name = substr($file->name,0,strrpos($file->name,"."))."_".$i.substr($file->name,strrpos($file->name,"."));
 				}else{
 					$file->name = substr($file->name,0,strrpos($file->name,($i-1).".")).$i.substr($file->name,strrpos($file->name,"."));
 				}
 				$i++;
 			}
 			$file->content = file_get_contents("php://input");
 		
 			// Since I don't know if the header content-length can be spoofed/is reliable, I check the file size again after it is uploaded
 			if (mb_strlen($file->content) > $limit) {
 				return false;
 				header($protocol.' 403 Forbidden');
 			}
 			$this->numWrittenBytes = file_put_contents("./temp/".$file->name, $file->content);
 			if ($this->numWrittenBytes !== false) {
 				header($protocol.' 201 Created');
 				$success = $this->add($file->name);
 				if(!$success){
  					unlink("./temp/".$file->name);
 				}
 				return $success;
 			}else {
 				return false;
 				header($protocol.' 505 Internal Server Error');
 			}
 		}else {
 			return false;
 			header($protocol.' 500 Internal Server Error');
 			$this->debug($headers);
 			exit('Correct headers are not set.');
 		}		
 	}
 	
 	public function debug($tab){
 		highlight_string(print_r($tab,true));
 	}
	
	//a surcharger
	public function get_params_form(){
		
	}
	
	//a surcharger
	public function get_params_to_save(){
	
	}	
	
	//a surcharger
	public function add($file){
	
	}
	
	//a surcharger
	public function update($new_file){
	
	}
	
	//a surcharger
	public function delete($file){
	
	}
	
	//a surcharger
	public function move($file,$dest){
	
	}
	
	//a surcharger
	public function get_uploaded_fileinfos(){
		
	}
	
	//a surcharger
	public function get_infos(){
		return "";
	}
	
	
	public function get_mimetype(){
		$finfo = new finfo(FILEINFO_MIME);
		//petit hack pour les formats exotiques(type BNF)
		$arrayMimetypess = array("application/bnf+zip");
		$arrayExtensions = array(".bnf");
		$original_extension = (false === $pos = strrpos($this->filepath, '.')) ? '' : substr($this->filepath, $pos);
		if(in_array($original_extension,$arrayExtensions)){
			for($i=0 ; $i<count($arrayExtensions) ; $i++){
				if($arrayExtensions[$i] == $original_extension){
					return $arrayMimetypess[$i];
				}
			}
		}else{
			$infos = $finfo->file($this->filepath);
			return substr($infos,0,strpos($infos,";"));
		}
	}
 }