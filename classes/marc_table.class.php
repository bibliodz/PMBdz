<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: marc_table.class.php,v 1.20 2013-03-12 17:12:21 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classe de gestion des tables MARC en XML

if ( ! defined( 'MARC_TABLE_CLASS' ) ) {
  define( 'MARC_TABLE_CLASS', 1 );

// pas bon, � remonter dans les fichiers appelants
require_once("$class_path/XMLlist.class.php");

class marc_list {

// propri�t�s
	var $table;
	var $tablefav;
	var $parser;

// m�thodes

	// constructeur
	function marc_list($type) {
		global $lang;
		global $charset;
		global $include_path;
		switch($type) {
			case 'country':
				$parser = new XMLlist("$include_path/marc_tables/$lang/country.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'icondoc':
				$parser = new XMLlist("$include_path/marc_tables/icondoc.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'icondoc_big':
				$parser = new XMLlist("$include_path/marc_tables/icondoc_big.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'lang':
				$parser = new XMLlist("$include_path/marc_tables/$lang/lang.xml");
				$parser->analyser();
				$this->table = $parser->table;
				$this->tablefav = $parser->tablefav;
				break;
			case 'doctype':
				$parser = new XMLlist("$include_path/marc_tables/$lang/doctype.xml", 0);
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'recordtype':
				$parser = new XMLlist("$include_path/marc_tables/$lang/recordtype.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'function':
				$parser = new XMLlist("$include_path/marc_tables/$lang/function.xml");
				$parser->analyser();
				$this->table = $parser->table;
				$this->tablefav = $parser->tablefav;
				break;
			case 'section_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/section_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'typdoc_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/typdoc_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;			
			case 'codstatdoc_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/codstat_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;			
			case 'diacritique':
			// Armelle : a priori plus utile.
				$parser = new XMLlist("$include_path/marc_tables/diacritique.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'nivbiblio':
				$parser = new XMLlist("$include_path/marc_tables/$lang/nivbiblio.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;	
			case 'relationtypeup':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypeup.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;		
			case 'relationtypedown':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypedown.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'relationtype_aut':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtype_aut.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'relationtype_autup':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtype_autup.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'relationtypedown_unimarc':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypedown_unimarc.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'relationtypeup_unimarc':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypeup_unimarc.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'languages':
				$parser = new XMLlist("$include_path/messages/languages.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;		
			default:
				$this->table=array();
				break;
		}
	}

}

class marc_select {

// propri�t�s

	var $display;

// m�thodes

	// constructeur


	function marc_select($type, $name='mySelector', $selected='', $onchange='', $option_premier_code='', $option_premier_info='')
	{
		$source = new marc_list($type);
		$source_tab = $source->table;

		if($option_premier_code!=='' && $option_premier_info!=='') {
			$option_premier_tab = array($option_premier_code=>$option_premier_info);
			$source_tab=$option_premier_tab + $source_tab;
		}
		
		if ($onchange) $onchange=" onchange=\"$onchange\" ";
		$this->display = "<select id='$name' name='$name' $onchange >";
		
		if($selected) {
			foreach($source_tab as $value=>$libelle) {
				if(!($value == $selected))
					$tag = "<option value='$value'>";
				else
					$tag = "<option value='$value' selected='selected'>";

				$this->display .= "$tag$libelle</option>";
			}

		} else {

			// cirque � cause d'un bug d'IE
			reset($source_tab);
			$this->display .= "<option value='".key($source_tab)."' selected='selected'>";
			$this->display .= pos($source_tab).'</option>';

			while(next($source_tab)) {
				$this->display .= "<option value='".key($source_tab)."'>";
				$this->display .= pos($source_tab).'</option>';
			}

		}
		$this->display .= "</select>";

	}
}

} # fin de d�claration
