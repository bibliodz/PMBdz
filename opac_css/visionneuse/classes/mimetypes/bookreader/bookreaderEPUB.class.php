<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bookreaderEPUB.class.php,v 1.6 2013-07-24 13:09:22 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($visionneuse_path."/../classes/epubData.class.php");
require_once($visionneuse_path."/classes/mimetypes/bookreader/PDFMetadata.class.php");

class bookreaderEPUB {
	var $doc;			//le document EPUB � traiter
	var $parameters;	//tableau d�crivant les param�tres de la classe
	var $ebook;			//l'objet ebook
	var $html_ordered;	//tableau des chemins vers les fichiers html de l'ebook dans l'ordre
	var $PDFMetadata;
	var $pagesSizes;
	
	function bookreaderEPUB($doc,$parameters){
		$this->doc = $doc;
		$this->parameters = $parameters;
    	$this->ebook = new epubData($this->doc->driver->get_cached_filename($this->doc->id));
    	$this->PDFMetadata = new PDFMetadata($this->generatePDF());
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
		if (!file_exists($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".".$extension)) {
			$resolution = $this->parameters['resolution_image'];
			if ($format == "imagick") {
				exec("pdftoppm -f $page -l $page -r ".$resolution." ".$this->doc->driver->get_cached_filename($this->doc->id).".pdf ".$this->doc->driver->get_cached_filename("page_".$this->doc->id));
				print "pdftoppm -f $page -l $page -r ".$resolution." ".$this->doc->driver->get_cached_filename($this->doc->id).".pdf ".$this->doc->driver->get_cached_filename("page_".$this->doc->id);
				$imagick = new Imagick();
				$imagick->setResolution($resolution,$resolution);
				$imagick->readImage($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len, "0", STR_PAD_LEFT).".ppm");
				$imagick->writeImage($this->doc->driver->get_cached_filename("page_".$this->doc->id)."-".str_pad($page, $len,"0",STR_PAD_LEFT).".png");
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
			exec("pdftotext -bbox ".$this->doc->driver->get_cached_filename($this->doc->id).".pdf ".$this->doc->driver->get_cached_filename($this->doc->id).".bbox");
			//bbox ne g�re pas les entit�s html pr�sentent dans le titre
			$contents = file_get_contents($this->doc->driver->get_cached_filename($this->doc->id).".bbox");
			if ((preg_match("/\<title\>(.*)\<\/title\>/", $contents, $match)) && ($match[1])) {
				file_put_contents($this->doc->driver->get_cached_filename($this->doc->id).".bbox", str_replace($match[1], htmlentities($match[1], ENT_QUOTES, $charset), $contents));
			}
		}

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
						//trouv�
						//texte � afficher en aper�u
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
		global $charset;
		
		$bookmarks = array();
		$toc = new DOMDocument();
		$toc->load($this->doc->driver->get_cached_filename($this->doc->id)."_toc.xml");
		
		$items = $toc->getElementsByTagName("item");
		for ($i = 0; $i < $items->length; $i++) {
			$current_item = $items->item($i);
			if (($current_item->parentNode->nodeName) != "outline") {
				//Calcul de la profondeur du bookmark
				$deep = 0;
				$parent = $current_item->parentNode;
				while ($parent->nodeName == "item") {
					$deep++;
					$parent = $parent->parentNode;
				}
				if ($deep < 2) {
					//R�cup�ration de la page du bookmark
					$page = $current_item->getAttribute("page")*1;
					//R�cup�ration du titre du bookmark
					if ($charset == "iso-8859-1") {
						$title = htmlentities(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $current_item->getAttribute("title")), ENT_QUOTES, $charset);
					} else {
						$title = htmlentities($current_item->getAttribute("title"), ENT_QUOTES, $charset);
					}
					$new = true;
					for ($j = 0; $j < count($bookmarks); $j++) {
						$current_bookmark = $bookmarks[$j];
						//Si un bookmark existe d�j� � la m�me page et m�me profondeur, on n'en cr�e pas un nouveau, on concat�ne le titre
						if (($current_bookmark["page"] == $page) && ($current_bookmark["deep"] == $deep)) {
							$bookmarks[$j]["label"] .= "&nbsp;|&nbsp;".$title;
							$new = false;
							break;
						}
					}
					if ($new) {
						$bookmarks[] = array(
								"label" => $title,
								"deep" => $deep,
								"page" => $page,
								"analysis_page" => $page,
								);
					}
				}
			}
		}
		return $bookmarks;
	}
	
	function getPDF($pdfParams){
		$file = $this->generatePDF();
		if (file_exists($file)){
		    header('Content-Type: application/pdf');
		    header('Content-Disposition: attachment; filename=' . str_replace(" ","_",basename(utf8_decode($pdfParams["outname"]))));
			readfile($file);
			exit;
		} else {
			print "Le PDF n'a pas �t� g�n�r� correctement.";
		}
	}
	
	function generatePDF(){
		global $charset;
		
		if (!file_exists($this->doc->driver->get_cached_filename($this->doc->id).".pdf")){
			$zip = new ZipArchive();
			$res = $zip->open($this->doc->driver->get_cached_filename($this->doc->id));
			
			if ($res === true) {
				if (!is_dir($this->doc->driver->get_cached_filename($this->doc->id)."_unzip")) mkdir($this->doc->driver->get_cached_filename($this->doc->id)."_unzip");
				$zip->extractTo($this->doc->driver->get_cached_filename($this->doc->id)."_unzip");
				$zip->close();
				$tab_html_docs = array();
				
				//R�solution des probl�mes de compatibilit� de wkhtmltopdf :
				//- On supprime les arobases pour contourner les @font-face
				//- On espace les % pour les styles d'image
				$items = $this->ebook->items;
				$opfdir = $this->ebook->opfDir;
				$search = array("@font-face", "%");
				$replace = array("font-face", " %");
				foreach ($items as $file) {
					if ($file["media-type"] == "text/css") {
						$file_path = $this->doc->driver->get_cached_filename($this->doc->id)."_unzip/".$opfdir.$file["href"];
						file_put_contents($file_path, str_replace($search, $replace, file_get_contents($file_path)));
					}
				}
				
				//- On espace les % pour les styles d'image
				//- On antislashe les espace dans les noms de fichiers pour compatibilit� en ligne de commande
				foreach ($this->getHtmlOrdered() as $file) {
					$file_path = $this->doc->driver->get_cached_filename($this->doc->id)."_unzip/".$file;
					file_put_contents($file_path, str_replace("%", " %", file_get_contents($file_path)));
					$tab_html_docs[] = str_replace(" ", "\ ", $file_path);
				}
				$list_html_docs = implode(" ", $tab_html_docs);
				if ($this->doc->titre) {
					if ($charset != "utf-8") $titre = utf8_encode($this->doc->titre);
					else $titre = $this->doc->titre;
				} else {
					$titre = $this->doc->id;
				}
				exec("wkhtmltopdf --title ".str_replace(" ", "\ ", $titre)." --encoding windows-1250 --dump-outline ".$this->doc->driver->get_cached_filename($this->doc->id)."_toc.xml --footer-center [page] cover ".$list_html_docs." ".$this->doc->driver->get_cached_filename($this->doc->id).".pdf");
				print("wkhtmltopdf --title ".str_replace(" ", "\ ", $titre)." --dump-outline ".$this->doc->driver->get_cached_filename($this->doc->id)."_toc.xml --footer-center [page] cover ".$list_html_docs." ".$this->doc->driver->get_cached_filename($this->doc->id).".pdf");
				
// 				$this->rrmdir($this->doc->driver->get_cached_filename($this->doc->id)."_unzip");
			} else {
				print "Erreur � l'ouverture de l'ebook!";
			}
		}
		return $this->doc->driver->get_cached_filename($this->doc->id).".pdf";
	}
	
	function getPageCount(){
		$page_count = $this->PDFMetadata->nb_pages;
		return $page_count;
	}
	
	function getHtmlOrdered(){
		if (!$this->html_ordered) {
			$this->html_ordered = array();
			
			$spine = array();
			$spine = $this->ebook->spine;
			$items = array();
			$items = $this->ebook->items;
			$opfdir = $this->ebook->opfDir;
			
			for ($i = 0; $i < count($spine); $i++) {
				$this->html_ordered[] = $opfdir.$items[$spine[$i]]["href"];
			}
		}
		return $this->html_ordered;
	}
	
	function rrmdir($dir){
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
}

?>