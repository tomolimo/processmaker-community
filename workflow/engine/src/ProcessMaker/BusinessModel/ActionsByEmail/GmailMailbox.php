<?php

namespace ProcessMaker\BusinessModel\ActionsByEmail;

use Exception;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_ModifyMessageRequest;
use stdClass;

/**
 * Class GmailMailbox
 * @package ProcessMaker\BusinessModel\ActionsByEmail
 */
class GmailMailbox
{
    private $service;

    /**
     * GmailMailbox constructor.
     * @param array $emailSetup
     */
    public function __construct(array $emailSetup)
    {
        // Google Client instance
        $googleClient = new Google_Client();
        $googleClient->setClientId($emailSetup['OAUTH_CLIENT_ID']);
        $googleClient->setClientSecret($emailSetup['OAUTH_CLIENT_SECRET']);
        $googleClient->refreshToken($emailSetup['OAUTH_REFRESH_TOKEN']);
        $googleClient->setAccessType('offline');
        $googleClient->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);

        // Set Gmail service instance
        $this->service = new Google_Service_Gmail($googleClient);
    }

    /**
     * This function uses Gmail API to perform a search on the mailbox.
     *
     * @param string $criteria
     * @return array
     * @throws Exception
     */
    public function searchMailbox(string $criteria = 'ALL'): array
    {
        // Transform criteria to values accepted by Gmail service
        switch ($criteria) {
            case 'UNSEEN':
                $criteria = 'is:unread';
                break;
            case 'SEEN':
                $criteria = 'is:read';
                break;
            default:
                $criteria = '';
        }

        // Initialize variables
        $nextPageToken = null;
        $mailsIds = [];

        // Get unread user's messages
        try {
            do {
                // Build optional parameters array
                $optParams = [
                    'q' => $criteria,
                    'pageToken' => $nextPageToken
                ];

                // Get service response
                $response = $this->service->users_messages->listUsersMessages('me', $optParams);

                // Get the mails identifiers
                $messages = $response->getMessages();
                foreach ($messages as $message) {
                    $mailsIds[] = $message->getId();
                }

                // Get next page token
                $nextPageToken = $response->getNextPageToken();
            } while (!is_null($nextPageToken));
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
     * @throws Exception
     */
    public function getMail(string $mailId, bool $markAsSeen = true): object
    {
        try {
            // Get message data
            $response = $this->service->users_messages->get('me', $mailId);

            // Get payload
            $payload = $response->getPayload();

            // Get headers
            $headers = [];
            foreach ($payload['headers'] as $item) {
                $headers[$item->name] = $item->value;
            }

            // Get complete and decoded message body
            $body = $this->getMessageBodyRecursive($payload);
        } catch (Exception $e) {
            throw $e;
        }

        // Build message object
        $message = new stdClass();
        $message->fromAddress = $headers['From'];
        $message->toString = $headers['To'];
        $message->subject = $headers['Subject'];
        $message->textPlain = $body['plain'] ?? $body['html'];

        return $message;
    }

    /**
     * Remove UNREAD label in the mail.
     *
     * @param string $mailId
     * @return void
     * @throws Exception
     */
    public function markMailAsRead($mailId): void
    {
        // Build modify message request
        $modifyMessageRequest = new Google_Service_Gmail_ModifyMessageRequest();
        $modifyMessageRequest->setRemoveLabelIds(['UNREAD']);

        // Modify the mail
        try {
            $this->service->users_messages->modify('me', $mailId, $modifyMessageRequest);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get message html body and plain body
     *
     * @param object $part
     * @return array
     */
    private function getMessageBodyRecursive(object $part): array
    {
        if ($part->mimeType == 'text/html') {
            return [
                'html' => $this->base64UrlDecode($part->body->data)
            ];
        } else if ($part->mimeType == 'text/plain') {
            return [
                'plain' => $this->base64UrlDecode($part->body->data)
            ];
        } else if ($part->parts) {
            $return = [];
            foreach ($part->parts as $subPart) {
                $result = $this->getMessageBodyRecursive($subPart);
                $return = array_merge($return, $result);
                if (array_key_exists('html', $return)) {
                    break;
                }
            }
            return $return;
        }
        return [];
    }

    /**
     * Returns a base64 decoded web safe string
     *
     * @param string $string
     * @return string
     */
    private function base64UrlDecode(string $string): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
    }
}
