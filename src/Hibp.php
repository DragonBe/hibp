<?php
declare(strict_types=1);

namespace Dragonbe\Hibp;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Hibp implements HibpInterface, \Countable
{
    const HIBP_API_URI = 'https://api.pwnedpasswords.com';
    const HIBP_API_TIMEOUT = 300;
    const HIBP_CLIENT_UA = 'DragonBe\Hibp-0.0.1RC1 Composer\1.6.4 PHP\7.2';
    const HIBP_CLIENT_ACCEPT = 'application/vnd.haveibeenpwned.v2+json';
    const HIBP_RANGE_LENGTH = 5;
    const HIBP_RANGE_BASE = 0;
    const HIBP_COUNT_BASE = 0;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var int
     */
    protected $count = self::HIBP_COUNT_BASE;

    /**
     * Hibp constructor.
     *
     * @param ClientInterface $client
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        ClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->client = $client;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function isPwnedPassword(string $password, bool $isShaHash = false): bool
    {
        if (! $isShaHash) {
            $password = sha1($password);
        }
        if (40 !== strlen($password) && $isShaHash) {
            throw new \InvalidArgumentException(
                'Password does not appear to be a SHA1 hashed password, please verify your input'
            );
        }
        $password = strtoupper($password);
        $range = $this->getHashRange($password);

        $request = new Request('GET', '/range/' . $range);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $connectException) {
            throw $this->exception(\RuntimeException::class, 'Cannot connect to HIBP API');
        }
        $resultStream = (string) $response->getBody();
        return $this->passwordInResponse($password, $resultStream);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Creates a hash range that will be send to HIBP API
     *
     * @param string $passwordHash
     * @return string
     */
    private function getHashRange(string $passwordHash): string
    {
        $range = substr($passwordHash, self::HIBP_RANGE_BASE, self::HIBP_RANGE_LENGTH);
        return $range;
    }

    /**
     * Checks if the password is in the response from HIBP
     *
     * @param string $password
     * @param string $resultStream
     * @return bool
     */
    private function passwordInResponse(string $password, string $resultStream): bool
    {
        $data = explode("\r\n", $resultStream);
        $totalCount = self::HIBP_COUNT_BASE;
        $hashes = array_filter($data, function ($value) use ($password, &$totalCount) {
            list($hash, $count) = explode(':', $value);
            if (0 === strcmp($hash, substr($password, self::HIBP_RANGE_LENGTH))) {
                $totalCount = (int) $count;
                return true;
            }
            return false;
        });
        if ([] === $hashes) {
            return false;
        }
        $this->count = $totalCount;
        return true;
    }

    /**
     * Helper method to create exceptions
     *
     * @param string $exceptionClass
     * @param string $message
     * @return mixed
     */
    private function exception(string $exceptionClass, string $message)
    {
        return new $exceptionClass($message);
    }
}
