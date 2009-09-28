<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_parser.php','_parser','',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi/class.tx_jetts_selector.php','_selector','',1);

?>
