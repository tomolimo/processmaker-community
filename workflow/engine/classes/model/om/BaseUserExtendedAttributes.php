<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/UserExtendedAttributesPeer.php';

/**
 * Base class that represents a row from the 'USER_EXTENDED_ATTRIBUTES' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseUserExtendedAttributes extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        UserExtendedAttributesPeer
    */
    protected static $peer;

    /**
     * The value for the uea_id field.
     * @var        string
     */
    protected $uea_id;

    /**
     * The value for the uea_name field.
     * @var        string
     */
    protected $uea_name;

    /**
     * The value for the uea_attribute_id field.
     * @var        string
     */
    protected $uea_attribute_id;

    /**
     * The value for the uea_hidden field.
     * @var        int
     */
    protected $uea_hidden;

    /**
     * The value for the uea_required field.
     * @var        int
     */
    protected $uea_required;

    /**
     * The value for the uea_password field.
     * @var        int
     */
    protected $uea_password;

    /**
     * The value for the uea_option field.
     * @var        string
     */
    protected $uea_option;

    /**
     * The value for the uea_roles field.
     * @var        string
     */
    protected $uea_roles;

    /**
     * The value for the uea_owner field.
     * @var        string
     */
    protected $uea_owner;

    /**
     * The value for the uea_date_create field.
     * @var        int
     */
    protected $uea_date_create;

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
     * Get the [uea_id] column value.
     * 
     * @return     string
     */
    public function getUeaId()
    {

        return $this->uea_id;
    }

    /**
     * Get the [uea_name] column value.
     * 
     * @return     string
     */
    public function getUeaName()
    {

        return $this->uea_name;
    }

    /**
     * Get the [uea_attribute_id] column value.
     * 
     * @return     string
     */
    public function getUeaAttributeId()
    {

        return $this->uea_attribute_id;
    }

    /**
     * Get the [uea_hidden] column value.
     * 
     * @return     int
     */
    public function getUeaHidden()
    {

        return $this->uea_hidden;
    }

    /**
     * Get the [uea_required] column value.
     * 
     * @return     int
     */
    public function getUeaRequired()
    {

        return $this->uea_required;
    }

    /**
     * Get the [uea_password] column value.
     * 
     * @return     int
     */
    public function getUeaPassword()
    {

        return $this->uea_password;
    }

    /**
     * Get the [uea_option] column value.
     * 
     * @return     string
     */
    public function getUeaOption()
    {

        return $this->uea_option;
    }

    /**
     * Get the [uea_roles] column value.
     * 
     * @return     string
     */
    public function getUeaRoles()
    {

        return $this->uea_roles;
    }

    /**
     * Get the [uea_owner] column value.
     * 
     * @return     string
     */
    public function getUeaOwner()
    {

        return $this->uea_owner;
    }

    /**
     * Get the [optionally formatted] [uea_date_create] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUeaDateCreate($format = 'Y-m-d H:i:s')
    {

        if ($this->uea_date_create === null || $this->uea_date_create === '') {
            return null;
        } elseif (!is_int($this->uea_date_create)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->uea_date_create);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [uea_date_create] as date/time value: " .
                    var_export($this->uea_date_create, true));
            }
        } else {
            $ts = $this->uea_date_create;
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
     * Set the value of [uea_id] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaId($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_id !== $v) {
            $this->uea_id = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_ID;
        }

    } // setUeaId()

    /**
     * Set the value of [uea_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaName($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_name !== $v) {
            $this->uea_name = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_NAME;
        }

    } // setUeaName()

    /**
     * Set the value of [uea_attribute_id] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaAttributeId($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_attribute_id !== $v) {
            $this->uea_attribute_id = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_ATTRIBUTE_ID;
        }

    } // setUeaAttributeId()

    /**
     * Set the value of [uea_hidden] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUeaHidden($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->uea_hidden !== $v) {
            $this->uea_hidden = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_HIDDEN;
        }

    } // setUeaHidden()

    /**
     * Set the value of [uea_required] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUeaRequired($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->uea_required !== $v) {
            $this->uea_required = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_REQUIRED;
        }

    } // setUeaRequired()

    /**
     * Set the value of [uea_password] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUeaPassword($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->uea_password !== $v) {
            $this->uea_password = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_PASSWORD;
        }

    } // setUeaPassword()

    /**
     * Set the value of [uea_option] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaOption($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_option !== $v) {
            $this->uea_option = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_OPTION;
        }

    } // setUeaOption()

    /**
     * Set the value of [uea_roles] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaRoles($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_roles !== $v) {
            $this->uea_roles = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_ROLES;
        }

    } // setUeaRoles()

    /**
     * Set the value of [uea_owner] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUeaOwner($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->uea_owner !== $v) {
            $this->uea_owner = $v;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_OWNER;
        }

    } // setUeaOwner()

    /**
     * Set the value of [uea_date_create] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUeaDateCreate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [uea_date_create] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->uea_date_create !== $ts) {
            $this->uea_date_create = $ts;
            $this->modifiedColumns[] = UserExtendedAttributesPeer::UEA_DATE_CREATE;
        }

    } // setUeaDateCreate()

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

            $this->uea_id = $rs->getString($startcol + 0);

            $this->uea_name = $rs->getString($startcol + 1);

            $this->uea_attribute_id = $rs->getString($startcol + 2);

            $this->uea_hidden = $rs->getInt($startcol + 3);

            $this->uea_required = $rs->getInt($startcol + 4);

            $this->uea_password = $rs->getInt($startcol + 5);

            $this->uea_option = $rs->getString($startcol + 6);

            $this->uea_roles = $rs->getString($startcol + 7);

            $this->uea_owner = $rs->getString($startcol + 8);

            $this->uea_date_create = $rs->getTimestamp($startcol + 9, null);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 10; // 10 = UserExtendedAttributesPeer::NUM_COLUMNS - UserExtendedAttributesPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating UserExtendedAttributes object", $e);
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
            $con = Propel::getConnection(UserExtendedAttributesPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            UserExtendedAttributesPeer::doDelete($this, $con);
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
            $con = Propel::getConnection(UserExtendedAttributesPeer::DATABASE_NAME);
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
                    $pk = UserExtendedAttributesPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setUeaId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += UserExtendedAttributesPeer::doUpdate($this, $con);
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


            if (($retval = UserExtendedAttributesPeer::doValidate($this, $columns)) !== true) {
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
        $pos = UserExtendedAttributesPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getUeaId();
                break;
            case 1:
                return $this->getUeaName();
                break;
            case 2:
                return $this->getUeaAttributeId();
                break;
            case 3:
                return $this->getUeaHidden();
                break;
            case 4:
                return $this->getUeaRequired();
                break;
            case 5:
                return $this->getUeaPassword();
                break;
            case 6:
                return $this->getUeaOption();
                break;
            case 7:
                return $this->getUeaRoles();
                break;
            case 8:
                return $this->getUeaOwner();
                break;
            case 9:
                return $this->getUeaDateCreate();
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
        $keys = UserExtendedAttributesPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getUeaId(),
            $keys[1] => $this->getUeaName(),
            $keys[2] => $this->getUeaAttributeId(),
            $keys[3] => $this->getUeaHidden(),
            $keys[4] => $this->getUeaRequired(),
            $keys[5] => $this->getUeaPassword(),
            $keys[6] => $this->getUeaOption(),
            $keys[7] => $this->getUeaRoles(),
            $keys[8] => $this->getUeaOwner(),
            $keys[9] => $this->getUeaDateCreate(),
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
        $pos = UserExtendedAttributesPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                $this->setUeaId($value);
                break;
            case 1:
                $this->setUeaName($value);
                break;
            case 2:
                $this->setUeaAttributeId($value);
                break;
            case 3:
                $this->setUeaHidden($value);
                break;
            case 4:
                $this->setUeaRequired($value);
                break;
            case 5:
                $this->setUeaPassword($value);
                break;
            case 6:
                $this->setUeaOption($value);
                break;
            case 7:
                $this->setUeaRoles($value);
                break;
            case 8:
                $this->setUeaOwner($value);
                break;
            case 9:
                $this->setUeaDateCreate($value);
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
        $keys = UserExtendedAttributesPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setUeaId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setUeaName($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setUeaAttributeId($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setUeaHidden($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setUeaRequired($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setUeaPassword($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setUeaOption($arr[$keys[6]]);
        }

        if (array_key_exists($keys[7], $arr)) {
            $this->setUeaRoles($arr[$keys[7]]);
        }

        if (array_key_exists($keys[8], $arr)) {
            $this->setUeaOwner($arr[$keys[8]]);
        }

        if (array_key_exists($keys[9], $arr)) {
            $this->setUeaDateCreate($arr[$keys[9]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(UserExtendedAttributesPeer::DATABASE_NAME);

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_ID)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_ID, $this->uea_id);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_NAME)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_NAME, $this->uea_name);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_ATTRIBUTE_ID)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_ATTRIBUTE_ID, $this->uea_attribute_id);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_HIDDEN)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_HIDDEN, $this->uea_hidden);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_REQUIRED)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_REQUIRED, $this->uea_required);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_PASSWORD)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_PASSWORD, $this->uea_password);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_OPTION)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_OPTION, $this->uea_option);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_ROLES)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_ROLES, $this->uea_roles);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_OWNER)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_OWNER, $this->uea_owner);
        }

        if ($this->isColumnModified(UserExtendedAttributesPeer::UEA_DATE_CREATE)) {
            $criteria->add(UserExtendedAttributesPeer::UEA_DATE_CREATE, $this->uea_date_create);
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
        $criteria = new Criteria(UserExtendedAttributesPeer::DATABASE_NAME);

        $criteria->add(UserExtendedAttributesPeer::UEA_ID, $this->uea_id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return     string
     */
    public function getPrimaryKey()
    {
        return $this->getUeaId();
    }

    /**
     * Generic method to set the primary key (uea_id column).
     *
     * @param      string $key Primary key.
     * @return     void
     */
    public function setPrimaryKey($key)
    {
        $this->setUeaId($key);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of UserExtendedAttributes (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setUeaName($this->uea_name);

        $copyObj->setUeaAttributeId($this->uea_attribute_id);

        $copyObj->setUeaHidden($this->uea_hidden);

        $copyObj->setUeaRequired($this->uea_required);

        $copyObj->setUeaPassword($this->uea_password);

        $copyObj->setUeaOption($this->uea_option);

        $copyObj->setUeaRoles($this->uea_roles);

        $copyObj->setUeaOwner($this->uea_owner);

        $copyObj->setUeaDateCreate($this->uea_date_create);


        $copyObj->setNew(true);

        $copyObj->setUeaId(NULL); // this is a pkey column, so set to default value

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
     * @return     UserExtendedAttributes Clone of current object.
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
     * @return     UserExtendedAttributesPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new UserExtendedAttributesPeer();
        }
        return self::$peer;
    }
}

