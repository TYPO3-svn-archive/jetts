<?php

class tx_jetts_tcemainprocdm {
	
	/*
plugin.tx_jetts.acl {
	byContentType {
		noEdit = bullets
		noHide = textpic
		noDelete = image
		allowed = text
	}
	byUid {
		noEdit = 2
		noHide = 21
		noDelete = 8
	}
	byColumn {
		0 {
			noEdit = image
			noHide = text
			noDelete = bullets,text
			allowed = text,textpic,image
		}
	}
}
	 */
	function processCmdmap_preProcess(&$command, $table, $id, $value, &$parent) {

		//if($GLOBALS['BE_USER']->user['admin']) return;
		
		$contentTable = $GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable'];
		
		if($table == $contentTable && $command == 'delete') {
			$rec = t3lib_BEfunc::getRecord($table, $id);
			$perms = $this->getPermissions($rec);
			$LL = $GLOBALS['LANG']->includeLLfile('EXT:jetts/locallang_db.xml',0);
			if(
				t3lib_div::inList($perms['byContentType']['noEdit'],$rec['CType'])
				|| t3lib_div::inList($perms['byColumn'][$rec['colPos']]['noEdit'],$rec['CType'])
				|| t3lib_div::inList($perms['byUid']['noEdit'],$id)
				|| t3lib_div::inList($perms['byContentType']['noHide'],$rec['CType'])
				|| t3lib_div::inList($perms['byColumn'][$rec['colPos']]['noHide'],$rec['CType'])
				|| t3lib_div::inList($perms['byUid']['noHide'],$id)
				|| t3lib_div::inList($perms['byContentType']['noDelete'],$rec['CType'])
				|| t3lib_div::inList($perms['byColumn'][$rec['colPos']]['noDelete'],$rec['CType'])
				|| t3lib_div::inList($perms['byUid']['noDelete'],$id)					
			) {
				$command = '';
				$parent->log($table,$id,3,0,1,$GLOBALS['LANG']->getLLL('jetts.hooks.error.noDelete',$LL),1,array());
			}
		}
	}
	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$parent) {
		
		//if($GLOBALS['BE_USER']->user['admin']) return;

		$contentTable = $GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable'];
		
		if($table == $contentTable) {
			$LL = $GLOBALS['LANG']->includeLLfile('EXT:jetts/locallang_db.xml',0);
			switch($status) {
				case 'new':
					$rec = $fieldArray;
					$rec['uid'] = $id;
					$perms = $this->getPermissions($rec);
					$error = false;
					// check for allowed new content elements (globally)
					if($perms['byContentType']['allowed']) {
						if(!t3lib_div::inList($perms['byContentType']['allowed'],$fieldArray['CType'])) {
							$error = true;
						}
					}
					// check for allowed new content elements (by column)
					if($perms['byColumn'][$fieldArray['colPos']]['allowed']) {
						if(!t3lib_div::inList($perms['byColumn'][$fieldArray['colPos']]['allowed'],$fieldArray['CType'])) {
							$error = true;
						}else{
							$error = false;
						}
					}
					if($error) {
						$fieldArray = array();
						$parent->log($table,$id,3,0,1,$GLOBALS['LANG']->getLLL('jetts.hooks.error.allowed',$LL),1,array());
					}
					break;
				case 'update':
					$rec = t3lib_BEfunc::getRecord($table, $id);
					$perms = $this->getPermissions($rec);
					// check if element can be edited
					if(
						t3lib_div::inList($perms['byContentType']['noEdit'],$rec['CType'])
						|| t3lib_div::inList($perms['byColumn'][$rec['colPos']]['noEdit'],$rec['CType'])
						|| t3lib_div::inList($perms['byUid']['noEdit'],$id)
					) {
						$fieldArray = array();
						$parent->log($table,$id,3,0,1,$GLOBALS['LANG']->getLLL('jetts.hooks.error.noEdit',$LL),1,array());
					}
					// check if element can be hidden (if hidden=1)
					if(intval($fieldArray['hidden']) == 1) {
						if(
							t3lib_div::inList($perms['byContentType']['noHide'],$rec['CType'])
							|| t3lib_div::inList($perms['byColumn'][$rec['colPos']]['noHide'],$rec['CType'])
							|| t3lib_div::inList($perms['byUid']['noHide'],$id)
						){
							$fieldArray = array();
							$parent->log($table,$id,3,0,1,$GLOBALS['LANG']->getLLL('jetts.hooks.error.noHide',$LL),1,array());
						}
					}
					break;
			}
		}
	}
	
	function getPermissions($row) {
		$tscPID = t3lib_BEfunc::getTSconfig_pidValue($GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable'],$row['uid'],$row['pid']);
		$TS = $GLOBALS['BE_USER']->getTSConfig('plugin.tx_jetts.acl',t3lib_BEfunc::getPagesTSconfig($tscPID));
		$TS = $TS['properties'];
		$TS = t3lib_div::removeDotsFromTS($TS);
		array_walk_recursive($TS,array($this, 'expandLists'));
		return $TS;
	}
	
	function expandLists(&$item, $key) {
		$item = t3lib_div::expandList($item);
	}
}

?>