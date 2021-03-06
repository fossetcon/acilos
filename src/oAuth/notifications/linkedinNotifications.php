<?php
	
/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines a notification poller 
** This is DEPRECATED
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
	
	function objectToArray($d){
		if(is_object($d)){
			$d = get_object_vars($d);
		}
		if(is_array($d)){
			return array_map(__FUNCTION__, $d);
		}
		else{
			return $d;
		}
	}
	
	function linkedInFetch($method, $resource, $token) {
		$params = array('oauth2_access_token' => $token,
			'format' => 'json',
		);

		// Need to use HTTPS
		$url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
		// Tell streams to make a (GET, POST, PUT, or DELETE) request
		$context = stream_context_create(
			array('http' => array(
				'method' => $method)
			)
		);

		// Hocus Pocus
		$response = file_get_contents($url, false, $context);

		// Native PHP object, please
		return json_decode($response);
	}
	
	$filename = "../linkedinUserCreds.txt";
	$file = file_get_contents($filename) or die("Cannot open the file: " . $filename);
	$obj = json_decode($file, true);
	
	//$feed = linkedInFetch('GET', '/v1/people/~/connections', $obj['access_token']);
	$feed = linkedInFetch('POST', '/v1/people/~/mailbox', $obj['access_token']);
	$feed = objectToArray($feed);
	print_r($feed);
	
?>	
	
	