<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    // Set our table name
    protected $table = 'CONFIGURATION';
    // Set the PK
    protected $primaryKey = ['CFG_UID', 'OBJ_UID', 'PRO_UID', 'USR_UID', 'APP_UID'];
    // No timestamps
    public $timestamps = false;
    
    public $incrementing = false;
}
