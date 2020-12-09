<?php

namespace Allumina\PlaydCore\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Events\BaseModelCreating;
use App\Events\BaseModelUpdating;
use Ramsey\Uuid\Uuid;
use stdClass;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    public const CATEGORY_IDENTIFIER_SEPARATOR = '|';
    public const PATH_SEPARATOR = '.';

    public const DATA_TYPE_ENUM = 'enum';
    public const DATA_TYPE_UNIT = 'unit';
    public const DATA_TYPE_CONTENT = 'content';
    public const DATA_TYPE_BOOLEAN = 'bool';
    public const DATA_TYPE_INTEGER = 'int';
    public const DATA_TYPE_DOUBLE = 'double';
    public const DATA_TYPE_ARRAY = 'array';
    public const DATA_TYPE_OBJECT = 'object';

    protected const STRING_CONCAT_SYMBOL = '_';

    public const USER_SYSTEM = 'system';

    public const ID = '_id';
    public const UID = 'uid';
    public const IDENTIFIER = 'identifier';
    public const LOCALE = 'locale';
    public const FRIENDLY = 'friendly';
    public const CATEGORY = 'category';
    public const TYPE = 'type';
    public const SUBTYPE = 'subtype';
    public const DATATYPE = 'datatype';
    public const SORT_INDEX = 'sort_index';
    public const IS_VISIBLE = 'is_visible';
    public const IS_ENABLED = 'is_enabled';
    public const IS_DELETED = 'is_deleted';
    public const FLAGS = 'flags';
    public const OWNER_ID = 'owner_id';
    public const CREATE_DATE = 'create_date';
    public const UPDATE_DATE = 'update_date';
    public const DELETE_DATE = 'delete_date';
    public const USER_ID = 'user_id';
    public const LOCAL_ID = 'local_id';
    public const PARENT_ID = 'parent_id';
    public const ANCESTOR_ID = 'ancestor_id';
    public const GROUP_ID = 'group_id';
    public const EXTERNAL_ID = 'external_id';
    public const APPLICATION_ID = 'application_id';
    public const ENVIRONMENT_ID = 'environment_id';
    public const VERSION = 'version';
    public const HASH = 'hash';
    public const RAW = 'raw';
    public const INTERNAL = 'internal';
    public const ACL = 'acl';

    public const CREATED_AT = 'create_date';
    public const UPDATED_AT = 'update_date';
    public const DELETED_AT = 'delete_date';

    protected $primaryKey = 'uid';
    protected $dates = ['delete_date'];
    protected $dateFormat = 'U';

    protected $casts = [
        self::UID => 'string',
        self::CREATE_DATE => 'timestamp',
        self::UPDATE_DATE => 'timestamp',
        self::DELETE_DATE => 'timestamp',
    ];

    protected $attributes = [
        self::UID => null,
        self::IDENTIFIER => null,
        self::LOCALE => null,
        self::FRIENDLY => null,
        self::CATEGORY => null,
        self::TYPE => null,
        self::SUBTYPE => null,
        self::DATATYPE => null,
        self::SORT_INDEX => 0,
        self::IS_VISIBLE => false,
        self::IS_ENABLED => false,
        self::IS_DELETED => false,
        self::FLAGS => 0,
        self::OWNER_ID => null,
        self::USER_ID => null,
        self::LOCAL_ID => null,
        self::PARENT_ID => null,
        self::ANCESTOR_ID => null,
        self::GROUP_ID => null,
        self::EXTERNAL_ID => null,
        self::APPLICATION_ID => null,
        self::ENVIRONMENT_ID => null,
        self::VERSION => 0,
        self::HASH => null,
        self::RAW => null,
        self::INTERNAL => null,
        self::ACL => null
    ];

    protected $fillable = [
        self::UID,
        self::IDENTIFIER,
        self::LOCALE,
        self::FRIENDLY,
        self::CATEGORY,
        self::TYPE,
        self::SUBTYPE,
        self::DATATYPE,
        self::SORT_INDEX,
        self::IS_VISIBLE,
        self::IS_ENABLED,
        self::IS_DELETED,
        self::FLAGS,
        self::OWNER_ID,
        self::USER_ID,
        self::LOCAL_ID,
        self::PARENT_ID,
        self::ANCESTOR_ID,
        self::GROUP_ID,
        self::EXTERNAL_ID,
        self::APPLICATION_ID,
        self::ENVIRONMENT_ID,
        self::VERSION,
        self::HASH,
        self::RAW,
        self::INTERNAL,
        self::ACL
    ];

    protected $hidden = [
        self::ID,
        self::INTERNAL,
        self::HASH,
        self::ACL
    ];

    public function path(string $category = null, string $type = null, string $subtype = null)
    {
        if (is_null($category)) { $category = $this->category; }
        if (is_null($type)) { $type = $this->type; }
        if (is_null($subtype)) { $subtype = $this->subtype; }
        return trim($this->collection . self::PATH_SEPARATOR . $category . self::PATH_SEPARATOR . $type . self::PATH_SEPARATOR . $subtype, self::PATH_SEPARATOR);
    }

    public function categoryIdentifier(string $category = null, string $type = null, string $subtype = null)
    {
        if (is_null($category)) { $category = $this->category; }
        if (is_null($type)) { $type = $this->type; }
        if (is_null($subtype)) { $subtype = $this->subtype; }
        return trim($category . self::CATEGORY_IDENTIFIER_SEPARATOR . $type . self::CATEGORY_IDENTIFIER_SEPARATOR . $subtype, self::CATEGORY_IDENTIFIER_SEPARATOR);
    }

    public function categoryShortIdentifier(string $type = null, string $subtype = null)
    {
        if (is_null($type)) { $type = $this->type; }
        if (is_null($subtype)) { $subtype = $this->subtype; }
        return trim( $type . self::CATEGORY_IDENTIFIER_SEPARATOR . $subtype, self::CATEGORY_IDENTIFIER_SEPARATOR);
    }

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    public function assignId(bool $force = false)
    {
        if (is_null($this->uid) || strlen($this->uid) <= 0 || $force) {
            $this->uid = Uuid::uuid4()->toString();
        }
    }

    public function assignIdentifier(bool $force = false)
    {
        if (is_null($this->identifier) || strlen($this->identifier) <= 0 || $force) {
            $this->identifier = Uuid::uuid4()->toString();
        }
    }

    public static function explodePath(string $path, bool $full = true) {
        $temp = explode(self::PATH_SEPARATOR, $path);
        if (!is_null($temp)) {
            if (is_array($temp) && count($temp) > 0) {
                $output = new stdClass();
                if ($full) {
                    if (count($temp) > 0) {
                        $output->collection = ($temp[0] != '*') ? $temp[0] : null;
                    } else {
                        $output->collection = null;
                    }
                    if (count($temp) > 1) {
                        $output->category = ($temp[1] != '*') ? $temp[1] : null;
                    } else {
                        $output->category = null;
                    }
                    if (count($temp) > 2) {
                        $output->type = ($temp[2] != '*') ? $temp[2] : null;
                    } else {
                        $output->type = null;
                    }
                    if (count($temp) > 3) {
                        $output->subtype = ($temp[3] != '*') ? $temp[3] : null;
                    } else {
                        $output->subtype = null;
                    }
                } else {
                    if (count($temp) > 1) {
                        $output->category = ($temp[0] != '*') ? $temp[0] : null;
                    } else {
                        $output->category = null;
                    }
                    if (count($temp) > 2) {
                        $output->type = ($temp[1] != '*') ? $temp[1] : null;
                    } else {
                        $output->type = null;
                    }
                    if (count($temp) > 3) {
                        $output->subtype = ($temp[2] != '*') ? $temp[2] : null;
                    } else {
                        $output->subtype = null;
                    }
                }
                $output->short = $output->type . '|' . $output->subtype;
                $output->short = trim($output->short);
                $output->short = trim($output->short, '|');
                $output->short = trim($output->short);
                return $output;
            } else {
                return $temp;
            }
        }
        return null;
    }

    protected function __parse(array $attributes = array())
    {
        if (array_key_exists(self::UID, $attributes)) { $this->uid = $attributes[self::UID]; }
        if (array_key_exists(self::IDENTIFIER, $attributes)) { $this->identifier = $attributes[self::IDENTIFIER]; }
        if (array_key_exists(self::LOCALE, $attributes)) { $this->locale = $attributes[self::LOCALE]; }
        if (array_key_exists(self::FRIENDLY, $attributes)) { $this->friendly = $attributes[self::FRIENDLY]; }
        if (array_key_exists(self::CATEGORY, $attributes)) { $this->category = $attributes[self::CATEGORY]; }
        if (array_key_exists(self::TYPE, $attributes)) { $this->type = $attributes[self::TYPE]; }
        if (array_key_exists(self::SUBTYPE, $attributes)) { $this->subtype = $attributes[self::SUBTYPE]; }
        if (array_key_exists(self::DATATYPE, $attributes)) { $this->datatype = $attributes[self::DATATYPE]; }
        if (array_key_exists(self::IS_VISIBLE, $attributes)) { $this->is_visible = $attributes[self::IS_VISIBLE]; }
        if (array_key_exists(self::IS_ENABLED, $attributes)) { $this->is_enabled = $attributes[self::IS_ENABLED]; }
        if (array_key_exists(self::IS_DELETED, $attributes)) { $this->is_deleted = $attributes[self::IS_DELETED]; }
        if (array_key_exists(self::FLAGS, $attributes)) { $this->flags = $attributes[self::FLAGS]; }
        if (array_key_exists(self::SORT_INDEX, $attributes)) { $this->sort_index = $attributes[self::SORT_INDEX]; }
        if (array_key_exists(self::LOCAL_ID, $attributes)) { $this->local_id = $attributes[self::LOCAL_ID]; }
        if (array_key_exists(self::OWNER_ID, $attributes)) { $this->owner_id = $attributes[self::OWNER_ID]; }
        if (array_key_exists(self::USER_ID, $attributes)) { $this->user_id = $attributes[self::USER_ID]; }
        if (array_key_exists(self::PARENT_ID, $attributes)) { $this->parent_id = $attributes[self::PARENT_ID]; }
        if (array_key_exists(self::ANCESTOR_ID, $attributes)) { $this->ancestor_id = $attributes[self::ANCESTOR_ID]; }
        if (array_key_exists(self::GROUP_ID, $attributes)) { $this->group_id = $attributes[self::GROUP_ID]; }
        if (array_key_exists(self::EXTERNAL_ID, $attributes)) { $this->external_id = $attributes[self::EXTERNAL_ID]; }
        if (array_key_exists(self::APPLICATION_ID, $attributes)) { $this->application_id = $attributes[self::APPLICATION_ID]; }
        if (array_key_exists(self::ENVIRONMENT_ID, $attributes)) { $this->environment_id = $attributes[self::ENVIRONMENT_ID]; }

        if (array_key_exists(self::RAW, $attributes)) { $this->raw = $attributes[self::RAW]; }
    }

    public static function getIdentifierByFriendly(string $friendly, string $locale, string $category = null, string $type = null, string $subtype = null) {
        $output = null;
        if (!is_null($friendly) && !is_null($locale)) {
            $query = ItemModel::where(self::FRIENDLY, '=', $friendly)
                ->where(self::LOCALE, '=', $locale);
            if (!is_null($category)) { $query = $query->where(self::CATEGORY, '=', $category); }
            if (!is_null($type)) { $query = $query->where(self::TYPE, '=', $type); }
            if (!is_null($subtype)) { $query = $query->where(self::SUBTYPE, '=', $subtype); }
            $temp = $query->get();
            $output = $temp;
        }
        return $output;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (strlen($model->uid) <= 0) $model->uid = Uuid::uuid4()->toString();
            $model->version = 1;
            unset($model->hash);
            $model->hash = hash(Constants::HASH_ALGORITHM, json_encode($model));
        });

        static::updating(function ($model) {
            $model->version = $model->version + 1;
            unset($model->hash);
            $model->hash = hash(Constants::HASH_ALGORITHM, json_encode($model));
        });

        static::deleting(function ($model) {
            $model->version = $model->version + 1;
            $model->is_deleted = true;
            unset($model->hash);
            $model->hash = hash(Constants::HASH_ALGORITHM, json_encode($model));
        });
    }

}
