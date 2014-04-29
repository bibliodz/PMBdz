<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: export.class.php,v 1.30 2013-11-18 15:58:06 mbertin Exp $

//Export d'une notice PMB en XML PMB MARC

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// le fichier sp�cifique d'import contient la fonction d'export sp�cifique des exemplaires
//if (!$pmb_import_modele)  $pmb_import_modele= "func_bdp.inc.php" ;
//require_once ("$base_path/admin/import/$pmb_import_modele") ;
require_once($class_path."/parametres_perso.class.php");
require_once("$class_path/XMLlist.class.php");

class export {

	var $notice;
	var $xml_array = array();
	var $notice_list = array();
	var $current_notice = 0;
	var $notice_exporte=array();
	//Enregistre les bulletins d�ja export�
	var $bulletins_exporte=array();
	//Pour savoir si il y des bulletins � exporter
	var $expl_bulletin_a_exporter=array();
	
	//Initialisation avec une liste de numeros de notices (si liste vide alors on prend toute la base)
	function export($l_idnotices, $noti_exporte=array(),$bull_exporte=array()) {
		$this->notice_exporte = $noti_exporte;
		$this->bulletins_exporte = $bull_exporte;
		if (is_array($l_idnotices)) {
			$this -> notice_list = $l_idnotices;
		} else {
			if ($l_idnotices != "") {
				$this -> notice_list[] = $l_idnotices;
			} else {
				$requete = "select distinct notice_id from notices";
				$resultat = mysql_query($requete);
				while (($res = mysql_fetch_object($resultat))) {
					$this -> notice_list[] = $res -> notice_id;
				}
			}
		}
	}
	
	//Conversion au format XML du tableau de donnees
	function toxml() {
		global $charset;
		$this -> notice = "<notice>\n";
		//Record descriptor
		$desc=array("rs","dt","bl","hl","el","ru");
		for ($i=0; $i<count($desc); $i++) {
			$this->notice.="  <".$desc[$i].">";
			if ($this->xml_array[$desc[$i]]["value"]=="") $this->xml_array[$desc[$i]]["value"]="*";
			$this->notice.=$this->xml_array[$desc[$i]]["value"]."</".$desc[$i].">\n";
		}
		for ($i = 0; $i < count($this -> xml_array["f"]); $i ++) {
			$this -> notice.= "  <f";
			foreach ( $this -> xml_array["f"][$i] as $key => $value ) { //Pour chaque attribut
				if((!is_array($value)) && ($key!="ind") && ($key!="value")){ // Si c'est un attr et pas l'indicateur "ind"
       				$this -> notice.= " ".$key."=\"".htmlspecialchars($value,ENT_QUOTES,$charset)."\""; //On construit le champ f avec nom de l'attribut = sa valeur
				}
			}
			if ($this -> xml_array["f"][$i]["value"] == "") {
				$this -> notice.= " ind=\"".$this -> xml_array["f"][$i]["ind"]."\"";
			}
			$this -> notice.= ">";
			if ($this -> xml_array["f"][$i]["value"] == "") {
				$this->notice.="\n";
				for ($j = 0; $j < count($this -> xml_array["f"][$i]["s"]); $j ++) {
					$this -> notice.= "    <s c=\"".$this -> xml_array["f"][$i]["s"][$j]["c"]."\">".htmlspecialchars($this -> xml_array["f"][$i]["s"][$j]["value"],ENT_QUOTES,$charset)."</s>\n";
				}
				$this->notice.="  ";
			} else {
				$this -> notice.=htmlspecialchars($this -> xml_array["f"][$i]["value"],ENT_QUOTES,$charset);
			}

			$this -> notice.= "</f>\n";
		}
		$this -> notice.= "</notice>\n";
	}
	
	function tojson() {
		$this->notice = json_encode($this->xml_array);
	}
	
	function toserialized() {
		$this->notice = serialize($this->xml_array);
	}
	
	function to_raw_array() {
		$this->notice = $this->xml_array;
	}
	
	//Ajout d'un champ dans le tableau de donnees
	function add_field($field_code, $field_ind, $sub_fields, $value = "",$attrs= "") {
		$f_ = array();
		$f_["c"] = $field_code;
		if ($field_ind)
			$f_["ind"] = $field_ind;
		if($attrs != "" && is_array($attrs)){ //Si on a un tableau d'attribut
			foreach ( $attrs as $key1 => $value1 ) {//Pour chaque couple (nom,valeur) de chaque attribut
	       		$f_[$key1] = $value1;
			}
		}
		if ($value == "" && is_array($sub_fields)) {
			$flag_s = 0;
			while (list ($key, $val) = each($sub_fields)) {
				if(is_array($val)){
				  foreach($val as $valeur){
				  	$s = array();
					$s["c"] = $key;
					$s["value"] = $valeur;
					$f_["s"][] = $s;
					$flag_s = 1; 
				  }	
				} elseif ($val) {
					$s = array();
					$s["c"] = $key;
					$s["value"] = $val;
					$f_["s"][] = $s;
					$flag_s = 1;
				}
			}
		} else {
			$f_["value"] = $value;
			$flag_s = 1;
		}
		if ($flag_s)
			$this -> xml_array["f"][] = $f_;
	}

	//Generation XML de la prochaine notice (renvoi true si prochaine notice, false si plus de notices disponibles)
	function get_next_notice($lender = "", $td = array(), $sd = array(), $keep_expl = false, $params=array(), $force_diffusion=false) {
		global $include_path, $lang;
		global $opac_show_book_pics;
		global $dbh,$charset;
		
		unset($this->xml_array);
		$this -> xml_array = array();
		$this -> notice = "";
		
		if(($force_diffusion == false) && ($this->current_notice!=-1)){
			// Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72		
			$sql_exclu="select orinot_diffusion from origine_notice,notices where notice_id = '".$this -> notice_list[$this -> current_notice]."' and origine_catalogage = orinot_id";
			$result_exclu=mysql_query($sql_exclu);
			$diffusable=mysql_result($result_exclu,0,0);
		}else{
			$diffusable=true;
		}
		if (($this->current_notice!=-1) && ( (array_search($this->notice_list[$this -> current_notice],$this->notice_exporte)!==false) || !$diffusable)) {
			$this -> current_notice++;
			if ($this -> current_notice >= count($this -> notice_list))
				$this -> current_notice = -1;
			return true;
		}
		
		if ($this -> current_notice != -1) {
			//Recuperation des infos de la notice
			$requete = "select * from notices where notice_id=".$this -> notice_list[$this -> current_notice];
			$resultat = mysql_query($requete);
			$res = mysql_fetch_object($resultat);
			
			//Remplissage des champs immediats

			//Numero unique de la base
			$this -> add_field("001", "", "", $res -> notice_id);
			
			//Champ de traitement
			if($charset == "utf-8"){
				$encodage="50  ";
			}else{
				$encodage="0103";
			}
			$c100=substr($res -> create_date, 0, 4).substr($res -> create_date, 5, 2).substr($res -> create_date, 8, 2)."u        u  u0frey".$encodage."    ba";
			$this-> add_field("100","  ",array("a"=>$c100),"");
			
			//Titre
			$titre[c] = "200";
			$titre[ind] = "1 ";
			$labels = array("a", "c", "d", "e");
			$subfields = array();
			for ($i = 1; $i < 5; $i ++) {
				$v="";
				eval("\$v=\$res->tit$i;");
				$subfields[$labels[$i -1]] = $v;
			}
			
			if($res->niveau_biblio == 'b' && $res->niveau_hierar == '2'){
				$req_bulletin = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero from bulletins WHERE num_notice=".$res->notice_id;
				$result_bull = mysql_query($req_bulletin);
				while(($bull=mysql_fetch_object($result_bull))){
					$subfields["h"] = $bull -> bulletin_numero;
					$subfields["i"] = $bull -> bulletin_titre;
					$subfields["9"] = "id:".$bull->bulletin_id ;
				}
			}
			$this -> add_field("200", "1 ", $subfields);
			
			//Titres Uniformes
			$rqt_tu = "select * from notices_titres_uniformes,titres_uniformes where tu_id =ntu_num_tu and ntu_num_notice = '".$this->notice_list[$this->current_notice]."' order by ntu_ordre";
			$result_tu = mysql_query($rqt_tu);
			if(mysql_num_rows($result_tu)){		
				while($row_tu = mysql_fetch_object($result_tu)){
					$subfields = array();
					$subfields["9"] = "id:".$row_tu->tu_id;
					$subfields["a"] = $row_tu->tu_name;
					$subfields["u"] = $row_tu->tu_tonalite;
					$subfields["n"] = $row_tu->tu_comment;
					$subfields["i"] = $row_tu->ntu_titr;
					$subfields["k"] = $row_tu->ntu_date;
					$subfields["l"] = $row_tu->ntu_sous_vedette;
					$subfields["m"] = $row_tu->ntu_langue;
					$subfields["q"] = $row_tu->ntu_version;
					$subfields["w"] = $row_tu->ntu_mention;
					$rqt_tucomp="SELECT * FROM tu_distrib WHERE distrib_num_tu='".$row_tu->tu_id."' ORDER BY distrib_ordre,distrib_name";
					$result_tucomp = mysql_query($rqt_tucomp);
					if(mysql_num_rows($result_tucomp)){		
						while($row_tucomp = mysql_fetch_object($result_tucomp)){
							$subfields["r"][] = $row_tucomp->distrib_name;
						}
					}
					$rqt_tucomp="SELECT * FROM tu_ref WHERE ref_num_tu='".$row_tu->tu_id."' ORDER BY ref_ordre,ref_name";
					$result_tucomp = mysql_query($rqt_tucomp);
					if(mysql_num_rows($result_tucomp)){		
						while($row_tucomp = mysql_fetch_object($result_tucomp)){
							$subfields["s"][] = $row_tucomp->ref_name;
						}
					}
					$rqt_tucomp="SELECT * FROM tu_subdiv WHERE subdiv_num_tu='".$row_tu->tu_id."' ORDER BY subdiv_ordre,subdiv_name";
					$result_tucomp = mysql_query($rqt_tucomp);
					if(mysql_num_rows($result_tucomp)){		
						while($row_tucomp = mysql_fetch_object($result_tucomp)){
							$subfields["j"][] = $row_tucomp->subdiv_name;
						}
					}
					
					$this->add_field("500", "10", $subfields);
				}
			}
			
			//Titre du p�rio pour les notices de bulletin
			$subfields=array();
			if($res->niveau_biblio == 'b' && $res->niveau_hierar == '2'){				
				$req_bulletin = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1 as titre from bulletins, notices WHERE bulletin_notice=notice_id AND num_notice=".$res->notice_id;
				$result_bull = mysql_query($req_bulletin);
				while(($bull=mysql_fetch_object($result_bull))){
					$subfields["a"] = $bull->titre;
				}				
			}
			$this -> add_field("530", "  ", $subfields);
			
			//Date en 210 pour les notices de bulletin
			$subfields=array();
			if($res->niveau_biblio == 'b' && $res->niveau_hierar == '2'){				
				$req_bulletin = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero from bulletins WHERE num_notice=".$res->notice_id;
				$result_bull = mysql_query($req_bulletin);
				while(($bull=mysql_fetch_object($result_bull))){
					$subfields["h"] = $bull->date_date;
					$subfields["d"] = $bull->mention_date;
				}				
			}
			$this -> add_field("210", "  ", $subfields);

			//isbn
			$subfields = array();
			$subfields["a"] = $res -> code;
			$subfields["d"] = $res -> prix;
			$this -> add_field("010", "  ", $subfields);

			// URL
			$subfields = array();
			$subfields["u"] = $res -> lien;
			$subfields["q"] = $res -> eformat;
			$this -> add_field("856", "  ", $subfields);

			//Langage
			$rqttmp_lang = "select type_langue,code_langue from notices_langues where num_notice='$res->notice_id' order by ordre_langue ";
			$restmp_lang = mysql_query($rqttmp_lang);
			$ind="0 ";
			$subfields_101 = array();
			while (($tmp_lang = mysql_fetch_object($restmp_lang))) {
				if($tmp_lang->type_langue){
					$ind="1 ";
					$subfields_101['c'][]=$tmp_lang->code_langue;
				} else {
					$subfields_101['a'][]=$tmp_lang->code_langue;
				}
			}
			$this->add_field('101',$ind,$subfields_101);
			
			//Mention d'edition
			$subfields = array();
			$subfields["a"] = $res -> mention_edition;
			$this -> add_field("205", "  ", $subfields);
			
			//Collation
			$subfields = array();
			$subfields["a"] = $res -> npages;
			$subfields["c"] = $res -> ill;
			$subfields["d"] = $res -> size;
			$subfields["e"] = $res -> accomp;
			
			$this -> add_field("215", "  ", $subfields);
	
			//Notes
			$subfields = array();
			$subfields["a"] = $res -> n_gen;
			$this -> add_field("300", "  ", $subfields);
			$subfields["a"] = $res -> n_contenu;
			$this -> add_field("327", "  ", $subfields);
			$subfields["a"] = $res -> n_resume;
			$this -> add_field("330", "  ", $subfields);
			
			//Auteurs
			
			//Recherche des auteurs;
			$requete = "select author_id, author_type, author_name, author_rejete, author_date, responsability_fonction, responsability_type 
			,author_subdivision, author_lieu,author_ville, author_pays,author_numero,author_web, author_comment
			from authors, responsability where responsability_notice=".$res->notice_id." and responsability_author=author_id order by responsability_ordre asc";
			$resultat = mysql_query($requete);

			while (($auth=mysql_fetch_object($resultat))) {				
				//Si c'est un 70 (individuel) alors on l'exporte
				$subfields = array();
				$attrs = array();
				if ($params["include_authorite_ids"])
					$attrs["id"] = $auth->author_id;
				if ($auth->author_type == "70") {
					// Personne physique
					//Champ = author_type + responsability_type (70 + 0 pour auteur principal = 700 !)
					$auth_code = $auth->author_type.$auth->responsability_type;
					$subfields["a"] = $auth->author_name;
					$subfields["b"] = $auth->author_rejete;
					$subfields["c"] = $auth->author_comment;
					//Fonction
					$subfields["4"] = $auth->responsability_fonction;
					//Dates
					if ($auth->author_date!="") {
						$subfields["f"] = $auth->author_date ;
					}
					$subfields["N"] = $auth->author_web;
					$subfields["9"] = "id:".$auth->author_id;
					$this->add_field($auth_code," 1", $subfields, "", $attrs);
				} elseif (($auth->author_type == "71") || ($auth->author_type == "72")) {
					//Collectivit�
					$auth_code = $auth->author_type.$auth->responsability_type;
					$subfields["a"] = $auth->author_name;
					$subfields["b"] = $auth->author_subdivision;
					$subfields["c"] = $auth->author_comment;
					$subfields["g"] = $auth->author_rejete;
					$subfields["d"] = $auth->author_numero;
					//Fonction
					$subfields["4"] = $auth->responsability_fonction;
					//Dates
					if ($auth->author_date!="") {
						$subfields["f"] = $auth->author_date ;
					}
					$lieu=$auth->author_lieu;
					if($auth->author_ville) {
						if($lieu) $lieu.="; ";
						$lieu.=$auth->author_ville;
					}
					if($auth->author_pays) {
						if($lieu) $lieu.="; ";
						$lieu.=$auth->author_pays;
					}					
					$subfields["e"] = $lieu;
					$subfields["K"] = $auth->author_lieu;
					$subfields["L"] = $auth->author_ville;
					$subfields["M"] = $auth->author_pays;
					$subfields["N"] = $auth->author_web;
					$subfields["9"] = "id:".$auth->author_id;
					if ($auth->author_type == "71") {
						$auth_code = $auth->author_type.$auth->responsability_type;
						$this->add_field($auth_code,"02", $subfields, "", $attrs);
					} elseif ($auth->author_type == "72") {
						$auth_code = "71".$auth->responsability_type;
						$this->add_field($auth_code,"12", $subfields, "", $attrs);
					}
					
				}					
			}
			
			//Editeurs et date de la notice
			$c102_export=false;//Le champ 102 n'est pas r�p�table
			$requete = "select * from publishers where ed_id =".$res -> ed1_id;
			$resultat = mysql_query($requete);
			$subfields = array();
			$attrs = array();
			if ($params["include_authorite_ids"])
				$attrs["id"] = $res->ed1_id;
			if (($ed1 = mysql_fetch_object($resultat))) {
				$subfields["a"] = $ed1 -> ed_ville;
				$subfields["b"] = trim($ed1 -> ed_adr1."\n".$ed1 -> ed_adr2."\n".$ed1 -> ed_cp."\n".$ed1 -> ed_ville."\n".$ed1 -> ed_pays);
				$subfields["c"] = $ed1 -> ed_name;
				$subfields["d"] = $res -> year;
				$subfields["z"] = $ed1 -> ed_pays;
				if(trim($ed1 -> ed_pays)){
					$parser = new XMLlist("$include_path/marc_tables/$lang/country.xml");
					$parser->analyser();
					if($tmp=array_search(trim($ed1->ed_pays),$parser->table)){
						$subfields_102=array();
						$subfields_102["a"]=mb_strtolower($tmp);
						$this -> add_field("102", "  ", $subfields_102);
						$c102_export=true;
					}
				}
				$subfields["9"][] = "id:".$ed1->ed_id;
				if(trim($ed1->ed_web)) $subfields["9"][] = "web:".$ed1->ed_web;
				if(trim($ed1->ed_comment)) $subfields["9"][] = "comment:".$ed1->ed_comment;
			}elseif($res -> year  && $res->niveau_biblio != 'b'){
				$subfields["d"] = $res -> year;
			}
			$this -> add_field("210", "  ", $subfields, "", $attrs);

			$requete = "select * from publishers where ed_id =".$res -> ed2_id;
			$resultat = mysql_query($requete);
			$subfields = array();
			$attrs = array();
			if ($params["include_authorite_ids"])
				$attrs["id"] = $res->ed2_id;
			if (($ed1 = mysql_fetch_object($resultat))) {
				$subfields["a"] = $ed1 -> ed_ville;
				$subfields["b"] = trim($ed1 -> ed_adr1."\n".$ed1 -> ed_adr2."\n".$ed1 -> ed_cp."\n".$ed1 -> ed_ville."\n".$ed1 -> ed_pays);
				$subfields["c"] = $ed1 -> ed_name;
				$subfields["d"] = $res -> year;
				$subfields["z"] = $ed1 -> ed_pays;
				if(trim($ed1 -> ed_pays) && !$c102_export){
					$parser = new XMLlist("$include_path/marc_tables/$lang/country.xml");
					$parser->analyser();
					if($tmp=array_search(trim($ed1->ed_pays),$parser->table)){
						$subfields_102=array();
						$subfields_102["a"]=mb_strtolower($tmp);
						$this -> add_field("102", "  ", $subfields_102);
					}
				}	
				$subfields["9"][] = "id:".$ed1->ed_id;
				if(trim($ed1->ed_web)) $subfields["9"][] = "web:".$ed1->ed_web;
				if(trim($ed1->ed_comment)) $subfields["9"][] = "comment:".$ed1->ed_comment;
			}
			$this->add_field("210", "  ", $subfields, "", $attrs);
			
			//Collections
			$requete = "select * from collections where collection_id=".$res -> coll_id;
			$resultat = mysql_query($requete);
			$subfields = array();
			$subfields_410 = array();
			$subfields_411 = array();
			$subfields_s = array();
			$attrs = array();
			if ($params["include_authorite_ids"])
				$attrs["id"] = $res->coll_id;
			if (($col = mysql_fetch_object($resultat))) {
				$subfields["a"] = $col -> collection_name;
				$subfields_410["t"] = $col -> collection_name;
				$subfields["v"] = $res -> nocoll;
				$subfields_410["v"] = $res -> nocoll;
				$subfields["x"] = $col -> collection_issn;
				$subfields_410["x"] = $col -> collection_issn;
				$subfields["9"] ="id:".$res -> coll_id;
				$subfields_410["9"] ="id:".$res -> coll_id;
			}			
			//Recherche des sous collections
			$requete = "select * from sub_collections where sub_coll_id=".$res -> subcoll_id;
			$resultat = mysql_query($requete);
			if (($subcol = mysql_fetch_object($resultat))) {
				$subfields_s["i"] = $subcol -> sub_coll_name;
				$subfields_411["t"] = $subcol -> sub_coll_name;
				$subfields_s["x"] = $subcol -> sub_coll_issn;
				$subfields_411["x"] = $subcol -> sub_coll_issn;
				$subfields_s["9"] = "id:".$res -> subcoll_id;
				$subfields_411["9"] ="id:".$res -> subcoll_id;
			}
			$attrs2 = array();
			if ($params["include_authorite_ids"])
				$attrs2["id"] = $res -> subcoll_id;
			$this -> add_field("225", "2 ", $subfields, "", $attrs);
			$this -> add_field("410", " 0", $subfields_410, "", $attrs);
			$this -> add_field("225", "2 ", $subfields_s, "", $attrs2);
			$this -> add_field("411", " 0", $subfields_411, "", $attrs2);

			$requete = "select * from series where serie_id=".$res -> tparent_id;
			$resultat = mysql_query($requete);
			$subfields = array();
			$attrs = array();
			if (($serie = mysql_fetch_object($resultat))) {
				$subfields["t"] = $serie -> serie_name;
				$subfields["v"] = $res -> tnvol;
				if ($params["include_authorite_ids"]){
					$attrs["id"] = $serie -> serie_id;
				}
			}
			$this -> add_field("461", " 0", $subfields, '', $attrs);

			//dewey
			$subfields = array();
			//Recher du code dewey
			$requete = "select * from indexint where indexint_id=".$res -> indexint;
			$resultat = mysql_query($requete);
			if (($code_dewey=mysql_fetch_object($resultat))) {
				$subfields["a"] = $code_dewey -> indexint_name;
				$subfields["l"] = $code_dewey -> indexint_comment;
				$subfields["9"] = "id:".$code_dewey -> indexint_id;
				$this -> add_field("676", "  ", $subfields);
			}

			//Vignette
			if ($opac_show_book_pics) {
				$vignette=get_vignette($this -> notice_list[$this -> current_notice]);
				if ($vignette) {
					$this->add_field("896","  ",array("a"=>$vignette));
				}
			}
			
			if ($keep_expl) {
				if($res->niveau_biblio == 'b' && $res->niveau_hierar == '2'){//Si c'est une notice de bulletin
					$requete="SELECT bulletin_id FROM bulletins WHERE num_notice='".$res -> notice_id."'";
					$res_bull=mysql_query($requete);
					if(mysql_num_rows($res_bull)){
						$id_bull=mysql_result($res_bull,0,0);
						if((array_search($id_bull,$this->bulletins_exporte)===false) && (array_search($id_bull,$this->expl_bulletin_a_exporter)===false)){
					    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
					    	$this->expl_bulletin_a_exporter[]=$id_bull;
					    }
					}
				}else{//Si non
					//Traitement des exemplaires
					$this->processing_expl($lender,$td,$sd,$params,$res->notice_id,0);
				}
			}

			//Mots cles
			$subfields = array();
			$subfields["a"] = $res -> index_l;
			$this -> add_field("610", "0 ", $subfields);

			//Descripteurs
			$requete="SELECT libelle_categorie,categories.num_noeud,categories.langue,categories.num_thesaurus FROM categories, notices_categories WHERE notcateg_notice=".$res->notice_id." and categories.num_noeud = notices_categories.num_noeud ORDER BY ordre_categorie";
            $resultat=mysql_query($requete);
            if (mysql_num_rows($resultat)) {
                  for ($i=0; $i<mysql_num_rows($resultat); $i++) {
                      $subfields=array();
                      $subfields["9"][]="id:".mysql_result($resultat,$i,1);
                      $subfields["9"][]="lang:".mysql_result($resultat,$i,2);
                      $subfields["9"][]="idthes:".mysql_result($resultat,$i,3);
                      $subfields["a"]=mysql_result($resultat,$i,0);
                      $this -> add_field("606"," 1",$subfields);
                }
            }
			
			//Champs perso de notice traite par la table notice_custom
			$this->processing_cp("notices",$res->notice_id);

			//Notices li�es, relations entre notices
			if($params["exp_generer_liens"]){
				//On choisit d'exporter les notices m�res
				if($params["exp_export_mere"]){
					$requete="SELECT num_notice, linked_notice, relation_type, rank from notices_relations where num_notice=".$res->notice_id." order by num_notice, rank asc";
					$resultat=mysql_query($requete);
					while(($notice_fille=mysql_fetch_object($resultat))) {						
						$requete_mere="SELECT * FROM notices WHERE notice_id=".$notice_fille->linked_notice;
						$resultat_mere=mysql_query($requete_mere);
						while(($notice_mere=mysql_fetch_object($resultat_mere))) {
							// Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72
							$sql_exclu="select orinot_diffusion from origine_notice,notices where notice_id = '".$notice_mere->notice_id."' and origine_catalogage = orinot_id ";
							$result_exclu=mysql_query($sql_exclu);
							$diffusable=mysql_result($result_exclu,0,0);
							
							$subfields = array();	
							$list_titre = array();
							$list_auteurs = array();
							$list_options = array();
							//On recopie les informations de la notice fille
							if($params["exp_export_notice_mere_link"] && $diffusable) $subfields["0"] = $notice_mere->notice_id;
							$list_titre[] = ($notice_mere->tit1) ? $notice_mere->tit1 : " ";
							//auteur
							$rqt_aut = "select author_name, author_rejete from responsability join authors on author_id = responsability_author and responsability_notice=".$notice_mere->notice_id." where responsability_type != 2 order by responsability_type,responsability_ordre";
							$res_aut=mysql_query($rqt_aut);
							$mere_aut = array();
							while(($mere_aut=mysql_fetch_object($res_aut))) {
								$list_auteurs[] = $mere_aut->author_name.($mere_aut->author_rejete ? ", ".$mere_aut->author_rejete : "");
							}
							$list_options[] = "bl:".$notice_mere->niveau_biblio.$notice_mere->niveau_hierar;
							$list_options[] = "id:".$notice_mere->notice_id;
							if($notice_fille->rank) $list_options[] = "rank:".$notice_fille->rank;
							if($notice_fille->relation_type) $list_options[] = "type_lnk:".$notice_fille->relation_type;
							$list_options[] = 'lnk:parent';
							$subfields["9"] = $list_options;
							//Relation avec mono = ISBN
							if($notice_mere->niveau_biblio == 'm' && $notice_mere->niveau_hierar == '0'){
								if($notice_mere->code) $subfields["y"] = $notice_mere->code;
								$subfields["t"] = $list_titre;
								$subfields["a"] = $list_auteurs;
							}
							//Relation avec p�rio = ISSN
							if($notice_mere->niveau_biblio == 's' && $notice_mere->niveau_hierar == '1'){
								if($notice_mere->code) $subfields["x"] = $notice_mere->code;
								$subfields["t"] = $list_titre;
							}
							//Relation avec articles 
							if($notice_mere->niveau_biblio == 'a' && $notice_mere->niveau_hierar == '2'){
								$req_art = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1, code from analysis join bulletins on bulletin_id=analysis_bulletin join notices on bulletin_notice=notice_id where analysis_notice=".$notice_mere->notice_id;
								$result_art=mysql_query($req_art);
								while(($notice_art=mysql_fetch_object($result_art))){
									$subfields["d"] = $notice_art->date_date;
									$subfields["e"] = $notice_art->mention_date;
									$subfields["v"] = $notice_art->bulletin_numero;
									if($notice_art->code) $subfields["x"] = $notice_art->code;
								    $list_titre[] = ($notice_art->bulletin_titre) ? $notice_art->bulletin_titre : " ";
								    $list_titre[] = ($notice_art->tit1) ? $notice_art->tit1 : " ";
								    $subfields["t"] = $list_titre;
								    if($keep_expl && (array_search($notice_art->bulletin_id,$this->bulletins_exporte)===false) && (array_search($notice_art->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
								    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
								    	$this->expl_bulletin_a_exporter[]=$notice_art->bulletin_id;
								    }
								}
							}
							//Relation avec bulletins
							if($notice_mere->niveau_biblio == 'b' && $notice_mere->niveau_hierar == '2'){
								$req_bull = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1, code from bulletins join notices on bulletin_notice=notice_id  WHERE num_notice=".$notice_mere->notice_id;
								$result_bull=mysql_query($req_bull);
								while(($notice_bull=mysql_fetch_object($result_bull))){
									$subfields["d"] = $notice_bull->date_date;
									$subfields["e"] = $notice_bull->mention_date;
									$subfields["v"] = $notice_bull->bulletin_numero;
									if($notice_bull->code) $subfields["x"] = $notice_bull->code;
									$list_titre[] = ($notice_bull->bulletin_titre) ? $notice_bull->bulletin_titre : " ";
									$list_titre[] = ($notice_bull->tit1) ? $notice_bull->tit1 : " ";
									$subfields["t"] = $list_titre;
									if($keep_expl && (array_search($notice_bull->bulletin_id,$this->bulletins_exporte)===false) && (array_search($notice_bull->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
								    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
								    	$this->expl_bulletin_a_exporter[]=$notice_bull->bulletin_id;
								    }
								}
							}							
							$list_attribut = new XMLlist("$include_path/marc_tables/$lang/relationtypeup_unimarc.xml");
							$list_attribut->analyser();
							$table_attribut = $list_attribut->table;
							//On teste si la relation est sp�ciale, de type contient dans une boite
							if($notice_fille->relation_type=='d')
								$indicateur="d0";
							else $indicateur="  ";
							$this->add_field($table_attribut[$notice_fille->relation_type],$indicateur,$subfields);
							
							//On exporte les notices m�res li�es
							if($params["exp_export_notice_mere_link"] && (array_search($notice_mere->notice_id,$this->notice_exporte)===false) && $diffusable){
								$this->notice_list[]=$notice_mere->notice_id;
							}
						}						
					}
				}
				//On choisit d'exporter les notices filles
				if($params["exp_export_fille"]){
					$requete="SELECT num_notice, linked_notice, relation_type, rank from notices_relations where linked_notice=".$res->notice_id." order by num_notice, rank asc";
					$resultat=mysql_query($requete);
					while(($notice_mere=mysql_fetch_object($resultat))) {						
						$requete_fille="SELECT * FROM notices WHERE notice_id=".$notice_mere->num_notice;
						$resultat_fille=mysql_query($requete_fille);
						while(($notice_fille=mysql_fetch_object($resultat_fille))) {
							// Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72
							$sql_exclu="select orinot_diffusion from origine_notice,notices where notice_id = '".$notice_fille->notice_id."' and origine_catalogage = orinot_id ";
							$result_exclu=mysql_query($sql_exclu);
							$diffusable=mysql_result($result_exclu,0,0);
							$subfields = array();
							$list_titre = array();
							$list_options = array();
							//On recopie les informations de la notice fille
							if($params["exp_export_notice_fille_link"] && $diffusable) $subfields["0"] = $notice_fille->notice_id;
							$list_titre[] = ($notice_fille->tit1) ? $notice_fille->tit1 : " ";
							$list_options[] = "bl:".$notice_fille->niveau_biblio.$notice_fille->niveau_hierar;
							$list_options[] = "id:".$notice_fille->notice_id;
							if($notice_mere->rank) $list_options[] = "rank:".$notice_mere->rank;
							if($notice_mere->relation_type) $list_options[] = "type_lnk:".$notice_mere->relation_type;
							$list_options[] = 'lnk:child';
							$subfields["9"] = $list_options;
							//Relation avec mono = ISBN
							if($notice_fille->niveau_biblio == 'm' && $notice_fille->niveau_hierar == '0'){
								if($notice_fille->code) $subfields["y"] = $notice_fille->code;
								$subfields["t"] = $list_titre;
							}	
							//Relation avec p�rio = ISSN
							if($notice_fille->niveau_biblio == 's' && $notice_fille->niveau_hierar == '1'){
								if($notice_fille->code) $subfields["x"] = $notice_fille->code;
								$subfields["t"] = $list_titre;
							}
							//Relation avec articles 
							if($notice_fille->niveau_biblio == 'a' && $notice_fille->niveau_hierar == '2'){
								$req_art = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1, code from analysis join bulletins on bulletin_id=analysis_bulletin join notices on bulletin_notice=notice_id where analysis_notice=".$notice_fille->notice_id;
								$result_art=mysql_query($req_art);
								while(($notice_art=mysql_fetch_object($result_art))){
									$subfields["d"] = $notice_art->date_date;
									$subfields["e"] = $notice_art->mention_date;
									$subfields["v"] = $notice_art->bulletin_numero;
									if($notice_art->code) $subfields["x"] = $notice_art->code;
								    $list_titre[] = ($notice_art->bulletin_titre) ? $notice_art->bulletin_titre : " ";
								    $list_titre[] = ($notice_art->tit1) ? $notice_art->tit1 : " ";
								    $subfields["t"] = $list_titre;
								    if($keep_expl && (array_search($notice_art->bulletin_id,$this->bulletins_exporte)===false) && (array_search($notice_art->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
								    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
								    	$this->expl_bulletin_a_exporter[]=$notice_art->bulletin_id;
								    }
								}
							}
							//Relation avec bulletins
							if($notice_fille->niveau_biblio == 'b' && $notice_fille->niveau_hierar == '2'){
								$req_bull = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1, code from bulletins join notices on bulletin_notice=notice_id  WHERE num_notice=".$notice_fille->notice_id;
								$result_bull=mysql_query($req_bull);
								while(($notice_bull=mysql_fetch_object($result_bull))){
									$subfields["d"] = $notice_bull->date_date;
									$subfields["e"] = $notice_bull->mention_date;
									$subfields["v"] = $notice_bull->bulletin_numero;
									if($notice_bull->code) $subfields["x"] = $notice_bull->code;
									$list_titre[] = ($notice_bull->bulletin_titre) ? $notice_bull->bulletin_titre : " ";
									$list_titre[] = ($notice_bull->tit1) ? $notice_bull->tit1 : " ";
									$subfields["t"] = $list_titre;
									if($keep_expl && (array_search($notice_bull->bulletin_id,$this->bulletins_exporte)===false) && (array_search($notice_bull->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
								    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
								    	$this->expl_bulletin_a_exporter[]=$notice_bull->bulletin_id;
								    }
								}
							}
							$list_attribut = new XMLlist("$include_path/marc_tables/$lang/relationtypedown_unimarc.xml");
							$list_attribut->analyser();
							$table_attribut = $list_attribut->table;
							//On teste si la relation est sp�ciale, de type contient dans une boite
							if($notice_fille->relation_type=='d')
								$indicateur="d0";
							else $indicateur="  ";
							$this->add_field($table_attribut[$notice_mere->relation_type],$indicateur,$subfields);
							
							//On exporte les notices filles li�es
							if($params["exp_export_notice_fille_link"] && (array_search($notice_fille->notice_id,$this->notice_exporte)===false) && $diffusable){
								$this->notice_list[]=$notice_fille->notice_id;
							}
						}						
					}
				}
				
				//On choisit d'exporter les liens vers les p�riodiques pour les notices d'article
				if($params["exp_export_perio_link"]){
					$req_perio_link = "SELECT notice_id, tit1, code from bulletins,analysis,notices WHERE bulletin_notice=notice_id and bulletin_id=analysis_bulletin and analysis_notice=".$res->notice_id;
					$result_perio_link=mysql_query($req_perio_link);
					while(($notice_perio_link=mysql_fetch_object($result_perio_link))){
						// Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72
						$sql_exclu="select orinot_diffusion from origine_notice,notices where notice_id = '".$notice_perio_link->notice_id."' and origine_catalogage = orinot_id ";
						$result_exclu=mysql_query($sql_exclu);
						$diffusable=mysql_result($result_exclu,0,0);
						$subfields_461=array();
						$list_options=array();
						if($params["exp_export_notice_perio_link"] && $diffusable) $subfields_461["0"] = $notice_perio_link->notice_id;
						$subfields_461["t"] = ($notice_perio_link->tit1) ? $notice_perio_link->tit1 : " ";
						if($notice_perio_link->code) $subfields_461["x"] = $notice_perio_link->code;
						$list_options[] = "id:".$notice_perio_link->notice_id;
						$list_options[] = 'lnk:perio';
						$subfields_461["9"] = $list_options;
						$this->add_field("461","  ",$subfields_461);
						//On exporte les notices de p�rio li�es
						if($params["exp_export_notice_perio_link"] && (array_search($notice_perio_link->notice_id,$this->notice_exporte)===false) && $diffusable){
							$this->notice_list[]=$notice_perio_link->notice_id;			
						}
					}
				}
				
				//On g�n�re le bulletinage pour les notices de p�rio
				if($params["exp_export_bulletinage"]){					
					$req_bulletinage = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero from bulletins, notices WHERE bulletin_notice = notice_id AND notice_id=".$res->notice_id;
					$result_bulletinage=mysql_query($req_bulletinage);					
					while(($notice_bulletinage=mysql_fetch_object($result_bulletinage))){
						$subfields_462=array();
						$list_options=array();
						$subfields_462["d"] = $notice_bulletinage->date_date;
						$subfields_462["e"] = $notice_bulletinage->mention_date;
						$subfields_462["v"] = $notice_bulletinage->bulletin_numero;
						$subfields_462["t"] = ($notice_bulletinage->bulletin_titre) ? $notice_bulletinage->bulletin_titre : " ";
						$list_options[] = "id:".$notice_bulletinage->bulletin_id;
						$list_options[] = 'lnk:bull';
						$subfields_462["9"] = $list_options;
						$this->add_field("462","  ",$subfields_462);
						if($keep_expl && (array_search($notice_bulletinage->bulletin_id,$this->bulletins_exporte)===false) && (array_search($notice_bulletinage->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
					    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
					    	$this->expl_bulletin_a_exporter[]=$notice_bulletinage->bulletin_id;
					    }
					}					
				}
				
				//On choisit d'exporter les liens vers les bulletins pour les notices d'article
				if($params["exp_export_bull_link"]){
					$req_bull_link = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero from bulletins, analysis WHERE bulletin_id=analysis_bulletin and analysis_notice=".$res->notice_id;
					$result_bull_link=mysql_query($req_bull_link);						
					while(($notice_bull_link=mysql_fetch_object($result_bull_link))){
						$subfields_463 = array();
						$list_options = array();
						$subfields_463["d"] = $notice_bull_link->date_date;
						$subfields_463["e"] = $notice_bull_link->mention_date;
						$subfields_463["v"] = $notice_bull_link->bulletin_numero;
						$subfields_463["t"] = ($notice_bull_link->bulletin_titre) ? $notice_bull_link->bulletin_titre : " ";
						$list_options[] = "id:".$notice_bull_link->bulletin_id;
						$list_options[] = 'lnk:bull';
						$subfields_463["9"] = $list_options;
						$this->add_field("463","  ",$subfields_463);
						if($keep_expl && (array_search($notice_bull_link->bulletin_id,$this->bulletins_exporte)===false)  && (array_search($notice_bull_link->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
					    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
					    	$this->expl_bulletin_a_exporter[]=$notice_bull_link->bulletin_id;
					    }
					}					
				 }
				
				//On choisit d'exporter les liens vers les articles pour les notices de p�rio
				if($params["exp_export_art_link"]){
					$req_art_link = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, analysis_notice, a.tit1 as titre, a.npages as page from notices p left join bulletins on bulletin_notice=p.notice_id left join analysis on analysis_bulletin=bulletin_id join notices a on a.notice_id=analysis_notice WHERE p.notice_id=".$res->notice_id;
					$result_art_link=mysql_query($req_art_link);					
					while(($notice_art_link=mysql_fetch_object($result_art_link))){
						// Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72
						$sql_exclu="select orinot_diffusion from origine_notice,notices where notice_id = '".$notice_art_link->analysis_notice."' and origine_catalogage = orinot_id ";
						$result_exclu=mysql_query($sql_exclu);
						$diffusable=mysql_result($result_exclu,0,0);
						$subfields_464=array();
						$tab_titre=array();
						$list_options=array();
						$tab_titre[]= ($notice_art_link->titre) ? $notice_art_link->titre : " ";
						$tab_titre[]= ($notice_art_link->bulletin_titre) ? $notice_art_link->bulletin_titre : " ";
						if($params["exp_export_notice_art"] && $diffusable) $subfields_464["0"] = $notice_art_link->analysis_notice;
						$subfields_464["t"] = $tab_titre;
						$subfields_464["d"] = $notice_art_link->date_date;
						$subfields_464["e"] = $notice_art_link->mention_date;
						$subfields_464["v"] = $notice_art_link->bulletin_numero;
						$list_options[] = "id:".$notice_art_link->analysis_notice;
						$list_options[] = "page:".$notice_art_link->page;
						$list_options[] = 'lnk:art';
						$subfields_464["9"] = $list_options;
						$this->add_field("464","  ",$subfields_464);
						if($keep_expl && (array_search($notice_art_link->bulletin_id,$this->bulletins_exporte)===false)  && (array_search($notice_art_link->bulletin_id,$this->expl_bulletin_a_exporter)===false)){
					    	//Si on exporte les exemplaires on garde l'ID du bulletin pour exporter ses exemplaires
					    	$this->expl_bulletin_a_exporter[]=$notice_art_link->bulletin_id;
					    }
						//On exporte les notices d'articles li�es
						if($params["exp_export_notice_art_link"] && (array_search($notice_art_link->analysis_notice,$this->notice_exporte)===false)  && $diffusable){
							$this->notice_list[]=$notice_art_link->analysis_notice;			
						}					
					}			
					
				}
			}
		
			//Etat de collection
			if($res->niveau_biblio == 's' && $res->niveau_hierar == '1'){
				global $opac_view_filter_class;
				if($opac_view_filter_class){
					$req="SELECT collections_state.*, archempla_libelle, archtype_libelle, archstatut_opac_libelle, archstatut_gestion_libelle, location_libelle FROM collections_state " .
						"LEFT JOIN arch_emplacement ON archempla_id=collstate_emplacement " .
						"LEFT JOIN arch_type ON archtype_id=collstate_type " .
						"LEFT JOIN arch_statut ON archstatut_id=collstate_statut " .
						"LEFT JOIN docs_location ON idlocation=location_id " .
						"WHERE id_serial='".$res->notice_id."'" .
						" AND idlocation in(". implode(",",$opac_view_filter_class->params["nav_collections"]).")" .
						" AND ((archstatut_visible_opac=1 and archstatut_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (archstatut_visible_opac_abon=1 and archstatut_visible_opac=1)" : "").")";
				} else {
					$req="SELECT collections_state.*, archempla_libelle, archtype_libelle, archstatut_opac_libelle, archstatut_gestion_libelle, location_libelle FROM collections_state " .
						"LEFT JOIN arch_emplacement ON archempla_id=collstate_emplacement " .
						"LEFT JOIN arch_type ON archtype_id=collstate_type " .
						"LEFT JOIN arch_statut ON archstatut_id=collstate_statut " .
						"LEFT JOIN docs_location ON idlocation=location_id " .
						"WHERE id_serial='".$res->notice_id."'" .
						" AND ((archstatut_visible_opac=1 and archstatut_visible_opac_abon=0)".( $_SESSION["user_code"]? " or (archstatut_visible_opac_abon=1 and archstatut_visible_opac=1)" : "").")";
				}
				$res_etat=mysql_query($req);
				if($res_etat && mysql_num_rows($res_etat)){
					while ($etat = mysql_fetch_object($res_etat)) {
						$subfields = array();
						$attrs = array();
						$subfields["9"]="id:".$etat->collstate_id;
						$subfields["a"]=$etat->location_libelle;
						$subfields["b"]=$etat->state_collections;
						$subfields["c"]=$etat->archempla_libelle;
						$subfields["d"]=$etat->archtype_libelle;
						$subfields["e"]=$etat->collstate_origine;
						$subfields["f"]=$etat->collstate_cote;
						$subfields["g"]=$etat->collstate_archive;
						$subfields["h"]=$etat->archstatut_opac_libelle;
						$subfields["i"]=$etat->collstate_lacune;
						$subfields["j"]=$etat->collstate_note;
						$subfields["k"]=$etat->archstatut_gestion_libelle;
						$this -> add_field("950", "  ", $subfields, "", $attrs);
						//Export des cp d'etat de collection
						$this->processing_cp("collstate",$etat->collstate_id,$etat->collstate_id);
					}
				}
			}
			
			//Record field
			$biblio = $res->niveau_biblio ;
			$hierar = $res->niveau_hierar ;
			if(($biblio=='b')&&($hierar=='2')){
				//si on a un bulletin on modifie b2 en s2
				$biblio='s';
				$hierar='2';
			}
			$this->xml_array['rs']['value']="n";
			$this->xml_array['dt']['value']=$res->typdoc;
			$this->xml_array['bl']['value']=$biblio;
			$this->xml_array['hl']['value']=$hierar;
			$this->xml_array['el']['value']=1;
			$this->xml_array['ru']['value']="i";
			if (array_search($res->notice_id,$this->notice_exporte)===false) {
				$this->notice_exporte[]=$res->notice_id;
			}
			$this -> toxml();
			$this -> current_notice++;
			if ($this -> current_notice >= count($this -> notice_list))
				$this -> current_notice = -1;
			return true;
		} else {
			return false;
		}
	}
	
	function get_next_bulletin($lender = "", $td = array(), $sd = array(), $keep_expl = false, $params=array()){
		global $is_expl_caddie,$charset;
		
		unset($this->xml_array);
		$this -> xml_array = array();
		$this -> notice = "";

		//On regarde si on a encore des exemplaires a exporter
		//echo "Je passe ici : ".count($this->expl_bulletin_a_exporter)."<br>";
		if(count($this->expl_bulletin_a_exporter)){
			//Si mon tableau n'est pas vide
			$id_bulletin=array_shift($this->expl_bulletin_a_exporter);
			$this->bulletins_exporte[]=$id_bulletin;
		}else{
			return false;
		}
		
		//On regarde si on a des exemplaires pour ce bulletin
		$requete="select expl_id from exemplaires where expl_bulletin='".$id_bulletin."'";
		$res=mysql_query($requete);
		
		if(mysql_num_rows($res)){
			//Si le bulletin a des exemplaires on cr�er une notice d'article bidon pour cr�er les exemplaires

			if (!$is_expl_caddie)  {
				$requete_panier="select count(*) from expl_cart_id";
				$res_panier=@mysql_query($requete_panier);
				if ($res_panier) $is_expl_caddie=2; else $is_expl_caddie=1;
			}
			
			//Numero unique de la base
			$this -> add_field("001", "", "", $id_bulletin."-bull");
			
			//Champ de traitement
			if($charset == "utf-8"){
				$c100=date("Ymd")."u        u  u0frey50      ba";
			}else{
				$c100=date("Ymd")."u        u  u0frey0103    ba";
			}
			$this-> add_field("100","  ",array("a"=>$c100),"");
			
			
			$this->xml_array['rs']['value']="n";
			$this->xml_array['dt']['value']="a";
			$this->xml_array['bl']['value']="a";
			$this->xml_array['hl']['value']="2";
			$this->xml_array['el']['value']="1";
			$this->xml_array['ru']['value']="i";
			
			
			//Lien vers le bulletin et le perio pour recreer l'exemplaire
			$req_art = "SELECT bulletin_id, bulletin_numero, date_date, mention_date, bulletin_titre, bulletin_numero, tit1, code, notice_id from bulletins join notices on bulletin_notice=notice_id where bulletin_id=".$id_bulletin;
			$result_art=mysql_query($req_art);
			while(($notice_art=mysql_fetch_object($result_art))){
				//Pour le cas ou l'article est r�cup�r� en temps que monographie
				$subfields=array();
				$subfields["a"] = $notice_art->bulletin_titre ? $notice_art->bulletin_titre.", ".$notice_art->mention_date : $notice_art->mention_date;
				if(!$subfields["a"]) $subfields["a"] = "Notice de bulletin";
				$subfields["d"] = "Article_expl_bulletin";
				$subfields["h"] = $notice_art->tit1;
				$subfields["i"] = $notice_art->bulletin_numero;
				$this -> add_field("200", "1 ", $subfields);
				
				$subfields=array();
				$date=explode("-",$notice_art->date_date);
				$subfields["d"] = $date[0];
				$this -> add_field("210", "  ", $subfields);
				
				$subfields_463 = array();
				$list_options = array();
				$list_titre = array();
				if($params["exp_export_notice_perio_link"]) $subfields_463["0"] = $notice_art->notice_id;
				$subfields_463["d"] = $notice_art->date_date;
				$subfields_463["e"] = $notice_art->mention_date;
				$subfields_463["v"] = $notice_art->bulletin_numero;
				if($notice_art->code) $subfields_463["x"] = $notice_art->code;
			    $list_titre[] = ($notice_art->bulletin_titre) ? $notice_art->bulletin_titre : " ";
			    $list_titre[] = ($notice_art->tit1) ? $notice_art->tit1 : " ";
			    $subfields_463["t"] = $list_titre;
				$list_options[] = "id:".$notice_art->bulletin_id;
				$list_options[] = 'lnk:bull_expl';
				$subfields_463["9"] = $list_options;
				$this->add_field("463","  ",$subfields_463);
			}	
			
			//Traitement des exemplaires
			$this -> processing_expl($lender,$td,$sd,$params,0,$id_bulletin);				
			$this -> toxml();
			
		}
		
		if(!count($this->expl_bulletin_a_exporter)){
			//On vient de traiter le dernier
			return false;
		}else{
			return true;
		}
	}
	
	
	function processing_expl($lender,$td,$sd,$params,$expl_notice,$expl_bulletin){
		global $is_expl_caddie;
		//Traitement des exemplaires
		$requete = "select expl_id, create_date, expl_cb,expl_cote,expl_statut,statut_libelle, statusdoc_codage_import, expl_typdoc, tdoc_libelle, tdoc_codage_import, expl_note, expl_comment, expl_section, section_libelle, sdoc_codage_import, expl_owner, lender_libelle, codestat_libelle, statisdoc_codage_import, expl_date_retour, expl_date_depot, expl_note, pret_flag, location_libelle, locdoc_codage_import from exemplaires, docs_statut, docs_type, docs_section, docs_codestat, lenders, docs_location where ".($expl_bulletin != 0 ?"expl_bulletin=".$expl_bulletin." AND expl_notice=0":"expl_notice=".$expl_notice." AND expl_bulletin=0")." and expl_statut=idstatut and expl_typdoc=idtyp_doc and expl_section=idsection and expl_owner=idlender and expl_codestat=idcode and expl_location=idlocation";
		/*if (($lender != "x")&&($lender!=""))
			$requete.= " and expl_owner=".$lender;
		if (count($td) != 0)
			$requete.= " and expl_typdoc in (".implode(",", $td).")";
		if (count($sd) != 0)
			$requete.= " and expl_statut in (".implode(",", $sd).")";*/
		$resultat = mysql_query($requete);
		
		while (($ex = mysql_fetch_object($resultat))) {
			$subfields = array();
			global $export996 ;
			$export996 = array() ;
			if(function_exists(export_traite_exemplaires)) $subfields = export_traite_exemplaires ($ex);
			$this -> add_field("995", "  ", $subfields);
			//J'ajoute dans le sous champs 996 tous ce qu'il faut � l'exemlaire pour le reconstruire
			foreach($ex as $key => $value) {
				if((trim($value) !== "" ) && ( $value !=  "0000-00-00")){
					$export996["9"][]=$key.":".$value;
				}
		     }
			if (count($export996) != 0) $this -> add_field("996", "  ", $export996);
			//Export des cp d'exemplaires
			$this->processing_cp("expl",$ex->expl_id,$ex->expl_cb);
		}
	}
	
	function processing_cp($type,$id,$val_f=""){
		$mes_pp= new parametres_perso($type);
		$mes_pp->get_values($id);
		$values = $mes_pp->values;
		foreach ( $values as $field_id => $vals ) {
			//si on peut on exporte les infos du r�solveur (DOI / PMID)
			if(($type == "notices") && ($mes_pp->t_fields[$field_id]['TYPE'] == "resolve")){
				//les ids sont fix� en dur, on c'est trait� : DOI = 2 , PMID = 1
				foreach ( $vals as $value ) {
					$id_infos = explode('|',$value);
				 	switch($id_infos[1]){
				 		case "1" :
				 			$resolver = "PMID";
				 			break;
				 		case "2" :
				 			$resolver = "DOI";
				 			break;
				 		default :
				 			$resolver = "";
				 	}
				 	if($resolver != ""){
				 		$subfields = array();
				 		$subfields["a"]= $id_infos[0];
					 	$subfields["b"]=$resolver; 
					 	$this->add_field("014","  ",$subfields);
				 	}
				} 						
			}
			if($mes_pp->t_fields[$field_id]["EXPORT"]) { //champ exportable
				foreach ( $vals as $value ) {
					$subfields = array();
					switch ($mes_pp->t_fields[$field_id]['TYPE'] && $mes_pp->t_fields[$field_id]["OPAC_SHOW"]) {
						case "resolve":
						case "url":
							$subfields["c"]=$value;
							$id_infos = explode('|',$value);
							$link=$mes_pp->get_formatted_output(array($value),$field_id);//Valeur
							if((count($id_infos) == 2)){
								$subfields["a"]=$id_infos[0];
								$subfields["b"]=$link;
							}else{
								$subfields["a"]=$link;
							}
							break;
						case "query_auth":
						case "query_list":
							$subfields["c"]=$value;
							//break; C'est voulu
						default:
							$subfields["a"]=$mes_pp->get_formatted_output(array($value),$field_id);//Val
							break;
					}

				 	$subfields["l"]=$mes_pp->t_fields[$field_id]["TITRE"];//Libelle du champ
				 	$subfields["n"]=$mes_pp->t_fields[$field_id]["NAME"];//Nom du champ
				 	$subfields["t"]=$mes_pp->t_fields[$field_id]["TYPE"];//Type du champ
				 	if($val_f){
				 		$subfields["f"]=$val_f;
				 	}
				 	switch ( $type ) {
						case "notices":
							$this->add_field("900","  ",$subfields);
							break;
						case "expl":
							$this->add_field("999","  ",$subfields);
							break;
						case "collstate":
							$this->add_field("951","  ",$subfields);
							break;
						default:
							break;
					}
				 	
				} 
			}
		}
	}
}
?>
