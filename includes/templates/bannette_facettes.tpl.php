<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bannette_facettes.tpl.php,v 1.1 2013-03-19 11:19:18 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");


$tpl_facette_elt_ajax="
	<div class='row'>
		<label for='list_crit_!!i_field!!'>".htmlentities($msg['list_crit_form_facette'],ENT_QUOTES,$charset)."</label>
	</div>
	<div class='row'>
		<select id='list_crit_!!i_field!!' name='list_crit_!!i_field!!' onchange=\"load_subfields('!!i_field!!',0);\" >
			!!liste1!!
		</select>
		<div id='liste2_!!i_field!!' ></div>
		<input type='button' class='bouton' value='$msg[raz]' onclick=\"fonction_raz_facette('i_full_field_!!i_field!!');\" />
	</div>
	<script>load_subfields('!!i_field!!','!!ss_crit!!')</script>	

";

$tpl_facette_elt="
<div id='i_full_field_!!i_field!!'>
	$tpl_facette_elt_ajax
</div>	

";
$dsi_facette_tpl = "
<script src='javascript/ajax.js'></script>

<script type='text/javascript'>
	
	function add_facette(i_field){
		var i_field=document.getElementById('max_facette').value;
		
		var xhr_object=  new http_request();					
		xhr_object.request('./ajax.php?module=dsi&categ=bannettes&id_bannette=!!id_bannette!!&sub=facettes&suite=add_facette&i_field='+i_field, false,\"\",true,back_add_facette);		
		
	}	
	
	function back_add_facette(response){
		
		var i_field=document.getElementById('max_facette').value;
		
		var new_div = document.createElement('div');
		new_div.setAttribute('id','i_full_field_'+i_field);
		
		new_div.innerHTML =response;
		document.getElementById('add_facette').appendChild (new_div);
		
		document.getElementById('max_facette').value++;
	}
	
	function load_subfields(i_field,id_ss_champs){
	
		var lst = document.getElementById('list_crit_'+i_field);
		var id = lst.value;
		var id_subfields = id_ss_champs;
		var xhr_object=  new http_request();	
		var url='./ajax.php?module=dsi&categ=bannettes&id_bannette=!!id_bannette!!&sub=facettes&suite=ss_crit&i_field='+i_field+'&crit_id='+id+'&ss_crit_id='+id_ss_champs;			
		xhr_object.request(url);
					
		var div = document.getElementById('liste2_'+i_field);
		div.innerHTML = xhr_object.get_text() ;
	}
	
	function fonction_raz_facette(i_field) {
		document.getElementById(i_field).innerHTML='';
	}	
</script>
<input type='hidden' id='max_facette' name='max_facette' value='!!max_facette!!' />
<input type='button' class='bouton' value='+' onClick=\"add_facette();\"/>

<div id='add_facette'>!!facettes!!</div>

";
