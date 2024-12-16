<?php

namespace Jsadways\ScopeFilter\Services\Filter;

use App\Exceptions\ServiceException;
use Illuminate\Support\Collection;
use Jsadways\ScopeFilter\Core\Service\Filter\Contracts\FilterContract;

class FilterService implements FilterContract
{
    public function format(FilterFormatDto $filters): array
    {
        // TODO: Implement explode() method.
        $filter_data = $filters->get();

        return $filter_data['filters']->reduce(function ($result,$value,$key){
            $element = explode('_',$key);
            $operator = array_pop($element);
            $field = implode('_',$element);

            $result[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];

            return $result;
        },[]);
    }

    public function getTable(FilterGetTableDto $data): string
    {
        // TODO: Implement getTable() method.
        $fullRelationName = $data->get()['relation'];
        $modelClass = $data->get()['modelClass'];

        //relation多層處理
        $relations = explode('.',$fullRelationName);
        foreach($relations as $relation){
            $modelClass = $modelClass->{$relation}()->getRelated();
        }

        return $modelClass->getTable();
    }
}
