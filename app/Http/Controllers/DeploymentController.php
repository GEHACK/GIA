<?php

namespace App\Http\Controllers;

use App\Jobs\GetHomedir;
use App\Models\Deployment;
use App\Models\Dj\Contest;
use App\Models\Contest as pContest;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class DeploymentController extends CrudController {
    protected static $model = Deployment::class;

    protected static $routes = [
        "{id}/touch" => ["touch" => "POST"],
        "loc/{ip}"   => ["loc" => "GET"],
    ];

    public function touch($id) {
        Deployment::find($id)->touch();
    }

    public function loc($ip) {
        $depl = Deployment::with('room')->where("ip", $ip)->first();
        if (is_null($depl))
            abort(404);

        $po = $depl->getRoomPosition();

        return sprintf("Room: %s, Row: %s, Col: %s", $po["room"], $po["row"], $po["column"]);
    }
}
