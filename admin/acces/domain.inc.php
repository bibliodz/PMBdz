<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: domain.inc.php,v 1.6 2013-06-17 08:24:45 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/acces.class.php");
require_once("$include_path/templates/acces.tpl.php");

//recuperation domaine
if (!$id) return;
if (!$ac) {
	$ac= new acces();
	$t_cat= $ac->getCatalog();
}
if (!$dom) {
	$dom=$ac->setDomain($id);
}


echo window_title($database_window_title.$msg[7].$msg[1003].$msg[1001]);
//construction menu
$admin_menu_acces = "<h1>".htmlentities($msg["admin_menu_acces"], ENT_QUOTES, $charset)."<span>&nbsp;&gt;&nbsp;!!menu_sous_rub!!</span></h1>";
$admin_menu_acces.= "<div class='hmenu'>";
foreach($t_cat as $k=>$v) {
	$lib=htmlentities($v['comment'], ENT_QUOTES, $charset);
	$admin_menu_acces.= '<span';
	if ($id==$k) {
		$admin_menu_acces.= " class='selected'";
		$menu_sous_rub=$lib;
	}
	$admin_menu_acces.= "><a href='./admin.php?categ=acces&sub=domain&action=view&id=".$k."'>$lib</a></span>";
}
unset($v);
$admin_menu_acces.= "</div>";
$admin_menu_acces=str_replace('!!menu_sous_rub!!',$menu_sous_rub, $admin_menu_acces);
$admin_layout = str_replace('!!menu_contextuel!!', $admin_menu_acces, $admin_layout);
print $admin_layout;
		

function show_domain($id,$maj=false) {

	global $dbh, $msg, $charset;
	global $dom;
	global $dom_view_form, $dom_glo_rights_form,$maj_form; 
	
	$form = $dom_view_form;
	
	//affichage lien roles utilisateurs
	$txt = htmlentities($dom->getComment('user_prf_lib'),ENT_QUOTES,$charset);
	$row = "<tr style=\"cursor:pointer;\" onmousedown=\"document.location='./admin.php?categ=acces&sub=user_prf&action=list&id=$id';\" ";
	$row.= "onmouseout=\"this.className='even'\" onmouseover=\"this.className='surbrillance'\" class=\"even\"><td><strong>$txt</strong></td></tr>";
	//affichage lien profils ressources
	$txt = htmlentities($dom->getComment('res_prf_lib'),ENT_QUOTES,$charset);
	$row.= "<tr style=\"cursor:pointer;\" onmousedown=\"document.location='./admin.php?categ=acces&sub=res_prf&action=list&id=$id';\" ";
	$row.= "onmouseout=\"this.className='odd'\" onmouseover=\"this.className='surbrillance'\" class=\"odd\"><td><strong>$txt</strong></td></tr>";
	$form = str_replace ('<!-- rows -->', $row, $form);
	
	//affichage droits
	$r_header = $msg['dom_rights_lib'];
	$form = str_replace ('!!rights_header!!', htmlentities($r_header, ENT_QUOTES, $charset), $form);
	
	//recuperation roles utilisateurs
	$t_u[0]= $dom->getComment('user_prf_def_lib');	//role par defaut
	$qu=$dom->loadUsedUserProfiles();
	$ru=mysql_query($qu, $dbh);
	if (mysql_num_rows($ru)) {
		while(($row=mysql_fetch_object($ru))) {
			
	        $t_u[$row->prf_id]= $row->prf_name;
		}
	}
	//print '<pre>';print_r($t_u);print '</pre>';
	
	//recuperation profils ressources
	$t_r[0]=$dom->getComment('res_prf_def_lib');	//profil par defaut
	$qr=$dom->loadUsedResourceProfiles();
	$rr=mysql_query($qr, $dbh);
	if (mysql_num_rows($rr)) {
		while(($row=mysql_fetch_object($rr))) {
	        $t_r[$row->prf_id]= $row->prf_name;
		}
	}
	//print '<pre>';print_r($t_r);print '</pre>';
	
	//Recuperation des controles dependants de l'utilisateur	
	$t_ctl=$dom->getControls(0);
	//print '<pre>';print_r($t_ctl);print '</pre><br />';
	
	//Recuperation des controles independants de l'utilisateur	
	$t_ctli=$dom->getControls(1);
	//print '<pre>';print_r($t_ctl);print '</pre><br />';
	
	//Recuperation des droits
	$t_rights = $dom->loadDomainRights();
	//print '<pre>';print_r($t_rights);print '</pre><br />';

	
	//creation du formulaire
	
	//droits independants des profils
	if (count($t_ctli)) {
		
			$r_rows = "";
			foreach($t_ctli as $k2=>$v2) {
										
				$r_rows.="
					<tr>
						<td style='width:25px;' ><input type='checkbox' name='chk_rights[0][0][".$k2."]' id='chk_rights[0][0][".$k2."]' value='1' ";
				if ($t_rights[0][0] & (pow(2,$k2-1)) ) {
					$r_rows.= "checked='checked' ";
				}
				$r_rows.= "/></td>
						<td><label for='chk_rights[0][0][".$k2."]'>".htmlentities($v2, ENT_QUOTES, $charset)."</label></td>
					</tr>";
			}

			$dom_glo_rights_form = str_replace ('<!-- rows -->', $r_rows, $dom_glo_rights_form);		
			$form = str_replace('<!-- dom_glo_rights_form -->',$dom_glo_rights_form,$form);
	}

	
	
	//droits par profils	
	$nb_u=count($t_u);
	$nb_r=count($t_r);
	if ($nb_u && $nb_r) {
		
		$dom_usr_sel= "<select name='dom_usr_sel' class='dom_sel' onchange='dom_move_to(this.value, this.selectedIndex);'>";
		foreach($t_u as $k0=>$v0) {
			$dom_usr_sel.= "<option value='col_".$k0."' ".(($k0==0)?"selected='selected'":'')." >".(htmlentities($v0, ENT_QUOTES, $charset))."</option>";
		}
		$dom_usr_sel.= "</select>";
		
		$dom_nb_col_sel= "<select name='dom_nb_col_sel' onchange='dom_resize_to(this.value, this.selectedIndex);'>";
		for($i=5;$i<$nb_u;$i+=5) {
			$dom_nb_col_sel.="<option value='".$i."' >".$i."</option>";
		}
		$dom_nb_col_sel.="<option value='".$nb_u."' >".$nb_u."</option>";
		$dom_nb_col_sel.= "</select>";
		
		$form = str_replace('<!-- prf_rights_lib -->',htmlentities($msg['dom_prf_rights_lib'],ENT_QUOTES,$charset),$form);
		
		//1ere colonne
		$nr=10;
		$l_form="<div class='dom_row2' ><div class='dom_cell2' ><br /><input type='button' class='bouton_small' value='<<' onclick='dom_move_first();' /><input type='button' class='bouton_small' value='<' onclick='dom_move_left();' />".$dom_nb_col_sel."<input type='button' class='bouton_small' value='>' onclick='dom_move_right();' /><br />".$dom_usr_sel."</div></div>";
		foreach($t_r as $k=>$v) {
			if(!$nr) {
				$l_form.="<div class='dom_row2'><div class='dom_cell2'><input type='button' class='bouton_small' value='<<' onclick='dom_move_first();' /><input type='button' class='bouton_small' value='<' onclick='dom_move_left();' />".$dom_nb_col_sel."<input type='button' class='bouton_small' value='>' onclick='dom_move_right();' /><br />".$dom_usr_sel."</div></div>";
				$nr=10;
			}
			$nr--;
			$l_form.= "<div class='dom_row2'><div class='dom_cell2_h' title='".htmlentities($v, ENT_QUOTES, $charset)."' >".htmlentities($v, ENT_QUOTES, $charset)."</div></div>";
		}
		$form = str_replace ('<!-- col_h -->', $l_form, $form);
		
		//autres colonnes
		$n_col=1;
		foreach($t_u as $k1=>$v1) {

			$form = str_replace ('<!-- o_cols -->', "<div id='col_".$k1."' class='dom_col2' ".(($n_col>5)?"style='display:none'":'')." ><!-- col_n --></div><!-- o_cols -->", $form);
			$n_col++;
			$r_form = "<div class='dom_row2'><div class='dom_cell2_h' title='".htmlentities($v1, ENT_QUOTES, $charset)."' >".htmlentities($v1, ENT_QUOTES, $charset)."</div></div><!-- rows -->";
			
			$nr=10;
			foreach($t_r as $k2=>$v2) {
				if(!$nr) {
					$r_form = str_replace('<!-- rows -->',"<div class='dom_row2'><div class='dom_cell2_h' title='".htmlentities($v1, ENT_QUOTES, $charset)."' >".htmlentities($v1, ENT_QUOTES, $charset)."</div></div><!-- rows -->",$r_form);
					$nr=10;
				}
				$nr--;
				$r_rows = "<div class='dom_row2'><div class='dom_cell2'><table>";
				foreach($t_ctl as $k3=>$v3) {
					$r_rows.="
					<tr>
					<td style='width:25px;' ><input type='checkbox' name='chk_rights[".$k1."][".$k2."][".$k3."]' id='chk_rights[".$k1."][".$k2."][".$k3."]' value='1' ";
					if ($t_rights[$k1][$k2] & (pow(2,$k3-1)) ) {
						$r_rows.= "checked='checked' ";
					}
					$r_rows.= "/></td>
					<td><label for='chk_rights[".$k1."][".$k2."][".$k3."]'>".htmlentities($v3, ENT_QUOTES, $charset)."</label></td>
					</tr>";
				}
				$r_rows.= "</table></div></div>";
				$r_form = str_replace('<!-- rows -->', $r_rows.'<!-- rows -->', $r_form);
			}
			$form = str_replace ('<!-- col_n -->', $r_form, $form);
		}		
		
	}
	
	//bouton enregistrer
	$bt_enr = "<input type='button' onclick=\"
	this.form.action='./admin.php?categ=acces&sub=domain&action=update&id=$id'; 
	this.form.submit();return false;\" 
	value=\"".addslashes($msg['77'])."\" class='bouton' />";
	$form = str_replace('<!-- bt_enr -->', $bt_enr,$form);
	
	//bouton appliquer
	$bt_app = "<input type='button' onclick=\"pbar_init();\" 
	value=\"".addslashes($msg['dom_prf_ini'])."\" class='bouton' />";
	$form = str_replace('<!-- bt_app -->', $bt_app,$form);
	
	$chk_sav_spe_rights = "<input type='checkbox' id='chk_sav_spe_rights' name='chk_sav_spe_rights' value='1' />&nbsp;<label for='chk_sav_spe_rights' >".htmlentities($msg['dom_sav_spe_rights'], ENT_QUOTES, $charset)."</label>";
	$form = str_replace('<!-- chk_sav_spe_rights -->', $chk_sav_spe_rights, $form);

	//bouton raz droits calcul�s
	$bt_raz ="<input type='button' onclick=\"
	this.form.action='./admin.php?categ=acces&sub=domain&action=raz&id=$id';
	this.form.submit();return false;\"
	value=\"".addslashes($msg['dom_prf_raz'])."\" class='bouton' />";
	$form = str_replace('<!-- bt_raz -->', $bt_raz,$form);
	
	if ($maj) {
		$form = str_replace('<!-- maj -->',$maj_form,$form);
	}
	
	print $form;
}


switch ($action) {
	case 'update' :
		$dom->saveDomainRights($chk_rights);
		show_domain($id,true);
		break;
	case 'raz' :
		$dom->deleteDomainRights();
	case 'view' :
	default:
		show_domain($id);
		break;
}

?>