<?php

require_once 'propel/map/MapBuilder.php';
include_once 'creole/CreoleTypes.php';


/**
 * This class adds structure of 'JOBS_PENDING' table to 'workflow' DatabaseMap object.
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
class JobsPendingMapBuilder
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'classes.model.map.JobsPendingMapBuilder';

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

        $tMap = $this->dbMap->addTable('JOBS_PENDING');
        $tMap->setPhpName('JobsPending');

        $tMap->setUseIdGenerator(true);

        $tMap->addPrimaryKey('ID', 'Id', 'string', CreoleTypes::BIGINT, true, 20);

        $tMap->addColumn('QUEUE', 'Queue', 'string', CreoleTypes::VARCHAR, true, 255);

        $tMap->addColumn('PAYLOAD', 'Payload', 'string', CreoleTypes::LONGVARCHAR, true, null);

        $tMap->addColumn('ATTEMPTS', 'Attempts', 'int', CreoleTypes::TINYINT, true, 3);

        $tMap->addColumn('RESERVED_AT', 'ReservedAt', 'int', CreoleTypes::TINYINT, false, 10);

        $tMap->addColumn('AVAILABLE_AT', 'AvailableAt', 'int', CreoleTypes::TINYINT, true, 10);

        $tMap->addColumn('CREATED_AT', 'CreatedAt', 'int', CreoleTypes::TINYINT, true, 10);

    } // doBuild()

} // JobsPendingMapBuilder
