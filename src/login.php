<?php	

	/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the app login page
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
	session_start();

	$dom = $_SERVER['SERVER_NAME'];
	$domArr = explode(".", $dom);
	$one = array_pop($domArr);
	$two = array_pop($domArr);
	$cookieDom = $two . "." . $one;
	$cookieDom = null;
	
	function getData(){
		try{
			$credObj = file_get_contents("serviceCreds.json");
			$credObj = json_decode($credObj, true);
		}catch (Exception $e){
			$credObj = array(
				"facebook" => array(),
				"twitter" => array(),
				"linkedin" => array(),
				"instagram" => array(),
				"google" => array(),
				"login" => "first"
			);
			file_put_contents("serviceCreds.json", json_encode($credObj));
		}
		return $credObj;
	}
	
	if(isset($_GET['logout']) && $_GET['logout'] == "true"){
		setcookie("facebookCook", $_COOKIE['PHPSESSID'], time()-3600, '/', $cookieDom, false, false);
		setcookie("linkedinCook", $_COOKIE['PHPSESSID'], time()-3600, '/', $cookieDom, false, false);
		setcookie("twitterCook", $_COOKIE['PHPSESSID'], time()-3600, '/', $cookieDom, false, false);
		setcookie("instagramCook", $_COOKIE['PHPSESSID'], time()-3600, '/', $cookieDom, false, false);
		setcookie("googleCook", $_COOKIE['PHPSESSID'], time()-3600, '/', $cookieDom, false, false);
		header('Location: /auth.php');
	}
	
	#print_r($_COOKIE);
	$var = getData();
	$fCount = (string)count($var['facebook']);
	$tCount = (string)count($var['twitter']);
	$lCount = (string)count($var['linkedin']);
	$iCount = (string)count($var['instagram']);
	$gCount = (string)count($var['google']);
	$login = $var['login'];

	//print_r($var);

	$goToManAccounts = "false";

	foreach($var as $key => $value){
		if($key != "login"){
			for($x = 0; $x < count($var[$key]); $x++){
				if(isset($var[$key][$x]['accounts']) && count($var[$key][$x]['accounts']) > 0){
					for($y = 0; $y < count($var[$key][$x]['accounts']); $y++){
						if(isset($var[$key][$x]['accounts'][$y]['authenticated'])){
							if($var[$key][$x]['accounts'][$y]['authenticated'] == "false"){
								$goToManAccounts = "true";
							}
						}else{
							$goToManAccounts = "true";
						}
					}
				}else{
					$goToManAccounts = "true";
				}
			}
		}
	} 
	
	if(isset($_GET['error'])){
		$errorCode = $_GET['error'];
		$errorService = $_GET['service'];
	}else{
		$errorCode = "";
	}	
	if(count($var['facebook'][0]['accounts']) > 0){
		$fCook = "true";
	}else{
		$fCook = "false";
	}
	if(count($var['linkedin'][0]['accounts']) > 0){
		$lCook = "true";
	}else{
		$lCook = "false";
	}
	if(count($var['instagram'][0]['accounts']) > 0){
		$iCook = "true";
	}else{
		$iCook = "false";
	}
	if(count($var['twitter'][0]['accounts']) > 0){
		$tCook = "true";
	}else{
		$tCook = "false";
	}
	if(count($var['google'][0]['accounts']) > 0){
		$gCook = "true";
	}else{
		$gCook = "false";
	}
	
	if(isset($_GET['facebook']) && $_GET['facebook'] == "true"){
		setcookie("facebookCook", $_COOKIE['PHPSESSID'], time()+ (604800), '/', $cookieDom, false, false);
		$ctx = stream_context_create(array(
		    'http' => array(
			'timeout' => 1
			)
		    )
		);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/cronManager.php", 0, $ctx);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/clientManager.php", 0, $ctx);
		if((isset($_GET['login']) && $_GET['login'] !== "second") || $goToManAccounts == "true"){
			header('Location: /#/manAccounts/AuthAccounts');
		}else{
			header('Location: /#/mainFeed');
		}
	}
	if(isset($_GET['linkedin']) && $_GET['linkedin'] == "true"){
		setcookie("linkedinCook", $_COOKIE['PHPSESSID'], time()+ (604800), '/', $cookieDom, false, false);
		$ctx = stream_context_create(array(
		    'http' => array(
			'timeout' => 1
			)
		    )
		);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/cronManager.php", 0, $ctx);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/clientManager.php", 0, $ctx);
		if((isset($_GET['login']) && $_GET['login'] !== "second") || $goToManAccounts == "true"){
			header('Location: /#/manAccounts/AuthAccounts');
		}else{	
			header('Location: /#/mainFeed');
		}
	}
	if(isset($_GET['twitter']) && $_GET['twitter'] == "true"){
		setcookie("twitterCook", $_COOKIE['PHPSESSID'], time()+ (604800), '/', $cookieDom, false, false);
		$ctx = stream_context_create(array(
		    'http' => array(
			'timeout' => 1
			)
		    )
		);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/cronManager.php", 0, $ctx);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/clientManager.php", 0, $ctx);
		if((isset($_GET['login']) && $_GET['login'] !== "second") || $goToManAccounts == "true"){
			header('Location: /#/manAccounts/AuthAccounts');
		}else{
			header('Location: /#/mainFeed');
		}
	}
	if(isset($_GET['instagram']) && $_GET['instagram'] == "true"){
		setcookie("instagramCook", $_COOKIE['PHPSESSID'], time()+ (604800), '/', $cookieDom, false, false);
		$ctx = stream_context_create(array(
		    'http' => array(
			'timeout' => 1
			)
		    )
		);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/cronManager.php", 0, $ctx);
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/clientManager.php", 0, $ctx);
		if((isset($_GET['login']) && $_GET['login'] !== "second") || $goToManAccounts == "true"){
			header('Location: /#/manAccounts/AuthAccounts');
		}else{	
			header('Location: /#/mainFeed');
		}
	}
	if(isset($_GET['google']) && $_GET['google'] == "true"){
		setcookie("googleCook", $_COOKIE['PHPSESSID'], time()+ (604800), '/', $cookieDom, false, false);
			$ctx = stream_context_create(array(
			    'http' => array(
				'timeout' => 1
				)
			    )
			);
			file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/cronManager.php", 0, $ctx);
			file_get_contents("http://".$_SERVER['HTTP_HOST']."/cron/poller/clientManager.php", 0, $ctx);
		if((isset($_GET['login']) && $_GET['login'] !== "second") || $goToManAccounts == "true"){
			header('Location: /#/manAccounts/AuthAccounts');
		}else{
			header('Location: /#/mainFeed');
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Please Login</title>	
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="pragma" content="no-cache">
	
	<link rel="stylesheet" href="app/resources/css/app.css">
	 <script data-dojo-config="async: 1, tlmSiblingOfDojo: 0, isDebug: 1, cacheBust: 1" src="dojo/dojo.js"></script>
	
</head>
<body class="bodyCSS">
   
	<script type="text/javascript">
		require([			
			'dojo/_base/declare',
			"dojo/_base/window",
			'dojo/dom-construct',
			'dojo/topic',
			'dojo/has',
			'dojo/_base/kernel',
			"dojo/_base/lang",
			"dojo/DeferredList",
			
			"dojox/mobile/RoundRectList",
			"dojox/mobile/TextBox",
			"dojox/mobile/ListItem",
			"dojox/mobile/Button",
			"dojox/mobile/Container",
			"dojox/mobile/GridLayout",
			"dojox/mobile/Pane",
			
			"dojo/_base/xhr"
			
		], function(			
			declare, 
			domWindow,
			domConstruct, 
			topic, 
			has,
			kernel, 
			lang, 
			DeferredList, 
			
			RoundRectList, 
			TextBox, 
			ListItem, 
			Button, 
			Container, 
			Grid, 
			Pane, 
			
			xhr						
		){
		
			buildLoginView = function(){
				
				if('<?php echo $fCount; ?>' == 0 &&
					'<?php echo $iCount; ?>' == 0 &&
					'<?php echo $lCount; ?>' == 0 &&
					'<?php echo $tCount; ?>' == 0 &&
					'<?php echo $gCount; ?>' == 0
				){
					window.location = "credentials.php";
				}else{
					xhr.get({
						url: 'serviceCreds.json',
						handleAs: 'json',
						async: false,
						preventCache: true,
						content: { },
						error: function(error){
							console.log("Error has occurred: " + error);
						}
					}).then(lang.hitch(this, function(serviceCreds){
						var leftPane = new Container({
							style: "text-align: center"
						});
						domWindow.body().appendChild(leftPane.domNode);

						if(has('iphone') != undefined){
							domWindow.body().style.overflow = "scroll";
						}

						if(has('android' < 4)){
							domWindow.body().style.overflow = "scroll";
						}

						document.body.style.backgroundColor = "#d0d3d4";

						console.log("document.body: ", document.body.style);
						
						var item = new ListItem({
							label: "Welcome to",
							style: "border:none;height:35px;font-size;font-family:arial;font-size:20px; background-color: #d0d3d4"
						});
						leftPane.addChild(item);

						var acilosLoginDiv = domConstruct.create("div", {innerHTML: "<img src=app/resources/img/acilosLoginLogo.png>"});
						leftPane.domNode.appendChild(acilosLoginDiv);

						var item = new ListItem({
							label: "Please Log In To Continue",
							style: "border:none;height:35px;font-size;font-family:arial;font-size:20px; background-color: #d0d3d4"
						});
						leftPane.addChild(item);
						
						if('<?php echo $errorCode; ?>' != ""){
							var list = new RoundRectList({	})
							if('<?php echo $errorCode; ?>' == "1"){
								var item = new ListItem({
									variableHeight: true,
									label: "Failed to authenticate user",
									style: "border:none;height:30px;padding-left:4px;background-color:inherit"
								});
								var button = new Button({
									label: "Change accounts",
									style: "margin-left:5px;margin-bottom:10px",
									onClick: function(){
										var newWin = window.open();
										newWin.location = "https://"+'<?php echo $errorService; ?>'+".com";
									}
								});
								leftPane.addChild(item);
								leftPane.addChild(button);
							}
							if('<?php echo $errorCode; ?>' == "2"){
								var item = new ListItem({
									label: "Error logging in to " + '<?php echo $errorService; ?>',
									style: "background-color:inherit"
								});
								leftPane.addChild(item);
							}						
						}

						loginPart(leftPane, serviceCreds);
					}));
				}
			},
			
			loginButtonsNitems = function(param, div, serviceCreds, mainDiv){
				if(param == "facebook"){
					if('<?php echo $login; ?>' == "first"){
						var button = new Button({
							"class": "loginLogoButton",
							onClick: lang.hitch(null, function(){
								window.location = serviceCreds[param][0]["auth"] + "&state=outside";
							},serviceCreds, param)
						});
						var faceDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/facebookLogin.png>"});
						button.domNode.appendChild(faceDiv);
						div.appendChild(button.domNode);
					}else{						
						for(var o = 0; o < serviceCreds[param][0]["accounts"].length; o++){
							if(serviceCreds[param][0]["accounts"][o].authenticated == "true"){
								var button = new Button({
									"class": "loginLogoButton",
									onClick: lang.hitch(null, function(){
										window.location = serviceCreds[param][0]["auth"] + "&state=outside";
									},serviceCreds, param)
								});
								var faceDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/facebookLogin.png>"});
								button.domNode.appendChild(faceDiv);
								div.appendChild(button.domNode);
								break;
							}
						}
					}
				}
				if(param == "twitter"){
					if('<?php echo $login; ?>' == "first"){
						var button = new Button({
							"class": "loginLogoButton",
							onClick: lang.hitch(null, function(){
								window.location = serviceCreds[param][0]["auth"] + "&state=outside";
							},serviceCreds, param)
						});
						var twitDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/twitterLogin.png>"});
						button.domNode.appendChild(twitDiv);
						div.appendChild(button.domNode);
					}else{
						for(var o = 0; o < serviceCreds[param][0]["accounts"].length; o++){
							if(serviceCreds[param][0]["accounts"][o].authenticated == "true"){
								var button = new Button({
									"class": "loginLogoButton",
									onClick: lang.hitch(null, function(){
										window.location = serviceCreds[param][0]["auth"] + "&state=outside";
									},serviceCreds, param)
								});
								var twitDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/twitterLogin.png>"});
								button.domNode.appendChild(twitDiv);
								div.appendChild(button.domNode);
								break;
							}
						}
					}					
				}
				if(param == "instagram"){
					if('<?php echo $login; ?>' == "first"){
						var button = new Button({
							"class": "loginLogoButton",
							onClick: lang.hitch(null, function(){
								window.location = serviceCreds[param][0]["auth"] + "&state=outside";
							},serviceCreds, param)
						});
						var instaDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/instagramLogin.png>"});
						button.domNode.appendChild(instaDiv);
						div.appendChild(button.domNode);
					}else{
						for(var o = 0; o < serviceCreds[param][0]["accounts"].length; o++){
							if(serviceCreds[param][0]["accounts"][o].authenticated == "true"){
								var button = new Button({
									"class": "loginLogoButton",
									onClick: lang.hitch(null, function(){
										window.location = serviceCreds[param][0]["auth"] + "&state=outside";
									},serviceCreds, param)
								});
								var instaDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/instagramLogin.png>"});
								button.domNode.appendChild(instaDiv);
								div.appendChild(button.domNode);
								break;
							}
						}
					}
				}
				if(param == "linkedin"){
					if('<?php echo $login; ?>' == "first"){
						var button = new Button({
							"class": "loginLogoButton",
							onClick: lang.hitch(null, function(){
								window.location = serviceCreds[param][0]["auth"] + "&state=outside";
							},serviceCreds, param)
						});
						var linkDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/linkedinLogin.png>"});
						button.domNode.appendChild(linkDiv);
						div.appendChild(button.domNode);	
					}else{
						for(var o = 0; o < serviceCreds[param][0]["accounts"].length; o++){
							if(serviceCreds[param][0]["accounts"][o].authenticated == "true"){
								var button = new Button({
									"class": "loginLogoButton",
									onClick: lang.hitch(null, function(){
										window.location = serviceCreds[param][0]["auth"] + "&state=outside";
									},serviceCreds, param)
								});
								var linkDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/linkedinLogin.png>"});
								button.domNode.appendChild(linkDiv);
								div.appendChild(button.domNode);
								break;
							}
						}
					}
				}
				if(param == "google"){
					if('<?php echo $login; ?>' == "first"){
						var button = new Button({
							"class": "loginLogoButton",
							onClick: lang.hitch(null, function(){
								window.location = serviceCreds[param][0]["auth"] + "&state=outside";
							},serviceCreds, param)
						});
						var googDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/googlePlusLogin.png>"});
						button.domNode.appendChild(googDiv);
						div.appendChild(button.domNode);	
					}else{
						for(var o = 0; o < serviceCreds[param][0]["accounts"].length; o++){
							if(serviceCreds[param][0]["accounts"][o].authenticated == "true"){
								var button = new Button({
									"class": "loginLogoButton",
									onClick: lang.hitch(null, function(){
										window.location = serviceCreds[param][0]["auth"] + "&state=outside";
									},serviceCreds, param)
								});
								var googDiv = domConstruct.create("div", {"class":"loginLogo", innerHTML: "<img src=app/resources/img/googlePlusLogin.png>"});
								button.domNode.appendChild(googDiv);
								div.appendChild(button.domNode);
								break;
							}
						}
					}
				}
				mainDiv.appendChild(div);
			},
			
			loginPart = function(leftPane, serviceCreds){
				var mainDiv = domConstruct.create("div", {style: "width: 304px; margin-top: auto; margin-left: auto; margin-right: auto; margin-bottom: auto"});
				
				if(	'<?php echo $fCook; ?>' != "true" &&
					'<?php echo $tCook; ?>' != "true" &&
					'<?php echo $lCook; ?>' != "true" &&
					'<?php echo $iCook; ?>' != "true" &&
					'<?php echo $gCook; ?>' != "true" &&
					'<?php echo $login; ?>' != "first"
				){
					window.location = "login.php?login=first";
				}else{
					if('<?php echo $tCount; ?>' != 0){
						var div1 = domConstruct.create("span", {});
						loginButtonsNitems("twitter", div1, serviceCreds, mainDiv);
					}
					if('<?php echo $fCount; ?>' != 0){
						var div2 = domConstruct.create("span", {});
						loginButtonsNitems("facebook", div2, serviceCreds, mainDiv);
					}
					if('<?php echo $lCount; ?>' != 0){
						var div3 = domConstruct.create("span", {});
						loginButtonsNitems("linkedin", div3, serviceCreds, mainDiv);
					}
					if('<?php echo $iCount; ?>' != 0){
						var div4 = domConstruct.create("span", {});
						loginButtonsNitems("instagram", div4, serviceCreds, mainDiv);
					}
					if('<?php echo $gCount; ?>' != 0){
						var div4 = domConstruct.create("span", {});
						loginButtonsNitems("google", div4, serviceCreds, mainDiv);
					}
				}

				leftPane.domNode.appendChild(mainDiv);
			},
			
			buildLoginView();
		})
	</script>
</body>
</html>
