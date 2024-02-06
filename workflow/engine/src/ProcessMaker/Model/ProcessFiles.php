<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessFiles extends Model
{
    use HasFactory;

    protected $table = 'PROCESS_FILES';
    protected $primaryKey = 'PRF_UID';
    public $incrementing = false;
    public $timestamps = false;

}
