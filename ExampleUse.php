<?php

require('RealFollowersPlus.php');

$username = "USERNAMEIGMU";

$a = new RealFollowersPlus($username);

$auth = $a->auth();
echo "Current Coin : " . $auth['object']['cash']['deposit']  . "\n";
$a->miningMe();
echo "Coin after mining : " . $a->getCoin();
