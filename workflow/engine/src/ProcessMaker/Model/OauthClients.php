<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthClients extends Model
{
    use HasFactory;

    protected $table = "OAUTH_CLIENTS";
    protected $primaryKey = "CLIENT_ID";
    public $incrementing = false;
    public $timestamps = false;

}
