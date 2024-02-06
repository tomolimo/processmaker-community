<?php

require_once 'classes/model/om/BaseProcessFiles.php';


/**
 * Skeleton subclass for representing a row from the 'PROCESS_FILES' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class ProcessFiles extends BaseProcessFiles {

    /**
     * Remove a process file record
     *
     * @param string $prfUid
     *
     * @return string
     *
     * @throws Exception
     **/
    public function remove($prfUid)
    {
        $connection = Propel::getConnection(ProcessFilesPeer::DATABASE_NAME);
        try {
            $object = ProcessFilesPeer::retrieveByPK($prfUid);
            if (!is_null($object)) {
                $connection->begin();
                $object->delete();
                $connection->commit();
            } else {
                throw new Exception('This row doesn\'t exist!');
            }
        } catch (Exception $error) {
            $connection->rollback();
            throw $error;
        }
    }

} // ProcessFiles
