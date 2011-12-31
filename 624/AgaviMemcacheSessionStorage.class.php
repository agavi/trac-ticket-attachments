<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * a class to use memcache as a session storage for the agavi
 * session, especially useful in loadbalanced environments 
 * where you can't change the php.ini to use the proper session
 * handler
 * 
 * * <b>Required parameters:</b>
 *
 * # <b>memcache_servers</b> - [none] - an array of memcache connection urls, format
 *                                      see http://www.php.net/manual/en/ref.memcache.php .
 * @package    agavi
 * @subpackage storage
 *
 * @author     felix gilcher, exozet interact <felix.gilcher@exozet.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * 
 */
class AgaviMemcacheSessionStorage extends AgaviSessionStorage
{
    /**
	 * Initialize this Storage.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     felix gilcher, exozet interact <felix.gilcher@exozet.com>
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
        parent::initialize($context, $parameters);
        
        ini_set('session.save_handler', 'memcache');
        
        if (!$this->hasParameter('memcache_servers') || (false == ($servers = $this->getParameter('memcache_servers')))) {
            throw new AgaviInitializationException('No memcache servers defined for AgaviMemcacheSessionStorage');
        }
        
        $connect_url = implode(',', $this->getParameter('memcache_servers'));
        session_save_path($connect_url);
	}
}
?>