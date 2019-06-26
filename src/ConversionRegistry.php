<?php

namespace Optix\Media;

use Optix\Media\Exceptions\InvalidConversion;

class ConversionRegistry
{
    /**
     * @var array
     */
    protected $conversions = [];

    /**
     * Get all the registered conversions.
     *
     * @return array
     */
    public function all()
    {
        return $this->conversions;
    }

    /**
     * Register a new conversion.
     *
     * @param  string  $name
     * @param  callable  $conversion
     * @return void
     */
    public function register(string $name, callable $conversion)
    {
        $this->conversions[$name] = $conversion;
    }

    /**
     * Get the conversion with the specified name.
     *
     * @param  string  $name
     * @return mixed
     *
     * @throws InvalidConversion
     */
    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw InvalidConversion::doesNotExist($name);
        }

        return $this->conversions[$name];
    }

    /**
     * Determine if a conversion with the specified name exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function exists(string $name)
    {
        return isset($this->conversions[$name]);
    }
}
