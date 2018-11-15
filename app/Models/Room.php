<?php

namespace App\Models;

use App\Models\Dj\User;
use Eventix\Http\HasOrderableChildren;

class Room extends SimpleBaseModel {

    use HasOrderableChildren;

    protected $connection = "mysql";
    protected $primaryKey = 'guid';

    protected static $rules = [
        'name' => ["required","unique:rooms,name"]
    ];

    protected $fillable = [
        "name",
        "rows",
        "columns",
        "offset"
    ];

    protected $casts = [
        "rows" => "int",
        "columns" => "int",
    ];

    protected $hidden = ['contest_id'];

    public function contest() {
        return $this->belongsTo(Contest::class, "contest_id");
    }

    public function deployments() {
        return $this->hasManyOrdered(Deployment::class, "room_id");
    }
}