<?php

require_once 'propel/util/BasePeer.php';
// The object class -- needed for instanceof checks in this class.
// actual class may be a subclass -- as returned by TaskPeer::getOMClass()
include_once 'classes/model/Task.php';

/**
 * Base static class for performing query and update operations on the 'TASK' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseTaskPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'workflow';

    /** the table name for this class */
    const TABLE_NAME = 'TASK';

    /** A class that can be returned by this peer. */
    const CLASS_DEFAULT = 'classes.model.Task';

    /** The total number of columns. */
    const NUM_COLUMNS = 67;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;


    /** the column name for the PRO_UID field */
    const PRO_UID = 'TASK.PRO_UID';

    /** the column name for the PRO_ID field */
    const PRO_ID = 'TASK.PRO_ID';

    /** the column name for the TAS_UID field */
    const TAS_UID = 'TASK.TAS_UID';

    /** the column name for the TAS_ID field */
    const TAS_ID = 'TASK.TAS_ID';

    /** the column name for the TAS_TITLE field */
    const TAS_TITLE = 'TASK.TAS_TITLE';

    /** the column name for the TAS_DESCRIPTION field */
    const TAS_DESCRIPTION = 'TASK.TAS_DESCRIPTION';

    /** the column name for the TAS_DEF_TITLE field */
    const TAS_DEF_TITLE = 'TASK.TAS_DEF_TITLE';

    /** the column name for the TAS_DEF_SUBJECT_MESSAGE field */
    const TAS_DEF_SUBJECT_MESSAGE = 'TASK.TAS_DEF_SUBJECT_MESSAGE';

    /** the column name for the TAS_DEF_PROC_CODE field */
    const TAS_DEF_PROC_CODE = 'TASK.TAS_DEF_PROC_CODE';

    /** the column name for the TAS_DEF_MESSAGE field */
    const TAS_DEF_MESSAGE = 'TASK.TAS_DEF_MESSAGE';

    /** the column name for the TAS_DEF_DESCRIPTION field */
    const TAS_DEF_DESCRIPTION = 'TASK.TAS_DEF_DESCRIPTION';

    /** the column name for the TAS_TYPE field */
    const TAS_TYPE = 'TASK.TAS_TYPE';

    /** the column name for the TAS_DURATION field */
    const TAS_DURATION = 'TASK.TAS_DURATION';

    /** the column name for the TAS_DELAY_TYPE field */
    const TAS_DELAY_TYPE = 'TASK.TAS_DELAY_TYPE';

    /** the column name for the TAS_TEMPORIZER field */
    const TAS_TEMPORIZER = 'TASK.TAS_TEMPORIZER';

    /** the column name for the TAS_TYPE_DAY field */
    const TAS_TYPE_DAY = 'TASK.TAS_TYPE_DAY';

    /** the column name for the TAS_TIMEUNIT field */
    const TAS_TIMEUNIT = 'TASK.TAS_TIMEUNIT';

    /** the column name for the TAS_ALERT field */
    const TAS_ALERT = 'TASK.TAS_ALERT';

    /** the column name for the TAS_PRIORITY_VARIABLE field */
    const TAS_PRIORITY_VARIABLE = 'TASK.TAS_PRIORITY_VARIABLE';

    /** the column name for the TAS_ASSIGN_TYPE field */
    const TAS_ASSIGN_TYPE = 'TASK.TAS_ASSIGN_TYPE';

    /** the column name for the TAS_ASSIGN_VARIABLE field */
    const TAS_ASSIGN_VARIABLE = 'TASK.TAS_ASSIGN_VARIABLE';

    /** the column name for the TAS_GROUP_VARIABLE field */
    const TAS_GROUP_VARIABLE = 'TASK.TAS_GROUP_VARIABLE';

    /** the column name for the TAS_MI_INSTANCE_VARIABLE field */
    const TAS_MI_INSTANCE_VARIABLE = 'TASK.TAS_MI_INSTANCE_VARIABLE';

    /** the column name for the TAS_MI_COMPLETE_VARIABLE field */
    const TAS_MI_COMPLETE_VARIABLE = 'TASK.TAS_MI_COMPLETE_VARIABLE';

    /** the column name for the TAS_ASSIGN_LOCATION field */
    const TAS_ASSIGN_LOCATION = 'TASK.TAS_ASSIGN_LOCATION';

    /** the column name for the TAS_ASSIGN_LOCATION_ADHOC field */
    const TAS_ASSIGN_LOCATION_ADHOC = 'TASK.TAS_ASSIGN_LOCATION_ADHOC';

    /** the column name for the TAS_TRANSFER_FLY field */
    const TAS_TRANSFER_FLY = 'TASK.TAS_TRANSFER_FLY';

    /** the column name for the TAS_LAST_ASSIGNED field */
    const TAS_LAST_ASSIGNED = 'TASK.TAS_LAST_ASSIGNED';

    /** the column name for the TAS_USER field */
    const TAS_USER = 'TASK.TAS_USER';

    /** the column name for the TAS_CAN_UPLOAD field */
    const TAS_CAN_UPLOAD = 'TASK.TAS_CAN_UPLOAD';

    /** the column name for the TAS_VIEW_UPLOAD field */
    const TAS_VIEW_UPLOAD = 'TASK.TAS_VIEW_UPLOAD';

    /** the column name for the TAS_VIEW_ADDITIONAL_DOCUMENTATION field */
    const TAS_VIEW_ADDITIONAL_DOCUMENTATION = 'TASK.TAS_VIEW_ADDITIONAL_DOCUMENTATION';

    /** the column name for the TAS_CAN_CANCEL field */
    const TAS_CAN_CANCEL = 'TASK.TAS_CAN_CANCEL';

    /** the column name for the TAS_OWNER_APP field */
    const TAS_OWNER_APP = 'TASK.TAS_OWNER_APP';

    /** the column name for the STG_UID field */
    const STG_UID = 'TASK.STG_UID';

    /** the column name for the TAS_CAN_PAUSE field */
    const TAS_CAN_PAUSE = 'TASK.TAS_CAN_PAUSE';

    /** the column name for the TAS_CAN_SEND_MESSAGE field */
    const TAS_CAN_SEND_MESSAGE = 'TASK.TAS_CAN_SEND_MESSAGE';

    /** the column name for the TAS_CAN_DELETE_DOCS field */
    const TAS_CAN_DELETE_DOCS = 'TASK.TAS_CAN_DELETE_DOCS';

    /** the column name for the TAS_SELF_SERVICE field */
    const TAS_SELF_SERVICE = 'TASK.TAS_SELF_SERVICE';

    /** the column name for the TAS_START field */
    const TAS_START = 'TASK.TAS_START';

    /** the column name for the TAS_TO_LAST_USER field */
    const TAS_TO_LAST_USER = 'TASK.TAS_TO_LAST_USER';

    /** the column name for the TAS_SEND_LAST_EMAIL field */
    const TAS_SEND_LAST_EMAIL = 'TASK.TAS_SEND_LAST_EMAIL';

    /** the column name for the TAS_DERIVATION field */
    const TAS_DERIVATION = 'TASK.TAS_DERIVATION';

    /** the column name for the TAS_POSX field */
    const TAS_POSX = 'TASK.TAS_POSX';

    /** the column name for the TAS_POSY field */
    const TAS_POSY = 'TASK.TAS_POSY';

    /** the column name for the TAS_WIDTH field */
    const TAS_WIDTH = 'TASK.TAS_WIDTH';

    /** the column name for the TAS_HEIGHT field */
    const TAS_HEIGHT = 'TASK.TAS_HEIGHT';

    /** the column name for the TAS_COLOR field */
    const TAS_COLOR = 'TASK.TAS_COLOR';

    /** the column name for the TAS_EVN_UID field */
    const TAS_EVN_UID = 'TASK.TAS_EVN_UID';

    /** the column name for the TAS_BOUNDARY field */
    const TAS_BOUNDARY = 'TASK.TAS_BOUNDARY';

    /** the column name for the TAS_DERIVATION_SCREEN_TPL field */
    const TAS_DERIVATION_SCREEN_TPL = 'TASK.TAS_DERIVATION_SCREEN_TPL';

    /** the column name for the TAS_SELFSERVICE_TIMEOUT field */
    const TAS_SELFSERVICE_TIMEOUT = 'TASK.TAS_SELFSERVICE_TIMEOUT';

    /** the column name for the TAS_SELFSERVICE_TIME field */
    const TAS_SELFSERVICE_TIME = 'TASK.TAS_SELFSERVICE_TIME';

    /** the column name for the TAS_SELFSERVICE_TIME_UNIT field */
    const TAS_SELFSERVICE_TIME_UNIT = 'TASK.TAS_SELFSERVICE_TIME_UNIT';

    /** the column name for the TAS_SELFSERVICE_TRIGGER_UID field */
    const TAS_SELFSERVICE_TRIGGER_UID = 'TASK.TAS_SELFSERVICE_TRIGGER_UID';

    /** the column name for the TAS_SELFSERVICE_EXECUTION field */
    const TAS_SELFSERVICE_EXECUTION = 'TASK.TAS_SELFSERVICE_EXECUTION';

    /** the column name for the TAS_NOT_EMAIL_FROM_FORMAT field */
    const TAS_NOT_EMAIL_FROM_FORMAT = 'TASK.TAS_NOT_EMAIL_FROM_FORMAT';

    /** the column name for the TAS_OFFLINE field */
    const TAS_OFFLINE = 'TASK.TAS_OFFLINE';

    /** the column name for the TAS_EMAIL_SERVER_UID field */
    const TAS_EMAIL_SERVER_UID = 'TASK.TAS_EMAIL_SERVER_UID';

    /** the column name for the TAS_AUTO_ROOT field */
    const TAS_AUTO_ROOT = 'TASK.TAS_AUTO_ROOT';

    /** the column name for the TAS_RECEIVE_SERVER_UID field */
    const TAS_RECEIVE_SERVER_UID = 'TASK.TAS_RECEIVE_SERVER_UID';

    /** the column name for the TAS_RECEIVE_LAST_EMAIL field */
    const TAS_RECEIVE_LAST_EMAIL = 'TASK.TAS_RECEIVE_LAST_EMAIL';

    /** the column name for the TAS_RECEIVE_EMAIL_FROM_FORMAT field */
    const TAS_RECEIVE_EMAIL_FROM_FORMAT = 'TASK.TAS_RECEIVE_EMAIL_FROM_FORMAT';

    /** the column name for the TAS_RECEIVE_MESSAGE_TYPE field */
    const TAS_RECEIVE_MESSAGE_TYPE = 'TASK.TAS_RECEIVE_MESSAGE_TYPE';

    /** the column name for the TAS_RECEIVE_MESSAGE_TEMPLATE field */
    const TAS_RECEIVE_MESSAGE_TEMPLATE = 'TASK.TAS_RECEIVE_MESSAGE_TEMPLATE';

    /** the column name for the TAS_RECEIVE_SUBJECT_MESSAGE field */
    const TAS_RECEIVE_SUBJECT_MESSAGE = 'TASK.TAS_RECEIVE_SUBJECT_MESSAGE';

    /** the column name for the TAS_RECEIVE_MESSAGE field */
    const TAS_RECEIVE_MESSAGE = 'TASK.TAS_RECEIVE_MESSAGE';

    /** The PHP to DB Name Mapping */
    private static $phpNameMap = null;


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    private static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('ProUid', 'ProId', 'TasUid', 'TasId', 'TasTitle', 'TasDescription', 'TasDefTitle', 'TasDefSubjectMessage', 'TasDefProcCode', 'TasDefMessage', 'TasDefDescription', 'TasType', 'TasDuration', 'TasDelayType', 'TasTemporizer', 'TasTypeDay', 'TasTimeunit', 'TasAlert', 'TasPriorityVariable', 'TasAssignType', 'TasAssignVariable', 'TasGroupVariable', 'TasMiInstanceVariable', 'TasMiCompleteVariable', 'TasAssignLocation', 'TasAssignLocationAdhoc', 'TasTransferFly', 'TasLastAssigned', 'TasUser', 'TasCanUpload', 'TasViewUpload', 'TasViewAdditionalDocumentation', 'TasCanCancel', 'TasOwnerApp', 'StgUid', 'TasCanPause', 'TasCanSendMessage', 'TasCanDeleteDocs', 'TasSelfService', 'TasStart', 'TasToLastUser', 'TasSendLastEmail', 'TasDerivation', 'TasPosx', 'TasPosy', 'TasWidth', 'TasHeight', 'TasColor', 'TasEvnUid', 'TasBoundary', 'TasDerivationScreenTpl', 'TasSelfserviceTimeout', 'TasSelfserviceTime', 'TasSelfserviceTimeUnit', 'TasSelfserviceTriggerUid', 'TasSelfserviceExecution', 'TasNotEmailFromFormat', 'TasOffline', 'TasEmailServerUid', 'TasAutoRoot', 'TasReceiveServerUid', 'TasReceiveLastEmail', 'TasReceiveEmailFromFormat', 'TasReceiveMessageType', 'TasReceiveMessageTemplate', 'TasReceiveSubjectMessage', 'TasReceiveMessage', ),
        BasePeer::TYPE_COLNAME => array (TaskPeer::PRO_UID, TaskPeer::PRO_ID, TaskPeer::TAS_UID, TaskPeer::TAS_ID, TaskPeer::TAS_TITLE, TaskPeer::TAS_DESCRIPTION, TaskPeer::TAS_DEF_TITLE, TaskPeer::TAS_DEF_SUBJECT_MESSAGE, TaskPeer::TAS_DEF_PROC_CODE, TaskPeer::TAS_DEF_MESSAGE, TaskPeer::TAS_DEF_DESCRIPTION, TaskPeer::TAS_TYPE, TaskPeer::TAS_DURATION, TaskPeer::TAS_DELAY_TYPE, TaskPeer::TAS_TEMPORIZER, TaskPeer::TAS_TYPE_DAY, TaskPeer::TAS_TIMEUNIT, TaskPeer::TAS_ALERT, TaskPeer::TAS_PRIORITY_VARIABLE, TaskPeer::TAS_ASSIGN_TYPE, TaskPeer::TAS_ASSIGN_VARIABLE, TaskPeer::TAS_GROUP_VARIABLE, TaskPeer::TAS_MI_INSTANCE_VARIABLE, TaskPeer::TAS_MI_COMPLETE_VARIABLE, TaskPeer::TAS_ASSIGN_LOCATION, TaskPeer::TAS_ASSIGN_LOCATION_ADHOC, TaskPeer::TAS_TRANSFER_FLY, TaskPeer::TAS_LAST_ASSIGNED, TaskPeer::TAS_USER, TaskPeer::TAS_CAN_UPLOAD, TaskPeer::TAS_VIEW_UPLOAD, TaskPeer::TAS_VIEW_ADDITIONAL_DOCUMENTATION, TaskPeer::TAS_CAN_CANCEL, TaskPeer::TAS_OWNER_APP, TaskPeer::STG_UID, TaskPeer::TAS_CAN_PAUSE, TaskPeer::TAS_CAN_SEND_MESSAGE, TaskPeer::TAS_CAN_DELETE_DOCS, TaskPeer::TAS_SELF_SERVICE, TaskPeer::TAS_START, TaskPeer::TAS_TO_LAST_USER, TaskPeer::TAS_SEND_LAST_EMAIL, TaskPeer::TAS_DERIVATION, TaskPeer::TAS_POSX, TaskPeer::TAS_POSY, TaskPeer::TAS_WIDTH, TaskPeer::TAS_HEIGHT, TaskPeer::TAS_COLOR, TaskPeer::TAS_EVN_UID, TaskPeer::TAS_BOUNDARY, TaskPeer::TAS_DERIVATION_SCREEN_TPL, TaskPeer::TAS_SELFSERVICE_TIMEOUT, TaskPeer::TAS_SELFSERVICE_TIME, TaskPeer::TAS_SELFSERVICE_TIME_UNIT, TaskPeer::TAS_SELFSERVICE_TRIGGER_UID, TaskPeer::TAS_SELFSERVICE_EXECUTION, TaskPeer::TAS_NOT_EMAIL_FROM_FORMAT, TaskPeer::TAS_OFFLINE, TaskPeer::TAS_EMAIL_SERVER_UID, TaskPeer::TAS_AUTO_ROOT, TaskPeer::TAS_RECEIVE_SERVER_UID, TaskPeer::TAS_RECEIVE_LAST_EMAIL, TaskPeer::TAS_RECEIVE_EMAIL_FROM_FORMAT, TaskPeer::TAS_RECEIVE_MESSAGE_TYPE, TaskPeer::TAS_RECEIVE_MESSAGE_TEMPLATE, TaskPeer::TAS_RECEIVE_SUBJECT_MESSAGE, TaskPeer::TAS_RECEIVE_MESSAGE, ),
        BasePeer::TYPE_FIELDNAME => array ('PRO_UID', 'PRO_ID', 'TAS_UID', 'TAS_ID', 'TAS_TITLE', 'TAS_DESCRIPTION', 'TAS_DEF_TITLE', 'TAS_DEF_SUBJECT_MESSAGE', 'TAS_DEF_PROC_CODE', 'TAS_DEF_MESSAGE', 'TAS_DEF_DESCRIPTION', 'TAS_TYPE', 'TAS_DURATION', 'TAS_DELAY_TYPE', 'TAS_TEMPORIZER', 'TAS_TYPE_DAY', 'TAS_TIMEUNIT', 'TAS_ALERT', 'TAS_PRIORITY_VARIABLE', 'TAS_ASSIGN_TYPE', 'TAS_ASSIGN_VARIABLE', 'TAS_GROUP_VARIABLE', 'TAS_MI_INSTANCE_VARIABLE', 'TAS_MI_COMPLETE_VARIABLE', 'TAS_ASSIGN_LOCATION', 'TAS_ASSIGN_LOCATION_ADHOC', 'TAS_TRANSFER_FLY', 'TAS_LAST_ASSIGNED', 'TAS_USER', 'TAS_CAN_UPLOAD', 'TAS_VIEW_UPLOAD', 'TAS_VIEW_ADDITIONAL_DOCUMENTATION', 'TAS_CAN_CANCEL', 'TAS_OWNER_APP', 'STG_UID', 'TAS_CAN_PAUSE', 'TAS_CAN_SEND_MESSAGE', 'TAS_CAN_DELETE_DOCS', 'TAS_SELF_SERVICE', 'TAS_START', 'TAS_TO_LAST_USER', 'TAS_SEND_LAST_EMAIL', 'TAS_DERIVATION', 'TAS_POSX', 'TAS_POSY', 'TAS_WIDTH', 'TAS_HEIGHT', 'TAS_COLOR', 'TAS_EVN_UID', 'TAS_BOUNDARY', 'TAS_DERIVATION_SCREEN_TPL', 'TAS_SELFSERVICE_TIMEOUT', 'TAS_SELFSERVICE_TIME', 'TAS_SELFSERVICE_TIME_UNIT', 'TAS_SELFSERVICE_TRIGGER_UID', 'TAS_SELFSERVICE_EXECUTION', 'TAS_NOT_EMAIL_FROM_FORMAT', 'TAS_OFFLINE', 'TAS_EMAIL_SERVER_UID', 'TAS_AUTO_ROOT', 'TAS_RECEIVE_SERVER_UID', 'TAS_RECEIVE_LAST_EMAIL', 'TAS_RECEIVE_EMAIL_FROM_FORMAT', 'TAS_RECEIVE_MESSAGE_TYPE', 'TAS_RECEIVE_MESSAGE_TEMPLATE', 'TAS_RECEIVE_SUBJECT_MESSAGE', 'TAS_RECEIVE_MESSAGE', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    private static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('ProUid' => 0, 'ProId' => 1, 'TasUid' => 2, 'TasId' => 3, 'TasTitle' => 4, 'TasDescription' => 5, 'TasDefTitle' => 6, 'TasDefSubjectMessage' => 7, 'TasDefProcCode' => 8, 'TasDefMessage' => 9, 'TasDefDescription' => 10, 'TasType' => 11, 'TasDuration' => 12, 'TasDelayType' => 13, 'TasTemporizer' => 14, 'TasTypeDay' => 15, 'TasTimeunit' => 16, 'TasAlert' => 17, 'TasPriorityVariable' => 18, 'TasAssignType' => 19, 'TasAssignVariable' => 20, 'TasGroupVariable' => 21, 'TasMiInstanceVariable' => 22, 'TasMiCompleteVariable' => 23, 'TasAssignLocation' => 24, 'TasAssignLocationAdhoc' => 25, 'TasTransferFly' => 26, 'TasLastAssigned' => 27, 'TasUser' => 28, 'TasCanUpload' => 29, 'TasViewUpload' => 30, 'TasViewAdditionalDocumentation' => 31, 'TasCanCancel' => 32, 'TasOwnerApp' => 33, 'StgUid' => 34, 'TasCanPause' => 35, 'TasCanSendMessage' => 36, 'TasCanDeleteDocs' => 37, 'TasSelfService' => 38, 'TasStart' => 39, 'TasToLastUser' => 40, 'TasSendLastEmail' => 41, 'TasDerivation' => 42, 'TasPosx' => 43, 'TasPosy' => 44, 'TasWidth' => 45, 'TasHeight' => 46, 'TasColor' => 47, 'TasEvnUid' => 48, 'TasBoundary' => 49, 'TasDerivationScreenTpl' => 50, 'TasSelfserviceTimeout' => 51, 'TasSelfserviceTime' => 52, 'TasSelfserviceTimeUnit' => 53, 'TasSelfserviceTriggerUid' => 54, 'TasSelfserviceExecution' => 55, 'TasNotEmailFromFormat' => 56, 'TasOffline' => 57, 'TasEmailServerUid' => 58, 'TasAutoRoot' => 59, 'TasReceiveServerUid' => 60, 'TasReceiveLastEmail' => 61, 'TasReceiveEmailFromFormat' => 62, 'TasReceiveMessageType' => 63, 'TasReceiveMessageTemplate' => 64, 'TasReceiveSubjectMessage' => 65, 'TasReceiveMessage' => 66, ),
        BasePeer::TYPE_COLNAME => array (TaskPeer::PRO_UID => 0, TaskPeer::PRO_ID => 1, TaskPeer::TAS_UID => 2, TaskPeer::TAS_ID => 3, TaskPeer::TAS_TITLE => 4, TaskPeer::TAS_DESCRIPTION => 5, TaskPeer::TAS_DEF_TITLE => 6, TaskPeer::TAS_DEF_SUBJECT_MESSAGE => 7, TaskPeer::TAS_DEF_PROC_CODE => 8, TaskPeer::TAS_DEF_MESSAGE => 9, TaskPeer::TAS_DEF_DESCRIPTION => 10, TaskPeer::TAS_TYPE => 11, TaskPeer::TAS_DURATION => 12, TaskPeer::TAS_DELAY_TYPE => 13, TaskPeer::TAS_TEMPORIZER => 14, TaskPeer::TAS_TYPE_DAY => 15, TaskPeer::TAS_TIMEUNIT => 16, TaskPeer::TAS_ALERT => 17, TaskPeer::TAS_PRIORITY_VARIABLE => 18, TaskPeer::TAS_ASSIGN_TYPE => 19, TaskPeer::TAS_ASSIGN_VARIABLE => 20, TaskPeer::TAS_GROUP_VARIABLE => 21, TaskPeer::TAS_MI_INSTANCE_VARIABLE => 22, TaskPeer::TAS_MI_COMPLETE_VARIABLE => 23, TaskPeer::TAS_ASSIGN_LOCATION => 24, TaskPeer::TAS_ASSIGN_LOCATION_ADHOC => 25, TaskPeer::TAS_TRANSFER_FLY => 26, TaskPeer::TAS_LAST_ASSIGNED => 27, TaskPeer::TAS_USER => 28, TaskPeer::TAS_CAN_UPLOAD => 29, TaskPeer::TAS_VIEW_UPLOAD => 30, TaskPeer::TAS_VIEW_ADDITIONAL_DOCUMENTATION => 31, TaskPeer::TAS_CAN_CANCEL => 32, TaskPeer::TAS_OWNER_APP => 33, TaskPeer::STG_UID => 34, TaskPeer::TAS_CAN_PAUSE => 35, TaskPeer::TAS_CAN_SEND_MESSAGE => 36, TaskPeer::TAS_CAN_DELETE_DOCS => 37, TaskPeer::TAS_SELF_SERVICE => 38, TaskPeer::TAS_START => 39, TaskPeer::TAS_TO_LAST_USER => 40, TaskPeer::TAS_SEND_LAST_EMAIL => 41, TaskPeer::TAS_DERIVATION => 42, TaskPeer::TAS_POSX => 43, TaskPeer::TAS_POSY => 44, TaskPeer::TAS_WIDTH => 45, TaskPeer::TAS_HEIGHT => 46, TaskPeer::TAS_COLOR => 47, TaskPeer::TAS_EVN_UID => 48, TaskPeer::TAS_BOUNDARY => 49, TaskPeer::TAS_DERIVATION_SCREEN_TPL => 50, TaskPeer::TAS_SELFSERVICE_TIMEOUT => 51, TaskPeer::TAS_SELFSERVICE_TIME => 52, TaskPeer::TAS_SELFSERVICE_TIME_UNIT => 53, TaskPeer::TAS_SELFSERVICE_TRIGGER_UID => 54, TaskPeer::TAS_SELFSERVICE_EXECUTION => 55, TaskPeer::TAS_NOT_EMAIL_FROM_FORMAT => 56, TaskPeer::TAS_OFFLINE => 57, TaskPeer::TAS_EMAIL_SERVER_UID => 58, TaskPeer::TAS_AUTO_ROOT => 59, TaskPeer::TAS_RECEIVE_SERVER_UID => 60, TaskPeer::TAS_RECEIVE_LAST_EMAIL => 61, TaskPeer::TAS_RECEIVE_EMAIL_FROM_FORMAT => 62, TaskPeer::TAS_RECEIVE_MESSAGE_TYPE => 63, TaskPeer::TAS_RECEIVE_MESSAGE_TEMPLATE => 64, TaskPeer::TAS_RECEIVE_SUBJECT_MESSAGE => 65, TaskPeer::TAS_RECEIVE_MESSAGE => 66, ),
        BasePeer::TYPE_FIELDNAME => array ('PRO_UID' => 0, 'PRO_ID' => 1, 'TAS_UID' => 2, 'TAS_ID' => 3, 'TAS_TITLE' => 4, 'TAS_DESCRIPTION' => 5, 'TAS_DEF_TITLE' => 6, 'TAS_DEF_SUBJECT_MESSAGE' => 7, 'TAS_DEF_PROC_CODE' => 8, 'TAS_DEF_MESSAGE' => 9, 'TAS_DEF_DESCRIPTION' => 10, 'TAS_TYPE' => 11, 'TAS_DURATION' => 12, 'TAS_DELAY_TYPE' => 13, 'TAS_TEMPORIZER' => 14, 'TAS_TYPE_DAY' => 15, 'TAS_TIMEUNIT' => 16, 'TAS_ALERT' => 17, 'TAS_PRIORITY_VARIABLE' => 18, 'TAS_ASSIGN_TYPE' => 19, 'TAS_ASSIGN_VARIABLE' => 20, 'TAS_GROUP_VARIABLE' => 21, 'TAS_MI_INSTANCE_VARIABLE' => 22, 'TAS_MI_COMPLETE_VARIABLE' => 23, 'TAS_ASSIGN_LOCATION' => 24, 'TAS_ASSIGN_LOCATION_ADHOC' => 25, 'TAS_TRANSFER_FLY' => 26, 'TAS_LAST_ASSIGNED' => 27, 'TAS_USER' => 28, 'TAS_CAN_UPLOAD' => 29, 'TAS_VIEW_UPLOAD' => 30, 'TAS_VIEW_ADDITIONAL_DOCUMENTATION' => 31, 'TAS_CAN_CANCEL' => 32, 'TAS_OWNER_APP' => 33, 'STG_UID' => 34, 'TAS_CAN_PAUSE' => 35, 'TAS_CAN_SEND_MESSAGE' => 36, 'TAS_CAN_DELETE_DOCS' => 37, 'TAS_SELF_SERVICE' => 38, 'TAS_START' => 39, 'TAS_TO_LAST_USER' => 40, 'TAS_SEND_LAST_EMAIL' => 41, 'TAS_DERIVATION' => 42, 'TAS_POSX' => 43, 'TAS_POSY' => 44, 'TAS_WIDTH' => 45, 'TAS_HEIGHT' => 46, 'TAS_COLOR' => 47, 'TAS_EVN_UID' => 48, 'TAS_BOUNDARY' => 49, 'TAS_DERIVATION_SCREEN_TPL' => 50, 'TAS_SELFSERVICE_TIMEOUT' => 51, 'TAS_SELFSERVICE_TIME' => 52, 'TAS_SELFSERVICE_TIME_UNIT' => 53, 'TAS_SELFSERVICE_TRIGGER_UID' => 54, 'TAS_SELFSERVICE_EXECUTION' => 55, 'TAS_NOT_EMAIL_FROM_FORMAT' => 56, 'TAS_OFFLINE' => 57, 'TAS_EMAIL_SERVER_UID' => 58, 'TAS_AUTO_ROOT' => 59, 'TAS_RECEIVE_SERVER_UID' => 60, 'TAS_RECEIVE_LAST_EMAIL' => 61, 'TAS_RECEIVE_EMAIL_FROM_FORMAT' => 62, 'TAS_RECEIVE_MESSAGE_TYPE' => 63, 'TAS_RECEIVE_MESSAGE_TEMPLATE' => 64, 'TAS_RECEIVE_SUBJECT_MESSAGE' => 65, 'TAS_RECEIVE_MESSAGE' => 66, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, )
    );

    /**
     * @return     MapBuilder the map builder for this peer
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function getMapBuilder()
    {
        include_once 'classes/model/map/TaskMapBuilder.php';
        return BasePeer::getMapBuilder('classes.model.map.TaskMapBuilder');
    }
    /**
     * Gets a map (hash) of PHP names to DB column names.
     *
     * @return     array The PHP to DB name map for this peer
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     * @deprecated Use the getFieldNames() and translateFieldName() methods instead of this.
     */
    public static function getPhpNameMap()
    {
        if (self::$phpNameMap === null) {
            $map = TaskPeer::getTableMap();
            $columns = $map->getColumns();
            $nameMap = array();
            foreach ($columns as $column) {
                $nameMap[$column->getPhpName()] = $column->getColumnName();
            }
            self::$phpNameMap = $nameMap;
        }
        return self::$phpNameMap;
    }
    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants TYPE_PHPNAME,
     *                         TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return     string translated name of the field.
     */
    static public function translateFieldName($name, $fromType, $toType)
    {
        $toNames = self::getFieldNames($toType);
        $key = isset(self::$fieldKeys[$fromType][$name]) ? self::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(self::$fieldKeys[$fromType], true));
        }
        return $toNames[$key];
    }

    /**
     * Returns an array of of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants TYPE_PHPNAME,
     *                      TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     array A list of field names
     */

    static public function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, self::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM. ' . $type . ' was given.');
        }
        return self::$fieldNames[$type];
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *      $c->addAlias("alias1", TablePeer::TABLE_NAME);
     *      $c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. TaskPeer::COLUMN_NAME).
     * @return     string
     */
    public static function alias($alias, $column)
    {
        return str_replace(TaskPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      criteria object containing the columns to add.
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria)
    {

        $criteria->addSelectColumn(TaskPeer::PRO_UID);

        $criteria->addSelectColumn(TaskPeer::PRO_ID);

        $criteria->addSelectColumn(TaskPeer::TAS_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_ID);

        $criteria->addSelectColumn(TaskPeer::TAS_TITLE);

        $criteria->addSelectColumn(TaskPeer::TAS_DESCRIPTION);

        $criteria->addSelectColumn(TaskPeer::TAS_DEF_TITLE);

        $criteria->addSelectColumn(TaskPeer::TAS_DEF_SUBJECT_MESSAGE);

        $criteria->addSelectColumn(TaskPeer::TAS_DEF_PROC_CODE);

        $criteria->addSelectColumn(TaskPeer::TAS_DEF_MESSAGE);

        $criteria->addSelectColumn(TaskPeer::TAS_DEF_DESCRIPTION);

        $criteria->addSelectColumn(TaskPeer::TAS_TYPE);

        $criteria->addSelectColumn(TaskPeer::TAS_DURATION);

        $criteria->addSelectColumn(TaskPeer::TAS_DELAY_TYPE);

        $criteria->addSelectColumn(TaskPeer::TAS_TEMPORIZER);

        $criteria->addSelectColumn(TaskPeer::TAS_TYPE_DAY);

        $criteria->addSelectColumn(TaskPeer::TAS_TIMEUNIT);

        $criteria->addSelectColumn(TaskPeer::TAS_ALERT);

        $criteria->addSelectColumn(TaskPeer::TAS_PRIORITY_VARIABLE);

        $criteria->addSelectColumn(TaskPeer::TAS_ASSIGN_TYPE);

        $criteria->addSelectColumn(TaskPeer::TAS_ASSIGN_VARIABLE);

        $criteria->addSelectColumn(TaskPeer::TAS_GROUP_VARIABLE);

        $criteria->addSelectColumn(TaskPeer::TAS_MI_INSTANCE_VARIABLE);

        $criteria->addSelectColumn(TaskPeer::TAS_MI_COMPLETE_VARIABLE);

        $criteria->addSelectColumn(TaskPeer::TAS_ASSIGN_LOCATION);

        $criteria->addSelectColumn(TaskPeer::TAS_ASSIGN_LOCATION_ADHOC);

        $criteria->addSelectColumn(TaskPeer::TAS_TRANSFER_FLY);

        $criteria->addSelectColumn(TaskPeer::TAS_LAST_ASSIGNED);

        $criteria->addSelectColumn(TaskPeer::TAS_USER);

        $criteria->addSelectColumn(TaskPeer::TAS_CAN_UPLOAD);

        $criteria->addSelectColumn(TaskPeer::TAS_VIEW_UPLOAD);

        $criteria->addSelectColumn(TaskPeer::TAS_VIEW_ADDITIONAL_DOCUMENTATION);

        $criteria->addSelectColumn(TaskPeer::TAS_CAN_CANCEL);

        $criteria->addSelectColumn(TaskPeer::TAS_OWNER_APP);

        $criteria->addSelectColumn(TaskPeer::STG_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_CAN_PAUSE);

        $criteria->addSelectColumn(TaskPeer::TAS_CAN_SEND_MESSAGE);

        $criteria->addSelectColumn(TaskPeer::TAS_CAN_DELETE_DOCS);

        $criteria->addSelectColumn(TaskPeer::TAS_SELF_SERVICE);

        $criteria->addSelectColumn(TaskPeer::TAS_START);

        $criteria->addSelectColumn(TaskPeer::TAS_TO_LAST_USER);

        $criteria->addSelectColumn(TaskPeer::TAS_SEND_LAST_EMAIL);

        $criteria->addSelectColumn(TaskPeer::TAS_DERIVATION);

        $criteria->addSelectColumn(TaskPeer::TAS_POSX);

        $criteria->addSelectColumn(TaskPeer::TAS_POSY);

        $criteria->addSelectColumn(TaskPeer::TAS_WIDTH);

        $criteria->addSelectColumn(TaskPeer::TAS_HEIGHT);

        $criteria->addSelectColumn(TaskPeer::TAS_COLOR);

        $criteria->addSelectColumn(TaskPeer::TAS_EVN_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_BOUNDARY);

        $criteria->addSelectColumn(TaskPeer::TAS_DERIVATION_SCREEN_TPL);

        $criteria->addSelectColumn(TaskPeer::TAS_SELFSERVICE_TIMEOUT);

        $criteria->addSelectColumn(TaskPeer::TAS_SELFSERVICE_TIME);

        $criteria->addSelectColumn(TaskPeer::TAS_SELFSERVICE_TIME_UNIT);

        $criteria->addSelectColumn(TaskPeer::TAS_SELFSERVICE_TRIGGER_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_SELFSERVICE_EXECUTION);

        $criteria->addSelectColumn(TaskPeer::TAS_NOT_EMAIL_FROM_FORMAT);

        $criteria->addSelectColumn(TaskPeer::TAS_OFFLINE);

        $criteria->addSelectColumn(TaskPeer::TAS_EMAIL_SERVER_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_AUTO_ROOT);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_SERVER_UID);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_LAST_EMAIL);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_EMAIL_FROM_FORMAT);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_MESSAGE_TYPE);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_MESSAGE_TEMPLATE);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_SUBJECT_MESSAGE);

        $criteria->addSelectColumn(TaskPeer::TAS_RECEIVE_MESSAGE);

    }

    const COUNT = 'COUNT(TASK.TAS_UID)';
    const COUNT_DISTINCT = 'COUNT(DISTINCT TASK.TAS_UID)';

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns (You can also set DISTINCT modifier in Criteria).
     * @param      Connection $con
     * @return     int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, $con = null)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // clear out anything that might confuse the ORDER BY clause
        $criteria->clearSelectColumns()->clearOrderByColumns();
        if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->addSelectColumn(TaskPeer::COUNT_DISTINCT);
        } else {
            $criteria->addSelectColumn(TaskPeer::COUNT);
        }

        // just in case we're grouping: add those columns to the select statement
        foreach ($criteria->getGroupByColumns() as $column) {
            $criteria->addSelectColumn($column);
        }

        $rs = TaskPeer::doSelectRS($criteria, $con);
        if ($rs->next()) {
            return $rs->getInt(1);
        } else {
            // no rows returned; we infer that means 0 matches.
            return 0;
        }
    }
    /**
     * Method to select one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      Connection $con
     * @return     Task
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = TaskPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }
        return null;
    }
    /**
     * Method to do selects.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      Connection $con
     * @return     array Array of selected Objects
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, $con = null)
    {
        return TaskPeer::populateObjects(TaskPeer::doSelectRS($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect()
     * method to get a ResultSet.
     *
     * Use this method directly if you want to just get the resultset
     * (instead of an array of objects).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      Connection $con the connection to use
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     * @return     ResultSet The resultset object with numerically-indexed fields.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectRS(Criteria $criteria, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }

        if (!$criteria->getSelectColumns()) {
            $criteria = clone $criteria;
            TaskPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(self::DATABASE_NAME);

        // BasePeer returns a Creole ResultSet, set to return
        // rows indexed numerically.
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function populateObjects(ResultSet $rs)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = TaskPeer::getOMClass();
        $cls = Propel::import($cls);
        // populate the object(s)
        while ($rs->next()) {

            $obj = new $cls();
            $obj->hydrate($rs);
            $results[] = $obj;

        }
        return $results;
    }
    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return     TableMap
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(self::DATABASE_NAME)->getTable(self::TABLE_NAME);
    }

    /**
     * The class that the Peer will make instances of.
     *
     * This uses a dot-path notation which is tranalted into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @return     string path.to.ClassName
     */
    public static function getOMClass()
    {
        return TaskPeer::CLASS_DEFAULT;
    }

    /**
     * Method perform an INSERT on the database, given a Task or Criteria object.
     *
     * @param      mixed $values Criteria or Task object containing data that is used to create the INSERT statement.
     * @param      Connection $con the connection to use
     * @return     mixed The new primary key.
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from Task object
        }


        // Set the correct dbName
        $criteria->setDbName(self::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->begin();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }

        return $pk;
    }

    /**
     * Method perform an UPDATE on the database, given a Task or Criteria object.
     *
     * @param      mixed $values Criteria or Task object containing data create the UPDATE statement.
     * @param      Connection $con The connection to use (specify Connection exert more control over transactions).
     * @return     int The number of affected rows (if supported by underlying database driver).
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }

        $selectCriteria = new Criteria(self::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(TaskPeer::TAS_UID);
            $selectCriteria->add(TaskPeer::TAS_UID, $criteria->remove(TaskPeer::TAS_UID), $comparison);

        } else {
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(self::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Method to DELETE all rows from the TASK table.
     *
     * @return     int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll($con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->begin();
            $affectedRows += BasePeer::doDeleteAll(TaskPeer::TABLE_NAME, $con);
            $con->commit();
            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Method perform a DELETE on the database, given a Task or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or Task object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      Connection $con the connection to use
     * @return     int  The number of affected rows (if supported by underlying database driver).
     *             This includes CASCADE-related rows
     *              if supported by native driver or if emulated using Propel.
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
    */
    public static function doDelete($values, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(TaskPeer::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } elseif ($values instanceof Task) {

            $criteria = $values->buildPkeyCriteria();
        } else {
            // it must be the primary key
            $criteria = new Criteria(self::DATABASE_NAME);
            $criteria->add(TaskPeer::TAS_UID, (array) $values, Criteria::IN);
        }

        // Set the correct dbName
        $criteria->setDbName(self::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->begin();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            $con->commit();
            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given Task object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param      Task $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return     mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate(Task $obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(TaskPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(TaskPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->containsColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_TYPE))
            $columns[TaskPeer::TAS_TYPE] = $obj->getTasType();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_TIMEUNIT))
            $columns[TaskPeer::TAS_TIMEUNIT] = $obj->getTasTimeunit();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_ALERT))
            $columns[TaskPeer::TAS_ALERT] = $obj->getTasAlert();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_ASSIGN_TYPE))
            $columns[TaskPeer::TAS_ASSIGN_TYPE] = $obj->getTasAssignType();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_ASSIGN_LOCATION))
            $columns[TaskPeer::TAS_ASSIGN_LOCATION] = $obj->getTasAssignLocation();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_ASSIGN_LOCATION_ADHOC))
            $columns[TaskPeer::TAS_ASSIGN_LOCATION_ADHOC] = $obj->getTasAssignLocationAdhoc();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_TRANSFER_FLY))
            $columns[TaskPeer::TAS_TRANSFER_FLY] = $obj->getTasTransferFly();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_CAN_UPLOAD))
            $columns[TaskPeer::TAS_CAN_UPLOAD] = $obj->getTasCanUpload();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_VIEW_UPLOAD))
            $columns[TaskPeer::TAS_VIEW_UPLOAD] = $obj->getTasViewUpload();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_VIEW_ADDITIONAL_DOCUMENTATION))
            $columns[TaskPeer::TAS_VIEW_ADDITIONAL_DOCUMENTATION] = $obj->getTasViewAdditionalDocumentation();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_CAN_CANCEL))
            $columns[TaskPeer::TAS_CAN_CANCEL] = $obj->getTasCanCancel();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_CAN_PAUSE))
            $columns[TaskPeer::TAS_CAN_PAUSE] = $obj->getTasCanPause();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_CAN_SEND_MESSAGE))
            $columns[TaskPeer::TAS_CAN_SEND_MESSAGE] = $obj->getTasCanSendMessage();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_CAN_DELETE_DOCS))
            $columns[TaskPeer::TAS_CAN_DELETE_DOCS] = $obj->getTasCanDeleteDocs();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_SELF_SERVICE))
            $columns[TaskPeer::TAS_SELF_SERVICE] = $obj->getTasSelfService();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_START))
            $columns[TaskPeer::TAS_START] = $obj->getTasStart();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_TO_LAST_USER))
            $columns[TaskPeer::TAS_TO_LAST_USER] = $obj->getTasToLastUser();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_SEND_LAST_EMAIL))
            $columns[TaskPeer::TAS_SEND_LAST_EMAIL] = $obj->getTasSendLastEmail();

        if ($obj->isNew() || $obj->isColumnModified(TaskPeer::TAS_DERIVATION))
            $columns[TaskPeer::TAS_DERIVATION] = $obj->getTasDerivation();

        }

        return BasePeer::doValidate(TaskPeer::DATABASE_NAME, TaskPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     Task
     */
    public static function retrieveByPK($pk, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }

        $criteria = new Criteria(TaskPeer::DATABASE_NAME);

        $criteria->add(TaskPeer::TAS_UID, $pk);


        $v = TaskPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      Connection $con the connection to use
     * @throws     PropelException Any exceptions caught during processing will be
     *       rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(self::DATABASE_NAME);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria();
            $criteria->add(TaskPeer::TAS_UID, $pks, Criteria::IN);
            $objs = TaskPeer::doSelect($criteria, $con);
        }
        return $objs;
    }
}


// static code to register the map builder for this Peer with the main Propel class
if (Propel::isInit()) {
    // the MapBuilder classes register themselves with Propel during initialization
    // so we need to load them here.
    try {
        BaseTaskPeer::getMapBuilder();
    } catch (Exception $e) {
        Propel::log('Could not initialize Peer: ' . $e->getMessage(), Propel::LOG_ERR);
    }
} else {
    // even if Propel is not yet initialized, the map builder class can be registered
    // now and then it will be loaded when Propel initializes.
    require_once 'classes/model/map/TaskMapBuilder.php';
    Propel::registerMapBuilder('classes.model.map.TaskMapBuilder');
}

