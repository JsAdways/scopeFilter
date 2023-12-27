<?php

namespace Jsadways\ScopeFilter;

use Exception;
use Illuminate\Database\Eloquent\Builder;

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

                $query = match ($rule) {
                    'k' => $query->where($field, 'like', "%{$v}%"),
                    'ipp' => $query->where($field, 'like', "%{$v}"),
                    'ie' => $query->where($field, 'like', "%{$v}"),
                    'iel' => $query->where($field, 'ilike', "%{$v}"),
                    'in' => (is_array($v)) ? $query->whereIn($field, $v) : $query,
                    'nin' => (is_array($v)) ? $query->whereNotIn($field, $v) : $query,
                    'ge' => $query->where($field, '>=', $v),
                    'gt' => $query->where($field, '>', $v),
                    'ne' => $query->where($field, '!=', $v),
                    'eq' => $query->where($field, '=', $v),
                    'lt' => $query->where($field, '<', $v),
                    'le' => $query->where($field, '<=', $v),
                    'dr' => (is_array($v)) ? $query->whereBetween($field, [$v[0], $v[1]]) : $query
                };
            }

            return $query;
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }
}
