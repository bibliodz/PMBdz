<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: origin.tpl.php,v 1.1 2011-12-20 13:12:44 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$origin_form="
<div class='form-contenu'>
	<form class='form-".$current_module."' name='origin' method='post' action='./admin.php?categ=!!type!!&sub=origins&action=update&id=!!id!!'>
		<h3>!!title!!</h3>
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<label class='etiquette' for='origin_name'>".$msg['origin_name']."</label>
		</div>
		<div class='row'>
			<input type='text' name='origin_name' id='origin_name' value='!!origin_name!!' />
		</div>
		<div class='row'>
			<label class='etiquette' for='origin_name'>".$msg['origin_country']."</label>
		</div>
		<div class='row'>
			<input type='text' name='origin_country' id='origin_country' value='!!origin_country!!' />
		</div>
		<div class='row'>
			<label class='etiquette' >".$msg['origin_diffusible']."</label>&nbsp;
			<input type='checkbox' name='origin_diffusible' id='origin_diffusible' value='1' !!checked!!/>
		</div>
		<div class='row'><hr /></div>
		<div class='row'>
			<div class='left'>
				<input type='button' class='bouton' onclick='history.go(-1);' value='".htmlentities($msg['origin_cancel'],ENT_QUOTES,$charset)."' />&nbsp;
				<input type='submit' class='bouton' onclick='return test_origin_form();' value='".htmlentities($msg['origin_save'],ENT_QUOTES,$charset)."' />
			</div>
			<div class='right'>
				<input type='button' class='bouton' onclick='document.location=\"./admin.php?categ=!!type!!&sub=origins&action=delete&id=!!id!!\"' value='".htmlentities($msg['origin_delete'],ENT_QUOTES,$charset)."' />
			</div>
		</div>
	</form>
	<script type='text/javascript'>
		function test_origin_form(){
			if(document.forms['origin'].origin_name.value == ''){
				alert('".htmlentities($msg['origin_need_name'],ENT_QUOTES,$charset)."');
				return false;
			} 
		}
	</script>
</div>";

$origin_tab_display = "
	<table>
		<tr>
			<th>".htmlentities($msg['origin_name'],ENT_QUOTES,$charset)."</th>
			<th>".htmlentities($msg['origin_country'],ENT_QUOTES,$charset)."</th>
			<th>".htmlentities($msg['origin_diffusible'],ENT_QUOTES,$charset)."</th>
		</tr>
		!!rows!!
	</table>
	<input type='button' class='bouton' onclick='document.location=\"./admin.php?categ=!!type!!&sub=origins&action=add\"' value='".htmlentities($msg['authorities_origin_add'],ENT_QUOTES,$charset)."'/>";