<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facette_search_opac.class.php,v 1.12 2014-01-13 10:26:21 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes de gestion des facettes pour la recherche OPAC

// inclusions principales
require_once("$include_path/templates/facette_search_opac_tpl.php");
require_once("$include_path/user_error.inc.php");

class facette_search {
	var $fields_array=array();
	protected $id;
	protected $name;
	protected $crit;
	protected $ss_crit;
	protected $nb_result;
	protected $visible;
	protected $order_sort;
	protected $type_sort;
	protected $limit_plus;
	
	
	function facette_search(){
		$this->fields_array = $this->fields_array();
	}
	
//recuperation de champs_base.xml
	function fields_array(){
		global $include_path,$msg;
		global $dbh, $champ_base;
		
		if(!count($champ_base)) {
			$file = $include_path."/indexation/notices/champs_base_subst.xml";
			if(!file_exists($file)){
				$file = $include_path."/indexation/notices/champs_base.xml";
			}
			$fp=fopen($file,"r");
	    	if ($fp) {
				$xml=fread($fp,filesize($file));
			}
			fclose($fp);
			$champ_base=_parser_text_no_function_($xml,"INDEXATION");
		}
		return $champ_base;
	}

//creation de la liste des criteres principaux
	function create_list_fields(){
		global $msg,$tpl_form_facette;
		//recuperation du fichier xml de configuration
		$array = $this->array_sort();
		$array2 = $this->array_subfields($this->crit);
		$post_flag = true;
		$post_param = "list_crit";
		$post_param2 = "&sub_field";
		$script ="<script type='text/javascript' src='./javascript/http_request.js'></script>
			<script type='text/javascript'>
			
				function load_subfields(id_ss_champs){
					var lst = document.getElementById('list_crit');
					var id = lst.value;
					var id_subfields = id_ss_champs;
					var xhr_object=  new http_request();					
					xhr_object.request('./ajax.php?module=admin&categ=opac&section=lst_facette',\"$post_flag\",\"$post_param=\"+id+\"$post_param2=\"+id_subfields,'true',cback,0,0)
				}
				
				function cback(response){						
					var div = document.getElementById('liste2');
					div.innerHTML = response;
				}
			</script>";
		
		$tpl_form_facette = str_replace('!!script!!', $script, $tpl_form_facette);
		
		$select ="<select id='list_crit' name='list_crit' onchange='load_subfields(0)'>";		
		foreach ($array as $id => $value) {			
			if($id==$this->crit){
				$select.="<option value=".$id." selected='selected'>".$value."</option>";
			} else {
				$select.="<option value=".$id.">".$value."</option>";
			}
		}
		$select.="</select></br>";
		if($this->crit!=null) $select .= "<script>load_subfields(".$this->ss_crit.")</script>";
		return $select;
	}
	
//liste liee => sous champs
	function create_list_subfields($id,$id_ss_champs=0,$suffixe_id=0){
		global $msg,$charset;
		$array = $this->array_subfields($id);
		$tab_ss_champs = array();
		$select_ss_champs="<label>".$msg["facette_filtre_secondaire"]."</label></br>";
		if($suffixe_id){
			$name_ss_champs="list_ss_champs_".$suffixe_id;
		}else{
			$name_ss_champs="list_ss_champs";
		}
		$select_ss_champs.="<select id='$name_ss_champs' name='$name_ss_champs'>";
		
		if((count($array)>1)){
			foreach($array as $j=>$val2){
				if($id_ss_champs == $j) $select_ss_champs.="<option value=".$j." selected='selected'>".htmlentities($val2,ENT_QUOTES,$charset)."</option>";
				else $select_ss_champs.="<option value=".$j.">".htmlentities($val2,ENT_QUOTES,$charset)."</option>";
			}
			
			$select_ss_champs.="</select></br>";
			return $select_ss_champs;
		}elseif(count($array)==1){
			foreach($array as $j=>$val2){
				$select_ss_champs = "<input type='hidden' name='$name_ss_champs' value='1'/>";
			}
			return $select_ss_champs;
		}
	}
	
//formulaire MaJ ou de creation d'une facette	
	function form_facette(){
		global $tpl_form_facette, $msg,$charset;
		
		$list_champs = $this->create_list_fields();
		
		$tpl_form_facette = str_replace('!!name_del_facette!!',sprintf($msg['label_alert_delete_facette'],htmlentities($this->name,ENT_QUOTES,$charset)),$tpl_form_facette);
		$tpl_form_facette = str_replace('!!nameF!!',sprintf($msg['name_facette'],htmlentities($this->name,ENT_QUOTES,$charset)),$tpl_form_facette);
		if($this->id==null){
			$tpl_form_facette = str_replace('!!libelle!!', htmlentities($msg['lib_nelle_facette_form'],ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!val_submit_form!!', htmlentities($msg['submitSendFacette'],ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!valHidden!!', htmlentities("creation",ENT_QUOTES,$charset), $tpl_form_facette);
			$input_delete_disable = "";
			$tpl_form_facette = str_replace('!!val_submit_suppr!!', $input_delete_disable, $tpl_form_facette);
			$val_nb = 0;
			$val_nb += 0;
			$tpl_form_facette = str_replace('!!val_nb!!', $val_nb, $tpl_form_facette);
			$tpl_form_facette = str_replace('!!defaut_check_order!!',$msg['default_check_facette'],$tpl_form_facette);
			$tpl_form_facette = str_replace('!!defaut_check_order2!!',"",$tpl_form_facette);
			$tpl_form_facette = str_replace('!!defaut_check_type2!!',$msg['default_check_facette'],$tpl_form_facette);
			$tpl_form_facette = str_replace('!!defaut_check_type!!',"",$tpl_form_facette);
			$tpl_form_facette = str_replace('!!limit_plus!!',"0",$tpl_form_facette);
		} else {
			$input_delete = "<input class='bouton' type='button' value='".htmlentities($msg['submitSupprFacette'],ENT_QUOTES,$charset)."' onClick='javascript:confirm_delete()'/>";
			$tpl_form_facette = str_replace('!!val_submit_suppr!!',$input_delete, $tpl_form_facette);
			$tpl_form_facette = str_replace('!!libelle!!', htmlentities($msg['update_facette'],ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!val_submit_form!!', htmlentities($msg['submitMajFacette'],ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!valHidden!!', htmlentities($this->id,ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!val_nb!!', htmlentities($this->nb_result,ENT_QUOTES,$charset), $tpl_form_facette);
			$tpl_form_facette = str_replace('!!limit_plus!!',$this->limit_plus,$tpl_form_facette);
			$tpl_form_facette = str_replace('!!id!!', htmlentities($this->id,ENT_QUOTES,$charset), $tpl_form_facette);
			
			if($this->visible==1)$tpl_form_facette = str_replace('!!defaut_check!!', htmlentities($msg['default_check_facette'],ENT_QUOTES,$charset), $tpl_form_facette);
			if($this->order_sort)$tpl_form_facette = str_replace('!!defaut_check_order2!!', htmlentities($msg['default_check_facette'],ENT_QUOTES,$charset), $tpl_form_facette);
			else $tpl_form_facette = str_replace('!!defaut_check_order!!',htmlentities($msg['default_check_facette'],ENT_QUOTES,$charset),$tpl_form_facette);
			if($this->type_sort)$tpl_form_facette = str_replace('!!defaut_check_type2!!', htmlentities($msg['default_check_facette'],ENT_QUOTES,$charset), $tpl_form_facette);
			else $tpl_form_facette = str_replace('!!defaut_check_type!!',htmlentities($msg['default_check_facette'],ENT_QUOTES,$charset),$tpl_form_facette);
			
		} 
		
		$tpl_form_facette = str_replace('!!liste1!!', $list_champs, $tpl_form_facette);	
		return $tpl_form_facette;
	}

//enregistrement ou MaJ d une facette*
	function save_form_facette(){
		global $label_facette,$list_crit,$list_nb,$list_ss_champs,$visible,$hidden_form,$dbh,$type_sort,$order_sort,$limit_plus;
		$redirect_list = "<script language='javascript'>location.href='./admin.php?categ=opac&sub=facette_search_opac&section=facette'</script>";
		
		if($visible==true) $visible=1;
		else $visible=0;
		
		$requete="select max(facette_order) as ordre from facettes ";
		$resultat=mysql_query($requete);
		$ordre_max=@mysql_result($resultat,0,0);
		$ordre_max++;
		
		$hidden_form+=0;
		$list_ss_champs+=0;
		$listNb+=0;
		$list_crit+=0;
		$limit_plus+=0;
		if((!empty($hidden_form)&&($hidden_form!="creation"))){
			
			$req="UPDATE facettes 
					SET facette_name='".$label_facette."',facette_critere='".$list_crit."',
						facette_ss_critere='".$list_ss_champs."',facette_nb_result='".$list_nb."',
						facette_visible='".$visible."',facette_type_sort='".$type_sort."',facette_order_sort='".$order_sort."',
						facette_limit_plus=$limit_plus
					WHERE id_facette='".$hidden_form."'";
			$rep = mysql_query($req,$dbh) or die(mysql_error()."<br>$req");
		} else {
			$req="INSERT INTO facettes				
				SET facette_name='".$label_facette."',facette_critere='".$list_crit."',
						facette_ss_critere='".$list_ss_champs."',facette_nb_result='".$list_nb."',
						facette_visible='".$visible."',facette_type_sort='".$type_sort."',facette_order_sort='".$order_sort."',
						facette_order=$ordre_max,
						facette_limit_plus=$limit_plus
				";
			$rep = mysql_query($req,$dbh) or die(mysql_error()."<br>$req");
			
		} 
		return $redirect_list;
	}
	
//vue des listes deja creees => page d accueil des facettes
	function view_list_facette(){
		global $tpl_vue_facettes,$msg,$dbh,$charset;
		
		$req = "SELECT * FROM facettes  order by facette_order, facette_name";
		$rq = mysql_query($req,$dbh) or die("Erreur SQL");
		$lst="";
		$array = $this->array_sort();
		$array_subfields = array();
		
		$i = 0;
		
		while($rslt = mysql_fetch_object($rq)){
			
			$intit_crit = htmlentities($array[$rslt->facette_critere],ENT_QUOTES,$charset);
			$array_subfields = $this->array_subfields($rslt->facette_critere);
			$idF = $rslt->id_facette+0;
			
			if(sizeof($array_subfields)>1) $intit_subfields = htmlentities($array_subfields[$rslt->facette_ss_critere],ENT_QUOTES,$charset);
			else $intit_subfields = $msg["admin_opac_facette_ss_critere"];
			$nb_result = $rslt->facette_nb_result+0;
			
			if($nb_result==0)$nb_result = $msg["admin_opac_facette_illimite"];
			$visible = $rslt->facette_visible+0;
			
			if($visible==0)$visible="";
			else $visible="X";
			
			if ($i % 2) $pair_impair = "even"; else $pair_impair = "odd";
			$on_mouse_down="onMouseDown=\"document.location='./admin.php?categ=opac&sub=facette_search_opac&section=facette&action=edit&idF=$idF'\"";
			$td_javascript=" ";
        	$tr_surbrillance = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
			$on_mouse_down="onMouseDown=\"document.location='./admin.php?categ=opac&sub=facette_search_opac&section=facette&action=edit&idF=".$idF."'\"";
			$sort_gestion="";
			if($rslt->facette_type_sort) $sort_gestion .= $msg['intit_gest_tri2'];
			else $sort_gestion .= $msg['intit_gest_tri1'];
			$sort_gestion .= " ".$msg['quotas_and']." ";
			if($rslt->facette_order_sort) $sort_gestion .= $msg['intit_gest_tri4'];
			else $sort_gestion .= $msg['intit_gest_tri3'];
			
			$lst .= "
				<tr >
					<td>
						<input type='button' class='bouton_small' value='-' onClick=\"document.location='./admin.php?categ=opac&sub=facette_search_opac&section=facette&action=up&idF=$idF'\"/></a>
						<input type='button' class='bouton_small' value='+' onClick=\"document.location='./admin.php?categ=opac&sub=facette_search_opac&section=facette&action=down&idF=$idF'\"/>
					</td>
					<td style='cursor:pointer' class=".$pair_impair." ".$tr_surbrillance." ".$on_mouse_down.">".htmlentities($rslt->facette_name,ENT_QUOTES,$charset)."</td>
					<td>".$intit_crit."</td>
					<td>".$intit_subfields."</td>
					<td>".$nb_result."</td>
					<td>".$sort_gestion."</td>
					<td>".$visible."</td>
				</tr>
			";
		
			$tpl_vue_facettes = str_replace('!!id!!',$rslt->id_facette, $tpl_vue_facettes);
			$i++;
		}
		$tpl_vue_facettes = str_replace('!!lst_facette!!', $lst, $tpl_vue_facettes);
		
		return $tpl_vue_facettes;
	}
	
	function facette_up($idF){
		$requete="select facette_order from facettes where id_facette=$idF";
		$resultat=mysql_query($requete);
		$ordre=mysql_result($resultat,0,0);
		$requete="select max(facette_order) as ordre from facettes where facette_order<$ordre";
		$resultat=mysql_query($requete);
		$ordre_max=@mysql_result($resultat,0,0);
		if ($ordre_max) {
			$requete="select id_facette from facettes where facette_order=$ordre_max limit 1";
			$resultat=mysql_query($requete);
			$id_facette_max=mysql_result($resultat,0,0);
			$requete="update facettes set facette_order='".$ordre_max."' where id_facette=$idF";
			mysql_query($requete);
			$requete="update facettes set facette_order='".$ordre."' where id_facette=".$id_facette_max;
			mysql_query($requete);
		}		
	}
	
	function facette_down($idF){
		$requete="select facette_order from facettes where id_facette=$idF";
		$resultat=mysql_query($requete);
		$ordre=mysql_result($resultat,0,0);
		$requete="select min(facette_order) as ordre from facettes where facette_order>$ordre";
		$resultat=mysql_query($requete);
		$ordre_min=@mysql_result($resultat,0,0);
		if ($ordre_min) {
			$requete="select id_facette from facettes where facette_order=$ordre_min limit 1";
			$resultat=mysql_query($requete);
			$id_facette_min=mysql_result($resultat,0,0);
			$requete="update facettes set facette_order='".$ordre_min."' where id_facette=$idF";
			mysql_query($requete);
			$requete="update facettes set facette_order='".$ordre."' where id_facette=".$id_facette_min;
			mysql_query($requete);
		}		
	}
	
	function facette_order_by_name($idF){
		global $dbh;
		$req = "SELECT id_facette  FROM facettes order by facette_name";
		$rq = mysql_query($req,$dbh);	
		$i=1;
		while($res = mysql_fetch_object($rq)){
			$req="UPDATE facettes SET facette_order='".$i++."' where id_facette=".$res->id_facette;
			mysql_query($req,$dbh);
		}
	}
	
//reaffiche le formulaire avec les bons elements
	function edit_facette(){
		global $dbh,$charset;
		global $idF;
		
		$req = "SELECT * FROM facettes WHERE id_facette='".$idF."'";
		$rep = mysql_query($req,$dbh) or die(mysql_error()."<br>$req");
		
		$rslt = mysql_fetch_object($rep);
		
		$this->id = $rslt->id_facette;
		$this->name = $rslt->facette_name;
		$this->crit =$rslt->facette_critere+0;
		$this->ss_crit = $rslt->facette_ss_critere+0;
		$this->nb_result = $rslt->facette_nb_result+0;
		$this->limit_plus = $rslt->facette_limit_plus+0;
		$this->visible = $rslt->facette_visible+0;
		$this->order_sort = $rslt->facette_order_sort+0;
		$this->type_sort = $rslt->facette_type_sort+0;
						
		$form_edit = $this->form_facette();
		
		return $form_edit;
	}
	
	function array_sort(){
		global $msg;
		
		$array_sort = array();
		
		$nb = count($this->fields_array['FIELD']);
		for($i=0;$i<$nb;$i++){
			if($tmp= $msg[$this->fields_array['FIELD'][$i]['NAME']]){
				$lib = $tmp;
			}else{
				$lib = $this->fields_array['FIELD'][$i]['NAME'];
			}
			$id2 = $this->fields_array['FIELD'][$i]['ID'] + 0;
			$array_sort[$id2] = $lib;
			
		}
		asort($array_sort);
		return $array_sort;
		
	}
	
	function array_subfields($id){
		global $msg,$charset;
		$array = $this->fields_array;
		$array_subfields = array();
		$bool_search = 0;
		$i = 0;
		
		if($id!=100){
			while($bool_search==0){
				if($array['FIELD'][$i]['ID']==$id){
					$isbd=$array['FIELD'][$i]['ISBD'];
					$array = $array['FIELD'][$i]['TABLE'][0]['TABLEFIELD'];
					$bool_search = 1;
				} 
				$i++;
			}
			$size = count($array);
			for($i=0;$i<$size;$i++){
				if ($array[$i]['NAME']) $array_subfields[$array[$i]['ID']+0] = $msg[$array[$i]['NAME']];
			}
			if($isbd){
				$array_subfields[$isbd[0]['ID']+0]=$msg['facette_isbd'];
			}
		}else{
			$req= mysql_query("select idchamp,titre from notices_custom order by titre asc");
			$j=0;
			while($rslt=mysql_fetch_object($req)){
				$array_subfields[$rslt->idchamp+0] = $rslt->titre;
				$j++;
			}
		}	
		return $array_subfields;
	}
	
	function delete_facette(){
		global $id,$dbh;
		
		$redirect_list = "<script language='javascript'>location.href='./admin.php?categ=opac&sub=facette_search_opac&section=facette'</script>";
		$req = "DELETE FROM facettes WHERE id_facette='".$id."'";
		$rep = mysql_query($req,$dbh) or die(mysql_error()."<br>$req");
		
		print $redirect_list; 	
	}
	
}

