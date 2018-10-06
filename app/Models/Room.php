<?php

namespace App\Models;

use App\Models\Dj\User;

class Room extends SimpleBaseModel {
    protected $connection = "mysql";

    protected $rules = [
        'name' => 'required|unique:rooms,name',
    ];

    protected $with = ["deployments"];

    protected $fillable = [
        "name",
        "rows",
        "cols",
    ];

    public function contest() {
        return $this->belongsTo(Contest::class, "contest_id");
    }

    public function deployments() {
        return $this->belongsToMany(Deployment::class, "room_deployment", "room_id", "deployment_id")
            ->withPivot(["numerator", "denominator"])
            ->orderBy(DB::raw("numerator / denominator"));
    }
}