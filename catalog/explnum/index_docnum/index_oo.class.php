<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index_oo.class.php,v 1.3 2012-03-23 14:10:19 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/zip.class.php");

/**
 * Classe qui permet la gestion de l'indexation des fichiers OpenOffice
 */
class index_oo{
	
	var $fichier='';
	function index_oo($filename, $mimetype='', $extension=''){
		$this->fichier = $filename;
	}
	
	/**
	 * M�thode qui retourne le texte � indexer des docs OpenOffice
	 */
	function get_text($filename){
		global $charset;
		$zip = new zip($filename);
		$texte = $zip->getFileContent("content.xml");			
		//On enl�ve toute les balises offices
		preg_match_all("(<([^<>]*)>)",$texte,$result);	
		for($i=0;$i<sizeof($result[0]);$i++){
			$texte = str_replace($result[0][$i]," ",$texte);
		}
		
		$texte = str_replace("&apos;","'",$texte);
		$texte = str_replace("&nbsp;"," ",$texte);
		if($charset != "utf-8"){
			$texte =  utf8_decode($texte);		
		}
		$texte = html_entity_decode($texte,ENT_QUOTES,$charset);
		return $texte;
		
	}
}
?>