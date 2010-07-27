Ext.override(Ext.layout.FormLayout, {
	renderItem : function(c, position, target){
		if(c && !c.rendered && (c.isFormField || c.fieldLabel) && c.inputType != 'hidden'){
			var args = this.getTemplateArgs(c);
			if(typeof position == 'number'){
				position = target.dom.childNodes[position] || null;
			}
			if(position){
				c.itemCt = this.fieldTpl.insertBefore(position, args, true);
			}else{
				c.itemCt = this.fieldTpl.append(target, args, true);
			}
			c.actionMode = 'itemCt';
			c.render('x-form-el-'+c.id);
			c.container = c.itemCt;
			c.actionMode = 'container';
		}else {
			Ext.layout.FormLayout.superclass.renderItem.apply(this, arguments);
		}
	}
});
Ext.override(Ext.form.Field, {
	getItemCt : function(){
		return this.itemCt;
	}
});

Ext.namespace('TYPO3.Backend.t3Jetts');

TYPO3.Backend.t3Jetts = Ext.extend(Ext.Component, {
	
	constructor: function(config) {
		
		config = Ext.apply({}, config);
		
		TYPO3.Backend.t3Jetts.superclass.constructor.call(this, config);
	},
	
	drawWizard: function() {
		
		var myObj = this;

		//
		var storeFields = [
	       {name: 'title'},
	       {name: 'type'},
	       {name: 'xpath'},
	       {name: 'typoscript'},
	       {name: 'llKey'},
	       {name: 'pid'},
	       {name: 'TSKey'}
	    ];
		
		this.tagList = this.buildElementsGridPanel(storeFields,mapping_json.tags,TYPO3.lang.tagListTitle,'tagList');
		this.attrList = this.buildElementsGridPanel(storeFields,mapping_json.attrs,TYPO3.lang.attrListTitle,'attrList');
		
		var vIFrame = new Ext.ux.ManagedIFrame.Panel({
			id:'htmlTemplateFrame',
			autoScroll: true,
			defaultSrc: htmlTemplate,
			title:'HTML template',
			margins: '35 0 0 0',
			height: 500,
		});

		var footer = new Ext.Panel({
			layout:'column',
			frame:false,
			items: [this.tagList,this.attrList]
		});
		
		var notes = new Ext.Panel({
			layout:'column',
			frame:false,
			items: [{
				title: 'Notes',
				columnWidth: 1,
				html: Ext.get('notes').dom.innerHTML
			}]
		});
		
		var viewport = new Ext.Panel({
			renderTo: 'extjs-iframe',
			items: [vIFrame,footer,notes]
		});
		
		vIFrame.addListener(
			'documentloaded', function(frameEl) { myObj.addIframeListeners(frameEl); }
		);
	},
	
	buildElementsGridPanel: function(fields,data,title,listName) {
		
		var myObj = this;
		
		store = new Ext.data.JsonStore({
			autoSave:false,
	        fields: fields
	    });
		
		store.loadData(data);	
		
		var cm = new Ext.grid.ColumnModel({
	        defaults: {
	            sortable: true
	        },
	        columns: [
	            {
	                header: TYPO3.lang.GridColumnTitle,
	                dataIndex: 'title'
	            }, {
	            	hidden: true,
	                header: TYPO3.lang.GridColumnXpath,
	                dataIndex: 'xpath'
	            }, {
	            	hidden: true,
	                header: TYPO3.lang.GridColumnTyposcript,
	                dataIndex: 'typoscript'
	            }, {
	                header: TYPO3.lang.GridColumnMappingType,
	                dataIndex: 'type',
	                renderer:function(val) { return TYPO3.lang['mappingType'+val]}
	            }
	        ]
	    });
		
		list = new Ext.grid.EditorGridPanel({
	        store: store,
	        cm: cm,
	        stripeRows: true,
	        height: 250,
	        title: title,
	        columnWidth:.5,
	        viewConfig: {
	            autoFill: true,
	            forceFit: true,
	        },
	        tbar: [{
	            text: TYPO3.lang.delete,
	            handler: function(btn, ev) {
	                var index = myObj[listName].getSelectionModel().getSelectedCell();
	                if (!index) {
	                    return false;
	                }
	                var rec = myObj[listName].getStore().getAt(index[0]);
	                myObj[listName].getStore().remove(rec);
	                myObj.storeMapping();
	            },
	        },
	        '-',
	        {
	            text: TYPO3.lang.edit,
	            handler: function(btn, ev) {
	                var index = myObj[listName].getSelectionModel().getSelectedCell();
	                if (!index) {
	                    return false;
	                }
	                var rec = myObj[listName].getStore().getAt(index[0]);
	                myObj.displayMappingForm({store:rec.data,rec:rec});
	            },
	        }],
	    });
		
		return list;
	},

	addIframeListeners: function(frameEl) {
    	var myObj = this;

		frameEl.getDoc().on(
			'click',
			function(e,targetEl){
				e.preventDefault(); // Prevents the browsers default handling of the event
				e.stopPropagation(); // Cancels bubbling of the event
				e.stopEvent() // preventDefault + stopPropagation
			},
			this,
			{delegate:'a'}
		);
		frameEl.getDoc().on(
			'contextmenu',
			function(e,targetEl) {
				e.stopEvent();
//				var orig_xy = frameEl.getXY();
				var xy = e.getXY();
//				xy = [orig_xy[0]+xy[0],orig_xy[1]+xy[1]];
				if(this.menu) this.menu.destroy();
				this.menu = new Ext.menu.Menu({
					id:'nodeContextMenu',
					items: myObj.buildContextMenuItems(targetEl,this)
				});
				this.menu.showAt(xy);
			}
		);
	},
		
	getElementXPath: function(element) {
		if (element && element.id) {
			return '//'+element.localName+'[@id="' + element.id + '"]';
		}else{
			return this.getElementTreeXPath(element);
		}
	},

	getElementTreeXPath: function(element) {
		var isRelative = false;
		var paths = [];

		for (; element && element.nodeType == 1; element = element.parentNode)
		{
			if(element.id) {
				paths.splice(0, 0, this.getElementXPath(element));
				isRelative = true;
				break;
			}else{
	 		   var index = 0;
				for (var sibling = element.previousSibling; sibling; sibling = sibling.previousSibling)
				{
					if (sibling.localName == element.localName)
						++index;
				}

				var tagName = element.localName.toLowerCase();
				var pathIndex = (index ? "[" + (index+1) + "]" : "");
				paths.splice(0, 0, tagName + pathIndex);
			}
		}

		return paths.length ? ((isRelative) ? "" : "/") + paths.join("/") : null;
	},
	
	buildContextMenuItems: function(element,menu) {
		var items = [];
		var path = [];
		var myObj = this;

		for (; element && element.nodeType == 1; element = element.parentNode) {
			path.push(element.nodeName);
			label = path.join(" < ");
			if(element.id) label+="['@id='"+element.id+"]";
			subitems = [];
			if(element.hasChildNodes()) {
				subitems.push({
					text: TYPO3.lang.mapTag+' "'+element.nodeName+'"',
					xpath:myObj.getElementXPath(element),
					nodeName: element.nodeName,
					handler: function() {
						myObj.displayMappingForm({store:{xpath:this.xpath}},'tag',this.nodeName);
					}
				});
				subitems.push('-');
			}
			for (var i in element.attributes) {
				var at = element.attributes[i];
				if(at.nodeType == 2 && at.nodeName != 'id') {
					subitems.push({
						text: TYPO3.lang.mapAttr+' "'+at.nodeName+'"',
						xpath: myObj.getElementXPath(element)+'/@'+at.nodeName,
						nodeName: at.nodeName,
						handler: function() {
							myObj.displayMappingForm({store:{xpath:this.xpath}},'attr',this.nodeName);
						}
					});
				}
			}
			items.push({
				text: label,
				menu: { items: subitems }
			});
			if(element.id) break;
		}
		
		items.push({
			text:TYPO3.lang.cancel
		});
		
		
		return items;
	},
	
	displayMappingForm: function(p,elType,nodeName) {
		var myObj = this;
		
		if(elType == 'tag') {
			var typeStore = [['1',TYPO3.lang.mappingType1], ['2',TYPO3.lang.mappingType2]];
		}else{
			var typeStore = [['2',TYPO3.lang.mappingType2], ['4',TYPO3.lang.mappingType4]];
			if(LLList.length > 0) {
				typeStore.push(['3',TYPO3.lang.mappingType3]);
			}
		}
		var form = new Ext.form.FormPanel({
			editRec: p.rec,
	        baseCls: 'x-plain',
	        labelWidth: 100,
	        width:200,
	        layout: 'form',
	        defaults: {
	            xtype: 'textfield',
	            width:200
	        },
	        items: [
			{
			    fieldLabel: TYPO3.lang.fieldTitle,
			    name: 'title',
			    value: p.store.title
			},{
	            xtype: 'combo',
	            store: typeStore,
	            fieldLabel: TYPO3.lang.fieldMappingType,
	            name: 'type',
			    value: p.store.type,
			    triggerAction: 'all',
	            listeners: {
					'change' : {
		                fn: function(field, newVal, oldVal){
							myObj.toggleFields(form,newVal);
		                }
					},
					'render' : {
		                fn: function(field){
							myObj.toggleFields(form,field.getValue());
		                }
					}
				}
	        },{
	            fieldLabel: TYPO3.lang.fieldColPos,
	            name: 'colPos',
	            hidden:true,
			    value: p.store.colPos
	        },{
	            fieldLabel: TYPO3.lang.fieldTyposcript,
	            name: 'typoscript',
	            hidden:true,
			    value: (typeof(p.store.typoscript) == 'undefined') ? 'lib.' : p.store.typoscript
	        },{
	        	xtype: 'combo',
	        	store: LLList,
	            fieldLabel: TYPO3.lang.fieldLL,
	            name: 'llKey',
	            hidden:true,
			    triggerAction: 'all',
			    value: p.store.llKey,
			    disableKeyFilter: true
	        },{
	            fieldLabel: TYPO3.lang.fieldPid,
	            name: 'pid',
	            hidden:true,
			    value: p.store.pid
	        },{
	            fieldLabel: TYPO3.lang.fieldXpath,
	            name: 'xpath',
			    value: p.store.xpath
	        }]
	    });

	    var w = new Ext.Window({
	        collapsible: true,
	        maximizable: true,
	        width: 400,
	        height: 300,
	        layout: 'fit',
	        plain: true,
	        bodyStyle: 'padding:10px;',
	        buttonAlign: 'center',
	        items: form,
	        buttons: [{
	            text: TYPO3.lang.save,
	            handler: function() {
	        		var storeGrid = (elType == 'tag') ? myObj.tagList : myObj.attrList;
	        		var f = form.getForm();
	        		var TSKey = f.findField('title').getValue().replace(/\s/g,"_");
	    			TSKey = TSKey.replace(/[^\a-z_0-9]/gi, "").toUpperCase();
	        		switch(parseInt(f.findField('type').getValue())) {
	        			case 0:
	        				break;
	        			case 1:
	        				var TS = f.findField('colPos').getValue().toString();
	        				break;
	        			case 2:
	        				var TS = f.findField('typoscript').getValue();
	        				break;
	        			case 3:
	        				var TS = '';
        					TSKey = f.findField('llKey').getValue();
	        				break;
	        			case 4:
	        				var TS = f.findField('pid').getValue().toString();
	        				break;
	        		}
	        		if(parseInt(f.findField('type').getValue()) != 3) TSKey = TSKey+'_'+storeGrid.getStore().data.length;
	        		var values = {
						xpath:f.findField('xpath').getValue(),
						title:f.findField('title').getValue(),
						type:f.findField('type').getValue(),
						typoscript:TS,
						TSKey:TSKey,
						llKey:f.findField('llKey').getValue(),
						pid:f.findField('pid').getValue()
					};
	        		myObj.updateGrid(storeGrid,values,form.editRec);
	        		myObj.storeMapping();
	                //
	                w.close();
	        	}
	        },{
	            text: TYPO3.lang.cancel,
	            handler: function() {
	            	w.close()
	            }
	        }]
	    });
	    w.show();
	},
	
	toggleFields: function(form,newVal) {
		var colPosField = form.getForm().findField('colPos');
		var TSField = form.getForm().findField('typoscript');
		var LLField = form.getForm().findField('llKey');
		var PidField = form.getForm().findField('pid');
		colPosField.hide();
		TSField.hide();
		LLField.hide();
		PidField.hide();
		switch(parseInt(newVal)) {
			case 1:
				colPosField.show();
				colPosField.focus();
				break;
			case 2:
				TSField.show();
				TSField.focus();
				break;
			case 3:
				LLField.show();
				LLField.focus();
				break;
			case 4:
				PidField.show();
				PidField.focus();
				break;
		}
    },
	
	updateGrid: function(grid,values,rec) {
		var store = grid.getStore();
		if(typeof(rec) == 'undefined') {
			var rt = store.recordType;
			var r = new rt(values);
			grid.stopEditing();
			store.add(r);
            grid.startEditing(0, 0);
		}else{
			rec.data = values;
			rec.commit();
		}
	},

	storeMapping: function() {
		var JSON = {tags:[],attrs:[]};
		var storeTags = this.tagList.getStore();
		var storeAttrs = this.attrList.getStore();
		storeTags.each(function(){
			JSON.tags.push(this.data);
		});
		storeAttrs.each(function(){
			JSON.attrs.push(this.data);
		});
		var field = Ext.select("textarea[id^=tceforms-textarea]");
		field.update(Ext.encode(JSON));
	}

});

Ext.onReady(function() {
	t3Jetts = new TYPO3.Backend.t3Jetts({});
	t3Jetts.drawWizard();
});