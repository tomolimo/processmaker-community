<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbeRequest extends Model
{
    protected $table = "ABE_REQUESTS";

    public $timestamps = false;

    /**
     * Relation between application
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function application()
    {
        return $this->hasOne(Application::class, 'APP_UID', 'APP_UID');
    }

    /**
     * Relation between abeConfiguration
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function abeConfiguration()
    {
        return $this->hasOne(AbeConfiguration::class, 'ABE_UID', 'ABE_UID');
    }
}
