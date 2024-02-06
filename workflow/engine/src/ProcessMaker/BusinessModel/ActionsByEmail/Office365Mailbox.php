<?php

namespace ProcessMaker\BusinessModel\ActionsByEmail;

use Exception;
use League\OAuth2\Client\Grant\RefreshToken;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use ProcessMaker\Office365OAuth\Office365OAuth;
use stdClass;

/**
 * Class Office365Mailbox
 * @package ProcessMaker\BusinessModel\ActionsByEmail
 */
class Office365Mailbox
{
    const SCOPE = 'https://graph.microsoft.com/Mail.ReadWrite';

    private $service;
    private $messages = [];

    /**
     * Office365Mailbox constructor.
     * @param array $emailSetup
     * @throws Exception
     */
    public function __construct(array $emailSetup)
    {
        // Get client instance
        $office365OAuth = new Office365OAuth();
        $office365OAuth->setClientID($emailSetup['OAUTH_CLIENT_ID']);
        $office365OAuth->setClientSecret($emailSetup['OAUTH_CLIENT_SECRET']);
        $provider = $office365OAuth->getOffice365Client();

        // Get fresh access token
        try {
            $accessToken = $provider->getAccessToken(
                new RefreshToken(),
                [
                    'refresh_token' => $emailSetup['OAUTH_REFRESH_TOKEN'],
                    'scope' => self::SCOPE
                ]
            );
        } catch (Exception $e) {
            throw $e;
        }

        // Set Office365 service instance
        $this->service = new Graph();
        $this->service->setAccessToken($accessToken->getToken());
    }

    /**
     * This function uses Office365 API to perform a search on the mailbox.
     *
     * @param string $criteria
     * @return array
     * @throws Exception
     */
    public function searchMailbox(string $criteria = 'ALL'): array
    {
        // Transform criteria to values accepted by Office365 service
        switch ($criteria) {
            case 'UNSEEN':
                $criteria = 'isRead eq false';
                break;
            case 'SEEN':
                $criteria = 'isRead eq true';
                break;
            default:
                $criteria = '';
        }

        // Initialize variables
        $nextLink = '';
        $mailsIds = [];

        // Get unread user's messages
        try {
            do {
                // First time the link is generated, by default 100 results per call
                if (empty($nextLink)) {
                    $nextLink = '/me/messages?$filter=' . $criteria . '&$select=id,body,toRecipients,from,subject&$top=100';
                }

                // Get service response
                $response = $this->service->createRequest('GET', $nextLink)->execute();

                // Get the mails identifiers
                $messages = $response->getResponseAsObject(Model\Message::class);
                foreach ($messages as $message) {
                    // Collect the messages identifiers
                    $mailsIds[] = $message->getId();

                    // Create a simple message object
                    $simpleMessage= new stdClass();
                    $simpleMessage->textPlain = strip_tags($message->getBody()->getContent());
                    $simpleMessage->toString = $message->getToRecipients()[0]['emailAddress']['address'];
                    $simpleMessage->fromAddress = $message->getFrom()->getEmailAddress()->getAddress();
                    $simpleMessage->subject = $message->getSubject();

                    // Add the new message object to messages array
                    $this->messages[$message->getId()] = $simpleMessage;
                }

                // Get next link
                $nextLink = $response->getNextLink();
            } while (!is_null($nextLink));
        } catch (Exception $e) {
            throw $e;
        }

        return $mailsIds;
    }

    /**
     * Get mail data.
     *
     * @param string $mailId ID of the mail
     * @param bool $markAsSeen Mark the email as seen, maintained by compatibility reasons, currently not used
     * @return object
     */
    public function getMail(string $mailId, bool $markAsSeen = true): object
    {
        return $this->messages[$mailId] ?? null;
    }

    /**
     * Set "Is Read" property to "true" in the mail.
     *
     * @param string $mailId
     * @return void
     * @throws Exception
     */
    public function markMailAsRead($mailId): void
    {
        // Set "Is Read" property to "true"
        $message = new Model\Message();
        $message->setIsRead(true);

        // Get service response
        try {
            $this->service->createRequest('PATCH', '/me/messages/' . $mailId)->attachBody($message)->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
