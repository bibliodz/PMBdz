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
 * Diarization main class
*
* $Id: voxilabDiarization.class.php,v 1.1 2014-01-10 15:46:42 apetithomme Exp $
*/

if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

define("VOXILAB_FILE_DOESNT_EXIST",1);
define("VOXILAB_DIARIZATION_ERROR",2);

require_once 'voxilabSpeechfile.class.php';

class voxilabDiarization
{
	 /*** Attributes: ***/
	/**
	 * Protocol used
	 * @var voxilabProtocol
	 */
	private $protocol;


	/**
	 * @param string protocol Protocol to use

	 * @param array options Array of options

	 * @return void
	 * @access public
	 */
	public function __construct($protocol, $options) {
		$protocolClass = "voxilab".ucfirst(strtolower($protocol));
		require_once $protocolClass.".class.php";
		$this->protocol = new $protocolClass($options);
	} // end of member function __construct

	/**
	 * Send a file to the diarization
	 *
	 * @param string path Path of the file to send to the diarization

	 * @return void
	 * @access public
	 */
	public function sendFile( $path ) {
		if (file_exists($path)) {
			$result = json_decode($this->protocol->postFile($path));
			
			if ($result->status != "uploaded") {
				throw new Exception("Diarization error !",VOXILAB_DIARIZATION_ERROR);
			}
			
			$speechfile = new voxilabSpeechfile($result->id, $this);
			return $speechfile;
		} else throw new Exception("File ".$path." doesn't exist !",VOXILAB_FILE_DOESNT_EXIST);
	} // end of member function sendFile
	
	/**
	 * Get file with an id
	 * 
	 * @param int id File identifier
	 * @return voxilabSpeechfile
	 */
	public function getFile($id) {
		return new voxilabSpeechfile($id, $this);
	}
	
	/**
	 * Send a command to the protocol
	 * 
	 * @param string command Name of the command to send
	 * @return array
	 */
	public function sendCommand($id, $command) {
		return json_decode($this->protocol->command($id, $command));
	}

} // end of voxilabDiarization
?>
