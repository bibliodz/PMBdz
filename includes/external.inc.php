<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: external.inc.php,v 1.11 2013-04-15 12:29:19 mbertin Exp $

//Fonctions pour les recherches externes

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/iso2709.class.php");

function entrepot_to_unimarc($recid) {
	global $dbh,$charset;
	
	$requete = "SELECT source_id FROM external_count WHERE rid=".addslashes($recid).";";
	$myQuery = mysql_query($requete, $dbh);
	$source_id = mysql_result($myQuery, 0, 0);
	
	$requete="select * from entrepot_source_$source_id where recid='".addslashes($recid)."' group by ufield,usubfield,field_order,subfield_order,value order by field_order,subfield_order";
	$resultat = mysql_query($requete, $dbh);
	$unimarc=new iso2709_record("",USER_UPDATE);
	if($charset == "utf-8"){//Si on est en UTF-8 alors ce qui sort de la base est en UTF-8 et donc la notice sera en utf-8
		$unimarc->is_utf8 = true;
	}
	$field_order=-1;
	$field=$r->ufield;
	$field_ind= $r->field_ind;
	while(strlen($field_ind)<2){
		$field_ind.= ' ';
	}
	$sfields=array();
	
	while ($r=mysql_fetch_object($resultat)) {
		switch ($r->ufield) {
			case "rs":
				$unimarc->set_rs($r->value);
				break;
			case "dt":
				$unimarc->set_dt($r->value);
				break;
			case "bl":
				$unimarc->set_bl($r->value);
				break;
			case "hl":
				$unimarc->set_hl($r->value);
				break;
			case "el":
				$unimarc->set_el($r->value);
				break;
			case "ru":
				$unimarc->set_ru($r->value);
				break;
			case "001":
				$unimarc->add_field("001",'  ',$r->value);
			default:
				if ($field_order!=$r->field_order) {
					if (count($sfields)) {
						$unimarc->add_field($field,$field_ind,$sfields);
					}
					$field=$r->ufield;
					$field_ind= $r->field_ind;
					while(strlen($field_ind)<2){
						$field_ind.= ' ';
					}
					$sfields=array();
					$field_order=$r->field_order;
				}
				if (!$r->usubfield) 
					$unimarc->add_field($r->ufield,'',$r->value);
				else {
					$sfields[][0]=$r->usubfield;
					$sfields[count($sfields)-1][1]=$r->value;
				}
				break;
		}
	}
	if (count($sfields)) {
		$unimarc->add_field($field,$field_ind,$sfields);
	}
	$unimarc->update();
	return array('notice' => $unimarc->full_record, 'source_id' => $source_id);
}

function suppr_item_to_entrepot($item){
	global $dbh;
	
	$requete = "SELECT source_id FROM external_count WHERE rid=".addslashes($item).";";
	$myQuery = mysql_query($requete, $dbh);
	//on �vite les mauvaises surprises (genre rechargement de page...)
	if(mysql_num_rows($myQuery)){
		$source_id = mysql_result($myQuery, 0, 0);
		if($source_id){
			//on supprime les documents num�riques int�gr�s en tant que fichiers
			$q = "select value as file_name from entrepot_source_$source_id where recid='".addslashes($item)."' and ufield='897' and usubfield='a' and value like '/%' ";
			$r = mysql_query($q,$dbh);
			if (mysql_num_rows($r)) {
				while ($row = mysql_fetch_object($r)) {
					@unlink($row->file_name);
				}
			}
			//on supprime les infos
			$requete="delete from entrepot_source_$source_id where recid='".addslashes($item)."'";
			$resultat = mysql_query($requete, $dbh);
			//on supprime la r�f�rence
			$requete = "delete from external_count WHERE rid=".addslashes($item).";";
			$myQuery = mysql_query($requete, $dbh);
			
		}
	}
}
?>
