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
 * A segment : part of the speech identified with an individual speaker
*
* $Id: voxilabSegment.class.php,v 1.1 2014-01-10 15:46:42 apetithomme Exp $
*/

if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

require_once 'voxilabSpeaker.class.php';

class voxilabSegment
{
	 /*** Attributes: ***/

	/**
	 * Time at the start (hundredth of a second)
	 * @access private
	 */
	private $start;

	/**
	 * Duration of the segment (hundredth of a second)
	 * @access private
	 */
	private $duration;

	/**
	 * Time at the end (hundredth of a second)
	 * @access private
	 */
	private $end;
	
	/**
	 * @var voxilabSpeaker
	 */
	private $speaker;


	/**
	 * 
	 *
	 * @param float start Time of start (hundredth of a second)

	 * @param float duration Duration of the segment (hundredth of a second)

	 * @param voxilabSpeaker speaker Segment speaker

	 * @return void
	 * @access public
	 */
	public function __construct( $start,  $duration,  $speaker ) {
		$this->start = $start;
		$this->duration = $duration;
		$this->speaker = $speaker;
		$this->getEnd();
	} // end of member function __construct


	/**
	 * Get the time at the end
	 *
	 * @return float
	 * @access public
	 */
	public function getEnd( ) {
		if (!$this->end) {
			$this->end = $this->start + $this->duration;
		}
		return $this->end;
	} // end of member function getEnd

	public function __toString() {
		print "Segment [Start : ".$this->start.". End : ".$this->end.". Duration : ".$this->duration.". Speaker : ".$this->speaker->getID().".]<br />";
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function getDuration() {
		return $this->duration;
	}
	
	public function getSpeaker() {
		return $this->speaker;
	}
	
	/**
	 * Encode the segment into Json
	 * @return string
	 */
	public function toJson() {
		$array = array(
				'start' => $this->start,
				'duration' => $this->duration,
				'speaker' => array(
						'id' => $this->speaker->getID(),
						'gender' => $this->speaker->getGender()
						)
				);
		return json_encode($array);
	}

} // end of voxilabSegment
?>
