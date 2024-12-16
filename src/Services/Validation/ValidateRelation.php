<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Contracts\Validation\ValidateContract;
use Throwable;

class ValidateRelation implements ValidateContract
{
    public function __construct(
        protected Model $target
    ){}

    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        return $filters->reduce(function ($result,$condition,$relationName){
            if($this->_checkRelationExist($relationName)){
                $result->put($relationName,$condition);
            }
            return $result;
        },collect([]));
    }

    protected function _checkRelationExist(string $relation):bool
    {
        try {
            $relation_array = explode('.', $relation);
            $target = $this->target;

            foreach ($relation_array as $relation_name) {
                $target = $target->{$relation_name}()->{'getRelated'}();
            }

            return true;
        }catch (Throwable $throwable){
            return false;
        }
    }
}
