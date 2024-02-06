<?php

require_once 'propel/map/MapBuilder.php';
include_once 'creole/CreoleTypes.php';


/**
 * This class adds structure of 'SCHEDULER' table to 'workflow' DatabaseMap object.
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
class SchedulerMapBuilder
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'classes.model.map.SchedulerMapBuilder';

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

        $tMap = $this->dbMap->addTable('SCHEDULER');
        $tMap->setPhpName('Scheduler');

        $tMap->setUseIdGenerator(true);

        $tMap->addPrimaryKey('ID', 'Id', 'string', CreoleTypes::BIGINT, true, 20);

        $tMap->addColumn('TITLE', 'Title', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('STARTINGTIME', 'Startingtime', 'string', CreoleTypes::VARCHAR, false, 100);

        $tMap->addColumn('ENDINGTIME', 'Endingtime', 'string', CreoleTypes::VARCHAR, false, 100);

        $tMap->addColumn('EVERYON', 'Everyon', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('INTERVAL', 'Interval', 'string', CreoleTypes::VARCHAR, false, 10);

        $tMap->addColumn('DESCRIPTION', 'Description', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('EXPRESSION', 'Expression', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('DEFAULT_VALUE', 'DefaultValue', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('BODY', 'Body', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('TYPE', 'Type', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('CATEGORY', 'Category', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('SYSTEM', 'System', 'int', CreoleTypes::TINYINT, false, 3);

        $tMap->addColumn('TIMEZONE', 'Timezone', 'string', CreoleTypes::VARCHAR, false, 255);

        $tMap->addColumn('ENABLE', 'Enable', 'int', CreoleTypes::TINYINT, false, 3);

        $tMap->addColumn('CREATION_DATE', 'CreationDate', 'int', CreoleTypes::TIMESTAMP, false, null);

        $tMap->addColumn('LAST_UPDATE', 'LastUpdate', 'int', CreoleTypes::TIMESTAMP, false, null);

    } // doBuild()

} // SchedulerMapBuilder
