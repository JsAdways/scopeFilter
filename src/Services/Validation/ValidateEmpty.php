<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class ValidateEmpty implements ValidationContract
{
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        return $filters->filter(function ($condition){
            return $condition!== '' && $condition !== null;
        });
    }
}
