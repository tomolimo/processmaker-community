<?php

namespace ProcessMaker\Model;

use \Illuminate\Database\Eloquent\Model;

/**
 * Class TaskScheduler
 * @package ProcessMaker\Model
 *
 * Represents a dynaform object in the system.
 */
class TaskScheduler extends Model
{
    protected $table = 'SCHEDULER';
    public $timestamps = true;
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}
