<?php

require_once 'propel/map/MapBuilder.php';
include_once 'creole/CreoleTypes.php';


/**
 * This class adds structure of 'CASE_LIST' table to 'workflow' DatabaseMap object.
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
class CaseListMapBuilder
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'classes.model.map.CaseListMapBuilder';

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

        $tMap = $this->dbMap->addTable('CASE_LIST');
        $tMap->setPhpName('CaseList');

        $tMap->setUseIdGenerator(true);

        $tMap->addPrimaryKey('CAL_ID', 'CalId', 'int', CreoleTypes::INTEGER, true, null);

        $tMap->addColumn('CAL_TYPE', 'CalType', 'string', CreoleTypes::VARCHAR, true, 255);

        $tMap->addColumn('CAL_NAME', 'CalName', 'string', CreoleTypes::VARCHAR, true, 255);

        $tMap->addColumn('CAL_DESCRIPTION', 'CalDescription', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('ADD_TAB_UID', 'AddTabUid', 'string', CreoleTypes::VARCHAR, true, 32);

        $tMap->addColumn('CAL_COLUMNS', 'CalColumns', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('USR_ID', 'UsrId', 'string', CreoleTypes::BIGINT, true, 20);

        $tMap->addColumn('CAL_ICON_LIST', 'CalIconList', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('CAL_ICON_COLOR', 'CalIconColor', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('CAL_ICON_COLOR_SCREEN', 'CalIconColorScreen', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('CAL_CREATE_DATE', 'CalCreateDate', 'int', CreoleTypes::TIMESTAMP, true, null);

        $tMap->addColumn('CAL_UPDATE_DATE', 'CalUpdateDate', 'int', CreoleTypes::TIMESTAMP, true, null);

    } // doBuild()

} // CaseListMapBuilder
