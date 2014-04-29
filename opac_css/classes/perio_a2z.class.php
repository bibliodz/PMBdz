<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: perio_a2z.class.php,v 1.50 2014-03-14 17:31:02 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des perio a2z
if ( ! defined( 'PERIO_CLASS' ) ) {
  define( 'PERIO_CLASS', 1 );
  
require_once($base_path."/includes/templates/perio_a2z.tpl.php");
require_once($base_path."/classes/notice_info.class.php");
require_once($base_path.'/classes/notice_affichage.class.php');


require_once($base_path.'/includes/templates/notice_display.tpl.php');
require_once($base_path.'/includes/explnum.inc.php');
require_once($base_path.'/classes/notice_affichage.class.php');
require_once($base_path.'/includes/bul_list_func.inc.php');
require_once($base_path.'/classes/upload_folder.class.php');


require_once($base_path.'/includes/notice_affichage.inc.php');
require_once($base_path.'/includes/navbar.inc.php');

require_once($include_path."/resa_func.inc.php"); 
require_once($base_path.'/classes/notice.class.php');
require_once($base_path."/includes/templates/avis.tpl.php");
require_once("$class_path/acces.class.php");
require_once($base_path."/includes/notice_affichage.inc.php");

require_once($include_path."/notice_affichage.inc.php");


class perio_a2z {
	// ---------------------------------------------------------------
	//		propriétés de la classe
	// ---------------------------------------------------------------
	var $tab_alpha_notice = array();
	var $onglets_contens = array();
	var $onglets_sub_contens = array();
	var $max_per_onglet = 12;
	var $location = 0;
	var $surlocation = 0;
	var $titles=array(); //Liste des titres de périodiques
	
// ---------------------------------------------------------------
//		perio_a2z : constructeur
// ---------------------------------------------------------------
function perio_a2z($bull_id=0,$abecedaire=0,$max_per_onglet=12){
	// A MODIFIER!!
	
	$bull_id+=0;
	if($max_per_onglet){
		$this->max_per_onglet=$max_per_onglet;
	}
	if(!$bull_id){
		if(!$abecedaire){
			$this->getData();
		}else{
			$this->getDataAbc();
		}
	}
}

function getData() {
	global $dbh;
	global $filtre_select;
	
	$this->titles=array();
	
	$ongletInc=0;
	$req=$this->getQuery();
	$resultat=mysql_query($req);
	if ($nb_notices=mysql_num_rows($resultat)) {
		while($r=mysql_fetch_object($resultat)){
			if(substr($r->notice_id,0,strlen("es)"))=="es" &&  $filtre_select!=2){
				// si cataloguée, on ne l'affiche pas. sauf si on filtre par fonds externe ($filtre_select!=2)
				if ($this->get_doublon_ex($this->extract_external_id($r->notice_id))) continue;
			}
			$letter=substr(strtolower(trim($r->index_sew)),0,1);
			if(is_numeric($letter)){
				$letter="0";
			}
			
			if(!$ongletInc || $qtOngletCours==$this->max_per_onglet || (isset($lastLetter) && $letter!=$lastLetter && (is_numeric($lastLetter) || $lastLetter==" "))){
				$ongletInc++;
				$qtOngletCours=0;
				$this->onglets_contens[$ongletInc]["first_label"]=strtoupper(trim($r->index_sew));
				$this->onglets_contens[$ongletInc]["letter"]=$letter;
			}
			
			$lastLetter=$letter;
			
			$qtOngletCours++;
			$this->onglets_contens[$ongletInc]["last_label"]=strtoupper(trim($r->index_sew));
			
			//Sous-onglet (uniquement pour compatibilité avec affichage abécédaire
			$this->onglets_sub_contens[$ongletInc][1]["id"][]=$r->notice_id;
			
			//On mémorise le couple onglet/sous-onglet pour la recherche ajax
			$t=array();
			$t["onglet"]=$ongletInc.'_1';
			$t["label"]=strtoupper(trim($r->index_sew));
			$t["title"]=$r->tit1;
			$t["id"]=$r->notice_id;
			if(substr($r->notice_id,0,strlen("es)"))=="es"){
				//$this->titles[]="xxxxxxxxx";
				//print $r->notice_id;
			}else{
				$this->titles[]=$t;
			}
		}
		//On transforme les labels
		foreach($this->onglets_contens as $onglet=>$myOnglet){
			$lastOnglet=$onglet;
			if($onglet==1){
				$this->onglets_contens[$onglet]["first_label"]=substr($this->onglets_contens[$onglet]["first_label"],0,1);
			}else{
				$mesTermes=$this->difference_label($this->onglets_contens[$onglet-1]["last_label"],$this->onglets_contens[$onglet]["first_label"]);
				$this->onglets_contens[$onglet-1]["last_label"]=$mesTermes[0];
				$this->onglets_contens[$onglet]["first_label"]=$mesTermes[1];
			}
		}
		//On retravaille le dernier
		$mesTermes=$this->difference_label($this->onglets_contens[$lastOnglet]["first_label"],$this->onglets_contens[$lastOnglet]["last_label"]);
		$this->onglets_contens[$lastOnglet]["last_label"]=$mesTermes[1];
		foreach($this->onglets_contens as $onglet=>$myOnglet){
			$this->onglets_contens[$onglet]["label"]=$this->onglets_contens[$onglet]["first_label"]." - ".$this->onglets_contens[$onglet]["last_label"];
			//Cas particulier des inclassables et numériques
			if($this->onglets_contens[$onglet]["letter"]==" "){
				$this->onglets_contens[$onglet]["label"]=" # ";
			}elseif(is_numeric($this->onglets_contens[$onglet]["letter"])){
				$this->onglets_contens[$onglet]["label"]="0 - 9";
			}
		}
		
	}
}

function getDataAbc() {
	global $dbh,$filtre_select;

	$this->titles=array();
	
	$tbCorrespondance= array();
	$ongletInc=0;
	
	$req=$this->getQuery();
	$resultat=mysql_query($req);
	if ($nb_notices=mysql_num_rows($resultat)) {
		while($r=mysql_fetch_object($resultat)){
			if(substr($r->notice_id,0,strlen("es)"))=="es" && $filtre_select!=2){
				// si cataloguée, on ne l'affiche pas. sauf si on filtre par fonds externe ($filtre_select!=2)
				if ($this->get_doublon_ex($this->extract_external_id($r->notice_id))) continue;
			}
			$letter=substr(trim(strtolower($r->index_sew)),0,1);
			//On classe selon la première lettre
			if(is_numeric($letter)){
				$letter="0";
			}
			if(isset($tbCorrespondance[$letter])){
				$tbCorrespondance[$letter]["qt"]++;
				$tbCorrespondance[$letter]["lastTitle"]=trim($r->index_sew);
				$onglet=$tbCorrespondance[$letter]["onglet"];
			}else{
				$ongletInc++;
				$ongletSubInc=1;
				$tbCorrespondance[$letter]["qt"]=1;
				$tbCorrespondance[$letter]["onglet"]=$ongletInc;
				if(is_numeric($letter)){
					$this->onglets_contens[$ongletInc]["label"]="0 - 9";
				}elseif($letter==" "){// les inclassables
					$this->onglets_contens[$ongletInc]["label"]=" # ";
				}else{
					$this->onglets_contens[$ongletInc]["label"]=" ".strtoupper($letter)." ";
				}
				$this->onglets_contens[$ongletInc]["letter"][]=$letter;
				$onglet=$ongletInc;
			}
			//Sous-onglet
			if(!isset($this->onglets_sub_contens[$onglet][$ongletSubInc])){
				$this->onglets_sub_contens[$onglet][$ongletSubInc]["label"]=strtoupper(trim($r->index_sew));
			}
			$this->onglets_sub_contens[$onglet][$ongletSubInc]["id"][]=$r->notice_id;
			$this->onglets_sub_contens[$onglet][$ongletSubInc]["last_label"]=strtoupper(trim($r->index_sew));
			if(count($this->onglets_sub_contens[$onglet][$ongletSubInc]["id"])==$this->max_per_onglet){
				$ongletSubInc++;
			}
			//On mémorise le couple onglet/sous-onglet pour la recherche ajax
			$t=array();
			$t["onglet"]=$onglet.'_'.$ongletSubInc;
			$t["label"]=strtoupper(trim($r->index_sew));
			$t["title"]=$r->tit1;
			$t["id"]=$r->notice_id;
			if(substr($r->notice_id,0,strlen("es)"))=="es"){
// 				$this->titles[]="xxxxxxxxx";
// 				print $r->notice_id;
			}else{
				$this->titles[]=$t;			
			}
			
		}
		
		//On transforme les labels
		foreach($this->onglets_sub_contens as $onglet=>$myOnglet){			
			foreach($myOnglet as $ongletSub=>$myOngletSub){
				$lastOngletSub=$ongletSub;
				if($ongletSub==1){
					$this->onglets_sub_contens[$onglet][$ongletSub]["label"]=substr($this->onglets_sub_contens[$onglet][$ongletSub]["label"],0,1);
				}else{
					$mesTermes=$this->difference_label($this->onglets_sub_contens[$onglet][$ongletSub-1]["last_label"],$this->onglets_sub_contens[$onglet][$ongletSub]["label"]);
					$this->onglets_sub_contens[$onglet][$ongletSub-1]["last_label"]=$mesTermes[0];
					$this->onglets_sub_contens[$onglet][$ongletSub]["label"]=$mesTermes[1];
				}
			}
			//On retravaille le dernier
			$mesTermes=$this->difference_label($this->onglets_sub_contens[$onglet][$lastOngletSub]["label"],$this->onglets_sub_contens[$onglet][$lastOngletSub]["last_label"]);
			$this->onglets_sub_contens[$onglet][$lastOngletSub]["last_label"]=$mesTermes[1];
		}
		
	}
}

function difference_label($label1,$label2){
	$retour=array();
	$terme1=$terme2="";
	for($i=0; $i<strlen($label1);$i++){
		$terme1=substr($label1,0,$i+1);
		$terme2=substr($label2,0,$i+1);
		if($terme1 !=  $terme2){			
			break;
		}
	}
	$retour[0]=$terme1;
	$retour[1]=$terme2;
	return $retour;
}

function getQuery() {
	global $location;
	global $surloc;
	global $abt_actif;
	global $gestion_acces_active, $gestion_acces_empr_notice;
	global $filtre_select;

	$this->location=$location;
	$this->surlocation=$surloc;
	
	if($abt_actif){
		$from_abt_actif = " ,abts_abts ";
		$where_abt_actif = "  and num_notice=notice_id and date_fin >= CURDATE() ";
	}
	$opac_view_restrict=" and !(opac_visible_bulletinage&0x10) ";
	if($_SESSION["opac_view"] && $_SESSION["opac_view_query"] ){
		$opac_view_restrict.=" and notice_id in (select opac_view_num_notice from  opac_view_notices_".$_SESSION["opac_view"].") ";
	}
	//droits d'acces emprunteur/notice
	$acces_j='';
	if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
		$ac= new acces();
		$dom_2= $ac->setDomain(2);
		$acces_j = $dom_2->getJoin($_SESSION['id_empr_session'],4,'notice_id');
	}
	if($acces_j) {
		$statut_j='';
		$statut_r='';
	} else {
		$statut_j=',notice_statut ';
		$statut_r="and statut=id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")";
	}
	
	if($location){
		
		$req="
		SELECT distinct serial_id as notice_id, index_sew, tit1 FROM (
			(
				SELECT DISTINCT bulletin_notice as serial_id ,index_sew, tit1 FROM notices $acces_j, bulletins, exemplaires $from_abt_actif $statut_j
				WHERE notice_id=bulletin_notice and bulletin_id = expl_bulletin  and expl_location=$location  $opac_view_restrict $where_abt_actif $statut_r
			)union( 
				SELECT DISTINCT id_serial as serial_id ,index_sew, tit1 from notices $acces_j, collections_state $from_abt_actif $statut_j
				WHERE notice_id=id_serial and location_id=$location  $opac_view_restrict $where_abt_actif $statut_r
			)union(
				SELECT DISTINCT bulletin_notice as serial_id ,index_sew, tit1 FROM notices $acces_j, bulletins, explnum, explnum_location $from_abt_actif $statut_j
				WHERE notice_id=bulletin_notice and bulletin_id = explnum_bulletin AND num_explnum=explnum_id and num_location=$location $opac_view_restrict $where_abt_actif $statut_r
			)
		) AS sub order by index_sew	
		";		
		
	} elseif($surloc) {
		
		$req="
		SELECT distinct serial_id as notice_id, index_sew, tit1 FROM (
			(
				SELECT DISTINCT bulletin_notice as serial_id ,index_sew, tit1 FROM notices $acces_j, bulletins, exemplaires $from_abt_actif $statut_j
				WHERE notice_id=bulletin_notice and bulletin_id = expl_bulletin AND expl_location in( select idlocation from  docs_location where surloc_num= $surloc) $opac_view_restrict $where_abt_actif $statut_r
			)union( 
				SELECT DISTINCT id_serial as serial_id ,index_sew, tit1 from notices $acces_j, collections_state $from_abt_actif $statut_j
				WHERE notice_id=id_serial and location_id in( select idlocation from  docs_location where surloc_num= $surloc) $opac_view_restrict $where_abt_actif $statut_r
			)union(
				SELECT DISTINCT notice_id as serial_id ,index_sew, tit1 FROM notices $acces_j, bulletins, explnum, explnum_location $from_abt_actif $statut_j
				WHERE notice_id=bulletin_notice and bulletin_id = explnum_bulletin AND num_explnum=explnum_id and num_location in( select idlocation from docs_location  where surloc_num= $surloc) $opac_view_restrict $where_abt_actif $statut_r
			)union(
				SELECT DISTINCT notice_id as serial_id ,index_sew, tit1 FROM notices $acces_j, bulletins, explnum, explnum_location, analysis $from_abt_actif $statut_j
				WHERE notice_id=bulletin_notice and bulletin_id = explnum_notice and analysis_bulletin=bulletin_id AND num_explnum=explnum_id and num_location in( select idlocation from docs_location  where surloc_num= $surloc) $opac_view_restrict $where_abt_actif $statut_r
			)
		) AS sub order by index_sew	
		";
		
	} else {
		$req="SELECT DISTINCT notice_id, index_sew, tit1 from notices $acces_j $from_abt_actif $statut_j where niveau_biblio='s' $opac_view_restrict  $where_abt_actif $statut_r order by index_sew";
	}
	$sources_list=$this->get_external_sources_list();	
	if(count($sources_list) && $filtre_select!=1){
		if($filtre_select==2){
			$sources_queries =array();
			foreach($sources_list as $conn => $sources_ids){
				foreach($sources_ids as $source_id){
					$sources_queries[]= "
					select * from ((
						SELECT concat('es_".$conn."_',entrepot3.recid) as id, entrepot3.value as index_sew, entrepot3.value as tit1
						from entrepot_source_".$source_id." as entrepot1 
						join entrepot_source_".$source_id." as entrepot2 on entrepot1.recid = entrepot2.recid 
						join entrepot_source_".$source_id." as entrepot3 on entrepot1.recid = entrepot3.recid
						where entrepot1.ufield='bl' and entrepot1.value='s' and entrepot2.ufield='hl' and entrepot2.value='1' and entrepot3.ufield='200' and entrepot3.usubfield='a' group by 1
					) union (
						SELECT concat('es_".$conn."_',entrepot3.recid) as id, entrepot3.value as index_sew, entrepot3.value as tit1 
						FROM entrepot_source_".$source_id." as entrepot1 
						join entrepot_source_".$source_id." as entrepot2 on entrepot1.recid = entrepot2.recid 
						join entrepot_source_".$source_id." as entrepot3 on entrepot3.recid = entrepot2.recid 
						WHERE entrepot1.ufield = 'bl' and entrepot1.value='a' and entrepot2.ufield = 'hl' and entrepot2.value = '2' and entrepot3.ufield = '461' and entrepot3.usubfield='t' and entrepot3.value != '' group by entrepot3.value 
					)) as sub".$source_id." group by tit1";
				}
			}			
			$req1 = "select id as notice_id, tit1, index_sew from ((".implode(") union (",$sources_queries).")) as sousrequte group by tit1 order by index_sew";
		}else{
			$sources_queries =array();
			foreach($sources_list as $conn => $sources_ids){
				foreach($sources_ids as $source_id){
					$sources_queries[]= "
					select * from ((
						SELECT concat('es_".$conn."_',entrepot3.recid) as id, entrepot3.value as index_sew, entrepot3.value as tit1
						from entrepot_source_".$source_id." as entrepot1 
						join entrepot_source_".$source_id." as entrepot2 on entrepot1.recid = entrepot2.recid 
						join entrepot_source_".$source_id." as entrepot3 on entrepot1.recid = entrepot3.recid
						where entrepot1.ufield='bl' and entrepot1.value='s' and entrepot2.ufield='hl' and entrepot2.value='1' and entrepot3.ufield='200' and entrepot3.usubfield='a' group by 1
					) union (
						SELECT concat('es_".$conn."_',entrepot3.recid) as id, entrepot3.value as index_sew, entrepot3.value as tit1 
						FROM entrepot_source_".$source_id." as entrepot1 
						join entrepot_source_".$source_id." as entrepot2 on entrepot1.recid = entrepot2.recid 
						join entrepot_source_".$source_id." as entrepot3 on entrepot3.recid = entrepot2.recid 
						WHERE entrepot1.ufield = 'bl' and entrepot1.value='a' and entrepot2.ufield = 'hl' and entrepot2.value = '2' and entrepot3.ufield = '461' and entrepot3.usubfield='t' and entrepot3.value != '' group by entrepot3.value 
					)) as sub".$source_id." group by tit1";
				}
			}
			$req1="
				SELECT id as notice_id, index_sew, tit1 FROM (
					(
						SELECT notice_id as id , index_sew, tit1 from notices $acces_j $from_abt_actif $statut_j
						where niveau_biblio='s' $opac_view_restrict  $where_abt_actif $statut_r
					) union 
					(".implode(") union (",$sources_queries).")
				)AS sub order by trim(index_sew)";
		}
		return $req1;
	}
	return $req;
}

function startwith($elt) {
	if (substr(strip_empty_words($elt["title"]),0,strlen(strip_empty_words($this->start)))==strip_empty_words($this->start)) {
		return true;
	} else return false;
}

function filter($start) {
	$this->start=$start;
	$titles=array_filter($this->titles,array($this,startwith));
	return $titles;
}

function filterSearch($start,$datas) {
	global $dbh;
	
	//Liste des identifiants de périodiques disponibles
	$listeId=array();
	if(count($this->titles)){
		foreach($this->titles as $title){
			$listeId[]=$title["id"];
		}
	}
	//commence par
	$this->start=$start;
	$titles=array_filter($this->titles,array($this,startwith));
	//on complète
	if(count($titles)<20){
		$aq=new analyse_query(stripslashes($datas."*"));
		$query=$aq->get_query("notices", "index_sew", "index_sew", "notice_id", "niveau_biblio='s' AND niveau_hierar='1' AND notice_id IN (".implode(",",$listeId).")");
		$result = mysql_query($query,$dbh);
		if ($result) {
			while ($row = mysql_fetch_object($result)) {
				foreach($this->titles as $title){
					if($title["id"] == $row->notice_id){
						if(!in_array($title,$titles)){
							$titles[]=$title;
						}
						continue;
					}
				}
			}
		}
	}
	return $titles;
}

function compose_label($titre1,$titre2) {
	$therme1=$therme2="";
	for($i=0; $i<strlen($titre1);$i++){
		$therme1=substr($titre1,0,$i+1);
		$therme2=substr($titre2,0,$i+1);
		if($therme1 !=  $therme2){			
			break;
		}
	}
	$label=substr($therme1,0,5)." - ".substr($therme2,0,5);
	return($label);
}
	
function get_form($onglet_sel='1_1',$flag_empty=0,$flag_ajax=0){
	global $dbh,$msg,$charset;
	global $a2z_perio_display,$onglet_a2z,$ongletSub_a2z,$ongletSubList_a2z, $a2z_perio,$a2z_tpl;
	global $abt_actif;
	global $avis_tpl_form1_script;
	global $filtre_select;
	
	$myArray = explode("_",$onglet_sel);
	$onglet_sel = $myArray[0];
	$ongletSub_sel = $myArray[1];

	if(!$this->onglets_contens) return"";
	if($flag_ajax)$form=$avis_tpl_form1_script.$a2z_tpl;
	else $form=$avis_tpl_form1_script."<div id='perio_a2z'>\n".$a2z_tpl."</div>";
	$form_list="";
	$form_sublist="";
	foreach($this->onglets_contens as $onglet_num => $onglet){
		$line=$onglet_a2z;
		$line = str_replace('!!onglet_num!!',$onglet_num, $line);
		$line = str_replace('!!onglet_label!!',$onglet["label"], $line);
				
		$lineSub = $ongletSub_a2z;
		$lineSub = str_replace('!!onglet_num!!',$onglet_num, $lineSub);
		$subList="";
		if(count($this->onglets_sub_contens[$onglet_num])>1){
			foreach($this->onglets_sub_contens[$onglet_num] as $ongletSub_num => $ongletSub){
				$lineSubList = $ongletSubList_a2z;
				$lineSubList = str_replace('!!onglet_num!!',$onglet_num, $lineSubList);
				$lineSubList = str_replace('!!ongletSub_num!!',$ongletSub_num, $lineSubList);
				$lineSubList = str_replace('!!ongletSub_label!!',$ongletSub["label"]." - ".$ongletSub["last_label"], $lineSubList);
				$subList.=$lineSubList;
			}
		}
		$lineSub = str_replace('!!ongletSub_list!!',$subList, $lineSub);
		
		if($onglet_num==$onglet_sel && !$flag_empty){
			foreach($this->onglets_sub_contens[$onglet_num] as $ongletSub_num => $ongletSub){
				if($ongletSub_num==$ongletSub_sel){
					// liste des périodiques
					$perio_list="";
					$view=0;
					$perio_id_list="";
					if (is_array($ongletSub["id"])) {
						//tri de la liste par titre
						$serials_ids = "";
						$es_queries = array();
						foreach($ongletSub["id"] as $id){
							if(strpos($id,"es")!==false){
								// si cataloguée, on ne l'affiche pas. sauf si on filtre par fonds externe ($filtre_select!=2)
								if ($filtre_select==2 || !$this->get_doublon_ex($this->extract_external_id($id))){
									$elems = explode("_",$id);
									$query = "select * from external_count where rid =".$elems[2];
									$result = mysql_query($query);
									if(mysql_num_rows($result)){
										while($row = mysql_fetch_object($result)){
											$es_queries[]="(select '".$id."' as notice_id,entrepot_source_".$row->source_id.".value as tit1 from external_count join entrepot_source_".$row->source_id." on entrepot_source_".$row->source_id.".recid = external_count.rid where external_count.rid = '".$elems[2]."' and entrepot_source_".$row->source_id.".ufield = '200' and entrepot_source_".$row->source_id.".usubfield='a')";
										}
									}
								}
							}else{
								if($serials_ids) $serials_ids.= ",";
								$serials_ids.= $id;
							}
						}
						$es_query = $int_query = "";
						if(count($es_queries)){
							$es_query = "select * from (".implode(" union ",$es_queries).") as sub";
						}
						if($serials_ids){
							$int_query = "select notice_id,tit1 from notices where notice_id in(".$serials_ids.")";
						}
						
						if($int_query && $es_query){
							$query = "select * from ((".$int_query.") union (".$es_query.")) as sub order by tit1";
						}else if ($es_query){
							$query = $es_query." order by tit1";
						}else if ($int_query){ 
							$query = $int_query." order by tit1";
						}
						$result = mysql_query($query,$dbh);
						if ($result) {
							while ($notice = mysql_fetch_object($result)) {
								if(!$perio_id_list)$perio_id_list="'".$notice->notice_id."'";else	$perio_id_list.=",'".$notice->notice_id."'";
								$perio = $a2z_perio;
								
								$perio = str_replace('!!id!!',$notice->notice_id, $perio);	
								$perio = str_replace('!!perio_title!!',$notice->tit1, $perio);
								
								if(strpos($notice->notice_id,"es")!==false){
									$perio = str_replace('!!abt_actif!!',$this->get_external_icon($notice->notice_id), $perio);
								}else{
									$req = "select abt_id from abts_abts  where num_notice=".$notice->notice_id." and date_fin >= CURDATE() ";
									$res = mysql_query($req);
									if (mysql_num_rows($res)) {
										$perio = str_replace('!!abt_actif!!',"<img src='./images/check.png'>", $perio);									
									}else{
										$perio = str_replace('!!abt_actif!!',"", $perio);	
									}
								}
								$perio_list.= $perio;
								if(!$view){
									$view++;
									$form = str_replace('!!perio_display!!',$this->get_perio($notice->notice_id), $form);			
								}
							}
						}
					}
					$line = str_replace('!!onglet_class!!',"isbd_public_active", $line);
					$lineSub = str_replace('!!ongletSub_display!!','block', $lineSub);
				}
			}
		}else{
			$line = str_replace('!!onglet_class!!',"isbd_public_inactive", $line);
			$lineSub = str_replace('!!ongletSub_display!!','none', $lineSub);	
		}
		
		$form_list.=$line;
		$form_sublist.=$lineSub;
	}	
	if($abt_actif) $check_abt_actif=" checked='checked' ";
	else $check_abt_actif="";
		
	$form = str_replace('!!check_abt_actif!!',$check_abt_actif, $form);	
	$form = str_replace('!!onglet_sel!!',$onglet_sel, $form);	
	$form = str_replace('!!location!!',$this->location, $form);	
	$form = str_replace('!!surlocation!!',$this->surlocation, $form);	
	$form = str_replace('!!perio_display!!',"", $form);		
	$form=str_replace('!!a2z_onglets_list!!',$form_list, $form);
	$form=str_replace('!!a2z_onglets_sublist!!',$form_sublist, $form);	
	$form=str_replace('!!perio_id_list!!',$perio_id_list, $form);	
	$form=str_replace('!!a2z_perio_list!!',$perio_list, $form);	
	$form = str_replace('!!filtre!!',$this->get_filtre_form(), $form);	

	return $form;
}

function get_filtre_form(){
	global $msg, $filtre_select;
	
	$sources_list=$this->get_external_sources_list();
	if(!count($sources_list)) return "";
	
	$filtre_select+=0;
	$selected=array();
	$selected[$filtre_select]= " selected='selected' ";
	$sel_categ.= "</select>";
	$form=$msg["perio_a2z_filtre"]."
		<select class='saisie-25em' id='filtre_select' name='filtre_select' onchange=\"memo_onglet=new Array(); reload_all(); \">
			<option value='0' $selected[0] >".$msg["perio_a2z_filtre_all"]."</option>
			<option value='1' $selected[1] >".$msg["perio_a2z_fonds_propre"]."</option>
			<option value='2' $selected[2] >".$msg["perio_a2z_fonds_externe"]."</option>
		</select>
	";
	
	return $form;
}	

function get_onglet($onglet_sel='1_1'){
	global $dbh,$msg,$charset,$base_path;
	global $a2z_perio_display,$onglet_a2z, $a2z_perio,$a2z_tpl_ajax;
	global $filtre_select;
	$myArray = explode("_",$onglet_sel);
	$onglet_sel = $myArray[0];
	$ongletSub_sel = $myArray[1];
	
	$form=$a2z_tpl_ajax;
	$form_list="";
	foreach($this->onglets_sub_contens[$onglet_sel] as $onglet_num => $onglet){
		if($onglet_num==$ongletSub_sel){
			// onglet actif
			$line = str_replace('!!onglet_class!!',"isbd_public_active", $line);
			// liste des périodiques
			$perio_list="";
			$view=0;
			$perio_id_list="";
			if (is_array($onglet["id"])) {
				//tri de la liste par titre
				$serials_ids = implode(",", $onglet["id"]);
				foreach($onglet["id"] as $id_brute){
					if(substr($id_brute,0,2)=="es"){
						//notice externe					
						if( $filtre_select!=2){
							// si cataloguée, on ne l'affiche pas. sauf si on filtre par fonds externe ($filtre_select!=2)
							if ($this->get_doublon_ex($this->extract_external_id($id_brute))) continue;
						}
						$requete = "SELECT source_id FROM external_count WHERE rid=".addslashes($this->extract_external_id($id_brute));
						$myQuery = mysql_query($requete, $dbh);
						$source_id = mysql_result($myQuery, 0, 0);		
						$query="select ufield,value from entrepot_source_$source_id where recid='".addslashes($this->extract_external_id($id_brute))."' and ((ufield='200' and usubfield='a') or (ufield='461' and usubfield='t') or (ufield='bl') or (ufield='hl')) ";						
						$result = mysql_query($query,$dbh);
						if ($result) {
							while($row= mysql_fetch_object($result)){
								$infos[$row->ufield] = $row->value;
							}
							switch($infos['bl'].$infos['hl']){
								case "a2" :
								case "s2" :
									$tit1 = $infos['461'];
									break;
								default :
									$tit1 = $infos['200'];
									break;
									
							}
							
							$perio = $a2z_perio;						
							$perio = str_replace('!!id!!',$id_brute, $perio);
							$perio = str_replace('!!perio_title!!',$tit1, $perio);										
							$perio = str_replace('!!abt_actif!!',$this->get_external_icon($id_brute), $perio);								
							$perio_list.= $perio;
							if(!$view){
								$view++;
								$form = str_replace('!!perio_display!!',$this->get_perio($id_brute), $form);
							}						
							
						}	
					}else{
						$query = "select notice_id,tit1 from notices where notice_id =$id_brute";
						//print $query."<br>";
						$result = mysql_query($query,$dbh);
						if ($result) {
							if ($notice = mysql_fetch_object($result)) {
								$perio = $a2z_perio;
						
								$perio = str_replace('!!id!!',$notice->notice_id, $perio);
								$perio = str_replace('!!perio_title!!',$notice->tit1, $perio);
						
								$req = "select abt_id from abts_abts  where num_notice=".$notice->notice_id." and date_fin >= CURDATE() ";
								$res = mysql_query($req);
								if (mysql_num_rows($res)) {
									$perio = str_replace('!!abt_actif!!',"<img src='./images/check.png'>", $perio);
								}else{
									$perio = str_replace('!!abt_actif!!',"", $perio);
								}
								$perio_list.= $perio;
								if(!$view){
									$view++;
									$form = str_replace('!!perio_display!!',$this->get_perio($notice->notice_id), $form);
								}
							}
						}
					}
				}
			}
		}
		$form_list.=$line;
	}	
	$form=str_replace('!!perio_id_list!!',$perio_id_list, $form);	
	$form=str_replace('!!a2z_perio_list!!',$perio_list, $form);	

	return $form;
}


function get_doublon_ex($id){
	global $dbh;
	$requete = "SELECT source_id FROM external_count WHERE rid=".addslashes($id);
	$myQuery = mysql_query($requete, $dbh);
	$source_id = mysql_result($myQuery, 0, 0);		
	
	$rqt = "select value from entrepot_source_".$source_id." where recid='".addslashes($id)."' and ufield='011' and usubfield='a'";
	$res=mysql_query($rqt);

	if(mysql_num_rows($res)){
		if($r = mysql_fetch_object($res)) {			
			$code=$r->value;
			$rqt = "select notice_id from notices where code='$code'";
			$res_notice=mysql_query($rqt);
			
			if(mysql_num_rows($res_notice)){
				if($r_notice = mysql_fetch_object($res_notice)) {
					return $r_notice->notice_id;					
				}
			}
		}
	}
	return 0;
}

function get_perio_ex($id){
	if(strpos($id,"es") === false){
		return $this->get_perio($id);
 	}
	global $opac_notices_depliable;
	$opac_notices_depliable=0;
	return aff_serial_unimarc($this->extract_external_id($id));	
}

function get_bulletin_retard($serial_id){
	global $opac_sur_location_activate,$msg;
	
	$bulletin_retard_form="	
	<h3><span id='titre_exemplaires' class='bulletin_retard' >".$msg["bulletin_retard_title"]."</span></h3>
	<table class='bulletin_retard'>
		<tr>
			<th class='expl_header_location_libelle'>".$msg["bulletin_retard_location"]."</th>
			<th class='expl_header_location_libelle'>".$msg["bulletin_retard_date_parution"]."</th>
			<th class='expl_header_location_libelle'>".$msg["bulletin_retard_libelle_numero"]."</th>
			<th class='expl_header_location_libelle'>".$msg["bulletin_retard_comment"]."</th>
		</tr>
		!!bulletin_retard_list!!
	</table> 
	";
	$bulletin_retard_line="
		<tr class='!!tr_class!!' onmouseout=\"this.className='!!tr_class!!'\" onmouseover=\"this.className='surbrillance'\">
			<td>!!location_libelle!!</td>
			<td>!!date_parution!!</td>
			<td>!!libelle_numero!!</td>
			<td>!!comment_opac!!</td>		
		</tr>
	";
	$tpl="";
	$req="SELECT surloc_num, location_id,location_libelle, rel_date_parution,rel_libelle_numero, rel_comment_opac 
		from perio_relance, abts_abts, docs_location
		where  location_id=idlocation and rel_abt_num=abt_id and num_notice=$serial_id and rel_comment_opac!='' group by rel_abt_num,rel_date_parution,	rel_libelle_numero order by rel_nb desc";		

	$result = mysql_query($req);
	if(mysql_num_rows($result)){
		$tr_class="";
		while($r = mysql_fetch_object($result)) {	
			$surloc_libelle="";
			if($opac_sur_location_activate && $r->surloc_num ){
				$req="select surloc_libelle from sur_location where surloc_id = ".$r->surloc_num;
				$res_surloc = mysql_query($req);
				if(mysql_num_rows($res_surloc)){
					$surloc= mysql_fetch_object($res_surloc);
					$surloc_libelle=$surloc->surloc_libelle." / ";
				}
			}			
			$line=$bulletin_retard_line;
			
			$line=str_replace("!!location_libelle!!", $surloc_libelle.$r->location_libelle , $line);	
			$line=str_replace("!!date_parution!!", $r->rel_date_parution, $line);	
			$line=str_replace("!!libelle_numero!!", $r->rel_libelle_numero, $line);		
			$line=str_replace("!!comment_opac!!", $r->rel_comment_opac, $line);	
			if($tr_class=='even')$tr_class="odd"; else $tr_class='even';
			$line=str_replace("!!tr_class!!",$tr_class, $line);	
			$lines.=$line	;	
		}
		$tpl=$bulletin_retard_form;
		$tpl=gen_plus("bulletin_retard",$msg["bulletin_retard_title"],str_replace("!!bulletin_retard_list!!", $lines, $tpl));		
	}
	return $tpl;		
}	

function get_perio($id) {
	//on simplifie les appels..
	if(strpos($id,"es") !== false){
		return $this->get_perio_ex($id);
	}
		
	global $msg,$charset,$dbh;
	global $f_bull_deb_id,$f_bull_end_id,$opac_bull_results_per_page,$page,$opac_fonction_affichage_liste_bull,$bull_date_start,$bull_date_end;
	global $bull_num_deb,$bull_num_end;
	global $flag_no_get_bulletin;
	global $recherche_ajax_mode;

	$flag_no_get_bulletin=1;
	$opac_notices_depliable=0;
	$resultat_aff.=aff_notice($id, 0, 1, 0, "", 0, 0, 1, $recherche_ajax_mode);
	/*
	$notice = new notice_affichage($id) ;
	$notice->do_header();
	$notice->do_public();
	//$notice->do_isbd();
	$notice->genere_simple(0, 'PUBLIC') ;					
	$resultat_aff .= $notice->result;	
	*/
	$requete = "SELECT notice_id, niveau_biblio,typdoc,opac_visible_bulletinage FROM notices WHERE notice_id='$id'  and (opac_visible_bulletinage&0x1) LIMIT 1";	
	$res = @mysql_query($requete, $dbh);
	if (($obj=mysql_fetch_object($res))) {
	//Recherche dans les numéros	
		$start_num = $bull_num_deb;
		$end_num = $bull_num_end;
		if($f_bull_deb_id && $f_bull_end_id){
			$restrict_num = $this->compare_date($f_bull_deb_id,$f_bull_end_id);
			$restrict_date = ""; 
		} else if($f_bull_deb_id && !$f_bull_end_id){
			$restrict_num = $this->compare_date($f_bull_deb_id);
			$restrict_date = ""; 
		} else if(!$f_bull_deb_id && $f_bull_end_id){
			$restrict_num = $this->compare_date("",$f_bull_end_id);
			$restrict_date = ""; 
		} else if((!$f_bull_deb_id) && (!$f_bull_end_id)){					
			if($start_num && !$end_num){
				$restrict_num = " and bulletin_numero like '%".$start_num."%' ";
				$restrict_date = ""; 
			} else if(!$start_num && $end_num){
				$restrict_num = "and bulletin_numero like '%".$end_num."%' ";
				$restrict_date = ""; 
			} else if($start_num && $end_num){
				$restrict_num = "and bulletin_numero like '%".$start_num."%' ";
				$restrict_date = ""; 
			}
		}
		
		// Recherche dans les dates et libellés de période
		if(!$restrict_num) 
			$restrict_date = $this->compare_date($bull_date_start,$bull_date_end);
											
		// nombre de références par pages (12 par défaut)
		if (!isset($opac_bull_results_per_page)) $opac_bull_results_per_page=12; 
		if(!$page) $page=1;
		$debut =($page-1)*$opac_bull_results_per_page;
		$limiter = " LIMIT $debut,$opac_bull_results_per_page";
		
						
		//Recherche par numéro
		$num_field_start = "
			<input type='hidden' name='f_bull_deb_id' id='f_bull_deb_id' />
			<input id='bull_num_deb' name='bull_num_deb' type='text' size='10' completion='bull_num' autfield='f_bull_deb_id' value='".$start_num."' />";
		$numfield_end = "
			<input type='hidden' name='f_bull_end_id' id='f_bull_end_id' />
			<input id='bull_num_end' name='bull_num_end' type='text' size='10' completion='bull_num' autfield='f_bull_end_id' value='".$end_num."' />";
		
		//Recherche par date
		$deb_value = str_replace("-","",$bull_date_start);
		$fin_value = str_replace("-","",$bull_date_end);
		$date_deb_value = ($deb_value ? formatdate($deb_value) : '...');
		$date_fin_value = ($fin_value ? formatdate($fin_value) : '...');
		$date_debut = "
			<input type='hidden' id='bull_date_start' name='bull_date_start' value='$bull_date_start'/>
			<input type='button' class='bouton' id='date_deb_btn' name='date_deb_btn'  value='".$date_deb_value."' onClick=\"window.open('./select.php?what=calendrier&caller=form_values&date_caller=&param1=bull_date_start&param2=date_deb_btn&auto_submit=NO&date_anterieure=YES', 'date_fin', 'width=250,height=300,toolbar=no,dependent=yes,resizable=yes')\"/>
			<input type='button' class='bouton' name='del' value='X' onclick='this.form.date_deb_btn.value=\"...\";this.form.bull_date_start.value=\"\";' />
		";
		$date_fin = "
			<input type='hidden' id='bull_date_end' name='bull_date_end' value='$bull_date_end' />
			<input type='button' class='bouton' id='date_fin_btn' name='date_fin_btn' value='".$date_fin_value."' onClick=\"window.open('./select.php?what=calendrier&caller=form_values&date_caller=&param1=bull_date_end&param2=date_fin_btn&auto_submit=NO&date_anterieure=YES', 'date_fin', 'width=250,height=300,toolbar=no,dependent=yes,resizable=yes')\"/>
			<input type='button' class='bouton' name='del' value='X' onclick='this.form.date_fin_btn.value=\"...\";this.form.bull_date_end.value=\"\";' />
		";
		$bulletin_retard=$this->get_bulletin_retard($id);			
		$tableau = "		
		<a name='tab_bulletin'></a>
		<h3><span id='titre_exemplaires'>".$msg["a2z_perio_list_bulletins"]."</span></h3>
		<div id='form_search_bull'>
			
				<script src='./includes/javascript/ajax.js'></script>
				<form name=\"form_values\" action=\"./index.php?lvl=notice_display&id=$id\" >\n
					<input type=\"hidden\" name=\"premier\" value=\"\">\n
					<input type=\"hidden\" id='page' name=\"page\" value=\"$page\">\n
					<table>
						<tr>
							
							<td ><strong>".$msg["search_per_bull_num"]." : ".$msg["search_bull_start"]."</strong></td>
							<td >$num_field_start</td>						
							<td ><strong>".$msg["search_bull_end"]."</strong> $numfield_end</td>
							
							<td align='left' rowspan=2><input type='button' class='boutonrechercher' value='".$msg["142"]."' onclick='show_perio($id);' /></td>
						</tr>
						<tr>
							<td ><strong>".$msg["search_per_bull_date"]." : ".$msg["search_bull_start"]."</strong></td>
							<td>$date_debut</td>
							<td><strong>".$msg["search_bull_end"]."</strong> $date_fin</td>
							
						</tr>
					</table>
				</form>
			<div class='row'></div><br />
		</div>\n";
		$resultat_aff.= $tableau;
		
		
//		$resultat_aff.= "<script type='text/javascript'>ajax_parse_dom();</script>";	
		$resultat_aff.=$bulletin_retard;
		// A EXTERNALISER ENSUITE DANS un bulletin_list.inc.php
		$requete="SELECT bulletins.*,count(explnum_id) as nbexplnum FROM bulletins LEFT JOIN explnum ON explnum_bulletin = bulletin_id where bulletin_id in(
		SELECT bulletin_id FROM bulletins WHERE bulletin_notice='$id' $restrict_num $restrict_date and num_notice=0
		) or bulletin_id in(
		SELECT bulletin_id FROM bulletins,notice_statut, notices WHERE bulletin_notice='$id' $restrict_num $restrict_date 
		and notice_id=num_notice
		and statut=id_notice_statut 
		and((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").")) 
		GROUP BY bulletins.bulletin_id ";
		
		$rescount1=mysql_query($requete);
		$count1=mysql_num_rows($rescount1);
					
		//si on recherche par date ou par numéro, le résultat sera trié par ordre croissant
		if (($restrict_num)||($restrict_date)) $requete.=" ORDER BY date_date, bulletin_numero*1 ";
		else $requete.=" ORDER BY date_date DESC, bulletin_numero*1 DESC";
		$requete.=$limiter;
		$res = @mysql_query($requete, $dbh);
		$count=mysql_num_rows($res);
		if ($count) {
		//	if ($opac_fonction_affichage_liste_bull) eval("\$opac_fonction_affichage_liste_bull (\$res);");
		//	else
			 $resultat_aff.=$this->affichage_liste_bulletins_normale($res); 
		} else $resultat_aff.= "<strong>".$msg["bull_no_found"]."</strong>";
		//$resultat_aff.= "<br />";		
		
		// constitution des liens
		if (!$count1) $count1=$count;
		$nbepages = ceil($count1/$opac_bull_results_per_page);
		$url_page = "";//javascript:if (document.getElementById(\"onglet_isbd$id\")) if (document.getElementById(\"onglet_isbd$id\").className==\"isbd_public_active\") document.form_values.premier.value=\"ISBD\"; else document.form_values.premier.value=\"PUBLIC\"; document.form_values.page.value=!!page!!; document.form_values.submit()";
		$action = "show_perio($id);return false;";
		if ($nbepages>1) $form="<div class='row'></div>\n<div id='navbar_perio'><center>".printnavbar_onclick($page, $nbepages, $url_page,$action)."</center></div>";
	
	}
	
	return $resultat_aff.$form;
}


function get_bulletin($id){
	global $charset, $dbh,$msg,$css;
	global $opac_visionneuse_allow, $icon_doc,$opac_cart_allow,$opac_max_resa;
	global $begin_result_liste,$notice_header,$opac_resa_planning;
	global $opac_show_exemplaires,$fonction,$opac_resa_popup,$opac_resa,$popup_resa,$allow_book;
	global $opac_perio_a2z_show_bulletin_notice;

	$resultat_aff="";
	$libelle = $msg[270];
	$largeur = 500;		
	$requete = "SELECT bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, bulletin_titre, bulletin_cb, date_format(date_date, '".$msg["format_date_sql"]."') as aff_date_date,num_notice FROM bulletins WHERE bulletin_id='$id'";
	
	$res = @mysql_query($requete, $dbh);
	while(($obj=mysql_fetch_array($res))) {
		//on cherches des documents numériques
		$req = "select explnum_id from explnum where explnum_bulletin = ".$obj["bulletin_id"];
		$resultat = mysql_query($req, $dbh) or die ($req." ".mysql_error());
		$nb_ex = mysql_num_rows($resultat);
		//on met le nécessaire pour la visionneuse
		if($opac_visionneuse_allow && $nb_ex){
			$resultat_aff.= "
			<script type='text/javascript'>
				function sendToVisionneuse(explnum_id){
					document.getElementById('visionneuseIframe').src = 'visionneuse.php?mode=perio_bulletin&idperio=".$obj['bulletin_notice']."'+(typeof(explnum_id) != 'undefined' ? '&explnum_id='+explnum_id+\"\" : '\'');
				}
			</script>";
		}
		$typdocchapeau="a";
		$icon="";
		$requete3 = "SELECT notice_id,typdoc FROM notices WHERE notice_id='".$obj["bulletin_notice"]."' ";
		$res3 = @mysql_query($requete3, $dbh);
		while(($obj3=mysql_fetch_object($res3))) {
			$notice3 = new notice($obj3->notice_id);
			$typdocchapeau=$obj3->typdoc;
		}
		$notice3->fetch_visibilite();
		if (!$icon) $icon="icon_per.gif";
		$icon = $icon_doc["b".$typdocchapeau];
		
/*		//carrousel pour la navigation
		if($opac_show_bulletin_nav)
			$res_print = do_carroussel($obj);
		else $res_print="";
*/		
		//$res_print .= "<h3><img src=./images/$icon /> ".$notice3->print_resume(1,$css)."."." <b>".$obj["bulletin_numero"]."</b>".($nb_ex ? "&nbsp;<a href='#docnum'>".($nb_ex > 1 ? "<img src='./images/globe_rouge.png' />" : "<img src='./images/globe_orange.png' />")."</a>" : "")."</h3>\n";		
		$res_print .= "<h3><img src=./images/$icon /> ".$notice3->print_resume(1,$css)."."." <b>".$obj["bulletin_numero"]."</b></h3>\n";
		$num_notice=$obj['num_notice'];
/*		
		if ($obj['bulletin_titre']) {
			$res_print .=  htmlentities($obj['bulletin_titre'],ENT_QUOTES, $charset)."<br />";
		} 
		if ($obj['mention_date']) $res_print .= $msg['bull_mention_date']." &nbsp;".$obj['mention_date']."\n";
*/		 
		if ($obj['date_date']) $res_print .= $msg['bull_date_date']." &nbsp;".$obj['aff_date_date']." \n";     
		if ($obj['bulletin_cb']) {
			$res_print .= "<br />".$msg["code_start"]." ".htmlentities($obj['bulletin_cb'],ENT_QUOTES, $charset)."\n";
			$code_cb_bulletin = $obj['bulletin_cb'];
		} 
	}	
	
	do_image($res_print, $code_cb_bulletin, 0 ) ;
	if ($opac_perio_a2z_show_bulletin_notice && $num_notice) {
		// Il y a une notice de bulletin
		$resultat_aff.= $res_print ;		
		global $opac_notices_depliable;
		global $seule;
		$memo_opac_notices_depliable=$opac_notices_depliable;
		$memo_seule=$seule;
		$opac_notices_depliable = 0;
		$seule=1;
		$resultat_aff.= pmb_bidi(aff_notice($num_notice,0,0)) ;		
		$opac_notices_depliable=$memo_opac_notices_depliable;
		$seule=$memo_seule;
	} else {
		// construction des dépouillements
		$requete = "SELECT * FROM analysis, notices, notice_statut WHERE analysis_bulletin='$id' AND notice_id = analysis_notice AND statut = id_notice_statut and ((notice_visible_opac=1 and notice_visible_opac_abon=0)".($_SESSION["user_code"]?" or (notice_visible_opac_abon=1 and notice_visible_opac=1)":"").") "; 
		$res = @mysql_query($requete, $dbh);
		if (mysql_num_rows($res)) {
			$depouill= "<h3>".$msg['bull_dep']."</h3>";			
			if ($opac_notices_depliable) $depouill .= $begin_result_liste;
			if ($opac_cart_allow) $depouill.="<a href=\"cart_info.php?id=".$id."&lvl=analysis&header=".rawurlencode(strip_tags($notice_header))."\" target=\"cart_info\" class=\"img_basket\">".$msg["cart_add_result_in"]."</a>"; 		
			$depouill.= "<blockquote>";
			while(($obj=mysql_fetch_array($res))) {
				$depouill.= pmb_bidi(aff_notice($obj["analysis_notice"]));
			}
			$depouill.= "</blockquote>";
		} //else $depouill = $msg["no_analysis"];
		
		$resultat_aff.= $res_print ;	
		$resultat_aff.= $depouill ;
		if ($notice3->visu_expl && (!$notice3->visu_expl_abon || ($notice3->visu_expl_abon && $_SESSION["user_code"])))	{	
			if (!$opac_resa_planning) {
				$resa_check=check_statut(0,$id) ;
				if ($resa_check) {
					$requete_resa = "SELECT count(1) FROM resa WHERE resa_idbulletin='$id'";
					$nb_resa_encours = mysql_result(mysql_query($requete_resa,$dbh), 0, 0) ;
					if ($nb_resa_encours) $message_nbresa = str_replace("!!nbresa!!", $nb_resa_encours, $msg["resa_nb_deja_resa"]) ;
				
					if (($_SESSION["user_code"] && $allow_book) && $opac_resa && !$popup_resa) {
						$ret_resa .= "<h3>".$msg["bulletin_display_resa"]."</h3>";
						if ($opac_max_resa==0 || $opac_max_resa>$nb_resa_encours) {
							if ($opac_resa_popup) $ret_resa .= "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
							else $ret_resa .= "<a href='./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
							$ret_resa .= $message_nbresa ;
						} else $ret_resa .= str_replace("!!nb_max_resa!!", $opac_max_resa, $msg["resa_nb_max_resa"]) ; 
						$ret_resa.= "<br />";
					} elseif (!($_SESSION["user_code"]) && $opac_resa && !$popup_resa) {
						// utilisateur pas connecté
						// préparation lien réservation sans être connecté
						$ret_resa .= "<h3>".$msg["bulletin_display_resa"]."</h3>";
						if ($opac_resa_popup) $ret_resa .= "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
						else $ret_resa .= "<a href='./do_resa.php?lvl=resa&id_bulletin=".$id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
						$ret_resa .= $message_nbresa ;
						$ret_resa .= "<br />";
					} elseif ($fonction=='notice_affichage_custom_bretagne') {
						if ($opac_resa_popup) $reserver = "<a href='#' onClick=\"if(confirm('".$msg["confirm_resa"]."')){w=window.open('./do_resa.php?lvl=resa&id_notice=".$this->notice_id."&oresa=popup','doresa','scrollbars=yes,width=500,height=600,menubar=0,resizable=yes'); w.focus(); return false;}else return false;\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
						else $reserver = "<a href='./do_resa.php?lvl=resa&id_notice=".$this->notice_id."&oresa=popup' onClick=\"return confirm('".$msg["confirm_resa"]."')\" id=\"bt_resa\">".$msg["bulletin_display_place_resa"]."</a>" ;
						$reservernbre = $message_nbresa ;
					} else $ret_resa = ""; 
					$resultat_aff.= pmb_bidi($ret_resa) ;
				}
			}
			
			if ($opac_show_exemplaires) {
				if($fonction=='notice_affichage_custom_bretagne')	$resultat_aff.= pmb_bidi(notice_affichage_custom_bretagne::expl_list("m",0,$id));
				else $resultat_aff.= pmb_bidi(notice_affichage::expl_list("m",0,$id,0));
			}
		}
		if ($notice3->visu_explnum && (!$notice3->visu_explnum_abon || ($notice3->visu_explnum_abon && $_SESSION["user_code"]))) { 
			if (($explnum = show_explnum_per_notice(0, $id, ''))) $resultat_aff.= pmb_bidi("<a name='docnum'><h3>".$msg["explnum"]."</h3></a>".$explnum);
		}	
	}
	mysql_free_result($res);
	
	$resultat_aff.= notice_affichage::autres_lectures (0,$id);
	return($resultat_aff);
}


function compare_date($date_debut="",$date_fin=""){
	
	global $dbh;
	
	if($date_debut && $date_fin){
		if($date_fin<$date_debut){
			$restrict = " and date_date between '".$date_fin."' and '".$date_debut."' ";
		} else if($date_fin == $date_debut) {
			$restrict = " and date_date='".$date_debut."' ";
		} else {
			$restrict = " and date_date between '".$date_debut."' and '".$date_fin."' ";
		}
	} else if($date_debut){
		$restrict = " and date_date >='".$date_debut."' ";
	} else if($date_fin){
		$restrict = " and date_date <='".$date_fin."' ";
	}
	
	return $restrict;
}

function affichage_liste_bulletins_normale($res) {
	global $charset, $dbh;
	$resultat_aff="";
	while(($tableau=mysql_fetch_array($res))) {
	
		$sql = "SELECT COUNT(1) FROM explnum WHERE explnum_bulletin='".$tableau["bulletin_id"]."'";
		$result = @mysql_query($sql, $dbh);
		$count=mysql_result($result, 0, 0);
		
		$titre="";
		if($count)$titre.= '<img src="./images/attachment.png">';		

		$titre.= $tableau['bulletin_numero'];
		if ($tableau['mention_date']) $titre.= pmb_bidi(" (".$tableau['mention_date'].")\n"); 
		elseif ($tableau['date_date']) $titre.= pmb_bidi(" (".formatdate($tableau['date_date']).")\n");
		if ($tableau['bulletin_titre']) $titre.= pmb_bidi(" : ".htmlentities($tableau['bulletin_titre'],ENT_QUOTES, $charset)."\n"); 
		
			
		//	($id,$titre,$contenu,$maximise=0) {	
		$resultat_aff.=gen_plus("bull_id_".$tableau['bulletin_id'],$titre, $this->get_bulletin($tableau['bulletin_id']) );	
			

	}
	return $resultat_aff;
}

function get_external_sources_list(){
	global $dbh;
	$es_list = array();
	$query = "select source_id,parameters from connectors_sources where id_connector = 'es_list'";
	$result = mysql_query($query,$dbh);
	if(mysql_num_rows($result)){
		while($row = mysql_fetch_object($result)){
			$source_params = unserialize($row->parameters);
			if($source_params['use_in_a2z']){
				$es_list[$row->source_id]=$source_params['es_selected'];
			}
		}
	}
	return $es_list;
}

function extract_external_id($id){
	return substr($id,strrpos($id,"_")+1);
}

function get_external_icon($id){
	$infos = explode("_",$id);
	$query = "select ico_notice,parameters from connectors_sources where source_id = ".$infos[1];
	$icon = "";
	$result =mysql_query($query);
	if(mysql_num_rows($result)){
		$row =mysql_fetch_object($result);
		$params = unserialize($row->parameters);
		if($params['source_as_origine'] == 0){
			$icon = "<img src='./images/".$row->ico_notice."'/>";
		}else{
			$query="select ico_notice,name from connectors_sources join external_count on connectors_sources.source_id = external_count.source_id where rid = ".$infos[2];
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$icon = "<img src='./images/".$row->ico_notice."'/>";
			}
		}
	}
	return $icon;
}

} # fin de définition de la classe 


} # fin de déclaration