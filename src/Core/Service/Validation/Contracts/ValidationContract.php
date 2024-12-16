<?php
namespace Jsadways\ScopeFilter\Core\Service\Validation\Contracts;

use Illuminate\Support\Collection;

interface ValidationContract{
    public function extract(Collection $filters): Collection;
}
