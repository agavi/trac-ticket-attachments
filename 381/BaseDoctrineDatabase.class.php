<?php

/**
 * An Agavi Database Wrapper ORM for Doctrine, derived from the native PDO driver.
 *
 *
 * @package    BusinessEdge
 * @subpackage database
 *
 * @author     Ross Lawley <ross.lawley@libraryhouse.net>
 * @since      0.1
 *
 * @version    $Id: BaseDoctrineDatabase.class.php 585 2007-06-25 13:58:12Z  $
 */
class BaseDoctrineDatabase extends AgaviDatabase
{

	/**
	 * Stores the actual AgaviDatabase connection
	 *
	 * @var        AgaviDatabase The AgaviDatabase instance used internally.
	 *
	 * @since      0.1
	 */
	protected $connection = null;

	/**
	 * Connect to the database.
	 *
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be
	 *                                           created.
	 *
	 * @author     Ross Lawley <ross.lawley@libraryhouse.net>
	 * @since      0.1
	 */
	public function connect()
	{
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');
			switch ($method) {
				case 'normal':
					$runtime = AgaviToolkit::expandDirectives($this->getParameter('config', null));
					break;
				case 'server':
					$runtime = $_SERVER[$this->getParameter('config')];
					break;
				case 'env':
					$runtime = $_ENV[$this->getParameter('config')];
					break;
				default:
					$error = 'Invalid DoctrineDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new AgaviDatabaseException($error);
			}

			$dsn = $this->getParameter('dsn');
			if($dsn == null) {
				// missing required dsn parameter
				$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
				throw new AgaviDatabaseException($error);
			}

			//$this->agaviDatabase = new Doctrine_Connection($dsn);
			$this->connection = Doctrine_Manager::connection($dsn);
			$this->resource =& $this->manager;
		} catch( Doctrine_Db_Exception $e) {
			// the connection's foobar'd
			throw new AgaviDatabaseException($e->getMessage ());
		}
	}

	/**
	 * Initialize Doctrine set the autoloading
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @author     Ross Lawley <ross.lawley@libraryhouse.net>
	 * @since      0.11.?
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		// Ensure that Doctrine hasn't already been included
		if (!class_exists('Doctrine')) {
			// get doctrine class path
			$classPath = AgaviToolkit::expandDirectives($this->getParameter('classpath',null));
			if(!is_null($classPath)) {
				require($classPath);
			} else {
				require(AgaviConfig::get('core.libs_dir').'/doctrine/Doctrine.php');
			}
			spl_autoload_register(array('Doctrine', 'autoload'));
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Ross Lawley <ross.lawley@libraryhouse.net>
	 * @since      0.1
	 */
	public function shutdown()
	{
		return;
	}
}

?>
