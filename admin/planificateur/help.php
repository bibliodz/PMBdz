<?php
// +--------------------------------------------------------------------------+
// | PMB est sous licence GPL, la réutilisation du code est cadrée            |
// +--------------------------------------------------------------------------+
// $Id: help.php,v 1.1 2011-07-29 12:32:11 dgoron Exp $

//Impression

$base_path = "../..";
$base_auth = "ADMINISTRATION_AUTH";
$base_title = "";
$base_nobody=1;
$base_noheader=1;

require($base_path."/includes/init.inc.php");

if ($action_help=="configure_time") {
//	$param['bg_color']		= '#EEEEEE';
//	print '<style type="text/css">
//		<!--
//		.row_help { background-color : '.$param['bg_color'].'; }
//		-->
//		</style>';

	print $std_header;
	print "<h3>".$msg["planificateur_help_perio"]."</h3>\n";
	print "<table >
		<tr>
			<th></th>
			<th>".$msg["planificateur_help_format_h"]."</th>
			<th>".$msg["planificateur_help_format_m"]."</th>
		</tr>
		<tr style='background-color:#EEEEEE'>
			<td>".$msg["planificateur_help_all_values"]."</td>
			<td>*</td>
			<td>*</td>
		</tr>
		<tr>
			<td>".$msg["planificateur_help_value_min"]."</td>
			<td>0</td>
			<td>0</td>
		</tr>
		<tr style='background-color:#EEEEEE'>
			<td>".$msg["planificateur_help_value_max"]."</td>
			<td>23</td>
			<td>59</td>
		</tr>
		<tr>
			<td>".$msg["planificateur_help_separateur"]."</td>
			<td>- (".$msg["planificateur_help_separateur_value"].")</td>
			<td>- (".$msg["planificateur_help_separateur_value"].")</td>
		</tr>
		<tr style='background-color:#EEEEEE'>
			<td>".$msg["planificateur_help_repetitivite"]."</td>
			<td>{".$msg["planificateur_help_repetitivite_value"]."}</td>
			<td>{".$msg["planificateur_help_repetitivite_value"]."}</td>
		</tr>
		<tr>
			<td>".$msg["planificateur_help_examples"]."</td>
			<td>4<br />
				2-14<br />
				11-19{2}
			</td>
			<td>46<br />
				2-44<br />
				07-37{9}
			</td>
		</tr>
	</table>";
	
//	print "<b>".$msg["planificateur_help_format_h"]."</b>
//	<ul>
//		<li>".$msg["planificateur_help_h_all"]."</li>
//		<li>".$msg["planificateur_help_h_fixe"]."</li>
//		<li>".$msg["planificateur_help_h_interval"]."</li>
//		<li>".$msg["planificateur_help_h_interval2"]."</li>
//	</ul>
//	";
//	
//	print "<b>".$msg["planificateur_help_format_m"]."</b>
//	<ul>
//		<li>".$msg["planificateur_help_m_all"]."</li>
//		<li>".$msg["planificateur_help_m_fixe"]."</li>
//		<li>".$msg["planificateur_help_m_interval"]."</li>
//		<li>".$msg["planificateur_help_m_interval2"]."</li>
//	</ul>
//	";
 	
	print "</body></html>";
}

?>