<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Contracts\Validation\ValidateContract;

class ValidateEmpty implements ValidateContract
{
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        return $filters->filter(function ($condition){
            return $condition!== '' && $condition !== null;
        });
    }
}
