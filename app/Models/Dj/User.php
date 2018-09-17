<?php

namespace App\Models\Dj;

use Illuminate\Database\Eloquent\Model;

class User extends Model {

    protected $connection = 'domjudge';
    protected $table = 'user';

    protected $primaryKey = 'userid';
    protected $with = ['team'];

    public $timestamps = false;

    protected $attributes = [
        "username",
        "name",
        "email",
        "last_login",
        "last_ip_address",
        "password",
        "ip_address",
        "enabled",
    ];

    public function team() {
        return $this->belongsTo(Team::class, 'teamid');
    }
}