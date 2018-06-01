<?php
declare(strict_types=1);

namespace Dragonbe\Test\Hibp;

use Dragonbe\Hibp\Hibp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class HibpTest extends TestCase
{
    /**
     * Testing that an exception is thrown when the HIBP service
     * is unreachable.
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
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

    public function testExceptionIsThrownWhenRateLimitIsReached()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * A provider that generates common used passwords in plain text
     *
     * @return array
     */
    public function pwnedCommonPasswordProvider(): array
    {
        return [
            ['password', 'pwned1_password.txt'],
            ['querty', 'pwned2_password.txt'],
            ['admin', 'pwned3_password.txt'],
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
     * Tests that a common password is found in HIBP service
     *
     * @param string $plainTextPassword
     * @param string $passwordFile
     *
     * @covers \DragonBe\Hibp\Hibp::__construct()
     * @covers \DragonBe\Hibp\Hibp::isPwnedPassword()
     *
     * @dataProvider pwnedCommonPasswordProvider
     */
    public function testCanFindPwnedPasswordInPlainText(string $plainTextPassword, string $passwordFile)
    {
        $client = $this->mockClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp = new Hibp($client);
        $resultSet = $hibp->isPwnedPassword($plainTextPassword);
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
     *
     * @dataProvider pwnedCommonPasswordProvider
     */
    public function testCanFindPwndPasswordAsSha1Hash(string $plainTextPassword, string $passwordFile)
    {
        $client = $this->mockClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp = new Hibp($client);
        $sha1Password = sha1($plainTextPassword);
        $resultSet = $hibp->isPwnedPassword($sha1Password, true);
        $this->assertTrue($resultSet);
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
     *
     * @dataProvider strongUniquePasswordProvider
     */
    public function testCanNotFindGoodPasswordInPlainText(string $strongPassword, string $passwordFile)
    {
        $client = $this->mockClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp = new Hibp($client);
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
     *
     * @dataProvider strongUniquePasswordProvider
     */
    public function testCanNotFindGoodPasswordAsSha1Hash(string $strongPassword, string $passwordFile)
    {
        $client = $this->mockClientResponse(__DIR__ . '/_files/' . $passwordFile);
        $hibp = new Hibp($client);
        $sha1Password = sha1($strongPassword);
        $resultSet = $hibp->isPwnedPassword($sha1Password, true);
        $this->assertFalse($resultSet);
    }

    private function mockClientResponse(string $serverResponseFile): Client
    {
        $statusCode = $this->getStreamStatusCode($serverResponseFile);
        $headers = $this->getStreamHeaders($serverResponseFile);
        $body = $this->getStreamBody($serverResponseFile);
        
        $mockHandler = new MockHandler([
           new Response($statusCode, $headers, $body),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        return $client;
    }

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

    private function getStreamRequest(string $serverResponseFile): string
    {
        $data = $this->getStream($serverResponseFile);
        return $data['request'];
    }

    private function getStreamStatusCode(string $serverResponseFile): int
    {
        $data = $this->getStream($serverResponseFile);
        return $data['statuscode'];
    }

    private function getStreamHeaders(string $serverResponseFile): array
    {
        $data = $this->getStream($serverResponseFile);
        return $data['headers'];
    }

    private function getStreamBody(string $serverResponseFile): string
    {
        $data = $this->getStream($serverResponseFile);
        return $data['body'];
    }
}
