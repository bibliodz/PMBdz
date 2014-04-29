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
 * A diarized sound or video file
*
* $Id: voxilabSpeechfile.class.php,v 1.1 2014-01-10 15:46:42 apetithomme Exp $
*/

if (stristr ($_SERVER['REQUEST_URI'], ".class.php"))
	die ("no access");

require_once 'voxilabSpeaker.class.php';
require_once 'voxilabSegment.class.php';

class voxilabSpeechfile
{
	 /*** Attributes: ***/

	/**
	 * File identifier
	 * @access private
	 */
	private $id;
	
	/**
	 * Instance of voxilabDiarization
	 * @var voxilabDiarization
	 */
	private $diarization;

	/**
	 * File status
	 * @access private
	 */
	private $status;
	
	/**
	 * Array of segments
	 * @var array
	 * @access private
	 */
	private $segments = array();
	
	/**
	 * Array of speakers
	 * @var array
	 * @access private
	 */
	private $speakers = array();


	/**
	 * 
	 *
	 * @param int id Identifiant du fichier
	 * 
	 * @param string url URL to call for diarization

	 * @param string name Nom du fichier

	 * @return void
	 * @access public
	 */
	public function __construct( $id, $diarization ) {
		$this->id = $id;
		$this->diarization = $diarization;
	} // end of member function __construct

	/**
	 * Get all informations about the file
	 * 
	 * @return array
	 * @access private
	 */
	private function getInfos() {
		if ($this->status == "diarization_phase7") {
			if (!count($this->speakers)) {
				$speakers = $this->diarization->sendCommand($this->id, 'speakers');
		
				foreach ($speakers as $speaker) {
					$this->speakers[$speaker->id] = new voxilabSpeaker($speaker->id, $speaker->gender);
				}
			}
			if (!count($this->segments) && count($this->speakers)) {
				$segments = $this->diarization->sendCommand($this->id, 'segments');
				
				foreach ($segments as $segment) {
					$voxilabSegment = new voxilabSegment($segment->start, $segment->duration, $this->speakers[$segment->speaker->id]);
					$this->segments[] = $voxilabSegment;
					$this->speakers[$segment->speaker->id]->addSegment($voxilabSegment);
				}
			}
		}
	}
	
	/**
	 * Get an array of speakers
	 *
	 * @return array
	 * @access public
	 */
	public function getSpeakers( ) {
		if (!count($this->speakers)) {
			$this->getInfos();
		}
		return $this->speakers;
	} // end of member function getSpeakers

	/**
	 * Get an array of segments
	 *
	 * @return array
	 * @access public
	 */
	public function getSegments( ) {
		if (!count($this->segments)) {
			$this->getInfos();
		}
		return $this->segments;
	} // end of member function getSegments

	/**
	 * Get file status
	 *
	 * @return string
	 * @access public
	 */
	public function getStatus( ) {
		if ($this->status != "diarization_phase7") {
			$result = $this->diarization->sendCommand($this->id, 'status');
			
			$this->status = $result->status;
		}
		return $this->status;
	} // end of member function getStatus

	/**
	 * Get a speaker with an id
	 *
	 * @param int id Speaker identifier

	 * @return voxilabSpeaker
	 * @access public
	 */
	public function getSpeakerByID( $id ) {
		if (!count($this->speakers)) {
			$this->getInfos();
		}
		if ($this->speakers[$id]) {
			return $this->speakers[$id];
		} else return false;
	} // end of member function getSpeaker

	/**
	 * Get a segment with a time
	 *
	 * @param float time Time (hundredth of a second)

	 * @return voxilabSegment
	 * @access public
	 */
	public function getSegmentByTime( $time ) {
		if (!count($this->segments)) {
			$this->getInfos();
		}
		foreach ($this->segments as $segment) {
			if (($segment->getStart() <= $time) && ($time < $segment->getEnd())) {
				return $segment;
			} else continue;
		}
		return false;
	} // end of member function getSegmentByTime

	/**
	 * Get segments in an interval
	 *
	 * @param float begin Beginning of the interval

	 * @param float end End of the interval

	 * @return voxilabSegment
	 * @access public
	 */
	public function getSegmentsInInterval( $begin,  $end ) {
		if (!count($this->segments)) {
			$this->getInfos();
		}
		$return = array();
		foreach ($this->segments as $segment) {
			if (($segment->getEnd() > $begin) && ($segment->getStart() <= $end)) {
				$return[] = $segment;
			}
		}
		return $return;
	} // end of member function getSegmentsInInterval
	
	/**
	 * Get segments with a speaker identifier
	 * 
	 * @param int speaker Speaker identifier
	 * 
	 * @return voxilabSegment
	 */
	public function getSegmentsBySpeaker($speaker) {
		if (!count($this->speakers)) {
			$this->getInfos();
		}
		return $this->speakers[$speaker]->getSegments();
	}
	
	public function getID() {
		return $this->id;
	}

} // end of voxilabSpeechfile
?>
