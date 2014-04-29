<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_bcdi.inc.php,v 1.10 2012-12-14 11:03:24 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// DEBUT param�trage propre � la base de donn�es d'importation :
require_once($class_path."/serials.class.php");

function recup_noticeunimarc_suite($notice) {
	global $info_464,$analytique	;
	global $info_900,$info_901,$info_902,$info_903,$info_904,$info_905,$info_906,$info_606_a;
	
	$analytique=array();
	$info_464="";
	$info_900="";
	$info_901="";
	$info_902="";
	$info_903="";
	$info_904="";
	$info_905="";
	$info_906="";
	
	$record = new iso2709_record($notice, AUTO_UPDATE); 
	for ($i=0;$i<count($record->inner_directory);$i++) {
		$cle=$record->inner_directory[$i]['label'];
		switch($cle) {
			case "464":
				//C'est un p�riodique donc un d�pouillement ou une notice objet
				$info_464=$record->get_subfield($cle,"t","v","p","d","z","e");
				break;
			default:
				break;
	
		} /* end of switch */
	
	} /* end of for */
	
	$info_606_a=$record->get_subfield_array_array("606","a");
	$info_900=$record->get_subfield_array_array("900","a");
	$info_901=$record->get_subfield_array_array("901","a");
	$info_902=$record->get_subfield_array_array("902","a");
	$info_903=$record->get_subfield("903","a");
	$info_904=$record->get_subfield("904","a");
	$info_905=$record->get_subfield("905","a");
	$info_906=$record->get_subfield_array_array("906","a");
	
} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres � la bretagne
	
function import_new_notice_suite() {
	global $dbh ;
	global $notice_id ;
	
	global $info_464 ;
	global $info_606_a;
	global $info_900,$info_901,$info_902,$info_903,$info_904,$info_905,$info_906;
	
	global $pmb_keyword_sep;
	
	global $bulletin_ex;
	
	//Cas des p�riodiques
	if (is_array($info_464)) {
		$requete="select * from notices where notice_id=$notice_id";
		$resultat=mysql_query($requete);
		$r=mysql_fetch_object($resultat);
		//Notice chapeau existe-t-elle ?
			$requete="select notice_id from notices where tit1='".addslashes($info_464[0]['t'])."' and niveau_hierar='1' and niveau_biblio='s'";
			$resultat=mysql_query($requete);
			if (@mysql_num_rows($resultat)) {
				//Si oui, r�cup�ration id
				$chapeau_id=mysql_result($resultat,0,0);	
				//Bulletin existe-t-il ?
				$requete="select bulletin_id from bulletins where bulletin_numero='".addslashes($info_464[0]['v'])."' and  mention_date='".addslashes($info_464[0]['d'])."' and bulletin_notice=$chapeau_id";	
				$resultat=mysql_query($requete);
				if (@mysql_num_rows($resultat)) {
					//Si oui, r�cup�ration id bulletin
					$bulletin_id=mysql_result($resultat,0,0);	
				} else {
					//Si non, cr�ation bulltin
					$info=array();
					$bulletin=new bulletinage("",$chapeau_id);
					$info['bul_titre']=addslashes("Bulletin N�".$info_464[0]['v']);
					$info['bul_no']=addslashes($info_464[0]['v']);
					$info['bul_date']=addslashes($info_464[0]['d']);
					if (!$info_464[0]['e']) {
						if ($info_904[0]) {
							$info['date_date']=$info_904[0];
						}
					} else {
						$info['date_date']=$info_464[0]['e'];
					}
					$bulletin_id=$bulletin->update($info);
				}
			} else {
				//Si non, cr�ation notice chapeau et bulletin
				$chapeau=new serial();
				$info=array();
				$info['tit1']=addslashes($info_464[0]['t']);
				$info['niveau_biblio']='s';
				$info['niveau_hierar']='1';
				$info['typdoc']=$r->typdoc;
				
				$chapeau->update($info);
				$chapeau_id=$chapeau->serial_id;
				
				$bulletin=new bulletinage("",$chapeau_id);
				$info=array();
				$info['bul_titre']=addslashes("Bulletin N�".$info_464[0]['v']);
				$info['bul_no']=addslashes($info_464[0]['v']);
				$info['bul_date']=addslashes($info_464[0]['d']);
				if (!$info_464[0]['e']) {
					if ($info_904[0]) {
						$info['date_date']=$info_904[0];
					}
				} else {
					$info['date_date']=$info_464[0]['e'];
				}
				$bulletin_id=$bulletin->update($info);
			}
			//Notice objet ?
			if ($info_464[0]['z']=='objet') {
				//Supression de la notice
				$requete="delete from notices where notice_id=$notice_id";
				mysql_query($requete);
				$bulletin_ex=$bulletin_id;
			} else {
				//Passage de la notice en article
				$requete="update notices set niveau_biblio='a', niveau_hierar='2', year='".addslashes($info_464[0]['d'])."', npages='".addslashes($info_464[0]['p'])."', date_parution='".$info['date_date']."' where notice_id=$notice_id";
				mysql_query($requete);
				$requete="insert into analysis (analysis_bulletin,analysis_notice) values($bulletin_id,$notice_id)";
				mysql_query($requete);
				$bulletin_ex=$bulletin_id;
			}
	} else $bulletin_ex=0;
	
	//Traitement du th�saurus
	$unknown_desc=array();
	$ordre_categ = 0;
	for ($i=0; $i<count($info_606_a); $i++) {
		for ($j=0; $j<count($info_606_a[$i]); $j++) {
			$descripteur=trim($info_606_a[$i][$j]);
			//Recherche du terme
			$requete="SELECT id_noeud,num_renvoi_voir from noeuds JOIN categories ON (noeuds.id_noeud = categories.num_noeud) where categories.libelle_categorie = '".addslashes($descripteur)."' ";
			$resultat=mysql_query($requete);
			if (mysql_num_rows($resultat) == 1) {
				$categ_id=mysql_result($resultat,0,0);
				if(mysql_result($resultat,0,1)){
					$categ_id=mysql_result($resultat,0,1);
				}
				$requete="INSERT IGNORE INTO notices_categories (notcateg_notice,num_noeud,ordre_categorie) VALUES($notice_id,$categ_id,$ordre_categ)";
				mysql_query($requete);
				$ordre_categ++;
			} else {
				$unknown_desc[]=$descripteur;
			}
		}
	}
	
	if (count($unknown_desc)) {
		$mots_cles=implode($pmb_keyword_sep,$unknown_desc);
		$requete="UPDATE notices SET index_l=IF(index_l != '',CONCAT(index_l,'".$pmb_keyword_sep."','".addslashes($mots_cles)."'),'".addslashes($mots_cles)."'), index_matieres=IF(index_matieres != '',CONCAT(index_matieres,' ','".addslashes(strip_empty_words($mots_cles))."'),'".addslashes(strip_empty_words($mots_cles))."') WHERE notice_id='".$notice_id."'";
		mysql_query($requete);
	}
		
	//Th�me
	if (count($info_900)) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=1";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,1) == "list") && (mysql_result($res,0,2) == "integer")){
			$requete="select max(notices_custom_list_value*1) from notices_custom_lists where notices_custom_champ=1";
			$resultat=mysql_query($requete);
			$max=@mysql_result($resultat,0,0);
			$n=$max+1;
			for ($i=0; $i<count($info_900); $i++) {
				for ($j=0; $j<count($info_900[$i]); $j++) {
					$requete="select notices_custom_list_value from notices_custom_lists where notices_custom_list_lib='".addslashes($info_900[$i][$j])."' and notices_custom_champ=1";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value=mysql_result($resultat,0,0);
					} else {
						$requete="insert into notices_custom_lists (notices_custom_champ,notices_custom_list_value,notices_custom_list_lib) values(1,$n,'".addslashes($info_900[$i][$j])."')";
						mysql_query($requete);
						$value=$n;
						$n++;
					}
					$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(1,$notice_id,$value)";
					mysql_query($requete);
				}
			}
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=1 ou il n\'est pas de type liste entier : le 900 n\'est donc pas repris (Th�me)') ") ;
		}
		
	}
	
	//Genres
	if (count($info_901)) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=2";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,1) == "list") && (mysql_result($res,0,2) == "integer")){
			$requete="select max(notices_custom_list_value*1) from notices_custom_lists where notices_custom_champ=2";
			$resultat=mysql_query($requete);
			$max=@mysql_result($resultat,0,0);
			$n=$max+1;
			for ($i=0; $i<count($info_901); $i++) {
				for ($j=0; $j<count($info_901[$i]); $j++) {
					$requete="select notices_custom_list_value from notices_custom_lists where notices_custom_list_lib='".addslashes($info_901[$i][$j])."' and notices_custom_champ=2";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value=mysql_result($resultat,0,0);
					} else {
						$requete="insert into notices_custom_lists (notices_custom_champ,notices_custom_list_value,notices_custom_list_lib) values(2,$n,'".addslashes($info_901[$i][$j])."')";
						mysql_query($requete);
						$value=$n;
						$n++;
					}
					$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(2,$notice_id,$value)";
					mysql_query($requete);
				}
			}
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=2 ou il n\'est pas de type liste entier : le 901 n\'est donc pas repris (Genres)') ") ;
		}
	}
	
	//Discipline
	if (count($info_902)) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=3";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,1) == "list") && (mysql_result($res,0,2) == "integer")){
			$requete="select max(notices_custom_list_value*1) from notices_custom_lists where notices_custom_champ=3";
			$resultat=mysql_query($requete);
			$max=@mysql_result($resultat,0,0);
			$n=$max+1;
			for ($i=0; $i<count($info_902); $i++) {
				for ($j=0; $j<count($info_902[$i]); $j++) {
					$requete="select notices_custom_list_value from notices_custom_lists where notices_custom_list_lib='".addslashes($info_902[$i][$j])."' and notices_custom_champ=3";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value=mysql_result($resultat,0,0);
					} else {
						$requete="insert into notices_custom_lists (notices_custom_champ,notices_custom_list_value,notices_custom_list_lib) values(3,$n,'".addslashes($info_902[$i][$j])."')";
						mysql_query($requete);
						$value=$n;
						$n++;
					}
					$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(3,$notice_id,$value)";
					mysql_query($requete);
				}
			}
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=3 ou il n\'est pas de type liste entier : le 902 n\'est donc pas repris (Discipline)') ") ;
		}
	}
	
	//Type de nature
	if ($info_905[0]) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=6";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,1) == "list") && (mysql_result($res,0,2) == "integer")){
			$requete="select max(notices_custom_list_value*1) from notices_custom_lists where notices_custom_champ=6";
			$resultat=mysql_query($requete);
			$max=@mysql_result($resultat,0,0);
			$n=$max+1;
			$requete="select notices_custom_list_value from notices_custom_lists where notices_custom_list_lib='".addslashes($info_905[0])."' and notices_custom_champ=6";
			$resultat=mysql_query($requete);
			if (mysql_num_rows($resultat)) {
				$value=mysql_result($resultat,0,0);
			} else {
				$requete="insert into notices_custom_lists (notices_custom_champ,notices_custom_list_value,notices_custom_list_lib) values(6,$n,'".addslashes($info_905[0])."')";
				mysql_query($requete);
				$value=$n;
				$n++;
			}
			$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(6,$notice_id,$value)";
			mysql_query($requete);
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=6 ou il n\'est pas de type liste entier : le 905 n\'est donc pas repris (Type de nature)') ") ;
		}
		
	}
	
	//Niveau
	if (count($info_906)) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=7";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,1) == "list") && (mysql_result($res,0,2) == "integer")){
			for ($i=0; $i<count($info_906); $i++) {
				for ($j=0; $j<count($info_906[$i]); $j++) {
					$requete="select max(notices_custom_list_value*1) from notices_custom_lists where notices_custom_champ=7";
					$resultat=mysql_query($requete);
					$max=@mysql_result($resultat,0,0);
					$n=$max+1;
					$requete="select notices_custom_list_value from notices_custom_lists where notices_custom_list_lib='".addslashes($info_906[$i][$j])."' and notices_custom_champ=7";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value=mysql_result($resultat,0,0);
					} else {
						$requete="insert into notices_custom_lists (notices_custom_champ,notices_custom_list_value,notices_custom_list_lib) values(7,$n,'".addslashes($info_906[$i][$j])."')";
						mysql_query($requete);
						$value=$n;
						$n++;
					}
					$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(7,$notice_id,$value)";
					mysql_query($requete);
				}
			}
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=7 ou il n\'est pas de type liste entier : le 906 n\'est donc pas repris (Niveau)') ") ;
		}
	}
	
	//Ann�e de p�remption
	if ($info_903[0]) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=4";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,2) == "integer")){
			$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_integer) values(4,$notice_id,'".addslashes($info_903[0])."')";
			mysql_query($requete);
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=4 ou il n\'est pas de type entier : le 903 n\'est donc pas repris (Ann�e de p�remption)') ") ;
		}
	}
	
	//Date de saisie
	if ($info_904[0]) {
		$requete="SELECT name,type,datatype FROM notices_custom WHERE idchamp=5";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && (mysql_result($res,0,2) == "date")){
			$requete="insert into notices_custom_values (notices_custom_champ,notices_custom_origine,notices_custom_date) values(5,$notice_id,'".$info_904[0]."')";
			mysql_query($requete);
		}else{
			mysql_query("insert into error_log (error_origin, error_text) values ('import_expl_".addslashes(SESSid).".inc', 'Il n\'y a pas de CP de notice avec l\'identifiant=5 ou il n\'est pas de type date : le 904 n\'est donc pas repris (Date de saisie)') ") ;
		}
	}
	
} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $msg, $dbh ;
	
	global $prix, $notice_id, $info_995, $typdoc_995, $tdoc_codage, $book_lender_id, 
		$section_995, $sdoc_codage, $book_statut_id, $locdoc_codage, $codstatdoc_995, $statisdoc_codage,
		$cote_mandatory,$info_464 ;
	
	global $bulletin_ex;
	
	// lu en 010$d de la notice
	$price = $prix[0];
	
	// la zone 995 est r�p�table
	for ($nb_expl = 0; $nb_expl < sizeof($info_995); $nb_expl++) {
		/* RAZ expl */
		$expl = array();
		
		/* pr�paration du tableau � passer � la m�thode */
		$expl['cb'] 	    = $info_995[$nb_expl]['f'];
		if (($bulletin_ex)&&(is_array($info_464))) {
			$expl['bulletin']=$bulletin_ex;
			$expl['notice']=0;
		} else {
			$expl['notice']     = $notice_id ;
			$expl['bulletin']=0;
		}
		// $expl['typdoc']     = $info_995[$nb_expl]['r']; � chercher dans docs_typdoc
		$data_doc=array();
		//$data_doc['tdoc_libelle'] = $info_995[$nb_expl]['r']." -Type doc import� (".$book_lender_id.")";
		//$data_doc['tdoc_libelle'] = $typdoc_995[$info_995[$nb_expl]['r']];
		//if (!$data_doc['tdoc_libelle']) $data_doc['tdoc_libelle'] = "\$r non conforme -".$info_995[$nb_expl]['r']."-" ;
		$data_doc['duree_pret'] = 0 ; /* valeur par d�faut */
		$data_doc['tdoc_codage_import'] = $info_995[$nb_expl]['r'] ;
		$data_doc['tdoc_libelle']=$info_995[$nb_expl]['r'] ;
		if ($tdoc_codage) $data_doc['tdoc_owner'] = $book_lender_id ;
			else $data_doc['tdoc_owner'] = 0 ;
		$expl['typdoc'] = docs_type::import($data_doc);
		
		$expl['cote'] = $info_995[$nb_expl]['k'];
         
        if (!trim($expl['cote'])) $expl['cote']="ARCHIVES";
                      	
		// $expl['section']    = $info_995[$nb_expl]['q']; � chercher dans docs_section
		$data_doc=array();
		if (!$info_995[$nb_expl]['t']) 
			$info_995[$nb_expl]['t'] = "inconnu";
		$data_doc['section_libelle'] = $info_995[$nb_expl]['t'] ;
		$data_doc['sdoc_codage_import'] = $info_995[$nb_expl]['t'] ;
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
		
		// $expl['location']   = $info_995[$nb_expl]['']; � fixer par combo_box
		// fig� dans le code ici pour l'instant :
		//$info_995[$nb_expl]['localisation']="Bib princip"; /* biblio principale */
		$data_doc=array();
		$data_doc['location_libelle'] = "inconnu";
		if ($info_995[$nb_expl]['a']) {
			$data_doc['location_libelle'] = $info_995[$nb_expl]['a'];
			$data_doc['locdoc_codage_import'] = $info_995[$nb_expl]['a'];
		} else {
			$data_doc['locdoc_codage_import']="cdi";
		}
		if ($locdoc_codage) $data_doc['locdoc_owner'] = $book_lender_id ;
			else $data_doc['locdoc_owner'] = 0 ;
		$expl['location'] = docs_location::import($data_doc);
		
		// $expl['codestat']   = $info_995[$nb_expl]['q']; 'q' utilis�, �ventuellement � fixer par combo_box
		$data_doc=array();
		//$data_doc['codestat_libelle'] = $info_995[$nb_expl]['q']." -Pub vis� import� (".$book_lender_id.")";
		if (!$info_995[$nb_expl]['q']) 
			$info_995[$nb_expl]['q'] = "inconnu";
		$data_doc['codestat_libelle'] = $info_995[$nb_expl]['q'] ;
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