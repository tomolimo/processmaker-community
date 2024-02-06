<?php

use PHPMailer\PHPMailer\PHPMailer;

class mailer
{

    /**
     * instanceMailer
     *
     * @return object(PHPMailer) $mailer
     */
    function instanceMailer ()
    {
        $mailer = new PHPMailer();
        $mailer->PluginDir = PATH_THIRDPARTY . 'phpmailer/';
        //DEFAULT CONFIGURATION
        $mailer->Mailer = 'mail';
        $mailer->Host = "";
        $mailer->SMTPAuth = false;
        $mailer->Username = "";
        $mailer->Password = "";
        $mailer->Timeout = 30;
        $mailer->CharSet = 'utf-8';
        $mailer->Encoding = 'base64';
        if (defined( 'MAIL_MAILER' )) {
            $mailer->Mailer = MAIL_MAILER;
        }
        if (defined( 'MAIL_HOST' )) {
            $mailer->Host = MAIL_HOST;
        }
        if (defined( 'MAIL_SMTPAUTH' )) {
            $mailer->SMTPAuth = MAIL_SMTPAUTH;
        }
        if (defined( 'MAIL_USERNAME' )) {
            $mailer->Username = MAIL_USERNAME;
        }
        if (defined( 'MAIL_PASSWORD' )) {
            $mailer->Password = MAIL_PASSWORD;
        }
        if (defined( 'MAIL_TIMEOUT' )) {
            $mailer->Timeout = MAIL_TIMEOUT;
        }
        if (defined( 'MAIL_CHARSET' )) {
            $mailer->CharSet = MAIL_CHARSET;
        }
        if (defined( 'MAIL_ENCODING' )) {
            $mailer->Encoding = MAIL_ENCODING;
        }
        return $mailer;
    }

    /* ARPA INTERNET TEXT MESSAGES
     * Returns an array with the "name" and "email" of an ARPA type
     * email $address.
     * @author David Callizaya
     */
    function arpaEMAIL ($address)
    {
        $arpa = array ();
        preg_match( "/([^<>]*)(?:\<([^<>]*)\>)?/", $address, $matches );
        $isEmail = preg_match( "/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $matches[1] );
        $arpa['email'] = ((isset( $matches[2] )) && ($matches[2] != '')) ? $matches[2] : (($isEmail) ? $matches[1] : '');
        $arpa['name'] = ($isEmail) ? '' : $matches[1];
        return $arpa;
    }

    /**
     * sendTemplate
     *
     * @param string $from default value empty
     * @param string $target default value default value empty
     * @param string $cc default value default value empty
     * @param string $bcc default value default value empty
     * @param string $subject default value empty
     * @param array $Fields
     * @param string $templateFile default value "empty.html"
     * @param array $attachs
     * @param boolean $plainText default value false
     * @param boolean $returnContent default value false
     *
     * @return object $content or $result
     */
    function sendTemplate ($from = "", $target = "", $cc = "", $bcc = "", $subject = "", $Fields = array(), $templateFile = "empty.html", $attachs = array(), $plainText = false, $returnContent = false)
    {
        // Read the content of the TemplateFile
        $fp = fopen( PATH_HTMLMAIL . $templateFile, "r" );
        $content = fread( $fp, filesize( PATH_HTMLMAIL . $templateFile ) );
        fclose( $fp );
        //Replace the @@Fields with the $Fields array.
        $content = mailer::replaceFields( $Fields, $content );
        //Compatibility  with class.Application
        if ($attachs === 'FALSE') {
            return $content;
        }
            //Create the alternative body (text only)
            //$h2t =& new html2text($content);
        $text = ''; //$h2t->get_text();
        //Prepate el phpmailer
        $mailer = mailer::instanceMailer();
        $arpa = mailer::arpaEMAIL( $from );
        $mailer->From = $arpa['email'] == '' ? $mailer->defaultEMail : $arpa['email'];
        $mailer->FromName = $arpa['name'];
        $arpa = mailer::arpaEMAIL( $target );
        $mailer->AddAddress( $arpa['email'], $arpa['name'] );
        $mailer->AddCC( $cc );
        $mailer->AddBCC( $bcc );
        $mailer->Subject = $subject;
        if ($plainText) {
            $content = $text;
        }
        if ($content === '') {
            $content = 'empty';
        }
        $mailer->Body = $content;
        //$mailer->AltBody = $text;
        $mailer->isHTML( ! $plainText );
        //Attach the required files
        if (is_array( $attachs ))
            if (sizeof( $attachs ) > 0)
                foreach ($attachs as $aFile)
                    $mailer->AddAttachment( $aFile, basename( $aFile ) );
            //Send the e-mail.
        for ($r = 1; $r <= 4; $r ++) {
            $result = $mailer->Send();
            if ($result) {
                break;
            }
        }
        //unset($h2t);
        if ($result && $returnContent) {
            return $content;
        }
        return $result;
    }

    /**
     * sendHtml
     *
     * @param string $from default value empty
     * @param string $target default value empty
     * @param string $cc default value empty
     * @param string $bcc default value empty
     * @param string $subject default value empty
     * @param array $Fields
     * @param string $content default value empty
     * @param array $attachs default value empty
     * @param string $plainText default false
     * @param boolean $returnContent default value false
     *
     * @return object $result
     */
    function sendHtml ($from = "", $target = "", $cc = "", $bcc = "", $subject = "", $Fields = array(), $content = "", $attachs = array(), $plainText = false, $returnContent = false)
    {
        //Replace the @@Fields with the $Fields array.
        $content = mailer::replaceFields( $Fields, $content );
        //Create the alternative body (text only)
        //$h2t =& new html2text($content);
        $text = ''; //$h2t->get_text();
        //Prepate el phpmailer
        $mailer = mailer::instanceMailer();
        $arpa = mailer::arpaEMAIL( $from );
        $mailer->From = $arpa['email'] == '' ? $mailer->defaultEMail : $arpa['email'];
        $mailer->FromName = $arpa['name'];
        $arpa = mailer::arpaEMAIL( $target );
        $mailer->AddAddress( $arpa['email'], $arpa['name'] );
        $mailer->AddCC( $cc );
        $mailer->AddBCC( $bcc );
        $mailer->Subject = $subject;
        if ($plainText) {
            $content = $text;
        }
        if ($content === '') {
            $content = 'empty';
        }
        $mailer->Body = $content;
        //$mailer->AltBody = $text;
        $mailer->isHTML( ! $plainText );
        //Attach the required files
        if (is_array( $attachs ))
            if (sizeof( $attachs ) > 0)
                foreach ($attachs as $aFile)
                    $mailer->AddAttachment( $aFile, basename( $aFile ) );
            //Send the e-mail.
        for ($r = 1; $r <= 4; $r ++) {
            $result = $mailer->Send();
            if ($result) {
                break;
            }
        }
        //unset($h2t);
        if ($result && $returnContent) {
            return $content;
        }
        return $result;
    }

    /**
     * sendText
     *
     * @param string $from default value empty
     * @param string $target default value empty
     * @param string $cc default value empty
     * @param string $bcc default value empty
     * @param string $subject default value empty
     * @param string $content default value empty
     * @param array $attachs
     *
     * @return object $result
     */
    function sendText ($from = "", $target = "", $cc = "", $bcc = "", $subject = "", $content = "", $attachs = array())
    {
        //Prepate el phpmailer
        $mailer = mailer::instanceMailer();
        $arpa = mailer::arpaEMAIL( $from );
        $mailer->From = $arpa['email'] == '' ? $mailer->defaultEMail : $arpa['email'];
        $mailer->FromName = $arpa['name'];
        $arpa = mailer::arpaEMAIL( $target );
        $mailer->AddAddress( $arpa['email'], $arpa['name'] );
        $mailer->AddCC( $cc );
        $mailer->AddBCC( $bcc );
        $mailer->Subject = $subject;
        if ($content === '') {
            $content = 'empty';
        }
        $mailer->Body = $content;
        $mailer->AltBody = $content;
        $mailer->isHTML( false );
        //Attach the required files
        if (sizeof( $attachs ) > 0) {
            foreach ($attachs as $aFile) {
                $mailer->AddAttachment( $aFile, basename( $aFile ) );
            }
        }
            //Send the e-mail.
        for ($r = 1; $r <= 4; $r ++) {
            $result = $mailer->Send();
            if ($result) {
                break;
            }
        }
        return $result;
    }

    /**
     * replaceFields
     *
     * @param array $Fields
     * @param string $content default value string empty
     *
     * @return none
     */
    function replaceFields ($Fields = array(), $content = "")
    {
        return G::replaceDataField( $content, $Fields );
    }

    /**
     * html2text
     *
     * empty
     *
     * @return none
     */
    function html2text ()
    {
        //$h2t =& new html2text($content);
        //return $h2t->get_text();
    }
}

