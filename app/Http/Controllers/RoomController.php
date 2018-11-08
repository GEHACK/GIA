<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Script;
use App\Models\Room;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class RoomController extends CrudController {
    protected static $model = Room::class;
    protected static $relations = ['deployments'];
}