<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: external_services.tpl.php,v 1.2 2013-04-11 08:21:05 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

//Administration g�n�rale
$es_admin_general="
<form class='form-$current_module' id='es_rights' name='es_rights' method='post' action='./admin.php?categ=external_services&sub=general'>
	<h3>D&eacute;finition des droits pour les groupes et les m&eacute;thodes</h3>
	<div class='form-contenu'>
	<input type='hidden' name='is_not_first' value='1'/>
	!!table_rights!!
	</div>
	<div class='row'>
		<input type='button' value='Annuler' class='bouton' onClick='document.location=\"admin.php?categ=external_services\"'/>&nbsp;
		<input type='button' value='Enregistrer' class='bouton' onClick='this.form.submit()'/>
	</div>
</form>";

//Par utilisateur
$es_admin_peruser="
<form class='form-$current_module' id='es_rights' name='es_rights' method='post' action='./admin.php?categ=external_services&sub=peruser'>
	<h3>D&eacute;finition des droits pour l'utilisateur !!user!!</h3>
	<div class='form-contenu'>
	<input type='hidden' name='is_not_first' value=''/>
	!!table_rights!!
	</div>
	<div class='row'>
		<input type='button' value='Annuler' class='bouton' onClick='document.location=\"admin.php?categ=external_services\"'/>&nbsp;
		<input type='button' value='Enregistrer' class='bouton' onClick='this.form.is_not_first.value=1; this.form.submit()'/>
	</div>
</form>";
?>