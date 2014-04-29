<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_searcher.class.php,v 1.4 2013-08-27 14:02:06 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/searcher.class.php");

class cms_editorial_searcher extends searcher {
	public $type_obj; 	// type �ditorial (rubrique/article)
	
	function __construct($user_query,$type_obj="article"){
		$this->type_obj = $type_obj;
		$this->field_restrict[] = array(
			'field' => 'type',
			'values' => $this->type_obj,
			'op' => "and",
			'not' => false,
		);
		parent::__construct($user_query);
	}
	
	protected function _get_search_type(){
		return "editorial_all_fields";
	}
	
	protected function _get_notices_ids(){
		if(!$this->searched){
			$query = $this->_get_search_query();
			$this->notices_ids="";
			$res = mysql_query($query);
			if($res && mysql_num_rows($res)){
				while ($row = mysql_fetch_object($res)){
					if($this->notices_ids!="") $this->notices_ids.=",";
					$this->notices_ids.=$row->num_obj;
				}
				mysql_free_result($res);
			}
			$this->searched=true;
		}
		return $this->notices_ids;
	}	
	
	protected function _get_search_query(){
		$this->_calc_query_env();
		if($this->user_query !== "*"){
			$query = $this->aq->get_query_mot("num_obj","cms_editorial_words_global_index","word","cms_editorial_fields_global_index","value",$this->field_restrict);
		}else{
			$query =" select id_".$this->type_obj." from cms_".$this->type_obj."s";//�a peut pas �tre pire avec un s
		}
		return $query;
	}

	public function get_result(){
		$this->_get_notices_ids();
		return $this->notices_ids;
	}

	public function get_sorted_result($sort = "pert",$sort_order="desc",$limit=20){
		$this->get_result();
		
		
		$query = $this->_get_pert(false,true);
		
		if($sort == 'pert'){
			$query = $this->_get_pert(false,true);
		}else{
			$query = "select uni.*,$sort from (".$query.") as uni join cms_".$this->type_obj."s on id_".$this->type_obj." = num_obj ";
		}
		
		$query.= " order by ".$sort." ".$sort_order;
		if($limit>0){
			$query.= " limit ".$limit;
		}
		$result = mysql_query($query);
		$this->result = array();
		if($result && mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$this->result[] = $row->num_obj;	
			}
		}
		return $this->result;
	}
	
	public function _get_pert($with_explnum=false, $return_query=false){
		global $opac_allow_term_troncat_search;
		global $empty_word;

		
		$with_explnum = false;
		$query_pert_explnum = "";
		$troncat = "";
		if ($opac_allow_term_troncat_search) {
			 $troncat = "%";
		}
		$terms = $this->aq->get_positive_terms_obj($this->aq->tree);
		$words = array();
		$literals = array();
		$queries = array();		
		if($this->notices_ids){
			foreach($terms as $term){
				if(!$term->literal){
					if(!in_array($term,$words))
						$words[]=$term;
				}else $literals[] = $term;
			}
			if($this->aq->input !== "*"){
				$query_pert_explnum = "";
				if(count($words)){
					$pert_query_words = "";
					$pert_query_words.= "select num_obj, sum(!!pert!!) as pert from cms_editorial_words_global_index join words on num_word = id_word where type='".$this->type_obj."' and ";
					$where = "";
					foreach($words as $term){
						if($where !="") $where.= " or ";
							$crit="word ";
						if (strpos($term->word,"*")!==false || $opac_allow_term_troncat_search){
							if (strpos($term->word,"*") === false) {
								//Si c'est un mot vide, on ne troncature pas
								if (in_array($term->word,$empty_word)===false) {
									if($term->not) $crit.= "not ";
									$crit.= "like '".addslashes($term->word.$troncat)."'";
								} else {
									if($term->not) $crit.= "! ";
									$crit.="= '".addslashes($term->word)."'";
								}
							} else {
								if($term->not) $crit.= "not ";
								$crit.= "like '".addslashes(str_replace("*","%",$term->word))."'";
							}
						}else{
							if($term->not) $crit.= "!";
							$crit.= "= '".addslashes($term->word)."'";
						}
						$where.= " ".$crit;
						$pert_query_words = str_replace("!!pert!!","((".$crit.") * pond *".$term->pound.")+!!pert!!",$pert_query_words);
					}
					$where.= (count($restrict) > 0? " and ".$this->aq->get_field_restrict($restrict,$neg_restrict) : "");
					$pert_query_words = str_replace("!!pert!!",0,$pert_query_words);
					if($all_fields && $opac_exclude_fields!= ""){
						$where.=" and code_champ not in (".$opac_exclude_fields.")";
					}
					$queries[]= $pert_query_words.$where." group by num_obj ";
				}
				if(count($literals)){
					$pert_query_literals = "select distinct num_obj, sum(!!pert!!) as pert from cms_editorial_fields_global_index where type='".$this->type_obj."' and ";
					$where = "";
					foreach($literals as $term){
						//on n'ajoute pas une clause dans le where qui parcours toute la base...
						if($where !="") $where.= " or ";
						$crit = "value ";
						if($term->not) $crit.= "not ";
						$crit.= "like '".($term->start_with == 0 ? "%":"").addslashes(str_replace("*","%",$term->word))."%'";
						$where.= " ".$crit;
						$crit = str_replace("%%","%",$crit);
						$pert_query_literals = str_replace("!!pert!!","((".$crit.") * pond *".$term->pound.")+!!pert!!",$pert_query_literals);
					}
					$where.= (count($restrict) > 0? " and ".$this->aq->get_field_restrict($restrict,$neg_restrict) : "");
					$pert_query_literals = str_replace("!!pert!!",0,$pert_query_literals);
					if($all_fields && $opac_exclude_fields!= ""){
						$where.=" and code_champ not in (".$opac_exclude_fields.")";
					}
					$queries[]= $pert_query_literals.$where." group by num_obj ";
				}
				$query = "select distinct num_obj, sum(pert) as pert from ((".implode(") union all (",$queries).")) as uni where num_obj in (".$this->notices_ids.") group by num_obj";
			}else{
				$query = "select * from(select num_obj, sum(pond) as pert from notices_fields_global_index ".(count($restrict) > 0 ? "where ".$this->aq->get_field_restrict($restrict,$neg_restrict) : "")." group by num_obj) as uni where num_obj in (".$this->notices_ids.")";
			}
		}

		if($return_query){
			return $query;	
		}else{
			$table = "search_result".md5(microtime(true));
			$rqt = "create temporary table ".$table." $query";
			$res = mysql_query($rqt)or die (mysql_error());
			mysql_query("alter table ".$table." add index i_id(num_obj)");
			return $table;
		}
	}
}