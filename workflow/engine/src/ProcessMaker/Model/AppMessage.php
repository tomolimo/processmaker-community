<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppMessage extends Model
{
    use HasFactory;

    protected $table = 'APP_MESSAGE';
    public $timestamps = false;

}
