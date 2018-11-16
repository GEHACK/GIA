<?php

use Illuminate\Http\Request;

\App\Http\Controllers\TemplateController::buildRoutes($router);
\App\Http\Controllers\DeploymentController::buildRoutes($router);
\App\Http\Controllers\RoomController::buildRoutes($router);
\App\Http\Controllers\ScriptController::buildRoutes($router);

$router->get('test/{id}', function($deplId) {
    $depls = \App\Models\Room::find('mf8')->deployments()->get();
    // $depls = \App\Models\Deployment::all();

    $arrs = [];
    foreach ($depls as $depl) {
        $po = $depl->getRoomPosition();
        $arrs[$depl->guid] = sprintf("Room: %s, Row: %s, Col: %s", $po["room"], $po["row"], $po["column"]);
    }

    return $arrs;
});

$router->get("seed", function () {
    $map = [
        "teams"       => \App\Models\Dj\Team::with('users')->get(),
        "users"       => \App\Models\Dj\User::all(),
        "rooms"       => \App\Models\Room::with('deployments.scripts')->orderBy("name")->get(),
        "deployments" => \App\Models\Deployment::whereNull('room_id')->with('scripts')->get(),
    ];

    $map["rooms"]["__rules"] = \App\Models\Room::getFrontendRules();
    $map["teams"]["__rules"] = \App\Models\Dj\Team::getFrontendRules();
    $map["deployments"]["__rules"] = \App\Models\Deployment::getFrontendRules();
    $map["users"]["__rules"] = \App\Models\Dj\User::getFrontendRules();

    return $map;
});

$router->get("tts", function (Request $r) {
    if (is_null($cHash = $r->header("CONTEST_HASH")) || is_null($contest = \App\Models\Contest::where("hash", $cHash)->first()))
        abort(404);

    $now = $_SERVER["REQUEST_TIME"];
    $a = \App\Models\Dj\Contest::find($contest->cid);

    $tta = $a->activatetime - $now;
    $tte = $a->endtime - $now;
    $tts = $tta > 0 || $tte < 0 ? 100 : $a->starttime - $now;

    return [
        "tta" => max(-1, $tta),
        "tts" => max(0, $tts),
        "tte" => max(-1, $tte),
    ];
});

$router->get("key", function () {
    return \DB::select(\DB::raw('select count(guid) from deployments'));
    return \Helpers::getKey();
});

\App\Http\Controllers\OpsController::buildRoutes($router);


// \App\Http\Controllers\AssignmentController::buildRoutes($router);
// \App\Http\Controllers\PixieController::buildRoutes($router);
// \App\Http\Controllers\RoomController::buildRoutes($router);
