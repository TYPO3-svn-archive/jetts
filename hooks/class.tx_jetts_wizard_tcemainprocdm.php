<?php

class tx_jetts_wizard_tcemainprocdm {

	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray) {
		if(strpos($id,'NEW') === false) {
			if($table == 'tx_jetts_mapping') {
				$rec = t3lib_BEfunc::getRecord($table, $id);
				$mapping = json_decode($rec['mapping_json']);
				if(!is_null($mapping)) {
					
					$TS = '';
					$TS .= 'template {'."\n";
					$TS .= '  content = FILE'."\n";
					$TS .= '  content.file = ' . $rec['html'] . "\n\n";
					if(locallangFile != '/') $TS .= '    locallangFile = '. $rec['llxml'] . "\n\n";
					$TS .= '  subparts {'."\n";
					$TS .= '    DOCUMENT_BODY = //body'."\n\n";
					
					foreach($mapping->tags as $tag) {
						$TS .= '    // '.$tag->title."\n";
						$TS .= '    '.$tag->TSKey.' = '.$tag->xpath."\n";
					}
					reset($mapping->tags);
					$TS .= '  }'."\n\n";
					$TS .= '  marks {'."\n";
					
					foreach($mapping->attrs as $attr) {
						$TS .= '    // '.$attr->title."\n";
						$TS .= '    '.$attr->TSKey.' = '.$attr->xpath."\n";
					}
					reset($mapping->attrs);
					$TS .= '  }'."\n";
					$TS .= '}'."\n";
					$TS .= 'workOnSubpart = DOCUMENT_BODY'."\n\n";
					$TS .= 'subparts {'."\n";
					
					foreach($mapping->tags as $tag) {
						if(intval($tag->type) == 1) {
							$TS .= '  '.$tag->TSKey.' < lib.jetts.content.get'."\n";
							$TS .= '  '.$tag->TSKey.'.select.where = colPos='.$tag->typoscript."\n";
						}else{
							$TS .= '  '.$tag->TSKey.' < '.$tag->typoscript."\n";
						}
					}
					$TS .= '}'."\n\n";
					$TS .= 'marks {'."\n";
					
					foreach($mapping->attrs as $attr) {
						if(intval($attr->type) == 4) {
							$TS .= '  '.$attr->TSKey.' = TEXT'."\n";
							$TS .= '  '.$attr->TSKey.'.stdWrap.typolink.parameter = '.$attr->pid."\n";
							$TS .= '  '.$attr->TSKey.'.stdWrap.typolink.returnLast = url'."\n";
						}elseif($attr->typoscript != '') {
							$TS .= '  '.$attr->TSKey.' < '.$attr->typoscript."\n";
						}
					}
					$TS .= '}'."\n";
					
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						'tx_jetts_mapping',
						'uid='.$id,
						array('mapping' => $TS)
					);
				}
			}		
		}
	}
}

?>