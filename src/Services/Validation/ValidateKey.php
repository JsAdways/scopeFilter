<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class ValidateKey implements ValidationContract
{
    private array $validKey = ["keyword","or","and","OrRelation_or","AndRelation_or","OrRelation_and","AndRelation_and"];

    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        //符合定義key值
        return $filters->filter(function($item,$key){
            return collect($this->validKey)->contains($key);
        });
    }
}
