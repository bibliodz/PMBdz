<?php
// +--------------------------------------------------------------------------+
// | PMB est sous licence GPL, la r�utilisation du code est cadr�e            |
// +--------------------------------------------------------------------------+
// $Id: print_doc_dsi.php,v 1.4 2013-04-09 06:54:29 ngantier Exp $

//Impression DSI

$base_path = ".";
$base_auth = "DSI_AUTH";
$base_title = "\$msg[dsi_menu_title]";
$base_nobody=1;
$base_noheader=1;


require($base_path."/includes/init.inc.php");

require_once($class_path."/mono_display.class.php");
require_once($include_path."/notice_authors.inc.php");
require_once($include_path."/notice_categories.inc.php");
require_once($class_path."/author.class.php");
require_once($class_path."/editor.class.php");
require_once($include_path."/isbn.inc.php");
require_once($class_path."/collection.class.php");
require_once($class_path."/subcollection.class.php");
require_once($class_path."/serie.class.php");
require_once($include_path."/explnum.inc.php");
require_once($class_path."/category.class.php");
require_once($class_path."/indexint.class.php");
require_once($class_path."/search.class.php");
require_once($class_path."/serial_display.class.php");

include_once("$class_path/bannette.class.php");
include_once("$class_path/equation.class.php");
include_once("$class_path/classements.class.php");
require_once("$class_path/docs_location.class.php");
require_once("./dsi/func_abo.inc.php");
require_once("./dsi/func_pro.inc.php");
require_once("./dsi/func_common.inc.php");
require_once("./dsi/func_clas.inc.php");
require_once("./dsi/func_equ.inc.php");
require_once("./dsi/func_diff.inc.php");

if (!$id_bannette) die( "<script>self.close();</script>" );

$bannette = new bannette($id_bannette) ;
$bannette->construit_diff() ;

header("Content-Disposition: attachment; filename=bibliographie.doc;");
header('Content-type: application/msword'); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
echo $bannette->document_diffuse;
//print pmb_bidi($bannette->document_diffuse) ;

