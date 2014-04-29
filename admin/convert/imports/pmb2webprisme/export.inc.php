<?php
require_once("$class_path/marc_table.class.php");
require_once("$class_path/category.class.php");
function _export_($id,$keep_expl) {
	global $charset;
	$notice="<?xml version='1.0' encoding='".$charset."'?>\n";
	$notice.="<notice>\n";
	$requete="select * from notices where notice_id=$id";
	$resultat=mysql_query($requete);
	
	$rn=mysql_fetch_object($resultat);
	
	//R�f�rence
	//$notice.="  <REF>".htmlspecialchars($id)."</REF>\n";
	
	//Organisme (OP)
	$requete="select notices_custom_list_lib from notices_custom_lists, notices_custom_values where notices_custom_lists.notices_custom_champ=1 and notices_custom_values.notices_custom_champ=1 and notices_custom_integer=notices_custom_list_value and notices_custom_origine=$id";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$op=mysql_result($resultat,0,0);
		$notice.="  <OP>".htmlspecialchars(strtoupper($op),ENT_QUOTES,$charset)."</OP>\n";
	}
	//Date saisie (DS)
	$requete="select notices_custom_date from notices_custom_values where notices_custom_champ=3 and notices_custom_origine=$id";
	$resultat=mysql_query($requete);
	if (mysql_num_rows($resultat)) {
		$date=mysql_result($resultat,0,0);
	} else $date=date("Y")."-".date("m")."-".date("d");
	$notice.="<DS>".$date."</DS>\n";
		
	//NOM
	$serie="";
	if ($rn->tparent_id) {
		$requete="select serie_name from series where serie_id=".$rn->tparent_id;
		$resultat=mysql_query($requete);
		if (mysql_num_rows($resultat)) $serie=mysql_result($resultat,0,0);
	}
	if ($rn->tnvol) $serie.=($serie?" ":"").$rn->tnvol;
	if ($serie) $serie.=". ";
	// ajout GM 15/12/2006 pour export sous-titre dans TI
	if ($rn->tit4!="") {$soustitre=" : ".$rn->tit4;}
	// fin ajout GM
	// modif GM 15/12/2006 ajout du sous-titre pour l'export
	// $notice.="  <TI>".htmlspecialchars(strtoupper($serie.$rn->tit1))."</TI>\n";
	$notice.="  <NOM>".htmlspecialchars(strtoupper($serie.$rn->tit1.$soustitre),ENT_QUOTES,$charset)."</NOM>\n";
	
	//MEL
	$no=$rn->n_gen;
	if ($no) {
		$notice.="<MEL>".htmlspecialchars($no,ENT_QUOTES,$charset)."</MEL>\n";
	}

	$requete="select num_noeud from notices_categories where notcateg_notice=$id";
	$resultat=mysql_query($requete);
	$doc=array();
	$de=array();
	while (list($categ_id)=mysql_fetch_row($resultat)) {
		$categ=new category($categ_id);
		switch ($categ->thes->id_thesaurus) {
			case 1:
				$de[]=$categ->libelle;
				break;
			case 12:
				$doc[]=$categ->libelle;
				break;
		}
	}
	//Descripteurs (DE)
	if (count($de)) {
		sort($de);
		$notice.="<DE>".htmlspecialchars(strtoupper(implode(",",$de)),ENT_QUOTES,$charset)."</DE>\n";
	}
	//Descripteurs Web
	if (count($doc)) {
		sort($doc);
		$notice.="<DOC>".htmlspecialchars(strtoupper(implode(",",$doc)),ENT_QUOTES,$charset)."</DOC>\n";
	}
	//Resum� (COMMENT)
	if ($rn->n_resume) {
		$notice.="<COMMENT>".htmlspecialchars($rn->n_resume,ENT_QUOTES,$charset)."</COMMENT>\n";
	}
	//Site (SITE)
	if ($rn->lien) {
		$notice.="<SITE>".htmlspecialchars($rn->lien,ENT_QUOTES,$charset)."</SITE>\n";
	}
	//LI
	if ($rn->n_contenu) {
		$notice.="<LI>".htmlspecialchars($rn->n_contenu,ENT_QUOTES,$charset)."</LI>\n";
	}
	//DO
	if ($rn->indexint) {
		$requete="select indexint_name from indexint where indexint_id=".$rn->indexint;
		$resultat=mysql_query($requete);
		$do=mysql_result($resultat,0,0);
		$notice.="<DO>".htmlspecialchars($do,ENT_QUOTES,$charset)."</DO>\n";
	}
	$notice.="</notice>";
	return $notice;
}

?>
