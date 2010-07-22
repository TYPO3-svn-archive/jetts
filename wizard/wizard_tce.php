<?php

require_once(PATH_t3lib.'class.t3lib_tceforms.php');

class user_jetts_wizard {

function main($PA, $fobj)    {
	
	global $TCA;
	
	t3lib_div::loadTCA($table);
	$conf = $TCA['tx_jetts_mapping'];
//	print_r($conf['columns']['mapping_json']);
	
	$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
	$tceforms->initDefaultBEMode();
	$tceforms->backPath = $GLOBALS['BACK_PATH'];
	$tceforms->doSaveFieldName = 'doSave';
	
//	print_r($PA);
//	print_r($fobj);
	$PA['itemFormElValue'] = nl2br(str_replace(' ','&nbsp;',$PA['itemFormElValue']));
	$field = $tceforms->getSingleField_typeNone_render($PA['fieldConf']['config'],$PA['itemFormElValue']);
	$field .= '<input type="hidden" name="data[tx_jetts_mapping]['.$PA['row']['uid'].'][mapping_json]" value="'.htmlspecialchars($PA['row']['mapping_json']).'" />';

	$field = $tceforms->renderWizards(
		array($field),
		$conf['columns']['mapping_json']['config']['wizards'],
		'tx_jetts_mapping',
		$PA['row'],
		'mapping_json',
		$PA,
		'data[tx_jetts_mapping]['.$PA['row']['uid'].'][mapping_json]',
		array()
	);
	//data[tx_jetts_mapping][1][mapping]

	//	print_r($field);
//	print_r($PA['fieldConf']['config']);
//	$PA['fieldConf']['config']['type'] = 'text';
	//$PA['itemFormElValue'] = 'toto';
//	$field = $tceforms->getSingleField_typeText('tx_jetts_mapping','mapping',$PA['row'],$PA);
//	$field = str_replace('name="data[tx_jetts_mapping][1][mapping]"','name="data[tx_jetts_mapping][1][mapping]" readonly="readonly"',$field);
//	function renderWizards($itemKinds,$wizConf,$table,$row,$field,&$PA,$itemName,$specConf,$RTE=0)	{

	return $field;

/*
return '
<div style="
border: 2px dashed #666666;
width : 90%;
margin: 5px 5px 5px 5px;
padding: 5px 5px 5px 5px;">
<h2>My Own Form Field:</h2>
<input
name="'.$PA['itemFormElName'].'"
value="'.htmlspecialchars($PA['itemFormElValue']).'"
onchange="'.htmlspecialchars(implode('',$PA['fieldChangeFunc'])).'"
'.$PA['onFocus'].'
/>
</div>';
*/
}
}
  
?>