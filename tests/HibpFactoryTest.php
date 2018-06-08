<?php
declare(strict_types=1);

namespace Dragonbe\Test\Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;
use PHPUnit\Framework\TestCase;

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
}
