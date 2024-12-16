<?php

namespace Jsadways\ScopeFilter\Services\Common;

abstract class Dto
{
    public function get(): array
    {
        $properties = get_object_vars($this);
        return array_filter($properties, fn ($value) => !is_null($value));
    }
}
