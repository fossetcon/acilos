<?php

/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines the base feed object used for mining feedData
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

require_once('twitterObject.php');
require_once('facebookNewsFeedObject.php');
require_once('instagramObject.php');
require_once('linkedInNetworkObject.php');
require_once('googleFeedObject.php');

#PRODUCT
class Actor{
    public $name = "N/A";
    public $id = "N/A";
    public $searchable = "N/A";
    public $displayName = "N/A";
    public $image = "N/A";
    public $url = "N/A";
    public $location = "N/A";
    public $geoLocation = "N/A";
    public $description = "N/A";
   
   public function getGeoLocation($loc){
	if($loc == ""){
		return $loc;
	}else{
		$cityclean = str_replace(" ", "+", $loc);
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $cityclean . "&sensor=false";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$var = json_decode($response, true);

		$latLong = "";
		if($var['status'] == "ZERO_RESULTS"){
			$latLong = "";
		}else{
            if(isset($var['results'][0])){
                $latLong = $var['results'][0]['geometry']['location']['lat'] . "#" . $var['results'][0]['geometry']['location']['lng'];
            }
		}
		return $latLong;
	}
   }
    public function setName($name){
        $this->name = $name;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function setdisplayName($displayName){
        $this->displayName = $displayName;
    }
    public function setImage($image){
        $this->image = $image;
    }
    public function setURL($url){
        $this->url = $url;
    }
    public function setLocation($location){
        $this->location = $location;
    }
    public function setGeoLocation($geoLocation){
	$vars = $this->getGeoLocation($geoLocation);
        $this->geoLocation = $vars;
    }
    public function setDescription($description){
        $this->description = $description;
    }
    public function setSearchable($searchable){
        $this->searchable = $searchable;
    }
}

class textBlockWithURLS{
	public $text = "N/A";
	public $links = array();

	public function setText($textBlock){
		$this->text = $textBlock;
	}
	public function getHeaders($url){
		//$thing = file_get_contents($url);

        if(isset($http_response_header)){
            $numLines = count($http_response_header);
            for ( $i = 0; $i < $numLines; $i++ ) {
                $line = $http_response_header[$i];
                $lineArr = explode(" ", $line);

                for($g = 0; $g < count($lineArr); $g++){
                    if((string)$lineArr[$g] == "301"){          
                        break;
                    }elseif((string)$lineArr[$g] == "200"){             
                        $retArr = array(
                            "website" => $url,
                            "headers" => $http_response_header
                        );
                        return $retArr;
                    }
                }
                if ( substr_compare( $line, 'Location', 0, 8, true ) == 0 ) {
                    $location = explode(": ", $line);               
                    $retArr = array(
                        "website" => $location[1],
                        "headers" => $http_response_header
                    );
                    return $retArr;
                }
            }
        }
	}

	public function setLinks($sentence){
		preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $sentence, $result, PREG_PATTERN_ORDER);
		$res = $result[0];

		$urlArr = array();
		for($k = 0; $k < count($res); $k++){
			$response = $this->getHeaders($res[$k]);
			for ( $i = 0; $i < count($response["headers"]); $i++ ) {
				$line = $response["headers"][$i];
				if ( substr_compare( $line, 'Content-Type', 0, 12, true ) == 0 ) {
						$content_type = $line;
						break;
				}
			}

			/* Get the MIME type and character set */
            if(isset($content_type)){
                preg_match( '@Content-Type:\s+([\w/+]+)(;\s+charset=(\S+))?@i', $content_type, $matches );
            }
			
			if ( isset( $matches[1] ) )
				$mime = $matches[1];

			$youtube = "www.youtube.com";
			$website = "text\/";
			$video = "video\/";
			$image = "image\/";

			$obj = array();

			if( preg_match("/\b$youtube\b/i", $response["website"]) ){
				$search = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
				$replace = "http://www.youtube.com/embed/$2";
				$text = preg_replace($search, $replace, $res[$k]);

				//$urlArr[$res[$k]]["type"] = "youtube";
				//$urlArr[$res[$k]]["youtube"] = $text;

				$obj['type'] = "youtube";
				$obj['url'] = $res[$k];
				$obj['youtube'] = $text;

			}elseif(isset($mime)){
                if( preg_match("/\b$website\b/i", $mime) ){
    				$search = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
    				$replace = "http://www.youtube.com/embed/$2";
    				$text = preg_replace($search, $replace, $response["website"]);
    				if(preg_match("/\b$youtube\b/i", $text)){
    					$obj["type"] = "youtube";
    					$obj["youtube"] = $text;
    				}else{
    					$obj["type"] = "website";
    					$obj["website"] = $text;
    				}

    				$obj['url'] = $res[$k];
                }elseif( preg_match("/\b$video\b/i", $mime) ){
                    $obj["type"] = "video";
                    $obj["video"] = $response["website"];

                    $obj['url'] = $res[$k];
                }elseif( preg_match("/\b$image\b/i", $mime) ){
                    $obj["type"] = "image";
                    $obj["image"] = $response["website"];

                    $obj['url'] = $res[$k];
                }
			}

			$urlArr[]=$obj;
		}
		$this->links = $urlArr;
	}
}

#PRODUCT
class activityObject{
   
    public $actor = array();
    public $content = array();
    public $published = "N/A";
    public $generator = "N/A";
    public $title = "N/A";
    public $verb = "N/A";
    public $id = "N/A";
    public $service = "N/A";
    public $starred = "N/A";
    public $postLink = "N/A";
    public $isLiked = "N/A";
    public $isCommented = "N/A";
    public $isFavorited = "N/A";
    public $mainAccountName = "N/A";
    public $mainAccountID = "N/A";
    public $mainAccountColor = "N/A";   
    public function setActor($actor){
        $this->actor = $actor;
    }
    public function setContent($content){
        $this->content = $content;
    }
    public function setPublished($published){
        $this->published = $published;
    }
    public function setGenerator($generator){
        $this->generator = $generator;
    }
    public function setTitle($title){
        $this->title = $title;
    }
    public function setVerb($verb){
        $this->verb = $verb;
    }
     public function setId($id){
        $this->id = $id;
    }
    public function setService($service){
        $this->service = $service;
    }
    public function setStarred($starred){
	$this->starred = $starred;
    }
    public function setPostLink($postLink){
	$this->postLink = $postLink;
    }
    public function setIsLiked($isLiked){
	$this->isLiked = $isLiked;
    }
    public function setIsCommented($isCommented){
	$this->isCommented = $isCommented;
    }
    public function setIsFavorited($isFavorited){
	$this->isFavorited = $isFavorited;
    }
    public function setMainAccountName($mainAccountName){
	$this->mainAccountName = $mainAccountName;
    }
    public function setMainAccountID($mainAccountID){
	$this->mainAccountID = $mainAccountID;
    }
    public function setMainAccountColor($mainAccountColor){
	$this->mainAccountColor = $mainAccountColor;
    }
}

#ABSTRACT BUILDER
abstract class activityObjectBuilder {
    public $activityObject;
   
    public function getActivityObj() {
        return $this->activityObject;
    }
    public function createActivityObj() {
        $this->activityObject = new activityObject();
    }
   
    public abstract function buildActor($actor);
    public abstract function buildContent($content);
    public abstract function buildPublished($published);
    public abstract function buildGenerator($generator);
    public abstract function buildTitle($title);
    public abstract function buildVerb($verb);
    public abstract function buildId($id);
    public abstract function buildService($service);
    public abstract function buildStarred($starred);
    public abstract function buildPostLink($postLink, $account);
    public abstract function buildIsLiked($isLiked);
    public abstract function buildIsCommented($isCommented);
    public abstract function buildIsFavorited($isFavorited);
    public abstract function buildMainAccountName($mainAccountName);
    public abstract function buildMainAccountID($mainAccountID);
    public abstract function buildMainAccountColor($mainAccountColor);
}

#DIRECTOR
class Manager {
    public $activityObjectBuilder;
   
    public function setBuilder(ActivityObjectBuilder $activityObjectBuilder){
        $this->activityObjectBuilder = $activityObjectBuilder;
    }
    public function getActivityObj(){
        return $this->activityObjectBuilder->getActivityObj();
    }
    public function parseActivityObj($obj, $account){
        $this->activityObjectBuilder->createActivityObj();
        $this->activityObjectBuilder->buildActor($obj);
        $this->activityObjectBuilder->buildContent($obj);
        $this->activityObjectBuilder->buildPublished($obj);
        $this->activityObjectBuilder->buildGenerator($obj);
        $this->activityObjectBuilder->buildTitle($obj);
        $this->activityObjectBuilder->buildVerb($obj);
        $this->activityObjectBuilder->buildId($obj);
        $this->activityObjectBuilder->buildService($obj);
        $this->activityObjectBuilder->buildStarred($obj);
        $this->activityObjectBuilder->buildPostLink($obj, $account);
        $this->activityObjectBuilder->buildIsLiked($obj);
        $this->activityObjectBuilder->buildIsCommented($obj);
        $this->activityObjectBuilder->buildIsFavorited($obj);
        $this->activityObjectBuilder->buildMainAccountName($account);
        $this->activityObjectBuilder->buildMainAccountID($account);
        $this->activityObjectBuilder->buildMainAccountColor($account);
    }   
}