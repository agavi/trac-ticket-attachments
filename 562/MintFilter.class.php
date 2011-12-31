<?php

/**
 * MintFilter using Mint statistics (www.haveamint.com) and managing them outside
 * templates and with different development environments
 *
 * <b>parameters:</b>
 *
 * # <b>mint_url</b> - [false] - The Url where your Mint install and JS lives
 *
 * # <b>routes_to_ignore</b> - [array] - An array of route names to be ignored.
 *
 * # <b>ip_addresses_to_ignore</b> - [array] - An array of IP addresses to be ignored.
 *
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class MintFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.0
	 */
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$filterChain->execute($container);

		$context = $this->getContext();
		$request = $context->getRequest();
		$response = $container->getResponse();

		// Grab the configurations
		$cfg = array_merge($this->getParameters(), $request->getAttributes('org.agavi.filter.SlotPopulationFilter'));

		// Ensure we only action the correct output types
		$outputType = $container->getOutputType();
		if(is_array($cfg['output_types']) && !in_array($outputType->getName(), $cfg['output_types'])) {
			return;
		}

		$content = $response->getContent();
		$matched_routes = $request->getAttribute('matched_routes', 'org.agavi.routing');

		// Find any routes that are to be ignored
		$ignore_route = false;
		if (!empty($cfg['routes_to_ignore'])) {
			foreach ($cfg['routes_to_ignore'] as $route) {
				if (in_array($route, $matched_routes)) {
					$ignore_route = true;
					break;
				}
			}
		}

		// Check if we need to inject the mint script
		if ($cfg['mint_url'] === false || strpos($content,'frameset') !== false ||
			in_array($_SERVER['REMOTE_ADDR'], $cfg['ip_addresses_to_ignore']) || $ignore_route) {
			return ;
		}

		// Inject the mint script
		$replace = array ('</head>', '</HEAD>');
		$response->setContent(str_replace($replace, "<script src='{$cfg['mint_url']}' type='text/javascript'></script>\r</head>", $content));
		return;
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during
	 *                                         initialization
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->setParameter('mint_url', false);
		$this->setParameter('routes_to_ignore', array());
		$this->setParameter('ip_addresses_to_ignore', array());

		// initialize parent
		parent::initialize($context, $parameters);
		$this->setParameter('methods', (array) $this->getParameter('methods'));
		if($ot = $this->getParameter('output_types')) {
			$this->setParameter('output_types', (array) $ot);
		}
	}
}

?>
