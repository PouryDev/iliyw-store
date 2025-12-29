<?php

namespace App\DTOs;

use Illuminate\Support\Arr;

/**
 * Base Data Transfer Object
 * 
 * DTOs are immutable objects used to transfer data between layers
 */
abstract class BaseDTO
{
    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($this);
        }
        
        return $data;
    }

    /**
     * Get only non-null values
     *
     * @return array
     */
    public function toArrayWithoutNulls(): array
    {
        return array_filter($this->toArray(), fn($value) => $value !== null);
    }

    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new static();
        }
        
        $parameters = $constructor->getParameters();
        $args = [];
        
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $args[] = $data[$name] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        }
        
        return new static(...$args);
    }
}

