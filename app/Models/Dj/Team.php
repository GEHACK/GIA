<?php

namespace App\Models\Dj;

use Illuminate\Database\Eloquent\Model;

class Team extends Model {

    protected $connection = 'domjudge';
    protected $table = 'team';

    protected $primaryKey = 'teamid';

    protected $attributes = [
        "id",
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
}