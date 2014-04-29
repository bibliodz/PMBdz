<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sip2.php,v 1.4 2010-10-13 09:34:47 ngantier Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "CIRCULATION_AUTH";  
$base_noheader=1;
require_once("$base_path/includes/init.inc.php");  
require_once("$class_path/sip2_protocol.class.php");
require_once("$class_path/sip2_trame.class.php");
require_once("$include_path/sip2/sip2_functions.inc.php");
$message=stripslashes($message);

$protocol=new sip2_protocol("$include_path/sip2/protocol.xml",$charset);

if($debug) $fp_debug=fopen("temp/messages.log","a+");
if($fp_debug){
	fwrite($fp_debug,$message."\n");
}

//Analyse de la trame
$trame=new sip2_trame($message,$protocol);

$last_trame="";
$message_pair="";

//Si il y a une erreur ?
if ($trame->error) {
	print $trame->error_message;
	//Si c'est une erreur on redemande le message
	$message_pair=96;
	$values=array();
} else {
	//Sinon tout va bien
	$message_pair=$trame->message_pair;
	$values=$trame->message_values;
	if ($trame->message_id==97) {
		//Demande du dernier message
		if ($_SESSION[$id]["ltrame"]) {
			//Si dernier message pas vide
			$last_trame=$_SESSION[$id]["ltrame"];
			print $_SESSION[$id]["ltrame"];
			$message_pair="";
		} else {
			//Si dernier message vide, on envoie une redemande
			$message_pair=96;
			$values=array();
		}
	}
}
if ($message_pair) {
	$tramer=new sip2_trame("",$protocol);
	$tramer->set_message_id($message_pair);
	$tramer->set_checksum(true);
	$tramer->set_sequence_number($trame->sequence_number*1);
	//Appel de la fonction
	$func_response="_".strtolower($protocol->messages[$message_pair]["NAME"])."_";
	$values=$func_response($values);
	$tramer->set_message_values($values);
	//Si il y a une erreur, erreur d�finitive !
	if ($tramer->error) {
		print $tramer->error_message;
	  	print "exit";
	} else {
	    //On construit la trame
	    $tramer->make_trame();
	    //Si il y a une erreur
	    if ($tramer->error) {
	    	print $tramer->error_message;
	    	print "exit";
	    } else {
	    	print $tramer->trame;
	    	if($fp_debug){
	    		fwrite($fp_debug,$tramer->trame."\n");
	    	}
	    	$last_trame=$tramer->trame;
	    }
	}
}
if($fp_debug){
	fclose($fp_debug);	    		
}
$_SESSION[$id]["ltrame"]=$last_trame;
?>