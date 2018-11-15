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

    protected $hidden = [
        'teamid',
        'userid'
    ];

    protected $appends = [
        "team_id",
        'guid',
    ];

    public function getGuidAttribute() {
        return $this->userid;
    }

    public function getTeamIdAttribute() {
        return $this->getOriginal('teamid');
    }

    public function team() {
        return $this->belongsTo(Team::class, 'teamid');
    }
}