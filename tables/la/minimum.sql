-- +-------------------------------------------------+
-- � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
-- +-------------------------------------------------+
-- $Id: agneaux.sql,v 1.6 2012-08-20 19:49:58 gueluneau Exp $

-- MySQL dump 10.9
--
-- Host: localhost    Database: bibli
-- ------------------------------------------------------
-- Server version	4.1.9-max

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE="NO_AUTO_VALUE_ON_ZERO" */;

--
-- Table structure for table `actes`
--

DROP TABLE IF EXISTS `actes`;
CREATE TABLE `actes` (
  `id_acte` int(8) unsigned NOT NULL auto_increment,
  `date_acte` date NOT NULL default '0000-00-00',
  `numero` varchar(25) NOT NULL default '',
  `type_acte` int(3) unsigned NOT NULL default '0',
  `statut` int(3) unsigned NOT NULL default '0',
  `date_paiement` date NOT NULL default '0000-00-00',
  `num_paiement` varchar(255) NOT NULL default '',
  `num_entite` int(5) unsigned NOT NULL default '0',
  `num_fournisseur` int(5) unsigned NOT NULL default '0',
  `num_contact_livr` int(8) unsigned NOT NULL default '0',
  `num_contact_fact` int(8) unsigned NOT NULL default '0',
  `num_exercice` int(8) unsigned NOT NULL default '0',
  `commentaires` text NOT NULL,
  `reference` varchar(255) NOT NULL default '',
  `index_acte` text NOT NULL,
  `devise` varchar(25) NOT NULL default '',
  `commentaires_i` text NOT NULL,
  `date_valid` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id_acte`),
  KEY `num_fournisseur` (`num_fournisseur`),
  KEY `date` (`date_acte`),
  KEY `num_entite` (`num_entite`),
  KEY `numero` (`numero`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `actes`
--


/*!40000 ALTER TABLE `actes` DISABLE KEYS */;
LOCK TABLES `actes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `actes` ENABLE KEYS */;

--
-- Table structure for table `admin_session`
--

DROP TABLE IF EXISTS `admin_session`;
CREATE TABLE `admin_session` (
  `userid` int(10) unsigned NOT NULL default '0',
  `session` blob,
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin_session`
--


/*!40000 ALTER TABLE `admin_session` DISABLE KEYS */;
LOCK TABLES `admin_session` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `admin_session` ENABLE KEYS */;

--
-- Table structure for table `analysis`
--

DROP TABLE IF EXISTS `analysis`;
CREATE TABLE `analysis` (
  `analysis_bulletin` int(8) unsigned NOT NULL default '0',
  `analysis_notice` int(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`analysis_bulletin`,`analysis_notice`),
  KEY `analysis_notice` (`analysis_notice`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `analysis`
--


/*!40000 ALTER TABLE `analysis` DISABLE KEYS */;
LOCK TABLES `analysis` WRITE;
INSERT INTO `analysis` VALUES (1,21),(1,33),(1,35),(1,36),(1,37),(1,38),(1,39),(1,40),(1,41),(2,25),(2,26),(2,29),(2,30),(2,31),(2,32);
UNLOCK TABLES;
/*!40000 ALTER TABLE `analysis` ENABLE KEYS */;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
CREATE TABLE `audit` (
  `type_obj` int(1) NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(8) unsigned NOT NULL default '0',
  `user_name` varchar(20) NOT NULL default '',
  `type_modif` int(1) NOT NULL default '1',
  `quand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `audit`
--


/*!40000 ALTER TABLE `audit` DISABLE KEYS */;
LOCK TABLES `audit` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `audit` ENABLE KEYS */;

--
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
CREATE TABLE `authors` (
  `author_id` mediumint(8) unsigned NOT NULL auto_increment,
  `author_type` enum('70','71') NOT NULL default '70',
  `author_name` varchar(255) default NULL,
  `author_rejete` varchar(255) default NULL,
  `author_date` varchar(255) NOT NULL default '',
  `author_see` mediumint(8) unsigned NOT NULL default '0',
  `author_web` varchar(255) NOT NULL default '',
  `index_author` text,
  `author_comment` text,
  PRIMARY KEY  (`author_id`),
  KEY `author_see` (`author_see`),
  KEY `author_name` (`author_name`),
  KEY `author_rejete` (`author_rejete`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `authors`
--


/*!40000 ALTER TABLE `authors` DISABLE KEYS */;
LOCK TABLES `authors` WRITE;
INSERT INTO `authors` VALUES (1,'70','ຄະນະອັກສອນສາດ ມ/ຊ','','13102006',0,'',' ຄະນະອັກສອນສາດ ມ/ຊ ',''),(2,'70','ສີລາ ວິລະວົງ','','',0,'',' ສີລາ ວິລະວົງ ',''),(3,'70','ດຳດວນ ພົມດວງສີ','','13102006',0,'',' ດຳດວນ ພົມດວງສີ ',''),(4,'70','ປະທິບ ຊຸມພົນ','','13102006',0,'',' ປະທິບ ຊຸມພົນ ',''),(5,'71','ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ','','13102006',0,'',' ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ ',''),(6,'70','ສຸເນດ ໂພທິສານ','','13102006',0,'',' ສຸເນດ ໂພທິສານ ',''),(7,'70','ສູນກາງສະຫະພັນກຳມະບານລາວ','','13102006',0,'',' ສູນກາງສະຫະພັນກຳມະບານລາວ ',''),(8,'70','ສຸຈິດ ວົງເທບ','','13102006',0,'',' ສຸຈິດ ວົງເທບ ',''),(9,'70','ບຸນສີ ບູລົມ','','13102006',0,'',' ບຸນສີ ບູລົມ ',''),(10,'70','ບົວໄຂ ເພັງພະຈັນ','','13102006',0,'',' ບົວໄຂ ເພັງພະຈັນ ',''),(11,'70','ໂຄຈອນ ແກ້ວມະນີວົງ','','13102006',0,'',' ໂຄຈອນ ແກ້ວມະນີວົງ ',''),(12,'71','ກົມການເມືອງ ແລະ ການປົກຄອງ','','13102006',0,'',' ກົມການເມືອງ ແລະ ການປົກຄອງ ',''),(13,'71','ກົມປ່າໄມ້','','13102006',0,'',' ກົມປ່າໄມ້ ',''),(14,'70','ບຸນມີ ເທບສີເມືອງ','','13102006',0,'',' ບຸນມີ ເທບສີເມືອງ ',''),(15,'70','ພາກວິຊາພາສາລາວ-ວັນນະຄະດີ','','13102006',0,'',' ພາກວິຊາພາສາລາວ-ວັນນະຄະດີ ',''),(16,'70','ສຳລິດ ບົວສີສະຫວັດ','','13102006',0,'',' ສຳລິດ ບົວສີສະຫວັດ ',''),(17,'71','ອົງການອະນາໄມໂລກ','','13102006',0,'',' ອົງການອະນາໄມໂລກ ',''),(18,'71','ມູນນິທິຊາຊາກາວາ ເພື່ອສັນຕິພາບ','','13102006',0,'',' ມູນນິທິຊາຊາກາວາ ເພື່ອສັນຕິພາບ ',''),(19,'71','ຄະນະຈັດຕັງສູນກາງພັກ','','13102006',0,'',' ຄະນະຈັດຕັງສູນກາງພັກ ',''),(20,'70','ຄຳຜາຍ ບຸບຜາ','','13102006',0,'',' ຄຳຜາຍ ບຸບຜາ ',''),(21,'70','ທອງມາລີ ສຸລາດ','','13102006',0,'',' ທອງມາລີ ສຸລາດ ',''),(22,'70','ໃຊພອນ ສິທາລາດ','','13102006',0,'',' ໃຊພອນ ສິທາລາດ ','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `authors` ENABLE KEYS */;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE `avis` (
  `id_avis` mediumint(8) NOT NULL auto_increment,
  `num_empr` mediumint(8) NOT NULL default '0',
  `num_notice` mediumint(8) NOT NULL default '0',
  `note` int(3) default NULL,
  `sujet` text,
  `commentaire` text,
  `dateajout` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `valide` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_avis`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `avis`
--


/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
LOCK TABLES `avis` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;

--
-- Table structure for table `bannette_abon`
--

DROP TABLE IF EXISTS `bannette_abon`;
CREATE TABLE `bannette_abon` (
  `num_bannette` int(9) unsigned NOT NULL default '0',
  `num_empr` int(9) unsigned NOT NULL default '0',
  `actif` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`num_bannette`,`num_empr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannette_abon`
--


/*!40000 ALTER TABLE `bannette_abon` DISABLE KEYS */;
LOCK TABLES `bannette_abon` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `bannette_abon` ENABLE KEYS */;

--
-- Table structure for table `bannette_contenu`
--

DROP TABLE IF EXISTS `bannette_contenu`;
CREATE TABLE `bannette_contenu` (
  `num_bannette` int(9) unsigned NOT NULL default '0',
  `num_notice` int(9) unsigned NOT NULL default '0',
  `date_ajout` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`num_bannette`,`num_notice`),
  KEY `date_ajout` (`date_ajout`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannette_contenu`
--


/*!40000 ALTER TABLE `bannette_contenu` DISABLE KEYS */;
LOCK TABLES `bannette_contenu` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `bannette_contenu` ENABLE KEYS */;

--
-- Table structure for table `bannette_equation`
--

DROP TABLE IF EXISTS `bannette_equation`;
CREATE TABLE `bannette_equation` (
  `num_bannette` int(9) unsigned NOT NULL default '0',
  `num_equation` int(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`num_bannette`,`num_equation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannette_equation`
--


/*!40000 ALTER TABLE `bannette_equation` DISABLE KEYS */;
LOCK TABLES `bannette_equation` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `bannette_equation` ENABLE KEYS */;

--
-- Table structure for table `bannette_exports`
--

DROP TABLE IF EXISTS `bannette_exports`;
CREATE TABLE `bannette_exports` (
  `num_bannette` int(11) unsigned NOT NULL default '0',
  `export_format` int(3) NOT NULL default '0',
  `export_data` longblob NOT NULL,
  `export_nomfichier` varchar(255) default '',
  PRIMARY KEY  (`num_bannette`,`export_format`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannette_exports`
--


/*!40000 ALTER TABLE `bannette_exports` DISABLE KEYS */;
LOCK TABLES `bannette_exports` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `bannette_exports` ENABLE KEYS */;

--
-- Table structure for table `bannettes`
--

DROP TABLE IF EXISTS `bannettes`;
CREATE TABLE `bannettes` (
  `id_bannette` int(9) unsigned NOT NULL auto_increment,
  `num_classement` int(8) unsigned NOT NULL default '1',
  `nom_bannette` varchar(255) NOT NULL default '',
  `comment_gestion` varchar(255) NOT NULL default '',
  `comment_public` varchar(255) NOT NULL default '',
  `entete_mail` text NOT NULL,
  `date_last_remplissage` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_last_envoi` datetime NOT NULL default '0000-00-00 00:00:00',
  `proprio_bannette` int(9) unsigned NOT NULL default '0',
  `bannette_auto` int(1) unsigned NOT NULL default '0',
  `periodicite` int(3) unsigned NOT NULL default '7',
  `diffusion_email` int(1) unsigned NOT NULL default '0',
  `categorie_lecteurs` int(8) unsigned NOT NULL default '0',
  `nb_notices_diff` int(4) unsigned NOT NULL default '0',
  `num_panier` int(8) unsigned NOT NULL default '0',
  `limite_type` char(1) NOT NULL default '',
  `limite_nombre` int(6) NOT NULL default '0',
  PRIMARY KEY  (`id_bannette`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannettes`
--


/*!40000 ALTER TABLE `bannettes` DISABLE KEYS */;
LOCK TABLES `bannettes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `bannettes` ENABLE KEYS */;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
CREATE TABLE `budgets` (
  `id_budget` int(8) unsigned NOT NULL auto_increment,
  `num_entite` int(5) unsigned NOT NULL default '0',
  `num_exercice` int(8) unsigned NOT NULL default '0',
  `libelle` varchar(255) NOT NULL default '',
  `commentaires` text,
  `montant_global` float(8,2) unsigned NOT NULL default '0.00',
  `seuil_alerte` int(3) unsigned NOT NULL default '100',
  `statut` int(3) unsigned NOT NULL default '0',
  `type_budget` int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_budget`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `budgets`
--


/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
LOCK TABLES `budgets` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;

--
-- Table structure for table `bulletins`
--

DROP TABLE IF EXISTS `bulletins`;
CREATE TABLE `bulletins` (
  `bulletin_id` int(8) unsigned NOT NULL auto_increment,
  `bulletin_numero` varchar(255) NOT NULL default '',
  `bulletin_notice` int(8) NOT NULL default '0',
  `mention_date` varchar(50) NOT NULL default '',
  `date_date` date NOT NULL default '0000-00-00',
  `bulletin_titre` text,
  `index_titre` text,
  `bulletin_cb` varchar(30) default NULL,
  PRIMARY KEY  (`bulletin_id`),
  KEY `bulletin_numero` (`bulletin_numero`),
  KEY `bulletin_notice` (`bulletin_notice`),
  KEY `date_date` (`date_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bulletins`
--


/*!40000 ALTER TABLE `bulletins` DISABLE KEYS */;
LOCK TABLES `bulletins` WRITE;
INSERT INTO `bulletins` VALUES (1,'001',20,'ລາວອັບເດດ','2006-10-13','ຄວາມສາມັກຄີ ','  ',''),(2,'002',23,'ລາວກ້າວໜ້າ','2006-10-13','ດົນຕີພື້ນເມືອງຂອງລາວ','  ',''),(3,'003',24,'ເພື່ອທຳມະຊາດ','2006-10-13','ຮັກປ່າ','  ','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `bulletins` ENABLE KEYS */;

--
-- Table structure for table `caddie`
--

DROP TABLE IF EXISTS `caddie`;
CREATE TABLE `caddie` (
  `idcaddie` int(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `type` varchar(20) NOT NULL default 'NOTI',
  `comment` varchar(255) default NULL,
  `autorisations` mediumtext,
  PRIMARY KEY  (`idcaddie`),
  KEY `caddie_type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `caddie`
--


/*!40000 ALTER TABLE `caddie` DISABLE KEYS */;
LOCK TABLES `caddie` WRITE;
INSERT INTO `caddie` VALUES (1,'Notices pour exposition','NOTI','Placer dans ce panier les notices de l\'expo virtuelle','1 2'),(2,'Notices pour retour BDP','NOTI','Remplir ce panier � l\'issue du pointage des exemplaires en retour','1 2'),(3,'Exemplaires pour retour BDP','EXPL','Placer dans ce panier les exemplaires de documents � rendre � la BDP','1 2'),(4,'Notices en doublons sur titre','NOTI','Doublons sur le premier titre','1 2'),(8,'Exemple de panier d\'exemplaires','EXPL','','1 4 3 2'),(5,'Loire - Notices pour th�me du mois','NOTI','','1 4'),(6,'Loire - Bulletins contenant des articles pour expo mois','BULL','','1 4'),(7,'Cochon - notices pour exposition mois prochain','NOTI','','1');
UNLOCK TABLES;
/*!40000 ALTER TABLE `caddie` ENABLE KEYS */;

--
-- Table structure for table `caddie_content`
--

DROP TABLE IF EXISTS `caddie_content`;
CREATE TABLE `caddie_content` (
  `caddie_id` int(8) unsigned NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default '0',
  `content` blob,
  `blob_type` varchar(10) default NULL,
  `flag` varchar(10) default NULL,
  KEY `caddie_id` (`caddie_id`,`object_id`),
  KEY `object_id` (`object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `caddie_content`
--


/*!40000 ALTER TABLE `caddie_content` DISABLE KEYS */;
LOCK TABLES `caddie_content` WRITE;
INSERT INTO `caddie_content` VALUES (5,17,NULL,NULL,NULL),(5,19,NULL,NULL,NULL),(6,1,NULL,NULL,NULL),(6,2,NULL,NULL,NULL),(5,42,NULL,NULL,NULL),(5,0,'3370000451297','EXPL_CB',NULL),(5,46,NULL,NULL,NULL),(8,0,'10','EXPL_CB','1'),(7,44,NULL,NULL,NULL),(7,47,NULL,NULL,NULL),(5,41,NULL,NULL,NULL),(5,32,NULL,NULL,NULL),(5,49,NULL,NULL,NULL),(7,50,NULL,NULL,NULL),(7,48,NULL,NULL,NULL),(7,51,NULL,NULL,NULL),(5,25,NULL,NULL,NULL),(8,22,NULL,NULL,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `caddie_content` ENABLE KEYS */;

--
-- Table structure for table `caddie_procs`
--

DROP TABLE IF EXISTS `caddie_procs`;
CREATE TABLE `caddie_procs` (
  `idproc` smallint(5) unsigned NOT NULL auto_increment,
  `type` varchar(20) NOT NULL default 'SELECT',
  `name` varchar(255) NOT NULL default '',
  `requete` blob NOT NULL,
  `comment` tinytext NOT NULL,
  `autorisations` mediumtext,
  `parameters` text,
  PRIMARY KEY  (`idproc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `caddie_procs`
--


/*!40000 ALTER TABLE `caddie_procs` DISABLE KEYS */;
LOCK TABLES `caddie_procs` WRITE;
INSERT INTO `caddie_procs` VALUES (3,'SELECT','EXPL par section / propri�taire','select expl_id as object_id, \'EXPL\' as object_type from exemplaires where expl_section in (!!section!!) and expl_owner=!!proprio!!','S�lection d\'exemplaires par section par propri�taire','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"section\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Section]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idsection, section_libelle from docs_section order by section_libelle]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"proprio\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Propri�taire]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select idlender, lender_libelle from lenders order by lender_libelle</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(4,'SELECT','EXPL o� cote commence par','select expl_id as object_id, \'EXPL\' as object_type from exemplaires where expl_cote like \'!!comme_cote!!%\'','S�lection d\'exemplaire � partir du d�but de cote','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"comme_cote\" MANDATORY=\"no\">\n  <ALIAS><![CDATA[D�but de la cote]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>20</SIZE>\r\n <MAXSIZE>20</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(6,'ACTION','Retour BDP des exemplaires','update exemplaires set expl_statut=!!nouveau_statut!! where expl_id in (CADDIE(EXPL))','Permet de changer le statut des exemplaires d\'un panier','1 2 3','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"nouveau_statut\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[nouveau_statut]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>SELECT idstatut, statut_libelle FROM docs_statut</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(1,'SELECT','Notices par auteur','SELECT notice_id as object_id, \'NOTI\' as object_type FROM notices, authors, responsability WHERE author_name like \'%!!critere!!%\' AND author_id=responsability_author AND notice_id=responsability_notice\r\n','S�lection des notices dont le nom de l\'auteur contient certaines lettres','1 2 3','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"critere\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Caract�res contenus dans le nom]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>25</SIZE>\r\n <MAXSIZE>25</MAXSIZE>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(2,'SELECT','Notices en doublons','create TEMPORARY TABLE tmp SELECT tit1 FROM notices GROUP BY tit1 HAVING count(*)>1\r\nSELECT notice_id as object_id, \'NOTI\' as object_type FROM notices, tmp wHERE notices.tit1=tmp.tit1','S�lection des notices en doublons sur le premier titre','1 2 3',NULL),(7,'SELECT','Jamais pr�t�s','SELECT expl_id as object_id, \'EXPL\' as object_type, concat(\"LIVRE \",tit1) as Titre FROM notices join exemplaires on expl_notice=notice_id LEFT JOIN pret_archive ON arc_expl_notice = notice_id where arc_expl_id IS NULL AND expl_id IS NOT NULL UNION SELECT expl_id as object_id, \'EXPL\' as object_type, concat(\"PERIO \",tit1, \" Num�ro : \",bulletin_numero) as Titre FROM (bulletins INNER JOIN notices ON bulletins.bulletin_notice = notices.notice_id) INNER JOIN exemplaires on expl_bulletin=bulletin_id LEFT JOIN pret_archive ON expl_id = arc_expl_id WHERE pret_archive.arc_id Is Null','Ajoute dans un panier les exemplaires jamais pr�t�s','1 2',NULL),(8,'SELECT','S�lection d\'exemplaires par statut','select expl_id as object_id, \'EXPL\' as object_type from exemplaires where expl_statut in (!!statut!!)','','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"statut\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[statut]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idstatut, statut_libelle from docs_statut]]></QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(9,'SELECT','S�lection d\'exemplaires par localisation, section, statut, propri�taire','select expl_id as object_id, \'EXPL\' as object_type from exemplaires where expl_section in (!!section!!) and expl_location in (!!location!!) and expl_statut in (!!statut!!) and expl_owner=!!proprio!!  ','','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"section\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Section]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idsection, section_libelle from docs_section order by 2]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"location\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Localisation]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idlocation, location_libelle from docs_location order by 2]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"statut\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Statut]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idstatut, statut_libelle from docs_statut order by 2]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"proprio\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Propri�taire]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idlender, lender_libelle from lenders order by 2]]></QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>');
UNLOCK TABLES;
/*!40000 ALTER TABLE `caddie_procs` ENABLE KEYS */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `num_noeud` int(9) unsigned NOT NULL default '0',
  `langue` varchar(5) NOT NULL default 'fr_FR',
  `libelle_categorie` text NOT NULL,
  `note_application` text NOT NULL,
  `comment_public` text NOT NULL,
  `comment_voir` text NOT NULL,
  `index_categorie` text NOT NULL,
  PRIMARY KEY  (`num_noeud`,`langue`),
  KEY `categ_langue` (`langue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--


/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
LOCK TABLES `categories` WRITE;
INSERT INTO `categories` VALUES (2539,'la_LA','ປ່າໄມ້','ປ່າໄມ້','','','  '),(2539,'fr_FR','ປ່າໄມ້','ປ່າໄມ້','','','  '),(2538,'la_LA','ພູມສາດ ຕ່າງປະເທດ','ພູມສາດ ຕ່າງປະເທດ','','','  '),(2538,'fr_FR','ພູມສາດ ຕ່າງປະເທດ','ພູມສາດ ຕ່າງປະເທດ','','','  '),(2537,'la_LA','ພູມສາດ ລາວ','ພູມສາດ ລາວ','','','  '),(2537,'fr_FR','ພູມສາດ ລາວ','ພູມສາດ ລາວ','','','  '),(2536,'la_LA','ພູມສາດ','ພູມສາດ','','','  '),(2536,'fr_FR','ພູມສາດ','ພູມສາດ','','','  '),(2535,'la_LA','ປະຫວັດສາດ ຕ່າງປະເທດ','ປະຫວັດສາດ ຕ່າງປະເທດ','','','  '),(2535,'fr_FR','ປະຫວັດສາດ ຕ່າງປະເທດ','ປະຫວັດສາດ ຕ່າງປະເທດ','','','  '),(2534,'la_LA','ປະຫວັດສາດ ລາວ','ປະຫວັດສາດ ລາວ','','','  '),(2534,'fr_FR','ປະຫວັດສາດ ລາວ','ປະຫວັດສາດ ລາວ','','','  '),(2533,'la_LA','ປະຫວັດສາດ','ປະຫວັດສາດ','','','  '),(2533,'fr_FR','ປະຫວັດສາດ','ປະຫວັດສາດ','','','  '),(2532,'la_LA','ດ້ານຄອມພີວເຕີ້','ດ້ານຄອມພີວເຕີ້','','','  '),(2532,'fr_FR','ດ້ານຄອມພີວເຕີ້','ດ້ານຄອມພີວເຕີ້','','','  '),(2531,'la_LA','ດ້ານການແພດ','ດ້ານການແພດ','','','  '),(2531,'fr_FR','ດ້ານການແພດ','ດ້ານການແພດ','','','  '),(2520,'fr_FR','ວັນນະຄະດີ','ວັນນະຄະດີ','','','  '),(2520,'la_LA','ວັນນະຄະດີ','','','','  '),(2521,'fr_FR','ວິທະຍາສາດ','ວິທະຍາສາດ','','','  '),(2521,'la_LA','ວິທະຍາສາດ','','','','  '),(2522,'fr_FR','ທຳມະຊາດ','ທຳມະຊາດ','','','  '),(2522,'la_LA','ທຳມະຊາດ','','','','  '),(2523,'fr_FR','ແຮ່ທາດຕ່າງໆ','','','','  '),(2523,'la_LA','ແຮ່ທາດຕ່າງໆ','','','','  '),(2524,'fr_FR','ວັນນະຄະດີລາວ','ວັນນະຄະດີລາວ','','','  '),(2524,'la_LA','ວັນນະຄະດີລາວ','','','','  '),(2525,'fr_FR','ວັນນະຄະດີຕ່າງປະເທດ','ວັນນະຄະດີຕ່າງປະເທດ','','','  '),(2525,'la_LA','ວັນນະຄະດີຕ່າງປະເທດ','','','','  '),(2526,'fr_FR','ກົດໜາຍ','ກົດໜາຍ','','','  '),(2526,'la_LA','ກົດໜາຍ','','','','  '),(2527,'fr_FR','ກົດໜາຍ ອາຍາ','ກົດໜາຍ ອາຍາ','','','  '),(2527,'la_LA','ກົດໜາຍ ອາຍາ','','','','  '),(2528,'fr_FR','ກົດໜາຍ ແພ່ງ','ກົດໜາຍ ແພ່ງ','','','  '),(2528,'la_LA','ກົດໜາຍ ແພ່ງ','ກົດໜາຍ ແພ່ງ','','','  '),(2529,'fr_FR','ກົດໜາຍ ລາວ','ກົດໜາຍ ລາວ','','','  '),(2529,'la_LA','ກົດໜາຍ ລາວ','','','','  '),(2530,'fr_FR','ກົດໜາຍ ຕ່າງປະເທດ','ກົດໜາຍ ຕ່າງປະເທດ','','','  '),(2530,'la_LA','ກົດໜາຍ ຕ່າງປະເທດ','ກົດໜາຍ ຕ່າງປະເທດ','','','  ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

--
-- Table structure for table `classements`
--

DROP TABLE IF EXISTS `classements`;
CREATE TABLE `classements` (
  `id_classement` int(8) unsigned NOT NULL auto_increment,
  `type_classement` char(3) NOT NULL default 'BAN',
  `nom_classement` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_classement`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `classements`
--


/*!40000 ALTER TABLE `classements` DISABLE KEYS */;
LOCK TABLES `classements` WRITE;
INSERT INTO `classements` VALUES (1,'','_NON CLASSE_'),(2,'BAN','ທົດລອງ'),(3,'EQU','ເຄມີ'),(4,'EQU','ຟີຊິກ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `classements` ENABLE KEYS */;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
CREATE TABLE `collections` (
  `collection_id` mediumint(8) unsigned NOT NULL auto_increment,
  `collection_name` varchar(255) NOT NULL default '',
  `collection_parent` mediumint(8) unsigned NOT NULL default '0',
  `collection_issn` varchar(12) NOT NULL default '',
  `index_coll` text,
  PRIMARY KEY  (`collection_id`),
  KEY `collection_name` (`collection_name`),
  KEY `collection_parent` (`collection_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `collections`
--


/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
LOCK TABLES `collections` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
CREATE TABLE `comptes` (
  `id_compte` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `type_compte_id` int(10) unsigned NOT NULL default '0',
  `solde` decimal(16,2) default '0.00',
  `prepay_mnt` decimal(16,2) NOT NULL default '0.00',
  `proprio_id` int(10) unsigned NOT NULL default '0',
  `droits` text NOT NULL,
  PRIMARY KEY  (`id_compte`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `comptes`
--


/*!40000 ALTER TABLE `comptes` DISABLE KEYS */;
LOCK TABLES `comptes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `comptes` ENABLE KEYS */;

--
-- Table structure for table `coordonnees`
--

DROP TABLE IF EXISTS `coordonnees`;
CREATE TABLE `coordonnees` (
  `id_contact` int(8) unsigned NOT NULL auto_increment,
  `type_coord` int(1) unsigned NOT NULL default '0',
  `num_entite` int(5) unsigned NOT NULL default '0',
  `libelle` varchar(255) NOT NULL default '',
  `contact` varchar(255) NOT NULL default '',
  `adr1` varchar(255) NOT NULL default '',
  `adr2` varchar(255) NOT NULL default '',
  `cp` varchar(15) NOT NULL default '',
  `ville` varchar(100) NOT NULL default '',
  `etat` varchar(100) NOT NULL default '',
  `pays` varchar(100) NOT NULL default '',
  `tel1` varchar(100) NOT NULL default '',
  `tel2` varchar(100) NOT NULL default '',
  `fax` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `commentaires` text,
  PRIMARY KEY  (`id_contact`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `coordonnees`
--


/*!40000 ALTER TABLE `coordonnees` DISABLE KEYS */;
LOCK TABLES `coordonnees` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `coordonnees` ENABLE KEYS */;

--
-- Table structure for table `docs_codestat`
--

DROP TABLE IF EXISTS `docs_codestat`;
CREATE TABLE `docs_codestat` (
  `idcode` smallint(5) unsigned NOT NULL auto_increment,
  `codestat_libelle` varchar(255) default NULL,
  `statisdoc_codage_import` char(2) NOT NULL default '',
  `statisdoc_owner` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idcode`),
  KEY `statisdoc_owner` (`statisdoc_owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docs_codestat`
--


/*!40000 ALTER TABLE `docs_codestat` DISABLE KEYS */;
LOCK TABLES `docs_codestat` WRITE;
INSERT INTO `docs_codestat` VALUES (10,'ບໍ່ເຈາະຈົງ','u',0),(11,'ໄວໜຸ່ມ','j',0),(12,'ຜູ້ໃຫ່ຽ','a',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `docs_codestat` ENABLE KEYS */;

--
-- Table structure for table `docs_location`
--

DROP TABLE IF EXISTS `docs_location`;
CREATE TABLE `docs_location` (
  `idlocation` smallint(5) unsigned NOT NULL auto_increment,
  `location_libelle` varchar(255) default NULL,
  `locdoc_codage_import` varchar(255) NOT NULL default '',
  `locdoc_owner` mediumint(8) unsigned NOT NULL default '0',
  `location_pic` varchar(255) NOT NULL default '',
  `location_visible_opac` tinyint(1) NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `adr1` varchar(255) NOT NULL default '',
  `adr2` varchar(255) NOT NULL default '',
  `cp` varchar(50) NOT NULL default '',
  `town` varchar(100) NOT NULL default '',
  `state` varchar(100) NOT NULL default '',
  `country` varchar(100) NOT NULL default '',
  `phone` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `website` varchar(100) NOT NULL default '',
  `logo` varchar(255) NOT NULL default '',
  `logosmall` varchar(255) NOT NULL default '',
  `commentaire` text NOT NULL,
  PRIMARY KEY  (`idlocation`),
  KEY `locdoc_owner` (`locdoc_owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docs_location`
--


/*!40000 ALTER TABLE `docs_location` DISABLE KEYS */;
LOCK TABLES `docs_location` WRITE;
INSERT INTO `docs_location` VALUES (1,'ຫໍສະຫມຸດເເຫ່ງຊາດ','',2,'images/site/bib_princ.jpg',1,'ຫໍສະຫມຸດເເຫ່ງຊາດ','ຖະໜົນເສດຖາທິລາດ','ບ້ານຊຽງຍືນ','ຕູ້ ປ.ນ 122','ວຽງຈັນ','','ສ.ປ.ປ.ລາວ','+85621 251 405','bnl@laosky.com','http://www.bnlaos.org/','logo_default.jpg','logo_default_small.jpg',''),(2,'ສະຫງວນໄວ້','',2,'',0,'ຫໍສະຫມຸດທົດລອງຂອງ​PMB','','','','','','','','pmb@sigb.net','http://www.sigb.net','logo_default.jpg','logo_default_small.jpg',''),(7,'ຫໍສະຫມຸດເຄື່ອນທີ່','',2,'images/site/bibliobus.jpg',1,'ຫໍສະຫມຸດທົດລອງຂອງ PMB','','','72500','','','','','pmb@sigb.net','http://www.sigb.net','logo_default.jpg','logo_default_small.jpg','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `docs_location` ENABLE KEYS */;

--
-- Table structure for table `docs_section`
--

DROP TABLE IF EXISTS `docs_section`;
CREATE TABLE `docs_section` (
  `idsection` smallint(5) unsigned NOT NULL auto_increment,
  `section_libelle` varchar(255) default NULL,
  `sdoc_codage_import` varchar(255) NOT NULL default '',
  `sdoc_owner` mediumint(8) unsigned NOT NULL default '0',
  `section_pic` varchar(255) NOT NULL default '',
  `section_visible_opac` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`idsection`),
  KEY `sdoc_owner` (`sdoc_owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docs_section`
--


/*!40000 ALTER TABLE `docs_section` DISABLE KEYS */;
LOCK TABLES `docs_section` WRITE;
INSERT INTO `docs_section` VALUES (10,'ເອກະສານ','',2,'images/site/documentaire.jpg',1),(11,'ເອກະສານສຳລັບເດັກນ້ອຍ','',2,'images/site/documentaire.jpg',1),(12,'ນະວະນິຍາຍເດັກ','',2,'images/site/enfants.jpg',1),(13,'ນະວະນິຍາຍ','',2,'images/site/sec3.jpg',1),(16,'ປະຫວັດສາດ','',2,'images/site/sec1.jpg',1),(17,'ນະວະນິຍາຍກ່ຽວກັບຕຳຫຼວດ','',2,'images/site/enfants.jpg',1),(18,'ນະວະນິຍາຍຕ່າງປະເທດ','',2,'images/site/histoire.jpg',1),(20,'ເອກະສານສຳລັບໄວໝູ່ມ','',2,'images/site/sec3.jpg',1),(21,'ປື້ມຮູບເດັກນ້ອຍ','',2,'images/site/sec1.jpg',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `docs_section` ENABLE KEYS */;

--
-- Table structure for table `docs_statut`
--

DROP TABLE IF EXISTS `docs_statut`;
CREATE TABLE `docs_statut` (
  `idstatut` smallint(5) unsigned NOT NULL auto_increment,
  `statut_libelle` varchar(255) default NULL,
  `pret_flag` tinyint(4) NOT NULL default '1',
  `statusdoc_codage_import` char(2) NOT NULL default '',
  `statusdoc_owner` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idstatut`),
  KEY `statusdoc_owner` (`statusdoc_owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docs_statut`
--


/*!40000 ALTER TABLE `docs_statut` DISABLE KEYS */;
LOCK TABLES `docs_statut` WRITE;
INSERT INTO `docs_statut` VALUES (1,'ຢູ່ໃນສະພາບດີ',1,'',0),(2,'ກຳລັງນຳເຂົ້າ',0,'',0),(11,'ໃຊ້ການບໍ່ໄດ້',0,'',0),(12,'ສູນຫາຍ',0,'',0),(13,'ໃຫ້ອ່ານເບິ່ງຢູ່ຫໍສະໝຸດເທົ່ານັ້ນ',0,'',0),(14,'ຢູ່ໃນສາງ',0,'',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `docs_statut` ENABLE KEYS */;

--
-- Table structure for table `docs_type`
--

DROP TABLE IF EXISTS `docs_type`;
CREATE TABLE `docs_type` (
  `idtyp_doc` tinyint(3) unsigned NOT NULL auto_increment,
  `tdoc_libelle` varchar(255) default NULL,
  `duree_pret` smallint(6) NOT NULL default '31',
  `duree_resa` int(6) unsigned NOT NULL default '15',
  `tdoc_owner` mediumint(8) unsigned NOT NULL default '0',
  `tdoc_codage_import` varchar(255) NOT NULL default '',
  `tarif_pret` decimal(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (`idtyp_doc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docs_type`
--


/*!40000 ALTER TABLE `docs_type` DISABLE KEYS */;
LOCK TABLES `docs_type` WRITE;
INSERT INTO `docs_type` VALUES (1,'ປື້ມ',14,15,2,'','0.00'),(12,'ກະແຊັດວີດີໂອ',14,15,2,'','0.00'),(13,'ຊີດີຕ່າງໆ',14,15,2,'','0.00'),(14,'ວີຊີດີ',5,15,2,'','0.00'),(15,'ງານສິນລະປະ',5,15,2,'','0.00'),(16,'ບັດ ແລະ ແຜນທີ່',31,15,2,'','0.00'),(17,'ຊີດີຣ໋ອມ',10,5,2,'','0.00'),(18,'ວາລະສານ',8,5,0,'','0.00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `docs_type` ENABLE KEYS */;

--
-- Table structure for table `docsloc_section`
--

DROP TABLE IF EXISTS `docsloc_section`;
CREATE TABLE `docsloc_section` (
  `num_section` int(5) unsigned NOT NULL default '0',
  `num_location` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`num_section`,`num_location`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `docsloc_section`
--


/*!40000 ALTER TABLE `docsloc_section` DISABLE KEYS */;
LOCK TABLES `docsloc_section` WRITE;
INSERT INTO `docsloc_section` VALUES (10,1),(10,7),(11,1),(11,7),(12,1),(12,7),(13,1),(13,7),(16,1),(16,7),(17,1),(17,7),(18,1),(18,7),(19,1),(19,7),(20,1),(20,7),(21,1),(21,7),(23,1),(23,7),(24,1),(24,7),(25,1),(25,7),(26,1),(26,7);
UNLOCK TABLES;
/*!40000 ALTER TABLE `docsloc_section` ENABLE KEYS */;

--
-- Table structure for table `empr`
--

DROP TABLE IF EXISTS `empr`;
CREATE TABLE `empr` (
  `id_empr` smallint(6) NOT NULL auto_increment,
  `empr_cb` varchar(255) default NULL,
  `empr_nom` varchar(255) NOT NULL default '',
  `empr_prenom` varchar(255) NOT NULL default '',
  `empr_adr1` varchar(255) NOT NULL default '',
  `empr_adr2` varchar(255) NOT NULL default '',
  `empr_cp` varchar(10) NOT NULL default '',
  `empr_ville` varchar(255) NOT NULL default '',
  `empr_pays` varchar(255) NOT NULL default '',
  `empr_mail` varchar(50) NOT NULL default '',
  `empr_tel1` varchar(255) NOT NULL default '',
  `empr_tel2` varchar(255) NOT NULL default '',
  `empr_prof` varchar(255) NOT NULL default '',
  `empr_year` int(4) unsigned NOT NULL default '0',
  `empr_categ` smallint(5) unsigned NOT NULL default '0',
  `empr_codestat` smallint(5) unsigned NOT NULL default '0',
  `empr_creation` date NOT NULL default '0000-00-00',
  `empr_modif` date NOT NULL default '0000-00-00',
  `empr_sexe` tinyint(3) unsigned NOT NULL default '0',
  `empr_login` varchar(255) NOT NULL default '',
  `empr_password` varchar(10) NOT NULL default '',
  `empr_date_adhesion` date default NULL,
  `empr_date_expiration` date default NULL,
  `empr_msg` tinytext,
  `empr_lang` varchar(10) NOT NULL default 'fr_FR',
  `empr_ldap` tinyint(1) unsigned default '0',
  `type_abt` int(1) NOT NULL default '0',
  `last_loan_date` date default NULL,
  `empr_location` int(6) unsigned NOT NULL default '1',
  `date_fin_blocage` date default NULL,
  PRIMARY KEY  (`id_empr`),
  UNIQUE KEY `empr_cb` (`empr_cb`),
  KEY `empr_nom` (`empr_nom`),
  KEY `empr_date_adhesion` (`empr_date_adhesion`),
  KEY `empr_date_expiration` (`empr_date_expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr`
--


/*!40000 ALTER TABLE `empr` DISABLE KEYS */;
LOCK TABLES `empr` WRITE;
INSERT INTO `empr` VALUES (1,'1','ວິຊ່ຽນ','ແກ້ວມະນີ','ບ້ານນາແຮ່ 04/49','ເມືອງສີໂຄດ ແຂວງກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','keomany2002@hotmailo.com','020 7 74 12 35','','ນັກຂຽນໂປແກມ',13081981,10,7,'2006-10-13','2006-10-13',1,'       1','13/08/1981','2006-10-13','2007-10-13','ເປັນນັກອ່ານປະຈຳ ທີ່ເຂົ້າມາຫໍສະໝຸດ ຢ່າງໜ້ອຍ 1 ຄັ້ງ/ອາທິດ','la_LA',0,0,'2006-10-16',1,NULL),(3,'3','ວິໄລທອງ','ວົງທະສອນ','ບ້ານທາດຂາວ','ທະໝົນທ່າເດື່ອ ເມືອງສີສະຕະນາດ ກຳແພງນະຄອນ','856','ສີສະຕະນາດ','','vthasone@hotmail.com','020 59 19 571','','ນັກຂຽນໂປແກມ',2101978,10,7,'2006-10-13','2006-10-13',1,'        12','02101978','2006-06-13','2007-06-13','ຊະມາຊິກ ທີ່ມາເປັນປະຈຳ','la_LA',0,0,'2006-10-14',1,NULL),(2,'2','ຈິນນະລາດ','ຄຳສິນ','ບ້ານດົງນາໂຊກ','ເມືອງສີໂຄດ ແຂວງກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','touy_chinnalath@yahoo.com','020 7 60 78 07','','ນັກຂຽນໂປແກມ',25111981,10,7,'2006-10-13','2006-10-13',1,'         1','25 11 1981','2005-07-13','2007-07-13','ຊະມາຊິກເກົ່າ','la_LA',0,0,'2006-10-13',1,NULL),(4,'4','ໄຊຍະສຸກ','ທາລົມ','ບ້ານອາກາດ','ທະໝົນຫຼວງພະບາງ ເມືອງສີໂຄດ ກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','','020 5123456','','ອອກແບບ',2071983,10,7,'2006-10-13','2006-10-13',2,'        1','02071983','2005-11-10','2006-11-10','ຊະມາຊິກປະຈຳ','fr_FR',0,0,NULL,1,NULL),(5,'5','ຂັນທະວີວັນ','ສົນໄລ','ບ້ານດອນກອຍ','ກຳແພງນະຄອນ','002','ນາຊາຍທອງ','ລາວ','ksonlay@yahoo.com','020 78 73 573','','ນັກຂຽນໂປແກມ',5031980,10,4,'2006-10-13','2006-10-13',1,'           1','05031980','2005-10-01','2007-10-01','ຊະມາຊິກປະຈຳ','fr_FR',0,0,'2006-10-13',1,NULL),(6,'6','ແສງຈັນດາວົງ','ໂພໄຊສີ','ບ້ານໜອງດ້ວງ','ເມືອງສີໂຄດ ແຂວງກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','abrun@hotmail.com','020 78 33 876','','ນັກຂຽນໂປແກມ',7121981,10,4,'2006-10-13','2006-10-13',1,'','07121981','2006-10-13','2007-10-13','ຊະມາຊິກປະຈຳ','la_LA',0,0,'2006-10-13',1,NULL),(7,'11586-11592','ກິດຕິພັນ','ຄຳຫຼ້າ','ບ້ານທົ່ງປົ່ງ','ເມືອງສີໂຄດ ແຂວງກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','ktpkhamla@wfp.org','020 55 21 293','','ນັກຂຽນໂປແກມ',19061980,10,7,'2006-10-13','2006-11-08',1,'         12','19061980','2005-12-07','2006-12-07','ຊະມາຊິກປະຈຳ','la_LA',0,0,'2006-10-13',1,NULL),(8,'8','ຈັນທະລັງສີ','ສຸລິວົງ','ບ້ານຊະພັງໜໍ້','ເມືອງໄຊທານີ  ກຳແພງນະຄອນ','001','ໄຊທານີ','ລາວ','soulivongch@ifmt.org','020 57 06 549','','ຜູ້ຄູມເຄືອຂ່າຍ ຄອມພີວເຕີ້',26031978,10,7,'2006-10-13','2006-10-13',1,'           12','26031978','2006-10-13','2007-10-13','ນັກອ່ານປະຳ','la_LA',0,0,NULL,1,NULL),(9,'9','ພົມມະວົງ','ຈັນທະລາ','ບ້ານໜອງແຕ່ງ','ເມືອງສີໂຄດ ແຂວງກຳແພງນະຄອນ','856','ສີໂຄດ','ລາວ','','020 71 32  567','','ນັກຂ່າວ',5071981,10,4,'2006-10-13','2006-10-13',1,'         123','05071981','2006-10-13','2007-10-13','','la_LA',0,0,'2006-11-08',1,NULL),(10,'10','ປານເພັດ','ທອງອິນ','ບ້ານສາຍລົມ','ເມືອງໄຊທານີ  ກຳແພງນະຄອນ','001','ໄຊທານີ','','','020 77 84 612','','ນັກສິກສາ',15081987,8,4,'2006-10-13','2006-10-13',2,'        123','15081987','2006-10-13','2007-10-13','ນັກສິກສາມະຫາວິທະຍາໄລແຫ່ງຊາດ','la_LA',0,0,'2006-11-08',1,NULL),(11,'11','ທອງດຳ','ດຳ','ບ້ານຊະພັງໜໍ້','','003','ສັງທອງ','','','','','ພະນັກງານ',13071985,12,4,'2006-10-16','2006-10-16',1,'      1','13071985','2006-10-02','2007-10-02','','fr_FR',0,0,NULL,1,NULL),(12,'12','ທອງແດງ','ມະນີວັນ','ບ້ານ ວັດໄຕນ້ອຍ','ກຳແພງນະຄອນ','235','ສີໂຄດ','ລາວ','','020 7 829859','','ພະນັກງານ',19121975,10,7,'2006-10-23','2006-10-23',1,'       12','19121975','2006-08-08','2007-08-08','','la_LA',0,0,NULL,1,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr` ENABLE KEYS */;

--
-- Table structure for table `empr_categ`
--

DROP TABLE IF EXISTS `empr_categ`;
CREATE TABLE `empr_categ` (
  `id_categ_empr` smallint(5) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `duree_adhesion` int(10) unsigned default '365',
  `tarif_abt` decimal(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id_categ_empr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_categ`
--


/*!40000 ALTER TABLE `empr_categ` DISABLE KEYS */;
LOCK TABLES `empr_categ` WRITE;
INSERT INTO `empr_categ` VALUES (8,'ຜູ້ໃຫ່ຍ',365,'0.00'),(9,'ເດັກນ້ອຍ',365,'0.00'),(10,'ພະນັກງານ',365,'0.00'),(11,'ພະນັກງານບຳນານ',365,'0.00'),(12,'ຄົນຫວ່າງງານ',365,'0.00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_categ` ENABLE KEYS */;

--
-- Table structure for table `empr_codestat`
--

DROP TABLE IF EXISTS `empr_codestat`;
CREATE TABLE `empr_codestat` (
  `idcode` smallint(5) unsigned NOT NULL auto_increment,
  `libelle` varchar(50) NOT NULL default 'DEFAULT',
  PRIMARY KEY  (`idcode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_codestat`
--


/*!40000 ALTER TABLE `empr_codestat` DISABLE KEYS */;
LOCK TABLES `empr_codestat` WRITE;
INSERT INTO `empr_codestat` VALUES (4,'ພາກວິຊາ'),(6,'ອາຊີ'),(7,'ລາວ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_codestat` ENABLE KEYS */;

--
-- Table structure for table `empr_custom`
--

DROP TABLE IF EXISTS `empr_custom`;
CREATE TABLE `empr_custom` (
  `idchamp` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `titre` varchar(255) default NULL,
  `type` varchar(10) NOT NULL default 'text',
  `datatype` varchar(10) NOT NULL default '',
  `options` text,
  `multiple` int(11) NOT NULL default '0',
  `obligatoire` int(11) NOT NULL default '0',
  `ordre` int(11) default NULL,
  PRIMARY KEY  (`idchamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_custom`
--


/*!40000 ALTER TABLE `empr_custom` DISABLE KEYS */;
LOCK TABLES `empr_custom` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_custom` ENABLE KEYS */;

--
-- Table structure for table `empr_custom_lists`
--

DROP TABLE IF EXISTS `empr_custom_lists`;
CREATE TABLE `empr_custom_lists` (
  `empr_custom_champ` int(10) unsigned NOT NULL default '0',
  `empr_custom_list_value` varchar(255) default NULL,
  `empr_custom_list_lib` varchar(255) default NULL,
  `ordre` int(11) default NULL,
  KEY `empr_custom_champ` (`empr_custom_champ`),
  KEY `champ_list_value` (`empr_custom_champ`,`empr_custom_list_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_custom_lists`
--


/*!40000 ALTER TABLE `empr_custom_lists` DISABLE KEYS */;
LOCK TABLES `empr_custom_lists` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_custom_lists` ENABLE KEYS */;

--
-- Table structure for table `empr_custom_values`
--

DROP TABLE IF EXISTS `empr_custom_values`;
CREATE TABLE `empr_custom_values` (
  `empr_custom_champ` int(10) unsigned NOT NULL default '0',
  `empr_custom_origine` int(10) unsigned NOT NULL default '0',
  `empr_custom_small_text` varchar(255) default NULL,
  `empr_custom_text` text,
  `empr_custom_integer` int(11) default NULL,
  `empr_custom_date` date default NULL,
  `empr_custom_float` float default NULL,
  KEY `empr_custom_champ` (`empr_custom_champ`),
  KEY `empr_custom_origine` (`empr_custom_origine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_custom_values`
--


/*!40000 ALTER TABLE `empr_custom_values` DISABLE KEYS */;
LOCK TABLES `empr_custom_values` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_custom_values` ENABLE KEYS */;

--
-- Table structure for table `empr_groupe`
--

DROP TABLE IF EXISTS `empr_groupe`;
CREATE TABLE `empr_groupe` (
  `empr_id` int(6) unsigned NOT NULL default '0',
  `groupe_id` int(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (`empr_id`,`groupe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `empr_groupe`
--


/*!40000 ALTER TABLE `empr_groupe` DISABLE KEYS */;
LOCK TABLES `empr_groupe` WRITE;
INSERT INTO `empr_groupe` VALUES (1,1),(1,2),(2,1),(2,2),(3,1),(4,1),(5,1),(10,0),(11,0),(12,0),(12,3);
UNLOCK TABLES;
/*!40000 ALTER TABLE `empr_groupe` ENABLE KEYS */;

--
-- Table structure for table `entites`
--

DROP TABLE IF EXISTS `entites`;
CREATE TABLE `entites` (
  `id_entite` int(5) unsigned NOT NULL auto_increment,
  `type_entite` int(3) unsigned NOT NULL default '0',
  `num_bibli` int(5) unsigned NOT NULL default '0',
  `raison_sociale` varchar(255) NOT NULL default '',
  `commentaires` text,
  `siret` varchar(25) NOT NULL default '',
  `naf` varchar(5) NOT NULL default '',
  `rcs` varchar(25) NOT NULL default '',
  `tva` varchar(25) NOT NULL default '',
  `num_cp_client` varchar(25) NOT NULL default '',
  `num_cp_compta` varchar(255) NOT NULL default '',
  `site_web` varchar(100) NOT NULL default '',
  `logo` varchar(255) NOT NULL default '',
  `autorisations` mediumtext NOT NULL,
  `num_frais` int(8) unsigned NOT NULL default '0',
  `num_paiement` int(8) unsigned NOT NULL default '0',
  `index_entite` text NOT NULL,
  PRIMARY KEY  (`id_entite`),
  KEY `raison_sociale` (`raison_sociale`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `entites`
--


/*!40000 ALTER TABLE `entites` DISABLE KEYS */;
LOCK TABLES `entites` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `entites` ENABLE KEYS */;

--
-- Table structure for table `equations`
--

DROP TABLE IF EXISTS `equations`;
CREATE TABLE `equations` (
  `id_equation` int(9) unsigned NOT NULL auto_increment,
  `num_classement` int(8) unsigned NOT NULL default '1',
  `nom_equation` varchar(255) NOT NULL default '',
  `comment_equation` varchar(255) NOT NULL default '',
  `requete` blob NOT NULL,
  `proprio_equation` int(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_equation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `equations`
--


/*!40000 ALTER TABLE `equations` DISABLE KEYS */;
LOCK TABLES `equations` WRITE;
INSERT INTO `equations` VALUES (1,1,'keomany','ທົດສອບເບິ່ງ','a:2:{s:6:\"SEARCH\";a:1:{i:0;s:3:\"f_1\";}i:0;a:5:{s:6:\"SEARCH\";s:3:\"f_1\";s:2:\"OP\";s:9:\"STARTWITH\";s:5:\"FIELD\";a:1:{i:0;s:1:\"a\";}s:5:\"INTER\";N;s:8:\"FIELDVAR\";N;}}',0),(2,1,'keo','tester','a:2:{s:6:\"SEARCH\";a:1:{i:0;s:3:\"f_2\";}i:0;a:5:{s:6:\"SEARCH\";s:3:\"f_2\";s:2:\"OP\";s:9:\"STARTWITH\";s:5:\"FIELD\";a:1:{i:0;s:1:\"b\";}s:5:\"INTER\";N;s:8:\"FIELDVAR\";N;}}',0),(3,1,'ແກ້ວ','','a:2:{s:6:\"SEARCH\";a:1:{i:0;s:3:\"f_2\";}i:0;a:5:{s:6:\"SEARCH\";s:3:\"f_2\";s:2:\"OP\";s:9:\"STARTWITH\";s:5:\"FIELD\";a:1:{i:0;s:1:\"b\";}s:5:\"INTER\";N;s:8:\"FIELDVAR\";N;}}',0),(4,4,'ແມ່ນຫຍັງວະ','ກດເຫກ້່ກດ້ເຫກັພິເະຳພ້ພະາ່ຳພເະໄພ່ະິສກເກດເຫກເກດເຶຫ້','a:2:{s:6:\"SEARCH\";a:1:{i:0;s:3:\"f_3\";}i:0;a:5:{s:6:\"SEARCH\";s:3:\"f_3\";s:2:\"OP\";s:9:\"STARTWITH\";s:5:\"FIELD\";a:1:{i:0;s:1:\"a\";}s:5:\"INTER\";N;s:8:\"FIELDVAR\";N;}}',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `equations` ENABLE KEYS */;

--
-- Table structure for table `error_log`
--

DROP TABLE IF EXISTS `error_log`;
CREATE TABLE `error_log` (
  `error_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `error_origin` varchar(255) default NULL,
  `error_text` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `error_log`
--


/*!40000 ALTER TABLE `error_log` DISABLE KEYS */;
LOCK TABLES `error_log` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `error_log` ENABLE KEYS */;

--
-- Table structure for table `etagere`
--

DROP TABLE IF EXISTS `etagere`;
CREATE TABLE `etagere` (
  `idetagere` int(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `comment` blob NOT NULL,
  `validite` int(1) unsigned NOT NULL default '0',
  `validite_date_deb` date NOT NULL default '0000-00-00',
  `validite_date_fin` date NOT NULL default '0000-00-00',
  `visible_accueil` int(1) unsigned NOT NULL default '1',
  `autorisations` mediumtext,
  PRIMARY KEY  (`idetagere`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `etagere`
--


/*!40000 ALTER TABLE `etagere` DISABLE KEYS */;
LOCK TABLES `etagere` WRITE;
INSERT INTO `etagere` VALUES (3,'Loire','Exposition virtuelle sur la Loire',1,'0000-00-00','0000-00-00',1,'1 4 3 2');
UNLOCK TABLES;
/*!40000 ALTER TABLE `etagere` ENABLE KEYS */;

--
-- Table structure for table `etagere_caddie`
--

DROP TABLE IF EXISTS `etagere_caddie`;
CREATE TABLE `etagere_caddie` (
  `etagere_id` int(8) unsigned NOT NULL default '0',
  `caddie_id` int(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`etagere_id`,`caddie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `etagere_caddie`
--


/*!40000 ALTER TABLE `etagere_caddie` DISABLE KEYS */;
LOCK TABLES `etagere_caddie` WRITE;
INSERT INTO `etagere_caddie` VALUES (3,5);
UNLOCK TABLES;
/*!40000 ALTER TABLE `etagere_caddie` ENABLE KEYS */;

--
-- Table structure for table `exemplaires`
--

DROP TABLE IF EXISTS `exemplaires`;
CREATE TABLE `exemplaires` (
  `expl_id` mediumint(8) unsigned NOT NULL auto_increment,
  `expl_cb` varchar(50) NOT NULL default '',
  `expl_notice` mediumint(8) unsigned NOT NULL default '0',
  `expl_bulletin` int(8) unsigned NOT NULL default '0',
  `expl_typdoc` tinyint(3) unsigned NOT NULL default '0',
  `expl_cote` varchar(50) NOT NULL default '',
  `expl_section` smallint(5) unsigned NOT NULL default '0',
  `expl_statut` smallint(5) unsigned NOT NULL default '0',
  `expl_location` smallint(5) unsigned NOT NULL default '0',
  `expl_codestat` smallint(5) unsigned NOT NULL default '0',
  `expl_date_depot` date NOT NULL default '0000-00-00',
  `expl_date_retour` date NOT NULL default '0000-00-00',
  `expl_note` tinytext NOT NULL,
  `expl_prix` varchar(255) NOT NULL default '',
  `expl_owner` mediumint(8) unsigned NOT NULL default '0',
  `expl_lastempr` int(10) unsigned NOT NULL default '0',
  `last_loan_date` date default NULL,
  `create_date` datetime NOT NULL default '2005-01-01 00:00:00',
  `update_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`expl_id`),
  UNIQUE KEY `expl_cb` (`expl_cb`),
  KEY `expl_typdoc` (`expl_typdoc`),
  KEY `expl_cote` (`expl_cote`),
  KEY `expl_notice` (`expl_notice`),
  KEY `expl_codestat` (`expl_codestat`),
  KEY `expl_owner` (`expl_owner`),
  KEY `expl_bulletin` (`expl_bulletin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `exemplaires`
--


/*!40000 ALTER TABLE `exemplaires` DISABLE KEYS */;
LOCK TABLES `exemplaires` WRITE;
INSERT INTO `exemplaires` VALUES (1,'000001',1,0,1,'050',10,1,1,12,'0000-00-00','0000-00-00','','7000 ກີບ',2,0,'2006-10-13','2006-10-13 15:16:43','2006-10-13 15:19:51'),(2,'000002',1,0,1,'050',10,1,1,12,'0000-00-00','0000-00-00','','7000 ກີບ',2,7,'2006-10-13','2006-10-13 15:17:14','2006-10-13 15:21:35'),(3,'000003',1,0,1,'050',10,1,1,12,'0000-00-00','0000-00-00','','7000 ກີບ',2,0,NULL,'2006-10-13 15:18:21','2006-10-13 15:18:21'),(4,'000004',1,0,1,'050',10,1,1,10,'0000-00-00','0000-00-00','','',2,0,NULL,'2006-10-13 15:18:50','2006-10-13 15:18:50'),(5,'000005',1,0,1,'050',10,1,1,12,'0000-00-00','0000-00-00','','',2,0,NULL,'2006-10-13 15:19:11','2006-10-13 15:19:11'),(6,'000011',2,0,1,'001',10,1,1,10,'0000-00-00','0000-00-00','','9600ກີບ',2,0,'2006-10-13','2006-10-13 15:29:24','2006-10-13 15:35:07'),(7,'000012',2,0,1,'001',10,1,1,10,'0000-00-00','0000-00-00','','9600ກີບ',2,0,NULL,'2006-10-13 15:30:02','2006-10-13 15:30:02'),(8,'000021',3,0,1,'000',10,1,1,10,'0000-00-00','0000-00-00','','96000ກີບ',2,0,'2006-10-13','2006-10-13 15:33:14','2006-10-13 15:35:23'),(9,'000022',3,0,1,'000',10,1,1,10,'0000-00-00','0000-00-00','','96000ກີບ',2,0,'2006-10-13','2006-10-13 15:33:52','2006-10-13 15:38:51'),(10,'000023',3,0,1,'000',10,1,1,10,'0000-00-00','0000-00-00','','96000ກີບ',2,0,NULL,'2006-10-13 15:34:28','2006-10-13 15:34:28'),(11,'000031',4,0,1,'500',10,1,1,10,'0000-00-00','0000-00-00','','82000 ກີບ',2,0,NULL,'2006-10-13 15:47:56','2006-10-13 15:48:56'),(12,'000041',5,0,1,'800',10,1,1,10,'0000-00-00','0000-00-00','','170000ກີບ',2,0,NULL,'2006-10-13 15:52:20','2006-10-13 15:52:20'),(13,'000051',6,0,1,'002',10,1,1,10,'0000-00-00','0000-00-00','','13000ກີບ',2,0,NULL,'2006-10-13 15:54:42','2006-10-13 15:54:42'),(14,'000061',7,0,1,'110',10,1,1,10,'0000-00-00','0000-00-00','','7500 ກີບ',2,0,NULL,'2006-10-13 15:58:02','2006-10-13 15:58:02'),(15,'000071',8,0,1,'009',10,1,1,10,'0000-00-00','0000-00-00','','5000 ກີບ',2,3,'2006-10-14','2006-10-13 16:00:17','2006-10-14 08:18:56'),(16,'000081',9,0,1,'789',10,1,1,10,'0000-00-00','0000-00-00','','200000 ກີບ',2,0,NULL,'2006-10-13 16:02:18','2006-10-13 16:02:18'),(17,'000080',10,0,1,'808',10,1,1,10,'0000-00-00','0000-00-00','','20000ກີບ',2,0,NULL,'2006-10-13 16:06:42','2006-10-13 16:06:42'),(18,'000091',11,0,1,'870',10,1,1,10,'0000-00-00','0000-00-00','','700000ກີບ',2,0,NULL,'2006-10-13 16:10:26','2006-10-13 16:10:26'),(19,'0001001',12,0,1,'890',10,1,1,10,'0000-00-00','0000-00-00','','5800ກີບ',2,0,NULL,'2006-10-13 16:13:07','2006-10-13 16:13:07'),(20,'0001002',13,0,1,'120',10,1,1,10,'0000-00-00','0000-00-00','','8000ກີບ',2,0,NULL,'2006-10-13 16:14:47','2006-10-13 16:14:47'),(21,'00001003',14,0,1,'450',10,1,1,10,'0000-00-00','0000-00-00','','78000ກີບ',2,0,NULL,'2006-10-13 16:18:14','2006-10-13 16:18:14'),(22,'0001003',15,0,1,'560',10,1,1,10,'0000-00-00','0000-00-00','','34000ກີບ',2,0,NULL,'2006-10-13 16:20:37','2006-10-13 16:20:37'),(23,'0001004',16,0,1,'870',10,1,1,10,'0000-00-00','0000-00-00','','12500ກີບ',2,0,NULL,'2006-10-13 16:23:11','2006-10-13 16:23:11'),(24,'0001006',17,0,1,'730',10,1,1,10,'0000-00-00','0000-00-00','','73000ກີບ',2,0,NULL,'2006-10-13 16:25:42','2006-10-13 16:25:42'),(25,'000123',0,2,1,'500',16,1,7,10,'0000-00-00','0000-00-00','','10000ກີບ',2,0,NULL,'2006-10-13 16:43:08','2006-10-13 16:43:08'),(26,'000124',0,3,1,'500',10,1,7,10,'0000-00-00','0000-00-00','','25000ກີບ',2,0,NULL,'2006-10-13 16:45:58','2006-10-13 16:46:27'),(27,'AQ3',25,0,1,'000',10,1,1,10,'0000-00-00','0000-00-00','','',2,1,'2006-10-16','2006-10-14 09:10:09','2006-10-16 16:59:54'),(28,'3370000451300',50,0,1,'JR COC',13,1,1,12,'2004-08-05','0000-00-00','','',2,0,NULL,'2005-01-01 00:00:00','2005-06-22 23:15:28'),(29,'3370000451302',51,0,1,'590 BOU',10,1,1,12,'2004-08-05','2004-08-05','','',2,0,NULL,'2005-01-01 00:00:00','2005-08-10 22:25:04'),(30,'33700004500167',53,0,1,'RK ROB',10,1,1,12,'2004-08-05','2004-08-05','','',2,0,NULL,'2005-01-01 00:00:00','2005-06-22 23:15:28'),(32,'6438646236',2,0,1,'R HER',13,1,1,12,'2004-09-13','2004-09-13','','',2,0,NULL,'2005-01-01 00:00:00','2005-06-22 23:15:28'),(33,'1005',58,0,1,'1',10,1,1,12,'0000-00-00','0000-00-00','tester ','100',2,0,NULL,'2006-08-22 17:46:35','2006-08-22 17:47:38'),(34,'11586-11592',60,0,1,'000',10,1,1,12,'0000-00-00','0000-00-00','à»€àº§àº»à»‰àº²àº?à»ˆàº½àº§àº?àº±àºšàºžàº»àº‡àºªàº²àº§àº°àº”àº²àº™','à»‘à»’à»’à»’',2,0,NULL,'2006-08-24 18:11:59','2006-08-28 14:29:04'),(35,'11586-11593',60,0,1,'009',10,1,1,12,'0000-00-00','0000-00-00','gh,jhg,jdh','100000',2,0,NULL,'2006-08-24 18:14:25','2006-08-25 10:05:57'),(41,'00111',59,0,1,'099',10,1,1,12,'0000-00-00','0000-00-00','','',2,0,NULL,'2006-10-05 17:42:20','2006-10-05 17:42:20'),(37,'PE37',63,0,1,'000',10,1,1,12,'0000-00-00','0000-00-00','àº?àº²àº™à»ƒàº«à»‰àº¢àº·àº¡àº”à»ˆàº§àº™','24000',2,9,'2006-11-08','2005-01-01 00:00:00','2006-11-08 15:50:29'),(40,'11602-03',65,0,1,'001',10,1,1,12,'0000-00-00','0000-00-00','','15000àº?àºµàºš',2,0,NULL,'2006-10-05 17:19:56','2006-10-05 17:19:56');
UNLOCK TABLES;
/*!40000 ALTER TABLE `exemplaires` ENABLE KEYS */;

--
-- Table structure for table `exercices`
--

DROP TABLE IF EXISTS `exercices`;
CREATE TABLE `exercices` (
  `id_exercice` int(8) unsigned NOT NULL auto_increment,
  `num_entite` int(5) unsigned NOT NULL default '0',
  `libelle` varchar(255) NOT NULL default '',
  `date_debut` date NOT NULL default '2006-01-01',
  `date_fin` date NOT NULL default '2006-01-01',
  `statut` int(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_exercice`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `exercices`
--


/*!40000 ALTER TABLE `exercices` DISABLE KEYS */;
LOCK TABLES `exercices` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `exercices` ENABLE KEYS */;

--
-- Table structure for table `expl_custom`
--

DROP TABLE IF EXISTS `expl_custom`;
CREATE TABLE `expl_custom` (
  `idchamp` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `titre` varchar(255) default NULL,
  `type` varchar(10) NOT NULL default 'text',
  `datatype` varchar(10) NOT NULL default '',
  `options` text,
  `multiple` int(11) NOT NULL default '0',
  `obligatoire` int(11) NOT NULL default '0',
  `ordre` int(11) default NULL,
  PRIMARY KEY  (`idchamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `expl_custom`
--


/*!40000 ALTER TABLE `expl_custom` DISABLE KEYS */;
LOCK TABLES `expl_custom` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `expl_custom` ENABLE KEYS */;

--
-- Table structure for table `expl_custom_lists`
--

DROP TABLE IF EXISTS `expl_custom_lists`;
CREATE TABLE `expl_custom_lists` (
  `expl_custom_champ` int(10) unsigned NOT NULL default '0',
  `expl_custom_list_value` varchar(255) default NULL,
  `expl_custom_list_lib` varchar(255) default NULL,
  `ordre` int(11) default NULL,
  KEY `expl_custom_champ` (`expl_custom_champ`),
  KEY `expl_champ_list_value` (`expl_custom_champ`,`expl_custom_list_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `expl_custom_lists`
--


/*!40000 ALTER TABLE `expl_custom_lists` DISABLE KEYS */;
LOCK TABLES `expl_custom_lists` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `expl_custom_lists` ENABLE KEYS */;

--
-- Table structure for table `expl_custom_values`
--

DROP TABLE IF EXISTS `expl_custom_values`;
CREATE TABLE `expl_custom_values` (
  `expl_custom_champ` int(10) unsigned NOT NULL default '0',
  `expl_custom_origine` int(10) unsigned NOT NULL default '0',
  `expl_custom_small_text` varchar(255) default NULL,
  `expl_custom_text` text,
  `expl_custom_integer` int(11) default NULL,
  `expl_custom_date` date default NULL,
  `expl_custom_float` float default NULL,
  KEY `expl_custom_champ` (`expl_custom_champ`),
  KEY `expl_custom_origine` (`expl_custom_origine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `expl_custom_values`
--


/*!40000 ALTER TABLE `expl_custom_values` DISABLE KEYS */;
LOCK TABLES `expl_custom_values` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `expl_custom_values` ENABLE KEYS */;

--
-- Table structure for table `explnum`
--

DROP TABLE IF EXISTS `explnum`;
CREATE TABLE `explnum` (
  `explnum_id` int(11) unsigned NOT NULL auto_increment,
  `explnum_notice` mediumint(8) unsigned NOT NULL default '0',
  `explnum_bulletin` int(8) unsigned NOT NULL default '0',
  `explnum_nom` varchar(255) NOT NULL default '',
  `explnum_mimetype` varchar(255) NOT NULL default '',
  `explnum_url` text NOT NULL,
  `explnum_data` mediumblob,
  `explnum_vignette` mediumblob,
  `explnum_extfichier` varchar(20) default '',
  `explnum_nomfichier` text,
  PRIMARY KEY  (`explnum_id`),
  KEY `explnum_notice` (`explnum_notice`),
  KEY `explnum_bulletin` (`explnum_bulletin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `explnum`
--


/*!40000 ALTER TABLE `explnum` DISABLE KEYS */;
LOCK TABLES `explnum` WRITE;
INSERT INTO `explnum` VALUES (1,42,0,'Reproduction basse qualité','image/jpeg','','����\0JFIF\0\0H\0H\0\0���Exif\0\0MM\0*\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0b\0\0\0\0\0\0\0j(\0\0\0\0\0\0\01\0\0\0\0\0\0\0r2\0\0\0\0\0\0\0��i\0\0\0\0\0\0\0�\0\0\0�\0\0\0H\0\0\0\0\0\0H\0\0\0Adobe Photoshop 7.0\02004:08:04 18:34:44\0\0\0\0�\0\0\0\0��\0\0�\0\0\0\0\0\0��\0\0\0\0\0\0Q\0\0\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0(\0\0\0\0\0\0\0\0\0\0\0\0\0&\0\0\0\0\0\0\Z�\0\0\0\0\0\0\0H\0\0\0\0\0\0H\0\0\0����\0JFIF\0\0H\0H\0\0��\0Adobe_CM\0��\0Adobe\0d�\0\0\0��\0�\0			\n\r\r\r��\0\0T\0�\"\0��\0\0��?\0\0\0\0\0\0\0\0\0\0	\n\0\0\0\0\0\0\0\0\0	\n\03\0!1AQa\"q�2���B#$R�b34r��C%�S���cs5���&D�TdE£t6�U�e���u��F\'���������������Vfv��������7GWgw��������\05\0!1AQaq\"2����B#�R��3$b�r��CScs4�%���&5��D�T�dEU6te����u��F���������������Vfv��������\'7GWgw�������\0\0\0?\0�ʻ�:�W�ꫵ����A��W���ci�m���m��~��Bv��\'+:�\r�z�e-��Ƴ�}�\0g�g�[�6Oؿ�~�Ӭ��d��U5����R�XH���c��\0m?��\0�5X�6�۬�u[�7>��F��*;k�uWn�?����\0��\"�or��˹���2���+c���3��ا�}_{-�\0�S�\'ޣ�4M$Yhu���9�ʮõ���]Ogڜ�\0R���տL���e/�������1�c��F�)Ƨ}{���\0�����\\��\r!�ݎ��Xb���f�E��\0m^������G*��K��n��촖�[�anm���3�c1�5�_WյI춙[��5�i%����ln�]���=?��uB��ikq[C�}��a�{�i>��]k\Z���~O�{*ĺ�n�eX���f@�$�cC��C�$����h��d?߫ӎ�\0�:�����Hd��&��[O�ƶ\Z�k�=j��c}]�~�������~�������X�������T���~�͟��{�\0I�}��ERe~�Y��.c�Si!��u��Ȣ����=?K/��\0�{����2K�;Cq�q���S�}Of�K\Z�i����ƻ��ٽD���U�sh{��V�\n�E�ߏ�H����\0Z�������+��!Uux�Q]�PXk��:ǚf�O�N[�s��\0�X�\0f�^�e6��E�k�9\Z7m��C�.�+���{�߳-�}i}?������^Ó��G�C�$��[��z�g���������o�IZ-~^M6�oQ�X��������~������\0C#����z�����z��hƺ�˫��#�6�����۽[�K���?ӻ���}��(S���]n6YnFU����[g�[��{�5�m\r�e��:�~���\n�~%�fe}����)p6mu�a�&ʘ�^�2�w����\0�R(H3�]�\0��n�02A���nO�q���\0����7���?H������r\rnmom�n/�Cq��N����~埤�\"�N=W`��#,zh�Kks��s���=&��u/e��m�%��?Ed����猬���5�z�E�Ǥd��k=O^���-�U����\Z��2썍}�[��=\'��ƶ�s*uoe���mY��W�����bR��6���O��6�WW�[��\0��*��\0��\0��n5���[v�lu�ݬih{�n�\\�W��{w�\0:�C�\Z�x�n^\0sMo��t��-~�n��cZ���u��������5t>O����e��\n�B��ڝ�:}�Y���ޣ��]��w��L���\ZH�ԯѱ�����Dk�1.���̖�����,�6��]���w�~g�EDفM��I�Bꩦ��ҁf�f��k���_Ի���\0I�A}��K��F;��y/�Z�=��=V7�ٷ��t�]��Я�z��ˤ��X�/����g�jm���{��ُ�3��?X��?Kuh���U��{};1����n=[E���}����������Է�Qu���9W6������U��dc�r7���S����W�T�k��W�e�O�.Ӌ�ʩh� ��\0K��5ͩ�f5V3���Y���Y�l�\Z�����|8ײ��P��ks>����S�T�q�n5�n��v#M��̓���Hk�VZ�W:���%U�����e��6�>��m����[�;��cn��1�*��.�mv>^M�R�eM�����p��z�2�\0�lg�?��e2�1�/��P�[H�8��5��=)�m5�`lg����\0�e��}G���{���{�\Z�ᑊ�(>��=+�*�w��\'��꫷��������6f�m��\rg��7��z��>�j���S[2�َ~їm>�M�\Z�#�1c���l�g�,�}�f�\0�^���,��i��󭶆�����n����?I���]�9�����e?�kNjkCq���.mNǺֆ����Ȝ]���V�ׇ�����?��K/����~Uؗ=�.�)��u�\'ۊ�i������j�\0�z�L�;�ǳ5�k]�*��xֻ1�\0N����_����?�����%6�����Km\"��:�={���[w�?�6%�o���m�w��E0盳��ِ���S���[m�\0����U�\r+�K,\"qX��Me�is.ul\'&��7�]T�7�u���^�JY�����.�K�]��v\Z�����\0����?��C}4F�l�z5S�-���o&�\0�,�7{\Z,݊�_m���eX_���Y?��N,���&�Y9��*����ǻs=����U��G���j����{��S��\05���V�S[6�Ϫ�)��Z����\0�zY�������h��LZ���\n�q�[����C\\�]E�z46�ѿ�*�)ǿ\'*�U�vF�\Z��m�ضzή�]��W���?�Ea�x!��g�p�1�e8�=�zW��M��}vS������g�%�����z�J��˾�_Rë\'��>�͙f��\09M�l�q���\0�3�\'�F��t=_���c:��O�[�z���ֵ�\rs-slv���mgЪ�R��U�K�Z�����q�3����h�;]�ҩ��=;�/�4���R�IήM4�~�s6Z��\0���7��O�~���k�q2I,&�S��4�M�5�����ϯ�\0��32]UMv^-՟J˚��Qs]����g�nM���9Ev{�\0��Y��n�?%�.elum�9�E�����s�}�2�z�h�����ݎlnnfm��5���:�6�n}L��?�f��,���_�M�8�;�X�\\˙}\"���Y�q.�][��w��\0ϩ)|۱uyU�<������Z�k�k��+֫���\0\n��3��\"�3M�1�k��5_�]][j��oٯ���t��fc��-�;�q�hi�ʛe!�}[��\\�+g���?������VY��1���:_c�����h�{��a��-^���?�$��l����N�^����zz8=�����W�K�5�e�g�i���uSS�E7�\r���9�9��;��3}UUCk�����\0�շ��\\*6�Sq��\\��E��j��c�K�O^�\0�{���g����im�OkF�M2\Z�l��Ys������̟��A.]l.s�lf3(����+�(%�b۱���Vھ��������m��m�`�C�j\0X��>�+k27SE��}����k}O��˺��=7G���x��z{Y�������\0�L+����7\"��Vc�a{}�h���w���?欳�-oI^\r��qnHa���k�8䵡�����X���܇����\0����i{��F@�����\nl�����l��z�S�\0]��a���e�ٗ�2r+�l��略Q�+Pmu����g���ߤ��O\Z��W���CC�Ml�{���	u~�7_�\'�W���\0	��� k/��Ci���lp�����}�nQ�g���o�������F����\0�{.��Ua��̆zA�=V{m~��~�+��;�����+s\Z ���sX���;�Ko��d�َ�Z���o��������eV����mcMV���z�������Ʋ���6�\Z\r�m\'��Hȷ�������?�z���>�����ޣ�,���a��ļ�6_o�S���?��\03��������kٸ�1���8[,$M�ymu���/�v6�X�/k��r�l��K_�M-����f����ܳ���U����gQ�l>�6Z���/��{>�wW���[�=�\0�{򺋝E��o���:��̓Qmo{o��n�_�[1�Zj����CR�]K��1�Z&�Y��k�6�G��]���~�b�NG�_M9e�\r{>���c�8�m��Y��c��\0J����ٻ��qx}�ױ�m��k�\0���u�m~�.�/���3���^v��2���s��klW`�c��W���ݲ��o��ש�;%βѐ�j�f������Se�z7_�.���Nʙ���w�\Z���V�SZ�Zi�!�^6�6���uz?�?�?�lE	.���YnS~�-dn�\Z��͞��V[[?Af��[�\0M驰;(Xl�(Sc�n9sj�z?j�{\Z�������}?G/��\0ͪ.n^c+�U2��ɹ�����/s�k�{�?R�*�e?�=\\�����M���r��E�ӏF�w:�ֈ/g�x�6��~�}������II��z�\0��[����ʘ�\0Q�\0�w��\0H�nM�R�8e]c����eu\n��7~E[k.�W��o�������8��Q�̖��+�����Ƚ��\r[�mU=���6�[{?K[����\0�3�,fT�ZE��ii��x{��Uf̯S��в�=�̤��0�X�e�eN�cCXE���+������l�>��?I�\0Z�Qm���cѓf��D���ak��X�sj���1������z�\n�-}�ˢ��uuz��\\4��Ӯڽ?Smv�R��:���ɪ����v]��2���1��)��`�N����e��?Q���O�y+�Xur�]�hk%�}����4���k�ֽ����\0ū8Ϊ���cdё2��k\\�����ǳ��#=��E��=+.�\n��g2�qoĺ��k趻o�.�X}<l�6�YS=l���:n���\0��JF���mñ�������m���Ǻ�6۫o�믧#��ؒ��ò�F�a��ٮ�@d�`����e�q�G�����ӫy���=��������\'�u}U�c6z�8U1��:s��&���eѴ<3�a��Ͷ�m���v}��)�u�ߓc�m����\n���3���-��V�_���\0���j�.�{�~�0���pm.u��\08�w?��\0�*��:u��5ٝK	�I>�yT�C���}M�?�tzi���r=\nE�k���хW��w��~KjߍU���i�}E[#+\'�=��MO������wC[.�ؔ6�������\09��\0I��F�2�\'������n9qc�w9��Ѡ���3�ߠߧ��v#��WvG����XsK�=���%t�M��g�=?�oM���}�M[=��l������7w�_閕]~�5x��X��΂Ɲ��S�z��}u��\0	�\rWl^���0Y�[��sko�ZH,�����?I�7��������M�η�1�s2�p.�uS��=7����=o��CY�Q���;Ul�� ��X���-���5����o�W����N2^�k^r�m�72r{Yc+�ֽ���W���\0�eȡ�]u�\'�ޝ���n����~�k��:[���M~��.��C�X}�xs�Յ�ϲ�J�~��:�v?,���=B��������m�ow���?����_���l{i��u�h��n-k��W����}~����%u�Y�p��ƹ�`����om����=���u)ߌZˎF\0�\Z�*���5���wl����F���/�4\\|&b8X��\'�m���փX~�䆻��O���U��~>;j�\'$W��{ni{�c�m���{������\0�}ޕ����T��r�-ާ�-6����m[v[[��\0q������=*�X��5�p�dF�p�]����ֻ��\0��\0�X�.��uli��u�8\\�e~�lg���c�����O�����n�L�r}\n�G��{� �ls�����\"Bm�N6�k�I��%�\"�_�;k��~�ޖN�����z_��!��1�n�ŭ�[7�ױ�p`�e^����\0j?IU��*�Wn�z�T����$9��\r�O���KwS�\0g���3[L�A��s�֐ּ�ۜ��{\\���\0E���$$�C��N܊1�[������m��]WW�ͫc}��j�������mŹ����-����6m��W�e^�Q�g��Lq�!�\0f����ml�5��\0m>���U�X�U����D{_���H~C��+���e�Wß�+۲�:��V�\0_�?i�\Z�:��C=z�4?\'-�vMLc�{�\'YG��;��1}R܋������ن��3\Zz���J�݇�c�Ʊ������Q�uv�\0;��\00W.���5Օk��s���`\r��>�U��Ӂ�_�>���1c>�~��\\��lF�� �������F q�>��\0����\r��վ��g��}>}��������z�6�S���F��_�߿���E��J���>��������ޟ��7���n�v7Н�\0���z�Ul��-ٷ�7dzq�k���\0��^��$�W��E_����������%���ߴ?����\0�Fg��^���zwz;c�~���>�Sw����\0���~���S�_c����i����͏W���?��)�^����c��tG�=��$�ڡ�}�ԍ~���?g�0w}r�~�>}}����,o�����k��C�Q�>���{~����G���[wG����s�o��l�K{g��=A�Fͻ��{���ڼM$~�گ�_dw����D�>���}�=o��j���}�n��ϰ����~����i%��U�>�w�����>��>ɶ5����G�=o�=�77�o���[>׻�x���\0Oӟ�ѿ�_W�7�k�\Z�t���������>�Photoshop 3.0\08BIM%\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\08BIM�\0\0\0\0�<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE plist PUBLIC \"-//Apple Computer//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">\n<plist version=\"1.0\">\n<dict>\n	<key>com.apple.print.PageFormat.PMHorizontalRes</key>\n	<dict>\n		<key>com.apple.print.ticket.creator</key>\n		<string>com.apple.printingmanager</string>\n		<key>com.apple.print.ticket.itemArray</key>\n		<array>\n			<dict>\n				<key>com.apple.print.PageFormat.PMHorizontalRes</key>\n				<real>72</real>\n				<key>com.apple.print.ticket.client</key>\n				<string>com.apple.printingmanager</string>\n				<key>com.apple.print.ticket.modDate</key>\n				<date>2004-08-04T16:32:30Z</date>\n				<key>com.apple.print.ticket.stateFlag</key>\n				<integer>0</integer>\n			</dict>\n		</array>\n	</dict>\n	<key>com.apple.print.PageFormat.PMOrientation</key>\n	<dict>\n		<key>com.apple.print.ticket.creator</key>\n		<string>com.apple.printingmanager</string>\n		<key>com.apple.print.ticket.itemArray</key>\n		<array>\n			<dict>\n				<key>com.apple.print.PageFormat.PMOrientation</key>\n				<integer>1</integer>\n				<key>com.apple.print.ticket.client</key>\n				<string>com.apple.printingmanager</string>\n				<key>com.apple.print.ticket.modDate</key>\n				<date>2004-08-04T16:32:30Z</date>\n				<key>com.apple.print.ticket.stateFlag</key>\n				<integer>0</integer>\n			</dict>\n		</array>\n	</dict>\n	<key>com.apple.print.PageFormat.PMScaling</key>\n	<dict>\n		<key>com.apple.print.ticket.creator</key>\n		<string>com.apple.printingmanager</string>\n		<key>com.apple.print.ticket.itemArray</key>\n		<array>\n			<dict>\n				<key>com.apple.print.PageFormat.PMScaling</key>\n				<real>1</real>\n				<key>com.apple.print.ticket.client</key>\n				<string>com.apple.printingmanager</string>\n				<key>com.apple.print.ticket.modDate</key>\n				<date>2004-08-04T16:32:30Z</date>\n				<key>com.apple.print.ticket.stateFlag</key>\n				<integer>0</integer>\n			</dict>\n		</array>\n	</dict>\n	<key>com.apple.print.PageFormat.PMVerticalRes</key>\n	<dict>\n		<key>com.apple.print.ticket.creator</key>\n		<string>com.apple.printingmanager</string>\n		<key>com.apple.print.ticket.itemArray</key>\n		<array>\n			<dict>\n				<key>com.apple.print.PageFormat.PMVerticalRes</key>\n				<real>72</real>\n				<key>com.apple.print.ticket.client</key>\n				<string>com.apple.printingmanager</string>\n				<key>com.apple.print.ticket.modDate</key>\n				<date>2004-08-04T16:32:30Z</date>\n				<key>com.apple.print.ticket.stateFlag</key>\n				<integer>0</integer>\n			</dict>\n		</array>\n	</dict>\n	<key>com.apple.print.PageFormat.PMVerticalScaling</key>\n	<dict>\n		<key>com.apple.print.ticket.creator</key>\n		<string>com.apple.printingmanager</string>\n		<key>com.apple.print.ticket.itemArray</key>\n		<array>\n			<dict>\n				<key>com.apple.print.PageFormat.PMVerticalScaling</key>\n				<real>1</real>\n				<key>com.apple.print.ticket.client</key>\n				<string>com.apple.printingmanager</string>\n				<key>com.apple.print.ticket.modDate</key>\n				<date>2004-08-04T16:32:30Z</date>\n				<key>com.apple.print.ticket.stateFlag</key>\n				<integer>0</integer>\n			</dict>\n		</array>\n	</dict>\n	<key>com.apple.print.subTicket.paper_info_ticket</key>\n	<dict>\n		<key>com.apple.print.PageFormat.PMAdjustedPageRect</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.printingmanager</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PageFormat.PMAdjustedPageRect</key>\n					<array>\n						<real>0.0</real>\n						<real>0.0</real>\n						<real>783</real>\n						<real>559</real>\n					</array>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.printingmanager</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2004-08-04T16:32:30Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>0</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.PageFormat.PMAdjustedPaperRect</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.printingmanager</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PageFormat.PMAdjustedPaperRect</key>\n					<array>\n						<real>-18</real>\n						<real>-18</real>\n						<real>824</real>\n						<real>577</real>\n					</array>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.printingmanager</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2004-08-04T16:32:30Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>0</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.PaperInfo.PMPaperName</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.print.pm.PostScript</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PaperInfo.PMPaperName</key>\n					<string>iso-a4</string>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.print.pm.PostScript</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2003-07-01T17:49:36Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>1</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.PaperInfo.PMUnadjustedPageRect</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.print.pm.PostScript</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PaperInfo.PMUnadjustedPageRect</key>\n					<array>\n						<real>0.0</real>\n						<real>0.0</real>\n						<real>783</real>\n						<real>559</real>\n					</array>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.printingmanager</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2004-08-04T16:32:30Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>0</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.PaperInfo.PMUnadjustedPaperRect</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.print.pm.PostScript</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PaperInfo.PMUnadjustedPaperRect</key>\n					<array>\n						<real>-18</real>\n						<real>-18</real>\n						<real>824</real>\n						<real>577</real>\n					</array>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.printingmanager</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2004-08-04T16:32:30Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>0</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.PaperInfo.ppd.PMPaperName</key>\n		<dict>\n			<key>com.apple.print.ticket.creator</key>\n			<string>com.apple.print.pm.PostScript</string>\n			<key>com.apple.print.ticket.itemArray</key>\n			<array>\n				<dict>\n					<key>com.apple.print.PaperInfo.ppd.PMPaperName</key>\n					<string>A4</string>\n					<key>com.apple.print.ticket.client</key>\n					<string>com.apple.print.pm.PostScript</string>\n					<key>com.apple.print.ticket.modDate</key>\n					<date>2003-07-01T17:49:36Z</date>\n					<key>com.apple.print.ticket.stateFlag</key>\n					<integer>1</integer>\n				</dict>\n			</array>\n		</dict>\n		<key>com.apple.print.ticket.APIVersion</key>\n		<string>00.20</string>\n		<key>com.apple.print.ticket.privateLock</key>\n		<false/>\n		<key>com.apple.print.ticket.type</key>\n		<string>com.apple.print.PaperInfoTicket</string>\n	</dict>\n	<key>com.apple.print.ticket.APIVersion</key>\n	<string>00.20</string>\n	<key>com.apple.print.ticket.privateLock</key>\n	<false/>\n	<key>com.apple.print.ticket.type</key>\n	<string>com.apple.print.PageFormatTicket</string>\n</dict>\n</plist>\n8BIM�\0\0\0\0\0x\0\0\0\0H\0H\0\0\0\0/����8Ag{�\0\0\0\0H\0H\0\0\0\0�(\0\0\0\0d\0\0\0\0\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0h\0�\0\0\0\0\0 \0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\08BIM�\0\0\0\0\0\0H\0\0\0\0\0H\0\0\0\08BIM&\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0?�\0\08BIM\r\0\0\0\0\0\0\0\08BIM\0\0\0\0\0\0\0\08BIM�\0\0\0\0\0	\0\0\0\0\0\0\0\0\08BIM\n\0\0\0\0\0\0\08BIM\'\0\0\0\0\0\n\0\0\0\0\0\0\0\08BIM�\0\0\0\0\0H\0/ff\0\0lff\0\0\0\0\0\0\0/ff\0\0���\0\0\0\0\0\0\02\0\0\0\0Z\0\0\0\0\0\0\0\0\05\0\0\0\0-\0\0\0\0\0\0\0\08BIM�\0\0\0\0\0p\0\0�����������������������\0\0\0\0�����������������������\0\0\0\0�����������������������\0\0\0\0�����������������������\0\08BIM\0\0\0\0\0\0\0\0\0\0@\0\0@\0\0\0\08BIM\0\0\0\0\0\0\0\0\08BIM\Z\0\0\0\0A\0\0\0\0\0\0\0\0\0\0\0\0\0Q\0\0�\0\0\0\0c\0h\0a\0r\0t\0e\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Q\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0null\0\0\0\0\0\0boundsObjc\0\0\0\0\0\0\0\0\0Rct1\0\0\0\0\0\0\0Top long\0\0\0\0\0\0\0\0Leftlong\0\0\0\0\0\0\0\0Btomlong\0\0Q\0\0\0\0Rghtlong\0\0�\0\0\0slicesVlLs\0\0\0Objc\0\0\0\0\0\0\0\0slice\0\0\0\0\0\0sliceIDlong\0\0\0\0\0\0\0groupIDlong\0\0\0\0\0\0\0originenum\0\0\0ESliceOrigin\0\0\0\rautoGenerated\0\0\0\0Typeenum\0\0\0\nESliceType\0\0\0\0Img \0\0\0boundsObjc\0\0\0\0\0\0\0\0\0Rct1\0\0\0\0\0\0\0Top long\0\0\0\0\0\0\0\0Leftlong\0\0\0\0\0\0\0\0Btomlong\0\0Q\0\0\0\0Rghtlong\0\0�\0\0\0urlTEXT\0\0\0\0\0\0\0\0\0nullTEXT\0\0\0\0\0\0\0\0\0MsgeTEXT\0\0\0\0\0\0\0\0altTagTEXT\0\0\0\0\0\0\0\0cellTextIsHTMLbool\0\0\0cellTextTEXT\0\0\0\0\0\0\0\0	horzAlignenum\0\0\0ESliceHorzAlign\0\0\0default\0\0\0	vertAlignenum\0\0\0ESliceVertAlign\0\0\0default\0\0\0bgColorTypeenum\0\0\0ESliceBGColorType\0\0\0\0None\0\0\0	topOutsetlong\0\0\0\0\0\0\0\nleftOutsetlong\0\0\0\0\0\0\0bottomOutsetlong\0\0\0\0\0\0\0rightOutsetlong\0\0\0\0\08BIM\0\0\0\0\0\08BIM\0\0\0\0\0\0\0\08BIM\0\0\0\0\Z�\0\0\0\0\0\0�\0\0\0T\0\0�\0\0~\0\0\0\Z�\0\0����\0JFIF\0\0H\0H\0\0��\0Adobe_CM\0��\0Adobe\0d�\0\0\0��\0�\0			\n\r\r\r��\0\0T\0�\"\0��\0\0��?\0\0\0\0\0\0\0\0\0\0	\n\0\0\0\0\0\0\0\0\0	\n\03\0!1AQa\"q�2���B#$R�b34r��C%�S���cs5���&D�TdE£t6�U�e���u��F\'���������������Vfv��������7GWgw��������\05\0!1AQaq\"2����B#�R��3$b�r��CScs4�%���&5��D�T�dEU6te����u��F���������������Vfv��������\'7GWgw�������\0\0\0?\0�ʻ�:�W�ꫵ����A��W���ci�m���m��~��Bv��\'+:�\r�z�e-��Ƴ�}�\0g�g�[�6Oؿ�~�Ӭ��d��U5����R�XH���c��\0m?��\0�5X�6�۬�u[�7>��F��*;k�uWn�?����\0��\"�or��˹���2���+c���3��ا�}_{-�\0�S�\'ޣ�4M$Yhu���9�ʮõ���]Ogڜ�\0R���տL���e/�������1�c��F�)Ƨ}{���\0�����\\��\r!�ݎ��Xb���f�E��\0m^������G*��K��n��촖�[�anm���3�c1�5�_WյI춙[��5�i%����ln�]���=?��uB��ikq[C�}��a�{�i>��]k\Z���~O�{*ĺ�n�eX���f@�$�cC��C�$����h��d?߫ӎ�\0�:�����Hd��&��[O�ƶ\Z�k�=j��c}]�~�������~�������X�������T���~�͟��{�\0I�}��ERe~�Y��.c�Si!��u��Ȣ����=?K/��\0�{����2K�;Cq�q���S�}Of�K\Z�i����ƻ��ٽD���U�sh{��V�\n�E�ߏ�H����\0Z�������+��!Uux�Q]�PXk��:ǚf�O�N[�s��\0�X�\0f�^�e6��E�k�9\Z7m��C�.�+���{�߳-�}i}?������^Ó��G�C�$��[��z�g���������o�IZ-~^M6�oQ�X��������~������\0C#����z�����z��hƺ�˫��#�6�����۽[�K���?ӻ���}��(S���]n6YnFU����[g�[��{�5�m\r�e��:�~���\n�~%�fe}����)p6mu�a�&ʘ�^�2�w����\0�R(H3�]�\0��n�02A���nO�q���\0����7���?H������r\rnmom�n/�Cq��N����~埤�\"�N=W`��#,zh�Kks��s���=&��u/e��m�%��?Ed����猬���5�z�E�Ǥd��k=O^���-�U����\Z��2썍}�[��=\'��ƶ�s*uoe���mY��W�����bR��6���O��6�WW�[��\0��*��\0��\0��n5���[v�lu�ݬih{�n�\\�W��{w�\0:�C�\Z�x�n^\0sMo��t��-~�n��cZ���u��������5t>O����e��\n�B��ڝ�:}�Y���ޣ��]��w��L���\ZH�ԯѱ�����Dk�1.���̖�����,�6��]���w�~g�EDفM��I�Bꩦ��ҁf�f��k���_Ի���\0I�A}��K��F;��y/�Z�=��=V7�ٷ��t�]��Я�z��ˤ��X�/����g�jm���{��ُ�3��?X��?Kuh���U��{};1����n=[E���}����������Է�Qu���9W6������U��dc�r7���S����W�T�k��W�e�O�.Ӌ�ʩh� ��\0K��5ͩ�f5V3���Y���Y�l�\Z�����|8ײ��P��ks>����S�T�q�n5�n��v#M��̓���Hk�VZ�W:���%U�����e��6�>��m����[�;��cn��1�*��.�mv>^M�R�eM�����p��z�2�\0�lg�?��e2�1�/��P�[H�8��5��=)�m5�`lg����\0�e��}G���{���{�\Z�ᑊ�(>��=+�*�w��\'��꫷��������6f�m��\rg��7��z��>�j���S[2�َ~їm>�M�\Z�#�1c���l�g�,�}�f�\0�^���,��i��󭶆�����n����?I���]�9�����e?�kNjkCq���.mNǺֆ����Ȝ]���V�ׇ�����?��K/����~Uؗ=�.�)��u�\'ۊ�i������j�\0�z�L�;�ǳ5�k]�*��xֻ1�\0N����_����?�����%6�����Km\"��:�={���[w�?�6%�o���m�w��E0盳��ِ���S���[m�\0����U�\r+�K,\"qX��Me�is.ul\'&��7�]T�7�u���^�JY�����.�K�]��v\Z�����\0����?��C}4F�l�z5S�-���o&�\0�,�7{\Z,݊�_m���eX_���Y?��N,���&�Y9��*����ǻs=����U��G���j����{��S��\05���V�S[6�Ϫ�)��Z����\0�zY�������h��LZ���\n�q�[����C\\�]E�z46�ѿ�*�)ǿ\'*�U�vF�\Z��m�ضzή�]��W���?�Ea�x!��g�p�1�e8�=�zW��M��}vS������g�%�����z�J��˾�_Rë\'��>�͙f��\09M�l�q���\0�3�\'�F��t=_���c:��O�[�z���ֵ�\rs-slv���mgЪ�R��U�K�Z�����q�3����h�;]�ҩ��=;�/�4���R�IήM4�~�s6Z��\0���7��O�~���k�q2I,&�S��4�M�5�����ϯ�\0��32]UMv^-՟J˚��Qs]����g�nM���9Ev{�\0��Y��n�?%�.elum�9�E�����s�}�2�z�h�����ݎlnnfm��5���:�6�n}L��?�f��,���_�M�8�;�X�\\˙}\"���Y�q.�][��w��\0ϩ)|۱uyU�<������Z�k�k��+֫���\0\n��3��\"�3M�1�k��5_�]][j��oٯ���t��fc��-�;�q�hi�ʛe!�}[��\\�+g���?������VY��1���:_c�����h�{��a��-^���?�$��l����N�^����zz8=�����W�K�5�e�g�i���uSS�E7�\r���9�9��;��3}UUCk�����\0�շ��\\*6�Sq��\\��E��j��c�K�O^�\0�{���g����im�OkF�M2\Z�l��Ys������̟��A.]l.s�lf3(����+�(%�b۱���Vھ��������m��m�`�C�j\0X��>�+k27SE��}����k}O��˺��=7G���x��z{Y�������\0�L+����7\"��Vc�a{}�h���w���?欳�-oI^\r��qnHa���k�8䵡�����X���܇����\0����i{��F@�����\nl�����l��z�S�\0]��a���e�ٗ�2r+�l��略Q�+Pmu����g���ߤ��O\Z��W���CC�Ml�{���	u~�7_�\'�W���\0	��� k/��Ci���lp�����}�nQ�g���o�������F����\0�{.��Ua��̆zA�=V{m~��~�+��;�����+s\Z ���sX���;�Ko��d�َ�Z���o��������eV����mcMV���z�������Ʋ���6�\Z\r�m\'��Hȷ�������?�z���>�����ޣ�,���a��ļ�6_o�S���?��\03��������kٸ�1���8[,$M�ymu���/�v6�X�/k��r�l��K_�M-����f����ܳ���U����gQ�l>�6Z���/��{>�wW���[�=�\0�{򺋝E��o���:��̓Qmo{o��n�_�[1�Zj����CR�]K��1�Z&�Y��k�6�G��]���~�b�NG�_M9e�\r{>���c�8�m��Y��c��\0J����ٻ��qx}�ױ�m��k�\0���u�m~�.�/���3���^v��2���s��klW`�c��W���ݲ��o��ש�;%βѐ�j�f������Se�z7_�.���Nʙ���w�\Z���V�SZ�Zi�!�^6�6���uz?�?�?�lE	.���YnS~�-dn�\Z��͞��V[[?Af��[�\0M驰;(Xl�(Sc�n9sj�z?j�{\Z�������}?G/��\0ͪ.n^c+�U2��ɹ�����/s�k�{�?R�*�e?�=\\�����M���r��E�ӏF�w:�ֈ/g�x�6��~�}������II��z�\0��[����ʘ�\0Q�\0�w��\0H�nM�R�8e]c����eu\n��7~E[k.�W��o�������8��Q�̖��+�����Ƚ��\r[�mU=���6�[{?K[����\0�3�,fT�ZE��ii��x{��Uf̯S��в�=�̤��0�X�e�eN�cCXE���+������l�>��?I�\0Z�Qm���cѓf��D���ak��X�sj���1������z�\n�-}�ˢ��uuz��\\4��Ӯڽ?Smv�R��:���ɪ����v]��2���1��)��`�N����e��?Q���O�y+�Xur�]�hk%�}����4���k�ֽ����\0ū8Ϊ���cdё2��k\\�����ǳ��#=��E��=+.�\n��g2�qoĺ��k趻o�.�X}<l�6�YS=l���:n���\0��JF���mñ�������m���Ǻ�6۫o�믧#��ؒ��ò�F�a��ٮ�@d�`����e�q�G�����ӫy���=��������\'�u}U�c6z�8U1��:s��&���eѴ<3�a��Ͷ�m���v}��)�u�ߓc�m����\n���3���-��V�_���\0���j�.�{�~�0���pm.u��\08�w?��\0�*��:u��5ٝK	�I>�yT�C���}M�?�tzi���r=\nE�k���хW��w��~KjߍU���i�}E[#+\'�=��MO������wC[.�ؔ6�������\09��\0I��F�2�\'������n9qc�w9��Ѡ���3�ߠߧ��v#��WvG����XsK�=���%t�M��g�=?�oM���}�M[=��l������7w�_閕]~�5x��X��΂Ɲ��S�z��}u��\0	�\rWl^���0Y�[��sko�ZH,�����?I�7��������M�η�1�s2�p.�uS��=7����=o��CY�Q���;Ul�� ��X���-���5����o�W����N2^�k^r�m�72r{Yc+�ֽ���W���\0�eȡ�]u�\'�ޝ���n����~�k��:[���M~��.��C�X}�xs�Յ�ϲ�J�~��:�v?,���=B��������m�ow���?����_���l{i��u�h��n-k��W����}~����%u�Y�p��ƹ�`����om����=���u)ߌZˎF\0�\Z�*���5���wl����F���/�4\\|&b8X��\'�m���փX~�䆻��O���U��~>;j�\'$W��{ni{�c�m���{������\0�}ޕ����T��r�-ާ�-6����m[v[[��\0q������=*�X��5�p�dF�p�]����ֻ��\0��\0�X�.��uli��u�8\\�e~�lg���c�����O�����n�L�r}\n�G��{� �ls�����\"Bm�N6�k�I��%�\"�_�;k��~�ޖN�����z_��!��1�n�ŭ�[7�ױ�p`�e^����\0j?IU��*�Wn�z�T����$9��\r�O���KwS�\0g���3[L�A��s�֐ּ�ۜ��{\\���\0E���$$�C��N܊1�[������m��]WW�ͫc}��j�������mŹ����-����6m��W�e^�Q�g��Lq�!�\0f����ml�5��\0m>���U�X�U����D{_���H~C��+���e�Wß�+۲�:��V�\0_�?i�\Z�:��C=z�4?\'-�vMLc�{�\'YG��;��1}R܋������ن��3\Zz���J�݇�c�Ʊ������Q�uv�\0;��\00W.���5Օk��s���`\r��>�U��Ӂ�_�>���1c>�~��\\��lF�� �������F q�>��\0����\r��վ��g��}>}��������z�6�S���F��_�߿���E��J���>��������ޟ��7���n�v7Н�\0���z�Ul��-ٷ�7dzq�k���\0��^��$�W��E_����������%���ߴ?����\0�Fg��^���zwz;c�~���>�Sw����\0���~���S�_c����i����͏W���?��)�^����c��tG�=��$�ڡ�}�ԍ~���?g�0w}r�~�>}}����,o�����k��C�Q�>���{~����G���[wG����s�o��l�K{g��=A�Fͻ��{���ڼM$~�گ�_dw����D�>���}�=o��j���}�n��ϰ����~����i%��U�>�w�����>��>ɶ5����G�=o�=�77�o���[>׻�x���\0Oӟ�ѿ�_W�7�k�\Z�t�������\08BIM!\0\0\0\0\0U\0\0\0\0\0\0\0A\0d\0o\0b\0e\0 \0P\0h\0o\0t\0o\0s\0h\0o\0p\0\0\0\0A\0d\0o\0b\0e\0 \0P\0h\0o\0t\0o\0s\0h\0o\0p\0 \07\0.\00\0\0\0\08BIM\0\0\0\0\0��\0\0\0\0��Hhttp://ns.adobe.com/xap/1.0/\0<?xpacket begin=\'﻿\' id=\'W5M0MpCehiHzreSzNTczkc9d\'?>\n<?adobe-xap-filters esc=\"CR\"?>\n<x:xapmeta xmlns:x=\'adobe:ns:meta/\' x:xaptk=\'XMP toolkit 2.8.2-33, framework 1.5\'>\n<rdf:RDF xmlns:rdf=\'http://www.w3.org/1999/02/22-rdf-syntax-ns#\' xmlns:iX=\'http://ns.adobe.com/iX/1.0/\'>\n\n <rdf:Description about=\'uuid:bd23180e-e7d6-11d8-bc4b-eba1eb0a597d\'\n  xmlns:xapMM=\'http://ns.adobe.com/xap/1.0/mm/\'>\n  <xapMM:DocumentID>adobe:docid:photoshop:bd23180c-e7d6-11d8-bc4b-eba1eb0a597d</xapMM:DocumentID>\n </rdf:Description>\n\n</rdf:RDF>\n</x:xapmeta>\n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                                                                    \n                                                       \n<?xpacket end=\'w\'?>��\0Adobe\0d�\0\0\0��\0�\0 !!3$3Q00QB///B\'\'\"\"\"334&4\"\"��\0Q�\"\0��\0\0 ��\0\0\0\0\0\0\0\0	\n\0\0\0\0\0	\n\05\0!1AQ\"aq2���B����R#r3b��C4����S$s�c����DTd%5E&t6Ue����u��F���������������Vfv��������\0\0/\0!1AQaq��\"2�����BR#br�3�C$��4SDcs�҃��T��%&5dEU6te����u��F���������������Vfv���\0\0\0?\0�m$S��S2�6l�8ǓO:�SSu����Ӳ�2F]�\'���g~3qz��;5��[�%��m������Ɉ}X�h�g�$��o��|6b/��ꫩaD#T�G,�C4v�\r�[Z@�%v��|0H�7/G�KHVһKv�2 �u�r�3b�\05\r���#�\nh4Zs���:0l�	���K ؾ�#07H�D�\r�G!��w��\0�v��\0�[\0����ot\r@��^\\���P�3��P7�:fs0�A��)��Y�K�s*Ԛ|ޛ�;�P�OV�Uy�	�P��N�\'DCTӉ�Eqiq�4M� h$,��4�r����H5h�\"H7�QM�����\0�e�\0I��/\rn��H��4�#2	��=�Q�P �Z�#�@��\0%�|�D$w�ڃ�n�\Z�ֶ�{��At���`	�K�V�0ǵ\0�L���g`@�XGx�J���\0@&}��9A5��<��:�aS+Nn_�\'�^P���3�	�Ij����MXM%���e�4E��,D���־-��T����bGyc:`e�P�����Bq�r�\0��@�\Zڙ�wށ�:�uy�q�5�����D\0v(��j���i�y}0?�YE��*�v��K`��\'��E�\0��V����@u��`w@��T�iE�DXD�����\0�@�&1#e��\'p,K,bh�&1�A�F�����@DR6�����p� \n����@2�K]d	`7\0$\0��\0�cɸp�iÌ���?\rѓR�*����E8c NG����AbY\"��x$��@�i��ՖZ�P5������s�ʁԯ<2J@�6Ӕ3K4n\Zۦd{�9	Lr3���Q�D�����{C��������S���\Zm�7o?��$n�j�43W�伀�%�.�\\9z@W�@�7G��q�H�^s����4��o7Q��|�Q���K�\r�kppШ�@�e�y<�a�Ϗ��w=\ZR��X\0��@��Bd�e\0yL`P�6�J\'|GhMǩ&0$���3����g����65h��|�g,A՘��qD�@�Y\0��֏4��ܧd�r����@�6)a�`Q�Lcg�Hb��U��2�\'&+ �J \'�ayd��+��� �r�,�0\0�Z��\Z\rZHK��LCɄ�ؐ��97\Z`4�<8	r�{�2n�v��ł\\��l��qM���N1��f �#W��Ɂ�d�w_\Z\n�Z��9=�Z2�F��@��4�a�=�t\Z����=�4�X�=8�<<ф���聘Ҙ\r� S#q\Z�	�~�Ṡ�4����7\0��@�ydJ~z�Q\ZGl���# ��O����B���p�$�E4\Z\0�܇�T��]vfp���B�wy��ӱ��G�L�37ܽ��X�%z)o��͹hOx���qpQ�\'җ� ]��ȑ<�J�#w��q�i�z�l��O��d��a9\Zs�x�;Ʈy1�Ё1�$-����Xq�j\\���C brQ��b�; L�&b)�D�|C]���0vcy��c��)��(.�y3����痐���o�VO�v�3���$�̜\Z9�3��F��1�������p�⏂�T��C��8�ڄz~��Q��Д��\04�L{���!�`F��ybf`\r�~���yFC�@���$|�1  \'DĀ�R\Z�Ô\rf�A�V\0���L�F$_(!�QE�%ޜ9���/�-�>s�dX�L��+���ƻ��{��B�u�E8F94�H��T�j�.HԄ�������\0���\rZ\n2pɻW�C$��t�%\r4@�j�#}�\"{ n�.~o6��!�����h��q�g�L#=�N�Q�{A��6�@Rb�%}��@ۻ$��;�f?�\r�K*�Ow�a��^��w8��Q5���bUh��\0R�m2x�|w\0\r���\0�z6G�\nг#�[�0ˌJ$W!N�,3�{���4`,j��8@�l�\\�?R����0����N�\r\01r�͒�&H� ̝@�IF�*H��D�ɳ�Њ\'��R&tՎ�>�s�\0��f\"��L��\'��\'��FY���\Z.�I�&��c����R)��6���а���:�Ϫjn18�����Z�ˋX������\r�*�����\Z��ѡ��	�h|w:�k06��爐\n�1)�K�I�8K���s�(�xѠ��m�\0���5/D`Gv_R7]Î|�N5�>e8��X�:�ḱ�%zZ$Ǣ.��\'��`(��(��\n4i$3�7��,lď�헔`�\Z���S(�ݶh�>W)�:\Z�dP#�����uyp�� ����(O϶�n��a��\Z�}+�P:j��(q��d�\r����,^iS�í�9	�<��q8�6��)pi��wv�9�7ͦ8DM�wE������a���H@Lr\r�Of�1�i��&  @�@�ԉ4ءe�� Z+�8@��b`����x�\n�\' Q;mH�|ke�Z�jڔe)DY�ζ.�\"B�dC���y��Y�s�^d\0�0��q������\Z��hJ:8��a!��ٰJ9�h��t�q(N?r�]���$�F����h!ҙ.\r��\\�@����@�J#��Z�v�\n�M4�#(<������9eE��kC��e�Dh�3�Ki�:J1#VvD�HITI���=�oC�f8�\0�@$#ܳ�����̌�kOLw@4���\"5@�4�� p]�LX�8�1��N# H� l3D�\r�/4q��mT�b�(����w�/4�U��\0I��Y֑?��\0%��G���Z@�1�F�}�o�,Of����t KR�a�r��vM$DU�c�\r4\ZB��I\"��@�S8n�����j�ˈ\\E��#H�퐈�M�������j�m�@�U��[�t.q�F�^���3�*\0�⋷�h���9d�e^!�xK4b-2D�Li\0�(��ːƨh�x�彷�@��Yekm�j:���Tl5�^�j�٘GD��\\�e@�/N��P8��~��2�ĤE�b1\'�^gQ��de$�#ˡ�b�ځ�≹F��\0�t��M��bD���m�<.:v\"N��)蠃A\0Dr;1�D��P��`9�Q�LC�	m���2L&c�4�K��s��\0�_�Q��\r��I��?����d��%��d��!;ۤ�b/��\n@�l�\\�%|�	�Ǜ@$j��G�����; L25\n2_f�-�5�c|�J�j �4b\n0�\n}A���4p���FF���m���@�㖌�Wj̉�׃=<�1���\"�a}h���5�܈��P3� Z7�˧���դ�r�)��|�h��y@�-_��(�4A�ݸLyc$<�^������l��rD~m	��[�P3�AGP.<c���1�G-�\0�@%v_^&#��u8��ϡ�{wŅ�F�Owf��j!��#-��{�N�D\n��#v�rbp��<ҭW#Z[�+��E\rP0��4t.y�)��m��i����r��n#�k�2y��761��0�YG�D���mD9�)V���R8��S�b`(�����wb=��1q��$엋�S\"�(�	WtHrm��2��]P4D��5��w�-剔�j���05%��|�:���h:g�B\"�����1�]��r�\0��ϣ�|��Y?�M�����P׭�S{pl�9bEL�\\e�ޜ#d��_\'�I�ЇXgl�)�pGd��]�x�q���Ј�u��1�7q��B!��Sx�#�3��.Q�����R\0�(��]��\Z�}��$r�xt�؋@����L��(�\"q�n[u�)Kux�����N.\0�]�e/b�G���S\0|2�}��NBd(1\r��v��E1�A�Fr�v��W!ƪw�N���*R�NX#�$v�	�� jg*�q����t|�@sA����M8��0o���n��C�ځR�NX�p��R�3���Q�}Yx�r?������\0�/h���e�||�sY�LS��R1��@��K�=K6tv�����q��3�\"m�����@�yce���C+Ga [�#����BBG�Z�;���* �r�$v�B���@��\\1�O$ � �\r��9\"d(q�di��Ź~�;��z �%� ��&_�=kq�$��� i�\0F���dc\\4rOO*�����8u�cu݂%��(\"�U�rۓ�`4�AA�e�p3�m�-W��\0S]���\0�����ƢL�:�7�+\'�������u����T�r��\0��2�@�\'���	�A�y$e0by-�3\0D�H6����&{�֔�l�Z�H�!�� ���(�/6\\���r��81�(�2w0B�,ю��9��\0�a�8ϯg��\\�HY��J>	0\0�Q��!Ń�	�7�D\"u�Y� �Y�-F\0jŹ���d\rg�HۗR�/�u�\n@�㺴���2%%��%�@ké�f�\0�\Z�,�;��u$hD���¬�G}��\0	2��ӆ8n��˽֏.9Q�=����Џ��1ǻ�r���%| J>� <�%	L�i�\n\"�$�C��l��3_!>���@�ӻ�Ȗo�ɻZ@ڢ�1��fVi��j�wGic!��M�A\0���,d�.�z�D�\0��S�|W{BW�F��|�@��z��@�	�<����d�� i(��o�I˔BcWtr�dq����=hkݩGp��bG�E�)�l#L��7?cz����c�NB��c4\'Gg-U��U�;�o�VO�y�M��?�=�	Bo�c���vd���	�b�C�gk!��c�b˦H@�Y��AvF%�\0�Gvu�@��_f�<fR�R$B!�A��g�Re\";͋|LY1;}����0I�$�x\'`�s͗Ӎ�W|���#o�F��2��\Z��X��@I�N h�	�tt�h�;�׹D�.�z�)hƴcщ��2g�$� u�*��	_d�g��h`���6h���K�b���0@r�B\'��nhΨ@�	����tr����X\Z�OH�:��$��1쁐�N��${�qd���[bni�{\'e\"��	\Z�	��0Ҁ܀@)�N�3�\'�&����Ǥ�r��F�#���sH�0�Y���R�#!�5R�kO0D3��֖�#(�hh�M�>^P9���;�Z1�A:p�\"K\'�:�U\'YA�Q����Q�2\"�l�v�D��=�1��\"G.s\'C���1�[���2���F�F͠i��d�,Ou�;�\ZH[Xn���6�|_�\'H���ꋸ 9D&�y�1\'�#�,�DP۬����D���!B��*j7H\Z��A�\Z����2����gnf]��9\"Q�Ƞa1��a�a#��r쁕�\Z�$\"^|�1/�Hm��e�����\0\Zd�H�}�zaP1�#(߽�\nc6���ٌ��@���֜������GVI��j$ֺ e,vD�gY�B&^4D�Yd]�	Lx(\Z@�\0|Zs�\'���;%��\n��*Z�K�@��s�]�RbX�K�Q�?P[�9b��+N���HM1�4��[�����?�� n\r9��f�\0f�l>,���Y���ܶ���*�1�/\r��39��=ٌH����)��:&7���:c>P|8�r���P�d��9bx��,\"\\�\r\n�s�Gt�MZ.B>%�E D�T����v��щ7��ZSA{㫄s���N1�y�´���@�s\0[1�:�l���d���I�m� c�7Z�b@��t�(��\" 0�\Z�d�铌�\r`����$�����ў���c\Z.\r��\0ۦCZ�?<D�t���Y\r���F;u���:��! �=�� r\n.������q�wh�}AZ2r8�	�⁮�6v����,���;8�h�>)�!�W�q�ˌ�پ�\0ā���G���Ǉ��7v�\"k�\r\0\0���	_�g�ώ6�[��✣@x�XH\'T�+W;��6�qQ�z�kU-͉8헊6���{fV��>-��,d�$?F�4Ɂ����i���U�`�@�&T4�旛^GJ�\r��#��y��`�K�]�\r9H�}?6�I�#�P1Ǹ�7.�\'�\Zj��ߦv��6���@DZ��x\\�#�\\#~.Y�N2:�P~nR�����I��$\r�[�N@;�Rv hg���H���,��Āud��[B�1�����B���\'h\0.��&S��%���L���1z�v�耓ma#t��N$�$�~�6�g��������\'Wx��x���Ɍ�\r7(F�h�P]/�v�aB7G�rM_v72f�Y\\��32�[(�&�9H^@ҁ~��>���E��1!n�n1��H�\0u\rKe�B h����y[$SG|��9y�!n�!\Z������A�g-��1\0�hb\Z��`.� �e�ۣ�ba�?��N�(�ę�z3οi���އ́�5���\r`��w�N����T�ܠnB,NP{Z�17�D����J>����`ڞ�3��1�����@�$`ݷ�l�R�$m�bl #E���9b��\\�2b���L�#h@��@15�4�#��4�B:��ܶb%�1\0r�^���3�I�9��H�p�)�u%�1�O>8�-!��xI��zv\"�\nc<��Ț���gn��~³���Ά�H#!�Lc\'h>�oc�#�R��-ȧ2���2��������\0�s�\0�7�)���N���\'�\0E�2��԰]`\\�cGʡn?̿bȐS#��I��V�;m�,�be�%-���,���(�d\0�HiN��h�#*L2n�Q�(\"����C�,�g�)���F�w\r� s��/���Y9Fo5=g&�)�\0[�hE�I�z������^�p�\0\"9DŔȆ(�F�d��A!ޢ{1,@�@śN�i�Lh\Z@�h�����c\Zˌ#R �N���w�F\'Zc�\0�oN#AÌq֔�uo	y�ˣ�sGż�4�����v�bI�D�1�6\n���yct�Y	���|�g)G�}`\r62�)!Ň�����iL#��@��-�%|������mɐ�>m���>)�l2d+�H�\r�f@iQ��H�(��r�N�r�����k�j�y�{O��ِ\r�Op���Sq�E\0 u,����E�4�2$�;ev�-2%kR�]72K\na2F�ވ��5n0������\r8����������f���\0��.c�\0�����g���45]��Q\Z86I�%>���h��H�üE�b.�S�q�: �7�^э�`�LA��Xw$Xc.a�Y��fp�)}�<���3H�Җ�L��DG�(�vlh�eڝD�d�\0d��<���{�]w1D�e�v���j��´F[5.�4d4p�f4�b��b\0�D��]�N�.��y�V���ږL�Q+S�D�n Z�\Z���%	\rA������7�^S�I�O`���xs��9��c�%�B�\n�=Q��ofL\0@�d�k����\'(Gq��D�\0�M�Qc&m�\0	��ysHD�Ձ��4܅��8������j����ك�{@�����Ռ���D�uֵ��S��\Z$O^�f�3��k���w��{�\r31��lo�M�A\0D���YN4GW` 6��5 \\�A�9,\ny�-Z���	���� g�bL��Y�f���ŠD��q�1�/I�|��)�{@s���ru\0yGf�ZB3���\0�7�+\'��GRc���|��Y?�dt�����%�p���Cp���LS�\0�>\n)�\n	\0�y��Ht�ΪA�r�N�ny��ƽ���\'R������c8���i�/b`̝[�4����9�����r�ͷ���dT��{9�)2?�h�c�M�!�@�#�J/��A����=3�`^<���铔�����mH��ZXq����L\"\rG)�bВG��;H�&��b�P�G���2�P��F�b�����j1Gl���z�pB{����A��FI�����.B[\'-<�W�Ԇ�ި>,z���B1߯��D�9�x�?��\0%�lS���@M r��E�E��̝܂>�[x��@���9�fW�/a��@n$q$De�n�znU�;��sb3�� ���rn�@�(��^�HJ\'�.��%�G[\'~�����Ѡ5h0Ǌ@�_-y�^dX��)���nLuY�D s剗N,b1��G��rɲ�S�$5�vU;NV�M���H��>�����27��z�7�+\'�	�FY���:Dx��4����f����d�ch~lt	N��Ѱґ�$N�f;��h�����)H�%(�Kwd\\���H֑L�)��2FS���B-P�@���Ex4�{K5�=i�r�3�w��Tz:{�ϸ`Op�����1���p�H(��RbM_.�@�iG@H��[$ g�M���mbI<#&���N��N��@��R��|�4F�ǔg\"\"MkO&9�@?��B@(�6����\'#�HH`32�<,���h�E9��h.9J���/�c�����G��Ov�c�K��JG��L�A��D�.� s܏!����,wM as��t\"{�.� D7�gx���\\�J�`2�&t\"9\"\0^��:[bQiNh�yXb��%���s�hB2/6Lgtuz̃��(�Ô��OC &�`/h�4`�KR�.�q��\"Q��#X���Ŕ�8����o�VO�n�[���o�VO�v�=O��\"N�ZQyAuݣ�e�-U9����t���R����!-���x����p���6�@�qN��5$4P;�c�f\0���r-�w�\r�!�K���P��e>h���z d28*en�H��M��#�# ��h9c <�\\g�[��`�\r��v����ƯM�($hY��lq�.��Bq��\Z�|��@�J�3�w�z@\r\0�ͭ1ȓ}޲\n�)�+G9�܇�Nc�giq��gM)�%��N^	3>\r�L�_	!��p�\'A$1�;����F4�@����%cG2ɣ�@��C@tu\0�\\���=5@��5E\0���̈́v o�J��d�(A�JN����(2@�4A\0�A\r��9O%0dD�SA�Z��$m��h�\0��NŞX\r��*�11���>b�H\n�<��L�ZG�Vr5H���\0��@q�H���0#R�&TuF�.\\�H��\Z q��OA�f�\0�d�\0�7�)�=����\0����?�р)Ћ\Z�s �!�k`g�]L��\08�Q=ˡ�j&�`j�&��vI�{3	�(�2�\'Ɲ�=��)��dE�43x��c�(	�\0�S0�&7����I�g��0��(�W9f�E��N�Ѥ2�!o<���z�9V�9OJ�c)Dh=��[�P JŹ���@�(Gu�4\\�2�z��vG#��38�w\"�9�F���u��]�Ǩ��ߺ�p$Dnٓ؇r4@�3>o��,HRrt�*��Yh/$�����M��n2(��H�C�� i�� V���L���ݼز�)ؠn/�`�u��R��@���\"�%�0t:7` g(�m$\n��.f@p������ƏȔ�X����g9\0;n��\n�\Z�b�<[�o�/�4�B(��5y�d7�@z��G���j���\'>/a��Q>��\0�,����Fz1\n��	< y�>\'���3�ЛÓ�/wA�f�\0�d�\0�7fO���^y@u���kJ��Qq\0���4�0�\Z1}��� A��1m$۬M���`��Qn^�����\0]�\0�9`��&��0�q��0e@\Z}���]�y�dL�\n\n�	��e6�i@���NC\\������ ��7J�@�o7M9�|���Y��_�r�$��yj�ܻ�3o�@ɴ�E�r7��t�r�H�T�%K���dMyh0r�\r�r�8E9�1�Es�tQ�C�@�h�d*����ФXh\'n��˭SE���k���^����bCŴ�>�ѵ e�;�B1��u:��;Bv��(-\0�g�dA��@�&e�G��c\'d\0\"Y�,k��o~�7���)G�n(�p�?6��&�{9�wa�RP�6E�Y%I�3v�\n)F�9PL��0i�Fͽ����\0����9�z�7�+\'�	�0��\0�m�	�u���f�.�\08ԯ��n(�sT����A-��\0���G�K��A��;�7�u@�iѴ��|6���#M��VGb�\'�@�P\'������I���\'EGB�Tu��ρ@ K�5*[>Dρ@��JD����	 r־�$�J\'�J�E���BH\Zn)��H�m�-\0�h숟jLH\n@�H!�i����@���Ҵk�mAr��E�t\"���Z>b�0A�X��\Z\Z���=ÀP;7R��\\#i�@��$(���|i�@�jI)�{j^�\07�t�����l���A{O�A�/j��\\�eZ���P4�`�h#��Wi�(h1I�|��QB^|�\0^ݴ8y3BF\'B�yo��|��Y?�<>��\0�_�_C���2�:����v`�����}��T�C�j�����}��x�����5@�Ŀ�|���}�W���P�\Z�}��T�W�����~	P>�$��ީ~	XS����ޫ�J����\Z�}��T�K�J���~\rP>�_�T�C�j����\Z�}�_�T�K�J�����}��T�W�����%@��߂T��','����\0JFIF\0\0\0\0\0\0��\0>CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), default quality\n��\0C\0		\n\r\Z\Z $.\' \",#(7),01444\'9=82<.342��\0C			\r\r2!!22222222222222222222222222222222222222222222222222��\0\0A\0d\"\0��\0\0\0\0\0\0\0\0\0\0\0	\n��\0�\0\0\0}\0!1AQa\"q2���#B��R��$3br�	\n\Z%&\'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz���������������������������������������������������������������������������\0\0\0\0\0\0\0\0	\n��\0�\0\0w\0!1AQaq\"2�B����	#3R�br�\n$4�%�\Z&\'()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz��������������������������������������������������������������������������\0\0\0?\0�55o)�]b[F�J�GU#(ˑ���������`�Y!���BU2�&y����=h��&�vi�\\�V<z\Z���0�1�\0a��$�H�^?�j���(E��F�]Ė�� [-�{�؎���X{�4YHr����c>��V{d�{�:��VU1�lt$��N=i�J��e�yn��0�ĩ\0�I�$}��9P�m� �K�ק�s���)-G�spZ��lm�teF=q�>�}\0�Z��e��Y���]����w9�����&��M����ɰ��f�@�\'�B})ho�	\\ϐ��Ǟx������!Kx#�c��_-T�A�z��^�HI3G��HQ��9N}I���Ig����{�0۳{d��_Z\0e��V�k?�V;tG{��m�x��0	���s�����7�#����ч��s�*KƜZ��Z��Ϙ�b&E������}yXBC+#�BB\0#d�>U�9�?���cF���D8������VN�>�O�&7p��eb;dn���+B�x�e�^ݚf8�����c�~\'ި�G5�S��9@���n:~��0GS���i��b����c<�0D��d}��)�me��5/1��o��#���pO���Eu��G$�&c^=��en�	 }y_cP��pe.\"�0w8Fa��<���}*;�eM�_�0�*��\\�$pq�z��nm�S��G-Ē�S�|t�>����i�2#h�*�2l�����ݷ��c�7��@��#�,�J�28�������ݤIm2�6�Y<;mۓ�{oΣ���R�cI8,=q�}q@n����{�#g\"��y�v��N�\'��e2��Xr။N�ʣ�`F�)�#I�-��\nB�(.��(玃��4�T��Fv�Qԑ��?Z\0�M4�$�/#t��37/��	������Z��H�H��8\n�\\�烎�Ӛ��r�\\�\0}�R5P}I*	�|ǿj�Z<M�|��1��Y�Np^}��b;K�m�Ln������<�O�J����g���b�#��u}9���Ks��4�~ՎE+\"r�Q�Q�9�	��\Z����\\\';�,Ns�������$�w�=�(C2�f����=v���ѻԬ�`�D�9#$�x|�v��xc�o�4QD6���eRjFx�A?ýd]H|�%�À>IF�0?����oo�\rU:�B�C�<@F	|��9<���ri\Z�%���q~���m-��>Pj+�)Y��ٕ�^Ik���w\0�09�lg�o�v�K��[��S��I%WwM�c���Ŏ�\\J�y�p�\n����ʵ�lm�2I#��@�B_n<�s�t?N�ʎ�F=:�EO��,,�H�gPW�=�0��v��GMX�U��6]�\'�ٞ�$�^7�Hp�Y*q�ڲ(=y��d{v�Vh/u	?yʤI\"�ڜ�ߩ������\Zl;Z��rNp=2ǽ6ܡ���9c��l��#,3߱���^�<���ql�rz�Z/��e��W!�;���@��\\@˶�LL6���F[co=y<����-�yS�ą�m�c�0?\n��	m�H���W\n����}�##�LԚk�� �b\\��36N\0#��\0{�q@\n!p��a�t�� J�+�ԃ��M�sI�K\"�uXaP��(�^8���v!�m��&ՠe�9�q�1�Z��q$���cVbmN���}zc��\0�\02��������2����i�wϳsy�p2p��\'����x`�2񪼉j�g��:c�����$��ъG�Bx�\'�S�>��{�`����2�c��\"ĭ��B2LQ1��G�ET�[ �Oם[r�T��\0ۼ֊��Q�6����so<�8��\nY�ݽ	9��Dh�#P2��6��?1�ֱ���ۈ�����@pL{V�+�K��p\n�s�=?^+��#�F��In��y\nҐW�������RM�{y�e�	q������5a������p�H�~���D��6,F0�?�\"���9��O#���\0��B;i\" �@E՜�wc�|ƣ�hZ9\"�\",\n��� ��s�L7���:)��X�ǧ^�ր��|�fyC$av��:���\0㊜Io,�ll\0	��;�����~��?���U|��W<c���:�Ԃ8�n�W��r���)�\"�iq��q��O?C�?Z�ڜ͑�m�\0�)��dt�9J�F��g-����@��N���ld�� �����\0?� ��{��v���eP�I	ps��}�	����L��ep��\'�\'��-+�of�YC(Y\\H|�\0�x�OZλ��\n�#N{}(�\\����^��qmn~q�q�n(��d�;V���X����#Eu��G����R�\0�E%\r�!R����\0j��}��QI���M�Ɗ)��zg�~4Q@��Z(�	 ����QVA��','jpg','charte.jpg'),(2,42,0,'Retranscription','text/plain','','Sachent touz presenz e avenir que en notre court en dreit establi Guillaume de \r\nRezay de la paroisse de Ceaux reconnut en dreit par davant nous que il a vendu e \r\noctroie et encores vent et octroie a mestouztemps perdurablement a heritage a \r\nMonsour [] de Vernee chevalier, a ses hers e a ceux qui ont ou en auront cause \r\nde par lui sept souz e seis deniers de cens d\'annuel rente desqueux Garnier \r\nMorin li devoit e soleit rendre treis souz e Jordan Perier quatre souz e seis \r\ndeniers chescun an en la feste de langevine sus prez sus terres e sus vignes que \r\nles diz Garnier e [martin] Jordan [] ont doudit Guillaume le sicomme il disseit \r\nlesqueles chouses sont sises en la paroisse de Ceaux Desqueux sept souz e seis \r\ndeniers de cens d\'annuel rente de tout le dreit de tout le destreit de toute la \r\npropriete possession obeissance e seignorie que le dit vendur y avoit e poet e \r\ndevoit avoir senz riens netenir il en a fet au dit achatour e a ses hers e a \r\nceux qui ont ou auront cause de par  lui pleniere e perdurable cession par la \r\nbaillee par la doneison e par l\'octroy de cestes presentes lettres pour le pris \r\nde seixante e deiz sous de monnaie corante que le dit vondour eust e reczut \r\ndoudit achatour si comme il reconnut en dreit par devant nous e donz il se tint \r\ndou tout en tout  pour bien paier e a oblige  audit achatour le vendour desnomme \r\nsoy et ses hers e touz ses biens meubles e immeubles presenz et avenir a li \r\ndeffendre e garent[ir] est celle dite rente quite e delivre e especiamment  de \r\ntout doare envers personne sa femme e generament de touz autres impedimenz e de \r\ntoutes autres obligacions contraires vers touz  e contre touz e toutes [segont] \r\ndict et [segont] ce [seume] de terre en rendant audit vendour e a ses hers \r\ndoudit achatour chescun an en la feste de Langevine une maille de franc devoir \r\npour toute redevance e reconnut en [for] tout le dit vendour quil deit et est \r\n[tenuz][pssere] e [oudit] la dite rente sur touz ses autres biens si ensuist \r\navenoit que les dites chouses sur lesquelles elle es assise ne [soffesoient] et \r\nnous ledit vendour en notre court  en dreit present e consentant rendant quant \r\nen cest au rente de escript et non escript a tout privilege dottez donne et a \r\ndonne a toutes costumes de terre a toute [decoustume]; toutes autres excepcions \r\n[jugeron] et [ condepemnon] pleingement de notre court a ce tenir e donna la foy \r\nde son [cel] en notre main de non venir en contre ce fut donne a Angers sauf \r\nnotre dit dreit le joedi devant la Saint Urban lan de grace mil CC quatrevinz e \r\ndeiz e noef.','','txt','charte.txt'),(3,42,0,'Sceau','image/gif','','GIF89a�\0�\0��\0!!!)))111999BBBJJJJBB9111))B11!)J))Z11)�kcc91Z1)�kc{ZRkJBZ91R1)J)!cB9�RBkRJcJBJ1)B)!1)��{{cZ�{kZB9��s�kZ�sZkB1�ZBkZRcRJ�{kJ91Μ�B1){ZJ)!ZJB��{�kZޜ{kJ9�cJ�R9scZRB9���Δs��c�sR�sc��{sZJ絔��sΜ{�sZ�cJ�{Zޜs{R9sJ1��s����kZ�ƥ�{c֥����cJ9��cZB1{ZB֜sΔkB)έ���{罜��s��kƜ{�sZ�kR�cJޥ{�{ZsR9�έ�ƥ޵�֭��ΥΥ��Ɯｔ��s経ޭ���k�Ɣ֥{��c���Μs�{�sR��c��Z�cB{R1cZRB91kZJcRBJ9)B1!!ZRJRJB�޽{kZ91)޽���sֵ���k1)!�Υƥ��Ɯ��{�sZ罔ZJ9��s޵��kR֭��Μ{cJ��cRB1ｌ��k�sRޭ{��c�ƌkR9�kJ��Z�cB�{RcJ1{Z9�kB罌)!Ɯkֵ��֥޵�֭{��c�kB��Ƶ�{kZB�֜kR1��{�sZ�ޭ��Z�k9��s��kscJ��sZJ1���ޥB9)έsZRB��91!JB11)�����JB)������ccZRRJ���BB999111)���))!RRB!!RR9JJ1)1!JRJ)1)BJJ9BB199!))!!9BJBJR)19JJRB9JJBJB9B1)1)!)B19\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!�\0\07\0,\0\0\0\0�\0�\0@�\0ň����LQ*Uz3iң6�<|��ͬ7\'al�&ʤ1\r��%1T����+L�/E*ɩhQ�%J�ܸ��V\'I�.��D��#l4��đc�(\'y9)��J\rߌ�1�V�rdB���I������s�ܡ4�N�8w�i&kE�1������FP�B�bQA�P\Zf,��dҪCV\rY�hѤ5���j�M��ZS���_�~�\"�*����x�0�8Z>�Q�/�Z�$P������9�&�h���L6q�4s��VQ88%�)\Z�\r6o����	I�O)m��Kr �I%c���r�!\n*�Q�(u��FHiR\'��A���\Zq�x�&��sQ�~���b,���Al��\0D�S����ɒ�u��%�\",\'R�ɇ�A�t�1Gl@@7�t#�g0���ܳ�e�َ��yO;n�If�c��mЀ\0�3N\00�â?<�&�B��^�� �d0�.��i1��i1?,@�6���6��S���`����J���Z@���+���J@���Y@���������j��F+m�j���\\k��j��ڲí��`���ӎ��{�����=��\Z/�����Ĳ��\00�8(�C\"7$�� ?d��Øn�� �\Z�š�������6\nh�$�l��ӲS�+�\\��.S����k@����:;�3���|��̳ͳ��z��2�,�����:�l���c�\0����,���\r#�� i��Bz�ےf@�Wd@��2�\"������M��`@�+S����\Zm̎�8�ڸ���\n�-�o{��7�{m6>��s������>=�:��l;U�[@�ͪKk��➯�Ϟ��������\0(p3	\'��3���Ó2s&�C�����C�#���w�K�=*+N��,��-�<��[�?��C\\��e���,t;���x�@��C(cV�\"H�h��H\0�Z\0\00�� ���M�\Z�H�_�R�L�B ��6�	-�bǈA!f�>A�l[���\0���?l	Qe���8g���f�\nݵ\nx@�ecl`=��3́X�b�b7�����Bc����O~�RW;��\0�@`�\001\Z`PB\rp\0԰3�!��B\'hA`,�\'Nj/x��N��\n�����-�I1�T��\'?G��U������Eխҋ�h���8�z�r�S��;]h��<ԡ�Y���,{�U�$^.��\\3�\r1�i\0$T\0�a!A�a\rjPC.ƙdp�H��B1��TC\Z��iL�P��X��6����������ɥ3\n�fPd���Q���㋫SD]W�wm���FǨ\\��\0�eGc��^��\0�d��L�ɬj�S�3p�Ƶ,s�U�0�B���#�����hą<��\'��I���ZpBz��0\n3�͟�;@�j�J)�\0[��*c)EW�N�8ˆ-�Hˉ�����0c�Wzx4�Ec��wp.[+�J�UL��Z4E�d���:�s������;`D�DpT��p�lP�&��\"q$�a��U\nT���uX���O| ��ኢ�F9Ġ�U��,]SyE���g�#\Z-�(V���|�]`�Xz�uh؝�9���-���sM%��!6q\\1�V���ـ��7�BrXb�� \nà! ��L��b\0��@����d(�������o܂_AW���̽<n�\Z��b1h�D[ɳ�b���1�p8V=����x�b��Ǹ��ԍ���*W9\0����Ꭾip\0X\0�@bP�ĸ�}��`��(�7K����p�.<AOlbf0*̠�@+�$���wd�P�T.E����>�p2%[�o17�����B�\0�~X�����w�B���1iU���Ns�7\0\0�C\nR�M�Q�A�`&�@1\n�\0hD+^y�,8A�$pb��v�7�,h�\0�zu��+V\'�e킟���R{9�8�k*V�W���r@\Z�\rF0�o������\"\0�<.ZH3-ܚw����߲���=Se�V��4��\03��\0�@��@>�`%PAW�@*F�\n4�ZP\\�U�B�h�*v�\n\\�a	��E7b�o����F7ޔ�b]-kU{LS�m��T���Љ��X��58����\0\n�4P��\ZЈD$��t$�8�4�~-N6�r۪M#����/q��׻�t\"a�k��x�k-��\"ϣ(�*��\ne�k�b6Ph8#�Y\'z7\"������3��2J/w�˝�P#j@0d�:��d�{X<M��ҿ!�C<�뱀F�����G��k\0۟O��W�ҏ����:vh��.)ȭlY���6f��V��!V��ǝ��x�uV֑\0r�\01p6�\0��p*�\0��h�\r	1�\0�1&�(� �(��� )��(�P����@1p75\00�PR�	���\r�\02\n�|�\r7���M0VP�q`��؇zqwY���uq�}M�z�\0z�\r�5W`c]�WDsRM�d�:��D�T9�$-����A��\r��ݰ\r\n r\'ʰ*���bou8��\rC�\r� \0�@���z�\r��@�爍舑@�p\"�\r��� 	�p\r��̀S��v��	J�p�P�0\0ې\r�@CX������pIPU����Gwq�tR~��-�\\�6Qx�ШKc8P�up4�R�?8�x��S�8� ��\0q�\r�0�pq���և��\0�\0/@?P��8)W�����\r1p�դ0ހ�#�@�p��@��(�������	���\r��(3��@��\0\n%���	�0=`�ް	xpv�	��nQ\ne��yL�z��7עJ��PluP��:�r\0W]����cVRd�Cfh�~*�LͲS��(�0�\"\0k�\r�io�����5\re�� �W�0���В�\r/���\r��y9�/ �p0��\0���p��\r�\0DPg���Ya���\0�C\0�0/����\r@���p�t�	v@g�� �Tx�\\��@!�3�QQ�Q�%b�$\\��-̈-�\"_}���x�� ���۰t��\r� t?`����p_Ѓ����	�O��\0�@�����\0cS6ސ��\0�I�/`\r/���g6���)��\0e�\r��>io�P�����`o˲>��dQD:�ho�V�y@��3�	��:�eE6s\0�u3�E��D��4;��y�qH\0����C��z}�*�@�PG�����\r�?	�y��`�� �t\r�ۀ�`	3\r(���̐�}8����@>T�G�5��5�S�B2�V�	uY*�h����$]�uUK[K9S�3��h�U��:@�.j�<�\0�x(P&aSaV��w8��0&>$��\0\r`����\0@� ��pg���.�	�@�*��r\r\'��0$\0�\'�@/�2n\'�^�uY��PԨ,z@u�����$4�3�QQ�j����������J$���֮,�w�S\0W�|K�A�S0��\0�z���z�%���0�����71���o	\r0\0���������\0���!�~x�������E-@4x��-�ծ����2��h��-�\ZP�jK%W�JQ�\0��\0~�hc�c3��p�P��b�ը�d\0�v-+�;��\0�vM�b��vG\0�g���\0/@���D\r��\r��OEV�	h�c\'vpP5�\0�\0#�����R�LڸX��2~7\\5S@I;�׺P<�E<�:<�]�4��W�V4<ƭU��Va��EjR{�2,�2,r�2�M�8kݐ����!S����j���\Z��`�Tc�	b�f�)Z *fP��d`ŀ7��.�z2��L�c��bF���I�8��\'��3V�҅]խ�T4&eQ�`Q�Q�zV����,�Rq�fM`|g|הA��� v��\0�����b7o�	ko<��#w�\r��\'gr��4M�;-#n��2�X�S�\'�åPز0�@J���E5F_���JR�$R��c��O|�nB���t�0oc�u�X{rw�I�z�w�B\'t�t=�&bRMX+����&�*Os,�r+U7-Ge�ƽר�7+:3.be\\�z��E�\\(�9�]!�]A�]A�]��c��\\]�]�r,����\r`tJ��J�����|�<\0mb&��m�s���/g�.�2A�6u$�-�?�S?�#j�:�vDO4\\\r��\r�n�y\0��@�F�KR��c!�B�K�3\0�bF$-�fu�v4W[MGS�҂*�h2#L2��K�4Du�L�L3��X/�o��I�d:Y�v;��K|�Qw�=R��wbxJ.5����^���/���{�\rѪ���|x=@C�X�	E�3W������]��K?�K��]��c��]�V6��d3e�բ�YS�cn�ҍ�*�-�h2?�(3ёs�e�r\\V6��|�P,���P��W��G�].��8&\\���\0��b��\n�P	QQ	�0	\nq�-��-��%1�uC�Qa � 1�j����\0l0C�%k��o�`0,�Q	E�u�uc�B�� �2�rP[����%�0�0B	@�%t@	n`� [� z�K ai�N�i�e�b�?�O�@R��\0���G�!\r���T�aش�ؗP\"8�%��Z��D��\0*�9`	\0��e��`��]	AE�� �0�	\n�`T��p�Y���E ܭ]�rp��=14C�`	]&�Pepgv�	`�`wV�	�m��	�B� ��,�(�Z�@^���7(�*Whu\'&X{��/U�+f�*��,v��@2����-hr|^�|g���@q�w& �����n��00�{�P�;�(u37�\"g�����|��\0�c�soU�7Q��2#<��l�(e�L�Ȥ�6ͮ�ħ��|��hu\0�S,\"̿��mI�4��5��ʶ�+W���5��\0�\r��Ⱈ��)��k���s)��)\0\n8�7#�>)�8Q�2��>o�?2�X��Fd����Js�P�c�WąXA�,�&<�����m��m�N5�2_��M�b0�#�π6v~۳= 0��\0\n��=�p��\n����>�38�8Mֽ���2e�͎���X��T�Ѣ���UE��,�Ǟn��&A\'_�΢��;u��&�0(��\Z\0�����\07�	1\0\r��*�	J0\n(�f pY\0)(@>�po#���-�9|�8\rh/s���?�s�Q������3A��%��4W�543�:�\0-�.��+�S���Nk`�~��`u\0\"tP�@\n�\0\"��!�`z� R|�\n�@�\n�-�XV`?��*DjXp����z�S��V�3���y��:K�Ln25���x�|x�c�*%?h$Gs$�w�\r%�$�0��\Z��G�PK�H�\Z�$\r�!O�DV\n��	KF�E�	\\P{p�@��3\n�a�ϴ�v� j��\n��5���:\"5L�d_���K�pR,�	dW�`T �@\r40Q�āXX�޹�\r @@\0��]����F`֨Q���\ZM�fD�FMZ�jҤ%&�Y37����c%�&[I����/[6W\r�[w�ށ�օ\rv�Y�cɞ�g�޺z�x5p\0�]ȝ���y�	^X�ṉ窛7X�`u�H������+:��`Gv�!\nLHP���ٕ�h���b ��d��\0(c��\"&̗L.�xyԨ%�\0�N�Ks�hJ2�͘1r2�B\"GN�O�]Ŋ,>��Ůg��\"��y�v�[n�l]��7�7����Rl0�IL�\";\0�<��y0*��*�l!�����PB(|\r���!��L2	�q� *`�-����-�(t��M��d�:��#$�#���dI*�@c�S�IO��(\"+���J�ʰ�\Zk,��Z�.��;K>��+����O��; ���s2�L��-?��4,�f���lh����(��`3�\0�h�6p�b qdSM���O�X�Lb��K6�e�26qW=��Zee�2��!m\Zj�DG3�HK͊U/��.��M3���v�+ݫ/���2�[�[\"�֠��ݒ\"tA64\n\'r7P�H��sD*�\0��m�E��wTQ)�y�C����v�ňb<1�Wg5��2�P�Uvŵ��hd`ITm4t+|7����j,h�BS[l7+�D�L6�i�E`2�~N`�+�T(]	�e�F-��FK��fL<�q��\0 \0\0� b¦�\"�i�l*�����c;=�Jbn=�P�[R�:R��g ��7|�s@��5�啺 E�TK�Ӷن�H�y��	�W�6qa�Q/������g�f�9�+L\r��/d5�J�#{�I��HZ1���@�b���-z�f�cX��-T1T�PE�VNI��4�H���O�eUT9%�m����o�Y`�H!��n�ƛ׼�1<�0nq�����B�~�� ���H�B�߈Ep��X��~� 4L��F�~���j��ei�\\�ᓻ��!�&j$J\\�\08��	`\0)ZQ\0Ƒ�\0@��X@7�		�\"s����?&2B��`�� A܀�`��\rA<�j|#f0�C8�	�	g0���F��P0\"y� I;�D�4�?���pJ�&��\nME>��(�\"\'��\0m̥[�L\0���*5�9ʤ�J��y$�;Td�|��$�6�����ۆΨ�n�o�`�3ƳFP<c�xF�E�Ƃ��$�9��\rm�2���7�10B�N�D1�	\nhB\0�H\0&����b\0`�76�54�(�0E�\0:h�����KN2���7�a�%+�:e���r�tw#-I<��(���-+r|�������h90\n\"�ȍ\0l��S��̜L�w?d���`3�D0��p:��M���`�a�d����\r0���3����3^��X��	@4aZT��)��	%L��K|�3.��h(�,��Z��ю�,N�A˺D,��+!�YԮVˬ�$�����n8�\0�f�!�H8��(�\rb`�%�f�Aq��3^n�W�\"^� �?�\"A�a�xF�L��Q�A�$·�X##q�x��(e\"��!X�9���v��@Vd�yO����1��Q֚􂘾�0�9˗�\"�zɲ�U�@��\'1\rHQ�}:�n4@�\0#!�`���Z;pd	B��H��{�E�qH���D�Z�u�\r�����\0(�h`�����&�I�/�\0̠_6@�;n�\0���a�%hAv�\Z@�+�xvLf�W�����-]�K�&,��1�K|�Xb�%�`i\"RzȠ�{����F8��\0�}×��6��\r�������7 D�6��x4p�k���\Zq�\r�c\Z�p\\ xB��0����f#��\0E$�ȈH|�\0�\0��q���6�ް��E���%?<�Vf3�4�g?��X��Y\rgi�!�p����Ҙ�۟�/-W�29���\r�J�g<@��(@6�x�b\"���U6�ڎ��RX�.|kmA��\0��5`���3�,��\ZX��X���gͲ��g�V7��-�8f0�	���4�\n��-h�z},���(�D�-Is�5!B0��P�pvP���\Z �g�)V�=��D�\Z1�<>pn����SA���q�V�j��\r{���a;�=ui�[�`$\"�B�x���5�>�����R{$�����!��H��K$��[�\0 ����t�`@����\rq��@1L�gtΡ���!D Cd?�!�@!\n��ˇ�a�aӐ��zvKZ�Ҟ���/yy�W�Ԡ:=�@�1��)t��>�`ə�8�ڸ��2������(��5�@�h�\\�l�Eȅh@i�M��Ԅ;��Mp$%R(�Q؅�ɂ�P`\0��#?�+��C��z4�K��c�������h���>K��`��x���ơ��׀�ۆ�9�{H1#1�� ���6�6`�K��Gh��ЎLP%hK��AP؀7z#gȣТ2\n��QD{�F�C���-A��\r��pzS+�Pܳ=�3B����\nwÌy��n!�����n9�����}�p+��#1p�x�2|\0�I��E8f�`|�_F`|5�k�1C14�±�é\Z⡗�,.�Ң��Y\Z��i�Ǔ</������\n��w�?��<�`�Š��hϋ$���Xz	��4�a�C�z�%R�s���=�\n*��$�n��]<-l��(@q���� ��h��1Ͱ�w��!t�����y$B� =ң0�kB��I<��9��묁�Z�z ��H��n������!I�ș�$?�,��ʒ,��ԝ���taG�YˣGy��A�u =� �uP����x�~�u`��\0����x�L�0����{�ܹ�D\r��J�U\Zw�+��rsZL��K�ZL��H��l:�TY��Wē����k��C�4�j���8;B�!_I���M�,��=��	KXL��ޔ=��M�X��37�|�C0�H����S4�w;\0z:Y�y\ZɐE���,O���[�����S�u��EA��РLt4LVZLs|L����M\\�v)��t�D�u���,����i�F�\0	�u�K#伥��Øs�ّy0]O�ʟ�1p	GȄ/��/��J��I��7��6��Ix�G!�!�����Q]�\Z��/\0�0+\r�<D��L`R=��6h6���P�f���x�K���K��)\00��8�6S;�\r�1�Q<E(�%-��\0�7����Cu�7�0��)��K��P��#�:�L���Ѓ�!X4X>�\03��\0�>0��\rP�\'x�\n��y\0��h`hi@�\\hd��V`�U4uI�IpJP�9�5�7X\n9Hh�Mq���)�Q]�/�R�]��\"�#�R*��H�!�v{�Q�x�K�7\03u�)���VJ�5\0I��5`�7�C��I�R�<u�-E���\"`R~=��}Ճ1�6�M80�JXg�8��h�:8+ЁP�>X���\0\'\0Z%�� ��\0PȂYu�\n�\0�n��(Я�\07@����dU;P\n��Z�8�Ԕ���Jӥp)p�_�K�\0K�\r��z�z�V*�b�[�q#�VG��\\H���\r��Ry�W+�V\Z��7��7��FX�h��9�J\0�f]�#��8��K�ӈ�N��Nx�J�T 2<@�}�0�c�:,�R`�<X>@<��:Q���Q�[�襃9��êN�.؂-�.�?�=��1���&��Q`�H��\0�\0P(��A�OИ:x6��O�*��&Q�E�U�A ��a��)�l�����A�L��+�\0#؅v*n\'#�A؅b B�����݅`L#BP��P0��r��B�g`����R�.`B0a6a�i���b��`\Z�`-(�g���˩JE�]�,��������\0	ˉ90�bˁ��\n3��s�����/07ֹ3��@��@�^�Dʡ��c�<�<���FmdBƙB>d\0�FBJO3\"\0�P#A\0L�`f���`���bt2�Ŝ�W���<cb��LLqC�а̞37�(k!H�.C�1���o�+N����`����b.�b���\n�	���*f�8LȀ��d�1b�����1�S���:���NV^g��A9��8*��գ2a�uk��P�����8���ʩ�JH��V�C��\0��#�����+AP���\r>�b0o�h����8�>�ًc�Вi���=L>F������z?���)�(G�|�?i	���	h�\0I�1�h�\0�v ��1����C��#*\0q��±�q+L���f �`O�+\0��`P��z%�~���u|�N�td�����ȷ.1����|L�H������J�G������p[<�^詁�3�	p�HP��ۣH�B��Ҷ��d�`K�`���sk�;����;\r�^r#@����ne��c\rA����^�e���(�����������t�����B\0%�)\0�������Iذ\Z��=��\'kodb��Nm�J.��Jva��s���������H\Z�aPܡ��P�-����/���D��ɚ�ǩ�м��~�N�y!oUVl�&���	 [:�#چ���ÄD #)���v�Ӯ���d��qP(�C,�����ܶ����<��I)��(?ɺ���r�p���1����3�����J����2G1�U^ �D-|�nxf�.�J?�0\0��-�v�+8���B�#\"ϳ����kot\\I+ѝ����hp���ĺ\n�<1�<�h˳�ǹ`�\\<�d�{�JA�h\"}/�]��M�h��1���ᄨx�7��:��O�% ���3؅c�#?F���m�֒Lά�t�˷���p�:�����3Q�����hLT�U��H8�\0��G���5S�0�Kx�4�鍃<�P����E�;�N��P��$��^ó<�m��M	�Mr��µ��K���\n͊�vk:i9%;���h����w_)vX������|���3?�VoS���6XX�e��E�n�\n���Z_�N0S�8Se[Z��-�k��q�k��G�I�w��\n����x���Џ�I����ˀ�����q%ؐy�,��ԡ�Г$Эku�i�� �)P��	E��Y�\0;�|�\"\n`\n`5\n_ZP��X3���e�4�	��!��w,�d�\n�>:D�0Σ������piy�a�ǘC�ώp\Z�(��D_Y�K,�ⴇu���H���B�=��{(��m�{���PX��Ё<Xzi�����_e7@S+�T=�\Z|�d����	4�o]���\r�g��@�\r�;�^6��Wq]ǐ��,9O#A�$�`��y���9��z5I�3��^vB�+`�\'ѠC��YT�Р\n|*p\Z�@դ���g�^�� @��2�\Z3�K�G`Ԭ��V�Հ5��,-`����%Xt8���S�2�×�!�z�!6Hph�#�m�@ǅ,ԼQc=��@	�v��-��C��@M��h\"���p��V5������\Z�~�\0R�>�\nt��S�F �m,Yh0#r�Q�JQ&�a��Q#0rX���I���G(s̡	�I�!Gu`��\'|d`�e��\rg�}&�hu�@�}�e��j\ZuK$�s[I���L�ͣ�:��L�l�Ώ[]U�VE5x�EU�\n5��h�@p���Yh�\r�#�F_�2�$m4��\Z��`p�A�Up��r�IH,�J���@�j@F݅�a�\n1\n\"����A��Ql)$#H�����-��n7&\0�K7��H��X��WM\'�U��Tag�T�F\n�Q@^W�B�G\0z�w�DPC�r|Q�|Q��144��0ؠH���Zb�!��b��?tYA5)��v,�9j�A����e�)�;���.\Z�0O9֔�J/&GkE)\n�sQ�C�SSE�T��$xT!9�V��������<[|e�bfQ�R��1F%k!!�#�AC�T���iĠ�6�������Sw\"�f�B�~v�A�����-�40j��!�$!\'�J���[p2�S�v?b��s�<$��+�1��@^;\\�%\0:� �/T�肃/��`B�L#M�%d�b�&�X�:뛔A��eh���l�{s���Ȁ+Y���hڎ���� :�h򢒊#p8�����J/V�F�]t�V�;��\nۻ�\\�����7N\0?XB0CD�O��\"���B~��b�4B�#��4��\'\"�2��Z\0Et2��	�:��Տ|�!���5\r��@����DD&�U� ����&G/A@hr���\rR\"�گ�����c!���f����#,c	@8��쯊�[���A��3�(���+\\a	L�.h��]h!�f؄f7\n3�J[ن�B�\"	;Dy�PցD�}�R&R^FtӒ�Y�H��^�\\8�ޤ$%��dM$�!�\nXi\n��(���;4PRI\0{ ���R\0\Z�\"�B�\n�X�-��c� �!��3�`Ɯ��	10(H@B�A]i@�JW�q�%\n))Pi\n�c/Q5��M�����t�\0�F�O!1�jqIRB*� �n�d\'�\',��j����s2�]���t\"�$\0\0PT\0���~��EdT\n���\"8�$T�1�y\'l����tI`�(D�Ulc�^)�*���t+VڦO��%��I�qۿ0�����pe8�m<�?�F6�:5P�P��$�A���\rT	�=�ȭl+���U���m����R;�u� ��8\0�\0�Ai��� q��\"�W�C)DQ\n<��h��*p�>��P�*p�W��A7�	F0\"��R;�s�v���۴�6o+WzVGX���6��X< ��A\rq(��`�|���\n(\Z����A\\��F�Z�|��\"\"�0�5�i�(�]��R	TVb	��qb�&J����0�Ĺ�V%h!��H��0\n>DH�HC+>�\nU@8�ZP*̠�?,��Umj�ҍX8U\0��+�N�q�U收L�f*�h����M�q���X��ı8����CĂ/PE\r��\0h@ø�}@$��Z��)���:y�\"\rNH�{�n�����b�;\"��t\0��M�26A�Q��i�B\Z�1�B���B/��V�(�!��g�z��4�ȊS����5n$��g,Z�e�y�1�ж̥6�6eK���\'�$n�7����8��0�7j�k���er�c��L+\0�9l�úƙ�\n�dҁN��(W��CeY˙��\0@�EKT\r�q2^~.��e&�X���ć�\rr�8Ӛf-#�Ze(�<��!�D0���7#���5�`�o�\\݀,`K~@7��daC#��p٭�\Zh���������$��\rT\Zs�ɜ���U��Ć ,iv\\����p<��Fj]�3�x�}z�o��zO}\0��*\"~I_f\0(�B6��L�bF���o�c\0۸3����f���X�Np�*��\nt�E\Zr��&��K���e\rb\'k*�j�Kb����b͝{�[����ʑ��Z�3�#Yd��7�ݍ�kC?� �!�m����ӷ!�Hh���(�g�7��80Av��l\'�9��l��`F\"�	gD�\0���4��\\c� �6�UP�������`+Ђ��)F1�X����7��E����`��ĩ����:$���=�8%���n��}C�<�+� ̞�e���<1�2ȓ <k1@Ā �և��S����� �Ğ6t;\\!$�8$@ ��\0!��(\0`��0B7h���}��\08�_  !l�\\0��_؁(�h�-|�ku�94���	�-�94����@B^�����P`8�M{��ܕ��=p�㠃\0�Ï���\0�i��i�k)�2B:�����9U��`�`��C0B,@7��յ��x�� &Ă<0�5��\"l&��`� @ \\� �]\0t� |�\0��&�9�7\\\"l�П4\0C2�A�A-��(��a��a�A�j������m�J�(r<J��C�0��h���ܗ�E�9#Y@FBʹ�2@,@Y�\0����`6`�\rL�N�78C�%�$��3�@\"04\0X�/��5l�8hCXc\"�$3 ��%BH>�6�� ���ɢ2d��bd� \\�l�(Ё)�A3t-�-��\\�3$�ǡ�،F�,\n��M�@��$�@�� ��&5[�U���=:��#JN8<N+��-@&� 4#���XdC��C!�C7��\r(_pU6��3,�6xCIZ�I*C,�� @0�%�����5$�5�B1�B$@$�7h�3tC�3�\ZD�_(C=�4$�7$B ��cXA�\Zx���!�<�PR�FX6�Ȉ�@x�Y�KT�g$�D����Q4I��T���C:\0YL\\\0�L,��+i�cf%><f$����C$�Iހ3���@7P�\r�@�@\"$\\�a6�_\"\"�@7�C=���2$� a\"\n0> a!��0� �<Ð\r�N�@03#h\"$�l�*́��,��Ā��D0�k��4�	mHHp�Hl(RId�˥FxN���L#		��ض��!<U8�C��X e�ŀ��SE�y$�3\n\"�\0\nX�d\\��_X��8d�e� ހ &l�^�\'p��Ua#&�m��B�`�3DB(����������-��3p��d�X��=%\n�<^D�aI��´P���olM�*	;\0�@q�B1Ֆl�!�e88\"Y%@�Y\"��@!@6(�3���p	;j6@!>X�tC6�!e6��8�\0V�f$�C��\0\"��8���1&l�\'��I����7l7@G\0R� 9\0\"(�\0���BBgrPIDw��|Ɔ���K����l�wN)D�J���-kY�����P�7,@$@th�y\0_�eU��RY��e(@�)��\0�&��� ��fzו��O\nł� �)��& �j2Bkf#@�1�7��!4���^jZt,�6���M	�zH\ny��`ȇ�,�`\'��j�DbxڣՈ����V�L�HUT5+�\Z���ڎ`7P`2B0�� `k��D�e�Vk9@��0�\0�ª ,!C!�\"�\rL�\r�B\Z�A�P$@ ����ek��\0\\Cܮ�*����7Ū��(��rF>�����p�Rm���B��h�ʘXz�C9�7��\0��sB����\n\nB��(�aB!��zZb��^6DB�ZF���t_�aU6B$B�!���5�!�\"�Ʃ<����Gnї6��n��w��Z�$�D��z�n�v��z�i��i�,h�Q�0�)\"z>dA��������[)�5Q��B���;�ggV�e��LJh;�yXpH꛵��5ܤ���X�m��NyEWEy�\"R�0�\"�hT�k��H���Qr�ċ���-�GDl�%��,��#��D9$�9#���h�mC\0�gd���G�\"�	�=�&R��2@7\\����1�;Y� �d��\Z6�V����h3!q����D�°D G�J�� o��fذ�$�����=����;@9�Ҷ�����.N� -��X�F\"�(�2@$<���O`\0!D�= ��f7XS�$@V)@8�T*�075ES�xj�1��\"�k��Fcg=�C=�I�4�� �Hq��:�to�Ci���t\'�pL��\r��\n\"2��^%�t9������k\0�_n���;Y�*�\Z�������3du!�d�W[�8/y�	��7y���u�+.[{kl��n�>�ļ\\�xfґ:�[�/yB����X�+�4\0�O\0��z��A�^p��3�l6�\0D�A.H�\ZP��P�1@C�]\\�1�,Yi3�3lP\rv	;��-1tܴw~lh���>\n��Hf �\rOOr7L\0�/�=i4E����ZqP��ؙm,���uw[0�DA#��\Z4C.�7`yc5H-���\\%̉$�����,�!��!$�3$\\���\\ŕ[�RlcG�P��\"2���DܶE�DI�j�1���p���*Ձ��&QG�a�X��\0d�D�_�Ub#`@&��\Z|�\"��(Bx+_ �\"���\'h���|���(ԁ,��-���-�A��۱4�s�L�E9=A[�l�D���d��1�� [��\n� 2C��G;Jz-\"J4y4Ԫ�R����ao��@\n�$�TA �&�9��}��$$z�AiA�\n4Zd�)��D�-���A��p�Y�yV��1�-G�hMAl�����Hl�\r���%���m�ȍǅ\\�t�z��TP����5y���WM��y�\ZT��i�q�3�3�1��\Z�\'@�c]�W�B!̀!du:F�����s�P���㾳�NG��\n̎�)mPUT��᡼�K�(��*]{�\n����P��Y�FY�Dq\'�3C��E�D�@`܇u�ݝ�ǻ�誮r���K�!�.�+W�@t�țGj����=D�=��)��te5I��iG�\r��p���yMӆ�6#σ�F���q��:�LT�\"w�t\Z]���L�+�|�|�i?@��3B���}ݛ��7@�����\\8��}}��;�M�XS��w\rEV�9�\0R\'u�[�l[��7�6���H �m��l�\n �t�s=��>J ̂7�i0+�����+�m`Zq�����ۭu��>���?4�Q�Cu�M���RC�qPy<�Q�u�������by��4���\r�����SOJ;�4�JK����<�3���;�?���ݾc������8g��6��Ν+𐀽��I��@��$.��p#H��\n( I�ȓص4��%K-�(``&Κ:q�3���u��5P/(Q�~=�hP����:��:��UՊ\0���y`d3�ԧ|Eq�,@�[�p\r\Z�:� V,�����7�X��W1�%�$���G��@6�(3�ƚ3?��eg����<�sgۜ�U��Oi����\n}�5�m��nWEp�֭^��U\'{m�u���|���n����;�����b��#�o��qe��En�,�=��-Y��L߾��=��l��(��J*ݬ�7�誸�²ʫ��**6��p����0>�V�D�8\nM��>�%�$:ʹ�A�&���F���)��*�����Zg��Ī\n�!�)R��B@�$�j�������*�)I��>s�!�@��3/;���643�4�(�+1#�$�i�J�r2)˚Ȳ�*/Tk��d�����!�#<���J����T�ɰ��G��[�Ɯx�)����3��\\鳑&�/&��U\rK���SO\Zm�9]r�Jv�KM��,��.D4*���+�\"�*��2nIy��TI�(0@��?�zU-יt�iN\r8W�Y�#\r^]s���V煷����4��=7_v��4u���൨Dx���!\nb�=r�y��\0z�jH��j�y䱖䑭ex��g(��Gc1��Ɩ֩)F�u��v�O?�\0�yh��5��Vo\r��ъ���Lg��B�r�0Y�ޙ��w\Z�\nH#�UGc\'�JxFV��W^YH	�0���J*�$�J���Iژ�6��{p�\'�{�ţC�/��{�0�1��$�Ȥ�1D�DM���N�Y�\r8��5คv���p��[�0��{��A�$9�n|�IDC����7�7��tJ^G=�*�8��;��88	�;�������H#��P��6��*� �\r`D_h�\0��dDC\Z͈F5�Q@6C\rTC�(a�8��u�F\'ܠ8@\"s�C&����q}���0]�Po�;��1����`\0�0ԡ�b3!�/���\0�$�@741uq�D�ǆFPlx��\Z�G�aw��&�$X������(!�Nc���0�$$�m(h��8����C(B1�?�An�BD�=,�\0\r�(�<�3,a	Z�(귁\r�oO�_f0�nP\0$`3��@i8pu|�$$a=Iāq�%�`\0�\nT �o�C�Ah�������qry�a�~(*�pn֤$21Bc1@]Xx�\nv�h0Z\'E\\򲖒�D�`�1H�q�`#� ڍx�Ӆ�XG=�э�|���	6hbG��)1MX���� �	AI��\'x�O�R�T, ������� ��ЉN���-��N>��p��\ry�#�����PU\"ls����\r��:`nnv;�加�/lNs�CXo���U��K�����EP���\Z�a�8���OT)��z���HP��PB��u��� �F0��8Ĭ(�P���4$%��Ju����U�\0\r<��<��4\\a�ԀL;�l �Щ�7���H ;��S8ā�E$D�hZ�����\",A\n�\n��\0�i7bu��$B6����������)D��M��{�Ci������D#�ZK0h���{�1*�pRa��&>�<\0q�p��A�P�[X�`���N�!	�H��p��u���-d��-xO��v\\\n�`~����Fl�O؀b���T����7t\"X��BQB�Ѣt�B6Lb8֡�b>��bl��,a	�s�u��@*���Yu�@XL���[]+�L̝�\rf�#B��6Do��e`�>\"XyL�$~��A��T�\'>�V�a�$�C� <��P\Zx�j�*��V>�[;�þ*�@g2L�eЂ0�#�W\0&���,`�Ƹ�!�q+`��ֆ���&��3h�Lн�M�o�7����:���u����,gb8B	u��%�@9[�eط�7�	7c �3��	�:���&1`L�7���.�\'����B�����A\"1�� \0�\0����e­���1@:��� J��b#�T�;QHy�T��\\iOWRw��\r��	LmɆ�ٮsm���ع�ѭ\rF0�b�w��0tp�� 	<\\�A�v�r\0��F8 �q8@�H\"��`\"ۄ�|2��Ab����G�oO�\"�9�ҡ���[&%�a�e.�<&=�A�x�c4��M���ڂ3��\'������+_�������|�W����>��oʿ@\0�p@\0�q����&�p�lw>�F=�Ao�b�?�W��w�����=!��;\Z�!@�2�$�@� ��-lO}�\0�1������:�3��NҮO:�\01������;NO0���D��!���`�b@�A@���N��:��M����n�&��\0��Jb2(P�C��$3\Z�\"B��/���$4��SpB��\0��k�9�ܸ� /�n;�:��\r�\r���0���/�a��\'@����/�v��>��`劁�`w�\n��M�D�K��9�\0��=$�;�.D��K�NCHBX�&����P\Z���,hq(�)���\Z\"��O�Xo���X�2�c���;B\0B�C\0� VO�`p���A\0���~`n`Co��� 1�0��X.�,1�A�mO���@�L�/3�qC��&��NV�`1��)��,@�)�9�B*n#\"4�*����2�\")R (� *\"-�4\")�#/$��@�\0@f��A�A�v�2�����m]��V��h�UL�U��]�% ��5�2*r$9�)�;f�\"�rB<2����c��C�\Z�\Z@~�%_�1��j�\'���p��<a8)���/A�T��M���KTEU<d)u�\r�1Y�W��F��,S�B*�e!D�2#d�+�p1.�+����J�=<�1�R\0 �f��~ b@�j��ı�v��t2O!\0�/��炲�<�$^D>��U3:A�KNbN��>d\"F�T:0*6e@2(j�@�H�R#�P\"0\"C#��<4�\n?3�]3%1 �!~�����5��4�v!A4�N�\n�Aڍ���Q)�$3N�:eE%V1)S?V�1��g��ff#(�G�\Z�(�%7�r4�3q�\0��+���*�!�c3\"��^s��fNr�����`@���4���\nN!����ȮV��&�t:-�T\nS$��T��KcB\'ĎU��X�$<�-�,\n2(��H:3F�#f�1�25+p\"$�*5%C��A\0�a!�d.b \"�ۚ�0�6����9�۸�\n8�ӭ���0�/)GB1�21�&�>r&g|�;y\\n�2�-p�e�cG�\":#P9C�ƣ+ao#L�+-���4%�e�-]�?!7���@�IK5@�Kx���1ɮ�>�Tx�C3�$d�C�:\r�g`�M�W:jа[��?�\'^&)��)��,q�\"�+�S=�S�2b\0\Z@HOn�cm�6-5~ �&��[���D�\\��@!B`��/���N�K�f^��9F$dDT��5d�Op�DW�Xp�S��a�b&)�\0��=��jMD�sEB>[�$Q2\0Vr8���6���td��T=֏\\�\n!f���Z1oY5E�&1���h�eiv�]�����f|%T|�_W�`h�e^4r�$H6�4���Gc�>ۣ=����b�2C��S9���~��<��J�-T�un�ng�/ o�.�D��v�$ޔW��>t��x�OΰJbi�u3Y�BRt��<�bO\'�s�0<8Ds��r�%�a��>���65����醂��@v�Ƶn�a��8��1�4yŐ5�v5�bi�w�f$��V\'�i{�J��a��b!��6�B0�=�ws=7G��$|	@t�w�\0`\0\\���a�0�H\0�@&+��\r�$�2�A�w�sO�-K�M�N�V2?Ei��F��X��S,\\�D@�6vq��`&Q�!3�3>��Y-�GS��% \Z\0\n\'\n��@�6abaV���H©��m������ޱwU�-ִ1g 5F�4F�\'�6S�ff�y����rB�E�[�@�����7��\0c�0��`\r���!���X`�����h�D��\0�.L���A�X�SՂy�9����y��(�Q\nv �O_4�!E��Z8�9C�V�!D�.�W]\0��@\rpY�`\r�@\r�`,������Ī�8��Y���@� ��%~�M��D{5�擉�E*uD)�EY�%33sG^T�A8�\"H ER^��E������`lǡ�@���A�u92@��j�\n�H����j��ʐ�����Ű�=�g:jv@�uW��h7zNQ\ZG�S3W�!�B��Y��G �Cn:^A�1���_:��0����\0� �u���!�����ڧh������I�X�u|�\n�Z��5�da�0F*���%�_\rƂ��\'��B��`d��*�vO�A�!�c%H�^�ES>��D�EVm\Z��:.�Q��p��&hu�\nA\Z����j��;�H\Z�au6{ux��\0��ICZc�oĊ=y��W)z�+rD*J�e(F�S\Zl(wQ�8��YD��%��^�#졯�2����\rS D7�\n[�x\n�@�`�� ����:N�|J�:���\Z�!��;��K2\0\'��,��@�����S0��q&��E�)�G�lV9Y��k~�*�Q\\z,����?�z_zVVL�-p�M_�-�\"���\Z6m\r�\0�E<�!\Zr!�!\r� � 4���[��9���\nj\r��;؃da:pzF�7zE;�X���%�!Ÿ���@�\"H�¥�bc��A�4f^BC@4U��4�&>�K��&�MV,B�F�5a�\0p`�����;4�6a�\0�!\Z��\Z��4�ů�t�R�`�#�2��i=�\'t1J��j�6��EU�E��z�\"88}��!�8�7,e��}n�^`�LC$Ub��5ފ��1�v�a \n�\r\Za\r$Hě�\0t\0�]\Z�!�5{���`\Zz�o�K[ǭY���5�� ��B�C!�B6��)d�)��7l^j�\"���7��)~�+�1��oE��:�<4*���;�e��\0q��\Z��ٰF�p\0ʻ���Wǉ�@�`a�Y�0��ɐ����[޵k�-���6l;3���5=7ց7 EA�|+�\\7��k��oGc�G�Vi�V�E�u&�q=Yx��\0�!o	*��q��n`\r�!�^�V�V�u�H$Z���Fa� \r���Fz��S�;@`�et� ]f��b�\"�ͳ(����}8�]R�+rb��[(g»�����|���s������\n�k��ʊA	6����7h��k\0���Ksl��S��O�6a2�Mĉ��e3�1#�l���Ө�#�z�[w����\\i�u��`δ�r�M�0����R�O�|�\\7#���0-𴀁P�:�*u*;�:�&�^WvTأj�����\r��)0��R)��G|���J�\"`n\nwr3%N�9��TI�D��L�ꈩ�*�E|+���Fr�H��ֹ<�i�:g��Y3���:i�^9�\'��+�����y	�!0n7L�xU���6�T�.�JV+��ҵMw\n�:שe��;G`�\0����e���082U�4iL0��TJ�H3p�G(sh�	m$1Fz�!GBu�r��\'<l��E�tH%a�ZF4�D�PQI(�V�L&r�Z�5�V�l��p	u�ͤSnE�g�u�U�UtOI�sY1e�XQW��\\lB\'��~���F_�PP�sT�	o�Q�qB8�����C��Rh%�\"jM)��H#Y�N$��N��6c�@��bG�FTQC	u�Q���:WYgݔPI;ER�T�FBהXd-��坕�Zߴ�\0:��\0!�P�	a|�EyE1�^ul���If��}r&�	��!��D��D�B��*�i��\'D-y��I@�&[N:}d#mʭ���\0W�M��TSQUUt��\Z�uO���TN)��e��V[�W\01l2엙 [�.8D��.�Dq�&z��e5g\"�b`��\'�����G�l�ܺ��H�S�(,\"��q�>5��Ly�bl��đ�.͸N�&0L�TF�ڕW^A�0WMA�]��fu��T��[\r�N\0xb	:��x���8l�&o�1�aԱy��A�e:SA:\Z|����7�F�`��M���҃�F/�����6]�SK3=��m?�$�>��cm�\r�RR%Y7�I��d�nk$Uy��[8@/t)F�_D���*[�Ar|���x�	�T��3��+�\nUha3�y�R��*2\r$�#Tjp�ī6��ھhR�ݸ�%*��M�Q�tjG�A\0�2(���H����b$��+�+\0z�%�\01@㐅��E�� 0��/�O���%�0�\'� �(̠����9u�H��A�:�Y��T��\Z�dx��\r�R\\\Z3��D+Z�Lx�qJTb|\nW���Jn�a���Zհ<�R�@�q,\00H�#�\"\n���\"0\n)�a���DJ��T���0�&�P2����6��T�%<�r��ʸ49v$�Y��x�^��O2	�@<�𙜢���Ƥ��j)܉�!-�»��{�@[ć�	�T� ON\Zш>�!\n�)�2��(�.va�~zb����\'TYU<�\0���\"�#%��E,�1c��5SEŬ�L<�̪��3��r��y�>�]-�欤�$$1�LZ$�ЎG\n ����A�NΓ����e\"��.2��]\\ai��\'�1�M��fP\nZC%�u)���Jݕ�4.�6���j8����G5�y���ѩ�%�\ZIa�h5�(�,��Ԧ�$�ٰ\0V�NP�\0t����J�����A1\\@e��Y�\n�p����i����JT���H�6��[-�\n�JU��˕.���9�m��\"���\"`I�����&�f�P�������#+�~5�Z�P{�\Z�����<.\0���Y#���>�C0W��N����]��Q��1(�6�VVM	V(+���3b�x]C�E�C\'�\ZJM���mjT¸�jV���HU�[Qő�\r�8\0�\rvf��(�g?k�A���@V=A`)��dP�\"��	*xK	<0�3��\0?���C��*T�jUb�<�F�p�*�h,��H�e�\"���T�1�]�E����B����K��.g�٣���\0\08������q�E���� 20��J@�\':W$ԡ�h��pk9�hHp�����f�yX�*�Ǝi��� �:�����7\Z`;cg��x ���(x�i5a�Mo�b��V�#N��vǋ��yֲ�\0�.�^���_` �� �&��pb�c��~[�����4�&=���3��ȏ�|��5��v�ooP&k��먲\0�^��p@\"!���\\��yP�\r�x�0I1�̈́1L��)� ��KcC�\0�hG�q��,e\Z\0�}�e;I�_d�c\'�.�a�;���\r�d ~��H�����n��JVjG;�>i2����u��lu��\0s�c1�C8`�`�A�����\"\"���-�(��M��� I��F�ϧ7�÷\nU`MKZ���I�;K\\��\r�\nbP������E�,�`��C)�P:����(p��Q�\r�G�*����|\0��R�qJ\0{�%��/v��^�\Z�?� i.�c^�Ģ��8$�\0\n0p��F�EH����6�#�M�F1�K�0ف`q�c�+|�X08�\0?�u � ��?��eT Ux�>�����}�����\0i�\n��؇0\r��\r�	��Xj�w#i��~�VlR(T�G��x��x���^�\r���@��	���	�6\n�����\0��53�\0\'�G�%(21�FS�BC�dS�8��9>p���uW�3va�K�@�p��}����i�f���\nO��\nB�\n<�}��\r�0\0��\r����\r���w\"Gu�g�p�v�Ax��\0r��^�\r����X\r�\rc��\0\n��	�p5��^\r�\rJ��\n�by�f�MF�0����7R1oe�g;vS��w���nA>�Ȉ�S:����	��\n����Oه��P�Z�\nA(�Z\0���\ni�]u\n�����\rզ���w7�q����X�xcv!ǑI���В���5����?�5P��:ɍ�(����W��-�=M���].�*��H�\0��� Ii~G�P8�Ȉp��j������`�0\r�0\r�\0�9���\nY �q9�b)��\n��J��F�����\0�H�7�8UuS9l7l�uC�6\0�\'�0	�h�.��� �����钟ٍА��\rj�6��.P�1����u�ao��~�8\0��%\0�p�\r�\0K����0(p\nfy�p��\0�.	�0rX����py\\H?�sӐs� �� ��\r�@�0\0 8�w�xC�B\0�em�ș�(�=���	y-��X���\ra�\0ـ6�p�]DqH�K��@!o�S�T�z�x}�\r��o��P8>ƈ��\0��P�X��P֩��Р��V��s�\0�K�3У�\0�A���x��\r� �������v~ �K��݀��)�Yړ�@�^�\0��4ʍ:	#�7tXt $*��R3�*��nM	�V��e��	�m�X�o�(\0��\rf8\r�`��`3�4\n�?ɑ\0f蹤\'Ʌ����q�\n�\0:�y���\0�\r7�\r�&�9�����o��V��\0 �\0��\0 �����	d*�\\�\0��\0_Z�Z����afhj6��n�^r�=/����X��䷧|�u\\�Z�6*��ȑK���i�C�\0�\Z~\n�x��\r�@J�?�	�\n?�?���eW��\rP\0? 1\0\n�*݀�U١�	?�	2�	s \n,В�ڍ�ʍ�(�]��;��jK�Zz��;���W+��[+���2H�w|w���#>��\0U�\r��\n�Z��j��0�J���V\0�0\0 3 �������yK���	�j���d��p�@s�\0��y?�_�^�� �\rPI\nVPqPG`Z0�V\Z	����;�>ْ�[�K�s�(bi�56a��a��d4S�cxUR��u0\0���\n2�����ʩ��s�P;�E{�s��\n�ِ�pϐ�7�y `Z�\Z�P�F���\r��\0.�	���P\0��\r�p4�;\0@G�	�`M0v�	w�\nݨ�!�${����Ʌ��\Z<1z�2��Ng���4Vr*u}��9���\r\'I�p4� ��\r�\r9�\rK\Z�\'���\0�9��uk�u����Y��x�ր	`jWЯ����F0\r�p\r��yW\0	�W���͐�\0�`S�	�p	�����C��y���+��Ʌ��P*<�0ZS=q���ݔ�v�QvZC����\r�%���\0�	�=ʅJ��a�@�s�\n\n1P9�\03��U\nyd䰞�~�\r\ZL��еxT�p̀	%�O��߰�\0�0�?��	�\r�Kc@Q�v�V\nw�	s�C<��ˍ���ҹ�G�: �RǄLo�1H�<+}�LiS}wN9$����sx����0�@�x��s�y\r0�Ϩ��h���x[�\r��YmF�	���>��t+/0� �	I(\0��p�Pz/�	J �\0�\0��	t\0A�����J(��̥��k��!\Z2/l0v�dM)=/{����Vy�n��\0�\0�J	ڠK(�\rR������Q=\0߉�G��,��@:Jp����PjŰ	�@\0�\0�f���`O/�	��\r�\r�p�@�p\r�0\nt�	� ��|�@e��!KӋ\Z����x��uL8��5R��HCD}X������ѧ��\r���N�����p��+;��\r���жtKJ��)�ِ�����p��	���ې\0���\r���?�\r�p��	@�����0��p\rI�\r�p\r��x�\0�\0V`g�	���@��ݍy�����@�A,�5�b0��.�Watc��z́��焕]����G��,��\r;�+a�+�w�\n��y�	�\r�*�;��֐ݛ�ʰ�8�\0D.�/�\r\r�\n�Q�\r�M��xMw��=̵@I��	�3=�h�\'b.\Z�\"�4���\\w�E�S�?�PSqK�Q+͵1�T��3�4�\r2)��\n�5����ɱ�\n�Psݰ\r��:�_����\0L�5����-�j��\r�@���J�`��0݃`	���Z����:�M�O�dʐӀ;�� �`vv\ngP\n�ж�,����l.\ZB;�0��>��$��FX��P<�0@s���Ϙ\rf0\r���J;E:@u�@t�(���t�\0\r�\r�`y.���˘��ޠ��pm��`\r�MT��7p��֋���*0�\0@����@�v`6\n[ �W0\\\nƶ�g�/n6 �4d#<QL?�4\r�W��4�Ka�%�e��9�C,���rg�P����Q�oW�Ө���}?��9��{�1�g�~�0������P]{\r������\0��Ì�Ѡ�sk�	��y�]��M�p��ҾJ��pգ��p>7�!.0�QA�>QG�z�a;N7r�q�H��;t��3>�R��\0���\Z	��\n��6�\n\r`?P�3@���\r����\r��I��DU��	�K~��	=��@��:���0����D�����\0�\0�MZ�j	Q��ԌH���@���(\Z��.[�u\rt�H��Ǌ�8Xw@�J�N��xQ�L��(ڼȮ�Mv6�,�\0�E� �ۡ�&�֭[��b(p�MP6A�	���/��b�]�m�1�zc�\rFm\"�,Q\"f?�&�j��b�N`텑AW�%\"4Զk �9���f�v���\n����ȱ��<t�����\n�i��qd6��a40��oE�r%K���W1��u!g���ӀM�9u_`h;w`h�-}8�M�+�ϴ9��`����u�0�FBfH���L���\nL���@�y!��&�l\'�Hp�B�9@�@�0�]��n\0E����!|^���٦�^`&\Zx��;B9#�,��&�d\Z	��>�H��>�M8~ci���kN�֔)���.Λ�+�;�h���@�o��=����g$�mb�q!ԁ\0��\0����{�y�*�j+��\Z��PL��4T�Y��|a�M��q~�uLw�ĀL����(��g�1�4�<���mj��:0����nA���ZB�\\3�+7%5i����|��:�e�6;�+j\0�p��C�G�Ջe\0A��nnP&m\'�ޫ�Qm�q��Jmf#��DtE�q�o���P�J�#����\'L0QF�r�O��#N��\0o ����HB#�����=.825�NK2��:8�Nj%��I[��e;v{�I�������߰�\n��X�!�6A�! ��od��C�Ij�{����R�0L$����lv�s�y��kA$F�\"�b����0u�m~��a��\"�f�sLsf��m8\'<ܺ�)$-��H\\�x��:��$�ܲP��lir;^xᆻ���F�����q@G�i_f+8e�3�g��q�)\0W��(d\Z�5|\0�x�s~!�!�e�k	����>��M�\nIC�ϴ��)�І8\n���0b��=���X�;m\"E��-ꌄ$1�����D��|N�NL����4Q�N�q���c(F�W8��`��\0��o|�ڈ�\0b�	A�\nD,�`�@$ �p�;�+�\0fQF$vъc<D>؄2�	d#i�����ܠc4T@\r ��\0Q��F*�Ѽ��!�1�D�e�5}���q(��]�b�r22Hv��X��h\0\n�}�З�q�Ϛ��z\'7�1�9�3�gD\"�\0�@A<�����;�,І���16�\"�j�`�.����E I��DN��D�o�K�;��J/.�yء��3��z��\r�42=�uDl�0o̴���m�<�L���٦n_ԗy���bŢ�PT9	Е�凛�P�\0n��X|�d4���a�bD��F ��Ս�?�\0Q\"@��EP�Z��Fa�Mp��8;2��H�pM՝��b�Ǖ���E�d^�[JI�{��.\'%�وE���U\'�DĚLv�{�`K���G��@?P��fsD჎\n����4 )	\"~ �g��#��>������\n� �`&��A��s���\r?F\0�e��xīC�Z)�ٸ\"u�[���D:�Bi=�c&u0�l)a�c�&&qeOmX�\";�����:�ᔪ\0/�c��\rm�\nו�7 \n\nX��O����\r�m\Z\r(�oŁ���ŠA���\rk#/�ۆ=��Hŋ��h��� �+[*���T=��ɉ+�o�kߌLֲ�R�k�7�Oz2��sH�բ�S�f8��nY�[Є���MD�۰�b��˼�B/\"a����Gnq	��j�^�w��)�Z��N�#���kg;����.�D�5�:�S�%�������se��;��g�\0��g_�y�6-*�*��S��u||S�h��x�g\Z*��a�l�YM��T�a�k��Ўv���~%o�u�̍;�hf9��Y�d��EW��}�\'����85��p���տ�\"۲LE��+;��!N�\0�����҃e�ϠڟCb˕�|� ����2q$ ݸG$���ļ�{a�=&j�k`wu�;�iVV��B�S�����[�H�ۋ^��m�+����w@q%�\ru��٬��5�Ix�����qOJ��\0�w�c��iGnボ�i��b�����\0	HDp��U�D��q�1�D<�x.�=v�潻�B���8/�p�8��wqkŵ���\rO����p��|�X���F6�pK�n����C�|y����������ڜM�1���� T����� A���\n����qGw#��&��!�{����?��:�������m���q\"�k�yxu��yH	�38�h5�2��ȯ5�)���U��{�����I\nII\n�j�5�7b�Paë#ۘ�����q����*�w\"\0��q\n�������c������7���\"[�B�!:\"~�����&RT�=3�y@\03���7��326��+d�*Aۗ���p��m���<��nb�c����z�`�z�pL��H��HP�\0P\0�;Q3����S\0w�|�C��:*�7,�=�Z2t�=�@��K�]Tz08���큉���$�����,�3�=L����7۱�X#b��X�n�ß���P��CBh��X��$���\0�B�\rЀi���B)DE�b�̂���ࢸ�躔�:��\Z�X3�2<�5|,���o�H�j��:�a�\'$���󀾐��?\"��\0М�F����p�(�M�]0�\Z����I�IM�thp��ʡ���E,Ew+E*T\0����뀓�[��2�<�)z	�h\"T�L�S���F|8�2&��C��%���e�l����8II�W`��)�a�p\"��Gh�\\��j�j�i�8��\r��\Z��L���x��I��h@��`&��:��,��7Y�ڄE�k�3�%$a\\	�4H3)K�@��T@��D\0\'BN��5�(NN{=ڻ�+����W�������K�L���$���@���/h5Pn�Ln��^\08p7�+��	x�C ���L�L�I�q{%<�����U\\Ef\"2\"�C����7%2�P��H\0��26L8!R�+C/�K��NK;�3k�3�?�����t�l�\0`OERȄ�\"��h\07�N��9�[�QX%�3؀�&	�hCX�U\\Їx��Ǻ��ڐ�MS�1A��`/�#H7\\	��&�3��2t	�B��N��\Z�ѨŨ����Ki�8�L\0F0�0��G�\05@�fP�fhE�j��f\0�$�[Ѓ1=Ȅ�R4��$��;�Q(`�c��s7fdP�\"S�����\r��,v�419�*���Z)��S<m,�\Z�㫎�;�@EJk�U�.��_�;�3#\0��0�J�TE��5��ME5P`XJ`N��:�% �20�2�\Z�,@�}U3`�Al��)|:!V�|J�����5ޜH�!5�x����S������S�����9��c>|\nD��N���)8�G��:��N��)�u��50�5`6X�7��L��2�OH��B��0\ZЂ]�V����#��#�������K-�����N �X�8]������]|���E�@�=�ʚ��N;aMV�VJ��f�@1�C��\n��{m�KHZM\0Mx��}�J�J@�/1\n�O8��P�B`��P8DFر��4�\r�;�X����ȩsE\0��^J	�\0�]	�Q�{H�[����\Z�a��V��V�U��%��,�@�	�T�X`L(*@9�����H�2�2X�bH�S�\0�%4��t$G��.�(�z^\rT��%.��������֙ ��ᴃ��s9t��U8���,�=Γ�5�c/�K��dʹ�.�B4���\0���h���k�I<��j�Md�E��+�\0Q,�X��@B�����{����c�w�֬KX�4��7�ج.�J���9�J�����:P��:�:;���[��L\0�z\0���3�H����XF/ZX�P�o�)q�,_,^���1���\ZPP��W�Pn�HnGG�`efX�[X�����d��ֻ�%��[�G�\"-�M���a�%V	���M�H��<N�I�⬲�b��3���1��C�`\nK6�b���>�?t\0K�~�~y�v�Ƙs�h��,����b/�+w��bM1���@V+@b���u��	��Ys��5�W��\'s\"���]<���SF@F\0y��+��9<hft����t�g2�d�x���&��\nK��m���iu�����=���f4=�b�U����4���B��w��P��\"L,���R��\\�M8��[�YC�!2�U�7<#\0-�ii\0	К^_xz\03�tTG-�k��Iƫ�r��:l0�+5[�m�e�Չ��*���\"z��,��86\\Y��d.��b�z084���Bβ��HK�^N`��[�,>�⸍� DGN�N���^GM���6���t<nqLeVƹ�u���TR�Ubɇxn�s���2�\Zd&��KE�Kج���K�� ��/DH�j��\Z�^�^	g^m�4�hF��.��BZ�f,_����i-F�\'GJ�f����\n�#���,�m�+ ���p����\0|Ъq���$�8�����LYV���cC(:�Fα;�_\\C��xC6�=������k���L�+&�T^Gl«%g�Nl<SPnŨ���\'���r/S\r�����7��g�61�:^QNCf��P7�3TG�h���B�`I0pK�CX_hx!���M<n�BtU�^<�0�M%<���n�|����-�	��q��M��\\�����0�q��hM���<�ss�q���\"�@��u��\"�;���OeF��e�\n-v�;�f_�n:2O�%[ è��� ��g�l���@������k}���:��,Ǫӳ��[Æ4�>V�����\"	�Љm�,bi�������*g�n���(�H��t�2B�)��K3��忖���\"	�pw�#c5�r2���>�o�����q�E�.<\"\\+_onv��a�C�,ݍ%�Vj��n�\"W�m�p!�7�=[Lô����x��P���@^��=�I��p@�u�����=\'k�/.��.d��\"����+ߡ=v����רj��N�\"!����t3v���\"����#2۴���=�;��c��LaTC5h����q�ͥ��K[S&�����v�*��*s�*|.*�}�`\r��Q��������cꍓ���T��_�S������W�o1\"����7s��J���ڟ���2m�ؗ�x8��^�o�q���.c���q)�_B	��������5��>NyXyx���w�X���@f3p�A�\n����\0Ev��x�@ƌ�X���\"H�\"G�L�r%˖*Li��ƍ%bl��!D�>�kt�و8�n]6�J��Kz@��T�^E�@�֮Z�i�JU�z�]h֬Nm1^�hpcϏ!QNt�w/ߓ+�i�#͑���s�O|A\r:^�Ь��e�.]WV�yI�Q�*U�U�\\�\"\0�h��\Z�昡��\r�Ũ�.܋7���o�)�d��J����y��ŷ�צ}��УK7��,^|g���ћ�3��[O�Cmz�9y	��g\0�P�>��XG�E]�ETs%w`�Ǡo�-HQ<�\r`��U�N�	��9�BE!�T����y[M�:	�i`�w\Z����@CE�[\"�瓀�]T�]J\Z���$i����F�]�U�[L%��ep	^)�bّY�chmG�x�y�fyT�W#z2nE�X^��UX`Ԑ~��8���%N6҃�$E���F��lX@^*I���-Z��4��%\Z��B�TRD�7�g��:՝�y5Y	���@L	䟨>�*Hs]��]M��E�\Z&i���$e�6-�h�ñ��L\0�h����B�u\"�����<4��^����jyn%�9�\0e\"���kF��[�[N\"I�n�1Ve��F�E\n?�ma�6L�Ą�d���\"����(�]���:Q����;넷�;��fp����6�W3z��c�<��#W�\0;','����\0JFIF\0\0\0\0\0\0��\0>CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), default quality\n��\0C\0		\n\r\Z\Z $.\' \",#(7),01444\'9=82<.342��\0C			\r\r2!!22222222222222222222222222222222222222222222222222��\0\0d\0U\"\0��\0\0\0\0\0\0\0\0\0\0\0	\n��\0�\0\0\0}\0!1AQa\"q2���#B��R��$3br�	\n\Z%&\'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz���������������������������������������������������������������������������\0\0\0\0\0\0\0\0	\n��\0�\0\0w\0!1AQaq\"2�B����	#3R�br�\n$4�%�\Z&\'()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz��������������������������������������������������������������������������\0\0\0?\0ȷ��)��ޡG󧿏��=��#�(�\0\Z�v\0=�������M}���|B�m��{k���N?��!��a:l��C������W�}������Ɓ�( ��xێ��:�u(�����5�[��֒�&v��c�zz�g�,D[E�ű�ďZ�I�x�\'��sY`{�\n1kQN��=?��(�?�\"�����YZ��\"�\'Y���2�\0v��<����G��GqV�������}^���-�{Vf�vh�ˌR���	������p�.�j�3W��*Q�@�O�S�	n�U���▹�h����W�\0⨮:5���R���Ϲ��j1����8<�U���F�fW�Xx#O��L�-c\r�1,��q�\"�OV�另kƯ�\'�{W�5��omn���h�Ƭq�E\0��W>\"��{_R-_�i���A\nH� �Nk������~��Ķ���]J��s�;~��Z�Bb�}���/���xY��e� ���W+�G4�f���r�x#�ӐP��������n���C��?�� ��׽I)P�M���y�jG8ܬ=�������1�0P{�̞��M\'&�|	uwڌ�;�d�$��՝Ux�RmH��]��Uc�����e⹿�[ę������nY���N�Q������5��\\��\0d`���m�v���������g\r̝����L�1��Cy\\Ǳ�y�\0�S���u�:We�;\Z��q�l	��s4�������)�m�r �vw�zc�S�R�4N�����UbT�s���g������K�S$�˘�\'ؾ�?�XQ��b	%A�?�S��;Ց�@�)�t5����0\\��r_�r9����e!#E��X��pp�O�=�V���H�X�H�م\0����En��,E��F1�܂9��gӏ�Y�3#yq���)��^������P�H�-��\Z��*�\0d��pNFN���w�~�n>^��ب�HY%�_�ê��������\0��d\\H\0nT��ׯ��oi\Z������$-�Ah���N篡��)8�kM�-N+�2aGү����wL�\n*:Ԫr	5���r%����QZ\"l1�㊊E4�=�*	 �����e�����i�iU����\0׷C�s�\n˻C$�c�N\0^�סxGᶳ�Gֱs�dGBs9���x\'�<t��(�Vk۲9[_�͍!���=zw���NMQ�ˡ�JA#�,����}k��W�<j�o��ǨB�y�L\Z2A埌��	�5�s#�4�4�O����c�`	����n.\n�`�s����z�������S`g��9��k{2P��zz�����T���֘��H�q�E=I\\�(�,S�Ae�1Y�l�ɔ�Q�Zs�%�ct���T{\Z�r�EU�Y����N�u$>�V��Z�V�V���I)n>����1K�u;�5��,�X����W�=)1NH���%�Ð9��Tq��A��z���(0�9�����w��\0qL`����(�.�}3Q�ޢ�b�r*68�h��B�nP���u��\0�\0�<}��h��$�\Z� �)�ES����','GIF','chartesceau.GIF'),(4,42,0,'contre-sceau','image/gif','','GIF89a�\0��\0���)))111999BBBJJJRRRZZZccckkksss������������cZZJBBB99)!!J99!1!!!R1)�RBcJBR91J1)B)!kZRJ91B1){ZJZ9)R1!!kJ9scZRB9�{cƌk1!����ƭ�sc｜sZJ��skRBƔs�cJR9)J1!ƭ�޽���s����kZ�ƥ{cR�{cޭ�cJ9ZB1{ZBΔkB)�ֽ�ε�ƭƥ���{罜��s��kƜ{�sZ�kR�cJ�{ZsR9kJ1�ֵ�έ޵�֭��ΥΥ��Ɯｔ��s経ޭ���k��cΜsƔkν�cZR���ֽ��{k�sc���cRBsZBJ9)B1!!��޽��RJB��{�ε�ƭε�ƭ�����޽{kZ�ֵscR��{�ƥ޽�έ��֭1)!�Υƥ��Ɯ��{�sZ罔ZJ9��s޵��kR��k֭�{cJ��cRB1Υ{��ckR9�kJ�sJJB9)!�޵�Υ�֥έ��Ɣ����޽�έ�{c��c�kJ�sZcR9�������޵��kƭ�ƽ����cZJ�罽�{B9){sc��ε���絜�kkcRJB11))!��ƥ��skRZR9���{sR��������������{���ccZRRJ��祥�JJB��s{{k���BB9kkZ11)))!ssZ!!99)!!��{!){�{ckcJRJ191!)!kssRZZZccJRRBJJ9BB199Zkk)119BJ!)ks{JRZBJR)19cckJJR99B119BBR!))9B9JkckB9B\0\0\0\0\0\0\0\0\0!�\0\07\0,\0\0\0\0�\0@�\0�QdJ���lؘ���!�J4LU�H��BS\n#T(ĠĊ��t���l\ZR�ئ\r�62�@jD���7[ʼ	�\'$)RH5�B�hQ(Q�@�Ѣ\'��Lz�h�!���x�ʅ�WNZ<uI�ѣG�j,���\"1SAդP����t���B�Y=�dIG3�>Y�x�,��$b��	#ʉ&_�vң,X�^fb�B�$�P-I���*)� ۔��������߲a��͆���	1*<����)S:t(C$?���^�gO����U\ndԔHNf�9T(P�@��*����D��p�*�� ��(`~dE��]z���t��#�1��#X`�W �m��!ַ��O�w�*�m�	}����$�l�H �A�_�G�`\0ߌ��#P �T3���\Z&�`\"	.x Bj�\0)�Cz���SR�Hj�� p��ƛlt DF�aDC4�R]/��ЇN=��CRPLğ@81��:������!�\\�p��&��bi-�h��W�i�	��w\Z4^faT��D!S�u�I���YY0��# ���^зE\n>���V��\'���ELp�pN��Dk��T{-�֢���^K��P��|��KO��F������o���o�\08h�A��vؒ�.�X\n�/���J*ZhZ��h�\n!�0Fq�	A���D�\\J�9��~�AF4{��a��3�3Sb�^2	�f]�H����~�mr�>�FMm��h;�3��h�u��Sm�_kk\0��n�ئͭ�c�}���n��r�����C�5p�=)�!�\0�ã�aH\"Y\02+��ڜEA|�E`@��3L���N�>�<��<��XC\rm�]��<��}\0۬k��<Ϝ��>�t\rm�ʍ���S5��N[w�ŋ��9ү;���_뼶�?ý�s[+}ެ����z�+�7p��\\3\0&}���M�\'߉�z�g0�FĠFe��L�LL`������l]�2��z0��؝9���!��X\n�5t�0z蘖��BiY�z������z{�[�k譭Y�;l���VĶ!��ra����k�c\\�2�:�1\0k@\0�P�\\AFHCK�V��������\0�TeMp�(`@���k��.D�eMl�^>�\'�u�c��$�F�Hs��%|�%\'iIs�Г��G��e�Q���Y�:��Q����e+�(KW�2^�,�*�X�S\Z@]ު�/�y/�I�^��5*��#E��\\�6��7U���1��a�|C.����R�Kh��\0⋖���;�9o��k^�p7�HꓑD�@PO*1ji�V��ĶUѡJl[\r�UăRo�HD�g�Bz$]��(E���UF+�������P�\Z�iL!���\ZȩKuZ�Ld�>jN	�	j�\Z����(D���� ^󈇽�����X�S9��c��$	-�UH�����GAEI=�vo{e��X)�o��\0Y��]�X�m�Ԥu�+Ig�.s�\Z��L��	�6���d3��lX�`�Ѐ5N`\r�J\'��g�\0�R�Z��^Uw\'�������\"�uA�������*Y/����^]�XJ{��G�2�0��\\b^���㫵JyW��\\�-@4�M��T\Z4g&$��О���\0z���l�����|3�8��ܑ��{J�6}����B��\nr�\"%�O����%lt%�W\\�]>��O���Vؖ��7RiZa֫\0��{l����\0�q�L���6�i|�K_8`C��E��\r�w��;�V:�=�����XW�L6O��T��!PP�����}[�ත�v��ʅa|�X��K~ۺPʾ�k[����b��4���@&Ahr���7r��|c!������;x�Mw�!�\'����a���\'�`+oR̾U+p\'0C!�u{Y�\"�P����������x.:��M��uO\n/�����|8�����x�K�q\r`�Є�&��)NB��@t�Q�xV\rkͫ��:�e��%�>�H�s��#4�$-�I~�V��T�Z�Q����|M����h,\Z���ɭ�j�~�������ﭹ�\\���u��\Z��\0�2\0��	��,��g#��S#�@ֱ|�L\rh/x�\r<��<)3r|���%�y`ۂ���D0��Lpq����]DC\Z���c��k�H����D��DP�����t�L�~ŧ5���\\Knv��u�E;ڂG8���@\0��<��x�K���5�q\rˆ���7�ki��S��t�7M`>���#a�\0.{C��p��Q�r�#�@F��B��{�\n;8��\Z��01�g�P�+��5�A	k���fA~g$C�HE$ؠ�u�� �\Z��E\0{��ق{<�/��W��_�E�Tmx0{f\r-�rXm��x���nF-�F|gsA%�A�o�z�BFP�{�\'�Gp$�{�0�$<��T�&-��.��@\0�SP\0$`��#0�C}} ;�~�K�/��\0\0�0��`~ s�/�4�<��R�耐�ey��R��\r^�K?�H2_�p�7z�ekc�`�P�0\0��r���h�q�b5/�5���:��[��I�\'O\'�\'� �ȉ\'ȉﰉ�X��{�����[��HB4�L�pk�@��S\0;X\rg�р#�sP}!�Ixx��p\nz���\nlgW\r���Y9e��w�b8���R�\rЀ	�}���ȋ;\r�P�\0}�c�؋^G���׈�e\0���\0?�Y_Tyc�r�W.U�U�$u�\'u*d��e\'����H�0��x�|�\0����\'I([��:,�WnFx�ҋ\r�H=�t�F��M��� 	��e�?�0��y��x�\0\0�x���\r�c�B�#Dxm�T\r`��\r�\r0V�S�%c�X`�0�0�����0X�\rӰ�@hט��\'yʄ���4`&[#9PKGV��Ie�\0	���Xf�������`�I��[�DI`UA46g�fl*�۷��E\"���X�0\0��	�\0�� <�0l0�hB�s�FC�]\r��=G\r\\	?V���8\0�ZI\0Ǜ\"0Ԁ�����\n�h��(�����Q�to��]�I�U\0�I��{�(�7pi����9��o����y��U�UU�=��KE�.��W��pv��rehy��E��r�E�\r	�	ǁ$�ЇEB\0�#�َ�	�\nϠ�Ұ\r~�R��R��@\r�&*�މ� P�3\Zm�@���x�\0�`.�O	VUa�\0�`�Yp��\r������I�	��8j�\0Y���`��o�f�V�f��:7�O҂6����a�0�ȜH����� \08�E���0q}�����ZY\0Ȁ	�8@�p������81E\r�x����\r�	�����W\rz��rh��E����6{$TV�d���`�guɉ� [���X�����������e�\0\\��`\\6f�&f��`�uA˰��BZ����e�<�\'/�@x��m�`\r�@q\"�Ѡ�4U\r��X�@h,Zh�\0^��p� \r?�\rs wP\r<\nO� �\0}ѐ	]d\r�T�ܨ_Y�A�i	6AWv���[�\'fb�������{�W�)�{;�{V��{���%X{�����֟����o��[�4-��K�kZ�8Ȝ��E�g�ך�ٚ0E\r�u�u�H%�4�uۀ\r�b��z���x�J��qYt5��z����VPP�B��g\n�\'Ta*�9�����c�\0�`�c:��i�Yp�����\'X�yu���H��\rF�\0{9�A�=�YEX��F���\\��Y�r��r��LCـFɍ�\0�\Zm����(�D��d[DZ�H`�:���LJ���bz�������\0��\0�Y��뾔���H��\'��	��7�)�{8��9��?{B�ཅ�`��H��6f�K��J�Xr\'�Ѱm;��T\r�d\rF9�d��K���E�6���x����5a�-U�:%$P�p<{��+���\0�K:��雾>���;����{��I��Z�5[������V���Pf�\nj&���Zl�=S4R�T�_8z�F�u\r\'�9M:�l���fN�No�M�m7M�u@we�/\r�/��Mz�<[v������|�����{��{Pɖ|��\0\n@� �,�\n��C�=���k�Il�����k�Ȁ��{\0l�����e��A�;�Z��Ѣ5hvg�UkWL�Gn�Ў92}�!}�������2��\n(� ��4F�M�0np\"��\\�o0\0$:���	 |�\\�?���P��\0��ϕ���Ý�ɛ|�]�	0��쾩�7|��j�((��ZP��Iu�	�Z�PP�Hb�5�=(-.&g��.*	���4�@���Paw�(�!(P���m D�C�4`��im��{����\0��|��Pՙ�\0�L�B,�@�>|�H�N�����h�d6f&f����K�$Uv�i�eCR�K�6�B��$5)�.ۂR�2/;�،���\nA�5�ȩƉM|֠ј|��١�ɟ�ä��S=<l�g]�k\r�k���Yu�����U�\'4A�:�*[[$]|�4D�U�V-� �M[�Ɗ��n�.\r�f\\�<ւi�\n{�\nמ4{��ʔ�ĝ�;Л�ɟ�a]�a�����k��]�9��z{�<p0��!����e���\Zo��%=�$-U3-]ܙQ�uu�O��<�TW@D�������C=\"�B\'����I�{���Lu�Y�&nʦ�����ګm�$�{E��ސ�MP�:P����A��U�:	ޅ\\xQ-\\H���ْ<pC���T��ܯ�_��6�:�mH��\0��ݼ�I�u��̘c�V�A&���ͱ��Pe��A&�!u�����auNM	�-��\n0q0�1\'D\0\'2@����� Ԝ2C�I1\n/�KGz�!�\0v�N�����)� ��������\0��\0\Zy��r<@�m�N���F� �\"���2,p��!������\0a�$����q&�>�&N��|�ח�P}�m������0�?`\'}�����Q \nP��\0�@(N	M��z� \'�(��(����)�����.Z�	Z�Ȓ@y1���(����\Z�r��m� � ��F���O� �� �!�0��_�-Yg�3.�\"/ו�&��e�qk�/�W:\"�_C\"$�0$?\Z�qM�D�^ہ2�Ь�@�~����x������r�!�!���]*\\@\Z۞��Bkq�rQqr\"Q1�� �\"3�P��>c\"���1�B44���,7���3O֒Zk�>��=[�f�bZ��J���ƅl�o��e\0���*�ePv\0<��pW�)� �/Տ����Z`��_ ��~;�\\`-\"�Mf\0�0] g]ۂ_]�K)\nO��w���l��p�*ļs	��;p`��s���P\"ŉV���E�!N����ɍ()����!J��ED��L�7\rУ�r�N�\'\r(@\0?.d(�\"M�4*D��QuU�BOe=�dR�-��<ڲeғ0��<;0��g��s+�-Ä��;��}ϋ�W�\\�����0aCs��3���Bu�d	R���4ϱZ2a��Ba�~yR\'jҪ]��)[��Λ�n��V�\0kײ	�\"ĭo�Y��P�@Y�0��%�,�%JĄI\"�O��:�i��q�e�}b��������9�o뷏�\0��\053�on��65{\r��j�M�a����\ZD��*�mCeK0�h��ǚ��`�9\n���j2�Ulq��HlE()�i�d��E$��&	��\0������ؚ��N�L0�����ih�\"˫!��!PMD@���\':m���n�(B� \"���\\t%����������C{�7k��F\ZJ+�&�\n2D�A:��\rOs�4p2�$SS�!�����s��^}l1��Q��uĴ�?��/sr�l�6�4�4��L��V��V\rmpZ8e�LYl\r���ي�f�R5�j*�tO�@\r���\Z\r�h7^tׅ����x��},�ʋ�r�!�b+1����1��TS��x-�M\0�A@s�)#mu��	�M(nO��U�ր,���q��x+\0k����LF��\\l�I7�l��\0餍>�t�\Z]l����L�eP`(e��+�J8#�ܳ!�Ω�W�&�@�y3�O�)������췧���&��)<�~[NyA�����VMp���I�\nz���p5���L�A�����t���j�[�W�L���_\n�������Z6��6�׷Ԝ@�4\\�X��Pn��T�ѻ���*,�7���yz�u�\Ze��o�\rqÞ��<�o��f�z�I�48k`o�t]�Τ����jR�_&�ֿ���6�\"	�H2���-�r`�x�&5-?�\Z֚ʁ���%N\r�V�tF�p)���2�-K5�̓\np��I��XJ�%:��\Z���$v*��E.0ЂtP��8�h��2Ϡ\0�,C�yt�I����T60�gm�q�� �\rn(�747�LZ*��\rY�\0Rd �2�>~�rZKY�jָ���r�k�\r)�GJc\0��F6z���g׸��h�&:��h+��JSe\"�\'�E�Nr\0(I�����`�6��e��aOقc �蠛�fǸ!�v�K�D��@� \Z�4PmFC���!0����@!%)�N.nJ\\��Kx�R��$�6���\n�#)��F\0@�\0  eӔ��l��Ѐ\'Ӆ\rׁ���N(�J�#L�3���Ŗ�X�����PF@s��<4&d�#��Fp��,��r٬\0��a��V�S������?`�\"�Є�a	x��&��\Z��_�\\&��$�P	�|����u����A�P��5��k_��ׅ�5�e(>�Q��ym�	#�Ct�3��0�A�����^(MӘ�dG�iL�:��>���#\"�y���Ms�� g5��TL�`*P.( ��)th�\Z���$l	�H\0���_�\"�P��B*�}��-o�Q�y�������x�jWR4���HE\rpHCR��\0 P�hl\n]�<hR�jФ��� � Y��y�V=}�O{�(�|O�n��21�ٍ�w��<F�\0\'`���9rE����SG�]\\`��@Da�:��	��A�P�*֐�:���,���U�������i_F���^�:�\0�#)�r\0�1��	0Cn1!�o��\Z�\'5���J��ѰǦ�*�x�ĀF5�j�*�W���k��\'[	�Li�l���w+M��;<\\�\rcz���t�1�O�cĒ!�&�%�p�f�&J\"QH\0\nlsM�1&\\��R��x�p�&��\r�����\0��xŲ��\Z��\0�p�1|�	\\����䚫q��F���Q9\r9K9���T� \0o�\0�p���IH�gI	�]k^{��w�!^���6cap@m�7ʡ&�^\ZӜִ�A,�qh�\0�<���&�,��1�?�\r$JB�\0���R���D�B��^ڃ�kAz��	`����+�ptI�\Z\"����_OyS\ZA�6�A)pT`F�T�ʵ\rl4Y��x�����4@J\Z�6�#��.��G���6�m�� �S�k\Z����9_q�#�\n\'ޚ0��\\�VyI�BZ�lF9ΔJp�kP�g9���rRpTk����&�16�9\Z\" \0�1��ʹ_���\0\n��	�@\Z��:8�a�o��(�@nq1j$�\0� ��9|���٤�=��c0�z<��M�[z��9\"�1H˃�	p�;D<��@�G�i�hF��8�4��@��0-���c���ײ��|в*�+\0{0�Pz( )P�}��ئ���ڱ\0vJ���\0uX��c�j����\0{�@p�\0p������j@\n|���u�>��2��2v����F{�I��-��?/1w�4�r�ÿ��)���Hc������Q��51@����2@e�C\0��\0i�z�Cz5v \0v0��/�(\0d�IPEpA��/\Z+�\nyÀ	Pqc�86s1��s�-\n��A\0/z@\0n��X��o�:�+��9�\0{��S�u��8���;��)����BC:�4y���S�q��):J�e�;�[�jCn�h�hB7����?�Z�5i������칞W{��1#\n9�6���o�:�=��YbA�\0m+�@o@d�3xjh�\0��@(f/`��	����^�8s\\8��ژw<Ls��5L�<F1-����0c����ɇ�:rC7ј5�&���S&���\0�\0�i����4�2\0k�J0����@60K\ne�+�ˤ��/+���H����Y)1�8��`<tt<d�B��I11�,hH���˔5FҒF�{�d��e�I��6�NK�b��\0i�jR�����R��Ni��r�\\��X2؉�w+��«k������p1o�/�ɐ�x��p���<�.��4�\0� e\\I<���h�F�N��N��N����N�<����L��|N�+��� �r���,p\\����;�©l�����N�7h�\"��ʹ�a\r�8X����Ak`��\n�ڣ$�p!��R-�0���;j�<��4c,� %@�]Qe����r�8�,C�ĸ�F�����8�TO���q��?���\0��h���	����1ı����t!�z-C�&sY:E�8��+W�3�65�-�7 �\08m\"P+���i8dȰ�ܩ�RM��K[\0D]�cHTDU�cXQD��G�NJU\0	0��N��Q	�8O13PLQ�Fd �s�S\09�8��k��X)���[J�{B�X\'��U|§��s�������\"\'\nPS��F� ��Eh�Q�Rh��7p��\0I���\\���!��Hs�t5u5��Ui�P��x�FMTr8�|��U���L�Q�F�O�$�KQЊ��J\ns��×r�.q������%*���Љ�!d��I��K29�-W��UYK�)h�6 �\"!�6�E����?<�R��OU}��{Zr��}=��,�J��\Z�N�$���0i���B�����F�	-@�e�L�:K��0-�-��j��@[Ġ02-[*����i��}���\0�r��Y\0��T�\rLx��0Z@TEZ�}��с���ڂML��?ݰ�\Z��)��4Rfj)����@ι�;\r-1�)�0�}\0��Η�j˼%�%�\Z�\0��0\n����`��݆�)͔)eJCx�L\r�4�T��΁5�M�Ԃ\r1��^�t�3$R8t�+t�̢ȸ�L�\r21X1��8�9�T���OE!\r���Q(%�>�͐�Ld��2����e���!�q�0���2�Nʽ8�Ĵh$-���cO��4���6� �Še���m�}����Y�\r?������_�p�L�?����jj�6*4�>���l���C�(V�rHEE0_��,62e\Z�6��1.�3�9��2�DI�*ITc3�0>/�3�c2㣛c\Z0�6��?�c��H��Y�%�B^dHGn�Rh����(��QpG8��Ȏ\'(/X�B(,HC�-8e-�U�. .xeO - O��.؂N�GhG��CX�C��Nx���cނP.M>�X�%h\'h�&8��\'�d��,�,��,�G����,HF�,0C�-S|�-`c6f�P25!��1Ec!�gI��y�gݚ�9�����3:2�\0ث����c�W�c�����#!B. ��hH��(���F�!AD�) �e�Xd�e���\'���`�RA�A��Y&��pev�.���`g&��,(�c���0g��f���@�dMv/��ނ@@����ȂN(f��\n��-��U�t^��0NH:ޒ�x�k|�ā�bI�gz��H�Dy��.l6���s�1�g!0/��|��&S=��9.�?�>(�B��FiR If��H��f~���N��d��Q&eB�iM\0jN�i�A0�R~�W.e�N�/���j�(����G`�~n�Ύ��P�B��B���>�N��a^�k�a&f�F�t�R>�.\\p����o%��&WE��l�6l+V������~c|�b,��16�{�l_����J�	i���i����(���f�eGxmM�G(k���NX�8j.��W���>eCЂ��.؄WN,8n���-8��\n/�nG�G���s�����\\Ύ���\'�b�굎k�F��`C\0F�i�C���D��^���\r��#�D�D�o@�@jS�@3���p�5��Vl�-6�o,�b2�c\Z��G�c�-d���t�/q�fgފU(,\0�4߄���W@���WPe�C N �2?n��E���^�U0�eRg�k�X���\Z�J e_�\'8���N`��G�Rf�.�C�e����|\Z�����|!�q�]�\Z�Ē@xw!vw!	|\'K�.j��g�b���c��&mQ�jVs>s O6�/0n�0 �t��\\?�08/��&��GX�\\��\'xVk.��ȂNfr+?�P�]\'/x�1 2`n��j�nrjߎ���n����悶������,������G��(��(�K��i�s�h!7w����D�\0�$�Q��+�+��O@�]H��Ou�n�Z�����0P]��>�\'���{�~x�`W���&iDI�����p�Ep1�G��\Z�x20�N&g�~�g��R�����;B��ȌA���=�g���_��.����k��1\nα+~`�;ȁ([��MH���u~���B`�M��M��a��KP�-�\Z���(�����\'I]}��Y�[��T�ݐ�ߋb�0�0�	��s�8p����p�B�\n�[�D�jL�ѣ�y�<�IR8j������5�9Y�h�!<\n3�	�\'��2i˖D��@��Ӂ	�R8��فX�r=P�᳃Y)��w.��}Y�\Z4���3��dؕkAsY�|ңĄ�J|�c_������/��\Z#\'D�Ѳ�����@�epM\0�=j��akɒB�:	��d��@YMz�)K�D_���SLP�bڪ5+��x۾uۖ+[�]��5{��h�rc�-#��� C\n�T�8�f�AV��sz�Q��f��T���5d`�=s���-o���P��d�aYt2I�$b�!�bH\"��1�<3�T^�U�]k�E�@䐎9j\'�]z鵌x�!`�a�5��c�ݗF�QF��7����%f]�%�fe�XFOI	\0�\0���-��B5�E!<l�!\"��o�tH![tR�^,b�qQ9*�Vj�Տw��yE`[�蕕�\\9꒞\"��_�y�A$���A��B[N9&���P��\Z�4��yZs��+f�0H�4qO<A�HM8�D$�\01\n� �$�`b�S�k@UUY�.Yj�cY	���A��Ux,�����2� 0����Fa\"l��]�+��If��Q�0�\n#��`��<8���q2.d�\n\"= �\"1�Ј�`2B�l��\n����o2���xn{]j�`	t�Eeqd[�g���{�:�\n,0��I&�/��gSj���Tnq����H\n��M�����T3x5�T`�ш��#N���=H�0H.�gB�����T�|R�գ�jEĖכ\Z���᝵��c\\���Ww�/|���u�f�(Y�sg��f����	F3�\0\\��ʙ`������K&��B��d����~&�r�\Z6?H�*������Z@fw�a��l*S92�bw;s �V�c(��|@�i�_*���m�m��G�kX�\Z��^Tv�l���^6�q8��ؘ�i�C\Zb�����:���ɯIB$KX�\"@�=�+$�\0;%Ou�����3]J�9H���E�ɍUd���^�.��/	=��T�\'L�tx�lh��\Z�F4P����.�@\ru��\n (ia�\nH�4�l�~�Y�%��)밮��\0EuE��cI��cȰ\\Q���yRڀ�+ZH���� H�A�m��	�4�n{�����=f���da6i��P�@L�G�(/���+W�K`��ĵ�����d�6���! =Ḁ�T	=��3��L>��J}�����g�i�}�F%p(�4���\\dB\Z��� �6L�.2�Q�2�h�����I�g��-�ӂ�/��k�W7�$�l��+T���3w��gg(��Ӂ]�\'�x\Z�y�3&�\'�!�an��V�G��7�F����rU\'��Y��	 zn���3��#��\\r��:�1O��v|�k�0ӫ^zdc@ӓ���� ��;/���Ӱ��,c�K��2�@�A/�L�e�\Z�E����~�}��N���j |��#�(��R �K���5�����;˧�)~u\rz%�)Vʁ�2y\n��f9�m7�ޥ\'Q/k��m��l/.-�8v�$����Jf`�Ը\05�[Ld���\r�k�aB�¡����tQ�a�����*�RRq�� ��+����Г��b��!�z#�h���u/�7\nT����[g2�X\r������ֶ��@L�qSs7�lw�	:P0�����7�&��5��>��|]���4���e��i�f3:�aR?!`��l�(���{=���H��r�O\n��\r+c�ز�D��\'��fv�����\Z�H�Y��h���\\�!s�ȅ�r1e�e4�������`��KiqO�bbgj���<��눊<����8������v������\nr,c�ݫ��3M��ޢ��[6�\0lr�5���`�p0���&4q+TӖC��=e�@iQ�B�U�K,�sWZR/�y8k�J ��	;�&P`�8e)�!\Z4�\"�`����R�� ���@xd;��OR#>M����X��6	�����M��\0�&7�	\r晋9�/m9�U \Z�5�8�wQ`0���d�/ ю_��d�7��Q�*l|U8��QbT�g2��>\Zt�4�P�P\r�A�xgv��,�D���3� i�D�R\0�n�k\\#�{�j�w��@Ӎ_-�4\r�Al̈H�t����$�u�Z��©P��i��+���\0oh�Zg=J7�o�9\Z� \0�ۇ�\0g?;��&�cC�E����#��ŧ��F{�����@M�&@����o��Bhz�����F��<\\�v���G���$���9�E�t�+3%t�����^�%\0��_�\r��:�8�4�P\nM ����\0ό��$����d�nI�9ߐh���	8؃��C8�\r� \n�\r���5� �i\\ix��5@f!A�����\05Q�H��؇�Ց����7�֕�7xC����a<�D����\r\rVM��`�������B#�\0\r��8�(�Bx`c%���K\\��|@\r_�z����\0K���`\0�`ޠ%� &�`&r\"�`���Cc��_\\��\ZC��AH�v�rq����Z(P��;(�tA�*\\9�!AT$��*��V\n�����B�B K́\Z�؂���3�B\"D� �� �@�U@$�7\\��U\08�Kd�i��\">�(�����%�\r���C���DU��U�>ȇ�v��\'!@>�\0��\0��Y�\r&�H�9Q�Yp�R �`@B���U���\ń\n��С+�����`��,��0�0$�+$\"8��D��mR`��	>\0�@�#\'��z?��嗀I��\r��P5���<��(^b$�)z�YD��틾��N��N�و!@9d0�.�^0&�;�d�˪�[�>\"n�\0Έ\0&�$�X� �,��A\Z��\Z����Be28�3,4.��ͅTЈ��h�Ã�@$K\0>�D���]O��K����M����-.Pgp� ?\n�T�=(� ��@$MWP$��T��ݎ^E�`&/&�!�H��H�E��B�)b���\Z\0�X\0.P����\n�@��4,$�\'��/\\�/�\08\r8�3���=V��Zz\"\'�Ip^�i��id[�U������������� �� P� |�24�C3�A,`�h8�K�(K���|gxV�:��L>�gV�Sq)�C(\\��\"\"&c�g�\\\nL�Ea����ڞK���N�a�\Z�*��\0|�,�B*�A��\n�h5�܎��=��\0�(>�(��i�\0H�9h�+́/(B,N48��eϽ8��D�����d�5���8��/\0Q5��J��\r��R�p�;(�%!�\'���FL���(V%b����8��8$�^%���\"���Bh�S�i��e��`�$́N6�*��0��|���\"��j&�Z*(Z�o�#8�(���\n���Y@,H����$T��^�T���N�;�$@\nT�1i\'�,?��:؅�a�)��_��;�X\0\\0�X>(�;�g0��b�阎�8PQ�d��^�C�����P]\\�Ie�\rҡN�kO���*��4��N�fB�l�V�\0�7<�2@���\n�迺���Vi��� d�Y�^t��FC&��hH8�1�B5,N�Nj\'�j��bp�J�QQ�Z�E��\r�B+�Жnb.��v�9�,ؔdC�F\0Yȡdڝ*���\Z�A��\Z�A,����J��j�of�\0�\0��7(\02�R\0.X@�]�ڞ4H V�8���N`*��f�=�]�*5�/`�\"&�m0Y*V�&� >�CD�n�,�(����9���C0b�`�l��b��8L��^��~J�R��H��Wڙ�|���*�@\Z�\r��퍛Y���`��\'��5d@&4���8P�f��ٙ�����`~�,Y����c�����}��D&�\05�����1�.��V�����ڃ:Tę�Ψ�Βta�-I����Xbn�ю�\0�R�Hy\\y��C,M#\Z���qX�\nXo�*��P�<��r�vZ�p�/�7��̤�a&�b��̫�O���\08P\0&ԉC����\0h��b��V����<H��0\0��� ���� ��Hě��G�|Jt�\r�\\Ma�<�� ��}�\0L���%�]8]x�`�E����*���O�@o@��iR�XC�����$X�p��\nxh�6���p��|����Š�(i��̢~�5�3(��T�}\r\0A��Z�C��L��q�:q)������B�tIs�\"�a����,�\"P}n�IpԁM3WPc0z��/Q���#?�Cv.up�\0h*rr�2́�Ф�V�*&`���p�e\0;�ؚ�R`�\n�\0qGY�<�u;@����\r�R;5S�5;Ѓ�,-�nd4ϧ�����.Ƨ�i�\"�L�2W�c�\03kL�L�����,�#���`����#%u(5K\'>�D�v�¥8)�@MZ�4D�A�UBsu5�FW�T��M�C~�5�CBe�c�L���:0@7��7��	�vUa��CA��x�\n���L��sV�ݎ\Z��4�\'c��N62[Q-~���1�!��Nᚑ+���D��:�`�F�\0�Cj?�T�Q�b�-��fZ\0��K�V(hT#��8Vy�hi*8$\'	�2&|�[�;<�+�7n�05��6�����ܚ�d�\0?����z9_\r-�r��+��]��\r��y�fQ\\N��7��P�\\\n��C~s��1UF�Q\\��;��<� >��F�S��\"\"����*ߜ�nhF��X6�\\\0&8�4����3V�e��#w,>\0���I[_��:�_��;X+�TΖ���d�.&2��|�҈�l\0�.*k�ؚ��\Z(I�^jJ��\ZIBR�Tz\'�D*�H���B4T�7��4l5lC1e�CI;�95�$�lV,�2�+�\0L����Z��o������HG�D!�<���U9�&��~�\Z{k֝�`2�/�7J��+�_Iv� �L\rL�+�r�P�J�������<�j��~����iȇ���&\0`�S<������p^bU�|\0̼�׼A�\\��C�n�A0���2�t%���{���H���fkbF�b-��b���c5������g���E.�xO��0Q�X���C�7ip��h\0?�-�5���^�ѩ�PC��T-�%j�v��pF�A��YJLa�I�1Ã�< C�{�|V=b��<t�+��i���Hj��#�\Z`�) ���Ք������y;��̖��Ǎi�ę��е\08țx�%R\"Kd�\'\n����pp��W�<�V��aipU��0�W@M\\�L=���}5��\'�+0/�)��4w��ο���}�a}�~苡�Do�z�\'ϛ��<�\0�7�%B4��\\>�&\\<�1߁s\r�g\0eJ�V\Z(P \Z;p�2e�e�.lpxjА�g��2d�& ��kK��)ҥ�X�6\0�\n��dY��^J\nP�<`��g\'�����\\[F��\0r�	�!C� 2y	,,��q�/��q�q�\'K^̘�ā��L4h��;#ܰbb弙�gΛF��e\\�Qd[��НDV%Jt^� 0(� �ʕ�\Z������Tϕ�ҧM�^;���w�^�YA�q�߄�5�,8�s*���wn�ȏ�e4��\"�h5�댰�L��B�\"�̱��l3�k���:M��11�&:\0#:2G��\\�/8t~��t���&���{#���A����j�k�#�(�*�I�����%dT���4�=�R���p[怼.��\"��3��!��\\�N<�s3\Z�,<m�B�0����>dl0�@F �as\"�4b7��\Zi$�3���Tb��p��G䢩&�X0�����਩\\[ؕ�]u��\r�\0�\\j�X�P�F̹���#4��KE�J�Z=�L���Y�N	$��;�|��?�=&�@	3C�D3�7�G5�/6�Z�	&)�h�U�yԂ�%��\nn���M�F�$F�+-Nui*�Ğ\nr�����AIQ�dDa00�]1���h�f�AZ�y\"uiAV���g09`��ï�kz3�c���&g	�^ O���ή�4����Qy�г�\0cHRJ-��\"��ӂ՚�$�@���fL6ٔ^�\'��OU\'\Zv,@���6\\�s����\Z�PD��G�N�\\v9����\r��\0��j,���ڱ���Eٶ�w���\Z\\r����=�t�)c^��5H�\Z��\"�\'Z�?L3�(?�Β��<\\�-[��c�e��\'�i�h�Yr\\�`C�A\nQDBP A�07��\Zo��b7�Bnp�<PA\nJ7��\rp`�N~�����ăw)���滱�x�J�	��6�`�Qq[�ش�jA{�^��%h��$����B�=qp���\\\"1���%3{���\0�	��#F!T�C�$�(�!(�B���(P�xDA	<��j3�\"���B�okb3�c����i�j�S�>4��E\"�kjc�g���DR�x*%�B	 8�/�(%�	�������}ˑ\\� Db\nShC%d \"�\nmh�0@e�T�:0�A7�����pk��Z��t\'�	jPB[�L����e0ޒۤ��|�g�˿��}@k$�0��q��D>��BA��}�R�e	UD\r\0*����˥�fL<�,�0��10���a��k}��g8}�<ٴlfc� ٙ�d�C�Q�&WCO֍?Bd��R�ǢshO?C��}��)��)���JY&���\022_K�����o.ba��>rR?>����K���!��T�����ZN:���{b�������4�Ԥ��N��bH(K7�Q�\0�ȏ@q��\n�>��Y��DP��.&)�C7#�RK��J���&L?�\r6c��\0����aܑ�x�5k�[�ʹ�E>�A�Q�$)ٶ��)��\'��D��Ȧ?��HH�J�\0o���h\r����{z�n&J�\Z�E\'�ܲP�04%��j������,5Er[�kV�!�h�0���ל�\0	�Mx�.t�$I�V�/�zH��I�,�#@̈���P�G7�}FZ<UJ��ekA�|زߔ��ty|�[T\n�(�\'Y�t��|E�H^Ҵb9��Q��=�;p)�6�`���w!\r�g�D|.C@	Nj�-e� ����TH��\nT���W���<t�3(hc�D�>��LԱ��h���%,�qg��)����91X^��mnc,�qs�S�,��6�|�� ��%�sRW�b�)��.��M~�,��p\nZ)��_��8p��B}��-9{.��ka�m�:�S3��~\0f�|�f/v��mR$T�ڇ��`�[o]��\ZZ��#�5#��$��+캁S!��H�1Ͷ�.�m�A˴�m����Sқ��5�e��+�ÛgW�akh�D�2�6�^�jcj�4D5�zG�%5�:\r7���7��{��\rU�E�ԨO\\��Mؚ�ہ��\"��>i���J�_Ot�G%�.N��\\U�N<2�L��k���&-�?i7�������jX=���S�)�`\n\Z���G|�i@x³�l�)�Px9�Qs4���\0y����|%*ax�3������ HȀ�=$���cBD��\Zш(���Q E�@|(L!���!\n��NL�	^XD!\n�	,$\"\\�>\'���w��?<� L����#�K@\n��A!��,<�����߉B�q	a��A�`�	��&a!� ��� ����o2��� � A�@��2t�\r�B����&/�L!A�����\n��X�O��/�L!�\\�WO�0	��r��O�h\0~`\nq��H��|/\n����o\0a:!�@������	������PA����O��/aA�����o�����\0p�\0���V�� ���$0#p��3�)0��\r�@N��M��@��|a�o�l�L�Ag|Ajq�l��6O�n������ZP���a�	]����\0�0�\n���~`�o\"!�`	�`>�%�� �\0A�����O���K�B�	4����%�5�9�\0\nP�p�1�&#��/�o�o��:1�p\r�PJ�dT�A�~:�jq�*O:��@��T\0%�*gCo;��~R(o(/\Z�1	�sH 	��\n�ra��V��` ��� ��/2֐A��P8!��@�L�b\0.���\0!�`�0��<�ӏ��`+A�O�OS	01 a�@�/���\r�!$K1�R0!SeF�d\0O�mqnP�Ltp�$q%�����b��jQ�v����2�n�([O	�@e�������rF±	a	�o/1Q	��V��2�3����Q���s,�Q�ذ\'02���0�8����p���\n��o��P�r,��4����<\0Jf�FeLP6A�1i�h4K\'ՈWS�ic�6}��b�7���Gq��do\n����9ő�\Zq�\r\r;����a��,5��r�������<0�/ �`���8��<���>�pI�0	�+�RI��o�� A��sA�A0ȏp�dL��6TrSM&q�PQ�dR�6A�;@�v7Y�9��*G��snT	e����sz`*��~�	�o��\0q/	s@�\0��,�t���QHpaK�0$;�T����N1;� � \0�.�o��?2�3�(>q#�����>C��0@D\0V_E�P��e��dPR6S�5K�D1(�CK�$M6}1%\'�[R&etH��(�2	1G\\!)���Gd�\'��\0��!�����ưA��`J_�4��,ղ����<��/#pI�T�̐��s;�.C��N��&��qW�1��A=�ֳ3��� ��d��b�B���\0D��be��mqIt,��Uhf��ՂJ�B��\0�4WQP�Yp8��c���s��*#�V��dW�	�2\0�N2;���	6�\n�g_!u_A�u9�X�O����r��N�t#M�	�3��i��j��@����TZ}���3\r��O�lT�}��FH�X^\"b��}�bGj�%�o��6_���,�4s�S[�����s*�\0�`�\0\n,�	�`:ÐH������	|@AJ��u��� �0Z�V#0\0����;/�i!0�8#�����(�Z8O��\"�/���w�@3�����l����(�F�㬤(��q�qdIb��}\\b�H�T�Vn��p����\Z�Cc�r,\'.\'|���O�@l��հ<GP�x����Q�D�K}�i��!+�/k!��y;2�4[�o���	Ȁ��� ��\r!a��,������L%��dDL�L�%�#qV⬒̬��{�D�ܗ|��a�r�6_ifU�aB �`���\0xx�@��\r����}W�p���o\nnU\"a9�`R�L;!O?��/Oňڹ� O�����%03��A���!=i���o�L,���(�E唗%�ԇN�}�D��l}��phD|+Jb�����<2`�Ba��vA�`���aj�hzvav�@T�uaL!_)����s8�c��&����ع�!� T�������p��J�=��F�c�Rٔa���aH�PYp�dK�d�mY|����n�\0Dz�Ńr\0t@t@��fqV�5�u� Jag�1B	���}�%F��c�X����}�N��U��;�\r:���U�����	\0�Vo���.Ђ|:�\0X���G.�M|Z�[��;.>��E%���FHe�X���*�AIR\0\r���	��6��	�t	!$�`ؒ�����FAD\0�Fn�vnGea���{Y�{#ږ�m�%a|��$��7�B�G��7�N˴�KÓhLLb:\\�F\\�z��/ڈ��\0D�2`<.@r@�Ka�ҙ�y#33��\0\nT�-(`�|,F-HI-�%��|,�\"��g,nK���m��a��	�{�#��$��=�.p�⠡����Γm.�T�X�\Z����XX�V�A\\@��g`;���R�U���AY�/a�أ��.f�->�ɛ|�j���8-�bƾ��	�ޣ���ʳm-�b��.��L�\0�-��>�=L<�g��fLp\0��M%����;8�a�tD���**�����z`s�P��`�� O	s�/���k�T`�\0&\0�G}{F���b$��#��L�`�&Sf��r=-��{ڊEւ���y��(��<����Ҏ�\0��Q��J�ϡ7@��M��*�7��Z�<( � \r~\0l!\0�32�����x��i���<��Ť��Ŕ��2�.���(���E���Fb��H�h��4.파|B�5��޹C��b�ېݗ_IϿb&�ūbR@�a\n�	.r�t+�s�����`N�.�8b�f���7\0?7\n?�+�6+�H��s�njC>>��>�:��~�g��{��n�C�,�������\Z�c\0��$  �ۙO�\rQ=P��6���ɀ\r,}�/�ޑ�s��?�q`�����ˠz��z�|4-��BF��\0j-v��С��<ͣ�����5��y#�.^����\':�c�C�c���B\0*�B \0�֠hM-9�����e��i�#F����t�P�G���x6a���8�����V���eK�4�c�;��@��9o����悚;�4h�}�`2�g����P�N�z�꾨�(@��)S縒m���T�_�����kիf˖�Z��չt�~+\0�2��ϭ7�v<�u���,XbI���dʏ8ָ�h�eH�i2�����Ω��	Be��G�$Q�d�&�Ao��ϡ�)]T�˭\Z`]/ٽ���;�8��V���>��T�|�??/�\0=����%\0n@a&�puD4 �x\nH\'Y��t�D�[r�\r.�H$��\0��h#��!K�Ť�9��a���aM1���PGɆ���\0�E	gNvҝwUz煇�r|�%W_l���]8���x>&���1����أ����=��\n�|iF,����AK���*�0�C��D�N4�(P4�.<C�I|�v\0&̈́�KR0O�\'qH�:vx�ME�8�M:#q� pd��}\n�����\\P:פ��A�{�1�${�ʊ��)�\r:��S\0 �5H�嗮Ē�@�9g~�����\"\n���PJ)�4\"CC����3�^u�3nX��)�9���aY�Xۊ7��芘nz�yڝ�ާJ�\Z��}\\��8�8U\n��ה�:�\\��c�5\\�A5* �*�A�+0� �*�\0�pK�/l�ч����)�H\"�o@����T���I��UeQ�գ����9-�4�m�5�o�=Gd��]��TiG5��V��U��j{O���u���z}�m����\n�\0_�s��&\"Tp\r=����2\\γ\n\"�	.\"�K,#T���`0U�}*5��-m�uwI7e5rY�Ք���d�Q4��/�\0�j\0�nۍ�_��0����J��P:lk�Pc8�҈��_��5���\n@{�y�b� ��2.��t�}0�(��.���\\U3W��\'\"�t-\'��Wo�U��*�:ގ<E����-�a�\\L�6)epV̡X�F�o�*o�*�d�T` �F2a\r8$\Z<���~�AdB\Z�(b&nXD��h��__�2�&�k��BQ�`r�H�$BY�\Z8���.��ܜ��3��HN���2�2756���x&��0G;[486�P�\Z��4��AT p���XD2��F$[��L\Z��@�\'�2�&��$%�I�N�E��kQ\\�Ԋl��� \n�6깇my����6��1<�]��$z���;[1��DC0��-yCP��p���q#�s����t�Mlx���� V�&/x�(^��	��SֆR5��)���ĬR�����}�n+(��#b&oJ(��ژC��U�{\0�#�YHj�0����\0����\\\0椆7[��nrRi4���!���Yd`��(�Ջ&*��n��)�����B��0�6Om\nX�FB�)�*}4\0.��@B�7��	��\rh ie$75PR�j���<�.�0�=�}r�ZE��`&�t\ZW^���H\'.YFP&0ˀG/edw���D�\0�$ϩ�Q�&��M1�q|����烢%�y��B��*��$� �Q�&��o�1WF�4#�m�\npt�GkTˉ��!� *���\Z��1e�E9���!��Z��<�;�y�*��w��R#��J�Q\rb��|rX���֚\"��	��V\0��%0I�qk�����F\n�ă5�uvy�t��.KYq^;i���آnjlh��v�����M^sN��^b4{�<��hKH\"���jU+o�jW\0\'��D�kI�HS��*z�Jj��VY�%;�it��J���C���;��\0�9��{t��?eB��*�M2��\r�f63b���r�g@\Z�b)5�MiQ���퀅��l�\'��.t�NF�S��KZ��R�xf�g:꺮��E�uמ#r�,�ʼJ콰�sޜ��Ξ�yq� ]��#�Ϻv���c�\\�\Z��5��L���o�y��!��,U662��&�J�YI(o�|�ˊ�â�D\\wQ�7��%T������w/U����O�s�w�8��!VrҐ_&\"]>8(\Z�\ZH0���GK\\�x� �.q��hISW�E)��6>)�l�A)���D�7��r%����s�����\'�\\-���9g�	=�īy��<����B�X�\"\rk(���C4�G��hFX���F6\Z�h]���D���G�y��R��C����ĀX��2R2�����zyT�G<�ҥ�E�������a4_3��M��;m�5�]���\Z�dG�ADdK��/i��F��-���@qv>��$Ư��ng\rDY��WC�K�˨Hqx�\r�a�׽��/_y^a��ث|Wm~$��\Z�}�ϟNU,�y�!� o[�Bfb��v����~�=��^�^�l	l��h5&XK*H!�A� ��j~�L�S\0��0z�y�0��D}�TCW�!�]�x��L��6X�_e~�N��0�l��	-`�G�Dq�����z88 \Z��Is4E�?��\'&qa�|���61���#�\0��\r�����+\'�w�j�)�s��t�QOb<�&%��P��<d+ut��+�P�z�g\r�\rF\r�`[�5NgN8H\r�NEăWD@\0��?��*�6��!�be�\"#�nEYadT[s� +k�N��Zz�BG�%��tw�B>��B�Cs��w�g1Ze<;7t �g-�_%up�c��Rp\02������X�{<i��\'E�mH�4��.!Gj#�\Z3�\"C�E#r��Ā|W7�Uo�R�i�U��K��S�b�!8|c�K�B\r�y*%H�RR�0(D�$N<�l?8N�?\"W���%�@Tt)�/\"fJK×�Y�M��χ+X��w��x�L�g�go��x-����L�U��g�HܤRTG\r�q\r�\05h�7hD����`GXi�d�V�\Z^Q5����*�\Z,rw�ET�^Z�r���ڈ<��0�4�!X�w9}��o\"Ho�GUu�g��dN�8H�`�2?���Д7��z68�S�?GCP|�S|@�B����/_��)�)#v�b$]�\0P��\Z$*�\0Z�H}X���w���s]�q)0U�/~}�=��+�`�e\r(5H�M��1\Z����Mɔ5Ȕ�Y�J��Q@L���Օ�vO�s]��`�&�n���y�Ƈ��4�BVE}�H}\ZC��~�Wy���oB�w�6�<��\Z�+�+ǸR�Dl�xcv�3����!���II�ٔ����qH��GS.@�wQO��8r��@��E*B��rȀF>�iE<�R�\Z��NG�y�yyiL�CA�Zr��ÛK\n��y�\r��= ,-\0?�c:�3?@D?mj?�YD�G��iJ�q \'O/Uf5$\"T�Tr��]b�����XZȀP��E;��pcY}�LX¥�4�z����v0��6�Yc�Lk����\Z���\"�!� \0�j��Ad�����9�E�N4:m�5N�;��(�E��@/��^��p�E�9�)_v|�����Kz�N��1�����\Z#��j�zIUSŋ�W�;�+*D����ya�V�S>I	M��%��	�����5�\\��|�\Z�T��5H�;�XzJT�\"P�!#4]�\0gdU��׭�\0���+�*���s5	�෠fv0�7�����7ۥ��C�f\r��y\0JY���ٝ6��9��i�fS�BPY�iQe��!_��OP�]�������p��^��T4~��\r\r$+Ml+M(�1�8�GC�Kwt�PSxLP\"t�lj�twR�h��u\0&uI�p�wN�׃g?��>��@1�F���{[{��!4ZJ[�@�E,ңR�r��\r�T�p�1w;d1��(� M��<�&+�ՠ1#P�ڲf���Vy8�AY\0$��%\0 �V����\r�Wg�lD�z��R4(�N	���4L�4JP4�^�6�����N�(?A�A,ǅ��\0b��: r���C��+�@��\0���T\0� �7+t;G]e�	��m8��K�� \r}x\r���p��\0�\0A[>��[le��G`FzwD��?S�3:��%E�Ra`�O}�)\0eT��r�\0.w�G�v5��\n<��dI�Fp�4\"�0�Y�t���ɫ?�ho�xtC��o���� \0��AK�\'��%��sL�$\\�\\�_��hƍ�˰��D\'����hwX@��4r.�Y��[���|;x���+��Ɩ$?�+��\n�	ܢ�oË4�{R��i��f_���ӭ��1Z��0�\'�������L̿l���[������=\\���X�S��\"�%FAq�[غ������`�bk�Q[�Q5Nt1�JC���T��0� �`\'��\n�\0@�\"�1�# ��X�����q{�W�I�W!��9��1�*,��ƾ��Ǿ�oL�!�[p�`]UиGE��%���|�]aP�D+Q\ZB�]Aʀ�b��`�	@�	�:/��(Mǻ?�H�`����0��2���s��P�qX�40�Z�/�\'\'�\'�al�!.�s����DH�6�\r��&�q��Ak�%��y��r�[����\Z��.H#.�(%�7���<��/�%�����GL#\'ɀ	PEM��Yd1��TH��cOM՘�!p9� 4�i���<\0|�	z� ��1P�F��%�4J�\'���L\0�`\"��%�х]�$-�|-�v,����%=\0)�U�DPH�NT��\0w/����D��EY;#\rȱ`��[hĥ�a���z��؄[�I�\n�*0�P2��=���~�������PO\0	+.LT.c�W+�o�hl�\Z�{}���\r�\r2II��zM��,\0�����D��mL�W3��K/�r)��@`s�������ak��|5Ze�R�K\0e%I�-?� �TM�s��	�?�t�<�w��0\0��	?@!{2�&1X�\Z�3�!5\0�\Z��=� ]�s<�s\\Й*š�ѡ����r��BN��:XE�������-�K<�)�QK�~�a;��������M��\0����a�Ɔ4C>�e=���*�*`��l�j�.��p:���\0������	�}4�r\0ww�)�0������ё^�o�\ZM��6\09zG\r0>\'h�[��˄=��<�SZ,��1��Sw��O�rj	�O���b+��퀲�rO�	0��J\Z\"�k�l�UH<�1Հ���8�0ES\0 jPk�h�Gpz� ~��P�@���\Z_^80��?^�����[M���hս���x=T<0l���}»<\0��C΁X�(OVJ(��\"�wj�k�Q���ΉJK�ą�\\�\0�P�`Jrh�1���K�Pո�8��\"��)�i�t�퀐��I�������[L4��?I�17���]ҳ\ZH�P�$hl�C=�\Z�	�`���`s0��� <��Kl�&�6R���dJ�Qw������ZH�b�|���������Pe3��d��Z\0r��\01b��,A���7sR��E��-:��9�L��Z���\ZQ@�H�``^ʒ�� f\0����gS�N�f�B�D~#H� &Ѫ�6����C�X���fs�Uz�_��;�����=v�H�SI���|���=�`|���kn�9��\'�0���?�� �dy�[N�`�8���;pN��u�U��7��:�\\���PR����	YYiT���Χ]���z�J�(SP�:-���H�WS{͙�y/�o��mў-{��\n��U\0\'mРh�*H�\r�Pj�	�E�n����L��i\'���	��i�zN:ǜ�\0��5<@C�6$LC��	�C����+ǲp�Ew4�l�s@-4\r8�\0t|�P$��a�\0b�J:Fz\n�9\\�c�v�㎋f)N@h)E�j���>*�5�.��㎻�d�\'��c���\r�z����� �L�o�/1�@�\n*��h8�b�D����)�k�S���q��	m�p.M��TSE|ǜK�y���[����(����٧�|�����k�	K* 5�FB���D�@$\n�|rU��#@6�$�]�P>D8�\Zu1x�j.��\rl�&�j��,4\0g�nyFOt1P�\Z�1pjL�F0x�@��/�폀o�3p!k��Ͳ��-z�9\0����K���m�������h��0�X�,�w,KlF�6�L�S�B:�O2`�}V5��K�\0Q��`Pe�B���>��C6�Uw�1���5٤��5˒�;~��o�c���G�` �+����DǤ�\02��o���L�Yhnn��A���\Zk;���\0���0V�<\\�f�\n��uY{=1^�IQ13���S�q�s����OB��۞%��棒\n)\\����P\"	>R�#Ud���0�s�\Z80=k�z�{�di�y3�`��\"h����F���|* ���@��@�ģ���$�@�ldJ}�;�1\ZU��T����B�ND�jQ\nQx;^%�.R���B����$\'�X��,ը�q�V���U�v ���`��P�zO�2PA���n��[ܬ�G-��P�/,��l�ܢ��J5JQ�\"��\nE�kT��`\\7���Ģs�{��\r�fCs�]J�!�(0~	��r�����E�+Gz��F�j�)]΁,�4͆L#I�D�IlZ�^�4A�P����\\�mu�W ��P�\0��\"0\\��%�+*C\\4�c��\0F�D��N�	���ai����E4�y8��\'�=L2�a}P���˪^���*g�Z�*�����h����=A�4���x\'1�\r!:$N*\"`�T��%,�~�\\��B�R(��Cӗ>ѩ�\'��\0W�BB8���1�oz���4���f�R����c�j0,\Z�� �i\rjHB\r��p!�)�-�	x�P���T�p�>	�HĤHV���7`��Ĥ(��ጇ`\"�\n��>b\ZDqX��AŰ�(\rz \n[,�;��&\r6Χjg�7�̍&\Z\06�(\rH�a}h�\nܠ΂!�K?���.���d��x���1���/\Z��\0��B�ex�@��<���4\\U�B��Xy���k\n��*��j�	h��bȡ\n�4�\r�`ڴ�i�C�\"v@����,��4�*��;kz� ���\0�X�+L����pEͯ���\r��K\Zn���Tu��)�> %V�B�C_v������d���!A���\'�+el���g-\Zx��!�����Vh�!\Zd�$Y�4e�xH�h|@=H�\Z$����] �oO8{���0+V��\nW��)�B���S8E�>�Ԉ͟*U{��τ���JIT ��\"�0� (j���X�v�1H�c4/,`�\"U0��kvy�\"�`2�D�2d4�\n�:br\r�t��G�iZa\r[<���jm�ύ��Yd1h4�n�\'��O5�r1���ȗ=�1i(�m����m��K��N@�\0C\Z��v�;�p��4쑎NU�C�C76�a~��Vq�$2�;*٢q�uC������IE�xx*yF){T<�2�׵a�°3�|t�lNlbl�m�:�D����7D�9�Y�l\n��wGg�4��	i�\Z����ƃ�h�*{�����p�7�o��D�+��:\0��v�����U\'q���0\'z]	W�+ƀ20����l�\0Y��Q�9�4z)jnq�s�(�\\\0���CWfkĔN�<2�֐6+�\Z���ߢ#\r��K �\0\n��G�L��`�oN�5��m��:@�4Xg2Hd�4Wk�I��(g�i��4��^u�1o��3$��c��^�ok�(�Q#ҙ��T�����	�H��y$�\rZ��\"l�sp�K�X��3���� �e�8#:i<p��ژ����q0�e��3q��h)�xX2C�4�oX>�$��Yy��ۮ\\�ޙ�� ��L�Rᾲ�$�QJ/�A�j��Ȝ�p\rEA�X�����	k�0p8�e�_`�)[�h`��P�����\0{8�\\\"�F;���\0�1��X�k��v��g�:i؆K���Az�ᑱ|����At��\0�\\�\Z����>���z���M�;P��ɇ,��P	(3%SL/T>�|�	͒E�ppP`E�H�98�hP�0�i		��~(\0h����v�쳵�`\n�:��zh\03Xi�=[��Y� E�A�\Z�O4�¨xG�\0(]�w��J$��+��!}\\At��|�1�A��+%���&[E(su��Wć��Ȉ�E|����8�=�H�^C���\n�\0\0ܶ�	���\0��Fw�-�\n7±��{\n�[�w�\0W ���@�h��ˉ�\0vx�����K���Vq���\Zw��J�$�D髻��A�4a9��(�����8���sx�	`ph������Ŷى�q�8�^�Ț%�A(\0�p�o��0i:{8\0�����-���\04��X�������vHlڳ&䉢\\�Ӭ�i���>�c$\r����I���o���b���Ű�z�DN,��������� c�g8\0\Z��}�!�;�}���N�hp	W��jJ ��̬�op\ZP�`�!I���x\n(��(�	P���X��2\n�����腁`�� ��\"����{J�I����R[�Ā>[��*(���Ny@��!N����д�2�V3��J2������8�(�P�#q�w���\0~���<�I�\0\\ �q�j�=੐�6���������X\0P�ɀ\n8\03P��7x�\n\03Ё��P�y�фb��O�AĠ�� ���1����h!Y��Y��yA@�F��]Ǽx����OB$y���Q�Rr\Z�h�e��K{��T��\\�)2��GQ��@����P��\"�h�ܘ����#ep�Byi�w26{\0pHʲ\"�	ش�Y�r[ѱ���MM\n�쒫��3��A��V��>��!2��>O\nE��Ԁ!�Q��O�Lݲ\rL���ٜnk��x�Ɓ\n��ذu��У\0���`��\0ǘ\n��:{P�(DC/���T�Z9��$�n��D��S���T���a���C�E:�:M;�B�S�y�g`	�c��2��hC렆D���\\ȅB�-jh�A����4򩁠�	�t\n4�(���\\-�\n�a���Xx�`��\'3����������yԝ�}Vש�:���D��X�YQ�A훋g8�DE��T֘W���84�5l�Q�X Ē��L�	h\n��\"3H�b`��������	ab1�=s<��ا��|�J�XYڜ���Pj8��������M�P�^Š��	���>겐�9WA�CH��w5�Ը\r�H?ט˵��k�\\ȄL\0�D���V-��iXuxK:L��b�jh]�@\'�X�����e�W���x�텤�iY���+ɰʃ�D�\"�M����p\r����\"��J����W+>�A�@�����\\P���L��0�ι���P���8��=(�j��hP�)&��:�c�\0�eY��Z�)����\nQׄ:!�1YHʙ����[��.�]�y����^�mĸWB�G�\r(;z�5t(,��U0b��?�����7D�)6�.r�q;����YL��l����$���}�a@�Mݬ��p��ڕ�H���M��.I�?F���\'d�����\\A�:�4�=T�P��@��͋�<�UD�1��I<׀{@e�؉�Vs������TA��ى�YАA9��O	��r�{�1�`M�DDGb$�[	Q�$\\�����������Ȥ�xDd\0��@\0d�Y�g�M�1\\ٙ����N�E�����jjH�hBs6�N�?L!��)8�P�����\Z>�;ͱ���\"���\n�M��Ց���XQ?���5��^�w��Qٻ��L��Ӊ�G��+�V>�𱷒ͭ���	�8c&s�V!��똢)�5Q��X��:�ö�M����?��O]�hl��m�X�`��N��%#�6і�hŌI̮�>5�-���f�����eF��pْ�^��`��`���S�F�	���6v���\r�\088�Ѐl��þ�۝�,:l����$b$��eI�� H\n����s�D�⻙ԋ}j���M�}����j����F��$�Ʈ�+`Ε�.aII�+o@pd���9�O����;��1	tH\r�X�Lh�ȅ\\�p��_]�3;�րЀ8�ގ��}��,&��b���Ky��q��\\�(�S_��Pf^qfզ���\'?��o��<��o�Vz+���=��� �M���o6���W�m������:��\rτl�Ķ�H��;#�j	n��	��\"�Sh\n��^�^��\ri���&�H\0\'���MY��Ӈ��qpo�aᕞ�r������>�Ө\\Srd�{(��7�u���7Ѐ=�þ�@�n���l��X�� ���K#��Tt���N��������h���(�tMuԶ�s�h��oO��G\0w���Z�G���!4���\r�r���s6��υ_�#�bg)�8�c��d���.�!�Z)Μ�aH�x���	���hk��C�+�Ty\'W��o�L\'wL�j�Ђ�t��t���S���hgv���4\"�Q�fin���䨹9B�s�}�p�L�������Gq��3��L\0���!�,�N/��5N���L��\n�~y0�&gyN��q�\'����y�n�t芞h$��y�yg٬˲��FG	Yc���	I�����zڨυ�Ά���lX�ׅ��\0W��V��z���O��ԋ�����\0����(Q��h�h×H$8��\'��\'��`~�gy��t�y?m�����K�P�{/Q��e�4���SXC�Q���UHDG\r��y�AH�@?j\r�P�ǀ\r�u��VmP.�2�I�B�>�X`����0�܁��!@0!��&�4i���r,]�s�`����f�̉�Lf�)09�Ϡ>�%�i�ʦȚ&(�4��rސy��-d֐@.[fn���y|vgn+��o�~+��[މ��LPZi�\rRK�)כjoz#B��\"b9ΕKW]��M�[mt����yn�a�L�>�6,2dj��\rUeV�U�-�P@�&��o�<.\\�8�͝o>3�s�*�Bu5�K������\\H�^�3��c�|�m���y���7�N�\\z�u���7VP\"�7\"P��l�!�\"*`b�#�� �#4bo��B��\0#P@�G_y�@S�L(��q��xqC�9���L�U�Ts�A��8/q��U�! �w�7Az�yd�`�s���G�<�ݗ�\\l�U\0=	h�sR��D���:JS�\n�B�+2��G\n��R�s� �\rR͈�iA��(��A#d42#�d�nH!�RI�Ŕ�q\n,`\\��y� W�r�%�uHT�Ke��7&uee�R>К5�G��>g�u�Z��9��UnEEt����5�:\0᠊�!$?�PJ?�!��4��oDFp��6�Fc�~p��!�L0�m���ӱQ59N��9��kM�ҤӒJb���	�tRL\\!��`��z�V{��f���>\r�����E�]颛��r]Z�!VM��t@�������P $(���\n+ˋn��dX�so� �����S�965S72�?~��/���=>sI]���5##UU5{3��}�Yj`���z�VEkU�V�r-��u�����׀TG�`i�ࢂ)Z7�@8�S@I%CH�C�nLƶ��x ����U�P&�=���ƓR�K����dq�ۤ��G�4�Ɩ�T�K6n�^��xĤ����=f��Z�e\0֕�>l!�jG��YP\"w��\\*����/��`&��J�\0Nh��1@����\r��=������C!\0�����n�E��o/cR�g8��$851�ʖD���r-��lC��$тV��-���#)���Q:թ�u�`�e���.j\Z�\Z�<Gxa3�%����{>b�h��E\nA�m�Bl@*T�H��˞D,�t#N�sr�*j�(=�NP��E*q�*��JͲ$���A�������|�Z�5���#�jZ�����9�ř��D��GI�.s�A#1�\Z���D��PІsF`�\r�I�6��g��p#Mi�\Z�\\D�\0¯p�	Np��*�939�X�1���F;ڊ�(�K���+��ƌڃFҩ.�1]E�����1�HCWh$�kFC��Z��64\"\nR�g<I�S�VT@\n�ЁG��6Y������r81W��,Oj�I,��K\\+���+(A�;viV��rL�1`G֑F4�c[�8���l��>�9��&���	�G#�4��\'u9Oּ�B��D�$�A� ��Q�PH�b1��rUK��8b���l~�e�t\"�qD.��j��\\�̌*4�.���򨧀���z��-l=-@{�F�B�V�Mv,:�\n�Ś���Ԯ<9/�nI}֑��d$�\n��S��d��G|S?R\"\'�QR~��$d%�1k�Xm�#�C<�\r�y�#&��,e\Z�{�融�#s}ƚ��6��>l��9�J���\\ӄ�u��X�N\\t,�ԫ^��2K:v%������p�@mjy%�^��~�j%v겘�V���l�+Z�u0^�Ca\r͍h�+��G������Z�W�$��Z\Z�ҥ�h6P���H��$֒$O)�C���t����oRP�.�9���;\\�����ٜX)�˵�RLZK����`��Z#\ZG��a�yXMq^]��uL3�8�\\1��ї;�e-��K���%�.8�Yz��W����j8����s��JZ��r2?U�ۜĖb�Qzj4-^�\'�[��e7��=�ڸ�s�d���~�O���̸�U#���>^͟r9��v�q2��1��cb��zϭ�7�))��8]��F������he�ns5<R��6Mr{����2�������=]���m��>�@G��4]�	��ȕX��.�֕ͽ˲�*N�Jd%�}�ͯ~�Zm�$��RTb�rۄ����-qY�க�d�yz����)[sݖ���&p�	�i��1��j�˃gz\\��b�SF�Sn/�c�����j�a�#��hV��Ȯ���v�Ym\nDko�Z�H<	��,-���/�������k���ְ�Xb�h�-��.g|�I�E����b���r�_�L�xe$,�W����(�B*���_$$I����jK��x�;��X��o��M�;�Q�E���[\0F G�Z�	^�ㅙ�K������ǘ͚@Wӭ�	�Gu�˙����	�5�W� Z%�蹊w�U�A�q�q�\n��)	u��Оv`���I(��X�M�B�Z����֕HuСŘQ�\\�X�y�}�������\Z�U~D�\Z��u���FȈ���Q�=K\r�X�JL��M٬ Y��O�DI�-��Ĕ��8!�՝x��%�CY��\rʠ�Ua{L��1��Z���)�U��`�^t釈�G,^|��\n�at��@4��]��|x��M!����`�@Y�P�-���^�1	N�Si\\Sd��l� �\\ݝ�Y�Һ#0�Qr�P��� }�����`ᩉ_�\"Ҥ�|�}L�E�~P=L�È�U�9޼��&z�ZQzxL��zQ��K�@�k��dQ����U�2����ǹ�Q�ǹ�U�	#{p����\r��ۚh�ω�� SM����	�I~(�\r������cEЃ}��}�L�����N���Z��˙���^x,��U�t|R��XS�D�H�	`U$cJ�\Z`\r��pP�=�B��\"��\ZЬbs�K�V�`�G=�\"�4�Is��P�	`��!#�e����Xx�G�AU��L����X�RiZ�Ut��DY�Ym�X��8�G0��͝������Y��,F޸�%_� ��Z��N�b��E��=Pα��Q譗������ɝX�Ñ	E2Eb$���F6ၙ����e�\Z1v�&�yԃ�䘈�ߍ�J�UL�UL������	��N��F�!_���(����Q�b~�U��H��zR�y����CwJ�l�����U��IY�eŕtEV��~Z�9 0�\Z��܄��(�Ū�f\Z��s]�s�Yr_i�i�����\\^g��څ�l�G��_��U9\0;','����\0JFIF\0\0\0\0\0\0��\0>CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), default quality\n��\0C\0		\n\r\Z\Z $.\' \",#(7),01444\'9=82<.342��\0C			\r\r2!!22222222222222222222222222222222222222222222222222��\0\0d\0S\"\0��\0\0\0\0\0\0\0\0\0\0\0	\n��\0�\0\0\0}\0!1AQa\"q2���#B��R��$3br�	\n\Z%&\'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz���������������������������������������������������������������������������\0\0\0\0\0\0\0\0	\n��\0�\0\0w\0!1AQaq\"2�B����	#3R�br�\n$4�%�\Z&\'()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz��������������������������������������������������������������������������\0\0\0?\0��/��&��^�*��p?:1�Q��~њ+�����o{0����.\0��$5U�����B	�q�n��ck3ۤ.�`���ݣҷ���a������.O��$5�J\Z^氭��{�ߖ�?x9�mLis$EQs����T��v����\0W�k���d;.f��HF+��.{�z�9l�Y+iL�����j���v .��Sԕv�B���?�V�:�\0T�ǡl�Z�4���f=�{O?`۳n8�?�Ex�&#k��\0��\0�?S�t_֣��h�!=���Ť�sFhӮi4]`����!s��G��bk��F>����k����N�0e�\"���+9-�Q�s�ŝ�������TS�[99n?�:�R&N���j5䞽{����I(��4P;�����֝;�Q��X�,⼐��\Z5b���\'�=����4�@�9�/j��������h�����9!Nz>G�OBk���f�P���� �}��y�q�΄+�7���\0Z���|�1�Rj�mprʤ�ޔ�Ɲ�xcz�浂��z�W=�]�\0��I��k�S��(��Iܑz�jE�/\'����>c鞵,c��J�I���GO���ʸ�4d\Z�	��X���δ>^@�}j��v����;��-�«bq%����$���~��z���<�xS�=k~���m6;;⅋F;��+$.���09��8E�B�%-M\r7MY �]���*�\0x���Ui㚻\ZĦ̃�0# c\'��l��J�\0*3��=�:����B��D����/|��?\nf�~�?٦�q򚿧ʰ<S<;Ǒ2�p�nB���T�?i��c*0w�TD��v�O�J�.��3S�Q��\'򢀹��R��L�*��{S\Z\";P2����wg劂}kY�㊯2��1L�knbw&�I�eG��q�.\0=y�j�q\0�J0��ÿ� {\Z��R,O\r���ۦ�B�0����$/�����6�-����>�Z;YY���u\0�[V�<l�IC�1�@�5S$��YS�8�g�!2`\0ִ#Vق0\r]�$/�\\�G��4}��P�z�����f�;��ɤ2&>�ZON1S;���*o��~�����J����̹�Q�s��N�\ZԴ���E���ڸ���|���#h��0Z�A2�\'�:bD}�~�m8�I5y��z��+�χ����?�iF\r;ܩN��\'�§𢘲.G4Uٙ�r�:��\0�6����T��e�u����o�\0g�%7������o�ޞ���\0|����]��e��Ɠ������Ɗ(\0�\0����y��\0|���߻�\0�p�G�h���#�S�*�S�5\"���s��~�?�E�_�J��畿����EP#��','GIF','chartecontresceau.GIF'),(5,46,0,'Adagio En Sol Mineur (Albinoni T.)','URL','http://multimedia.fnac.com/multimedia/asp/audio.asp?Z=L%27Adagio+d%27Albinoni&Y=346265&T=Adagio+En+Sol+Mineur+%28Albinoni+T%2E%29&N=Albinoni&P=Tomaso&M=Forlane&E=3399240165271&V=1&I=1&G=E&audio=/1/7/2/3399240165271A01.ra','','','',''),(6,46,0,'Pochette','URL','http://multimedia.fnac.com/multimedia/images_produits/grandes/1/7/2/3399240165271.JPG',NULL,'','',NULL),(7,46,0,'Canon En Re Majeur (Pachelbel J.)','URL','http://multimedia.fnac.com/multimedia/asp/audio.asp?Z=L%27Adagio+d%27Albinoni&Y=346265&T=Canon+En+Re+Majeur+%28Pachelbel+J%2E%29&N=Albinoni&P=Tomaso&M=Forlane&E=3399240165271&V=1&I=6&G=E&audio=/1/7/2/3399240165271A06.ra',NULL,'','',NULL),(8,46,0,'Choral N 6 Tire De La Cantata Bwv 147 \'\'Jesus Que Ma Joie Demeure\'\' (Bach J.S.)','URL','http://multimedia.fnac.com/multimedia/asp/audio.asp?Z=L%27Adagio+d%27Albinoni&Y=346265&T=Choral+N+6+Tire+De+La+Cantata+Bwv+147+%27%27Jesus+Que+Ma+Joie+De&N=Albinoni&P=Tomaso&M=Forlane&E=3399240165271&V=1&I=5&G=E&audio=/1/7/2/3399240165271A05.ra',NULL,'','',NULL),(9,47,0,'reproduction basse qualité','image/gif','','GIF89a�\0�\0\0�qִ�5,#�qL���캌׫r�����i�٪�蹮��ϗj�ə͋V��V�\\7�ݸ�uO��x�̧�˘�ٹ����ɫ�ǈ�ت�h�ȉ����ͧئZ�ך��sͺ��Z��t����c��V�ͷ�֚�؈�i<鼞͠��A#�>�DS���ζ�����ֈɉA��\\�¶���{n�A?���pkf������!�\0\0\0\0\0,\0\0\0\0�\0\0���p�+����xP&����)�\"�S��I�z��xL.��O�:bY���8�ͪ��\0���4�fYr%iWV��V\\����g�����S��I�po~\"���\"�}z����{�EbNs)~���ol1�	�\Z�ś��K�B�q��Ϛ����������t��\Z�Ÿ��������x��w(!!%`������	F0`A�l)\\\n��d���ç����X��8q�x��Cg���ΥT�e���,БO�� ���3����?�r�$��/]��#T)�P�65*U鄫X�ܚ�=���E�Գh�f��6)�n����nܻQ����-_�g�j�p׻�D�B�\0�7���K��6˘\'�%�Y�f��3�Mհ��VH��ز�BcM{v�Ϝs��M\Z4o�OSsELxB����}�vf��m?�ܜ���ucO����NCj]�Xy���ѯU�����q����~^Ԃ�{�8���������^|�}�q��~��!�9����.����:� ����Q������0�H��\"�8��Hh\"��(���qh�B���ը�-�rZ\0�1�c��\"�X.9ߑ�祃<&�:d��R\"$We��eݖ4�b�t*gnx��_�9�(&����=LvP��V�2�`���D+�ɜz�G/e�@�5�$I�b��F�fD(�s����N�(��B�T&�\0.�(O�0��@�����-��\\:۰��T*F����E��\n)؞�;�$i\"��`릹��CB\Zp��}�O��j)��P��R�Ӓ˪�9X$0��[¹�\n����0�\"\\\n�߼1Sa��1��0C}�(�\0.l�-���*���9����%�@S\'\"	\Zc�m�IZ���D�C)\n]���+>��<��%�lt�����Pc����l\\K.h,Ŋ�\ZBrl���� �`��m�[��(�-��(/�p��`�L���<+�y�L�\r^7t�]w싁�l�<���|0���薷}.�3�\nV��a~w�[>�\0+f���65�^�5],�\rO��<�7?���\0\'�K>�%��.\0	���\n�!���z�>�k���mgNu�uO�֩�̊����wI�h�<�͢D�	���L�w�	h\r�\0\0�p�O}m�\0��7�[�Kd��\0�bG��a����DX:���@����/9}c���g�\0Hj�Q�.�	���0X��x@���J��X�\0@�1hק��R�\07k_�8h\r�����Bw��mWK�\n\"�Lf�!���,�0o:�cr��M&�c�n�qN��$\'I�JNR#�@�T��Ѐ�a����/p����U<�D���cU\'�O�Z�_	�:��1M��Wp8���`\0`�ɽ�9��O��4+y@\0a&eӜw���yv$��Ѐ�.\n&��,Q1��\0��\nHY�K�``� �%��h�pB=D���B�� �#���<Ѵ���@\\@�+�SHņn�`�l�C<�sv�S�#Ғ6�o�q�-M��˹�o{��Y��_nsw(7!�P���yG,j)�TF��K�A!�׃\0��$( ��J@J�����H�����UԎm�T��h*ҧ�P���rP���4J1ך�� ����\0/\Z1��lE\'y��5!kM������gDk[�8�~��|�0_rJ6�]���3E�)��VV�*m�4a?��ׯ�g�:D��X�.�r� y��a��\nf�����$[�,���b0�Y��\'Bz҅l�*��.L�x��V����X��y�.�,\'j���F\'D�dB%���E��_~�D #�0^p7��\Z�ha����_�UӾ�80نOY���\0��ܝ��U�F7iV�	��8N)�ƅ�4��	u�W�L\r�`&��\0�y�@n���(!d	�`�}���/�ыt3sM8�(!��6�K��8�*bW��l#��HZW�g���A�Uе�h��A\0QX���4���9��k��G\0arUV�aKeY�t�\"�|,�hp�tjs���\'�vZ�\0��m����qNѵ͏�=\r�,T=�6��U�ڰ)��p�Q�7n�9\n*p�����n�Z�8l(ܒ(���|��́�f4��xuWN�Hx��_��>�-��E��M�qi���_�����g�c<�L���B��9K֘Z������lصY^)0�hLD\0��X��H�������Y��f�|���<�� 汑�5���� ��)sR���Ea���sV����w���i�ծ��n��fy�p#��1̉\\��XƤ��d�i�$�2�,�fJ��l���,��Zxv6�j�ڡ\r�2K�F��^\r�Z�z�X����W,��u�o��r�kB�Z�ɦ�]b�\">��\Z����}�:\\y��>��[U�\Z��}`���`<ڪvb�\"�x)~��J|\ZX�����O�Q����\0or`Jn�&��ï�  �p������y�^���@V�Z��M}�BQYG֏�X;*	� |�j���\'\n������L�Vi2�G\n�\0�\n]������]C��w�QuSJp�g޷m�|ig<�1�br�7p�g{w-\'7�gz1��Gu�6��Ķlq�DH��W���7�\ṅ\n9�``{�-\0]s|��G��O\"$W��x\0!c �ф�f\0\r�����~%=qO�B�\r��\0�p;O�8̳3s=�F�`(��f�85�hV���{�����3@1�Yxv�PW�0�cRW\Z�$o.gH,�\"(��*�tH��8zD]ې�A%�FEYH\0\0`��gb��:�w��0�uR�]�-���F;�ԈA�qs��1�i�h(��j����\Z�auaܒ�z������9`Z\0&p������rÇ\\mpL�R\'`8�G��o�qP7���5鐣@�CȄ��~z�|�\\�i�5L�H��W��Ke�V5t-�T)�Au��\Z�a��Ȋ7���x�892�HV30�\ZY�h4 :��\n�-�2x|���uX����]���<���<!}S����0�;��v��+�1\0C)������\0kx�\0v��\rNI;�5�r*�(^�0-G��\n�\'?�S���\n��(����˨�p?)��RG��\Z|��_g���q��lȆ|yTTrr�c�����s��x�\"�[�R�Oa������$Zx�fU��i-wE��B��t]���\"*(Hs��j55��L!Г�\0W�y��������p��噜h�O|�7�WgB1(4�Y���R��I���W}�Cٞ���0�Cg���\'�Oȸ.��R��������$<Y��2(�S��*W�8��FAjp�o�#:0ZI0y��������0lڜ�S�����ɑ�q�H�]�|�FP�}�-�vj�,�X�F������E4<ڝP�5U�\n1��9���)>��eB\nmg| }#�sS(sfdօ]�#I4�}:P�\'�<��<�:0�$�ZjJ\\j����{�Uy�lz��8�Y�q3����ڛ�����\r�v�ل8Sduj��\"�WJ@���e\n����byS���3S�\n}:�����(��b�TD�_�z�q�V������Oy�ʞ̹�W���؍Y�{D��e\nj��󄯗�� �Zs���l04����f�\0z�| �Κ����� �)��ں�����ɎڦI�:i��D��P����$9�\Z/�J՟�/���K�\'��󙲩y�\n�������{���	��7;9��Z&�$�]F7�:́�ސ#�C��+��{7ۛ}����\'��ς��)>�\'��9{�,i\r�@�m�[���\nڶ5��o�q��H�*#���^�|�~+�b�)�.;��\Z4yՙ#����*0;�r��R똕�Z[��b��������ų�<G����֨�\n=x�F{�Z�&;�M�KM˱�b���	*#�������[��۰}ۓh(s[&88g�Ⱥ��h?����?�(�)��[C�.�sh����^�/�.��.���zh��C�&�;j�b�.�g򗋬��O鬜+�^i�L�\\��`	��j�8c�w����J3i�uʔL)|�e�\Z����J�<܇u�\\�6�� :������GE��T��o�ʕ��v:\\���G(�CȜ�[���v)�^c4D�L= A1����E�}\Z3�6�kG���C��Y�������K�0�� &�g�JĞ�Iڰ����{\rc���:�ކ��\\��c�����g�&�~��t�(�Ǿ[�;r\"l`�/�j|?=z;oܭ�e���C�󢿲�d�������k�ry���3�W��*����R��@��ܺN���\0����s̫�́�ʓ��ٛ,��C��<|�\n��+��еpEd��Om��.���˙0ʼ\r��o���,�H��ά�ڬ�Y5p�)�ωͷ|ʟk����\'��Je����L�������;ˑ�zK�.;\r��ϝ��L����-�e�)j@���,�L������:�0ڻÛ���4�$J�a��#\r�!ͽ��2����\Z󺪨�3����6��0�ӈa��{��k��@���?��~)�����,+l�u)��--D��;��´�W�J���$m�m�\0��L�{b�IJ-MM�,ͳ<+�cK�V=%��4]�X�$��ט��8�����_�џ����.,��Mp����3���>�����~,�����ϐK���<2@�=-��[��8ڡ+֠�ץѝn����t �o���:��Ӧ���ܘi.؂�C�ܼ91��-����x*�����=�Z۱E͟s�m\"�D6<�#@��p �4xb�H}Ɓ�h(�Pfc}�\"$��q,���<܋L�����L����|q(����䇭�������E�B-�[�DXf�(�2�n1���\n���Y= �}ߞ��=S���à�9`�-�=��ʢ����:��Yw�������v�h��u2�-�W��B�V��k��\rĽ����-��ă�k<�y���ݗ�0�6��\0�}w���m&��\'��4g��#����>%��En���k��\n�ͪב��ck�~�\nG�<���`�í�q�}e�l�\0)��ȇ�3��绉����<��Y�nK$�2�����\r�q΀�w�?��{y��}�&�L�:�[,�ގ�k�}�fkP|��٣�g\\幗w�뽉7��ΑC1=F��%�T��2�ȱ��}�wI(CķT�ӕ	Ĳل�Y��M/��|�����L\n�	`E$7��(j�����Iܰ���\n=��>�f�����3�	؋�Y��b*���Jz���CP@�H�Ѐ	��ps`PH<�,��#ۋRz�������[~7��s�b��g����e���!^�N�-��m�%���s�͒���V�$�\0p���Ԇ�l�n���\0!s�ІX��o׼�4@6���w��:o� �x/\'y�+w_��[�z���<�E�a�V��%���	���\"O�9]�\\���O�Ʈ���&���1D�_�C���o�Џ�+V\'�\Z	����WG���,��9�P@��_��o�.�w�&�4,g ����NW\'�/��](�eH$v(Ҁ�lX���` ��-G�j�2ZaVk�e@ `�c�a!��[b��B���;5�@4hz\\Brh. ##&���X�%9;	?AC�<GKIOS1��0�0�Ʈ�N��`� *<�h��o� �p_��\0=�R)-{}Qf��5QMË����ϡ��N����e]/}�z}�/�<�\"H ����.]��,P��\rܶn���U��D��\rq� ��v_a)��-4��aYK�MU[�M���a\Z�xnSOs��B!�e<W���)��h�<b�`��d�?9\nPĈ��P�ޅ@��Πfy�T5*�K�Ma!hq��p�-k/��X\'-���@W��eD-್GV�x/3��j�$���	[\rd̲���	�y0@���Q���fXs����!7v�\0�d��X�\0�$�V���/��}�cŻs|@��+̸Ǘ{�D( �gbY��֝;�z�kE���`��KWoO�O͛K�PN���a^6�b�����������零�[!3|l�~�x����mva�/���ڈ\"@`��	 �`������6Cn�s�m�	ib�\ne\\��\r9԰���g���ϖ+�s��.�㦂)xi���o:7�@Ӏ�h�� �\"����)�\Z�K�=��+J*	�r����\0B�\"\r`�#�7��3Rt-�:\r���T�Q�c`��\n�8��ԪφĈN�\'�V�pU��UX\"�(�l�q�O�3\r����ʹ\"���.�����O$M8���O${�Ě��Z߽u��(�9F�-#���SFXu��Ǚ3Luh��yd8\\*��t�����Wuǒ7ޏs��$6�/6X�G���Ɯ\"\\U���_=�\0���X��d������)���XW�G��m}Zj�K���x���4t��3;]5^�B��G����5���̯��%�c�>\ZiW��x����ꨧ�o��}��3$Ɗ�fa\\���D<^�<�\'�S���z�;�T��gֿ<d�u\"�/��_�~��s) ?�I�ˎnx��i�u7���� 6\'YO���BB���R���z���b�NJ@lV���\n�X�эHL�Hx^~��Wz��;L�$�{��\'����A�C��Q.	W�p_�d`�[M�~��h���;\Z�iRe��i�#<_A����o�WC\n:�W��LU�b�v�V����C�0�������d�1T3Ɖ�d�w��!yx4�`ǃA���p�<�>�\'�\n:��P4�2�@i&ˡK�E� Z^���8�F��!L��Jܨ�9R&+�\"%��1\n���� �96����%��E?摈:1�\0��NB]S�\ZQ\'�q�c���*<��,�\Z=�\0��WVr���JV��1O��%e��$\Z��.L$�Ib��\"H�-#�vH��2��x^xR�Ӌڼ�(ى=�Es�~b_5�R�W>��n�%NP���j�4�87bN_�G!.����)�\r��$L�$�����n�����I��$�#�L�J���[�Z(d<�(,&\ZS����<DF��i�U6*U\00Sz<q�p�4<��j�:E�:���M�]�ě˾r��_�����h*Z-��&�\0��3s\0�iFC4�	@:I��\'�HQ�A�X�`��0�ߜ�\'���h\0հ�Y����[C��M��̐]�\Z�l��D\Z��%A�Mm�iA�׸I5*Z\Z�\0�E�\\6�lrX��\0@�Z��n֏x�Ng�\n�F\"�g�J��ަ���Os�O�q�4�#i@]���?�� �����8E�m\Z���-���ܤ�-\r�T $i�Z�*@��LuK�_�f!)�\r��7��\r\n��di����D\"��E�9.S$�ŋ/%\0E���W�S���ɵtr�hq��9C�(�[���K4�a��6쿒��<�J<��a�DD���h����8����,Y,`i�GD��A|�H�XUh��C��n wp3�ތL�m�\\��r��U����m�����ʻ������9�\"_�X����u�/;�t�$�^W�o(R�Kc��~0�6���\\�.�Xt���w	L�\n\"�\\h����ҁqQ�B4�S�W.\r�h��\n�}��=��ء�[\n��Yk�N�d��n/oR��F�ږ>�@���#G�D�{u�z�`X�瑇v���as�w\n_(Rl�\r�E�E��Ox	����i]|:V���\'V/9C���\"ǨK�Ծ\"�I;���v����_<1}B��=�̇3�AG�9�v�/�鈳4��ˈ������קFTDg9N�h�DyB[�I��7s�&۷�-oT���|ù\n�~���(�W�Z\\�kU=CW�LY���c�ۀ����Q�A�-��2�>m.\"���^)�Nb�x�;W�t�2p��s�板�̃�k��W�g���S��kX�����c&%�Q���W�\0o�p��譿��Y&�h�\"g����yq�<��a�=@)=���Ԑ��\\q�)�m����˽��r�SY�9��������+2A���l��\0���r�{p�e�\0\r��\0&`����D����R��������H���#��/ќ�Ԥ�M��-��\n��b��O��\n�o)���<�(�mQ�\0�#>^j�4�@�\n�-��>@s�9�\r\Z���\00`\0�*\0ƃ�,>6�Z6�t6&�<�,.U�\'��wJh�2����Į`~&h~�gx��jA��Ϛ���f�B���pF�q������N\'�p�̦��\Zz,�N��d�6р�ɒn��P�ƃ�Z��(�Rf$�]1��?� �\0��n1�O-7�H]�\rb��pDv�����t@6���\rikv��B�2���=��L!�����<�hg�|�K~	;vQ���D��pee��#�{sd�0���|��$af�\Z訲���n���}rmW���T�E\0tJ�A��&h�eI�\0��q��L���w17ao�%�0\r��\0\0����#*oy )zI�|LН.M.z����v���z��VL,���d� �܆EM~�f����d\0Ӳ�x2#w\r�Xr�\Z��a���8�J\'��	|� 	<z�;�T�,�$�\"%1Ŋ��eM�h��vL�&s1a�`�\n�0@��Os�d�hVKA�/�i3���Dn�������M��A/f���@ lR� �!�磻�7B�0�\Ze��ާD&�s��|M ��-1ϯ:S��uP�#����\r��_:$P\"�P:��ҘCT��|�,~E�1/q�_>\0�T��0\0��+G,z��\'��-�ᛔS\0$��:\"S:9��Ps��.��0@���.�1<�8�AJ�\0q�%ak$���$`�-��x��������\n`�>���@ղ����T@�bt�����lFˀB}Q2�J��Ԙ\"j��CAT�4&$���?E�S&�d�r��L�R�I����hR%Kj��0\0�z�B���|�9�t��L��$/S��@��L�j�p@!��\rY�p|�Z��N��\0���_�02�?{�?��	+a�&�H�	t�g,�@$�3�L�2��\"�X-�2A02����NQ-ÒD��<o1��I��!\nA��̽i��S�OV�s\0��6	A���7��6F�F[�ŘrB}�1����WY�]m���Y��\"�DX1��!�-,��)c!)��[]���ѮRTk�\\}�\'\05�!B�T.8�b�Jќo����5Y�	�;���x�SM�`]��D�%)��J���%2܎��s/��I	(Q=��]w�	�z\nRn*��^4�j�P�(�k0fyU��gM�p�����5��p�§�l�����%\Z�n\'ˆ��WN 0�%?Q�c=$<D\0Z]�҈h\'a���:��̔l�P��eӶmE�s�� a�65Ly6m�vR����Wa�4�6���^�\nnT4_��.r�pxwv�o��C���5�R���W�Ө��Nskl9�\0��漗U�:�t��Hp`n�Gz�0C=�^�q�l�v�\r<�9w6�re�[��e��,wmon*�A�r�!p�J�hAB�E�w{�W�7��W���{���{;xG�L���ʎ|��5��B���;���P7����(7�P�	�R��qt$).c�7t�k�d�vy��z�W�vl1��7X{\'��0�s�l�1OC`�Wg;C8����F���!��Vh�K��W���W.�.��גx8@Ņ�x�;�V1GMӀ$�/xμX{������Y��m��oOƘSGվ ��k$��vE�\0�`�5�����}k-v-G���0��\ruU�\"\Z��vH�-=����l�\'*��E-J@!6]�����8lD��o3��%=�$��{��v̲��^ف��e���#�#���\0aOKDk�Læ�w�JS��Y{������8��Y��9X�����v2����\0�x�w<�t�F�]͗�3%f����d�0�$��\nB�zK����\0̌l���ŏ�εP�٠�5�rb2��\"G@Q�W��`zn��tk:?����{��;�3�j��`W.禮�&P\0��b�������ly���mcO�|��E��Q�D����:���,�uڽz���yf�U��⨯�a�U���tS�I�\"\0��T�\Z�.���i]�kKy����b�xa�)Q���M~I`��%yᒡ���I��8�~5Xj;�TV���Z�U��Z��Qԃ����BH%t�b�.�f*���OR�m`�p	�\Z�ȦR���#Ξ�I��2�e♍G�\"��J�vlX\'�nL��*?c���2���Z��k��x���*��`1���!�����CZ�����<{x�A�E��@t��n��)�zC!�Hxh�ER�A\'Y�;��Iy�\\���0t�Q�H�OoM�����1�vN�=���\n�U\ZY{��W����Z������\\K��x�;��x|��2�s:]|E#�r\n;� 4E����o�����ls����q�z\n?�4�`�Y	�1W_���\'|	k�·X�\Z�?�\'R;�\n��#!�R�X1����Ƹ�����I��\r9~���Yڭ��\'��p�BZe�җ!Us����s���,8�H��0��3�-���CsVu�ɿ0]̅/�8}����|w�\r��M��߼?vw �PP�y��N�����줎1�6�XU���E��w��&����k���爦RW���3}uէ.Ks	ڗ��j�<?��J}��g������OȦ�ta���_�ȱ�^%�8�n�l������Y=|v\'��y���IFv\0f��5�K����L�m;�vʠ۹=�e�{�$�?�gz�g�\Zg�]ߢ�c������LW~/\\ި�=��y4�����ݹ�E���J���[��DV�L?8��+�[X1����a�p�w���\r4f���O�ٶoFþ�^���i�wA�Q�j��Qyg�1�\Z�\\�1tKI��>� �1ە��\'9��q�q��#ݬ��/O�c�\rP��3ǢSXc޷��|�ޯ�`�Ϝ���\Z��Gu\nV�O�\0Z��#��$�>X�(�����I��%�Y;�&�fȣe8j\"\nM\'�t2���H��1�����l�h\r���p{�d�����7:�0t����1\0^Q .,�@.LN2*&)ȍ�\r؝��兪�����\Z�q�:HH��ښ���f�V��\0_\"��n$Wh�NE=�MI_i�900on�����|���������ݴ����;\'^5.<\n�HRX\n��)�P$T�2u�Ĝr�<\0���\nZ�n!�5�W�aR8\Z;�q�|��4*��\"�I-aĔq��p���Qw��+\"�ܵ�FG�4V��\\���~�\0^�)^�;Y�R��Ԫ7�%J�U�\"�d\Z�y��+�v�s��b��Q9�(�S�h\Z�Y�N�<F$� �:��&E��էx�<�w�Q�V+=c���P@j.%;vL�~,�\ZP������w���z[\\3�[VjO��녦6n�k^̮>��s�]�	���m�=�]T��o���C�Q�IBy����C���JB�ƛo���qD�!ߕt�_,�\\��=���$Rf{�N��{�s�I��%�X}�� @��Ǟ0�w��A!\\��5�$8����,�R�F3\\�&�\0�\"ᄥd3�J�\r���Vx\r*T Q�	�~}�8��,~�G@4\0�Zx�s7.\"�>�]��Y�ߐ_���9Zll��[R�rL1��Ts� Gh��ES/��DX�@\0�a�B�.ة��1bӎ�h���6D�CG��SD�醍QMA����C��*�~�xE��G\"�\Z{�ꇥ�����)�B%��\r�*�NQ9�\Z,���vu�	^����ʟP6��������Z��@5�:V{�*tl���6������n�� g����#e�ɕw`���b�Pt�` �1�ax�|`|N��xrF̢���nf��J��;Z�͗�l�#�P���[���.1���dy��Wx��Q-4!�,\'�oy�ķ�G�95�$�hk*�Q�*�JtN�\\��u��Mكl�xg!h�Yڭ=֦p��$, v��~��\\�\"3��Q���,ޒ�۹8�y�aJ�� �\r�@Nտb����?�yi=x,L��O�%�Z�Z��@��c��Ø�B2��c��I�m�\0�\0#���0\04X<G?\n��+��<�\'�\r�L��pʙ%��\\����u�P�_�:g���|�S]�X!�yF$|�-b�81%,��a`��+U�J>a�lD$\\���S=�#͛����4��yl��v��)m�,2O3� ����s#�B��=�kE����B��m�K3`-f���	gX�L ���`�T\"�.l,QZ�j����&?�jq�p�x���WP&\\��8`0��Y|[��X�#�1Z[I�Q�[f��06:��Џ�d\"�TUD\'��Od��*%���]��j8�h\0[�G��+3~3\\E��l�����D��U�>0F4�:�e�E��!\0����F$#^��J~[-�y�P��6p@�v�#/_��/2҅1B0L(�\\q��ON�s\'�ph��!��p\'�H�?�c�tJ�#�ճ>�tT-Q�O�����\\L���S�bh����>44��]dl�*-P ����V)?�.�!�ŞveʤL-1��xF	1���3�D	�EϜ���z�O���~)/8jAi�T�`�NW=&� \0Cjl�V���\'�0�UK�fx�U��Sy�\np�1�NC\'\\�h����*a�+��ӽ�}4�k��_{,�˂����-P�r���f���!f#�!1}�\' mX��Xr��h�@����v�����L����g_�z[��;xRG���H�Tm\".e+�\nG��ZM��5�L� ���6b��)�>�����P��<g;�j`[\n�ؖ�(=��$��oQ��ؿ�M���F6����Z��b�^06eh��&�w���h�\"��ioݠ\\�i�6b~�N}��9�X\\��^��v-w�9>h��d�L3vEu�BX/��gȖs��-x+��u2��L0�}��c`�Q��^0�\"�V�i[ۯ��׃!�v�	����.(윥����+�s�{fY�.З�\"	��nhh��B�v��Vw�<L�kRd\0eՁD�N�T��~L��ܶ���-N��5V5�s���Pr�����d�����j\0�?Q���#��\n�\r���]���X�K[0�r�m�>ԍ!��VPu�GW֙��G����D\"��\0y�˹��	�.���.�17�.�\'�.ŵ��Lz�#CIpc��U�:�@����W�-�Y�4D�	���@��z�D^���h�Cʫ+j����/�f���H��r\'iz�<�>�U8c��_�Ny=u��i��t�G��������j89D�U�� �1������P?�8v�پ?*k8�2�d�7H1�?�6��޹�]j��*&R�k�)�yj\"�Eʩ[�\0���=Sz��`�\0P\rA\0���[�_����u�w�=���ھUz��:��{�>\'�^�D� ��Yκ7���1Z|�_Ĺ��D����\nM�E1�З@Z10L\0�䔄ڡޱ]`������g�Jy�N sH�ў�읃�LgL\nYpX�|mC^�Á�a-޻aD/��I2���-�T�U�e���JHp�	�Ja�P�Z���\0b�i\r��%��������I��͋������X�\0�a\\R	��K���\n�wU��@@��	\n�\n`@oAa\"*b\0ښ�X�L!���^Li�E��Ms�\\��p�\Zf��,���	�\"�!��������1y�*К�a�9I��KH!#P����E�@R#����\"��>-[���t\"	���(�BÙ��)J!������r��py�\0�\Z��g��Bq\\]|ʜ�\0�x,\0\r�C%�@	,�����@�̡?#��U��Y�ZY��z]�4b�5�a<���lé�6�a�#s͡@�@%�I0eQ�`�W��ܶ����V��>茁cM#j�Rb�D�%^�&�\'��5J��D���ĥL�)���� \r��T�`���U����;N��U����XBB\'@��?�!NZ 3�b\"��t�&e�=�\\d����(�^E�:~��b[6��\n�WT��*��U�M-:��]�|��@�����DM��M*�x��9Y�2~S&�e��㷴���^�ОP�_^z���\'\\f�B���._A�E!��M/ə@���(�qQN7�h!rPKe�uTh��v�\"����\\j�ؑ����&�&5��lev R�An&Gϼ��f[�pdb�,�bV\"Qd^t��N|�cQKN�ʂ�ƃB�?��a�}Z��	�x��<z�zb�\"��(�\'�)e��ͅ�N��̄�֩K\0����纀��UQ��]c��i�u�c��cV���a�&��\'�e\"�A}�]H �}�ƬҦ|����o��j`p�˴L�@�+�����;ڋ�\0(3�׏F�����\ZwJW�^(���	j��ɔR)l~�]\"�Ɉ��@�&Z:銪���L�$��CY���B%������Ӝ�^���u6�g:bv�a���j�N\r��2B��&:̚�ӣB���Yo��z�3�2ujX�@��}�a\n���%E�����cu��������~�M�=��*+��\\FP�2�<��)j�\r_�a*V�B�&�i�©#�����(�a%�xAv�De���)�*�Z�ck���+x��%~����=L��b�.�Y�$�\"�������&a*��Kd.��)A闫N��I\03T�>�@g���w������N�)�gX*J},s�|�����W�ҩA��^�h��.�\rP^��I@;\Z0�PT��D�dJ\"h�.Fቫ�R�R�-�el�$���}��Tx!�Q�fI���V����ڠi�!`v����\rQR`��@zC�$�@��M0��6���R�\\P]]yت*u�iV�����F	�G	��\n�*����i�Z�3��b&���k*r*�jJTD;�Ag�k�`�V�r����z�>H�u��$Ze�RO�G%�g6Ec{P��XYSQ_v\'~������`��8�R��͂1�a�����n��D�u�I�o��m�2��)��\0P@�.sN����\nr��@��h��J�J���gr�������V�9�cR�bGh��4�,���z�6p�[k�c,�丮�J���&AlA|p�U^�@A��R�o������4��ظ$�@e�@D\'���>b��[�i�V�9�#q�J�.ڥ�g%��CB�@��X��ȩ�ޯ�\rP��M�d�)�iڜ\r;	Ѫaٝv�(���q�2����,�Rj�*O�+xrYG@1`ȓ�P1%\"��De���c�,׹, 3�����>���t븊�����2����4ow�i,-�P�].��=��ML��9�,Ĩ*3������qE��bL�6 �n�v��\\~s˂3g2�n����r�>���t��\"�B+��T�#��Q�]\0!��(O���@\'h6Gb�41BSXB�/A�3��U���U��V���,I�C\r�\r�(He���`=\"�E�F�*$�[���U捻�)M۴�����<���=����r%�3+K��\np�Y�A�/O3�q]7��4)�t�0��V_���L�*=״]g0bӲ����u��\"�i\"�N4m�P���>o�a(�Յ1��B!�2dZ	��m�\"�M�h��Lu�����\"Z���h��e�	�2Z�n,Ree�ӦEw�4n������\"G�j�\Z�v�h5w4�J2��m��Ɩ�R��<;+�F����[�T.Q˵`z�,3�i�y\Z\'�h�k�R6�⁦KW�6ysiw5�i���j��:����N�}&�Ee\\�=��ڷm�w���~��|B��#�3m�Z��B��Di�v�+6��.I��Ӻ��Sو�Z�\'��\r|/b|f�&�s���ǜ�Q!�.�\n6�a�eW��c��ڧi�T���q��[�G�����w�X2�x�/�n�۝��_���q7\r�saL�x,g���7u���Y@iTv:��e{@\Z�Z-v��ug7�x+���g�ɀ�A�eN�o7�,\0kdx�s���%6��7��Vs�F�#��o`Ww�����ښ�_��ez����t�~t�wW&5\Z���6-�6� �9���C7�:Q����,6���Aw� ��d�����a��W���\"x�8Ӯ�sVhI��+��1�f��x{{���&��g�`�#��>Qo�̮{��*Y�p��1�*d^�]�k����d����<ߠI͍8T�S���3N�:�e�>�Tb�;��xg}��	���P��β��)�c�\\��#PE�sW�>z=�z)�p��0r#�Y���N�3�S᢭E���r�3-���r�\0�#���h`�_�Iˡ$r����<�����D\'��s����f�jm޳��wd��8�L@��}}+9�;��?�\\8�N+@l��U��պ�o�\0\"w�C\r�E{>�Mg 8��.6x�����k�n���#Y)u���9��y�q�Z��uh�p�����B����\"Ho�~\n�\Z��܎����Є:�Ӭzx�S�/i\n������c����սe޶�\'9\0�<�N���)���H>T2���1lV�ِ�4n\0�\n@f���q�M\Z�b�@�/����?��~���{�dyd�a�+̫\Z[�9����s���j���д\\p(9=���,Db�#,j�Q+���q��Xm4\"���3�B�rl�=�k�UvYl���[��Cq��-�d}Ė��<5����-E�b�h��b>,��EA�p��Eן8*5vD�>d��P\"M�A�����eZ�%R��0\Z8e�ȕ;W�K�N@�\"�R��G>\\f��\"�j�mDgI::\\�QR�M�2ݦP��U�C�jY�dR��\"��pt�z�g�\\˗�d���3���&h���\"ϥXW]��7�6�rr,��I0.\\q�X�f����3kR��\0|��8H7�MU�@ħ�����u��=�A\\�c�$Bs���W�+ů��j�����Ҽ��ݴq�T��mW�\r<\0���tS�@8���E��e0_�>3�q��+��h�%��lp�@/�����fS�9��YčX���9��΅z�p��Z�@������P��<�$�9��4�8���:�O�\nl�m/���h����N���b�*�%ɍ�r���fi\n��\r��yF̕��	%��#F7�(C��{1�	\r���k�Y�G���F�O��h*NI�\0\"�?v����S���\n�8Xig�\'�*QuE$�J�Ϯa�<i�aŹ2x�J����Tj��$Si�M,h��CDT�ӎ�f���B0\"\\\\��:X�bCPa+��z#�DZaĢ�0됕WY�� �\r�H�\'_ 5\r&���ͧf�j�b%I6aΉ�P�u+a�l5=	$de�\"���p��|S@\"r��9ͅ�@��Z��[�zwo��Wf4nIv�LO��fl�H�8u!��\r�@/	Ae\"@���H��KL	~�q�?٘v�y���[��n�d�J�\\s����5K�)��u���ReijЛ?��AH�e�81G�gi��v����z�\'�yS2���Q�p�����͑F0+��q�8Y9_�\Z@\\o�~*ruhA~���b�=dڭhrJ��H���̉�YG��!���pc�� û0�J{t�ǈ�\rґ�iX��\Z#���R�:�|���J_\'����:����3G3�]��$� 2I�%��<�k��Z�g$��}\Z�Q�줮��Ý�����EJ2�	U��m�V�Saks�$�~�9 Ȃ��b���%(9��pZg� WJ��P�o�@�<F�%Rq���b	�hD\0������u��K( �k\0)z`(��IzD��\0�3��,�&��&�ŦTIv/�㞔3��)�+	\rk$��� c\n̈3��sIE�4\Z�X	\Z5�n�Op!Ki�V��N�2dC·\'-�2�]�\"E��@p!���\0 ��c���&�IK����3Po��<�]���T�X\"�\"�Z7?h�o��\\D$��ː�|�d�xp�@&�=��- 	����X�x�A�M2����Ǣ�`AԢ��SІN<-��(��0t6���Х�t��a�(�I���!��Y���)L��?}�6��Pl��u4�#���8*��rtj:�T\Zu=A���,���P�&\\��BQ�3`sa���\0\\�.5�48�Ѐ�rv�i@zP���@�^��P��C\r,a��WwK�+��bю7�,d爊�\n��sd��.[��Vk�h�AW���W�`�\'�ϙV����dD1��3���M��X�h���Ӏ�F�\\n�FH�%se@�-/��TVZ�I2�Q�{PܚVH�(�9�-�)L��4�n}�u���Ծ�#w�_�*���!}�����W&\0�v<_r1���e��V��1�$�?l`���>��	�a����Mp}7���c�fh�Kw0�\\��o��{b#w�=q��̰��<r��d&9�z��.A`6���Y�d.��_>r����&�yYg�o�`���4��,��sl�1���vf❅\\g=/��>�.La+�v>n}�~ �i�9��s����Rzϒ���\'ɚ��r\0Ȭ1�G������t��gTW��y�s���jZ�y���[�ke�\0�`ˍ������O��W7��u�Y�Wj���F��*#�P��&�6��t�����̈b��(	�<�\\�X���%���|�Z�~7S��������o~�+�`��8{�\0�@��v�\\d��Y� l��dU��w����7�w�ǻ��7v���y�w�M�+5��Ô�G?��ρ-h�8��TC�i\\�/\0�ˤLE\0Pk�a\0H�17̰u����\r,	N\0\Z���e\'���n�z^����C���^F���څ�X����!�gߎ���t�[��k��	7��lui;�^�\0�\"�r>�	�e� \0���iA��%䇗;`�^��޳��(�-�b~>�T��\\s����������т����G�jA������k�iЅ�D�o>�|`�a`����6�<��_���\r:��t�������o��˟��������+<ὥ�\0�?�?f�\0�;��#��a�,���\njκ�ʣ$(����\r�ؾM�?��+��ڽ�ӫʛ�����[��A�������+���>���������8��A\Z<©{��b?�s�&��\0��۳B�ºbB,���Z(!��P�>�C�ԁ��4ܤ��A3,:\"�>��:�A+�h� ��[B\0�E�>����>4D<�D4�\"B�DG��!��G�� �M��8��KdD9��\Z#E8�D<4E>����VlC\Z�E5�E5�eZ��XCM�X��Ex�ZF[�E�\"�bDFeR�eA`t���h<Fi�Eh|�k�_�`��Wt����VGr�@k��n\Z��\0�dt�w��x��y��z��{��{\0;','����\0JFIF\0\0\0\0\0\0��\0>CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), default quality\n��\0C\0		\n\r\Z\Z $.\' \",#(7),01444\'9=82<.342��\0C			\r\r2!!22222222222222222222222222222222222222222222222222��\0\0d\0P\"\0��\0\0\0\0\0\0\0\0\0\0\0	\n��\0�\0\0\0}\0!1AQa\"q2���#B��R��$3br�	\n\Z%&\'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz���������������������������������������������������������������������������\0\0\0\0\0\0\0\0	\n��\0�\0\0w\0!1AQaq\"2�B����	#3R�br�\n$4�%�\Z&\'()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz��������������������������������������������������������������������������\0\0\0?\0��Bhmĳ�9�f�J�	\'���_[@�f����i8Rx�q�<��T|`g{Q1���-&�*��~x9���b}�hd��|�(X�2I��/��P������ݜ�=\nq�����_���uk6�Ɏr�FÐ@�?7L��3��qUu?i:;�5�nҌ�}� c���[;M�Vܛ�&� q+�U0�g c��Np5H5}O��w�KOd�ZJ�p��Us��0GR�sQI���*�+��\'c���Ih��	\\����ڜ3�q�A�2oi�Yڛ�3ު���\0Z�H����8�������K�K��Ԙ���ggV���@9�.�{�p����V�)n���I����2�\0u��͙�K{L>4��͵5X�����U,N=�\'��t��k��.\"�[1t=\n��<�`Oi$���u����i��+����q��کCe��,�����4�0�\\%ö�h�\r�T����<��O$\n����F:��j�)�UɈFCc�\'��sVuG������hZ.�l�2+=T�}A5�Ma�.�/�bӚi��\Z4P���Fb�\"�T�އ�W����,@ԭ�\'N�Β��R6��NnPw4���;Y_�ه8Ld����?�U�\r�`(�ڧ��\\�j�\0���8P�H��#��cH�3��].�x�����d���QiE\\�U��)t��ƍӁ�qמ3҂�B?*w��ݳ��q�*�[����Nq��JI4�ؐ���9�m��������FTα�0�9�l}�|�h�?��T?ia*\'�.�߼<�������ޟ4�$�K�wF��c�wZ����l�G2,Jd��dq�x��4��\n���lFD�-��n�K4���t��֑�g�Dh/ �I��H�G�H����J���^�g���I��dk�A	S�s��ϦG\'��{ԶR��x<�pKmy����G#�?Z����Һ�ڕ�����D��rz���T���.tx�	uQrzw���q����ѝO��5�``H�>r;m�F\\����u��BU^7M��l�`9�F�L�]K�Q�D�ԕQ�9���A��8�2M�-�4m��s��!B��z�H/�w�-eP����A8�N���3��\Z����[[9p7���G8�^0h�Kq(9;#GT�?컷��b�v���]�\rc<�]�5�Z�h孑�ȣ�<u<�q� ���o5��[]���{	�\\&z�����#���}%O����7A�_��*�d\0�C7#p)�9\"��}W�Ѯl$��̜�D�+��	���\0�qU�-��R#8��3���;�\\�����3V���8RЩ�<u�d�\09i��.�)�6/ʽ:�\0�O΢Ȯg�bwI<ǜ��$��q����ҙ��7�\Z�u���H�2�w�1ӂ��J_<���lǂ�\0�p:dpBO�Z��ѕGx�|M7��3���#�\0�\\�����Qr[%6��;��q��Mjx�W�����\\�-bY�Wq�\0P	��Y��M�\rb�7t�[���o�6\\���OO�O��X��F�F�8^�u�9�\n�y-�����r$������k~���-��(rA;�E��y��o�f*ܛ8-���.<�\"�Aa�T������&�aH<�TڙBGbNw�:��8�9���=���#L6�)��0H��&�E��+@�r;R1�#<���c-,c(u8��i\"���h�en����?�lU�t�G�,\"f��� �,8;��=��N�{*i��\\�H��Q ߻\0<�u�0��N����T�3������9J�CD��2M��lc��r�!��8=0��?��uA�Q��ފ��j�7S��8�\0�5��JQyV󕔰�~����b�\0�lyƯN}c�G�EmA�ta[�(�ŞF�0�b%��W?籑a�*�|eW�kc�s�\0�F�uی��{c����U,m�Q����Im�Nx\0_����;Ӄk�t��J��1fb�7`�9�*[Œ�l�f#�V���:��G�h��d��&P$xʶ�x$~U����+�A��3F�7\nT\0�29����s�4�fh۟.��B��d�� ec�`�r\0�`R[�]����fťUr%��N�F	-�^3�#>�f���kYB��0����3X��Sw�8�P3��3�\0֭����p��3І�kkf�K&��#;FA��qQC2\\B�u�\0�m�$�5����I$��vS�\r��`�+g�۱Н�~�>��&�ò0�*�\"�t9Q� q�����k�ž���H[X�d�8s���u����ë�levm ����\0�W�8�U��y�j�&G��+J�¿}؅h��6Y5@��m�<���*��Ј�p��2�b�#\0�I���<�o5�ܲD����;������S��n�r۪�ܫ�1�QЎ8=�X���t���̎=Y�\r*Ʊ�`02x d������ɦ]�ç��D�$B�z�`q�����:���#$=���s���֖;7�Ւx\\\0Y�G~�Φ�;�>ht9�=\n��2��,��2�l`�={��Ϧ�%���y,Jr��S���\0`��򫫥��B�HFp�!�#��ӎ���Y4��ٰ9o3������01���x�`nOq�D�>���\"u���C,���t�G^?<�O�(�O��$�\0\\�\0�8$�;g�2:�*V�@*ʨ���3v�Fq�����Z��� �%�8� (��~�䎝�=�yA8�N�.�7.[��z��ҵf\\k�ؠgP$��\0��g]Z�k�W�����!P�\'��ۨ�r�o˯��H���!���z��A��inp�������,:g6!�Gc�n|+�O���i$ �����\r���U���ҹc�jj��Y���Q^���?�N۱��KS\0_�R��w\\���\0?J�|1l�b��h� #����E��;<���%m���԰;�ß�v��`čGR��\0��QK�ð���_�D��dS�֚<%��`�ȕ_��(�p]{I>�7��<���^ܘ�tfs�~��Զ���NF(%k�I!/3l��a��!}�V��OBe&֬��','gif','scan.gif'),(10,48,0,'photo','image/gif','','GIF89aZ\0�\0�\0\0���b.*�N-�kR��dҲ�Щt����j*�ҩ���������qA+ÔUՉ5�ǘ�r�z-q`J�����Ģ\\,��g������E,\'�Y�A-���ɽ��_A�����GТa�޹����������������������������������������~H���������������������������徆�T#���������!�\0\0\0\0\0,\0\0\0\0Z\0�\0\0�@�pH,\Z���a�T)ШtJ�>��lr˽b�ڰ�5���:�F���8Y��{�x�>χ��}����M��] �?s��;	#�0��h������$��w�&R���	�	)(��~����\"���%����P�������ˈ\"�1å��#䙎�,ۭޗ������냢�;�����G�V�\n̴1�&�4�fm*8q��z��]�j���E`�\n7��0�]Z��d�c�Y n���B���/��ְ��-j�����[���xI\0e��Lݰt9��5[�DN�0�WV.��>X\0\\�rc����,��j��@��ԸpԵ��놵{�*9�����P2,�M{�&h&PU��Ir��6�@j<N�4��.�����eS���=���������i��޾?+�P����Oޅ�h��.L\0��t�I�_h�@��s���wl�Ҋ��\Zy�NǛ�����#�	�m4�ZBʥ� ]��%�s�u�&���:�!�{RE(��8���S��q( q��XI.�QWBD���܀� �L{�5Q\"�5�g-&�����f��s�\\D�y��G�X!�T��\\tNؐ,�]`���q�ݑش��_�H�Ѝ�@�l�%��IDLn�#Q9Ý��K�W��{�&f��	h��*�4��7V� c��Y�y��G�Y�&�Y<XJg|g� ���jmg��� �ڝ��7�\0��*�f��S��\"K+�F�	Đ�\0������f�8�,��Yi�ؒ�a�t���a�����ך+�h�A �O��C��\"�������������0�L�y�Y��6��ƯI��S#\0��R�1�(�X� |�I��[nʖV�q������4�<�������t\0#�o�7����I�Z2�GD|s�C�s�N���3ռ������,�9�ҵ�B���[o�3�i�Jw�I<���rO��7X�2�轻7߬����#Lx�w�7�O��هs\0�\0;O�N�7�-��[��0�N�\0^-u�����w�#/�8ߎ{�:�O^��[@��o��z�<3�f����R?o=�+����b_���o/�!zϻ����8���2|��o�>.��/3�wq�,����W���oq\\h��,`�\0��}�8	p�\0��?R�\r%x8@|���G����u=!\0I���̓(D����5����C�o}�؄�N]���� )��\"�0�X��D+�φ��Z���+08�b�S�+�0�j����(���\'���ڸ�$�Q�C���(E8�j�� M�&쐇\Z����t�0Y4$%�����$\00`������pK��Q�v��S�1��$%zHh?F��&9��X�_R�v$d�(�:�R�#�1���Q��a��?O>b�q$%7yE��s�*�i�M70@�\r�������_�V$X��m�`���\'�3_-f#�Q�>f���DXB4�i#>�ȵ�\nl�\0;xN5�s����0\n�*Rt���d��PzD?�t�!�)�jxS��ˈ�!R��ϟz��2=D��YG	�4�Gx���J����L�!FV\Z�\n��QD�U� Ս�Pl=��A�*P��s�((���h-�.�	5�E�\ZR��Q����N�S�U5��,�B����t��%�K7�ׅ����@��|�s}\0<�XsZ�:�5���1x��2�tS��gWp.��e�`��\"���V���ȹս&�m�*j�f6��v���o�K7�u󹖵�TǇ��Z�i��X�\Z�񵖨��?n<�����5��խB�>��V�,�f8^���Omp{��f��-��+X�ym��e��[�.\0;','����\0JFIF\0\0\0\0\0\0��\0>CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), default quality\n��\0C\0		\n\r\Z\Z $.\' \",#(7),01444\'9=82<.342��\0C			\r\r2!!22222222222222222222222222222222222222222222222222��\0\0d\04\"\0��\0\0\0\0\0\0\0\0\0\0\0	\n��\0�\0\0\0}\0!1AQa\"q2���#B��R��$3br�	\n\Z%&\'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz���������������������������������������������������������������������������\0\0\0\0\0\0\0\0	\n��\0�\0\0w\0!1AQaq\"2�B����	#3R�br�\n$4�%�\Z&\'()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz��������������������������������������������������������������������������\0\0\0?\0�����@�L�X��y�p��,�{\n\0��k�kF�yd8DL�m��ܻƭ�Dm�X���Q��y�r�2�#�W�8��q҉o.K�Y�E,�,��r-�i\r����PGR\rp�)K�ZN�cw;�1GҢ��m����B��$�MK���s0�ѿ�P�?�����_�7�J��\0`��\0�l�P#黋��`y�`� �1�+��u�uP��٫dd����\0�^��ڇ����uk���<�P��\0�\Z�Xi6�ik���j�Nz�p�+�-��<���3Z���ZYJ�ʄ��<q�g9���k������a�@�`�n�q��5WG���-�+�%o�!���=�J��G٭w2�S��R�i:�w�[��0~���P�?�����?hӟ�V�*?�-��Ų]��o(���Q�|�_2�_J՛Ú���k.�O=�*�� ��8��ׁ�J���X��\0[Ʃ\"I�N��FQ��!�}����IЛ��zG��U�|?g{���0�d`�����֯1�Q�y���{l��jq���z���\Z��N����&�9&�w����c��������\0��a�\0`��\0�l�Q�G�B�����\0Ѳ�@O�Y�ƅ��?�`x԰�V#��c��U������N���!�x�)�ZK����~=����A�:��i>$�R�;,������NX�f?\\����M�����6��|v`0Z���B��\'�vbygN5#�]��@���������\0��a�\0`��\0�l�Q�F�\0�B��\0�T�6Z(���ց�@◚\0��u��j�\Z��q�.m�\\G	.T@�|���m�=�W);�̨ѮN8������^��k�m��ώ�(|�VK�x�/��{j�ĥNq��o�v��\0y	R��i�d��)�)ݿ»N#��7�J�����\0Ѳ�G��\0%\n���Q�\0��h��@��3Iޗ��(Z�/�v+�F�#Q�A+d���(�p�\0�zp�����6���=w������8�O�gV�q4�>I�<�ǩ�%e����.w)S��k�>��\n���\\Ii4{��6$�c��>G��\\��J��ޫCLL9j;l�>a������*?�-~ѿ�P�?������9ϧ��E\0����(���9���#_�ZM\"·ۀ�;��pI�}�I�@4Q\\�U�J�_�׈֜>����\0%\n���Q�\0�٨���9��','gif','scan2.gif');
UNLOCK TABLES;
/*!40000 ALTER TABLE `explnum` ENABLE KEYS */;

--
-- Table structure for table `frais`
--

DROP TABLE IF EXISTS `frais`;
CREATE TABLE `frais` (
  `id_frais` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `condition_frais` text NOT NULL,
  `montant` float(8,2) unsigned NOT NULL default '0.00',
  `num_cp_compta` varchar(255) NOT NULL default '',
  `num_tva_achat` varchar(25) NOT NULL default '0',
  PRIMARY KEY  (`id_frais`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `frais`
--


/*!40000 ALTER TABLE `frais` DISABLE KEYS */;
LOCK TABLES `frais` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frais` ENABLE KEYS */;

--
-- Table structure for table `grilles`
--

DROP TABLE IF EXISTS `grilles`;
CREATE TABLE `grilles` (
  `grille_typdoc` char(2) NOT NULL default 'a',
  `grille_niveau_biblio` char(1) NOT NULL default 'm',
  `grille_localisation` mediumint(8) NOT NULL default '0',
  `descr_format` longtext,
  PRIMARY KEY  (`grille_typdoc`,`grille_niveau_biblio`,`grille_localisation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `grilles`
--


/*!40000 ALTER TABLE `grilles` DISABLE KEYS */;
LOCK TABLES `grilles` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `grilles` ENABLE KEYS */;

--
-- Table structure for table `groupe`
--

DROP TABLE IF EXISTS `groupe`;
CREATE TABLE `groupe` (
  `id_groupe` int(6) unsigned NOT NULL auto_increment,
  `libelle_groupe` varchar(50) NOT NULL default '',
  `resp_groupe` int(6) unsigned default '0',
  PRIMARY KEY  (`id_groupe`),
  UNIQUE KEY `libelle_groupe` (`libelle_groupe`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groupe`
--


/*!40000 ALTER TABLE `groupe` DISABLE KEYS */;
LOCK TABLES `groupe` WRITE;
INSERT INTO `groupe` VALUES (1,'ນັກສຶກສາ',7),(2,'ພະນັກງານ',0),(3,'ນັກວິໄຈ',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `groupe` ENABLE KEYS */;

--
-- Table structure for table `import_marc`
--

DROP TABLE IF EXISTS `import_marc`;
CREATE TABLE `import_marc` (
  `id_import` bigint(5) unsigned NOT NULL auto_increment,
  `notice` longblob NOT NULL,
  `origine` varchar(50) default '',
  `no_notice` int(10) unsigned default '0',
  PRIMARY KEY  (`id_import`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `import_marc`
--


/*!40000 ALTER TABLE `import_marc` DISABLE KEYS */;
LOCK TABLES `import_marc` WRITE;
INSERT INTO `import_marc` VALUES (45,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >26</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ວັດສີສະເກດ</s>\n    <s c=\"d\">Wat Sysakhet</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">650000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">220 ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສະພານທອງການພິມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n    <s c=\"d\">1985</s>\n  </f>\n</notice>\n','005472001161679380',19),(44,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >25</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ປື້ມທົ່ວໄປ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n</notice>\n','005472001161679380',18),(43,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >17</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ວິທີຮັກສາຄວາມງາມ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">73000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"c\">64ໜ້າ</s>\n  </f>\n  <f c=\"300\" ind=\"  \">\n    <s c=\"a\">ການຮັກສາຄວາມງາມ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ບົວໄຂ ເພັງພະຈັນ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ປາກປາສັກການພິມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',17),(42,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >16</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຕຳລາຢາພືນເມືອງ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">12500ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">125ໜ້າ</s>\n  </f>\n  <f c=\"300\" ind=\"  \">\n    <s c=\"a\">ຕຳລາຢາພືນເມືອງ ທີ່ມີຄຸນປະໂຫຍດທາງການແພດ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ບຸນສີ ບູລົມ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ນະຄອນຫລວງ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n    <s c=\"d\">2000</s>\n  </f>\n</notice>\n','005472001161679380',16),(41,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >15</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຮິດຄອງປະເພນີລາວ 2</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">34000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">35ໜ້າ</s>\n  </f>\n  <f c=\"330\" ind=\"  \">\n    <s c=\"a\">ຮິດຄອງປະເພນີລາວ </s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ຄະນະອັກສອນສາດ ມ/ຊ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສຳນັກພິມແລະຈຳໜ່າຍປືມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',15),(40,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >14</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຄົນຄວ້າວິທະຍາສາດທາງດ້ານວິຊາການແພດ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">78000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">785ໜ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ຄຳຜາຍ ບຸບຜາ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ຂອນແກ່ນ</s>\n    <s c=\"a\">ຂອນແກ່ນ</s>\n  </f>\n</notice>\n','005472001161679380',14),(39,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >13</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ແນວທາງການດຳເນີນງານສຳລັບຄະນະກຳມະການ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">8000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">96ໜ້າ</s>\n  </f>\n  <f c=\"710\" ind=\" 1\">\n    <s c=\"a\">ອົງການອະນາໄມໂລກ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສະພານທອງການພິມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',13),(38,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >12</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຮິດຄອງປະເພນີລາວ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">5800ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"0 \">\n    <s c=\"a\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">67ໜ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ສຸເນດ ໂພທິສານ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ໂຮງພິມສຶກສາ</s>\n    <s c=\"a\">ສີສະຕະນາດ</s>\n  </f>\n</notice>\n','005472001161679380',12),(37,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >11</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ກົດໝາຍປ່າໄມ້</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">700000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">156ໜ້າ</s>\n  </f>\n  <f c=\"710\" ind=\" 1\">\n    <s c=\"a\">ກົມປ່າໄມ້</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ນະຄອນຫລວງ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',11),(36,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >10</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ການປຽບທຽບຜົນສົມທາງດ້ານຄະນິດສາດ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">20000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">65ໜ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ບຸນສີ ບູລົມ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສຳນັກພິມແລະຈຳໜ່າຍປືມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',10),(35,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >9</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ປະຫວັດສາດລາວ 1946</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">200000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">852ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ສຸຈິດ ວົງເທບ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ອົງການອະນາໄມໂລກ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',9),(34,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >8</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ທ້າວສຸຣະນາລີ ບາງທັດສະນະຂອງຄົນໄທ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">5000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">68ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ຄຳຜາຍ ບຸບຜາ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ປາກປາສັກການພິມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',8),(33,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >7</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ສະກຸນຕົ້ນດອກເຜິ້ງຂອງປະເທດໄທ,ລາວ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">7500 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">450ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ບຸນມີ ເທບສີເມືອງ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ກຸງເທບ</s>\n    <s c=\"a\">ກຸງເທບ</s>\n    <s c=\"d\">20004</s>\n  </f>\n</notice>\n','005472001161679380',7),(32,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >6</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ກາບເມືອງພວນ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">13000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">51ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ບົວໄຂ ເພັງພະຈັນ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສີສະຫວາດການພິມ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',6),(31,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >5</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ວິລະກຳເຈົ້າອານຸ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">170000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">900ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ສຸເນດ ໂພທິສານ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສະຖາບັນ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n  </f>\n</notice>\n','005472001161679380',5),(30,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >4</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຄອງແສນແສບຢ່າຊໍ້າຮອຍ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">82000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">53ໜ້າ</s>\n    <s c=\"c\">ມີພາບປະກອບ</s>\n  </f>\n  <f c=\"710\" ind=\" 1\">\n    <s c=\"a\">ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ສະຖາບັນ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n    <s c=\"d\">2000</s>\n  </f>\n</notice>\n','005472001161679380',4),(29,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >3</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ເມື່ອຂ້ອຍປິດສະໝຸດບັນທຶກ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">96000ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">277 ໝ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ດຳດວນ ພົມດວງສີ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ໂຮງພິມແຫ່ງລັດ</s>\n    <s c=\"a\">ສີໂຄດຕະບອງ</s>\n    <s c=\"d\">2002</s>\n  </f>\n</notice>\n','005472001161679380',3),(28,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >2</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ພົງສາວະດານລາວ ເຖິງ 1946</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">9600ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">83 ໝ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ສີລາ ວິລະວົງ</s>\n    <s c=\"4\">070</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ໂຮງພິມມັນທາຕຸລາດ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n    <s c=\"d\">2001</s>\n  </f>\n</notice>\n','005472001161679380',2),(27,'<notice>\n  <rs>n</rs>\n  <dt>a</dt>\n  <bl>m</bl>\n  <hl>*</hl>\n  <el>1</el>\n  <ru>i</ru>\n  <f c=\"001\" >1</f>\n  <f c=\"100\" ind=\"  \">\n    <s c=\"a\">20061024u        u  u0frey0103    ba</s>\n  </f>\n  <f c=\"200\" ind=\"1 \">\n    <s c=\"a\">ຊີວິດ ແລະ ຜົນງານຂອງພຣະມະຫາເຖຣະ5 ອົງ</s>\n  </f>\n  <f c=\"010\" ind=\"  \">\n    <s c=\"d\">7000 ກີບ</s>\n  </f>\n  <f c=\"101\" ind=\"1 \">\n    <s c=\"a\">lao</s>\n    <s c=\"c\">lao</s>\n  </f>\n  <f c=\"215\" ind=\"  \">\n    <s c=\"a\">52 ໜ້າ</s>\n  </f>\n  <f c=\"700\" ind=\" 1\">\n    <s c=\"a\">ຄະນະອັກສອນສາດ ມ/ຊ</s>\n    <s c=\"4\">070</s>\n    <s c=\"f\">13102006</s>\n  </f>\n  <f c=\"210\" ind=\"  \">\n    <s c=\"c\">ນະຄອນຫລວງ</s>\n    <s c=\"a\">ກຳແພງນະຄອນ</s>\n    <s c=\"d\">2001</s>\n  </f>\n  <f c=\"676\" ind=\"  \">\n    <s c=\"a\">050</s>\n  </f>\n</notice>\n','005472001161679380',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `import_marc` ENABLE KEYS */;

--
-- Table structure for table `indexint`
--

DROP TABLE IF EXISTS `indexint`;
CREATE TABLE `indexint` (
  `indexint_id` mediumint(8) unsigned NOT NULL auto_increment,
  `indexint_name` varchar(255) NOT NULL default '',
  `indexint_comment` text,
  `index_indexint` text,
  PRIMARY KEY  (`indexint_id`),
  UNIQUE KEY `indexint_name` (`indexint_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `indexint`
--


/*!40000 ALTER TABLE `indexint` DISABLE KEYS */;
LOCK TABLES `indexint` WRITE;
INSERT INTO `indexint` VALUES (1,'000','ຂໍ້ມູນ ການຕິດຕໍ່ຊື່ສານ',' 000 '),(2,'010','ຄວາມຮູ້ກ່ຽວກັບຫໍສະໝຸດ',' 010 ຄວາມຮູ້ກ່ຽວກັບຫໍສະໝຸດ '),(3,'020','ຫໍສະໝຸດ - ແລະຜູ້ອ່ານ, ເອກະສານ',' 020 ຫໍສະໝຸດ - ແລະຜູ້ອ່ານ, ເອກະສານ '),(6,'050','ວາລະສານທົ່ວໄປ - ລາຍປີ',' 050 ວາລະສານທົ່ວໄປ - ລາຍປີ '),(11,'100','ປັດຊະຍາ',' 100 ປັດຊະຍາ '),(31,'300','ວິທະຍາສາດສັງຄົມ',' 300 ວິທະຍາສາດສັງຄົມ '),(32,'310','ສະຖິຕິ',' 310 ສະຖິຕິ '),(33,'320','ການເມືອງ',' 320 ການເມືອງ '),(35,'340','ກົດໜາຍ',' 340 ກົດໜາຍ '),(36,'350','ການຄູ້ມຄອງ',' 350 ການຄູ້ມຄອງ '),(38,'370','ການສຶກສາ',' 370 ການສຶກສາ '),(41,'400','ພາສາ',' 400 ພາສາ '),(43,'420','ພາສາ ອັງກິດ',' 420 ພາສາ ອັງກິດ '),(44,'430','ພາສາ ເຢຍລະມັນ',' 430 ພາສາ ເຢຍລະມັນ '),(45,'440','ພາສາຝຣັ່ງ - (ວັດຈະນານຸກົມ, ໄວຍາກອນ)',' 440 ພາສາຝຣັ່ງ - (ວັດຈະນານຸກົມ, ໄວຍາກອນ) '),(46,'450','ພາສາ ອີ່ຕ່າລີ້',' 450 ພາສາ ອີ່ຕ່າລີ້ '),(47,'460','ພາສາ ແອັດສະປ່າຍ ',' 460 ພາສາ ແອັດສະປ່າຍ  '),(48,'470','ພາສາ ລ່າແຕ່ງ',' 470 ພາສາ ລ່າແຕ່ງ '),(49,'480','ພາສາ ກະເລັກ',' 480 ພາສາ ກະເລັກ '),(51,'500','ວິທະຍາສາດ',' 500 ວິທະຍາສາດ '),(52,'510','ເລກ',' 510 ເລກ '),(54,'530','ຟີຊິກ',' 530 ຟີຊິກ '),(61,'600','ເຕັກນິກ\r\n',' 600 ເຕັກນິກ\r\n '),(4,'030','Encyclopédies générales',' 030 encyclopedies generales '),(5,'040','X',' 040 x '),(7,'060','Organisations générales - congrès',' 060 organisations generales congres '),(8,'070','Presse Edition',' 070 presse edition '),(9,'080','Recueils - mélanges, discours',' 080 recueils melanges discours '),(10,'090','Manuscrits Livres rares',' 090 manuscrits livres rares '),(12,'110','Métaphysique',' 110 metaphysique '),(13,'120','Connaissance',' 120 connaissance '),(14,'130','Parapsychologie - astrologie, graphologie',' 130 parapsychologie astrologie graphologie '),(15,'140','Systèmes philosophiques',' 140 systemes philosophiques '),(16,'150','Psychologie',' 150 psychologie '),(17,'160','Logique',' 160 logique '),(18,'170','Morale - ethique',' 170 morale ethique '),(19,'180','Philosophes anciens - et orientaux',' 180 philosophes anciens orientaux '),(20,'190','Philosophes modernes - (XVIe S. à nos jours)',' 190 philosophes modernes xvie s nos jours '),(21,'200','Religion',' 200 religion '),(22,'210','Religion naturelle',' 210 religion naturelle '),(23,'220','Bible Evangiles',' 220 bible evangiles '),(24,'230','Théologie doctrinale chrétienne - (dogme)',' 230 theologie doctrinale chretienne dogme '),(25,'240','Théologie spirituelle - vie religieuse',' 240 theologie spirituelle vie religieuse '),(26,'250','Théologie pastorale',' 250 theologie pastorale '),(27,'260','L\'Eglise chrétienne et la société',' 260 eglise chretienne societe '),(28,'270','Histoire de l\'Eglise chrétienne',' 270 histoire eglise chretienne '),(29,'280','Autres confessions chrétiennes',' 280 autres confessions chretiennes '),(30,'290','Autres religions et mythologies',' 290 autres religions mythologies '),(34,'330','Economie - finances, production, consommation',' 330 economie finances production consommation '),(37,'360','Aide Assistance Secours',' 360 aide assistance secours '),(39,'380','Commerce Transports Communication',' 380 commerce transports communication '),(40,'390','Costumes et folklore',' 390 costumes folklore '),(42,'410','Linguistique',' 410 linguistique '),(50,'490','Autres langues - russe, arabe, …',' 490 autres langues russe arabe '),(53,'520','Astronomie',' 520 astronomie '),(55,'540','Chimie - minéralogie',' 540 chimie mineralogie '),(56,'550','Sciences de la Terre - géologie, météorologie',' 550 sciences terre geologie meteorologie '),(57,'560','Paléontologie - (les fossiles)',' 560 paleontologie fossiles '),(58,'570','Sciences de la vie - biologie, génétique',' 570 sciences vie biologie genetique '),(59,'580','Botanique - (les plantes)',' 580 botanique plantes '),(60,'590','Zoologie - (les animaux)',' 590 zoologie animaux '),(62,'610','Médecine - hygiène, santé',' 610 medecine hygiene sante '),(63,'620','Techniques industrielles - mécanique, électricité, radio, énergie…',' 620 techniques industrielles mecanique electricite radio energie '),(64,'630','Agriculture - forêt, élevage, pêche',' 630 agriculture foret elevage peche '),(65,'640','Arts ménagers - cuisine, coutûre, soins de beauté',' 640 arts menagers cuisine couture soins beaute '),(66,'650','Entreprise - travail de bureaux, vente, publicité',' 650 entreprise travail bureaux vente publicite '),(67,'660','Industries chimiques et alimentaires',' 660 industries chimiques alimentaires '),(68,'670','Fabrications industrielles - métallurgie, bois, textile',' 670 fabrications industrielles metallurgie bois textile '),(69,'680','Articles manufacturés',' 680 articles manufactures '),(70,'690','Bâtiment - construction',' 690 batiment construction '),(71,'700','Arts et loisirs',' 700 arts loisirs '),(72,'710','Urbanisme - art du paysage',' 710 urbanisme art paysage '),(73,'720','Architecture',' 720 architecture '),(74,'730','Sculpture',' 730 sculpture '),(75,'740','Dessin - arts décoratifs',' 740 dessin arts decoratifs '),(76,'750','Peinture',' 750 peinture '),(77,'760','Arts graphiques - graphisme',' 760 arts graphiques graphisme '),(78,'770','Photographie',' 770 photographie '),(79,'780','Musique',' 780 musique '),(80,'790','Loisirs - spectacles, jeux, sports',' 790 loisirs spectacles jeux sports '),(81,'800','Littérature',' 800 litterature '),(82,'810','Littérature américaine',' 810 litterature americaine '),(83,'820','Littérature anglaise',' 820 litterature anglaise '),(84,'830','Littérature allemande',' 830 litterature allemande '),(85,'840','Littérature française',' 840 litterature francaise '),(86,'850','Littérature italienne',' 850 litterature italienne '),(87,'860','Littérature espagnole et portugaise',' 860 litterature espagnole portugaise '),(88,'870','Littérature latine',' 870 litterature latine '),(89,'880','Littérature grecque',' 880 litterature grecque '),(90,'890','Autres littératures',' 890 autres litteratures '),(91,'900','Histoire géographie',' 900 histoire geographie '),(92,'910','Géographie - voyages',' 910 geographie voyages '),(93,'920','Biographies - vie d\'un personnage, généalogie',' 920 biographies vie personnage genealogie '),(94,'930','Histoire ancienne',' 930 histoire ancienne '),(95,'940','Histoire de l\'Europe',' 940 histoire europe '),(96,'950','Histoire de l\'Asie',' 950 histoire asie '),(97,'960','Histoire de l\'Afrique',' 960 histoire afrique '),(98,'970','Histoire de l\'Amérique du Nord',' 970 histoire amerique nord '),(99,'980','Histoire de l\'Amérique du Sud',' 980 histoire amerique sud '),(100,'990','Histoire de l\'Océanie',' 990 histoire oceanie ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `indexint` ENABLE KEYS */;

--
-- Table structure for table `lenders`
--

DROP TABLE IF EXISTS `lenders`;
CREATE TABLE `lenders` (
  `idlender` smallint(5) unsigned NOT NULL auto_increment,
  `lender_libelle` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`idlender`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lenders`
--


/*!40000 ALTER TABLE `lenders` DISABLE KEYS */;
LOCK TABLES `lenders` WRITE;
INSERT INTO `lenders` VALUES (1,'ເປັນຂອງຫ້ອງສະໝຸດ'),(2,'ເປັນຂອງຫ້ອງສະໝຸດທ້ອງຖິ່ນ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `lenders` ENABLE KEYS */;

--
-- Table structure for table `liens_actes`
--

DROP TABLE IF EXISTS `liens_actes`;
CREATE TABLE `liens_actes` (
  `num_acte` int(8) unsigned NOT NULL default '0',
  `num_acte_lie` int(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`num_acte`,`num_acte_lie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `liens_actes`
--


/*!40000 ALTER TABLE `liens_actes` DISABLE KEYS */;
LOCK TABLES `liens_actes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `liens_actes` ENABLE KEYS */;

--
-- Table structure for table `lignes_actes`
--

DROP TABLE IF EXISTS `lignes_actes`;
CREATE TABLE `lignes_actes` (
  `id_ligne` int(15) unsigned NOT NULL auto_increment,
  `type_ligne` int(3) unsigned NOT NULL default '0',
  `num_acte` int(8) unsigned NOT NULL default '0',
  `lig_ref` int(15) unsigned NOT NULL default '0',
  `num_acquisition` int(12) unsigned NOT NULL default '0',
  `num_rubrique` int(8) unsigned NOT NULL default '0',
  `num_produit` int(8) unsigned NOT NULL default '0',
  `num_type` int(8) unsigned NOT NULL default '0',
  `libelle` text NOT NULL,
  `code` varchar(255) NOT NULL default '',
  `prix` float(8,2) unsigned NOT NULL default '0.00',
  `tva` float(8,2) unsigned NOT NULL default '0.00',
  `nb` int(5) unsigned NOT NULL default '1',
  `date_ech` date NOT NULL default '0000-00-00',
  `date_cre` date NOT NULL default '0000-00-00',
  `statut` int(3) unsigned NOT NULL default '0',
  `remise` float(8,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id_ligne`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lignes_actes`
--


/*!40000 ALTER TABLE `lignes_actes` DISABLE KEYS */;
LOCK TABLES `lignes_actes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `lignes_actes` ENABLE KEYS */;

--
-- Table structure for table `noeuds`
--

DROP TABLE IF EXISTS `noeuds`;
CREATE TABLE `noeuds` (
  `id_noeud` int(9) unsigned NOT NULL auto_increment,
  `autorite` varchar(255) NOT NULL default '',
  `num_parent` int(9) unsigned NOT NULL default '0',
  `num_renvoi_voir` int(9) unsigned NOT NULL default '0',
  `visible` char(1) NOT NULL default '1',
  `num_thesaurus` int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_noeud`),
  KEY `num_parent` (`num_parent`),
  KEY `num_thesaurus` (`num_thesaurus`),
  KEY `autorite` (`autorite`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `noeuds`
--


/*!40000 ALTER TABLE `noeuds` DISABLE KEYS */;
LOCK TABLES `noeuds` WRITE;
INSERT INTO `noeuds` VALUES (1,'TOP',0,0,'0',1),(2484,'ORPHELINS',1,0,'0',1),(1378,'1377',1,0,'1',1),(1379,'1378',1,0,'1',1),(1380,'1379',1,0,'1',1),(1381,'1380',1,0,'1',1),(1382,'1381',1,0,'1',1),(1383,'1382',1,0,'1',1),(1384,'1383',1,0,'1',1),(1385,'1384',1,0,'1',1),(1386,'1385',1,0,'1',1),(1387,'1386',1,0,'1',1),(1388,'1387',1378,0,'1',1),(1389,'1388',1378,0,'1',1),(1390,'1389',1378,0,'1',1),(1391,'1390',1378,0,'1',1),(1392,'1391',1378,0,'1',1),(1393,'1392',1378,0,'1',1),(1394,'1393',1378,0,'1',1),(1395,'1394',1378,0,'1',1),(1396,'1395',1378,0,'1',1),(1397,'1396',1378,0,'1',1),(1398,'1397',1378,0,'1',1),(1399,'1398',1378,0,'1',1),(1400,'1399',1378,0,'1',1),(1401,'1400',1378,0,'1',1),(1402,'1401',1390,0,'1',1),(1403,'1402',1408,0,'1',1),(1404,'1403',1390,0,'1',1),(1405,'1404',1390,0,'1',1),(1406,'1405',1390,0,'1',1),(1407,'1406',1408,0,'1',1),(1408,'1407',1390,0,'1',1),(1409,'1408',1408,0,'1',1),(1410,'1409',1406,0,'1',1),(1411,'1410',1391,0,'1',1),(1412,'1411',1391,0,'1',1),(1413,'1412',1391,0,'1',1),(1414,'1413',1391,0,'1',1),(1415,'1414',1391,0,'1',1),(1416,'1415',1391,0,'1',1),(1417,'1416',1391,0,'1',1),(1418,'1417',1391,0,'1',1),(1419,'1418',1391,0,'1',1),(1420,'1419',1394,0,'1',1),(1421,'1420',2046,0,'1',1),(1422,'1421',2045,0,'1',1),(1423,'1422',2045,0,'1',1),(1424,'1423',2045,0,'1',1),(1425,'1424',2045,0,'1',1),(1426,'1425',2045,0,'1',1),(1427,'1426',2045,0,'1',1),(1428,'1427',2045,0,'1',1),(1429,'1428',2045,0,'1',1),(1430,'1429',2045,0,'1',1),(1431,'1430',1420,0,'1',1),(1432,'1431',1420,0,'1',1),(1433,'1432',1420,0,'1',1),(1434,'1433',1420,0,'1',1),(1435,'1434',1420,0,'1',1),(1436,'1435',1420,0,'1',1),(1437,'1436',1420,0,'1',1),(1438,'1437',1420,0,'1',1),(1439,'1438',1420,0,'1',1),(1440,'1439',1420,0,'1',1),(1441,'1440',1420,0,'1',1),(1442,'1441',2046,0,'1',1),(1443,'1442',1420,0,'1',1),(1444,'1443',1420,0,'1',1),(1445,'1444',2046,0,'1',1),(1446,'1445',1422,0,'1',1),(1447,'1446',1423,0,'1',1),(1448,'1447',1424,0,'1',1),(1449,'1448',1425,0,'1',1),(1450,'1449',1426,0,'1',1),(1451,'1450',1427,0,'1',1),(1452,'1451',1428,0,'1',1),(1453,'1452',1429,0,'1',1),(1454,'1453',1430,0,'1',1),(1455,'1454',2046,0,'1',1),(1456,'1455',1422,0,'1',1),(1457,'1456',1423,0,'1',1),(1458,'1457',1424,0,'1',1),(1459,'1458',1425,0,'1',1),(1460,'1459',1426,0,'1',1),(1461,'1460',1427,0,'1',1),(1462,'1461',1428,0,'1',1),(1463,'1462',1428,0,'1',1),(1464,'1463',1429,0,'1',1),(1465,'1464',1430,0,'1',1),(1466,'1465',2046,0,'1',1),(1467,'1466',1422,0,'1',1),(1468,'1467',1423,0,'1',1),(1469,'1468',1424,0,'1',1),(1470,'1469',1425,0,'1',1),(1471,'1470',1426,0,'1',1),(1472,'1471',1427,0,'1',1),(1473,'1472',1429,0,'1',1),(1474,'1473',1428,0,'1',1),(1475,'1474',1430,0,'1',1),(1476,'1475',1426,0,'1',1),(1477,'1476',1427,0,'1',1),(1478,'1477',1429,0,'1',1),(1479,'1478',1430,0,'1',1),(1480,'1479',1422,0,'1',1),(1481,'1480',1423,0,'1',1),(1482,'1481',1424,0,'1',1),(1483,'1482',1425,0,'1',1),(1484,'1483',2160,0,'1',1),(1485,'1484',2160,0,'1',1),(1486,'1485',2160,0,'1',1),(1487,'1486',2160,0,'1',1),(1488,'1487',2160,0,'1',1),(1489,'1488',2160,0,'1',1),(1490,'1489',2160,0,'1',1),(1491,'1490',1916,0,'1',1),(1492,'1491',1399,0,'1',1),(1493,'1492',1379,0,'1',1),(1494,'1493',1379,0,'1',1),(1495,'1494',1379,0,'1',1),(1496,'1495',1379,0,'1',1),(1497,'1496',1379,0,'1',1),(1498,'1497',1379,0,'1',1),(1499,'1498',1495,0,'1',1),(1500,'1499',1495,0,'1',1),(1501,'1500',1495,0,'1',1),(1502,'1501',1495,0,'1',1),(1503,'1502',1495,0,'1',1),(1504,'1503',1495,0,'1',1),(1505,'1504',1495,0,'1',1),(1506,'1505',1382,0,'1',1),(1507,'1506',1495,0,'1',1),(1508,'1507',1495,0,'1',1),(1509,'1508',1495,0,'1',1),(1510,'1509',1495,0,'1',1),(1511,'1510',1495,0,'1',1),(1512,'1511',1497,0,'1',1),(1513,'1512',1497,0,'1',1),(1514,'1513',1497,0,'1',1),(1515,'1514',1380,0,'1',1),(1516,'1515',1380,0,'1',1),(1517,'1516',1380,0,'1',1),(1518,'1517',1380,0,'1',1),(1519,'1518',1380,0,'1',1),(1520,'1519',1380,0,'1',1),(1521,'1520',1380,0,'1',1),(1522,'1521',1380,0,'1',1),(1523,'1522',1380,0,'1',1),(1524,'1523',1525,1641,'1',1),(1525,'1524',1515,0,'1',1),(1526,'1525',1515,0,'1',1),(1527,'1526',1526,0,'1',1),(1528,'1527',1526,0,'1',1),(1529,'1528',1526,0,'1',1),(1530,'1529',1526,0,'1',1),(1531,'1530',1515,0,'1',1),(1532,'1531',1515,0,'1',1),(1533,'1532',1515,0,'1',1),(1534,'1533',1515,0,'1',1),(1535,'1534',1515,0,'1',1),(1536,'1535',1516,0,'1',1),(1537,'1536',1516,0,'1',1),(1538,'1537',1516,0,'1',1),(1539,'1538',1516,0,'1',1),(1540,'1539',1516,0,'1',1),(1541,'1540',1516,0,'1',1),(1542,'1541',1516,0,'1',1),(1543,'1542',1516,0,'1',1),(1544,'1543',1516,0,'1',1),(1545,'1544',1517,0,'1',1),(1546,'1545',1517,0,'1',1),(1547,'1546',1523,0,'1',1),(1548,'1547',1517,0,'1',1),(1549,'1548',1517,0,'1',1),(1550,'1549',1551,0,'1',1),(1551,'1550',1517,0,'1',1),(1552,'1551',1517,0,'1',1),(1553,'1552',1517,0,'1',1),(1554,'1553',1518,0,'1',1),(1555,'1554',1518,0,'1',1),(1556,'1555',1518,0,'1',1),(1557,'1556',1518,0,'1',1),(1558,'1557',1518,0,'1',1),(1559,'1558',1519,0,'1',1),(1560,'1559',1519,0,'1',1),(1561,'1560',1519,0,'1',1),(1562,'1561',1519,0,'1',1),(1563,'1562',1519,0,'1',1),(1564,'1563',1519,0,'1',1),(1565,'1564',1519,0,'1',1),(1566,'1565',1519,0,'1',1),(1567,'1566',1555,0,'1',1),(1568,'1567',2167,0,'1',1),(1569,'1568',2167,0,'1',1),(1570,'1569',2167,0,'1',1),(1571,'1570',2167,0,'1',1),(1572,'1571',2167,0,'1',1),(1573,'1572',2167,0,'1',1),(1574,'1573',2168,0,'1',1),(1575,'1574',2168,0,'1',1),(1576,'1575',2168,0,'1',1),(1577,'1576',1520,0,'1',1),(1578,'1577',1520,0,'1',1),(1579,'1578',1520,0,'1',1),(1580,'1579',1520,0,'1',1),(1581,'1580',1520,0,'1',1),(1582,'1581',1521,0,'1',1),(1583,'1582',1521,0,'1',1),(1584,'1583',1521,0,'1',1),(1585,'1584',1521,0,'1',1),(1586,'1585',1521,0,'1',1),(1587,'1586',1521,0,'1',1),(1588,'1587',1521,0,'1',1),(1589,'1588',1521,0,'1',1),(1590,'1589',1521,0,'1',1),(1591,'1590',1521,0,'1',1),(1592,'1591',1522,0,'1',1),(1593,'1592',2166,0,'1',1),(1594,'1593',2166,0,'1',1),(1595,'1594',2166,0,'1',1),(1596,'1595',1522,0,'1',1),(1597,'1596',1522,0,'1',1),(1598,'1597',1522,0,'1',1),(1599,'1598',1522,0,'1',1),(1600,'1599',1522,0,'1',1),(1601,'1600',1522,0,'1',1),(1602,'1601',1522,0,'1',1),(1603,'1602',1523,0,'1',1),(1604,'1603',1523,0,'1',1),(1605,'1604',1523,0,'1',1),(1606,'1605',1523,0,'1',1),(1607,'1606',1523,0,'1',1),(1608,'1607',1523,0,'1',1),(1609,'1608',1523,0,'1',1),(1610,'1609',1523,0,'1',1),(1611,'1610',1523,0,'1',1),(1612,'1611',1381,0,'1',1),(1613,'1612',2022,0,'1',1),(1614,'1613',1381,0,'1',1),(1615,'1614',2022,0,'1',1),(1616,'1615',2022,0,'1',1),(1617,'1616',1381,0,'1',1),(1618,'1617',1381,0,'1',1),(1619,'1618',1381,0,'1',1),(1620,'1619',1381,0,'1',1),(1621,'1620',2022,0,'1',1),(1622,'1621',1620,0,'1',1),(1623,'1622',1620,0,'1',1),(1624,'1623',1620,0,'1',1),(1625,'1624',1620,0,'1',1),(1626,'1625',1620,0,'1',1),(1627,'1626',1620,0,'1',1),(1628,'1627',1620,0,'1',1),(1629,'1628',1621,0,'1',1),(1630,'1629',1621,0,'1',1),(1631,'1630',1621,0,'1',1),(1632,'1631',1621,0,'1',1),(1633,'1632',1621,0,'1',1),(1634,'1633',1621,0,'1',1),(1635,'1634',1621,0,'1',1),(1636,'1635',1621,0,'1',1),(1637,'1636',1621,0,'1',1),(1638,'1637',1621,0,'1',1),(1639,'1638',1621,0,'1',1),(1640,'1639',1639,0,'1',1),(1641,'1640',1644,0,'1',1),(1642,'1641',1639,0,'1',1),(1643,'1642',2141,0,'1',1),(1644,'1643',1639,0,'1',1),(1645,'1644',1639,0,'1',1),(1646,'1645',1382,0,'1',1),(1647,'1646',1382,0,'1',1),(1648,'1647',1382,0,'1',1),(1649,'1648',1382,0,'1',1),(1650,'1649',1382,0,'1',1),(1651,'1650',1382,0,'1',1),(1652,'1651',1382,0,'1',1),(1653,'1652',1382,0,'1',1),(1654,'1653',1382,0,'1',1),(1655,'1654',1382,0,'1',1),(1656,'1655',1382,0,'1',1),(1657,'1656',1382,0,'1',1),(1658,'1657',1647,0,'1',1),(1659,'1658',1647,0,'1',1),(1660,'1659',1647,0,'1',1),(1661,'1660',1647,0,'1',1),(1662,'1661',1647,0,'1',1),(1663,'1662',1651,0,'1',1),(1664,'1663',1651,0,'1',1),(1665,'1664',1651,0,'1',1),(1666,'1665',1651,0,'1',1),(1667,'1666',1651,0,'1',1),(1668,'1667',1651,0,'1',1),(1669,'1668',1651,0,'1',1),(1670,'1669',1651,0,'1',1),(1671,'1670',1651,0,'1',1),(1672,'1671',1651,0,'1',1),(1673,'1672',1651,0,'1',1),(1674,'1673',1651,0,'1',1),(1675,'1674',1654,0,'1',1),(1676,'1675',1654,0,'1',1),(1677,'1676',1654,0,'1',1),(1678,'1677',1654,0,'1',1),(1679,'1678',1654,0,'1',1),(1680,'1679',1654,0,'1',1),(1681,'1680',1684,0,'1',1),(1682,'1681',1383,0,'1',1),(1683,'1682',1383,0,'1',1),(1684,'1683',1383,0,'1',1),(1685,'1684',1683,0,'1',1),(1686,'1685',1383,0,'1',1),(1687,'1686',1383,0,'1',1),(1688,'1687',1684,0,'1',1),(1689,'1688',1684,0,'1',1),(1690,'1689',1383,0,'1',1),(1691,'1690',1684,0,'1',1),(1692,'1691',1683,0,'1',1),(1693,'1692',1383,0,'1',1),(1694,'1693',1383,0,'1',1),(1695,'1694',1385,0,'1',1),(1696,'1695',1383,0,'1',1),(1697,'1696',1383,0,'1',1),(1698,'1697',1684,0,'1',1),(1699,'1698',1684,0,'1',1),(1700,'1699',1383,0,'1',1),(1701,'1700',1684,0,'1',1),(1702,'1701',1682,0,'1',1),(1703,'1702',1682,0,'1',1),(1704,'1703',1682,0,'1',1),(1705,'1704',1682,0,'1',1),(1706,'1705',1682,0,'1',1),(1707,'1706',1687,0,'1',1),(1708,'1707',1687,0,'1',1),(1709,'1708',1687,0,'1',1),(1710,'1709',1687,0,'1',1),(1711,'1710',1687,0,'1',1),(1712,'1711',1687,0,'1',1),(1713,'1712',1687,0,'1',1),(1714,'1713',1683,0,'1',1),(1715,'1714',1696,0,'1',1),(1716,'1715',1696,0,'1',1),(1717,'1716',1696,0,'1',1),(1718,'1717',1696,0,'1',1),(1719,'1718',1696,0,'1',1),(1720,'1719',1696,0,'1',1),(1721,'1720',1696,0,'1',1),(1722,'1721',1696,0,'1',1),(1723,'1722',1384,0,'1',1),(1724,'1723',1384,0,'1',1),(1725,'1724',1384,0,'1',1),(1726,'1725',1384,0,'1',1),(1727,'1726',2203,0,'1',1),(1728,'1727',1384,0,'1',1),(1729,'1728',1384,0,'1',1),(1730,'1729',1384,0,'1',1),(1731,'1730',1384,0,'1',1),(1733,'1732',1384,0,'1',1),(1734,'1733',1917,0,'1',1),(1735,'1734',1734,0,'1',1),(1736,'1735',1734,0,'1',1),(1737,'1736',1734,0,'1',1),(1738,'1737',1734,0,'1',1),(1739,'1738',1734,0,'1',1),(1740,'1739',1734,0,'1',1),(1741,'1740',1734,0,'1',1),(1742,'1741',1734,0,'1',1),(1743,'1742',1734,0,'1',1),(1744,'1743',1734,0,'1',1),(1745,'1744',1915,0,'1',1),(1746,'1745',1734,0,'1',1),(1747,'1746',1734,0,'1',1),(1748,'1747',1734,0,'1',1),(1749,'1748',1734,0,'1',1),(1750,'1749',1734,0,'1',1),(1751,'1750',1734,0,'1',1),(1752,'1751',1734,0,'1',1),(1753,'1752',1734,0,'1',1),(1754,'1753',1734,0,'1',1),(1755,'1754',1734,0,'1',1),(1756,'1755',1734,0,'1',1),(1757,'1756',1734,0,'1',1),(1758,'1757',1385,0,'1',1),(1759,'1758',1385,0,'1',1),(1760,'1759',1385,0,'1',1),(1761,'1760',1385,0,'1',1),(1762,'1761',1385,0,'1',1),(1763,'1762',1385,0,'1',1),(1764,'1763',1385,0,'1',1),(1765,'1764',1385,0,'1',1),(1766,'1765',1385,0,'1',1),(1767,'1766',1385,0,'1',1),(1768,'1767',1385,0,'1',1),(1769,'1768',1385,0,'1',1),(1770,'1769',1385,0,'1',1),(1771,'1770',1385,0,'1',1),(1772,'1771',1385,0,'1',1),(1773,'1772',1765,0,'1',1),(1774,'1773',1765,0,'1',1),(1775,'1774',1765,0,'1',1),(1776,'1775',1765,0,'1',1),(1777,'1776',1765,0,'1',1),(1778,'1777',1386,0,'1',1),(1779,'1778',1386,0,'1',1),(1780,'1779',1386,0,'1',1),(1781,'1780',1386,0,'1',1),(1782,'1781',1386,0,'1',1),(1783,'1782',1386,0,'1',1),(1784,'1783',1386,0,'1',1),(1785,'1784',1386,0,'1',1),(1786,'1785',1386,0,'1',1),(1787,'1786',1386,0,'1',1),(1788,'1787',1387,0,'1',1),(1789,'1788',1387,0,'1',1),(1790,'1789',1387,0,'1',1),(1791,'1790',1387,0,'1',1),(1792,'1791',1387,0,'1',1),(1793,'1792',1387,0,'1',1),(1794,'1793',1387,0,'1',1),(1795,'1794',1387,0,'1',1),(1796,'1795',1387,0,'1',1),(1797,'1796',1788,0,'1',1),(1798,'1797',1788,0,'1',1),(1799,'1798',1788,0,'1',1),(1800,'1799',1788,0,'1',1),(1801,'1800',1788,0,'1',1),(1802,'1801',1788,0,'1',1),(1803,'1802',1788,0,'1',1),(1804,'1803',1789,0,'1',1),(1805,'1804',1789,0,'1',1),(1806,'1805',1789,0,'1',1),(1807,'1806',1789,0,'1',1),(1808,'1807',1789,0,'1',1),(1809,'1808',1789,0,'1',1),(1810,'1809',1789,0,'1',1),(1811,'1810',1790,0,'1',1),(1812,'1811',1790,0,'1',1),(1813,'1812',1790,0,'1',1),(1814,'1813',1790,0,'1',1),(1815,'1814',1790,0,'1',1),(1816,'1815',1790,0,'1',1),(1817,'1816',1790,0,'1',1),(1818,'1817',1790,0,'1',1),(1819,'1818',1791,0,'1',1),(1820,'1819',1791,0,'1',1),(1821,'1820',1791,0,'1',1),(1822,'1821',1791,0,'1',1),(1823,'1822',1791,0,'1',1),(1824,'1823',1791,0,'1',1),(1825,'1824',1791,0,'1',1),(1826,'1825',1791,0,'1',1),(1827,'1826',1791,0,'1',1),(1828,'1827',1791,0,'1',1),(1829,'1828',1791,0,'1',1),(1830,'1829',1791,0,'1',1),(1831,'1830',1791,0,'1',1),(1832,'1831',1792,0,'1',1),(1833,'1832',1792,0,'1',1),(1834,'1833',1792,0,'1',1),(1835,'1834',1792,0,'1',1),(1836,'1835',1792,0,'1',1),(1837,'1836',1792,0,'1',1),(1838,'1837',1793,0,'1',1),(1839,'1838',1793,0,'1',1),(1840,'1839',1793,0,'1',1),(1841,'1840',1793,0,'1',1),(1842,'1841',1793,0,'1',1),(1843,'1842',1793,0,'1',1),(1844,'1843',1794,0,'1',1),(1845,'1844',1794,0,'1',1),(1846,'1845',1794,0,'1',1),(1847,'1846',1794,0,'1',1),(1848,'1847',1797,0,'1',1),(1849,'1848',1797,0,'1',1),(1850,'1849',1797,0,'1',1),(1851,'1850',1797,0,'1',1),(1852,'1851',1798,0,'1',1),(1853,'1852',1798,0,'1',1),(1854,'1853',1798,0,'1',1),(1855,'1854',1798,0,'1',1),(1856,'1855',1798,0,'1',1),(1857,'1856',1798,0,'1',1),(1858,'1857',1798,0,'1',1),(1859,'1858',1798,0,'1',1),(1860,'1859',1798,0,'1',1),(1861,'1860',1798,0,'1',1),(1862,'1861',1798,0,'1',1),(1863,'1862',1798,0,'1',1),(1864,'1863',1798,0,'1',1),(1865,'1864',1798,0,'1',1),(1866,'1865',1799,0,'1',1),(1867,'1866',1799,0,'1',1),(1868,'1867',1799,0,'1',1),(1869,'1868',1799,0,'1',1),(1870,'1869',1799,0,'1',1),(1871,'1870',1800,0,'1',1),(1872,'1871',1800,0,'1',1),(1873,'1872',1800,0,'1',1),(1874,'1873',1800,0,'1',1),(1875,'1874',1800,0,'1',1),(1876,'1875',1800,0,'1',1),(1877,'1876',1800,0,'1',1),(1878,'1877',1800,0,'1',1),(1879,'1878',1801,0,'1',1),(1880,'1879',1801,0,'1',1),(1881,'1880',1801,0,'1',1),(1882,'1881',1801,0,'1',1),(1883,'1882',1801,0,'1',1),(1884,'1883',1801,0,'1',1),(1885,'1884',1801,0,'1',1),(1886,'1885',1802,0,'1',1),(1887,'1886',1802,0,'1',1),(1888,'1887',1802,0,'1',1),(1889,'1888',1802,0,'1',1),(1890,'1889',1802,0,'1',1),(1891,'1890',1802,0,'1',1),(1892,'1891',1802,0,'1',1),(1893,'1892',1802,0,'1',1),(1894,'1893',1802,0,'1',1),(1895,'1894',1802,0,'1',1),(1896,'1895',1803,0,'1',1),(1897,'1896',1803,0,'1',1),(1898,'1897',1803,0,'1',1),(1899,'1898',1803,0,'1',1),(1900,'1899',1803,0,'1',1),(1901,'1900',1803,0,'1',1),(1902,'1901',1818,0,'1',1),(1903,'1902',1818,0,'1',1),(1904,'1903',1818,0,'1',1),(1905,'1904',1818,0,'1',1),(1906,'1905',1818,0,'1',1),(1907,'1906',1818,0,'1',1),(1908,'1907',1818,0,'1',1),(1909,'1908',1818,0,'1',1),(1910,'1909',1832,0,'1',1),(1911,'1910',1832,0,'1',1),(1912,'1911',1832,0,'1',1),(1913,'1912',1832,0,'1',1),(1914,'1913',1832,0,'1',1),(1915,'1914',1833,0,'1',1),(1916,'1915',1833,0,'1',1),(1917,'1916',1833,0,'1',1),(1918,'1917',1833,0,'1',1),(1919,'1918',1833,0,'1',1),(1920,'1919',1833,0,'1',1),(1921,'1920',1833,0,'1',1),(1922,'1921',1834,0,'1',1),(1923,'1922',1834,0,'1',1),(1924,'1923',1834,0,'1',1),(1925,'1924',1834,0,'1',1),(1926,'1925',1834,0,'1',1),(1927,'1926',1835,0,'1',1),(1928,'1927',1835,0,'1',1),(1929,'1928',1835,0,'1',1),(1930,'1929',1835,0,'1',1),(1931,'1930',1835,0,'1',1),(1932,'1931',1835,0,'1',1),(1933,'1932',1835,0,'1',1),(1934,'1933',1835,0,'1',1),(1935,'1934',1836,0,'1',1),(1936,'1935',1836,0,'1',1),(1937,'1936',1836,0,'1',1),(1938,'1937',1836,0,'1',1),(1939,'1938',1837,0,'1',1),(1940,'1939',1837,0,'1',1),(1941,'1940',1837,0,'1',1),(1942,'1941',1837,0,'1',1),(1943,'1942',1837,0,'1',1),(1944,'1943',1837,0,'1',1),(1945,'1944',1837,0,'1',1),(1946,'1945',1837,0,'1',1),(1947,'1946',1837,0,'1',1),(1948,'1947',1838,0,'1',1),(1949,'1948',1838,0,'1',1),(1950,'1949',1838,0,'1',1),(1951,'1950',1838,0,'1',1),(1952,'1951',1948,0,'1',1),(1953,'1952',1948,0,'1',1),(1954,'1953',1948,0,'1',1),(1955,'1954',1948,0,'1',1),(1956,'1955',1948,0,'1',1),(1957,'1956',1948,0,'1',1),(1958,'1957',1948,0,'1',1),(1959,'1958',1951,0,'1',1),(1960,'1959',1951,0,'1',1),(1961,'1960',1951,0,'1',1),(1962,'1961',1951,0,'1',1),(1963,'1962',1951,0,'1',1),(1964,'1963',1951,0,'1',1),(1965,'1964',1951,0,'1',1),(1966,'1965',1839,0,'1',1),(1967,'1966',1839,0,'1',1),(1968,'1967',1839,0,'1',1),(1969,'1968',1835,0,'1',1),(1970,'1969',1840,0,'1',1),(1971,'1970',1840,0,'1',1),(1972,'1971',1840,0,'1',1),(1973,'1972',1840,0,'1',1),(1974,'1973',1840,0,'1',1),(1975,'1974',1840,0,'1',1),(1976,'1975',1840,0,'1',1),(1977,'1976',1840,0,'1',1),(1978,'1977',1841,0,'1',1),(1979,'1978',1841,0,'1',1),(1980,'1979',1841,0,'1',1),(1981,'1980',1841,0,'1',1),(1982,'1981',1841,0,'1',1),(1983,'1982',1841,0,'1',1),(1984,'1983',1842,0,'1',1),(1985,'1984',1842,0,'1',1),(1986,'1985',1842,0,'1',1),(1987,'1986',1842,0,'1',1),(1988,'1987',1842,0,'1',1),(1989,'1988',1842,0,'1',1),(1990,'1989',1843,0,'1',1),(1991,'1990',1843,0,'1',1),(1992,'1991',1843,0,'1',1),(1993,'1992',1843,0,'1',1),(1994,'1993',1843,0,'1',1),(1995,'1994',1843,0,'1',1),(1996,'1995',1843,0,'1',1),(1997,'1996',1843,0,'1',1),(1998,'1997',1843,0,'1',1),(1999,'1998',1843,0,'1',1),(2000,'1999',1845,0,'1',1),(2001,'2000',1845,0,'1',1),(2002,'2001',1845,0,'1',1),(2003,'2002',1845,0,'1',1),(2004,'2003',1845,0,'1',1),(2005,'2004',1845,0,'1',1),(2006,'2005',1846,0,'1',1),(2007,'2006',1846,0,'1',1),(2008,'2007',1846,0,'1',1),(2009,'2008',1846,0,'1',1),(2010,'2009',1846,0,'1',1),(2011,'2010',1847,0,'1',1),(2012,'2011',1847,0,'1',1),(2013,'2012',1847,0,'1',1),(2014,'2013',1847,0,'1',1),(2015,'2014',1847,0,'1',1),(2016,'2015',1847,0,'1',1),(2017,'2016',1847,0,'1',1),(2018,'2017',1847,0,'1',1),(2019,'2018',1847,0,'1',1),(2020,'2019',1847,0,'1',1),(2021,'2020',1847,0,'1',1),(2022,'2021',1381,0,'1',1),(2023,'2022',1698,0,'1',1),(2024,'2023',1787,0,'1',1),(2025,'2024',1698,0,'1',1),(2026,'2025',1787,0,'1',1),(2027,'2026',1698,0,'1',1),(2028,'2027',1787,0,'1',1),(2029,'2028',1503,0,'1',1),(2030,'2029',2032,0,'1',1),(2031,'2030',2032,0,'1',1),(2032,'2031',1653,0,'1',1),(2034,'2033',1554,0,'1',1),(2035,'2034',2046,0,'1',1),(2036,'2035',2046,0,'1',1),(2037,'2036',1787,0,'1',1),(2039,'2038',1937,0,'1',1),(2040,'2039',1731,0,'1',1),(2043,'2042',1691,0,'1',1),(2044,'2043',1424,0,'1',1),(2045,'2044',1394,0,'1',1),(2046,'2045',1394,0,'1',1),(2047,'2046',2046,0,'1',1),(2048,'2047',2046,0,'1',1),(2049,'2048',2046,0,'1',1),(2050,'2049',2046,0,'1',1),(2051,'2050',2046,0,'1',1),(2052,'2051',2046,0,'1',1),(2053,'2052',2046,0,'1',1),(2054,'2053',2049,0,'1',1),(2055,'2054',1969,0,'1',1),(2056,'2055',1912,0,'1',1),(2057,'2056',1593,0,'1',1),(2058,'2057',1593,0,'1',1),(2059,'2058',1593,0,'1',1),(2060,'2059',1593,0,'1',1),(2061,'2060',1593,0,'1',1),(2062,'2061',1593,0,'1',1),(2063,'2062',1593,0,'1',1),(2064,'2063',1593,0,'1',1),(2065,'2064',1593,0,'1',1),(2066,'2065',1982,0,'1',1),(2067,'2066',1830,0,'1',1),(2068,'2067',1455,0,'1',1),(2069,'2068',1936,0,'1',1),(2070,'2069',1945,0,'1',1),(2071,'2070',1554,0,'1',1),(2072,'2071',1554,0,'1',1),(2074,'2073',2051,0,'1',1),(2075,'2074',1652,0,'1',1),(2076,'2075',2125,0,'1',1),(2077,'2076',1984,0,'1',1),(2078,'2077',1442,0,'1',1),(2079,'2078',2082,0,'1',1),(2080,'2079',2082,0,'1',1),(2081,'2080',2082,0,'1',1),(2082,'2081',1550,0,'1',1),(2083,'2082',1954,0,'1',1),(2084,'2083',2035,0,'1',1),(2085,'2084',1708,0,'1',1),(2086,'2085',1503,0,'1',1),(2087,'2086',2086,0,'1',1),(2088,'2087',1808,0,'1',1),(2089,'2088',2036,0,'1',1),(2090,'2089',2089,0,'1',1),(2092,'2091',1984,0,'1',1),(2093,'2092',1944,0,'1',1),(2094,'2093',2125,0,'1',1),(2095,'2094',1425,0,'1',1),(2096,'2095',1426,0,'1',1),(2097,'2096',1427,0,'1',1),(2098,'2097',1937,0,'1',1),(2099,'2098',1428,0,'1',1),(2100,'2099',1915,0,'1',1),(2101,'2100',1599,0,'1',1),(2102,'2101',1599,0,'1',1),(2103,'2102',1599,0,'1',1),(2104,'2103',1599,0,'1',1),(2105,'2104',1599,0,'1',1),(2106,'2105',1599,0,'1',1),(2107,'2106',1599,0,'1',1),(2108,'2107',2036,0,'1',1),(2109,'2108',1606,0,'1',1),(2110,'2109',1445,0,'1',1),(2111,'2110',2049,0,'1',1),(2112,'2111',1420,0,'1',1),(2113,'2112',2051,0,'1',1),(2114,'2113',1911,0,'1',1),(2115,'2114',1914,0,'1',1),(2116,'2115',1777,0,'1',1),(2117,'2116',1810,0,'1',1),(2118,'2117',1981,0,'1',1),(2119,'2118',1922,0,'1',1),(2120,'2119',1383,0,'1',1),(2121,'2120',2120,0,'1',1),(2122,'2121',1944,0,'1',1),(2123,'2122',1934,0,'1',1),(2124,'2123',2048,0,'1',1),(2125,'2124',1780,0,'1',1),(2126,'2125',2128,0,'1',1),(2127,'2126',2128,0,'1',1),(2128,'2127',2125,0,'1',1),(2129,'2128',1758,0,'1',1),(2130,'2129',2129,0,'1',1),(2131,'2130',2129,0,'1',1),(2132,'2131',1694,0,'1',1),(2135,'2134',1378,0,'1',1),(2136,'2135',2135,0,'1',1),(2137,'2136',1608,0,'1',1),(2138,'2137',1975,0,'1',1),(2139,'2138',2140,0,'1',1),(2140,'2139',1639,0,'1',1),(2141,'2140',1639,0,'1',1),(2142,'2141',1397,0,'1',1),(2143,'2142',1917,0,'1',1),(2144,'2143',1917,0,'1',1),(2145,'2144',1913,0,'1',1),(2146,'2145',2158,0,'1',1),(2147,'2146',1394,0,'1',1),(2148,'2147',2158,0,'1',1),(2150,'2149',2157,0,'1',1),(2151,'2150',1919,0,'1',1),(2152,'2151',2147,0,'1',1),(2153,'2152',2147,0,'1',1),(2154,'2153',2147,0,'1',1),(2155,'2154',2147,0,'1',1),(2156,'2155',2147,0,'1',1),(2157,'2156',2147,0,'1',1),(2158,'2157',2147,0,'1',1),(2159,'2158',1399,0,'1',1),(2160,'2159',1399,0,'1',1),(2161,'2160',1399,0,'1',1),(2162,'2161',1526,0,'1',1),(2163,'2162',1526,0,'1',1),(2164,'2163',1515,0,'1',1),(2165,'2164',1515,0,'1',1),(2166,'2165',1522,0,'1',1),(2167,'2166',1520,0,'1',1),(2168,'2167',1520,0,'1',1),(2169,'2168',1520,0,'1',1),(2170,'2169',1378,0,'1',1),(2171,'2170',1522,0,'1',1),(2172,'2171',2170,0,'1',1),(2173,'2172',1981,0,'1',1),(2174,'2173',1993,0,'1',1),(2175,'2174',1401,0,'1',1),(2177,'2176',1389,0,'1',1),(2179,'2178',2359,0,'1',1),(2180,'2179',1618,0,'1',1),(2181,'2180',2135,0,'1',1),(2182,'2181',1405,0,'1',1),(2183,'2182',1621,0,'1',1),(2184,'2183',1405,0,'1',1),(2185,'2184',1400,0,'1',1),(2186,'2185',1400,0,'1',1),(2188,'2187',1825,0,'1',1),(2189,'2188',1805,0,'1',1),(2190,'2189',1983,0,'1',1),(2191,'2190',1612,0,'1',1),(2192,'2191',2185,0,'1',1),(2193,'2192',2047,0,'1',1),(2194,'2193',2049,0,'1',1),(2196,'2195',1733,0,'1',1),(2197,'2196',2196,0,'1',1),(2198,'2197',2197,0,'1',1),(2200,'2199',1378,0,'1',1),(2201,'2200',2200,0,'1',1),(2203,'2202',1386,0,'1',1),(2204,'2203',1999,0,'1',1),(2205,'2204',1780,0,'1',1),(2206,'2205',2205,0,'1',1),(2207,'2206',2205,0,'1',1),(2208,'2207',1954,0,'1',1),(2209,'2208',1982,0,'1',1),(2210,'2209',1998,0,'1',1),(2211,'2210',1844,0,'1',1),(2212,'2211',2205,0,'1',1),(2213,'2212',1993,0,'1',1),(2214,'2213',1613,0,'1',1),(2215,'2214',2214,0,'1',1),(2216,'2215',2214,0,'1',1),(2217,'2216',1635,0,'1',1),(2218,'2217',1638,0,'1',1),(2219,'2218',1621,0,'1',1),(2220,'2219',2219,0,'1',1),(2221,'2220',2219,0,'1',1),(2222,'2221',1615,0,'1',1),(2223,'2222',1631,0,'1',1),(2224,'2223',2125,0,'1',1),(2225,'2224',2125,0,'1',1),(2226,'2225',1408,0,'1',1),(2227,'2226',1764,0,'1',1),(2228,'2227',2368,0,'1',1),(2229,'2228',1489,0,'1',1),(2231,'2230',1486,0,'1',1),(2232,'2231',1401,0,'1',1),(2233,'2232',1490,0,'1',1),(2235,'2234',1396,0,'1',1),(2236,'2235',1613,0,'1',1),(2237,'2236',1405,0,'1',1),(2238,'2237',1850,0,'1',1),(2239,'2238',1405,0,'1',1),(2240,'2239',1402,0,'1',1),(2242,'2241',1490,0,'1',1),(2244,'2243',1848,0,'1',1),(2245,'2244',2359,0,'1',1),(2246,'2245',1401,0,'1',1),(2248,'2247',1490,0,'1',1),(2250,'2249',1490,0,'1',1),(2252,'2251',1525,0,'1',1),(2253,'2252',2252,0,'1',1),(2254,'2253',1545,0,'1',1),(2255,'2254',1551,0,'1',1),(2256,'2255',1605,0,'1',1),(2257,'2256',1611,0,'1',1),(2258,'2257',1611,0,'1',1),(2259,'2258',1571,0,'1',1),(2260,'2259',1405,0,'1',1),(2261,'2260',1683,0,'1',1),(2262,'2261',1766,0,'1',1),(2264,'2263',1731,0,'1',1),(2265,'2264',1551,0,'1',1),(2266,'2265',2265,0,'1',1),(2267,'2266',2171,0,'1',1),(2268,'2267',1525,1643,'1',1),(2269,'2268',1525,0,'1',1),(2270,'2269',1525,0,'1',1),(2271,'2270',1551,0,'1',1),(2272,'2271',2271,0,'1',1),(2273,'2272',2275,0,'1',1),(2274,'2273',2275,0,'1',1),(2275,'2274',2277,0,'1',1),(2276,'2275',1778,1643,'1',1),(2277,'2276',1551,0,'1',1),(2278,'2277',1549,0,'1',1),(2279,'2278',1731,0,'1',1),(2280,'2279',1546,0,'1',1),(2281,'2280',1546,0,'1',1),(2282,'2281',1546,0,'1',1),(2283,'2282',1571,0,'1',1),(2284,'2283',1555,0,'1',1),(2285,'2284',1555,0,'1',1),(2286,'2285',1555,0,'1',1),(2287,'2286',1718,0,'1',1),(2288,'2287',1982,0,'1',1),(2289,'2288',2203,0,'1',1),(2290,'2289',2172,0,'1',1),(2291,'2290',1725,0,'1',1),(2292,'2291',1850,0,'1',1),(2293,'2292',1805,0,'1',1),(2294,'2293',1759,0,'1',1),(2295,'2294',1474,0,'1',1),(2297,'2296',1648,0,'1',1),(2298,'2297',1656,0,'1',1),(2299,'2298',1731,0,'1',1),(2300,'2299',1471,0,'1',1),(2302,'2301',1471,0,'1',1),(2303,'2302',1764,0,'1',1),(2304,'2303',1916,0,'1',1),(2306,'2305',1396,0,'1',1),(2307,'2306',1917,0,'1',1),(2308,'2307',1780,0,'1',1),(2309,'2308',2308,0,'1',1),(2310,'2309',1650,0,'1',1),(2311,'2310',1774,0,'1',1),(2312,'2311',1805,0,'1',1),(2313,'2312',2205,0,'1',1),(2314,'2313',1711,0,'1',1),(2315,'2314',1711,0,'1',1),(2316,'2315',1711,0,'1',1),(2317,'2316',1711,0,'1',1),(2318,'2317',1621,0,'1',1),(2319,'2318',1854,0,'1',1),(2320,'2319',1658,0,'1',1),(2321,'2320',2320,0,'1',1),(2322,'2321',1658,0,'1',1),(2324,'2323',1879,0,'1',1),(2325,'2324',1894,0,'1',1),(2326,'2325',1785,0,'1',1),(2327,'2326',1764,0,'1',1),(2328,'2327',1496,0,'1',1),(2329,'2328',1496,0,'1',1),(2330,'2329',2032,0,'1',1),(2332,'2331',1513,0,'1',1),(2335,'2334',1655,0,'1',1),(2336,'2335',1770,0,'1',1),(2337,'2336',2336,0,'1',1),(2338,'2337',2185,0,'1',1),(2339,'2338',2185,0,'1',1),(2340,'2339',2185,0,'1',1),(2341,'2340',1657,0,'1',1),(2342,'2341',1396,0,'1',1),(2343,'2342',1513,0,'1',1),(2345,'2344',1495,0,'1',1),(2346,'2345',1388,0,'1',1),(2347,'2346',1656,0,'1',1),(2348,'2347',1686,0,'1',1),(2349,'2348',1648,0,'1',1),(2350,'2349',2308,0,'1',1),(2352,'2351',1703,0,'1',1),(2353,'2352',1490,0,'1',1),(2354,'2353',1780,0,'1',1),(2356,'2355',2359,0,'1',1),(2358,'2357',1490,0,'1',1),(2359,'2358',1490,0,'1',1),(2361,'2360',2125,0,'1',1),(2362,'2361',1490,0,'1',1),(2364,'2363',1490,0,'1',1),(2366,'2365',1490,0,'1',1),(2368,'2367',1489,0,'1',1),(2369,'2368',2308,0,'1',1),(2371,'2370',1771,0,'1',1),(2372,'2371',1662,0,'1',1),(2373,'2372',1648,0,'1',1),(2374,'2373',1656,0,'1',1),(2375,'2374',1688,0,'1',1),(2376,'2375',1650,0,'1',1),(2377,'2376',2125,0,'1',1),(2378,'2377',1758,0,'1',1),(2380,'2379',1633,0,'1',1),(2381,'2380',1400,0,'1',1),(2382,'2381',2185,0,'1',1),(2383,'2382',1613,0,'1',1),(2384,'2383',2205,0,'1',1),(2385,'2384',2384,0,'1',1),(2386,'2385',2205,0,'1',1),(2387,'2386',2386,0,'1',1),(2388,'2387',2386,0,'1',1),(2389,'2388',1904,0,'1',1),(2390,'2389',1810,0,'1',1),(2391,'2390',2205,0,'1',1),(2392,'2391',1899,0,'1',1),(2393,'2392',1861,0,'1',1),(2394,'2393',1652,0,'1',1),(2395,'2394',1652,0,'1',1),(2396,'2395',2203,0,'1',1),(2397,'2396',1780,0,'1',1),(2398,'2397',1976,0,'1',1),(2399,'2398',1693,0,'1',1),(2400,'2399',2399,0,'1',1),(2402,'2401',1721,0,'1',1),(2403,'2402',1787,0,'1',1),(2404,'2403',2120,0,'1',1),(2405,'2404',1731,0,'1',1),(2406,'2405',1656,0,'1',1),(2407,'2406',1729,0,'1',1),(2408,'2407',2205,0,'1',1),(2409,'2408',1935,0,'1',1),(2410,'2409',1392,0,'1',1),(2411,'2410',2205,0,'1',1),(2412,'2411',2411,0,'1',1),(2413,'2412',1780,0,'1',1),(2414,'2413',2125,0,'1',1),(2416,'2415',2373,0,'1',1),(2418,'2417',2373,0,'1',1),(2420,'2419',1616,0,'1',1),(2422,'2421',1780,0,'1',1),(2423,'2422',2125,0,'1',1),(2424,'2423',2125,0,'1',1),(2425,'2424',2185,0,'1',1),(2426,'2425',2185,0,'1',1),(2427,'2426',2185,0,'1',1),(2428,'2427',1657,0,'1',1),(2429,'2428',2428,0,'1',1),(2430,'2429',2429,0,'1',1),(2431,'2430',1466,0,'1',1),(2432,'2431',1765,0,'1',1),(2438,'2437',1923,0,'1',1),(2439,'2438',1925,0,'1',1),(2440,'2439',1921,0,'1',1),(2441,'2440',1903,0,'1',1),(2442,'2441',1466,0,'1',1),(2443,'2442',1819,0,'1',1),(2444,'2443',1466,0,'1',1),(2445,'2444',1831,0,'1',1),(2446,'2445',1620,0,'1',1),(2447,'2446',1616,0,'1',1),(2448,'2447',2447,0,'1',1),(2449,'2448',1616,0,'1',1),(2451,'2450',2022,0,'1',1),(2452,'2451',2451,0,'1',1),(2454,'2453',2451,0,'1',1),(2456,'2455',1394,0,'1',1),(2457,'2456',1618,0,'1',1),(2458,'2457',2457,0,'1',1),(2460,'2459',1618,0,'1',1),(2461,'2460',1612,0,'1',1),(2462,'2461',2036,0,'1',1),(2463,'2462',1455,0,'1',1),(2464,'2463',1828,0,'1',1),(2465,'2464',1765,0,'1',1),(2466,'2465',2465,0,'1',1),(2467,'2466',2465,0,'1',1),(2469,'2468',2457,0,'1',1),(2471,'2470',2472,0,'1',1),(2472,'2471',1618,0,'1',1),(2473,'2472',2457,0,'1',1),(2475,'2474',1618,0,'1',1),(2476,'2475',1389,0,'1',1),(2477,'2476',1716,0,'1',1),(2478,'2477',2086,0,'1',1),(2479,'2478',1420,0,'1',1),(2480,'2479',1726,0,'1',1),(2481,'2480',1726,2480,'1',1),(2482,'2481',1726,0,'1',1),(2483,'2482',1726,2482,'1',1),(2485,'2484',1764,0,'1',1),(2486,'2485',1764,2485,'1',1),(2487,'2486',1764,0,'1',1),(2488,'2487',1379,0,'1',1),(2489,'2488',2488,0,'1',1),(2490,'2489',2489,0,'1',1),(2491,'2490',1686,0,'1',1),(2492,'2491',1684,0,'1',1),(2493,'2492',1729,0,'1',1),(2494,'2493',2488,0,'1',1),(2495,'2494',1729,0,'1',1),(2496,'2495',1686,0,'1',1),(2497,'2496',1686,0,'1',1),(2498,'2497',1684,0,'1',1),(2499,'2498',2498,0,'1',1),(2500,'2499',2498,0,'1',1),(2501,'2500',2492,2502,'1',1),(2502,'2501',1787,0,'1',1),(2503,'2502',1787,0,'1',1),(2504,'2503',2502,0,'1',1),(2505,'2504',2503,0,'1',1),(2506,'2505',2503,0,'1',1),(2507,'2506',2505,0,'1',1),(2508,'2507',1765,0,'1',1),(2509,'2508',2508,0,'1',1),(2510,'2509',1767,0,'1',1),(2511,'2510',2484,1670,'1',1),(2512,'NONCLASSES',1,0,'0',1),(2513,'',1,0,'1',1),(2514,'',1,0,'1',1),(2515,'',1,0,'1',1),(2516,'',1,0,'1',1),(2517,'',1,0,'1',1),(2518,'',1,0,'1',1),(2519,'',1,0,'1',1),(2520,'',1,0,'1',1),(2521,'',1,0,'1',1),(2522,'',1,0,'1',1),(2523,'',2522,0,'1',1),(2524,'',2520,0,'1',1),(2525,'',2520,0,'1',1),(2526,'',1,0,'1',1),(2527,'',2526,0,'1',1),(2528,'',2526,0,'1',1),(2529,'',2526,0,'1',1),(2530,'',2526,0,'1',1),(2531,'',2521,0,'1',1),(2532,'',2521,0,'1',1),(2533,'',1,0,'1',1),(2534,'',2533,0,'1',1),(2535,'',2533,0,'1',1),(2536,'',1,0,'1',1),(2537,'',2536,0,'1',1),(2538,'',2536,0,'1',1),(2539,'',2522,0,'1',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `noeuds` ENABLE KEYS */;

--
-- Table structure for table `notice_statut`
--

DROP TABLE IF EXISTS `notice_statut`;
CREATE TABLE `notice_statut` (
  `id_notice_statut` smallint(5) unsigned NOT NULL auto_increment,
  `gestion_libelle` varchar(255) default NULL,
  `opac_libelle` varchar(255) default NULL,
  `notice_visible_opac` tinyint(1) NOT NULL default '1',
  `notice_visible_gestion` tinyint(1) NOT NULL default '1',
  `expl_visible_opac` tinyint(1) NOT NULL default '1',
  `class_html` varchar(255) NOT NULL default '',
  `notice_visible_opac_abon` tinyint(1) NOT NULL default '0',
  `expl_visible_opac_abon` int(10) unsigned NOT NULL default '0',
  `explnum_visible_opac` int(1) unsigned NOT NULL default '1',
  `explnum_visible_opac_abon` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_notice_statut`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notice_statut`
--


/*!40000 ALTER TABLE `notice_statut` DISABLE KEYS */;
LOCK TABLES `notice_statut` WRITE;
INSERT INTO `notice_statut` VALUES (1,'ບໍ່ເຈາະຈົງສະຖານະພາບ','',1,1,1,'statutnot1',0,0,1,0),(2,'ຫ້າມໃຫ້ຢືມ','',0,1,1,'statutnot2',0,0,1,0),(3,'ສັ່ງເຂົ້າຢູ່','',1,1,1,'statutnot4',0,0,1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `notice_statut` ENABLE KEYS */;

--
-- Table structure for table `notices`
--

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `notice_id` mediumint(8) unsigned NOT NULL auto_increment,
  `typdoc` char(2) NOT NULL default 'a',
  `tit1` tinytext NOT NULL,
  `tit2` tinytext NOT NULL,
  `tit3` tinytext NOT NULL,
  `tit4` tinytext NOT NULL,
  `tparent_id` mediumint(8) unsigned NOT NULL default '0',
  `tnvol` varchar(16) default '',
  `ed1_id` mediumint(8) unsigned NOT NULL default '0',
  `ed2_id` mediumint(8) unsigned NOT NULL default '0',
  `coll_id` mediumint(8) unsigned NOT NULL default '0',
  `subcoll_id` mediumint(8) unsigned NOT NULL default '0',
  `year` varchar(16) default '',
  `nocoll` varchar(16) default '',
  `mention_edition` varchar(255) NOT NULL default '',
  `code` varchar(16) NOT NULL default '',
  `npages` varchar(54) NOT NULL default '',
  `ill` varchar(54) NOT NULL default '',
  `size` varchar(54) NOT NULL default '',
  `accomp` varchar(54) NOT NULL default '',
  `n_gen` text NOT NULL,
  `n_contenu` text NOT NULL,
  `n_resume` text NOT NULL,
  `lien` tinytext NOT NULL,
  `eformat` varchar(255) NOT NULL default '',
  `index_l` text NOT NULL,
  `indexint` int(8) unsigned NOT NULL default '0',
  `index_serie` tinytext,
  `index_matieres` text NOT NULL,
  `niveau_biblio` char(1) NOT NULL default 'm',
  `niveau_hierar` char(1) NOT NULL default '0',
  `origine_catalogage` int(8) unsigned NOT NULL default '1',
  `prix` varchar(255) NOT NULL default '',
  `index_n_gen` text,
  `index_n_contenu` text,
  `index_n_resume` text,
  `index_sew` text,
  `index_wew` text,
  `statut` int(5) NOT NULL default '1',
  `commentaire_gestion` text NOT NULL,
  `create_date` datetime NOT NULL default '2005-01-01 00:00:00',
  `update_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `notice_parent` int(9) unsigned NOT NULL default '0',
  `relation_type` char(1) NOT NULL default 'a',
  PRIMARY KEY  (`notice_id`),
  KEY `typdoc` (`typdoc`),
  KEY `tparent_id` (`tparent_id`),
  KEY `ed1_id` (`ed1_id`),
  KEY `ed2_id` (`ed2_id`),
  KEY `coll_id` (`coll_id`),
  KEY `subcoll_id` (`subcoll_id`),
  KEY `cb` (`code`),
  KEY `indexint` (`indexint`),
  KEY `notice_parent` (`notice_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices`
--


/*!40000 ALTER TABLE `notices` DISABLE KEYS */;
LOCK TABLES `notices` WRITE;
INSERT INTO `notices` VALUES (1,'a','ຊີວິດ ແລະ ຜົນງານຂອງພຣະມະຫາເຖຣະ5 ອົງ','','','',0,'',8,0,0,0,'2001','','1','','52 ໜ້າ','','','','','','','','','',6,'  ','  ','m','0',1,'7000 ກີບ','  ','  ','  ',' 5 ',' ຊີວິດ ແລະ ຜົນງານຂອງພຣະມະຫາເຖຣະ5 ອົງ   ',1,'','2006-10-13 15:15:24','2006-10-13 15:15:24',0,'a'),(2,'a','ພົງສາວະດານລາວ ເຖິງ 1946','','','',0,'',10,0,0,0,'2001','','1','','83 ໝ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'9600ກີບ','  ','  ','  ',' 1946 ',' ພົງສາວະດານລາວ ເຖິງ 1946   ',1,'','2006-10-13 15:28:56','2006-10-13 15:28:56',0,'a'),(3,'a','ເມື່ອຂ້ອຍເປິດສະໝຸດບັນທຶກ','','','',0,'',9,0,0,0,'2002','','1','','277 ໝ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'96000ກີບ','  ','  ','  ',' ເມື່ອຂ້ອຍເປິດສະໝຸດບັນທຶກ ',' ເມື່ອຂ້ອຍເປິດສະໝຸດບັນທຶກ   ',1,'','2006-10-13 15:32:56','2006-11-09 13:40:19',0,'a'),(4,'a','ຄອງແສນແສບຢ່າຊໍ້າຮອຍ','','','',0,'',6,0,0,0,'2000','','1','','53ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'82000 ກີບ','  ','  ','  ','  ',' ຄອງແສນແສບຢ່າຊໍ້າຮອຍ   ',1,'','2006-10-13 15:47:40','2006-10-13 15:47:40',0,'a'),(5,'a','ວິລະກຳເຈົ້າອານຸ','','','',0,'',6,0,0,0,'','','','','900ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'170000ກີບ','  ','  ','  ','  ',' ວິລະກຳເຈົ້າອານຸ   ',1,'','2006-10-13 15:52:00','2006-10-13 15:52:00',0,'a'),(6,'a','ກາບເມືອງພວນ','','','',0,'',13,0,0,0,'','','','','51ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'13000ກີບ','  ','  ','  ','  ',' ກາບເມືອງພວນ   ',1,'','2006-10-13 15:54:24','2006-10-13 15:54:24',0,'a'),(7,'a','ສະກຸນຕົ້ນດອກເຜິ້ງຂອງປະເທດໄທ,ລາວ','','','',0,'',17,0,0,0,'20004','','1','','450ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'7500 ກີບ','  ','  ','  ','  ',' ສະກຸນຕົ້ນດອກເຜິ້ງຂອງປະເທດໄທ,ລາວ   ',1,'','2006-10-13 15:57:46','2006-10-13 15:57:46',0,'a'),(8,'a','ທ້າວສຸຣະນາລີ ບາງທັດສະນະຂອງຄົນໄທ','','','',0,'',16,0,0,0,'','','','','68ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'5000 ກີບ','  ','  ','  ','  ',' ທ້າວສຸຣະນາລີ ບາງທັດສະນະຂອງຄົນໄທ   ',1,'','2006-10-13 15:59:50','2006-10-13 15:59:50',0,'a'),(9,'a','ປະຫວັດສາດລາວ 1946','','','',0,'',14,0,0,0,'','','','','852ໜ້າ','ມີພາບປະກອບ','','','','','','','','',0,'  ','  ','m','0',1,'200000 ກີບ','  ','  ','  ',' 1946 ',' ປະຫວັດສາດລາວ 1946   ',1,'','2006-10-13 16:02:02','2006-10-13 16:02:02',0,'a'),(10,'a','ການປຽບທຽບຜົນສົມທາງດ້ານຄະນິດສາດ','','','',0,'',20,0,0,0,'','','','','65ໜ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'20000 ກີບ','  ','  ','  ','  ',' ການປຽບທຽບຜົນສົມທາງດ້ານຄະນິດສາດ   ',1,'','2006-10-13 16:06:11','2006-10-13 16:06:11',0,'a'),(11,'a','ກົດໝາຍປ່າໄມ້','','','',0,'',8,0,0,0,'','','','','156ໜ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'700000ກີບ','  ','  ','  ','  ',' ກົດໝາຍປ່າໄມ້   ',1,'','2006-10-13 16:09:57','2006-10-13 16:09:57',0,'a'),(12,'a','ຮິດຄອງປະເພນີລາວ','','','',0,'',2,0,0,0,'','','','','67ໜ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'5800ກີບ','  ','  ','  ','  ',' ຮິດຄອງປະເພນີລາວ   ',1,'','2006-10-13 16:12:44','2006-10-13 16:12:44',0,'a'),(13,'a','ແນວທາງການດຳເນີນງານສຳລັບຄະນະກຳມະການ','','','',0,'',19,0,0,0,'','','','','96ໜ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'8000 ກີບ','  ','  ','  ','  ',' ແນວທາງການດຳເນີນງານສຳລັບຄະນະກຳມະການ   ',1,'','2006-10-13 16:14:28','2006-10-13 16:14:28',0,'a'),(14,'a','ຄົນຄວ້າວິທະຍາສາດທາງດ້ານວິຊາການແພດ','','','',0,'',18,0,0,0,'','','','','785ໜ້າ','','','','','','','','','',0,'  ','  ','m','0',1,'78000ກີບ','  ','  ','  ','  ',' ຄົນຄວ້າວິທະຍາສາດທາງດ້ານວິຊາການແພດ   ',1,'','2006-10-13 16:18:02','2006-10-13 16:18:02',0,'a'),(15,'a','ຮິດຄອງປະເພນີລາວ 2','','','',0,'',20,0,0,0,'','','','','35ໜ້າ','','','','','','ຮິດຄອງປະເພນີລາວ ','','','',0,'  ','  ','m','0',1,'34000ກີບ','  ','  ','  ',' 2 ',' ຮິດຄອງປະເພນີລາວ 2   ',1,'','2006-10-13 16:20:06','2006-10-13 16:20:06',0,'a'),(16,'a','ຕຳລາຢາພືນເມືອງ','','','',0,'',8,0,0,0,'2000','','6','','125ໜ້າ','','','','ຕຳລາຢາພືນເມືອງ ທີ່ມີຄຸນປະໂຫຍດທາງການແພດ','','','','','',0,'  ','  ','m','0',1,'12500ກີບ','  ','  ','  ','  ',' ຕຳລາຢາພືນເມືອງ   ',1,'','2006-10-13 16:21:42','2006-10-13 16:22:48',0,'a'),(17,'a','ວິທີຮັກສາຄວາມງາມ','','','',0,'',16,0,0,0,'','','','','','64ໜ້າ','','','ການຮັກສາຄວາມງາມ','','','','','',0,'  ','  ','m','0',1,'73000ກີບ','  ','  ','  ','  ',' ວິທີຮັກສາຄວາມງາມ   ',1,'','2006-10-13 16:25:15','2006-10-13 16:25:15',0,'a'),(18,'a','ຊີວິດ ແລະ ຜົນງານ','','','',0,'',6,0,0,0,'','','','','','','','','','','','','','',0,NULL,'','s','1',1,'',NULL,NULL,NULL,'  ','ຊີວິດ ແລະ ຜົນງານ   ',1,'','2006-10-13 16:27:45','2006-10-13 16:27:45',0,'a'),(19,'a','ຄູ່ມືສຳລັບຄູ່ສອນ','','','',0,'',2,0,0,0,'','','','','','','','','','','','','','',0,NULL,'','s','1',1,'',NULL,NULL,NULL,'  ','ຄູ່ມືສຳລັບຄູ່ສອນ   ',1,'','2006-10-13 16:31:07','2006-10-13 16:31:07',0,'a'),(20,'a','ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ','','','',0,'',16,0,0,0,'','','','','','','','','','','','','','',0,NULL,'','s','1',1,'',NULL,NULL,NULL,' ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ    ','ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ   ',1,'','2006-10-13 16:34:39','2006-10-14 16:36:47',0,'a'),(21,'a','ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ','','','',0,'',0,0,0,0,'','','','','','','','','','','','','','',35,'','  ','a','2',1,'','  ','  ','  ','  ','ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ   ',1,'','2006-10-13 16:37:54','2006-10-13 16:37:54',0,'a'),(22,'a','ພູມປັນຍາບູຮານລາວ','','','',0,'',12,0,0,0,'','','','','','','','','','','','','','',33,NULL,'','s','1',1,'',NULL,NULL,NULL,'  ','ພູມປັນຍາບູຮານລາວ   ',1,'','2006-10-13 16:39:48','2006-10-13 16:39:48',0,'a'),(23,'a','ແຄນ ແລະ ສຽງແຄນ','','','',0,'',13,0,0,0,'','','','','','','','','','','','','','',0,NULL,'','s','1',1,'',NULL,NULL,NULL,'  ','ແຄນ ແລະ ສຽງແຄນ   ',1,'','2006-10-13 16:41:12','2006-10-13 16:41:12',0,'a'),(24,'a','ບົດລາຍງານສະພາບແວດລ້ອມ ສປປ ລາວ','','','',0,'',15,0,0,0,'','','','','','','','','','','','','','',0,NULL,'','s','1',1,'',NULL,NULL,NULL,'  ','ບົດລາຍງານສະພາບແວດລ້ອມ ສປປ ລາວ   ',1,'','2006-10-13 16:44:23','2006-10-13 16:44:23',0,'a'),(25,'a','ປື້ມທົ່ວໄປ','','','',0,'',0,0,0,0,'','','','','','','','','','','','','','',0,'  ','  ','m','0',1,'','  ','  ','  ','  ປື້ມທົ່ວໄປ    ',' ປື້ມທົ່ວໄປ   ',1,'','2006-10-14 09:09:03','2006-10-16 07:23:21',0,'a'),(27,'a','ບົດສະເໜີກ່ຽວກັບວິທະຍາສາດສິ່ງແວດລ້ອມ','','','',0,'',12,0,0,0,'','','','','121ໜ້າ','ມີພາບປະກອບ','','','','','','','','',2,'  ','  ','m','0',1,'12500ກີບ','  ','  ','  ','  ບົດສະເໜີກ່ຽວກັບວິທະຍາສາດສິ່ງແວດລ້ອມ    ',' ບົດສະເໜີກ່ຽວກັບວິທະຍາສາດສິ່ງແວດລ້ອມ   ',1,'','2006-10-27 15:39:29','2006-10-27 15:39:29',0,'a'),(28,'a','ການ','','','',0,'',0,0,0,0,'','','','','','','','','','','','','','',0,'  ','  ','m','0',1,'','  ','  ','  ','  ການ    ',' ການ   ',1,'','2006-11-03 18:59:28','2006-11-03 18:59:28',0,'a');
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices` ENABLE KEYS */;

--
-- Table structure for table `notices_categories`
--

DROP TABLE IF EXISTS `notices_categories`;
CREATE TABLE `notices_categories` (
  `notcateg_notice` int(9) unsigned NOT NULL default '0',
  `num_noeud` int(9) unsigned NOT NULL default '0',
  `num_vedette` int(3) unsigned NOT NULL default '0',
  `ordre_vedette` int(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`notcateg_notice`,`num_noeud`,`num_vedette`),
  KEY `num_noeud` (`num_noeud`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_categories`
--


/*!40000 ALTER TABLE `notices_categories` DISABLE KEYS */;
LOCK TABLES `notices_categories` WRITE;
INSERT INTO `notices_categories` VALUES (1,2520,0,1),(2,2533,0,1),(3,2045,0,1),(4,2533,0,1),(5,2533,0,1),(6,2520,0,1),(7,2522,0,1),(8,2520,0,1),(9,2533,0,1),(10,2520,0,1),(11,2522,0,1),(12,2521,0,1),(14,2526,0,1),(15,2520,0,1),(18,2520,0,1),(19,2520,0,1),(19,2524,0,1),(20,2529,0,1),(21,2534,0,1),(22,2533,0,1),(23,2521,0,1),(24,2539,0,1),(1,2112,0,1),(2,2045,0,1),(3,2520,0,1),(4,1436,0,1),(5,1936,0,1),(6,2045,0,1),(6,2279,0,1),(7,1445,0,1),(8,1414,0,1),(9,1414,0,1),(10,1414,0,1),(11,1391,0,1),(12,1391,0,1),(13,1599,0,1),(14,1655,0,1),(15,2214,0,1),(16,1884,0,1),(17,1748,0,1),(18,1828,0,1),(19,1423,0,1),(19,1447,0,1),(24,1406,0,1),(25,1648,0,1),(25,1830,0,1),(25,2297,0,1),(26,1844,0,1),(29,1899,0,1),(30,1545,0,1),(31,1410,0,1),(32,1748,0,1),(33,1976,0,1),(35,1976,0,1),(36,1976,0,1),(37,1976,0,1),(38,1976,0,1),(39,1721,0,1),(39,1976,0,1),(41,1545,0,1),(41,1748,0,1),(42,1748,0,1),(44,1525,0,1),(47,1525,0,1),(47,1639,0,1),(48,1401,0,1),(49,1740,0,1),(50,1596,0,1),(51,2125,0,1),(53,2110,0,1),(54,1748,0,1),(58,2514,0,1),(27,2520,0,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_categories` ENABLE KEYS */;

--
-- Table structure for table `notices_custom`
--

DROP TABLE IF EXISTS `notices_custom`;
CREATE TABLE `notices_custom` (
  `idchamp` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `titre` varchar(255) default NULL,
  `type` varchar(10) NOT NULL default 'text',
  `datatype` varchar(10) NOT NULL default '',
  `options` text,
  `multiple` int(11) NOT NULL default '0',
  `obligatoire` int(11) NOT NULL default '0',
  `ordre` int(11) default NULL,
  PRIMARY KEY  (`idchamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_custom`
--


/*!40000 ALTER TABLE `notices_custom` DISABLE KEYS */;
LOCK TABLES `notices_custom` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_custom` ENABLE KEYS */;

--
-- Table structure for table `notices_custom_lists`
--

DROP TABLE IF EXISTS `notices_custom_lists`;
CREATE TABLE `notices_custom_lists` (
  `notices_custom_champ` int(10) unsigned NOT NULL default '0',
  `notices_custom_list_value` varchar(255) default NULL,
  `notices_custom_list_lib` varchar(255) default NULL,
  `ordre` int(11) default NULL,
  KEY `notices_custom_champ` (`notices_custom_champ`),
  KEY `noti_champ_list_value` (`notices_custom_champ`,`notices_custom_list_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_custom_lists`
--


/*!40000 ALTER TABLE `notices_custom_lists` DISABLE KEYS */;
LOCK TABLES `notices_custom_lists` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_custom_lists` ENABLE KEYS */;

--
-- Table structure for table `notices_custom_values`
--

DROP TABLE IF EXISTS `notices_custom_values`;
CREATE TABLE `notices_custom_values` (
  `notices_custom_champ` int(10) unsigned NOT NULL default '0',
  `notices_custom_origine` int(10) unsigned NOT NULL default '0',
  `notices_custom_small_text` varchar(255) default NULL,
  `notices_custom_text` text,
  `notices_custom_integer` int(11) default NULL,
  `notices_custom_date` date default NULL,
  `notices_custom_float` float default NULL,
  KEY `notices_custom_champ` (`notices_custom_champ`),
  KEY `notices_custom_origine` (`notices_custom_origine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_custom_values`
--


/*!40000 ALTER TABLE `notices_custom_values` DISABLE KEYS */;
LOCK TABLES `notices_custom_values` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_custom_values` ENABLE KEYS */;

--
-- Table structure for table `notices_global_index`
--

DROP TABLE IF EXISTS `notices_global_index`;
CREATE TABLE `notices_global_index` (
  `num_notice` mediumint(8) NOT NULL default '0',
  `no_index` mediumint(8) NOT NULL default '0',
  `infos_global` text NOT NULL,
  `index_infos_global` text NOT NULL,
  PRIMARY KEY  (`num_notice`,`no_index`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_global_index`
--


/*!40000 ALTER TABLE `notices_global_index` DISABLE KEYS */;
LOCK TABLES `notices_global_index` WRITE;
INSERT INTO `notices_global_index` VALUES (1,1,'   ຊີວິດ ແລະ ຜົນງານຂອງພຣະມະຫາເຖຣະ5 ອົງ        ຄະນະອັກສອນສາດ ມ/ຊ  ວັນນະຄະດີ 050 ວາລະສານທົ່ວໄປ - ລາຍປີ ນະຄອນຫລວງ ','     5               ຄະນະອັກສອນສາດ ມ/ຊ      050 ວາລະສານທົ່ວໄປ - ລາຍປີ   ນະຄອນຫລວງ  '),(2,1,'   ພົງສາວະດານລາວ ເຖິງ 1946        ສີລາ ວິລະວົງ  ປະຫວັດສາດ ໂຮງພິມມັນທາຕຸລາດ ','     1946               ສີລາ ວິລະວົງ      ໂຮງພິມມັນທາຕຸລາດ  '),(3,1,'   ເມື່ອຂ້ອຍເປິດສະໝຸດບັນທຶກ        ດຳດວນ ພົມດວງສີ  ວັນນະຄະດີ ໂຮງພິມແຫ່ງລັດ ','     ເມື່ອຂ້ອຍເປິດສະໝຸດບັນທຶກ               ດຳດວນ ພົມດວງສີ      ໂຮງພິມແຫ່ງລັດ  '),(4,1,'   ຄອງແສນແສບຢ່າຊໍ້າຮອຍ        ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ  ປະຫວັດສາດ ສະຖາບັນ ','                    ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ      ສະຖາບັນ  '),(5,1,'   ວິລະກຳເຈົ້າອານຸ        ສຸເນດ ໂພທິສານ  ປະຫວັດສາດ ສະຖາບັນ ','                    ສຸເນດ ໂພທິສານ      ສະຖາບັນ  '),(6,1,'   ກາບເມືອງພວນ        ບົວໄຂ ເພັງພະຈັນ  ວັນນະຄະດີ ສີສະຫວາດການພິມ ','                    ບົວໄຂ ເພັງພະຈັນ      ສີສະຫວາດການພິມ  '),(7,1,'   ສະກຸນຕົ້ນດອກເຜິ້ງຂອງປະເທດໄທ,ລາວ        ບຸນມີ ເທບສີເມືອງ  ທຳມະຊາດ ກຸງເທບ ','                    ບຸນມີ ເທບສີເມືອງ      ກຸງເທບ  '),(8,1,'   ທ້າວສຸຣະນາລີ ບາງທັດສະນະຂອງຄົນໄທ        ຄຳຜາຍ ບຸບຜາ  ວັນນະຄະດີ ປາກປາສັກການພິມ ','                    ຄຳຜາຍ ບຸບຜາ      ປາກປາສັກການພິມ  '),(9,1,'   ປະຫວັດສາດລາວ 1946        ສຸຈິດ ວົງເທບ  ປະຫວັດສາດ ອົງການອະນາໄມໂລກ ','     1946               ສຸຈິດ ວົງເທບ      ອົງການອະນາໄມໂລກ  '),(10,1,'   ການປຽບທຽບຜົນສົມທາງດ້ານຄະນິດສາດ        ບຸນສີ ບູລົມ  ວັນນະຄະດີ ສຳນັກພິມແລະຈຳໜ່າຍປືມ ','                    ບຸນສີ ບູລົມ      ສຳນັກພິມແລະຈຳໜ່າຍປືມ  '),(11,1,'   ກົດໝາຍປ່າໄມ້        ກົມປ່າໄມ້  ທຳມະຊາດ ນະຄອນຫລວງ ','                    ກົມປ່າໄມ້      ນະຄອນຫລວງ  '),(12,1,'   ຮິດຄອງປະເພນີລາວ        ສຸເນດ ໂພທິສານ  ກົມການເມືອງ ແລະ ການປົກຄອງ  ໂຄຈອນ ແກ້ວມະນີວົງ  ວິທະຍາສາດ ໂຮງພິມສຶກສາ ','                    ສຸເນດ ໂພທິສານ   ກົມການເມືອງ ແລະ ການປົກຄອງ   ໂຄຈອນ ແກ້ວມະນີວົງ      ໂຮງພິມສຶກສາ  '),(13,1,'   ແນວທາງການດຳເນີນງານສຳລັບຄະນະກຳມະການ        ອົງການອະນາໄມໂລກ  ສະພານທອງການພິມ ','                    ອົງການອະນາໄມໂລກ   ສະພານທອງການພິມ  '),(14,1,'   ຄົນຄວ້າວິທະຍາສາດທາງດ້ານວິຊາການແພດ        ຄຳຜາຍ ບຸບຜາ  ກົດໜາຍ ຂອນແກ່ນ ','                    ຄຳຜາຍ ບຸບຜາ      ຂອນແກ່ນ  '),(15,1,'   ຮິດຄອງປະເພນີລາວ 2    ຮິດຄອງປະເພນີລາວ     ຄະນະອັກສອນສາດ ມ/ຊ  ວັນນະຄະດີ ສຳນັກພິມແລະຈຳໜ່າຍປືມ ','     2               ຄະນະອັກສອນສາດ ມ/ຊ      ສຳນັກພິມແລະຈຳໜ່າຍປືມ  '),(16,1,'   ຕຳລາຢາພືນເມືອງ     ຕຳລາຢາພືນເມືອງ ທີ່ມີຄຸນປະໂຫຍດທາງການແພດ   ບຸນສີ ບູລົມ  ນະຄອນຫລວງ ','                    ບຸນສີ ບູລົມ   ນະຄອນຫລວງ  '),(17,1,'   ວິທີຮັກສາຄວາມງາມ     ການຮັກສາຄວາມງາມ   ບົວໄຂ ເພັງພະຈັນ  ປາກປາສັກການພິມ ','                    ບົວໄຂ ເພັງພະຈັນ   ປາກປາສັກການພິມ  '),(18,1,'  ຊີວິດ ແລະ ຜົນງານ        ສີລາ ວິລະວົງ  ວັນນະຄະດີ ສະຖາບັນ ','          ສີລາ ວິລະວົງ      ສະຖາບັນ  '),(19,1,'  ຄູ່ມືສຳລັບຄູ່ສອນ        ມູນນິທິຊາຊາກາວາ ເພື່ອສັນຕິພາບ  ວັນນະຄະດີ ວັນນະຄະດີລາວ ໂຮງພິມສຶກສາ ','          ມູນນິທິຊາຊາກາວາ ເພື່ອສັນຕິພາບ         ໂຮງພິມສຶກສາ  '),(20,1,'  ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ        ດຳດວນ ພົມດວງສີ  ກົດໜາຍ ລາວ ປາກປາສັກການພິມ ','   ເອກະສານເພີ່ມທະວີຄວາມສາມັກຄີ          ດຳດວນ ພົມດວງສີ      ປາກປາສັກການພິມ  '),(22,1,'  ພູມປັນຍາບູຮານລາວ        ຄະນະຈັດຕັງສູນກາງພັກ  ປະຫວັດສາດ 320 ການເມືອງ ການປົກຄອງ ','          ຄະນະຈັດຕັງສູນກາງພັກ      320 ການເມືອງ   ການປົກຄອງ  '),(23,1,'  ແຄນ ແລະ ສຽງແຄນ        ທອງມາລີ ສຸລາດ  ວິທະຍາສາດ ສີສະຫວາດການພິມ ','          ທອງມາລີ ສຸລາດ      ສີສະຫວາດການພິມ  '),(26,1,'   dfhsdfh        ','      dfhsdfh                 '),(25,1,'   ປື້ມທົ່ວໄປ        ','      ປື້ມທົ່ວໄປ                 '),(24,1,'  ບົດລາຍງານສະພາບແວດລ້ອມ ສປປ ລາວ        ກົມປ່າໄມ້  ປ່າໄມ້ ມູນນິທິຊາຊາກາວາ ','          ກົມປ່າໄມ້      ມູນນິທິຊາຊາກາວາ  '),(29,1,'   Bagnes � Madagascar         G�o   Un nouveau monde : la Terre Madagascar 910 G�ographie - voyages ','  jojo   bagnes madagascar               geo nouveau monde terre   madagascar   910 geographie voyages  '),(30,1,'   Tatars de Crim�e         G�o   Un nouveau monde : la Terre Voyage 910 G�ographie - voyages ','  jojo   tatars crimee               geo nouveau monde terre   voyage   910 geographie voyages  '),(31,1,'   Marigot africain         G�o   Un nouveau monde : la Terre Afrique ','  jojo   marigot africain               geo nouveau monde terre   afrique  '),(32,1,'   Chateaux de la Loire (2)         G�o   Un nouveau monde : la Terre Pays de la Loire 910 G�ographie - voyages ','  jojo   chateaux loire 2               geo nouveau monde terre   pays loire   910 geographie voyages  '),(33,1,'   Paysages afghans         G�o   Un nouveau monde : la Terre Afghanistan 910 G�ographie - voyages ','  jojo   paysages afghans               geo nouveau monde terre   afghanistan   910 geographie voyages  '),(35,1,'   Peuples d\'Afghanistan         G�o   Un nouveau monde : la Terre Afghanistan 910 G�ographie - voyages ','  jojo   peuples afghanistan               geo nouveau monde terre   afghanistan   910 geographie voyages  '),(36,1,'   Tribus Pachtounes         G�o   Un nouveau monde : la Terre Afghanistan 910 G�ographie - voyages ','  jojo   tribus pachtounes               geo nouveau monde terre   afghanistan   910 geographie voyages  '),(37,1,'   femmes afghanes         G�o   Un nouveau monde : la Terre Afghanistan ','  jojo   femmes afghanes               geo nouveau monde terre   afghanistan  '),(38,1,'   Histoire de l\'Afghanistan         G�o   Un nouveau monde : la Terre Afghanistan 910 G�ographie - voyages ','  jojo   histoire afghanistan               geo nouveau monde terre   afghanistan   910 geographie voyages  '),(39,1,'   Islam afghan         G�o   Un nouveau monde : la Terre Islam Afghanistan ','  jojo   islam afghan               geo nouveau monde terre   islam   afghanistan  '),(40,1,'   Famille Allix         G�o   Un nouveau monde : la Terre ','  jojo   famille allix               geo nouveau monde terre  '),(41,1,'   Chateaux de la Loire (1)       chateau loire chenonceau chambord cheverny  G�o   Un nouveau monde : la Terre Voyage Pays de la Loire 910 G�ographie - voyages ','  jojo   chateaux loire 1            chateau loire chenonceau chambord cheverny   geo nouveau monde terre   voyage   pays loire   910 geographie voyages  '),(42,1,'   Charte du XIIIe si�cle, par laquelle Guillaume de Rezay de la paroisse de Ceaux (Maine et Loire) vend � Messire de Vern�e, chevalier, sept sous et six deniers de rente.   Acte pass� en la cour d\'Angers le jeudi avant la Saint Urbain l\'an mille deux cent quatre vingt dix neuf.  excellent �tat de conservation date en vieux style (V.ST.) - M. DU POUGET, archiviste-pal�ographe de l\'Indre, a bien voulu attirer mon attention sur le fait que cette charte �tait dat�e du joedi devant la Saint Alban (Saint Aubin d\'Angers, qui se f�te le 1er mars - P�ques tombant en 1299 le 19 avril, il y a effectivement bien lieu de consid�rer que cette charte est du 25 f�vrier 1300, nouveau style (N.ST.) charte rente archive Ceaux paroisse cens Angers Maine-et-Loire Rezay Guillaume de Pays de la Loire 940 Histoire de l\'Europe ','  jojo   charte xiiie siecle par laquelle guillaume rezay paroisse ceaux maine loire vend messire vernee chevalier sept sous six deniers rente acte passe cour angers jeudi avant saint urbain an mille deux cent quatre vingt dix neuf      excellent etat conservation   date vieux style v st m pouget archiviste paleographe indre bien voulu attirer mon attention sur fait que cette charte etait datee joedi devant saint alban saint aubin angers qui se fete 1er mars paques tombant 1299 19 avril il y effectivement bien lieu considerer que cette charte est 25 fevrier 1300 nouveau style n st   charte rente archive ceaux paroisse cens angers maine loire   rezay guillaume   pays loire   940 histoire europe  '),(44,1,'   Bruit de cochon     Bruitage courts. Bonne qualit� d\'enregistrement.  cochon porc truie verrat porcelet goret cochette suid�s artiodactyles groin sound-fishing.net  Mammif�res 590 Zoologie - (les animaux) sound-fishing.net ','  jojo   bruit cochon      bruitage courts bonne qualite enregistrement      cochon porc truie verrat porcelet goret cochette suides artiodactyles groin   sound fishing net   mammiferes   590 zoologie animaux   sound fishing net  '),(48,1,'   Canne   � pommeau en forme de cochon  canne en bois pr�cieux, bichromie, pommeau sculpt� et peint  canne cochon pied porc pommeau argent ouvrage pr�cieux sculpture\r\n Favulier Jacques Sculpture 680 Articles manufactur�s ','  jojo   canne pommeau forme cochon      canne bois precieux bichromie pommeau sculpte peint      canne cochon pied porc pommeau argent ouvrage precieux sculpture   favulier jacques   sculpture   680 articles manufactures  '),(46,1,'   L\'adagio d\'Albinoni    Canon de Pachelbel, J�sus que ma joie demeure de J.S. Bach, Andante pour mandoline de Vivaldi, Menuet de Mozart, Menuet de Boccherini  On conna�t mal ce compositeur v�nitien exactement contemporain de Vivaldi, mais une seule �uvre, pourtant, a assur� sa notori�t�, l�Adagio pour cordes, extrait en fait du Concerto en r� majeur. Cette longue cantil�ne plaintive a servi au film Quatre mariages et un enterrement.  Marion Alain Bride Philip 780 Musique Forlane ','     adagio albinoni   canon pachelbel jesus que ma joie demeure j s bach andante pour mandoline vivaldi menuet mozart menuet boccherini      on connait mal ce compositeur venitien exactement contemporain vivaldi mais seule uvre pourtant assure sa notoriete adagio pour cordes extrait fait concerto re majeur cette longue cantilene plaintive servi film quatre mariages enterrement      marion alain   bride philip   780 musique   forlane  '),(47,1,'   Couverture du magazine rustica   Ce que doit �tre le porc parfait \" Ce que doit �tre le porc parfait \" mentionn� en couverture    Mammif�res Mammif�res 590 Zoologie - (les animaux) Rustica ','  jojo   couverture magazine rustica ce que doit etre porc parfait   \" ce que doit etre porc parfait \" mentionne couverture            mammiferes   mammiferes   590 zoologie animaux   rustica  '),(49,1,'   Tours. N�65. Flle 78     Carte de Cassini Cote : Ge FF 18595 (65) BNF Richelieu Cartes et Plans Reprod. Sc 96/614\r\n. - Carte lev�e entre 1760 et 1762 par Bottin, Langelay, v�rifi�e en 1763 et 1764 par La Briffe Ponsan. Lettre par Chambon. 78e feuille publi�e. Tours Indre-et-Loire France Cassini de Thury C�sar-Fran�ois Centre 910 G�ographie - voyages D�p�t de la Guerre ','  jojo   tours n 65 flle 78      carte cassini   cote ge ff 18595 65 bnf richelieu cartes plans reprod sc 96 614 carte levee entre 1760 1762 par bottin langelay verifiee 1763 1764 par briffe ponsan lettre par chambon 78e feuille publiee   tours indre loire france   cassini thury cesar francois   centre   910 geographie voyages   depot guerre  '),(50,1,'   Le Cochon d\'Hollywood       cochon porc hollywood acteur studio cin�ma Fraxler Hans Livre Collection Folio benjamin Gallimard ','  jojo   cochon hollywood            cochon porc hollywood acteur studio cinema   fraxler hans   livre   collection folio benjamin   gallimard  '),(51,1,'   Le Porc et les produits de la charcuterie, hygi�ne, inspection, r�glementation, par Th. Bourrier,..      Exemples illustr�s, gravures repr�sentant une ferme en Indre-et-Loire Indre-et-Loire ferme porc �levage verrat truie porcelet cochelle Bourrier Th�odore Aliments 640 Arts m�nagers - cuisine, cout�re, soins de beaut� Asselin et Houzeau ','  jojo   porc produits charcuterie hygiene inspection reglementation par th bourrier         exemples illustres gravures representant ferme indre loire   indre loire ferme porc elevage verrat truie porcelet cochelle   bourrier theodore   aliments   640 arts menagers cuisine couture soins beaute   asselin houzeau  '),(53,1,'   Nimitz   roman     Langlois-Chassaignon Claudie Robinson Patrick Roman et nouvelle 800 Litt�rature A. Michel ','  jojo   nimitz roman               langlois chassaignon claudie   robinson patrick   roman nouvelle   800 litterature   michel  '),(54,1,'   �tudes arch�ologiques dans la Loire-Inf�rieure, ...   Arrondissements de Nantes et de Paimboeuf    Loire-Atlantique Orieux Eug�ne Pays de la Loire 910 G�ographie - voyages impr. de Mme Vve Mellinet ','  jojo   etudes archeologiques dans loire inferieure arrondissements nantes paimboeuf            loire atlantique   orieux eugene   pays loire   910 geographie voyages   impr mme vve mellinet  '),(57,1,'   Germinal        Pichard Georges Zola �mile BD adultes M�dia 1000 ','  jojo   germinal               pichard georges   zola emile   bd adultes   media 1000  '),(58,1,'   ພົງສາວະດານລາວ ເຖິງ 1946     ກ່ຽວກັບປະຫວັດສາດ, ໆລໆ   ສິນລະປະ ແລະວັດທະນະທຳ ໂຮງພິມມັນທາຕຸລາດ ','     1946                    '),(65,1,'   ຄອງແສນແສບຢ່າຊຳຮອຍ        ສະຖາບັນຄົນຄວ້າວັດທະນະທຳ  ສະຖາບັນ ','                         '),(59,1,'   ທົດລອງ        ສູນກາງສະຫະພັນກຳມະບານລາວ  ສະຖາບັນ ','                         '),(60,1,'   ກອງປະຊຸມສະຫະພັນກຳມະບານລາວ IV    ສະຫຼຸບຜົນສຳເລັດຂອງກອງປະຊູມ ກອງປະຊູມ  ກ່ຽວກັບກອງປະຊູມ ສູນກາງສະຫະພັນກຳມະບານລາວ  000 ຂໍ້ມູນ ການຕິດຕໍ່ຊື່ສານ ນະຄອນຫລວງ ','     iv                  000     '),(64,1,'   ຂໍ້ມູນສຳຮອງ        ຄະນະອັກສອນສາດ ມ/ຊ  ຫໍພິພິທະພັນ ','                         '),(61,1,'  ວິລະກຳເຈົ້າອະນຸ     ປະຫວັດເຈົ້າອານຸ  ປະຫວັດ ສຸເນດ ໂພທິສານ  000 ຂໍ້ມູນ ການຕິດຕໍ່ຊື່ສານ ສະຖາບັນ ','      bravo test reusi             000     '),(63,1,'   ກາບເມືອງພວນ     ກາບກອນ   ຄະນະອັກສອນສາດ ມ/ຊ  000 ຂໍ້ມູນ ການຕິດຕໍ່ຊື່ສານ ຫໍພິພິທະພັນ ','                       000     '),(27,1,'   ບົດສະເໜີກ່ຽວກັບວິທະຍາສາດສິ່ງແວດລ້ອມ        ຄຳຜາຍ ບຸບຜາ  ວັນນະຄະດີ 010 ຄວາມຮູ້ກ່ຽວກັບຫໍສະໝຸດ ການປົກຄອງ ','      ບົດສະເໜີກ່ຽວກັບວິທະຍາສາດສິ່ງແວດລ້ອມ                  ຄຳຜາຍ ບຸບຜາ      010 ຄວາມຮູ້ກ່ຽວກັບຫໍສະໝຸດ   ການປົກຄອງ  '),(28,1,'   ການ        ','      ການ                 ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_global_index` ENABLE KEYS */;

--
-- Table structure for table `notices_langues`
--

DROP TABLE IF EXISTS `notices_langues`;
CREATE TABLE `notices_langues` (
  `num_notice` int(8) unsigned NOT NULL default '0',
  `type_langue` int(1) unsigned NOT NULL default '0',
  `code_langue` char(3) NOT NULL default '',
  PRIMARY KEY  (`num_notice`,`type_langue`,`code_langue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notices_langues`
--


/*!40000 ALTER TABLE `notices_langues` DISABLE KEYS */;
LOCK TABLES `notices_langues` WRITE;
INSERT INTO `notices_langues` VALUES (1,0,'lao'),(1,1,'lao'),(2,0,'lao'),(2,1,'lao'),(3,0,'lao'),(3,1,'lao'),(4,0,'lao'),(4,1,'lao'),(5,0,'lao'),(5,1,'lao'),(6,0,'lao'),(6,1,'lao'),(7,0,'lao'),(7,1,'lao'),(8,0,'lao'),(8,1,'lao'),(9,0,'lao'),(9,1,'lao'),(10,0,'lao'),(10,1,'lao'),(11,0,'lao'),(11,1,'lao'),(12,0,'lao'),(13,0,'lao'),(13,1,'lao'),(14,0,'lao'),(14,1,'lao'),(15,0,'lao'),(16,0,'lao'),(17,0,'lao'),(18,0,'lao'),(19,0,'lao'),(20,0,'lao'),(21,0,'lao'),(21,1,'lao'),(22,0,'lao'),(23,0,'lao'),(24,0,'lao'),(25,0,'lao'),(27,0,'lao'),(27,1,'lao'),(28,0,'lao');
UNLOCK TABLES;
/*!40000 ALTER TABLE `notices_langues` ENABLE KEYS */;

--
-- Table structure for table `offres_remises`
--

DROP TABLE IF EXISTS `offres_remises`;
CREATE TABLE `offres_remises` (
  `num_fournisseur` int(5) unsigned NOT NULL default '0',
  `num_produit` int(8) unsigned NOT NULL default '0',
  `remise` float(4,2) unsigned NOT NULL default '0.00',
  `condition_remise` text,
  PRIMARY KEY  (`num_fournisseur`,`num_produit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `offres_remises`
--


/*!40000 ALTER TABLE `offres_remises` DISABLE KEYS */;
LOCK TABLES `offres_remises` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `offres_remises` ENABLE KEYS */;

--
-- Table structure for table `opac_sessions`
--

DROP TABLE IF EXISTS `opac_sessions`;
CREATE TABLE `opac_sessions` (
  `empr_id` int(10) unsigned NOT NULL default '0',
  `session` blob,
  `date_rec` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`empr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `opac_sessions`
--


/*!40000 ALTER TABLE `opac_sessions` DISABLE KEYS */;
LOCK TABLES `opac_sessions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `opac_sessions` ENABLE KEYS */;

--
-- Table structure for table `origine_notice`
--

DROP TABLE IF EXISTS `origine_notice`;
CREATE TABLE `origine_notice` (
  `orinot_id` int(8) unsigned NOT NULL auto_increment,
  `orinot_nom` varchar(255) NOT NULL default '',
  `orinot_pays` varchar(255) NOT NULL default 'FR',
  `orinot_diffusion` int(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`orinot_id`),
  KEY `orinot_nom` (`orinot_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `origine_notice`
--


/*!40000 ALTER TABLE `origine_notice` DISABLE KEYS */;
LOCK TABLES `origine_notice` WRITE;
INSERT INTO `origine_notice` VALUES (1,'Catalogage interne','FR',1),(2,'BnF','FR',1),(3,'ກະຊວງສຶກສາທິການ','LA',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `origine_notice` ENABLE KEYS */;

--
-- Table structure for table `ouvertures`
--

DROP TABLE IF EXISTS `ouvertures`;
CREATE TABLE `ouvertures` (
  `date_ouverture` date NOT NULL default '0000-00-00',
  `ouvert` int(1) NOT NULL default '1',
  `commentaire` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`date_ouverture`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ouvertures`
--


/*!40000 ALTER TABLE `ouvertures` DISABLE KEYS */;
LOCK TABLES `ouvertures` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ouvertures` ENABLE KEYS */;

--
-- Table structure for table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE `paiements` (
  `id_paiement` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `commentaire` text NOT NULL,
  PRIMARY KEY  (`id_paiement`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `paiements`
--


/*!40000 ALTER TABLE `paiements` DISABLE KEYS */;
LOCK TABLES `paiements` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `paiements` ENABLE KEYS */;

--
-- Table structure for table `parametres`
--

DROP TABLE IF EXISTS `parametres`;
CREATE TABLE `parametres` (
  `id_param` int(6) unsigned NOT NULL auto_increment,
  `type_param` varchar(20) default NULL,
  `sstype_param` varchar(255) default NULL,
  `valeur_param` text,
  `comment_param` varchar(255) default NULL,
  `section_param` varchar(255) NOT NULL default '',
  `gestion` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id_param`),
  UNIQUE KEY `typ_sstyp` (`type_param`,`sstype_param`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `parametres`
--


/*!40000 ALTER TABLE `parametres` DISABLE KEYS */;
LOCK TABLES `parametres` WRITE;
INSERT INTO `parametres` VALUES (1,'pmb','bdd_version','v4.26','Version de noyau de la base de donn�es, � ne changer qu\'en version inf�rieure si un param�tre �tait mal pass� et relancer la mise � jour. En g�n�ral, contactez plut�t la mailing liste pmb.user@sigb.net','',0),(2,'z3950','accessible','1','Z3950 accessible ?\r\n 0 : non, menu inaccessible\r\n 1 : Oui, la librairie PHP_YAZ est activ�e, la recherche z3950 est possible','',0),(3,'pmb','nb_lastautorities','10','Nombre de derni�res autorit�es affich�es en gestion d\'autorit�s','',0),(4,'pdflettreretard','1before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້','Texte apparaissant avant la liste des ouvrages en retard dans le courrier de relance de retard','',0),(5,'pdflettreretard','1after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ','Texte apparaissant apr�s la liste des ouvrages en retard dans le courrier','',0),(6,'pdflettreretard','1fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire de la lettre.','',0),(7,'pdflettreretard','1madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ ,','Ent�te de la lettre','',0),(8,'pdflettreretard','1nb_par_page','7','Nombre d\'ouvrages en retard imprim� sur les pages suivantes.','',0),(9,'pdflettreretard','1nb_1ere_page','4','Nombre d\'ouvrages en retard imprim� sur la premi�re page','',0),(10,'pdflettreretard','1taille_bloc_expl','16','Taille d\'un bloc (2 lignes) d\'ouvrage en retard. Le d�but de chaque ouvrage en retard sera espac� de cette valeur sur la page','',0),(11,'pdflettreretard','1debut_expl_1er_page','160','D�but de la liste des exemplaires sur la premi�re page, en mm depuis le bord sup�rieur de la page. Doit �tre r�gl� en fonction du texte qui pr�c�de la liste des ouvrages, lequel peut �tre plus ou moins long.','',0),(12,'pdflettreretard','1debut_expl_page','15','D�but de la liste des exemplaires sur les pages suivantes, en mm depuis le bord sup�rieur de la page.','',0),(13,'pdflettreretard','1limite_after_list','270','Position limite en bas de page. Si un �l�ment imprim� tente de d�passer cette limite, il sera imprim� sur la page suivante.','',0),(14,'pdflettreretard','1marge_page_gauche','10','Marge de gauche en mm','',0),(15,'pdflettreretard','1marge_page_droite','10','Marge de droite en mm','',0),(16,'pdflettreretard','1largeur_page','210','Largeur de la page en mm','',0),(17,'pdflettreretard','1hauteur_page','297','Hauteur de la page en mm','',0),(18,'pdflettreretard','1format_page','P','Format de la page : \r\n P : Portrait\r\n L : Landscape = paysage','',0),(19,'pdfcartelecteur','pos_h','20','Position horizontale en mm � partir du bord gauche de la page','',0),(20,'pdfcartelecteur','pos_v','20','Position verticale en mm � partir du bord sup�rieur de la page','',0),(21,'pdfcartelecteur','biblio_name','$biblio_name','Nom de la biblioth�que ou du centre de ressources imprim� sur la carte de lecteur. Mettre $biblio_name pour reprendre le nom sp�cifi� en localisation d\'exemplaire ou bien mettre autre chose.','',0),(22,'pdfcartelecteur','largeur_nom','80','Largeur accord�e � l\'impression du nom du lecteur en mm','',0),(23,'pdfcartelecteur','valabledu','ໃຊ້ໄດ້ວັນທີ່','\'Valable du\' dans \"VALABLE DU ##/##/#### au ##/##/####\"','',0),(24,'pdfcartelecteur','valableau','ຫາ','\'au\' dans \"valable du ##/##/#### AU ##/##/####\"','',0),(25,'pdfcartelecteur','carteno','ເລກບັດ :','Mention pr�c�dant le num�ro de la carte','',0),(26,'sauvegarde','cle_crypt1','9b4a840d790eadc71b9064c9a843719b','','',0),(27,'sauvegarde','cle_crypt2','51580d4fd5f1ad2d981c91ddb04095ec','','',0),(28,'pmb','resa_dispo','1','R�servation de documents disponibles possible ?\r\n 0 : Non\r\n 1 : Oui','',0),(29,'mailretard','1objet','$biblio_name : ເອກະສານສົ່ງຊ້າ','Objet du mail de relance de retard','',0),(30,'mailretard','1before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້ :','Texte apparaissant avant la liste des ouvrages en retard dans le mail de relance de retard','',0),(31,'mailretard','1after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ.','Texte apparaissant apr�s la liste des ouvrages en retard dans le mail','',0),(32,'mailretard','1madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ','Ent�te du mail','',0),(33,'mailretard','1fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire du mail de relance de retard','',0),(34,'pmb','serial_link_article','0','Pr�remplissage du lien des d�pouillements avec le lien de la notice m�re en catalogage des p�riodiques ?\r\n 0 : Non\r\n 1 : Oui','',0),(35,'pmb','num_carte_auto','1','Num�ro de carte de lecteur automatique ? \r\n 1 : Oui\r\n 0 : Non (si utilisation de cartes pr�-imprim�es)','',0),(36,'opac','modules_search_title','2','Recherche simple dans les titres:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(37,'opac','modules_search_author','2','Recherche simple dans les auteurs:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(38,'opac','modules_search_publisher','1','Recherche simple dans les �diteurs:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(39,'opac','modules_search_collection','1','Recherche simple dans les collections:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(40,'opac','modules_search_subcollection','1','Recherche simple dans les sous-collections:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(41,'opac','modules_search_category','1','Recherche simple dans les cat�gories:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(42,'opac','modules_search_keywords','1','Recherche simple dans les indexations libres (mots cl�):\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(43,'opac','modules_search_abstract','1','Recherche simple dans le champ r�sum� :\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(44,'opac','modules_search_content','0','Recherche simple dans les notes de contenu:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut\r\nINUTILISE POUR L\'INSTANT','c_recherche',0),(45,'opac','categories_categ_path_sep','>','S�parateur pour les cat�gories','i_categories',0),(46,'opac','categories_columns','3','Nombre de colonnes du sommaire g�n�ral des cat�gories','i_categories',0),(47,'opac','categories_categ_rec_per_page','6','Nombre de notices � afficher par page dans l\'exploration des cat�gories','i_categories',0),(48,'opac','categories_categ_sort_records','index_serie, tnvol, index_sew','Explorateur de cat�gories : mode de tri des notices :\r\n index_serie, tnvol, index_sew > par titre de s�rie, num�ro dans la s�rie et index des titres\r\n rand() : al�atoire','i_categories',0),(49,'opac','search_results_first_level','4','Nombre de r�sulats affich�s sur la premi�re page','z_unused',0),(50,'opac','search_results_per_page','10','Nombre de r�sulats affich�s sur les pages suivantes','d_aff_recherche',0),(51,'opac','authors_aut_rec_per_page','1','Nombre d\'auteurs affich�s par page','d_aff_recherche',0),(52,'opac','categories_sub_display','3','Nombre de sous-categories sur la premi�re page','i_categories',0),(53,'opac','categories_sub_mode','libelle_categorie','Mode affichage des sous-categories : \r\n rand() > al�atoire\r\n libelle_categorie > ordre alpha','i_categories',0),(54,'opac','authors_aut_sort_records','index_serie, tnvol, index_sew','Visu auteurs : tri des notices','d_aff_recherche',0),(55,'opac','default_lang','la_LA','Langue de l\'opac : fr_FR ou en_US ou es_ES ou ar ou la_LA','a_general',0),(56,'opac','show_categ_browser','1','Affichage des cat�gories en page d\'accueil OPAC 1: oui  ou 0: non','f_modules',0),(57,'opac','show_book_pics','1','Afficher les vignettes de livres dans les fiches ouvrages :\r\n 0 : Non\r\n 1 : Oui','e_aff_notice',0),(58,'opac','resa','1','R�servations possibles par l\'OPAC 1: oui  ou 0: non','a_general',0),(59,'opac','resa_dispo','1','R�servations possibles de documents disponibles par l\'OPAC \r\n 1: oui \r\n 0: non','a_general',0),(60,'opac','show_meteo','0','Affichage de la m�t�o dans l\'OPAC 1: oui  ou 0: non','f_modules',0),(61,'opac','duration_session_auth','1200','Dur�e de la session lecteur dans l\'OPAC en secondes','a_general',0),(62,'pmb','relance_adhesion','31','Nombre de jours avant expiration adh�sion pour relance','',0),(63,'pmb','pret_adhesion_depassee','1','Pr�ts si adh�sion d�pass�e : 0 INTERDIT incontournable, 1 POSSIBLE','',0),(64,'pdflettreadhesion','fdp','ຜູ້ຮັບຜິດຊອບ.','Formule de politesse en bas de page','',0),(65,'pdflettreadhesion','madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ ,','Civilit� du destinataire','',0),(66,'pdflettreadhesion','texte','Votre abonnement arrive � �ch�ance le !!date_fin_adhesion!!. Nous vous remercions de penser � le renouveller lors de votre prochaine visite.\r\n\r\nNous vous prions de recevoir, Madame, Monsieur, l\'expression de nos meilleures salutations.\r\n\r\n\r\n','Phrase d\'introduction de l\'�ch�ance de l\'abonnement','',0),(67,'pdflettreadhesion','marge_page_gauche','10','Marge gauche de la page en mm','',0),(68,'pdflettreadhesion','marge_page_droite','10','Marge droite de la page en mm','',0),(69,'pdflettreadhesion','largeur_page','210','Largeur de la page en mm','',0),(70,'pdflettreadhesion','hauteur_page','297','Hauteur de la page en mm','',0),(71,'pdflettreadhesion','format_page','P','P pour Portrait, L pour paysage (Landscape)','',0),(72,'mailrelanceadhesion','objet','$biblio_name : ການເປັນຊະມາຊິກຂອງທ່ານ','Objet du courrier de relance d\'adh�sion. Utilisez biblio_name pour reprendre le nom pr�cis� dans la localisation des exemplaires.','',0),(73,'mailrelanceadhesion','texte','ການເປັນຊະມາຊິກຂອງທ່ານຈະໜົດກຳນົດວັນທີ່ !!date_fin_adhesion!!. ພວກເຮົາຈະຂອບໃຈທ່ານຫຼາຍໆ ທີ່ ທ່ານຈະເຂົ້າມາຕໍ່ບັດຊະມາຊິກຂອງທ່ານ.\r\n\r\nດ້ວຍຄວາມນັບຖື,\r\n\r\n','Texte de la relance, !!date_fin_adhesion!! sera remplac� � l\'�dition par la date de fin d\'adh�sion du lecteur','',0),(74,'mailrelanceadhesion','madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ,','Ent�te du courrier de relance d\'adh�sion','',0),(75,'mailrelanceadhesion','fdp','ດ້ວຍຄວາມເຄົາລົບ','Formule de politesse en bas de page','',0),(76,'opac','show_marguerite_browser','0','0 ou 1 : marguerite des cat�gories','f_modules',0),(77,'opac','show_100cases_browser','0','0 ou 1 : affichage de 100 cat�gories','f_modules',0),(78,'pmb','indexint_decimal','1','0 ou 1 : l\'indexation interne est-elle une cotation d�cimale type Dewey','',0),(79,'opac','modules_search_indexint','1','Recherche simple dans les indexations internes:\r\n 0 : interdite\r\n 1 : autoris�e\r\n 2 : autoris�e et valid�e par d�faut','c_recherche',0),(80,'empr','birthdate_optional','1','Ann�e de naissance facultative : \r\n 0 > non:elle est obligatoire \r\n 1 Oui','',0),(81,'categories','show_empty_categ','1','Affichage des cat�gories ne contenant aucune notice :\r\n0=non, 1=oui','',0),(82,'categories','term_search_n_per_page','50','Nombre de termes affich�s par page lors d\'une recherche par terme dans les cat�gories','',0),(83,'opac','show_loginform','1','Affichage du login lecteur dans l\'OPAC \r\n 0 > non\r\n 1 Oui','f_modules',0),(84,'opac','default_style','bueil','Style graphique de l\'OPAC, 1 style par d�faut, nomargin : sans affichage du bandeau de gauche','a_general',0),(85,'opac','show_exemplaires','1','Afficher les exemplaires dans l\'OPAC\n 1 Oui,\n 0 : Non','e_aff_notice',0),(86,'pmb','import_modele','func_bdp.inc.php','Quel script de fonctions d\'import utiliser pour personnaliser l\'import ?','',0),(87,'pmb','quotas_avances','0','Quotas de pr�ts avanc�es ? \r\n 0 : Non\r\n 1 : Oui','',0),(88,'opac','logo','logo_default.jpg','Nom du fichier de l\'image logo','z_unused',0),(89,'opac','logosmall','images/site/livre.png','Nom du fichier de l\'image petit logo','b_aff_general',0),(90,'opac','show_bandeaugauche','1','Affichage du bandeau de gauche ? \n 0 : Non\n 1 : Oui','f_modules',0),(91,'opac','show_liensbas','1','Affichage des liens(pmb, google, bibli) en bas de page ? \n 0 : Non\n 1 : Oui','f_modules',0),(92,'opac','show_homeontop','0','Affichage du lien HOME (retour accueil) sous le nom de la biblioth�que ou du centre de ressources (n�cessaire si masquage bandeau gauche) ? \r\n 0 : Non\r\n 1 : Oui','f_modules',0),(93,'pmb','resa_quota_pret_depasse','1','R�servation possible m�me si quota de pr�t d�pass� ? \n 0 : Non\n 1 : Oui','',0),(94,'pmb','import_limit_read_file','100','Limite de taille de lecture du fichier en import, en g�n�ral 100 ou 200 doit fonctionner, si probl�me de time out : fixer plus bas, 50 par exemple.','',0),(95,'pmb','import_limit_record_load','100','Limite de taille de traitement de notices en import, en g�n�ral 100 ou 200 doit fonctionner, si probl�me de time out : fixer plus bas, 50 par exemple.','',0),(96,'opac','biblio_preamble_p1','ຫໍສະໝຸດຂອງການທົດສອບ PMB ສະເໜີທ່ານ 60 ເອກະສານ ເພື່ອທົດສອບລະບົບ, ໜ້ານີ້ສະເໜີຫຼາຍທາງເລືອກຂອງການຊອກ ແລະ ການ ເຄື່ອນທີ່ຈາກໜ້ານີ້ຫາໜ້າອື່ນ, ສິ່ງເຫຼົ່ານີ້ ແມ່ນສາມາດດັດແປງໄດ້ .','Paragraphe 1 d\'informations (par exemple, description du fonds)','b_aff_general',0),(97,'opac','biblio_preamble_p2','ການບໍລິການ PMB ແມ່ນເປັນຂອງທ່ານແລ້ວ ເພື່ອຊ່ວຍທ່ານໃນການດັດແກ້ ຫຼື ເຮັດໃຫ້  PMB ຂອງທ່ານແທດເໝາະກັບການນຳໃຊ້.','Paragraphe 2 d\'informations : accueil du public.','b_aff_general',0),(98,'opac','biblio_quicksummary_p1','','Paragraphe 1 de r�sum�, est masqu� par d�faut dans la feuille de style, voir id quickSummary.p1','z_unused',0),(99,'opac','biblio_quicksummary_p2','','Paragraphe 2 de r�sum�, est masqu� par d�faut dans la feuille de style, voir id quickSummary.p2','z_unused',0),(100,'opac','show_dernieresnotices','0','Affichage des derni�res notices cr��es en bas de page ? \n 0 : Non\n 1 : Oui','f_modules',0),(101,'opac','show_etageresaccueil','1','Affichage des �tag�res dans la page d\'accueil en bas de page ? \n 0 : Non\n 1 : Oui','f_modules',0),(102,'opac','biblio_important_p1','','Infos importantes 1, dans la feuille de style, voir id important.p1','b_aff_general',0),(103,'opac','biblio_important_p2','','Infos importantes, dans la feuille de style, voir id important.p2','b_aff_general',0),(104,'opac','biblio_name','ຫໍສະໝຸດແຫ່ງຊາດ','Nom de la biblioth�que ou du centre de ressources dans l\'opac','b_aff_general',0),(105,'opac','biblio_website','www.bnlaos.org','Site web de la biblioth�que ou du centre de ressources dans l\'opac','b_aff_general',0),(106,'opac','biblio_adr1','ຖະໜົນ ເສດຖາທິລາດ','Adresse 1 de la biblioth�que ou du centre de ressources dans l\'opac','b_aff_general',0),(107,'opac','biblio_town','ວຽງຈັນ','Ville dans l\'opac','b_aff_general',0),(108,'opac','biblio_cp','ຕູ້ ປ.ນ 122 ບ້ານຊຽງຍືນ','Code postal dans l\'opac','b_aff_general',0),(109,'opac','biblio_country','ສປປລາວ ','Pays dans l\'opac','b_aff_general',0),(110,'opac','biblio_phone','(+85621) 251 405','T�l�phone dans l\'opac','b_aff_general',0),(111,'opac','biblio_dep','37','D�partement dans l\'opac pour la m�t�o','b_aff_general',0),(112,'opac','biblio_email','bnl@laosky.com','Email de contact dans l\'opac','b_aff_general',0),(113,'opac','etagere_notices_order','index_serie, tnvol, index_sew','Ordre d\'affichage des notices dans les �tag�res dans l\'opac \n  index_serie, tit1 : tri par titre de s�rie et titre \n rand()  : al�atoire','j_etagere',0),(114,'opac','etagere_notices_format','4','Format d\'affichage des notices dans les �tag�res de l\'�cran d\'accueil \r\n 1 : ISBD seul \r\n 2 : Public seul \r\n 4 : ISBD et Public \r\n 8 : R�duit (titre+auteurs) seul','j_etagere',0),(115,'opac','etagere_notices_depliables','1','Affichage d�pliable des notices dans les �tag�res de l\'�cran d\'accueil \r\n 0 : Non \r\n 1 : Oui','j_etagere',0),(116,'opac','etagere_nbnotices_accueil','5','Nombre de notices affich�es dans les �tag�res de l\'�cran d\'accueil \r\n 0 : Toutes \r\n -1 : Aucune \r\n x : x notices affich�es au maximum','j_etagere',0),(117,'opac','nb_aut_rec_per_page','15','Nombre de notices affich�es pour une autorit� donn�e','d_aff_recherche',0),(118,'opac','notices_format','4','Format d\'affichage des notices dans les �tag�res de l\'�cran d\'accueil \n 1 : ISBD seul \n 2 : Public seul \n 4 : ISBD et Public \n 5 : ISBD et Public avec ISBD en premier \n 8 : R�duit (titre+auteurs) seul','e_aff_notice',0),(119,'opac','notices_depliable','1','Affichage d�pliable des notices en r�sultat de recherche  0 : Non  1 : Oui','e_aff_notice',0),(120,'opac','term_search_n_per_page','50','Nombre de termes affich�s par page en recherche par terme','c_recherche',0),(121,'opac','show_empty_categ','1','En recherche par terme, affichage des cat�gories ne contenant aucun ouvrage :\r\n 0 : Non \r\n 1 : Oui','i_categories',0),(122,'opac','allow_extended_search','1','Autorisation ou non de la recherche avanc�e dans l\'OPAC \n 0 : Non \n 1 : Oui','c_recherche',0),(123,'opac','allow_term_search','1','Autorisation ou non de la recherche par termes dans l\'OPAC \n 0 : Non \n 1 : Oui','c_recherche',0),(124,'opac','term_search_height','350','Hauteur en pixels de la frame de recherche par termes (si pas pr�cis� ou z�ro : par d�faut 200 pixels)','c_recherche',0),(125,'opac','categories_nb_col_subcat','3','Nombre de colonnes de sous-cat�gories en navigation dans les cat�gories \n 3 par d�faut','i_categories',0),(126,'opac','max_resa','5','Nombre maximum de r�servation sur un document \r\n 5 par d�faut \r\n 0 pour illimit�','a_general',0),(127,'pmb','show_help','1','Affichage de l\'aide contextuelle dans PMB en partie gestion \r\n 1 Oui \r\n 0 Non','',0),(128,'opac','show_help','1','Affichage de l\'aide en ligne dans l\'OPAC de PMB  \n 1 Oui \n 0 Non','f_modules',0),(129,'opac','cart_allow','1','Paniers possibles dans l\'OPAC de PMB  \n 1 Oui \n 0 Non','f_modules',0),(130,'opac','max_cart_items','200','Nombre maximum de notices dans un panier utilisateur.','h_cart',0),(131,'opac','show_section_browser','1','Afficher le butineur de localisation et de sections ?\n 0 : Non\n 1 : Oui','f_modules',0),(132,'opac','nb_localisations_per_line','6','Nombre de localisations affich�es par ligne en page d\'accueil (si show_section_browser=1)','k_section',0),(133,'opac','nb_sections_per_line','6','Nombre de sections affich�es par ligne en visualisation de localisation (si show_section_browser=1)','k_section',0),(134,'opac','cart_only_for_subscriber','1','Paniers de notices r�serv�s aux adh�rents de la biblioth�que ou du centre de ressources ?\r\n 1: Oui\r\n 0: Non, autoris� pour tout internaute','h_cart',0),(135,'opac','notice_reduit_format','0','Format d\'affichage des r�duits des notices :\r\n 0 normal = titre+auteurs principaux\r\n P 1,2,3: Perso. : tit+aut+champs persos id 1 2 3\r\n E 1,2,3: Perso. : tit+aut+�dit+champs persos id 1 2 3 \r\n T : tit1+tit4','e_aff_notice',0),(136,'pdflettreresa','before_list','Suite � votre demande de r�servation, nous vous informons que le ou les ouvrages ci-dessous sont � votre disposition � la biblioth�que.','Texte apparaissant avant la liste des ouvrages en r�sa dans le courrier de confirmation de r�sa','',0),(137,'pdflettreresa','after_list','Pass� le d�lai de r�servation, ces ouvrages seront remis en circulation, vous priant de les retirer dans les meilleurs d�lais.','Texte apparaissant apr�s la liste des ouvrages','',0),(138,'pdflettreresa','fdp','Le responsable.','Signataire de la lettre, utiliser $biblio_name pour reprendre le param�tre \"biblio name\" ou bien mettre autre chose.','',0),(139,'pdflettreresa','madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ ','Ent�te de la lettre','',0),(140,'pdflettreresa','nb_par_page','7','Nombre d\'ouvrages en retard imprim� sur les pages suivantes.','',0),(141,'pdflettreresa','nb_1ere_page','4','Nombre d\'ouvrages en retard imprim� sur la premi�re page','',0),(142,'pdflettreresa','taille_bloc_expl','16','Taille d\'un bloc (2 lignes) d\'ouvrage en r�servation. Le d�but de chaque ouvrage en r�sa sera espac� de cette valeur sur la page','',0),(143,'pdflettreresa','debut_expl_1er_page','160','D�but de la liste des ouvrages sur la premi�re page, en mm depuis le bord sup�rieur de la page. Doit �tre r�gl� en fonction du texte qui pr�c�de la liste des ouvrages, lequel peut �tre plus ou moins long.','',0),(144,'pdflettreresa','debut_expl_page','15','D�but de la liste des ouvrages sur les pages suivantes, en mm depuis le bord sup�rieur de la page.','',0),(145,'pdflettreresa','limite_after_list','270','Position limite en bas de page. Si un �l�ment imprim� tente de d�passer cette limite, il sera imprim� sur la page suivante.','',0),(146,'pdflettreresa','marge_page_gauche','10','Marge de gauche en mm','',0),(147,'pdflettreresa','marge_page_droite','10','Marge de droite en mm','',0),(148,'pdflettreresa','largeur_page','210','Largeur de la page en mm','',0),(149,'pdflettreresa','hauteur_page','297','Hauteur de la page en mm','',0),(150,'pdflettreresa','format_page','P','Format de la page : \r\n P : Portrait\r\n L : Landscape = paysage','',0),(151,'opac','categories_max_display','200','Pour la page d\'accueil, nombre maximum de cat�gories principales affich�es','i_categories',0),(152,'opac','search_other_function','','Fonction compl�mentaire pour les recherches en page d\'accueil','c_recherche',0),(153,'opac','lien_bas_supplementaire','<a href=\'http://www.sigb.net.com/poomble.php\' target=_blank>ລິ້ງຕໍ່ຫາເວັບອື່ນ\r\n</a>','Lien suppl�mentaire en bas de page d\'accueil, � renseigner compl�tement : a href= lien /a','b_aff_general',0),(154,'z3950','import_modele','func_other.inc.php','Quel script de fonctions d\'import utiliser pour personnaliser l\'import en int�gration z3950 ?','',0),(155,'ldap','server','chinon','Serveur LDAP, IP ou host','',0),(156,'ldap','basedn','','Racine du nom de domaine LDAP','',0),(157,'ldap','port','389','Port du serveur LDAP','',0),(158,'ldap','filter','(&(objectclass=person)(gidnumber=GID))','Serveur LDAP, IP ou host','',0),(159,'ldap','fields','uid,gecos,departmentnumber','Champs du serveur LDAP','',0),(160,'ldap','lang','fr_FR','Langue du serveur LDAP','',0),(161,'ldap','groups','','Groupes du serveur LDAP','',0),(162,'ldap','accessible','0','LDAP accessible ?','',0),(163,'opac','categories_show_only_last','0','Dans la fiche d\'une notice : \n 0 tout afficher \n 1 : afficher uniquement la derni�re feuille de l\'arbre de la cat�gorie','i_categories',0),(164,'categories','show_only_last','0','Dans la fiche d\'une notice : \n 0 tout afficher \n 1 : afficher uniquement la derni�re feuille de l\'arbre de la cat�gorie','',0),(165,'pmb','prefill_cote','custom_cote_02.inc.php','Script personnalis� de construction de la cote de l\'exemplaire','',0),(166,'ldap','proto','3','Version du protocole LDAP : 3 ou 2','',0),(167,'ldap','binddn','uid=UID,ou=People','Description de la liaison : construction de la chaine binddn pour lier l\'authentification au serveur LDAP dans l\'OPAC','',0),(168,'empr','corresp_import','','Table de correspondances colonnes/champs en import de lecteurs � partir d\'un fichier ASCII','',0),(169,'pmb','type_audit','0','Gestion/affichage des dates de cr�ation/modification \n 0: Rien\n 1: Cr�ation et derni�re modification\n 2: Cr�ation et toutes les dates de modification','',0),(170,'pmb','gestion_abonnement','0','Utiliser la gestion des abonnements des lecteurs ? \n 0 : Non\n 1 : Oui, gestion simple, \n 2 : Oui, gestion avanc�e','',0),(171,'pmb','utiliser_calendrier','0','Utiliser le calendrier des jours d\'ouverture ? \n 0 : Non\n 1 : Oui','',0),(172,'pmb','gestion_financiere','0','Utiliser le module gestion financi�re ? \n 0 : Non\n 1 : Oui','',0),(173,'pmb','gestion_tarif_prets','0','Utiliser la gestion des tarifs de pr�ts ? \n 0 : Non\n 1 : Oui, gestion simple, \n 2 : Oui, gestion avanc�e','',0),(174,'pmb','gestion_amende','0','Utiliser la gestion des amendes:\n 0 = Non\n 1 = Gestion simple\n 2 = Gestion avanc�e','',0),(175,'finance','amende_jour','0.15','Amende par jour de retard pour tout type de document. Attention, le s�parateur d�cimal est le point, pas la virgule','',1),(176,'finance','delai_avant_amende','15','D�lai avant d�clenchement de l\'amende, en jour','',1),(177,'finance','delai_recouvrement','7','D�lai entre 3eme relance et mise en recouvrement officiel de l\'amende, en jour','',1),(178,'finance','amende_maximum','0','Amende maximum, quel que soit le retard l\'amende est plafonn�e � ce montant. 0 pour d�sactiver ce plafonnement.','',1),(179,'pdflettreresa','priorite_email','1','Priorit� des lettres de confirmation de r�servation par mail lors de la validation d\'une r�servation:\n 0 : Lettre seule \n 1 : Mail, � d�faut lettre\n 2 : Mail ET lettre\n 3 : Aucune alerte','',0),(180,'pdflettreresa','priorite_email_manuel','1','Priorit� des lettres de confirmation de r�servation par mail lors de l\'impression � partir du bouton :\n 0 : Lettre seule \n 1 : Mail, � d�faut lettre\n 2 : Mail ET lettre\n 3 : Aucune alerte','',0),(181,'finance','blocage_abt','1','Blocage du pr�t si le compte abonnement est d�biteur\n 0 : pas de blocage \n 1 : blocage avec for�age possible  : blocage incontournable.','',1),(182,'finance','blocage_pret','1','Blocage du pr�t si le compte pr�t est d�biteur\n 0 : pas de blocage \n 1 : blocage avec for�age possible  : blocage incontournable.','',1),(183,'finance','blocage_amende','1','Blocage du pr�t si le compte amende est d�biteur\n 0 : pas de blocage \n 1 : blocage avec for�age possible  : blocage incontournable.','',1),(184,'pmb','gestion_devise','&euro;','Devise de la gestion financi�re, ce qui va �tre affich� en code HTML','',0),(185,'opac','book_pics_url','','URL des vignettes des notices, dans le chemin fourni, !!isbn!! sera remplac� par le code ISBN ou EAN de la notice purg� de tous les tirets ou points. \n exemple : http://www.monsite/opac/images/vignettes/!!isbn!!.jpg','e_aff_notice',0),(186,'opac','lien_moteur_recherche','<a href=http://www.google.fr target=_blank>&#3735;&#3763;&#3713;&#3762;&#3737;&#3722;&#3757;&#3713;&#3713;&#3761;&#3738;&#3776;&#3751;&#3761;&#3738; &#3713;&#3769;&#3784;&#3778;&#3713;&#3785;  </a>','Lien suppl�mentaire en bas de page d\'accueil, � renseigner compl�tement : a href= lien /a','b_aff_general',0),(187,'pmb','pret_express_statut','2','Statut de notice � utiliser en cr�ation d\'exemplaires en pr�ts express','',0),(188,'opac','notice_affichage_class','','Nom de la classe d\'affichage pour personnalisation de l\'affichage des notices','e_aff_notice',0),(189,'pmb','confirm_retour','0','En retour de documents, le retour doit-il �tre confirm� ? \n 0 : Non, on peut passer les codes-barres les uns apr�s les autres \n 1 : Oui, il faut valider le retour apr�s chaque code-barre','',0),(190,'opac','show_meteo_url','<img src=\"http://perso0.free.fr/cgi-bin/meteo.pl?dep=72\" alt=\"\" border=\"0\" hspace=0>','URL de la m�t�o affich�e','f_modules',0),(191,'pmb','limitation_dewey','0','Nombre maximum de caract�res dans la Dewey (676) en import : \n 0 aucune limitation \n 3 : limitation de 000 � 999 \n 5 (exemple) limitation 000.0 \n -1 : aucune importation','',0),(192,'finance','delai_1_2','15','D�lai entre 1ere et 2eme relance','',1),(193,'finance','delai_2_3','15','D�lai entre 2eme et 3eme relance','',1),(194,'pmb','lecteurs_localises','0','Lecteurs localis�s ? \n 0: Non \n 1: Oui','',0),(195,'dsi','active','1','D.S.I activ�e ? \n 0: Non \n 1: Oui','',0),(196,'dsi','auto','0','D.S.I automatique activ�e ? \n 0: Non \n 1: Oui','',0),(197,'dsi','insc_categ','0','Inscription automatique dans les bannettes de la cat�gorie du lecteur en cr�ation ? \n 0: Non \n 1: Oui','',0),(198,'opac','allow_bannette_priv','0','Possibilit� pour les lecteurs de cr�er ou modifier leurs bannettes priv�es \n 0: Non \n 1: Oui','l_dsi',0),(199,'opac','allow_resiliation','0','Possibilit� pour les lecteurs de r�silier leur abonnement aux bannettes pro \n 0: Non \n 1: Oui','l_dsi',0),(200,'opac','show_categ_bannette','0','Affichage des bannettes de la cat�gorie du lecteur et possibilit� de s\'y abonner \n 0: Non \n 1: Oui','l_dsi',0),(201,'opac','url_base','./','URL de base de l\'opac : typiquement mettre l\'url publique web http://monsite/opac/ ne pas oublier le / final','a_general',0),(202,'finance','relance_1','0.53','Frais de la premi�re lettre de relance','',1),(203,'finance','relance_2','0.53','Frais de la deuxi�me lettre de relance','',1),(204,'finance','relance_3','2.50','Frais de la troisi�me lettre de relance','',1),(205,'finance','statut_perdu','','Statut (d\'exemplaire) perdu pour des ouvrages non rendus','',1),(206,'pdflettreretard','2after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ.','Texte apparaissant apr�s la liste des ouvrages en retard dans le courrier','',0),(207,'pdflettreretard','2before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້','Texte apparaissant avant la liste des ouvrages en retard dans le courrier de relance de retard','',0),(208,'pdflettreretard','2debut_expl_1er_page','160','D�but de la liste des exemplaires sur la premi�re page, en mm depuis le bord sup�rieur de la page. Doit �tre r�gl� en fonction du texte qui pr�c�de la liste des ouvrages, lequel peut �tre plus ou moins long.','',0),(209,'pdflettreretard','2debut_expl_page','15','D�but de la liste des exemplaires sur les pages suivantes, en mm depuis le bord sup�rieur de la page.','',0),(210,'pdflettreretard','2fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire de la lettre.','',0),(211,'pdflettreretard','2format_page','P','Format de la page : \r\n P : Portrait\r\n L : Landscape = paysage','',0),(212,'pdflettreretard','2hauteur_page','297','Hauteur de la page en mm','',0),(213,'pdflettreretard','2largeur_page','210','Largeur de la page en mm','',0),(214,'pdflettreretard','2limite_after_list','270','Position limite en bas de page. Si un �l�ment imprim� tente de d�passer cette limite, il sera imprim� sur la page suivante.','',0),(215,'pdflettreretard','2madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ,','Ent�te de la lettre','',0),(216,'pdflettreretard','2marge_page_droite','10','Marge de droite en mm','',0),(217,'pdflettreretard','2marge_page_gauche','10','Marge de gauche en mm','',0),(218,'pdflettreretard','2nb_1ere_page','4','Nombre d\'ouvrages en retard imprim� sur la premi�re page','',0),(219,'pdflettreretard','2nb_par_page','7','Nombre d\'ouvrages en retard imprim� sur les pages suivantes.','',0),(220,'pdflettreretard','2taille_bloc_expl','16','Taille d\'un bloc (2 lignes) d\'ouvrage en retard. Le d�but de chaque ouvrage en retard sera espac� de cette valeur sur la page','',0),(221,'pdflettreretard','3after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ.','Texte apparaissant apr�s la liste des ouvrages en retard dans le courrier','',0),(222,'pdflettreretard','3before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້:','Texte apparaissant avant la liste des ouvrages en retard dans le courrier de relance de retard','',0),(223,'pdflettreretard','3debut_expl_1er_page','160','D�but de la liste des exemplaires sur la premi�re page, en mm depuis le bord sup�rieur de la page. Doit �tre r�gl� en fonction du texte qui pr�c�de la liste des ouvrages, lequel peut �tre plus ou moins long.','',0),(224,'pdflettreretard','3debut_expl_page','15','D�but de la liste des exemplaires sur les pages suivantes, en mm depuis le bord sup�rieur de la page.','',0),(225,'pdflettreretard','3fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire de la lettre.','',0),(226,'pdflettreretard','3format_page','P','Format de la page : \r\n P : Portrait\r\n L : Landscape = paysage','',0),(227,'pdflettreretard','3hauteur_page','297','Hauteur de la page en mm','',0),(228,'pdflettreretard','3largeur_page','210','Largeur de la page en mm','',0),(229,'pdflettreretard','3limite_after_list','270','Position limite en bas de page. Si un �l�ment imprim� tente de d�passer cette limite, il sera imprim� sur la page suivante.','',0),(230,'pdflettreretard','3madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ,','Ent�te de la lettre','',0),(231,'pdflettreretard','3marge_page_droite','10','Marge de droite en mm','',0),(232,'pdflettreretard','3marge_page_gauche','10','Marge de gauche en mm','',0),(233,'pdflettreretard','3nb_1ere_page','4','Nombre d\'ouvrages en retard imprim� sur la premi�re page','',0),(234,'pdflettreretard','3nb_par_page','7','Nombre d\'ouvrages en retard imprim� sur les pages suivantes.','',0),(235,'pdflettreretard','3taille_bloc_expl','16','Taille d\'un bloc (2 lignes) d\'ouvrage en retard. Le d�but de chaque ouvrage en retard sera espac� de cette valeur sur la page','',0),(236,'pdflettreretard','3before_recouvrement','Sans nouvelles de votre part dans les sept jours, nous nous verrons contraints de d�l�guer au tr�sor public le recouvrement des ouvrages suivants :','Texte avant la liste des ouvrages en recouvrement','',0),(237,'opac','bannette_notices_order',' index_serie, tnvol, index_sew ','Ordre d\'affichage des notices dans les bannettes dans l\'opac \n  index_serie, tnvol, index_sew : tri par titre de s�rie et titre \n rand()  : al�atoire','l_dsi',0),(238,'opac','bannette_notices_format','8','Format d\'affichage des notices dans les bannettes \n 1 : ISBD seul \n 2 : Public seul \n 4 : ISBD et Public \n 8 : R�duit (titre+auteurs) seul','l_dsi',0),(239,'opac','bannette_notices_depliables','1','Affichage d�pliable des notices dans les bannettes \n 0 : Non \n 1 : Oui','l_dsi',0),(240,'opac','bannette_nb_liste','0','Nbre de notices par bannettes en affichage de la liste des bannettes \n 0 Toutes \n N : maxi N\n -1 : aucune','l_dsi',0),(241,'opac','dsi_active','0','DSI, bannettes accessibles par l\'OPAC ? \n 0 : Non \n 1 : Oui','l_dsi',0),(242,'mailretard','2after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ.','Texte apparaissant apr�s la liste des ouvrages en retard dans le mail','',0),(243,'mailretard','2before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້ :','Texte apparaissant avant la liste des ouvrages en retard dans le mail de relance de retard','',0),(244,'mailretard','2fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire du mail de relance de retard','',0),(245,'mailretard','2madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ,','Ent�te du mail','',0),(246,'mailretard','2objet','$biblio_name : ເອກະສານກາຍກຳນົດສົ່ງ','Objet du mail de relance de retard','',0),(247,'mailretard','3after_list','ພວກເຮົາຂໍຂອບໃຈນຳທ່ານທີ່ຈະຕິດຕໍ່ພວກເຮົາໂດຍທາງໂທລະສັບ ໜາຍເລກ $biblio_phone ຫຼື ໂດຍ email $biblio_email ເພື່ອສຶກສາຄວາມເປັນໄປໄດ້ຂອງການຕໍ່ເວລາການໃຫ້ຢືມ ຫຼືສົ່ງເອກະສານຄືນ.','Texte apparaissant apr�s la liste des ouvrages en retard dans le mail','',0),(248,'mailretard','3before_list','ຍົກເວັ້ນຂໍ້ຜິດພາດຂອງທາງເຮົາ, ທ່ານມີສິດໃນໜຶ່ງຫຼືຫຼາຍເອກະສານ ເຊິ່ງໄລຍະເວລາຂອງການໃຫ້ຢືມແມ່ນໄດ້ກາຍກຳນົດມື້ນີ້ :','Texte apparaissant avant la liste des ouvrages en retard dans le mail de relance de retard','',0),(249,'mailretard','3fdp','ຜູ້ຮັບຜິດຊອບ.','Signataire du mail de relance de retard','',0),(250,'mailretard','3madame_monsieur','ທ່ານຍິງ, ທ່ານຊາຍ,','Ent�te du mail','',0),(251,'mailretard','3objet','$biblio_name : ເອກະສານການກຳນົດສົ່ງ','Objet du mail de relance de retard','',0),(252,'mailretard','3before_recouvrement','Sans nouvelles de votre part dans les sept jours, nous nous verrons contraints de d�l�guer au tr�sor public le recouvrement des ouvrages suivants :','Texte avant la liste des ouvrages en recouvrement','',0),(253,'mailretard','priorite_email','1','Priorit� des lettres de retard lors des relances :\n 0 : Lettre seule \n 1 : Mail, � d�faut lettre\n 2 : Mail ET lettre','',0),(254,'pmb','import_modele_lecteur','','Mod�le d\'import des lecteurs','',0),(255,'pmb','blocage_retard','0','Bloquer le pr�t d\'une dur�e �quivalente au retard ? 0=non, 1=oui','',0),(256,'pmb','blocage_delai','7','D�lai � partir duquel le retard est pris en compte','',0),(257,'pmb','blocage_max','60','Nombre maximum de jours bloqu�s (0 = pas de limite)','',0),(258,'pmb','blocage_coef','1','Coefficient de proportionnalit� des jours de retard pour le blocage','',0),(259,'pmb','blocage_retard_force','1','1 = Le pr�t peut-�tre forc� lors d\'un blocage du compte, 2 = Pas de for�age possible','',0),(260,'opac','etagere_order',' name ','Tri des �tag�res dans l\'�cran d\'accueil, \n name = par nom\n name DESC = par nom d�croissant','j_etagere',0),(261,'pmb','book_pics_show','0','Affichage des couvertures de livres en gestion\n 1: oui  \n 0: non','',0),(262,'pmb','book_pics_url','','URL des vignettes des notices, dans le chemin fourni, !!isbn!! sera remplac� par le code ISBN ou EAN de la notice purg� de tous les tirets ou points. \r\n exemple : http://www.monsite/opac/images/vignettes/!!isbn!!.jpg','',0),(263,'pmb','opac_url','./opac_css/','URL de l\'OPAC vu depuis la partie gestion, par d�faut ./opac_css/','',0),(264,'opac','resa_popup','1','Demande de connexion sous forme de popup ? :\n 0 : Non\n 1 : Oui','a_general',0),(265,'pmb','vignette_x','100','Largeur de la vignette cr��e pour un exemplaire num�rique image','',0),(266,'pmb','vignette_y','100','Hauteur de la vignette cr��e pour un exemplaire num�rique image','',0),(267,'pmb','vignette_imagemagick','','Chemin de l\'ex�cutable ImageMagick (/usr/bin/imagemagick par exemple)','',0),(268,'opac','show_rss_browser','0','Affichage des flux RSS du catalogue en page d\'accueil OPAC 1: oui  ou 0: non','f_modules',0),(269,'pmb','mail_methode','php','M�thode d\'envoi des mails : \n php : fonction mail() de php\n smtp,hote:port,auth,user,pass : en smtp, mettre O ou 1 pour l\'authentification...','',0),(270,'opac','mail_methode','php','M�thode d\'envoi des mails dans l\'opac : \n php : fonction mail() de php\n smtp,hote:port,auth,user,pass : en smtp, mettre O ou 1 pour l\'authentification...','a_general',0),(271,'opac','search_show_typdoc','1','Affichage de la restriction par type de document pour les recherches en page d\'accueil','c_recherche',0),(272,'pmb','verif_on_line','0','Dans le menu Administration > Outils > Maj Base : v�rification d\'une version plus r�cente de PMB en ligne ? \r\n0 : non : si vous n\'�tes pas connect� � internet \r\n 1 : Oui : si vous avez une connexion � internet','',0),(273,'opac','show_languages','1 fr_FR,it_IT,es_ES,ca_ES,en_UK,nl_NL,oc_FR,la_LA','Afficher la liste d�roulante de s�lection de la langue ?','a_general',0),(274,'pmb','pdf_font','Saysettha','Police de caract�res � chasse variable pour les �ditions en pdf - Police Arial','',0),(275,'pmb','pdf_fontfixed','Courier','Police de caract�res � chasse fixe pour les �ditions en pdf - Police Courier','',0),(276,'z3950','debug','0','Debugage (export fichier) des notices lues en Z3950 \r\n 0: Non \r\n 1: 0ui','',0),(277,'pmb','nb_lastnotices','10','Nombre de derni�res notices affich�es en Catalogue - Derni�res notices','',0),(278,'opac','show_dernieresnotices_nb','10','Nombre de derni�res notices affich�es en Catalogue - Derni�res notices','f_modules',0),(279,'pmb','recouvrement_auto','0','Par d�faut passage en recouvrement propos� en gestion des relances si niveau=3 et devrait �tre en 4: \r\n 1: Oui, recouvrement propos� par d�faut \r\n 0: Ne rien faire par d�faut','',0),(280,'pmb','keyword_sep',' ','S�parateur des mots cl�s dans la partie indexation libre, espace ou ; ou , ou ...','',0),(281,'thesaurus','mode_pmb','0','Niveau d\'utilisation des th�saurus.\n 0 : Un seul th�saurus par d�faut.\n 1 : Choix du th�saurus possible.','',0),(282,'thesaurus','defaut','1','Identifiant du th�saurus par d�faut.','',0),(283,'thesaurus','liste_trad','la_LA','Liste des langues affich�es dans les th�saurus.','',0),(284,'opac','thesaurus','0','Niveau d\'utilisation des th�saurus.\n 0 : Un seul th�saurus par d�faut.\n 1 : Choix du th�saurus possible.','a_general',0),(285,'acquisition','active','0','Module acquisitions activ�.\n 0 : Non.\n 1 : Oui.','',0),(286,'acquisition','gestion_tva','0','Gestion de la TVA.\n 0 : Non.\n 1 : Oui.','',0),(287,'acquisition','poids_sugg','U=1.00,E=0.70,V=0.00','Pond�ration des suggestions par d�faut en pourcentage.\n U=Utilisateurs, E=Emprunteurs, V=Visiteurs.\n ex : U=1.00,E=0.70,V=0.00 \n','',0),(288,'acquisition','format','8,CA,DD,BL,FA','Taille du Num�ro et Pr�fixes des actes d\'achats.\nex : 8,CA,DD,BL,FA \n8 = Pr�fixe + 8 Chiffres\nCA=Commande Achat, DD=Demande de Devis,BL=Bon de Livraison, FA=Facture Achat \n','',0),(289,'acquisition','budget','0','Utilisation d\'un budget pour les commandes.\n 0:optionnel\n 1:obligatoire','',0),(290,'acquisition','pdfcde_format_page','210x297','Largeur x Hauteur de la page en mm','pdfcde',0),(291,'acquisition','pdfcde_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdfcde',0),(292,'acquisition','pdfcde_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdfcde',0),(293,'acquisition','pdfcde_pos_logo','10,10,20,20','Position du logo: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur','pdfcde',0),(294,'acquisition','pdfcde_pos_raison','35,10,100,10,16','Position Raison sociale: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(295,'acquisition','pdfcde_pos_date','150,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(296,'acquisition','pdfcde_pos_adr_fac','10,35,60,5,10','Position Adresse de facturation: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(297,'acquisition','pdfcde_pos_adr_liv','10,75,60,5,10','Position Adresse de livraison: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(298,'acquisition','pdfcde_pos_adr_fou','100,55,100,6,14','Position Adresse fournisseur: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(299,'acquisition','pdfcde_pos_num','10,110,0,10,16','Position num�ro de commande: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfcde',0),(300,'acquisition','pdfcde_text_size','10','Taille de la police texte','pdfcde',0),(301,'acquisition','pdfcde_text_before','','Texte avant le tableau de commande','pdfcde',0),(302,'acquisition','pdfcde_text_after','','Texte apr�s le tableau de commande','pdfcde',0),(303,'acquisition','pdfcde_tab_cde','5,10','Table de commandes: Hauteur ligne,Taille police','pdfcde',0),(304,'acquisition','pdfcde_pos_tot','10,40,5,10','Position total de commande: Distance par rapport au bord gauche de la page, Largeur, Hauteur ligne,Taille police','pdfcde',0),(305,'acquisition','pdfcde_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdfcde',0),(306,'acquisition','pdfcde_pos_sign','10,60,5,10','Position signature: Distance par rapport au bord gauche de la page, Largeur, Hauteur ligne,Taille police','pdfcde',0),(307,'acquisition','pdfcde_text_sign','ຜູ້ຮັບຜິດຊອບຫໍສະໝຸດ.','Texte signature','pdfcde',0),(308,'acquisition','pdfdev_format_page','210x297','Largeur x Hauteur de la page en mm','pdfdev',0),(309,'acquisition','pdfdev_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdfdev',0),(310,'acquisition','pdfdev_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdfdev',0),(311,'acquisition','pdfdev_pos_logo','10,10,20,20','Position du logo: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur','pdfdev',0),(312,'acquisition','pdfdev_pos_raison','35,10,100,10,16','Position Raison sociale: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(313,'acquisition','pdfdev_pos_date','150,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(314,'acquisition','pdfdev_pos_adr_fac','10,35,60,5,10','Position Adresse de facturation: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(315,'acquisition','pdfdev_pos_adr_liv','10,75,60,5,10','Position Adresse de livraison: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(316,'acquisition','pdfdev_pos_adr_fou','100,55,100,6,14','Position Adresse fournisseur: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(317,'acquisition','pdfdev_pos_num','10,110,0,10,16','Position num�ro de commande: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfdev',0),(318,'acquisition','pdfdev_text_size','10','Taille de la police texte','pdfdev',0),(319,'acquisition','pdfdev_text_before','','Texte avant le tableau de commande','pdfdev',0),(320,'acquisition','pdfdev_comment','0','Affichage des commentaires : 0=non, 1=oui','pdfdev',0),(321,'acquisition','pdfdev_text_after','','Texte apr�s le tableau de commande','pdfdev',0),(322,'acquisition','pdfdev_tab_dev','5,10','Table de commandes: Hauteur ligne,Taille police','pdfdev',0),(323,'acquisition','pdfdev_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdfdev',0),(324,'acquisition','pdfdev_pos_sign','10,60,5,10','Position signature: Distance par rapport au bord gauche de la page, Largeur, Hauteur ligne,Taille police','pdfdev',0),(325,'acquisition','pdfdev_text_sign','ຜູ້ຮັບຜິດຊອບຫໍສະໝຸດ.','Texte signature','pdfdev',0),(326,'opac','export_allow','1','Export de notices � partir de l\'opac : \n 0 : interdit \n 1 : pour tous \n 2 : pour les abonn�s uniquement','a_general',0),(327,'opac','resa_planning','0','Utiliser un planning de r�servation ? \n 0: Non \n 1: Oui','a_general',0),(328,'opac','resa_contact','<a href=\'mailto:pmb@sigb.net\'>bnl@laosky.com</a>','Code HTML d\'information sur la personne � contacter par exemple en cas de probl�me de r�servation.','a_general',0),(329,'opac','default_operator','0','Op�rateur par d�faut. 0 : OR, 1 : AND.','c_recherche',0),(330,'opac','modules_search_all','2','Recherche simple dans l\'ensemble des champs :0 : interdite,  1 : autoris�e,  2 : autoris�e et valid�e par d�faut','c_recherche',0),(331,'acquisition','pdfliv_format_page','210x297','Largeur x Hauteur de la page en mm','pdfliv',0),(332,'acquisition','pdfliv_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdfliv',0),(333,'acquisition','pdfliv_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdfliv',0),(334,'acquisition','pdfliv_pos_raison','10,10,100,10,16','Position Raison sociale: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfliv',0),(335,'acquisition','pdfliv_pos_adr_liv','10,20,60,5,10','Position Adresse de livraison: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfliv',0),(336,'acquisition','pdfliv_pos_adr_fou','110,20,100,5,10','Position �l�ments fournisseur: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfliv',0),(337,'acquisition','pdfliv_pos_num','10,60,0,6,14','Position num�ro Commande/Livraison: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfliv',0),(338,'acquisition','pdfliv_tab_liv','5,10','Table de livraisons: Hauteur ligne,Taille police','pdfliv',0),(339,'acquisition','pdfliv_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdfliv',0),(340,'pmb','default_operator','0','Op�rateur par d�faut. \n 0 : OR, \n 1 : AND.','',0),(341,'mailretard','priorite_email_3','0','Faire le troisi�me niveau de relance par mail :\n 0 : Non, lettre \n 1 : Oui, par mail','',0),(342,'opac','show_suggest','0','Proposer de faire des suggestions dans l\'OPAC.\n 0 : Non.\n 1 : Oui, avec authentification.\n 2 : Oui, sans authentification.','f_modules',0),(343,'acquisition','email_sugg','0','Information par email de l\'�volution des suggestions.\n 0 : Non\n 1 : Oui','',0),(344,'acquisition','pdfliv_text_size','10','Taille de la police texte','pdfliv',0),(345,'acquisition','pdfliv_pos_date','170,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfliv',0),(346,'acquisition','pdffac_text_size','10','Taille de la police texte','pdffac',0),(347,'acquisition','pdffac_format_page','210x297','Largeur x Hauteur de la page en mm','pdffac',0),(348,'acquisition','pdffac_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdffac',0),(349,'acquisition','pdffac_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdffac',0),(350,'acquisition','pdffac_pos_raison','10,10,100,10,16','Position Raison sociale: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdffac',0),(351,'acquisition','pdffac_pos_date','170,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdffac',0),(352,'acquisition','pdffac_pos_adr_fac','10,20,60,5,10','Position Adresse de facturation: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdffac',0),(353,'acquisition','pdffac_pos_adr_fou','110,20,100,5,10','Position �l�ments fournisseur: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdffac',0),(354,'acquisition','pdffac_pos_num','10,60,0,6,14','Position num�ro Commande/Facture: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdffac',0),(355,'acquisition','pdffac_tab_fac','5,10','Table de facturation: Hauteur ligne,Taille police','pdffac',0),(356,'acquisition','pdffac_pos_tot','10,40,5,10','Position total de commande: Distance par rapport au bord gauche de la page, Largeur, Hauteur ligne,Taille police','pdffac',0),(357,'acquisition','pdffac_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdffac',0),(358,'acquisition','pdfsug_text_size','8','Taille de la police texte','pdfsug',0),(359,'acquisition','pdfsug_format_page','210x297','Largeur x Hauteur de la page en mm','pdfsug',0),(360,'acquisition','pdfsug_orient_page','P','Orientation de la page: P=Portrait, L=Paysage','pdfsug',0),(361,'acquisition','pdfsug_marges_page','10,20,10,10','Marges de page en mm : Haut,Bas,Droite,Gauche','pdfsug',0),(362,'acquisition','pdfsug_pos_titre','10,10,100,10,16','Position titre: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfsug',0),(363,'acquisition','pdfsug_pos_date','170,10,0,6,8','Position Date: Distance par rapport au bord gauche de la page,Distance par rapport au haut de la page,Largeur,Hauteur,Taille police','pdfsug',0),(364,'acquisition','pdfsug_tab_sug','5,10','Table de suggestions: Hauteur ligne,Taille police','pdfsug',0),(365,'acquisition','pdfsug_pos_footer','15,8','Position bas de page: Distance par rapport au bas de page, Taille police','pdfsug',0),(366,'acquisition','mel_rej_obj','Rejet suggestion','Objet du mail de rejet de suggestion','mel',0),(367,'acquisition','mel_rej_cor','Votre suggestion du !!date!! est rejet�e.\n\n','Corps du mail de rejet de suggestion','mel',0),(368,'acquisition','mel_con_obj','Confirmation suggestion','Objet du mail de confirmation de suggestion','mel',0),(369,'acquisition','mel_con_cor','Votre suggestion du !!date!! est retenue pour un prochain achat.\n\n','Corps du mail de confirmation de suggestion','mel',0),(370,'acquisition','mel_aba_obj','Abandon suggestion','Objet du mail d\'abandon de suggestion','mel',0),(371,'acquisition','mel_aba_cor','Votre suggestion du !!date!! n\'est pas retenue ou n\'est pas disponible � la vente.\n\n','Corps du mail d\'abandon de suggestion','mel',0),(372,'acquisition','mel_cde_obj','Commande suggestion','Objet du mail de commande de suggestion','mel',0),(373,'acquisition','mel_cde_cor','Votre suggestion du !!date!! est en commande.\n\n','Corps du mail de commande de suggestion','mel',0),(374,'acquisition','mel_rec_obj','R�ception suggestion','Objet du mail de r�ception de suggestion','mel',0),(375,'acquisition','mel_rec_cor','Votre suggestion du !!date!! a �t� re�ue et sera bient�t disponible en r�servation.\n\n','Corps du mail de r�ception de suggestion','mel',0),(376,'opac','allow_tags_search','0','Recherche par tag (mots cl�s utilisateurs) \n 1 = oui \n 0 = non','c_recherche',0),(377,'opac','allow_add_tag','0','Permettre aux utilisateurs d\'ajouter un tag � une notice.\n 0 : non\n 1 : oui\n 2 : identification obligatoire pour ajouter','a_general',0),(378,'opac','avis_allow','0','Permet de consulter/ajouter un avis pour les notices \n 0 : non \n 1 : sans �tre identifi� : consultation possible, ajout impossible \n 2 : identification obligatoire pour consulter et ajouter','a_general',0),(379,'opac','avis_nb_max','30','Nombre maximal de commentaires conserv� par notice. Les plus vieux sont effac�s au profit des plus r�cent quand ce nombre est atteint.','a_general',0),(380,'pmb','show_rtl','0','Affichage possible de droite a gauche \n 0 non \n 1 oui','',0),(381,'opac','avis_show_writer','0','Afficher le r�dacteur de l\'avis \n 0 : non \n 1 : Pr�nom NOM \n 2 : login OPAC uniquement','a_general',0),(382,'pmb','form_editables','0','Grilles de notices �ditables \n 0 non \n 1 oui','',0),(383,'acquisition','sugg_to_cde','0','Transfert des suggestions en commande.\n 0 : Non.\n 1 : Oui.','',0),(384,'categories','categ_in_line','0','Affichage des cat�gories en ligne.\n 0 : Non.\n 1 : Oui.','',0),(385,'opac','categories_categ_in_line','0','Affichage des cat�gories en ligne.\n 0 : Non.\n 1 : Oui.','i_categories',0),(386,'pmb','label_construct_script','','Script de construction d\'�tiquette de cote','',0),(387,'dsi','func_after_diff','','Script � ex�cuter apr�s diffusion d\'une bannette','',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `parametres` ENABLE KEYS */;

--
-- Table structure for table `pret`
--

DROP TABLE IF EXISTS `pret`;
CREATE TABLE `pret` (
  `pret_idempr` smallint(6) unsigned NOT NULL default '0',
  `pret_idexpl` smallint(6) unsigned NOT NULL default '0',
  `pret_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `pret_retour` date default NULL,
  `pret_arc_id` int(10) unsigned NOT NULL default '0',
  `niveau_relance` int(1) NOT NULL default '0',
  `date_relance` date default '0000-00-00',
  `printed` int(1) NOT NULL default '0',
  PRIMARY KEY  (`pret_idexpl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pret`
--


/*!40000 ALTER TABLE `pret` DISABLE KEYS */;
LOCK TABLES `pret` WRITE;
INSERT INTO `pret` VALUES (7,1,'2006-10-13 15:19:51','2006-10-27',1,0,'0000-00-00',0),(2,2,'2006-10-13 15:25:18','2006-10-27',3,0,'0000-00-00',0),(5,6,'2006-10-13 15:35:07','2006-10-27',4,0,'0000-00-00',0),(5,8,'2006-10-13 15:35:23','2006-10-27',5,0,'0000-00-00',0),(6,9,'2006-10-13 15:38:51','2006-10-27',6,0,'0000-00-00',0),(11,24,'2006-08-28 14:35:57','2006-09-11',5,0,'0000-00-00',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `pret` ENABLE KEYS */;

--
-- Table structure for table `pret_archive`
--

DROP TABLE IF EXISTS `pret_archive`;
CREATE TABLE `pret_archive` (
  `arc_id` int(10) unsigned NOT NULL auto_increment,
  `arc_debut` datetime default '0000-00-00 00:00:00',
  `arc_fin` datetime default NULL,
  `arc_empr_cp` varchar(5) default '',
  `arc_empr_ville` varchar(40) default '',
  `arc_empr_prof` varchar(50) default '',
  `arc_empr_year` int(4) unsigned default '0',
  `arc_empr_categ` smallint(5) unsigned default '0',
  `arc_empr_codestat` smallint(5) unsigned default '0',
  `arc_empr_sexe` tinyint(3) unsigned default '0',
  `arc_expl_typdoc` tinyint(3) unsigned default '0',
  `arc_expl_cote` varchar(20) NOT NULL default '',
  `arc_expl_statut` smallint(5) unsigned default '0',
  `arc_expl_location` smallint(5) unsigned default '0',
  `arc_expl_codestat` smallint(5) unsigned default '0',
  `arc_expl_owner` mediumint(8) unsigned default '0',
  `arc_expl_section` int(5) unsigned NOT NULL default '0',
  `arc_expl_id` int(10) unsigned NOT NULL default '0',
  `arc_expl_notice` int(10) unsigned NOT NULL default '0',
  `arc_expl_bulletin` int(10) unsigned NOT NULL default '0',
  `arc_groupe` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`arc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pret_archive`
--


/*!40000 ALTER TABLE `pret_archive` DISABLE KEYS */;
LOCK TABLES `pret_archive` WRITE;
INSERT INTO `pret_archive` VALUES (1,'2006-08-24 18:42:53','2006-11-08 15:50:29','856','ສີໂຄດ','ນັກຂ່າວ',5071981,10,4,1,1,'000',1,1,12,2,10,37,63,0,''),(2,'2006-08-24 18:47:30','2006-11-08 15:51:52','001','ໄຊທານີ','ນັກສິກສາ',15081987,8,4,2,1,'',1,1,12,0,10,38,60,0,''),(3,'2006-08-24 18:54:00','2006-11-08 15:54:41','001','ໄຊທານີ','ນັກສິກສາ',15081987,8,4,2,1,'',1,1,12,0,10,39,64,0,''),(4,'2006-10-13 15:35:07','2006-10-27 00:00:00','002','ນາຊາຍທອງ','ນັກຂຽນໂປແກມ',5031980,10,4,1,1,'001',1,1,10,2,10,6,2,0,''),(5,'2006-10-13 15:35:23','2006-10-27 00:00:00','002','ນາຊາຍທອງ','ນັກຂຽນໂປແກມ',5031980,10,4,1,1,'000',1,1,10,2,10,8,3,0,''),(6,'2006-10-13 15:38:51','2006-10-27 00:00:00','856','ສີໂຄດ','ນັກຂຽນໂປແກມ',7121981,10,4,1,1,'000',1,1,10,2,10,9,3,0,''),(7,'2006-10-14 08:17:42','2006-10-14 08:18:56','856','ສີສະຕະນາດ','ນັກຂຽນໂປແກມ',2101978,10,7,1,1,'009',1,1,10,2,10,15,8,0,''),(8,'2006-10-14 09:10:37','2006-10-14 09:13:35','001','ໄຊທານີ','ນັກສິກສາ',15081987,8,4,2,1,'000',1,1,10,2,10,27,25,0,''),(9,'2006-10-14 09:14:21','2006-10-16 16:59:54','856','ສີໂຄດ','ນັກຂຽນໂປແກມ',13081981,10,7,1,1,'000',1,1,10,2,10,27,25,0,''),(10,'2006-10-27 15:48:04','2006-10-27 15:51:09','001','ໄຊທານີ','ນັກສິກສາ',15081987,8,4,2,1,'010',1,1,10,2,13,29,27,0,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE `pret_archive` ENABLE KEYS */;

--
-- Table structure for table `procs`
--

DROP TABLE IF EXISTS `procs`;
CREATE TABLE `procs` (
  `idproc` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `requete` blob NOT NULL,
  `comment` tinytext NOT NULL,
  `autorisations` mediumtext,
  `parameters` text,
  PRIMARY KEY  (`idproc`),
  KEY `idproc` (`idproc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `procs`
--


/*!40000 ALTER TABLE `procs` DISABLE KEYS */;
LOCK TABLES `procs` WRITE;
INSERT INTO `procs` VALUES (1,'Liste expl/statut','select expl_cote, expl_cb, tit1 from exemplaires, notices where expl_statut=!!param1!! and expl_notice=notice_id order by expl_cote','Liste param�tr�e d\'exemplaires par statut ','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Statut]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idstatut,statut_libelle from docs_statut]]></QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[Choisissez un statut]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(2,'Comptage expl /statut','select statut_libelle from exemplaires, docs_statut, count(*) as Nbre where idstatut=expl_statut group by statut_libelle order by idstatut','Nombre d\'exemplaires par statut d\'exmplaire','1 2',NULL),(3,'Comptage expl /pr�teur','select lender_libelle, count(*) as Nbre from exemplaires, lenders where expl_owner=idlender group by lender_libelle order by lender_libelle ','Nombre d\'exemplaires par pr�teur','1 2',NULL),(4,'Comptage  expl /pr�teur /statut','select lender_libelle, idstatut, statut_libelle , count(*) as Nbre from exemplaires, lenders, docs_statut where expl_owner=idlender and expl_statut=idstatut group by lender_libelle,statut_libelle order by lender_libelle,statut_libelle ','Nombre d\'exemplaires par pr�teur et par statut d\'exmplaire','1 2',NULL),(5,'Liste expl d\'un pr�teur /statut','select lender_libelle, statut_libelle, expl_cote, expl_cb, tit1 from exemplaires, notices, docs_statut, lenders where expl_statut=!!statut!! and expl_owner=!!Proprietaire!! and expl_notice=notice_id and expl_statut=idstatut and expl_owner=idlender order by lender_libelle, statut_libelle, expl_cote, expl_cb ','Liste d\'exemplaires d\'un propri�taire par statut, cote, code-barre, titre (pratique pour lister les documents non point�s apr�s l\'import)','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"statut\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Statut]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select idstatut, statut_libelle from docs_statut</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"Proprietaire\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Proprietaire]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select idlender, lender_libelle from lenders</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(6,'Comptage expl /section','select idsection, section_libelle, count(*) as Nbre from exemplaires, docs_section where idsection=expl_section group by idsection, section_libelle order by idsection','Nombre d\'exemplaires par section','1 2',NULL),(7,'Liste expl pour une ou plusieurs sections par pr�teur','select section_libelle, expl_cote, expl_cb, tit1 from exemplaires, notices, docs_section, lenders where idsection in (!!sections!!) and expl_owner=!!preteur!! and expl_notice=notice_id and expl_section=idsection and expl_owner=idlender order by section_libelle, expl_cote, expl_cb ','Liste des exemplaires ayant une ou plusieurs sections particuli�res pour un pr�teur','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"sections\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Section(s)]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idsection, section_libelle from docs_section]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"preteur\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Pr�teur]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select idlender, lender_libelle from lenders order by idlender]]></QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[Choisissez un pr�teur]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(8,'Stat : Compte expl /propri�taire','select lender_libelle as Proprio, count(*) as Nbre from exemplaires, lenders where idlender=expl_owner group by expl_owner, lender_libelle','Nbre d\'exemplaires par propri�taire d\'exemplaire','1 2',NULL),(9,'Liste expl du fonds propre','select statut_libelle, expl_cote, expl_cb, tit1 from exemplaires, notices, docs_statut where expl_owner=0 and expl_notice=notice_id and expl_statut=idstatut order by statut_libelle, expl_cote, expl_cb ','Liste des exemplaires du fonds propre par statut, cote, code-barre, titre','1 2',NULL),(10,'Liste expl pour un pr�teur','select expl_cote, expl_cb, tit1 from exemplaires, notices, docs_statut, lenders where expl_owner=!!proprietaire!! and expl_notice=notice_id and expl_statut=idstatut and expl_owner=idlender order by  expl_cote, expl_cb ','Liste des exemplaires pour 1 propri�taire tri� par cote et code-barre','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"proprietaire\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Propri�taire]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select idlender, lender_libelle from lenders order by idlender</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\">Choisissez un pr�teur</UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(11,'Comptage lecteurs /categ','select libelle, count(*) as \'Nbre lecteurs\' from empr, empr_categ where id_categ_empr=empr_categ group by libelle order by libelle','Nombre de lecteurs par cat�gorie','1 2',NULL),(13,'Liste lecteurs /cat�gories','select libelle as Cat�gorie, empr_nom as Nom, empr_prenom as Pr�nom, empr_year as DateNaissance from empr, empr_categ where id_categ_empr=empr_categ order by libelle, empr_nom, empr_prenom','Liste des lecteurs par cat�gorie de lecteur, lecteur','1 2',NULL),(14,'Pr�ts par cat�gories','SELECT empr_categ.libelle as Cat�gorie, empr.empr_nom as Nom, empr.empr_prenom as Pr�nom, empr.empr_cb as Num�ro, exemplaires.expl_cb as CodeBarre, notices.tit1 as Titre FROM pret,empr,empr_categ,exemplaires,notices WHERE empr_categ.id_categ_empr in (!!categorie!!) and empr.empr_categ = empr_categ.id_categ_empr and pret.pret_idempr = empr.id_empr and pret.pret_idexpl = exemplaires.expl_id and exemplaires.expl_notice = notices.notice_id order by 1,2,3,6','Liste des exemplaires en pr�t pour une ou plusieurs cat�gories de lecteurs','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[categorie]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select id_categ_empr, libelle from empr_categ order by libelle]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(20,'Liste fonds propre / statut','select statut_libelle, expl_cote, expl_cb, tit1 from exemplaires, notices, docs_statut where expl_owner=0 and expl_notice=notice_id and expl_statut=idstatut order by statut_libelle, expl_cote, expl_cb ','Pointage fonds propre','1 2',NULL),(21,'Stat : Compte lecteurs /age','SELECT count(*), CASE WHEN  (!!param1!! - empr_year) <= 13 THEN \'Jusque 13 ans\' WHEN (!!param1!! - empr_year) >13 and (!!param1!! - empr_year)<=24 THEN \'14 � 24 ans\' WHEN (!!param1!! - empr_year)>24 and (!!param1!! - empr_year)<=59 THEN \'25 � 29 ans\' WHEN (!!param1!! - empr_year)>59 THEN \'60 ans et plus\'  ELSE \'erreur sur age\' END as categ_age from empr where empr_categ in (!!categorie!!) and (year(empr_date_expiration)=!!param1!! or year(empr_date_adhesion)=!!param1!!) group by categ_age','Nbre de lecteurs par tranche d\'age pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Cat�gorie]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select id_categ_empr, libelle from empr_categ order by libelle</QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(22,'Stat : Compte lecteurs /sexe /age','SELECT count(*), case when empr_sexe=\'1\' then \'Hommes\' when empr_sexe=\'2\' then \'Femmes\' else \'erreur sur sexe\' end as Sexe, CASE WHEN  (!!param1!! - empr_year) <= 13 THEN \'Jusque 13 ans\' WHEN (!!param1!! - empr_year) >13 and (!!param1!! - empr_year) <= 24 THEN \'14 � 24 ans\' WHEN (!!param1!! - empr_year) >24 and (!!param1!! - empr_year) <= 59 THEN \'25 � 59 ans\' WHEN (!!param1!! - empr_year) >59 THEN \'60 ans et plus\'  ELSE \'erreur sur age\' END as categ_age from empr where empr_categ in (!!categorie!!) and (year(empr_date_expiration)=!!param1!! or year(empr_date_adhesion)=!!param1!!) group by sexe, categ_age','Nbre de lecteurs par sexe et tranche d\'age pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Cat�gorie]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select id_categ_empr, libelle from empr_categ order by libelle</QUERY>\r\n <MULTIPLE>no</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(23,'Stat : Compte lecteurs /ville /cat�gorie','select empr_ville as Ville, count(*) as Nbre from empr where empr_categ in (!!categorie!!) and (year(empr_date_expiration)=!!annee!! or year(empr_date_adhesion)=!!annee!!) group by empr_ville order by empr_ville','Nbre de lecteurs par ville de r�sidence pour une ou plusieurs cat�gorie','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Cat�gorie]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY>select id_categ_empr, libelle from empr_categ order by libelle</QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"annee\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(24,'Stat : Compte �l�ves','SELECT count(*) as nbre_eleve from empr where empr_categ in (!!categorie!!) and and (year(empr_date_expiration)=!!annee!! or year(empr_date_adhesion)=!!annee!!)','Nbre de lecteurs \'El�ve\' = cat�gorie � s�lectionner ','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Cat�gorie de lecteurs]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select id_categ_empr, libelle from empr_categ order by libelle]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"annee\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(25,'Stat : Compte pr�ts pour �l�ve ou profs','SELECT count(*) as nbre_pret_eleve from pret_archive where arc_empr_categ in (!!categorie!!) and year(arc_debut) = \'!!param1!!\'\r\n','Nbre de pr�ts pour les �l�ves de l\'�cole ou pour les profs (pr�ts pour la classe) pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"categorie\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Cat�gorie]]></ALIAS>\n  <TYPE>query_list</TYPE>\n<OPTIONS FOR=\"query_list\">\r\n <QUERY><![CDATA[select id_categ_empr, libelle from empr_categ order by libelle]]></QUERY>\r\n <MULTIPLE>yes</MULTIPLE>\r\n <UNSELECT_ITEM VALUE=\"\"><![CDATA[]]></UNSELECT_ITEM>\r\n</OPTIONS>\n </FIELD>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS>\n </FIELD>\n</FIELDS>'),(26,'Stat : Compte pr�ts Documentaires E','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_pret_Docu_E FROM pret_archive where (left (arc_expl_cote,2)=\'E \' or left (arc_expl_cote,3)=\'EB \' or left (arc_expl_cote,2)=\'E.\')and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre de pr�ts de documentaires Enfants pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(27,'Stat : Compte pr�ts Fictions E','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_prets_fiction_E FROM pret_archive where (left (arc_expl_cote,3)=\'EA \' or left (arc_expl_cote,3)=\'EBD\' or left (arc_expl_cote,3)=\'EC \' or left (arc_expl_cote,3)=\'ER \') and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre de pr�ts de fictions Enfants pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(28,'Stat : Compte pr�ts Fictions A','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_prets_fiction_A FROM pret_archive where (left (arc_expl_cote,1)=\'R\' or left (arc_expl_cote,3)=\'BD \' or left (arc_expl_cote,2)=\'JR\' or left (arc_expl_cote,3)=\'JBD\') and left (arc_expl_cote,3)<>\'RE \' and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre de pr�ts de fictions Jeunes ou Adultes pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(29,'Stat : Compte pr�ts Documentaires A & J','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_prets_Docu_A FROM pret_archive where (left (arc_expl_cote,2)=\'H \' or left (arc_expl_cote,2)=\'B \' or left (arc_expl_cote,3)=\'FR \' or left (arc_expl_cote,2)=\'J \' or left (arc_expl_cote,2)=\'J.\' or left(arc_expl_cote,1) between \'0\' and \'9\') and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre de pr�ts de documentaires Jeunes ou Adultes pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(30,'Stat : Compte pr�ts TOTAL (hors P�rio)','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_prets_TOTAL FROM pret_archive where arc_expl_cote not like \'P %\' and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre total de pr�ts hors p�riodiques pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>'),(31,'Stat : Compte pr�ts P�riodiques','SELECT year(arc_debut) as annee, month (arc_debut) as mois, count(*) nb_prets_TOTAL FROM pret_archive where arc_expl_cote like \'P %\' and year(arc_debut) = \'!!param1!!\' group by annee, mois order by annee, mois','Nbre de pr�ts de p�riodiques pour une ann�e','1 2','<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<FIELDS>\n <FIELD NAME=\"param1\" MANDATORY=\"yes\">\n  <ALIAS><![CDATA[Ann�e de calcul]]></ALIAS>\n  <TYPE>text</TYPE>\n<OPTIONS FOR=\"text\">\r\n <SIZE>5</SIZE>\r\n <MAXSIZE>4</MAXSIZE>\r\n</OPTIONS> \n </FIELD>\n</FIELDS>');
UNLOCK TABLES;
/*!40000 ALTER TABLE `procs` ENABLE KEYS */;

--
-- Table structure for table `publishers`
--

DROP TABLE IF EXISTS `publishers`;
CREATE TABLE `publishers` (
  `ed_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ed_name` varchar(255) NOT NULL default '',
  `ed_adr1` varchar(255) NOT NULL default '',
  `ed_adr2` varchar(255) NOT NULL default '',
  `ed_cp` varchar(10) NOT NULL default '',
  `ed_ville` varchar(96) NOT NULL default '',
  `ed_pays` varchar(96) NOT NULL default '',
  `ed_web` varchar(255) NOT NULL default '',
  `index_publisher` text,
  `ed_comment` text,
  PRIMARY KEY  (`ed_id`),
  KEY `ed_name` (`ed_name`),
  KEY `ed_ville` (`ed_ville`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `publishers`
--


/*!40000 ALTER TABLE `publishers` DISABLE KEYS */;
LOCK TABLES `publishers` WRITE;
INSERT INTO `publishers` VALUES (1,'ຫົ່ງກ່າລີ້ ມ່າຍ','909 third Avenue','Newyork NY 10022','01','New York','ອາເມລິກາ','www.hungryminds.com',' ຫົ່ງກ່າລີ້ ມ່າຍ ',''),(2,'ໂຮງພິມສຶກສາ','ກຳແພງນະຄອນ','','123','ສີສະຕະນາດ','ລາວ','',' ໂຮງພິມສຶກສາ ',''),(3,'ໂຮງພິມມັນທາຕຸລາດ','ກຳແພງນະຄອນ','','125','ສີໂຄດຕະບອງ','ລາວ','',' ໂຮງພິມມັນທາຕຸລາດ ',''),(4,'ໂຮງພິມດາວວິໄລ','ກຳແພງນະຄອນ','','1024','ສັງທອງ','ລາວ','',' ໂຮງພິມດາວວິໄລ ',''),(5,'ອະດິດ','ຫຼວງພະບາງ','','','ຫຼວງພະບາງ','ລາວ','',' ອະດິດ ',''),(6,'ສະຖາບັນ','ກຳແພງນະຄອນ','','','ກຳແພງນະຄອນ','ລາວ','',' ສະຖາບັນ ',''),(7,'ຫໍພິພິທະພັນ','ກຳແພງນະຄອນ','','','ກຳແພງນະຄອນ','ລາວ','',' ຫໍພິພິທະພັນ ',''),(8,'ນະຄອນຫລວງ','ກຳແພງນະຄອນ','','','ກຳແພງນະຄອນ','ລາວ','',' ນະຄອນຫລວງ ',''),(9,'ໂຮງພິມແຫ່ງລັດ','ກຳແພງນະຄອນ','','','ສີໂຄດຕະບອງ','ສປປລາວ','',' ໂຮງພິມແຫ່ງລັດ ',''),(10,'ໂຮງພິມມັນທາຕຸລາດ','','','','ກຳແພງນະຄອນ','ລາວ','',' ໂຮງພິມມັນທາຕຸລາດ ',''),(11,'ສູນຝຶກປ່າໄມ້','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ສູນຝຶກປ່າໄມ້ ',''),(12,'ການປົກຄອງ','','','','ກຳແພງນະຄອນ','ລາວ','',' ການປົກຄອງ ',''),(13,'ສີສະຫວາດການພິມ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ສີສະຫວາດການພິມ ',''),(14,'ອົງການອະນາໄມໂລກ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ອົງການອະນາໄມໂລກ ',''),(15,'ມູນນິທິຊາຊາກາວາ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ມູນນິທິຊາຊາກາວາ ',''),(16,'ປາກປາສັກການພິມ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ປາກປາສັກການພິມ ',''),(17,'ກຸງເທບ','','','','ກຸງເທບ','ໄທ','',' ກຸງເທບ ',''),(18,'ຂອນແກ່ນ','','','','ຂອນແກ່ນ','ໄທ','',' ຂອນແກ່ນ ',''),(19,'ສະພານທອງການພິມ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ສະພານທອງການພິມ ',''),(20,'ສຳນັກພິມແລະຈຳໜ່າຍປືມ','','','','ກຳແພງນະຄອນ','ສປປລາວ','',' ສຳນັກພິມແລະຈຳໜ່າຍປືມ ','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `publishers` ENABLE KEYS */;

--
-- Table structure for table `quotas`
--

DROP TABLE IF EXISTS `quotas`;
CREATE TABLE `quotas` (
  `quota_type` int(10) unsigned NOT NULL default '0',
  `constraint_type` varchar(255) NOT NULL default '',
  `elements` int(10) unsigned NOT NULL default '0',
  `value` float default NULL,
  PRIMARY KEY  (`quota_type`,`constraint_type`,`elements`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quotas`
--


/*!40000 ALTER TABLE `quotas` DISABLE KEYS */;
LOCK TABLES `quotas` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `quotas` ENABLE KEYS */;

--
-- Table structure for table `quotas_finance`
--

DROP TABLE IF EXISTS `quotas_finance`;
CREATE TABLE `quotas_finance` (
  `quota_type` int(10) unsigned NOT NULL default '0',
  `constraint_type` varchar(255) NOT NULL default '',
  `elements` int(10) unsigned NOT NULL default '0',
  `value` float default NULL,
  PRIMARY KEY  (`quota_type`,`constraint_type`,`elements`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quotas_finance`
--


/*!40000 ALTER TABLE `quotas_finance` DISABLE KEYS */;
LOCK TABLES `quotas_finance` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `quotas_finance` ENABLE KEYS */;

--
-- Table structure for table `recouvrements`
--

DROP TABLE IF EXISTS `recouvrements`;
CREATE TABLE `recouvrements` (
  `recouvr_id` int(16) unsigned NOT NULL auto_increment,
  `empr_id` int(10) unsigned NOT NULL default '0',
  `id_expl` int(10) unsigned NOT NULL default '0',
  `date_rec` date NOT NULL default '0000-00-00',
  `libelle` varchar(255) default NULL,
  `montant` decimal(16,2) default '0.00',
  PRIMARY KEY  (`recouvr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `recouvrements`
--


/*!40000 ALTER TABLE `recouvrements` DISABLE KEYS */;
LOCK TABLES `recouvrements` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `recouvrements` ENABLE KEYS */;

--
-- Table structure for table `resa`
--

DROP TABLE IF EXISTS `resa`;
CREATE TABLE `resa` (
  `id_resa` mediumint(8) unsigned NOT NULL auto_increment,
  `resa_idempr` mediumint(8) unsigned NOT NULL default '0',
  `resa_idnotice` mediumint(8) unsigned NOT NULL default '0',
  `resa_idbulletin` int(8) unsigned NOT NULL default '0',
  `resa_date` datetime default NULL,
  `resa_date_debut` date NOT NULL default '0000-00-00',
  `resa_date_fin` date NOT NULL default '0000-00-00',
  `resa_cb` varchar(14) NOT NULL default '',
  `resa_confirmee` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_resa`),
  KEY `resa_date_fin` (`resa_date_fin`),
  KEY `resa_date` (`resa_date`),
  KEY `resa_cb` (`resa_cb`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resa`
--


/*!40000 ALTER TABLE `resa` DISABLE KEYS */;
LOCK TABLES `resa` WRITE;
INSERT INTO `resa` VALUES (3,4,3,0,'2006-10-14 09:39:39','0000-00-00','0000-00-00','',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `resa` ENABLE KEYS */;

--
-- Table structure for table `resa_ranger`
--

DROP TABLE IF EXISTS `resa_ranger`;
CREATE TABLE `resa_ranger` (
  `resa_cb` varchar(14) NOT NULL default '',
  PRIMARY KEY  (`resa_cb`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resa_ranger`
--


/*!40000 ALTER TABLE `resa_ranger` DISABLE KEYS */;
LOCK TABLES `resa_ranger` WRITE;
INSERT INTO `resa_ranger` VALUES ('PE38');
UNLOCK TABLES;
/*!40000 ALTER TABLE `resa_ranger` ENABLE KEYS */;

--
-- Table structure for table `responsability`
--

DROP TABLE IF EXISTS `responsability`;
CREATE TABLE `responsability` (
  `responsability_author` mediumint(8) unsigned NOT NULL default '0',
  `responsability_notice` mediumint(8) unsigned NOT NULL default '0',
  `responsability_fonction` char(3) NOT NULL default '',
  `responsability_type` mediumint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`responsability_author`,`responsability_notice`,`responsability_fonction`),
  KEY `responsability_notice` (`responsability_notice`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `responsability`
--


/*!40000 ALTER TABLE `responsability` DISABLE KEYS */;
LOCK TABLES `responsability` WRITE;
INSERT INTO `responsability` VALUES (1,1,'070',0),(2,2,'070',0),(3,3,'070',0),(5,4,'070',0),(6,5,'070',0),(10,6,'070',0),(14,7,'070',0),(20,8,'070',0),(8,9,'070',0),(9,10,'070',0),(13,11,'070',0),(6,12,'070',0),(17,13,'070',0),(20,14,'070',0),(1,15,'070',0),(9,16,'070',0),(10,17,'070',0),(2,18,'070',0),(18,19,'070',0),(3,20,'070',0),(20,21,'070',0),(19,22,'070',0),(21,23,'070',0),(13,24,'070',0),(4,4,'070',0),(5,5,'070',0),(6,6,'070',0),(8,7,'068',2),(7,7,'070',0),(9,8,'070',0),(9,9,'070',0),(10,11,'070',0),(12,12,'440',1),(11,12,'070',0),(13,13,'070',0),(15,14,'044',2),(14,14,'340',2),(17,15,'007',1),(16,15,'070',0),(18,16,'070',0),(19,17,'650',0),(20,18,'061',2),(21,18,'017',2),(22,18,'017',2),(23,18,'017',2),(26,19,'070',2),(27,19,'',1),(25,19,'070',1),(24,19,'723',0),(28,42,'720',0),(30,44,'370',0),(32,46,'545',2),(31,46,'250',0),(33,48,'705',0),(34,49,'180',0),(35,50,'070',0),(36,51,'070',0),(38,53,'068',2),(37,53,'070',0),(39,54,'070',0),(40,57,'070',0),(41,57,'007',2),(63,60,'070',0),(63,59,'070',0),(62,63,'070',0),(60,65,'070',0),(61,61,'070',0),(62,64,'070',0),(20,27,'160',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `responsability` ENABLE KEYS */;

--
-- Table structure for table `rss_content`
--

DROP TABLE IF EXISTS `rss_content`;
CREATE TABLE `rss_content` (
  `rss_id` int(10) unsigned NOT NULL default '0',
  `rss_content` longblob NOT NULL,
  `rss_last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`rss_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rss_content`
--


/*!40000 ALTER TABLE `rss_content` DISABLE KEYS */;
LOCK TABLES `rss_content` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `rss_content` ENABLE KEYS */;

--
-- Table structure for table `rss_flux`
--

DROP TABLE IF EXISTS `rss_flux`;
CREATE TABLE `rss_flux` (
  `id_rss_flux` int(9) unsigned NOT NULL auto_increment,
  `nom_rss_flux` varchar(255) NOT NULL default '',
  `link_rss_flux` blob NOT NULL,
  `descr_rss_flux` blob NOT NULL,
  `lang_rss_flux` varchar(255) NOT NULL default 'fr',
  `copy_rss_flux` blob NOT NULL,
  `editor_rss_flux` varchar(255) NOT NULL default '',
  `webmaster_rss_flux` varchar(255) NOT NULL default '',
  `ttl_rss_flux` int(9) unsigned NOT NULL default '60',
  `img_url_rss_flux` blob NOT NULL,
  `img_title_rss_flux` blob NOT NULL,
  `img_link_rss_flux` blob NOT NULL,
  `format_flux` blob NOT NULL,
  PRIMARY KEY  (`id_rss_flux`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rss_flux`
--


/*!40000 ALTER TABLE `rss_flux` DISABLE KEYS */;
LOCK TABLES `rss_flux` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `rss_flux` ENABLE KEYS */;

--
-- Table structure for table `rss_flux_content`
--

DROP TABLE IF EXISTS `rss_flux_content`;
CREATE TABLE `rss_flux_content` (
  `num_rss_flux` int(9) unsigned NOT NULL default '0',
  `type_contenant` char(3) NOT NULL default 'BAN',
  `num_contenant` int(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`num_rss_flux`,`type_contenant`,`num_contenant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rss_flux_content`
--


/*!40000 ALTER TABLE `rss_flux_content` DISABLE KEYS */;
LOCK TABLES `rss_flux_content` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `rss_flux_content` ENABLE KEYS */;

--
-- Table structure for table `rubriques`
--

DROP TABLE IF EXISTS `rubriques`;
CREATE TABLE `rubriques` (
  `id_rubrique` int(8) unsigned NOT NULL auto_increment,
  `num_budget` int(8) unsigned NOT NULL default '0',
  `num_parent` int(8) unsigned NOT NULL default '0',
  `libelle` varchar(255) NOT NULL default '',
  `commentaires` text NOT NULL,
  `montant` float(8,2) unsigned NOT NULL default '0.00',
  `num_cp_compta` varchar(255) NOT NULL default '',
  `autorisations` mediumtext NOT NULL,
  PRIMARY KEY  (`id_rubrique`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rubriques`
--


/*!40000 ALTER TABLE `rubriques` DISABLE KEYS */;
LOCK TABLES `rubriques` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `rubriques` ENABLE KEYS */;

--
-- Table structure for table `sauv_lieux`
--

DROP TABLE IF EXISTS `sauv_lieux`;
CREATE TABLE `sauv_lieux` (
  `sauv_lieu_id` int(10) unsigned NOT NULL auto_increment,
  `sauv_lieu_nom` varchar(50) default NULL,
  `sauv_lieu_url` varchar(255) default NULL,
  `sauv_lieu_protocol` varchar(10) default 'file',
  `sauv_lieu_host` varchar(255) default NULL,
  `sauv_lieu_login` varchar(20) default NULL,
  `sauv_lieu_password` varchar(20) default NULL,
  PRIMARY KEY  (`sauv_lieu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sauv_lieux`
--


/*!40000 ALTER TABLE `sauv_lieux` DISABLE KEYS */;
LOCK TABLES `sauv_lieux` WRITE;
INSERT INTO `sauv_lieux` VALUES (1,'sauvegarde','d:\\temp\\','file','','','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `sauv_lieux` ENABLE KEYS */;

--
-- Table structure for table `sauv_log`
--

DROP TABLE IF EXISTS `sauv_log`;
CREATE TABLE `sauv_log` (
  `sauv_log_id` int(10) unsigned NOT NULL auto_increment,
  `sauv_log_start_date` date default NULL,
  `sauv_log_file` varchar(255) default NULL,
  `sauv_log_succeed` int(11) default '0',
  `sauv_log_messages` mediumtext,
  `sauv_log_userid` int(11) default NULL,
  PRIMARY KEY  (`sauv_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sauv_log`
--


/*!40000 ALTER TABLE `sauv_log` DISABLE KEYS */;
LOCK TABLES `sauv_log` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sauv_log` ENABLE KEYS */;

--
-- Table structure for table `sauv_sauvegardes`
--

DROP TABLE IF EXISTS `sauv_sauvegardes`;
CREATE TABLE `sauv_sauvegardes` (
  `sauv_sauvegarde_id` int(10) unsigned NOT NULL auto_increment,
  `sauv_sauvegarde_nom` varchar(50) default NULL,
  `sauv_sauvegarde_file_prefix` varchar(20) default NULL,
  `sauv_sauvegarde_tables` mediumtext,
  `sauv_sauvegarde_lieux` mediumtext,
  `sauv_sauvegarde_users` mediumtext,
  `sauv_sauvegarde_compress` int(11) default '0',
  `sauv_sauvegarde_compress_command` mediumtext,
  `sauv_sauvegarde_crypt` int(11) default '0',
  `sauv_sauvegarde_key1` varchar(32) default NULL,
  `sauv_sauvegarde_key2` varchar(32) default NULL,
  PRIMARY KEY  (`sauv_sauvegarde_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sauv_sauvegardes`
--


/*!40000 ALTER TABLE `sauv_sauvegardes` DISABLE KEYS */;
LOCK TABLES `sauv_sauvegardes` WRITE;
INSERT INTO `sauv_sauvegardes` VALUES (1,'tout','bibli','7','','1,3',0,'internal::',0,'',''),(2,'notice','bibli','5','','1',0,'internal::',0,'','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `sauv_sauvegardes` ENABLE KEYS */;

--
-- Table structure for table `sauv_tables`
--

DROP TABLE IF EXISTS `sauv_tables`;
CREATE TABLE `sauv_tables` (
  `sauv_table_id` int(10) unsigned NOT NULL auto_increment,
  `sauv_table_nom` varchar(50) default NULL,
  `sauv_table_tables` text,
  PRIMARY KEY  (`sauv_table_id`),
  UNIQUE KEY `sauv_table_nom` (`sauv_table_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sauv_tables`
--


/*!40000 ALTER TABLE `sauv_tables` DISABLE KEYS */;
LOCK TABLES `sauv_tables` WRITE;
INSERT INTO `sauv_tables` VALUES (1,'Biblio','analysis,bulletins,docs_codestat,docs_location,docs_section,docs_statut,docs_type,exemplaires,notices,etagere_caddie,notices_custom,notices_custom_lists,notices_custom_values'),(2,'Autorit�s','authors,categories,collections,noeuds,publishers,responsability,series,sub_collections,thesaurus,voir_aussi'),(3,'Aucune utilit�','error_log,import_marc,old_categories,old_notices_categories,sessions'),(4,'Z3950','z_attr,z_bib,z_notices,z_query'),(5,'Emprunteurs','empr,empr_categ,empr_codestat,empr_custom,empr_custom_lists,empr_custom_values,empr_groupe,expl_custom_values,groupe,pret,pret_archive,resa'),(6,'Application','categories,lenders,parametres,procs,sauv_lieux,sauv_log,sauv_sauvegardes,sauv_tables,users,explnum,indexint,notices_categories,origine_notice,quotas,etagere,resa_ranger,admin_session,opac_sessions,audit,notice_statut,ouvertures'),(7,'TOUT','actes,admin_session,analysis,audit,authors,bannette_abon,bannette_contenu,bannette_equation,bannette_exports,bannettes,budgets,bulletins,caddie,caddie_content,caddie_procs,categories,classements,collections,comptes,coordonnees,docs_codestat,docs_location,docs_section,docs_statut,docs_type,docsloc_section,empr,empr_categ,empr_codestat,empr_custom,empr_custom_lists,empr_custom_values,empr_groupe,entites,equations,error_log,etagere,etagere_caddie,exemplaires,exercices,expl_custom,expl_custom_lists,expl_custom_values,explnum,frais,groupe,import_marc,indexint,lenders,liens_actes,lignes_actes,noeuds,notice_statut,notices,notices_categories,notices_custom,notices_custom_lists,notices_custom_values,notices_global_index,offres_remises,opac_sessions,origine_notice,ouvertures,paiements,parametres,pret,pret_archive,procs,publishers,quotas,quotas_finance,recouvrements,resa,resa_ranger,responsability,rss_content,rss_flux,rss_flux_content,rubriques,sauv_lieux,sauv_log,sauv_sauvegardes,sauv_tables,series,sessions,sub_collections,suggestions,suggestions_origine,thesaurus,transactions,tva_achats,type_abts,type_comptes,types_produits,users,voir_aussi,z_attr,z_bib,z_notices,z_query'),(9,'Caddies','caddie_procs,caddie,caddie_content'),(10,'DSI','bannette_abon,bannette_contenu,bannette_equation,bannettes,classements,equations,rss_content,rss_flux,rss_flux_content'),(11,'Finance','comptes,quotas_finance,recouvrements,transactions,type_abts,type_comptes'),(12,'',NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `sauv_tables` ENABLE KEYS */;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
CREATE TABLE `series` (
  `serie_id` mediumint(8) unsigned NOT NULL auto_increment,
  `serie_name` varchar(255) NOT NULL default '',
  `serie_index` text,
  PRIMARY KEY  (`serie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `series`
--


/*!40000 ALTER TABLE `series` DISABLE KEYS */;
LOCK TABLES `series` WRITE;
INSERT INTO `series` VALUES (1,'Dayak',' dayak '),(2,'Le pithécantrope dans la valise',' pithecantrope dans valise '),(3,'Mange-coeur',' mange coeur '),(4,'Jojo',' jojo '),(5,'à»?àº?à»‰àº§','  ');
UNLOCK TABLES;
/*!40000 ALTER TABLE `series` ENABLE KEYS */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `SESSID` varchar(12) NOT NULL default '',
  `login` varchar(20) NOT NULL default '',
  `IP` varchar(20) NOT NULL default '',
  `SESSstart` varchar(12) NOT NULL default '',
  `LastOn` varchar(12) NOT NULL default '',
  `SESSNAME` varchar(25) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sessions`
--


/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
LOCK TABLES `sessions` WRITE;
INSERT INTO `sessions` VALUES ('1179204990','admin','127.0.0.1','1163749428','1163753279','PhpMyBibli'),('1216318863','admin','127.0.0.1','1163669698','1163670482','PhpMyBibli');
UNLOCK TABLES;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

--
-- Table structure for table `sub_collections`
--

DROP TABLE IF EXISTS `sub_collections`;
CREATE TABLE `sub_collections` (
  `sub_coll_id` mediumint(8) unsigned NOT NULL auto_increment,
  `sub_coll_name` varchar(255) NOT NULL default '',
  `sub_coll_parent` mediumint(9) unsigned NOT NULL default '0',
  `sub_coll_issn` varchar(12) NOT NULL default '',
  `index_sub_coll` text,
  PRIMARY KEY  (`sub_coll_id`),
  KEY `sub_coll_name` (`sub_coll_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sub_collections`
--


/*!40000 ALTER TABLE `sub_collections` DISABLE KEYS */;
LOCK TABLES `sub_collections` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sub_collections` ENABLE KEYS */;

--
-- Table structure for table `suggestions`
--

DROP TABLE IF EXISTS `suggestions`;
CREATE TABLE `suggestions` (
  `id_suggestion` int(12) unsigned NOT NULL auto_increment,
  `titre` tinytext NOT NULL,
  `editeur` varchar(255) NOT NULL default '',
  `auteur` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `prix` float(8,2) unsigned NOT NULL default '0.00',
  `commentaires` text,
  `statut` int(3) unsigned NOT NULL default '0',
  `num_produit` int(8) NOT NULL default '0',
  `num_entite` int(5) NOT NULL default '0',
  `index_suggestion` text NOT NULL,
  `nb` int(5) unsigned NOT NULL default '1',
  `date_creation` date NOT NULL default '0000-00-00',
  `date_decision` date NOT NULL default '0000-00-00',
  `num_rubrique` int(8) unsigned NOT NULL default '0',
  `num_fournisseur` int(5) unsigned NOT NULL default '0',
  `num_notice` int(8) unsigned NOT NULL default '0',
  `url_suggestion` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_suggestion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `suggestions`
--


/*!40000 ALTER TABLE `suggestions` DISABLE KEYS */;
LOCK TABLES `suggestions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `suggestions` ENABLE KEYS */;

--
-- Table structure for table `suggestions_origine`
--

DROP TABLE IF EXISTS `suggestions_origine`;
CREATE TABLE `suggestions_origine` (
  `origine` varchar(100) NOT NULL default '',
  `num_suggestion` int(12) unsigned NOT NULL default '0',
  `type_origine` int(3) unsigned NOT NULL default '0',
  `date_suggestion` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`origine`,`num_suggestion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `suggestions_origine`
--


/*!40000 ALTER TABLE `suggestions_origine` DISABLE KEYS */;
LOCK TABLES `suggestions_origine` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `suggestions_origine` ENABLE KEYS */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id_tag` mediumint(8) NOT NULL auto_increment,
  `libelle` varchar(200) NOT NULL default '',
  `num_notice` mediumint(8) NOT NULL default '0',
  `user_code` varchar(50) NOT NULL default '',
  `dateajout` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--


/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
LOCK TABLES `tags` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;

--
-- Table structure for table `thesaurus`
--

DROP TABLE IF EXISTS `thesaurus`;
CREATE TABLE `thesaurus` (
  `id_thesaurus` int(3) unsigned NOT NULL auto_increment,
  `libelle_thesaurus` varchar(255) NOT NULL default '',
  `langue_defaut` varchar(5) NOT NULL default 'fr_FR',
  `active` char(1) NOT NULL default '1',
  `opac_active` char(1) NOT NULL default '1',
  `num_noeud_racine` int(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_thesaurus`),
  UNIQUE KEY `libelle_thesaurus` (`libelle_thesaurus`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `thesaurus`
--


/*!40000 ALTER TABLE `thesaurus` DISABLE KEYS */;
LOCK TABLES `thesaurus` WRITE;
INSERT INTO `thesaurus` VALUES (1,'Agneaux','fr_FR','1','1',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `thesaurus` ENABLE KEYS */;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id_transaction` int(10) unsigned NOT NULL auto_increment,
  `compte_id` int(8) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_name` varchar(255) NOT NULL default '',
  `machine` varchar(255) NOT NULL default '',
  `date_enrgt` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_prevue` date default NULL,
  `date_effective` date default NULL,
  `montant` decimal(16,2) NOT NULL default '0.00',
  `sens` int(1) NOT NULL default '0',
  `realisee` int(1) NOT NULL default '0',
  `commentaire` text,
  `encaissement` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id_transaction`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--


/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
LOCK TABLES `transactions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;

--
-- Table structure for table `tva_achats`
--

DROP TABLE IF EXISTS `tva_achats`;
CREATE TABLE `tva_achats` (
  `id_tva` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `taux_tva` float(4,2) unsigned NOT NULL default '0.00',
  `num_cp_compta` varchar(25) NOT NULL default '0',
  PRIMARY KEY  (`id_tva`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tva_achats`
--


/*!40000 ALTER TABLE `tva_achats` DISABLE KEYS */;
LOCK TABLES `tva_achats` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tva_achats` ENABLE KEYS */;

--
-- Table structure for table `type_abts`
--

DROP TABLE IF EXISTS `type_abts`;
CREATE TABLE `type_abts` (
  `id_type_abt` int(5) unsigned NOT NULL auto_increment,
  `type_abt_libelle` varchar(255) default NULL,
  `prepay` int(1) unsigned NOT NULL default '0',
  `prepay_deflt_mnt` decimal(16,2) NOT NULL default '0.00',
  `tarif` decimal(16,2) NOT NULL default '0.00',
  `commentaire` text NOT NULL,
  `caution` decimal(16,2) NOT NULL default '0.00',
  `localisations` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_type_abt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `type_abts`
--


/*!40000 ALTER TABLE `type_abts` DISABLE KEYS */;
LOCK TABLES `type_abts` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `type_abts` ENABLE KEYS */;

--
-- Table structure for table `type_comptes`
--

DROP TABLE IF EXISTS `type_comptes`;
CREATE TABLE `type_comptes` (
  `id_type_compte` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `type_acces` int(8) unsigned NOT NULL default '0',
  `acces_id` text NOT NULL,
  PRIMARY KEY  (`id_type_compte`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `type_comptes`
--


/*!40000 ALTER TABLE `type_comptes` DISABLE KEYS */;
LOCK TABLES `type_comptes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `type_comptes` ENABLE KEYS */;

--
-- Table structure for table `types_produits`
--

DROP TABLE IF EXISTS `types_produits`;
CREATE TABLE `types_produits` (
  `id_produit` int(8) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL default '',
  `num_cp_compta` varchar(25) NOT NULL default '0',
  `num_tva_achat` varchar(25) NOT NULL default '0',
  PRIMARY KEY  (`id_produit`),
  KEY `libelle` (`libelle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `types_produits`
--


/*!40000 ALTER TABLE `types_produits` DISABLE KEYS */;
LOCK TABLES `types_produits` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `types_produits` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userid` int(5) NOT NULL auto_increment,
  `create_dt` date NOT NULL default '0000-00-00',
  `last_updated_dt` date NOT NULL default '0000-00-00',
  `username` varchar(20) NOT NULL default '',
  `pwd` varchar(50) NOT NULL default '',
  `nom` varchar(30) NOT NULL default '',
  `prenom` varchar(30) default NULL,
  `rights` int(8) unsigned NOT NULL default '0',
  `user_lang` varchar(5) NOT NULL default 'fr_FR',
  `nb_per_page_search` int(10) unsigned NOT NULL default '4',
  `nb_per_page_select` int(10) unsigned NOT NULL default '10',
  `nb_per_page_gestion` int(10) unsigned NOT NULL default '20',
  `param_popup_ticket` smallint(1) unsigned NOT NULL default '0',
  `param_sounds` smallint(1) unsigned NOT NULL default '1',
  `param_licence` int(1) unsigned NOT NULL default '0',
  `deflt_notice_statut` int(6) unsigned NOT NULL default '1',
  `deflt_docs_type` int(6) unsigned NOT NULL default '1',
  `deflt_lenders` int(6) unsigned NOT NULL default '0',
  `deflt_styles` varchar(20) NOT NULL default 'default',
  `deflt_docs_statut` int(6) unsigned default '0',
  `deflt_docs_codestat` int(6) unsigned default '0',
  `value_deflt_lang` varchar(20) default 'fre',
  `value_deflt_fonction` varchar(20) default '070',
  `deflt_docs_location` int(6) unsigned default '0',
  `deflt_docs_section` int(6) unsigned default '0',
  `value_deflt_module` varchar(30) default 'circu',
  `user_email` varchar(255) default '',
  `user_alert_resamail` int(1) unsigned NOT NULL default '0',
  `deflt2docs_location` int(6) unsigned NOT NULL default '0',
  `deflt_thesaurus` int(3) unsigned NOT NULL default '1',
  `value_prefix_cote` tinyblob NOT NULL,
  `xmlta_doctype` char(2) NOT NULL default 'a',
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--


/*!40000 ALTER TABLE `users` DISABLE KEYS */;
LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (1,'2002-07-28','2006-11-15','admin','43e9a4ab75570f5b','Super User','',255,'la_LA',20,10,20,0,1,1,1,1,2,'couleurs_onglets',1,10,'lao','070',1,10,'admin','pmb@sigb.net',1,1,1,'','a'),(2,'2004-01-21','2006-10-16','circ','3f3df3af7d72f2fb','Agent de prêt','',1,'fr_FR',10,10,20,0,1,0,1,1,1,'vert_et_parme',1,10,'fre','070',1,13,'circu','',0,1,1,'','a'),(3,'2004-01-21','2006-10-16','cat','7b4ed80e2270250a','Bibliothècaire-adjoint','',7,'fr_FR',10,10,20,0,1,0,1,1,1,'default',1,10,'fre','070',1,13,'catal','',0,1,1,'','a'),(4,'2004-01-21','2006-10-16','bib','7c99ea71225fa75a','Bibliothècaire','',23,'fr_FR',10,10,20,0,1,0,1,1,1,'default',13,12,'fre','070',7,13,'circu','',0,1,1,'','a');
UNLOCK TABLES;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Table structure for table `voir_aussi`
--

DROP TABLE IF EXISTS `voir_aussi`;
CREATE TABLE `voir_aussi` (
  `num_noeud_orig` int(9) unsigned NOT NULL default '0',
  `num_noeud_dest` int(9) unsigned NOT NULL default '0',
  `langue` varchar(5) NOT NULL default '',
  `comment_voir_aussi` text NOT NULL,
  PRIMARY KEY  (`num_noeud_orig`,`num_noeud_dest`,`langue`),
  KEY `num_noeud_dest` (`num_noeud_dest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `voir_aussi`
--


/*!40000 ALTER TABLE `voir_aussi` DISABLE KEYS */;
LOCK TABLES `voir_aussi` WRITE;
INSERT INTO `voir_aussi` VALUES (1390,1602,'fr_FR',''),(1391,1599,'fr_FR',''),(1392,1600,'fr_FR',''),(1394,2166,'fr_FR',''),(1395,1596,'fr_FR',''),(1398,1597,'fr_FR',''),(1399,1592,'fr_FR',''),(1400,1601,'fr_FR',''),(1401,1592,'fr_FR',''),(1411,2105,'fr_FR',''),(1413,2106,'fr_FR',''),(1414,2104,'fr_FR',''),(1415,2103,'fr_FR',''),(1416,2102,'fr_FR',''),(1417,2101,'fr_FR',''),(1431,2058,'fr_FR',''),(1435,2060,'fr_FR',''),(1545,2491,'fr_FR',''),(1553,1612,'fr_FR',''),(1563,2493,'fr_FR',''),(1592,1399,'fr_FR',''),(1592,1401,'fr_FR',''),(1595,2479,'fr_FR',''),(1596,1395,'fr_FR',''),(1597,1398,'fr_FR',''),(1598,2200,'fr_FR',''),(1599,1391,'fr_FR',''),(1600,1392,'fr_FR',''),(1601,1400,'fr_FR',''),(1602,1390,'fr_FR',''),(1607,2407,'fr_FR',''),(1612,1553,'fr_FR',''),(1623,1795,'fr_FR',''),(1623,1796,'fr_FR',''),(1628,1737,'fr_FR',''),(1670,2494,'fr_FR',''),(1672,2494,'fr_FR',''),(1726,2491,'fr_FR',''),(1729,2496,'fr_FR',''),(1737,1628,'fr_FR',''),(1760,2280,'fr_FR',''),(1795,1623,'fr_FR',''),(1796,1623,'fr_FR',''),(2057,2112,'fr_FR',''),(2058,1431,'fr_FR',''),(2060,1435,'fr_FR',''),(2101,1417,'fr_FR',''),(2102,1416,'fr_FR',''),(2103,1415,'fr_FR',''),(2104,1414,'fr_FR',''),(2105,1411,'fr_FR',''),(2106,1413,'fr_FR',''),(2112,2057,'fr_FR',''),(2166,1394,'fr_FR',''),(2184,2485,'fr_FR',''),(2184,2486,'fr_FR',''),(2200,1598,'fr_FR',''),(2280,1760,'fr_FR',''),(2407,1607,'fr_FR',''),(2467,2510,'fr_FR',''),(2479,1595,'fr_FR',''),(2485,2184,'fr_FR',''),(2486,2184,'fr_FR',''),(2490,2495,'fr_FR',''),(2491,1545,'fr_FR',''),(2491,1726,'fr_FR',''),(2491,2496,'fr_FR',''),(2491,2499,'fr_FR',''),(2491,2500,'fr_FR',''),(2492,2491,'fr_FR',''),(2493,2490,'fr_FR',''),(2493,2495,'fr_FR',''),(2494,1670,'fr_FR',''),(2494,1672,'fr_FR',''),(2494,2490,'fr_FR',''),(2495,2493,'fr_FR',''),(2496,2491,'fr_FR',''),(2496,2497,'fr_FR',''),(2497,2496,'fr_FR',''),(2499,1689,'fr_FR',''),(2499,2491,'fr_FR',''),(2499,2496,'fr_FR',''),(2500,1689,'fr_FR',''),(2500,2491,'fr_FR',''),(2500,2492,'fr_FR',''),(2502,2492,'fr_FR',''),(2504,2503,'fr_FR',''),(2507,2509,'fr_FR',''),(2508,1764,'fr_FR',''),(2509,2507,'fr_FR',''),(2510,1672,'fr_FR','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `voir_aussi` ENABLE KEYS */;

--
-- Table structure for table `z_attr`
--

DROP TABLE IF EXISTS `z_attr`;
CREATE TABLE `z_attr` (
  `attr_bib_id` int(6) unsigned NOT NULL default '0',
  `attr_libelle` varchar(250) NOT NULL default '',
  `attr_attr` varchar(250) default NULL,
  PRIMARY KEY  (`attr_bib_id`,`attr_libelle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `z_attr`
--


/*!40000 ALTER TABLE `z_attr` DISABLE KEYS */;
LOCK TABLES `z_attr` WRITE;
INSERT INTO `z_attr` VALUES (2,'sujet','21'),(2,'titre','4'),(2,'auteur','1003'),(2,'isbn','7'),(3,'sujet','21'),(3,'titre','4'),(3,'isbn','7'),(3,'auteur','1003'),(5,'auteur','1004'),(5,'titre','4'),(5,'isbn','7'),(5,'sujet','21'),(7,'isbn','7'),(7,'auteur','1003'),(7,'titre','4'),(7,'sujet','21'),(8,'auteur','1'),(8,'titre','4'),(8,'isbn','7'),(8,'sujet','21'),(8,'mots','1016'),(10,'auteur','1003'),(10,'titre','4'),(10,'isbn','7'),(10,'sujet','21'),(12,'sujet','21'),(12,'auteur','1003'),(12,'titre','4'),(12,'isbn','7'),(11,'sujet','21'),(11,'auteur','1003'),(11,'isbn','7'),(11,'titre','4'),(15,'auteur','1003'),(15,'titre','4'),(15,'isbn','7'),(15,'sujet','21'),(17,'sujet','21'),(17,'auteur','1003'),(17,'isbn','7'),(17,'titre','4'),(21,'sujet','21'),(21,'auteur','1003'),(21,'isbn','7'),(21,'titre','4');
UNLOCK TABLES;
/*!40000 ALTER TABLE `z_attr` ENABLE KEYS */;

--
-- Table structure for table `z_bib`
--

DROP TABLE IF EXISTS `z_bib`;
CREATE TABLE `z_bib` (
  `bib_id` int(6) unsigned NOT NULL auto_increment,
  `bib_nom` varchar(250) default NULL,
  `search_type` varchar(20) default NULL,
  `url` varchar(250) default NULL,
  `port` varchar(6) default NULL,
  `base` varchar(250) default NULL,
  `format` varchar(250) default NULL,
  `auth_user` varchar(250) NOT NULL default '',
  `auth_pass` varchar(250) NOT NULL default '',
  `sutrs_lang` varchar(10) NOT NULL default '',
  `fichier_func` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bib_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `z_bib`
--


/*!40000 ALTER TABLE `z_bib` DISABLE KEYS */;
LOCK TABLES `z_bib` WRITE;
INSERT INTO `z_bib` VALUES (2,'ENS Cachan','CATALOG','138.231.48.2','21210','ADVANCE','unimarc','','','',''),(3,'BN France','CATALOG','z3950.bnf.fr','2211','ABCDEFGHIJKLMNOPQRSTUVWXYZ1456','UNIMARC','Z3950','Z3950_BNF','',''),(5,'Univ Lyon 2 SCD','CATALOG','scdinf.univ-lyon2.fr','21210','ouvrages','unimarc','','','',''),(7,'Univ Oxford','CATALOG','library.ox.ac.uk','210','ADVANCE','usmarc','','','',''),(10,'Univ Laval (QC)','CATALOG','ariane2.ulaval.ca','2200','UNICORN','USMARC','','','',''),(11,'Univ Lib Edinburgh','CATALOG','catalogue.lib.ed.ac.uk','7090','voyager','USMARC','','','',''),(12,'Library Of Congress','CATALOG','z3950.loc.gov','7090','Voyager','USMARC','','','',''),(15,'ENS Paris','CATALOG','halley.ens.fr','210','INNOPAC','UNIMARC','','','',''),(17,'Polytechnique Montr�al','CATALOG','advance.biblio.polymtl.ca','210','ADVANCE','USMARC','','','',''),(21,'SUDOC','CATALOG','carmin.sudoc.abes.fr','210','ABES-Z39-PUBLIC','UNIMARC','','','',''),(8,'Univ Valenciennes','CATALOG','195.221.187.151','210','INNOPAC','UNIMARC','','','','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `z_bib` ENABLE KEYS */;

--
-- Table structure for table `z_notices`
--

DROP TABLE IF EXISTS `z_notices`;
CREATE TABLE `z_notices` (
  `znotices_id` int(11) unsigned NOT NULL auto_increment,
  `znotices_query_id` int(11) default NULL,
  `znotices_bib_id` int(6) unsigned default '0',
  `isbd` text,
  `isbn` varchar(250) default NULL,
  `titre` varchar(250) default NULL,
  `auteur` varchar(250) default NULL,
  `z_marc` longblob NOT NULL,
  PRIMARY KEY  (`znotices_id`),
  KEY `idx_z_notices_idq` (`znotices_query_id`),
  KEY `idx_z_notices_isbn` (`isbn`),
  KEY `idx_z_notices_titre` (`titre`),
  KEY `idx_z_notices_auteur` (`auteur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `z_notices`
--


/*!40000 ALTER TABLE `z_notices` DISABLE KEYS */;
LOCK TABLES `z_notices` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `z_notices` ENABLE KEYS */;

--
-- Table structure for table `z_query`
--

DROP TABLE IF EXISTS `z_query`;
CREATE TABLE `z_query` (
  `zquery_id` int(11) unsigned NOT NULL auto_increment,
  `search_attr` varchar(255) default NULL,
  `zquery_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`zquery_id`),
  KEY `zquery_date` (`zquery_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `z_query`
--


/*!40000 ALTER TABLE `z_query` DISABLE KEYS */;
LOCK TABLES `z_query` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `z_query` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

