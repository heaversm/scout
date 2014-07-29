<?php
require_once 'include.php';
$app_id = '456342141134634';
$secret = '0ca8462dadf98a383cd3b6f04a24a790';
var_dump(FacebookWrapper::create()->getAppAccessToken($app_id, $secret));
Category::getInstance()->create('Featured');
Category::getInstance()->create('Almost');
?>