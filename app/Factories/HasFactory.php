<?php

namespace App\Factories;

use Illuminate\Database\Eloquent\Factories\HasFactory as ParentHasFactory;

trait HasFactory
{
    use ParentHasFactory
    {
        ParentHasFactory::factory as private parentFactory;
    }

    /**
     * Get a new factory instance for the model.
     *
     * @param  mixed  $parameters
     * @return \App\Factories\Factory
     */
    public static function factory(...$parameters)
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
                    ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : null)
                    ->state(is_array($parameters[0] ?? null) ? $parameters[0] : ($parameters[1] ?? []));
    }
}
