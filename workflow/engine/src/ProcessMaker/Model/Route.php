<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Process
 * @package ProcessMaker\Model
 *
 * Represents a business process object in the system.
 */
class Route extends Model
{
    // Set our table name
    protected $table = 'ROUTE';

    public $timestamps = false;
}