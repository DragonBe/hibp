<?php
declare(strict_types=1);

namespace Dragonbe\Hibp;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class HibpFactory
{
    /**
     * Factor method that will immediately create the correct
     * GuzzleHttp client to use for HIBP.
     *
     * @return Hibp
     */
    public static function create(): Hibp
    {
        return self::createRealClient();
    }

    /**
     * Creates a real HTTP client for using in your applications
     * and make calls to the outside world.
     *
     * @return Hibp
     */
    private static function createRealClient(): Hibp
    {
        $client = new Client([
            'base_uri' => Hibp::HIBP_API_URI,
            'timeout' => Hibp::HIBP_API_TIMEOUT,
            'headers' => [
                'User-Agent' => Hibp::HIBP_CLIENT_UA,
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ]
        ]);
        return new Hibp($client);
    }

    /**
     * Creates a fake HTTP client to use for unit testing
     * purposes.
     *
     * @param $mockArray array
     * @return Hibp
     */
    public static function createTestClient(array $mockArray = []): Hibp
    {
        $mock = new MockHandler($mockArray);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        return new Hibp($client);
    }
}
