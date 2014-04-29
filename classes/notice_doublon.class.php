<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_doublon.class.php,v 1.8 2014-03-12 12:46:55 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/parser.inc.php");
require_once($class_path."/parametres_perso.class.php");

class notice_doublon {
	var $external = false;		//bool�en qui d�termine si l'on est en recherche externe ou non...
	var $signature = '';
	var $duplicate = object;
	
	// constructeur
	function notice_doublon($external = false,$source_id=0) {
		global $include_path;
		global $msg;
		
		$this->source_id = $source_id;
		$this->external= $external; 	
		// lecture des fonctions de pi�ges � ex�cuter pour faire un pret
		$this->parse_xml_fields($include_path."/notice/notice.xml");		
	}

	function parse_xml_fields($filename) {
		global $msg;
		$f_pos=strrpos($filename,'.');
		$f_end=substr($filename,$f_pos);
		$f_deb=substr($filename,0,$f_pos);
		if (file_exists($f_deb."_subst".$f_end)) $filename=$f_deb."_subst".$f_end;
		$fp=fopen($filename,"r") or die("Can't find XML file");
		$xml=fread($fp,filesize($filename));
		fclose($fp);
		$param=_parser_text_no_function_($xml, "FIELDS");
		
		for($i=0; $i<count($param['FIELD']); $i++) {
			
			$name=$param['FIELD'][$i]['NAME'];	
			$this->fields[$name]['name'] = $param['FIELD'][$i]['NAME'];;
			$this->fields[$name]['size_max'] = $param['FIELD'][$i]['SIZE_MAX'];
			$this->fields[$name]['html'] = $param['FIELD'][$i]['HTML'][0]['value'];
			$this->fields[$name]['html_ext'] = $param['FIELD'][$i]['HTML_EXT'][0]['value'];
			$this->fields[$name]['sql'] = $param['FIELD'][$i]['SQL'][0]['value'];
			$this->fields[$name]['sql_ext']= $param['FIELD'][$i]['SQL_EXT'][0]['value'];
			$label = $param['FIELD'][$i]['LABEL'];
			if(stripos($label,'msg:')===0 ) {
				$label = $msg[substr($label,4)];
			}
			$this->fields[$name]['label']= $label;
		}
		return 0;
	}
	
	function read_field_form($field) {
		if($this->external) $html=$this->fields[$field]["html_ext"];
		else $html=$this->fields[$field]["html"];
		$size_max=	$this->fields[$field]["size_max"];
		
		if(!$html) {
			// c'est surement un param perso
			$p_perso=new parametres_perso("notices");
			$chaine=$p_perso->read_form_fields_perso($field); 			
			return $chaine;
		} else  {
			for($i=0;$i<$size_max;$i++) {
				$chaine.=stripslashes($GLOBALS[$html]);
				// incr�ment du name de l'objet dans le formulaire
				$html++;				
			}	
			return $chaine;
		}	
	}
	
	function read_field_database($field,$id) {
		global $dbh;
		if($this->external) $rqt = $this->fields[$field]["sql_ext"];	
		else $rqt=$this->fields[$field]["sql"];	
 		if(!$rqt) {			
			// c'est surement un param perso
			$p_perso=new parametres_perso("notices");
			$chaine=$p_perso->read_base_fields_perso($field,$id); 		
			return '';	
		} else {
			$rqt=str_replace('!!id!!',$id,$rqt);
			if($this->external) $rqt=str_replace('!!source_id!!',$this->source_id,$rqt);	
			$result = mysql_query($rqt, $dbh);			
			if (($row = mysql_fetch_row($result) ) ) {
	        	return $row[0];
			} else {
				// rien
				return '';		
			}	
 		}	
	}
	
	function gen_signature($id=0) {
		global $dbh;
		global $msg;
		global $pmb_notice_controle_doublons;

		$field_list=explode(',',str_replace(' ','',$pmb_notice_controle_doublons));
				
		// Pas de control activ� en param�trage: Sortir.
		if( ($metod = $field_list[0]) < 1 ) return 0;
		foreach($field_list as  $i => $field) {
			if ($i>0){	
				if (!$id) {
					// le formulaire � lire
					$chaine.= $this->read_field_form($field);
				} else {
					// la base � lire
					$chaine.= $this->read_field_database($field,$id);
				}	
			}	
		}		
		// encodage signature par SOUNDEX (option 2) et par md5 (32 caract�res)
		if($metod == 2) {	
			$rqt = "SELECT SOUNDEX('".addslashes($chaine)."')";
			$result = mysql_query($rqt, $dbh);				
			if (($row = mysql_fetch_row($result) ) ) {
	        	$chaine = $row[0];
			}					
		}		
		$this->signature = md5($chaine);	
		return $this->signature;
	}			
	
	function getDuplicate() {
		
		global $dbh;
		$q = "select signature, niveau_biblio ,niveau_hierar ,notice_id from notices where signature='".$this->signature."' limit 1";
		$r = mysql_query($q, $dbh);
		if (mysql_num_rows($r)) {
			$this->duplicate= mysql_fetch_object($r);
		}		
		return $this->duplicate;
	}

// Fin class notice_doublon		
}

?>