<?php
// +-------------------------------------------------+
// | PMB                                                                      |
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sel_ontology.tpl.php,v 1.2 2013-09-04 08:52:12 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");



//-------------------------------------------
//	$sel_header : header
//-------------------------------------------
$sel_header = "
<div class='row'>
	<label for='ontology_search_field' class='etiquette'>".$msg["ontology_selector"]."!!ontology_search_field!!</label>
	</div>
<div class='row'>
";

//-------------------------------------------
//	$jscript : script de m.a.j. du parent
//-------------------------------------------
if($dyn){
	$jscript = "
	<script type='text/javascript'>
	
	function set_parent(f_caller, id_value, libelle_value,callback)
	{
		var w = parent;
		n_categ=w.opener.document.forms[f_caller].elements['nb_".$racine."'].value;
		flag = 1;
	
		//Vérification que la catégorie n'est pas déjà sélectionnée
		for (i=0; i<n_categ; i++) {
			if (w.opener.document.getElementById('f_".$racine."'+'_'+i).value==id_value) {
				alert('".$msg["term_already_in_use"]."');
				flag = 0;
				break;
			}
		}
	
		if (flag) {
			for (i=0; i<n_categ; i++) {
				if ((w.opener.document.getElementById('f_".$racine."'+'_'+i).value==0)||(w.opener.document.getElementById('f_".$racine."'+'_'+i).value=='')) break;
			}
	
			if (i==n_categ) w.opener.ontology_add_".$racine."();
			w.opener.document.getElementById('f_".$racine."'+'_'+i).value = id_value;
			w.opener.document.getElementById('".$racine."'+'_'+i).value = reverse_html_entities(libelle_value);
		}
	}
	
	</script>
	";
}else{
	$jscript = "
	<script type='text/javascript'>
	
	function set_parent(f_caller, id_value, libelle_value,callback)
	{
		window.opener.document.forms[f_caller].elements['$code'].value = id_value;
		window.opener.document.forms[f_caller].elements['$label'].value = reverse_html_entities(libelle_value);
		if(callback)
			window.opener[callback]('$infield');
		window.close();
	}
	
	</script>
	";
}


//-------------------------------------------
//	$sel_search_form : module de recherche
//-------------------------------------------
$sel_search_form ="
<form name='search_form' method='post' action='$base_url'>
	<input type='text' name='f_user_input' value=\"!!deb_rech!!\">
	&nbsp;
	<input type='submit' class='bouton_small' value='$msg[142]' />
</form>
<script type='text/javascript'>
	document.forms['search_form'].elements['f_user_input'].focus();
</script>
<hr />
";

//-------------------------------------------
//	$sel_footer : footer
//-------------------------------------------
$sel_footer = "
</div>
";
