<?php

require_once 'classes/model/om/BaseOauthAuthorizationCodes.php';


/**
 * Skeleton subclass for representing a row from the 'OAUTH_AUTHORIZATION_CODES' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class OauthAuthorizationCodes extends BaseOauthAuthorizationCodes
{
    /**
     * Delete all records related to a user uid
     * @param string $userUid User uid
     * @return int
     * @throws PropelException
     */
    public function removeByUser($userUid)
    {
        $criteria = new Criteria();
        $criteria->add(OauthAuthorizationCodesPeer::USER_ID, $userUid);
        $resultSet = OauthAuthorizationCodesPeer::doDelete($criteria);
        return $resultSet;
    }
} // OauthAuthorizationCodes
