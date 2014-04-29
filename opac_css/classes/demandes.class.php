<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes.class.php,v 1.9 2012-03-14 14:18:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once("$class_path/marc_table.class.php");

class demandes{
	
	var $liste_etat = array();
	
	function demandes(){
		global $base_path;
		
		$list_etat = new marc_list("etat_demandes");
		$this->liste_etat = $list_etat->table;
	}
	
	/*
	 * Affichage du formulaire d'enregistrement
	 */
	function show_form(){
		global $form_do_demande, $dbh, $charset;
		
		$date = formatdate(today());
		$date_fin=date("Ymd",time());

		//Selecteur des th�mes
		$req = "select * from demandes_theme order by libelle_theme";
		$res = mysql_query($req,$dbh);
		$theme_selector = "<select name='idtheme' >";		 
		while($dmde = mysql_fetch_object($res)){
			$theme_selector .= "<option value='".$dmde->id_theme."'>".htmlentities($dmde->libelle_theme,ENT_QUOTES,$charset)."</option>";			
		}	
		$theme_selector .= "</select>";	
		
		//Selecteur des types
		$req = "select * from demandes_type order by libelle_type";
		$res = mysql_query($req,$dbh);
		$type_selector = "<select name='idtype' >";		 
		while($dmde = mysql_fetch_object($res)){
			$type_selector .= "<option value='".$dmde->id_type."'>".htmlentities($dmde->libelle_type,ENT_QUOTES,$charset)."</option>";			
		}	
		$type_selector .= "</select>";	
		
		$form_do_demande = str_replace('!!date_fin!!',$date_fin, $form_do_demande);
		$form_do_demande = str_replace('!!date_fin_btn!!',$date, $form_do_demande);
		$form_do_demande = str_replace('!!select_theme!!',$theme_selector, $form_do_demande);
		$form_do_demande = str_replace('!!select_type!!',$type_selector, $form_do_demande);
		
		print $form_do_demande;
	}
	
	/*
	 * Enregistrement de la demande
	 */
	function save(){
		
		global $titre, $sujet, $idtheme, $idtype, $date_fin, $id_empr, $dbh, $demandes_statut_notice, $pmb_type_audit;
		global $demandes_email_demandes;
		
		$index_wew = $titre;
		$index_sew = strip_empty_words($index_wew);
		$index_ncontenu =  strip_empty_words($sujet);					
		$req = "insert into notices set 
		tit1='".$titre."',
		n_contenu='".$sujet."',
		statut ='".$demandes_statut_notice."',
		index_sew ='".$index_sew."',
		index_wew ='".$index_wew."',
		index_n_contenu = '".$index_ncontenu."',
		create_date = CURRENT_TIMESTAMP()
		";
		mysql_query($req,$dbh);
		$id_notice = mysql_insert_id();
		
		if ($pmb_type_audit) {
			$query = "INSERT INTO audit SET ";
			$query .= "type_obj='1', ";
			$query .= "object_id='$id_notice', ";
			$query .= "type_modif=1 ";
			$result = @mysql_query($query, $dbh);
		}
		
		$date=date("Ymd",time());
		$req="insert into demandes set 
			titre_demande='".$titre."',
			sujet_demande='".$sujet."',
			theme_demande='".$idtheme."',
			type_demande='".$idtype."',
			deadline_demande='".$date_fin."',
			date_demande='".$date."',
			date_prevue='".$date."',
			num_demandeur='".$id_empr."',
			num_notice='".$id_notice."',
			etat_demande=1
		";
		mysql_query($req,$dbh);

		if($demandes_email_demandes) $this->alert_mail_users_pmb($id_empr);
	}
	
	/*
	 * Affichage de la liste des demandes
	 */
	function show_list($idetat=0){
		
		global $id_empr, $form_liste_demande, $msg, $dbh, $base_path, $opac_url_base,$opac_permalink,$charset;
		
		$req="select id_demande, num_demandeur,theme_demande,type_demande,etat_demande, date_demande, 
			if(date_prevue>deadline_demande,date_prevue, deadline_demande) as deadline, titre_demande, sujet_demande, progression, num_user_cloture,
			concat(empr_prenom,' ',empr_nom) as dmdeur, group_concat(distinct if(concat(prenom,' ',nom)!='',concat(prenom,' ',nom),username) separator '/ ' ) as user, group_concat(username separator '/ ' ) as login, num_notice
			from demandes 
			join demandes_theme on theme_demande=id_theme 
			join demandes_type on type_demande=id_type
			join empr on id_empr=num_demandeur
			left join demandes_users du on du.num_demande=id_demande 
			left join users on userid=du.num_user
			where num_demandeur='".$id_empr."' 
			";
		if ($idetat) $req .= " and etat_demande='".$idetat."' "; 
		$req .= " group by id_demande 
		order by date_demande desc"; 
		$res = mysql_query($req,$dbh);	
		
		if(!$idetat){
			$entete = "<th>".$msg['demandes_etat']."</th>";
			$form_liste_demande = str_replace('!!entete_etat!!',$entete,$form_liste_demande);
		} else $form_liste_demande = str_replace('!!entete_etat!!','',$form_liste_demande);
		
		$form_liste_demande = str_replace('!!select_etat!!',$this->getStateSelector($idetat,true),$form_liste_demande);						
		$liste = "";
		if(mysql_num_rows($res)){
			$parity=1;					
			while(($dmde = mysql_fetch_object($res))){
				if ($parity % 2) {
					$pair_impair = "even";
				} else {
					$pair_impair = "odd";
				}
				$parity += 1;

				$tr_javascript = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
				$action = "onclick=document.location='./empr.php?tab=request&lvl=list_dmde&sub=see_action&iddemande=$dmde->id_demande'";
				$liste .= "<tr class='".$pair_impair."' ".$tr_javascript." style='cursor: pointer'  $action>";
				$liste .= "<td>".htmlentities($dmde->titre_demande,ENT_QUOTES,$charset)."</td>";
				
				$liste .= (!$idetat ? "<td>".htmlentities($this->liste_etat[$dmde->etat_demande],ENT_QUOTES,$charset)."</td>" : '');
				
				$req = "select notice_visible_opac as visible, notice_visible_opac_abon as visu_abo from notice_statut join notices on id_notice_statut=statut where notice_id='".$dmde->num_notice."'";
				$res_vis = mysql_query($req,$dbh);
				$noti_display = mysql_fetch_object($res_vis);
				
				if($noti_display->visible || $noti_display->visu_abo){
					$link_noti = "<a href='".$opac_url_base."index.php?lvl=notice_display&id=".$dmde->num_notice."' alt='".$msg['demandes_see_notice']."' title='".$msg['demandes_see_notice']."'><img src='$base_path/images/mois.gif' /></a>";
				} else $link_noti = "";
				$liste .="
					<td>".htmlentities(formatdate($dmde->date_demande),ENT_QUOTES,$charset)."</td>
					<td>".htmlentities(formatdate($dmde->deadline),ENT_QUOTES,$charset)."</td>
					<td>".htmlentities($dmde->user,ENT_QUOTES,$charset)."</td>
					<td>
						<img src=\"$base_path/images/jauge.png\" height='15px' width=\"".$dmde->progression."%\" title='".$dmde->progression."%' />
					</td>
					<td>$link_noti</td>
				"; 
				$liste .= "</tr>";				
			}
		} else {
			$liste .= "<tr><td>".$msg['demandes_liste_vide']."</td></tr>";
		}
		
		$form_liste_demande = str_replace('!!liste_dmde!!',$liste,$form_liste_demande);
		print $form_liste_demande;
	}
	
	/*
	 * Selecteur d'etat	
	 */	
	 
	function getStateSelector($idetat=0,$default=false){
		global $charset, $msg;
		$selector = "<select name='idetat' onchange='submit();'>";
		$select="";
		if($default) $selector .= "<option value='0'>".htmlentities($msg['demandes_all_states'],ENT_QUOTES,$charset)."</option>";
		for($i=1;$i<=count($this->liste_etat);$i++){
			if($idetat == $i) $select = "selected";
			$selector .= "<option value='$i' $select>".htmlentities($this->liste_etat[$i],ENT_QUOTES,$charset)."</option>";
			$select = "";
		}
		$selector .= "</select>";
		
		return $selector;
	}
	
	/*
	 * Alerter par mail les utilisateurs de la  nouvelle demande
	 */
	function alert_mail_users_pmb($id_empr) {
		global $dbh;
		global $msg, $charset;
		global $opac_biblio_name, $opac_biblio_email ;
		global $opac_url_base ;
		global $titre, $sujet, $idtheme, $idtype, $date_fin;
		
		// param�trage OPAC: choix du nom de la biblioth�que comme exp�diteur
		$requete = "select location_libelle, email from empr, docs_location where empr_location=idlocation and id_empr='$id_empr' ";
		$res = mysql_query($requete, $dbh);
		$loc=mysql_fetch_object($res) ;
		
		$PMBusernom = $loc->location_libelle ;
		$PMBuserprenom = "" ;
		$PMBuseremail = $loc->email ;
	
		// libell� du th�me de la demande
		$requete = "select libelle_theme from demandes_theme where id_theme='".$idtheme."'";
		$res = mysql_query($requete, $dbh);
		$theme=mysql_fetch_object($res) ;
		$libelle_theme = $theme->libelle_theme;
		
		// libell� du type de la demande
		$requete = "select libelle_type from demandes_type where id_type='".$idtype."'";
		$res = mysql_query($requete, $dbh);
		$typ=mysql_fetch_object($res) ;
		$libelle_type = $typ->libelle_type;
		
		$query = "select distinct empr_prenom, empr_nom, empr_cb, empr_mail, empr_tel1, empr_tel2, empr_ville, location_libelle, nom, prenom, user_email, date_format(sysdate(), '".$msg["format_date_heure"]."') as aff_quand from empr, docs_location, users where id_empr='$id_empr' and empr_location=idlocation and user_alert_demandesmail=1";
		$result = @mysql_query($query, $dbh);
		$headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=".$charset."\n";
		while ($empr=@mysql_fetch_object($result)) {
			$output_final = "<html><body>" ;
			$output_final .= "<a href='".$opac_url_base."'><font color=green><strong>".$msg["mail_obj_demande_added"] ;
			$sujet = $msg["mail_obj_demande_added"] ;
			
			$output_final .= "</strong></font></a> ".$empr->aff_quand."
								<br /><strong>".$empr->empr_prenom." ".$empr->empr_nom."</strong>							
								<br /><i>".$empr->empr_mail." / ".$empr->empr_tel1." / ".$empr->empr_tel2."</i>";
			if ($empr->empr_cp || $empr->empr_ville) $output_final .= "<br /><u>".$empr->empr_cp." ".$empr->empr_ville."</u>";
			$output_final .= "<hr />".$msg[situation].": ".$empr->location_libelle."<hr />";
			
			$output_final .= $msg["mail_content_demande_theme"]." ".$libelle_theme."<br />";
			$output_final .= $msg["mail_content_demande_type"]." ".$libelle_type."<br />";
			
			$output_final .= $titre."<br /><br />";
			$output_final .= $sujet."<br /><br />";
			$output_final .= $msg["mail_content_demande_date_fin"]." ".formatdate($date_fin)."<br />";	
			
			$output_final .= "<hr /></body></html> ";
			$res_envoi=mailpmb($empr->nom." ".$empr->prenom, $empr->user_email,$sujet." ".$empr->aff_quand,$output_final,$opac_biblio_email, $opac_biblio_email, $headers, "", "", 1);
		}
	}
}
?>