<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: rdf.class.php,v 1.2 2013-12-02 09:07:25 dbellamy Exp $


if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

require_once ("$class_path/rdf/arc2/ARC2.php");


class rdf {

	public $errors = array(); // Tableau avec les erreurs rencontr�es
	public $config = array(
		  /* db */
		  'db_name' => DATA_BASE,
		  'db_user' => USER_NAME,
		  'db_pwd' => USER_PASS,
		  'db_host' => SQL_SERVER,
		  /* store */
		  'store_name' => "rdfstore",
		  /* stop after 100 errors */
		  'max_errors' => 100,
		  'store_strip_mb_comp_str' => 0
		);
	
	/**
	 * Constructor
	 * @param text $store_name : Pr�fixe utilis� pour le sch�ma rdf
	 * @return void
	 */
	public function __construct ($store_name="",$config=array()) {
		
		if(is_array($config) && count($config)){
			$this->config=$config;
		}
		if(trim($store_name)){
			$this->config['store_name']=$store_name;
		}
		$this->connect();
	}
	
	/**
	 * Connexion au store
	 * @return bool
	 */
	private function connect(){
		
		$this->store = ARC2::getStore($this->config);
		if(!@$this->store->getDBCon()){//On regarde si l'on peut se connecter avec les informations fournies
			$this->errors[]="Error connexion";
			return false;
		}else{
			if (!$this->store->isSetUp()) {//Si les tables du store n'existent pas
				$this->store->setUp();//On cr�e les tables
				if($erreurs=$this->store->getErrors()){//Si la cr�ation � �chou�e
					foreach ( $erreurs as $value ) {
       					$this->errors[]=$value;
					}
					return false;
					$this->store->closeDBCon();
				}else{
					//Si on vient de faire la cr�ation pour pouvoir faire autre chose on doit se d�connecter et se reconnecter
					$this->store->closeDBCon();
					$this->store = ARC2::getStore($this->config);
				}
				
			}
		}
		return true;
	}
	
	/**
	 * Vide la base li�e au store.
	 * 
	 */
	public function reset(){
		
		if(count($this->errors)){
			return array();
		}
		$this->store->reset();
	}
	
	/**
	 * Supprime la base li�e au store.
	 * 
	 */
	public function drop(){
		
		if(count($this->errors)){
			return array();
		}
		$this->store->drop();
	}
	
	

}


class sparql extends rdf {

	// Tableau avec les namespaces les plus courants
	public $ns = array(		"skos:"	=> "http://www.w3.org/2004/02/skos/core#",
							"dc:"	=> "http://purl.org/dc/elements/1.1",
							"dct:"	=> "http://purl.org/dc/terms/",
							"owl:"	=> "http://www.w3.org/2002/07/owl#",
							"rdf:"	=> "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
							"rdfs:"	=> "http://www.w3.org/2000/01/rdf-schema#",
							"xsd:"	=> "http://www.w3.org/2001/XMLSchema#",
							"pmb:"	=> "http://www.pmbservices.fr/ontology#"
					);  
	
	
	/**
	 * Constructor
	 * @param text $store_name : Pr�fixe utilis� pour les tables de stockage du sch�ma rdf
	 * @return void
	 */
	public function __construct ($store_name="",$config=array()) {
		
		parent::__construct($store_name, $config); 
	}
	
	
	/**
	 * Execution d'une requete sparql dans le store
	 * @param text $query : Requ�te sparql � ex�cuter
	 * @return array : De la forme : 
		 Array(
    		[query_type] => "Type de la requ�te. Expl: Select, Delete, Load, Insert, ...". Toujours pr�sent
    		[result] => Array(. Toujours pr�sent
    		            [t_count] => Nb �l�ments trait�s. Pas pr�sent si select
    					[...] => D'autres cl�s possible
            			[variables] => Array( //Dans le cas d'un select
                    						[0] => "Variable utilis� dans la requete sparql si pr�sente"
                						)

            			[rows] => Array( "R�sultats correspondants � la requete" //Dans le cas d'un select
                    				[0] => Array( "Forme [Variable (Option si pr�sent)] => Vateur"
				                            [label] => Science
				                            [label type] => literal
				                            [label lang] => fr
                        				)
                 			)

        			)

    		[query_time] => 0.11747407913208 //Temps d'execution de la requete. Toujours pr�sent
		)
	 */
	public function query($query){
		
		$result=array();

		if(!count($this->errors)){//Si je n'ai pas d�j� des erreurs
			$result_tmp = $this->store->query($query);//J'execute la requete
			if($erreurs=$this->store->getErrors()){//Si l'execution de la requete a �chou�
				foreach ( $erreurs as $value ) {
					$this->errors[]=$value;
				}
			}elseif(!$result_tmp){//Si l'execution de la requete a �chou�
			}else{
				$result=$result_tmp;
			}
		}
		return $result;
	}

	
	public function get_prefix_text(){
		
		$val="";
		foreach ( $this->ns as $key => $value ) {
       		$val.="PREFIX ".$key." <".$value.">\n";
		}
		return $val;
	}
	
	
	public function format_type($type){
		
		$type_trouve="";
		foreach ( $this->ns as $key => $value ) {
       		if(preg_match("@^".$value."(.+)$@",$type,$matches)){
       			$type_trouve=$key.$matches[1];
       			break;
       		}
		}
		if(!$type_trouve){
			$type_trouve=$type;
		}
		return $type_trouve;
	}
	
	
	/**
	 * Permet d'obtenir la liste de toutes les ressources du store avec si il existe :
	 *  @param array $uri_resource_scheme: Tableau avec la liste des URI des schemas � filtrer
	 * 	@return array : De la forme : 
		 Array(
    		[URI_de_la_ressource] =>  Array(
    		            [type_ressource_li�e] =>  Array(
                    						[0] => Array(
			                    						[type] => Type de la ressource li�e (literal / Uri)
			                    						[val] => Valeur de la ressource li�e
			                    						[lang] => Langue de la ressource li�e
			                						)
			                				[...] => 
                						)

            			[...] =>

        			)

    		[...] => 
		)
	 */
	public function get_resource_list($type_resource,$uri_resource_scheme=array()) {
		
		$result=array();
		$filter="";
		if(count($uri_resource_scheme)){
			$filter="FILTER (REGEX(?resource, '^".implode("$|^",$uri_resource_scheme)."$', 'i')) .";
		}
		$query=$this->get_prefix_text()."SELECT ?resource ?type_resource_liee ?resource_liee
		WHERE {
		  ?resource a ".$type_resource." .
		  ?resource ?type_resource_liee ?resource_liee .
		  ".$filter."
		}";
		$res=$this->query($query);
		if(count($res["result"]["rows"])){
			foreach ( $res["result"]["rows"] as $value ) {
       			$result[$value["resource"]][$this->format_type($value["type_resource_liee"])][]=array("type"=> $value["resource_liee type"], "val"=> $value["resource_liee"], "lang"=> $value["resource_liee lang"]);
			}
		}
		return $result;
	}

	
	/**
	 * Permet d'obtenir tous les URI des Schemas du store :
	 * 	@return array : De la forme :
	 Array(
	 [URI_du_Scheme] =>  Array(// Attention si pas de ConceptScheme dans le store ce tableau est vide
	 [type_ressource_li�e] =>  Array(
	 [0] => Array(
	 	[type] => Type de la ressource li�e (literal / Uri)
	 	[val] => Valeur de la ressource li�e
	 	[lang] => Langue de la ressource li�e
	 	)
	 [...] =>
	 )
	
	 [...] =>
	
	 )
	
	 [...] =>
	 )
	 */
	public function get_scheme_list() {
		$result=array();
		//Je cherche dans le store les resources li�es � un scheme avec le lien inScheme
		$query=$this->get_prefix_text()."SELECT ?a
		WHERE {
		?resource skos:inScheme ?a .
	}";
		$res=$this->query($query);
		if(count($res["result"]["rows"])){
			foreach ( $res["result"]["rows"] as $value ) {
				if(!in_array($value["a"],$result)){
					$result[]=$value["a"];
				}
			}
		}
		//Je cherche dans le store les resources li�es � un scheme avec le lien topConceptOf
		$query=$this->get_prefix_text()."SELECT ?a
		WHERE {
		?resource skos:topConceptOf ?a .
	}";
		$res=$this->query($query);
		if(count($res["result"]["rows"])){
			foreach ( $res["result"]["rows"] as $value ) {
				if(!in_array($value["a"],$result)){
					$result[]=$value["a"];
				}
			}
		}
		//Tous les "Scheme" d�finis dans le store
		$query=$this->get_prefix_text()."SELECT ?resource
		WHERE {
		?resource a skos:ConceptScheme .
	}";
		$res=$this->query($query);
		if(count($res["result"]["rows"])){
			foreach ( $res["result"]["rows"] as $value ) {
				if(!in_array($value["resource"],$result)){
					$result[]=$value["resource"];
				}
			}
		}
	
		//On va chercher toutes les informations sur les schemas utilis�s dans le store
		if(count($result)){
			$final=array();
			$result2=$this->get_resource_list("skos:ConceptScheme",$result);
			foreach ( $result as $key => $value ) {
				if($result2[$value]){
					$final[$value]=$result2[$value];
				}else{
					$final[$value]=array();
				}
				 
			}
		}
		return $final;
	}
	
	public function load_file($file){
		//LOAD n'accepte qu'un chemin absolu
		$res=$this->query('LOAD <file://'.realpath($file).'>');
		if($res){
			return $res['result']['t_count'];
		}else{
			return false;
		}
	}
	
	
}

?>