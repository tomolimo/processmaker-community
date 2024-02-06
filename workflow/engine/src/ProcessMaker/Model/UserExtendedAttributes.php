<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExtendedAttributes extends Model
{
    use HasFactory;

    protected $table = "USER_EXTENDED_ATTRIBUTES";
    protected $primaryKey = "UEA_ID";
    public $incrementing = true;
    public $timestamps = false;

}
