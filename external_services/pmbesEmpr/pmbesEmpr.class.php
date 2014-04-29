<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesEmpr.class.php,v 1.2 2013-09-03 09:11:53 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");
require_once($class_path."/emprunteur.class.php");
require_once($class_path."/parametres_perso.class.php");

class pmbesEmpr extends external_services_api_class {
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant à l'erreur
	var $es;				//Classe mère qui implémente celle-ci !
	var $msg;
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}
	
	function empr_list($filters=array()) {
		global $dbh;
		global $msg;
		global $charset;
		$sql_filters="";
		if(is_array($filters)){
			$i=0;
			foreach($filters as $filter){
				if(!$filter['field']) continue;				
				
				if($i==0) $sql_filters=" where ";
				else {
					if($filter['operateur'])
						$sql_filters.=" ".$filter['separator']." ";
					else
						$sql_filters.=" and ";
				}
				$sql_filters.= $filter['field']." ".$filter['operator']." '".$filter['value']."' ";
				$i++;
			}
		}
		$infos= array();
		$sql = "SELECT id_empr, empr_cb FROM empr $sql_filters";
		$res = mysql_query($sql);
		$i=0;
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]['empr_cb']=$res_info->empr_cb;	
			$infos[$i]['empr_id']=$res_info->id_empr;
			$i++;		
		}
		return $this->build_ok($infos);
	}
	
	function fetch_empr($empr_cb='', $empr_id='') {
		global $dbh;
		global $msg;		
		global $charset;
		
		$empr_cb=$this->clean_field($empr_cb);
		$empr_id += 0;
		if (!$empr_id && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
		
		if($empr_id)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '".addslashes($empr_cb)."' ";
		
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu.");
			
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
		$empr= new emprunteur($empr_id,'',false,1);
		$sql = "select id_groupe, libelle_groupe from groupe, empr_groupe where empr_id='".$empr_id."' and id_groupe=groupe_id order by libelle_groupe";
		$res = mysql_query($sql);
		$i=0;
		$groupes_infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$groupes_infos[$i]['id']=$res_info->id_groupe;
			$groupes_infos[$i]['libelle']=$res_info->libelle_groupe;	
			$i++;		
		}
		
		$requete_nb_pret = "select count(1) as nb_pret from pret where pret_idempr=".$empr_id;
		$result_nb_pret = mysql_query($requete_nb_pret, $dbh);
		$r_nb_pret = mysql_fetch_object($result_nb_pret);
		$nb_pret = $r_nb_pret->nb_pret ;
		
		
		$p_perso = new parametres_perso("empr");
		$perso_ = $p_perso->show_fields($empr_id);	
		$pperso_list=array();
		if (count($perso_)){
			for ($i=0; $i<count($perso_["FIELDS"]); $i++) {
				$p=$perso_["FIELDS"][$i];				
				$pperso_list[$i]["id"]=$p["ID"];
				$pperso_list[$i]["name"]=$p["NAME"];
				$pperso_list[$i]["libelle"]=$p["TITRE"];
				$pperso_list[$i]["aff"]=$p["AFF"];
			}				
		}
		$result = array(
			'empr_id' => $empr_id,
			'empr_cb' => $empr->cb,
			'nom' => $empr->nom,	
			'prenom' => $empr->prenom,	
			'adr1' => $empr->adr1,	
			'adr2' => $empr->adr2,	
			'cp' => $empr->cp,	
			'ville' => $empr->ville,	
			'pays' => $empr->pays,	
			'mail' => $empr->mail,		
			'tel1' => $empr->tel1,	
			'sms' => $empr->sms,	
			'tel2' => $empr->tel2,		
			'prof' => $empr->prof,	
			'birth' => $empr->birth,	
			'categ' => $empr->categ,	
			'cat_l' => $empr->cat_l,		
			'cstat' => $empr->cstat,	
			'cdate' => $empr->cdate,	
			'mdate' => $empr->mdate,		
			'sexe' => $empr->sexe,	
			'login' => $empr->login,	
			'pwd' => $empr->pwd,		
			'type_abt' => $empr->type_abt,	
			'location' => $empr->empr_location,	
			'location_l' => $empr->empr_location_l,		
			'date_blocage' => $empr->date_blocage,	
			'statut' => $empr->empr_statut,	
			'statut_libelle' => $empr->empr_statut_libelle,	
			'total_loans' => $empr->total_loans,	
			'allow_loan' => $empr->allow_loan,	
			'allow_book' => $empr->allow_book,				
			'allow_opac' => $empr->allow_opac,	
			'allow_dsi' => $empr->allow_dsi,		
			'allow_dsi_priv' => $empr->allow_dsi_priv,	
			'allow_sugg' => $empr->allow_sugg,		
			'allow_prol' => $empr->allow_prol,	
			'date_adhesion' => $empr->date_adhesion,		
			'date_expiration' => $empr->date_expiration,	
			'last_loan_date' => $empr->last_loan_date,
			'msg' => $empr->empr_msg,	
			'cstat' => $empr->cstat,		
			'cstat_l' => $empr->cstat_l,	
			'pperso_list' => $pperso_list,
			'groupe_list' => $groupes_infos,
			'nb_pret' => $nb_pret,
			'prets' => $empr->prets,
			'nb_retard' => $empr->retard,
			'nb_resa'=> $empr->nb_reservations,
			'nb_previsions'=> $empr->nb_previsions,
		);
		
		return $this->build_ok($result);
	}
	
	function delete_empr($empr_cb='', $empr_id='') {
		global $dbh;
		global $msg;		
		global $charset;
		
		$empr_cb=$this->clean_field($empr_cb);
		$empr_id += 0;
		if (!$idempr && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
		
		if($idempr)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '$empr_cb' ";
		
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu.");
			
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
		$status= emprunteur::del_empr($empr_id);
		if($status==false) return $this->build_error( "Ce lecteur a des prêts en cours.");
		return $this->build_ok();
	}
	
	function create_empr($empr_cb='',$fields) {
		global $dbh,$lang;
		global $msg;		
		global $charset;
		global $pmb_num_carte_auto,$deflt2docs_location,$pmb_gestion_abonnement,$pmb_gestion_financiere;
		
		$empr_cb=$this->clean_field((string)$empr_cb);				
		if(!$empr_cb && $pmb_num_carte_auto) $empr_cb = emprunteur::gen_num_carte_auto();
		if (!$empr_cb)  return $this->build_error( "Un code barre est obligatoire.");	
					
		$sql = "SELECT id_empr, empr_cb FROM empr WHERE  empr_cb = '".addslashes($empr_cb)."' ";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)) return $this->build_error( "Le code $empr_cb est déjà utilisé.");
			
		// clean des entrées
		$fields=$this->clean_fields($fields);
		/*
		$fields['nom']=$this->clean_field($fields['nom']);
		$fields['prenom']=$this->clean_field($fields['prenom']);
		$fields['adr1']=$this->clean_field($fields['adr1']);
		$fields['adr2']=$this->clean_field($fields['adr2']);
		$fields['cp']=$this->clean_field($fields['cp']);
		$fields['ville']=$this->clean_field($fields['ville']);
		$fields['pays']=$this->clean_field($fields['pays']);
		$fields['mail']=$this->clean_field($fields['mail']);
		$fields['tel1']=$this->clean_field($fields['tel1']);
		$fields['sms']=$this->clean_field($fields['sms'])+0;
		$fields['tel2']=$this->clean_field($fields['tel2']);
		$fields['prof']=$this->clean_field($fields['prof']);
		$fields['birth']=$this->clean_field($fields['birth']);
		$fields['sexe']=$this->clean_field($fields['sexe'])+0;
		$fields['login']=$this->clean_field($fields['login']);
		$fields['pwd']=$this->clean_field($fields['pwd']);
		$fields['msg']=$this->clean_field($fields['msg']);
		$fields['lang']=$this->clean_field($fields['lang']);
		$fields['location']=$this->clean_field($fields['location']+0);
		$fields['date_adhesion']=$this->clean_field($fields['date_adhesion']);
		$fields['date_expiration']=$this->clean_field($fields['date_expiration']);
		$fields['categ']=$this->clean_field($fields['categ'])+0;
		$fields['statut']=$this->clean_field($fields['statut']+0);
		$fields['lang']=$this->clean_field($fields['lang']);
		$fields['cstat']=$this->clean_field($fields['cstat'])+0;
		$fields['type_abt']=$this->clean_field($fields['type_abt']+0);
		$fields['ldap']=$this->clean_field($fields['ldap'])+0;*/
		$fields['sexe']+=0;
		$fields['location']+=0;
		$fields['categ']+=0;
		$fields['statut']+=0;
		$fields['lang']+=0;
		$fields['cstat']+=0;
		$fields['type_abt']+=0;
		$fields['ldap']+=0;
		
		// vérification des champs obligatoie, et des Id...		
		if(!$fields['nom']) return $this->build_error( "Le champ 'nom' n'est pas renseigné.");
		if(!$fields['categ']) return $this->build_error( "Le champ 'categ' n'est pas renseigné.");
		if(!$fields['statut']) return $this->build_error( "Le champ 'statut' n'est pas renseigné.");
		if(!$fields['cstat']) return $this->build_error( "Le champ 'cstat' n'est pas renseigné.");
		
		$q="select idstatut from empr_statut where idstatut='".$fields['statut']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'statut' = ".$fields['statut']." n'est pas un Id présent dans la base de donnée.");
		
		$q="select idcode from empr_codestat where idcode='".$fields['cstat']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'cstat' = ".$fields['cstat']." n'est pas un Id présent dans la base de donnée.");
		
		$q="select id_categ_empr from empr_categ where id_categ_empr='".$fields['categ']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'categ' = ".$fields['categ']." n'est pas un Id présent dans la base de donnée.");
		
		if($fields['location']){			
			$q="select idlocation from docs_location where idlocation='".$fields['location']."' limit 1";
			$r = mysql_query($q, $dbh);
			if (!mysql_num_rows($r)) $fields['location']=0;
		}
		if (!$fields['location']) {
			$loca = mysql_query("select min(idlocation) as idlocation from docs_location", $dbh);
			$locaid = mysql_fetch_object($loca);
			$fields['location'] = $locaid->idlocation;
		}
		if($fields['mail'])if(!emprunteur::mail_is_valid($fields['mail'])) return $this->build_error( "Le champ 'mail' = ".$fields['mail']." n'est pas un mail valide.");
		
		if(!$fields['sexe']) $fields['sexe']=0;
		if(!$fields['lang']) $fields['lang']=$lang;		
		
		$requete = "INSERT INTO empr SET ";
		$requete .= "empr_cb='".addslashes($empr_cb)."', ";
		$requete .= "empr_nom='".addslashes($fields['nom'])."', ";
		$requete .= "empr_prenom='".addslashes($fields['prenom'])."', ";
		$requete .= "empr_adr1='".addslashes($fields['adr1'])."', ";
		$requete .= "empr_adr2='".addslashes($fields['adr2'])."', ";
		$requete .= "empr_cp='".addslashes($fields['cp'])."', ";
		$requete .= "empr_ville='".addslashes($fields['ville'])."', ";
		$requete .= "empr_pays='".addslashes($fields['pays'])."', ";
		$requete .= "empr_mail='".addslashes($fields['mail'])."', ";
		$requete .= "empr_tel1='".addslashes($fields['tel1'])."', ";
		$requete .= "empr_sms='".addslashes($fields['sms'])."', ";
		$requete .= "empr_tel2='".addslashes($fields['tel2'])."', ";
		$requete .= "empr_prof='".addslashes($fields['prof'])."', ";
		$requete .= "empr_year='".addslashes($fields['birth'])."', ";
		$requete .= "empr_categ='".$fields['categ']."', ";
		$requete .= "empr_statut='".$fields['statut']."', ";
		$requete .= "empr_lang='".addslashes($fields['lang'])."', ";
			
		if ($fields['date_adhesion']=="") $requete .= "empr_date_adhesion=CURRENT_DATE(), "; else $requete .= "empr_date_adhesion='".addslashes($fields['date_adhesion'])."', ";
		if (($fields['date_expiration']=="") or ($fields['date_expiration']==$fields['date_adhesion'])) {
			/* AJOUTER ICI LE CALCUL EN FONCTION DE LA CATEGORIE */
			$rqt_empr_categ = "select duree_adhesion from empr_categ where id_categ_empr = ".$fields['categ']." ";
			$res_empr_categ = mysql_query($rqt_empr_categ, $dbh);
			$empr_categ = mysql_fetch_object($res_empr_categ);
			
			if($fields['date_adhesion'])	$rqt_date = "select date_add('".addslashes($fields['date_adhesion'])."', INTERVAL ".$empr_categ->duree_adhesion." DAY) as date_expiration " ;
			else $rqt_date = "select date_add(CURRENT_DATE(), INTERVAL ".$empr_categ->duree_adhesion." DAY) as date_expiration " ;
			$resultatdate=mysql_query($rqt_date);
			$resdate=mysql_fetch_object($resultatdate);
			$requete .= "empr_date_expiration='".$resdate->date_expiration."', ";
		
		} else $requete .= "empr_date_expiration='".$fields['date_adhesion']."', ";
		$requete .= "empr_codestat=".$fields['cstat'].", ";
		$requete .= "empr_creation=CURRENT_TIMESTAMP(), ";
		$requete .= "empr_modif=CURRENT_DATE(), ";
		$requete .= "empr_sexe='".$fields['sexe']."', ";
		$requete .= "empr_msg='".addslashes($fields['msg'])."', ";
		$requete .= "empr_login='".addslashes($fields['login'])."', ";
		$requete .= "empr_location='".$fields['location']."', ";
		
		// ldap - MaxMan
		if ($fields['ldap']){
			$requete .= "empr_ldap='1', ";
			$fields['pwd']="";
		}else{
			$requete .= "empr_ldap='0', ";
		}
		
		//Gestion financière
		if (($pmb_gestion_abonnement==2)&&($pmb_gestion_financiere)) {
			$requete.="type_abt='".$fields['type_abt']."', ";
		} else {
			$requete.="type_abt=0, ";
		}
		
		if ($fields['pwd']!="") $requete .= "empr_password='".addslashes($fields['pwd'])."' ";
		else $requete .= "empr_password='".addslashes($fields['birth'])."' ";
		
		$res = mysql_query($requete, $dbh);
		if(!$res)return $this->build_error( "Impossible de créer le lecteur: $requete");				
		
		// on récupère l'id du de l'emprunteur
		$empr_id = mysql_insert_id($dbh);
		
		if(is_array($fields['pperso_list'])){
			if(count($fields['pperso_list'])){
				$p_perso = new parametres_perso("empr");
				foreach($fields['pperso_list'] as $pp){
					$name=$pp["name"];
					global $$name;
					$$name=$pp["value_list"];	
					
				}
				$p_perso->rec_fields_perso($empr_id);
			}
		}
		if(is_array($fields['groupe_list'])) emprunteur::rec_groupe_empr($empr_id, $fields['groupe_list']) ;
		emprunteur::ins_lect_categ_dsi($empr_id, $fields['categ'], 0) ;
		if (($pmb_gestion_financiere)&&($pmb_gestion_abonnement))	emprunteur::rec_abonnement($empr_id,$type_abt,$fields['categ']);	
		
		$result = array(
			'empr_id' => $empr_id,
			'empr_cb' => $empr_cb
		);
		
		return $this->build_ok($result);			
	}	
	
	function update_empr($empr_cb='', $empr_id=0, $fields) {
		global $dbh,$lang;
		global $msg;
		global $charset;
		global $pmb_num_carte_auto,$deflt2docs_location,$pmb_gestion_abonnement,$pmb_gestion_financiere;
	
		$empr_cb=$this->clean_field((string)$empr_cb);
		$empr_id += 0;
		if (!$empr_id && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
		
		if($empr_id)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '".addslashes($empr_cb)."' ";
		
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu: 'empr_cb' = $empr_cb .");
			
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
			
		// clean des entrées
		$fields=$this->clean_fields($fields);/*
		$fields['nom']=$this->clean_field($fields['nom']);
		$fields['prenom']=$this->clean_field($fields['prenom']);
		$fields['adr1']=$this->clean_field($fields['adr1']);
		$fields['adr2']=$this->clean_field($fields['adr2']);
		$fields['cp']=$this->clean_field($fields['cp']);
		$fields['ville']=$this->clean_field($fields['ville']);
		$fields['pays']=$this->clean_field($fields['pays']);
		$fields['mail']=$this->clean_field($fields['mail']);
		$fields['tel1']=$this->clean_field($fields['tel1']);
		$fields['sms']=$this->clean_field($fields['sms'])+0;
		$fields['tel2']=$this->clean_field($fields['tel2']);
		$fields['prof']=$this->clean_field($fields['prof']);
		$fields['birth']=$this->clean_field($fields['birth']);
		$fields['sexe']=$this->clean_field($fields['sexe'])+0;
		$fields['login']=$this->clean_field($fields['login']);
		$fields['pwd']=$this->clean_field($fields['pwd']);
		$fields['msg']=$this->clean_field($fields['msg']);
		$fields['lang']=$this->clean_field($fields['lang']);
		$fields['location']=$this->clean_field($fields['location']+0);
		$fields['date_adhesion']=$this->clean_field($fields['date_adhesion']);
		$fields['date_expiration']=$this->clean_field($fields['date_expiration']);
		$fields['categ']=$this->clean_field($fields['categ'])+0;
		$fields['statut']=$this->clean_field($fields['statut']+0);
		$fields['lang']=$this->clean_field($fields['lang']);
		$fields['cstat']=$this->clean_field($fields['cstat'])+0;
		$fields['type_abt']=$this->clean_field($fields['type_abt']+0);
		$fields['ldap']=$this->clean_field($fields['ldap'])+0;*/
		$fields['sexe']+=0;
		$fields['location']+=0;
		$fields['categ']+=0;
		$fields['statut']+=0;
		$fields['lang']+=0;
		$fields['cstat']+=0;
		$fields['type_abt']+=0;
		$fields['ldap']+=0;
		
	
		// vérification des champs obligatoires
		if(!$fields['nom']) return $this->build_error( "Le champ 'nom' n'est pas renseigné.");
		if(!$fields['categ']) return $this->build_error( "Le champ 'categ' n'est pas renseigné.");
		if(!$fields['statut']) return $this->build_error( "Le champ 'statut' n'est pas renseigné.");
		if(!$fields['cstat']) return $this->build_error( "Le champ 'cstat' n'est pas renseigné.");
	
		// vérification des relations
		$q="select idstatut from empr_statut where idstatut='".$fields['statut']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'statut' = ".$fields['statut']." n'est pas un Id présent dans la base de donnée.");
	
		$q="select idcode from empr_codestat where idcode='".$fields['cstat']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'cstat' = ".$fields['cstat']." n'est pas un Id présent dans la base de donnée.");
	
		$q="select id_categ_empr from empr_categ where id_categ_empr='".$fields['categ']."' limit 1";
		$r = mysql_query($q, $dbh);
		if (!mysql_num_rows($r)) return $this->build_error( "Le champ 'categ' = ".$fields['categ']." n'est pas un Id présent dans la base de donnée.");
	
		if($fields['location']){
			$q="select idlocation from docs_location where idlocation='".$fields['location']."' limit 1";
			$r = mysql_query($q, $dbh);
			if (!mysql_num_rows($r)) $fields['location']=0;
		}
		if (!$fields['location']) {
			$loca = mysql_query("select min(idlocation) as idlocation from docs_location", $dbh);
			$locaid = mysql_fetch_object($loca);
			$fields['location'] = $locaid->idlocation;
		}
		if($fields['mail'])if(!emprunteur::mail_is_valid($fields['mail'])) return $this->build_error( "Le champ 'mail' = ".$fields['mail']." n'est pas un mail valide.");
	
		if(!$fields['sexe']) $fields['sexe']=0;
		if(!$fields['lang']) $fields['lang']=$lang;
	
		$requete = "UPDATE empr SET ";
		$requete .= "empr_nom='".addslashes($fields['nom'])."', ";
		$requete .= "empr_prenom='".addslashes($fields['prenom'])."', ";
		$requete .= "empr_adr1='".addslashes($fields['adr1'])."', ";
		$requete .= "empr_adr2='".addslashes($fields['adr2'])."', ";
		$requete .= "empr_cp='".addslashes($fields['cp'])."', ";
		$requete .= "empr_ville='".addslashes($fields['ville'])."', ";
		$requete .= "empr_pays='".addslashes($fields['pays'])."', ";
		$requete .= "empr_mail='".addslashes($fields['mail'])."', ";
		$requete .= "empr_tel1='".addslashes($fields['tel1'])."', ";
		$requete .= "empr_sms='".addslashes($fields['sms'])."', ";
		$requete .= "empr_tel2='".addslashes($fields['tel2'])."', ";
		$requete .= "empr_prof='".addslashes($fields['prof'])."', ";
		$requete .= "empr_year='".addslashes($fields['birth'])."', ";
		$requete .= "empr_categ='".$fields['categ']."', ";
		$requete .= "empr_statut='".$fields['statut']."', ";
		$requete .= "empr_lang='".addslashes($fields['lang'])."', ";
			
		if ($fields['date_adhesion']=="") $requete .= "empr_date_adhesion=CURRENT_DATE(), "; else $requete .= "empr_date_adhesion='".addslashes($fields['date_adhesion'])."', ";
		if (($fields['date_expiration']=="") or ($fields['date_expiration']==$fields['date_adhesion'])) {
			/* AJOUTER ICI LE CALCUL EN FONCTION DE LA CATEGORIE */
			$rqt_empr_categ = "select duree_adhesion from empr_categ where id_categ_empr = ".$fields['categ']." ";
			$res_empr_categ = mysql_query($rqt_empr_categ, $dbh);
			$empr_categ = mysql_fetch_object($res_empr_categ);
				
			if($fields['date_adhesion'])	$rqt_date = "select date_add('".addslashes($fields['date_adhesion'])."', INTERVAL ".$empr_categ->duree_adhesion." DAY) as date_expiration " ;
			else $rqt_date = "select date_add(CURRENT_DATE(), INTERVAL ".$empr_categ->duree_adhesion." DAY) as date_expiration " ;
			$resultatdate=mysql_query($rqt_date);
			$resdate=mysql_fetch_object($resultatdate);
			$requete .= "empr_date_expiration='".$resdate->date_expiration."', ";
	
		} else $requete .= "empr_date_expiration='".$fields['date_adhesion']."', ";
		$requete .= "empr_codestat=".$fields['cstat'].", ";
		$requete .= "empr_modif=CURRENT_DATE(), ";
		$requete .= "empr_sexe='".$fields['sexe']."', ";
		$requete .= "empr_msg='".addslashes($fields['msg'])."', ";
		$requete .= "empr_login='".addslashes($fields['login'])."', ";
		$requete .= "empr_location='".$fields['location']."', ";
	
		// ldap - MaxMan
		if ($fields['ldap']){
			$requete .= "empr_ldap='1', ";
			$fields['pwd']="";
		}else{
			$requete .= "empr_ldap='0', ";
		}
	
		//Gestion financière
		if (($pmb_gestion_abonnement==2)&&($pmb_gestion_financiere)) {
			$requete.="type_abt='".$fields['type_abt']."', ";
		} else {
			$requete.="type_abt=0, ";
		}
	
		if ($fields['pwd']!="") $requete .= "empr_password='".addslashes($fields['pwd'])."' ";
		else $requete .= "empr_password='".addslashes($fields['birth'])."' ";
	
		$requete .= " WHERE id_empr=".$empr_id." limit 1";
		
		$res = mysql_query($requete, $dbh);
		if(!$res)return $this->build_error( "Impossible de modifier le lecteur: $requete");
		
		if(is_array($fields['pperso_list'])){
			$p_perso = new parametres_perso("empr");
			foreach($fields['pperso_list'] as $pp){
				$name=$pp["name"];
				global $$name;
				$$name=$pp["value_list"];
	
			}
			$p_perso->rec_fields_perso($empr_id);
		}
	
		if(is_array($fields['groupe_list'])) emprunteur::rec_groupe_empr($empr_id, $fields['groupe_list']) ;
		emprunteur::ins_lect_categ_dsi($empr_id, $fields['categ'], 0) ;
		if (($pmb_gestion_financiere)&&($pmb_gestion_abonnement))	emprunteur::rec_abonnement($empr_id,$type_abt,$fields['categ']);
	
		return $this->build_ok();			
	}
	
	function statut_list() {
		global $dbh;
		global $msg;
		global $charset;	
	
		$sql = "SELECT * FROM empr_statut ORDER BY statut_libelle ";	
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->idstatut;
			$infos[$i]["libelle"]=$res_info->statut_libelle;
			$infos[$i]["allow_loan"]=$res_info->allow_loan;
			$infos[$i]["allow_loan_hist"]=$res_info->allow_loan_hist;
			$infos[$i]["allow_book"]=$res_info->allow_book;
			$infos[$i]["allow_opac"]=$res_info->allow_opac;
			$infos[$i]["allow_dsi"]=$res_info->allow_dsi;
			$infos[$i]["allow_dsi_priv"]=$res_info->allow_dsi_priv;
			$infos[$i]["allow_sugg"]=$res_info->allow_sugg;
			$infos[$i]["allow_dema"]=$res_info->allow_dema;
			$infos[$i]["allow_prol"]=$res_info->allow_prol;
			$infos[$i]["allow_avis"]=$res_info->allow_avis;
			$infos[$i]["allow_tag"]=$res_info->allow_tag;
			$infos[$i]["allow_pwd"]=$res_info->allow_pwd;
			$infos[$i]["allow_liste_lecture"]=$res_info->allow_liste_lecture;
			$infos[$i]["allow_self_checkout"]=$res_info->allow_self_checkout;
			$infos[$i]["allow_self_checkin"]=$res_info->allow_self_checkin;
			$infos[$i]["allow_serialcirc"]=$res_info->allow_serialcirc;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	
	function categ_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM empr_categ ORDER BY libelle ";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->id_categ_empr;
			$infos[$i]["libelle"]=$res_info->libelle;
			$infos[$i]["duree_adhesion"]=$res_info->duree_adhesion;
			$infos[$i]["tarif_abt"]=$res_info->tarif_abt;
			$infos[$i]["age_min"]=$res_info->age_min;
			$infos[$i]["age_max"]=$res_info->age_max;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function codestat_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM empr_codestat ORDER BY libelle ";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->idcode;
			$infos[$i]["libelle"]=$res_info->libelle;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function groupe_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM groupe ORDER BY libelle_groupe ";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->id_groupe;
			$infos[$i]["libelle"]=$res_info->libelle_groupe;
			$infos[$i]["resp_groupe"]=$res_info->resp_groupe;
			$infos[$i]["lettre_rappel"]=$res_info->lettre_rappel;
			$infos[$i]["mail_rappel"]=$res_info->id_groupe;
			$infos[$i]["lettre_rappel_show_nomgroup"]=$res_info->lettre_rappel_show_nomgroup;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function abt_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM type_abts ORDER BY type_abt_libelle";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->id_type_abt;
			$infos[$i]["libelle"]=$res_info->type_abt_libelle;
			$infos[$i]["prepay"]=$res_info->prepay;
			$infos[$i]["prepay_deflt_mnt"]=$res_info->prepay_deflt_mnt;
			$infos[$i]["tarif"]=$res_info->tarif;
			$infos[$i]["commentaire"]=$res_info->commentaire;
			$infos[$i]["caution"]=$res_info->caution;
			$infos[$i]["localisations"]=$res_info->localisations;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function location_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM docs_location ORDER BY location_libelle";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->idlocation;
			$infos[$i]["libelle"]=$res_info->location_libelle;		
			$infos[$i]["visible_opac"]=$res_info->location_visible_opac;	
			$infos[$i]["codage_import"]=$res_info->locdoc_codage_import;			
			$infos[$i]["name"]=$res_info->name;
			$infos[$i]["adr1"]=$res_info->adr1;
			$infos[$i]["adr2"]=$res_info->adr2;
			$infos[$i]["cp"]=$res_info->cp;
			$infos[$i]["town"]=$res_info->town;
			$infos[$i]["state"]=$res_info->state;
			$infos[$i]["country"]=$res_info->country;
			$infos[$i]["phone"]=$res_info->phone;
			$infos[$i]["email"]=$res_info->email;
			$infos[$i]["website"]=$res_info->website;
			$infos[$i]["logo"]=$res_info->logo;
			$infos[$i]["commentaire"]=$res_info->commentaire;
			$infos[$i]["surloc_num"]=$res_info->surloc_num;
			$infos[$i]["surloc_used"]=$res_info->surloc_used;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function surlocation_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM sur_location ORDER BY surloc_libelle";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->surloc_id;
			$infos[$i]["libelle"]=$res_info->surloc_libelle;
			$infos[$i]["visible_opac"]=$res_info->surloc_visible_opac;
			$infos[$i]["name"]=$res_info->surloc_name;
			$infos[$i]["adr1"]=$res_info->surloc_adr1;
			$infos[$i]["adr2"]=$res_info->surloc_adr2;
			$infos[$i]["cp"]=$res_info->surloc_cp;
			$infos[$i]["town"]=$res_info->surloc_town;
			$infos[$i]["state"]=$res_info->surloc_state;
			$infos[$i]["country"]=$res_info->surloc_country;
			$infos[$i]["phone"]=$res_info->surloc_phone;
			$infos[$i]["email"]=$res_info->surloc_email;
			$infos[$i]["website"]=$res_info->surloc_website;
			$infos[$i]["logo"]=$res_info->surloc_logo;
			$infos[$i]["commentaire"]=$res_info->surloc_commentaire;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function caddie_list() {
		global $dbh;
		global $msg;
		global $charset;
	
		$sql = "SELECT * FROM empr_caddie ORDER BY name";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["id"]=$res_info->idemprcaddie;
			$infos[$i]["libelle"]=$res_info->name;
			$infos[$i]["comment"]=$res_info->comment;
			$infos[$i]["autorisations"]=$res_info->autorisations;
			$sql_count = "SELECT id_empr FROM empr_caddie_content,empr where empr_caddie_id =".$res_info->idemprcaddie." and id_empr= object_id ";
			$res_count = mysql_query($sql_count);
			$infos[$i]["nb_empr"]=mysql_num_rows($res_count);
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function caddie_empr_list($caddie_id) {
		global $dbh;
		global $msg;
		global $charset;
				
		$caddie_id+=0;
		$sql = "SELECT id_empr, empr_cb,flag FROM empr_caddie_content,empr where empr_caddie_id =$caddie_id and id_empr= object_id ORDER BY empr_nom";
		$res = mysql_query($sql);
		$i=0;
		$infos= array();
		while( $res_info=mysql_fetch_object($res)){
			$infos[$i]["empr_id"]=$res_info->id_empr;
			$infos[$i]["empr_cb"]=$res_info->empr_cb;
			if($res_info->flag)	$infos[$i]["flag"]=1;
			else $infos[$i]["flag"]=0;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function procédure_exec($id) {
		global $dbh;
		global $msg;
		global $charset;
		global $PMBuserid;
		
		$id+=0;
		if ($PMBuserid!=1)
			$where=" and (autorisations='$PMBuserid' or autorisations like '$PMBuserid %' or autorisations like '% $PMBuserid %' or autorisations like '% $PMBuserid') ";
		
		$requete = "SELECT * FROM empr_caddie_procs WHERE idproc=$id $where ";
		$res = mysql_query($requete, $dbh);
		$nbr_lignes = mysql_num_rows($res);
		$row = mysql_fetch_row($res);
		$idp = $row[0];
		$name = $row[2];
		$commentaire = $row[4];
		if (!$code)
			$code = $row[3];
		$commentaire = $row[4];
		
		return $this->build_ok($infos);
	}
	
	function caddie_pointage_raz($caddie_id) {
		global $dbh;
		global $msg;
		global $charset;
		global $PMBuserid;
		$caddie_id+=0;
		$myCart = new empr_caddie($caddie_id);
		print aff_empr_cart_titre ($myCart);
		$droit = verif_droit_empr_caddie($caddie_id) ;
		if ($droit) $myCart->depointe_items();
	
		return $this->build_ok($infos);
	}			
	function add_in_caddie($empr_cb='', $empr_id=0, $caddie_id) {
		global $dbh,$lang;
		global $msg;
		global $charset;
	
		$empr_cb=$this->clean_field((string)$empr_cb);
		$empr_id += 0;
		if (!$empr_id && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
	
		if($empr_id)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '".addslashes($empr_cb)."' ";
	
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu: 'empr_cb' = $empr_cb .");
				
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
		
		$caddie_id+=0;
		$sql = "SELECT idemprcaddie FROM empr_caddie WHERE idemprcaddie = $caddie_id";
		$res = mysql_query($sql);
		if (!$res) return $this->build_error( "Panier inconnu: 'caddie_id' = $caddie_id .",array('sql'=>$sql));
		
		$sql = "INSERT INTO empr_caddie_content SET empr_caddie_id=$caddie_id, object_id=$empr_id";
		mysql_query($sql);
		return $this->build_ok();
	}	
	
	function pointe_in_caddie($empr_cb='', $empr_id=0, $caddie_id) {
		global $dbh,$lang;
		global $msg;
		global $charset;
	
		$empr_cb=$this->clean_field((string)$empr_cb);
		$empr_id += 0;
		if (!$empr_id && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
	
		if($empr_id)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '".addslashes($empr_cb)."' ";
	
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu: 'empr_cb' = $empr_cb .");
	
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
	
		$caddie_id+=0;
		$sql = "SELECT idemprcaddie FROM empr_caddie WHERE idemprcaddie = $caddie_id";
		$res = mysql_query($sql);
		if (!$res) return $this->build_error( "Panier inconnu: 'caddie_id' = $caddie_id .");
	
		$sql = "update empr_caddie_content SET flag='1' where object_id=$empr_id and empr_caddie_id=$caddie_id limit 1";
		mysql_query($sql);
		return $this->build_ok();
	}	
	
	function is_in_caddie($empr_cb='', $empr_id=0, $caddie_id) {
		global $dbh,$lang;
		global $msg;
		global $charset;
	
		$empr_cb=$this->clean_field((string)$empr_cb);
		$empr_id += 0;
		if (!$empr_id && $empr_cb=='') return $this->build_error( "idempr et empr_cb vide.");
	
		if($empr_id)	$where=" id_empr = $empr_id ";
		else $where=" empr_cb = '".addslashes($empr_cb)."' ";
	
		if (!$idempr && $empr_cb) {
			$sql = "SELECT id_empr, empr_cb FROM empr WHERE $where";
			$res = mysql_query($sql);
			if (!$res) return $this->build_error( "Lecteur inconnu: 'empr_cb' = $empr_cb .");
	
			$empr_res = mysql_fetch_object($res);
			$empr_id=$empr_res->id_empr;
		}
	
		$caddie_id+=0;
		$sql = "SELECT idemprcaddie FROM empr_caddie WHERE idemprcaddie = $caddie_id";
		$res = mysql_query($sql);
		if (!$res) return $this->build_error( "Panier inconnu: 'caddie_id' = $caddie_id .");
		
		$sql = "SELECT * FROM empr_caddie_content WHERE empr_caddie_id = $caddie_id";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)) {
			$res_info=mysql_fetch_object($res);
			return $this->build_ok(array(
				'status' => true
			));
		}			
		return $this->build_ok(array(
			'status' => false
		));				
	}
		
	function lang_list() {	
		global $include_path;
		global $msg;
		global $charset;
		
		$la = new XMLlist("$include_path/messages/languages.xml", 0);
		$la->analyser();
		$languages = $la->table;
		$i=0;
		foreach($languages as $codelang => $libelle){
			$infos[$i]["codelang"]=$codelang;
			$infos[$i]["libelle"]=$libelle;
			$i++;
		}
		return $this->build_ok($infos);
	}
	
	function clean_field($field,$addslashes=0){
		global $charset;
		if ($this->proxy_parent->input_charset!='utf-8' && $charset == 'utf-8') {
			$field = utf8_encode($field);
		}
		else if ($this->proxy_parent->input_charset=='utf-8' && $charset != 'utf-8') {
			$field = utf8_decode($field);
		}
		if($addslashes==1) $field=addslashes($field);
		
		return $field;
	}	
	
	function clean_fields($field){
		array_walk_recursive($field, function(&$data,$key,$input_charset) {
			global $charset;
			if ($input_charset!='utf-8' && $charset == 'utf-8') {
				$data = utf8_encode($data);
			}
			else if ($input_charset=='utf-8' && $charset != 'utf-8') {
				$data = utf8_decode($data);
			}
			
		},$this->proxy_parent->input_charset);
		return $field;
	}
	
	function build_ok($result=array(),$msg=""){	
		array_walk_recursive($result, function(&$data) {
			$data = utf8_normalize($data);
		});
		return  array(
			'status' => true,
			'status_msg' => utf8_normalize($msg),
			'data'=>$result
		);
	}
	
	function build_error($msg){
		return  array(
			'status' => false,
			'status_msg' => utf8_normalize($msg)
		);
	}
}
