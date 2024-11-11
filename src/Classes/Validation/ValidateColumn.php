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
        return $filters->reduce(function ($result,$value,$key){
            $element = explode('_',$key);
            $operator = array_pop($element);
            $field = implode('_',$element);

            if($this->tableColumns->contains($field)){
                $result->push([
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value
                ]);
            }
            return $result;
        },collect([]));
    }
}
