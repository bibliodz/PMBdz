<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: author.inc.php,v 1.38 2013-12-27 09:27:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], "inc.php")) die("no access");

// gestion d'un �l�ment � ne pas afficher
if (!$no_display) $no_display=0;

// la variable $caller, pass�e par l'URL, contient le nom du form appelant
$base_url = "./select.php?what=auteur&caller=$caller&param1=$param1&param2=$param2&no_display=$no_display&bt_ajouter=$bt_ajouter&dyn=$dyn&callback=$callback&infield=$infield"
		."&max_field=".$max_field."&field_id=".$field_id."&field_name_id=".$field_name_id."&add_field=".$add_field;

// classe pour la gestion des auteurs
require_once("$class_path/author.class.php");
// contenu popup s�lection auteur
require("./selectors/templates/sel_author.tpl.php");

//Initialise toutes les variables en fonction du type d'autorit�s
$sel_all=$sel_pp=$see_coll=$see_con=" ";
	
switch($id_type_autorite){

	case 70 : 
		$sel_pp = "selected";
		$author_form = str_replace("!!titre_ajout!!",$msg[207],$author_form);
		$author_form = str_replace("!!display!!","display:none",$author_form);		
		$libelleReq = " AND author_type='70' ";
		$libelleBtn = $msg[207];
		$sel_header = str_replace("!!select_titre!!",$msg[214],$sel_header);
		$completion=' ';
	break;
	case 71 : 
		$sel_coll = "selected";		
		$author_form = str_replace("!!titre_ajout!!",$msg["aut_ajout_collectivite"],$author_form);
		$author_form = str_replace("!!display!!","display:inline",$author_form);	
		$libelleReq = " AND author_type='71' ";
		$libelleBtn = $msg["aut_ajout_collectivite"];
		$sel_header = str_replace("!!select_titre!!",$msg["aut_select_coll"],$sel_header);
		$completion='collectivite_name';
	break;
	case 72 : 
		$sel_con="selected";		
		$author_form = str_replace("!!titre_ajout!!",$msg["aut_ajout_congres"],$author_form);
		$author_form = str_replace("!!display!!","display:inline",$author_form);	
		$libelleReq = " AND author_type='72' ";
		$libelleBtn = $msg["aut_ajout_congres"];
		$sel_header = str_replace("!!select_titre!!",$msg["aut_select_congres"],$sel_header);
		$completion='congres_name';
	break;
	default : 
		$sel_all = "selected";		
		$author_form = str_replace("!!titre_ajout!!",$msg[207],$author_form);
		$author_form = str_replace("!!display!!","display:none",$author_form);	
		$libelleReq = "";
		$libelleBtn = $msg[207];
		$sel_header = str_replace("!!select_titre!!",$msg[214],$sel_header);
		$completion=' ';
	break; 		
}

//$type_autorite = $id_type_autorite;
$sel_search_form = str_replace("!!sel_pp!!",$sel_pp,$sel_search_form);
$author_form = str_replace("!!sel_pp!!",$sel_pp,$author_form);
$sel_search_form = str_replace("!!sel_coll!!",$sel_coll,$sel_search_form);
$author_form = str_replace("!!sel_coll!!",$sel_coll,$author_form);
$sel_search_form = str_replace("!!sel_con!!",$sel_con,$sel_search_form);
$author_form = str_replace("!!sel_con!!",$sel_con,$author_form);
$sel_search_form = str_replace("!!sel_all!!",$sel_all,$sel_search_form);
$author_form = str_replace("!!completion!!",$completion,$author_form);
// affichage du header
print $sel_header;

// traitement en entr�e des requ�tes utilisateur
if ($deb_rech) $f_user_input = $deb_rech ;

if($f_user_input=="" && $user_input=="") {
	$user_input='';
} else {
	// traitement de la saisie utilisateur
	if ($user_input) $f_user_input=$user_input;
	if (($f_user_input)&&(!$user_input)) $user_input=$f_user_input;
}

if($bt_ajouter == "no"){
	$bouton_ajouter="";
}else{
	$bouton_ajouter= "<input type='button' class='bouton_small' onclick=\"document.location='$base_url&action=add&deb_rech='+this.form.f_user_input.value+'&id_type_autorite=$id_type_autorite'\" value='$libelleBtn'>";
}

//Action
switch($action){
	case 'add':
		$author_form = str_replace("!!deb_saisie!!", htmlentities(stripslashes($f_user_input),ENT_QUOTES,$charset), $author_form);
		print $author_form;
		break;
	case 'update':
		$value['type']		=	$author_type;
		$value['name']		=	$author_name;
		$value['rejete']	=	$author_rejete;
		$value['date']		=	$date;
		$value['voir_id']	=	0;
		$value['lieu']		=	$lieu;
		$value['ville']		=	$ville;
		$value['pays']		=	$pays;
		$value['subdivision']=	$subdivision;
		$value['numero']	=	$numero;

		$auteur = new auteur();
		$auteur->update($value);
		$sel_search_form = str_replace("!!bouton_ajouter!!", $bouton_ajouter, $sel_search_form);
		$sel_search_form = str_replace("!!deb_rech!!", htmlentities(stripslashes($f_user_input),ENT_QUOTES,$charset), $sel_search_form);
		print $sel_search_form;
		print $jscript;
		show_results($dbh, $author_name, 0, 0,$auteur->id);
		break;
	default:
		$sel_search_form = str_replace("!!bouton_ajouter!!", $bouton_ajouter, $sel_search_form);
		$sel_search_form = str_replace("!!deb_rech!!", htmlentities(stripslashes($f_user_input),ENT_QUOTES,$charset), $sel_search_form);
		print $sel_search_form;
		print $jscript;
		show_results($dbh, $user_input, $nbr_lignes, $page, 0);
		break;
}

print $sel_footer;

// function d'affichage
function show_results($dbh, $user_input, $nbr_lignes=0, $page=0, $id = 0) {
	global $nb_per_page;
	global $base_url;
	global $caller;
	global $callback;
	global $class_path;
	global $no_display;
 	global $charset;
 	global $msg ;
 	global $libelleReq;
 	global $id_type_autorite;
 	 	
	if (!$id) { 	
		// on r�cup�re le nombre de lignes 
		if($user_input=="") {
			$requete = "SELECT COUNT(1) FROM authors where author_id!='$no_display' ".$libelleReq;
		} else {
			$aq=new analyse_query(stripslashes($user_input));
			if ($aq->error) {
				error_message($msg["searcher_syntax_error"],sprintf($msg["searcher_syntax_error_desc"],$aq->current_car,$aq->input_html,$aq->error_message));
				exit;
			}
			$requete=$aq->get_query_count("authors","concat(author_name,', ',author_rejete) ","index_author","author_id","author_id!='$no_display'");
			$requete.=$libelleReq;
		}
		$res = mysql_query($requete, $dbh);
		$nbr_lignes = @mysql_result($res, 0, 0);
	} else {
		$nbr_lignes=1;
	}
	if(!$page) $page=1;
	$debut =($page-1)*$nb_per_page;

	if($nbr_lignes) {
		// on lance la vraie requ�te
		if (!$id) {
			if($user_input=="") {
				$requete = "SELECT * FROM authors where author_id!='$no_display' $libelleReq ORDER BY author_name, author_rejete LIMIT $debut,$nb_per_page ";
			} else {
				$members=$aq->get_query_members("authors","concat(author_name,', ',author_rejete)","index_author","author_id");
				$requete="select *,".$members["select"]." as pert from authors where ".$members["where"]." and author_id!='$no_display' $libelleReq group by author_id order by pert desc,index_author limit $debut,$nb_per_page";	
			}
		} else {
			$requete="select * from authors where author_id='".$id."'".$libelleReq;
		}
		$res = @mysql_query($requete, $dbh);
		while(($author=mysql_fetch_object($res))) {
			$auteur = new auteur($author->author_id);
			$author_voir="" ;
			// gestion des voir :
			if($author->author_see) {
				$auteur_see = new auteur($author->author_see);
	
				$author_voir = "<a href='#' onclick=\"set_parent('$caller', '$author->author_see', '".htmlentities(addslashes($auteur_see->isbd_entry),ENT_QUOTES, $charset)."','$callback')\">".htmlentities($auteur_see->isbd_entry,ENT_QUOTES, $charset)."</a>";
				$author_voir = ".&nbsp;-&nbsp;<i>$msg[210]</i>&nbsp;:&nbsp;".$author_voir;
			}
  
			print "<div class='row'>";
			print pmb_bidi("<a href='#' onclick=\"set_parent('$caller', '$author->author_id', '".htmlentities(addslashes($auteur->isbd_entry),ENT_QUOTES, $charset)."','$callback')\">$auteur->isbd_entry</a>");
			print pmb_bidi($author_voir );
			print "</div>";

			}
		mysql_free_result($res);

		// constitution des liens
		$nbepages = ceil($nbr_lignes/$nb_per_page);
		$suivante = $page+1;
		$precedente = $page-1;

		// affichage du lien pr�c�dent si n�c�ssaire
		print "<div class='row'>&nbsp;<hr /></div><div align='center'>";
		$url_base = $base_url."&rech_regexp=$rech_regexp&user_input=".rawurlencode(stripslashes($user_input))."&id_type_autorite=".$id_type_autorite;
		$nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		print $nav_bar;
		print "</div>";
		}
	else {
		print $msg["no_author_found"];
	}
}

