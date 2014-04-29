<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titre_uniforme.class.php,v 1.10 2014-03-05 10:49:24 mhoestlandt Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/notice.class.php");

/*
 *  Classe recopi�e de la gestion, all�g�e des m�thodes inutiles en OPAC
 */
class titre_uniforme {
	
	// ---------------------------------------------------------------
	//		propri�t�s de la classe
	// ---------------------------------------------------------------	
	var $id;					// MySQL id in table 'titres_uniformes'
	var $name;					// titre_uniforme name
	var $tonalite;				// tonalite de l'oeuvre musicale
	var $comment ;  			// Commentaire, peut contenir du HTML
	var $import_denied=0;		// bool�en pour interdire les modification depuis un import d'autorit�s
	var $form;					// cat�gorie � laquelle appartient l'oeuvre (roman, pi�ce de th�atre, poeme, ...)
	var $date; 					// date de cr�ation originelle de l'oeuvre
	var $date_date;				// date format�e yyyy-mm-dd
	var $characteristic; 		// caract�ristique permettant de distinguer une oeuvre d'une autre peuvre portant le m�me titre
	var $intended_termination;	// compl�tude d'une oeuvre est finie ou se poursuit ind�finiment
	var $intended_audience;		// categorie de personnes � laquelle l'oeuvre s'adresse
	var $context;				// contexte historique, social, intellectuel, artistique ou autre au sein duquel l'oeuvre a �t� con�ue
	var $coordinates;			// coordonnees d'une oeuvre g�ographique (degr�s, minutes et secondes de longitude et latitude ou angles de d�clinaison et d'ascension des limiets de la zone repr�sent�e)
	var $equinox;				// ann�e de r�f�rence pour une carte ou un mod�le c�leste
	var $subject;				// contenu de l'oeuvre et sujets qu'elle aborde
	var $place;					// pays ou juridiction territoriale dont l'oeuvre est originaire
	var $history;				// informations concernant l'histoire de l'oeuvre
	var $num_author;			// identifiant de l'auteur principal de l'oeuvre
	var $display;				// usable form for displaying ( _name_ (_date_) / _author_name_ _author_rejete_ )
	var $tu_isbd;				// affichage isbd du titre uniforme AFNOR Z 44-061 (1986),
	
	// ---------------------------------------------------------------
	//		titre_uniforme($id) : constructeur
	// ---------------------------------------------------------------
	function titre_uniforme($id=0,$recursif=0) {
		if($id) {
			// on cherche � atteindre une notice existante
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
	//		getData() : r�cup�ration infos titre_uniforme
	// ---------------------------------------------------------------
	function getData() {
		global $dbh,$msg;

		$this->name = '';			
		$this->tonalite = '';
		$this->comment ='';
		$this->distrib=array();
		$this->ref=array();
		$this->subdiv=array();
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
			$requete = "SELECT * FROM titres_uniformes WHERE tu_id='".addslashes($this->id)."' LIMIT 1 ";
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
				
				$requete = "SELECT * FROM tu_distrib WHERE distrib_num_tu='$this->id' order by distrib_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->distrib[]["label"]=$param->distrib_name;
					}	
				}					
				$requete = "SELECT *  FROM tu_ref WHERE ref_num_tu='$this->id' order by ref_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->ref[]["label"]=$param->ref_name;
					}	
				}			
				$requete = "SELECT *  FROM tu_subdiv WHERE subdiv_num_tu='$this->id' order by subdiv_ordre";
				$result = mysql_query($requete, $dbh);
				if(mysql_num_rows($result)) {
					while(($param=mysql_fetch_object($result))) {
						$this->subdiv[]["label"]=$param->subdiv_name;
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
								
								
			} else {
				// pas trouv� avec cette cl�
				$this->id = 0;				
				
			}
		}
	}
	
	// ---------------------------------------------------------------
	//  print_resume($level) : affichage d'informations sur le titre uniforme
	// ---------------------------------------------------------------

	function print_resume($level = 2) {
		global $msg,$charset;
		
		if(!$this->id)
			return;

		// adaptation par rapport au niveau de d�tail souhait�
		switch ($level) {
			// case x :
			case 2 :
			default :
				global $titre_uniforme_level2_display;
				$titre_uniforme_display = $titre_uniforme_level2_display;
			break;
		}
		$print = $titre_uniforme_display;

		$print_distrib=$print_ref=$print_subdiv='';
		foreach ($this->distrib as $field) {
			if($print_distrib) $print_distrib.="; ";
			$print_distrib.=$field["label"];
		}
		foreach ($this->ref as $field) {
			if($print_ref) $print_ref.="; ";
			$print_ref.=$field["label"];
		}
		foreach ($this->subdiv as $field) {
			if($print_subdiv) $print_subdiv.="; ";
			$print_subdiv.=$field["label"];
		}	
		
		// remplacement des champs
		$print = str_replace("!!id!!", $this->id, $print);
		$print = str_replace("!!name!!", $this->name, $print);		
		
		$tu_auteur = new auteur($this->num_author);
		$print = str_replace("!!auteur!!", ($this->num_author?"<p>".$msg["aut_oeuvre_form_auteur"]." : <a href='index.php?lvl=author_see&id=".$tu_auteur->id."'>".htmlentities($tu_auteur->display,ENT_QUOTES,$charset)."</a></p>":""), $print);
		$print = str_replace("!!forme!!", ($this->form?"<p>".$msg["aut_oeuvre_form_forme"]." : ".htmlentities($this->form,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!date!!", ($this->date?"<p>".$msg["aut_oeuvre_form_date"]." : ".htmlentities($this->date,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!sujet!!", ($this->subject?"<p>".$msg["aut_oeuvre_form_sujet"]." : ".htmlentities($this->subject,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!lieu!!", ($this->place?"<p>".$msg["aut_oeuvre_form_lieu"]." : ".htmlentities($this->place,ENT_QUOTES,$charset)."</p>":""), $print);
		$completude='';
		if($this->intended_termination==1){
			$completude="Oeuvre finie";
		} elseif($this->intended_termination==2){
			$completude="Oeuvre infinie";
		}
		$print= str_replace("!!completude!!", ($completude?"<p>".$msg["aut_oeuvre_form_completude"]." : ".htmlentities($completude,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!public!!", ($this->intended_audience?"<p>".$msg["aut_oeuvre_form_public"]." : ".htmlentities($this->intended_audience,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!histoire!!", ($this->history?"<p>".$msg["aut_oeuvre_form_histoire"]." : ".htmlentities($this->history,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!contexte!!", ($this->context?"<p>".$msg["aut_oeuvre_form_contexte"]." : ".htmlentities($this->context,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!distribution!!", ($print_distrib?"<p>Distribution (oeuvre musicale) : ".htmlentities($print_distrib,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!reference!!", ($print_ref?"<p>R�f�rence (oeuvre musicale) : ".htmlentities($print_ref,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!tonalite!!", ($this->tonalite?"<p>Tonalit� (oeuvre musicale) : ".htmlentities($this->tonalite,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!subdivision!!", ($print_subdiv?"<p>Subdivision de forme : ".htmlentities($print_subdiv,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!coordonnees!!", ($this->coordinates?"<p>".$msg["aut_oeuvre_form_coordonnees"]." : ".htmlentities($this->context,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!equinoxe!!", ($this->equinox?"<p>".$msg["aut_oeuvre_form_equinoxe"]." : ".htmlentities($this->equinox,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!caracteristique!!", ($this->characteristic?"<p>".$msg["aut_oeuvre_form_caracteristique"]." : ".htmlentities($this->characteristic,ENT_QUOTES,$charset)."</p>":""), $print);
		$print = str_replace("!!aut_comment!!", $this->comment, $print);		

		return $print;
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
		!!bouton_parcourir!!
		!!bouton_ajouter!!
		</div>\n";
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
	//		search_form() : affichage du form de recherche
	// ---------------------------------------------------------------
	function search_form() {
		global $user_query;
		global $msg;
		$user_query = str_replace ('!!user_query_title!!', $msg[357]." : ".$msg["aut_menu_titre_uniforme"] , $user_query);
		$user_query = str_replace ('!!action!!', './autorites.php?categ=titres_uniformes&sub=reach&id=', $user_query);
		$user_query = str_replace ('!!add_auth_msg!!', $msg["aut_titre_uniforme_ajouter"] , $user_query);
		$user_query = str_replace ('!!add_auth_act!!', './autorites.php?categ=titres_uniformes&sub=titre_uniforme_form', $user_query);
		$user_query = str_replace ('<!-- lien_derniers -->', "<a href='./autorites.php?categ=titres_uniformes&sub=titre_uniforme_last'>".$msg["aut_titre_uniforme_derniers_crees"]."</a>", $user_query);
		print pmb_bidi($user_query) ;
	}
	
	// ---------------------------------------------------------------
	//		do_isbd() : g�n�ration de l'isbd du titre uniforme (AFNOR Z 44-061 de 1986)
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
	
	
} // class auteur


