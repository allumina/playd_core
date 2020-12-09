<?php

namespace Allumina\PlaydCore\Models;

use App\Exceptions\DataNotFoundExceptioon;
use App\Filters\BaseFilter;

class SystemModel extends BaseModel
{
    const COLLECTION = 'system';

    public const CATEGORY_APPLICATION = 'application';
    public const CATEGORY_ENVIRONMENT = 'environment';
    public const CATEGORY_PERMISSION = 'permission';
    public const CATEGORY_SET = 'set';
    public const CATEGORY_MODEL = 'model';
    public const CATEGORY_CATEGORY = 'category';
    public const CATEGORY_TYPE = 'type';
    public const CATEGORY_SUBTYPE = 'subtype';
    public const CATEGORY_VALIDATOR = 'validator';
    public const CATEGORY_MODULE = 'module';
    public const CATEGORY_GROUP = 'group';
    public const CATEGORY_THRESHOLD = 'threshold';
    public const CATEGORY_WEBHOOK = 'webhook';
    public const CATEGORY_HOOK = 'hook';

    public const TYPE_DEVELOPMENT = 'development';
    public const TYPE_STAGING = 'staging';
    public const TYPE_PRODUCTION = 'production';
    public const TYPE_APPLICATION = 'application';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_USER = 'user';
    public const TYPE_SETTINGS = 'settings';
    public const TYPE_ITEMS = 'items';
    public const TYPE_CONTENTS = 'contents';
    public const TYPE_ALERT = 'alert';
    public const TYPE_SCORE = 'score';
    public const TYPE_AGGREGATE = 'aggregate';

    public const MEASUREMENT = 'measurement';
    public const SYMPTOM = 'symptom';
    public const PERSONAL = 'personal';
    public const LIFESTYLE = 'lifestyle';

    public const FLAGS_ROOT_APPLICATION = 100;
    public const FLAGS_NETWORK_APPLICATION = 10;
    public const FLAGS_WRITE_PERMISSION = 2;
    public const FLAGS_READ_PERMISSION = 4;
    public const FLAGS_READ_AND_WRITE_PERMISSION = 6;

    protected $connection = 'mongodb';
    protected $collection = 'system';

    public function parse(array $attributes = array())
    {
        parent::__parse($attributes);
    }

    public function isSet() {
        return $this->category === SystemModel::CATEGORY_SET;
    }

    public function isPermission() {
        return $this->category === SystemModel::CATEGORY_PERMISSION;
    }

    public static function getSets(string $application_id, string $environment_id, string $type, bool $is_visible = null, bool $is_enabled = true, bool $is_deleted = false) {
        return SystemModel::where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::CATEGORY, SystemModel::CATEGORY_SET)
            ->where(SystemModel::TYPE, $type)
            ->get();
    }

    public static function getSet(string $application_id, string $environment_id, string $type, string $friendly) {
        return SystemModel::where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::CATEGORY, SystemModel::CATEGORY_SET)
            ->where(SystemModel::TYPE, $type)
            ->where(SystemModel::FRIENDLY, $friendly)
            ->get()
            ->first();
    }

    public static function getPermission(string $application_id, string $environment_id, string $type, string $target_id, string $user_id) {
        return SystemModel::where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::CATEGORY, SystemModel::CATEGORY_PERMISSION)
            ->where(SystemModel::TYPE, $type)
            ->where(SystemModel::USER_ID, $user_id)
            ->where(SystemModel::OWNER_ID, $target_id)
            ->get()
            ->first();
    }

    public static function createApplication(string $identifier, string $friendly, int $flags = 0)
    {
        $model = SystemModel::getApplicationByIdentifier($identifier);
        if (is_null($model)) {
            $model = new SystemModel();
            $model->assignId();
        }
        $model->identifier = $identifier;
        $model->friendly = $friendly;
        $model->category = SystemModel::CATEGORY_APPLICATION;
        $model->type = null;
        $model->subtype = null;
        $model->flags = $flags;
        $model->is_visible = true;
        $model->is_enabled = true;
        $model->is_deleted = false;
        $model->raw = null;
        $model->save();
        return $model;
    }

    public static function createEnvironment(string $type, string $application_id, int $flags = 0)
    {
        $model = SystemModel::where(SystemModel::TYPE, $type)
            ->where(SystemModel::APPLICATION_ID, $application_id)
            ->get()->first();
        if (is_null($model)) {
            $model = new SystemModel();
            $model->assignId();
            $model->assignIdentifier();
            $model->friendly = $model->identifier;
        }
        $model->category = SystemModel::CATEGORY_ENVIRONMENT;
        $model->type = $type;
        $model->subtype = null;
        $model->flags = $flags;
        $model->is_visible = true;
        $model->is_enabled = true;
        $model->is_deleted = false;
        $model->raw = null;
        $model->application_id = $application_id;
        $model->save();
        return $model;
    }

    public static function getApplicationByIdentifier(string $identifier, bool $is_visible = null, bool $is_enabled = true, bool $is_deleted = false) {
        $query = self::where(SystemModel::IDENTIFIER, $identifier);
        if (!is_null($is_visible)) { $query = $query->where(SystemModel::IS_VISIBLE, $is_visible); }
        if (!is_null($is_enabled)) { $query = $query->where(SystemModel::IS_ENABLED, $is_enabled); }
        if (!is_null($is_deleted)) { $query = $query->where(SystemModel::IS_DELETED, $is_deleted); }
        return $query->get()->first();
    }

    public static function findApplication(string $friendly) {
        $query = SystemModel::where(SystemModel::FRIENDLY, $friendly);
        return $query->get()->first();
    }

    public static function getEnvironments(string $application_id, string $type, bool $is_visible = null, bool $is_enabled = true, bool $is_deleted = false) {
        $query = self::where(SystemModel::CATEGORY, SystemModel::CATEGORY_ENVIRONMENT)
            ->where(SystemModel::TYPE, $type);
        if (!is_null($is_visible)) { $query = $query->where(SystemModel::IS_VISIBLE, $is_visible); }
        if (!is_null($is_enabled)) { $query = $query->where(SystemModel::IS_ENABLED, $is_enabled); }
        if (!is_null($is_deleted)) { $query = $query->where(SystemModel::IS_DELETED, $is_deleted); }
        BaseFilter::orQuery($query, SystemModel::APPLICATION_ID, $application_id);
        return $query->get();
    }

    public static function grantPermission(string $target, string $type, string $application_id, string $environment_id, int $flags, string $user_id = SystemModel::USER_SYSTEM) {
        $model = self::checkPermission($target, $application_id, $environment_id, $type);
        if (is_null($model)) {
            $model = new SystemModel();
            $model->assignId();
            $model->assignIdentifier();
        }
        $model->locale = null;
        $model->friendly = $model->identifier;
        $model->category = SystemModel::CATEGORY_PERMISSION;
        $model->type = $type;
        $model->subtype = null;
        $model->flags = $flags;
        $model->is_visible = true;
        $model->is_enabled = true;
        $model->is_deleted = false;
        $model->raw = null;
        $model->user_id = $user_id; // User granting permission
        $model->owner_id = $target;
        $model->application_id = $application_id; //
        $model->environment_id = $environment_id;
        $model->save();
    }

    public static function revokePermission(string $target, string $type, string $application_id, string $environment_id, string $user_id = SystemModel::USER_SYSTEM) {
        $model = self::checkPermission($target, $application_id, $environment_id, $type);
        if (is_null($model)) {
            throw new DataNotFoundExceptioon();
        } else {
            $model->flags = 0;
            $model->user_id = $user_id; // User revoking permission
            $model->save();
        }
    }

    public static function getPermissions(string $application_id, string $environment_id, string $type, string $user_id = null, bool $is_visible = null, bool $is_enabled = true, bool $is_deleted = false) {
        $query = self::where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::TYPE, $type);
        if (!is_null($user_id)) { $query = $query->where(SystemModel::USER_ID,  $user_id); }
        if (!is_null($is_visible)) { $query = $query->where(SystemModel::IS_VISIBLE,  $is_visible); }
        if (!is_null($is_enabled)) { $query = $query->where(SystemModel::IS_ENABLED,  $is_enabled); }
        if (!is_null($is_deleted)) { $query = $query->where(SystemModel::IS_DELETED,  $is_deleted); }
        return $query->get();
    }

    public static function getThresholds(string $application_id, string $environment_id, bool $is_visible = null, bool $is_enabled = true, bool $is_deleted = false) {
        $query = self::where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::CATEGORY, SystemModel::CATEGORY_THRESHOLD);
        if (!is_null($is_visible)) { $query = $query->where(SystemModel::IS_VISIBLE,  $is_visible); }
        if (!is_null($is_enabled)) { $query = $query->where(SystemModel::IS_ENABLED,  $is_enabled); }
        if (!is_null($is_deleted)) { $query = $query->where(SystemModel::IS_DELETED,  $is_deleted); }
        $temp = $query->get();
        $output = array();
        foreach ($temp as $temp_item) {
            // $target = SystemModel::explodePath($temp_item->friendly);
            $output[$temp_item->friendly] = $temp_item;
        }
        return $output;
    }

    private static function checkPermission(string $target, string $application_id, string $environment_id, string $type) {
        $query = SystemModel::where(SystemModel::CATEGORY, SystemModel::CATEGORY_PERMISSION)
            ->where(SystemModel::OWNER_ID, $target)
            ->where(SystemModel::APPLICATION_ID, $application_id)
            ->where(SystemModel::ENVIRONMENT_ID, $environment_id)
            ->where(SystemModel::TYPE, $type);
        return $query->get()->first();
    }
}
