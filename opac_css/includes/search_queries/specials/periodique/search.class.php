<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.class.php,v 1.1 2011-06-13 08:18:52 gueluneau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

//Classe de gestion de la recherche sp�cial "combine"

class periodique_search {
	var $id;
	var $n_ligne;
	var $params;
	var $search;

	//Constructeur
    function periodique_search($id,$n_ligne,$params,&$search) {
    	$this->id=$id;
    	$this->n_ligne=$n_ligne;
    	$this->params=$params;
    	$this->search=&$search;
    }
    
    //fonction de r�cup�ration des op�rateurs disponibles pour ce champ sp�cial (renvoie un tableau d'op�rateurs)
    function get_op() {
    	$operators = array();
   		$operators["EQ"]="=";
    	return $operators;
    }
    
    //fonction de r�cup�ration de l'affichage de la saisie du crit�re
    function get_input_box() {
    	global $msg;
    	global $charset;

    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	
    	//Affichage de la liste des p�riodiques
    	if (!$this->is_empty($valeur)) {
    		$requete="select tit1 from notices where notice_id=".$valeur[0];
    		$r=mysql_query($requete);
    		return "<b><i>".mysql_result($r,0,0)."</i></b><input type='hidden' name='field_".$this->n_ligne."_s_".$this->id."[]' value='".$valeur[0]."'/>";
    	} else {
    		$r="<select name='field_".$this->n_ligne."_s_".$this->id."[]'>";
    		$requete="select notice_id,tit1 from notices where niveau_biblio='s' order by index_sew";
    		$res_perio=mysql_query($requete);
    		while ($t_perio=mysql_fetch_object($res_perio)) {
    			$r.="<option value='".$t_perio->notice_id."'".($valeur[0]==$t_perio->notice_id?" selected='selected'":"").">".htmlentities($t_perio->tit1,ENT_QUOTES,$charset)."</option>";
    		}
    		$r.="</select>";
    	}
    	return $r;
    }
    
    //fonction de conversion de la saisie en quelque chose de compatible avec l'environnement
    function transform_input() {
    }
    
    //fonction de cr�ation de la requ�te (retourne une table temporaire)
    function make_search() {
    	global $opac_indexation_docnum_allfields;
    	
    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	
    	if (!$this->is_empty($valeur)) {
    		mysql_query("create temporary table t_s_perio (notice_id integer unsigned not null)");
    		$requete="insert into t_s_perio select distinct analysis_notice from analysis join bulletins on (analysis_bulletin=bulletin_id) join notices on (bulletin_notice=notice_id and notice_id=".$valeur[0].")";
    		mysql_query($requete);
 			mysql_query("alter table t_s_perio add primary key(notice_id)");
    	}
		return "t_s_perio"; 
    }
    
    //fonction de traduction litt�rale de la requ�te effectu�e (renvoie un tableau des termes saisis)
    function make_human_query() {
    	global $msg;
    	global $include_path;
    			
    	//R�cup�ration de la valeur de saisie 
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	
    	$tit=array();
    	if (!$this->is_empty($valeur)) {
    		$requete="select tit1 from notices where notice_id=".$valeur[0];
    		$r=mysql_query($requete);
    		$tit[0]=mysql_result($r,0,0);
    	} else $tit[0]="[vide]";
		return $tit;    
    }
    
    function make_unimarc_query() {
    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global $$valeur_;
    	$valeur=$$valeur_;
    	return "";
    }
    
    
    
	//fonction de v�rification du champ saisi ou s�lectionn�
    function is_empty($valeur) {
    	if (count($valeur)) {
    		if ($valeur[0]=="") return true;
    			else return ($valeur[0] === false);
    	} else {
    		return true;
    	}	
    }
}
?>