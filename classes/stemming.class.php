<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: stemming.class.php,v 1.1 2012-12-06 09:38:25 arenou Exp $


class stemming {
	public $word;
	public $clean_word="";
	public $stem;
	public $vowels = array("a","e","i","o","u","y","�","�","�","�","�","�","�","�","�","�","�");
	public $standard_suffixes = array(
		"ance","iqUe","isme","able","iste","eux","ances","iqUes","ismes","ables","istes",
		"atrice","ateur","ation","atrices","ateurs","ations",
		"logie","logies",
		"usion","ution","usions","utions",
		"ence","ences",
		"ement","ements",
		"it�","it�s",
		"if","ive","ifs","ives",
		"eaux",
		"aux",
		"euse","euses",
		"issement","issements",
		"amment",
		"emment",
		"ment","ments"	
	);
	public $verbs_suffixes_i = array(
		"�mes","�t","�tes","i","ie","ies","ir","ira","irai","iraIent","irais","irait","iras","irent","irez","iriez","irions","irons","iront","is","issaIent",
		"issais","issait","issant","issante","issantes","issants","isse","issent","isses","issez","issiez","issions","issons","it"
	);
	public $others_verbs_suffixes= array(
		"ions",
		"�","�e","�es","�s","�rent","er","era","erai","eraIent","erais","erait","eras","erez","eriez","erions","erons","eront","ez","iez",
		"�mes","�t","�tes","a","ai","aIent","ais","ait","ant","ante","antes","ants","as","asse","assent","asses","assiez","assions"
	);
	public $residual_suffixes = array(
		"ion",
		"ier","i�re","Ier","I�re",
		"e",
		"�"
	);
	
	public $rv = "";
	public $r1 = "";
	public $r2 = "";
	public $pos_rv;
	public $pos_r1;
	public $pos_r2;
	public $do_step_2a=false;
	public $do_step_2b=false;
	public $do_step_3 = false;
	public $do_step_4 = true;
	
	public function __construct($word){
		$this->word = $word;
		$this->process();
	}

	protected function get_clean_word(){
		$clean_word = strtolower($this->word);
		
		for($i=0; $i<strlen($clean_word) ; $i++){
			switch($clean_word[$i]){
				case "i" :
					if(in_array($this->clean_word[$i-1],$this->vowels) && in_array($clean_word[$i+1],$this->vowels)){
						$this->clean_word.= strtoupper($clean_word[$i]);
					}else{
						$this->clean_word.= $clean_word[$i];
					}
					break;
				case "u" :
					if($this->clean_word[$i-1] == "q" || (in_array($this->clean_word[$i-1],$this->vowels) && in_array($clean_word[$i+1],$this->vowels))){
						$this->clean_word.= strtoupper($clean_word[$i]);
					}else{
						$this->clean_word.= $clean_word[$i];
					}
					break;
				case "y" :
					if(in_array($this->clean_word[$i-1],$this->vowels) || in_array($clean_word[$i+1],$this->vowels)){
						$this->clean_word.= strtoupper($clean_word[$i]);
					}else{
						$this->clean_word.= $clean_word[$i];
					}
					break;
				default :
					$this->clean_word.= $clean_word[$i];
					break;
			}
		}
		return $this->clean_word;
	}
	
	protected function get_rv(){
		//on commence par regarder les exceptions
		$start = substr($this->clean_word,0,3);
		if($start == "par" || $start == "col" || $start == "tap"){
			$this->rv = substr($this->clean_word,3);
			return $this->rv;
		}
		//le mot commence par une double voyelle...
		if(in_array($this->clean_word[0],$this->vowels) && in_array($this->clean_word[1],$this->vowels)){
			$this->rv = substr($this->clean_word,3);
			return $this->rv;
		}
		//dans le cas g�n�ral c'est apr�s la premi�re voyelle dans le mot...
		for($i=1;$i<strlen($this->clean_word) ; $i++){
			if(in_array($this->clean_word[$i],$this->vowels)){
				$this->rv = substr($this->clean_word,$i+1);
				return $this->rv;
			}
		}
		//pas de voyelles, c'est le reste du mot...
		$this->rv= substr($this->clean_word,1);
		return $this->rv;
	}
	
	protected function get_r1(){
		for($i=1 ; $i<strlen($this->clean_word) ; $i++){
			if(in_array($this->clean_word[$i-1],$this->vowels) && !in_array($this->clean_word[$i],$this->vowels)){
				$this->r1 = substr($this->clean_word,$i+1);
				return $this->r1;
			}
		}
		$this->r1= substr($this->clean_word,1);
		return $this->r1;
	}
	
	protected function get_r2(){
		for($i=1 ; $i<strlen($this->r1) ; $i++){
			if(in_array($this->r1[$i-1],$this->vowels) && !in_array($this->r1[$i],$this->vowels)){
				$this->r2 = substr($this->r1,$i+1);
				return $this->r2;
			}
		}		
	}	
	
	protected function standard_suffix_removal(){
		foreach($this->standard_suffixes as $suffix){
			//si le sufixe correspond, on applique la r�gle associ�e
			if(substr($this->stem,-strlen($suffix)) == $suffix){
				switch ($suffix){
					case "ance":
					case "iqUe":
					case "isme":
					case "able":
					case "iste":
					case "eux":
					case "ances":
					case "iqUes":
					case "ismes":
					case "ables":
					case "istes":
						$this->delete_if_in_r("r2",$suffix);
						break(2);
					case "atrice":
					case "ateur":
					case "ation":
					case "atrices":
					case "ateurs":
					case "ations":
						if($this->delete_if_in_r("r2",$suffix)){
							if($this->preceded_by($suffix,"ic")){
								$this->delete_if_in_r_else_replace("r2","ic","iqU");
							}
						}
						break(2);
					case "logie":
					case "logie":
						$this->replace_if_in_r("r2",$suffix,"log");
						break(2);
					case "usion":
					case "ution":
					case "usions":
					case "utions":
						$this->replace_if_in_r("r2",$suffix,"u");
						break(2);
					case "ence":
					case "ences":
						$this->replace_if_in_r("r2",$suffix,"ent");
						break(2);
					case "ement":
					case "ements":
						//supprime le suffixe dans RV
						$this->delete_if_in_r("rv",$suffix);
						//s�rie de cas un peu particulier...
						if($this->preceded_by($suffix,"iv")){
							//suffixe pr�c�d� de ic
							$this->delete_if_in_r("r2","iv");
							if($this->preceded_by("iv".$suffix,"at")){
							//suffixe pr�c�d� de at
							$this->delete_if_in_r("r2","at");
							}
						}else if($this->preceded_by($suffix,"eus")){
							$this->delete_if_in_r("r2","eus");
							$this->replace_if_in_r("r1","eus","eux");
						}else if($this->preceded_by($suffix,"abl")){
							$this->delete_if_in_r("r2","abl");
						}else if($this->preceded_by($suffix,"iqU")){
							$this->delete_if_in_r("r2","iqU");	
						}else if($this->preceded_by($suffix,"i�r")){
							$this->replace_if_in_r("rv","i�r","i");
						}else if($this->preceded_by($suffix,"I�r")){
							$this->replace_if_in_r("rv","I�r","i");
						}
						break(2);
					case "it�":
					case "it�s":
						$this->delete_if_in_r("r2",$suffix);
						if($this->preceded_by($suffix,"abil")){
							$this->delete_if_in_r_else_replace("r2","abil","abl");
						}else if($this->preceded_by($suffix,"ic")){
							$this->delete_if_in_r_else_replace("r2","ic","iqU");
						}else if($this->preceded_by($suffix,"iv")){
							$this->delete_if_in_r("r2","iv");
						}
						break(2);
					case "if":
					case "ive":
					case "ifs":
					case "ives":
						if($this->delete_if_in_r("r2",$suffix)){
							if($this->preceded_by($suffix,"at")){
								$this->delete_if_in_r("r2","at");
							}
							if($this->preceded_by("at".$suffix,"ic")){
								$this->delete_if_in_r_else_replace("r2","ic",'iqU');
							}	
						}
						break(2);
					case "eaux":
						$this->replace_suffix($suffix,"eau");
						break(2);
					case "aux":
						$this->replace_if_in_r("r1",$suffix,"al");
						break(2);
					case "euse":
					case "euses":
						$this->delete_if_in_r("r2",$suffix);
						$this->replace_if_in_r("r1",$suffix,"eux");	
						break(2);
					case "issement":
					case "issements":
						if(!in_array(substr($this->clean_word,-(strlen($suffix)+1),1),$this->vowels)){
							$this->delete_if_in_r("r1",$suffix);
						}
						break(2);
					case "amment":
						$this->replace_if_in_r("rv",$suffix,"ant");
						$this->do_step_2a = true;
						break(2);
					case "emment":
						$this->replace_if_in_r("rv",$suffix,"ent");
						$this->do_step_2a = true;
						break(2);
					case "ment":
					case "ments":
						if(in_array(substr($this->clean_word,-(strlen($suffix)+1),1),$this->vowels)){
							//la voyelle pr�c�dente doit aussi �tre dans RV
							if(strpos($this->rv,substr($this->stem,-(strlen($suffix)+1)))!==false){
								$this->delete_if_in_r("rv",$suffix);	
							}
							$this->do_step_2a = true;
						}
						break(2);
				}
			}
		}
		if($this->clean_word == $this->stem){
			$this->do_step_2a = true;
		}else{
			$this->do_step_3 = true;
			$this->do_step_4 = false;
		}
		return $this->stem;
	}

	protected function verbs_suffixes_i_process(){
		$stem =$this->stem;
		foreach($this->verbs_suffixes_i as $suffix){
			//si le sufixe correspond, on supprime de rv
			if(substr($this->stem,-strlen($suffix)) == $suffix && !$this->preceded_by_vowel($suffix)){
				//la non-voyelle pr�c�dente doit aussi �tre dans RV
				if(strpos($this->rv,substr($this->stem,-(strlen($suffix)+1)))!==false){
					$this->delete_if_in_r("rv",$suffix);
					break;
				}
			}
		}
		if($this->stem == $stem){
			$this->do_step_2b = true;
		}else{
			$this->do_step_3 = true;
			$this->do_step_4 = false;
		}
	}
	
	protected function other_verbs_suffixes_process(){
		$stem =$this->stem;
		foreach($this->others_verbs_suffixes as $suffix){
			if(substr($this->stem,-strlen($suffix)) == $suffix && $stem == $this->stem){
				switch($suffix){
					case "ions" :
						$this->delete_if_in_r("r2",$suffix);
						break;
					case "�":
					case "�e":
					case "�es":
					case "�s":
					case "�rent":
					case "er":
					case "era":
					case "erai":
					case "eraIent":
					case "erais":
					case "erait":
					case "eras":
					case "erez":
					case "eriez":
					case "erions":
					case "erons":
					case "eront":
					case "ez":
					case "iez":
						$this->delete_if_in_r("rv",$suffix);
						break;
					case "�mes":
					case "�t":
					case "�tes":
					case "a":
					case "ai":
					case "aIent":
					case "ais":
					case "ait":
					case "ant":
					case "ante":
					case "antes":
					case "ants":
					case "as":
					case "asse":
					case "assent":
					case "asses":
					case "assiez":
					case "assions":
						$this->delete_if_in_r("rv",$suffix);
						//pr�c�d� d'un e
						if($this->preceded_by($suffix,"e")){
							//qui est dans RV
							if(strpos($this->rv,substr($this->clean_word,-(strlen($suffix)+1)))!==false){
								//alors on le vire...
								$this->delete_if_in_r("rv","e");
							}
						}
						break;
				}
			}
		}
		if($this->stem != $stem){
			$this->do_step_3 = true;
			$this->do_step_4 = false;
		}	
	}
	
	protected function residual_suffixes_process(){
		if(substr($this->stem,-1,1) == "s" && !in_array(substr($this->stem,-2,1),array("a","i","o","u","�","s"))){
			$this->stem = substr($this->stem,0,strlen($this->stem)-1);
		}
		foreach($this->residual_suffixes as $suffix){
			if(substr($this->stem,-strlen($suffix)) == $suffix){
				switch($suffix){
					case "ion" :
						if($this->preceded_by($suffix,"s") || $this->preceded_by($suffix,"t")){
							if(strpos($this->rv,substr($this->clean_word,-(strlen($suffix)+1)))!==false){
								$this->delete_if_in_r("r2",$suffix);
							}
						}
						break(2);
					case "ier" :
					case "i�re" :
					case "Ier" :
					case "I�re" :
						$this->replace_if_in_r("rv",$suffix,"i");
						break(2);
					case "e" :
						$this->delete_if_in_r("rv",$suffix);
						break(2);
					case "�" :
						if($this->preceded_by($suffix,"gu")){
							$this->delete_if_in_r("rv",$suffix);
						}
						break(2);
				}
			}
		}
	}
	
	protected function undouble(){
		$end = substr($this->stem,-3);
		if($end == "enn" || $end == "onn" || $end == "ett" || $end == "ell" || substr($this->stem,-4) == "eill"){
			$this->stem = substr($this->stem,0,strlen($this->stem)-1);
		}
	}
	
	protected function unaccent(){
		$no_vowels = false;
		for($i=(strlen($this->stem)-1) ; $i>=0 ; $i--){
			if(!in_array($this->stem[$i],$this->vowels)){
				$no_vowels=true;
				continue;
			}else{
				if($no_vowels && $this->stem[$i] == "�" || $this->stem[$i] == "�"){
					$this->stem = substr($this->stem,0,strrpos($this->stem,$this->stem[$i]))."e".substr($this->stem,strrpos($this->stem,$this->stem[$i]));
				}
				break;
			}
		}

	}
	
	protected function delete_if_in_r_else_replace($r,$suffix,$replace){
		switch($r){
			case "rv" :
				$r = $this->rv;
				$pos_r = $this->pos_rv; 
				break;
			case "r1" : 
				$r = $this->r1;
				$pos_r = $this->pos_r1;
				break;
			case "r2" :
				$r = $this->r2;
				$pos_r = $this->pos_r2;
				break;
		}
		$pos_suffix = strrpos($this->stem,$suffix);
		$suffix_len = strlen($suffix);
		if($r && $pos_suffix !== false && $pos_suffix>=$pos_r){
			$this->stem = substr($this->stem,0,$pos_suffix).substr($this->stem,$pos_suffix+$suffix_len);
		}else{
			$this->stem = substr($this->stem,0,$pos_suffix).$replace.substr($this->stem,$pos_suffix+$suffix_len);
		}		
	}
	
	protected function delete_if_in_r($r,$suffix){
		switch($r){
			case "rv" :
				$r = $this->rv;
				$pos_r = $this->pos_rv; 
				break;
			case "r1" : 
				$r = $this->r1;
				$pos_r = $this->pos_r1;
				break;
			case "r2" :
				$r = $this->r2;
				$pos_r = $this->pos_r2;
				break;
		}
		
		$pos_suffix = strrpos($this->stem,$suffix);
		$suffix_len = strlen($suffix);
		if($r && $pos_suffix !== false && $pos_suffix>=$pos_r){
			$this->stem = substr($this->stem,0,$pos_suffix).substr($this->stem,$pos_suffix+$suffix_len);
			return true;
		}else{
			return false;
		}
	}

	protected function replace_if_in_r($r,$suffix,$replace){
		switch($r){
			case "rv" :
				$r = $this->rv;
				$pos_r = $this->pos_rv; 
				break;
			case "r1" : 
				$r = $this->r1;
				$pos_r = $this->pos_r1;
				break;
			case "r2" :
				$r = $this->r2;
				$pos_r = $this->pos_r2;
				break;
		}
		
		$pos_suffix = strrpos($this->stem,$suffix);
		$suffix_len = strlen($suffix);
		if($r && $pos_suffix !== false && $pos_suffix>=$pos_r){
			$this->stem = substr($this->stem,0,$pos_suffix).$replace.substr($this->stem,$pos_suffix+$suffix_len);
			return true;
		}else{
			return false;
		}
	}
	
	protected function delete_suffix($suffix){
		if(strrpos($this->stem,$suffix)!== false){
			$this->stem = substr($this->stem,0,strrpos($this->stem,$suffix));
			return true;
		}else{
			return false;
		}
	}
	
	protected function replace_suffix($suffix,$replace){
		$pos_suffix = strrpos($this->stem,$suffix);
		$suffix_len = strlen($suffix);
		if(strrpos($this->stem,$suffix)!== false){
			$this->stem = substr($this->stem,0,$pos_suffix).$replace.substr($this->stem,$pos_suffix+$suffix_len);
			return true;
		}else{
			return false;
		}
	}
	protected function process(){
		$this->sort_suffixes();
		$this->get_clean_word();
		$this->get_rv();
		$this->get_r1();
		$this->get_r2();
		$this->stem = $this->clean_word;
		$this->pos_rv = strrpos($this->stem,$this->rv);
		$this->pos_r1 = strrpos($this->stem,$this->r1);
		$this->pos_r2 = strrpos($this->stem,$this->r2);
		$step1 = $step2a = $step2b = $step3 = $step4 = "";
		
		//step 1
		$this->standard_suffix_removal();
		$step1 = $this->stem;
		//step 2
		if($this->do_step_2a){
			$this->verbs_suffixes_i_process();
			$step2a = $this->stem;
		}
		if($this->do_step_2b){
			$this->other_verbs_suffixes_process();
			$step2b = $this->stem;
		}
		//step 3
		if($this->do_step_3){
			$stem = $this->stem;
			$this->stem = substr($this->stem,0,strlen($this->stem)-1).str_replace(array("Y","�"),array("i","c"),substr($this->stem,-1,1));
			if($stem != $this->stem){
				$this->do_step_4 = false;
			}
		}
		//step 4
		if($this->do_step_4){
			$this->residual_suffixes_process();
		}
		//step 5
		$this->undouble();
		//step 6 
		$this->unaccent();
		//step 7 and final...
		$this->stem = strtolower($this->stem);
	}
	
	protected function sort_suffixes(){
		usort($this->standard_suffixes,array($this,_sort_suffixes));
		usort($this->verbs_suffixes_i,array($this,_sort_suffixes));
		usort($this->others_verbs_suffixes,array($this,_sort_suffixes));
		usort($this->residual_suffixes,array($this,_sort_suffixes));
	}
	
	protected function _sort_suffixes($a,$b){
		if(strlen($a)==strlen($b)){
			return 0;
		}
    	return (strlen($a) < strlen($b)) ? 1 : -1;
	}
	
	protected function preceded_by($suffix,$by){
		return substr($this->clean_word,-(strlen($suffix)+strlen($by)),strlen($by)) == $by;
	}
	
	protected function preceded_by_vowel($suffix){
		return in_array(substr($this->clean_word,-(strlen($suffix)+1),1),$this->vowels);
	}
}	