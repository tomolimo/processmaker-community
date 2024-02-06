<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class OauthClients extends Model
{
    protected $table = "OAUTH_CLIENTS";
    protected $primaryKey = "CLIENT_ID";
    public $incrementing = false;
    public $timestamps = false;

}
