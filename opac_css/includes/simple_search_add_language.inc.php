<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: simple_search_add_language.inc.php,v 1.4 2012-07-16 07:48:04 dgoron Exp $

function search_other_function_filters() {
	global $code_langue_restrict;
	global $charset, $msg, $marc_liste_langues;
	if (!$marc_liste_langues) $marc_liste_langues=new marc_list('lang');
	$r="<select name='code_langue_restrict'>";
	$r.="<option value=''>".$msg[all_languages]."</option>";
	$requete="select distinct code_langue from notices_langues where code_langue is not null and code_langue!='' order by ordre_langue";
	$resultat=mysql_query($requete);
    // on met les balises <option> dans un tableau, index� par le nom traduit de la langue
    $t=array();
    while ($res=mysql_fetch_object($resultat)) {
        if ($marc_liste_langues->table[$res->code_langue]) {
            $s="<option value='".htmlentities($res->code_langue,ENT_QUOTES,$charset)."' ";
            if ($res->code_langue==$code_langue_restrict) $s.="selected";
            $s.=">".$marc_liste_langues->table[$res->code_langue];
            $s.="</option>";
            $t[$marc_liste_langues->table[$res->code_langue]]=$s;
        }
    }
    // tri le tableau selon les cl�s (ici les noms des langues, pas les codes)
    ksort($t);

    // recopie des balises tri�es dans la liste <select>
    foreach($t as $k => $v) $r.=$v;

    $r.="</select>";
	return $r;
}

function search_other_function_clause() {
	global $code_langue_restrict;
	
	$r = "";
	if ($code_langue_restrict) {
		$r .= "select distinct num_notice as notice_id from notices_langues where code_langue='".$code_langue_restrict."' and type_langue=0";
	} 
	return $r;
}

function search_other_function_has_values() {
	global $code_langue_restrict;
	if ($code_langue_restrict) return true; 
	else return false;
}

function search_other_function_get_values(){
	global $code_langue_restrict;
	return $code_langue_restrict;
}

function search_other_function_rec_history($n) {
	global $code_langue_restrict;
	$_SESSION["code_langue_restrict".$n]=$code_langue_restrict;
}

function search_other_function_get_history($n) {
	global $code_langue_restrict;
	$code_langue_restrict=$_SESSION["code_langue_restrict".$n];
}

function search_other_function_human_query($n) {
	global $msg, $marc_liste_langues, $code_langue_restrict;
	if (!$marc_liste_langues) $marc_liste_langues=new marc_list('lang');
	$r="";
	$code_langue_restrict=$_SESSION["code_langue_restrict".$n];
	if ($code_langue_restrict) {
		$r=$msg[langue_publication_query]." : ";
		$r.=$marc_liste_langues->table[$code_langue_restrict];	
	}
	return $r;
}

?>