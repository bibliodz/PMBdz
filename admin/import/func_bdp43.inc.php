<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_bdp43.inc.php,v 1.4 2009-05-16 11:15:42 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// DEBUT param�trage propre � la base de donn�es d'importation :
$section_bdp43=array(
	"Bande-dessin�e Adultes",
	"Romans Adultes",
	"Romans policiers Adultes",
	"Documentaires Adultes",
	"Fond local",
	"P�riodiques adultes",
	"Albums",
	"Bande-dessin�e Jeunes",
	"Contes",
	"Romans Enfants",
	"Romans fantastiques",
	"Documentaires Jeunes",
	"Livres parl�s",
	"Exposition",
	"Musique"
	);


$corresp_bdp43=array(
	array("DA"),
	array("R"),
	array("RP"),
	array("1","2","3","4","5","6","7","8","9","0"),
	array("V1","V2","V3","V4","V5","V6","V7","V8","V9","V0","VR"),
	array("PER"),
	array("A"),
	array("D"),
	array("C"),
	array("JR"),
	array("SF"),
	array("J1","J2","J3","J4","J5","J6","J7","J8","J9","J0"),
	array("LP"),
	array("LUDO","AFF"),
	array("1.","2.","3.","4.","5.","6.","7.","8.","9.","0.", "GF")
	);

$sec_search_bdp43=array(
"DA",
"RP",
"R",
"1.","2.","3.","4.","5.","6.","7.","8.","9.","0.", "GF",
"1","2","3","4","5","6","7","8","9","0",
"V1","V2","V3","V4","V5","V6","V7","V8","V9","V0","VR",
"PER",
"LUDO","AFF",
"A",
"D",
"C",
"JR",
"SF",
"J1","J2","J3","J4","J5","J6","J7","J8","J9","J0",
"LP"
);

function recup_noticeunimarc_suite($notice) {
	} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres BDP : rien de plus
	
function import_new_notice_suite() {
	global $dbh ;
	global $notice_id ;
	
	global $index_sujets ;
	global $pmb_keyword_sep ;
	
	global $info_600_a, $info_600_j, $info_600_x, $info_600_y, $info_600_z ;
	global $info_601_a, $info_601_j, $info_601_x, $info_601_y, $info_601_z ;
	global $info_602_a, $info_602_j, $info_602_x, $info_602_y, $info_602_z ;
	global $info_605_a, $info_605_j, $info_605_x, $info_605_y, $info_605_z ;
	global $info_606_a, $info_606_j, $info_606_x, $info_606_y, $info_606_z ;
	global $info_607_a, $info_607_j, $info_607_x, $info_607_y, $info_607_z ;

	if (is_array($index_sujets)) $mots_cles = implode (" $pmb_keyword_sep ",$index_sujets);
		else $mots_cles = $index_sujets;
	
	for ($a=0; $a<sizeof($info_600_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_600_a[$a][0] ;
		for ($j=0; $j<sizeof($info_600_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_600_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_600_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_600_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_600_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_600_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_600_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_600_z[$a][$j] ;
		}
	for ($a=0; $a<sizeof($info_601_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_601_a[$a][0] ;
		for ($j=0; $j<sizeof($info_601_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_601_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_601_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_601_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_601_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_601_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_601_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_601_z[$a][$j] ;
		}
	for ($a=0; $a<sizeof($info_602_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_602_a[$a][0] ;
		for ($j=0; $j<sizeof($info_602_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_602_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_602_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_602_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_602_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_602_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_602_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_602_z[$a][$j] ;
		}
	for ($a=0; $a<sizeof($info_605_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_605_a[$a][0] ;
		for ($j=0; $j<sizeof($info_605_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_605_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_605_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_605_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_605_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_605_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_605_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_605_z[$a][$j] ;
		}
	for ($a=0; $a<sizeof($info_606_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_606_a[$a][0] ;
		for ($j=0; $j<sizeof($info_606_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_606_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_606_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_606_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_606_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_606_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_606_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_606_z[$a][$j] ;
		}
	for ($a=0; $a<sizeof($info_607_a); $a++) {
		$mots_cles .= " $pmb_keyword_sep ".$info_607_a[$a][0] ;
		for ($j=0; $j<sizeof($info_607_j[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_607_j[$a][$j] ;
		for ($j=0; $j<sizeof($info_607_x[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_607_x[$a][$j] ;
		for ($j=0; $j<sizeof($info_607_y[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_607_y[$a][$j] ;
		for ($j=0; $j<sizeof($info_607_z[$a]); $j++) $mots_cles .= " $pmb_keyword_sep ".$info_607_z[$a][$j] ;
		}
	$mots_cles ? $index_matieres = strip_empty_words($mots_cles) : $index_matieres = '';
	$rqt_maj = "update notices set index_l='".addslashes($mots_cles)."', index_matieres=' ".addslashes($index_matieres)." ' where notice_id='$notice_id' " ;
	$res_ajout = mysql_query($rqt_maj, $dbh);
	} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $msg, $dbh ;
	global $nb_expl_ignores ;
	global $prix, $notice_id, $info_995, $typdoc_995, $tdoc_codage, $book_lender_id, 
		$section_bdp43, $sec_search_bdp43,$corresp_bdp43,$section_995, $sdoc_codage, $book_statut_id, $locdoc_codage, $codstatdoc_995, $statisdoc_codage,
		$cote_mandatory, $book_location_id ;
		
	// lu en 010$d de la notice
	$price = $prix[0];
	
	// la zone 995 est r�p�table
	for ($nb_expl = 0; $nb_expl < sizeof ($info_995); $nb_expl++) {
		/* RAZ expl */
		$expl = array();
		
		/* pr�paration du tableau � passer � la m�thode */
		$expl['cb'] 	    = $info_995[$nb_expl]['f'];
		$expl['notice']     = $notice_id ;
		
		// $expl['typdoc']     = $info_995[$nb_expl]['r']; � chercher dans docs_typdoc
		$data_doc=array();
		//$data_doc['tdoc_libelle'] = $info_995[$nb_expl]['r']." -Type doc import� (".$book_lender_id.")";
		$data_doc['tdoc_libelle'] = $typdoc_995[$info_995[$nb_expl]['r']];
		if (!$data_doc['tdoc_libelle']) $data_doc['tdoc_libelle'] = "\$r non conforme -".$info_995[$nb_expl]['r']."-" ;
		$data_doc['duree_pret'] = 0 ; /* valeur par d�faut */
		$data_doc['tdoc_codage_import'] = $info_995[$nb_expl]['r'] ;
		if ($tdoc_codage) $data_doc['tdoc_owner'] = $book_lender_id ;
			else $data_doc['tdoc_owner'] = 0 ;
		$expl['typdoc'] = docs_type::import($data_doc);
		
		$expl['cote'] = $info_995[$nb_expl]['k'];
                      	
		// traitement des sections en fonction de la cote
		// recherche d�but dans le tableau bien ordonn� afin de trouv� les DA avant les D
		// 		si trouv� : on va le chercher dans le tableau de tableau, 
		//			�a donne l'index avec lequel on va chercher le libell�
		reset($sec_search_bdp43) ;
		$flag = 0 ;
		while (list($cle_tab,$val_tab)=each($sec_search_bdp43)) {
			$p=strpos((string)$info_995[$nb_expl]['k'],(string)$val_tab) ;
			if (($p!==false) && ($p==0)) {
				$flag=1;
				break;
				}
			}
		if ($flag==1) {
			//Recherche de la section
			for ($i=0; $i<count($corresp_bdp43); $i++) {
				$as=array_search($val_tab,$corresp_bdp43[$i]);
				if (($as!==null)&&($as!==false)) {
					$codage_section_lu=$i+1;
					$libelle_section_lu=$section_bdp43[$i];
				}
			}
		} else {
				$codage_section_lu = "INCONNU" ;
				$libelle_section_lu = "Section inconnue" ;
				}
		
		$data_doc=array();
		$data_doc['section_libelle'] = $libelle_section_lu;
		$data_doc['sdoc_codage_import'] = $codage_section_lu ;
		if ($sdoc_codage) $data_doc['sdoc_owner'] = $book_lender_id ;
			else $data_doc['sdoc_owner'] = 0 ;
		$expl['section'] = docs_section::import($data_doc);
		
		
		/* $expl['statut']     � chercher dans docs_statut */
		/* TOUT EST COMMENTE ICI, le statut est maintenant choisi lors de l'import
		if ($info_995[$nb_expl]['o']=="") $info_995[$nb_expl]['o'] = "e";
		$data_doc=array();
		$data_doc['statut_libelle'] = $info_995[$nb_expl]['o']." -Statut import� (".$book_lender_id.")";
		$data_doc['pret_flag'] = 1 ; 
		$data_doc['statusdoc_codage_import'] = $info_995[$nb_expl]['o'] ;
		$data_doc['statusdoc_owner'] = $book_lender_id ;
		$expl['statut'] = docs_statut::import($data_doc);
		FIN TOUT COMMENTE */
		
		$expl['statut'] = $book_statut_id;
		
		$expl['location'] = $book_location_id;
		
		// $expl['codestat']   = $info_995[$nb_expl]['q']; 'q' utilis�, �ventuellement � fixer par combo_box
		$data_doc=array();
		//$data_doc['codestat_libelle'] = $info_995[$nb_expl]['q']." -Pub vis� import� (".$book_lender_id.")";
		$data_doc['codestat_libelle'] = $codstatdoc_995[$info_995[$nb_expl]['q']];
		$data_doc['statisdoc_codage_import'] = $info_995[$nb_expl]['q'] ;
		if ($statisdoc_codage) $data_doc['statisdoc_owner'] = $book_lender_id ;
			else $data_doc['statisdoc_owner'] = 0 ;
		$expl['codestat'] = docs_codestat::import($data_doc);
		
		
		// $expl['creation']   = $info_995[$nb_expl]['']; � pr�ciser
		// $expl['modif']      = $info_995[$nb_expl]['']; � pr�ciser
                      	
		$expl['note']       = $info_995[$nb_expl]['u'];
		$expl['prix']       = $price;
		$expl['expl_owner'] = $book_lender_id ;
		$expl['cote_mandatory'] = $cote_mandatory ;
		
		$expl['date_depot'] = substr($info_995[$nb_expl]['m'],0,4)."-".substr($info_995[$nb_expl]['m'],4,2)."-".substr($info_995[$nb_expl]['m'],6,2) ;      
		$expl['date_retour'] = substr($info_995[$nb_expl]['n'],0,4)."-".substr($info_995[$nb_expl]['n'],4,2)."-".substr($info_995[$nb_expl]['n'],6,2) ;
		
		// quoi_faire
		if ($info_995[$nb_expl]['0']) $expl['quoi_faire'] = $info_995[$nb_expl]['0']  ;
			else $expl['quoi_faire'] = 2 ;
		
		$expl_id = exemplaire::import($expl);
		if ($expl_id == 0) {
			$nb_expl_ignores++;
			}
                      	
		//debug : affichage zone 995 
		/*
		echo "995\$a =".$info_995[$nb_expl]['a']."<br />";
		echo "995\$b =".$info_995[$nb_expl]['b']."<br />";
		echo "995\$c =".$info_995[$nb_expl]['c']."<br />";
		echo "995\$d =".$info_995[$nb_expl]['d']."<br />";
		echo "995\$f =".$info_995[$nb_expl]['f']."<br />";
		echo "995\$k =".$info_995[$nb_expl]['k']."<br />";
		echo "995\$m =".$info_995[$nb_expl]['m']."<br />";
		echo "995\$n =".$info_995[$nb_expl]['n']."<br />";
		echo "995\$o =".$info_995[$nb_expl]['o']."<br />";
		echo "995\$q =".$info_995[$nb_expl]['q']."<br />";
		echo "995\$r =".$info_995[$nb_expl]['r']."<br />";
		echo "995\$u =".$info_995[$nb_expl]['u']."<br /><br />";
		*/
		} // fin for
	} // fin traite_exemplaires	TRAITEMENT DES EXEMPLAIRES JUSQU'ICI

// fonction sp�cifique d'export de la zone 995
function export_traite_exemplaires ($ex=array()) {
	global $msg, $dbh ;
	
	$subfields["a"] = $ex -> lender_libelle;
	$subfields["c"] = $ex -> lender_libelle;
	$subfields["f"] = $ex -> expl_cb;
	$subfields["k"] = $ex -> expl_cote;
	$subfields["u"] = $ex -> expl_note;

	if ($ex->statusdoc_codage_import) $subfields["o"] = $ex -> statusdoc_codage_import;
	if ($ex -> tdoc_codage_import) $subfields["r"] = $ex -> tdoc_codage_import;
		else $subfields["r"] = "uu";
	if ($ex -> sdoc_codage_import) $subfields["q"] = $ex -> sdoc_codage_import;
		else $subfields["q"] = "u";
	
	global $export996 ;
	$export996['f'] = $ex -> expl_cb ;
	$export996['k'] = $ex -> expl_cote ;
	$export996['u'] = $ex -> expl_note ;

	$export996['m'] = substr($ex -> expl_date_depot, 0, 4).substr($ex -> expl_date_depot, 5, 2).substr($ex -> expl_date_depot, 8, 2) ;
	$export996['n'] = substr($ex -> expl_date_retour, 0, 4).substr($ex -> expl_date_retour, 5, 2).substr($ex -> expl_date_retour, 8, 2) ;

	$export996['a'] = $ex -> lender_libelle;
	$export996['b'] = $ex -> expl_owner;

	$export996['v'] = $ex -> location_libelle;
	$export996['w'] = $ex -> ldoc_codage_import;

	$export996['x'] = $ex -> section_libelle;
	$export996['y'] = $ex -> sdoc_codage_import;

	$export996['e'] = $ex -> tdoc_libelle;
	$export996['r'] = $ex -> tdoc_codage_import;

	$export996['1'] = $ex -> statut_libelle;
	$export996['2'] = $ex -> statusdoc_codage_import;
	$export996['3'] = $ex -> pret_flag;
	
	global $export_traitement_exemplaires ;
	$export996['0'] = $export_traitement_exemplaires ;
	
	return 	$subfields ;

	}	