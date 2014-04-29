<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: print_thesaurus.php,v 1.16 2013-08-13 15:28:33 mbertin Exp $

$base_path = ".";
$base_auth = "AUTORITES_AUTH";
$base_title = $msg[print_thes_title];
$base_nobody=1;
$base_noheader=1;


require($base_path."/includes/init.inc.php");
@set_time_limit(0);
require_once("$class_path/thesaurus.class.php");
require_once("$class_path/noeuds.class.php");
require_once("$class_path/categories.class.php");
// constantes
			$color[1]="black";
			$color[2]="#c9e9ff"; // bleu
			$color[3]="#c6ffc5"; // vert
			$color[4]="#ffedc5"; // saumon
			$color[5]="#fcffc5"; // jaune
			$color[6]="#d7d8ff"; // violet
		
			$fontsize[1]=" font-size:1.2em; ";
			$fontsize[2]=" font-size:1.0em; ";
			$fontsize[3]=" font-size:0.9em; "; 
			$fontsize[4]=" font-size:0.8em; "; 
			$fontsize[5]=" font-size:0.8em; "; 
			$fontsize[6]=" font-size:0.8em; "; 
			$fontsize[7]=" font-size:0.8em; "; 
			$fontsize[8]=" font-size:0.8em; "; 
			$fontsize[9]=" font-size:0.8em; "; 

			$paddingmargin[0]=" padding-bottom: 10px; ";
			$paddingmargin[1]=" padding-bottom: 10px; ";
			$paddingmargin[2]=" padding-bottom: 8px; ";
			$paddingmargin[3]=" padding-bottom: 6px; ";
			$paddingmargin[4]=" ";
			$paddingmargin[5]=" ";
			$paddingmargin[6]=" ";
			$paddingmargin[7]=" ";
			$paddingmargin[8]=" ";
			$paddingmargin[9]=" ";


if ($action!="print") {
	print $std_header;
	print "<h3>".$msg[print_thes_title]."</h3>\n";
	print "<form name='print_options' action='print_thesaurus.php?action=print' method='post'>
		<b>".$msh[print_thes_options]."</b>
		<blockquote>".$msg[print_thes_list_type]."
			<select name='typeimpression'>";
	if ($id_noeud_origine){
		print "\n<option value='arbo' selected>".$msg[print_thes_arbo]."</option>
				<option value='alph' disabled>".$msg[print_thes_alph]."</option>
				<option value='rota' disabled>".$msg[print_thes_rota]."</option>";
		$val_enable="document.getElementById(\"options_xmlskos\").style.visibility=\"hidden\";";
	}else{
		print "\n<option value='arbo' selected>".$msg[print_thes_arbo]."</option>
				<option value='alph' >".$msg[print_thes_alph]."</option>
				<option value='rota' >".$msg[print_thes_rota]."</option>";
		$val_enable="document.print_options.typeimpression.options[1].disabled = 0;print_options.typeimpression.options[2].disabled=0; document.getElementById(\"options_xmlskos\").style.visibility=\"hidden\";";
	}
	
	print "\n</select>
		</blockquote>
		<blockquote>
			<input type='checkbox' name='aff_note_application' CHECKED value='1' />&nbsp;".$msg[print_thes_na]."<br />
			<input type='checkbox' name='aff_commentaire' CHECKED value='1' />&nbsp;".$msg[print_thes_comment]."<br />
			<input type='checkbox' name='aff_voir' CHECKED value='1'/>&nbsp;".$msg[print_thes_voir]."<br />
			<input type='checkbox' name='aff_voir_aussi' CHECKED value='1'/>&nbsp;".$msg[print_thes_ta]."<br />
			<input type='checkbox' name='aff_tg' CHECKED value='1'/>&nbsp;".$msg[print_thes_tg]."<br />
			<input type='checkbox' name='aff_ts' CHECKED value='1'/>&nbsp;".$msg[print_thes_ts]."
		</blockquote>
		<b>".$msg["print_output_title"]."</b>
		<blockquote>
			<input type='radio' name='output' value='printer' checked onClick='".$val_enable."'/>&nbsp;".$msg["print_output_printer"]."<br />
			<input type='radio' name='output' value='tt' onClick='".$val_enable."'/>&nbsp;".$msg["print_output_writer"]."<br />
			<input type='radio' name='output' value='xml' onClick='document.print_options.typeimpression.selectedIndex= 0;document.print_options.typeimpression.options[1].disabled = 1;print_options.typeimpression.options[2].disabled = 1; document.getElementById(\"options_xmlskos\").style.visibility=\"hidden\";' />&nbsp;".$msg["print_output_xml"]."
		";
		if(!$id_noeud_origine){
			print "<br /><input type='radio' name='output' value='xmlskos' onClick='document.print_options.typeimpression.selectedIndex= 0;document.print_options.typeimpression.options[1].disabled = 1;print_options.typeimpression.options[2].disabled = 1;document.getElementById(\"options_xmlskos\").style.visibility=\"visible\";' />&nbsp;".$msg["print_output_skos"] ;
			print "<div id='options_xmlskos' name='options_xmlskos' style='visibility:hidden;'>".$msg["print_output_skos_uri"]."&nbsp;<input type='text' name='uri_thes_skos' value='".$pmb_opac_url."thesaurus/".$aff_num_thesaurus."'/><br/>";
			print "<input type='checkbox' name='do_polyhierarchie' CHECKED value='1' />&nbsp;".$msg[print_output_skos_polyhierarchie];
			print "</div";
		}
		print "
		</blockquote>
		<input type='hidden' name='aff_langue' value='fr_FR'>
		<input type='hidden' name='id_noeud_origine' value='$id_noeud_origine'>
		<input type='hidden' name='aff_num_thesaurus' value='";
	if ($aff_num_thesaurus>0) print $aff_num_thesaurus;
	else die( "> Error with # of thesaurus");
	print "'><center><input type='submit' value='".$msg["print_print"]."' class='bouton'/>&nbsp;<input type='button' value='".$msg["print_cancel"]."' class='bouton' onClick='self.close();'/></center>";
	print "</body></html>";
	}

$rqlang = "select langue_defaut from thesaurus where id_thesaurus=".$aff_num_thesaurus ;
$reslang = mysql_query($rqlang) or die("<br />Query 'langue_defaut' failed ".mysql_error()."<br />".$rqlang);
$objlang = mysql_fetch_object($reslang);
if ($objlang->langue_defaut) $aff_langue = $objlang->langue_defaut;
else $aff_langue ="fr_FR";

if ($action=="print") {
	if(substr($output,0,3) =="xml"){
		header("Content-Type: text/xml; charset=utf-8");
		if($output == "xmlskos"){
			header("Content-Disposition: attachement; filename=thesaurus.skos.xml");
		}else{
			header("Content-Disposition: attachement; filename=thesaurus.xml");
		}
		
		
		$thes = new thesaurus($aff_num_thesaurus);
		if($thes && $thes->num_noeud_racine){
			if($id_noeud_origine){
				$id_noeud_debut=$id_noeud_origine;
			}else{
				$id_noeud_debut=$thes->num_noeud_racine;
			}
			
			//Je peux commencer le th�saurus
			$dom = new DOMDocument('1.0', 'UTF-8');
			//$dom->preserveWhiteSpace = false;
		    $dom->formatOutput = true;
		    
		    if($output == "xml"){
		    	$racine=creer_noeud_xml($dom,$dom,"THESAURII");
			    $noeud=creer_noeud_xml($dom,$racine,"DATE_EX",date('Y-m-d\TH:i:s'));
				$noeudthes=creer_noeud_xml($dom,$racine,"THES");
				creer_noeud_xml($dom,$noeudthes,"LIB_THES",$thes->libelle_thesaurus);
				
				$res=categories::listChilds($id_noeud_debut, $aff_langue,1, "libelle_categorie");
			    if($res && mysql_num_rows($res)){
			    	while ($categ=mysql_fetch_object($res)) {
						if(trim($categ->libelle_categorie)){
							creer_categ_xml($dom,$noeudthes,0,$categ->num_noeud,$categ->libelle_categorie,$categ->note_application,$categ->comment_public,$categ->num_parent);
						}
					}
			    }
		    }elseif($output == "xmlskos"){
		    	if(!$uri_thes_skos){
		    		$uri_thes_skos=$pmb_opac_url."thesaurus/".$aff_num_thesaurus;
		    	}
		    	$uri_thes_skos.="/";
		    	//uri_thes_skos, do_polyhierarchie
		    	$racine=creer_noeud_xml($dom,$dom,"rdf:RDF","",array("xmlns:rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#", "xmlns:skos" => "http://www.w3.org/2004/02/skos/core#"));
		    	$conceptScheme=creer_noeud_xml($dom,$racine,"skos:ConceptScheme","",array("rdf:about"=>$uri_thes_skos."#Thesaurus"));
		    	$prefLabel=creer_noeud_xml($dom,$conceptScheme,"skos:prefLabel",$thes->libelle_thesaurus,array("xml:lang"=>substr($thes->langue_defaut,0,2)));
		    	
		    	//hasTopConcept
		    	$requete="SELECT id_noeud FROM noeuds WHERE num_parent='".$id_noeud_debut."' AND  num_renvoi_voir='0' AND autorite != 'ORPHELINS' AND num_thesaurus='".$aff_num_thesaurus."'";
				$res_hasTopConcept=mysql_query($requete);
				if($res_hasTopConcept && mysql_num_rows($res_hasTopConcept)){
					while ($hasTopConcept=mysql_fetch_object($res_hasTopConcept)) {
						creer_noeud_xml($dom,$conceptScheme,"skos:hasTopConcept","",array("rdf:resource"=>$uri_thes_skos.$hasTopConcept->id_noeud));
					}
				}
		    	cree_export_skos($dom,$racine,$thes->num_noeud_racine,$thes->num_noeud_orphelins);
		    }
			//$dom->save('./temp/thesaurus_rra.xml');
			echo $dom->saveXML();			
		}else{
			die("Load thesaurus failed");
		}
	}else{
		if ($output=="tt") {
			header("Content-Type: application/word");
			header("Content-Disposition: attachement; filename=thesaurus.doc");
		}
		print "<html><body style='font-family : Arial, Helvetica, Verdana, sans-serif;'>";
		print "<h2>".affiche_text($msg["print_thes_titre_".$typeimpression])."</h2>";
		switch($typeimpression) {
			case "arbo":
				$res.="<td width=10% bgcolor='".$color[$niveau-9]."'> </td>";
				
				if ($id_noeud_origine) {
					// un noeud �tait fourni pour n'imprimer que cette branche
					$id_noeud_top = $id_noeud_origine ;
				} else {
					$rqt_id_noeud_top = "select id_noeud from noeuds where autorite='TOP' and num_thesaurus=".$aff_num_thesaurus ;
					$result_rqt_id_noeud_top = mysql_query($rqt_id_noeud_top) or die("Query 'TOP' failed");
					$obj_id_noeud_top = mysql_fetch_object($result_rqt_id_noeud_top);
					$id_noeud_top = $obj_id_noeud_top->id_noeud;
				}
				
				// premier parcours pour calculer la profondeur du th�saurus : $profondeurmax
				$niveau=0;
				$resultat="";
				$profondeurmax=0;
				enfants($id_noeud_top, $niveau, $resultat, $profondeurmax, false);
				/// deuxi�me parcours, cette fois-ci on imprime
				$niveau=0;
				$resultat="";
				echo "<table width=100% cellspacing=0 cellpadding=3>";
				enfants($id_noeud_top, $niveau, $resultat, $profondeurmax, true);
				echo "</table>" ;
				break;
			case "alph":
				$rqt = "select id_noeud from noeuds n, categories c where c.num_thesaurus=$aff_num_thesaurus and n.num_thesaurus=$aff_num_thesaurus and id_noeud=num_noeud and langue='$aff_langue' and autorite!='TOP' and autorite!='ORPHELINS' and autorite!='NONCLASSES' order by libelle_categorie ";
				$result = mysql_query($rqt) or die("Query alpha failed");
				while ($obj_id_noeud = mysql_fetch_object($result)){
					echo infos_categorie($obj_id_noeud->id_noeud);
				}
				break;
			case "rota":
				$mots=array();
				if (file_exists("$include_path/marc_tables/$aff_langue/empty_words_thesaurus")) {
					$mots_vides_thesaurus=true;
					include("$include_path/marc_tables/$aff_langue/empty_words_thesaurus");
				} else $mots_vides_thesaurus=false;  
				$rqt = "select id_noeud, libelle_categorie, index_categorie from noeuds n, categories c where c.num_thesaurus=$aff_num_thesaurus and n.num_thesaurus=$aff_num_thesaurus and id_noeud=num_noeud and langue='$aff_langue' and autorite!='TOP' and autorite!='ORPHELINS' and autorite!='NONCLASSES' order by libelle_categorie ";
				$result = mysql_query($rqt) or die("Query rota failed");
				while ($obj = mysql_fetch_object($result)) {
					// r�cup�ration de l'index du libell�, nettoyage
					$icat=$obj->index_categorie ;
					// si mots vides suppl�mentaires
					if ($mots_vides_thesaurus) {
						// suppression des mots vides
						if (is_array($empty_word_thesaurus)) {
							foreach($empty_word_thesaurus as $dummykey=>$word) {
								$word = convert_diacrit($word);
								$icat = pmb_preg_replace("/^${word}$|^${word}\s|\s${word}\s|\s${word}\$/i", ' ', $icat);
							}
						}
					}
					$icat = trim($icat);
					// echo "<br />".$obj->id_noeud." - ".$icat ;
					$icat = pmb_preg_replace('/\s+/', ' ', $icat);
	
					// l'index est propre, on va pouvoir exploser sur espace.
					$mot=array();
					// index non vide (des fois que le m�nage pr�c�dent l'aie vid� compl�tement)
					if ($icat) {
						$mot = explode(' ',$icat);
						for ($imot=0;$imot<count($mot);$imot++) {
							if ($mot[$imot]) {
								$mots[$mot[$imot]][]=$obj->id_noeud ;
							}
						}
					}
				}
				// on a un super tableau de mots
				ksort($mots, SORT_STRING);
				echo "<table>";
				foreach ($mots as $mot=>$idiz) {
					// on parcourt tous les mots trouv�s
					$rqt="select libelle_categorie, num_noeud from categories where num_noeud in(".implode(",",$idiz).") and langue='".$aff_langue."' order by index_categorie";
					$ressql = mysql_query($rqt) or die ($rqt."<br /><br />".mysql_error());
					while ($data=mysql_fetch_object($ressql)) {
						// on parcourt toutes les cat�gories utilisant ce mot pour chercher la position d'utilisation du mot
						$catnette = " ".str_replace(" - ","   ",strtolower(strip_empty_chars_thesaurus($data->libelle_categorie)))." ";
						$catnette = str_replace(" -","  ",$catnette);
						$catnette = str_replace("- ","  ",$catnette);
						$posdeb=strpos($catnette," ".$mot." ");
						$posfin=$posdeb+strlen($mot);
						// echo "<br /><br />deb $posdeb - fin: $posfin mot: $mot LIB: ".$data->libelle_categorie ;
						echo "
							<tr>
								<td align=right valign=top>".affiche_text(substr($data->libelle_categorie,0,$posdeb))."</td>
								<td align=left valign=top><b>".affiche_text(substr($data->libelle_categorie,$posdeb,$posfin-$posdeb))."</b>".affiche_text(substr($data->libelle_categorie,$posfin));
						echo infos_categorie($data->num_noeud, false, true)."</td></tr>";
					}
				}
				// print_r($mots);
				echo "</table>";
				break;
		}
		// pied de page
		print "</body></html>";
	}
	
}

mysql_close($dbh);

function infos_noeud($idnoeud, $niveau, $profondeurmax) {

	global $dbh, $aff_langue;
	global $aff_note_application, $aff_commentaire, $aff_voir, $aff_voir_aussi, $aff_tg, $aff_ts;
	global $color, $fontsize, $paddingmargin ;
	global $id_noeud_origine;
	
	// r�cup�ration info du noeud
	$rqt = "select num_noeud, libelle_categorie, num_parent, note_application, comment_public, case when langue='$aff_langue' then '' else langue end as trad, langue from categories,noeuds where num_noeud = id_noeud and num_noeud='$idnoeud' order by trad ";
	$ressql = mysql_query($rqt) or die ($rqt."<br /><br />".mysql_error());
	while ($data=mysql_fetch_object($ressql)) {
		$res.= "\n<tr>";
		$niv=$niveau-1;
		switch($niv) {
			case 10:
				$res.="<td width=10% bgcolor='".$color[$niveau-9]."'> </td>";
			case 9:
				$res.="<td width=10% bgcolor='".$color[$niveau-8]."'> </td>";
			case 8:
				$res.="<td width=10% bgcolor='".$color[$niveau-7]."'> </td>";
			case 7:
				$res.="<td width=10% bgcolor='".$color[$niveau-6]."'> </td>";
			case 6:
				$res.="<td width=10% bgcolor='".$color[$niveau-5]."'> </td>";
			case 5:
				$res.="<td width=10% bgcolor='".$color[$niveau-4]."'> </td>";
			case 4:
				$res.="<td width=10% bgcolor='".$color[$niveau-3]."'> </td>";
			case 3:
				$res.="<td width=10% bgcolor='".$color[$niveau-2]."'> </td>";
			case 2:
				$res.="<td width=10% bgcolor='".$color[$niveau-1]."'> </td>";
			case 1:
				$res.="<td width=10% bgcolor='".$color[$niveau]."'> </td>";
		}

		$printingBranche = false;
		// afin d'avoir les bons colspan sur la branche en cas d'impression d'une branche
		if ($id_noeud_origine==$idnoeud){
			$niveau=$niveau+1 ;
			$printingBranche = true;
		} 

		if (($data->note_application || $data->comment_public) && ($aff_note_application || $aff_commentaire)) {
			$style="style='border-top: 1px dotted gray;border-bottom: 1px dotted gray; ";
			$largeur="40%";
		} else {
			$style="style='";
			$largeur="70%";
		}
		$style.=" ".$fontsize[$niveau]." ".$paddingmargin[$niveau]." '";
		if ($data->trad) $res.="<td colspan='".($profondeurmax-($niveau-1))."' width=$largeur valign=top $style><font color='blue'>".affiche_text($data->trad)."</font> ".affiche_text($data->libelle_categorie)."";
		else $res.="<td colspan='".($profondeurmax-($niveau-1))."' width=$largeur valign=top $style>".affiche_text($data->libelle_categorie);

		//TERME G�N�RAL DANS LE CAS DE L'IMPRESSION D'UNE BRANCHE
		if ($printingBranche){
			$rqttg = "select libelle_categorie from categories where num_noeud = '".$data->num_parent."'";
			$restg = mysql_query($rqttg) or die ($rqttg."<br /><br />".mysql_error());
			if (mysql_num_rows($restg)) {
				$datatg=mysql_fetch_object($restg);
				$res.= "<br /><font color='blue'>TG ".affiche_text($datatg->libelle_categorie)."</font>";
			}		
		} 
			
		if ($aff_voir_aussi) {
			$rqtva = "select libelle_categorie from categories, voir_aussi where num_noeud_orig=$idnoeud and num_noeud=num_noeud_dest and categories.langue='".$data->langue."' and voir_aussi.langue='".$data->langue."' order by libelle_categorie " ;
			$resva = mysql_query($rqtva) or die ($rqtva."<br /><br />".mysql_error());
			if (mysql_num_rows($resva)) {
				$res.= "\n<font color='green'>";
				while ($datava=mysql_fetch_object($resva)) $res.= "<br />TA ".affiche_text($datava->libelle_categorie);
				$res.= "</font>";
			}
			
		}
		if ($aff_voir) {
			$rqtva = "select libelle_categorie from categories, noeuds where num_renvoi_voir=$idnoeud and num_noeud=id_noeud and categories.langue='".$data->langue."' order by libelle_categorie " ;
			$resva = mysql_query($rqtva) or die ($rqtva."<br /><br />".mysql_error());
			if (mysql_num_rows($resva)) {
				$res.= "\n<font size=-1>";
				while ($datava=mysql_fetch_object($resva)) $res.= "<br />EP <i>".affiche_text($datava->libelle_categorie)."</i>";
				$res.= "</font>";
			}
		}
		$res.="</td>";
		if ($aff_note_application && $data->note_application) $res.="<td width=30% valign=top $style><font color=#ff706d>".affiche_text($data->note_application)."</font></td>";
		if ($aff_commentaire && $data->comment_public) $res.="<td width=30% valign=top $style><font color=black>".affiche_text($data->comment_public)."</font></td>";
		$res.="\n</tr>";
	}
	return $res ;
}

function infos_categorie($idnoeud, $printcategnoeud=true, $forcer_em=false) {

	global $dbh, $aff_langue;
	global $aff_note_application, $aff_commentaire, $aff_voir, $aff_voir_aussi, $aff_tg, $aff_ts;
	
	// r�cup�ration info du noeud
	$rqt = "select num_noeud, num_parent, libelle_categorie, note_application, comment_public, case when langue='$aff_langue' then '' else langue end as trad, langue from categories join noeuds on num_noeud=id_noeud where num_noeud='$idnoeud' order by trad ";
	$ressql = mysql_query($rqt) or die ($rqt."<br /><br />".mysql_error());
	while ($data=mysql_fetch_object($ressql)) {

		if ($data->trad) $res.="<br /><font color=blue>".affiche_text($data->trad)."</font> ".affiche_text($data->libelle_categorie)."";
		elseif ($printcategnoeud) $res.="<br /><br /><b>".affiche_text($data->libelle_categorie)."</b>";

		// EP et EM
		if ($aff_voir) {
			$rqtva = "select libelle_categorie from categories, noeuds where num_renvoi_voir=$idnoeud and num_noeud=id_noeud and categories.langue='".$data->langue."' order by libelle_categorie " ;
			$resva = mysql_query($rqtva) or die ($rqtva."<br /><br />".mysql_error());
			if (mysql_num_rows($resva)) {
				$res.= "\n<font size=-1>";
				while ($datava=mysql_fetch_object($resva)) $res.= "<br />EP <i>".affiche_text($datava->libelle_categorie)."</i>";
				$res.= "</font>";
			}
		}
		if ($aff_voir || $forcer_em) {
			$rqtva = "select libelle_categorie from categories, noeuds where id_noeud=$idnoeud and num_noeud=num_renvoi_voir and categories.langue='".$data->langue."' order by libelle_categorie " ;
			$resva = mysql_query($rqtva) or die ($rqtva."<br /><br />".mysql_error());
			if (mysql_num_rows($resva)) {
				$res.= "\n<font size=-1>";
				while ($datava=mysql_fetch_object($resva)) $res.= "<br />EM <i>".affiche_text($datava->libelle_categorie)."</i>";
				$res.= "</font>";
			}
		}

		// TG
		if ($aff_tg) {
			$rqttg = "select libelle_categorie from categories join noeuds on num_noeud=id_noeud where num_noeud='$data->num_parent' and libelle_categorie not like '~%' and categories.langue='".$data->langue."' " ;
			$restg = mysql_query($rqttg) or die ($rqttg."<br /><br />".mysql_error());
			if (mysql_num_rows($restg)) {
					$res.= "\n<font color=black>";
					while ($datatg=mysql_fetch_object($restg)) $res.= "<br />TG ".affiche_text($datatg->libelle_categorie);
					$res.= "</font>";
				}
		}
		
		// TS
		if ($aff_ts) {
			$rqtts = "select libelle_categorie from categories join noeuds on num_noeud=id_noeud where num_parent='$data->num_noeud' and libelle_categorie not like '~%' and categories.langue='".$data->langue."' " ;
			$rests = mysql_query($rqtts) or die ($rqttg."<br /><br />".mysql_error());
			if (mysql_num_rows($rests)) {
					$res.= "\n<font color=black>";
					while ($datats=mysql_fetch_object($rests)) $res.= "<br />TS ".affiche_text($datats->libelle_categorie);
					$res.= "</font>";
				}
		}		
		// TA
		if ($aff_voir_aussi) {
			$rqtva = "select libelle_categorie from categories, voir_aussi where num_noeud_orig=$idnoeud and num_noeud=num_noeud_dest and categories.langue='".$data->langue."' and voir_aussi.langue='".$data->langue."' order by libelle_categorie " ;
			$resva = mysql_query($rqtva) or die ($rqtva."<br /><br />".mysql_error());
			if (mysql_num_rows($resva)) {
				$res.= "\n<font color=green>";
				while ($datava=mysql_fetch_object($resva)) $res.= "<br />TA ".affiche_text($datava->libelle_categorie);
				$res.= "</font>";
			}
			
		}
		
		if ($aff_note_application && $data->note_application) $res.="<br /><font color=#ff706d>NA ".affiche_text($data->note_application)."</font>";
		if ($aff_commentaire && $data->comment_public) $res.="<br /><font color=black>PU ".affiche_text($data->comment_public)."</font>";
	}
	return $res ;
}

function enfants($id, $niveau, &$resultat, &$profondeurmax, $imprimer=false) {

	global $dbh, $aff_langue;

	if ($imprimer) {
		$resultat=infos_noeud($id, $niveau, $profondeurmax) ;
		echo $resultat;
		flush();
	} elseif ($niveau>$profondeurmax) $profondeurmax=$niveau; 
	
	// chercher les enfants
	$rqt = "select id_noeud from noeuds, categories where num_parent='$id' and id_noeud=num_noeud and langue='$aff_langue' and autorite!='TOP' and autorite!='ORPHELINS' and autorite!='NONCLASSES' order by libelle_categorie ";
	$res = mysql_query($rqt) ;
	if (mysql_num_rows($res)) {
		$niveau++;
		while ($data=mysql_fetch_object($res)) {
			enfants($data->id_noeud, $niveau, $resultat, $profondeurmax, $imprimer);
		}
	}
}

function strip_empty_chars_thesaurus($string) {
	// traitement des diacritiques
	$string = convert_diacrit($string);

	// Mis en commentaire : qu'en est-il des caract�res non latins ???
	// SUPPRIME DU COMMENTAIRE : ER : 12/05/2004 : �a fait tout merder...
	// RECH_14 : Attention : ici suppression des �ventuels "
	//          les " ne sont plus supprim�s 
	$string = stripslashes($string) ;
	$string = pmb_alphabetic('^a-z0-9\s', ' ',pmb_strtolower($string));
	
	// espaces en d�but et fin
	$string = pmb_preg_replace('/^\s+|\s+$/', '', $string);
	
	return $string;
}


function affiche_text($string){
	global $charset;
	return htmlentities($string,ENT_QUOTES,$charset);
}


//Fonctions utilis�es pour l'export du th�saurus en xml

function creer_categ_xml($dom,$parent,$niveau, $num_noeud,$libelle_categorie,$note_application,$comment_public,$num_parent){
	global $dbh, $aff_langue;
	global $aff_note_application, $aff_commentaire, $aff_voir, $aff_voir_aussi, $aff_tg, $aff_ts;
	
	
	$noeud_categ=creer_noeud_xml($dom,$parent,"DE");

	//ID
	creer_noeud_xml($dom,$noeud_categ,"ID",$num_noeud);

    //Libell�
    creer_noeud_xml($dom,$noeud_categ,"LIB_DE",$libelle_categorie);

    //Note application
    if($note_application && $aff_note_application){
    	creer_noeud_xml($dom,$noeud_categ,"NA",$note_application);
    }
    
     //Commentaire public
    if($comment_public && $aff_commentaire){
    	creer_noeud_xml($dom,$noeud_categ,"NOTE",$comment_public);
    }
    
    //Voir aussi
    if($aff_voir_aussi){
    	$requete="SELECT libelle_categorie,num_noeud_dest FROM voir_aussi JOIN categories ON num_noeud_dest=num_noeud AND categories.langue='".$aff_langue."' WHERE num_noeud_orig='".$num_noeud."' AND voir_aussi.langue='".$aff_langue."' ORDER BY libelle_categorie";
	    $res=mysql_query($requete);
	    if($res && mysql_num_rows($res)){
	    	while ( $va=mysql_fetch_object($res) ) {
				if(trim($va->libelle_categorie)){
					creer_noeud_xml($dom,$noeud_categ,"TA",$va->libelle_categorie);
				}
			}
	    }
    }
   
    //Employ� pour
    if($aff_voir){
    	$requete="SELECT libelle_categorie,id_noeud FROM noeuds JOIN categories ON id_noeud=num_noeud AND categories.langue='".$aff_langue."' WHERE num_renvoi_voir='".$num_noeud."' ORDER BY libelle_categorie";
	    $res=mysql_query($requete);
	    if($res && mysql_num_rows($res)){
	    	while ( $ep=mysql_fetch_object($res) ) {
				if(trim($ep->libelle_categorie)){
					creer_noeud_xml($dom,$noeud_categ,"EP",$ep->libelle_categorie);
				}
			}
	    }
    }
    
    //Terme g�n�rique
    if($aff_tg && $num_parent){
    	$requete="SELECT libelle_categorie FROM categories WHERE langue='".$aff_langue."' AND num_noeud='".$num_parent."'";
	    $res=mysql_query($requete);
	    if($res && mysql_num_rows($res)){
	    	while ( $tg=mysql_fetch_object($res) ) {
				if(trim($tg->libelle_categorie)){
					creer_noeud_xml($dom,$noeud_categ,"TG",$tg->libelle_categorie);
				}
			}
	    }
    }
    
    //TS
    if($aff_ts){
    	$res=categories::listChilds($num_noeud, $aff_langue,0, "libelle_categorie");
	    if($res && mysql_num_rows($res)){
	    	$noeud_ts=creer_noeud_xml($dom,$noeud_categ,"TS".$niveau);
	    	while ($categ=mysql_fetch_object($res)) {
				if(trim($categ->libelle_categorie)){
					creer_categ_xml($dom,$noeud_ts,($niveau+1),$categ->num_noeud,$categ->libelle_categorie,$categ->note_application,$categ->comment_public,$categ->num_parent);
				}
			}
	    }
    } 
    
}

function encode_libelle_xml($val){
	global $charset;
	
	if($charset == "utf-8"){
		return htmlspecialchars($val,ENT_QUOTES,$charset);
	}else{
		return htmlspecialchars(utf8_encode($val),ENT_QUOTES,$charset);
	}
}

function creer_noeud_xml(&$dom,&$noeud_parent,$name,$val="",$att=array()){
	if($val){
		$noeud=$dom->createElement($name,encode_libelle_xml($val));
	}else{
		$noeud=$dom->createElement($name);
	}
	$noeud=$noeud_parent->appendChild($noeud);
	
	if(count($att)){
		do_att_xml($dom,$noeud,$att);
	}
	
	return $noeud;
}

function do_att_xml(&$dom,&$noeud_parent,$att=array()){
	foreach ( $att as $name => $value ) {
		$element_att = $dom->createAttribute($name);
		$element_att->value = $value;
		$noeud_parent->appendChild($element_att);
	}
}
//Fin des fonctions utilis�es pour l'export du th�saurus en xml

function cree_export_skos(&$dom,&$racine,$num_noeud_racine,$num_noeud_orphelins){
	global $aff_num_thesaurus,$uri_thes_skos,$do_polyhierarchie,$aff_voir_aussi;
	global $aff_note_application, $aff_commentaire,$aff_tg, $aff_ts,$aff_voir;
	
	//On corrige les renvoies de renvoies dans les noeuds si il y en a
	$arrayId=array();
	$monTest=true;
	while($monTest){
		$requete="SELECT n1.id_noeud, n2.num_renvoi_voir FROM noeuds n1 LEFT JOIN noeuds n2 ON n1.num_renvoi_voir=n2.id_noeud WHERE n1.num_renvoi_voir <>'0' AND  n2.num_renvoi_voir <>'0'";
		$res=mysql_query($requete);
		if(mysql_num_rows($res)){		
			while ($ligne=mysql_fetch_object($res)) {
				if(!in_array($ligne->id_noeud,$arrayId)){
					$arrayId[]=$ligne->id_noeud;
				}
				mysql_query("UPDATE noeuds SET num_renvoi_voir=".$ligne->num_renvoi_voir." WHERE id_noeud=".$ligne->id_noeud);
			}
		}else{
			$monTest=false;
		}
	}
	
	//Je r�cup�re les ids de tous les noeuds qui n'ont pas de renvoie
	$requete="SELECT * FROM noeuds WHERE num_thesaurus='".$aff_num_thesaurus."' AND num_renvoi_voir='0' AND id_noeud != ".$num_noeud_racine." AND autorite != 'ORPHELINS'";
	$res=mysql_query($requete);
	if($res && mysql_num_rows($res)){
		while ($noeud=mysql_fetch_object($res)) {
			$concept=creer_noeud_xml($dom,$racine,"skos:Concept","",array("rdf:about"=>$uri_thes_skos.$noeud->id_noeud));
			
			$noeud_liee=array();
			//On ne traite jamais les Orphelins et le noeud racine
			$noeud_liee[$num_noeud_orphelins]=1;
			$noeud_liee[$num_noeud_racine]=1;
			
			//Gestion des parents
			if($aff_tg && $noeud->num_parent){
				if($num_noeud_racine == $noeud->num_parent){
					creer_noeud_xml($dom,$concept,"skos:topConceptOf","",array("rdf:resource"=>$uri_thes_skos."#Thesaurus"));
				}else{
					creer_noeud_xml($dom,$concept,"skos:broader","",array("rdf:resource"=>$uri_thes_skos.$noeud->num_parent));
				}
				$noeud_liee[$noeud->num_parent] = 1;
			}
			
			//Les renvois
			if($aff_voir){
				$requete="SELECT id_noeud, num_parent, libelle_categorie, langue FROM noeuds JOIN categories ON id_noeud=num_noeud AND noeuds.num_thesaurus=categories.num_thesaurus WHERE num_renvoi_voir='".$noeud->id_noeud."' AND noeuds.num_thesaurus='".$aff_num_thesaurus."'";
				$res_renvoi=mysql_query($requete);
				if($res_renvoi && mysql_num_rows($res_renvoi)){
					while ($renvoi=mysql_fetch_object($res_renvoi)) {
						if($do_polyhierarchie){
							//Je regarde si le libell� du renvoie est le m�me que celui du noeuds
							$requete="SELECT * FROM categories WHERE num_noeud='".$noeud->id_noeud."' AND num_thesaurus='".$aff_num_thesaurus."' AND libelle_categorie='".addslashes($renvoi->libelle_categorie)."' AND langue='".addslashes($renvoi->langue)."'";
							$res2=mysql_query($requete);
							if($res2 && mysql_num_rows($res2)){//Dans ce cas il s'agit de la m�me categ qui a �t� duppliqu� pour la polyhierarchie
								if(!$noeud_liee[$renvoi->num_parent]){
									creer_noeud_xml($dom,$concept,"skos:broader","",array("rdf:resource"=>$uri_thes_skos.$renvoi->num_parent));
									$noeud_liee[$renvoi->num_parent]=1;
								}else{
									creer_noeud_xml($dom,$concept,"skos:altLabel",$renvoi->libelle_categorie,array("xml:lang"=>substr($renvoi->langue,0,2)));
								}
							}else{
								creer_noeud_xml($dom,$concept,"skos:altLabel",$renvoi->libelle_categorie,array("xml:lang"=>substr($renvoi->langue,0,2)));
							}
						}else{
							creer_noeud_xml($dom,$concept,"skos:altLabel",$renvoi->libelle_categorie,array("xml:lang"=>substr($renvoi->langue,0,2)));
						}
					}
				}
			}
			
			//Gestion des enfants
			if($aff_ts){
				//$requete="SELECT id_noeud FROM noeuds WHERE num_parent='".$noeud->id_noeud."' AND  num_renvoi_voir='0' AND num_thesaurus='".$aff_num_thesaurus."'";
				$requete="SELECT id_noeud, num_renvoi_voir FROM noeuds WHERE num_parent='".$noeud->id_noeud."' AND num_thesaurus='".$aff_num_thesaurus."'";
				$res_narrower=mysql_query($requete);
				if($res_narrower && mysql_num_rows($res_narrower)){
					while ($narrower=mysql_fetch_object($res_narrower)) {
						if($narrower->num_renvoi_voir){
							if(!$noeud_liee[$narrower->num_renvoi_voir]){
								creer_noeud_xml($dom,$concept,"skos:narrower","",array("rdf:resource"=>$uri_thes_skos.$narrower->num_renvoi_voir));
								$noeud_liee[$narrower->num_renvoi_voir]=1;
							}
						}elseif(!$noeud_liee[$narrower->id_noeud]){
							creer_noeud_xml($dom,$concept,"skos:narrower","",array("rdf:resource"=>$uri_thes_skos.$narrower->id_noeud));
							$noeud_liee[$narrower->id_noeud]=1;
						}
						
					}
				}
			}
			
			
			//Je vais chercher les informations des cat�gories
			$requete="SELECT * FROM categories WHERE num_noeud='".$noeud->id_noeud."' AND num_thesaurus='".$aff_num_thesaurus."'";
			$res_cat=mysql_query($requete);
			if($res_cat && mysql_num_rows($res_cat)){
				while ($categ=mysql_fetch_object($res_cat)) {
					//Appartenance au sch�ma
					creer_noeud_xml($dom,$concept,"skos:inScheme","",array("rdf:resource"=>$uri_thes_skos."#Thesaurus"));
					
					//Libell�
					if($tmp = trim($categ->libelle_categorie)){
						creer_noeud_xml($dom,$concept,"skos:prefLabel",$categ->libelle_categorie,array("xml:lang"=>substr($categ->langue,0,2)));
					}
					
					 //Note application
				    if($aff_note_application){
				    	if($tmp = trim($categ->note_application)){
							creer_noeud_xml($dom,$concept,"skos:scopeNote",$categ->note_application,array("xml:lang"=>substr($categ->langue,0,2)));
						}
				    }
				    
				     //Commentaire public
				    if($aff_commentaire){
				    	if($tmp = trim($categ->comment_public)){
							creer_noeud_xml($dom,$concept,"skos:note",$categ->comment_public,array("xml:lang"=>substr($categ->langue,0,2)));
						}
				    }
				}
			}
			
			//Les voir aussi
			if($aff_voir_aussi){
				$requete="SELECT num_noeud_dest FROM voir_aussi WHERE num_noeud_orig='".$noeud->id_noeud."'";
				$res_related=mysql_query($requete);
				if($res_related && mysql_num_rows($res_related)){
					while ($related=mysql_fetch_object($res_related)) {
						if(!$noeud_liee[$related->num_noeud_dest]){
							creer_noeud_xml($dom,$concept,"skos:related","",array("rdf:resource"=>$uri_thes_skos.$related->num_noeud_dest));
							$noeud_liee[$related->num_noeud_dest]=1;
						}
					}
				}
			}
		}
	}
}

?>