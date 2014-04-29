<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher.class.php,v 1.47 2014-02-21 16:09:29 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/analyse_query.class.php");
require_once($class_path."/search.class.php");
require_once($class_path."/filter_results.class.php");
require_once($class_path."/sort.class.php");
if($opac_search_other_function){
	require_once($include_path."/".$opac_search_other_function);
}

//classe devant piloter les recherches (quelle id�e...)
class searcher {
	protected $searched;		// bool�en pour �viter de tourner en rond...
	public $user_query;			// recherche saisie
	protected $aq;				// analyse query
	public $notices_ids;		// liste de notices sous forme de chaine
	public $typdocs;			// tableau des typdoc
	protected $pert;			// tableau de pertinance...
	protected $tri="default";	// tri � utiliser
	protected $field_restrict;	// ids des champs � utiliser (restriction)
	protected $keep_empty = 0;	// flag pour les mots vides
	public $nb_explnum=0;		// nombre de documents num�riques associ�s � la recherche...
	public $explnums=array();	// tableau contenant les documents num�riques associ�s � la recherche
	public $table_tempo;		// table temporaire contenant les r�sultats filtr�s tri�s..;

	public function __construct($user_query){
		$this->searched=false;
		$this->user_query = $user_query;
		$this->_analyse();
		$this->_delete_old_objects();
	}

	protected function _analyse(){
		global $opac_stemming_active;
		if($this->user_query){
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,$opac_stemming_active);
		}
	}


	protected function _calc_query_env(){
	//appeler avant ma g�n�ration de la requete de recherche...
	}

	protected function _get_search_query(){
		$this->_calc_query_env();
		if($this->user_query !== "*"){
			$query = $this->aq->get_query_mot("id_notice","notices_mots_global_index","word","notices_fields_global_index","value",$this->field_restrict);
			if($this->_get_typdoc_filter()!=""){
				$query = "select id_notice from ($query) as q1".$this->_get_typdoc_filter();
			}
		}else{
			$query =" select notice_id as id_notice from notices";
			if($this->_get_typdoc_filter(true)!=""){
				$query.= $this->_get_typdoc_filter(true);
			}
		}
		$this->_get_filter_by_custom_search($query);
		return $query;
	}

	protected function _get_pert($with_explnum=false, $query=false){
		if($query){
			return $this->aq->get_pert($this->notices_ids,$this->field_restrict,false,$with_explnum,$query);
		}else{
			$this->table_tempo = $this->aq->get_pert($this->notices_ids,$this->field_restrict,false,$with_explnum,$query);
		}
	}

	protected function _get_notices_ids(){
		if(!$this->searched){
			$query = $this->_get_search_query();
			$this->notices_ids="";
			$res = mysql_query($query);
			if($res){
				if(mysql_num_rows($res)){
					while ($row = mysql_fetch_object($res)){
						if($this->notices_ids!="") $this->notices_ids.=",";
						$this->notices_ids.=$row->id_notice;
					}
				}
				mysql_free_result($res);
			}
			$this->searched=true;
		}
		return $this->notices_ids;
	}

	protected function _delete_old_objects(){
		$delete= "delete from search_cache where delete_on_date < NOW()";
		mysql_query($delete);
	}

	protected function _get_user_query(){
		return $this->user_query;
	}

	protected function _get_sign($sorted=false){
		global $opac_search_other_function;
		global $typdoc;
		global $page;
		global $lang;
		global $dont_check_opac_indexation_docnum_allfields;

		$str_to_hash = session_id();
		$str_to_hash.= "&opac_view=".$_SESSION['opac_view'];
		$str_to_hash.= $_SESSION['user_code'];
		$str_to_hash.= "&lang=".$lang;
		$str_to_hash.= "&type_search=".$this->_get_search_type();
		$str_to_hash.= "&user_query=".$this->_get_user_query();
		$str_to_hash.= "&typdoc=".$typdoc;
		$str_to_hash.= "&dont_check_opac_indexation_docnum_allfields=".$dont_check_opac_indexation_docnum_allfields;
		if($opac_search_other_function){
			$str_to_hash.= "&perso=".search_other_function_get_values();
		}
		if($sorted){
			$str_to_hash.= "&tri=".$this->tri;
			$str_to_hash.= "&page=$page";
		}
		return md5($str_to_hash);
	}

	protected function _get_in_cache($sorted=false){
		$read = "select value from search_cache where object_id='".$this->_get_sign($sorted)."'";
		$res = mysql_query($read);
		if(mysql_num_rows($res)>0){
			$row = mysql_fetch_object($res);
			if(!$sorted){
				$cache = $row->value;
			}else{
				$cache = unserialize($row->value);
			}
			return $cache;
		}else {
			return false;
		}
	}

	protected function _set_in_cache($sorted=false){
		global $opac_search_cache_duration;
		if($sorted == false){
			$str_to_cache = $this->notices_ids;
		}else{
			$str_to_cache = serialize($this->result);
		}
		$insert = "insert into search_cache set object_id ='".addslashes($this->_get_sign($sorted))."', value ='".addslashes($str_to_cache)."', delete_on_date = now() + interval ".$opac_search_cache_duration." second";
		mysql_query($insert);
	}

	public function get_nb_results(){
		if(!$this->notices_ids) $this->get_result();
		if($this->notices_ids == ""){
			return 0 ;
		}else{
			return substr_count($this->notices_ids,",")+1;
		}
	}

	protected function _sort($start,$number){
		if($this->table_tempo != ""){
			$sort = new sort("notices","session");
			
			$query = $sort->appliquer_tri_from_tmp_table($this->tri,$this->table_tempo,"notice_id",$start,$number);
			$res = mysql_query($query);
			if(mysql_num_rows($res)){
				$this->result=array();
				while($row = mysql_fetch_object($res)){
					$this->result[] = $row->notice_id;
				}
			}
		}
	}

	public function get_result(){
		global $opac_search_noise_limit_type;

		$this->tri = $tri;
		$cache_result = $this->_get_in_cache();
		if($cache_result===false){
			$this->_get_notices_ids();
			$this->_filter_results();
			//Ecretage
			if($opac_search_noise_limit_type && $this->user_query != "*"){
				$limit = 0;
				//calcul pertinance
				$this->_get_pert();
				//calcul du seuil.
				
				switch(substr($opac_search_noise_limit_type,0,1)){
					// moyenne - ecart_type
					case 1 :
						$query = "select (avg(pert)-stddev_pop(pert)) as seuil from ".$this->table_tempo;
						break;
					// moyenne - % ecart_type
					case 2 :
						$ratio = substr($opac_search_noise_limit_type,2);
						$query = "select (avg(pert)-(stddev_pop(pert))*".$ratio.") as seuil from ".$this->table_tempo;
						break;
					// %max
					case 3 :
						$ratio = substr($opac_search_noise_limit_type,2);
						$query = "select (max(pert)*".$ratio.") as seuil from ".$this->table_tempo;
						break;				
				}
				$result = mysql_query($query) or die(mysql_error());
				if(mysql_num_rows($result)){
					$limit = mysql_result($result,0,0);
				}
				if($limit){
					mysql_query('create table aa_test select * from '.$this->table_tempo);
					
					$query = "delete from ".$this->table_tempo." where pert < ".$limit;
					mysql_query($query);
					$query ="select distinct notice_id from ".$this->table_tempo;
					$result = mysql_query($query) or die(mysql_error());
					
					if(mysql_num_rows($result)){
						$this->notices_ids = "";
						while($row = mysql_fetch_object($result)){
							if($this->notices_ids) $this->notices_ids.=",";
							$this->notices_ids.=$row->notice_id;
						}
					}
				}
			}
			$this->_set_in_cache();
		}else{
			$this->notices_ids = $cache_result;
		}
		return $this->notices_ids;
	}

	public function get_sorted_result($tri = "default",$start=0,$number=20){
		$this->tri = $tri;
		$cache_result = $this->_get_in_cache(true);
		if($cache_result===false){
			$cache_result = $this->_get_in_cache();
			if($cache_result!==false){
				$this->notices_ids = $cache_result;
			}else{
				$this->_get_notices_ids();
				$this->_filter_results();
				$this->_set_in_cache();
			}
			$this->_sort_result($start,$number);
			$this->_set_in_cache(true);
		}else{
			$this->result = $cache_result;
		}
		return $this->result;
	}

	public function get_typdocs(){
		if(!$this->typdocs){
			if(!$this->notices_ids){
				$this->get_result();
			}
			$this->typdocs = array();
			if($this->notices_ids != ""){
				$query = "select distinct typdoc from notices where notice_id in (".$this->notices_ids.")";
				$res = mysql_query($query);
				if(mysql_num_rows($res)){
					while ($row = mysql_fetch_object($res)){
						$this->typdocs[] = $row->typdoc;
					}
				}
			}
		}
		return $this->typdocs;
	}

	public function get_nb_explnums(){
		global $gestion_acces_active;
		global $gestion_acces_empr_notice;

		if(!$this->notices_ids){
			$this->get_result();
		}
		$this->nb_explnum = 0;
		if($this->notices_ids != ""){
			$acces_j='';
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1){
				$ac= new acces();
				$dom_2= $ac->setDomain(2);
				$join = $dom_2->getJoin($_SESSION['id_empr_session'],16,'notice_id');
			}
			if(!$join){
				$join_noti = "join notices on notice_id = explnum_notice join notice_statut on statut=id_notice_statut and ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($_SESSION["user_code"]?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
				$join_issue ="join notice_statut on notices.statut=id_notice_statut and ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($_SESSION["user_code"]?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
			}
			$query_noti = "select explnum_id from explnum $join_noti where explnum_notice in (".$this->notices_ids.")";
			$query_issue = "select explnum_id from explnum join bulletins on explnum_bulletin!= 0 and explnum_bulletin = bulletin_id join notices on notice_id = num_notice and num_notice!=0 $join_issue where notice_id in (".$this->notices_ids.")";
			$query = "select explnum_id from(".$query_noti ." union ".$query_issue.") as uni";
			$res = mysql_query($query);
			$this->nb_explnum =mysql_num_rows($res);
		}
		return $this->nb_explnum;
	}

	protected function _sort_result($start,$number){
		$this->_get_pert();
		$this->_sort($start,$number);
	}

	protected function _filter_results(){
		$this->_get_notices_ids();

		if($this->notices_ids!='') {
			$fr = new filter_results($this->notices_ids);
			$this->notices_ids = $fr->get_results();
		}
	}

	protected function _get_filter_by_custom_search(&$query){
		global $opac_search_other_function;
		$custom_query = '';
		if ($opac_search_other_function){
			$custom_query = search_other_function_clause();
			if ($custom_query) {
				$query = 'select id_notice from ('.$query.') as q2 where id_notice in ('.$custom_query.')';
			}
		}
		return;
	}

	protected function  _get_typdoc_filter($on_notice=false){
		global $typdoc;
		$return ="";
		if($on_notice){
			if($typdoc) {
				$return = " where typdoc = '".$typdoc."'";
			}else{
				$return = " where 1 ";
			}
		}else{
			if($typdoc) {
				$return = " join notices on id_notice = notice_id and typdoc = '".$typdoc."'";
			}else{
				$return = " join notices on id_notice = notice_id ";
			}
		}
		//$return = $this->_get_filter_by_custom_search($return);
		return $return;
	}

	public function get_full_query(){
		$this->get_result();
		return $this->_get_pert(false,true);
	}

	public function get_explnums($tri){ 
		global $gestion_acces_active;
		global $gestion_acces_empr_notice;
		$this->explnums = array();
		$this->get_result();
		//$table = $this->_get_pert();
		$this->_get_pert();
		//liste complete des r�sultats..;
		if($this->notices_ids != ""){ 
			$sort = new sort("notices","session");
			//$query = $sort->appliquer_tri_from_tmp_table($tri,$table,"notice_id",0,0);
			$query = $sort->appliquer_tri_from_tmp_table($tri,$this->table_tempo,"notice_id",0,0);
			//v�rification de la visibilit� des documents num�riques
			$acces_j='';
			if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1){
				$ac= new acces();
				$dom_2= $ac->setDomain(2);
				$join = $dom_2->getJoin($_SESSION['id_empr_session'],16,'notice_id');
			}
			if(!$join){
				$join ="join notices on ".$sort->table_tri_tempo.".notice_id = notices.notice_id join notice_statut on notices.statut=id_notice_statut and ((explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($_SESSION["user_code"]?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"").")";
			}
			$explnum_noti = "select explnum_id,".$sort->table_tri_tempo.".* from explnum join ".$sort->table_tri_tempo." on explnum_notice!=0 and explnum_notice = ".$sort->table_tri_tempo.".notice_id $join";
			$rqt = "create temporary table explnum_list $explnum_noti";
			mysql_query($rqt);
			$explnum_issue = "select explnum_id,".$sort->table_tri_tempo.".* from explnum join bulletins on explnum_bulletin!=0 and bulletin_id = explnum_bulletin join ".$sort->table_tri_tempo." on num_notice != 0 and num_notice = ".$sort->table_tri_tempo.".notice_id $join";
			$rqt = "insert ignore into explnum_list $explnum_issue";
			mysql_query($rqt);
			mysql_query("alter table explnum_list order by ".$sort->get_order_by($tri));
			$rqt = "select explnum_id from explnum_list order by ".$sort->get_order_by($tri);
			$res = mysql_query($rqt);
			//si get_order_by renvoit une valeur nulle, on ne s'occupe pas du tri. 
			if (!$res) {
				$rqt = "select explnum_id from explnum_list";
				$res = mysql_query($rqt);
			}
			if ($res) {
				if(mysql_num_rows($res)){
					while($row = mysql_fetch_object($res)){
						$this->explnums[] = $row->explnum_id;
					}
				}
			}
		}
		return $this->explnums;
	}
}

class searcher_all_fields extends searcher{
	protected $members_explnum_noti;	// �l�ments de la requete sur les docnums de notices
	protected $members_explnum_bull;	// �l�ments de la requete sur les docnums de bulletins
	protected $aq_wew;					// modelisation de la recherche en conservant les mots vides

	public function __construct($user_query){
		global $opac_stemming_active;
		global $opac_search_all_keep_empty_words;
		
		parent::__construct($user_query);
		if($this->user_query && $opac_search_all_keep_empty_words){
			$this->aq_wew= new analyse_query($this->user_query,0,0,1,1,$opac_stemming_active);
		}
		//on va l'utiliser pour g�rer la recherche "tous les champs sauf les autorit�s" et "toutes les autorit�s"
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => array(18,19,20,21,23,24,25,26),
			'op' => "or",
			'not' => false
		);
	}

	protected function _get_search_type(){
		return "all_fields";
	}

	public function get_full_query(){
		$this->get_result();
		return $this->_get_pert(true);
	}

	// sp�cialement pour la recherche tous les champs (histoire de mots vides et d'autorit�s...)
	protected function _get_all_fields_search_query(){//Je vois pas comment �a marche...
		global $lang;
		global $opac_search_all_keep_empty_words ;

		//on applique la recherche � "tous les champs sauf les autorit�s"
		if($opac_search_all_keep_empty_words && count($this->aq->tree)!=count($this->aq_wew->tree)){
			$restrict = $this->field_restrict;
// 			$restrict[] = array(
// 				'field' => "lang",
// 				'values' => array("",$lang),
// 				'op' => "and",
// 				'not' => true
// 			);
			$query_without_authories = $this->aq->get_query_mot("id_notice","notices_mots_global_index","word","notices_fields_global_index","value",$restrict,true,true);
			$restrict = $this->field_restrict;
// 			$restrict[] = array(
// 				'field' => "lang",
// 				'values' => array("",$lang),
// 				'op' => "and",
// 				'not' => false
// 			);
			$query_authorities = $this->aq_wew->get_query_mot("id_notice","notices_mots_global_index","word","notices_fields_global_index","value",$restrict,false);
			$query = "select distinct id_notice from (($query_without_authories) union ($query_authorities)) as q1";
			if($this->_get_typdoc_filter()!=""){
				$query.= $this->_get_typdoc_filter();
			}
		}else{
			$restrict = array();
// 			$restrict[] = array(
// 				'field' => "lang",
// 				'values' => array("",$lang),
// 				'op' => "and",
// 				'not' => false
// 			);
			$query = $this->aq->get_query_mot("id_notice","notices_mots_global_index","word","notices_fields_global_index","value",$restrict,false,true);
			if($this->_get_typdoc_filter()!=""){
				$query= "select distinct id_notice from ($query) as q1". $this->_get_typdoc_filter();
			}
		}
		return $query;
	}

	protected function _get_pert($get_query=false){
		global $opac_indexation_docnum_allfields;
		global $opac_search_all_keep_empty_words ;
		
		$with_explnum = false;
		if($opac_indexation_docnum_allfields){
			$with_explnum = true;
		}
		if($opac_search_all_keep_empty_words && $this->user_query !== "*" && (count($this->aq->tree) != count($this->aq_wew->tree))){
			$without_empty = $this->aq->get_pert($this->notices_ids,$this->field_restrict,true,$with_explnum,true,true);
			$with_empty = $this->aq_wew->get_pert($this->notices_ids,$this->field_restrict,false,$with_explnum,true,true);
			$query = "select notice_id, max(pert) as pert from (($without_empty) union all($with_empty))as q1 group by notice_id";
			if($get_query){
				return $query;
			}else{
				$this->table_tempo = "seach_result".md5(microtime(true));
				$res = mysql_query("create temporary table ".$this->table_tempo." ".$query);
				mysql_query("alter table ".$this->table_tempo." add index i_id(notice_id)");
			}
		}else{
			if($get_query){
				return $this->aq->get_pert($this->notices_ids,array(),false,$with_explnum,true,true);
			}
			$this->table_tempo = $this->aq->get_pert($this->notices_ids,array(),false,$with_explnum,false,true);
		}
	}

	// la surcharge de la fonction
	protected function _get_search_query(){
		global $opac_indexation_docnum_allfields,$dont_check_opac_indexation_docnum_allfields;
		$this->_calc_query_env();
		if($this->user_query !== "*"){
			if($opac_indexation_docnum_allfields && !$dont_check_opac_indexation_docnum_allfields){
				//si la recherche dans les documents num�riques est incluse dans la recherche tous les champs, on doit le prendre en compte
				$this->_get_explnum_members();
				$query_noti = $this->_get_all_fields_search_query();
				$query_explnum_noti="select distinct explnum_notice as id_notice from explnum ".$this->_get_explnum_filter("notice","explnum_notice")." ".$this->_get_explnum_where()." and explnum_notice !=0 and explnum_bulletin=0 ";//.$this->_get_explnum_end("notice");
				$query_explnum_bull="select distinct num_notice as id_notice from explnum join bulletins on num_notice!= 0 and explnum_bulletin = bulletin_id ".$this->_get_explnum_filter("bulletin","num_notice")." ".$this->_get_explnum_where()." and explnum_bulletin !=0 and explnum_notice=0 ";//.$this->_get_explnum_end();
				if($this->_get_typdoc_filter()!=""){
					$query_explnum_noti= "select distinct id_notice from ($query_explnum_noti) as q2 ".$this->_get_typdoc_filter();
					$query_explnum_bull= "select distinct id_notice from ($query_explnum_bull) as q3 ". $this->_get_typdoc_filter();
				}
				$query = "select distinct id_notice from (($query_noti) union ($query_explnum_noti) union ($query_explnum_bull))as uni ";
			}else{
				$query = $this->_get_all_fields_search_query();
			}
		}else{
			$query = "select distinct notice_id as id_notice from notices";
			if($this->_get_typdoc_filter(true)!=""){
				$query.= $this->_get_typdoc_filter(true);
			}
		}
		$this->_get_filter_by_custom_search($query);
		return $query;
	}

	protected function _get_explnum_end($type){
		if($type == "notice"){
			return 	$this->members_explnum_noti['post'];
		}else{
			return 	$this->members_explnum_bull['post'];
		}
	}

	protected function _get_explnum_members(){
		$this->members_explnum_noti = $this->aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","explnum_notice","",0,0,true);
		$this->members_explnum_bull = $this->aq->get_query_members("explnum","explnum_index_wew","explnum_index_sew","id_notice","",0,0,true);
	}

	protected function _get_explnum_pert(){
		return $this->members_explnum_noti['select']." as pert";
	}

	protected function _get_explnum_where(){
		$where="where ((".$this->members_explnum_noti['where'].")) ";
		if($this->view_restrict) $where.=" and ".$this->view_restrict;
		return $where;
	}

	protected function _get_explnum_filter($type="notice",$field){
		global $gestion_acces_active;
		global $gestion_acces_empr_notice;
		if ($gestion_acces_active==1 && $gestion_acces_empr_notice==1) {
			$ac= new acces();
			$dom_2= $ac->setDomain(2);
			$join = $dom_2->getJoin($_SESSION['id_empr_session'],16,$field);
		}
		if(!$join){
			switch($type){
				case "notice" :
					$join="join notices on explnum_notice = notice_id join notice_statut on notices.statut= id_notice_statut and (explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($_SESSION["user_code"]?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"");
					break;
				case "bulletin" :
					$join="join notices on explnum_notice = bulletins.num_notice join notice_statut on notices.statut= id_notice_statut and (explnum_visible_opac=1 and explnum_visible_opac_abon=0)".($_SESSION["user_code"]?" or (explnum_visible_opac_abon=1 and explnum_visible_opac=1)":"");
					break;
			}
		}
		return $join;
	}
}

class searcher_title extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => array(1,2,3,4,6,23),
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "title";
	}
}

class searcher_keywords extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 17,
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "keywords";
	}
}

class searcher_notes extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => array(12,13,14),
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "notes";
	}
}

class searcher_abstract extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 14,
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "abstract";
	}
}

class searcher_general_note extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 12,
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "general_note";
	}
}

class searcher_contents_note extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 13,
			'op' => "and",
			'not' => false
		);
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "contents_note";
	}
}

class searcher_extended extends searcher{
	protected $serialized_query;	// recherche s�rialis�e
	public $table;				// table tempo de la multi

	public function __construct($serialized_query=""){
		$this->serialized_query = $serialized_query;
		parent::__construct("");
	}

	protected function _get_search_type(){
		return "extended";
	}

	protected function _get_user_query(){
		return search::serialize_search();
	}

	protected function _get_search_query(){
		global $es,$msg;
		if(!is_object($es)) $es = new search();
		if($this->serialized_query){
			$es->unserialize_search($this->serialized_query);
		}else{
			global $search;
    		//V�rification des champs vides
    		for ($i=0; $i<count($search); $i++) {
    			if($i==0){//On supprime le premier op�rateur inter (il est renseign� pour les recherches pr�d�finies avec plusieurs champs et une recherche avec le premier champ vide
    				$inter="inter_".$i."_".$search[$i];
    				global $$inter;
    				$$inter="";
    			}
	    		$op="op_".$i."_".$search[$i];
    			global $$op;
    			$field_="field_".$i."_".$search[$i];
	   			global $$field_;
	   			$field=$$field_;
	   			$s=explode("_",$search[$i]);
	   			if ($s[0]=="f") {
		    		$champ=$es->fixedfields[$s[1]]["TITLE"];
	   			} elseif ($s[0]=="s") {
		    		$champ=$es->specialfields[$s[1]]["TITLE"];
	   			} else {
		    		$champ=$es->pp->t_fields[$s[1]]["TITRE"];
	   			}
	   			if (((string)$field[0]=="") && (!$es->op_empty[$$op])) {
		    		$search_error_message=sprintf($msg["extended_empty_field"],$champ);
	   				$flag=true;
					break;
	   			}
	   		}
    	}
    	$es->remove_forbidden_fields();
    	$this->table = $es->make_search();
		return "select notice_id as id_notice from ".$this->table;
	}

	protected function _get_pert($with_explnum=false, $query=false){
		if(!$this->notices_ids) return;
		$this->table_tempo = "search_result".md5(microtime(true));
		$rqt = "create temporary table ".$this->table_tempo." select notice_id,100 as pert from notices where notice_id in(".$this->notices_ids.")";
		$res = mysql_query($rqt);
		mysql_query("alter table ".$this->table_tempo." add index i_id(notice_id)");
	}
	
	public function get_result(){
		$this->tri = $tri;
		$cache_result = $this->_get_in_cache();
		if($cache_result===false){
			$this->_get_notices_ids();
			$this->_filter_results();
			$this->_set_in_cache();
		}else{
			$this->notices_ids = $cache_result;
			$this->table = "search_result".md5(microtime(true));
			$rqt = "create temporary table ".$this->table." engine=memory select notice_id from notices where notice_id in(".$this->notices_ids.")";
			$res = mysql_query($rqt);
			mysql_query("alter table ".$this->table." add index i_id(notice_id)");			
		}
		return $this->notices_ids;
	}
	
}

class searcher_authors extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => array(27,28,29),
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "authors";
	}
	
	protected function _analyse(){
		if($this->user_query){
			//on veut pas du stemming pour les auteurs
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,false);
		}
	}
}

class searcher_publishers extends searcher{

	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 19,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "publishers";
	}
	
	protected function _analyse(){
		if($this->user_query){
			//on veut pas du stemming pour les �diteurs
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,false);
		}
	}
}

class searcher_indexint extends searcher{
	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 20,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "indexint";
	}
}

class searcher_collection extends searcher{
	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 21,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "collection";
	}
	
	protected function _analyse(){
		if($this->user_query){
			//on veut pas du stemming pour les collections
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,false);
		}
	}
}

class searcher_subcollection extends searcher{
	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 24,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "subcollection";
	}
	
	protected function _analyse(){
		if($this->user_query){
			//on veut pas du stemming pour les sous-collections
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,false);
		}
	}
}

class searcher_serie extends searcher{
	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 23,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "serie";
	}
	
	protected function _analyse(){
		if($this->user_query){
			//on veut pas du stemming pour les s�ries
			$this->aq= new analyse_query($this->user_query,0,0,1,$this->keep_empty,false);
		}
	}
}

class searcher_uniform_title extends searcher{
	public function __construct($user_query){
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 26,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "uniform_title";
	}
}

class searcher_categorie extends searcher{
	public function __construct($user_query){
		global $lang;
		$this->field_restrict=array();
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 25,
			'op' => "and",
			'not' => false
		);
		$this->field_restrict[]= array(
			'field' => "lang",
			'values' => $lang,
			'op' => "and",
			'not' => false
		);
		$this->keep_empty=1;
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "categorie";
	}
}

class searcher_pfield extends searcher{

	public function __construct($user_query,$id=0){
		$this->field_restrict=array();
		$sub=array();
		if($id>0){
			$sub[]=array(
				'sub_field' => "code_ss_champ",
				'values' => $id,
				'op' => "and",
				'not' => false
			);
		}
		$this->field_restrict[]= array(
			'field' => "code_champ",
			'values' => 100,
			'op' => "and",
			'not' => false,
			'sub'=> $sub
		);
			
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "pfield";
	}
}

class searcher_generic extends searcher{

	public function __construct($user_query,$fields=""){
		$this->fields_list_gen=$fields;
		$this->field_restrict = array();
		$tmp=explode(",",$fields);//On r�cup�re la liste des champs
		if(count($tmp)){
			if(!preg_match("/;/",$fields)){//Si je n'ai pas de ; alors je n'ai qu'une liste de champ
				if(count($tmp) == 1){//Je n'ai qu'un champ
					$this->field_restrict[]= array(
						'field' => "code_champ",
						'values' => $fields,
						'op' => "and",
						'not' => false
					);
				}else{
					$this->field_restrict[]= array(
						'field' => "code_champ",
						'values' => $tmp,
						'op' => "and",
						'not' => false
					);
				}
			}else{
				foreach ( $tmp as $value ) {
	       			if(preg_match("/^([0-9]+?);([0-9;]+)$/",$value,$matches)){
	       				//J'ai un ou des sous champs
	       				if(preg_match("/;/",$matches[2])){
	       					$list_sub=explode(";",$matches[2]);
	       				}else{
	       					$list_sub=$matches[2];
	       				}
	       				$sub=array();
	       				$sub[]=array(
							'sub_field' => "code_ss_champ",
							'values' => $list_sub,//La liste des sous champs
							'op' => "and",
							'not' => false
						);
	       				$this->field_restrict[]= array(
							'field' => "code_champ",
							'values' => $matches[1],//Le champ
							'op' => "or",
							'not' => false,
							'sub'=> $sub
						);
	       			}else{
	       				$this->field_restrict[]= array(
							'field' => "code_champ",
							'values' => $value,
							'op' => "or",
							'not' => false
						);
	       			}
				}
			}
		}
		parent::__construct($user_query);
	}

	protected function _get_search_type(){
		return "generic_".$this->fields_list_gen;
	}
}