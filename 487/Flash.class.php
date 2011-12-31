<?php

class Flash extends AgaviParameterHolder {
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * Initialize this Flash class.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Filter.
	 *
	 * @author     Ross Lawley
	 * @since      0.11.2
	 */
	public function __construct(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$request = $this->context->getRequest();
		parent::__construct($parameters);

		if ($this->context->getStorage()->read('agavi.org.flash')) {
			$flash = $this->context->getStorage()->read('agavi.org.flash');
			$this->write($flash[0], $flash[1]);
		}
	}

	/**
	 * Adds a Flash message
	 *
	 * Adds A Flash message into the flash array
	 *
	 * @param $message object the actual message
	 * @param $style string The style of the message for formatting..
	 *
	 * @author     Ross Lawley
	 * @since      0.11.2
	 */
	function write($message, $style = 'general') {
		$this->setParameter('flash', array($message, $style));
		$this->context->getStorage()->write('agavi.org.flash', $this->getParameter('flash'));
	}

	/**
	 * Checks for flash messages
	 *
	 * Adds A Flash message into the content array
	 *
	 * @return bool whether or not there is are any flash messages
	 *
	 * @author     Ross Lawley
	 * @since      0.11.2
	 */
	function hasFlash() {
		return $this->hasParameter('flash');
	}

	/**
	 * Checks for flash messages of type $type
	 *
	 * @return bool whether or not there is a flash messages of given type
	 *
	 * @author     André Fiedler
	 * @author     Ross Lawley
	 * @since      1.0
	*/
	function hasFlashType($type) {
		$flash = $this->getParameter('flash', array());
		return (array_key_exists(1, $flash) && $flash[1] === $type);
	}

	/**
	 * Returns the Flash Messages Array
	 *
	 * @return array an array of flash messages
	 *
	 * @author     Ross Lawley
	 * @author     André Fiedler
	 * @since      0.11.2
	 */
	function read() {
		$this->context->getStorage()->write('agavi.org.flash', array());
		return $this->removeParameter('flash');
	}
}
?>
