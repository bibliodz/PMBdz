<?php
/**
 * \brief Class pmb_remote permet de se connecter � un PMB client et d'effectuer des GET et POST tel un navigateur 
 * Voir l'exemple en fin de fichier pour s'initier � la classe
 * @author ngantier
 * \date mars 2008
 * \ingroup server
 */

class pmb_remote {
	private $http_url="http://localhost/pmb";	// Adresse du serveur http a contacter 
	private $http_url_login="http://localhost/pmb/main.php";	// URL du serveur http a contacter pour l'autentification par cookies
	private $http_port="";					// Port http du serveur 
	private $http_proxy='';					// pour utilisation d'un proxy 	
	private $http_use_cookie=true;			// Gestion d'une session par cookie sur le serveur : false=non, true=oui 
	private $http_cookie_login=array();		// memo cookie 
	private $http_cookie_renew_pattern="";	// Expression r�guli�re qui permet de d�tecter qu'une session par cookie a expir� 
	private $http_use_ssl=false;				// Utiliser une connexion s�curis�e par ssl : false=non, true=oui 
	private $http_ssl_key="";				// Cl� priv�e pour la connexion ssl
	private $http_ssl_crt="";				// Cl� publique pour l'autentification 
	private $http_renew_pattern="/login-box/"; // detection d'une erreur de connection de PMB
	private $http_cookies=array();
	private $http_core="";		// Corps de la r�ponse http 
	private $http_header="";	// Hearder de la r�ponse http
	private $curl_link; 		// Lien curl courant
	private $error=false;		// Si true, il y a eu une erreur lors d'un traitement
	
	public $error_message="";	// Si ::error = true, message d'explication de l'erreur 
	public $response=""; // contenue de la r�ponse: la page html demand�e
	/**
	 * Constructeur
	 * @param string $pmb_url url de PMB avec / � la fin. Exemple: "https://gestion.bibli.fr/compte_client/"
	 * @param string $port Port http du serveur 
	 * @param string $proxy pour utilisation �ventuel d'un proxy 	
	 * @param string $user user de la connection
	 * @param string $password password de la connection
	 * @param string $database Base de donn�e utilis�e
	 * @param string $ssl_path path des cl�s num�rique avec / � la fin. Exemple: "/home/xxxxxx/.ssl/"
	 */
    function pmb_remote($pmb_url,$port,$proxy,$user,$password,$database,$ssl_path="") {
		$this->http_url=$pmb_url;
		$this->http_url_login=$pmb_url."main.php";
		$this->http_port=$port;
		$this->http_proxy=$proxy;
		$this->http_cookie_login=array("user"=>"$user","password"=>"$password","database"=>"$database");
		if ($ssl_path) {
			$this->http_ssl_crt= $ssl_path."pmb.crt";
			$this->http_ssl_key= $ssl_path."pmb.key";	
			$this->http_use_ssl=true;		
		}
    }
    

	// fonction appeler par curl pour m�moriser la r�ponse
	function get_http_core($curl_ressource,$data) {
		$this->http_core.=$data;
		return strlen($data);
	}
	
	// fonction appeler par curl pour m�moriserles ent�tes de la r�ponse http dans ::http_header
	function get_http_header($curl_ressource,$data) {
		if (strpos($data,"Set-Cookie:")!==false) {
			$this->http_cookies[]=trim(substr($data,12));
		}
		$this->http_header.=$data;
		return strlen($data);
	}

	// Intialise et pr�pare les options curl pour la connexion http
	function prepare_http($http_params) {
		//Initialisation de la connexion
    	$this->curl_link = curl_init();
		curl_setopt($this->curl_link, CURLOPT_WRITEFUNCTION,array(&$this,"get_http_core"));
		curl_setopt($this->curl_link, CURLOPT_HEADERFUNCTION,array(&$this,"get_http_header"));	
		curl_setopt($this->curl_link, CURLOPT_HEADER, 0);
		curl_setopt($this->curl_link, CURLOPT_TIMEOUT,300);
		curl_setopt($this->curl_link, CURLOPT_PROXY, $this->http_proxy);
		//Gestion du SSL
		if ($this->http_use_ssl) {
			curl_setopt($this->curl_link,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($this->curl_link,CURLOPT_SSLCERT,$this->http_ssl_crt);
			curl_setopt($this->curl_link,CURLOPT_SSLKEY,$this->http_ssl_key);
		}
		if (count($this->http_cookies)) {				
			curl_setopt($this->curl_link,CURLOPT_COOKIE,implode(";",$this->http_cookies));
		}
		foreach ($http_params as $param=>$value) {
			curl_setopt($this->curl_link,constant("CURLOPT_".$param),$value);
		}
	}

	//brief Fermeture de la connexion curl
	function close_http() {
		curl_close($this->curl_link);
	}

	
	// fait une requ�te http par GET ou POST en v�rifiant la session par cookie
	function make_logged_http_request($url,$post=0,$post_data="") {
		//Initialisation de la requ�te
		if($post) {
			$http_params=array(
				"URL"=>$this->http_url.$url,
				"POST"=>true,
				"HTTPGET"=>false,
				"POSTFIELDS"=>$post_data);
		} else {
			$http_params=array(
				"URL"=>$this->http_url.$url,
				"POST"=>false,
				"HTTPGET"=>true,
				"POSTFIELDS"=>"");			
		}
		$this->prepare_http($http_params);
		if ($this->make_http_request()) {
			//Requete OK, on cherche un statut "erreur de session" si il y a une session cookie
			if ($this->http_use_cookie) {
				//Recherche du pattern erreur
				$p=preg_match($this->http_renew_pattern,$this->http_core);
				// Si session expir�e, reconnexion
				if ($p) {
					//Reconnexion
					if ($this->http_do_login()) {
						$this->prepare_http($http_params);
						if ($this->make_http_request()) {
							//On s'est reconnect�, OK !
							return true;
						} else {
							//Erreur !
							$this->error=true;
							$this->error_message="Impossible d'obtenir une r�ponse � l'URL : ".$this->http_url.$url;
							return false;
						}	
					} else {
						//Erreur !
						$this->error=true;
						$this->error_message="Impossible de recr�er une session valide";
						return false;
					}
				}
			}
		} else {
			//Requete pas OK
			$this->error=true;
			$this->error_message="Impossible d'obtenir une r�ponse � l'URL : ".$this->http_url.$url;
			return false;
		}
		return true;
	}
	
	// Ex�cute la requ�te http pr�par�e par ::http_prepare()
	function make_http_request() {
		$this->http_headers="";
		$this->http_core="";
		$cexec=curl_exec($this->curl_link);
		$this->close_http();
		return $cexec;
	}
    
    // Login pour une session cookie
    function http_do_login() {
    	//Y-a-t-il une autentification par cookies ?
		if ($this->http_use_cookie) {
			//Pr�paration de la requ�te POST avec les �l�ments de login
			$post_vars=array();
			foreach($this->http_cookie_login as $key=>$val) {
				$post_vars[]=$key."=".rawurlencode($val);
			}				
			//Initialisation de la requ�te http
			$http_params=array(
				"POST"=>true,
				"URL"=>$this->http_url_login,
				"POSTFIELDS"=>implode("&",$post_vars)
			);
			$this->prepare_http($http_params);
			//Remise � zero des cookies
			$this->http_cookies=array();
			
			//Autentification
			if ($this->make_http_request()) {
				//A-t-on re�u des cookies ?
				if (count($this->http_cookies)) {			
					return true;
				} else {
					//L'autentification a �chou�
					$this->error=true;
					$this->error_message="La session http n'a p� �tre cr��e";
					return false;
				}
			} else {
				//La requ�te POST n'a pas march�
				$this->error=true;
				$this->error_message="Impossible de s'autentifier sur le serveur http !";
				return false;
			}
		} else return true;
    }
 
    // Effectue l'ouverture de session de pmb
    function connection() {
 		if(!$this->http_do_login()) return false; 
    	return true;  
    } 
       
    // Effectue la d�connection
	function disconnection() {
		 $this->response='';
  		if(!$this->make_logged_http_request("logout.php",1,"")) {
  			return false;
  		}
  		$this->response=$this->http_core; 
  		return true;				
    }   
    
    // requ�te http GET
    function http_get($url) {
    	$this->response='';
  		if(!$this->make_logged_http_request($url)) {
  			return false;
  		}
  		$this->response=$this->http_core; 
  		return true;		
    } 
    
    // requ�te http POST   
    function http_post($url,$param) {
    	$this->response='';
    	$postparam="";
    	foreach($param as $key=>$val) {
    		if($postparam)$postparam.="&";
    		$postparam.=rawurlencode($key)."=".rawurlencode($val);
    	}
  		if(!$this->make_logged_http_request($url,1,$postparam)){
  			return false;
  		}
  		$this->response=$this->http_core; 	
  		return true;	
    }
}

/*
 * 
 * Exemple d'utilisation de la classe: remplacer !!client!! et !!compte_utilisateur!!, et tester...
 * 
 * 
// instancier la classe pmb_remote
$pmb=new pmb_remote("https://gestion.bibli.fr/!!client!!/",443,'',"admin","admin","calyon","/home/!!compte_utilisateur!!/.ssl/");

// connection � PMB
if (!$pmb->connection()) {
	print $pmb->error_message."\n";
	exit;
}

// Faire un post 
$param["categ"]="isbd";
$param["id"]="1709";
if (!$pmb->http_post("catalog.php",$param)) {
	print $pmb->error_message."\n";
	exit;
}
print $pmb->response."\n";

// Faire un get
if (!$pmb->http_get("catalog.php?categ=isbd&id=1709")) {
	print $pmb->error_message."\n";
	exit;
}
print $pmb->response."\n";
*/

?>