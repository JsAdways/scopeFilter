<?php
namespace Jsadways\ScopeFilter\Classes\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Contracts\Validation\ValidateContract;

class Validation
{
    public function __construct(
        protected ValidateContract $validator
    ){}

    public function extract(Collection $filters): Collection
    {
        return $this->validator->extract($filters);
    }
}
