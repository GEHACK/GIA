<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Dj\Contest;
use App\Models\Contest as pContest;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class ContestController extends CrudController {

    protected static $name = 'template';
    protected static $model = Contest::class;

    protected static $routes = [
        "tts"                  => ["timeToStart" => "GET"],
        "register/laptop/{id}" => ["registerDeployment" => "POST"],
        "register/{cid}"       => ["registerContest" => "POST"],
        "key"                  => ["keyRetrieve" => "GET"],
    ];

    protected static $blacklist = self::map;

    public function registerContest($id) {
        $pc = pContest::create(["cid" => $id]);

        if ($pc->isInvalid())
            abort(406, $pc->getErrors());

        return $pc;
    }

    public function keyRetrieve() {
        return \Helpers::getKey();
    }

    public function registerDeployment(Request $r, $id) {
        $depl = Deployment::find($id);

        $attrs = [
            "id"       => $id,
            "ip"       => $r->ip(),
            "proxy_ip" => null,
        ];

        if ($r->hasHeader("X-Real-IP")) {
            $attrs["proxy_ip"] = $attrs["ip"];
            $attrs["ip"] = $r->header("X-Real-IP");
        }

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
            $depl = Deployment::create($attrs);
            if ($depl->isInvalid())
                abort(406, $depl->getErrors());

            // Remove old hosts
            $builder = Deployment::where('id', '<>', $depl->id)->where('ip', $depl->ip);
            if (is_null($attrs["proxy_ip"])){
                $builder = $builder->whereNull('proxy_ip');
            } else {
                $builder = $builder->where('proxy_ip', $depl->proxy_ip);
            }

            $builder->delete();
        }

        return $depl;
    }

    public function timeToStart(Request $r) {
        if (is_null($cHash = $r->header("CONTEST_HASH")) || is_null($contest = pContest::where("hash", $cHash)->first()))
            $this->errorNotFound();

        $now = $_SERVER["REQUEST_TIME"];
        $a = Contest::find($contest->cid);

        $tta = $a->activatetime - $now;
        $tte = $a->endtime - $now;
        $tts = $tta > 0 || $tte < 0 ? 100 : $a->starttime - $now;

        return [
            "tta" => max(-1, $tta),
            "tts" => max(0, $tts),
            "tte" => max(-1, $tte),
        ];
    }
}
