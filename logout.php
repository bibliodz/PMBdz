<?php

// +-------------------------------------------------+

// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: logout.php,v 1.4 2007-03-14 15:18:56 gueluneau Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "";  
$base_title = "\$msg[8]";
$base_noheader=1;
require_once ("$base_path/includes/init.inc.php");  
 
// modules propres � logout.php ou � ses sous-modules

sessionDelete('PhpMyBibli');
mysql_close($dbh);

// appel de l'index

header("Location: index.php");
exit();

?>