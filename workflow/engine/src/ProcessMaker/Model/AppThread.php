<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class AppThread extends Model
{
    protected $table = 'APP_THREAD';
    // We do not have create/update timestamps for this table
    public $timestamps = false;
}