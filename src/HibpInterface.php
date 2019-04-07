<?php


namespace Dragonbe\Hibp;

interface HibpInterface
{
    /**
     * Checks a password against HIBP service and checks
     * if the password is matching in the resultset
     *
     * @param string $password
     * @param bool $isShaHash
     * @return bool
     */
    public function isPwnedPassword(string $password, bool $isShaHash = false): bool;
}
