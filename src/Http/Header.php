<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

use Origital\Http\Exception\HeaderException;

class Header
{
    private $normalizedName;
    private $name;
    private $value = [];

    static public function normalizeName(string $name)
    {
        return str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
    }

    public function __construct(string $name, $value = [])
    {
        $this->setName($name)
             ->setValue($value);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if (empty($this->value)) {
            return $this->normalizedName . ': 1';
        }
        $headers   = [];
        $commaSafe = true;
        foreach ($this->value as $value) {
            $headers[] = $this->normalizedName . ': ' . $value;
            if ($commaSafe && strpos($value, ',') !== false) {
                $commaSafe = false;
            }
        }
        if ($this->getNormalizedName() === 'Set-Cookie') {
            $commaSafe = false;
        }
        return $commaSafe ? $this->normalizedName . ': ' . implode(', ', $this->value) : implode("\r\n", $headers);
    }

    public function getNormalizedName()
    {
        return $this->normalizedName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name): Header
    {
        if (empty($name)) {
            throw new HeaderException('Empty header name');
        }
        $this->name = $name;
        $this->normalizedName = self::normalizeName($name);
        return $this;
    }

    public function getValue()
    {
        return count($this->value) === 1 ? $this->value[0] : $this->value;
    }

    public function setValue($value): Header
    {
        $this->value = !is_array($value) ? [$value] : $value;
        return $this;
    }

    public function addValue($value): Header
    {
        $this->value[] = $value;
        return $this;
    }
}
