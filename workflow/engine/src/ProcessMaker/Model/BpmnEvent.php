<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BpmnEvent extends Model
{
    protected $table = 'BPMN_EVENT';
    public $timestamps = false;

}
