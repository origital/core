<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Event;

trait EventEmitterTrait
{
    private $__events = [];

    protected function emit(string $event, array $data = [])
    {
        $callbacks = $this->__events[$event] ?? [];
        if (!empty($callbacks)) {
            $event = new Event($event, $this);
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, [$event, $data]);
            }
        }
        $callbacks = $this->__events['*'] ?? [];
        if (!empty($callbacks)) {
            $event = new Event($event, $this);
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, [$event, $data]);
            }
        }
        return $this;
    }

    public function subscribe(string $event, callable $callback)
    {
        if (!is_array($this->__events[$event])) {
            $this->__events[$event] = [];
        }
        $this->__events[$event][] = $callback;
        return $this;
    }

    public function unsubscribe(string $event, callable $callback)
    {
        if (is_array($this->__events[$event])) {
            $callbacks = $this->__events[$event];
            foreach ($callbacks as $i => $registered) {
                if ($callback === $registered) {
                    array_splice($this->__events[$event], $i, 1);
                    break;
                }
            }
        }
        return $this;
    }
}
