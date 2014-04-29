<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail-retard.inc.php,v 1.31 2012-04-11 15:31:20 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once ("$include_path/notice_authors.inc.php");  
require_once ($include_path."/mail.inc.php") ;
require_once ("$class_path/author.class.php");  
require_once ($class_path."/serie.class.php");

if (!$relance) $relance = 1;

// popup de mail de retard de pr�t
/* re�oit : id_empr et �ventuellement cb_doc */

// l'objet du mail
$var = "mailretard_".$relance."objet";
eval ("\$objet=\"".$$var."\";");

// la formule de politesse du bas (le signataire)
$var = "mailretard_".$relance."fdp";
eval ("\$fdp=\"".$$var."\";");

// le texte apr�s la liste des ouvrages en retard
$var = "mailretard_".$relance."after_list";
eval ("\$after_list=\"".$$var."\";");

// le texte avant la liste des ouvrges en retard
$var = "mailretard_".$relance."before_list";
eval ("\$before_list=\"".$$var."\";");

// le "Madame, Monsieur," ou tout autre truc du genre "Cher adh�rent,"
$var = "mailretard_".$relance."madame_monsieur";
eval ("\$madame_monsieur=\"".$$var."\";");

if($madame_monsieur) $texte_mail.=$madame_monsieur."\r\n\r\n";
if($before_list) $texte_mail.=$before_list."\r\n\r\n";

//R�cup�ration des exemplaires
$rqt = "select expl_cb from pret, exemplaires where pret_idempr='".$id_empr."' and pret_retour < curdate() and pret_idexpl=expl_id order by pret_date " ;
$req = mysql_query($rqt) or die('Erreur SQL !<br />'.$rqt.'<br />'.mysql_error()); 

$i=0;
while ($data = mysql_fetch_array($req)) {
	
	/* R�cup�ration des infos exemplaires et pr�t */
	$requete = "SELECT notices_m.notice_id as m_id, notices_s.notice_id as s_id, expl_cb, pret_date, pret_retour, tdoc_libelle, section_libelle, location_libelle, trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date, concat(' (',mention_date,')') ,''))) as tit, ";
	$requete.= " date_format(pret_date, '".$msg["format_date"]."') as aff_pret_date, ";
	$requete.= " date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour, ";
	$requete.= " IF(pret_retour>sysdate(),0,1) as retard, notices_m.tparent_id, notices_m.tnvol " ; 
	$requete.= "FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), docs_type, docs_section, docs_location, pret ";
	$requete.= "WHERE expl_cb='".addslashes($data['expl_cb'])."' and expl_typdoc = idtyp_doc and expl_section = idsection and expl_location = idlocation and pret_idexpl = expl_id  ";
	
	$res = mysql_query($requete);
	$expl = mysql_fetch_object($res);
	
	$responsabilites=array() ;
	$header_aut = "" ;
	$responsabilites = get_notice_authors(($expl->m_id+$expl->s_id)) ;
	$as = array_search ("0", $responsabilites["responsabilites"]) ;
	if ($as!== FALSE && $as!== NULL) {
		$auteur_0 = $responsabilites["auteurs"][$as] ;
		$auteur = new auteur($auteur_0["id"]);
		$header_aut .= $auteur->isbd_entry;
	} else {
		$aut1_libelle=array();
		$as = array_keys ($responsabilites["responsabilites"], "1" ) ;
		for ($i = 0 ; $i < count($as) ; $i++) {
			$indice = $as[$i] ;
			$auteur_1 = $responsabilites["auteurs"][$indice] ;
			$auteur = new auteur($auteur_1["id"]);
			$aut1_libelle[]= $auteur->isbd_entry;
		}
		$header_aut .= implode (", ",$aut1_libelle) ;
	}
	$header_aut ? $auteur=" / ".$header_aut : $auteur="";
	
	// r�cup�ration du titre de s�rie
	$tit_serie="";
	if ($expl->tparent_id && $expl->m_id) {
		$parent = new serie($expl->tparent_id);
		$tit_serie = $parent->name;
		if($expl->tnvol)
			$tit_serie .= ', '.$expl->tnvol;
	}
	if($tit_serie) {
		$expl->tit = $tit_serie.'. '.$expl->tit;
	}

	$texte_mail.=$expl->tit.$auteur."\r\n";
	$texte_mail.="    -".$msg[fpdf_date_pret]." : ".$expl->aff_pret_date." ".$msg[fpdf_retour_prevu]." : ".$expl->aff_pret_retour."\r\n";
	$texte_mail.="    -".$expl->location_libelle.": ".$expl->section_libelle." (".$expl->expl_cb.")\r\n\r\n\r\n";
	$i++;
}
$texte_mail.="\r\n";
if($after_list) $texte_mail.=$after_list."\r\n\r\n";
if($fdp) $texte_mail.=$fdp."\r\n\r\n";
$texte_mail.=mail_bloc_adresse() ;

//Si mail de rappel affect� au responsable du groupe
$requete="select id_groupe,resp_groupe from groupe,empr_groupe where id_groupe=groupe_id and empr_id=$id_empr and resp_groupe and mail_rappel limit 1";
$res=mysql_query($requete);
/* R�cup�ration du nom, pr�nom et mail du lecteur destinataire */
if(mysql_num_rows($res) > 0) {
	$requete="select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr='".mysql_result($res, 0,1)."'";
	$result=mysql_query($requete);
	$coords_dest=mysql_fetch_object($result);
} else {
	$requete="select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr=$id_empr";
	$result=mysql_query($requete);
	$coords_dest=mysql_fetch_object($result);
}
			
/* R�cup�ration du nom, pr�nom et mail du lecteur concern� */
$requete="select id_empr, empr_mail, empr_nom, empr_prenom, empr_cb from empr where id_empr=$id_empr";
$res=mysql_query($requete);
$coords=mysql_fetch_object($res);
	
//remplacement nom et prenom
$texte_mail=str_replace("!!empr_name!!", $coords->empr_nom,$texte_mail); 
$texte_mail=str_replace("!!empr_first_name!!", $coords->empr_prenom,$texte_mail); 

$headers .= "Content-type: text/plain; charset=".$charset."\n";
	
$res_envoi=mailpmb($coords_dest->empr_prenom." ".$coords_dest->empr_nom, $coords_dest->empr_mail, $objet." : ".$coords->empr_prenom." ".mb_strtoupper($coords->empr_nom,$charset)." (".$coords->empr_cb.")",$texte_mail, $biblio_name, $biblio_email,$headers, "", $PMBuseremailbcc,1);	

if ($res_envoi) echo "<center><h3>".sprintf($msg["mail_retard_succeed"],$coords_dest->empr_mail)."</h3><br /><a href=\"\" onClick=\"self.close(); return false;\">".$msg["mail_retard_close"]."</a></center><br /><br />".nl2br($texte_mail);
	else echo "<center><h3>".sprintf($msg["mail_retard_failed"],$coords_dest->empr_mail)."</h3><br /><a href=\"\" onClick=\"self.close(); return false;\">".$msg["mail_retard_close"]."</a></center>";

?>