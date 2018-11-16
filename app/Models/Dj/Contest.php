<?php

namespace App\Models\Dj;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model {

    protected $connection = 'domjudge';
    protected $table = 'contest';
    public $timestamps = false;

    protected $primaryKey = 'cid';

    protected $attributes = [
        "name",
        "short_name",
        "starttime",
    ];
}
