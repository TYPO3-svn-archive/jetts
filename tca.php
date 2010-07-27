<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA['tx_jetts_mapping'] = array (
    'ctrl' => $TCA['tx_jetts_mapping']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'hidden,title,description,thumbnail,html,llxml,mapping_json,mapping,notes'
    ),
    'feInterface' => $TCA['tx_jetts_mapping']['feInterface'],
    'columns' => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'title' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.title',        
            'config' => array (
                'type' => 'input',    
                'size' => '30',    
                'eval' => 'required',
            )
        ),
        'description' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.description',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',    
                'rows' => '5',
            )
        ),
        'thumbnail' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.thumbnail',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'gif,png,jpeg,jpg',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => 'uploads/tx_jetts',
                'show_thumbs' => 1,    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'html' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.html',        
            'config' => array (
                'type' => 'input',
            )
        ),
        'llxml' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.llxml',        
            'config' => array (
                'type' => 'input',
            )
        ),
		'mapping' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.mapping',        
            'config' => array (
				'type' => 'text',
                'wizards' => array(
                    'JETTS' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 0,
                        'type'          => 'script',
                        'title'         => 'Jetts',
                        'icon'          => 'EXT:jetts/ext_icon.gif',
                        'script'        => 'EXT:jetts/wizard/wizard.php'
                    ),
                ),
        		'cols' => '30',
                'rows' => '5'
            )
        ),
        'mapping_json' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.mapping_json',        
            'config' => array (
				'type' => 'passthrough',
            )
        ),
        'notes' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.notes',        
            'config' => array (
                'type' => 'text',
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => '
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.general,hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, thumbnail, html, notes,
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.mapping,mapping_json, mapping,
    		--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.ll,llxml'
    	)
    ),
    'palettes' => array (
        '1' => array('showitem' => '')
    )
);

?>