<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Dj\Contest;
use App\Models\Contest as pContest;
use Carbon\Carbon;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class TemplateController extends CrudController {

    protected static $name = 'template';
    protected static $model = null;

    protected static $routes = [
        "setup"   => ["getSetup" => "GET"],
        "greeter" => ["getGreeter" => "GET"],
        "preseed" => ["getPreseed" => "GET"],
        "firstboot"    => ["firstboot" => "GET"],
    ];

    protected static $blacklist = self::map;

    public function getSetup() {
        return view('setup');
    }

    public function getGreeter() {
        return view('greeter');
    }

    public function getPreseed(Request $r) {
        $this->ensureDeployment($r);
        return view('preseed');
    }

    public function firstboot(Request $r) {
        $depl = $this->ensureDeployment($r);

        $script = $depl->scripts()->create([
            "type"   => "percentage",
            "status" => "submitted",
            "value"  => 0,
            "name"   => "Setup - " . Carbon::now(),
        ]);

        return view("firstboot", ["script" => $script, 'depl' => $depl]);
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
                $depl->save();
            }
        } else {
            // create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null)
            $request = $r->duplicate($r->request->add($attrs));
            $request->setMethod("POST");
            $request->server->set("REQUEST_URI", "/deployments");
            global $app;
            $resp = $app->dispatch($request);
            $depl = $resp->getOriginalContent();
            return $resp->getOriginalContent()->loadMissing($depl->getWith());
        }

        return $depl;
    }
}
