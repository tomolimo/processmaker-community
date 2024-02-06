<?php

namespace ProcessMaker\Services\OAuth2;

use OAuth2\Server;

/**
 * Extended class where the properties are correctly initialized, compatibility with PHP 7.3.x
 */
class OAuth2Server extends Server
{
    protected $responseTypes = [];
}
