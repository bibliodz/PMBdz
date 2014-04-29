<?php


if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($base_path.'/includes/resa_func.inc.php');
require_once($include_path."/mail.inc.php");
require_once($base_path.'/classes/notice.class.php');
require_once($base_path.'/classes/resa.class.php');

if($_SESSION['user_code']){
	global $resa_cart_display;
	$resa_cart_display='';
	
	//Récupération des notices
	switch($sub){
		case 'resa_cart' :
			$notices = $_SESSION['cart'];			
			break;
		case 'resa_cart_checked':		
			$notices = $notice;
			break;
		default:
			print "<script>document.location='".$base_path."/index.php';</script>";
			break;
	}
	
	$id_empr=$_SESSION['id_empr_session'];
	
	$resa_cart_display="<table><tr><th colspan=2>".$msg["empr_menu_resa"]." : </th></tr>";
	foreach($notices as $notice_id){
		$resa_cart_display.="<tr>";
		$bulletin_id=0;
		//On vérifi si notre notice n'est pas une notice de bulletin.
		$query='SELECT bulletin_id FROM bulletins WHERE num_notice='.$notice_id;
		$result = mysql_query($query, $dbh);
		if(mysql_num_rows($result)){
			while($line=mysql_fetch_array($result,MYSQL_ASSOC)){
				$bulletin_id=$line['bulletin_id'];
			}
		}
		
		$resa=new reservation($id_empr, $notice_id, $bulletin_id);
		if($resa->add($_SESSION['empr_location'])){
			$resa_cart_display.="<td>".$resa->notice."</td><td>".$resa->message."</td>";
		}else{
			$resa_cart_display.="<td>".$resa->notice."</td><td>".$resa->message."</td>";
		}
		$resa_cart_display.="</tr>";
	}
	$resa_cart_display.="</table>";
	
	if(!$opac_resa_popup){
		require_once $base_path.'/includes/show_cart.inc.php';
	}	
} else {
	print "<script>document.location='".$base_path."/index.php';</script>";
}