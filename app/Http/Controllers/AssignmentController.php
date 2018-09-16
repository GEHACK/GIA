<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Dj\Contest;
use App\Models\Contest as pContest;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class AssignmentController extends CrudController {

    protected static $name = 'assign';

    protected static $routes = [
        "setup"                  => ["getSetup" => "GET"],
        "greeter" => ["getGreeter" => "GET"],
    ];

    protected static $blacklist = self::map;

    public function getSetup() {
        return view('setup');
    }

    public function getGreeter() {
        return view('greeter');
    }
}
