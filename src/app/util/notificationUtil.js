define(['dojo/_base/declare',
		'dojo/_base/kernel',
		'dojo/topic',
		'dojo-mama/util/toaster',
		'dojo/_base/lang',
		'dojo/dom',
		'dojo/query',
		'dojo/dom-class',
		
		'app/util/xhrManager'
	], function(
		declare, 
		kernel, 
		topic, 
		toaster,
		lang,
		dom,
		query,
		domClass,
		
		xhrManager
	){
	
	//define these as normal functions that can then be called from the rest of the object
	var check = function(){
		return xhrManager.send('GET', 'rest/v1.0/Notifications/checkNotifications', {})
	};
	
	var updateNotifications = function(obj){
		var numberNots = 0;
		var keys = [];
		for(var k in obj){
			keys.push(k);
		}
		for(var x = 0; x < keys.length; x++){
			//I think this will work
			if(obj[keys[x]].length > 0){
				if(obj[keys[x]]['notes'] > 0){
					numberNots+= obj[keys[x]]['notes'];
				}
				if(obj[keys[x]]['messages'] > 0){
					numberNots+= obj[keys[x]]['messages'];
				}
				if(obj[keys[x]]['friends'].length > 0){
					numberNots++;
				}
			}
		}
		if(numberNots > 0){
			var list = query(".dmMenuBar");
			var menuBar = list[0];
			//if(menuBar != null){
				menuBar.firstChild.innerHTML = "Pending ("+numberNots+")";
			//}
			/*
			if(dom.byId("dojox_mobile_Pane_6") != null){
				if(domClass.contains(dom.byId("dojox_mobile_Pane_6"), "dmMenuBar")){
					var menuBar = dom.byId("dojox_mobile_Pane_6");
				}else{
					//otherwise start walking down right pane
					var possible = dom.byId("dojox_mobile_Pane_1");
					var out = false;
					while(out != true){
						possible = possible.firstChild;
						if(domClass.contains(possible, "dmMenuBar")){
							var menuBar = possible;
							out = true;
						}
					}
				}
			}
			*/
		}
		kernel.global.notifications.noteObj = obj;
	};
		
	var notificationUtil = declare([], {
		
		makeItHappen: function(){
			check().then(lang.hitch(this, updateNotifications));
			//still need to lang.hitch this where this is the function scope
			window.setInterval(lang.hitch(this, function(){
				check().then(lang.hitch(this, updateNotifications));
			//check every 5 minutes (cron is 5 minutes)
			}), 300000);	
		}
	
	});

	return new notificationUtil();
});
