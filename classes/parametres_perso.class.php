<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: parametres_perso.class.php,v 1.57 2014-01-31 08:20:23 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

//Gestion des champs personalis�s
require_once($include_path."/templates/parametres_perso.tpl.php");
require_once($include_path."/parser.inc.php");
require_once($include_path."/fields_empr.inc.php");
require_once($include_path."/datatype.inc.php");

class parametres_perso {
	
	var $prefix;
	var $no_special_fields;
	var $error_message;
	var $values;
	var $base_url;
	var $t_fields;
	var $option_visibilite=array();
	/* deux valeurs possible block (affich�) ou none (masqu�) 
	$option_visibilite["multiple"]="block";
	$option_visibilite["obligatoire"]="block";
	$option_visibilite["exclusion"]="block";
	$option_visibilite["export"]="block";
	$option_visibilite["search"]="block";
	*/
	//Cr�ateur : passer dans $prefix le type de champs persos et dans $base_url l'url a appeller pour les formulaires de gestion	
	function parametres_perso($prefix,$base_url="",$option_visibilite=array()) {
		global $_custom_prefixe_;

		/*if(!count($option_visibilite)){
			$option_visibilite["multiple"]="block";
			$option_visibilite["obligatoire"]="block";
			$option_visibilite["exclusion"]="block";
			$option_visibilite["export"]="block";
			if($search_actif){
				$option_visibilite["search"]="block";
			}else{
				$option_visibilite["search"]="none";
			}
		}*/
		
		$this->option_visibilite=$option_visibilite;
		
		$this->prefix=$prefix;
		$this->base_url=$base_url;
		$_custom_prefixe_=$prefix;
		
		//Lecture des champs
		$this->no_special_fields=0;
		$this->t_fields=array();
		$requete="select idchamp, name, titre, type, datatype, obligatoire, options, multiple, search, export, exclusion_obligatoire, pond, opac_sort from ".$this->prefix."_custom order by ordre";

		$resultat=mysql_query($requete);
		if (mysql_num_rows($resultat)==0)
			$this->no_special_fields=1;
		else {
			while ($r=mysql_fetch_object($resultat)) {
				$this->t_fields[$r->idchamp]["DATATYPE"]=$r->datatype;
				$this->t_fields[$r->idchamp]["NAME"]=$r->name;
				$this->t_fields[$r->idchamp]["TITRE"]=$r->titre;
				$this->t_fields[$r->idchamp]["TYPE"]=$r->type;
				$this->t_fields[$r->idchamp]["OPTIONS"]=$r->options;
				$this->t_fields[$r->idchamp]["MANDATORY"]=$r->obligatoire;
				$this->t_fields[$r->idchamp]["OPAC_SHOW"]=$r->multiple;
				$this->t_fields[$r->idchamp]["SEARCH"]=$r->search;
				$this->t_fields[$r->idchamp]["EXPORT"]=$r->export;
				$this->t_fields[$r->idchamp]["EXCLUSION"]=$r->exclusion_obligatoire;
				$this->t_fields[$r->idchamp]["POND"]=$r->pond;
				$this->t_fields[$r->idchamp]["OPAC_SORT"]=$r->opac_sort;
			}
		}
	}
	
	//Affichage de l'�cran de gestion des param�tres perso (la liste de tous les champs d�finis)
	function show_field_list() {
		global $type_list_empr;
		global $datatype_list;
		global $form_list;
		global $msg;
	
		$res="";		
		$requete="select idchamp, name, titre, type, datatype, multiple, obligatoire, ordre ,search, export,exclusion_obligatoire, opac_sort from ".$this->prefix."_custom order by ordre";
		$resultat=mysql_query($requete);
		/*if(!$resultat)
		{
			echo "ya pas de res : ".mysql_num_rows($resultat)."<br />";
		}
		echo "nombre : ".mysql_num_rows($resultat)."<br />";*/
		if (mysql_num_rows($resultat)==0) {
			$res="<br /><br />".$msg["parperso_no_field"]."<br />";
			$form_list=str_replace("!!liste_champs_perso!!",$res,$form_list);
			$form_list=str_replace("!!base_url!!",$this->base_url,$form_list);
			return $form_list;
		} else {
			$res="<table width=100%>\n";
			$res.="<tr><th></th><th>".$msg["parperso_field_name"]."</th><th>".$msg["parperso_field_title"]."</th><th>".$msg["parperso_input_type"]."</th><th>".$msg["parperso_data_type"]."</th>";
			if($this->option_visibilite["multiple"] == "block") $res.= "<th>".((strpos($this->prefix,"gestfic")!==false) ? $msg["parperso_fiche_visibility"] : $msg["parperso_opac_visibility"])."</th>" ;
			if($this->option_visibilite["opac_sort"] == "block") $res.= "<th>".$msg["parperso_opac_sort"]."</th>" ;
			if($this->option_visibilite["obligatoire"] == "block") $res.= "<th>".$msg["parperso_mandatory"]."</th>" ;
			if($this->option_visibilite["search"] == "block") $res.= "<th>".$msg["parperso_field_search_tableau"]."</th>" ;
			if($this->option_visibilite["export"] == "block") $res.= "<th>".$msg["parperso_exportable"]."</th>" ;
			if($this->option_visibilite["exclusion"] == "block") $res.= "<th>".$msg["parperso_exclusion_entete"]."</th></tr>\n" ;
			else $res .= "</tr>\n";
			$parity=1;
			$n=0;
			while ($r=mysql_fetch_object($resultat)) {
				if ($parity % 2) {
					$pair_impair = "even";
				} else {
					$pair_impair = "odd";
				}
				$parity+=1;
				$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
				$action_td=" onmousedown=\"document.location='".$this->base_url."&action=edit&id=$r->idchamp';\" ";
				$res.="<tr class='$pair_impair' style='cursor: pointer' $tr_javascript>";
				$res.="<td>";
				$res.="<input type='button' class='bouton_small' value='-' onClick='document.location=\"".$this->base_url."&action=up&id=".$r->idchamp."\"'/></a><input type='button' class='bouton_small' value='+' onClick='document.location=\"".$this->base_url."&action=down&id=".$r->idchamp."\"'/>";
				$res.="</td>";
				$res.="<td $action_td><b>".$r->name."</b></td><td $action_td>".$r->titre."</td><td $action_td>".$type_list_empr[$r->type]."</td><td $action_td>".$datatype_list[$r->datatype]."</td>";
				if($this->option_visibilite["multiple"] == "block") { 
					$res.="<td $action_td>";
					if ($r->multiple==1) $res.=$msg["40"]; else $res.=$msg["39"];
					$res.="</td>";
				}
				if($this->option_visibilite["opac_sort"] == "block") { 	
					$res.="<td $action_td>";
					if ($r->opac_sort==1) $res.=$msg["40"]; else $res.=$msg["39"];
					$res.="</td>";
				}
				if($this->option_visibilite["obligatoire"] == "block") { 
					$res.="<td $action_td>";
					if ($r->obligatoire==1) $res.=$msg["40"]; else $res.=$msg["39"];
					$res.="</td>";
				}
				if($this->option_visibilite["search"] == "block") { 
					$res.="<td $action_td>";
					if ($r->search==1) $res.=$msg["40"]; else $res.=$msg["39"];
					$res.="</td>";
				}
				if($this->option_visibilite["export"] == "block") { 	
					$res.="<td $action_td>";
					if ($r->export==1) $res.=$msg["40"]; else $res.=$msg["39"];
					$res.="</td>";
				}
				if($this->option_visibilite["exclusion"] == "block"){
					$res.="<td $action_td>";
					if ($r->exclusion_obligatoire==1) $res.=$msg["40"]; 
					else $res.=$msg["39"];
					$res.="</td>";
				}
				$res.="</tr>\n";
			}
			$res.="</table>";
			$form_list=str_replace("!!liste_champs_perso!!",$res,$form_list);
			$form_list=str_replace("!!base_url!!",$this->base_url,$form_list);
			return $form_list;
		}
	}
		
	function gen_liste_field($select_name="p_perso_liste",$selected_id=0,$msg_no_select) {
		global $msg;
	
		$onchange="";
		$requete="select idchamp, name, titre, type, datatype, multiple, obligatoire, ordre ,search, export,exclusion_obligatoire, opac_sort from ".$this->prefix."_custom order by ordre";
		return gen_liste ($requete, "idchamp", "titre", $select_name, $onchange, $selected_id, 0, $msg["parperso_no_field"], 0,$msg_no_select, 0) ;
	}
	
	//Affichage du formulaire d'�dition d'un champ perso
	function show_edit_form($idchamp=0) {
		global $charset;
		global $type_list_empr;
		global $datatype_list;
		global $form_edit;
		global $include_path;
		global $msg;
				
		if ($idchamp!=0 and $idchamp!="") {
			$requete="select idchamp, name, titre, type, datatype, options, multiple, obligatoire, ordre, search, export, exclusion_obligatoire, pond, opac_sort from ".$this->prefix."_custom where idchamp=$idchamp";
			$resultat=mysql_query($requete) or die(mysql_error());
			$r=mysql_fetch_object($resultat);
			
			$name=$r->name;
			$titre=htmlentities($r->titre,ENT_QUOTES,$charset);
			$type=$r->type;
			$datatype=$r->datatype;
			$options=htmlentities($r->options,ENT_QUOTES,$charset);
			$multiple=$r->multiple;
			$obligatoire=$r->obligatoire;
			$ordre=$r->ordre;
			$search=$r->search;
			$export=$r->export;
			$exclusion=$r->exclusion_obligatoire;
			$pond=$r->pond;
			$opac_sort=$r->opac_sort;
			$form_edit=str_replace("!!form_titre!!",sprintf($msg["parperso_field_edition"],$name),$form_edit);
			$form_edit=str_replace("!!action!!","update",$form_edit);
			
			if ($r->options!="") {
				$param=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$r->options, "OPTIONS");
				$form_edit=str_replace("!!for!!",$param["FOR"],$form_edit);
			} else {
				$form_edit=str_replace("!!for!!","",$form_edit);
			}
			$form_edit=str_replace("!!supprimer!!","&nbsp;<input type='button' class='bouton' value='".$msg["63"]."' onClick=\"if (confirm('".$msg["parperso_delete_field"]."')) { this.form.action.value='delete'; this.form.submit();} else return false;\">",$form_edit);
		} else {
			$form_edit=str_replace("!!form_titre!!",$msg["parperso_create_new_field"],$form_edit);
			$form_edit=str_replace("!!action!!","create",$form_edit);
			$form_edit=str_replace("!!for!!","",$form_edit);
			$form_edit=str_replace("!!supprimer!!","",$form_edit);
		}
		
		$onclick="openPopUp('".$include_path."/options_empr/options.php?name=&type='+this.form.type.options[this.form.type.selectedIndex].value+'&_custom_prefixe_=".$this->prefix."','options',550,600,-2,-2,'menubars=no,resizable=yes,scrollbars=yes');";
		$form_edit=str_replace("!!onclick!!",$onclick,$form_edit);
		
		$form_edit=str_replace("!!idchamp!!",$idchamp,$form_edit);
		$form_edit=str_replace("!!name!!",$name,$form_edit);
		$form_edit=str_replace("!!titre!!",$titre,$form_edit);
		$form_edit=str_replace("!!pond!!",$pond,$form_edit);	
		
		//Liste des types
		$t_list="<select name='type'>\n";
		reset($type_list_empr);
		while (list($key,$val)=each($type_list_empr)) {
			$t_list.="<option value='".$key."'";
			if ($type==$key) $t_list.=" selected";
			$t_list.=">".htmlentities($val,ENT_QUOTES, $charset)."</option>\n";
		}
		$t_list.="</select>\n";
		$form_edit=str_replace("!!type_list!!",$t_list,$form_edit);
		
		//Liste des types de donn�es
		$t_list="<select name='datatype'>\n";
		reset($datatype_list);
		while (list($key,$val)=each($datatype_list)) {
			$t_list.="<option value='".$key."'";
			if ($datatype==$key) $t_list.=" selected";
			$t_list.=">".htmlentities($val,ENT_QUOTES, $charset)."</option>\n";
		}
		$t_list.="</select>\n";
		$form_edit=str_replace("!!datatype_list!!",$t_list,$form_edit);
		
		$form_edit=str_replace("!!options!!",$options,$form_edit);
		
		if ($multiple==1) $f_multiple="checked"; else $f_multiple="";
		$form_edit=str_replace("!!multiple_checked!!",$f_multiple,$form_edit);
		
		if ($obligatoire==1) $f_obligatoire="checked"; else $f_obligatoire="";
		$form_edit=str_replace("!!obligatoire_checked!!",$f_obligatoire,$form_edit);
		
		if ($search==1) $f_search="checked"; else $f_search="";
		$form_edit=str_replace("!!search_checked!!",$f_search,$form_edit);
		
		if ($export==1) $f_export="checked"; else $f_export="";
		$form_edit=str_replace("!!export_checked!!",$f_export,$form_edit);
		
		if ($exclusion==1) $f_exclusion="checked"; else $f_exclusion="";
		$form_edit=str_replace("!!exclusion_checked!!",$f_exclusion,$form_edit);
		
		if ($opac_sort==1) $f_opac_sort="checked"; else $f_opac_sort="";
		$form_edit=str_replace("!!opac_sort_checked!!",$f_opac_sort,$form_edit);
		
		foreach ( $this->option_visibilite as $key => $value ) {
       		$form_edit=str_replace("!!".$key."_visible!!",$value,$form_edit);
		}
		
		if(strpos($this->prefix,"gestfic")!==false)
			$form_edit = str_replace("!!msg_visible!!",$msg['parperso_fiche_visibility'],$form_edit);
		else $form_edit = str_replace("!!msg_visible!!",$msg['parperso_opac_visibility'],$form_edit);
		
		$form_edit=str_replace("!!ordre!!",$ordre,$form_edit);
		$form_edit=str_replace("!!base_url!!",$this->base_url,$form_edit);
		
		echo $form_edit;
	}

	//Cr�ation d'une erreur si options non valides ou formulaires de cr�ation d'un champ mal rempli
	function make_error($message) {
		global $msg;
		error_message_history($msg["540"],$message, 1);
		exit();
	}	

	//Validation du formulaire de cr�ation
	function check_form() {
		global $action,$idchamp;
		global $name,$titre,$type,$_for,$multiple,$obligatoire,$exclusion,$msg,$search,$export,$pond,$opac_sort;
		//V�rification conformit� du champ name
		if (!preg_match("/^[A-Za-z][A-Za-z0-9_]*$/",$name)) $this->make_error(sprintf($msg["parperso_check_field_name"],$name));
		//On v�rifie que le champ name ne soit pas d�j� existant
		if ($action == "update") $requete="select idchamp from ".$this->prefix."_custom where name='$name' and idchamp<>$idchamp";
		else $requete="select idchamp from ".$this->prefix."_custom where name='$name'";
		$resultat=mysql_query($requete);
		if (mysql_num_rows($resultat) > 0) $this->make_error(sprintf($msg["parperso_check_field_name_already_used"],$name));
		if ($titre=="") $titre=$name;
		if ($_for!=$type) $this->make_error($msg["parperso_check_type"]);
		if ($multiple=="") $multiple=0;
		if ($obligatoire=="") $obligatoire=0;
		if($search=="") $search=0;
		if($export=="") $export=0;
		if($exclusion=="") $exclusion=0;
		if($pond=="") $pond=1;
		if($opac_sort=="") $opac_sort=0;
	}	
	
	//Validation des valeurs des champs soumis lors de la saisie d'une fichie emprunteur ou autre...
	function check_submited_fields() {
		global $chk_list_empr,$charset;
		
		$nberrors=0;
		$this->error_message="";
		
		if (!$this->no_special_fields) {
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				$check_message="";
				$field=array();
				$field["ID"]=$key;
				$field["NAME"]=$this->t_fields[$key]["NAME"];
				$field["MANDATORY"]=$this->t_fields[$key]["MANDATORY"];
				$field["ALIAS"]=$this->t_fields[$key]["TITRE"];
				$field["OPTIONS"][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$key]["OPTIONS"], "OPTIONS");
				$field["DATATYPE"]=$this->t_fields[$key]["DATATYPE"];
				$field["PREFIX"]=$this->prefix;
				$field["SEARCH"]=$this->t_fields[$key]["SEARCH"];
				$field["EXPORT"]=$this->t_fields[$key]["EXPORT"];
				$field["EXCLUSION"]=$this->t_fields[$key]["EXCLUSION"];
				$field["OPAC_SORT"]=$this->t_fields[$key]["OPAC_SORT"];
				eval("\$field[VALUES]=\$".$val["NAME"].";");
				eval($chk_list_empr[$this->t_fields[$key]["TYPE"]]."(\$field,\$check_message);");
				if ($check_message!="") {
					$nberrors++;
					$this->error_message.="<p>".$check_message."</p>";
				}
			}
		}
		return $nberrors;
	}
	
	//Presence ou nom de valeurs lors de la saisie
	function presence_submited_fields() {
		global $chk_list_empr,$charset;

		if (!$this->no_special_fields) {
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				$field_name = $this->t_fields[$key]["NAME"];
				global $$field_name;
				$field = $$field_name;
				if ($field[0]) 
					return true;					
			}
		}
		return false;
	}	
	
	//Presence ou nom de valeurs lors de la saisie dans les champs exclus
	function presence_exclusion_fields() {
		global $chk_list_empr,$charset;
		//global $exclu_tab;
		$exclu_tab=array();
		if (!$this->no_special_fields) {
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				if($this->t_fields[$key]["EXCLUSION"])
					$exclu_tab[] = $this->t_fields[$key];			
			}
			if(is_array($exclu_tab)) {
				while (list($key,$val)=each($exclu_tab)) {
					$field_name = $exclu_tab[$key]["NAME"];
					global $$field_name;
					$field = $$field_name;
					if ($field[0]) 
						return true;					
				}
			}
			return false;
		}
		return false;
	}	
	
	// retourne la liste des valeurs des champs perso cherchable d'une notice 
	function get_fields_recherche($id) {
		$return_val='';
		$this->get_values($id);		
		foreach ( $this->values as $field_id => $vals ) {
			if($this->t_fields[$field_id]["SEARCH"] ) {
				foreach ( $vals as $value ) {
				 	$return_val.=$this->get_formatted_output(array($value),$field_id).' ';//Sa valeur
				} 
			}	 
		}
		return stripslashes($return_val);	
	}
	
	// retourne la liste des valeurs des champs perso cherchable d'une notice sous forme d'un tableau par champ perso 
	function get_fields_recherche_mot($id) {
		$return_val=array();
		$this->get_values($id);		
		foreach ( $this->values as $field_id => $vals ) {
			if($this->t_fields[$field_id]["SEARCH"] ) {
				foreach ( $vals as $value ) {
				 	$return_val[$field_id].=stripslashes($this->get_formatted_output(array($value),$field_id)).' ';//Sa valeur
				} 
			}	 
		}
		return $return_val;	
	}
	
	// retourne la liste des valeurs des champs perso cherchable d'une notice sous forme d'un tableau par champ perso 
	function get_fields_recherche_mot_array($id) {
		$return_val=array();
		$this->get_values($id);		
		foreach ( $this->values as $field_id => $vals ) {
			if($this->t_fields[$field_id]["SEARCH"] ) {
				foreach ( $vals as $value ) {
				 	$return_val[$field_id][]=stripslashes($this->get_formatted_output(array($value),$field_id)).' ';//Sa valeur
				} 
			}	 
		}
		return $return_val;	
	}
	
	//Enregistrement des champs perso soumis lors de la saisie d'une fichie emprunteur ou autre...
	function rec_fields_perso($id) {
		//Enregistrement des champs personalis�s
		$requete="delete from ".$this->prefix."_custom_values where ".$this->prefix."_custom_origine=$id";
		mysql_query($requete);
		reset($this->t_fields);
		while (list($key,$val)=each($this->t_fields)) {
			$name=$val["NAME"];
			global $$name;
			$value=$$name;
			for ($i=0; $i<count($value); $i++) {
				if ($value[$i]!=="") {
					$requete="insert into ".$this->prefix."_custom_values (".$this->prefix."_custom_champ,".$this->prefix."_custom_origine,".$this->prefix."_custom_".$val["DATATYPE"].") values($key,$id,'".$value[$i]."')";
					mysql_query($requete);
				}
			}
		}
	}
	
	function read_form_fields_perso($name) {
		//Enregistrement des champs personalis�s
		$return_val='';
		reset($this->t_fields);
		while (list($key,$val)=each($this->t_fields)) {
			if($val["NAME"] == $name) {
				global $$name;
				$value=$$name;
				for ($i=0; $i<count($value); $i++) {
					$return_val.=$value[$i];
				}
			}	
		}
		return $return_val;
	}	
	function read_base_fields_perso($name,$id) {
		global $val_list_empr;
		global $charset;
		
		$perso=array();
		//R�cup�ration des valeurs stock�es
		$this->get_values($id);
		if (!$this->no_special_fields) {
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				if($val["NAME"] == $name){
					for ($i=0; $i<count($this->values[$key]); $i++) {			
						$return_val.=$this->values[$key][$i];
					}		
				}	
			}
			
		}	
		
		return $return_val;
	}
	
	function read_base_fields_perso_values($name,$id) {
		global $val_list_empr;
		global $charset;
	
		$perso=array();
		//R�cup�ration des valeurs stock�es
		$this->get_values($id);
		if (!$this->no_special_fields) {
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				if($val["NAME"] == $name){
					for ($i=0; $i<count($this->values[$key]); $i++) {
						$perso[]=$this->values[$key][$i];
					}
				}
			}				
		}	
		return $perso;
	}
		
	//R�cup�ration des valeurs stock�es dans les base pour un emprunteur ou autre
	function get_values($id) {
		//R�cup�ration des valeurs stock�es pour l'emprunteur
		if ((!$this->no_special_fields)&&($id)) {
			$this->values=$this->list_values=array();
			
			$requete="select ".$this->prefix."_custom_champ,".$this->prefix."_custom_origine,".$this->prefix."_custom_small_text, ".$this->prefix."_custom_text, ".$this->prefix."_custom_integer, ".$this->prefix."_custom_date, ".$this->prefix."_custom_float from ".$this->prefix."_custom_values where ".$this->prefix."_custom_origine=".$id;
			$resultat=mysql_query($requete);
			while ($r=mysql_fetch_array($resultat)) {
				$this->values[$r[$this->prefix."_custom_champ"]][]=$r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]];
				$this->list_values[]=$r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]];
			}
		} else $this->values=$this->list_values=array();
	}
	
	//Affichage des champs � saisir dans le formulaire de modification/cr�ation d'un emprunteur ou autre
	function show_editable_fields($id,$from_z3950=false) {
		global $aff_list_empr,$charset;
		$perso=array();
		
		if (!$this->no_special_fields) {
			if(!$from_z3950){
				$this->get_values($id);
			}
			$check_scripts="";
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				$t=array();
				$t["NAME"]=$val["NAME"];
				$t["TITRE"]=$val["TITRE"];
			
				$field=array();
				$field["ID"]=$key;
				$field["NAME"]=$this->t_fields[$key]["NAME"];
				$field["MANDATORY"]=$this->t_fields[$key]["MANDATORY"];				
				$field["SEARCH"]=$this->t_fields[$key]["SEARCH"];
				$field["EXPORT"]=$this->t_fields[$key]["EXPORT"];	
				$field["EXCLUSION"]=$this->t_fields[$key]["EXCLUSION"];	
				$field["OPAC_SORT"]=$this->t_fields[$key]["OPAC_SORT"];	
				$field["ALIAS"]=$this->t_fields[$key]["TITRE"];
				$field["DATATYPE"]=$this->t_fields[$key]["DATATYPE"];
				$field["OPTIONS"][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$key]["OPTIONS"], "OPTIONS");
				$field["VALUES"]=$this->values[$key];
				$field["PREFIX"]=$this->prefix;
				eval("\$aff=".$aff_list_empr[$this->t_fields[$key][TYPE]]."(\$field,\$check_scripts);");
				$t["AFF"]=$aff;
				$t["NAME"]=$field["NAME"];
				$perso["FIELDS"][]=$t;
			}
		
			//Compilation des javascripts de validit� renvoy�s par les fonctions d'affichage
			$check_scripts="<script>function cancel_submit(message) { alert(message); return false;}\nfunction check_form() {\n".$check_scripts."\nreturn true;\n}\n</script>";
			$perso["CHECK_SCRIPTS"]=$check_scripts;
		} else 
			$perso["CHECK_SCRIPTS"]="<script>function check_form() { return true; }</script>";
		return $perso;
	}
	
	//Affichage des champs en lecture seule pour visualisation d'un fiche emprunteur ou autre...
	function show_fields($id) {
		global $val_list_empr;
		global $charset;
		$perso=array();
		//R�cup�ration des valeurs stock�es pour l'emprunteur
		$this->get_values($id);
		if (!$this->no_special_fields) {
			//Affichage champs persos
			$c=0;
			reset($this->t_fields);
			while (list($key,$val)=each($this->t_fields)) {
				$t=array();
				$t["TITRE"]="<b>".htmlentities($val["TITRE"],ENT_QUOTES,$charset)." : </b>";
				$t["OPAC_SHOW"]=$val["OPAC_SHOW"];
				$field=array();
				$field["ID"]=$key;
				$field["NAME"]=$this->t_fields[$key]["NAME"];
				$field["MANDATORY"]=$this->t_fields[$key]["MANDATORY"];
				$field["SEARCH"]=$this->t_fields[$key]["SEARCH"];
				$field["EXPORT"]=$this->t_fields[$key]["EXPORT"];		
				$field["EXCLUSION"]=$this->t_fields[$key]["EXCLUSION"];	
				$field["OPAC_SORT"]=$this->t_fields[$key]["OPAC_SORT"];	
				$field["ALIAS"]=$this->t_fields[$key]["TITRE"];
				$field["DATATYPE"]=$this->t_fields[$key]["DATATYPE"];
				$field["OPTIONS"][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$key]["OPTIONS"], "OPTIONS");
				$field["VALUES"]=$this->values[$key];
				$field["PREFIX"]=$this->prefix;
				$aff=$val_list_empr[$this->t_fields[$key]["TYPE"]]($field,$this->values[$key]);
				if (is_array($aff) && $aff[ishtml] == true)$t["AFF"] = $aff["value"];
				else $t["AFF"]=htmlentities($aff,ENT_QUOTES,$charset);
				$t["NAME"]=$field["NAME"];
				$t["ID"]=$field["ID"];
				$perso["FIELDS"][]=$t;
			}
		}
		return $perso;
	}
	
	function get_formatted_output($values,$field_id) {
		global $val_list_empr,$charset;
		
		$field=array();
		$field["ID"]=$field_id;
		$field["NAME"]=$this->t_fields[$field_id]["NAME"];
		$field["MANDATORY"]=$this->t_fields[$field_id]["MANDATORY"];
		$field["SEARCH"]=$this->t_fields[$field_id]["SEARCH"];	
		$field["EXPORT"]=$this->t_fields[$field_id]["EXPORT"];
		$field["EXCLUSION"]=$this->t_fields[$field_id]["EXCLUSION"];
		$field["OPAC_SORT"]=$this->t_fields[$field_id]["OPAC_SORT"];
		$field["ALIAS"]=$this->t_fields[$field_id]["TITRE"];
		$field["DATATYPE"]=$this->t_fields[$field_id]["DATATYPE"];
		$field["OPTIONS"][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$field_id]["OPTIONS"], "OPTIONS");
		$field["VALUES"]=$values;
		$field["PREFIX"]=$this->prefix;
		$aff=$val_list_empr[$this->t_fields[$field_id]["TYPE"]]($field,$values);
		if(is_array($aff)) return $aff['withoutHTML']; 
		else return $aff;
	}

	//Suppression de la base des valeurs d'un emprunteur ou autre...
	function delete_values($id) {
		$requete = "DELETE FROM ".$this->prefix."_custom_values where ".$this->prefix."_custom_origine=$id";
		$res = mysql_query($requete);
	}
	
	//Gestion des actions en administration
	function proceed() {
		global $action;
		global $name,$titre,$type,$datatype,$_options,$multiple,$obligatoire,$search,$export,$exclusion,$ordre,$idchamp,$id,$pond,$opac_sort;
		
		switch ($action) {
			case "nouv":
				$this->show_edit_form();
				break;
			case "edit":
				$this->show_edit_form($id);
				break;
			case "create":
				$this->check_form();
				$requete="select max(ordre) from ".$this->prefix."_custom";
				$resultat=mysql_query($requete);
				if (mysql_num_rows($resultat)!=0)
					$ordre=mysql_result($resultat,0,0)+1;
				else
					$ordre=1;
	
				$requete="insert into ".$this->prefix."_custom set name='$name', titre='$titre', type='$type', datatype='$datatype', options='$_options', multiple=$multiple, obligatoire=$obligatoire, ordre=$ordre, search=$search, export=$export, exclusion_obligatoire=$exclusion, opac_sort=$opac_sort ";
				mysql_query($requete);
				echo $this->show_field_list();
				break;
			case "update":
				$this->check_form();
				$requete="update ".$this->prefix."_custom set name='$name', titre='$titre', type='$type', datatype='$datatype', options='$_options', multiple=$multiple, obligatoire=$obligatoire, ordre=$ordre, search=$search, export=$export, exclusion_obligatoire=$exclusion, pond=$pond, opac_sort=$opac_sort where idchamp=$idchamp";
				mysql_query($requete);
				echo $this->show_field_list();
				break;
			case "up":
				$requete="select ordre from ".$this->prefix."_custom where idchamp=$id";
				$resultat=mysql_query($requete);
				$ordre=mysql_result($resultat,0,0);
				$requete="select max(ordre) as ordre from ".$this->prefix."_custom where ordre<$ordre";
				$resultat=mysql_query($requete);
				$ordre_max=@mysql_result($resultat,0,0);
				if ($ordre_max) {
					$requete="select idchamp from ".$this->prefix."_custom where ordre=$ordre_max limit 1";
					$resultat=mysql_query($requete);
					$idchamp_max=mysql_result($resultat,0,0);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre_max."' where idchamp=$id";
					mysql_query($requete);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre."' where idchamp=".$idchamp_max;
					mysql_query($requete);
				}
				echo $this->show_field_list();
				break;
			case "down":
				$requete="select ordre from ".$this->prefix."_custom where idchamp=$id";
				$resultat=mysql_query($requete);
				$ordre=mysql_result($resultat,0,0);
				$requete="select min(ordre) as ordre from ".$this->prefix."_custom where ordre>$ordre";
				$resultat=mysql_query($requete);
				$ordre_min=@mysql_result($resultat,0,0);
				if ($ordre_min) {
					$requete="select idchamp from ".$this->prefix."_custom where ordre=$ordre_min limit 1";
					$resultat=mysql_query($requete);
					$idchamp_min=mysql_result($resultat,0,0);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre_min."' where idchamp=$id";
					mysql_query($requete);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre."' where idchamp=".$idchamp_min;
					mysql_query($requete);
				}
				echo $this->show_field_list();
				break;
			case "delete":
				$requete="delete from ".$this->prefix."_custom where idchamp=$idchamp";
				mysql_query($requete);
				$requete="delete from ".$this->prefix."_custom_values where ".$this->prefix."_custom_champ=$idchamp";
				mysql_query($requete);
				$requete="delete from ".$this->prefix."_custom_lists where ".$this->prefix."_custom_champ=$idchamp";
				mysql_query($requete);
				echo $this->show_field_list();
				break;
			default:
				echo $this->show_field_list();
		}
	}
	
	function get_pond($id){
		return $this->t_fields[$id]["POND"];
	}
	
	function get_ajax_list($name, $start) {
		global $charset,$dbh;

		$values=array();
		reset($this->t_fields);
		while (list($key,$val)=each($this->t_fields)) {
			if($val['NAME'] == $name) {
				switch ($val['TYPE']) {
					case 'list' :
						$q="select ".$this->prefix."_custom_list_value, ".$this->prefix."_custom_list_lib from ".$this->prefix."_custom_lists where ".$this->prefix."_custom_champ=".$key." order by ordre";
						$r=mysql_query($q,$dbh);	
						if(mysql_num_rows($r)) {
							while ($row=mysql_fetch_row($r)) {
								$values[$row[0]]=$row[1];
							}
						}
						break;
					case 'query_list' :
						$field['OPTIONS'][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$val['OPTIONS'], 'OPTIONS');
						$q=$field['OPTIONS'][0]['QUERY'][0]['value'];
						$r = mysql_query($q,$dbh);
						if(mysql_num_rows($r)) {
							while ($row=mysql_fetch_row($r)) {
								$values[$row[0]]=$row[1];
							}
						}
						break;
				}
				break;
			}	
		}
		if (count($values) && $start && $start!='%') {
			$filtered_values=array();
			foreach($values as $k=>$v) {
				if (strtolower(substr($v,0,strlen($start)))==strtolower($start)) {
					$filtered_values[$k]=$v;
				}
			}
			return $filtered_values;
		}
		return $values;
	}	
	
	function get_field_form($id,$field_name,$values){
		global $aff_list_empr_search,$charset;
		$field=array();
		$field[ID]=$id;
		$field[NAME]=$this->t_fields[$id][NAME];
		$field[MANDATORY]=$this->t_fields[$id][MANDATORY];
		$field[ALIAS]=$this->t_fields[$id][TITRE];
		$field[DATATYPE]=$this->t_fields[$id][DATATYPE];
		$field[OPTIONS][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$id][OPTIONS], "OPTIONS");
		$field[VALUES]=$values;
		$field[PREFIX]=$this->prefix;
		eval("\$r=".$aff_list_empr_search[$this->t_fields[$id][TYPE]]."(\$field,\$check_scripts,\$field_name);");
		return $r;
	}
	
	/**
	 * @param integer $id l'identifiant de l'�l�ment concern� par la valeur du champ personalis�
	 * @param string $fieldName le nom du champ personalis�
	 * @param mixed $value la valeur � inserer
	 * @param string $prefix le pr�fix de la table des champs personalis� concern�, par d�faut notices
	 * @return boolean true si r�ussi, false sinon
	 * 
	 * Importe la valeur d'un champ personalis� pour un �l�ment concern�.
	 * G�re les liste et v�rifi le type de document.
	 * Controle les URL et r�solveur de lien.
	 * 
	 * La valeur doit-�tre celle � ajouter. (Ne trouve pas l'�diteur concern� si lien vers �diteurs par exemple)
	 */
	static function import($id,$fieldName,$value,$prefix="notices") {
		$tab = array();
		$idchamp=0;
		$type='';
		$datatype='';
		$check_message='';
		
		//on trouve l'id, le type de champ et le type des donn�es
		$query='SELECT idchamp,type,datatype FROM '.$prefix.'_custom WHERE name="'.addslashes($fieldName).'" LIMIT 1';
		$result=mysql_query($query) or die('Echec d\'execution de la requete '.$query.'  : '.mysql_error());
		
		if(mysql_num_rows($result)){
			while($line=mysql_fetch_array($result,MYSQL_ASSOC)){
				$idchamp=$line['idchamp'];
				$type=$line['type'];
				$datatype=$line['datatype'];
			}
		}else{
			return false;
		}	
		
		switch ($type){
			case 'list':
				//Selection des valeurs si list
				$query = 'SELECT * FROM '.$prefix.'_custom_lists WHERE '.$prefix.'_custom_champ='.$idchamp;
				$result=mysql_query($query) or die('Echec d\'execution de la requete '.$query.'  : '.mysql_error());
				
				if(mysql_num_rows($result)){
					while($line=mysql_fetch_array($result,MYSQL_ASSOC)){
						$tab[$line[$prefix.'_custom_list_lib']] = $line[$prefix.'_custom_list_value'];
					}
				}
				if (!$tab[$value]) {
					//Ajout dans _custom_list
					if($datatype=='integer' || $datatype=='float'){
						if (!sizeof($tab)) {
							$val = 1;
						} else {
							$val = max($tab)+1;
						}
					}else{
						$val = $value;
					}
				
					$query = 'INSERT INTO '.$prefix.'_custom_lists ('.$prefix.'_custom_champ,'.$prefix.'_custom_list_value,'.$prefix.'_custom_list_lib) VALUES ('.$idchamp.',"'.addslashes(trim($val)).'","'.addslashes(trim($value)).'")';
					mysql_query($query) or die('Echec d\'execution de la requete '.$query.'  : '.mysql_error());
					
					$tab[$value] = $val;
				}
				break;
			case 'date_box':
			case 'comment':
			case 'text':
			case 'query_list':
			case 'query_auth':
			case 'external':
				//rien � faire 
				$tab[$value] = $value;
				break;
			case 'resolve':
				//ici on v�rifi la pr�sence d'un pipe dans le resolveur
				if(!preg_match('/\|/', $value)){
					return false;
				}
				$tab[$value] = $value;
				break;
			case 'url':
				//ici on v�rifi le format de l'url
				if(!preg_match('/^http:\/\/www\./', $value)){
					return false;
				}
				$tab[$value] = $value;
				break;
			default:
				return false;
				break;
		}
		
		//on appele la fonction de nettoyage du type de donn�e.
		$tab[$value]=call_user_func('chk_type_'.$datatype,$tab[$value],$check_message);
		
		if($check_message){
			print $check_message;
			return false;
		}
		
 		//Ajout dans _custom_values
		$query='DELETE FROM '.$prefix.'_custom_values WHERE '.$prefix.'_custom_champ='.$idchamp.' AND '.$prefix.'_custom_origine='.$id.' AND '.$prefix.'_custom_'.$datatype.'="'.trim(addslashes($tab[$value])).'"';
		mysql_query($query) or die('Echec d\'execution de la requete '.$query.'  : '.mysql_error());
		
		$query = 'INSERT INTO '.$prefix.'_custom_values ('.$prefix.'_custom_champ,'.$prefix.'_custom_origine,'.$prefix.'_custom_'.$datatype.') VALUES ('.$idchamp.','.$id.',"'.trim(addslashes($tab[$value])).'")';
		mysql_query($query) or die('Echec d\'execution de la requete '.$query.'  : '.mysql_error());

		return true;
	}
	
}
?>