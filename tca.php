<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA['tx_jetts_mapping'] = array (
    'ctrl' => $TCA['tx_jetts_mapping']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'hidden,title,description,thumbnail,html,llxml,mapping_json,mapping,notes,work_on_subpart,ts_override,show_columns'
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
        		'size' => '48',
        		'eval' => 'required,trim',
        		'wizards' => array(
        			'_PADDING' => '2',
        			'link_html' => array(
        				'type' => 'popup',
        				'title' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.html',
        				'icon' => 'link_popup.gif',
        				'script' => 'browse_links.php?mode=wizard&amp;act=file',
        				'params' => array(
        					'blindLinkOptions' => 'page,url,mail,spec,folder',
        					'allowedExtensions' => 'htm,html,tmpl,tpl'
        				),
        				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
        			)
        		)
            )
        ),
        'llxml' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.llxml',        
            'config' => array (
                'type' => 'input',
        		'size' => '48',
        		'eval' => 'trim',
        		'wizards' => array(
        			'_PADDING' => '2',
        			'link_xml' => array(
        				'type' => 'popup',
        				'title' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.llxml',
        				'icon' => 'link_popup.gif',
        				'script' => 'browse_links.php?mode=wizard&amp;act=file',
        				'params' => array(
        					'blindLinkOptions' => 'page,url,mail,spec',
        					'allowedExtensions' => 'xml',
        					'buttons.link.targetSelector.disabled' => 1,
        					'buttons.link.popupSelector.disabled' => 1
        				),
        				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
        			)
        		)
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
				'cols' => '50',
                'rows' => '5'
        	)
        ),
        'show_columns' => array (        
	        'exclude' => 0,        
	        'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.show_columns',        
	        'config' => array (
	            'type' => 'select',
	            'itemsProcFunc' => 'tx_jetts_wizard_tce->getColumns',
	            'size' => 10,  
	            'minitems' => 0,
	            'maxitems' => 99,
	        )
	    ),
        'work_on_subpart' => array(
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.work_on_subpart',        
	        'config' => array (
	            'type' => 'select',
	            'itemsProcFunc' => 'tx_jetts_wizard_tce->getSubparts',
	            'items' => array(
	            	array('DOCUMENT_BODY', 'DOCUMENT_BODY')
	            ),
	            'size' => 1,  
	            'minitems' => 0,
	            'maxitems' => 1,
	        )
        ),
        'ts_override' => array(
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.ts_override',        
            'config' => array (
                'type' => 'text',
        		'default' => '#
# Example to override generated Typoscript
#
# relPathPrefix.IMG = http://static.mysite.com/fileadmin/default/templates/
#
',
				'cols' => '50',
                'rows' => '5'
            )
        )
    ),
    'types' => array (
        '0' => array('showitem' => '
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.general, hidden, title;;1;;, thumbnail, html, llxml,
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.mapping, mapping, notes,
    		--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.advanced,show_columns,work_on_subpart,ts_override'
    	)
    ),
    'palettes' => array (
        '1' => array('showitem' => 'description')
    )
);

?>