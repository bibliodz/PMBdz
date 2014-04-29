//cette m�thode est en r�alit� une r��criture de la m�thode checkAcceptance de l'objet dijit.tree.dndSource
//d�termine si l'item est d�pla�able
function cms_check_if_draggeable_item_tree(source,node){
	var item = source.tree.selectedItem;
	var type = item.type[0];
	//on peut d�placer une rubrique ou un article! 
	switch(type){
		case 'root_section' :
		case 'section' :
		case 'article' :
			return true;
			break;
		case 'articles' :
		default :
			return false;
			break;	
	}	
}

//cette m�thode est en r�alit� une r��criture de la m�thode checkItemAcceptance de l'objet dijit.tree.dndSource
//d�termine si c'est d�posable en l'endroit
function cms_check_if_item_tree_can_drop_here(target,source,position){
	var target_item = dijit.getEnclosingWidget(target).item;
	var current_item = dijit.getEnclosingWidget(target).tree.selectedItem;

	if(target_item.root){
		//pour le root,seulement les rubriques
		switch(current_item.type[0]){
			case "root_section" :
			case "section" :
				return true;
				break;
			default : 
				return false;
				break;
		}
	}else{
		switch(target_item.type[0]){
			case 'root_section' :
			case 'section' :
				return true;
				break;
			case 'article' :
				if (current_item.type[0] == 'article' && position != 'over') return true;
				else return false;
				break;
			default :
				return false;
				break;						
		}
	}
}

function cms_child_change(parent,childs){
	if (parent.id != 'root') {
		if(parent.type[0] == 'section' || parent.type[0] == 'root_section'){
			var num_parent = parent.id[0];
			var children = new Array();
			var articles = new Array();
			for(var i=0 ; i<childs.length ; i++){
				var child = childs[i];
				if(child.type[0] == 'section' || child.type[0] == 'root_section'){
					children.push(child.id[0]);
				}else if (child.type[0] == "article"){
					articles.push(child.id[0].replace("article_",""));
				}
			}
			cms_dnd_tree_update(num_parent,children);
			if(articles.length){
				cms_update_articles_parent(articles,num_parent);
			}
		} else if (parent.type[0] == 'articles') {
			var num_parent = parent.id[0].replace("articles_","");
			var articles = new Array();
			for(var i=0 ; i<childs.length ; i++){
				var child = childs[i];
				articles.push(child.id[0].replace("article_",""));
			}
			cms_update_articles_parent(articles,num_parent);
		} else {
			return false;
		}
	}
}

function cms_section_leave_root(item){
	this.store.setValue(item,'type','section');
	var container = document.getElementById('editorial_tree_container');
	var target_id = dijit.byId('section_tree').dndController.current.item.id[0];
	var position = dijit.byId('section_tree').dndController.dropPosition;
	
	var children = Array();
	var root_children = this.root.children;
	for (var i=0; i<root_children.length ; i++){
		children.push(root_children[i].id[0]);
	}
	cms_dnd_tree_update(0,children);
	
	if (document.getElementById('target_id') == null) {
		var t_id = document.createElement('input');
		t_id.type = 'hidden';
		t_id.id = 'target_id';
		t_id.name = 'target_id';
		t_id.value = target_id;
		container.parentNode.appendChild(t_id);
	} else {
		document.getElementById('target_id').value = target_id;
	}
	
	if (document.getElementById('position') == null) {
		var pos = document.createElement('input');
		pos.type = 'hidden';
		pos.id = 'position';
		pos.name = 'position';
		pos.value = position;
		container.parentNode.appendChild(pos);
	} else {
		document.getElementById('position').value = position;
	}
}

function cms_section_add_to_root(item){
	if(item.type[0] == 'section' || item.type[0] == 'root_section'){
		var target_id = "";
		if (document.getElementById('target_id') != null) {
			target_id = document.getElementById('target_id').value;
		}
		var position = "";
		if (document.getElementById('position') != null) {
			position = document.getElementById('position').value;
		}
		var root_children = this.root.children;
		var children = new Array();
		var flag = false;
		
		for (var i=0; i<root_children.length ; i++){
			var child_id = root_children[i].id[0];
			if ((target_id == child_id) && (position == 'Before')) {
				children.push(item.id[0]);
				flag = true;
			}
			children.push(child_id);
			if ((target_id == child_id) && (position == 'After')) {
				children.push(item.id[0]);
				flag = true;
			}
		}
		if (flag == false) {
			children.push(item.id[0]);
		}
		this.store.setValue(item,'type','root_section');
		cms_dnd_tree_update(0,children,cms_articles_updated);
	}
}

function cms_dnd_tree_update(num_parent,children,callback){
	var update = new http_request();
	update.request('./ajax.php?module=cms&categ=update_section',true,'&num_parent='+num_parent+'&new_children='+children,true,callback);
}

function cms_load_content_infos(item,node,evt){
	var content = dijit.byId('content_infos');
	var change = false;
	var add_section_button = document.getElementById('add_section_button');
	var add_article_button = document.getElementById('add_article_button');
	if(item.id != "root"){
		switch(item.type[0]){
			case "section" :
			case "root_section" :
				change =true;
				content.set('href','./ajax.php?module=cms&categ=get_infos&type=section&id='+item.id[0]);
				add_section_button.href = "./cms.php?categ=section&sub=edit&id=new&num_parent="+item.id[0];
				add_article_button.href = "./cms.php?categ=article&sub=edit&id=new&num_parent="+item.id[0];
				break;
			case "article" :
				change =true;
				content.set('href','./ajax.php?module=cms&categ=get_infos&type=article&id='+item.id[0].replace("article_",""));
				var parent_id = dijit.byId('section_tree').selectedNode.getParent().item.id[0].replace("articles_","");
				add_section_button.href = "./cms.php?categ=section&sub=edit&id=new&num_parent="+parent_id;
				add_article_button.href = "./cms.php?categ=article&sub=edit&id=new&num_parent="+parent_id;
				break;
			case "articles" :
				change = true;
				content.set('href','./ajax.php?module=cms&categ=get_infos&type=list_articles&id='+item.id[0].replace("articles_",""));
				add_section_button.href = "./cms.php?categ=section&sub=edit&id=new&num_parent="+item.id[0].replace("articles_","");
				add_article_button.href = "./cms.php?categ=article&sub=edit&id=new&num_parent="+item.id[0].replace("articles_","");
				break;
			default :
				change =false;
				//do nothing
				break;
		}
	} else {
		add_section_button.href = "./cms.php?categ=section&sub=edit&id=new";
		add_article_button.href = "./cms.php?categ=article&sub=edit&id=new";
	}
	
	if(change){
		content.refresh();
	}
}

function cms_update_articles_parent(ids_articles,num_section){
	var update = new http_request();
	update.request('./ajax.php?module=cms&categ=update_article',true,'&num_section='+num_section+'&articles='+ids_articles,true,cms_articles_updated);	
}

function cms_articles_updated(response){
	dijit.byId('editorial_tree_container').refresh();
	if (dijit.byId('content_infos').href) dijit.byId('content_infos').refresh();
}

function get_icon_class(item,opened){
	var icon_class = "";
	if(item.id == 'root'){
		icon_class = (!item || this.model.mayHaveChildren(item)) ? (opened ? "dijitFolderOpened" : "dijitFolderClosed") : "dijitLeaf";
	}else {
		switch(item.type[0]){
			case "section" :
			case "root_section" :
				if(this.model.mayHaveChildren(item)){
					if(!item.icon){
						icon_class = opened ? "dijitFolderOpened" : "dijitFolderClosed";
					}else{
						icon_class = "no_icon";
					}
				}else{
					if(!item.icon){
						icon_class = "dijitFolderOpened";
					}else{
						icon_class = "no_icon";
					}
				}
				break;
			case "articles" : 
				icon_class = opened ? "dijitFolderOpened" : "dijitFolderClosed";
				break;
			case "article" :
				if(!item.icon){
					icon_class= "dijitLeaf";
				}else{
					icon_class = "no_icon";
				}
				break;
		}
	}
	return icon_class;
}

function get_label_class(item,opened){
	var label_class = "";
	if(item.icon){
		label_class = "no_icon";
	}
	return label_class;
}

function get_label(item){
	var label = this.model.getLabel(item);
	if(item.icon){
		label = "<img src='"+item.icon[0]+"' alt='"+label+"' title='"+label+"'/>&nbsp;"+label;
	}
	return label;
}
