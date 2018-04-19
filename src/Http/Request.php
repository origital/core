<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

class Request
{
    private $method;
    private $uri;
    private $headers = [];

    /**
     * @var Body
     */
    private $body;
    private $files;
    private $params;
    private $cookies;
    private $attributes;

    static private $contextRequest = null;

    static public function createFromContext()
    {
        if (self::$contextRequest === null) {
            $method = strtolower($_SERVER['REQUEST_METHOD'] ?? '');
            $requestHeaders = [];
            if (function_exists('\getallheaders')) {
                $requestHeaders = \getallheaders();
                if ($requestHeaders === false) {
                    $requestHeaders = [];
                }
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $requestHeaders[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
            }
            $headers = [];
            foreach ($requestHeaders as $name => $value) {
                $header = new Header($name, $value);
                $headers[$header->getNormalizedName()] = $header;
            }
            $uri = new Uri($_SERVER['REQUEST_URI'] ?? '');
            $uri->setScheme(!empty($_SERVER['HTTPS']) ? 'https' : 'http');
            $uri->setQueryParams($_GET);
            if (empty($uri->getHost()) && isset($headers['Host'])) {
                $parts = explode(':', $headers['Host']->getValue() ?? '');
                $host = $parts[0];
                $port = $parts[1] ?? null;
                $uri->setHost($host)
                    ->setPort($port);
            }
            $body = 'php://input';
            $request = new Request($method, $uri, $headers, $body);
            $request->setParams($_POST)
                    ->setCookies($_COOKIE)
                    ->setFiles($_FILES);
            self::$contextRequest = $request;
        }
        return clone self::$contextRequest;
    }

    public function __construct(string $method, Uri $uri, array $headers = [], $body = 'php://input')
    {
        $this->setMethod($method)
             ->setUri($uri)
             ->setHeaders($headers)
             ->setBody($body);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod($method): Request
    {
        $this->method = $method;
        return $this;
    }

    public function getUri(): Uri
    {
        return $this->uri;
    }

    public function setUri(Uri $uri): Request
    {
        $this->uri = $uri;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): Request
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

    public function setHeader(Header $header): Request
    {
        $this->headers[$header->getNormalizedName()] = $header;
        return $this;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function setBody($body): Request
    {
        $body = Body::assertInstance($body, 'r');
        $this->body = $body;
        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFile(string $name, $default = null)
    {
        return $this->files[$name] ?? $default;
    }

    public function setFiles($files): Request
    {
        $this->files = $files;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    public function setParams($params): Request
    {
        $this->params = $params;
        return $this;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getCookieValue(string $name, $default = null)
    {
        return $this->cookies[$name] ?? $default;
    }

    public function setCookies(array $cookies): Request
    {
        $this->cookies = $cookies;
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function setAttributes($attributes): Request
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setAttribute(string $name, $value): Request
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function addAttributes(array $attributes): Request
    {
        foreach ($attributes as $name => $value) {
            $this->attributes[$name] = $value;
        }
        return $this;
    }
}
