<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_result.tpl.php,v 1.12 2013-12-10 09:06:14 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// template for PMB OPAC
/*
$search_result_header= "<div><span>";

$search_result_footer ="</span></div>";

*/

/*
 * template search lvl1 recherche affili�e
 *  !!mode!! : mode de recherche
 *  !!search_type!! : type de recherche
 *  !!label!! : libell� du mode de recherche
 * 	!!style!! : style du block (utile pour masquer ou non le block...) 
 *  !!link!! lien en fonction du nombre de r�sultat affili�s
 *  !!user_query!! : query cherch�
 *  !!nb_results!! : nombre de r�sultats dans le catalogue
 *  !!form_name!! : nom du formulaire � soumettre...
 *  !!form!! : le formulaire � soumettre...
 */
$search_result_affiliate_lvl1 = "
<div id='!!mode!!_result' !!style!!>
	<strong>!!label!!</strong>
	<blockquote id='!!mode!!_result_blockquote'>
		<div id='!!mode!!_results_in_catalog'>
			<strong>".$msg['in_catalog']."</strong> !!nb_result!! ".$msg['results']." 	
			!!link!!	
			
		</div>
		<div id='!!mode!!_results_affiliate'>
			<strong>".$msg['in_affiliate_source']."</strong><img src='images/patience.gif' />
		</div>
		<script type='text/javascript'>
			var !!mode!!_search = new http_request();
			!!mode!!_search.request('./ajax.php?module=ajax&categ=search',true,'&type=!!mode!!&search_type=!!search_type!!&user_query=!!user_query!!',true,!!mode!!Results);
			function !!mode!!Results(response){
				var rep = eval('('+response+')');
				var div = document.getElementById('!!mode!!_results_affiliate');
				div.innerHTML='';
				var strong = document.createElement('strong');
				strong.innerHTML = \"".$msg['in_affiliate_source']."\";
				div.appendChild(strong);
				var text_node = document.createTextNode(' '+(rep.nb_results.total ? rep.nb_results.total : rep.nb_results) + ' ". $msg['results']." ');
				div.appendChild(text_node);
				if(rep.nb_results>0 || rep.nb_results.total>0){
					var a = document.createElement('a');
					a.setAttribute('href','#');
					a.innerHTML = \"".$msg['suite']."&nbsp;<img src='./images/search.gif' border='0' align='absmiddle'/>\";
					if(a.addEventListener){
						a.addEventListener('click',function(){
							document.!!form_name!!.action='./index.php?lvl=more_results&tab=affiliate';
							document.!!form_name!!.submit();
							return false;
						},true);
					}else if(a.attachEvent){
						a.attachEvent('onclick',function(){
							document.search_objects.action='./index.php?lvl=more_results&tab=affiliate';
							document.search_objects.submit();
							return false;
						});
					}else{
						a.addEvent('onclick',function(){
							document.search_objects.action='./index.php?lvl=more_results&tab=affiliate';
							document.search_objects.submit();
							return false;
						});
					}
					div.appendChild(a);
					document.getElementById('!!mode!!_result').style.display = 'block';
				}
			}
		</script>
	</blockquote>
	<div style=search_result>
		!!form!!
	</div>
</div>";

$search_extented_result_affiliate_lvl1 = "
<div id='!!mode!!_result' !!style!!>
	<strong>!!label!!</strong>
	<blockquote id='!!mode!!_result_blockquote'>
		<div id='!!mode!!_results_in_catalog'>
			<strong>".$msg['in_catalog']."</strong> !!nb_result!! ".$msg['results']." 	
			!!link!!	
			
		</div>
		<div id='!!mode!!_results_affiliate'>
			<strong>".$msg['in_affiliate_source']."</strong><img src='images/patience.gif' />
		</div>
		<script type='text/javascript'>
			var !!mode!!_search = new http_request();
			!!mode!!_search.request('./ajax.php?module=ajax&categ=search',true,'&type=!!mode!!&search_type=!!search_type!!&user_query=!!user_query!!',true,!!mode!!Results);
			function !!mode!!Results(response){
				var rep = eval('('+response+')');
				var div = document.getElementById('!!mode!!_results_affiliate');
				div.innerHTML='';
				var strong = document.createElement('strong');
				strong.innerHTML = \"".$msg['in_affiliate_source']."\";
				div.appendChild(strong);
				var text_node = document.createTextNode(' '+(rep.nb_results.total ? rep.nb_results.total : rep.nb_results) + ' ". $msg['results']." ');
				div.appendChild(text_node);
				if(rep.nb_results>0 || rep.nb_results.total>0){
					var a = document.createElement('a');
					a.setAttribute('href','#');
					a.innerHTML = \"".$msg['suite']."&nbsp;<img src='./images/search.gif' border='0' align='absmiddle'/>\";
					if(a.addEventListener){
						a.addEventListener('click',function(){
							document.!!form_name!!.action='./index.php?lvl=more_results&mode=extended&tab=affiliate';
							document.!!form_name!!.submit();
							return false;
						},true);
					}else{
						a.addEvent('onclick',function(){
							document.search_objects.action='./index.php?lvl=more_results&mode=extended&tab=affiliate';
							document.search_objects.submit();
							return false;
						});
					}
					div.appendChild(a);
					document.getElementById('!!mode!!_result').style.display = 'block';
				}
			}
		</script>
	</blockquote>
	<div style=search_result>
		!!form!!
	</div>
</div>";