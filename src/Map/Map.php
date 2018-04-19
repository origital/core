<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Map;

use Origital\Map\Exception\NotFoundException;

class Map
{
    /**
     * Array of entries
     *
     * @internal
     * @var mixed[]
     */
    private $entries;

    /**
     * Array for caching entries
     *
     * Stores 'key' => 'value' pairs in order to speed up subsequent requests of the same entry.
     *
     * @internal
     * @var mixed[]
     */
    private $cache;

    /**
     * Constructor
     *
     * @param mixed[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->setEntries($entries);
    }

    /**
     * Retrieves an entry
     *
     * Multidimensional structures can be accessed by using a single key comprised of
     * intermediate keys separated with dots (e.g., 'group.subgroup.param' references
     * Map::$entries['group']['subgroup']['param']).
     *
     * @param string $key Key for the entry
     * @param mixed $default Default value returned if the $key is not found
     * @param bool $strict Forces to throw an exception if the param does not exist
     * @throws NotFoundException
     * @return mixed Entry or $default
     */
    public function get(string $key, $default = null, bool $strict = false)
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $partial = $this->entries;
        $parts   = explode('.', $key);
        foreach ($parts as $part) {
            if (isset($partial[$part])) {
                $partial = $partial[$part];
            } else {
                if ($strict) {
                    throw new NotFoundException('Entry `' . $key . '` was not found');
                }
                $partial = $default;
                break;
            }
        }
        $this->cache[$key] = $partial;
        return $partial;
    }

    /**
     * Sets an entry
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value): Map
    {
        $partial = &$this->entries;
        $parts   = explode('.', $key);
        foreach ($parts as $part) {
            if (!isset($partial[$part])) {
                $partial[$part] = [];
            }
            $partial = &$partial[$part];
        }
        $partial = $value;
        $this->cache[$key] = $value;
        return $this;
    }

    /**
     * Removes an entry
     *
     * @param string $key
     * @return $this
     */
    public function remove(string $key): Map
    {
        $partial = &$this->entries;
        $parts   = explode('.', $key);
        foreach ($parts as $part) {
            if (!isset($partial[$part])) {
                return $this;
            }
            $partial = &$partial[$part];
        }
        unset($partial);
        unset($this->cache[$key]);
        return $this;
    }

    /**
     * Checks whether an entry exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            $this->get($key, null, true);
        } catch (NotFoundException $e) {
            return false;
        }
        return true;
    }

    /**
     * Merges the entries of this Map with the ones of another Map
     *
     * @param Map $entries
     * @return $this
     */
    public function mergeWith(Map $entries): Map
    {
        return $this->setEntries($entries->asArray() + $this->asArray());
    }

    /**
     * Returns array of entries
     *
     * @return mixed[]
     */
    public function asArray(): array
    {
        return $this->entries;
    }

    /**
     * Sets the internal array of entries and resets cache
     *
     * @param mixed[] $entries
     * @return $this
     */
    protected function setEntries(array $entries): Map
    {
        $this->entries = $entries;
        $this->cache   = [];
        return $this;
    }
}
