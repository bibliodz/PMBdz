<?php 
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facette_search.class.php,v 1.41 2014-03-07 15:32:27 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($base_path."/includes/notice_affichage.inc.php");
require_once($class_path."/acces.class.php");
require_once($class_path."/suggest.class.php");

class facettes{
	public $tab_facettes_record;
	public $tab_facettes_opac;
	
	function facettes(){
		$tab_facettes_record = array();
		$tab_facettes_opac = array();
	}
	
	function facette_existing(){
		global $msg,$dbh,$charset;
		
		$req = "SELECT * FROM facettes WHERE facette_visible=1 ORDER BY facette_order, facette_name";
		$req = mysql_query($req,$dbh);
		while($rslt = mysql_fetch_object($req)){
			$tab_temp = array();
			$tab_temp = array(
					'id'=> $rslt->id_facette+0,
					'name'=>$rslt->facette_name,
					'id_critere'=>$rslt->facette_critere+0,
					'id_ss_critere'=>$rslt->facette_ss_critere+0,
					'nb_result'=>$rslt->facette_nb_result+0,
					'limit_plus'=>$rslt->facette_limit_plus+0,
					'type_sort'=>$rslt->facette_type_sort+0,
					'order_sort'=>$rslt->facette_order_sort+0
					);
			
			$this->tab_facettes_record[]= $tab_temp;
		}
		return $this->tab_facettes_record;
	}
	
	function nb_results_by_facette($tab_id_notice){
		global $dbh;
		global $lang;
		global $msg;
		$size = sizeof($this->tab_facettes_record);
		$i = 0;
		$array_result = array();
		if($tab_id_notice!=""){
			for($i;$i<$size;$i++){
				$limit = "";
				$order_sort = "";
				$type_sort = "";
				$end_req_sql="";
				if ($this->tab_facettes_record[$i]['type_sort']==0) {
					$type_sort = "nb_result";
				} else {
					$type_sort = "value";
				}
				if($this->tab_facettes_record[$i]['order_sort']==0){
					$order_sort = "asc";
				} else {
					$order_sort = "desc";
				}
				if($this->tab_facettes_record[$i]['nb_result']>0){
					$limit = "LIMIT"." ".$this->tab_facettes_record[$i]['nb_result'];
				}
				$end_req_sql = "order by ".$type_sort." ".$order_sort." ".$limit;
				
				//AND (lang = '' OR lang = ".$lang.")
				$req = "select distinct value ,count(id_notice) as nb_result from (SELECT value,id_notice FROM notices_fields_global_index 
										WHERE id_notice IN (".$tab_id_notice.")
										AND code_champ = ".($this->tab_facettes_record[$i]['id_critere']+0)."
										AND code_ss_champ = ".($this->tab_facettes_record[$i]['id_ss_critere']+0)."
										AND lang in ('','".$lang."')) as sub 
										GROUP BY value ".$end_req_sql;
				$res = @mysql_query($req,$dbh);
				$j=0;
				$array_tmp = array();
				$array_value = array();
				$array_nb_result = array();
				
				if(mysql_num_rows($res)){
					while($rslt = mysql_fetch_object($res)){				
						$array_tmp[$j] =  $rslt->value." "."(".($rslt->nb_result+0).")";
						$array_value[$j] = $rslt->value;
						$array_nb_result[$j] = ($rslt->nb_result+0);
							
						
						$j++;
					} 
				}			
				$array_result[] = array(
					'name'=>$this->tab_facettes_record[$i]['name'],
					'facette'=>$array_tmp,
					'code_champ'=>$this->tab_facettes_record[$i]['id_critere'],
					'code_ss_champ'=>$this->tab_facettes_record[$i]['id_ss_critere'],
					'value'=>$array_value,
					'nb_result'=>$array_nb_result,
					'size_to_display'=>$this->tab_facettes_record[$i]['limit_plus']
					);
			}
		}
		
		$this->tab_facettes_opac = $array_result;
	}
	
	function see_more($json_facette_plus){
		global $charset;
		
		$facette_opac=$json_facette_plus;
		$arrayRetour = array();
		$count=count($facette_opac['facette']);
		for($j=0;$j<$count;$j++){
			$fields_search = "&facette_test=1&name=".rawurlencode($facette_opac['name'])."&value=".rawurlencode($facette_opac['value'][$j])."&champ=".$facette_opac['code_champ']."&ss_champ=".$facette_opac['code_ss_champ']."";
			$tmpArray = array();
			$tmpArray['facette_libelle'] = htmlentities($facette_opac['value'][$j],ENT_QUOTES,$charset);
			$tmpArray['facette_number'] = htmlentities($facette_opac['nb_result'][$j],ENT_QUOTES,$charset);
			if ($charset!='utf-8') {
				$tmpArray['facette_value'] = json_encode(array(utf8_encode($facette_opac['name']),utf8_encode($facette_opac['value'][$j]),$facette_opac['code_champ'],$facette_opac['code_ss_champ']));
			} else {
				$tmpArray['facette_value'] = json_encode(array($facette_opac['name'],$facette_opac['value'][$j],$facette_opac['code_champ'],$facette_opac['code_ss_champ']));
			}
			$tmpArray['facette_value'] = htmlentities($tmpArray['facette_value'],ENT_QUOTES,$charset);
			$tmpArray['facette_link'] = "./index.php?lvl=more_results&mode=extended".$fields_search;
			$arrayRetour[]=$tmpArray;
		}
		return json_encode($arrayRetour);
	}
		
	public static function do_level1() {
		global $msg,$mode,$autolevel1,$opac_autolevel2,$tab,$charset;
		if (($_SESSION["level1"])&&(!$autolevel1)&&($tab!="affiliate")) {
			$table="<h3>".htmlentities($msg['autolevel1_search'],ENT_QUOTES,$charset)."</h3>\n<table id='lvl1_list'>";
			$n=0;
			foreach($_SESSION["level1"] as $mod_search=>$level) {
				$current=false;
				switch ($mod_search) {
					case "abstract":
						$form_name="search_abstract";
						$lvl_msg=$msg["abstract"];
						if ($mode=="abstract") $current=true;
						break;
					case "author":
						$form_name="search_authors";
						$lvl_msg=$msg["authors"];
						if ($mode=="auteur") $current=true;
						break;
					case "category":
						$form_name="search_categorie";
						$lvl_msg=$msg["categories"];
						if ($mode=="categorie") $current=true;
						break;
					case "collection":
						$form_name="search_collection";
						$lvl_msg=$msg["collections"];
						if ($mode=="collection") $current=true;
						break;
					case "docnum":
						$form_name="search_docnum";
						$lvl_msg=$msg["docnum"];
						if ($mode=="docnum") $current=true;
						break;
					case "indexint":
						$form_name="search_indexint";
						$lvl_msg=$msg["indexint"];
						if ($mode=="indexint") $current=true;
						break;
					case "keywords":
						$form_name="search_keywords";
						$lvl_msg=$msg["keywords"];
						if ($mode=="keyword") $current=true;
						break;
					case "publisher":
						$form_name="search_publishers";
						$lvl_msg=$msg["publishers"];
						if ($mode=="editeur") $current=true;
						break;
					case "subcollection":
						$form_name="search_sub_collection";
						$lvl_msg=$msg["subcollections"];
						if ($mode=="souscollection") $current=true;
						break;
					case "title":
						$form_name="search_objects";
						$lvl_msg=$msg["titles"];
						if (($mode=="titre")||($mode=="title")) $current=true;
						break;
					case "titre_uniforme":
						$form_name="search_titres_uniformes";
						$lvl_msg=$msg["titres_uniformes"];
						if ($mode=="titre_uniforme") $current=true;
						break;
					case "tous":
						$form_name="search_tous";
						$lvl_msg=$msg["tous"];
						if ($mode=="tous") $current=true;
						break;
				}
				if ($n % 2) $pair_impair = "odd"; else $pair_impair = "even";
				$td_javascript=" ";
		        $tr_surbrillance = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
				$table.="<tr class='$pair_impair' $tr_surbrillance>
							<td>".$level["form"].($current?"<span class='current'>":"<a href='javascript:document.forms[\"$form_name\"].submit()'>")."$lvl_msg (".$level["count"].")".($current?"</span>":"</a>")."</td>
						</tr>";
				$n++;
			} 
			$table.="</table>";
		} else {
			if (($opac_autolevel2)&&($autolevel1)&&($tab!="affiliate")) {
				//G�n�ration du post et du get... 
				//Attention tous ce qui passe par ajax.php doit �tre en utf-8
				$table="<script>";
				$to_submit="";
				foreach ($_POST as $key=>$val) {//Attention si on a un tableau de tableau c'est mort
					if (!is_array($val)) {
						$to_submit.=($to_submit?"&":"").rawurlencode(($charset == "utf-8")?$key:utf8_encode($key))."=".rawurlencode(($charset == "utf-8")?$val:utf8_encode($val));
					} else {
						foreach($val as $subkey=>$subval) {
							$to_submit.=($to_submit?"&":"").rawurlencode((($charset == "utf-8")?$key:utf8_encode($key))."[".addslashes(($charset == "utf-8")?$subkey:utf8_encode($subkey))."]")."=".rawurlencode(($charset == "utf-8")?$subval:utf8_encode($subval));
						}
					}
				}
				foreach ($_GET as $key=>$val) {//Attention si on a un tableau de tableau c'est mort
					if (!is_array($val)) {
						$to_submit.=($to_submit?"&":"").rawurlencode(($charset == "utf-8")?$key:utf8_encode($key))."=".rawurlencode(($charset == "utf-8")?$val:utf8_encode($val));
					} else {
						foreach($val as $subkey=>$subval) {
							$to_submit.=($to_submit?"&":"").rawurlencode((($charset == "utf-8")?$key:utf8_encode($key))."[".addslashes(($charset == "utf-8")?$subkey:utf8_encode($subkey))."]")."=".rawurlencode(($charset == "utf-8")?$subval:utf8_encode($subval));
						}
					}
				}
				$table.="var tosubmit=\"".$to_submit."\";" .
						"function updateLevel1(result) {
							document.getElementById('lvl1').innerHTML=result;
						}
						getlevel2=new http_request();
						getlevel2.request('./ajax.php?module=ajax&categ=level1',true,tosubmit,true,updateLevel1);";
				$table.="</script>";
				$table.="<h3>".htmlentities($msg['autolevel1_search'],ENT_QUOTES,$charset)."</h3>\n" .
						"<img src='images/patience.gif' id='wait_level1'/>";
			}
		}
		return $table;
	}
	
	public static function make_facette_search_env() {
		global $search;
		global $op_0_s_1;
		global $field_0_s_1;
		
		//historique des recherches
		$search[] = "s_1";
		$op_0_s_1 = "EQ";
		$field_0_s_1[] = $_SESSION['last_query']+0; 
		
		//creation des globales => parametres de recherche
		if ($_SESSION['facette']) {
			for ($i=0;$i<count($_SESSION['facette']);$i++) {
				$search[] = "s_3";
		    	$field = "field_".($i+1)."_s_3";
		    	$field_=array();
    			$field_ = $_SESSION['facette'][$i];
    			global $$field;
    			$$field = $field_;
    			
		    	$op = "op_".($i+1)."_s_3";
		    	$op_ = "EQ";
    			global $$op;
    			$$op=$op_;
    		    
    			$inter = "inter_".($i+1)."_s_3";
    			$inter_ = "and";
    			global $$inter;
    			$$inter = $inter_;
			}
		}
	}
	
	public static function checked_facette_search($check_facette){
		global $param_delete_facette,$charset;
		
		if (!is_array($_SESSION['facette'])){
			$_SESSION['facette'] = array();
		}

		//Suppression facette
		if($param_delete_facette!=""){
			//On �vite le rafraichissement de la page
			if(isset($_SESSION['facette'][$param_delete_facette])){
				unset($_SESSION['facette'][$param_delete_facette]);
				$_SESSION['facette'] = array_values($_SESSION['facette']);
			}
		} else {
			$tmpArray = array();

			foreach ($check_facette as $k=>$v) {
				$check_facette[$k]=json_decode($v);
				//json_encode/decode ne fonctionne qu'avec des donn�es utf-8				
				if ($charset!='utf-8') {
					foreach($check_facette[$k] as $key=>$value){
						$check_facette[$k][$key]=utf8_decode($check_facette[$k][$key]);
					}
				}
				foreach($check_facette[$k] as $key=>$value){
					$check_facette[$k][$key]=stripslashes($check_facette[$k][$key]);
				}
			}
		
			foreach ($check_facette as $k=>$v) {				
				$ajout=true;
				if (count($tmpArray)) {
					foreach ($tmpArray as $k2=>$v2) {
						if (($v2[2]==$v[2]) && ($v2[3]==$v[3])) {
							$tmpArray[$k2][1][] = $v[1];
							$ajout=false;
							break;
						}
					}
				}
				if ($ajout) {
					$tmpItem = array();
					$tmpItem[0] = $v[0];
					$tmpItem[1] = array($v[1]);
					$tmpItem[2] = $v[2];
					$tmpItem[3] = $v[3];
					$tmpArray[] = $tmpItem;
				}
			}
			//ajout facette : on v�rifie qu'elle n'est pas d�j� en session (rafraichissement page)
			if (count($_SESSION['facette'])) {
				foreach ($_SESSION['facette'] as $k=>$v) {
					if ($tmpArray == $v) {
						$trouve = true;
						break;
					}
				}
			}
			if (!$trouve) {
				$_SESSION['facette'][] = $tmpArray;
			}
		}

		facettes::make_facette_search_env();
	} 
	
	public static function make_facette($id_notice_array){
		global $es;
		$face = new facettes();
		$face->facette_existing();
		$face->nb_results_by_facette($id_notice_array);
		return $face->create_table_facettes();
	}

	public static function make_ajax_facette($id_notice_array){
		global $es;
		$face = new facettes();
		$face->facette_existing();
		$face->nb_results_by_facette($id_notice_array);
		return $face->create_ajax_table_facettes();
	}

	public static function get_facette_wrapper(){
		return "
		<script> 		
			function test(elmt){
				var idElmt=elmt.rowIndex;
				var tab = document.getElementById('facette_list');
				var tr_tab = tab.getElementsByTagName('th');
				alert(tr_tab[idElmt].rowIndex);
				if(idElmt > 0) idElmt = idElmt/2;
				idElmt = idElmt.toString();
				var list = document.getElementById(idElmt);
				if(list.style.display == 'none'){
					list.style.display = 'block';
				}else list.style.display = 'none';

			}
			
			function facette_see_more(id,json_facette_plus){
				var req = new http_request();
				var sended_datas={'json_facette_plus':json_facette_plus};
				req.request(\"./ajax.php?module=ajax&categ=facette&sub=see_more\",true,'sended_datas='+JSON.stringify(sended_datas),true,function(data){
					
					var jsonArray = JSON.parse(data);
					var myTable = document.getElementById('facette_list_'+id);
					//on supprime la ligne '+'
					myTable.deleteRow(-1);
					//on ajoute les lignes au tableau
					for(var i=0;i<jsonArray.length;i++) {
						var newRow = myTable.insertRow(-1);
						var newCell = newRow.insertCell(0);
						newCell.innerHTML = \"<span class='facette_coche'><input type='checkbox' name='check_facette[]' value='\" + jsonArray[i]['facette_value'] + \"'></span>\";
						newCell = newRow.insertCell(1);
						newCell.innerHTML = \"<a class='facette_link' href='\" + jsonArray[i]['facette_link'] + \"'>\"
										+ \"<span class='facette_libelle'>\" + jsonArray[i]['facette_libelle'] + \"</span> \"
										+ \"<span class='facette_number'>[\" + jsonArray[i]['facette_number'] + \"]</span>\"
										+ \"</a>\";
					}
				});
			}
		
			function valid_facettes_multi(){
				//on bloque si aucune case coch�e
				var form = document.facettes_multi;
				for (i=0, n=form.elements.length; i<n; i++){
					if ((form.elements[i].checked == true)) {
						form.submit();
					}
				}
			}
			</script>
		";
			
	}
	
	public function create_table_facettes(){
		$return=self::get_facette_wrapper();
		$return.=$this->create_ajax_table_facettes();
		return $return;		
	}
	
	public function create_ajax_table_facettes(){
		global $charset;
		global $mode;
		global $msg;
		
		$arrayFacettesNotClicked = array();
		$facette_plus = array();
		
		foreach ($this->tab_facettes_opac as $keyFacette=>$vTabFacette) {
			$affiche = true;
			foreach ($vTabFacette['value'] as $keyValue=>$vLibelle) {
				$clicked = false;
				if (count($_SESSION['facette'])) {
					foreach ($_SESSION['facette'] as $vSessionFacette) {
						foreach ($vSessionFacette as $vDetail) {
							if (($vDetail[2]==$vTabFacette['code_champ']) && ($vDetail[3]==$vTabFacette['code_ss_champ']) && (in_array($vLibelle,$vDetail[1]))) {
								$clicked = true;
								break;
							}
						}
					}
				}
				if (!$clicked) {
					$key = $vTabFacette['name']."_".$this->tab_facettes_record[$keyFacette]['id'];			
					if ($vTabFacette['size_to_display']!='0') {
						if (count($arrayFacettesNotClicked[$key])>=$vTabFacette['size_to_display']) {
							$tmpArray = array();
							$tmpArray['see_more'] = true;
							$arrayFacettesNotClicked[$key][]=$tmpArray;
							$affiche = false;
						}
					}
					if ($affiche) {
						$tmpArray = array();
						$tmpArray['libelle'] = $vLibelle;
						$tmpArray['code_champ'] = $vTabFacette['code_champ'];
						$tmpArray['code_ss_champ'] = $vTabFacette['code_ss_champ'];
						$tmpArray['nb_result'] = $vTabFacette['nb_result'][$keyValue];
						$arrayFacettesNotClicked[$key][]=$tmpArray;
					} else {
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['facette'][]=$vLibelle." "."(".$vTabFacette['nb_result'][$keyValue].")";
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['value'][]=$vLibelle;	
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['nb_result'][]=$vTabFacette['nb_result'][$keyValue];
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['code_champ']=$vTabFacette['code_champ'];
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['code_ss_champ']=$vTabFacette['code_ss_champ'];
						$facette_plus[$this->tab_facettes_record[$keyFacette]['id']]['name']=$vTabFacette['name'];
					}
				}
			}
		}

		if (count($_SESSION['facette'])) {
			$table_facette_clicked = "<table id='active_facette'>";
			$tr_surbrillance = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
			$n = 0;
			foreach ($_SESSION['facette'] as $k=>$v) {
				($n % 2)?$pair_impair="odd":$pair_impair="even";
				$n++;
				if (count($_SESSION['facette'])==1) {
					$link = "index.php?lvl=more_results&get_last_query=1&reinit_facette=1";
				} else {
					$link = "index.php?lvl=more_results&mode=extended&facette_test=1&param_delete_facette=".$k;	
				}
				$table_facette_clicked .= "
						<tr class='".$pair_impair."' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\">
							<td>";
				$tmp=0;
				foreach($v as $vDetail){
					foreach($vDetail[1] as $vDetailLib){
						if($tmp){
							$table_facette_clicked .= "<br>";
						}
						$table_facette_clicked .= $vDetail[0]." : ".$vDetailLib;
						$tmp++;
					}
				}
				$table_facette_clicked .= "
							</td>
							<td>
								<a href='".$link."'>
									<img src='./images/cross.png'/>
								</a>
							</td>
						</tr>";
			}
			$table_facette_clicked .= "</table>";
		}

		if (count($arrayFacettesNotClicked)) {
			$table_facette .= "<form name='facettes_multi' method='POST' action='./index.php?lvl=more_results&mode=extended&facette_test=1'>";
			foreach ($arrayFacettesNotClicked as $tmpName=>$facette) {
				$flagSeeMore = false;
				$tmpArray = explode("_",$tmpName);
				$idfacette = array_pop($tmpArray);
				$name = implode($tmpArray);
				$table_facette .= "<table id='facette_list_".$idfacette."'>";
				$table_facette .= "
					<tr>
						<th onclick='javascript:test(this);' colspan='2'>
							".htmlentities($name,ENT_QUOTES,$charset)."
						</th>
					</tr>";
				$j=0;
				foreach ($facette as $detailFacette) {
					$link =  "./index.php?lvl=more_results&mode=extended&facette_test=1";
					$link .= "&name=".rawurlencode($name)."&value=".rawurlencode($detailFacette['libelle'])."&champ=".$detailFacette['code_champ']."&ss_champ=".$detailFacette['code_ss_champ']."";
					if ($charset!='utf-8') {
						$cacValue = json_encode(array(utf8_encode($name),utf8_encode($detailFacette['libelle']),$detailFacette['code_champ'],$detailFacette['code_ss_champ']));
					} else {
						$cacValue = json_encode(array($name,$detailFacette['libelle'],$detailFacette['code_champ'],$detailFacette['code_ss_champ']));
					}					
					if (!isset($detailFacette['see_more'])) {					
						$table_facette .= "
							<tr>
								<td class='facette_col_coche'>
									<span class='facette_coche'>
										<input type='checkbox' name='check_facette[]' value='".htmlentities($cacValue,ENT_QUOTES,$charset)."'>
									</span>
								</td>
								<td  class='facette_col_info'>
									<a href='".$link."'>
										<span class='facette_libelle'>
											".htmlentities($detailFacette['libelle'],ENT_QUOTES,$charset)."
										</span>
										<span class='facette_number'>
											[".htmlentities($detailFacette['nb_result'],ENT_QUOTES,$charset)."]
										</span>
									</a>
								</td>
							</tr>";
						$j++;
					} elseif(!$flagSeeMore) {
						$table_facette .= "
							<tr>
								<td colspan='2'>
									<a href='javascript:facette_see_more(".$idfacette.",".json_encode(pmb_utf8_array_encode($facette_plus[$idfacette])).");'>".$msg["facette_plus_link"]."</a>
								</td>
							</tr>";
						$flagSeeMore = true;
					}
				}
				$table_facette .="</table>";
			}
			$table_facette .= "<input class='bouton' type='button' value='".$msg["facette_filtre"]."' name='filtre' onClick='valid_facettes_multi()'>";
			$table_facette .= "</form>";
		}

		$table = "";
		if(count($_SESSION['facette'])){
			$table .= "<h3>".$msg['facette_active']."</h3>".$table_facette_clicked."<br/>";
		}
		if(count($arrayFacettesNotClicked)){
			$table .= "<h3>".$msg['facette_list']."</h3>".$table_facette."<br/>";
		}
		
		return $table;
	}
	
	public static function make_facette_suggest($id_notice_array){
		global $opac_modules_search_title,$opac_modules_search_author,$opac_modules_search_publisher,$opac_modules_search_titre_uniforme;
		global $opac_modules_search_collection,$opac_modules_search_subcollection,$opac_modules_search_category,$opac_modules_search_indexint;
		global $opac_modules_search_keywords,$opac_modules_search_abstract,$opac_modules_search_docnum;
		global $msg,$user_query,$opac_autolevel2,$base_path;
		
		$suggestion = new suggest($user_query);
		
		if ($opac_autolevel2==2) {
			$action = $base_path."/index.php?lvl=more_results&autolevel1=1";
		} else {
			$action = $base_path."/index.php?lvl=search_result&search_type_asked=simple_search";
		}
		if ($opac_modules_search_title==2) $look["look_TITLE"]=1;
		if ($opac_modules_search_author==2) $look["look_AUTHOR"]=1 ;
		if ($opac_modules_search_publisher==2) $look["look_PUBLISHER"] = 1 ; 
		if ($opac_modules_search_titre_uniforme==2) $look["look_TITRE_UNIFORME"] = 1 ; 
		if ($opac_modules_search_collection==2) $look["look_COLLECTION"] = 1 ;	
		if ($opac_modules_search_subcollection==2) $look["look_SUBCOLLECTION"] = 1 ;
		if ($opac_modules_search_category==2) $look["look_CATEGORY"] = 1 ;
		if ($opac_modules_search_indexint==2) $look["look_INDEXINT"] = 1 ;
		if ($opac_modules_search_keywords==2) $look["look_KEYWORDS"] = 1 ;
		if ($opac_modules_search_abstract==2) $look["look_ABSTRACT"] = 1 ;
		$look["look_ALL"] = 1 ;
		if ($opac_modules_search_docnum==2) $look["look_DOCNUM"] = 1;
		foreach($look as $looktype=>$lookflag) {
			 $action.="&".$looktype."=1"; 
		}
		$table_facette_suggest ="<table><tbody>";
		
		//on recr�e un tableau pour regrouper les �ventuels doublons
		$tmpArray = array();
		$tmpArray = $suggestion->listUniqueSimilars();
		
		if (count($tmpArray)) {
			foreach($tmpArray as $word){
				$table_facette_suggest.="<tr>
					<td>
						<a href='".$action."&user_query=".rawurlencode($word)."'>
							<span class='facette_libelle'>".$word."</span>
						</a>
					</td>
				</tr>";
			}
		}
		$table_facette_suggest.="</tbody></table>";
		
		if (count($tmpArray)) {
			$table = "<h3>".$msg['facette_suggest']."</h3>".$table_facette_suggest."<br/>";
		} else {
			$table = "";
		}
		
		return $table;
	}
	
	public static function expl_voisin($id_notice=0){
		global $charset,$msg;
		$data=array();
		$notices_list = facettes::get_expl_voisin($id_notice);
		$display=facettes::aff_notices_list($notices_list);
		$data['aff']="";
		if($display)$data['aff']= "<h3 class='avis_detail'>".$msg['expl_voisin_search']."</h3>".$display;
		if ($charset!="utf-8") $data['aff']= utf8_encode($data['aff']);
		$data['id']=$id_notice;
		return $data;
	}	
		
	function get_expl_voisin($id_notice=0){
		global $dbh;
		global $opac_nb_notices_similaires;
		
		$id_notice+=0;
		$notice_list=array();	
		$req = "select expl_cote from exemplaires where expl_notice=$id_notice";
		$res = @mysql_query($req,$dbh);
		
		$nb_result = $opac_nb_notices_similaires;
		if($nb_result>6 || $nb_result<0 || !(isset($opac_nb_notices_similaires))){
			$nb_result=6;
		}
		$nb_asc="";
		$nb_desc="";
		if(($nb_result%2)==0){
			$nb_asc = $nb_result/2;
			$nb_desc = $nb_asc;
		} else {
			$nb_desc = $nb_result%2;
			$nb_asc = $nb_result-$nb_desc;
		}		
		
		if($r=mysql_fetch_object($res)){
			$cote=$r->expl_cote;			
			$query = "
			(select distinct expl_notice from exemplaires where expl_notice!=0 and expl_cote >= '".$cote."' and expl_notice!=$id_notice order by expl_cote asc limit ".$nb_asc.")
				union 
			(select distinct expl_notice from exemplaires where expl_notice!=0 and expl_cote < '".$cote."' and expl_notice!=$id_notice  order by expl_cote desc limit ".$nb_desc.")" ;
			$result = mysql_query($query,$dbh);
			if(mysql_num_rows($result) > 0){				
				while($row = mysql_fetch_object($result)){
					$notice_list[] = $row->expl_notice;
				}
			}			
		}	
		return $notice_list;
	}	
			
	
	public static function similitude($id_notice=0){
		global $charset,$msg;
		$data=array();
		$notices_list = facettes::get_similitude_notice($id_notice);
		$display= facettes::aff_notices_list($notices_list);		
		$data['aff']="";
		if($display)$data['aff']= "<h3 class='avis_detail'>".$msg['simili_search']."</h3>".$display;
		if ($charset!="utf-8") $data['aff']= utf8_encode($data['aff']);
		$data['id']=$id_notice;
		return $data;
	}
	
	function get_similitude_notice($id_notice=0){
		global $dbh;
		global $opac_nb_notices_similaires;
		
		$id_notice+=0;
		$req="select distinct code_champ, code_ss_champ, num_word from notices_mots_global_index where	(
				code_champ in(1,17,19,20,25) 
 			)and
			id_notice=$id_notice";
		/*27,28,29
 				or (code_champ=90 and code_ss_champ=2)
				or (code_champ=90 and code_ss_champ=3)
	 			or (code_champ=90 and code_ss_champ=4) 
		 */
		// 7337 43421
		
		$res=mysql_query($req,$dbh);
		$where_mots="";
		while($r=mysql_fetch_object($res)){
			if($where_mots)$where_mots.=" or ";
			$where_mots.="(code_champ =".$r->code_champ." AND code_ss_champ =".$r->code_ss_champ." AND num_word =".$r->num_word." and id_notice != ".$id_notice.")";
		}
		
		$nb_result = $opac_nb_notices_similaires;
		if($nb_result>6 || $nb_result<0 || !(isset($opac_nb_notices_similaires))){
			$nb_result=6;
		}
		$req = "select id_notice, sum(pond) as s from notices_mots_global_index where $where_mots group by id_notice order by s desc limit ".$nb_result;
		
		$res = @mysql_query($req,$dbh);		
		$notice_list=array();
		while($r=mysql_fetch_object($res)){
			if($r->s >80)
				$notice_list[] = $r->id_notice;
		}
		return $notice_list;
	}
	
	function aff_notices_list($notices_list){
		global $dbh,$charset;
		global $opac_show_book_pics,$opac_book_pics_url,$opac_book_pics_msg,$opac_url_base;
		global $opac_notice_affichage_class,$gestion_acces_active,$gestion_acces_empr_notice;
		global $opac_notice_reduit_format_similaire ;
		
		$img_list = "";
		$title_list = "";
		
		$tabNotice = array();
		
		if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {				
			$ac= new acces();
			$dom_2= $ac->setDomain(2);
		}		
		foreach($notices_list as $notice_id){		
			$acces_v=TRUE;	
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {	
				$acces_v = $dom_2->getRights($_SESSION['id_empr_session'],$notice_id,4);
			} else {
				$requete = "SELECT notice_visible_opac, expl_visible_opac, notice_visible_opac_abon, expl_visible_opac_abon, explnum_visible_opac, explnum_visible_opac_abon FROM notices, notice_statut WHERE notice_id ='".$notice_id."' and id_notice_statut=statut ";
				$myQuery = mysql_query($requete, $dbh);
				if(mysql_num_rows($myQuery)) {
					$statut_temp = mysql_fetch_object($myQuery);
					if(!$statut_temp->notice_visible_opac)	$acces_v=FALSE;
					if($statut_temp->notice_visible_opac_abon && !$_SESSION['id_empr_session'])	$acces_v=FALSE;
				} else 	$acces_v=FALSE;
			}
			if(!$acces_v) continue;
			
			$req = "select * from notices where notice_id=$notice_id";			
			$res = @mysql_query($req,$dbh);
			if($r=mysql_fetch_object($res)){
				$image="";
				
				if (substr($opac_notice_reduit_format_similaire,0,1)!="H" && $opac_show_book_pics=='1') {
					$image="<a href='".$opac_url_base."index.php?lvl=notice_display&id=".$notice_id."'>"."<img class='vignetteimg_simili' src='./images/no_image.jpg' hspace='4' vspace='2'></a>";
					
					if ($r->thumbnail_url) {
						$url_image_ok=$r->thumbnail_url;
						$title_image_ok="";
						$image = "<a href='".$opac_url_base."index.php?lvl=notice_display&id=".$notice_id."'>"."<img class='vignetteimg_simili' src='".$url_image_ok."' title=\"".$title_image_ok."\" hspace='4' vspace='2'>"."</a>";
					} elseif($r->code && $opac_book_pics_url){
						$code_chiffre = pmb_preg_replace('/-|\.| /', '', $r->code);
						$url_image = $opac_url_base."getimage.php?url_image=".urlencode($opac_book_pics_url)."&noticecode=!!noticecode!!";
						$url_image_ok = str_replace("!!noticecode!!", $code_chiffre, $url_image);
						$title_image_ok = htmlentities($opac_book_pics_msg, ENT_QUOTES, $charset);
						$image = "<a href='".$opac_url_base."index.php?lvl=notice_display&id=".$notice_id."'>"."<img class='vignetteimg_simili' src='".$url_image_ok."' title=\"".$title_image_ok."\" hspace='4' vspace='2'>"."</a>";
					}				
				}		
				$notice = new $opac_notice_affichage_class($notice_id, "", 0,0,1);	
				$notice->do_header_similaire();				
				$notice_header= "<a href='".$opac_url_base."index.php?lvl=notice_display&id=".$notice_id."'>".$notice->notice_header."</a>";		
				$i++;
			}			
			
			// affichage du titre et de l'image dans la m�me cellule
			if($image!=""){
				$img_list.="<td align='center'>".$image."<br />".$notice_header."</td>";
			} else {
				$img_list.="<td align='center'>".$notice_header."</td>";
			}		
			
		}
		if(!$i)return"";		
		$display="<table width='100%' style='table-layout:fixed;'><tr>".$img_list."</tr></table>";		
		
		return $display;
	}
	
}// end class
