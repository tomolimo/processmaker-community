<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'CONFIGURATION';
    // Set the PK
    protected $primaryKey = ['CFG_UID', 'OBJ_UID', 'PRO_UID', 'USR_UID', 'APP_UID'];
    // No timestamps
    public $timestamps = false;
    
    public $incrementing = false;

    protected $fillable = ['CFG_UID', 'OBJ_UID', 'CFG_VALUE', 'PRO_UID', 'USR_UID', 'APP_UID'];
}
