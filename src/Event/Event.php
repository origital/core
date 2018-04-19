<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Event;

use Origital\Event\Exception\InvalidNameException;

class Event
{
    private $name;
    private $target;

    public function __construct(string $name, object $target)
    {
        if (empty($name)) {
            throw new InvalidNameException('Invalid `name`; cannot be empty');
        }
        $this->name   = $name;
        $this->target = $target;
    }

    public function __toString()
    {
        return $this->name . '@' . get_class($this->target);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTarget()
    {
        return $this->target;
    }
}
