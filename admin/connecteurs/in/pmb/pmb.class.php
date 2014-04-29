<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb.class.php,v 1.4 2012-11-23 10:32:12 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path,$base_path, $include_path;
require_once($class_path."/connecteurs.class.php");
require_once($class_path."/jsonRPCClient.php");

define("JSONRPC",1);
define("SOAP",2);

class xml_dom_pmb {
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
	function xml_dom_pmb($xml,$charset="iso-8859-1") {
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
	 a/b/id@c[3]	Le troisi�me noeud �l�ment c (<c id="2">Texte 2</c>). L'attribut est ignor�
	 
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
	 * @return array Tableau des donn�es s�rialis�es ou des valeurs de l'attribut, \b false si le chemin n'existe pas
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

class pmb extends connector {
	var $source_id;		
	var $search_id;
	var $url;					//url distante 
	var $username;				//identifiant pour la recherche distante
	var $password;				//mot de passe pour la recherche distante
	var $del_old;				//Supression ou non des notices dej� existantes
	var $current_protocole;		//protocole utilis� en cours	
	var $searchindexes;			//Liste des indexes de recherche possibles pour le site
	var $current_searchindex;	//Num�ro de l'index de recherche de la classe
	var $match_index;			//Type de recherche (power ou simple)
	
	function pmb($connector_path="") {
    	parent::connector($connector_path);

    }
    
    function get_id() {
    	return "pmb";
    }
    
    //Est-ce un entrepot ?
	function is_repository() {
		return 2;
	}
   
	function unserialize_source_params($source_id) {
    	$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			$params["PARAMETERS"]=$vars;
		}
		return $params;
    }
	
	function source_get_property_form($source_id) {
    	global $charset;
    	// m�thode de la classe parente
    	$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}

		if (!$max_return) $max_return=100;
		$form="
		<script type='text/javascript'>var old_search_index='search_index_".$url."'</script>
		<div class='row'>
			<div class='colonne3'>
				<label for='url'>".$this->msg["pmb_site"]."</label>
			</div>
			<div class='colonne_suite'>
				<input name='url' id='url' class='saisie-50em' type='text' value='".htmlentities($url,ENT_QUOTES,$charset)."'/>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='protocole'>".$this->msg["pmb_protocole"]."</label>
			</div>
			<div class='colonne_suite'>
				<input name='protocole' id='protocole' class='' type='radio' value='".SOAP."' ".
					//on coche l'option d�j� enregistr�e. Par d�faut l'option Json est coch�e
					($protocole == SOAP ? " checked " : $protocole = JSONRPC)."'/>".$this->msg["pmb_prtcl_soap"]."
				<input name='protocole' id='protocole' class='' type='radio' value='".JSONRPC."'".
					($protocole == JSONRPC ? " checked " : $protocole = JSONRPC)."/>".$this->msg["pmb_prtcl_json"]."
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='max_return'>".$this->msg["pmb_max_return"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='text' name='max_return' id='max_return' class='saisie-5em' value='".htmlentities($max_return,ENT_QUOTES,$charset)."' size='10'/>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='display_items'>".$this->msg["pmb_display_items"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='checkbox' name='display_items' id='display_items' class='' value='".($display_items=="0" ? "0" : "1")."'".
				($display_items == "1" ? " checked " : $display_items = "0").
				"onchange='checkItems();' />
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='authentification'>".$this->msg["pmb_authentification"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='checkbox' name='authentification' id='authentification' class='' value='".($authentification=="0" ? "0" : "1")."'".
				($authentification == "1" ? " checked " : $authentification = "0").
				//on d�sactive login et mot de passe en fonction du checkbox
				"onchange='checkAuth();' />
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for=''>".$this->msg["pmb_username"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='text' name='auth_login' id='auth_login' class='saisie-5em' value='".htmlentities($username,ENT_QUOTES,$charset)."'".
				($authentification == "0" ? " disabled " : "")."'/>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for=''>".$this->msg["pmb_password"]."</label>
			</div>
			<div class='colonne_suite'>
				<input type='text' name='auth_password' id='auth_password' class='saisie-5em' value='".htmlentities($password,ENT_QUOTES,$charset)."'".
				($authentification == "0" ? " disabled " : "")."'/>
			</div>
		</div>

		<div class='row'></div>
		<script type='text/javascript'>
		
		function checkAuth(){
				if(document.getElementById('authentification').value == '1'){
					document.getElementById('authentification').value = '0';
					document.getElementById('auth_login').disabled = true;
					document.getElementById('auth_password').disabled = true;
				}else {
					document.getElementById('authentification').value = '1';
					document.getElementById('auth_login').disabled = false;
					document.getElementById('auth_password').disabled = false;
				}
			}
		function checkItems() {
			if(document.getElementById('display_items').value == '1'){
				document.getElementById('display_items').value = '0';
			} else {
				document.getElementById('display_items').value = '1';
			}
		}
		</script>		
		";
		return $form;
    }
    
//Fonction de recherche
	function search($source_id,$query,$search_id) {
		global $charset;
		global $pmb_curl_proxy;	
		global $base_path;
			
		$this->error=false;
		$this->error_message="";
		
		$params=$this->get_source_params($source_id);
		$this->fetch_global_properties();
		if ($params["PARAMETERS"]) {
			//Affichage du formulaire avec $params["PARAMETERS"]
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}
		
		// on affecte la valeur 100 au nombre de notices retourn�es si aucune valeur n'a �t� affect�e
		if (!$max_return) $max_return=100;
		
		$nb_pages=floor($max_return/40);
		$stop=false;
		
		//construit le tableau pour la recherche multi-crit�re
		foreach ($query as $i=>$q) {
			$t = array();
			$t["inter"]= $q->inter;
			$uid=explode("_",$q->uid);
			if ($uid[0]=="f")
				$t["field"]= $uid[1];
			else
				$t["field"] = $q->uid;
			$t["operator"]= $q->op;
			if (count($q->values)>1) {
				$vi=0;
				foreach ($q->values as $value) {
					$t["value"][$vi]= $value;
					$vi++;
				}	
			} else $t["value"]=$q->values[0];
			$tab_query[$i]=$t;
		}
		
		// on v�rifie d'abord les param�tres du formulaires
		if ($display_items == "1") $items = true;
		else $items = false;
		//on v�rifie le protocole � utiliser...
		$this->current_protocole = $protocole;

		switch ($this->current_protocole) {
			case JSONRPC:
				$ws=new jsonRPCClient($url);
				$res = $ws->pmbesSearch_advancedSearch($query[0]->sc_type,$tab_query);
				//Si il y a des r�sultats
				if ($res["nbResults"]) {
				  	//Si le nombre de notices est > � la valeur max alors on attribue � nb la valeur max
				  	$nb=$res["nbResults"]>$max_return ? $max_return : $res["nbResults"];
				  	//On va chercher le contenu des notices au format pmb_xml_unimarc
				  	$notices=$ws->pmbesSearch_fetchSearchRecords($res["searchId"],0,$nb,"pmb_xml_unimarc","utf-8",false,$items);
				  			
				  	for ($i=0; $i<$nb; $i++) {
				 		$this->rec_records($notices[$i], $source_id, $search_id);
				  	}
				} 
				break;
			case SOAP:
				$ws=new SoapClient($url."&wsdl");
				$res = $ws->pmbesSearch_advancedSearch($query[0]->sc_type,$tab_query);
		
				//Si il y a des r�sultats
				if ($res->nbResults) {
				  	//Si le nombre de notices est > � la valeur max alors on attribue � nb la valeur max
				  	$nb=$res->nbResults>$max_return ? $max_return : $res->nbResults;
				  	//On va chercher le contenu des notices au format pmb_xml_unimarc
				  	$notices=$ws->pmbesSearch_fetchSearchRecords($res->searchId,0,$nb,"pmb_xml_unimarc","utf-8",false,$items);
				  
				  	for ($i=0; $i<$nb; $i++) {
				 		$this->rec_records($notices[$i], $source_id, $search_id);
				  	}
				}
				break;
		}
	}
	
	function rec_records($notice_xml_uni, $source_id, $search_id) {
		global $charset,$base_path;
	
		if ($notice_xml_uni && $this->current_protocole) {
			// l'instanciation est diff�rente selon les protocoles
			if ($this->current_protocole == JSONRPC) {
				$rec_uni_dom = new xml_dom_pmb($notice_xml_uni["noticeContent"],"utf-8");
			} else {
				$rec_uni_dom = new xml_dom_pmb($notice_xml_uni->noticeContent,"iso-8859-1");
			}
			$the_notice=$rec_uni_dom->get_nodes("notice");
						
			if (is_array($notice_xml_uni["noticeExplNums"]) && count($notice_xml_uni["noticeExplNums"]) ) {
				$i = count($the_notice[0]['CHILDS'])*1;
				foreach($notice_xml_uni["noticeExplNums"] as $k=>$docnum) {
							
					$the_notice[0]['CHILDS'][$i]=array('NAME'		=>'f',
														'ATTRIBS'	=>array('c'	=>'897'),
														'TYPE'		=>'1',
														'CHILDS'	=>array(0	=>array('NAME'		=>'s',
																						'ATTRIBS'	=>array('c'	=>'a'),
																						'TYPE'		=>'1',
																						'CHILDS'	=>array(0	=>array('DATA'	=>$docnum['url'],
																														'TYPE'	=>'2'
																												)
																									)
																				),
																			1	=>array('NAME'		=>'s',
																						'ATTRIBS'	=>array('c'	=>'b'),
																						'TYPE'		=>'1',
																						'CHILDS'	=>array(0	=>array('DATA'	=>$docnum['name'],
																														'TYPE'	=>'2'
																												)
																									)
																				),
																			2	=>array('NAME'		=>'s',
																						'ATTRIBS'	=>array('c'	=>'f'),
																						'TYPE'		=>'1',
																						'CHILDS'	=>array(0	=>array('DATA'	=>$docnum['filename'],
																														'TYPE'	=>'2'
																												)
																									)
																				)
																	)
													);
					$i++;
				}
			}		
				
			if($the_notice) {
				foreach ($the_notice as $anotice) {
					$this->rec_record($rec_uni_dom,$anotice, $source_id, $search_id);
				}
			}	
		}
	}
	
	function rec_record($rec_uni_dom,$notice, $source_id, $search_id) {
		global $charset,$base_path;
		
			$date_import=date("Y-m-d H:i:s",time());

			//Initialisation
			$ref="";
			$ufield="";
			$usubfield="";
			$field_order=0;
			$subfield_order=0;
			$value="";
				
			$fs=$rec_uni_dom->get_nodes("f", $notice);
			//Recherche du 001
			for ($i=0; $i<count($fs); $i++) {
				if ($fs[$i]["ATTRIBS"]["c"]=="001") {
					$ref=$rec_uni_dom->get_datas($fs[$i]);
					break;
				}
			}

			//Mise � jour 
			if ($ref) {
				//Si conservation des anciennes notices, on regarde si elle existe
				if (!$this->del_old) {
					$requete="select count(*) from entrepot_source_".$source_id." where ref='".addslashes($ref)."' and search_id='".addslashes($search_id)."'";
					$rref=mysql_query($requete);
					if ($rref) $ref_exists=mysql_result($rref,0,0);
				}
				//Si pas de conservation des anciennes notices, on supprime
				if ($this->del_old) {
					$requete="delete from entrepot_source_".$source_id." where ref='".addslashes($ref)."' and search_id='".addslashes($search_id)."'";
					mysql_query($requete);
				}
				//Si pas de conservation ou ref�rence inexistante
				if (($this->del_old)||((!$this->del_old)&&(!$ref_exists))) {
					//Insertion de l'ent�te
					$n_header["rs"]=$rec_uni_dom->get_value("notice/rs");
					$n_header["ru"]=$rec_uni_dom->get_value("notice/ru");
					$n_header["el"]=$rec_uni_dom->get_value("notice/el");
					$n_header["bl"]=$rec_uni_dom->get_value("notice/bl");
					$n_header["hl"]=$rec_uni_dom->get_value("notice/hl");
					$n_header["dt"]=$rec_uni_dom->get_value("notice/dt");

					
					//R�cup�ration d'un ID
					$requete="insert into external_count (recid, source_id) values('".addslashes($this->get_id()." ".$source_id." ".$ref)."', ".$source_id.")";
					$rid=mysql_query($requete);
					if ($rid) $recid=mysql_insert_id();
					
					foreach($n_header as $hc=>$code) {
						$requete="insert into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid,search_id) values(
						'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".$date_import."',
						'".$hc."','',-1,0,'".addslashes($code)."','',$recid,'".addslashes($search_id)."')";
						mysql_query($requete);
					}
					for ($i=0; $i<count($fs); $i++) {
						$ufield=$fs[$i]["ATTRIBS"]["c"];
						$field_order=$i;
						$ss=$rec_uni_dom->get_nodes("s",$fs[$i]);
						
						if (is_array($ss)) {
							for ($j=0; $j<count($ss); $j++) {
								$usubfield=$ss[$j]["ATTRIBS"]["c"];
								$value=$rec_uni_dom->get_datas($ss[$j]);
								$subfield_order=$j;
								$requete="insert into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid,search_id) values(
								'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
								'".addslashes($ufield)."','".addslashes($usubfield)."',".$field_order.",".$subfield_order.",'".addslashes($value)."',
								' ".addslashes(strip_empty_words($value))." ',$recid,'".addslashes($search_id)."')";
								if ($charset != "utf-8") {
									$requete = utf8_decode($requete);
								}
								mysql_query($requete);
							}
						} else {
							$value=$rec_uni_dom->get_datas($fs[$i]);
							$requete="insert into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid,search_id) values(
							'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
							'".addslashes($ufield)."','".addslashes($usubfield)."',".$field_order.",".$subfield_order.",'".addslashes($value)."',
							' ".addslashes(strip_empty_words($value))." ',$recid,'".addslashes($search_id)."')";
							if ($charset != "utf-8") {
								$requete = utf8_decode($requete);
							}
							mysql_query($requete);
						}
					}
				}
			}
		}	
    
	function make_serialized_source_properties($source_id) {
    	global $url,$response_group,$search_index,$max_return,$protocole, $authentification,$display_items;
    	$t["url"]=stripslashes($url);
    	$t["protocole"]=$protocole;
    	$t["response_group"]=$response_group;
  		$t["search_index"]=$search_index;
  		$t["max_return"]=$max_return;
  		$t["authentification"]=$authentification;
  		$t["display_items"]=$display_items;
		$this->sources[$source_id]["PARAMETERS"]=serialize($t);
	}
	
	function cancel_maj($source_id) {
		return false;
	}
	
	function break_maj($source_id) {
		return false;
	}
	
	function maj_entrepot($source_id,$callback_progress="",$recover=false,$recover_env="") {
		return 0;
	}

	
    
	//Recupere la liste des periodiques sous forme de tableau
	function fetch_serial_list($source_id,$opac_user_id=-1) {
		
		global $charset;
		global $pmb_curl_proxy;	
		global $base_path;
	
		$params=$this->get_source_params($source_id);
		$this->fetch_global_properties();
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}

		//on v�rifie le protocole � utiliser...
		$this->current_protocole = $protocole;

		$ret = array();
		switch ($this->current_protocole) {
			case JSONRPC:
				$ws=new jsonRPCClient($url);
				break;
			case SOAP:				
				$ws=new SoapClient($url."&wsdl");
				break;
		}
		if ($ws) $res = $ws->pmbesNotices_fetchSerialList($opac_user_id);
		if (count($res)) {
			switch ($this->current_protocole) {
				case JSONRPC:
					$ret=$this->object_to_array($res);
					break;
				case SOAP:
					$ret=$this->object_to_array($res);		
					break;
			}
		}
		return $ret;
	}

    
	//Recupere une liste de notices
	function fetch_notice_list_full($source_id,$ids=array(), $rec_format='raw_array_assoc',$rec_charset='utf-8',$w_links=false,$record=false) {
		
		global $charset;
		global $pmb_curl_proxy;	
		global $base_path;
	
		$params=$this->get_source_params($source_id);
		$this->fetch_global_properties();
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}

		//on v�rifie le protocole � utiliser...
		$this->current_protocole = $protocole;

		$ret = array();
		switch ($this->current_protocole) {
			case JSONRPC:
				$ws=new jsonRPCClient($url);
				break;
			case SOAP:				
				$ws=new SoapClient($url."&wsdl");
				break;
		}
		if ($ws) $res = $ws->pmbesNotices_fetchNoticeListFull($ids,$rec_format,$rec_charset,$w_links);
		if (count($res)) {
			switch ($this->current_protocole) {
				case JSONRPC:
					$ret=$this->object_to_array($res);
					break;
				case SOAP:
					$ret=$this->object_to_array($res);
					break;
			}
		}
		if($record) {
			foreach($ret as $notice) {
				$this->rec_records($notice, $source_id, $search_id);
			}
		}
		return $ret;
	}

	//Recupere une liste de bulletins
	function fetch_bulletin_list_full($source_id,$ids=array(), $rec_format='raw_array_assoc',$rec_charset='utf-8') {
		
		global $charset;
		global $pmb_curl_proxy;	
		global $base_path;
	
		$params=$this->get_source_params($source_id);
		$this->fetch_global_properties();
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			foreach ($vars as $key=>$val) {
				global $$key;
				$$key=$val;
			}	
		}

		//on v�rifie le protocole � utiliser...
		$this->current_protocole = $protocole;

		$ret = array();
		switch ($this->current_protocole) {
			case JSONRPC:
				$ws=new jsonRPCClient($url);
				break;
			case SOAP:				
				$ws=new SoapClient($url."&wsdl");
				break;
		}
		if ($ws) $res = $ws->pmbesNotices_fetchBulletinListFull($ids,$rec_format,$rec_charset);
		if (count($res)) {
			switch ($this->current_protocole) {
				case JSONRPC:
					$ret=$this->object_to_array($res);
					break;
				case SOAP:
					$ret=$this->object_to_array($res);
					break;
			}
		}
		return $ret;
	}
	
	function object_to_array($obj) {
		if (is_object($obj)) {
			$obj = (array) $obj;
		}
		if (is_array($obj) && count($obj)) {
			foreach($obj as $k=>$v) {
				$obj[$k]=$this->object_to_array($v);
			}
		} 
		return $obj;
	}

}