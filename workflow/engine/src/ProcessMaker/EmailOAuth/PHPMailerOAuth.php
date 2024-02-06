<?php

namespace ProcessMaker\EmailOAuth;

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerOAuth extends PHPMailer
{

    /**
     * Constructor of the class.
     * @param array $options
     */
    public function __construct($options)
    {
        $oauth = new OAuth($options);
        $this->setOAuth($oauth);
    }
}
