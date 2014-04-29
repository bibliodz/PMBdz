<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: analyse_query.class.php,v 1.48 2013-03-15 10:02:19 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

//Structure de stockage d'un terme
class term {
	var $word; 		//mot (si pas sous expression)
	var $operator;	//op�rateur (and, or ou vide)
	var $sub;		//sous expression = tableau de term
	var $not;		//N�gation du terme (not)
	var $literal;	//Le terme est entier (il y avait des guillemets)
	var $start_with; //L'expression doit commencer par
	var $pound; 	//poids du terme
	
	//Constructeur
	function term($word,$literal,$not,$start_with,$operator,$sub,$pound=1) {
		$this->word=$word;
		$this->operator=$operator;
		$this->sub=$sub;	
		$this->not=$not;		
		$this->literal=$literal;
		$this->start_with=$start_with;
		$this->pound=$pound;
	}
}

//Classe d'analyse d'une requ�te bool�enne
class analyse_query {
	var $current_car;		//Caract�re courant analys�
	var $parenthesis;		//Est-ce une sous expression d'une expression ?
	var $operator="";		//Op�rateur du terme en cours de traitement
	var $neg=0;				//N�gation appliqu�e au terme en cours de traitement
	var $guillemet=0;		//Le terme en cours de traitement est-il entour� par des guillemets
	var $start_with=0;		//Le terme en cours est-il � traiter avec commence par ?
	var $input;				//Requete � analyser
	var $term="";			//Terme courant
	var $literal=0;			//Le termen en cours de traitement est-il lit�ral ?
	var $tree=array();		//Arbre de r�sultat
	var $error=0;			//Y-a-t-il eu un erreur pendant le traitement
	var $error_message="";	//Message d'erreur
	var $input_html="";		//Affichage html de la requ�te initiale (�ventuellement avec erreur surlign�e)
	var $search_linked_words=1;	//rechercher les mots li�s pour le mot
	var $keep_empty;			//on garde les mots vides
	
	//Constructeur
    function analyse_query($input,$debut=0,$parenthesis=0,$search_linked_words=1,$keep_empty=0) {
    	// remplacement espace ins�cable 0xA0:	&nbsp;
    	global $empty_word;
    	
    	$input=clean_nbsp($input);
    	$this->parenthesis=$parenthesis;
		$this->current_car=$debut;
		$this->input=$input;
		$this->keep_empty=$keep_empty;
		$this->search_linked_words=$search_linked_words;
		$this->recurse_analyse();	
		// pour remonter les termes exacts, mais ne marche pas pour les autorit�s. A revoir
    	if(!$parenthesis) {
	    	if((!$this->keep_empty && in_array($this->input,$empty_word)===false) || $this->keep_empty) {
	    		$t=new term(trim($this->input,"_~\""),2,0,1,"or",null,0.2);
				$this->store_in_tree($t,0);		
	    	}		
		}    
    }
    
	// Recherche les synonymes d'un mot  
    function get_synonymes($mot) {
		$mot= addslashes($mot);
		$rqt="select id_mot from mots where mot='".$mot."'";
		$execute_query=mysql_query($rqt);
		if (mysql_num_rows($execute_query)) {			
			//constitution d'un tableau avec le mot et ses synonymes
			$r=mysql_fetch_object($execute_query);
			$rqt1="select mot,ponderation from mots,linked_mots where type_lien=1 and num_mot=".$r->id_mot." and mots.id_mot=linked_mots.num_linked_mot";
			$execute_query1=mysql_query($rqt1);			 			
		 	if (mysql_num_rows($execute_query1)) {
				while (($r1=mysql_fetch_object($execute_query1))) {
					$synonymes[$r1->mot]=$r1->ponderation;					
				}
		 	}
		}		
		return($synonymes);
	 }
    
	function nettoyage_mot_vide($string) {
 		//R�cup�ration des mots vides
 		global $empty_word;
 		if (!is_array($empty_word)) $empty_word=array();
		//Supression des espaces avant et apr�s le terme
		$string = trim($string);
		//D�composition en mots du mot nettoy� (ex : l'arbre devient l arbre qui donne deux mots : l et arbre)
		$words=array();
		if ($string) {
			$words=explode(" ",$string);
		}
		//Variable de stockage des mots restants apr�s supression des mots vides
		$words_empty_free=array();
		//Pour chaque mot
		for ($i=0; $i<count($words); $i++) {
			$words[$i]=trim($words[$i]);
			//V�rification que ce n'est pas un mot vide
			if (($this->keep_empty)||(in_array($words[$i],$empty_word)===false)) {
				//Si ce n'est pas un mot vide, on stoque
				$words_empty_free[]=$words[$i];
			}
		}
		return $words_empty_free;
	}
	
	function calcul_term(&$t,$mot,$litteral,$ponderation) {		
		// Litt�ral ?	
		if($litteral) {
			// Oui c'est un mot litt�ral
			$t->word=$mot;
			$t->literal=1;
			// fin
			return;
		} else {
			// Non ce n'est pas un mot litt�ral
			// Un espace dans le mot?
			if(strchr($mot, ' ')) {
				// Oui ula un espace
				$t->word=$mot;
				$t->literal=1;
				// fin
				return;				
			} else {
				// Non, pas d'espace dans le mot
				// Nettoyage des caract�res
				$mot_clean = convert_diacrit($mot);
				
				$mot_clean = pmb_alphabetic('^a-z0-9\s\*', ' ',pmb_strtolower($mot_clean));		
				// Nettoyage des mots vides
				$mot_clean_vide=$this->nettoyage_mot_vide($mot_clean);
				// Combien de mots reste-t-il ?
				if(count($mot_clean_vide) > 1) {
					// Plusieurs					
					if (!count($t->sub)) $op_sub=''; else $op_sub="or";				
					foreach($mot_clean_vide as $word) {
						$terms[]=new term($word,0,0,0,$op_sub,"",$ponderation);			
						$op_sub="or";
					}
					$t->sub=$terms;
				} elseif(count($mot_clean_vide) == 1)  {
					// Un seul
					$t->word=$mot_clean_vide[0];
					$t->literal=0;
					// fin
					return;				
				}else return;				
			}
		}
	}
	
    //Stockage d'un terme dans l'arbre de r�sultat
	function store_in_tree($t,$search_linked_words) {
 		// Mot ou expression ?
 		if (!$t->sub && $t->word) {
 			//C'est un mot
 			// Synonyme activ� && ce n'est pas une expression commence par '_xx*' ?
 			if ($search_linked_words && !$this->start_with) { 
 				// Oui, Synonyme activ�
 				// C'est un litt�ral ?
 				if ($t->literal) {
 					// Oui, c'est un litt�ral
 					// Recherche de synonymes
 					$synonymes=$this->get_synonymes($t->word);
					$mots=$t->word;
					
 					// Y-a-t'il des synonymes ?
 					if($synonymes) {						
 						// Oui il y a des synonymes 								 					
	 					// Pour chaque synonyme et le terme ajout � $t->sub
	 					$op_sub="";
						foreach($synonymes as $synonyme => $ponderation) {
							
							$t->sub[]=new term($synonyme,0,0,0,$op_sub,"",$ponderation);	
							$this->calcul_term($t->sub[count($t->sub)-1],$synonyme,0,$ponderation);
							$op_sub="or";
						} 		
						// Ajout du term force lit�ral � 1	
						$t->word="";
						$t->sub[]=new term($mots,1,0,0,$op_sub,"",$t->pound);	
						$this->calcul_term($t->sub[count($t->sub)-1],$mots,1,$t->pound);
						$op_sub="or";
						
 					} 					
 				} else {
 					// Non, ce n'est pas un litt�ral
 					// Recherche de synonymes
  					$synonymes=$this->get_synonymes($t->word);
					$mots=$t->word;
					$t->word="";
					
 					// Y-a-t'il des synonymes ?
 					if($synonymes) { 						
 						// Oui il y a des synonymes
						foreach($synonymes as $synonyme => $ponderation) {																
							$liste_mots[$synonyme]=$ponderation;
						} 						 					
 					} 
 					// Suite et, Non, il n'y a pas de synonyme
	 				// Nettoyage des caract�res
					$mot_clean = convert_diacrit($mots);					
					$mot_clean = pmb_alphabetic('^a-z0-9\s\*', ' ',pmb_strtolower($mot_clean));		
					
					// Nettoyage des mots vides
					$mot_clean_vide=$this->nettoyage_mot_vide($mot_clean);
					
					// Pour chaque mot nettoyer
					if(count($mot_clean_vide)) foreach($mot_clean_vide as $word) {						
		 				// Recherche de synonymes
		 				$synonymes_clean=$this->get_synonymes($word);				 					
		 				// Pour chaque synonyme et le terme ajout � $t->sub
						if(count($synonymes_clean))foreach($synonymes_clean as $synonyme => $ponderation) {									
							$liste_mots[$synonyme]=$ponderation;						
						}													
					}
											
					// ajout des mots nettoy�s
					if(count($mot_clean_vide))foreach($mot_clean_vide as $word) {
						$liste_mots[$word]=$t->pound;		
					}

						
					if (!count($t->sub)) $op_sub=''; else $op_sub="or";		
					if(count($liste_mots) > 1) {
						$t->word="";
						// Plusieurs mots									
						foreach($liste_mots as $word => $ponderation) {
							$t->sub[]=new term($word,0,0,0,$op_sub,"",$ponderation);	
							$this->calcul_term($t->sub[count($t->sub)-1],$word,0,$ponderation);
							$op_sub="or";
						}
						//$t->sub=$terms;
					} elseif(count($liste_mots) == 1)  {
						// Un seul mot
						foreach($liste_mots as $word=> $ponderation) {
							$t->word=$word;		
						}							
					} else return;
 				}				
 			} else {
 				// Non, Synonyme d�sactiv�
 				// C'est un litt�ral ?
 				if ($t->literal) {
 					// Oui, c'est un litt�ral
 					// plus rien � faire					
 				} else {
 					// Non, ce n'est pas un litt�ral
 					// Nettoyage des caract�res
					$mot_clean = convert_diacrit($t->word);
					
					$mot_clean = pmb_alphabetic('^a-z0-9\s\*', ' ',pmb_strtolower($mot_clean));
 					// Nettoyage des mots vides
					$mot_clean_vide=$this->nettoyage_mot_vide($mot_clean);
					// Combien de mots reste-t-il ?
					if(count($mot_clean_vide) > 1) {
						$t->word="";
						// Plusieurs mots					
						if (!count($t->sub)) $op_sub=''; else $op_sub="or";				
						foreach($mot_clean_vide as $word) {
							$terms[]=new term($word,0,0,0,$op_sub,"",$ponderation);			
							$op_sub="or";
						}
						$t->sub=$terms;
					} elseif(count($mot_clean_vide) == 1)  {
						// Un seul mot
						$t->word=$mot_clean_vide[0];								
					} else return;
 				}	
 			}
 		} elseif ($t->sub && !$t->word) {
 			// C'est une expression :
 			// Vider op�rateur
 			if (!count($this->tree)) $t->operator="";
 		} else {
 			//	Ce n'est ni un mot, ni une exrssion: c'est rien 			
 			return;
 		}
 		// Inscription dans l'arbre
 		$this->tree[]=$t;		
		//print "<pre>";print_r($this->tree);print"</pre>";			
 	}
	
	//Affichage sous forme RPN du r�sultat de l'analyse
	function show_analyse_rpn($tree="") {
		//Si tree vide alors on prend l'arbre de la classe
		if ($tree=="") $tree=$this->tree;
		$r="";
		//Pour chaque branche ou feuille de l'arbre
		for ($i=0; $i<count($tree); $i++) {
			//Si le terme est un mot
			if ($tree[$i]->sub==null) {
				//Affichage du mot avec le pr�fixe N pour terme Normal et L pour terme lit�ral, C pour Commence par
				if ($tree[$i]->start_with) $r.="C "; 
				if ($tree[$i]->literal) $r.="L "; else $r.="N ";
				$r.=$tree[$i]->word."\n";
			} else
				//Sinon on analyse l'expression 
				$r.=$this->show_analyse_rpn($tree[$i]->sub);
			//Affichage n�gation et op�rateur si n�cessaire
			if ($tree[$i]->not) $r.="not\n";
			if ($tree[$i]->operator) $r.=$tree[$i]->operator."\n";
		}
		return $r;
	}

	//Affichage sous forme math�matique logique du r�sultat de l'analyse
	function show_analyse($tree="") {
		if ($tree=="") $tree=$this->tree;
		$r="";
		for ($i=0; $i<count($tree); $i++) {
			if ($tree[$i]->operator) $r.=$tree[$i]->operator." ";
			if ($tree[$i]->not) $r.="not";
			if ($tree[$i]->sub==null) {
				if ($tree[$i]->start_with) $start_w="start with "; else $start_w="";
				if ($tree[$i]->not) $r.="(";
				$r.=$start_w;
				if ($tree[$i]->literal) $r.="\"";
				$r.=$tree[$i]->word;
				if ($tree[$i]->literal) $r.="\"";
				if ($tree[$i]->not) $r.=")";
				$r.=" ";
			} else { $r.="( ".$this->show_analyse($tree[$i]->sub).") "; }
		}
		return $r;
	}	

	//Construction r�cursive de la requ�te SQL
	function get_query_r($tree,&$select,&$pcount,$table,$field_l,$field_i,$id_field,$neg_parent=0,$main=1) {
		$where="";
		for ($i=0; $i<count($tree); $i++) {
			
			if (($tree[$i]->operator)&&($tree[$i]->literal!=2)) $where.=$tree[$i]->operator." ";
			if ($tree[$i]->sub==null) {
				if ($tree[$i]->literal) $clause="trim(".$field_l.") "; else $clause=$field_i." ";
				if ($tree[$i]->not) $clause.="not ";
				$clause.="like '";
				if (!$tree[$i]->start_with) $clause.="%";
				if (!$tree[$i]->literal) $clause.=" ";
				$clause.=addslashes(str_replace("*","%",$tree[$i]->word));
				if (!$tree[$i]->literal) $clause.=" ";
				$clause.="%'";
				if($tree[$i]->literal!=2) $where.=$clause." ";
				//if ((!$tree[$i]->not)&&(!$neg_parent)) {
					if ($select) $select.="+";
					$select.="(".$clause.")";
					if ($tree[$i]->pound && ($tree[$i]->pound!=1)) $select.="*".$tree[$i]->pound;
					$pcount++;
				//}
			} else { 
				if ($tree[$i]->not) $where.="not ";
				//$tree[$i]->not
				$where.="( ".$this->get_query_r($tree[$i]->sub,$select,$pcount,$table,$field_l,$field_i,$id_field,$tree[$i]->not,0).") "; 
			}
		}
		if ($main) {
			if ($select=="") $select="1";
			if ($where=="") $where="0";
			$q["select"]="(".$select.")";
			$q["where"]="(".$where.")";
			$q["post"]=" group by ".$id_field." order by pert desc,".$field_i." asc";
			return $q;
		}
		else
			return $where;
	}

	//Fonction d'appel de la construction r�cursive de la requ�te SQL
	function get_query($table,$field_l,$field_i,$id_field,$restrict="",$offset=0,$n=0) {
		$select="";
		$pcount=0;
		$q=$this->get_query_r($this->tree,$select,$pcount,$table,$field_l,$field_i,$id_field,0,1);
		$res="select ".$id_field.",".$q["select"]." as pert from ".$table." where (".$q["where"].")";
		if ($restrict!="") $res.=" and ".$restrict;
		$res.=$q["post"];
		if ($n!=0) $res.=" limit ".$offset.",".$n;
		return $res;
	}
	
	function get_query_members($table,$field_l,$field_i,$id_field,$restrict="",$offset=0,$n=0,$is_fulltext=false) {
		global $pmb_search_full_text;
		if (($is_fulltext)&&($pmb_search_full_text)) $q=$this->get_query_full_text($table,$field_l,$field_i,$id_field); else {
			$select="";
			$pcount=0;
			$q=$this->get_query_r($this->tree,$select,$pcount,$table,$field_l,$field_i,$id_field,0,1);
		}
		if ($restrict) $q["restrict"]=$restrict;
		return $q;
	}
	
	function get_query_full_text($table,$field_l,$field_i,$id_field) {
		$q["select"]="(match($field_l) against ('".addslashes($this->input)."' in boolean mode))";
		$q["where"]="(match($field_l) against ('".addslashes($this->input)."' in boolean mode))";
		$q["post"]=" group by ".$id_field." order by pert desc,".$field_i." asc";
		return $q;
	}
	
	//Requ�te de comptage des r�sultats
	function get_query_count($table,$field_l,$field_i,$id_field,$restrict="") {
		$select="";
		$pcount=0;
		$q=$this->get_query_r($this->tree,$select,$pcount,$table,$field_l,$field_i,$id_field,0,1);
		$res="select count(distinct ".$id_field.") from ".$table." where (".$q["where"].")";
		if ($restrict!="") $res.=" and ".$restrict;
		return $res;
	}
	
	//Analyse de la requ�te saisie (machine d'�tat)
	function recurse_analyse() {
		global $msg;
		global $charset;
		global $pmb_default_operator;
		$s="new_word";
		$end=false;
		
		while (!$end) {
			switch ($s) {
				//D�but d'un nouveau terme
				case "new_word":
					if ($this->current_car>(pmb_strlen($this->input)-1)) { 
						$end=true; 
						if ($this->parenthesis) {
							$this->error=1;
							$this->error_message=$msg["aq_missing_term_and_p"];
							break;
						}
						if ($this->guillemet) {
							$this->error=1;
							$this->error_message=$msg["aq_missing_term_and_g"];
							break;
						}
						break; 
					}	
					$cprec=pmb_getcar($this->current_car - 1,$this->input);			
					$c=pmb_getcar($this->current_car,$this->input);
					$this->current_car++;
					//Si terme pr�c�d� par un op�rateur (+, -, ~) et pas d'op�rateur et pas de guillemet ouvert et pas de commence par : 
					//affectation op�rateur. N�anmoins, si c'est le premier terme on n'en tient pas compte
					if ((($c=="+")||($c=="-" && $cprec == " ")||($c=="~"))&&($this->operator=="")&&(!$this->guillemet)&&(!$this->neg)&&(!$this->start_with)) {
						if (($c=="+")&&(count($this->tree))) {
							if ($pmb_default_operator == 1) {
								$this->operator="or";
							} else {
								$this->operator="and";
							}
						} else if (($c=="-" && $cprec == " ")&&(count($this->tree))) {
							$this->operator="and";
							$this->neg=1;
						} else if ((($c=="-" && $cprec == " ")&&(!count($this->tree)))||($c=="~")) $this->neg=1;
						//Apr�s l'op�rateur, on continue � chercher le d�but du terme
						$s="new_word";
						break;
					}
					//Si terme pr�c�d� par un op�rateur et qu'il y a d�j� un op�rateur ou un commence par et qu'on est pas 
					//dans des guillemets alors erreur !
					if ((($c=="+")||($c=="-" && $cprec == " ")||($c=="~"))&&(!$this->guillemet)&&(($this->operator!="")||($this->neg)||($this->start_with))) {
						if (!$this->start_with) {
							if (($c=="~")&&($this->operator=="and")) {
								if (!$this->neg)
									$message_op=$msg["aq_and_not_error"];
								else
									$message_op=$msg["aq_minus_error"];
							} else if ((($c=="+")||($c=="-" && $cprec == " "))&&($this->neg)&&(!$this->operator)) {
								$message_op=sprintf($msg["aq_neg_error"],$c);
							}  else {
								$message_op=$msg["aq_only_one"];
							}
						} else $message_op=$msg["aq_start_with_error"];
						$end=true; $this->error_message=$message_op; $this->error=1; break;
					}
					//Si terme pr�c�d� par "commence par" et qu'on est pas dans les guillemets alors op�rateur commence par activ�
					if (($c=="_")&&(!$this->guillemet)) {
						$this->start_with=1;
						break;
					}
					
					//Si premier guillemet => terme lit�ral
					if (($c=="\"")&&($this->guillemet==0))	{
						$this->guillemet=1;
						$this->literal=1;
						//Apr�s le guillemets, on continue � chercher le d�but du terme
						break;
					}			
					//Si guillement et guillemet d�j� ouvert => annulation du terme lit�ral
					if (($c=="\"")&&($this->guillemet==1)) {
						$this->guillemet=0;
						$this->literal=0;
						//Apr�s le guillemets, on continue � chercher le d�but du terme
						break;
					}
					//Si il y a un espace et pas dans les guillemets, on en tient pas compte
					if (($c==" ")&&(!$this->guillemet)) break;
					//Si une parent�se ouverte, alors analyse de la sous expression
					if (($c=="(")&&(!$this->guillemet)) {
						$sub_a=new analyse_query($this->input,$this->current_car,1,$this->search_linked_words);
						//Si erreur dans sous expression, erreur !
						if ($sub_a->error) {
							$this->error=1;
							//Mise � jour du caract�re courant o� s'est produit l'erreur
							$this->current_car=$sub_a->current_car;
							$this->error_message=$sub_a->error_message;
							$end=true;
							break;
						} else {
							//Si pas d'erreur, stockage du r�sultat dans terme
							$this->term=$sub_a->tree;
							//Si il n'y a pas d'op�rateur et que ce n'est pas le premier terme, 
							//op�rateur par d�faut
							//if ((!$this->operator)&&(count($this->tree))) $this->operator="or";
							if ((!$this->operator)&&(count($this->tree))){
      							if ($pmb_default_operator == 1) {
      								$this->operator="and";
      							} else {
      								$this->operator="or";
      							}
      						}
							$this->current_car=$sub_a->current_car;
							//D�but Attente du prochain terme
							$s="space_first";
							break;
						}
					}
					//Si parent�se fermante et parent�se d�j� ouverte alors on s'en va
					if (($c==")")&&($this->parenthesis)&&(!$this->guillemet)) {
						$end=true;
						break;
					}
					//Si aucun des cas pr�c�dents, c'est le d�but du terme
					$this->term.=$c;
					//Si il n'y a pas d'op�rateur et que ce n'est pas le premier terme, 
					//op�rateur par d�faut
					//if ((!$this->operator)&&(count($this->tree))) $this->operator="or";
					if ((!$this->operator)&&(count($this->tree))){
						if ($pmb_default_operator == 1) {
							$this->operator="and";
						} else {
							$this->operator="or";
						}
					}
					//Lecture du terme
					$s="stockage_car";
					break;
				//Lecture d'un terme
				case "stockage_car":
					if ($this->current_car>(pmb_strlen($this->input)-1)) {
						//Si on lit une sous expression et qu'on arrive � la fin avant la parent�se fermante
						//alors erreur
						//sinon, passage � l'�tat attente du prochain terme (pourquoi me direz-vous alors qu'on arrive � la fin ? parceque ce cas est g�r� en space_first) 
						if ($this->guillemet) { $this->error_message=$msg["aq_missing_g"]; $end=true; $this->error=1; break; }
						if ($this->parenthesis) { $this->error_message=$msg["aq_missing_p"]; $end=true; $this->error=1; break; }
						$s="space_first";
					}
					//Lecture caract�re
					$cprec=pmb_getcar($this->current_car - 1,$this->input);	
					$c=pmb_getcar($this->current_car,$this->input);
					$this->current_car++;
					//Si espace et terme lit�ral : l'espace fait partie du terme
					if ((($c==" ")||($c=="+")||($c=="-" && $cprec == " "))&&($this->guillemet==1)) { $this->term.=$c; break; }
					//Si espace et terme non lit�ral : espace = s�parateur de terme => passage � D�but Attente du prochain terme
					if ((($c==" ")||($c=="+")||($c=="-" && $cprec == " "))&&($this->guillemet==0)) { $s="space_first"; $this->current_car--; break; }
					//Si guillemet et terme lit�ral : guillemet = fin du terme => passage � D�but Attente du prochain terme
					if (($c=="\"")&&($this->guillemet==1)) { $s="space_first"; $this->guillemet=0; break; }
					//Si parent�se fermante et sous-expression et que l'on est pas dans un terme lit�ral, 
					//alors fin de sous expression � analyser => passage � D�but Attente du prochain terme
					if (($c==")")&&($this->parenthesis==1)&&($this->guillemet==0)) { $s="space_first"; $this->current_car--; break; }
					//Si aucun des cas pr�c�dent, ajout du caract�re au terme... et on recommence
					$this->term.=$c;
					break;
				//D�but Attente du prochain terme apr�s la fin d'un terme
				//A ce niveau, on s'attend � un caract�re s�parateur et si on le trouve, on enregistre le terme dans l'arbre 
				//Ensuite on passe � l'�tat attente du prochain terme ("space_wait") qui saute tous les caract�res vides avant de renvoyer � new_word
				case "space_first":
					if ($this->current_car>(pmb_strlen($this->input)-1)) {
						//Si fin de chaine et parent�se ouverte => erreur
						if ($this->parenthesis) { $end=true; $this->error_message=$msg["aq_missing_p"]; $this->error=1; break; }
						//Sinon c'est la fin de l'analyse : on enregistre le dernier terme et on s'arr�te
						$end=true;
						if (is_array($this->term))
							$t=new term("",$this->literal,$this->neg,$this->start_with,$this->operator,$this->term);
						else
							$t=new term($this->term,$this->literal,$this->neg,$this->start_with,$this->operator,null);
						$this->store_in_tree($t,$this->search_linked_words);
						break;
					}				
					//Lecture du prochain caract�re
					$cprec=pmb_getcar($this->current_car - 1,$this->input);	
					$c=pmb_getcar($this->current_car,$this->input);
					$this->current_car++;
					//Si parent�se fermante et sous expression en cours d'analyse => fin d'analyse de la sous expression
					if (($c==")")&&($this->parenthesis)) {
						$end=true;
						//Enregistrement du dernier terme
						if (is_array($this->term))
							$t=new term("",$this->literal,$this->neg,$this->start_with,$this->operator,$this->term);
						else
							$t=new term($this->term,$this->literal,$this->neg,$this->start_with,$this->operator,null);
						$this->store_in_tree($t,$this->search_linked_words);
						break;
					}
					//Sinon, si ce n'est pas un espace, alors erreur (ce n'est pas le s�parateur attendu)
					if (($c!=" ")&&($c!="+")&&($c!="-" && $cprec!=" ")) {
						$end=true; $this->error_message=$msg["aq_missing_space"]; $this->error=1; break;
					}
					//Si tout va bien, on attend le prochain terme
					if ($c!=" ")
						$this->current_car--;
					$s="space_wait";
					break;
				//Attente du prochain terme : on saute tous les espaces avant de renvoyer � la lecture du nouveau terme ! 
				case "space_wait":
					if ($this->current_car>(pmb_strlen($this->input)-1)) {
						//Si prent�se ouverte et fin de la chaine => erreur
						if ($this->parenthesis) { $end=true; $this->error_message=$msg["aq_missing_p"]; $this->error=1; break; }
						//Sinon, si fin de la chaine, enregistrement du terme pr�c�dent et fin d'analyse
						if (is_array($this->term))
							$t=new term("",$this->literal,$this->neg,$this->start_with,$this->operator,$this->term);
						else
							$t=new term($this->term,$this->literal,$this->neg,$this->start_with,$this->operator,null);
						$this->store_in_tree($t,$this->search_linked_words);
						$end=true;
						break;
					}
					//Lecture du caract�re suivant
					$c=pmb_getcar($this->current_car,$this->input);
					$this->current_car++;
					//Si ) et sous expression en cours d'analyse, fin de l'analyse de la sous expression
					if (($c==")")&&($this->parenthesis==1))	{
						//Enregistrement du terme et fin d'analyse
						if (is_array($this->term))
							$t=new term("",$this->literal,$this->neg,$this->start_with,$this->operator,$this->term);
						else
							$t=new term($this->term,$this->literal,$this->neg,$this->start_with,$this->operator,null);
						$this->store_in_tree($t,$this->search_linked_words);
						$end=true;
						break;
					}
					//Si le caract�re n'est pas un espace, alors c'est le d�but du prochain terme
					if ($c!=" ") {
						$this->current_car--;
						//Enregistrement du dernier terme
						if (is_array($this->term))
							$t=new term("",$this->literal,$this->neg,$this->start_with,$this->operator,$this->term);
						else
							$t=new term($this->term,$this->literal,$this->neg,$this->start_with,$this->operator,null);
						$this->store_in_tree($t,$this->search_linked_words);
						//Remise � z�ro des indicateurs
						$this->operator="";
						$this->term="";
						$this->neg=0;
						$this->literal=0;
						$this->start_with=0;
						//Passage � nouveau terme
						$s="new_word";
						break;
					}
					//Sinon on reste en attente
					break;
			}
		}
		if ($this->error) {
			$this->input_html=pmb_substr($this->input,0,$this->current_car-1)."!!red!!".pmb_substr($this->input,$this->current_car-1,1)."!!s_red!!".pmb_substr($this->input,$this->current_car);
		} else $this->input_html=$this->input;
		if ((!$this->error)&&(!count($this->tree))) {
			$this->error=1;
			$this->error_message=$msg["aq_no_term"];			
		}
		$this->input_html=htmlentities($this->input_html,ENT_QUOTES,$charset);
		$this->input_html=str_replace("!!red!!","<font color='#DD0000'><b><u>",$this->input_html);
		$this->input_html=str_replace("!!s_red!!","</u></b></font>",$this->input_html);
	}
}

?>