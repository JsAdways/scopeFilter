<?php

namespace Jsadways\ScopeFilter\Services\Filter;

use Jsadways\ScopeFilter\Services\Common\Dto;
use Illuminate\Support\Collection;

final class FilterFormatDto extends Dto
{
    public function __construct(
        public readonly Collection $filters
    ) {}
}
