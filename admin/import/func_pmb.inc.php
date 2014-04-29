<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_pmb.inc.php,v 1.4 2013-11-28 09:12:24 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/thesaurus.class.php");
require_once("$class_path/noeuds.class.php");
require_once("$class_path/categories.class.php");
require_once($class_path."/serials.class.php");


if($action == "beforeupload"){
	$var_lib="<h2 align='center'>Fonction destin�e � l'�change de notices entre PMB et principalement en cas de suppression par erreur de notices</h2> 
            	<div class='form-contenu'> 
            		<div class='row'>
            			<h3>Pr�requis en cas de suppression :</h3>
                        <h3 style='margin-left:2em;'>- R�cup�rer et installer sur un autre PMB une sauvegarde de la base datant d'avant la suppression et dans ce PMB mettre les notices � r�cup�rer dans un panier.</h3>
                    </div><br/>
                    <div class='row'> 
                        <h3>Proc�dure pour l'�change de notices entre PMB :</h3> 
                        <h3 style='margin-left:2em;'>- Export des notices:</h3>
                        <h3 style='margin-left:4em;'>- R�aliser un panier avec toutes les notices que vous souhaitez �changer (Utiliser les transferts entre paniers en cas de besoin)</h3>
                        <h3 style='margin-left:6em;'>- Si vous souhaitez reprendre un p�riodique avec tous ses articles, il vous faut mettre toutes les notices d'article dans le panier</h3>
                        <h3 style='margin-left:6em;'>- Si vous souhaitez reprendre un p�riodique avec des notices de bulletins, il vous faut mettre toutes les notices de bulletin dans le panier</h3>
                        <h3 style='margin-left:6em;'>- Si vous avez des liens entre notices vous devez mettre dans le panier les notices li�es</h3>
                        <h3 style='margin-left:4em;'>- Avant de faire l'export, assurez vous d'avoir choch� l'option \"Exportable\" pour tous les champs personnalis�s que vous souhaitez avoir et qu'ils soient d�finis de la m�me fa�on entre les deux PMB</h3>
                        <h3 style='margin-left:4em;'>- R�aliser un export de type \"UNIMARC ISO2709\" du panier en cochant les options suivantes (laisser les autres d�coch�es) : </h3>
                        <h3 style='margin-left:6em;'>- Conserver les informations des exemplaires dans la zone 995 (Si vous souhaitez reprendre les informations d'exemplaire)</h3>
                        <h3 style='margin-left:6em;'>- G�n�rer les liens (Si vous avez des liens entre notices, des bulletins, des p�riodiques ou des articles dans votre panier)</h3>
                        <h3 style='margin-left:6em;'>- Liens vers les notices m�res (Si vous avez des liens entre notices)</h3>
                        <h3 style='margin-left:6em;'>- Liens vers les notices filles (Si vous avez des liens entre notices)</h3>
                        <h3 style='margin-left:6em;'>- Liens vers les bulletins pour les notices d'article (Si vous avez des notices d'article)</h3>
                        <h3 style='margin-left:6em;'>- Liens vers les p�riodiques pour les notices d'article (Si vous avez des notices d'article)</h3>
                        <h3 style='margin-left:6em;'>- G�n�rer le bulletinage pour les notices de p�riodique (Si vous avez des notices de p�riodique et que vous souhaitez reprendre leur bulletinage)</h3> 
                        <h3 style='margin-left:2em;'>- Import des notices :</h3>
                        <!--<h3 style='margin-left:4em;'>- Si vous voyez ceci c'est que vous avez choisit la fonction func_pmb.inc.php</h3>-->
                        <h3 style='margin-left:4em;'>- Dans les options ci-dessous faites attention � celles-ci :</h3>
                        <h3 style='margin-left:6em;'>- G�n�rer les liens entre notices ? : Choisir \"Oui\"</h3>
                        <h3 style='margin-left:6em;'>- Statut des notices import�es : A utiliser pour retrouver facilement les notices que vous venez d'importer dans votre fonds</h3>
                        <h3 style='margin-left:6em;'>- Les informations de propri�taire, statut et localisation sont prises en compte si elles ne sont pas renseign�es dans les exemplaires import�s</h3>
                        <h3 style='margin-left:6em;'>- Les informations de codage sont prises en compte</h3>
                    	<br/>
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

function renseigne_cp($val,$notice_id,$type="notices"){
	$nom=$val["n"];
	$valeur=$val["a"];
	if(!trim($nom) || !trim($valeur) || !$notice_id){
		return false;
	}
	//on va chercher les informations sur le champs
	$rqt = "SELECT idchamp, type, datatype FROM ".$type."_custom WHERE name='" . addslashes(trim($nom)) . "'";
	$res = mysql_query($rqt);
	if (!mysql_num_rows($res))
		return false;
	
	$cp=mysql_fetch_object($res);
	
	if($cp->type != $val["t"]){
		return false;
	}
	
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
						$requete="insert into ".$type."_custom_lists (".$type."_custom_champ,".$type."_custom_list_value,".$type."_custom_list_lib) values('".$cp->idchamp."','".addslashes(trim($valeur))."','".addslashes($valeur)."')";
						if(!mysql_query($requete)) return false;
						$value2=trim($valeur);
					}
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_".$cp->datatype.") values('".$cp->idchamp."','".$notice_id."','".$value2."')";
					if(!mysql_query($requete)) return false;
					break;
			}
			break;
		case "url":
			$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_".$cp->datatype.") values('".$cp->idchamp."','".$notice_id."','".addslashes(trim($val["c"]))."')";
			if(!mysql_query($requete)) return false;
			break;
		case "resolve":
			$mes_pp= new parametres_perso($type);
			if($mes_pp->get_formatted_output(array($val["c"]),$cp->idchamp) == $val["b"]){
				$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_".$cp->datatype.") values('".$cp->idchamp."','".$notice_id."','".addslashes($val["c"])."')";
				if(!mysql_query($requete)) return false;
			}else{
				return false;
			}
			break;
		case "query_list":
		case "query_auth":
			$mes_pp= new parametres_perso($type);
			if($mes_pp->get_formatted_output(array($val["c"]),$cp->idchamp) == $valeur){
				$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_".$cp->datatype.") values('".$cp->idchamp."','".$notice_id."','".addslashes($val["c"])."')";
				if(!mysql_query($requete)) return false;
			}else{
				return false;
			}
			break;
		default:
			switch ($cp->datatype) {
				case "small_text":
					$requete="insert into ".$type."_custom_values (".$type."_custom_champ,".$type."_custom_origine,".$type."_custom_small_text) values('".$cp->idchamp."','".$notice_id."','".addslashes(trim($valeur))."')";
					if(!mysql_query($requete)) return false;
					break;
				case "int":
				case "integer":
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
	global $info_100,$info_606_a,$info_606_9,$info_900,$info_999,$info_950,$info_951,$info_996_9;
	$info_100=array();
	$info_606_a=array();
	$info_606_9=array();
	$info_900=array();
	$info_950=array();
	$info_951=array();
	$info_999=array();
	$info_996_9=array();
	$record = new iso2709_record($notice, AUTO_UPDATE);
	
	$info_100=$record->get_subfield("100","a");
	$info_606_a=$record->get_subfield_array_array("606","a");
	$info_606_9=$record->get_subfield_array_array("606","9");
	$info_900=$record->get_subfield("900","a","b","c","l","n","t");
	$info_950=$record->get_subfield("950","a","b","c","d","e","f","g","h","i","j","k");
	$info_951=$record->get_subfield("951","a","b","c","l","n","f","t");
	$info_996_9=$record->get_subfield_array_array("996","9");
	$info_999=$record->get_subfield("999","a","b","c","l","n","f","t");

} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres BDP : rien de plus
	
function import_new_notice_suite() {
	global $id_unimarc,$info_100,$notice_id, $info_606_a, $info_606_9,$info_900,$info_950,$info_951;
	global $suffix,$isbn_OK,$from_file,$thesaurus_defaut;
	global $bibliographic_level, $hierarchic_level;
	if(trim($info_100[0])){
		$date=decoupe_date(substr($info_100[0], 0, 8));
		$requete="update notices set create_date = '".addslashes($date)."' where notice_id='".$notice_id."' ";
		mysql_query($requete);
		/*if(!mysql_query($requete)){
			echo "requete echou� : ".$requete."<br>";
		}*/
	}
	$incr_categ=0;
	if(count($info_606_a)){
		$thes = new thesaurus($thesaurus_defaut);
		for($i=0;$i<count($info_606_a);$i++){
			if($libelle=trim($info_606_a[$i][0])){
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
						if($categ->libelle_categorie == $libelle){
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
					//Je regarde si il y a une autre cat�gorie avec ce libell� dans les th�saurus
					$q="SELECT id_noeud from noeuds JOIN categories ON noeuds.id_noeud = categories.num_noeud WHERE categories.libelle_categorie = '".addslashes($libelle)."'";
					$res=mysql_query($q);
					if($res){
						if(mysql_num_rows($res) == 1){
							$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".mysql_result($res,0,0)."', ordre_categorie='".$incr_categ."' " ;
							$res_ajout = @mysql_query($rqt_ajout);
							$incr_categ++;
						}elseif(mysql_num_rows($res) > 1){
							$mon_msg= "Cat�gorie non reprise car elle est pr�sente plusieurs fois dans les th�saurus de PMB: ".$libelle;
							affiche_mes_erreurs($mon_msg);
						}else{
							$n=new noeuds();
							$n->num_parent=$thes->num_noeud_racine;
							$n->num_thesaurus=$thesaurus_defaut;
							$n->save();
							$resultat=$id_n=$n->id_noeud;
							$c=new categories($id_n, $thes->langue_defaut);
							$c->libelle_categorie=$libelle;
							$c->save();
							$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".$id_n."', ordre_categorie='".$incr_categ."' " ;
							$res_ajout = @mysql_query($rqt_ajout);
							$incr_categ++;
							$mon_msg= "Cat�gorie cr��e � la racine du th�saurus par d�faut: ".$libelle;
							affiche_mes_erreurs($mon_msg);
						}
					}
				}
			}
		}
	}
	
	if(($bibliographic_level== "s") && ($hierarchic_level == "1") && count($info_950)){
		foreach ( $info_950 as $value) {
       		//Emplacement
   			if(!$value["c"]){
   				$mon_msg= "Etat de collection non import� car pas d'emplacement pour le p�riodique: ".$id_unimarc;
				affiche_mes_erreurs($mon_msg);
				continue;
   			}
			$requete="SELECT archempla_id FROM arch_emplacement WHERE archempla_libelle='".addslashes($value["c"])."'";
			$res=mysql_query($requete);
			if(mysql_num_rows($res)){
				$id_empl=mysql_result($res,0,0);
			}else{
				$requete="INSERT INTO arch_emplacement(archempla_libelle) VALUES('".addslashes($value["c"])."')";
				if(!mysql_query($requete)){
					$mon_msg= "Etat de collection non import� probl�me avec la requ�te: ".$requete;
					affiche_mes_erreurs($mon_msg);
					continue;
				}else{
					$id_empl=mysql_insert_id();
				}
			}
   			
   			//Statut
   			if(!$value["h"] && !$value["k"]){
   				$mon_msg= "Etat de collection non import� car pas de statut pour le p�riodique: ".$id_unimarc;
				affiche_mes_erreurs($mon_msg);
				continue;
   			}
			$requete="SELECT archstatut_id FROM arch_statut WHERE archstatut_gestion_libelle='".addslashes($value["k"])."' AND  archstatut_opac_libelle='".addslashes($value["h"])."'";
			$res=mysql_query($requete);
			if(mysql_num_rows($res)){
				$id_statut=mysql_result($res,0,0);
			}else{
				$requete="INSERT INTO arch_statut(archstatut_gestion_libelle,archstatut_opac_libelle) VALUES('".addslashes($value["k"])."','".addslashes($value["h"])."')";
				if(!mysql_query($requete)){
					$mon_msg= "Etat de collection non import� probl�me avec la requ�te: ".$requete;
					affiche_mes_erreurs($mon_msg);
					continue;
				}else{
					$id_statut=mysql_insert_id();
				}
			}
			
			//Support
   			if(!$value["d"]){
   				$mon_msg= "Etat de collection non import� car pas de support pour le p�riodique: ".$id_unimarc;
				affiche_mes_erreurs($mon_msg);
				continue;
   			}
			$requete="SELECT archtype_id FROM arch_type WHERE archtype_libelle='".addslashes($value["d"])."'";
			$res=mysql_query($requete);
			if(mysql_num_rows($res)){
				$id_support=mysql_result($res,0,0);
			}else{
				$requete="INSERT INTO arch_type(archtype_libelle) VALUES('".addslashes($value["d"])."')";
				if(!mysql_query($requete)){
					$mon_msg= "Etat de collection non import� probl�me avec la requ�te: ".$requete;
					affiche_mes_erreurs($mon_msg);
					continue;
				}else{
					$id_support=mysql_insert_id();
				}
			}
			
			//Localisation
   			if(!$value["a"]){
   				$mon_msg= "Etat de collection non import� car pas de localisation pour le p�riodique: ".$id_unimarc;
				affiche_mes_erreurs($mon_msg);
				continue;
   			}
			$requete="SELECT idlocation FROM docs_location WHERE location_libelle='".addslashes($value["a"])."'";
			$res=mysql_query($requete);
			if(mysql_num_rows($res)){
				$id_loc=mysql_result($res,0,0);
			}else{
				$requete="INSERT INTO docs_location(location_libelle) VALUES('".addslashes($value["a"])."')";
				if(!mysql_query($requete)){
					$mon_msg= "Etat de collection non import� probl�me avec la requ�te: ".$requete;
					affiche_mes_erreurs($mon_msg);
					continue;
				}else{
					$id_loc=mysql_insert_id();
				}
			}
			
			$stat=$value["b"];
			$cote=$value["f"];
			$archive=$value["g"];
			$origine=$value["e"];
			$note=$value["j"];
			$lacune=$value["i"];  			
   			
   			$requete="insert into collections_state(id_serial,location_id,state_collections,collstate_emplacement,collstate_type,collstate_origine,collstate_cote,collstate_archive,collstate_statut,collstate_lacune,collstate_note) values (" .
				"'".$notice_id."','".$id_loc."','".addslashes($stat)."','".$id_empl."','".$id_support."','".addslashes($origine)."','".addslashes($cote)."','".addslashes($archive)."','".$id_statut."','".addslashes($lacune)."','".addslashes($note)."'" .
				")";
			if(!mysql_query($requete)){
				$mon_msg= "Etat de collection non import� probl�me avec la requete: ".$requete;
				affiche_mes_erreurs($mon_msg);
				continue;
			}else{
				$id_coll_stat=mysql_insert_id();
				if(count($info_951)){
					foreach ( $info_951 as $cle => $val ) {
		       			if($val["f"] == $id_coll_stat){
		       				//Je suis bien sur un cp de cet exemplaire
		       				if(!renseigne_cp($val,$id_coll_stat,"collstate")){
								$mon_msg= "La valeur  : ".$value["a"]." n'a pas �t� reprise dans le champ personnalis� : ".$value["n"]." car le champ n'existe pas";
								affiche_mes_erreurs($mon_msg);
							}else{
								unset($info_951[$cle]);
							}
		       			}
					}
				}
			}
		}
	}
	
	if(count($info_900)){
		for($i=0;$i<count($info_900);$i++){
			if(trim($info_900[$i]["a"])){
				if(!renseigne_cp($info_900[$i],$notice_id)){
					$mon_msg= "La valeur  : ".$info_900[$i]["a"]." n'a pas �t� reprise dans le champ personnalis� : ".$info_900[$i]["n"]." car le champ n'existe pas ou n'est pas d�fini de la m�me fa�on";
					affiche_mes_erreurs($mon_msg);
				}
			}
		}
	}
	
	
} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $nb_expl_ignores,$bulletin_ex ;
	global $prix, $notice_id, $info_996_9,$info_999, $typdoc_995, $tdoc_codage, $book_lender_id, 
		$section_995, $sdoc_codage, $book_statut_id, $codstatdoc_995, $statisdoc_codage,
		$cote_mandatory, $book_location_id ;
	global $suffix;	
	// lu en 010$d de la notice
	$price = $prix[0];
	
	$exemplaires=array();
	for($i=0;$i<count($info_996_9);$i++){
		foreach ( $info_996_9[$i] as $value ) {
   			if(preg_match("/^(.*?):(.*)$/",$value,$matches)){
   				$exemplaires[$i][$matches[1]]=$matches[2];
   			}
		}
	}
	
	// la zone 995 est r�p�table
	for ($nb_expl = 0; $nb_expl < sizeof ($exemplaires); $nb_expl++) {
		/* RAZ expl */
		$expl = array();
		
		$data=array();
		/*if(!$info_996[$nb_expl]['a'])$info_996[$nb_expl]['a'] ="Ind�termin�";
		$data['lender_libelle']=$info_996[$nb_expl]['a'];
		$book_lender_id=lender::import($data);*/
		
		//Propri�taire
		if($tmp=trim($exemplaires[$nb_expl]["lender_libelle"])){
			$requete="SELECT idlender FROM lenders WHERE lender_libelle LIKE '".addslashes($tmp)."'";
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
		$cbarre = $exemplaires[$nb_expl]["expl_cb"];
		if(!$cbarre){
			$mon_msg= "ERREUR : J'ai un exemplaire sans code barres il ne sera donc pas cr��";
			affiche_mes_erreurs($mon_msg);
			continue;
		}
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
		
		if($cbarre != $expl['cb']){
			$mon_msg= "ERREUR : l'exemplaire avec le code barres : ".$cbarre." existe d�j� donc il ne sera pas cr��";
			affiche_mes_erreurs($mon_msg);
			continue;
		}
		
		if ($bulletin_ex) {
			$expl['bulletin']=$bulletin_ex;
			$expl['notice']=0;
		} else {
			$expl['notice']     = $notice_id ;
			$expl['bulletin']=0;
		}
		
		//Support exemplaire
		$data_doc=array();
		$data_doc['tdoc_libelle'] = $exemplaires[$nb_expl]["tdoc_libelle"];
		//if (!$data_doc['tdoc_libelle']) $data_doc['tdoc_libelle'] = "Ind�termin�" ;
		
		$requete="SELECT idtyp_doc FROM docs_type WHERE tdoc_libelle LIKE '".addslashes($data_doc['tdoc_libelle'])."'";
		$res=mysql_query($requete);
		if(($data_doc['tdoc_libelle']) && mysql_num_rows($res) && ($id=mysql_result($res,0,0))){
			$expl['typdoc'] = $id;
		}else{
			$data_doc['duree_pret'] = 0 ; /* valeur par d�faut */
			$data_doc['tdoc_codage_import'] = $exemplaires[$nb_expl]["tdoc_codage_import"];
			if ($tdoc_codage) $data_doc['tdoc_owner'] = $local_book_lender_id ;
				else $data_doc['tdoc_owner'] = 0 ;
			$expl['typdoc'] = docs_type::import($data_doc);
		}
		
		
		$expl['cote'] = $exemplaires[$nb_expl]["expl_cote"];	

		//Section
		$data_doc=array();
		$data_doc['section_libelle'] = $exemplaires[$nb_expl]["section_libelle"];
			
		$requete="SELECT idsection FROM docs_section WHERE section_libelle LIKE '".addslashes($data_doc['section_libelle'])."'";
		$res=mysql_query($requete);
		if(($data_doc['section_libelle']) && mysql_num_rows($res) && ($id=mysql_result($res,0,0)) ){
			$expl['section'] = $id;
		}else{
			$data_doc['sdoc_codage_import'] = $exemplaires[$nb_expl]["sdoc_codage_import"];
			if ($sdoc_codage) $data_doc['sdoc_owner'] = $local_book_lender_id ;
				else $data_doc['sdoc_owner'] = 0 ;
			$expl['section'] = docs_section::import($data_doc);
		}
		
		//Statut
		$data_doc=array();
		$data_doc['statut_libelle'] = $exemplaires[$nb_expl]["statut_libelle"];
		
		$requete="SELECT  idstatut FROM docs_statut WHERE statut_libelle LIKE '".addslashes($data_doc['statut_libelle'])."'";
		$res=mysql_query($requete);
		if(($data_doc['statut_libelle']) && mysql_num_rows($res) && ($id=mysql_result($res,0,0)) ){
			$expl['statut'] = $id;
		}elseif($exemplaires[$nb_expl]["statusdoc_codage_import"]){
			$data_doc['pret_flag'] = 1 ; 
			$data_doc['statusdoc_codage_import'] = $exemplaires[$nb_expl]["statusdoc_codage_import"];
			if ($sdoc_codage) $data_doc['statusdoc_owner'] = $local_book_lender_id ;
				else $data_doc['statusdoc_owner'] = 0 ;
			$expl['statut'] = docs_statut::import($data_doc);
		}else{
			$expl['statut'] = $book_statut_id;
		}
		
		//Localisation
		$requete="SELECT idlocation FROM docs_location WHERE location_libelle LIKE '".addslashes($exemplaires[$nb_expl]["location_libelle"])."'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res) && $id=mysql_result($res,0,0)){
			$expl['location'] = $id;
		}else{
			$expl['location'] = $book_location_id;
		}		
		
		//Code stat
		$data_doc=array();
		$data_doc['codestat_libelle'] = $exemplaires[$nb_expl]["codestat_libelle"];
		
		$requete="SELECT idcode FROM docs_codestat WHERE codestat_libelle  LIKE '".addslashes($data_doc['codestat_libelle'])."'";
		$res=mysql_query($requete);
		if(($data_doc['codestat_libelle']) && mysql_num_rows($res) && ($id=mysql_result($res,0,0))){
			$expl['codestat'] = $id;
		}else{
			$data_doc['statisdoc_codage_import'] = $exemplaires[$nb_expl]["statisdoc_codage_import"];
			if ($statisdoc_codage) $data_doc['statisdoc_owner'] = $local_book_lender_id ;
				else $data_doc['statisdoc_owner'] = 0 ;
			$expl['codestat'] = docs_codestat::import($data_doc);
		}
		

        $expl['creation']   = $exemplaires[$nb_expl]["create_date"];
		$expl['note']       = $exemplaires[$nb_expl]["expl_note"];
		$expl['comment']       = $exemplaires[$nb_expl]["expl_comment"];
		$expl['prix']       = $exemplaires[$nb_expl]["expl_prix"];
		$expl['expl_owner'] = $local_book_lender_id ;
		$expl['cote_mandatory'] = $cote_mandatory ;
		
		$expl['date_depot'] = $exemplaires[$nb_expl]["date_depot"];
		$expl['date_retour'] = $exemplaires[$nb_expl]["date_retour"];
		
		// quoi_faire
		$expl['quoi_faire'] = 2 ;
		
		$expl_id = exemplaire::import($expl);
		if ($expl_id == 0) {
			$nb_expl_ignores++;
		}else{
			//Champ perso d'exemplaire
			//echo "Passe ici<br>";
			foreach ( $info_999 as $key => $value ) {
       			if($value["f"] == $cbarre){
       				//Je suis bien sur un cp de cet exemplaire
       				if(!renseigne_cp($value,$expl_id,"expl")){
						$mon_msg= "La valeur  : ".$value["a"]." n'a pas �t� reprise dans le champ personnalis� : ".$value["n"]." car le champ n'existe pas";
						affiche_mes_erreurs($mon_msg);
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
	global $export_traitement_exemplaires ;
	$export996['0'] = $export_traitement_exemplaires ;
	
	return 	$subfields ;

}

function affiche_mes_erreurs($mon_msg,$affiche=true,$log=true){
	global $charset;
	if($charset == "utf-8"){
		$mon_msg= utf8_encode($mon_msg);
	}
	if($affiche){
		echo $mon_msg."<br>";
	}
	if($log){
		mysql_query("insert into error_log (error_origin, error_text) values ('import_".addslashes(SESSid).".inc', '".addslashes($mon_msg)."') ") ;
	}
}	
