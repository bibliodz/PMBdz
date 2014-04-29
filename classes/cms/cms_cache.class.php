<?php

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

final class cms_cache{
	
	/**
	 * @var array() 
	 */
	private static $cms_cache_arrayObject;
	
	/**
	 * @param object $cms_object an storable cms object
	 * @return number the object's index
	 */
	private static function get_index($cms_object){
		$index=0;
		
		switch(get_class($cms_object)){
			case 'cms_articles':
				$index=$cms_object->num_section;
				break;
			case 'cms_editorial_parametres_perso':
				$index=$cms_object->num_type;
				break;
			case 'cms_editorial_publications_states':
				$index=0;
				break;
			case 'cms_logo':
				$index=$cms_object->type.'_'.$cms_object->id;
				break;
			default:
				$index=$cms_object->id;
				break;
		}
		return $index;
	}
	
	/**
	 * @param object $cms_object an storable cms object
	 * @return bool true if exists in the array, false otherwise
	 */
	public static function get_at_cms_cache($cms_object){
		if(is_null(self::$cms_cache_arrayObject[get_class($cms_object)][self::get_index($cms_object)])){
			return false;
		}else{
			return self::$cms_cache_arrayObject[get_class($cms_object)][self::get_index($cms_object)];
		}
	}
	
	/**
	 * @param object $cms_object an storable cms object
	 */
	public static function set_at_cms_cache($cms_object){
		self::$cms_cache_arrayObject[get_class($cms_object)][self::get_index($cms_object)]=$cms_object;
	}
	
	/*
	 * Private contructor
	 */
	private function __construct() {}
	
	/*
	 * Prevent cloning of instance
	 */
	private function __clone() {
		throw new Exception('Clone is not allowed !');
	}
	
	/*
	 * Set the instance to null
	 */
	private function __destruct() {
		self::$cms_cache_arrayObject=null;
	}
}
