<?php

namespace Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RicardoFiorani\GuzzlePsr18Adapter\Client;

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * This example shows the usage of a PSR18 compliant
 * HTTP class to make a call to the Have I been pwned
 * API service.
 *
 * @see https://www.php-fig.org/psr/psr-18/
 */
/**
 * @var ClientInterface
 */
$client = new Client(HibpFactory::createConfig());

/**
 * @var RequestInterface
 */
$request = new Request('GET', '/');

/**
 * @var ResponseInterface
 */
$response = new Response();

/**
 * @var Hibp
 */
$hibp = new Hibp($client, $request, $response);

echo 'Password "password": ' . ($hibp->isPwnedPassword('password') ? 'Pwned' : 'OK') . PHP_EOL;
echo 'Password "NVt3MpvQ": ' . ($hibp->isPwnedPassword('NVt3MpvQ') ? 'Pwned' : 'OK') . PHP_EOL;
