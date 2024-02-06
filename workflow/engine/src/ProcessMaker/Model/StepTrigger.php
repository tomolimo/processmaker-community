<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class StepTrigger extends Model
{
    protected $table = 'STEP_TRIGGER';
    protected $primaryKey = 'STEP_UID';
    public $incrementing = false;
    public $timestamps = false;

}
