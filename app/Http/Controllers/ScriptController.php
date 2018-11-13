<?php

namespace App\Http\Controllers;

use App\Models\Script;
use Eventix\Http\CrudController;

class ScriptController extends CrudController {
    protected static $name = 'script';
    protected static $model = Script::class;
}