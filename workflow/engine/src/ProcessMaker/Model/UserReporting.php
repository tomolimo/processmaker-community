<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReporting extends Model
{
    use HasFactory;

    protected $table = "USR_REPORTING";
    public $timestamps = false;
}