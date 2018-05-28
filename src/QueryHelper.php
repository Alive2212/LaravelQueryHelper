<?php
/**
 * Created by PhpStorm.
 * User: alive
 * Date: 10/8/17
 * Time: 4:04 AM
 */

namespace App\Resources\Model;

class QueryHelper
{
    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function deepFilter($query, $filters)
    {
        $result = $query;
        foreach ($filters as $filter) {
            $keys = explode('.', $filter['key']);
            if (collect($keys)->count() == 2) {
                $result = $result->whereHas($keys[0], function ($query) use ($filter, $keys) {
                    $query->where($keys[1], $filter['operator'], $filter['value']);
                });
            } elseif (collect($keys)->count() == 3) {
                $result = $result->whereHas($keys[0], function ($query) use ($filter, $keys) {
                    $query->whereHas($keys[1], function ($query) use ($filter, $keys) {
                        $query->where($keys[2], $filter['operator'], $filter['value']);
                    });
                });
            } elseif (collect($keys)->count() == 4) {
                $result = $result->whereHas($keys[0], function ($query) use ($filter, $keys) {
                    $query->whereHas($keys[1], function ($query) use ($filter, $keys) {
                        $query->whereHas($keys[2], function ($query) use ($filter, $keys) {
                            $query->where($keys[3], $filter['operator'], $filter['value']);
                        });
                    });
                });
            } elseif (collect($keys)->count() == 1) {
                $result = $result->where($keys[0], $filter['operator'], $filter['value']);
            }
        }
        return $result;
    }

    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function orDeepFilter($query, $filters)
    {
        $result = $query;
        $keys = explode('.', $filters[0]['key']);
        if (collect($keys)->count() == 2) {
            $result = $result->orWhereHas($keys[0], function ($query) use ($filters, $keys) {
                foreach ($filters as $filter) {
                    $keys = explode('.', $filter['key']);
                    $query->where($keys[1], $filter['operator'], $filter['value']);
                }
            });
        }
        return $result;
    }

    /**
     * @param $query
     * @param $param
     * @return mixed
     */
    public function orderBy($query, $param)
    {
        $result = $query;
        if ($param->count()) {
            $query->orderBy($param['field'], $param['operator']);
        }
        return $result;
    }

    /**
     * @param $params
     * @param $field
     * @return array
     */
    public function getValueArray($params, $field)
    {
        $result = [];
        foreach ($params as $key => $item) {
            array_push($result, $item[$field]);
        }
        return $result;
    }
}