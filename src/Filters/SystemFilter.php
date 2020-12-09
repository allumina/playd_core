<?php

namespace Allumina\PlaydCore\Filters;

use App\Models\SystemModel;
use Illuminate\Database\Eloquent\Collection;

class SystemFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function parse(array $attributes = array())
    {
        parent::__parse($attributes);
    }

    public function apply($model, bool $mongo = false)
    {
        $this->mongo = $mongo;
        $query = $model::where(function ($q) {
            parent::__prepareQuery($q);
        });
        return parent::__completeQuery($query);
    }

    public function query() {
        if ($this->isComplex()) {
            return $this->queryComplex();
        } else {
            return $this->querySimple();
        }
    }

    public function queryComplex() {
        $output = new Collection();
        $subfilters = $this->__parseComplex(SystemModel::class);
        $check = array();
        foreach ($subfilters as $subfilter) {
            $subdata = $subfilter->querySimple();
            foreach ($subdata as $item) {
                if (!in_array($item->uid, $check)) {
                    $output->add($item);
                    array_push($check, $item->uid);
                }
            }
        }
        return $output;
    }

    public function querySimple() {
        $query = $this->apply(SystemModel::class, true);
        if (isset($this->columns)) {
            return $query->get(explode(BaseFilter::COLUMNS_SEPARATOR, $this->columns));
        }
        else {
            return $query->get();
        }
    }

    public static function find(array $query) {
        return SystemModel::raw(function($collection) use ($query) {
            return $collection->find($query);
        });
    }

    public static function aggregate(array $query) {
        return SystemModel::raw(function($collection) use ($query) {
            return $collection->aggregate($query);
        });
    }
}
