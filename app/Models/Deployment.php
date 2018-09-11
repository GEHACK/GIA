<?php

namespace App\Models;

class Deployment extends SimpleBaseModel {

    protected $rules = [
        'id'     => "required|unique:deployments,id",
        // "userid" => "sometimes|exists:domjudge.user,userid",

        "ip"       => "required|ipv4",
    ];

    protected $fillable = [
        "id",
        "userid",
        "proxy_ip",
        "ip",
    ];
}