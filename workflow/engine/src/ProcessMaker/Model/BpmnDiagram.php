<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BpmnDiagram extends Model
{
    use HasFactory;

    protected $table = 'BPMN_DIAGRAM';
    public $timestamps = false;

}
