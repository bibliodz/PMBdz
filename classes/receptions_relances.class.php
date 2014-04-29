<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: receptions_relances.class.php,v 1.7 2013-04-04 13:02:43 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once("$class_path/pdf_factory.class.php");
require_once("$class_path/rtf_factory.class.php");
require_once("$class_path/lignes_actes.class.php");
require_once("$class_path/types_produits.class.php");

class lettreRelance_PDF {
	
	var $PDF;
	var $orient_page = 'P';			//Orientation page (P=portrait, L=paysage)
	var $largeur_page = 210;		//Largeur de page
	var $hauteur_page = 297;		//Hauteur de page
	var $unit = 'mm';				//Unite 
	var $marge_haut = 10;			//Marge haut
	var $marge_bas = 20;			//Marge bas
	var $marge_droite = 10;			//Marge droite
	var $marge_gauche = 10;			//Marge gauche
	var $w = 190;					//Largeur utile page
	var $font = 'Helvetica';		//Police
	var $fs = 10;					//Taille police 
	var $x_logo = 10;				//Distance du logo / bord gauche de page
	var $y_logo = 10;				//Distance du logo / bord haut de page
	var $l_logo = 20;				//Largeur logo
	var $h_logo = 20;				//Hauteur logo
	var $x_raison = 35;				//Distance raison sociale / bord gauche de page
	var $y_raison = 10;				//Distance raison sociale / bord haut de page
	var $l_raison = 100;			//Largeur raison sociale
	var $h_raison = 10;				//Hauteur raison sociale
	var $fs_raison = 16;			//Taille police raison sociale
	var $x_date = 150;				//Distance date / bord gauche de page
	var $y_date = 10;				//Distance date / bord haut de page
	var $l_date = 0;				//Largeur date
	var $h_date = 6;				//Hauteur date
	var $fs_date = 8;				//Taille police date
	var $sep_ville_date = '';		//S�parateur entre ville et date
	var $x_adr_rel = 10;			//Distance adr relance / bord gauche de page
	var $y_adr_rel = 35;			//Distance adr relance / bord haut de page
	var $l_adr_rel = 60;			//Largeur adr relance
	var $h_adr_rel = 5;				//Hauteur adr relance
	var $fs_adr_rel = 10;			//Taille police adr relance
	var $text_adr_tel = '';
	var $text_adr_fax = '';
	var $text_adr_email = '';
	var $x_adr_fou = 100;			//Distance adr fournisseur / bord gauche de page
	var $y_adr_fou = 55;			//Distance adr fournisseur / bord haut de page
	var $l_adr_fou = 100;			//Largeur adr fournisseur
	var $h_adr_fou = 6;				//Hauteur adr fournisseur
	var $fs_adr_fou = 14;			//Police adr fournisseur
	var $x_titre = 10;				//Distance titre / bord gauche de page
	var $y_titre = 90;				//Distance titre / bord haut de page
	var $l_titre = 100;				//Largeur titre
	var $h_titre = 10;				//Hauteur titre
	var $fs_titre = 16;				//Police titre
	var $text_titre = '';
	var $x_num = 10;				//Distance num commande/devis / bord gauche de page
	var $l_num = 0;					//Largeur num commande
	var $h_num = 10;				//Hauteur num commande
	var $fs_num = 16;				//Taille police num commande/devis
	var $text_num = '';				//Texte commande/devis
	var $text_ech = '';				//Texte date echeance
	var $text_num_ech = '';
	var $x_num_cli = 10;			//Distance num client / bord gauche de page
	var $y_num_cli = 80;			//Distance num client / bord haut de page
	var $l_num_cli = 0;				//Largeur num commande
	var $h_num_cli = 10;			//Hauteur num commande
	var $fs_num_cli = 16;			//Taille police num commande/devis
	var $text_num_cli = '';			//Texte num�ro client
	var $text_before = '';			//texte avant table relances
	var $text_after = '';			//texte apr�s table relances
	var $h_tab = 5;					//Hauteur de ligne table relance
	var $fs_tab = 10;				//Taille police table relance
	var $x_tab = 10;				//position table relance / bord droit page 
	var $y_tab = 10;				//position table relance / haut page sur pages 2 et + 
	var $x_sign = 10;				//Distance signature / bord gauche de page
	var $l_sign = 60;				//Largeur cellule signature
	var $h_sign = 5;				//Hauteur signature
	var $fs_sign = 10;				//Taille police signature
	var $text_sign = '';			//Texte signature
	var $y_footer = 15;				//Distance footer / bas de page
	var $fs_footer = 8;				//Taille police footer
	var $x_col1 = '';
	var $w_col1 = '';
	var $txt_header_col1 = '';
	var $x_col2 = '';
	var $w_col2 = '';
	var $txt_header_col2 = '';
	var $x_col3 = '';
	var $w_col3 = '';
	var $txt_header_col3 = '';
	var $x_col4 = '';
	var $w_col4 = '';
	var $txt_header_col4 = '';
	var $x_col5 = '';
	var $w_col5 = '';
	var $txt_header_col5 = '';
	var $y = 0;
	var $h = 0;
	var $s = 0;
	var $h_header = 0;
	var $p_header = false;
	var $filename = 'lettre_relance.pdf';
	
	function __construct() {
		
		global $msg, $charset, $pmb_pdf_font;
		global $acquisition_pdfrel_orient_page, $acquisition_pdfrel_text_size, $acquisition_pdfrel_format_page, $acquisition_pdfrel_marges_page;
		global $acquisition_pdfrel_pos_logo, $acquisition_pdfrel_pos_raison, $acquisition_pdfrel_pos_date, $acquisition_pdfrel_pos_adr_fac;
		global $acquisition_pdfrel_pos_adr_liv, $acquisition_pdfrel_pos_adr_fou, $acquisition_pdfrel_pos_num, $acquisition_pdfrel_text_before;
		global $acquisition_pdfrel_text_after, $acquisition_pdfrel_tab_rel, $acquisition_pdfrel_pos_sign, $acquisition_pdfrel_text_sign;
		global $acquisition_pdfrel_pos_footer, $acquisition_pdfrel_pos_titre, $acquisition_pdfrel_pos_num_cli ;
			
		if($acquisition_pdfrel_orient_page) $this->orient_page = $acquisition_pdfrel_orient_page;
		
		$format_page = explode('x',$acquisition_pdfrel_format_page);
		if($format_page[0]) $this->largeur_page = $format_page[0];
		if($format_page[1]) $this->hauteur_page = $format_page[1];

		$this->PDF = pdf_factory::make($this->orient_page, $this->unit, array($this->largeur_page, $this->hauteur_page));
		
		$marges_page = explode(',', $acquisition_pdfrel_marges_page);
		if ($marges_page[0]) $this->marge_haut = $marges_page[0];
		if ($marges_page[1]) $this->marge_bas = $marges_page[1];
		if ($marges_page[2]) $this->marge_droite = $marges_page[2];
		if ($marges_page[3]) $this->marge_gauche = $marges_page[3];
				
		$this->w = $this->largeur_page-$this->marge_gauche-$this->marge_droite;
		
		$this->font = $pmb_pdf_font;
		if($acquisition_pdfrel_text_size) $this->fs = $acquisition_pdfrel_text_size;

		$pos_logo = explode(',', $acquisition_pdfrel_pos_logo);
		if ($pos_logo[0]) $this->x_logo = $pos_logo[0];
		if ($pos_logo[1]) $this->y_logo = $pos_logo[1];
		if ($pos_logo[2]) $this->l_logo = $pos_logo[2];
		if ($pos_logo[3]) $this->h_logo = $pos_logo[3];

		$pos_raison = explode(',', $acquisition_pdfrel_pos_raison);
		if ($pos_raison[0]) $this->x_raison = $pos_raison[0];
		if ($pos_raison[1]) $this->y_raison = $pos_raison[1];
		if ($pos_raison[2]) $this->l_raison = $pos_raison[2];
		if ($pos_raison[3]) $this->h_raison = $pos_raison[3];
		if ($pos_raison[4]) $this->fs_raison = $pos_raison[4];
		
		$pos_date = explode(',', $acquisition_pdfrel_pos_date);
		if ($pos_date[0]) $this->x_date = $pos_date[0];
		if ($pos_date[1]) $this->y_date = $pos_date[1];
		if ($pos_date[2]) $this->l_date = $pos_date[2];
		if ($pos_date[3]) $this->h_date = $pos_date[3];
		if ($pos_date[4]) $this->fs_date = $pos_date[4];
		$this->sep_ville_date = $msg['acquisition_act_sep_ville_date'];
		
		$pos_adr_rel = explode(',', $acquisition_pdfrel_pos_adr_rel);
		if ($pos_adr_rel[0]) $this->x_adr_rel = $pos_adr_rel[0];
		if ($pos_adr_rel[1]) $this->y_adr_rel = $pos_adr_rel[1];
		if ($pos_adr_rel[2]) $this->l_adr_rel = $pos_adr_rel[2];
		if ($pos_adr_rel[3]) $this->h_adr_rel = $pos_adr_rel[3];
		if ($pos_adr_rel[4]) $this->fs_adr_rel = $pos_adr_rel[4];
		$this->text_adr_tel = $msg['acquisition_tel'].".";
		$this->text_adr_fax = $msg['acquisition_fax'].".";
		$this->text_adr_email = $msg['acquisition_mail']." :";
		
		$pos_adr_fou = explode(',', $acquisition_pdfrel_pos_adr_fou);
		if ($pos_adr_fou[0]) $this->x_adr_fou = $pos_adr_fou[0];
		if ($pos_adr_fou[1]) $this->y_adr_fou = $pos_adr_fou[1];
		if ($pos_adr_fou[2]) $this->l_adr_fou = $pos_adr_fou[2];
		if ($pos_adr_fou[3]) $this->h_adr_fou = $pos_adr_fou[3];
		if ($pos_adr_fou[4]) $this->fs_adr_fou = $pos_adr_fou[4];
		
		$pos_titre = explode(',', $acquisition_pdfrel_pos_titre);
		if ($pos_titre[0]) $this->x_titre = $pos_titre[0];
		if ($pos_titre[1]) $this->y_titre = $pos_titre[1];
		if ($pos_titre[2]) $this->l_titre = $pos_titre[2];
		if ($pos_titre[3]) $this->h_titre = $pos_titre[3];
		if ($pos_titre[4]) $this->fs_titre = $pos_titre[4];
		$this->text_titre = $msg['acquisition_recept_lettre_titre'];
		
		$pos_num = explode(',', $acquisition_pdfrel_pos_num);
		if ($pos_num[0]) $this->x_num = $pos_num[0];
		if ($pos_num[2]) $this->l_num = $pos_num[1];
		if ($pos_num[3]) $this->h_num = $pos_num[2];
		if ($pos_num[4]) $this->fs_num = $pos_num[3];
		$this->text_num = $msg['acquisition_act_num_cde'];
		$this->text_ech = $msg['acquisition_recept_lettre_ech'];
				
		$pos_num_cli = explode(',', $acquisition_pdfrel_pos_num_cli);
		if ($pos_num_cli[0]) $this->x_num_cli = $pos_num_cli[0];
		if ($pos_num_cli[0]) $this->x_num_cli = $pos_num_cli[0];
		if ($pos_num_cli[2]) $this->l_num_cli = $pos_num_cli[1];
		if ($pos_num_cli[3]) $this->h_num_cli = $pos_num_cli[2];
		if ($pos_num_cli[4]) $this->fs_num_cli = $pos_num_cli[3];
		$this->text_num_cli = $msg['acquisition_num_cp_client'];
		
		$this->text_before = $acquisition_pdfrel_text_before;
		$this->text_after = $acquisition_pdfrel_text_after;
		
		$pos_tab = explode(',', $acquisition_pdfrel_tab_rel);
		if ($pos_tab[0]) $this->h_tab = $pos_tab[0];
		if ($pos_tab[1]) $this->fs_tab = $pos_tab[1];
		$this->x_tab = $this->marge_gauche;
		$this->y_tab = $this->marge_haut; 
		
		$pos_sign = explode(',', $acquisition_pdfrel_pos_sign);
		if ($pos_sign[0]) $this->x_sign = $pos_sign[0];
		if ($pos_sign[1]) $this->l_sign = $pos_sign[1];
		if ($pos_sign[2]) $this->h_sign = $pos_sign[2];
		if ($pos_sign[3]) $this->fs_sign = $pos_sign[3];
			
		if ($acquisition_pdfrel_text_sign) $this->text_sign = $acquisition_pdfrel_text_sign;
			else $this->text_sign = $msg['acquisition_act_sign'];
		
		$pos_footer = explode(',', $acquisition_pdfrel_pos_footer);
		if ($pos_footer[0]) $this->PDF->y_footer = $pos_footer[0];
			else $this->PDF->y_footer=$this->y_footer;
		if ($pos_footer[1]) $this->PDF->fs_footer = $pos_footer[1];
			else $this->PDF->fs_footer=$this->fs_footer;
		
		$this->x_col1 =  $this->x_tab;
		$this->w_col1 = round($this->w*20/100);
		$this->txt_header_col1 = $msg['acquisition_act_tab_typ']."\n".$msg['acquisition_act_tab_code'];
		
		$this->x_col2 = $this->x_col1 + $this->w_col1;
		$this->w_col2 = round($this->w*50/100);
		$this->txt_header_col2 = $msg['acquisition_act_tab_lib'];
		
		$this->x_col3 = $this->x_col2 + $this->w_col2;
		$this->w_col3 = round($this->w*10/100); 
		$this->txt_header_col3 = $msg['acquisition_qte_cde'];
		
		$this->x_col4 = $this->x_col3 + $this->w_col3;
		$this->w_col4 = round($this->w*10/100); 
		$this->txt_header_col4 = $msg['acquisition_qte_liv'];
		
		$this->x_col5 = $this->x_col4 + $this->w_col4;
		$this->w_col5 = round($this->w*10/100); 
		$this->txt_header_col5 = $msg['acquisition_act_tab_sol'];
		
		$this->PDF->Open();
		$this->PDF->SetMargins($this->marge_gauche, $this->marge_haut, $this->marge_droite);
		$this->PDF->setFont($this->font);

		$this->h_header = $this->h_tab * max( 	$this->PDF->NbLines($this->w_col1, $this->txt_header_col1 ),
		$this->PDF->NbLines($this->w_col2,$this->txt_header_col2),
		$this->PDF->NbLines($this->w_col3, $this->txt_header_col3),
		$this->PDF->NbLines($this->w_col4, $this->txt_header_col4),
		$this->PDF->NbLines($this->w_col5, $this->txt_header_col5) );
		$this->p_header = false;
		
		$this->PDF->footer_type = 2;
		$this->PDF->msg_footer = $msg['acquisition_act_page'];
	}
	
	
	function doLettre(&$bib, &$bib_coord, &$fou, &$fou_coord, &$tab_act) {
		
		global $msg;
		
		$this->PDF->AddPage();
		$this->PDF->npage = 1;
		
		//Affichage logo
		if($bib->logo != '') {
			$this->PDF->Image($bib->logo, $this->x_logo, $this->y_logo, $this->l_logo, $this->h_logo);
		}
		
		//Affichage raison sociale
		$raison =  $bib->raison_sociale;
		$this->PDF->setFontSize($this->fs_raison);
		$this->PDF->SetXY($this->x_raison, $this->y_raison);
		$this->PDF->MultiCell($this->l_raison, $this->h_raison, $raison, 0, 'L', 0);
		
		//Affichage date $ville
		$ville_end=stripos($bib_coord->ville,"cedex");	
		if($ville_end!==false) $ville=trim(substr($bib_coord->ville,0,$ville_end));
		else $ville=$bib_coord->ville;
		$date = $ville.$this->sep_ville_date.format_date(today());
		$this->PDF->setFontSize($this->fs_date);
		$this->PDF->SetXY($this->x_date, $this->y_date);
		$this->PDF->Cell($this->l_date, $this->h_date, $date, 0, 0, 'L', 0);
		
		//Affichage coordonnees fournisseur
		//si pas de raison sociale d�finie, on reprend le libell�
		//si il y a une raison sociale, pas besoin 
		if($fou->raison_sociale != '') {
			$adr_fou = $fou->raison_sociale."\n";
		} else { 
			$adr_fou = $coord_fou->libelle."\n";
		}
		if($fou_coord->adr1 != '') $adr_fou.= $fou_coord->adr1."\n";
		if($fou_coord->adr2 != '') $adr_fou.= $fou_coord->adr2."\n";
		if($fou_coord->cp != '') $adr_fou.= $fou_coord->cp." ";
		if($fou_coord->ville != '') $adr_fou.= $fou_coord->ville."\n\n";
		if ($fou_coord->contact != '') $adr_fou.= $fou_coord->contact;
		$this->PDF->setFontSize($this->fs_adr_fou);
		$this->PDF->SetXY($this->x_adr_fou, $this->y_adr_fou);
		$this->PDF->MultiCell($this->l_adr_fou, $this->h_adr_fou, $adr_fou, 0, 'L', 0);
		
		//Affichage adresse bibliotheque
		$adr_rel=''; 
		if($bib_coord->libelle != '') $adr_rel.= $bib_coord->libelle."\n"; 
		if($bib_coord->adr1 != '') $adr_rel.= $bib_coord->adr1."\n";
		if($bib_coord->adr2 != '') $adr_rel.= $bib_coord->adr2."\n";
		if($bib_coord->cp != '') $adr_rel.= $bib_coord->cp." ";
		if($bib_coord->ville != '') $adr_rel.= $bib_coord->ville."\n";
		if($bib_coord->tel1 != '') $adr_rel.= $this->text_adr_tel." ".$bib_coord->tel1."\n";
		if($bib_coord->fax != '') $adr_rel.= $this->text_adr_fax." ".$bib_coord->fax."\n";
		if($bib_coord->email != '') $adr_rel.= $this->text_adr_email." ".$bib_coord->email."\n";
		$this->PDF->setFontSize($this->fs_adr_rel);
		$this->PDF->SetXY($this->x_adr_rel, $this->y_adr_rel);
		$this->PDF->MultiCell($this->l_adr_rel, $this->h_adr_rel, $adr_rel, 1, 'L', 0);
		
		//Affichage numero client
		$numero_cli = $this->text_num_cli." ".$fou->num_cp_client;
		$this->PDF->SetFontSize($this->fs_num_cli);
		$this->PDF->SetXY($this->x_num_cli, $this->y_num_cli);
		$this->PDF->Cell($this->l_num_cli, $this->h_num_cli, $numero_cli, 0, 0, 'L', 0);
		$this->PDF->Ln();
		
		//Affichage titre
		$this->PDF->setFontSize($this->fs_titre);
		$this->PDF->SetXY($this->x_titre, $this->y_titre);
		$this->PDF->Cell($this->l_titre, $this->h_titre, $this->text_titre, 0, 0, 'L', 0);
		
		//Affichage tiret pliage 
		$this->PDF->Line(0,105, 3, 105);
		$this->y=$this->PDF->GetY();
		$this->PDF->Ln();
		$this->PDF->Ln();

		//Affichage texte before
		if ($this->text_before != '') {
			$this->PDF->SetFontSize($this->fs);
			$this->PDF->MultiCell($this->w, $this->h_tab, $this->text_before, 0, 'J', 0);
		}
		
		//Affichage des lignes de relances
		$this->PDF->SetAutoPageBreak(false);
		$this->PDF->AliasNbPages();
	
		$this->PDF->SetFontSize($this->fs_tab);
		$this->PDF->SetFillColor(230);
		$this->y = $this->PDF->GetY();
		$this->PDF->SetXY($this->x_tab,$this->y);
		
		foreach($tab_act as $id_act=>$tab_lig) {
			
			$this->p_header = false;
			$act = new actes($id_act);
			$this->text_num_ech = $this->text_num.' '.$act->numero;
			if ($act->date_ech!='0000-00-00') $this->text_num_ech.= ' '.sprintf($this->text_ech,format_date($act->date_ech));
			
			foreach($tab_lig as $id_lig) {
				
				$lig = new lignes_actes($id_lig);
				$typ = new types_produits($lig->num_type);
				$col1 = $typ->libelle;
				if($lig->code) $col1.= "\n".$lig->code;
				$col2 = $lig->libelle;
				$col3 = $lig->nb;
				$col4 = $lig->getNbDelivered();
				$col5 = $col3-$col4;
				
				//Est ce qu'on d�passe ?		
				$this->h = $this->h_tab * max( 	$this->PDF->NbLines($this->w_col1, $col1),
							$this->PDF->NbLines($this->w_col2, $col2),
							$this->PDF->NbLines($this->w_col3, $col3),
							$this->PDF->NbLines($this->w_col4, $col4),
							$this->PDF->NbLines($this->w_col5, $col5) );
				$this->s = $this->y+$this->h;
				if(!$this->p_header) $this->s=$this->s + $this->h_header;		
				
				//Si oui, chgt page
				if ($this->s > ($this->hauteur_page-$this->marge_bas-$this->fs_footer)){
					$this->PDF->AddPage();
					$this->y = $this->y_tab;
					$this->p_header = false;
				}
				if (!$this->p_header) {
					$this->doEntete();		
					$this->y+=$this->h_header;		
				}
				$this->p_header = true; 
				
				$this->PDF->SetXY($this->x_col1, $this->y);
				$this->PDF->Rect($this->x_col1, $this->y, $this->w_col1, $this->h);
				$this->PDF->MultiCell($this->w_col1, $this->h_tab, $col1, 0, 'L');
				$this->PDF->SetXY($this->x_col2, $this->y);
				$this->PDF->Rect($this->x_col2, $this->y, $this->w_col2, $this->h);
				$this->PDF->MultiCell($this->w_col2, $this->h_tab, $col2, 0, 'L');
				$this->PDF->SetXY($this->x_col3, $this->y);
				$this->PDF->Rect($this->x_col3, $this->y, $this->w_col3, $this->h);
				$this->PDF->MultiCell($this->w_col3, $this->h_tab, $col3, 0, 'R');
				$this->PDF->SetXY($this->x_col4, $this->y);
				$this->PDF->Rect($this->x_col4, $this->y, $this->w_col4, $this->h);
				$this->PDF->MultiCell($this->w_col4, $this->h_tab, $col4, 0, 'R');
				$this->PDF->SetXY($this->x_col5, $this->y);
				$this->PDF->Rect($this->x_col5, $this->y, $this->w_col5, $this->h);
				$this->PDF->MultiCell($this->w_col5, $this->h_tab, $col5, 0, 'R');
				$this->y+= $this->h;
			
			}
		}

		$this->PDF->SetAutoPageBreak(true, $this->marge_bas);
		$this->PDF->SetX($this->marge_gauche);
		$this->PDF->SetY($this->y);
		$this->PDF->Ln();
		$this->PDF->SetFontSize($this->fs);
	
		//Affichage texte after
		$this->PDF->Ln();
		if ($this->text_after != '') {
			$this->PDF->MultiCell($this->w, $this->h_tab, $this->text_after, 0, 'J', 0);
			$this->PDF->Ln();
		}
		
		//Affichage signature
		$this->PDF->Ln();
		$this->PDF->SetFontSize($this->fs_sign);
		$this->PDF->SetX($this->x_sign);
		$this->PDF->MultiCell($this->l_sign, $this->h_sign, $this->text_sign, 0, 'L', 0);
					

	}
	
	function getLettre($format=0,$name='lettre_relance.pdf') {
		if (!$format) {
			return $this->PDF->OutPut();
		} else {
			return $this->PDF->OutPut($name,'S');
		}
	}
	
	function getFileName() {
		return $this->filename;
	}
	
	//Entete de tableau
	function doEntete() {
		$this->PDF->SetXY($this->x_num,$this->y);
		$this->PDF->MultiCell($this->w_num, $this->h_num, $this->text_num_ech, 0, 'L');
		$this->y = $this->PDF->GetY();
		$this->PDF->SetXY($this->x_col1, $this->y);
		$this->PDF->Rect($this->x_col1, $this->y, $this->w_col1, $this->h_header, 'FD');
		$this->PDF->MultiCell($this->w_col1, $this->h_tab, $this->txt_header_col1, 0, 'L');
		$this->PDF->SetXY($this->x_col2, $this->y);
		$this->PDF->Rect($this->x_col2, $this->y, $this->w_col2, $this->h_header, 'FD');
		$this->PDF->MultiCell($this->w_col2, $this->h_tab, $this->txt_header_col2, 0, 'L');
		$this->PDF->SetXY($this->x_col3, $this->y);
		$this->PDF->Rect($this->x_col3, $this->y, $this->w_col3, $this->h_header, 'FD');
		$this->PDF->MultiCell($this->w_col3, $this->h_tab, $this->txt_header_col3, 0, 'L');
		$this->PDF->SetXY($this->x_col4, $this->y);
		$this->PDF->Rect($this->x_col4, $this->y, $this->w_col4, $this->h_header, 'FD');
		$this->PDF->MultiCell($this->w_col4, $this->h_tab, $this->txt_header_col4, 0, 'L');
		$this->PDF->SetXY($this->x_col5, $this->y);
		$this->PDF->Rect($this->x_col5, $this->y, $this->w_col5, $this->h_header, 'FD');
		$this->PDF->MultiCell($this->w_col5, $this->h_tab, $this->txt_header_col5, 0, 'L');
	}
	
}


class lettreRelance_RTF {
	
	var $RTF;
	var $sect;
	var $orient_page = 'P';			//Orientation page (P=portrait, L=paysage)
	var $largeur_page = 21;			//Largeur de page
	var $hauteur_page = 29.7;		//Hauteur de page
	var $unit = 'cm';				//Unite 
	var $marge_haut = 1;			//Marge haut
	var $marge_bas = 2;				//Marge bas
	var $marge_droite = 1;			//Marge droite
	var $marge_gauche = 1;			//Marge gauche
	var $w = 19;					//Largeur utile page
	var $fonts = array();			//Tableau de polices
	var $font = 'Helvetica';		//Nom police
	var $fs = 10;					//Taille police 
	var $x_logo = 1;				//Distance du logo / bord gauche de page
	var $y_logo = 1;				//Distance du logo / bord haut de page
	var $l_logo = 2;				//Largeur logo
	var $h_logo = 2;				//Hauteur logo
	var $x_raison = 3.5;			//Distance raison sociale / bord gauche de page
	var $y_raison = 1;				//Distance raison sociale / bord haut de page
	var $l_raison = 10;				//Largeur raison sociale
	var $h_raison = 1;				//Hauteur raison sociale
	var $fs_raison = 16;			//Taille police raison sociale
	var $x_date = 15;				//Distance date / bord gauche de page
	var $y_date = 1;				//Distance date / bord haut de page
	var $l_date = 0;				//Largeur date
	var $h_date = 6;				//Hauteur date
	var $fs_date = 8;				//Taille police date
	var $sep_ville_date = '';		//S�parateur entre ville et date
	var $x_adr_rel = 1;				//Distance adr relance / bord gauche de page
	var $y_adr_rel = 3.5;			//Distance adr relance / bord haut de page
	var $l_adr_rel = 6;				//Largeur adr relance
	var $h_adr_rel = 0.5;			//Hauteur adr relance
	var $fs_adr_rel = 10;			//Taille police adr relance
	var $text_adr_tel = '';
	var $text_adr_fax = '';
	var $text_adr_email = '';
	var $x_adr_fou = 10;			//Distance adr fournisseur / bord gauche de page
	var $y_adr_fou = 5.5;			//Distance adr fournisseur / bord haut de page
	var $l_adr_fou = 10;			//Largeur adr fournisseur
	var $h_adr_fou = 0.6;			//Hauteur adr fournisseur
	var $fs_adr_fou = 14;			//Police adr fournisseur
	var $x_titre = 1;				//Distance titre / bord gauche de page
	var $y_titre = 9;				//Distance titre / bord haut de page
	var $l_titre = 10;				//Largeur titre
	var $h_titre = 1;				//Hauteur titre
	var $fs_titre = 16;				//Police titre
	var $text_titre = '';
	var $x_num = 1;					//Distance num commande/devis / bord gauche de page
	var $l_num = 0;					//Largeur num commande
	var $h_num = 1;					//Hauteur num commande
	var $fs_num = 16;				//Taille police num commande/devis
	var $text_num = '';				//Texte commande/devis
	var $text_ech = '';				//Texte date echeance
	var $text_num_ech = '';
	var $x_num_cli = 1;				//Distance num client / bord gauche de page
	var $y_num_cli = 8;				//Distance num client / bord haut de page
	var $l_num_cli = 0;				//Largeur num commande
	var $h_num_cli = 1;				//Hauteur num commande
	var $fs_num_cli = 16;			//Taille police num commande/devis
	var $text_num_cli = '';			//Texte num�ro client
	var $text_before = '';			//texte avant table relances
	var $text_after = '';			//texte apr�s table relances
	var $h_tab = 0.5;				//Hauteur de ligne table relance
	var $fs_tab = 10;				//Taille police table relance
	var $x_tab = 1;					//position table relance / bord droit page 
	var $y_tab = 1;					//position table relance / haut page sur pages 2 et + 
	var $x_sign = 1;				//Distance signature / bord gauche de page
	var $l_sign = 6;				//Largeur cellule signature
	var $h_sign = 0.5;				//Hauteur signature
	var $fs_sign = 10;				//Taille police signature
	var $text_sign = '';			//Texte signature
	var $y_footer = 1.5;			//Distance footer / bas de page
	var $fs_footer = 8;				//Taille police footer
	var $msg_footer = '';
	var $x_col1 = '';
	var $w_col1 = '';
	var $txt_header_col1 = '';		//Hauteur entete tableau
	var $x_col2 = '';
	var $w_col2 = '';
	var $txt_header_col2 = '';
	var $x_col3 = '';
	var $w_col3 = '';
	var $txt_header_col3 = '';
	var $x_col4 = '';
	var $w_col4 = '';
	var $txt_header_col4 = '';
	var $x_col5 = '';
	var $w_col5 = '';
	var $txt_header_col5 = '';
	var $p_header = false;
	var $tab = null;
	var $row = 1;
	var $filename = 'lettre_relance.rtf';
	
	function __construct() {
		
		global $msg, $charset, $pmb_pdf_font;
		global $acquisition_pdfrel_orient_page, $acquisition_pdfrel_text_size, $acquisition_pdfrel_format_page, $acquisition_pdfrel_marges_page;
		global $acquisition_pdfrel_pos_logo, $acquisition_pdfrel_pos_raison, $acquisition_pdfrel_pos_date, $acquisition_pdfrel_pos_adr_fac;
		global $acquisition_pdfrel_pos_adr_liv, $acquisition_pdfrel_pos_adr_fou, $acquisition_pdfrel_pos_num, $acquisition_pdfrel_text_before;
		global $acquisition_pdfrel_text_after, $acquisition_pdfrel_tab_rel, $acquisition_pdfrel_pos_sign, $acquisition_pdfrel_text_sign;
		global $acquisition_pdfrel_pos_footer, $acquisition_pdfrel_pos_titre, $acquisition_pdfrel_pos_num_cli ;
		
		
		$this->RTF = rtf_factory::make();
		if($acquisition_pdfrel_orient_page=='L') $this->RTF->setLandscape();
		
		$format_page = explode('x',$acquisition_pdfrel_format_page);
		if($format_page[0]) $this->largeur_page = $format_page[0] / 10;
		if($format_page[1]) $this->hauteur_page = $format_page[1] / 10;
		$this->RTF->paperHeight = $this->hauteur_page;
		$this->RTF->paperWidth = $this->largeur_page;
		
		$marges_page = explode(',', $acquisition_pdfrel_marges_page);
		if ($marges_page[0]) $this->marge_haut = $marges_page[0] / 10;
		if ($marges_page[1]) $this->marge_bas = $marges_page[1] / 10;
		if ($marges_page[2]) $this->marge_droite = $marges_page[2] / 10;
		if ($marges_page[3]) $this->marge_gauche = $marges_page[3] / 10;
		
		$this->w = $this->largeur_page-$this->marge_droite-$this->marge_gauche;
		
		$this->font = $pmb_pdf_font;
		if($acquisition_pdfrel_text_size) $this->fs = $acquisition_pdfrel_text_size;
		$this->fonts['standard'] = new Font($this->fs, $this->font);
		
		$pos_logo = explode(',', $acquisition_pdfrel_pos_logo);
		if ($pos_logo[0]) $this->x_logo = $pos_logo[0] / 10;
		if ($pos_logo[1]) $this->y_logo = $pos_logo[1] / 10;
		if ($pos_logo[2]) $this->l_logo = $pos_logo[2] / 10;
		if ($pos_logo[3]) $this->h_logo = $pos_logo[3] / 10;

		$pos_raison = explode(',', $acquisition_pdfrel_pos_raison);
		if ($pos_raison[0]) $this->x_raison = $pos_raison[0] / 10;
		if ($pos_raison[1]) $this->y_raison = $pos_raison[1] / 10;
		if ($pos_raison[2]) $this->l_raison = $pos_raison[2] / 10;
		if ($pos_raison[3]) $this->h_raison = $pos_raison[3] / 10;
		if ($pos_raison[4]) $this->fs_raison = $pos_raison[4];
		$this->fonts['raison'] = new Font($this->fs_raison, $this->font);
		
		$pos_date = explode(',', $acquisition_pdfrel_pos_date);
		if ($pos_date[0]) $this->x_date = $pos_date[0] / 10;
		if ($pos_date[1]) $this->y_date = $pos_date[1] / 10;
		if ($pos_date[2]) $this->l_date = $pos_date[2] / 10;
		if ($pos_date[3]) $this->h_date = $pos_date[3] / 10;
		if ($pos_date[4]) $this->fs_date = $pos_date[4];
		$this->fonts['date'] = new Font($this->fs_date, $this->font);
		$this->sep_ville_date = $msg['acquisition_act_sep_ville_date'];
		
		$pos_adr_rel = explode(',', $acquisition_pdfrel_pos_adr_rel);
		if ($pos_adr_rel[0]) $this->x_adr_rel = $pos_adr_rel[0] / 10;
		if ($pos_adr_rel[1]) $this->y_adr_rel = $pos_adr_rel[1] / 10;
		if ($pos_adr_rel[2]) $this->l_adr_rel = $pos_adr_rel[2] / 10;
		if ($pos_adr_rel[3]) $this->h_adr_rel = $pos_adr_rel[3] / 10;
		if ($pos_adr_rel[4]) $this->fs_adr_rel = $pos_adr_rel[4];
		$this->fonts['adr_rel'] = new Font($this->fs_adr_rel, $this->font);
		$this->text_adr_tel = $msg['acquisition_tel'].".";
		$this->text_adr_fax = $msg['acquisition_fax'].".";
		$this->text_adr_email = $msg['acquisition_mail']." :";
		
		$pos_adr_fou = explode(',', $acquisition_pdfrel_pos_adr_fou);
		if ($pos_adr_fou[0]) $this->x_adr_fou = $pos_adr_fou[0] / 10;
		if ($pos_adr_fou[1]) $this->y_adr_fou = $pos_adr_fou[1] / 10;
		if ($pos_adr_fou[2]) $this->l_adr_fou = $pos_adr_fou[2] / 10;
		if ($pos_adr_fou[3]) $this->h_adr_fou = $pos_adr_fou[3] / 10;
		if ($pos_adr_fou[4]) $this->fs_adr_fou = $pos_adr_fou[4];
		$this->fonts['adr_fou'] = new Font($this->fs_adr_fou, $this->font);
		
		$pos_titre = explode(',', $acquisition_pdfrel_pos_titre);
		if ($pos_titre[0]) $this->x_titre = $pos_titre[0] / 10;
		if ($pos_titre[1]) $this->y_titre = $pos_titre[1] / 10;
		if ($pos_titre[2]) $this->l_titre = $pos_titre[2] / 10;
		if ($pos_titre[3]) $this->h_titre = $pos_titre[3] / 10;
		if ($pos_titre[4]) $this->fs_titre = $pos_titre[4];
		$this->fonts['titre'] = new Font($this->fs_titre, $this->font);
		$this->text_titre = $msg['acquisition_recept_lettre_titre'];
		
		$pos_num = explode(',', $acquisition_pdfrel_pos_num);
		if ($pos_num[0]) $this->x_num = $pos_num[0] / 10;
		if ($pos_num[2]) $this->l_num = $pos_num[1] / 10;
		if ($pos_num[3]) $this->h_num = $pos_num[2] / 10;
		if ($pos_num[4]) $this->fs_num = $pos_num[3];
		$this->fonts['num'] = new Font($this->fs_num, $this->font);
		$this->text_num = $msg['acquisition_act_num_cde'];
		$this->text_ech = $msg['acquisition_recept_lettre_ech'];
				
		$pos_num_cli = explode(',', $acquisition_pdfrel_pos_num_cli);
		if ($pos_num_cli[0]) $this->x_num_cli = $pos_num_cli[0] / 10;
		if ($pos_num_cli[0]) $this->x_num_cli = $pos_num_cli[0] / 10;
		if ($pos_num_cli[2]) $this->l_num_cli = $pos_num_cli[1] / 10;
		if ($pos_num_cli[3]) $this->h_num_cli = $pos_num_cli[2] / 10;
		if ($pos_num_cli[4]) $this->fs_num_cli = $pos_num_cli[3];
		$this->fonts['num_cli'] = new Font($this->fs_num_cli, $this->font);
		$this->text_num_cli = $msg['acquisition_num_cp_client'];
		
		$this->text_before = $acquisition_pdfrel_text_before;
		$this->text_after = $acquisition_pdfrel_text_after;
		
		$pos_tab = explode(',', $acquisition_pdfrel_tab_rel);
		if ($pos_tab[0]) $this->h_tab = $pos_tab[0] / 10;
		if ($pos_tab[1]) $this->fs_tab = $pos_tab[1] /10;
		$this->x_tab = $this->marge_gauche;
		$this->y_tab = $this->marge_haut; 
		
		$pos_sign = explode(',', $acquisition_pdfrel_pos_sign);
		if ($pos_sign[0]) $this->x_sign = $pos_sign[0] / 10;
		if ($pos_sign[1]) $this->l_sign = $pos_sign[1] / 10;
		if ($pos_sign[2]) $this->h_sign = $pos_sign[2] / 10;
		if ($pos_sign[3]) $this->fs_sign = $pos_sign[3];
		$this->fonts['sign'] = new Font($this->fs_sign, $this->font);
		
			
		if ($acquisition_pdfrel_text_sign) $this->text_sign = $acquisition_pdfrel_text_sign; 
			else $text_sign = $msg['acquisition_act_sign'];
		
		$pos_footer = explode(',', $acquisition_pdfrel_pos_footer);
		if ($pos_footer[0]) $this->PDF->y_footer = $pos_footer[0] / 10;
			else $this->PDF->y_footer=$this->y_footer;
		if ($pos_footer[1]) $this->PDF->fs_footer = $pos_footer[1] / 10;
			else $this->PDF->fs_footer=$this->fs_footer;
		
		$this->x_col1 =  $this->x_tab;
		$this->w_col1 = floor($this->w*20/100);
		$this->txt_header_col1 = $msg['acquisition_act_tab_typ']."\n".$msg['acquisition_act_tab_code'];
		
		$this->x_col2 = $this->x_col1 + $this->w_col1;
		$this->w_col2 = floor($this->w*50/100);
		$this->txt_header_col2 = $msg['acquisition_act_tab_lib'];
		
		$this->x_col3 = $this->x_col2 + $this->w_col2;
		$this->w_col3 = floor(($this->w-$this->w_col1-$this->w_col2)/3);
		$this->txt_header_col3 = $msg['acquisition_qte_cde'];
		
		$this->x_col4 = $this->x_col3 + $this->w_col3;
		$this->w_col4 = floor(($this->w-$this->w_col1-$this->w_col2)/3);
		$this->txt_header_col4 = $msg['acquisition_qte_liv'];
		
		$this->x_col5 = $this->x_col4 + $this->w_col4;
		$this->w_col5 = floor(($this->w-$this->w_col1-$this->w_col2)/3); 
		$this->txt_header_col5 = $msg['acquisition_act_tab_sol'];
		
		$this->RTF->setMargins($this->marge_gauche, $this->marge_haut, $this->marge_droite ,$this->marge_bas);

		$this->RTF->addFooter('all');
		$this->msg_footer = $this->RTF->to_utf8($msg['acquisition_act_page']);
	}
	
	function doLettre(&$bib, &$bib_coord, &$fou, &$fou_coord, &$tab_act) {
		
		global $msg;
		
		$this->sect = &$this->RTF->addSection();
		//$this->RTF->footers[] = $this->msg_footer; 
		
		$tab1 = $this->sect->addTable();
		$tab1->addRows(1,0);
		$tab1->addColumnsList(array( 	$this->x_raison - $this->x_logo, 
										$this->x_date - $this->x_raison, 
										$this->largeur_page - $this->marge_droite - $this->x_date
									)
								);
		//$this->PDF->npage = 1;
		
		//Affichage logo
		if($bib->logo != '') {
			$par_logo = new ParFormat();
			$tab1->addImageToCell(1, 1, $bib->logo, new ParFormat(), $this->l_logo, $this->h_logo);		
		}
		
		//Affichage raison sociale
		$raison = $this->RTF->to_utf8($bib->raison_sociale);
		$par_raison = new ParFormat();
		$tab1->writeToCell(1,2,$raison, $this->fonts['raison'], $par_raison);
		
		//Affichage date ville
		$ville_end=stripos($bib_coord->ville,"cedex");	
		if($ville_end!==false) $ville=trim(substr($bib_coord->ville,0,$ville_end));
		else $ville=$bib_coord->ville;
		$date = $ville.$this->sep_ville_date.format_date(today());
		$date = $this->RTF->to_utf8($date);
		$par_ville = new ParFormat();
		$tab1->writeToCell(1,3,$date, $this->fonts['date'], $par_ville);
				
		$this->sect->writeText('', $this->fonts['standard'], new parFormat());
		
		$tab2 = $this->sect->addTable();
		$tab2->addRows(1,0);
		$tab2->addColumnsList(array( 	$this->l_adr_rel - $this->x_adr_rel, 
										$this->x_adr_fou - $this->l_adr_rel - $this->x_adr_rel,
										$this->largeur_page - $this->x_adr_fou
									)
								);
		
		//Affichage adresse bibliotheque
		$adr_rel=''; 
		if($bib_coord->libelle != '') $adr_rel.= $bib_coord->libelle."\r\n"; 
		if($bib_coord->adr1 != '') $adr_rel.= $bib_coord->adr1."\r\n";
		if($bib_coord->adr2 != '') $adr_rel.= $bib_coord->adr2."\r\n";
		if($bib_coord->cp != '') $adr_rel.= $bib_coord->cp." ";
		if($bib_coord->ville != '') $adr_rel.= $bib_coord->ville."\r\n";
		if($bib_coord->tel1 != '') $adr_rel.= $this->text_adr_tel." ".$bib_coord->tel1."\r\n";
		if($bib_coord->fax != '') $adr_rel.= $this->text_adr_fax." ".$bib_coord->fax."\r\n";
		if($bib_coord->email != '') $adr_rel.= $this->text_adr_email." ".$bib_coord->email."\r\n";
		$adr_rel = $this->RTF->to_utf8($adr_rel);
		$par_adr_rel = new parFormat();
		$tab2->writeToCell(1,1,$adr_rel, $this->fonts['adr_rel'], $par_adr_rel);
										
		//Affichage coordonnees fournisseur
		//si pas de raison sociale d�finie, on reprend le libell�
		//si il y a une raison sociale, pas besoin 
		if($fou->raison_sociale != '') {
			$adr_fou = $fou->raison_sociale."\r\n";
		} else { 
			$adr_fou = $coord_fou->libelle."\r\n";
		}
		if($fou_coord->adr1 != '') $adr_fou.= $fou_coord->adr1."\r\n";
		if($fou_coord->adr2 != '') $adr_fou.= $fou_coord->adr2."\r\n";
		if($fou_coord->cp != '') $adr_fou.= $fou_coord->cp." ";
		if($fou_coord->ville != '') $adr_fou.= $fou_coord->ville."\r\n\r\n";
		if ($fou_coord->contact != '') $adr_fou.= $fou_coord->contact;
		$adr_fou = $this->RTF->to_utf8($adr_fou);
		$par_adr_fou = new parFormat();
		$tab2->writeToCell(1,3,$adr_fou, $this->fonts['adr_fou'], $par_adr_fou);
		
		
		//Affichage numero client
		$numero_cli = $this->RTF->to_utf8($this->text_num_cli." ".$fou->num_cp_client);
		$par_numero_cli = new parFormat();
		$par_numero_cli->setSpaceAfter(10);
		$this->sect->writeText($numero_cli, $this->fonts['num_cli'], $par_numero_cli);
		
		//Affichage titre
		$text_titre = $this->RTF->to_utf8($this->text_titre);
		$par_titre = new parFormat();
		$par_titre->setSpaceAfter(10);
		$par_titre->setIndentLeft($this->x_titre - $this->marge_gauche);
		$this->sect->writeText($text_titre, $this->fonts['titre'], $par_titre);

		//Affichage texte before
		if ($this->text_before != '') {
			$text_before = $this->RTF->to_utf8($this->text_before);
			$par_before = new parFormat();
			$this->sect->writeText($text_before, $this->fonts['standard'], $par_before);
		}
		//Affichage des lignes de relances
		foreach($tab_act as $id_act=>$tab_lig) {
			
			$this->p_header = false;
			$act = new actes($id_act);
			$this->text_num_ech = $this->text_num.' '.$act->numero;
			if ($act->date_ech!='0000-00-00') $this->text_num_ech.= ' '.sprintf($this->text_ech,format_date($act->date_ech));
			$this->doEntete();
			
			foreach($tab_lig as $id_lig) {
				
				$lig = new lignes_actes($id_lig);
				$typ = new types_produits($lig->num_type);
				$col1 = $typ->libelle;
				if($lig->code) $col1.= "\r\n".$lig->code;
				$col2 = $lig->libelle;
				$col3 = $lig->nb;
				$col4 = $lig->getNbDelivered();
				$col5 = $col3-$col4;
				
				$this->tab->addRows(1,0);
				$this->tab->addColumnsList(array( 	$this->w_col1, 
													$this->w_col2,
													$this->w_col3,
													$this->w_col4,
													$this->w_col5
												)
											);
				$border_format = new BorderFormat(0.5, "#000000");

				$txt_col1 = $this->RTF->to_utf8($col1);
				$par_col1 = new parFormat();
				$this->tab->writeToCell($this->row,1,$txt_col1, $this->fonts['standard'], $par_col1);
				
				$txt_col2 = $this->RTF->to_utf8($col2);
				$par_col2 = new parFormat();
				$this->tab->writeToCell($this->row,2,$txt_col2, $this->fonts['standard'], $par_col2);

				$txt_col3 = $this->RTF->to_utf8($col3);
				$par_col3 = new parFormat();
				$this->tab->writeToCell($this->row,3,$txt_col3, $this->fonts['standard'], $par_col3);
				
				$txt_col4 = $this->RTF->to_utf8($col4);
				$par_col4 = new parFormat();
				$this->tab->writeToCell($this->row,4,$txt_col4, $this->fonts['standard'], $par_col4);
				
				$txt_col5 = $this->RTF->to_utf8($col5);
				$par_col5 = new parFormat();
				$this->tab->writeToCell($this->row,5,$txt_col5, $this->fonts['standard'], $par_col5);
				
				$this->tab->setBordersOfCells($border_format, 1, 1, $this->row, 5);
				
				$this->row++;
			}
		}

		//Affichage texte after
		if ($this->text_after != '') {
			$text_after = $this->RTF->to_utf8($this->text_after);
			$par_after = new parFormat();
			
			$this->sect->writeText($text_after, $this->fonts['standard'], $par_after);
		}
		
		//Affichage signature
		$text_sign = $this->RTF->to_utf8($this->text_sign);
		$par_sign = new parFormat();
		$par_sign->setSpaceBefore(10);
		$par_sign->setIndentLeft($this->x_sign - $this->marge_gauche);
		$this->sect->writeText($text_sign, $this->fonts['sign'], $par_sign);
		$this->sect->insertPageBreak();
	}
	
	
	function getLettre($name='lettre_relance') {	
		
		return $this->RTF->sendRtf($name);
	}	
	
	function getFileName() {
		return $this->filename;
	}
	
	function doEntete() {

		$text_num_ech = $this->RTF->to_utf8($this->text_num_ech);
		$par_num_ech = new parFormat();
		$par_num_ech->setSpaceBefore(10);
		$par_num_ech->setSpaceAfter(10);
		$this->sect->writeText($text_num_ech, $this->fonts['standard'], $par_num_ech);
		
		$this->tab = $this->sect->addTable();
		$this->row=1;
				
		$this->tab->addRows(1,0);
		$this->tab->addColumnsList(array( 	$this->w_col1, 
											$this->w_col2,
											$this->w_col3,
											$this->w_col4,
											$this->w_col5
										)
									);
		$border_format = new BorderFormat(0.5, "#000000");
		$txt_header_col1 = $this->RTF->to_utf8($this->txt_header_col1);
		$par_header_col1 = new parFormat();
		$this->tab->writeToCell($this->row,1,$txt_header_col1, $this->fonts['standard'], $par_header_col1);
		$txt_header_col2 = $this->RTF->to_utf8($this->txt_header_col2);
		$par_header_col2 = new parFormat();
		$this->tab->writeToCell($this->row,2,$txt_header_col2, $this->fonts['standard'], $par_header_col2);
		$txt_header_col3 = $this->RTF->to_utf8($this->txt_header_col3);
		$par_header_col3 = new parFormat();
		$this->tab->writeToCell($this->row,3,$txt_header_col3, $this->fonts['standard'], $par_header_col3);
		$txt_header_col4 = $this->RTF->to_utf8($this->txt_header_col4);
		$par_header_col4 = new parFormat();
		$this->tab->writeToCell($this->row,4,$txt_header_col4, $this->fonts['standard'], $par_header_col4);
		$txt_header_col5 = $this->RTF->to_utf8($this->txt_header_col5);
		$par_header_col5 = new parFormat();
		$this->tab->writeToCell($this->row,5,$txt_header_col5, $this->fonts['standard'], $par_header_col5);
		$this->tab->setBordersOfCells($border_format, 1, 1, 1, 5);
		$this->tab->setBackgroundOfCells('#D3D3D3', 1, 1, 1, 5); 
		$this->row++;
	}
}