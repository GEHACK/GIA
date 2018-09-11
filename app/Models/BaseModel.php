<?php

namespace App\Models;

use Webpatser\Uuid\Uuid;

class BaseModel extends SimpleBaseModel {
    protected $primaryKey = 'guid';

    protected static function boot() {
        parent::boot();

        static::creating(function (BaseModel $baseModel) {
            $baseModel->{$baseModel->getKeyName()} = (string)Uuid::generate();
        }, 10000000);
    }
}
