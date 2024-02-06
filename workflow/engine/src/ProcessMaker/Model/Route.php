<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Process
 * @package ProcessMaker\Model
 *
 * Represents a business process object in the system.
 */
class Route extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'ROUTE';

    public $timestamps = false;
}