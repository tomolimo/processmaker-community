<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class BpmnProject extends Model
{
    // Set our table name
    protected $table = 'BPMN_PROJECT';
    protected $primaryKey = 'PRJ_UID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;
}