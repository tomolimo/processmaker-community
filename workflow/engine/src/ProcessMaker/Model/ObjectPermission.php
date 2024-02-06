<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class ObjectPermission extends Model
{
    protected $table = "OBJECT_PERMISSION";
    protected $primaryKey = 'OP_UID';
    public $timestamps = false;
}
