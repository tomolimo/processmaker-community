<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BpmnProcess extends Model
{
    use HasFactory;

    protected $table = 'BPMN_PROCESS';
    public $timestamps = false;

}
