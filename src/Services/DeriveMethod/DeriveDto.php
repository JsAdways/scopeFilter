<?php

namespace Jsadways\ScopeFilter\Services\DeriveMethod;

use Illuminate\Database\Eloquent\Builder;
use Jsadways\ScopeFilter\Services\Common\Dto;

final class DeriveDto extends Dto
{
    public function __construct(
        public Builder $query,
        public string $operator,
        public string $logic_gate,
        public mixed $value
    ) {}
}
