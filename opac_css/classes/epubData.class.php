<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: epubData.class.php,v 1.2 2013-02-22 16:06:33 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class epubData {
	
	var $filename = ''; //Fichier source de l'eBook
	var $opfFile = ''; //Fichier d'entr�e de l'eBook
	var $opfDir = ''; //R�pertoire d'entr�e de l'eBook
	var $metas = array(); //Tableau des m�tadatas de l'eBook
	var $items = array(); //Liste des fichiers composant l'eBook
	var $spine = array(); //Ordre d'affichage des fichiers
	var $spineToc = ''; //Fichier table des mati�res
	var $spinePageMap = ''; //Fichier liste des pages
	var $pages = array(); //Liste des pages
	var $toc = array(); //Table des mati�res
	var $charset = ''; //Charset de l'epub
	
	//Constructeur
	public function epubData($filename){
		if (is_file($filename)){
			$this->filename = $filename;
			if ($this->isValidEpub()) {
				$this->fetchMetadatas();
				$this->fetchItems();
				$this->fetchSpine();
				$this->fetchPages();
				$this->fetchToc();
			} else {
				print ("Fichier eBook non valide : ".$this->filename.".\n");
			}
		} else {
			print ("Fichier non trouv� : '".$filename."'.\n");
		}
	}
	
	//R�cup�ration du contenu texte en vue d'indexation de l'eBook
	public function getFullTextContent($otherCharset='utf-8') {
		$chaineRetour = '';
		foreach ($this->spine as $spinId) {
			if ($this->items[$spinId]) {
				$contents = html_entity_decode($this->getContentFile($this->opfDir.$this->items[$spinId]["href"]));
			}			
			$chaineRetour .= strip_tags($contents);			
		}
		if ($otherCharset != "utf-8") {
			$chaineRetour = utf8_decode($chaineRetour);
		}
		return $chaineRetour;
	}
	
	//R�cup�ration du contenu d'une page
	public function getPageContent($page) {
		$chaineRetour = $this->getContentFile($this->opfDir.$page);
		if (!preg_match('`meta charset`',$chaineRetour)) {
			$chaineRetour = str_replace("<head>","<head><meta charset=\"UTF-8\">",$chaineRetour);
		}
		return $chaineRetour;
	}
	
	private function isValidEpub() {
		$isValid = true;
		//On v�rifie le fichier "mimetype" et son contenu
		$mime = $this->getContentFile("mimetype");
		if (!preg_match('(application\/epub\+zip)', $mime)) {
			$isValid = false;
		}
		return $isValid;
	}
	
	//R�cup�ration des m�tadatas
	private function fetchMetadatas() {
		//On ouvre le container.xml
		$contents = $this->getContentFile("META-INF/container.xml");
		if (trim($contents)) {
			$xml = simplexml_load_string($contents);
			//On va chercher le fichier opf
			$tmpFile = (string)$xml->rootfiles->rootfile->attributes()->{'full-path'};
			$tmpArray = explode("/",$tmpFile);
			$this->opfFile =array_pop($tmpArray);
			$this->opfDir = implode("/",$tmpArray).(implode("/",$tmpArray)?"/":"");
			$contents = $this->getContentFile($this->opfDir.$this->opfFile);
			//On cherche le charset
			$this->charset = strtolower(mb_detect_encoding($contents));
			$xml = simplexml_load_string($contents);		
			//on d�clare les namespaces
			$namespaces = $xml->getNamespaces(true);
			foreach ($namespaces as $k=>$v) {
				if (trim($k)) {
					define(strtoupper($k), $v);
				}
			}
			//on va chercher les metas
			$xmlMeta = $xml->children(OPF, false)->metadata->children(DC, false);
			foreach ($xmlMeta as $k=>$v) {
				$this->metas[$this->decodeCharset($k)] = $this->decodeCharset($v);
			}
		}
	}
	
	//R�cup�ration des items de l'eBook
	private function fetchItems() {
		$contents = $this->getContentFile($this->opfDir.$this->opfFile);
		if (trim($contents)) {
			$xml = simplexml_load_string($contents);
			foreach ($xml->manifest->item as $item) {
				$this->items[(string)$item->attributes()->{'id'}]["href"] =  (string)$item->attributes()->{'href'};
				$this->items[(string)$item->attributes()->{'id'}]["media-type"] =  (string)$item->attributes()->{'media-type'};
			}
		}
	}
	
	//R�cup�ration de l'ordre d'affichage des fichiers
	private function fetchSpine() {
		$contents = $this->getContentFile($this->opfDir.$this->opfFile);
		if (trim($contents)) {
			$xml = simplexml_load_string($contents);
			$this->spineToc = $this->decodeCharset($xml->spine->attributes()->{'toc'});
			$this->spinePageMap = $this->decodeCharset($xml->spine->attributes()->{'page-map'});
			foreach ($xml->spine->itemref as $item) {
				$this->spine[] =  (string)$item->attributes()->{'idref'};
			}
		}
	}
	
	//R�cup�ration des pages de l'eBook
	private function fetchPages() {
		$contents = $this->getContentFile($this->opfDir.$this->items[$this->spinePageMap]["href"]);
		if (trim($contents)) {
			$xml = simplexml_load_string($contents);
			foreach ($xml->page as $item) {
				$tmpArray = array();
				$tmpArray['name'] = $this->decodeCharset($item->attributes()->{'name'});
				$tmpArray['href'] = $this->decodeCharset($item->attributes()->{'href'});
				$this->pages[] = $tmpArray;
			}
		} else {
			//Parfois nous n'avons pas de liste des pages
			foreach ($this->items as $item) {
				if ($item["media-type"]=='application/xhtml+xml') {
					$tmpArray = array();
					$tmpArray['name'] = $item["href"];
					$tmpArray['href'] = $item["href"];
					$this->pages[] = $tmpArray;
				}
			}
		}
	}
	
	//R�cup�ration de la table des mati�res
	private function fetchToc() {
		$contents = $this->getContentFile($this->opfDir.$this->items[$this->spineToc]["href"]);
			if (trim($contents)) {
			$xml = simplexml_load_string($contents);
			foreach ($xml->navMap->navPoint as $item) {
				$this->readNavPoint($item,0);
			}
		}
	}
	
	//m�thode pour retrouver de fa�on r�currente les points de navigation
	private function readNavPoint($simpleXmlObject,$level){
		$tmpArray = array();
		$tmpArray['playOrder'] = $this->decodeCharset($simpleXmlObject->attributes()->{'playOrder'});
		$tmpArray['text'] = $this->decodeCharset($simpleXmlObject->navLabel->text);
		$tmpArray['content'] = (string)$simpleXmlObject->content->attributes()->{'src'};
		$tmpArray['level'] = $level;
		$this->toc[] = $tmpArray;
		$level++;
		foreach ($simpleXmlObject->navPoint as $item) {
			$this->readNavPoint($item,$level);
		}
	}
	
	//On va chercher le contenu d'un fichier de l'archive eBook
	private function getContentFile($file){
		//On essaye de gagner du temps pour l'affichage dans la visionneuse
		session_write_close();
		$zip = zip_open($this->filename);
		$contents = "";
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				if(zip_entry_name($zip_entry) == $file && zip_entry_open($zip, $zip_entry))	{
					$contents = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					zip_entry_close($zip_entry);
				}
			}
			zip_close($zip);
			return $contents;
		}
	}
	
	//Fonction de d�codage selon l'environnement
	private function decodeCharset($string) {	
		$string = htmlentities($string,ENT_QUOTES,$this->charset);
		return $string;
	}
}
?>