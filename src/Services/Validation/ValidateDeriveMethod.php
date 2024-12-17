<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Jsadways\ScopeFilter\Core\Attributes\Derive;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;
use ReflectionClass;

class ValidateDeriveMethod implements ValidationContract
{
    public function __construct(
        protected Model $target
    ){}

    /**
     * @throws \ReflectionException
     */
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        $relationName = $filters->keys()[0];
        $target = $this->target;
        if($relationName !== 0){
            $relation_array = explode('.', $relationName);

            foreach ($relation_array as $relation_name) {
                $target = $target->{$relation_name}()->{'getRelated'}();
            }
        }

        $deriveMethods = collect((new ReflectionClass($target))->getMethods())->reduce(function ($result,$method){
            if($method->getAttributes(Derive::class)){
                $result->push($method->getName());
            }

            return $result;
        },collect([]));

        return collect($filters[$relationName])->filter(function ($filter)use($deriveMethods){
            return $deriveMethods->contains($filter['field']);
        });
    }
}
