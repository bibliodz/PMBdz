<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_parametres_perso.class.php,v 1.15 2014-02-17 14:16:36 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/parametres_perso.class.php");

class cms_editorial_parametres_perso extends parametres_perso {
	public $num_type;
	
	public function  __construct($type,$base_url="") {
		global $_custom_prefixe_,$msg;
		global $aff_list_empr;
		global $aff_list_empr_search;
		global $aff_filter_list_empr;
		global $chk_list_empr;
		global $val_list_empr;
		global $type_list_empr;
		global $options_list_empr;

		$this->option_visibilite = array(
			'multiple' => "none",
			'opac_sort' => "none",
			'exclusion' => "none"
		);

		//on rajoute
		$aff_list_empr['html'] = "aff_html_empr";
		$aff_list_empr_search['html'] = "aff_comment_empr_search";
		$aff_filter_list_empr['html'] = "aff_filter_comment_empr";
		$chk_list_empr['html'] = "chk_comment_empr";
		$val_list_empr['html'] = "val_comment_empr";
		$type_list_empr['html'] = $msg["parperso_html"];
		$options_list_empr['html'] = "options_html.php";

		$this->prefix="cms_editorial";
		$this->base_url=$base_url;
		$_custom_prefixe_="cms_editorial";

		$this->num_type = $type*1;

		$this->fetch_data_cache();
	}	
	
	protected function fetch_data_cache(){
		if($tmp=cms_cache::get_at_cms_cache($this)){
			$this->restore($tmp);
		}else{
			$this->fetch_data();
			cms_cache::set_at_cms_cache($this);
		}
	}
	
	protected function restore($cms_object){
		foreach(get_object_vars($cms_object) as $propertieName=>$propertieValue){
			$this->{$propertieName}=$propertieValue;
		}
	}
	
	protected function fetch_data(){
		//Lecture des champs
		$this->no_special_fields=0;
		$this->t_fields=array();
		$requete="select idchamp, name, titre, type, datatype, obligatoire, options, multiple, search, export, exclusion_obligatoire, pond, opac_sort from ".$this->prefix."_custom where num_type = '".$this->num_type."' order by ordre";
		
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
				$requete="select max(ordre) from ".$this->prefix."_custom where num_type = ".$this->num_type;
				$resultat=mysql_query($requete);
				if (mysql_num_rows($resultat)!=0)
					$ordre=mysql_result($resultat,0,0)+1;
				else
					$ordre=1;
	
				$requete="insert into ".$this->prefix."_custom set num_type = '$this->num_type', name='$name', titre='$titre', type='$type', datatype='$datatype', options='$_options', multiple=$multiple, obligatoire=$obligatoire, ordre=$ordre, search=$search, export=$export, exclusion_obligatoire=$exclusion, opac_sort=$opac_sort ";
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
				$requete="select max(ordre) as ordre from ".$this->prefix."_custom where ordre<$ordre and num_type = ".$this->num_type;
				$resultat=mysql_query($requete);
				$ordre_max=@mysql_result($resultat,0,0);
				if ($ordre_max) {
					$requete="select idchamp from ".$this->prefix."_custom where ordre=$ordre_max and num_type = ".$this->num_type." limit 1";
					$resultat=mysql_query($requete);
					$idchamp_max=mysql_result($resultat,0,0);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre_max."' where idchamp=$id and num_type = ".$this->num_type;
					mysql_query($requete);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre."' where idchamp=".$idchamp_max." and num_type = ".$this->num_type;
					mysql_query($requete);
				}
				echo $this->show_field_list();
				break;
			case "down":
				$requete="select ordre from ".$this->prefix."_custom where idchamp=$id";
				$resultat=mysql_query($requete);
				$ordre=mysql_result($resultat,0,0);
				$requete="select min(ordre) as ordre from ".$this->prefix."_custom where ordre>$ordre and num_type = ".$this->num_type;
				$resultat=mysql_query($requete);
				$ordre_min=@mysql_result($resultat,0,0);
				if ($ordre_min) {
					$requete="select idchamp from ".$this->prefix."_custom where ordre=$ordre_min and num_type = ".$this->num_type." limit 1";
					$resultat=mysql_query($requete);
					$idchamp_min=mysql_result($resultat,0,0);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre_min."' where idchamp=$id and num_type = ".$this->num_type;
					mysql_query($requete);
					$requete="update ".$this->prefix."_custom set ordre='".$ordre."' where idchamp=".$idchamp_min." and num_type = ".$this->num_type;
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
	public function get_selector_options($selected=0){
		global $msg,$charset;
		$options = $type = "";
		//les champs g�n�riques...
		$query = "select editorial_type_element from cms_editorial_types where id_editorial_type=".$this->num_type;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$type = mysql_result($result,0,0);
			$generic_type = $this->get_generic_type($type);
			if($generic_type){
				$generic = new cms_editorial_parametres_perso($generic_type,$this->base_url);
				$options.=$generic->get_selector_options($selected);
			}
		}
		
		if(strpos($type,"generic") !== false){
		$options.= "
			<option value='0'".(!$selected ? "selected='selected'" : "").">".$msg['cms_editorial_form_type_field_choice']."</option>";			
		}
		foreach($this->t_fields as $id=>$field){
			$options.= "
			<option value='".$id."'".($id==$selected ? "selected='selected'" : "").">".htmlentities($field["TITRE"],ENT_QUOTES,$charset)."</option>";	
		}
		return $options;
	}
	
	//Affichage de l'�cran de gestion des param�tres perso (la liste de tous les champs d�finis)
	function show_field_list() {
		global $type_list_empr;
		global $datatype_list;
		global $form_list;
		global $msg;
	
		$query="select editorial_type_label, editorial_type_element, editorial_type_comment from cms_editorial_types where id_editorial_type = ".$this->num_type;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$row = mysql_fetch_object($result);
		}
		
		if(strpos($row->editorial_type_element,"generic")!== false){
			$res="<h3>".$msg['editorial_content_type_fieldslist_'.$row->editorial_type_element.'_definition']."</h3>";
		}else{
			$res="<h3>".sprintf($msg['editorial_content_type_fieldslist_definition'],$row->editorial_type_label)."</h3>";
		}
		$res.="
		<div class='row'>&nbsp;<div>";		
		$requete="select idchamp, name, titre, type, datatype, multiple, obligatoire, ordre ,search, export,exclusion_obligatoire, opac_sort from ".$this->prefix."_custom where num_type = ".$this->num_type." order by ordre";
		$resultat=mysql_query($requete);
		/*if(!$resultat)
		{
			echo "ya pas de res : ".mysql_num_rows($resultat)."<br />";
		}
		echo "nombre : ".mysql_num_rows($resultat)."<br />";*/
		if (mysql_num_rows($resultat)==0) {
			$res.="<br /><br />".$msg["parperso_no_field"]."<br />";
			$form_list=str_replace("!!liste_champs_perso!!",$res,$form_list);
			$form_list=str_replace("!!base_url!!",$this->base_url,$form_list);
		} else {
			$res.="<table width=100%>\n";
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
		}
		//ajout d'un bouton retour � la liste...
		if(strpos($row->editorial_type_element,"generic")!== false){
			$base_url = str_replace($row->editorial_type_element,substr($row->editorial_type_element,0,strpos($row->editorial_type_element,"_")),$this->base_url);
		}else{
			$base_url = $this->base_url;
		}
		$form_list.= "&nbsp;<input type='button' class='bouton' value=' ".$msg['editorial_content_type_fieldslist_back']." ' onclick='document.location=\"".str_replace("&quoi=fields&type_id=".$this->num_type,"&action=",$base_url)."\"'/>";
		return $form_list;
	}
	
	//R�cup�ration des valeurs stock�es dans les base pour un emprunteur ou autre
	function get_out_values($id) {
		//R�cup�ration des valeurs stock�es 
		if ((!$this->no_special_fields)&&($id)) {
			$this->values = array() ;
			$requete="select ".$this->prefix."_custom_champ,".$this->prefix."_custom_origine,".$this->prefix."_custom_small_text, ".$this->prefix."_custom_text, ".$this->prefix."_custom_integer, ".$this->prefix."_custom_date, ".$this->prefix."_custom_float from ".$this->prefix."_custom_values join ".$this->prefix."_custom on idchamp=".$this->prefix."_custom_champ and num_type = ".$this->num_type."  where ".$this->prefix."_custom_origine=".$id;
			$resultat=mysql_query($requete);
			while ($r=mysql_fetch_array($resultat)) {
				$this->values[$this->t_fields[$r[$this->prefix."_custom_champ"]]["NAME"]]['label'] = $this->t_fields[$r[$this->prefix."_custom_champ"]]["TITRE"];
				$this->values[$this->t_fields[$r[$this->prefix."_custom_champ"]]["NAME"]]['id'] = $r[$this->prefix."_custom_champ"];
				$this->values[$this->t_fields[$r[$this->prefix."_custom_champ"]]["NAME"]]['values'][] = array(
					'value' => $r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]],
					'format_value' => $this->get_formatted_output(array($r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]]),$r[$this->prefix."_custom_champ"],true)	
				);
			}
		} else $this->values=array();
		return $this->values;
	}
	
	public function delete_all(){
		$query = "select idchamp from ".$this->prefix."_custom where num_type = ".$this->num_type;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while ($row = mysql_fetch_object($result)){
				$requete="delete from ".$this->prefix."_custom where idchamp=$row->idchamp";
				mysql_query($requete);
				$requete="delete from ".$this->prefix."_custom_values where ".$this->prefix."_custom_champ=$row->idchamp";
				mysql_query($requete);
				$requete="delete from ".$this->prefix."_custom_lists where ".$this->prefix."_custom_champ=$row->idchamp";
				mysql_query($requete);
			}
		}
	}
	
	//Suppression de la base des valeurs d'un emprunteur ou autre...
	function delete_values($id) {
//		on r�cup�re la liste des champs associ�s...
		$query = "select idchamp from ".$this->prefix."_custom where num_type = ".$this->num_type;
		$result = mysql_query($query);
		$idchamp = "";
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				if($idchamp) $idchamp.=",";
				$idchamp.=$row->idchamp;
			}
		}
		if(!$idchamp) $idchamp="''";
		
		$requete = "DELETE FROM ".$this->prefix."_custom_values where ".$this->prefix."_custom_champ in (".$idchamp.") and ".$this->prefix."_custom_origine=$id";
		$res = mysql_query($requete);
	}
	
	//R�cup�ration des valeurs stock�es dans les base pour un emprunteur ou autre
	function get_values($id) {
		//R�cup�ration des valeurs stock�es pour l'emprunteur
		if ((!$this->no_special_fields)&&($id)) {
			$this->values=$this->list_values=array();
			
			$requete="select ".$this->prefix."_custom_champ,".$this->prefix."_custom_origine,".$this->prefix."_custom_small_text, ".$this->prefix."_custom_text, ".$this->prefix."_custom_integer, ".$this->prefix."_custom_date, ".$this->prefix."_custom_float from ".$this->prefix."_custom_values join ".$this->prefix."_custom on idchamp=".$this->prefix."_custom_champ and num_type = ".$this->num_type." where ".$this->prefix."_custom_origine=".$id;
			$resultat=mysql_query($requete);
			while ($r=mysql_fetch_array($resultat)) {
				$this->values[$r[$this->prefix."_custom_champ"]][]=$r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]];
				$this->list_values[]=$r[$this->prefix."_custom_".$this->t_fields[$r[$this->prefix."_custom_champ"]]["DATATYPE"]];
			}
		} else $this->values=$this->list_values=array();
	}
	
	//Affichage des champs � saisir dans le formulaire de modification/cr�ation d'un emprunteur ou autre
	function show_editable_fields($id,$type="") {
		global $aff_list_empr,$charset;
		$perso=array();
		//on va chercher les champs g�n�riques
		$generic_type = $this->get_generic_type($type);
		if($generic_type){
			$generic = new cms_editorial_parametres_perso($generic_type,$this->base_url);
			$p = $generic->show_editable_fields($id);
			$perso['FIELDS'] = $p['FIELDS'];
			$perso['CHECK_SCRIPTS'] = $p['CHECK_SCRIPTS'];
		}
		if (!$this->no_special_fields) {
			$this->get_values($id);
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
	
	//Enregistrement des champs perso soumis lors de la saisie d'une fichie emprunteur ou autre...
	function rec_fields_perso($id,$type="") {
		$this->check_submited_fields();
		$requete="delete ".$this->prefix."_custom_values from ".$this->prefix."_custom_values join cms_editorial_custom on cms_editorial_custom_champ = idchamp and num_type = ".$this->num_type." where ".$this->prefix."_custom_origine=$id";
		mysql_query($requete);	
		if($type){
			//Enregistrement des champs personalis�s
			//on va chercher les champs g�n�riques
			$generic_type = $this->get_generic_type($type);
			if($generic_type){
				$generic = new cms_editorial_parametres_perso($generic_type,$this->base_url);
				$generic->rec_fields_perso($id);
			}
		}
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
	
	public function get_generic_type($type){
		$generic_type = 0;
		if($type){
			$query = "select id_editorial_type from cms_editorial_types where editorial_type_element like '".$type."_generic'";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_object($result);
				$generic_type = $row->id_editorial_type;
			}
		}
		return $generic_type;
	}
	
	public function get_formatted_output($values, $field_id, $keep_html=false){
		global $val_list_empr, $charset;
		
		$field=array();
		$field["ID"]=$field_id;
		$field["NAME"]=$this->t_fields[$field_id]["NAME"];
		$field["MANDATORY"]=$this->t_fields[$field_id]["MANDATORY"];
		$field["OPAC_SORT"]=$this->t_fields[$field_id]["OPAC_SORT"];
		$field["ALIAS"]=$this->t_fields[$field_id]["TITRE"];
		$field["DATATYPE"]=$this->t_fields[$field_id]["DATATYPE"];
		$field["OPTIONS"][0]=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$this->t_fields[$field_id]["OPTIONS"], "OPTIONS");
		$field["VALUES"]=$values;
		$field["PREFIX"]=$this->prefix;
		$aff=$val_list_empr[$this->t_fields[$field_id]["TYPE"]]($field,$values);
		if(is_array($aff)){
			if($keep_html){
				return $aff['value'];
			}else return $aff['withoutHTML'];
		}
		else return $aff;
	}
}