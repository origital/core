<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

class Response
{
    private $statusCode;

    /**
     * @var Header[]
     */
    private $headers = [];

    /**
     * @var Cookie[]
     */
    private $cookies = [];

    /**
     * @var Body
     */
    private $body;
    private $attributes;

    public function __construct(
        int $statusCode = 200,
        $body = 'php://temp',
        array $headers = [],
        array $cookies = []
    ) {
        $this->setStatusCode($statusCode)
             ->setBody($body)
             ->setHeaders($headers)
             ->setCookies($cookies);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): Response
    {
        foreach ($headers as $header) {
            $this->setHeader($header);
        }
        return $this;
    }

    public function getHeader(string $name): ?Header
    {
        $name = Header::normalizeName($name);
        return $this->headers[$name] ?? null;
    }

    public function setHeader(Header $header): Response
    {
        $this->headers[$header->getNormalizedName()] = $header;
        return $this;
    }

    public function removeHeader(string $name):Response
    {
        $name = Header::normalizeName($name);
        unset($this->headers[$name]);
        return $this;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function setCookies(array $cookies): Response
    {
        foreach ($cookies as $cookie) {
            $this->setCookie($cookie);
        }
        return $this;
    }

    public function getCookie(string $name): ?Cookie
    {
        return $this->cookies[$name] ?? null;
    }

    public function setCookie(Cookie $cookie): Response
    {
        $this->cookies[$cookie->getName()] = $cookie;
        return $this;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function setBody($body): Response
    {
        $body = Body::assertInstance($body, 'wb+');
        $this->body = $body;
        return $this;
    }

    public function write($data): Response
    {
        $this->body->write((string)$data);
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes): Response
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function setAttribute(string $name, $value): Response
    {
        $this->attributes[$name] = $value;
        return $this;
    }
}
