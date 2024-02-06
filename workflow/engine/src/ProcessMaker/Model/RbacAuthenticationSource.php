<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RbacAuthenticationSource extends Model
{
    use HasFactory;

    protected $table = "RBAC_AUTHENTICATION_SOURCE";
    public $incrementing = false;
    public $timestamps = false;

}
