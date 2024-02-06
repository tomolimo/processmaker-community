<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Process
 * @package ProcessMaker\Model
 *
 * Represents a business process object in the system.
 */
class SubApplication extends Model
{
    // Set our table name
    protected $table = 'SUB_APPLICATION';
    // No timestamps
    public $timestamps = false;
    // Primary key
    protected $primaryKey = 'APP_UID';
    // The IDs are auto-incrementing
    public $incrementing = false;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'SA_STATUS' => '',
        'SA_VALUES_OUT' => '',
        'SA_VALUES_IN' => '',
        'SA_INIT_DATE' => '',
        'SA_FINISH_DATE' => ''
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'APP_UID',
        'APP_PARENT',
        'DEL_INDEX_PARENT',
        'DEL_THREAD_PARENT',
        'SA_STATUS',
        'SA_VALUES_OUT',
        'SA_VALUES_IN',
        'SA_INIT_DATE',
        'SA_FINISH_DATE'
    ];

}
