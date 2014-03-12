<?php

require_once('../objects/activityObject.php');
require_once('../objects/userBaseObject.php');
require_once('../../cron/objects/clientBaseObject.php');
require_once('../../oAuth/twitteroauth/twitteroauth.php');
require_once('../../vendor/autoload.php');

require_once('../../rest/v1.0/lib/counts.php');
require_once('../../rest/v1.0/lib/S3Functions.php');

use \ElasticSearch\Client;
use \Aws\S3\S3Client;

$clientObject = getClients();

function getData($size){
	$index = "app";
	$host = "localhost";
	$port = "9200";

	$es = Client::connection("http://$host:$port/$index/$index");
	$searchArr = array();

	$searchArr = array(
		'size' => $size,
		"query" => array(
			'match_all' => array()
		),
		'sort' => array(
			'published' => array(
				"order" => "asc"
			)
		)
	);

	$res = $es->search($searchArr);

	return $res;
}

function getGeoLocation($loc){
	$key = "Fmjtd%7Cluub2g07nu%2Cb0%3Do5-9ub2gr";
	if($loc == ""){
		return $loc;
	}else{
		$cityclean = str_replace(" ", ",", $loc);
		#$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $cityclean . "&sensor=false";
		$url = "http://www.mapquestapi.com/geocoding/v1/address?key=" . $key . "&callback=&inFormat=kvp&outFormat=json&location=".$cityclean;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$var = json_decode($response, true);

		$latLong = "";
		if($var['info']['statuscode'] != 0){
			$latLong = "";
		}else{
			$latLong = $var['results'][0]['locations'][0]['latLng']['lat'] . "#" . $var['results'][0]['locations'][0]['latLng']['lng'] ;
		}
		return $latLong;
	}
}

function writeObject($obj){
	#echo "write object"; 

	$index = "app";
	$host = "localhost";
	$port = "9200";

	$es = Client::connection("http://$host:$port/$index/$index/");

	$obj['id'] = strtolower($obj['id']);
	$exists = getObject($obj['id']);
	if(isset($exists['starred'])){
		$obj['starred'] = $exists['starred'];
		$obj['isLiked'] = $exists['isLiked'];
		$obj['isCommented'] = $exists['isCommented'];
		$obj['isFavorited'] = $exists['isFavorited'];
	}

	$grr = $es->index($obj, $obj['id']);
	#print_r($grr);

	global $clientObject;
	updateRecentPost($clientObject, $obj);
}

function getObject($id){
	#echo "getting object"; 

	$index = "app";
	$host = "localhost";
	$port = "9200";

	$es = Client::connection("http://$host:$port/$index/$index");
	$res = $es->get($id);

	return $res;
}

function getClients(){
	echo "getting clients"; ?><br/><?php

	$index = "client";
	$host = "localhost";
	$port = "9200";

	$es = Client::connection("http://$host:$port/$index/$index");

	$clients = $es->search(array(
		'size' => 2000,
		'query' => array(
			'match_all' => array()
		)
	));

	$dataArr = array();
	for($x = 0; $x < count($clients['hits']['hits']); $x++){
		$tempArr = explode("-----", $clients['hits']['hits'][$x]['_source']['data']['id']);
		$dataArr[$tempArr[1]] = $clients['hits']['hits'][$x]['_source'];
	}

	return $dataArr;
}

function writeClient($obj){
	"writing to client"; 

	$index = "client";
	$host = "localhost";
	$port = "9200";

	$es = Client::connection("http://$host:$port/$index/$index/");

	$grr = $es->index($obj, $obj['data']['id']);
	return $grr;
}

function updateRecentPost($clientObject, $post){

	//Dont count CONN objects as posts
	if($post['title']) != "CONN"){
		//if that poster is a client then update most recent
		if(isset($clientObject[$post['actor']->id])){
			$tempClientObj = $clientObject[$post['actor']->id];
			$tempClientObj['data']['service'] = $post['service'];
			if($tempClientObj['data']['post']['recentPostTime'] < $post['published'] || $tempClientObj['data']['post']['recentPostTime'] = ''){
				$tempClientObj['data']['post']['recentPostTime'] = $post['published'];
				$tempClientObj['data']['post']['recentPost'] = $post['id'];
				$tempClientObj['data']['post']['totalPosts'] = $tempClientObj['data']['post']['totalPosts'] + 1;
				if($post['actor']->location != '' || $post['actor']->location != null){
					$arr = explode("#", $post['actor']->location);
					if(count($arr) == 2){
						$tempClientObj['data']['currentTown'] = $post['actor']->location;
					}else{
						$geo = getGeoLocation($post['actor']->location);
						$tempClientObj['data']['currentTown'] = $geo;
					}
				}

				//$log->logInfo($logPrefix.'updateRecentPost() calling writeClient to update recentPost');
				writeClient($tempClientObj);
			}
		}else{
			//else add them as a client and set their most recent
			$client = new clientObject(); 
			$client->setDisplayName($post['actor']->displayName);
			$client->setID($post['service'].'-----'.$post['actor']->id);
			$client->setRecentPost($post['id']);
			$client->setRecentPostTime($post['published']);
			$client->setTotalPosts(1);
			$client->setService($post['service']);
			$client->setFriendDegree("second");

			if($post['actor']->location != '' || $post['actor']->location != null){
				$geo = getGeoLocation($post['actor']->location);
				$client->setCurrentTown($geo);
			}		

			$type = $post['service'];
			$credential = array('id' => $post['actor']->id, 'givenName' => $post['actor']->displayName, 'displayName' => $post['actor']->displayName);
			$client->setCredential($type, $credential);

			$var = writeClient((array)$client);

		}
	}
}
//INSTAGRAM GOOOOO   -----------------------------------------------------------------------------
function getUserFeed(){
	echo "get instagram stuff"; ?><br/><?php

	//get the token from the file
	$filename = "../../serviceCreds.json";
	$file = file_get_contents($filename);

	$tokenObject = json_decode($file, true);
	$instagramTokens = $tokenObject['instagram'];

	foreach($instagramTokens as $obj){	
		$url = "https://api.instagram.com/v1/users/self/feed?access_token=".$obj['access_token'];

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);

		$var = json_decode($res, true);

		normalizeInstaObject($var['data'], $obj);
	}
}

function normalizeInstaObject($objArray, $account){
	echo "normal insta";
	//global //$log;
	//global //?$logPrefix;
	//$log->logInfo($logPrefix."There are " . count($objArray) . " objects in the Instagram timeline");

	$mediaArray = array();
	for($k = 0; $k < count($objArray); $k++){
		$obj = $objArray[$k];

		#print_r($obj);

		$manager = new Manager();
		$builder = new instagramObjectBuilder();
		$manager->setBuilder($builder);
		$manager->parseActivityObj($obj, $account);

		$item = $manager->getActivityObj();

		writeObject((array)$item);
		global $credentialObject;
		$credentialObject['Instagram']['status'] = 'good';
	}
}
//INSTAGRAM STOP   -----------------------------------------------------------------------------

//FACEBOOK GOOOOOO --------------------------------------------------------------------------
function normalizeNewsFeedObj($objArray, $account){
	echo "normal face stuff"; 
	for($k = 0; $k < count($objArray); $k++){
		$obje = $objArray[$k];

		#print_r($obj); 

		$manager = new Manager();
		$builder = new facebookNewsFeedObjectBuilder();
		$manager->setBuilder($builder);

		$manager->parseActivityObj($obje, $account);

		$item = $manager->getActivityObj();

		#print_r($item); 

		writeObject((array)$item);
		global $credentialObject;
		$credentialObject['Facebook']['status'] = 'good';
	}
}

function getUserNewsFeed(){
	echo "get facebook stuff"; 

	$filename = "../../serviceCreds.json";

	$file = file_get_contents($filename);

	$tokenObject = json_decode($file, true);
	$facebookTokens = $tokenObject['facebook'];

	foreach($facebookTokens as $obj){
		$url = 'https://graph.facebook.com/me/home?&access_token=' . $obj['access_token'];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$var = json_decode($response, true);	

		#print_r($var);

		#take off the data layer so that 90% of the obj is top level
		normalizeNewsFeedObj($var['data'], $obj);
	}
}
//FACEBOOK STOP ----------------------------------------------------------------------------

//TWITTER GOOOOOOO ------------------------------------------------------------------------
function normalizeTwitterObject($objArray, $account){
	echo "normal twitter stuff"; 
	for($k = 0; $k < count($objArray); $k++){
		$obj = $objArray[$k];

		#print_r($obj);

		$manager = new Manager();
		$builder = new twitterObjectBuilder();
		$manager->setBuilder($builder);
		$manager->parseActivityObj($obj, $account);

		$item = $manager->getActivityObj();

		writeObject((array)$item);
		global $credentialObject;
		$credentialObject['Twitter']['status'] = 'good';
	}
}

function getUserTimeline(){
	echo "get twitter stuff"; ?><br/><?php
	$filename = "../../serviceCreds.json";
	$file = file_get_contents($filename);

	$tokenObject = json_decode($file, true);
	$twitterTokens = $tokenObject['twitter'];

	foreach($twitterTokens as $obj){
		$connection = new TwitterOAuth($obj['key'], $obj['secret'], $obj['access_token'], $obj['access_secret']);
		$connection->host = "https://api.twitter.com/1.1";

		$method = "/statuses/home_timeline";
		#took this down to 40 to see if it would improve the load on elasticsearch
		$var = $connection->get($method, array("count" => 40));

		$array = objectToArray($var);

		if($array['errors']){
			print_r($array['errors'][0]['message']);
			print_r($array['errors'][0]['code']);
			//refresh token or call get new token again
			//file_get_contents("../../oAuth/twitterAccess.php?appKey=" + $obj['appKey'] + "&appSecret=" + $obj['appSecret']);
		}else{
			normalizeTwitterObject($array, $obj);	    
		}
	}
}
//TWITTER STOP ---------------------------------------------------------------

//LINKEDIN GOOOOOOOOOOOOOO -----------------------------------------------------
function linkedInFetch($method, $resource, $token) {
	echo "linkedin fetch"; ?><br/><?php
	//global //$log;
	//global //?$logPrefix;
    $params = array('oauth2_access_token' => $token,
		    'format' => 'json',
	      );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    $context = stream_context_create(
		    array('http' => 
			array('method' => $method,
			)
		    )
		);
 
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);

    // Native PHP object, please
    return json_decode($response);
}

function linkedInFetchWithParams($method, $resource, $token, $start, $count) {
	echo "linkedin fetch with params"; ?><br/><?php
	//global //$log;
	//global //?$logPrefix;
    $params = array('oauth2_access_token' => $token,
		    'format' => 'json',
		    'start' => $start,
		    'count' => $count
	      );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    $context = stream_context_create(
		    array('http' => 
			array('method' => $method,
			)
		    )
		);
 
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
 
    // Native PHP object, please
    return json_decode($response);
}

function normalizeLinkedinObj($objArray, $account){
	echo "normal linkedin object"; ?><br/><?php
	#print_r($objArray);
	for($k = 0; $k < count($objArray); $k++){
			$obj = $objArray[$k];

			//print_r($obj);

			$manager = new Manager();
			$builder = new linkedInNetworkObjectBuilder();
			$manager->setBuilder($builder);

			$manager->parseActivityObj($obj, $account);

			$item = $manager->getActivityObj();

			//print_r($item); ?><br/><?php

			writeObject((array)$item);
			global $credentialObject;
			$credentialObject['Linkedin']['status'] = 'good';
	}
}

function normalizeDiscussionObj($objArray, $account){
	echo "normal linkedin discussion object"; ?><br/><?php
	#print_r($objArray);
	for($k = 0; $k < count($objArray); $k++){
			$obj = $objArray[$k];

			#print_r($obj);

			$manager = new Manager();
			$builder = new linkedInNetworkObjectBuilder();
			$manager->setBuilder($builder);

			$manager->parseActivityObj($obj, $account);

			$item = $manager->getActivityObj();

			#print_r($item); ?><br/><?php

			writeObject((array)$item);
			global $credentialObject;
			$credentialObject['Linkedin']['status'] = 'good';
	} 
}

function getPersonalFeed(){
	echo "get linkedin objects"; ?><br/><?php

	$filename = "../../serviceCreds.json";
	$file = file_get_contents($filename);

	$tokenObject = json_decode($file, true);
	$linkedinTokens = $tokenObject['linkedin'];

	foreach($linkedinTokens as $obj){
		$feed = linkedInFetchWithParams('GET', '/v1/people/~/network/updates', $obj['access_token'], 0, 100);

		$feed = objectToArray($feed);

		#print_r($feed);

		normalizeLinkedinObj($feed['values'], $obj);
	}
}

function getDiscussionObjects(){
	echo "get linkedin discussion objects"; ?><br/><?php

	$filename = "../../oAuth/linkedinToken.json";
	$file = file_get_contents($filename);

	$tokenObject = json_decode($file, true);

	foreach($tokenObject as $obj){	
		$user = linkedInFetch('GET', '/v1/people/~/group-memberships', $obj['access_token']);
		#print_r($token);
		$user = objectToArray($user);

		$groupArr = array();
		$counter = 0;
		for($z = 0; $z < count($user['values']); $z++){
			$groupPost = linkedInFetch('GET', '/v1/groups/'.$user['values'][$z]['group']['id'].'/posts:(creator:(first-name,last-name,picture-url,id,headline),title,summary,creation-timestamp,id,likes,comments,attachment:(image-url,content-domain,content-url,title,summary))', $token);
			$thing = objectToArray($groupPost);

			for($t = 0; $t < count($thing['values']); $t++){
				$groupArr[$counter]['networkObjectType'] = "DISCUSS";
				$groupArr[$counter]['timestamp'] = $thing['values'][$t]['creationTimestamp'];
				$groupArr[$counter]['id'] = $thing['values'][$t]['id'];

				$groupArr[$counter]['group']['name'] = $user['values'][$z]['group']['name'];
				$groupArr[$counter]['group']['id'] = $user['values'][$z]['group']['id'];
				$groupArr[$counter]['group']['status'] =  $user['values'][$z]['membershipState']['code'];

				$groupArr[$counter]['title'] = $thing['values'][$t]['title'];
				$groupArr[$counter]['summary'] = $thing['values'][$t]['summary'];
				$groupArr[$counter]['creator']['firstName'] = $thing['values'][$t]['creator']['firstName'];
				$groupArr[$counter]['creator']['lastName'] = $thing['values'][$t]['creator']['lastName'];
				$groupArr[$counter]['creator']['pictureUrl'] = $thing['values'][$t]['creator']['pictureUrl'];
				$groupArr[$counter]['creator']['headline'] = $thing['values'][$t]['creator']['headline'];
				$groupArr[$counter]['creator']['id'] = $thing['values'][$t]['creator']['id'];

				if($thing['values'][$t]['attachment'] != null){
					$groupArr[$counter]['attachment'][0]['contentDomain'] = $thing['values'][$t]['attachment']['contentDomain'];
					$groupArr[$counter]['attachment'][0]['contentUrl'] = $thing['values'][$t]['attachment']['contentUrl'];
					$groupArr[$counter]['attachment'][0]['imageUrl'] = $thing['values'][$t]['attachment']['imageUrl'];
					$groupArr[$counter]['attachment'][0]['summary'] = $thing['values'][$t]['attachment']['summary'];
					$groupArr[$counter]['attachment'][0]['title'] = $thing['values'][$t]['attachment']['title'];
				}else{
					$groupArr[$counter]['attachment'] = array();
				}

				$groupArr[$counter]['comments'] = array();
				for($x = 0; $x < $thing['values'][$t]['comments']['_total']; $x++){
					$groupArr[$counter]['comments'][$x]['person']['firstName'] = $thing['values'][$t]['comments']['values'][$x]['creator']['firstName'];
					$groupArr[$counter]['comments'][$x]['person']['lastName'] = $thing['values'][$t]['comments']['values'][$x]['creator']['lastName'];
					$groupArr[$counter]['comments'][$x]['person']['id'] = $thing['values'][$t]['comments']['values'][$x]['creator']['id'];
					$groupArr[$counter]['comments'][$x]['person']['headline'] = $thing['values'][$t]['comments']['values'][$x]['creator']['headline'];
					$groupArr[$counter]['comments'][$x]['person']['pictureUrl'] = $thing['values'][$t]['comments']['values'][$x]['creator']['pictureUrl'];
					$groupArr[$counter]['comments'][$x]['id'] = $thing['values'][$t]['comments']['values'][$x]['id'];
					$groupArr[$counter]['comments'][$x]['text'] = $thing['values'][$t]['comments']['values'][$x]['text'];
				}
				$groupArr[$counter]['likes'] = array();
				for($x = 0; $x < $thing['values'][$t]['likes']['_total']; $x++){
					$groupArr[$counter]['likes'][$x]['person']['firstName'] = $thing['values'][$t]['likes']['values'][$x]['person']['firstName'];
					$groupArr[$counter]['likes'][$x]['person']['lastName'] = $thing['values'][$t]['likes']['values'][$x]['person']['lastName'];
					$groupArr[$counter]['likes'][$x]['person']['id'] = $thing['values'][$t]['likes']['values'][$x]['person']['id'];
					$groupArr[$counter]['likes'][$x]['person']['headline'] = $thing['values'][$t]['likes']['values'][$x]['person']['headline'];
					$groupArr[$counter]['likes'][$x]['person']['pictureUrl'] = $thing['values'][$t]['likes']['values'][$x]['person']['pictureUrl'];
				}
			$counter++;
			}
		}
		normalizeDiscussionObj($groupArr, $obj);
	}
}

//300 = 5 mins
if(!file_exists("../../lockFiles/cronManager.lock") || (time() > filemtime("../../lockFiles/polly.lock") + 300)){
	touch("../../lockFiles/cronManager.lock");

	if(isset($_GET['ServiceObj'])){
		//$credentialObject = array(
		//	"Linkedin" => array(
		//		"status" => "bad"
		//	),
		//	"Twitter" => array(
		//		"status" => "bad"
		//	),
		//	"Facebook" => array(
		//		"status" => "bad"
		//	),
		//	"Instagram" => array(
		//		"status" => "bad"
		//	)
		//);

		$serviceObj = json_decode($_GET['ServiceObj'], true);
		foreach($serviceObj as $key => $value){
			if($key == "Facebook" && $value == "true"){
				getUserNewsFeed();
			}

			if($key == "Linkedin" && $value == "true"){				
				getDiscussionObjects();				
				getPersonalFeed();
			}

			if($key == "Twitter" && $value == "true"){
				getUserTimeline();
			}

			if($key == "Instagram" && $value == "true"){
				getUserFeed();
			}			
		}

		//Write out to the credential file how everything went
		//$filename = "appCredentialStatus.txt";
		//$fp = fopen($filename, 'w');
		//fwrite($fp, json_encode($credentialObject));
		//fclose($fp);

		//this will call the notification module
		require_once('../../oAuth/notifications/facebookNotifications.php');

	}else{

		echo "backup check";?><br/><?php
		$fs = disk_free_space("/");
		//convert bytes to megs
		$avail = $fs/(1024 * 1024);
		//if there is less than 400 megs
		if($avail < 400){
			//get all data count
			$totes = countAll();
			$total = json_decode($totes, true);	

			//backup 20% of the total data to make more room
			$backupNum = floor($total['count'] * .2);

			//get the data
			$data = getData($backupNum);

			//write it to a delete array and to make an object to save to S3
			$writeArr = array();
			$idArr = array();
			for($x = 0; $x < count($data['hits']['hits']); $x++){
				array_push($writeArr, $data['hits']['hits'][$x]['_source']);
				$idArr[$data['hits']['hits'][$x]['_source']['id']] = 1;
			}

			$object = array(
				"name" => "file-".time()."-".$backupNum.".json",
				"data" => $writeArr,
				"version" => "1.0"
			);

			$fileName = "file-".time().".json";

			//save the data to a temp file with object/writeArr
			file_put_contents($fileName, json_encode($object));

			//delete saved data from app with idArr
			deleteAllBackedUp($idArr);

			//upload the temp file
			uploadS3File($fileName);

			//delete the temp file
			unlink(realpath($fileName));

			$totes = countAll();
			$total = json_decode($totes, true);	

		}		
		echo "linkedin feed"; ?><br/><?php
		getPersonalFeed();

		echo "facebook feed"; ?><br/><?php
		getUserNewsFeed();

		echo "calling twitter stuff";?><br/><?php
		getUserTimeline();

		echo "instagram feed"; ?><br/><?php
		getUserFeed();

		echo "linkedin feed"; ?><br/><?php
		getDiscussionObjects();


	}
	unlink("../../lockFiles/cronManager.lock");
}

?>