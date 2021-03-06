<?php

/****************************************************************************
** Acilos app: https://github.com/omnibond/acilos
** Copyright (C) 2014 Omnibond Systems LLC. and/or its subsidiary(-ies).
** All rights reserved.
** Omnibond Systems - www.omnibond.com for Acilos.com
**
** This file defines server side group of function pertaining to findContact searches against users in elasticsearch
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

use \ElasticSearch\Client;
	
function getSuggestionsFunc($word){ 
	
	$index = "client";
	$host = "localhost";
	$port = "9200";
	
	$searchArr = array(
	    "facets" => array(
			"results" => array(
			    "terms" => array( 
					"field" => "data.searchIndex"
			    )
			)
	    ),
	    "query" => array(
			"match" => array(
				"data.searchIndex.autocomplete" => $word
			)
	    )
	);



	$es = Client::connection("http://$host:$port/$index/$index");
	
	$arr = $es->search($searchArr);
	
	return $arr;
	
}

?>
