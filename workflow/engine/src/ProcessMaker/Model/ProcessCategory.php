<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProcessCategory
 * @package ProcessMaker\Model
 *
 * Represents a process category object in the system.
 */
class ProcessCategory extends Model
{
    // Set our table name
    protected $table = 'PROCESS_CATEGORY';

    public $timestamps = false;
}
