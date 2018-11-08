<?php

use Illuminate\Http\Request;

\App\Http\Controllers\TemplateController::buildRoutes($router);
\App\Http\Controllers\DeploymentController::buildRoutes($router);
\App\Http\Controllers\RoomController::buildRoutes($router);
\App\Http\Controllers\ScriptController::buildRoutes($router);

$router->get("seed", function() {
    return [
        "rooms" => \App\Models\Room::with('deployments.scripts')->orderBy("name")->get(),
        "teams" => \App\Models\Dj\Team::with('users')->get(),
        "deployments" => \App\Models\Deployment::whereNull('room_id')->with('scripts')->get(),
        "users" => \App\Models\Dj\User::whereNull('teamid')->get()
    ];
});

$router->get("tts", function (Request $r) {
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
});

// \App\Http\Controllers\AssignmentController::buildRoutes($router);
// \App\Http\Controllers\PixieController::buildRoutes($router);
// \App\Http\Controllers\RoomController::buildRoutes($router);
