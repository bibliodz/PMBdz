<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: expl.class.php,v 1.68 2013-12-17 08:28:31 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classe de gestion des exemplaires
require_once($class_path."/parametres_perso.class.php");
require_once($class_path."/audit.class.php");
require_once($class_path."/sur_location.class.php");
require_once($class_path."/serialcirc.class.php");

if ($pmb_numero_exemplaire_auto) require_once($include_path."/$pmb_numero_exemplaire_auto_script");

//require_once($include_path."/gen_code/gen_code_exemplaire.php");
if ( ! defined( 'EXEMPLAIRE_CLASS' ) ) {
  define( 'EXEMPLAIRE_CLASS', 1 );

class exemplaire {
	
	var $expl_id = 0;
	var $cb = '';
	var $id_notice = 0;
	var $id_bulletin = 0;
	var $id_bulletin_notice = 0;
	var $typdoc_id = 0;
	var $typdoc = '';
	var $duree_pret = 0;
	var $cote = '';
	var $section_id = 0;
	var $section = '';
	var $statut_id = 0;
	var $statut = '';
	var $pret = 0;
	var $location_id = 0;
	var $location = '';
	var $codestat_id = 0;
	var $codestat = '';
	var $date_depot = '0000-00-00';
	var $date_retour = '0000-00-00';
	var $note = '';
	var $prix = '';
	var $owner_id = 0;
	var $lastempr = 0;
	var $last_loan_date = '0000-00-00';
	var $create_date = '0000-00-00';
	var $update_date = '0000-00-00';
	var $type_antivol="";
	var $tranfert_location_origine = 0;
	var $tranfert_statut_origine = 0;
	var $expl_comment='';
	var $nbparts = 1;
	var $expl_retloc = 0;
	
	var $ajax_cote_fields = '';
	var $explr_acces_autorise="MODIF" ; // sera �gal � INVIS, MODIF ou UNMOD en fonction des droits de l'utilisateur sur la localisation
	var $error = false;
	
	// constructeur
	function exemplaire($cb='', $id=0, $id_notice=0, $id_bulletin=0) {
	
		global $dbh;
		global $class_path;
		global $pmb_sur_location_activate;
		
		// on checke si l'exemplaire est connu
		if ($cb && !$id) $clause_where = " WHERE expl_cb like '$cb' ";
		
		if ( (!$cb && $id) || ($cb && $id) ) $clause_where = " WHERE expl_id = '$id' ";
		
		if ($cb || $id) {
			$requete = "SELECT *, section_libelle, location_libelle";
			$requete .= " FROM exemplaires LEFT JOIN docs_section ON (idsection = expl_section) LEFT JOIN docs_location ON (idlocation = expl_location)";
			$requete .= $clause_where ;
			$result = @mysql_query($requete, $dbh);
	
			if(mysql_num_rows($result)) {
				$item = mysql_fetch_object($result);
				$this->expl_id		= $item->expl_id;
				$this->cb			= $item->expl_cb;
				$this->id_notice	= $item->expl_notice;
				$this->id_bulletin	= $item->expl_bulletin;
				$this->typdoc_id	= $item->expl_typdoc;
				$this->typdoc		= $item->tdoc_libelle;
				$this->duree_pret	= $item->duree_pret;
				$this->cote			= $item->expl_cote;
				$this->section_id	= $item->expl_section;
				$this->section		= $item->section_libelle;
				$this->statut_id	= $item->expl_statut;
				//$this->statut		= $item->statut_libelle;		
				//$this->pret		= $item->pret_flag;				
				$this->location_id	= $item->expl_location;
				$this->location		= $item->location_libelle;
				$this->codestat_id	= $item->expl_codestat;
				//$this->codestat	= $item->codestat_libelle;
				$this->date_depot 	= $item->expl_date_depot ;
				$this->date_retour 	= $item->expl_date_retour ;
				$this->note			= $item->expl_note;
				$this->prix			= $item->expl_prix;
				$this->owner_id		= $item->expl_owner;
				$this->lastempr		= $item->expl_lastempr;
				$this->last_loan_date =  $item->last_loan_date;
				$this->create_date 	= $item->create_date;
				$this->update_date 	= $item->update_date;
				$this->type_antivol = $item->type_antivol ;
				$this->transfert_location_origine = $item->transfert_location_origine;
				$this->transfert_statut_origine = $item->transfert_statut_origine;
				$this->expl_comment	= $item->expl_comment;
				$this->nbparts		= $item->expl_nbparts;
				$this->expl_retloc	= $item->expl_retloc;
				
				if($pmb_sur_location_activate){
					$sur_loc= sur_location::get_info_surloc_from_location($item->expl_location);
					$this->sur_loc_libelle=$sur_loc->libelle;
					$this->sur_loc_id=$sur_loc->id;
				}
				// visibilite des exemplaires
				global $explr_invisible, $explr_visible_unmod, $explr_visible_mod, $pmb_droits_explr_localises ;
				if ($pmb_droits_explr_localises) {
					$tab_invis=explode(",",$explr_invisible);
					$tab_unmod=explode(",",$explr_visible_unmod);
	
					$as_invis = array_search($this->location_id,$tab_invis);
					$as_unmod = array_search($this->location_id,$tab_unmod);
					if ($as_invis!== FALSE && $as_invis!== NULL) {
						$this->explr_acces_autorise="INVIS" ;	
					} elseif ($as_unmod!== FALSE && $as_unmod!== NULL) {
						$this->explr_acces_autorise="UNMOD" ;
					} else {
						$this->explr_acces_autorise="MODIF" ;	
					}
				} else {
					$this->explr_acces_autorise="MODIF" ;	
				}
				
			} else { // rien trouv� en base
				$this->cb = $cb;
				$this->id_notice = $id_notice;
				$this->id_bulletin = $id_bulletin;
				global $explr_invisible, $explr_visible_unmod, $explr_visible_mod, $pmb_droits_explr_localises ;
				if ($pmb_droits_explr_localises) {
					if ($explr_visible_mod) {
						$this->explr_acces_autorise="MODIF" ;
					} else {
						$this->explr_acces_autorise="UNMOD" ;
					}
				} else {
					$this->explr_acces_autorise="MODIF" ;
				}
			}
		} else { // rien de fourni apparemment
			$this->cb = $cb;
			$this->id_notice = $id_notice;
			$this->id_bulletin = $id_bulletin;
			global $explr_invisible, $explr_visible_unmod, $explr_visible_mod, $pmb_droits_explr_localises ;
			if ($pmb_droits_explr_localises) {
				if ($explr_visible_mod) {
					$this->explr_acces_autorise="MODIF" ;
				} else {
					$this->explr_acces_autorise="UNMOD" ;
				}
			} else {
				$this->explr_acces_autorise="MODIF" ;
			}
		}
		if ($this->id_bulletin) {
  			$qb="select bulletin_notice from bulletins where bulletin_id='".$this->id_bulletin."' ";
   			$rb=@mysql_query($qb, $dbh);
   			if (mysql_num_rows($rb)) {
   				$this->id_bulletin_notice=mysql_result($rb,0,0);
   			}
		}
	}	
	

	//sauvegarde en base
	function save() {

		global $dbh;
		
		$this->error=false;

		if ( 	(trim($this->cb)!=='')
				&& ($this->id_notice || $this->id_bulletin) 
				&& ($this->typdoc_id)
				&& (trim($this->cote)!=='')
				&& ($this->location_id)
				&& ($this->section_id)
				&& ($this->codestat_id)	
				&& ($this->owner_id)	
				&& ($this->statut_id)	) {
						
			if ($this->expl_id) {
				$q = "update exemplaires set ";
			} else {
				$q = "insert into exemplaires set ";
			}
			$q.= "expl_cb = '".$this->cb."', ";
			$q.= "expl_notice = '".$this->id_notice."', ";
			if ($this->id_notice) {
				$q.= "expl_bulletin = '0', ";
			} else {
				$q.= "expl_bulletin = '".$this->id_bulletin ."', ";	
			}
			$q.= "expl_typdoc = '".$this->typdoc_id."', ";
			$q.= "expl_cote = '".trim($this->cote)."', ";
			$q.= "expl_section = '".$this->section_id."', ";
			$q.= "expl_statut =  '".$this->statut_id."', ";
			$q.= "expl_location = '".$this->location_id."', ";
			$q.= "expl_codestat = '".$this->codestat_id."', ";
			$q.= "expl_date_depot = '".$this->date_depot."', ";
			$q.= "expl_date_retour = '".$this->date_retour."', ";
			$q.= "expl_note = '".$this->note."', ";
			$q.= "expl_prix = '".$this->prix."', ";
			$q.= "expl_owner = '".$this->owner_id."', ";
			$q.= "expl_lastempr = '".$this->lastempr."', ";
			$q.= "last_loan_date = '".$this->last_loan_date."', ";
			$q.= "create_date = '".$this->create_date."', ";
			$q.= "type_antivol = '".$this->type_antivol."', ";
			$q.= "transfert_location_origine = '".$this->tranfert_location_origine."', ";
			$q.= "transfert_statut_origine = '".$this->tranfert_statut_origine."', ";
			$q.= "expl_comment = '".$this->expl_comment."', ";
			$q.= "expl_nbparts = '".$this->nbparts."', ";
			$q.= "expl_retloc = '".$this->expl_retloc."' ";
			if ($this->expl_id) {
				$q.= "where expl_id='".$this->expl_id."' ";
			}
			$r = mysql_query($q, $dbh); 
			if ($r) {
				if(!$this->expl_id) $this->expl_id = mysql_insert_id($dbh);
			} else {
				$this->error=true;
			}
		} else {
			$this->error=true; 				
		}
		return !$this->error;
	}
	
	
	function fill_form (&$form, $action) {
		global $charset;
		global $msg;
		global $pmb_antivol;
		global $option_num_auto;
		global $dbh;
		global $pmb_expl_show_dates;
	
		if (isset($option_num_auto)) {
	  		$requete="DELETE from exemplaires_temp where sess not in (select SESSID from sessions)";
	   		mysql_query($requete,$dbh);
	  	
	    	//Appel � la fonction de g�n�ration automatique de cb
	    	$code_exemplaire =init_gen_code_exemplaire($this->id_notice,$this->id_bulletin);
	    	do {
	    		$code_exemplaire = gen_code_exemplaire($this->id_notice,$this->id_bulletin,$code_exemplaire);
	    		$requete="select expl_cb from exemplaires WHERE expl_cb='$code_exemplaire'";
	    		$res0 = mysql_query($requete,$dbh);
	    		$requete="select cb from exemplaires_temp WHERE cb='$code_exemplaire' AND sess <>'".SESSid."'";
	    		$res1 = mysql_query($requete,$dbh);
	    	} while((mysql_num_rows($res0)||mysql_num_rows($res1)));
	    		
	   		//Memorise dans temps le cb et la session pour le cas de multi utilisateur session
	   		$this->cb = $code_exemplaire;
	   		$requete="INSERT INTO exemplaires_temp (cb ,sess) VALUES ('$this->cb','".SESSid."')";
	   		mysql_query($requete,$dbh);
		}
	
		$form = str_replace('!!action!!', $action, $form);
		if ($this->id_notice) {
			$form = str_replace('!!id!!', $this->id_notice, $form);
		} else {
			$form = str_replace('!!id!!', $this->id_bulletin, $form);
		}
	 	$form = str_replace('!!cb!!',   htmlentities($this->cb  , ENT_QUOTES, $charset), $form);
	 	$form = str_replace('!!nbparts!!',   htmlentities($this->nbparts  , ENT_QUOTES, $charset), $form);
	 	$form = str_replace('!!note!!', htmlentities($this->note, ENT_QUOTES, $charset), $form);
	 	$form = str_replace('!!comment!!', htmlentities($this->expl_comment, ENT_QUOTES, $charset), $form);
	 	if ($this->id_notice) {
	 		$form = str_replace('!!cote!!', htmlentities(prefill_cote($this->id_notice,$this->cote), ENT_QUOTES, $charset), $form);
	 	} else {
	 		$form = str_replace('!!cote!!', htmlentities(prefill_cote($this->id_bulletin_notice,$this->cote), ENT_QUOTES, $charset), $form);
	 	}
	 	$form = str_replace('!!prix!!', htmlentities($this->prix, ENT_QUOTES, $charset), $form);
	
		// select "type document"
		$form = str_replace('!!type_doc!!',
					do_selector('docs_type', 'f_ex_typdoc', $this->typdoc_id),
					$form);		
	
		// select "section"
		$form = str_replace('!!section!!',
					$this->do_selector(),
					$form);
	
		// select "statut"
		$form = str_replace('!!statut!!',
					do_selector('docs_statut', 'f_ex_statut', $this->statut_id),
					$form);
	
		// select "localisation"
	
		//visibilit� des exemplaires
		global $explr_visible_mod, $pmb_droits_explr_localises ;
		if ($pmb_droits_explr_localises) $where_clause_explr = "idlocation in (".$explr_visible_mod.") and";
		$form = str_replace('!!localisation!!',
				gen_liste ("select distinct idlocation, location_libelle from docs_location, docsloc_section where $where_clause_explr num_location=idlocation order by 2 ", "idlocation", "location_libelle", 'f_ex_location', "calcule_section(this);", $this->location_id, "", "","","",0),
				$form);
		
		// select "code statistique"
		$form = str_replace('!!codestat!!',
				do_selector('docs_codestat', 'f_ex_cstat', $this->codestat_id),
				$form);
	
		if ($pmb_antivol) {
			global $value_deflt_antivol;
			if ($this->type_antivol=="") $this->type_antivol=$value_deflt_antivol;
			// select "type_antivol"
			$selector = "<select name='type_antivol' id='type_antivol'>";
			$selector .= "<option value='0'";
			if ($this->type_antivol==0) $selector .= ' selected="selected"';
			$selector .= '>';
			$selector .= $msg["type_antivol_aucun"].'</option>';
			$selector .= "<option value='1'";
			if ($this->type_antivol==1) $selector .= ' selected="selected"';
			$selector .= '>';
			$selector .= $msg["type_antivol_magnetique"].'</option>';
			$selector .= "<option value='2'";
			if ($this->type_antivol==2) $selector .= ' selected="selected"';
			$selector .= '>';
			$selector .= $msg["type_antivol_autre"].'</option>';		
			$selector .= '</select>';
		} else $selector="";
		$form = str_replace('!!type_antivol!!',
					$selector,
					$form);
		
		// select "owner"
		$form = str_replace('!!owner!!',
				do_selector('lenders', 'f_ex_owner', $this->owner_id),
				$form);
		
		//dates creation / modification
		if ($this->expl_id && ($pmb_expl_show_dates=='1' || $pmb_expl_show_dates=='3')) {
			$form = str_replace('<!-- msg_exp_cre_date -->',"<label class='etiquette' >".htmlentities($msg['exp_cre_date'],ENT_QUOTES,$charset)."</label>",$form);
			$form = str_replace('<!-- exp_cre_date -->',format_date($this->create_date),$form);
			$form = str_replace('<!-- msg_exp_upd_date -->',"<label class='etiquette' >".htmlentities($msg['exp_upd_date'],ENT_QUOTES,$charset)."</label>",$form);
			$form = str_replace('<!-- exp_upd_date -->',format_date($this->update_date),$form);
		}
		
		//dates d�p�t / retour
		if ($this->expl_id && ($pmb_expl_show_dates=='2' || $pmb_expl_show_dates=='3')) {
			$form = str_replace('<!-- msg_exp_filing_date -->',"<label class='etiquette' >".htmlentities($msg['filing_date'],ENT_QUOTES,$charset)."</label>",$form);
			$form = str_replace('<!-- exp_filing_date -->',format_date($this->date_depot),$form);
			$form = str_replace('<!-- msg_exp_return_date -->',"<label class='etiquette' >".htmlentities($msg['return_date'],ENT_QUOTES,$charset)."</label>",$form);
			$form = str_replace('<!-- exp_return_date -->',format_date($this->date_retour),$form);
		}
		
		$p_perso=new parametres_perso("expl");
		if (!$p_perso->no_special_fields) {
			$c=0;
			$perso="<hr />";
			$perso_=$p_perso->show_editable_fields($this->expl_id);
			for ($i=0; $i<count($perso_["FIELDS"]); $i++) {
				$p=$perso_["FIELDS"][$i];
				if ($c==0) $perso.="<div class='row'>\n";
				$perso.="<div class='colonne2'><label for='".$p["NAME"]."' class='etiquette'>".$p["TITRE"]."</label><div class='row'>".$p["AFF"]."</div></div>\n";
				$c++;
				if ($c==2) {
					$perso.="</div>\n";
					$c=0;
				}
			}	
			if ($c==1) $perso.="<div class='colonne2'>&nbsp;</div>\n</div>\n";
			$perso=$perso_["CHECK_SCRIPTS"]."\n".$perso;
		} else {
			$perso="\n<script>function check_form() { return true; }</script>\n";
		}
		$form = str_replace("!!champs_perso!!",$perso,$form);
		
		//Remplissage ajax de la cote
		global $pmb_prefill_cote_ajax, $pmb_antivol;
		if($pmb_prefill_cote_ajax)
			$expl_ajax_cote=" completion='expl_cote' listfield='".$this->ajax_cote_fields.",f_ex_cb,f_ex_typdoc,f_ex_location,f_ex_owner,f_ex_statut,f_ex_cstat".($pmb_antivol>0 ? ",type_antivol":"")."' ";
		else $expl_ajax_cote="";
		$form = str_replace("!!expl_ajax_cote!!",$expl_ajax_cote,$form);
	}
	
	function expl_form ($action='', $annuler='') {
		global $expl_form;
		global $msg, $pmb_type_audit, $select_categ_prop ;
		
		if ($action && $this->id_notice) {
			$action .= '&id='.$this->id_notice.'&org_cb='.urlencode($this->cb);
		}
		$this->fill_form ($expl_form, $action);
		
		if ($pmb_type_audit && $this->expl_id) $link_audit =  "<input class='bouton' type='button' onClick=\"openPopUp('./audit.php?type_obj=2&object_id=$this->expl_id', 'audit_popup', 700, 500, -2, -2, '$select_categ_prop')\" title='$msg[audit_button]' value='$msg[audit_button]' />";
				else $link_audit = "" ;
	
		// action du bouton annuler
		if($annuler && $this->id_notice) {
			// default : retour � la liste des exemplaires
			$annuler = './catalog.php?categ=expl&id='.$this->id_notice;
		}
		$expl_form = str_replace('!!annuler_action!!', $annuler, $expl_form);
		$expl_form = str_replace('!!link_audit!!', $link_audit, $expl_form);
	
		// affichage
		return $expl_form;
	}
	
	function zexpl_form($action) {	
		global $expl_form;
		
		$this->fill_form ($expl_form, $action);
	
		$expl_form = str_replace('!!supprimer!!', "", $expl_form);
		$expl_form = str_replace('!!link_audit!!', "", $expl_form);
	
		// affichage
		print "<span class='zexpl_form'>".pmb_bidi($expl_form)."</span>";
	}
	
	// ----------------------------------------------------------------------------
	//	fonction do_selector qui g�n�re des combo_box avec tout ce qu'il faut
	// ----------------------------------------------------------------------------
	function do_selector() {
	
		global $dbh;
	 	global $charset;
		
		global $deflt_docs_section;
		global $deflt_docs_location;
	
		if (!$this->section_id) $this->section_id=$deflt_docs_section ;
		if (!$this->location_id) $this->location_id=$deflt_docs_location;
	
		$rqtloc = "SELECT idlocation FROM docs_location order by location_libelle";
		$resloc = mysql_query($rqtloc, $dbh);
		while (($loc=mysql_fetch_object($resloc))) {
			$requete = "SELECT idsection, section_libelle FROM docs_section, docsloc_section where idsection=num_section and num_location='$loc->idlocation' order by section_libelle";
			$result = mysql_query($requete, $dbh);
			$nbr_lignes = mysql_num_rows($result);
			if ($nbr_lignes) {			
				if ($loc->idlocation==$this->location_id) $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:block\">\r\n";
					else $selector .= "<div id=\"docloc_section".$loc->idlocation."\" style=\"display:none\">\r\n";
				$selector .= "<select name='f_ex_section".$loc->idlocation."' id='f_ex_section".$loc->idlocation."'>\r\n";
				while (($line = mysql_fetch_row($result))) {
					$selector .= "<option value='$line[0]'";
					$line[0] == $this->section_id ? $selector .= ' SELECTED>' : $selector .= '>';
		 			$selector .= htmlentities($line[1],ENT_QUOTES, $charset).'</option>\r\n';
				}                                         
				$selector .= '</select></div>';
				$this->ajax_cote_fields .= ($this->ajax_cote_fields != '' ? ",f_ex_section".$loc->idlocation : "f_ex_section".$loc->idlocation);
				}                 
			}
		return $selector;                         
	}                                                 
	 
	
	// ---------------------------------------------------------------
	//		import() : import d'un exemplaire 
	// ---------------------------------------------------------------
	// fonction d'import d'exemplaire (membre de la classe 'exemplaire');
	function import($data) {                          
		global $msg;                              
	                                                  
		// cette m�thode prend en entr�e un tableau constitu� des informations exemplaires suivantes :
		//	$data['cb'] 	                  
		//	$data['notice']
		//  $data['bulletin']                   
		//	$data['typdoc']
		//	$data['cote']                     
		//	$data['section']                  
		//	$data['statut']                   
		//	$data['location']                 
		//	$data['codestat']                 
		//	$data['creation']                 
		//	$data['modif']                    
		//	$data['note']                     
		//	$data['prix']                     
		//	$data['expl_owner']               
		//	$data['cote_mandatory'] cote obligatoire = 1, non obligatoire = 0
		//	$data['quoi_faire'] que faire de cet exemplaire :
		//		0 : supprimer, 1 ou vide : Mettre � jour ou ajouter, 2 : ajouter si possible, sinon rien.
	                                                  
		global $dbh;                              
	                                                  
		// check sur le type de  la variable pass�e en param�tre
		if(!sizeof($data) || !is_array($data)) {  
			// si ce n'est pas un tableau ou un tableau vide, on retourne 0
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[544]."') ") ;
			return 0;                         
			}                                 
	                                                  
		if ($data['quoi_faire']=="") $data['quoi_faire']="2" ;
		if ((string)$data['quoi_faire']=="0") {
			$sql_del = "delete from exemplaires where expl_cb='".addslashes($data['cb'])."' " ;
			mysql_query($sql_del) ;
			return -1 ;
			}
			                                  		                                  
		// check sur les �l�ments du tableau (cb, cote, notice, typdoc, section, statut, location, codestat, owner sont requis).
		$long_maxi = mysql_field_len(mysql_query("SELECT expl_cb FROM exemplaires limit 1"),0);
		$data['cb'] = rtrim(substr(trim($data['cb']),0,$long_maxi));
		$long_maxi = mysql_field_len(mysql_query("SELECT expl_cote FROM exemplaires limit 1"),0);
		$data['cote'] = rtrim(substr(trim($data['cote']),0,$long_maxi));
		$long_maxi = mysql_field_len(mysql_query("SELECT expl_prix FROM exemplaires limit 1"),0);
		$data['prix'] = rtrim(substr(trim($data['prix']),0,$long_maxi));
	                                                  
		if ($data['expl_owner']=="") {
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', 'No lender given') ") ;
			return 0;                         
			}                                 
		
		if($data['cb']=="") {                     
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[545]."') ") ;
			return 0;                         
			}                                 
		
		if ($data['cote']=="") {                  
			if ($data['cote_mandatory']==1) { 
				$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[546]."') ") ;
				return 0;                 
				} else {                  
					$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[567]."') ") ;
					}                 
			}                                 
		
		if($data['notice']==0) {
			if ($data['bulletin']==0) {                  
				$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[547]."') ") ;
				return 0;                         
			}                                 
		}
		
		if($data['typdoc']==0) {                  
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[548]."') ") ;
			return 0;                         
			}                                 
		if($data['section']==0) {                 
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[549]."') ") ;
			return 0;             
			}                                 
		if($data['statut']==0) {                  
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[550]."') ") ;
			return 0;                         
			}                                 
		if($data['location']==0) {                
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[551]."') ") ;
			return 0;                         
			}                                 
		if($data['codestat']==0) {                
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[552]."') ") ;
			return 0;                         
			}                                 
		if($data['type_antivol']=="") {                
			$data['type_antivol']="1";
			} 
		// pr�paration de la requ�te              
		$key0 = addslashes($data['cb']);          
		$key1 = addslashes($data['cote']);        
		                                          
		/* v�rification que l'exemplaire existe ou pas */
		$exe = new stdClass();
		$query = "SELECT expl_id FROM exemplaires WHERE expl_cb='${key0}' LIMIT 1 ";
		$result = @mysql_query($query, $dbh);     
		if(!$result) die("can't SELECT exemplaires ".$query);
		if(mysql_num_rows($result)) $exe  = mysql_fetch_object($result);
	                                                  
	    if (!$data['date_depot']) $data['date_depot']="sysdate()" ; else $data['date_depot']="'".$data['date_depot']."'" ;                   
		if (!$data['date_retour']) $data['date_retour']="sysdate()" ; else $data['date_retour']="'".$data['date_retour']."'" ;                   
	                                                  
		// l'exemplaire existe et on ne pouvait que l'ajouter, on retourne l'ID 
		if ($exe->expl_id!="" && $data['quoi_faire']=="2") {
			$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('expl_".addslashes(SESSid).".class', '".$msg[553].$data['cb']."') ") ;
			return $exe->expl_id;
			}                                 
		
		// l'exemplaire existe et on doit le mettre � jour
		if ($exe->expl_id!="" && $data['quoi_faire']=="1") {
			$sql_a_faire = "update exemplaires SET " ;
			$sql_a_faire_suite = " where expl_cb='".addslashes($data['cb'])."' " ;
			}
		
		// l'exemplaire n'existe pas : on doit le cr�er
		if ($exe->expl_id=="") {
			$sql_a_faire = "insert into exemplaires SET " ;
			$sql_a_faire_suite = "" ;
			}
		
		$query  = $sql_a_faire ;
		$query .= "expl_cb='".$key0."', ";        
		$query .= "expl_notice='".$data['notice']."', ";
		$query .= "expl_bulletin='".$data['bulletin']."', ";
		$query .= "expl_typdoc='".$data['typdoc']."', ";
		$query .= "expl_cote=trim('".$key1."'), ";      
		$query .= "expl_section='".$data['section']."', ";
		$query .= "expl_statut='".$data['statut']."', ";
		$query .= "expl_location='".$data['location']."', ";
		$query .= "expl_codestat='".$data['codestat']."', ";
		$query .= "expl_note='".addslashes($data['note'])."', ";
		$query .= "expl_comment='".addslashes($data['comment'])."', ";
		$query .= "expl_prix='".addslashes($data['prix'])."', ";
		$query .= "expl_owner='".$data['expl_owner']."', ";      
		$query .= "expl_date_depot=".$data['date_depot'].", ";      
		$query .= "expl_date_retour=".$data['date_retour'].", ";      
		//$query .= "type_antivol=".$data['type_antivol'].", ";
		if($data['creation']){
			$query .= "create_date='".$data['creation']."'"; 
		}else{
			$query .= "create_date=sysdate() ";
		} 
	  
		$query .= $sql_a_faire_suite ;    
		$result = @mysql_query($query, $dbh);     
		if(!$result) die("can't INSERT into exemplaires ".$query);
	                                                  
		if ($exe->expl_id="") {
			audit::insert_creation(AUDIT_EXPL,mysql_insert_id($dbh));
			return mysql_insert_id($dbh);
		} else {
			$sql_id = mysql_query("select expl_id from exemplaires where expl_cb='".addslashes($data['cb'])."' ") ;
			$exe  = mysql_fetch_object($sql_id);  
			audit::insert_modif(AUDIT_EXPL,$exe->expl_id); 
			return $exe->expl_id;
		}       
	   
	} /* fin m�thode import */                
	
	// Suppression
	function del_expl($id=0) {
		
		global $dbh;
			
		$sql_pret = mysql_query("select 1 from pret where pret_idexpl ='$id' ") ;
		if (mysql_num_rows($sql_pret)) return 0 ;
		
		$requete = "select idcaddie FROM caddie where type='EXPL' ";
		$result = mysql_query($requete, $dbh);
		for($i=0;$i<mysql_num_rows($result);$i++) {
			$temp=mysql_fetch_object($result);
			$requete_suppr = "delete from caddie_content where caddie_id='".$temp->idcaddie."' and object_id='".$id."' ";
			$result_suppr = mysql_query($requete_suppr, $dbh);
			}
		audit::delete_audit (AUDIT_EXPL, $id) ;
		$p_perso=new parametres_perso("expl");
		$p_perso->delete_values($id);
		
		// nettoyage transfert
		$requete_suppr = "delete from transferts_demande where num_expl='$id'";
		$result_suppr = mysql_query($requete_suppr);
		
		// nettoyage circulation d�riodique
		serialcirc::delete_expl($id);
		$sql_del = mysql_query("delete from exemplaires where expl_id='$id' ") ;
		
		return 1 ;	
		}

		//sauvegarde un ensemble de notices dans un entrepot agnostique a partir d'un tableau d'ids d'exemplaires
		function save_to_agnostic_warehouse($expl_ids=array(),$source_id=0,$keep_expl=1) {
			global $base_path,$class_path,$include_path,$dbh;
			if (is_array($expl_ids) && count($expl_ids) && $source_id*1) {
				$export_params=array(
					'genere_lien'=>1,
					'notice_mere'=>1,
					'notice_fille'=>1,
					'mere'=>0,
					'fille'=>0,
					'bull_link'=>1,
					'perio_link'=>1,
					'art_link'=>0,
					'bulletinage'=>0,
					'notice_perio'=>0,
					'notice_art'=>0,
					'export_only_expl_ids'=> $expl_ids
				);
				$notice_ids=array();
				$bulletin_ids=array();
				$perio_ids=array();
				$q='select expl_notice,expl_bulletin,bulletin_notice from exemplaires left join bulletins on expl_bulletin=bulletin_id and expl_bulletin!=0 where expl_id in ('.implode(',',$expl_ids).')';
				$r=mysql_query($q,$dbh);
				if (mysql_num_rows($r)) {
					while($row=mysql_fetch_object($r)){
						if($row->expl_notice) $notice_ids[]=$row->expl_notice;
						if($row->expl_bulletin) $bulletin_ids[]=$row->expl_bulletin;
						if($row->bulletin_notice) $perio_ids[]=$row->bulletin_notice;
					}
				}
				if (count($notice_ids) || count($bulletin_ids)) {
					require_once($base_path.'/admin/convert/export.class.php');
					require_once($base_path."/admin/connecteurs/in/agnostic/agnostic.class.php");
					$conn=new agnostic($base_path.'/admin/connecteurs/in/agnostic');
					$source_params = $conn->get_source_params($source_id);
					$export_params['docnum']=1;
					$export_params['docnum_rep']=$source_params['REP_UPLOAD'];
				}
				if (count($notice_ids)) {
					$notice_ids=array_unique($notice_ids);
					$e=new export($notice_ids);
					$records=array();
					do{
						$nn = $e->get_next_notice('',array(),array(),$keep_expl,$export_params);
						if ($e->notice) $records[] = $e->xml_array;
					} while($nn);
					
					$conn->rec_records_from_xml_array($records,$source_id);
				}
				if (count($bulletin_ids)) {
					$bulletin_ids=array_unique($bulletin_ids);
					$perio_ids=array_unique($perio_ids);
					$e=new export($perio_ids);
					$e->expl_bulletin_a_exporter=$bulletin_ids;
					$records=array();
					do{
						$nn = $e->get_next_bulletin('',array(),array(),$keep_expl,$export_params);
						if ($e->notice) $records[] = $e->xml_array;
					} while($nn);
					$conn->rec_records_from_xml_array($records,$source_id);
				}
			}
		}	
		
	} # fin de la classe exemplaire                   
                                                  
} # fin de d�finition                             
