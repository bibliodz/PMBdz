<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index_pdf.class.php,v 1.4 2012-03-23 14:10:19 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/**
 * Classe qui permet la gestion de l'indexation des fichiers PDF
 */
class index_pdf{
	
	var $fichier='';
	
	function index_pdf($filename, $mimetype='', $extension=''){
		$this->fichier = $filename;
	}
	
	/**
	 * M�thode qui retourne le texte � indexer des pdf
	 */
	function get_text($filename){
		global $charset;
		
		$fp = popen("pdftotext -enc UTF-8 ".$filename." -", "r");
		while(!feof($fp)){
			$line = fgets($fp,4096); 
			$texte .= $line;
			// Si trop gros, il faudra faire ceci, ou pas:
			// if(strlen($texte)>65536) break;
		}
		pclose($fp);
	
		if($charset != "utf-8"){
			return utf8_decode($texte);
		}
		return $texte;
	}
}
?>