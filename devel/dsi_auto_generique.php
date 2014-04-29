#!/usr/bin/php
<?php
/*
Installation (Attention � la s�curit�) : 
Renommer ce script  (dsi_auto_xxx.php) et le coller dans le r�pertoire pmb/devel
Renseigner ensuite la ligne :
$pmb=new pmb_remote("PMB_URL","PORT","PROXY","LOGIN","PASSWORD","DATABASE_NAME","SSL_PATH");
puis la d�commenter.
avec 
PMB_URL =  URL base PMB
PORT = Port du serveur Web
PROXY = Adresse du proxy (optionnel) 
LOGIN = Login de l'utilisateur qui effectue l'envoi de la DSI
PASSWORD = Mot de passe de l'utilisateur
DATABASE_NAME = Nom de la base de donn�e
SSL_PATH = Chemin vers le r�pertoire de stockage du certificat num�rique (xxx.crt,xxx.key) (optionnel)

exemple : 
$pmb=new pmb_remote("http://localhost/pmb/",80,"","dbellamy","pwd","bibli");

Dans le r�pertoire /etc/cron.daily, ajouter un script sh ex�cutable (� adapter avec le chemin vers dsi_auto_xxx.php):

#!/bin/sh
cd /var/www/html/pmb/devel/
/var/www/html/pmb/devel/dsi_auto_xxx.php 


Pour tester plus rapidement, le script peut �tre mis dans cron.hourly (Faire chauffer une heure)
*/


require_once("pmb_remote.class.php");

//Connexion 
//$pmb=new pmb_remote("PMB_URL","PORT","PROXY","LOGIN","PASSWORD","DATABASE_NAME","SSL_PATH");


// connection � PMB
if (!$pmb->connection()) {
	print "DSI Auto : Erreur de connexion : ".$pmb->error_message."\n";
	exit;
}

// Faire un get
if (!$pmb->http_get("dsi/diffusion_auto.php")) {
	print "DSI Auto : Erreur de diffusion : ".$pmb->error_message."\n";
	exit;
}
print "DSI Auto : diffusion OK : \n".$pmb->response."\n";
$pmb->disconnection();
?>