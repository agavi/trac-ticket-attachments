<?php
/**
 * ConsoleRequest
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Felix Gilcher <felix@andersground.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.RC4
 *
 * @version    $Id$
 */
class ConsoleRequest extends AgaviRequest
 {
	/**
	 * the command passed to the cli script
	 */
	private $command;

	/**
	 * the name the cli script was called with
	 */
	private $scriptName;
	
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * Constructor.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'request_data_holder_class' => 'AgaviRequestDataHolder',
		));
	}

	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     Felix Gilcher <felix@andersground.net>
	 * @since      0.11.RC4
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$this->setMethod('console');
		
		$argv = & $GLOBALS['argv'];
		
		$this->scriptName = $argv[0];
				
		if (isset($argv[1])) {
			$this->command = $argv[1];
		}
		
		$parameters = array();
		
		$i = 2; 
		$argc = count($argv);
		
		while ($i < $argc) {
			if (!empty($argv[$i + 1]) && '-' != $argv[$i + 1]{0}) {
				$parameters[ltrim($argv[$i], '-')] = $argv[$i + 1];
				$i += 2;
			} else {
				$parameters[ltrim($argv[$i], '-')] = true;
				++$i;
			}
		}
		
		if(!AgaviConfig::get('core.use_routing', false)) {
			// routing disabled, set the module and action parameters manually and bail out
			$module = strtok($this->getCommand(), '.');
			$action = strtok('');
			
			$parameters[$this->getParameter('module_accessor')] = $module;
			$parameters[$this->getParameter('action_accessor')] = $action;
		}
		
		
		$rdhc = $this->getParameter('request_data_holder_class');
		$this->setRequestData(new $rdhc(array(
			constant("$rdhc::SOURCE_PARAMETERS") => $parameters
		)));
		
	}
	
	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		if($this->getParameter("unset_input", true)) {
			unset($GLOBALS['argv']);
		}
	}
 }
?>
