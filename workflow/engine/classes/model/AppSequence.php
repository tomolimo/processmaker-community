<?php

require_once 'classes/model/om/BaseAppSequence.php';


/**
 * Skeleton subclass for representing a row from the 'APP_SEQUENCE' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class AppSequence extends BaseAppSequence {

    const APP_TYPE_NORMAL = 'NORMAL';
    const APP_TYPE_WEB_ENTRY = 'WEB_ENTRY';

    /**
     * Get an Set new sequence number
     *
     * @param string $sequenceType
     * @return mixed
     * @throws Exception
     */
    public function sequenceNumber($sequenceType)
    {
        try {
            $con = Propel::getConnection('workflow');
            $stmt = $con->createStatement();
            $sql = "UPDATE APP_SEQUENCE SET ID=LAST_INSERT_ID(ID+1) WHERE APP_TYPE = '{$sequenceType}'";
            $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
            $sql = "SELECT LAST_INSERT_ID()";
            $rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
            $rs->next();
            $row = $rs->getRow();
            $result = $row['LAST_INSERT_ID()'];

            // If the type is WEB_ENTRY, we need to change to negative
            if ($sequenceType === 'WEB_ENTRY') {
                $result *= -1;
            }
        } catch (Exception $e) {
            throw ($e);
        }
        return $result;
    }


    /**
     * Update sequence number
     *
     * @param int $number
     * @param string $sequenceType
     *
     * @throws Exception
     */
    public function updateSequenceNumber($number, $sequenceType = AppSequence::APP_TYPE_NORMAL)
    {
        try {
            // Get the current connection
            $connection = Propel::getConnection('workflow');

            // Create a statement instance
            $statement = $connection->createStatement();

            // Get the record according to the sequence type
            $criteria = new Criteria();
            $criteria->add(AppSequencePeer::APP_TYPE, $sequenceType);
            $rsCriteria = AppSequencePeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $rsCriteria->next();
            $row = $rsCriteria->getRow();

            // Insert/Update sequence table with the number sent
            if ($row) {
                $sql = "UPDATE APP_SEQUENCE SET ID=LAST_INSERT_ID('{$number}') WHERE APP_TYPE = '{$sequenceType}'";
            } else {
                $sql = "INSERT INTO APP_SEQUENCE (ID, APP_TYPE) VALUES ('{$number}', '{$sequenceType}')";
            }
            $statement->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
        } catch (Exception $e) {
            throw ($e);
        }
    }

} // AppSequence
