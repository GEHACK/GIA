<?php

use Eventix\Http\Model;
use Illuminate\Http\Request;

\App\Http\Controllers\TemplateController::buildRoutes($router);
\App\Http\Controllers\DeploymentController::buildRoutes($router);
\App\Http\Controllers\RoomController::buildRoutes($router);
\App\Http\Controllers\ScriptController::buildRoutes($router);

$router->get("seed", function () {
    // $deployment = \App\Models\Deployment::with('scripts.deployment')->find('DK3sMi1v9yglLfH1ge29raTeq5YAjTnB');
    //
    // return $deployment->getWith();
    //
    // $arrs = [];
    // foreach ($deployment->getWith() as $rel)
    //     if (is_subclass_of($cn = get_class($deployment->{$rel}()->getRelated()), Model::class))
    //         $arrs[$rel] = $cn::getFrontendRules();
    //
    // return $arrs;

    $map = [
        "rooms"       => \App\Models\Room::with('deployments.scripts')->orderBy("name")->get(),
        "teams"       => \App\Models\Dj\Team::with('users')->get(),
        "deployments" => \App\Models\Deployment::whereNull('room_id')->with('scripts')->get(),
        "users"       => \App\Models\Dj\User::whereNull('teamid')->get()
    ];

    $map["rooms"]["__rules"] = \App\Models\Room::getFrontendRules();
    $map["teams"]["__rules"] = \App\Models\Dj\Team::getFrontendRules();
    $map["deployments"]["__rules"] = \App\Models\Deployment::getFrontendRules();
    $map["users"]["__rules"] = \App\Models\Dj\User::getFrontendRules();

    return $map;
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

$router->get("key", function () {
    return \Helpers::getKey();
});

\App\Http\Controllers\OpsController::buildRoutes($router);


// \App\Http\Controllers\AssignmentController::buildRoutes($router);
// \App\Http\Controllers\PixieController::buildRoutes($router);
// \App\Http\Controllers\RoomController::buildRoutes($router);
