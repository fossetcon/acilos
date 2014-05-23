<?php

$str = file_get_contents('prods.csv');

$Data = str_getcsv($str, "\n");
$finalArr = array();
foreach($Data as &$Row){
	$Row = str_getcsv($Row, ",");
	$temp = array(
		"product" => $Row[0],
		"region" => $Row[1],
		"country" => $Row[2],
		"countryCode" => '',
		"state" => $Row[3],
		"zipcode" => $Row[4],
		"geo" => $Row[5],
		"shortprod" => $Row[6],
		'uuid' => uniqid()
	);
	array_push($finalArr, $temp);
}
print_r(count($finalArr));
file_put_contents("prods.json", json_encode($finalArr));

?>