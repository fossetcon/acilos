/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the twitterFeedItem for many modules
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
define([
		"dojo/_base/declare"
		,"dojo/dom"
		,"dojo/dom-class"
		,"dojo/dom-construct"
		,"dojo/_base/window"
		,"dojo/dom-style"
		,"dojo/dom-attr"
		,"dojo/_base/lang"
		,"dojo/_base/Deferred"
		,"dojo/DeferredList"
		,"dojo/on"
		
		,"dijit/registry"
		,"dijit/Dialog"
		,"app/dropDown"

		,"dojox/mobile/RoundRectList"
		,"dojox/mobile/ListItem"
		,"dojox/mobile/ContentPane"
		,"dojox/mobile/Accordion"
		,"dojox/mobile/ToolBarButton"

		,"app/DeferredExecuterMixin"
		
		,"app/mainFeed/FeedItemBase"
		,"app/mainFeed/FeedAccordion"
		,"app/mainFeed/CommentPane"
		
		,"dojo/ready"
		,'app/util/xhrManager'

	], function(
		declare
		,dom
		,domClass
		,domConstruct
		,domWindow
		,domStyle
		,domAttr
		,lang
		,Deferred
		,DeferredList
		,on
		
		,registry
		,Dialog
		,dropDown
		
		,RoundRectList
		,ListItem
		,Pane
		,Accordion
		,ToolBarButton
		
		,DeferredExecuterMixin
		
		,DataObjItemBase
		,DataObjAccordion
		,DataObjPane
		
		,ready

		,xhrManager
	){
		return declare("twitterFeedItem",[ListItem], {
			variableHeight: true,
			"class": "feedItemListItemClass",
			
			sendTwitterFav: function(id){
				var params = {id: id};
				return xhrManager.send('POST', 'rest/v1.0/Post/sendTwitterFav', params);
			},
			
			sendTwitRetweet: function(tweetID){
				var params = {tweetID: tweetID};
				return xhrManager.send('POST', 'rest/v1.0/Post/sendTwitRetweet', params);
			},
			
			sendTwitterUnFav: function(id){
				var params = {id: id};
				return xhrManager.send('POST', 'rest/v1.0/Post/sendTwitterUnFav', params);
			},
			
			setIsLiked: function(id, liked){
				var params = {id: id, liked: liked};
				return xhrManager.send('POST', 'rest/v1.0/Favorites/setIsLiked', params);
			},
			
			setIsCommented: function(id){
				var params = {id: id};
				return xhrManager.send('POST', 'rest/v1.0/Favorites/setIsCommented', params);
			},
			
			postCreate: function(){
				this.buildView();
			},
			
			getObjects: function(){
				return app.send('getObjects', 'php/' + this.serviceURL,{})
					.then(lang.hitch(this,this.handleGetObjects));
			},
			
			handleGetObjects: function(obj){
				console.log("handleGetObjects",arguments);
				this.objects = obj;
			},
			
			buildView: function(color){				
				this.paneLeft = new Pane({
					"class": "paneLeftClass"
				});
				this.paneRight = new Pane({
					"class": "paneRightClass"
				});
				this.roundLeft = new RoundRectList({
					"class": "roundRectLeftClass"
				});
				this.roundRight = new RoundRectList({
					"class": "roundRectRightClass"
				});
				this.paneLeft.addChild(this.roundLeft);
				this.paneRight.addChild(this.roundRight);
				this.addChild(this.paneLeft);
				this.addChild(this.paneRight);

				var obj = this.data.hits.hits[this.counter]._source;

				for(var g = 0; g < this.authObj['twitter'].length; g++){
					if(this.authObj['twitter'][g]['user'] == obj.mainAccountID){
						this.domNode.style.borderLeft = "5px solid " + this.authObj['twitter'][g]['color'];
						this.domNode.style.marginBottom = "10px";
						break;
					}else{
						this.domNode.style.borderLeft = "5px solid " + obj.mainAccountColor;
						this.domNode.style.marginBottom = "10px";
					}
				}
				
				if(!obj.content){
					return;
				}
				var type = obj.content.objectType;
				var action = '';
				switch(type){
					case "link":
						action = " posted a link";
					break;
					case "status":
						action = " updated their status";
					break;
					case "photo":
						action = " added a new photo";
					break;
					default:
						//herpderp
					break;
				}
				//LeftPane/RoundRect
				this.picItem = new ListItem({
					variableHeight: true,
					"class": "picItemClass"
				});
				if(obj.actor.searchable != null){
					this.userPic = domConstruct.create("div", {innerHTML: '<img src="'+obj.actor.image+'" width="50px" height="50px" />', "class": "feedPicDivItemClass"});
				}else{
					this.userPic = domConstruct.create("div", {innerHTML: '<img src="app/resources/img/blankPerson.jpg" width="50px" height="50px" />', "class": "feedPicDivItemClass"});
				}
				this.picItem.domNode.appendChild(this.userPic);
				
				this.starItem = new ListItem({
					variableHeight: true,
					"class": "starItemClass"
				})
				this.star = new domConstruct.create("div", {
					"class": "starButtonClass",
					checked: false
				})
				this.starClient = new domConstruct.create("div", {
					"class": "starClientButtonClass",
					checked: false
				})
				if(obj.starred == "true"){
					this.star = new domConstruct.create("div", {
						"class": "starButtonClassChecked",
						checked: true
					})
				}

				if(this.starClientObj){
					for(var x = 0; x < this.starClientObj.length; x++){
						if(obj.actor.id == this.starClientObj[x]){
							this.starClient = new domConstruct.create("div", {
								"class": "starClientButtonClassChecked",
								checked: true
							})
							break;
						}
					}
				}
				
				this.star.onclick = lang.hitch(this, function(obj){
					if(this.star.checked == true){
						var response = this.setStarred("false", obj.id);
						this.star.checked = false;
						domClass.remove(this.star, "starButtonClassChecked");
						domClass.add(this.star, "starButtonClass");
					}else if(this.star.checked == false){
						var response = this.setStarred("true", obj.id);
						this.star.checked = true;
						domClass.remove(this.star, "starButtonClass");
						domClass.add(this.star, "starButtonClassChecked");
					}else{
						console.log("we shouldn't have gotten here");
					}
				}, obj)
				this.starClient.onclick = lang.hitch(this, function(obj){
					if(this.starClient.checked == true){
						var response = this.setStarredClient("false", obj.service+"-----"+obj.actor.id);
						this.starClient.checked = false;
						domClass.remove(this.starClient, "starClientButtonClassChecked");
						domClass.add(this.starClient, "starClientButtonClass");
					}else if(this.starClient.checked == false){
						var response = this.setStarredClient("true", obj.service+"-----"+obj.actor.id);
						this.starClient.checked = true;
						domClass.remove(this.starClient, "starClientButtonClass");
						domClass.add(this.starClient, "starClientButtonClassChecked");
					}else{
						console.log("we shouldn't have gotten here");
					}
				}, obj)
				
				this.starItem.domNode.appendChild(this.star);
				this.starItem.domNode.appendChild(this.starClient);
				this.roundLeft.addChild(this.picItem);
				this.roundLeft.addChild(this.starItem);
			//LeftPane/RoundRect
			
			//RightPane/RoundRect
				this.servPub = domConstruct.create("div", {innerHTML: '<span><a href="https://twitter.com/' + obj.actor.displayName +'" target="_blank">'+obj.actor.displayName+'</a></span>' + " " + action + " via " + '<span><a href="' + obj.postLink +'" target="_blank">'+obj.service+'</a></span>', "class": "feedServiceDivItemClass"});
				this.dataPub = domConstruct.create("div", {innerHTML: this.getDate((obj.published).toString()), "class": "feedDateDivItemClass"});
								
				this.dateServItem = new ListItem({
					variableHeight: true,
					"class": "feedPicDateItemClass"
				})
				
				this.dateServItem.domNode.appendChild(this.servPub);
				this.dateServItem.domNode.appendChild(this.dataPub);
				
				this.roundRight.addChild(this.dateServItem);

				//PIC ITEM-------------------------------
				this.twitterPic = new ListItem({
					variableHeight: true,
					"class": "feedPicDateItemClass"
				});
				if(obj.content && (obj.content != null)){
					if(obj.content.mediaUrl && (obj.content.mediaUrl != null) && (obj.content.mediaUrl != "")){		
						var div = domConstruct.create("div", {innerHTML: '<img src="'+obj.content.mediaUrl+'"style="max-width:90%;max-height:90%;"></img>'});

						div.onclick = lang.hitch(this, function(){
							var dialog = new Dialog({
								title: "Click to close ->",
								"class": "blackBackDijitDialog",
								onHide: lang.hitch(this, function(){
									if(blackoutDiv){
										document.body.removeChild(blackoutDiv);
									}
								})
							});	

							var dialogDiv = domConstruct.create("div", {innerHTML: '<img src="'+obj.content.mediaUrl+'"style=""></img>'});

							var blackoutDiv = domConstruct.create("div", {"class": "blackoutDiv"});

							dialog.set("content", dialogDiv);
							dialog.show();

							document.body.appendChild(blackoutDiv);
						});

						this.twitterPic.domNode.appendChild(div);
					}
				}
				this.roundRight.addChild(this.twitterPic);

				//TEXT ITEM FOUR-------------------------
				if(obj.content.text.text != null){
					var stringArr = obj.content.text.text.split(" ");
					/*
					var urlArr = [];
					
					for(var u = 0; u < stringArr.length; u++){
						if(this.isURL(stringArr[u])){
							urlArr.push(stringArr[u]);
						}
					}
					*/
					string = this.parseSpecialChars(obj.content.text.text);
					this.textContent = new ListItem({
						variableHeight: true,
						label: string,
						"class": "feedTextContentItemClass"
					});
					this.roundRight.addChild(this.textContent);

					this.commentHolder = new ListItem({
						variableHeight: true,
						"class": "feedCommentItemClass"
					})
					
					var x = this.data.hits.hits[this.counter]._source.content;
					var id = this.data.hits.hits[this.counter]._source.content.id;
					var source = this.data.hits.hits[this.counter]._source;
					
					var replyDiv = domConstruct.create("div", {innerHTML: "Reply", "class": "twitterOrangeDiv", style: "margin-right: 5px"});
					if(x.retweet == null){
						this.retweet = 0;
					}else{
						this.retweet = x.retweet;
					}
					if(source.isCommented == "true"){
						var retweetDiv = domConstruct.create("div", {innerHTML: "Retweeted(" + this.retweet + ")", "class": "twitterBlueDiv", style: "margin-right: 5px"});
					}else{
						var retweetDiv = domConstruct.create("div", {innerHTML: "Retweet(" + this.retweet + ")", "class": "twitterOrangeDiv", style: "margin-right: 5px"});
					}
					if(x.favorite == null){
						this.favorite = 0;
					}else{
						this.favorite = x.favorite;
					}
					if(source.isLiked == "true"){
						var favoriteDiv = domConstruct.create("div", {innerHTML: "Favorited(" + this.favorite + ")", "class": "twitterBlueDiv", style: "margin-right: 5px"});
					}else{
						var favoriteDiv = domConstruct.create("div", {innerHTML: "Favorite(" + this.favorite + ")", "class": "twitterOrangeDiv", style: "margin-right: 5px"});
					}
					var blastDiv = domConstruct.create("div", {style: "margin-left:5px", innerHTML: "Blast", "class": "twitterBlueDiv"});

					this.replyCounter = 0;
					this.retweetCounter = 0;
					this.favoriteCounter = 0;
					
					replyDiv.onclick = lang.hitch(this, function(){	
						if(!this.pane){
							this.pane = new DataObjPane({
								data: this.data,
								type: 'twitReply',
								counter: this.counter
							})
							this.addChild(this.pane);
						}else{
							if(this.replyCounter%2 == 0){
								this.pane.destroyRecursive()
								this.pane = null;
								this.pane = new DataObjPane({
									data: this.data,
									type: 'twitReply',
									counter: this.counter
								})
								this.addChild(this.pane);
								this.favoriteCounter = 0;
								this.retweetCounter = 0;
							}else{
								this.pane.destroyRecursive()
								this.pane = null;
							}
						}
						this.replyCounter++;
					})
					
					retweetDiv.onclick = lang.hitch(this, function(retweetDiv, id){
						if(domClass.contains(retweetDiv, "twitterOrangeDiv")){
							this.retweet++;
							domClass.remove(retweetDiv, "twitterOrangeDiv");
							domClass.add(retweetDiv, "twitterBlueDiv");
							retweetDiv.innerHTML = "Retweeted(" + (this.retweet) + ")";
							this.setIsCommented("twitter-"+id, "true");
							
							var tweetID = obj.id.split("-----");
							tweetID = tweetID[1];

							this.sendTwitRetweet(tweetID).then(lang.hitch(this, function(obj){
								console.log("obj is", obj);
							}));
						}
					}, retweetDiv, id);
					
					favoriteDiv.onclick = lang.hitch(this, function(favoriteDiv, id){
						if(domClass.contains(favoriteDiv, "twitterOrangeDiv")){
							this.favorite++
							domClass.remove(favoriteDiv, "twitterOrangeDiv");
							domClass.add(favoriteDiv, "twitterBlueDiv");
							favoriteDiv.innerHTML = "Favorited(" + (this.favorite) + ")";
							this.setIsLiked("twitter-"+id, "true");
							
							var tweetID = obj.id.split("-----");
							tweetID = tweetID[1];

							this.sendTwitterFav(tweetID).then(lang.hitch(this, function(obj){
								console.log("obj is", obj);
							}));
						}else{
							this.favorite--;
							domClass.remove(favoriteDiv, "twitterBlueDiv");
							domClass.add(favoriteDiv, "twitterOrangeDiv");
							favoriteDiv.innerHTML = "Favorite(" + (this.favorite) + ")";
							this.setIsLiked("twitter-"+id, "false");
							
							var tweetID = obj.id.split("-----");
							tweetID = tweetID[1];

							this.sendTwitterUnFav(tweetID).then(lang.hitch(this, function(obj){
								console.log("obj is", obj);
							}));
						}
						this.favoriteCounter++;
					}, favoriteDiv, id);
					
					blastDiv.onclick = lang.hitch(this, function(blastDiv, source){
						console.log("source: ", source);//function
					}, blastDiv, source);
					
					this.commentHolder.domNode.appendChild(replyDiv);
					this.commentHolder.domNode.appendChild(retweetDiv);
					this.commentHolder.domNode.appendChild(favoriteDiv);
					this.commentHolder.domNode.appendChild(blastDiv);
					this.roundRight.addChild(this.commentHolder);
					
				}
			//RightPane/RoundRect
			}
		});
	}
);
