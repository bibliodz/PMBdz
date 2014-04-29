<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: openurl_descriptors_kev_mtx.class.php,v 1.1 2011-08-02 12:36:00 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/openurl/descriptors/openurl_descriptors.class.php");

class openurl_descriptor_identifier_kev_mtx extends openurl_descriptor_identifier{

    function openurl_descriptor_identifier_kev_mtx($notice=array()) {
		parent::openurl_descriptor_identifier($notice);
    }
    
    function serialize($tab){
    	return openurl_serialize_kev_mtx::serialize($tab);
    }
}

class openurl_descriptor_identifier_kev_mtx_isbn extends openurl_descriptor_identifier_kev_mtx{

    function openurl_descriptor_identifier_kev_mtx_isbn($notice) {
    	parent::openurl_descriptor_identifier_kev_mtx($notice);
    	$this->uri = $this->uri.":urn:ISBN";
    }
    
    function serialize($debug=false){
    	$this->infos=array();
    	if($this->notice['bl']['value'] == "m"){
	 		foreach($this->notice['f'] as $f){
				switch($f['c']){
					case "010" :
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$this->infos[$this->entityType.'_id'] = "urn:ISBN:".$s['value'];
									break;
							}
						}	
						break;
				}
	 		}
    	}
    	if($debug) highlight_string("ISBN (identifier):".print_r($this->infos,true));
	 	return parent::serialize($this->infos);
    }
    
    function unserialize($infos){
    	$this->infos = $infos;
    	$this->search_infos[] =array(
    		'id' => $this->entityType == "rft" ? $this->crit_id['isbn'] : $this->crit_id['book_isbn'],
    		'op' => "STARTWITH",
    		'value' => str_replace("urn:ISBN:","",$infos)
    	); 
    }
}


class openurl_descriptor_identifier_kev_mtx_issn extends openurl_descriptor_identifier_kev_mtx{

    function openurl_descriptor_identifier_kev_mtx_issn($notice) {
    	parent::openurl_descriptor_identifier_kev_mtx($notice);
    	$this->uri = $this->uri.":urn:ISSN";
    }
    
    function serialize($debug=false){
    	$this->infos=array();
    	
 		if($this->notice['bl']['value'] == "s" && $this->notice['hl']['value'] == "1"){
	 		foreach($this->notice['f'] as $f){
				switch($f['c']){
					case "010" :
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$this->infos[$this->entityType.'_id'] = "urn:ISSN:".$s['value'];
									break;
							}
						}	
						break;
				}
	 		}
   		}
   		if($debug) highlight_string("ISSN (identifier):".print_r($this->infos,true));
    	return parent::serialize($this->infos);
    }

    function unserialize($infos){
    	$this->infos = $infos;
     	$this->search_infos[] =array(
    		'id' => $this->entityType == "rft" ? $this->crit_id['issn'] : $this->crit_id['parent_issn'],
    		'op' => "STARTWITH",
    		'value' => str_replace("urn:ISSN:","",$infos)
    	);
    }
}

class openurl_descriptor_identifier_kev_mtx_doi extends openurl_descriptor_identifier_kev_mtx{

    function openurl_descriptor_identifier_kev_mtx_doi($notice) {
    	parent::openurl_descriptor_identifier_kev_mtx($notice);
    	$this->uri = $this->uri.":info:doi";
    }

    function serialize($debug=false){
    	$infos=array();
    	$doi = false;

    	for($i=0 ; $i<count($this->notice['f']) ; $i++){
    		switch($this->notice['f'][$i]['c']){
  				case "014" :
					if($this->notice['f'][$i]['s'][1]['c'] == "b" && $this->notice['f'][$i]['s'][1]['value'] == "DOI"){
						$infos[$this->entityType.'_id'] = "info:doi:".$this->notice['f'][$i]['s'][0]['value'];
					}
					break;
  			}
    	}
   		if($debug) highlight_string("DOI (identifier):".print_r($infos,true));
    	return parent::serialize($infos);
    }
    
    function unserialize($infos){
    	$this->infos = $infos;
    	//on ne traite que pour la notice, pas un parent...
    	if($this->entityType == "rft"){
	    	$this->search_infos[] =array(
	    		'id' => $this->crit_id['external_id'],
	    		'op' => "EQ",
	    		'value' => str_replace("info:doi:","",$infos),
	     		'var' => array(
	     			array(
	     				'name' => "id_resolver",
	     				'value' => 2
	     			)
	     		)
	    	);
    	}
    }
}

class openurl_descriptor_identifier_kev_mtx_pmid extends openurl_descriptor_identifier_kev_mtx{

    function openurl_descriptor_identifier_kev_mtx_pmid($notice) {
    	parent::openurl_descriptor_identifier_kev_mtx($notice);
    	$this->uri = $this->uri.":info:pmid";
    }
    
    function serialize($debug=false){
    	$this->infos=array();
    	$doi = false;
    	
    	for($i=0 ; $i<count($this->notice['f']) ; $i++){
    		switch($this->notice['f'][$i]['c']){
  				case "014" :
					if($this->notice['f'][$i]['s'][1]['c'] == "b" && $this->notice['f'][$i]['s'][1]['value'] == "PMID"){
						$this->infos[$this->entityType.'_id'] = "info:pmid:".$this->notice['f'][$i]['s'][0]['value'];
					}
					break;
  			} 		
    	}
   		if($debug) highlight_string("PMID (identifier):".print_r($this->infos,true));
    	return parent::serialize($this->infos);
    }
    
    function unserialize($infos){
    	$this->infos = $infos;
    	//on ne traite que pour la notice, pas un parent...
    	if($this->entityType == "rft"){
	    	$this->search_infos[] =array(
	    	'id' => $this->crit_id['external_id'],
	    		'op' => "EQ",
	    		'value' => str_replace("info:pmid:","",$infos),
	     		'var' => array(
	     			array(
	     				'name' => "id_resolver",
	     				'value' => 1
	     			)
	     		)
	    	);
    	}
    }
}

class openurl_descriptor_identifier_kev_mtx_resolver extends openurl_descriptor_identifier_kev_mtx{
	
	function openurl_descriptor_identifier_kev_mtx_resolver($adr){
		parent::openurl_descriptor_identifier_kev_mtx();
		$this->adr = $adr;
	}
	
	function serialize($debug=false){
		$this->infos = array();
		$this->infos[$this->entityType.'_id'] = $this->adr;
		if($debug) highlight_string("Resolver Type (identifier):".print_r($this->infos,true));
		return parent::serialize($this->infos);
	}
	
    function unserialize($infos){
    	$this->infos = $infos;
    }
}

class openurl_descriptor_identifier_kev_mtx_referrer extends openurl_descriptor_identifier_kev_mtx{
	
	function openurl_descriptor_identifier_kev_mtx_referrer($adr){
		parent::openurl_descriptor_identifier_kev_mtx();
		$this->adr = $adr;
	}
	
	function serialize($debug=false){
		//TODO : v�rifier ce info:sid
		$this->infos = array();
		$this->infos[$this->entityType.'_id'] = "info:sid/".$this->adr;
		if($debug) highlight_string("Referrer (identifier):".print_r($this->infos,true));
		return parent::serialize($this->infos);
	}
	
    function unserialize($infos){
    	$this->infos = $infos;
    }
}

class openurl_descriptor_byval_kev_mtx extends openurl_descriptor_byval{

    function openurl_descriptor_byval_kev_mtx($notice="") {
    	parent::openurl_descriptor_byval($notice);
    	$this->uri = $this->uri.":kev:mtx";
    }   
    
    function serialize(){
    	return openurl_serialize_kev_mtx::serialize($this->infos);
    }
}


class openurl_descriptor_byval_kev_mtx_book extends openurl_descriptor_byval_kev_mtx{

    function openurl_descriptor_byval_kev_mtx_book($notice) {
    	parent::openurl_descriptor_byval_kev_mtx($notice);
    	$this->uri = $this->uri.":book";
    } 
	
	function serialize($debug=false){
		$isChapter = false;
		$this->infos = array();

		$this->getCommonInfos();
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "463" :
					$isChapter = true;
					break;
			}
		}
		
		if($isChapter) $this->getChapterInfos();
		else $this->getMonoInfos();
		if($debug) highlight_string("kev mtx ".($isChapter ? "Chapter" : "Book")." Type (by_val):".print_r($this->infos,true));
		return parent::serialize();
	}
	
	function unserialize($infos){
		$this->infos = $infos;
		$this->search_infos[]= array(
    		'id' => $this->crit_id['typdoc'],
    		'op' => "EQ",
    		'value' => "m"
     	);
     	if($this->entityType == "rft") $this->unserializeCommon();
     	//chapitre
     	if($this->infos[$this->entityType."."."genre"] == 'bookitem'){
     		$this->unserializeChapter();
     	}else{
     		$this->unserializeBook();
     	}
	}
	
	function unserializeCommon(){
	 	$aut = array();
		foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
				case "aulast" :
    			case "aufirst" :
    				$aut[]=$value;	
    				break;
     			case "au" :
     				$this->search_infos[]= array(
    					'id' => $this->crit_id['author'],
    					'op' => "BOOLEAN",
    					'value' => $value
     				);
    				break;
    			case "aucorp" :
      				$this->search_infos[]= array(
    					'id' => $this->crit_id['author_corp'],
    					'op' => "BOOLEAN",
    					'value' => $value
     				);   				
    				break;
    			case "edition" :
    				//mention d'�dition
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['mention_edition'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
    			case "series" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['collection'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
    			case "issn" :
    				//issn de collection
     				$this->search_infos[] =array(
    					'id' => $this->crit_id['collection_issn'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
    			case "pub" :
    				//editeur
      				$this->search_infos[] =array(
    					'id' => $this->crit_id['publisher'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);      				
    				break;
    			case "place" :
					//lieu d'�dition
    				$this->search_infos[] =array(
    					'id' => $this->crit_id['pub_place'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);      				
    				break;
    			case "date" :
    				//ann�e d'�dition
    				$this->search_infos[] =array(
    					'id' => $this->crit_id['year_edition'],
    					'op' => "CONTAINS_AT_LEAST",
    					'value' => $value
    				);      				
    				break;
			}
		}
 		if(count($aut)){
      		$this->search_infos[]= array(
    			'id' => $this->crit_id['first_author'],
    			'op' => "BOOLEAN",
    			'value' => implode(' ',$aut)
     		);   				
 		}		
	}
	
	function unserializeChapter(){
		foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
    			case "atitle" :
     				$this->search_infos[] =array(
    					'id' => $this->crit_id['title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);
    				break;
       			case "title" :
    				//titre du livre
     				$this->search_infos[] =array(
    					'id' => $this->crit_id['book_title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);
    				break;
       			case "isbn" :
    				//isbn du livre
     				$this->search_infos[] =array(
    					'id' => $this->crit_id['book_isbn'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);
    				break;
				case "pages" :
     				$this->search_infos[] =array(
    					'id' => $this->crit_id['pages'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);
    				break;
			}
		}		
	}
	
	function unserializeBook(){
		foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
    			case "isbn" :
    				 $this->search_infos[] =array(
    					'id' => $this->entityType == "rft" ? $this->crit_id['isbn'] : $this->crit_id['parent_isbn'],
    					'op' => "STARTWITH",
    					'value' => $value
    				);
    				break;	
				case "title" :
     				$this->search_infos[] =array(
    					'id' =>$this->entityType == "rft" ? $this->crit_id['title'] : $this->crit_id['book_title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
				case "tpages" :
     				if($this->entityType == "rft"){
						$this->search_infos[] =array(
    						'id' => $this->crit_id['tpages'],
    						'op' => "BOOLEAN",
    						'value' => $value
    					); 
					}
    				break;
			}
		}		
	}
	
	function getCommonInfos(){	
		$this->infos[$this->entityType.'_val_fmt']=$this->uri;
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "205" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//mention d'�dition
							case "a" :
								$this->infos[$this->entityType.'.edition'] = $s['value'];
								break;
						}
					}
					break;
				case "225" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.series'] = $s['value'];
								break;
							case "x" :
								$this->infos[$this->entityType.'.issn'] = $s['value'];
								break;
						}
					}
					break;
				case "700" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.aulast'] = $s['value'];
								break;
							case "b" :
								$this->infos[$this->entityType.'.aufirst'] = $s['value'];
								break;
						}
					}				
					break;
				case "701" :
					if(!$this->infos[$this->entityType.'.au']) $this->infos[$this->entityType.'.au']=array();
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.au'][] = $last.($first ? ", $first" : "");	
					break;
				case "702" :
					if(!$this->infos[$this->entityType.'.au']) $this->infos[$this->entityType.'.au']=array();
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.au'][] = $last.($first ? ", $first" : "");	
					break;
				case "710" :
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.aucorp'] = $last.($first ? ", $first" : "");	
					break;	
				case "711" :
					if(!$this->infos[$this->entityType.'.aucorp']){
						$last = $first = "";
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$last = $s['value'];
									break;
								case "b" :
									$first = $s['value'];
									break;
							}
						}
						$this->infos[$this->entityType.'.aucorp'][] = $last.($first ? ", $first" : "");	
					}
					break;
				case "712" :
					if(!$this->infos[$this->entityType.'.aucorp']){
						$last = $first = "";
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$last = $s['value'];
									break;
								case "b" :
									$first = $s['value'];
									break;
							}
						}
						$this->infos[$this->entityType.'.aucorp'][] = $last.($first ? ", $first" : "");	
					}
					break;				
			}
		}
	}
	
	function getMonoInfos(){
		$this->infos[$this->entityType.'.genre'] = "book";
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "010" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.isbn'] = $s['value'];
								break;
						}
					}	
					break;
				case "200" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//titre
							case "a" :
								$this->infos[$this->entityType.'.title'] = $s['value'];
								break;
						}
					}
					break;
				case "210" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//lieu de publication
							case "a" :
								$this->infos[$this->entityType.'.place'] = $s['value'];
								break;
							//�diteur
							case "c" :
								$this->infos[$this->entityType.'.pub'] = $s['value'];
								break;
							//date de publication
							case "d" :
								$this->infos[$this->entityType.'.date'] = $s['value'];
								break;	
						}
					}
					break;
				case "215":
					foreach($f['s'] as $s){
						switch($s['c']){
							//nb pages
							case "a" :
								$this->infos[$this->entityType.'.tpages'] = $s['value'];
							break;
						}
					}
					break;
			}
		}  	
	}

	function getChapterInfos(){
		$this->infos[$this->entityType.'.genre'] = "bookitem";
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "200" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//titre
							case "a" :
								$this->infos[$this->entityType.'.atitle'] = $s['value'];
								break;
						}
					}
					break;
				case "210" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//lieu de publication
							case "a" :
								$this->infos[$this->entityType.'.place'] = $s['value'];
								break;
							//�diteur
							case "c" :
								$this->infos[$this->entityType.'.pub'] = $s['value'];
								break;
							//date de publication
							case "d" :
								$this->infos[$this->entityType.'.date'] = $s['value'];
								break;	
						}
					}
					break;
				case "215":
					foreach($f['s'] as $s){
						switch($s['c']){
							//nb pages
							case "a" :
								$this->infos[$this->entityType.'.pages'] = $s['value'];
							break;
						}
					}
					break;
				case "463" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//titre livre
							case "t" :
								$this->infos[$this->entityType.'.title'] = $s['value'];
							break;
							//titre livre
							case "y" :
								$this->infos[$this->entityType.'.isbn'] = $s['value'];
							break;
						}
					}
					break;					
			}
		}  	
		
	}
}

class openurl_descriptor_byval_kev_mtx_journal extends openurl_descriptor_byval_kev_mtx{

    function openurl_descriptor_byval_kev_mtx_journal($notice) {
    	parent::openurl_descriptor_byval_kev_mtx($notice);
    	$this->uri = $this->uri.":journal";
    } 
	
	function serialize($debug=false){
		$this->infos = array();
		$this->getCommonInfos();
		switch($this->notice['bl']['value'].$this->notice['hl']['value']){
			case "s1" :
				$this->getSerialInfos();
				break;
			case "s2" :
				$this->getBulletinInfos();
				break;
			case "a2" :
			default :
				$this->getArticleInfos();
				break;
		}
		if($debug) highlight_string("kev_mtx Journal (by_val):".print_r($this->infos,true));
		return parent::serialize();
	}
    
	function unserialize($infos){
    	$this->infos = $infos;
    	$this->unserializeCommon();
    	switch($this->infos[$this->entityType.".genre"]){
    		case "journal" :
      			if($this->entityType=="rft"){
    				$this->search_infos[]= array(
    					'id' => $this->crit_id['typdoc'],
    					'op' => "EQ",
    					'value' => "s"
     				); 
      			}
     			$this->unserializeSerial();  			
    			break;
    		case "issue" :
    			if($this->entityType=="rft"){
	    			$this->search_infos[]= array(
	    					'id' => $this->crit_id['typdoc'],
	    					'op' => "EQ",
	    					'value' => "b"
	     			);    			
    			}
     			$this->unserializeBulletin();
    			break;  
    		case "article" :
    		default :
    			if($this->entityType=="rft"){
	    			$this->search_infos[]= array(
	    					'id' => $this->crit_id['typdoc'],
	    					'op' => "EQ",
	    					'value' => "a"
	     			);  
    			}
     			$this->unserializeArticle();    			
    			break;
    	}
    }
    
 	function unserializeCommon(){
 		$aut = array();
		foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
				case "aulast" :
    			case "aufirst" :
    				$aut[]=$value;	
    				break;
     			case "au" :
     				$this->search_infos[]= array(
    					'id' => $this->crit_id['author'],
    					'op' => "BOOLEAN",
    					'value' => $value
     				);
    				break;
    			case "aucorp" :
      				$this->search_infos[]= array(
    					'id' => $this->crit_id['author_corp'],
    					'op' => "BOOLEAN",
    					'value' => $value
     				);   				
    				break;	
    			case "date" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['date_parution'],
    					'op' => "EQ",
    					'value' => $value
    				);   				
    				break;
			}
		}
 		if(count($aut)){
      		$this->search_infos[]= array(
    			'id' => $this->crit_id['first_author'],
    			'op' => "BOOLEAN",
    			'value' => implode(' ',$aut)
     		);   				
 		}
    }   
    
    function unserializeSerial(){
    	foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
    			case "jtitle" :
     				 $this->search_infos[] =array(
    					'id' => $this->entityType == "rft" ? $this->crit_id['title'] : $this->crit_id['serial_title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;	
     			case "issn" :
    				 $this->search_infos[] =array(
    					'id' => $this->entityType == "rft" ? $this->crit_id['issn'] : $this->crit_id['parent_issn'],
    					'op' => "STARTWITH",
    					'value' => $value
    				);  		
			}
    	} 	
    }
    
    function unserializeBulletin(){
    	foreach($this->infos as $key => $value){
    		
			switch(str_replace($this->entityType.".","",$key)){
    			case "issue" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
       			case "part" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['num_bull'],
    					'op' => "CONTAINS",
    					'value' => $value
    				);   				
    				break;			
			}
    	}    	
    }

    function unserializeArticle(){
       	foreach($this->infos as $key => $value){
			switch(str_replace($this->entityType.".","",$key)){
    			case "atitle" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['title'],
    					'op' => "BOOLEAN",
    					'value' => $value
    				);   				
    				break;
       			case "part" :
     				 $this->search_infos[] =array(
    					'id' => $this->crit_id['num_bull'],
    					'op' => "CONTAINS",
    					'value' => $value
    				);
    				break;
			}
    	}   	
    }    
	
	function getCommonInfos(){	
		$this->infos[$this->entityType.'_val_fmt']=$this->uri;
		$autcoll = array();
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "700" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.aulast'] = $s['value'];
								break;
							case "b" :
								$this->infos[$this->entityType.'.aufirst'] = $s['value'];
								break;
						}
					}				
					break;
				case "701" :
					if(!$this->infos[$this->entityType.'.au']) $this->infos[$this->entityType.'.au']=array();
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.au'][] = $last.($first ? ", $first" : "");	
					break;
				case "702" :
					if(!$this->infos[$this->entityType.'.au']) $this->infos[$this->entityType.'.au']=array();
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.au'][] = $last.($first ? ", $first" : "");	
					break;
				case "710" :
					$last = $first = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$last = $s['value'];
								break;
							case "b" :
								$first = $s['value'];
								break;
						}
					}
					$this->infos[$this->entityType.'.aucorp'] = $last.($first ? ", $first" : "");	
					break;	
				case "711" :
					if(!$this->infos[$this->entityType.'.aucorp']){
						$last = $first = "";
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$last = $s['value'];
									break;
								case "b" :
									$first = $s['value'];
									break;
							}
						}
						$this->infos[$this->entityType.'.aucorp'][] = $last.($first ? ", $first" : "");	
					}
					break;
				case "712" :
					if(!$this->infos[$this->entityType.'.aucorp']){
						$last = $first = "";
						foreach($f['s'] as $s){
							switch($s['c']){
								case "a" :
									$last = $s['value'];
									break;
								case "b" :
									$first = $s['value'];
									break;
							}
						}
						$this->infos[$this->entityType.'.aucorp'][] = $last.($first ? ", $first" : "");	
					}
					break;				
			}
		}
	}
	
	function getSerialInfos(){
		$this->infos[$this->entityType.'.genre'] = "journal";	
		$publisher = "";
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "010" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.issn'] = $s['value'];
								break;							
						}
					}
					break;
				case "200" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "a" :
								$this->infos[$this->entityType.'.jtitle'] = $s['value'];
								break;							
						}
					}
					break;
				case "210" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "c" :
								$publisher = $s['value'];
								break;							
						}
					}
					break;						
			}
		}
		if($publisher && !$this->infos[$this->entityType.'.aucorp']) $this->infos[$this->entityType.'.aucorp'] = $publisher;
	}

	function getBulletinInfos(){
		$this->infos[$this->entityType.'.genre'] = "issue";
		$lib = $btitle = "";
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "200" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "i" :
								$btitle = $s['value'];
								break;
							case "h" :
								$this->infos[$this->entityType.'.part'] = $s['value'];
								break;
						}
					}
					break;
				case "210" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "h" :
								$this->infos[$this->entityType.'.date'] = $s['value'];
								break;
							case "d" :
								$lib = $s['value'];
								break;
						}
					}	
					break;
				case "215" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//pagination
							case "a" :
								$this->infos[$this->entityType.'.pages'] = $s['value'];
								break;
						}
					}
					break;
				//lien avec le p�rio pour un article
				case "461" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "t" :
								$this->infos[$this->entityType.'.jtitle'] = $s['value'];
								break;
						}
					}					
					break;
			}
		}
		if($btitle) $issue = $btitle.($lib ? " - ".$lib : ""); 
		else $issue = $lib;
		$this->infos[$this->entityType.'.issue'] = $issue;						
	}
	
	function getArticleInfos(){
		$this->infos[$this->entityType.'.genre'] = "article";
		foreach($this->notice['f'] as $f){
			switch($f['c']){
				case "200" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//titre de l'article
							case "a" :
								$this->infos[$this->entityType.'.atitle'] = $s['value'];
								break;
						}
					}
					break;
				case "215" :
					foreach($f['s'] as $s){
						switch($s['c']){
							//pagination
							case "a" :
								$this->infos[$this->entityType.'.pages'] = $s['value'];
								break;
						}
					}
					break;	
				//lien avec le p�rio pour un article
				case "461" :
					foreach($f['s'] as $s){
						switch($s['c']){
							case "t" :
								$this->infos[$this->entityType.'.jtitle'] = $s['value'];
								break;
						}
					}					
					break;
				//lien avec le bulletin
				case "463" :
					$lib = $btitle = "";
					foreach($f['s'] as $s){
						switch($s['c']){
							case "d" :
								$this->infos[$this->entityType.'.date'] = $s['value'];
								break;
							case "e" :
								$lib= $s['value'];		
								break;
							case "t" :
								$btitle= $s['value'];		
								break;
							case "v" :
								$this->infos[$this->entityType.'.part'] = $s['value'];
								break;						
						}
					}
					if($btitle){
						$issue = $btitle.($lib ? " - ".$lib : ""); 
					}else $issue = $lib;
					$this->infos[$this->entityType.'.issue'] = $issue;			
					break;
			}
		}
	}
}

class openurl_descriptor_byval_kev_mtx_service_type extends openurl_descriptor_byval_kev_mtx{
	var $allowedServices = array();	//tableau autorisant ou non les services
	
    function openurl_descriptor_byval_kev_mtx_service_type($allowedServices=array()) {
    	parent::openurl_descriptor_byval_kev_mtx();
    	$this->uri = $this->uri.":sch_svc";
    	$this->allowedServices = $allowedServices;
    }   
    
    function serialize($debug=false){
    	$this->infos[$this->entityType.'_val_fmt']=$this->uri;
    	foreach($this->allowedServices as $key => $value){
			$this->infos[$this->entityType.'.'.$key]= $value != 0 ? "yes" : "no";
    	}
		if($debug) highlight_string("Service Type (by_val):".print_r($this->infos,true));
		return parent::serialize();
    }
    
    function unserialize($infos){
    	$this->infos = $infos;
    }
}

class openurl_descriptor_identifier_kev_mtx_requester extends openurl_descriptor_identifier_kev_mtx{
	
	function openurl_descriptor_identifier_kev_mtx_requester(){
		parent::openurl_descriptor_identifier_kev_mtx();
	}
	
	function serialize($debug=false){
		$this->infos = array();
		$this->infos[$this->entityType.'_id'] = $this->adr;
		if($debug) highlight_string("Resolver Type (identifier):".print_r($this->infos,true));
		return parent::serialize($this->infos);
	}
	
    function unserialize($infos){
    	$this->infos = $infos;
    }
}

class openurl_descriptor_byref_kev_mtx extends openurl_descriptor_byref{
	var $notice_id;
	var $source_id;
	var $byref_url;
	
	function openurl_descriptor_byref_kev_mtx($notice_infos,$source_id,$byref_url){
		parent::openurl_descriptor_byref(array());
		
		for ($i=0 ; $i<count($notice_infos['f']) ; $i++){
			switch($notice_infos['f'][$i]['c']){
				case "001" :
					$this->notice_id = $notice_infos['f'][$i]['value'];
					break 2;
			}
		}
		$this->source_id = $source_id;
		$this->uri = $this->uri.":kev:mtx";
		$this->byref_url = $byref_url;
	}
	function serialize($tab){
		return openurl_serialize_kev_mtx::serialize($tab);
	}
}

class openurl_descriptor_byref_kev_mtx_book extends openurl_descriptor_byref_kev_mtx{
	
	function openurl_descriptor_byref_kev_mtx_book($notice_infos,$source_id,$byref_url){
		parent::openurl_descriptor_byref_kev_mtx($notice_infos,$source_id,$byref_url);
		$this->uri = $this->uri.":book";
	}
	
	function serialize(){
		$this->infos = array();
		$this->infos[$this->entityType.'_ref_fmt'] = $this->uri;
		$this->infos[$this->entityType.'_ref'] = $this->byref_url."?in_id=".$this->source_id."&notice_id=".$this->notice_id."&uri=".$this->uri."&entity=".$this->entityType;
		return parent::serialize($this->infos);
	}
}

class openurl_descriptor_byref_kev_mtx_journal extends openurl_descriptor_byref_kev_mtx{
	
	function openurl_descriptor_byref_kev_mtx_journal($notice_infos,$source_id,$byref_url){
		parent::openurl_descriptor_byref_kev_mtx($notice_infos,$source_id,$byref_url);
		$this->uri = $this->uri.":journal";
	}
	
	function serialize(){
		global $opac_url_base;
		$this->infos = array();
		$this->infos[$this->entityType.'_ref_fmt'] = $this->uri;
		$this->infos[$this->entityType.'_ref'] = $this->byref_url."?in_id=".$this->source_id."&notice_id=".$this->notice_id."&uri=".$this->uri."&entity=".$this->entityType;
		return parent::serialize($this->infos);
	}
}