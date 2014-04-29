<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: iimport_authorities.php,v 1.6 2013-03-21 10:28:55 mbertin Exp $

// d�finition du minimum necessaire
$base_path="../..";
$base_auth = "AUTORITES_AUTH";

$base_title = "";

require_once ("$base_path/includes/init.inc.php");
require_once($class_path."/origin.class.php");
require_once($include_path."/templates/import_authorities.tpl.php");
require_once($class_path."/notice_authority.class.php");
require_once($class_path."/notice_authority_serie.class.php");
require_once($base_path."/autorites/import/classes/".$pmb_import_modele_authorities.".class.php");

switch($action){
	// chargement du fichier
	case "upload" :
		//reprise des imports de notices...
		$tmp_file = $_FILES['userfile']['tmp_name'];
		if (!isset($from_file)) $from_file = $_FILES['userfile']['name'];
		$file_submit = $base_path.'/temp/'.basename($tmp_file);
		if ($file_submit=="") {
		    printf ($msg[503],$from_file); /* wrong permissions to copy the file %s ... Contact your admin... */
		    break;
		}
 		if (!move_uploaded_file($tmp_file,$file_submit)) {
		    printf ($msg[504],$from_file); /* Fail to copy %s, Contact your admin... */
		} else {
			if(!is_array($type_link)) $type_link = unserialize($type_link);
			printf ($msg[505],$from_file); /* File transfered, Loading is about to go on */
			$form = str_replace("!!file_submit!!",$file_submit,$authorities_import_preload_form);
			$form = str_replace("!!from_file!!",$from_file,$form);
			$form = str_replace("!!create_link!!",$create_link,$form);
			$form = str_replace("!!create_link_spec!!",$create_link_spec,$form);
			$form = str_replace("!!force_update!!",$force_update,$form);
			$form = str_replace("!!reload!!","",$form);
			$form = str_replace("!!authorities_type!!",$authorities_type,$form);
			$form = str_replace("!!type_link!!",serialize($type_link),$form);
			$form = str_replace("!!id_thesaurus!!",$id_thesaurus,$form);
			print $form;
		}
		break;
	// lecture du fichier et insertion en base... 
	case "load" :
		loadfile_in_base();
		$type_link = unserialize(stripslashes($type_link));
		if(!is_array($type_link)) $type_link = array();
		if ($pb_fini=="EOF") {
			//lecture_termin�
			$form = str_replace("!!file_submit!!",$file_submit,$authorities_import_afterupload_form);
			$form = str_replace("!!total!!","",$form);
			$form = str_replace("!!nb_notices!!",0,$form);
			$form = str_replace("!!nb_notices_rejetees!!",0,$form);
			//on vide la table des messages d'erreurs avant de commencer l'import
			$sql = "DELETE FROM error_log WHERE error_origin LIKE 'iimport_authorities".addslashes(SESSid).".php' ";
			$sql_result = mysql_query($sql) or die ("Couldn't delete error_log table !");
		}else{
			$form = str_replace("!!file_submit!!",$file_submit,$authorities_import_preload_form);
			$form = str_replace("!!reload!!","yes",$form);
		}
		$form = str_replace("!!from_file!!",$from_file,$form);
		$form = str_replace("!!create_link!!",$create_link,$form);
		$form = str_replace("!!create_link_spec!!",$create_link_spec,$form);
		$form = str_replace("!!force_update!!",$force_update,$form);
		$form = str_replace("!!authorities_type!!",$authorities_type,$form);
		$form = str_replace("!!type_link!!",serialize($type_link),$form);
		$form = str_replace("!!id_thesaurus!!",$id_thesaurus,$form);
		print $form;
		break;
	// formulaire de base
	// import en base des notices
	case "import" :
		$type_link = unserialize(stripslashes($type_link));
		if($nb_notices_import){
			$nb_notices_import = unserialize(stripslashes($nb_notices_import));
		}
		printf ($msg[509], $from_file);
		//on compte les notices qui restent...
		$query = "select count(id_import) from import_marc where origine = '".addslashes(SESSid)."'";	
		$result = mysql_query($query);
		if(mysql_nums_rows){
			$nb_notices_remanning = mysql_result($result,0,0);
			if(!$total){
				$total= $nb_notices_remanning;
			}
		}
		$query = "select notice, id_import from import_marc where origine='".addslashes(SESSid)."' ORDER BY id_import limit $pmb_import_limit_record_load ";
		$res = mysql_query($query) or die ("Couldn't select import table !");

		mysql_query("create table if not exists authorities_import_links (
			id_authority_import_links int unsigned not null auto_increment,
			num_authority int unsigned not null default 0,
			authority_type varchar(50) not null default '',
			authority_number varchar(50) not null default '',
			link_type varchar(20) not null default '',
			num_authority_from int unsigned not null default 0,
			authority_type_from varchar(50) not null default '',
			comment text not null , 
			primary key (id_authority_import_links)
		)");
		mysql_query("create table if not exists authorities_import (
			id_authority_import int unsigned not null auto_increment,
			num_authority int unsigned not null default 0,
			authority_type varchar(50) not null default '',
			authority_number varchar(50) not null default '',
			primary key (id_authority_import)
		)");

        if(mysql_num_rows($res)){
			while ($notobj = mysql_fetch_object($res)) {
	            $idnotice_import=$notobj->id_import ;
	            $nb_notices++;
	            //on la traite comme une notice d'autorit�s...
	            $notice_authority = new notice_authority($notobj->notice);
	            if($notice_authority->error){
	            	//en cas d'erreur � la lecture d'un format unimarc A, on a peut �tre un format unimarc B et une notice de collection
	            	$notice_authority = new notice_authority_serie($notobj->notice,"UNI","iso-8859-1",$type_link['subcollection']);
	            	 if($notice_authority->error){
		            	$fp = fopen ("../../temp/err_import_authorities".SESSid.".unimarc","a+");
		                fwrite ($fp, $notobj->notice);
		                fclose ($fp);
		                $nb_notices_rejetees++;
	            	 }
	            }
           		//on a une notice correcte, on regarde si on doit la trait�e...
	            if(!$notice_authority->error && ($authorities_type == 'all' || $notice_authority->type == $authorities_type)){ 
	            	//on y va...
	            	$authority_import = new $pmb_import_modele_authorities($notice_authority,$create_link,$create_link_spec,$force_update,$id_thesaurus,$type_link['rejected'],$type_link['associated']);
	            	//on r�cup�re les infos classiques...
	            	$authority_import->get_informations();
	            	//on donne la possibilit� d'agir sur les donn�es avant l'import...
	            	$authority_import->get_informations_callback();
	            	//si la notice est d'un type connu et donc importable dans PMB
	            	if($authority_import->notice->type != ""){
						// on importe
	            		$authority_import->import();
	            		// et on donne la possibilit� d'un traitement post-import
	            		$authority_import->import_callback();
	            		
	            		if(isset($nb_notices_import) && is_array($nb_notices_import) && $nb_notices_import[$authority_import->notice->type]){
	            			$nb_notices_import[$authority_import->notice->type]++;
	            		}else{
	            			$nb_notices_import[$authority_import->notice->type]=1;
	            		}
	            		
	            	}elseif(!$notice_authority->error){
		            	$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('iimport_authorities".addslashes(SESSid).".php', '".addslashes($msg[import_authorite_bad_type].($notice_authority->type != "" ? $msg["import_authorities_type_".$notice_authority->type] : $msg["52"]))."') ") ;
		            }
	            }elseif(!$notice_authority->error){
	            	$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('iimport_authorities".addslashes(SESSid).".php', '".addslashes($msg[import_authorite_bad_type].($notice_authority->type != "" ? $msg["import_authorities_type_".$notice_authority->type] : $msg["52"]))."') ") ;
	            }
				// la notice � �t� trait�e, on la supprime de la table d'import...
				$query = "delete from import_marc where id_import = ".$idnotice_import;
				mysql_query($query);
			}
        }
        //on regarde si besoin d'une autre passe...
		if($nb_notices_remanning>$pmb_import_limit_record_load){
			$form = str_replace("!!file_submit!!",$file_submit,$authorities_import_afterupload_form);
			$form = str_replace("!!from_file!!",$from_file,$form);
			$form = str_replace("!!create_link!!",$create_link,$form);
			$form = str_replace("!!create_link_spec!!",$create_link_spec,$form);
			$form = str_replace("!!force_update!!",$force_update,$form);
			$form = str_replace("!!authorities_type!!",$authorities_type,$form);
			$form = str_replace("!!total!!",$total,$form);
			$form = str_replace("!!nb_notices!!",$nb_notices,$form);
			$form = str_replace("!!nb_notices_import!!",serialize($nb_notices_import),$form);
			$form = str_replace("!!nb_notices_rejetees!!",$nb_notices_rejetees,$form);
			$form = str_replace("!!type_link!!",serialize($type_link),$form);
			$form = str_replace("!!id_thesaurus!!",$id_thesaurus,$form);
			print $form;
			printf($msg['nb_authorities_already_imported'],$nb_notices,$total); 
		}else{
			//c'est fini !
			printf($msg['end_authorities_import'],$nb_notices);
			print "<br/>";
			if(isset($nb_notices_import) && is_array($nb_notices_import)){
				foreach ( $nb_notices_import as $key => $value ) {
       				print $value."&nbsp;".htmlentities($msg["import_authorities_type_".$key]." ".$msg["import_authorities_import_success"],ENT_QUOTES,$charset)."<br/>";
				}
			}
			if($nb_notices_rejetees){
				printf($msg['import_authorities_error'],$nb_notices_rejetees);
				if(file_exists("../../temp/err_import_authorities".SESSid.".unimarc")){
					print "&nbsp;<a href='../../temp/err_import_authorities".SESSid.".unimarc' target='_blank' />".$msg['download']."</a><br/>";
				}
			}
			$gen_liste_log="";
            $resultat_liste=mysql_query("SELECT error_origin, error_text, count(*) as nb_error FROM error_log where error_origin in ('iimport_authorities".addslashes(SESSid).".php') group by error_origin, error_text ORDER BY error_origin, error_text" );
            $nb_liste=mysql_num_rows($resultat_liste);
            if ($nb_liste>0) {
                $gen_liste_log = "<br /><br /><b>".$msg[538]."</b><br /><table border='1'>" ;
                $gen_liste_log.="<tr><th>".$msg[539]."</th><th>".$msg[540]."</th><th>".$msg[541]."</th></tr>";
                $i_log=0;
                while ($i_log<$nb_liste) {
                    $gen_liste_log.="<tr>";
                    $gen_liste_log.="<td>".htmlentities(mysql_result($resultat_liste,$i_log,"error_origin"),ENT_QUOTES,$charset)."</td>" ;
                    $gen_liste_log.="<td><b>".htmlentities(mysql_result($resultat_liste,$i_log,"error_text"),ENT_QUOTES,$charset)."</b></td>" ;
                    $gen_liste_log.="<td>".htmlentities(mysql_result($resultat_liste,$i_log,"nb_error"),ENT_QUOTES,$charset)."</td>" ;
                    $gen_liste_log.="</tr>" ;
                    $i_log++;
                   }
                }
            $gen_liste_str.="</table>\n" ;
            print $gen_liste_log;
			//on supprime les tables d'import...
			mysql_query("drop table authorities_import");
			mysql_query("drop table authorities_import_links");
		}
		break;
	// formulaire de base	
	case "before_upload" :
	default : 
		//affichage du selectionneur de thesaurus et du lien vers les th�saurus
		$liste_thesaurus = thesaurus::getThesaurusList();
		$sel_thesaurus = '';
		$lien_thesaurus = '';
		
		$sel_thesaurus = "<select id='id_thesaurus' name='id_thesaurus'> ";
		foreach($liste_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
			$sel_thesaurus.= "<option value='".$id_thesaurus."' "; ;
			if ($id_thesaurus == $id_thes) $sel_thesaurus.= " selected";
			$sel_thesaurus.= ">".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</option>";
		}
		$sel_thesaurus.= "</select>";	
		$form = str_replace("!!thesaurus!!",$sel_thesaurus,$authorites_import_form_content);

		print $form;
		break;
}


function loadfile_in_base(){
	global $msg ;
	global $sub ;
	global $noticenumber, $file_submit, $from_file, $pb_fini, $reload ;
	global $pmb_import_limit_read_file ;

	if ($noticenumber=="") $noticenumber=0;

	if (!file_exists($file_submit)) {
		print $file_submit;
		printf ($msg[506],$from_file); /* The file %s doesn't exist... */
		return;
	}
	
	if (filesize($file_submit)==0) {
		printf ($msg[507],$from_file); /* The file % is empty, it's going to be deleted */
		unlink ($to_file);
		return;
	}
	
	$handle = fopen ($file_submit, "rb");
	if (!$handle) {
		printf ($msg[508],$from_file); /* Unable to open the file %s ... */
		return;
	}
	
	$file_size=filesize ($file_submit);

	$contents = fread ($handle, $file_size);
	fclose ($handle);
	
	/* First load of the shot, let's empty the import table */
	if ($reload=="") {
		$sql = "DELETE FROM import_marc WHERE origine='".addslashes(SESSid)."' ";
		$sql_result = mysql_query($sql) or die ("Couldn't delete import table !");
		$sql = "DELETE FROM error_log WHERE error_origin LIKE '%_".addslashes(SESSid).".%' ";
		$sql_result = mysql_query($sql) or die ("Couldn't delete error_log table !");
	}
	
	/* The whole file is in $contents, let's read it */
	$str_lu="";
	$j=0;
	$i=0;
	$pb_fini="";
	$txt="";
	while ( ($i<=strlen($contents)) && ($pb_fini=="") ) {
		$car_lu=substr($contents,$i,1) ;
		$i++;
		if ($i<=strlen($contents)) {
			if ($car_lu != chr(0x1d)) {
				/* the read car isn't the end of the notice */
				$str_lu = $str_lu.$car_lu;
			} else {
				/* the read car is the end of a notice */
				$str_lu = $str_lu.$car_lu;
				$j++;
				$sql = "INSERT INTO import_marc (notice,origine) VALUES('".addslashes($str_lu)."','".addslashes(SESSid)."')";
				$sql_result = mysql_query($sql) 
					or die ("Couldn't insert record!");
				if ($j>=$pmb_import_limit_read_file && $i<strlen($contents)) {
					/* let's rewrite the file with the remaing string  */
					$handle = fopen ($file_submit, "wb");
					fwrite ($handle, substr($contents,$i, $file_size-$i));
					fclose ($handle);
					printf (" ".$msg[510], ($file_size-$i)) ;
					$pb_fini="NOTEOF";
				} else if ($j>=$pmb_import_limit_read_file && $i>=strlen($contents)){
					$pb_fini = "EOF";					
				}
				$str_lu="";
			}
		} else { /* the wole file has been read */
			$pb_fini="EOF";
		}
	} /* end while red file */	
	if ($pb_fini=="EOF") { /* The file has been read, we can delete it */
		unlink ($file_submit);
	}
}