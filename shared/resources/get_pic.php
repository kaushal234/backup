<?php
include_once("common.inc.php");

switch($m){
case 'user':
    if(isset($id,$lastname) && !empty($id) && is_numeric($id))
	{
		$user = new tldUser($id);
		if(strtolower($user->getLastname()) == strtolower($lastname)){
			echo $user->outPhoto();
		}
	}
	else
	{
		echo <<<EOF
<p class="alert">Not enough parameters specified</p>
EOF;
	}
break;
}
