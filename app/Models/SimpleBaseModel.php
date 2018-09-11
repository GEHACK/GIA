<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;
use Webpatser\Uuid\Uuid;

class SimpleBaseModel extends Model {
    use ValidatingTrait;

    // Determines if a failed save/reinstate should result in an automatic error. @ValidatingTrait
    protected $rules = [];
    public $incrementing = false;
    public $timestamps = true;
    protected $dateFormat = "Y-m-d\TH:i:sP";
    protected $dates = ['deleted_at'];
}
