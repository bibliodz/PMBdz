<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_sections_delete.inc.php,v 1.1 2011-09-14 08:44:13 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/cms/cms_section.class.php");

if(!$cms_section_form_id){
	return false;
}else{
	$section = new cms_section($cms_section_form_id);
	$section->delete();
}
print "<a href='$base_path/cms.php?categ=sections&sub=list'>Revenir à la liste</a>";

