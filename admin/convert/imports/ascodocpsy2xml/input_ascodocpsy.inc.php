<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: input_ascodocpsy.inc.php,v 1.2 2014-01-07 09:12:18 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function _get_n_notices_($fi,$file_in,$input_params,$origine) {
	global $base_path;
	//mysql_query("delete from import_marc");
	
	$first=true;
	$stop=false;
	$content="";
	$index=array();
	$n=1;
	//Lecture du fichier d'entr�e
	while (!$stop) {
		
		//Recherche du retour chariot
		$pos_deb=strpos($content,"\n");
		while (($pos_deb===false)&&(!feof($fi))) {
			$content.=fread($fi,4096);
			$content=str_replace("\r\n","\n",$content);//Pour avoir le bon s�parateur
			//$content=str_replace("\r","\n",$content);//Pour avoir le bon s�parateur
			$pos_deb=strpos($content,"\n");
		}
		//D�but accroch�
		if ($pos_deb!==false) {
			//Notice = d�but jusqu'au retour chariot
			$notice=substr($content,0,$pos_deb);
			$content=substr($content,$pos_deb+1);
		} else {
			//Pas de notice suivante, c'est la fin du fichier
			$notice=$content;
			$stop=true;
		}
		
		//Si c'est la premi�re notice, c'est la ligne d'intitul�s !!
		if ($first) {
			//les champs sont s�par�s par la tabulation
			$cols=explode("\t",$notice);
			$fcols=fopen("$base_path/temp/".$origine."_cols.txt","w+");
			if ($fcols) {
				fwrite($fcols,serialize($cols));
				fclose($fcols);
			}
			$notice="";
			$first=false;
		}
		if ($notice) {
			$requete="insert into import_marc (no_notice, notice, origine) values($n,'".addslashes($notice)."','$origine')";
			mysql_query($requete);
			$n++;
			$t=array();
			$t["POS"]=$n;
			$t["LENGHT"]=1;
			$index[]=$t;
		}
	}
	return $index;
}


?>