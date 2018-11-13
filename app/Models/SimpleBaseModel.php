<?php

namespace App\Models;

use Watson\Validating\ValidatingTrait;

class SimpleBaseModel extends \Eventix\Http\Model {
    use ValidatingTrait;
    protected $primaryKey = 'guid';

    // Determines if a failed save/reinstate should result in an automatic error. @ValidatingTrait
    public $incrementing = false;
    public $timestamps = true;
    protected $dateFormat = "Y-m-d\TH:i:sP";
    protected $dates = ['deleted_at'];

    public function getWith() {
        return $this->with;
    }
}
