<?php

require_once __DIR__ . '/vendor/autoload.php';

$hibp = \Dragonbe\Hibp\HibpFactory::create();
echo 'Password "password": ' . ($hibp->isPwnedPassword('password') ? 'Pwned' : 'OK') . PHP_EOL;
echo 'Password "NVt3MpvQ": ' . ($hibp->isPwnedPassword('NVt3MpvQ') ? 'Pwned' : 'OK') . PHP_EOL;

