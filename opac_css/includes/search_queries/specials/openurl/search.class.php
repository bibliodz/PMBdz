<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.class.php,v 1.1 2011-08-02 12:35:59 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/rec_history.inc.php");

//Classe de gestion de la recherche sp�cial "combine"

class openurl_search {
	var $id;
	var $n_ligne;
	var $params;
	var $search;

	//Constructeur
    function openurl_search($id,$n_ligne,$params,&$search) {
    	$this->id=$id;
    	$this->n_ligne=$n_ligne;
    	$this->params=$params;
    	$this->search=&$search;
    }
    
    //fonction de r�cup�ration des op�rateurs disponibles pour ce champ sp�cial (renvoie un tableau d'op�rateurs)
    function get_op() {
    	$operators = array();
    	if ($_SESSION["nb_queries"]!=0) {
    		$operators["EQ"]="=";
    	}
    	return $operators;
    }
    
    //fonction de r�cup�ration de l'affichage de la saisie du crit�re
    function get_input_box() {
    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	//on stocke l'environnement courant
    	$current_search = search::serialize_search();
    	//on le d�truit
    	search::destroy_global_env();
    	//et on se met dans le contexte de la requete OpenURL
    	$this->s = new search("search_openurl");
    	$this->s->unserialize_search($valeur[0]);
    	global $search;
    	//on g�n�re une human_query
    	$r.=$this->s->make_human_query();
    	$r.="<span><input type='hidden' name='field_".$this->n_ligne."_s_".$this->id."[]' value='".htmlentities($valeur[0],ENT_QUOTES,$charset)."'/></span>";
    	//et on d�truit le contexte d'OpenURL pour revenir en mode normal
 		search::destroy_global_env();
    	search::unserialize_search($current_search);   	
    	return $r;
    }

    //fonction de conversion de la saisie en quelque chose de compatible avec l'environnement
    function transform_input() {
    }
    
    //fonction de cr�ation de la requ�te (retourne une table temporaire)
    function make_search() {
     	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	   	
     	//on stocke l'environnement courant
    	$current_search = search::serialize_search();
    	//on le d�truit
    	search::destroy_global_env();
    	//et on se met dans le contexte de la requete OpenURL
    	$this->s = new search("search_openurl");
    	$this->s->unserialize_search($valeur[0]);
    	global $search;
    	//on cherche...
    	$table_tempo=$this->s->make_search("openurl_".$this->n_ligne,true);  	
    	//et on d�truit le contexte d'OpenURL pour revenir en mode normal 	
    	search::destroy_global_env();
    	search::unserialize_search($current_search);
    	
    	return $table_tempo;  	
    }
    
    //fonction de traduction litt�rale de la requ�te effectu�e (renvoie un tableau des termes saisis)
    function make_human_query() {
     	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
      	$litteral=array();
    	
      	//on stocke l'environnement courant
    	$current_search = search::serialize_search();
    	//on le d�truit
    	search::destroy_global_env();
    	//et on se met dans le contexte de la requete OpenURL
    	$this->s = new search("search_openurl");
    	$this->s->unserialize_search($valeur[0]);
    	global $search;
    	//on g�n�re une human_query
    	$litteral[0]=$this->s->make_human_query();
    	//et on d�truit le contexte d'OpenURL pour revenir en mode normal
 		search::destroy_global_env();
    	search::unserialize_search($current_search);   	
    	
    	return $litteral;	   	
    }
    
    function make_unimarc_query() {
    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	
    	if (!$this->is_empty($valeur)) {
    		
    		//enregistrement de l'environnement courant
    		$this->search->push();
    		
    		//on instancie la classe search avec le nom de la nouvelle table temporaire
			switch ($_SESSION["search_type".$valeur[0]]) {
			case 'simple_search':
				global $search;
				
				switch($_SESSION["notice_view".$valeur[0]]["search_mod"]) {
				case 'title':
					$search[0]="f_6";
					$op_="BOOLEAN";
					$valeur_champ=$_SESSION["user_query".$valeur[0]];
				break;
				case 'all':
					$search[0]="f_7";
					$op_="BOOLEAN";
					$valeur_champ=$_SESSION["user_query".$valeur[0]];
				break;
				case 'abstract':
					$search[0]="f_13";
					$op_="BOOLEAN";
					$valeur_champ=$_SESSION["user_query".$valeur[0]];
				break;
				case 'keyword':
					$search[0]="f_12";
					$op_="BOOLEAN";
					$valeur_champ=$_SESSION["user_query".$valeur[0]];
				break;
				case 'author_see':
					$search[0]="f_8";	
					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;
				case 'categ_see':
					$search[0]="f_1";	
					$op_="EQ";
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;		
				case 'indexint_see':	
					$search[0]="f_2";
					$op_="EQ";	
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;		
				case 'coll_see':	
					$search[0]="f_4";
					$op_="EQ";	
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;		
				case 'publisher_see':	
					$search[0]="f_3";
					$op_="EQ";	
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;		
				case 'subcoll_see':	
					$search[0]="f_5";
					$op_="EQ";	
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;
				case 'titre_uniforme_see':	
					$search[0]="f_6";
					$op_="EQ";	
					$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				break;		
				}
				//op�rateur
    			$op="op_0_".$search[0];
    			global $$op;
    			$$op=$op_;
    		    			
    			//contenu de la recherche
    			$field="field_0_".$search[0];
    			$field_=array();
    			$field_[0]=$valeur_champ;
    			global $$field;
    			$$field=$field_;
    	    	    	    	
    	    	//op�rateur inter-champ
    			$inter="inter_0_".$search[0];
    			global $$inter;
    			$$inter="";
    			    		
    			//variables auxiliaires
    			$fieldvar_="fieldvar_0_".$search[0];
    			global $$fieldvar_;
    			$$fieldvar_="";
    			$fieldvar=$$fieldvar_;	
								
	       		$es=new search("search_simple_fields");	
	       	break;	
			case 'extended_search':
				get_history($valeur[0]);
				$es=new search();
			break;
			case 'term_search':
				global $search;
				
				$search[0]="f_1";
				$op_="EQ";
				$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				
				//op�rateur
    			$op="op_0_".$search[0];
    			global $$op;
    			$$op=$op_;
    		    			
    			//contenu de la recherche
    			$field="field_0_".$search[0];
    			$field_=array();
    			$field_[0]=$valeur_champ;
    			global $$field;
    			$$field=$field_;
    	    	
    	    	//op�rateur inter-champ
    			$inter="inter_0_".$search[0];
    			global $$inter;
    			$$inter="";
    			    		
    			//variables auxiliaires
    			$fieldvar_="fieldvar_0_".$search[0];
    			global $$fieldvar_;
    			$$fieldvar_="";
    			$fieldvar=$$fieldvar_;
    							
				$es=new search("search_simple_fields");	
			break;
			case 'module':
				global $search;
	       			       		
	       		switch($_SESSION["notice_view".$valeur[0]]["search_mod"]) {
	       		case 'categ_see':
					$search[0]="f_1";	
				break;		
				case 'indexint_see':	
					$search[0]="f_2";	
				break;		
				case 'etagere_see':
					$search[0]="f_14";
				break;	
				case 'section_see':
					$search[0]="f_15";
					global $search_localisation;
					$search_localisation=$_SESSION["notice_view".$valeur[0]]["search_location"];
				break;
				}
				
				$op_="EQ";
				$valeur_champ=$_SESSION["notice_view".$valeur[0]]["search_id"];
				
				//op�rateur
    			$op="op_0_".$search[0];
    			global $$op;
    			$$op=$op_;
    		    			
    			//contenu de la recherche
    			$field="field_0_".$search[0];
    			$field_=array();
    			$field_[0]=$valeur_champ;
    			global $$field;
    			$$field=$field_;
    	    	
    	    	//op�rateur inter-champ
    			$inter="inter_0_".$search[0];
    			global $$inter;
    			$$inter="";
    			    		
    			//variables auxiliaires
    			$fieldvar_="fieldvar_0_".$search[0];
    			global $$fieldvar_;
    			//fieldvar attention pour la section
    			$$fieldvar_="";
    			$fieldvar=$$fieldvar_;
    			
				$es=new search("search_simple_fields");
			break;
			
			}
						
			$mt=$es->make_unimarc_query();
									
			//restauration de l'environnement courant
			$this->search->pull();
			
    	}
		return $mt; 
    }
    
    //fonction de d�coupage d'une chaine trop longue
    function cutlongwords($valeur) {
    	if (strlen($valeur)>=50) {
    		$pos=strrpos(substr($valeur,0,50)," ");
    		if ($pos) {
    			$valeur=substr($valeur,0,$pos+1)."...";
    		} 
    	} 
    	return $valeur;		
    }
    
	//fonction de v�rification du champ saisi ou s�lectionn�
    function is_empty($valeur) {
    	if (count($valeur)) {
    		if ($valeur[0]=="-1") return true;
    			else return ($valeur[0] === false);
    	} else {
    		return true;
    	}	
    }
}
?>