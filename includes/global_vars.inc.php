<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: global_vars.inc.php,v 1.20 2013-04-08 14:40:45 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// prevents direct script access
pt_register ("SERVER", "REQUEST_URI");
if(preg_match('/global_vars\.inc\.php/', $REQUEST_URI)) {
	include('./forbidden.inc.php'); forbidden();
}

//Corrections des caract�res bizarres de M$
function cp1252_normalize($str){
	global $pmb_cp1252_normalize;

	if($pmb_cp1252_normalize==1){
		if(is_array($str)){
			foreach($str as $key=>$val){
				$str[$key]=cp1252_normalize($val);
			}
		}else{
			$cp1252_map = array(
				"\x80" => "EUR", /* EURO SIGN */
				"\x82" => "\xab", /* SINGLE LOW-9 QUOTATION MARK */
				"\x83" => "\x66",     /* LATIN SMALL LETTER F WITH HOOK */
				"\x84" => "\xab", /* DOUBLE LOW-9 QUOTATION MARK */
				"\x85" => "...", /* HORIZONTAL ELLIPSIS */
				"\x86" => "?", /* DAGGER */
				"\x87" => "?", /* DOUBLE DAGGER */
				"\x88" => "?",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
				"\x89" => "?", /* PER MILLE SIGN */
				"\x8a" => "S",   /* LATIN CAPITAL LETTER S WITH CARON */
				"\x8b" => "\x3c", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
				"\x8c" => "OE",   /* LATIN CAPITAL LIGATURE OE */
				"\x8e" => "Z",   /* LATIN CAPITAL LETTER Z WITH CARON */
				"\x91" => "\x27", /* LEFT SINGLE QUOTATION MARK */
				"\x92" => "\x27", /* RIGHT SINGLE QUOTATION MARK */
				"\x93" => "\x22", /* LEFT DOUBLE QUOTATION MARK */
				"\x94" => "\x22", /* RIGHT DOUBLE QUOTATION MARK */
				"\x95" => "\b7", /* BULLET */
				"\x96" => "\x20", /* EN DASH */
				"\x97" => "\x20\x20", /* EM DASH */
				"\x98" => "\x7e",   /* SMALL TILDE */
				"\x99" => "?", /* TRADE MARK SIGN */
				"\x9a" => "S",   /* LATIN SMALL LETTER S WITH CARON */
				"\x9b" => "\x3e;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
				"\x9c" => "oe",   /* LATIN SMALL LIGATURE OE */
				"\x9e" => "Z",   /* LATIN SMALL LETTER Z WITH CARON */
				"\x9f" => "Y"    /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
			);
			$str = strtr($str, $cp1252_map);
		}
	}
	return $str;
}


function pt_register() {
	/* Trouv� dans la doc de php au rayon register_globals :
	You can then register your global variables for use like this:
	// register a GET var
	pt_register('GET', 'user_id', 'password');
	// register a server var
	pt_register('SERVER', 'PHP_SELF');
	// register some POST vars
	pt_register('POST', 'submit', 'field1', 'field2', 'field3'); 
	
	CETTE FONCTION DOIT �TRE ETENDUE A FILES et compagnie...
	*/
	$num_args = func_num_args();
	$vars = array();
	if ($num_args >= 2) {
		$method = strtoupper(func_get_arg(0));
		
		if (($method != 'SESSION') && 
			($method != 'GET') && 
			($method != 'POST') && 
			($method != 'SERVER') && 
			($method != 'COOKIE') && 
			($method != 'FILES') && 
			($method != 'REQUEST') && 
			($method != 'ENV')) {
			die('The first argument of pt_register must be one of the following: 
				SESSION, GET, POST, SERVER, COOKIE, FILES, REQUEST or ENV');
		}
		
		$varname = "_{$method}";
		global ${$varname};
		
		for ($i = 1; $i < $num_args; $i++) {
			$parameter = func_get_arg($i);
			if (isset(${$varname}[$parameter])) {
	        	global $$parameter;
	        	if (get_magic_quotes_gpc())
				$$parameter = ${$varname}[$parameter];
				else $$parameter = addslashes(${$varname}[$parameter]);
			}
		}
		
	} else {
	    die('You must specify at least two arguments');
	}
	
} /* fin pt_register() */

// -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*
// -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*
if ((!isset($base_nosession) || !$base_nosession) && $_COOKIE["PhpMyBibli-SESSID"]) {
	header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: post-check=0, pre-check=0",false);
	session_cache_limiter('must-revalidate');
	session_name("pmb".$_COOKIE["PhpMyBibli-SESSID"]);
	session_start();
}

//Si on a demand� la r�cup�ration d'un environnement...
if (isset($_SESSION["last_required"]) && ($_SESSION["last_required"])&&($REQUEST_URI!="./print.php")) {
	//Restauration
	$_POST=$_SESSION["session_history"][$_SESSION["CURRENT"]][$_SESSION["last_required"]]["POST"];
	$_GET=$_SESSION["session_history"][$_SESSION["CURRENT"]][$_SESSION["last_required"]]["GET"];
	$_SESSION["last_required"]=false;
} else if (isset($_SESSION["PRINT"]) && ($_SESSION["PRINT"])&&(substr($REQUEST_URI,-9)=="print.php")) {
	$_POST=$_SESSION["PRINT"]["POST"];
	$_GET=$_SESSION["PRINT"]["GET"];
} else if (isset($_SESSION["PRINT_CART"]) && ($_SESSION["PRINT_CART"])&&(substr($REQUEST_URI,-14)=="print_cart.php")) {
	$_POST=$_SESSION["PRINT_CART"]["POST"];
	$_GET=$_SESSION["PRINT_CART"]["GET"];
}

/* VERSION SUPER GLOBALS */
// on commence par tout unset... 
//$arr = array_merge($_ENV, $_GET, $_POST, $_COOKIE, $_FILES, $_REQUEST, $_SERVER);
//while(list($__key__PMB) = each($arr)) unset(${$__key__PMB});                 
//$arr = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_POST_FILES, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS );
//while(list($__key__PMB) = each($arr)) unset(${$__key__PMB});                 

function add_sl(&$var) {
	if (is_array($var)) {
		reset($var);
		while (list($k,$v)=each($var)) {
			add_sl($var[$k]);
		}
	} else {
		$var=addslashes($var);
	}
}

// on r�cup�re tout sans se poser de question, attention � la s�curit� ! 
while (list($__key__PMB, $val) = @each($_GET)) {
	if ($__key__PMB!="base_path") {
		$val = cp1252_normalize($val);
		if (get_magic_quotes_gpc())
			$GLOBALS[$__key__PMB] = $val;
		else {
			add_sl($val);
			$GLOBALS[$__key__PMB] = $val;
		}
	}
}

while (list($__key__PMB, $val) = @each($_POST)) {
	if ($__key__PMB!="base_path") {
		$val = cp1252_normalize($val);
		if (get_magic_quotes_gpc())
			$GLOBALS[$__key__PMB] = $val;
		else {
			add_sl($val);
			$GLOBALS[$__key__PMB] = $val;
		}
	}
}

while (list($__key__PMB, $val) = @each($_FILES)) {
	if ($__key__PMB!="base_path") {
		if (get_magic_quotes_gpc())
			$GLOBALS[$__key__PMB] = $val;
		else {
			add_sl($val);
			$GLOBALS[$__key__PMB] = $val;
		}
	}
}

// quand register_globals sera � off il faudra r�cup�rer en automatique le strict minimum
pt_register ("COOKIE", "PhpMyBibli-SESSID","PhpMyBibli-LOGIN","PhpMyBibli-SESSNAME","PhpMyBibli-LOGIN");
pt_register ("SERVER", "REMOTE_ADDR","HTTP_USER_AGENT", "PHP_SELF", "REQUEST_URI", "REQUEST_URL", "QUERY_STRING", "SCRIPT_NAME");

// cookie des recherches Z3950
if (strstr($REQUEST_URI,"catalog.php") && $categ == 'z3950' && $action == 'search') {
	$expiration = time() + 30000000; 
	setcookie ('PMB-Z3950-criterion1', $crit1, $expiration);
	setcookie ('PMB-Z3950-criterion2', $crit2, $expiration);
	setcookie ('PMB-Z3950-boolean', $bool1, $expiration);
	if ($clause=="") {
		for ($i=0; $i<count($bibli); $i++) {
			if ($clause=="") $clause.=$bibli[$i];
				else $clause.=",".$bibli[$i];
		}
	}
	setcookie ('PMB-Z3950-clause', $clause, $expiration);
}
