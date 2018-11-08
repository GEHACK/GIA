<?php

namespace App\Models;

use App\Models\Dj\User;

class Deployment extends BaseModel {
    protected $connection = "mysql";

    protected $rules = [
        "ip" => "required|ipv4",
        "room_id" => "nullable|exists:rooms,guid",
    ];

    protected $with = ["scripts"];

    protected $fillable = [
        "userid",
        "proxy_ip",
        "room_id",
        "ip",
    ];

    public function user() {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function room() {
        return $this->belongsToMany(Room::class, "room_deployment", "deployment_id", "room_id");
    }

    public function scripts() {
        return $this->hasMany(Script::class, "deployment_id");
    }

    public static function getRootAttacher() {
        $res = Room::select("rooms.*", \DB::raw("count(d.guid) as cnt"))
            ->leftjoin("deployments as d", "d.room_id", "=", "rooms.guid")
            ->groupBy("rooms.guid")
            ->havingRaw("cnt < rooms.columns * rooms.rows")
            ->orderBy("name")
            ->first();

        if (is_null($res))
            return $res;

        return $res->deployments();
    }
}