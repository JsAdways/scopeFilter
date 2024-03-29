<?php

namespace Jsadways\ScopeFilter;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use mysql_xdevapi\Collection;

trait ScopeFilterTrait
{
    /**
     * Scope filter
     *
     * 查詢調整過濾 scoper
     *
     * @param  \Illuminate\Database\Eloquent\Builder
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     */
    public function scopeFilter(Builder $query, array $filters): Builder|Exception
    {

        try {
            foreach ($filters as $k => $v) {
                $splitKeys = explode('_', $k);
                $rule = end($splitKeys);
                array_pop($splitKeys);
                $field = implode('_', $splitKeys);

                if($rule === 'or'){
                    $query = $query->where(function($sub_query)use($v){
                        foreach ($v as $sub_k=>$sub_v){
                            $splitKeys = explode('_', $sub_k);
                            $sub_rule = end($splitKeys);
                            array_pop($splitKeys);
                            $sub_field = implode('_', $splitKeys);
                            $sub_query = self::conditionFilter($sub_query,['field'=>$sub_field,'rule'=>$sub_rule,'value'=>$sub_v],'or');
                        }
                    });
                }else{
                    $query = self::conditionFilter($query,['field'=>$field,'rule'=>$rule,'value'=>$v],'and');
                }
            }

            return $query;
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * condition filter
     *
     * 查詢調整過濾 scoper
     *
     * @param  \Illuminate\Database\Eloquent\Builder
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     */
    protected function conditionFilter(Builder $query, array $filter, string $type): Builder|Exception
    {
        try {
            $field = $filter['field'];
            $rule = $filter['rule'];
            $value = $filter['value'];

            if($type === 'or'){
                $query = match ($rule) {
                    'k' => $query->orWhere($field, 'like', "%{$value}%"),
                    'ipp' => $query->orWhere($field, 'like', "%{$value}"),
                    'ie' => $query->orWhere($field, 'like', "%{$value}"),
                    'iel' => $query->orWhere($field, 'ilike', "%{$value}"),
                    'in' => (is_array($value)) ? $query->orWhereIn($field, $value) : $query,
                    'nin' => (is_array($value)) ? $query->orWhereNotIn($field, $value) : $query,
                    'ge' => $query->orWhere($field, '>=', $value),
                    'gt' => $query->orWhere($field, '>', $value),
                    'ne' => $query->orWhere($field, '!=', $value),
                    'eq' => $query->orWhere($field, '=', $value),
                    'lt' => $query->orWhere($field, '<', $value),
                    'le' => $query->orWhere($field, '<=', $value),
                    'dr' => (is_array($value)) ? $query->orWhereBetween($field, [$value[0], $value[1]]) : $query
                };
            }else{
                $query = match ($rule) {
                    'k' => $query->where($field, 'like', "%{$value}%"),
                    'ipp' => $query->where($field, 'like', "%{$value}"),
                    'ie' => $query->where($field, 'like', "%{$value}"),
                    'iel' => $query->where($field, 'ilike', "%{$value}"),
                    'in' => (is_array($value)) ? $query->whereIn($field, $value) : $query,
                    'nin' => (is_array($value)) ? $query->whereNotIn($field, $value) : $query,
                    'ge' => $query->where($field, '>=', $value),
                    'gt' => $query->where($field, '>', $value),
                    'ne' => $query->where($field, '!=', $value),
                    'eq' => $query->where($field, '=', $value),
                    'lt' => $query->where($field, '<', $value),
                    'le' => $query->where($field, '<=', $value),
                    'dr' => (is_array($value)) ? $query->whereBetween($field, [$value[0], $value[1]]) : $query
                };
            }

            return $query;
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }
}
