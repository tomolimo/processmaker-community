<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashletInstance extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = "DASHLET_INSTANCE";
    // Set the PK
    protected $primaryKey = 'DAS_INS_UID';
    // No timestamps
    public $timestamps = false;
}
