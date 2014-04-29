<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_27S.inc.php,v 1.7 2009-05-16 11:15:42 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");




// +-------------------------------------------------+

// Attention, n'a pas �t� modifi� pour le multi-thesaurus

// +-------------------------------------------------+





// DEBUT param�trage propre � la base de donn�es d'importation :
//	les champs UNIMARC lus qui vont �tre stock�s dans des champs personnalis�s sont pr�cis�s ici
$id949_a = 2 ; // autres CDU
$id949_c = 1 ; // numero de document
$id949_d = 3 ; // signature du catalographe
$ISO_decode_do_not_decode = 1;

function recup_noticeunimarc_suite($notice) {
	global $info_949		;

	$info_949 = array();

	$record = new iso2709_record($notice, AUTO_UPDATE); 
	for ($i=0;$i<count($record->inner_directory);$i++) {
		$cle=$record->inner_directory[$i]['label'];
		switch($cle) {
			case "949": // infos bibliotheque 27 septembre
				$info_949=$record->get_subfield($cle,"a","c","d");
				break;
			default:
				break;
	
			} /* end of switch */
	
		} /* end of for */
	} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres au CNL
	
	
function import_new_notice_suite() {
	global $dbh ;
	global $notice_id ;
	
	global $info_949 ;
	global $id949_a, $id949_c, $id949_d;
	
	global $info_606_a, $info_606_j, $info_606_x, $info_606_y, $info_606_z ;
	global $id_rech_theme; 

      $id_rech_theme = 0;

//	echo "<pre>";
//	print_r ($info_949);
//	echo "</pre>";

	// 949$a est stock� dans un champ personnalis� texte
	// ce champ personnalis� a l'id $id949_a
	// TRAITEMENT :
	//	Rechercher si l'enregistrement existe d�j� dans notices_custom_values = SELECT 1 FROM notices_custom_values WHERE notices_custom_champ=$id949_a AND notices_custom_origine=$notice_id
	//	Cr�er si besoin
	$rqt = "SELECT count(1) FROM notices_custom_values WHERE notices_custom_champ='".$id949_a."' AND notices_custom_origine='".$notice_id."' " ;
	if (!mysql_result(mysql_query($rqt, $dbh),0,0)) {
		$rqt_ajout = "INSERT INTO notices_custom_values (notices_custom_champ, notices_custom_origine, notices_custom_small_text) VALUES ('".$id949_a."', '".$notice_id."', '".$info_949[0]['a']."')" ;
		$res_ajout = mysql_query ($rqt_ajout, $dbh) ;
		}

	// 949$c est stock� dans un champ personnalis� texte
	// ce champ personnalis� a l'id $id949_c
	// TRAITEMENT :
	//	Rechercher si l'enregistrement existe d�j� dans notices_custom_values = SELECT 1 FROM notices_custom_values WHERE notices_custom_champ=$id949_c AND notices_custom_origine=$notice_id
	//	Cr�er si besoin
	$rqt = "SELECT count(1) FROM notices_custom_values WHERE notices_custom_champ='".$id949_c."' AND notices_custom_origine='".$notice_id."' " ;
	if (!mysql_result(mysql_query($rqt, $dbh),0,0)) {
		$rqt_ajout = "INSERT INTO notices_custom_values (notices_custom_champ, notices_custom_origine, notices_custom_small_text, notices_custom_integer) VALUES ('".$id949_c."', '".$notice_id."', '".$info_949[0]['c']."', ".$info_949[0]['c'].")" ;
		$res_ajout = mysql_query ($rqt_ajout, $dbh) ;
		}

	// 949$d est stock� dans un champ personnalis� texte
	// ce champ personnalis� a l'id $id949_d
	// TRAITEMENT :
	//	Rechercher si l'enregistrement existe d�j� dans notices_custom_values = SELECT 1 FROM notices_custom_values WHERE notices_custom_champ=$id949_d AND notices_custom_origine=$notice_id
	//	Cr�er si besoin
	$rqt = "SELECT count(1) FROM notices_custom_values WHERE notices_custom_champ='".$id949_d."' AND notices_custom_origine='".$notice_id."' " ;
	if (!mysql_result(mysql_query($rqt, $dbh),0,0)) {
		$rqt_ajout = "INSERT INTO notices_custom_values (notices_custom_champ, notices_custom_origine, notices_custom_small_text) VALUES ('".$id949_d."', '".$notice_id."', '".$info_949[0]['d']."')" ;
		$res_ajout = mysql_query ($rqt_ajout, $dbh) ;
		}

	// les champs $606 sont stock�s dans les cat�gories
	//	$a >> en sous cat�gories de $id_rech_theme
	// 		$j en compl�ment de $a
	//		$x en sous cat�gories de $a
	// TRAITEMENT :
	// pour $a=0 � size_of $info_606_a
	//	pour $j=0 � size_of $info_606_j[$a]
	//		concat�ner $libelle_j .= $info_606_j[$a][$j]
	//	$libelle_final = $info_606_a[0]." ** ".$libelle_j
	//	Rechercher si l'enregistrement existe d�j� dans categories = 
	//		SELECT categ_id FROM categories WHERE categ_parent='".$id_rech_theme."' AND categ_libelle='".addslashes($libelle_final)."' "
	//	Cr�er si besoin et r�cup�rer l'id $categid_a
	//	$categid_parent =  $categid_a
	//	pour $x=0 � size_of $info_606_x[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//			SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_x[$a][$x])."' "
	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	for ($a=0; $a<sizeof($info_606_a); $a++) {
		for ($j=0; $j<sizeof($info_606_j[$a]); $j++) {
			if (!$libelle_j) $libelle_j .= $info_606_j[$a][$j] ;
				else $libelle_j .= " ** ".$info_606_j[$a][$j] ;
			}
		if (!$libelle_j) $libelle_final = $info_606_a[$a][0] ;
			else $libelle_final = $info_606_a[$a][0]." ** ".$libelle_j ;
		if (!$libelle_final) break ; 
		$rqt_a = "SELECT categ_id FROM categories WHERE categ_parent='".$id_rech_theme."' AND categ_libelle='".addslashes($libelle_final)."' " ;
		$res_a = mysql_query($rqt_a,$dbh) ;
		if (mysql_num_rows($res_a)) {
			$categid_a = mysql_result($res_a, 0, 0) ;
			} else {
				$rqt_ajout = "insert into categories set categ_parent='".$id_rech_theme."', categ_libelle='".addslashes($libelle_final)."', index_categorie=' ".strip_empty_words($libelle_final)." ' " ;
				$res_ajout = mysql_query($rqt_ajout, $dbh);
				$categid_a = mysql_insert_id($dbh) ;
				}
		// r�cup des sous-categ en cascade sous $a
		$categ_parent =  $categid_a ;
		for ($x=0 ; $x < sizeof($info_606_x[$a]) ; $x++) {
			$rqt_x = "SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_x[$a][$x])."' " ;
			$res_x = mysql_query($rqt_x,$dbh) ;
			if (mysql_num_rows($res_x)) {
				$categ_parent = mysql_result($res_x, 0, 0) ;
				} else {
					$rqt_ajout = "insert into categories set categ_parent='".$categ_parent."', categ_libelle='".addslashes($info_606_x[$a][$x])."', index_categorie=' ".strip_empty_words($info_606_x[$a][$x])." ' " ;
					$res_ajout = mysql_query($rqt_ajout, $dbh);
					$categ_parent = mysql_insert_id($dbh) ;
					}
			} // fin r�cup des $x en cascade sous l'id de la cat�gorie 606$a

		if ($categ_parent != $id_rech_theme) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', notcateg_categorie='".$categ_parent."' " ;
			$res_ajout = @mysql_query($rqt_ajout, $dbh);
			}
		}
				
	
	} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $msg, $dbh ;
	
	global $prix, $notice_id, $info_995, $typdoc_995, $tdoc_codage, $book_lender_id, 
		$section_995, $sdoc_codage, $book_statut_id, $locdoc_codage, $codstatdoc_995, $statisdoc_codage,
		$cote_mandatory, $book_location_id;
		
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
                      	
		// $expl['section']    = $info_995[$nb_expl]['q']; � chercher dans docs_section
		$data_doc=array();
		if (!$info_995[$nb_expl]['q']) 
			$info_995[$nb_expl]['q'] = "u";
		$data_doc['section_libelle'] = $section_995[$info_995[$nb_expl]['q']];
		$data_doc['sdoc_codage_import'] = $info_995[$nb_expl]['q'] ;
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
