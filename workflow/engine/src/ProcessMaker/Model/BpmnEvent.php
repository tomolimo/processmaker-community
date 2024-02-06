<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BpmnEvent extends Model
{
    use HasFactory;

    protected $table = 'BPMN_EVENT';
    public $timestamps = false;

}
