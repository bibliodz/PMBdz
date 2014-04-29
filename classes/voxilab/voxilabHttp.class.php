<?php
/***********************************************************************************************
 Ce fichier fait partie du projet voxilabPHP, impl�mentation PHP de
l'API Voxilab (https://github.com/voxilab)

Cette  impl�mentation d�velopp�e par � 2013- PMB Services.

Ce programme est r�gi par la licence CeCILL soumise au droit fran�ais et
respectant les principes de diffusion des logiciels libres. Vous pouvez
utiliser, modifier et/ou redistribuer ce programme sous les conditions
de la licence CeCILL telle que diffus�e par le CEA, le CNRS et l'INRIA
sur le site "http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie,
de modification et de redistribution accord�s par cette licence, il n'est
offert aux utilisateurs qu'une garantie limit�e.  Pour les m�mes raisons,
seule une responsabilit� restreinte p�se sur l'auteur du programme,  le
titulaire des droits patrimoniaux et les conc�dants successifs.

A cet �gard  l'attention de l'utilisateur est attir�e sur les risques
associ�s au chargement,  � l'utilisation,  � la modification et/ou au
d�veloppement et � la reproduction du logiciel par l'utilisateur �tant
donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe �
manipuler et qui le r�serve donc � des d�veloppeurs et des professionnels
avertis poss�dant  des  connaissances  informatiques approfondies.  Les
utilisateurs sont donc invit�s � charger  et  tester  l'ad�quation  du
logiciel � leurs besoins dans des conditions permettant d'assurer la
s�curit� de leurs syst�mes et ou de leurs donn�es et, plus g�n�ralement,
� l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez
pris connaissance de la licence CeCILL, et que vous en avez accept� les
termes.
***********************************************************************************************/

/*
 * Implementation of the HTTP Protocol for diarization
*
* $Id: voxilabHttp.class.php,v 1.1 2014-01-10 15:46:42 apetithomme Exp $
*/

if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

require_once 'voxilabProtocol.class.php';

class voxilabHttp implements voxilabProtocol
{
	/*** Attributes: ***/
	/**
	 * Array of protocol options
	 * @var array
	 */
	private $options;
	
	/**
	 * @var resource ch A cURL handle returned by curl_init()
	 */
	private $ch;
	
	/**
	 * @param array options Array of protocol options
	 */
	public function __construct($options) {
		$this->options = $options;
	}
	
	/**
	 * Post a file
	 *
	 * @param string path Path of the file to post
	 *
	 * @return string Json string
	 */
	public function postFile($path) {
		if (file_exists($path)) {
			$this->initProtocol();
			
			curl_setopt($this->ch, CURLOPT_URL, $this->options['url']);
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, array("file"=>"@".$path));
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			
			$result = curl_exec($this->ch);
			curl_close($this->ch);
			return $result;
		} else throw new Exception("File ".$path." doesn't exist !");
	}
	
	/**
	 * Execute a command
	 *
	 * @param int id File identifier
	 *
	 * @param string command Command to execute
	 *
	 * @return string Json string
	 */
	public function command($id, $command) {
		$this->initProtocol();
		
		switch ($command) {
			case "status" :
				$url = $this->options['url']."/".$id;
				break;
			case "speakers" :
				$url = $this->options['url']."/".$id."/speakers";
				break;
			case "segments" :
				$url = $this->options['url']."/".$id."/segments";
				break;
			default :
				$url = $this->options['url']."/".$id;
				break;
		}
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($this->ch);
		curl_close($this->ch);
		return $result;
	}
	
	/**
	 * Set the global options for the protocol
	 *
	 * @param resource ch A cURL handle returned by curl_init()
	 */
	public function initProtocol() {
		$this->ch = curl_init();
		if (isset($this->options['proxy'])) curl_setopt($this->ch, CURLOPT_PROXY, $this->options['proxy']);
		if (isset($this->options['proxyuserpwd'])) curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->options['proxyuserpwd']);
		if (isset($this->options['sslcert']) && isset($this->options['sslkey'])) {
			curl_setopt($this->ch, CURLOPT_SSLCERT, $this->options['sslcert']);
			curl_setopt($this->ch, CURLOPT_SSLKEY, $this->options['sslkey']);
			if (isset($this->options['sslkeypasswd'])) curl_setopt($this->ch, CURLOPT_SSLKEYPASSWD, $this->options['sslkeypasswd']);
		}
	}
}
?>