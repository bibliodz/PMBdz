<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority_import.class.php,v 1.5 2013-11-28 09:30:09 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/notice_authority.class.php");
require_once($class_path."/notice_authority_serie.class.php");
require_once($include_path."/misc.inc.php");

/*
 * Classe d'import par d�faut, d�rivable pour personnalisation
 * appel d�fini par le param�tre $pmb_import_modele_authorities
 */

class authority_import {
	var $notice;
	var $create_link;
	var $create_link_spec;
	var $force_update;
	var $use_rejected;
	var $use_associated;
	var $use_parallel;
	
	var $authority_id;
	var $num_origin;
	var $id_authority_source;
	var $splitted;
	
	/*
	 * Constructeur
	 */
	public function __construct($notice,$create_link=0,$create_link_spec=0,$force_update=0,$id_thesaurus,$rejected=true,$associated=true,$parallel=false){
		$this->notice = $notice;
		$this->create_link = $create_link;
		$this->create_link_spec = $create_link_spec;
		$this->force_update = $force_update;
		$this->use_rejected = $rejected;
		$this->use_associated = $associated;
		$this->use_parallel = $parallel;
		$this->id_thesaurus = $id_thesaurus;
		$this->splitted = false;
	}
	
	/*
	 * Pour avoir le num�ro d'autorit� 
	 */
	public static function format_authority_number($authority_number,$size=14){
		if($authority_number){ 
			if(strlen($authority_number) == $size){
				$number = str_replace("FRBNF","",$authority_number);
				return substr($number,0,-1);	
			}else{
				return $authority_number;
			}
		}else{
			return "";
		}
	}	
	
	/*
	 * M�thode analysant le contenu UNIMARC pour en ressortir les infos exploitables dans PMB
	 */
	public function get_informations(){
		$this->notice->get_informations($this->use_rejected,$this->use_associated,$this->use_parallel);
	}
	
	/*
	 * A surcharger
	 */
	public function get_informations_callback(){
		
	}
		
	/*
	 * A surcharger
	 */
	public function import_callback(){
		
	}
	
	public function import_link(){
		switch($this->create_link_spec){
			//seulement ce qui existe dans PMB ou va exister � la fin de l'import!
			case 1 :
			//on commence par stockter le fait que la notice courante est dans le fichier...
				$query = "insert into authorities_import set 
					num_authority = ".$this->authority_id.",
					authority_number = '".$this->notice->common_data['authority_number']."',
					authority_type = '".$this->notice->type."'";
				mysql_query($query);
				
				//on regarde si elle n'as pas d�j� �t� cit�s en lien...
				$query = "select * from authorities_import_links where authority_type = '".$this->notice->type."' and authority_number = '".$this->notice->common_data['authority_number']."'";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					while($row = mysql_fetch_object($result)){
						$data = array(
							'type_authority' => $row->authority_type_from,
							'link_code' => $row->link_type
						);
						
						$from_code = $this->get_authority_link_code($data['type_authority']);
						$to_code = $this->get_authority_link_code($this->notice->type); 
						$link_code = $data['link_code'];
						if($link_code == "") $link_code = "z";
						if($from_code!= 0 && $to_code!= 0){
							//on regarde si un lien existe pas d�j� entre les 2...
							$query = "select * from aut_link where aut_link_from = ".$from_code." and aut_link_from_num = ".$row->num_authority_from." and aut_link_to = ".$to_code." and aut_link_to_num = ".$this->authority_id." and aut_link_type= '".$link_code."'";
							$result = mysql_query($query);
							if(mysql_num_rows($result) == 0){
								$query = "insert into aut_link set 
								aut_link_from = ".$from_code.",
								aut_link_from_num = ".$row->num_authority_from.",
								aut_link_to = ".$to_code.",
								aut_link_to_num = ".$this->authority_id.",
								aut_link_type= '1',
								aut_link_comment='".$row->comment."'";
								mysql_query($query);
							}
						}
						//on a cr�e les liens associ�s � cette notice, on supprime la r�f�rence...
						$query = "delete from authorities_import_links where authority_type = '".$this->notice->type."' and authority_number = '".$this->notice->common_data['authority_number']."'";
						mysql_query($query);
					}
				}
				
				//pour les rejet�s
				for($i=0 ; $i<count($this->notice->rejected_forms) ; $i++){
					$link_id=0;
					if(!$this->splitted){
						$link_id = $this->notice->check_if_exists($this->notice->rejected_forms[$i]);
					}
					if($link_id==0){
						$link_id = $this->save_alternative_form($this->notice->rejected_forms[$i],true);
					}
					if($link_id != 0){
						$this->update_rejected_form($link_id,$this->notice->rejected_forms[$i]);
						if($this->notice->rejected_forms[$i]['authority_number'] != ""){
							$query = "insert into authorities_sources set 
								num_authority = ".$link_id.",
								authority_number = '".$this->notice->format_authority_number($this->notice->rejected_forms[$i]['authority_number'])."',
								authority_type = '".$this->notice->rejected_forms[$i]['type_authority']."',
								num_origin_authority = ".$this->num_origin.",
								import_date = now()";
							$result = mysql_query($query);
						}
					}
				}

				//on traite maintenant les liens
				// pour les voir/voir aussi
				for($i=0 ; $i<count($this->notice->associated_forms) ; $i++){
					//si pas de num�ro, on peut pas rep�rer...
					if($this->notice->associated_forms[$i]['authority_number']){
						//on commence par regarder si on l'a d�j� crois� dans le fichier...
						$query ="select num_authority from authorities_import where authority_number = '".$this->format_authority_number($this->notice->associated_forms[$i]['authority_number'])."' and authority_type = '".$this->notice->associated_forms[$i]['type_authority']."'";
						$result = mysql_query($query);
						if(mysql_num_rows($result)){
							// on l'a crois�, on fait le lien....
							$row = mysql_fetch_object($result);
							if($row->num_authority!=0){
								$this->update_associated_form($row->num_authority,$this->notice->associated_forms[$i]);	
							}
						}else{
							// on l'a pas crois�, on marque qu'il existe un lien
							$query = "insert into authorities_import_links set 
								authority_type = '".$this->notice->associated_forms[$i]['type_authority']."',
								authority_number = '".$this->notice->associated_forms[$i]['authority_number']."',
								link_type = '".$this->notice->associated_forms[$i]['link_code']."',
								num_authority_from  = ".$this->authority_id. ",
								authority_type_from = '".$this->notice->type."',
								comment = '".$this->notice->associated_forms[$i]['comment']."'";
							mysql_query($query);
						}
					}	
				}
				break;
			// on reprend tout...
			case 2 :
				//pour les rejet�s
				for($i=0 ; $i<count($this->notice->rejected_forms) ; $i++){
					$link_id=0;
					if(!$this->splitted){
						$link_id = $this->notice->check_if_exists($this->notice->rejected_forms[$i]);
					}
					if($link_id==0){
						$link_id = $this->save_alternative_form($this->notice->rejected_forms[$i],true);
					}
					if($link_id != 0){
						$this->update_rejected_form($link_id,$this->notice->rejected_forms[$i]);
						if($this->notice->rejected_forms[$i]['authority_number'] != ""){
							$query = "insert into authorities_sources set 
								num_authority = ".$link_id.",
								authority_number = '".$this->notice->format_authority_number($this->notice->rejected_forms[$i]['authority_number'])."',
								authority_type = '".$this->notice->rejected_forms[$i]['type_authority']."',
								num_origin_authority = ".$this->num_origin.",
								import_date = now()";
							$result = mysql_query($query);
						}
					}
				}
				//pour les voir/voir aussi
				for($i=0 ; $i<count($this->notice->associated_forms) ; $i++){
					if($this->notice->associated_forms[$i]['authority_number'] != ""){
						//on regarde si on l'as pas d�j� crois�...
						$query = "select num_authority from authorities_sources where authority_number = '".$this->notice->associated_forms[$i]['authority_number']."' and authority_type = '".$this->notice->associated_forms[$i]['type_authority']."' and num_origin_authority = ".$this->num_origin;
						$result = mysql_query($query);
						if(mysql_num_rows($result)){
							$link_id = mysql_result($result,0,0);
						}else{
							$link_id = $this->save_alternative_form($this->notice->associated_forms[$i]);
							if($link_id!=0){
								$query = "insert into authorities_sources set 
									num_authority = ".$link_id.",
									authority_number = '".$this->notice->format_authority_number($this->notice->associated_forms[$i]['authority_number'])."',
									authority_type = '".$this->notice->associated_forms[$i]['type_authority']."',
									num_origin_authority = ".$this->num_origin.",
									import_date = now()";
								$result = mysql_query($query);
							}
						}
					}
					if(!$link_id){
						$link_id = $this->save_alternative_form($this->notice->associated_forms[$i]);
					} 
					if($link_id!=0){
						$this->update_associated_form($link_id,$this->notice->associated_forms[$i]);
					}
				}
				break;
		}
	}
	
	public function save_authority(){
		global $msg;
		
		//on regarde l'autorit� PMB est associ� � plusieurs num�ro d'autorit�...
		$force_creation = false;
		if($this->authority_id){
			$id = $this->authority_id;
		}else{
			$id = $this->notice->check_if_exists($this->notice->specifics_data,$this->id_thesaurus);
		}
		if($id!=0){
			$query = "select * from authorities_sources where num_authority = ".$id." and authority_type = '".$this->notice->type."' and num_origin_authority != ".$this->num_origin;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				while($row = mysql_fetch_object($result)){
					if($row->authority_favorite == 1){
						$force_creation = true;
						$this->authority_id = 0;
						break;
					}
				}
			} 
		}
		if($id==0 && !$force_creation){	
			switch($this->notice->type){
				case "author" :
					$this->authority_id = auteur::import($this->notice->specifics_data);
					break;
				case "uniform_title" :
					$this->authority_id = titre_uniforme::import($this->notice->specifics_data);
					break;
				case "collection" :
					$this->authority_id = collection::import($this->notice->specifics_data);
					if($this->authority_id!=0 && $this->notice->specifics_data['subcollections']){
						for ( $i=0 ; $i<count($this->notice->specifics_data['subcollections']) ; $i++){
							$this->notice->specifics_data['subcollections'][$i]['coll_parent'] = $this->authority_id;
							$subcoll_id = subcollection::check_if_exists($this->notice->specifics_data['subcollections'][$i]);
							if($subcoll_id!=0 && $this->notice->specifics_data['subcollections'][$i]['authority_number']){
								$query = "insert into authorities_sources set 
									num_authority = ".$subcoll_id.",
									authority_number = '".$this->notice->specifics_data['subcollections'][$i]['authority_number']."',	
									authority_type = 'subcollection',
									num_origin_authority = ".$this->num_origin.",
									authority_favorite = 0,
									import_date = now()";
								mysql_query($query);
							}
						}
					}
					break;
				case "subcollection" :
					$this->authority_id = subcollection::import($this->notice->specifics_data);
					if($this->authority_id!=0 && $this->notice->specifics_data['collection'] && $this->notice->specifics_data['collection']['authority_number']){
						$coll_id = collection::check_if_exists($this->notice->specifics_data['collection']);
						$query = "insert into authorities_sources set 
							num_authority = ".$coll_id.",
							authority_number = '".$this->notice->specifics_data['collection']['authority_number']."',	
							authority_type = 'collection',
							num_origin_authority = ".$this->num_origin.",
							authority_favorite = 0,
							import_date = now()";
						mysql_query($query);
					}
					break;
				case "category" :
					$this->authority_id = category::import($this->notice->specifics_data,$this->id_thesaurus,$this->get_parent_category(),$this->notice->common_data['lang']);
					break;
				default :
					//	on fait rien...
					break;
			}
			if($this->authority_id!=0){
				$query = "insert into authorities_sources set 
					num_authority = ".$this->authority_id.",
					authority_number = '".$this->notice->common_data['authority_number']."',	
					authority_type = '".$this->notice->type."',
					num_origin_authority = ".$this->num_origin.",
					authority_favorite = 1,
					import_date = now(),
					update_date = now()";
				mysql_query($query);
			}
		}else{
			$data = addslashes_array($this->notice->specifics_data);
			switch($this->notice->type){
				case "author" :
					$authority = new auteur($this->authority_id);
					break;
				case "uniform_title" :
					 $authority = new titre_uniforme($this->authority_id);
					break;
				case "collection" :
					 $authority = new collection($this->authority_id);
					break;
				case "subcollection" :
					 $authority = new subcollection($this->authority_id);
					break;
				case "category" :
					$authority = new category($this->authority_id);
					break;
				default :
				//	on fait rien...
					break;
			}
			if($authority && !$authority->import_denied){
				if($this->notice->type == "category"){
					$result=$authority->update($data,$this->id_thesaurus,$this->get_parent_category(),$this->notice->common_data['lang']);
				}else{
					$result=$authority->update($data,$force_creation);
				}
				if($result){
					if($this->authority_id){
						$query = "update authorities_sources set 
							authority_favorite = 1, 
							update_date = now() 
						where id_authority_source = ".$this->id_authority_source;
					}else{
						$this->authority_id = $authority->id;
						$query = "insert into authorities_sources set 
							num_authority = ".$this->authority_id.",
							authority_number = '".$this->notice->common_data['authority_number']."',	
							authority_type = '".$this->notice->type."',
							num_origin_authority = ".$this->num_origin.",
							authority_favorite = 1,
							import_date = now(),
							update_date = now()";
					}
					mysql_query($query);
				}
			}else{
				$sql_log = mysql_query("insert into error_log (error_origin, error_text) values ('import_authorities_".addslashes(SESSid).".inc', '".$this->notice->common_data['authority_number']." || ".$this->notice->common_data['source']['origin']." || ".$msg['import_authorities_log_authority_locked']."') ") ;
				return false;
			}
		}		
		return $this->authority_id;	
	}
	
	public function save_alternative_form($data,$rejected= false){
		if($rejected && $this->splitted){
			// on doit �tre sur que les formes sont r�cr�e !
			$id = 0;
			switch($this->notice->type){
				case "author" :
					$authority = new auteur($id);
					break;
				case "uniform_title" :
					 $authority = new titre_uniforme($id);
					break;
				case "collection" :
					 $authority = new collection($id);
					break;
				case "subcollection" :
					 $authority = new subcollection($id);
					break;
				case "category" :
					$num_parent = 0;
					$query = "select id_noeud from noeuds where autorite = 'ORPHELINS' and num_thesaurus = ".$this->id_thesaurus;
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$num_parent = mysql_result($result,0,0);
					}
					$authority = new category($id);
					break;
				default :
				//	on fait rien...
					break;
			}
			if($authority && !$authority->import_denied){
				$data = addslashes_array($data);
				if($this->notice->type == "category"){
					$result=$authority->update($data,$this->id_thesaurus,$num_parent,$this->notice->common_data['lang']);
				}else{
					$result=$authority->update($data,true);
				}
				if($result){
					$id = $authority->id;
				}
			}
		}else{
			switch($data['type_authority']){
				case "author" :
					$id = auteur::import($data);
					break;
				case "uniform_title" :
					$id = titre_uniforme::import($data);
					break;
				case "collection" :
					$id = collection::import($data);
					break;
				case "subcollection" :
					$id = subcollection::import($data);
					break;
				case "category" :
					$num_parent = 0;
					if($rejected){
						$query = "select id_noeud from noeuds where autorite = 'ORPHELINS' and num_thesaurus = ".$this->id_thesaurus;
						$result = mysql_query($query);
						if(mysql_num_rows($result)){
							$num_parent = mysql_result($result,0,0);
						}
					}else{
						$num_parent = $this->get_parent_category();
					}
					$id = category::import($data,$this->id_thesaurus,$num_parent,$this->notice->common_data['lang']);
					break;
				default : 
					$id=0;
					break;
			}
		}
		return $id;
	}
	
	public function update_rejected_form($rejected_id,$data){
		if($rejected_id != 0 && $this->notice->type == $data['type_authority']){
			switch($data['type_authority']){
				// Forme associ�e - Nom de Personne
				case "author" :
					$query = "update authors set author_see = ".$this->authority_id." where author_id = ".$rejected_id;
					$result = mysql_query($query);
					if(!$result) return false;
					break;
				// Forme associ�e - Titre Uniforme
				case "uniform_title" :
					//pas de forme rejet� pour un titre uniforme dans PMB, si ca se pr�sente, on d�place en lien..
					//on ajoute/modifie le lien...
					$from_code = $this->get_authority_link_code($data['type_authority']);
					$to_code = $this->get_authority_link_code($this->notice->type); 
					if($from_code!= 0 && $to_code!= 0){
						//on regarde si un lien existe pas d�j� entre les 2...
						$query = "select * from aut_link where aut_link_from = ".$from_code." and aut_link_from_num = ".$rejected_id." and aut_link_to = ".$to_code." and aut_link_to_num = ".$this->authority_id." and aut_link_type= '1'";
						$result = mysql_query($query);
						if(mysql_num_rows($result) == 0){
							$query = "insert into aut_link set 
								aut_link_from = ".$from_code.",
								aut_link_from_num = ".$rejected_id.",
								aut_link_to = ".$to_code.",
								aut_link_to_num = ".$this->authority_id.",
								aut_link_type= '1',
								aut_link_comment = '".$data['comment']."'";	
							return mysql_query($query);
						}
					}
					break;
				case "category" :
					$query = "update noeuds set num_renvoi_voir = ".$this->authority_id." where id_noeud = ".$rejected_id;
					$result = mysql_query($query);
					if(!$result) return false;
					break;
			}
			return true;
		}
		return false;
	}

	public function update_associated_form($associated_id,$data){
		global $lang;
		
		if($associated_id!= 0){
			$from_code = $this->get_authority_link_code($this->notice->type);
			$to_code = $this->get_authority_link_code($data['type_authority']); 
			$link_code = $data['link_code'];
			if($link_code == "") $link_code = "z";

			if($from_code!= 0 && $to_code!= 0){
				//les cat�gories ont leurs syst�mes de voir aussi interne...
				if($data['type_authority'] == "category" && $this->notice->type == "category" && $link_code == "z"){
					//on regarde si le lien existe pas d�j� entre les 2...
					$query = "select num_noeud_orig from voir_aussi where num_noeud_orig = ".$this->authority_id." and num_noeud_dest = ".$associated_id;
					$result = mysql_query($query);
					if(!mysql_num_rows($result)){
						$query = "insert into voir_aussi set num_noeud_orig = ".$this->authority_id.", num_noeud_dest = ".$associated_id.", langue = '".$lang."', comment_voir_aussi = '".addslashes($data['comment'])."'";
						return mysql_query($query);
					}
				}else{ 
					//lien entre autorit� classique
					//on regarde si un lien existe pas d�j� entre les 2...
					$query = "select * from aut_link where aut_link_from = ".$from_code." and aut_link_from_num = ".$this->authority_id." and aut_link_to = ".$to_code." and aut_link_to_num = ".$associated_id." and aut_link_type= '".$link_code."'";
					$result = mysql_query($query);
					if(mysql_num_rows($result) == 0){
						$query = "insert into aut_link set 
							aut_link_from = ".$from_code.",
							aut_link_from_num = ".$this->authority_id.",
							aut_link_to = ".$to_code.",
							aut_link_to_num = ".$associated_id.",
							aut_link_type= '".$link_code."',
							aut_link_comment = '".$data['comment']."'";
						return mysql_query($query);
					}
				}
			}
		}
		return false;
	}

	public function get_authority_link_code($type){
		switch($type){
			case "author" :
				$authority_type_code = AUT_TABLE_AUTHORS;
				break;
			case "uniform_title" :	
				$authority_type_code = AUT_TABLE_TITRES_UNIFORMES;
				break;
			case "category" :	
				$authority_type_code = AUT_TABLE_CATEG;
				break;
			case "collection" :	
				$authority_type_code = AUT_TABLE_COLLECTIONS;
				break;
			case "subcollection" :	
				$authority_type_code = AUT_TABLE_SUB_COLLECTIONS;
				break;
		}
		return $authority_type_code;
	}
	
	/*
	 * On scinde la cr�ation, mise � jour et s�paration d'autorit�
	 */
	public function import(){
		global $msg;
		
		$this->num_origin = origin::import("authorities",$this->notice->common_data['source']);
		//on commence par regarder si le num�ro d'autorit� est pr�sent dans la table authorities_sources...
		if($this->notice->common_data['authority_number']){
			$query = "select num_authority from authorities_sources where authority_number = '".$this->notice->common_data['authority_number']."' and authority_type = '".$this->notice->type."' and num_origin_authority = ".$this->num_origin;
			$result = mysql_query($query);
			if(mysql_num_rows($result)){
				//existe dans la table authority_source
				$row = mysql_fetch_object($result);
				$id_authority = $row->num_authority;
			}
			if($id_authority == 0){
				//existe pas, on regarde si l'autorit� existe dans PMB
				$id_authority = $this->notice->check_if_exists($this->notice->specifics_data);
			}
		}
		if($id_authority != 0){
			//on regarde si l'autorit� PMB est d�j� import�e dans un autre source authorities_sources
			$query = "select * from authorities_sources where num_authority = ".$id_authority." and authority_type= '".$this->notice->type."' and authority_favorite = 1 and num_origin_authority != ".$this->num_origin;
			$result =  mysql_query($query);
			if(mysql_num_rows($result)){
				//d�j� import�e avec une autre source, on la s�pare
				$this->split_authority($id_authority);
			}else{
				//jamais cit�e, on la met � jour
				$this->update_authority($id_authority);
			}
		}else{
			$this->create_authority();
		}

		if($this->authority_id != 0 && $this->create_link != 0){
			$this->import_link();
		}
	}

	public function create_authority(){
		switch($this->notice->type){
				case "author" :
					$this->authority_id = auteur::import($this->notice->specifics_data);
					break;
				case "uniform_title" :
					$this->authority_id = titre_uniforme::import($this->notice->specifics_data);
					break;
				case "collection" :
					$this->authority_id = collection::import($this->notice->specifics_data);
					if($this->authority_id!=0 && $this->notice->specifics_data['subcollections']){
						for ( $i=0 ; $i<count($this->notice->specifics_data['subcollections']) ; $i++){
							$this->notice->specifics_data['subcollections'][$i]['coll_parent'] = $this->authority_id;
							$subcoll_id = subcollection::check_if_exists($this->notice->specifics_data['subcollections'][$i]);
							if($subcoll_id!=0 && $this->notice->specifics_data['subcollections'][$i]['authority_number']){
								$query = "insert into authorities_sources set 
									num_authority = ".$subcoll_id.",
									authority_number = '".$this->notice->specifics_data['subcollections'][$i]['authority_number']."',	
									authority_type = 'subcollection',
									num_origin_authority = ".$this->num_origin.",
									authority_favorite = 0,
									import_date = now()";
								mysql_query($query);
							}
						}
					}
					break;
				case "subcollection" :
					$this->authority_id = subcollection::import($this->notice->specifics_data);
					if($this->authority_id!=0 && $this->notice->specifics_data['collection'] && $this->notice->specifics_data['collection']['authority_number']){
						$coll_id = collection::check_if_exists($this->notice->specifics_data['collection']);
						$query = "insert into authorities_sources set 
							num_authority = ".$coll_id.",
							authority_number = '".$this->notice->specifics_data['collection']['authority_number']."',	
							authority_type = 'collection',
							num_origin_authority = ".$this->num_origin.",
							authority_favorite = 0,
							import_date = now()";
						mysql_query($query);
					}
					break;
				case "category" :
					$this->authority_id = category::import($this->notice->specifics_data,$this->id_thesaurus,$this->get_parent_category(),$this->notice->common_data['lang']);
					break;
				default :
					//	on fait rien...
					break;
		}
		if($this->authority_id){
			$query = "insert into authorities_sources set 
				num_authority = ".$this->authority_id.",
				authority_number = '".$this->notice->common_data['authority_number']."',	
				authority_type = '".$this->notice->type."',
				num_origin_authority = ".$this->num_origin.",
				authority_favorite = 1,
				import_date = now(),
				update_date = now()";
			mysql_query($query);
		}else{
			return false;
		}
	}
	
	public function update_authority($id_authority){
		$query = "select * from authorities_sources where num_authority = ".$id_authority." and authority_type= '".$this->notice->type."' and num_origin_authority = ".$this->num_origin;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$infos = mysql_fetch_object($result);
		}else{
			$need_update = false;
		}
		if(is_object($infos) && ($infos->update_date!=0 && $this->force_update == 0)){
			//on regarde la date de la notice dans le notice en cours d'import...
			if(strlen($this->notice->common_data['source']['date']) == 8){
				$query = "select datediff('".substr($this->notice->common_data['source']['date'],0,4)."-".substr($this->notice->common_data['source']['date'],4,2)."-".substr($this->notice->common_data['source']['date'],6,2)."','".$infos->update_date."')";
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					if(mysql_result($result,0,0)>0){
						$need_update = true;
					}
				}
			}else{
				$need_update = true;
			}			
		}else{	
			$need_update = true;
		}
		if($need_update){
			switch($this->notice->type){
				case "author" :
					$authority = new auteur($id_authority);
					break;
				case "uniform_title" :
					 $authority = new titre_uniforme($id_authority);
					break;
				case "collection" :
					 $authority = new collection($id_authority);
					break;
				case "subcollection" :
					 $authority = new subcollection($id_authority);
					break;
				case "category" :
					$authority = new category($id_authority);
					break;
				default :
				//	on fait rien...
					break;
			}
			if($authority && !$authority->import_denied){
				if($this->notice->type == "category"){
					$data = $this->notice->specifics_data;
					$result=$authority->update($data,0,0,$this->notice->common_data['lang']);
				}else{
					$data = addslashes_array($this->notice->specifics_data);
					$result=$authority->update($data);
				}
				if($result){
					$this->authority_id = $authority->id;
					if($infos->id_authority_source){
						$query = "update authorities_sources set 
							authority_favorite = 1,
							update_date = now() 
						where id_authority_source = ".$infos->id_authority_source;
					}else{
						$query = "insert into authorities_sources set 
							num_authority = ".$this->authority_id.",
							authority_number = '".$this->notice->common_data['authority_number']."',	
							authority_type = '".$this->notice->type."',
							num_origin_authority = ".$this->num_origin.",
							authority_favorite = 1,
							import_date = now(),
							update_date = now()";
					}
					return mysql_query($query);
				}
			}	
		}	
		return false;
	}
	
	public function split_authority($id_authority){
		$query = "select * from authorities_sources where num_authority = ".$id_authority." and authority_type= '".$this->notice->type."' and num_origin_authority = ".$this->num_origin;
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			$infos = mysql_fetch_object($result);
		}
			
		$data = addslashes_array($this->notice->specifics_data);
		switch($this->notice->type){
			case "author" :
				$authority = new auteur(0);
				break;
			case "uniform_title" :
				 $authority = new titre_uniforme(0);
				break;
			case "collection" :
				 $authority = new collection(0);
				break;
			case "subcollection" :
				 $authority = new subcollection(0);
				break;
			case "category" :
				//si on split une cat�gorie, on le fait dans la m�me branche...
				$query = "select num_parent from noeuds where id_noeud = ".$id_authority;
				$result = mysql_query($query);
				if(mysql_num_rows($result)){
					$num_parent = mysql_result($result,0,0);
				}else{
					$num_parent = $this->get_parent_category();
				}
				$authority = new category(0);
				break;
			default :
			//	on fait rien...
				break;
		}
		if($authority && !$authority->import_denied){
			if($this->notice->type == "category"){
				$result=$authority->update($data,$this->id_thesaurus,$num_parent,$this->notice->common_data['lang']);
			}else{
				$result=$authority->update($data,true);
			}
			if($result){
				$this->splitted = true;
				$this->authority_id = $authority->id;
				if($infos->id_authority_source){
					$query = "update authorities_sources set 
						num_authority_source = ".$this->authority_id.",
						authority_favorite = 1,
						update_date = now() 
					where id_authority_source = ".$infos->id_authority_source;
					mysql_query($query);
					//on doit aussi g�rer la s�paration dans les notices
					$query = "select * from notices_authorities_sources where num_authority_source = ".$infos->id_authority_source;
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						while($row = mysql_fetch_object($result)){
							switch($this->notice->type){
								case "author" :
									$query = "update responsability set responsability_author = ".$this->authority_id." where responsability_author = ".$info->num_authority." and responsability_notice = ".$row->num_notice;
									break;
								case "uniform_title" :
									$query = "update notices_titres_uniformes set ntu_num_tu = ".$this->authority_id." where ntu_num_tu = ".$info->num_authority." and ntu_num_notice = ".$row->num_notice;
									break;
								case "collection" :
									$query = "update notices set coll_id = ".$this->authority_id.", subcoll_id = 0 where notice_id = ".$row->num_notice;
									break;
								case "subcollection" :
									$query = "update notices set subcoll_id = ".$this->authority_id." where notice_id = ".$row->num_notice;
									break;
								case "category" :
									$query = "update notices_categories set num_noeud = ".$this->authority_id." where notcateg_notice = ".$row->num_notice;
									break;
								default :
									$query = "";
							}
							if($query!=""){
								mysql_query($query);
							}
						}
						return true;
					}							
				}else{
					$query = "insert into authorities_sources set 
						num_authority = ".$this->authority_id.",
						authority_number = '".$this->notice->common_data['authority_number']."',	
						authority_type = '".$this->notice->type."',
						num_origin_authority = ".$this->num_origin.",
						authority_favorite = 1,
						import_date = now(),
						update_date = now()";
					return mysql_query($query);
				}
			}
		}
		return false;	
	}

	protected function get_parent_category(){
		return 0;
	}
}