<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/JobsPendingPeer.php';

/**
 * Base class that represents a row from the 'JOBS_PENDING' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseJobsPending extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        JobsPendingPeer
    */
    protected static $peer;

    /**
     * The value for the id field.
     * @var        string
     */
    protected $id;

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
     * The value for the attempts field.
     * @var        int
     */
    protected $attempts;

    /**
     * The value for the reserved_at field.
     * @var        int
     */
    protected $reserved_at;

    /**
     * The value for the available_at field.
     * @var        int
     */
    protected $available_at;

    /**
     * The value for the created_at field.
     * @var        int
     */
    protected $created_at;

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
     * Get the [attempts] column value.
     * 
     * @return     int
     */
    public function getAttempts()
    {

        return $this->attempts;
    }

    /**
     * Get the [reserved_at] column value.
     * 
     * @return     int
     */
    public function getReservedAt()
    {

        return $this->reserved_at;
    }

    /**
     * Get the [available_at] column value.
     * 
     * @return     int
     */
    public function getAvailableAt()
    {

        return $this->available_at;
    }

    /**
     * Get the [created_at] column value.
     * 
     * @return     int
     */
    public function getCreatedAt()
    {

        return $this->created_at;
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
            $this->modifiedColumns[] = JobsPendingPeer::ID;
        }

    } // setId()

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
            $this->modifiedColumns[] = JobsPendingPeer::QUEUE;
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
            $this->modifiedColumns[] = JobsPendingPeer::PAYLOAD;
        }

    } // setPayload()

    /**
     * Set the value of [attempts] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAttempts($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->attempts !== $v) {
            $this->attempts = $v;
            $this->modifiedColumns[] = JobsPendingPeer::ATTEMPTS;
        }

    } // setAttempts()

    /**
     * Set the value of [reserved_at] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setReservedAt($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->reserved_at !== $v) {
            $this->reserved_at = $v;
            $this->modifiedColumns[] = JobsPendingPeer::RESERVED_AT;
        }

    } // setReservedAt()

    /**
     * Set the value of [available_at] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAvailableAt($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->available_at !== $v) {
            $this->available_at = $v;
            $this->modifiedColumns[] = JobsPendingPeer::AVAILABLE_AT;
        }

    } // setAvailableAt()

    /**
     * Set the value of [created_at] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCreatedAt($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->created_at !== $v) {
            $this->created_at = $v;
            $this->modifiedColumns[] = JobsPendingPeer::CREATED_AT;
        }

    } // setCreatedAt()

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

            $this->queue = $rs->getString($startcol + 1);

            $this->payload = $rs->getString($startcol + 2);

            $this->attempts = $rs->getInt($startcol + 3);

            $this->reserved_at = $rs->getInt($startcol + 4);

            $this->available_at = $rs->getInt($startcol + 5);

            $this->created_at = $rs->getInt($startcol + 6);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 7; // 7 = JobsPendingPeer::NUM_COLUMNS - JobsPendingPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating JobsPending object", $e);
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
            $con = Propel::getConnection(JobsPendingPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            JobsPendingPeer::doDelete($this, $con);
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
            $con = Propel::getConnection(JobsPendingPeer::DATABASE_NAME);
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
                    $pk = JobsPendingPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += JobsPendingPeer::doUpdate($this, $con);
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


            if (($retval = JobsPendingPeer::doValidate($this, $columns)) !== true) {
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
        $pos = JobsPendingPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getQueue();
                break;
            case 2:
                return $this->getPayload();
                break;
            case 3:
                return $this->getAttempts();
                break;
            case 4:
                return $this->getReservedAt();
                break;
            case 5:
                return $this->getAvailableAt();
                break;
            case 6:
                return $this->getCreatedAt();
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
        $keys = JobsPendingPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getQueue(),
            $keys[2] => $this->getPayload(),
            $keys[3] => $this->getAttempts(),
            $keys[4] => $this->getReservedAt(),
            $keys[5] => $this->getAvailableAt(),
            $keys[6] => $this->getCreatedAt(),
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
        $pos = JobsPendingPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                $this->setQueue($value);
                break;
            case 2:
                $this->setPayload($value);
                break;
            case 3:
                $this->setAttempts($value);
                break;
            case 4:
                $this->setReservedAt($value);
                break;
            case 5:
                $this->setAvailableAt($value);
                break;
            case 6:
                $this->setCreatedAt($value);
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
        $keys = JobsPendingPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setQueue($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setPayload($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setAttempts($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setReservedAt($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setAvailableAt($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setCreatedAt($arr[$keys[6]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(JobsPendingPeer::DATABASE_NAME);

        if ($this->isColumnModified(JobsPendingPeer::ID)) {
            $criteria->add(JobsPendingPeer::ID, $this->id);
        }

        if ($this->isColumnModified(JobsPendingPeer::QUEUE)) {
            $criteria->add(JobsPendingPeer::QUEUE, $this->queue);
        }

        if ($this->isColumnModified(JobsPendingPeer::PAYLOAD)) {
            $criteria->add(JobsPendingPeer::PAYLOAD, $this->payload);
        }

        if ($this->isColumnModified(JobsPendingPeer::ATTEMPTS)) {
            $criteria->add(JobsPendingPeer::ATTEMPTS, $this->attempts);
        }

        if ($this->isColumnModified(JobsPendingPeer::RESERVED_AT)) {
            $criteria->add(JobsPendingPeer::RESERVED_AT, $this->reserved_at);
        }

        if ($this->isColumnModified(JobsPendingPeer::AVAILABLE_AT)) {
            $criteria->add(JobsPendingPeer::AVAILABLE_AT, $this->available_at);
        }

        if ($this->isColumnModified(JobsPendingPeer::CREATED_AT)) {
            $criteria->add(JobsPendingPeer::CREATED_AT, $this->created_at);
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
        $criteria = new Criteria(JobsPendingPeer::DATABASE_NAME);

        $criteria->add(JobsPendingPeer::ID, $this->id);

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
     * @param      object $copyObj An object of JobsPending (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setQueue($this->queue);

        $copyObj->setPayload($this->payload);

        $copyObj->setAttempts($this->attempts);

        $copyObj->setReservedAt($this->reserved_at);

        $copyObj->setAvailableAt($this->available_at);

        $copyObj->setCreatedAt($this->created_at);


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
     * @return     JobsPending Clone of current object.
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
     * @return     JobsPendingPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new JobsPendingPeer();
        }
        return self::$peer;
    }
}

