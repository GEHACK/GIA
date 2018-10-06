<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\ExecJob;
use App\Models\Room;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class PixieController extends CrudController {
    protected static $name = "pixie";
    protected static $routes = [
        "preseed"      => ["preseed" => "GET"],
        "firstboot"    => ["firstboot" => "GET"],
        "script/{sid}" => [
            "update" => ["updateScript" => "POST"],
            "finish" => ["finishScript" => "POST"],
        ],
    ];

    protected static $blacklist = self::map;

    public function preseed(Request $r) {
        $this->ensureDeployment($r);

        return view("preseed");
    }

    public function firstboot(Request $r) {
        $depl = $this->ensureDeployment($r);

        $script = $depl->scripts()->create([
            "type"   => "percentage",
            "status" => "submitted",
            "value"  => 0,
        ]);

        return view("firstboot", ["script" => $script]);
    }

    public function ping(Request $r) {
        $this->ensureDeployment($r);
    }

    private function ensureDeployment(Request $r) {
        $attrs = [
            "ip"       => $r->ip(),
            "proxy_ip" => null,
        ];

        if ($r->hasHeader("X-Real-IP")) {
            $attrs["proxy_ip"] = $attrs["ip"];
            $attrs["ip"] = $r->header("X-Real-IP");
        }

        $depl = Deployment::where($attrs)->first();

        if (!is_null($depl)) {
            $depl->fill($attrs);

            if ($depl->isDirty()) {
                if ($depl->isInvalid())
                    abort(406, $depl->getErrors());

                $depl->save();
            } else {
                $depl->touch();
            }
        } else {
            $attrs["id"] = str_random(32);
            $depl = Deployment::create($attrs);
            if ($depl->isInvalid())
                abort(406, $depl->getErrors());

            // Attempt to find an available room
            $room = Room::select("rooms.*", DB::raw("count(rd.*) as cnt"))
                ->leftjoin("room_deployment as rd", "rd.room_id", "=", "rooms.guid")
                ->groupBy("rooms.guid")
                ->having(\DB::raw("cnt < rooms.columns * rooms.rows"))
                ->first();

            if (!is_null($room))
                $room->deployments()->attach($depl);

            // Remove old hosts
            $builder = Deployment::where('id', '<>', $depl->id)->where('ip', $depl->ip);
            if (is_null($attrs["proxy_ip"])) {
                $builder = $builder->whereNull('proxy_ip');
            } else {
                $builder = $builder->where('proxy_ip', $depl->proxy_ip);
            }

            $builder->delete();
        }

        return $depl;
    }

    public function updateScript($sid, Request $r) {
        $script = ExecJob::findOrFail($sid);
        $script->update([
            "status" => "running",
            "value"  => $r->getContent(),
        ]);

        if ($script->isInvalid())
            abort(406, $script->getErrors());

        return $script;
    }

    public function finishScript($sid, Request $r) {
        $script = ExecJob::findOrFail($sid);

        $script->update([
            "status" => "finished",
            "result"  => $r->getContent(),
        ]);

        if ($script->isInvalid())
            abort(406, $script->getErrors());

        return $script;
    }
}