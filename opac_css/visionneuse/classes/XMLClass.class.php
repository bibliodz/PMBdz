<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: XMLClass.class.php,v 1.3 2013-04-04 13:22:53 mbertin Exp $

class XMLClass {
	var $defaultMimetypeFile;			//xml d�crivant les classes � utilis� par d�faut
	var $defaultMimetype;				//tab r�sultant du xml par defaut	
	var $mimetypeFiles = array();		//tableau associatif des manisfest par classes d'affichage 
	var $classMimetypes = array();		//tableau associatif r�sultant des diff�rents manifest, d�crivant les mimetypes support�s par chaque classe
		
    function XMLClass($file=""){
    	$this->file = $file;   	
	}
    
 	//M�thodes
 	function defaultMimetypeParse($parser, $nom, $attributs){
		global $_starttag; $_starttag=true;
		if($nom == 'MIMETYPE' && $attributs['TYPE'] && $attributs['CLASS']){
			$this->defaultMimetype[$attributs['TYPE']] = $attributs['CLASS'];
		}
		if($nom == 'MIMETYPES'){
			$this->defaultMimetype = array();
		}
	}

	//on fait tout dans la m�thode d�butBalise....
	function finBalise($parser, $nom){//besoin de rien
	}   
	function texte($parser, $data){//la non plus
	}
	
	function analyser($file=""){
 		global $charset;
		
		if($file != "") $xmlToParse = $file;
		else $xmlToParse = $this->file;
		
		if (!($fp = @fopen($xmlToParse , "r"))) {
			die("impossible d'ouvrir le fichier $xmlToParse");
			}
		$data = fread ($fp,filesize($xmlToParse));

 		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
		if (preg_match($rx, $data, $m)) $encoding = strtoupper($m[1]);
			else $encoding = "ISO-8859-1";
		
 		$this->analyseur = xml_parser_create($encoding);
 		xml_parser_set_option($this->analyseur, XML_OPTION_TARGET_ENCODING, $charset);		
		xml_parser_set_option($this->analyseur, XML_OPTION_CASE_FOLDING, true);
		xml_set_object($this->analyseur, $this);
		xml_set_element_handler($this->analyseur, "debutBalise", "finBalise");
		xml_set_character_data_handler($this->analyseur, "texte");
	
		fclose($fp);

		if ( !xml_parse( $this->analyseur, $data, TRUE ) ) {
			die( sprintf( "erreur XML %s � la ligne: %d ( $xmlToParse )\n\n",
			xml_error_string(xml_get_error_code( $this->analyseur ) ),
			xml_get_current_line_number( $this->analyseur) ) );
		}

		xml_parser_free($this->analyseur);
 	}
}
?>