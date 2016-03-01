<?php

error_reporting(E_ALL);
$userAgent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36";
$redis = new Redis();
$redis->connect("localhost", 6379);

$setRedisCache = function($key, $value) use($redis) {
    return $redis->hSet("cookies", $key, $value);
};

$delRedisCache = function($key) use($redis) {
    return $redis->hDel("cookies", $key);
};

$getRedisCache = function($key) use($redis) {
    return $redis->hGet("cookies", $key);
};

$setCookie = function($username, $cookie) use($setRedisCache) {
    return $setRedisCache($username, $cookie);
};

$getCookie = function($username) use($getRedisCache) {
    return $getRedisCache($username);
};

$delCookie = function($username) use($delRedisCache) {
    return $delRedisCache($username);
};

$curlTool = function($url, $username, $setCookie = true, $post = false, $postData = []) use($setCookie, $getCookie, $userAgent) {
    sleep(1);
    $tmpfile = "/tmp/" . rand(1, 10000000) . ".cookie";
    $cookie = $getCookie($username);
    file_put_contents($tmpfile, $cookie);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

    curl_setopt($ch, CURLOPT_REFERER, "http://jiaoyi.sina.com.cn/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        $postData = http_build_query($postData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        var_dump($postData);
        var_dump(strlen($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: */*", "Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($postData)]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: */*", "Content-Type: application/x-www-form-urlencoded"]);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
//    if ($setCookie) {
//        curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
//    } else {
//        curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
//    }
    $data = curl_exec($ch);
    var_dump(curl_getinfo($ch));

    curl_close($ch);
    $cookie = file_get_contents($tmpfile);
    unlink($tmpfile);
    var_dump($data);
    $setCookie($username, $cookie);
    return $data;
};

$loginTool = function($username, $password) use($curlTool) {
    $curlTool("http://jiaoyi.sina.com.cn/jy/", $username);
    $preLoginUrl = "http://login.sina.com.cn/sso/prelogin.php?entry=finance&client=ssologin.js(v1.4.18)&su=" . base64_encode(urlencode($username)) . "&_=" . time();
    $preLoginData = $curlTool($preLoginUrl, $username);
    $preLoginData = json_decode($preLoginData);
    if (!empty($preLoginData->retcode)) {
        throw new Exception("prelogin url failed, with retcode error number {$preLoginData->retcode}.");
    }

    $loginUrl = "https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.18)&_=" . rand();
    $sp = "";
    openssl_public_encrypt($preLoginData->servertime + "\t" + $preLoginData->nonce + "\n" + $password, $sp, $preLoginData->pubkey);
    $inArr = [
        "entry" => "finance",
        "gateway" => "1",
        "from" => "",
        "savestate" => 30,
        "useticket" => "0",
        "pagerefer" => "",
        "vsnf" => "1",
        "su" => base64_encode(urlencode($username)),
        "service" => "sso",
        "servertime" => $preLoginData->servertime,
        "nonce" => $preLoginData->nonce,
        "pwencode" => "rsa2",
        "rsakv" => $preLoginData->rsakv,
        "sp" => bin2hex($sp),
        "sr" => "1920*1080",
        "cdult" => 3,
        "domain" => "sina.com.cn",
        "returntype" => "TEXT",
        "prelt" => 101,
        "encoding" => "UTF-8",
    ];
    var_dump($inArr);
    $loginResult = $curlTool($loginUrl, $username, true, true, $inArr);
    var_dump(json_decode($loginResult));
//    $filterResult = function() use($loginResult) {
//        $ret = [];
//        $data = [];
//        preg_match_all("/<input\ type\=\'hidden\'\ name\=\'[a-z]*?\'\ value\=\'[a-z0-9A-Z\@\.]*?\'/", $loginResult, $data);
//        foreach($data[0] as $v) {
//            $tmp = preg_replace("/<input\ type\=\'hidden\'\ name\=\'[a-z]*?\'\ value\=\'[a-z0-9A-Z\@\.]*?\'/", "$1,$2", $v);
//            $tmp = explode(",", $tmp);
//            $ret[$tmp[0]] = $tmp[1];
//        }
//        return $ret;
//    };
//    $loginResult = $filterResult();
//    $loginResult = $curlTool("https://login.sina.com.cn/signup/signin.php", $username, true, true, $loginResult);
    return $loginResult;
};

$logoutTool = function($username) use($curlTool) {
    $logoutUrl = "";
};

file_put_contents("/tmp/loginfasb", $ret);
