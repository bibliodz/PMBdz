<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: misc.inc.php,v 1.56 2014-03-07 15:32:27 abacarisse Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/semantique.class.php");
require_once($class_path."/parse_format.class.php");

//ajout des mots vides calcul�s
$add_empty_words=semantique::add_empty_words();
if ($add_empty_words) eval($add_empty_words);

//Fonction de r�cup�ration d'une URL vignette
function get_vignette($notice_id) {
	global $opac_book_pics_url;
	global $opac_url_base;
	
	$requete="select code,thumbnail_url from notices where notice_id=$notice_id";
	$res=mysql_query($requete);
	
	if ($res) {
		$notice=mysql_fetch_object($res);
		if ($notice->code || $notice->thumbnail_url) {
			if ($notice->thumbnail_url) 
				$url_image_ok=$notice->thumbnail_url;
			else {
				$code_chiffre = pmb_preg_replace('/-|\.| /', '', $notice->code);
				$url_image = $opac_book_pics_url ;
				$url_image = $opac_url_base."getimage.php?url_image=".urlencode($url_image)."&noticecode=!!noticecode!!&vigurl=".urlencode($notice->thumbnail_url) ;
				$url_image_ok = str_replace("!!noticecode!!", $code_chiffre, $url_image) ;
			}
		}
	} else {
		$url_image_ok=$opac_url_base."images/vide.png";
	}
	return $url_image_ok;
}

// ----------------------------------------------------------------------------
//	fonctions de formatage de cha�ne
// ----------------------------------------------------------------------------
// reg_diacrit : fonction pour traiter les caract�res accentu�s en recherche avec regex


function reg_diacrit($chaine) {
	// Armelle : a priori inutile.
	global $charset;
	global $include_path;
	// pr�paration d'une chaine pour requ�te par REGEXP
	global $tdiac ;
	if (!$tdiac) { 
			$tdiac = new XMLlist("$include_path/messages/diacritique$charset.xml");
			$tdiac->analyser();
	}
	foreach($tdiac->table as $wreplace => $wdiacritique) {
			if(pmb_preg_match("/$wdiacritique/", $chaine))
				$chaine = pmb_preg_replace("/$wdiacritique/", $wreplace, $chaine);
	}
		$tab = pmb_split('/\s/', $chaine);
	// mise en forme de la chaine pour les alternatives
	// on fonctionne avec OU (pour l'instant)
	if(sizeof($tab) > 1) {
		foreach($tab as $dummykey=>$word) {
			if($word) $this->mots[] = "($word)";
		}
		return join('|', $this->mots);
	} else {
		return $chaine;
	}
}

function convert_diacrit($string) {
	global $tdiac;
	global $charset;
	global $include_path;
	if(!$string) return;
	if (!$tdiac) { 
			$tdiac = new XMLlist("$include_path/messages/diacritique$charset.xml");
			$tdiac->analyser();
	}
	foreach($tdiac->table as $wreplace => $wdiacritique) {
		if(pmb_preg_match("/$wdiacritique/", $string))
			$string = pmb_preg_replace("/$wdiacritique/", $wreplace, $string);
	}	
	return $string;
}

//strip_empty_chars : enl�ve tout ce qui n'est pas alphab�tique ou num�rique d'une chaine
function strip_empty_chars($string) {
	// traitement des diacritiques
	$string = convert_diacrit($string);
	// Mis en commentaire : qu'en est-il des caract�res non latins ???
	// SUPPRIME DU COMMENTAIRE : ER : 12/05/2004 : �a fait tout merder...
	// RECH_14 : Attention : ici suppression des �ventuels "
	//          les " ne sont plus supprim�s 
	$string = stripslashes($string) ;
	$string = pmb_alphabetic('^a-z0-9\s', ' ',pmb_strtolower($string));

	// remplacement espace  ins�cable 0xA0:	&nbsp;  	Non-breaking space
	$string = clean_nbsp($string);
	// espaces en d�but et fin
	$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);
	
	// espaces en double
	$string = pmb_preg_replace('/\s+/', ' ', $string);
	
	return $string;
}

// strip_empty_words : fonction enlevant les mots vides d'une cha�ne
function strip_empty_words($string) {

	// on inclut le tableau des mots-vides pour la langue par defaut
	// c'est normalement la langue de catalogage...
	// si apr�s nettoyage des mots vide la chaine est vide alors on garde la chaine telle quelle (sans les accents)
	
	global $empty_word;
	// nettoyage de l'entr�e

	// traitement des diacritiques
	$string = convert_diacrit($string);

	// Mis en commentaire : qu'en est-il des caract�res non latins ???
	// SUPPRIME DU COMMENTAIRE : ER : 12/05/2004 : �a fait tout merder...
	// RECH_14 : Attention : ici suppression des �ventuels "
	//          les " ne sont plus supprim�s 
	$string = stripslashes($string) ;
	$string = pmb_alphabetic('^a-z0-9\s', ' ',pmb_strtolower($string));
	
	// remplacement espace  ins�cable 0xA0:	&nbsp;  	Non-breaking space
	$string = clean_nbsp($string);
	
	// espaces en d�but et fin
	$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);
	
	// espaces en double
	$string = pmb_preg_replace('/\s+/', ' ', $string);
	
	$string_avant_mots_vides = $string ; 
	// suppression des mots vides
	if(is_array($empty_word)) {
		foreach($empty_word as $dummykey=>$word) {
			$word = convert_diacrit($word);
			$string = pmb_preg_replace("/^${word}$|^${word}\s|\s${word}\s|\s${word}\$/i", ' ', $string);
			// RECH_14 : suppression des mots vides coll�s � des guillemets
			if (pmb_preg_match("/\"${word}\s/i",$string)) $string = pmb_preg_replace("/\"${word}\s/i", '"', $string);
			if (pmb_preg_match("/\s${word}\"/i",$string)) $string = pmb_preg_replace("/\s${word}\"/i", '"', $string);
			}
		}


	// re nettoyage des espaces g�n�r�s
	// espaces en d�but et fin
	$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);
	// espaces en double
	$string = pmb_preg_replace('/\s+/', ' ', $string);
	
	if (!$string) {
		$string = $string_avant_mots_vides ;
		// re nettoyage des espaces g�n�r�s
		// espaces en d�but et fin
		$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);
		// espaces en double
		$string = pmb_preg_replace('/\s+/', ' ', $string);
		}

	return $string;
	}

// clean_string() : fonction de nettoyage d'une cha�ne
function clean_string($string) {

	// on supprime les caract�res non-imprimables
	$string = pmb_preg_replace("/\\x0|[\x01-\x1f]/U","",$string);

	// suppression des caract�res de ponctuation ind�sirables
	// $string = pmb_preg_replace('/[\{\}\"]/', '', $string);

	// supression du point et des espaces de fin
	$string = pmb_preg_replace('/\s+\.$|\s+$/', '', $string);

	// nettoyage des espaces autour des parenth�ses
	$string = pmb_preg_replace('/\(\s+/', '(', $string);
	$string = pmb_preg_replace('/\s+\)/', ')', $string);

	// idem pour les crochets
	$string = pmb_preg_replace('/\[\s+/', '[', $string);
	$string = pmb_preg_replace('/\s+\]/', ']', $string);

	// petit point de d�tail sur les apostrophes
	$string = pmb_preg_replace('/\'\s+/', "'", $string); 

	// 'trim' par regex
	$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);

	// suppression des espaces doubles
	$string = pmb_preg_replace('/\s+/', ' ', $string);

	return $string;
	}

//Corrections des caract�res bizarres (voir pourris) de M$
function cp1252Toiso88591($str){
	$cp1252_map = array(
		"\x80" => "EUR", /* EURO SIGN */
		"\x82" => "\xab", /* SINGLE LOW-9 QUOTATION MARK */
		"\x83" => "\x66",     /* LATIN SMALL LETTER F WITH HOOK */
		"\x84" => "\xab", /* DOUBLE LOW-9 QUOTATION MARK */
		"\x85" => "...", /* HORIZONTAL ELLIPSIS */
		"\x86" => "?", /* DAGGER */
		"\x87" => "?", /* DOUBLE DAGGER */
		"\x88" => "?",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
		"\x89" => "?", /* PER MILLE SIGN */
		"\x8a" => "S",   /* LATIN CAPITAL LETTER S WITH CARON */
		"\x8b" => "\x3c", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
		"\x8c" => "OE",   /* LATIN CAPITAL LIGATURE OE */
		"\x8e" => "Z",   /* LATIN CAPITAL LETTER Z WITH CARON */
		"\x91" => "\x27", /* LEFT SINGLE QUOTATION MARK */
		"\x92" => "\x27", /* RIGHT SINGLE QUOTATION MARK */
		"\x93" => "\x22", /* LEFT DOUBLE QUOTATION MARK */
		"\x94" => "\x22", /* RIGHT DOUBLE QUOTATION MARK */
		"\x95" => "\b7", /* BULLET */
		"\x96" => "\x20", /* EN DASH */
		"\x97" => "\x20\x20", /* EM DASH */
		"\x98" => "\x7e",   /* SMALL TILDE */
		"\x99" => "?", /* TRADE MARK SIGN */
		"\x9a" => "S",   /* LATIN SMALL LETTER S WITH CARON */
		"\x9b" => "\x3e;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
		"\x9c" => "oe",   /* LATIN SMALL LIGATURE OE */
		"\x9e" => "Z",   /* LATIN SMALL LETTER Z WITH CARON */
		"\x9f" => "Y"    /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
	);
	$str = strtr($str, $cp1252_map);
	return $str;
}
	
// ----------------------------------------------------------------------------
//	test_title_query() : nouvelle version analyse d'une rech. sur titre
// ----------------------------------------------------------------------------
function test_title_query($query, $operator=TRUE, $force_regexp=FALSE) {
	// Armelle : a priori utilise uniquement dans �dition des p�riodique. Changer la-bas.
	// fonction d'analyse d'une recherche sur titre
	// la fonction retourne un tableau :
	$query_result = array(  'type' => 0,
	                        'restr' => '',
	                        'order' => '',
	                        'nbr_rows' => 0);
	
	// FORCAGE ER 12/05/2004 : le match against avec la troncature* ne fonctionne pas...
	$force_regexp = TRUE ;
	
	// $query_result['type'] = type de la requ�te :
	// 0 : rien (probl�me) 
	// 1: match/against
	// 2: regexp
	// 3: regexp pure sans traitement
	// $query_result['restr'] = crit�res de restriction
	// $query_result['order'] = crit�res de tri
	// $query_result['indice'] = fa�on d'obtenir un indice de pertinence
	// $query_result['nbr_rows'] = nombre de lignes qui matchent
	
	// si operator TRUE La recherche est bool�enne AND
	// si operator FALSE La recherche est bool�enne OR
	// si force_regexp : la recherche est forc�e en mode regexp
	
	$stopwords = FALSE;
	global $dbh;
	
	// initialisation op�rateur
	$operator ? $dopt = 'AND' : $dopt = 'OR';
	
	$query = strtolower($query);
	
	// espaces en d�but et fin
	$query = preg_replace('/^\s+|\s+$/', '', $query);
	
	// espaces en double
	$query = preg_replace('/\s+/', ' ', $query);
	
	
	// traitement des caract�res accentu�s
	$query = convert_diacrit($query);
	
	// contr�le de la requete
	if(!$query)
		return $query_result;
	
	// d�terminer si la requ�te est une regexp
	// si c'est le cas, on utilise la saisie utilisateur sans modification
	// (on part du principe qu'il sait ce qu'il fait)
	
	if(preg_match('/\^|\$|\[|\]|\.|\*|\{|\}|\|/', $query)) {
		// regexp pure : pas de modif de la saisie utilisateur
		$query_result['type'] = 3;
		$query_result['restr'] =  "index_serie REGEXP '$query'";
		$query_result['restr'] .= " OR tit1 REGEXP '$query'";
		$query_result['restr'] .= " OR tit2 REGEXP '$query'";
		$query_result['restr'] .= " OR tit3 REGEXP '$query'";
		$query_result['restr'] .= " OR tit4 REGEXP '$query'";
	       	$query_result['order'] = "index_serie ASC, tnvol ASC, tit1 ASC";
		} else {
	 		// nettoyage de la cha�ne
	 		$query = preg_replace("/[\(\)\,\;\'\!\-\+]/", ' ', $query);
	 		
	 		// on supprime les mots vides
	 		$query = strip_empty_words($query);
	 		
	 		// contr�le de la requete
	 		if(!$query) return $query_result;
	
			// la saisie est split�e en un tableau
			$tab = preg_split('/\s+/', $query);
			
			// on cherche � d�tecter les mots de moins de 4 caract�res (stop words)
			// si il y des mots remplissant cette condition, c'est la m�thode regexp qui sera employ�e
			foreach($tab as $dummykey=>$word) {
				if(strlen($word) < 4) {
					$stopwords = TRUE;
					break;
					}
				}
	
			if($stopwords || $force_regexp) {
				// m�thode REGEXP
				$query_result['type'] = 2;
				 // constitution du membre restricteur
				// premier mot
				$query_result['restr'] = "(index_sew REGEXP '${tab[0]} ) '";
				for ($i = 1; $i < sizeof($tab); $i++) {
					$query_result['restr'] .= " $dopt (index_sew REGEXP '${tab[$i]}' )";
					}
				// contitution de la clause de tri
				$query_result['order'] = "index_serie ASC, tnvol ASC, tit1 ASC";
				} else {
					// m�thode FULLTEXT
					$query_result['type'] = 1;
					// membre restricteur
					$query_result['restr'] = "MATCH (index_wew) AGAINST ('*${tab[0]}*')";
					for ($i = 1; $i < sizeof($tab); $i++) {
						$query_result['restr'] .= " $dopt MATCH";
						$query_result['restr'] .= " (index_wew)";
						$query_result['restr'] .= " AGAINST ('*${tab[$i]}*')";
						}
					// membre de tri
					$query_result['order'] = "index_serie DESC, tnvol ASC, index_sew ASC";
					}
			}
	
	// r�cup�ration du nombre de lignes
	$rws = "SELECT count(1) FROM notices WHERE ${query_result['restr']}";
	$result = @mysql_query($rws, $dbh);
	$query_result['nbr_rows'] = @mysql_result($result, 0, 0);
	
	return $query_result;
	}

//Fonction de pr�paration des chaines pour regexp sans match against
function analyze_query($query) {
	// Armelle - a priori plus utilis�
	// d�terminer si la requ�te est une regexp
	// si c'est le cas, on utilise la saisie utilisateur sans modification
	// (on part du principe qu'il sait ce qu'il fait)
	if(preg_match('/\^|\$|\[|\]|\.|\*|\{|\}|\|\+/', $query)) {
		// traitement des caract�res accentu�s
		$query = preg_replace('/[������������]/'	, 'a', $query);
		$query = preg_replace('/[��������]/'		, 'e', $query);
		$query = preg_replace('/[��������]/'		, 'i', $query);
		$query = preg_replace('/[����������]/'		, 'o', $query);
		$query = preg_replace('/[��������]/'		, 'u', $query);
		$query = preg_replace('/[��]/m'				, 'c', $query);
		return $query;
	} else {
		return reg_diacrit($query);
	}
}

// ----------------------------------------------------------------------------
//	fonction sur les dates
// ----------------------------------------------------------------------------
// today() : retourne la date du jour au format MySQL-DATE
// penser � mettre � jour les classes concern�es
function today() {
	$jour = date('Y-m-d');
	return $jour;
	}

// ----------------------------------------------------------------------------
//	fonction qui retourne le nom de la page courante (SANS L'EXTENSION .php) !
// ----------------------------------------------------------------------------
function current_page() {
	return str_replace("/", "", preg_replace("#\/.*\/(.*\.php)$#", "\\1", $_SERVER["PHP_SELF"]));
	}

// ----------------------------------------------------------------------------
//	fonction gen_liste qui g�n�re des combo_box super sympas
// ----------------------------------------------------------------------------
function gen_liste ($requete, $champ_code, $champ_info, $nom, $on_change, $selected, $liste_vide_code, $liste_vide_info,$option_premier_code,$option_premier_info) {
	$resultat_liste=mysql_query($requete);
	$renvoi="<select name=\"$nom\"  id=\"$nom\" onChange=\"$on_change\">\n";
	$nb_liste=mysql_num_rows($resultat_liste);
	if ($nb_liste==0) {
		$renvoi.="<option value=\"$liste_vide_code\">$liste_vide_info</option>\n";
		} else {
			if ($option_premier_info!="") {	
				$renvoi.="<option value=\"$option_premier_code\" ";
				if ($selected==$option_premier_code) $renvoi.="selected='selected'";
				$renvoi.=">$option_premier_info</option>\n";
				}
			$i=0;
			while ($i<$nb_liste) {
				$renvoi.="<option value=\"".mysql_result($resultat_liste,$i,$champ_code)."\" ";
				if ($selected==mysql_result($resultat_liste,$i,$champ_code)) $renvoi.="selected";
				$renvoi.=">".mysql_result($resultat_liste,$i,$champ_info)."</option>\n";
				$i++;
				}
			}
	$renvoi.="</select>\n";
	return $renvoi;
	}

// ----------------------------------------------------------------------------
//	fonction qui retourne le nom de la page courante (SANS L'EXTENSION .php) !
// ----------------------------------------------------------------------------
function inslink($texte="", $lien="",$param="") {
	if ($lien) return "<a href='$lien' $param>$texte</a>" ;
	else return "$texte" ;
}

// ----------------------------------------------------------------------------
//	fonction qui ins�re l'entr�e $entree dans un table si image possible avec le $code
// ----------------------------------------------------------------------------
function do_image(&$entree, $code, $depliable ) {
	global $charset;
	global $opac_show_book_pics ;
	global $opac_book_pics_url ;
	global $opac_book_pics_msg;
	global $opac_url_base ;
	if ($code<>"") {
		if ($opac_show_book_pics=='1' && $opac_book_pics_url) {
			$code_chiffre = preg_replace('/-|\.| /', '', $code);
			$url_image = $opac_book_pics_url ;
			$url_image = $opac_url_base."getimage.php?url_image=".urlencode($url_image)."&noticecode=!!noticecode!!" ;
			if ($depliable) $image = "<img src='$opac_url_base/images/vide.png' align='right' hspace='4' vspace='2' isbn='".$code_chiffre."' url_image='".$url_image."'>";
				else {
					$url_image_ok = str_replace("!!noticecode!!", $code_chiffre, $url_image) ;
					$title_image_ok = htmlentities($opac_book_pics_msg, ENT_QUOTES, $charset);
					$image = "<img src='".$url_image_ok."' title=\"".$title_image_ok."\" align='right' hspace='4' vspace='2'>";
					}
			} else $image="" ;
		if ($image) $entree = "<table width='100%'><tr><td>$entree</td><td valign=top align=right>$image</td></tr></table>" ;
			else $entree = "<table width='100%'><tr><td>$entree</td></tr></table>" ;

		} else $entree = "<table width='100%'><tr><td>$entree</td></tr></table>" ;	
	}

// ------------------------------------------------------------------
//  pmb_preg_match($regex,$chaine) : recherche d'une regex
// ------------------------------------------------------------------
function pmb_preg_match($regex,$chaine) {
	global $charset;
	if ($charset != 'utf-8') {
		return preg_match($regex,$chaine);
	}
	else {
		return preg_match($regex.'u',$chaine);
	}
}

// ------------------------------------------------------------------
//  pmb_preg_replace($regex,$replace,$chaine) : remplacement d'une regex par une autre
// ------------------------------------------------------------------
function pmb_preg_replace($regex,$replace,$chaine) {
	global $charset;
	if ($charset != 'utf-8') {
		return preg_replace($regex,$replace,$chaine);
	}
	else {
		return preg_replace($regex.'u',$replace,$chaine);
	}
}

// ------------------------------------------------------------------
//  pmb_str_replace($toreplace,$replace,$chaine) : remplacement d'une chaine par une autre
// ------------------------------------------------------------------
function pmb_str_replace($toreplace,$replace,$chaine) {
	global $charset;
	if ($charset != 'utf-8') {
		return str_replace($toreplace,$replace,$chaine);
	}
	else {
		return preg_replace("/".$toreplace."/u",$replace,$chaine);
	}
}

// ------------------------------------------------------------------
//  pmb_split($separateur,$string) : s�pare un chaine de caract�re selon un separateur
// ------------------------------------------------------------------
function pmb_split($separateur,$chaine) {
	global $charset;
	if ($charset != 'utf-8') {
		return preg_split($separateur,$chaine);
	}
	else {
		return mb_split($separateur,$chaine);
	}
}

/* 
 * ------------------------------------------------------------------
 * pmb_alphabetic($regex,$replace,$string) : enleve les caracteres non alphabetique. Equivalent de [a-z0-9]
 * 
 * Pour les caracteres latins;
 * Pour l'instant pour les caracteres non latins:
 * Armenien :
 * \x{0531}-\x{0587}\x{fb13}-\x{fb17}
 * Arabe :
 * \x{0621}-\x{0669}\x{066E}-\x{06D3}\x{06D5}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}
 * Cyrillique :	
 * \x{0400}-\x{0486}\x{0488}-\x{0513}
 * Chinois : 
 * \x{4E00}-\x{9BFF}
 * Japonais (Hiragana - Katakana - Suppl. phonetique katakana - Katakana demi-chasse) :
 * \x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{31F0}-\x{31FF}\x{FF00}-\x{FFEF}
 * Grec :
 * \x{0386}\x{0388}-\x{038A}\x{038C}\x{038E}-\x{03A1}\x{03A3}-\x{03CE}\x{03D0}\x{03FF}\x{1F00}-\x{1F15}\x{1F18}-\x{1F1D}\x{1F20}-\x{1F45}\x{1F48}-\x{1F4D}\x{1F50}-\x{1F57}\x{1F59}\x{1F5B}\x{1F5D}\x{1F5F}-\x{1F7D}\x{1F80}-\x{1FB4}\x{1FB6}-\x{1FBC}\x{1FC2}-\x{1FC4}\x{1FC6}-\x{1FCC}\x{1FD0}-\x{1FD3}\x{1FD6}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FF4}\x{1FF6}-\x{1FFC}
 * G�orgien
 * \x{10A0}-\x{10C5}\x{10D0}-\x{10FC}\x{2D00}-\x{2D25}
 * ------------------------------------------------------------------
 */

function pmb_alphabetic($regex,$replace,$string) {
	global $charset;
	
	if ($charset != 'utf-8') {
		return preg_replace('/['.$regex.']/', ' ', $string);	
	} else {
		return preg_replace('/['.$regex
				.'\x{0531}-\x{0587}\x{fb13}-\x{fb17}'
				.'\x{0621}-\x{0669}\x{066E}-\x{06D3}\x{06D5}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}'
				.'\x{0400}-\x{0486}\x{0488}-\x{0513}'
				.'\x{4E00}-\x{9BFF}'
				.'\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{31F0}-\x{31FF}\x{FF00}-\x{FFEF}'
				.'\x{0386}\x{0388}-\x{038A}\x{038C}\x{038E}-\x{03A1}\x{03A3}-\x{03CE}\x{03D0}\x{03FF}\x{1F00}-\x{1F15}\x{1F18}-\x{1F1D}\x{1F20}-\x{1F45}\x{1F48}-\x{1F4D}\x{1F50}-\x{1F57}\x{1F59}\x{1F5B}\x{1F5D}\x{1F5F}-\x{1F7D}\x{1F80}-\x{1FB4}\x{1FB6}-\x{1FBC}\x{1FC2}-\x{1FC4}\x{1FC6}-\x{1FCC}\x{1FD0}-\x{1FD3}\x{1FD6}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FF4}\x{1FF6}-\x{1FFC}'
				.'\x{10A0}-\x{10C5}\x{10D0}-\x{10FC}\x{2D00}-\x{2D25}'
				.']/u', ' ', $string);
	}
}

// ------------------------------------------------------------------
//  pmb_strlen($string) : calcule la longueur d'une chaine pour utf-8 il s'agit du nombre de caract�res.
// ------------------------------------------------------------------
function pmb_strlen($string) {
	global $charset;
	
	if ($charset != 'utf-8') 
		return strlen($string);
	else {
		return mb_strlen($string,$charset);
	}		
}

// ------------------------------------------------------------------
//  pmb_getcar($currentcar,$string) : recupere le caractere $cuurentcar de la chaine
// ------------------------------------------------------------------
function pmb_getcar($currentcar,$string) {
	global $charset;
	
	if ($charset != 'utf-8') 
		return $string[$currentcar];
	else {
		return mb_substr($string,$currentcar, 1,$charset);
	}		
}

// ------------------------------------------------------------------
//  pmb_substr($chaine,$depart,$longueur) : recupere n caracteres 
// ------------------------------------------------------------------
function pmb_substr($chaine,$depart,$longueur=0) {
	global $charset;
	
	if ($charset != 'utf-8') { 
		if ($longueur == 0)
			return substr($chaine,$depart);
		else
			return substr($chaine,$depart,$longueur);
	}
	else {
		if ($longueur == 0)
			return mb_substr($chaine,$depart,$charset);
		else
			return mb_substr($chaine,$depart,$longueur,$charset);
	}		
}

// ------------------------------------------------------------------
//  pmb_strtolower($string) : passage d'une chaine de caract�re en minuscule
// ------------------------------------------------------------------
function pmb_strtolower($string) {
	global $charset;
	if ($charset != 'utf-8') {
		return strtolower($string);
	}
	else {
		return mb_strtolower($string,$charset);
	}
}

// ------------------------------------------------------------------
//  pmb_strtoupper($string) : passage d'une chaine de caract�re en majuscule
// ------------------------------------------------------------------
function pmb_strtoupper($string) {
	global $charset;
	if ($charset != 'utf-8') {
		return strtoupper($string);
	}
	else {
		return mb_strtoupper($string,$charset);
	}
}

// ------------------------------------------------------------------
//  pmb_escape() : renvoi la bonne fonction javascript en fonction du charset
// ------------------------------------------------------------------
function pmb_escape() {
	global $charset;
	if ($charset != 'utf-8') {
		return "escape";
	}
	else {
		return "encodeURIComponent";
	}
}

// ------------------------------------------------------------------
//  pmb_bidi($string) : renvoi la chaine de caractere en g�rant les problemes 
//  d'affichage droite gauche des parenth�ses
// ------------------------------------------------------------------
function pmb_bidi($string) {
	global $charset;
	global $lang;
	
	return $string;
	
	if ($charset != 'utf-8' or $lang == 'ar') {
		// utf-8 obligatoire pour l'arabe
		return $string;
	}
	else {
		//\x{0600}-\x{06FF}\x{0750}-\x{077F} : Arabic
		//x{0590}-\x{05FF} : hebrew
		if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{0590}-\x{05FF}]/u', $string)) {

			// 1 - j'entoure les caract�res arabes + espace ou parenthese ou chiffre de <span dir=rtl>'
			 $string = preg_replace("/([\s*(&nbsp;)*(&amp;)*\-*\(*0-9*]*[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{0590}-\x{05FF}]+([,*\s*(&nbsp;)*(&amp;)*\-*\(*0-9*]*[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{0590}-\x{05FF}]*[,*\s*(&nbsp;)*(&amp;)*\-*\)*0-9*]*)*)/u","<span dir='rtl'>\\1</span>",$string);
			 // 2 - j'enleve les span dans les 'value' ca marche pas dans les ecrans de saisie
			 $string = preg_replace('/value=[\'\"]<span dir=\'rtl\'>(.*?)<\/span>[\'\"]/u','value=\'\\1\'',$string);
			 // 3 - j'enleve les span dans les 'title'
			 $string = preg_replace('/title=[\'\"]<span dir=\'rtl[\'\"]>(.*?)<\/span>/u','title=\'\\1',$string);
			 // 4 - j'enleve les span dans les 'alt'
			 $string = preg_replace('/alt=[\'\"]<span dir=\'rtl[\'\"]>(.*?)<\/span>/u','alt=\'\\1',$string);
			 // 4 - j'enleve les span sont entre cote, c'est que c'est dans une valeur.
			 $string = preg_replace('/[\'\"]<span dir=\'rtl[\'\"]>(.*?)<\/span>\'/u','\'\\1\'',$string);
			 // 4 - j'enleve les span dans les textarea.
			 //preg_match('/<textarea(.*?)><span dir=\'rtl[\'\"](.*?)<\/span>/u',$string,$toto);
			 //printr($toto);
			 $string = preg_replace('/<textarea(.*?)><span dir=\'rtl[\'\"](.*?)<\/span>/u','<textarea \\1 \\2',$string);
			 return $string;
		}
		else {
			return $string;
		}
		
	}
}

function gen_plus_form($id, $titre, $contenu,$startopen=false) {
	return "	
		<div class='row'></div>
		<div id='$id' class='notice-parent'>
			<img src='./getgif.php?nomgif=plus' name='imEx' id='$id" . "Img' title='".addslashes($msg['plus_detail'])."' border='0' onClick=\"expandBase('$id', true); return false;\" hspace='3'>
			<span class='notice-heada'>
				$titre
			</span>
		</div>
		<div id='$id" . "Child' class='notice-child' ".($startopen?"startOpen='Yes' ":"")."style='margin-bottom:6px;display:none;width:94%'>
			$contenu
		</div>
		";
}

// ------------------------------------------------------------------
//  mail_bloc_adresse() : renvoie un code HTML contenant le bloc d'adresse � mettre en bas 
//  des mails envoy�s par PMB (r�sa, pr�ts) 
// ------------------------------------------------------------------
function mail_bloc_adresse() {
	global $msg ;
	global $biblio_name, $biblio_email,$biblio_website ;
	global $biblio_adr1, $biblio_adr2, $biblio_cp, $biblio_town, $biblio_phone ; 
	$ret = $biblio_name ;
	if ($biblio_adr1) $ret .= "<br />".$biblio_adr1 ;  
	if ($biblio_adr2) $ret .= "<br />".$biblio_adr2 ;  
	if ($biblio_cp && $biblio_town) $ret .= "<br />".$biblio_cp." ".$biblio_town ;
	elseif ($biblio_town) $ret .= "<br />".$biblio_cp." ".$biblio_town ;
	if ($biblio_phone) $ret .= "<br />".$msg['location_details_phone']." ".$biblio_phone ;
	if ($biblio_email) $ret .= "<br />".$msg['location_details_email']." ".$biblio_email ;
	if ($biblio_website) $ret .= "<br />".$msg['location_details_website']." <a href='".$biblio_website."'>".$biblio_website."</a>" ;

	return $ret ;
}

//---------------------------------
//CONFIGURATION DU PROXY POUR CURL
//---------------------------------

function configurer_proxy_curl(&$curl){
	global $opac_curl_proxy;
	
	if($opac_curl_proxy!=''){
		$param_proxy = explode(',',$opac_curl_proxy);
		$adresse_proxy = $param_proxy[0];
		$port_proxy = $param_proxy[1];
		$user_proxy = $param_proxy[2];
		$pwd_proxy = $param_proxy[3];
		
		curl_setopt($curl, CURLOPT_PROXY, $adresse_proxy);
		curl_setopt($curl, CURLOPT_PROXYPORT, $port_proxy);
		curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$user_proxy:$pwd_proxy");
	}

}

//remplacement espace ins�cable 0xA0: &nbsp; Non-breaking space => probl�me li� � certaine version de navigateur
function clean_nbsp($input) {	
	global $charset;
    if($charset=="iso-8859-1")$input = str_replace(chr(0xa0), ' ', $input);
    return $input;
}

function addslashes_array($input_arr){
    if(is_array($input_arr)){
        $tmp = array();
        foreach ($input_arr as $key1 => $val){
            $tmp[$key1] = addslashes_array($val);
        }
        return $tmp;
    } 
    else {
    	if (is_string($input_arr))
        	return addslashes($input_arr);
        else
        	return $input_arr;
    }
}

function stripslashes_array($input_arr){
    if(is_array($input_arr)){
        $tmp = array();
        foreach ($input_arr as $key1 => $val){
            $tmp[$key1] = stripslashes_array($val);
        }
        return $tmp;
    } 
    else {
    	if (is_string($input_arr))
        	return stripslashes($input_arr);
        else
        	return $input_arr;
    }
}

function console_log($msg_to_log){
	print "<script type='text/javascript'>if(typeof console != 'undefined') {console.log('".addslashes($msg_to_log)."');}</script>";
}

function parseHTML($buffer){
	$htmlparser=new parse_format("inhtml.inc.php");		
	$htmlparser->cmd = $buffer;
	return $htmlparser->exec_cmd(true);
}

function gen_plus($id,$titre,$contenu,$maximise=0,$script_before='', $script_after='') {
	global $msg;
	if($maximise) $max=" startOpen=\"Yes\""; else $max='';
	return "	
	<div class='row'></div>
	<div id='$id' class='notice-parent'>
		<img src='./getgif.php?nomgif=plus' class='img_plus' name='imEx' id='$id"."Img' title='".$msg['plus_detail']."' border='0' onClick=\"expandBase('$id', true); return false;\" hspace='3'>
		<span class='notice-heada'>
			$titre
		</span>
	</div>
	<div id='$id"."Child' class='notice-child' style='margin-bottom:6px;display:none;width:94%' $max>
		$contenu
	</div>
	";
}

function pmb_utf8_decode($elem){
	if(is_array($elem)){
		foreach ($elem as $key =>$value){
			$elem[$key] = pmb_utf8_decode($value);
		}
	}else if(is_object($elem)){
		$elem = pmb_obj2array($elem);
		$elem = pmb_utf8_decode($elem);
	}else{
		$elem = utf8_decode($elem);
	}
	return $elem;
}

function pmb_utf8_encode($elem){
	if(is_array($elem)){
		foreach ($elem as $key =>$value){
			$elem[$key] = pmb_utf8_encode($value);
		}
	}else if(is_object($elem)){
		$elem = pmb_obj2array($elem);
		$elem = pmb_utf8_encode($elem);
	}else{
		$elem = utf8_encode($elem);
	}
	
	return $elem;
}

function pmb_utf8_array_encode($elem){
	global $charset;
	if($charset != "utf-8"){
		return pmb_utf8_encode($elem);
	}else{
		return $elem;
	}
}

function pmb_utf8_array_decode($elem){
	global $charset;
	if($charset != "utf-8"){
		return pmb_utf8_decode($elem);
	}else{
		return $elem;
	}
}

function pmb_obj2array($obj){
	$array = array();
	if(is_object($obj)){
		foreach($obj as $key => $value){
			if(is_object($value)){
				$value = pmb_obj2array($value);
			}
			$array[$key] = $value;
		}
	}else{
		$array = $obj;
	}
	return $array;
}
