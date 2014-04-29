<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesOPACAnonymous.class.php,v 1.18 2013-02-20 16:09:28 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

define("LIST_LOAN_LATE",0);
define("LIST_LOAN_CURRENT",1);
define("LIST_LOAN_PRECEDENT",2);

class pmbesOPACAnonymous extends external_services_api_class{
	var $error=false;		//Y-a-t-il eu une erreur
	var $error_message="";	//Message correspondant � l'erreur
	
	function restore_general_config() {
		
	}
	
	function form_general_config() {
		return false;
	}
	
	function save_general_config() {
		
	}

	function simpleSearch($searchType=0,$searchTerm="",$PMBUserId=-1, $OPACEmprId=-1) {
		return $this->proxy_parent->pmbesSearch_simpleSearch($searchType, $searchTerm, -1, 0);
	}
	
	function simpleSearchLocalise($searchType=0,$searchTerm="",$PMBUserId=-1, $OPACEmprId=-1,$location,$section=0) {
		return $this->proxy_parent->pmbesSearch_simpleSearchLocalise($searchType, $searchTerm, -1, 0,$location,$section=0);
	}
	function get_sort_types() {
		return $this->proxy_parent->pmbesSearch_get_sort_types();
	}
	
	function fetchSearchRecords($searchId, $firstRecord, $recordCount, $recordFormat, $recordCharset='iso-8859-1') {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecords($searchId, $firstRecord, $recordCount, $recordFormat, $recordCharset, true, true);
	}

	function fetchSearchRecordsSorted($searchId, $firstRecord, $recordCount, $recordFormat, $recordCharset='iso-8859-1', $sort_type="") {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsSorted($searchId, $firstRecord, $recordCount, $recordFormat, $recordCharset, true, true, $sort_type);
	}
	
	function fetchSearchRecordsArray($searchId, $firstRecord, $recordCount, $recordCharset='iso-8859-1') {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsArray($searchId, $firstRecord, $recordCount, $recordCharset, true, true);
	}
	
	function fetchSearchRecordsArraySorted($searchId, $firstRecord, $recordCount, $recordCharset='iso-8859-1', $sort_type="") {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsArraySorted($searchId, $firstRecord, $recordCount, $recordCharset, true, true, $sort_type);
	}
	
	function getAdvancedSearchFields($lang, $fetch_values=false) {
		return $this->proxy_parent->pmbesSearch_getAdvancedSearchFields("opac|search_fields", $lang, $fetch_values);
	}
	
	function getAdvancedExternalSearchFields($lang, $fetch_values=false) {
		return $this->proxy_parent->pmbesSearch_getAdvancedSearchFields("opac|search_fields_unimarc", $lang, $fetch_values);
	}
	
	function advancedSearch($search_description) {
		return $this->proxy_parent->pmbesSearch_advancedSearch("opac|search_fields", $search_description, -1, 0);
	}
	
	function advancedSearchExternal($search_description, $source_ids) {
		array_walk($source_ids, create_function('&$a', '$a+=0;')); //Soyons s�r de ne stocker que des entiers dans le tableau.
		$source_ids = array_unique($source_ids);
		if (!$source_ids)
			return FALSE;
		return $this->proxy_parent->pmbesSearch_advancedSearch("opac|search_fields_unimarc|sources(".implode(',',$source_ids).")", $search_description, -1, 0);
	}

	function fetch_notice_items($notice_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC pour les droits d'affichage
		return $this->proxy_parent->pmbesItems_fetch_notice_items($notice_id, 0);
	}
	
	function listNoticeExplNums($notice_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC pour les droits d'affichage
		return $this->proxy_parent->pmbesNotices_listNoticeExplNums($notice_id, 0);
	}
	
	function listBulletinExplNums($bulletinId) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC pour les droits d'affichage
		return $this->proxy_parent->pmbesNotices_listBulletinExplNums($bulletinId, 0);
	}
	
	function fetchNoticeList($noticelist, $recordFormat, $recordCharset) {
		if (!is_array($noticelist))
			return array();
			
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC pour les droits d'affichage

		return $this->proxy_parent->pmbesNotices_fetchNoticeList($noticelist, $recordFormat, $recordCharset, true, true);
	}

	function fetchExternalNoticeList($noticelist, $recordFormat, $recordCharset) {
		return $this->proxy_parent->pmbesNotices_fetchExternalNoticeList($noticelist, $recordFormat, $recordCharset);
	}
	
	function fetchNoticeListArray($noticelist, $recordCharset) {
		if (!is_array($noticelist))
			return array();
			
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC pour les droits d'affichage

		return $this->proxy_parent->pmbesNotices_fetchNoticeListArray($noticelist, $recordCharset, false, false);
	}
	
	function fetchNoticeListFull($noticelist, $recordFormat, $recordCharset, $includeLinks) {
		if (!is_array($noticelist))
			return array();

		if (!$noticelist)
			return array();
		
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetchNoticeListFull($noticelist, $recordFormat, $recordCharset, $includeLinks);
	}
	
	function fetchBulletinListFull($bulletinlist, $recordFormat, $recordCharset) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetchBulletinListFull($bulletinlist, $recordFormat, $recordCharset);
	}
	
	function findNoticeBulletinId($noticeId) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_findNoticeBulletinId($noticeId);
	}
	
	function fetchNoticeByExplCb($explCb, $recordFormat, $recordCharset) {
		global $dbh;
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetchNoticeByExplCb(0,$explCb, $recordFormat, $recordCharset, true, true);
	}
	
	function get_author_information_and_notices($author_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesAuthors_get_author_information_and_notices($author_id, 0);
	}

	function get_collection_information_and_notices($collection_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesCollections_get_collection_information_and_notices($collection_id, 0);
	}
	
	function get_subcollection_information_and_notices($subcollection_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesCollections_get_subcollection_information_and_notices($subcollection_id, 0);
	}
	
	function get_publisher_information_and_notices($publisher_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesPublishers_get_publisher_information_and_notices($publisher_id, 0);
	}
	
	function list_thesauri() {
		return $this->proxy_parent->pmbesThesauri_list_thesauri(0);
	}
	
	function fetch_thesaurus_node_full($node_id) {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesThesauri_fetch_node_full($node_id, 0);
	}
	
	function fetch_notices_bulletins($noticelist){
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetch_notices_bulletins($noticelist);	
	}
	
	function fetchNoticesCollstates($serialIds){
		
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC

		return $this->proxy_parent->pmbesNotices_fetchNoticesCollstates($serialIds);
	}
	
	function list_shelves() {
		return $this->proxy_parent->pmbesOPACGeneric_list_shelves(0);
	}
	
	function retrieve_shelf_content( $shelf_id) {
		return $this->proxy_parent->pmbesOPACGeneric_retrieve_shelf_content($shelf_id, 0);
	}	
	
	function fetchNoticeListFullWithBullId($noticelist, $recordFormat, $recordCharset, $includeLinks=true) {
		if (!is_array($noticelist))
			return array();

		if (!$noticelist)
			return array();
			
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetchNoticeListFullWithBullId($noticelist, $recordFormat, $recordCharset, $includeLinks);
	}

	function fetchNoticesBulletinsList($noticelist){
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		
		return $this->proxy_parent->pmbesNotices_fetchNoticesBulletinsList($noticelist);	
	}
	
	function fetchSearchRecordsFull($searchId, $firstRecord, $recordCount,  $recordCharset='iso-8859-1') {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsFull($searchId, $firstRecord, $recordCount, $recordCharset, true, true);
	}

	function fetchSearchRecordsFullSorted($searchId, $firstRecord, $recordCount, $recordCharset='iso-8859-1', $sort_type="") {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsFullSorted($searchId, $firstRecord, $recordCount, $recordCharset, true, true, $sort_type);
	}
	
	function fetchSearchRecordsFullWithBullId($searchId, $firstRecord, $recordCount,  $recordCharset='iso-8859-1') {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsFullWithBullId($searchId, $firstRecord, $recordCount, $recordCharset, true, true);
	}

	function fetchSearchRecordsFullWithBullIdSorted($searchId, $firstRecord, $recordCount, $recordCharset='iso-8859-1', $sort_type="") {
		return $this->proxy_parent->pmbesSearch_fetchSearchRecordsFullWithBullIdSorted($searchId, $firstRecord, $recordCount, $recordCharset, true, true, $sort_type);
	}
	
	function fetchSerialList() {
		$this->proxy_parent->isOPAC=true;//Je sauvegarde dans le parent que je viens de l'OPAC
		return $this->proxy_parent->pmbesNotices_fetchSerialList(0);
	}
	
	function listExternalSources() {
		return $this->proxy_parent->pmbesSearch_listExternalSources(0);
	}
	
}
?>