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



Ext.onReady(function(){

	var myObj = this;	
	var fm = Ext.form;
	
	var storeFields = [
       {name: 'title'},
       {name: 'type'},
       {name: 'xpath'},
       {name: 'typoscript'},
       {name: 'llKey'},
       {name: 'pid'},
       {name: 'TSKey'}
    ];
	var storeTags = new Ext.data.JsonStore({
		autoSave:false,
        fields: storeFields
    });
	var storeAttrs = new Ext.data.JsonStore({
		autoSave:false,
        fields: storeFields
    });
	
	storeTags.loadData(mapping_json.tags);	
	storeAttrs.loadData(mapping_json.attrs);
	
	var vIFrame = new Ext.ux.ManagedIFrame.Panel({
		id:'htmlTemplateFrame',
		autoScroll: true,
		defaultSrc: htmlTemplate,
		title:'HTML template',
		margins: '35 0 0 0',
		height:400,
	});
	
	var cmTag = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [
            {
                header: 'Title',
                dataIndex: 'title'
            }, {
            	hidden: true,
                header: 'Xpath',
                dataIndex: 'xpath'
            }, {
            	hidden: true,
                header: 'Typoscript',
                dataIndex: 'typoscript'
            }, {
                header: 'Mapped to',
                dataIndex: 'type',
                renderer:typeValues
            }
        ]
    });

	var cmAttr = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [
            {
                header: 'Title',
                dataIndex: 'title'
            }, {
            	hidden: true,
                header: 'Xpath',
                dataIndex: 'xpath'
            }, {
            	hidden: true,
                header: 'Typoscript',
                dataIndex: 'typoscript'
            }, {
                header: 'Mapped to',
                dataIndex: 'type',
                renderer:typeValues
            }
        ]
    });


	function typeValues(val){
		switch(parseInt(val)) {
			case 1:
				return 'Content Area';
				break;
			case 2:
				return 'Typoscript Object';
				break;
			case 3:
				return 'language label';
				break;
			case 4:
				return 'Link to page';
				break;
		}
    }

	var tagList = new Ext.grid.EditorGridPanel({
        store: storeTags,
        cm: cmTag,
        stripeRows: true,
        height: 200,
        title: 'Mapped tags',
        columnWidth:.5,
        viewConfig: {
            autoFill: true,
            forceFit: true,
        },
        tbar: [{
            text: 'Delete',
            handler: function(btn, ev) {
                var index = tagList.getSelectionModel().getSelectedCell();
                if (!index) {
                    return false;
                }
                var rec = storeTags.getAt(index[0]);
                storeTags.remove(rec);
                myObj.encodeTyposcript();
            },
            scope: this
        },{
            text: 'Edit',
            handler: function(btn, ev) {
                var index = tagList.getSelectionModel().getSelectedCell();
                if (!index) {
                    return false;
                }
                var rec = storeTags.getAt(index[0]);
                myObj.displayMappingForm({store:rec.data,rec:rec});
            },
            scope: this
        }],

    });
	
	var attrList = new Ext.grid.EditorGridPanel({
        store: storeAttrs,
        cm: cmAttr,
        stripeRows: true,
        height: 200,
        title: 'Mapped attributes',
        columnWidth:.5,
        viewConfig: {
            autoFill: true,
            forceFit: true,
        },
        tbar: [{
            text: 'Delete',
            handler: function(btn, ev) {
                var index = attrList.getSelectionModel().getSelectedCell();
                if (!index) {
                    return false;
                }
                var rec = storeAttrs.getAt(index[0]);
                storeAttrs.remove(rec);
                myObj.encodeTyposcript();
            },
            scope: this
        },{
            text: 'Edit',
            handler: function(btn, ev) {
                var index = attrList.getSelectionModel().getSelectedCell();
                if (!index) {
                    return false;
                }
                var rec = storeAttrs.getAt(index[0]);
                myObj.displayMappingForm({store:rec.data,rec:rec});
            },
            scope: this
        }],
    });

	var footer = new Ext.Panel({
		layout:'column',
		frame:false,
		items: [tagList,attrList]
	});
	
	var viewport = new Ext.Panel({
		renderTo: 'extjs-iframe',
		items: [vIFrame,footer]
	});
	
	vIFrame.addListener(
		'domready', function(frameEl) { myObj.addIframeListeners(frameEl); }
	);

	addIframeListeners = function(frameEl) {
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
			function(e,targetEl){
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
			});
	}
	
	getElementXPath = function(element)
	{
		if (element && element.id)
			return '//'+element.localName+'[@id="' + element.id + '"]';
		else
			return this.getElementTreeXPath(element);
	}

	getElementTreeXPath = function(element)
	{
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
	}
	
	buildContextMenuItems = function(element,menu) {
		var items = [];
		var path = [];
		for (; element && element.nodeType == 1; element = element.parentNode) {
			path.push(element.nodeName);
			label = path.join(" < ");
			if(element.id) label+="['@id='"+element.id+"]";
			subitems = [];
			subitems.push({
				text: 'Map tag',
				xpath:myObj.getElementXPath(element),
				nodeName: element.nodeName,
				handler: function() {
					myObj.displayMappingForm({store:{xpath:this.xpath}},'tag',this.nodeName);
				}
			});
			subitems.push('-');
			for (var i in element.attributes) {
				var at = element.attributes[i];
				if(at.nodeType == 2 && at.nodeName != 'id') {
					subitems.push({
						text: 'Map attribute "'+at.nodeName+'"',
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
			text:'cancel'
		});
		
		
		return items;
	}
	
	displayMappingForm = function(p,elType,nodeName) {
		if(elType == 'tag') {
			var typeStore = [['1','Content area'], ['2','typoscript object']];
		}else{
			var typeStore = [['2','typoscript object'], ['4','Link to page']];
			if(LLList.length > 0) {
				typeStore.push(['3','Language label']);
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
			    fieldLabel: 'title',
			    name: 'title',
			    value: p.store.title
			},{
	            xtype: 'combo',
	            store: typeStore,
	            fieldLabel: 'Map To',
	            name: 'type',
			    value: p.store.type,
			    triggerAction: 'all',
	            listeners: {
					'change' : {
						scope:this,
		                fn: function(field, newVal, oldVal){
							myObj.toggleFields(form,newVal);
		                }
					},
					'render' : {
						scope:this,
		                fn: function(field){
							myObj.toggleFields(form,field.getValue());
		                }
					}
				}
	        },{
	            fieldLabel: 'column id',
	            name: 'colPos',
	            hidden:true,
			    value: p.store.colPos
	        },{
	            fieldLabel: 'Typoscript object',
	            name: 'typoscript',
	            hidden:true,
			    value: (typeof(p.store.typoscript) == 'undefined') ? 'lib.' : p.store.typoscript
	        },{
	        	xtype: 'combo',
	        	store: LLList,
	            fieldLabel: 'Key in LLXML file',
	            name: 'llKey',
	            hidden:true,
			    triggerAction: 'all',
			    value: p.store.llKey,
			    disableKeyFilter: true
	        },{
	            fieldLabel: 'Page id',
	            name: 'pid',
	            hidden:true,
			    value: p.store.pid
	        },{
	            fieldLabel: 'xpath address',
	            name: 'xpath',
			    value: p.store.xpath
	        }]
	    });
		
		toggleFields = function(form,newVal) {
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
        }

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
	            text: 'Save',
	            handler: function() {
	        		var storeGrid = (elType == 'tag') ? tagList : attrList;
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
	        		if(parseInt(f.findField('type').getValue()) != 3) TSKey += '_'+storeGrid.store.totalLength.toString();
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
	        		myObj.encodeTyposcript();
	                //
	                w.close();
	        	}
	        },{
	            text: 'Cancel',
	            handler: function() {
	            	w.close()
	            }
	        }]
	    });
	    w.show();
	}
	
	updateGrid = function(grid,values,rec) {
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
	}

	encodeTyposcript = function() {
		var JSON = {tags:[],attrs:[]};
		storeTags.each(function(){
			JSON.tags.push(this.data);
		});
		storeAttrs.each(function(){
			JSON.attrs.push(this.data);
		});
		var field = Ext.select("textarea").elements[0];
		field.innerHTML = Ext.encode(JSON);
	}

});