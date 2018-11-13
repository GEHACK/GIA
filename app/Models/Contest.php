<?php

namespace App\Models;

class Contest extends BaseModel {

    protected $fillable = [
        "cid",
    ];

    protected static $rules = [
        "cid" => ["required", "unique:contests,cid", "exists:domjudge.contest,cid"],
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function (BaseModel $baseModel) {
            $baseModel->hash = str_random(32);
        }, 1000000);
    }
}