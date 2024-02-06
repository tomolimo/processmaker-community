<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebEntry extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'WEB_ENTRY';
    protected $primaryKey = 'WE_UID';
    public $incrementing = false;
    // We do not have create/update timestamps for this table
    public $timestamps = false;
}
