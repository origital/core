<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Event;

interface EventEmitter
{
    public function subscribe(string $event, callable $callback);
    public function unsubscribe(string $event, callable $callback);
}
