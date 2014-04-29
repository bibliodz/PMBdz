<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial.class.php,v 1.22 2014-02-05 16:32:22 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/cms/cms_root.class.php");
require_once($class_path."/cms/cms_logo.class.php");
require_once($class_path."/cms/cms_editorial_publications_states.class.php");

require_once($class_path."/categories.class.php");
require_once($include_path."/templates/cms/cms_editorial.tpl.php");
require_once($class_path."/double_metaphone.class.php");
require_once($class_path."/stemming.class.php");
require_once($class_path."/cms/cms_collections.class.php");

class cms_editorial extends cms_root {
	public $id;						// identifiant du contenu
	public $num_parent;				// id du parent
	public $title;					// le titre du contenu
	public $resume;					// r�sum� du contenu
	public $logo;					// objet g�rant le logo
	public $publication_state;		// statut de publication	
	public $start_date;				// date de d�but de publication
	public $end_date;				// date de fin de publication
	public $descriptors = array();	// descripteurs
	protected $type;				// le type de l'objet
	public $num_type;				// id du type de contenu 
	public $type_content = "";		// libell� du type de contenu
	public $fields_type;
	protected $opt_elements;		// les �l�ments optionnels constituants l'objet
	public $create_date;			//
	public $documents_linked=array();		//tableau des docs li�s
	
	public function __construct($id=2,$type="section",$num_parent=0){
		$this->type = $type;
		if($id){
			$this->id = $id;
			$this->fetch_data_cache();
			$this->logo = new cms_logo($this->id,$this->type);
		}else{
			$this->id = 0;
			$this->title = "";
			$this->resume = "";
			$this->logo = new cms_logo(0,$this->type);
			$this->publication_state = "";
			$this->start_date = "";
			$this->end_date = "";
			$this->num_parent = $num_parent;
			$this->descriptors = array();
			$this->num_type;
			$this->create_date = "";
			$this->documents_linked = array();
		}
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
	
	protected function get_descriptors(){
		// les descripteurs...
		$rqt = "select num_noeud from cms_".$this->type."s_descriptors where num_".$this->type." = '".$this->id."' order by ".$this->type."_descriptor_order";
		$res = mysql_query($rqt);
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				$categ = new categories($row->num_noeud, $lang);
				$this->descriptors[] = $categ->num_noeud;
			}
		}
	}
	
	protected function get_fields_type(){
		$this->fields_type = array();
		$query = "select id_editorial_type from cms_editorial_types where editorial_type_element = '".$this->type."_generic'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$fields_type = new cms_editorial_parametres_perso(mysql_result($result,0,0));
			$this->fields_type = $fields_type->get_out_values($this->id);
		}
		if($this->num_type){
			$query = "select editorial_type_label from cms_editorial_types where id_editorial_type = ".$this->num_type;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$this->type_content = mysql_result($result,0,0);
				$fields_type = new cms_editorial_parametres_perso($this->num_type);
				$this->fields_type = array_merge($this->fields_type,$fields_type->get_out_values($this->id));
			}
		}
	}
	
	public function delete(){
		$result = $this->is_deletable();
		if($result === true){
			//l'el�ment
			$del = "delete from cms_".$this->type."s where id_".$this->type."='".$this->id."'";
			mysql_query($del);
			//ses descripteurs
			$del_desc = "delete from cms_".$this->type."_descriptors where num_".$this->type." = '".$this->id."'";
			mysql_query($del_desc);	
			//l'indexation
			$del_desc = "delete from cms_".$this->type."_descriptors where num_".$this->type." = '".$this->id."'";
			mysql_query($del_desc);
			//ses champs persos
			$fields_type = new cms_editorial_parametres_perso($this->num_type);
			$fields_type->delete_values($this->id);
			//indexation
			$query = "delete from cms_editorial_fields_global_index where num_obj = ".$this->id." and type='".$this->type."'";
			mysql_query($query);
			$query = "delete from cms_editorial_words_global_index where num_obj = ".$this->id." and type='".$this->type."'";
			mysql_query($query);
			//ses extensions
			$query ="delete from cms_modules_extensions_datas where extension_datas_type_element ='".$this->type."' and extension_datas_num_element = '".$this->id."'";
			mysql_query($query);
			return true;
		}else{
			return $result;
		}
	}
		
	public function get_form($name="cms_form_editorial",$id="cms_form_editorial",$attr="",$close=true){
		//on r�cup�re le template
		global $cms_editorial_form_tpl;
		global $cms_editorial_form_del_button_tpl;
		global $cms_editorial_form_dupli_button_tpl;
		global $msg;
		global $lang;
		global $base_path;
		
		$fields_form="";
		$fields_form.=$this->get_type_field();
		$fields_form.=$this->get_parent_field();
		$fields_form.=$this->get_title_field();
		$fields_form.=$this->get_resume_field();
		$fields_form.=$this->get_contenu_field();
		$fields_form.=$this->get_logo_field();
		$fields_form.=$this->get_desc_field();
		$fields_form.=$this->get_publication_state_field();
		$fields_form.=$this->get_dates_field();
		$fields_form.=$this->get_documents_form();
		
		
		$form = str_replace("!!fields!!",$fields_form,$cms_editorial_form_tpl);
		
		if($this->id){
			$del_button = $cms_editorial_form_del_button_tpl;
			$dupli_button = $cms_editorial_form_dupli_button_tpl;
			$type_href=$base_path."/ajax.php?module=cms&categ=get_type_form&elem=".$this->type."&type_id=".$this->num_type."&id=".$this->id;
		}else{
			$del_button = "";
			$dupli_button = "";
			$type_href=$base_path."/ajax.php?module=cms&categ=get_type_form&elem=".$this->type."&type_id=&id=".$this->id;;
		}
		$form = str_replace("!!cms_editorial_form_suppr!!",$del_button,$form);
		$form = str_replace("!!cms_editorial_form_dupli!!",$dupli_button,$form);
		$form = str_replace("!!type_href!!",$type_href,$form);
		
		$form = str_replace("!!type!!",$this->type,$form);
		$form = str_replace("!!cms_editorial_form_name!!",$name,$form);
		$form = str_replace("!!cms_editorial_form_id!!",$id,$form);
		$form = str_replace("!!cms_editorial_form_obj_id!!",$this->id,$form);
		
		if(!$this->id){
			$attr = "enctype='multipart/form-data' ".$attr;
		}
		$form = str_replace("!!cms_editorial_form_attr!!",$attr,$form);

		$form = str_replace("!!form_title!!",$msg['cms_'.($this->id ? "" : "new_").$this->type."_form_title"],$form);

		if($close){
			$form = str_replace("!!cms_editorial_suite!!","",$form);
		}		
		return $form;
	}
	
	public function get_ajax_form($name="cms_form_editable",$id="cms_form_editable"){
		global $msg;
		
		$form = $this->get_form($name,$id,"onsubmit='cms_ajax_submit();return false;'",false);
		$suite ="
		<script>
			function cms_ajax_submit(){
				var values = '';
			
				if(typeof(check_form) == 'function' && !check_form()){
					return false;
				}
				if(document.forms['$name'].cms_editorial_form_delete.value == 1){
					if(confirm(\"".$msg['cms_editorial_form_'.$this->type.'_delete_confirm']."\")){
						cms_".$this->type."_delete();
					}
				} else if(document.forms['$name'].cms_editorial_form_duplicate.value == 1){
					if ('".$this->type."' == 'section') {
						if (confirm('".$msg['cms_editorial_form_duplicate_branch_confirm']."')) {
							cms_".$this->type."_duplicate(1);
						} else {
							cms_".$this->type."_duplicate(0);
						}
					} else {
						cms_".$this->type."_duplicate(0);
					}
				}else{
					for(var i=0 ; i<document.forms['$name'].elements.length ; i++){
						var element = document.forms['$name'].elements[i];
						if(element.name){
							if(element.type == 'select-multiple'){
								for(var j=0; j< element.options.length; j++){
									if(element.options[j].selected == true){
										values+='&'+element.name+'='+encodeURIComponent(element.options[j].value); 
									}
								}
							}else if(element.type == 'checkbox'){
								if(element.checked == true){
									values+='&'+element.name+'='+encodeURIComponent(element.value);
								}
							}else if(element.type == 'radio'){
								if(element.checked == true){
									values+='&'+element.name+'='+encodeURIComponent(element.value);
								}
							}else{
								values+='&'+element.name+'='+encodeURIComponent(element.value);
							}
						}
					}
					var post = new http_request();
					post.request('./ajax.php?module=cms&categ=save_".$this->type."',true,values,true,cms_".$this->type."_saved);
				}
			}
			
			function cms_".$this->type."_delete(){
				var post = new http_request();
				post.request('./ajax.php?module=cms&categ=delete_".$this->type."',true,'&id='+document.forms['$name'].cms_editorial_form_obj_id.value,true,cms_".$this->type."_deleted);
			}
			
			function cms_".$this->type."_duplicate(recursive){
				var post = new http_request();
				post.request('./ajax.php?module=cms&categ=duplicate_".$this->type."',true,'&id='+document.forms['$name'].cms_editorial_form_obj_id.value+'&recursive='+recursive,true,cms_".$this->type."_duplicated);
			}

			function cms_".$this->type."_deleted(response){
				var result = eval('('+response+')');
				if(result.status == 'ok'){
					dijit.byId('editorial_tree_container').refresh();
					dijit.byId('content_infos').destroyDescendants();
				}else{
					alert(result.error_message);
				}
			}

			function cms_".$this->type."_duplicated(){
					dijit.byId('editorial_tree_container').refresh();
					dijit.byId('content_infos').destroyDescendants();
			}

			function cms_".$this->type."_saved(response){
				dijit.byId('editorial_tree_container').refresh();
				dijit.byId('content_infos').refresh();
			}
		</script>";
		$form = str_replace("!!cms_editorial_suite!!",$suite,$form);
		return $form;		
	}
	
	public function get_parent_selector(){
		//� surcharger...
	}
	
	protected function get_parent_field(){
		global $msg;
		global $cms_editorial_parent_field;
		return str_replace("!!cms_editorial_form_parent_options!!",$this->get_parent_selector(),$cms_editorial_parent_field);
	}
	
	protected function get_title_field(){
		global $cms_editorial_title_field;
		return str_replace("!!cms_editorial_form_title!!",$this->title,$cms_editorial_title_field);
	}
	
	protected function get_resume_field(){
		global $cms_editorial_resume_field;
		return str_replace("!!cms_editorial_form_resume!!",$this->resume,$cms_editorial_resume_field);
	}
	
	protected function get_contenu_field(){
		global $cms_editorial_contenu_field;
		if($this->opt_elements['contenu']==true){
			return str_replace("!!cms_editorial_form_contenu!!",$this->contenu,$cms_editorial_contenu_field);	
		}else{
			return "";		
		}
	}
	
	protected function get_logo_field(){
		return $this->logo->get_form();
	}
	
	protected function get_desc_field(){
		global $lang;
		global $cms_editorial_desc_field;
		global $cms_editorial_first_desc,$cms_editorial_other_desc;
		
		$categs = "";
		if(count($this->descriptors)){
			for ($i=0 ; $i<count($this->descriptors) ; $i++){
				if($i==0) $categ=$cms_editorial_first_desc;
				else $categ = $cms_editorial_other_desc;
				//on y va
				$categ = str_replace('!!icateg!!', $i, $categ);
				$categ = str_replace('!!categ_id!!', $this->descriptors[$i], $categ);
				$categorie = new categories($this->descriptors[$i],$lang);
				$categ = str_replace('!!categ_libelle!!', $categorie->libelle_categorie, $categ);			
				$categs.=$categ;
			}
			$categs = str_replace("!!max_categ!!",count($this->descriptors),$categs);
		}else{
			$categs=$cms_editorial_first_desc;
			$categs = str_replace('!!icateg!!', 0, $categs) ;
			$categs = str_replace('!!categ_id!!', "", $categs);
			$categs = str_replace('!!categ_libelle!!', "", $categs);
			$categs = str_replace('!!max_categ!!', 1, $categs);
		}		
		return str_replace("!!cms_categs!!",$categs,$cms_editorial_desc_field);
	}
	
	protected function get_publication_state_field(){
		global $cms_editorial_publication_state_field;
		$publications_states = new cms_editorial_publications_states();
		return str_replace("!!cms_editorial_form_publications_states_options!!",$publications_states->get_selector_options($this->publication_state),$cms_editorial_publication_state_field);
	}
	
	protected function get_dates_field(){
		global $cms_editorial_dates_field;
		global $msg;
		$day = date("Ymd");
		$form = str_replace("!!day!!",$day,$cms_editorial_dates_field);
		
		$start_date = formatDate($this->start_date);
		if(!$start_date) $start_date = $msg['no_date'];
		$form = str_replace("!!cms_editorial_form_start_date_value!!",$this->start_date,$form);
		$form = str_replace("!!cms_editorial_form_start_date!!",$start_date,$form);
		
		$end_date = formatDate($this->end_date);
		if(!$end_date) $end_date = $msg['no_date'];
		$form = str_replace("!!cms_editorial_form_end_date_value!!",$this->end_date,$form);
		$form = str_replace("!!cms_editorial_form_end_date!!",$end_date,$form);
		return $form;
	}
	
	protected function get_type_field(){
		global $cms_editorial_type_field;
		$types = new cms_editorial_types($this->type);
		$types->get_types();
		if(count($types->types)){
			return str_replace("!!cms_editorial_form_type_options!!",$types->get_selector_options($this->num_type),$cms_editorial_type_field);
		}else{
			return "";
		}
	}
	
	public function get_from_form(){
		global $cms_editorial_form_obj_id;
		global $cms_editorial_form_type;
		global $cms_editorial_form_parent;
		global $cms_editorial_form_title;
		global $cms_editorial_form_resume;
		global $cms_editorial_form_contenu;
		global $max_categ;
		global $cms_editorial_form_publication_state;
		global $cms_editorial_form_start_date_value;
		global $cms_editorial_form_end_date_value;
		global $cms_documents_linked;

		for ($i=0 ; $i<$max_categ ; $i++){
			$categ_id = 'f_categ_id'.$i;
			global $$categ_id;
			if($$categ_id > 0){
				$this->descriptors[] = $$categ_id;
			}
		}
		$this->id = $cms_editorial_form_obj_id;
		$this->num_type = $cms_editorial_form_type;
		$this->num_parent = stripslashes($cms_editorial_form_parent);
		$this->title = stripslashes($cms_editorial_form_title);
		$this->resume = stripslashes($cms_editorial_form_resume);
		if($this->resume == '<br _moz_editor_bogus_node="TRUE" />'){
			$this->resume = "";
		}
		$this->start_date = stripslashes($cms_editorial_form_start_date_value);
		$this->end_date = stripslashes($cms_editorial_form_end_date_value);
		$this->publication_state = stripslashes($cms_editorial_form_publication_state);
		if($this->opt_elements['contenu']) {
			$this->contenu = stripslashes($cms_editorial_form_contenu);
			if($this->contenu == '<br _moz_editor_bogus_node="TRUE" />'){
				$this->contenu = "";
			}
		}
		$this->logo->id = $this->id;
		$this->documents_linked = $cms_documents_linked;
	}

	protected function save_logo(){
		//on agit que si un fichier a �t� soumis...
		if(count($_FILES)){
			$this->logo->id = $this->id;
			$this->logo->save();	
		}
	}
	
	public function maj_indexation($datatype='all') {
		global $include_path;
		global $dbh, $champ_base;
		//recuperation du fichier xml de configuration
		if(!count($champ_base)) {
			$file = $include_path."/indexation/editorial_content/".$this->type."_subst.xml";
			if(!file_exists($file)){
				$file = $include_path."/indexation/editorial_content/".$this->type.".xml";
			}
			$fp=fopen($file,"r");
    		if ($fp) {
				$xml=fread($fp,filesize($file));
			}
			fclose($fp);
			$champ_base=_parser_text_no_function_($xml,"INDEXATION");
		}
		$tableau=$champ_base;
		
		//analyse des donnees des tables
		$temp_not=array();
		$temp_not['t'][0][0]=$tableau['REFERENCE'][0][value] ;
		$temp_ext=array();
		$temp_marc=array();
		$champ_trouve=false;
		$tab_code_champ = array();
		$tab_languages=array();
		$tab_keep_empty = array();
		$tab_pp=array();
		for ($i=0;$i<count($tableau['FIELD']);$i++) { //pour chacun des champs decrits
			//recuperation de la liste des informations a mettre a jour
			if ( $datatype=='all' || ($datatype==$tableau['FIELD'][$i]['DATATYPE']) ) {
				//conservation des mots vides
				if($tableau['FIELD'][$i]['KEEPEMPTYWORD'] == "yes"){
					$tab_keep_empty[]=$tableau['FIELD'][$i]['ID'];
				}
				//champ perso
				if($tableau['FIELD'][$i]['DATATYPE'] == "custom_field"){
					$tab_pp[$tableau['FIELD'][$i]['ID']]=$tableau['FIELD'][$i]['TABLE'][0]['value'];
				}else if ($tableau['FIELD'][$i]['EXTERNAL']=="yes") {
					//champ externe � la table notice
					//Stockage de la structure pour un acc�s plus facile
					$temp_ext[$tableau['FIELD'][$i]['ID']]=$tableau['FIELD'][$i];
				} else {
					//champ de la table notice
					$temp_not['f'][0][$tableau['FIELD'][$i]['ID']]= $tableau['FIELD'][$i]['TABLE'][0]['TABLEFIELD'][0]['value'];
					$tab_code_champ[0][$tableau['FIELD'][$i]['TABLE'][0]['TABLEFIELD'][0]['value']] = array(
						'champ' => $tableau['FIELD'][$i]['ID'],
						'ss_champ' => 0,
						'pond' => $tableau['FIELD'][$i]['POND'],
						'no_words' => ($tableau['FIELD'][$i]['DATATYPE'] == "marclist" ? true : false)
					);
					if($tableau['FIELD'][$i]['TABLE'][0]['TABLEFIELD'][0]['MARCTYPE']){
						$tab_code_champ[0][$tableau['FIELD'][$i]['TABLE'][0]['TABLEFIELD'][0]['value']]['marctype']=$tableau['FIELD'][$i]['TABLE'][0]['TABLEFIELD'][0]['MARCTYPE'];
					}
				}
				$champ_trouve=true;
			}
		}
		if ($champ_trouve) {
			$tab_req=array();
			//Recherche des champs directs
			if($datatype=='all') {
				$tab_req[0]["rqt"]= "select ".implode(',',$temp_not['f'][0])." from ".$temp_not['t'][0][0];
				$tab_req[0]["rqt"].=" where ".$tableau['REFERENCEKEY'][0][value]."='".$this->id."'";
				$tab_req[0]["table"]=$temp_not['t'][0][0];
			}
			foreach($temp_ext as $k=>$v) {
				//Construction de la requete
				//Champs pour le select
				$select=array();

				//on harmonise les fichiers XML d�crivant des requetes...
				for ($i = 0; $i<count($v["TABLE"]); $i++) {
					$table = $v['TABLE'][$i];	
					$select=array();
					for ($j=0;$j<count($table['TABLEFIELD']);$j++) {
						$select[]=($table['ALIAS'] ? $table['ALIAS']."." : "").$table['TABLEFIELD'][$j]["value"];
						if($table['LANGUAGE']){
							$select[]=$table['LANGUAGE'][0]['value'];
							$tab_languages[$k]=$table['LANGUAGE'][0]['value'];
						}
						$field_name = $table['TABLEFIELD'][$j]["value"];
						if(strpos($table['TABLEFIELD'][$j]["value"],".")!== false){
							$field_name = substr($table['TABLEFIELD'][$j]["value"],strpos($table['TABLEFIELD'][$j]["value"],".")+1);
						}
						$tab_code_champ[$v['ID']][$field_name] = array(
							'champ' => $v['ID'],
							'ss_champ' => $table['TABLEFIELD'][$j]["ID"],
							'pond' => $table['TABLEFIELD'][$j]['POND'],
							'no_words' => ($tableau['FIELD'][$i]['DATATYPE'] == "marclist" ? true : false)
						);
						if($v['TABLEFIELD'][$j]['marclist']){
							$tab_code_champ[$v['ID']][$v['TABLEFIELD'][$j]["value"]]['marctype']=$v['TABLEFIELD'][$j]['marctype'];
						}
					}
					$query="select ".implode(",",$select)." from ".$tableau['REFERENCE'][0]['value'];		
					$jointure="";						
					for( $j=0 ; $j<count($table['LINK']) ; $j++){
						$link = $table['LINK'][$j];
						if($link["TABLE"][0]['ALIAS']){
							$alias = $link["TABLE"][0]['ALIAS'];
						}else{
							$alias = $link["TABLE"][0]['value'];
						}
						switch ($link["TYPE"]) {
							case "n1" :
								if ($link["TABLEKEY"][0]['value']) {
									$jointure .= " JOIN " . $link["TABLE"][0]['value'].($link["TABLE"][0]['value'] != $alias  ? " AS ".$alias : "");
									if($link["EXTERNALTABLE"][0]['value']){
										$jointure .= " ON " . $link["EXTERNALTABLE"][0]['value'] . "." . $link["EXTERNALFIELD"][0]['value'];
									}else{
										$jointure .= " ON " . ($table['ALIAS']? $table['ALIAS'] : $table['NAME']) . "." . $link["EXTERNALFIELD"][0]['value'];
									}
									$jointure .= "=" . $alias . "." . $link["TABLEKEY"][0]['value'];
								} else {
									$jointure .= " JOIN " . $table['NAME'] . ($table['ALIAS']? " as ".$table['ALIAS'] :"");
									$jointure .= " ON " . $tableau['REFERENCE'][0]['value'] . "." . $tableau['REFERENCEKEY'][0]['value'];
									$jointure .= "=" . ($table['ALIAS']? $table['ALIAS'] : $table['NAME']) . "." . $link["EXTERNALFIELD"][0]['value'];
								}
								break;
							case "1n" :
								$jointure .= " JOIN " . $table['NAME'] . ($table['ALIAS']? " as ".$table['ALIAS'] :"");
								$jointure .= " ON (" . ($table['ALIAS']? $table['ALIAS'] : $table['NAME']) . "." . $table["TABLEKEY"][0]['value'];
								$jointure .= "=" . $tableau['REFERENCE'][0]['value'] . "." . $link["REFERENCEFIELD"][0]['value'] . ") ";
								break;
							case "nn" :
								$jointure .= " JOIN " . $link["TABLE"][0]['value'].($link["TABLE"][0]['value'] != $alias  ? " AS ".$alias : "");
								$jointure .= " ON (" . $tableau['REFERENCE'][0]['value'] . "." .  $tableau['REFERENCEKEY'][0]['value'];
								$jointure .= "=" . $alias . "." . $link["REFERENCEFIELD"][0]['value'] . ") ";
								if ($link["TABLEKEY"][0]['value']) {
									$jointure .= " JOIN " . $table['NAME'] . ($table['ALIAS']? " as ".$table['ALIAS'] :"");
									$jointure .= " ON (" . $alias . "." . $link["TABLEKEY"][0]['value'];
									$jointure .= "=" . ($table['ALIAS']? $table['ALIAS'] : $table['NAME']) . "." . $link["EXTERNALFIELD"][0]['value'] ." ".$link["LINKRESTRICT"][0]['value']. ") ";
								} else {
									$jointure .= " JOIN " . $table['NAME'] . ($table['ALIAS']? " as ".$table['ALIAS'] :"");
									$jointure .= " ON (" . $alias . "." . $link["EXTERNALFIELD"][0]['value'];
									$jointure .= "=" . ($table['ALIAS']? $table['ALIAS'] : $table['NAME']) . "." . $table["TABLEKEY"][0]['value'] . " ".$link["LINKRESTRICT"][0]['value'].") ";
								}
								break;
						}
					}
					if($table['LANGUAGE']){
						$tab_req_lang[$k]= "select ".$table['LANGUAGE'][0]['value']." from ";
					}
					$query.=$jointure." where ".$temp_not['t'][0][0].".".$tableau['REFERENCEKEY'][0][value]."=".$this->id;
					if($table['LANGUAGE']){
						$tab_req_lang[$k].=$jointure." where ".$temp_not['t'][0][0].".".$tableau['REFERENCEKEY'][0][value]."=".$this->id;
					}
					$tab_req[$k]["new_rqt"]['rqt'][]=$query;
				}
				$tab_req[$k]["rqt"] = implode(" union ",$tab_req[$k]["new_rqt"]['rqt']);
		
			}
			//qu'est-ce qu'on efface?
			if($datatype=="all") {
				$req_del="delete from cms_editorial_words_global_index where num_obj='".$this->id."' and type = '".$this->type."'";
				mysql_query($req_del,$dbh);
				//la table pour les recherche exacte
				$req_del="delete from cms_editorial_fields_global_index where num_obj='".$this->id."' and type = '".$this->type."'";
				mysql_query($req_del,$dbh);					
			}else{
				foreach ( $tab_code_champ as $subfields ) {
					foreach($subfields as $subfield){
						$req_del="delete from cms_editorial_words_global_index where num_obj='".$this->id."' and type = '".$this->type."' and code_champ='".$subfield['champ']."'";
						mysql_query($req_del,$dbh);
						//la table pour les recherche exacte
						$req_del="delete from cms_editorial_fields_global_index where num_obj='".$this->id."' and type = '".$this->type."' and code_champ='".$subfield['champ']."'";
						mysql_query($req_del,$dbh);	
						break;
					}
				}
				
				//Les champs perso
				if(count($tab_pp)){
					foreach ( $tab_pp as $id ) {
       					$req_del="delete from cms_editorial_words_global_index where num_obj='".$this->id."' and type = '".$this->type."' and code_champ='".$id."' ";
       					mysql_query($req_del,$dbh);
						//la table pour les recherche exacte
						$req_del="delete from cms_editorial_fields_global_index where num_obj='".$this->id."' and type = '".$this->type."' and code_champ='".$id."' ";
						mysql_query($req_del,$dbh);	
					}
				}
			}
			
			//qu'est-ce qu'on met a jour ?
			$tab_insert=array();	
			$tab_field_insert=array();
			foreach($tab_req as $k=>$v) {	
				$r=mysql_query($v["rqt"],$dbh);
				$tab_mots=array();
				$tab_fields=array();
				if (mysql_num_rows($r)) {
					while(($tab_row=mysql_fetch_array($r,MYSQL_ASSOC))) {
						if(isset($tab_row[$tab_languages[$k]])){
							$lang = $tab_row[$tab_languages[$k]];
							unset($tab_row[$tab_languages[$k]]);
						}else{
							$lang="";
						}
						foreach($tab_row as $nom_champ => $liste_mots) {
							if($tab_code_champ[$k][$nom_champ]['marctype']){
								$marclist = new marc_list($tab_code_champ[$k][$nom_champ]['marctype']);
								$liste_mots = $marclist->table[$liste_mots];
							}
							if($liste_mots!='') {
								$tab_tmp=array();
								if(!in_array($k,$tab_keep_empty)){
									$tab_tmp=explode(' ',strip_empty_words($liste_mots));
								}else{
									$tab_tmp=explode(' ',strip_empty_chars(clean_string($liste_mots)));
								}
							//	if($lang!="") $tab_tmp[]=$lang;
								//la table pour les recherche exacte
								if(!$tab_fields[$nom_champ]) $tab_fields[$nom_champ]=array();
								$tab_fields[$nom_champ][] = array(
									'value' =>trim($liste_mots),
									'lang' => $lang
								);
								if(!$tab_code_champ[$k][$nom_champ]['no_words']){
									foreach($tab_tmp as $mot) {
										if(trim($mot)){
											$tab_mots[$nom_champ][$mot]=$lang;
										}
									}
								}
							}
						}
					}
				}
				foreach ($tab_mots as $nom_champ=>$tab) {
					$pos=1;
					foreach ( $tab as $mot => $lang ) {
						//on cherche le mot dans la table de mot...
						$query = "select id_word from words where word = '".$mot."' and lang = '".$lang."'";
						$result = mysql_query($query);
						if(mysql_num_rows($result)){
							$num_word = mysql_result($result,0,0);
						}else{
							$dmeta = new DoubleMetaPhone($mot);
							$stemming = new stemming($mot);
							$element_to_update = "";
							if($dmeta->primary || $dmeta->secondary){
								$element_to_update.="
								double_metaphone = '".$dmeta->primary." ".$dmeta->secondary."'";
							}
							if($element_to_update) $element_to_update.=",";
							$element_to_update.="stem = '".$stemming->stem."'";
							
							$query = "insert into words set word = '".$mot."', lang = '".$langage."'".($element_to_update ? ", ".$element_to_update : "");
							mysql_query($query);
							$num_word = mysql_insert_id();
						}
						$tab_insert[]="(".$this->id.",'".$this->type."',".$tab_code_champ[$k][$nom_champ]['champ'].",".$tab_code_champ[$k][$nom_champ]['ss_champ'].",".$num_word.",".$tab_code_champ[$k][$nom_champ]['pond'].",$pos)";
						$pos++;
					}
				}
				//la table pour les recherche exacte
				foreach ($tab_fields as $nom_champ=>$tab) {
					foreach($tab as $order => $values){
       					//$tab_field_insert[]="(".$this->id.",".$tab_code_champ[$v["table"]][$nom_champ][0].",".$tab_code_champ[$v["table"]][$nom_champ][1].",".$order.",'".addslashes($values['value'])."','".addslashes($values['lang'])."',".$tab_code_champ[$v["table"]][$nom_champ][2].")";
       					$tab_field_insert[]="(".$this->id.",'".$this->type."',".$tab_code_champ[$k][$nom_champ]['champ'].",".$tab_code_champ[$k][$nom_champ]['ss_champ'].",".$order.",'".addslashes($values['value'])."','".addslashes($values['lang'])."',".$tab_code_champ[$k][$nom_champ]['pond'].")";
					}
				}
			}
			//Les champs perso
			if(count($tab_pp)){
				foreach ( $tab_pp as $code_champ => $table ) {
       				$p_perso=new cms_editorial_parametres_perso($this->num_type);
      				$data=$p_perso->get_fields_recherche_mot($this->id);
      				$j=0;
       				foreach ( $data as $code_ss_champ => $value ) {
       					$tab_mots=array();
       					$tab_tmp=explode(' ',strip_empty_words($value));
       					//la table pour les recherche exacte
       					$tab_field_insert[]="(".$this->id.",'".$this->type."',".$code_champ.",".$code_ss_champ.",".$j.",'".addslashes(trim($value))."','',".$p_perso->get_pond($code_ss_champ).")";
       					$j++;
						foreach($tab_tmp as $mot) {
							if(trim($mot)){
								$tab_mots[$mot]= "";
							}
						}
						$pos=1;
						foreach ( $tab_mots as $mot => $lang ) {
							//on cherche le mot dans la table de mot...
							$query = "select id_word from words where word = '".$mot."' and lang = '".$lang."'";
							$result = mysql_query($query);
							if(mysql_num_rows($result)){
								$num_word = mysql_result($result,0,0);
							}else{
								$query = "insert into words set word = '".$mot."', lang = '".$lang."'";
								mysql_query($query);
								$num_word = mysql_insert_id();
							}
							$tab_insert[]="(".$this->id.",'".$this->type."',".$code_champ.",".$code_ss_champ.",".$num_word.",".$p_perso->get_pond($code_ss_champ).",$pos)";
							$pos++;
						}
					}
				}
			}
			$req_insert="insert into cms_editorial_words_global_index(num_obj,type,code_champ,code_ss_champ,num_word,pond,position) values ".implode(',',$tab_insert);
			mysql_query($req_insert,$dbh);
			//la table pour les recherche exacte
			$req_insert="insert into cms_editorial_fields_global_index(num_obj,type,code_champ,code_ss_champ,ordre,value,lang,pond) values ".implode(',',$tab_field_insert);
			mysql_query($req_insert,$dbh);				
		}
	}
	
	public static function get_format_data_structure($type,$full=true){
		global $msg;
		$main_fields = array();
		$main_fields[] = array(
			'var' => "id",
			'desc'=> $msg['cms_module_common_datasource_desc_id_'.$type]
		);
		if($type == "section"){
			$main_fields[] = array(
				'var' => "num_parent",
				'desc'=> $msg['cms_module_common_datasource_desc_num_parent']
			);		
		}else{
			$main_fields[] = array(
				'var' => "parent",
				'desc'=> $msg['cms_module_common_datasource_desc_parent'],
				'children' => self::prefix_var_tree(cms_section::get_format_data_structure(false,false),"parent")
			);
		}
		$main_fields[] = array(
			'var' => "title",
			'desc' => $msg['cms_module_common_datasource_desc_title']
		);
		$main_fields[] = array(
			'var' => "resume",
			'desc' => $msg['cms_module_common_datasource_desc_resume']
		);
		if($type == "article"){
			$main_fields[] = array(
				'var' => "content",
				'desc' => $msg['cms_module_common_datasource_desc_content']
			);
		}		
		$main_fields[] = array(
			'var' => "logo",
			'children' => array(
				array(
					'var' => "logo.small_vign",
					'desc' => $msg['cms_module_common_datasource_desc_small_vign']
				),
				array(
					'var' => "logo.vign",
					'desc' => $msg['cms_module_common_datasource_desc_vign']
				),
				array(
					'var' => "logo.large",
					'desc' => $msg['cms_module_common_datasource_desc_large']
				),			
				array(
					'var' => "logo.exists",
					'desc' => $msg['cms_module_common_datasource_desc_logo_exists']
				),
			),			
			'desc' => $msg['cms_module_common_datasource_desc_logo']
		);
		$main_fields[] = array(
			'var' => "publication_state",
			'desc' => $msg['cms_module_common_datasource_desc_publication_state']
		);
		$main_fields[] = array(
			'var' => "start_date",
			'desc' => $msg['cms_module_common_datasource_desc_start_date']
		);
		$main_fields[] = array(
			'var' => "end_date",
			'desc' => $msg['cms_module_common_datasource_desc_end_date']
		);
		$main_fields[] = array(
			'var' => "descriptors",
			'desc' => $msg['cms_module_common_datasource_desc_descriptors']
		);
		$main_fields[] = array(
			'var' => "type",
			'desc' => $msg['cms_module_common_datasource_desc_type_'.$type]
		);
		$main_fields[] = array(
			'var' => "fields_type",
			'desc' => $msg['cms_module_common_datasource_desc_fields_type_'.$type]
		);
		$main_fields[] = array(
			'var' => "create_date",
			'desc' => $msg['cms_module_common_datasource_desc_create_date']
		);	
		
		//pour les types de contenu
		$fields_type=array();
		$types = new cms_editorial_types($type);
		$fields_type = $types->get_format_data_structure($full);
		return array(
			array(
				'var' => $msg['cms_module_common_datasource_main_fields'],
				"children" => $main_fields
			),
			array(
				'var' =>  $msg['cms_module_common_datasource_types'],
				'desc' => $msg['cms_module_common_datasource_desc_types'],
				"children" => $fields_type			
			)
		);	
		
	}
	
	public function prefix_var_tree($tree,$prefix){
		for($i=0 ; $i<count($tree) ; $i++){
			$tree[$i]['var'] = $prefix.".".$tree[$i]['var'];
			if($tree[$i]['children']){
				$tree[$i]['children'] = self::prefix_var_tree($tree[$i]['children'],$prefix);
			}
		}
		return $tree;
	}
	
	public function get_documents_form(){
		$collections = new cms_collections();
		return $collections->get_documents_form($this->documents_linked);
	}
	
	public function get_documents(){
		$this->documents_linked =array();
		$query = "select document_link_num_document from cms_documents_links where document_link_type_object = '".$this->type."' and document_link_num_object = ".$this->id;
		$result = mysql_query($query);
		
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$this->documents_linked[] = $row->document_link_num_document;
			}
		}	
	}
	
	public function save_documents(){
		//on commence par tout virer
		$query = "delete from cms_documents_links where document_link_type_object = '".$this->type."' and document_link_num_object = ".$this->id;
		$result = mysql_query($query);
		
		if(count($this->documents_linked)){
			$query = "insert into cms_documents_links (document_link_type_object,document_link_num_object,document_link_num_document) values";
			$documents ="";
			foreach($this->documents_linked as $doc){
				if($documents)$documents.=",";
				$documents.="('".$this->type."',".$this->id.",".$doc.")";
			}
			mysql_query($query.$documents);
		}
	}
}