<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexint.class.php,v 1.50 2014-01-23 13:51:54 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des 'indexations internes'
if ( ! defined( 'INDEXINT_CLASS' ) ) {
  define( 'INDEXINT_CLASS', 1 );

require_once($class_path."/notice.class.php");
require_once("$class_path/aut_link.class.php");
require_once("$class_path/aut_pperso.class.php");
require_once("$class_path/audit.class.php");

class indexint {

// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------
var $indexint_id=0;		// MySQL indexint_id in table 'indexint'
var	$name='';			// nom de l'indexation
var	$comment='';		// commentaire
var	$display='';		// name + comment
var $isbd_entry_lien_gestion ; // lien sur le nom vers la gestion
var $id_pclass='1';
var $name_pclass='';

// ---------------------------------------------------------------
//		indexint($id) : constructeur
// ---------------------------------------------------------------
function indexint($id=0,$id_pclass=1) {
	$this->id_pclass=$id_pclass;
	
	if($id) {
		$this->indexint_id = $id;

		$this->getData();
	} else {
		$this->indexint_id = 0;
		$this->getData();
	}
}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos 
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	
	if(!$this->indexint_id) {
		// pas d'identifiant. on retourne un tableau vide
		$this->indexint_id	=0;
		$this->name		='';
		$this->comment		='';
		$this->name_pclass	= 	'';
		//$this->id_pclass	= 	1;
	} else {
		$requete = "SELECT indexint_id,indexint_name,indexint_comment, num_pclass, id_pclass,name_pclass FROM indexint,pclassement 
		WHERE indexint_id='".$this->indexint_id."' and id_pclass = num_pclass " ;
		$result = mysql_query($requete, $dbh) or die ($requete."<br />".mysql_error());
		if(mysql_num_rows($result)) {
			$temp = mysql_fetch_object($result);
			$this->indexint_id	= $temp->indexint_id;
			$this->name			= $temp->indexint_name;
			$this->comment		= $temp->indexint_comment;
			$this->id_pclass	= $temp->id_pclass;
			$this->name_pclass	= $temp->name_pclass;
			if ($this->comment) $this->display = $this->name." ($this->comment)" ;
				else $this->display = $this->name ;
			// Ajoute un lien sur la fiche autorit� si l'utilisateur � acc�s aux autorit�s
			if (SESSrights & AUTORITES_AUTH) $this->isbd_entry_lien_gestion = "<a href='./autorites.php?categ=indexint&sub=indexint_form&id=".$this->indexint_id."&id_pclass=".$this->id_pclass."' class='lien_gestion'>".$this->display."</a>";
				else $this->isbd_entry_lien_gestion = $this->display;
		} else {
			// pas de titre avec cette cl�
			$this->indexint_id	=	0;
			$this->name			=	'';
			$this->comment		=	'';
			$this->name_pclass	= 	'';
			$this->id_pclass	= 	1;
		}		
	}
}

// ---------------------------------------------------------------
//		show_form : affichage du formulaire de saisie
// ---------------------------------------------------------------
function show_form() {

	global $msg;
	global $charset;
	global $indexint_form;
	global $exact;
	global $pmb_type_audit;
	
	if($this->indexint_id) {
		$action = "./autorites.php?categ=indexint&sub=update&id=".$this->indexint_id."&id_pclass=".$this->id_pclass;
		$libelle = $msg[indexint_update];
		$button_remplace = "<input type='button' class='bouton' value='$msg[158]' ";
		$button_remplace .= "onclick='unload_off();document.location=\"./autorites.php?categ=indexint&sub=replace&id=".$this->indexint_id."&id_pclass=".$this->id_pclass."\"'>";
		
		$button_voir = "<input type='button' class='bouton' value='$msg[voir_notices_assoc]' ";
		$button_voir .= "onclick='unload_off();document.location=\"./catalog.php?categ=search&mode=1&etat=aut_search&aut_type=indexint&aut_id=".$this->indexint_id."\"'>";
		
		$button_delete = "<input type='button' class='bouton' value='$msg[63]' ";
		$button_delete .= "onClick=\"confirm_delete();\">";
	} else {
		$action = './autorites.php?categ=indexint&sub=update&id=&id_pclass='.$this->id_pclass;
		$libelle = $msg[indexint_create];
		$button_remplace = '';
		$button_delete ='';
	}
	$aut_link= new aut_link(AUT_TABLE_INDEXINT,$this->indexint_id);
	$indexint_form = str_replace('<!-- aut_link -->', $aut_link->get_form('saisie_indexint') , $indexint_form);
	
	$aut_pperso= new aut_pperso("indexint",$this->indexint_id);
	$indexint_form = str_replace('!!aut_pperso!!',	$aut_pperso->get_form(), $indexint_form);
	
	$indexint_form = str_replace('!!id_pclass!!', $this->id_pclass, $indexint_form);
	$indexint_form = str_replace('!!id!!', $this->indexint_id, $indexint_form);
	$indexint_form = str_replace('!!libelle!!', $libelle, $indexint_form);
	$indexint_form = str_replace('!!action!!', $action, $indexint_form);
	$indexint_form = str_replace('!!id!!', $this->s_id, $indexint_form);
	$indexint_form = str_replace('!!indexint_nom!!', htmlentities($this->name,ENT_QUOTES,$charset), $indexint_form);
	$indexint_form = str_replace('!!indexint_comment!!', htmlentities($this->comment,ENT_QUOTES,$charset), $indexint_form);
	$indexint_form = str_replace('!!remplace!!', $button_remplace,  $indexint_form);
	$indexint_form = str_replace('!!voir_notices!!', $button_voir,  $indexint_form);
	$indexint_form = str_replace('!!delete!!', $button_delete,  $indexint_form);
	// pour retour � la bonne page en gestion d'autorit�s
	// &user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page
	global $user_input, $nbr_lignes, $page, $axact ;
	$indexint_form = str_replace('!!user_input_url!!',		rawurlencode(stripslashes($user_input)),			$indexint_form);
	$indexint_form = str_replace('!!user_input!!',			htmlentities($user_input,ENT_QUOTES, $charset),		$indexint_form);
	$indexint_form = str_replace('!!exact!!',				htmlentities($exact,ENT_QUOTES, $charset),			$indexint_form);
	$indexint_form = str_replace('!!nbr_lignes!!',			$nbr_lignes,										$indexint_form);
	$indexint_form = str_replace('!!page!!',				$page,												$indexint_form);	
	if ($pmb_type_audit && $this->indexint_id)
		$bouton_audit= "&nbsp;<input class='bouton' type='button' onClick=\"openPopUp('./audit.php?type_obj=".AUDIT_INDEXINT."&object_id=".$this->indexint_id."', 'audit_popup', 700, 500, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" title=\"".$msg['audit_button']."\" value=\"".$msg['audit_button']."\" />&nbsp;";	
	$indexint_form = str_replace('!!audit_bt!!',				$bouton_audit,												$indexint_form);
	
	print $indexint_form;
}

// ---------------------------------------------------------------
//		replace_form : affichage du formulaire de remplacement
// ---------------------------------------------------------------
function replace_form() {
	global $indexint_replace;
	global $msg;
	global $include_path;
	global $charset ;
	global $dbh;
	
	if(!$this->indexint_id || !$this->name) {
		require_once("$include_path/user_error.inc.php");
		error_message($msg[indexint_replace], $msg[indexint_unable], 1, './autorites.php?categ=indexint&sub=&id=');
		return false;
	}

	$notin="$this->indexint_id";
	$liste_remplacantes="";
	$lenremplacee = strlen($this->name)-1 ;
	while ($lenremplacee>0) {
		$recherchee = substr($this->name,0,$lenremplacee) ;
		
		$requete = "SELECT indexint_id,indexint_name,indexint_comment FROM indexint WHERE num_pclass='".$this->id_pclass."' and indexint_name='".addslashes($recherchee)."' and indexint_id not in (".$notin.") order by indexint_name " ;
		$result = mysql_query($requete, $dbh) or die ($requete."<br />".mysql_error());
		while ($lue=mysql_fetch_object($result)) {
			$notin.=",".$lue->indexint_id;
			$liste_remplacantes.="<tr><td><a href='./autorites.php?categ=indexint&sub=replace&id=".$this->indexint_id."&n_indexint_id=".$lue->indexint_id."'>".htmlentities($lue->indexint_name,ENT_QUOTES, $charset)."</a></td><td>".htmlentities($lue->indexint_comment,ENT_QUOTES, $charset)."</tr>";
			$trouvees=1 ;
		}
		if ($trouvees) $liste_remplacantes.="<tr><td>&nbsp;</td><td>&nbsp;</td></tr>" ;
		$trouvees = 0 ;
		$lenremplacee = $lenremplacee-1 ;
	} 
	if ($liste_remplacantes) $liste_remplacantes="<table>".$liste_remplacantes."</table>";

	$indexint_replace=str_replace('!!id!!', $this->indexint_id, $indexint_replace);
	$indexint_replace=str_replace('!!id_pclass!!', $this->id_pclass, $indexint_replace);
	$indexint_replace=str_replace('!!indexint_name!!', htmlentities($this->name,ENT_QUOTES, $charset), $indexint_replace);
	$indexint_replace=str_replace('!!liste_remplacantes!!', $liste_remplacantes, $indexint_replace);

	print $indexint_replace;
}


// ---------------------------------------------------------------
//		delete() : suppression 
// ---------------------------------------------------------------
function delete() {
	global $dbh;
	global $msg;
	
	if(!$this->indexint_id)
		// impossible d'acc�der � cette indexation
		return $msg[indexint_unable];

	// r�cup�ration du nombre de notices affect�es
	$requete = "SELECT COUNT(1) FROM notices WHERE ";
	$requete .= "indexint=".$this->indexint_id;
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = mysql_result($res, 0, 0);

	if(!$nbr_lignes) {
		// indexation non-utilis� dans les notices : Suppression OK
		// effacement dans la table des indexations internes
		$requete = "DELETE FROM indexint WHERE indexint_id=".$this->indexint_id;
		$result = mysql_query($requete, $dbh);
		// liens entre autorit�s
		$aut_link= new aut_link(AUT_TABLE_INDEXINT,$this->indexint_id);
		$aut_link->delete();
			
		$aut_pperso= new aut_pperso("indexint",$this->indexint_id);
		$aut_pperso->delete();
		audit::delete_audit(AUDIT_INDEXINT,$this->indexint_id);
		return false;
	} else {
		// Cette indexation est utilis�e dans des notices, impossible de la supprimer
		return '<strong>'.$this->name."</strong><br />${msg[indexint_used]}";
	}
}

// ---------------------------------------------------------------
//		replace($by) : remplacement 
// ---------------------------------------------------------------
function replace($by,$link_save) {

	global $msg;
	global $dbh;

	if(!$by) {
		// pas de valeur de remplacement !!!
		return "serious error occured, please contact admin...";
	}
	if (($this->indexint_id == $by) || (!$this->indexint_id))  {
		// impossible de remplacer une autorit� par elle-m�me
		return $msg[indexint_self];
	}
	
	$aut_link= new aut_link(AUT_TABLE_INDEXINT,$this->indexint_id);
	// "Conserver les liens entre autorit�s" est demand�
	if($link_save) {
		// liens entre autorit�s
		$aut_link->add_link_to(AUT_TABLE_INDEXINT,$by);		
	}
	$aut_link->delete();
	
	// a) remplacement dans les notices
	$requete = "UPDATE notices SET indexint=$by WHERE indexint='".$this->indexint_id."' ";
	$res = mysql_query($requete, $dbh);

	// b) suppression de l'indexation � remplacer
	$requete = "DELETE FROM indexint WHERE indexint_id=".$this->indexint_id;
	$res = mysql_query($requete, $dbh);
	
	audit::delete_audit(AUDIT_INDEXINT,$this->indexint_id);
	
	indexint::update_index($by);

	return FALSE;
}

// ---------------------------------------------------------------
//		update($value) : mise � jour de l'indexation
// ---------------------------------------------------------------
function update($nom, $comment,$id_pclass=0) {

	global $dbh;
	global $msg;
	global $include_path;
	global $thesaurus_classement_mode_pmb,$thesaurus_classement_defaut;
	
	if(!$nom)
		return false;

	// nettoyage de la cha�ne en entr�e
	$nom = clean_string($nom);
	if ($thesaurus_classement_mode_pmb == 0 || $id_pclass==0) {
		$id_pclass=$thesaurus_classement_defaut;
	}
	$requete = "SET indexint_name='$nom', ";
	$requete .= "indexint_comment='$comment', ";
	$requete .= "num_pclass='$id_pclass', ";
	$requete .= "index_indexint=' ".strip_empty_words($nom." ".$comment)." '";

	if($this->indexint_id) {
		// update
		$requete = 'UPDATE indexint '.$requete;
		$requete .= ' WHERE indexint_id='.$this->indexint_id.' LIMIT 1;';
		if(mysql_query($requete, $dbh)) {
			$aut_link= new aut_link(AUT_TABLE_INDEXINT,$this->indexint_id);
			$aut_link->save_form();
			$aut_pperso= new aut_pperso("indexint",$this->indexint_id);
			$aut_pperso->save_form();
			indexint::update_index($this->indexint_id);
			audit::insert_modif(AUDIT_INDEXINT,$this->indexint_id);
			return TRUE;
		}else {
			require_once("$include_path/user_error.inc.php");
			warning($msg[indexint_update], $msg[indexint_unable]);
			return FALSE;
		}
	} else {
		// cr�ation : s'assurer que le nom n'existe pas d�j�
		$dummy = "SELECT * FROM indexint WHERE indexint_name = '".$nom."' and num_pclass='".$id_pclass."' LIMIT 1 ";
		$check = mysql_query($dummy, $dbh);
		if(mysql_num_rows($check)) {
			require_once("$include_path/user_error.inc.php");
			warning($msg[indexint_create], $msg[indexint_exists]);
			return FALSE;
		}
		$requete = 'INSERT INTO indexint '.$requete.';';
		if(mysql_query($requete, $dbh)) {
			$this->indexint_id=mysql_insert_id();
			$aut_link= new aut_link(AUT_TABLE_INDEXINT,$this->indexint_id);
			$aut_link->save_form();
			$aut_pperso= new aut_pperso("indexint",$this->indexint_id);
			$aut_pperso->save_form();
			audit::insert_creation(AUDIT_INDEXINT,$this->indexint_id);
			return TRUE;
		}
		else {
			require_once("$include_path/user_error.inc.php");
			warning($msg[indexint_create], $msg[indexint_unable_create]);
			return FALSE;
		}
	}
}

// ---------------------------------------------------------------
//		import() : import d'une indexation
// ---------------------------------------------------------------
// fonction d'import de notice : indexation interne : INUTILISEE � la date du 12/02/04
function import($name,$comment="",$id_pclassement="") {

	global $dbh;
	global $pmb_limitation_dewey ;
	global $thesaurus_classement_defaut;
	
	// check sur la variable pass�e en param�tre
	if (!$name) return 0;

	if ($pmb_limitation_dewey<0) return 0;

	if ($pmb_limitation_dewey) $name=substr($name,0,$pmb_limitation_dewey) ;
	 
	// tentative de r�cup�rer l'id associ�e dans la base (implique que l'autorit� existe)
	// pr�paration de la requ�te
	$key = addslashes($name);
	$comment = addslashes($comment);
	if (!$id_pclassement) {
		 $num_pclass=$thesaurus_classement_defaut;
	} else {
		$num_pclass=$id_pclassement;
	}
	
	//On regarde si le plan de classement existe
	$query = "SELECT name_pclass FROM pclassement WHERE id_pclass='".addslashes($num_pclass)."' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT pclassement ".$query);
	if(!mysql_num_rows($result)){//Le plan de classement demand� n'existe pas
		return 0;// -> pas d'import
	}
	
	$query = "SELECT indexint_id FROM indexint WHERE indexint_name='".rtrim(substr($key,0,255))."' and num_pclass='$num_pclass' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT indexint ".$query);
	// r�sultat

	// r�cup�ration du r�sultat de la recherche
	$tindexint = mysql_fetch_object($result);
	
	// du r�sultat et r�cup�ration �ventuelle de l'id
	if ($tindexint->indexint_id) return $tindexint->indexint_id;

	// id non-r�cup�r�e >> cr�ation
	if (!$id_pclassement) {
		 $num_pclass=$thesaurus_classement_defaut;
	} else {
		$num_pclass=$id_pclassement;
	}
	$query = "INSERT INTO indexint SET indexint_name='$key', indexint_comment='$comment', index_indexint=' ".strip_empty_words($key." ".$comment)." ', num_pclass=$num_pclass ";

	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into indexint ".$query);
	$id=mysql_insert_id($dbh);
	audit::insert_creation(AUDIT_INDEXINT,$id);
	return $id;
}

// ---------------------------------------------------------------
//		search_form() : affichage du form de recherche
// ---------------------------------------------------------------
static function search_form($id_pclass=0) {
	global $user_query, $user_input;
	global $msg;
	global $dbh;
	global $thesaurus_classement_mode_pmb;
	global $charset ;

	// Gestion Indexation d�cimale multiple
	if ($thesaurus_classement_mode_pmb != 0) { //la liste des pclassement n'est pas affich�e en mode monopclassement
		$base_url = "./autorites.php?categ=indexint&sub=&id=";
		$sel_pclassement = '';
		$requete = "SELECT id_pclass, name_pclass,	typedoc FROM pclassement order by id_pclass" ;
		$result = mysql_query($requete, $dbh) or die ($requete."<br />".mysql_error());
		
		$sel_pclassement = "<select class='saisie-30em' id='id_pclass' name='id_pclass' ";
		$sel_pclassement.= "onchange = \"document.location = '".$base_url."&id_pclass='+document.getElementById('id_pclass').value; \">" ;
		$sel_pclassement.= "<option value='0' "; ;
		
		if ($id_pclass==0) $sel_pclassement.= " selected";
		$sel_pclassement.= ">".htmlentities($msg["pclassement_select_index_standart"],ENT_QUOTES, $charset)."</option>";
		while ($lue=mysql_fetch_object($result)) {
			$sel_pclassement.= "<option value='".$lue->id_pclass."' "; ;
			if ($lue->id_pclass == $id_pclass) $sel_pclassement.= " selected";
			$sel_pclassement.= ">".htmlentities($lue->name_pclass,ENT_QUOTES, $charset)."</option>";
		}	
		$sel_pclassement.= "</select>&nbsp;";
		$pclass_url="&id_pclass=".$id_pclass;
		$user_query = str_replace ('<!-- sel_pclassement -->', $sel_pclassement , $user_query);
		$user_query = str_replace ('<!-- lien_classement -->', "<a href='./autorites.php?categ=indexint&sub=pclass'>".$msg['pclassement_link_edition']."</a> ", $user_query);
	
	}	
	$user_query = str_replace ('!!user_query_title!!', $msg[357]." : ".$msg[indexint_menu_title] , $user_query);
	$user_query = str_replace ('!!action!!', './autorites.php?categ=indexint&sub=reach&id=', $user_query);
	$user_query = str_replace ('!!add_auth_msg!!', $msg["indexint_create_button"] , $user_query);
	$user_query = str_replace ('!!add_auth_act!!', './autorites.php?categ=indexint&sub=indexint_form'.$pclass_url, $user_query);
	$user_query = str_replace ('<!-- lien_derniers -->', "<a href='./autorites.php?categ=indexint&sub=indexint_last$pclass_url'>$msg[indexint_last]</a>", $user_query);
	$user_query = str_replace("!!user_input!!",htmlentities(stripslashes($user_input),ENT_QUOTES, $charset),$user_query);
	
	print pmb_bidi($user_query) ;
}

function has_notices() {
	global $dbh;
	$query = "select count(1) from notices where indexint=".$this->indexint_id;
	$result = mysql_query($query, $dbh);
	return (@mysql_result($result, 0, 0));
}

//---------------------------------------------------------------
// update_index($id) : maj des n-uplets la table notice_global_index en rapport avec cet indexint
//---------------------------------------------------------------
function update_index($id) {
	global $dbh;
	// On cherche tous les n-uplet de la table notice correspondant � cet auteur.
	$found = mysql_query("select distinct notice_id from notices where indexint='".$id."'",$dbh);
	// Pour chaque n-uplet trouv�s on met a jour la table notice_global_index avec l'auteur modifi� :
	while($mesNotices = mysql_fetch_object($found)) {
		$notice_id = $mesNotices->notice_id;
		notice::majNoticesGlobalIndex($notice_id);
		notice::majNoticesMotsGlobalIndex($notice_id,'indexint');
	}
}
} # fin de d�finition de la classe indexint

} # fin de d�laration

