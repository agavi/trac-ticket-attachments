<?php
/**
 * ConsoleRouting
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Felix Gilcher <felix@andersground.net>
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.RC4
 *
 * @version    $Id$
 */
class ConsoleRouting extends AgaviRouting
{
	
	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext The Context.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
 	 * @author     Felix Gilcher <felix@andersground.net>
	 * @since      0.11.RC4
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		$rq = $this->context->getRequest();
		$this->input = $rq->getCommand();
		$configs = AgaviConfig::get("core.config_dir") . '/console.xml';
		if(is_readable($configs)) {
			$this->setParameters(include(AgaviConfigCache::checkConfig($configs)));
		}
	}

	/**
	 * Generate a formatted Agavi URL.
	 *
	 * @param      string A route name.
	 * @param      array  An associative array of parameters.
	 * @param      mixed  An array of options, or the name of an options preset.
	 *
	 * @return     string The generated URL.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.RC4
	 */
	public function gen($route, array $params = array(), $options = array())
	{
		if (!$this->hasParameter('path')) {
			throw new BadMethodCallException('No default path set! - unable to generate route');
		}

		if(!AgaviConfig::get('core.use_routing')) {
			throw new BadMethodCallException('Not using routing - unable to generate the url');
		}
		
		$req = $this->context->getRequest();

		if(substr($route, -1) == '*') {
			$options['refill_all_parameters'] = true;
			$route = substr($route, 0, -1);
		}

		if($route === null) {
			throw new BadMethodCallException('No Route to map to - unable to generate the url');
		}

		$routes = $this->getAffectedRoutes($route);
		if(count($routes)) {
			// the route exists and routing is enabled, the parent method handles it
			list($path, $usedParams, $options) = parent::gen($routes, array_merge(array_map('rawurlencode', array_filter($params, array('AgaviToolkit', 'isNotArray'))), array_filter($params, 'is_null')), $options);
		}

		return $this->getParameter('path').$path;
	}

}

?>
