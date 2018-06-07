<?php
declare(strict_types=1);

namespace Dragonbe\Hibp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

class Hibp
{
    const HIBP_API_URI = 'https://api.pwnedpasswords.com';
    const HIBP_API_TIMEOUT = 300;
    const HIBP_CLIENT_UA = 'DragonBe\Hibp-0.0.1RC1 Composer\1.6.4 PHP\7.2';
    const HIBP_CLIENT_ACCEPT = 'application/vnd.haveibeenpwned.v2+json';
    const HIBP_RANGE_LENGTH = 5;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Hibp constructor.
     *
     * @param Client $client
     */
    public function __construct(?Client $client = null)
    {
        if (null !== $client) {
            $this->client = $client;
        } else {
            $this->client = $this->createClient();
        }
    }

    /**
     * Checks a password against HIBP service and checks
     * if the password is matching in the resultset
     *
     * @param string $password
     * @param bool $isShaHash
     * @return bool
     */
    public function isPwnedPassword(string $password, $isShaHash = false): bool
    {
        if (! $isShaHash) {
            $password = sha1($password);
        }
        $password = strtoupper($password);
        $range = $this->getHashRange($password);
        try {
            $response = $this->client->get('/range/' . $range);
        } catch (ConnectException $connectException) {
            throw $this->exception(\RuntimeException::class, 'Cannot connect to HIBP API');
        } catch (ClientException $clientException) {
            throw $this->exception(\DomainException::class, $clientException->getMessage());
        }
        $resultStream = (string) $response->getBody();
        return $this->passwordInResponse($password, $resultStream);
    }

    /**
     * Creates a hash range that will be send to HIBP API
     *
     * @param string $passwordHash
     * @return string
     */
    private function getHashRange(string $passwordHash): string
    {
        $range = substr($passwordHash, 0, self::HIBP_RANGE_LENGTH);
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
        $hashes = array_filter($data, function ($value) use ($password) {
            list($hash, $count) = explode(':', $value);
            return (0 === strcmp($hash, substr($password, 5)));
        });
        if ([] === $hashes) {
            return false;
        }
        return true;
    }

    /**
     * Creates a Guzzle HTTP client consuming the
     * HIBP API
     *
     * @return Client
     */
    private function createClient(): Client
    {
        $client = new Client([
            'base_uri' => self::HIBP_API_URI,
            'timeout' => self::HIBP_API_TIMEOUT,
            'headers' => [
                'User-Agent' => self::HIBP_CLIENT_UA,
                'Accept' => self::HIBP_CLIENT_ACCEPT,
            ]
        ]);
        return $client;
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
