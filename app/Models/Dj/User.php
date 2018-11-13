<?php

namespace App\Models\Dj;

use Eventix\Http\Model;

class User extends Model {

    protected $connection = 'domjudge';
    protected $table = 'user';

    protected $primaryKey = 'userid';

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

    protected $appends = ['id'];
    public function getIdAttribute() {
        return $this->userid;
    }

    public function team() {
        return $this->belongsTo(Team::class, 'teamid');
    }
}