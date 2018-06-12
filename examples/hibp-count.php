<?php

require_once __DIR__ . '/vendor/autoload.php';

$hibp = \Dragonbe\Hibp\HibpFactory::create();
$passwords = ['password', 'NVt3MpvQ'];
foreach ($passwords as $password) {
    $found = $hibp->isPwnedPassword($password);
    $count = count($hibp);

    echo sprintf(
        'Password "%s": %s',
        $password,
        $found ? ('Pwned (' . $count . ' times)') : 'OK'
    ) . PHP_EOL;
}

