<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: autoloader.class.php,v 1.1 2012-03-16 08:59:01 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

/*
 * Classe d'inclusion automatique
 */
class autoloader{
	protected $debug = false;

	public function __construct($set_classic=true,$debug = false){
		$this->debug = $debug;
		if($set_classic){
			//on ajoute dans le registe d'appel automatique, la m�thode de chargement classique...
			$this->add_register();
		}
	}
	
	/*
	 * On empile dans le registe automatique un nouvelle m�thode d'appel (qui peut �tre mis en t�te de la pile)
	 * � partir de php 5.3
	 */
	public function add_register($type="classic",$first = false){
		if(substr(phpversion(),2,1) >= 3){
			spl_autoload_register(array($this, $type),true,$first);
		}else{
			spl_autoload_register(array($this, $type),true);
		}
	}
	
	/*
	 * On empile dans le registe automatique un nouvelle m�thode d'appel (qui peut �tre mis en t�te de la pile)
	 */
	public function del_register($type="classic"){
		spl_autoload_unregister(array($this, $type));
	}
	
	/*
	 * 
	 */
	public function set_clean_pile(){
		$functions = spl_autoload_functions();
		foreach($functions as $func){
			spl_autoload_unregister(array($this,$func[1]));
		}
	}
	
	public function list_pile(){
		return spl_autoload_functions();	
	}
	
	private function load($file){
		global $base_path;
		global $class_path;
		global $include_path;
		global $javascript_path;
		global $styles_path;		
		if(file_exists($file)){
			require_once($file);
			if($this->debug){
				print "Load success<br>";
			}
		}else{
			if($this->debug){
				print "File ".$file." can't find<br>";
			}
			return false;
		}
		return true;
	}

	/*
	 * Inclusion classique...
	 * Les classes sont dans le r�pertoire classes/ ou un sous-r�pertoire...,
	 * Eventuellement un template dans includes/templates/ ou un sous-r�pertoire...
	 */
	private function classic($class_name) {
		global $class_path;
		global $include_path;
		if($this->debug){
			print '<br>Trying to load '. $class_name. ' via '. __METHOD__. "()<br>";
		}
		//fichier de la classe
		$class_file = $class_path."/".$class_name.".class.php";
		$success = $this->load($class_file);
		if($success){
			//fichier de template associ� � la classe
			$tpl_file = $include_path."/templates/".$class_name.".tpl.php";
			$this->load($tpl_file);	
		}else{
			$class_file = "";
			$subfolder = substr($class_name,0,strpos($class_name,"_"));
			if($subfolder != ""){
				//fichier de la classe
				$class_file = $class_path."/".$subfolder."/".$class_name.".class.php";
				$success = $this->load($class_file);
				if($success){
					//fichier de template associ� � la classe
					$tpl_file = $include_path."/templates/".$subfolder."/".$class_name.".tpl.php";
					$this->load($tpl_file);
				}
			}
		}
	}	

	/*
	 * Inclusion pour les classes modules du portail
	 */
	private function cms_modules($class_name) {
		global $base_path;
		if($this->debug){
			echo '<br>Trying to load ', $class_name, ' via ', __METHOD__, "()<br>";
		}
		//inclusion de la classe d'un module...
		if(!class_exists($class_name)){
			$module = str_replace("cms_module_","",$class_name);
			$class_file = $base_path."/cms/modules/".$module."/".$class_name.".class.php";
			$success = $this->load($class_file);
			if(!$success){
				$module = $class_file = "";
				$var = str_replace("cms_module_","",$class_name);
				$module = substr($var,0,strpos($var,"_"));
				$element = substr($var,strpos($var,"_")+1);
				if(strpos($element,"_") !== false){
					$element = substr($element,0,strpos($element,"_"));
					$class_file = $base_path."/cms/modules/".$module."/".$element."s/".$class_name.".class.php";
				}else{
					$class_file = $base_path."/cms/modules/".$module."/".$element."s/".$class_name.".class.php";
				}
				$this->load($class_file);	
			}
		}else if($this->debug){
			echo "Already loaded<br>";
		}
	}	
}
