<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class ProcessFiles extends Model
{
    protected $table = 'PROCESS_FILES';
    protected $primaryKey = 'PRF_UID';
    public $incrementing = false;
    public $timestamps = false;

}
