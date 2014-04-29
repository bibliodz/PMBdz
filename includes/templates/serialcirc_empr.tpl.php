<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serialcirc_empr.tpl.php,v 1.1 2012-03-13 13:48:06 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");


$empr_serialcirc_tmpl="			
<div class='row'>	
 <hr/>
</div>		
<div class='row'>
	<div class='left'>
		<h3>".htmlentities($msg["serialcirc_empr_title"],ENT_QUOTES,$charset)."</h3>
	</div>
	
</div>	
<div class='row'></div>
	<table width='100%' class='sortable'>
		<tr>
			<th>
				".htmlentities($msg["serialcirc_empr_perio"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_empr_abt"],ENT_QUOTES,$charset)."
			</th>
			<th>
				".htmlentities($msg["serialcirc_empr_bulletinage"],ENT_QUOTES,$charset)."
			</th>
		</tr>
		!!serialcirc_empr_list!!
	</table>
";
$empr_serialcirc_tmpl_item="
		<tr>
			<td>
				!!periodique!!
			</td>
			<td>
				!!abt!!
			</td>
			<td>
				!!bulletinage_see!!
			</td>
		</tr>
";		