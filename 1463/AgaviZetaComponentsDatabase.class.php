<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviZetaComponentsDatabase provides connectivity for ZetaComponents/ezComponents
 * database API layer.
 *
 * Example config:
 *
 * reqular mysql config:
 * <database name="zeta_mysql_main" class="AgaviZetaComponentsDatabase">
 *     <ae:parameter name="type">mysql</ae:parameter>
 *     <ae:parameter name="dbname">test</ae:parameter>
 *     <ae:parameter name="host">localhost</ae:parameter>
 *     <ae:parameter name="charset">UTF8</ae:parameter>
 *     <ae:parameter name="username">root</ae:parameter>
 *     <ae:parameter name="password">root</ae:parameter>
 * </database>
 *
 * in memory sqlite:
 * <database name="zeta_sqlite_cache" class="AgaviZetaComponentsDatabase">
 *     <ae:parameter name="type">sqlite</ae:parameter>
 *     <ae:parameter name="port">memory</ae:parameter>
 * </database>
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Behrooz Shabani <behrooz@rock.com>
 *
 * @since      1.0.7
 *
 * @version    $Id
 */

class AgaviZetaComponentsDatabase extends AgaviDatabase
{
	/**
	 * Initialize this Database.
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @throws     AgaviDatabaseException if can't load ezcDbFactory
	 * @author     Behrooz Shabani <behrooz@rock.com>
	 * @since      1.0.7
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		if(!class_exists('ezcDbFactory'))
		{
			throw new AgaviDatabaseException('ZetaComponents has not configured correctly so ezcDbFactory class is not accessible');
		}
	}

	/**
	 * Connect to the database.
	 *
	 * @throws     ezcDbMissingParameterException if database parameter has not specified
	 * @author     Behrooz Shabani <behrooz@rock.com>
	 * @since      1.0.7
	 */
	public function connect()
	{
		$this->resource = $this->connection = ezcDbFactory::create($this->getParameters());
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     AgaviDatabaseException If an error occurs while shutting
	 * down this database.
	 *
	 * @author     Behrooz Shabani <behrooz@rock.com>
	 * @since      1.0.7
	 */
	public function shutdown()
	{
		// assigning null to a previously open connection object causes a disconnect
		$this->connection = null;
	}
}
