<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

use Origital\Http\Exception\UriException;

class Uri
{
    protected $schemes = [
        'http'  =>  '80',
        'https' => '443',
    ];

    private $scheme;
    private $username;
    private $password;
    private $host;
    private $port;
    private $path;
    private $query;
    private $fragment;

    private $uriString;
    private $userInfo;
    private $authority;

    private $queryParams = [];

    public function __construct($uri = null)
    {
        if (is_string($uri)) {
            $this->parse($uri);
        } else if (is_array($uri)) {
            $this->setComponents($uri);
        } else if ($uri !== null) {
            throw new UriException('Invalid URI; `string` or `array` expected');
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->uriString !== null) {
            return $this->uriString;
        }
        $this->uriString = $this->createUriString(
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(),
            $this->getQuery(),
            $this->getFragment()
        );
        return $this->uriString;
    }

    public function toArray(): array
    {
        $array = [
            'scheme'        => $this->scheme,
            'username'      => $this->username,
            'user'          => $this->username,
            'password'      => $this->password,
            'pass'          => $this->password,
            'userInfo'      => $this->getUserInfo(),
            'host'          => $this->host,
            'port'          => $this->port,
            'portNumber'    => $this->getPortNumber(),
            'authorityPort' => $this->getAuthorityPort(),
            'authority'     => $this->getAuthority(),
            'path'          => $this->path,
            'query'         => $this->query,
            'queryParams'   => $this->queryParams,
            'fragment'      => $this->fragment,
        ];
        return $array;
    }

    public function parse(string $uriString): Uri
    {
        $parts = parse_url($uriString);
        if ($parts === false) {
            throw new UriException('Invalid URI format');
        }
        $this->setScheme  ($parts['scheme']   ?? '')
             ->setUsername($parts['user']     ?? '')
             ->setPassword($parts['pass']     ?? '')
             ->setHost    ($parts['host']     ?? '')
             ->setPort    ($parts['port']     ?? '')
             ->setPath    ($parts['path']     ?? '')
             ->setQuery   ($parts['query']    ?? '')
             ->setFragment($parts['fragment'] ?? '');
        return $this;
    }

    public function setComponents(array $components): Uri
    {
        $allowed = [
            'scheme',
            'username',
            'password',
            'host',
            'port',
            'path',
            'query',
            'queryParams',
            'fragment',
        ];
        foreach ($components as $component => $value) {
            if (!in_array($component, $allowed)) {
                throw new UriException('Invalid URI; there is no component called `' . $component . '`');
            }
            call_user_func([$this, 'set' . ucfirst($component)], $value);
        }
        return $this;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function setScheme($scheme): Uri
    {
        $this->uriString = null;
        $this->authority = null;
        $this->scheme    = (string)$scheme;
        return $this;
    }

    public function getAuthority(): string
    {
        if ($this->authority !== null) {
            return $this->authority;
        }
        $userInfo = $this->getUserInfo();
        $port     = $this->getAuthorityPort();
        $this->authority = $userInfo . (!empty($userInfo) ? '@' : '')
            . $this->host
            . (!empty($port) ? ':' : '') . $port;
        return $this->authority;
    }

    public function getAuthorityPort(): string
    {
        $showPort = !empty($this->port)
            && (empty($this->scheme)
                || isset($this->schemes[$this->scheme]) && $this->schemes[$this->scheme] !== $this->port);
        return $showPort ? $this->port : '';
    }

    public function getUserInfo(): string
    {
        if ($this->userInfo !== null) {
            return $this->userInfo;
        }
        if (empty($this->username)) {
            return '';
        }
        $this->userInfo = $this->username . (!empty($this->password) ? ':' . $this->password : '');
        return $this->userInfo;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername($username): Uri
    {
        $this->uriString = null;
        $this->userInfo  = null;
        $this->authority = null;
        $this->username  = (string)$username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($password): Uri
    {
        $this->uriString = null;
        $this->userInfo  = null;
        $this->authority = null;
        $this->password  = (string)$password;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost($host): Uri
    {
        $this->uriString = null;
        $this->authority = null;
        $this->host      = (string)$host;
        return $this;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getPortNumber(): ?int
    {
        if (!empty($this->port)) {
            return (int)$this->port;
        }
        if (!empty($this->scheme) && isset($this->schemes[$this->scheme])) {
            return (int)$this->schemes[$this->scheme];
        }
        return null;
    }

    public function setPort($port): Uri
    {
        $this->uriString = null;
        $this->authority = null;
        $this->port      = (string)$port;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath($path): Uri
    {
        $this->uriString = null;
        $this->path      = (string)$path;
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery($query): Uri
    {
        $this->uriString   = null;
        $this->query       = (string)$query;
        $this->queryParams = $this->parseQueryParams($this->query);
        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): Uri
    {
        $this->uriString   = null;
        $this->query       = http_build_query($queryParams);
        $this->queryParams = $queryParams;
        return $this;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function setFragment($fragment): Uri
    {
        $this->uriString = null;
        $this->fragment  = (string)$fragment;
        return $this;
    }

    private function createUriString(
        $scheme    = null,
        $authority = null,
        $path      = null,
        $query     = null,
        $fragment  = null
    ) {
        $uri = '';
        if (!empty($scheme)) {
            $uri .= $scheme . ':';
        }
        if (!empty($authority) || !empty($scheme)) {
            $uri .= '//';
        }
        $uri .= $authority;
        if (!empty($authority) || !empty($scheme)) {
            if (empty($path) || $path[0] !== '/') {
                $uri .= '/';
            }
        }
        $uri .= $path;
        if (!empty($query)) {
            $uri .= '?' . $query;
        }
        if (!empty($fragment)) {
            $uri .= '#' . $fragment;
        }
        return $uri;
    }

    private function parseQueryParams(string $query): array
    {
        $params = [];
        $query  = preg_replace('/^(:?[^=]*\?)?/', '', $query);
        parse_str($query, $params);
        $this->queryParams = $params;
        return $params;
    }
}
