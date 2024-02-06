<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseManager extends Model
{
    use HasFactory;

    protected $table = "LICENSE_MANAGER";
    protected $primaryKey = "LICENSE_UID";
    public $incrementing = false;
    public $timestamps = false;

}
