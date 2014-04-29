<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titre_uniforme.class.php,v 1.25 2014-03-05 10:49:13 mhoestlandt Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/notice.class.php");
require_once("$class_path/aut_link.class.php");
require_once("$class_path/aut_pperso.class.php");
require_once("$class_path/audit.class.php");
require_once("$class_path/author.class.php");

class titre_uniforme {
	
	// ---------------------------------------------------------------
	//		propriétés de la classe
	// ---------------------------------------------------------------	
	var $id;					// MySQL id in table 'titres_uniformes'
	var $name;					// titre_uniforme name
	var $tonalite;				// tonalite de l'oeuvre musicale
	var $comment ;  			// Commentaire, peut contenir du HTML
	var $import_denied=0;		// booléen pour interdire les modification depuis un import d'autorités
	var $form;					// catégorie à laquelle appartient l'oeuvre (roman, pièce de théatre, poeme, ...)
	var $date; 					// date de création originelle de l'oeuvre (telle que saisie)
	var $date_date;				// date formatée yyyy-mm-dd
	var $characteristic; 		// caractéristique permettant de distinguer une oeuvre d'une autre peuvre portant le même titre
	var $intended_termination;	// complétude d'une oeuvre est finie ou se poursuit indéfiniment
	var $intended_audience;		// categorie de personnes à laquelle l'oeuvre s'adresse
	var $context;				// contexte historique, social, intellectuel, artistique ou autre au sein duquel l'oeuvre a été conçue
	var $coordinates;			// coordonnees d'une oeuvre géographique (degrés, minutes et secondes de longitude et latitude ou angles de déclinaison et d'ascension des limiets de la zone représentée)
	var $equinox;				// année de référence pour une carte ou un modèle céleste
	var $subject;				// contenu de l'oeuvre et sujets qu'elle aborde
	var $place;					// pays ou juridiction territoriale dont l'oeuvre est originaire
	var $history;				// informations concernant l'histoire de l'oeuvre
	var $num_author;			// identifiant de l'auteur principal de l'oeuvre
	var $display;				// usable form for displaying ( _name_ (_date_) / _author_name_ _author_rejete_ )
	var $tu_isbd;				// affichage isbd du titre uniforme AFNOR Z 44-061 (1986)
	
	// ---------------------------------------------------------------
	//		titre_uniforme($id) : constructeur
	// ---------------------------------------------------------------
	function titre_uniforme($id=0,$recursif=0) {
		if($id) {
			// on cherche à atteindre une notice existante
			$this->recursif=$recursif;
			$this->id = $id;
			$this->getData();
		} else {
			// la notice n'existe pas
			$this->id = 0;
			$this->getData();
		}
	}
	
	// ---------------------------------------------------------------
	//		getData() : récupération infos titre_uniforme
	// ---------------------------------------------------------------
	function getData() {
		global $dbh,$msg;

		$this->name = '';			
		$this->tonalite = '';
		$this->comment ='';
		$this->distrib=array();
		$this->ref=array();
		$this->subdiv=array();
		$this->libelle="";
		$this->import_denied=0;
		$this->form = '';
		$this->date ='';
		$this->date_date ='';
		$this->characteristic = '';
		$this->intended_termination = '';
		$this->intended_audience = '';
		$this->context = '';
		$this->coordinates = '';
		$this->equinox = '';
		$this->subject = '';
		$this->place = '';
		$this->history = '';
		$this->num_author = '';
		$this->display = '';
		if($this->id) {
			$requete = "SELECT * FROM titres_uniformes WHERE tu_id=$this->id LIMIT 1 ";
			$result = @mysql_query($requete, $dbh);
			if(mysql_num_rows($result)) {
				$temp = mysql_fetch_object($result);				
				$this->id	= $temp->tu_id;
				$this->name	= $temp->tu_name;
				$this->tonalite	= $temp->tu_tonalite;
				$this->comment	= $temp->tu_comment	;
				$this->import_denied = $temp->tu_import_denied;
				$this->form = $temp->tu_forme;
				$this->date  = $temp->tu_date;
				$this->date_date  = $temp->tu_date_date;
				$this->characteristic = $temp->tu_caracteristique;
				$this->intended_termination = $temp->tu_completude;
				$this->intended_audience = $temp->tu_public;
				$this->context = $temp->tu_contexte;
				$this->coordinates = $temp->tu_coordonnees;
				$this->equinox = $temp->tu_equinoxe;
				$this->subject = $temp->tu_sujet;
				$this->place = $temp->tu_lieu;
				$this->history = $temp->tu_histoire;
				$this->num_author = $temp->tu_num_author;
				
				$libelle[]=$this->name;
				
				if($this->tonalite)$libelle[]=$this->tonalite;
				$requete = "SELECT * FROM tu_distrib WHERE distrib_num_tu='$this->id' order by distrib_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->distrib[]["label"]=$param->distrib_name;
						$libelle[]=$param->distrib_name;
					}	
				}					
				$requete = "SELECT *  FROM tu_ref WHERE ref_num_tu='$this->id' order by ref_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->ref[]["label"]=$param->ref_name;
						$libelle[]=$param->ref_name;
					}	
				}			
				$requete = "SELECT *  FROM tu_subdiv WHERE subdiv_num_tu='$this->id' order by subdiv_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->subdiv[]["label"]=$param->subdiv_name;
						$libelle[]=$param->subdiv_name;
					}	
				}	
				
				$this->display = $this->name;
				if($this->date){
					$this->display.=" (".$this->date.")";
				}
				if($this->num_author){
					$tu_auteur = new auteur($this->num_author);
					$libelle[] = $tu_auteur->display;
					$this->display.=" / ".$tu_auteur->rejete." ".$tu_auteur->name;
				}
				
				$this->libelle=implode("; ",$libelle);
				
			} else {
				// pas trouvé avec cette clé
				$this->id = 0;				
			}
		}
	}
	function gen_input_selection($label,$form_name,$item,$values,$what_sel,$class='saisie-80em' ) {  
	
		global $msg;
		$select_prop = "scrollbars=yes, toolbar=no, dependent=yes, resizable=yes";
		$link="'./select.php?what=$what_sel&caller=$form_name&p1=f_".$item."_code!!num!!&p2=f_".$item."!!num!!&deb_rech='+".pmb_escape()."(this.form.f_".$item."!!num!!.value), '$what_sel', 400, 400, -2, -2, '$select_prop'";
		$size_item=strlen($item)+2;
		$script_js="
		<script>
		function fonction_selecteur_".$item."() {
			var nom='f_".$item."';
	        name=this.getAttribute('id').substring(4);  
			name_id = name.substr(0,nom.length)+'_code'+name.substr(nom.length);
			openPopUp('./select.php?what=$what_sel&caller=$form_name&p1='+name_id+'&p2='+name, '$what_sel', 400, 400, -2, -2, '$select_prop');
	        
	    }
	    function fonction_raz_".$item."() {
	        name=this.getAttribute('id').substring(4);
			name_id = name.substr(0,$size_item)+'_code'+name.substr($size_item);
	        document.getElementById(name).value='';
			document.getElementById(name_id).value='';
	    }
	    function add_".$item."() {
	        template = document.getElementById('add".$item."');
	        ".$item."=document.createElement('div');
	        ".$item.".className='row';
	
	        suffixe = eval('document.".$form_name.".max_".$item.".value')
	        nom_id = 'f_".$item."'+suffixe
	        f_".$item." = document.createElement('input');
	        f_".$item.".setAttribute('name',nom_id);
	        f_".$item.".setAttribute('id',nom_id);
	        f_".$item.".setAttribute('type','text');
	        f_".$item.".className='$class';
	        f_".$item.".setAttribute('value','');
			f_".$item.".setAttribute('completion','".$item."');
	        
			id = 'f_".$item."_code'+suffixe
			f_".$item."_code = document.createElement('input');
			f_".$item."_code.setAttribute('name',id);
	        f_".$item."_code.setAttribute('id',id);
	        f_".$item."_code.setAttribute('type','hidden');
			f_".$item."_code.setAttribute('value','');
	 
	        del_f_".$item." = document.createElement('input');
	        del_f_".$item.".setAttribute('id','del_f_".$item."'+suffixe);
	        del_f_".$item.".onclick=fonction_raz_".$item.";
	        del_f_".$item.".setAttribute('type','button');
	        del_f_".$item.".className='bouton';
	        del_f_".$item.".setAttribute('readonly','');
	        del_f_".$item.".setAttribute('value','".$msg["raz"]."');
	
	        sel_f_".$item." = document.createElement('input');
	        sel_f_".$item.".setAttribute('id','sel_f_".$item."'+suffixe);
	        sel_f_".$item.".setAttribute('type','button');
	        sel_f_".$item.".className='bouton';
	        sel_f_".$item.".setAttribute('readonly','');
	        sel_f_".$item.".setAttribute('value','".$msg["parcourir"]."');
	        sel_f_".$item.".onclick=fonction_selecteur_".$item.";
	
	        ".$item.".appendChild(f_".$item.");
			".$item.".appendChild(f_".$item."_code);
	        space=document.createTextNode(' ');
	        ".$item.".appendChild(space);
	        ".$item.".appendChild(del_f_".$item.");
	        ".$item.".appendChild(space.cloneNode(false));
	        if('$what_sel')".$item.".appendChild(sel_f_".$item.");
	        
	        template.appendChild(".$item.");
	
	        document.".$form_name.".max_".$item.".value=suffixe*1+1*1 ;
	        ajax_pack_element(f_".$item.");
	    }
		</script>";
		
		//template de zone de texte pour chaque valeur				
		$aff="
		<div class='row'>
		<input type='text' class='$class' id='f_".$item."!!num!!' name='f_".$item."!!num!!' value=\"!!label_element!!\" autfield='f_".$item."_code!!num!!' completion=\"".$item."\" />
		<input type='hidden' id='f_".$item."_code!!num!!' name='f_".$item."_code!!num!!' value='!!id_element!!'>
		<input type='button' class='bouton' value='".$msg["raz"]."' onclick=\"this.form.f_".$item."!!num!!.value='';this.form.f_".$item."_code!!num!!.value=''; \" />
		!!bouton_parcourir!!";
		// 1 seul auteur pour 1 oeuvre
		if($item=="author"){
			$aff.="</div>\n";
		} else {
			$aff.="!!bouton_ajouter!!
					</div>\n";
		}		
		
		if($what_sel)$bouton_parcourir="<input type='button' class='bouton' value='".$msg["parcourir"]."' onclick=\"openPopUp(".$link.")\" />";
		else $bouton_parcourir="";
		$aff= str_replace('!!bouton_parcourir!!', $bouton_parcourir, $aff);	

		$template=$script_js."<div id=add".$item."' class='row'>";
		$template.="<div class='row'><label for='f_".$item."' class='etiquette'>".$label."</label></div>";
		$num=0;
		if(!$values[0]) $values[0] = array("id"=>"","label"=>"");
		foreach($values as $value) {
			
			$label_element=$value["label"];
			$id_element=$value["id"];
			
			$temp= str_replace('!!id_element!!', $id_element, $aff);	
			$temp= str_replace('!!label_element!!', $label_element, $temp);	
			$temp= str_replace('!!num!!', $num, $temp);	
			
			if(!$num) $temp= str_replace('!!bouton_ajouter!!', " <input class='bouton' value='".$msg["req_bt_add_line"]."' onclick='add_".$item."();' type='button'>", $temp);	
			else $temp= str_replace('!!bouton_ajouter!!', "", $temp);	
			$template.=$temp;			
			$num++;
		}	
		$template.="<input type='hidden' name='max_".$item."' value='$num'>";			
		
		$template.="</div><div id='add".$item."'/>
		</div>";
		return $template;		
	}	
	// ---------------------------------------------------------------
	//		show_form : affichage du formulaire de saisie
	// ---------------------------------------------------------------
	function show_form() {
	
		global $msg;
		global $titre_uniforme_form;
		global $charset;
		global $user_input, $nbr_lignes, $page ;
		global $pmb_type_audit;
		
		if($this->id) {
			$action = "./autorites.php?categ=titres_uniformes&sub=update&id=$this->id";
			$libelle = $msg["aut_titre_uniforme_modifier"];
			$button_remplace = "<input type='button' class='bouton' value='$msg[158]' ";
			$button_remplace .= "onclick='unload_off();document.location=\"./autorites.php?categ=titres_uniformes&sub=replace&id=$this->id\"'>";
			
			$button_voir = "<input type='button' class='bouton' value='$msg[voir_notices_assoc]' ";
			$button_voir .= "onclick='unload_off();document.location=\"./catalog.php?categ=search&mode=9&etat=aut_search&aut_type=titre_uniforme&aut_id=$this->id\"'>";
			
			$button_delete = "<input type='button' class='bouton' value='$msg[63]' ";
			$button_delete .= "onClick=\"confirm_delete();\">";
			
		} else {
			$action = './autorites.php?categ=titres_uniformes&sub=update&id=';
			$libelle = $msg["aut_titre_uniforme_ajouter"];
			$button_remplace = '';
			$button_delete ='';
		}
		
		if($this->import_denied == 1){
			$import_denied_checked = "checked='checked'";
		}else{
			$import_denied_checked = "";
		}	
		
		// remplacement de tous les champs du formulaire par les données
		$aut_link= new aut_link(AUT_TABLE_TITRES_UNIFORMES,$this->id);
		$titre_uniforme_form = str_replace('<!-- aut_link -->', $aut_link->get_form('saisie_titre_uniforme') , $titre_uniforme_form);
		
		$aut_pperso= new aut_pperso("tu",$this->id);
		$titre_uniforme_form = str_replace('!!aut_pperso!!',	$aut_pperso->get_form(), $titre_uniforme_form);
		
		$titre_uniforme_form = str_replace('!!id!!',				$this->id,		$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!action!!',			$action,		$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!libelle!!',			$libelle,		$titre_uniforme_form);
		
		$titre_uniforme_form = str_replace('!!nom!!',				htmlentities($this->name,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!aut_id!!',			htmlentities($this->num_author,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!form!!',				htmlentities($this->form,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!date!!',				htmlentities($this->date,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!subject!!',			htmlentities($this->subject,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!place!!',				htmlentities($this->place,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!history!!',			htmlentities($this->history,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!intended_audience!!',	htmlentities($this->intended_audience,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!context!!',			htmlentities($this->context,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!tonalite!!',			htmlentities($this->tonalite,ENT_QUOTES, $charset),	$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!coordinates!!',		htmlentities($this->coordinates,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!equinox!!',			htmlentities($this->equinox,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!characteristic!!',	htmlentities($this->characteristic,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!comment!!',			htmlentities($this->comment,ENT_QUOTES, $charset),	$titre_uniforme_form);
		// auteur
		$tu_auteur_id = $this->num_author;		
		if($tu_auteur_id){
			$tu_auteur = new auteur($tu_auteur_id);
		}		
		$titre_uniforme_form = str_replace('!!aut_name!!',htmlentities($tu_auteur->display,ENT_QUOTES, $charset), $titre_uniforme_form);
		// complétude
		$intended_termination_id = $this->intended_termination;
		$select_0=""; 	$select_1=""; $select_2="";	
		if($intended_termination_id == 1){
			$select_1 = "selected";
		} elseif($intended_termination_id == 2){
			$select_2 = "selected";
		} else {
			$select_0 = "selected";
		}
		$titre_uniforme_form = str_replace('!!intended_termination_0!!',	htmlentities($select_0,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!intended_termination_1!!',	htmlentities($select_1,ENT_QUOTES, $charset), $titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!intended_termination_2!!',	htmlentities($select_2,ENT_QUOTES, $charset), $titre_uniforme_form);		
		// distribution
		$distribution_form=$this->gen_input_selection($msg["aut_titre_uniforme_form_distribution"],"saisie_titre_uniforme","distrib",$this->distrib,"","saisie-80em");
		$titre_uniforme_form = str_replace("<!--	Distribution instrumentale et vocale (pour la musique)	-->",$distribution_form, $titre_uniforme_form);
		// reference
		$ref_num_form=$this->gen_input_selection($msg["aut_titre_uniforme_form_ref_numerique"],"saisie_titre_uniforme","ref",$this->ref,"","saisie-80em");
		$titre_uniforme_form = str_replace("<!--	Référence numérique (pour la musique)	-->",$ref_num_form, $titre_uniforme_form);
		// subdivision
		$sub_form=$this->gen_input_selection($msg["aut_titre_uniforme_form_subdivision_forme"],"saisie_titre_uniforme","subdiv",$this->subdiv,"","saisie-80em");
		$titre_uniforme_form = str_replace('<!-- Subdivision de forme -->',	$sub_form, $titre_uniforme_form);
		
		$titre_uniforme_form = str_replace('!!remplace!!',			$button_remplace,	$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!voir_notices!!',		$button_voir,		$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!delete!!',			$button_delete,		$titre_uniforme_form);
			
		$titre_uniforme_form = str_replace('!!user_input_url!!',	rawurlencode(stripslashes($user_input)),							$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!user_input!!',		htmlentities($user_input,ENT_QUOTES, $charset),						$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!nbr_lignes!!',		$nbr_lignes,														$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!page!!',				$page,																$titre_uniforme_form);
		$titre_uniforme_form = str_replace('!!tu_import_denied!!',	$import_denied_checked,												$titre_uniforme_form);	
		if ($pmb_type_audit && $this->id)
				$bouton_audit= "&nbsp;<input class='bouton' type='button' onClick=\"openPopUp('./audit.php?type_obj=".AUDIT_TITRE_UNIFORME."&object_id=".$this->id."', 'audit_popup', 700, 500, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" title=\"".$msg['audit_button']."\" value=\"".$msg['audit_button']."\" />&nbsp;";
		
		$titre_uniforme_form = str_replace('!!audit_bt!!',			$bouton_audit,														$titre_uniforme_form);
		
		print $titre_uniforme_form;
	}
	
	// ---------------------------------------------------------------
	//		replace_form : affichage du formulaire de remplacement
	// ---------------------------------------------------------------
	function replace_form() {
		global $titre_uniforme_replace;
		global $msg;
		global $include_path;
	
		if(!$this->id || !$this->name) {
			require_once("$include_path/user_error.inc.php");
			error_message($msg[161], $msg[162], 1, './autorites.php?categ=titres_uniformes&sub=&id=');
			return false;
		}	
		$titre_uniforme_replace=str_replace('!!old_titre_uniforme_libelle!!', $this->display, $titre_uniforme_replace);
		$titre_uniforme_replace=str_replace('!!id!!', $this->id, $titre_uniforme_replace);
		print $titre_uniforme_replace;
		return true;
	}
	
	
	// ---------------------------------------------------------------
	//		delete() : suppression 
	// ---------------------------------------------------------------
	function delete() {
		global $dbh;
		global $msg;
		
		if(!$this->id)	// impossible d'accéder à cette notice titre uniforme
			return $msg[403]; 
	
		// effacement dans les notices
		// récupération du nombre de notices affectées
		$requete = "SELECT count(1) FROM notices_titres_uniformes WHERE ntu_num_tu='$this->id' ";
	
		$res = mysql_query($requete, $dbh);
		$nbr_lignes = mysql_result($res, 0, 0);
		if($nbr_lignes) {
			// Ce titre uniforme est utilisé dans des notices, impossible de le supprimer
			return '<strong>'.$this->display."</strong><br />${msg['titre_uniforme_delete']}";
		}
	
		// effacement dans la table des titres_uniformes
		$requete = "DELETE FROM titres_uniformes WHERE tu_id='$this->id' ";
		mysql_query($requete, $dbh);
		// delete les champs répétables
		$requete = "DELETE FROM tu_distrib WHERE distrib_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_ref WHERE ref_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_subdiv WHERE subdiv_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		
		//suppression dans la table de stockage des numéros d'autorités...
		$this->delete_autority_sources($this->id);
		
		// liens entre autorités
		$aut_link= new aut_link(AUT_TABLE_TITRES_UNIFORMES,$this->id);
		$aut_link->delete();
		
		$aut_pperso= new aut_pperso("tu",$this->id);
		$aut_pperso->delete();
		audit::delete_audit(AUDIT_TITRE_UNIFORME,$this->id);
		return false;
	}
	
	// ---------------------------------------------------------------
	//		delete_autority_sources($idcol=0) : Suppression des informations d'import d'autorité
	// ---------------------------------------------------------------
	function delete_autority_sources($idtu=0){
		$tabl_id=array();
		if(!$idtu){
			$requete="SELECT DISTINCT num_authority FROM authorities_sources LEFT JOIN titres_uniformes ON num_authority=tu_id  WHERE authority_type = 'uniform_title' AND tu_id IS NULL";
			$res=mysql_query($requete);
			if(mysql_num_rows($res)){
				while ($ligne = mysql_fetch_object($res)) {
					$tabl_id[]=$ligne->num_authority;
				}
			}
		}else{
			$tabl_id[]=$idtu;
		}
		foreach ( $tabl_id as $value ) {
	       //suppression dans la table de stockage des numéros d'autorités...
			$query = "select id_authority_source from authorities_sources where num_authority = ".$value." and authority_type = 'uniform_title'";
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while ($ligne = mysql_fetch_object($result)) {
					$query = "delete from notices_authorities_sources where num_authority_source = ".$ligne->id_authority_source;
					mysql_query($query);
				}
			}
			$query = "delete from authorities_sources where num_authority = ".$value." and authority_type = 'uniform_title'";
			mysql_query($query);
		}
	}
	
	// ---------------------------------------------------------------
	//		replace($by) : remplacement 
	// ---------------------------------------------------------------
	function replace($by,$link_save) {
	
		global $msg;
		global $dbh;
	
		if (($this->id == $by) || (!$this->id))  {
			return $msg[223];
		}
		
		$aut_link= new aut_link(AUT_TABLE_TITRES_UNIFORMES,$this->id);
		// "Conserver les liens entre autorités" est demandé
		if($link_save) {
			// liens entre autorités
			$aut_link->add_link_to(AUT_TABLE_TITRES_UNIFORMES,$by);		
		}
		$aut_link->delete();
	
		// remplacement dans les responsabilités
		$requete = "UPDATE notices_titres_uniformes SET ntu_num_tu='$by' WHERE ntu_num_tu='$this->id' ";
		@mysql_query($requete, $dbh);
				
		// effacement dans la table des titres_uniformes
		$requete = "DELETE FROM titres_uniformes WHERE tu_id='$this->id' ";
		mysql_query($requete, $dbh);
		// delete les champs répétables
		$requete = "DELETE FROM tu_distrib WHERE distrib_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_ref WHERE ref_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_subdiv WHERE subdiv_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		
		//nettoyage d'autorities_sources
		$query = "select * from authorities_sources where num_authority = ".$this->id." and authority_type = 'uniform_title'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				if($row->authority_favorite == 1){
					//on suprime les références si l'autorité a été importée...
					$query = "delete from notices_authorities_sources where num_authority_source = ".$row->id_authority_source;
					mysql_result($query);
					$query = "delete from authorities_sources where id_authority_source = ".$row->id_authority_source;
					mysql_result($query);
				}else{
					//on fait suivre le reste
					$query = "update authorities_sources set num_authority = ".$by." where num_authority_source = ".$row->id_authority_source;
					mysql_query($query);
				}
			}
		}
		
		audit::delete_audit(AUDIT_TITRE_UNIFORME,$this->id);
				
		titre_uniforme::update_index($by);
		
		return FALSE;
	}
	
	// ---------------------------------------------------------------
	//		update($value) : mise à jour 
	// ---------------------------------------------------------------
	function update($value) {
	
		global $dbh;
		global $msg;
		global $include_path;
		
		if(!$value['name'])	return false;
	
		// nettoyage des chaînes en entrée		
		$value['name'] = clean_string($value['name']);
		$value['num_author'] = clean_string($value['num_author']);
		$value['form'] = clean_string($value['form']);
		$value['date'] = clean_string($value['date']);
		$value['subject'] = clean_string($value['subject']);
		$value['place'] = clean_string($value['place']);
		$value['history'] = clean_string($value['history']);
		$value['characteristic'] = clean_string($value['characteristic']);
		$value['intended_termination'] = clean_string($value['intended_termination']);
		$value['intended_audience'] = clean_string($value['intended_audience']);
		$value['context'] = clean_string($value['context']);
		$value['equinox'] = clean_string($value['equinox']);
		$value['coordinates'] = clean_string($value['coordinates']);
		$value['tonalite'] = clean_string($value['tonalite']);
		$value['comment'] = clean_string($value['comment']);
			
		$titre=titre_uniforme::import_tu_exist($value,1,$this->id);
		if($titre){
			require_once("$include_path/user_error.inc.php");
			warning($msg["aut_titre_uniforme_creation"], $msg["aut_titre_uniforme_doublon_erreur"]);
			return FALSE;
		}
		$tu_auteur = new auteur($value['num_author']);
		if(!$tu_auteur->id){
			$value['num_author']=0;
		} else {
			$value['num_author']=$tu_auteur->id;			
		}
				
		$flag_index=0;
		$requete  = "SET ";
		$requete .= "tu_name='".$value["name"]."', ";
		$requete .= "tu_num_author='".$value['num_author']."', ";
		$requete .= "tu_forme='".$value["form"]."', ";
		$requete .= "tu_date='".$value["date"]."', ";
		$requete .= "tu_sujet='".$value["subject"]."', ";
		$requete .= "tu_lieu='".$value["place"]."', ";
		$requete .= "tu_histoire='".$value["history"]."', ";
		$requete .= "tu_caracteristique='".$value["characteristic"]."', ";
		$requete .= "tu_completude='".$value["intended_termination"]."', ";
		$requete .= "tu_public='".$value["intended_audience"]."', ";
		$requete .= "tu_contexte='".$value["context"]."', ";
		$requete .= "tu_equinoxe='".$value["equinox"]."', ";
		$requete .= "tu_coordonnees='".$value["coordinates"]."', ";
		$requete .= "tu_tonalite='".$value["tonalite"]."', ";		
		$requete .= "tu_comment='".$value["comment"]."', ";
		$requete .= "tu_import_denied='".$value["import_denied"]."'";

		if($this->id) {
			// update
			$requete = 'UPDATE titres_uniformes '.$requete;
			$requete .= ' WHERE tu_id='.$this->id.' ;';
			
			if(mysql_query($requete, $dbh)) {
				$flag_index=1;
			} else {
				require_once("$include_path/user_error.inc.php"); 
				warning($msg["aut_titre_uniforme_creation"], $msg["aut_titre_uniforme_modif_erreur"]);
				return FALSE;
			}	
			
			audit::insert_modif (AUDIT_TITRE_UNIFORME, $this->id) ;
		} else {
			// creation
			$requete = 'INSERT INTO titres_uniformes '.$requete.' ';
			$result = mysql_query($requete,$dbh);
			if($result) {
				$this->id=mysql_insert_id();				
			} else {
				require_once("$include_path/user_error.inc.php"); 
				warning($msg["aut_titre_uniforme_creation"], $msg["aut_titre_uniforme_creation_erreur"]);
				return FALSE;
			}
			audit::insert_creation(AUDIT_TITRE_UNIFORME, $this->id) ;
		}
		$aut_link= new aut_link(AUT_TABLE_TITRES_UNIFORMES,$this->id);
		$aut_link->save_form();
		
		$aut_pperso= new aut_pperso("tu",$this->id);
		$aut_pperso->save_form();
		
		// Gestion des champ répétables
		$requete = "DELETE FROM tu_distrib WHERE distrib_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_ref WHERE ref_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		$requete = "DELETE FROM tu_subdiv WHERE subdiv_num_tu='$this->id' ";
		mysql_query($requete, $dbh);
		
		// Distribution instrumentale et vocale (pour la musique)
		for($i=0;$i<count($value['distrib']);$i++) {
			$requete = "INSERT INTO tu_distrib SET
			distrib_num_tu='$this->id',
			distrib_name='".$value['distrib'][$i]."',
			distrib_ordre='$i' ";
			mysql_query($requete, $dbh);
		}
		// Référence numérique (pour la musique)
		for($i=0;$i<count($value['ref']);$i++) {
		$requete = "INSERT INTO tu_ref SET
		ref_num_tu='$this->id',
		ref_name='".$value['ref'][$i]."',
		ref_ordre='$i' ";
				mysql_query($requete, $dbh);
		}
		// Subdivison de forme
		for($i=0;$i<count($value['subdiv']);$i++) {
		$requete = "INSERT INTO tu_subdiv SET
		subdiv_num_tu='$this->id',
		subdiv_name='".$value['subdiv'][$i]."',
		subdiv_ordre='$i' ";
		mysql_query($requete, $dbh);
		}
		
		// mise à jour du champ index du titre uniforme
		if($this->id)titre_uniforme::update_index_tu($this->id);
		
		// réindexation de la notice
		if($flag_index)titre_uniforme::update_index($this->id);		
		return TRUE;
	}
		
	// ---------------------------------------------------------------
	//		import() : import d'un titre_uniforme
	// ---------------------------------------------------------------
	// fonction d'import de notice titre_uniforme 
	function import($value,$from_form=0) {
		global $dbh;
		// Si vide on sort
		if(trim($value['name']) == '') return FALSE;
		if(!$from_form) {
			$value['name'] = addslashes($value['name']);
			$value['num_author'] = addslashes($value['num_author']);
			$value['form'] = addslashes($value['form']);
			$value['date'] = addslashes($value['date']);
			$value['subject'] = addslashes($value['subject']);
			$value['place'] = addslashes($value['place']);
			$value['history'] = addslashes($value['history']);
			$value['characteristic'] = addslashes($value['characteristic']);
			$value['intended_termination'] = addslashes($value['intended_termination']);
			$value['intended_audience'] = addslashes($value['intended_audience']);
			$value['context'] = addslashes($value['context']);
			$value['equinox'] = addslashes($value['equinox']);
			$value['coordinates'] = addslashes($value['coordinates']);
			$value['tonalite'] = addslashes($value['tonalite']);
			$value['comment'] = addslashes($value['comment']);
			for($i=0;$i<count($value['distrib']);$i++) {	
				$value['distrib'][$i]= addslashes($value['distrib'][$i]);		
			}
			for($i=0;$i<count($value['ref']);$i++) {	
				$value['ref'][$i]= addslashes($value['ref'][$i]);		
			}
			for($i=0;$i<count($value['subdiv']);$i++) {	
				$value['subdiv'][$i]= addslashes($value['subdiv'][$i]);		
			}			
		}		
			
		// s'assurer que ce titre uniforme n'existe pas déjà
		/*$dummy = "SELECT * FROM titres_uniformes WHERE tu_name='".$value['name']."' ";
		$check = mysql_query($dummy, $dbh);
		if (mysql_num_rows($check)) {
			$tu=mysql_fetch_object($check);
			$tu_id=$tu->tu_id;
			return $tu->tu_id;
		}*/
		
		$tu_auteur = new auteur($value['num_author']);
		if(!$tu_auteur->id){
			$value['num_author']=0;
		} else {
			$value['num_author']=$tu_auteur->id;			
		}
		
		$titre=titre_uniforme::import_tu_exist($value,$from_form);
		if($titre){
			return $titre;
		}
			
		$requete  = "INSERT INTO titres_uniformes SET ";
		$requete .= "tu_name='".$value["name"]."', ";
		$requete .= "tu_num_author='".$value["num_author"]."', ";
		$requete .= "tu_forme='".$value["form"]."', ";
		$requete .= "tu_date='".$value["date"]."', ";
		$requete .= "tu_sujet='".$value["subject"]."', ";
		$requete .= "tu_lieu='".$value["place"]."', ";
		$requete .= "tu_histoire='".$value["history"]."', ";
		$requete .= "tu_caracteristique='".$value["characteristic"]."', ";
		$requete .= "tu_completude='".$value["intended_termination"]."', ";
		$requete .= "tu_public='".$value["intended_audience"]."', ";
		$requete .= "tu_contexte='".$value["context"]."', ";
		$requete .= "tu_equinoxe='".$value["equinox"]."', ";
		$requete .= "tu_coordonnees='".$value["coordinates"]."', ";
		$requete .= "tu_tonalite='".$value["tonalite"]."', ";		
		$requete .= "tu_comment='".$value["comment"]."'";
		
		// insertion du titre uniforme	et mise à jour de l'index tu
		if(mysql_query($requete, $dbh)) {
			$tu_id=mysql_insert_id();			
		} else {
			return FALSE;
		}		

		// Distribution instrumentale et vocale (pour la musique)
		for($i=0;$i<count($value['distrib']);$i++) {
			$requete = "INSERT INTO tu_distrib SET
			distrib_num_tu='$tu_id',
			distrib_name='".$value['distrib'][$i]."',
			distrib_ordre='$i' ";
			mysql_query($requete, $dbh);
		}
		// Référence numérique (pour la musique)
		for($i=0;$i<count($value['ref']);$i++) {
			$requete = "INSERT INTO tu_ref SET
			ref_num_tu='$tu_id',
			ref_name='".$value['ref'][$i]."',
			ref_ordre='$i' ";
			mysql_query($requete, $dbh);
		}
		// Subdivision de forme
		for($i=0;$i<count($value['subdiv']);$i++) {
			$requete = "INSERT INTO tu_subdiv SET
			subdiv_num_tu='$tu_id',
			subdiv_name='".$value['subdiv'][$i]."',
			subdiv_ordre='$i' ";
			mysql_query($requete, $dbh);
		}
		
		audit::insert_creation(AUDIT_TITRE_UNIFORME, $tu_id) ;		
		
		// mise à jour du champ index du titre uniforme
		if($tu_id)titre_uniforme::update_index_tu($this->id);
		
		return 	$tu_id;		
	}
	
	// ---------------------------------------------------------------
	//		import_tu_exist() : Recherche si le titre uniforme existe déjà
	// ---------------------------------------------------------------
	function import_tu_exist($value,$from_form=0,$tu_id=0) {
		global $dbh;
		// Si vide on sort
		if(trim($value['name']) == '') return FALSE;
		if(!$from_form) {
			$value['name'] = addslashes($value['name']);
			$value['tonalite'] = addslashes($value['tonalite']);
			$value['num_author'] = addslashes($value['num_author']);
			$value['form'] = addslashes($value['form']);
			$value['date'] = addslashes($value['date']);
			$value['subject'] = addslashes($value['subject']);
			$value['place'] = addslashes($value['place']);
			$value['history'] = addslashes($value['history']);
			$value['characteristic'] = addslashes($value['characteristic']);
			$value['intended_termination'] = addslashes($value['intended_termination']);
			$value['intended_audience'] = addslashes($value['intended_audience']);
			$value['context'] = addslashes($value['context']);
			$value['equinox'] = addslashes($value['equinox']);
			$value['coordinates'] = addslashes($value['coordinates']);
			
			for($i=0;$i<count($value['distrib']);$i++) {	
				$value['distrib'][$i]= addslashes($value['distrib'][$i]);		
			}
			for($i=0;$i<count($value['ref']);$i++) {	
				$value['ref'][$i]= addslashes($value['ref'][$i]);		
			}
		}	
		$dummy = "SELECT * FROM titres_uniformes WHERE tu_name='".$value['name']."' AND tu_tonalite='".$value['tonalite']."' AND tu_num_author='".$value['num_author']."' ";
		$dummy.= "AND tu_forme='".$value['form']."' AND tu_date='".$value['date']."' AND tu_sujet='".$value['subject']."' AND tu_lieu='".$value['place']."' ";
		$dummy.= "AND tu_histoire='".$value['history']."' AND tu_caracteristique='".$value['characteristic']."' AND tu_completude='".$value['intended_termination']."' ";
		$dummy.= "AND tu_public='".$value['intended_audience']."' AND tu_contexte='".$value['context']."' AND tu_coordonnees='".$value['coordinates']."' ";
		$dummy.= "AND tu_equinoxe='".$value['equinox']."'";
		if($tu_id) $dummy = $dummy."and tu_id!='".$tu_id."'"; // Pour la création ou la mise à jour par l'interface 
		$check = mysql_query($dummy, $dbh);
		if (mysql_error()=="" && mysql_num_rows($check)) {
			while($row = mysql_fetch_object($check)){
				$tu_id=$row->tu_id;
				$différent=false;
				
				//Test si les titres de même nom ont aussi la (ou les) même distribution
				if(count($value['distrib']) == 0){ //Si le titre que je veux ajouter n'a pas de distribution je regarde si celui qui existe en a une
					$requete = "select distrib_num_tu from tu_distrib where  
					distrib_num_tu='$tu_id' ";
					$test = mysql_query($requete, $dbh);
					if (mysql_num_rows($test)) {
						$différent = true; //S'il a une distribution, le titre que je veux ajouter est différent
					}
					
				}else{
					//On teste s'il y a autant de distribution
					$requete = "select distrib_num_tu from tu_distrib where distrib_num_tu='$tu_id' ";
					$nb=mysql_num_rows(mysql_query($requete, $dbh));
					if($nb != count($value['distrib'])){ //Si il y en a pas autant c'est un titre différent
						$différent = true;
					}else{ //Sinon on regarde si ce sont les mêmes
						$nb_occurence=array_count_values($value['distrib']);//avoir le nombre d'occurence de chaque terme
						for($i=0;$i<count($value['distrib']);$i++) {
							$requete = "select count(distrib_num_tu) from tu_distrib where  
							distrib_num_tu='$tu_id' and 
							distrib_name='".$value['distrib'][$i]."' group by distrib_num_tu "; 
							$test = mysql_query($requete, $dbh);
							$nb=@mysql_result($test,0,0);
							if (!$nb) {
								$différent = true; //Si une des distributions n'existe pas c'est un titre uniforme différent
							}elseif($nb != $nb_occurence[$value['distrib'][$i]]){
								$différent = true; //Si le nombre de cette distribution est différent c'est un titre uniforme différent
							}
						}	
					}
				}
				//Test si les titres de même nom ont aussi la (ou les) même réference
				if(count($value['ref']) == 0){ //Si le titre que je veux ajouter n'a pas de référence, je regarde si celui qui existe en a une
					$requete = "select ref_num_tu from tu_ref where  
					ref_num_tu='$tu_id' ";
					$test = mysql_query($requete, $dbh);
					if (mysql_num_rows($test)) {
						$différent = true; //S'il a une réference, le titre que je veux ajouter est différent
					}
					
				}else{
					//On teste s'il y a autant de réference
					$requete = "select ref_num_tu from tu_ref where ref_num_tu='$tu_id' ";
					$nb=mysql_num_rows(mysql_query($requete, $dbh));
					if($nb != count($value['ref'])){ //Si il y en a pas autant c'est un titre différent
						$différent = true;
					}else{ //Sinon on regarde si ce sont les mêmes
						$nb_occurence=array_count_values($value['ref']);//avoir le nombre d'occurence de chaque terme
						for($i=0;$i<count($value['ref']);$i++) {
							$requete = "select count(ref_num_tu) from tu_ref where  
							ref_num_tu='$tu_id' and 
							ref_name='".$value['ref'][$i]."' group by ref_num_tu "; 
							$test = mysql_query($requete, $dbh);
							$nb=@mysql_result($test,0,0);
							if (!$nb) {
								$différent = true; //Si une des réference n'existe pas c'est un titre uniforme différent
							}elseif($nb != $nb_occurence[$value['ref'][$i]]){
								$différent = true; //Si le nombre de cette réference est différent c'est un titre uniforme différent
							}
						}	
					}
				}
				if($différent == false){ //Si le titre n'est pas différent on retourne l'id du titre identique
					return $tu_id;
				}	
			}
			return $tu->tu_id;	}		
		// Subdivision de forme 
		for($i=0;$i<count($value['subdiv']);$i++) {		
		}	
		return 0;
	}	
	// ---------------------------------------------------------------
	//		search_form() : affichage du form de recherche
	// ---------------------------------------------------------------
	static function search_form() {
		global $user_query, $user_input;
		global $msg, $charset;
		
		$user_query = str_replace ('!!user_query_title!!', $msg[357]." : ".$msg["aut_menu_titre_uniforme"] , $user_query);
		$user_query = str_replace ('!!action!!', './autorites.php?categ=titres_uniformes&sub=reach&id=', $user_query);
		$user_query = str_replace ('!!add_auth_msg!!', $msg["aut_titre_uniforme_ajouter"] , $user_query);
		$user_query = str_replace ('!!add_auth_act!!', './autorites.php?categ=titres_uniformes&sub=titre_uniforme_form', $user_query);
		$user_query = str_replace ('<!-- lien_derniers -->', "<a href='./autorites.php?categ=titres_uniformes&sub=titre_uniforme_last'>".$msg["aut_titre_uniforme_derniers_crees"]."</a>", $user_query);
		$user_query = str_replace("!!user_input!!",htmlentities(stripslashes($user_input),ENT_QUOTES, $charset),$user_query);
			
		print pmb_bidi($user_query) ;
	}
	
	//---------------------------------------------------------------
	// update_index($id) : maj des n-uplets la table notice_global_index en rapport avec ce titre uniforme
	//---------------------------------------------------------------
	function update_index($id) {
		global $dbh;
		// On cherche tous les n-uplet de la table notice correspondant à ce titre_uniforme.
		$found = mysql_query("select ntu_num_notice from notices_titres_uniformes where ntu_num_tu = ".$id,$dbh);
		// Pour chaque n-uplet trouvés on met a jour la table notice_global_index avec l'auteur modifié :
		while(($mesNotices = mysql_fetch_object($found))) {
			$notice_id = $mesNotices->ntu_num_notice;
			notice::majNoticesGlobalIndex($notice_id);
			notice::majNoticesMotsGlobalIndex($notice_id,'uniformtitle'); //TODO preciser le datatype avant d'appeler cette fonction
		}
	}
	
	//---------------------------------------------------------------
	// get_informations_from_unimarc : ressort les infos d'un titre uniforme depuis une notice unimarc
	//---------------------------------------------------------------
	
	function get_informations_from_unimarc($fields,$zone){
		$data = array();
		if($zone == "2"){
			$data['name'] = $fields[$zone.'30'][0]['a'][0];
			$data['tonalite']= $fields[$zone.'30'][0]['u'][0];
			$data['date']= $fields[$zone.'30'][0]['k'][0];
			$data['distrib'] = array();
			for($i=0 ; $i<count($fields[$zone.'30'][0]['r']) ; $i++){
				$data['distrib'][] = $fields[$zone.'30'][0]['r'][$i];
			}
			$data['ref'] = array();
			for($i=0 ; $i<count($fields[$zone.'30'][0]['s']) ; $i++){
				$data['ref'][] = $fields[$zone.'30'][0]['s'][$i];
			}
			$data['subdiv'] = array();
			for($i=0 ; $i<count($fields[$zone.'30'][0]['j']) ; $i++){
				$data['subdiv'][] = $fields[$zone.'30'][0]['j'][$i];
			}
			$data['comment'] = "";
			for($i=0 ; $i<count($fields['300']) ; $i++){
				for($j=0; $j<count($fields['300'][$i]['a']) ; $j++){
					if($data['comment'] != "") $data['comment'].="\n";
					$data['comment'] .= $fields['300'][$i]['a'][$j];
				}
			}
		}else{
			$data['name'] = $fields['a'][0];
			$data['tonalite']= $fields['u'][0];
			$data['date']= $fields['k'][0];
			$data['distrib'] = array();
			for($i=0 ; $i<count($fields['r']) ; $i++){
				$data['distrib'][] = $fields['r'][$i];
			}
			$data['ref'] = array();
			for($i=0 ; $i<count($fields['s']) ; $i++){
				$data['ref'][] = $fields['s'][$i];
			}	
			$data['subdiv'] = array();
			for($i=0 ; $i<count($fields['j']) ; $i++){
				$data['subdiv'][] = $fields['j'][$i];
			}	
		}
		$data['type_authority'] = "uniform_title";
		return $data;
	}
	
	// ---------------------------------------------------------------
	//		majIndexTu() : mise à jour du champ tu_index d'un titre uniforme
	// ---------------------------------------------------------------
	static function update_index_tu($tu_id){
		global $dbh;
		global $msg;
		global $include_path;		
		
		if($tu_id){
			
			$requete = "UPDATE titres_uniformes SET index_tu=";
						
			$oeuvre = new titre_uniforme($tu_id);			
			$auteur = new auteur($oeuvre->num_author);
			
			$index.= $oeuvre->name." ".$oeuvre->tonalite." ".$oeuvre->subject." ".$oeuvre->place." ".$oeuvre->history." ";
			$index.= $oeuvre->date." ".$oeuvre->context." ".$oeuvre->equinox." ".$oeuvre->coordinates." ";
			$index.= $auteur->name." ".$auteur->rejete." ";
			
			$req = "SELECT distrib_name FROM tu_distrib WHERE distrib_num_tu='$tu_id' ";
			$res = mysql_query($req, $dbh);
			if($distrib=mysql_fetch_object($res)){
				$index.= $distrib->distrib_name." ";
			}
			$req = "SELECT ref_name FROM tu_ref WHERE ref_num_tu='$tu_id' ";
			$res = mysql_query($req, $dbh);
			if($ref=mysql_fetch_object($res)){
				$index.= $ref->ref_name." ";
			}
			
			$requete .= "'".addslashes(strip_empty_chars($index))."' WHERE tu_id=".$tu_id;
			$result = mysql_query($requete,$dbh);
		}
		return ;		
	}
	
	// ---------------------------------------------------------------
	//		do_isbd() : génération de l'isbd du titre uniforme (AFNOR Z 44-061 de 1986)
	// ---------------------------------------------------------------
	function do_isbd() {
		global $msg;
	
		$this->tu_isbd="";
		if(!$this->id) return;
		
		if($this->num_author){
			$tu_auteur = new auteur ($this->num_author);
			$this->tu_isbd = $tu_auteur->display.". ";
		}
		if($this->name){
			$this->tu_isbd.= $this->name;
		}
				
		return $this->tu_isbd;
	}
} // class titre uniforme


