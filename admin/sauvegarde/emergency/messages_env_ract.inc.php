<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: messages_env_ract.inc.php,v 1.5 2007-03-10 08:32:25 touraine37 Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$filename=$_POST["filename"];
$compress=$_POST["compress"];
$crypt=$_POST["crypt"];
$tables=$_POST["tables"];
$decompress=$_POST["decompress"];
$decompress_ext=$_POST["decompress_ext"];
$decompress_type=$_POST["decompress_type"];
$phrase1=$_POST["phrase1"];
$phrase2=$_POST["phrase2"];
$db=$_POST["db"];
$host=$_POST["host"];
$db_user=$_POST["db_user"];
$db_password=$_POST["db_password"];
$critical=$_POST["critical"];

$msg["sauv_misc_ract_title"]="Restauration d'un jeu";
$msg["sauv_misc_ract_cant_connect"]="La connexion au serveur de base de donn�es n'a pu �tre �tablie";
$msg["sauv_misc_ract_db_dont_exists"]="La base %s n'existe pas";
$msg["sauv_misc_ract_cant_open_file"]="Le fichier n'a pu �tre ouvert !";
$msg["sauv_misc_ract_no_sauv"]="Le fichier n'est pas un fichier de sauvegarde !";
$msg["sauv_misc_ract_decryt_msg"]="D�cryptage du fichier...";
$msg["sauv_misc_ract_bad_keys"]="Vous n'avez pas fourni les bonnes clefs pour d�crypter le fichier !";
$msg["sauv_misc_ract_create"]="Le fichier SQL n'a pu �tre cr��, v�rifiez les droits du r�pertoire admin/backup/backups/";
$msg["sauv_misc_ract_decompress"]="D�compression du fichier...";
$msg["sauv_misc_ract_not_bz2"]="Le fichier de donn�es n'a pas �t� compress� avec bz2";
$msg["sauv_misc_ract_restaure_tables"]="Restauration des tables";
$msg["sauv_misc_ract_open_failed"]="Le fichier SQL n'a pu �tre ouvert";
$msg["sauv_misc_ract_restaured_t"]="Table %s restaur�e.";
$msg["sauv_misc_ract_start_restaure"]="D�but de restauration de la table %s...";
$msg["sauv_misc_ract_ignore"]="Ignore la table %s ...";
$msg["sauv_misc_ract_invalid_request"]="Requ�te invalide : %s";
$msg["sauv_misc_ract_correct"]="La restauration s'est pass�e correctement";
