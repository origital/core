<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

use Origital\Http\Exception\CookieException;

class Cookie
{
    private $name;
    private $value;
    private $expires;
    private $path;
    private $domain;
    private $secure   = false;
    private $httpOnly = false;

    public function __construct(
        string $name,
        string $value   = null,
               $expires = null,
        string $path    = null,
        bool  $domain   = false,
        bool  $httpOnly = false
    ) {
        $this->setName($name)
             ->setValue($value)
             ->setExpires($expires)
             ->setPath($path)
             ->setDomain($domain)
             ->setHttpOnly($httpOnly);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $cookie = $this->name . '=' . $this->value;
        if ($this->expires !== null) {
            $cookie .= '; Expires=' . $this->expires;
        }
        if ($this->path !== null) {
            $cookie .= '; Path=' . $this->path;
        }
        if ($this->domain !== null) {
            $cookie .= '; Domain=' . $this->domain;
        }
        if ($this->secure === true) {
            $cookie .= '; Secure';
        }
        if ($this->httpOnly === true) {
            $cookie .= '; HttpOnly';
        }
        return $cookie;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Cookie
    {
        $this->name = (string)$name;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(string $value = null): Cookie
    {
        $this->value = (string)$value;
        return $this;
    }

    public function getExpires(): ?string
    {
        return $this->expires;
    }

    public function setExpires($expires = null): Cookie
    {
        if ($expires === null) {
            $this->expires = null;
            return $this;
        }
        if (!($expires instanceof \DateTime)) {
            if (is_int($expires)) {
                $timestamp = $expires;
                $expires = new \DateTime();
                $expires->setTimestamp($timestamp);
            } else {
                try {
                    $expires = new \DateTime($expires);
                } catch (\Exception $e) {
                    throw new CookieException('Invalid date format');
                }
            }
        }
        $this->expires = $expires->format(\DateTime::RFC1123);
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path = null): Cookie
    {
        $this->path = $path;
        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain = null): Cookie
    {
        $this->domain = $domain;
        return $this;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function setSecure(bool $secure): Cookie
    {
        $this->secure = $secure;
        return $this;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function setHttpOnly(bool $httpOnly): Cookie
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }
}
