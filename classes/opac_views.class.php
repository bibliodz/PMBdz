<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: opac_views.class.php,v 1.1 2012-08-08 14:42:07 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes d'affectation des vues aux utilisateurs OPAC
// on r�utilise la m�canique des quotas...
require_once($class_path."/quotas.class.php");


class opac_views  extends quota {
	
	public function __construct(){
		global $include_path,$lang;
		
	}
	
	//formulaire d'un champ de quota...
	public function get_quota_form($prefix,$value){
		global $msg;
		
		$value= unserialize($value);
		if(!is_array($value)){
			$value = array(
				'allowed' => array(0),
				'default' => 0
			);
		}
		if(!is_array($value['allowed'])){
			$value['allowed'] = array();
		}
		$form="
		<table>
			<tr>
				<th>".$msg['opac_view_allowed']."</th>
				<th>".$msg['opac_view']."</th>
				<th>".$msg['opac_view_default']."</th>
			</tr>
			<tr>
				<td>
					<input type='checkbox' ".(in_array(0,$value['allowed']) ? "checked='checked' " : "")."name='".$prefix."[allowed][]' value='0'/>
				</td>
				<td>".$msg['opac_view_classic_opac']."</td>
				<td>
					<input type='radio' ".(0 == $value['default'] ? "checked='checked' " : "")."name='".$prefix."[default]' value='0'/>
				</td>
			</tr>";
		$query = "select opac_view_id, opac_view_name from opac_views order by opac_view_name";
		$result = mysql_query($query);
		if(mysql_num_rows($result)){
			while($row = mysql_fetch_object($result)){
				$form.="
			<tr>";
				$form.="
				<td>
					<input type='checkbox' ".(in_array($row->opac_view_id,$value['allowed']) ? "checked='checked' " : "")."name='".$prefix."[allowed][]' value='".htmlentities($row->opac_view_id,ENT_QUOTES,$charset)."'/>
				</td>
				<td>
					".htmlentities($row->opac_view_name,ENT_QUOTES,$charset)."
				</td>
				<td>
					<input type='radio' ".($row->opac_view_id == $value['default'] ? "checked='checked' " : "")."name='".$prefix."[default]' value='".htmlentities($row->opac_view_id,ENT_QUOTES,$charset)."'/>
				</td>";
				$form.="
			</tr>";
			}
		}
		$form.="	
		</table>";
		return $form;
	}
	
	function get_storable_value($value){
		return addslashes(serialize($value));
	}
}