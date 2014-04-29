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
 * A speaker : a distinct person identified in the speech. A speaker is always associated with segments.
*
* $Id: voxilabSpeaker.class.php,v 1.1 2014-01-10 15:46:42 apetithomme Exp $
*/

if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

require_once 'voxilabSegment.class.php';

class voxilabSpeaker
{
	 /*** Attributes: ***/

	/**
	 * Speaker identifier
	 * @access public
	 */
	private $id;

	/**
	 * Speaker gender
	 * @access private
	 */
	private $gender;
	
	/**
	 * Speaker segments
	 * @var array
	 */
	private $segments = array();


	/**
	 * 
	 *
	 * @param int id Speaker identifier

	 * @param char gender Speaker gender

	 * @return void
	 * @access public
	 */
	public function __construct( $id,  $gender ) {
		$this->id = $id;
		$this->gender = $gender;
	} // end of member function __construct

	public function __toString() {
		print "Speaker ".$this->id.". Gender : ".$this->gender."<br />";
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getGender() {
		return $this->gender;
	}
	
	/**
	 * Add a segment to the speaker
	 * @param voxilabSegment segment New segment
	 */
	public function addSegment($segment) {
		$this->segments[] = $segment;
	}
	
	/**
	 * Encode the segment into Json
	 * @return string
	 */
	public function toJson() {
		$segments = array();
		foreach ($this->segments as $segment) {
			$segments[] = array(
					'start' => $segment->getStart(),
					'duration' => $segment->getDuration()
					);
		}
		$array = array(
				'id' => $this->id,
				'gender' => $this->gender,
				'segments' => $segments
				);
		return json_encode($array);
	}
	
	public function getSegments() {
		return $this->segments;
	}

} // end of voxilabSpeaker
?>
