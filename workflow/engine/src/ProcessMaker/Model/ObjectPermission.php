<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectPermission extends Model
{
    use HasFactory;

    protected $table = "OBJECT_PERMISSION";
    protected $primaryKey = 'OP_UID';
    public $timestamps = false;
}
