<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_expor_import_erreur_pmb.inc.php,v 1.3 2013-10-29 11:25:03 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/thesaurus.class.php");
require_once("$class_path/noeuds.class.php");
require_once("$class_path/categories.class.php");
require_once($class_path."/serials.class.php");


if($action == "beforeupload"){
	$var_lib="<h2 align='center'>ATTENTION : Cette fonction d'import est destin�e  � l'import/export en cas de suppression par erreur de notices</h2> 
            	<div class='form-contenu'> 
            		<div class='row'> 
                        <h3>R�cup�rer et installer sur un autre PMB une sauvegarde de la base datant d'avant la suppression, puis faire l'un des exports (iso-2709) ci-dessous et proc�der � l'import avec cette fonction en cochant bien \"G�n�rer les liens entre notices ?\" du fichier obtenu pr�c�demment.<br/> Affecter un statut particulier aux notices import�es afin de les retrouver plus facilement.</h3>
						<br/>
                    </div> 
                    <div class='row'> 
                        <h3>Pour la suppression par erreur d'un p�riodique, de ses bulletins, ses articles et ses exemplaires : </h3>
                        <p>(Avec le d�doublonage � l'import cette m�thode est aussi valable pour la suppression d'un bulletin de p�riodique)</p>
                        <h3 style='margin-left:2em;'>    Mettre la notice du p�riodique dans un panier de notice et faire un export du panier avec les options suivantes : <br/></h3>
                        <h3 style='margin-left:4em;'>        - Conserver les informations des exemplaires dans la zone 995 (export des exemplaires)<br/></h3>
                        <h3 style='margin-left:4em;'>        - G�n�rer les liens (pour la reconstruction du bulletinage)<br/></h3>
                        <h3 style='margin-left:4em;'>        - Exporter les notices li�es : Tout cocher (pour avoir les notices de bulletin)<br/></h3>
                        <h3 style='margin-left:4em;'>        - Exporter les structures de p�riodique: Tout cocher (pour pouvoir reconstruire correctement les liens entre le p�riodique, ses bulletins et ses articles)<br/></h3>
                    	<br/>
                   </div>
                   	<div class='row'> 
                        <h3>Pour la suppression par erreur d'articles :</h3>
                        <h3 style='margin-left:2em;'>    Deux possibilit�s : mettre les articles dans un panier de notices ou mettre le/les bulletins avec les articles � reprendre dans un panier de bulletins puis faire un export du panier avec les options suivantes : <br/></h3>
                        <h3 style='margin-left:4em;'>    Si le bulletin et ses exemplaires ont �t� supprim�s : <br/></h3>
                        <h3 style='margin-left:6em;'>        - Conserver les informations des exemplaires dans la zone 995 (export des exemplaires)<br/></h3>
                        <h3 style='margin-left:4em;'>    Dans tous les cas : cocher que ce qui est cit� ci-dessous<br/></h3>
                        <h3 style='margin-left:6em;'>        - G�n�rer les liens (pour la reconstruction du bulletinage)<br/></h3>
                        <h3 style='margin-left:6em;'>        - Liens vers les bulletins pour les notices d'article<br/></h3>
                        <h3 style='margin-left:6em;'>        - Liens vers les p�riodiques pour les notices d'article<br/></h3>
                    	<br/>
                   </div>
                     <div class='row'> 
                        <h3>Pour la suppression par erreur d'une ou plusieurs notices :</h3>
                        <h3 style='margin-left:2em;'>    Mettre la/les notices supprim�e(s) dans un panier de notices et faire un export du panier avec les options suivantes : <br/></h3>
                        <h3 style='margin-left:4em;'>        - Conserver les informations des exemplaires dans la zone 995 (export des exemplaires)<br/></h3>
                        <h3 style='margin-left:4em;'>        - G�n�rer les liens (si vos notices �taient reli�es � d'autres notices et que ces autres notices ont aussi �t� supprim�es sinon cela risque de cr�er des doublons)<br/></h3>
                        <h3 style='margin-left:4em;'>        - Exporter les notices li�es : Tout cocher (pour avoir les notices li�es)<br/></h3>
                        <h3 style='margin-left:4em;'>        - Exporter les structures de p�riodique : Ne rien cocher<br/></h3>
                    </div>
				</div>";
	if($charset == "utf-8"){
		echo utf8_encode($var_lib);
	}else{
		echo $var_lib;
	}
}


function decoupe_date($date_nom_formate,$annee_seule=false){
	$date="";
	$tab=preg_split("/\D/",$date_nom_formate);
	
	switch(count($tab)){
		case 3 :
			if(strlen($tab[0]) == 4){
				$date=$tab[0]."-".$tab[1]."-".$tab[2];
			}elseif(strlen($tab[2]) == 4){
				$date=$tab[2]."-".$tab[1]."-".$tab[0];
			}elseif($tab[0] > 31){
				$date="19".$tab[0]."-".$tab[1]."-".$tab[2];
			}elseif($tab[2] > 31){
				$date="19".$tab[2]."-".$tab[1]."-".$tab[0];
			}
			break;
		case 2 :
			if(strlen($tab[0]) == 4){
				$date=$tab[0]."-".$tab[1]."-01";
			}elseif(strlen($tab[1]) == 4){
				$date=$tab[1]."-".$tab[0]."-01";
			}elseif($tab[0] > 31){
				$date="19".$tab[0]."-".$tab[1]."-01";
			}elseif($tab[1] > 31){
				$date="19".$tab[1]."-".$tab[0]."-01";
			}
			break;
		case 1 :
			if(strlen($tab[0]) == 8){
				$date=substr($tab[0],0,4)."-".substr($tab[0],4,2)."-".substr($tab[0],6,2);
			}elseif(strlen($tab[0]) == 6){
				$date=substr($tab[0],0,4)."-".substr($tab[0],4,2)."-01";
			}elseif(strlen($tab[0]) == 4){
				$date=substr($tab[0],0,4)."-01-01";
			}
	}
	
	if($annee_seule){
		return substr($date,0,4);
	}else{
		return $date;
	}
	
}

function renseigne_cp($nom,$valeur,$notice_id,$type="notices"){
	if(!trim($nom) || !trim($valeur) || !$notice_id){
		return false;
	}
	//on va chercher les informations sur le champs
	$rqt = "SELECT idchamp, type, datatype FROM ".$type."_custom WHERE name='" . addslashes(trim($nom)) . "'";
	$res = mysql_query($rqt);
	if (!mysql_num_rows($res))
		return false;
	
	$cp=mysql_fetch_object($res);
	
	//On enregistre la valeur au bon endroit
	switch ($cp->type) {
		case "list":
			//On est sur une liste
			switch ($cp->datatype) {
				case "integer":
					$requete="select ".$type."_custom_list_value from ".$type."_custom_lists where ".$type."_custom_list_lib='".addslashes(trim($valeur))."' and ".$type."_custom_champ='".$cp->idchamp."' ";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value2=mysql_result($resultat,0,0);
					} else {
						$requete="select max(".$type."_custom_list_value*1) from ".$type."_custom_lists where ".$type."_custom_champ='".$cp->idchamp."' ";
						$resultat=mysql_query($requete);
						$max=@mysql_result($resultat,0,0);
						$n=$max+1;
						$requete="insert into ".$type."_custom_lists (".$type."_custom_champ,".$type."_custom_list_value,".$type."_custom_list_lib) values('".$cp->idchamp."',$n,'".addslashes(trim($valeur))."')";
						if(!mysql_query($requete)) return false;
						$value2=$n;
					}
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_integer) values('".$cp->idchamp."','".$notice_id."','".$value2."')";
					if(!mysql_query($requete)) return false;
					break;
				default:
					$requete="select ".$type."_custom_list_value from ".$type."_custom_lists where ".$type."_custom_list_lib='".addslashes(trim($valeur))."' and ".$type."_custom_champ='".$cp->idchamp."' ";
					$resultat=mysql_query($requete);
					if (mysql_num_rows($resultat)) {
						$value2=mysql_result($resultat,0,0);
					} else {
						$requete="insert into ".$type."_custom_lists (".$type."_custom_champ,".$type."_custom_list_value,".$type."_custom_list_lib) values('".addslashes(trim($valeur))."',$n,'".addslashes(trim($valeur))."')";
						if(!mysql_query($requete)) return false;
						$value2=trim($valeur);
					}
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_".$cp->datatype.") values('".$cp->idchamp."','".$notice_id."','".$value2."')";
					if(!mysql_query($requete)) return false;
					break;
			}
			break;
		default:
			switch ($cp->datatype) {
				case "small_text":
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_small_text) values('".$cp->idchamp."','".$notice_id."','".addslashes(trim($valeur))."')";
					if(!mysql_query($requete)) return false;
					break;
				case "int":
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_integer) values('".$cp->idchamp."','".$notice_id."','".addslashes(trim($valeur))."')";
					if(!mysql_query($requete)) return false;
					break;
				case "text":
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_text) values('".$cp->idchamp."','".$notice_id."','".addslashes(trim($valeur))."')";
					if(!mysql_query($requete)) return false;
					break;
				case "date":
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_date) values('".$cp->idchamp."','".$notice_id."','".addslashes(decoupe_date(trim($valeur)))."')";
					if(!mysql_query($requete)) return false;
					break;
			}
			break;
	}
	return true;
}

// UPDATE `notices_custom` SET export=1
function recup_noticeunimarc_suite($notice) {
	global $info_100,$info_606_a,$info_606_9,$info_900,$info_999;
	$info_100=array();
	$info_606_a=array();
	$info_606_9=array();
	$info_900=array();
	$info_999=array();
	$record = new iso2709_record($notice, AUTO_UPDATE);
	
	$info_100=$record->get_subfield("100","a");
	$info_606_a=$record->get_subfield_array_array("606","a");
	$info_606_9=$record->get_subfield_array_array("606","9");
	$info_900=$record->get_subfield("900","a","l","n");
	$info_999=$record->get_subfield("999","a","l","n","f");

} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres BDP : rien de plus
	
function import_new_notice_suite() {
	global $id_unimarc,$info_100,$notice_id, $info_606_a, $info_606_9,$info_900;
	global $suffix,$isbn_OK,$from_file;
	
	if(trim($info_100[0])){
		$date=decoupe_date(substr($info_100[0], 0, 8));
		$requete="update notices set create_date = '".addslashes($date)."' where notice_id='".$notice_id."' ";
		mysql_query($requete);
		/*if(!mysql_query($requete)){
			echo "requete echou� : ".$requete."<br>";
		}*/
	}
	$incr_categ=0;
	for($i=0;$i<count($info_606_a);$i++){
		if(trim($info_606_a[$i][0])){
			//echo "ici : ".$info_606[$i]["a"]."<br>";
			$trouve=false;
			$id_noeud=0;
			foreach ( $info_606_9[$i] as $value ) {
       			if(preg_match("/^id:([0-9]+)$/",$value,$matches)){
       				$id_noeud=$matches[1];
       				break;
       			}
			}
			if($id_noeud){
				if(categories::exists($id_noeud,"fr_FR")){
					//echo "la : ".$info_606[$i]["a"]."<br>";
					$categ = new categories($id_noeud,"fr_FR");
					if($categ->libelle_categorie == $info_606_a[$i][0]){
						//echo "ou la : ".$info_606[$i]["a"]."<br>";
						// ajout de l'indexation � la notice dans la table notices_categories
						$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".$categ->num_noeud."', ordre_categorie='".$incr_categ."' " ;
						$res_ajout = @mysql_query($rqt_ajout);
						$incr_categ++;
						$trouve=true;
					}
				}
			}
			
			if(!$trouve){
				$mon_msg= "Cat�gorie non reprise car l'identifant n'existe pas dans PMB : ".$info_606_a[$i][0];
				mysql_query("insert into error_log (error_origin, error_text) values ('import_".addslashes(SESSid).".inc', '".addslashes($mon_msg)."') ") ;
			}
		}
	}
	
	for($i=0;$i<count($info_900);$i++){
		if(trim($info_900[$i]["a"])){
			if(!renseigne_cp($info_900[$i]["n"], $info_900[$i]["a"],$notice_id)){
				$mon_msg= "La valeur  : ".$info_900[$i]["a"]." n'a pas �t� reprise dans le champ personalis� : ".$info_900[$i]["n"]." car le champ n'existe pas";
				mysql_query("insert into error_log (error_origin, error_text) values ('import_".addslashes(SESSid).".inc', '".addslashes($mon_msg)."') ") ;
				/*echo "Erreur � l'enregistrement du champ perso<br>";
				echo "<pre>";
				print_r($info_900[$i]);
				echo "</pre>";*/
			}
		}
		
	}
	
} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $nb_expl_ignores,$bulletin_ex ;
	global $prix, $notice_id, $info_996,$info_999, $typdoc_995, $tdoc_codage, $book_lender_id, 
		$section_995, $sdoc_codage, $book_statut_id, $codstatdoc_995, $statisdoc_codage,
		$cote_mandatory, $book_location_id ;
	global $suffix;	
	// lu en 010$d de la notice
	$price = $prix[0];
	
	// la zone 995 est r�p�table
	for ($nb_expl = 0; $nb_expl < sizeof ($info_996); $nb_expl++) {
		/* RAZ expl */
		$expl = array();
		
		$data=array();
		/*if(!$info_996[$nb_expl]['a'])$info_996[$nb_expl]['a'] ="Ind�termin�";
		$data['lender_libelle']=$info_996[$nb_expl]['a'];
		$book_lender_id=lender::import($data);*/
		
		//Propri�taire
		if(trim($info_996[$nb_expl]['a'])){
			$requete="SELECT idlender FROM lenders WHERE lender_libelle LIKE '".addslashes($info_996[$nb_expl]['a'])."'";
			$res=mysql_query($requete);
			if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
				$local_book_lender_id=$id;
			}else{
				$local_book_lender_id=$book_lender_id;
			}
		}else{
			$local_book_lender_id=$book_lender_id;
		}
		
		/* pr�paration du tableau � passer � la m�thode */
		$cbarre = $info_996[$nb_expl]['f'];
		if(!$cbarre)$cbarre= "ind";
		$pb = 1 ;
		$num_login=1 ;
		$expl['cb']=$cbarre;
		while ($pb==1) {
			$q = "SELECT expl_cb FROM exemplaires WHERE expl_cb='".addslashes($expl['cb'])."' LIMIT 1 ";
			$r = mysql_query($q);
			$nb = mysql_num_rows($r);
			if ($nb) {
				$expl['cb'] =$cbarre."-".$num_login ;
				$num_login++;
			} else $pb = 0 ;
		}
		
		if($info_996[$nb_expl]['f'] != $expl['cb']){
			$mon_msg= "ERREUR : l'exemplaire avec le code barres : ".$info_996[$nb_expl]['f']." existe d�j� donc il ne sera pas cr��";
			mysql_query("insert into error_log (error_origin, error_text) values ('import_".addslashes(SESSid).".inc', '".addslashes($mon_msg)."') ") ;
			continue;
		}
		
		if ($bulletin_ex) {
			$expl['bulletin']=$bulletin_ex;
			$expl['notice']=0;
		} else {
			$expl['notice']     = $notice_id ;
			$expl['bulletin']=0;
		}
		
		$data_doc=array();
		$data_doc['tdoc_libelle'] = $info_996[$nb_expl]['e'];
		if (!$data_doc['tdoc_libelle']) $data_doc['tdoc_libelle'] = "Ind�termin�" ;
		
		$requete="SELECT idtyp_doc FROM docs_type WHERE tdoc_libelle LIKE '".addslashes($data_doc['tdoc_libelle'])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['typdoc'] = $id;
		}else{
			$data_doc['duree_pret'] = 0 ; /* valeur par d�faut */
			$data_doc['tdoc_codage_import'] = $data_doc['tdoc_libelle'] ;
			if ($tdoc_codage) $data_doc['tdoc_owner'] = $local_book_lender_id ;
				else $data_doc['tdoc_owner'] = 0 ;
			$expl['typdoc'] = docs_type::import($data_doc);
		}
		
		
		$expl['cote'] = $info_996[$nb_expl]['k'];            	

		
		if (!$info_996[$nb_expl]['x']) 
			$info_996[$nb_expl]['x'] = "Ind�termin�";
			
		$requete="SELECT idsection FROM docs_section WHERE section_libelle LIKE '".addslashes($info_996[$nb_expl]['x'])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['section'] = $id;
		}else{
			$data_doc=array();
			$data_doc['section_libelle'] = $info_996[$nb_expl]['x'];
			$data_doc['sdoc_codage_import'] = $info_996[$nb_expl]['x'] ;
			if ($sdoc_codage) $data_doc['sdoc_owner'] = $local_book_lender_id ;
				else $data_doc['sdoc_owner'] = 0 ;
			$expl['section'] = docs_section::import($data_doc);
		}
		
		
		
		if (!$info_996[$nb_expl]['1']) $info_996[$nb_expl]['1'] = "Ind�termin�";
		
		$requete="SELECT  idstatut FROM docs_statut WHERE statut_libelle LIKE '".addslashes($info_996[$nb_expl]['1'])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['statut'] = $id;
		}else{
			$data_doc=array();
			$data_doc['statut_libelle'] = $info_996[$nb_expl]['1'];
			$data_doc['pret_flag'] = 1 ; 
			$data_doc['statusdoc_codage_import'] = $info_996[$nb_expl]['1'] ;
			if ($sdoc_codage) $data_doc['statusdoc_owner'] = $local_book_lender_id ;
				else $data_doc['statusdoc_owner'] = 0 ;
			$expl['statut'] = docs_statut::import($data_doc);
		}
		
		$requete="SELECT idlocation FROM docs_location WHERE location_libelle LIKE '".addslashes($info_996[$nb_expl]['v'])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['location'] = $id;
		}else{
			$expl['location'] = $book_location_id;
		}		
		
		if (!$info_996[$nb_expl]['c']) $info_996[$nb_expl]['c'] = "Ind�termin�";
		
		$requete="SELECT idcode FROM docs_codestat WHERE codestat_libelle  LIKE '".addslashes($info_996[$nb_expl]['c'])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['codestat'] = $id;
		}else{
			$data_doc=array();
			$data_doc['codestat_libelle'] = $info_996[$nb_expl]['c'];
			$data_doc['statisdoc_codage_import'] = $info_996[$nb_expl]['c'] ;
			if ($statisdoc_codage) $data_doc['statisdoc_owner'] = $local_book_lender_id ;
				else $data_doc['statisdoc_owner'] = 0 ;
			$expl['codestat'] = docs_codestat::import($data_doc);
		}
		
		if($info_996[$nb_expl]['4']){
			$expl['creation']   =$info_996[$nb_expl]['4'];
		}
                      	
		$expl['note']       = $info_996[$nb_expl]['u'];
		$expl['comment']       = $info_996[$nb_expl]['z'];
		$expl['prix']       = $price;
		$expl['expl_owner'] = $local_book_lender_id ;
		$expl['cote_mandatory'] = $cote_mandatory ;
		
		$expl['date_depot'] = substr($info_996[$nb_expl]['m'],0,4)."-".substr($info_996[$nb_expl]['m'],4,2)."-".substr($info_996[$nb_expl]['m'],6,2) ;      
		$expl['date_retour'] = substr($info_996[$nb_expl]['n'],0,4)."-".substr($info_996[$nb_expl]['n'],4,2)."-".substr($info_996[$nb_expl]['n'],6,2) ;
		
		// quoi_faire
		$expl['quoi_faire'] = 2 ;
		
		$expl_id = exemplaire::import($expl);
		if ($expl_id == 0) {
			$nb_expl_ignores++;
		}else{
			//Champ perso d'exemplaire
			//echo "Passe ici<br>";
			foreach ( $info_999 as $key => $value ) {
       			if($value["f"] == $info_996[$nb_expl]['f']){
       				//Je suis bien sur un cp de cet exemplaire
       				if(!renseigne_cp($value["n"], $value["a"],$expl_id,"expl")){
						$mon_msg= "La valeur  : ".$value["a"]." n'a pas �t� reprise dans le champ personalis� : ".$value["n"]." car le champ n'existe pas";
						mysql_query("insert into error_log (error_origin, error_text) values ('import_".addslashes(SESSid).".inc', '".addslashes($mon_msg)."') ") ;
					}else{
						unset($info_999[$key]);
					}
       			}
			}
		}
        
		} // fin for
	} // fin traite_exemplaires	TRAITEMENT DES EXEMPLAIRES JUSQU'ICI

// fonction sp�cifique d'export de la zone 995
function export_traite_exemplaires ($ex=array()) {
	
	$subfields=array();
	
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
	$export996['z'] = $ex -> expl_comment ;

	$export996['m'] = substr($ex -> expl_date_depot, 0, 4).substr($ex -> expl_date_depot, 5, 2).substr($ex -> expl_date_depot, 8, 2) ;
	$export996['n'] = substr($ex -> expl_date_retour, 0, 4).substr($ex -> expl_date_retour, 5, 2).substr($ex -> expl_date_retour, 8, 2) ;

	$export996['a'] = $ex -> lender_libelle;
	$export996['b'] = $ex -> expl_owner;
	
	$export996['c'] = $ex -> codestat_libelle;
	$export996['d'] = $ex -> expl_codestat;

	$export996['v'] = $ex -> location_libelle;
	$export996['w'] = $ex -> ldoc_codage_import;

	$export996['x'] = $ex -> section_libelle;
	$export996['y'] = $ex -> sdoc_codage_import;

	$export996['e'] = $ex -> tdoc_libelle;
	$export996['r'] = $ex -> tdoc_codage_import;

	$export996['1'] = $ex -> statut_libelle;
	$export996['2'] = $ex -> statusdoc_codage_import;
	$export996['3'] = $ex -> pret_flag;
	
	$export996['4'] = substr($ex -> create_date, 0, 4)."-".substr($ex -> create_date, 5, 2)."-".substr($ex -> create_date, 8, 2);
	$export996['6'] = $ex -> expl_id;
	
	global $export_traitement_exemplaires ;
	$export996['0'] = $export_traitement_exemplaires ;
	
	return 	$subfields ;

	}	
