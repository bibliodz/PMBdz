<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_menu_view_menu_django.class.php,v 1.4 2013-06-13 14:58:34 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_menu_view_menu_django extends cms_module_common_view_django{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "
<!-- Attention, avec un template Django, vous devez g�rer la pronfondeur de votre menu dans le template, il n'existe pas de r�cursivit� avec Django -->
<ul class='cms_menu cms_menu_deep0'>
	{% for item in items %}
		<li {% if item.current %} class='cms_menu_current'{% endif %}>
			{% if item.link %}
				<a href='{{item.link}}'>{{item.title}}</a>
			{% else %}
				{{item.title}}
			{% endif %}
			<!-- Voici un exemple pour la profondeur dans les menus, � r�p�ter autant de fois que de niveau.. -->
			{% if item.children %}
				<ul class='cms_menu cms_menu_deep1'>
					{% for children1 in item.children %}
						<li {% if children1.current %} class='cms_menu_current'{% endif %}>
							{% if children1.link %}
								<a href='{{children1.link}}'>{{children1.title}}</a>
							{% else %}
								{{children1.title}}
							{% endif %}
							{% if children1.children %}
								<ul class='cms_menu cms_menu_deep2'>
									{% for children2 in children1.children %}
										<li {% if children2.current %} class='cms_menu_current'{% endif %}>
											{% if children2.link %}
												<a href='{{children2.link}}'>{{children2.title}}</a>
											{% else %}
												{{children2.title}}
											{% endif %}
										</li>
									{% endfor %}
								</ul>
							{% endif %}
						</li>
					{% endfor %}
				</ul>
			{% endif %}
		</li>
	{% endfor %}
</ul>";
	}
	
	public function render($datas){	
		//on rajoute nos �l�ments...
		//le titre
		global $opac_url_base;
		$opac_url = substr($opac_url_base,strpos($opac_url_base,"://")+3);
		if(!is_array($datas['items'])){
			$datas['items']=array();
		}
		foreach($datas['items'] as $key => $item){
			if(str_replace($opac_url,"",$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']) == substr($item['link'],2)){
				$datas['items'][$key]['current'] = true;
			}
		}
		//on rappelle le tout...
		return parent::render($datas);
	}
	
	public function get_format_data_structure(){
		$menu = new cms_module_menu_datasource_menu();
		$format = $menu->get_format_data_structure();
		$format[0]['children'][] = array(
			'var' => "items[i].current",
			'desc' => $this->msg['cms_module_menu_view_menu_django']
		);
		return $format;
	}
}