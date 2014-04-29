<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: openurl_parameters.class.php,v 1.1 2011-08-02 12:35:58 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


//Cette classe doit fournir le jeu de param�tres permettant le bon fonctionnement d'OpenURL 
class openurl_parameters {
	var $xml;		// xml de configuration
	var $params;	// parametres 
	var $conn_params;	//param�tres du connecteurs
	
	function openurl_parameters($xml=""){
		$this->xml = $xml;
	}
	
	function setParameters($params){
		$this->conn_params = $params;
	    $this->params = array(
	    	'transport' => $this->setTransportParams(),
	    	'serialization' => $this->setSerializationParams(),
	    	'entities' => $this->setEntitiesParams()
	    );
	}
	
	function setTransportParams(){
		return array(
			'protocole' => $this->conn_params['protocole'],
			'method' => $this->conn_params['method'],
			'param' => $this->conn_params['tparameters'],
			'byref_url' => $this->conn_params['byref_url']
		);
	}
	
	function setSerializationParams(){
		return $this->conn_params['serialization'];
	}
	
	function setEntitiesParams(){
		$param = array(
			'referent' => array(
				'identifier' => array(
					'isbn'=> $this->conn_params['rft_isbn'],
					'issn'=> $this->conn_params['rft_issn'],
					'pmid'=> $this->conn_params['rft_pmid'],
					'doi'=> $this->conn_params['rft_doi']
				),
				'byval' => $this->conn_params['rft_byval'],
				'byref' => $this->conn_params['rft_byref'],
				'private' => $this->conn_params['rft_private']
			),
			'referring_entity' => array(
				'allow' => ($this->conn_params['rfe_allow'] ? "yes":"no"),
				'elem' => array(
					'identifier' => array(
						'isbn'=> $this->conn_params['rfe_isbn'],
						'issn'=> $this->conn_params['rfe_issn'],
						'pmid'=> $this->conn_params['rfe_pmid'],
						'doi'=> $this->conn_params['rfe_doi']
					),
					'byval' => $this->conn_params['rfe_byval'],
					'byref' => $this->conn_params['rfe_byref'],
					'private' => $this->conn_params['rfe_private']				
				)
			),
			'requester' => array(
				'allow' => ($this->conn_params['req_allow'] ? "yes":"no"),
				'value' => $this->conn_params['req_parameter']
			),
			'service_type' => array(
				'allow' => ($this->conn_params['svc_allow'] ? "yes":"no"),
				'values' => array() // on a besoin d'une boucle...
			),
			'resolver' => array(
				'allow' => ($this->conn_params['res_allow'] ? "yes":"no"),
				'value' => $this->conn_params['res_parameter']
			),
			'referrer' => array(
				'allow' => ($this->conn_params['rfr_allow'] ? "yes":"no"),
				'value' => $this->conn_params['rfr_parameter']
			)
		);
		foreach($this->conn_params['svc_services'] as $service){
			$param['service_type']['values'][$service['name']]= $service['value'];
		}
		return $param;
	}
	
	function getParameters(){
		return $this->params;
	}
}