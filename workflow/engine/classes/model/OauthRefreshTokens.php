<?php

require_once 'classes/model/om/BaseOauthRefreshTokens.php';


/**
 * Skeleton subclass for representing a row from the 'OAUTH_REFRESH_TOKENS' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class OauthRefreshTokens extends BaseOauthRefreshTokens
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
        $criteria->add(OauthRefreshTokensPeer::USER_ID, $userUid);
        $resultSet = OauthRefreshTokensPeer::doDelete($criteria);
        return $resultSet;
    }
} // OauthRefreshTokens
