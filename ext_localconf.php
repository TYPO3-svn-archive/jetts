<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);

// instantiate the parser plugin
t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_parser.php','_parser','',1);

// instantiate the Typoscript template selector if set in configuration
if($_EXTCONF['enableTyposcriptSelector'] || (t3lib_div::int_from_ver(TYPO3_version) < 4003000)) {
	t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_selector.php','_selector','',1);
}

// instantiate the mapping wizard only if version of TYPO3 is above 4.3 (we need extJs)
if(t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
	t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_pi.php','_pi','',1);
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:jetts/hooks/class.tx_jetts_wizard_tcemainprocdm.php:tx_jetts_wizard_tcemainprocdm';
}

// instantiate hooks for ACL system
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:jetts/hooks/class.tx_jetts_tcemainprocdm.php:tx_jetts_tcemainprocdm';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:jetts/hooks/class.tx_jetts_tcemainprocdm.php:tx_jetts_tcemainprocdm';

?>
