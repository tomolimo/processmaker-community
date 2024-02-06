<?php

namespace ProcessMaker\Office365OAuth;

use League\OAuth2\Client\Provider\GenericProvider;
use ProcessMaker\EmailOAuth\EmailBase;
use ProcessMaker\GmailOAuth\GmailOAuth;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

class Office365OAuth
{

    use EmailBase;

    const URL_AUTHORIZE = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    const URL_ACCESS_TOKEN = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    const SMTP_SCOPE = 'https://outlook.office.com/SMTP.Send';

    private $options = [
        'response_mode' => 'query',
        'prompt' => 'consent',
        // Scopes requested in authentication
        'scope' => 'offline_access https://outlook.office.com/SMTP.Send https://graph.microsoft.com/Mail.ReadWrite'
    ];

    /**
     * Constructor of the class.
     */
    public function __construct()
    {
        $this->setServer("smtp.office365.com");
        $this->setPort(587);
    }

    /**
     * Get $options property.
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get a Microsoft object, this may vary depending on the service provider.
     * @return Google_Client
     */
    public function getOffice365Client()
    {
        $provider = new Microsoft([
            'clientId' => $this->getClientID(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri' => $this->getRedirectURI(),
            'urlAuthorize' => self::URL_AUTHORIZE,
            'urlAccessToken' => self::URL_ACCESS_TOKEN,
            'accessType' => 'offline'
        ]);
        $provider->defaultScopes = $this->options['scope'];
        return $provider;
    }
}
