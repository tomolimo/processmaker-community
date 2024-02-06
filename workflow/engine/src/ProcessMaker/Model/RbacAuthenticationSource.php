<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RbacAuthenticationSource extends Model
{
    protected $table = "RBAC_AUTHENTICATION_SOURCE";
    public $incrementing = false;
    public $timestamps = false;

}
