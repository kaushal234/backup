<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Auth\ApiAuthenticator;
use App\Client\ApiClient;
use App\Pi\User;

$DEFAULT_MENU = '';

/** @var ApiClient $client */
if (!$client->isAuthenticated()) {
    $_SESSION['pi_user'] = '';
    $_SESSION['pi_sn'] = '';
    $_SESSION['pi_cprj'] = '';
    $_SESSION['pi_pdno'] = '';
    $_SESSION['pi_family'] = '';
    $_SESSION['pi_snid'] = '';
    $_SESSION['pi_opno'] = '';
    $_SESSION['pi_tano_dsca'] = '';

    $body = include($PATH.'/login.tpl.php');

    $pi_user_input = trim($_POST['pi_user_input'] ?? '');
    $_SESSION['pi_user_input'] = $pi_user_input;
    $pi_pwd_input = $_POST['pi_pwd_input'] ?? '';
    if ($pi_user_input !== '') {
        $authenticationClient = new ApiClient($_ENV['API_URL']);
        $authenticationClient->setToken($_ENV['API_AUTHORIZED_APP_TOKEN']);
        $apiAuthenticator = new ApiAuthenticator($authenticationClient);
        try {
            $userFromAPI = ['username' => $pi_user_input];
            if (is_numeric($pi_user_input)) {
                $userFromAPI = $apiAuthenticator->getUserFromAPI((int) $pi_user_input);
            }

            $userHasToken = false;
            if (($userFromAPI['username'] ?? null) && $pi_pwd_input){
                /** @var ApiClient $client */
                $apiAuthenticator = new ApiAuthenticator($client);
                $apiAuthenticator->authenticate($userFromAPI['username'],  mb_convert_encoding(stripslashes($pi_pwd_input), 'UTF-8'));
                $userHasToken = true;
                $_SESSION['pi_user'] = $client->getUser()->get('lastname')." ".$client->getUser()->get('firstname');
                $_SESSION['pi_user_id'] = $client->getUser()->get('legacyId');
                $_SESSION['pi_user_new_id'] = $client->getUser()->get('id');
                $_SESSION['pi_user_ko'] = '';
                header("location:/shop/autoselect.php");
            }
        } catch (\Exception $exception) {
            $_SESSION['pi_user_ko'] = _('User or password incorrect');
            error_log(sprintf('PIO Auth to API error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            header("location:/shop/autoselect.php?m[0]=login");
        }
    }
}

if ('logout' === ($m[1] ?? null)) {
    $_SESSION['pi_user'] = '';
    $_SESSION['pi_sn'] = '';
    $_SESSION['pi_cprj'] = '';
    $_SESSION['pi_pdno'] = '';
    $_SESSION['pi_family'] = '';
    $_SESSION['pi_snid'] = '';
    $_SESSION['pi_opno'] = '';
    $_SESSION['pi_tano_dsca'] = '';
    setcookie('_jwt', '',  time()-3600 , '/' , '', false, true);
    unset($_SESSION['pi_user_id'], $_SESSION['pi_er_input'], $_COOKIE['_jwt']);
    header('location:/shop/autoselect.php');
    exit;
}
