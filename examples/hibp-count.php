<?php

namespace Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * This example shows how you can validate multiple passwords
 * to the Have I been pwned API service.
 *
 * It also shows how you can retrieve the amount of times a
 * given password was found in breaches.
 */

/**
 * @var Hibp
 */
$hibp = HibpFactory::create();

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

