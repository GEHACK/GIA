<?php

namespace App\Models;

class ExecJob extends BaseModel {

    protected $table = 'execJobs';

    protected $fillable = [
        "type",
        "value",
        "status",
        "result",
    ];

    protected $rules = [
        "type"   => "required|in:percentage,absolute",
        "status" => "required|in:submitted,running,finished,terminated",
    ];

    public function deployment() {
        return $this->belongsTo(Deployment::class, "deployment_id");
    }
}