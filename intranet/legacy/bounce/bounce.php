<?php

use ApiBundle\Model\User;

include_once("common.inc.php");

global $kernel;
$tokenStorage = $kernel->getContainer()->get('security.token_storage.legacy');
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

/** @var User|null $userConnected */
$userConnected = $tokenStorage->getToken()->getUser();

if (null === $userConnected) {
    $portal = null;
}

$token = $user->generateToken($userConnected->token);
if($token===FALSE){
    $portal = NULL;
}

switch($portal){
case 'dms':
    $URL = "$DMS_URL/index.php?m[0]=bounce&token=$token";
break;
default:
    $URL = "$INTRANET_URL";
break; 
}

header("Location: $URL");
?>