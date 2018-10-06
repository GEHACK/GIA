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
}
