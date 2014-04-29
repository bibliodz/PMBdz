<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bookreaderPDF.class.php,v 1.13 2013-07-24 13:09:22 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($visionneuse_path."/classes/mimetypes/bookreader/PDFMetadata.class.php");

class bookreaderPDF {
	var $doc;			//le document PDF à traiter
	var $parameters;	//tableau décrivant les paramètres de la classe
	var $PDFMetadata;
	var $pagesSizes;
	
	function bookreaderPDF($doc,$parameters){
		$this->doc = $doc;
		$this->parameters = $parameters;
		$this->PDFMetadata = new PDFMetadata($this->doc->driver->get_cached_filename($this->doc->id));
		$this->getPagesSizes();
	}
	
	function getPage($page){
		
		$format = $this->parameters['format_image'];
		
		switch ($format) {
			case "imagick":
			case "png":
				$extension = "png";
				$content_type = "image/x-png";
				break;
			case "jpeg":
				$extension = "jpg";
				$content_type = "image/jpeg";
				break;
		}
		
		$len = strlen($this->getPageCount());
		if (!file_exists($this->doc->driver->get_cached_filename($this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".".$extension)) {
			$resolution = $this->parameters['resolution_image'];
			if ($format == "imagick") {
				exec("pdftoppm -f $page -l $page -r ".$resolution." ".$this->doc->driver->get_cached_filename($this->doc->id)." ".$this->doc->driver->get_cached_filename("page_".$this->doc->id));
				$imagick = new Imagick();
				$imagick->setResolution($resolution,$resolution);
				$imagick->readImage($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len, "0", STR_PAD_LEFT).".ppm");
				$imagick->writeImage($$this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".png");
				unlink($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len, "0", STR_PAD_LEFT).".ppm");
			} else {
				exec("pdftoppm -f $page -l $page -r ".$resolution." -".$format." ".$this->doc->driver->get_cached_filename($this->doc->id)." ".$this->doc->driver->get_cached_filename("page_".$this->doc->id));
			}
		}
		if (file_exists($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".".$extension)) {
			header("Content-Type: ".$content_type);
			print file_get_contents($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".".$extension);
		}
	}
	
	function getWidth($page){
		return $this->PDFMetadata->pagesSizes[$page]['width']*72/$this->parameters['resolution_image'];
	}
	
	function getHeight($page){
		return $this->PDFMetadata->pagesSizes[$page]['height']*72/$this->parameters['resolution_image'];
	}
	
	function getPagesSizes(){
		$this->pagesSizes= array();
		foreach($this->PDFMetadata->pagesSizes as $page => $size){
			$this->pagesSizes[$page] = array(
				'width' => $size['width']*72/$this->parameters['resolution_image'],
				'height' => $size['height']*72/$this->parameters['resolution_image']
			);
		}
	}
	
	function search($user_query){
		global $charset;
		
		$matches = array();
		
		if (!file_exists($this->doc->driver->get_cached_filename($this->doc->id).".bbox")){
			exec("pdftotext -bbox ".$this->doc->driver->get_cached_filename($this->doc->id)." ".$this->doc->driver->get_cached_filename($this->doc->id).".bbox");
		}
		ini_set("zend.ze1_compatibility_mode", "0");
		$dom = new DOMDocument();
		$dom->load($this->doc->driver->get_cached_filename($this->doc->id).".bbox");
		
		$terms = explode(" ",strtolower(convert_diacrit($user_query)));
		
		$pages = $dom->getElementsByTagName("page");
		$height = 0;
		$width = 0;
		
		//on parcourt les pages
		for($i=0 ; $i<$pages->length ; $i++){
			$current_page = $pages->item($i);
			$height = $current_page->getAttribute("height");
			$width = $current_page->getAttribute("width");
			
			$h_ratio = $this->getHeight($i+1)/$height;
			$w_ratio = $this->getWidth($i+1)/$width;

			$words = $current_page->getElementsByTagName("word");
			//on parcourt les mots du fichier
			for($j=0 ; $j<$words->length ; $j++){
				//on parcourt les termes de la recherche
				$current_word = $words->item($j);
				foreach($terms as $term){
					if(($pos = strpos(strtolower(convert_diacrit($current_word->nodeValue)),$term)) !== false){
						//trouvé
						//texte à afficher en aperçu
						$text = "...";
						for ($k=$j-3 ; $k<=$j+3 ; $k++){
							if ($charset == "iso-8859-1") {
								if ($j == $k) $text .= "<span style='background-color:#CCCCFF;font-size:100%;font-style:normal;color:#000000;'>".htmlentities(iconv("UTF-8", "ISO-8859-1//TRANSLIT",$words->item($k)->nodeValue),ENT_QUOTES,$charset)."</span> ";
								else $text .= htmlentities(iconv("UTF-8", "ISO-8859-1//TRANSLIT",$words->item($k)->nodeValue),ENT_QUOTES,$charset)." ";
							} else {
								if ($j == $k) $text .= "<span style='background-color:#CCCCFF;font-size:100%;font-style:normal;color:#000000;'>".htmlentities($words->item($k)->nodeValue,ENT_QUOTES,$charset)."</span> ";
								else $text .= htmlentities($words->item($k)->nodeValue,ENT_QUOTES,$charset)." ";
							}
						}
						$text .= "... ";
						
						$matches[] = array(
							"text"=> $text,
							'par' => array(
								array(
									'page' => ($i+1),
									'page_height' => $height,
									'b' => $height,
									't' => 0,
									'page_width' => $width,
									'r' => $width,
									'l' =>  0,
									'boxes' => array(
										array(
											'l' => $current_word->getAttribute("xMin")*$w_ratio,
											'r' => $current_word->getAttribute("xMax")*$w_ratio,
											'b' => $current_word->getAttribute("yMax")*$h_ratio,
											't' => $current_word->getAttribute("yMin")*$h_ratio,
											'page' => ($i+1)
										)
									)
								)
							)
						);
					} else {
						//perdu
						continue;
					}
				}
			}
		}
		return array('matches' => $matches);
	}
	
	function getBookmarks(){
		return $this->PDFMetadata->getBookmarks();
	}
	
	function getPDF(){
		
	}
	
	function getPageCount(){
		return $this->PDFMetadata->nb_pages;
	}
}

?>