<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: consolidation.class.php,v 1.7 2013-03-28 17:58:26 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


require_once ($class_path . "/parse_format.class.php");

if(!defined('DEFAULT_CONSO')) define('DEFAULT_CONSO',1);
if(!defined('INTERVAL_CONSO')) define('INTERVAL_CONSO',2);
if(!defined('ECHEANCE_CONSO')) define('ECHEANCE_CONSO',3);

class consolidation {
	
	var $mode=1;
	var $date_debut='';
	var $date_fin='';
	var $echeance='';
	var $list_idview = array();
	var $remove_data=0;
	var $flag = false;
	var $cols_vue=array();
	
	var $nom_vue='';
	var $date_consolidation='0000-00-00 00:00:00';
	var $date_debut_log='0000-00-00 00:00:00';
	var $date_fin_log='0000-00-00 00:00:00';
	
	
	function consolidation($mode=1,$date_debut='',$date_fin='',$echeance='',$list_ck='',$remove_data=0){
		$this->mode = $mode;
		$this->date_debut = $date_debut;
		$this->date_fin = $date_fin;
		$this->echeance = $echeance;
		$this->list_idview = $list_ck;
		$this->remove_data = $remove_data;
	}

	
	function make_consolidation(){

		global $dbh;
		
		foreach($this->list_idview as $id_vue){
			
			$req_vue=mysql_query('select * from statopac_vues where id_vue='.$id_vue.' ',$dbh);
			if (mysql_num_rows($req_vue)) {
				$row = mysql_fetch_object($req_vue);
				$this->nom_vue = $row->nom_vue;
				$this->date_debut_log = $row->date_debut_log;
				$this->date_fin_log = $row->date_fin_log;
				$this->date_consolidation = $row->date_consolidation;
			} else {
				continue;
			}
			
			
			switch($this->mode){
				
				case INTERVAL_CONSO:
					$this->check_structure($id_vue);
					$this->calculer_sur_periode($id_vue, $this->date_debut,$this->date_fin);
					$this->consolider($id_vue);
					break;
				
				case ECHEANCE_CONSO:
					$this->check_structure($id_vue);
					$this->calculer_until($id_vue, $this->echeance);
					$this->consolider($id_vue);
					break;
					
				default:
					$this->check_structure($id_vue);
					$this->calculer_since_last($id_vue);
					$this->consolider($id_vue);
					break;			
				
			}
		}
	}

	
	/**
	 * Fonction qui permet d'extraire un lot de log entre des dates pr�cises
	 */
	function calculer_sur_periode($id_vue, $date_deb, $date_fin){
		global $dbh;		
		
		$req = "create temporary table logs_filtre_$id_vue select * from statopac where date_log between '".addslashes($date_deb)."' and '".addslashes($date_fin)."'";
		mysql_query($req,$dbh);
		if ($this->flag || $this->remove_data) {
			$this->set_dates_log($id_vue);
		} else {
			$this->set_dates_log($id_vue,true);
		}
	}

	
	/**
	 * Fonction qui permet d'extraire un lot de log depuis le d�but des enregistrements jusqu'� l'�ch�ance fix�e
	 */
	function calculer_until($id_vue, $echeance){
		global $dbh;
		if ($this->flag || $this->remove_data || $this->date_fin_log=='0000-00-00 00:00:00') {
			$req = "create temporary table logs_filtre_$id_vue select * from statopac where date_log <='".addslashes($echeance)."'";
			mysql_query($req,$dbh);
			$this->set_dates_log($id_vue);
		} else {
			$req = "create temporary table logs_filtre_$id_vue select * from statopac where date_log > '".$this->date_fin_log."' and  date_log <= '".addslashes($echeance)."' ";
			mysql_query($req,$dbh);
			$this->set_dates_log($id_vue,true);
		}
	}

	
	/**
	 * Fonction qui permet d'extraire un lot de log depuis la date de derni�re consolidation
	 */	
	function calculer_since_last($id_vue){
		global $dbh;

		//$req_vue = "select date_consolidation from statopac_vues where id_vue='".addslashes($id_vue)."'";
		//$res_vue = mysql_query($req_vue,$dbh);
		if ($this->flag || $this->remove_data || $this->date_fin_log=='0000-00-00 00:00:00') {
			$req = "create temporary table logs_filtre_$id_vue select * from statopac where date_log <= now()";
			mysql_query($req,$dbh);
			$this->set_dates_log($id_vue);
		} else {
			$req = "create temporary table logs_filtre_$id_vue select * from statopac where date_log > '".$this->date_fin_log."' ";
			//Si on prend date_consolidation on risque de perdre des informations
			mysql_query($req,$dbh);
			$this->set_dates_log($id_vue,true);
		}
	}


	/**
	 * Calcul des dates de debut et de fin de log 
	 */
	function set_dates_log($id_vue,$only_last=false) {
		global $dbh;
		if (!$id_vue) return;
		$q = 'select min(date_log) as min_date, max(date_log) as max_date from logs_filtre_'.$id_vue;
		$r = mysql_query($q, $dbh);
		if (mysql_num_rows($r)) {
			$row = mysql_fetch_object($r);
			if(!$this->date_debut_log || $this->date_debut_log === "0000-00-00 00:00:00"){
				$this->date_debut_log = $row->min_date;
			}else{
				//On regarde quel date on doit garder
				$res=mysql_query("SELECT DATEDIFF('".$this->date_debut_log."','".$row->min_date."')");
				if(mysql_num_rows($res)){
					if((mysql_result($res,0,0))*1 > 1){
						$this->date_debut_log=$row->min_date;
					}else{
						//La date de d�but est d�j� ent�rieur aux logs que l'on va ajouter
					}
				}
			}				
			if(!$this->date_fin_log || $this->date_fin_log === "0000-00-00 00:00:00"){
				$this->date_fin_log = $row->max_date;
			}else{
				//On regarde quel date on doit garder
				$res=mysql_query("SELECT DATEDIFF('".$this->date_fin_log."','".$row->max_date."')");
				if(mysql_num_rows($res)){
					if((mysql_result($res,0,0))*1 > 1){
						//La date de fin est d�j� supp�rieur aux logs que l'on va ajouter
					}else{
						$this->date_fin_log=$row->max_date;
						
					}
				}
			}
		}
	}
	
	
	/**
	 * Fonction qui v�rifie que la structure des tables de vues n'a pas �t� modifi�e 
	 */
	function check_structure($id_vue) {
		
		global $dbh;
		
		//Test pour savoir si la structure des colonnes a �t� modifi�e
		$rqt_sum="select sum(maj_flag) from statopac_vues_col where num_vue=$id_vue";
		$res_sum=mysql_query($rqt_sum);
		$this->flag = mysql_result($res_sum,0,0);
		
		//On supprime la table dynamique si la structure a �t� modifi�e
		if($this->flag) {
			$rqt_trunc = "DROP TABLE statopac_vue_".addslashes($id_vue);
			@mysql_query($rqt_trunc, $dbh);
		}
		$rqt_create = "CREATE TABLE IF NOT EXISTS statopac_vue_".addslashes($id_vue)." (id_ligne INT( 8 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY)";
		mysql_query($rqt_create, $dbh);	
		
		//On supprime les donn�es si demand�
		if($this->remove_data) {
			$rqt_trunc = "TRUNCATE TABLE  statopac_vue_".addslashes($id_vue);
			@mysql_query($rqt_trunc, $dbh);
		}
		
		// cr�ation des colonnes de la table de la vue
		$rqt_col = "select id_col, nom_col, expression, filtre, datatype from statopac_vues_col where num_vue='".addslashes($id_vue)."'";
		$res_col=mysql_query($rqt_col, $dbh);
		$this->cols_vue=array();
		while(($col=mysql_fetch_object($res_col))){			
			//On ajoute les champs (indicateurs)
			$this->cols_vue[]=$col;
			if($col->datatype == 'small_text')
				$type_col = 'varchar(255)';
			else $type_col = $col->datatype; 
			$rqt_addfield = "ALTER TABLE statopac_vue_".addslashes($id_vue)." ADD ".addslashes(trim($col->nom_col))." ".addslashes($type_col)." NOT NULL";
			mysql_query($rqt_addfield);
		}
	}
	
	
	/**
	 * Fonction qui cr��e les tables dynamiques consolid�es
	 */
	function consolider($id_vue){
		global $dbh, $tab_val, $liste_tabfiltre, $pmb_set_time_limit;

		set_time_limit($pmb_set_time_limit);
		$q_ins='';
		$champ='';

		$rqt_tempo="SELECT * from logs_filtre_$id_vue";
		$res_tempo=mysql_query($rqt_tempo, $dbh);
		$n_total=mysql_num_rows($res_tempo);
				
		$tab_val =array();
		$liste_tabfiltre = array();
		$n=0;
		
		$s_ins=mysql_num_rows($res_tempo);
		if ($s_ins) {

			$this->show_progress_bar();
			$this->set_progress_text(' '.$this->nom_vue.' : ');
			$this->set_progress_percent(0);
			
			$print_format=new parse_format('consolidation.inc.php');
			$percent_conserve='0';
			
			while( ($ligne=mysql_fetch_array($res_tempo))){
				
				$percent=round(($n/$n_total)*100);
				if ($percent_conserve!=$percent) { // $percent%5==0 && 
					$this->set_progress_percent($percent);
					$percent_conserve=$percent;
				}
				$n++;
				
				$resultat =array();
				
				foreach ($this->cols_vue as $col) {			
					
					// si filtre, pour chaque ligne de log :
					if($col->filtre){
						$rqt_create = "CREATE TEMPORARY TABLE  filtre_".$ligne[0]."_".$col->id_col." (
							`id_log` int( 8 ) unsigned NOT NULL AUTO_INCREMENT ,
							`date_log` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
							`url_demandee` varchar( 255 ) NOT NULL default '',
							`url_referente` varchar( 255 ) NOT NULL default '',
							`get_log` blob NOT NULL ,
							`post_log` blob NOT NULL ,
							`num_session` varchar( 255 ) NOT NULL default '',
							`server_log` blob NOT NULL ,
							`empr_carac` blob NOT NULL ,
							`empr_doc` blob NOT NULL ,
							`empr_expl` blob NOT NULL ,
							`nb_result` blob NOT NULL ,
							 `gen_stat` blob NOT NULL ,
							PRIMARY KEY ( `id_log` )
						)";
						mysql_query($rqt_create, $dbh);
						$parser->cmd = $col->filtre ;
						$parser->environnement['tempo']="logs_filtre_$id_vue";
						$parser->environnement['num_ligne']=$ligne[0];
						$print_format->environnement['ligne']=$ligne;
						$val_filtre = $parser->exec_cmd_conso();
						$filtre_tab = $this->creer_filtre($col->filtre,$val_filtre,$id_vue,$ligne[0],"filtre_".$ligne[0]."_".$col->id_col);
					}	
					
					$print_format->cmd = $col->expression;
					$print_format->environnement['tempo']="logs_filtre_$id_vue";
					$print_format->environnement['num_ligne']=$ligne[0];
					$print_format->environnement['ligne']=$ligne;
					if($col->filtre)$print_format->environnement['filtre']= $filtre_tab;
					$resultat[$col->nom_col] = $print_format->exec_cmd_conso();
					
				}
				$values='';	
				if(!$champ) $champ = implode(',',array_keys($resultat));
				foreach($resultat as $valeur){
					$values .= ($values ? ',\''.addslashes($valeur).'\'' : '\''.addslashes($valeur).'\'');
				}
				if (strlen($q_ins)>=1000000) {
					mysql_query($q_ins, $dbh);	
					unset($q_ins);$q_ins='';
				}
				$q_ins.= ($q_ins)?',('.$values.')' : 'insert into  statopac_vue_'.addslashes($id_vue).' ('.$champ.') values ('.$values.')';
	
			}
			
			mysql_query($q_ins, $dbh);	
			unset($q_ins);
			//On supprime les tables de filtres
			foreach ($liste_tabfiltre as $key=>$val){
				mysql_query("DROP TABLE ".$val);
			}
			mysql_query("drop table logs_filtre_$id_vue",$dbh);
			
			//mise � jour des dates
			$q = "UPDATE statopac_vues SET date_consolidation=now(), date_debut_log='".$this->date_debut_log."', date_fin_log='".$this->date_fin_log."' WHERE id_vue='".addslashes($id_vue)."'";
			mysql_query($q,$dbh);
			if($this->flag) mysql_query("UPDATE statopac_vues_col SET maj_flag=0 WHERE num_vue='".addslashes($id_vue)."'");
		}
	}

	
	/**
	 * Fonction qui permet de cr�er un filtre de r�sultat par rapport � une valeur 
	 */
	function creer_filtre($filtre,$valeur_filtre,$id_vue,$ligne_repere,$table){
		global $dbh,$tab_val, $liste_tabfiltre;
		
		$table_filtre ="";
		foreach($tab_val as $key=>$val){
			if($filtre == $val['filtre']){ 
				if($valeur_filtre == $val['valeur_filtre']){
					$table_filtre = $val['table_filtre'];
					 mysql_query("DROP TABLE ".$table);
					break;
				} else {
					mysql_query("DROP TABLE ".$val['table_filtre']);
					$ind=array_search($val['table_filtre'],$liste_tabfiltre) ;
					if($ind != false)
						unset($liste_tabfiltre[$ind]);	
					unset($tab_val[$key]);
				}
			} 				
		}
		if(!$table_filtre){
			$liste_tabfiltre[] = $table;
			$rqt="SELECT * from logs_filtre_$id_vue";
			$res=mysql_query($rqt, $dbh);
		
			while($ligne_log=mysql_fetch_object($res)) {
			
				$format=new parse_format('consolidation.inc.php');	
				$format->environnement['tempo']="logs_filtre_$id_vue";
				$format->environnement['num_ligne']=$ligne_log->id_log;									
				$format->cmd = $filtre;
				$val_filtre_courant = $format->exec_cmd_conso();
				if($val_filtre_courant == $valeur_filtre){				
					$rqt="insert ignore into ".$table."  select * from logs_filtre_$id_vue where id_log='".addslashes($ligne_log->id_log)."'";
					mysql_query($rqt,$dbh);				
				}
			}				
		}
		if(!$valeur_filtre) 
			$valeur_filtre="no_value";
		if(!$table_filtre)
			$tab_val[] = array('valeur_filtre' => $valeur_filtre, 'filtre' => $filtre, 'table_filtre' => $table);	
			
		return ($table_filtre ? $table_filtre : $table);
	}
	
	function show_progress_bar(){
		print "<div class='row' style='text-align:center; width:80%; border: 1px solid #000000; padding: 4px;'>
			<div style='text-align:left; width:100%; height:16px;'>
				<img id='progress' src='images/jauge.png' style='width:1px; height:16px'/>
			</div>
			<div style='text-align:center'>
				<span id='progress_text'></span>&nbsp;
				<span id='progress_percent'></span>
			</div>
		</div>";
		flush();
	}
	
	function init_progress_bar() {
		print "<script>document.getElementById('progress').src='images/jauge.png'</script>";
		flush();
	}
	
	function set_progress_percent($percent) {
		print "<script>document.getElementById('progress').style.width='$percent%';
				document.getElementById('progress_percent').innerHTML='$percent%';
		</script>";
		flush();
	}
	
	function set_progress_text($text){
		global $charset;
		print "<script>document.getElementById('progress_text').innerHTML='".htmlentities($text,ENT_QUOTES,$charset)."';</script>";
		flush();
	}
}
?>