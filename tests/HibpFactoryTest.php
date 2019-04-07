<?php
declare(strict_types=1);

namespace Dragonbe\Test\Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

class HibpFactoryTest extends TestCase
{
    /**
     * Testing that the Factory actually generates a Hibp object
     * ready to use.
     *
     * @covers \Dragonbe\Hibp\HibpFactory::create()
     * @covers \Dragonbe\Hibp\HibpFactory::createRealClient()
     * @group IntegrationTest
     */
    public function testFactoryGeneratesHibpObjectReadyToUse()
    {
        $hibp = HibpFactory::create();
        $this->assertInstanceOf(Hibp::class, $hibp);
    }

    /**
     * Testing that our Factory can provide an instance of Hibp
     * for testing purposes.
     *
     * @covers \Dragonbe\Hibp\HibpFactory::createTestClient()
     */
    public function testFactoryGeneratesHibpObjectForTesting()
    {
        $hibp = HibpFactory::createTestClient();
        $this->assertInstanceOf(Hibp::class, $hibp);
    }

    /**
     * Testing that we can generate our default configuration
     * with expected results.
     *
     * @covers \Dragonbe\Hibp\HibpFactory::createConfig()
     */
    public function testFactoryCreationOfDefaultConfig()
    {
        $hibpConfig = HibpFactory::createConfig();
        $expectedConfig = [
            'base_uri' => Hibp::HIBP_API_URI,
            'timeout' => Hibp::HIBP_API_TIMEOUT,
            'headers' => [
                'User-Agent' => Hibp::HIBP_CLIENT_UA,
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ]
        ];
        $this->assertSame($expectedConfig, $hibpConfig);
    }

    /**
     * Testing that we can generate our custom configuration
     * when providing different configuration settings
     *
     * @covers \Dragonbe\Hibp\HibpFactory::createConfig()
     */
    public function testFactoryCreationWithOverridingConfiguration()
    {
        $hibpConfig = HibpFactory::createConfig([
            'timeout' => 250,
            'headers' => [
                'User-Agent' => 'phpunit/7.3.5',
            ],
        ]);
        $expectedConfig = [
            'base_uri' => Hibp::HIBP_API_URI,
            'timeout' => 250,
            'headers' => [
                'User-Agent' => 'phpunit/7.3.5',
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ]
        ];
        $this->assertSame($expectedConfig, $hibpConfig);
    }

    /**
     * Testing that we can add additional configuration settings
     * by just providing the "new" configuration options.
     *
     * @covers \Dragonbe\Hibp\HibpFactory::createConfig()
     */
    public function testFactoryConfigAddsNotDefinedConfigurationOptions()
    {
        $hibpConfig = HibpFactory::createConfig(['foo' => 'bar']);
        $expectedConfig = [
            'base_uri' => Hibp::HIBP_API_URI,
            'timeout' => Hibp::HIBP_API_TIMEOUT,
            'headers' => [
                'User-Agent' => Hibp::HIBP_CLIENT_UA,
                'Accept' => Hibp::HIBP_CLIENT_ACCEPT,
            ],
            'foo' => 'bar',
        ];
        $this->assertSame($expectedConfig, $hibpConfig);
    }
}
