<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index_html.class.php,v 1.2 2012-03-23 14:10:19 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/**
 * Classe qui permet la gestion de l'indexation des fichiers HTML
 */
class index_html{
	
	var $fichier='';
	
	/**
	 * Constructeur
	 */
	function index_html($filename, $mimetype='', $extension=''){
		$this->fichier = $filename;
	}
	
	/**
	 * R�cup�ration du texte � indexer dans le fichier HTML
	 */
	function get_text($filename){
		
		$fp = fopen($filename, "r");
		while(!feof($fp)){
			$line = fgets($fp,4096); 
			$texte .= $line;
		}
		fclose($fp);
		
		//Traitement du texte 
		$result = array();
		$result_style = array();
		$texte = str_replace("\n","",$texte);
		$texte = str_replace("\r","",$texte);
		//On enl�ve les htmlentities
		$texte = html_entity_decode($texte);
		//On enl�ve les balises <script> et <style>
		preg_match_all("(<script.*?>.*?</script>)",$texte,$result);	
		preg_match_all("(<style.*?>.*?</style>)",$texte,$result_style);	
		for($i=0;$i<sizeof($result[0]);$i++){
			$texte = str_replace($result[0][$i],"",$texte);
		}
		for($i=0;$i<sizeof($result_style[0]);$i++){
			$texte = str_replace($result_style[0][$i],"",$texte);
		}
		//On enl�ve les tags
		$texte_final = strip_tags($texte);

		return $texte_final;
	}
}
?>