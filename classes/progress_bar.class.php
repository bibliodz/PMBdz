<?php
// +-------------------------------------------------+
// � 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: progress_bar.class.php,v 1.4 2012-10-22 08:53:47 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class progress_bar{
	protected static $nb_instance = 1;

	var $html_id; 	//Identifiant de l'instance
	var $count;		//Valeur maximum de l'indicateur
	var $pas;		//On affiche la progression tous les pas
	var $nb_progress_call;	//Nombre d'appels 
	var $finish;			//On a d�pass� 100% (c.a.d. $nb_progress_call>$count)
	
	//L'�chelle de valeur est de 0 � $count
	//Le pourcentage est de $nb_progress_call/$count
	//L'affichage est rafraichi tous les $pas appels
	
	//Constructeur.	 $text
	function progress_bar($text='',$count=0,$pas=1) {
		
		$this->html_id = self::$nb_instance;
		self::$nb_instance++;
		$this->show();
		if($text)$this->set_text($text);
		$this->count=$count;
		$this->pas=$pas;
		$this->nb_progress_call=0;
		$this->finish=0;
	}
		
	function show(){
        print "
	        <div class='row' id='progress_bar_".$this->html_id."' style='text-align:center; width:80%; border: 1px solid #000000; padding: 4px;'>
	            <div style='text-align:left; width:100%; height:16px;'>
	                <img id='progress_".$this->html_id."' src='images/jauge.png' style='width:1%; height:16px'/>
	            </div>
	            <div style='text-align:center'>
	                <span id='progress_text_".$this->html_id."'></span>&nbsp;
	                <span id='progress_percent_".$this->html_id."'></span>
	            </div>
	        </div>";
	    ob_flush();
        flush();
    }
   
    function init() {
        print "<script type='text/javascript'>document.getElementById('progress_".$this->html_id."').src='images/jauge.png'</script>";
        ob_flush();
        flush();
    }
   
    function set_percent($percent) {
    	// on envoit des espaces en plus pour que flush() vide bien le buffer (>256)
        print "
	        <script type='text/javascript'>
	       		document.getElementById('progress_".$this->html_id."').style.width='".$percent."%';
	       		document.getElementById('progress_percent_".$this->html_id."').innerHTML='".$percent."%';
	        </script> 
	        
	        
	                                                                                                                                                                                                                                     
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	   ";
	   ob_flush();
       flush();
    }
        
    function progress() {
    	if($this->finish) return;    	
    	$this->nb_progress_call++;
    	
    	$percent=intval(100*($this->nb_progress_call/$this->count));
    	
    	if($percent>=100){
    		$this->set_percent(100);
    		$this->finish=1;
    	}    	
    	if(!($this->nb_progress_call%$this->pas)){    		 		
	        $this->set_percent($percent);
    	
    	}
    } 
     
    function set_text($text){
        global $charset;
        print "<script type='text/javascript'>document.getElementById('progress_text_".$this->html_id."').innerHTML='".htmlentities($text,ENT_QUOTES,$charset)."';</script>";
        ob_flush();
        flush();
    }
        
    function hide(){
        print "<script type='text/javascript'>var obj=document.getElementById('progress_bar_".$this->html_id."'); obj.parentNode.removeChild(obj)</script>";
        ob_flush();
        flush();
    }	
					
}
?>