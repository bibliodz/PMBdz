//+-------------------------------------------------+
//� 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
//+-------------------------------------------------+
//$Id: tablist.js,v 1.21 2013-12-02 09:07:25 dbellamy Exp $

//gestion des listes "collapsibles" en Javascript

/*var imgOpened = new Image();
imgOpened.src = './getgif.php?nomgif=moins';
var imgClosed = new Image();
imgClosed.src = './getgif.php?nomgif=plus';*/
var expandedDb = '';

/*
 * abacarisse
 * On declare en global la variable des param addThis
 */
var waitingAddthis = new Array();
if(!param_social_network){
	var param_social_network;
	/*
	 * On recup les param
	 */
	if(opac_show_social_network==1){
		var req = new http_request();
		req.request("./ajax.php?module=ajax&categ=param_social_network&sub=get",false,null,true,function(data){
			param_social_network= JSON.parse(data);
			for(var i=0 ; i<waitingAddthis.length ; i++){
				creeAddthis(waitingAddthis[i]);
			}
			waitingAddthis = new Array();
		});
	}
}


function waitingAddthisLoaded(elem){
	waitingAddthis.push(elem);
}
//on regarde si le client est DOM-compliant

var isDOM = (typeof(document.getElementsByTagName) != 'undefined') ? 1 : 0;

//Konqueror (support DOM partiel) : on rejette
if(isDOM && typeof(navigator.userAgent) != 'undefined') {
	var browserName = ' ' + navigator.userAgent.toLowerCase();
	if(browserName.indexOf('konqueror') > 0) {
		isDOM = 0;
	}
}

function changeCoverImage(elt) {
	imgs=elt.getElementsByTagName('img');
	for (i=0; i < imgs.length; i++) {
		img=imgs[i];		
		isbn=img.getAttribute('isbn');
		vigurl=img.getAttribute('vigurl');
		url_image=img.getAttribute('url_image');
		if (vigurl) {
			if (img.src.substring(img.src.length-8,img.src.length)=='vide.png') {
				img.src=vigurl;
			}
		} else if (isbn) {
			if (img.src.substring(img.src.length-8,img.src.length)=='vide.png') {
				img.src=url_image.replace(/!!noticecode!!/,isbn);
			}
		}
	}
}

function initIt(){
	if (!isDOM) {
//		alert("ce navigateur n'est pas compatible avec le DOM.");
		return;
	}
	var tempColl    = document.getElementsByTagName('DIV');
	var tempCollCnt = tempColl.length;
	for (var i = 0; i < tempCollCnt; i++) {
		if (tempColl[i].className == 'notice-child') {
			if (tempColl[i].getAttribute('startOpen') == 'Yes' ) {
				expandBase (tempColl[i].id.substring(0,tempColl[i].id.indexOf('Child')), true);
			} else tempColl[i].style.display = 'none';
		}
	}
} // end of the 'initIt()' function

function creeAddthis(el){
	try{
	if(opac_show_social_network==1){
		var addthisEl = document.getElementById(el + 'addthis');
		for(key in param_social_network.toolBoxParams){
			if(addthisEl.getAttribute(key) != param_social_network.toolBoxParams[key]){
				addthisEl.setAttribute(key,param_social_network.toolBoxParams[key]);
			}
		}
		
		for(buttonKey in param_social_network.buttons){
			var button;
			button = addthisEl.appendChild(document.createElement("a"));
			if(param_social_network.buttons[buttonKey].text){
				button.innerHTML=param_social_network.buttons[buttonKey].text;
			}
			for(attrKey in param_social_network.buttons[buttonKey].attributes){
				button.setAttribute(attrKey,param_social_network.buttons[buttonKey].attributes[attrKey]);
			}
			for(elemKey in param_social_network.buttons[buttonKey].elements){
				var element;
				element = button.appendChild(document.createElement(param_social_network.buttons[buttonKey].elements[elemKey].tag));
				if(param_social_network.buttons[buttonKey].elements[elemKey].text){
					element.innerHtml=param_social_network.buttons[buttonKey].elements[elemKey].text;
				}
				for(elemAttrKey in param_social_network.buttons[buttonKey].elements[elemKey].attributes){
					element.setAttribute(elemAttrKey,param_social_network.buttons[buttonKey].elements[elemKey].attributes[elemAttrKey]);
				}
			}
		}	
		
		addthis.toolbox(addthisEl);
		//on marque l'�l�ment
		addthisEl.setAttribute("added","1");
	}
	}catch(e){
		if(typeof console != 'undefined') {
			console.log(e)
		}
	}
}

/*
 * Fonction de cr�ation des boutons addThis
 */
//function create_button(el){
//	var addthisEl = document.getElementById(el + 'addthis');
//	
//	for(key in param_social_network.toolBoxParams){
//		if(addthisEl.getAttribute(key) != param_social_network.toolBoxParams[key]){
//			addthisEl.setAttribute(key,param_social_network.toolBoxParams[key]);
//		}
//	}
//	
//	for(buttonKey in param_social_network.buttons){
//		var button;
//		button = addthisEl.appendChild(document.createElement("a"));
//		if(param_social_network.buttons[buttonKey].text){
//			button.innerHTML=param_social_network.buttons[buttonKey].text;
//		}
//		for(attrKey in param_social_network.buttons[buttonKey].attributes){
//			button.setAttribute(attrKey,param_social_network.buttons[buttonKey].attributes[attrKey]);
//		}
//		for(elemKey in param_social_network.buttons[buttonKey].elements){
//			var element;
//			element = button.appendChild(document.createElement(param_social_network.buttons[buttonKey].elements[elemKey].tag));
//			if(param_social_network.buttons[buttonKey].elements[elemKey].text){
//				element.innerHtml=param_social_network.buttons[buttonKey].elements[elemKey].text;
//			}
//			for(elemAttrKey in param_social_network.buttons[buttonKey].elements[elemKey].attributes){
//				element.setAttribute(elemAttrKey,param_social_network.buttons[buttonKey].elements[elemKey].attributes[elemAttrKey]);
//			}
//		}
//	}	
//	
//	addthis.toolbox(addthisEl);
//	//on marque l'�l�ment
//	addthisEl.setAttribute("added","1");
//}

//function creeAddthis(el){
//	var addthisEl = document.getElementById(el + 'addthis');
//	
//	var aEl = addthisEl.appendChild(document.createElement("a"));
//	var aEl2 = addthisEl.appendChild(document.createElement("a"));
//	var aEl3 = addthisEl.appendChild(document.createElement("a"));
//
//	aEl.setAttribute("class","addthis_button_facebook_like");
//	aEl.setAttribute("fb:like:layout","button_count");	
//	aEl2.setAttribute("class","addthis_button_tweet");	
//	aEl3.setAttribute("class","addthis_counter addthis_button_compact");
//	
//	addthis.toolbox(addthisEl);
//
//	//on marque l'�l�ment
//	addthisEl.setAttribute("added","1");
//} // end of the 'creeAddthis()' function

function ReinitializeAddThis(){
	if(window.addthis){
		window.addthis.ost = 0;
		window.addthis.ready();
	}
}

function expandBase(el, unexpand){
	if (!isDOM)
		return;
	var whichEl = document.getElementById(el + 'Child');
	var whichIm = document.getElementById(el + 'Img');

	if (whichEl.style.display == 'none' && whichIm) {
		if(whichEl.getAttribute("enrichment")){
			getEnrichment(el.replace("el",""));
		} 
		var whichAddthis = document.getElementById(el + 'addthis');
		if (whichAddthis && !whichAddthis.getAttribute("added")){
			creeAddthis(el);
		}
		whichEl.style.display  = 'block';
		whichIm.src = whichIm.src.replace('nomgif=plus','nomgif=moins');
		changeCoverImage(whichEl);
	}
	else if (unexpand) {
		whichEl.style.display  = 'none';
		whichIm.src = whichIm.src.replace("nomgif=moins","nomgif=plus");
	}
	ReinitializeAddThis();
} // end of the 'expandBase()' function

function expandAll() {
	var tempColl    = document.getElementsByTagName('DIV');
	var tempCollCnt = tempColl.length;

	for (var i = 0; i < tempCollCnt; i++) {
		if(tempColl[i].className == 'notice-child'){
			tempColl[i].style.display = 'block';
			var el = tempColl[i].getAttribute("id").replace("Child","");
			if (tempColl[i].getAttribute("enrichment")){
				getEnrichment(el.replace("el",""));
			}
			var whichAddthis = document.getElementById(el + 'addthis');
			if (whichAddthis && !whichAddthis.getAttribute("added")){
				creeAddthis(el);
			}
		}
		changeCoverImage(tempColl[i]);
	}
	tempColl    = document.getElementsByTagName('IMG');
	tempCollCnt = tempColl.length;
	for (var i = 0; i < tempCollCnt; i++) {
		if(tempColl[i].name == 'imEx') {
			tempColl[i].src=tempColl[i].src.replace("nomgif=plus","nomgif=moins");
		}
	}
	ReinitializeAddThis();
}

function collapseAll() {
	var tempColl    = document.getElementsByTagName('DIV');
	var tempCollCnt = tempColl.length;
	for (var i = 0; i < tempCollCnt; i++) {
		if(tempColl[i].className == 'notice-child')
			tempColl[i].style.display = 'none';
	}
	tempColl    = document.getElementsByTagName('IMG');
	tempCollCnt = tempColl.length;
	for (var i = 0; i < tempCollCnt; i++) {
		if(tempColl[i].name == 'imEx') {
			tempColl[i].src=tempColl[i].src.replace("nomgif=moins","nomgif=plus");
		}
	}
}

onload = initIt;