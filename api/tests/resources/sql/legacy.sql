-- MySQL dump 10.14  Distrib 5.5.41-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: tld
-- ------------------------------------------------------
-- Server version	5.5.41-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_people_links`
--

DROP TABLE IF EXISTS `admin_people_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_people_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agr`
--

DROP TABLE IF EXISTS `agr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `acronym` varchar(10) NOT NULL,
  `desca` varchar(100) NOT NULL COMMENT 'short description',
  `descb` text NOT NULL COMMENT 'Long description',
  `url` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acronym` (`acronym`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agrl`
--

DROP TABLE IF EXISTS `agrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agrl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `airport_codes`
--

DROP TABLE IF EXISTS `airport_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `airport_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `city_code_3` varchar(3) NOT NULL COMMENT '3-Letter City Alpha Code',
  `city_name` varchar(60) NOT NULL,
  `state` varchar(60) NOT NULL,
  `ctry_code_2` varchar(2) NOT NULL COMMENT 'Country code ISO 2-letter',
  `time_zone` varchar(10) NOT NULL COMMENT 'SSIM Standard',
  `stv` varchar(1) NOT NULL,
  `airport_code` varchar(10) NOT NULL,
  `airport_name` varchar(22) NOT NULL,
  `airport_numeric` int(4) NOT NULL,
  `type` varchar(60) NOT NULL,
  `source` varchar(10) NOT NULL,
  `latitude` DOUBLE PRECISION DEFAULT NULL,
  `longitude` DOUBLE PRECISION DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ctry_code_2` (`ctry_code_2`),
  KEY `airport_code` (`airport_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11613 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_check_request`
--

DROP TABLE IF EXISTS `ap_check_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_check_request` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `t_orno` int(8) NOT NULL,
  `t_pono` int(8) NOT NULL,
  `t_dtcr` datetime NOT NULL,
  `t_usrc` varchar(50) NOT NULL,
  `t_mtcr` text NOT NULL,
  `t_dtcl` datetime NOT NULL,
  `t_usrl` varchar(50) NOT NULL,
  `t_mtcl` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_check_request_comments`
--

DROP TABLE IF EXISTS `ap_check_request_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_check_request_comments` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `parent_id` int(8) NOT NULL,
  `t_dtcm` datetime DEFAULT NULL,
  `t_usrm` varchar(50) DEFAULT NULL,
  `t_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_invoice_costs`
--

DROP TABLE IF EXISTS `ap_invoice_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_invoice_costs` (
  `t_cono` int(8) NOT NULL AUTO_INCREMENT,
  `t_inno` int(8) NOT NULL,
  `erp` int(3) NOT NULL,
  `t_type` char(20) NOT NULL,
  `t_acco` char(10) NOT NULL,
  `t_dim1` char(10) NOT NULL,
  `t_dim2` char(10) NOT NULL,
  `t_dim3` char(10) NOT NULL,
  `t_refe` char(30) NOT NULL,
  `t_amnt` float NOT NULL,
  PRIMARY KEY (`t_cono`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_invoice_header`
--

DROP TABLE IF EXISTS `ap_invoice_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_invoice_header` (
  `t_inno` int(8) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `t_suno` char(10) NOT NULL COMMENT 'Supplier',
  `t_dtcr` date NOT NULL COMMENT 'Creation date',
  `t_uscr` varchar(50) NOT NULL COMMENT 'Creation User',
  `t_indt` datetime NOT NULL,
  `t_ccur` varchar(3) NOT NULL COMMENT 'Currency',
  `t_inmt` float NOT NULL COMMENT 'Invoice Amount',
  `t_xref` varchar(50) NOT NULL COMMENT 'Supplier invoice reference',
  `t_year` int(4) NOT NULL COMMENT 'Fiscal year',
  `t_fimt` int(2) NOT NULL COMMENT 'Fiscal period',
  `t_remt` int(2) NOT NULL COMMENT 'Reporting period',
  `t_vatp` int(2) NOT NULL COMMENT 'Tax period',
  `t_ttyp` char(3) NOT NULL COMMENT 'Transaction',
  `t_invd` date NOT NULL COMMENT 'Invoice date',
  `t_vatc` char(3) NOT NULL COMMENT 'Country',
  `t_cvat` char(9) NOT NULL COMMENT 'Tax code',
  `t_cpay` char(3) NOT NULL COMMENT 'Term of payment',
  `t_paym` char(3) NOT NULL COMMENT 'Payment method',
  `t_stat` varchar(15) NOT NULL,
  `t_crea` datetime NOT NULL,
  `t_subm` datetime NOT NULL,
  `seq` int(11) NOT NULL,
  PRIMARY KEY (`t_inno`)
) ENGINE=InnoDB AUTO_INCREMENT=656 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_invoice_header_old`
--

DROP TABLE IF EXISTS `ap_invoice_header_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_invoice_header_old` (
  `t_inno` int(8) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `t_suno` char(10) NOT NULL,
  `t_orno` int(8) NOT NULL,
  `t_pono` int(8) NOT NULL,
  `t_dtcr` datetime NOT NULL,
  `t_uscr` varchar(50) NOT NULL,
  `t_indt` datetime NOT NULL,
  `t_ccur` varchar(3) NOT NULL,
  `t_inmt` float NOT NULL,
  `t_inqt` float NOT NULL,
  `t_xref` varchar(50) NOT NULL,
  PRIMARY KEY (`t_inno`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_invoice_lines`
--

DROP TABLE IF EXISTS `ap_invoice_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_invoice_lines` (
  `t_lino` int(8) NOT NULL AUTO_INCREMENT,
  `t_inno` int(8) NOT NULL,
  `erp` int(3) NOT NULL,
  `t_orno` int(8) NOT NULL,
  `t_pono` int(8) NOT NULL,
  `t_inqt` float NOT NULL,
  `t_inmt` float NOT NULL,
  PRIMARY KEY (`t_lino`)
) ENGINE=InnoDB AUTO_INCREMENT=966 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_seq_trigger`
--

DROP TABLE IF EXISTS `ap_seq_trigger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_seq_trigger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `t_type` varchar(10) NOT NULL,
  `t_rate` float NOT NULL,
  `t_valu` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp` (`erp`),
  KEY `t_type` (`t_type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `time_server` float NOT NULL,
  `time_network` float NOT NULL,
  `time_display` float NOT NULL,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bom`
--

DROP TABLE IF EXISTS `bom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` varchar(10) NOT NULL DEFAULT '',
  `t_mitm` varchar(50) NOT NULL DEFAULT '',
  `t_pono` int(50) NOT NULL DEFAULT 0,
  `t_sitm` varchar(50) NOT NULL DEFAULT '',
  `t_qana` decimal(11,3) NOT NULL DEFAULT 0.000,
  `t_cuni` varchar(10) NOT NULL DEFAULT '',
  `t_indt` date NOT NULL DEFAULT '0000-00-00',
  `t_exdt` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `mitm` (`t_mitm`),
  KEY `t_sitm` (`t_sitm`)
) ENGINE=InnoDB AUTO_INCREMENT=460948 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bomcust`
--

DROP TABLE IF EXISTS `bomcust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bomcust` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` varchar(10) NOT NULL DEFAULT '',
  `t_cprj` varchar(20) NOT NULL DEFAULT '',
  `t_pono` varchar(20) NOT NULL DEFAULT '',
  `t_sitm` varchar(50) NOT NULL DEFAULT '',
  `t_dsca` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bomedm`
--

DROP TABLE IF EXISTS `bomedm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bomedm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` varchar(10) NOT NULL DEFAULT '',
  `eitm` varchar(50) NOT NULL DEFAULT '',
  `cdrw` varchar(50) NOT NULL DEFAULT '',
  `revi` varchar(50) NOT NULL DEFAULT '',
  `bindt` date NOT NULL DEFAULT '0000-00-00',
  `bexdt` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `eitm` (`eitm`),
  KEY `bindt` (`bindt`),
  KEY `bexdt` (`bexdt`)
) ENGINE=InnoDB AUTO_INCREMENT=29705 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_bp`
--

DROP TABLE IF EXISTS `cal_bp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_bp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tplno` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `owner` int(11) NOT NULL DEFAULT 0,
  `module` varchar(4) NOT NULL DEFAULT '',
  `status` varchar(10) NOT NULL DEFAULT '',
  `dt_opened` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `short_desc` varchar(200) NOT NULL DEFAULT '',
  `long_desc` text NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `expiration` date NOT NULL DEFAULT '0000-00-00',
  `cur_step` int(11) NOT NULL DEFAULT 1,
  `dt_closed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=70725 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_events`
--

DROP TABLE IF EXISTS `cal_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `company` varchar(20) DEFAULT NULL,
  `date` date DEFAULT '0000-00-00',
  `day` int(2) NOT NULL DEFAULT 0,
  `month` int(2) NOT NULL DEFAULT 0,
  `end` date NOT NULL DEFAULT '0000-00-00',
  `description` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=641 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_seq_tpl`
--

DROP TABLE IF EXISTS `cal_seq_tpl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_seq_tpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `short_desc` varchar(200) NOT NULL DEFAULT '',
  `def_task` text NOT NULL COMMENT 'default task description',
  `def_escalation_trigger` int(11) NOT NULL,
  `private` char(1) NOT NULL DEFAULT 'N',
  `parallel` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'sales.demo.approval', 'Demo application approvals', '', 5, 'N', 'N');
INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'sales.extranetuser.approval', 'Extranet User application approvals', '', 5, 'N', 'N');
INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'sales.new.customer', 'New eCustomer approval', '', 5, 'N', 'N');
INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'sales.Customer.Validation', 'Initiation of customer validation sequence', '', 5, 'N', 'N');
INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'sales.extranetuser.differentCustomerApproval', 'Sequence to approve extranet access on different eCustomer than the one set on the contact profile', '', 5, 'N', 'N');
INSERT INTO cal_seq_tpl (parent_id, name, short_desc, def_task, def_escalation_trigger, private, parallel) VALUES (0, 'guest_user.new', 'Sequence for new Guest User', '', 5, 'N', 'N');

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_seq_tpl_nodes`
--

DROP TABLE IF EXISTS `cal_seq_tpl_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_seq_tpl_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `group_name` varchar(50) NOT NULL DEFAULT '',
  `allow_supervisor` tinyint(1) NOT NULL DEFAULT 0,
  `step` int(11) NOT NULL DEFAULT 1,
  `days_to_do` int(11) NOT NULL DEFAULT 14,
  `dsca` text NOT NULL COMMENT 'desc attached to node',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_seq_user_nodes`
--

DROP TABLE IF EXISTS `cal_seq_user_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_seq_user_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL,
  `step` int(11) NOT NULL DEFAULT 1,
  `days_to_do` int(11) NOT NULL DEFAULT 14,
  `dsca` text NOT NULL COMMENT 'desc attached to node',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=3686 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cal_st`
--

DROP TABLE IF EXISTS `cal_st`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_st` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(25) NOT NULL COMMENT 'Type of GTS event',
  `module` varchar(10) NOT NULL COMMENT 'module',
  `bu_id` int(3) NOT NULL COMMENT 'bu id#',
  `assignor` int(11) NOT NULL COMMENT 'scheduled task owner',
  `assignee` int(11) NOT NULL COMMENT 'scheduled task assignee',
  `date_start` date DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `escalation_trigger` int(11) NOT NULL DEFAULT 60,
  `description` text DEFAULT NULL,
  `repd_unit` varchar(5) NOT NULL COMMENT 'repetition unit',
  `repd_val` int(5) NOT NULL COMMENT 'repetition value',
  `leadtime_value` int(5) NOT NULL COMMENT 'leadtime_value',
  `leadtime_unit` varchar(5) NOT NULL COMMENT 'leadtime_unit',
  `action` varchar(20) NOT NULL COMMENT 'what action?',
  `status` varchar(15) NOT NULL DEFAULT 'ACTIVE',
  `dt_status_changed` datetime NOT NULL,
  `auto_close` tinyint DEFAULT 0 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bu_id` (`bu_id`),
  KEY `assignor` (`assignor`),
  KEY `assignee` (`assignee`)
) ENGINE=InnoDB AUTO_INCREMENT=738 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ccr`
--

DROP TABLE IF EXISTS `ccr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ccr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_list` text NOT NULL COMMENT 'comma separated email address list',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `exu_id` int(11) NOT NULL COMMENT 'extranet user id of poster',
  `cuid` int(11) NOT NULL COMMENT 'customer id',
  `type` varchar(50) NOT NULL,
  `buid` int(5) NOT NULL DEFAULT 0,
  `product_type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '0',
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `dt` date NOT NULL DEFAULT '0000-00-00' COMMENT 'date, time entered',
  `dt_closed` date NOT NULL DEFAULT '0000-00-00',
  `dt_eta` date NOT NULL DEFAULT '0000-00-00',
  `days_suspended` int(11) NOT NULL DEFAULT 0,
  `dsca` varchar(150) NOT NULL,
  `dscb` text NOT NULL,
  `resolution` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `picture_filename` varchar(254) NOT NULL,
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `final_fweight` int(11) NOT NULL DEFAULT 0,
  `poster` int(11) NOT NULL DEFAULT 0,
  `initiator` int(11) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL,
  `email_cc` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`dt`),
  KEY `factory` (`buid`),
  KEY `product_type` (`product_type`),
  KEY `model` (`model`),
  KEY `status` (`status`),
  KEY `poster` (`poster`),
  KEY `initiator` (`initiator`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1 COMMENT='Customer Communication Record';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `com_modules`
--

DROP TABLE IF EXISTS `com_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `com_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `oid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `dsc` varchar(255) NOT NULL COMMENT 'Description',
  `note` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `user_guide_id` int(11) NOT NULL COMMENT 'DMS user guide',
  `help_page_id` int(11) NOT NULL COMMENT 'DMS help page',
  `migrated` tinyint(4) NOT NULL DEFAULT 0,
  `key_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`),
  KEY `user_guide_id` (`user_guide_id`),
  KEY `help_page_id` (`help_page_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=latin1 COMMENT='Common - Modules list table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configurator`
--

DROP TABLE IF EXISTS `configurator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configurator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `configurator_options_id` int(11) NOT NULL,
  `configurator_elements_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configurator_elements`
--

DROP TABLE IF EXISTS `configurator_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configurator_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `configurator_id` int(11) NOT NULL,
  `value` varchar(250) NOT NULL,
  `configurator_elements_id` int(11) NOT NULL,
  `configurator_options_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `configurator_id` (`configurator_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configurator_options`
--

DROP TABLE IF EXISTS `configurator_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configurator_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `keytag` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cor`
--

DROP TABLE IF EXISTS `cor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `company_name` varchar(50) NOT NULL DEFAULT '',
  `short_desc` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `url` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `company_name` (`company_name`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cor_prod`
--

DROP TABLE IF EXISTS `cor_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cor_prod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(50) NOT NULL DEFAULT '',
  `tld_model` varchar(255) NOT NULL DEFAULT '',
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=486 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `alt_name` text NOT NULL,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `nb_code` int(11) NOT NULL,
  `fips_code` varchar(10) NOT NULL,
  `fips_name` varchar(250) NOT NULL,
  `region` varchar(100) NOT NULL,
  `sub_region` varchar(100) NOT NULL,
  `cdh_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `gps_lat` varchar(20) NOT NULL,
  `gps_long` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `iso_code_2` (`iso_code_2`)
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cpa`
--

DROP TABLE IF EXISTS `cpa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cpa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `dept` varchar(50) NOT NULL DEFAULT '',
  `bu` int(5) NOT NULL DEFAULT 0,
  `erp` int(4) DEFAULT 0,
  `proj_leader` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `last_status` varchar(15) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `date_target` date NOT NULL,
  `date_closed` date NOT NULL DEFAULT '0000-00-00',
  `date_suspended` date NOT NULL DEFAULT '0000-00-00',
  `days_suspended` int(11) NOT NULL DEFAULT 0,
  `short_desc` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `containment_action` text NOT NULL,
  `root_cause` text NOT NULL,
  `corrective_action` text NOT NULL,
  `preventive_action` text NOT NULL,
  `resolution` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `picture_filename` varchar(100) NOT NULL DEFAULT '',
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `final_fweight` int(11) NOT NULL DEFAULT 0,
  `poster` int(11) NOT NULL DEFAULT 0,
  `initiator` int(11) NOT NULL DEFAULT 0,
  `verification_description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1423 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cpr`
--

DROP TABLE IF EXISTS `cpr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cpr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `qdate` date NOT NULL,
  `competitor` int(11) unsigned NOT NULL,
  `model` varchar(256) NOT NULL,
  `options` text NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(14,4) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `exchange_rate` decimal(14,4) NOT NULL,
  `inco_terms` varchar(256) NOT NULL,
  `inco_loc` text NOT NULL,
  `markup_percent` int(2) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1219 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crabs`
--

DROP TABLE IF EXISTS `crabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `buid` int(11) NOT NULL,
  `assignor` int(11) NOT NULL,
  `init_emno` int(11) NOT NULL COMMENT 'initiator emno',
  `fix_emno` int(11) NOT NULL COMMENT 'employee number',
  `insp_emno` int(11) NOT NULL COMMENT 'Inspected by',
  `insp_id` int(11) NOT NULL COMMENT 'inspector userid',
  `insp_dt` datetime NOT NULL COMMENT 'inspection date',
  `fix_dt` datetime NOT NULL COMMENT 'fixed date',
  `entered_by` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `erid` int(11) NOT NULL COMMENT 'unit T serial number',
  `pdno` int(6) NOT NULL COMMENT 'Production order number',
  `pn` varchar(20) NOT NULL,
  `opno` varchar(10) NOT NULL COMMENT 'operation',
  `who` varchar(10) NOT NULL COMMENT 'who is to blame?',
  `dept` varchar(20) NOT NULL,
  `ncrid` int(11) NOT NULL,
  `code` varchar(3) NOT NULL,
  `dsca` text NOT NULL,
  `act` text NOT NULL,
  `filtering_flag` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buid` (`buid`),
  KEY `erid` (`erid`)
) ENGINE=InnoDB AUTO_INCREMENT=114021 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `csr`
--

DROP TABLE IF EXISTS `csr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `csr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `sso_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `work_type` varchar(60) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `send_tech` varchar(1) NOT NULL,
  `dt_work` datetime NOT NULL,
  `dt_sche` datetime NOT NULL,
  `dt_completed` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `con_id` int(11) NOT NULL,
  `apc` varchar(10) NOT NULL,
  `hourmeter` int(11) NOT NULL,
  `short_desc` varchar(250) NOT NULL,
  `int_desc` text NOT NULL,
  `ext_desc` text NOT NULL,
  `erp_inv` varchar(20) NOT NULL,
  `module` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL,
  `ship_to` text NOT NULL,
  `bill_to` varchar(60) NOT NULL,
  `bill_instruction` text NOT NULL,
  `confidential_flag` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `entered_by` (`entered_by`),
  KEY `sso_id` (`sso_id`),
  KEY `tech_id` (`tech_id`),
  KEY `con_id` (`con_id`),
  KEY `module` (`module`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58642 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `csr_old`
--

DROP TABLE IF EXISTS `csr_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `csr_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `sso_id` int(2) NOT NULL,
  `call_via` varchar(50) NOT NULL COMMENT 'Contact method',
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL,
  `dt_sche` datetime NOT NULL COMMENT 'Scheduled date and time',
  `apc` varchar(10) NOT NULL COMMENT 'airport code',
  `entered_by` int(11) NOT NULL,
  `assignor` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL COMMENT 'Service Tech ID',
  `cust_nama` varchar(255) NOT NULL,
  `cust_cona` varchar(255) NOT NULL COMMENT 'customer contact name',
  `cust_tela` varchar(30) NOT NULL COMMENT 'Customer contact number',
  `cust_telb` varchar(30) NOT NULL,
  `notes` text NOT NULL COMMENT 'ship to',
  `dsca` text NOT NULL COMMENT 'service description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3215 DEFAULT CHARSET=latin1 COMMENT='OBSOLETE csr table to keep as archive';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer_feedback`
--

DROP TABLE IF EXISTS `customer_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `source` varchar(20) NOT NULL,
  `recipients` text NOT NULL,
  `subject` varchar(60) NOT NULL,
  `customer_name` varchar(60) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `ext_user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(80) NOT NULL,
  `title` varchar(255) NOT NULL,
  `country` varchar(80) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `er_sn` varchar(20) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2887 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `customer_name` varchar(60) NOT NULL DEFAULT 'company_name',
  `customer_short_name` varchar(20) NOT NULL DEFAULT '',
  `customer_address` text NOT NULL,
  `ctry_id` int(11) NOT NULL COMMENT 'Country',
  `customer_tel` varchar(20) NOT NULL DEFAULT '',
  `customer_fax` varchar(20) NOT NULL DEFAULT '',
  `type` varchar(40) NOT NULL,
  `hidden` tinyint(4) NOT NULL DEFAULT 0,
  `approved` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `logo_file` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL,
  `asm_id` int(11) NOT NULL,
  `deleted_at` date NOT NULL,
  `customer_em_jira_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4074 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers_countries`
--

DROP TABLE IF EXISTS `customers_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `ctry_id` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers_crt`
--

DROP TABLE IF EXISTS `customers_crt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers_crt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `customer_id` int(11) NOT NULL,
  `sales_rep_id` int(11) NOT NULL COMMENT 'Sales representative',
  `parts_rep_id` int(11) NOT NULL COMMENT 'Parts representative',
  `services_rep_id` int(11) NOT NULL COMMENT 'Services representative',
  `parts_location_id` int(11) NOT NULL COMMENT 'Spare Parts Hub Location',
  `services_location_id` int(11) NOT NULL COMMENT 'Service Hub Location',
  `erp_location_id` int(11) NOT NULL COMMENT 'TLD ERP number',
  `cuno` varchar(50) NOT NULL COMMENT 'BAAN cust number',
  `deleted_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `sales_rep_id` (`sales_rep_id`),
  KEY `parts_rep_id` (`parts_rep_id`),
  KEY `services_rep_id` (`services_rep_id`),
  KEY `parts_location_id` (`parts_location_id`),
  KEY `services_location_id` (`services_location_id`),
  KEY `erp_location_id` (`erp_location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4390 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
-- Static ACL granting extranet user 7200 (julien.lepers@tld.com) access to customer 4074.
-- Uses a low explicit id (below AUTO_INCREMENT) so it never collides with rows created by the
-- fixture live-sync, and is independent from the CRT/ACL synchronization tests that mutate the
-- synced rows (e.g. PUT on CRT #1) which would otherwise break ServiceBulletin visibility.
INSERT INTO customers_crt (id, parent_id, customer_id, sales_rep_id, parts_rep_id, services_rep_id, parts_location_id, services_location_id, erp_location_id, cuno, deleted_at)
VALUES (1, 0, 4074, 0, 0, 0, 0, 0, 0, 'SBTEST', '0000-00-00');

--
-- Table structure for table `customers_msgs`
--

DROP TABLE IF EXISTS `customers_msgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers_msgs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `userid` varchar(50) DEFAULT NULL,
  `timestamp` varchar(25) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers_tables`
--

DROP TABLE IF EXISTS `customers_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `table` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `mod` varchar(50) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  `contraints` varchar(256) DEFAULT NULL,
  `uri` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demerit`
--

DROP TABLE IF EXISTS `demerit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demerit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `factory` int(5) NOT NULL DEFAULT 0,
  `product_type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '0',
  `last_status` varchar(15) NOT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `date_closed` date NOT NULL DEFAULT '0000-00-00',
  `date_suspended` date NOT NULL DEFAULT '0000-00-00',
  `days_suspended` int(11) NOT NULL DEFAULT 0,
  `short_desc` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `containment_action` text NOT NULL,
  `root_cause` text NOT NULL,
  `corrective_action` text NOT NULL,
  `preventive_action` text NOT NULL,
  `resolution` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `picture_filename` varchar(254) NOT NULL,
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `final_fweight` int(11) NOT NULL DEFAULT 0,
  `poster` int(11) NOT NULL DEFAULT 0,
  `initiator` int(11) NOT NULL DEFAULT 0,
  `assignee` int(11) NULL,
  `verification_description` text NULL,
  `status_updated_at` datetime NOT NULL,
  `is_ibs` tinyint(1) DEFAULT 0,
  `is_link` tinyint(1) DEFAULT 0,
  `is_ihs` tinyint(1) DEFAULT 0 NULL,
  `is_apu_off` tinyint(1) DEFAULT 0 NULL,
  `is_ready_to_close` tinyint(1) DEFAULT 0 NULL,
  PRIMARY KEY (`id`),
  KEY `factory` (`factory`),
  KEY `product_type` (`product_type`),
  KEY `model` (`model`),
  KEY `poster` (`poster`),
  KEY `initiator` (`initiator`)
) ENGINE=InnoDB AUTO_INCREMENT=5001 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO demerit (parent_id, factory, product_type, model, last_status, status, date, date_closed, date_suspended, days_suspended, short_desc, description, containment_action, root_cause, corrective_action, preventive_action, resolution, rejection_reason, picture_filename, ifactor, final_fweight, poster, initiator, assignee, verification_description, status_updated_at, is_ibs, is_link, is_ihs, is_ready_to_close)
VALUES
    (0, 4, 'Passenger Steps', 'ABT-NB-E', 'PENDING', 'PENDING', '2025-06-17', '0000-00-00', '0000-00-00', 0, 'PDC Short Description', 'PDC Description', '', '', '', '', '', '', '', 100, 0, 1, 1, 0, null, '2025-06-17 14:13:44', 0, 0, 0, 0),
    (0, 4, 'Passenger Steps', 'ABT-NB-E', 'PENDING', 'PENDING', '2025-06-17', '0000-00-00', '0000-00-00', 0, 'PDC Short Description', 'PDC Description', '', '', '', '', '', '', '', 100, 0, 1, 1, 0, null, '2025-06-17 14:13:44', 0, 0, 0, 0)
;


--
-- Table structure for table `demerit_history`
--

DROP TABLE IF EXISTS `demerit_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demerit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `fweight` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=858189 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `descriptions_translate`
--

DROP TABLE IF EXISTS `descriptions_translate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `descriptions_translate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(15) NOT NULL,
  `lang1` varchar(200) NOT NULL,
  `lang2` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=764 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms`
--

DROP TABLE IF EXISTS `dms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `dt_act` datetime NOT NULL COMMENT 'Last activation date',
  `owner_id` int(11) NOT NULL COMMENT 'owner id',
  `title` varchar(250) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `lang` varchar(10) NOT NULL,
  `status` varchar(60) NOT NULL,
  `type_id` int(11) NOT NULL,
  `periodicity` int(11) NOT NULL,
  `sysref` varchar(40) NOT NULL COMMENT 'System Reference',
  `portal` varchar(10) NOT NULL,
  `access_type` varchar(20) NOT NULL,
  `rev_id` int(11) NOT NULL COMMENT 'revision id',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `owner_id` (`owner_id`),
  KEY `type_id` (`type_id`),
  KEY `rev_id` (`rev_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1632 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms_approver`
--

DROP TABLE IF EXISTS `dms_approver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms_approver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `step` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'user id',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2880 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms_bu`
--

DROP TABLE IF EXISTS `dms_bu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms_bu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `buid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buid` (`buid`)
) ENGINE=InnoDB AUTO_INCREMENT=4105 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms_department`
--

DROP TABLE IF EXISTS `dms_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dptid` int(11) NOT NULL COMMENT 'Department ID',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6113 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms_revision`
--

DROP TABLE IF EXISTS `dms_revision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms_revision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `revision` int(11) NOT NULL,
  `src_fid` int(11) NOT NULL COMMENT 'Source file id',
  `pub_fid` int(11) NOT NULL COMMENT 'Publish file id',
  `seq_id` int(11) NOT NULL COMMENT 'Sequence id',
  `purpose` text NOT NULL COMMENT 'revision purpose',
  `next_rev_comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `src_fid` (`src_fid`),
  KEY `pub_fid` (`pub_fid`),
  KEY `seq_id` (`seq_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2348 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dms_type`
--

DROP TABLE IF EXISTS `dms_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dms_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `short_desc` varchar(60) NOT NULL,
  `cycle` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `downloads`
--

DROP TABLE IF EXISTS `downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downloads` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `category` varchar(50) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `description` text DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  `lang` char(2) DEFAULT NULL,
  `format` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `downloads_perms`
--

DROP TABLE IF EXISTS `downloads_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downloads_perms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `access` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eap`
--

DROP TABLE IF EXISTS `eap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `parent_module` varchar(5) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `pvt` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Private for members only?',
  `dt_opened` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_closed` datetime NOT NULL COMMENT 'date and time closed',
  `reporter` int(11) NOT NULL,
  `reported_by` varchar(255) NOT NULL DEFAULT '0',
  `t_emno` int(5) NOT NULL DEFAULT 0,
  `pn` varchar(20) NOT NULL DEFAULT '',
  `short_desc` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT '',
  `ifactor` varchar(4) NOT NULL DEFAULT '1',
  `type` varchar(30) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `factory` int(3) NOT NULL DEFAULT 0,
  `indt` date NOT NULL DEFAULT '0000-00-00',
  `action_plan` text NOT NULL,
  `currency` char(3) NOT NULL DEFAULT '',
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `poster` int(11) NOT NULL DEFAULT 0,
  `assignee` int(11) NOT NULL COMMENT 'assigned engineer for overnight processing',
  `info` varchar(20) NOT NULL,
  `discipline` varchar(50),
  UNIQUE KEY `id` (`id`),
  KEY `status` (`status`),
  KEY `pn` (`pn`),
  KEY `short_desc` (`short_desc`),
  KEY `category` (`category`),
  KEY `type` (`type`),
  KEY `model` (`model`),
  KEY `factory` (`factory`),
  KEY `assignee` (`assignee`),
  KEY `poster` (`poster`),
  KEY `idx_eap_parent_id` (`parent_id`),
  KEY `idx_eap_parent_module` (`parent_module`),
  KEY `idx_eap_parent_id_parent_module` (`parent_id`,`parent_module`)
) ENGINE=InnoDB AUTO_INCREMENT=39334 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eap_files`
--

DROP TABLE IF EXISTS `eap_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eap_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `long_desc` text NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eap_parts`
--

DROP TABLE IF EXISTS `eap_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eap_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `pn` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pn` (`pn`)
) ENGINE=InnoDB AUTO_INCREMENT=63978 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eap_prj`
--

DROP TABLE IF EXISTS `eap_prj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eap_prj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dsca` varchar(100) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eap_proposals`
--

DROP TABLE IF EXISTS `eap_proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eap_proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `dt_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `action_plan` text NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `currency` char(3) NOT NULL DEFAULT '',
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edi_logs`
--

DROP TABLE IF EXISTS `edi_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(50) NOT NULL,
  `transaction_type` varchar(20) NOT NULL,
  `message_id` int(11) unsigned NOT NULL,
  `message_type` varchar(20) NOT NULL,
  `message_flow` varchar(3) NOT NULL,
  `message_content` text NOT NULL,
  `source_name` varchar(50) NOT NULL,
  `from` varchar(50) NOT NULL,
  `to` varchar(50) NOT NULL,
  `dt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`,`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32121 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eparts_commissions`
--

DROP TABLE IF EXISTS `eparts_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eparts_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `t_cuno` varchar(11) DEFAULT NULL,
  `erp` int(11) DEFAULT NULL,
  `commission` decimal(4,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eparts_favorites`
--

DROP TABLE IF EXISTS `eparts_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eparts_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `serialized_cart` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eparts_orders`
--

DROP TABLE IF EXISTS `eparts_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eparts_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pono` varchar(256) NOT NULL COMMENT 'PO Number',
  `name` varchar(256) NOT NULL COMMENT 'Contact Name',
  `email` varchar(100) NOT NULL COMMENT 'Contact Email',
  `phon` varchar(100) NOT NULL COMMENT 'Contact Phone Number',
  `sdat` date NOT NULL COMMENT 'Requested Ship Date',
  `rush` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Rush Importance (0=No Rush, 1=Rush, 2=Immediate Rush)',
  `cour` varchar(256) NOT NULL COMMENT 'Forwarding Agent',
  `csvc` varchar(50) NOT NULL COMMENT 'Shipping Service',
  `actn` varchar(100) NOT NULL COMMENT 'Forwarding Agent Account Number',
  `ctxt` text NOT NULL COMMENT 'Shipping Info',
  `cdel` varchar(50) NOT NULL COMMENT 'Delivery Address Code',
  `dadr` text NOT NULL COMMENT 'Delivery Address',
  `ccor` varchar(50) NOT NULL COMMENT 'Billing Address Code',
  `inst` text NOT NULL,
  `total` float(15,2) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `serialized_cart` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1123 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eparts_users`
--

DROP TABLE IF EXISTS `eparts_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eparts_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `cdel` varchar(50) NOT NULL COMMENT 'Default Delivery Address Code',
  `ccor` varchar(50) NOT NULL COMMENT 'Default Postal Address Code',
  `cour` varchar(50) NOT NULL COMMENT 'Default Courier',
  `csvc` varchar(50) NOT NULL COMMENT 'Default Shipping Service Level',
  `actn` varchar(100) NOT NULL COMMENT 'Default Courier Account Number',
  `current_cart` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=910 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_approvers`
--

DROP TABLE IF EXISTS `erp_approvers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_approvers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `type` char(6) NOT NULL,
  `ref_num` varchar(10) NOT NULL,
  `approver_id` int(11) NOT NULL DEFAULT 0,
  `bloc` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Bloc by default ?',
  `assignor_id` int(11),
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`ref_num`)
) ENGINE=InnoDB AUTO_INCREMENT=1869 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_archive`
--

DROP TABLE IF EXISTS `erp_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL COMMENT 'message id number',
  `uid` varchar(100) NOT NULL COMMENT 'email of user who printed the baan document',
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `erp` int(3) NOT NULL DEFAULT 0,
  `doc_type` varchar(30) NOT NULL DEFAULT '',
  `dest` int(3) NOT NULL COMMENT 'destination erp number',
  `num` varchar(20) NOT NULL DEFAULT '',
  `dat` date NOT NULL DEFAULT '0000-00-00',
  `filepath` varchar(255) NOT NULL DEFAULT '',
  `xml` text NOT NULL,
  `response` text NOT NULL COMMENT 'response from dest erp',
  `status` varchar(50) NOT NULL,
  `flow_type` varchar(100) NOT NULL COMMENT 'Workflow type',
  PRIMARY KEY (`id`),
  KEY `doc_type` (`doc_type`),
  KEY `erp` (`erp`),
  KEY `num` (`num`),
  KEY `erp_archive_dt_index` (`dt`)
) ENGINE=InnoDB AUTO_INCREMENT=1982668 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_dino_trno`
--

DROP TABLE IF EXISTS `erp_dino_trno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_dino_trno` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `erp` int(3) NOT NULL,
  `dino` varchar(20) NOT NULL,
  `courier` varchar(10) NOT NULL,
  `trno` varchar(50) NOT NULL,
  `trnoBAK` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp` (`erp`),
  KEY `dino` (`dino`)
) ENGINE=InnoDB AUTO_INCREMENT=74873 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_forex2`
--

DROP TABLE IF EXISTS `erp_forex2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_forex2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `nam_year` int(4) NOT NULL,
  `nam_month` int(2) NOT NULL,
  `nam_cur` varchar(3) NOT NULL,
  `typ` enum('END','AVG','TLD') NOT NULL,
  `rate` decimal(12,8) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `nam_year_2` (`nam_year`,`nam_month`),
  KEY `nam_cur` (`nam_cur`),
  KEY `typ` (`typ`)
) ENGINE=InnoDB AUTO_INCREMENT=22962 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_int`
--

DROP TABLE IF EXISTS `erp_int`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_int` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `src` int(3) NOT NULL COMMENT 'source erp number',
  `ttyp` varchar(20) NOT NULL COMMENT 'transaction type',
  `dst` int(3) NOT NULL COMMENT 'destination erp number',
  `params` text NOT NULL COMMENT 'param definitions',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='erp integration matrix';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_licences`
--

DROP TABLE IF EXISTS `erp_licences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_licences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licences` smallint(6) NOT NULL,
  `baan_id` varchar(8) NOT NULL,
  `baan_comp` varchar(3) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `desktop` varchar(15) NOT NULL,
  `process_id` varchar(10) NOT NULL,
  `client_id` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `baan_id` (`baan_id`),
  KEY `dt` (`dt`),
  KEY `licences` (`licences`)
) ENGINE=InnoDB AUTO_INCREMENT=38764558 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_ml`
--

DROP TABLE IF EXISTS `erp_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_ml` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `erp` int(3) NOT NULL,
  `dt_entered` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dsca` varchar(255) NOT NULL,
  `t_prno` varchar(50) NOT NULL COMMENT 'project number',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_mll`
--

DROP TABLE IF EXISTS `erp_mll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_mll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `tsec` int(11) NOT NULL COMMENT 'time in seconds',
  `t_prno` varchar(20) NOT NULL,
  `t_mitm` varchar(50) NOT NULL,
  `t_pono` varchar(5) NOT NULL,
  `t_sitm` varchar(50) NOT NULL,
  `t_qana` decimal(5,0) NOT NULL,
  `t_cuni` varchar(10) NOT NULL,
  `t_dsca` varchar(255) NOT NULL,
  `t_csig` varchar(3) NOT NULL,
  `t_revi` varchar(5) NOT NULL,
  `t_indt` date NOT NULL,
  `t_exdt` date NOT NULL,
  `changed` varchar(1) NOT NULL,
  `p` smallint(6) NOT NULL,
  `m` smallint(6) NOT NULL,
  `o` smallint(6) NOT NULL,
  `c` smallint(6) NOT NULL,
  `lev` smallint(6) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9200 DEFAULT CHARSET=latin1 COMMENT='Material List Lines';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_mrp_archive`
--

DROP TABLE IF EXISTS `erp_mrp_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_mrp_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `t_comp` varchar(3) NOT NULL,
  `t_horo` datetime NOT NULL,
  `t_item` varchar(40) NOT NULL,
  `t_date` date NOT NULL,
  `t_qana` float NOT NULL,
  `t_kotr` tinyint(4) NOT NULL,
  `t_koor` tinyint(4) NOT NULL,
  `t_orno` int(11) NOT NULL,
  `t_pono` int(11) NOT NULL,
  `t_stoc` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `t_comp` (`t_comp`),
  KEY `t_horo` (`t_horo`),
  KEY `t_item` (`t_item`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_msg`
--

DROP TABLE IF EXISTS `erp_msg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `erp_from` int(3) NOT NULL,
  `erp_to` int(3) NOT NULL,
  `doc_type` varchar(30) NOT NULL,
  `status` varchar(50) NOT NULL,
  `eparts_order` int(11) NOT NULL,
  `pono` varchar(256) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3386 DEFAULT CHARSET=latin1 COMMENT='ERP message communication table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_po_ddat`
--

DROP TABLE IF EXISTS `erp_po_ddat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_po_ddat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `erp` int(3) NOT NULL DEFAULT 0,
  `t_orno` varchar(20) NOT NULL DEFAULT '',
  `t_pono` varchar(5) NOT NULL DEFAULT '',
  `t_ddat` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `t_ponum` (`t_orno`),
  KEY `erp` (`erp`)
) ENGINE=InnoDB AUTO_INCREMENT=704502 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `erp_shortages_comments`
--

DROP TABLE IF EXISTS `erp_shortages_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_shortages_comments` (
  `erp` int(3) NOT NULL,
  `t_orno` int(8) NOT NULL,
  `t_pono` int(8) NOT NULL,
  `t_ponb` int(8) NOT NULL,
  `t_koor` int(2) NOT NULL,
  `Comment` text NOT NULL,
  `Critical` varchar(20) NOT NULL,
  PRIMARY KEY (`erp`,`t_orno`,`t_pono`,`t_ponb`,`t_koor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esq`
--

DROP TABLE IF EXISTS `esq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Equipment Shipping Quotations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esql`
--

DROP TABLE IF EXISTS `esql`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esql` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Equipment Shipping Quotations Lines';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esr`
--

DROP TABLE IF EXISTS `esr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `sso_id` int(11) NOT NULL,
  `cuid` int(11) NOT NULL COMMENT 'Customer ID',
  `dt_open` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Date creation',
  `status` varchar(20) NOT NULL COMMENT 'Status',
  `inco` varchar(10) NOT NULL COMMENT 'ESR Incoterm',
  `load_place` varchar(100) NOT NULL COMMENT 'Loading place',
  `departure` varchar(100) NOT NULL COMMENT 'Departure of shipment',
  `arrival` varchar(100) NOT NULL COMMENT 'Arrival of shipment',
  `car_id` int(11) NOT NULL COMMENT 'Inland carrier',
  `fwd_id` int(11) NOT NULL COMMENT 'Forwarder',
  `modality` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `ship_auth` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `sso_id` (`sso_id`),
  KEY `cuid` (`cuid`)
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esrl`
--

DROP TABLE IF EXISTS `esrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esrl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `erid` int(11) NOT NULL COMMENT 'ER ID',
  `dt_shipped` date NOT NULL COMMENT 'Departure of shipment',
  `dt_estimated` date NOT NULL COMMENT 'Estimated arrival date',
  `dt_arrived` date NOT NULL COMMENT 'Actual arrival date',
  `dt_pick_up` date NOT NULL,
  `sqe` varchar(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erid` (`erid`)
) ENGINE=InnoDB AUTO_INCREMENT=1804 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_login_logs`
--

DROP TABLE IF EXISTS `extranet_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_login_logs` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `dt` datetime NOT NULL,
  `user_agent` varchar(256) NOT NULL,
  `http_referer` varchar(256) NOT NULL,
  `portal` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19048 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_news`
--

DROP TABLE IF EXISTS `extranet_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(150) NOT NULL DEFAULT '',
  `en` text DEFAULT NULL,
  `filename` varchar(80) DEFAULT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `cat` mediumint(9) NOT NULL DEFAULT 1,
  `extranet` char(3) NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_users`
--

DROP TABLE IF EXISTS `extranet_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL DEFAULT '',
  `salutation` varchar(10) NOT NULL,
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `division` varchar(100) NOT NULL DEFAULT '',
  `department` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `lang` varchar(2) NOT NULL COMMENT 'Prefered language',
  `phone` varchar(30) NOT NULL DEFAULT '',
  `direct_phone` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(20) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `home_phone` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `password` varchar(20) NOT NULL DEFAULT 'LOCKED OUT',
  `extranet_pass_bck` varchar(20) NOT NULL DEFAULT 'LOCKED OUT',
  `perms` enum('user','admin') NOT NULL DEFAULT 'user',
  `address` text NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `country` varchar(80) NOT NULL,
  `shipping_address` text NOT NULL,
  `counter` int(5) NOT NULL DEFAULT 0,
  `last` datetime DEFAULT NULL,
  `login` datetime DEFAULT NULL,
  `userid` varchar(80) NOT NULL DEFAULT '',
  `hidden` int(1) NOT NULL DEFAULT 0,
  `customer_name` varchar(50) NOT NULL DEFAULT '',
  `cust_carrier_name` varchar(100) NOT NULL COMMENT 'customer carrier name',
  `company_name` varchar(100) NOT NULL DEFAULT '',
  `note` text NOT NULL,
  `ship_acct_num` varchar(20) NOT NULL COMMENT 'Shipping Account number',
  `requestor_num` varchar(20) NOT NULL COMMENT 'requestor number',
  `employe_num` varchar(20) NOT NULL COMMENT 'Employe number',
  `default_erp` varchar(50) NOT NULL DEFAULT '',
  `tld_rep_id` int(11) NOT NULL DEFAULT 0,
  `parts_rep_id` int(11) NOT NULL DEFAULT 0,
  `parts_location_id` int(11) NOT NULL DEFAULT 0,
  `erp` int(11) NOT NULL DEFAULT 0,
  `t_cuno` varchar(11) NOT NULL DEFAULT '',
  `t_cdel` varchar(11) NOT NULL DEFAULT '',
  `seqid` int(11) NOT NULL,
  `enable` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Is enable?',
  `archived` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=7200 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_users_fav`
--

DROP TABLE IF EXISTS `extranet_users_fav`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_users_fav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `fav_user` varchar(255) NOT NULL,
  `fav_pn` varchar(100) NOT NULL,
  `fav_dsca` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2219 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_users_pwhist`
--

DROP TABLE IF EXISTS `extranet_users_pwhist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_users_pwhist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `change_date` date DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6563 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extranet_users_roles`
--

DROP TABLE IF EXISTS `extranet_users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extranet_users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `crt_id` int(11) NOT NULL,
  `cdel` varchar(5) NOT NULL COMMENT 'Baan cust delivery id',
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_extranet_users_cust_link_extranet_users` (`parent_id`),
  KEY `fk_extranet_users_cust_link_extranet_cust_ref` (`crt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24683 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
-- Links extranet user 7200 (julien.lepers@tld.com) to the static customers_crt #1 (customer 4074).
-- See the customers_crt insert above for the rationale.
INSERT INTO extranet_users_roles (id, parent_id, crt_id, cdel, role)
VALUES (1, 7200, 1, '', 'TOC');

--
-- Table structure for table `fcr`
--

DROP TABLE IF EXISTS `fcr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `sfr_status` varchar(50) NOT NULL,
  `sub_status` varchar(50) NOT NULL,
  `equote_id` varchar(20) NOT NULL,
  `ordered_qty` int(11) NOT NULL,
  `price` decimal(14,4) NOT NULL,
  `exwprice` decimal(14,4) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `competitor` int(11) NOT NULL,
  `reason` varchar(256) NOT NULL,
  `comment` text NOT NULL,
  `filename` varchar(256) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6112 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `portal` varchar(10) NOT NULL COMMENT 'source of file',
  `poster` varchar(100) NOT NULL COMMENT 'poster userid',
  `filepath` varchar(200) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `mime` varchar(20) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Size in B',
  `filename` varchar(100) NOT NULL COMMENT 'original filename',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=156268 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO file (id, parent_id, dt, portal, filepath, filename, extension, poster) VALUES (1, 0, '2022-11-10 00:00:00', 'INTRANET', '/var/uploads/mod_files/file.txt', 'file.txt', 'txt', 1);
INSERT INTO file (id, parent_id, dt, portal, filepath, filename, extension, poster) VALUES (2, 62, '2022-11-10 00:00:00', 'INTRANET', '/uploads/image_1200x1200.jpg', 'cat', 'jpg', 1);

--
-- Table structure for table `file_archive`
--

DROP TABLE IF EXISTS `file_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `portal` varchar(10) NOT NULL COMMENT 'source of file',
  `poster` varchar(100) NOT NULL COMMENT 'poster userid',
  `filepath` varchar(200) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `mime` varchar(20) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Size in B',
  `filename` varchar(100) NOT NULL COMMENT 'original filename',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155722 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_log`
--

DROP TABLE IF EXISTS `file_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `portal` varchar(10) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `env` varchar(250) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `action` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111200 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fin_kpi`
--

DROP TABLE IF EXISTS `fin_kpi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_kpi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `buid` int(11) NOT NULL COMMENT 'BU id',
  `ynam` year(4) NOT NULL COMMENT 'Applicable year',
  `mnam` tinyint(4) NOT NULL COMMENT 'Applicable month',
  `erp_tot_sls` decimal(10,2) NOT NULL,
  `erp_tot_pur` decimal(10,2) NOT NULL,
  `erp_net_inv_val` decimal(10,2) NOT NULL,
  `erp_net_wip_val` decimal(10,2) NOT NULL,
  `erp_net_raw_material_val` decimal(10,2) NOT NULL,
  `erp_tot_ar` decimal(10,2) NOT NULL,
  `erp_tot_ap` decimal(10,2) NOT NULL,
  `cc_qua` int(11) NOT NULL,
  `cc_quv` decimal(10,2) NOT NULL,
  `cc_pric` decimal(10,2) NOT NULL,
  `cc_priv` decimal(10,2) NOT NULL,
  `wip_vald` decimal(10,2) NOT NULL,
  `inv_vald` decimal(10,2) NOT NULL,
  `fse_stdh` decimal(10,2) NOT NULL,
  `fse_acth` decimal(10,2) NOT NULL,
  `phr_proh` decimal(10,2) NOT NULL,
  `phr_poth` decimal(10,2) NOT NULL,
  `ics_ame` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from TLD America',
  `ics_asi` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from TLD Asia',
  `ics_eur` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from TLD Europe',
  `ics_prc` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from TLD China',
  `ics_meai` decimal(3,2) NOT NULL COMMENT 'TLD MEAI',
  `ics_laaj` decimal(3,2) NOT NULL,
  `ics_nam` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from TLD NAM',
  `ics_aero` decimal(3,2) NOT NULL COMMENT 'Customer satisfaction number from AERO',
  `sso_tot_sls` decimal(10,2) NOT NULL,
  `sso_tot_ar` decimal(10,2) NOT NULL,
  `sso_fin_gds` decimal(10,2) NOT NULL,
  `sph_tot_sls` decimal(10,2) NOT NULL,
  `sph_net_inv_val` decimal(10,2) NOT NULL,
  `nb_whsekeeper` int(11) NOT NULL,
  `nb_sfe` int(11) NOT NULL,
  `nb_wgl` int(11) NOT NULL,
  `nb_sgl` int(11) NOT NULL COMMENT 'Number of shopfloor group leader',
  `workshop_surface` decimal(10,2) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `buid` (`buid`,`ynam`,`mnam`)
) ENGINE=InnoDB AUTO_INCREMENT=684 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fin_periods`
--

DROP TABLE IF EXISTS `fin_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_periods` (
  `nam_period` int(6) NOT NULL,
  UNIQUE KEY `nam_period` (`nam_period`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This is a generic means of correlating data to ym periods';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwf`
--

DROP TABLE IF EXISTS `gwf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gwf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `pvt` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Private for members only?',
  `bu` int(4) DEFAULT 0,
  `ctg` varchar(50) NOT NULL,
  `ifactor` int(5) NOT NULL DEFAULT 1,
  `type` varchar(50) NOT NULL COMMENT 'Product Type',
  `model` varchar(255) NOT NULL COMMENT 'Model',
  `assignor` int(11) NOT NULL DEFAULT 0,
  `status` varchar(15) NOT NULL DEFAULT 'OPEN',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `dsca` varchar(100) NOT NULL,
  `dscb` text NOT NULL,
  `assignee` int(11) NOT NULL DEFAULT 0,
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  `d_escal` date NOT NULL DEFAULT '0000-00-00',
  `dt_closed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dest` date NOT NULL COMMENT 'estimated completion date',
  `kwd1` varchar(20) NOT NULL,
  `kwd2` varchar(20) NOT NULL,
  `kwd3` varchar(20) NOT NULL,
  `kwd4` varchar(20) NOT NULL,
  `kwd5` varchar(20) NOT NULL,
  `payload` text NOT NULL COMMENT 'serialized object ',
  `payload_class` varchar(50) NOT NULL COMMENT 'class name of object in payload',
  `picture_filename` varchar(254) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assignor` (`assignor`),
  KEY `assignee` (`assignee`),
  KEY `parent_id` (`parent_id`),
  KEY `module` (`ctg`)
) ENGINE=InnoDB AUTO_INCREMENT=4238 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwf_members`
--

DROP TABLE IF EXISTS `gwf_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gwf_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'userid',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hr_jobs`
--

DROP TABLE IF EXISTS `hr_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(10) NOT NULL DEFAULT 'OPEN',
  `assignor` int(11) NOT NULL COMMENT 'owner of this job',
  `buid` int(11) NOT NULL COMMENT 'bu id',
  `country` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `diploma` varchar(250) NOT NULL,
  `experience` varchar(250) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `description` text DEFAULT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `assignor` (`assignor`),
  KEY `buid` (`buid`)
) ENGINE=InnoDB AUTO_INCREMENT=1187 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internal_news`
--

DROP TABLE IF EXISTS `internal_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internal_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) NOT NULL DEFAULT '',
  `en` text DEFAULT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `cat` mediumint(9) NOT NULL DEFAULT 1,
  `extranet` char(3) NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  KEY `cat` (`cat`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=5355 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date_entered` date NOT NULL DEFAULT '0000-00-00',
  `sales_org` varchar(20) NOT NULL DEFAULT '',
  `model` varchar(30) DEFAULT NULL,
  `qty` int(2) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  `transfer_price` varchar(40) DEFAULT NULL,
  `original_tx` varchar(40) DEFAULT NULL,
  `book_value` varchar(40) DEFAULT NULL,
  `spec` text NOT NULL,
  `vendor` varchar(80) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `investors`
--

DROP TABLE IF EXISTS `investors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investors` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `en_title` varchar(200) NOT NULL DEFAULT '',
  `en` text NOT NULL,
  `fr_title` varchar(200) NOT NULL DEFAULT '',
  `fr` text NOT NULL,
  `es_title` varchar(200) NOT NULL DEFAULT '',
  `es` text NOT NULL,
  `zh_title` varchar(200) NOT NULL DEFAULT '',
  `zh` text NOT NULL,
  `pt_title` varchar(200) NOT NULL DEFAULT '',
  `pt` text NOT NULL,
  `de_title` varchar(200) NOT NULL DEFAULT '',
  `de` text NOT NULL,
  `ja_title` varchar(200) NOT NULL DEFAULT '',
  `ja` text NOT NULL,
  `ru_title` varchar(200) NOT NULL DEFAULT '',
  `ru` text NOT NULL,
  `date_old` int(11) NOT NULL DEFAULT 0,
  `cat` mediumint(9) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inwd_items`
--

DROP TABLE IF EXISTS `inwd_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inwd_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `t_item` varchar(100) NOT NULL,
  `inward_proc` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inwd_receipts`
--

DROP TABLE IF EXISTS `inwd_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inwd_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `t_orno` int(11) NOT NULL,
  `t_pono` int(11) NOT NULL,
  `t_srnb` int(11) NOT NULL,
  `w_date` date NOT NULL,
  `w_user` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inwd_shippings`
--

DROP TABLE IF EXISTS `inwd_shippings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inwd_shippings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `sn` varchar(100) NOT NULL,
  `t_orno` int(11) NOT NULL,
  `t_pono` int(11) NOT NULL,
  `t_item` varchar(100) NOT NULL,
  `t_qucs` float NOT NULL,
  `w_date` date NOT NULL,
  `w_user` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `isr`
--

DROP TABLE IF EXISTS `isr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `poster_id` int(11) NOT NULL,
  `bu_from_id` int(11) NOT NULL,
  `bu_to_id` int(11) NOT NULL,
  `cuno` varchar(10) NOT NULL COMMENT 'ERP customer id',
  `ttype` varchar(60) NOT NULL COMMENT 'Transportation type',
  `cnum` varchar(60) NOT NULL COMMENT 'Container number',
  `tnum` varchar(60) NOT NULL COMMENT 'Tracking number',
  `dt_outb` date NOT NULL COMMENT 'Date outbound',
  `dt_ship` date NOT NULL COMMENT 'Date shipment',
  `dt_eta` date NOT NULL COMMENT 'Date ETA',
  `status` varchar(20) NOT NULL,
  `notes` text NOT NULL,
  `doc_ship` varchar(255) NOT NULL COMMENT 'Shiping document',
  `doc_qa` varchar(255) NOT NULL COMMENT 'Quality document',
  `doc_inv` varchar(255) NOT NULL COMMENT 'Invoice document',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `isr_lines`
--

DROP TABLE IF EXISTS `isr_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isr_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `t_dino` varchar(60) NOT NULL COMMENT 'Packing Slip number',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=915 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lists`
--

DROP TABLE IF EXISTS `lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(9) NOT NULL DEFAULT 0,
  `list_name` varchar(100) NOT NULL DEFAULT '',
  `list_key` varchar(100) NOT NULL DEFAULT '',
  `list_item` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `list_name` (`list_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4116 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lists_items`
--

DROP TABLE IF EXISTS `lists_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(9) NOT NULL DEFAULT 0,
  `list_name` varchar(50) NOT NULL DEFAULT '',
  `list_key` varchar(50) NOT NULL DEFAULT '',
  `list_item` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `erp` int(3) NOT NULL DEFAULT 0,
  `role` varchar(6) NOT NULL COMMENT 'sso?',
  `factory` char(1) NOT NULL DEFAULT 'N',
  `warehouse` char(1) NOT NULL DEFAULT 'N',
  `sph` char(1) NOT NULL DEFAULT 'N' COMMENT 'location is a Spare Parts Hub ?',
  `sh` char(1) NOT NULL DEFAULT 'N' COMMENT 'Service Hub',
  `hq` char(1) NOT NULL DEFAULT '',
  `location` varchar(50) NOT NULL DEFAULT '',
  `business_unit` varchar(60) NOT NULL,
  `juridical_location_id` int(11) NOT NULL,
  `repid` int(11) NOT NULL,
  `public` varchar(1) NOT NULL,
  `hidden` varchar(1) NOT NULL,
  `disable` varchar(1) NOT NULL,
  `region` varchar(50) NOT NULL DEFAULT '',
  `company_name` varchar(100) NOT NULL DEFAULT '',
  `street1` varchar(50) NOT NULL DEFAULT '',
  `street2` varchar(50) NOT NULL DEFAULT '',
  `town` varchar(50) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(50) NOT NULL DEFAULT '',
  `postal_code` varchar(50) NOT NULL DEFAULT '',
  `tel` varchar(50) NOT NULL DEFAULT '',
  `parts_tel` varchar(50) NOT NULL,
  `fax` varchar(50) NOT NULL DEFAULT '',
  `parts_fax` varchar(50) NOT NULL,
  `firewall` varchar(20) NOT NULL DEFAULT '',
  `router` varchar(20) NOT NULL DEFAULT '',
  `default_route` varchar(20) NOT NULL DEFAULT '',
  `fw_outside_network` varchar(20) NOT NULL DEFAULT '',
  `fw_outside_ip` varchar(20) NOT NULL DEFAULT '',
  `fw_inside_ip` varchar(20) NOT NULL DEFAULT '',
  `fw_inside_network` varchar(255) NOT NULL DEFAULT '',
  `wins1` varchar(20) NOT NULL DEFAULT '',
  `wins2` varchar(20) NOT NULL DEFAULT '',
  `dns1` varchar(20) NOT NULL DEFAULT '',
  `dns2` varchar(20) NOT NULL DEFAULT '',
  `email_domain` varchar(50) NOT NULL,
  `enc` varchar(20) NOT NULL DEFAULT '',
  `weather` varchar(10) NOT NULL DEFAULT '',
  `sph_email` varchar(50) NOT NULL COMMENT 'SPH email',
  `sh_email` varchar(50) NOT NULL COMMENT 'Service TOC email',
  `sh_tel` varchar(60) NOT NULL COMMENT 'Service Hub phone',
  `dcur` varchar(3) NOT NULL,
  `timezone` varchar(40) NOT NULL,
  `region_id` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `erp` (`erp`),
  KEY `location` (`location`),
  KEY `repid` (`repid`),
  KEY `juridical_location_id` (`juridical_location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations_kpi_sso`
--

DROP TABLE IF EXISTS `locations_kpi_sso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations_kpi_sso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ynam` year(4) NOT NULL COMMENT 'Applicable year',
  `mnam` tinyint(4) NOT NULL COMMENT 'Applicable month',
  `sso_tot_sls` decimal(10,2) NOT NULL,
  `sso_tot_ar` decimal(10,2) NOT NULL,
  `sso_fin_gds` decimal(10,2) NOT NULL,
  `sph_tot_sls` decimal(10,2) NOT NULL,
  `sph_net_inv_val` decimal(10,2) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `parent_id` (`parent_id`,`ynam`,`mnam`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations_sph`
--

DROP TABLE IF EXISTS `locations_sph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations_sph` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `userid` varchar(50) DEFAULT NULL,
  `timestamp` varchar(25) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=6200878 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manuals`
--

DROP TABLE IF EXISTS `manuals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `erp` int(3) NOT NULL DEFAULT 0,
  `brand` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `description` text NOT NULL,
  `features` text NOT NULL,
  `lang` varchar(20) NOT NULL COMMENT 'Lanuage',
  `status` varchar(20) NOT NULL DEFAULT 'PRELIMINARY' COMMENT 'Status of the Manual (Released or Preliminary)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17327 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manuals_diag`
--

DROP TABLE IF EXISTS `manuals_diag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manuals_diag` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `dt_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `erp` int(3) NOT NULL DEFAULT 0,
  `factory_num` varchar(50) NOT NULL DEFAULT '',
  `rev` varchar(11) NOT NULL DEFAULT '',
  `category` varchar(30) NOT NULL,
  `doc_type` varchar(20) NOT NULL DEFAULT '',
  `endescription` text NOT NULL,
  `frdescription` text NOT NULL COMMENT 'alternative language description',
  `diagram_filename` varchar(255) NOT NULL DEFAULT '',
  `ennotes` text NOT NULL,
  `frnotes` text NOT NULL COMMENT 'alternative language notes',
  `hmd5` varchar(50) NOT NULL COMMENT 'hash md5',
  `filesize` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `doc_type` (`doc_type`),
  KEY `category` (`category`),
  KEY `factory_num` (`factory_num`),
  KEY `parent_id` (`parent_id`),
  KEY `diagram_filename` (`diagram_filename`),
  KEY `hmd5` (`hmd5`)
) ENGINE=InnoDB AUTO_INCREMENT=747811 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manuals_docs`
--

DROP TABLE IF EXISTS `manuals_docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manuals_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `item` varchar(10) NOT NULL DEFAULT '',
  `doc_num` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `doc_num` (`doc_num`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1012365 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manuals_downloads`
--

DROP TABLE IF EXISTS `manuals_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manuals_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt_entered` date NOT NULL,
  `poster_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `er_id` int(11) NOT NULL,
  `erp` int(3) NOT NULL,
  `std_manual` tinyint(4) NOT NULL,
  `full_manual` tinyint(4) NOT NULL,
  `extra_cd` tinyint(4) NOT NULL,
  `chapter_5` tinyint(4) NOT NULL,
  `dt_delivery` date NOT NULL,
  `downloaded` tinyint(4) NOT NULL DEFAULT 0,
  `hide` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manuals_parts`
--

DROP TABLE IF EXISTS `manuals_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manuals_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `item` varchar(20) NOT NULL DEFAULT '',
  `pn` varchar(50) NOT NULL DEFAULT '',
  `vendor_pn` varchar(20) NOT NULL DEFAULT '',
  `ocm_pn` varchar(20) NOT NULL DEFAULT '',
  `qty` varchar(20) NOT NULL DEFAULT '',
  `um` varchar(10) NOT NULL DEFAULT '',
  `en` varchar(100) NOT NULL DEFAULT '',
  `fr` varchar(100) NOT NULL DEFAULT '',
  `dscu` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Desc in UTF8',
  `note` text NOT NULL,
  `group_p` varchar(2) DEFAULT NULL,
  `group_m` varchar(2) DEFAULT NULL,
  `group_o` varchar(2) DEFAULT NULL,
  `group_c` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `pn` (`pn`),
  KEY `vendor_pn` (`vendor_pn`),
  KEY `en` (`en`),
  KEY `ocm_pn` (`ocm_pn`)
) ENGINE=InnoDB AUTO_INCREMENT=7817809 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meap`
--

DROP TABLE IF EXISTS `meap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `parent_module` varchar(5) NOT NULL,
  `factory` int(11) NOT NULL DEFAULT 0,
  `product_type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '0',
  `status` varchar(15) NOT NULL DEFAULT 'PROPOSAL',
  `pvt` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Private for members only?',
  `pcd_0` date NOT NULL,
  `pcd_1` date NOT NULL,
  `pcd_2` date NOT NULL,
  `pcd_3` date NOT NULL,
  `pcd_4` date NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `date_closed` date NOT NULL DEFAULT '0000-00-00',
  `date_suspended` date NOT NULL DEFAULT '0000-00-00',
  `days_suspended` int(11) NOT NULL DEFAULT 0,
  `type` varchar(15),
  `purpose` varchar(25) NOT NULL DEFAULT '',
  `short_desc` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `resolution` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `filename` varchar(254) NOT NULL,
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `final_fweight` int(11) NOT NULL DEFAULT 0,
  `poster` int(11) NOT NULL DEFAULT 0,
  `proj_leader` int(11) NOT NULL DEFAULT 0,
  `econ_capitalized` varchar(1) NOT NULL,
  `econ_currency` varchar(3) NOT NULL,
  `econ_target_dh` int(5) DEFAULT NULL,
  `econ_eac_dh` int(5) DEFAULT NULL,
  `econ_actual_dh` int(5) DEFAULT NULL,
  `econ_actual_dh_dt` datetime NOT NULL,
  `econ_target_sea` int(9) DEFAULT NULL,
  `econ_eac_sea` int(9) DEFAULT NULL,
  `econ_actual_sea` int(9) DEFAULT NULL,
  `econ_actual_sea_dt` datetime NOT NULL,
  `econ_target_maoc` int(9) DEFAULT NULL,
  `econ_eac_maoc` int(9) DEFAULT NULL,
  `econ_actual_maoc` int(9) DEFAULT NULL,
  `econ_actual_maoc_dt` datetime NOT NULL,
  `econ_target_pmc` int(9) DEFAULT NULL,
  `econ_eac_pmc` int(9) DEFAULT NULL,
  `econ_actual_pmc` int(9) DEFAULT NULL,
  `econ_actual_pmc_dt` datetime NOT NULL,
  `econ_target_plh` int(5) DEFAULT NULL,
  `econ_eac_plh` int(5) DEFAULT NULL,
  `econ_actual_plh` int(5) DEFAULT NULL,
  `econ_actual_plh_dt` datetime NOT NULL,
  `econ_target_material` int(9) DEFAULT NULL,
  `econ_eac_material` int(9) DEFAULT NULL,
  `econ_actual_material` int(9) DEFAULT NULL,
  `econ_actual_material_dt` datetime NOT NULL,
  `econ_target_hours` int(5) DEFAULT NULL,
  `econ_eac_hours` int(5) DEFAULT NULL,
  `econ_actual_hours` int(5) DEFAULT NULL,
  `econ_actual_hours_dt` datetime NOT NULL,
  `cost_calculation_method` varchar(35) NOT NULL DEFAULT 'Non Applicable (non E-product)',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `factory` (`factory`),
  KEY `product_type` (`product_type`),
  KEY `model` (`model`),
  KEY `status` (`status`),
  KEY `poster` (`poster`),
  KEY `type` (`type`),
  KEY `initiator` (`proj_leader`),
  KEY `idx_meap_parent_id` (`parent_id`),
  KEY `idx_meap_parent_module` (`parent_module`),
  KEY `idx_meap_parent_id_parent_module` (`parent_id`,`parent_module`)
) ENGINE=InnoDB AUTO_INCREMENT=423 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meap_archive`
--

DROP TABLE IF EXISTS `meap_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meap_archive` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `archive_type` varchar(100) NOT NULL,
  `archived_value` varchar(100) NOT NULL,
  `archive_dt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2290 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meap_pcd`
--

DROP TABLE IF EXISTS `meap_pcd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meap_pcd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `original_target` date DEFAULT NULL,
  `management_target` date DEFAULT NULL,
  `current_target` date DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2356 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meap_factories`
--

DROP TABLE IF EXISTS `meap_factories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meap_factories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `factory_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `factory_id` (`factory_id`),
  KEY `parent_id` (`parent_id`),
  UNIQUE KEY `unique_factory_per_meap` (`parent_id`,`factory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mfg_margins`
--

DROP TABLE IF EXISTS `mfg_margins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mfg_margins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `er_id` int(11) NOT NULL,
  `month` mediumint(6) NOT NULL,
  `year` mediumint(6) NOT NULL,
  `cur` varchar(3) NOT NULL,
  `factory_rev` decimal(11,2) NOT NULL,
  `std_hour` decimal(11,2) NOT NULL,
  `act_hour` decimal(11,2) NOT NULL,
  `std_lab_cost` decimal(11,2) NOT NULL,
  `act_lab_cost` decimal(11,2) NOT NULL,
  `std_mat` decimal(11,2) NOT NULL,
  `act_mat` decimal(11,2) NOT NULL,
  `std_other_mat` decimal(11,2) NOT NULL,
  `act_other_mat` decimal(11,2) NOT NULL,
  `std_other_dir_cost` decimal(11,2) NOT NULL,
  `act_other_dir_cost` decimal(11,2) NOT NULL,
  `comment` text NOT NULL,
  `ocp_hours` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `er_id` (`er_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2450 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration_csr`
--

DROP TABLE IF EXISTS `migration_csr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_csr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `csr_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `sso_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `work_type` varchar(60) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `send_tech` varchar(1) NOT NULL,
  `dt_work` datetime NOT NULL,
  `dt_sche` datetime NOT NULL,
  `con_id` int(11) NOT NULL,
  `apc` varchar(10) NOT NULL,
  `hourmeter` int(11) NOT NULL,
  `short_desc` varchar(250) NOT NULL,
  `int_desc` text NOT NULL,
  `ext_desc` text NOT NULL,
  `erp_inv` varchar(20) NOT NULL,
  `module` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL,
  `ship_to` text NOT NULL,
  `bill_to` varchar(60) NOT NULL,
  `bill_instruction` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `csr_id` (`csr_id`),
  KEY `entered_by` (`entered_by`),
  KEY `sso_id` (`sso_id`),
  KEY `tech_id` (`tech_id`),
  KEY `con_id` (`con_id`),
  KEY `module` (`module`,`module_id`),
  KEY `csr_work_type_index` (`work_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2364 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration_csr_xref`
--

DROP TABLE IF EXISTS `migration_csr_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_csr_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `csr_id` int(11) NOT NULL,
  `csr2_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `csr_id` (`csr_id`),
  KEY `csr2_id` (`csr2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2364 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration_sr`
--

DROP TABLE IF EXISTS `migration_sr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_sr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `sso_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `work_type` varchar(20) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `dt_work` datetime NOT NULL,
  `dt_sche` datetime NOT NULL,
  `con_id` int(11) NOT NULL,
  `apc` varchar(10) NOT NULL,
  `hourmeter` int(11) NOT NULL,
  `short_desc` varchar(250) NOT NULL,
  `int_desc` text NOT NULL,
  `ext_desc` text NOT NULL,
  `erp_inv` varchar(20) NOT NULL,
  `module` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `entered_by` (`entered_by`),
  KEY `sso_id` (`sso_id`),
  KEY `tech_id` (`tech_id`),
  KEY `con_id` (`con_id`),
  KEY `module` (`module`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39433 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration_sr_xref`
--

DROP TABLE IF EXISTS `migration_sr_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_sr_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sr_id` int(11) NOT NULL,
  `csr_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36369 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mim`
--

DROP TABLE IF EXISTS `mim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `priv` varchar(1) NOT NULL COMMENT 'Private?',
  `typa` varchar(50) NOT NULL COMMENT 'MIM type',
  `cuid` int(11) NOT NULL COMMENT 'customer id',
  `cor_id` int(11) NOT NULL COMMENT 'competitor id',
  `type_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL DEFAULT '0',
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `dt` date NOT NULL DEFAULT '0000-00-00',
  `short_desc` varchar(75) NOT NULL,
  `dsca` text NOT NULL,
  `poster` int(11) NOT NULL DEFAULT 0,
  `initiator` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3324 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mim_not`
--

DROP TABLE IF EXISTS `mim_not`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mim_not` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `typa` varchar(50) NOT NULL COMMENT 'MIM type',
  `cuid` int(11) NOT NULL COMMENT 'customer id',
  `cor_id` int(11) NOT NULL COMMENT 'competitor id',
  `type_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL DEFAULT '0',
  `dt` date NOT NULL DEFAULT '0000-00-00',
  `poster` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=553 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mimetypes`
--

DROP TABLE IF EXISTS `mimetypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mimetypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ext` varchar(4) NOT NULL DEFAULT '',
  `mimetype` varchar(50) NOT NULL DEFAULT '',
  `icon_path` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inv`
--

DROP TABLE IF EXISTS `mis_inv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `d_wrty` date NOT NULL COMMENT 'warranty expiration',
  `qty` int(11) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `serial` varchar(20) NOT NULL DEFAULT '',
  `man_serial` varchar(40) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `serial` (`serial`),
  KEY `man_serial` (`man_serial`)
) ENGINE=InnoDB AUTO_INCREMENT=15191 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inv_comments`
--

DROP TABLE IF EXISTS `mis_inv_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inv_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `poster` int(11) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=947 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inv_comp`
--

DROP TABLE IF EXISTS `mis_inv_comp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inv_comp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `qty` int(11) NOT NULL DEFAULT 0,
  `description` varchar(100) NOT NULL DEFAULT '',
  `serial` varchar(20) NOT NULL DEFAULT '',
  `man_serial` varchar(40) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9605 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inv_logs`
--

DROP TABLE IF EXISTS `mis_inv_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inv_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) DEFAULT NULL,
  `dt` datetime NOT NULL,
  `comp` varchar(3) DEFAULT NULL,
  `employee` varchar(10) NOT NULL DEFAULT '',
  `supervisor` varchar(10) NOT NULL,
  `equipment` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee` (`employee`),
  KEY `equipment` (`equipment`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_assignments`
--

DROP TABLE IF EXISTS `mis_inventory_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` date NOT NULL,
  `poster_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `destination_type` varchar(20) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `dt_from` date NOT NULL,
  `dt_to` date NOT NULL,
  `comment` text NOT NULL,
  `task_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2004 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_brands`
--

DROP TABLE IF EXISTS `mis_inventory_brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `support_url` varchar(250) NOT NULL,
  `disable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_buildings`
--

DROP TABLE IF EXISTS `mis_inventory_buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_buildings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `disable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_contracts`
--

DROP TABLE IF EXISTS `mis_inventory_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `account` varchar(50) NOT NULL,
  `support` varchar(50) NOT NULL,
  `link` varchar(100) NOT NULL,
  `poster` int(11) NOT NULL,
  `start_dt` date NOT NULL,
  `end_dt` date NOT NULL,
  `filename` varchar(255) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_item_types`
--

DROP TABLE IF EXISTS `mis_inventory_item_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_item_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(20) NOT NULL,
  `short_desc` varchar(3) NOT NULL,
  `length` tinyint(1) DEFAULT 6,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_inventory_items`
--

DROP TABLE IF EXISTS `mis_inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` date NOT NULL,
  `dt_warranty_end` date NOT NULL,
  `type_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `brand_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturer_sn` varchar(100) NOT NULL,
  `tld_sn` varchar(20) NOT NULL,
  `fixasset_id` varchar(20) NOT NULL,
  `state` varchar(50) NOT NULL,
  `buyer_bu_id` int(11) NOT NULL,
  `buyer_dpt_id` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `notify` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3202 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mis_tts`
--

DROP TABLE IF EXISTS `mis_tts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mis_tts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(20) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT 'OTHER',
  `userid` int(11) NOT NULL DEFAULT 0,
  `location` int(11) NOT NULL DEFAULT 0,
  `owner` int(11) NOT NULL DEFAULT 0,
  `assignee` int(11) NOT NULL DEFAULT 0,
  `ifactor` int(10) NOT NULL DEFAULT 1,
  `budget_hours` int(10) NOT NULL,
  `dt_opened` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_spec` datetime NOT NULL COMMENT 'date when spec received',
  `eta` date NOT NULL DEFAULT '0000-00-00',
  `dt_closed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `problem` text NOT NULL,
  `est_cost` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `solution` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`),
  KEY `userid` (`userid`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB AUTO_INCREMENT=1985 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_costs`
--

DROP TABLE IF EXISTS `mod_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `date_open` date NOT NULL,
  `poster` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `um` varchar(10) NOT NULL COMMENT 'Unit of measure',
  `qty` int(11) NOT NULL COMMENT 'Quantity',
  `cur` varchar(3) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`module`),
  KEY `poster` (`poster`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=7418 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_costs_type`
--

DROP TABLE IF EXISTS `mod_costs_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_costs_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `type` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_faq`
--

DROP TABLE IF EXISTS `mod_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_faq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `symptom` text NOT NULL,
  `problem` text NOT NULL,
  `solution` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=5663 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_files`
--

DROP TABLE IF EXISTS `mod_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(10) NOT NULL,
  `poster` int(11) NOT NULL COMMENT 'poster',
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `fid` int(11) NOT NULL COMMENT 'file id',
  `level` int(11) NOT NULL,
  `expiration_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`module`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=138040 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO mod_files (id, parent_id, module, poster, date, description, filename, fid, level) VALUES (61, 1, 'TOC', 1, '2022-12-07', 'just toc test file', 'file.txt', 1, 2);
INSERT INTO mod_files (id, parent_id, module, poster, date, description, filename, fid, level) VALUES (62, 59, 'SB3', 1, '2022-12-07', 'use gitkeep file', '.gitkeep', 2, 0);

--
-- Table structure for table `mod_keys`
--

DROP TABLE IF EXISTS `mod_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `key1` varchar(50) NOT NULL,
  `key2` varchar(50) NOT NULL,
  `key3` varchar(50) NOT NULL,
  `key4` varchar(50) NOT NULL,
  `key5` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_id` (`parent_id`,`module`,`type`,`key1`,`key2`,`key3`,`key4`,`key5`)
) ENGINE=InnoDB AUTO_INCREMENT=63207 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_kpi`
--

DROP TABLE IF EXISTS `mod_kpi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_kpi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(60) NOT NULL,
  `key1` varchar(60) NOT NULL,
  `key2` varchar(60) NOT NULL,
  `key3` varchar(60) NOT NULL,
  `y` year(4) NOT NULL COMMENT 'Applicable year',
  `m` varchar(2) NOT NULL COMMENT 'Applicable month',
  `name` varchar(60) NOT NULL,
  `val` float NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mod_kpi_key1_index` (`key1`),
  KEY `mod_kpi_key2_index` (`key2`),
  KEY `mod_kpi_key3_index` (`key3`),
  KEY `mod_kpi_module_index` (`module`),
  KEY `mod_kpi_parent_id_index` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14714 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_kpireview`
--

DROP TABLE IF EXISTS `mod_kpireview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_kpireview` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name_id` varchar(256) NOT NULL,
  `xval` varchar(100) NOT NULL,
  `zval` varchar(100) NOT NULL,
  `last_yval` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_idx` (`name_id`,`xval`,`zval`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_kpireview_comments`
--

DROP TABLE IF EXISTS `mod_kpireview_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_kpireview_comments` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `parent_id` int(20) NOT NULL,
  `poster` int(11) NOT NULL,
  `comment` text NOT NULL,
  `dt` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_labors`
--

DROP TABLE IF EXISTS `mod_labors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_labors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(60) NOT NULL,
  `dt_open` datetime NOT NULL,
  `poster_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dt_work` datetime NOT NULL,
  `description` varchar(250) NOT NULL,
  `nb_hours` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`module`),
  KEY `poster_id` (`poster_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=881 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_links`
--

DROP TABLE IF EXISTS `mod_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(4) NOT NULL,
  `type` varchar(4) NOT NULL,
  `item` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id_2` (`parent_id`,`module`),
  KEY `type` (`type`,`item`)
) ENGINE=InnoDB AUTO_INCREMENT=316889 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO mod_links (id, parent_id, module, type, item) VALUES (1, 58, 'WC', 'TOC', 1);
INSERT INTO mod_links (id, parent_id, module, type, item) VALUES (2, 19, 'TOC', 'TOC', 17);
INSERT INTO mod_links (id, parent_id, module, type, item) VALUES (3, 58, 'WC', 'TOC', 19);
INSERT INTO mod_links (id, parent_id, module, type, item) VALUES (4, 5001, 'PDC', 'TOC', 25);
INSERT INTO mod_links (id, parent_id, module, type, item) VALUES (5, 25, 'TOC', 'PDC', 5002);

--
-- Table structure for table `mod_lists`
--

DROP TABLE IF EXISTS `mod_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(10) NOT NULL DEFAULT '',
  `list_name` varchar(50) DEFAULT NULL,
  `list_key` varchar(50) NOT NULL COMMENT 'list_key',
  `list_key2` varchar(50) NOT NULL,
  `value` varchar(254) DEFAULT NULL COMMENT 'value',
  `value2` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `list_name` (`list_name`),
  KEY `list_key` (`list_key`),
  KEY `list_key2` (`list_key2`),
  KEY `value` (`value`),
  KEY `parent_id_2` (`parent_id`,`module`,`list_name`,`list_key`)
) ENGINE=InnoDB AUTO_INCREMENT=267423 DEFAULT CHARSET=latin1 COMMENT='Generic lists';
/*!40101 SET character_set_client = @saved_cs_client */;
# SOL18819 linked to /sales/orders/2
INSERT INTO mod_lists (parent_id, module, list_name, list_key, list_key2, value, value2)
VALUES
    (18819, 'SOL', 'DCUR', '', '', 'EUR', ''),
    (18819, 'SOL', 'CCUR', '', '', 'USD', '');
--
-- Table structure for table `mod_logs`
--

DROP TABLE IF EXISTS `mod_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(10) NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `poster` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `log_num` int(11) NOT NULL DEFAULT 0 COMMENT 'Log number',
  PRIMARY KEY (`id`),
  KEY `poster` (`poster`),
  KEY `parent_id` (`parent_id`,`module`),
  KEY `module` (`module`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2030870 DEFAULT CHARSET=latin1 COMMENT='Generic logs linked to module docs';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO mod_logs (parent_id, module, date, poster, comment, log_num)
VALUES
    (62, 'WC', '2026-01-26 09:55:57', 2377, 'WC Comment', 0),
    (5001, 'PDC', '2026-01-26 09:55:57', 2377, 'PDC 5001 Comment', 0),
    (5002, 'PDC', '2026-01-26 09:55:57', 2377, 'PDC 5002 Comment', 0)
;

--
-- Table structure for table `mod_models`
--

DROP TABLE IF EXISTS `mod_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(10) NOT NULL DEFAULT '',
  `type` varchar(254) DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `model` (`model`),
  KEY `parent_id` (`parent_id`,`module`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=106648 DEFAULT CHARSET=latin1 COMMENT='Generic logs linked to module docs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_not`
--

DROP TABLE IF EXISTS `mod_not`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_not` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `uid` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `email_from` varchar(260) NOT NULL,
  `email_to` text NOT NULL,
  `email_cc` text NOT NULL,
  `email_bcc` text NOT NULL,
  `email_subject` text NOT NULL,
  `email_body` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `parent_id` (`parent_id`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=8729 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_org`
--

DROP TABLE IF EXISTS `mod_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_org` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `div_id` int(11) NOT NULL COMMENT 'division',
  `bu_id` int(11) NOT NULL COMMENT 'business unit',
  `dpt_id` int(11) NOT NULL COMMENT 'department',
  `fct_id` int(11) NOT NULL COMMENT 'function',
  PRIMARY KEY (`id`),
  KEY `div_id` (`div_id`),
  KEY `bu_id` (`bu_id`),
  KEY `dpt_id` (`dpt_id`),
  KEY `fct_id` (`fct_id`),
  KEY `parent_id` (`parent_id`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=13097 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mod_parts`
--

DROP TABLE IF EXISTS `mod_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `pn` varchar(60) NOT NULL,
  `dsc` varchar(60) NOT NULL,
  `qty` int(11) NOT NULL,
  `um` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=9304 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `family` varchar(50) NOT NULL,
  `model` varchar(255) NOT NULL DEFAULT '',
  `hide` int(11) NOT NULL DEFAULT 0,
  `erpid` int(11) DEFAULT NULL,
  `light` tinyint(1) NOT NULL DEFAULT 0,
  `finance_family` varchar(255) DEFAULT NULL,
  `innovative_level` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `model` (`model`)
) ENGINE=InnoDB AUTO_INCREMENT=992 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ncr`
--

DROP TABLE IF EXISTS `ncr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ncr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `factory` int(5) NOT NULL DEFAULT 0,
  `process` varchar(30) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `hours` int(11) NOT NULL DEFAULT 1,
  `reported_by` varchar(30) NOT NULL DEFAULT '',
  `t_emno` int(11) DEFAULT 0,
  `model` varchar(255) NOT NULL,
  `problem` text NOT NULL,
  `short_desc` varchar(100) NOT NULL DEFAULT '',
  `solution` text NOT NULL,
  `responsible` varchar(10) NOT NULL DEFAULT '',
  `vendor_erp` int(3) DEFAULT 0,
  `vendor_id` varchar(10) NOT NULL DEFAULT '',
  `vendor_name` varchar(50) NOT NULL DEFAULT '',
  `po_num` varchar(20) NOT NULL DEFAULT '',
  `rush` char(1) NOT NULL DEFAULT 'N',
  `charge_vendor` char(1) NOT NULL DEFAULT 'N',
  `failure_type` varchar(100) NOT NULL,
  `photo` varchar(100) NOT NULL DEFAULT '',
  `ifactor` varchar(4) NOT NULL,
  `investigation` text NOT NULL,
  `scrap` char(1) NOT NULL DEFAULT '',
  `rework` char(1) NOT NULL DEFAULT '',
  `FAI` char(1) NOT NULL,
  `use_as_is` char(1) NOT NULL DEFAULT '',
  `derogation` char(1) NOT NULL DEFAULT '',
  `return_vendor` char(1) NOT NULL DEFAULT '',
  `charge_vendor4repairs` char(1) NOT NULL DEFAULT '',
  `scar` char(1) NOT NULL DEFAULT '',
  `car` char(1) NOT NULL DEFAULT '',
  `other` char(1) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `repair_approver` varchar(30) NOT NULL DEFAULT '',
  `repair_sig_date` date NOT NULL DEFAULT '0000-00-00',
  `cost_cur` char(3) NOT NULL DEFAULT '',
  `cost_total` float DEFAULT NULL,
  `cost_break` text NOT NULL,
  `cost_ref` varchar(10) NOT NULL DEFAULT '',
  `non_quality_cost` float NOT NULL,
  `t_ninv` varchar(11) DEFAULT '',
  `containment` varchar(1) DEFAULT '' NOT NULL,
  PRIMARY KEY (`id`),
  KEY `factory` (`factory`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=57123 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ncr_files`
--

DROP TABLE IF EXISTS `ncr_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ncr_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  `description` text NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16317 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ncr_parts`
--

DROP TABLE IF EXISTS `ncr_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ncr_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `part_number` varchar(20) NOT NULL DEFAULT '',
  `short_desc` varchar(50) NOT NULL DEFAULT '',
  `qty` int(11) NOT NULL DEFAULT 0,
  `ref_type` varchar(10) NOT NULL DEFAULT '',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `sn` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `part_number` (`part_number`)
) ENGINE=InnoDB AUTO_INCREMENT=63714 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `en_title` varchar(200) NOT NULL DEFAULT '',
  `en` text NOT NULL,
  `fr_title` varchar(200) NOT NULL DEFAULT '',
  `fr` text NOT NULL,
  `es_title` varchar(200) NOT NULL DEFAULT '',
  `es` text NOT NULL,
  `zh_title` varchar(255) NOT NULL DEFAULT '',
  `zh` text NOT NULL,
  `pt_title` varchar(200) NOT NULL DEFAULT '',
  `pt` text NOT NULL,
  `de_title` varchar(200) NOT NULL DEFAULT '',
  `de` text NOT NULL,
  `ja_title` text NOT NULL,
  `ja` text NOT NULL,
  `ru_title` text NOT NULL,
  `ru` text NOT NULL,
  `cat` mediumint(9) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `cat` (`cat`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nto`
--

DROP TABLE IF EXISTS `nto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `category` varchar(10) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nto_aircraft`
--

DROP TABLE IF EXISTS `nto_aircraft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nto_aircraft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(9) NOT NULL DEFAULT 0,
  `aircraft_model` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nto_models`
--

DROP TABLE IF EXISTS `nto_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nto_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `model` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `odp`
--

DROP TABLE IF EXISTS `odp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `odp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(30) NOT NULL DEFAULT '0',
  `pdf` varchar(50) NOT NULL DEFAULT '',
  `xls` varchar(50) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ERP` varchar(50) NOT NULL COMMENT 'ERP Number',
  `ITEM` varchar(50) NOT NULL DEFAULT '',
  `t_dsca` varchar(100) NOT NULL DEFAULT '',
  `FR` varchar(100) NOT NULL DEFAULT '',
  `WT` double NOT NULL DEFAULT 0,
  `UM` varchar(50) NOT NULL DEFAULT '',
  `CURRENCY` varchar(50) NOT NULL DEFAULT '',
  `MIP` decimal(15,2) NOT NULL DEFAULT 0.00,
  `STDCUR` varchar(3) NOT NULL COMMENT 'standard cost currency',
  `STDCOST` decimal(15,2) NOT NULL DEFAULT 0.00,
  `LEAD` varchar(50) NOT NULL DEFAULT '',
  `ONHAND` double NOT NULL DEFAULT 0,
  `ONORDER` double NOT NULL DEFAULT 0,
  `ALLOCATED` double NOT NULL DEFAULT 0,
  `ECOSTK` double NOT NULL DEFAULT 0,
  `WHSE` varchar(50) NOT NULL DEFAULT '',
  `whse_fullname` varchar(50) NOT NULL,
  `SUP` varchar(11) NOT NULL DEFAULT '',
  `LSTDT` date NOT NULL DEFAULT '0000-00-00',
  `OB` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ITEM` (`ITEM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parts_trans`
--

DROP TABLE IF EXISTS `parts_trans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `t_eitm` varchar(30) NOT NULL,
  `t_clan` varchar(2) NOT NULL,
  `t_dsca` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'alternative lang',
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_eitm` (`t_eitm`,`t_clan`)
) ENGINE=InnoDB AUTO_INCREMENT=90799117 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parts_whse`
--

DROP TABLE IF EXISTS `parts_whse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts_whse` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `WHSEDESC` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parts_xref`
--

DROP TABLE IF EXISTS `parts_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `brand_from` varchar(50) NOT NULL DEFAULT '',
  `pn_from` varchar(50) NOT NULL DEFAULT '',
  `brand_to` varchar(50) NOT NULL DEFAULT '',
  `pn_to` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `pn_from` (`pn_from`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `nickname` varchar(50) NOT NULL,
  `div_id` int(11) NOT NULL,
  `bu_id` int(11) NOT NULL,
  `dpt_id` int(11) NOT NULL,
  `reports_to` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `fct_id` int(11) NOT NULL,
  `phone` varchar(25) NOT NULL DEFAULT '',
  `direct_phone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(20) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `home_phone` varchar(20) NOT NULL DEFAULT '',
  `photo` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `pass` varchar(35) NOT NULL,
  `address` text NOT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `last` varchar(20) DEFAULT NULL,
  `login` varchar(20) DEFAULT NULL,
  `hidden` int(1) NOT NULL DEFAULT 0,
  `email` varchar(255) NOT NULL DEFAULT '',
  `disabled` char(1) NOT NULL DEFAULT 'N',
  `baan_id` varchar(8) NOT NULL COMMENT 'Baan user ID',
  `baan_employee_id` int(11) NOT NULL COMMENT 'Baan Employee ID',
  `windows_id` varchar(40) NOT NULL,
  `contract_type` varchar(255) NOT NULL DEFAULT '',
  `coefficient` int(11) NOT NULL DEFAULT 100,
  `enable_at` date DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fct_id` (`fct_id`),
  KEY `div_id` (`div_id`),
  KEY `bu_id` (`bu_id`),
  KEY `dpt_id` (`dpt_id`),
  KEY `reports_to` (`reports_to`)
) ENGINE=InnoDB AUTO_INCREMENT=2358 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people_groups`
--

DROP TABLE IF EXISTS `people_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `email` varchar(50) NOT NULL DEFAULT '',
  `group_name` varchar(50) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `email` (`email`),
  KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10197 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people_groups_select`
--

DROP TABLE IF EXISTS `people_groups_select`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_groups_select` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `group_name` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `link` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people_password_history`
--

DROP TABLE IF EXISTS `people_password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `change_date` date DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29471 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people_tokens`
--

DROP TABLE IF EXISTS `people_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT 'people.id',
  `dt` int(11) NOT NULL COMMENT 'timestamp',
  `ip` varchar(15) NOT NULL COMMENT 'user remote ip',
  `env` varchar(250) NOT NULL COMMENT 'User environement',
  `token` varchar(32) NOT NULL COMMENT 'token in MD5',
  `jwt` text DEFAULT NULL COMMENT 'Store JWT to share with other application',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=17467 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_answers`
--

DROP TABLE IF EXISTS `pi_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `answer` varchar(50) NOT NULL,
  `answerComponent` varchar(50) NOT NULL,
  `answerModel` varchar(50) NOT NULL,
  `answerSerial` varchar(50) NOT NULL,
  `answerBrand` varchar(50) NOT NULL,
  `active` varchar(1) NOT NULL,
  `entered_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `derogation` varchar(50) NOT NULL,
  `d_entered_by` int(11) NOT NULL,
  `d_entered_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`active`),
  KEY `created_on` (`created_on`),
  KEY `pi_answers__parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2360 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_config_func`
--

DROP TABLE IF EXISTS `pi_config_func`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_config_func` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `koop` varchar(3) NOT NULL,
  `cart` varchar(1) NOT NULL,
  `inspect` varchar(1) NOT NULL,
  `punchout` varchar(1) NOT NULL,
  `indirect` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_crab_eap`
--

DROP TABLE IF EXISTS `pi_crab_eap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_crab_eap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `comp` int(3) NOT NULL,
  `t_cprj` varchar(20) NOT NULL,
  `t_cprj_baan` int(6) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL,
  `t_item` varchar(15) NOT NULL,
  `model` varchar(40) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit` (`unit`,`comp`,`t_cprj`,`t_pdno`),
  KEY `comp` (`comp`,`t_cprj`,`t_pdno`,`t_opno`),
  KEY `parent_id` (`parent_id`,`comp`,`t_cprj`,`t_pdno`,`t_opno`),
  KEY `comp_2` (`comp`,`t_cprj`,`t_pdno`,`t_opno`,`question_id`),
  KEY `parent_id_2` (`parent_id`,`comp`,`t_cprj`,`t_pdno`,`t_opno`,`question_id`),
  KEY `t_opno` (`t_opno`,`date`),
  KEY `idx_pi_crab_eap_question_id` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_family`
--

DROP TABLE IF EXISTS `pi_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_family` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family` varchar(100) NOT NULL,
  `pi_group` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_family_matrix`
--

DROP TABLE IF EXISTS `pi_family_matrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_family_matrix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family` varchar(100) NOT NULL,
  `factory` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_notifications`
--

DROP TABLE IF EXISTS `pi_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `t_cprj` varchar(20) NOT NULL,
  `t_cprj_baan` int(6) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL,
  `action` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `assignee` varchar(200) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comp` (`comp`),
  KEY `t_cprj` (`t_cprj`),
  KEY `t_pdno` (`t_pdno`),
  KEY `t_opno` (`t_opno`)
) ENGINE=InnoDB AUTO_INCREMENT=4273 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_operations_status`
--

DROP TABLE IF EXISTS `pi_operations_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_operations_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `t_cprj` varchar(20) NOT NULL,
  `t_cprj_baan` int(6) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL COMMENT 'Operation Number',
  `first_answer` datetime NOT NULL,
  `status` varchar(15) NOT NULL,
  `date_status` date NOT NULL,
  `warehouseNotified` varchar(3) NOT NULL,
  `wh_not_date` datetime NOT NULL,
  `wh_not_delay` varchar(50) NOT NULL,
  `wh_not_user` int(11) NOT NULL,
  `wh_out_date` datetime NOT NULL,
  `wh_out_user` varchar(50) NOT NULL,
  `wh_out_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comp` (`comp`),
  KEY `t_cprj` (`t_cprj`),
  KEY `t_pdno` (`t_pdno`),
  KEY `t_opno` (`t_opno`)
) ENGINE=InnoDB AUTO_INCREMENT=453 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_questions`
--

DROP TABLE IF EXISTS `pi_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `t_opno` varchar(15) NOT NULL COMMENT 'Operation Number',
  `t_item` longtext NOT NULL COMMENT 'Baan PN',
  `model` varchar(40) NOT NULL,
  `owner` varchar(3) NOT NULL,
  `mtl` int(11) NOT NULL DEFAULT 0,
  `sha` int(11) NOT NULL DEFAULT 0,
  `she` int(11) NOT NULL DEFAULT 0,
  `sor` int(11) NOT NULL DEFAULT 0,
  `stl` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `wim` int(11) NOT NULL DEFAULT 0,
  `wux` int(11) NOT NULL DEFAULT 0,
  `leb` int(11) NOT NULL DEFAULT 0,
  `aer` int(11) NOT NULL DEFAULT 0,
  `pow` int(11) NOT NULL DEFAULT 0,
  `mai` int(11) NOT NULL DEFAULT 0,
  `wol` int(11) NOT NULL DEFAULT 0,
  `subject_en` varchar(100) NOT NULL,
  `subject_fr` varchar(100) NOT NULL,
  `subject_zh` varchar(100) NOT NULL,
  `desc_en` text NOT NULL,
  `desc_fr` text NOT NULL,
  `desc_zh` text NOT NULL,
  `position` int(11) NOT NULL,
  `help_en` varchar(100) NOT NULL,
  `help_fr` varchar(100) NOT NULL,
  `help_zh` varchar(100) NOT NULL,
  `attachment_en` int(11) NOT NULL,
  `attachment_fr` int(11) NOT NULL,
  `attachment_zh` int(11) NOT NULL,
  `answer_type` varchar(30) NOT NULL,
  `answer_unit` varchar(30) NOT NULL,
  `component_sn` varchar(40) NOT NULL,
  `match_list` int(11) NOT NULL,
  `answer_max` varchar(30) NOT NULL,
  `answer_min` varchar(30) NOT NULL,
  `non_conformity` varchar(1) NOT NULL COMMENT 'Non conformity value Y/N',
  `created_on` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int(11) NOT NULL,
  `dt_validity` date NOT NULL,
  `dt_expiration` date NOT NULL,
  `create_mode` varchar(20) NOT NULL,
  `gt1` varchar(1) NOT NULL COMMENT 'Y/N',
  `gt3` varchar(1) NOT NULL COMMENT 'Y/N',
  `active` varchar(1) NOT NULL COMMENT 'Y/N',
  PRIMARY KEY (`id`),
  KEY `t_opno` (`t_opno`,`dt_validity`,`dt_expiration`,`active`),
  KEY `idx_pi_questions_t_opno` (`t_opno`),
  KEY `idx_pi_questions_model` (`model`),
  KEY `idx_pi_questions_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=63380 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_questions_logs`
--

DROP TABLE IF EXISTS `pi_questions_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_questions_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `t_opno` varchar(15) NOT NULL COMMENT 'Operation Number',
  `t_item` longtext NOT NULL COMMENT 'Baan PN',
  `model` varchar(40) NOT NULL,
  `owner` varchar(3) NOT NULL,
  `mtl` int(11) NOT NULL DEFAULT 0,
  `sha` int(11) NOT NULL DEFAULT 0,
  `she` int(11) NOT NULL DEFAULT 0,
  `sor` int(11) NOT NULL DEFAULT 0,
  `stl` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `wim` int(11) NOT NULL DEFAULT 0,
  `wux` int(11) NOT NULL DEFAULT 0,
  `leb` int(11) NOT NULL DEFAULT 0,
  `aer` int(11) NOT NULL DEFAULT 0,
  `pow` int(11) NOT NULL DEFAULT 0,
  `mai` int(11) NOT NULL DEFAULT 0,
  `wol` int(11) NOT NULL DEFAULT 0,
  `subject_en` varchar(100) NOT NULL,
  `subject_fr` varchar(100) NOT NULL,
  `subject_zh` varchar(100) NOT NULL,
  `desc_en` text NOT NULL,
  `desc_fr` text NOT NULL,
  `desc_zh` text NOT NULL,
  `position` int(11) NOT NULL,
  `help_en` varchar(100) NOT NULL,
  `help_fr` varchar(100) NOT NULL,
  `help_zh` varchar(100) NOT NULL,
  `attachment_en` int(11) NOT NULL,
  `attachment_fr` int(11) NOT NULL,
  `attachment_zh` int(11) NOT NULL,
  `answer_type` varchar(30) NOT NULL,
  `answer_unit` varchar(30) NOT NULL,
  `component_sn` varchar(40) NOT NULL,
  `match_list` int(11) NOT NULL,
  `answer_max` varchar(30) NOT NULL,
  `answer_min` varchar(30) NOT NULL,
  `non_conformity` varchar(1) NOT NULL COMMENT 'Non conformity value Y/N',
  `created_on` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int(11) NOT NULL,
  `dt_validity` date NOT NULL,
  `dt_expiration` date NOT NULL,
  `create_mode` varchar(20) NOT NULL,
  `gt1` varchar(1) NOT NULL COMMENT 'Y/N',
  `gt3` varchar(1) NOT NULL COMMENT 'Y/N',
  `active` varchar(1) NOT NULL COMMENT 'Y/N',
  `reason` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `pi_questions_logs_parent_id_index` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101509 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_questions_pn_xref`
--

DROP TABLE IF EXISTS `pi_questions_pn_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_questions_pn_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `t_item` char(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pi_questions_pn_xref_id_uindex` (`id`),
  UNIQUE KEY `unique_question_item` (`question_id`,`t_item`)
) ENGINE=InnoDB AUTO_INCREMENT=73437 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_questions_tmp`
--

DROP TABLE IF EXISTS `pi_questions_tmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_questions_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `t_opno` varchar(15) NOT NULL COMMENT 'Operation Number',
  `t_item` varchar(15) NOT NULL COMMENT 'Baan PN',
  `model` varchar(40) NOT NULL,
  `owner` varchar(3) NOT NULL,
  `mtl` int(11) NOT NULL DEFAULT 0,
  `sha` int(11) NOT NULL DEFAULT 0,
  `she` int(11) NOT NULL DEFAULT 0,
  `sor` int(11) NOT NULL DEFAULT 0,
  `stl` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `wux` int(11) NOT NULL DEFAULT 0,
  `leb` int(11) NOT NULL DEFAULT 0,
  `aer` int(11) NOT NULL DEFAULT 0,
  `pow` int(11) NOT NULL DEFAULT 0,
  `mai` int(11) NOT NULL DEFAULT 0,
  `wol` int(11) NOT NULL DEFAULT 0,
  `subject_en` varchar(100) NOT NULL,
  `subject_fr` varchar(100) NOT NULL,
  `subject_zh` varchar(100) NOT NULL,
  `desc_en` text NOT NULL,
  `desc_fr` text NOT NULL,
  `desc_zh` text NOT NULL,
  `position` int(11) NOT NULL,
  `help_en` varchar(100) NOT NULL,
  `help_fr` varchar(100) NOT NULL,
  `help_zh` varchar(100) NOT NULL,
  `attachment_en` int(11) NOT NULL,
  `attachment_fr` int(11) NOT NULL,
  `attachment_zh` int(11) NOT NULL,
  `answer_type` varchar(30) NOT NULL,
  `answer_unit` varchar(30) NOT NULL,
  `component_sn` varchar(40) NOT NULL,
  `match_list` int(11) NOT NULL,
  `answer_max` varchar(30) NOT NULL,
  `answer_min` varchar(30) NOT NULL,
  `non_conformity` varchar(1) NOT NULL COMMENT 'Non conformity value Y/N',
  `created_on` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int(11) NOT NULL,
  `dt_validity` date NOT NULL,
  `dt_expiration` date NOT NULL,
  `create_mode` varchar(20) NOT NULL,
  `gt1` varchar(1) NOT NULL COMMENT 'Y/N',
  `gt3` varchar(1) NOT NULL COMMENT 'Y/N',
  `active` varchar(1) NOT NULL COMMENT 'Y/N',
  PRIMARY KEY (`id`),
  KEY `t_opno` (`t_opno`,`t_item`,`dt_validity`,`dt_expiration`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=3719 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_questions_unit`
--

DROP TABLE IF EXISTS `pi_questions_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_questions_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `comp` int(3) NOT NULL,
  `t_cprj` varchar(20) NOT NULL,
  `t_cprj_baan` int(6) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL COMMENT 'Operation Number',
  `t_item` varchar(15) NOT NULL COMMENT 'Baan PN',
  `model` varchar(40) NOT NULL,
  `owner` varchar(3) NOT NULL,
  `mtl` int(11) NOT NULL DEFAULT 0,
  `sha` int(11) NOT NULL DEFAULT 0,
  `she` int(11) NOT NULL DEFAULT 0,
  `sor` int(11) NOT NULL DEFAULT 0,
  `stl` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `wim` int(11) NOT NULL DEFAULT 0,
  `wux` int(11) NOT NULL DEFAULT 0,
  `leb` int(11) NOT NULL DEFAULT 0,
  `aer` int(11) NOT NULL DEFAULT 0,
  `pow` int(11) NOT NULL DEFAULT 0,
  `mai` int(11) NOT NULL DEFAULT 0,
  `wol` int(11) NOT NULL DEFAULT 0,
  `subject_en` varchar(100) NOT NULL,
  `subject_fr` varchar(100) NOT NULL,
  `subject_zh` varchar(100) NOT NULL,
  `desc_en` text NOT NULL,
  `desc_fr` text NOT NULL,
  `desc_zh` text NOT NULL,
  `position` int(11) NOT NULL,
  `help_en` varchar(100) NOT NULL,
  `help_fr` varchar(100) NOT NULL,
  `help_zh` varchar(100) NOT NULL,
  `attachment_en` int(11) NOT NULL,
  `attachment_fr` int(11) NOT NULL,
  `attachment_zh` int(11) NOT NULL,
  `answer_type` varchar(30) NOT NULL,
  `answer_unit` varchar(30) NOT NULL,
  `component_sn` varchar(40) NOT NULL,
  `match_list` int(11) NOT NULL,
  `answer_max` varchar(30) NOT NULL,
  `answer_min` varchar(30) NOT NULL,
  `non_conformity` varchar(1) NOT NULL COMMENT 'Non conformity value Y/N',
  `created_on` datetime NOT NULL,
  `entered_by` int(11) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int(11) NOT NULL,
  `dt_validity` date NOT NULL,
  `dt_expiration` date NOT NULL,
  `create_mode` varchar(20) NOT NULL,
  `gt1` varchar(1) NOT NULL COMMENT 'Y/N',
  `gt3` varchar(1) NOT NULL COMMENT 'Y/N',
  `active` varchar(1) NOT NULL COMMENT 'Y/N',
  `alert` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comp` (`comp`,`t_opno`,`model`),
  KEY `comp_2` (`comp`,`model`),
  KEY `comp_3` (`comp`,`t_cprj`,`t_pdno`,`t_opno`,`active`),
  KEY `unit` (`unit`,`t_opno`,`model`,`active`),
  KEY `position` (`position`),
  KEY `pi_questions_unit_parent_id_index` (`parent_id`),
  KEY `pi_questions_unit_t_cprj_index` (`t_cprj`),
  KEY `pi_questions_unit_t_pdno_index` (`t_pdno`),
  KEY `pi_questions_unit_t_opno_index` (`t_opno`),
  KEY `idx_pi_questions_unit_t_opno` (`t_opno`),
  KEY `idx_model` (`model`),
  KEY `pi_questions_unit_sn_index` (`unit`)
) ENGINE=InnoDB AUTO_INCREMENT=3194 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_speed_logs`
--

DROP TABLE IF EXISTS `pi_speed_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_speed_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `page` varchar(25) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `comp` int(3) NOT NULL,
  `t_cprj` varchar(20) NOT NULL,
  `t_cprj_baan` int(6) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(1) NOT NULL,
  `duration` float NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `remote_addr` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18352 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_timekeeping_log`
--

DROP TABLE IF EXISTS `pi_timekeeping_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_timekeeping_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp` int(3) NOT NULL,
  `int_user` int(6) NOT NULL,
  `baan_user` int(6) NOT NULL,
  `t_koot` varchar(10) NOT NULL,
  `t_pdno` varchar(23) NOT NULL,
  `t_pdno_baan` int(6) NOT NULL,
  `t_opno` varchar(15) NOT NULL,
  `t_tano` varchar(15) NOT NULL,
  `created_on` datetime NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `remote_addr` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pi_timekeeping_log_comp_t_pdno` (`comp`,`t_pdno`),
  KEY `idx_pi_timekeeping_log_created_on` (`created_on`)
) ENGINE=InnoDB AUTO_INCREMENT=3431104 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_unit_family`
--

DROP TABLE IF EXISTS `pi_unit_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_unit_family` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit` varchar(20) NOT NULL,
  `family` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pi_unit_family_family` (`family`)
) ENGINE=InnoDB AUTO_INCREMENT=11602 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pip`
--

DROP TABLE IF EXISTS `pip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `process` varchar(30) NOT NULL DEFAULT '',
  `factory` int(11) NOT NULL DEFAULT 0,
  `product_type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '0',
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `date_closed` date NOT NULL DEFAULT '0000-00-00',
  `date_suspended` date NOT NULL DEFAULT '0000-00-00',
  `days_suspended` int(11) NOT NULL DEFAULT 0,
  `short_desc` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `resolution` text NOT NULL,
  `rejection_reason` text NOT NULL,
  `picture_filename` varchar(254) NOT NULL,
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `final_fweight` int(11) NOT NULL DEFAULT 0,
  `poster` int(11) NOT NULL DEFAULT 0,
  `initiator` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `factory` (`factory`),
  KEY `product_type` (`product_type`),
  KEY `model` (`model`),
  KEY `status` (`status`),
  KEY `poster` (`poster`),
  KEY `initiator` (`initiator`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `po_exten`
--

DROP TABLE IF EXISTS `po_exten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `po_exten` (
  `id` int(11) NOT NULL,
  `erp` int(11) NOT NULL,
  `date_display` datetime NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`,`erp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `port_codes`
--

DROP TABLE IF EXISTS `port_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `port_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `ctry_code_2` varchar(2) NOT NULL,
  `port_code` varchar(3) NOT NULL,
  `port_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ctry_code_2` (`ctry_code_2`)
) ENGINE=InnoDB AUTO_INCREMENT=7569 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prj`
--

DROP TABLE IF EXISTS `prj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp` int(3) NOT NULL,
  `po` int(11) NOT NULL,
  `orno` int(11) NOT NULL,
  `prj` int(11) NOT NULL,
  `item` varchar(20) NOT NULL,
  `inv` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prj` (`prj`),
  KEY `po` (`po`),
  KEY `orno` (`orno`)
) ENGINE=InnoDB AUTO_INCREMENT=2157 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_categories`
--

DROP TABLE IF EXISTS `products_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
  `en` varchar(60) DEFAULT NULL,
  `fr` text DEFAULT NULL,
  `es` text DEFAULT NULL,
  `pt` text DEFAULT NULL,
  `zh` text DEFAULT NULL,
  `ja` text NOT NULL,
  `de` text NOT NULL,
  `ru` text NOT NULL,
  `public` tinyint(4) NOT NULL DEFAULT 1,
  `dms_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `en` (`en`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_datasheets`
--

DROP TABLE IF EXISTS `products_datasheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_datasheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
  `model` varchar(255) DEFAULT NULL,
  `public` tinyint(4) NOT NULL DEFAULT 1,
  `hidden` tinyint(4) NOT NULL DEFAULT 0,
  `en` text NOT NULL,
  `fr` text NOT NULL,
  `es` text NOT NULL,
  `zh` text NOT NULL,
  `pt` text NOT NULL,
  `de` text NOT NULL,
  `ja` text NOT NULL,
  `ru` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `model` (`model`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=304 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_datasheets_archive`
--

DROP TABLE IF EXISTS `products_datasheets_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_datasheets_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `model` varchar(50) DEFAULT NULL,
  `public` tinyint(4) NOT NULL,
  `en` text NOT NULL,
  `fr` text NOT NULL,
  `es` text NOT NULL,
  `zh` text NOT NULL,
  `pt` text NOT NULL,
  `de` text NOT NULL,
  `ja` text NOT NULL,
  `ru` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=297 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_datasheets_dms`
--

DROP TABLE IF EXISTS `products_datasheets_dms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_datasheets_dms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dms_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products_history`
--

DROP TABLE IF EXISTS `products_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `version` varchar(20) NOT NULL DEFAULT '',
  `approved_by` varchar(50) NOT NULL DEFAULT '',
  `en` text NOT NULL,
  `fr` text NOT NULL,
  `en_file` varchar(50) NOT NULL DEFAULT '',
  `fr_file` varchar(50) NOT NULL DEFAULT '',
  `es_file` varchar(50) NOT NULL DEFAULT '',
  `pt_file` varchar(50) NOT NULL DEFAULT '',
  `de_file` varchar(50) NOT NULL DEFAULT '',
  `ja_file` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=416 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionaire`
--

DROP TABLE IF EXISTS `questionaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `expiration` date NOT NULL DEFAULT '0000-00-00',
  `author` varchar(100) NOT NULL DEFAULT '',
  `category` varchar(20) NOT NULL DEFAULT '',
  `category2` varchar(20) NOT NULL DEFAULT '',
  `difficulty` int(1) NOT NULL DEFAULT 0,
  `question` text NOT NULL,
  `picture` varchar(50) NOT NULL DEFAULT '',
  `response1` text NOT NULL,
  `reason1` text NOT NULL,
  `response2` text NOT NULL,
  `reason2` text NOT NULL,
  `answer` tinyint(4) NOT NULL DEFAULT 0,
  `reason_picture` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionaire_results`
--

DROP TABLE IF EXISTS `questionaire_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionaire_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `user` varchar(100) NOT NULL DEFAULT '',
  `result` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5621 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sage_xref`
--

DROP TABLE IF EXISTS `sage_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sage_xref` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `Sage_UID` varchar(50) NOT NULL COMMENT 'Sage User ID',
  `TLD_Item` varchar(50) NOT NULL COMMENT 'TLD Item PN',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TLD_Item` (`TLD_Item`)
) ENGINE=InnoDB AUTO_INCREMENT=18812 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sage_xref_prices`
--

DROP TABLE IF EXISTS `sage_xref_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sage_xref_prices` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `TLD_Item` varchar(50) NOT NULL COMMENT 'TLD Item PN',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TLD_Item` (`TLD_Item`)
) ENGINE=InnoDB AUTO_INCREMENT=32770 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sales_areas`
--

DROP TABLE IF EXISTS `sales_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `continent` varchar(60) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT '',
  `customer` varchar(150) NOT NULL DEFAULT '',
  `rep` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `continent` (`continent`(1)),
  KEY `country` (`country`),
  KEY `customer` (`customer`),
  KEY `rep` (`rep`)
) ENGINE=InnoDB AUTO_INCREMENT=423 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sb`
--

DROP TABLE IF EXISTS `sb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `poster_id` int(11) NOT NULL,
  `bu_id` int(11) NOT NULL,
  `status` varchar(60) NOT NULL,
  `category` varchar(60) NOT NULL,
  `category_reason` text NULL,
  `type` varchar(60) NOT NULL,
  `ifactor` int(11) NOT NULL,
  `confidential` varchar(1) NOT NULL,
  `title` varchar(260) NOT NULL,
  `description` text NOT NULL,
  `dt_ssd_approval` datetime NOT NULL,
  `dt_ssd_decision` datetime NOT NULL,
  `dt_implementation` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `labor` int(11) NOT NULL,
  `nb_tech_needed` int(11) NOT NULL,
  `parts_needed` varchar(1) NOT NULL,
  `factory_part_availability_status` varchar(30) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `poster_id` (`poster_id`),
  KEY `bu_id` (`bu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4421 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO sb (id, parent_id, dt, poster_id, bu_id, status, category, category_reason, type, ifactor, confidential, title, description, dt_ssd_approval, dt_ssd_decision, dt_implementation, dt_closed, labor, nb_tech_needed, parts_needed, factory_part_availability_status)
VALUES (58, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'RECOMMENDED', '', 'MAINTENANCE', 100, 'N', 'NBL BRAKE PEDAL ADJUSTMENT', 'issue detected', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', '');
INSERT INTO sb (id, parent_id, dt, poster_id, bu_id, status, category, category_reason, type, ifactor, confidential, title, description, dt_ssd_approval, dt_ssd_decision, dt_implementation, dt_closed, labor, nb_tech_needed, parts_needed, factory_part_availability_status)
VALUES (59, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'COMPULSORY', '', 'MAINTENANCE', 100, 'N', 'NBL BRAKE PEDAL ADJUSTMENT', 'issue detected', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', '');

--
-- Kit-parts-availability visibility scenarios (TTS-1062, TTS-56529) — see
-- api/src/LegacyBundle/features/service_bulletin.feature "KITREADYTEST" scenarios.
--
INSERT INTO sb (id, parent_id, dt, poster_id, bu_id, status, category, category_reason, type, ifactor, confidential, title, description, dt_ssd_approval, dt_ssd_decision, dt_implementation, dt_closed, labor, nb_tech_needed, parts_needed, factory_part_availability_status)
VALUES (101, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'COMPULSORY', '', 'MAINTENANCE', 100, 'N', 'KITREADYTEST NOT READY', 'kit parts availability test - no line ready yet', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', ''),
       (102, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'COMPULSORY', '', 'MAINTENANCE', 100, 'N', 'KITREADYTEST READY OTHER LINE', 'kit parts availability test - ready via another line', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', ''),
       (103, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'COMPULSORY', '', 'MAINTENANCE', 100, 'Y', 'KITREADYTEST CONFIDENTIAL', 'kit parts availability test - confidential overrides readiness', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', ''),
       (104, 37469, CURRENT_DATE, 1, 7, 'DRAFT', 'COMPULSORY', '', 'MAINTENANCE', 100, 'N', 'KITREADYTEST DRAFT STATUS', 'kit parts availability test - non visible sb status overrides readiness', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', ''),
       (105, 37469, CURRENT_DATE, 1, 7, 'IMPLEMENTATION', 'COMPULSORY', '', 'MAINTENANCE', 100, 'N', 'KITREADYTEST NO CUSTOMER ACCESS', 'kit parts availability test - no accessible customer line overrides readiness', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 1, 'N', '');


--
-- Table structure for table `sb_coverage`
--

DROP TABLE IF EXISTS `sb_coverage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_coverage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL DEFAULT '',
  `sn_from` varchar(20) NOT NULL DEFAULT '',
  `sn_to` varchar(20) NOT NULL DEFAULT '',
  `sn_list` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=929 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sb_lines`
--

DROP TABLE IF EXISTS `sb_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `er_id` int(11) NOT NULL,
  `part_decision` varchar(1) NOT NULL COMMENT 'EVP Part Decision',
  `cust_part_decision` varchar(1) NOT NULL COMMENT 'Customer Part Decision',
  `spr_id` int(11) NOT NULL,
  `service_decision` varchar(1) NOT NULL COMMENT 'EVP Service Decision',
  `cust_service_decision` varchar(1) NOT NULL COMMENT 'Customer Service Decision',
  `csr_id` int(11) NOT NULL,
  `status` varchar(60) NOT NULL,
  `dt_cust_to_decide` date NOT NULL COMMENT 'Date status CUSTOMER_TO_DECIDE',
  `closure_type` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `er_id` (`er_id`),
  KEY `parent_id` (`parent_id`),
  KEY `spr_id` (`spr_id`),
  KEY `csr_id` (`csr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101151 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO sb_lines (parent_id, er_id, part_decision, cust_part_decision, spr_id, service_decision, cust_service_decision, csr_id, status, dt_cust_to_decide, closure_type)
VALUES (58, 37455, '', '', 0, '', '', 0, '', null, ''),
       (58, 37463, '', '', 0, '', '', 0, '', null, ''),
       (58, 37469, '', '', 0, '', '', 0, '', null, '');
INSERT INTO sb_lines (parent_id, er_id, part_decision, cust_part_decision, spr_id, service_decision, cust_service_decision, csr_id, status, dt_cust_to_decide, closure_type)
VALUES (59, 37454, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, ''),
       (59, 37455, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, ''),
       (59, 37463, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, ''),
       (59, 37469, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, '');

-- Kit-parts-availability visibility scenarios (see the "sb" inserts above for context).
INSERT INTO sb_lines (parent_id, er_id, part_decision, cust_part_decision, spr_id, service_decision, cust_service_decision, csr_id, status, dt_cust_to_decide, closure_type)
VALUES (101, 37454, '', '', 0, '', '', 0, '', null, ''),
       (102, 37454, '', '', 0, '', '', 0, '', null, ''),
       (102, 37469, '', '', 0, '', '', 0, 'CUSTOMER_TO_DECIDE', null, ''),
       (103, 37454, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, ''),
       (104, 37454, '', '', 0, '', '', 0, 'TLD_TO_IMPLEMENT', null, ''),
       (105, 37469, '', '', 0, '', '', 0, 'CUSTOMER_TO_DECIDE', null, '');
--
-- Table structure for table `sb_not`
--

DROP TABLE IF EXISTS `sb_not`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_not` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `recipients` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `subject` text NOT NULL,
  `email` text NOT NULL,
  `fid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fid` (`fid`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65459 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sb_signature`
--

DROP TABLE IF EXISTS `sb_signature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_signature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `sso_id` int(11) NOT NULL,
  `status` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sso_id` (`sso_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2284 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sbs`
--

DROP TABLE IF EXISTS `sbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sbs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `sb_type` varchar(20) NOT NULL DEFAULT 'SERVICE BULLETIN',
  `factory_sb_number` varchar(30) DEFAULT NULL,
  `entered_by` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `urgency` varchar(30) NOT NULL DEFAULT '',
  `sbs_file` varchar(100) DEFAULT NULL,
  `brand` varchar(10) DEFAULT NULL,
  `factory` varchar(20) NOT NULL DEFAULT '',
  `entered_date` date DEFAULT NULL,
  `dt_closed` datetime NOT NULL COMMENT 'date and time closed',
  `signature` varchar(50) NOT NULL DEFAULT '',
  `extranet` char(3) NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  KEY `sb_type` (`sb_type`),
  KEY `urgency` (`urgency`),
  KEY `status` (`status`),
  KEY `factory` (`factory`)
) ENGINE=InnoDB AUTO_INCREMENT=3898 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sbs_files`
--

DROP TABLE IF EXISTS `sbs_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sbs_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2363 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sbs_lines`
--

DROP TABLE IF EXISTS `sbs_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sbs_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `model` varchar(255) NOT NULL DEFAULT '',
  `sn_from` varchar(20) NOT NULL DEFAULT '',
  `sn_to` varchar(20) NOT NULL DEFAULT '',
  `model_range` varchar(20) NOT NULL,
  `sn_list` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `model` (`model`),
  KEY `sn_from` (`sn_from`),
  KEY `sn_to` (`sn_to`)
) ENGINE=InnoDB AUTO_INCREMENT=4555 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sbs_parts`
--

DROP TABLE IF EXISTS `sbs_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sbs_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `pn` varchar(20) NOT NULL DEFAULT '',
  `dsca` varchar(100) NOT NULL DEFAULT '',
  `qty` varchar(10) NOT NULL DEFAULT '',
  `um` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `pn` (`pn`)
) ENGINE=InnoDB AUTO_INCREMENT=3123 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scar`
--

DROP TABLE IF EXISTS `scar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `dt_approval` datetime NOT NULL,
  `ifactor` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `bu_id` int(11) NOT NULL,
  `supplier_bu_id` int(11) NOT NULL,
  `supplier_ref` varchar(6) NOT NULL,
  `supplier_name` varchar(250) NOT NULL,
  `short_desc` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `root_cause` text NOT NULL,
  `action` text NOT NULL,
  `preventive_action` text NOT NULL,
  `commercial` text NOT NULL,
  `conclusion` text NOT NULL,
  `file_name` varchar(250) NOT NULL,
  `tld_assignor` int(11) NOT NULL,
  `sup_assignee` varchar(30) NOT NULL,
  `leader` int(11) NOT NULL,
  `verification_description` text NULL,
  PRIMARY KEY (`id`),
  KEY `poster` (`poster_id`),
  KEY `bu` (`bu_id`),
  KEY `supplier` (`supplier_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=1314 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scar_comments`
--

DROP TABLE IF EXISTS `scar_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scar_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `poster_type` varchar(10) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=771 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scm_members`
--

DROP TABLE IF EXISTS `scm_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scm_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `erp` int(3) NOT NULL COMMENT 'SCM ERP#',
  `t_ccon` varchar(60) NOT NULL COMMENT 'SCM#',
  `user_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL COMMENT 'Type of member',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `erp` (`erp`,`t_ccon`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `sor_lid` int(11) NOT NULL DEFAULT 0 COMMENT 'TOBE DELETED, id of sor line this unit is assigned to',
  `sor_uid` int(11) DEFAULT 10 COMMENT 'SOR Unit ID',
  `esrid` int(11) NOT NULL DEFAULT 0 COMMENT 'equipment shipping record id',
  `tranid_sso` int(11) NOT NULL,
  `tranid_erp` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `state` varchar(20) NOT NULL,
  `brandbak` varchar(10) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `sn` varchar(20) NOT NULL DEFAULT '',
  `cust_asset_num` varchar(20) NOT NULL DEFAULT '',
  `entered_by` varchar(80) NOT NULL DEFAULT '',
  `man_location` varchar(20) NOT NULL DEFAULT '',
  `buyer_customer_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `maintainer_customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(60) NOT NULL DEFAULT '',
  `customer_name_prev` varchar(255) NOT NULL,
  `customer_contact` varchar(30) NOT NULL DEFAULT '',
  `customer_ref` varchar(30) NOT NULL DEFAULT '',
  `agent_name` varchar(255) NOT NULL DEFAULT '',
  `location_short` varchar(20) NOT NULL DEFAULT '',
  `airport_code` varchar(50) NOT NULL DEFAULT '',
  `delivery_location` text NOT NULL,
  `del_ctry` varchar(20) NOT NULL COMMENT 'Country of delivery',
  `reg_password` varchar(30) NOT NULL DEFAULT '',
  `reg_details` int(11) DEFAULT NULL,
  `warranty_length` int(11) DEFAULT 24,
  `warranty_length_hours` int(11) ,
  `warranty_conditions` text NOT NULL,
  `options_desc` text NOT NULL,
  `sales_org` varchar(20) NOT NULL DEFAULT '',
  `sso_service` varchar(20) NOT NULL DEFAULT '',
  `sls_orno` varchar(20) NOT NULL,
  `sales_rep` varchar(50) NOT NULL DEFAULT '',
  `date_entered` date DEFAULT NULL,
  `dt_commissioned` date NOT NULL,
  `date_shipped` date NOT NULL,
  `date_arrived` date DEFAULT NULL,
  `date_registered` date DEFAULT NULL,
  `date_warranty_end` date NOT NULL,
  `rrd_sso` date NOT NULL COMMENT 'SSO RRD',
  `rrd_erp` date NOT NULL COMMENT 'Factory RRD',
  `mfg_comments` text NOT NULL,
  `t_prno` varchar(20) NOT NULL,
  `t_prno_baan` varchar(20) NOT NULL,
  `t_pdno` varchar(23) NOT NULL COMMENT 'Main Work Order#',
  `t_pdno_baan` varchar(20) NOT NULL COMMENT 'Main Work Order#',
  `family` varchar(100) NOT NULL,
  `dpur` date NOT NULL COMMENT 'po date',
  `ddel_req` date NOT NULL COMMENT 'Requested Delivery Date',
  `ddel_est1BAK` date NOT NULL COMMENT 'Factory promised delivery date',
  `dgt_est` date NOT NULL COMMENT 'factory initial projected gt date',
  `dgt_com` date NOT NULL COMMENT 'committed gt date',
  `dgt_rev` date NOT NULL COMMENT 'revised gt date',
  `ddel_est2` date NOT NULL COMMENT 'projected del date',
  `odp_note` text NOT NULL COMMENT 'odp comments',
  `factory_comment` text NOT NULL COMMENT 'Factory comment (used for Planning Online)',
  `dyt` date NOT NULL COMMENT 'Date Yellow Tag',
  `dgt_act` date NOT NULL COMMENT 'Actual GT date',
  `ddel_act2` date NOT NULL COMMENT 'Actual delivery date',
  `ddel_act` date NOT NULL COMMENT 'actual ship date',
  `dprt` date NOT NULL COMMENT 'date parts at unit shipping location',
  `diml` decimal(10,0) NOT NULL COMMENT 'Dimension, length',
  `dimw` decimal(10,0) NOT NULL COMMENT 'Dimension, width',
  `dimh` decimal(10,0) NOT NULL COMMENT 'Dimension, height',
  `dimk` decimal(10,0) NOT NULL COMMENT 'Weight',
  `insp` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'onsite inspection',
  `publishable` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Publish from CBOM available',
  `er_batch_qty` int(11) NOT NULL DEFAULT 1 COMMENT 'Batch quantity',
  `hours` int(11) NOT NULL COMMENT 'ER hourmeter',
  `eng_tier` varchar(50) NOT NULL,
  `maintenance_contract_ref` varchar(20) NOT NULL,
  `maintenance_contract_erp` varchar(3) NOT NULL,
  `cust_equipment_type` varchar(256) NOT NULL,
  `operation_status` varchar(10) NOT NULL,
  `comb_mod` varchar(20) NOT NULL COMMENT 'Combined mode',
  `tld_link` tinyint(1) NOT NULL DEFAULT 0,
  `sim_status` VARCHAR(10) NOT NULL DEFAULT 'INACTIVE',
  `fms_contract_length` TINYINT UNSIGNED DEFAULT 0,
  `fms_end_use_date` date DEFAULT NULL,
  `light` tinyint(1) NOT NULL DEFAULT 0,
  `promised_cbom_date` date DEFAULT NULL,
  `last_cbom_update_date` date DEFAULT NULL,
  `first_estimated_green_tag_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `model` (`model`),
  KEY `customer_name` (`customer_name`),
  KEY `airport_code` (`airport_code`),
  KEY `sn` (`sn`),
  KEY `man_location` (`man_location`),
  KEY `sales_org` (`sales_org`),
  KEY `buyer_customer_id` (`buyer_customer_id`),
  KEY `customer_id` (`customer_id`),
  KEY `parent_id` (`parent_id`),
  KEY `sor_uid` (`sor_uid`),
  KEY `tranid_sso` (`tranid_sso`),
  KEY `tranid_erp` (`tranid_erp`)
) ENGINE=InnoDB AUTO_INCREMENT=37455 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO service
(id, tranid_sso, tranid_erp, status, state, sn, buyer_customer_id, customer_id, customer_name_prev, del_ctry, warranty_conditions, options_desc, sls_orno, dt_commissioned, date_shipped, date_warranty_end, rrd_sso, rrd_erp, mfg_comments, t_prno, t_prno_baan, t_pdno, t_pdno_baan, family, dpur, ddel_req, ddel_est1BAK, dgt_est, dgt_com, dgt_rev, ddel_est2, odp_note, factory_comment, dyt, dgt_act, ddel_act2, ddel_act, dprt, diml, dimw, dimh, dimk, hours, eng_tier, maintenance_contract_ref, maintenance_contract_erp, cust_equipment_type, operation_status, comb_mod, delivery_location)
VALUES (37454, 0, 0, '', '', 'SN_001', 4074, 4074, '', 'Estonia', '', '', '', '2025-03-30', '2024-08-11', '0000-00-00', '0000-00-00', '0000-00-00', '', '', '', '', '', '', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '', '', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, 0, 0, 0, 10, '', '', '', '', '', '', '');


--
-- Table structure for table `service_docs`
--

DROP TABLE IF EXISTS `service_docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `doc_num` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1079 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_files`
--

DROP TABLE IF EXISTS `service_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6568 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Data for table `service_files` (customer files)
-- Row 6568 is attached to service 37454 (accessible to extranet user julien.lepers@tld.com through customer 4074).
-- Row 6569 is attached to service 37455 (NOT accessible to that extranet user), used to assert the security scoping.
--

LOCK TABLES `service_files` WRITE;
INSERT INTO `service_files` (`id`, `parent_id`, `date`, `description`, `filename`) VALUES
(6568, 37454, '2024-01-15', 'Customer manual', '1705312800-customer_manual.pdf'),
(6569, 37455, '2024-01-16', 'Confidential file', '1705399200-confidential.pdf');
UNLOCK TABLES;

--
-- Table structure for table `service_hourmeter`
--

DROP TABLE IF EXISTS `service_hourmeter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_hourmeter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `hourmeter` int(11) NOT NULL,
  `module` varchar(10) NOT NULL,
  `module_id` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `module` (`module`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24870 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_lines`
--

DROP TABLE IF EXISTS `service_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `sr_status` varchar(50) NOT NULL DEFAULT '',
  `cu_contact` varchar(100) NOT NULL COMMENT 'customer contact name',
  `sr_location` varchar(60) NOT NULL DEFAULT '',
  `date_entered` date NOT NULL DEFAULT '0000-00-00',
  `date` date DEFAULT NULL,
  `hourmeter` varchar(11) NOT NULL DEFAULT '',
  `work_type` varchar(20) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `extranet_desc` text NOT NULL,
  `technician` varchar(30) NOT NULL DEFAULT '',
  `factory_technician` varchar(50) NOT NULL DEFAULT '',
  `technician_hours` float NOT NULL DEFAULT 0,
  `technician_cost_te` decimal(6,2) NOT NULL DEFAULT 0.00,
  `technician_cost_manhours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `cost_parts` decimal(6,2) NOT NULL DEFAULT 0.00,
  `cost_notes` text NOT NULL,
  `ship_inst` text NOT NULL,
  `parts_courier` text NOT NULL,
  `reason` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `work_type` (`work_type`,`reason`)
) ENGINE=InnoDB AUTO_INCREMENT=39433 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_reg`
--

DROP TABLE IF EXISTS `service_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_reg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date_registered` date NOT NULL DEFAULT '0000-00-00',
  `company` varchar(30) NOT NULL DEFAULT '',
  `person` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_serials`
--

DROP TABLE IF EXISTS `service_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `component` varchar(30) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL,
  `serial` varchar(50) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `manid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `manid` (`manid`)
) ENGINE=InnoDB AUTO_INCREMENT=313230 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_strans`
--

DROP TABLE IF EXISTS `service_strans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_strans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `who` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `status` varchar(3) NOT NULL,
  `log` text NOT NULL,
  `limitations_log` text NOT NULL,
  `repairs_log` text NOT NULL,
  `fmc_log` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=309 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_upgrades`
--

DROP TABLE IF EXISTS `service_upgrades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_upgrades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `dt_open` date NOT NULL,
  `dt_upgrade` date NOT NULL,
  `description` text NOT NULL,
  `pn` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sfr`
--

DROP TABLE IF EXISTS `sfr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sfr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `sso_id` int(11) NOT NULL,
  `asm_id` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `init_id` int(11) NOT NULL COMMENT 'entered by id#',
  `equote_id` varchar(20) NOT NULL,
  `user_customer_id` int(11) DEFAULT NULL,
  `buyer_customer_id` int(11) DEFAULT NULL,
  `cust_nama` varchar(100) NOT NULL,
  `cust_ctry` varchar(50) NOT NULL,
  `apc` varchar(10) NOT NULL,
  `erp_id` int(3) NOT NULL,
  `model` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `year_id` mediumint(6) NOT NULL COMMENT 'Expected year of order',
  `month_id` mediumint(6) NOT NULL COMMENT 'Expected month of order',
  `cust_pur_pc` decimal(10,0) NOT NULL,
  `tld_succ_pc` decimal(10,0) NOT NULL,
  `tot_succ_pc` decimal(10,0) NOT NULL,
  `dpo_ini` date NOT NULL,
  `dpo_rev` date NOT NULL,
  `dt_ship` date NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `eng_tier` varchar(50) NOT NULL,
  `dt_cfsso` date NOT NULL COMMENT 'SSO cash forecast',
  `dt_cferp` date NOT NULL COMMENT 'Factory cash forecast',
  `sfr_master_id` int(11) DEFAULT NULL,
  `third_party_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sso_id` (`sso_id`),
  KEY `erp_id` (`erp_id`),
  KEY `asm_id` (`asm_id`),
  KEY `init_id` (`init_id`),
  KEY `user_customer_id` (`user_customer_id`),
  KEY `buyer_customer_id` (`buyer_customer_id`),
  KEY `cust_nama` (`cust_nama`)
) ENGINE=InnoDB AUTO_INCREMENT=16218 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sfr_master`
--

DROP TABLE IF EXISTS `sfr_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sfr_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sor`
--

DROP TABLE IF EXISTS `sor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `bu` int(3) NOT NULL,
  `sso` int NOT NULL,
  `juridical_entity_id` int(11) NOT NULL,
  `t_cuno` varchar(20) NOT NULL COMMENT 'erp customer id number',
  `dt_entered` timestamp NOT NULL DEFAULT current_timestamp(),
  `dt_closed` datetime NOT NULL COMMENT 'date and time closed',
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `eqno` varchar(20) NOT NULL,
  `orno` tinytext NOT NULL COMMENT 'SO num with customer',
  `asm` int(11) NOT NULL,
  `buyer_customer_id` int(11) DEFAULT NULL,
  `user_customer_id` int(11) DEFAULT NULL,
  `cu_nama` varchar(80) NOT NULL,
  `cu_new` varchar(1) NOT NULL DEFAULT 'N',
  `agnt_nama` varchar(50) NOT NULL,
  `cu_orno` varchar(250) NOT NULL,
  `del_pen` varchar(1) NOT NULL DEFAULT 'N',
  `inco` varchar(3) NOT NULL COMMENT 'inco terms',
  `inco_loc` varchar(50) NOT NULL COMMENT 'inco terms location',
  `src_xml` text NOT NULL,
  `note` text NULL,
  PRIMARY KEY (`id`),
  KEY `asm` (`asm`),
  KEY `bu` (`bu`),
  KEY `sso` (`sso`),
  KEY `buyer_customer_id` (`buyer_customer_id`),
  KEY `user_customer_id` (`user_customer_id`),
  KEY `cu_nama` (`cu_nama`),
  KEY `juridical_entity_id` (`juridical_entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18894 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
#Needed for manufacturing margin report
INSERT INTO sor (id, bu, sso, buyer_customer_id) VALUES (30, 680, 45, 4106);
--
-- Table structure for table `sor_lines`
--

DROP TABLE IF EXISTS `sor_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sor_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt_opened` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `dt_create_factory` datetime NOT NULL,
  `dzk_sso` date NOT NULL COMMENT 'Date, Zero bacKlog, SSO',
  `dzk_erp` date NOT NULL COMMENT 'Date, Zero bacKlog, ERP',
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `bu` int(3) NOT NULL,
  `sls_orno` varchar(20) NOT NULL COMMENT 'sales, PO to factory',
  `erp_orno` varchar(20) NOT NULL COMMENT 'Factory SO#',
  `model` varchar(255) NOT NULL,
  `eng_tier` varchar(50) NOT NULL COMMENT 'Engine tier',
  `qty` int(11) NOT NULL,
  `del_pen` varchar(1) NOT NULL,
  `conf_sls` varchar(1) NOT NULL DEFAULT 'N',
  `conf_erp` varchar(1) NOT NULL DEFAULT 'N',
  `delpen_cond` text NOT NULL COMMENT 'delivery penalty conditions',
  `wrty_spec` text NOT NULL,
  `wrty_std` text NOT NULL,
  `conf_wrty_erp` varchar(1) NOT NULL DEFAULT 'N',
  `warranty_length` TINYINT UNSIGNED DEFAULT 24,
  `warranty_length_hours` INT(11),
  `tpay` text NOT NULL COMMENT 'Payment Terms',
  `conf_cxo` varchar(1) NOT NULL DEFAULT 'N',
  `cu_ocur` varchar(3) NOT NULL COMMENT 'customer order currency',
  `dp_pc` decimal(10,2) NOT NULL,
  `dp_amt` decimal(10,2) NOT NULL,
  `receivedp_amt` decimal(10,2) NOT NULL,
  `parts_inc` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Ship with spare parts',
  `docs_inc` text NOT NULL,
  `conf_lc` varchar(1) NOT NULL COMMENT 'Letter of credit required?',
  `receive_lc` varchar(1) NOT NULL,
  `notes` text NOT NULL,
  `trans` varchar(10) NOT NULL COMMENT 'transportation, responsibility',
  `inco` varchar(3) NOT NULL COMMENT 'inco terms',
  `inco_loc` varchar(50) NOT NULL COMMENT 'inco terms location',
  `conf_cis` varchar(1) NOT NULL COMMENT 'Customer inspection before shipment?',
  `ctry` varchar(100) NOT NULL COMMENT 'Country',
  `intro_new` varchar(30) NOT NULL,
  `factory_margin` tinyint(4) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `sfr_id` int(11) NOT NULL,
  `export_licence_status` varchar(15) NOT NULL DEFAULT 'NOT REQUIRED',
  `engineering_flag` tinyint(1) NOT NULL DEFAULT 0,
  `fms_contract_length` TINYINT UNSIGNED DEFAULT 0,
  `certificate_of_origin_required` tinyint(1) NOT NULL DEFAULT 0,
  `factory_shipping` int(3) DEFAULT NULL,
  `batch_quantity` int(11) DEFAULT NULL,
  `conf_dms` varchar(1) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `bu` (`bu`)
) ENGINE=InnoDB AUTO_INCREMENT=18818 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
# /sales/orders/1
INSERT INTO sor_lines (parent_id, status, dt_opened, dt_create_factory) VALUES (18894, 'SHIPPED', NOW(), NOW());
# /sales/orders/2
INSERT INTO sor_lines (parent_id, status, dt_opened) VALUES (18895, 'CREATE_PO', NOW()), (18895, 'CREATE_PO', NOW());
# /sales/orders/3
INSERT INTO sor_lines (parent_id, status, dt_opened) VALUES (18896, 'PENDING', '2016-12-11');
# Needed for manufacturing margin notification report
INSERT INTO sor_lines (id, parent_id, status, ctry, model, factory_margin, bu, dt_opened) VALUES (20, 30, 'PENDING', 'Japan', 'Produit Test', 50, 73, NOW());

--
-- Table structure for table `sor_opts`
--

DROP TABLE IF EXISTS `sor_opts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sor_opts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `caty` varchar(50) NOT NULL COMMENT 'line category',
  `dsca` varchar(255) NOT NULL COMMENT 'Description',
  `qty` int(11) NOT NULL COMMENT 'Quantity',
  `mrsp_cur` varchar(3) NOT NULL COMMENT 'Published TP',
  `mrsp` decimal(10,2) NOT NULL COMMENT 'Published TP',
  `prip_cur` varchar(3) NOT NULL COMMENT 'Published Price',
  `prip` decimal(10,2) NOT NULL COMMENT 'Published Price',
  `pric_cur` varchar(3) NOT NULL COMMENT 'Negotiated TP',
  `pric` decimal(10,2) NOT NULL COMMENT 'Negotiated TP',
  `pris_cur` varchar(3) NOT NULL COMMENT 'Actual Sales Price',
  `pris` decimal(10,2) NOT NULL COMMENT 'Actual Sales Price',
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100292 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
# SOL18819 linked to /sales/orders/2
INSERT INTO sor_opts (parent_id, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris)
VALUES
  (18819, 'BASE UNIT', 'BASE CAMP', 1, 'EUR', 5000, 'EUR', 5000, 'EUR', 5000, 'EUR', 5000),
  (18819, 'OPTION', 'COOL OPTION', 1, 'EUR', 1000, 'EUR', 1000, 'EUR', 1000, 'EUR', 1000);
--
-- Table structure for table `sor_tran`
--

DROP TABLE IF EXISTS `sor_tran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sor_tran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `nref` varchar(20) NOT NULL COMMENT 'ref num, either INV or SO',
  `dref` date NOT NULL COMMENT 'ref date, either INV or SO date',
  `dtran` date NOT NULL COMMENT 'transaction date',
  `tgrp` varchar(3) NOT NULL COMMENT 'transaction group, either SSO or ERP',
  `ttyp` enum('B','R') NOT NULL COMMENT 'B=booking, R=revenue',
  `tcur` varchar(3) NOT NULL COMMENT 'transaction currency',
  `tval` decimal(11,2) NOT NULL,
  `notes` text NOT NULL,
  `postid` int(11) NOT NULL COMMENT 'poster userid',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `dtran` (`dtran`),
  KEY `tgrp` (`tgrp`),
  KEY `ttyp` (`ttyp`)
) ENGINE=InnoDB AUTO_INCREMENT=42391 DEFAULT CHARSET=latin1 COMMENT='Sales Finance transaction table for SOR';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sor_units`
--

DROP TABLE IF EXISTS `sor_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sor_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `short_desc` varchar(254) NOT NULL,
  `long_desc` text NOT NULL,
  `del_location` text NOT NULL COMMENT 'Delivery location',
  `del_dat` date NOT NULL COMMENT 'Requested Delivery Date',
  `ddel_est1` date NOT NULL COMMENT 'Factory promised delivery date',
  `dgt_est` date NOT NULL COMMENT 'factory initial projected gt date',
  `batch_qty` int(11) NOT NULL DEFAULT 1 COMMENT 'Batch quantity',
  `del_early` varchar(1) NOT NULL COMMENT 'Early delivery ok ?',
  `ddel_asm` date DEFAULT NULL COMMENT 'ASM promised delivery date',
  `dpas_rating` varchar(255) DEFAULT NULL COMMENT 'DPAS rating',
  `sleep_com` decimal(7,4)  DEFAULT  0.0000 NOT NULL COMMENT 'Sleeping commission Rate',
  `sleep_com_sso_id` int(11) NOT NULL DEFAULT 0 NOT NULL COMMENT 'Sleeping commission recipient (SSO)',
  `commissioning` tinyint(1) DEFAULT 0 NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28357 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/* sor_units needed for manufacturing margin notification report */
INSERT INTO sor_units (id, parent_id) VALUES (10, 20);
# SOL18819 linked to /sales/orders/2
INSERT INTO sor_units (parent_id, short_desc, long_desc, del_location, del_dat, ddel_est1, dgt_est, del_early)
VALUES
       (18819, 'SHORT DESC 1', '', '', CURDATE(), CURDATE(), CURDATE(), 'N'),
       (18819, 'SHORT DESC 2', '', '', CURDATE(), CURDATE(), CURDATE(), 'N');
--
-- Table structure for table `spq`
--

DROP TABLE IF EXISTS `spq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt_open` datetime NOT NULL,
  `dt_submit` datetime NOT NULL,
  `dt_closed` datetime NOT NULL,
  `dt_received` date NOT NULL,
  `dt_ship` date NOT NULL,
  `dt_suspended` date NOT NULL,
  `days_suspended` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `sph_id` int(11) NOT NULL,
  `rfq` varchar(20) NOT NULL,
  `qono` varchar(20) NOT NULL COMMENT 'Baan Quotation Number',
  `qono_val` int(11) NOT NULL,
  `baan_so` varchar(20) NOT NULL,
  `request_type` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL,
  `last_status` varchar(30) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `poster_id` (`poster_id`),
  KEY `sso_id` (`sph_id`),
  KEY `customer_id` (`customer_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3722 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spr`
--

DROP TABLE IF EXISTS `spr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `entered_by` int(11) NOT NULL,
  `assignor` int(11) NOT NULL,
  `sso_id` int(3) NOT NULL,
  `psr_id` int(11) NOT NULL COMMENT 'Parts Sales Rep',
  `sph_id` int(11) NOT NULL COMMENT 'Spare Parts Hub id',
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cust_nama` varchar(255) NOT NULL,
  `cust_cona` varchar(255) NOT NULL COMMENT 'attention',
  `cust_tela` varchar(20) NOT NULL COMMENT 'Customer Contact Telephone',
  `cust_emla` varchar(255) NOT NULL COMMENT 'email #1',
  `ship_to` text NOT NULL COMMENT 'ship to',
  `dt_ship` date NOT NULL,
  `nota` text NOT NULL COMMENT 'notes',
  `ship_tnum` text NOT NULL COMMENT 'tracking numbers',
  `estimated_shipping_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4912 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spr_lines`
--

DROP TABLE IF EXISTS `spr_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spr_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `item` varchar(50) NOT NULL COMMENT 'part number',
  `dsca` varchar(255) NOT NULL COMMENT 'description',
  `oqua` int(11) NOT NULL COMMENT 'order qty',
  `um` varchar(10) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8768 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sqe`
--

DROP TABLE IF EXISTS `sqe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sqe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt_open` date NOT NULL,
  `status` varchar(60) NOT NULL,
  `qcur` varchar(3) NOT NULL,
  `vendor_id` int(11) NOT NULL COMMENT 'Shipper Name',
  `container` varchar(250) NOT NULL COMMENT 'Containerization mode',
  `noncontainer` varchar(100) NOT NULL,
  `transhipment` varchar(1) NOT NULL,
  `preadviseday` int(11) NOT NULL,
  `ca_name` varchar(250) NOT NULL COMMENT 'Carrier Name',
  `ttd` int(11) NOT NULL COMMENT 'Transit time in days',
  `eta` date NOT NULL COMMENT 'Estimated Date of Arrival',
  `port_load` varchar(250) NOT NULL COMMENT 'Port loading',
  `port_dest` varchar(250) NOT NULL COMMENT 'Port destination',
  `dt_validity` date NOT NULL COMMENT 'Validity of quote',
  `note` text NOT NULL,
  `chosen` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Chosen offer ?',
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=558 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sqe_lines`
--

DROP TABLE IF EXISTS `sqe_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sqe_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `desca` text NOT NULL COMMENT 'cost description',
  `price_cur` varchar(3) NOT NULL COMMENT 'Currency',
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=924 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sqr`
--

DROP TABLE IF EXISTS `sqr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sqr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt_open` date NOT NULL,
  `status` varchar(100) NOT NULL,
  `tld_contact` int(11) NOT NULL COMMENT 'TLD shipping contact',
  `tld_entity` int(11) NOT NULL,
  `inco` varchar(3) NOT NULL COMMENT 'Inco terma',
  `inco_loc` varchar(255) NOT NULL COMMENT 'Inco location',
  `note` text NOT NULL,
  `dt_validity` date NOT NULL,
  `dt_deadline` date NOT NULL COMMENT 'Deadline date',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=475 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sqr_lines`
--

DROP TABLE IF EXISTS `sqr_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sqr_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `factory_id` int(11) NOT NULL COMMENT 'Factory',
  `container` varchar(100) NOT NULL COMMENT 'Containeryiation mode',
  `noncontainer` varchar(100) NOT NULL,
  `transhipment` varchar(1) NOT NULL,
  `customs_code` varchar(100) NOT NULL,
  `inland_er` varchar(1) NOT NULL,
  `dt_pu` date NOT NULL COMMENT 'Pick up date estimated',
  `loc_pu` text NOT NULL COMMENT 'Other location pick up',
  `note` text NOT NULL,
  `category` varchar(20) NOT NULL,
  `roll_er` varchar(1) NOT NULL COMMENT 'Rolling equipment',
  `er_type` varchar(100) NOT NULL COMMENT 'Equipment type',
  `er_model` varchar(100) NOT NULL COMMENT 'Model of the equipment',
  `er_qty` int(11) DEFAULT NULL COMMENT 'ER quantity',
  `dimgc` decimal(10,2) NOT NULL COMMENT 'Ground clearance dimension',
  `diml` decimal(10,2) NOT NULL COMMENT 'lenght',
  `dimw` decimal(10,2) NOT NULL COMMENT 'width',
  `dimh` decimal(10,2) NOT NULL COMMENT 'height',
  `dimk` decimal(10,2) NOT NULL COMMENT 'weight',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=654 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sr_parts`
--

DROP TABLE IF EXISTS `sr_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sr_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `item` varchar(30) NOT NULL DEFAULT '',
  `dsca` varchar(30) NOT NULL DEFAULT '',
  `qty` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `item` (`item`)
) ENGINE=InnoDB AUTO_INCREMENT=8338 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sr_serials`
--

DROP TABLE IF EXISTS `sr_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sr_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `serial` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4162 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `srm`
--

DROP TABLE IF EXISTS `srm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `srm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `open_date` datetime NOT NULL,
  `poster_id` int(11) NOT NULL,
  `requestor_id` int(11) NOT NULL,
  `date_from` datetime NOT NULL,
  `date_to` datetime NOT NULL,
  `item_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `poster_id` (`poster_id`),
  KEY `requestor_id` (`requestor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `srm_item`
--

DROP TABLE IF EXISTS `srm_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `srm_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` varchar(60) NOT NULL,
  `location_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `srm_itemlocation`
--

DROP TABLE IF EXISTS `srm_itemlocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `srm_itemlocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ssr`
--

DROP TABLE IF EXISTS `ssr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ssr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_access_log`
--

DROP TABLE IF EXISTS `stats_access_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_env` varchar(255) NOT NULL,
  `user_ip` varchar(100) NOT NULL,
  `request_type` varchar(20) NOT NULL,
  `request_uri` varchar(255) NOT NULL,
  `request_m0` varchar(100) NOT NULL,
  `request_m1` varchar(100) NOT NULL,
  `request_m2` varchar(100) NOT NULL,
  `request_m3` varchar(100) NOT NULL,
  `request_m4` varchar(100) NOT NULL,
  `request_m5` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7812785 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supp_classification`
--

DROP TABLE IF EXISTS `supp_classification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supp_classification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `t_suno` varchar(10) NOT NULL,
  `erp` int(11) NOT NULL,
  `quality` tinyint(2) NOT NULL DEFAULT 0,
  `delivery` tinyint(2) NOT NULL DEFAULT 0,
  `communication` tinyint(2) NOT NULL DEFAULT 0,
  `support` tinyint(2) NOT NULL DEFAULT 0,
  `innovation` tinyint(2) NOT NULL DEFAULT 0,
  `cost` tinyint(2) NOT NULL DEFAULT 0,
  `esg` varchar(3) NOT NULL DEFAULT 'N/A',
  `anti_corruption` varchar(3) NOT NULL DEFAULT 'N/A',
  `last_eval` date DEFAULT NULL,
  `last_eval_user` int(11) DEFAULT NULL,
  `supp_pur_status_id` int(11) DEFAULT NULL,
  `supp_exper_class_id` int(11) DEFAULT NULL,
  `esg_status` VARCHAR(45) NOT NULL DEFAULT 'N/A',
  `screening_status` VARCHAR(45) NOT NULL DEFAULT 'N/A',
  `last_screening_date` DATE NOT NULL DEFAULT '0000-00-00',
  `master_suno` varchar(10) DEFAULT NULL,
  `master_erp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_suno` (`t_suno`,`erp`)
) ENGINE=InnoDB AUTO_INCREMENT=5109 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supp_exper_class`
--

DROP TABLE IF EXISTS `supp_exper_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supp_exper_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` text DEFAULT NULL,
  `quality` tinyint(2) NOT NULL DEFAULT 0,
  `delivery` tinyint(2) NOT NULL DEFAULT 0,
  `communication` tinyint(2) NOT NULL DEFAULT 0,
  `support` tinyint(2) NOT NULL DEFAULT 0,
  `innovation` tinyint(2) NOT NULL DEFAULT 0,
  `cost` tinyint(2) NOT NULL DEFAULT 0,
  `esg` tinyint(2) NOT NULL DEFAULT 0,
  `anti_corruption` tinyint(2) NOT NULL DEFAULT 0,
  `frequency_period` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supp_pur_status`
--

DROP TABLE IF EXISTS `supp_pur_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supp_pur_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rank` varchar(45) NOT NULL,
  `description` text DEFAULT NULL,
  `frequence_ranking` tinyint(2) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tas`
--

DROP TABLE IF EXISTS `tas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `email` varchar(250) NOT NULL,
  `token` varchar(32) NOT NULL,
  `ressource` varchar(10) NOT NULL,
  `ressource_ref` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `status` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=3608 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tas_log`
--

DROP TABLE IF EXISTS `tas_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tas_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `ip` varchar(60) NOT NULL,
  `action` varchar(100) NOT NULL,
  `server_env` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=795 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `erp` int(4) DEFAULT 0,
  `bu_id` int(3) NOT NULL,
  `module` varchar(10) NOT NULL DEFAULT '',
  `assignor` int(11) NOT NULL DEFAULT 0,
  `status` varchar(15) NOT NULL DEFAULT 'OPEN',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `task` text NOT NULL,
  `assignee` int(11) NOT NULL DEFAULT 0,
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  `d_escal` date NOT NULL DEFAULT '0000-00-00',
  `escalation_trigger` int(11) NOT NULL DEFAULT 60,
  `dt_closed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `seq` char(1) NOT NULL DEFAULT 'N',
  `seq_mode` varchar(60) NOT NULL,
  `tplno` int(11) NOT NULL DEFAULT 0,
  `cur_step` int(11) NOT NULL DEFAULT 0,
  `close_params` text NOT NULL,
  `hours` int(11) NOT NULL,
  `ifactor` int(11) NOT NULL DEFAULT 1,
  `dcomp` date NOT NULL,
  `cat` varchar(1) NOT NULL COMMENT 'MIS task category',
  `ticket_module_id` int(11) NULL DEFAULT NULL,
  `closed_not_done` tinyint DEFAULT 0 NOT NULL,
  `reason` varchar(10),
  PRIMARY KEY (`id`),
  KEY `assignor` (`assignor`),
  KEY `assignee` (`assignee`),
  KEY `parent_id` (`parent_id`,`module`),
  KEY `bu_id` (`bu_id`),
  KEY `status` (`status`),
  KEY `seq` (`seq`),
  KEY `module` (`module`),
  KEY `tplno` (`tplno`)
) ENGINE=InnoDB AUTO_INCREMENT=610956 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO tasks (parent_id, erp, bu_id, module, assignor, status, date, task, assignee) VALUES (1, 500, 45, 'NCR', 2374, 'OPEN', '2020-12-24', 'test for NCR', 2374);
INSERT INTO tasks (parent_id, erp, bu_id, module, assignor, status, date, task, assignee) VALUES (1,500, 45, 'CRAB', 2374, 'OPEN', '2020-12-24', 'test for CRAB', 2374);
INSERT INTO tasks (id, parent_id, erp, bu_id, module, assignor, status, date, task, assignee) VALUES (13, 1,500, 45, 'MIS', 2374, 'OPEN', '2020-12-24', 'test for MIS', 2374);

--
-- Table structure for table `tasks_comments`
--

DROP TABLE IF EXISTS `tasks_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `step` int(11) DEFAULT 0,
  `status` varchar(10) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `poster` int(11) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `poster` (`poster`)
) ENGINE=InnoDB AUTO_INCREMENT=2782825 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timekeeping`
--

DROP TABLE IF EXISTS `timekeeping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timekeeping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `tld_dpt` varchar(30) NOT NULL,
  `location` int(11) NOT NULL COMMENT 'Company/location',
  `user_id` int(11) NOT NULL COMMENT 'Employee ID',
  `dt_open` date NOT NULL,
  `category` varchar(80) NOT NULL COMMENT 'Work activity',
  `product_type` varchar(150) NOT NULL COMMENT 'Model',
  `project` varchar(40) NOT NULL COMMENT 'Project type',
  `link` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL COMMENT 'Task ID',
  `time_actual` int(4) NOT NULL COMMENT 'Hours (actual) in % of day',
  `time_forecast` int(4) NOT NULL COMMENT 'Hours (forecast) in % of day',
  `hours_actual` decimal(3,1) NOT NULL,
  `hours_forecast` decimal(3,1) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_timekeeping_link` (`link`),
  KEY `idx_timekeeping_module_id` (`module_id`),
  KEY `idx_timekeeping_link_id_module_id` (`link`,`module_id`),
  KEY `idx_timekeeping_user_id` (`user_id`),
  KEY `idx_timekeeping_location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=105773 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timekeeping_lists`
--

DROP TABLE IF EXISTS `timekeeping_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timekeeping_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `status` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `tk_in_eap_linked_to_meap`
--

DROP TABLE IF EXISTS `tk_in_eap_linked_to_meap`;
/*!50001 DROP VIEW IF EXISTS `tk_in_eap_linked_to_meap`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_eap_linked_to_meap` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_in_meap`
--

DROP TABLE IF EXISTS `tk_in_meap`;
/*!50001 DROP VIEW IF EXISTS `tk_in_meap`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_meap` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_in_subeap_lvl1`
--

DROP TABLE IF EXISTS `tk_in_subeap_lvl1`;
/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl1`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_subeap_lvl1` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_in_subeap_lvl2`
--

DROP TABLE IF EXISTS `tk_in_subeap_lvl2`;
/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_subeap_lvl2` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_in_subeap_lvl3`
--

DROP TABLE IF EXISTS `tk_in_subeap_lvl3`;
/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl3`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_subeap_lvl3` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_in_subeap_lvl4`
--

DROP TABLE IF EXISTS `tk_in_subeap_lvl4`;
/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl4`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_in_subeap_lvl4` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_eap_linked_to_meap`
--

DROP TABLE IF EXISTS `tk_tasks_in_eap_linked_to_meap`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_eap_linked_to_meap`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_eap_linked_to_meap` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_meap`
--

DROP TABLE IF EXISTS `tk_tasks_in_meap`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_meap`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_meap` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_subeap_lvl1`
--

DROP TABLE IF EXISTS `tk_tasks_in_subeap_lvl1`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl1`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_subeap_lvl1` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_subeap_lvl2`
--

DROP TABLE IF EXISTS `tk_tasks_in_subeap_lvl2`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_subeap_lvl2` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_subeap_lvl3`
--

DROP TABLE IF EXISTS `tk_tasks_in_subeap_lvl3`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl3`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_subeap_lvl3` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tk_tasks_in_subeap_lvl4`
--

DROP TABLE IF EXISTS `tk_tasks_in_subeap_lvl4`;
/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl4`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tk_tasks_in_subeap_lvl4` AS SELECT
 1 AS `tk_id`,
 1 AS `tk_dt_open`,
 1 AS `tk_category,`,
 1 AS `tk_time_actual`,
 1 AS `tk_hours_actual`,
 1 AS `tk_tld_dpt`,
 1 AS `tk_user_id`,
 1 AS `meap_id`,
 1 AS `tk_module`,
 1 AS `tk_module_id`,
 1 AS `tk_location`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tld_departments`
--

DROP TABLE IF EXISTS `tld_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tld_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dpt` varchar(100) NOT NULL,
  `sso` char(1) NOT NULL,
  `erp` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dpt` (`dpt`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_divisions`
--

DROP TABLE IF EXISTS `tld_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE tld_divisions
(
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    CONSTRAINT tld_divisions_pk PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE UNIQUE INDEX tld_divisions_name_uindex ON tld_divisions (name);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_sub_divisions`
--

DROP TABLE IF EXISTS `tld_sub_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE tld_sub_divisions
(
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `division_id` int(11) NOT NULL,
    CONSTRAINT tld_sub_divisions_pk PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE UNIQUE INDEX tld_sub_divisions_name_uindex ON tld_sub_divisions (name);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_regions`
--

DROP TABLE IF EXISTS `tld_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tld_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `division` varchar(100) NOT NULL,
  `repid` int(11) NOT NULL,
  `sub_division_id` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `repid` (`repid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_regions_locations`
--

DROP TABLE IF EXISTS `tld_regions_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tld_regions_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `buid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_id` (`parent_id`,`buid`),
  KEY `buid` (`buid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_functions`
--

DROP TABLE IF EXISTS `tld_functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tld_functions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `dsc` varchar(250) NOT NULL COMMENT 'Description',
  `level` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tld_juridical_locations`
--

DROP TABLE IF EXISTS `tld_juridical_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tld_juridical_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `address` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `toc`
--

DROP TABLE IF EXISTS `toc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `postid` int(11) NOT NULL,
  `ssoid` int(11) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp DEFAULT NULL,
  `dt_closed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(20) NOT NULL,
  `tecid` int(11) NOT NULL,
  `assid` int(11) NOT NULL COMMENT 'Assignee ID',
  `cuid` int(11) NOT NULL COMMENT 'customer id',
  `conid` int(11) NOT NULL COMMENT 'customer contact id',
  `erid` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `short_desc` varchar(100) NOT NULL COMMENT 'Short problem description',
  `prob_dsca` text NOT NULL COMMENT 'problem desc from customer',
  `disp_tec` varchar(1) NOT NULL,
  `ifactor` varchar(10) NOT NULL COMMENT 'Important factor',
  `wfactor` float NOT NULL COMMENT 'Weight Factor',
  `est_hours` int(11) NOT NULL COMMENT 'Estimated hours',
  `third_party` varchar(1) NOT NULL COMMENT 'Third party?',
  `apc` varchar(10) NOT NULL COMMENT 'Airport code',
  `activity_type` varchar(100) NOT NULL,
  `toc_type` varchar(25) NOT NULL DEFAULT 'Not Defined Yet',
  `notification` varchar(1) NOT NULL COMMENT 'Notification enable?',
  `unit_operation_status` varchar(10) NOT NULL,
  `action_module` varchar(10) NOT NULL COMMENT 'Further action module',
  `action_ref` int(11) NOT NULL COMMENT 'Further action module id',
  `survey_work` varchar(1) NOT NULL,
  `survey_responsiveness` varchar(1) NOT NULL,
  `survey_communication` varchar(1) NOT NULL,
  `survey_attitude` varchar(1) NOT NULL,
  `survey_comment` text NOT NULL,
  `factory_support_flag` tinyint(4) NOT NULL DEFAULT 0,
  `parts_notification_at` datetime DEFAULT NULL,
  `parts_received_at` datetime DEFAULT NULL,
  `ast_arrived_at` datetime DEFAULT NULL,
  `ast_left_at` datetime DEFAULT NULL,
  `factory_support_required_at` datetime DEFAULT NULL,
  `factory_support_given_at` datetime DEFAULT NULL,
  `tld_notification` boolean DEFAULT TRUE,
  `error_codes` varchar(500) DEFAULT NULL,
  `is_ibs` tinyint(1) DEFAULT 0,
  `is_ihs` tinyint(1) DEFAULT 0,
  `is_link` tinyint(1) DEFAULT 0,
  `warranty_id` int(11) DEFAULT NULL,
  `parts_added` tinyint(1) DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `erid` (`erid`),
  KEY `cuid` (`cuid`),
  KEY `conid` (`conid`),
  KEY `postid` (`postid`),
  KEY `status` (`status`),
  KEY `ssoid` (`ssoid`),
  KEY `tecid` (`tecid`),
  KEY `assid` (`assid`)
) ENGINE=InnoDB AUTO_INCREMENT=37024 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
CREATE INDEX toc_warranty_index ON toc (warranty_id);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 65424, 724, 'Oil leaking', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Warranty', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 0);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 65424, 724, 'Oil leaking', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Warranty', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 57126);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 37455, 724, 'Oil leaking', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Warranty', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 57126);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 37469, 724, 'Oil leaking', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Warranty', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 57126);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 37465, 724, 'Oil leaking', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Warranty', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 57126);
INSERT INTO toc (parent_id, postid, ssoid, dt, dt_closed, status, tecid, assid, cuid, conid, erid, hours, short_desc, prob_dsca, disp_tec, ifactor, wfactor, est_hours, third_party, apc, activity_type, notification, unit_operation_status, action_module, action_ref, survey_work, survey_responsiveness, survey_communication, survey_attitude, survey_comment, factory_support_flag, parts_notification_at, parts_received_at, ast_arrived_at, ast_left_at, factory_support_required_at, factory_support_given_at, tld_notification, error_codes, toc_type, is_ibs, is_ihs, is_link, warranty_id) VALUES (0, 6082, 11, '2021-06-04 11:53:52', '2021-06-04 11:57:06', 'SOLVED', 6082, 1507, 201, 1985, 65424, 724, 'Commissioning intervention', '', '', '1', 1.25727, 0, 'N', 'ONT', 'Commissioning', 'N', 'MCF', '', 0, '', '', '', '', '', 0, null, null, null, null, null, null, 1, '', 'Warranty', 0, 0, 0, 63);

--
-- Table structure for table `toc_contacts`
--

DROP TABLE IF EXISTS `toc_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toc_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1739 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `toc_not`
--

DROP TABLE IF EXISTS `toc_not`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toc_not` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `recipients` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `email` text NOT NULL,
  `fid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `parent_id` (`parent_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=30174 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `toc_zones`
--

DROP TABLE IF EXISTS `toc_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toc_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT 'Link to countries',
  `zone` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=248 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `en` varchar(255) NOT NULL,
  `fr` text NOT NULL,
  `es` text NOT NULL,
  `zh` text NOT NULL,
  `pt` text NOT NULL,
  `de` text NOT NULL,
  `ja` text NOT NULL,
  `ru` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `en_unique` (`en`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vat`
--

DROP TABLE IF EXISTS `vat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cu_code` varchar(10) NOT NULL,
  `cu_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `remark` text NOT NULL,
  `vat_date` date NOT NULL,
  `vat_no` varchar(20) NOT NULL,
  `gt_no` varchar(50) NOT NULL,
  `invoice_no` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `vat_no` (`vat_no`)
) ENGINE=MyISAM AUTO_INCREMENT=1022 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vat_comments`
--

DROP TABLE IF EXISTS `vat_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vat_comments` (
  `id` int(20) NOT NULL,
  `tobeinvoice` varchar(1) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `userid` varchar(100) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `title` varchar(10) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `language` varchar(2) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `fax` varchar(30) NOT NULL,
  `country` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `email` varchar(256) NOT NULL,
  `company` varchar(50) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `tld_rep_id_bak` int(11) NOT NULL DEFAULT 0,
  `login_counter` int(11) NOT NULL DEFAULT 0,
  `last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fl_email_report` varchar(1) NOT NULL DEFAULT 'Y',
  `fl_class` varchar(1) NOT NULL COMMENT 'Vendor class A or B',
  `enable` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'is enable ?',
  `last_edit` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=7940 DEFAULT CHARSET=latin1;
INSERT INTO vendors (id, parent_id, userid, firstname, lastname, title, position, department, language, zip_code, phone, mobile, fax, country, comment, email, address, password, fl_class, enable) VALUES (5, 5, 'darty@vendor.fr', 'darty', 'vendor', 'mr', 'vendeur', 'sales', 'fr', '37000', '0606060606', '0606060606', '0606060606', 'France', 'comment', 'darty@vendor.fr', 'somewhere over the rainbow', 'darty123','A', 'Y');

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendors_groups`
--

DROP TABLE IF EXISTS `vendors_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `userid` varchar(50) NOT NULL DEFAULT '',
  `groupid` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20117 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO vendors_groups (id, parent_id, userid, groupid) VALUES (5, 5, 5, 'gg_QA');

--
-- Table structure for table `vendors_login_logs`
--

DROP TABLE IF EXISTS `vendors_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors_login_logs` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `dt` datetime NOT NULL,
  `user_agent` varchar(256) NOT NULL,
  `http_referer` varchar(256) NOT NULL,
  `portal` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52784 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendors_pwhist`
--

DROP TABLE IF EXISTS `vendors_pwhist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors_pwhist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `change_date` date DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6043 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendors_suno`
--

DROP TABLE IF EXISTS `vendors_suno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors_suno` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `erp` int(3) NOT NULL,
  `t_suno` varchar(10) NOT NULL,
  `type` varchar(10) NOT NULL,
  `tld_rep_id` int(11) NOT NULL,
  `carrier` varchar(255) NOT NULL,
  `ship_acct_num` varchar(30) NOT NULL,
  `t_nama` varchar(45) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15622 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO vendors_suno (id, parent_id, erp, t_suno, type, tld_rep_id, carrier, ship_acct_num, t_nama) VALUES (5, 5, 500, 'TA2500', 'vendor', 10, 'FEDEX', 666, 'ALOT METAL S.L.');

--
-- Table structure for table `vwc`
--

DROP TABLE IF EXISTS `vwc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vwc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `module` varchar(3) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `erp` int(4) NOT NULL DEFAULT 0,
  `entered_by` int(11) NOT NULL,
  `assignee` int(11) NOT NULL,
  `suno` varchar(10) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'PENDING',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `req` text NOT NULL,
  `req_crd_amt` decimal(10,2) DEFAULT NULL COMMENT 'Requested credit amount',
  `scar` varchar(1) NOT NULL DEFAULT 'N',
  `scarno` int(11) NOT NULL,
  `shp_nam` varchar(100) NOT NULL,
  `shp_trk_no` varchar(100) NOT NULL,
  `su_rma` varchar(20) NOT NULL,
  `su_crd_not` varchar(20) NOT NULL,
  `su_crd_amt` decimal(10,2) NOT NULL,
  `act_crd_amt` decimal(10,2) NOT NULL,
  `su_shp_inst` text NOT NULL,
  `su_accepted` varchar(1) DEFAULT NULL,
  `cost_break` text DEFAULT NULL,
  `sbdp` varchar(1) NOT NULL COMMENT 'Defective Parts To Ship Back?',
  `resolution` text NOT NULL,
  `supplier_erp` int(4) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `suno` (`suno`),
  KEY `module` (`module`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78109 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vwc_notes`
--

DROP TABLE IF EXISTS `vwc_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vwc_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `poster` varchar(50) NOT NULL DEFAULT '',
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vwc_parts`
--

DROP TABLE IF EXISTS `vwc_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vwc_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `supply_it` varchar(11) NOT NULL DEFAULT '',
  `part_number` varchar(30) NOT NULL DEFAULT '',
  `sn` varchar(20) NOT NULL,
  `vendor_pn` varchar(20) NOT NULL DEFAULT '',
  `vendor_sn` varchar(20) NOT NULL DEFAULT '',
  `part_description` varchar(30) NOT NULL DEFAULT '',
  `brand` varchar(30) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `rec_qty` int(11) NOT NULL DEFAULT 0,
  `um` varchar(10) NOT NULL DEFAULT '',
  `failure_type` varchar(20) NOT NULL DEFAULT '',
  `failure_system` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `part_number` (`part_number`)
) ENGINE=InnoDB AUTO_INCREMENT=44943 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `warranty`
--

DROP TABLE IF EXISTS `warranty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warranty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `warranty_status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `entered_by` varchar(50) NOT NULL DEFAULT '',
  `claimant_details` text DEFAULT NULL,
  `warranty_details` text DEFAULT NULL,
  `customer_name` varchar(80) DEFAULT NULL,
  `claim_date` date DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `model` varchar(255) NOT NULL DEFAULT '',
  `man_location` varchar(30) NOT NULL DEFAULT '',
  `sales_org` varchar(30) NOT NULL DEFAULT '',
  `serial_number` varchar(30) DEFAULT NULL,
  `equipment_location` text DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `er_operation_status` varchar(3) NOT NULL,
  `problem_desc` text DEFAULT NULL,
  `extranet_prob_desc` text NOT NULL,
  `failure_code1` char(2) NOT NULL DEFAULT '',
  `failure_code2` char(2) NOT NULL DEFAULT '',
  `intervention` char(3) NOT NULL DEFAULT '',
  `est_man_hours` int(11) DEFAULT NULL,
  `prod_man_accept_user` varchar(50) DEFAULT NULL,
  `prod_man_accept_date` date DEFAULT NULL,
  `parts_date_delivery` date DEFAULT NULL,
  `prod_man_comments` text NOT NULL,
  `parts_courier` text DEFAULT NULL,
  `return_parts` char(3) NOT NULL DEFAULT '',
  `service_accept_date` date DEFAULT NULL,
  `service_date_delivery` date DEFAULT NULL,
  `service_comments` text NOT NULL,
  `service_ship_inst` text NOT NULL,
  `technician` varchar(30) NOT NULL,
  `technician_cost_te` decimal(10,2) DEFAULT NULL,
  `technician_cost_labour` decimal(10,2) DEFAULT NULL,
  `parts_cost` decimal(10,2) DEFAULT NULL,
  `note_cost` text NOT NULL COMMENT 'costs note (backup of field costs)',
  `part_failing` varchar(100) NOT NULL COMMENT 'critical PN failing',
  `part_return_address` text NOT NULL,
  `part_return_date` date NOT NULL DEFAULT '0000-00-00',
  `parts_order_ref` text NOT NULL,
  `filtering_flag` varchar(60) NOT NULL,
  `dt_filtering_flag` date NOT NULL,
  `category` varchar(55) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entered_by` (`entered_by`),
  KEY `man_location` (`man_location`),
  KEY `serial_number` (`serial_number`),
  KEY `customer_name` (`customer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=57126 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_accept_user, prod_man_accept_date, parts_date_delivery, prod_man_comments, parts_courier, return_parts, service_accept_date, service_date_delivery, service_comments, service_ship_inst, technician, technician_cost_te, technician_cost_labour, parts_cost, note_cost, part_failing, part_return_address, part_return_date, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (58, 37454, 'PENDING', 'someone@tld-america.com', 'Customer claim', 'details', 'OOOOOPS', '2021-06-04', 'Loaders', '929', 'TLD TBD', 'TLD TBD', 'T1000', 'ONT', 724, 'MCF', 'The customer reports that it does not work.', 'The customer reports that the unit is leaking oil.', '', '', 'NO', 0, null, null, '', '', '', '', null, null, '', '', 'One, some', 0.00, 90.00, 0.00, 'Customer invoice - $2.00', '', '', '', '', 'TO BE FILTERED', '');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_comments, return_parts, service_comments, service_ship_inst, technician, note_cost, part_failing, part_return_address, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (59,65425,'APPROVED','user-ast-bu2@tld.fr','Second claim','Additional details','customer_for_fur','2023-11-15','Belt Loaders','NBL-E','TLD USA','TLD USA','T334455','LAX',3200,'MCF','Engine issue','Engine overheating','','','YES',4,'','','','','Technician Two','','','','','TO BE FILTERED', '2023-11-15');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_comments, return_parts, service_comments, service_ship_inst, technician, note_cost, part_failing, part_return_address, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (60,65426,'PENDING','representative_6_@loop.io','First claim','<p>\n    SHIPPED: 2024-01-30,\n</p>\n<p>\n    WARRANTY LEN: 24,\n</p>\n<p>\n    WARRANTY END: 0000-00-00\n</p>\n<p>\n    <b>Special Warranty Conditions:</b><br>\n    \r\nThe equipment delivered by TLD is guaranteed against any material and construction defects, in accordance with TLD general warranty conditions (available on TLD website at https://www.tld-group.com/wp-content/uploads/tld-media/tld-general-warranty-conditions.pdf), The warranty claims are only considered after full payment of the equipment by the Buyer to TLD.\n</p>','customer_for_fur','2023-01-22','Lavatory and Water Trucks','LC100E-SL Lavatory Service Car','TLD WIN','TLD USA','T85401','UBP',3200,'MCF','Engine issue','Engine overheating','','','YES',4,'','','','','Technician Two','','','','','TO BE FILTERED', '2023-11-15');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_comments, return_parts, service_comments, service_ship_inst, technician, note_cost, part_failing, part_return_address, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (61,65427,'CLOSED','user-ast-bu2@tld.fr','Second claim','Additional details','AIR DE RIEN','2023-11-15','Belt Loaders','NBL-E','TLD USA','TLD USA','z68919','LAX porte 2 en bas a droite',3200,'MCF','Engine issue','Engine overheating','','','YES',4,'','','','','Technician Two','','','','','TO BE FILTERED', '2023-11-15');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_accept_user, prod_man_accept_date, parts_date_delivery, prod_man_comments, parts_courier, return_parts, service_accept_date, service_date_delivery, service_comments, service_ship_inst, technician, technician_cost_te, technician_cost_labour, parts_cost, note_cost, part_failing, part_return_address, part_return_date, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (62, 37053, 'PENDING', 'someone@tld-america.com', 'Customer claim', 'details', 'OOOOOPS', '2021-06-04', 'Loaders', '929', 'TLD TBD', 'TLD TBD', 'T1000', 'ONT', 724, 'MCF', 'The customer reports that it does not work.', 'The customer reports that the unit is leaking oil.', '', '', 'NO', 0, null, null, '', '', '', '', null, null, '', '', 'One, some', 0.00, 90.00, 0.00, 'Customer invoice - $2.00', '', '', '', '', 'TO BE FILTERED', '');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_comments, return_parts, service_comments, service_ship_inst, technician, note_cost, part_failing, part_return_address, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (63,65428,'PENDING','user-ast-bu2@tld.fr','Commissioning claim','Additional details','commissioning_hidden','2023-11-15','Belt Loaders','NBL-E','TLD USA','TLD USA','T85401','LAX',3200,'MCF','Engine issue','Engine overheating','','','YES',4,'','','','','Technician Two','','','','','TO BE FILTERED', '2023-11-15');
INSERT INTO warranty (id, parent_id, warranty_status, entered_by, claimant_details, warranty_details, customer_name, claim_date, type, model, man_location, sales_org, serial_number, equipment_location, hours, er_operation_status, problem_desc, extranet_prob_desc, failure_code1, failure_code2, intervention, est_man_hours, prod_man_comments, return_parts, service_comments, service_ship_inst, technician, note_cost, part_failing, part_return_address, parts_order_ref, filtering_flag, dt_filtering_flag) VALUES (64,37025,'REJECTED','user-ast-bu2@tld.fr','Will be reopen on change type to factory support','Additional details','commissioning_hidden','2023-11-15','Belt Loaders','NBL-E','TLD USA','TLD USA','T85401','LAX',3200,'MCF','Engine issue','Engine overheating','','','YES',4,'','','','','Technician Two','','','','','TO BE FILTERED', '2023-11-15');

-- Table structure for table `warranty_files`
--

DROP TABLE IF EXISTS `warranty_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warranty_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  `public` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46832 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO warranty_files (id, parent_id, date, description, filename) VALUES (60, 58, '1990-08-11', 'just for test', 'file.txt');

--
-- Table structure for table `warranty_parts`
--

DROP TABLE IF EXISTS `warranty_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warranty_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `supply_it` varchar(11) NOT NULL DEFAULT '',
  `part_number` varchar(30) NOT NULL DEFAULT '',
  `part_description` varchar(60) NOT NULL DEFAULT '',
  `brand` varchar(30) NOT NULL DEFAULT '',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_in` decimal(10,2) NOT NULL COMMENT 'qty returned to TLD',
  `d_in` date NOT NULL COMMENT 'Date parts returned',
  `um` varchar(10) NOT NULL DEFAULT '',
  `failure_type` varchar(20) NOT NULL DEFAULT '',
  `failure_system` varchar(20) NOT NULL DEFAULT '',
  `sn` varchar(20) NOT NULL COMMENT 'replacement part component serial number',
  `notes` text DEFAULT NULL,
  `spr_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `part_number` (`part_number`)
) ENGINE=InnoDB AUTO_INCREMENT=109095 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO warranty_parts (parent_id, supply_it, part_number, part_description, brand, quantity, qty_in, d_in, um, failure_type, failure_system, sn, notes, spr_id) VALUES (57126, 'YES', 'WW2006', 'Willy Waller', '', 1.00, 0.00, '', '', '', '', '', '', null);

INSERT INTO spr (id, parent_id, status, entered_by, assignor, sso_id, psr_id, sph_id, dt, cust_nama, cust_cona, cust_tela, cust_emla, ship_to, dt_ship, nota, ship_tnum, estimated_shipping_date) VALUES (9001, 60, 'SHIPPED', 1, 1, 1, 1, 1, '2024-01-30 10:00:00', 'customer_for_fur', '', '', '', '', '2024-02-01', '', '1Z999AA10123456784', null);

INSERT INTO warranty_parts (parent_id, supply_it, part_number, part_description, brand, quantity, qty_in, d_in, um, failure_type, failure_system, sn, notes, spr_id) VALUES (60, 'YES', 'WW2006', 'Willy Waller', '', 1.00, 0.00, '', 'EA', '', '', '', '', 9001);
INSERT INTO warranty_parts (parent_id, supply_it, part_number, part_description, brand, quantity, qty_in, d_in, um, failure_type, failure_system, sn, notes, spr_id) VALUES (60, 'NO', 'WW2007', 'Willy Waller XL', '', 2.00, 0.00, '', 'EA', '', '', '', '', null);

--
-- Table structure for table `warranty_tracking`
--

DROP TABLE IF EXISTS `warranty_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warranty_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `erp` int(11) NOT NULL,
  `packing_slip` int(11) NOT NULL,
  `so_no` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `tracking_no` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16614 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pi_bijection`
--
DROP TABLE IF EXISTS `pi_bijection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pi_bijection` (
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `erp` SMALLINT(3),
    `family_id` SMALLINT(5),
    `operation_number` SMALLINT(3),
    `item` VARCHAR(16),
    `description` VARCHAR(255)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `tk_in_eap_linked_to_meap`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_eap_linked_to_meap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_eap_linked_to_meap` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`meap`.`id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((`timekeeping` left join `eap` on(`eap`.`id` = `timekeeping`.`module_id`)) join `meap` on(`meap`.`id` = `eap`.`parent_id`)) where `timekeeping`.`link` = 'EAP' and `eap`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_in_meap`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_meap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_meap` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`timekeeping`.`module_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from `timekeeping` where `timekeeping`.`link` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_in_subeap_lvl1`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl1`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_subeap_lvl1` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((`timekeeping` join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `timekeeping`.`module_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_in_subeap_lvl2`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_subeap_lvl2` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from (((`timekeeping` join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `timekeeping`.`module_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_in_subeap_lvl3`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl3`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_subeap_lvl3` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((((`timekeeping` join `eap` `eap_lvl4` on(`eap_lvl4`.`id` = `timekeeping`.`module_id` and `eap_lvl4`.`parent_module` = 'EAP')) join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `eap_lvl4`.`parent_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_in_subeap_lvl4`
--

/*!50001 DROP VIEW IF EXISTS `tk_in_subeap_lvl4`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_in_subeap_lvl4` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`timekeeping`.`module_id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from (((((`timekeeping` join `eap` `eap_lvl5` on(`eap_lvl5`.`id` = `timekeeping`.`module_id` and `eap_lvl5`.`parent_module` = 'EAP')) join `eap` `eap_lvl4` on(`eap_lvl4`.`id` = `eap_lvl5`.`parent_id` and `eap_lvl4`.`parent_module` = 'EAP')) join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `eap_lvl4`.`parent_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_eap_linked_to_meap`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_eap_linked_to_meap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_eap_linked_to_meap` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) left join `eap` on(`eap`.`id` = `tasks`.`parent_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'EAP' and `eap`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_meap`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_meap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_meap` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`tasks`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from (`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_subeap_lvl1`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl1`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_subeap_lvl1` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from (((`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `tasks`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_subeap_lvl2`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_subeap_lvl2` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((((`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `tasks`.`parent_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_subeap_lvl3`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl3`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_subeap_lvl3` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from (((((`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) join `eap` `eap_lvl4` on(`eap_lvl4`.`id` = `tasks`.`parent_id` and `eap_lvl4`.`parent_module` = 'EAP')) join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `eap_lvl4`.`parent_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tk_tasks_in_subeap_lvl4`
--

/*!50001 DROP VIEW IF EXISTS `tk_tasks_in_subeap_lvl4`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`192.111.1.87` SQL SECURITY DEFINER */
/*!50001 VIEW `tk_tasks_in_subeap_lvl4` AS select `timekeeping`.`id` AS `tk_id`,`timekeeping`.`dt_open` AS `tk_dt_open`,`timekeeping`.`time_actual` AS `tk_time_actual`,`timekeeping`.`hours_actual` AS `tk_hours_actual`,`timekeeping`.`tld_dpt` AS `tk_tld_dpt`,`timekeeping`.`user_id` AS `tk_user_id`,`eap_lvl1`.`parent_id` AS `meap_id`,`timekeeping`.`link` AS `tk_module`,`tasks`.`id` AS `tk_module_id`,`timekeeping`.`location` AS `tk_location` from ((((((`timekeeping` left join `tasks` on(`tasks`.`id` = `timekeeping`.`module_id`)) join `eap` `eap_lvl5` on(`eap_lvl5`.`id` = `tasks`.`parent_id` and `eap_lvl5`.`parent_module` = 'EAP')) join `eap` `eap_lvl4` on(`eap_lvl4`.`id` = `eap_lvl5`.`parent_id` and `eap_lvl4`.`parent_module` = 'EAP')) join `eap` `eap_lvl3` on(`eap_lvl3`.`id` = `eap_lvl4`.`parent_id` and `eap_lvl3`.`parent_module` = 'EAP')) join `eap` `eap_lvl2` on(`eap_lvl2`.`id` = `eap_lvl3`.`parent_id` and `eap_lvl2`.`parent_module` = 'EAP')) join `eap` `eap_lvl1` on(`eap_lvl1`.`id` = `eap_lvl2`.`parent_id`)) where `timekeeping`.`link` = 'Task' and `tasks`.`module` = 'EAP' and `eap_lvl1`.`parent_module` = 'MEAP' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-08 14:43:52
