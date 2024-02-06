<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'TRANSLATION';
    protected $primaryKey = ['TRN_CATEGORY', 'TRN_ID', 'TRN_LANG'];
    public $incrementing = false;
    public $timestamps = false;

}
