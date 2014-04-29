<?php

require_once("classes/fpdf.class.php");

class convert extends fpdf{
	var $logoUrl;	//url du logo d�pos� sur chaque page...
	var $header;	//header de page...
	var $footers;	//pied de page du document...
	var $resolution;
	
	var $outlines=array();
	var $OutlineRoot;
	
	
	function convert($params=array()){
		parent::FPDF();
		$this->footers = $params['footers'];
		$this->setCreator(utf8_decode($params['creator']));
		$this->SetTextColor(0);	
	}

	function getSize($dimension,$resolution){
		$this->resolution = $resolution;
		$dimension = explode(",",$dimension);
		$resolution = explode(",",$resolution);
		$this->resolution = $resolution[0];
		$size = array(
			$this->convertPxToMm($dimension[0],$resolution[0]),
			$this->convertPxToMm($dimension[1],$resolution[1])
		);
		return $size;
	}
	
	function convertPxToMm($px,$dpi=0){
		return ($px*25.4)/($dpi ? $dpi : $this->resolution);
	}
		
	function Footer(){
		if ($this->logoUrl !="") $this->Image($this->logoUrl,10,8,20);
		if ($this->header) {
			$this->SetFont('Arial',"",14);
			$this->Cell(80); //D�calage � droite
			$this->Cell(30,10,$this->header,0,'C');	
		}
		
		//si on a un footer sp�cificique pour la page courante...
		$footer = array();
		if($this->footers['page'.$this->PageNo()]){
			$footer = $this->footers['page'.$this->PageNo()];
		}else if ($this->footers['all']){
			$footer = $this->footers['all'];
		}
		
		//on applique le footer
		if($footer['name']){
			$this->SetY((-15*$this->h/297));
			$this->SetX((5*$this->w/210));
			//Police Arial italique 8
			$this->SetFont('Arial','I',(8*$this->w/210));
			if($footer['link']){
				$this->Cell(0,10,utf8_decode($footer['name']),0,0,'',false,utf8_decode($footer['link']));
			}else{
				$this->Cell(0,10,utf8_decode($footer['name']),0,0,'',false,'');
			}
		}
	}
	
	function Error($msg){
		//erreur sur la classe FDPF, on la log avant d'arreter la g�n�ration...
		logMsg($msg);	
		//Fatal error
		parent::Error($msg);
	}
	
	/*************************************************************************
	 *  Fonctions pour les signets (provient du site FPDF / Auteur : Olivier  *
	 *  http://www.fpdf.org/fr/script/script1.php                            *
	 *  Modifi� par Arnaud RENOU (prise en compte d'un num�ro de page        *
	 *************************************************************************/
	
	function Bookmark($txt, $page=-1, $level=0, $y=0)	{
		if($y==-1)
			$y=$this->GetY();
		if($page == -1){
			$page = $this->PageNo();
		}
		$this->outlines[]=array('t'=>$txt, 'l'=>$level, 'y'=>($this->h-$y)*$this->k, 'p'=>$page);
	}
	function BookmarkUTF8($txt,$page=-1, $level=0, $y=0){
		$this->Bookmark($this->_UTF8toUTF16($txt),$page, $level,$y);
	}
	
	function _putbookmarks(){
		$nb=count($this->outlines);
		if($nb==0)
			return;
		$lru=array();
		$level=0;
		foreach($this->outlines as $i=>$o)
		{
			if($o['l']>0)
			{
				$parent=$lru[$o['l']-1];
				//Set parent and last pointers
				$this->outlines[$i]['parent']=$parent;
				$this->outlines[$parent]['last']=$i;
				if($o['l']>$level)
				{
					//Level increasing: set first pointer
					$this->outlines[$parent]['first']=$i;
				}
			}
			else
				$this->outlines[$i]['parent']=$nb;
			if($o['l']<=$level and $i>0)
			{
				//Set prev and next pointers
				$prev=$lru[$o['l']];
				$this->outlines[$prev]['next']=$i;
				$this->outlines[$i]['prev']=$prev;
			}
			$lru[$o['l']]=$i;
			$level=$o['l'];
		}
		//Outline items
		$n=$this->n+1;
		foreach($this->outlines as $i=>$o)
		{
			$this->_newobj();
			$this->_out('<</Title '.$this->_textstring($o['t']));
			$this->_out('/Parent '.($n+$o['parent']).' 0 R');
			if(isset($o['prev']))
				$this->_out('/Prev '.($n+$o['prev']).' 0 R');
			if(isset($o['next']))
				$this->_out('/Next '.($n+$o['next']).' 0 R');
			if(isset($o['first']))
				$this->_out('/First '.($n+$o['first']).' 0 R');
			if(isset($o['last']))
				$this->_out('/Last '.($n+$o['last']).' 0 R');
			$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]',1+2*$o['p'],$o['y']));
			$this->_out('/Count 0>>');
			$this->_out('endobj');
		}
		//Outline root
		$this->_newobj();
		$this->OutlineRoot=$this->n;
		$this->_out('<</Type /Outlines /First '.$n.' 0 R');
		$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
		$this->_out('endobj');
	}
	
	function _putresources(){
		parent::_putresources();
		$this->_putbookmarks();
	}
	
	function _putcatalog(){
		parent::_putcatalog();
		if(count($this->outlines)>0)
		{
			$this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
			$this->_out('/PageMode /UseOutlines');
		}
	}

}
?>