<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_page_delete.inc.php,v 1.1 2012-03-05 16:25:01 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/cms/cms_pages.class.php");

$page = new cms_page($id);
$page->delete();

require_once($base_path."/cms/cms_pages/cms_pages_list.inc.php");