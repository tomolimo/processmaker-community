<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BpmnProcess extends Model
{
    protected $table = 'BPMN_PROCESS';
    public $timestamps = false;

}
