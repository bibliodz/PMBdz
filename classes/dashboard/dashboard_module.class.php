<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: dashboard_module.class.php,v 1.1 2014-01-07 10:16:16 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/h2o/h2o.php");

class dashboard_module {
	protected $alert_url="";		// URL � appeler pour les alertes
	protected $default_template="template"; // Nom du template Dango � utiliser par d�faut
	public $infos=array();			// Structure de donn�es correspondantes aux donn�es du module
 	public $module="";
	
	public function __construct(){
		global $base_path;
	}
	
	public function get_infos() {
		global $dbh;
		global $include_path;
		$xml = new DOMDocument();
		$filepath = $include_path."/dashboard/".$this->module."/infos";
		if(file_exists($filepath."_subst.xml")){
			$filepath.="_subst.xml";
		}else{
			$filepath.=".xml";
		}
		if(file_exists($filepath)){
			$xml->load($filepath);
			$elements = $xml->getElementsByTagName("information");	
			for($i=0 ; $i<$elements->length ; $i++){
				$name = $this->charset_normalize($elements->item($i)->getElementsByTagName('name')->item(0)->nodeValue,"utf-8");
				$query = $this->charset_normalize($elements->item($i)->getElementsByTagName('query')->item(0)->nodeValue,"utf-8");
				if(!$query){
					$fonction = $elements->item($i)->getElementsByTagName('fonction')->item(0);
					$class = $contruct = $params = $internal = "";
					if($fonction->attributes->length>0){
						$type = $this->charset_normalize($fonction->attributes->item(0)->nodeValue,"utf-8");
					}
					$class_infos = $elements->item($i)->getElementsByTagName('class');
					if($class_infos->length){
						$class_name = $this->charset_normalize($class_infos->item(0)->attributes->item(0)->nodeValue,"utf-8");
						$contructor_params = $class_infos->item(0)->getElementsByTagName('contruct_param');
						$constructor_parameters = array();
						for ($j=0 ; $j<$contructor_params->length ; $j++){
							$constructor_parameters[] = $this->charset_normalize($contructor_params->item($j)->nodeValue,"utf-8");
						}
					}				
					$params = $fonction->getElementsByTagName('param');
					$parameters = array();
					for ($j=0 ; $j<$params->length ; $j++){
						$parameters[] = $this->charset_normalize($params->item($j)->nodeValue,"utf-8");
					}
					$method = $this->charset_normalize($fonction->getElementsByTagName("method")->item(0)->nodeValue,"utf-8");
					if($type="internal"){
						$this->infos[$name] = call_user_func_array(array($this,$method), $parameters);
					}else if($class_name != ""){
						$reflectionObject = new ReflectionClass($class_name);
						$obj = $reflectionObject->newInstanceArgs($constructor_parameters);
						$this->infos[$name] = call_user_func_array(array($obj,$method), $parameters);
					}else{
						$this->infos[$name] = call_user_func_array($method, $parameters);
					}
				}else{	
					$result = mysql_query($query,$dbh);
					$this->infos[$name]=array();
					if(mysql_num_rows($result)){
						while($row = mysql_fetch_assoc($result)){
							$this->infos[$name][] = $row;
						}
					}
				}
				$action ="";
				if($elements->item($i)->getElementsByTagName('action')->length){
					$action = $this->charset_normalize($elements->item($i)->getElementsByTagName('action')->item(0)->nodeValue,"utf-8");
				}
			}
		}
	}

	public function render_infos($template=""){
		$template = $this->load_template($template);
		
		if(!count($this->infos)) $this->get_infos();
		if(count($this->infos)){
			$rendered = array(
				array(
					'name' => $this->module_name,
					'alert_url' => $this->alert_url,
					'module' => $this->module,
					'id' => "dashboard_".$this->module."_0",
					'html' => h2o($template)->render($this->infos)
				)	
			);
		}else{
			$rendered = array();
		}
		return $rendered;
	}

	public function render($template="template"){
		$html = array();
		$html[]=$this->render_infos($template);
// 		$html.=$this->render_alert();
		return $html;
	}
	
	public  function get_quick_params_form(){
		return "";
	}
	public  function save_quick_params(){
		return true;
	}
	
	protected  function get_user_param_form($field){
		global $msg,$dbh,$charset;
		global $$field;
		
		global $location_user_section;
		$field_deb = substr($field,0,6);
// 		$html="
// 		<script type='text/javascript'>
// 			function dashboard_save_params(name,value){
// 				var req= new http_request();
// 				req.request('./ajax.php?module=".$this->module."&categ=dashboard&sub=save_quick_params',1,'".$field."='+value,1,dashboard_params_saved);
// 			}
// 		</script>";
		$html="";
		
		switch ($field_deb) {
			case "deflt_" :
				if ($field=="deflt_styles") {
					$html_style="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>
							<div class='colonne_suite'>".make_user_style_combo($$field)."
							</div>
						</div>\n";
				} elseif ($field=="deflt_docs_location") {
					//visibilit� des exemplaires
					if ($pmb_droits_explr_localises && $usr->explr_visible_mod) $where_clause_explr = "idlocation in (".$usr->explr_visible_mod.") and";
					else $where_clause_explr = "";
					$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location, docsloc_section where $where_clause_explr num_location=idlocation order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "account_calcule_section(this);", $$field, "", "","","",0);
					$html.="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>\n
							<div class='colonne_suite'>".$selector."
							</div>
						</div>\n";
					//localisation de l'utilisateur pour le calcul de la section
					$location_user_section = $$field;
				} elseif ($field=="deflt_collstate_location") {
					$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "", $$field, "", "","0",$msg["all_location"],0);
					$html.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>"			
						.$selector.
						"</div></div>\n";
				} elseif ($field=="deflt_resas_location") {
					$selector = gen_liste ("select distinct idlocation, location_libelle from docs_location order by 2 ", "idlocation", "location_libelle", 'form_'.$field, "", $$field, "", "","0",$msg["all_location"],0);
					$html.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>"			
						.$selector.
						"</div></div>\n";
				} elseif ($field=="deflt_docs_section") {
					// calcul des sections
					$selector="";
					if (!$location_user_section) $location_user_section = $deflt_docs_location;
					if ($pmb_droits_explr_localises && $usr->explr_visible_mod) $where_clause_explr = "where idlocation in (".$usr->explr_visible_mod.")";
					else $where_clause_explr = "";
					$rqtloc = "SELECT idlocation FROM docs_location $where_clause_explr order by location_libelle";
					$resloc = mysql_query($rqtloc, $dbh);
					while ($loc=mysql_fetch_object($resloc)) {
						$requete = "SELECT idsection, section_libelle FROM docs_section, docsloc_section where idsection=num_section and num_location='$loc->idlocation' order by section_libelle";
						$result = mysql_query($requete, $dbh);
						$nbr_lignes = mysql_num_rows($result);
						if ($nbr_lignes) {
							if ($loc->idlocation==$location_user_section ) $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:block\">\r\n";
							else $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:none\">\r\n";
							$selector .= "<select name='f_ex_section".$loc->idlocation."' id='f_ex_section".$loc->idlocation."'>\r\n";
							while($line = mysql_fetch_row($result)) {
								$selector .= "<option value='$line[0]' ";
								$selector .= (($line[0] == $$field) ? "selected='selected' >" : '>');
					 			$selector .= htmlentities($line[1],ENT_QUOTES, $charset).'</option>\r\n';
							}
							$selector .= '</select></div>';
						}
					}
					$html.="
						<div class='row'>
							<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
							</div>\n
							<div class='colonne_suite'>".$selector."
							</div>
						</div>\n";
				} elseif ($field=="deflt_upload_repertoire") {
					$selector = "";
						$requpload = "select repertoire_id, repertoire_nom from upload_repertoire";
						$resupload = mysql_query($requpload, $dbh);
						$selector .=  "<div id='upload_section'>";
						$selector .= "<select name='form_deflt_upload_repertoire'>";
						$selector .= "<option value='0'>".$msg[upload_repertoire_sql]."</option>";
						while(($repupload = mysql_fetch_object($resupload))){
							$selector .= "<option value='".$repupload->repertoire_id."' ";
							if ($$field == $repupload->repertoire_id ) {
								$selector .= "selected='selected' ";
							}
							$selector .= ">";
							$selector .= htmlentities($repupload->repertoire_nom,ENT_QUOTES,$charset)."</option>";
						}
						$selector .=  "</select></div>";
						$html.="
							<div class='row'>
								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
								</div>
								<div class='colonne_suite'>".$selector."
								</div>
							</div>";
				} elseif($field=="deflt_import_thesaurus"){
					$requete="select * from thesaurus order by 2";
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_num_rows($resultat_liste);
					if ($nb_liste==0) {
						$html.="" ;
					} else {
						$html.="
							<div class='row'>
								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
								</div>\n
								<div class='colonne_suite'>
									<select class='saisie-30em' name=\"form_".$field."\">";
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_row ( $resultat_liste );
							$html.="<option value=\"".$liste_values[0]."\" " ;
							if ($$field==$liste_values[0]) {
								$html.="selected='selected' " ;
							}
							$html.=">".$liste_values[1]."</option>\n" ;
							$j++;
						}
						$html.="</select>
								</div>
							</div>\n" ;
					}
					
				} elseif ($field=="deflt_short_loan_activate") {
						$html.="<div class='row'><div class='colonne60'>".$msg[$field]."</div>\n
							<div class='colonne_suite'>
							<input type='checkbox' class='checkbox'";
						if ($$field==1) $html.=" checked"; 
						$html.=" value='1' name='form_$field'></div></div>\n" ;
				} elseif ($field=="deflt_cashdesk"){
					$requete="select * from cashdesk order by cashdesk_name";
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_num_rows($resultat_liste);
					if ($nb_liste==0) {
						$html.="" ;
					} else {
// 						$html.="
// 							<div class='row'>
// 								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
// 								</div>\n
// 								<div class='colonne_suite'>
// 									<select class='saisie-30em' name=\"form_".$field."\" onchange='dashboard_save_params(this.name,this.value)'>";
						
						$html.="
							<div class='row'>
								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
								</div>\n
								<div class='colonne_suite'>
									<select class='saisie-30em' name=\"form_".$field."\">";
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_object( $resultat_liste );
							$html.="<option value=\"".$liste_values->cashdesk_id."\" " ;
							if ($$field==$liste_values->cashdesk_id) {
								$html.="selected" ;
							}
							$html.=">".htmlentities($liste_values->cashdesk_name,ENT_QUOTES,$charset)."</option>\n" ;
							$j++;
						}
						$html.="</select>
								</div>
							</div>\n" ;
					}
				}else {
					$deflt_table = substr($field,6);
					if($deflt_table == "integration_notice_statut") $deflt_table= "notice_statut";
					switch($field) {
						case "deflt_entites":
							$requete="select id_entite, raison_sociale from ".$deflt_table." where type_entite='1' order by 2 ";
							break;
						case "deflt_exercices":
							$requete="select id_exercice, libelle from ".$deflt_table." order by 2 ";
							break;
						case "deflt_rubriques":
							$requete="select id_rubrique, concat(budgets.libelle,':', rubriques.libelle) from ".$deflt_table." join budgets on num_budget=id_budget order by 2 ";
							break;
						default :
							$requete="select * from ".$deflt_table." order by 2";
							break;
					}
	
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_num_rows($resultat_liste);
					if ($nb_liste==0) {
						$html.="" ;
					} else {
						$html.="
							<div class='row'>
								<div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;
								</div>\n
								<div class='colonne_suite'>
									<select class='saisie-30em' name=\"form_".$field."\">";
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_row ( $resultat_liste );
							$html.="<option value=\"".$liste_values[0]."\" " ;
							if ($$field==$liste_values[0]) {
								$html.="selected='selected' " ;
							}
							$html.=">".$liste_values[1]."</option>\n" ;
							$j++;
						}
						$html.="</select>
								</div>
							</div>\n" ;
					}
				}
				break;
	
			case "param_" :
				if ($field=="param_allloc") {
					$html="<div class='row'><div class='colonne60'>".$msg[$field]."</div>\n
						<div class='colonne_suite'>
						<input type='checkbox' class='checkbox'";
					if ($$field==1) $html.=" checked";
					$html.=" value='1' name='form_$field'></div></div>\n" ;
				} else {
					$html.="<div class='row'>";
					//if (strpos($msg[$field],'<br />')) $param_user .= "<br />";
					$html.="<input type='checkbox' class='checkbox'";
					if ($$field==1) $html.=" checked";
					$html.=" value='1' name='form_$field'>\n
						$msg[$field]
						</div>\n";
				}
				break ;
	
			case "value_" :
				switch ($field) {
					case "value_deflt_fonction" :
						$flist=new marc_list('function');
						$f=$flist->table[$$field];
						$html.="<div class='row'><div class='colonne60'>
						$msg[$field]&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>
						<input type='text' class='saisie-30emr' id='form_value_deflt_fonction_libelle' name='form_value_deflt_fonction_libelle' value='".htmlentities($f,ENT_QUOTES, $charset)."' />
						<input type='button' class='bouton_small' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=function&caller=userform&p1=form_value_deflt_fonction&p2=form_value_deflt_fonction_libelle', 'select_func0', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
						<input type='button' class='bouton_small' value='X' onclick=\"this.form.elements['form_value_deflt_fonction'].value='';this.form.elements['form_value_deflt_fonction_libelle'].value='';return false;\" />
						<input type='hidden' name='form_value_deflt_fonction' id='form_value_deflt_fonction' value=\"$$field\" />
						</div></div><br />";
						break;
					case "value_deflt_lang" :
						$llist=new marc_list('lang');
						$l=$llist->table[$$field];
						$html.="<div class='row'><div class='colonne60'>
						$msg[$field]&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>
						<input type='text' class='saisie-30emr' id='form_value_deflt_lang_libelle' name='form_value_deflt_lang_libelle' value='".htmlentities($l,ENT_QUOTES, $charset)."' />
						<input type='button' class='bouton_small' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=lang&caller=userform&p1=form_value_deflt_lang&p2=form_value_deflt_lang_libelle', 'select_lang', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
						<input type='button' class='bouton_small' value='X' onclick=\"this.form.elements['form_value_deflt_lang'].value='';this.form.elements['form_value_deflt_lang_libelle'].value='';return false;\" />
						<input type='hidden' name='form_value_deflt_lang' id='form_value_deflt_lang' value=\"$$field\" />
						</div></div><br />";
						break;
					case "value_deflt_relation" :
					case "value_deflt_relation_serial" :
					case "value_deflt_relation_bulletin" :
					case "value_deflt_relation_analysis" :
						$html.="<div class='row'><div class='colonne60'>
						$msg[$field]&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>";
						
						$liste_type_relation_down=new marc_list("relationtypedown");
						$liste_type_relation_up=new marc_list("relationtypeup");
						$liste_type_relation_both=array();
						
						foreach($liste_type_relation_up->table as $key_up=>$val_up){
							foreach($liste_type_relation_down->table as $key_down=>$val_down){
								if($val_up==$val_down){
									$liste_type_relation_both[$key_down]=$val_down;
									unset($liste_type_relation_down->table[$key_down]);
									unset($liste_type_relation_up->table[$key_up]);
								}
							}
						}
						
						$html.="<select onchange='' name='form_".$field."' size='1'>
						<optgroup class='erreur' label='$msg[notice_lien_montant]'>";
						
						foreach($liste_type_relation_up->table as $key=>$val){
							if($key.'-up'==$$field){
								$html.='<option  style="color:#000000" value="'.$key.'-up" selected="selected">'.$val.'</option>';
							}else{
								$html.='<option  style="color:#000000" value="'.$key.'-up">'.$val.'</option>';
							}
						}
						$html.="</optgroup>
						<optgroup class='erreur' label='$msg[notice_lien_descendant]'>";
						
						foreach($liste_type_relation_down->table as $key=>$val){
							if($key.'-down'==$$field){
								$html.='<option  style="color:#000000" value="'.$key.'-down" selected="selected" >'.$val.'</option>';
							}else{
								$html.='<option  style="color:#000000" value="'.$key.'-down">'.$val.'</option>';
							}
						}
						$html.="</optgroup>
						<optgroup class='erreur' label='$msg[notice_lien_symetrique]'>";
						
						foreach($liste_type_relation_both as $key=>$val){
							if($key.'-down'==$$field){
								$html.='<option  style="color:#000000" value="'.$key.'-down" selected="selected" >'.$val.'</option>';
							}else{
								$html.='<option  style="color:#000000" value="'.$key.'-down">'.$val.'</option>';
							}
						}
						$html.="</optgroup>
						</select>";
						$html.="</div></div><br />";
						break;
					default :
						$html.="<div class='row'><div class='colonne60'>
						$msg[$field]&nbsp;:&nbsp;</div>\n
						<div class='colonne_suite'>
						<input type='text' class='saisie-20em' name='form_$field' value='".htmlentities($$field,ENT_QUOTES, $charset)."' />
						</div></div><br />";
						break;
				}
				break ;
	
			case "deflt2" :
				if ($field=="deflt2docs_location") {
					// localisation des lecteurs
					$deflt_table = substr($field,6);
					$requete="select * from ".$deflt_table." order by 2";
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_num_rows($resultat_liste);
					if ($nb_liste==0) {
						$html.="" ;
					} else {
						$html.="
							<div class='row'><div class='colonne60'>".
							$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
							<div class='colonne_suite'>
							<select class='saisie-30em' name=\"form_".$field."\">";
	
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_row ( $resultat_liste );
							$html.="<option value=\"".$liste_values[0]."\" " ;
							if ($$field==$liste_values[0]) {
								$html.="selected='selected' " ;
							}
							$html.=">".$liste_values[1]."</option>\n" ;
							$j++;
						}
						$html.="</select></div></div>!!param_allloc!!<br />\n" ;
					}
				} else {
					$deflt_table = substr($field,6);
					$requete="select * from ".$deflt_table." order by 2 ";
					$resultat_liste=mysql_query($requete,$dbh);
					$nb_liste=mysql_numrows($resultat_liste);
					if ($nb_liste==0) {
						$html.="" ;
					} else {
						$html.="
								<div class='row'><div class='colonne60'>".
									$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
								<div class='colonne_suite'>
									<select class='saisie-30em' name=\"form_".$field."\">";
						$j=0;
						while ($j<$nb_liste) {
							$liste_values = mysql_fetch_row ( $resultat_liste );
							$html.="<option value=\"".$liste_values[0]."\" " ;
							if ($$field==$liste_values[0]) {
								$html.="selected='selected' " ;
							}
							$html.=">".$liste_values[1]."</option>\n" ;
							$j++;
						}
						$html.="</select></div></div>\n" ;
					}
				}
				break;
	
			case "xmlta_" :
				switch($field) {
					case "xmlta_indexation_lang" :
						$langues = new XMLlist("$include_path/messages/languages.xml");
						$langues->analyser();
						$clang = $langues->table;
					
						$combo = "<select name='form_".$field."' id='form_".$field."' class='saisie-20em' >";
						if(!$$field) $combo .= "<option value='' selected>--</option>";
						else $combo .= "<option value='' >--</option>";
						while(list($cle, $value) = each($clang)) {
							// arabe seulement si on est en utf-8
							if (($charset != 'utf-8' and $user_lang != 'ar') or ($charset == 'utf-8')) {
								if(strcmp($cle, $$field) != 0) $combo .= "<option value='$cle'>$value ($cle)</option>";
								else $combo .= "<option value='$cle' selected>$value ($cle)</option>";
							}
						}
						$combo .= "</select>";
						$html.="
						<div class='row'><div class='colonne60'>".
						$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
						<div class='colonne_suite'>$combo</div></div>\n" ;
						break;
					case "xmlta_doctype_serial" :
						$html.="
							<div class='row'><div class='colonne60'>".
							$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
							<div class='colonne_suite'>";
						$select_doc = new marc_select("doctype", "form_".$field, $$field, "");
						$html.= $select_doc->display ;
						$html.="</div></div>\n" ;
						break;
					case "xmlta_doctype_bulletin" :
					case "xmlta_doctype_analysis" :
						$html.="
							<div class='row'><div class='colonne60'>".
							$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
							<div class='colonne_suite'>";
						$select_doc = new marc_select("doctype", "form_".$field, $$field, "","0",$msg[$field."_parent"]);
						$html.= $select_doc->display ;
						$html.="</div></div>\n" ;
						break;
					default :
						$deflt_table = substr($field,6);
						$html.="
							<div class='row'><div class='colonne60'>".
							$msg[$field]."&nbsp;:&nbsp;</div>\n";
						$html.= "
							<div class='colonne_suite'>";
						$select_doc = new marc_select("$deflt_table", "form_".$field, $$field, "");
						$html.= $select_doc->display ;
						$html.="</div></div>\n" ;
						break;
				}
			case "deflt3" :
				$q='';
				$t=array();
				switch($field) {
					case "deflt3bibli":
						$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
						$q.="select id_entite, raison_sociale from entites where type_entite='1' order by 2 ";
						break;
					case "deflt3exercice":
						$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
						$q.="select id_exercice, libelle from exercices order by 2 ";
						break;
					case "deflt3rubrique":
						$q="select 0,'".addslashes($msg['deflt3none'])."' union ";
						$q.="select id_rubrique, concat(budgets.libelle,':', rubriques.libelle) from rubriques join budgets on num_budget=id_budget order by 2 ";
						break;
					case "deflt3dev_statut":
						$t=actes::getStatelist(TYP_ACT_DEV);
						break;
					case "deflt3cde_statut":
						$t=actes::getStatelist(TYP_ACT_CDE);
						break;
					case "deflt3liv_statut":
						$t=actes::getStatelist(TYP_ACT_LIV);
						break;
					case "deflt3fac_statut":
						$t=actes::getStatelist(TYP_ACT_FAC);
						break;
					case "deflt3sug_statut":
						$m=new suggestions_map();
						$t=$m->getStateList();
						break;
					case 'deflt3lgstatcde':
					case 'deflt3lgstatdev':
						$q=lgstat::getList('QUERY');
						break;
					case 'deflt3receptsugstat':
						$m=new suggestions_map();
						$t=$m->getStateList('ORDERED',TRUE);
						break;
				}
				if($q) {
					$r=mysql_query($q, $dbh);
					$nb=mysql_num_rows($r);
					while($row=mysql_fetch_row($r)) {
						$t[$row[0]]=$row[1];
					}
				}
				if (count($t)) {
					$html.="<div class='row'><div class='colonne60'>".$msg[$field]."&nbsp;:&nbsp;</div>\n";
					$html.= "<div class='colonne_suite'><select class='saisie-30em' name=\"form_".$field."\">";
					foreach($t as $k=>$v) {
						$html.="<option value=\"".$k."\" " ;
						if ($$field==$k) {
							$html.="selected='selected' " ;
						}
						$html.=">".htmlentities($v, ENT_QUOTES, $charset)."</option>\n" ;
					}
					$html.="</select></div></div><br />\n";
				}
				break;
	
			case "speci_" :
				$speci_func = substr($field, 6);
				eval('$speci_user.= get_'.$speci_func.'($id, $$field, $i, \'userform\');');
				break;
	
			case "explr_" :
				$$field=$$field;
				break;
			default :
				break ;
		}
		
		switch($field){
			case "deflt2docs_location" : 
				$html = str_replace("!!param_allloc!!", self::get_user_param_form("param_allloc"), $html);
				break;			
		}		
		return $html;
				

	}
	
	private function load_template($template=""){
		global $include_path;
		global $lang;
		
		if(!$template) $template = $this->template;
		if(!$template) $template = "template";
		
		$filepath = $include_path."/dashboard/".$this->module."/".$template;
		if(file_exists($filepath."_subst.xml")){
			$filepath.="_subst.xml";
		}else{
			$filepath.=".xml";
		}
		
		if(!file_exists($filepath)){
			return false;
		}else{
			$xml = new DOMDocument();
			$xml->load($filepath);
			//langue de r�f�rence
			$default_lang = "";
			$xml_template = $xml->getElementsByTagName("template")->item(0);
			
			if($xml_template->hasAttributes()){
				$attributes = $xml_template->attributes;
				for($i=0 ; $i<$attributes->length ; $i++){
					if($attributes->item($i)->nodeName == "default_lang"){
						//dom retourne de l'utf-8 � tous les coups...
						$default_lang = $this->charset_normalize($attributes->item($i)->nodeValue,"utf-8");
						break;
					}
				}
			}
			//on cherche le template qui va bien...
			$html_templates = $xml_template->getElementsByTagName("content");
			$template = array();
			for($i=0 ; $i<$html_templates->length ; $i++){
				if($i == 0 || $html_templates->length == 1){
					$template = $this->charset_normalize($html_templates->item($i)->nodeValue,"utf-8");
				}
				$attributes = $html_templates->item($i)->attributes;
				for($j=0 ; $j<$attributes->length ; $j++){
					if($attributes->item($j)->nodeName == "lang"){
						$current_lang = $this->charset_normalize($attributes->item($j)->nodeValue,"utf-8");
						if($current_lang == $lang){
							$template = $this->charset_normalize($html_templates->item($i)->nodeValue,"utf-8");
							break(2);
						}
					}
					if($attributes->item($j)->nodeName == "default_lang"){
						$current_lang = $this->charset_normalize($attributes->item($j)->nodeValue,"utf-8");
						if($current_lang == $lang){
							$template = $this->charset_normalize($html_templates->item($i)->nodeValue,"utf-8");
							break;
						}
					}
				}
			}
		}
		return $template;
	}
	protected static function charset_normalize($elem,$input_charset){
		global $charset;
		if(is_array($elem)){
			foreach ($elem as $key =>$value){
				$elem[$key] = self::charset_normalize($value,$input_charset);
			}
		}else{
			//PMB dans un autre charset, on converti la chaine...
			$elem = self::clean_cp1252($elem, $input_charset);
			if($charset != $input_charset){
				$elem = iconv($input_charset,$charset,$elem);
			}
		}
		return $elem;
	}
	protected static function clean_cp1252($str,$charset){
		switch($charset){
			case "utf-8" :
				$cp1252_map = array(
				"\x80" => "EUR", /* EURO SIGN */
				"\x82" => "\xab", /* SINGLE LOW-9 QUOTATION MARK */
				"\x83" => "\x66",     /* LATIN SMALL LETTER F WITH HOOK */
				"\x84" => "\xab", /* DOUBLE LOW-9 QUOTATION MARK */
				"\x85" => "...", /* HORIZONTAL ELLIPSIS */
				"\x86" => "?", /* DAGGER */
				"\x87" => "?", /* DOUBLE DAGGER */
				"\x88" => "?",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
				"\x89" => "?", /* PER MILLE SIGN */
				"\x8a" => "S",   /* LATIN CAPITAL LETTER S WITH CARON */
				"\x8b" => "\x3c", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
				"\x8c" => "OE",   /* LATIN CAPITAL LIGATURE OE */
				"\x8e" => "Z",   /* LATIN CAPITAL LETTER Z WITH CARON */
				"\xe2\x80\x98" => "\x27", /* LEFT SINGLE QUOTATION MARK */
				"\xe2\x80\x99" => "\x27", /* RIGHT SINGLE QUOTATION MARK */
				"\x93" => "\x22", /* LEFT DOUBLE QUOTATION MARK */
				"\x94" => "\x22", /* RIGHT DOUBLE QUOTATION MARK */
				"\x95" => "\b7", /* BULLET */
				"\x96" => "\x20", /* EN DASH */
				"\x97" => "\x20\x20", /* EM DASH */
				"\x98" => "\x7e",   /* SMALL TILDE */
				"\x99" => "?", /* TRADE MARK SIGN */
				"\x9a" => "S",   /* LATIN SMALL LETTER S WITH CARON */
				"\x9b" => "\x3e;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
				"\x9c" => "oe",   /* LATIN SMALL LIGATURE OE */
				"\x9e" => "Z",   /* LATIN SMALL LETTER Z WITH CARON */
				"\x9f" => "Y"    /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
				);
				break;
			case "iso8859-1" :
				$cp1252_map = array(
				"\x80" => "EUR", /* EURO SIGN */
				"\x82" => "\xab", /* SINGLE LOW-9 QUOTATION MARK */
				"\x83" => "\x66",     /* LATIN SMALL LETTER F WITH HOOK */
				"\x84" => "\xab", /* DOUBLE LOW-9 QUOTATION MARK */
				"\x85" => "...", /* HORIZONTAL ELLIPSIS */
				"\x86" => "?", /* DAGGER */
				"\x87" => "?", /* DOUBLE DAGGER */
				"\x88" => "?",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
				"\x89" => "?", /* PER MILLE SIGN */
				"\x8a" => "S",   /* LATIN CAPITAL LETTER S WITH CARON */
				"\x8b" => "\x3c", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
				"\x8c" => "OE",   /* LATIN CAPITAL LIGATURE OE */
				"\x8e" => "Z",   /* LATIN CAPITAL LETTER Z WITH CARON */
				"\x91" => "\x27", /* LEFT SINGLE QUOTATION MARK */
				"\x92" => "\x27", /* RIGHT SINGLE QUOTATION MARK */
				"\x93" => "\x22", /* LEFT DOUBLE QUOTATION MARK */
				"\x94" => "\x22", /* RIGHT DOUBLE QUOTATION MARK */
				"\x95" => "\b7", /* BULLET */
				"\x96" => "\x20", /* EN DASH */
				"\x97" => "\x20\x20", /* EM DASH */
				"\x98" => "\x7e",   /* SMALL TILDE */
				"\x99" => "?", /* TRADE MARK SIGN */
				"\x9a" => "S",   /* LATIN SMALL LETTER S WITH CARON */
				"\x9b" => "\x3e;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
				"\x9c" => "oe",   /* LATIN SMALL LIGATURE OE */
				"\x9e" => "Z",   /* LATIN SMALL LETTER Z WITH CARON */
				"\x9f" => "Y"    /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
				);
				break;
		}
		return strtr($str, $cp1252_map);
	}	
}