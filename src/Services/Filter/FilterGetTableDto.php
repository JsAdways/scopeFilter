<?php

namespace Jsadways\ScopeFilter\Services\Filter;

use Illuminate\Database\Eloquent\Model;
use Jsadways\ScopeFilter\Services\Common\Dto;

final class FilterGetTableDto extends Dto
{
    public function __construct(
        public readonly Model $modelClass,
        public readonly string $relation,
    ) {}
}
