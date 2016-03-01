<?php
$redis = new Redis();
$redis->connect("localhost", 6379);

$setRedisCache = function($key, $value) use($redis) {
	return $redis->set($key, $value);
};

$getRedisCache = function($key) use($redis) {
	return $redis->
};

$curlTool = function($url, $post = false, $postData = []) use($setRedisCache) {

};

$loginTool = function() use($curlTool) {

};

$logoutTool = function() use($curlTool) {};
