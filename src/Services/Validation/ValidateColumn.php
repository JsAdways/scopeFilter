<?php
namespace Jsadways\ScopeFilter\Services\Validation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Jsadways\ScopeFilter\Core\Service\Validation\Contracts\ValidationContract;

class ValidateColumn implements ValidationContract
{
    public function extract(Collection $filters): Collection
    {
        // TODO: Implement extract() method.
        $tableName = $filters->keys()[0];

        return collect($filters[$tableName])->filter(function ($filter)use($tableName){
            return Schema::hasColumn($tableName,$filter['field']);
        });
    }
}
