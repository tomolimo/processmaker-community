<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use G;
use Illuminate\Database\Eloquent\Model;

class ElementTaskRelation extends Model
{
    use HasFactory;

    protected $table = 'ELEMENT_TASK_RELATION';
    protected $primaryKey = 'ETR_UID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;
}