<?php

namespace App\Models;

class Script extends BaseModel {

    protected $fillable = [
        "type",
        "value",
        "status",
        "result",
        "name",
    ];

    protected static $rules = [
        "type"   => ["required", "in:percentage,absolute"],
        "status" => ["required", "in:submitted,running,finished,terminated"],
    ];

    public function deployment() {
        return $this->belongsTo(Deployment::class, "deployment_id", 'guid');
    }
}