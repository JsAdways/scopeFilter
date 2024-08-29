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
    private array $validKey = ["keyword","or","and","OrRelation_or","AndRelation_or","OrRelation_and","AndRelation_and"];
    private Collection $tableColumns;
    private Collection $fillableColumns;
    private Collection $tableRelationColumns;
    private Builder $query;

    /**
     * Scope filter
     *
     * 查詢調整過濾 scope
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
            $this->query = $query;
            //存下column name
            $this->tableColumns = collect(Schema::getColumnListing($this->getTable()));
            $this->fillableColumns = collect($this->getFillable());
            $relations = $this->_getAvailableRelations();
            $this->tableRelationColumns = collect([]);
            foreach($relations as $relation){
                $this->tableRelationColumns->put($relation,collect(Schema::getColumnListing($this->{$relation}()->getRelated()->getTable())));
            }

            //檢查key是否在$validKey中
            $validFilters = $this->_checkValidKey(collect($filters));
            $validFilters->reduce(function ($result,$item,$key){
                //實際呼叫_fitXXX
                $this->{'_fit'.ucfirst($key)}($item);
            },[]);

            return $this->query;
        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * check valid key
     *
     * 檢查傳入key直是否合法
     *
     * @param Collection $filters
     * @return Collection
     */
    protected function _checkValidKey(Collection $filters): Collection
    {
        return $filters->filter(function($item,$key){
            return collect($this->validKey)->contains($key);
        });
    }

    /**
     * fit keyword
     *
     * 處理keyword關鍵字查詢語法
     *
     * @param string $value
     * @return void
     * @throws Exception
     */
    protected function _fitKeyword(string $value):void
    {
        $this->query->where(function($sub_query)use($value){
            //組合mode中所有的fillable column
            $this->fillableColumns->map(function ($column)use($sub_query,$value){
                $filterData = [
                    'field' => $column,
                    'operator' => 'k',
                    'value' => $value,
                ];
                $this->query = $this->_matchCondition($sub_query,$filterData,'or');
            });

            $this->tableRelationColumns->map(function ($columns,$relation)use($value){
                //組合所有relation中的fillable column
                $relation_conditions = $columns->reduce(function ($result,$item)use($value){
                    $result[$item.'_k'] = $value;

                    return $result;
                },[]);

                $this->_fitOrRelation_or([$relation=>$relation_conditions]);
            });
        });
    }

    /**
     * fit or
     *
     * 處理Or查詢語法
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitOr(array $value): void
    {
        $this->query->where(function($sub_query)use($value){
            collect($value)->map(function ($item,$key)use($sub_query){
                //check key fit table column name
                $checkResult = $this->_checkColumnValid($this->tableColumns,$key,$item);
                if($checkResult !== false){
                    //if check pass, fit where condition
                    $this->query = $this->_matchCondition($sub_query,$checkResult,'or');
                }
            });
        });
    }

    /**
     * fit and
     *
     * 處理And查詢語法
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitAnd(array $value): void
    {
        $this->query->where(function($sub_query)use($value){
            collect($value)->map(function ($item,$key)use($sub_query){
                //check key fit table column name
                $checkResult = $this->_checkColumnValid($this->tableColumns,$key,$item);
                if($checkResult !== false){
                    //if check pass, fit where condition
                    $this->query = $this->_matchCondition($sub_query,$checkResult,'and');
                }
            });
        });
    }

    /**
     * fit or relation or
     *
     * 處理orWhereHas查詢語法，內部條件為Or
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitOrRelation_or(array $value): void
    {
        $this->_fitRelation($value,'orWhereHas','or');
    }

    /**
     * fit and relation or
     *
     * 處理whereHas查詢語法，內部條件為Or
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitAndRelation_or(array $value): void
    {
        $this->_fitRelation($value,'whereHas','or');
    }

    /**
     * fit or relation and
     *
     * 處理orWhereHas查詢語法，內部條件為And
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitOrRelation_and(array $value): void
    {
        $this->_fitRelation($value,'orWhereHas','and');
    }

    /**
     * fit and relation and
     *
     * 處理whereHas查詢語法，內部條件為and
     *
     * @param array $value
     * @return void
     * @throws Exception
     */
    protected function _fitAndRelation_and(array $value): void
    {
        $this->_fitRelation($value,'whereHas','and');
    }

    /**
     * fit relation
     *
     * 實際處理relation相關語法
     *
     * @param array $value
     * @param string $whereHas whereHas, orWhereHas
     * @param string $logic and , or
     * @return void
     */
    protected function _fitRelation(array $value,string $whereHas,string $logic):void
    {
        collect($value)->map(function ($item, $key)use($whereHas,$logic){
            if ($this->_checkRelationValid($key)) {
                $this->query->{$whereHas}($key,function(Builder $sub_query)use($key,$item,$logic){
                    $sub_query->where(function(Builder $relation_query)use($key,$item,$logic){
                        collect($item)->map(function ($relation_item, $relation_key) use ($key,$relation_query,$logic) {
                            //check key fit table column name
                            $checkResult = $this->_checkColumnValid($this->tableRelationColumns[$key], $relation_key, $relation_item);
                            if ($checkResult !== false) {
                                //if check pass, fit where condition
                                $this->_matchCondition($relation_query, $checkResult, $logic);
                            }
                        });
                    });
                });
            }
        });
    }

    /**
     * check column valid
     *
     * 檢查查詢名稱是否為正確的欄位，正確回覆拆分後的field,operator,value
     *
     * @param Collection $columns
     * @param string $key
     * @param $value
     * @return bool|array
     */
    protected function _checkColumnValid(Collection $columns, string $key,$value): bool|array
    {
        $element = explode('_',$key);
        $operator = array_pop($element);
        $field = implode('_',$element);

        if($columns->contains($field)){
            return [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }

        return false;
    }

    /**
     * check relation valid
     *
     * 檢查查詢relation名稱是否正確
     *
     * @param string $relation
     * @return bool
     */
    protected function _checkRelationValid(string $relation): bool
    {
        return $this->tableRelationColumns->keys()->contains($relation);
    }

    /**
     * match condition
     *
     * 實際組合filter condition where條件
     *
     * @param Builder $query
     * @param array $filter
     * @param string $logic
     * @return Builder|Exception
     * @throws Exception
     */
    protected function _matchCondition(Builder $query, array $filter, string $logic): Builder|Exception
    {
        try {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];
            $whereString = ($logic === 'or') ? 'orWhere' : 'where';

            return match ($operator) {
                'k' => $query->{$whereString}($field, 'like', "%{$value}%"),
                'ipp', 'ie' => $query->{$whereString}($field, 'like', "%{$value}"),
                'iel' => $query->{$whereString}($field, 'ilike', "%{$value}"),
                'in' => (is_array($value)) ? $query->{$whereString.'In'}($field, $value) : $query,
                'nin' => (is_array($value)) ? $query->{$whereString.'NotIn'}($field, $value) : $query,
                'ge' => $query->{$whereString}($field, '>=', $value),
                'gt' => $query->{$whereString}($field, '>', $value),
                'ne' => $query->{$whereString}($field, '!=', $value),
                'eq' => $query->{$whereString}($field, '=', $value),
                'lt' => $query->{$whereString}($field, '<', $value),
                'le' => $query->{$whereString}($field, '<=', $value),
                'nl' => $query->{$whereString.'Null'}($field),
                'nnl' => $query->{$whereString.'NotNull'}($field),
                'cge' => $query->{$whereString.'Column'}($field, '>=', $value),
                'cgt' => $query->{$whereString.'Column'}($field, '>', $value),
                'cne' => $query->{$whereString.'Column'}($field, '!=', $value),
                'ceq' => $query->{$whereString.'Column'}($field, '=', $value),
                'clt' => $query->{$whereString.'Column'}($field, '<', $value),
                'cle' => $query->{$whereString.'Column'}($field, '<=', $value),
                'dr' => (is_array($value)) ? $query->{$whereString.'Between'}($field, [$value[0], $value[1]]) : $query
            };


        } catch (Exception $e) {
            throw new Exception("scopeFilter - {$e->getMessage()}");
        }
    }

    /**
     * get available relation
     *
     * 找到Model中定義之relation
     *
     * @return array ['name']
     */
    protected function _getAvailableRelations(): array
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
