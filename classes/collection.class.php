<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collection.class.php,v 1.44 2014-01-23 13:51:54 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des collections

if ( ! defined( 'COLLECTION_CLASS' ) ) {
  define( 'COLLECTION_CLASS', 1 );

require_once($class_path."/notice.class.php");
require_once("$class_path/aut_link.class.php");
require_once("$class_path/aut_pperso.class.php");
require_once($class_path."/editor.class.php");
require_once($class_path."/subcollection.class.php");
require_once("$class_path/audit.class.php");

class collection {

// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------

var $id;		// MySQL id in table 'collections'
var $name;		// collection name
var $parent;	// MySQL id of parent publisher
var $editeur;	// name of parent publisher
var $editor_isbd; // isbd form of publisher
var $display;	// usable form for displaying	( _name_ (_editeur_) )
var $isbd_entry = ''; // isbd form
var $issn;		// ISSN of collection
var $isbd_entry_lien_gestion ; // lien sur le nom vers la gestion
var $collection_web;		// web de collection
var $collection_web_link;	// lien web de collection
// ---------------------------------------------------------------
//		collection($id) : constructeur
// ---------------------------------------------------------------
function collection($id=0) {
	if($id) {
		// on cherche � atteindre une notice existante
		$this->id = $id;
		$this->getData();
	} else {
		// la notice n'existe pas
		$this->id = 0;
		$this->getData();
	}
}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos collection
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	if(!$this->id) {
		// pas d'identifiant. on retourne un tableau vide
		$this->id		= 0;
		$this->name		=	'';
		$this->parent	=	0;
		$this->editeur	=	'';
		$this->editor_isbd = '';
		$this->display	=	'';
		$this->issn		=	'';
		$this->collection_web	= '';
		$this->comment	= '';
	} else {
		$requete = "SELECT * FROM collections WHERE collection_id=$this->id LIMIT 1 ";
		$result = @mysql_query($requete, $dbh);
		if(mysql_num_rows($result)) {
			$temp = mysql_fetch_object($result);
			mysql_free_result($result);
			$this->id = $temp->collection_id;
			$this->name = $temp->collection_name;
			$this->parent = $temp->collection_parent;
			$this->issn = $temp->collection_issn;
			$this->collection_web	= $temp->collection_web;
			$this->comment	= $temp->collection_comment;
			if($temp->collection_web) 
				$this->collection_web_link = " <a href='$temp->collection_web' target=_blank><img src='./images/globe.gif' border=0 /></a>";
			else 
				$this->collection_web_link = "" ;
			
			$editeur = new editeur($temp->collection_parent);
			$this->editor_isbd = $editeur->isbd_entry;
			$this->issn ? $this->isbd_entry = $this->name.', ISSN '.$this->issn : $this->isbd_entry = $this->name;
			$this->editeur = $editeur->name;
			$this->display = $this->name.' ('.$this->editeur.')';
			// Ajoute un lien sur la fiche collection si l'utilisateur � acc�s aux autorit�s
			if (SESSrights & AUTORITES_AUTH) 
				$this->isbd_entry_lien_gestion = "<a href='./autorites.php?categ=collections&sub=collection_form&id=".$this->id."' class='lien_gestion'>".$this->name."</a>";
			else 
				$this->isbd_entry_lien_gestion = $this->name;
		} else {
			// pas de collection avec cette cl�
			$this->id		=	0;
			$this->name		=	'';
			$this->parent	=	0;
			$this->editeur	=	'';
			$this->editor_isbd = '';
			$this->display	=	'';
			$this->issn		=	'';
			$this->collection_web = '';
			$this->collection_web_link = "" ;
			$this->comment = "" ;
		}
	}
}

// ---------------------------------------------------------------
//		delete() : suppression de la collection
// ---------------------------------------------------------------
function delete() {
	global $dbh;
	global $msg;

	if(!$this->id)
		// impossible d'acc�der � cette notice de collection
		return $msg[406];

	// r�cup�ration du nombre de notices affect�es
	$requete = "SELECT COUNT(1) FROM notices WHERE ";
	$requete .= "coll_id=$this->id";
	$res = mysql_query($requete, $dbh);
	$nbr_lignes = mysql_result($res, 0, 0);
	if(!$nbr_lignes) {
		// on regarde si la collection a des collections enfants 
		$requete = "SELECT COUNT(1) FROM sub_collections WHERE ";
		$requete .= "sub_coll_parent=".$this->id;
		$res = mysql_query($requete, $dbh);
		$nbr_lignes = mysql_result($res, 0, 0);
		if(!$nbr_lignes) {
			// effacement dans la table des collections
			$requete = "DELETE FROM collections WHERE collection_id=".$this->id;
			$result = mysql_query($requete, $dbh);
			//Import d'autorit�
			$this->delete_autority_sources($this->id);
			// liens entre autorit�s
			$aut_link= new aut_link(AUT_TABLE_COLLECTIONS,$this->id);
			$aut_link->delete();
			$aut_pperso= new aut_pperso("collection",$this->id);
			$aut_pperso->delete();
			
			audit::delete_audit(AUDIT_COLLECTION,$this->id);
			return false;
		} else {
			// Cet collection a des sous-collections, impossible de la supprimer
			return '<strong>'.$this->display."</strong><br />${msg[408]}";
		}
	} else {
		// Cette collection est utilis� dans des notices, impossible de la supprimer
		return '<strong>'.$this->display."</strong><br />${msg[407]}";
	}
}

// ---------------------------------------------------------------
//		delete_autority_sources($idcol=0) : Suppression des informations d'import d'autorit�
// ---------------------------------------------------------------
function delete_autority_sources($idcol=0){
	$tabl_id=array();
	if(!$idcol){
		$requete="SELECT DISTINCT num_authority FROM authorities_sources LEFT JOIN collections ON num_authority=collection_id  WHERE authority_type = 'collection' AND collection_id IS NULL";
		$res=mysql_query($requete);
		if(mysql_num_rows($res)){
			while ($ligne = mysql_fetch_object($res)) {
				$tabl_id[]=$ligne->num_authority;
			}
		}
	}else{
		$tabl_id[]=$idcol;
	}
	foreach ( $tabl_id as $value ) {
       //suppression dans la table de stockage des num�ros d'autorit�s...
		$query = "select id_authority_source from authorities_sources where num_authority = ".$value." and authority_type = 'collection'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while ($ligne = mysql_fetch_object($result)) {
				$query = "delete from notices_authorities_sources where num_authority_source = ".$ligne->id_authority_source;
				mysql_query($query);
			}
		}
		$query = "delete from authorities_sources where num_authority = ".$value." and authority_type = 'collection'";
		mysql_query($query);
	}
}

// ---------------------------------------------------------------
//		replace($by) : remplacement de la collection
// ---------------------------------------------------------------
function replace($by,$link_save=0) {

	global $msg;
	global $dbh;

	if(!$by) {
		// pas de valeur de remplacement !!!
		return "serious error occured, please contact admin...";
	}

	if (($this->id == $by) || (!$this->id))  {
		// impossible de remplacer une collection par elle-m�me
		return $msg[226];
	}
	// a) remplacement dans les notices
	// on obtient les infos de la nouvelle collection
	$n_collection = new collection($by);
	if(!$n_collection->parent) {
		// la nouvelle collection est foireuse
		return $msg[406];
	}
	
	$aut_link= new aut_link(AUT_TABLE_COLLECTIONS,$this->id);
	// "Conserver les liens entre autorit�s" est demand�
	if($link_save) {
		// liens entre autorit�s
		$aut_link->add_link_to(AUT_TABLE_COLLECTIONS,$by);		
	}
	$aut_link->delete();

	$requete = "UPDATE notices SET ed1_id=".$n_collection->parent.", coll_id=$by WHERE coll_id=".$this->id;
	$res = mysql_query($requete, $dbh);

	// b) remplacement dans la table des sous-collections
	$requete = "UPDATE sub_collections SET sub_coll_parent=$by WHERE sub_coll_parent=".$this->id;
	$res = mysql_query($requete, $dbh);

	// c) suppression de la collection
	$requete = "DELETE FROM collections WHERE collection_id=".$this->id;
	$res = mysql_query($requete, $dbh);
	
	//nettoyage d'autorities_sources
	$query = "select * from authorities_sources where num_authority = ".$this->id." and authority_type = 'collection'";
	$result = mysql_query($query);
	if(mysql_num_rows($result)){
		while($row = mysql_fetch_object($result)){
			if($row->authority_favorite == 1){
				//on suprime les r�f�rences si l'autorit� a �t� import�e...
				$query = "delete from notices_authorities_sources where num_authority_source = ".$row->id_authority_source;
				mysql_result($query);
				$query = "delete from authorities_sources where id_authority_source = ".$row->id_authority_source;
				mysql_result($query);
			}else{
				//on fait suivre le reste
				$query = "update authorities_sources set num_authority = ".$by." where num_authority_source = ".$row->id_authority_source;
				mysql_query($query);
			}
		}
	}
	audit::delete_audit (AUDIT_COLLECTION, $this->id);
	
	collection::update_index($by);
	return FALSE;

}

// ---------------------------------------------------------------
//		show_form : affichage du formulaire de saisie
// ---------------------------------------------------------------
function show_form() {

	global $msg;
	global $collection_form;
 	global $charset;
	global $pmb_type_audit;

	if($this->id) {
		$action = "./autorites.php?categ=collections&sub=update&id=$this->id";
		$libelle = $msg[168];
		$button_remplace = "<input type='button' class='bouton' value='$msg[158]' ";
		$button_remplace .= "onclick='unload_off();document.location=\"./autorites.php?categ=collections&sub=replace&id=$this->id\"'>";

		$button_voir = "<input type='button' class='bouton' value='$msg[voir_notices_assoc]' ";
		$button_voir .= "onclick='unload_off();document.location=\"./catalog.php?categ=search&mode=2&etat=aut_search&aut_type=collection&aut_id=$this->id\"'>";

		$button_delete = "<input type='button' class='bouton' value='$msg[63]' ";
		$button_delete .= "onClick=\"confirm_delete();\">";
	} else {
		$action = './autorites.php?categ=collections&sub=update&id=';
		$libelle = $msg[167];
		$button_remplace = '';
		$button_delete ='';
	}
	
	$aut_link= new aut_link(AUT_TABLE_COLLECTIONS,$this->id);
	$collection_form = str_replace('<!-- aut_link -->', $aut_link->get_form('saisie_collection') , $collection_form);
	
	$aut_pperso= new aut_pperso("collection",$this->id);		
	$collection_form = str_replace('!!aut_pperso!!',		$aut_pperso->get_form(),								$collection_form);
	
	$collection_form = str_replace('!!id!!', 					$this->id, 											$collection_form);
	$collection_form = str_replace('!!libelle!!', 				$libelle, 											$collection_form);
	$collection_form = str_replace('!!action!!', 				$action, $collection_form);
 	$collection_form = str_replace('!!collection_nom!!', 		htmlentities($this->name,ENT_QUOTES, $charset), 	$collection_form);
 	$collection_form = str_replace('!!ed_libelle!!', 			htmlentities($this->editeur,ENT_QUOTES, $charset), 	$collection_form);
	$collection_form = str_replace('!!ed_id!!', 				$this->parent, 										$collection_form);
	$collection_form = str_replace('!!issn!!', 					$this->issn, 										$collection_form);
	$collection_form = str_replace('!!delete!!', 				$button_delete, 									$collection_form);
	$collection_form = str_replace('!!remplace!!', 				$button_remplace, 									$collection_form);
	$collection_form = str_replace('!!voir_notices!!', 			$button_voir, 										$collection_form);
	$collection_form = str_replace('!!collection_web!!',		htmlentities($this->collection_web,ENT_QUOTES, $charset),	$collection_form);
	$collection_form = str_replace('!!comment!!',				htmlentities($this->comment,ENT_QUOTES, $charset),	$collection_form);
	// pour retour � la bonne page en gestion d'autorit�s
	// &user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page
	global $user_input, $nbr_lignes, $page ;
	$collection_form = str_replace('!!user_input_url!!',		rawurlencode(stripslashes($user_input)),			$collection_form);
	$collection_form = str_replace('!!user_input!!',			htmlentities($user_input,ENT_QUOTES, $charset),		$collection_form);
	$collection_form = str_replace('!!nbr_lignes!!',			$nbr_lignes,										$collection_form);
	$collection_form = str_replace('!!page!!',					$page,												$collection_form);		
	
	if ($pmb_type_audit && $this->id)
			$bouton_audit= "&nbsp;<input class='bouton' type='button' onClick=\"openPopUp('./audit.php?type_obj=".AUDIT_COLLECTION."&object_id=".$this->id."', 'audit_popup', 700, 500, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" title=\"".$msg['audit_button']."\" value=\"".$msg['audit_button']."\" />&nbsp;";
	
	$collection_form = str_replace('!!audit_bt!!',				$bouton_audit,												$collection_form);
	print $collection_form;
}

// ---------------------------------------------------------------
//		replace_form : affichage du formulaire de remplacement
// ---------------------------------------------------------------
function replace_form()	{
	global $collection_replace_form;
	global $msg;
	global $include_path;

	if(!$this->id || !$this->name) {
		require_once("$include_path/user_error.inc.php"); 
		error_message($msg[161], $msg[162], 1, './autorites.php?categ=collections&sub=&id=');
		return false;
	}

	$collection_replace_form=str_replace('!!id!!', $this->id, $collection_replace_form);
	$collection_replace_form=str_replace('!!coll_name!!', $this->name, $collection_replace_form);
	$collection_replace_form=str_replace('!!coll_editeur!!', $this->editeur, $collection_replace_form);
	print $collection_replace_form;
}


// ---------------------------------------------------------------
//		update($value) : mise � jour de la collection
// ---------------------------------------------------------------
function update($value,$force_creation = false) {
	global $dbh;
	global $msg,$charset;
	global $include_path;
	
	// nettoyage des valeurs en entr�e
	$value['name'] = clean_string($value['name']);
	$value['issn'] = clean_string($value['issn']);
	
	if(!$value['parent']){
		if($value['publisher']){
			//on les a, on cr�e l'�diteur
			$value['publisher']=stripslashes_array($value['publisher']);//La fonction d'import fait les addslashes contrairement � l'update
			$value['parent'] = editeur::import($value['publisher']);
		}
	}
	
	if ((!$value['name']) || (!$value['parent'])) 
		return false;
	
	// construction de la requ�te
	$requete = "SET collection_name='$value[name]', ";
	$requete .= "collection_parent='$value[parent]', ";
	$requete .= "collection_issn='$value[issn]', ";
	$requete .= "collection_web='$value[collection_web]', ";
	$requete .= "collection_comment='$value[comment]', ";
	$requete .= "index_coll=' ".strip_empty_words($value[name])." ".strip_empty_words($value["issn"])." '";

	if($this->id) {
		// update
		$requete = 'UPDATE collections '.$requete;
		$requete .= ' WHERE collection_id='.$this->id.' ;';
		if(mysql_query($requete, $dbh)) {
			$requete = "update notices set ed1_id='".$value[parent]."' WHERE coll_id='".$this->id."' ";
			$res = mysql_query($requete, $dbh) ;
			// liens entre autorit�s
			$aut_link= new aut_link(AUT_TABLE_COLLECTIONS,$this->id);
			$aut_link->save_form();			
			$aut_pperso= new aut_pperso("collection",$this->id);
			$aut_pperso->save_form();
			collection::update_index($this->id);
			
			audit::insert_modif (AUDIT_COLLECTION, $this->id) ;
		} else {
			require_once("$include_path/user_error.inc.php");
			warning($msg[167],htmlentities($msg[169]." -> ".$this->display,ENT_QUOTES, $charset));
			return FALSE;
		}
	} else {
		if(!$force_creation){
			// cr�ation : s'assurer que la collection n'existe pas d�j�
			$dummy = "SELECT * FROM collections WHERE collection_name REGEXP '^${value[name]}$' AND collection_parent='$value[parent]' LIMIT 1 ";
			$check = mysql_query($dummy, $dbh);
			if(mysql_num_rows($check)) {
				require_once("$include_path/user_error.inc.php");
				warning($msg[167],htmlentities($msg[171]." -> ".$this->display,ENT_QUOTES, $charset));
				return FALSE;
			}
		}
		$requete = 'INSERT INTO collections '.$requete.';';
		if(mysql_query($requete, $dbh)) {
			$this->id=mysql_insert_id();
			// liens entre autorit�s
			$aut_link= new aut_link(AUT_TABLE_COLLECTIONS,$this->id);
			$aut_link->save_form();
			audit::insert_creation (AUDIT_COLLECTION, $this->id) ;
		} else {
			require_once("$include_path/user_error.inc.php");
			warning($msg[167],htmlentities($msg[170]." -> ".$requete,ENT_QUOTES, $charset));
			return FALSE;
		}
	}
	if($value['subcollections']){
		for ( $i=0 ; $i<count($value['subcollections']) ; $i++){
			$subcoll=stripslashes_array($value['subcollections'][$i]);//La fonction d'import fait les addslashes contrairement � l'update
			$subcoll['coll_parent'] = $this->id;
			subcollection::import($subcoll);
		}
	}
	return true;
}

// ---------------------------------------------------------------
//		import() : import d'une collection
// ---------------------------------------------------------------

// fonction d'import de collection (membre de la classe 'collection');

function import($data) {

	// cette m�thode prend en entr�e un tableau constitu� des informations �diteurs suivantes :
	//	$data['name'] 	Nom de la collection
	//	$data['parent']	id de l'�diteur parent de la collection
	//	$data['issn']	num�ro ISSN de la collection

	global $dbh;

	// check sur le type de  la variable pass�e en param�tre
	if(!sizeof($data) || !is_array($data)) {
		// si ce n'est pas un tableau ou un tableau vide, on retourne 0
		return 0;
	}

	// check sur les �l�ments du tableau (data['name'] est requis).
	
	$long_maxi_name = mysql_field_len(mysql_query("SELECT collection_name FROM collections limit 1"),0);
	$data['name'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['name']))),0,$long_maxi_name));

	//si on a pas d'id, on peut avoir les infos de l'�diteur 
	if(!$data['parent']){
		if($data['publisher']){
			//on les a, on cr�e l'�diteur
			$data['parent'] = editeur::import($data['publisher']);
		}
	}
	
	if(($data['name']=="") || ($data['parent']==0)) /* il nous faut imp�rativement un �diteur */
		return 0;


	// pr�paration de la requ�te
	$key0 = addslashes($data['name']);
	$key1 = $data['parent'];
	$key2 = addslashes($data['issn']);
	
	/* v�rification que l'�diteur existe bien ! */
	$query = "SELECT ed_id FROM publishers WHERE ed_id='${key1}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) 
		die("can't SELECT publishers ".$query);
	if (mysql_num_rows($result)==0) 
		return 0;

	/* v�rification que la collection existe */
	$query = "SELECT collection_id FROM collections WHERE collection_name='${key0}' AND collection_parent='${key1}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT collections ".$query);
	$collection  = mysql_fetch_object($result);

	/* la collection existe, on retourne l'ID */
	if($collection->collection_id)
		return $collection->collection_id;

	// id non-r�cup�r�e, il faut cr�er la forme.
	$query = "INSERT INTO collections SET collection_name='$key0', ";
	$query .= "collection_parent='$key1', ";
	$query .= "collection_issn='$key2', ";
	$query .= "index_coll=' ".strip_empty_words($key0)." ".strip_empty_words($key2)." ', ";
	$query .= "collection_comment = '".addslashes($data['comment'])."'";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't INSERT into database");
	
	$id = mysql_insert_id($dbh);
	
	if($data['subcollections']){
		for ( $i=0 ; $i<count($data['subcollections']) ; $i++){
			$subcoll = $data['subcollections'][$i];
			$subcoll['coll_parent'] = $id;
			subcollection::import($subcoll);
		}
	}
	
	audit::insert_creation (AUDIT_COLLECTION, $id) ;

	return $id;
}
	
// ---------------------------------------------------------------
//		search_form() : affichage du form de recherche
// ---------------------------------------------------------------

static function search_form() {
	global $user_query, $user_input;
	global $msg,$charset;

	$user_query = str_replace ('!!user_query_title!!', $msg[357]." : ".$msg[136] , $user_query);
	$user_query = str_replace ('!!action!!', './autorites.php?categ=collections&sub=reach&id=', $user_query);
	$user_query = str_replace ('!!add_auth_msg!!', $msg[163] , $user_query);
	$user_query = str_replace ('!!add_auth_act!!', './autorites.php?categ=collections&sub=collection_form', $user_query);
	$user_query = str_replace ('<!-- lien_derniers -->', "<a href='./autorites.php?categ=collections&sub=collection_last'>$msg[1312]</a>", $user_query);
	$user_query = str_replace("!!user_input!!",htmlentities(stripslashes($user_input),ENT_QUOTES, $charset),$user_query);
	print pmb_bidi($user_query) ;
}

//---------------------------------------------------------------
// update_index($id) : maj des n-uplets la table notice_global_index en rapport avec cet collection	
//---------------------------------------------------------------
function update_index($id) {
	global $dbh;
	// On cherche tous les n-uplet de la table notice correspondant � cet auteur.
	$found = mysql_query("select distinct notice_id from notices where coll_id='".$id."'",$dbh);
	// Pour chaque n-uplet trouv�s on met a jour la table notice_global_index avec l'auteur modifi� :
	while($mesNotices = mysql_fetch_object($found)) {
		$notice_id = $mesNotices->notice_id;
		notice::majNoticesGlobalIndex($notice_id);
		notice::majNoticesMotsGlobalIndex($notice_id,'collection');
	}
}

//---------------------------------------------------------------
// get_informations_from_unimarc : ressort les infos d'une collection depuis une notice unimarc
//---------------------------------------------------------------
function get_informations_from_unimarc($fields,$from_subcollection=false,$import_subcoll=false){
	$data = array();
	
	if(!$from_subcollection){
		$data['name'] = $fields['200'][0]['a'][0];
		if(count($fields['200'][0]['i'])){
			foreach ( $fields['200'][0]['i'] as $value ) {
       			$data['name'].= ". ".$value;
			}
		}
		if(count($fields['200'][0]['e'])){
			foreach ( $fields['200'][0]['e'] as $value ) {
       			$data['name'].= " : ".$value;
			}
		}
		$data['issn'] = $fields['011'][0]['a'][0];
		if($fields['312']){
			for($i=0 ; $i<count($fields['312']) ; $i++){
				for($j=0 ; $j<count($fields['312'][$i]['a']) ; $j++){
					if($data['comment']!= "") $data['comment'] .= "\n";
					$data['comment'].=$fields['312'][$i]['a'][$j];
				}
			}
		}
		$data['publisher'] = editeur::get_informations_from_unimarc($fields);
		if($import_subcoll){
			$data['subcollections'] = subcollection::get_informations_from_unimarc($fields,true);
		}
	}else{
		$data['name'] = $fields['410'][0]['t'][0];
		$data['issn'] = $fields['410'][0]['x'][0];
		$data['authority_number'] = $fields['410'][0]['3'][0];
		$data['publisher'] = editeur::get_informations_from_unimarc($fields);
	}
	return $data;
}

function check_if_exists($data){
	global $dbh;
	
	//si on a pas d'id, on peut avoir les infos de l'�diteur 
	if(!$data['parent']){
		if($data['publisher']){
			//on les a, on cr�e l'�diteur
			$data['parent'] = editeur::check_if_exists($data['publisher']);
		}
	}

	// pr�paration de la requ�te
	$key0 = addslashes($data['name']);
	$key1 = $data['parent'];
	$key2 = addslashes($data['issn']);
	
	/* v�rification que la collection existe */
	$query = "SELECT collection_id FROM collections WHERE collection_name='${key0}' AND collection_parent='${key1}' LIMIT 1 ";
	$result = @mysql_query($query, $dbh);
	if(!$result) die("can't SELECT collections ".$query);
	$collection  = mysql_fetch_object($result);

	/* la collection existe, on retourne l'ID */
	if($collection->collection_id)
		return $collection->collection_id;	
		
	return 0;
}

} # fin de d�finition de la classe collection

} # fin de d�laration
