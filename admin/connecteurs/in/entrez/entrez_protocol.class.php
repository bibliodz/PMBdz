<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: entrez_protocol.class.php,v 1.4 2009-11-12 15:25:26 gueluneau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path,$base_path, $include_path;

class xml_dom_entrez {
	var $xml;				/*!< XML d'origine */
	var $charset;			/*!< Charset courant (iso-8859-1 ou utf-8) */
	/**
	 * \brief Arbre des noeuds du document
	 * 
	 * L'arbre est compos� de noeuds qui ont la structure suivante :
	 * \anchor noeud
	 * \verbatim
	 $noeud = array(
	 	NAME	=> Nom de l'�l�ment pour un noeud de type �l�ment (TYPE = 1)
	 	ATTRIBS	=> Tableau des attributs (nom => valeur)
	 	TYPE	=> 1 = Noeud �l�ment, 2 = Noeud texte
	 	CHILDS	=> Tableau des noeuds enfants
	 )
	 \endverbatim
	 */
	var $tree; 
	var $error=false; 		/*!< Signalement d'erreur : true : erreur lors du parse, false : pas d'erreur */
	var $error_message=""; 	/*!< Message d'erreur correspondant � l'erreur de parse */
	var $depth=0;			/*!< \protected */
	var $last_elt=array();	/*!< \protected */
	var $n_elt=array();		/*!< \protected */
	var $cur_elt=array();	/*!< \protected */
	var $last_char=false;	/*!< \protected */
	
	/**
	 * \protected
	 */
	function close_node() {
		$this->last_elt[$this->depth-1]["CHILDS"][]=$this->cur_elt;
		$this->last_char=false;
		$this->cur_elt=$this->last_elt[$this->depth-1];
		$this->depth--;
	}
	
	/**
	 * \protected
	 */
	function startElement($parser,$name,$attribs) {
		if ($this->last_char) $this->close_node();
		$this->last_elt[$this->depth]=$this->cur_elt;
		$this->cur_elt=array();
		$this->cur_elt["NAME"]=$name;
		$this->cur_elt["ATTRIBS"]=$attribs;
		$this->cur_elt["TYPE"]=1;
		$this->last_char=false;
		$this->depth++;
	}
	
	/**
	 * \protected
	 */
	function endElement($parser,$name) {
		if ($this->last_char) $this->close_node();
		$this->close_node();
	}
	
	/**
	 * \protected
	 */
	function charElement($parser,$char) {
		if ($this->last_char) $this->close_node();
		$this->last_char=true;
		$this->last_elt[$this->depth]=$this->cur_elt;
		$this->cur_elt=array();
		$this->cur_elt["DATA"].=$char;
		$this->cur_elt["TYPE"]=2;
		$this->depth++;
	}
	
	/**
	 * \brief Instanciation du parser
	 * 
	 * Le document xml est pars� selon le charset donn� et une repr�sentation sous forme d'arbre est g�n�r�e
	 * @param string $xml XML a manipuler
	 * @param string $charset Charset du document XML
	 */
	function xml_dom_entrez($xml,$charset="iso-8859-1") {
		$this->charset=$charset;
		$this->cur_elt=array("NAME"=>"document","TYPE"=>"0");
		
		//Initialisation du parser
		$xml_parser=xml_parser_create($this->charset);
		xml_set_object($xml_parser,$this);
		xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser,"charElement");
		
		if (!xml_parse($xml_parser, $xml)) {
       		$this->error_message=sprintf("XML error: %s at line %d",xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser));
       		$this->error=true;
		}
		$this->tree=$this->last_elt[0];
	}
	
	/**
	 * \anchor path_node
	 * \brief R�cup�ration d'un noeud par son chemin
	 * 
	 * Recherche un noeud selon le chemin donn� en param�tre. Un noeud de d�part peut �tre pr�cis�
	 * @param string $path Chemin du noeud recherch�
	 * @param noeud [$node] Noeud de d�part de la recherche (le noeud doit �tre de type 1)
	 * @return noeud Noeud correspondant au chemin ou \b false si non trouv�
	 * \note Les chemins ont la syntaxe suivante :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Le premier noeud �l�ment c (<c id="0">Texte</c>)
	 a/b/c[2]/d	Le premier noeud �l�ment d du deuxi�me noeud c (<d>Sous texte</d>)
	 a/b/c[3]	Le troisi�me noeud �l�ment c (<c id="2">Texte 2</c>) 
	 a/b/id@c	Le premier noeud �l�ment c (<c id="0">Texte</c>). L'attribut est ignor�
	 a/b/id@c[3]	Le trois�me noeud �l�ment c (<c id="2">Texte 2</c>). L'attribut est ignor�
	 
	 Les attributs ne peuvent �tre cit�s que sur le noeud final.
	 \endverbatim
	 */
	function get_node($path,$node="") {
		if ($node=="") $node=&$this->tree;
		$paths=explode("/",$path);
		for ($i=0; $i<count($paths); $i++) {
			if ($i==count($paths)-1) {
				$pelt=explode("@",$paths[$i]);
				if (count($pelt)==1) { 
					$p=$pelt[0]; 
				} else {
					$p=$pelt[1];
					$attr=$pelt[0];
				}
			} else $p=$paths[$i];
			if (preg_match("/\[([0-9]*)\]$/",$p,$matches)) {
				$name=substr($p,0,strlen($p)-strlen($matches[0]));
				$n=$matches[1];
			} else {
				$name=$p;
				$n=0;
			}
			$nc=0;
			$found=false;
			for ($j=0; $j<count($node["CHILDS"]); $j++) {
				if (($node["CHILDS"][$j]["TYPE"]==1)&&($node["CHILDS"][$j]["NAME"]==$name)) {
					//C'est celui l� !!
					if ($nc==$n) {
						$node=&$node["CHILDS"][$j];
						$found=true;
						break;
					} else $nc++;
				}
			}
			if (!$found) return false;
		}
		return $node;
	}
	
	/**
	 * \anchor path_nodes
	 * \brief R�cup�ration d'un ensemble de noeuds par leur chemin
	 * 
	 * Recherche d'un ensemble de noeuds selon le chemin donn� en param�tre. Un noeud de d�part peut �tre pr�cis�
	 * @param string $path Chemin des noeuds recherch�s
	 * @param noeud [$node] Noeud de d�part de la recherche (le noeud doit �tre de type 1)
	 * @return array noeud Tableau des noeuds correspondants au chemin ou \b false si non trouv�
	 * \note Les chemins ont la syntaxe suivante :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Tous les �l�ments c fils de a/b 
	 a/b/c[2]/d	Tous les �l�ments d fils de a/b et du deuxi�me �l�ment c
	 a/b/id@c	Tous les noeuds �l�ments c fils de a/b. L'attribut est ignor�
	 \endverbatim
	 */
	function get_nodes($path,$node="") {
		$n=0;
		$nodes="";
		while ($nod=$this->get_node($path."[$n]",$node)) {
			$nodes[]=$nod;
			$n++;
		}
		return $nodes;
	}
	
	/**
	 * \brief R�cup�ration des donn�es s�rialis�es d'un noeud �l�ment
	 * 
	 * R�cup�re sous forme texte les donn�es d'un noeud �l�ment :\n
	 * -Si c'est un �l�ment qui n'a qu'un noeud texte comme fils, renvoie le texte\n
	 * -Si c'est un �l�ment qui a d'autres �l�ments comme fils, la version s�rialis�e des enfants est renvoy�e
	 * @param noeud $node Noeud duquel r�cup�rer les donn�es
	 * @param bool $force_entities true : les donn�es sont renvoy�es avec les entit�s xml, false : les donn�es sont renvoy�es sans entit�s
	 * @return string donn�es s�rialis�es du noeud �l�ment
	 */
	function get_datas($node,$force_entities=false) {
		$char="";
		if ($node["TYPE"]!=1) return false;
		//Recherche des fils et v�rification qu'il n'y a que du texte !
		$flag_text=true;
		for ($i=0; $i<count($node["CHILDS"]); $i++) {
			if ($node["CHILDS"][$i]["TYPE"]!=2) $flag_text=false;
		}
		if ((!$flag_text)&&(!$force_entities)) {
			$force_entities=true;
		}
		for ($i=0; $i<count($node["CHILDS"]); $i++) {
			if ($node["CHILDS"][$i]["TYPE"]==2)
				if ($force_entities) 
					$char.=htmlspecialchars($node["CHILDS"][$i]["DATA"],ENT_NOQUOTES,$this->charset);
				else $char.=$node["CHILDS"][$i]["DATA"];
			else {
				$char.="<".$node["CHILDS"][$i]["NAME"];
				if (count($node["CHILDS"][$i]["ATTRIBS"])) {
					foreach ($node["CHILDS"][$i]["ATTRIBS"] as $key=>$val) {
						$char.=" ".$key."=\"".htmlspecialchars($val,ENT_NOQUOTES,$this->charset)."\"";
					}
				}
				$char.=">";
				$char.=$this->get_datas($node["CHILDS"][$i],$force_entities);
				$char.="</".$node["CHILDS"][$i]["NAME"].">";
			}
		}
		return $char;
	}
	
	/**
	 * \brief R�cup�ration des attributs d'un noeud
	 * 
	 * Renvoie le tableau des attributs d'un noeud �l�ment (Type 1)
	 * @param noeud $node Noeud �l�ment duquel on veut les attributs
	 * @return mixed Tableau des attributs Nom => Valeur ou false si ce n'est pas un noeud de type 1
	 */
	function get_attributes($node) {
		if ($node["TYPE"]!=1) return false;
		return $node["ATTRIBUTES"];
	}
	
	/**
	 * \brief R�cup�re les donn�es ou l'attribut d'un noeud par son chemin
	 * 
	 * R�cup�re les donn�es s�rialis�es d'un noeud ou la valeur d'un attribut selon le chemin
	 * @param string $path chemin du noeud recherch�
	 * @param noeud $node Noeud de d�part de la recherche
	 * @return string Donn�e s�rialsi�e ou valeur de l'attribut, \b false si le chemin n'existe pas
	 * \note Exemples de valeurs renvoy�es selon le chemin :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Renvoie : "Texte"
	 a/b/c[2]/d	Renvoie : "Sous texte"
	 a/b/c[2]	Renvoie : "<d>Sous texte</d>"
	 a/b/c[3]	Renvoie : "Texte 2" 
	 a/b/id@c	Renvoie : "0"
	 a/b/id@c[3]	Renvoie : "2"
	 \endverbatim
	 */
	function get_value($path,$node="") {
		$elt=$this->get_node($path,$node);
		if ($elt) {
			$paths=explode("/",$path);
			$pelt=explode("@",$paths[count($paths)-1]);
			if (count($pelt)>1) {
				$a=$pelt[0];
				//Recherche de l'attribut
				if (preg_match("/\[([0-9]*)\]$/",$a,$matches)) {
					$attr=substr($a,0,strlen($a)-strlen($matches[0]));
					$n=$matches[1];
				} else {
					$attr=$a;
					$n=0;
				}
				$nc=0;
				$found=false;
				foreach($elt["ATTRIBS"] as $key=>$val) {
					if ($key==$attr) {
						//C'est celui l� !!
						if ($nc==$n) {
							$value=$val;
							$found=true;
							break;
						} else $nc++;
					}
				}
				if (!$found) $value="";
			} else {
				$value=$this->get_datas($elt);
			}
		}
		return $value;
	}
	
	/**
	 * \brief R�cup�re les donn�es ou l'attribut d'un ensemble de noeuds par leur chemin
	 * 
	 * R�cup�re les donn�es s�rialis�es ou la valeur d'un attribut d'un ensemble de noeuds selon le chemin
	 * @param string $path chemin des noeuds recherch�s
	 * @param noeud $node Noeud de d�part de la recherche
	 * @return array Tableau des donn�es s�rialis�es ou des valeur de l'attribut, \b false si le chemin n'existe pas
	 * \note Exemples de valeurs renvoy�es selon le chemin :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Renvoie : [0]=>"Texte",[1]=>"<d>Sous texte</d>",[2]=>"Texte 2"
	 a/b/c[2]/d	Renvoie : [0]=>"Sous texte"
	 a/b/id@c	Renvoie : [0]=>"0",[1]=>"1",[2]=>"2"
	 \endverbatim
	 */
	function get_values($path,$node="") {
		$n=0;
		while ($elt=$this->get_node($path."[$n]",$node)) {
			$elts[$n]=$elt;
			$n++;
		}
		if (count($elts)) {
			for ($i=0; $i<count($elts); $i++) {
				$elt=$elts[$i];
				$paths=explode("/",$path);
				$pelt=explode("@",$paths[count($paths)-1]);
				if (count($pelt)>1) {
					$a=$pelt[0];
					//Recherche de l'attribut
					if (preg_match("/\[([0-9]*)\]$/",$a,$matches)) {
						$attr=substr($a,0,strlen($a)-strlen($matches[0]));
						$n=$matches[1];
					} else {
						$attr=$a;
						$n=0;
					}
					$nc=0;
					$found=false;
					foreach($elt["ATTRIBS"] as $key=>$val) {
						if ($key==$attr) {
							//C'est celui l� !!
							if ($nc==$n) {
								$values[]=$val;
								$found=true;
								break;
							} else $nc++;
						}
					}
					if (!$found) $values[]="";
				} else {
					$values[]=$this->get_datas($elt);
				}
			}
		}
		return $values;
	}
}

class entrez_request {
	var $database;
	var $request_text;
	var $total_items = 0;
	var $current_item_index = 0;
	var $current_id_list = array();
	var $current_responses = array();
	
	function entrez_request($database, $request_text) {
		$this->database = $database;
		$this->request_text = $request_text;
	}
	
	function get_next_idlist($retmax=100) {
		global $base_path;
		
		//host,port,user,password
		global $pmb_curl_proxy;

		if ($pmb_curl_proxy) {
			$proxies=explode(";",$pmb_curl_proxy);
			$proxy=explode(",",$proxies[0]);
			$proxytable=array('proxy_host'     =>$proxy[0],
                          'proxy_port'     => (int)$proxy[1],
                          'proxy_login'    => $proxy[2],
                          'proxy_password' => $proxy[3]);
		} else $proxytable=array();
		$client = new SoapClient($base_path."/admin/connecteurs/in/entrez/eutils.wsdl",$proxytable);
		$params = array(
			"db" => $this->database,
			"RetStart" => $this->current_item_index,
			"RetMax" => $retmax,
			"term" => $this->request_text
		);
		
		$result = $client->run_eSearch($params);
		if (isset($result->IdList->Id)) {
			$this->current_id_list = is_array($result->IdList->Id) ? $result->IdList->Id : array($result->IdList->Id);
			return true;
		}
		else {
			$this->current_id_list = array();
		}
		return false;
	}
	
	function retrieve_currentidlist_notices() {
		global $base_path;
		
		$current_responses = array();
		if (!$this->current_id_list)
			return; //Pas de liste, pas de fetch
		
		//host,port,user,password
		global $pmb_curl_proxy;
		
		if ($pmb_curl_proxy) {
			$proxies=explode(";",$pmb_curl_proxy);
			$proxy=explode(",",$proxies[0]);
			$proxytable=array('proxy_host'     =>$proxy[0],
                          'proxy_port'     => (int)$proxy[1],
                          'proxy_login'    => $proxy[2],
                          'proxy_password' => $proxy[3],
						  'trace'=>1);
		} else $proxytable=array('trace'=>1);
		
		$client = new SoapClient($base_path."/admin/connecteurs/in/entrez/efetch_pubmed.wsdl", $proxytable);

		//Si un jour ils voudront bien marquer dans leur doc que �a mange les virgules leur truc �a pourrait faire gagner du temps aux developpeurs.
		$params = array(
			"id" => implode(",", $this->current_id_list)
		);

		$result = $client->run_eFetch($params);
		$response_xml = $client->__getLastResponse();
		$current_responses[] = $response_xml;

		$this->current_responses = $current_responses;
	}
	
	function get_current_responses() {
		return $this->current_responses;
	}
}

?>