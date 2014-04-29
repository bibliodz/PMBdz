<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: abts_perio.class.php,v 1.1 2011-07-08 14:12:40 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes d'info sur bulletinage de p�riodique
		
class abts_perio {

var $serial_id       = 0;         // id du p�riodique 

// constructeur
function abts_perio($serial_id=0) {
	
	if($serial_id) {
		$this->serial_id = $serial_id;
		$this->fetch_data();
	}	
	return $this->serial_id;
}
    
// r�cup�ration des infos en base
function fetch_data() {
	global $dbh;
	
	$req="SELECT surloc_num, location_id,location_libelle, rel_date_parution,rel_libelle_numero, rel_comment_opac 
		from perio_relance, abts_abts, docs_location
		where  location_id=idlocation and rel_abt_num=abt_id and num_notice=".$this->serial_id." and rel_comment_opac!='' group by rel_abt_num,rel_date_parution,rel_libelle_numero order by rel_nb desc";		

	$result = mysql_query($req);
	if(mysql_num_rows($result)){
		$tr_class="";
		while($r = mysql_fetch_object($result)) {	
			$surloc_libelle="";
			if($opac_sur_location_activate && $r->surloc_num ){
				$req="select surloc_libelle from sur_location where surloc_id = ".$r->surloc_num;
				$res_surloc = mysql_query($req);
				if(mysql_num_rows($res_surloc)){
					$surloc= mysql_fetch_object($res_surloc);
					$surloc_libelle=$surloc->surloc_libelle." / ";
				}
			}			
			$line=$bulletin_retard_line;
			
			$line=str_replace("!!location_libelle!!", $surloc_libelle.$r->location_libelle , $line);	
			$line=str_replace("!!date_parution!!", $r->rel_date_parution, $line);	
			$line=str_replace("!!libelle_numero!!", $r->rel_libelle_numero, $line);		
			$line=str_replace("!!comment_opac!!", $r->rel_comment_opac, $line);	
			if($tr_class=='even')$tr_class="odd"; else $tr_class='even';
			$line=str_replace("!!tr_class!!",$tr_class, $line);	
			$lines.=$line	;	
		}
		$tpl=$bulletin_retard_form;
		$tpl=gen_plus("bulletin_retard",$msg["bulletin_retard_title"],str_replace("!!bulletin_retard_list!!", $lines, $tpl));		
	}
}



} // fin d�finition classe
