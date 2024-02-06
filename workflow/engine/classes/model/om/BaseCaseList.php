<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/CaseListPeer.php';

/**
 * Base class that represents a row from the 'CASE_LIST' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseCaseList extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        CaseListPeer
    */
    protected static $peer;

    /**
     * The value for the cal_id field.
     * @var        int
     */
    protected $cal_id;

    /**
     * The value for the cal_type field.
     * @var        string
     */
    protected $cal_type;

    /**
     * The value for the cal_name field.
     * @var        string
     */
    protected $cal_name;

    /**
     * The value for the cal_description field.
     * @var        string
     */
    protected $cal_description;

    /**
     * The value for the add_tab_uid field.
     * @var        string
     */
    protected $add_tab_uid;

    /**
     * The value for the cal_columns field.
     * @var        string
     */
    protected $cal_columns;

    /**
     * The value for the usr_id field.
     * @var        string
     */
    protected $usr_id;

    /**
     * The value for the cal_icon_list field.
     * @var        string
     */
    protected $cal_icon_list;

    /**
     * The value for the cal_icon_color field.
     * @var        string
     */
    protected $cal_icon_color;

    /**
     * The value for the cal_icon_color_screen field.
     * @var        string
     */
    protected $cal_icon_color_screen;

    /**
     * The value for the cal_create_date field.
     * @var        int
     */
    protected $cal_create_date;

    /**
     * The value for the cal_update_date field.
     * @var        int
     */
    protected $cal_update_date;

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
     * Get the [cal_id] column value.
     * 
     * @return     int
     */
    public function getCalId()
    {

        return $this->cal_id;
    }

    /**
     * Get the [cal_type] column value.
     * 
     * @return     string
     */
    public function getCalType()
    {

        return $this->cal_type;
    }

    /**
     * Get the [cal_name] column value.
     * 
     * @return     string
     */
    public function getCalName()
    {

        return $this->cal_name;
    }

    /**
     * Get the [cal_description] column value.
     * 
     * @return     string
     */
    public function getCalDescription()
    {

        return $this->cal_description;
    }

    /**
     * Get the [add_tab_uid] column value.
     * 
     * @return     string
     */
    public function getAddTabUid()
    {

        return $this->add_tab_uid;
    }

    /**
     * Get the [cal_columns] column value.
     * 
     * @return     string
     */
    public function getCalColumns()
    {

        return $this->cal_columns;
    }

    /**
     * Get the [usr_id] column value.
     * 
     * @return     string
     */
    public function getUsrId()
    {

        return $this->usr_id;
    }

    /**
     * Get the [cal_icon_list] column value.
     * 
     * @return     string
     */
    public function getCalIconList()
    {

        return $this->cal_icon_list;
    }

    /**
     * Get the [cal_icon_color] column value.
     * 
     * @return     string
     */
    public function getCalIconColor()
    {

        return $this->cal_icon_color;
    }

    /**
     * Get the [cal_icon_color_screen] column value.
     * 
     * @return     string
     */
    public function getCalIconColorScreen()
    {

        return $this->cal_icon_color_screen;
    }

    /**
     * Get the [optionally formatted] [cal_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getCalCreateDate($format = 'Y-m-d H:i:s')
    {

        if ($this->cal_create_date === null || $this->cal_create_date === '') {
            return null;
        } elseif (!is_int($this->cal_create_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->cal_create_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [cal_create_date] as date/time value: " .
                    var_export($this->cal_create_date, true));
            }
        } else {
            $ts = $this->cal_create_date;
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
     * Get the [optionally formatted] [cal_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getCalUpdateDate($format = 'Y-m-d H:i:s')
    {

        if ($this->cal_update_date === null || $this->cal_update_date === '') {
            return null;
        } elseif (!is_int($this->cal_update_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->cal_update_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [cal_update_date] as date/time value: " .
                    var_export($this->cal_update_date, true));
            }
        } else {
            $ts = $this->cal_update_date;
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
     * Set the value of [cal_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCalId($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->cal_id !== $v) {
            $this->cal_id = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_ID;
        }

    } // setCalId()

    /**
     * Set the value of [cal_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalType($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_type !== $v) {
            $this->cal_type = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_TYPE;
        }

    } // setCalType()

    /**
     * Set the value of [cal_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalName($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_name !== $v) {
            $this->cal_name = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_NAME;
        }

    } // setCalName()

    /**
     * Set the value of [cal_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalDescription($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_description !== $v) {
            $this->cal_description = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_DESCRIPTION;
        }

    } // setCalDescription()

    /**
     * Set the value of [add_tab_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->add_tab_uid !== $v) {
            $this->add_tab_uid = $v;
            $this->modifiedColumns[] = CaseListPeer::ADD_TAB_UID;
        }

    } // setAddTabUid()

    /**
     * Set the value of [cal_columns] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalColumns($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_columns !== $v) {
            $this->cal_columns = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_COLUMNS;
        }

    } // setCalColumns()

    /**
     * Set the value of [usr_id] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrId($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->usr_id !== $v) {
            $this->usr_id = $v;
            $this->modifiedColumns[] = CaseListPeer::USR_ID;
        }

    } // setUsrId()

    /**
     * Set the value of [cal_icon_list] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalIconList($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_icon_list !== $v) {
            $this->cal_icon_list = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_ICON_LIST;
        }

    } // setCalIconList()

    /**
     * Set the value of [cal_icon_color] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalIconColor($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_icon_color !== $v) {
            $this->cal_icon_color = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_ICON_COLOR;
        }

    } // setCalIconColor()

    /**
     * Set the value of [cal_icon_color_screen] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalIconColorScreen($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->cal_icon_color_screen !== $v) {
            $this->cal_icon_color_screen = $v;
            $this->modifiedColumns[] = CaseListPeer::CAL_ICON_COLOR_SCREEN;
        }

    } // setCalIconColorScreen()

    /**
     * Set the value of [cal_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCalCreateDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [cal_create_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->cal_create_date !== $ts) {
            $this->cal_create_date = $ts;
            $this->modifiedColumns[] = CaseListPeer::CAL_CREATE_DATE;
        }

    } // setCalCreateDate()

    /**
     * Set the value of [cal_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCalUpdateDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [cal_update_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->cal_update_date !== $ts) {
            $this->cal_update_date = $ts;
            $this->modifiedColumns[] = CaseListPeer::CAL_UPDATE_DATE;
        }

    } // setCalUpdateDate()

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

            $this->cal_id = $rs->getInt($startcol + 0);

            $this->cal_type = $rs->getString($startcol + 1);

            $this->cal_name = $rs->getString($startcol + 2);

            $this->cal_description = $rs->getString($startcol + 3);

            $this->add_tab_uid = $rs->getString($startcol + 4);

            $this->cal_columns = $rs->getString($startcol + 5);

            $this->usr_id = $rs->getString($startcol + 6);

            $this->cal_icon_list = $rs->getString($startcol + 7);

            $this->cal_icon_color = $rs->getString($startcol + 8);

            $this->cal_icon_color_screen = $rs->getString($startcol + 9);

            $this->cal_create_date = $rs->getTimestamp($startcol + 10, null);

            $this->cal_update_date = $rs->getTimestamp($startcol + 11, null);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 12; // 12 = CaseListPeer::NUM_COLUMNS - CaseListPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating CaseList object", $e);
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
            $con = Propel::getConnection(CaseListPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            CaseListPeer::doDelete($this, $con);
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
            $con = Propel::getConnection(CaseListPeer::DATABASE_NAME);
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
                    $pk = CaseListPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setCalId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += CaseListPeer::doUpdate($this, $con);
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


            if (($retval = CaseListPeer::doValidate($this, $columns)) !== true) {
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
        $pos = CaseListPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getCalId();
                break;
            case 1:
                return $this->getCalType();
                break;
            case 2:
                return $this->getCalName();
                break;
            case 3:
                return $this->getCalDescription();
                break;
            case 4:
                return $this->getAddTabUid();
                break;
            case 5:
                return $this->getCalColumns();
                break;
            case 6:
                return $this->getUsrId();
                break;
            case 7:
                return $this->getCalIconList();
                break;
            case 8:
                return $this->getCalIconColor();
                break;
            case 9:
                return $this->getCalIconColorScreen();
                break;
            case 10:
                return $this->getCalCreateDate();
                break;
            case 11:
                return $this->getCalUpdateDate();
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
        $keys = CaseListPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getCalId(),
            $keys[1] => $this->getCalType(),
            $keys[2] => $this->getCalName(),
            $keys[3] => $this->getCalDescription(),
            $keys[4] => $this->getAddTabUid(),
            $keys[5] => $this->getCalColumns(),
            $keys[6] => $this->getUsrId(),
            $keys[7] => $this->getCalIconList(),
            $keys[8] => $this->getCalIconColor(),
            $keys[9] => $this->getCalIconColorScreen(),
            $keys[10] => $this->getCalCreateDate(),
            $keys[11] => $this->getCalUpdateDate(),
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
        $pos = CaseListPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                $this->setCalId($value);
                break;
            case 1:
                $this->setCalType($value);
                break;
            case 2:
                $this->setCalName($value);
                break;
            case 3:
                $this->setCalDescription($value);
                break;
            case 4:
                $this->setAddTabUid($value);
                break;
            case 5:
                $this->setCalColumns($value);
                break;
            case 6:
                $this->setUsrId($value);
                break;
            case 7:
                $this->setCalIconList($value);
                break;
            case 8:
                $this->setCalIconColor($value);
                break;
            case 9:
                $this->setCalIconColorScreen($value);
                break;
            case 10:
                $this->setCalCreateDate($value);
                break;
            case 11:
                $this->setCalUpdateDate($value);
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
        $keys = CaseListPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setCalId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setCalType($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setCalName($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setCalDescription($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setAddTabUid($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setCalColumns($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setUsrId($arr[$keys[6]]);
        }

        if (array_key_exists($keys[7], $arr)) {
            $this->setCalIconList($arr[$keys[7]]);
        }

        if (array_key_exists($keys[8], $arr)) {
            $this->setCalIconColor($arr[$keys[8]]);
        }

        if (array_key_exists($keys[9], $arr)) {
            $this->setCalIconColorScreen($arr[$keys[9]]);
        }

        if (array_key_exists($keys[10], $arr)) {
            $this->setCalCreateDate($arr[$keys[10]]);
        }

        if (array_key_exists($keys[11], $arr)) {
            $this->setCalUpdateDate($arr[$keys[11]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CaseListPeer::DATABASE_NAME);

        if ($this->isColumnModified(CaseListPeer::CAL_ID)) {
            $criteria->add(CaseListPeer::CAL_ID, $this->cal_id);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_TYPE)) {
            $criteria->add(CaseListPeer::CAL_TYPE, $this->cal_type);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_NAME)) {
            $criteria->add(CaseListPeer::CAL_NAME, $this->cal_name);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_DESCRIPTION)) {
            $criteria->add(CaseListPeer::CAL_DESCRIPTION, $this->cal_description);
        }

        if ($this->isColumnModified(CaseListPeer::ADD_TAB_UID)) {
            $criteria->add(CaseListPeer::ADD_TAB_UID, $this->add_tab_uid);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_COLUMNS)) {
            $criteria->add(CaseListPeer::CAL_COLUMNS, $this->cal_columns);
        }

        if ($this->isColumnModified(CaseListPeer::USR_ID)) {
            $criteria->add(CaseListPeer::USR_ID, $this->usr_id);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_ICON_LIST)) {
            $criteria->add(CaseListPeer::CAL_ICON_LIST, $this->cal_icon_list);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_ICON_COLOR)) {
            $criteria->add(CaseListPeer::CAL_ICON_COLOR, $this->cal_icon_color);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_ICON_COLOR_SCREEN)) {
            $criteria->add(CaseListPeer::CAL_ICON_COLOR_SCREEN, $this->cal_icon_color_screen);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_CREATE_DATE)) {
            $criteria->add(CaseListPeer::CAL_CREATE_DATE, $this->cal_create_date);
        }

        if ($this->isColumnModified(CaseListPeer::CAL_UPDATE_DATE)) {
            $criteria->add(CaseListPeer::CAL_UPDATE_DATE, $this->cal_update_date);
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
        $criteria = new Criteria(CaseListPeer::DATABASE_NAME);

        $criteria->add(CaseListPeer::CAL_ID, $this->cal_id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return     int
     */
    public function getPrimaryKey()
    {
        return $this->getCalId();
    }

    /**
     * Generic method to set the primary key (cal_id column).
     *
     * @param      int $key Primary key.
     * @return     void
     */
    public function setPrimaryKey($key)
    {
        $this->setCalId($key);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of CaseList (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setCalType($this->cal_type);

        $copyObj->setCalName($this->cal_name);

        $copyObj->setCalDescription($this->cal_description);

        $copyObj->setAddTabUid($this->add_tab_uid);

        $copyObj->setCalColumns($this->cal_columns);

        $copyObj->setUsrId($this->usr_id);

        $copyObj->setCalIconList($this->cal_icon_list);

        $copyObj->setCalIconColor($this->cal_icon_color);

        $copyObj->setCalIconColorScreen($this->cal_icon_color_screen);

        $copyObj->setCalCreateDate($this->cal_create_date);

        $copyObj->setCalUpdateDate($this->cal_update_date);


        $copyObj->setNew(true);

        $copyObj->setCalId(NULL); // this is a pkey column, so set to default value

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
     * @return     CaseList Clone of current object.
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
     * @return     CaseListPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new CaseListPeer();
        }
        return self::$peer;
    }
}

