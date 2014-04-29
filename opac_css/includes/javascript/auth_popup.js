// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: auth_popup.js,v 1.1 2012-06-21 15:11:26 arenou Exp $

function auth_popup(url){
	if(url==''){
		url = "./ajax.php?module=ajax&categ=auth&action=get_form";
	}
	var div = document.createElement('div');
	div.setAttribute('id','auth_popup');
	div.setAttribute("style","z-index:inherit;position:absolute;background:white;top:30%;left:40%;");
	var iframe = document.createElement("iframe");
	iframe.setAttribute('src',url);
	iframe.setAttribute("style","height:150px;width:250px;");
	var close = document.createElement('div');
	var img = document.createElement('img');
	img.setAttribute('src','./images/cross.png');
	img.setAttribute('style','width:20px;position:absolute;right:0px;cursor:pointer;');
	img.onclick = function (){
		var frame = window.parent.document.getElementById('auth_popup');
		if(!frame){
			frame = document.getElementById('auth_popup');
		}
		frame.parentNode.removeChild(frame);
	}
	div.appendChild(img);
	div.appendChild(iframe);
	var att = document.getElementById('att');
	if(att){
		att.appendChild(div);
	}else{
		document.body.appendChild(div);
	}
	
}