<?php

namespace App\Models\Dj;


use Eventix\Http\Model;

class Team extends Model {
    protected $connection = 'domjudge';
    protected $table = 'team';

    protected $primaryKey = 'teamid';

    protected $attributes = [
        "external_id",
        "name",
        "category_id",
        "afflid",
        "enabled",
        "members",
        "room",
        "comments",
        "judging_last_started",
        "teampage_first_visited",
        "hostname",
        "penalty",
    ];

    protected $hidden = ['teamid'];

    protected $appends = ['guid'];
    public function getGuidAttribute() {
        return $this->teamid;
    }

    public function users() {
        return $this->hasMany(User::class, 'teamid');
    }
}