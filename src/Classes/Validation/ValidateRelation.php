<?php
namespace Jsadways\ScopeFilter\Classes\Validation;

use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Contracts\Validation\ValidateContract;

class ValidateRelation implements ValidateContract
{
    public function __construct(
        protected Collection $tableRelationColumns
    ){}

    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        $mapping_keys = $this->tableRelationColumns->keys();
        return $filters->reduce(function ($result,$relation)use($mapping_keys){
            if($mapping_keys->contains($relation)){
                $result->push($relation);
            }
            return $result;
        },collect([]));
    }
}
