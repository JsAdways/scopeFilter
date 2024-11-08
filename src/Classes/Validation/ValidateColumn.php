<?php
namespace Jsadways\ScopeFilter\Classes\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Contracts\Validation\ValidateContract;

class ValidateColumn implements ValidateContract
{
    public function __construct(
        protected Collection $tableColumns
    ){}

    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        return $filters->filter(function ($item, $key){
            $element = explode('_',$key);
            array_pop($element);
            $field = implode('_',$element);
            return $this->tableColumns->contains($field);
        });
    }
}
