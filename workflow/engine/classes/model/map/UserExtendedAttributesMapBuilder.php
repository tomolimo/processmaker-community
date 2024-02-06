<?php

require_once 'propel/map/MapBuilder.php';
include_once 'creole/CreoleTypes.php';


/**
 * This class adds structure of 'USER_EXTENDED_ATTRIBUTES' table to 'workflow' DatabaseMap object.
 *
 *
 *
 * These statically-built map classes are used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    workflow.classes.model.map
 */
class UserExtendedAttributesMapBuilder
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'classes.model.map.UserExtendedAttributesMapBuilder';

    /**
     * The database map.
     */
    private $dbMap;

    /**
     * Tells us if this DatabaseMapBuilder is built so that we
     * don't have to re-build it every time.
     *
     * @return     boolean true if this DatabaseMapBuilder is built, false otherwise.
     */
    public function isBuilt()
    {
        return ($this->dbMap !== null);
    }

    /**
     * Gets the databasemap this map builder built.
     *
     * @return     the databasemap
     */
    public function getDatabaseMap()
    {
        return $this->dbMap;
    }

    /**
     * The doBuild() method builds the DatabaseMap
     *
     * @return     void
     * @throws     PropelException
     */
    public function doBuild()
    {
        $this->dbMap = Propel::getDatabaseMap('workflow');

        $tMap = $this->dbMap->addTable('USER_EXTENDED_ATTRIBUTES');
        $tMap->setPhpName('UserExtendedAttributes');

        $tMap->setUseIdGenerator(true);

        $tMap->addPrimaryKey('UEA_ID', 'UeaId', 'string', CreoleTypes::BIGINT, true, 20);

        $tMap->addColumn('UEA_NAME', 'UeaName', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('UEA_ATTRIBUTE_ID', 'UeaAttributeId', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('UEA_HIDDEN', 'UeaHidden', 'int', CreoleTypes::INTEGER, false, null);

        $tMap->addColumn('UEA_REQUIRED', 'UeaRequired', 'int', CreoleTypes::INTEGER, false, null);

        $tMap->addColumn('UEA_PASSWORD', 'UeaPassword', 'int', CreoleTypes::INTEGER, false, null);

        $tMap->addColumn('UEA_OPTION', 'UeaOption', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('UEA_ROLES', 'UeaRoles', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('UEA_OWNER', 'UeaOwner', 'string', CreoleTypes::BIGINT, false, 20);

        $tMap->addColumn('UEA_DATE_CREATE', 'UeaDateCreate', 'int', CreoleTypes::TIMESTAMP, false, null);

    } // doBuild()

} // UserExtendedAttributesMapBuilder
