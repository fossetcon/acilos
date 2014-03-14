/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the mainView of the manDatabase module
** 
**
** $QT_BEGIN_LICENSE:LGPL$
**
** GNU Lesser General Public License Usage
** Alternatively, this file may be used under the terms of the GNU Lesser
** General Public License version 2.1 as published by the Free Software
** Foundation and appearing in the file LICENSE.LGPL included in the
** packaging of this file.  Please review the following information to
** ensure the GNU Lesser General Public License version 2.1 requirements
** will be met: http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
**
**
** If you have questions regarding the use of this file, please contact
** Omnibond Systems -  www.omnibond.com
**
** $QT_END_LICENSE$
*/
define(['dojo/_base/declare',
		'dojo-mama/views/ModuleScrollableView',
		'dojo/dom-construct',
		'dojo/topic',
		"dojo/_base/lang",
		"dojo/on",
		'dojo/dom-geometry',
		
		'app/util/xhrManager',
		'app/SelectorBar',
		'app/TitleBar',
		
		"dojox/mobile/EdgeToEdgeList",
		"dojox/mobile/Button",
		"dojox/mobile/ListItem",
		"dojox/mobile/ToolBarButton",
		"dojox/mobile/EdgeToEdgeCategory"
], function(
	declare, 
	ModuleScrollableView, 
	domConstruct, 
	topic, 
	lang,
	on,
	domGeom,

	xhrManager, 
	SelectorBar, 
	TitleBar, 
	
	EdgeToEdgeList, 
	Button, 
	ListItem, 
	ToolBarButton, 
	EdgeToEdgeCategory
) {
	return declare([ModuleScrollableView], {		
		style: "overflow:scroll",
		
		postCreate: function(){
			this.buildMainList();
		},
		
		buildMainList: function(){
			this.mainList = new EdgeToEdgeList({ });
			var responseList = new EdgeToEdgeList({ });
			var responseItem = new ListItem({
				style: "border: none"
			});
			responseList.addChild(responseItem);
			
			var item = new ListItem({
				label: "Restart the amazon instance",
				clickable:true,
				onClick: lang.hitch(this, function(responseItem){
					this.restartAmazon.response = responseItem;
					this.router.go("/RestartAmazon");
				}, responseItem)
			});
			this.mainList.addChild(item);
			var item = new ListItem({
				label: "Restore previous saves",
				clickable:true,
				onClick: lang.hitch(this, function(responseItem){
					this.restoreDB.response = responseItem;
					this.router.go("/RestoreDB");
				}, responseItem)
			});
			this.mainList.addChild(item);

			this.mainList.addChild(responseList);
			this.addChild(this.mainList);
		},
		
		activate: function() {
			topic.publish("/dojo-mama/updateSubNav", {back: '/settings', title: "Restore previous saves"} );
			
		}
	})
});