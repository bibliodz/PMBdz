<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: explnum_ajax.inc.php,v 1.5 2014-02-25 15:05:18 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/explnum_associate_svg.class.php');

switch($quoifaire){
	
	case 'exist_file':
		existing_file($id,$id_repertoire);	
		break;
	case 'get_associate_svg':
		get_associate_svg($explnum_id);
		break;
	case 'get_associate_js':
		get_associate_js($explnum_id);
		break;
	case 'update_associate_author':
		update_associate_author($speaker_id, $author_id);
		break;
	case 'update_associate_speaker':
		update_associate_speaker($segment_id, $speaker_id);
		break;
	case 'add_new_speaker':
		add_new_speaker($explnum_id);
		break;
	case 'delete_associate_speaker':
		delete_associate_speaker($speaker_id);
		break;
}

function existing_file($id,$id_repertoire){
	
	global $dbh,$fichier;
	
	if(!$id){
		$rqt = "select repertoire_path, explnum_path, repertoire_utf8, explnum_nomfichier as nom, explnum_extfichier as ext from explnum join upload_repertoire on explnum_repertoire=repertoire_id  where explnum_repertoire='$id_repertoire' and explnum_nomfichier ='$fichier'";
		$res = mysql_query($rqt,$dbh);
		
		if(mysql_num_rows($res)){
			$expl = mysql_fetch_object($res);
			$path = str_replace('//','/',$expl->repertoire_path.$expl->explnum_path);
			if($expl->repertoire_utf8)
				$path = utf8_encode($path);
					
			if($expl->ext)
				$file = substr($expl->nom,0,strpos($expl->nom,"."));
			else $file = $expl->nom;
			$exist = false;
			$i=0;
			while(!$exist){
				$i++;
				$filename = ($i ? $file."_".$i : $file).($expl->ext ? ".".$expl->ext : "");
				if(!file_exists($path.$filename)){
					print $filename;
					$exist = true;
				}
			}
		} else print "0";
	} else print "0";
}

function get_associate_svg($explnum_id) {
	$explnum_associate_svg = new explnum_associate_svg($explnum_id);
	$svg = $explnum_associate_svg->getSvg(true);
	ajax_http_send_response($svg,"text/xml");
}

function get_associate_js($explnum_id) {
	$explnum_associate_svg = new explnum_associate_svg($explnum_id);
	$js = $explnum_associate_svg->getJs(true);
	ajax_http_send_response($js,"text/xml");
}

function update_associate_author($speaker_id, $author_id) {
	global $dbh;
	$query = 'update explnum_speakers set explnum_speaker_author = '.$author_id.' where explnum_speaker_id = '.$speaker_id;
	mysql_query($query, $dbh);
}

function update_associate_speaker($segment_id, $speaker_id) {
	global $dbh;
	$query = 'update explnum_segments set explnum_segment_speaker_num = '.$speaker_id.' where explnum_segment_id = '.$segment_id;
	mysql_query($query, $dbh);
}

function add_new_speaker($explnum_id) {
	global $dbh;
	$query = 'insert into explnum_speakers (explnum_speaker_explnum_num, explnum_speaker_speaker_num) values ('.$explnum_id.', "PMB")';
	mysql_query($query, $dbh);
}

function delete_associate_speaker($speaker_id) {
	global $dbh;
	$query = 'delete from explnum_speakers where explnum_speaker_id = '.$speaker_id;
	mysql_query($query, $dbh);
}
?>