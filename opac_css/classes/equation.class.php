<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: equation.class.php,v 1.4 2009-05-16 10:52:43 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// d�finition de la classe de gestion des '�quations de recherche'
require_once($class_path."/search.class.php");

class equation {

// ---------------------------------------------------------------
//		propri�t�s de la classe
// ---------------------------------------------------------------
	var $id_equation=0;	
	var $num_classement=1; 
	var	$nom_equation="";
	var	$comment_equation="";
	var	$requete="";
	var	$proprio_equation=0;
	var $search_class;
	var $uman_query = "" ;

// ---------------------------------------------------------------
//		constructeur
// ---------------------------------------------------------------
function equation ($id=0) {
	//Instantiation d'une classe recherche
	$this->search_class=new search(false);
	if ($id) {
		// on cherche � atteindre une notice existante
		$this->id_equation = $id;
		$this->getData();
		} else {
			// la notice n'existe pas
			$this->id_equation = 0;
			$this->getData();
			}
	}

// ---------------------------------------------------------------
//		getData() : r�cup�ration infos
// ---------------------------------------------------------------
function getData() {
	global $dbh;
	global $msg;
	
	if (!$this->id_equation) {
		// pas d'identifiant. on retourne un tableau vide
	 	$this->id_equation=0;
	 	$this->num_classement = 1 ;
		$this->nom_equation="";
		$this->comment_equation="";
		$this->requete="";
		$this->proprio_equation=0;
		$this->human_query = "" ;
		} else {
			$requete = "SELECT id_equation, num_classement, nom_equation,comment_equation,requete, proprio_equation FROM equations WHERE id_equation='".$this->id_equation."' " ;
			$result = mysql_query($requete, $dbh) or die ($requete."<br /> in equation.class.php : ".mysql_error());
			if(mysql_num_rows($result)) {
				$temp = mysql_fetch_object($result);
			 	$this->id_equation		= $temp->id_equation ;
			 	$this->num_classement	= $temp->num_classement ;
				$this->nom_equation		= $temp->nom_equation ;
				$this->comment_equation	= $temp->comment_equation ;	
				$this->requete			= $temp->requete ;
				$this->proprio_equation	= $temp->proprio_equation ;	
				$this->human_query = $this->search_class->make_serialized_human_query($this->requete) ;
				} else {
					// pas de bannette avec cette cl�
				 	$this->id_equation=0;
				 	$this->num_classement = 1 ;
					$this->nom_equation="";
					$this->comment_equation="";
					$this->requete="";
					$this->proprio_equation=0;
					$this->human_query = "" ;
					}
			}
	}

// ---------------------------------------------------------------
//		show_form : affichage du formulaire de saisie
// ---------------------------------------------------------------
function show_form() {

	global $msg, $charset;
	global $dsi_equation_form;
	
	if($this->id_equation) {
		$action = "./dsi.php?categ=equations&sub=$type&id_equation=$this->id_equation&suite=update";
		$button_delete = "<input type='button' class='bouton' value='$msg[63]' ";
		$button_delete .= "onClick=\"confirm_delete();\">";
		$libelle = $msg['dsi_equ_form_modif'];
		$button_modif_requete = "<input type='button' class='bouton' value=\"$msg[dsi_equ_modif_requete]\" onClick=\"document.modif_requete_form.submit();\">";
		$form_modif_requete = $this->make_hidden_search_form();
		} else {
			$action = "./dsi.php?categ=equations&sub=$type&id_equation=0&suite=update";
			$libelle = $msg['dsi_equ_form_creat'];
			$button_delete ='';
			$button_modif_requete = "";
			$form_modif_requete = "";
			}

	$dsi_equation_form = str_replace('!!libelle!!', $libelle, $dsi_equation_form);

	$dsi_equation_form = str_replace('!!id_equation!!', $this->id_equation, $dsi_equation_form);
	$dsi_equation_form = str_replace('!!action!!', $action, $dsi_equation_form);
	$dsi_equation_form = str_replace('!!nom_equation!!', htmlentities($this->nom_equation,ENT_QUOTES, $charset), $dsi_equation_form);
	
	$dsi_equation_form = str_replace('!!num_classement!!', show_classement_utilise ('EQU', $this->num_classement, 0), $dsi_equation_form);
	
	$dsi_equation_form = str_replace('!!comment_equation!!', htmlentities($this->comment_equation,ENT_QUOTES, $charset), $dsi_equation_form);

	$dsi_equation_form = str_replace('!!requete!!', htmlentities($this->requete,ENT_QUOTES, $charset), $dsi_equation_form);
	$dsi_equation_form = str_replace('!!requete_human!!', $this->search_class->make_serialized_human_query($this->requete), $dsi_equation_form);
	
	if ($this->proprio_equation==0) $dsi_equation_form = str_replace('!!proprio_equation!!', htmlentities($msg['dsi_equ_no_proprio'],ENT_QUOTES, $charset), $dsi_equation_form);
		else $dsi_equation_form = str_replace('!!proprio_equation!!', "Choix de proprio � faire", $dsi_equation_form);
	
	$dsi_equation_form = str_replace('!!delete!!', $button_delete,  $dsi_equation_form);
	$dsi_equation_form = str_replace('!!bouton_modif_requete!!', $button_modif_requete,  $dsi_equation_form);
	$dsi_equation_form = str_replace('!!form_modif_requete!!', $form_modif_requete,  $dsi_equation_form);
	
	return $dsi_equation_form;
	}

// ---------------------------------------------------------------
//		delete() : suppression 
// ---------------------------------------------------------------
function delete() {
	global $dbh;
	global $msg;
	
	if (!$this->id_equation)
		// impossible d'acc�der � cette �quation
		return $msg[409];

	$requete = "delete from bannette_equation WHERE num_equation='$this->id_equation'";
	$res = mysql_query($requete, $dbh);
	$requete = "delete from equations WHERE id_equation='$this->id_equation'";
	$res = mysql_query($requete, $dbh);
	}


// ---------------------------------------------------------------
//		update 
// ---------------------------------------------------------------
function update($temp) {

	global $dbh;
	global $msg;
	
	if ($this->id_equation) {
		// update
		$req = "UPDATE equations set ";
		$clause = " WHERE id_equation='".$this->id_equation."'";
		} else {
			$req = "insert into equations set ";
			$clause = "";
			}
	$req.="num_classement='$temp->num_classement',";
	$req.="nom_equation='".trim($temp->nom_equation)."',";
	$req.="comment_equation='".trim($temp->comment_equation)."',";	
	$req.="requete='$temp->requete',";
	$req.="proprio_equation='$temp->proprio_equation'";	
	$req.=$clause ;
	$res = mysql_query($req, $dbh);
	if (!$this->id_equation) $this->id_equation = mysql_insert_id() ;
	}

// pour maj de requete d'�quation
    function make_hidden_search_form($url="") {
    	global $search;
    	global $charset;
    	global $page;
    	$url = "./catalog.php?categ=search&mode=6" ;
    	// remplir $search
    	$this->search_class->unserialize_search($this->requete);
    	
    	$r="<form name='modif_requete_form' action='$url' style='display:none' method='post'>";
    	
    	for ($i=0; $i<count($search); $i++) {
    		$inter="inter_".$i."_".$search[$i];
    		global $$inter;
    		$op="op_".$i."_".$search[$i];
    		global $$op;
    		$field_="field_".$i."_".$search[$i];
    		global $$field_;
    		$field=$$field_;
    		
    		$r.="<input type='hidden' name='search[]' value='".htmlentities($search[$i],ENT_QUOTES,$charset)."'/>";
    		$r.="<input type='hidden' name='".$inter."' value='".htmlentities($$inter,ENT_QUOTES,$charset)."'/>";
    		$r.="<input type='hidden' name='".$op."' value='".htmlentities($$op,ENT_QUOTES,$charset)."'/>";
    		for ($j=0; $j<count($field); $j++) {
    			$r.="<input type='hidden' name='".$field_."[]' value='".htmlentities($field[$j],ENT_QUOTES,$charset)."'/>";
    		}
    	}
    	$r.="<input type='hidden' name='id_equation' value='$this->id_equation'/>";
    	$r.="</form>";
    	return $r;
    }
    

} # fin de d�finition de la classe equation
