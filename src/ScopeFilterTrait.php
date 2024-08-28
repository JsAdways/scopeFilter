<?php

namespace Jsadways\ScopeFilter;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

trait ScopeFilterTrait
{
    /**
     * Scope filter
     *
     * 查詢調整過濾 scoper
     *
     * @param Builder $query
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     * @return Builder|Exception
     * @throws Exception
     */
    public function scopeFilter(Builder $query, array $filters): Builder|Exception
    {
        try {
            $filters = $this->fitColumns(collect($filters));

            foreach($filters as $filter){
                if($filter['operator'] === 'or'){
                    $query = $query->where(function($sub_query)use($filter){
                        $sub_filters = $this->fitColumns(collect($filter['value']));
                        foreach($sub_filters as $sub_filter){
                            $sub_query = $this->conditionFilter($sub_query,$sub_filter,'or');
                        }
                    });

                }
                else if($filter['operator'] === '@'){
                    $query = $query->orWhere(function($sub_query)use($filter){
                        foreach($filter['value'] as $relation_name => $relation){
                            $sub_filters = $this->fitColumns(collect($relation),$relation_name);
                            foreach($sub_filters as $sub_filter){
                                $sub_query = $this->conditionRelationFilter($sub_query,$sub_filter,$relation_name,'or');
                            }
                        }
                    });

                }
                else{
                    $query = $this->conditionFilter($query,$filter,'and');
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
     * @param Builder $query
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     * @param string $type
     * @return Builder|Exception
     * @throws Exception
     */
    protected function conditionFilter(Builder $query, array $filter, string $type): Builder|Exception
    {
        try {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];
            $function_append = ($type === 'or') ? 'or' : '';
            $w = ($function_append === '') ? 'w' : 'W';

            return match ($operator) {
                'k' => $query->{$function_append.$w.'here'}($field, 'like', "%{$value}%"),
                'ipp', 'ie' => $query->{$function_append.$w.'here'}($field, 'like', "%{$value}"),
                'iel' => $query->{$function_append.$w.'here'}($field, 'ilike', "%{$value}"),
                'in' => (is_array($value)) ? $query->{$function_append.$w.'hereIn'}($field, $value) : $query,
                'nin' => (is_array($value)) ? $query->{$function_append.$w.'hereNotIn'}($field, $value) : $query,
                'ge' => $query->{$function_append.$w.'here'}($field, '>=', $value),
                'gt' => $query->{$function_append.$w.'here'}($field, '>', $value),
                'ne' => $query->{$function_append.$w.'here'}($field, '!=', $value),
                'eq' => $query->{$function_append.$w.'here'}($field, '=', $value),
                'lt' => $query->{$function_append.$w.'here'}($field, '<', $value),
                'le' => $query->{$function_append.$w.'here'}($field, '<=', $value),
                'nl' => $query->{$function_append.$w.'hereNull'}($field),
                'nnl' => $query->{$function_append.$w.'hereNotNull'}($field),
                'cge' => $query->{$function_append.$w.'hereColumn'}($field, '>=', $value),
                'cgt' => $query->{$function_append.$w.'hereColumn'}($field, '>', $value),
                'cne' => $query->{$function_append.$w.'hereColumn'}($field, '!=', $value),
                'ceq' => $query->{$function_append.$w.'hereColumn'}($field, '=', $value),
                'clt' => $query->{$function_append.$w.'hereColumn'}($field, '<', $value),
                'cle' => $query->{$function_append.$w.'hereColumn'}($field, '<=', $value),
                'dr' => (is_array($value)) ? $query->{$function_append.$w.'hereBetween'}($field, [$value[0], $value[1]]) : $query
            };
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * relation condition filter
     *
     * 查詢調整過濾 scoper
     *
     * @param Builder $query
     * @param array[
     *     'name_k' => ['hello world']' ['{field}_{rule}' => ['value']]
     * ]
     * @param string $relation
     * @param string $type
     * @return Builder|Exception
     * @throws Exception
     */
    protected function conditionRelationFilter(Builder $query, array $filter, string $relation, string $type): Builder|Exception
    {
        try {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];
            $function_append = ($type === 'or') ? 'or' : '';
            $w = ($function_append === '') ? 'w' : 'W';

            return match ($operator) {
                'k' => $query->{$function_append.$w.'hereRelation'}($relation,$field, 'like', "%{$value}%"),
                'ipp', 'ie' => $query->{$function_append.$w.'hereRelation'}($relation,$field, 'like', "%{$value}"),
                'iel' => $query->{$function_append.$w.'hereRelation'}($relation,$field, 'ilike', "%{$value}"),
                'ge' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '>=', $value),
                'gt' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '>', $value),
                'ne' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '!=', $value),
                'eq' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '=', $value),
                'lt' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '<', $value),
                'le' => $query->{$function_append.$w.'hereRelation'}($relation,$field, '<=', $value)
            };
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * fit columns
     *
     * 確認傳入columns是否正確
     *
     * @param Collection $filters
     * @return Collection {field:'file_name',operator:'>=,=,...','value':'condition value'}
     */
    protected function fitColumns(Collection $filters,String $relation = ''):Collection
    {
        $columns = collect(Schema::getColumnListing($this->getTable()));
        if($relation !== ''){
            $columns = collect(Schema::getColumnListing($this->{$relation}()->getRelated()->getTable()));
        }

        if($filters->has('keyword')){
            $other_filter_keys = $filters->keys()->reject('keyword')->map(function ($item){return explode('_',$item)[0];});
            $filters['keyword_or'] = $this->fitModelKeyword($filters['keyword'],$other_filter_keys);
            $filters['relation_@'] = $this->fitRelationKeyword($filters['keyword']);
        }

        return collect($filters)->filter(function($value){
            //移除空條件，數字不處理
            return is_numeric($value) || !empty($value);
        })->reduce(function ($result,$value,$key)use($columns){
            //比對條件與資料表欄位名稱，移除非正確欄位名稱查詢條件
            $element = explode('_',$key);
            $operator = array_pop($element);
            $field = implode('_',$element);
            if($columns->contains($field) || $field === 'keyword' || $field === 'relation'){
                $result->push([
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value
                ]);
            }

            return $result;
        },collect([]));
    }


    /**
     * fit model keyword
     *
     * 組合此Model預設keyword搜尋條件
     *
     * @param String $keyword
     * @param Collection $other_filter_key
     * @return array ['fillableColumn_K'=>keyword]
     */
    protected function fitModelKeyword(String $keyword,Collection $other_filter_key):array
    {
        $fillables = collect($this->getFillable());
        $fillables = $fillables->diff($other_filter_key);
        return $this->fitKeywordColumns($fillables,$keyword);

    }


    /**
     * fit relation keyword
     *
     * 組合此Model Relation 預設keyword搜尋條件
     *
     * @param String $keyword
     * @return array ['fillableColumn_K'=>keyword]
     */
    protected function fitRelationKeyword(String $keyword):array
    {
        $result = [];
        $relations = $this->getAvailableRelations();
        foreach($relations as $relation){
            $relation_fillables = collect($this->{$relation}()->getRelated()->getFillable());
            $relation_filters = $this->fitKeywordColumns($relation_fillables,$keyword);
            $result[$relation] = $relation_filters;
        }

        return $result;
    }

    /**
     * fit keyword columns
     *
     * 實際將欄位組合_k = keyword
     *
     * @param Collection $fillables
     * @param String $keyword
     * @return array ['fillableColumn_K'=>keyword]
     */
    protected function fitKeywordColumns(Collection $fillables,String $keyword): array
    {
        return $fillables->reduce(function ($result_column,$fillable_column)use($keyword){
            $result_column[$fillable_column.'_k'] = $keyword;

            return $result_column;
        },[]);
    }

    /**
     * get available relation
     *
     * 找到Model中定義之relation
     *
     * @return array ['name']
     */
    protected function getAvailableRelations(): array
    {
        return array_keys(array_reduce(
            (new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC),
            function ($result, ReflectionMethod $method) {
                // If this function has a return type
                ($returnType = (string) $method->getReturnType()) &&

                // And this function returns a relation
                is_subclass_of($returnType, Relation::class) &&

                // Add name of this method to the relations array
                ($result = array_merge($result, [$method->getName() => $returnType]));

                return $result;
            }, []
        ));
    }


}
