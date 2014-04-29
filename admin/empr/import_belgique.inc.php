<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: import_belgique.inc.php,v 1.9 2010-12-01 16:28:26 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/emprunteur.class.php");
//import_belgique - Version modifi�e par A.-M Cubat en avril 2006
//Ecole N.-D. de la Sagesse - Avenue Van Overbeke, 10 - B-1083 Bruxelles (Belgique)


//Diff�rence majeure entre l'import "Bretagne" et l'import "Belgique" : l'import des professeurs

//Dans la version import_bretagne, les �l�ves �taient "import�s" en groupe (cr�ation automatique des groupes, insertion automatique dans le bon groupe)
//Les profs �taient "import�s" individuellement, le nombre de champs import�s dans leur fiche "lecteur" �tait r�duit, leur num�ro de lecteur �tait tir� au sort.

//Dans la version import_belgique, non seulement les groupes "�l�ves", mais aussi les groupes "professeurs" sont cr��s automatiquement
//Il n'est plus n�cessaire de cr�er "manuellement" un ou plusieurs groupes de professeurs en r��ditant la fiche de chaque membre du personnel.
//C'est donc un avantage majeur, qui apporte un net gain de temps au niveau de l'encodage. Imaginez la situation s'il y a une centaine de professeurs.

//Merci aux concepteurs de import_bretagne, ce sont eux qui ont mis au point l'import des �l�ves avec cr�ation automatique des groupes
//Je n'ai aucun m�rite, je n'ai rien invent� - j'ai modifi� un programme existant que j'aurais �t� incapable de r�diger moi-m�me.
//J'y ai simplement ajout� la possibilit� de cr�er �galement des groupes de professeurs.


//import_belgique permet donc d'avoir pour les professeurs exactement les m�mes champs que pour les �l�ves.
//Vous n'aurez donc qu'un seul mod�le de fichier de lecteurs en Excel, ce qui est plus pratique.

//La structure du fichier texte doit �tre la suivante : 11 champs (pour tout le monde)
//Num�ro identifiant/Nom/Pr�nom/Rue/Compl�ment de rue/Code postal/Commune/T�l�phone/Date de naissance/Classe/Sexe

//La liste de ces 11 champs figurera peut-�tre � la premi�re ligne de votre fichier Excel (c'est souvent plus simple, ne vous privez pa de cette facilit�)
//Mais si c'est le cas, il faut absolument la supprimer avant de convertir le fichier. La fiche du premier lecteur doit se trouver � la premi�re ligne.
//Sinon, vous cr�ez un lecteur dont le nom est "nom", dont le pr�nom est "pr�nom", et dont l'ann�e de naissance est "ann�e" au lieu d'une date !

//Pas question de changer l'ordre des champs, ni d'en supprimer
//Vous n'�tes pas oblig� de compl�ter ces 11 champs, mais n'oubliez pas de laisser dans ce cas des colonnes vides dans Excel.
//Evitez toutefois de laisser le champ "sexe" vide pour la fiche du premier lecteur (car c'est le dernier champ de la premi�re ligne).
//Excel risque de "croire" qu'il y a moins de 11 colonnes � convertir (le nombre de cellules compl�t�es � la premi�re ligne est d�terminant � cet �gard).

//Le fichier Excel doit �tre converti en format .csv avant l'import dans PMB - c'est un type de fichier texte avec le point-virgule comme s�parateur de champ.
//V�rifiez que chaque ligne du fichier .csv se termine bien par un point-virgule, sinon vous perdrez le dernier caract�re du dernier champ de chaque ligne.

//Contenu des champs
//Pour le Sexe, vous mettez "M" ou "F" (�l�ves ou professeurs, peu importe)
//SI vous voulez cr�er des groupes (d'�l�ves ou de professeurs), il faut bien s�r compl�ter la colonne "Classe" pour tout le monde.

//Si le champ "Classe" reste vide, ce lecteur sera encod� dans la base mais ne fera partie d'aucun groupe, et risque donc de passer "inaper�u" dans certains cas.
//Si vous voulez cr�er plusieurs groupes de professeurs, il suffit d'avoir des libell�s diff�rents (Profs - Profs1 - Profs2 ...) - comme pour les �l�ves et leurs classes..
//Standardisez l'orthographe - "Profs 1" n'est pas la m�me chose que "Profs1" (avec ou sans espace interm�diaire) - cela cr�erait 2 groupes diff�rents.

//Au moment d'importer, s�lectionnez le fichier .csv et choisissez entre "import des �l�ves" et "import des professeurs".
//Le programme cr�e automatiquement les groupes (dans un cas comme dans l'autre), et ins�re aussi chacun dans le groupe correspondant..
//Il attribue le bon code statistique � chaque lecteur et le place �galement dans la bonne cat�gorie.


//La suite du commentaire concerne les adaptations �ventuelles � faire pour les codes statistiques et pour les cat�gories - en fonction de votre configuration.

//J'ai attribu� � tous les �l�ves et tous les professeurs le code statistique "�cole".
//J'ai mis les �l�ves dans la cat�gorie "�l�ves", les professeurs dans la cat�gorie "professeurs".
//Vous pouvez bien s�r changer ces param�tres, j'explique comment proc�der.

//Pour attribuer un code statistique ou une cat�gorie � un lecteur, il faut conna�tre la clef primaire (le "id") du code ou de la cat�gorie.
//Donc, il  faut aller voir dans les tables empr_codestat et empr_categ si vous voulez changer ceci.
//Clic droit de souris sur l'ic�ne PHP - choisir Administration - PHPMyAdmin - administration BDD.

//Voici comment les tables se pr�sentent chez nous, cela vous aidera � faire les modifications qui seront  probablement n�cessaires chez vous.

//Je n'ai pas supprim� de clefs primaires dans les tables empr_codestat et empr_categ, j'ai chang� les libell�s mais j'ai gard� les clefs primaires d'origine.

//Dans la table empr_codestat, j'ai gard� idcode = 2 mais j'ai remplac� le libell� "communaut� de communes" par le libell� "�cole".
//Dans la table empr_categ, j'ai gard� id_categ_empr = 1 mais j'ai remplac� le libell� "enfants"  par le libell� "�l�ves".
//Dans la table empr_categ, j'ai gard� id_categ_empr = 2 mais j'ai remplac� le libell� "retrait�s"  par le libell� "professeurs".

//Si vous voulez d'autres valeurs, c'est facile, il suffit de changer 4 lignes dans ce programme.
//Voici les lignes qui correspondent aux codes de notre configuration.

//Chez nous, les �l�ves ont le code statistique 2 (�cole), et sont dans la cat�gorie 1  (�l�ves)
//Import_eleves  - cet �l�ve n'est pas enregistr�).
//$req_insert .= "'$tab[6]', '$tab[7]', '$tab[8]', 1, 2, '$date_auj', '$sexe', ";
//Iimport_eleves (cet �l�ve est d�j�  enregistr�)
// $req_update .= "empr_tel1 = '$tab[7]', empr_year = '$tab[8]', empr_categ = '1', empr_codestat = '2', empr_modif = '$date_auj', empr_sexe = '$sexe', ";

//Chez nous, les profs ont le code statistique 2 (�cole), et sont dans la cat�gorie 2  (professeurs)
//Import_profs  - ce prof n'est pas enregistr�)
//$req_insert .= "'$tab[6]', '$tab[7]', '$tab[8]', 2, 2, '$date_auj', '$sexe', ";
//Import_profs  - ce prof est d�j� enregistr�)
//$req_update .= "empr_tel1 = '$tab[7]', empr_year = '$tab[8]', empr_categ = '2', empr_codestat = '2', empr_modif = '$date_auj', empr_sexe = '$sexe', ";

//Attention ! Comme je le disais, cette version est con�ue en fonction des n� de clefs primaires de la configuration actuelle de mon �cole.
//Si vous avez d'autres libell�s, ou les m�mes libell�s mais li�s � d'autres n� de clefs primaires, il faudra modifier les valeurs (les id)

//Chercher les commentaires suivants : Cet �l�ve est d�j� enregistr� - n'est pas enregistr� - Cet prof est d�j� enregistr� - n'est pas enregistr�
//Vous trouverez facilement les 4 lignes � modifier - ce sont celles reprises ci-dessus et qui commencent par $req_insert ou par $req_update
//Un bref commentaire � ces endroits-l� vous rappelle les codes que j'ai employ�s et vous permet de rep�rer ais�ment les lignes � changer.

//A vous de remplacer les valeurs qui s'y trouvent (1 ou 2 dans le cas pr�sent) par celles que vous trouverez dans vos tables empr_categ et empr_codestat.
//PHPMyAdmin vous permettra de savoir quelles clefs primaires correspondent aux libell�s des cat�gories et des codes statistiques que vous avez s�lectionn�s.

//Bonne chance !

//Explications de A.-M. Cubat


function show_import_choix_fichier($dbh) {
	global $msg;
	global $current_module ;

print "
<form class='form-$current_module' name='form1' ENCTYPE=\"multipart/form-data\" method='post' action=\"./admin.php?categ=empr&sub=implec&action=1\">
<h3>Choix du fichier</h3>
<div class='form-contenu'>
	<div class='row'>
		<label class='etiquette' for='form_import_lec'>".$msg["import_lec_fichier"]."</label>
        <input name='import_lec' accept='text/plain' type='file' class='saisie-80em' size='40'>
		</div>	
	<div class='row'>
        <label class='etiquette' for='form_import_lec'>". $msg["import_lec_separateur"]."</label>
        <select name='Sep_Champs' >
            <option value=';'>;</option>
            <option value='.'>.</option>
        </select>
    </div>
    <br />
	<div class='row'>
        <input type=radio name='type_import' value='nouveau_lect' checked>
        <label class='etiquette' for='form_import_lec'>Nouveaux lecteurs</label>
        (ajoute ou modifie les lecteurs pr�sents dans le fichier)
        <br />
        <input type=radio name='type_import' value='maj_complete'>
        <label class='etiquette' for='form_import_lec'>Mise � jour compl�te</label>
        (supprime les lecteurs non pr�sents dans le fichier et qui n'ont pas de pr�t en cours)
    </div>
    <div class='row'></div>
    
	</div>
<div class='row'>
	<input name='imp_elv' type='submit' class='bouton' value='Import des �l�ves'/>
	<input name='imp_prof' value='Import des professeurs' type='submit' class='bouton'/>
</div>
</form>";
}

function cre_login($nom, $prenom, $dbh) {
    $empr_login = substr($prenom,0,1).$nom ;
    $empr_login = strtolower($empr_login);
    $empr_login = clean_string($empr_login) ;
    $empr_login = convert_diacrit(strtolower($empr_login)) ;
    $empr_login = preg_replace('/[^a-z0-9\.]/', '', $empr_login);
    $pb = 1 ;
    $num_login=1 ;
    while ($pb==1) {
        $requete = "SELECT empr_login FROM empr WHERE empr_login='$empr_login' AND empr_nom <> '$nom' AND empr_prenom <> '$prenom' LIMIT 1 ";
        $res = mysql_query($requete, $dbh);
        $nbr_lignes = mysql_num_rows($res);
        if ($nbr_lignes) {
            $empr_login .= $num_login ;
            $num_login++;
        } 
        else $pb = 0 ;
    }
    return $empr_login;
}

function import_eleves($separateur, $dbh, $type_import){

    //La structure du fichier texte doit �tre la suivante : 
    //Num�ro identifiant/Nom/Pr�nom/Rue/Compl�ment de rue/Code postal/Commune/T�l�phone/Date de naissance/Classe/Sexe

    $eleve_abrege = array("Num�ro identifiant","Nom","Pr�nom");
    $date_auj = date("Y-m-d", time());
    $date_an_proch = date("Y-m-d", time()+3600*24*30.42*12);
    
    //Upload du fichier
    if (!($_FILES['import_lec']['tmp_name'])) {
        print "Cliquez sur Pr&eacute;c&eacute;dent et choisissez un fichier";
    } elseif (!(move_uploaded_file($_FILES['import_lec']['tmp_name'], "./temp/".basename($_FILES['import_lec']['tmp_name'])))) {
        print "Le fichier n'a pas pu �tre t�l�charg�. Voici plus d'informations :<br />";
        print_r($_FILES)."<p>";
    }
    $fichier = @fopen( "./temp/".basename($_FILES['import_lec']['tmp_name']), "r" );
       
    if ($fichier) {

        if ($type_import == 'maj_complete') {
            //Vide la table empr_groupe
            mysql_query("DELETE FROM empr_groupe",$dbh);
            //Supprime les �l�ves qui n'ont pas de pr�ts en cours
            $req_select_verif_pret = "SELECT id_empr FROM empr left join pret on id_empr=pret_idempr WHERE pret_idempr is null and empr_cb NOT LIKE 'E%'";
            $select_verif_pret = mysql_query($req_select_verif_pret,$dbh);
            while (($verif_pret = mysql_fetch_array($select_verif_pret))) {
            	//pour tous les emprunteurs qui n'ont pas de pret en cours
                emprunteur::del_empr($verif_pret["id_empr"]);
            }
        }
        
        
        while (!feof($fichier)) {
            $buffer = fgets($fichier, 4096);
            $buffer = mysql_escape_string($buffer);
            $tab = explode($separateur, $buffer);

            //Gestion du sexe
            switch ($tab[10]{0}) {
                case M: 
                    $sexe = 1;
                    break;
                case F:
                    $sexe = 2; 
                    break;
                default:
                    $sexe = 0;
                    break;
            }

            // Traitement de l'�l�ve
            $select = mysql_query("SELECT id_empr FROM empr WHERE empr_cb = '".$tab[0]."'",$dbh);
            $nb_enreg = mysql_num_rows($select);
            
            //Test si un num�ro id est fourni
            if (!$tab[0] || $tab[0] == "") {
                print("<b> El�ve non pris en compte car \"Num�ro identifiant\" non renseign� : </b><br />");
                for ($i=0;$i<3;$i++) {
                    print($eleve_abrege[$i]." : ".$tab[$i].", ");
                }
                print("<br />");
                $nb_enreg = 2;
            }
            
            $login = cre_login($tab[1],$tab[2], $dbh);
            
            switch ($nb_enreg) {
                case 0:
                	//Ce �l�ve n'est pas enregistr� 
                    $req_insert = "INSERT INTO empr(empr_cb, empr_nom, empr_prenom, empr_adr1, empr_adr2, empr_cp, empr_ville, ";
                    $req_insert .= "empr_tel1, empr_year, empr_categ, empr_codestat, empr_creation, empr_sexe,  ";
                    $req_insert .= "empr_login, empr_password, empr_date_adhesion, empr_date_expiration) ";
                    $req_insert .= "VALUES ('$tab[0]','$tab[1]','$tab[2]','$tab[3]', '$tab[4]', '$tab[5]', ";
	//V�rifier dans la table empr_categ si id_categ_empr 1 = �l�ves    V�rifier dans la table empr_codestat si idcode 2 = �cole    Sinon, changer les valeurs
                    $req_insert .= "'$tab[6]', '$tab[7]', '$tab[8]', 1, 2, '$date_auj', '$sexe', ";
                    $req_insert .= "'$login', '$tab[8]', '$date_auj', '$date_an_proch')";
                    $insert = mysql_query($req_insert,$dbh);
                    if (!$insert) {
                        print("<b>Echec de la cr�ation de l'�l�ve suivant (Erreur : ".mysql_error().") : </b><br />");
                        for ($i=0;$i<3;$i++) {
                            print($eleve_abrege[$i]." : ".$tab[$i].", ");
                        }
                        print("<br />");
                    }
                    else {
                        $cpt_insert ++;
                    }
                    gestion_groupe($tab[9], $tab[0], $dbh);
                    $j++;
                    break;

                case 1:
                	//Ce �l�ve est d�j� enregistr� 
                    $req_update = "UPDATE empr SET empr_nom = '$tab[1]', empr_prenom = '$tab[2]', empr_adr1 = '$tab[3]', ";
                    $req_update .= "empr_adr2 = '$tab[4]', empr_cp = '$tab[5]', empr_ville = '$tab[6]', ";
					//V�rifier dans la table empr_categ si id_categ_empr 1 = �l�ves    V�rifier dans la table empr_codestat si idcode 2 = �cole    Sinon, changer les valeurs
                    $req_update .= "empr_tel1 = '$tab[7]', empr_year = '$tab[8]', empr_categ = '1', empr_codestat = '2', empr_modif = '$date_auj', empr_sexe = '$sexe', ";
                    $req_update .= "empr_login = '$login', empr_password= '$tab[8]', ";
                    $req_update .= "empr_date_adhesion = '$date_auj', empr_date_expiration = '$date_an_proch' ";
                    $req_update .= "WHERE empr_cb = '$tab[0]'";
                    $update = mysql_query($req_update, $dbh);
                    if (!$update) {
                        print("<b>Echec de la modification de l'�l�ve suivant (Erreur : ".mysql_error().") : </b><br />");
                        for ($i=0;$i<3;$i++) {
                            print($eleve_abrege[$i]." : ".$tab[$i].", ");
                        }
                        print("<br />");
                    }
                    else {
                        $cpt_maj ++;
                    }
                    gestion_groupe($tab[9], $tab[0], $dbh);
                    $j++;
                    break;
                case 2:
                    break;
                default:
                    print("<b>Echec pour l'�l�ve suivant (Erreur : ".mysql_error().") : </b><br />");
                    for ($i=0;$i<3;$i++) {
                        print($eleve_abrege[$i]." : ".$tab[$i].", ");
                    }
                    print("<br />");
                    break;
            }
        }

        //Affichage des insert et update
        print("<br />_____________________<br />");
        if ($cpt_insert) print($cpt_insert." El�ves cr��s. <br />");
        if ($cpt_maj) print($cpt_maj." El�ves modifi�s. <br />");
        fclose($fichier);
    }
    
}

function gestion_groupe($lib_groupe, $empr_cb, $dbh) {
    
    $sel = mysql_query("SELECT id_groupe from groupe WHERE libelle_groupe = \"".$lib_groupe."\"",$dbh);
    $nb_enreg_grpe = mysql_num_rows($sel);
    
    if (!$nb_enreg_grpe) {
		//insertion dans la table groupe
		mysql_query("INSERT INTO groupe(libelle_groupe) VALUES(\"".$lib_groupe."\")");
		$groupe=mysql_insert_id();
    	} else {
    		$grpobj = mysql_fetch_object ($sel) ;
    		$groupe = $grpobj->id_groupe ; 
    		}

	//insertion dans la table empr_groupe
    $sel_empr = mysql_query("SELECT id_empr FROM empr WHERE empr_cb = \"".$empr_cb."\"",$dbh);
    $empr = mysql_fetch_array($sel_empr);
    @mysql_query("INSERT INTO empr_groupe(empr_id, groupe_id) VALUES ('$empr[id_empr]','$groupe')");
}

function import_profs($separateur, $dbh, $type_import){

    //La structure du fichier texte doit �tre la suivante : 
    //Num�ro identifiant/Nom/Pr�nom/Rue/Compl�ment de rue/Code postal/Commune/T�l�phone/Date de naissance/Classe/Sexe
 
    $prof_abrege = array("Num�ro identifiant","Nom","Pr�nom");
    $date_auj = date("Y-m-d", time());
    $date_an_proch = date("Y-m-d", time()+3600*24*30.42*12);
    
    //Upload du fichier
    if (!($_FILES['import_lec']['tmp_name']))
        print "Cliquez sur Pr&eacute;c&eacute;dent et choisissez un fichier";
    elseif (!(move_uploaded_file($_FILES['import_lec']['tmp_name'], "./temp/".basename($_FILES['import_lec']['tmp_name'])))) {
        print "Le fichier n'a pas pu �tre t�l�charg�. Voici plus d'informations :<br />";
        print_r($_FILES)."<p>";
    }
    $fichier = @fopen( "./temp/".basename($_FILES['import_lec']['tmp_name']), "r" );
       
    if ($fichier) {

        if ($type_import == 'maj_complete') {
            //Vide la table empr_groupe
            mysql_query("DELETE FROM empr_groupe",$dbh);
            //Supprime les profs qui n'ont pas de pr�ts en cours
            $req_select_verif_pret = "SELECT id_empr FROM empr left join pret on id_empr=pret_idempr WHERE pret_idempr is null and empr_cb NOT LIKE 'E%'";
            $select_verif_pret = mysql_query($req_select_verif_pret,$dbh);
            while (($verif_pret = mysql_fetch_array($select_verif_pret))) {
            	//pour tous les emprunteurs qui n'ont pas de pret en cours
                emprunteur::del_empr($verif_pret["id_empr"]);
            }
        }
        
        
        while (!feof($fichier)) {
            $buffer = fgets($fichier, 4096);
            $buffer = mysql_escape_string($buffer);
            $tab = explode($separateur, $buffer);

            //Gestion du sexe
            switch ($tab[10]{0}) {
                case M: 
                    $sexe = 1;
                    break;
                case F:
                    $sexe = 2; 
                    break;
                default:
                    $sexe = 0;
                    break;
            }

            // Traitement du prof
            $select = mysql_query("SELECT id_empr FROM empr WHERE empr_cb = '".$tab[0]."'",$dbh);
            $nb_enreg = mysql_num_rows($select);
            
            //Test si un num�ro id est fourni
            if (!$tab[0] || $tab[0] == "") {
                print("<b> Prof non pris en compte car \"Num�ro identifiant\" non renseign� : </b><br />");
                for ($i=0;$i<3;$i++) {
                    print($prof_abrege[$i]." : ".$tab[$i].", ");
                }
                print("<br />");
                $nb_enreg = 2;
            }
            
            $login = cre_login($tab[1],$tab[2], $dbh);
            
            switch ($nb_enreg) {
                case 0:
                	//Ce prof n'est pas enregistre 
                    $req_insert = "INSERT INTO empr(empr_cb, empr_nom, empr_prenom, empr_adr1, empr_adr2, empr_cp, empr_ville, ";
                    $req_insert .= "empr_tel1, empr_year, empr_categ, empr_codestat, empr_creation, empr_sexe,  ";
                    $req_insert .= "empr_login, empr_password, empr_date_adhesion, empr_date_expiration) ";
                    $req_insert .= "VALUES ('$tab[0]','$tab[1]','$tab[2]','$tab[3]', '$tab[4]', '$tab[5]', ";
					//Verifier dans la table empr_categ si id_categ_empr 2 = profs    Verifier dans la table empr_codestat si idcode 2 = ecole    Sinon, changer les valeurs
                    $req_insert .= "'$tab[6]', '$tab[7]', '$tab[8]', 2, 2, '$date_auj', '$sexe', ";
                    $req_insert .= "'$login', '$tab[8]', '$date_auj', '$date_an_proch')";
                    $insert = mysql_query($req_insert,$dbh);
                    if (!$insert) {
                        print("<b>Echec de la cr�ation du prof suivant (Erreur : ".mysql_error().") : </b><br />");
                        for ($i=0;$i<3;$i++) {
                            print($prof_abrege[$i]." : ".$tab[$i].", ");
                        }
                        print("<br />");
                    }
                    else {
                        $cpt_insert ++;
                    }
                    gestion_groupe($tab[9], $tab[0], $dbh);
                    $j++;
                    break;

                case 1:
                	//Ce prof est d�ja enregistr� 
                    $req_update = "UPDATE empr SET empr_nom = '$tab[1]', empr_prenom = '$tab[2]', empr_adr1 = '$tab[3]', ";
                    $req_update .= "empr_adr2 = '$tab[4]', empr_cp = '$tab[5]', empr_ville = '$tab[6]', ";
	//V�rifier dans la table empr_categ si id_categ_empr 2 = profs    V�rifier dans la table empr_codestat si idcode 2 = �cole    Sinon, changer les valeurs
                    $req_update .= "empr_tel1 = '$tab[7]', empr_year = '$tab[8]', empr_categ = '2', empr_codestat = '2', empr_modif = '$date_auj', empr_sexe = '$sexe', ";
                    $req_update .= "empr_login = '$login', empr_password= '$tab[8]', ";
                    $req_update .= "empr_date_adhesion = '$date_auj', empr_date_expiration = '$date_an_proch' ";
                    $req_update .= "WHERE empr_cb = '$tab[0]'";
                    $update = mysql_query($req_update, $dbh);
                    if (!$update) {
                        print("<b>Echec de la modification du prof suivant (Erreur : ".mysql_error().") : </b><br />");
                        for ($i=0;$i<3;$i++) {
                            print($prof_abrege[$i]." : ".$tab[$i].", ");
                        }
                        print("<br />");
                    }
                    else {
                        $cpt_maj ++;
                    }
                    gestion_groupe($tab[9], $tab[0], $dbh);
                    $j++;
                    break;
                case 2:
                    break;
                default:
                    print("<b>Echec pour le prof suivant (Erreur : ".mysql_error().") : </b><br />");
                    for ($i=0;$i<3;$i++) {
                        print($prof_abrege[$i]." : ".$tab[$i].", ");
                    }
                    print("<br />");
                    break;
            }
        }

        //Affichage des insert et update
        print("<br />_____________________<br />");
        if ($cpt_insert) print($cpt_insert." Prof cr��s. <br />");
        if ($cpt_maj) print($cpt_maj." Profs modifi�s. <br />");
        fclose($fichier);
    }
    
}




switch($action) {
    case 1:
        if ($imp_elv){
            import_eleves($Sep_Champs, $dbh, $type_import);
        }
        elseif ($imp_prof) {
            import_profs($Sep_Champs, $dbh, $type_import);
        }
        else {
            show_import_choix_fichier($dbh);
        }
        break;
    case 2:
        break;
    default:
        show_import_choix_fichier($dbh);
        break;
}

?>



