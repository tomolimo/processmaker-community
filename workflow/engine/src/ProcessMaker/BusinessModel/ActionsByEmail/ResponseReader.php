<?php

namespace ProcessMaker\BusinessModel\ActionsByEmail;

use AbeConfigurationPeer;
use AbeResponses;
use ActionsByEmailCoreClass;
use AppDelegation;
use AppNotes;
use Bootstrap;
use Cases;
use Criteria;
use EmailServerPeer;
use Exception;
use G;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use PMLicensedFeatures;
use ProcessMaker\BusinessModel\ActionsByEmail;
use ProcessMaker\BusinessModel\EmailServer;
use ProcessMaker\ChangeLog\ChangeLog;
use ResultSet;
use WsBase;

/**
 * Class ResponseReader
 * @package ProcessMaker\BusinessModel\ActionsByEmail
 */
class ResponseReader
{
}
