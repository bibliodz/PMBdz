<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: gen_code_exemplaire_monarch.php,v 1.1 2013-06-20 14:18:22 abacarisse Exp $

function init_gen_code_exemplaire($notice_id,$bull_id)
{
	global $dbh;
	$requete="select max(expl_cb)as cb from exemplaires WHERE expl_cb like 'GEN%'";
	$query = mysql_query($requete, $dbh);
	if(mysql_num_rows($query)) {	
    	if(($cb = mysql_fetch_object($query)))
			$code_exemplaire= $cb->cb;
		else $code_exemplaire = "GEN000000"; 	
	} else $code_exemplaire = "GEN000000"; 
	return $code_exemplaire;  	   						
}

function gen_code_exemplaire($notice_id,$bull_id,$code_exemplaire)
{
	$code_exemplaire++;
	return $code_exemplaire;
}

/*
 * Fonction de calcul de la clé MONARCH
 * Non utilisé pour l'instant
 */
function monarch_key($barreCode){
	if(strlen($barreCode) == 13){
		$n4=0;
		foreach(preg_split('//', $barreCode,null,PREG_SPLIT_NO_EMPTY) as $index=>$char){
			if($index&1){
				$n4=$n4+intval($char);
			}else{
				foreach(preg_split('//', intval($char)*2,null,PREG_SPLIT_NO_EMPTY) as $nb){
					$n4=$n4+intval($nb);
				}
			}
		}
		return (10-($n4%10));
	}
}