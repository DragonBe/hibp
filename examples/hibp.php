<?php

namespace Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;

require_once __DIR__ . '/vendor/autoload.php';

/*
 * This example shows how to quickly get started
 * to make a call to the Have I been pwned API service.
 */

/**
 * @var Hibp
 */
$hibp = HibpFactory::create();

echo 'Password "password": ' . ($hibp->isPwnedPassword('password') ? 'Pwned' : 'OK') . PHP_EOL;
echo 'Password "NVt3MpvQ": ' . ($hibp->isPwnedPassword('NVt3MpvQ') ? 'Pwned' : 'OK') . PHP_EOL;

