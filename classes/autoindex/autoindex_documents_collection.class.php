<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: autoindex_documents_collection.class.php,v 1.3 2014-02-27 17:12:40 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class autoindex_documents_collection {
	

	/**
	 * Type d'index (permet d'identifier par exemple les tables d'index � utiliser)
	 * @access protected
	 */
	protected $index_type = 'notice';
	
	/**
	 * Codes champ, codes sous-champ � utiliser
	 * ex. pour notices
	 * $this->used_fields =array(
	 *   array(
	 *     'field'=>1,
	 *     'subfields' => array(
	 *       2,3
	 *     )
	 *   )
	 * );
	 * @access protected
	 */
	protected $used_fields = array();
	
	/**
	 * nombre de documents dans le fonds
	 */
	private $d = NULL;
	
	
	public function __construct($used_fields=array()) {
		$this->used_fields = $used_fields;
	}
	
	
	/**
	 * Calcul de la fr�quence inverse d'un mot par rapport au fonds 
	 * (voir http://fr.wikipedia.org/wiki/TF-IDF)
	 *
	 * @param autoindex_word word
	
	 * @return float
	 * @access public
	 */
	public function calc_inverse_frequency($word) {
		
		global $dbh;
		
		$dt = 0;		// nb de documents du fonds dans lesquels apparait le mot $word		
		$idf = 0;		// |$d|/|$dt| fr�quence inverse de documents contenant le mot $word

		if (is_object($word)) {
			
			if ($word->id) {
				//mot d�j� r�f�renc�
			
				// calcul du nb total de documents dans le fonds
				$this->calc_nb_docs();
				
				// calcul du nb de documents du fonds dans lesquels apparait le mot $word	
				$q_where = '0';
				if (is_array($this->used_fields) && count($this->used_fields)) {
					
					$q_fields=array();
					foreach ($this->used_fields as $kf=>$fields) {
						$q_fields[$kf]='';
						if($fields['field']) {
							$q_fields[$kf] = "(code_champ='".$fields['field']."'";
							if (is_array($fields['subfields']) && count($fields['subfields']) > 1) {
								$q_subfields=implode(',',$fields['subfields']);
								$q_fields[$kf].= " and code_ss_champ in (".$q_subfields.")";
							} else {
								if(!count($fields['subfields'])) {
									$q_fields[$kf].=  " and code_ss_champ=0 " ;
								} else {
									$q_fields[$kf].=  " and code_ss_champ=".$fields['subfields'][0]." " ;
								}
							}
							$q_fields[$kf].= ')';
							
						}
					}
					if (count($q_fields)) {
						$q_where = '( '.implode(' or ', $q_fields).' )';
					} 
					$q_dt = "select count(distinct id_notice) from notices_mots_global_index where num_word=".addslashes($word->id)." and ".$q_where;
//echo $q_dt."\r\n";
					$r_dt = mysql_query($q_dt, $dbh);
					if (mysql_num_rows($r_dt)) {
						$dt = mysql_result($r_dt,0,0);
					}
					//rien trouv� dans le fonds et le mot sans langue existe
					if(!$dt && $word->wo_lang_id) {
						$q_dt = "select count(distinct id_notice) from notices_mots_global_index where num_word=".addslashes($word->wo_lang_id)." and ".$q_where;
//echo $q_dt."\r\n";
						$r_dt = mysql_query($q_dt, $dbh);
						if (mysql_num_rows($r_dt)) {
							$dt = mysql_result($r_dt,0,0);
						}
					}
						
					if($dt) {
						$idf = $this->d / $dt;
					} else {
						$idf = $this->d*1000;
					}
				}
							
				
			} else {
				//mot non r�f�renc�	
				$idf = $this->d*1000;
				
			}
		} 
		return $idf;

	}
	
	
	/**
	 *calcul du nb total de documents dans le fonds 
	 */	
	public function calc_nb_docs() {
		
		global $dbh;
		
		if (is_null($this->d)) {
			$q_d = "select count(*) from notices";
			$r_d = mysql_query($q_d, $dbh);
			if (mysql_num_rows($r_d)) {
				$this->d = mysql_result($r_d,0,0);
			} else {
				$this->d = 0;
			}
		}
	}
	
	
	/**
	 * Calcule la fr�quence inverse d'un stem par rapport au fonds
	 * (voir http://fr.wikipedia.org/wiki/TF-IDF)
	 *
	 * @param string stem
	
	 * @return float
	 * @access public
	 */
	public function calc_stem_inverse_frequency($stem) {
	
		global $dbh;
	
		$dt = 0;		// nb de documents du fonds dans lesquels apparait le stem $stem
		$idf = 0;		// |$d|/|$dt| fr�quence inverse de documents contenant le stem $stem
	
		// calcul du nb total de documents dans le fonds
		$this->calc_nb_docs();
		
		// calcul du nb de documents du fonds dans lesquels apparait le stem $stem
		$q_where = '0';
		if (is_array($this->used_fields) && count($this->used_fields)) {
				
			$q_fields=array();
			foreach ($this->used_fields as $kf=>$fields) {
				$q_fields[$kf]='';
				if($fields['field']) {
					$q_fields[$kf] = "(code_champ='".$fields['field']."'";
					if (is_array($fields['subfields']) && count($fields['subfields']) > 1) {
						$q_subfields=implode(',',$fields['subfields']);
						$q_fields[$kf].= " and code_ss_champ in (".$q_subfields.")";
					} else {
						if(!count($fields['subfields'])) {
							$q_fields[$kf].=  " and code_ss_champ=0 " ;
						} else {
							$q_fields[$kf].=  " and code_ss_champ=".$fields['subfields'][0]." " ;
						}
					}
					$q_fields[$kf].= ')';
						
				}
			}
			if (count($q_fields)) {
				$q_where = '( '.implode(' or ', $q_fields).' )';
			}
			
			$q_dt = "select count(distinct id_notice) from words join notices_mots_global_index on num_word=id_word where stem='".addslashes($stem->label)."' and lang='".$stem->lang."' "." and ".$q_where;
// echo $q_dt."\r\n";
			$r_dt = mysql_query($q_dt, $dbh);
			if (mysql_num_rows($r_dt)) {
				$dt = mysql_result($r_dt,0,0);
			}
			//rien trouv� dans le fonds, essai avec le stem sans langue
			if(!$dt) {
				$q_dt = "select count(distinct id_notice) from words join notices_mots_global_index on num_word=id_word where stem='".addslashes($stem->label)."' and lang='' "." and ".$q_where;
// echo $q_dt."\r\n";
				$r_dt = mysql_query($q_dt, $dbh);
				if (mysql_num_rows($r_dt)) {
					$dt = mysql_result($r_dt,0,0);
				}
			}

			if($dt) {
				$idf = $this->d / $dt;
			} else {
				$idf = $this->d*1000;
			}
		}
			
		return $idf;
	
	}
	
	
	
	
}
