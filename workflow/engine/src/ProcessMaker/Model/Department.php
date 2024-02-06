<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Department
 * @package ProcessMaker\Model
 */
class Department extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'DEPARTMENT';
    // We do not store timestamps
    public $timestamps = false;
}
