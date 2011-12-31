<?php

/**
 * The ezCacheModel Wrapper - loads defaults from the app/config/ezCache.xml
 *
 *
 * @see       http://ezcomponents.org/docs/api/trunk/Cache/ezcCacheManager.html
 * @package    Lib
 * @subpackage Cache
 *
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @since      0.11.1 RC1
 *
 * @version    $Id$
 */
class ezCacheManagerModel extends AgaviModel
{
	/**
	 * @var        mixed  A parameter holder
	 */
	protected $parameters = null;

	/**
	 * Tries to autoload the needed EzComponent Classes
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.1
	 */
	public static function autoload($className) {
		try {
			ezcBase::autoload($className);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Setup the ezCacheManager
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array of parameters
	 *
	 * The Parameters can contain the following
	 * * location - the location of the cache relative to the core cache dir ezCache dir
	 * * storage class - @see http://ezcomponents.org/docs/api/trunk/Cache/ezcCacheStorage.html
	 * * options - an array of options like time to live
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.1
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		// Ensure that ezcBase hasn't already been included
		if (!class_exists('ezcBase')) {
			// Change this to your location if different
			require_once AgaviConfig::get('ezcomponents.lib_dir') . '/trunk/Base/src/base.php';
			spl_autoload_register(array('ezCacheManagerModel', 'autoload'));
		}

		//add config parameters
		$configs = AgaviConfig::get("core.config_dir") . '/ezCache.xml';
		if(is_readable($configs)) {
			$config = include(AgaviConfigCache::checkConfig($configs));
		}

		// Override Configs with passed parameters if any
		$cfg = array_merge($config, $parameters);

		// Save the parameters in a AgaviParameterHolder for later use
		$this->parameters = new AgaviParameterHolder($cfg);

	}

	/**
	 * Create an ezCache
	 *
	 * @param      string The id of the cache element.
	 * @return     mixed true if successful or throws an AgaviCacheException
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.1
	 */
	public function createCache($id)
	{
		$cfg = $this->parameters;
		if (!is_null($cfg->getParameter('location'))) {
			$perms = fileperms(AgaviConfig::get('core.cache_dir')) ^ 0x4000;
			AgaviToolkit::mkdir($cfg->getParameter('location'), $perms, true);
		}
	
		// Force ttl options to be an int
		$options = $cfg->getParameter('options', array());
		if (isset($options['ttl'])) {
			$options['ttl'] = (int) $options['ttl'];
		}

		try {
			ezcCacheManager::createCache($id, 
				$cfg->getParameter('location'),
				$cfg->getParameter('storage_class', 'ezcCacheStorageFilePlain'),
				$options
			);
			return true;
		} catch(ezcBaseException $e) {
			// the cache was foobar'd
			throw new AgaviCacheException($e->getMessage());
		}
	}

	/**
	 * Gets an ezCache object for use
	 *
	 * @param      string The id of the cache element.
	 * @return     mixed the ezCache object if found or throws an AgaviCacheException
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.1
	 */
	public function getCache($id)
	{
		try {
			return ezcCacheManager::getCache( $id );
		} catch(ezcBaseException $e) {
			// Something didn't happen
			throw new AgaviCacheException($e->getMessage());
		}
	}

	/**
	 * Convenience for fetching an uninitialised ezCache object for use
	 *
	 * @param      string The id of the cache element.
	 * @return     mixed the ezCache object if found or throws an AgaviCacheException
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.1
	 */
	public function fetchCache($id)
	{
		$this->createCache($id);
		return $this->getCache( $id );
	}
}
?>
