<?php
include(dirname(__FILE__).'/function.php');

$api=@$_POST['API-Key'];
$url='http://api.ongkir.info/city/list';
$query=@$_POST['query'];
$type=@$_POST['type'];
$courier=@$_POST['courier'];
$format=@$_POST['format'];

$data=array(
	'API-Key'=>$api,
	'query'=>$query,
	'type'=>$type,
	'courier'=>$courier,
	'format'=>$format
);

if(strlen($query)>=3 && !empty($type) && !empty($courier) && !empty($format)){
	header('Content-type: application/json');
	echo post_to_url($url, $data);
}
?>