<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/SchedulerPeer.php';

/**
 * Base class that represents a row from the 'SCHEDULER' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseScheduler extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        SchedulerPeer
    */
    protected static $peer;

    /**
     * The value for the id field.
     * @var        string
     */
    protected $id;

    /**
     * The value for the title field.
     * @var        string
     */
    protected $title;

    /**
     * The value for the startingtime field.
     * @var        string
     */
    protected $startingtime;

    /**
     * The value for the endingtime field.
     * @var        string
     */
    protected $endingtime;

    /**
     * The value for the everyon field.
     * @var        string
     */
    protected $everyon;

    /**
     * The value for the interval field.
     * @var        string
     */
    protected $interval;

    /**
     * The value for the description field.
     * @var        string
     */
    protected $description;

    /**
     * The value for the expression field.
     * @var        string
     */
    protected $expression;

    /**
     * The value for the default_value field.
     * @var        string
     */
    protected $default_value;

    /**
     * The value for the body field.
     * @var        string
     */
    protected $body;

    /**
     * The value for the type field.
     * @var        string
     */
    protected $type;

    /**
     * The value for the category field.
     * @var        string
     */
    protected $category;

    /**
     * The value for the system field.
     * @var        int
     */
    protected $system;

    /**
     * The value for the timezone field.
     * @var        string
     */
    protected $timezone;

    /**
     * The value for the enable field.
     * @var        int
     */
    protected $enable;

    /**
     * The value for the creation_date field.
     * @var        int
     */
    protected $creation_date;

    /**
     * The value for the last_update field.
     * @var        int
     */
    protected $last_update;

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
     * Get the [title] column value.
     * 
     * @return     string
     */
    public function getTitle()
    {

        return $this->title;
    }

    /**
     * Get the [startingtime] column value.
     * 
     * @return     string
     */
    public function getStartingtime()
    {

        return $this->startingtime;
    }

    /**
     * Get the [endingtime] column value.
     * 
     * @return     string
     */
    public function getEndingtime()
    {

        return $this->endingtime;
    }

    /**
     * Get the [everyon] column value.
     * 
     * @return     string
     */
    public function getEveryon()
    {

        return $this->everyon;
    }

    /**
     * Get the [interval] column value.
     * 
     * @return     string
     */
    public function getInterval()
    {

        return $this->interval;
    }

    /**
     * Get the [description] column value.
     * 
     * @return     string
     */
    public function getDescription()
    {

        return $this->description;
    }

    /**
     * Get the [expression] column value.
     * 
     * @return     string
     */
    public function getExpression()
    {

        return $this->expression;
    }

    /**
     * Get the [default_value] column value.
     * 
     * @return     string
     */
    public function getDefaultValue()
    {

        return $this->default_value;
    }

    /**
     * Get the [body] column value.
     * 
     * @return     string
     */
    public function getBody()
    {

        return $this->body;
    }

    /**
     * Get the [type] column value.
     * 
     * @return     string
     */
    public function getType()
    {

        return $this->type;
    }

    /**
     * Get the [category] column value.
     * 
     * @return     string
     */
    public function getCategory()
    {

        return $this->category;
    }

    /**
     * Get the [system] column value.
     * 
     * @return     int
     */
    public function getSystem()
    {

        return $this->system;
    }

    /**
     * Get the [timezone] column value.
     * 
     * @return     string
     */
    public function getTimezone()
    {

        return $this->timezone;
    }

    /**
     * Get the [enable] column value.
     * 
     * @return     int
     */
    public function getEnable()
    {

        return $this->enable;
    }

    /**
     * Get the [optionally formatted] [creation_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getCreationDate($format = 'Y-m-d H:i:s')
    {

        if ($this->creation_date === null || $this->creation_date === '') {
            return null;
        } elseif (!is_int($this->creation_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->creation_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [creation_date] as date/time value: " .
                    var_export($this->creation_date, true));
            }
        } else {
            $ts = $this->creation_date;
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
     * Get the [optionally formatted] [last_update] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getLastUpdate($format = 'Y-m-d H:i:s')
    {

        if ($this->last_update === null || $this->last_update === '') {
            return null;
        } elseif (!is_int($this->last_update)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->last_update);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [last_update] as date/time value: " .
                    var_export($this->last_update, true));
            }
        } else {
            $ts = $this->last_update;
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
            $this->modifiedColumns[] = SchedulerPeer::ID;
        }

    } // setId()

    /**
     * Set the value of [title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTitle($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->title !== $v) {
            $this->title = $v;
            $this->modifiedColumns[] = SchedulerPeer::TITLE;
        }

    } // setTitle()

    /**
     * Set the value of [startingtime] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStartingtime($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->startingtime !== $v) {
            $this->startingtime = $v;
            $this->modifiedColumns[] = SchedulerPeer::STARTINGTIME;
        }

    } // setStartingtime()

    /**
     * Set the value of [endingtime] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEndingtime($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->endingtime !== $v) {
            $this->endingtime = $v;
            $this->modifiedColumns[] = SchedulerPeer::ENDINGTIME;
        }

    } // setEndingtime()

    /**
     * Set the value of [everyon] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEveryon($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->everyon !== $v) {
            $this->everyon = $v;
            $this->modifiedColumns[] = SchedulerPeer::EVERYON;
        }

    } // setEveryon()

    /**
     * Set the value of [interval] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setInterval($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->interval !== $v) {
            $this->interval = $v;
            $this->modifiedColumns[] = SchedulerPeer::INTERVAL;
        }

    } // setInterval()

    /**
     * Set the value of [description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDescription($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->description !== $v) {
            $this->description = $v;
            $this->modifiedColumns[] = SchedulerPeer::DESCRIPTION;
        }

    } // setDescription()

    /**
     * Set the value of [expression] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setExpression($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->expression !== $v) {
            $this->expression = $v;
            $this->modifiedColumns[] = SchedulerPeer::EXPRESSION;
        }

    } // setExpression()

    /**
     * Set the value of [default_value] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDefaultValue($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->default_value !== $v) {
            $this->default_value = $v;
            $this->modifiedColumns[] = SchedulerPeer::DEFAULT_VALUE;
        }

    } // setDefaultValue()

    /**
     * Set the value of [body] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setBody($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->body !== $v) {
            $this->body = $v;
            $this->modifiedColumns[] = SchedulerPeer::BODY;
        }

    } // setBody()

    /**
     * Set the value of [type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setType($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->type !== $v) {
            $this->type = $v;
            $this->modifiedColumns[] = SchedulerPeer::TYPE;
        }

    } // setType()

    /**
     * Set the value of [category] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCategory($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->category !== $v) {
            $this->category = $v;
            $this->modifiedColumns[] = SchedulerPeer::CATEGORY;
        }

    } // setCategory()

    /**
     * Set the value of [system] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setSystem($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->system !== $v) {
            $this->system = $v;
            $this->modifiedColumns[] = SchedulerPeer::SYSTEM;
        }

    } // setSystem()

    /**
     * Set the value of [timezone] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTimezone($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->timezone !== $v) {
            $this->timezone = $v;
            $this->modifiedColumns[] = SchedulerPeer::TIMEZONE;
        }

    } // setTimezone()

    /**
     * Set the value of [enable] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setEnable($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->enable !== $v) {
            $this->enable = $v;
            $this->modifiedColumns[] = SchedulerPeer::ENABLE;
        }

    } // setEnable()

    /**
     * Set the value of [creation_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCreationDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [creation_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->creation_date !== $ts) {
            $this->creation_date = $ts;
            $this->modifiedColumns[] = SchedulerPeer::CREATION_DATE;
        }

    } // setCreationDate()

    /**
     * Set the value of [last_update] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setLastUpdate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [last_update] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->last_update !== $ts) {
            $this->last_update = $ts;
            $this->modifiedColumns[] = SchedulerPeer::LAST_UPDATE;
        }

    } // setLastUpdate()

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

            $this->title = $rs->getString($startcol + 1);

            $this->startingtime = $rs->getString($startcol + 2);

            $this->endingtime = $rs->getString($startcol + 3);

            $this->everyon = $rs->getString($startcol + 4);

            $this->interval = $rs->getString($startcol + 5);

            $this->description = $rs->getString($startcol + 6);

            $this->expression = $rs->getString($startcol + 7);

            $this->default_value = $rs->getString($startcol + 8);

            $this->body = $rs->getString($startcol + 9);

            $this->type = $rs->getString($startcol + 10);

            $this->category = $rs->getString($startcol + 11);

            $this->system = $rs->getInt($startcol + 12);

            $this->timezone = $rs->getString($startcol + 13);

            $this->enable = $rs->getInt($startcol + 14);

            $this->creation_date = $rs->getTimestamp($startcol + 15, null);

            $this->last_update = $rs->getTimestamp($startcol + 16, null);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 17; // 17 = SchedulerPeer::NUM_COLUMNS - SchedulerPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating Scheduler object", $e);
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
            $con = Propel::getConnection(SchedulerPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            SchedulerPeer::doDelete($this, $con);
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
            $con = Propel::getConnection(SchedulerPeer::DATABASE_NAME);
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
                    $pk = SchedulerPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += SchedulerPeer::doUpdate($this, $con);
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


            if (($retval = SchedulerPeer::doValidate($this, $columns)) !== true) {
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
        $pos = SchedulerPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getTitle();
                break;
            case 2:
                return $this->getStartingtime();
                break;
            case 3:
                return $this->getEndingtime();
                break;
            case 4:
                return $this->getEveryon();
                break;
            case 5:
                return $this->getInterval();
                break;
            case 6:
                return $this->getDescription();
                break;
            case 7:
                return $this->getExpression();
                break;
            case 8:
                return $this->getDefaultValue();
                break;
            case 9:
                return $this->getBody();
                break;
            case 10:
                return $this->getType();
                break;
            case 11:
                return $this->getCategory();
                break;
            case 12:
                return $this->getSystem();
                break;
            case 13:
                return $this->getTimezone();
                break;
            case 14:
                return $this->getEnable();
                break;
            case 15:
                return $this->getCreationDate();
                break;
            case 16:
                return $this->getLastUpdate();
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
        $keys = SchedulerPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getTitle(),
            $keys[2] => $this->getStartingtime(),
            $keys[3] => $this->getEndingtime(),
            $keys[4] => $this->getEveryon(),
            $keys[5] => $this->getInterval(),
            $keys[6] => $this->getDescription(),
            $keys[7] => $this->getExpression(),
            $keys[8] => $this->getDefaultValue(),
            $keys[9] => $this->getBody(),
            $keys[10] => $this->getType(),
            $keys[11] => $this->getCategory(),
            $keys[12] => $this->getSystem(),
            $keys[13] => $this->getTimezone(),
            $keys[14] => $this->getEnable(),
            $keys[15] => $this->getCreationDate(),
            $keys[16] => $this->getLastUpdate(),
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
        $pos = SchedulerPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                $this->setTitle($value);
                break;
            case 2:
                $this->setStartingtime($value);
                break;
            case 3:
                $this->setEndingtime($value);
                break;
            case 4:
                $this->setEveryon($value);
                break;
            case 5:
                $this->setInterval($value);
                break;
            case 6:
                $this->setDescription($value);
                break;
            case 7:
                $this->setExpression($value);
                break;
            case 8:
                $this->setDefaultValue($value);
                break;
            case 9:
                $this->setBody($value);
                break;
            case 10:
                $this->setType($value);
                break;
            case 11:
                $this->setCategory($value);
                break;
            case 12:
                $this->setSystem($value);
                break;
            case 13:
                $this->setTimezone($value);
                break;
            case 14:
                $this->setEnable($value);
                break;
            case 15:
                $this->setCreationDate($value);
                break;
            case 16:
                $this->setLastUpdate($value);
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
        $keys = SchedulerPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setTitle($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setStartingtime($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setEndingtime($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setEveryon($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setInterval($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setDescription($arr[$keys[6]]);
        }

        if (array_key_exists($keys[7], $arr)) {
            $this->setExpression($arr[$keys[7]]);
        }

        if (array_key_exists($keys[8], $arr)) {
            $this->setDefaultValue($arr[$keys[8]]);
        }

        if (array_key_exists($keys[9], $arr)) {
            $this->setBody($arr[$keys[9]]);
        }

        if (array_key_exists($keys[10], $arr)) {
            $this->setType($arr[$keys[10]]);
        }

        if (array_key_exists($keys[11], $arr)) {
            $this->setCategory($arr[$keys[11]]);
        }

        if (array_key_exists($keys[12], $arr)) {
            $this->setSystem($arr[$keys[12]]);
        }

        if (array_key_exists($keys[13], $arr)) {
            $this->setTimezone($arr[$keys[13]]);
        }

        if (array_key_exists($keys[14], $arr)) {
            $this->setEnable($arr[$keys[14]]);
        }

        if (array_key_exists($keys[15], $arr)) {
            $this->setCreationDate($arr[$keys[15]]);
        }

        if (array_key_exists($keys[16], $arr)) {
            $this->setLastUpdate($arr[$keys[16]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(SchedulerPeer::DATABASE_NAME);

        if ($this->isColumnModified(SchedulerPeer::ID)) {
            $criteria->add(SchedulerPeer::ID, $this->id);
        }

        if ($this->isColumnModified(SchedulerPeer::TITLE)) {
            $criteria->add(SchedulerPeer::TITLE, $this->title);
        }

        if ($this->isColumnModified(SchedulerPeer::STARTINGTIME)) {
            $criteria->add(SchedulerPeer::STARTINGTIME, $this->startingtime);
        }

        if ($this->isColumnModified(SchedulerPeer::ENDINGTIME)) {
            $criteria->add(SchedulerPeer::ENDINGTIME, $this->endingtime);
        }

        if ($this->isColumnModified(SchedulerPeer::EVERYON)) {
            $criteria->add(SchedulerPeer::EVERYON, $this->everyon);
        }

        if ($this->isColumnModified(SchedulerPeer::INTERVAL)) {
            $criteria->add(SchedulerPeer::INTERVAL, $this->interval);
        }

        if ($this->isColumnModified(SchedulerPeer::DESCRIPTION)) {
            $criteria->add(SchedulerPeer::DESCRIPTION, $this->description);
        }

        if ($this->isColumnModified(SchedulerPeer::EXPRESSION)) {
            $criteria->add(SchedulerPeer::EXPRESSION, $this->expression);
        }

        if ($this->isColumnModified(SchedulerPeer::DEFAULT_VALUE)) {
            $criteria->add(SchedulerPeer::DEFAULT_VALUE, $this->default_value);
        }

        if ($this->isColumnModified(SchedulerPeer::BODY)) {
            $criteria->add(SchedulerPeer::BODY, $this->body);
        }

        if ($this->isColumnModified(SchedulerPeer::TYPE)) {
            $criteria->add(SchedulerPeer::TYPE, $this->type);
        }

        if ($this->isColumnModified(SchedulerPeer::CATEGORY)) {
            $criteria->add(SchedulerPeer::CATEGORY, $this->category);
        }

        if ($this->isColumnModified(SchedulerPeer::SYSTEM)) {
            $criteria->add(SchedulerPeer::SYSTEM, $this->system);
        }

        if ($this->isColumnModified(SchedulerPeer::TIMEZONE)) {
            $criteria->add(SchedulerPeer::TIMEZONE, $this->timezone);
        }

        if ($this->isColumnModified(SchedulerPeer::ENABLE)) {
            $criteria->add(SchedulerPeer::ENABLE, $this->enable);
        }

        if ($this->isColumnModified(SchedulerPeer::CREATION_DATE)) {
            $criteria->add(SchedulerPeer::CREATION_DATE, $this->creation_date);
        }

        if ($this->isColumnModified(SchedulerPeer::LAST_UPDATE)) {
            $criteria->add(SchedulerPeer::LAST_UPDATE, $this->last_update);
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
        $criteria = new Criteria(SchedulerPeer::DATABASE_NAME);

        $criteria->add(SchedulerPeer::ID, $this->id);

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
     * @param      object $copyObj An object of Scheduler (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setTitle($this->title);

        $copyObj->setStartingtime($this->startingtime);

        $copyObj->setEndingtime($this->endingtime);

        $copyObj->setEveryon($this->everyon);

        $copyObj->setInterval($this->interval);

        $copyObj->setDescription($this->description);

        $copyObj->setExpression($this->expression);

        $copyObj->setDefaultValue($this->default_value);

        $copyObj->setBody($this->body);

        $copyObj->setType($this->type);

        $copyObj->setCategory($this->category);

        $copyObj->setSystem($this->system);

        $copyObj->setTimezone($this->timezone);

        $copyObj->setEnable($this->enable);

        $copyObj->setCreationDate($this->creation_date);

        $copyObj->setLastUpdate($this->last_update);


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
     * @return     Scheduler Clone of current object.
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
     * @return     SchedulerPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new SchedulerPeer();
        }
        return self::$peer;
    }
}

