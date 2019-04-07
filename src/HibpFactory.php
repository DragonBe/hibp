<?php
declare(strict_types=1);

namespace Dragonbe\Hibp;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use RicardoFiorani\GuzzlePsr18Adapter\Client;

class HibpFactory
{
    /**
     * Factor method that will immediately create the correct
     * GuzzleHttp client to use for HIBP.
     *
     * @param array $config GuzzleHttp\Client configuration settings.
     *
     * @return Hibp
     */
    public static function create(array $config = []): Hibp
    {
        return self::createRealClient($config);
    }

    /**
     * Factory method to create a basic configuration with the
     * option to provide your own settings to override default
     * configuration options.
     *
     * @param array $config
     * @return array
     */
    public static function createConfig(array $config = []): array
    {
        return array_replace_recursive([
            'base_uri' => Hibp::HIBP_API_URI,
            'timeout' => Hibp::HIBP_API_TIMEOUT,
            'headers' => [
                'User-Agent' => Hibp::HIBP_CLIENT_UA,
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ]
        ], $config);
    }

    /**
     * Creates a real HTTP client for using in your applications
     * and make calls to the outside world.
     *
     * @param array $config GuzzleHttp\Client configuration settings.
     *
     * @return Hibp
     */
    private static function createRealClient(array $config): Hibp
    {
        $client = new Client(self::createConfig($config));
        $request = new Request(
            'GET',
            Hibp::HIBP_API_URI,
            [
                'User-Agent' => Hibp::HIBP_CLIENT_UA,
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ]
        );
        $response = new Response();
        return new Hibp($client, $request, $response);
    }

    /**
     * Creates a fake HTTP client to use for unit testing
     * purposes.
     *
     * @param array $mockArray
     *
     * @return Hibp
     */
    public static function createTestClient(array $mockArray = []): Hibp
    {
        $mock = new MockHandler($mockArray);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $request = new Request(
            'GET',
            '/'
        );
        $response = new Response();
        return new Hibp($client, $request, $response);
    }
}
