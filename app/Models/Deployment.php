<?php

namespace App\Models;

use App\Models\Dj\User;

class Deployment extends SimpleBaseModel {
    protected $connection = "mysql";

    protected $rules = [
        'id' => "required|unique:deployments,id",
        "ip" => "required|ipv4",
    ];

    protected $with = ["user"];

    protected $fillable = [
        "id",
        "userid",
        "proxy_ip",
        "ip",
    ];

    public function user() {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function room() {
        return $this->belongsToMany(Room::class, "room_deployment", "deployment_id", "room_id");
    }

    public function scripts() {
        return $this->hasMany(ExecJob::class, "deployment_id");
    }
}