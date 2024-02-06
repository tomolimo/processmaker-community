<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmailEvent extends Model
{
    protected $table = 'EMAIL_EVENT';
    public $timestamps = false;

}
