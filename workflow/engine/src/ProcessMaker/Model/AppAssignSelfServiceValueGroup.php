<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class AppAssignSelfServiceValueGroup extends Model
{
    protected $table = 'APP_ASSIGN_SELF_SERVICE_VALUE_GROUP';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Return the appSelfServiceValue this belongs to
    */
    public function appSelfService()
    {
        return $this->belongsTo(AppAssignSelfServiceValue::class, 'ID', 'ID');
    }
}

