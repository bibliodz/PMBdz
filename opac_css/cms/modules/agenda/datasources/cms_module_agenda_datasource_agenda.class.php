<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_agenda_datasource_agenda.class.php,v 1.7 2013-08-22 09:58:54 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_agenda_datasource_agenda extends cms_module_common_datasource{

	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_env_var",
			"cms_module_agenda_selector_calendars_date",
			"cms_module_agenda_selector_calendars"
		);
	}

	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		$datas = array();
		$selector = $this->get_selected_selector();

		switch($this->parameters['selector']){
			//devrait �tre le seul survivant...
			case "cms_module_agenda_selector_calendars" :
				if($selector){
					$calendars = array();
					$query = "select managed_module_box from cms_managed_modules join cms_cadres on id_cadre = ".$this->cadre_parent." and cadre_object = managed_module_name";
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$box = mysql_result($result,0,0);
						$infos =unserialize($box);
						$calendars = $selector->get_value();
						foreach($calendars as $calendar){
							$elem = $infos['module']['calendars'][$calendar];
							$query="select id_article from cms_articles where article_num_type = ".$elem['type'];
							$result = mysql_query($query);
							if(mysql_num_rows($result)){
								$articles = array();
								while($row = mysql_fetch_object($result)){
									$articles[]=$row->id_article;
								}
								$articles = $this->filter_datas("articles",$articles);
								foreach($articles as $article){
									$art = new cms_article($article);
									$event = $art->format_datas();
									foreach($event['fields_type'] as $field){
										if($field['id'] == $elem['start_date']){
											$event['event_start'] = $field['values'][0];
											$event['event_start']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
										}
										if($field['id'] == $elem['end_date']){
											$event['event_end'] = $field['values'][0];
											$event['event_end']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
										}
									}
									$event['id_type'] = $elem['type'];
									$event['color'] = $elem['color'];
									$event['calendar'] = $elem['name'];
									$events[] = $event;
								}
							}
						}
					}
					usort($events,array($this,"sort_event"));
					return array('events'=>$events);
				}
				break;
			case "cms_module_common_selector_env_var" :
				if($selector){
					$art = new cms_article($selector->get_value());
					$event = $art->format_datas();
					//allons chercher les infos du calendrier associ� � cet �v�nement
					$query = "select managed_module_box from cms_managed_modules join cms_cadres on id_cadre = ".$this->cadre_parent." and cadre_object = managed_module_name";
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$box = mysql_result($result,0,0);
						$infos =unserialize($box);
						foreach($infos['module']['calendars'] as $calendar){
							if($calendar['type'] == $art->num_type){
								foreach($event['fields_type'] as $field){
									if($field['id'] == $calendar['start_date']){
										$event['event_start'] = $field['values'][0];
										$event['event_start']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
									}
									if($field['id'] == $calendar['end_date']){
										$event['event_end'] = $field['values'][0];
										$event['event_end']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
									}
								}
								$event['id_type'] = $calendar['type'];
								$event['color'] = $calendar['color'];	
								$event['calendar'] = $calendar['name'];
								break;
							}
						}
					}
					return $event;
				}
				break;
			case "cms_module_agenda_selector_calendars_date" :
				if($selector){
					$query = "select managed_module_box from cms_managed_modules join cms_cadres on id_cadre = ".$this->cadre_parent." and cadre_object = managed_module_name";
					$result = mysql_query($query);
					if(mysql_num_rows($result)){
						$box = mysql_result($result,0,0);
						$infos =unserialize($box);
						$datas = $selector->get_value();
						$time = mktime(0,0,0,substr($datas['date'],5,2),substr($datas['date'],8,2),substr($datas['date'],0,4));
						foreach($datas['calendars'] as $calendar){
							$elem = $infos['module']['calendars'][$calendar];
							$query="select id_article from cms_articles where article_num_type = ".$elem['type'];
							$result = mysql_query($query);
							if(mysql_num_rows($result)){
								$articles = array();
								while($row = mysql_fetch_object($result)){
									$articles[]=$row->id_article;
								}
								$articles = $this->filter_datas("articles",$articles);
								if(is_array($articles)){
									foreach($articles as $article){
										$art = new cms_article($article);
										$event = $art->format_datas();
										foreach($event['fields_type'] as $field){
											if($field['id'] == $elem['start_date']){
												$event['event_start'] = $field['values'][0];
												$event['event_start']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
											}
											if($field['id'] == $elem['end_date']){
												$event['event_end'] = $field['values'][0];
												$event['event_end']['time'] = mktime(0,0,0,substr($field['values'][0]['value'],5,2),substr($field['values'][0]['value'],8,2),substr($field['values'][0]['value'],0,4));
											}
										}
										$event['id_type'] = $elem['type'];
										$event['color'] = $elem['color'];
										$event['calendar'] = $elem['name'];
										if($event['event_start']['time']>=$time || ($event['event_start'] && $event['event_end'] && $event['event_end']['time']>=$time)){
											$events[] = $event;
										}
									}
								}
							}
						}
					}
					usort($events,array($this,"sort_event"));
					return array('events'=>$events);
				}
				break;
		}
	}
	
	
	public static function sort_event($a,$b){
		if($a['event_start']['time'] > $b['event_start']['time']){
			return 1;
		}else if($a['event_start']['time'] == $b['event_start']['time']){
			if($a['event_end']['time'] > $b['event_end']['time']){
				return 1;
			}else{
				return -1;
			}
		}else{
			return -1;
		}
	}
	
	public function get_format_data_structure($type='event'){
		$format_datas = array();
		switch($type){
			//event
			case "event" :
				$format_datas = cms_article::get_format_data_structure("article");
				$format_datas[] = array(
					'var' => "event_start",
					'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_start_desc'],
					'children' => array(
						array(
							'var' => "event_start.format_value",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_start_format_value_desc'],
						),
						array(
							'var' => "event_start.value",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_start_value_desc'],
						),
						array(
							'var' => "event_start.time",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_start_time_desc'],
						)
					)
				);
				$format_datas[] = array(
					'var' => "event_end",
					'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_end_desc'],
					'children' => array(
						array(
							'var' => "event_end.format_value",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_end_format_value_desc'],
						),
						array(
							'var' => "event_end.value",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_end_value_desc'],
						),
						array(
							'var' => "event_end.time",
							'desc' => $this->msg['cms_module_agenda_datasource_agenda_event_end_time_desc'],
						)
					)
				);
				$format_datas[] = array(
					'var' => "id_type",
					'desc' => $this->msg['cms_module_agenda_datasource_agenda_id_type_desc']
				);
				$format_datas[] = array(
					'var' => "color",
					'desc' => $this->msg['cms_module_agenda_datasource_agenda_color_desc']
				);
				$format_datas[] = array(
					'var' => "calendar",
					'desc' => $this->msg['cms_module_agenda_datasource_agenda_calendar_desc']
				);
				break;
			case "eventslist" :
				$format_event = $this->get_format_data_structure("event");
				$format_datas[] = array(
					'var' => "events",
					'desc'=> $this->msg['cms_module_agenda_datasource_agenda_events_desc'],
					'children' => $this->prefix_var_tree($format_event,"events[i]")
				); 
				break;	
		}							
		return $format_datas;
	}
}











