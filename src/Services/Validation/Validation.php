<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class Validation
{
    public function __construct(
        protected ValidationContract $validator
    ){}

    public function extract(Collection $filters): Collection
    {
        return $this->validator->extract($filters);
    }
}
