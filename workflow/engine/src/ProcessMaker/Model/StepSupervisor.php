<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepSupervisor extends Model
{
    use HasFactory;

    protected $table = 'STEP_SUPERVISOR';
    protected $primaryKey = 'STEP_UID';
    public $incrementing = false;
    public $timestamps = false;

}
