<?php

$router->get('', function() {
    (new \App\Jobs\SetTeamname(\App\Models\Deployment::first()))->handle();
});

\App\Http\Controllers\ContestController::buildRoutes($router);
\App\Http\Controllers\TemplateController::buildRoutes($router);