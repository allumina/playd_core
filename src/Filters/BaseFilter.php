<?php

namespace Allumina\PlaydCore\Filters;

use App\Models\BaseModel;
use Carbon\Carbon;
use MongoDB\Driver\Query;

class BaseFilter
{
    private const CATEGORY_IDENTIFIER_SEPARATOR = '|';

    public const RAW_QUERY = 'raw_query';
    public const CREATE_DATE_MIN = 'create_date_min';
    public const CREATE_DATE_MAX = 'create_date_max';
    public const UPDATE_DATE_MIN = 'update_date_min';
    public const UPDATE_DATE_MAX = 'update_date_max';
    public const DELETE_DATE_MIN = 'delete_date_min';
    public const DELETE_DATE_MAX = 'delete_date_max';

    public const MAX_SIZE = 500;
    public const MAX_LAST = 30;
    public const ORDER_MODE_ASCENDING = 0;
    public const ORDER_MODE_DESCENDING = 1;
    public const OR_SEPARATOR = ",";
    public const COLUMNS_SEPARATOR = ",";

    protected $mongo = false;

    private $categories;
    private $types;
    private $subtypes;

    public $uid;
    public $identifier;
    public $friendly;
    public $locale;
    public $set;
    public $category;
    public $type;
    public $subtype;
    public $is_visible;
    public $is_enabled;
    public $is_deleted;
    public $flags;
    public $local_id;
    public $external_id;
    public $owner_id;
    public $user_id;
    public $parent_id;
    public $ancestor_id;
    public $group_id;
    public $application_id;
    public $environment_id;
    public $last_update_date;
    public $create_date_min;
    public $create_date_max;
    public $update_date_min;
    public $update_date_max;
    public $delete_date_min;
    public $delete_date_max;
    public $page;
    public $size;
    public $order_by;
    public $order_mode;
    public $last;
    public $columns;
    public $raw_query;

    public function isComplex()
    {
        return isset($this->last) && is_int($this->last);
    }

    public function categoryIdentifier()
    {
        return trim($this->category . self::CATEGORY_IDENTIFIER_SEPARATOR . $this->type . self::CATEGORY_IDENTIFIER_SEPARATOR . $this->subtype, self::CATEGORY_IDENTIFIER_SEPARATOR);
    }

    public function __construct()
    {
        $this->uid = null;
        $this->identifier = null;
        $this->locale = null;
        $this->friendly = null;
        $this->set = null;
        $this->category = null;
        $this->type = null;
        $this->subtype = null;
        $this->is_visible = null;
        $this->is_enabled = null;
        $this->is_deleted = null;
        $this->flags = null;
        $this->local_id = null;
        $this->owner_id = null;
        $this->user_id = null;
        $this->parent_id = null;
        $this->ancestor_id = null;
        $this->group_id = null;
        $this->external_id = null;
        $this->application_id = null;
        $this->environment_id = null;
        $this->last_update_date = null;
        $this->create_date_min = null;
        $this->create_date_max = null;
        $this->update_date_min = null;
        $this->update_date_max = null;
        $this->delete_date_min = null;
        $this->delete_date_max = null;
        $this->page = 0;
        $this->size = self::MAX_SIZE;
        $this->order_by = BaseModel::UPDATE_DATE;
        $this->order_mode = self::ORDER_MODE_DESCENDING;
        $this->last = null;
        $this->columns = null;

        $this->categories = array();
        $this->types = array();
        $this->subtypes = array();

        $this->raw_query = null;
    }

    protected function __parse($attributes)
    {
        if (array_key_exists(BaseModel::IDENTIFIER, $attributes) && !is_null($attributes[BaseModel::IDENTIFIER])) { $this->identifier = (string)$attributes[BaseModel::IDENTIFIER]; }
        if (array_key_exists(BaseModel::LOCALE, $attributes) && !is_null($attributes[BaseModel::LOCALE])) { $this->locale = (string)$attributes[BaseModel::LOCALE]; }
        if (array_key_exists(BaseModel::FRIENDLY, $attributes) && !is_null($attributes[BaseModel::FRIENDLY])) { $this->friendly = (string)$attributes[BaseModel::FRIENDLY]; }
        if (array_key_exists('set', $attributes) && !is_null($attributes['set'])) { $this->set = (string)$attributes['set']; }
        if (array_key_exists(BaseModel::CATEGORY, $attributes) && !is_null($attributes[BaseModel::CATEGORY])) { $this->category = (string)$attributes[BaseModel::CATEGORY]; }
        if (array_key_exists(BaseModel::TYPE, $attributes) && !is_null($attributes[BaseModel::TYPE])) { $this->type = (string)$attributes[BaseModel::TYPE]; }
        if (array_key_exists(BaseModel::SUBTYPE, $attributes) && !is_null($attributes[BaseModel::SUBTYPE])) { $this->subtype = (string)$attributes[BaseModel::SUBTYPE]; }
        if (array_key_exists(BaseModel::IS_VISIBLE, $attributes) && !is_null($attributes[BaseModel::IS_VISIBLE])) { $this->is_visible = boolval($attributes[BaseModel::IS_VISIBLE]); }
        if (array_key_exists(BaseModel::IS_ENABLED, $attributes) && !is_null($attributes[BaseModel::IS_ENABLED])) { $this->is_enabled = boolval($attributes[BaseModel::IS_ENABLED]); }
        if (array_key_exists(BaseModel::IS_DELETED, $attributes) && !is_null($attributes[BaseModel::IS_DELETED])) { $this->is_deleted = boolval($attributes[BaseModel::IS_DELETED]); }
        if (array_key_exists(BaseModel::FLAGS, $attributes) && !is_null($attributes[BaseModel::FLAGS])) { $this->flags = intval($attributes[BaseModel::FLAGS]); }

        if (array_key_exists(BaseModel::LOCAL_ID, $attributes) && !is_null($attributes[BaseModel::LOCAL_ID])) { $this->local_id = (string)$attributes[BaseModel::LOCAL_ID]; }
        if (array_key_exists(BaseModel::OWNER_ID, $attributes) && !is_null($attributes[BaseModel::OWNER_ID])) { $this->owner_id = (string)$attributes[BaseModel::OWNER_ID]; }
        if (array_key_exists(BaseModel::USER_ID, $attributes) && !is_null($attributes[BaseModel::USER_ID])) { $this->user_id = (string)$attributes[BaseModel::USER_ID]; }
        if (array_key_exists(BaseModel::PARENT_ID, $attributes) && !is_null($attributes[BaseModel::PARENT_ID])) { $this->parent_id = (string)$attributes[BaseModel::PARENT_ID]; }
        if (array_key_exists(BaseModel::ANCESTOR_ID, $attributes) && !is_null($attributes[BaseModel::ANCESTOR_ID])) { $this->ancestor_id = (string)$attributes[BaseModel::ANCESTOR_ID]; }
        if (array_key_exists(BaseModel::GROUP_ID, $attributes) && !is_null($attributes[BaseModel::GROUP_ID])) { $this->group_id = (string)$attributes[BaseModel::GROUP_ID]; }
        if (array_key_exists(BaseModel::EXTERNAL_ID, $attributes) && !is_null($attributes[BaseModel::EXTERNAL_ID])) { $this->external_id = (string)$attributes[BaseModel::EXTERNAL_ID]; }
        if (array_key_exists(BaseModel::APPLICATION_ID, $attributes) && !is_null($attributes[BaseModel::APPLICATION_ID])) { $this->application_id = (string)$attributes[BaseModel::APPLICATION_ID]; }
        if (array_key_exists(BaseModel::ENVIRONMENT_ID, $attributes) && !is_null($attributes[BaseModel::ENVIRONMENT_ID])) { $this->environment_id = (string)$attributes[BaseModel::ENVIRONMENT_ID]; }
        if (array_key_exists('last_update_date', $attributes) && !is_null($attributes['last_update_date'])) { $this->last_update_date = $attributes['last_update_date']; }

        if (array_key_exists(self::CREATE_DATE_MIN, $attributes) && !is_null($attributes[self::CREATE_DATE_MIN])) { $this->create_date_min = intval($attributes[self::CREATE_DATE_MIN]); }
        if (array_key_exists(self::CREATE_DATE_MAX, $attributes) && !is_null($attributes[self::CREATE_DATE_MAX])) { $this->create_date_max = intval($attributes[self::CREATE_DATE_MAX]); }
        if (array_key_exists(self::UPDATE_DATE_MIN, $attributes) && !is_null($attributes[self::UPDATE_DATE_MIN])) { $this->update_date_min = intval($attributes[self::UPDATE_DATE_MIN]); }
        if (array_key_exists(self::UPDATE_DATE_MAX, $attributes) && !is_null($attributes[self::UPDATE_DATE_MAX])) { $this->update_date_max = intval($attributes[self::UPDATE_DATE_MAX]); }
        if (array_key_exists(self::DELETE_DATE_MIN, $attributes) && !is_null($attributes[self::DELETE_DATE_MIN])) { $this->delete_date_min = intval($attributes[self::DELETE_DATE_MIN]); }
        if (array_key_exists(self::DELETE_DATE_MAX, $attributes) && !is_null($attributes[self::DELETE_DATE_MAX])) { $this->delete_date_max = intval($attributes[self::DELETE_DATE_MAX]); }

        if (array_key_exists(self::RAW_QUERY, $attributes) && !is_null($attributes[self::RAW_QUERY])) { $this->raw_query = json_decode($attributes[self::RAW_QUERY], true); }

        if (array_key_exists('last', $attributes) && !is_null($attributes['last'])) {
            if (is_int($attributes['last']) && $attributes['last'] <= self::MAX_LAST) {
                $this->last = intval($attributes['last']);
            }
            else {
                $this->last = self::MAX_LAST;
            }
        }

        if (array_key_exists('page', $attributes) && !is_null($attributes['page'])) { $this->page = intval($attributes['page']); }
        if (array_key_exists('size', $attributes) && !is_null($attributes['size'])) { $this->size = intval($attributes['size']); }
        if (array_key_exists('order_by', $attributes) && !is_null($attributes['order_by'])) { $this->order_by = (string)$attributes['order_by']; }
        if (array_key_exists('order_mode', $attributes) && !is_null($attributes['order_mode'])) { $this->order_mode = (string)$attributes['order_mode']; }
        if (array_key_exists('columns', $attributes) && !is_null($attributes['columns'])) { $this->columns = (string)$attributes['columns']; }
    }

    protected function __parseComplex($model, array $attributes = array()) {
        $this->__parseCategories();
        $this->__parseTypes();
        $this->__parseSubtypes();

        $complex = $model::raw(function($collection)
        {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => [
                            BaseModel::CATEGORY  => '$category',
                            BaseModel::TYPE  => '$type',
                            BaseModel::SUBTYPE  => '$subtype'
                        ]
                    ]
                ]
            ]);
        });

        $temp = array();
        foreach ($complex as $complexItem) {
            $tempData = $complexItem->getAttributes()['_id']->getArrayCopy();
            if (isset($tempData[BaseModel::CATEGORY]) && count($this->categories) > 0 && !in_array($tempData[BaseModel::CATEGORY], $this->categories)) {
                continue;
            }
            if (isset($tempData[BaseModel::TYPE]) && count($this->types) > 0 && !in_array($tempData[BaseModel::TYPE], $this->types)) {
                continue;
            }
            if (isset($tempData[BaseModel::SUBTYPE]) && count($this->subtypes) > 0 && !in_array($tempData[BaseModel::SUBTYPE], $this->subtypes)) {
                continue;
            }

            array_push($temp, $tempData);
        }

        $subfilters = array();
        foreach ($temp as $tempItem) {
            if (array_key_exists(BaseModel::CATEGORY, $tempItem) &&
                array_key_exists(BaseModel::TYPE, $tempItem) &&
                array_key_exists(BaseModel::SUBTYPE, $tempItem)) {
                $tempFilter = clone $this;
                $tempFilter->category = !is_null($tempItem[BaseModel::CATEGORY]) && strlen($tempItem[BaseModel::CATEGORY]) > 0 ? $tempItem[BaseModel::CATEGORY] : null;
                $tempFilter->type = !is_null($tempItem[BaseModel::TYPE]) && strlen($tempItem[BaseModel::TYPE]) > 0 ? $tempItem[BaseModel::TYPE] : null;
                $tempFilter->subtype = !is_null($tempItem[BaseModel::SUBTYPE]) && strlen($tempItem[BaseModel::SUBTYPE]) > 0 ? $tempItem[BaseModel::SUBTYPE] : null;
                $tempFilter->size = $this->last;
                $tempFilter->last = null;
                $tempFilter->categories = null;
                $tempFilter->types = null;
                $tempFilter->subtypes = null;

                if (!is_null($tempFilter->category) || !is_null($tempFilter->type) || !is_null($tempFilter->subtype)) {
                    $subfilters[$tempFilter->categoryIdentifier()] = $tempFilter;
                }
            }
        }
        return $subfilters;
    }

    private function __parseCategories() {
        if (!is_null($this->category)) {
            $temp = explode(self::OR_SEPARATOR, $this->category);
             if (is_array($temp) && count($temp) > 1) {
                foreach ($temp as $category) {
                    array_push($this->categories, $category);
                }
            } else {
                array_push($this->categories, $temp);
            }
        }
    }

    private function __parseTypes() {
        if (!is_null($this->type)) {
            $temp = explode(self::OR_SEPARATOR, $this->type);
             if (is_array($temp) && count($temp) > 1) {
                foreach ($temp as $type) {
                    array_push($this->types, $type);
                }
            } else {
                array_push($this->types, $temp);
            }
        }
    }

    private function __parseSubtypes() {
        if (!is_null($this->subtype)) {
            $temp = explode(self::OR_SEPARATOR, $this->subtype);
             if (is_array($temp) && count($temp) > 1) {
                foreach ($temp as $subtype) {
                    array_push($this->subtypes, $subtype);
                }
            } else {
                array_push($this->subtypes, $temp);
            }
        }
    }

    public function __completeQuery($query)
    {
        if (!isset($this->last) && !is_int($this->last)) {
            if (!is_null($this->page) && is_integer($this->page) && !is_null($this->size) && is_integer($this->size) && $this->size > 0 && $this->size <= self::MAX_SIZE) {
                $query->skip($this->page * $this->size);
                $query->take($this->size);
            } elseif (!is_null($this->size) && is_integer($this->size)) {
                $query->take($this->size);
            }
        }

        if (!is_null($this->order_by)) {
            $this->order_mode == self::ORDER_MODE_ASCENDING ?
                $query->orderBy($this->order_by) :
                $query->orderBy($this->order_by, 'desc');
        }
        return $query;
    }

    public static function orQuery(&$query, string $property, string $filter) {
        $temp = explode(self::OR_SEPARATOR, $filter);
        if (is_array($temp) && count($temp) > 1) {
            $query->where(function ($subquery) use ($temp, $property) {
                $subquery->where($property, $temp[0]);
                for ($i = 1; $i < count($temp); $i++) {
                    $subquery->orWhere($property, $temp[$i]);
                }
                return $subquery;
            });
        } else {
            $query->where($property, $filter);
        }
    }

    public function __prepareQuery($q) {
        if (!is_null($this->uid)) {
            $temp = explode(self::OR_SEPARATOR, $this->uid);
            if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where('uid', $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere('uid', $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where('uid', $this->uid);
            }
        }


        if (!is_null($this->identifier)) {
            $temp = explode(self::OR_SEPARATOR, $this->identifier);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::IDENTIFIER, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::IDENTIFIER, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::IDENTIFIER, $this->identifier);
            }
        }

        if (!is_null($this->friendly)) {
            $temp = explode(self::OR_SEPARATOR, $this->friendly);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::FRIENDLY, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::FRIENDLY, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::FRIENDLY, $this->friendly);
            }
        }


        if (!is_null($this->locale)) {
            $temp = explode(self::OR_SEPARATOR, $this->locale);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::LOCALE, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::LOCALE, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::LOCALE, $this->locale);
            }
        }

        if (!is_null($this->category)) {
            $temp = explode(self::OR_SEPARATOR, $this->category);
            if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::CATEGORY, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::CATEGORY, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::CATEGORY, $this->category);
            }
        }

        if (!is_null($this->type)) {
            $temp = explode(self::OR_SEPARATOR, $this->type);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::TYPE, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::TYPE, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::TYPE, $this->type);
            }
        }

        if (!is_null($this->subtype)) {
            $temp = explode(self::OR_SEPARATOR, $this->subtype);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::SUBTYPE, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::SUBTYPE, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::SUBTYPE, $this->subtype);
            }
        }

        if (!is_null($this->is_visible)) { $q->where(BaseModel::IS_VISIBLE, boolval($this->is_visible)); }
        if (!is_null($this->is_enabled)) { $q->where(BaseModel::IS_ENABLED, boolval($this->is_enabled)); }
        if (!is_null($this->is_deleted)) { $q->where(BaseModel::IS_DELETED, boolval($this->is_deleted)); }

        if (!is_null($this->flags)) { $q->where(BaseModel::FLAGS, $this->flags); }

        if (!is_null($this->local_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->local_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::LOCAL_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::LOCAL_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::LOCAL_ID, $this->local_id);
            }
        }

        if (!is_null($this->owner_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->owner_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::OWNER_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::OWNER_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::OWNER_ID, $this->owner_id);
            }
        }

        if (!is_null($this->user_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->user_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::USER_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::USER_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::USER_ID, $this->user_id);
            }
        }

        if (!is_null($this->parent_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->parent_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::PARENT_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::PARENT_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::PARENT_ID, $this->parent_id);
            }
        }

        if (!is_null($this->ancestor_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->ancestor_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::ANCESTOR_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::ANCESTOR_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::ANCESTOR_ID, $this->ancestor_id);
            }
        }

        if (!is_null($this->group_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->group_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::GROUP_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::GROUP_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::GROUP_ID, $this->group_id);
            }
        }

        if (!is_null($this->application_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->application_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::APPLICATION_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::APPLICATION_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::APPLICATION_ID, $this->application_id);
            }
        }

        if (!is_null($this->environment_id)) {
            $temp = explode(self::OR_SEPARATOR, $this->environment_id);
             if (is_array($temp) && count($temp) > 1) {
                $q->where(function ($subquery) use ($temp) {
                    $subquery->where(BaseModel::ENVIRONMENT_ID, $temp[0]);
                    for ($i = 1; $i < count($temp); $i++) {
                        $subquery->orWhere(BaseModel::ENVIRONMENT_ID, $temp[$i]);
                    }
                    return $subquery;
                });
            } else {
                $q->where(BaseModel::ENVIRONMENT_ID, $this->environment_id);
            }
        }

        if (!is_null($this->last_update_date)) {
            if ($this->mongo) { $q->where(BaseModel::UPDATE_DATE, '>=', Carbon::createFromTimestamp($this->last_update_date)); }
            else { $q->where(BaseModel::UPDATE_DATE, '>=', date('Y-m-d H:i:s', $this->last_update_date)); }
        }

        if (!is_null($this->create_date_min)) {
            if ($this->mongo) { $q->where(BaseModel::CREATE_DATE, '>=', Carbon::createFromTimestamp($this->create_date_min)); }
            else { $q->where(BaseModel::CREATE_DATE, '>=', date('Y-m-d H:i:s', $this->create_date_min)); }
        }
        if (!is_null($this->create_date_max)) {
            if ($this->mongo) { $q->where(BaseModel::CREATE_DATE, '<=', Carbon::createFromTimestamp($this->create_date_max)); }
            else { $q->where(BaseModel::CREATE_DATE, '<=', date('Y-m-d H:i:s', $this->create_date_max)); }
        }
        if (!is_null($this->update_date_min)) {
            if ($this->mongo) { $q->where(BaseModel::UPDATE_DATE, '>=', Carbon::createFromTimestamp($this->update_date_min)); }
            else { $q->where(BaseModel::UPDATE_DATE, '>=', date('Y-m-d H:i:s', $this->update_date_min)); }
        }
        if (!is_null($this->update_date_max)) {
            if ($this->mongo) { $q->where(BaseModel::UPDATE_DATE, '<=', Carbon::createFromTimestamp($this->update_date_max)); }
            else { $q->where(BaseModel::UPDATE_DATE, '<=', date('Y-m-d H:i:s', $this->update_date_max)); }
        }
        if (!is_null($this->delete_date_min)) {
            if ($this->mongo) { $q->where(BaseModel::DELETE_DATE, '>=', Carbon::createFromTimestamp($this->delete_date_min)); }
            else { $q->where(BaseModel::DELETE_DATE, '>=', date('Y-m-d H:i:s', $this->delete_date_min)); }
        }
        if (!is_null($this->delete_date_max)) {
            if ($this->mongo) { $q->where(BaseModel::DELETE_DATE, '<=', Carbon::createFromTimestamp($this->delete_date_max)); }
            else { $q->where(BaseModel::DELETE_DATE, '>=', date('Y-m-d H:i:s', $this->delete_date_max)); }
        }
    }
}
