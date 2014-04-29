<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: explnum.class.php,v 1.59 2014-03-13 14:29:10 mbertin Exp $


if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


require_once($class_path."/zip.class.php");
require_once($class_path."/upload_folder.class.php");
require_once($class_path."/docs_location.class.php");
require_once($include_path."/explnum.inc.php");
require_once($class_path."/indexation_docnum.class.php");
require_once($class_path."/diarization_docnum.class.php");
require_once($class_path."/notice.class.php");
// classe de gestion des exemplaires num�riques

if ( ! defined( 'EXPLNUM_CLASS' ) ) {
  define( 'EXPLNUM_CLASS', 1 );

	class explnum {
		
		var $explnum_id = 0;
		var $explnum_notice = 0;
		var $explnum_bulletin = 0;
		var $explnum_nom = '';
		var $explnum_mimetype = '';
		var $explnum_url = '';
		var $explnum_data = '';
		var $explnum_vignette = ''; 
		var $explnum_statut = '0';
		var $explnum_index = '';
		var $explnum_repertoire = 0;
		var $explnum_path = '';
		var $explnum_nomfichier = '';
		var $explnum_rep_nom ='';
		var $explnum_rep_path ='';
		var $explnum_index_wew ='';
		var $explnum_index_sew ='';
		var $explnum_ext ='';
		var $explnum_location = '';
		var $infos_docnum = array();
		var $params = array();
		var $unzipped_files = array();
		
		// constructeur
		function explnum($id=0, $id_notice=0, $id_bulletin=0) {
			global $dbh, $pmb_indexation_docnum_default;
			$this->unzipped_files = array();
			if ($id) {
		
				$requete = "SELECT explnum_id, explnum_notice, explnum_bulletin, explnum_nom, explnum_mimetype, explnum_extfichier, explnum_url, explnum_data, explnum_vignette, 
				explnum_statut, explnum_index_sew, explnum_index_wew, explnum_repertoire, explnum_nomfichier, explnum_path, repertoire_nom, repertoire_path, group_concat(num_location SEPARATOR ',') as loc
				 FROM explnum left join upload_repertoire on explnum_repertoire=repertoire_id left join explnum_location on num_explnum=explnum_id where explnum_id='$id' group by explnum_id";
				$result = mysql_query($requete, $dbh);
				
				if(mysql_num_rows($result)) {
					$item = mysql_fetch_object($result);
					$this->explnum_id        = $item->explnum_id       ;
					$this->explnum_notice    = $item->explnum_notice   ;
					$this->explnum_bulletin  = $item->explnum_bulletin ;
					$this->explnum_nom       = $item->explnum_nom      ;
					$this->explnum_mimetype  = $item->explnum_mimetype ;
					$this->explnum_url       = $item->explnum_url      ;
					$this->explnum_data      = $item->explnum_data     ;
					$this->explnum_vignette  = $item->explnum_vignette ;
					$this->explnum_statut    = $item->explnum_statut ;
					$this->explnum_index_wew = $item->explnum_index_wew;
					$this->explnum_index_sew = $item->explnum_index_sew;
					$this->explnum_index     = (($item->explnum_index_wew || $item->explnum_index_sew || $pmb_indexation_docnum_default) ? 'checked' : '');
					$this->explnum_repertoire = $item->explnum_repertoire;
					$this->explnum_path = $item->explnum_path;
					$this->explnum_rep_nom = $item->repertoire_nom;
					$this->explnum_rep_path = $item->repertoire_path;
					$this->explnum_nomfichier = $item->explnum_nomfichier;
					$this->explnum_ext = $item->explnum_extfichier;
					$this->explnum_location = $item->loc ? explode(",",$item->loc) : '';
				} else { // rien trouv� en base, on va faire comme pour une cr�ation
						$req = "select repertoire_nom, repertoire_path from  upload_repertoire, users where repertoire_id=deflt_upload_repertoire and username='".SESSlogin."'";
						$res = mysql_query($req,$dbh);
						if(mysql_num_rows($res)){
							$item = mysql_fetch_object($res);
							$this->explnum_rep_nom = $item->repertoire_nom;
							$this->explnum_rep_path = $item->repertoire_path;
						} else {
							$this->explnum_rep_nom = '';
							$this->explnum_rep_path = '';
						}
						$this->explnum_id = 0;
						$this->explnum_notice = $id_notice;
						$this->explnum_bulletin = $id_bulletin;
						$this->explnum_nom = '';
						$this->explnum_mimetype = '';
						$this->explnum_url = '';
						$this->explnum_data = '';
						$this->explnum_vignette  = '' ;
						$this->explnum_statut = '0';
						$this->explnum_index = ($pmb_indexation_docnum_default ? 'checked' : '');
						$this->explnum_repertoire = 0;
						$this->explnum_path = '';
						$this->explnum_nomfichier = '';
						$this->explnum_ext = '';
						$this->explnum_location= '';
				}
				
			} else { // rien de fourni apparemment : cr�ation
				$req = "select repertoire_id, repertoire_nom, repertoire_path from  upload_repertoire, users where repertoire_id=deflt_upload_repertoire and username='".SESSlogin."'";
				$res = mysql_query($req,$dbh);
				if(mysql_num_rows($res)){
					$item = mysql_fetch_object($res);
					$this->explnum_rep_nom = $item->repertoire_nom;
					$this->explnum_rep_path = $item->repertoire_path;
					$this->explnum_repertoire = $item->repertoire_id;
				} else {
					$this->explnum_rep_nom = '';
					$this->explnum_rep_path = '';
					$this->explnum_repertoire = 0;
				}
				$this->explnum_id = $id;
				$this->explnum_notice = $id_notice;
				$this->explnum_bulletin = $id_bulletin;
				$this->explnum_nom = '';
				$this->explnum_mimetype = '';
				$this->explnum_url = '';
				$this->explnum_data = '';
				$this->explnum_vignette  = '' ;
				$this->explnum_statut = '0';
				$this->explnum_index = ($pmb_indexation_docnum_default ? 'checked' : '');
				$this->explnum_path = '';
				$this->explnum_nomfichier='';
				$this->explnum_ext = '';
				$this->explnum_location = '';
			}
		}	
		
		/*
		 * Construction du formulaire
		 */
		function fill_form (&$form, $action, $suppr='') {
			global $charset;
			global $msg,$lang;
			global $pmb_scan_pmbws_client_url,$pmb_scan_pmbws_url;
			global $pmb_indexation_docnum, $dbh, $pmb_explnum_statut;
			global $b_mimetype;
			global $pmb_docnum_in_directory_allow, $pmb_docnum_in_database_allow;
			global $explnum_id;
			global $pmb_diarization_docnum;
			global $base_path;
			
			$form = str_replace('!!action!!', $action, $form);
			$form = str_replace('!!explnum_id!!', $this->explnum_id, $form);
			$form = str_replace('!!bulletin!!', $this->explnum_bulletin, $form);
			$form = str_replace('!!notice!!', $this->explnum_notice, $form);
			$form = str_replace('!!nom!!', htmlentities($this->explnum_nom ,ENT_QUOTES, $charset), $form);
			$form = str_replace('!!url!!', htmlentities($this->explnum_url ,ENT_QUOTES, $charset), $form);
			
			//Gestion de l'interface d'indexation
			if($pmb_indexation_docnum){
				 $checkbox = "<div class='row'>
				 		<input type='checkbox' id='ck_index' value='1' name='ck_index' $this->explnum_index /><label for='ck_index'>$msg[docnum_a_indexer]</label>	
				 	</div>
				 "; 
				 	
				 if($this->explnum_index_sew !='' && $this->explnum_index_wew !=''){
				 	$fct = "
				 	<script> function suppr_index(form){
				 		 if(!form.ck_index.checked) {
				 		 	conf = confirm(\"".$msg['docnum_suppr_index']."\");
				 			return conf;
				 		 } 
				 		 return true;
				 	}</script>
				 	";
				 	$form = str_replace("!!submit_action!!",'return suppr_index(this)',$form);
				 } else {
				 	$fct="";
				 	$form = str_replace("!!submit_action!!","return testing_file(".$this->explnum_id.");",$form);
				 }
				 $form = str_replace('!!ck_indexation!!', $checkbox.$fct, $form);
			} else {
				$form = str_replace("!!ck_indexation!!", "" , $form);
				$form = str_replace("!!submit_action!!","return testing_file(".$this->explnum_id.");",$form);
			}
			
			//Gestion de l'interface de segmentation
			if($pmb_diarization_docnum){
				 $checkbox = "<div class='row'>
				 		<input type='checkbox' id='ck_diarization' value='1' name='ck_diarization' /><label for='ck_diarization'>".$msg['diarize_explnum']."</label>	
				 	</div>
				 ";
				 
				 $form = str_replace('!!ck_diarization!!', $checkbox, $form);
			} else {
				$form = str_replace("!!ck_diarization!!", "" , $form);
			}
			
			//Gestion du scanner
			if (($pmb_scan_pmbws_client_url)&&($pmb_scan_pmbws_url)) {
				$scan_addon="
				<script>function afterscan(format) {
					if (document.explnum.f_fichier) {
						sitxt=document.createElement('span');
						sitxt.setAttribute('id','scanned_image_txt');
						sitxt.className='erreur';
						document.explnum.f_fichier.parentNode.replaceChild(sitxt,document.explnum.f_fichier);
					}
					document.getElementById('scanned_image_txt').innerHTML='".$msg["scan_image_recorded"]."';
					document.getElementById('scanned_image_ext').value=format;
				}</script>
				<input type='button' value='".$msg["scan_button"]."' onClick='openPopUp(\"".$pmb_scan_pmbws_client_url."?scanfield=scanned_image&urlbase=".rawurlencode($pmb_scan_pmbws_url)."&scanform=explnum&callbackimage=afterscan&lang=$lang&charset=$charset\",\"scanWindow\",900,700,0,0,\"scrollbars=yes, resizable=yes\")' class='bouton'/>
				<input type='hidden' name='scanned_image_ext' id='scanned_image_ext' value=''/>
				<input type='hidden' name='scanned_image' value=''/>
				<input type='hidden' id='scanned_texte' name='scanned_texte' value=''/>";
				$form = str_replace('<!-- !!scan_button!! -->',$scan_addon, $form);
			}
			
			// Ajout du bouton d'association s'il y a des segments en base
			$associer = "";
			$fct = "";
			if ($this->explnum_id) {
				$nb = 0;
				$query = "select count(*) as nb from explnum_segments where explnum_segment_explnum_num = ".$this->explnum_id;
				$result = mysql_query($query);
				if ($result && mysql_num_rows($result)) {
					$nb = mysql_fetch_object($result)->nb;
				}
				if ($nb > 0) {
					$associer = "<input type='button' class='bouton' value=\"".$msg['associate_speakers']."\" name='associate_speakers' id='associate_speakers' onClick=\"document.location = '".$base_path."/catalog.php?categ=explnum_associate&explnum_id=".$this->explnum_id."';\" />";
					
					if ($pmb_diarization_docnum) {
						// On ajoute une confirmation pour une deuxi�me segmentation => perte des associations
						$fct = "<script type='text/javascript'>
							function conf_diarize_again() {
								if (document.getElementById('ck_diarization').checked) {
									conf = confirm('".addslashes($msg['explnum_associate_conf_diarize_again'])."');
									if (!conf) {
										document.getElementById('ck_diarization').checked = false;
									}
								}
							}
							
							document.getElementById('ck_diarization').addEventListener('change', conf_diarize_again, false);
						</script>";
					}
				}
			}
			$form = str_replace("!!associate_speakers!!", $associer, $form);
			$form = str_replace("!!fct_conf_diarize_again!!", $fct, $form);
			
			// Ajout du bouton supprimer si modification
			if ($this->explnum_id && $suppr)
				$supprimer = "
					<script type=\"text/javascript\">
					    function confirm_delete() {
		        			result = confirm(\"${msg[314]} ?\");
		        			if(result)
		            			document.location = \"$suppr\";
		    			}
					</script>
					<input type='button' class='bouton' value=\"${msg['63']}\" name='del_ex' id='del_ex' onClick=\"confirm_delete();\" />
					";
			$form = str_replace('!!supprimer!!', $supprimer, $form);
			
			//Gestion du statut de notice
			if ($pmb_explnum_statut=='1') {
				$explnum_statut_form = "&nbsp;<input type='checkbox' id='f_statut_chk' name='f_statut_chk' value='1' ";
				if ($this->explnum_statut=='1') $explnum_statut_form.="checked='checked' ";
				$explnum_statut_form.= "/>&nbsp;<label class='etiquette' for='f_statut_chk'>".htmlentities($msg['explnum_statut_msg'], ENT_QUOTES, $charset)."</label>";
				$form =  str_replace('<!-- explnum_statut -->', $explnum_statut_form, $form);
			}
			
			//Conserver la vignette
			if ($this->explnum_vignette) 
				$form = str_replace('!!vignette_existante!!', "&nbsp;<input type='checkbox' checked='checked' name='conservervignette' id='conservervignette' value='1'>&nbsp;<label for='conservervignette'>".$msg[explnum_conservervignette]."</label>", $form);
			else $form = str_replace('!!vignette_existante!!', '', $form);
			global $_mimetypes_bymimetype_;
			create_tableau_mimetype();
			$selector_mimetype = "<label class='etiquette'>".htmlentities($msg['explnum_mime_label'], ENT_QUOTES, $charset)."</label>&nbsp;<select id='mime_vign' name='mime_vign' >
			<option value=''>".htmlentities($msg['explnum_no_mimetype'], ENT_QUOTES, $charset)."</option>
			";
			foreach($_mimetypes_bymimetype_ as $key=>$val){
				//$selected="";
				//if($this->explnum_mimetype == $key) 
					//$selected = "selected";
				$selector_mimetype .= "<option value='".$key."' $selected >".htmlentities($key, ENT_QUOTES, $charset)."</option>";			
			}
			$selector_mimetype .= "</select>";
			$form = str_replace('!!mimetype_list!!', $selector_mimetype, $form);
			
			
			//Int�gration de la gestion de l'interface de l'upload
			if ($pmb_docnum_in_directory_allow){				
				$div_up = "<div class='row'>";
				if ($pmb_docnum_in_database_allow) $div_up .= "<input type='radio' name='up_place' id='base' value='0' !!check_base!!/> <label for='base'>$msg[upload_repertoire_sql]</label>";
				
				$div_up .= "	<input type='radio' name='up_place' id='upload' value='1' !!check_up!! />
								<label for='upload'>$msg[upload_repertoire_server]
									<input type='text' name='path' id='path' class='saisie-50emr' value='!!path!!' /><input type='button' class='bouton' name='upload_path' id='upload_path' value='...' onclick='upload_openFrame(event)'/>
								</label> 
								<input type='hidden' name='id_rep' id='id_rep' value='!!id_rep!!' /> 
							</div>";
				$form = str_replace('!!div_upload!!',$div_up,$form);
				$up = new upload_folder($this->explnum_repertoire);
				//$nom_chemin = ($up->isHashing() ? $this->explnum_rep_nom : $this->explnum_rep_nom.$this->explnum_path);
				$nom_chemin=$this->explnum_rep_nom;
				if ($up->isHashing()) {
					$nom_chemin.="/";
				} else {
					$nom_chemin.=($this->explnum_path==='' ? "/" : $this->explnum_path);
				}
				$form = str_replace('!!path!!', htmlentities($nom_chemin ,ENT_QUOTES, $charset), $form);
				$form = str_replace('!!id_rep!!', htmlentities($this->explnum_repertoire ,ENT_QUOTES, $charset), $form);
			
				if($this->explnum_rep_nom || $this->isEnUpload()){
					$form = str_replace('!!check_base!!','', $form);
					$form = str_replace('!!check_up!!','checked', $form);
				} else {
					$form = str_replace('!!check_base!!','', $form);
					$form = str_replace('!!check_up!!','checked', $form);
				}
			} else  {
				$form = str_replace('!!div_upload!!','',$form);				
			}		

			//Ajout du selecteur de localisation
			if ($explnum_id) {
				if (!$this->explnum_location) {
					$requete = "select idlocation from docs_location";
					$res = mysql_query($requete);
					$i=0;
					while ($row = mysql_fetch_array($res)) {
						$liste_id[$i] = $row["idlocation"];
						$i++;
					}
				} else {
					$liste_id = $this->explnum_location;
				}
			} else {
				global $deflt_docs_location;			
				$liste_id[0] = $deflt_docs_location;
			}
			
			$docloc = new docs_location();
			$selector_location = $docloc->gen_multiple_combo($liste_id);
			
			$form = str_replace('!!location_explnum!!',"<div class='row'><label class='etiquette'>".htmlentities($msg['empr_location'],ENT_QUOTES,$charset)."</label></div>".$selector_location,$form);
		}
		
		/*
		 * Appel au constructeur du formulaire puis retourne le formulaire cr��
		 */
		function explnum_form ($action, $annuler='', $suppr='') {
			global $explnum_form;
			
			//$action .= '&id='.$this->explnum_id;
		
			$this->fill_form ($explnum_form, $action, $suppr);
			
			// action du bouton annuler
			if(!$annuler)
				// default : retour � la liste des exemplaires
				$annuler = './catalog.php?categ=expl&id='.$this->id_notice;
		
			$explnum_form = str_replace('!!annuler_action!!', $annuler, $explnum_form);
		
			// affichage
			return $explnum_form;
		}
		
		/*
		 * Mise � jour des documents num�riques
		 */
		function mise_a_jour($f_notice, $f_bulletin, $f_nom, $f_url, $retour, $conservervignette, $f_statut_chk){
			global $multi_ck, $base_path;
			
			$this->recuperer_explnum($f_notice, $f_bulletin, $f_nom, $f_url, $retour, $conservervignette, $f_statut_chk);
			if($multi_ck){
				//Gestion multifichier
									
				$this->unzip($base_path."/temp/".$this->infos_docnum["userfile_moved"]);
				if(!is_array($this->unzipped_files) || !count($this->unzipped_files)){//Si la d�compression n'a pas fonctionn�e on reprend le fonctionnement normal
					$this->infos_docnum["nom"] = "-x-x-x-x-";
					$this->analyser_docnum();
					$this->update();
				}else{
					$this->analyse_multifile();	
				}
				if(file_exists($base_path."/temp/".$this->infos_docnum["userfile_moved"])) 
					unlink($base_path."/temp/".$this->infos_docnum["userfile_moved"]);
				
			} else {
				//Gestion normale du fichier
				$this->analyser_docnum();
				$this->update();
			}
			
			if($f_notice){
				// Mise a jour de la table notices_mots_global_index
				notice::majNoticesMotsGlobalIndex($f_notice,"explnum");
			}elseif($f_bulletin){
				// Mise a jour de la table notices_mots_global_index pour toutes les notices en relation avec l'exemplaire
				$req_maj="SELECT bulletin_notice,num_notice FROM bulletins WHERE bulletin_id='".$f_bulletin."'";
				$res_maj=mysql_query($req_maj);
				if($res_maj && mysql_num_rows($res_maj)){
					if($tmp=mysql_result($res_maj,0,0)){//P�riodique
						notice::majNoticesMotsGlobalIndex($tmp,"explnum");
					}
					if($tmp=mysql_result($res_maj,0,1)){//Notice de bulletin
						notice::majNoticesMotsGlobalIndex($tmp,"explnum");
					}
				}
			}
		}
		
		
		/*
		 * Effacement de l'exemplaire num�rique
		 */
		function delete() {
			global $dbh;
			
			if($this->isEnUpload()){
				$up = new upload_folder($this->explnum_repertoire);
				$chemin = str_replace("//","/",$this->explnum_rep_path.$this->explnum_path.$this->explnum_nomfichier);
				$chemin = $up->encoder_chaine($chemin);
				if(file_exists($chemin)) 
					unlink($chemin);				
			}
			$requete = "DELETE FROM explnum WHERE explnum_id=".$this->explnum_id;
			mysql_query($requete, $dbh);
			//on oublie pas la localisation associ�
			$requete = "delete from explnum_location where num_explnum = ".$this->explnum_id;
			mysql_query($requete, $dbh);
			
			// Suppression des segments et locuteurs
			$requete = "delete from explnum_speakers where explnum_speaker_explnum_num = ".$this->explnum_id;
			mysql_query($requete, $dbh);
			
			$requete = "delete from explnum_segments where explnum_segment_explnum_num = ".$this->explnum_id;
			mysql_query($requete, $dbh);
			
			//On recalcule l'index global pour la notice
			if($this->explnum_notice){
				// Mise a jour de la table notices_mots_global_index
				notice::majNoticesMotsGlobalIndex($this->explnum_notice,"explnum");
			}elseif($this->explnum_bulletin){
				// Mise a jour de la table notices_mots_global_index pour toutes les notices en relation avec l'exemplaire
				$req_maj="SELECT bulletin_notice,num_notice FROM bulletins WHERE bulletin_id='".$this->explnum_bulletin."'";
				$res_maj=mysql_query($req_maj);
				if($res_maj && mysql_num_rows($res_maj)){
					if($tmp=mysql_result($res_maj,0,0)){//P�riodique
						notice::majNoticesMotsGlobalIndex($tmp,"explnum");
					}
					if($tmp=mysql_result($res_maj,0,1)){//Notice de bulletin
						notice::majNoticesMotsGlobalIndex($tmp,"explnum");
					}
				}
			}
		}
		
		/*
		 * Mise � jour de l'exemplaire num�rique
		 */
		function update($with_print = true){
			global $dbh, $msg;
			global $current_module, $pmb_explnum_statut;
			global $id_rep, $up_place;
			global $mime_vign;

			$update = false;
			if ($this->explnum_id) {
				$requete = "UPDATE explnum SET ";
				$limiter = " WHERE explnum_id='$this->explnum_id' ";
				$update = true;
			} else {
				$requete = "INSERT INTO explnum SET ";
				$limiter = "";
			}
			if($with_print){
				print "<div class=\"row\"><h1>$msg[explnum_doc_associe]</h1>";			
			}        
			if (!$this->params["erreur"]) {
				$requete .= " explnum_notice='".$this->infos_docnum["notice"]."'";
				$requete .= ", explnum_bulletin='".$this->infos_docnum["bull"]."'";
				$requete .= ", explnum_nom='".$this->infos_docnum["nom"]."'";
				$requete .= ", explnum_url='".$this->infos_docnum["url"]."'";
				if ($this->params["maj_mimetype"])
					$requete .= ", explnum_mimetype='".$this->infos_docnum["mime"]. "' ";
				if ($this->params["maj_data"] ) {
					if(!$this->params["is_upload"])
						$requete .= ", explnum_data='".addslashes($this->infos_docnum["contenu"])."'";
					$requete .= ", explnum_nomfichier='".addslashes($this->infos_docnum["userfile_name"])."'";
					$requete .= ", explnum_extfichier='".addslashes($this->infos_docnum["userfile_ext"])."'";
				}
				if ($this->params["maj_vignette"] && !$this->params["conservervignette"]) {
					$requete .= ", explnum_vignette='".addslashes($this->infos_docnum["contenu_vignette"])."'";
				}
				if ($pmb_explnum_statut=='1') {
					$requete.= ", explnum_statut='".$this->params["statut"]."'";
				}	
				$requete.= ", explnum_repertoire='".(($up_place)?$id_rep:0)."'";
				$requete.= ", explnum_path='".$this->infos_docnum["path"]."'";
				
				$requete .= $limiter;
				
				mysql_query($requete, $dbh) ;
				
				if(!$update)
					$this->explnum_id = mysql_insert_id();
				
				//Indexation du document
				global $pmb_indexation_docnum;							   			
				if($pmb_indexation_docnum){								
					$vign_index = $this->indexer_docnum();
					if(!$mime_vign && !$this->params["conservervignette"] && !$this->infos_docnum["vignette_name"]){
						if($vign_index){
							$req_mime = "update explnum set explnum_vignette='".addslashes($vign_index)."' where explnum_id='".$this->explnum_id."'";
							mysql_query($req_mime,$dbh);
						}else{
							$contenu_vignette = construire_vignette("", "",$this->infos_docnum["url"]);
							if($contenu_vignette){
								$req_mime = "update explnum set explnum_vignette='".addslashes($contenu_vignette)."' where explnum_id='".$this->explnum_id."'";
								mysql_query($req_mime,$dbh);
							}
						}
					}
				}elseif(!$mime_vign && !$this->params["conservervignette"] && !$this->infos_docnum["vignette_name"] && $this->infos_docnum["url"]){//Si pas d'indexation et que je ne force pas la vignette en fonction du mimetype et si j'ai une url
					$contenu_vignette = construire_vignette("", "",$this->infos_docnum["url"]);
					if($contenu_vignette){
						$req_mime = "update explnum set explnum_vignette='".addslashes($contenu_vignette)."' where explnum_id='".$this->explnum_id."'";
						mysql_query($req_mime,$dbh);
					}
				}
				
				// Segmentation du document
				global $pmb_diarization_docnum;
				if ($pmb_diarization_docnum) {
					$this->diarization_docnum();
				}

				//On enregistre la ou les localisations
				global $loc_selector;
				if($update){
					$req = "delete from explnum_location where num_explnum='".$this->explnum_id."'";
					mysql_query($req,$dbh);
				}
				if((count($loc_selector) == 1) && ($loc_selector[0] == -1)){
					//Ne rien faire
					//$req = "select idlocation from docs_location";
					//$res = mysql_query($req,$dbh);
					//while($loc=mysql_fetch_object($res)){
					//	$req = "replace into explnum_location set num_explnum='".$this->explnum_id."', num_location='".$loc->idlocation."'";
					//	mysql_query($req,$dbh); 
					//}
				} else {
					for($i=0;$i<count($loc_selector);$i++){
						$req = "replace into explnum_location set num_explnum='".$this->explnum_id."', num_location='".$loc_selector[$i]."'";
						mysql_query($req,$dbh); 
					}
				}
				
				// on reaffiche l'ISBD
				if($with_print){
					print "<div class='row'><div class='msg-perio'>".$msg['maj_encours']."</div></div>";
				}
				$id_form = md5(microtime());
				if (mysql_error()) {
					if($with_print){
						echo "MySQL error : ".mysql_error() ;
						print "
							<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"".$this->params["retour"]."\" >
								<input type='submit' class='bouton' name=\"id_form\" value=\"Ok\">
								</form>";
						print "</div>";
					}
					exit ;
				}
				if($with_print){
					print "
					<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"".$this->params["retour"]."\" style=\"display:none\">
						<input type=\"hidden\" name=\"id_form\" value=\"$id_form\">
						</form>";
					print "<script type=\"text/javascript\">document.dummy.submit();</script>";
				}
			} else {
				eval("\$bid=\"".$msg['explnum_erreurupload']."\";");
				if($with_print){
					print "<div class='row'><div class='msg-perio'>".$bid."</div></div>";
					print "
					<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"".$this->params["retour"]."\" >
						<input type='submit' class='bouton' name=\"id_form\" value=\"Ok\">
					</form>";
				}
			}
			if($with_print){	
				print "</div>";
			}
		}
		
		/*
		 * Indexation du document
		 */
		function indexer_docnum(){
			global $scanned_texte, $ck_index;
			
			if(!$this->explnum_id && $ck_index){			
				$id_explnum = $this->explnum_id;
				$indexation = new indexation_docnum($id_explnum, $scanned_texte);
				$indexation->indexer();
			} elseif($this->explnum_id && $ck_index){
				$indexation = new indexation_docnum($this->explnum_id, $scanned_texte);
				$indexation->indexer();				
			} elseif($this->explnum_id && !$ck_index && ($this->explnum_index_sew !='' || $this->explnum_index_wew !='')){
				$indexation = new indexation_docnum($this->explnum_id);
				$indexation->desindexer();	
			}
			return $indexation->vignette;	
		}
		
		/**
		 * Segmentation du document
		 */
		protected function diarization_docnum() {
			global $ck_diarization;
			
			if (in_array($this->infos_docnum['mime'], array("audio/mpeg", "audio/ogg", "video/mp4", "video/webm"))) {
				if ($ck_diarization) {
					$diarization = new diarization_docnum($this);
					$diarization->diarize();
				}
			}
		}
		
		/*
		 * Analyse du document
		 */
		function analyser_docnum(){
			global $path, $id_rep, $up_place, $base_path;
			
			$path = stripslashes($path);
			$upfolder = new upload_folder($id_rep);
			if ($this->infos_docnum["fic"]) {
				$is_upload = false;
				$chemin_hasher = "";
				if(($up_place && $path != '')){
					if($upfolder->isHashing()){
						$rep = $upfolder->hachage($this->infos_docnum["userfile_name"]);
						@mkdir($rep);
						$chemin_hasher = $upfolder->formate_path_to_nom($rep);
						$file_name = $rep.$this->infos_docnum["userfile_name"];				
					} else {		
						$file_name = $upfolder->formate_nom_to_path($path).$this->infos_docnum["userfile_name"];
					}	
									
					$chemin = $upfolder->formate_path_to_save($chemin_hasher ? $chemin_hasher : $path);
					$file_name = $upfolder->encoder_chaine($file_name);
					
					$nom_tmp=$this->infos_docnum["userfile_name"];
					$continue=true;
					$compte=1;
					do{
						$query = "select explnum_notice,explnum_id,explnum_bulletin from explnum where explnum_nomfichier = '".addslashes($nom_tmp)."' AND explnum_repertoire='".$id_rep."' AND explnum_path='".addslashes($chemin)."'";
						$result = mysql_query($query);
						if(mysql_num_rows($result) && ((mysql_result($result,0,0) != $this->infos_docnum["notice"]) || (mysql_result($result,0,2) != $this->infos_docnum["bull"]))){//Si j'ai d�j� un document num�rique avec ce fichier pour une autre notice je dois le renommer pour ne pas perdre l'ancien
							if(preg_match("/^(.+)(\..+)$/i",$this->infos_docnum["userfile_name"],$matches)){
								$nom_tmp=$matches[1]."_".$compte.$matches[2];
							}else{
								$nom_tmp=$this->infos_docnum["userfile_name"]."_".$compte;
							}
							$compte++;
						}else{
							if(mysql_num_rows($result) && (!$this->explnum_id || ($this->explnum_id != mysql_result($result,0,1)))){//J'ai d�j� ce fichier pour cette notice et je ne suis pas en modification
								//Je dois enlever l'ancien document num�rique pour ne pas l'avoir en double
								$old_docnum= new explnum(mysql_result($result,0,1));
								$old_docnum->delete();
							}elseif(mysql_num_rows($result)){
							}
							$continue=false;
						}
					}while($continue);
					if($compte != 1){
						$this->infos_docnum["userfile_name"]=$nom_tmp;
						if($upfolder->isHashing()){
							$file_name = $rep.$this->infos_docnum["userfile_name"];	
						}else{
							$file_name = $upfolder->formate_nom_to_path($path).$this->infos_docnum["userfile_name"];
						}
						$file_name = $upfolder->encoder_chaine($file_name);
					}else{
						
					}
					rename($base_path.'/temp/'.$this->infos_docnum["userfile_moved"],$file_name);
					$is_upload = true;
				} else $file_name = $base_path.'/temp/'.$this->infos_docnum["userfile_moved"];
				$fp = fopen($file_name , "r" ) ;
				$contenu = fread ($fp, filesize($file_name));
				if (!$fp || $contenu=="") 
					$this->params["erreur"]++ ;
				fclose ($fp) ;
			}
			
			//Dans le cas d'une modification, on regarde si il y a eu un d�placement du stockage
			if ($this->explnum_id){					
				if($this->isEnBase() && ($up_place && $path != '')){
					$new_path = $this->remove_from_base($path,$upfolder);
					$contenu="";
					if(!$upfolder->isHashing()){
						$chemin = $upfolder->formate_path_to_save($path);
					} else $chemin = $upfolder->formate_path_to_save($upfolder->formate_path_to_nom($new_path));
					$this->params["maj_data"] = true;
				} elseif($this->isEnUpload() && (!$up_place)){
					$contenu = $this->remove_from_upload();
					$id_rep=0;
					$path="";
					$this->params["maj_data"] = true;
				} elseif($this->isEnUpload() && ($up_place && $path)){
					$contenu = "";
					$chemin = $this->change_rep_upload($upfolder, $upfolder->formate_nom_to_path($path));
					if(!$upfolder->isHashing()){
						$chemin = $upfolder->formate_path_to_save($upfolder->formate_path_to_nom($path));
					} else $chemin =  $upfolder->formate_path_to_save($upfolder->formate_path_to_nom($chemin));					
					$this->params["maj_data"] = true;
				}
				 
			}

			if (!$this->infos_docnum["nom"]) {
				if ($this->infos_docnum["userfile_name"]) $this->infos_docnum["nom"] = $this->infos_docnum["userfile_name"] ;
				elseif ($this->infos_docnum["url"]) $this->infos_docnum["nom"] = $this->infos_docnum["url"] ;
				else $this->infos_docnum["nom"] = "-x-x-x-x-" ;
			}
			$this->params["is_upload"] = $is_upload;
			$this->infos_docnum["contenu"] = $contenu;
			$this->infos_docnum["path"] = $chemin;
			if ($this->infos_docnum["userfile_name"] && $this->infos_docnum["userfile_moved"] && file_exists($base_path.'/temp/'.$this->infos_docnum["userfile_moved"])) 
				unlink($base_path.'/temp/'.$this->infos_docnum["userfile_moved"]);
			if ($this->infos_docnum["vignette_name"]) 
				unlink($base_path.'/temp/'.$this->infos_docnum["vignette_moved"]);
			if($this->explnum_id && $this->infos_docnum["userfile_name"] && ($this->infos_docnum["userfile_name"] != $this->explnum_nomfichier)){
				$up = new upload_folder($this->explnum_repertoire);
				$old_file = str_replace('//','/',$this->explnum_rep_path.$this->explnum_path.$this->explnum_nomfichier);
				if(file_exists($old_file))
					unlink($up->encoder_chaine($old_file));
			}
		}
		
		/*
		 * R�cup�re les informations de l'exemplaire � ajouter � la la notice
		 */
		function recuperer_explnum($f_notice, $f_bulletin, $f_nom, $f_url, $retour, $conservervignette=0, $f_statut_chk=0){
			
			global $scanned_image,$scanned_image_ext,$base_path ;
			global $f_new_name, $mime_vign;
			
			$this->infos_docnum = array();
			$this->params = array();
			
			create_tableau_mimetype() ;
			
			$erreur=0;
			$userfile_name = $_FILES['f_fichier']['name'] ;
			$userfile_temp = $_FILES['f_fichier']['tmp_name'] ;
			$userfile_moved = basename($userfile_temp);
			
			$vignette_name = $_FILES['f_vignette']['name'] ;
			$vignette_temp = $_FILES['f_vignette']['tmp_name'] ;
			$vignette_moved = basename($vignette_temp);
			
			$userfile_name = preg_replace("/ |'|\\|\"|\//m", "_", $userfile_name);
			$vignette_name = preg_replace("/ |'|\\|\"|\//m", "_", $vignette_name);
			$contenu_vignette="";
			$userfile_ext = '';
			if ($userfile_name) {
				$userfile_ext = extension_fichier($userfile_name);
			}
			if ($this->explnum_id) {
				// modification
				// si $userfile_name est vide on ne fera pas la maj du data
				if (($scanned_image)||($userfile_name)) {
					//Avant tout, y-a-t-il une image ext�rieure ?
					if ($scanned_image) {
						//Si oui !
						$tmpid=str_replace(" ","_",microtime());
						$fp=@fopen($base_path."/temp/scanned_$tmpid.".$scanned_image_ext,"w+");
						if ($fp) {
							fwrite($fp,base64_decode($scanned_image));
							$nf=1;
							$part_name="scanned_image_".$nf;
							global $$part_name;
							while ($$part_name) {
								fwrite($fp,base64_decode($$part_name));
								$nf++;
								$part_name="scanned_image_".$nf;
								global $$part_name;
							}
							fclose($fp);
							$fic=1;
							$maj_data = 1;
							$userfile_name="scanned_$tmpid.".$scanned_image_ext;
							$userfile_ext=$scanned_image_ext;
							$userfile_moved = $userfile_name;
							$f_url="";
						} else $erreur++;
					} else if ($userfile_name) {
						if (move_uploaded_file($userfile_temp, $base_path.'/temp/'.$userfile_moved)) {					
							$fic=1;
							$f_url="";
							$maj_data = 1;
							move_uploaded_file($vignette_temp, $base_path.'/temp/'.$vignette_moved) ;
							
						} else {
							$erreur++;
						}
					}
					$mimetype = trouve_mimetype($userfile_moved, $userfile_ext) ;
					if (!$mimetype) $mimetype="application/data";
					$maj_mimetype = 1 ;
					if(!$conservervignette && !$mime_vign)	$contenu_vignette = construire_vignette($vignette_moved, $userfile_moved);
					$maj_vignette = 1 ;
				} else {
					if ($vignette_name) {
						move_uploaded_file($vignette_temp,$base_path.'/temp/'.$vignette_moved) ;
						if(!$conservervignette && !$mime_vign)	$contenu_vignette = construire_vignette($vignette_moved, $userfile_moved) ;
						$maj_vignette = 1 ;
					}
					if ($f_url) {
						$mimetype="URL";
						$maj_mimetype = 1 ;
						move_uploaded_file($vignette_temp,$base_path.'/temp/'.$vignette_moved) ;
						if(!$conservervignette && !$mime_vign)	$contenu_vignette = construire_vignette($vignette_moved, $userfile_moved) ;
						$maj_vignette = 1 ;
						$contenu="";
						$maj_data=1 ;
					}
				}
			} else {
				// creation
				//Y-a-t-il une image exterieure ?
				if ($scanned_image) {
					//Si oui !
					$tmpid=str_replace(" ","_",microtime());
					$fp=@fopen($base_path."/temp/scanned_$tmpid.".$scanned_image_ext,"w+");
					if ($fp) {
						fwrite($fp,base64_decode($scanned_image));
						$nf=1;
						$part_name="scanned_image_".$nf;
						global $$part_name;
						while ($$part_name) {
							fwrite($fp,base64_decode($$part_name));
							$nf++;
							$part_name="scanned_image_".$nf;
							global $$part_name;
						}
						fclose($fp);
						$fic=1;
						$maj_data = 1;
						$userfile_name="scanned_$tmpid.".$scanned_image_ext;
						$userfile_ext=$scanned_image_ext;
						$userfile_moved = $userfile_name;
						$f_url="";
					} else $erreur++;
				} else if (move_uploaded_file($userfile_temp, $base_path.'/temp/'.$userfile_moved)) {
					$fic=1;
					$f_url="";
					$maj_data = 1;
				} elseif (!$f_url) $erreur++;
			
				if (!$f_url && !$fic) $erreur++ ; 
				if ($f_url) {
					$mimetype = "URL" ;
				} else {
					$mimetype = trouve_mimetype($userfile_moved,$userfile_ext) ;
					if (!$mimetype) $mimetype="application/data";
				}
				$maj_mimetype = 1 ;
				
				move_uploaded_file($vignette_temp,$base_path.'/temp/'.$vignette_moved) ;
				if(!$mime_vign) $contenu_vignette = construire_vignette($vignette_moved, $userfile_moved);
				$maj_vignette = 1 ;
			}
			
			if($mime_vign && !$conservervignette){
				global $prefix_url_image ;
				if ($prefix_url_image) $tmpprefix_url_image = $prefix_url_image; 
					else $tmpprefix_url_image = "./" ;
				$contenu_vignette = construire_vignette('',$tmpprefix_url_image."images/mimetype/".icone_mimetype($mime_vign, ""));
				if($contenu_vignette) $maj_vignette = 1 ;
			}
			
			//Initialisation des tableaux d'infos
			$this->infos_docnum["mime"] = (($this->explnum_id && !$maj_mimetype) ? $this->explnum_mimetype : $mimetype);
			$this->infos_docnum["nom"] = $f_new_name ? $f_new_name : $f_nom;
			$this->infos_docnum["notice"] = $f_notice;
			$this->infos_docnum["bull"] = $f_bulletin;
			$this->infos_docnum["url"] = $f_url;
			$this->infos_docnum["fic"] = $fic;
			$this->infos_docnum["contenu_vignette"] = $contenu_vignette;
			$this->infos_docnum["userfile_name"] = (($this->explnum_id && !$userfile_name) ? $this->explnum_nomfichier : ($f_new_name ? $f_new_name : $userfile_name));
			$this->infos_docnum["userfile_ext"] = (($this->explnum_id && !$userfile_ext) ? $this->explnum_ext : $userfile_ext);
			$this->infos_docnum["userfile_moved"] = $userfile_moved;
			$this->infos_docnum["vignette_name"] = $vignette_name;
			$this->infos_docnum["vignette_moved"] = $vignette_moved;
			
			$this->params["error"] = $erreur;
			$this->params["maj_mimetype"] = $maj_mimetype;
			$this->params["maj_data"] = $maj_data;
			$this->params["maj_vignette"] = $maj_vignette;	
			$this->params["retour"] = $retour;
			$this->params["conservervignette"] = $conservervignette;
			$this->params["statut"] = $f_statut_chk;					
			
		}
		
		
		/*
		 * Teste si l'exemplaire est stock� en base
		 */
		function isEnBase(){			
			if($this->explnum_data && !$this->explnum_repertoire && !$this->explnum_path)
				return true;
			return false;
		}
		
		/*
		 * Teste si l'exemplaire est stock� sur le disque
		 */
		function isEnUpload(){
			if($this->explnum_repertoire && $this->explnum_path)
				return true;
			return false;
		}
		
		/*
		 * Teste si l'exemplaire est stock� sous forme d'URL
		 */
		function isURL() {
			if($this->explnum_url)
				return true;
			return false;
		}
		
		/*
		 * Retire l'exemplaire de la base pour le mettre en upload
		 */
		function remove_from_base($chemin,$upfolder){
						
			$content = $this->explnum_data;
			$nom = $this->explnum_nom;
			$new_path="";
			if($upfolder->isHashing()){
				$hashname = $upfolder->hachage($nom);
				$file_path = $upfolder->encoder_chaine($hashname.$nom);
				if(!is_dir($hashname))
					mkdir($hashname);
				$new_path = $upfolder->encoder_chaine($hashname);
			} else {
				$file_path = $upfolder->encoder_chaine($upfolder->formate_nom_to_path($chemin).$nom);
			}
			file_put_contents($file_path,$content);
			
			return $new_path;
		}
		
		/*
		 * Supprime le fichier upload� pour le mettre en base
		 */
		function remove_from_upload(){			
					
			$up=new upload_folder($this->explnum_repertoire);
			$path = $up->repertoire_path.$this->explnum_path.$this->explnum_nomfichier;
			$path = str_replace('//','/',$path);
			
			$path = $up->encoder_chaine($path);
			$contenu = file_get_contents($path);
					
			unlink($path);
			return $contenu;
		}
		
		/*
		 * Permet le changement de r�pertoire d'upload
		 */
		function change_rep_upload($rep, $new_path){
			
			$nom_fich = ($this->explnum_nomfichier != "" ? $this->explnum_nomfichier : $this->explnum_nom);
			$old_path = $this->explnum_rep_nom.$this->explnum_path;
			$old_path = str_replace('//','/',$old_path);
			
			
			if($rep->isHashing()){
				$new_rep = $rep->hachage($nom_fich);
				if(!is_dir($new_rep)) mkdir($new_rep);
			} else {
				$new_rep = $new_path;
			}	
					
			$up = new upload_folder($this->explnum_repertoire);
			$old_path = $up->formate_nom_to_path($old_path);
			$ancien_fichier= $up->encoder_chaine($old_path.$nom_fich);
			$nouveau_fichier= $rep->encoder_chaine($new_rep.$nom_fich);			
			
			if(!file_exists($nouveau_fichier) && ($nouveau_fichier != $ancien_fichier)){
				rename($ancien_fichier,$nouveau_fichier);
				if(file_exists($ancien_fichier)) 
					unlink($ancien_fichier);
				$nom_rep = $new_path;
			}
			
			return ($nom_rep ? $nom_rep : $new_rep);
		}
		
		/*
		 * Copie dans un r�pertoire
		 */
		function copy_to($new_dir_id=0,$rename=false){
			$ret=false;
			$old_dir_id=$this->explnum_repertoire;			
		
			if ($old_dir_id && $new_dir_id) {
				
				$old_dir = new upload_folder($old_dir_id);
				$new_dir = new upload_folder($new_dir_id);
			
				$old_file_name = ($this->explnum_nomfichier != '' ? $this->explnum_nomfichier : $this->explnum_nom);
				if ($rename) {
					$new_file_name = $this->rename();
				} else {
					$new_file_name = $old_file_name;	
				}
				
				$old_path = $old_dir->repertoire_path.$this->explnum_path;
				$old_path = str_replace('//','/',$old_path);
				
				if($new_dir->isHashing()){
					$new_sub_dir = $new_dir->hachage($new_file_name);
					if(!is_dir($new_sub_dir)) mkdir($new_sub_dir);
					$new_path = $new_dir->repertoire_path.$new_sub_dir;
				} else {
					$new_path = $new_dir->repertoire_path;
				}	
				$new_path = str_replace('//','/',$new_path);
				
				$old_file= $old_dir->encoder_chaine($old_path.$old_file_name);
				$new_file= $new_dir->encoder_chaine($new_path.$new_file_name);			
				if (file_exists($new_file)) {
					$new_file_name=$this->rename();
					$new_file= $new_dir->encoder_chaine($new_path.$new_file_name);
				}
				if(!file_exists($new_file)) {
					if (copy($old_file,$new_file)){
							$ret=true;
					}
				}
			} else if($new_dir_id) {
				
				$new_dir = new upload_folder($new_dir_id);
				
				$old_file_name = ($this->explnum_nomfichier != '' ? $this->explnum_nomfichier : $this->explnum_nom);
				if ($rename) {
					$new_file_name = $this->rename();
				} else {
					$new_file_name = $old_file_name;	
				}
				
				if($new_dir->isHashing()){
					$new_sub_dir = $new_dir->hachage($new_file_name);
					if(!is_dir($new_sub_dir)) mkdir($new_sub_dir);
					$new_path = $new_dir->repertoire_path.$new_sub_dir;
				} else {
					$new_path = $new_dir->repertoire_path;
				}	
				$new_path = str_replace('//','/',$new_path);
				$new_file= $new_dir->encoder_chaine($new_path.$new_file_name);	
				if (file_exists($new_file)) {
					$new_file_name=$this->rename();
					$new_file= $new_dir->encoder_chaine($new_path.$new_file_name);
				}
				if(!file_exists($new_file)) {
					if (file_put_contents($new_file,$this->explnum_data)){
						$ret=true;
					}
				}
				
			}
			return ($ret)?$new_file:$ret;
		}
		
		//fournit un nom de fichier unique
		function rename() {
			$new_file_name = 'file_'.md5(microtime()).(($this->explnum_ext)?'.'.$this->explnum_ext:'');
			return $new_file_name;
		}
		
		static function static_rename($ext='') {
			$new_file_name = 'file_'.md5(microtime()).(($ext)?'.'.$ext:'');
			return $new_file_name;
		}
		
		/*
		 * Fonction qui d�zippe dans le bon r�pertoire
		 */
		function unzip($filename){
			global $up_place, $path, $id_rep, $charset, $base_path;		
			
			$zip = new zip($filename);
			$zip->readZip();
			$cpt = 0;
			if($up_place && $path != ''){
				$up = new upload_folder($id_rep);
			}
		  					
			if(is_array($zip->entries) && count($zip->entries)){
				foreach($zip->entries as $file){
					$encod=mb_detect_encoding($file['fileName'],"UTF-8,ISO-8859-1");
					if($encod && ($encod =='UTF-8') && ($charset == "iso-8859-1")){
						$file['fileName'] = utf8_decode($file['fileName']);	
					}elseif($encod && ($encod =='ISO-8859-1') && ($charset == "utf-8")){
						$file['fileName'] = utf8_encode($file['fileName']);
					}
		  					
	  			if($up_place && $path != ''){
					$chemin = $path;
					if($up->isHashing()){
						$hashname = $up->hachage($file['fileName']);
						@mkdir($hashname);
						$filepath = $up->encoder_chaine($hashname.$file['fileName']);
					} else $filepath = $up->encoder_chaine($up->formate_nom_to_path($chemin).$file['fileName']);
						//On regarde si le fichier existe avant de le cr�er
						$continue=true;
						$compte=0;
						$filepath_tmp=$filepath;
						do{
							if(!file_exists($filepath_tmp)){
								$continue=false;
							}else{
								$compte++;
								if(preg_match("/^(.+)(\..+)$/i",$filepath,$matches)){
									$filepath_tmp=$matches[1]."_".$compte.$matches[2];
								}else{
									$filepath_tmp=$filepath."_".$compte;
								}
							}
						}while($continue);
						if($compte){
							$filepath=$filepath_tmp;
						}
					$fh =fopen($filepath, 'w+');
					fwrite($fh,$zip->getFileContent($file['fileName']));
					fclose($fh);
					if($compte){
						if(preg_match("/^(.+)(\..+)$/i",$file['fileName'],$matches)){
							$file['fileName']=$matches[1]."_".$compte.$matches[2];
						} else {
							$file['fileName']=$file['fileName']."_".$compte;
						}
					}
				} else {
					$chemin = $base_path.'/temp/'.$file['fileName'];
					$fh =fopen($chemin, 'w');
					fwrite($fh,$zip->getFileContent($file['fileName']));
					$base = true;				
				}				
				
				$this->unzipped_files[$cpt]['chemin'] = $chemin;
				$this->unzipped_files[$cpt]['nom'] = $file['fileName'];
				$this->unzipped_files[$cpt]['base'] = $base;
				$cpt++;
			}
		}
		}
		
		/*
		 * Gestion de l'ajout multifichier
		 */
		function analyse_multifile(){
			global $id_rep;
			
			create_tableau_mimetype() ;
			$repup = new upload_folder($id_rep);
			if(is_array($this->unzipped_files) && count($this->unzipped_files)){	
				for($i=0;$i<sizeof($this->unzipped_files);$i++){	
					$this->infos_docnum['userfile_name'] = $this->unzipped_files[$i]['nom'];
					if($repup->isHashing()){
						$hashname = $repup->hachage($this->infos_docnum['userfile_name']);
						$chemin =  $repup->formate_path_to_save($repup->formate_path_to_nom($hashname));
					} else $chemin = $repup->formate_path_to_save($this->unzipped_files[$i]["chemin"]);
	
					if($this->unzipped_files[$i]['base']){
						$this->infos_docnum['contenu'] = file_get_contents($this->unzipped_files[$i]['chemin']);
						$this->infos_docnum['path'] = '';
					} else {
						$this->infos_docnum['contenu'] = '';
						$this->infos_docnum['path'] = $chemin;
					}
					$ext = '';
					if ($this->infos_docnum['userfile_name']) {
						$ext = extension_fichier($this->infos_docnum['userfile_name']);
						$this->infos_docnum['userfile_ext'] = $ext;						
					}
					
					if($this->unzipped_files[$i]['base']){
						$this->infos_docnum['contenu_vignette'] = construire_vignette("",$this->infos_docnum['userfile_name']);
					} else {		
						if($repup->isHashing())			
							$this->infos_docnum['contenu_vignette'] = construire_vignette("",$repup->encoder_chaine($hashname.$this->infos_docnum['userfile_name']));
						else 
							$this->infos_docnum['contenu_vignette'] = construire_vignette("",$repup->encoder_chaine($repup->formate_nom_to_path($this->unzipped_files[$i]['chemin']).$this->infos_docnum['userfile_name']));
					}	
					$mimetype = trouve_mimetype($this->unzipped_files[$i]['chemin'],$this->infos_docnum['userfile_ext']);
					if (!$mimetype) $mimetype="application/data";
					$this->infos_docnum['mime'] = $mimetype;
					
					if ($this->unzipped_files[$i]['base']) {
						unlink($this->unzipped_files[$i]['chemin']);
					}
					if($mimetype == 'URL'){
						$this->infos_docnum['url'] = $this->unzipped_files[$i]['nom'];
						$this->infos_docnum['nom'] = '';
					} else {
						$this->infos_docnum['nom'] = $this->unzipped_files[$i]['nom'];
						$this->infos_docnum['url'] = '';
					}
					$this->update();
					$this->explnum_id=0;
				}
			}
		}

		function get_file_from_temp($filename,$name,$upload_place){
			global $base_path;
			global $ck_index;
			global $id_rep,$up_place;
			$up_place=$upload_place;
			
			
			create_tableau_mimetype();
			$ck_index = true;
			//Initialisation des tableaux d'infos
			$this->infos_docnum = $this->params = array();
			$this->infos_docnum["mime"] = trouve_mimetype($filename,extension_fichier($name));
			$this->infos_docnum["nom"] = substr($name,0,strrpos($name,"."));
			if(!$this->infos_docnum["nom"]){
				$this->infos_docnum["nom"]=$name;
			}
			$this->infos_docnum["notice"] = $this->explnum_notice;
			$this->infos_docnum["bull"] = $this->explnum_bulletin;
			$this->infos_docnum["url"] = "";
			$this->infos_docnum["fic"] = false;
			$this->infos_docnum["contenu_vignette"] = construire_vignette('', substr($filename,strrpos($filename,"/")));
			$this->infos_docnum["userfile_name"] = $name;
			$this->infos_docnum["userfile_ext"] = extension_fichier($name);

			if($up_place && $id_rep!=0){
				$upfolder = new upload_folder($id_rep);
				$chemin_hasher = "/";
				if($upfolder->isHashing()){
					$rep = $upfolder->hachage($this->infos_docnum["userfile_name"]);
					@mkdir($rep);
					$chemin_hasher = $upfolder->formate_path_to_nom($rep);
					$file_name = $rep.$this->infos_docnum["userfile_name"];	
					$chemin = $upfolder->formate_path_to_save($chemin_hasher);
				}else{
					$file_name = $upfolder->get_path($this->infos_docnum["userfile_name"]).$this->infos_docnum["userfile_name"];
					$chemin = $upfolder->formate_path_to_save("/");
				}
				$this->infos_docnum["path"] = $chemin ;
				$file_name = $upfolder->encoder_chaine($file_name);
				if(!$this->explnum_nomfichier){//Si je suis en cr�ation de fichier num�rique
					$nom_tmp=$this->infos_docnum["userfile_name"];
					$continue=true;
					$compte=1;
					do{
						$query = "select explnum_notice,explnum_id from explnum where explnum_nomfichier = '".addslashes($nom_tmp)."' AND explnum_repertoire='".$id_rep."' AND explnum_path='".addslashes($this->infos_docnum["path"])."'";
						$result = mysql_query($query);
						if(mysql_num_rows($result) && (mysql_result($result,0,0) != $this->infos_docnum["notice"])){//Si j'ai d�j� un document num�rique avec ce fichier pour une autre notice je dois le renommer pour ne pas perdre l'ancien
							if(preg_match("/^(.+)(\..+)$/i",$this->infos_docnum["userfile_name"],$matches)){
								$nom_tmp=$matches[1]."_".$compte.$matches[2];
							}else{
								$nom_tmp=$this->infos_docnum["userfile_name"]."_".$compte;
							}
							$compte++;
						}else{
							if(mysql_num_rows($result)){//J'ai d�j� ce fichier pour cette notice
								//Je dois enlever l'ancien document num�rique pour ne pas l'avoir en double
								$old_docnum= new explnum(mysql_result($result,0,1));
								$old_docnum->delete();
							}else{
								
							}
							$continue=false;
						}
					}while($continue);
					if($compte != 1){
						$this->infos_docnum["userfile_name"]=$nom_tmp;
						if($upfolder->isHashing()){
							$file_name = $rep.$this->infos_docnum["userfile_name"];	
						}else{
							$file_name = $upfolder->get_path($this->infos_docnum["userfile_name"]).$this->infos_docnum["userfile_name"];
						}
						$file_name = $upfolder->encoder_chaine($file_name);
					}else{
						
					}
				}else{
					
				}
				rename($filename,$file_name);
			}else{
				//enregistrement en base
				$this->infos_docnum["contenu"] = file_get_contents($filename);
			}

			$this->params["maj_mimetype"] = true;
			$this->params["maj_data"] = true;
			$this->params["maj_vignette"] = true;
		}
		
	function get_file_content(){
		$resultat = mysql_query("SELECT explnum_id, explnum_notice, explnum_bulletin, explnum_nom, explnum_mimetype, explnum_url, explnum_data, length(explnum_data) as taille,explnum_path, concat(repertoire_path,explnum_path,explnum_nomfichier) as path, repertoire_id FROM explnum left join upload_repertoire on repertoire_id=explnum_repertoire WHERE explnum_id = '".$this->explnum_id."' ");
		$nb_res = mysql_num_rows($resultat) ;
		if (!$nb_res) {
			exit ;
			} 
		$ligne = mysql_fetch_object($resultat);
		if (($ligne->explnum_data)||($ligne->explnum_path)) {
			if ($ligne->explnum_path) {
				$up = new upload_folder($ligne->repertoire_id);
				$path = str_replace("//","/",$ligne->path);
				$path=$up->encoder_chaine($path);
				if(file_exists($path) && filesize($path)){
					$fo = fopen($path,'rb');
					$data=fread($fo,filesize($path));
					fclose($fo);
				}else{
					$data="";
				}
			}else{
				$data = $ligne->explnum_data;
			}
		}
		return $data;
	}	
		
	} # fin de la classe explnum

		                                                  
} # fin de d�finition                             
