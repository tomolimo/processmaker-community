<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepTrigger extends Model
{
    use HasFactory;

    protected $table = 'STEP_TRIGGER';
    protected $primaryKey = 'STEP_UID';
    public $incrementing = false;
    public $timestamps = false;

}
