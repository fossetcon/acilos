/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the mainView for the custom feeds module
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
		
		'app/util/xhrManager',
		'app/SelectorBar',
		'app/SearchScroller',
		
		"dojox/mobile/ScrollableView",
		"app/SelRoundRectList",
		"app/SelEdgeToEdgeList",
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
	
	xhrManager, 
	SelectorBar, 
	SearchScroller,
	
	ScrollableView,
	RoundRectList, 
	EdgeToEdgeList, 
	Button, 
	ListItem, 
	ToolBarButton,
	EdgeToEdgeCategory
) {
	return declare([ModuleScrollableView], {

		deactivate: function(){
			if(this.selectorItem){
				this.selectorItem.destroyRecursive();
				this.selectorItem = null;
			}

			if(this.mainList){
				this.mainList.destroyRecursive();
				this.mainList = null;	
			}
		},	
		
		buildMainList: function(obj){
			console.log("buildMainList: ", obj);
			
			this.mainList = new EdgeToEdgeList({
				style: "margin-top: 40px;"
			});

			if(!this.selectorItem){
				this.scrollButton = new Button({
					"name": "scrollButton",
					"right": "true",
					onClick: lang.hitch(this, function(){
						var scroller = lang.hitch(this, function(){
							if(this.domNode.scrollTop <= 0){
								this.domNode.scrollTop = 0;
							}else{
								this.domNode.scrollTop = this.domNode.scrollTop - (this.domNode.scrollTop*.08);
								if(this.domNode.scrollTop != 0){
									setTimeout(scroller, 20);
								}
							}
						});
						setTimeout(scroller, 20);
					})
				});

				this.newFeedButton = new Button({
					"name": "newFeedButton",
					"left": "true",
					onClick: lang.hitch(this, function(){
						//this.router.go("/CreateFeedView");
						this.router.go("/NewCreateFeedView");
					})
				});

				this.editFeedButton = new Button({
					"name": "editFeedButton",
					"left": "true",
					onClick: lang.hitch(this, function(){
						this.router.go("/NewEditFeed");
					})
				});

				this.deleteFeedButton = new Button({
					"name": "deleteFeedButton",
					"left": "true",
					onClick: lang.hitch(this, function(){
						this.router.go("/NewDeleteFeed");
					})
				});

				this.localButton = new Button({
					"name": "localButton",
					"right": "true",
					onClick: lang.hitch(this, function(){
						this.router.go("/");
					})
				});

				this.selectorItem = new SelectorBar({
					buttons: [this.editFeedButton, this.newFeedButton, this.deleteFeedButton, this.scrollButton, this.localButton]
				})
				this.selectorItem.placeAt(this.domNode.parentNode);
			}

			if(obj == null || obj.length == 0){
				var item = new ListItem({
					label: "No feeds have been saved yet"
				});	
				this.mainList.addChild(item);	
			}else{
				for(var key in obj){
					var item = new ListItem({
						label: key,
						clickable: true,
						onClick: lang.hitch(this, function(obj, key){
							this.router.go("/NewFeedView/" + key + "/" + obj[key]['terms']);
						}, obj, key)
					});

					this.mainList.addChild(item);
				}
			}
			this.addChild(this.mainList);
		},
		
		activate: function(e){
			topic.publish("/dojo-mama/updateSubNav", {back: '/', title: "Your public feeds"} );

			if(this.mainList){
				this.mainList.destroyRecursive();
				this.getPublicQueryObject().then(lang.hitch(this, this.buildMainList));
			}else{
				this.getPublicQueryObject().then(lang.hitch(this, this.buildMainList));
			}
		}
	})
});