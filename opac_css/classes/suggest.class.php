<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: suggest.class.php,v 1.7 2014-02-17 13:41:18 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
	die("no access");

require_once ($include_path."/misc.inc.php");
require_once($class_path."/double_metaphone.class.php");
require_once($include_path."/parser.inc.php");

/**
 * Classe de suggestions depuis saisie en opac 
 */
class suggest {

	// ---------------------------------------------------------------------------------------------------
	//  propri�t�s de la classe
	// ---------------------------------------------------------------------------------------------------

	var $inputString;       		// chaine en entr�e
	var $cleanInputString;       	// chaine en entr�e nettoy�e
	var $searchIndex;       		// table d'indexation
	var $searchValueFields;       	// table des contenus de champs
	var $excludeNotes;       		// exclure les notes des recherches
	var $arrayWords;				// liste des mots en entr�e
	var $arraySimilars;				// liste de tous les approchants pond�r�s
	var $arraySimilarsByWord;		// liste de tous les approchants par mot
	var $arrayPermutations;			// liste des diff�rentes permutations d'approchants
	var $permutationCode;			// tableau utilis� pour les permutations
	var $permutationPos;			// position utilis�e pour les permutations
	var $maxSuggestions;			// nombre de suggestions maximum
	var $maxResultsByPermutation;	// nombre de r�sultats maximum par permutation
	var $tbChampBase;				// correspondance champs en base/libell�
	var $arrayResults;				// tableau des r�sultats class�s

// ---------------------------------------------------------------------------------------------------
//  suggest($string) : constructeur
// ---------------------------------------------------------------------------------------------------
	function suggest($string,$searchTable='notices',$maxSuggest=10,$maxResultsByPermutation=500,$excludeNotes=true) {
		$this->loadTbChampBase();
		$this->maxSuggestions = $maxSuggest;
		$this->arrayResults = array();
		if ($searchTable == 'notices') {
			$this->searchIndex = 'notices_mots_global_index';
			$this->searchValueFields = 'notices_fields_global_index';
		}
		$this->excludeNotes = $excludeNotes;
		$this->maxResultsByPermutation = $maxResultsByPermutation;
		if($tmp = trim($string)){
			$this->inputString = $tmp;
			$this->findWords();
		}
	}
	
// ---------------------------------------------------------------------------------------------------
//  loadTbChampBase() : correspondance champs base / libell�s OPAC
// ---------------------------------------------------------------------------------------------------
	function loadTbChampBase() {
		global $champ_base,$include_path,$msg;
	
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
		if(!count($this->tbChampBase)) {
			foreach($champ_base["FIELD"] as $k=>$v){
				if(isset($v["TABLE"][0]["TABLEFIELD"])){
					foreach($v["TABLE"][0]["TABLEFIELD"] as $k2=>$v2){
						if(isset($v2["NAME"])){
							if(isset($v2["ID"])){
								$this->tbChampBase[(int)$v["ID"]."_".(int)$v2["ID"]]=$msg[$v2["NAME"]];
							}else{
								$this->tbChampBase[(int)$v["ID"]."_0"]=$msg[$v2["NAME"]];
							}
						}else{
							if(isset($v2["ID"])){
								$this->tbChampBase[(int)$v["ID"]."_".(int)$v2["ID"]]=$msg[$v["NAME"]];
							}else{
								$this->tbChampBase[(int)$v["ID"]."_0"]=$msg[$v["NAME"]];
							}
						}
						if(isset($v2["ID"])){
							if(!trim($this->tbChampBase[(int)$v["ID"]."_".(int)$v2["ID"]])){
								$this->tbChampBase[(int)$v["ID"]."_".(int)$v2["ID"]]="___champ sans libell�___";
							}
						}else{
							if(!trim($this->tbChampBase[(int)$v["ID"]."_0"])){
								$this->tbChampBase[(int)$v["ID"]."_0"]="___champ sans libell�___";
							}
						}
					}
				}
				if(isset($v["ISBD"])){
					$tmp=$v["ISBD"][0];
					$this->tbChampBase[(int)$v["ID"]."_".(int)$tmp["ID"]]=$msg[$tmp["NAME"]."_".$tmp["CLASS_NAME"]];
					if(!trim($this->tbChampBase[(int)$v["ID"]."_".(int)$tmp["ID"]])){
						$this->tbChampBase[(int)$v["ID"]."_".(int)$tmp["ID"]]="___champ sans libell�___";
					}
				}
			}
		}
	}
	
// ---------------------------------------------------------------------------------------------------
//  findWords() : nettoie et trouve tous les mots de la chaine saisie
// ---------------------------------------------------------------------------------------------------
	function findWords() {
		$this->cleanInputString = $this->cleanString($this->inputString);
		$this->arrayWords = str_word_count($this->cleanInputString,1);
		if(count($this->arrayWords)){
			$this->findAndPermuteSimilars();
		}
	}
	
// ---------------------------------------------------------------------------------------------------
//  findSimilars() : trouve les approchants pond�r�s depuis le tableau de mots
//	la pond�ration est invers�e : plus "pond" est faible, plus le mot est pertinent
// ---------------------------------------------------------------------------------------------------
	function findAndPermuteSimilars() {
		global $dbh,$lang;
		
		if(count($this->arrayWords)){
			foreach($this->arrayWords as $key=>$word){
				$dmeta = new DoubleMetaPhone($word);
				$distMax=2;
				switch(count($this->arrayWords)){
					case 1 : $maxSimilars=10;
						break;
					case 2 : $maxSimilars=5;
						break;
					case 3 : $maxSimilars=3;
						break;
					case 4 : $maxSimilars=2;
						break;
					default : $maxSimilars=1;
						break;
				}		
				$query = "SELECT DISTINCT id_word, word
								FROM words
								WHERE word LIKE '".addslashes($word)."%'
								AND lang IN ('','".$lang."')
								AND id_word IN (
									SELECT DISTINCT num_word FROM ".$this->searchIndex."
								)
						UNION
							SELECT * FROM
							(
							SELECT DISTINCT id_word, word
								FROM words
								WHERE levenshtein('".$dmeta->primary." ".$dmeta->secondary."',double_metaphone) < ".$distMax."
								AND lang IN ('','".$lang."')
								AND id_word IN (
									SELECT DISTINCT num_word FROM ".$this->searchIndex."
								)
								ORDER BY levenshtein('".addslashes($word)."',word) ASC
							) as R1
						LIMIT ".$maxSimilars;
				$res=mysql_query($query,$dbh) or die();
				$count=1;
				$nbRows=mysql_num_rows($res);
				while($row=mysql_fetch_object($res)){
					$this->arraySimilarsByWord[$key][] = $row->id_word;
					$this->arraySimilars[$row->id_word]["word"] = $row->word;
					$this->arraySimilars[$row->id_word]["pond"] = $count/$nbRows;
					$count++;
				}
			}
			if(count($this->arraySimilarsByWord)){
				$this->permutationCode=array();
				$this->permutationPos=0;
				$this->findPermutations($this->arraySimilarsByWord);
			}
			if(count($this->arrayPermutations)){
				$this->findAndOrderPermutationInDatabase();
			}
		}
	}
	
// ---------------------------------------------------------------------------------------------------
//  listUniqueSimilars() : renvoie un tableau des suggestions uniques
// ---------------------------------------------------------------------------------------------------
	function listUniqueSimilars(){
		$arrayReturn = array();
		if (count($this->arraySimilars)) {
			foreach ($this->arraySimilars as $value) {
				$arrayReturn[] = $value["word"];
			}
		}
		$arrayReturn = array_unique($arrayReturn);
		
		return $arrayReturn;
	}	

// ---------------------------------------------------------------------------------------------------
//  findPermutations() : trouve les permutations du tableau en entr�e
//	attention : fonction r�cursive (d'o� le param�tre en entr�e, et les deux propri�t�s de classe)
// ---------------------------------------------------------------------------------------------------
	function findPermutations($array) {	
		if(count($array)) {
			for($i=0; $i<count($array[0]); $i++) {				
				$tmpArray = $array;
				$this->permutationCode[$this->permutationPos] = $array[0][$i];
				$tarr = array_shift($tmpArray);
				$this->permutationPos++;
				$this->findPermutations($tmpArray);
			}
		} else {
			asort($this->permutationCode);
			$tmpValeur=implode(",",$this->permutationCode);
			if(!is_array($this->arrayPermutations) || !in_array($tmpValeur,$this->arrayPermutations)){
				$this->arrayPermutations[]=$tmpValeur;
			}
		}
		$this->permutationPos--;
	}
	
// ---------------------------------------------------------------------------------------------------
//  arrayResultsSort($sort) : trie la propri�t� arrayResultFinal selon la cl� donn�e 
// ---------------------------------------------------------------------------------------------------
	function arrayResultsSort($sort){
		$sort_values=array();
		for ($i = 0; $i < sizeof($this->arrayResults); $i++) {
			$sort_values[$i] = $this->arrayResults[$i][$sort];
		}
		asort ($sort_values);
		reset ($sort_values);

		while (list ($arr_key, $arr_val) = each ($sort_values)) {
			$sorted_arr[] = $this->arrayResults[$arr_key];
		}
		$this->arrayResults = $sorted_arr;
	}
	
// ---------------------------------------------------------------------------------------------------
//  findAndOrderPermutationInDatabase() : trouve les champs de notice o� les permutations apparaissent
//	class�s par distance max des deux termes les plus �loign�s (ou position si un seul terme) pond�r�e
//	par nombre d'occurrences en regroupement
// ---------------------------------------------------------------------------------------------------
	function findAndOrderPermutationInDatabase() {
		global $dbh;
		
		if(count($this->arrayPermutations)){
			$arrayResults=array();
			foreach($this->arrayPermutations as $permutation){
				$itemPermutation=explode(",",$permutation);
				//Cas particulier si un seul mot
				if(count($itemPermutation)==1){
					$query="SELECT DISTINCT id_notice, code_champ, code_ss_champ, field_position 
							FROM ".$this->searchIndex." 
							WHERE num_word=".$itemPermutation[0];
					if($this->excludeNotes){
						$query.=" AND code_champ NOT IN (12,13,14)";
					}
					$query.=" ORDER BY 4 LIMIT 0,".$this->maxResultsByPermutation;
					$res=mysql_query($query,$dbh) or die();
					if(mysql_num_rows($res)){				
						while($row=mysql_fetch_object($res)){
							$key=$row->id_notice."_".$row->code_champ."_".$row->code_ss_champ."_".$row->field_position."_".$itemPermutation[0];
							$arrayResults[$key]=$row->field_position*$this->arraySimilars[$itemPermutation[0]]["pond"];
						}
					}
				}else{
					$ponderation=0;
					foreach($itemPermutation as $keyItem=>$idWord){
						$ponderation+=$this->arraySimilars[$idWord]["pond"];
						if(!$keyItem){
							$select="DISTINCT n.id_notice, n.code_champ, n.code_ss_champ, n.field_position, 
								(
									SELECT MAX(field_position)-MIN(field_position) 
									FROM ".$this->searchIndex." 
									WHERE id_notice=n.id_notice 
									AND code_champ=n.code_champ 
									AND code_ss_champ=n.code_ss_champ 
									AND num_word IN (".$permutation.")";
							if($this->excludeNotes){
								$select.=" AND code_champ NOT IN (12,13,14)";
							}
							$select.=") as distance";
							$from=$this->searchIndex." n";
							$where="n.num_word=".$idWord;
							if($this->excludeNotes){
								$where.=" AND n.code_champ NOT IN (12,13,14)";
							}
						}else{
							$from.=" JOIN ".$this->searchIndex." n".$keyItem." 
									ON n.id_notice=n".$keyItem.".id_notice 
									AND n.code_champ=n".$keyItem.".code_champ 
									AND n.code_ss_champ=n".$keyItem.".code_ss_champ";
							$where.=" AND n".$keyItem.".num_word=".$idWord;
						}
					}
					$query="SELECT ".$select." FROM ".$from." WHERE ".$where;
					$res=mysql_query($query,$dbh) or die();
					if(mysql_num_rows($res)){
						while($row=mysql_fetch_object($res)){
							$key=$row->id_notice."_".$row->code_champ."_".$row->code_ss_champ."_".$row->field_position."_".implode("_",$itemPermutation);
							$arrayResults[$key]=$row->distance*$ponderation;
						}
					}
				}
			}
			asort($arrayResults);
			//Regroupement par valeur/champ
			foreach($arrayResults as $key=>$value){
				$tmpArray=explode("_",$key);
				$query="SELECT value 
						FROM ".$this->searchValueFields."  
						WHERE id_notice=".$tmpArray[0]." 
						AND code_champ=".$tmpArray[1]." 
						AND code_ss_champ=".$tmpArray[2];
				$res=mysql_query($query,$dbh) or die();
				$row=mysql_fetch_object($res);
				$creeElement=true;
				if(count($this->arrayResults)){
					foreach($this->arrayResults as $key2=>$value2){
						if(($value2["field_content"]==$row->value) && ($value2["field_subfield"]==$tmpArray[1]."_".$tmpArray[2])){
							$this->arrayResults[$key2]["occurrences"]++;
							$creeElement=false;
							break;
						}
					}
				}
				if($creeElement){
					$tmpArrayTmp=array();
					$tmpArrayTmp["field_content"]=$row->value;
					$tmpArrayTmp["field_clean_content"]=$row->value;
					$tmpArrayTmp["field_subfield"]=$tmpArray[1]."_".$tmpArray[2];
					$tmpArrayTmp["ratio"]=$value;
					$tmpArrayTmp["occurrences"]=1;
					$this->arrayResults[]=$tmpArrayTmp;
				}
			}
			//Calcul des scores
			foreach($this->arrayResults as $key=>$value){
				$this->arrayResults[$key]["score"]=$value["ratio"]/$value["occurrences"];
			}
			//Classement des r�sultats
			$this->arrayResultsSort('score');
			//On limite et on g�re l'affichage
			$search=array();
			foreach($this->arraySimilars as $similar){
				$search[]=$similar["word"];
			}
			arsort($search);
			if(is_array($this->arrayResults)){
				//Les r�sultats qui commencent par la saisie sont plac�s en premiers
				$tmpArray=array();
				foreach($this->arrayResults as $key=>$value){
					if(preg_match('`^'.$this->cleanInputString.'`',$this->cleanString($value['field_content']))){
						$tmpArray[]=$value;
						unset($this->arrayResults[$key]);
					}
				}
				$this->arrayResults=array_merge($tmpArray,$this->arrayResults);	
				//limitation des r�sultats et gestion de l'affichage
				foreach($this->arrayResults as $key=>$value){
					if ($key<$this->maxSuggestions) {
						//champ dans lequel la valeur a �t� trouv�e
						$this->arrayResults[$key]["field_name"]=$this->tbChampBase[$value["field_subfield"]];
						//passage en gras des mots
						$this->arrayResults[$key]["field_content"]=$this->markBold($value["field_content"],implode("|",$search));
						//pour les occurences trop longues, juste les mots en gras
						$this->arrayResults[$key]["field_content_search"]=$this->listFoundWords($this->arrayResults[$key]["field_content"]);
					} else {
						unset($this->arrayResults[$key]);
					}
				}
			}
		}
	}

// ---------------------------------------------------------------------------------------------------
//  markBold($string,$wordToFind) : met un mot en gras dans une cha�ne
// ---------------------------------------------------------------------------------------------------
	function markBold($string,$wordsToFind){
		$specialChars = array("a","e","i","o","u","y","c","n" );
		$specialCharsReplacement = array("[a|�|�|�|�|�|�]{1}","[e|�|�|�|�]{1}","[i|�|�|�|�]{1}","[o|�|�|�|�|�|�]{1}","[u|�|�|�|�]{1}","[y|y]{1}","[c|�]{1}","[n|�]{1}" );
		$wordsToFind = str_replace($specialChars, $specialCharsReplacement, $wordsToFind);
		
		$tmpArray=preg_split("/([\s,\'\"\.\-\(\) ]+)/",trim($string),-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($tmpArray as $key=>$value){
			$tmpArray[$key]=preg_replace("/^($wordsToFind)$/i", "<b>\\1</b>",$value);
		}
		return implode("",$tmpArray);
	}
	
// ---------------------------------------------------------------------------------------------------
//  listFoundWords($string) : renvoie un tableau des mots uniques trouv�s en gras
// ---------------------------------------------------------------------------------------------------
	function listFoundWords($string){
		preg_match_all("`<b>(.*?)<\/b>`",$string,$arrayReturn);
		$arrayReturn = array_unique($arrayReturn[1]);
		return $arrayReturn;
	}
	
// ---------------------------------------------------------------------------------------------------
//  cleanString($string) : renvoie une chaine nettoy�e
// ---------------------------------------------------------------------------------------------------
	function cleanString($string){
		$string = str_replace("%","",$string);
		$string = convert_diacrit($string);
		$string = strip_empty_words($string);
		return $string;
	}

} # fin de d�finition de la classe