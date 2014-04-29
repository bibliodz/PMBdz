<?php
// Fichier de param�trage de la borne de pr�t.
// tous les xxxxxxxxxxxxxxx sont � �diter en fonction du client

/*	url de PMB et la connection � la base de donn�es
 * 
 * Mode debug : rajouter apr�s ..../sip2.php ceci: ?debug=1
 *  Permet de d'enregistrer les transactions dans temp/messages.log de PMB 
 */ 
$s->http_url="https://gestionxxxxxxxxxxxxxxx.bibli.fr/xxxxxxxxxxxxxxx/sip2.php";
$s->http_url_login="https://gestionxxxxxxxxxxxxxxx.bibli.fr/xxxxxxxxxxxxxxx/main.php";
$s->http_port=80;

$s->http_use_cookie=true;
$s->http_cookie_login=array("user"=>"xxxxxxxxxxxxxxx","password"=>"xxxxxxxxxxxxxxx","database"=>"xxxxxxxxxxxxxxx");
$s->http_renew_pattern="/class\=\'erreur\'/";

// Pour une connection s�curis�e, mettre $s->http_use_ssl � true et renseigner les fichiers ssl (.crt et .key)
// A recopier depuis le CD d'acces s�curis� dans le r�pertoire PHPRuntime
$s->http_use_ssl=true;
$s->http_ssl_crt="xxxxxxxxxxxxxxx.crt";
$s->http_ssl_key="xxxxxxxxxxxxxxx.key";

// adresse du service de la borne de pr�t
$s->socket_bind_address="127.0.0.1";

// $exec_cmd permet de lancer l'executable de la borne de pr�t, une fois la connection effectu�e.
// N�cessite le module PsTools permettant l'execution du processus tout en rendant la main
// $exec_cmd="C:\PsTools\psexec -d \"C:\Program Files\Bibliocheck4Selfservice 4_0_0_84\Bibliocheck4Selfservice.exe\"";
$exec_cmd="";

// $socket_write_type permet de gerer la m�thode de socket_write selon le constructeur de la borne
// Nedap: $socket_write_type=0;
// Ident: $socket_write_type=1;
$socket_write_type=1;

// $protocol_prolonge permet de gerer la m�thode de r�ponse d'une prolongation de pr�t, post� pour que pmb/sip2.php en prenne compte
// Nedap: $protocol_prolonge="";
// Ident: $protocol_prolonge="&protocol_prolonge=1";
$protocol_prolonge="&protocol_prolonge=1";

/* 
 * Pour lancer le service executer dans une console
 * Sous Linux;
 * 	php socket2http.class.php
 * Sous Windows, installer PHPRuntime;
 * 	php socket2http.class.php
 */
