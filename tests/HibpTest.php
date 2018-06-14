<?php
declare(strict_types=1);

namespace Dragonbe\Test\Hibp;

use Dragonbe\Hibp\Hibp;
use Dragonbe\Hibp\HibpFactory;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class HibpTest extends TestCase
{
    /**
     * Testing that we need to provide a client at construct or throw
     * a ArgumentCountError exception
     *
     * @covers \Dragonbe\Hibp\Hibp::__construct
     */
    public function testClassThrowsErrorWhenHttpClientIsNotProvided()
    {
        $this->expectException(\ArgumentCountError::class);
        $hibp = new Hibp();
        $this->fail('Expected error for missing HTTP client was not thrown');
    }

    /**
     * Testing that we need to provide a GuzzleHttp client at construct
     * or throw a TypeError exception
     *
     * @covers \Dragonbe\Hibp\Hibp::__construct
     */
    public function testClassThrowsTypeErrorWhenWrongArgumentIsProvided()
    {
        $foo = new \stdClass();
        $this->expectException(\TypeError::class);
        $hibp = new Hibp($foo);
        $this->fail('Expected error for wrong HTTP client was not thrown');
    }

    /**
     * Testing that an exception is thrown when the HIBP service
     * is unreachable.
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::exception()
     */
    public function testExceptionIsThrownWhenServiceNotAvailable()
    {
        $mockHandler = new MockHandler([
            new ConnectException("Error Communicating with Server", new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot connect to HIBP API');
        $hibp = new Hibp($client);
        $hibp->isPwnedPassword('foo');
        $this->fail('Expected exception was not thrown');
    }

    /**
     * Testing hitting the rate limit of HIBP API
     *
     * @covers \Dragonbe\Hibp\Hibp::__construct()
     * @covers \Dragonbe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::exception()
     */
    public function testExceptionIsThrownWhenRateLimitIsReached()
    {
        $passwordFile = 'hit_rate_limit.txt';
        $password = 'password';
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $this->expectException(\DomainException::class);
        $hibp->isPwnedPassword($password);
        $this->fail('Expected exception for hit rate was not triggered');
    }

    /**
     * Testing hitting a 404 when looking up a password
     *
     * @covers \Dragonbe\Hibp\Hibp::__construct()
     * @covers \Dragonbe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::exception()
     */
    public function testExceptionIsThrownWhenApiNotFound()
    {
        $passwordFile = 'not_found.txt';
        $password = 'password';
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $this->expectException(\DomainException::class);
        $hibp->isPwnedPassword($password);
        $this->fail('Expected exception for hit rate was not triggered');
    }

    /**
     * A provider that generates common used passwords in plain text
     *
     * @return array
     */
    public function pwnedCommonPasswordProvider(): array
    {
        return [
            ['password', 'pwned1_password.txt', 3303003],
            ['querty', 'pwned2_password.txt', 962],
            ['admin', 'pwned3_password.txt', 41812],
        ];
    }

    /**
     * A provider that generates randomly generated passwords in
     * plain text
     *
     * @return array
     */
    public function strongUniquePasswordProvider(): array
    {
        return [
            ['kjxkL[GkevdAXWiUXUarJgwFtdrcYiLfmeWKGcDwdwTNZHNTE8uHjAuYXNckZaMK', 'new1_password.txt'],
            ['revelry castor whipsaw thistle', 'new2_password.txt'],
            ['B8V6EDFpyz$p]fq3T9vJ', 'new3_password.txt'],
        ];
    }

    /**
     * Testing that we receive a password hash range of type string
     *
     * @throws \ReflectionException
     *
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     */
    public function testPasswordHashRangeReturnsString()
    {
        $getHashRange = new \ReflectionMethod(Hibp::class, 'getHashRange');
        $getHashRange->setAccessible(true);

        $client = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();

        $password = 'foobar';
        $hash = sha1($password);
        $range = $getHashRange->invokeArgs(new Hibp($client), [$hash]);
        $this->assertTrue(is_string($range));
        $this->assertSame(Hibp::HIBP_RANGE_LENGTH, strlen($range));
        $this->assertSame(
            substr($hash, 0, Hibp::HIBP_RANGE_LENGTH),
            $range
        );
    }

    /**
     * Tests that a common password is found in HIBP service
     *
     * @param string $plainTextPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     *
     * @dataProvider pwnedCommonPasswordProvider
     */
    public function testCanFindPwnedPasswordInPlainText(string $plainTextPassword, string $passwordFile)
    {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $resultSet = $hibp->isPwnedPassword($plainTextPassword);
        $this->assertTrue($resultSet);
    }

    /**
     * Testing to see that we can count how many times a password was used
     *
     * @param Hibp $hibp
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     * @covers \Dragonbe\Hibp\Hibp::count()
     *
     * @dataProvider pwnedCommonPasswordProvider
     */
    public function testWhenBadPasswordIsFoundWeCanCountHowManyTimesItWasUsed(
        string $plainTextPassword,
        string $passwordFile,
        int $count
    ) {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp->isPwnedPassword($plainTextPassword);
        $this->assertCount($count, $hibp);
    }

    /**
     * Tests that a common password is found in HIBP service
     *
     * @param string $plainTextPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     *
     * @dataProvider pwnedCommonPasswordProvider
     */
    public function testCanFindPwndPasswordAsSha1Hash(string $plainTextPassword, string $passwordFile)
    {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $sha1Password = sha1($plainTextPassword);
        $resultSet = $hibp->isPwnedPassword($sha1Password, true);
        $this->assertTrue($resultSet);
    }

    /**
     * Tests that a common password is found in HIBP service
     *
     * @param string $plainTextPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     * @covers \Dragonbe\Hibp\Hibp::count()
     *
     * @dataProvider strongUniquePasswordProvider
     */
    public function testCountIsZeroForStrongPasswords(string $plainTextPassword, string $passwordFile)
    {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp->isPwnedPassword($plainTextPassword);
        $this->assertCount(0, $hibp);
    }

    /**
     * Tests that a strong password in plain text is not found
     * in HIBP service
     *
     * @param string $strongPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     *
     * @dataProvider strongUniquePasswordProvider
     */
    public function testCanNotFindGoodPasswordInPlainText(string $strongPassword, string $passwordFile)
    {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $resultSet = $hibp->isPwnedPassword($strongPassword);
        $this->assertFalse($resultSet);
    }

    /**
     * Tests that a strong password as SHA1 hash is not found
     * in HIBP service
     *
     * @param string $strongPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     * @covers \Dragonbe\Hibp\Hibp::getHashRange()
     * @covers \Dragonbe\Hibp\Hibp::passwordInResponse()
     *
     * @dataProvider strongUniquePasswordProvider
     */
    public function testCanNotFindGoodPasswordAsSha1Hash(string $strongPassword, string $passwordFile)
    {
        $hibp = $this->createHibpWithMockedClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $sha1Password = sha1($strongPassword);
        $resultSet = $hibp->isPwnedPassword($sha1Password, true);
        $this->assertFalse($resultSet);
    }

    /**
     * Creates a mock response for GuzzleHttp Client and creates
     * a Hibp instance with these mocked responses.
     *
     * @param string $serverResponseFile
     * @return Hibp
     */
    private function createHibpWithMockedClientResponse(string $serverResponseFile): Hibp
    {
        $statusCode = $this->getStreamStatusCode($serverResponseFile);
        $headers = $this->getStreamHeaders($serverResponseFile);
        $body = $this->getStreamBody($serverResponseFile);

        return HibpFactory::createTestClient([
            new Response($statusCode, $headers, $body),
        ]);
    }

    /**
     * Generic method to process response fixtures
     *
     * @param string $serverResponseFile
     * @return array
     */
    private function getStream(string $serverResponseFile): array
    {
        $request = [];
        $headers = [];
        $body = [];
        $breakCnt = 0;

        $stream = file_get_contents($serverResponseFile);
        $stream = str_replace("\r\n", "\n", $stream);
        $data = explode("\n", $stream);

        foreach ($data as $line) {
            $cleanLine = rtrim($line);
            if (0 === strcmp('', $cleanLine)) {
                $breakCnt++;
            } elseif (1 > $breakCnt) {
                $request[] = $cleanLine;
            } elseif (2 > $breakCnt) {
                $headers[] = $cleanLine;
            } else {
                $body[] = $cleanLine;
            }
        }

        $statusCodeString = $headers[0];
        $statusCodeArray = explode(' ', $statusCodeString);
        $statusCode = (int) $statusCodeArray[1];

        $response = [
            'request' => $request[0],
            'statuscode' => $statusCode,
            'headers' => $headers,
            'body' => implode("\r\n", $body),
        ];
        return $response;
    }

    /**
     * Returns the request stream from a fixture
     *
     * @param string $serverResponseFile
     * @return string
     */
    private function getStreamRequest(string $serverResponseFile): string
    {
        $data = $this->getStream($serverResponseFile);
        return $data['request'];
    }

    /**
     * Returns the status code form a fixture
     *
     * @param string $serverResponseFile
     * @return int
     */
    private function getStreamStatusCode(string $serverResponseFile): int
    {
        $data = $this->getStream($serverResponseFile);
        return $data['statuscode'];
    }

    /**
     * Returns headers from a fixture
     *
     * @param string $serverResponseFile
     * @return array
     */
    private function getStreamHeaders(string $serverResponseFile): array
    {
        $data = $this->getStream($serverResponseFile);
        return $data['headers'];
    }

    /**
     * Returns the body of a fixture
     *
     * @param string $serverResponseFile
     * @return string
     */
    private function getStreamBody(string $serverResponseFile): string
    {
        $data = $this->getStream($serverResponseFile);
        return $data['body'];
    }
}
