<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class LicenseManager extends Model
{
    protected $table = "LICENSE_MANAGER";
    protected $primaryKey = "LICENSE_UID";
    public $incrementing = false;
    public $timestamps = false;

}
