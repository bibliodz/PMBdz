<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: bbcode.inc.php,v 1.1 2011-08-09 08:34:51 ngantier Exp $

require_once ($include_path . "/misc.inc.php");
	
function handle_url_tag($url, $link = '', $bbcode = false){
	
	$full_url = str_replace(array(' ', '\'', '`', '"'), array('%20', '', '', ''), $url);
	if (strpos($url, 'www.') === 0)			// If it starts with www, we add http://
		$full_url = 'http://'.$full_url;
	else if (strpos($url, 'ftp.') === 0)	// Else if it starts with ftp, we add ftp://
		$full_url = 'ftp://'.$full_url;
	else if (!preg_match('#^([a-z0-9]{3,6})://#', $url)) 	// Else if it doesn't start with abcdef://, we add http://
		$full_url = 'http://'.$full_url;

	
	if (!$bbcode)	$link = ($link == '' || $link == $url) ? ((strlen($url) > 55) ? substr($url, 0 , 39).'...'.substr($url, -10) : $url) : stripslashes($link);

	if ($bbcode){
		if ($full_url == $link)
			return '[url]'.$link.'[/url]';
		else
			return '[url='.$full_url.']'.$link.'[/url]';
	}
	else
		return '<a href="'.$full_url.'">'.$link.'</a>';
}
	
function handle_img_tag($url, $is_signature = false, $alt = null) {
	
	if ($alt == null)	$alt = $url;
	$img_tag = '<span ><img src="'.$url.'" /></span>';
	return $img_tag;
}	
	
function do_bbcode($text){
	
	$text=nl2br($text);
	
	if (strpos($text, '[quote') !== false){
		$text = preg_replace('#\[quote=(&quot;|"|\'|)(.*?)\\1\]#e', '"</p><div class=\"quotebox\"><cite>".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].":</cite><blockquote><p>"', $text);
		$text = preg_replace('#\[quote\]\s*#', '</p><div class="quotebox"><blockquote><p>', $text);
		$text = preg_replace('#\s*\[\/quote\]#S', '</p></blockquote></div><p>', $text);
	}
	
	$pattern[] = '#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e';
	$pattern[] = '#\[img=([^\[]*?)\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e';
	
	$replace[] = 'handle_img_tag(\'$1$3\', false)';
	$replace[] = 'handle_img_tag(\'$2$4\', false, \'$1\')';

	$pattern[] = '#\[b\](.*?)\[/b\]#ms';
	$pattern[] = '#\[i\](.*?)\[/i\]#ms';
	$pattern[] = '#\[u\](.*?)\[/u\]#ms';
	$pattern[] = '#\[colou?r=([a-zA-Z]{3,20}|\#[0-9a-fA-F]{6}|\#[0-9a-fA-F]{3})](.*?)\[/colou?r\]#ms';
	$pattern[] = '#\[h\](.*?)\[/h\]#ms';

	$replace[] = '<strong>$1</strong>';
	$replace[] = '<em>$1</em>';
	$replace[] = '<span class="bbu">$1</span>';
	$replace[] = '<span style="color: $1">$2</span>';
	$replace[] = '</p><h5>$1</h5><p>';


	$pattern[] = '#\[url\]([^\[]*?)\[/url\]#e';
	$pattern[] = '#\[url=([^\[]+?)\](.*?)\[/url\]#e';
	$pattern[] = '#\[email\]([^\[]*?)\[/email\]#';
	$pattern[] = '#\[email=([^\[]+?)\](.*?)\[/email\]#';

	$replace[] = 'handle_url_tag(\'$1\')';
	$replace[] = 'handle_url_tag(\'$1\', \'$2\')';
	$replace[] = '<a href="mailto:$1">$1</a>';
	$replace[] = '<a href="mailto:$1">$2</a>';

	$text = preg_replace($pattern, $replace, $text);
	
	return $text;
}
?>