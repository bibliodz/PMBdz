<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: opac_view.class.php,v 1.14 2014-02-11 13:02:59 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes de gestion des vues Opac

// inclusions principales
require_once("$include_path/misc.inc.php");
require_once("$class_path/search.class.php");
require_once("$class_path/XMLlist.class.php");
require_once("$class_path/param_subst.class.php");
require_once("$class_path/opac_filters.class.php");
require_once("$class_path/quotas.class.php");

class opac_view {
	
	public $id=0; 							//Identifiant de la vue courante
	public $id_empr=0; 						//Identifiant de l'emprunteur
	public $name=''; 						//Nom de la vue
	public $requete=''; 					//Requete associee a la vue
	public $comment=''; 					//Commentaire de la vue
	public $param_subst; 					//Classe d'�crasement des param�tres substitu�s pour cette vue
	public $opac_filters; 					//Filtres des �l�ments de l'OPAC
	public $view_list_empr_default=0; 		//Identifiant de la vue par d�faut pour l'utilisateur courant
	public $opac_views_list=array(); 		//Tableau des vues visibles en OPAC (opac_view_visible=1)
	public $selector; 						//Liste de s�lection des vues visibles en OPAC (opac_view_visible=1)
	public $search_class;
	public $opac_view_wo_query = 0;			//pas de recherche mc associ�e
	

	// constructeur
	function opac_view($id=0,$id_empr=0) {
		// si id, allez chercher les infos dans la base
		$this->id_empr = $id_empr;
		if($id === "default_opac"){
			$this->id = 0;
			if (!$this->check_right()){
				$this->build_env();
			}
		}else{
			$this->id = $id;
			$this->build_env();
		}
	}

	/*
	 * g�n�re l'environnement pour l'emprunteur
	 */
	function build_env(){
		global $dbh;
		
		if(!count($this->opac_views_list)){
			$this->list_views();
		}
		if(!$this->id || ($this->id && !$this->check_right())){
			$this->id = $this->view_list_empr_default;
		}
		if($this->id && $this->check_right()){
			$myQuery_defaut = mysql_query("SELECT * FROM opac_views WHERE opac_view_id=".$this->id, $dbh);
			if(mysql_num_rows($myQuery_defaut)){
				$r_defaut= mysql_fetch_object($myQuery_defaut);
				$this->id=	$r_defaut->opac_view_id;
				$this->name=$r_defaut->opac_view_name;
				$this->requete=$r_defaut->opac_view_query;
				$this->comment=$r_defaut->opac_view_comment;
				$this->param_subst=new param_subst("opac", "opac_view",$this->id);
				$this->opac_filters=new opac_filters($this->id);
				if (!$this->requete) {
					$this->opac_view_wo_query=1;
				}
				$this->regen();
			}
		}
	}

	/*
	 * regenere la recherche de restriction de la vue si necessaire
	 */
	function regen() {
		global $dbh;
		if ($this->id && !$this->opac_view_wo_query) {
			$q = "select if((unix_timestamp(now()) - ifnull(unix_timestamp(opac_view_last_gen),0) - opac_view_ttl)>0,1,0) as opac_view_valid from opac_views where opac_view_id=".$this->id." ";
			$r = mysql_query($q, $dbh);
			if (mysql_result($r,0,0)==1) {

				$q="update opac_views set opac_view_last_gen=now() where opac_view_id=".$this->id." ";
				mysql_query($q, $dbh);

				$q="truncate table opac_view_notices_".$this->id;
				mysql_query($q, $dbh);

				$this->search_class = new search("search_fields_gestion");
				$this->search_class->push();
				$this->search_class->unserialize_search($this->requete);
				$table=$this->search_class->make_search();
				$this->search_class->destroy_global_env();
				$this->search_class->pull();
				$q="INSERT INTO opac_view_notices_".$this->id." (opac_view_num_notice) select notice_id from $table ";
				mysql_query($q, $dbh);
				mysql_query("drop table $table");

			}
		}
	}

	/*
	 * Liste les vues disponibles
	 */
	function list_views(){
		global $dbh;
		global $pmb_opac_view_activate;
		global $include_path;
		global $lang;

		$this->opac_views_list=array();
		$this->view_list_empr_default=0;
		//on reprend...
		if ($this->id_empr){
			$req="SELECT * FROM opac_views, opac_views_empr  where opac_view_visible!=0 and emprview_view_num=opac_view_id and emprview_empr_num=".$this->id_empr;
			$myQuery = mysql_query($req, $dbh);
			if(mysql_num_rows($myQuery)){
				while($r = mysql_fetch_object($myQuery)){
					if($r->emprview_default) $this->view_list_empr_default=$r->opac_view_id;
					$this->opac_views_list[] = $r->opac_view_id;
				}
			}
			//on regarde l'OPAC classique
			$query = "select emprview_view_num,emprview_default from opac_views_empr where emprview_view_num = 0 and emprview_empr_num = ".$this->id_empr;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				$r = mysql_fetch_object($result);
				$this->opac_views_list[] = 0;
				if($r->emprview_default) $this->view_list_empr_default=0;
			}
			
		}
		if(count($this->opac_views_list) == 0){
			if($pmb_opac_view_activate == 2){
				$qt = new quota("OPAC_VIEW",$include_path."/quotas/own/".$lang."/opac_views.xml");
				$struct = array(
					'READER' => ($this->id_empr ? $this->id_empr : 0)
				);
				if($this->id_empr){
					$struct["READER"] = ($this->id_empr ? $this->id_empr : 0) ;
					$val = $qt->get_quota_value($struct);
				}else{
					$tmp = $qt->apply_conflict(array(""));
					$val = $tmp['VALUE'];
				}
				$values = unserialize($val);
				$this->opac_views_list = $values['allowed'];
				$this->view_list_empr_default = $values['default'];
			}else if(!$this->id_empr){
				$this->opac_views_list[] = 0;
				$req="SELECT * FROM opac_views where opac_view_visible=1";
				$myQuery = mysql_query($req, $dbh);
				if(mysql_num_rows($myQuery)){
					while($r = mysql_fetch_object($myQuery)){
						if($r->emprview_default) $this->view_list_empr_default=$r->opac_view_id;
						/*else if(!$this->id_empr && !$this->view_list_empr_default){
							//si pas d'emprunteur, on met la premi�re vue trouv�e par d�faut
							$this->view_list_empr_default = $r->opac_view_id;
						}*/
						$this->opac_views_list[]=$r->opac_view_id;
					}
				}
			}
		}
	}

	/*
	 * V�rifie la disponibilit� de la vue
	 */
	function check_right(){
		if(!count($this->opac_views_list))
			$this->list_views();
		if(in_array($this->id,$this->opac_views_list))
			return true;
		else return false;
	}

	function set_parameters(){
		if($this->id){
			$this->param_subst->set_parameters();
		}
	}


	function get_list($name='', $value_selected=0) {
		global $dbh,$charset;
		if ($this->id_empr) $myQuery = mysql_query("SELECT * FROM opac_views left join opac_views_empr on (emprview_view_num=opac_view_id and emprview_empr_num=$this->id_empr) where opac_view_visible!=0 order by opac_view_name ", $dbh);
		else $myQuery = mysql_query("SELECT * FROM opac_views where opac_view_visible=1 order by opac_view_name ", $dbh);

		$selector = "<select name='$name' id='$name'>";
		if(mysql_num_rows($myQuery)){
			while(($r=mysql_fetch_object($myQuery))) {
				$selector .= "<option value='".$r->opac_view_id."'";
				$r->opac_view_id == $value_selected ? $selector .= " selected='selected'>" : $selector .= ">";
		 		$selector .= htmlentities($r->opac_view_name,ENT_QUOTES, $charset)."</option>";
			}
		}
		$selector .= "</select>";
		$this->selector=$selector;

		return $selector;
	}
} // fin d�finition classe