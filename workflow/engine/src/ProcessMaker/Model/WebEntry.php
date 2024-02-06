<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class WebEntry extends Model
{
    // Set our table name
    protected $table = 'WEB_ENTRY';
    protected $primaryKey = 'WE_UID';

    // We do not have create/update timestamps for this table
    public $timestamps = false;
}
