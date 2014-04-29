<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_build.tpl.php,v 1.55 2013-12-02 09:07:25 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

if($cms_url_base_cms_build){
	$build_url=$cms_url_base_cms_build;
} else $build_url=$pmb_opac_url;


function cms_gen_objet_css($name){
	$objet_css="
	<input dojoType='dijit.form.NumberSpinner' value='' smallDelta='1' constraints='{min:-2000,max:2000,places:0}' id='$name' name='$name' style= 'width:80px'
		intermediateChanges='true'
	 	onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\"
	/>
	<select id='".$name."_def' name='".$name."_def' onchange =\"cms_change_css_format_number(this,'$name');cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\">				
		<option value='auto'>auto</option>
		<option value='px'>px</option>
		<option value='%'>%</option>
		<option value='inherit'>inherit</option>
	</select>
	";

	return $objet_css;
}

$cms_edit_css="
	<script type='text/javascript'>
			
		function cms_change_css_format_number(obj,id_number){
			obj_number=document.getElementById(id_number);
			obj_number_field=document.getElementById('widget_'+id_number);
			switch(obj.options[obj.selectedIndex].value){
				case 'auto':
					obj_number_field.style.display='none';
				break;
				case 'px': 
					obj_number_field.style.display='block';
				break;
				case '%': 
					obj_number_field.style.display='block';
				break;
				case 'inherit': 
					obj_number_field.style.display='none';
				break;
			}
		}
	
	    dojo.require('dijit.form.NumberSpinner');
	  
	    function cms_add_div_change(id){	
	   		var obj =parent.frames['opac_frame'].document.getElementById(id);
	    	var div_name='add_div_'+id;
	    	var obj_div=parent.frames['opac_frame'].document.getElementById(div_name);
			if(document.getElementById('div_class_row').checked) {
				if(obj_div) return;// il existe d�g�
				var obj_div = parent.frames['opac_frame'].document.createElement('div');	
				obj_div.setAttribute('id',div_name);
				obj_div.className='row';	
				obj.parentNode.insertBefore(obj_div,obj);
				
			}else{
				//on enl�ve le div
				if(obj_div){
					obj_div=obj_div.parentNode.removeChild(obj_div);					
				}
			}	
		}
	
	</script>
	
	<div id='cms_edit_form' cms_edit_id='' >
		<h3>
			".$msg["cms_edit_css"]."
		</h3>
		<div class='row'>
			<span id='cms_edit_title_obj'>
		</span></div>
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_position"]."&nbsp;</label>
		</div>
		<div class='row'>
			<select id='cms_position' name='cms_position' onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\">	
				<option value='absolute'>absolute</option>		
				<option value='fixed'>fixed</option>		
				<option value='relative'>relative</option>
				<option value='static'>static</option>
			</select>
		</div>					
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_left"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_left")."			
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_top"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_top")."			
		</div>			
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_zindex"]."&nbsp;</label>
		</div>
		<div class='row'>
			<input dojoType='dijit.form.NumberSpinner' value='' smallDelta='10' constraints=\"{min:9,max:1550,places:0}\" 
		 		id='cms_zIndex' jsId='cms_zIndex' name='cms_zIndex'
		 		style= 'width:80px'
			 	onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\"
			/>
		
		</div>	
	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_visibility"]."&nbsp;</label>
		</div>
		<div class='row'>			
			<select id='cms_visibility' name='cms_visibility' onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\">				
				<option value='hidden'>hidden</option>
				<option value='visible'>visible</option>
			</select>
		</div>				
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_height"]."&nbsp;</label>			
		</div>
		<div class='row'>			
			".cms_gen_objet_css("cms_height")."			
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_width"]."&nbsp;</label>
		</div>
		<div class='row'>					
			".cms_gen_objet_css("cms_width")."	
		</div>		
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_float"]."&nbsp;</label>
		</div>
		<div class='row'>					
			<select id='cms_float' name='cms_float' onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\">				
				<option value='left'>left</option>
				<option value='right'>right</option>
				<option value='none'>none</option>
				<option value='inherit'>inherit</option>
			</select>
		</div>			
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_margin_top"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_margin_top")."
		</div>
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_padding_top"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_padding_top")."
		</div>	
		<div class='row'>		
			<label class='etiquette'>".$msg["cms_edit_form_margin_right"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_margin_right")."
		</div>			
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_padding_right"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_padding_right")."
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_margin_bottom"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_margin_bottom")."
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_padding_bottom"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_padding_bottom")."
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_margin_left"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_margin_left")."
		</div>					
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_padding_left"]."&nbsp;</label>
		</div>
		<div class='row'>
			".cms_gen_objet_css("cms_padding_left")."
		</div>	
		<div class='row'>
			<label class='etiquette'>".$msg["cms_edit_form_display"]."&nbsp;</label>
		</div>
		<div class='row'>			
			<select id='cms_display' name='cms_display' onchange =\"cms_change_css(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\">				
				<option value='block'>block</option>
				<option value='none'>none</option>
				<option value='inline'>inline</option>
				<option value='inline-block'>inline-block</option>
				<option value='inline-table'>inline-table</option>
				<option value='list-item'>list-item</option>
				<option value='run-in'>run-in</option>
				<option value='table'>table</option>
				<option value='table-caption'>table-caption</option>
				<option value='table-column-group'>table-column-group</option>
				<option value='table-header-group'>table-header-group</option>
				<option value='table-footer-group'>table-footer-group</option>
				<option value='table-row-group'>table-row-group</option>
				<option value='table-cell'>table-cell</option>
				<option value='table-column'>table-column</option>
				<option value='table-row'>table-row</option>
				<option value='inherit'>inherit</option>
			</select>
		</div>		
		<div class='row'>		
			<input type='checkbox' id='div_class_row' name='div_class_row' onclick=\"cms_add_div_change(document.getElementById('cms_edit_form').getAttribute('cms_edit_id'));\" value='1' >	
			".$msg["cms_edit_form_div_class_row"]."
		</div>	
	</div>
";

$cms_objet_type_selection="
	<h3>
		".$msg["cms_edit_objet_selection"]."
	</h3>
	<div class='row'>
	
		<table border='0'  width='100%' cellspacing='0'>
		<tr>
			<td>"
				.$msg["cms_dragable_type"]."		
			</td>		
			<td>
				<input  type='radio' id='cms_dragable_cadre' name='cms_dragable_type' value='zone' onclick=\"cms_drag_activate_form();\" ><label for='cms_dragable_cadre'>".$msg["cms_dragable_zone"]."</label>
			</td>	
			<td>		
				<input  type='radio' id='cms_dragable_object' name='cms_dragable_type' value='cadre'  checked='checked' onclick=\"cms_drag_activate_form();\" ><label for='cms_dragable_object'>".$msg["cms_dragable_cadre"]."</label>
			</td>
		</tr>	
		<tr>
			<td>
				".$msg["cms_receptable_type"]."		
			</td>		
			<td>
				<input  type='radio' id='cms_receptable_conteneur' name='cms_receptable_type' value='conteneur' onclick=\"cms_drag_activate_form();\" ><label for='cms_receptable_conteneur'>".$msg["cms_receptable_conteneur"]."</label>
			</td>	
			<td>		
				<input  type='radio' id='cms_receptable_cadre' name='cms_receptable_type'  value='zone' checked='checked' onclick=\"cms_drag_activate_form();\" ><label for='cms_receptable_cadre'>".$msg["cms_receptable_zone"]."</label>
			</td>
		</tr>	
		</table>
	</div>	
	<div class='row'>		
		<input type='button'  class='bouton'  id='cms_drag_activate_button' active='' value='".$msg["cms_activer_drag_drop"]."'  onclick=\"cms_drag_activate_form(); return false;\">		
		<input type='button' class='bouton' value='".$msg["cms_build_reload"]."' onclick=\"cms_reload_opac(); return false;\">
	</div>	
	";
				
$cms_edit_objet="	
	<script type='text/javascript'>
	
		function cms_opac_loaded(url){
			document.getElementById('cms_drag_activate_button').setAttribute('active','1')
			cms_drag_activate_form();
			cms_refresh_cadres_list();
			
			var frame=document.getElementById('opac_frame');
			document.getElementById('cms_navig_information').innerHTML=url;
		}
		function cms_drag_activate_form(){
			if(document.getElementById('cms_drag_activate_button').getAttribute('active') ){
				cms_drag_activate(0,0,0);
				document.getElementById('cms_drag_activate_button').style.backgroundColor ='';
				document.getElementById('cms_drag_activate_button').setAttribute('active','')
				document.getElementById('cms_drag_activate_button').value='".$msg["cms_activer_drag_drop"]."';
				return;
			} else{
				document.getElementById('cms_drag_activate_button').style.backgroundColor ='#00FF00';
				document.getElementById('cms_drag_activate_button').setAttribute('active','1')
				document.getElementById('cms_drag_activate_button').value='".$msg["cms_reset_drag_drop"]."';
			}
			
			var radioButtons=document.getElementsByName('cms_dragable_type');
			var cms_dragable_type=0;
			for (var i=0; i < radioButtons.length; i ++) {	           
	            if (radioButtons[i].checked) {
                    cms_dragable_type=radioButtons[i].value;
                }
	        } 
	        
			var radioButtons=document.getElementsByName('cms_receptable_type');
			var cms_receptable_type=0;
			for (var i=0; i < radioButtons.length; i ++) {	           
	            if (radioButtons[i].checked) {
                    cms_receptable_type=radioButtons[i].value;
                }
	        } 	      
	        
			cms_drag_activate(1,cms_dragable_type,cms_receptable_type);
		}
	</script>

			

";	
$cms_build_cadres_in_page_tpl="
	<script type='text/javascript'>

		var cms_cadre_portail_list=new Array();
		!!cms_cadre_portail_list!!
	</script>
	<table id='cms_portail_cadres_list'>
		!!items!!
	</table>
";			           

$cms_build_cadres_not_in_page_tpl="	
	<table id='cms_portail_cadres_not_in_page_list'>
		!!items!!	
	</table>
";

$cms_build_cadre_tpl_item="
<tr class='!!odd_even!!' style='cursor: pointer;' onmouseout=\"this.className='!!odd_even!!'\" onmouseover=\"this.className='surbrillance'\"
 	onclick=\"cms_show_obj('!!cadre_object!!_!!id_cadre!!');return false; \" >
	<td>	
		<a onclick=\"cms_build_load_module('!!cadre_object!!','get_form',!!id_cadre!!);\" href='#' > 
			<img class='icon' width='16' height='16' title='".$msg["cms_build_edit_bt"]."' alt='".$msg["cms_build_page_add_bt"]."' src='./images/b_edit.png'  >
		</a>	
		!!cadre_name!!
		<div data-dojo-type='dijit/form/DropDownButton' style='float:right;'>
		    <span></span>
		    <!-- The dialog portion -->
		    <div data-dojo-type='dijit/TooltipDialog' id='ttDialog_!!id_cadre!!'>   
		    	<label class='etiquette' for='cadre_classement_list'>".$msg['cms_build_cadre_classement_list']."</label> 		   		
		   		<br />		        
				<select data-dojo-type='dijit/form/ComboBox' id='classement_!!id_cadre!!' name='classement_!!id_cadre!!'>
				    !!classement_list!!
				</select>		        	
		        <br />		
		 		<button data-dojo-type='dijit/form/Button'  onclick=\"cms_save_cadre_classement(!!id_cadre!!);return false; \"  type='submit'>".$msg["cms_build_cadre_classement_save"]."</button>
		    </div>
		</div>			
	</td>			
</tr>
";	

$cms_build_cadre_tpl_not_in_page_item="
<tr class='!!odd_even!!' style='cursor: pointer;' onmouseout=\"this.className='!!odd_even!!'\" onmouseover=\"this.className='surbrillance'\" >
	<td>	
		<a onclick=\"!!load_page_opac!!cms_build_load_module('!!cadre_object!!','get_form',!!id_cadre!!);\" href='#' > 
			<img class='icon' width='16' height='16' title='".$msg["cms_build_edit_bt"]."' alt='".$msg["cms_build_page_add_bt"]."' src='./images/b_edit.png'  >
		</a>	
		!!cadre_link!!
		<div data-dojo-type='dijit/form/DropDownButton' style='float:right'>
		    <span></span>
		    <!-- The dialog portion -->
		    <div data-dojo-type='dijit/TooltipDialog' id='ttDialog_!!id_cadre!!'>   
		    	<label class='etiquette' for='cadre_classement_list'>".$msg['cms_build_cadre_classement_list']."</label> 		   		
		   		<br />		        
				<select data-dojo-type='dijit/form/ComboBox' id='classement_!!id_cadre!!' name='classement_!!id_cadre!!'>
				    !!classement_list!!
				</select>	
		        <br />		
		 		<button data-dojo-type='dijit/form/Button'  onclick=\"cms_save_cadre_classement(!!id_cadre!!);return false; \"  type='submit'>".$msg["cms_build_cadre_classement_save"]."</button>
		    </div>
		</div>			
	</td>			
</tr>
";
		
$cms_build_pages_tpl="
<script type='text/javascript'>
    dojo.require('dijit.form.Button');
    dojo.require('dijit.Dialog');    
    dojo.require('dojo.parser');
    dojo.require('dojox.layout.ContentPane');
    dojo.require('dojox.widget.Dialog');
    dojo.require('dojox.widget.DialogSimple');
    
    function cms_build_page_edit_add(id){
     	if(!dijit.byId('cms_build_dialog')){
	        //creates a new dialog
	        try {
	        	var myDijit = new dojox.widget.DialogSimple({title: 'Referent',executeScripts:true, id:'cms_build_dialog'});    
			}catch(e){
				if(typeof console != 'undefined') {console.log(e);}
			};
		}  				
        //get the dialog
        var dialogDijit = dijit.byId('cms_build_dialog');   
        dialogDijit.set('title','".$msg["cms_menu_pages"]."');     
        var path = './ajax.php?module=cms&categ=pages&sub=edit&id='+id
        dialogDijit.attr('href', path);        
     	dialogDijit.startup();     	      
        dialogDijit.show();  		
	}
	 
	function cms_build_page_add(page){	
		var frame=document.getElementById('opac_frame')
		var url='".$build_url."index.php?cms_build_activate=1&lvl=cmspage&pageid='+page;
		frame.setAttribute('src', url);		
	}
	
	</script>
	<div id='cms_build_pages_list'>	
		<table>
			!!items!!
		</table>	
	</div>
	<input type='button' class='bouton' value='".$msg["cms_build_page_add_bt"]."' onclick=\"cms_build_page_edit_add('0'); return false;\">
";

$cms_build_pages_tpl_item="
<a href='#' onclick=\"cms_build_page_edit_add('!!id!!');\"><img class='icon' width='16' height='16' title='".$msg["cms_build_edit_bt"]."' alt='".$msg["cms_build_page_add_bt"]."' src='./images/b_edit.png'> </a> 	
<a href='#' onclick=\"cms_build_page_add('!!id!!');\">!!name!!</a> 
<br/>
";	

$cms_build_pages_tpl_item_old="
<tr class='!!odd_even!!' style='cursor: pointer;' onmouseout=\"this.className='!!odd_even!!'\" onmouseover=\"this.className='surbrillance'\" >
	<td>	
		<a href='#' onclick=\"cms_build_page_edit_add('!!id!!');\"><img class='icon' width='16' height='16' title='".$msg["cms_build_edit_bt"]."' alt='".$msg["cms_build_page_add_bt"]."' src='./images/b_edit.png'> </a> 	
		<a href='#' onclick=\"cms_build_page_add('!!id!!');\">!!name!!</a> 
		<div data-dojo-type='dijit/form/DropDownButton' style='float:right;'>
		    <span></span>
		    <!-- The dialog portion -->
		    <div data-dojo-type='dijit/TooltipDialog' id='ttDialog_page_!!id!!'>   
		    	<label class='etiquette' for='cadre_classement_list'>".$msg['cms_build_page_classement_list']."</label> 		   		
		   		<br />		        
				<select data-dojo-type='dijit/form/ComboBox' id='classement_page!!id!!' name='classement_page!!id!!'>
				    !!classement_page_list!!
				</select>		        	
		        <br />		
		 		<button data-dojo-type='dijit/form/Button'  onclick=\"cms_save_page_classement(!!id!!);return false; \"  type='submit'>".$msg["cms_build_page_classement_save"]."</button>
		    </div>
		</div>				
	</td>			
</tr>
";	

$cms_build_pages_ajax_tpl="
	<table>
		!!items!!
	</table>	
";

$cms_build_modules_tpl="
  <script type='text/javascript'>
        function cms_build_load_module(module,action,id){
            if(!module.match('cms_module_')){
                 module = 'cms_module_'+module;
            }                 
	    	if(!dijit.byId('cms_build_dialog')){
		        //creates a new dialog
		        var myDijit = new dojox.widget.DialogSimple({
		        	title: '".$msg["cms_build_modules"]."',
		        	executeScripts:true,
		        	id:'cms_build_dialog'
		        });
			}
			var dialogDijit = dijit.byId('cms_build_dialog'); 
			dialogDijit.set('title','".$msg["cms_build_modules"]."');
		
			//d�finition du post !
	        var post_datas = '&callback=window.parent.cms_build_save_module';
	        post_datas+='&cancel_callback=window.parent.cms_build_cancel_module';
	        post_datas+='&delete_callback=window.parent.cms_build_delete_callback';
	      	post_datas+='&cms_build_info=' + parent.frames['opac_frame'].document.getElementById('cms_build_info').value;

			var xhrAgrs = {
				url : './ajax.php?module=cms&categ=module&elem='+module+'&action='+action+'&id='+id,
				postData : post_datas,
				handelAs : 'text/html',
				load : function(data){	
					dialogDijit.set('content',data);
     				dialogDijit.startup(); 	 
  					dialogDijit.show();	
				}
			}
			dojo.xhrPost(xhrAgrs);
        }
      	
        function cms_build_cancel_module(data){
        	dijit.byId('cms_build_dialog').hide(); 	
        }
        		        
         function cms_build_delete_callback(data){
        	dijit.byId('cms_build_dialog').hide(); 
        	
        	cms_refresh_cadres_list();        	
			// delete du cadre dans l'opac si pr�sent
			var cadre=parent.frames['opac_frame'].document.getElementById(data.dom_id);
			if(cadre){
				cadre.parentNode.removeChild(cadre);
			}			
       	}
        		               
        function cms_build_save_module(data){
        	dijit.byId('cms_build_dialog').hide();
        	var content = '<h2>'+data.name+'</h2><p>".$msg['cms_module_refresh_frame']."</p>';
			cms_build_new_cadre(data.dom_id,content);
			cms_refresh_cadres_list();
			
			cms_drag_activate_form();
        }        
	</script>	
	!!items!!
";		

$cms_build_versions_tpl="

	<script type='text/javascript'>
	    dojo.require('dijit.form.Button');
	    dojo.require('dijit.Dialog');    
	    dojo.require('dojo.parser');
	    dojo.require('dojox.layout.ContentPane');
	    dojo.require('dojox.widget.Dialog');
	    dojo.require('dojox.widget.DialogSimple');	   
	    
	    function cms_build_version_edit_add(id){	    
	    	if(!dijit.byId('cms_build_dialog')){
		        //creates a new dialog
		        try {
		        	var myDijit = new dojox.widget.DialogSimple({title: 'Referent',executeScripts:true, id:'cms_build_dialog'});
				}catch(e){
					if(typeof console != 'undefined') {console.log(e);}
				};
			}   		
	        //get the dialog
	        var dialogDijit = dijit.byId('cms_build_dialog');  
	        dialogDijit.set('title','".$msg["cms_build_versions"]."');     
	        
	        var path = './ajax.php?module=cms&categ=versions&sub=edit&id='+id
	        dialogDijit.attr('href', path);        
	        
	     	dialogDijit.startup();     	      
	        dialogDijit.show();
		}
			 
		function cms_build_version_select(page){	
			var frame=document.getElementById('opac_frame')
			frame.setAttribute('src', '".$build_url."index.php?cms_build_activate=1&lvl=cmspage&pageid='+page);	
		}	
	</script>	
	<div id='cms_build_versions_list'>
		!!items!!
	</div>
	<input type='button' class='bouton' value='".$msg["cms_build_version_add_bt"]."' onclick=\"cms_build_version_edit_add('0'); return false;\">
";


 
$cms_build_versions_tpl_item="
<a href='#' onclick=\"cms_build_version_edit_add('!!id!!');\"><img class='icon' width='16' height='16' title='".$msg["cms_build_version_edit_bt"]."' alt='".$msg["cms_build_version_edit_bt"]."' src='./images/b_edit.png'> </a> 	
!!name!! !!opac_default!! !!cms_in_use!!<br/>
";	

$cms_build_versions_ajax_tpl="
	!!items!!
";

$cms_build_version_form_ajax_tpl = "
	<script type='text/javascript'>
		function confirm_delete_cms(id){
			
			var sup = confirm('".$msg['cms_version_confirm_suppr']."');
			if(!sup) return false;
			
			var post_data='';	
			// Envoi du tout au serveur
			var http=new http_request();		
			var url = './ajax.php?module=cms&categ=versions&sub=del_cms&id='+id;
			http.request(url,true,post_data); 					
			document.getElementById('cms_build_versions_list').innerHTML= http.get_text();
			dijit.byId('cms_build_dialog').hide();
		}
		
		function test_form(form) {		
			if(!form.name.value){
		    	alert('".$msg["cms_version_no_name"]."');
				return false;
		    }
	    }
	    
	    function cms_version_ajax_save(id){
			var post_data='';	
			// Envoi du tout au serveur
			var http=new http_request();		
			var url = './ajax.php?module=cms&categ=versions&sub=save&id='+id;
			post_data='name=' + document.getElementById('name').value;
			post_data+='&comment=' + document.getElementById('comment').value;
			var opac_default='';
			if(document.getElementById('opac_default').checked) opac_default=1;
			post_data+='&opac_default=' + opac_default;
			http.request(url,true,post_data); 	
			return http.get_text();
			
	    }
	    
	    function confirm_delete_version(id){			
			// Contexte de la page Opac: cms_build_info
			var post_data='';	
			// Envoi du tout au serveur
			var http=new http_request();		
			var url = './ajax.php?module=cms&categ=versions&sub=del_version&id='+id;
			http.request(url); 	
			document.getElementById('tr_version_'+id).parentNode.removeChild(document.getElementById('tr_version_'+id));
			return true;	
		}
	</script>

		<h3>!!form_title!!</h3>
		<div class='form-contenu'>
			<div class='row'>
				<label class='etiquette' for='name'>".$msg['cms_version_form_title']."</label>
			</div>
			<div class='row'>
				<input type=text id='name' name='name' value=\"!!name!!\" class='saisie-50em' />
			</div>		
		
			<div class='row'>
				<label class='etiquette' for='comment'>".$msg['cms_version_form_comment']."</label>
			</div>
			<div class='row'>
				<textarea id='comment' name='comment' cols='120' rows='5'>!!comment!!</textarea>
			</div>
			<div class='row'>
				<label class='etiquette' for='opac_default'>".$msg['cms_build_cms_opac_default']."</label>
				<input type='checkbox' id='opac_default' !!opac_default_checked!! value='1' name='opac_default'>
			</div>		
			<div class='row'>
				<label class='etiquette' for='version_tag'>".$msg['cms_version_form_version_tag']."</label>
			</div>	
			<div class='row'>	

				<h3>".$msg["cms_build_versions"]."</h3>
				<table class='cms_build_versions_list'>	
					<tr>			
						<th>".$msg['cms_build_versions_date']."</th>	
						
						<th></th>			
					</tr>
					!!version_list!!
				</table>
				
			</div>				

			
			<div class='row'>&nbsp;</div>
		</div>
		<div class='row'>
			<div class='left'>
				<input type='submit'  class='bouton' value='".$msg['cms_page_form_save']."'  
				onClick=\" 
					document.getElementById('cms_build_versions_list').innerHTML=cms_version_ajax_save(!!id!!);
					dijit.byId('cms_build_dialog').hide();
				 \" />			
			 </div>
			<div class='right'>
				!!form_suppr!!
			</div>
		</div>
		<div class='row'></div>
";

$cms_build_version_del_button_tpl ="
			<input type='button'  class='bouton' onclick='confirm_delete_cms(!!id!!);' value='".$msg['cms_build_version_del_bt']."'/>
";
$cms_build_version_tags_item ="
	<tr id='tr_version_!!id_version!!' class='!!odd_even!!' style='cursor: pointer' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='!!odd_even!!'\" >
		<td onmousedown=\"
			dijit.byId('cms_build_dialog').hide();
			document.location='./cms.php?categ=build&sub=block&build_id_version=!!id_version!!';\" >
			!!version_date!!
		</td>		

		<td>
			<input class='bouton' type='button' onclick=\"confirm_delete_version(!!id_version!!); \" value='X'>
		</td>
	</tr>
";


$cms_build_block_tpl="
<script src='./javascript/cms/cms_build.js'></script>
<script src='./javascript/cms/cms_drag_n_drop.js'></script>
<script src='./javascript/cms/cms_drop.js'></script>
<script src='./javascript/cms/cms_pages.js'></script>

<script type='text/javascript'>

	dojo.require('dojo.parser');
	dojo.require('dijit.layout.BorderContainer');
	dojo.require('dijit.layout.TabContainer');
	dojo.require('dijit.layout.AccordionContainer');
	dojo.require('dijit.layout.ContentPane');
	dojo.require('dijit.form.DropDownButton');
	dojo.require('dijit.TooltipDialog');
	dojo.require('dijit.form.TextBox');
	dojo.require('dijit.form.Button');
	dojo.require('dijit.form.ComboBox');
	dojo.require('dojo.store.Memory');
	
	function cms_save_cadre_classement(id_cadre){
		var id= 'classement_'+id_cadre;
		var classement=document.getElementById(id).value;
		cms_build_save_cadre_classement(id_cadre,classement);	
		cms_refresh_cadres_list();
	}
		
	function cms_save_page_classement(id_page){
		var id= 'classement_'+id_page;
		var classement=document.getElementById(id).value;		
		document.getElementById('cms_build_pages_list').innerHTML=cms_build_save_page_classement(id_page,classement);	;
	}
	
	function cms_change_placement(id_cadre,classement){
		var id= 'classement_'+id_cadre;
		var classement=document.getElementById(id).value=classement;
	}
	
	function cms_refresh_cadres_list(){
		dojo.forEach(dijit.findWidgets(dojo.byId('cms_cadre_list_in_page')), function(w) {
			w.destroyRecursive();		    
		});	
		dojo.forEach(dijit.findWidgets(dojo.byId('cms_cadre_list_not_in_page')), function(w) {
			w.destroyRecursive();		    
		});		
		var list=cms_build_load_cadres_in_page_list();				
		dojo.html.set('cms_cadre_list_in_page', list, { parseContent:true });		
			
        var list=cms_build_load_cadres_not_in_page_list();		
		dojo.html.set('cms_cadre_list_not_in_page', list, { parseContent:true });
	}	
		
		
	function cms_reload_opac(){
		if(document.getElementById('cms_drag_activate_button').getAttribute('active') ){
			cms_drag_activate(0,0,0);
			document.getElementById('cms_drag_activate_button').style.backgroundColor ='';
			document.getElementById('cms_drag_activate_button').setAttribute('active','')
			document.getElementById('cms_drag_activate_button').value='".$msg["cms_activer_drag_drop"]."';
		} 	

		var url = parent.frames['opac_frame'].location.href;		
		url=url.replace('&cms_build_activate=1&build_id_version=!!id_version!!','')
		parent.frames['opac_frame'].location=url;
			
	}
	
	function cms_load_opac_page(obj,opac_url){
		if(document.getElementById('cms_drag_activate_button').getAttribute('active') ){
			cms_drag_activate(0,0,0);
			document.getElementById('cms_drag_activate_button').style.backgroundColor ='';
			document.getElementById('cms_drag_activate_button').setAttribute('active','')
			document.getElementById('cms_drag_activate_button').value='".$msg["cms_activer_drag_drop"]."';
		} else{
		}				
		parent.frames['opac_frame'].location=opac_url;			
	}
	
	function cms_save_opac(){	
		var version=cms_drag_record();
		var http=new http_request();	
		var url = '".$build_url."ajax.php?module=cms&categ=build&action=set_version&value='+version;	
		http.request(url);	
		if(confirm('".$msg["cms_memoriser_drag_drop_ok"]."')){			
			cms_reload_opac();	
		}else{
		}
	}
</script>

<div id='cms_build_navig_informations'>
	<div data-dojo-type='dijit/form/DropDownButton'>
	    <span>".$msg['cms_build_navig_informations']."</span>
	    <div data-dojo-type='dijit/TooltipDialog'>
	        <h3>".$msg['cms_build_navig_information']."</h3>
	        <div id=cms_navig_information>
			</div>
	    </div>
	</div>
</div>
<div dojoType='dijit.layout.BorderContainer' design='sidebar' gutters='true' style='width: 100%; height: 800px;'>
	
	<div dojoType='dijit.layout.ContentPane'  region='center' >
		<IFRAME name='opac_frame' id='opac_frame' src='!!opac_url!!index.php?database=".LOCATION."&cms_build_activate=1&build_id_version=!!id_version!!' style='background-color:#FFFFFF;width:100%;height:710px;border:0px solid #000'></IFRAME>	
	</div>
	<div dojoType='dijit.layout.ContentPane' region='left' splitter='true' style='width:300px;' >
			
		  <div dojoType='dijit.layout.TabContainer' >			  
		        <div dojoType='dijit.layout.ContentPane' title='".$msg["cms_build_objet_content"]."' selected='true'>			           
		           		   
		           <div dojoType= 'dijit.layout.AccordionContainer' >		        
				       	
				        <div dojoType= 'dijit.layout.AccordionPane' title='".$msg["cms_build_objet_def"]."' selected='true'>
				        	$cms_objet_type_selection
				        	$cms_edit_objet
				        	<div class='row'>
				        		". gen_plus_titre("cadre_of_opac",$msg["cms_edit_sel_objet_list"],"
				        		<div class='row' id='cms_edit_sel_objet_list'>
									<table id='cms_edit_sel_objet_list_table' border='0'  width='100%' cellspacing='0'>
									</table>
								</div>
				        		
				        		",1)."		
							</div>								
				        	<div class='row'>
				        		". gen_plus_titre("cadre_of_cms",$msg["cms_edit_sel_portail_list"],"
					        	<div class='row' id='cms_cadre_list_in_page'>	
				        			!!cadre_list_in_page!!							
								</div>				        		
				        		",1)."		
							</div>								
				        	<div class='row'>
				        		". gen_plus_titre("cadre_not_in_page",$msg["cms_build_cadre_not_in_page"],"
								<div class='row' id='cms_cadre_list_not_in_page'>	
									!!cadre_list_not_in_page!!								
								</div>				        		
				        		",0)."		
							</div>
				        	<div class='row'>
				        		". gen_plus_titre("zone_of_opac",$msg["cms_edit_sel_cadre_list"],"
								<div class='row' id='cms_edit_sel_cadre_list'>
									<table id='cms_edit_sel_cadre_list_table' border='0'  width='100%' cellspacing='0'>
									</table>
								</div>
				        		
				        		",1)."		
							</div>
							<div class='row'>
							</div>
				        </div>						
				        <div dojoType= 'dijit.layout.AccordionPane' title='".$msg["cms_build_modules"]."'>	
				        	!!cms_objet_modules!!						          
				        </div>		
				        <div dojoType= 'dijit.layout.AccordionPane' title='".$msg["cms_build_pages"]."'>
				        	!!cms_objet_pages!!				          
				        </div>		
				        <div dojoType= 'dijit.layout.AccordionPane' title='".$msg["cms_build_versions"]."'>
				        	!!cms_objet_versions!!				          
				        </div>			
		      		</div>
		        </div>
		        <div dojoType='dijit.layout.ContentPane' title='".$msg["cms_build_css_content"]."'>
		           $cms_edit_css
		        </div>
		    </div>		
	</div>
	<div dojoType='dijit.layout.ContentPane' region='bottom' >	
		<input type='button' class='bouton' value='".$msg["cms_memoriser_drag_drop"]."' onclick=\"cms_save_opac(); return false;\">
		<input type='button' class='bouton' value='".$msg["cms_build_reload"]."' onclick=\"cms_reload_opac(); return false;\">
	</div>
</div>	

<script type='text/javascript'>
	var cms_contener_list=new Array();
	var cms_zone_list=new Array();
	var cms_zone_list_dragable=new Array();
	var cms_objet_list=new Array();
	!!cms_objet_list_declaration!!
	cms_build_init();	
	
</script>
";			   
	         
			           