<?php

namespace App\Models;

use App\Models\Dj\User;

class Deployment extends BaseModel {
    protected $connection = "mysql";

    protected static $rules = [
        "ip"      => ["required", "ipv4"],
        "room_id" => ["nullable", "exists:rooms,guid"],
        "user_id" => ["nullable"],
    ];

    protected $with = ["scripts"];

    protected $fillable = [
        "proxy_ip",
        "room_id",
        "userid",
	"ip",
	'numerator',
	'denominator',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function room() {
        return $this->belongsTo(Room::class, "room_id", 'guid');
    }

    public function scripts() {
        return $this->hasMany(Script::class, "deployment_id")->orderBy('created_at');
    }

    public static function getRootAttacher() {
        $res = Room::select("rooms.*", \DB::raw("count(d.guid) as cnt"))
            ->leftjoin("deployments as d", "d.room_id", "=", "rooms.guid")
            ->groupBy("rooms.guid")
            ->havingRaw("cnt < rooms.columns * rooms.rows - rooms.offset")
            ->orderBy("name")
            ->first();

        if (is_null($res))
            return $res;

        return $res->deployments();
    }

    public function getRoomPosition() {
        $count = \DB::table('deployments')
            ->selectRaw('count(guid) as c')
            ->where(\DB::raw('cast(numerator / denominator as decimal(16,8))'), '<', $this->numerator / $this->denominator)
            ->where('room_id', $this->room_id)
            ->where('guid', '<>', $this->guid)
            ->first()->c;

        $room = $this->room;
        if (is_null($room))
            return [
                "room"   => "unassigned",
                "column" => -1,
                "row"    => -1,
            ];

        $colMap = [];
        switch ($room->columns) {
            case 1:
                $colMap = [""];
                break;
            case 2:
                $colMap = ["l", "r"];
                break;
            case 3:
                $colMap = ["l", "m", "r"];
                break;
            default:
                for ($i = 0; $i < $room->columns; $i++) {
                    $colMap[$i] = str_repeat(".", $i) . "X" . str_repeat(".", $room->columns - 1 - $i);
                }
        }

        return [
            "room"   => $room->name,
            "column" => $colMap[$count % $room->columns],
            "row"    => floor($count / $room->columns) + 1,
        ];
    }
}
