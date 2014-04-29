<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: quotas_list.inc.php,v 1.15 2012-08-08 14:42:09 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//Gestion des �l�ments du type de quota

require_once($include_path."/templates/quotas.tpl.php");

//Liste des �l�ments
$parity=1;
$list_elements="<table>\n";
$elements=array();

if($qt->quota_type['ELEMENTS_LABEL']){
	$typ_quota_form=str_replace("!!quotas_elements_list!!",sprintf($msg['quotas_elements_list_label'],$qt->quota_type['ELEMENTS_LABEL']),$typ_quota_form);
	$typ_quota_form=str_replace("!!quotas_elements_conflicts!!",sprintf($msg['quotas_elements_conflicts_label'],$qt->quota_type['ELEMENTS_LABEL']),$typ_quota_form);
}else{
	$typ_quota_form=str_replace("!!quotas_elements_list!!",$msg['quotas_elements_list'],$typ_quota_form);
	$typ_quota_form=str_replace("!!quotas_elements_conflicts!!",$msg['quotas_elements_conflicts'],$typ_quota_form);
}
$typ_quota_form=str_replace("!!quotas_elements_list!!",$label,$typ_quota_form);


for($i=0;$i<count($qt->quota_type["QUOTAS"]);$i++) {
	$elts=explode(",",$qt->quota_type["QUOTAS"][$i]);
	$index=array();
	for ($j=0; $j<count($elts); $j++) {
		$index[]=$msg["quotas_by"]." ".$_quotas_elements_[$qt->get_element_by_name($elts[$j])]["COMMENT"];
	}
	if ($parity % 2) {
		$pair_impair = "even";
	} else {
		$pair_impair = "odd";
	}
	$parity += 1;
	$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./admin.php?categ=$categ&sub=$sub&elements=".$qt->get_elements_id_by_names($qt->quota_type["QUOTAS"][$i]).$query_compl."';\" ";
    $list_elements.="<tr class='$pair_impair' $tr_javascript style='cursor: pointer'><td><strong>".implode(" ".$msg["quotas_and"]." ",$index)."</strong></td></tr>\n"; 
    $elements[]=implode(" ".$msg["quotas_and"]." ",$index);
}
$list_elements.="</table>\n";

$typ_quota_form=str_replace("!!list_elements!!",$list_elements,$typ_quota_form);

//V�rification du formulaire
if ($first) {
	$min_value=abs($min_value);
	$max_value=abs($max_value);
	if(!$qt->quota_type['SPECIALCLASS']){
		$default_value=abs($default_value);
	}
	if (($qt->quota_type["MAX"])&&($qt->quota_type["MIN"])) {
		if ($min_value>$max_value) {
			error_message_history($msg["quotas_error"], $msg["quotas_error_max_lt_min"], 1);
			exit();
		}
	}
	if (($qt->quota_type["MAX"])&&($default_value>$max_value)&&(($max_value*1)!=0)) {
		error_message_history($msg["quotas_error"], $msg["quotas_error_default_gt_max"], 1);
		exit();
	}
	if (($qt->quota_type["MIN"])&&($default_value<$min_value)&&(($min_value*1)!=0)) {
		error_message_history($msg["quotas_error"], $msg["quotas_error_default_lt_min"], 1);
		exit();
	}
	$already=array();
	for ($i=0; $i<count($elements); $i++) {
		$as=array_search($conflict_list[$i],$already);
		if (($as!==NULL)&&($as!==FALSE)) {
			error_message_history($msg["quotas_error_order"], sprintf($msg["quotas_error_order_detail"],count($elements)), 1);
			exit();
		} else {
			$already[]=$conflict_list[$i];			
		}
	}
}

//Enregistrement des �l�ments dans la base
$recorded="";
if ($first==1) {
	//Nettoyage
	$requete="delete from ".$qt->table." where quota_type=".$qt->quota_type["ID"]." and constraint_type in ('MIN','MAX','DEFAULT','CONFLICT','PRIORITY','FORCE_LEND','MAX_QUOTA')";
	mysql_query($requete);
	//Max
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'MAX',0,'".$max_value."')";
	mysql_query($requete);
	//Min
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'MIN',0,'".$min_value."')";
	mysql_query($requete);
	//Default
	if($qt->quota_type['SPECIALCLASS']){
		global $class_path;
		require_once($class_path."/".$qt->quota_type['SPECIALCLASS'].".class.php");
		$default_value = call_user_func(array($qt->quota_type['SPECIALCLASS'],'get_storable_value'),$default_value);
	}
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'DEFAULT',0,'".$default_value."')";
	mysql_query($requete);
	//Conflict value
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'CONFLICT',0,'".$conflict_value."')";
	mysql_query($requete);
	//For�age du pr�t
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'FORCE_LEND',0,".$force_lend.")";
	mysql_query($requete);
	//Max_quota
	$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'MAX_QUOTA',0,".$max_quota.")";
	mysql_query($requete);
	//Priorit�s
	for ($i=0; $i<count($elements); $i++) {
		$id=$conflict_list[$i];
		$requete="insert into ".$qt->table." (quota_type,constraint_type,elements,value) values(".$qt->quota_type["ID"].",'PRIORITY',$id,'".$i."')";
		mysql_query($requete);
	}
	$recorded="<font color='#CC0000'><strong>".$msg["quotas_recorded"]."</strong></font>";
}

$typ_quota_form=str_replace("!!recorded!!",$recorded,$typ_quota_form);

//R�cup�ration des param�tres dans la base ou les valeurs par d�faut
$qt->get_values();

//Param�tres g�n�raux
if ($qt->quota_type["MAX"]) {
	$max_value_="
		<div class='row'><label class='etiquette' for='max_value'>".$msg["quotas_elements_max"]."</label></div>
		<div class='row'><input type='text'  class='saisie-5em' size='10' name='max_value' id='max_value' value='".htmlentities($max_value,ENT_QUOTES,$charset)."'/>";
	if ($qt->quota_type["MAX_QUOTA"]) {
		if ($max_quota) $checked="checked"; else $checked="";	
		$max_value_.="&nbsp;<span class='usercheckbox'><input type='checkbox' name='max_quota' value='1' $checked/>&nbsp;".htmlentities(sprintf($msg["quotas_max_quota"],$qt->get_title_by_elements_id($qt->get_elements_id_by_names($qt->quota_type["ENTITY"]))),ENT_QUOTES,$charset)."</span>";
	}
	$max_value_.="</div>
		";
}
if ($qt->quota_type["MIN"]) {
	$min_value_="
		<div class='row'><label class='etiquette' for='min_value'>".$msg["quotas_elements_min"]."</label></div>
		<div class='row'><input type='text'  class='saisie-5em' size='10' name='min_value' id='min_value' value='".htmlentities($min_value,ENT_QUOTES,$charset)."'/>";
	$min_value_.="</div>
		";
}


//label valeur par d�faut
if($qt->quota_type["DEFAULT_VALUE_LABEL"]){
	$label = $qt->quota_type["DEFAULT_VALUE_LABEL"];
	//TODO
	
}else{
	$label = sprintf($msg["quotas_elements_default"],htmlentities($qt->quota_type["SHORT_COMMENT"],ENT_QUOTES,$charset));
}

//valeur par d�faut
if($qt->quota_type["SPECIALCLASS"]){
	require_once($class_path."/".$qt->quota_type['SPECIALCLASS'].".class.php");
	$default_value_form= call_user_func(array($qt->quota_type['SPECIALCLASS'],'get_quota_form'),"default_value",stripslashes($default_value));
			
}else{
	$default_value_form = "<input type='text' class='saisie-5em' size='10' name='default_value' id='default_value' value='".htmlentities($default_value,ENT_QUOTES,$charset)."'/>";
}


$typ_quota_form=str_replace("!!max_value!!",$max_value_,$typ_quota_form);
$typ_quota_form=str_replace("!!min_value!!",$min_value_,$typ_quota_form);
$typ_quota_form=str_replace("!!default_value!!",$default_value_form,$typ_quota_form);
$typ_quota_form=str_replace("!!short_type_comment!!",$label,$typ_quota_form);

//gestion des conflits
if($qt->quota_type['CONFLIT_MAX']){
	$conflit_max = "<div class='row'><input type='radio' name='conflict_value' id='conflict_value' value='1' !!checked_1!! onClick=\"document.getElementById('conflict_order').style.display='none';\"> ".$msg['quotas_plus_grand']."</div>";
}else{
	$conflit_max = "";
}
$typ_quota_form=str_replace("!!conflit_max!!",$conflit_max,$typ_quota_form);
if($qt->quota_type['CONFLIT_MIN']){
	$conflit_min = "<div class='row'><input type='radio' name='conflict_value' value='2' !!checked_2!! onClick=\"document.getElementById('conflict_order').style.display='none';\"> ".$msg['quotas_plus_petit']."</div>";	
}else{
	$conflit_min = "";
}
$typ_quota_form=str_replace("!!conflit_min!!",$conflit_min,$typ_quota_form);
for ($i=1; $i<=4; $i++) {
	if ($conflict_value==$i) $checked="checked"; else $checked="";
	$typ_quota_form=str_replace("!!checked_$i!!",$checked,$typ_quota_form);
}

//liste des �l�ments en cas de confilt
$conflict_list_elements="";
for ($i=0; $i<count($elements); $i++) {
	if ($i==0) $conflict_list_elements=$msg["quotas_priority"]." "; else $conflict_list_elements.=$msg["quotas_then"]." ";
	$conflict_list_elements.="<select name='conflict_list[".$i."]'>\n";
	for ($j=0; $j<count($elements); $j++) {
		$id=$qt->get_elements_id_by_names($qt->quota_type["QUOTAS"][$j]);
		if ($id==$conflict_list[$i]) $checked="selected"; else $checked="";
		$conflict_list_elements.="<option value='$id' $checked>".htmlentities($elements[$j],ENT_QUOTES,$charset)."</option>\n";
	}
	$conflict_list_elements.="</select><br />";
}

$typ_quota_form=str_replace("!!conflict_list_elements!!",$conflict_list_elements,$typ_quota_form);

if ($qt->quota_type["FORCELEND"]) {
	$typ_quota_form=str_replace("!!force_lend!!","<div class='row'>".sprintf($msg["quotas_force_lend"],$qt->quota_type["COMMENTFORCELEND"])."&nbsp;<input type='checkbox' value='1' name='force_lend' !!checked_force_lend!!/></div>",$typ_quota_form);
	if ($force_lend) $checked_force_lend="checked"; else $checked_force_lend="";
	$typ_quota_form=str_replace("!!checked_force_lend!!",$checked_force_lend,$typ_quota_form);
} else
	$typ_quota_form=str_replace("!!force_lend!!","",$typ_quota_form);

if ($conflict_value==4) $typ_quota_form.="<script>document.getElementById('conflict_order').style.display='';</script>\n";

print $typ_quota_form;

?>