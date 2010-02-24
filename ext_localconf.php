<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_parser.php','_parser','',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_selector.php','_selector','',1);

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:jetts/hooks/class.tx_jetts_tcemainprocdm.php:tx_jetts_tcemainprocdm';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:jetts/hooks/class.tx_jetts_tcemainprocdm.php:tx_jetts_tcemainprocdm';

?>
