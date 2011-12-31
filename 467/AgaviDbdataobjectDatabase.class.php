<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviDbdataobjectDatabase is a bridge between Agavi and PEAR DB_DataObject 
 * database library
 *
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Mike Seth       <me@mikeseth.com>
 * @author     Armen Baghumian <armen@OpenSourceClub.org>     
 * @copyright  (c) Authors
 * @since      0.11
 *
 * @version    $Id$
 */

class AgaviDbdataobjectDatabase extends AgaviDatabase
{
    /**
     * Connect to the database.
     * 
     * @author     Mike Seth       <me@mikeseth.com>
     * @author     Armen Baghumian <armen@OpenSourceClub.org>     
     * @since      0.11
     */    
    public function connect()
    {
        $driver = 'DB';                             // default driver
        $dsn    = $this->getParameter('dsn');

        $do = $this->getParameter('dataobject');
        if ($do && isset($do['db_driver'])) {
            $driver = $do['db_driver'];
        }

        if ('DB' == $driver) {

            if (!class_exists('DB')) {
                include('DB.php');
            }

            $options = PEAR::getStaticProperty('DB', 'options');
            if ($options) {
                $this->connection = DB::connect($dsn, $options);
            } else {
                $this->connection = DB::connect($dsn);
            }
            
        } else {

            if (!class_exists('MDB2')) {
                include('MDB2.php');
            }
            
            $options          = PEAR::getStaticProperty('MDB2', 'options');
            $this->connection = MDB2::connect($dsn, $options);
        }

        if (PEAR::isError($this->connection)) {

            throw new AgaviDatabaseException($this->connection->getMessage());
            $this->connection = Null;
        }
    }

    /**
     * Initialize DB_DataObject 
     * 
     * @param      AgaviDatabaseManager The database manager of this instance.
     * @param      array An associative array of initialization parameters.
     *
     * @author     Mike Seth       <me@mikeseth.com>
     * @author     Armen Baghumian <armen@OpenSourceClub.org>
     * @since      0.11.0
     */
    public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
    {
        parent::initialize($databaseManager, $parameters);

        if(!class_exists('DB_DataObject')) {
            include('DB/DataObject.php');
        }

        $dsn = $this->getParameter('dsn', False);
        if (!$dsn) {
            $error = 'Please define a DSN in database configuration';
            throw new AgaviDatabaseException($error);
        }

        $do = $this->getParameter('dataobject');
        if ($do) {

            $do['database'] = $dsn;
            $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
            
            foreach($do as $key => $value) {
                $options[$key] = $value;
            }
        }

        // let's change DB_DataObjcet errors to Agavi friendly exceptions
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$this, 'handlePearError'));
        $options['dont_die'] = 'please dont die, please please please...';    
    }

     /**
     * Retrieve the database connection associated with this Database
     * implementation.
     *
     * When this is executed on a Database implementation that isn't an
     * abstraction layer, a copy of the resource will be returned.
     *
     * @return     mixed A database connection.
     *
     * @author     Sean Kerr <skerr@mojavi.org>
     * @since      0.9.0
     */
    public function getConnection()
    {
        if($this->connection === null) {
            $this->connect();
        }
        return parent::getConnection();
    }

    /**
     * Execute the shutdown procedure.
     *
     * @author     Mike Seth <me@mikeseth.com>     
     * @since      0.11
     */    
    public function shutdown()
    {
        if ($this->db) {
            $db->disconnect();
        }
    }

    /**
     *  Change change PEAR errors to Agavi friendly exceptions.
     *
     * @param      PEAR_Error object.
     *
     * @author     Armen Baghumian <armen@OpenSourceClub.org>     
     * @since      0.11
     */
    public function handlePearError($error)
    {
        switch ($error->getType()) {

            case 'DB_DataObject_Error':
                throw new AgaviDatabaseException($error->getMessage());
                break;
        
            // TODO: any other types should be included ?
            //       MDB2_Error, DB_Error, ...
        }
    }
}

?>
