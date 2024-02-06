<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/JobsFailedPeer.php';

/**
 * Base class that represents a row from the 'JOBS_FAILED' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseJobsFailed extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        JobsFailedPeer
    */
    protected static $peer;

    /**
     * The value for the id field.
     * @var        string
     */
    protected $id;

    /**
     * The value for the connection field.
     * @var        string
     */
    protected $connection;

    /**
     * The value for the queue field.
     * @var        string
     */
    protected $queue;

    /**
     * The value for the payload field.
     * @var        string
     */
    protected $payload;

    /**
     * The value for the exception field.
     * @var        string
     */
    protected $exception;

    /**
     * The value for the failed_at field.
     * @var        int
     */
    protected $failed_at;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [id] column value.
     * 
     * @return     string
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [connection] column value.
     * 
     * @return     string
     */
    public function getConnection()
    {

        return $this->connection;
    }

    /**
     * Get the [queue] column value.
     * 
     * @return     string
     */
    public function getQueue()
    {

        return $this->queue;
    }

    /**
     * Get the [payload] column value.
     * 
     * @return     string
     */
    public function getPayload()
    {

        return $this->payload;
    }

    /**
     * Get the [exception] column value.
     * 
     * @return     string
     */
    public function getException()
    {

        return $this->exception;
    }

    /**
     * Get the [optionally formatted] [failed_at] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getFailedAt($format = 'Y-m-d H:i:s')
    {

        if ($this->failed_at === null || $this->failed_at === '') {
            return null;
        } elseif (!is_int($this->failed_at)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->failed_at);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [failed_at] as date/time value: " .
                    var_export($this->failed_at, true));
            }
        } else {
            $ts = $this->failed_at;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Set the value of [id] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setId($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = JobsFailedPeer::ID;
        }

    } // setId()

    /**
     * Set the value of [connection] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setConnection($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->connection !== $v) {
            $this->connection = $v;
            $this->modifiedColumns[] = JobsFailedPeer::CONNECTION;
        }

    } // setConnection()

    /**
     * Set the value of [queue] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setQueue($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->queue !== $v) {
            $this->queue = $v;
            $this->modifiedColumns[] = JobsFailedPeer::QUEUE;
        }

    } // setQueue()

    /**
     * Set the value of [payload] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPayload($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->payload !== $v) {
            $this->payload = $v;
            $this->modifiedColumns[] = JobsFailedPeer::PAYLOAD;
        }

    } // setPayload()

    /**
     * Set the value of [exception] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setException($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->exception !== $v) {
            $this->exception = $v;
            $this->modifiedColumns[] = JobsFailedPeer::EXCEPTION;
        }

    } // setException()

    /**
     * Set the value of [failed_at] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFailedAt($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [failed_at] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->failed_at !== $ts) {
            $this->failed_at = $ts;
            $this->modifiedColumns[] = JobsFailedPeer::FAILED_AT;
        }

    } // setFailedAt()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (1-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param      ResultSet $rs The ResultSet class with cursor advanced to desired record pos.
     * @param      int $startcol 1-based offset column which indicates which restultset column to start with.
     * @return     int next starting column
     * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(ResultSet $rs, $startcol = 1)
    {
        try {

            $this->id = $rs->getString($startcol + 0);

            $this->connection = $rs->getString($startcol + 1);

            $this->queue = $rs->getString($startcol + 2);

            $this->payload = $rs->getString($startcol + 3);

            $this->exception = $rs->getString($startcol + 4);

            $this->failed_at = $rs->getTimestamp($startcol + 5, null);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 6; // 6 = JobsFailedPeer::NUM_COLUMNS - JobsFailedPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating JobsFailed object", $e);
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(JobsFailedPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            JobsFailedPeer::doDelete($this, $con);
            $this->setDeleted(true);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(JobsFailedPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            $affectedRows = $this->doSave($con);
            $con->commit();
            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     PropelException
     * @see        save()
     */
    protected function doSave($con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;


            // If this object has been modified, then save it to the database.
            if ($this->isModified()) {
                if ($this->isNew()) {
                    $pk = JobsFailedPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += JobsFailedPeer::doUpdate($this, $con);
                }
                $this->resetModified(); // [HL] After being saved an object is no longer 'modified'
            }

            $this->alreadyInSave = false;
        }
        return $affectedRows;
    } // doSave()

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param      array $columns Array of column names to validate.
     * @return     mixed <code>true</code> if all validations pass; 
                   array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if (($retval = JobsFailedPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = JobsFailedPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->getByPosition($pos);
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return     mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getConnection();
                break;
            case 2:
                return $this->getQueue();
                break;
            case 3:
                return $this->getPayload();
                break;
            case 4:
                return $this->getException();
                break;
            case 5:
                return $this->getFailedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param      string $keyType One of the class type constants TYPE_PHPNAME,
     *                        TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = JobsFailedPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getConnection(),
            $keys[2] => $this->getQueue(),
            $keys[3] => $this->getPayload(),
            $keys[4] => $this->getException(),
            $keys[5] => $this->getFailedAt(),
        );
        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name peer name
     * @param      mixed $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = JobsFailedPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return     void
     */
    public function setByPosition($pos, $value)
    {
        switch($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setConnection($value);
                break;
            case 2:
                $this->setQueue($value);
                break;
            case 3:
                $this->setPayload($value);
                break;
            case 4:
                $this->setException($value);
                break;
            case 5:
                $this->setFailedAt($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME,
     * TYPE_NUM. The default key type is the column's phpname (e.g. 'authorId')
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return     void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = JobsFailedPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setConnection($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setQueue($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setPayload($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setException($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setFailedAt($arr[$keys[5]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(JobsFailedPeer::DATABASE_NAME);

        if ($this->isColumnModified(JobsFailedPeer::ID)) {
            $criteria->add(JobsFailedPeer::ID, $this->id);
        }

        if ($this->isColumnModified(JobsFailedPeer::CONNECTION)) {
            $criteria->add(JobsFailedPeer::CONNECTION, $this->connection);
        }

        if ($this->isColumnModified(JobsFailedPeer::QUEUE)) {
            $criteria->add(JobsFailedPeer::QUEUE, $this->queue);
        }

        if ($this->isColumnModified(JobsFailedPeer::PAYLOAD)) {
            $criteria->add(JobsFailedPeer::PAYLOAD, $this->payload);
        }

        if ($this->isColumnModified(JobsFailedPeer::EXCEPTION)) {
            $criteria->add(JobsFailedPeer::EXCEPTION, $this->exception);
        }

        if ($this->isColumnModified(JobsFailedPeer::FAILED_AT)) {
            $criteria->add(JobsFailedPeer::FAILED_AT, $this->failed_at);
        }


        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return     Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(JobsFailedPeer::DATABASE_NAME);

        $criteria->add(JobsFailedPeer::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return     string
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param      string $key Primary key.
     * @return     void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of JobsFailed (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setConnection($this->connection);

        $copyObj->setQueue($this->queue);

        $copyObj->setPayload($this->payload);

        $copyObj->setException($this->exception);

        $copyObj->setFailedAt($this->failed_at);


        $copyObj->setNew(true);

        $copyObj->setId(NULL); // this is a pkey column, so set to default value

    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return     JobsFailed Clone of current object.
     * @throws     PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);
        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return     JobsFailedPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new JobsFailedPeer();
        }
        return self::$peer;
    }
}

