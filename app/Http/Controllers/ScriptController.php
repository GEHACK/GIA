<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Script;
use App\Models\Room;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class ScriptController extends CrudController {
    protected static $name = 'script';
    protected static $model = Script::class;
}