<?php
namespace Jsadways\ScopeFilter\Contracts\Validation;

use Illuminate\Support\Collection;

interface ValidateContract{
    public function extract(Collection $filters): Collection;
}
