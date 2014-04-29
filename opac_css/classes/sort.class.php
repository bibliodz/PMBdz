<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sort.class.php,v 1.40 2014-01-21 14:50:22 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
	die("no access");

require_once ($include_path . "/parser.inc.php");
require_once ($include_path . "/templates/sort.tpl.php");
require_once ($class_path . "/parametres_perso.class.php");

/**
 * Classe d'abstraction d'acces aux donn�es des tris stock�s
 * l'admin utilise la base et l'opac les sessions 
 */
class dataSort {

	var $typeData; //base ou session
	var $sortName;
	
	//pour le parcours des tris
	var $tabParcours;
	var $posParcours;

	function dataSort($sName,$tData) {
		$this->sortName = $sName;
		$this->typeData = $tData;
	}
	
	/**
	 * Retourne un tableau avec le nom du tri et la construction du tri
	 */
	function recupTriParId($id) {
		global $opac_default_sort;
		// tri par d�faut...
		if($id == "default"){
			//Plusieurs tris par d�faut d�finis dans les param�tres ? On va cherche le tout premier
			if (strstr($opac_default_sort,'|')) {
				$tmpArray = explode(";",$opac_default_sort);
				$tmpSort=explode("|",$tmpArray[0]);
				$tab["nom_tri"] = $tmpSort[1];
				$tab["tri_par"] = $tmpSort[0];
			} else {
				$tab["nom_tri"] = "";
				$tab["tri_par"] = $opac_default_sort!= "" ? $opac_default_sort : "d_num_6,c_text_1";
			}
			return $tab;
		}
		switch($this->typeData) {
			case 'base':
				$result = mysql_query("SELECT nom_tri, tri_par FROM tris WHERE id_tri=" . $id);
				if ($result) {
					$tab = mysql_fetch_assoc($result);
					mysql_free_result($result);
					return $tab;
				} else
					return null;
				break;
			case 'session':
				$tab["nom_tri"] = "";
				if($_SESSION["sortname".$this->sortName.$id]){
					$tab["nom_tri"] = $_SESSION["sortname".$this->sortName.$id];
				}
				$tab["tri_par"] = $_SESSION["sort".$this->sortName.$id];
				return $tab;
				break;
		}
	}
	

	/**
	 * Pour initialiser un parcours des tris
	 * Retourne le nombre de tris
	 */
	function initParcoursTris($objSort) {
		//on initialise la position du parcours
		$this->posParcours = 0;
		$this->nbResult=0;
		$this->tabParcours=null;
		
		switch($this->typeData) {
			
			case 'base':
				$result = mysql_query("SELECT id_tri, nom_tri, tri_par FROM tris WHERE tri_reference='" . $this->sortName . "' ORDER BY nom_tri;");
				//echo "SELECT id_tri, nom_tri, tri_par FROM tris WHERE tri_reference='" . $this->sortName . "' ORDER BY nom_tri<br />";
				if ($result) {
					
					//on charge les tris dans un tableau
					while ($this->tabParcours[$this->nbResult] = mysql_fetch_assoc($result)) {
						$this->nbResult++;
					}
					mysql_free_result($result);
					
					//s'il n'y a pas de tris
					if ($this->nbResult==0) {
						//on vide la session stockant le tri en cours
						$_SESSION["tri"] = "";
					}
					
					return $this->nbResult;
				} else {
					$_SESSION["tri"] = "";
					return 0;
				}
				break;
			
			case 'session':
				$this->nbResult = $_SESSION["nb_sort".$this->sortName];

				//s'il n'y a pas de tris
				if ($this->nbResult==0) {
					//on vide la session stockant le tri en cours
					$_SESSION["last_sort".$this->sortName]="";
				} else {
					//on charge les tris dans un tableau
					for($i=0; $i<$this->nbResult; $i++) {
						$this->tabParcours[$i]["id_tri"] = $i;
						$this->tabParcours[$i]["nom_tri"] = $objSort->descriptionTri($_SESSION["sort".$this->sortName.$i]);
						if($_SESSION["sortname".$this->sortName.$i]){
							$this->tabParcours[$i]["nom_tri"] = $_SESSION["sortname".$this->sortName.$i];
						}
						$this->tabParcours[$i]["tri_par"] = $_SESSION["sort".$this->sortName.$i];
					}
				}
				return $this->nbResult;
				break;
		}
		
	}
	
	/**
	 * Renvoi le tri suivant dans un parcours
	 */
	function parcoursTriSuivant() {
		switch($this->typeData) {
			case 'base':
				$result = $this->tabParcours[$this->posParcours];
				$this->posParcours++;
				return $result;
				break;
			case 'session':
				$result = $this->tabParcours[$this->posParcours];
				$this->posParcours++;
				return $result;
				break;
		}
	}
	
	/**
	 * Enregistre un tri
	 */
	function enregistreTri($id,$nomTri,$desTri) {
		global $msg;
		global $charset;

		switch($this->typeData) {
			case 'base':

				//$criteres = implode(",",$desTri);
				$txt_requete = "";
				
				//modif ou insertion ?
				if ($id != "") {
					//on modifie le tri avec les nouveaux criteres
					$txt_requete = "UPDATE tris SET nom_tri='" . htmlentities($nomTri, ENT_QUOTES, $charset) . "', tri_par='" . $desTri . "' ";
					$txt_requete .= "WHERE id_tri='" . $id . "'";
				} else {
					//on v�rifie que le nom de tri n'existe pas
					$txt_requete = "SELECT id_tri FROM tris WHERE nom_tri='" . htmlentities($nomTri, ENT_QUOTES, $charset) . "'";
					$txt_requete .= " AND tri_reference='" . $this->sortName . "'";
					if (mysql_num_rows(mysql_query($txt_requete)) == 0) {
						//on genere la requete d'insertion
						$txt_requete = "INSERT INTO tris (id_tri, tri_reference, nom_tri, tri_par) ";
						$txt_requete .= "VALUES ('','" . $this->sortName . "','" . htmlentities($nomTri, ENT_QUOTES, $charset) . "','" . $desTri . "')";
					} else {
						//le nom existe : on le dit
						return "<script>alert ('" . $msg['tri_existant'] . "');history.back();</script>";
					}
				}
				
				if ($txt_requete!="") {
					
					//execution de la requete de modif ou d'insertion
					$requete = mysql_query($txt_requete);
					
					if (!$requete) {
						// il y a eu une erreur d'execution de la requete
						return "Erreur mysql : " . $txt_requete . "<br />" . mysql_error();
					} else
						return "";
				} else
					return "";

				break;

			case 'session':
				//si nombre de tris enregistr�s dans la session n'est pas null, parcours des variables de session pour l'existence tri et sauvegarde
				if ($_SESSION["nb_sort".$this->sortName]<=0) {
					$_SESSION["sort".$this->sortName.$_SESSION["nb_sort".$this->sortName]]=htmlentities($desTri,ENT_QUOTES,$charset);
					if ($nomTri) {
						$_SESSION["sortname".$this->sortName.$_SESSION["nb_sort".$this->sortName]]=htmlentities($nomTri,ENT_QUOTES,$charset);
					}
					$_SESSION["nb_sort".$this->sortName]++;
				} else {
					$bool=false;
					for ($i=0;$i<$_SESSION["nb_sort".$this->sortName];$i++) {
						if ($_SESSION["sort".$this->sortName.$i] == htmlentities($desTri,ENT_QUOTES,$charset)) {	
							$bool=true;
						}
					}
					if ($bool==true) {
						return "<script>alert ('".$msg['tri_existant']."');</script>";
					} else {
						$_SESSION["sort".$this->sortName.$_SESSION["nb_sort".$this->sortName]] = htmlentities($desTri,ENT_QUOTES,$charset);
						if ($nomTri) {
							$_SESSION["sortname".$this->sortName.$_SESSION["nb_sort".$this->sortName]] = htmlentities($nomTri,ENT_QUOTES,$charset);
						}
						$_SESSION["nb_sort".$this->sortName]++;
					}		
				}
				break;

		}
	}
	
	/**
	 * Supprime un tri
	 */
	function supprimeTri($sort_ids=array()) {
		global $dbh;
		switch($this->typeData) {
			case 'base':
				$q = 'delete from tris where id_tri in('.implode(',',$sort_ids).') ';
				@mysql_query($q,$dbh);			
				break;
			case 'session':
				$nb_sort = $_SESSION['nb_sort'.$this->sortName];
				$last_sort = $_SESSION['sort'.$this->sortName.$_SESSION['last_sort'.$this->sortName]];
				
				//stockage des tris a conserver dans un tableau et suppression des variables session
				$tab_sort = array();
				$j=0;
				for($i=0; $i<$nb_sort; $i++) {
					if (!in_array($i,$sort_ids)) {
						//ce n'est pas un tri a supprimer
						//on le stocke dans le tableau
						$tab_sort[$j]['descTri'] = $_SESSION["sort".$this->sortName.$i];
						$tab_sort[$j]['nomTri'] = $_SESSION["sortname".$this->sortName.$i];
						$j++;
					}
					unset($_SESSION['sort'.$this->sortName.$i]);
					unset($_SESSION['sortname'.$this->sortName.$i]);
				}
				
				//reaffectation des variables session
				$_SESSION['last_sort'.$this->sortName]="";
				foreach($tab_sort as $k=>$v) {
					$_SESSION['sort'.$this->sortName.$k]=$v['descTri'];
					$_SESSION['sortname'.$this->sortName.$k]=$v['nomTri'];
					if ($last_sort==$v) {
						$_SESSION['last_sort'.$this->sortName]=$k;
					}
				}
				$_SESSION['nb_sort'.$this->sortName]=count($tab_sort);
				break;
		}
	}
	
}



/**
 * Classe de tri des r�sultats de recherche dans le catalogue
 * Utilise une variable de session("tri") pour stocker le tri en cours
 * 
 */
class sort {
	//var $sort_name;
	var $params;
	var $error = false;
	var $error_message = "";
	var $table_tri_tempo = "tri_tempo"; //table temporaire � utiliser
	var $table_primary_tri_tempo; //Cl� primaire de la table temporaire � cr�er
	var $limit; //limitation des enregistrements � utiliser dans la requ�te de tri pour le pager
	var $champs_select; //champs �ventuels � retourner dans la requ�te
	var $table_select; //table �ventuelle � retourner dans la requ�te
	var $table_primary_key_select; //cl� de la table �ventuelle � retourner dans la requ�te
	var $dSort; // objet d'acces aux informations 
	private static $nb_instance = 1;


	/**
	 * Applique le tri donn�
	 * @$sort_name nom du tri � appliquer
	 */
	function sort($sort_name, $accesTri) {
		if ($sort_name) {
			$sname = $sort_name;
		} else {
			$sname = 'notices';
		}
		$this->table_tri_tempo .= "_".self::$nb_instance;
		self::$nb_instance++;

		if ($accesTri) {
			$this->dSort = new dataSort($sname,$accesTri);
		} else {
			$this->dSort = new dataSort($sname,'base');
		}
		//on charge le fichier XML
		$this->parse();
		//on ajoute les tris par d�faut ajout�s en param�tres
		$this->add_default_sort();
	}
	
	/**
	* Ajoute les tris par d�faut �ventuellement saisis en param�tre
	*/
	function add_default_sort(){
		global $opac_default_sort_list;
		
		$sortArray = explode(" ",$opac_default_sort_list,2);
		if ($sortArray[1] != "") {
			if (strstr($sortArray[1],'|')) {
				//on v�rifie l'existence d'un flag : que la recherche par d�faut ne revienne pas si l'utilisateur l'a supprim�e par le formulaire
				if(!isset($_SESSION['sort'.$this->sortName.'flag'])){
					$tmpArray = explode(";",$sortArray[1]);
					foreach($tmpArray as $tmpArrayElement){
						//On r�cup�re la chaine de tri, et le libell�
						if(trim($tmpArrayElement)){
							$tmpSort=explode("|",$tmpArrayElement);
							$this->dSort->enregistreTri('',$tmpSort[1],$tmpSort[0]);
						}
					}
					$_SESSION['sort'.$this->sortName.'flag']=1;
				}
			} else if (strstr($sortArray[1],';')) {
				//on v�rifie l'existence d'un flag : que la recherche par d�faut ne revienne pas si l'utilisateur l'a supprim�e par le formulaire
				if(!isset($_SESSION['sort'.$this->sortName.'flag'])){
					$tmpArray = explode(";",$sortArray[1]);
					foreach($tmpArray as $tmpElement){
						//On r�cup�re la chaine de tri
						if(trim($tmpElement)){
							$this->dSort->enregistreTri('','',$tmpElement);
						}
					}
					$_SESSION['sort'.$this->sortName.'flag']=1;
				}
			} else {
				//on v�rifie l'existence d'un flag : que la recherche par d�faut ne revienne pas si l'utilisateur l'a supprim�e par le formulaire
				if(!isset($_SESSION['sort'.$this->sortName.'flag'])){
					//On r�cup�re la chaine de tri
					if(trim($sortArray[1])){
						$this->dSort->enregistreTri('','',$sortArray[1]);
					}
				}
				$_SESSION['sort'.$this->sortName.'flag']=1;
			}
		}
	}
	
	
	/**
	 * Affiche l'�cran de choix des tris enregistr�s
	 */
	function show_tris_form() {
		global $show_tris_form;
		global $ligne_tableau_tris;
		global $msg;

		if ($this->dSort->initParcoursTris($this) == 0 ) { 
			//il n'y a pas de tris enregistr�s
			
			//on renvoie un message pour le dire
			$tris = $msg['aucun_tri'];
			
		} else {
			// creation du tableau de la liste des tris enregistr�s
			$parity = 1;
			$tris = "";

			//affichage des enregistrements de tris possibles
			while ($result = $this->dSort->parcoursTriSuivant()) {
				//gestion du surlignage une ligne sur 2 
				if ($parity % 2)
					$pair_impair = "even";
				else
					$pair_impair = "odd";
				
				//html d'une ligne
				$tristemp = str_replace("!!id_tri!!", $result['id_tri'], $ligne_tableau_tris);
				$tristemp = str_replace("!!nom_tri!!", $result['nom_tri'], $tristemp);
				$tristemp = str_replace("!!pair_impair!!", $pair_impair, $tristemp);
				$tris .= $tristemp;
				
				$parity += 1;
			}
		}
		
		//on remplace dans le template les informations issues de la base
		$tris_form = str_replace("!!sortname!!", $this->dSort->sortName, $show_tris_form);
		$tris_form = str_replace("!!liste_tris!!", $tris, $tris_form);
		return $tris_form;
	}
	
	/**
	* affiche un selecteur des tris disponibles
	*/
	static function show_tris_selector() {
		global $msg,$opac_default_sort_list;

		$sortArray = explode(" ",$opac_default_sort_list);
		//Mode Ajax
		if ($sortArray[0] == 0) {
			$tris_selector = "&nbsp;<script type='text/javascript' src='./includes/javascript/select.js'></script>
						<script>
							var ajax_get_sort=new http_request();
			
							function get_sort_content(del_sort, ids) {
								var url = './ajax.php?module=ajax&categ=sort&sub=get_sort&raz_sort='+(typeof(del_sort) != 'undefined' ? del_sort : '')+'&suppr_ids='+(typeof(ids) != 'undefined' ? ids : '')+'&page_en_cours=!!page_en_cours!!';
								  ajax_get_sort.request(url,0,'',1,show_sort_content,0,0);
							}
			
							function show_sort_content(response) {
								document.getElementById('frame_notice_preview').innerHTML=ajax_get_sort.get_text();
								var tags = document.getElementById('frame_notice_preview').getElementsByTagName('script');
					       		for(var i=0;i<tags.length;i++){
									window.eval(tags[i].text);
					        	}
							}
							function kill_sort_frame() {
								var sort_view=document.getElementById('frame_notice_preview');
								if (sort_view)
									sort_view.parentNode.removeChild(sort_view);
							}
						</script>
						<a onClick='show_layer(); get_sort_content();' alt=\"".$msg['tris_dispos']."\" title=\"".$msg['tris_dispos']."\" style='cursor : pointer;'><img src='./images/orderby_az.gif' align='bottom' hspace='3' border='0' id='sort_icon'></a>";
		} elseif ($sortArray[0] == 1) {
			global $sort;
			if ($sort != "") $sel_sort = $sort;
 			else $sel_sort = -1;
			// creation du tableau de la liste des tris enregistr�s
			$tris_selector = "&nbsp;<select name='tri_selector' id='tri_selector' onChange='applySort(this.options[this.selectedIndex].value)'>";
			//affichage des enregistrements de tris possibles
			$sort = new sort('notices','session');
			$sort->dSort->initParcoursTris($sort);
 			$tris_selector .= "<option value=''>".$sort->descriptionTriParId("default")."</option>";
			while ($result = $sort->dSort->parcoursTriSuivant()) {
				$tris_selector .= "<option value='".$result['id_tri']."' ".(($sel_sort == $result['id_tri']) ? "selected='selected'" : "").">";
				$tris_selector .= $result['nom_tri'];
				//$tris_selector .= addslashes($result['nom_tri']);
				$tris_selector .= "</option>";
			}
			$tris_selector .= "<option value='custom'>".$msg['tris_dispos']."</option>";
			$tris_selector .= "</select>
			<script>
			function applySort(value){
				if (value=='custom') {
					maPage='index.php?lvl=sort&page_en_cours=!!page_en_cours!!';
			
				} else {
					maPage='index.php?!!page_en_cours1!!&get_last_query=1&sort='+value;
				}
				document.location = maPage;
			}
			</script>&nbsp;";
		} else {
			$tris_selector = "&nbsp;<a href='index.php?lvl=sort&page_en_cours=!!page_en_cours!!' alt=\"".$msg['tris_dispos']."\" title=\"".$msg['tris_dispos']."\"><img src='./images/orderby_az.gif' align='bottom' hspace='3' border='0' id='sort_icon'></a>";
		}

		return $tris_selector;
	}


	/**
	 * 
	 */
	function show_sel_form($id_tri=0) {
		switch($this->dSort->typeData) {
			case 'base':
				return $this->show_sel_formAdmin($id_tri);
				break;
			case 'session':
				return $this->show_sel_formOPAC($id_tri);
				break;
		}
	}




	/**
	 * Affiche l'�cran de s�lection des criteres de tri
	 */
	function show_sel_formAdmin($id_tri) {
		global $show_sel_form;
		global $charset;
		global $msg;

		//les champs de tris possible
		$fields = $this->params["FIELD"];

		//initialisation des variables
		$liste_selectionnes = "";
		$nom_du_tri = "";

		//g�n�ration de la liste des criteres
		$liste_criteres = "";

		//si id_tri est renseign�, c'est alors une modification du tri s�lectionn�
		if ($id_tri!=0) {
			$result = $this->dSort->recupTriParId($id_tri);
			//$requete = mysql_query("SELECT nom_tri, tri_par FROM tris WHERE id_tri='" . $id_tri . "'");
			if ($result) {
				//$result = mysql_fetch_array($requete);
				$nom_du_tri = $result['nom_tri'];

				//recherche et d�composition du tri
				$tri_par = explode(",", $result['tri_par']);

				for ($i = 0; $i < count($tri_par); $i++) {
					
					//on decompose la description du critere de tri (c_num_2)
					$tri_par1 = explode("_", $tri_par[$i]);
					
					for ($j = 0; $j < count($fields); $j++) {
						//on parcours tous les champs (pour r�cuperer le nom)
						if ($fields[$j]["ID"] == $tri_par1[2]) {
							//on est dans le bon champs
							
							//on determine le type et le sens du tri pour l'affichage							
							switch ($tri_par1[1]) {
								case 'num' :
									if ($tri_par1[0] == "c")
										$debut = "0-9 ";
									else
										$debut = "9-0 ";
									break;
								case 'text' :
									if ($tri_par1[0] == "c")
										$debut = "A-Z ";
									else
										$debut = "Z-A ";
									break;
							}

							//la liste des champs s�lectionn�s
							$liste_selectionnes .= "<option value='" . $tri_par1[0] . "_" . $tri_par1[1] . "_" . $tri_par1[2] . "'>";
							//si champ perso, on a d�j� le libell�
							if($fields[$j]['SOURCE'] == "cp") $name = $fields[$j]['LABEL'];
							else $name = $msg[$fields[$j]['NAME']]; 
							$liste_selectionnes .= $debut . "" . htmlentities($name, ENT_QUOTES, $charset);
							$liste_selectionnes .= "</option>\n";
							
							//ce champ est utilise donc on ne l'affichera pas
							$fields[$j]["UTILISE"] = true;
						
						}
					}
				}

				//on cr�� la liste des criteres restants
				for ($j = 0; $j < count($fields); $j++) {
					// sans les champs d�ja utilis�s
					if ($fields[$j]["UTILISE"]!=true){
						//si champ perso, on a d�j� le libell�
						if($fields[$j]['SOURCE'] == "cp") $name = $fields[$j]['LABEL'];
						else $name = $msg[$fields[$j]['NAME']]; 
						$liste_criteres .= "<option value='c_" . $fields[$j]["TYPE"] . "_" . $fields[$j]["ID"] . "'>" . htmlentities($name, ENT_QUOTES, $charset) . "</option>\n"; 
					}
						
				}
				
			}
		} else {
			//on cr�� la liste des criteres
			for ($j = 0; $j < count($fields); $j++) {
				//si champ perso, on a d�j� le libell�
				if($fields[$j]['SOURCE'] == "cp") $name = $fields[$j]['LABEL'];
				else $name = $msg[$fields[$j]['NAME']]; 
				$liste_criteres .= "<option value='c_" . $fields[$j]["TYPE"] . "_" . $fields[$j]["ID"] . "'>" . htmlentities($name, ENT_QUOTES, $charset) . "</option>\n";
			}
		}

		//on remplace toutes les variables dans le template
		$sel_form = str_replace("!!id_tri!!", $id_tri, $show_sel_form);
		$sel_form = str_replace("!!sortname!!", $this->dSort->sortName, $sel_form);
		$sel_form = str_replace("!!nom_tri!!", $nom_du_tri, $sel_form);
		$sel_form = str_replace("!!liste_criteres!!", $liste_criteres, $sel_form);
		$sel_form = str_replace("!!liste_selectionnes!!", $liste_selectionnes, $sel_form);
		
		return $sel_form;
	}

    function show_sel_formOPAC() {
    	global $show_sel_form;
		global $liste_criteres_tri;
		global $charset;
		global $msg;
		global $opac_nb_max_criteres_tri;

		$fields = $this->params["FIELD"];

    	for ($i=0;$i<count($fields);$i++) {
    		//si champ perso, on a d�j� le libell�
			if($fields[$i]['SOURCE'] == "cp") $name = $fields[$i]['LABEL'];
			else $name = $msg[$fields[$i]['NAME']]; 
    		$liste_criteres.="<option value='".$fields[$i]["ID"]."'>".htmlentities($name,ENT_QUOTES,$charset)."</option>\n";
    	}
    	
    	$listes_tri = "";
    	for ($i=1;$i<$opac_nb_max_criteres_tri;$i++) {
    		$listes_tri .= str_replace("!!idLigne!!",$i,$liste_criteres_tri);
    	}

    	$sel_form = str_replace("!!liste_criteres_tri!!", $listes_tri, $show_sel_form);
    	$sel_form = str_replace("!!liste_criteres!!", $liste_criteres, $sel_form);

    	return $sel_form;
    }

	/**
	 * Enregistre les criteres de tri dans la table tris
	 */
	function sauvegarder($id_tri, $nom_tri, $tris_par) {
		global $msg;
		global $charset;

		$criteres = implode(",",$tris_par);

		return $this->dSort->enregistreTri($id_tri,$nom_tri,$criteres);

	}

	/**
	 * Supprime un tri sauvegarder
	 */
	function supprimer($sort_ids=array()) {
		$this->dSort->supprimeTri($sort_ids);
	}
	
	/**
	 * Retourne le texte de description du tri � partir de sa description
	 */
	function descriptionTri($desTri) {
		global $msg;

		//r�cuperations des champs
		$fields = $this->params["FIELD"];
		
		$tris_par = explode(",",$desTri);
		
		$trier_par_texte = "";
		foreach ($tris_par as $selectValue) {
			//d�coupage du champ (ex : c_num_2 (croissance ou d�croissance (c ou d),
			//type de champ (num,text,...) et id du champ)
			$temp = explode("_", $selectValue);

			//on genere le texte descriptif � afficher
			for ($i = 0; $i < count($fields); $i++) {
				if ($fields[$i]["ID"] == $temp[2]) {
					if($fields[$i]['SOURCE'] == "cp"){
						$trier_par_texte .= $fields[$i]['LABEL'] . " ";
					}else{
						$trier_par_texte .= $msg[$fields[$i]["NAME"]] . " ";
					}
					if ($temp[0] == "c") {
						$trier_par_texte .= $msg["tri_texte_croissant"];
					} else {
						$trier_par_texte .= $msg["tri_texte_decroissant"];
					}
					$trier_par_texte .= ",";
				}
			}
		}
		//on enleve la derniere virgule et on ajoute la )
		$trier_par_texte = substr($trier_par_texte, 0, strlen($trier_par_texte) - 1);
		
		return $trier_par_texte;
	}
	
	
	/**
	 * Retourne le texte de description du tri a partir d'un id
	 */
	function descriptionTriParId($id_tri) {
		global $msg;

		if ($id_tri!="") {
			
			//r�cup�ration de la description du tri
			$result = $this->dSort->recupTriParId($id_tri);

			$nom_tri = $result['nom_tri'];
			
			$trier_par_texte = "(" . $this->descriptionTri($result['tri_par']) . ")";
			
			//on concatene le message complet
			$trier_par_texte = $nom_tri." ".$trier_par_texte;
		
			return $trier_par_texte;
		} else
			return "";
				
	}
	
	
	/**
	 * Applique le tri s�lectionner
	 * Renvoi la requete finale utilisant les criteres de tri
	 */
	function appliquer_tri($idTri, $selectTempo, $nomColonneIndex,$debLimit,$nbLimit) {
		global $msg;

		//r�cuperations des champs
		$fields = $this->params["FIELD"];
		$tableEnCours = $this->table_tri_tempo;
		

		//creation de la table de tri
		//$cmd_table = "DROP TABLE " . $tableEnCours;
		//mysql_query ($cmd_table);
		//$cmd_table = "CREATE TABLE " . $tableEnCours . " ENGINE=MyISAM (".$selectTempo.")";
		$cmd_table = "CREATE TEMPORARY TABLE " . $tableEnCours . " ENGINE=MyISAM (".$selectTempo.")";
		
		mysql_query ($cmd_table);
		$cmd_table = "ALTER TABLE " . $tableEnCours . " PRIMARY KEY " . $nomColonneIndex;
		mysql_query ($cmd_table);	

		//r�cup�ration de la description du tri
		$result = $this->dSort->recupTriParId($idTri);

		$trier_par = explode(",",$result['tri_par']);

		//parcours des champs sur lesquels trier		
		for ($j = 0; $j < count($trier_par); $j++) {
			//d�coupage du champ (ex : c_num_2 (croissance ou d�croissance (c ou d),
			//type de champ (num,text,...) et id du champ)
			$temp = explode("_", $trier_par[$j]);

			//on parcours tous les champs de tri possible
			for ($i = 0; $i < count($fields); $i++) {

				//afin de trouver ceux sur lesquels le tri s'applique
				if ($fields[$i]["ID"] == $temp[2]) {
					//on est sur un champ de tri

					//suivant le type de champ
					switch ($fields[$i]["TYPEFIELD"]) {
						case "internal":
							//c'est un champ de la requete de base
							
							//on verifie que le champ est dans la table temporaire
							$requete_fields = mysql_query("SELECT * FROM " . $tableEnCours . " LIMIT 1");
							$x = 0;
							while ($x < mysql_num_fields($requete_fields)) {
								$ligne = mysql_fetch_field($requete_fields, $x);
								if ($ligne->name == $fields[$i]["TABLEFIELD"][0][value]) {
									//le champ est la donc on ajoute le champ au order
									$orderby .= $this->ajoutOrder($fields[$i]["TABLEFIELD"][0][value],$temp[0]) . ",";
									$x = mysql_num_fields($requete_fields);
								}
								$x++;
							}
							mysql_free_result($requete_fields);
							break;

						case "select":
							//une requete union est n�c�ssaire
							
							//le nom du champ on ajoute tb pour corriger le probleme des noms numeriques
							$nomChamp = "tb".$fields[$i]["NAME"];
							
							//on ajoute la colonne au orderby
							$orderby .= $this->ajoutOrder($nomChamp,$temp[0]) . ",";
													
							//on ajoute la colonne � la table temporaire
							$this->ajoutColonneTableTempo($tableEnCours, $nomChamp, $temp[1]);

							
							//on parcours la ou les tables pour generer les updates
							for ($x = 0; $x < count($fields[$i]["TABLE"]); $x++) {
								
								$requete = $this->genereRequeteUpdate($fields[$i]["TABLE"][$x], $tableEnCours, $nomChamp, $nomColonneIndex);
								
								//echo("updateSort:".$requete."<br />");
								mysql_query ($requete);
							}
							
							//on a aussi des champs persos maitenant...
							if($fields[$i]['SOURCE'] == "cp"){
								$requete = $this->generateRequeteCPUpdate($fields[$i], $tableEnCours, $nomChamp);
								mysql_query ($requete);
							}
							
							break;

					} //switch
				} //if ($fields[$i]["ID"] == $temp[2]) {
			} //for ($i = 0; $i < count($fields); $i++) {
		} //for ($j = 0; $j < count($trier_par); $j++) {
		
		
		if ($orderby!="") {

			//on enleve la derniere virgule
			$orderby = substr($orderby, 0, strlen($orderby) - 1);

			//on va classer la table tempo suivant les criteres donn�s
			$requete = "ALTER TABLE " . $tableEnCours ." ORDER BY ". $orderby;
			mysql_query ($requete);
		}

		//on retourne la requete sur la table de tri
    	if ($this->table_select!="") {
    		//c'est une requete avec des informations ext�rieures
    		$requete = "SELECT " . $nomColonneIndex . "," . $this->champs_select;
    		$requete .= " FROM " . $this->table_tri_tempo . "," . $this->table_select;
    		$requete .= " WHERE " . $this->table_select . "." . $this->table_primary_key_select;
    		$requete .= "=" . $this->table_tri_tempo . "." . $nomColonneIndex;
    		$requete .= " GROUP BY " . $nomColonneIndex;	
    		if ($orderby!="") $requete .= " ORDER BY " . $orderby;
    		if ($nbLimit>0) $requete .= " LIMIT " . $debLimit . "," . $nbLimit;
    	} else {
			if ($nbLimit>0) {
	    		//requete de base sur la table tri�e avec limit
				$requete = "SELECT * FROM " . $tableEnCours . " LIMIT " . $debLimit . "," . $nbLimit; 
			} else {
	    		//requete de base sur la table tri�e
				$requete = "SELECT " . $nomColonneIndex . " FROM " . $tableEnCours;
			}
    	}
 		return $requete;

	}
	
	function appliquer_tri_from_tmp_table($idTri=0, $table, $nomColonneIndex,$start=0,$numbers=0){
		//r�cuperations des champs
		$fields = $this->params["FIELD"];
		$this->table_tri_tempo = $table;
		
		//r�cup�ration de la description du tri
		$result = $this->dSort->recupTriParId($idTri);

		$trier_par = explode(",",$result['tri_par']);
		$do=false;
		//parcours des champs sur lesquels trier
		for ($j = 0; $j < count($trier_par); $j++) {
			//d�coupage du champ (ex : c_num_2 (croissance ou d�croissance (c ou d),
			//type de champ (num,text,...) et id du champ)
			$temp = explode("_", $trier_par[$j]);
			//on parcours tous les champs de tri possible
			for ($i = 0; $i < count($fields); $i++) {
				
				//afin de trouver ceux sur lesquels le tri s'applique
				if ($fields[$i]["ID"] == $temp[2]) {
					//on est sur un champ de tri
					//suivant le type de champ
					switch ($fields[$i]["TYPEFIELD"]) {
						case "internal":
							//c'est un champ de la requete de base
							$nomChamp = $fields[$i]["TABLEFIELD"][0][value];
						
							//on verifie que le champ est dans la table temporaire
							$requete_fields = mysql_query("SELECT * FROM " . $this->table_tri_tempo . " LIMIT 1");
							$x = 0;
							if ($requete_fields) {
								while ($x < mysql_num_fields($requete_fields)) {
									$ligne = mysql_fetch_field($requete_fields, $x);
									if ($ligne->name == $nomChamp) {
										//le champ est la donc on ajoute le champ au order
										if($orderby!="") $orderby.=",";
										$orderby .= $this->ajoutOrder($nomChamp,$temp[0]);
										$x = mysql_num_fields($requete_fields);
									}
									$x++;
								}
								mysql_free_result($requete_fields);
							}
							break;
						case "select":
							//une requete union est n�c�ssaire
							
							//le nom du champ on ajoute tb pour corriger le probleme des noms numeriques
							$nomChamp = "tb".$fields[$i]["NAME"];
							
							//on ajoute la colonne au orderby
							if($orderby!="") $orderby.=",";
							$orderby .= $this->ajoutOrder($nomChamp,$temp[0]);
													
							//on ajoute la colonne � la table temporaire
							$this->ajoutColonneTableTempo($this->table_tri_tempo, $nomChamp, $temp[1]);

							//on parcours la ou les tables pour generer les updates
							for ($x = 0; $x < count($fields[$i]["TABLE"]); $x++) {
								$requete = $this->genereRequeteUpdate($fields[$i]["TABLE"][$x], $this->table_tri_tempo, $nomChamp, $nomColonneIndex);
								mysql_query ($requete);
							}
							
							//on a aussi des champs persos maitenant...
							if($fields[$i]['SOURCE'] == "cp"){
								$requete = $this->generateRequeteCPUpdate($fields[$i], $this->table_tri_tempo, $nomChamp);
								mysql_query ($requete);
							}
							break;

					} //switch
					// si le champ n'est pas num�rique, ca peut vite devenir long...
					if($temp[1] == "num" && $numbers >0){
						$this->delete_useless($nomChamp, $orderby,($start+$numbers));
					}
				
				} //if ($fields[$i]["ID"] == $temp[2]) {
			} //for ($i = 0; $i < count($fields); $i++) {
		} //for ($j = 0; $j < count($trier_par); $j++) {
		
		//on retourne la requete sur la table de tri
    	if ($this->table_select!="") {
    		//c'est une requete avec des informations ext�rieures
    		$requete = "SELECT " . $nomColonneIndex . "," . $this->champs_select;
    		$requete .= " FROM " . $this->table_tri_tempo . "," . $this->table_select;
    		$requete .= " WHERE " . $this->table_select . "." . $this->table_primary_key_select;
    		$requete .= "=" . $this->table_tri_tempo . "." . $nomColonneIndex;
    		$requete .= " GROUP BY " . $nomColonneIndex;	
    	} else {
	    	//requete de base sur la table tri�e
			$requete = "SELECT " . $nomColonneIndex . " FROM " . $this->table_tri_tempo;
    	}
    	if ($orderby!="") $requete .= " ORDER BY " . $orderby;
    	if($numbers>0){
    		$requete.=" limit $start,".$numbers;
    	}

 		return $requete;
	}
	
	function get_order_by($idTri){
		$orderby="";
		$fields = $this->params['FIELD'];
		$result = $this->dSort->recupTriParId($idTri);
		$trier_par = explode(",",$result['tri_par']);
		for ($j = 0; $j < count($trier_par); $j++) {
			$temp = explode("_", $trier_par[$j]);
			//on parcours tous les champs de tri possible
			for ($i = 0; $i < count($fields); $i++) {
				if ($fields[$i]["ID"] == $temp[2]) {
					switch ($fields[$i]["TYPEFIELD"]) {
						case "internal":
							$nomChamp = $fields[$i]["TABLEFIELD"][0][value];
							if($orderby!="")$orderby .=",";
							$orderby .= $this->ajoutOrder($nomChamp,$temp[0]);
							break;
						case "select":
							$nomChamp = "tb".$fields[$i]["NAME"];
							if($orderby!="")$orderby .=",";
							$orderby .= $this->ajoutOrder($nomChamp,$temp[0]);
							break;
					} //switch
				}
			}
		}
		return $orderby;
	}
	
	function delete_useless($nomCol,$orderby,$need){
		$query = "select ".$nomCol." as crit,count(*) as nb_elem from ".$this->table_tri_tempo." group by crit order by $orderby";
		$res = mysql_query($query);
		$keep = array();
		$nb_elem= 0;
		if(mysql_num_rows($res)){
			while($row = mysql_fetch_object($res)){
				$nb_elem+=($row->nb_elem);
				$keep[]=$row->crit;
				if($nb_elem>$need){
					$clean = "delete from ".$this->table_tri_tempo." where $nomCol not in ('".implode("','",$keep)."')"; 
					mysql_query($clean);
					break;
				}
			}
		}
	}
	
	/**
	 * Ajoute une colonne � la table temporaire du nom et du type pr�cis�
	 */
	function ajoutColonneTableTempo($nomTable, $nomCol,$type) {
		
		//d'abord on ajoute la colonne
		$cmd_table = "ALTER TABLE " . $nomTable . " ADD " . $nomCol . " ";
		
		//en fonction du type on met le type mysql
		switch($type) {
			case "num":
				$cmd_table .= "integer";
				break;
			case "text":
			default:
				$cmd_table .= "text";
				break;
		}
		
		//execution de l'ajout de la colonne
		mysql_query ($cmd_table);
//		//ajout d'un index;
//		$add_index = "alter table ".$nomTable." add index (".$nomCol.")";
//		mysql_query($add_index);
		
	}
	
	/**
	 * Renvoi le nom du champ et l'ordre de tri SQL
	 */
	function ajoutOrder($nomChp,$typeorder) {
		
		$tmpTxt = $nomChp;
		
		//suivant le type de tri
		switch ($typeorder) {
			case "c":
				$tmpTxt .= " ASC";
				break;
			case "d":
			default:
				$tmpTxt .= " DESC";
				break;
		}
		
		
		return $tmpTxt;
	}
	
	/**
	 * Genere la requete select d'un element table
	 */
	function genereRequeteUpdate($desTable, $nomTable, $nomChp, $nomColonneTempo) {

		$tables = $nomTable . "," .$this->params["REFERENCE"];
		$groupby = "";
		
		//
		//SELECT de base pour la r�cup�ration des informations
		//
		$extractinfo_sql = "SELECT ".$this->params["REFERENCE"].'.'.$this->params["REFERENCEKEY"].", ".$this->ajoutIfNull($desTable["TABLEFIELD"][0])." AS ".$nomChp." FROM ".$nomTable.' LEFT JOIN '.$this->params["REFERENCE"].' ON ('.$this->params["REFERENCE"].'.'.$this->params["REFERENCEKEY"].' = '.$nomTable.'.'.$this->params["REFERENCEKEY"].')';
		
		//
		//On ajout les �ventuelles liaisons
		//
		for ($x = 0; $x <= count($desTable["LINK"]); $x++) {
			if($desTable["LINK"][$x]["TABLE"][0]['ALIAS']){
				$alias = $desTable["LINK"][$x]["TABLE"][0]['ALIAS'];
			}else{
				$alias =$desTable["LINK"][$x]["TABLE"][0]['value'];
			}
			switch ($desTable["LINK"][$x]["TYPE"]) {
				case "n1" :
					if ($desTable["LINK"][$x]["TABLEKEY"][0]['value']) {
						$extractinfo_sql .= " LEFT JOIN " . $desTable["LINK"][$x]["TABLE"][0]['value'].($desTable["LINK"][$x]["TABLE"][0]['value'] != $alias  ? " AS ".$alias : "");
						$extractinfo_sql .= " ON " . $desTable["NAME"] . "." . $desTable["LINK"][$x]["EXTERNALFIELD"][0]['value'];
						$extractinfo_sql .= "=" . $alias . "." . $desTable["LINK"][$x]["TABLEKEY"][0]['value'];
					} else {
						$extractinfo_sql .= " LEFT JOIN " . $desTable["NAME"];
						$extractinfo_sql .= " ON " . $this->params["REFERENCE"] . "." . $this->params["REFERENCEKEY"];
						$extractinfo_sql .= "=" . $desTable["NAME"] . "." . $desTable["LINK"][$x]["EXTERNALFIELD"][0]['value'];
					}
					break;
				case "1n" :
					$extractinfo_sql .= " LEFT JOIN " . $desTable["NAME"];
					$extractinfo_sql .= " ON (" . $desTable["NAME"] . "." . $desTable["TABLEKEY"][0]['value'];
					$extractinfo_sql .= "=" . $this->params["REFERENCE"] . "." . $desTable["LINK"][$x]["REFERENCEFIELD"][0]['value'] . ") ";
					break;
				case "nn" :
					$extractinfo_sql .= " LEFT JOIN " . $desTable["LINK"][$x]["TABLE"][0]['value'].($desTable["LINK"][$x]["TABLE"][0]['value'] != $alias  ? " AS ".$alias : "");
					$extractinfo_sql .= " ON (" . $nomTable . "." . $this->params["REFERENCEKEY"];
					$extractinfo_sql .= "=" . $alias . "." . $desTable["LINK"][$x]["REFERENCEFIELD"][0]['value'] . ") ";
					
					if ($desTable["LINK"][$x]["TABLEKEY"][0]['value']) {
						$extractinfo_sql .= " LEFT JOIN " . $desTable["NAME"];
						$extractinfo_sql .= " ON (" . $alias . "." . $desTable["LINK"][$x]["TABLEKEY"][0]['value'];
						$extractinfo_sql .= "=" . $desTable["NAME"] . "." . $desTable["LINK"][$x]["EXTERNALFIELD"][0]['value'] ." ".$desTable["LINK"][$x]["LINKRESTRICT"][0]['value']. ") ";
					} else {
						$extractinfo_sql .= " LEFT JOIN " . $desTable["NAME"];
						$extractinfo_sql .= " ON (" . $alias . "." . $desTable["LINK"][$x]["EXTERNALFIELD"][0]['value'];
						$extractinfo_sql .= "=" . $desTable["NAME"] . "." . $desTable["TABLEKEY"][0]['value'] . " ".$desTable["LINK"][$x]["LINKRESTRICT"][0]['value'].") ";
						
					}
					
					break;
			}
		}
		
		//si on a un filtre supplementaire
		if (isset($desTable["FILTER"])) {
			$extractinfo_sql .= " WHERE " . $desTable["FILTER"][0][value];	
		}
		
		//On applique la restriction ORDER BY
		//Utilis� pour les types de langues ou d'auteurs, ...
		if (isset($desTable["ORDERBY"])) {
			$extractinfo_sql .= " ORDER BY ".$this->ajoutIfNull($desTable["ORDERBY"][0]);		
		}
		
		//Si l'on a un group by on passe par une sous-requete pour que le groupement soit fait apr�s le tri (Cas des Auteurs : C'est l'auteur principal qui doit �tre utilis� pour le tri)
		if (isset($desTable["GROUPBY"])) {
			$extractinfo_sql = "SELECT * FROM (".$extractinfo_sql.") AS asubquery";
			$extractinfo_sql .= " GROUP BY ".$desTable["GROUPBY"][0]["value"];
		}

		//
		//On met le tout dans une table temporaire
		//
		$sql = "DROP TEMPORARY TABLE IF EXISTS ".$nomTable."_update";
		mysql_query($sql);
		$temporary2_sql = "CREATE TEMPORARY TABLE ".$nomTable."_update ENGINE=MyISAM (".$extractinfo_sql.")";
		
		mysql_query($temporary2_sql);
		mysql_query("alter table ".$nomTable."_update add index(notice_id)");

		//
		//Et on rempli la table tri_tempo avec les �l�ments de la table temporaire
		//
		$requete = "UPDATE " . $this->params["REFERENCE"].", ".$nomTable.", ".$nomTable."_update";
		$requete .= " SET " . $nomTable.".".$nomChp . " = " . $nomTable."_update.".$nomChp;
		
		//le lien vers la table de tri temporaire
		$requete .= " WHERE " . $nomTable.".".$this->params["REFERENCEKEY"];
		$requete .= "=" . $nomTable."_update.".$this->params["REFERENCEKEY"];
		$requete .= " AND ".$this->params["REFERENCE"].".".$this->params["REFERENCEKEY"]."=".$nomTable.".".$this->params["REFERENCEKEY"];
		$requete .= " AND ".$nomTable."_update.".$nomChp." IS NOT NULL";
		$requete .= " AND ".$nomTable."_update.".$nomChp." != ''";
		
		return $requete;
	}


	/**
	 * Ajoute le ifnull si pr�cis�
	 */
	function ajoutIfNull($tableau) {
		if (isset($tableau["NULLVALUE"])) {
			$tmpTxt = "IFNULL(" . $tableau[value] . ",'" . $tableau["NULLVALUE"] . "')"; 
		} else {
			$tmpTxt = $tableau[value];
		}
	
		return $tmpTxt;	
	}


	/**
	 * Parse les fichiers XML de parametres
	 * il y a un fichier par type de tris
	 */
	function parse() {
		global $include_path;
		global $$params_name;
		global $charset;
		global $dbh;
		$params_name = $this->dSort->sortName . "_params";
		$params = $$params_name;

		if ($params) {
			$this->params = $params;
		} else {
			$nomfichier = $include_path . "/sort/" . $this->dSort->sortName . "/sort.xml";

			if (file_exists($include_path . "/sort/" . $this->dSort->sortName . "/sort_subst.xml")) {
				$nomfichier=$include_path . "/sort/" . $this->dSort->sortName . "/sort_subst.xml";
				$fp = fopen($nomfichier, "r");
			} else if (file_exists($nomfichier)) {
				$fp = fopen($nomfichier, "r");
			}

			if ($fp) {
				//un fichier est ouvert donc on le lit
				$xml = fread($fp, filesize($nomfichier));
				//on le ferme
				fclose($fp);
				//on le parse pour le transformer en tableau
				$params = _parser_text_no_function_($xml, "SORT");
				//on le stocke dans la classe
				$this->params = $params;
			} else {
				$this->error = true;
				$this->error_message = "Can't open definition file";
			}
		}
				
		//tri perso
		$p_perso = new parametres_perso("notices");

		foreach($p_perso->t_fields as $key => $t_field){
			if($t_field['OPAC_SHOW'] && $t_field['OPAC_SORT']){
				$param=_parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$t_field['OPTIONS'], "OPTIONS");
				switch($t_field['TYPE']){
					case "comment" :
					case "text":
						if($param['REPETABLE'][0]['value']){
							$tablefield = "group_concat(".$p_perso->prefix."_custom_".$t_field['DATATYPE']." separator ' ')";
							$groupby = "group by notice_id";
						}else{
							$tablefield = $p_perso->prefix."_custom_".$t_field['DATATYPE'];
							$groupby = "";
						}
						$p_tri = array(
							'SOURCE' => "cp",
							'TYPEFIELD' => "select",
							'ID' => "cp".$key,
							'TYPE' => "text",
							'NAME' => $t_field['NAME'],
							'LABEL' => $t_field['TITRE'],
							'TABLEFIELD' => array('value'=>$tablefield),
							'REQ_SUITE' => "left join ".$p_perso->prefix."_custom_values on notices.notice_id = ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_origine where ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_champ = '".$key."' ".$groupby 
						);
						break;
					case "list":
						if($param['MULTIPLE'][0]['value']){
							$tablefield = "group_concat(".$p_perso->prefix."_custom_list_lib separator ' ')";
							$groupby = "group by notice_id";
						}else{
							$tablefield = $p_perso->prefix."_custom_list_lib";
							$groupby = "";
						}				
						$p_tri = array(
							'SOURCE' => "cp",
							'TYPEFIELD' => "select",
							'ID' => "cp".$key,
							'TYPE' => "text",
							'NAME' => $t_field['NAME'],
							'LABEL' => $t_field['TITRE'],
							'TABLEFIELD' => array('value'=>$tablefield),
							'REQ_SUITE' => "left join ".$p_perso->prefix."_custom_values on notices.notice_id = ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_origine 
left join ".$p_perso->prefix."_custom_lists on ".$p_perso->prefix."_custom_".$t_field['DATATYPE']." = ".$p_perso->prefix."_custom_list_value 
where ".$p_perso->prefix."_custom_lists.".$p_perso->prefix."_custom_champ ='".$key."' and ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_champ ='".$key."'  ".$groupby 
						);
						break;
					case "date_box" :
						$p_tri = array(
							'SOURCE' => "cp",
							'TYPEFIELD' => "select",
							'ID' => "cp".$key,
							'TYPE' => "text",
							'NAME' => $t_field['NAME'],
							'LABEL' => $t_field['TITRE'],
							'TABLEFIELD' => array('value'=>$p_perso->prefix."_custom_".$t_field['DATATYPE']),
							'REQ_SUITE' => "left join ".$p_perso->prefix."_custom_values on notices.notice_id = ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_origine where ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_champ = '".$key."'"
						);
						break;
					case "query_list" :
						$tableid = "";
						$tablefield = "";
						$tablename = "";
						if($param['MULTIPLE'][0]['value']){
							if($param['QUERY'][0]['value']){
								$res = mysql_query($param['QUERY'][0]['value'],$dbh);
								if ($res) {
									$tableid = mysql_field_name($res,0);
									$tablefield = "group_concat(".mysql_field_name($res,1)." separator ' ')";
									$tablename = mysql_field_table($res,0);
								}
							}
							$groupby = "group by notice_id";
						} else {
							if($param['QUERY'][0]['value']){
								$res = mysql_query($param['QUERY'][0]['value'],$dbh);
								if ($res) {
									$tableid = mysql_field_name($res,0);
									$tablefield = mysql_field_name($res,1);
									$tablename = mysql_field_table($res,0);	
								}
							}
							$groupby = "";	
						}
			
						$p_tri = array(
							'SOURCE' => "cp",
							'TYPEFIELD' => "select",
							'ID' => "cp".$key,
							'TYPE' => "text",
							'NAME' => $t_field['NAME'],
							'LABEL' => $t_field['TITRE'],
							'TABLEFIELD' => array('value'=>$tablefield),
							'REQ_SUITE' => "left join ".$p_perso->prefix."_custom_values on notices.notice_id = ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_origine
left join ".$tablename." on ".$p_perso->prefix."_custom_".$t_field['DATATYPE']." = ".$tableid."						 
where ".$p_perso->prefix."_custom_values.".$p_perso->prefix."_custom_champ ='".$key."' ".$groupby 
						);
					break;
					default : 
						$p_tri =array();
						break;
				}
				if($p_tri)$this->params['FIELD'][]=$p_tri;
			}
		}
	}
	
	function generateRequeteCPUpdate($field, $nomTable, $nomChp){
		$requete = "
			SELECT 
				".$this->params['REFERENCE'].'.'.$this->params['REFERENCEKEY'].", 
				".$this->ajoutIfNull($field['TABLEFIELD'])." AS ".$nomChp." 
			FROM ".$nomTable." LEFT JOIN ".$this->params['REFERENCE']." ON (".$this->params['REFERENCE'].".".$this->params['REFERENCEKEY']." = ".$nomTable.".".$this->params['REFERENCEKEY'].") 
				".$field['REQ_SUITE'];

		//On met le tout dans une table temporaire
		$sql = "DROP TEMPORARY TABLE IF EXISTS ".$nomTable."_update";
		mysql_query($sql);
		$temporary2_sql = "CREATE TEMPORARY TABLE ".$nomTable."_update ENGINE=MyISAM (".$requete.")";
		mysql_query($temporary2_sql);
		mysql_query("alter table ".$nomTable."_update add index(notice_id)");
	
		//
		//Et on rempli la table tri_tempo avec les �l�ments de la table temporaire
		//
		$requete = "UPDATE ".$nomTable.", ".$nomTable."_update";
		$requete .= " SET " . $nomTable.".".$nomChp . " = " . $nomTable."_update.".$nomChp;
		
		//le lien vers la table de tri temporaire
		$requete .= " WHERE " . $nomTable.".".$this->params["REFERENCEKEY"];
		$requete .= "=" . $nomTable."_update.".$this->params["REFERENCEKEY"];
		$requete .= " AND ".$nomTable."_update.".$nomChp." IS NOT NULL";
		$requete .= " AND ".$nomTable."_update.".$nomChp." != ''";

		return $requete;
	}
}
?>